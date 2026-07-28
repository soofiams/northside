<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$produto_id = (int)($_POST['produto_id'] ?? 0);
$nome = trim($_POST['nome'] ?? '');
$estrelas = (int)($_POST['estrelas'] ?? 5);
$comentario = trim($_POST['comentario'] ?? '');

$voltar = 'produto.php?id=' . $produto_id;

$produto = buscarProdutoPorId($pdo, $produto_id);
if (!$produto) {
    header('Location: ' . URL_BASE . 'loja.php?erro=' . urlencode('Produto não encontrado.'));
    exit;
}

if ($nome === '' || $comentario === '') {
    header('Location: ' . URL_BASE . $voltar . '&erro=' . urlencode('Preenche o nome e o comentário.') . '#avaliar');
    exit;
}

criarAvaliacaoProduto($pdo, $produto_id, $nome, $estrelas, $comentario);

header('Location: ' . URL_BASE . $voltar . '&msg=' . urlencode('Obrigado! A tua avaliação foi publicada.') . '#avaliar');
exit;
