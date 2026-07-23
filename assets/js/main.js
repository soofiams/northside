// ============================================
// NORTHSIDE — Interações do site
// ============================================

// ---- Base de dados dos produtos (front-end apenas — na versão PHP virá da base de dados) ----
const PRODUTOS = {
    1: { nome: 'Auriculares Sem Fios Pro', preco: 24.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 15 },
    2: { nome: 'Smartwatch X1',            preco: 34.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 8  },
    3: { nome: 'Fita LED RGB 5M',          preco: 16.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 0  },
    4: { nome: 'Power Bank 20.000mAh',     preco: 29.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 20 },
    5: { nome: 'Garrafa Térmica 1L',       preco: 19.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 12 },
    6: { nome: 'Boné Northside',           preco: 14.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 30 },
    7: { nome: 'Auriculares Over-Ear ANC', preco: 199.99, imagem: 'assets/img/produtos/sem-imagem.jpg', stock: 10 },
};

const CHAVE_CARRINHO = 'northside_carrinho';

function formatarPreco(valor) {
    return valor.toFixed(2).replace('.', ',') + '€';
}

// ---- Leitura e escrita do carrinho (guardado no localStorage do browser) ----
function obterCarrinho() {
    try {
        return JSON.parse(localStorage.getItem(CHAVE_CARRINHO)) || {};
    } catch (e) {
        return {};
    }
}

function guardarCarrinho(carrinho) {
    localStorage.setItem(CHAVE_CARRINHO, JSON.stringify(carrinho));
    atualizarBadgeCarrinho();
}

function contarItensCarrinho() {
    const carrinho = obterCarrinho();
    return Object.values(carrinho).reduce((total, qtd) => total + qtd, 0);
}

function atualizarBadgeCarrinho() {
    const total = contarItensCarrinho();
    document.querySelectorAll('.cart-badge').forEach(function (badge) {
        badge.textContent = total;
        badge.style.display = total > 0 ? 'flex' : 'none';
    });
}

// ---- Adicionar produto ao carrinho ----
function adicionarAoCarrinho(id, quantidade) {
    quantidade = quantidade || 1;
    const produto = PRODUTOS[id];
    if (!produto || produto.stock <= 0) return;

    const carrinho = obterCarrinho();
    const atual = carrinho[id] || 0;
    carrinho[id] = Math.min(atual + quantidade, produto.stock);
    guardarCarrinho(carrinho);

    mostrarConfirmacaoAdicionado();
    if (document.getElementById('corpo-carrinho')) renderizarCarrinho();
}

// ---- Remover produto do carrinho ----
function removerDoCarrinho(id) {
    const carrinho = obterCarrinho();
    delete carrinho[id];
    guardarCarrinho(carrinho);
    if (document.getElementById('corpo-carrinho')) renderizarCarrinho();
}

// ---- Atualizar a quantidade de um produto já no carrinho ----
function atualizarQuantidadeCarrinho(id, quantidade) {
    const produto = PRODUTOS[id];
    if (!produto) return;
    const carrinho = obterCarrinho();

    if (quantidade <= 0) {
        delete carrinho[id];
    } else {
        carrinho[id] = Math.min(quantidade, produto.stock);
    }
    guardarCarrinho(carrinho);
    if (document.getElementById('corpo-carrinho')) renderizarCarrinho();
}

// Pequena confirmação visual (usada na página de produto)
function mostrarConfirmacaoAdicionado() {
    const aviso = document.getElementById('confirmacao-adicionado');
    if (!aviso) return;
    aviso.style.display = 'block';
    clearTimeout(window._timeoutConfirmacao);
    window._timeoutConfirmacao = setTimeout(function () {
        aviso.style.display = 'none';
    }, 2000);
}

// ---- Renderizar a tabela do carrinho (só corre na página carrinho.html) ----
function renderizarCarrinho() {
    const corpo = document.getElementById('corpo-carrinho');
    if (!corpo) return;

    const vazio = document.getElementById('carrinho-vazio-estado');
    const conteudo = document.getElementById('carrinho-conteudo');
    const carrinho = obterCarrinho();
    const ids = Object.keys(carrinho).filter(function (id) { return PRODUTOS[id]; });

    if (ids.length === 0) {
        if (conteudo) conteudo.style.display = 'none';
        if (vazio) vazio.style.display = 'block';
        return;
    }
    if (conteudo) conteudo.style.display = '';
    if (vazio) vazio.style.display = 'none';

    let html = '';
    let total = 0;

    ids.forEach(function (id) {
        const produto = PRODUTOS[id];
        const qtd = carrinho[id];
        const subtotal = qtd * produto.preco;
        total += subtotal;

        html += '<tr>' +
            '<td><div class="carrinho-item-nome">' +
                '<img src="' + produto.imagem + '" alt="">' +
                '<a href="produto.html"><strong>' + produto.nome + '</strong></a>' +
            '</div></td>' +
            '<td>' + formatarPreco(produto.preco) + '</td>' +
            '<td><div style="display:flex;gap:8px;align-items:center;">' +
                '<input type="number" value="' + qtd + '" min="1" max="' + produto.stock + '" class="input-qtd" style="width:60px;" ' +
                'onchange="atualizarQuantidadeCarrinho(' + id + ', parseInt(this.value) || 1)">' +
            '</div></td>' +
            '<td><strong>' + formatarPreco(subtotal) + '</strong></td>' +
            '<td><button class="btn-remover" onclick="removerDoCarrinho(' + id + ')">Remover</button></td>' +
        '</tr>';
    });

    corpo.innerHTML = html;

    const envio = total >= 50 ? 0 : 4.99;
    const elSubtotal = document.getElementById('carrinho-subtotal');
    const elEnvio = document.getElementById('carrinho-envio');
    const elTotal = document.getElementById('carrinho-total');
    if (elSubtotal) elSubtotal.textContent = formatarPreco(total);
    if (elEnvio) elEnvio.textContent = envio === 0 ? 'Grátis' : formatarPreco(envio);
    if (elTotal) elTotal.textContent = formatarPreco(total + envio);
}


document.addEventListener('DOMContentLoaded', function () {

    // ---- Badge do carrinho, atualizado em todas as páginas ----
    atualizarBadgeCarrinho();

    // ---- Renderizar o carrinho, se estivermos na página do carrinho ----
    renderizarCarrinho();

    // ---- Formulário principal da página de produto (com quantidade) ----
    const formProduto = document.getElementById('form-produto-principal');
    if (formProduto) {
        formProduto.addEventListener('submit', function (e) {
            e.preventDefault();
            const id = parseInt(formProduto.dataset.produtoId, 10);
            const qtdInput = document.getElementById('quantidade-produto');
            const qtd = qtdInput ? (parseInt(qtdInput.value, 10) || 1) : 1;
            adicionarAoCarrinho(id, qtd);
        });
    }

    // ---- Alternar imagens ao passar o rato nos cartões de produto ----
    document.querySelectorAll('.cartao-produto .img-wrap').forEach(function (wrap) {
        const imagens = wrap.querySelectorAll('img');
        if (imagens.length <= 1) return;

        let indiceAtual = 0;
        let intervalo = null;

        wrap.addEventListener('mouseenter', function () {
            intervalo = setInterval(function () {
                imagens[indiceAtual].classList.remove('img-ativa');
                indiceAtual = (indiceAtual + 1) % imagens.length;
                imagens[indiceAtual].classList.add('img-ativa');
            }, 900);
        });

        wrap.addEventListener('mouseleave', function () {
            clearInterval(intervalo);
            imagens[indiceAtual].classList.remove('img-ativa');
            indiceAtual = 0;
            imagens[0].classList.add('img-ativa');
        });
    });

    // ---- Modal de newsletter (10% de desconto) ----
    const modal = document.getElementById('modal-newsletter');
    const formModal = document.getElementById('form-newsletter-modal');
    const confirmacao = document.getElementById('modal-confirmacao');
    const fecharBotoes = document.querySelectorAll('[data-fechar-newsletter]');

    function abrirModal(e) {
        if (e) e.preventDefault();
        if (modal) modal.classList.add('aberto');
    }

    function fecharModal() {
        if (modal) modal.classList.remove('aberto');
    }

    const formRodape = document.getElementById('form-newsletter-footer');
    if (formRodape) {
        formRodape.addEventListener('submit', abrirModal);
    }

    document.querySelectorAll('[data-abrir-newsletter]').forEach(function (btn) {
        btn.addEventListener('click', abrirModal);
    });

    fecharBotoes.forEach(function (btn) {
        btn.addEventListener('click', fecharModal);
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) fecharModal();
        });
    }

    if (formModal) {
        formModal.addEventListener('submit', function (e) {
            e.preventDefault();
            formModal.style.display = 'none';
            if (confirmacao) confirmacao.style.display = 'block';
            setTimeout(fecharModal, 2200);
        });
    }

});
