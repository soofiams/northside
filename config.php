<?php
/**
 * CONFIGURAÇÃO DA LOJA NORTHSIDE
 * Altera estes valores consoante os dados do teu servidor/hosting.
 */

// --- Base de dados ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'northside');
define('DB_USER', 'root');      // altera para o teu utilizador MySQL
define('DB_PASS', '');          // altera para a tua password MySQL

// --- Loja ---
define('LOJA_NOME', 'NORTHSIDE');
define('LOJA_SLOGAN', 'WEAR YOU STORY');
define('PORTES_GRATIS_ACIMA_DE', 50.00);
define('URL_BASE', '/'); // se a loja não estiver na raiz do site, ex: '/northside/'

session_start();

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Erro de ligação à base de dados. Verifica os dados em config.php. (' . $e->getMessage() . ')');
}

// Nº total de artigos no carrinho, disponível em qualquer página
function carrinho_total_itens(): int {
    if (empty($_SESSION['carrinho'])) return 0;
    return array_sum($_SESSION['carrinho']);
}
