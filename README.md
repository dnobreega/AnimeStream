# YSA — Your Secrect Archive

Projeto de streaming educacional em **PHP + MySQL + HTML + CSS + JavaScript**.

## Animes incluídos

- Black Clover
- Classroom of the Elite
- Haikyuu!!
- Frieren
- Re:Zero
- Mushoku Tensei: Jobless Reincarnation
- Demon Slayer
- Steel Ball Run

## Requisitos

- XAMPP com Apache e PHP
- MySQL Server 8+ (pode ser gerenciado pelo MySQL Workbench)
- Porta MySQL padrão do projeto: **3307**

## Instalação

1. Coloque a pasta `AnimeStream` em `C:\xampp\htdocs\`.
2. Ligue o Apache e o MySQL Server.
3. No MySQL Workbench, abra `database/schema.sql` e execute tudo.
4. Confira `includes/config.php`:

   ```php
   const BASE_URL = '/AnimeStream';
   const DB_HOST = '127.0.0.1';
   const DB_PORT = '3307';
   const DB_NAME = 'anime_stream';
   const DB_USER = 'root';
   const DB_PASS = '';
   ```

5. Abra `http://127.0.0.1/AnimeStream/`.

## Login de teste

Administrador:
- E-mail: `admin@ysa.local`
- Senha: `Admin@12345`

Usuário normal:
- E-mail: `usuario@ysa.local`
- Senha: `Usuario@12345`

## Painel admin

`http://127.0.0.1/AnimeStream/admin/`

É possível gerenciar:

- animes;
- temporadas;
- episódios;
- usuários e permissões;
- thumbnails e caminhos/URLs de vídeo;
- destaque do catálogo.

## Vídeos locais

Coloque o arquivo em `assets/video/` e no episódio use um caminho como:

`assets/video/steel-ball-run.mp4`

Não use caminho do Windows como `C:\xampp\htdocs\...` e não use barras invertidas `\\` no campo. O projeto converte barras invertidas automaticamente, mas o recomendado é usar `/`.

## Observação de conteúdo

As capas e artes incluídas no ZIP são **placeholders originais**. Substitua-as por materiais para os quais você tenha autorização. O projeto não inclui episódios completos de anime.

## Novos recursos

A versão atual inclui pesquisa com resultados e sugestões enquanto você digita, botão para mostrar/ocultar senha nas telas de autenticação, perfil de usuário e comentários em cada episódio.

### Banco de dados existente

Se você já instalou o banco `anime_stream` e não quer apagar os dados, execute no MySQL Workbench apenas:

```sql
USE anime_stream;
SOURCE database/migration_comentarios.sql;
```

Ou abra `database/migration_comentarios.sql` no Workbench, selecione todo o conteúdo e execute.

Se estiver montando o banco do zero, use `database/schema.sql`, que já contém a tabela `comentarios`.

### Pesquisa

A pesquisa principal funciona na Home e no campo do cabeçalho. Enquanto o usuário digita, o JavaScript consulta `api/search.php` e mostra até 8 sugestões. A busca por palavras separadas também funciona, por exemplo `re zero`, `classroom elite`, `mushoku reincarnation` e `demon slayer`.

### Perfil

Usuários logados encontram o link `Perfil` no menu. A página permite alterar nome e e-mail e, separadamente, trocar a senha informando a senha atual.

### Comentários

Cada página de episódio possui uma área de comentários. Usuários autenticados podem publicar e excluir os próprios comentários; administradores também podem excluir comentários. O painel administrativo ganhou uma seção para moderar todos os comentários.


### Atualização: foto de perfil e ações do admin
Execute `database/migration_perfil_foto.sql` no MySQL Workbench para adicionar `foto_perfil` à tabela `usuarios`. O perfil aceita JPG, PNG e WebP até 2 MB.

## Continuar assistindo e tempo restante
Na Home, a seção "Continuar assistindo" usa o progresso salvo na tabela `progresso_episodios`. O cartão exibe uma barra de progresso e calcula automaticamente o tempo restante do episódio, no formato `37m restantes`, `1h 12m restantes` ou `42s restantes`.

O progresso é gravado automaticamente pelo player em `/api/progress.php` enquanto o usuário assiste ao episódio.


## Atualização do player
- Botão central de play funcional.
- Barra de espaço e tecla K reproduzem/pausam.
- Clique no vídeo reproduz/pausa.
- Carregando vídeo só aparece enquanto o navegador está carregando o arquivo.
- Progresso salvo e retomado pelo banco sem exibir porcentagem na interface.
- Continue assistindo mostra tempo restante e barra de progresso.

## Vídeos do YouTube

O projeto aceita episódios hospedados no YouTube usando o player oficial por iframe. No painel administrativo, escolha **YouTube** em "Tipo do vídeo" e cole a URL completa ou apenas o ID do vídeo. Antes de salvar, o PHP extrai e valida o ID; para registros do tipo `youtube`, somente o ID é gravado na coluna `episodios.video`.

Exemplo: `https://www.youtube.com/watch?v=abc123XYZ01` vira `abc123XYZ01` no banco. O player monta automaticamente `https://www.youtube.com/embed/abc123XYZ01` e usa a IFrame Player API para salvar e retomar o progresso. A documentação oficial do YouTube descreve o formato `/embed/VIDEO_ID` e a API para controlar reprodução e consultar o tempo atual do vídeo. 

Para um banco já existente, execute uma única vez `database/migration_youtube.sql`. Para instalações novas, o `database/schema.sql` já contém `youtube` no `video_tipo`.

Use apenas vídeos que você tenha autorização para distribuir e incorporar. Um vídeo também pode ter a incorporação desabilitada pelo proprietário; nesse caso o player informará que a reprodução incorporada não foi permitida.

## Banners do carrossel
Execute `database/migration_banners.sql` uma vez no banco `anime_stream`. Depois, no painel administrativo, abra **Banners** para cadastrar, editar, trocar a imagem, ativar/desativar, ordenar e excluir slides do carrossel principal. Imagens enviadas ficam em `assets/uploads/banners/`.
