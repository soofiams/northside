<?php
if (!isset($pdo)) { require_once __DIR__ . '/../config.php'; }
require_once __DIR__ . '/functions.php';
$categorias_menu = buscarCategorias($pdo);
$total_carrinho = carrinhoTotalItens();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titulo_pagina) ? htmlspecialchars($titulo_pagina) . ' — ' : '' ?><?= LOJA_NOME ?></title>
<link rel="stylesheet" href="<?= URL_BASE ?>assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../assets/css/style.css') ?: time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<header class="site-header">
<div class="top-bar">
    <div class="container-fluid">
        <span>ENVIO GRÁTIS EM TODAS AS ENCOMENDAS ACIMA DE <?= (int)buscarCustosEnvio($pdo)['gratis_acima_de'] ?>€</span>
        <div>
            <a href="#" data-abrir-modal="modal-sobre">Sobre Nós</a>
            <a href="#" id="link-ajuda-chat">Ajuda</a>
        </div>
    </div>
</div>

<div class="navbar-northside">
    <a href="<?= URL_BASE ?>index.php" class="logo-northside">
        NORTHSIDE
        <small>WEAR YOUR STORY</small>
    </a>

    <button class="btn-menu-mobile" id="btn-menu-mobile" aria-label="Abrir menu">
        <span></span><span></span><span></span>
    </button>

    <ul class="nav-links" id="nav-links">
        <li><a href="<?= URL_BASE ?>index.php" class="<?= ($pagina_atual ?? '') === 'inicio' ? 'ativo' : '' ?>">Início</a></li>
        <li><a href="<?= URL_BASE ?>loja.php" class="<?= ($pagina_atual ?? '') === 'loja' ? 'ativo' : '' ?>">Loja</a></li>
        <?php foreach ($categorias_menu as $cat): ?>
            <li><a href="<?= URL_BASE ?>loja.php?categoria=<?= urlencode($cat['slug']) ?>"><?= htmlspecialchars(mb_strtoupper($cat['nome'])) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= URL_BASE ?>avaliacoes.php" class="<?= ($pagina_atual ?? '') === 'avaliacoes' ? 'ativo' : '' ?>">AVALIAÇÕES</a></li>
    </ul>

    <div class="nav-icons">
        <form action="<?= URL_BASE ?>loja.php" method="get" style="display:inline;">
            <input type="text" name="pesquisa" placeholder="Pesquisar..." class="campo-pesquisa"
                value="<?= htmlspecialchars($_GET['pesquisa'] ?? '') ?>"
                style="border:1px solid #e5e7eb;border-radius:4px;padding:6px 10px;font-size:0.8rem;">
        </form>
        <a href="<?= URL_BASE ?>carrinho.php" title="Carrinho">
            🛒
            <span class="cart-badge" style="<?= $total_carrinho > 0 ? '' : 'display:none;' ?>"><?= $total_carrinho ?></span>
        </a>
    </div>
</div>
</header>

<main>
