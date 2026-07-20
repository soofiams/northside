<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$produto_id = (int)($_POST['produto_id'] ?? 0);
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
$voltar = $_POST['voltar'] ?? 'loja.php';

$produto = buscar_produto_por_id($pdo, $produto_id);

if (!$produto) {
    header('Location: ' . URL_BASE . 'loja.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];

$quantidade_atual = $_SESSION['carrinho'][$produto_id] ?? 0;
$quantidade_pretendida = $quantidade_atual + $quantidade;

// nunca ultrapassar o stock disponível
$quantidade_final = min($quantidade_pretendida, (int)$produto['stock']);

if ($quantidade_final <= 0) {
    header('Location: ' . URL_BASE . $voltar . (str_contains($voltar, '?') ? '&' : '?') . 'erro=' . urlencode('Produto esgotado.'));
    exit;
}

$_SESSION['carrinho'][$produto_id] = $quantidade_final;

$separador = str_contains($voltar, '?') ? '&' : '?';
header('Location: ' . URL_BASE . $voltar . $separador . 'msg=' . urlencode($produto['nome'] . ' adicionado ao carrinho.'));
exit;
