<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'loja';

$filtros = [];
if (!empty($_GET['categoria'])) $filtros['categoria_slug'] = $_GET['categoria'];
if (!empty($_GET['pesquisa'])) $filtros['pesquisa'] = $_GET['pesquisa'];

$produtos = buscar_produtos($pdo, $filtros);

$titulo_pagina = 'Loja';
$nome_categoria_atual = null;
if (!empty($filtros['categoria_slug'])) {
    foreach (buscar_categorias($pdo) as $c) {
        if ($c['slug'] === $filtros['categoria_slug']) $nome_categoria_atual = $c['nome'];
    }
}

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">
    <?php if ($nome_categoria_atual): ?>
        <?= htmlspecialchars(mb_strtoupper($nome_categoria_atual)) ?>
    <?php elseif (!empty($filtros['pesquisa'])): ?>
        RESULTADOS PARA "<?= htmlspecialchars($filtros['pesquisa']) ?>"
    <?php else: ?>
        TODOS OS PRODUTOS
    <?php endif; ?>
</h2>

<?php if (empty($produtos)): ?>
    <div class="carrinho-vazio">Não encontrámos produtos com esses critérios.</div>
<?php else: ?>
<div class="grid-produtos">
    <?php foreach ($produtos as $produto): ?>
        <div class="cartao-produto">
            <a href="produto.php?id=<?= $produto['id'] ?>">
                <div class="img-wrap">
                    <img src="<?= imagem_produto_url($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                </div>
            </a>
            <div class="corpo">
                <div class="categoria-label"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></div>
                <a href="produto.php?id=<?= $produto['id'] ?>"><h3><?= htmlspecialchars($produto['nome']) ?></h3></a>
                <div class="linha-preco">
                    <span class="preco"><?= formatar_preco($produto['preco']) ?></span>
                    <?php if ($produto['stock'] > 0): ?>
                        <form action="actions/carrinho_add.php" method="post">
                            <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                            <input type="hidden" name="voltar" value="loja.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>">
                            <button type="submit" class="btn-add-cart" title="Adicionar ao carrinho">+</button>
                        </form>
                    <?php else: ?>
                        <button class="btn-add-cart" disabled title="Esgotado">×</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
