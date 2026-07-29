<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email.php';

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Introduz um email válido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscritores (email) VALUES (?)");
    $stmt->execute([$email]);
    $ePrimeiraVez = $stmt->rowCount() > 0;

    // só gera um código novo se for mesmo a primeira vez (evita que a mesma
    // pessoa acumule vários códigos só por voltar a submeter o email)
    if ($ePrimeiraVez) {
        $codigoInfo = criarCodigoDescontoNewsletter($pdo, $email);
        enviarEmailCodigoDesconto($email, $codigoInfo['codigo'], $codigoInfo['percentagem'], $codigoInfo['validade']);
    }

    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    error_log('Erro ao subscrever newsletter: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível subscrever agora. Tenta mais tarde.']);
}
