<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Chat';

$conversaId = (int)($_GET['id'] ?? 0);
$conversa = buscarConversaChat($pdo, $conversaId);
if (!$conversa) {
    header('Location: chat.php');
    exit;
}

marcarMensagensComoLidas($pdo, $conversaId);
$conversas = listarConversasChat($pdo);
$mensagens = buscarMensagensChat($pdo, $conversaId);

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Chat com clientes</h1></div>

<div class="admin-chat-shell">
    <div class="admin-chat-lista">
        <?php foreach ($conversas as $c): ?>
            <a href="chat_conversa.php?id=<?= $c['id'] ?>" class="admin-chat-item <?= $c['id'] == $conversaId ? 'ativo' : '' ?>">
                <div class="nome"><?= htmlspecialchars($c['nome']) ?><?php if ($c['nao_lidas'] > 0 && $c['id'] != $conversaId): ?><span class="badge-contagem" style="margin-left:8px;"><?= $c['nao_lidas'] ?></span><?php endif; ?></div>
                <div class="email"><?= htmlspecialchars($c['email']) ?></div>
                <div class="previa"><?= htmlspecialchars($c['ultima_mensagem'] ?? '') ?></div>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="admin-chat-corpo">
        <div class="chat-cabecalho" style="background:var(--azul-northside);">
            <span><?= htmlspecialchars($conversa['nome']) ?> · <?= htmlspecialchars($conversa['email']) ?></span>
        </div>
        <div class="admin-chat-mensagens" id="admin-chat-mensagens" data-conversa-id="<?= $conversaId ?>" data-ultimo-id="<?= !empty($mensagens) ? end($mensagens)['id'] : 0 ?>">
            <?php foreach ($mensagens as $m): ?>
                <div class="chat-bolha <?= $m['remetente'] ?>">
                    <?= nl2br(htmlspecialchars($m['mensagem'])) ?>
                    <span class="hora"><?= date('H:i', strtotime($m['criado_em'])) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
        <form class="chat-form-enviar" id="admin-chat-form-enviar">
            <input type="text" id="admin-chat-input" placeholder="Escreve a tua resposta..." autocomplete="off">
            <button type="submit"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>
</div>

<script>
(function () {
    const painel = document.getElementById('admin-chat-mensagens');
    const conversaId = painel.dataset.conversaId;
    let ultimoId = parseInt(painel.dataset.ultimoId, 10) || 0;
    painel.scrollTop = painel.scrollHeight;

    function renderizarMensagem(m) {
        const bolha = document.createElement('div');
        bolha.className = 'chat-bolha ' + m.remetente;
        bolha.innerHTML = m.mensagem + '<span class="hora">' + m.hora + '</span>';
        painel.appendChild(bolha);
        painel.scrollTop = painel.scrollHeight;
        if (m.id > ultimoId) ultimoId = m.id;
    }

    function irBuscarNovas() {
        fetch('<?= URL_BASE ?>actions/chat_mensagens.php?conversa_id=' + conversaId + '&depois_de=' + ultimoId)
            .then(function (r) { return r.json(); })
            .then(function (resposta) {
                if (resposta.sucesso) resposta.mensagens.forEach(renderizarMensagem);
            });
    }
    setInterval(irBuscarNovas, 4000);

    document.getElementById('admin-chat-form-enviar').addEventListener('submit', function (e) {
        e.preventDefault();
        const input = document.getElementById('admin-chat-input');
        const texto = input.value.trim();
        if (!texto) return;

        renderizarMensagem({ id: ultimoId, remetente: 'northside', mensagem: texto, hora: new Date().toTimeString().slice(0, 5) });
        input.value = '';

        const dados = new FormData();
        dados.append('conversa_id', conversaId);
        dados.append('mensagem', texto);
        fetch('<?= URL_BASE ?>admin/chat_responder.php', { method: 'POST', body: dados });
    });
})();
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
