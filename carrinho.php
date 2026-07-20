<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$titulo_pagina = 'Carrinho';
$itens = carrinho_detalhado($pdo);
$total = carrinho_total_valor($itens);

require __DIR__ . '/includes/header.php';
?>

<h2 class="secao-titulo">O MEU CARRINHO</h2>

<?php if (!empty($_GET['msg'])): ?>
    <div class="alerta alerta-sucesso"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<?php if (empty($itens)): ?>
    <div class="carrinho-vazio">
        O teu carrinho está vazio.<br><br>
        <a href="loja.php" class="btn-northside">VER PRODUTOS</a>
    </div>
<?php else: ?>

<div style="padding: 0 30px;">
    <table class="carrinho-tabela">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço</th>
                <th>Quantidade</th>
                <th>Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): $p = $item['produto']; ?>
                <tr>
                    <td>
                        <div class="carrinho-item-nome">
                            <img src="<?= imagem_produto_url($p['imagem']) ?>" alt="">
                            <a href="produto.php?id=<?= $p['id'] ?>"><strong><?= htmlspecialchars($p['nome']) ?></strong></a>
                        </div>
                    </td>
                    <td><?= formatar_preco($p['preco']) ?></td>
                    <td>
                        <form action="actions/carrinho_update.php" method="post" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                            <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= (int)$p['stock'] ?>" class="input-qtd" style="width:60px;">
                            <button type="submit" class="btn-outline-northside" style="padding:6px 12px;font-size:0.72rem;">Atualizar</button>
                        </form>
                    </td>
                    <td><strong><?= formatar_preco($item['subtotal']) ?></strong></td>
                    <td>
                        <form action="actions/carrinho_remove.php" method="post">
                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-remover">Remover</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="resumo-carrinho">
        <div class="linha"><span>Subtotal</span><span><?= formatar_preco($total) ?></span></div>
        <div class="linha"><span>Envio</span><span><?= $total >= PORTES_GRATIS_ACIMA_DE ? 'Grátis' : formatar_preco(4.99) ?></span></div>
        <div class="linha total"><span>Total</span><span><?= formatar_preco($total >= PORTES_GRATIS_ACIMA_DE ? $total : $total + 4.99) ?></span></div>
        <button class="btn-northside" style="width:100%;margin-top:16px;">FINALIZAR COMPRA</button>
    </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
