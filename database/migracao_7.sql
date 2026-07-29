-- ============================================
-- MIGRAÇÃO 7 — código de desconto automático por email (newsletter)
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

SET @c1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='northside' AND TABLE_NAME='codigos_desconto' AND COLUMN_NAME='uso_unico');
SET @sql1 = IF(@c1 = 0, 'ALTER TABLE codigos_desconto ADD COLUMN uso_unico TINYINT(1) NOT NULL DEFAULT 0', 'SELECT "ja existia"');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;

SET @c2 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='northside' AND TABLE_NAME='codigos_desconto' AND COLUMN_NAME='usado');
SET @sql2 = IF(@c2 = 0, 'ALTER TABLE codigos_desconto ADD COLUMN usado TINYINT(1) NOT NULL DEFAULT 0', 'SELECT "ja existia"');
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @c3 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='northside' AND TABLE_NAME='codigos_desconto' AND COLUMN_NAME='usado_em');
SET @sql3 = IF(@c3 = 0, 'ALTER TABLE codigos_desconto ADD COLUMN usado_em TIMESTAMP NULL', 'SELECT "ja existia"');
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

SET @c4 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='northside' AND TABLE_NAME='codigos_desconto' AND COLUMN_NAME='email_associado');
SET @sql4 = IF(@c4 = 0, 'ALTER TABLE codigos_desconto ADD COLUMN email_associado VARCHAR(150) NULL', 'SELECT "ja existia"');
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

INSERT IGNORE INTO definicoes (chave, valor) VALUES ('newsletter_desconto_percentagem', '0.10');
