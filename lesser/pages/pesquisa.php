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
                <p class="search-page-subtitle">Digite pelo menos 2 caracteres para pesquisar um anime.</p>
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
            <p>Não encontramos resultados para “<?= e($q) ?>”. Tente outro nome.</p>
        </div>
    <?php elseif ($q === ''): ?>
        <div class="search-page-empty">
            <div class="search-empty-icon">⌕</div>
            <h2>Pesquise pelo seu próximo anime</h2>
            <p>Use a barra de pesquisa no topo para encontrar títulos do catálogo.</p>
        </div>
    <?php else: ?>
        <div class="anime-grid search-results-grid">
            <?php foreach ($results as $anime): ?>
                <article class="anime-card search-result-card">
                    <a href="<?= e(url('pages/anime.php?slug=' . urlencode($anime['slug']))) ?>">
                        <div class="anime-poster">
                            <img src="<?= e(url($anime['poster'])) ?>" alt="Capa de <?= e($anime['titulo']) ?>">
                            <span class="poster-tag"><?= e($anime['tipo']) ?></span>
                            <?php if (!empty($anime['destaque'])): ?>
                                <span class="search-featured-badge">★ Em destaque</span>
                            <?php endif; ?>
                        </div>
                        <div class="anime-body">
                            <h3><?= e($anime['titulo']) ?></h3>
                            <p><?= e($anime['generos']) ?></p>
                            <div class="anime-meta">
                                <span><?= e((string)$anime['ano']) ?></span>
                                <span><?= (int)$anime['total_episodios'] ?> eps.</span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
