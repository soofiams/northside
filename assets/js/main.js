// ============================================
// NORTHSIDE — Interações do site
// ============================================

// ---- Base de dados dos produtos (front-end apenas — na versão PHP virá da base de dados) ----
const PRODUTOS = {
    1: {
        nome: 'Auriculares Sem Fios Pro',
        preco: 24.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 15,
        categoria: 'ELETRÓNICOS',
        estrelas: '★★★★★',
        avaliacoes: 128,
        descricao: 'Auriculares bluetooth com cancelamento de ruído ativo, até 24h de autonomia com a caixa de carregamento.',
        especificacoes: {
            'Garantia': '24 meses',
            'Potência / Amperagem': '5V ⎓ 2A (entrada USB-C)',
            'Velocidade de carregamento': 'Carregamento rápido — 1h30 até 100%',
            'Autonomia': 'Até 24h com a caixa de carregamento'
        }
    },
    2: {
        nome: 'Smartwatch X1',
        preco: 34.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 8,
        categoria: 'ELETRÓNICOS',
        estrelas: '★★★★☆',
        avaliacoes: 96,
        descricao: 'Smartwatch com monitor de frequência cardíaca, GPS integrado e resistência à água.',
        especificacoes: {
            'Garantia': '24 meses',
            'Potência / Amperagem': '5V ⎓ 1A (carregamento magnético)',
            'Velocidade de carregamento': 'Carga completa em 1h15',
            'Autonomia': 'Até 7 dias em uso normal'
        }
    },
    3: {
        nome: 'Fita LED RGB 5M',
        preco: 16.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 0,
        categoria: 'CASA',
        estrelas: '★★★★☆',
        avaliacoes: 73,
        descricao: 'Fita LED RGB de 5 metros, controlada por aplicação móvel, com milhões de combinações de cor.',
        especificacoes: {
            'Garantia': '12 meses',
            'Potência / Amperagem': '12V ⎓ 2A',
            'Velocidade de carregamento': 'Não aplicável',
            'Autonomia': 'Ligação contínua à corrente'
        }
    },
    4: {
        nome: 'Power Bank 20.000mAh',
        preco: 29.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 20,
        categoria: 'ELETRÓNICOS',
        estrelas: '★★★★★',
        avaliacoes: 112,
        descricao: 'Bateria portátil de alta capacidade com carregamento rápido e duas saídas USB.',
        especificacoes: {
            'Garantia': '24 meses',
            'Potência / Amperagem': '5V ⎓ 3A (saída rápida)',
            'Velocidade de carregamento': 'Carrega um telemóvel médio 4x',
            'Autonomia': '20.000mAh de capacidade'
        }
    },
    5: {
        nome: 'Garrafa Térmica 1L',
        preco: 19.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 12,
        categoria: 'ESTILO',
        estrelas: '★★★★★',
        avaliacoes: 58,
        descricao: 'Garrafa térmica em aço inoxidável, mantém a temperatura até 12 horas.',
        especificacoes: {
            'Garantia': '12 meses',
            'Capacidade': '1 litro',
            'Material': 'Aço inoxidável de parede dupla',
            'Autonomia térmica': 'Até 12h quente / 24h frio'
        }
    },
    6: {
        nome: 'Boné Northside',
        preco: 14.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 30,
        categoria: 'ESTILO',
        estrelas: '★★★★☆',
        avaliacoes: 42,
        descricao: 'Boné em algodão bordado com o logótipo Northside.',
        especificacoes: {
            'Garantia': 'Trocas até 30 dias',
            'Material': '100% algodão',
            'Ajuste': 'Fivela regulável',
            'Origem': 'Desenhado no Porto'
        }
    },
    7: {
        nome: 'Auriculares Over-Ear ANC',
        preco: 199.99,
        imagem: 'assets/img/produtos/sem-imagem.jpg',
        stock: 10,
        categoria: 'ELETRÓNICOS',
        estrelas: '★★★★★',
        avaliacoes: 64,
        descricao: 'Auriculares over-ear com cancelamento de ruído ativo premium e som de alta fidelidade.',
        especificacoes: {
            'Garantia': '24 meses',
            'Potência / Amperagem': '5V ⎓ 2A (USB-C)',
            'Velocidade de carregamento': 'Carregamento rápido — 10 min = 5h de música',
            'Autonomia': 'Até 30h com ANC ativo'
        }
    },
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
                '<a href="produto.html?id=' + id + '"><strong>' + produto.nome + '</strong></a>' +
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

// ---- Construir o cartão de um produto (usado nos "Produtos Relacionados") ----
function construirCartaoProduto(id) {
    const p = PRODUTOS[id];
    if (!p) return '';
    const linhaPreco = p.stock > 0
        ? '<span class="preco">' + formatarPreco(p.preco) + '</span>' +
          '<button class="btn-add-cart" onclick="adicionarAoCarrinho(' + id + ')">+</button>'
        : '<span class="preco">' + formatarPreco(p.preco) + '</span>' +
          '<span class="rotulo-esgotado">ESGOTADO</span>';

    return '' +
    '<div class="cartao-produto">' +
        '<a href="produto.html?id=' + id + '">' +
            '<div class="img-wrap">' +
                '<img class="img-ativa" src="' + p.imagem + '" alt="' + p.nome + '">' +
                '<img src="' + p.imagem + '" alt="' + p.nome + ', vista 2">' +
            '</div>' +
        '</a>' +
        '<div class="corpo">' +
            '<div class="categoria-label">' + p.categoria + '</div>' +
            '<a href="produto.html?id=' + id + '"><h3>' + p.nome + '</h3></a>' +
            '<div class="estrelas">' + p.estrelas + ' <span class="num">(' + p.avaliacoes + ')</span></div>' +
            '<div class="linha-preco">' + linhaPreco + '</div>' +
        '</div>' +
    '</div>';
}

// ---- Renderizar a página de produto (só corre em produto.html) ----
function renderizarProduto() {
    const elNome = document.getElementById('produto-nome');
    if (!elNome) return; // não estamos na página de produto

    const params = new URLSearchParams(window.location.search);
    let id = parseInt(params.get('id'), 10);
    if (!PRODUTOS[id]) id = 1; // produto por omissão, se o id for inválido ou não existir

    const p = PRODUTOS[id];

    document.title = p.nome + ' — NORTHSIDE';
    document.getElementById('produto-categoria').textContent = p.categoria;
    elNome.textContent = p.nome;
    document.getElementById('produto-estrelas').innerHTML = p.estrelas + ' <span class="num">' + (p.avaliacoes ? p.avaliacoes + ' avaliações' : '') + '</span>';
    document.getElementById('produto-preco').textContent = formatarPreco(p.preco);
    document.getElementById('produto-descricao').textContent = p.descricao;
    document.getElementById('produto-imagem').src = p.imagem;
    document.getElementById('produto-imagem').alt = p.nome;

    // Estado de stock
    const badge = document.getElementById('produto-badge-stock');
    if (p.stock === 0) {
        badge.className = 'badge-stock esgotado';
        badge.textContent = '● Esgotado';
    } else if (p.stock <= 5) {
        badge.className = 'badge-stock pouco-stock';
        badge.textContent = '● Últimas ' + p.stock + ' unidades em stock';
    } else {
        badge.className = 'badge-stock em-stock';
        badge.textContent = '● Em stock (' + p.stock + ' disponíveis)';
    }

    // Formulário de compra
    const form = document.getElementById('form-produto-principal');
    const inputQtd = document.getElementById('quantidade-produto');
    const btnAdd = document.getElementById('btn-produto-add');
    form.dataset.produtoId = id;
    if (p.stock === 0) {
        inputQtd.style.display = 'none';
        btnAdd.textContent = 'PRODUTO ESGOTADO';
        btnAdd.disabled = true;
        btnAdd.style.opacity = '0.5';
        btnAdd.style.cursor = 'not-allowed';
    } else {
        inputQtd.max = p.stock;
        inputQtd.value = 1;
    }

    // Tabela de especificações
    let htmlEspecs = '';
    Object.keys(p.especificacoes).forEach(function (chave) {
        htmlEspecs += '<tr><th>' + chave + '</th><td>' + p.especificacoes[chave] + '</td></tr>';
    });
    document.getElementById('produto-especificacoes').innerHTML = htmlEspecs;

    // Produtos relacionados: até 3 produtos da mesma categoria (excluindo o atual)
    const relacionadosEl = document.getElementById('produtos-relacionados');
    if (relacionadosEl) {
        const relacionados = Object.keys(PRODUTOS)
            .filter(function (outroId) { return parseInt(outroId, 10) !== id && PRODUTOS[outroId].categoria === p.categoria; })
            .slice(0, 3);

        // se não houver 3 da mesma categoria, completa com outros produtos
        if (relacionados.length < 3) {
            Object.keys(PRODUTOS).forEach(function (outroId) {
                if (relacionados.length >= 3) return;
                if (parseInt(outroId, 10) === id || relacionados.includes(outroId)) return;
                relacionados.push(outroId);
            });
        }

        relacionadosEl.innerHTML = relacionados.map(construirCartaoProduto).join('');
    }
}


document.addEventListener('DOMContentLoaded', function () {

    // ---- Badge do carrinho, atualizado em todas as páginas ----
    atualizarBadgeCarrinho();

    // ---- Renderizar o carrinho, se estivermos na página do carrinho ----
    renderizarCarrinho();

    // ---- Renderizar a página de produto, se estivermos nela ----
    renderizarProduto();

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
    // (delegação de eventos, porque os cartões de "relacionados" só existem depois do JS correr)
    document.body.addEventListener('mouseover', function (e) {
        const wrap = e.target.closest('.img-wrap');
        if (!wrap || wrap.dataset.hoverAtivo) return;
        const imagens = wrap.querySelectorAll('img');
        if (imagens.length <= 1) return;

        wrap.dataset.hoverAtivo = '1';
        let indiceAtual = 0;
        let intervalo = setInterval(function () {
            imagens[indiceAtual].classList.remove('img-ativa');
            indiceAtual = (indiceAtual + 1) % imagens.length;
            imagens[indiceAtual].classList.add('img-ativa');
        }, 900);

        wrap.addEventListener('mouseleave', function () {
            clearInterval(intervalo);
            imagens[indiceAtual].classList.remove('img-ativa');
            indiceAtual = 0;
            imagens[0].classList.add('img-ativa');
            delete wrap.dataset.hoverAtivo;
        }, { once: true });
    });

    // ---- Modais (genérico: suporta vários — newsletter, sobre nós, etc.) ----
    function abrirModalPorId(id, e) {
        if (e) e.preventDefault();
        const alvo = document.getElementById(id);
        if (alvo) alvo.classList.add('aberto');
    }

    function fecharModal(modal) {
        if (modal) modal.classList.remove('aberto');
    }

    // Qualquer link/botão com data-abrir-modal="id-do-modal" abre esse modal
    document.querySelectorAll('[data-abrir-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            abrirModalPorId(btn.dataset.abrirModal, e);
        });
    });

    // O formulário do rodapé abre especificamente o modal da newsletter
    const formRodape = document.getElementById('form-newsletter-footer');
    if (formRodape) {
        formRodape.addEventListener('submit', function (e) {
            abrirModalPorId('modal-newsletter', e);
        });
    }

    // Fechar: botão "✕", clique fora da caixa, em qualquer modal da página
    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
        modal.querySelectorAll('[data-fechar-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () { fecharModal(modal); });
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) fecharModal(modal);
        });
    });

    // Submissão do formulário dentro do modal da newsletter
    const formModal = document.getElementById('form-newsletter-modal');
    const confirmacao = document.getElementById('modal-confirmacao');
    if (formModal) {
        formModal.addEventListener('submit', function (e) {
            e.preventDefault();
            formModal.style.display = 'none';
            if (confirmacao) confirmacao.style.display = 'block';
            setTimeout(function () {
                fecharModal(document.getElementById('modal-newsletter'));
            }, 2200);
        });
    }

    // ---- Checkout (finalizar compra), só existe na página do carrinho ----
    const btnFinalizar = document.getElementById('btn-finalizar-compra');
    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function () {
            const carrinho = obterCarrinho();
            const ids = Object.keys(carrinho).filter(function (id) { return PRODUTOS[id]; });
            if (ids.length === 0) return; // carrinho vazio, não há o que finalizar

            // Preencher o resumo dos produtos dentro do modal
            let html = '';
            let total = 0;
            ids.forEach(function (id) {
                const produto = PRODUTOS[id];
                const qtd = carrinho[id];
                const subtotal = qtd * produto.preco;
                total += subtotal;
                html += '<div class="linha-produto"><span>' + qtd + '× ' + produto.nome + '</span><span>' + formatarPreco(subtotal) + '</span></div>';
            });
            const envio = total >= 50 ? 0 : 4.99;
            html += '<div class="linha-produto"><span>Envio</span><span>' + (envio === 0 ? 'Grátis' : formatarPreco(envio)) + '</span></div>';
            html += '<div class="linha-total"><span>Total</span><span>' + formatarPreco(total + envio) + '</span></div>';
            document.getElementById('checkout-resumo').innerHTML = html;

            // Repor o formulário para um novo pedido e mostrar o modal
            const formCheckout = document.getElementById('form-checkout');
            const confirmacaoCheckout = document.getElementById('checkout-confirmacao');
            if (formCheckout) formCheckout.style.display = '';
            if (confirmacaoCheckout) confirmacaoCheckout.style.display = 'none';

            abrirModalPorId('modal-checkout');
        });
    }

    const formCheckout = document.getElementById('form-checkout');
    if (formCheckout) {
        formCheckout.addEventListener('submit', function (e) {
            e.preventDefault();
            const confirmacaoCheckout = document.getElementById('checkout-confirmacao');

            formCheckout.style.display = 'none';
            if (confirmacaoCheckout) confirmacaoCheckout.style.display = 'block';

            // Esvaziar o carrinho depois de confirmada a encomenda
            guardarCarrinho({});
            renderizarCarrinho();

            setTimeout(function () {
                fecharModal(document.getElementById('modal-checkout'));
            }, 3000);
        });
    }

});
