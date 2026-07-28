<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Códigos de Desconto';

// Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $pdo->prepare("DELETE FROM codigos_desconto WHERE id = ?")->execute([(int)$_POST['eliminar_id']]);
    header('Location: descontos.php?msg=' . urlencode('Código eliminado.'));
    exit;
}

// Criar ou atualizar
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['codigo'])) {
    $id = (int)($_POST['id'] ?? 0);
    $codigo = mb_strtoupper(trim($_POST['codigo']));
    $percentagem = (float)str_replace(',', '.', $_POST['percentagem'] ?? '0') / 100;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $validade = !empty($_POST['validade']) ? $_POST['validade'] : null;

    if ($codigo === '' || $percentagem <= 0 || $percentagem > 1) {
        $erro = 'Introduz um código e uma percentagem válida (entre 1 e 100).';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE codigos_desconto SET codigo=?, percentagem=?, ativo=?, validade=? WHERE id=?");
                $stmt->execute([$codigo, $percentagem, $ativo, $validade, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO codigos_desconto (codigo, percentagem, ativo, validade) VALUES (?,?,?,?)");
                $stmt->execute([$codigo, $percentagem, $ativo, $validade]);
            }
            header('Location: descontos.php?msg=' . urlencode('Código guardado.'));
            exit;
        } catch (PDOException $e) {
            $erro = str_contains($e->getMessage(), 'Duplicate') ? 'Já existe um código com esse nome.' : 'Não foi possível guardar.';
        }
    }
}

$codigos = $pdo->query("SELECT * FROM codigos_desconto ORDER BY criado_em DESC")->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Códigos de Desconto</h1></div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="admin-duas-colunas admin-duas-colunas-desconto">

    <div class="admin-painel">
        <h2>Códigos ativos</h2>
        <div class="admin-tabela-scroll">
<table class="admin-tabela">
            <thead><tr><th>Código</th><th>Desconto</th><th>Validade</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($codigos as $c): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($c['codigo']) ?></strong></td>
                        <td><?= round($c['percentagem'] * 100) ?>%</td>
                        <td><?= $c['validade'] ? date('d/m/Y', strtotime($c['validade'])) : '— sem validade —' ?></td>
                        <td><span class="pill <?= $c['ativo'] ? 'pill-ok' : 'pill-neutro' ?>"><?= $c['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                        <td class="admin-acoes">
                            <a href="descontos.php?editar=<?= $c['id'] ?>" class="editar">Editar</a>
                            <form method="post" onsubmit="return confirm('Eliminar este código?');">
                                <input type="hidden" name="eliminar_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($codigos)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não tens códigos.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
</div>
    </div>

    <div class="admin-painel">
        <?php
            $emEdicao = null;
            if (!empty($_GET['editar'])) {
                foreach ($codigos as $c) if ($c['id'] == (int)$_GET['editar']) $emEdicao = $c;
            }
        ?>
        <h2><?= $emEdicao ? 'Editar código' : 'Novo código' ?></h2>
        <form method="post" class="admin-form">
            <?php if ($emEdicao): ?><input type="hidden" name="id" value="<?= $emEdicao['id'] ?>"><?php endif; ?>

            <label>Código</label>
            <input type="text" name="codigo" required style="text-transform:uppercase;" value="<?= htmlspecialchars($emEdicao['codigo'] ?? '') ?>">

            <label>Percentagem de desconto (%)</label>
            <input type="text" name="percentagem" required value="<?= $emEdicao ? round($emEdicao['percentagem'] * 100) : '' ?>">

            <label>Validade (opcional)</label>
            <input type="date" name="validade" value="<?= $emEdicao['validade'] ?? '' ?>">

            <div class="checkbox-linha">
                <input type="checkbox" name="ativo" id="ativo-desconto" <?= ($emEdicao['ativo'] ?? 1) ? 'checked' : '' ?>>
                <label for="ativo-desconto" style="margin:0;">Código ativo</label>
            </div>

            <div style="margin-top:20px;display:flex;gap:12px;">
                <button type="submit" class="btn-northside"><?= $emEdicao ? 'GUARDAR' : 'CRIAR CÓDIGO' ?></button>
                <?php if ($emEdicao): ?><a href="descontos.php" class="btn-outline-northside">CANCELAR</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
