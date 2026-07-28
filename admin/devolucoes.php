<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Devoluções';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['devolucao_id'])) {
    $estadosValidos = ['pendente', 'aprovada', 'rejeitada', 'concluida'];
    if (in_array($_POST['novo_estado'], $estadosValidos)) {
        $stmt = $pdo->prepare("UPDATE devolucoes SET estado = ? WHERE id = ?");
        $stmt->execute([$_POST['novo_estado'], (int)$_POST['devolucao_id']]);
    }
    header('Location: devolucoes.php?msg=' . urlencode('Devolução atualizada.'));
    exit;
}

$devolucoes = $pdo->query(
    "SELECT d.*, ei.nome_produto, ei.tamanho, ei.quantidade, e.nome_cliente, e.email
     FROM devolucoes d
     JOIN encomenda_itens ei ON ei.id = d.encomenda_item_id
     JOIN encomendas e ON e.id = d.encomenda_id
     ORDER BY d.criado_em DESC"
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Devoluções (<?= count($devolucoes) ?>)</h1></div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

<div class="admin-painel">
    <div class="admin-tabela-scroll">
<table class="admin-tabela">
        <thead>
            <tr><th>Encomenda</th><th>Cliente</th><th>Produto</th><th>Motivo</th><th>Estado</th><th>Data</th><th>Ação</th></tr>
        </thead>
        <tbody>
            <?php foreach ($devolucoes as $dev): ?>
                <tr>
                    <td><a href="encomenda_detalhe.php?id=<?= $dev['encomenda_id'] ?>" class="admin-acoes editar">#<?= str_pad($dev['encomenda_id'], 5, '0', STR_PAD_LEFT) ?></a></td>
                    <td><?= htmlspecialchars($dev['nome_cliente']) ?><br><span class="campo-ajuda"><?= htmlspecialchars($dev['email']) ?></span></td>
                    <td><?= htmlspecialchars($dev['nome_produto']) ?><?= $dev['tamanho'] ? ' (' . htmlspecialchars($dev['tamanho']) . ')' : '' ?> — <?= $dev['quantidade'] ?>×</td>
                    <td style="max-width:220px;"><?= nl2br(htmlspecialchars($dev['motivo'])) ?></td>
                    <td>
                        <?php $cores = ['pendente' => 'pill-alerta', 'aprovada' => 'pill-ok', 'concluida' => 'pill-ok', 'rejeitada' => 'pill-erro']; ?>
                        <span class="pill <?= $cores[$dev['estado']] ?? 'pill-neutro' ?>"><?= htmlspecialchars($dev['estado']) ?></span>
                    </td>
                    <td><?= date('d/m/Y', strtotime($dev['criado_em'])) ?></td>
                    <td>
                        <form method="post" style="display:flex;gap:6px;">
                            <input type="hidden" name="devolucao_id" value="<?= $dev['id'] ?>">
                            <select name="novo_estado" style="padding:5px 8px;font-size:0.78rem;border:1px solid var(--border);border-radius:4px;">
                                <?php foreach (['pendente', 'aprovada', 'rejeitada', 'concluida'] as $estado): ?>
                                    <option value="<?= $estado ?>" <?= $dev['estado'] === $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn-outline-northside" style="padding:5px 10px;font-size:0.72rem;">OK</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($devolucoes)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não há pedidos de devolução.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
