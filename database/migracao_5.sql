-- ============================================
-- MIGRAÇÃO 5 — redes sociais editáveis
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

INSERT IGNORE INTO definicoes (chave, valor) VALUES
('rede_instagram', 'https://instagram.com/northside'),
('rede_facebook', 'https://facebook.com/northside'),
('rede_tiktok', 'https://tiktok.com/@northside');
