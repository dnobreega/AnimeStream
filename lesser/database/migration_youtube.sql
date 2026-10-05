-- =========================================================
-- MIGRATION: suporte a vídeos hospedados no YouTube
-- Banco: anime_stream
-- Execute uma vez no MySQL Workbench.
-- =========================================================

USE anime_stream;

ALTER TABLE episodios
MODIFY COLUMN video_tipo ENUM('mp4','hls','youtube') NOT NULL DEFAULT 'mp4';

-- Para vídeos do YouTube, a coluna episodios.video passa a armazenar
-- somente o ID do vídeo (ex.: abc123XYZ01), e video_tipo = 'youtube'.
