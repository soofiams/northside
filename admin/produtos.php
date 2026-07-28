<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
$titulo_admin = 'Produtos';

$stmt = $pdo->query(
    "SELECT p.*, c.nome AS categoria_nome,
        (SELECT COALESCE(SUM(stock),0) FROM produto_tamanhos pt WHERE pt.produto_id = p.id) AS stock_tamanhos,
        (SELECT COUNT(*) FROM produto_tamanhos pt WHERE pt.produto_id = p.id) AS tem_tamanhos
     FROM produtos p
     LEFT JOIN categorias c ON c.id = p.categoria_id
     ORDER BY p.criado_em DESC"
);
$produtos = $stmt->fetchAll();

require __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-topo">
    <h1>Produtos (<?= count($produtos) ?>)</h1>
    <a href="produto_form.php" class="btn-northside">+ NOVO PRODUTO</a>
</div>

<?php if (!empty($_GET['msg'])): ?><div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div><?php endif; ?>

<div class="admin-painel">
    <div class="admin-tabela-scroll">
<table class="admin-tabela">
        <thead>
            <tr><th></th><th>Nome</th><th>Categoria</th><th>Preço</th><th>Stock</th><th>Destaque</th><th>Ativo</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($produtos as $p): $temTamanhos = (int)$p['tem_tamanhos'] > 0; $stockReal = $temTamanhos ? (int)$p['stock_tamanhos'] : (int)$p['stock']; ?>
                <tr>
                    <td><img class="miniatura" src="<?= imagemProdutoUrl($p['imagem']) ?>" alt=""></td>
                    <td><strong><?= htmlspecialchars($p['nome']) ?></strong></td>
                    <td><?= htmlspecialchars($p['categoria_nome'] ?? '—') ?></td>
                    <td><?= formatarPreco($p['preco']) ?></td>
                    <td>
                        <?php if ($stockReal > 0): ?>
                            <span class="pill pill-ok"><?= $stockReal ?> un.<?= $temTamanhos ? ' (tamanhos)' : '' ?></span>
                        <?php else: ?>
                            <span class="pill pill-erro">Esgotado</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $p['destaque'] ? '⭐' : '—' ?></td>
                    <td><?= $p['ativo'] ? '✅' : '🚫' ?></td>
                    <td class="admin-acoes">
                        <a href="produto_form.php?id=<?= $p['id'] ?>" class="editar">Editar</a>
                        <form action="produto_eliminar.php" method="post" onsubmit="return confirm('Eliminar este produto? Esta ação não pode ser desfeita.');">
                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
                            <button type="submit" class="eliminar">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($produtos)): ?>
                <tr><td colspan="8" style="text-align:center;color:var(--cinza-texto);padding:24px;">Ainda não tens produtos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</div>

<?php require __DIR__ . '/includes/admin_footer.php'; ?>
