<?php
/**
 * Funções auxiliares da loja
 */

function formatar_preco(float $valor): string {
    return number_format($valor, 2, ',', '.') . '€';
}

function buscar_categorias(PDO $pdo): array {
    return $pdo->query("SELECT * FROM categorias ORDER BY nome")->fetchAll();
}

function buscar_produtos(PDO $pdo, array $filtros = []): array {
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

function buscar_produto_por_id(PDO $pdo, int $id): ?array {
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

function imagem_produto_url(?string $imagem): string {
    if (empty($imagem)) $imagem = 'sem-imagem.jpg';
    $caminho = URL_BASE . 'assets/img/produtos/' . $imagem;
    return $caminho;
}

// Devolve os produtos completos que estão atualmente no carrinho, com quantidade
function carrinho_detalhado(PDO $pdo): array {
    if (empty($_SESSION['carrinho'])) return [];

    $ids = array_keys($_SESSION['carrinho']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id IN ($placeholders)");
    $stmt->execute($ids);
    $produtos = $stmt->fetchAll();

    $resultado = [];
    foreach ($produtos as $produto) {
        $quantidade = $_SESSION['carrinho'][$produto['id']];
        // nunca deixar a quantidade no carrinho ultrapassar o stock disponível
        $quantidade = min($quantidade, (int)$produto['stock']);
        $resultado[] = [
            'produto' => $produto,
            'quantidade' => $quantidade,
            'subtotal' => $quantidade * $produto['preco'],
        ];
    }
    return $resultado;
}

function carrinho_total_valor(array $itens): float {
    return array_sum(array_column($itens, 'subtotal'));
}
