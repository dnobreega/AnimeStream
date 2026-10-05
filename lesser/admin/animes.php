<?php
require __DIR__.'/../includes/bootstrap.php'; require_admin();
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_id'])){
    if(!verify_csrf($_POST['csrf']??null)){ flash('error','Ação inválida.'); redirect('admin/animes.php'); }
    try{ db()->prepare('DELETE FROM animes WHERE id=?')->execute([(int)$_POST['delete_id']]); flash('success','Anime removido.'); }catch(Throwable $e){ flash('error','Não foi possível remover. Exclua temporadas e episódios vinculados primeiro.'); }
    redirect('admin/animes.php');
}
$animes=all_animes(); $pageTitle='Gerenciar animes'; require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page"><div class="admin-top"><div><span class="section-kicker">CATÁLOGO</span><h1>Gerenciar animes</h1></div><a class="btn btn-primary" href="<?= e(url('admin/anime-form.php')) ?>">+ Novo anime</a></div>
<div class="table-wrap"><table><thead><tr><th>Anime</th><th>Ano</th><th>Temporadas</th><th>Episódios</th><th>Destaque</th><th>Ações</th></tr></thead><tbody><?php foreach($animes as $a): ?><tr><td><div class="table-title"><img src="<?= e(url($a['poster'])) ?>" alt=""><div><strong><?= e($a['titulo']) ?></strong><small><?= e($a['slug']) ?></small></div></div></td><td><?= e((string)$a['ano']) ?></td><td><?= (int)$a['total_temporadas'] ?></td><td><?= (int)$a['total_episodios'] ?></td><td><span class="featured-status <?= $a['destaque']?'on':'off' ?>"><?= $a['destaque']?'Em destaque':'Normal' ?></span></td><td class="actions"><div class="action-buttons"><a class="btn-action edit" href="<?= e(url('admin/anime-form.php?id='.(int)$a['id'])) ?>">Editar</a><form class="action-inline" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="delete_id" value="<?= (int)$a['id'] ?>"><button class="btn-action delete" data-delete-label="este anime e todos os dados vinculados" type="submit">Excluir</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
