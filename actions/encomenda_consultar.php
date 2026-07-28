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

try {
    $encomenda = buscarEncomendaPorNumeroEmail($pdo, $numero, $email);

    if (!$encomenda) {
        echo json_encode(['sucesso' => false, 'erro' => 'Não encontrámos nenhuma encomenda com esse número e email.']);
        exit;
    }

    $itens = array_map(function ($item) {
        return [
            'nome' => $item['nome_produto'],
            'tamanho' => $item['tamanho'],
            'quantidade' => $item['quantidade'],
            'subtotal' => formatarPreco($item['subtotal']),
        ];
    }, $encomenda['itens']);

    echo json_encode([
        'sucesso' => true,
        'encomenda_id' => $encomenda['id'],
        'estado' => $encomenda['estado'],
        'data' => date('d/m/Y', strtotime($encomenda['criado_em'])),
        'total' => formatarPreco($encomenda['total']),
        'metodo_pagamento' => $encomenda['metodo_pagamento'],
        'morada' => $encomenda['morada'] . ', ' . $encomenda['codigo_postal'] . ' ' . $encomenda['cidade'],
        'itens' => $itens,
    ]);
} catch (PDOException $e) {
    error_log('Erro ao consultar encomenda: ' . $e->getMessage());
    echo json_encode(['sucesso' => false, 'erro' => 'Não foi possível consultar a encomenda agora. Tenta novamente.']);
}
