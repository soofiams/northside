-- ============================================
-- CORREÇÃO — associa os tamanhos aos produtos de roupa pelo NOME,
-- em vez de pelo ID (que pode não bater certo consoante a ordem
-- em que os produtos foram inseridos na tua base de dados).
--
-- Corre isto no phpMyAdmin (aba "SQL"). É seguro correr mais do
-- que uma vez — atualiza os valores em vez de duplicar linhas.
-- ============================================

USE northside;

INSERT INTO produto_tamanhos (produto_id, tamanho, stock)
SELECT id, 'XS', 4 FROM produtos WHERE nome = 'Hoodie Northside'
UNION ALL SELECT id, 'S', 10 FROM produtos WHERE nome = 'Hoodie Northside'
UNION ALL SELECT id, 'M', 12 FROM produtos WHERE nome = 'Hoodie Northside'
UNION ALL SELECT id, 'L', 8 FROM produtos WHERE nome = 'Hoodie Northside'
UNION ALL SELECT id, 'XL', 0 FROM produtos WHERE nome = 'Hoodie Northside'
UNION ALL SELECT id, 'XXL', 3 FROM produtos WHERE nome = 'Hoodie Northside'

UNION ALL SELECT id, 'XS', 6 FROM produtos WHERE nome = 'Camisola Northside'
UNION ALL SELECT id, 'S', 9 FROM produtos WHERE nome = 'Camisola Northside'
UNION ALL SELECT id, 'M', 11 FROM produtos WHERE nome = 'Camisola Northside'
UNION ALL SELECT id, 'L', 0 FROM produtos WHERE nome = 'Camisola Northside'
UNION ALL SELECT id, 'XL', 5 FROM produtos WHERE nome = 'Camisola Northside'
UNION ALL SELECT id, 'XXL', 0 FROM produtos WHERE nome = 'Camisola Northside'

UNION ALL SELECT id, 'XS', 12 FROM produtos WHERE nome = 'T-shirt Northside'
UNION ALL SELECT id, 'S', 15 FROM produtos WHERE nome = 'T-shirt Northside'
UNION ALL SELECT id, 'M', 18 FROM produtos WHERE nome = 'T-shirt Northside'
UNION ALL SELECT id, 'L', 14 FROM produtos WHERE nome = 'T-shirt Northside'
UNION ALL SELECT id, 'XL', 9 FROM produtos WHERE nome = 'T-shirt Northside'
UNION ALL SELECT id, 'XXL', 0 FROM produtos WHERE nome = 'T-shirt Northside'

ON DUPLICATE KEY UPDATE stock = VALUES(stock);

-- Para confirmares que resultou, corre esta consulta a seguir:
-- SELECT p.nome, pt.tamanho, pt.stock
-- FROM produto_tamanhos pt JOIN produtos p ON p.id = pt.produto_id
-- ORDER BY p.nome, FIELD(pt.tamanho,'XS','S','M','L','XL','XXL');
