<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Definições';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $definicoes = [
        'contacto_email' => trim($_POST['contacto_email'] ?? ''),
        'contacto_telefone' => trim($_POST['contacto_telefone'] ?? ''),
        'envio_gratis_acima_de' => (string)(float)str_replace(',', '.', $_POST['envio_gratis_acima_de'] ?? '0'),
        'envio_custo' => (string)(float)str_replace(',', '.', $_POST['envio_custo'] ?? '0'),
        'newsletter_desconto_percentagem' => (string)((float)str_replace(',', '.', $_POST['newsletter_desconto_percentagem'] ?? '10') / 100),
        'rede_instagram' => trim($_POST['rede_instagram'] ?? ''),
        'rede_facebook' => trim($_POST['rede_facebook'] ?? ''),
        'rede_tiktok' => trim($_POST['rede_tiktok'] ?? ''),
    ];

    $stmt = $pdo->prepare("INSERT INTO definicoes (chave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    foreach ($definicoes as $chave => $valor) {
        $stmt->execute([$chave, $valor]);
    }

    header('Location: definicoes.php?msg=' . urlencode('Definições guardadas.'));
    exit;
}

$contactoEmail = buscarDefinicao($pdo, 'contacto_email', 'apoio@northside.pt');
$contactoTelefone = buscarDefinicao($pdo, 'contacto_telefone', '+351 900 000 000');
$custosEnvio = buscarCustosEnvio($pdo);
$newsletterPercentagem = round((float)buscarDefinicao($pdo, 'newsletter_desconto_percentagem', '0.10') * 100);
$redeInstagram = buscarDefinicao($pdo, 'rede_instagram', '');
$redeFacebook = buscarDefinicao($pdo, 'rede_facebook', '');
$redeTiktok = buscarDefinicao($pdo, 'rede_tiktok', '');

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Definições</h1></div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

<div class="admin-painel" style="max-width:520px;">
    <h2>Contactos</h2>
    <p class="campo-ajuda" style="margin-bottom:16px;">Aparecem no modal "Contactos" do site, disponível em todas as páginas.</p>

    <form method="post" class="admin-form">
        <label>Email de apoio ao cliente</label>
        <input type="email" name="contacto_email" required value="<?= htmlspecialchars($contactoEmail) ?>">

        <label>Telemóvel de apoio ao cliente</label>
        <input type="tel" name="contacto_telefone" required value="<?= htmlspecialchars($contactoTelefone) ?>">

        <h2 style="margin-top:28px;">Envio</h2>
        <label>Portes grátis a partir de (€)</label>
        <input type="text" name="envio_gratis_acima_de" required value="<?= htmlspecialchars($custosEnvio['gratis_acima_de']) ?>">

        <label>Custo de envio (€)</label>
        <input type="text" name="envio_custo" required value="<?= htmlspecialchars($custosEnvio['custo']) ?>">

        <h2 style="margin-top:28px;">Newsletter</h2>
        <label>Desconto de boas-vindas (%)</label>
        <input type="text" name="newsletter_desconto_percentagem" required value="<?= $newsletterPercentagem ?>">
        <span class="campo-ajuda">Percentagem do código enviado automaticamente a quem subscreve a newsletter pela primeira vez.</span>

        <h2 style="margin-top:28px;">Redes sociais</h2>
        <p class="campo-ajuda" style="margin-bottom:8px;">Os links dos ícones no rodapé do site.</p>

        <label>Instagram</label>
        <input type="text" name="rede_instagram" placeholder="https://instagram.com/..." value="<?= htmlspecialchars($redeInstagram) ?>">

        <label>Facebook</label>
        <input type="text" name="rede_facebook" placeholder="https://facebook.com/..." value="<?= htmlspecialchars($redeFacebook) ?>">

        <label>TikTok</label>
        <input type="text" name="rede_tiktok" placeholder="https://tiktok.com/@..." value="<?= htmlspecialchars($redeTiktok) ?>">

        <button type="submit" class="btn-northside" style="margin-top:22px;">GUARDAR DEFINIÇÕES</button>
    </form>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
