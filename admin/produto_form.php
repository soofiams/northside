<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$produto = null;
$especificacoes = [];
$tamanhosAtuais = [];
$erro = '';

if ($id) {
    $stmt = $pdo->prepare("SELECT * FROM produtos WHERE id = ?");
    $stmt->execute([$id]);
    $produto = $stmt->fetch();
    if (!$produto) { $erro = 'Produto não encontrado.'; }
    else {
        $especificacoes = json_decode($produto['especificacoes'] ?? '{}', true) ?: [];
        foreach (buscarTamanhosProduto($pdo, $id) as $t) $tamanhosAtuais[$t['tamanho']] = $t['stock'];
    }
}

$categorias = buscarCategorias($pdo);
$titulo_admin = $id ? 'Editar produto' : 'Novo produto';
$TAMANHOS_POSSIVEIS = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = (float)str_replace(',', '.', $_POST['preco'] ?? '0');
    $categoria_id = !empty($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    $stock = max(0, (int)($_POST['stock'] ?? 0));
    $destaque = isset($_POST['destaque']) ? 1 : 0;
    $ativo = isset($_POST['ativo']) ? 1 : 0;
    $temTamanhos = isset($_POST['tem_tamanhos']);
    $imagem_final = $produto['imagem'] ?? 'sem-imagem.jpg';

    // especificações: duas listas paralelas (chave[] e valor[]), filtrando linhas vazias
    $espChaves = $_POST['esp_chave'] ?? [];
    $espValores = $_POST['esp_valor'] ?? [];
    $especificacoesFinal = [];
    foreach ($espChaves as $i => $chave) {
        $chave = trim($chave);
        $valor = trim($espValores[$i] ?? '');
        if ($chave !== '' && $valor !== '') $especificacoesFinal[$chave] = $valor;
    }

    if ($nome === '' || $preco <= 0) {
        $erro = 'Preenche pelo menos o nome e um preço válido.';
    } else {
        if (!empty($_FILES['imagem']['name'])) {
            $extensoesPermitidas = ['jpg', 'jpeg', 'png', 'webp'];
            $extensao = strtolower(pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION));
            if (!in_array($extensao, $extensoesPermitidas)) {
                $erro = 'Formato de imagem inválido. Usa jpg, jpeg, png ou webp.';
            } elseif ($_FILES['imagem']['size'] > 5 * 1024 * 1024) {
                $erro = 'A imagem não pode ultrapassar 5MB.';
            } else {
                $novoNome = uniqid('produto_') . '.' . $extensao;
                $destino = __DIR__ . '/../assets/img/produtos/' . $novoNome;
                if (move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
                    $imagem_final = $novoNome;
                } else {
                    $erro = 'Não foi possível guardar a imagem.';
                }
            }
        }

        if (!$erro) {
            $especificacoesJson = json_encode($especificacoesFinal, JSON_UNESCAPED_UNICODE);

            if ($id) {
                $stmt = $pdo->prepare(
                    "UPDATE produtos SET nome=?, descricao=?, preco=?, categoria_id=?, stock=?, destaque=?, ativo=?, imagem=?, especificacoes=? WHERE id=?"
                );
                $stmt->execute([$nome, $descricao, $preco, $categoria_id, $stock, $destaque, $ativo, $imagem_final, $especificacoesJson, $id]);
                $produtoId = $id;
                $mensagem = 'Produto atualizado com sucesso.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO produtos (nome, descricao, preco, categoria_id, stock, destaque, ativo, imagem, especificacoes) VALUES (?,?,?,?,?,?,?,?,?)"
                );
                $stmt->execute([$nome, $descricao, $preco, $categoria_id, $stock, $destaque, $ativo, $imagem_final, $especificacoesJson]);
                $produtoId = (int)$pdo->lastInsertId();
                $mensagem = 'Produto criado com sucesso.';
            }

            // tamanhos: substitui sempre a lista toda pelos valores atuais do formulário
            $pdo->prepare("DELETE FROM produto_tamanhos WHERE produto_id = ?")->execute([$produtoId]);
            if ($temTamanhos) {
                $stmtTamanho = $pdo->prepare("INSERT INTO produto_tamanhos (produto_id, tamanho, stock) VALUES (?, ?, ?)");
                foreach ($TAMANHOS_POSSIVEIS as $tamanho) {
                    $stockTamanho = max(0, (int)($_POST['tamanho_' . $tamanho] ?? 0));
                    $stmtTamanho->execute([$produtoId, $tamanho, $stockTamanho]);
                }
            }

            header('Location: produtos.php?msg=' . urlencode($mensagem));
            exit;
        }
    }

    // se houve erro, mantém os dados no ecrã
    $produto = array_merge($produto ?? [], [
        'nome' => $nome, 'descricao' => $descricao, 'preco' => $preco,
        'categoria_id' => $categoria_id, 'stock' => $stock,
        'destaque' => $destaque, 'ativo' => $ativo, 'imagem' => $imagem_final,
    ]);
    $especificacoes = $especificacoesFinal;
}

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo"><h1><?= $id ? 'Editar produto' : 'Novo produto' ?></h1></div>

<?php if ($erro): ?><div class="alerta alerta-erro"><?= htmlspecialchars($erro) ?></div><?php endif; ?>

<div class="admin-painel" style="max-width:640px;">
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <label>Nome do produto</label>
        <input type="text" name="nome" required value="<?= htmlspecialchars($produto['nome'] ?? '') ?>">

        <label>Descrição</label>
        <textarea name="descricao" rows="3"><?= htmlspecialchars($produto['descricao'] ?? '') ?></textarea>

        <div class="linha-dupla">
            <div>
                <label>Preço (€)</label>
                <input type="text" name="preco" required value="<?= htmlspecialchars($produto['preco'] ?? '') ?>">
            </div>
            <div>
                <label>Categoria</label>
                <select name="categoria_id">
                    <option value="">— Sem categoria —</option>
                    <?php foreach ($categorias as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (($produto['categoria_id'] ?? null) == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <label>Imagem do produto</label>
        <?php if (!empty($produto['imagem']) && $produto['imagem'] !== 'sem-imagem.jpg'): ?>
            <img src="<?= imagemProdutoUrl($produto['imagem']) ?>" style="width:70px;height:70px;object-fit:cover;border-radius:6px;margin-bottom:8px;display:block;">
        <?php endif; ?>
        <input type="file" name="imagem" accept=".jpg,.jpeg,.png,.webp">

        <div class="checkbox-linha">
            <input type="checkbox" name="tem_tamanhos" id="tem_tamanhos" <?= !empty($tamanhosAtuais) ? 'checked' : '' ?>>
            <label for="tem_tamanhos" style="margin:0;">Este produto tem tamanhos (roupa: XS a XXL)</label>
        </div>

        <div id="bloco-stock-simples" style="<?= !empty($tamanhosAtuais) ? 'display:none;' : '' ?>">
            <label>Stock disponível</label>
            <input type="number" name="stock" min="0" value="<?= htmlspecialchars($produto['stock'] ?? 0) ?>">
        </div>

        <div id="bloco-tamanhos" style="<?= empty($tamanhosAtuais) ? 'display:none;' : '' ?>">
            <label>Stock por tamanho</label>
            <div class="grid-tamanhos-stock">
                <?php foreach ($TAMANHOS_POSSIVEIS as $tamanho): ?>
                    <div>
                        <span class="campo-ajuda" style="display:block;margin-bottom:4px;font-weight:700;color:var(--azul-northside);"><?= $tamanho ?></span>
                        <input type="number" name="tamanho_<?= $tamanho ?>" min="0" value="<?= $tamanhosAtuais[$tamanho] ?? 0 ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <label style="margin-top:20px;">Especificações (garantia, material, etc.)</label>
        <div id="lista-especificacoes">
            <?php if (empty($especificacoes)) $especificacoes = ['' => '']; ?>
            <?php foreach ($especificacoes as $chave => $valor): ?>
                <div class="linha-repetivel">
                    <input type="text" name="esp_chave[]" placeholder="Ex: Garantia" value="<?= htmlspecialchars($chave === 0 ? '' : $chave) ?>">
                    <input type="text" name="esp_valor[]" placeholder="Ex: 24 meses" value="<?= htmlspecialchars($valor) ?>">
                    <button type="button" onclick="this.parentElement.remove()">✕</button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="btn-adicionar-linha" id="btn-add-especificacao">+ Adicionar especificação</button>

        <div class="checkbox-linha">
            <input type="checkbox" name="destaque" id="destaque" <?= !empty($produto['destaque']) ? 'checked' : '' ?>>
            <label for="destaque" style="margin:0;">Mostrar em "Destaques" na página inicial</label>
        </div>
        <div class="checkbox-linha">
            <input type="checkbox" name="ativo" id="ativo" <?= ($produto['ativo'] ?? 1) ? 'checked' : '' ?>>
            <label for="ativo" style="margin:0;">Produto ativo (visível na loja)</label>
        </div>

        <div style="margin-top:24px;display:flex;gap:12px;">
            <button type="submit" class="btn-northside"><?= $id ? 'GUARDAR ALTERAÇÕES' : 'CRIAR PRODUTO' ?></button>
            <a href="produtos.php" class="btn-outline-northside">CANCELAR</a>
        </div>
    </form>
</div>

<script>
document.getElementById('tem_tamanhos').addEventListener('change', function () {
    document.getElementById('bloco-stock-simples').style.display = this.checked ? 'none' : '';
    document.getElementById('bloco-tamanhos').style.display = this.checked ? '' : 'none';
});

document.getElementById('btn-add-especificacao').addEventListener('click', function () {
    const div = document.createElement('div');
    div.className = 'linha-repetivel';
    div.innerHTML = '<input type="text" name="esp_chave[]" placeholder="Ex: Garantia">' +
        '<input type="text" name="esp_valor[]" placeholder="Ex: 24 meses">' +
        '<button type="button" onclick="this.parentElement.remove()">✕</button>';
    document.getElementById('lista-especificacoes').appendChild(div);
});
</script>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
