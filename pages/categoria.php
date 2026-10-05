<?php
require __DIR__ . '/../includes/bootstrap.php';

$categoria = trim((string)($_GET['categoria'] ?? 'em-alta'));

switch ($categoria) {
    case 'em-alta':
        $animes = featured_animes();
        $pageTitle = 'Animes em alta no Brasil';
        $kicker = 'EM ALTA';
        $description = 'Todos os animes que estão atualmente em destaque no YSA.';
        break;

    case 'catalogo':
    case 'todos':
        $animes = all_animes();
        $pageTitle = 'Todos os animes';
        $kicker = 'CATÁLOGO';
        $description = 'Confira todo o catálogo de animes disponível no YSA.';
        break;

    default:
        header('Location: ' . url());
        exit;
}

require __DIR__ . '/../includes/header.php';
?>

<main class="section container category-page">
    <div class="category-page-head">
        <div>
            <span class="section-kicker"><?= e($kicker) ?></span>
            <h1><?= e($pageTitle) ?></h1>
            <p><?= e($description) ?></p>
        </div>
        <span class="catalog-count">
            <?= count($animes) ?> título<?= count($animes) === 1 ? '' : 's' ?>
        </span>
    </div>

    <?php if ($animes): ?>
        <div class="category-grid">
            <?php foreach ($animes as $anime): ?>
                <article class="anime-card">
                    <a class="card-link" href="<?= e(url('pages/anime.php?slug=' . urlencode($anime['slug']))) ?>">
                        <div class="poster">
                            <img
                                class="poster-image"
                                src="<?= e(url($anime['poster'])) ?>"
                                alt="Poster de <?= e($anime['titulo']) ?>"
                                loading="lazy"
                            >
                            <div class="hover-overlay" aria-hidden="true">
                                <span class="play"></span>
                            </div>
                        </div>
                        <h2 class="card-title"><?= e($anime['titulo']) ?></h2>
                        <div class="card-meta">
                            <?= e($anime['tipo'] ?: 'Anime') ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">Nenhum anime encontrado nesta categoria.</div>
    <?php endif; ?>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
