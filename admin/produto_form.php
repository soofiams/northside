<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$produto = null;
$erro = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    if (!$produto) { $erro = 'Produto não encontrado.'; }
}

$categorias = buscar_categorias($pdo);
$titulo_pagina = $id ? 'Editar produto' : 'Novo produto';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = (float)str_replace(',', '.', $_POST['preco'] ?? '0');
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $stock = max(0, (int)($_POST['stock'] ?? 0));
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $imagem_final = $produto['imagem'] ?? 'sem-imagem.jpg';

    if ($nome === '' || $preco <= 0) {
        $erro = 'Preenche pelo menos o nome e um preço válido.';
    } else {
        // Upload de imagem, se foi escolhida uma
        if (!empty($_FILES['imagem']['name'])) {
            $extensoes_permitidas = ['jpg', 'jpeg', 'png', 'webp'];
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (!in_array($extensao, $extensoes_permitidas)) {
                $erro = 'Formato de imagem inválido. Usa jpg, jpeg, png ou webp.';
            } elseif ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                $erro = 'A imagem não pode ultrapassar 5MB.';
            } else {
                $novo_nome = uniqid('produto_') . '.' . $extensao;
                $destino = __DIR__ . '/../assets/img/produtos/' . $novo_nome;
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                    $imagem_final = $novo_nome;
                } else {
                    $erro = 'Não foi possível guardar a imagem.';
                }
            }
        }

        if (!$erro) {
            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE produtos SET nome=?, descricao=?, preco=?, categoria_id=?, stock=?, destaque=?, ativo=?, imagem=? WHERE id=?"
                );
                $stmt->execute([$nome, $descricao, $preco, $categoria_id, $stock, $destaque, $ativo, $imagem_final, $id]);
                $mensagem = 'Produto atualizado com sucesso.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO produtos (nome, descricao, preco, categoria_id, stock, destaque, ativo, imagem) VALUES (?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$nome, $descricao, $preco, $categoria_id, $stock, $destaque, $ativo, $imagem_final]);
                $mensagem = 'Produto criado com sucesso.';
            }
            header('Location: index.php?msg=' . urlencode($mensagem));
            exit;
        }
    }

    // se houve erro, mantém os dados no ecrã
    $produto = array_merge($produto ?? [], [
        'nome' => $nome, 'descricao' => $descricao, 'preco' => $preco,
        'categoria_id' => $categoria_id, 'stock' => $stock,
        'destaque' => $destaque, 'ativo' => $ativo, 'imagem' => $imagem_final,
    ]);
}

require __DIR__ . '/includes/admin_header.php';
?>

<h2><?= $id ? 'Editar produto' : 'Novo produto' ?></h2>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<form method="post" enctype="multipart/form-data" class="admin-form" style="max-width:520px;">
    <label>Nome do produto</label>
    <input type="text" name="nome" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>">

    <label>Descrição</label>
    <textarea name="descricao" rows="4"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>

    <label>Preço (€)</label>
    <input type="text" name="preco" required value="<?= htmlspecialchars($produto['preco'] ?? '') ?>">

    <label>Categoria</label>
    <select name="categoria_id">
        <option value="">— Sem categoria —</option>
        <?php foreach ($categorias as $c): ?>
            <option value="<?= $c['id'] ?>" <?= (($produto['categoria_id'] ?? null) == $c['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Stock disponível</label>
    <input type="number" name="stock" min="0" required value="<?= htmlspecialchars($produto['stock'] ?? 0) ?>">

    <label>Imagem do produto</label>
    <?php if (!empty($produto['imagem']) && $produto['imagem'] !== 'sem-imagem.jpg'): ?>
        <img src="<?= imagem_produto_url($produto['imagem']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;">
    <?php endif; ?>
    <input type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp">

    <label style="display:flex;align-items:center;gap:8px;margin-top:16px;">
        <input type="checkbox" name="destaque" style="width:auto;" <?= !empty($produto['destaque']) ? 'checked' : '' ?>>
        Mostrar em "Destaques" na página inicial
    </label>

    <label style="display:flex;align-items:center;gap:8px;">
        <input type="checkbox" name="ativo" style="width:auto;" <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
        Produto ativo (visível na loja)
    </label>

    <div style="margin-top:24px;display:flex;gap:12px;">
        <button type="submit" class="btn-northside"><?= $id ? 'GUARDAR ALTERAÇÕES' : 'CRIAR PRODUTO' ?></button>
        <a href="index.php" class="btn-outline-northside">CANCELAR</a>
    </div>
</form>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
