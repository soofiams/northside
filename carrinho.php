<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$titulo_pagina = 'Carrinho';
$itens = carrinhoDetalhado($pdo);
$subtotal = carrinhoTotalValor($itens);
$envio = $subtotal >= PORTES_GRATIS_ACIMA_DE ? 0 : CUSTO_ENVIO;

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
    <div class="carrinho-scroll">
    <table class="carrinho-tabela">
        <thead>
            <tr><th>Produto</th><th>Preço</th><th>Quantidade</th><th>Subtotal</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($itens as $item): $p = $item['produto']; ?>
                <tr>
                    <td data-label="">
                        <div class="carrinho-item-nome">
                            <img src="<?= imagemProdutoUrl($p['imagem']) ?>" alt="">
                            <a href="produto.php?id=<?= $p['id'] ?>"><strong><?= htmlspecialchars($p['nome']) ?></strong></a>
                        </div>
                    </td>
                    <td data-label="Preço"><?= formatarPreco($p['preco']) ?></td>
                    <td data-label="Quantidade">
                        <form action="actions/carrinho_update.php" method="post" style="display:flex;gap:8px;align-items:center;">
                            <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                            <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= (int)$p['stock'] ?>" class="input-qtd" style="width:60px;">
                            <button type="submit" class="btn-outline-northside" style="padding:6px 12px;font-size:0.72rem;">Atualizar</button>
                        </form>
                    </td>
                    <td data-label="Subtotal"><strong><?= formatarPreco($item['subtotal']) ?></strong></td>
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
    </div>

    <div class="resumo-carrinho">
        <div class="linha"><span>Subtotal</span><span><?= formatarPreco($subtotal) ?></span></div>
        <div class="linha"><span>Envio</span><span><?= $envio == 0 ? 'Grátis' : formatarPreco($envio) ?></span></div>
        <div class="linha total"><span>Total</span><span><?= formatarPreco($subtotal + $envio) ?></span></div>
        <button class="btn-northside" id="btn-finalizar-compra" type="button" style="width:100%;margin-top:16px;">FINALIZAR COMPRA</button>
    </div>
</div>

<!-- Modal de checkout (finalizar compra) -->
<div class="modal-overlay" id="modal-checkout">
    <div class="modal-caixa modal-checkout-caixa">
        <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
        <h3>Finalizar Compra</h3>

        <form id="form-checkout" class="checkout-form" action="actions/finalizar_compra.php" method="post"
              data-subtotal="<?= $subtotal ?>" data-envio="<?= $envio ?>">
            <label for="checkout-nome">Nome completo</label>
            <input type="text" name="nome" id="checkout-nome" required>

            <label for="checkout-email">Email (para a confirmação da encomenda)</label>
            <input type="email" name="email" id="checkout-email" required>

            <label for="checkout-telemovel">Número de telemóvel</label>
            <input type="tel" name="telefone" id="checkout-telemovel" required>

            <label for="checkout-morada">Morada de entrega</label>
            <input type="text" name="morada" id="checkout-morada" placeholder="Rua, número, andar" required>

            <div class="checkout-linha-dupla">
                <div>
                    <label for="checkout-codigo-postal">Código postal</label>
                    <input type="text" name="codigo_postal" id="checkout-codigo-postal" placeholder="0000-000" required>
                </div>
                <div>
                    <label for="checkout-cidade">Localidade</label>
                    <input type="text" name="cidade" id="checkout-cidade" required>
                </div>
            </div>

            <label for="checkout-desconto-input">Código de desconto (opcional)</label>
            <div class="checkout-desconto-linha">
                <input type="text" id="checkout-desconto-input" placeholder="Ex: NORTHSIDE10">
                <button type="button" id="btn-aplicar-desconto" class="btn-outline-northside">Aplicar</button>
            </div>
            <div class="checkout-desconto-msg" id="checkout-desconto-msg"></div>
            <input type="hidden" name="codigo_desconto" id="checkout-codigo-desconto-final" value="">

            <div class="checkout-resumo" id="checkout-resumo">
                <div class="linha-produto"><span>Subtotal</span><span><?= formatarPreco($subtotal) ?></span></div>
                <div class="linha-produto"><span>Envio</span><span><?= $envio == 0 ? 'Grátis' : formatarPreco($envio) ?></span></div>
                <div class="linha-total"><span>Total</span><span><?= formatarPreco($subtotal + $envio) ?></span></div>
            </div>

            <label>Método de pagamento</label>
            <div class="checkout-pagamento-opcoes">
                <label><input type="radio" name="pagamento" value="MB WAY" checked> MB WAY</label>
                <label><input type="radio" name="pagamento" value="Klarna"> Klarna</label>
                <label><input type="radio" name="pagamento" value="Apple Pay"> Apple Pay</label>
                <label><input type="radio" name="pagamento" value="Google Pay"> Google Pay</label>
            </div>

            <button type="submit" class="btn-northside" style="width:100%;margin-top:20px;">CONFIRMAR ENCOMENDA</button>
        </form>

        <div class="modal-confirmacao" id="checkout-confirmacao"></div>
    </div>
</div>

<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
