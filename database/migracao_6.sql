-- ============================================
-- MIGRAÇÃO 6 — ordem das categorias (controla a sequência na navbar)
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

SET @coluna_existe = (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'northside' AND TABLE_NAME = 'categorias' AND COLUMN_NAME = 'ordem'
);
SET @sql = IF(@coluna_existe = 0,
    'ALTER TABLE categorias ADD COLUMN ordem INT NOT NULL DEFAULT 0',
    'SELECT "coluna ordem já existia"');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- dar uma ordem inicial às categorias que já tens, pela ordem alfabética atual
SET @n = 0;
UPDATE categorias SET ordem = (@n := @n + 1) ORDER BY nome;
