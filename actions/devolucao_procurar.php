<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$numero = (int)($_POST['numero_encomenda'] ?? 0);
$email = trim($_POST['email'] ?? '');

if ($numero <= 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['sucesso' => false, 'erro' => 'Introduz o número da encomenda e o email usado na compra.']);
    exit;
}

$encomenda = buscarEncomendaPorNumeroEmail($pdo, $numero, $email);

if (!$encomenda) {
    echo json_encode(['sucesso' => false, 'erro' => 'Não encontrámos nenhuma encomenda com esse número e email.']);
    exit;
}

$itens = array_map(function ($item) {
    return [
        'id' => $item['id'],
        'nome' => $item['nome_produto'],
        'tamanho' => $item['tamanho'],
        'quantidade' => $item['quantidade'],
        'ja_pedida' => (int)$item['ja_pedida'] > 0,
    ];
}, $encomenda['itens']);

echo json_encode([
    'sucesso' => true,
    'encomenda_id' => $encomenda['id'],
    'email' => $encomenda['email'],
    'itens' => $itens,
]);
