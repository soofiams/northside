<?php
require_once __DIR__ . '/../config.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilizador = trim($_POST['utilizador'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE utilizador = ?");
    $stmt->execute([$utilizador]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_utilizador'] = $admin['utilizador'];
        header('Location: index.php');
        exit;
    }
    $erro = 'Utilizador ou password incorretos.';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Login — Northside Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-login-wrap">
    <div class="admin-login-caixa">
        <div class="logo-northside">NORTHSIDE<small style="display:block;">BACKOFFICE</small></div>
        <?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
        <form method="post" class="admin-form">
            <label>Utilizador</label>
            <input type="text" name="utilizador" required autofocus>
            <label>Password</label>
            <input type="password" name="password" required>
            <button type="submit" class="btn-northside" style="width:100%;margin-top:20px;">ENTRAR</button>
        </form>
    </div>
</div>
</body>
</html>
