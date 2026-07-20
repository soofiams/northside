<?php
require_once __DIR__ . '/../config.php';

$produto_id = (int)($_POST['produto_id'] ?? 0);

if (isset($_SESSION['carrinho'][$produto_id])) {
    unset($_SESSION['carrinho'][$produto_id]);
}

header('Location: ' . URL_BASE . 'carrinho.php?msg=' . urlencode('Produto removido do carrinho.'));
exit;
