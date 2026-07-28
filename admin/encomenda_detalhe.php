<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Detalhe da encomenda';

$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['novo_estado'])) {
    $estadosValidos = ['pendente', 'confirmada', 'enviada', 'entregue', 'cancelada'];
    if (in_array($_POST['novo_estado'], $estadosValidos)) {
        $stmt = $pdo->prepare("UPDATE encomendas SET estado = ? WHERE id = ?");
        $stmt->execute([$_POST['novo_estado'], $id]);
    }
    header('Location: encomenda_detalhe.php?id=' . $id . '&msg=' . urlencode('Estado atualizado.'));
    exit;
}

$encomenda = buscarEncomendaCompleta($pdo, $id);
if (!$encomenda) {
    header('Location: encomendas.php');
    exit;
}

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo">
    <h1>Encomenda #<?= str_pad($encomenda['id'], 5, '0', STR_PAD_LEFT) ?></h1>
    <a href="encomendas.php" class="ver-loja">← Voltar às encomendas</a>
</div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

<div class="admin-duas-colunas admin-duas-colunas-encomenda">

    <div class="admin-painel">
        <h2>Produtos</h2>
        <div class="admin-tabela-scroll">
<table class="admin-tabela">
            <thead><tr><th>Produto</th><th>Tamanho</th><th>Qtd.</th><th>Preço</th><th>Subtotal</th></tr></thead>
            <tbody>
                <?php foreach ($encomenda['itens'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                        <td><?= htmlspecialchars($item['tamanho'] ?? '—') ?></td>
                        <td><?= $item['quantidade'] ?></td>
                        <td><?= formatarPreco($item['preco_unitario']) ?></td>
                        <td><strong><?= formatarPreco($item['subtotal']) ?></strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);max-width:280px;margin-left:auto;font-size:0.88rem;">
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span>Subtotal</span><span><?= formatarPreco($encomenda['subtotal']) ?></span></div>
            <div style="display:flex;justify-content:space-between;margin-bottom:6px;"><span>Envio</span><span><?= $encomenda['envio'] == 0 ? 'Grátis' : formatarPreco($encomenda['envio']) ?></span></div>
            <?php if ($encomenda['valor_desconto'] > 0): ?>
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;color:#1a7f43;"><span>Desconto</span><span>-<?= formatarPreco($encomenda['valor_desconto']) ?></span></div>
            <?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-weight:800;color:var(--azul-northside);font-size:1.05rem;padding-top:6px;border-top:1px solid var(--border);"><span>Total</span><span><?= formatarPreco($encomenda['total']) ?></span></div>
        </div>
    </div>

    <div>
        <div class="admin-painel">
            <h2>Dados do cliente</h2>
            <p style="font-size:0.88rem;line-height:1.8;margin:0;">
                <strong><?= htmlspecialchars($encomenda['nome_cliente']) ?></strong><br>
                <?= htmlspecialchars($encomenda['email']) ?><br>
                <?= htmlspecialchars($encomenda['telefone']) ?><br><br>
                <?= htmlspecialchars($encomenda['morada']) ?><br>
                <?= htmlspecialchars($encomenda['codigo_postal']) ?> <?= htmlspecialchars($encomenda['cidade']) ?>
            </p>
        </div>

        <div class="admin-painel">
            <h2>Estado da encomenda</h2>
            <form method="post" class="admin-form">
                <select name="novo_estado">
                    <?php foreach (['pendente', 'confirmada', 'enviada', 'entregue', 'cancelada'] as $estado): ?>
                        <option value="<?= $estado ?>" <?= $encomenda['estado'] === $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-northside" style="width:100%;margin-top:14px;">ATUALIZAR ESTADO</button>
            </form>

            <p class="campo-ajuda" style="margin-top:16px;">
                Pagamento: <strong><?= htmlspecialchars($encomenda['metodo_pagamento']) ?></strong><br>
                Email de confirmação: <?= $encomenda['email_enviado'] ? '✅ enviado' : '⚠️ não enviado' ?><br>
                Data: <?= date('d/m/Y H:i', strtotime($encomenda['criado_em'])) ?>
            </p>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
