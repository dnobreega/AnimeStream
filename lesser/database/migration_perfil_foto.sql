USE anime_stream;

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS foto_perfil VARCHAR(500) NULL AFTER atualizado_em;
