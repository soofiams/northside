<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'loja';

$filtros = [];
if (!empty($_GET['categoria'])) $filtros['categoria_slug'] = $_GET['categoria'];
if (!empty($_GET['pesquisa'])) $filtros['pesquisa'] = $_GET['pesquisa'];

$produtos = buscarProdutos($pdo, $filtros);

$titulo_pagina = 'Loja';
$titulo_secao = 'TODOS OS PRODUTOS';
if (!empty($filtros['pesquisa'])) {
    $titulo_secao = 'RESULTADOS PARA "' . mb_strtoupper($filtros['pesquisa']) . '"';
} elseif (!empty($filtros['categoria_slug'])) {
    foreach (buscarCategorias($pdo) as $c) {
        if ($c['slug'] === $filtros['categoria_slug']) $titulo_secao = mb_strtoupper($c['nome']);
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="newsletter-faixa">
    <div class="newsletter-faixa-texto">
        <strong>Poupa <?= round((float)buscarDefinicao($pdo, 'newsletter_desconto_percentagem', '0.10') * 100) ?>% na primeira compra</strong>
        <span>Subscreve a nossa newsletter e recebe já o código de desconto.</span>
    </div>
    <div class="newsletter-faixa-form">
        <button type="button" data-abrir-modal="modal-newsletter">Subscrever</button>
    </div>
</div>

<h2 class="secao-titulo" id="titulo-loja"><?= htmlspecialchars($titulo_secao) ?></h2>

<?php if (empty($produtos)): ?>
    <div class="carrinho-vazio">
        Não encontrámos produtos que correspondam à tua pesquisa.<br><br>
        <a href="loja.php" class="btn-northside">VER TODOS OS PRODUTOS</a>
    </div>
<?php else: ?>
    <div class="grid-produtos" id="grid-loja">
        <?php foreach ($produtos as $produto): $stock = (int)$produto['stock']; ?>
            <div class="cartao-produto">
                <a href="produto.php?id=<?= $produto['id'] ?>">
                    <div class="img-wrap">
                        <img class="img-ativa" src="<?= imagemProdutoUrl($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                    </div>
                </a>
                <div class="corpo">
                    <div class="categoria-label"><?= htmlspecialchars(mb_strtoupper($produto['categoria_nome'] ?? '')) ?></div>
                    <a href="produto.php?id=<?= $produto['id'] ?>">
                        <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                    </a>
                    <div class="estrelas"><?= estrelasHtml($produto['estrelas_media']) ?> <span class="num">(<?= (int)$produto['num_avaliacoes'] ?>)</span></div>
                    <div class="linha-preco">
                        <span class="preco"><?= formatarPreco($produto['preco']) ?></span>
                        <?php if ($stock > 0): ?>
                            <form action="actions/carrinho_add.php" method="post">
                                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                                <input type="hidden" name="voltar" value="loja.php<?= !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : '' ?>">
                                <button type="submit" class="btn-add-cart" title="Adicionar ao carrinho">+</button>
                            </form>
                        <?php else: ?>
                            <span class="rotulo-esgotado">ESGOTADO</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>