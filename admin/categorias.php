<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Categorias';

function gerarSlug(string $texto): string {
    $texto = normalizarTexto($texto);
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    return trim($texto, '-');
}

// Eliminar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $pdo->prepare("DELETE FROM categorias WHERE id = ?")->execute([(int)$_POST['eliminar_id']]);
    header('Location: categorias.php?msg=' . urlencode('Categoria eliminada.'));
    exit;
}

// Criar ou atualizar
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nome = trim($_POST['nome']);
    $ordem = (int)($_POST['ordem'] ?? 0);
    $slug = !empty($_POST['slug']) ? gerarSlug($_POST['slug']) : gerarSlug($nome);

    if ($nome === '' || $slug === '') {
        $erro = 'Introduz um nome válido para a categoria.';
    } else {
        try {
            if ($id) {
                $stmt = $pdo->prepare("UPDATE categorias SET nome=?, slug=?, ordem=? WHERE id=?");
                $stmt->execute([$nome, $slug, $ordem, $id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO categorias (nome, slug, ordem) VALUES (?,?,?)");
                $stmt->execute([$nome, $slug, $ordem]);
            }
            header('Location: categorias.php?msg=' . urlencode('Categoria guardada.'));
            exit;
        } catch (PDOException $e) {
            $erro = str_contains($e->getMessage(), 'Duplicate') ? 'Já existe uma categoria com esse link (slug).' : 'Não foi possível guardar.';
        }
    }
}

$categorias = $pdo->query(
    "SELECT c.*, (SELECT COUNT(*) FROM produtos p WHERE p.categoria_id = c.id) AS total_produtos
     FROM categorias c ORDER BY c.ordem ASC, c.nome ASC"
)->fetchAll();

$emEdicao = null;
if (!empty($_GET['editar'])) {
    foreach ($categorias as $c) if ($c['id'] == (int)$_GET['editar']) $emEdicao = $c;
}

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1>Categorias</h1></div>
<p class="campo-ajuda" style="margin:-14px 0 24px;">Estas categorias aparecem na navbar do site, pela ordem que definires aqui.</p>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>
<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="admin-duas-colunas admin-duas-colunas-desconto">

    <div class="admin-painel">
        <h2>Ordem na navbar</h2>
        <div class="admin-tabela-scroll">
        <table class="admin-tabela">
            <thead><tr><th>Ordem</th><th>Nome</th><th>Link (slug)</th><th>Produtos</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($categorias as $c): ?>
                    <tr>
                        <td><strong><?= $c['ordem'] ?></strong></td>
                        <td><?= htmlspecialchars($c['nome']) ?></td>
                        <td><span class="campo-ajuda">loja.php?categoria=<?= htmlspecialchars($c['slug']) ?></span></td>
                        <td><?= $c['total_produtos'] ?></td>
                        <td class="admin-acoes">
                            <a href="categorias.php?editar=<?= $c['id'] ?>" class="editar">Editar</a>
                            <form method="post" onsubmit="return confirm('Eliminar esta categoria? Os produtos ficam sem categoria, não são eliminados.');">
                                <input type="hidden" name="eliminar_id" value="<?= $c['id'] ?>">
                                <button type="submit" class="eliminar">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($categorias)): ?>
                    <tr><td colspan="5" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não tens categorias.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="admin-painel">
        <h2><?= $emEdicao ? 'Editar categoria' : 'Nova categoria' ?></h2>
        <form method="post" class="admin-form">
            <?php if ($emEdicao): ?><input type="hidden" name="id" value="<?= $emEdicao['id'] ?>"><?php endif; ?>

            <label>Nome (como aparece na navbar)</label>
            <input type="text" name="nome" required value="<?= htmlspecialchars($emEdicao['nome'] ?? '') ?>">

            <label>Link (slug — deixa em branco para gerar automaticamente)</label>
            <input type="text" name="slug" placeholder="ex: eletronicos" value="<?= htmlspecialchars($emEdicao['slug'] ?? '') ?>">
            <span class="campo-ajuda">Só letras minúsculas, números e traços — usado no link da categoria.</span>

            <label>Ordem</label>
            <input type="number" name="ordem" value="<?= $emEdicao['ordem'] ?? (count($categorias) + 1) ?>">
            <span class="campo-ajuda">Números mais baixos aparecem primeiro na navbar.</span>

            <div style="margin-top:20px;display:flex;gap:12px;">
                <button type="submit" class="btn-northside"><?= $emEdicao ? 'GUARDAR' : 'CRIAR CATEGORIA' ?></button>
                <?php if ($emEdicao): ?><a href="categorias.php" class="btn-outline-northside">CANCELAR</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
