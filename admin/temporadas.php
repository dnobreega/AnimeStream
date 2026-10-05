<?php
require __DIR__.'/../includes/bootstrap.php'; require_admin();
$seasons=all_seasons(); $pageTitle='Gerenciar temporadas'; require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page"><div class="admin-top"><div><span class="section-kicker">ORGANIZAÇÃO</span><h1>Temporadas</h1></div><a class="btn btn-primary" href="<?= e(url('admin/temporada-form.php')) ?>">+ Nova temporada</a></div><div class="table-wrap"><table><thead><tr><th>Anime</th><th>Temporada</th><th>Episódios</th><th>Ações</th></tr></thead><tbody><?php foreach($seasons as $s): ?><tr><td><?= e($s['anime_titulo']) ?></td><td>S<?= str_pad((string)$s['numero'],2,'0',STR_PAD_LEFT) ?> — <?= e($s['titulo']) ?></td><td><?= (int)$s['total_episodios'] ?></td><td class="actions"><div class="action-buttons"><a class="btn-action edit" href="<?= e(url('admin/temporada-form.php?id='.(int)$s['id'])) ?>">Editar</a><a class="btn-action delete" data-delete-label="esta temporada" href="<?= e(url('admin/temporada-delete.php?id='.(int)$s['id'])) ?>">Excluir</a></div></td></tr><?php endforeach; ?></tbody></table></div></main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
