    </main>

    <footer class="footer">
        <div class="barra-pagamentos">
            <span class="pagamentos-label">Métodos de pagamento disponíveis</span>
            <div class="pagamentos-badges">
                <span class="metodo-pagamento metodo-mbway">MB WAY</span>
                <span class="metodo-pagamento metodo-klarna">Klarna</span>
                <span class="metodo-pagamento metodo-icone"><i class="fab fa-apple-pay"></i></span>
                <span class="metodo-pagamento metodo-icone"><i class="fab fa-google-pay"></i></span>
            </div>
        </div>
        <div class="footer-simples">
            <a href="<?= URL_BASE ?>index.php" class="logo-northside">NORTHSIDE<small>WEAR YOUR STORY</small></a>
            <nav class="footer-links-simples">
                <a href="#" data-abrir-modal="modal-sobre">Sobre Nós</a>
                <a href="#" data-abrir-modal="modal-newsletter">Newsletter</a>
                <a href="#">Política de Privacidade</a>
                <a href="#">Contactos</a>
            </nav>
            <div class="footer-social">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© 2026 NORTHSIDE • Todos os direitos reservados.</span>
        </div>
    </footer>

    <!-- Modal de newsletter -->
    <div class="modal-overlay" id="modal-newsletter">
        <div class="modal-caixa">
            <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
            <div class="badge-desconto">-10% NA 1ª COMPRA</div>
            <h3>Junta-te à Northside</h3>
            <p>Subscreve a nossa newsletter e recebe já o teu código de desconto por email.</p>
            <form class="form-newsletter-modal" id="form-newsletter-modal" action="<?= URL_BASE ?>actions/newsletter_subscrever.php" method="post">
                <input type="email" name="email" placeholder="O teu email" required>
                <button type="submit" class="btn-northside">QUERO O DESCONTO</button>
            </form>
            <div class="modal-confirmacao" id="modal-confirmacao">✓ Verifica o teu email — o código está a caminho!</div>
        </div>
    </div>

    <!-- Modal Sobre Nós -->
    <div class="modal-overlay" id="modal-sobre">
        <div class="modal-caixa">
            <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
            <h3>Sobre a Northside</h3>
            <p style="margin-bottom:10px;">A Northside nasceu em 2026, no coração do Porto.</p>
            <p style="margin-bottom:10px;">Foi criada por duas pessoas apaixonadas pela cidade e pela sua história.</p>
            <p style="margin-bottom:0;">Uma marca portuguesa, pensada para chegar ao mundo inteiro.</p>
        </div>
    </div>

    <script src="<?= URL_BASE ?>assets/js/main.js"></script>
</body>
</html>
