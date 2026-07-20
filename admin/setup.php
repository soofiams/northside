<?php
/**
 * Corre este ficheiro UMA VEZ para criares a tua conta de administrador.
 * Depois de a criares, APAGA este ficheiro do servidor por segurança.
 */
require_once __DIR__ . '/../config.php';

$erro = '';
$sucesso = '';

// Impede criar uma segunda conta por aqui depois de já existir uma
$existe_admin = $pdo->query("SELECT COUNT(*) AS n FROM admins")->fetch()['n'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existe_admin) {
    $utilizador = trim($_POST['utilizador'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($utilizador) < 3 || strlen($password) < 6) {
        $erro = 'Utilizador (mín. 3 caracteres) e password (mín. 6 caracteres) são obrigatórios.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (utilizador, password_hash) VALUES (?, ?)");
        $stmt->execute([$utilizador, $hash]);
        $sucesso = 'Conta de administrador criada! Podes agora fazer login e deves apagar este ficheiro (admin/setup.php).';
        $existe_admin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Configuração inicial — Northside Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-wrap" style="max-width:420px;">
    <h2 class="secao-titulo" style="text-align:left;margin-left:0;">CONFIGURAÇÃO INICIAL</h2>

    <?php if ($sucesso): ?>
        <div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
        <a href="login.php" class="btn-northside">IR PARA O LOGIN</a>
    <?php elseif ($existe_admin): ?>
        <div class="alerta alerta-erro">Já existe uma conta de administrador. Por segurança, apaga este ficheiro (admin/setup.php).</div>
        <a href="login.php" class="btn-northside">IR PARA O LOGIN</a>
    <?php else: ?>
        <?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <label>Utilizador</label>
            <input type="text" name="utilizador" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn-northside" style="margin-top:20px;">CRIAR CONTA DE ADMIN</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
