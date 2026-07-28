<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Introduz o teu nome e um email válido.']);
    exit;
}

try {
    $conversaId = criarConversaChat($pdo, $nome, $email);

    // mensagem automática de boas-vindas
    criarMensagemChat(
        $pdo,
        $conversaId,
        'northside',
        'Olá ' . $nome . '! 👋 Em que podemos ajudar? A nossa equipa responde o mais rápido possível.'
    );

    echo json_encode(['sucesso' => true, 'conversa_id' => $conversaId]);
} catch (PDOException $e) {
    error_log('Erro ao iniciar conversa de chat: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível iniciar a conversa. Verifica se a migração do chat (database/migracao_3.sql) já foi corrida.']);
}
