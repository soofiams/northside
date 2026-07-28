<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$pagina_atual = 'acompanhar';
$titulo_pagina = 'Acompanhar Encomenda';

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">ACOMPANHAR ENCOMENDA</h2>

<div class="devolucoes-wrap">

    <div id="acompanhar-passo-1" class="devolucao-caixa">
        <p class="devolucao-intro">Introduz o número da tua encomenda e o email que usaste na compra — vais encontrar o número no email de confirmação.</p>

        <label for="acompanhar-numero">Número da encomenda</label>
        <input type="text" id="acompanhar-numero" placeholder="Ex: 12">

        <label for="acompanhar-email">Email da encomenda</label>
        <input type="email" id="acompanhar-email" placeholder="o-teu-email@exemplo.com">

        <div class="devolucao-msg" id="acompanhar-msg"></div>

        <button type="button" class="btn-northside" id="btn-consultar-encomenda" style="margin-top:16px;">CONSULTAR ENCOMENDA</button>
    </div>

    <div id="acompanhar-resultado" class="devolucao-caixa" style="display:none;">
        <p class="devolucao-intro">Encomenda <strong id="acompanhar-numero-confirmado"></strong> — feita a <span id="acompanhar-data"></span></p>

        <div id="acompanhar-cancelada-aviso" class="alerta alerta-erro" style="display:none;">Esta encomenda foi cancelada.</div>

        <div id="acompanhar-progresso" class="estado-encomenda-track">
            <div class="estado-passo" data-estado="confirmada">
                <div class="linha"></div>
                <div class="circulo"><i class="fa-solid fa-check"></i></div>
                <div class="rotulo">Confirmada</div>
            </div>
            <div class="estado-passo" data-estado="enviada">
                <div class="linha"></div>
                <div class="circulo"><i class="fa-solid fa-truck"></i></div>
                <div class="rotulo">Enviada</div>
            </div>
            <div class="estado-passo" data-estado="entregue">
                <div class="linha"></div>
                <div class="circulo"><i class="fa-solid fa-box-open"></i></div>
                <div class="rotulo">Entregue</div>
            </div>
        </div>

        <div id="acompanhar-lista-itens" style="margin-top:24px;"></div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);font-size:0.85rem;color:var(--cinza-texto);">
            <p style="margin:0 0 6px;"><strong>Total:</strong> <span id="acompanhar-total"></span></p>
            <p style="margin:0 0 6px;"><strong>Pagamento:</strong> <span id="acompanhar-pagamento"></span></p>
            <p style="margin:0;"><strong>Entrega em:</strong> <span id="acompanhar-morada"></span></p>
        </div>

        <button type="button" class="btn-outline-northside" id="btn-acompanhar-voltar" style="margin-top:20px;">CONSULTAR OUTRA ENCOMENDA</button>
    </div>

</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
