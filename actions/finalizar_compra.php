<?php
/**
 * ATENÇÃO: este endpoint deixou de ser usado.
 *
 * Antigamente, este ficheiro criava a encomenda diretamente, sem verificar
 * nenhum pagamento a sério — servia para a fase de simulação, antes de
 * termos a Stripe ligada.
 *
 * Agora o fluxo é: actions/stripe_criar_sessao.php (cria o pagamento na
 * Stripe) → o cliente paga no checkout da Stripe → stripe_sucesso.php
 * confirma o pagamento e só aí grava a encomenda.
 *
 * Mantemos este ficheiro só para não dar erro 404 a quem tenha esta
 * página em cache — mas ele nunca cria encomendas.
 */
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'sucesso' => false,
    'erro' => 'Este método de finalizar compra foi descontinuado. Atualiza a página e tenta novamente.',
]);
