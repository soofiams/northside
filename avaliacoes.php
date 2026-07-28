<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'avaliacoes';
$titulo_pagina = 'Avaliações';

$avaliacoes = buscarAvaliacoes($pdo);
$resumo = mediaAvaliacoesGeral($pdo);

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">AVALIAÇÕES DOS CLIENTES</h2>

<div class="resumo-avaliacoes">
    <div class="resumo-nota"><?= number_format($resumo['media'], 1, ',', '.') ?></div>
    <div>
        <div class="estrelas" style="font-size:1.3rem;"><?= estrelasHtml($resumo['media']) ?></div>
        <p>Com base em <?= $resumo['total'] ?> avaliações verificadas</p>
    </div>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
