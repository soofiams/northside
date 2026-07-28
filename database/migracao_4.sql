-- ============================================
-- MIGRAÇÃO 4 — valores de envio editáveis (não fixos no código)
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

INSERT IGNORE INTO definicoes (chave, valor) VALUES
('envio_gratis_acima_de', '50.00'),
('envio_custo', '4.99');
