<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Chat';

$conversas = listarConversasChat($pdo);

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Chat com clientes</h1></div>

<div class="admin-chat-shell">
    <div class="admin-chat-lista">
        <?php foreach ($conversas as $c): ?>
            <a href="chat_conversa.php?id=<?= $c['id'] ?>" class="admin-chat-item">
                <div class="nome"><?= htmlspecialchars($c['nome']) ?><?php if ($c['nao_lidas'] > 0): ?><span class="badge-contagem" style="margin-left:8px;"><?= $c['nao_lidas'] ?></span><?php endif; ?></div>
                <div class="email"><?= htmlspecialchars($c['email']) ?></div>
                <div class="previa"><?= htmlspecialchars($c['ultima_mensagem'] ?? '') ?></div>
            </a>
        <?php endforeach; ?>
        <?php if (empty($conversas)): ?>
            <div style="padding:20px;color:var(--cinza-texto);font-size:0.85rem;">Ainda não há conversas.</div>
        <?php endif; ?>
    </div>
    <div class="admin-chat-corpo">
        <div class="admin-chat-vazio">Escolhe uma conversa à esquerda para veres as mensagens.</div>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
