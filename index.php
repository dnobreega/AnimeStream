<?php
require __DIR__ . '/includes/bootstrap.php';
$q=trim((string)($_GET['q']??''));
$all=all_animes();
$animes=$q!=='' ? search_animes($q, 30) : $all;
$featured=featured_animes();
$banners=active_banners();
$heroItems=$banners ?: $featured;
$latest=db()->query("SELECT e.*,t.numero temporada_numero,a.titulo anime_titulo,a.slug anime_slug,a.poster FROM episodios e INNER JOIN temporadas t ON t.id=e.temporada_id INNER JOIN animes a ON a.id=t.anime_id ORDER BY e.criado_em DESC,e.id DESC LIMIT 12")->fetchAll();
$continueWatching = current_user_progress(8);
$latestProgressMap = is_logged_in() ? episode_progress_map((int)current_user()['id'], array_map('intval', array_column($latest, 'id'))) : [];
$pageTitle='Início';
require __DIR__ . '/includes/header.php';
?>
<main>
    <?php if($heroItems): ?>
    <section class="ysa-hero-slider" id="ysaHeroSlider" aria-label="Destaques">
        <div class="ysa-hero-track">
            <?php foreach($heroItems as $index => $item): ?>
                <?php
                    $heroTitle = trim((string)($item['banner_titulo'] ?? $item['titulo'] ?? ''));
                    $heroDescription = trim((string)($item['banner_descricao'] ?? $item['sinopse'] ?? $item['anime_sinopse'] ?? ''));
                    $heroImage = trim((string)($item['banner_imagem'] ?? $item['imagem'] ?? $item['poster'] ?? $item['anime_poster'] ?? ''));
                    $heroSlug = (string)($item['anime_slug'] ?? $item['slug'] ?? '');
                    $heroYear = $item['ano'] ?? null;
                    $heroType = $item['tipo'] ?? null;
                    $heroGenres = $item['generos'] ?? null;
                    $heroAnimeId = isset($item['anime_id']) ? (int)$item['anime_id'] : (int)($item['id'] ?? 0);
                    $heroWatchEpisodeId = anime_start_episode_id($heroAnimeId, is_logged_in() ? (int)current_user()['id'] : null);
                    $heroWatchUrl = $heroWatchEpisodeId > 0
                        ? url('pages/assistir.php?ep='.$heroWatchEpisodeId)
                        : url('pages/anime.php?slug='.urlencode($heroSlug));
                ?>
                <article class="ysa-hero-slide <?= $index === 0 ? 'is-active' : '' ?>" data-slide="<?= $index ?>">
                    <div class="ysa-hero-backdrop" style="background-image:url('<?= e(url($heroImage)) ?>')"></div>
                    <div class="ysa-hero-glow"></div>
                    <div class="container ysa-hero-content">
                        <div class="ysa-hero-copy">
                            <span class="ysa-hero-kicker">EM DESTAQUE</span>
                            <h1><?= e($heroTitle) ?></h1>
                            <div class="ysa-hero-meta">
                                <span class="ysa-hero-badge">YSA</span>
                                <?php if(!empty($heroYear)): ?><span><?= e((string)$heroYear) ?></span><?php endif; ?>
                                <?php if(!empty($heroType)): ?><span><?= e($heroType) ?></span><?php endif; ?>
                                <?php if(!empty($heroGenres)): ?><span><?= e($heroGenres) ?></span><?php endif; ?>
                            </div>
                            <?php if($heroDescription !== ''): ?>
                                <p><?= e($heroDescription) ?></p>
                            <?php endif; ?>
                            <div class="ysa-hero-actions">
                                <a class="ysa-hero-primary" href="<?= e($heroWatchUrl) ?>">▶ COMEÇAR A ASSISTIR</a>
                                <a class="ysa-hero-secondary" href="<?= e(url('pages/anime.php?slug='.urlencode($heroSlug))) ?>" aria-label="Ver informações de <?= e($heroTitle) ?>">i</a>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if(count($heroItems) > 1): ?>
            <button class="ysa-hero-arrow ysa-hero-prev" type="button" aria-label="Destaque anterior">‹</button>
            <button class="ysa-hero-arrow ysa-hero-next" type="button" aria-label="Próximo destaque">›</button>
            <div class="ysa-hero-dots" aria-label="Selecionar destaque">
                <?php foreach($heroItems as $index => $item): ?>
                    <button type="button" class="ysa-hero-dot <?= $index === 0 ? 'is-active' : '' ?>" data-go-to="<?= $index ?>" aria-label="Ir para <?= e((string)($item['banner_titulo'] ?? $item['titulo'] ?? 'Destaque')) ?>"></button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

  <section class="section container anime-row" id="destaques">

    <div class="section-heading">

        <div>
            <span class="eyebrow">EM ALTA</span>
            <h2>Animes em alta no Brasil</h2>
        </div>

        <a href="<?= e(url('pages/categoria.php?categoria=em-alta')) ?>" class="see-all">
            Ver mais
        </a>

    </div>


    <?php if($featured): ?>

        <div class="carousel-shell">

            <!-- SETA ESQUERDA -->
            <button
                class="carousel-arrow prev"
                type="button"
                aria-label="Animes anteriores"
            >
                ‹
            </button>


            <!-- ÁREA VISÍVEL -->
            <div class="carousel-viewport">

                <!-- TRILHA DOS CARDS -->
                <div class="carousel-track">

                    <?php foreach($featured as $anime): ?>

                        <article class="anime-card">

                            <a
                                class="card-link"
                                href="<?= e(url(
                                    'pages/anime.php?slug=' .
                                    urlencode($anime['slug'])
                                )) ?>"
                            >

                                <!-- POSTER -->
                                <div class="poster">

                                    <img
                                        class="poster-image"
                                        src="<?= e(url($anime['poster'])) ?>"
                                        alt="Poster de <?= e($anime['titulo']) ?>"
                                        loading="lazy"
                                    >

                                    <!-- HOVER -->
                                    <div
                                        class="hover-overlay"
                                        aria-hidden="true"
                                    >
                                        <span class="play"></span>
                                    </div>

                                </div>


                                <!-- TÍTULO -->
                                <h3 class="card-title">
                                    <?= e($anime['titulo']) ?>
                                </h3>


                                <!-- METADADOS -->
                                <div class="card-meta">
                                    <?= e($anime['tipo'] ?: 'Anime') ?>
                                </div>

                            </a>

                        </article>

                    <?php endforeach; ?>

                </div>

            </div>


            <!-- SETA DIREITA -->
            <button
                class="carousel-arrow next"
                type="button"
                aria-label="Próximos animes"
            >
                ›
            </button>

        </div>

    <?php else: ?>

        <div class="empty-state">
            Nenhum anime em destaque.
        </div>

    <?php endif; ?>

</section>

    <?php if(is_logged_in() && $continueWatching): ?>
    <section class="section container continue-section">
        <div class="section-head">
            <div><span class="section-kicker">PARA VOCÊ</span><h2>Continuar assistindo</h2></div>
        </div>
        <div class="continue-grid">
            <?php foreach($continueWatching as $item): ?>
                <?php
                    $watched = (float)$item['tempo_assistido'];
                    $duration = (float)$item['duracao'];
                    $remaining = max(0, $duration - $watched);
                ?>
                <a class="continue-card reveal" href="<?= e(url('pages/assistir.php?ep='.(int)$item['episodio_id'])) ?>">
                    <div class="continue-thumb">
                        <img src="<?= e(url($item['thumb'] ?: $item['poster'])) ?>" alt="<?= e($item['anime_titulo']) ?>">
                        <span class="continue-episode-tag">E<?= str_pad((string)$item['episodio_numero'],2,'0',STR_PAD_LEFT) ?></span>
                        <span class="continue-play">▶</span>
                        <?php if($duration > 0 && $remaining > 0): ?>
                            <span class="continue-remaining"><?= e(format_remaining_watch_time($remaining)) ?></span>
                        <?php endif; ?>
                        <div class="watch-progress" aria-label="Progresso de reprodução"><i style="width:<?= e((string)$item['percentual_assistido']) ?>%"></i></div>
                    </div>
                    <div class="continue-body">
                        <small><?= e($item['anime_titulo']) ?> • S<?= (int)$item['temporada_numero'] ?></small>
                        <strong>E<?= str_pad((string)$item['episodio_numero'],2,'0',STR_PAD_LEFT) ?> — <?= e($item['episodio_titulo']) ?></strong>
                        <span><?= $duration > 0 ? e(format_remaining_watch_time($remaining)) : e(format_watch_time($watched).' assistido') ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>


    <section class="section container anime-row" id="animes">
        <div class="section-heading">
            <div>
                <span class="eyebrow"><?= $q!=='' ? 'PESQUISA' : 'CATÁLOGO' ?></span>
                <h2><?= $q!=='' ? 'Resultados para “'.e($q).'”' : 'Todos os animes' ?></h2>
            </div>
            <div class="catalog-actions">
                <span class="catalog-count"><?= count($animes) ?> título<?= count($animes)===1?'':'s' ?></span>
                <?php if($q!==''): ?>
                    <a class="btn btn-small btn-ghost" href="<?= e(url('index.php')) ?>">Limpar</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if($q!==''): ?>
            <div class="search-result-info">
                A busca considera título, slug, gêneros e estúdio. Digite algumas palavras para encontrar resultados relacionados.
            </div>
        <?php endif; ?>

        <?php if($animes): ?>
            <div class="carousel-shell" role="region" aria-roledescription="carousel" aria-label="<?= e($q!=='' ? 'Resultados da pesquisa' : 'Todos os animes') ?>">
                <button class="carousel-arrow prev" type="button" aria-label="Animes anteriores">‹</button>

                <div class="carousel-viewport">
                    <div class="carousel-track">
                        <?php foreach($animes as $anime): ?>
                            <article class="anime-card">
                                <a class="card-link" href="<?= e(url('pages/anime.php?slug='.urlencode($anime['slug']))) ?>">
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
                                    <h3 class="card-title"><?= e($anime['titulo']) ?></h3>
                                    <div class="card-meta"><?= e($anime['tipo'] ?: 'Anime') ?></div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button class="carousel-arrow next" type="button" aria-label="Próximos animes">›</button>
            </div>
        <?php else: ?>
            <div class="empty-state">Nenhum anime encontrado.</div>
        <?php endif; ?>
    </section>

    <section class="section container">
        <div class="section-head"><div><span class="section-kicker">ATUALIZADO</span><h2>Últimos episódios cadastrados</h2></div></div>
        <div class="compact-grid">
            <?php foreach($latest as $ep): $progress=$latestProgressMap[(int)$ep['id']] ?? null; ?>
                <a class="compact-card reveal" href="<?= e(url('pages/assistir.php?ep='.(int)$ep['id'])) ?>">
                    <div class="compact-thumb-wrap">
                        <img src="<?= e(url($ep['thumb'] ?: $ep['poster'])) ?>" alt="">
                        <?php if($progress && (float)$progress['tempo_assistido']>3): ?><div class="episode-progress"><i style="width:<?= e((string)$progress['percentual_assistido']) ?>%"></i></div><?php endif; ?>
                    </div>
                    <div>
                        <small><?= e($ep['anime_titulo']) ?> • S<?= (int)$ep['temporada_numero'] ?></small>
                        <strong>E<?= str_pad((string)$ep['numero'],2,'0',STR_PAD_LEFT) ?> — <?= e($ep['titulo']) ?></strong>
                        <?php if($progress && (float)$progress['tempo_assistido']>3): ?><span class="compact-watch-time"><?= e(format_watch_time((float)$progress['tempo_assistido'])) ?><?= (float)$progress['duracao']>0 ? ' / '.e(format_watch_time((float)$progress['duracao'])) : '' ?></span><?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if(!$latest): ?><div class="empty-state">Cadastre episódios pelo painel administrativo.</div><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
