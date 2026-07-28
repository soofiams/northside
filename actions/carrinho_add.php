<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$produto_id = (int)($_POST['produto_id'] ?? 0);
$quantidade = max(1, (int)($_POST['quantidade'] ?? 1));
$tamanho = trim($_POST['tamanho'] ?? '') ?: null;
$voltar = $_POST['voltar'] ?? 'loja.php';
$separador = str_contains($voltar, '?') ? '&' : '?';

$produto = buscarProdutoPorId($pdo, $produto_id);

if (!$produto) {
    header('Location: ' . URL_BASE . 'loja.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

// Se o produto tiver tamanhos, é obrigatório escolher um
$tamanhosDisponiveis = buscarTamanhosProduto($pdo, $produto_id);
if (!empty($tamanhosDisponiveis) && !$tamanho) {
    header('Location: ' . URL_BASE . $voltar . $separador . 'erro=' . urlencode('Escolhe um tamanho antes de adicionar ao carrinho.'));
    exit;
}

$stockDisponivel = (int)$produto['stock'];
if ($tamanho) {
    $stockDisponivel = buscarStockTamanho($pdo, $produto_id, $tamanho);
    if ($stockDisponivel === null) {
        header('Location: ' . URL_BASE . $voltar . $separador . 'erro=' . urlencode('Tamanho inválido.'));
        exit;
    }
}

if (!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];

$chave = chaveCarrinho($produto_id, $tamanho);
$quantidade_atual = $_SESSION['carrinho'][$chave] ?? 0;
$quantidade_final = min($quantidade_atual + $quantidade, $stockDisponivel);

if ($quantidade_final <= 0) {
    header('Location: ' . URL_BASE . $voltar . $separador . 'erro=' . urlencode('Produto esgotado' . ($tamanho ? ' neste tamanho.' : '.')));
    exit;
}

$_SESSION['carrinho'][$chave] = $quantidade_final;

$mensagem = $produto['nome'] . ($tamanho ? ' (' . $tamanho . ')' : '') . ' adicionado ao carrinho.';
header('Location: ' . URL_BASE . $voltar . $separador . 'msg=' . urlencode($mensagem));
exit;
