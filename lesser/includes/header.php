<?php
$pageTitle = $pageTitle ?? APP_NAME;
$user = current_user();
$headerSearch = trim((string)($_GET['q'] ?? ''));
?>
<!DOCTYPE html>
<html lang="pt-BR" data-base-url="<?= e(rtrim(BASE_URL, '/')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="description" content="YSA — Your Secrect Archive, catálogo de anime em PHP + MySQL.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>?v=20261004-hero650">
</head>
<body>
<canvas id="particles" aria-hidden="true"></canvas>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand brand-logo-link" href="<?= e(url()) ?>" title="YSA — Your Secrect Archive">
            <img class="brand-logo" src="<?= e(url('assets/img/logo-ysa.png')) ?>" alt="YSA — Your Secrect Archive">
        </a>

        <nav id="mainNav" class="main-nav" aria-label="Navegação principal">
            <a href="<?= e(url()) ?>">Início</a>
            <a href="<?= e(url('index.php#animes')) ?>">Animes</a>
            <?php if ($user): ?>
                <a href="<?= e(url('pages/perfil.php')) ?>">Perfil</a>
            <?php else: ?>
                <a href="<?= e(url('pages/login.php')) ?>">Perfil</a>
            <?php endif; ?>
            <?php if ($user && $user['type']==='admin'): ?>
                <a class="admin-entry" href="<?= e(url('admin/index.php')) ?>"><span class="admin-entry-icon" aria-hidden="true">⚙</span><span>Admin</span></a>
            <?php endif; ?>
        </nav>

        <div class="header-search-wrap">
            <form class="global-search" method="get" action="<?= e(url('pages/pesquisa.php')) ?>" autocomplete="off">
                <span class="search-icon" aria-hidden="true">⌕</span>
                <input id="globalSearch" type="search" name="q" value="<?= e($headerSearch) ?>" placeholder="Pesquisar anime..." aria-label="Pesquisar anime" aria-autocomplete="list" aria-controls="searchSuggestions">
                <div id="searchSuggestions" class="search-suggestions" hidden></div>
            </form>
        </div>

        <div class="header-account">
            <?php if ($user): ?>
                <a class="header-avatar-link" href="<?= e(url('pages/perfil.php')) ?>" title="Abrir perfil" aria-label="Abrir perfil">
                    <?= avatar_html($user['photo'] ?? null, $user['name'], 'header-avatar') ?>
                </a>
                <a class="btn btn-small btn-outline" href="<?= e(url('logout.php')) ?>">Sair</a>
            <?php else: ?>
                <a class="btn btn-small btn-primary" href="<?= e(url('pages/login.php')) ?>">Entrar</a>
            <?php endif; ?>
        </div>

        <button class="menu-toggle" id="menuToggle" type="button" aria-label="Abrir menu" aria-controls="mainNav" aria-expanded="false">☰</button>
    </div>
</header>
<div class="toast-wrap">
    <?php foreach(get_flashes() as $f): ?>
        <div class="toast <?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
</div>
