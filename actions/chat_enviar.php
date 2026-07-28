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

try {
    $conversa = buscarConversaChat($pdo, $conversaId);
    if (!$conversa) {
        // a conversa guardada no browser já não existe (ex: base de dados foi reiniciada)
        echo json_encode(['sucesso' => false, 'erro' => 'conversa_invalida']);
        exit;
    }

    criarMensagemChat($pdo, $conversaId, 'cliente', $mensagem);
    echo json_encode(['sucesso' => true]);
} catch (PDOException $e) {
    error_log('Erro ao enviar mensagem de chat: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível enviar a mensagem. Tenta novamente.']);
}
