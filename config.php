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

// URL_BASE é detetado automaticamente a partir da pasta onde este ficheiro está,
// por isso não precisas de o configurar à mão — funciona seja qual for o nome
// que deres à pasta do projeto (northside, northside-php, loja, etc.)
$diretorio_projeto = str_replace('\\', '/', __DIR__);
$document_root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/'));
$url_base = (stripos($diretorio_projeto, $document_root) === 0)
    ? substr($diretorio_projeto, strlen($document_root))
    : '';
$url_base = '/' . trim($url_base, '/');
if ($url_base !== '/') $url_base .= '/';
define('URL_BASE', $url_base);

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
