<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$produto_id_raw = trim($_POST['produto_id'] ?? '');
$produto_id = $produto_id_raw !== '' ? (int)$produto_id_raw : null;
$nome = trim($_POST['nome'] ?? '');
$estrelas = (int)($_POST['estrelas'] ?? 5);
$comentario = trim($_POST['comentario'] ?? '');
$voltar = trim($_POST['voltar'] ?? '') ?: 'avaliacoes.php';

// se veio ligada a um produto, confirma que ele existe mesmo
if ($produto_id !== null) {
    $produto = buscarProdutoPorId($pdo, $produto_id);
    if (!$produto) {
        header('Location: ' . URL_BASE . 'loja.php?erro=' . urlencode('Produto não encontrado.'));
        exit;
    }
}

$separador = str_contains($voltar, '?') ? '&' : '?';

if ($nome === '' || $comentario === '') {
    header('Location: ' . URL_BASE . $voltar . $separador . 'erro=' . urlencode('Preenche o nome e o comentário.') . '#avaliar');
    exit;
}

criarAvaliacaoProduto($pdo, $produto_id, $nome, $estrelas, $comentario);

header('Location: ' . URL_BASE . $voltar . $separador . 'msg=' . urlencode('Obrigado! A tua avaliação foi publicada.') . '#avaliar');
exit;
