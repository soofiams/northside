    </main>

    <?php
        $contacto_email = buscarDefinicao($pdo, 'contacto_email', 'apoio@northside.pt');
        $contacto_telefone = buscarDefinicao($pdo, 'contacto_telefone', '+351 900 000 000');
    ?>

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
                <a href="<?= URL_BASE ?>devolucoes.php">Devoluções</a>
                <a href="#" data-abrir-modal="modal-privacidade">Política de Privacidade</a>
                <a href="#" data-abrir-modal="modal-contactos">Contactos</a>
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

    <!-- Modal Contactos (email e telemóvel vêm da tabela "definicoes", editáveis sem mexer no código) -->
    <div class="modal-overlay" id="modal-contactos">
        <div class="modal-caixa">
            <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
            <h3>Fala Connosco</h3>
            <p>Estamos disponíveis para o que precisares.</p>
            <div class="contacto-linha"><i class="fa-solid fa-envelope"></i> <a href="mailto:<?= htmlspecialchars($contacto_email) ?>"><?= htmlspecialchars($contacto_email) ?></a></div>
            <div class="contacto-linha"><i class="fa-solid fa-phone"></i> <a href="tel:<?= htmlspecialchars(preg_replace('/\s+/', '', $contacto_telefone)) ?>"><?= htmlspecialchars($contacto_telefone) ?></a></div>
        </div>
    </div>

    <!-- Modal Política de Privacidade -->
    <div class="modal-overlay" id="modal-privacidade">
        <div class="modal-caixa" style="max-width:520px;">
            <button class="fechar-modal" data-fechar-modal aria-label="Fechar">✕</button>
            <h3>Política de Privacidade</h3>
            <div class="modal-privacidade-texto">
                <p>A Northside recolhe apenas os dados necessários para processar as tuas encomendas: nome, email, telefone e morada de entrega.</p>
                <h4>Como usamos os teus dados</h4>
                <p>Os dados fornecidos no checkout são usados exclusivamente para preparar, enviar e confirmar a tua encomenda por email. Não vendemos nem partilhamos os teus dados com terceiros para fins de marketing.</p>
                <h4>Newsletter</h4>
                <p>Se subscreveres a newsletter, o teu email fica guardado apenas para o envio de novidades e promoções da Northside. Podes cancelar a subscrição a qualquer momento.</p>
                <h4>Os teus direitos</h4>
                <p>Podes pedir a qualquer momento para consultar, corrigir ou apagar os teus dados, através dos contactos disponíveis nesta página.</p>
            </div>
        </div>
    </div>

    <!-- Chat privado (cliente ↔ Northside) -->
    <button class="chat-botao" id="chat-botao" aria-label="Abrir chat">
        <i class="fa-solid fa-comment-dots"></i>
    </button>

    <div class="chat-painel" id="chat-painel">
        <div class="chat-cabecalho">
            <span>Fala com a Northside</span>
            <button id="chat-fechar" aria-label="Fechar chat">✕</button>
        </div>

        <!-- Formulário inicial (nome + email), só aparece na primeira vez -->
        <div class="chat-corpo" id="chat-form-inicial">
            <p class="chat-aviso">Deixa o teu nome e email para começarmos a conversa.</p>
            <input type="text" id="chat-nome" placeholder="O teu nome">
            <input type="email" id="chat-email" placeholder="O teu email">
            <button type="button" class="btn-northside" id="btn-chat-iniciar" style="width:100%;margin-top:10px;">COMEÇAR CONVERSA</button>
        </div>

        <!-- Mensagens, só aparece depois de iniciada a conversa -->
        <div class="chat-mensagens" id="chat-mensagens" style="display:none;"></div>

        <form class="chat-form-enviar" id="chat-form-enviar" style="display:none;">
            <input type="text" id="chat-input-mensagem" placeholder="Escreve a tua mensagem..." autocomplete="off">
            <button type="submit" aria-label="Enviar"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
    </div>

    <script src="<?= URL_BASE ?>assets/js/main.js"></script>
</body>
</html>
