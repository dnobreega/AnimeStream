CREATE DATABASE IF NOT EXISTS anime_stream CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE anime_stream;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS progresso_episodios;
DROP TABLE IF EXISTS comentarios;
DROP TABLE IF EXISTS tokens_recuperacao;
DROP TABLE IF EXISTS tokens_lembrar;
DROP TABLE IF EXISTS episodios;
DROP TABLE IF EXISTS temporadas;
DROP TABLE IF EXISTS animes;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(80) NOT NULL,
    email VARCHAR(160) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    senha_criptografada TEXT NULL,
    tipo ENUM('user','admin') NOT NULL DEFAULT 'user',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    foto_perfil VARCHAR(500) NULL,
    INDEX idx_usuario_tipo (tipo)
) ENGINE=InnoDB;

CREATE TABLE animes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    tipo VARCHAR(40) NOT NULL DEFAULT 'Série',
    ano SMALLINT NULL,
    generos VARCHAR(255) NOT NULL,
    estudio VARCHAR(120) NOT NULL,
    sinopse TEXT NULL,
    poster VARCHAR(500) NOT NULL,
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_anime_destaque (destaque)
) ENGINE=InnoDB;

CREATE TABLE banners (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anime_id INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao VARCHAR(1000) NULL,
    imagem VARCHAR(500) NOT NULL,
    ordem INT NOT NULL DEFAULT 0,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_banner_anime FOREIGN KEY (anime_id) REFERENCES animes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_banner_ativo_ordem (ativo, ordem, atualizado_em),
    INDEX idx_banner_anime (anime_id)
) ENGINE=InnoDB;

CREATE TABLE temporadas (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    anime_id INT UNSIGNED NOT NULL,
    numero INT UNSIGNED NOT NULL,
    titulo VARCHAR(120) NOT NULL,
    descricao VARCHAR(500) NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_temporada_anime FOREIGN KEY (anime_id) REFERENCES animes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_temporada_anime_numero (anime_id, numero),
    INDEX idx_temporada_anime (anime_id)
) ENGINE=InnoDB;

CREATE TABLE episodios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    temporada_id INT UNSIGNED NOT NULL,
    numero INT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao VARCHAR(1000) NULL,
    thumb VARCHAR(500) NULL,
    video VARCHAR(1000) NULL,
    video_tipo ENUM('mp4','hls','youtube') NOT NULL DEFAULT 'mp4',
    destaque TINYINT(1) NOT NULL DEFAULT 0,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_episodio_temporada FOREIGN KEY (temporada_id) REFERENCES temporadas(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_episodio_temporada_numero (temporada_id, numero),
    INDEX idx_episodio_destaque (destaque)
) ENGINE=InnoDB;

CREATE TABLE comentarios (
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

CREATE TABLE progresso_episodios (
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

CREATE TABLE tokens_lembrar (
    selector VARCHAR(64) PRIMARY KEY,
    token_hash CHAR(64) NOT NULL,
    usuario_id INT UNSIGNED NOT NULL,
    expira_em DATETIME NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lembrar_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE tokens_recuperacao (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    token_hash CHAR(64) NOT NULL UNIQUE,
    usuario_id INT UNSIGNED NOT NULL,
    expira_em DATETIME NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_recuperacao_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

INSERT INTO usuarios(nome,email,senha,senha_criptografada,tipo) VALUES
('Administrador','admin@ysa.local','$2y$12$eI4hj3HoPt7TSd7DN4EvbeOI9QhN90K61KIyFuv690J1w0lY8ATIG','dD9SGiTW2vvlYMmwfUU0ZBUqnVnLlVEH9INWgQlPk/AGXzSZvm8z','admin'),
('Usuário Demo','usuario@ysa.local','$2y$12$TLHDK9MNTv1Dw12.4pzQwuz9u6LTqQNb38eAQ3TSBm2BiaQsBJvX.','pO/dNMwoAnqEG8msN4idkIEuL4CFqFSDwy7uT+1z1BybL8DWKgYyEYg=','user');

INSERT INTO animes(titulo,slug,tipo,ano,generos,estudio,sinopse,poster,destaque) VALUES
('Black Clover','black-clover','Série',2017,'Ação • Fantasia • Magia','Studio Pierrot','Em um mundo onde a magia define a vida de todos, Asta nasce sem poder mágico, mas encontra uma forma única de enfrentar esse limite ao lado de seu rival Yuno.','assets/img/anime/black-clover.svg',1),
('Classroom of the Elite','classroom-of-the-elite','Série',2017,'Psicológico • Escolar • Drama','Lerche','Uma escola de elite reúne estudantes em um sistema de competição em que desempenho, estratégia e relações sociais afetam diretamente os privilégios de cada turma.','assets/img/anime/classroom-of-the-elite.svg',1),
('Haikyuu!!','haikyuu','Série',2014,'Esporte • Vôlei • Escolar','Production I.G','Hinata se apaixona pelo vôlei e entra para o time de Karasuno, onde reencontra Kageyama, seu antigo rival, iniciando uma jornada de crescimento e competição.','assets/img/anime/haikyuu.svg',1),
('Frieren','frieren','Série',2023,'Fantasia • Aventura • Drama','Madhouse','Após a derrota do Rei Demônio, Frieren segue uma nova jornada enquanto percebe como o tempo e as relações humanas transformam aquilo que ela conhece.','assets/img/anime/frieren.svg',1),
('Re:Zero','re-zero','Série',2016,'Fantasia • Isekai • Drama','WHITE FOX','Subaru é transportado para outro mundo e descobre que possui uma habilidade ligada ao retorno após a morte, precisando aprender com cada tentativa para proteger quem encontrou.','assets/img/anime/re-zero.svg',1),
('Mushoku Tensei: Jobless Reincarnation','mushoku-tensei','Série',2021,'Fantasia • Isekai • Aventura','Studio Bind','Um homem renasce em um mundo de fantasia como Rudeus Greyrat e decide usar a nova vida para superar arrependimentos e desenvolver suas habilidades.','assets/img/anime/mushoku-tensei.svg',0),
('Demon Slayer','demon-slayer','Série',2019,'Ação • Fantasia • Sobrenatural','ufotable','Tanjiro entra para o Corpo de Caçadores de Demônios depois que sua família é atacada e sua irmã é transformada, iniciando uma jornada de treinamento e combate.','assets/img/anime/demon-slayer.svg',1),
('Steel Ball Run','steel-ball-run','Série',NULL,'Ação • Aventura • Corrida','—','Uma corrida atravessa os Estados Unidos em uma jornada de velocidade, alianças, estratégias e confrontos. A página usa arte de placeholder para você substituir pelos materiais licenciados que quiser utilizar.','assets/img/anime/steel-ball-run.svg',1);

INSERT INTO temporadas(anime_id,numero,titulo,descricao)
SELECT id,1,'Temporada 1','Temporada inicial cadastrada automaticamente.' FROM animes;

INSERT INTO episodios(temporada_id,numero,titulo,descricao,thumb,video,video_tipo,destaque)
SELECT t.id,1,'Episódio 1 — Demonstração','Episódio de demonstração. Cadastre o vídeo autorizado no painel administrativo.',a.poster,CASE WHEN a.slug='steel-ball-run' THEN 'assets/video/steel-ball-run.mp4' ELSE NULL END,'mp4',CASE WHEN a.slug IN ('black-clover','haikyuu','frieren','re-zero','demon-slayer','steel-ball-run') THEN 1 ELSE 0 END
FROM temporadas t INNER JOIN animes a ON a.id=t.anime_id WHERE t.numero=1;

-- O vídeo local do projeto, se existir, pode ser vinculado manualmente ao episódio do Steel Ball Run:
-- UPDATE episodios e JOIN temporadas t ON t.id=e.temporada_id JOIN animes a ON a.id=t.anime_id
-- SET e.video='assets/video/steel-ball-run.mp4', e.video_tipo='mp4'
-- WHERE a.slug='steel-ball-run' AND t.numero=1 AND e.numero=1;
