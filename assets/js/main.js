// ============================================
// NORTHSIDE — Interações do site (versão PHP)
// A maior parte da lógica agora vive no servidor (PHP + MySQL).
// Este ficheiro trata só da interface: menu, modais, e as chamadas
// AJAX ao código de desconto e ao checkout, para manter a janela
// de finalizar compra sem recarregar a página.
// ============================================

function formatarPrecoJs(valor) {
    return valor.toFixed(2).replace('.', ',') + '€';
}

document.addEventListener('DOMContentLoaded', function () {

    // ---- Menu hambúrguer (mobile) ----
    const btnMenu = document.getElementById('btn-menu-mobile');
    const navLinks = document.getElementById('nav-links');
    if (btnMenu && navLinks) {
        btnMenu.addEventListener('click', function () {
            navLinks.classList.toggle('aberto');
            btnMenu.classList.toggle('aberto');
        });
        navLinks.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                navLinks.classList.remove('aberto');
                btnMenu.classList.remove('aberto');
            });
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

    // ============================================
    // Modais (genérico: newsletter, sobre nós, checkout)
    // ============================================
    function abrirModalPorId(id, e) {
        if (e) e.preventDefault();
        const alvo = document.getElementById(id);
        if (alvo) alvo.classList.add('aberto');
    }
    function fecharModal(modal) {
        if (modal) modal.classList.remove('aberto');
    }

    document.querySelectorAll('[data-abrir-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            abrirModalPorId(btn.dataset.abrirModal, e);
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (modal) {
        modal.querySelectorAll('[data-fechar-modal]').forEach(function (btn) {
            btn.addEventListener('click', function () { fecharModal(modal); });
        });
        modal.addEventListener('click', function (e) {
            if (e.target === modal) fecharModal(modal);
        });
    });

    // ============================================
    // Newsletter — envia por AJAX para actions/newsletter_subscrever.php
    // ============================================
    const formNewsletterModal = document.getElementById('form-newsletter-modal');
    const confirmacaoNewsletter = document.getElementById('modal-confirmacao');

    if (formNewsletterModal) {
        formNewsletterModal.addEventListener('submit', function (e) {
            e.preventDefault();
            const dados = new FormData(formNewsletterModal);

            fetch(formNewsletterModal.action, { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (resposta.sucesso) {
                        formNewsletterModal.style.display = 'none';
                        if (confirmacaoNewsletter) confirmacaoNewsletter.style.display = 'block';
                        setTimeout(function () {
                            fecharModal(document.getElementById('modal-newsletter'));
                            formNewsletterModal.style.display = '';
                            if (confirmacaoNewsletter) confirmacaoNewsletter.style.display = 'none';
                            formNewsletterModal.reset();
                        }, 2500);
                    } else {
                        alert(resposta.erro || 'Não foi possível subscrever agora.');
                    }
                })
                .catch(function () {
                    alert('Não foi possível ligar ao servidor. Tenta novamente.');
                });
        });
    }

    // Se a faixa "antes dos produtos" tiver um email escrito, passa-o para o modal
    document.querySelectorAll('[data-abrir-modal="modal-newsletter"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const faixa = btn.closest('.newsletter-faixa-form');
            if (!faixa) return;
            const campoOrigem = faixa.querySelector('input[type="email"]');
            const campoModal = document.querySelector('#form-newsletter-modal input[type="email"]');
            if (campoOrigem && campoModal && campoOrigem.value) {
                campoModal.value = campoOrigem.value;
            }
        });
    });

    // ============================================
    // Checkout — código de desconto (AJAX) + confirmação final (AJAX)
    // ============================================
    const formCheckout = document.getElementById('form-checkout');
    const btnFinalizar = document.getElementById('btn-finalizar-compra');

    if (btnFinalizar) {
        btnFinalizar.addEventListener('click', function () {
            abrirModalPorId('modal-checkout');
        });
    }

    if (formCheckout) {
        const subtotalBase = parseFloat(formCheckout.dataset.subtotal || '0');
        const envioBase = parseFloat(formCheckout.dataset.envio || '0');
        let descontoAtual = 0; // percentagem, ex: 0.10

        function renderizarResumoCheckout() {
            const valorDesconto = descontoAtual > 0 ? (subtotalBase + envioBase) * descontoAtual : 0;
            const totalFinal = Math.max(0, subtotalBase + envioBase - valorDesconto);

            let html = '<div class="linha-produto"><span>Subtotal</span><span>' + formatarPrecoJs(subtotalBase) + '</span></div>';
            html += '<div class="linha-produto"><span>Envio</span><span>' + (envioBase === 0 ? 'Grátis' : formatarPrecoJs(envioBase)) + '</span></div>';
            if (valorDesconto > 0) {
                html += '<div class="linha-desconto"><span>Desconto (-' + Math.round(descontoAtual * 100) + '%)</span><span>-' + formatarPrecoJs(valorDesconto) + '</span></div>';
            }
            html += '<div class="linha-total"><span>Total</span><span>' + formatarPrecoJs(totalFinal) + '</span></div>';
            document.getElementById('checkout-resumo').innerHTML = html;
        }

        const btnAplicarDesconto = document.getElementById('btn-aplicar-desconto');
        if (btnAplicarDesconto) {
            btnAplicarDesconto.addEventListener('click', function () {
                const campo = document.getElementById('checkout-desconto-input');
                const msg = document.getElementById('checkout-desconto-msg');
                const campoFinal = document.getElementById('checkout-codigo-desconto-final');
                const codigo = campo.value.trim();

                if (!codigo) {
                    msg.textContent = 'Escreve um código antes de aplicar.';
                    msg.className = 'checkout-desconto-msg erro';
                    return;
                }

                const dados = new FormData();
                dados.append('codigo', codigo);
                const campoEmail = document.getElementById('checkout-email');
                if (campoEmail && campoEmail.value.trim()) dados.append('email', campoEmail.value.trim());

                fetch('actions/aplicar_desconto.php', { method: 'POST', body: dados })
                    .then(function (r) { return r.json(); })
                    .then(function (resposta) {
                        msg.textContent = resposta.mensagem;
                        if (resposta.valido) {
                            msg.className = 'checkout-desconto-msg sucesso';
                            descontoAtual = resposta.percentagem;
                            campoFinal.value = resposta.codigo;
                        } else {
                            msg.className = 'checkout-desconto-msg erro';
                            descontoAtual = 0;
                            campoFinal.value = '';
                        }
                        renderizarResumoCheckout();
                    })
                    .catch(function () {
                        msg.textContent = 'Não foi possível validar o código agora.';
                        msg.className = 'checkout-desconto-msg erro';
                    });
            });
        }

        // Ecrã de transição, mostrado enquanto se prepara o pagamento na Stripe
        function mostrarEcraTransicaoPagamento() {
            const modalPagamento = document.getElementById('modal-pagamento-simulado');
            modalPagamento.classList.add('aberto');
        }
        function esconderEcraTransicaoPagamento() {
            document.getElementById('modal-pagamento-simulado').classList.remove('aberto');
        }

        formCheckout.addEventListener('submit', function (e) {
            e.preventDefault();
            const btnSubmit = formCheckout.querySelector('button[type="submit"]');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'A preparar...';

            const dados = new FormData(formCheckout);

            mostrarEcraTransicaoPagamento();

            fetch(formCheckout.action, { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (resposta.sucesso && resposta.url) {
                        // sucesso: sai da nossa página e vai para o checkout seguro da Stripe
                        window.location.href = resposta.url;
                    } else {
                        esconderEcraTransicaoPagamento();
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'IR PARA PAGAMENTO SEGURO';
                        alert(resposta.erro || 'Não foi possível iniciar o pagamento.');
                    }
                })
                .catch(function () {
                    esconderEcraTransicaoPagamento();
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'IR PARA PAGAMENTO SEGURO';
                    alert('Não foi possível ligar ao servidor. Tenta novamente.');
                });
        });
    }

    // ============================================
    // Seletor de tamanhos (roupa)
    // ============================================
    const tamanhosSelector = document.getElementById('tamanhos-selector');
    const formProdutoPrincipal = document.getElementById('form-produto-principal');
    if (tamanhosSelector && formProdutoPrincipal) {
        const inputTamanho = document.getElementById('input-tamanho-selecionado');
        const avisoTamanho = document.getElementById('aviso-tamanho');

        tamanhosSelector.querySelectorAll('.tamanho-pill').forEach(function (pill) {
            if (pill.classList.contains('indisponivel')) return;
            pill.addEventListener('click', function () {
                tamanhosSelector.querySelectorAll('.tamanho-pill').forEach(function (p) { p.classList.remove('selecionado'); });
                pill.classList.add('selecionado');
                inputTamanho.value = pill.dataset.tamanho;
                avisoTamanho.style.display = 'none';
            });
        });

        formProdutoPrincipal.addEventListener('submit', function (e) {
            if (!inputTamanho.value) {
                e.preventDefault();
                avisoTamanho.style.display = 'block';
            }
        });
    }

    // ============================================
    // Estrelas clicáveis no formulário de avaliação
    // ============================================
    const estrelasInput = document.getElementById('estrelas-input');
    if (estrelasInput) {
        const inputEstrelas = document.getElementById('input-estrelas');
        const spans = estrelasInput.querySelectorAll('span');

        function marcarEstrelas(valor) {
            spans.forEach(function (s) {
                s.classList.toggle('ativa', parseInt(s.dataset.valor, 10) <= valor);
            });
        }
        marcarEstrelas(5); // valor por omissão

        spans.forEach(function (s) {
            s.addEventListener('mouseenter', function () { marcarEstrelas(parseInt(s.dataset.valor, 10)); });
            s.addEventListener('click', function () {
                const valor = parseInt(s.dataset.valor, 10);
                inputEstrelas.value = valor;
                marcarEstrelas(valor);
            });
        });
        estrelasInput.addEventListener('mouseleave', function () {
            marcarEstrelas(parseInt(inputEstrelas.value, 10));
        });
    }

    // ============================================
    // Devoluções (só existe em devolucoes.php)
    // ============================================
    const btnProcurarEncomenda = document.getElementById('btn-procurar-encomenda');
    if (btnProcurarEncomenda) {
        const passo1 = document.getElementById('devolucao-passo-1');
        const passo2 = document.getElementById('devolucao-passo-2');
        const passo3 = document.getElementById('devolucao-passo-3');
        const msg1 = document.getElementById('devolucao-msg-passo-1');
        const msg2 = document.getElementById('devolucao-msg-passo-2');
        let numeroAtual = null;
        let emailAtual = null;

        btnProcurarEncomenda.addEventListener('click', function () {
            const numero = document.getElementById('devolucao-numero').value.trim();
            const email = document.getElementById('devolucao-email').value.trim();
            msg1.textContent = '';
            msg1.className = 'devolucao-msg';

            if (!numero || !email) {
                msg1.textContent = 'Preenche o número da encomenda e o email.';
                msg1.className = 'devolucao-msg erro';
                return;
            }

            const dados = new FormData();
            dados.append('numero_encomenda', numero);
            dados.append('email', email);

            fetch('actions/devolucao_procurar.php', { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (!resposta.sucesso) {
                        msg1.textContent = resposta.erro;
                        msg1.className = 'devolucao-msg erro';
                        return;
                    }

                    numeroAtual = resposta.encomenda_id;
                    emailAtual = resposta.email;
                    document.getElementById('devolucao-numero-confirmado').textContent = '#' + String(numeroAtual).padStart(5, '0');

                    const lista = document.getElementById('devolucao-lista-itens');
                    lista.innerHTML = resposta.itens.map(function (item) {
                        const detalhe = item.quantidade + '× ' + (item.tamanho ? ' (Tamanho ' + item.tamanho + ')' : '');
                        if (item.ja_pedida) {
                            return '<div class="devolucao-item-linha">' +
                                '<span class="nome">' + item.nome + '</span>' +
                                '<span class="detalhe">' + detalhe + '</span>' +
                                '<span class="ja-pedida">Já pedida</span></div>';
                        }
                        return '<div class="devolucao-item-linha">' +
                            '<input type="checkbox" value="' + item.id + '" class="devolucao-item-checkbox">' +
                            '<span class="nome">' + item.nome + '</span>' +
                            '<span class="detalhe">' + detalhe + '</span></div>';
                    }).join('');

                    passo1.style.display = 'none';
                    passo2.style.display = 'block';
                })
                .catch(function () {
                    msg1.textContent = 'Não foi possível ligar ao servidor. Tenta novamente.';
                    msg1.className = 'devolucao-msg erro';
                });
        });

        const btnVoltar = document.getElementById('btn-devolucao-voltar');
        if (btnVoltar) {
            btnVoltar.addEventListener('click', function () {
                passo2.style.display = 'none';
                passo1.style.display = 'block';
            });
        }

        const btnConfirmarDevolucao = document.getElementById('btn-confirmar-devolucao');
        if (btnConfirmarDevolucao) {
            btnConfirmarDevolucao.addEventListener('click', function () {
                const selecionados = Array.from(document.querySelectorAll('.devolucao-item-checkbox:checked')).map(function (c) { return c.value; });
                const motivo = document.getElementById('devolucao-motivo').value.trim();
                msg2.textContent = '';
                msg2.className = 'devolucao-msg';

                if (selecionados.length === 0) {
                    msg2.textContent = 'Escolhe pelo menos um produto para devolver.';
                    msg2.className = 'devolucao-msg erro';
                    return;
                }
                if (!motivo) {
                    msg2.textContent = 'Explica o motivo da devolução.';
                    msg2.className = 'devolucao-msg erro';
                    return;
                }

                const dados = new FormData();
                dados.append('numero_encomenda', numeroAtual);
                dados.append('email', emailAtual);
                dados.append('motivo', motivo);
                selecionados.forEach(function (id) { dados.append('itens[]', id); });

                fetch('actions/devolucao_criar.php', { method: 'POST', body: dados })
                    .then(function (r) { return r.json(); })
                    .then(function (resposta) {
                        if (!resposta.sucesso) {
                            msg2.textContent = resposta.erro;
                            msg2.className = 'devolucao-msg erro';
                            return;
                        }
                        passo2.style.display = 'none';
                        passo3.style.display = 'block';
                    })
                    .catch(function () {
                        msg2.textContent = 'Não foi possível ligar ao servidor. Tenta novamente.';
                        msg2.className = 'devolucao-msg erro';
                    });
            });
        }
    }

    // ============================================
    // Chat privado (widget presente em todas as páginas)
    // ============================================
    const chatBotao = document.getElementById('chat-botao');
    if (chatBotao) {
        const chatPainel = document.getElementById('chat-painel');
        const chatFormInicial = document.getElementById('chat-form-inicial');
        const chatMensagensEl = document.getElementById('chat-mensagens');
        const chatFormEnviar = document.getElementById('chat-form-enviar');
        const chatInputMensagem = document.getElementById('chat-input-mensagem');

        let conversaId = localStorage.getItem('northside_chat_conversa_id');
        let ultimoIdMensagem = 0;
        let intervaloPolling = null;

        function mostrarAvisoChat(texto) {
            let aviso = document.getElementById('chat-aviso-erro');
            if (!aviso) {
                aviso = document.createElement('div');
                aviso.id = 'chat-aviso-erro';
                aviso.style.cssText = 'padding:10px 16px;background:#fbe7e7;color:#c22b2b;font-size:0.78rem;text-align:center;';
                chatPainel.insertBefore(aviso, chatMensagensEl);
            }
            aviso.textContent = texto;
            aviso.style.display = 'block';
            setTimeout(function () { aviso.style.display = 'none'; }, 4000);
        }

        // Esquece a conversa guardada e volta a mostrar o formulário inicial
        // (usado quando a conversa já não existe do lado do servidor)
        function reiniciarConversa() {
            localStorage.removeItem('northside_chat_conversa_id');
            conversaId = null;
            ultimoIdMensagem = 0;
            clearInterval(intervaloPolling);
            chatMensagensEl.innerHTML = '';
            chatMensagensEl.style.display = 'none';
            chatFormEnviar.style.display = 'none';
            chatFormInicial.style.display = 'block';
            mostrarAvisoChat('A tua conversa anterior já não está disponível. Começa uma nova abaixo.');
        }

        function renderizarMensagem(m) {
            const bolha = document.createElement('div');
            bolha.className = 'chat-bolha ' + m.remetente;
            bolha.innerHTML = m.mensagem + '<span class="hora">' + m.hora + '</span>';
            chatMensagensEl.appendChild(bolha);
            chatMensagensEl.scrollTop = chatMensagensEl.scrollHeight;
            if (m.id > ultimoIdMensagem) ultimoIdMensagem = m.id;
        }

        function irBuscarMensagensNovas() {
            if (!conversaId) return;
            fetch('actions/chat_mensagens.php?conversa_id=' + conversaId + '&depois_de=' + ultimoIdMensagem)
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (resposta.sucesso) {
                        resposta.mensagens.forEach(renderizarMensagem);
                    } else if (resposta.erro === 'conversa_invalida') {
                        reiniciarConversa();
                    }
                })
                .catch(function () { /* falha de rede pontual — tenta outra vez no próximo intervalo */ });
        }

        function mostrarConversa() {
            chatFormInicial.style.display = 'none';
            chatMensagensEl.style.display = 'flex';
            chatFormEnviar.style.display = 'flex';
            irBuscarMensagensNovas();
            clearInterval(intervaloPolling);
            intervaloPolling = setInterval(irBuscarMensagensNovas, 4000);
        }

        function abrirChat() {
            chatPainel.classList.add('aberto');
            if (conversaId) mostrarConversa();
        }

        chatBotao.addEventListener('click', abrirChat);

        const linkAjuda = document.getElementById('link-ajuda-chat');
        if (linkAjuda) {
            linkAjuda.addEventListener('click', function (e) {
                e.preventDefault();
                abrirChat();
            });
        }

        document.getElementById('chat-fechar').addEventListener('click', function () {
            chatPainel.classList.remove('aberto');
            clearInterval(intervaloPolling);
        });

        const btnChatIniciar = document.getElementById('btn-chat-iniciar');
        if (btnChatIniciar) {
            btnChatIniciar.addEventListener('click', function () {
                const nome = document.getElementById('chat-nome').value.trim();
                const email = document.getElementById('chat-email').value.trim();
                if (!nome || !email) {
                    mostrarAvisoChat('Preenche o nome e o email.');
                    return;
                }

                btnChatIniciar.disabled = true;
                btnChatIniciar.textContent = 'A ligar...';

                const dados = new FormData();
                dados.append('nome', nome);
                dados.append('email', email);

                fetch('actions/chat_iniciar.php', { method: 'POST', body: dados })
                    .then(function (r) { return r.json(); })
                    .then(function (resposta) {
                        btnChatIniciar.disabled = false;
                        btnChatIniciar.textContent = 'COMEÇAR CONVERSA';
                        if (!resposta.sucesso) {
                            mostrarAvisoChat(resposta.erro || 'Não foi possível iniciar a conversa.');
                            return;
                        }
                        conversaId = resposta.conversa_id;
                        localStorage.setItem('northside_chat_conversa_id', conversaId);
                        mostrarConversa();
                    })
                    .catch(function () {
                        btnChatIniciar.disabled = false;
                        btnChatIniciar.textContent = 'COMEÇAR CONVERSA';
                        mostrarAvisoChat('Não foi possível ligar ao servidor. Tenta novamente.');
                    });
            });
        }

        chatFormEnviar.addEventListener('submit', function (e) {
            e.preventDefault();
            const texto = chatInputMensagem.value.trim();
            if (!texto || !conversaId) return;

            renderizarMensagem({ id: ultimoIdMensagem, remetente: 'cliente', mensagem: texto, hora: new Date().toTimeString().slice(0, 5) });
            chatInputMensagem.value = '';

            const dados = new FormData();
            dados.append('conversa_id', conversaId);
            dados.append('mensagem', texto);

            fetch('actions/chat_enviar.php', { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (!resposta.sucesso) {
                        if (resposta.erro === 'conversa_invalida') {
                            reiniciarConversa();
                        } else {
                            mostrarAvisoChat('A mensagem não chegou a enviar-se. Tenta outra vez.');
                        }
                    }
                })
                .catch(function () {
                    mostrarAvisoChat('Não foi possível ligar ao servidor. A mensagem não foi enviada.');
                });
        });
    }


    // ============================================
    // Acompanhar encomenda (só existe em encomenda.php)
    // ============================================
    const btnConsultarEncomenda = document.getElementById('btn-consultar-encomenda');
    if (btnConsultarEncomenda) {
        const passo1 = document.getElementById('acompanhar-passo-1');
        const resultado = document.getElementById('acompanhar-resultado');
        const msg = document.getElementById('acompanhar-msg');
        const ORDEM_ESTADOS = ['confirmada', 'enviada', 'entregue'];

        btnConsultarEncomenda.addEventListener('click', function () {
            const numero = document.getElementById('acompanhar-numero').value.trim();
            const email = document.getElementById('acompanhar-email').value.trim();
            msg.textContent = '';
            msg.className = 'devolucao-msg';

            if (!numero || !email) {
                msg.textContent = 'Preenche o número da encomenda e o email.';
                msg.className = 'devolucao-msg erro';
                return;
            }

            const dados = new FormData();
            dados.append('numero_encomenda', numero);
            dados.append('email', email);

            fetch('actions/encomenda_consultar.php', { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (!resposta.sucesso) {
                        msg.textContent = resposta.erro;
                        msg.className = 'devolucao-msg erro';
                        return;
                    }

                    document.getElementById('acompanhar-numero-confirmado').textContent = '#' + String(resposta.encomenda_id).padStart(5, '0');
                    document.getElementById('acompanhar-data').textContent = resposta.data;
                    document.getElementById('acompanhar-total').textContent = resposta.total;
                    document.getElementById('acompanhar-pagamento').textContent = resposta.metodo_pagamento;
                    document.getElementById('acompanhar-morada').textContent = resposta.morada;

                    const avisoCancelada = document.getElementById('acompanhar-cancelada-aviso');
                    const progresso = document.getElementById('acompanhar-progresso');
                    if (resposta.estado === 'cancelada') {
                        avisoCancelada.style.display = 'block';
                        progresso.style.display = 'none';
                    } else {
                        avisoCancelada.style.display = 'none';
                        progresso.style.display = 'flex';
                        const indiceAtual = ORDEM_ESTADOS.indexOf(resposta.estado); // -1 se ainda "pendente"
                        progresso.querySelectorAll('.estado-passo').forEach(function (passo, i) {
                            passo.classList.toggle('concluido', i <= indiceAtual);
                        });
                    }

                    const lista = document.getElementById('acompanhar-lista-itens');
                    lista.innerHTML = resposta.itens.map(function (item) {
                        const detalhe = item.quantidade + '× ' + item.nome + (item.tamanho ? ' (Tamanho ' + item.tamanho + ')' : '');
                        return '<div class="devolucao-item-linha"><span class="nome">' + detalhe + '</span><span class="detalhe" style="margin-left:auto;">' + item.subtotal + '</span></div>';
                    }).join('');

                    passo1.style.display = 'none';
                    resultado.style.display = 'block';
                })
                .catch(function () {
                    msg.textContent = 'Não foi possível ligar ao servidor. Tenta novamente.';
                    msg.className = 'devolucao-msg erro';
                });
        });

        const btnVoltarAcompanhar = document.getElementById('btn-acompanhar-voltar');
        if (btnVoltarAcompanhar) {
            btnVoltarAcompanhar.addEventListener('click', function () {
                resultado.style.display = 'none';
                passo1.style.display = 'block';
                document.getElementById('acompanhar-numero').value = '';
                document.getElementById('acompanhar-email').value = '';
            });
        }
    }

});
