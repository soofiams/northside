<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Encomendas';

$encomendas = $pdo->query("SELECT * FROM encomendas ORDER BY criado_em DESC")->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Encomendas (<?= count($encomendas) ?>)</h1></div>

<div class="admin-painel">
    <div class="admin-tabela-scroll">
<table class="admin-tabela">
        <thead>
            <tr><th>Nº</th><th>Cliente</th><th>Email</th><th>Total</th><th>Pagamento</th><th>Estado</th><th>Data</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($encomendas as $enc): ?>
                <tr>
                    <td>#<?= str_pad($enc['id'], 5, '0', STR_PAD_LEFT) ?></td>
                    <td><?= htmlspecialchars($enc['nome_cliente']) ?></td>
                    <td><?= htmlspecialchars($enc['email']) ?></td>
                    <td><strong><?= formatarPreco($enc['total']) ?></strong></td>
                    <td><?= htmlspecialchars($enc['metodo_pagamento']) ?></td>
                    <td>
                        <?php
                        $coresPill = ['pendente' => 'pill-alerta', 'confirmada' => 'pill-ok', 'enviada' => 'pill-ok', 'entregue' => 'pill-ok', 'cancelada' => 'pill-erro'];
                        ?>
                        <span class="pill <?= $coresPill[$enc['estado']] ?? 'pill-neutro' ?>"><?= htmlspecialchars($enc['estado']) ?></span>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($enc['criado_em'])) ?></td>
                    <td><a href="encomenda_detalhe.php?id=<?= $enc['id'] ?>" class="admin-acoes editar">Ver</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($encomendas)): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não há encomendas.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
