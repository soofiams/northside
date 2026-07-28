<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'devolucoes';
$titulo_pagina = 'Devoluções';

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">DEVOLUÇÕES</h2>

<div class="devolucoes-wrap">

    <!-- Passo 1: identificar a encomenda -->
    <div id="devolucao-passo-1" class="devolucao-caixa">
        <p class="devolucao-intro">Introduz o número da tua encomenda e o email que usaste na compra — vais encontrar o número no email de confirmação.</p>

        <label for="devolucao-numero">Número da encomenda</label>
        <input type="text" id="devolucao-numero" placeholder="Ex: 12">

        <label for="devolucao-email">Email da encomenda</label>
        <input type="email" id="devolucao-email" placeholder="o-teu-email@exemplo.com">

        <div class="devolucao-msg" id="devolucao-msg-passo-1"></div>

        <button type="button" class="btn-northside" id="btn-procurar-encomenda" style="margin-top:16px;">PROCURAR ENCOMENDA</button>
    </div>

    <!-- Passo 2: escolher os artigos e o motivo (aparece depois de encontrar a encomenda) -->
    <div id="devolucao-passo-2" class="devolucao-caixa" style="display:none;">
        <p class="devolucao-intro">Encomenda <strong id="devolucao-numero-confirmado"></strong> — escolhe o que queres devolver:</p>

        <div id="devolucao-lista-itens"></div>

        <label for="devolucao-motivo">Motivo da devolução</label>
        <textarea id="devolucao-motivo" rows="3" placeholder="Conta-nos o que se passou (ex: tamanho errado, produto com defeito...)"></textarea>

        <div class="devolucao-msg" id="devolucao-msg-passo-2"></div>

        <button type="button" class="btn-northside" id="btn-confirmar-devolucao" style="margin-top:16px;">PEDIR DEVOLUÇÃO</button>
        <button type="button" class="btn-outline-northside" id="btn-devolucao-voltar" style="margin-top:16px;">VOLTAR</button>
    </div>

    <!-- Confirmação -->
    <div id="devolucao-passo-3" class="devolucao-caixa" style="display:none;text-align:center;">
        <div class="modal-confirmacao" style="display:block;">
            ✓ Pedido de devolução enviado!<br>
            A nossa equipa vai analisar e entra em contacto por email nos próximos dias úteis.
        </div>
        <a href="index.php" class="btn-northside" style="margin-top:16px;display:inline-block;">VOLTAR AO INÍCIO</a>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
