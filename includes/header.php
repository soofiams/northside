<?php
if (!isset($pdo)) { require_once __DIR__ . '/../config.php'; }
require_once __DIR__ . '/functions.php';
$categorias_menu = buscar_categorias($pdo);
$total_carrinho = carrinho_total_itens();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' — ' : '' ?><?= LOJA_NOME ?></title>
<link rel="stylesheet" href="<?= URL_BASE ?>assets/css/style.css">
</head>
<body>

<div class="top-bar">
    <div class="container-fluid">
        <span>ENVIO GRÁTIS EM TODAS AS ENCOMENDAS ACIMA DE <?= (int)PORTES_GRATIS_ACIMA_DE ?>€</span>
        <div>
            <a href="#">Sobre Nós</a>
            <a href="#">Ajuda</a>
        </div>
    </div>
</div>

<div class="navbar-northside">
    <a href="<?= URL_BASE ?>index.php" class="logo-northside">
        NORTHSIDE
        <small>WEAR YOU STORY</small>
    </a>

    <ul class="nav-links">
        <li><a href="<?= URL_BASE ?>index.php" class="<?= ($pagina_atual ?? '') === 'inicio' ? 'ativo' : '' ?>">Início</a></li>
        <li><a href="<?= URL_BASE ?>loja.php" class="<?= ($pagina_atual ?? '') === 'loja' ? 'ativo' : '' ?>">Loja</a></li>
        <?php foreach ($categorias_menu as $cat): ?>
            <li><a href="<?= URL_BASE ?>loja.php?categoria=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars(mb_strtoupper($cat['nome'])) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <div class="nav-icons">
        <form action="<?= URL_BASE ?>loja.php" method="get" style="display:inline;">
            <input type="text" name="pesquisa" placeholder="Pesquisar..." style="border:1px solid #e5e7eb;border-radius:4px;padding:6px 10px;font-size:0.8rem;">
        </form>
        <a href="<?= URL_BASE ?>carrinho.php" title="Carrinho">
            🛒
            <?php if ($total_carrinho > 0): ?>
                <span class="cart-badge"><?= $total_carrinho ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>
