<?php
require __DIR__ . '/../includes/bootstrap.php';
$slug = trim((string)($_GET['slug'] ?? ''));
$anime = find_anime_by_slug($slug);

if (!$anime) {
    http_response_code(404);
    $pageTitle = 'Anime não encontrado';
    require __DIR__ . '/../includes/header.php';
    echo '<main class="section container"><div class="empty-state">Anime não encontrado.</div></main>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$seasons = all_seasons((int)$anime['id']);
$episodes = episodes_by_anime((int)$anime['id']);
$progressMap = is_logged_in()
    ? episode_progress_map((int)current_user()['id'], array_map('intval', array_column($episodes, 'id')))
    : [];

$requestedSeasonId = isset($_GET['temporada']) ? (int)$_GET['temporada'] : 0;
$currentSeason = null;

if ($requestedSeasonId > 0) {
    foreach ($seasons as $candidateSeason) {
        if ((int)$candidateSeason['id'] === $requestedSeasonId) {
            $currentSeason = $candidateSeason;
            break;
        }
    }
}

if (!$currentSeason && $seasons) {
    $currentSeason = $seasons[0];
}

$pageTitle = $anime['titulo'];
require __DIR__ . '/../includes/header.php';
?>

<main>
    <section class="detail-hero">
        <div class="container detail-grid reveal">
            <div class="detail-poster">
                <img src="<?= e(url($anime['poster'])) ?>" alt="<?= e($anime['titulo']) ?>">
            </div>

            <div class="detail-copy">
                <span class="section-kicker"><?= e($anime['tipo']) ?> • <?= e((string)$anime['ano']) ?></span>
                <h1><?= e($anime['titulo']) ?></h1>
                <p class="detail-synopsis"><?= e($anime['sinopse']) ?></p>
                <div class="chips">
                    <span><?= e($anime['generos']) ?></span>
                    <span>Estúdio: <?= e($anime['estudio']) ?></span>
                    <span><?= (int)$anime['total_temporadas'] ?> temporada<?= (int)$anime['total_temporadas'] === 1 ? '' : 's' ?></span>
                </div>
            </div>
        </div>
    </section>

    <section
        class="section season-section container"
        id="animeSeasons"
        data-anime-slug="<?= e($slug) ?>"
        data-base-url="<?= e(url('pages/anime.php?slug=' . rawurlencode($slug))) ?>"
    >
        <?php if ($currentSeason): ?>
            <div class="season-toolbar">
                <div class="season-toolbar-title">
                    <div class="season-picker" id="seasonPicker">
                        <button
                            type="button"
                            class="season-picker-trigger"
                            id="seasonPickerTrigger"
                            aria-expanded="false"
                            aria-controls="seasonPickerMenu"
                        >
                            <span class="season-picker-chevron" aria-hidden="true">⌄</span>
                            <span class="season-picker-current" id="seasonPickerCurrent"><?= e($currentSeason['titulo']) ?></span>
                            <span class="season-picker-count" id="seasonPickerCount">
                                <?php
                                $selectedEpisodeCount = 0;
                                foreach ($episodes as $episode) {
                                    if ((int)$episode['temporada_id'] === (int)$currentSeason['id']) {
                                        $selectedEpisodeCount++;
                                    }
                                }
                                echo $selectedEpisodeCount;
                                ?> Episódios
                            </span>
                        </button>

                        <div class="season-picker-menu" id="seasonPickerMenu" hidden>
                            <?php foreach ($seasons as $seasonOption): ?>
                                <?php
                                $optionEpisodeCount = 0;
                                foreach ($episodes as $episode) {
                                    if ((int)$episode['temporada_id'] === (int)$seasonOption['id']) {
                                        $optionEpisodeCount++;
                                    }
                                }
                                $isActive = (int)$seasonOption['id'] === (int)$currentSeason['id'];
                                ?>
                                <button
                                    type="button"
                                    class="season-option<?= $isActive ? ' active' : '' ?>"
                                    data-season-id="<?= (int)$seasonOption['id'] ?>"
                                    data-season-title="<?= e($seasonOption['titulo']) ?>"
                                    data-season-count="<?= $optionEpisodeCount ?>"
                                    aria-pressed="<?= $isActive ? 'true' : 'false' ?>"
                                >
                                    <span><?= e($seasonOption['titulo']) ?></span>
                                    <small><?= $optionEpisodeCount ?> Episódios</small>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php foreach ($seasons as $seasonPanel): ?>
                <?php
                $seasonId = (int)$seasonPanel['id'];
                $seasonEpisodes = array_values(array_filter(
                    $episodes,
                    fn($ep) => (int)$ep['temporada_id'] === $seasonId
                ));
                $isVisible = $seasonId === (int)$currentSeason['id'];
                ?>

                <div
                    class="season-episodes-panel<?= $isVisible ? ' is-active' : '' ?>"
                    data-season-panel="<?= $seasonId ?>"
                    <?= $isVisible ? '' : 'hidden' ?>
                >
                    <div class="season-active-meta">
                        <div>
                            <span class="season-number">S<?= str_pad((string)$seasonPanel['numero'], 2, '0', STR_PAD_LEFT) ?></span>
                            <?php if (!empty($seasonPanel['descricao'])): ?>
                                <p><?= e($seasonPanel['descricao']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="episode-row" aria-label="Episódios da <?= e($seasonPanel['titulo']) ?>">
                        <?php foreach ($seasonEpisodes as $ep): $progress = $progressMap[(int)$ep['id']] ?? null; ?>
                            <a
                                class="episode-card episode-card-stream reveal"
                                href="<?= e(url('pages/assistir.php?ep=' . (int)$ep['id'])) ?>"
                            >
                                <div class="episode-thumb">
                                    <img src="<?= e(url($ep['thumb'] ?: 'assets/img/anime/generic.svg')) ?>" alt="">
                                    <span class="episode-num">E<?= str_pad((string)$ep['numero'], 2, '0', STR_PAD_LEFT) ?></span>

                                    <?php if (!empty($ep['destaque'])): ?>
                                        <span class="featured">★ Em destaque</span>
                                    <?php endif; ?>

                                    <?php if ($progress && (float)$progress['tempo_assistido'] > 3): ?>
                                        <div class="episode-progress">
                                            <i style="width:<?= e((string)$progress['percentual_assistido']) ?>%"></i>
                                        </div>
                                        <span class="episode-watch-time overlay-time">
                                            <?= (float)$progress['duracao'] > 0
                                                ? e(format_remaining_watch_time(max(0, (float)$progress['duracao'] - (float)$progress['tempo_assistido'])))
                                                : e(format_watch_time((float)$progress['tempo_assistido']) . ' assistido') ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="episode-body">
                                    <small><?= e($anime['titulo']) ?></small>
                                    <h3><?= e($ep['titulo']) ?></h3>
                                    <p><?= e($ep['descricao'] ?: 'Sem descrição cadastrada.') ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <?php if (!$seasonEpisodes): ?>
                        <div class="empty-state season-empty">Nenhum episódio cadastrado nesta temporada.</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="season-picker-static">
                <span>Nenhuma temporada cadastrada</span>
            </div>
            <div class="empty-state season-empty">Nenhuma temporada cadastrada.</div>
        <?php endif; ?>
    </section>
</main>

<?php require __DIR__ . '/../includes/footer.php'; ?>
