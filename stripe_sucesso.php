<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/email.php';

$titulo_pagina = 'Encomenda Confirmada';
$sessionId = trim($_GET['session_id'] ?? '');

$erro = '';
$encomendaId = null;
$emailEnviado = false;
$totalFormatado = '';
$emailCliente = '';

if ($sessionId === '') {
    $erro = 'Sessão de pagamento inválida.';
} else {
    // Se esta sessão já tiver sido processada antes (ex: o cliente atualizou a página), não duplica a encomenda
    $existente = buscarEncomendaPorStripeSession($pdo, $sessionId);

    if ($existente) {
        $encomendaId = $existente['id'];
        $encomendaCompleta = buscarEncomendaCompleta($pdo, $encomendaId);
        $totalFormatado = formatarPreco($encomendaCompleta['total']);
        $emailCliente = $encomendaCompleta['email'];
        $emailEnviado = (bool)$encomendaCompleta['email_enviado'];
    } else {
        $caminhoAutoload = __DIR__ . '/vendor/autoload.php';
        if (!file_exists($caminhoAutoload)) {
            $erro = 'Não foi possível confirmar o pagamento (Stripe não está instalada no servidor).';
        } else {
            require_once $caminhoAutoload;
            try {
                \Stripe\Stripe::setApiKey(STRIPE_CHAVE_SECRETA);
                $session = \Stripe\Checkout\Session::retrieve($sessionId);

                if ($session->payment_status !== 'paid') {
                    $erro = 'O pagamento ainda não foi confirmado. Se acabaste de pagar, aguarda uns segundos e atualiza a página.';
                } elseif (empty($_SESSION['checkout_pendente'])) {
                    $erro = 'Não encontrámos os dados da tua encomenda nesta sessão. Se o pagamento foi feito, contacta-nos com o número da transação: ' . htmlspecialchars($sessionId);
                } else {
                    $dadosCliente = $_SESSION['checkout_pendente'];
                    $itens = carrinhoDetalhado($pdo);

                    if (empty($itens)) {
                        $erro = 'O carrinho já estava vazio ao confirmar o pagamento. Se foste cobrado, contacta-nos com o número da transação: ' . htmlspecialchars($sessionId);
                    } else {
                        $codigoDesconto = null;
                        if (!empty($dadosCliente['codigo_desconto_texto'])) {
                            $codigoDesconto = validarCodigoDesconto($pdo, $dadosCliente['codigo_desconto_texto'], $dadosCliente['email']);
                        }

                        $dadosCliente['metodo_pagamento'] = 'Stripe (' . strtoupper($session->payment_method_types[0] ?? 'cartão') . ')';

                        $encomendaId = criarEncomenda($pdo, $dadosCliente, $itens, $codigoDesconto, $sessionId);

                        $encomendaCompleta = buscarEncomendaCompleta($pdo, $encomendaId);
                        $emailEnviado = enviarEmailConfirmacao($encomendaCompleta);
                        if ($emailEnviado) marcarEmailEnviado($pdo, $encomendaId);

                        $totalFormatado = formatarPreco($encomendaCompleta['total']);
                        $emailCliente = $encomendaCompleta['email'];

                        // limpar o carrinho e os dados temporários
                        $_SESSION['carrinho'] = [];
                        unset($_SESSION['checkout_pendente']);
                    }
                }
            } catch (\Stripe\Exception\ApiErrorException $e) {
                error_log('Erro Stripe ao confirmar sessão: ' . $e->getMessage());
                $erro = 'Não foi possível confirmar o pagamento junto da Stripe. Tenta novamente ou contacta-nos.';
            }
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="devolucoes-wrap" style="padding-top:40px;">
    <?php if ($erro): ?>
        <div class="devolucao-caixa" style="text-align:center;">
            <div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div>
            <a href="carrinho.php" class="btn-northside" style="margin-top:12px;display:inline-block;">VOLTAR AO CARRINHO</a>
        </div>
    <?php else: ?>
        <div class="devolucao-caixa" style="text-align:center;">
            <div class="modal-confirmacao" style="display:block;">
                ✓ Encomenda #<?= str_pad($encomendaId, 5, '0', STR_PAD_LEFT) ?> confirmada!<br>
                Obrigado pela tua compra — total: <?= htmlspecialchars($totalFormatado) ?><br><br>
                <?= $emailEnviado
                    ? '📧 Enviámos um email de confirmação para <strong>' . htmlspecialchars($emailCliente) . '</strong>.'
                    : '⚠️ A encomenda foi registada, mas não foi possível enviar o email de confirmação.' ?>
            </div>
            <div style="margin-top:20px;">
                <a href="loja.php" class="btn-northside" style="display:inline-block;">CONTINUAR A COMPRAR</a>
                <a href="encomenda.php" class="btn-outline-northside" style="display:inline-block;">ACOMPANHAR ENCOMENDA</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
