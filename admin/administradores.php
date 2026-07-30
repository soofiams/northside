<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Administradores';

$erro = '';

// Eliminar um administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $idEliminar = (int)$_POST['eliminar_id'];
    $totalAdmins = (int)$pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

    if ($idEliminar === (int)$_SESSION['admin_id']) {
        $erro = 'Não podes eliminar a tua própria conta enquanto tens sessão iniciada.';
    } elseif ($totalAdmins <= 1) {
        $erro = 'Tem de existir sempre pelo menos um administrador.';
    } else {
        $pdo->prepare("DELETE FROM admins WHERE id = ?")->execute([$idEliminar]);
        header('Location: administradores.php?msg=' . urlencode('Administrador removido.'));
        exit;
    }
}

// Criar um novo administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['utilizador'])) {
    $utilizador = trim($_POST['utilizador']);
    $password = $_POST['password'] ?? '';
    $confirmarPassword = $_POST['confirmar_password'] ?? '';

    if (strlen($utilizador) < 3) {
        $erro = 'O utilizador tem de ter pelo menos 3 caracteres.';
    } elseif (strlen($password) < 6) {
        $erro = 'A password tem de ter pelo menos 6 caracteres.';
    } elseif ($password !== $confirmarPassword) {
        $erro = 'As passwords não coincidem.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (utilizador, password_hash) VALUES (?, ?)");
            $stmt->execute([$utilizador, $hash]);
            header('Location: administradores.php?msg=' . urlencode('Administrador criado com sucesso.'));
            exit;
        } catch (PDOException $e) {
            $erro = str_contains($e->getMessage(), 'Duplicate') ? 'Já existe um administrador com esse utilizador.' : 'Não foi possível criar a conta.';
        }
    }
}

$admins = $pdo->query("SELECT * FROM admins ORDER BY criado_em ASC")->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Administradores</h1></div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="admin-duas-colunas admin-duas-colunas-desconto">

    <div class="admin-painel">
        <h2>Contas existentes</h2>
        <div class="admin-tabela-scroll">
        <table class="admin-tabela">
            <thead><tr><th>Utilizador</th><th>Criado em</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($admins as $a): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($a['utilizador']) ?></strong>
                            <?php if ($a['id'] === (int)$_SESSION['admin_id']): ?><span class="pill pill-ok" style="margin-left:8px;">Tu</span><?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($a['criado_em'])) ?></td>
                        <td class="admin-acoes">
                            <?php if ($a['id'] !== (int)$_SESSION['admin_id'] && count($admins) > 1): ?>
                                <form method="post" onsubmit="return confirm('Eliminar o administrador \'<?= htmlspecialchars($a['utilizador']) ?>\'?');">
                                    <input type="hidden" name="eliminar_id" value="<?= $a['id'] ?>">
                                    <button type="submit" class="eliminar">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="admin-painel">
        <h2>Novo administrador</h2>
        <form method="post" class="admin-form">
            <label>Utilizador</label>
            <input type="text" name="utilizador" required>

            <label>Password</label>
            <input type="password" name="password" required>

            <label>Confirmar password</label>
            <input type="password" name="confirmar_password" required>

            <button type="submit" class="btn-northside" style="margin-top:20px;">CRIAR ADMINISTRADOR</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
