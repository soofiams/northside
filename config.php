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
define('URL_BASE', '/');        // se a loja não estiver na raiz do site, ex: '/northside/'
define('PORTES_GRATIS_ACIMA_DE', 50.00);
define('CUSTO_ENVIO', 4.99);

// --- Email (SMTP) — usado para enviar a confirmação de encomenda ---
// No Gmail: ativa a verificação em 2 passos e cria uma "Password de aplicação" em
// https://myaccount.google.com/apppasswords — usa essa password aqui, não a tua password normal.
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_UTILIZADOR', 'o-teu-email@gmail.com');
define('SMTP_PASSWORD', 'a-tua-password-de-aplicacao');
define('SMTP_NOME_REMETENTE', 'Northside');

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
