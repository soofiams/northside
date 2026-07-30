<?php
if (!isset($pdo)) { require_once __DIR__ . '/../../config.php'; }
require_once __DIR__ . '/../../includes/functions.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pagina_admin = basename($_SERVER['PHP_SELF']);
$devolucoesPendentes = contarDevolucoesPendentes($pdo);
$mensagensNaoLidas = contarMensagensChatNaoLidas($pdo);

function navAtivo(string $ficheiro, string $atual): string {
    return $ficheiro === $atual ? 'ativo' : '';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($titulo_admin) ? htmlspecialchars($titulo_admin) . ' — ' : '' ?>Northside Admin</title>
<link rel="stylesheet" href="<?= URL_BASE ?>assets/css/style.css?v=<?= @filemtime(__DIR__ . '/../../assets/css/style.css') ?: time() ?>">
<link rel="stylesheet" href="<?= URL_BASE ?>assets/css/admin.css?v=<?= @filemtime(__DIR__ . '/../../assets/css/admin.css') ?: time() ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
</head>
<body>

<!-- Barra do topo, só visível em mobile -->
<div class="admin-topo-mobile">
    <a href="index.php" class="logo-northside" style="color:#fff;">NORTHSIDE<small>BACKOFFICE</small></a>
    <button class="btn-menu-admin" id="btn-menu-admin" aria-label="Abrir menu">
        <span></span><span></span><span></span>
    </button>
</div>

<div class="admin-overlay-fundo" id="admin-overlay-fundo"></div>

<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="index.php" class="logo-northside">NORTHSIDE<small>BACKOFFICE</small></a>

        <nav class="admin-nav">
            <a href="index.php" class="<?= navAtivo('index.php', $pagina_admin) ?>"><i class="fa-solid fa-gauge"></i> Painel</a>
            <a href="produtos.php" class="<?= navAtivo('produtos.php', $pagina_admin) ?>"><i class="fa-solid fa-shirt"></i> Produtos</a>
            <a href="categorias.php" class="<?= navAtivo('categorias.php', $pagina_admin) ?>"><i class="fa-solid fa-bars"></i> Categorias (Navbar)</a>
            <a href="encomendas.php" class="<?= navAtivo('encomendas.php', $pagina_admin) ?>"><i class="fa-solid fa-box"></i> Encomendas</a>
            <a href="devolucoes.php" class="<?= navAtivo('devolucoes.php', $pagina_admin) ?>">
                <i class="fa-solid fa-rotate-left"></i> Devoluções
                <?php if ($devolucoesPendentes > 0): ?><span class="badge-contagem"><?= $devolucoesPendentes ?></span><?php endif; ?>
            </a>
            <a href="descontos.php" class="<?= navAtivo('descontos.php', $pagina_admin) ?>"><i class="fa-solid fa-tag"></i> Códigos de Desconto</a>
            <a href="avaliacoes.php" class="<?= navAtivo('avaliacoes.php', $pagina_admin) ?>"><i class="fa-solid fa-star"></i> Avaliações</a>
            <a href="chat.php" class="<?= navAtivo('chat.php', $pagina_admin) ?>">
                <i class="fa-solid fa-comment-dots"></i> Chat
                <?php if ($mensagensNaoLidas > 0): ?><span class="badge-contagem"><?= $mensagensNaoLidas ?></span><?php endif; ?>
            </a>
            <a href="definicoes.php" class="<?= navAtivo('definicoes.php', $pagina_admin) ?>"><i class="fa-solid fa-gear"></i> Definições</a>
            <a href="administradores.php" class="<?= navAtivo('administradores.php', $pagina_admin) ?>"><i class="fa-solid fa-user-shield"></i> Administradores</a>
        </nav>

        <div class="admin-sair">
            <a href="<?= URL_BASE ?>index.php" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> Ver a loja</a>
            <a href="logout.php" style="margin-top:8px;display:flex;"><i class="fa-solid fa-right-from-bracket"></i> Sair (<?= htmlspecialchars($_SESSION['admin_utilizador']) ?>)</a>
        </div>
    </aside>

    <div class="admin-conteudo">
