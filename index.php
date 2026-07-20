<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'inicio';
$titulo_pagina = 'Início';
$destaques = buscar_produtos($pdo, ['destaque' => true, 'limite' => 6]);

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<section class="hero">
    <h1>NORTHSIDE</h1>
    <div class="slogan">WEAR YOU STORY</div>
    <a href="loja.php" class="btn-northside">VER PRODUTOS</a>
</section>

<section class="vantagens">
    <div class="vantagem">
        <span class="icon">🚚</span>
        <div><strong>ENVIO RÁPIDO</strong><span>1-3 dias úteis</span></div>
    </div>
    <div class="vantagem">
        <span class="icon">🛡️</span>
        <div><strong>PAGAMENTOS SEGUROS</strong><span>100% protegido</span></div>
    </div>
    <div class="vantagem">
        <span class="icon">🎧</span>
        <div><strong>APOIO AO CLIENTE</strong><span>24/7 disponível</span></div>
    </div>
    <div class="vantagem">
        <span class="icon">📦</span>
        <div><strong>DEVOLUÇÕES FÁCEIS</strong><span>30 dias para devolver</span></div>
    </div>
</section>

<h2 class="secao-titulo">DESTAQUES</h2>

<div class="grid-produtos">
    <?php foreach ($destaques as $produto): ?>
        <div class="cartao-produto">
            <a href="produto.php?id=<?= $produto['id'] ?>">
                <div class="img-wrap">
                    <img src="<?= imagem_produto_url($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
                </div>
            </a>
            <div class="corpo">
                <div class="categoria-label"><?= htmlspecialchars($produto['categoria_nome'] ?? '') ?></div>
                <a href="produto.php?id=<?= $produto['id'] ?>">
                    <h3><?= htmlspecialchars($produto['nome']) ?></h3>
                </a>
                <div class="linha-preco">
                    <span class="preco"><?= formatar_preco($produto['preco']) ?></span>
                    <?php if ($produto['stock'] > 0): ?>
                        <form action="actions/carrinho_add.php" method="post">
                            <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                            <input type="hidden" name="voltar" value="index.php">
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

<?php require __DIR__ . '/includes/footer.php'; ?>