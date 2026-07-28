-- ============================================
-- NORTHSIDE — Base de Dados
-- Importa este ficheiro no phpMyAdmin ou via:
-- mysql -u utilizador -p < schema.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS northside CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE northside;

-- ===== Categorias (menu) =====
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- ===== Produtos =====
CREATE TABLE IF NOT EXISTS produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    descricao TEXT,
    preco DECIMAL(10,2) NOT NULL,
    imagem VARCHAR(255) DEFAULT 'sem-imagem.jpg',
    categoria_id INT NULL,
    stock INT NOT NULL DEFAULT 0,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    estrelas_media DECIMAL(2,1) NOT NULL DEFAULT 5.0,
    num_avaliacoes INT NOT NULL DEFAULT 0,
    especificacoes JSON NULL COMMENT 'pares chave/valor, ex: {"Garantia":"24 meses"}',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===== Avaliações de clientes (página avaliacoes.php e destaques da homepage) =====
CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NULL,
    nome_cliente VARCHAR(100) NOT NULL,
    estrelas TINYINT NOT NULL DEFAULT 5,
    comentario TEXT NOT NULL,
    aprovado TINYINT(1) NOT NULL DEFAULT 1,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===== Tamanhos por produto (roupa: XS a XXL), com stock próprio por tamanho =====
CREATE TABLE IF NOT EXISTS produto_tamanhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tamanho ENUM('XS','S','M','L','XL','XXL') NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    UNIQUE KEY produto_tamanho_unico (produto_id, tamanho),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Definições gerais do site, editáveis sem mexer no código (ex: contactos) =====
CREATE TABLE IF NOT EXISTS definicoes (
    chave VARCHAR(60) PRIMARY KEY,
    valor TEXT NOT NULL
) ENGINE=InnoDB;

-- ===== Códigos de desconto =====
CREATE TABLE IF NOT EXISTS codigos_desconto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    percentagem DECIMAL(4,2) NOT NULL COMMENT 'ex: 0.10 = 10%',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    validade DATE NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Encomendas =====
CREATE TABLE IF NOT EXISTS encomendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_cliente VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefone VARCHAR(30) NOT NULL,
    morada VARCHAR(255) NOT NULL,
    codigo_postal VARCHAR(20) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    metodo_pagamento VARCHAR(30) NOT NULL,
    codigo_desconto_id INT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    valor_desconto DECIMAL(10,2) NOT NULL DEFAULT 0,
    envio DECIMAL(10,2) NOT NULL DEFAULT 0,
    total DECIMAL(10,2) NOT NULL,
    estado ENUM('pendente','confirmada','enviada','entregue','cancelada') NOT NULL DEFAULT 'confirmada',
    email_enviado TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (codigo_desconto_id) REFERENCES codigos_desconto(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===== Itens de cada encomenda (guarda "fotografia" do produto no momento da compra) =====
CREATE TABLE IF NOT EXISTS encomenda_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encomenda_id INT NOT NULL,
    produto_id INT NULL,
    nome_produto VARCHAR(150) NOT NULL,
    preco_unitario DECIMAL(10,2) NOT NULL,
    quantidade INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tamanho VARCHAR(5) NULL,
    FOREIGN KEY (encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ===== Subscritores da newsletter =====
CREATE TABLE IF NOT EXISTS newsletter_subscritores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ===== Devoluções =====
CREATE TABLE IF NOT EXISTS devolucoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encomenda_id INT NOT NULL,
    encomenda_item_id INT NOT NULL,
    motivo TEXT NOT NULL,
    estado ENUM('pendente','aprovada','rejeitada','concluida') NOT NULL DEFAULT 'pendente',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (encomenda_id) REFERENCES encomendas(id) ON DELETE CASCADE,
    FOREIGN KEY (encomenda_item_id) REFERENCES encomenda_itens(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Chat privado entre cliente e Northside =====
CREATE TABLE IF NOT EXISTS chat_conversas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_atividade TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversa_id INT NOT NULL,
    remetente ENUM('cliente','northside') NOT NULL,
    mensagem TEXT NOT NULL,
    lida TINYINT(1) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversa_id) REFERENCES chat_conversas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ===== Administradores do backoffice (criados via admin/setup.php, numa fase seguinte) =====
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilizador VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;


-- ============================================
-- Dados iniciais
-- ============================================

INSERT INTO categorias (nome, slug) VALUES
('Eletrónicos', 'eletronicos'),
('Casa', 'casa'),
('Acessórios', 'acessorios'),
('Estilo', 'estilo'),
('Desporto', 'desporto');

INSERT INTO produtos (nome, descricao, preco, imagem, categoria_id, stock, destaque, estrelas_media, num_avaliacoes, especificacoes) VALUES
('Auriculares Sem Fios Pro', 'Auriculares bluetooth com cancelamento de ruído ativo, até 24h de autonomia com a caixa de carregamento.', 24.99, 'sem-imagem.jpg', 1, 15, 1, 5.0, 128,
    '{"Garantia":"24 meses","Potência / Amperagem":"5V ⎓ 2A (entrada USB-C)","Velocidade de carregamento":"Carregamento rápido — 1h30 até 100%","Autonomia":"Até 24h com a caixa de carregamento"}'),
('Smartwatch X1', 'Smartwatch com monitor de frequência cardíaca, GPS integrado e resistência à água.', 34.99, 'sem-imagem.jpg', 1, 8, 1, 4.5, 96,
    '{"Garantia":"24 meses","Potência / Amperagem":"5V ⎓ 1A (carregamento magnético)","Velocidade de carregamento":"Carga completa em 1h15","Autonomia":"Até 7 dias em uso normal"}'),
('Fita LED RGB 5M', 'Fita LED RGB de 5 metros, controlada por aplicação móvel, com milhões de combinações de cor.', 16.99, 'sem-imagem.jpg', 2, 0, 1, 4.5, 73,
    '{"Garantia":"12 meses","Potência / Amperagem":"12V ⎓ 2A","Velocidade de carregamento":"Não aplicável","Autonomia":"Ligação contínua à corrente"}'),
('Power Bank 20.000mAh', 'Bateria portátil de alta capacidade com carregamento rápido e duas saídas USB.', 29.99, 'sem-imagem.jpg', 1, 20, 1, 5.0, 112,
    '{"Garantia":"24 meses","Potência / Amperagem":"5V ⎓ 3A (saída rápida)","Velocidade de carregamento":"Carrega um telemóvel médio 4x","Autonomia":"20.000mAh de capacidade"}'),
('Garrafa Térmica 1L', 'Garrafa térmica em aço inoxidável, mantém a temperatura até 12 horas.', 19.99, 'sem-imagem.jpg', 3, 12, 1, 5.0, 58,
    '{"Garantia":"12 meses","Capacidade":"1 litro","Material":"Aço inoxidável de parede dupla","Autonomia térmica":"Até 12h quente / 24h frio"}'),
('Boné Northside', 'Boné em algodão bordado com o logótipo Northside.', 14.99, 'sem-imagem.jpg', 4, 30, 1, 4.5, 42,
    '{"Garantia":"Trocas até 30 dias","Material":"100% algodão","Ajuste":"Fivela regulável","Origem":"Desenhado no Porto"}'),
('Auriculares Over-Ear ANC', 'Auriculares over-ear com cancelamento de ruído ativo premium e som de alta fidelidade.', 199.99, 'sem-imagem.jpg', 1, 10, 0, 5.0, 64,
    '{"Garantia":"24 meses","Potência / Amperagem":"5V ⎓ 2A (USB-C)","Velocidade de carregamento":"10 min = 5h de música","Autonomia":"Até 30h com ANC ativo"}'),
('Hoodie Northside', 'Hoodie unissexo em algodão pesado, com o logótipo Northside bordado no peito.', 39.99, 'sem-imagem.jpg', 4, 18, 1, 5.0, 37,
    '{"Garantia":"Trocas até 30 dias","Material":"80% algodão, 20% poliéster","Tamanhos disponíveis":"XS a XXL","Origem":"Desenhado no Porto"}'),
('Camisola Northside', 'Camisola de malha leve, corte unissexo, com o logótipo Northside bordado.', 29.99, 'sem-imagem.jpg', 4, 22, 0, 4.5, 25,
    '{"Garantia":"Trocas até 30 dias","Material":"100% algodão","Tamanhos disponíveis":"XS a XXL","Origem":"Desenhado no Porto"}'),
('T-shirt Northside', 'T-shirt básica em algodão, com o logótipo Northside estampado.', 19.99, 'sem-imagem.jpg', 4, 40, 0, 5.0, 51,
    '{"Garantia":"Trocas até 30 dias","Material":"100% algodão","Tamanhos disponíveis":"XS a XXL","Origem":"Desenhado no Porto"}');

INSERT INTO avaliacoes (produto_id, nome_cliente, estrelas, comentario) VALUES
(1, 'Miguel C.', 5, 'Os auriculares chegaram super rápido e a qualidade de som é impressionante para o preço.'),
(4, 'Rita A.', 5, 'Comprei o power bank para viagens e nunca mais fiquei sem bateria. Recomendo!'),
(6, 'Tiago F.', 4, 'Boné com ótimo acabamento, exatamente como nas fotos. Entrega dentro do prazo.'),
(5, 'Beatriz S.', 5, 'A garrafa térmica mantém mesmo a temperatura o dia todo. Já comprei uma segunda de presente.'),
(NULL, 'André P.', 4, 'Bom apoio ao cliente, tive uma dúvida e responderam rapidamente. Voltarei a comprar.'),
(2, 'Sofia M.', 5, 'O smartwatch tem uma bateria incrível e a app é muito intuitiva. Superou as expectativas.'),
(NULL, 'Ricardo N.', 5, 'Site fácil de usar e envio mesmo rápido, chegou em 2 dias. Vou voltar a comprar de certeza.'),
(7, 'Carolina D.', 4, 'Os auriculares over-ear têm um cancelamento de ruído muito bom, ótimos para o trabalho.'),
(3, 'Hugo T.', 5, 'Adorei a fita LED, fácil de instalar e a app funciona muito bem. Deu um ar novo ao quarto.');

INSERT INTO codigos_desconto (codigo, percentagem, ativo) VALUES
('NORTHSIDE10', 0.10, 1),
('BEMVINDO15', 0.15, 1),
('PORTO20', 0.20, 1);

-- Tamanhos da roupa (produtos 8=Hoodie, 9=Camisola, 10=T-shirt).
-- Alguns tamanhos ficam a 0 de propósito, para veres o "Indisponível" em ação.
INSERT INTO produto_tamanhos (produto_id, tamanho, stock) VALUES
(8, 'XS', 4), (8, 'S', 10), (8, 'M', 12), (8, 'L', 8), (8, 'XL', 0), (8, 'XXL', 3),
(9, 'XS', 6), (9, 'S', 9), (9, 'M', 11), (9, 'L', 0), (9, 'XL', 5), (9, 'XXL', 0),
(10, 'XS', 12), (10, 'S', 15), (10, 'M', 18), (10, 'L', 14), (10, 'XL', 9), (10, 'XXL', 0);

-- Contactos (editáveis aqui, ou mais tarde através do backoffice)
INSERT INTO definicoes (chave, valor) VALUES
('contacto_email', 'apoio@northside.pt'),
('contacto_telefone', '+351 900 000 000'),
('envio_gratis_acima_de', '50.00'),
('envio_custo', '4.99');

-- Nota: a conta de administrador é criada mais tarde através de admin/setup.php (fase do backoffice)
