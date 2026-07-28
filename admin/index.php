<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Painel';

$totalEncomendas = (int)$pdo->query("SELECT COUNT(*) FROM encomendas")->fetchColumn();
$totalFaturado = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM encomendas")->fetchColumn();
$encomendasHoje = (int)$pdo->query("SELECT COUNT(*) FROM encomendas WHERE DATE(criado_em) = CURDATE()")->fetchColumn();
$totalProdutos = (int)$pdo->query("SELECT COUNT(*) FROM produtos WHERE ativo = 1")->fetchColumn();
$produtosEsgotados = (int)$pdo->query(
    "SELECT COUNT(*) FROM produtos p WHERE p.ativo = 1 AND p.stock = 0
     AND NOT EXISTS (SELECT 1 FROM produto_tamanhos pt WHERE pt.produto_id = p.id AND pt.stock > 0)"
)->fetchColumn();
$devolucoesPendentes = contarDevolucoesPendentes($pdo);
$mensagensNaoLidas = contarMensagensChatNaoLidas($pdo);

$ultimasEncomendas = $pdo->query("SELECT * FROM encomendas ORDER BY criado_em DESC LIMIT 6")->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo">
    <h1>Olá, <?= htmlspecialchars($_SESSION['admin_utilizador']) ?> 👋</h1>
</div>

<div class="admin-cartoes">
    <div class="admin-cartao">
        <div class="numero"><?= $totalEncomendas ?></div>
        <div class="rotulo">Encomendas no total</div>
    </div>
    <div class="admin-cartao">
        <div class="numero"><?= formatarPreco($totalFaturado) ?></div>
        <div class="rotulo">Faturado no total</div>
    </div>
    <div class="admin-cartao">
        <div class="numero"><?= $encomendasHoje ?></div>
        <div class="rotulo">Encomendas hoje</div>
    </div>
    <div class="admin-cartao">
        <div class="numero"><?= $totalProdutos ?></div>
        <div class="rotulo">Produtos ativos</div>
    </div>
    <div class="admin-cartao <?= $produtosEsgotados > 0 ? 'alerta' : '' ?>">
        <div class="numero"><?= $produtosEsgotados ?></div>
        <div class="rotulo">Produtos esgotados</div>
    </div>
    <div class="admin-cartao <?= $devolucoesPendentes > 0 ? 'alerta' : '' ?>">
        <div class="numero"><?= $devolucoesPendentes ?></div>
        <div class="rotulo">Devoluções pendentes</div>
    </div>
    <div class="admin-cartao <?= $mensagensNaoLidas > 0 ? 'alerta' : '' ?>">
        <div class="numero"><?= $mensagensNaoLidas ?></div>
        <div class="rotulo">Mensagens de chat por ler</div>
    </div>
</div>

<div class="admin-painel">
    <h2>Últimas encomendas</h2>
    <div class="admin-tabela-scroll">
<table class="admin-tabela">
        <thead>
            <tr><th>Nº</th><th>Cliente</th><th>Total</th><th>Estado</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($ultimasEncomendas as $enc): ?>
                <tr>
                    <td>#<?= str_pad($enc['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($enc['nome_cliente']) ?></td>
                    <td><strong><?= formatarPreco($enc['total']) ?></strong></td>
                    <td><span class="pill pill-ok"><?= htmlspecialchars($enc['estado']) ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($enc['criado_em'])) ?></td>
                    <td><a href="encomenda_detalhe.php?id=<?= $enc['id'] ?>" class="admin-acoes editar">Ver</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($ultimasEncomendas)): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não há encomendas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
