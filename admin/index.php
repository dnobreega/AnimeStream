<?php
require __DIR__.'/../includes/bootstrap.php'; require_admin();
$pageTitle='Painel administrativo';
$stats=[
    'Animes'=>(int)db()->query('SELECT COUNT(*) FROM animes')->fetchColumn(),
    'Temporadas'=>(int)db()->query('SELECT COUNT(*) FROM temporadas')->fetchColumn(),
    'Episódios'=>(int)db()->query('SELECT COUNT(*) FROM episodios')->fetchColumn(),
    'Usuários'=>(int)db()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
    'Comentários'=>(int)db()->query('SELECT COUNT(*) FROM comentarios')->fetchColumn(),
    'Banners'=>banner_count(),
];
require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page"><div class="admin-top"><div><span class="section-kicker">ADMIN</span><h1>Painel de controle</h1><p>Gerencie catálogo, temporadas, episódios e usuários.</p></div></div>
<div class="stat-grid"><?php foreach($stats as $label=>$value): ?><div class="stat-card reveal"><span><?= e($label) ?></span><strong><?= $value ?></strong></div><?php endforeach; ?></div>
<div class="admin-grid"><a class="admin-card reveal" href="<?= e(url('admin/animes.php')) ?>"><b>Animes</b><span>Adicionar, editar e excluir títulos do catálogo.</span></a><a class="admin-card reveal" href="<?= e(url('admin/banners.php')) ?>"><b>Banners</b><span>Trocar imagens, cadastrar, ativar, ordenar e excluir banners da Home.</span></a><a class="admin-card reveal" href="<?= e(url('admin/temporadas.php')) ?>"><b>Temporadas</b><span>Criar novas temporadas para qualquer anime.</span></a><a class="admin-card reveal" href="<?= e(url('admin/episodios.php')) ?>"><b>Episódios</b><span>Controlar títulos, thumbnails e vídeos.</span></a><a class="admin-card reveal" href="<?= e(url('admin/usuarios.php')) ?>"><b>Usuários</b><span>Definir quem é usuário e quem é administrador.</span></a><a class="admin-card reveal" href="<?= e(url('admin/comentarios.php')) ?>"><b>Comentários</b><span>Visualizar e excluir comentários dos episódios.</span></a></div></main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
