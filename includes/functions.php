<?php
/**
 * Funções auxiliares da loja Northside
 */

// ============================================
// Genéricas
// ============================================

function formatarPreco(float $valor): string {
    return number_format($valor, 2, ',', '.') . '€';
}

function imagemProdutoUrl(?string $imagem): string {
    if (empty($imagem)) $imagem = 'sem-imagem.jpg';
    return URL_BASE . 'assets/img/produtos/' . $imagem;
}

function normalizarTexto(string $txt): string {
    $txt = mb_strtolower(trim($txt), 'UTF-8');
    $comAcentos = ['á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç'];
    $semAcentos = ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c'];
    return str_replace($comAcentos, $semAcentos, $txt);
}

function estrelasHtml(float $media): string {
    $cheias = round($media);
    return str_repeat('★', (int)$cheias) . str_repeat('☆', 5 - (int)$cheias);
}

function buscarDefinicao(PDO $pdo, string $chave, string $default = ''): string {
    try {
        $stmt = $pdo->prepare("SELECT valor FROM definicoes WHERE chave = :chave");
        $stmt->execute(['chave' => $chave]);
        $valor = $stmt->fetchColumn();
        return $valor !== false ? $valor : $default;
    } catch (PDOException $e) {
        // a tabela "definicoes" ainda não existe (falta correr a migração) — não deixar a página em branco
        return $default;
    }
}

// ============================================
// Produtos e categorias
// ============================================

function buscarCategorias(PDO $pdo): array {
    return $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
}

function buscarProdutos(PDO $pdo, array $filtros = []): array {
    $sql = "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug
            FROM produtos p
            LEFT JOIN categorias c ON c.id = p.categoria_id
            WHERE p.ativo = 1";
    $params = [];

    if (!empty($filtros['categoria_slug'])) {
        $sql .= " AND c.slug = :slug";
        $params['slug'] = $filtros['categoria_slug'];
    }
    if (!empty($filtros['destaque'])) {
        $sql .= " AND p.destaque = 1";
    }
    if (!empty($filtros['pesquisa'])) {
        $sql .= " AND p.nome LIKE :pesquisa";
        $params['pesquisa'] = '%' . $filtros['pesquisa'] . '%';
    }

    $sql .= " ORDER BY p.criado_em DESC";
    if (!empty($filtros['limite'])) {
        $sql .= " LIMIT " . (int)$filtros['limite'];
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function buscarProdutoPorId(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.nome AS categoria_nome, c.slug AS categoria_slug
         FROM produtos p
         LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.id = :id AND p.ativo = 1"
    );
    $stmt->execute(['id' => $id]);
    $produto = $stmt->fetch();
    return $produto ?: null;
}

function buscarProdutosRelacionados(PDO $pdo, array $produtoAtual, int $limite = 3): array {
    $stmt = $pdo->prepare(
        "SELECT p.*, c.nome AS categoria_nome
         FROM produtos p LEFT JOIN categorias c ON c.id = p.categoria_id
         WHERE p.ativo = 1 AND p.id != :id AND p.categoria_id <=> :categoria_id
         ORDER BY p.criado_em DESC LIMIT :limite"
    );
    $stmt->bindValue(':id', $produtoAtual['id'], PDO::PARAM_INT);
    $stmt->bindValue(':categoria_id', $produtoAtual['categoria_id'], PDO::PARAM_INT);
    $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    $relacionados = $stmt->fetchAll();

    if (count($relacionados) < $limite) {
        $faltam = $limite - count($relacionados);
        $idsExcluir = array_merge([$produtoAtual['id']], array_column($relacionados, 'id'));
        $placeholders = implode(',', array_fill(0, count($idsExcluir), '?'));
        $stmt2 = $pdo->prepare(
            "SELECT p.*, c.nome AS categoria_nome
             FROM produtos p LEFT JOIN categorias c ON c.id = p.categoria_id
             WHERE p.ativo = 1 AND p.id NOT IN ($placeholders)
             ORDER BY p.criado_em DESC LIMIT $faltam"
        );
        $stmt2->execute($idsExcluir);
        $relacionados = array_merge($relacionados, $stmt2->fetchAll());
    }
    return $relacionados;
}

// ============================================
// Tamanhos (roupa)
// ============================================

function buscarTamanhosProduto(PDO $pdo, int $produtoId): array {
    try {
        $stmt = $pdo->prepare(
            "SELECT tamanho, stock FROM produto_tamanhos
             WHERE produto_id = :id
             ORDER BY FIELD(tamanho, 'XS','S','M','L','XL','XXL')"
        );
        $stmt->execute(['id' => $produtoId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // a tabela "produto_tamanhos" ainda não existe (falta correr a migração)
        return [];
    }
}

function buscarStockTamanho(PDO $pdo, int $produtoId, string $tamanho): ?int {
    try {
        $stmt = $pdo->prepare("SELECT stock FROM produto_tamanhos WHERE produto_id = :id AND tamanho = :tamanho");
        $stmt->execute(['id' => $produtoId, 'tamanho' => $tamanho]);
        $valor = $stmt->fetchColumn();
        return $valor === false ? null : (int)$valor;
    } catch (PDOException $e) {
        return null;
    }
}

// ============================================
// Avaliações
// ============================================

function buscarAvaliacoes(PDO $pdo, ?int $limite = null): array {
    $sql = "SELECT * FROM avaliacoes WHERE aprovado = 1 ORDER BY criado_em DESC";
    if ($limite) $sql .= " LIMIT " . (int)$limite;
    return $pdo->query($sql)->fetchAll();
}

function buscarAvaliacoesProduto(PDO $pdo, int $produtoId): array {
    $stmt = $pdo->prepare("SELECT * FROM avaliacoes WHERE produto_id = :id AND aprovado = 1 ORDER BY criado_em DESC");
    $stmt->execute(['id' => $produtoId]);
    return $stmt->fetchAll();
}

function mediaAvaliacoesGeral(PDO $pdo): array {
    $r = $pdo->query("SELECT AVG(estrelas) AS media, COUNT(*) AS total FROM avaliacoes WHERE aprovado = 1")->fetch();
    return ['media' => round((float)$r['media'], 1), 'total' => (int)$r['total']];
}

function criarAvaliacaoProduto(PDO $pdo, ?int $produtoId, string $nome, int $estrelas, string $comentario): void {
    $estrelas = max(1, min(5, $estrelas));

    $stmt = $pdo->prepare(
        "INSERT INTO avaliacoes (produto_id, nome_cliente, estrelas, comentario, aprovado)
         VALUES (:produto_id, :nome, :estrelas, :comentario, 1)"
    );
    $stmt->execute([
        'produto_id' => $produtoId,
        'nome' => $nome,
        'estrelas' => $estrelas,
        'comentario' => $comentario,
    ]);

    // só recalcula a média/contagem do produto se a avaliação estiver ligada a um
    if ($produtoId === null) return;

    $stmt2 = $pdo->prepare(
        "UPDATE produtos SET
            estrelas_media = (SELECT AVG(estrelas) FROM avaliacoes WHERE produto_id = :id AND aprovado = 1),
            num_avaliacoes = (SELECT COUNT(*) FROM avaliacoes WHERE produto_id = :id AND aprovado = 1)
         WHERE id = :id"
    );
    $stmt2->execute(['id' => $produtoId]);
}

// ============================================
// Carrinho (guardado na sessão PHP)
// ============================================

function chaveCarrinho(int $produtoId, ?string $tamanho): string {
    return $tamanho ? $produtoId . '_' . $tamanho : (string)$produtoId;
}

function analisarChaveCarrinho(string $chave): array {
    $partes = explode('_', $chave, 2);
    return ['produto_id' => (int)$partes[0], 'tamanho' => $partes[1] ?? null];
}

function carrinhoDetalhado(PDO $pdo): array {
    if (empty($_SESSION['carrinho'])) return [];

    $chaves = array_keys($_SESSION['carrinho']);
    $idsProdutos = array_unique(array_map(function ($chave) {
        return analisarChaveCarrinho($chave)['produto_id'];
    }, $chaves));

    $placeholders = implode(',', array_fill(0, count($idsProdutos), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders) AND ativo = 1");
    $stmt->execute($idsProdutos);
    $produtosPorId = [];
    foreach ($stmt->fetchAll() as $p) $produtosPorId[$p['id']] = $p;

    $resultado = [];
    foreach ($chaves as $chave) {
        $info = analisarChaveCarrinho($chave);
        $produto = $produtosPorId[$info['produto_id']] ?? null;
        if (!$produto) continue;

        $stockDisponivel = (int)$produto['stock'];
        if ($info['tamanho']) {
            $stockDisponivel = buscarStockTamanho($pdo, $info['produto_id'], $info['tamanho']) ?? 0;
        }

        $quantidade = min($_SESSION['carrinho'][$chave], $stockDisponivel);
        if ($quantidade <= 0) continue;

        $resultado[] = [
            'chave' => $chave,
            'produto' => $produto,
            'tamanho' => $info['tamanho'],
            'quantidade' => $quantidade,
            'subtotal' => $quantidade * $produto['preco'],
        ];
    }
    return $resultado;
}

function carrinhoTotalItens(): int {
    if (empty($_SESSION['carrinho'])) return 0;
    return array_sum($_SESSION['carrinho']);
}

function carrinhoTotalValor(array $itens): float {
    return array_sum(array_column($itens, 'subtotal'));
}

// ============================================
// Códigos de desconto
// ============================================

function validarCodigoDesconto(PDO $pdo, string $codigo): ?array {
    $codigo = mb_strtoupper(trim($codigo));
    if ($codigo === '') return null;

    $stmt = $pdo->prepare(
        "SELECT * FROM codigos_desconto
         WHERE codigo = :codigo AND ativo = 1 AND (validade IS NULL OR validade >= CURDATE())"
    );
    $stmt->execute(['codigo' => $codigo]);
    $resultado = $stmt->fetch();
    return $resultado ?: null;
}

// ============================================
// Encomendas
// ============================================

function criarEncomenda(PDO $pdo, array $dadosCliente, array $itensCarrinho, ?array $codigoDesconto): int {
    $subtotal = carrinhoTotalValor($itensCarrinho);
    $envio = calcularEnvio($pdo, $subtotal);
    $valorDesconto = $codigoDesconto ? round(($subtotal + $envio) * $codigoDesconto['percentagem'], 2) : 0;
    $total = max(0, $subtotal + $envio - $valorDesconto);

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare(
            "INSERT INTO encomendas
             (nome_cliente, email, telefone, morada, codigo_postal, cidade, metodo_pagamento, codigo_desconto_id, subtotal, valor_desconto, envio, total)
             VALUES (:nome, :email, :telefone, :morada, :codigo_postal, :cidade, :metodo_pagamento, :codigo_desconto_id, :subtotal, :valor_desconto, :envio, :total)"
        );
        $stmt->execute([
            'nome' => $dadosCliente['nome'],
            'email' => $dadosCliente['email'],
            'telefone' => $dadosCliente['telefone'],
            'morada' => $dadosCliente['morada'],
            'codigo_postal' => $dadosCliente['codigo_postal'],
            'cidade' => $dadosCliente['cidade'],
            'metodo_pagamento' => $dadosCliente['metodo_pagamento'],
            'codigo_desconto_id' => $codigoDesconto['id'] ?? null,
            'subtotal' => $subtotal,
            'valor_desconto' => $valorDesconto,
            'envio' => $envio,
            'total' => $total,
        ]);
        $encomendaId = (int)$pdo->lastInsertId();

        $stmtItem = $pdo->prepare(
            "INSERT INTO encomenda_itens (encomenda_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal, tamanho)
             VALUES (:encomenda_id, :produto_id, :nome_produto, :preco_unitario, :quantidade, :subtotal, :tamanho)"
        );
        $stmtStockProduto = $pdo->prepare("UPDATE produtos SET stock = stock - :qtd WHERE id = :id AND stock >= :qtd");
        $stmtStockTamanho = $pdo->prepare(
            "UPDATE produto_tamanhos SET stock = stock - :qtd WHERE produto_id = :id AND tamanho = :tamanho AND stock >= :qtd"
        );

        foreach ($itensCarrinho as $item) {
            $stmtItem->execute([
                'encomenda_id' => $encomendaId,
                'produto_id' => $item['produto']['id'],
                'nome_produto' => $item['produto']['nome'],
                'preco_unitario' => $item['produto']['preco'],
                'quantidade' => $item['quantidade'],
                'subtotal' => $item['subtotal'],
                'tamanho' => $item['tamanho'] ?? null,
            ]);

            if (!empty($item['tamanho'])) {
                $stmtStockTamanho->execute([
                    'qtd' => $item['quantidade'],
                    'id' => $item['produto']['id'],
                    'tamanho' => $item['tamanho'],
                ]);
            } else {
                $stmtStockProduto->execute(['qtd' => $item['quantidade'], 'id' => $item['produto']['id']]);
            }
        }

        $pdo->commit();
        return $encomendaId;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function buscarEncomendaCompleta(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM encomendas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $encomenda = $stmt->fetch();
    if (!$encomenda) return null;

    $stmtItens = $pdo->prepare("SELECT * FROM encomenda_itens WHERE encomenda_id = :id");
    $stmtItens->execute(['id' => $id]);
    $encomenda['itens'] = $stmtItens->fetchAll();
    return $encomenda;
}

function marcarEmailEnviado(PDO $pdo, int $encomendaId): void {
    $stmt = $pdo->prepare("UPDATE encomendas SET email_enviado = 1 WHERE id = :id");
    $stmt->execute(['id' => $encomendaId]);
}

// ============================================
// Envio (valores editáveis na tabela "definicoes")
// ============================================

// Lê os valores de envio da base de dados, com recurso às constantes de
// config.php caso a migração ainda não tenha sido corrida.
function buscarCustosEnvio(PDO $pdo): array {
    return [
        'gratis_acima_de' => (float)buscarDefinicao($pdo, 'envio_gratis_acima_de', (string)PORTES_GRATIS_ACIMA_DE),
        'custo' => (float)buscarDefinicao($pdo, 'envio_custo', (string)CUSTO_ENVIO),
    ];
}

// Calcula o custo de envio para um subtotal. O arredondamento a 2 casas
// decimais antes da comparação evita erros de vírgula flutuante do PHP
// (ex: 49,999999999996 em vez de 50,00 exato), que faziam o envio grátis
// às vezes não ativar mesmo estando no valor certo.
function calcularEnvio(PDO $pdo, float $subtotal): float {
    $custos = buscarCustosEnvio($pdo);
    $subtotalArredondado = round($subtotal, 2);
    return $subtotalArredondado >= $custos['gratis_acima_de'] ? 0.0 : $custos['custo'];
}

// ============================================
// Devoluções
// ============================================

// Procura a encomenda pelo número + email (confirma que é mesmo do cliente).
// Devolve a encomenda com os itens, cada um já a indicar se já tem uma devolução pedida.
function buscarEncomendaPorNumeroEmail(PDO $pdo, int $numero, string $email): ?array {
    $stmt = $pdo->prepare("SELECT * FROM encomendas WHERE id = :id AND email = :email");
    $stmt->execute(['id' => $numero, 'email' => $email]);
    $encomenda = $stmt->fetch();
    if (!$encomenda) return null;

    $stmtItens = $pdo->prepare(
        "SELECT ei.*, (SELECT COUNT(*) FROM devolucoes d WHERE d.encomenda_item_id = ei.id) AS ja_pedida
         FROM encomenda_itens ei WHERE ei.encomenda_id = :id"
    );
    $stmtItens->execute(['id' => $numero]);
    $encomenda['itens'] = $stmtItens->fetchAll();
    return $encomenda;
}

// Cria os pedidos de devolução para os itens escolhidos (um pedido por item).
function criarDevolucao(PDO $pdo, int $encomendaId, array $itensIds, string $motivo): int {
    $stmt = $pdo->prepare(
        "INSERT INTO devolucoes (encomenda_id, encomenda_item_id, motivo) VALUES (:encomenda_id, :item_id, :motivo)"
    );
    $criados = 0;
    foreach ($itensIds as $itemId) {
        $stmt->execute(['encomenda_id' => $encomendaId, 'item_id' => (int)$itemId, 'motivo' => $motivo]);
        $criados++;
    }
    return $criados;
}

// ============================================
// Chat privado (cliente ↔ Northside)
// ============================================

function criarConversaChat(PDO $pdo, string $nome, string $email): int {
    $stmt = $pdo->prepare("INSERT INTO chat_conversas (nome, email) VALUES (:nome, :email)");
    $stmt->execute(['nome' => $nome, 'email' => $email]);
    return (int)$pdo->lastInsertId();
}

function buscarConversaChat(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM chat_conversas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $conversa = $stmt->fetch();
    return $conversa ?: null;
}

function criarMensagemChat(PDO $pdo, int $conversaId, string $remetente, string $mensagem): void {
    $stmt = $pdo->prepare(
        "INSERT INTO chat_mensagens (conversa_id, remetente, mensagem) VALUES (:conversa_id, :remetente, :mensagem)"
    );
    $stmt->execute(['conversa_id' => $conversaId, 'remetente' => $remetente, 'mensagem' => $mensagem]);

    $stmtAtividade = $pdo->prepare("UPDATE chat_conversas SET ultima_atividade = NOW() WHERE id = :id");
    $stmtAtividade->execute(['id' => $conversaId]);
}

// Mensagens de uma conversa, opcionalmente só as que vieram depois de um certo ID
// (usado para o chat ir buscar só as mensagens novas, em vez de tudo outra vez).
function buscarMensagensChat(PDO $pdo, int $conversaId, int $depoisDeId = 0): array {
    $stmt = $pdo->prepare(
        "SELECT * FROM chat_mensagens WHERE conversa_id = :conversa_id AND id > :depois_de ORDER BY id ASC"
    );
    $stmt->execute(['conversa_id' => $conversaId, 'depois_de' => $depoisDeId]);
    return $stmt->fetchAll();
}
