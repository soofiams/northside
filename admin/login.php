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
    } else {
        $erro = 'Utilizador ou password incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
<meta charset="UTF-8">
<title>Login — Northside Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-wrap" style="max-width:380px;">
    <h2 class="secao-titulo" style="text-align:left;margin-left:0;">LOGIN ADMIN</h2>
    <?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>
    <form method="post" class="admin-form">
        <label>Utilizador</label>
        <input type="text" name="utilizador" required autofocus>
        <label>Password</label>
        <input type="password" name="password" required>
        <button type="submit" class="btn-northside" style="margin-top:20px;">ENTRAR</button>
    </form>
</div>
</body>
</html>
