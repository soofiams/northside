<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$chave = (string)($_POST['chave'] ?? '');
$quantidade = (int)($_POST['quantidade'] ?? 1);

if ($chave !== '' && isset($_SESSION['carrinho'][$chave])) {
    $info = analisarChaveCarrinho($chave);
    $produto = buscarProdutoPorId($pdo, $info['produto_id']);

    if ($produto) {
        $stockDisponivel = $info['tamanho']
            ? (buscarStockTamanho($pdo, $info['produto_id'], $info['tamanho']) ?? 0)
            : (int)$produto['stock'];

        if ($quantidade <= 0) {
            unset($_SESSION['carrinho'][$chave]);
        } else {
            $_SESSION['carrinho'][$chave] = min($quantidade, $stockDisponivel);
        }
    }
}

header('Location: ' . URL_BASE . 'carrinho.php?msg=' . urlencode('Carrinho atualizado.'));
exit;
