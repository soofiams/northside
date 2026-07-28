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

        formCheckout.addEventListener('submit', function (e) {
            e.preventDefault();
            const confirmacaoCheckout = document.getElementById('checkout-confirmacao');
            const btnSubmit = formCheckout.querySelector('button[type="submit"]');
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'A confirmar...';

            const dados = new FormData(formCheckout);

            fetch(formCheckout.action, { method: 'POST', body: dados })
                .then(function (r) { return r.json(); })
                .then(function (resposta) {
                    if (resposta.sucesso) {
                        formCheckout.style.display = 'none';
                        confirmacaoCheckout.innerHTML =
                            '✓ Encomenda #' + String(resposta.encomenda_id).padStart(5, '0') + ' confirmada!<br>' +
                            'Obrigado pela tua compra — total: ' + resposta.total + '<br><br>' +
                            (resposta.email_enviado
                                ? '📧 Enviámos um email de confirmação para <strong>' + resposta.email + '</strong>.'
                                : '⚠️ A encomenda foi registada, mas não foi possível enviar o email de confirmação (verifica as definições SMTP em config.php).');
                        confirmacaoCheckout.style.display = 'block';

                        setTimeout(function () {
                            window.location.href = 'carrinho.php';
                        }, 5000);
                    } else {
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = 'CONFIRMAR ENCOMENDA';
                        alert(resposta.erro || 'Não foi possível concluir a encomenda.');
                    }
                })
                .catch(function () {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'CONFIRMAR ENCOMENDA';
                    alert('Não foi possível ligar ao servidor. Tenta novamente.');
                });
        });
    }

});
