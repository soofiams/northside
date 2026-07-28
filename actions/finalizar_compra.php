<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/email.php';

header('Content-Type: application/json; charset=utf-8');

function responderErro(string $mensagem): void {
    echo json_encode(['sucesso' => false, 'erro' => $mensagem]);
    exit;
}

// --- Validar os dados do cliente ---
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$codigo_postal = trim($_POST['codigo_postal'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$pagamento = trim($_POST['pagamento'] ?? '');
$codigo_desconto_texto = trim($_POST['codigo_desconto'] ?? '');

if ($nome === '' || $telefone === '' || $morada === '' || $codigo_postal === '' || $cidade === '' || $pagamento === '') {
    responderErro('Preenche todos os campos obrigatórios.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responderErro('Introduz um email válido.');
}

// --- Carrinho ---
$itens = carrinhoDetalhado($pdo);
if (empty($itens)) {
    responderErro('O teu carrinho está vazio.');
}

// --- Código de desconto (revalidado no servidor, nunca confiar só no que veio do ecrã) ---
$codigoDesconto = null;
if ($codigo_desconto_texto !== '') {
    $codigoDesconto = validarCodigoDesconto($pdo, $codigo_desconto_texto);
}

// --- Criar a encomenda ---
try {
    $encomendaId = criarEncomenda($pdo, [
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'morada' => $morada,
        'codigo_postal' => $codigo_postal,
        'cidade' => $cidade,
        'metodo_pagamento' => $pagamento,
    ], $itens, $codigoDesconto);
} catch (Exception $e) {
    error_log('Erro ao criar encomenda: ' . $e->getMessage());
    responderErro('Não foi possível concluir a encomenda. Tenta novamente.');
}

// --- Enviar o email de confirmação ---
$encomendaCompleta = buscarEncomendaCompleta($pdo, $encomendaId);
$emailEnviado = false;
if ($encomendaCompleta) {
    $emailEnviado = enviarEmailConfirmacao($encomendaCompleta);
    if ($emailEnviado) marcarEmailEnviado($pdo, $encomendaId);
}

// --- Esvaziar o carrinho ---
$_SESSION['carrinho'] = [];

echo json_encode([
    'sucesso' => true,
    'encomenda_id' => $encomendaId,
    'email' => $email,
    'email_enviado' => $emailEnviado,
    'total' => formatarPreco($encomendaCompleta['total']),
]);
