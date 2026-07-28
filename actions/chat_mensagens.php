<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$conversaId = (int)($_GET['conversa_id'] ?? 0);
$depoisDeId = (int)($_GET['depois_de'] ?? 0);

if (!$conversaId) {
    echo json_encode(['sucesso' => false, 'erro' => 'Conversa inválida.']);
    exit;
}

try {
    // confirma que a conversa existe mesmo (pode ter sido apagada ou a base de dados reiniciada)
    if (!buscarConversaChat($pdo, $conversaId)) {
        echo json_encode(['sucesso' => false, 'erro' => 'conversa_invalida']);
        exit;
    }

    $mensagens = buscarMensagensChat($pdo, $conversaId, $depoisDeId);

    echo json_encode([
        'sucesso' => true,
        'mensagens' => array_map(function ($m) {
            return [
                'id' => $m['id'],
                'remetente' => $m['remetente'],
                'mensagem' => htmlspecialchars($m['mensagem']),
                'hora' => date('H:i', strtotime($m['criado_em'])),
            ];
        }, $mensagens),
    ]);
} catch (PDOException $e) {
    error_log('Erro ao buscar mensagens de chat: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível ligar ao chat agora.']);
}
