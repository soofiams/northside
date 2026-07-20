<?php
if (!isset($pdo)) { require_once __DIR__ . '/../../config.php'; }

// Protege todas as páginas do admin exceto o login e o setup inicial
$pagina_atual_script = basename($_SERVER['PHP_SELF']);
if (!in_array($pagina_atual_script, ['login.php', 'setup.php']) && empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' — ' : '' ?>Admin Northside</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="navbar-northside">
    <a href="index.php" class="logo-northside">NORTHSIDE<small>PAINEL DE ADMIN</small></a>
    <?php if (!empty($_SESSION['admin_id'])): ?>
        <div class="nav-icons">
            <a href="../index.php" target="_blank" style="font-size:0.85rem;font-weight:700;">Ver loja ↗</a>
            <a href="logout.php" style="font-size:0.85rem;font-weight:700;">Sair</a>
        </div>
    <?php endif; ?>
</div>
<div class="admin-wrap">
