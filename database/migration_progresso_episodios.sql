USE anime_stream;

CREATE TABLE IF NOT EXISTS progresso_episodios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    episodio_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    tempo_assistido DECIMAL(10,2) NOT NULL DEFAULT 0,
    duracao DECIMAL(10,2) NOT NULL DEFAULT 0,
    concluido TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_progresso_episodio FOREIGN KEY (episodio_id) REFERENCES episodios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_progresso_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_progresso_usuario_episodio (usuario_id, episodio_id),
    INDEX idx_progresso_usuario_atualizado (usuario_id, atualizado_em),
    INDEX idx_progresso_episodio (episodio_id)
) ENGINE=InnoDB;
