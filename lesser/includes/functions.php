<?php
declare(strict_types=1);
require_once __DIR__ . '/database.php';

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }


/* =========================================================
   SENHA ADMIN: HASH + CÓPIA CRIPTOGRAFADA
   - O campo `senha` continua usando password_hash() para login.
   - `senha_criptografada` é usado somente para a visualização
     autorizada pelo administrador.
   - AES-256-GCM impede que o conteúdo seja alterado silenciosamente.
   ========================================================= */
function encrypt_password_for_admin(string $password): string
{
    $key = base64_decode(APP_ENCRYPTION_KEY, true);
    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException('APP_ENCRYPTION_KEY inválida.');
    }

    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt(
        $password,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    if ($ciphertext === false) {
        throw new RuntimeException('Não foi possível criptografar a senha.');
    }

    return base64_encode($iv . $tag . $ciphertext);
}

function decrypt_password_for_admin(?string $payload): ?string
{
    if (!$payload) return null;

    $raw = base64_decode($payload, true);
    if ($raw === false || strlen($raw) < 28) return null;

    $key = base64_decode(APP_ENCRYPTION_KEY, true);
    if ($key === false || strlen($key) !== 32) return null;

    $iv = substr($raw, 0, 12);
    $tag = substr($raw, 12, 16);
    $ciphertext = substr($raw, 28);

    $password = openssl_decrypt(
        $ciphertext,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $iv,
        $tag
    );

    return $password === false ? null : $password;
}

function url(string $path = ''): string {
    $base = rtrim(BASE_URL, '/');
    if ($path === '') return $base . '/';
    if (preg_match('~^https?://~i', $path)) return $path;
    $path = str_replace('\\', '/', $path);
    return $base . '/' . ltrim($path, '/');
}

function redirect(string $path): never {
    header('Location: ' . (preg_match('~^https?://~i', $path) ? $path : url($path)));
    exit;
}

function flash(string $type, string $message): void { $_SESSION['flash'][] = ['type'=>$type,'message'=>$message]; }
function get_flashes(): array { $items = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return is_array($items) ? $items : []; }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(?string $token): bool {
    return is_string($token) && hash_equals($_SESSION['csrf'] ?? '', $token);
}

function current_user(): ?array { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return current_user() !== null; }
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'name' => $user['nome'],
        'email' => $user['email'],
        'type' => $user['tipo'],
        'photo' => $user['foto_perfil'] ?? null,
    ];
}
function require_login(): void {
    if (!is_logged_in()) { flash('error', 'Faça login para acessar esta página.'); redirect('pages/login.php'); }
}
function is_admin(): bool {
    $u = current_user();
    if (!$u) return false;
    $stmt = db()->prepare('SELECT id, nome, email, tipo, foto_perfil FROM usuarios WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$u['id']]);
    $found = $stmt->fetch();
    if (!$found) return false;
    $_SESSION['user'] = ['id'=>(int)$found['id'],'name'=>$found['nome'],'email'=>$found['email'],'type'=>$found['tipo'],'photo'=>$found['foto_perfil'] ?? null];
    return $found['tipo'] === 'admin';
}
function require_admin(): void {
    if (!is_admin()) { flash('error', 'Acesso restrito ao administrador.'); redirect('pages/login.php'); }
}

function find_user_by_email(string $email): ?array {
    $stmt = db()->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->execute([strtolower(trim($email))]);
    $row = $stmt->fetch();
    return $row ?: null;
}
function all_users(): array { return db()->query('SELECT id,nome,email,tipo,foto_perfil,senha_criptografada,criado_em FROM usuarios ORDER BY criado_em DESC,id DESC')->fetchAll(); }
function admin_count(): int { return (int)db()->query("SELECT COUNT(*) FROM usuarios WHERE tipo='admin'")->fetchColumn(); }

function all_animes(): array {
    $sql = "SELECT a.*, COUNT(e.id) total_episodios, COUNT(DISTINCT t.id) total_temporadas
            FROM animes a
            LEFT JOIN temporadas t ON t.anime_id=a.id
            LEFT JOIN episodios e ON e.temporada_id=t.id
            GROUP BY a.id
            ORDER BY a.titulo ASC";
    return db()->query($sql)->fetchAll();
}

function banners_table_exists(): bool {
    static $exists = null;
    if ($exists !== null) return $exists;
    try {
        db()->query('SELECT 1 FROM banners LIMIT 1');
        $exists = true;
    } catch (Throwable $e) {
        $exists = false;
    }
    return $exists;
}

function all_banners(): array {
    if (!banners_table_exists()) return [];
    $sql = "SELECT b.*, a.titulo anime_titulo, a.slug anime_slug, a.poster anime_poster,
                   a.ano, a.tipo, a.generos, a.estudio, a.sinopse anime_sinopse
            FROM banners b
            INNER JOIN animes a ON a.id=b.anime_id
            ORDER BY b.ordem ASC, b.atualizado_em DESC, b.id DESC";
    return db()->query($sql)->fetchAll();
}

function active_banners(): array {
    if (!banners_table_exists()) return [];
    $sql = "SELECT b.*, a.titulo anime_titulo, a.slug anime_slug, a.poster anime_poster,
                   a.ano, a.tipo, a.generos, a.estudio, a.sinopse anime_sinopse
            FROM banners b
            INNER JOIN animes a ON a.id=b.anime_id
            WHERE b.ativo=1
            ORDER BY b.ordem ASC, b.atualizado_em DESC, b.id DESC
            LIMIT 12";
    return db()->query($sql)->fetchAll();
}

function find_banner(int $id): ?array {
    if (!banners_table_exists()) return null;
    $stmt = db()->prepare("SELECT b.*, a.titulo anime_titulo, a.slug anime_slug
                           FROM banners b INNER JOIN animes a ON a.id=b.anime_id
                           WHERE b.id=? LIMIT 1");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function banner_count(): int {
    if (!banners_table_exists()) return 0;
    return (int)db()->query('SELECT COUNT(*) FROM banners')->fetchColumn();
}

function store_banner_image(array $file, ?string $oldImage = null): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldImage ?? '';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível enviar a imagem do banner.');
    }

    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > BANNER_MAX_SIZE) {
        throw new RuntimeException('A imagem do banner deve ter no máximo 8 MB.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Arquivo de banner inválido.');
    }

    $info = @getimagesize($tmp);
    if (!$info) {
        throw new RuntimeException('Envie uma imagem JPG, PNG ou WebP válida.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato não permitido. Use JPG, PNG ou WebP.');
    }

    if (!is_dir(BANNER_UPLOAD_DIR) && !mkdir(BANNER_UPLOAD_DIR, 0755, true) && !is_dir(BANNER_UPLOAD_DIR)) {
        throw new RuntimeException('Não foi possível criar a pasta de banners.');
    }

    $filename = bin2hex(random_bytes(18)) . '.' . $allowed[$mime];
    $destination = rtrim(BANNER_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Não foi possível salvar a imagem do banner.');
    }

    if ($oldImage && str_starts_with($oldImage, BANNER_UPLOAD_WEB . '/')) {
        $oldName = basename($oldImage);
        $oldPath = rtrim(BANNER_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldName;
        if (is_file($oldPath)) @unlink($oldPath);
    }

    return BANNER_UPLOAD_WEB . '/' . $filename;
}

function remove_banner_image(?string $image): void {
    $image = trim((string)$image);
    if ($image === '' || !str_starts_with($image, BANNER_UPLOAD_WEB . '/')) return;
    $path = rtrim(BANNER_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($image);
    if (is_file($path)) @unlink($path);
}

function valid_banner_source(string $value): bool {
    $value = trim($value);
    if ($value === '') return false;
    if (preg_match('~^assets/[a-zA-Z0-9_./?=&%#:+-]+$~', $value)) return true;
    return (bool)filter_var($value, FILTER_VALIDATE_URL)
        && in_array(strtolower((string)parse_url($value, PHP_URL_SCHEME)), ['http', 'https'], true);
}

function featured_animes(): array {
    $stmt = db()->query("SELECT a.*, COUNT(e.id) total_episodios
                         FROM animes a
                         LEFT JOIN temporadas t ON t.anime_id=a.id
                         LEFT JOIN episodios e ON e.temporada_id=t.id
                         WHERE a.destaque=1
                         GROUP BY a.id
                         ORDER BY a.atualizado_em DESC, a.titulo ASC
                         LIMIT 8");
    return $stmt->fetchAll();
}
function find_anime(int $id): ?array {
    $stmt = db()->prepare('SELECT a.*, COUNT(e.id) total_episodios, COUNT(DISTINCT t.id) total_temporadas
                           FROM animes a LEFT JOIN temporadas t ON t.anime_id=a.id
                           LEFT JOIN episodios e ON e.temporada_id=t.id WHERE a.id=? GROUP BY a.id LIMIT 1');
    $stmt->execute([$id]); $r=$stmt->fetch(); return $r ?: null;
}
function find_anime_by_slug(string $slug): ?array {
    $stmt = db()->prepare('SELECT a.*, COUNT(e.id) total_episodios, COUNT(DISTINCT t.id) total_temporadas
                           FROM animes a LEFT JOIN temporadas t ON t.anime_id=a.id
                           LEFT JOIN episodios e ON e.temporada_id=t.id WHERE a.slug=? GROUP BY a.id LIMIT 1');
    $stmt->execute([$slug]); $r=$stmt->fetch(); return $r ?: null;
}
function slugify(string $value): string {
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function all_seasons(?int $animeId = null): array {
    if ($animeId !== null) {
        $stmt = db()->prepare("SELECT t.*, a.titulo anime_titulo, COUNT(e.id) total_episodios
                               FROM temporadas t INNER JOIN animes a ON a.id=t.anime_id
                               LEFT JOIN episodios e ON e.temporada_id=t.id
                               WHERE t.anime_id=? GROUP BY t.id ORDER BY t.numero ASC");
        $stmt->execute([$animeId]); return $stmt->fetchAll();
    }
    return db()->query("SELECT t.*, a.titulo anime_titulo, COUNT(e.id) total_episodios
                        FROM temporadas t INNER JOIN animes a ON a.id=t.anime_id
                        LEFT JOIN episodios e ON e.temporada_id=t.id
                        GROUP BY t.id ORDER BY a.titulo ASC,t.numero ASC")->fetchAll();
}
function find_season(int $id): ?array {
    $stmt=db()->prepare("SELECT t.*,a.titulo anime_titulo FROM temporadas t INNER JOIN animes a ON a.id=t.anime_id WHERE t.id=? LIMIT 1");
    $stmt->execute([$id]); $r=$stmt->fetch(); return $r ?: null;
}
function find_episode(int $id): ?array {
    $stmt = db()->prepare("SELECT e.*,t.numero temporada_numero,t.titulo temporada_titulo,t.anime_id,a.titulo anime_titulo,a.slug anime_slug
                           FROM episodios e INNER JOIN temporadas t ON t.id=e.temporada_id
                           INNER JOIN animes a ON a.id=t.anime_id WHERE e.id=? LIMIT 1");
    $stmt->execute([$id]); $r=$stmt->fetch(); return $r ?: null;
}
function episodes_by_anime(int $animeId): array {
    $stmt=db()->prepare("SELECT e.*,t.numero temporada_numero,t.titulo temporada_titulo
                         FROM episodios e INNER JOIN temporadas t ON t.id=e.temporada_id
                         WHERE t.anime_id=? ORDER BY t.numero ASC,e.numero ASC");
    $stmt->execute([$animeId]); return $stmt->fetchAll();
}
function episodes_by_season(int $seasonId): array {
    $stmt = db()->prepare("SELECT e.*,
                                  t.numero AS temporada_numero,
                                  t.titulo AS temporada_titulo,
                                  t.anime_id
                           FROM episodios e
                           INNER JOIN temporadas t ON t.id = e.temporada_id
                           WHERE e.temporada_id = ?
                           ORDER BY e.numero ASC, e.id ASC");
    $stmt->execute([$seasonId]);
    return $stmt->fetchAll();
}
function all_episodes_admin(): array {
    return db()->query("SELECT e.*,t.anime_id,t.numero temporada_numero,t.titulo temporada_titulo,a.titulo anime_titulo
                        FROM episodios e INNER JOIN temporadas t ON t.id=e.temporada_id
                        INNER JOIN animes a ON a.id=t.anime_id
                        ORDER BY a.titulo ASC,t.numero ASC,e.numero ASC")->fetchAll();
}
function next_episode_number(int $seasonId, ?int $ignoreId=null): int {
    $stmt=db()->prepare('SELECT COALESCE(MAX(numero),0)+1 FROM episodios WHERE temporada_id=? AND id<>COALESCE(?,0)');
    $stmt->execute([$seasonId,$ignoreId]); return (int)$stmt->fetchColumn();
}
function valid_media_url(string $value): bool {
    $value=trim($value); if ($value==='') return true;
    if (preg_match('~^assets/(video|img)/[a-zA-Z0-9_./?=&%#:+-]+$~',$value)) return true;
    return (bool)filter_var($value,FILTER_VALIDATE_URL) && in_array(strtolower((string)parse_url($value,PHP_URL_SCHEME)),['http','https'],true);
}
function normalize_video_type(string $value): string {
    $path=strtolower(parse_url($value,PHP_URL_PATH) ?? $value);
    return str_ends_with($path,'.m3u8') ? 'hls' : 'mp4';
}

// Extrai o ID de um vídeo do YouTube ou aceita diretamente um ID válido.
function youtube_video_id(string $value): ?string {
    $value = trim($value);
    if ($value === '') return null;

    // ID padrão do YouTube: 11 caracteres.
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
        return $value;
    }

    $parts = parse_url($value);
    if (!$parts || !isset($parts['host'])) return null;

    $host = strtolower($parts['host']);
    $path = trim((string)($parts['path'] ?? ''), '/');

    // youtube.com/watch?v=ID
    if (str_contains($host, 'youtube.com')) {
        if (($parts['query'] ?? '') !== '') {
            parse_str($parts['query'], $query);
            if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', (string)$query['v'])) {
                return (string)$query['v'];
            }
        }

        // youtube.com/embed/ID ou youtube.com/shorts/ID
        foreach (['embed', 'shorts', 'live'] as $prefix) {
            if (str_starts_with($path, $prefix . '/')) {
                $candidate = substr($path, strlen($prefix) + 1);
                $candidate = explode('/', $candidate)[0];
                if (preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)) return $candidate;
            }
        }
    }

    // youtu.be/ID
    if ($host === 'youtu.be' || str_ends_with($host, '.youtu.be')) {
        $candidate = explode('/', $path)[0] ?? '';
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate)) return $candidate;
    }

    return null;
}

function youtube_embed_url(string $videoId): string {
    return 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?enablejsapi=1&playsinline=1&rel=0';
}

function remember_user(int $userId): void {
    $selector=bin2hex(random_bytes(16)); $validator=bin2hex(random_bytes(32));
    $hash=hash('sha256',$validator); $exp=date('Y-m-d H:i:s',time()+REMEMBER_DAYS*86400);
    db()->prepare('INSERT INTO tokens_lembrar(selector,token_hash,usuario_id,expira_em) VALUES(?,?,?,?)')->execute([$selector,$hash,$userId,$exp]);
    setcookie(REMEMBER_COOKIE,$selector.':'.$validator,['expires'=>time()+REMEMBER_DAYS*86400,'path'=>BASE_URL?:'/','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax']);
}
function restore_remembered_user(): void {
    if (is_logged_in() || empty($_COOKIE[REMEMBER_COOKIE])) return;
    $parts=explode(':',(string)$_COOKIE[REMEMBER_COOKIE],2); if(count($parts)!==2) return;
    [$selector,$validator]=$parts;
    $stmt=db()->prepare("SELECT tl.*,u.nome,u.email,u.tipo FROM tokens_lembrar tl INNER JOIN usuarios u ON u.id=tl.usuario_id WHERE tl.selector=? AND tl.expira_em>=NOW() LIMIT 1");
    $stmt->execute([$selector]); $row=$stmt->fetch();
    if(!$row || !hash_equals($row['token_hash'],hash('sha256',$validator))) return;
    login_user($row);
}

function create_password_reset(int $userId): string {
    $token=bin2hex(random_bytes(32)); $hash=hash('sha256',$token); $exp=date('Y-m-d H:i:s',time()+RESET_MINUTES*60);
    db()->prepare('DELETE FROM tokens_recuperacao WHERE usuario_id=?')->execute([$userId]);
    db()->prepare('INSERT INTO tokens_recuperacao(token_hash,usuario_id,expira_em) VALUES(?,?,?)')->execute([$hash,$userId,$exp]);
    return $token;
}

function episode_navigation(array $episode): array {
    $stmt=db()->prepare('SELECT id,numero,titulo FROM episodios WHERE temporada_id=? ORDER BY numero ASC');
    $stmt->execute([(int)$episode['temporada_id']]); $list=$stmt->fetchAll();
    $ids=array_column($list,'id'); $idx=array_search((int)$episode['id'],array_map('intval',$ids),true);
    return [
        'previous' => ($idx!==false && $idx>0) ? find_episode((int)$ids[$idx-1]) : null,
        'next' => ($idx!==false && $idx<count($ids)-1) ? find_episode((int)$ids[$idx+1]) : null,
    ];
}


function normalize_search_text(string $value): string {
    $value = trim(mb_strtolower($value, 'UTF-8'));
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';
    return trim(preg_replace('/\s+/', ' ', $value) ?? '');
}

function search_animes(string $query, int $limit = 12): array {
    $query = normalize_search_text($query);

    // Evita que uma única letra retorne praticamente todo o catálogo.
    if ($query === '' || mb_strlen($query, 'UTF-8') < 2) return [];

    $parts = preg_split('/\s+/u', $query, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $stop = ['of','the','a','an','de','da','do','das','dos','e'];
    $parts = array_values(array_filter($parts, static function(string $part) use ($stop): bool {
        return mb_strlen($part, 'UTF-8') >= 2 && !in_array($part, $stop, true);
    }));

    // Se a consulta tiver apenas termos muito curtos/stopwords, não inventar resultados.
    if (!$parts) return [];

    $titleNorm = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.titulo,' ',''),':',''),'-',''),'!',''),'.',''),'?',''),'–',''))";
    $slugNorm  = "LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(a.slug,' ',''),':',''),'-',''),'!',''),'.',''),'?',''),'_',''))";
    $clauses = [];
    $params = [];

    foreach ($parts as $part) {
        $token = '%' . $part . '%';
        $compact = '%' . str_replace(' ', '', $part) . '%';
        $clauses[] = "(
            LOWER(a.titulo) LIKE ?
            OR LOWER(a.slug) LIKE ?
            OR LOWER(a.generos) LIKE ?
            OR LOWER(a.estudio) LIKE ?
            OR {$titleNorm} LIKE ?
            OR {$slugNorm} LIKE ?
        )";
        array_push($params, $token, $token, $token, $token, $compact, $compact);
    }

    $limit = max(1, min(30, $limit));

    // Ordena resultados de forma previsível: correspondência exata, começo do título e depois ocorrência interna.
    $exact = "CASE WHEN LOWER(a.titulo) = ? OR {$titleNorm} = ? OR LOWER(a.slug) = ? THEN 0 ELSE 1 END";
    $prefix = "CASE WHEN LOWER(a.titulo) LIKE ? OR {$titleNorm} LIKE ? THEN 0 ELSE 1 END";

    $sql = "SELECT a.*, COUNT(DISTINCT e.id) total_episodios, COUNT(DISTINCT t.id) total_temporadas
            FROM animes a
            LEFT JOIN temporadas t ON t.anime_id=a.id
            LEFT JOIN episodios e ON e.temporada_id=t.id
            WHERE " . implode(' AND ', $clauses) . "
            GROUP BY a.id
            ORDER BY {$exact}, {$prefix}, a.destaque DESC, a.titulo ASC
            LIMIT {$limit}";

    $stmt = db()->prepare($sql);
    $first = $parts[0];
    $stmtParams = [
        $query, str_replace(' ', '', $query), str_replace(' ', '-', $query),
        $query . '%', str_replace(' ', '', $query) . '%'
    ];
    $stmt->execute(array_merge($stmtParams, $params));
    return $stmt->fetchAll();
}

function get_episode_progress(int $episodeId, int $userId): ?array {
    $stmt = db()->prepare('SELECT id, episodio_id, usuario_id, tempo_assistido, duracao, concluido, atualizado_em FROM progresso_episodios WHERE episodio_id=? AND usuario_id=? LIMIT 1');
    $stmt->execute([$episodeId, $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function save_episode_progress(int $episodeId, int $userId, float $currentTime, float $duration): void {
    $currentTime = max(0, $currentTime);
    $duration = max(0, $duration);
    if ($duration > 0) $currentTime = min($currentTime, $duration);
    $completed = ($duration > 0 && ($duration - $currentTime) <= 8) ? 1 : 0;

    $stmt = db()->prepare(
        'INSERT INTO progresso_episodios (episodio_id, usuario_id, tempo_assistido, duracao, concluido) VALUES (?,?,?,?,?) ' .
        'ON DUPLICATE KEY UPDATE tempo_assistido=VALUES(tempo_assistido), duracao=VALUES(duracao), concluido=VALUES(concluido), atualizado_em=CURRENT_TIMESTAMP'
    );
    $stmt->execute([$episodeId, $userId, round($currentTime, 2), round($duration, 2), $completed]);
}

function anime_start_episode_id(int $animeId, ?int $userId = null): ?int {
    if ($animeId <= 0) return null;
    $userId = $userId ?? (is_logged_in() ? (int)current_user()['id'] : 0);

    // Primeiro episódio cadastrado do anime.
    $firstStmt = db()->prepare(
        'SELECT e.id
         FROM episodios e
         INNER JOIN temporadas t ON t.id=e.temporada_id
         WHERE t.anime_id=?
         ORDER BY t.numero ASC, e.numero ASC, e.id ASC
         LIMIT 1'
    );
    $firstStmt->execute([$animeId]);
    $firstEpisodeId = $firstStmt->fetchColumn();
    if (!$firstEpisodeId) return null;
    $firstEpisodeId = (int)$firstEpisodeId;

    // Visitante não logado: sempre começa pelo primeiro episódio.
    if ($userId <= 0) return $firstEpisodeId;

    // Se todos os episódios do anime estão concluídos, recomeça do primeiro.
    $completedStmt = db()->prepare(
        'SELECT COUNT(DISTINCT e.id) AS total_episodios,
                COUNT(DISTINCT CASE WHEN p.concluido=1 THEN e.id END) AS episodios_concluidos
         FROM episodios e
         INNER JOIN temporadas t ON t.id=e.temporada_id
         LEFT JOIN progresso_episodios p
           ON p.episodio_id=e.id AND p.usuario_id=?
         WHERE t.anime_id=?'
    );
    $completedStmt->execute([$userId, $animeId]);
    $status = $completedStmt->fetch() ?: ['total_episodios'=>0,'episodios_concluidos'=>0];
    $total = (int)$status['total_episodios'];
    $completed = (int)$status['episodios_concluidos'];

    if ($total > 0 && $completed >= $total) {
        return $firstEpisodeId;
    }

    // Ainda não terminou o anime: retoma o episódio mais recentemente assistido.
    $lastStmt = db()->prepare(
        'SELECT e.id
         FROM progresso_episodios p
         INNER JOIN episodios e ON e.id=p.episodio_id
         INNER JOIN temporadas t ON t.id=e.temporada_id
         WHERE p.usuario_id=?
           AND t.anime_id=?
           AND p.tempo_assistido > 3
         ORDER BY p.atualizado_em DESC, p.id DESC
         LIMIT 1'
    );
    $lastStmt->execute([$userId, $animeId]);
    $lastEpisodeId = $lastStmt->fetchColumn();

    return $lastEpisodeId ? (int)$lastEpisodeId : $firstEpisodeId;
}

function current_user_progress(int $limit = 8): array {
    if (!is_logged_in()) return [];

    $userId = (int)current_user()['id'];
    $limit = max(1, min(20, $limit));

    // Mostra somente o episódio mais recentemente assistido de cada anime.
    // Assim, se o usuário assistir a vários episódios de Steel Ball Run, por
    // exemplo, o bloco "Continuar assistindo" terá apenas o último episódio
    // daquela série.
    $sql = "SELECT p.id progresso_id, p.episodio_id, p.tempo_assistido, p.duracao, p.concluido, p.atualizado_em,
                   e.numero episodio_numero, e.titulo episodio_titulo, e.thumb,
                   t.numero temporada_numero, t.titulo temporada_titulo,
                   a.id anime_id, a.titulo anime_titulo, a.slug anime_slug, a.poster
            FROM progresso_episodios p
            INNER JOIN episodios e ON e.id=p.episodio_id
            INNER JOIN temporadas t ON t.id=e.temporada_id
            INNER JOIN animes a ON a.id=t.anime_id
            WHERE p.usuario_id=?
              AND p.concluido=0
              AND p.tempo_assistido > 3
              AND NOT EXISTS (
                  SELECT 1
                  FROM progresso_episodios p2
                  INNER JOIN episodios e2 ON e2.id=p2.episodio_id
                  INNER JOIN temporadas t2 ON t2.id=e2.temporada_id
                  WHERE p2.usuario_id=p.usuario_id
                    AND p2.concluido=0
                    AND p2.tempo_assistido > 3
                    AND t2.anime_id=a.id
                    AND (
                        p2.atualizado_em > p.atualizado_em
                        OR (p2.atualizado_em=p.atualizado_em AND p2.id>p.id)
                    )
              )
            ORDER BY p.atualizado_em DESC, p.id DESC
            LIMIT {$limit}";

    $stmt = db()->prepare($sql);
    $stmt->execute([$userId]);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['percentual_assistido'] = ((float)$row['duracao'] > 0)
            ? min(100, max(0, round(((float)$row['tempo_assistido'] / (float)$row['duracao']) * 100, 1)))
            : 0;
    }
    unset($row);

    return $rows;
}

function episode_progress_map(int $userId, array $episodeIds): array {
    $episodeIds = array_values(array_unique(array_map('intval', $episodeIds)));
    if ($userId <= 0 || !$episodeIds) return [];
    $placeholders = implode(',', array_fill(0, count($episodeIds), '?'));
    $stmt = db()->prepare("SELECT episodio_id, tempo_assistido, duracao, concluido FROM progresso_episodios WHERE usuario_id=? AND episodio_id IN ({$placeholders})");
    $stmt->execute(array_merge([$userId], $episodeIds));
    $map = [];
    foreach ($stmt->fetchAll() as $row) {
        $row['percentual_assistido'] = ((float)$row['duracao'] > 0)
            ? min(100, max(0, round(((float)$row['tempo_assistido'] / (float)$row['duracao']) * 100, 1)))
            : 0;
        $map[(int)$row['episodio_id']] = $row;
    }
    return $map;
}

function format_watch_time(float $seconds): string {
    $seconds = max(0, (int)round($seconds));
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $secs = $seconds % 60;
    return $hours > 0 ? sprintf('%d:%02d:%02d', $hours, $minutes, $secs) : sprintf('%02d:%02d', $minutes, $secs);
}

function format_remaining_watch_time(float $seconds): string {
    $seconds = max(0, (int)round($seconds));
    if ($seconds <= 0) return 'Finalizado';

    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);

    if ($hours > 0) {
        return $hours . 'h ' . $minutes . 'm restantes';
    }
    if ($minutes > 0) {
        return $minutes . 'm restantes';
    }
    return $seconds . 's restantes';
}

function episode_comments(int $episodeId): array {
    $stmt = db()->prepare("SELECT c.*, u.nome usuario_nome, u.tipo usuario_tipo, u.foto_perfil usuario_foto
                           FROM comentarios c
                           INNER JOIN usuarios u ON u.id=c.usuario_id
                           WHERE c.episodio_id=?
                           ORDER BY c.criado_em DESC, c.id DESC");
    $stmt->execute([$episodeId]);
    return $stmt->fetchAll();
}
function user_comment_count(int $userId): int {
    $stmt = db()->prepare('SELECT COUNT(*) FROM comentarios WHERE usuario_id=?');
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
function find_comment(int $id): ?array {
    $stmt = db()->prepare('SELECT * FROM comentarios WHERE id=? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function avatar_html(?string $photo, string $name, string $class = 'avatar'): string {
    $photo = trim((string)$photo);
    $initial = e(strtoupper(mb_substr($name, 0, 1, 'UTF-8')));
    if ($photo !== '') {
        return '<img class="' . e($class) . ' avatar-image" src="' . e(url($photo)) . '" alt="Foto de ' . e($name) . '">';
    }
    return '<div class="' . e($class) . ' avatar-fallback" aria-hidden="true">' . $initial . '</div>';
}

function store_profile_photo(array $file, ?string $oldPhoto = null): string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $oldPhoto ?? '';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível enviar a foto.');
    }
    $size = (int)($file['size'] ?? 0);
    if ($size <= 0 || $size > PROFILE_MAX_SIZE) {
        throw new RuntimeException('A foto deve ter no máximo 2 MB.');
    }

    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        throw new RuntimeException('Arquivo de foto inválido.');
    }

    $info = @getimagesize($tmp);
    if (!$info) {
        throw new RuntimeException('Envie uma imagem JPG, PNG ou WebP válida.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato não permitido. Use JPG, PNG ou WebP.');
    }

    if (!is_dir(PROFILE_UPLOAD_DIR) && !mkdir(PROFILE_UPLOAD_DIR, 0755, true) && !is_dir(PROFILE_UPLOAD_DIR)) {
        throw new RuntimeException('Não foi possível criar a pasta de fotos.');
    }

    $filename = bin2hex(random_bytes(18)) . '.' . $allowed[$mime];
    $destination = rtrim(PROFILE_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($tmp, $destination)) {
        throw new RuntimeException('Não foi possível salvar a foto.');
    }

    if ($oldPhoto && str_starts_with($oldPhoto, PROFILE_UPLOAD_WEB . '/')) {
        $oldName = basename($oldPhoto);
        $oldPath = rtrim(PROFILE_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldName;
        if (is_file($oldPath)) @unlink($oldPath);
    }

    return PROFILE_UPLOAD_WEB . '/' . $filename;
}

function remove_profile_photo(?string $photo): void {
    $photo = trim((string)$photo);
    if ($photo === '' || !str_starts_with($photo, PROFILE_UPLOAD_WEB . '/')) return;
    $path = rtrim(PROFILE_UPLOAD_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . basename($photo);
    if (is_file($path)) @unlink($path);
}

function can_delete_comment(array $comment, ?bool $admin = null): bool {
    $user = current_user();
    if ($user === null) return false;
    if ((int)$user['id'] === (int)$comment['usuario_id']) return true;
    return $admin ?? (($user['type'] ?? 'user') === 'admin');
}
