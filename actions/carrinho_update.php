<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$produto_id = (int)($_POST['produto_id'] ?? 0);
$quantidade = (int)($_POST['quantidade'] ?? 1);

$produto = buscar_produto_por_id($pdo, $produto_id);

if ($produto && isset($_SESSION['carrinho'][$produto_id])) {
    if ($quantidade <= 0) {
        unset($_SESSION['carrinho'][$produto_id]);
    } else {
        // nunca ultrapassar o stock disponível
        $_SESSION['carrinho'][$produto_id] = min($quantidade, (int)$produto['stock']);
    }
}

header('Location: ' . URL_BASE . 'carrinho.php?msg=' . urlencode('Carrinho atualizado.'));
exit;
