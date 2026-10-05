<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

$eps = all_episodes_admin();

// Opções para os filtros da página.
$animes = [];
$temporadas = [];
$comVideo = 0;
$emDestaque = 0;

foreach ($eps as $item) {
    $animeId = (int)($item['anime_id'] ?? 0);
    $seasonId = (int)($item['temporada_id'] ?? 0);

    $animes[$animeId] = $item['anime_titulo'];
    $temporadas[$seasonId] = [
        'anime' => $item['anime_titulo'],
        'numero' => (int)$item['temporada_numero'],
        'titulo' => $item['temporada_titulo']
    ];

    if (!empty($item['video'])) $comVideo++;
    if (!empty($item['destaque'])) $emDestaque++;
}

ksort($animes);
ksort($temporadas);

$pageTitle = 'Gerenciar episódios';
require __DIR__.'/../includes/admin_header.php';
?>

<main class="section container admin-page episodes-admin-page">
    <div class="episodes-page-head">
        <div>
            <span class="section-kicker">CONTEÚDO • EPISÓDIOS</span>
            <h1>Gerenciar episódios</h1>
            <p>Organize, filtre e edite os episódios do catálogo em um só lugar.</p>
        </div>
        <a class="btn btn-primary episodes-new-btn" href="<?= e(url('admin/episodio-form.php')) ?>">
            <span aria-hidden="true">＋</span> Novo episódio
        </a>
    </div>

    <section class="episode-stat-grid" aria-label="Resumo dos episódios">
        <article class="episode-stat-card">
            <div class="episode-stat-icon">▶</div>
            <div>
                <span>Total de episódios</span>
                <strong><?= count($eps) ?></strong>
            </div>
        </article>

        <article class="episode-stat-card">
            <div class="episode-stat-icon">◉</div>
            <div>
                <span>Com vídeo</span>
                <strong><?= $comVideo ?></strong>
            </div>
        </article>

        <article class="episode-stat-card">
            <div class="episode-stat-icon">★</div>
            <div>
                <span>Em destaque</span>
                <strong><?= $emDestaque ?></strong>
            </div>
        </article>

        <article class="episode-stat-card">
            <div class="episode-stat-icon">▤</div>
            <div>
                <span>Temporadas</span>
                <strong><?= count($temporadas) ?></strong>
            </div>
        </article>
    </section>

    <section class="episode-manager-card">
        <div class="episode-manager-heading">
            <div>
                <span class="section-kicker">LISTA DE EPISÓDIOS</span>
                <h2>Encontre rapidamente o que deseja editar</h2>
            </div>
            <span class="episode-results-counter" id="episodeResultsCounter">
                <?= count($eps) ?> de <?= count($eps) ?> episódios
            </span>
        </div>

        <div class="episode-filters">
            <label class="episode-filter-search">
                <span aria-hidden="true">⌕</span>
                <input
                    type="search"
                    id="episodeFilterSearch"
                    placeholder="Filtrar episódios por título, anime ou número..."
                    autocomplete="off"
                >
            </label>

            <label class="episode-filter-select">
                <span>Anime</span>
                <select id="episodeFilterAnime">
                    <option value="">Todos os animes</option>
                    <?php foreach ($animes as $animeId => $animeTitle): ?>
                        <option value="<?= (int)$animeId ?>"><?= e($animeTitle) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <label class="episode-filter-select">
                <span>Temporada</span>
                <select id="episodeFilterSeason">
                    <option value="">Todas as temporadas</option>
                    <?php foreach ($temporadas as $seasonId => $season): ?>
                        <option value="<?= (int)$seasonId ?>">
                            <?= e($season['anime'].' • S'.str_pad((string)$season['numero'], 2, '0', STR_PAD_LEFT).' • '.$season['titulo']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>

            <button type="button" class="episode-filter-clear" id="episodeFilterClear">Limpar filtros</button>
        </div>
    </section>

    <?php if (!$eps): ?>
        <div class="empty-state">Nenhum episódio cadastrado.</div>
    <?php else: ?>
        <section class="episode-table-card">
            <div class="episode-table-head">
                <div>
                    <span class="section-kicker">CATÁLOGO</span>
                    <h2>Todos os episódios</h2>
                </div>
                <span class="episode-table-hint">Clique em editar para alterar um episódio.</span>
            </div>

            <div class="table-wrap episode-table-wrap">
                <table class="episodes-admin-table">
                    <thead>
                        <tr>
                            <th>Episódio</th>
                            <th>Anime</th>
                            <th>Temporada</th>
                            <th>Vídeo</th>
                            <th>Destaque</th>
                            <th class="episode-actions-th">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="episodesAdminBody">
                        <?php foreach ($eps as $item): ?>
                            <?php
                                $episodeId = (int)$item['id'];
                                $animeId = (int)($item['anime_id'] ?? 0);
                                $seasonId = (int)($item['temporada_id'] ?? 0);
                                $number = (int)$item['numero'];
                                $episodeCode = 'E'.str_pad((string)$number, 2, '0', STR_PAD_LEFT);
                                $editUrl = url('admin/episodio-form.php?id='.$episodeId);
                                $searchData = mb_strtolower(
                                    $item['anime_titulo'].' '.$item['temporada_titulo'].' '.$item['titulo'].' '.$episodeCode.' S'.$item['temporada_numero'],
                                    'UTF-8'
                                );
                            ?>
                            <tr
                                class="episode-admin-row"
                                data-anime-id="<?= $animeId ?>"
                                data-season-id="<?= $seasonId ?>"
                                data-search="<?= e($searchData) ?>"
                            >
                                <td>
                                    <div class="episode-row-main">
                                        <div class="episode-row-thumb">
                                            <?php if (!empty($item['thumb'])): ?>
                                                <img src="<?= e(url($item['thumb'])) ?>" alt="">
                                            <?php else: ?>
                                                <span><?= e($episodeCode) ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="episode-row-copy">
                                            <strong><?= e($episodeCode) ?></strong>
                                            <span><?= e($item['titulo']) ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="episode-anime-name"><?= e($item['anime_titulo']) ?></span>
                                </td>
                                <td>
                                    <span class="episode-season-badge">
                                        S<?= str_pad((string)$item['temporada_numero'], 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                    <small><?= e($item['temporada_titulo']) ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($item['video'])): ?>
                                        <span class="episode-video-status has-video"><span>●</span> <?= $item['video_tipo']==='youtube' ? 'YouTube' : strtoupper((string)$item['video_tipo']) ?></span>
                                    <?php else: ?>
                                        <span class="episode-video-status no-video"><span>●</span> Sem vídeo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="featured-status <?= !empty($item['destaque']) ? 'on' : 'off' ?>">
                                        <?= !empty($item['destaque']) ? 'Em destaque' : 'Normal' ?>
                                    </span>
                                </td>
                                <td class="actions episode-actions">
                                    <div class="action-buttons">
                                        <a class="btn-action edit" href="<?= e($editUrl) ?>">Editar</a>
                                        <a class="btn-action delete" data-delete-label="o episódio <?= e($episodeCode) ?>" href="<?= e(url('admin/episodio-delete.php?id='.$episodeId)) ?>">Excluir</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="episodeNoResults" hidden>
                            <td colspan="6">
                                <div class="episode-no-results">
                                    <strong>Nenhum episódio encontrado</strong>
                                    <span>Tente outro título, anime ou temporada.</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>
</main>

<script>
(() => {
    const search = document.getElementById('episodeFilterSearch');
    const anime = document.getElementById('episodeFilterAnime');
    const season = document.getElementById('episodeFilterSeason');
    const clear = document.getElementById('episodeFilterClear');
    const counter = document.getElementById('episodeResultsCounter');
    const empty = document.getElementById('episodeNoResults');
    const rows = [...document.querySelectorAll('.episode-admin-row')];

    if (!search || !anime || !season || !counter || !rows.length) return;

    function filterEpisodes() {
        const query = search.value.trim().toLowerCase();
        const animeId = anime.value;
        const seasonId = season.value;
        let visible = 0;

        rows.forEach(row => {
            const matchesQuery = !query || (row.dataset.search || '').includes(query);
            const matchesAnime = !animeId || row.dataset.animeId === animeId;
            const matchesSeason = !seasonId || row.dataset.seasonId === seasonId;
            const show = matchesQuery && matchesAnime && matchesSeason;

            row.hidden = !show;
            if (show) visible++;
        });

        counter.textContent = `${visible} de ${rows.length} episódio${rows.length === 1 ? '' : 's'}`;
        if (empty) empty.hidden = visible !== 0;
    }

    search.addEventListener('input', filterEpisodes);
    anime.addEventListener('change', filterEpisodes);
    season.addEventListener('change', filterEpisodes);

    clear?.addEventListener('click', () => {
        search.value = '';
        anime.value = '';
        season.value = '';
        filterEpisodes();
        search.focus();
    });
})();
</script>

<?php require __DIR__.'/../includes/admin_footer.php'; ?>
