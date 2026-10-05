<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

$id=(int)($_GET['id']??$_POST['id']??0);
$ep=$id?find_episode($id):null;
if($id && !$ep){ http_response_code(404); $pageTitle='Episódio não encontrado'; require __DIR__.'/../includes/admin_header.php'; echo '<main class="section container"><div class="empty-state">O episódio solicitado não foi encontrado. <a href="'.e(url('admin/episodios.php')).'">Voltar para os episódios</a></div></main>'; require __DIR__.'/../includes/admin_footer.php'; exit; }
$errors=[];
$seasonList=all_seasons();

if($_SERVER['REQUEST_METHOD']==='POST'){
    $seasonId=(int)($_POST['temporada_id']??0);
    $numero=(int)($_POST['numero']??0);
    $titulo=trim((string)($_POST['titulo']??''));
    $descricao=trim((string)($_POST['descricao']??''));
    $thumb=trim((string)($_POST['thumb']??''));
    $video=trim((string)($_POST['video']??''));
    $videoTipo=in_array($_POST['video_tipo']??'mp4',['mp4','hls','youtube'],true)?$_POST['video_tipo']:'mp4';
    $destaque=!empty($_POST['destaque'])?1:0;

    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Formulário inválido.';
    if(!$seasonId) $errors[]='Selecione uma temporada.';
    if($numero<1) $errors[]='Número inválido.';
    if($titulo==='') $errors[]='Informe o título.';
    if($thumb!=='' && !valid_media_url($thumb)) $errors[]='Thumbnail inválida.';
    if($videoTipo==='youtube') {
        $youtubeId = youtube_video_id($video);
        if($video!=='' && !$youtubeId) $errors[]='Informe um ID do YouTube válido ou uma URL do YouTube.';
        if($youtubeId) $video = $youtubeId;
    } elseif($video!=='' && !valid_media_url($video)) {
        $errors[]='URL do vídeo inválida.';
    }

    $check=db()->prepare('SELECT id FROM episodios WHERE temporada_id=? AND numero=? AND id<>? LIMIT 1');
    $check->execute([$seasonId,$numero,$id]);
    if($check->fetch()) $errors[]='Esse número de episódio já existe nesta temporada.';

    if(!$errors){
        if($id&&$ep){
            db()->prepare('UPDATE episodios SET temporada_id=?,numero=?,titulo=?,descricao=?,thumb=?,video=?,video_tipo=?,destaque=? WHERE id=?')->execute([$seasonId,$numero,$titulo,$descricao,$thumb,$video,$videoTipo,$destaque,$id]);
            flash('success','Episódio atualizado.');
        }else{
            db()->prepare('INSERT INTO episodios(temporada_id,numero,titulo,descricao,thumb,video,video_tipo,destaque) VALUES(?,?,?,?,?,?,?,?)')->execute([$seasonId,$numero,$titulo,$descricao,$thumb,$video,$videoTipo,$destaque]);
            flash('success','Episódio criado.');
        }
        redirect('admin/episodios.php');
    }
}elseif($ep){
    $_POST=$ep;
}

$pageTitle=$id?'Editar episódio':'Novo episódio';
require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page">
    <div class="episode-editor-layout">
        <div class="form-card reveal">
            <span class="section-kicker">EPISÓDIO</span>
            <h1><?= $id?'Editar episódio':'Novo episódio' ?></h1>

            <?php foreach($errors as $er): ?>
                <div class="alert error"><?= e($er) ?></div>
            <?php endforeach; ?>

            <form method="post" class="form-grid" id="episodeForm">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $id ?>">

                <label class="full">
                    Temporada
                    <select name="temporada_id" id="episodeSeason" required>
                        <option value="">Selecione</option>
                        <?php foreach($seasonList as $s): ?>
                            <option
                                value="<?= (int)$s['id'] ?>"
                                data-anime="<?= e($s['anime_titulo']) ?>"
                                data-season="<?= (int)$s['numero'] ?>"
                                data-season-title="<?= e($s['titulo']) ?>"
                                <?= ((int)($_POST['temporada_id']??0)===(int)$s['id'])?'selected':'' ?>>
                                <?= e($s['anime_titulo'].' — S'.str_pad((string)$s['numero'],2,'0',STR_PAD_LEFT).' — '.$s['titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>
                    Número
                    <input type="number" name="numero" id="episodeNumber" min="1" required value="<?= e((string)($_POST['numero']??1)) ?>">
                </label>

                <label>
                    Título
                    <input name="titulo" id="episodeTitle" required value="<?= e($_POST['titulo']??'') ?>">
                </label>

                <label class="full">
                    Descrição
                    <textarea name="descricao" id="episodeDescription" rows="4"><?= e($_POST['descricao']??'') ?></textarea>
                </label>

                <label>
                    Thumbnail
                    <input name="thumb" id="episodeThumb" value="<?= e($_POST['thumb']??'') ?>" placeholder="assets/img/anime/generic.svg">
                </label>

                <label>
                    Tipo do vídeo
                    <select name="video_tipo" id="episodeVideoType">
                        <option value="youtube" <?= (($_POST['video_tipo']??'')==='youtube')?'selected':'' ?>>YouTube</option>
                        <option value="mp4" <?= (($_POST['video_tipo']??'mp4')==='mp4')?'selected':'' ?>>MP4</option>
                        <option value="hls" <?= (($_POST['video_tipo']??'')==='hls')?'selected':'' ?>>HLS (.m3u8)</option>
                    </select>
                </label>

                <label class="full">
                    <span id="episodeVideoLabel">ID / URL do YouTube</span>
                    <input name="video" id="episodeVideo" value="<?= e($_POST['video']??'') ?>" placeholder="abc123XYZ01 ou https://www.youtube.com/watch?v=abc123XYZ01">
                    <small class="field-hint" id="episodeVideoHint">Cole o link do YouTube ou informe o ID. Para YouTube, somente o ID será salvo no banco.</small>
                </label>

                <div class="full">
                    <input class="featured-control-input" type="checkbox" name="destaque" id="episodeFeatured" value="1" <?= !empty($_POST['destaque'])?'checked':'' ?>>
                    <label class="featured-toggle" for="episodeFeatured">
                        <span class="featured-toggle-icon">★</span>
                        <span class="featured-toggle-copy">
                            <strong>Colocar em destaque</strong>
                            <small>O episódio aparecerá nas áreas de destaque do catálogo.</small>
                        </span>
                        <span class="featured-toggle-switch" aria-hidden="true"><i></i></span>
                    </label>
                </div>

                <div class="helper full" id="episodeVideoHelper">
                    Para YouTube, você pode colar o link completo ou somente o ID. Para MP4 local, use <code>assets/video/nome-do-arquivo.mp4</code>.
                </div>

                <div class="form-actions full">
                    <a class="btn btn-ghost" href="<?= e(url('admin/episodios.php')) ?>">Cancelar</a>
                    <button class="btn btn-primary">Salvar episódio</button>
                </div>
            </form>
        </div>

        <aside class="episode-preview reveal">
            <div class="preview-head">
                <div>
                    <span class="section-kicker">PRÉ-VISUALIZAÇÃO</span>
                    <h2>Como vai aparecer</h2>
                </div>
                <span class="preview-live">AO VIVO</span>
            </div>

            <div class="preview-card">
                <div class="episode-thumb preview-thumb">
                    <img id="previewThumb" src="<?= e(url($_POST['thumb'] ?: 'assets/img/anime/generic.svg')) ?>" alt="Prévia da thumbnail">
                    <span class="episode-num" id="previewNumber">E<?= str_pad((string)($_POST['numero']??1),2,'0',STR_PAD_LEFT) ?></span>
                    <span class="featured preview-featured" id="previewFeatured" <?= !empty($_POST['destaque'])?'':'hidden' ?>>Destaque</span>
                </div>
                <div class="episode-body">
                    <small id="previewAnime">Selecione um anime/temporada</small>
                    <h3 id="previewTitle"><?= e($_POST['titulo'] ?: 'Título do episódio') ?></h3>
                    <p id="previewDescription"><?= e($_POST['descricao'] ?: 'A descrição do episódio aparecerá aqui.') ?></p>
                </div>
            </div>

            <div class="preview-player">
                <div class="preview-player-title">
                    <span>Prévia do vídeo</span>
                    <small id="previewVideoStatus">Nenhum vídeo informado</small>
                </div>
                <video id="previewVideo" controls playsinline preload="metadata" poster="<?= e(url($_POST['thumb'] ?: 'assets/img/anime/generic.svg')) ?>"></video>
                <iframe id="previewYoutube" title="Prévia do YouTube" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen hidden></iframe>
                <div class="preview-video-empty" id="previewVideoEmpty">Informe um ID ou URL de vídeo para testar a prévia.</div>
            </div>
        </aside>
    </div>
</main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
