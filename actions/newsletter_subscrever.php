<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$email = trim($_POST['email'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Introduz um email válido.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter_subscritores (email) VALUES (?)");
    $stmt->execute([$email]);
    echo json_encode(['sucesso' => true]);
} catch (Exception $e) {
    error_log('Erro ao subscrever newsletter: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível subscrever agora. Tenta mais tarde.']);
}
