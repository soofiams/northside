<?php
require_once __DIR__ . '/../config.php';

$chave = (string)($_POST['chave'] ?? '');

if ($chave !== '' && isset($_SESSION['carrinho'][$chave])) {
    unset($_SESSION['carrinho'][$chave]);
}

header('Location: ' . URL_BASE . 'carrinho.php?msg=' . urlencode('Produto removido do carrinho.'));
exit;
