USE anime_stream;

ALTER TABLE usuarios
ADD COLUMN senha_admin_enc VARCHAR(1000) NULL AFTER senha;
