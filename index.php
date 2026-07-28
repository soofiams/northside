<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'inicio';
$titulo_pagina = 'Início';

$destaques = buscarProdutos($pdo, ['destaque' => true, 'limite' => 7]);
$avaliacoes = buscarAvaliacoes($pdo, 3);

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<section class="hero">
    <div class="hero-texto">
        <h1>Your Powered Life.</h1>
        <a href="loja.php" class="btn-northside" style="margin-top:18px;display:inline-block;">DESCOBRIR A NORTHSIDE</a>
    </div>
</section>

<section class="vantagens">
    <div class="vantagem"><span class="icon">🚚</span>Envio em 1-3 dias úteis</div>
    <div class="vantagem"><span class="icon">🛡️</span>Pagamento 100% seguro</div>
    <div class="vantagem"><span class="icon">🎧</span>Apoio ao cliente 24/7</div>
    <div class="vantagem"><span class="icon">📦</span>30 dias para devolver</div>
</section>

<div class="newsletter-faixa">
    <div class="newsletter-faixa-texto">
        <strong>Poupa 10% na primeira compra</strong>
        <span>Subscreve a nossa newsletter e recebe já o código de desconto.</span>
    </div>
    <div class="newsletter-faixa-form">
        <input type="email" placeholder="O teu email" id="newsletter-faixa-email">
        <button type="button" data-abrir-modal="modal-newsletter">Subscrever</button>
    </div>
</div>

<h2 class="secao-titulo">DESTAQUES</h2>

<div class="grid-produtos">
    <?php foreach ($destaques as $produto): $stock = (int)$produto['stock']; ?>
        <div class="cartao-produto">
            <a href="produto.php?id=<?= $produto['id'] ?>">
                <div class="img-wrap">
                    <img class="img-ativa" src="<?= imagemProdutoUrl($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                </div>
            </a>
            <div class="corpo">
                <div class="categoria-label"><?= htmlspecialchars(mb_strtoupper($produto['categoria_nome'] ?? '')) ?></div>
                <a href="produto.php?id=<?= $produto['id'] ?>"><h3><?= htmlspecialchars($produto['nome']) ?></h3></a>
                <div class="estrelas"><?= estrelasHtml($produto['estrelas_media']) ?> <span class="num">(<?= (int)$produto['num_avaliacoes'] ?>)</span></div>
                <div class="linha-preco">
                    <span class="preco"><?= formatarPreco($produto['preco']) ?></span>
                    <?php if ($stock > 0): ?>
                        <form action="actions/carrinho_add.php" method="post">
                            <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                            <input type="hidden" name="voltar" value="index.php">
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

<section class="avaliacoes-wrap">
    <h2 class="secao-titulo" style="margin-top:0;">AVALIAÇÕES</h2>
    <div class="avaliacoes-grid">
        <?php foreach ($avaliacoes as $av): ?>
            <div class="avaliacao-mini">
                <div class="estrelas"><?= estrelasHtml($av['estrelas']) ?></div>
                <p>"<?= htmlspecialchars($av['comentario']) ?>"</p>
                <div class="nome-mini">— <?= htmlspecialchars($av['nome_cliente']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:20px;">
        <a href="avaliacoes.php" class="btn-outline-northside">VER TODAS AS AVALIAÇÕES</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
