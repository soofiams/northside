<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'avaliacoes';
$titulo_pagina = 'Avaliações';

$avaliacoes = buscarAvaliacoes($pdo);
$resumo = mediaAvaliacoesGeral($pdo);
$produtosParaSelect = buscarProdutos($pdo);

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">AVALIAÇÕES DOS CLIENTES</h2>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="resumo-avaliacoes">
    <div class="resumo-nota"><?= number_format($resumo['media'], 1, ',', '.') ?></div>
    <div>
        <div class="estrelas" style="font-size:1.3rem;"><?= estrelasHtml($resumo['media']) ?></div>
        <p>Com base em <?= $resumo['total'] ?> avaliações verificadas</p>
    </div>
</div>

<div style="text-align:center;margin-bottom:10px;">
    <button type="button" class="btn-northside" data-abrir-modal="modal-avaliar">DEIXA A TUA AVALIAÇÃO</button>
</div>

<div class="avaliacoes-wrap" style="margin-top:0;">
    <div class="avaliacoes-grid" style="max-width:1100px;">
        <?php foreach ($avaliacoes as $av): ?>
            <div class="avaliacao-mini">
                <div class="estrelas"><?= estrelasHtml($av['estrelas']) ?></div>
                <p>"<?= htmlspecialchars($av['comentario']) ?>"</p>
                <div class="nome-mini">— <?= htmlspecialchars($av['nome_cliente']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal: deixar uma avaliação -->
<div class="modal-overlay" id="modal-avaliar">
    <div class="modal-caixa" style="max-width:440px;text-align:left;">
        <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
        <h3 style="text-align:center;">Deixa a tua Avaliação</h3>
        <p style="text-align:center;color:var(--cinza-texto);font-size:0.85rem;margin-bottom:18px;">A tua opinião ajuda outros clientes a escolher melhor.</p>

        <form action="actions/avaliacao_criar.php" method="post">
            <input type="hidden" name="voltar" value="avaliacoes.php">

            <label style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin-bottom:6px;">Sobre qual produto? (opcional)</label>
            <select name="produto_id" style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-size:0.9rem;">
                <option value="">Avaliação geral da loja</option>
                <?php foreach ($produtosParaSelect as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <label style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin:14px 0 6px;">Classificação</label>
            <div class="estrelas-input" id="estrelas-input">
                <span data-valor="1">★</span>
                <span data-valor="2">★</span>
                <span data-valor="3">★</span>
                <span data-valor="4">★</span>
                <span data-valor="5">★</span>
            </div>
            <input type="hidden" name="estrelas" id="input-estrelas" value="5">

            <label for="modal-avaliar-nome" style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin:14px 0 6px;">O teu nome</label>
            <input type="text" name="nome" id="modal-avaliar-nome" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;">

            <label for="modal-avaliar-comentario" style="display:block;font-weight:700;font-size:0.85rem;color:var(--azul-northside);margin:14px 0 6px;">O teu comentário</label>
            <textarea name="comentario" id="modal-avaliar-comentario" rows="3" required style="width:100%;padding:10px 12px;border:1px solid var(--border);border-radius:4px;font-family:inherit;"></textarea>

            <button type="submit" class="btn-northside" style="width:100%;margin-top:16px;">ENVIAR AVALIAÇÃO</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
