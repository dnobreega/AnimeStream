<?php
$pageTitle = $pageTitle ?? 'Painel administrativo';
$user = current_user();
$currentAdminPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');
?>
<!DOCTYPE html>
<html lang="pt-BR" data-base-url="<?= e(rtrim(BASE_URL, '/')) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="description" content="Painel administrativo — YSA — Your Secrect Archive.">
    <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>?v=20261003-banner">
</head>
<body class="admin-body">
<canvas id="particles" aria-hidden="true"></canvas>
<div class="admin-app" id="adminApp">
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-brand">
            <a href="<?= e(url()) ?>" class="admin-brand-logo" title="Voltar ao site">
                <img src="<?= e(url('assets/img/logo-ysa.png')) ?>" alt="YSA — Your Secrect Archive">
            </a>
            <div class="admin-brand-copy">
                <strong>Painel Admin</strong>
                <span>YSA • Controle</span>
            </div>
        </div>

        <nav class="admin-nav" aria-label="Navegação administrativa">
            <span class="admin-nav-label">GERENCIAMENTO</span>
            <a class="admin-nav-link <?= $currentAdminPage==='index.php'?'active':'' ?>" href="<?= e(url('admin/index.php')) ?>"><span class="admin-nav-icon">⌂</span>Dashboard</a>
            <a class="admin-nav-link <?= in_array($currentAdminPage,['banners.php','banner-form.php'],true)?'active':'' ?>" href="<?= e(url('admin/banners.php')) ?>"><span class="admin-nav-icon">▰</span>Banners</a>
            <a class="admin-nav-link <?= in_array($currentAdminPage,['animes.php','anime-form.php'],true)?'active':'' ?>" href="<?= e(url('admin/animes.php')) ?>"><span class="admin-nav-icon">▣</span>Animes</a>
            <a class="admin-nav-link <?= in_array($currentAdminPage,['temporadas.php','temporada-form.php'],true)?'active':'' ?>" href="<?= e(url('admin/temporadas.php')) ?>"><span class="admin-nav-icon">▤</span>Temporadas</a>
            <a class="admin-nav-link <?= in_array($currentAdminPage,['episodios.php','episodio-form.php'],true)?'active':'' ?>" href="<?= e(url('admin/episodios.php')) ?>"><span class="admin-nav-icon">▶</span>Episódios</a>
            <a class="admin-nav-link <?= $currentAdminPage==='usuarios.php'?'active':'' ?>" href="<?= e(url('admin/usuarios.php')) ?>"><span class="admin-nav-icon">♙</span>Usuários</a>
            <a class="admin-nav-link <?= $currentAdminPage==='comentarios.php'?'active':'' ?>" href="<?= e(url('admin/comentarios.php')) ?>"><span class="admin-nav-icon">✦</span>Comentários</a>
        </nav>

        <div class="admin-sidebar-bottom">
            <div class="admin-user-mini">
                <?= avatar_html($user['photo'] ?? null, $user['name'] ?? 'Admin', 'admin-mini-avatar') ?>
                <div><strong><?= e($user['name'] ?? 'Administrador') ?></strong><small>Administrador</small></div>
            </div>
            <a class="admin-logout" href="<?= e(url('logout.php')) ?>">Sair da conta</a>
        </div>
    </aside>

    <section class="admin-workspace">
        <header class="admin-topbar">
            <div class="admin-topbar-left">
                <button type="button" class="admin-sidebar-toggle" id="adminSidebarToggle" aria-label="Abrir menu administrativo" aria-controls="adminSidebar" aria-expanded="false">☰</button>
                <div>
                    <span class="admin-topbar-kicker">YSA ADMIN</span>
                    <strong><?= e($pageTitle) ?></strong>
                </div>
            </div>
            <a class="admin-view-site" href="<?= e(url()) ?>">Ver site <span>↗</span></a>
        </header>
