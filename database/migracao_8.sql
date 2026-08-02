-- ============================================
-- MIGRAÇÃO 8 — integração com a Stripe (pagamentos a sério)
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

SET @c1 = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='northside' AND TABLE_NAME='encomendas' AND COLUMN_NAME='stripe_session_id');
SET @sql1 = IF(@c1 = 0, 'ALTER TABLE encomendas ADD COLUMN stripe_session_id VARCHAR(255) NULL UNIQUE', 'SELECT "ja existia"');
PREPARE stmt1 FROM @sql1; EXECUTE stmt1; DEALLOCATE PREPARE stmt1;
