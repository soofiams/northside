<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Avaliações';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if (isset($_POST['eliminar'])) {
        $pdo->prepare("DELETE FROM avaliacoes WHERE id = ?")->execute([$id]);
        header('Location: avaliacoes.php?msg=' . urlencode('Avaliação eliminada.'));
        exit;
    }
    if (isset($_POST['alternar_aprovado'])) {
        $pdo->prepare("UPDATE avaliacoes SET aprovado = 1 - aprovado WHERE id = ?")->execute([$id]);
        header('Location: avaliacoes.php?msg=' . urlencode('Estado atualizado.'));
        exit;
    }
}

$avaliacoes = $pdo->query(
    "SELECT a.*, p.nome AS produto_nome
     FROM avaliacoes a LEFT JOIN produtos p ON p.id = a.produto_id
     ORDER BY a.criado_em DESC"
)->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Avaliações (<?= count($avaliacoes) ?>)</h1></div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

<div class="admin-painel">
    <div class="admin-tabela-scroll">
<table class="admin-tabela">
        <thead><tr><th>Cliente</th><th>Produto</th><th>Estrelas</th><th>Comentário</th><th>Estado</th><th>Data</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($avaliacoes as $av): ?>
                <tr>
                    <td><?= htmlspecialchars($av['nome_cliente']) ?></td>
                    <td><?= htmlspecialchars($av['produto_nome'] ?? 'Avaliação geral') ?></td>
                    <td><?= estrelasHtml($av['estrelas']) ?></td>
                    <td style="max-width:280px;"><?= htmlspecialchars($av['comentario']) ?></td>
                    <td><span class="pill <?= $av['aprovado'] ? 'pill-ok' : 'pill-neutro' ?>"><?= $av['aprovado'] ? 'Visível' : 'Oculta' ?></span></td>
                    <td><?= date('d/m/Y', strtotime($av['criado_em'])) ?></td>
                    <td class="admin-acoes">
                        <form method="post"><input type="hidden" name="id" value="<?= $av['id'] ?>"><button type="submit" name="alternar_aprovado" value="1" class="editar"><?= $av['aprovado'] ? 'Ocultar' : 'Mostrar' ?></button></form>
                        <form method="post" onsubmit="return confirm('Eliminar esta avaliação?');"><input type="hidden" name="id" value="<?= $av['id'] ?>"><button type="submit" name="eliminar" value="1" class="eliminar">Eliminar</button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($avaliacoes)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não há avaliações.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
