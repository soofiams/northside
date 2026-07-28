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

// Produtos relacionados: até 3 da mesma categoria, excluindo o atual;
// se não houver 3, completa com outros produtos ativos.
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
// Avaliações
// ============================================

function buscarAvaliacoes(PDO $pdo, ?int $limite = null): array {
    $sql = "SELECT * FROM avaliacoes WHERE aprovado = 1 ORDER BY criado_em DESC";
    if ($limite) $sql .= " LIMIT " . (int)$limite;
    return $pdo->query($sql)->fetchAll();
}

function mediaAvaliacoesGeral(PDO $pdo): array {
    $r = $pdo->query("SELECT AVG(estrelas) AS media, COUNT(*) AS total FROM avaliacoes WHERE aprovado = 1")->fetch();
    return ['media' => round((float)$r['media'], 1), 'total' => (int)$r['total']];
}

// ============================================
// Carrinho (guardado na sessão PHP)
// ============================================

function carrinhoDetalhado(PDO $pdo): array {
    if (empty($_SESSION['carrinho'])) return [];

    $ids = array_keys($_SESSION['carrinho']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders) AND ativo = 1");
    $stmt->execute($ids);
    $produtos = $stmt->fetchAll();

    $resultado = [];
    foreach ($produtos as $produto) {
        $quantidade = min($_SESSION['carrinho'][$produto['id']], (int)$produto['stock']);
        if ($quantidade <= 0) continue;
        $resultado[] = [
            'produto' => $produto,
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

// Devolve o código (com percentagem) se for válido, ou null caso contrário.
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

// Cria a encomenda + os seus itens, e diminui o stock. Devolve o ID da encomenda.
function criarEncomenda(PDO $pdo, array $dadosCliente, array $itensCarrinho, ?array $codigoDesconto): int {
    $subtotal = carrinhoTotalValor($itensCarrinho);
    $envio = $subtotal >= PORTES_GRATIS_ACIMA_DE ? 0 : CUSTO_ENVIO;
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
            "INSERT INTO encomenda_itens (encomenda_id, produto_id, nome_produto, preco_unitario, quantidade, subtotal)
             VALUES (:encomenda_id, :produto_id, :nome_produto, :preco_unitario, :quantidade, :subtotal)"
        );
        $stmtStock = $pdo->prepare("UPDATE produtos SET stock = stock - :qtd WHERE id = :id AND stock >= :qtd");

        foreach ($itensCarrinho as $item) {
            $stmtItem->execute([
                'encomenda_id' => $encomendaId,
                'produto_id' => $item['produto']['id'],
                'nome_produto' => $item['produto']['nome'],
                'preco_unitario' => $item['produto']['preco'],
                'quantidade' => $item['quantidade'],
                'subtotal' => $item['subtotal'],
            ]);
            $stmtStock->execute(['qtd' => $item['quantidade'], 'id' => $item['produto']['id']]);
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
