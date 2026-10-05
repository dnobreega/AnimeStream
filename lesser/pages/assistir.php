<?php
require __DIR__ . '/../includes/bootstrap.php';
$id=(int)($_GET['ep']??0); $episode=find_episode($id);
if(!$episode){ http_response_code(404); $pageTitle='Episódio não encontrado'; require __DIR__.'/../includes/header.php'; echo '<main class="section container"><div class="empty-state">Episódio não encontrado.</div></main>'; require __DIR__.'/../includes/footer.php'; exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $acao = (string)($_POST['acao'] ?? '');
    if ($acao === 'comentar') {
        require_login();
        $texto = trim((string)($_POST['texto'] ?? ''));
        if (!verify_csrf($_POST['csrf'] ?? null)) {
            flash('error','Formulário inválido. Atualize a página e tente novamente.');
        } elseif (mb_strlen($texto, 'UTF-8') < 2) {
            flash('error','Escreva pelo menos 2 caracteres.');
        } elseif (mb_strlen($texto, 'UTF-8') > 1000) {
            flash('error','O comentário pode ter no máximo 1000 caracteres.');
        } else {
            db()->prepare('INSERT INTO comentarios(episodio_id,usuario_id,texto) VALUES(?,?,?)')->execute([$id,(int)current_user()['id'],$texto]);
            flash('success','Comentário publicado.');
        }
        redirect('pages/assistir.php?ep='.$id.'#comments');
    }

    if ($acao === 'excluir_comentario') {
        require_login();
        $comment = find_comment((int)($_POST['comment_id'] ?? 0));
        if (!$comment) flash('error','Comentário não encontrado.');
        elseif (!can_delete_comment($comment, is_admin())) flash('error','Você não pode excluir este comentário.');
        elseif ((int)$comment['episodio_id'] !== $id) flash('error','Comentário inválido para este episódio.');
        elseif (!verify_csrf($_POST['csrf'] ?? null)) flash('error','Ação inválida.');
        else { db()->prepare('DELETE FROM comentarios WHERE id=?')->execute([(int)$comment['id']]); flash('success','Comentário excluído.'); }
        redirect('pages/assistir.php?ep='.$id.'#comments');
    }
}

$nav=episode_navigation($episode); $anime=find_anime((int)$episode['anime_id']); $episodes=episodes_by_season((int)$episode['temporada_id']); $comments=episode_comments($id);
$savedProgress = is_logged_in() ? get_episode_progress($id, (int)current_user()['id']) : null;
$pageTitle=$anime['titulo'].' — E'.str_pad((string)$episode['numero'],2,'0',STR_PAD_LEFT);
require __DIR__ . '/../includes/header.php';
?>
<main class="watch-page">
    <section class="section container watch-layout">
        <div class="watch-main reveal">
            <div class="player-wrap" id="playerWrap">
                <?php if(trim((string)$episode['video'])): ?>
                    <?php
                        $isYoutube = ($episode['video_tipo'] ?? 'mp4') === 'youtube';
                        $videoSrc = !$isYoutube ? url((string)$episode['video']) : '';
                        $youtubeId = $isYoutube ? youtube_video_id((string)$episode['video']) : null;
                        $ytOrigin = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
                    ?>
                    <?php if($isYoutube && $youtubeId): ?>
                        <iframe
                            id="youtubePlayer"
                            class="youtube-player-frame"
                            src="<?= e(youtube_embed_url($youtubeId) . '&origin=' . rawurlencode($ytOrigin)) ?>"
                            title="<?= e($anime['titulo'].' — '.$episode['titulo']) ?>"
                            allow="autoplay; encrypted-media; picture-in-picture; fullscreen"
                            allowfullscreen
                            data-youtube-id="<?= e($youtubeId) ?>"
                            data-episode-id="<?= (int)$episode['id'] ?>"
                            data-saved-time="<?= e((string)($savedProgress['tempo_assistido'] ?? 0)) ?>"
                            data-origin="<?= e($ytOrigin) ?>"></iframe>
                    <?php else: ?>
                        <video
                            id="videoPlayer"
                            controls
                            playsinline
                            preload="metadata"
                            poster="<?= e(url($episode['thumb'] ?: $anime['poster'])) ?>"
                            data-src="<?= e($videoSrc) ?>"
                            data-type="<?= e($episode['video_tipo']) ?>"
                            data-episode-id="<?= (int)$episode['id'] ?>"
                            data-saved-time="<?= e((string)($savedProgress['tempo_assistido'] ?? 0)) ?>"
                            <?php if(($episode['video_tipo'] ?? 'mp4') !== 'hls'): ?>src="<?= e($videoSrc) ?>"<?php endif; ?>
                        ></video>
                    <?php endif; ?>
                    <button class="player-center-play" id="playerCenterPlay" type="button" aria-label="Reproduzir vídeo"><span aria-hidden="true">▶</span></button>
                    <div class="player-loading" id="playerLoading" aria-live="polite" hidden>Carregando vídeo…</div>
                    <div class="player-topbar"><span>S<?= str_pad((string)$episode['temporada_numero'],2,'0',STR_PAD_LEFT) ?> • E<?= str_pad((string)$episode['numero'],2,'0',STR_PAD_LEFT) ?></span><?php if($savedProgress && (float)$savedProgress['tempo_assistido']>3): ?><span>Continuar em <?= e(format_watch_time((float)$savedProgress['tempo_assistido'])) ?></span><?php endif; ?></div>
                    <div class="player-error-message" id="playerErrorMessage" hidden>Não foi possível carregar este vídeo.</div>
                <?php else: ?>
                    <div class="no-video"><div>▶</div><h3>Vídeo ainda não cadastrado</h3><p>Adicione um ID ou URL do vídeo deste episódio pelo painel administrativo.</p></div>
                <?php endif; ?>
            </div>
            <div class="watch-header"><div><span class="section-kicker"><?= e($anime['titulo']) ?> • S<?= (int)$episode['temporada_numero'] ?></span><h1>E<?= str_pad((string)$episode['numero'],2,'0',STR_PAD_LEFT) ?> — <?= e($episode['titulo']) ?></h1></div><a class="btn btn-ghost" href="<?= e(url('pages/anime.php?slug='.urlencode($anime['slug']))) ?>">← Voltar ao anime</a></div>
            <p class="watch-description"><?= e($episode['descricao'] ?: 'Sem descrição cadastrada.') ?></p>
            <div class="watch-nav"><a class="btn btn-ghost <?= $nav['previous']?'':'disabled' ?>" href="<?= $nav['previous'] ? e(url('pages/assistir.php?ep='.(int)$nav['previous']['id'])) : '#' ?>">← Anterior</a><?php if($nav['next']): ?><a class="btn btn-primary" href="<?= e(url('pages/assistir.php?ep='.(int)$nav['next']['id'])) ?>">Próximo →</a><?php endif; ?></div>

            <section class="comments-section" id="comments">
                <div class="section-head comments-head">
                    <div><span class="section-kicker">COMUNIDADE</span><h2>Comentários <small id="commentsCount"><?= count($comments) ?></small></h2></div>
                </div>

                <?php if(is_logged_in()): ?>
                    <form class="comment-form reveal" id="commentForm" method="post" data-comment-form="1">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="acao" value="comentar">
                        <input type="hidden" name="episode_id" value="<?= (int)$id ?>">
                        <?= avatar_html(current_user()['photo'] ?? null, current_user()['name'], 'comment-avatar') ?>
                        <div class="comment-input-area">
                            <textarea name="texto" maxlength="1000" rows="4" placeholder="O que você achou deste episódio?" required></textarea>
                            <div class="comment-actions"><span>Máximo de 1000 caracteres</span><button class="btn btn-primary" type="submit">Comentar</button></div>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="comment-login-card reveal"><div><strong>Quer comentar?</strong><p>Entre na sua conta para participar da conversa.</p></div><a class="btn btn-primary" href="<?= e(url('pages/login.php')) ?>">Entrar</a></div>
                <?php endif; ?>

                <div class="comments-list" id="commentsList">
                    <?php foreach($comments as $comment): ?>
                        <article class="comment-card reveal">
                            <?= avatar_html($comment['usuario_foto'] ?? null, $comment['usuario_nome'], 'comment-avatar') ?>
                            <div class="comment-content">
                                <div class="comment-meta"><strong><?= e($comment['usuario_nome']) ?></strong><?php if($comment['usuario_tipo']==='admin'): ?><span class="comment-badge">ADMIN</span><?php endif; ?><time><?= e(date('d/m/Y H:i', strtotime((string)$comment['criado_em']))) ?></time></div>
                                <p><?= nl2br(e($comment['texto'])) ?></p>
                                <?php if(can_delete_comment($comment)): ?>
                                    <form method="post">
                                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="acao" value="excluir_comentario">
                                        <input type="hidden" name="comment_id" value="<?= (int)$comment['id'] ?>">
                                        <button class="btn-action delete comment-delete" data-delete-label="este comentário" data-ajax-delete="comment" type="submit">Excluir comentário</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                    <?php if(!$comments): ?><div class="empty-state" id="commentsEmpty">Ainda não há comentários. Seja o primeiro a comentar.</div><?php endif; ?>
                </div>
            </section>
        </div>
        <aside class="watch-side"><div class="side-title"><span>Episódios</span><small><?= count($episodes) ?></small></div><div class="side-list"><?php foreach($episodes as $ep): ?><a class="side-episode <?= (int)$ep['id']===$id?'active':'' ?>" href="<?= e(url('pages/assistir.php?ep='.(int)$ep['id'])) ?>"><span>E<?= str_pad((string)$ep['numero'],2,'0',STR_PAD_LEFT) ?></span><div><strong><?= e($ep['titulo']) ?></strong><small>S<?= (int)$ep['temporada_numero'] ?></small></div></a><?php endforeach; ?></div></aside>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
