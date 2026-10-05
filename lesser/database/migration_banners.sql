-- YSA — sistema de banners do carrossel principal
-- Execute este arquivo uma única vez no banco anime_stream.

USE anime_stream;

CREATE TABLE IF NOT EXISTS banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anime_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao VARCHAR(1000) NULL,
    imagem VARCHAR(500) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_banner_anime FOREIGN KEY (anime_id)
        REFERENCES animes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX idx_banner_ativo_ordem (ativo, ordem, atualizado_em),
    INDEX idx_banner_anime (anime_id)
) ENGINE=InnoDB;

-- Cria banners iniciais para os animes que já estão marcados como destaque.
-- O poster atual é usado como imagem inicial apenas para não deixar o carrossel vazio.
INSERT INTO banners (anime_id, titulo, descricao, imagem, ordem, ativo)
SELECT a.id, a.titulo, a.sinopse, a.poster, 0, 1
FROM animes a
WHERE a.destaque = 1
  AND NOT EXISTS (
      SELECT 1
      FROM banners b
      WHERE b.anime_id = a.id
  );
