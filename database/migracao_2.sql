-- ============================================
-- MIGRAÇÃO 2 — tamanhos de roupa, contactos editáveis, avaliação após compra
-- Corre isto no phpMyAdmin (aba "SQL") se já tinhas a base de dados importada
-- antes. Se estiveres a instalar a loja pela primeira vez, não precisas deste
-- ficheiro — o schema.sql já inclui tudo.
-- ============================================

USE northside;

CREATE TABLE IF NOT EXISTS produto_tamanhos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produto_id INT NOT NULL,
    tamanho ENUM('XS','S','M','L','XL','XXL') NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    UNIQUE KEY produto_tamanho_unico (produto_id, tamanho),
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS definicoes (
    chave VARCHAR(60) PRIMARY KEY,
    valor TEXT NOT NULL
) ENGINE=InnoDB;

-- Adiciona a coluna "tamanho" a encomenda_itens, só se ainda não existir
SET @coluna_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'northside' AND TABLE_NAME = 'encomenda_itens' AND COLUMN_NAME = 'tamanho'
);
SET @sql = IF(@coluna_existe = 0,
    'ALTER TABLE encomenda_itens ADD COLUMN tamanho VARCHAR(5) NULL AFTER subtotal',
    'SELECT "coluna tamanho já existia"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Dados dos tamanhos (só insere se a tabela ainda estiver vazia)
INSERT INTO produto_tamanhos (produto_id, tamanho, stock)
SELECT * FROM (SELECT 8 AS produto_id, 'XS' AS tamanho, 4 AS stock
    UNION ALL SELECT 8, 'S', 10
    UNION ALL SELECT 8, 'M', 12
    UNION ALL SELECT 8, 'L', 8
    UNION ALL SELECT 8, 'XL', 0
    UNION ALL SELECT 8, 'XXL', 3
    UNION ALL SELECT 9, 'XS', 6
    UNION ALL SELECT 9, 'S', 9
    UNION ALL SELECT 9, 'M', 11
    UNION ALL SELECT 9, 'L', 0
    UNION ALL SELECT 9, 'XL', 5
    UNION ALL SELECT 9, 'XXL', 0
    UNION ALL SELECT 10, 'XS', 12
    UNION ALL SELECT 10, 'S', 15
    UNION ALL SELECT 10, 'M', 18
    UNION ALL SELECT 10, 'L', 14
    UNION ALL SELECT 10, 'XL', 9
    UNION ALL SELECT 10, 'XXL', 0
) AS dados
WHERE NOT EXISTS (SELECT 1 FROM produto_tamanhos LIMIT 1);

INSERT IGNORE INTO definicoes (chave, valor) VALUES
('contacto_email', 'apoio@northside.pt'),
('contacto_telefone', '+351 900 000 000');
