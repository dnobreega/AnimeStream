USE anime_stream;

CREATE TABLE IF NOT EXISTS comentarios (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    episodio_id INT UNSIGNED NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    texto VARCHAR(1000) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentario_episodio FOREIGN KEY (episodio_id) REFERENCES episodios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comentario_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_comentario_episodio (episodio_id, criado_em),
    INDEX idx_comentario_usuario (usuario_id, criado_em)
) ENGINE=InnoDB;
