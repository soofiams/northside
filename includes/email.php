<?php
/**
 * Envio do email de confirmação de encomenda.
 *
 * Usa o PHPMailer (via Composer) para enviar por SMTP — é o método mais fiável,
 * porque a função mail() nativa do PHP raramente funciona num XAMPP/Laragon sem
 * configuração extra de um servidor de email local.
 *
 * Para instalar o PHPMailer:
 *   1. Instala o Composer (https://getcomposer.org/download/) se ainda não tiveres
 *   2. No terminal, dentro da pasta do projeto: composer require phpmailer/phpmailer
 *
 * Se não quiseres usar Composer, o código também tenta a função mail() nativa
 * como alternativa — mas é preciso configurar o php.ini com um servidor SMTP
 * (ex: através do sendmail.ini do XAMPP) para funcionar.
 */

function enviarEmailConfirmacao(array $encomenda): bool {
    $assunto = 'A tua encomenda Northside #' . str_pad($encomenda['id'], 5, '0', STR_PAD_LEFT) . ' foi confirmada';
    $corpoHtml = construirEmailConfirmacaoHtml($encomenda);

    $caminhoAutoload = __DIR__ . '/../vendor/autoload.php';

    // ---- Opção 1: PHPMailer via SMTP (recomendado) ----
    if (file_exists($caminhoAutoload)) {
        require_once $caminhoAutoload;

        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_UTILIZADOR;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(SMTP_UTILIZADOR, SMTP_NOME_REMETENTE);
            $mail->addAddress($encomenda['email'], $encomenda['nome_cliente']);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body = $corpoHtml;
            $mail->AltBody = 'A tua encomenda Northside #' . $encomenda['id'] . ' foi confirmada. Total: ' . formatarPreco($encomenda['total']);

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log('Falha ao enviar email (PHPMailer): ' . $e->getMessage());
            return false;
        }
    }

    // ---- Opção 2: mail() nativo do PHP (recurso de emergência) ----
    $cabecalhos = "MIME-Version: 1.0\r\n";
    $cabecalhos .= "Content-type: text/html; charset=UTF-8\r\n";
    $cabecalhos .= "From: " . SMTP_NOME_REMETENTE . " <" . SMTP_UTILIZADOR . ">\r\n";

    return @mail($encomenda['email'], $assunto, $corpoHtml, $cabecalhos);
}

function construirEmailConfirmacaoHtml(array $encomenda): string {
    $linhasItens = '';
    foreach ($encomenda['itens'] as $item) {
        $linhasItens .= '
            <tr>
                <td style="padding:8px 0;border-bottom:1px solid #eee;">' . htmlspecialchars($item['quantidade']) . '× ' . htmlspecialchars($item['nome_produto']) . '</td>
                <td style="padding:8px 0;border-bottom:1px solid #eee;text-align:right;">' . formatarPreco($item['subtotal']) . '</td>
            </tr>';
    }

    $linhaDesconto = '';
    if ($encomenda['valor_desconto'] > 0) {
        $linhaDesconto = '
            <tr>
                <td style="padding:6px 0;color:#1a7f43;">Desconto</td>
                <td style="padding:6px 0;text-align:right;color:#1a7f43;">-' . formatarPreco($encomenda['valor_desconto']) . '</td>
            </tr>';
    }

    return '
    <div style="font-family:Arial,sans-serif;max-width:520px;margin:0 auto;color:#1a1a1a;">
        <div style="background:#16234a;padding:24px;text-align:center;">
            <span style="color:#fff;font-size:1.4rem;font-weight:bold;letter-spacing:2px;">NORTHSIDE</span>
        </div>
        <div style="padding:28px 24px;">
            <h2 style="color:#16234a;margin-top:0;">Obrigado pela tua compra, ' . htmlspecialchars($encomenda['nome_cliente']) . '!</h2>
            <p>A tua encomenda <strong>#' . str_pad($encomenda['id'], 5, '0', STR_PAD_LEFT) . '</strong> foi confirmada e está a ser preparada.</p>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                ' . $linhasItens . '
                <tr>
                    <td style="padding:6px 0;">Subtotal</td>
                    <td style="padding:6px 0;text-align:right;">' . formatarPreco($encomenda['subtotal']) . '</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;">Envio</td>
                    <td style="padding:6px 0;text-align:right;">' . ($encomenda['envio'] == 0 ? 'Grátis' : formatarPreco($encomenda['envio'])) . '</td>
                </tr>
                ' . $linhaDesconto . '
                <tr>
                    <td style="padding:10px 0;border-top:2px solid #16234a;font-weight:bold;">Total</td>
                    <td style="padding:10px 0;border-top:2px solid #16234a;text-align:right;font-weight:bold;">' . formatarPreco($encomenda['total']) . '</td>
                </tr>
            </table>

            <p style="font-size:0.9rem;color:#4a5568;">
                <strong>Entrega em:</strong><br>
                ' . htmlspecialchars($encomenda['morada']) . '<br>
                ' . htmlspecialchars($encomenda['codigo_postal']) . ' ' . htmlspecialchars($encomenda['cidade']) . '
            </p>
            <p style="font-size:0.9rem;color:#4a5568;"><strong>Método de pagamento:</strong> ' . htmlspecialchars($encomenda['metodo_pagamento']) . '</p>

            <p style="margin-top:28px;font-size:0.85rem;color:#9aa5cc;">Northside — Nascido no Porto, feito para o mundo.</p>
        </div>
    </div>';
}
