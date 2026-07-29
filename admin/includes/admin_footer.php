    </div>
</div>

<script>
(function () {
    const btn = document.getElementById('btn-menu-admin');
    const sidebar = document.getElementById('admin-sidebar');
    const fundo = document.getElementById('admin-overlay-fundo');
    if (!btn || !sidebar) return;

    function fecharMenu() {
        sidebar.classList.remove('aberta');
        btn.classList.remove('aberto');
        fundo.classList.remove('visivel');
    }

    btn.addEventListener('click', function () {
        sidebar.classList.toggle('aberta');
        btn.classList.toggle('aberto');
        fundo.classList.toggle('visivel');
    });

    fundo.addEventListener('click', fecharMenu);

    // fechar ao escolher uma opção do menu
    sidebar.querySelectorAll('.admin-nav a').forEach(function (link) {
        link.addEventListener('click', fecharMenu);
    });
})();
</script>

</body>
</html>
