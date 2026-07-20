<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_pagina = 'Produtos';

$stmt = $pdo->query(
    "SELECT p.*, c.nome AS categoria_nome
     FROM produtos p
     LEFT JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.criado_em DESC"
);
$produtos = $stmt->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin:20px 0;">
    <h2 style="margin:0;">Produtos (<?= count($produtos) ?>)</h2>
    <a href="produto_form.php" class="btn-northside">+ NOVO PRODUTO</a>
</div>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<table class="admin-tabela">
    <thead>
        <tr>
            <th>Imagem</th>
            <th>Nome</th>
            <th>Categoria</th>
            <th>Preço</th>
            <th>Stock</th>
            <th>Destaque</th>
            <th>Ativo</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $p): ?>
        <tr>
            <td><img src="<?= imagem_produto_url($p['imagem']) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:4px;background:#f5f6f8;"></td>
            <td><?= htmlspecialchars($p['nome']) ?></td>
            <td><?= htmlspecialchars($p['categoria_nome'] ?? '—') ?></td>
            <td><?= formatar_preco($p['preco']) ?></td>
            <td>
                <?php if ($p['stock'] > 0): ?>
                    <span class="pill pill-ok"><?= $p['stock'] ?> un.</span>
                <?php else: ?>
                    <span class="pill pill-esgotado">Esgotado</span>
                <?php endif; ?>
            </td>
            <td><?= $p['destaque'] ? '⭐' : '—' ?></td>
            <td><?= $p['ativo'] ? '✅' : '🚫' ?></td>
            <td style="white-space:nowrap;">
                <a href="produto_form.php?id=<?= $p['id'] ?>">Editar</a>
                &nbsp;·&nbsp;
                <form action="produto_delete.php" method="post" style="display:inline;" onsubmit="return confirm('Tens a certeza que queres eliminar este produto?');">
                    <input type="hidden" name="id" value="<?= $p['id'] ?>">
                    <button type="submit" class="btn-remover" style="padding:0;">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($produtos)): ?>
        <tr><td colspan="8" style="text-align:center;padding:30px;">Ainda não tens produtos. Cria o primeiro!</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
