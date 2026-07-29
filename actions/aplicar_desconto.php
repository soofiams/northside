<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$codigo = trim($_POST['codigo'] ?? '');
$email = trim($_POST['email'] ?? '');

if ($codigo === '') {
    echo json_encode(['valido' => false, 'mensagem' => 'Escreve um código antes de aplicar.']);
    exit;
}

$resultado = validarCodigoDesconto($pdo, $codigo, $email !== '' ? $email : null);

if ($resultado) {
    echo json_encode([
        'valido' => true,
        'codigo' => $resultado['codigo'],
        'percentagem' => (float)$resultado['percentagem'],
        'mensagem' => '✓ Código aplicado: -' . round($resultado['percentagem'] * 100) . '% no total.',
    ]);
} else {
    echo json_encode(['valido' => false, 'mensagem' => 'Código inválido, expirado, já usado, ou associado a outro email.']);
}
