<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$produto = buscarProdutoPorId($pdo, $id);

if (!$produto) {
    http_response_code(404);
    $titulo_pagina = 'Produto não encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<div class="carrinho-vazio">Este produto não existe ou já não está disponível.<br><br><a href="loja.php" class="btn-northside">Voltar à loja</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$titulo_pagina = $produto['nome'];
$stock = (int)$produto['stock'];
$especificacoes = json_decode($produto['especificacoes'] ?? '{}', true) ?: [];
$relacionados = buscarProdutosRelacionados($pdo, $produto);
$tamanhos = buscarTamanhosProduto($pdo, $produto['id']);
$temTamanhos = !empty($tamanhos);
$avaliacoesProduto = buscarAvaliacoesProduto($pdo, $produto['id']);

require __DIR__ . '/includes/header.php';
?>

<?php if (!empty($_GET['erro'])): ?>
    <div class="alerta alerta-erro"><?= htmlspecialchars($_GET['erro']) ?></div>
<?php endif; ?>
<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="produto-detalhe">
    <div class="img-principal">
        <img src="<?= imagemProdutoUrl($produto['imagem']) ?>" alt="<?= htmlspecialchars($produto['nome']) ?>">
    </div>

    <div class="info">
        <div class="categoria-label"><?= htmlspecialchars(mb_strtoupper($produto['categoria_nome'] ?? '')) ?></div>
        <h1><?= htmlspecialchars($produto['nome']) ?></h1>
        <div class="estrelas" style="margin-bottom:10px;">
            <?= estrelasHtml($produto['estrelas_media']) ?>
            <span class="num"><?= number_format($produto['estrelas_media'], 1, ',', '.') ?> · <?= (int)$produto['num_avaliacoes'] ?> avaliações</span>
        </div>

        <?php if (!$temTamanhos): ?>
            <?php if ($stock === 0): ?>
                <span class="badge-stock esgotado">● Esgotado</span>
            <?php elseif ($stock <= 5): ?>
                <span class="badge-stock pouco-stock">● Últimas <?= $stock ?> unidades em stock</span>
            <?php else: ?>
                <span class="badge-stock em-stock">● Em stock (<?= $stock ?> disponíveis)</span>
            <?php endif; ?>
        <?php endif; ?>

        <div class="preco-grande"><?= formatarPreco($produto['preco']) ?></div>

        <?php if ($temTamanhos): ?>
            <div class="tamanhos-wrap">
                <label>Tamanho</label>
                <div class="tamanhos-selector" id="tamanhos-selector">
                    <?php foreach ($tamanhos as $t): $disponivel = (int)$t['stock'] > 0; ?>
                        <div class="tamanho-pill <?= $disponivel ? '' : 'indisponivel' ?>"
                             data-tamanho="<?= htmlspecialchars($t['tamanho']) ?>"
                             title="<?= $disponivel ? (int)$t['stock'] . ' disponíveis' : 'Indisponível' ?>">
                            <?= htmlspecialchars($t['tamanho']) ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="aviso-tamanho" id="aviso-tamanho">Escolhe um tamanho antes de adicionar ao carrinho.</div>
            </div>
        <?php endif; ?>

        <?php if ($stock > 0 || $temTamanhos): ?>
            <form action="actions/carrinho_add.php" method="post" class="form-comprar" id="form-produto-principal">
                <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">
                <input type="hidden" name="voltar" value="produto.php?id=<?= $produto['id'] ?>">
                <input type="hidden" name="tamanho" id="input-tamanho-selecionado" value="">
                <input type="number" name="quantidade" value="1" min="1" max="<?= $stock > 0 ? $stock : 99 ?>" class="input-qtd">
                <button type="submit" class="btn-northside">ADICIONAR AO CARRINHO</button>
            </form>
        <?php else: ?>
            <button class="btn-northside" disabled style="opacity:0.5;cursor:not-allowed;">PRODUTO ESGOTADO</button>
        <?php endif; ?>

        <p class="descricao-produto"><?= nl2br(htmlspecialchars($produto['descricao'])) ?></p>

        <?php if (!empty($especificacoes)): ?>
            <table class="tabela-especificacoes">
                <?php foreach ($especificacoes as $chave => $valor): ?>
                    <tr><th><?= htmlspecialchars($chave) ?></th><td><?= htmlspecialchars($valor) ?></td></tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($relacionados)): ?>
<h2 class="secao-titulo">PRODUTOS RELACIONADOS</h2>
<div class="grid-produtos grid-relacionados">
    <?php foreach ($relacionados as $rel): $stockRel = (int)$rel['stock']; ?>
        <div class="cartao-produto">
            <a href="produto.php?id=<?= $rel['id'] ?>">
                <div class="img-wrap">
                    <img class="img-ativa" src="<?= imagemProdutoUrl($rel['imagem']) ?>" alt="<?= htmlspecialchars($rel['nome']) ?>">
                </div>
            </a>
            <div class="corpo">
                <div class="categoria-label"><?= htmlspecialchars(mb_strtoupper($rel['categoria_nome'] ?? '')) ?></div>
                <a href="produto.php?id=<?= $rel['id'] ?>"><h3><?= htmlspecialchars($rel['nome']) ?></h3></a>
                <div class="estrelas"><?= estrelasHtml($rel['estrelas_media']) ?> <span class="num">(<?= (int)$rel['num_avaliacoes'] ?>)</span></div>
                <div class="linha-preco">
                    <span class="preco"><?= formatarPreco($rel['preco']) ?></span>
                    <?php if ($stockRel > 0): ?>
                        <form action="actions/carrinho_add.php" method="post">
                            <input type="hidden" name="produto_id" value="<?= $rel['id'] ?>">
                            <input type="hidden" name="voltar" value="produto.php?id=<?= $produto['id'] ?>">
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

<?php if (!empty($avaliacoesProduto)): ?>
<h2 class="secao-titulo">AVALIAÇÕES DESTE PRODUTO</h2>
<div class="avaliacoes-wrap" style="margin-top:0;">
    <div class="avaliacoes-grid" style="max-width:1000px;">
        <?php foreach ($avaliacoesProduto as $av): ?>
            <div class="avaliacao-mini">
                <div class="estrelas"><?= estrelasHtml($av['estrelas']) ?></div>
                <p>"<?= htmlspecialchars($av['comentario']) ?>"</p>
                <div class="nome-mini">— <?= htmlspecialchars($av['nome_cliente']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div id="avaliar" class="avaliacao-form-wrap">
    <h3>Já compraste este produto? Deixa a tua avaliação</h3>
    <p class="aviso">A tua opinião ajuda outros clientes a escolher melhor.</p>
    <form action="actions/avaliacao_criar.php" method="post">
        <input type="hidden" name="produto_id" value="<?= $produto['id'] ?>">

        <label style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin-bottom:6px;">Classificação</label>
        <div class="estrelas-input" id="estrelas-input">
            <span data-valor="1">★</span>
            <span data-valor="2">★</span>
            <span data-valor="3">★</span>
            <span data-valor="4">★</span>
            <span data-valor="5">★</span>
        </div>
        <input type="hidden" name="estrelas" id="input-estrelas" value="5">

        <label for="avaliacao-nome" style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin:14px 0 6px;">O teu nome</label>
        <input type="text" name="nome" id="avaliacao-nome" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;">

        <label for="avaliacao-comentario" style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin:14px 0 6px;">O teu comentário</label>
        <textarea name="comentario" id="avaliacao-comentario" rows="3" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-family:inherit;"></textarea>

        <button type="submit" class="btn-northside" style="margin-top:16px;">ENVIAR AVALIAÇÃO</button>
    </form>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
