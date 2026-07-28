<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_id'])) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não autenticado.']);
    exit;
}

$conversaId = (int)($_POST['conversa_id'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

if (!$conversaId || $mensagem === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia.']);
    exit;
}

try {
    criarMensagemChat($pdo, $conversaId, 'northside', $mensagem);
    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    error_log('Erro ao responder no chat (admin): ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível enviar a resposta.']);
}
