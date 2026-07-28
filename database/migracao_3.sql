-- ============================================
-- MIGRAÇÃO 3 — Devoluções e Chat
-- Corre isto no phpMyAdmin (aba "SQL").
-- ============================================

USE northside;

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
