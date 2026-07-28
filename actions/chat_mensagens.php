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
