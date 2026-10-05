<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

if (!banners_table_exists()) {
    flash('error', 'Instale a tabela de banners executando database/migration_banners.sql.');
    redirect('admin/banners.php');
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$banner = $id ? find_banner($id) : null;
$errors = [];

if ($id && !$banner) {
    flash('error', 'Banner não encontrado.');
    redirect('admin/banners.php');
}

$animes = all_animes();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animeId = (int)($_POST['anime_id'] ?? 0);
    $titulo = trim((string)($_POST['titulo'] ?? ''));
    $descricao = trim((string)($_POST['descricao'] ?? ''));
    $imagemUrl = trim((string)($_POST['imagem_url'] ?? ''));
    $ordem = (int)($_POST['ordem'] ?? 0);
    $ativo = !empty($_POST['ativo']) ? 1 : 0;
    $imagem = (string)($banner['imagem'] ?? '');

    if (!verify_csrf($_POST['csrf'] ?? null)) $errors[] = 'Formulário inválido.';
    if ($animeId <= 0 || !find_anime($animeId)) $errors[] = 'Selecione um anime válido.';
    if ($titulo === '') $errors[] = 'Informe o título do banner.';
    if ($ordem < 0) $errors[] = 'A ordem não pode ser negativa.';

    $hasUpload = isset($_FILES['imagem']) && ($_FILES['imagem']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    if (!$hasUpload && $imagemUrl !== '' && !valid_banner_source($imagemUrl)) {
        $errors[] = 'Informe uma URL/caminho válido para a imagem.';
    }
    if (!$id && !$hasUpload && $imagemUrl === '') {
        $errors[] = 'Envie uma imagem ou informe uma URL/caminho.';
    }

    if (!$errors && $hasUpload) {
        try {
            $imagem = store_banner_image($_FILES['imagem'], $banner['imagem'] ?? null);
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    } elseif (!$errors && $imagemUrl !== '') {
        if (($banner['imagem'] ?? '') !== $imagemUrl) {
            remove_banner_image($banner['imagem'] ?? null);
        }
        $imagem = $imagemUrl;
    }

    if (!$errors) {
        if ($id) {
            db()->prepare('UPDATE banners SET anime_id=?,titulo=?,descricao=?,imagem=?,ordem=?,ativo=? WHERE id=?')
                ->execute([$animeId,$titulo,$descricao ?: null,$imagem,$ordem,$ativo,$id]);
            flash('success', 'Banner atualizado com sucesso.');
        } else {
            db()->prepare('INSERT INTO banners(anime_id,titulo,descricao,imagem,ordem,ativo) VALUES(?,?,?,?,?,?)')
                ->execute([$animeId,$titulo,$descricao ?: null,$imagem,$ordem,$ativo]);
            flash('success', 'Banner criado com sucesso.');
        }
        redirect('admin/banners.php');
    }

    $banner = array_merge((array)$banner, [
        'anime_id' => $animeId,
        'titulo' => $titulo,
        'descricao' => $descricao,
        'imagem' => $imagem,
        'ordem' => $ordem,
        'ativo' => $ativo,
    ]);
} elseif (!$banner) {
    $banner = [
        'anime_id' => (int)($animes[0]['id'] ?? 0),
        'titulo' => '',
        'descricao' => '',
        'imagem' => '',
        'ordem' => 0,
        'ativo' => 1,
    ];
}

$pageTitle = $id ? 'Editar banner' : 'Novo banner';
require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page banner-form-page">
    <div class="admin-top">
        <div>
            <span class="section-kicker">HOME • CARROSSEL</span>
            <h1><?= $id ? 'Editar banner' : 'Novo banner' ?></h1>
            <p>Personalize a imagem e as informações que aparecem no carrossel principal.</p>
        </div>
        <a class="btn btn-ghost" href="<?= e(url('admin/banners.php')) ?>">← Voltar</a>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="banner-form-layout">
        <section class="form-card banner-form-card">
            <form method="post" enctype="multipart/form-data" id="bannerForm" class="form-grid">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="id" value="<?= $id ?>">

                <label>Anime
                    <select name="anime_id" id="bannerAnime" required>
                        <?php foreach ($animes as $anime): ?>
                            <option value="<?= (int)$anime['id'] ?>" <?= (int)$banner['anime_id'] === (int)$anime['id'] ? 'selected' : '' ?>>
                                <?= e($anime['titulo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <label>Ordem
                    <input type="number" name="ordem" min="0" value="<?= e((string)$banner['ordem']) ?>">
                </label>

                <label class="full">Título do banner
                    <input type="text" name="titulo" id="bannerTitle" maxlength="180" value="<?= e($banner['titulo']) ?>" required>
                </label>

                <label class="full">Descrição
                    <textarea name="descricao" id="bannerDescription" rows="5" maxlength="1000" placeholder="Texto exibido no carrossel..."><?= e($banner['descricao']) ?></textarea>
                </label>

                <div class="full banner-image-field">
                    <label>Imagem do banner</label>
                    <div class="banner-upload-box">
                        <div class="banner-upload-actions">
                            <label class="upload-btn">Escolher imagem
                                <input type="file" name="imagem" id="bannerImageFile" accept="image/jpeg,image/png,image/webp">
                            </label>
                            <span>JPG, PNG ou WebP · até 8 MB</span>
                        </div>
                        <input type="text" name="imagem_url" id="bannerImageUrl" value="<?= e($banner['imagem']) ?>" placeholder="ou assets/uploads/banners/banner.webp / URL https://...">
                    </div>
                </div>

                <div class="full">
                    <input class="featured-control-input" type="checkbox" name="ativo" id="bannerActive" value="1" <?= !empty($banner['ativo']) ? 'checked' : '' ?>>
                    <label class="featured-toggle banner-active-toggle" for="bannerActive">
                        <span class="featured-toggle-icon">●</span>
                        <span class="featured-toggle-copy"><strong>Mostrar no carrossel</strong><small>Desative para manter o banner cadastrado sem exibi-lo na Home.</small></span>
                        <span class="featured-toggle-switch" aria-hidden="true"><i></i></span>
                    </label>
                </div>

                <div class="form-actions full">
                    <a class="btn btn-ghost" href="<?= e(url('admin/banners.php')) ?>">Cancelar</a>
                    <button class="btn btn-primary" type="submit">Salvar banner</button>
                </div>
            </form>
        </section>

        <aside class="banner-live-preview">
            <div class="banner-preview-head">
                <div>
                    <span class="section-kicker">PRÉ-VISUALIZAÇÃO</span>
                    <h2>Como ficará na Home</h2>
                </div>
            </div>
            <article class="ysa-hero-slider banner-preview-slider">
                <div class="ysa-hero-slide is-active">
                    <div class="ysa-hero-backdrop" id="bannerPreviewBackdrop"></div>
                    <div class="ysa-hero-glow"></div>
                    <div class="container ysa-hero-content">
                        <div class="ysa-hero-copy">
                            <span class="ysa-hero-kicker">EM DESTAQUE</span>
                            <h1 id="bannerPreviewTitle">Título do banner</h1>
                            <div class="ysa-hero-meta">
                                <span class="ysa-hero-badge">YSA</span>
                                <span id="bannerPreviewAnime">Anime</span>
                            </div>
                            <p id="bannerPreviewDescription">Descrição do banner.</p>
                            <div class="ysa-hero-actions">
                                <span class="ysa-hero-primary">▶ COMEÇAR A ASSISTIR</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
            <p class="banner-preview-help">A imagem escolhida aqui será usada como fundo do slide, sem alterar o poster do anime.</p>
        </aside>
    </div>
</main>

<script>
(() => {
    const file = document.getElementById('bannerImageFile');
    const url = document.getElementById('bannerImageUrl');
    const title = document.getElementById('bannerTitle');
    const description = document.getElementById('bannerDescription');
    const anime = document.getElementById('bannerAnime');
    const backdrop = document.getElementById('bannerPreviewBackdrop');
    const previewTitle = document.getElementById('bannerPreviewTitle');
    const previewDescription = document.getElementById('bannerPreviewDescription');
    const previewAnime = document.getElementById('bannerPreviewAnime');

    function updateText() {
        previewTitle.textContent = title.value.trim() || 'Título do banner';
        previewDescription.textContent = description.value.trim() || 'Descrição do banner.';
        previewAnime.textContent = anime.options[anime.selectedIndex]?.text || 'Anime';
    }

    function updateImage() {
        if (file.files && file.files[0]) {
            const objectUrl = URL.createObjectURL(file.files[0]);
            backdrop.style.backgroundImage = `url("${objectUrl}")`;
            backdrop.style.backgroundSize = 'cover';
            backdrop.style.backgroundPosition = 'center';
            return;
        }
        const source = url.value.trim();
        if (source) {
            const normalized = source.startsWith('http') ? source : `${document.documentElement.dataset.baseUrl || ''}/${source.replace(/^\//, '')}`;
            backdrop.style.backgroundImage = `url("${normalized}")`;
        } else {
            backdrop.style.backgroundImage = 'none';
        }
    }

    [title, description, anime].forEach(el => el?.addEventListener('input', updateText));
    anime?.addEventListener('change', updateText);
    file?.addEventListener('change', updateImage);
    url?.addEventListener('input', updateImage);

    updateText();
    updateImage();
})();
</script>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
