<?php
require_once __DIR__ . '/../config.php';

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php?msg=' . urlencode('Produto eliminado.'));
exit;
