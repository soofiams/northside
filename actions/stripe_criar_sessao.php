<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

function responderErro(string $mensagem): void {
    echo json_encode(['sucesso' => false, 'erro' => $mensagem]);
    exit;
}

// --- Validar os dados do cliente (mesmos campos de sempre) ---
$nome = trim($_POST['nome'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$morada = trim($_POST['morada'] ?? '');
$codigo_postal = trim($_POST['codigo_postal'] ?? '');
$cidade = trim($_POST['cidade'] ?? '');
$codigo_desconto_texto = trim($_POST['codigo_desconto'] ?? '');

if ($nome === '' || $telefone === '' || $morada === '' || $codigo_postal === '' || $cidade === '') {
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

// --- Código de desconto (revalidado no servidor) ---
$codigoDesconto = null;
if ($codigo_desconto_texto !== '') {
    $codigoDesconto = validarCodigoDesconto($pdo, $codigo_desconto_texto, $email);
}

// --- Calcular os totais ---
$subtotal = carrinhoTotalValor($itens);
$envio = calcularEnvio($pdo, $subtotal);
$valorDesconto = $codigoDesconto ? round(($subtotal + $envio) * $codigoDesconto['percentagem'], 2) : 0;
$total = max(0, $subtotal + $envio - $valorDesconto);

if ($total <= 0) {
    responderErro('O valor da encomenda tem de ser superior a 0€.');
}

// --- Guardar os dados do cliente na sessão, para usar depois do pagamento confirmado ---
$_SESSION['checkout_pendente'] = [
    'nome' => $nome,
    'email' => $email,
    'telefone' => $telefone,
    'morada' => $morada,
    'codigo_postal' => $codigo_postal,
    'cidade' => $cidade,
    'codigo_desconto_texto' => $codigo_desconto_texto,
];

// --- Criar a sessão de checkout na Stripe ---
$caminhoAutoload = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($caminhoAutoload)) {
    responderErro('A Stripe ainda não está instalada no servidor (falta correr "composer require stripe/stripe-php").');
}
require_once $caminhoAutoload;

try {
    \Stripe\Stripe::setApiKey(STRIPE_CHAVE_SECRETA);

    $linhasProdutos = [];
    foreach ($itens as $item) {
        $nomeProduto = $item['produto']['nome'] . ($item['tamanho'] ? ' (Tamanho ' . $item['tamanho'] . ')' : '');
        $linhasProdutos[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => $nomeProduto],
                'unit_amount' => (int)round($item['produto']['preco'] * 100),
            ],
            'quantity' => $item['quantidade'],
        ];
    }

    if ($envio > 0) {
        $linhasProdutos[] = [
            'price_data' => [
                'currency' => 'eur',
                'product_data' => ['name' => 'Envio'],
                'unit_amount' => (int)round($envio * 100),
            ],
            'quantity' => 1,
        ];
    }

    $parametrosSessao = [
        'mode' => 'payment',
        'customer_email' => $email,
        'line_items' => $linhasProdutos,
        // Cartão tem de estar sempre listado — é ele que faz o Apple Pay e o Google Pay
        // aparecerem automaticamente como "carteiras" por cima do cartão, quando o
        // browser/dispositivo do cliente as suporta.
        'payment_method_types' => ['card', 'klarna', 'mb_way'],
        'success_url' => URL_BASE_ABSOLUTA . 'stripe_sucesso.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => URL_BASE_ABSOLUTA . 'carrinho.php?erro=' . urlencode('Pagamento cancelado — o teu carrinho continua guardado.'),
    ];

    // aplicar o desconto como um cupão de sessão, se houver código válido
    if ($valorDesconto > 0) {
        $cupao = \Stripe\Coupon::create([
            'name' => 'Desconto Northside',
            'amount_off' => (int)round($valorDesconto * 100),
            'currency' => 'eur',
            'duration' => 'once',
        ]);
        $parametrosSessao['discounts'] = [['coupon' => $cupao->id]];
    }

    $session = \Stripe\Checkout\Session::create($parametrosSessao);

    echo json_encode(['sucesso' => true, 'url' => $session->url]);
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log('Erro Stripe ao criar sessão: ' . $e->getMessage());
    responderErro('Não foi possível ligar ao Stripe. Verifica as chaves em config.php.');
} catch (Exception $e) {
    error_log('Erro ao criar sessão de checkout: ' . $e->getMessage());
    responderErro('Ocorreu um erro inesperado. Tenta novamente.');
}
