// ============================================
// NORTHSIDE — Interações do site
// ============================================

document.addEventListener('DOMContentLoaded', function () {

    // ---- 1. Alternar imagens ao passar o rato nos cartões de produto ----
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

    // ---- 2. Modal de newsletter (10% de desconto) ----
    const modal = document.getElementById('modal-newsletter');
    const abrirBotoes = document.querySelectorAll('[data-abrir-newsletter]');
    const fecharBotoes = document.querySelectorAll('[data-fechar-newsletter]');
    const formModal = document.getElementById('form-newsletter-modal');
    const confirmacao = document.getElementById('modal-confirmacao');

    abrirBotoes.forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (modal) modal.classList.add('aberto');
        });
    });

    fecharBotoes.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (modal) modal.classList.remove('aberto');
        });
    });

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) modal.classList.remove('aberto');
        });
    }

    if (formModal) {
        formModal.addEventListener('submit', function (e) {
            e.preventDefault();
            formModal.style.display = 'none';
            if (confirmacao) confirmacao.style.display = 'block';
            setTimeout(function () {
                if (modal) modal.classList.remove('aberto');
            }, 2200);
        });
    }

});
