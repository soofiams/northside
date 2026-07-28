<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$numero = (int)($_POST['numero_encomenda'] ?? 0);
$email = trim($_POST['email'] ?? '');
$itensIds = $_POST['itens'] ?? [];
$motivo = trim($_POST['motivo'] ?? '');

if ($numero <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Dados da encomenda inválidos.']);
    exit;
}
if (empty($itensIds) || !is_array($itensIds)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Escolhe pelo menos um produto para devolver.']);
    exit;
}
if ($motivo === '') {
    echo json_encode(['sucesso' => false, 'erro' => 'Explica o motivo da devolução.']);
    exit;
}

// revalidar sempre no servidor que a encomenda é mesmo deste email
$encomenda = buscarEncomendaPorNumeroEmail($pdo, $numero, $email);
if (!$encomenda) {
    echo json_encode(['sucesso' => false, 'erro' => 'Encomenda não encontrada.']);
    exit;
}

// filtrar só os IDs de itens que pertencem mesmo a esta encomenda
$idsValidos = array_column($encomenda['itens'], 'id');
$itensParaDevolver = array_filter($itensIds, function ($id) use ($idsValidos) {
    return in_array((int)$id, $idsValidos);
});

if (empty($itensParaDevolver)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Os produtos escolhidos não pertencem a esta encomenda.']);
    exit;
}

criarDevolucao($pdo, $numero, $itensParaDevolver, $motivo);

echo json_encode(['sucesso' => true]);
