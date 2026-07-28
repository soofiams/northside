<?php
/**
 * Corre este ficheiro UMA VEZ para criares a tua conta de administrador.
 * Depois de a criares, apaga este ficheiro do servidor por segurança.
 */
require_once __DIR__ . '/../config.php';

$erro = '';
$sucesso = '';
$existeAdmin = $pdo->query("SELECT COUNT(*) AS n FROM admins")->fetch()['n'] > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$existeAdmin) {
    $utilizador = trim($_POST['utilizador'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($utilizador) < 3 || strlen($password) < 6) {
        $erro = 'Utilizador (mín. 3 caracteres) e password (mín. 6 caracteres) são obrigatórios.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admins (utilizador, password_hash) VALUES (?, ?)");
        $stmt->execute([$utilizador, $hash]);
        $sucesso = 'Conta criada! Podes já fazer login — e deves apagar este ficheiro (admin/setup.php) do servidor.';
        $existeAdmin = true;
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Configuração inicial — Northside Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-login-wrap">
    <div class="admin-login-caixa">
        <div class="logo-northside">NORTHSIDE<small style="display:block;">CONFIGURAÇÃO INICIAL</small></div>

        <?php if ($sucesso): ?>
            <div class="alerta alerta-sucesso"><?= htmlspecialchars($sucesso) ?></div>
            <a href="login.php" class="btn-northside" style="width:100%;text-align:center;display:block;margin-top:10px;">IR PARA O LOGIN</a>
        <?php elseif ($existeAdmin): ?>
            <div class="alerta alerta-erro">Já existe uma conta de administrador. Por segurança, apaga este ficheiro (admin/setup.php).</div>
            <a href="login.php" class="btn-northside" style="width:100%;text-align:center;display:block;margin-top:10px;">IR PARA O LOGIN</a>
        <?php else: ?>
            <?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
            <form method="post" class="admin-form">
                <label>Utilizador</label>
                <input type="text" name="utilizador" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit" class="btn-northside" style="width:100%;margin-top:20px;">CRIAR CONTA</button>
            </form>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
