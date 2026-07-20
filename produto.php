<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$produto = buscar_produto_por_id($pdo, $id);

if (!$produto) {
    http_response_code(404);
    $titulo_pagina = 'Produto não encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<div class="carrinho-vazio">Este produto não existe ou já não está disponível. <br><a href="loja.php" class="btn-northside" style="margin-top:16px;display:inline-block;">Voltar à loja</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$titulo_pagina = $produto['nome'];
$stock = (int)$produto['stock'];

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="produto-detalhe">
    <div class="img-principal">
        <img src="<?= imagem_produto_url($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
    </div>

    <div class="info">
        <div class="categoria-label"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></div>
        <h1><?= htmlspecialchars($produto['nome']) ?></h1>

        <?php if ($stock === 0): ?>
            <span class="badge-stock esgotado">● Esgotado</span>
        <?php elseif ($stock <= 5): ?>
            <span class="badge-stock pouco-stock">● Últimas <?= $stock ?> unidades em stock</span>
        <?php else: ?>
            <span class="badge-stock em-stock">● Em stock (<?= $stock ?> disponíveis)</span>
        <?php endif; ?>

        <div class="preco-grande"><?= formatar_preco($produto['preco']) ?></div>

        <?php if ($stock > 0): ?>
            <form action="actions/carrinho_add.php" method="post" class="form-comprar">
                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                <input type="hidden" name="voltar" value="produto.php?id=<?= $produto['id'] ?>">
                <input type="number" name="quantidade" value="1" min="1" max="<?= $stock ?>" class="input-qtd">
                <button type="submit" class="btn-northside">ADICIONAR AO CARRINHO</button>
            </form>
        <?php else: ?>
            <button class="btn-northside" disabled style="opacity:0.5;cursor:not-allowed;">PRODUTO ESGOTADO</button>
        <?php endif; ?>

        <p class="descricao-produto"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
