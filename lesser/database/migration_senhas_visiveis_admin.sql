USE anime_stream;

ALTER TABLE usuarios
    ADD COLUMN senha_criptografada TEXT NULL AFTER senha;

-- IMPORTANTE:
-- O campo senha continua armazenando password_hash() e é usado para o login.
-- O campo senha_criptografada usa AES-256-GCM e somente o administrador
-- autenticado consegue visualizá-lo pelo painel.
--
-- Senhas que existiam antes desta migração e possuem apenas o hash NÃO podem
-- ser recuperadas. Para essas contas, o administrador deve definir uma nova
-- senha; a nova senha será gravada nos dois formatos automaticamente.
