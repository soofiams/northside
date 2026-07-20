-- ============================================
-- NORTHSIDE — Base de Dados
-- Importa este ficheiro no phpMyAdmin ou via:
-- mysql -u utilizador -p < schema.sql
-- ============================================

CREATE DATABASE IF NOT EXISTS northside CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE northside;

-- Categorias do menu (Eletrónicos, Casa, Acessórios, Estilo, Desporto)
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL,
    slug VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- Produtos
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
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Utilizadores do painel de administração
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

INSERT INTO produtos (nome, descricao, preco, imagem, categoria_id, stock, destaque) VALUES
('Auriculares Sem Fios Pro', 'Auriculares bluetooth com cancelamento de ruído ativo, até 24h de autonomia com a caixa de carregamento.', 24.99, 'sem-imagem.jpg', 1, 15, 1),
('Smartwatch X1', 'Smartwatch com monitor de frequência cardíaca, GPS integrado e resistência à água.', 34.99, 'sem-imagem.jpg', 1, 8, 1),
('Fita LED RGB 5M', 'Fita LED RGB de 5 metros, controlada por aplicação móvel, com milhões de combinações de cor.', 16.99, 'sem-imagem.jpg', 2, 0, 1),
('Power Bank 20.000mAh', 'Bateria portátil de alta capacidade com carregamento rápido e duas saídas USB.', 29.99, 'sem-imagem.jpg', 1, 20, 1),
('Garrafa Térmica 1L', 'Garrafa térmica em aço inoxidável, mantém a temperatura até 12 horas.', 19.99, 'sem-imagem.jpg', 4, 12, 1),
('Boné Northside', 'Boné em algodão bordado com o logótipo Northside.', 14.99, 'sem-imagem.jpg', 4, 30, 1);

-- Nota: a conta de administrador é criada através de admin/setup.php
-- (não incluímos aqui uma password pré-definida por segurança)
