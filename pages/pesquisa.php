<?php
require __DIR__ . '/../includes/bootstrap.php';

$q = trim((string)($_GET['q'] ?? ''));
$results = $q !== '' ? search_animes($q, 40) : [];

$pageTitle = $q !== '' ? 'Resultados da pesquisa' : 'Pesquisar animes';
require __DIR__ . '/../includes/header.php';
?>

<main class="search-page section container">
    <div class="search-page-head">
        <div>
            <span class="section-kicker">PESQUISA</span>
            <h1>Resultados da busca</h1>
            <?php if ($q !== ''): ?>
                <p class="search-page-subtitle">Resultados para <strong>“<?= e($q) ?>”</strong></p>
            <?php else: ?>
                <p class="search-page-subtitle">Pesquise por nome, apelido, gênero, estúdio ou ano.</p>
            <?php endif; ?>
        </div>
        <?php if ($q !== ''): ?>
            <span class="search-page-count"><?= count($results) ?> resultado<?= count($results) === 1 ? '' : 's' ?></span>
        <?php endif; ?>
    </div>

    <?php if (mb_strlen(normalize_search_text($q), 'UTF-8') < 2 && $q !== ''): ?>
        <div class="search-page-empty">
            <div class="search-empty-icon">⌕</div>
            <h2>Pesquisa muito curta</h2>
            <p>Digite pelo menos 2 caracteres para encontrar resultados.</p>
        </div>
    <?php elseif ($q !== '' && !$results): ?>
        <div class="search-page-empty">
            <div class="search-empty-icon">⌕</div>
            <h2>Nenhum anime encontrado</h2>
            <p>Não encontramos resultados para “<?= e($q) ?>”. Tente um apelido, gênero, estúdio, ano ou outra parte do nome.</p>
        </div>
    <?php elseif ($q === ''): ?>
        <div class="search-page-empty">
            <div class="search-empty-icon">⌕</div>
            <h2>Pesquise pelo seu próximo anime</h2>
            <p>Use a barra de pesquisa no topo. Você pode buscar por partes do nome, nomes alternativos, gêneros, estúdios ou ano.</p>
        </div>
    <?php else: ?>
        <div class="carousel-shell" role="region" aria-roledescription="carousel" aria-label="Resultados da pesquisa">
            <button class="carousel-arrow prev" type="button" aria-label="Animes anteriores">‹</button>
            <div class="carousel-viewport">
                <div class="carousel-track">
                    <?php foreach ($results as $anime): ?>
                        <article class="anime-card">
                            <a class="card-link" href="<?= e(url('pages/anime.php?slug=' . urlencode($anime['slug']))) ?>">
                                <div class="poster">
                                    <img class="poster-image" src="<?= e(url($anime['poster'])) ?>" alt="Poster de <?= e($anime['titulo']) ?>" loading="lazy">
                                    <div class="hover-overlay" aria-hidden="true"><span class="play"></span></div>
                                </div>
                                <h3 class="card-title"><?= e($anime['titulo']) ?></h3>
                                <div class="card-meta"><?= e($anime['tipo'] ?: 'Anime') ?></div>
                            </a>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <button class="carousel-arrow next" type="button" aria-label="Próximos animes">›</button>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
