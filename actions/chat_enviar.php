<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$conversaId = (int)($_POST['conversa_id'] ?? 0);
$mensagem = trim($_POST['mensagem'] ?? '');

if (!$conversaId || $mensagem === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'Mensagem vazia.']);
    exit;
}

$conversa = buscarConversaChat($pdo, $conversaId);
if (!$conversa) {
    echo json_encode(['sucesso' => false, 'erro' => 'Conversa não encontrada.']);
    exit;
}

criarMensagemChat($pdo, $conversaId, 'cliente', $mensagem);

echo json_encode(['sucesso' => true]);
