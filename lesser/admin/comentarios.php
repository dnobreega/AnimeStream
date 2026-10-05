<?php
require __DIR__.'/../includes/bootstrap.php'; require_admin();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id=(int)($_POST['id']??0);
    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Ação inválida.';
    if(!$errors){ $stmt=db()->prepare('DELETE FROM comentarios WHERE id=?'); $stmt->execute([$id]); flash('success','Comentário excluído.'); redirect('admin/comentarios.php'); }
}
$comments=db()->query("SELECT c.*,u.nome usuario_nome,a.titulo anime_titulo,e.numero episodio_numero FROM comentarios c INNER JOIN usuarios u ON u.id=c.usuario_id INNER JOIN episodios e ON e.id=c.episodio_id INNER JOIN temporadas t ON t.id=e.temporada_id INNER JOIN animes a ON a.id=t.anime_id ORDER BY c.criado_em DESC,c.id DESC")->fetchAll();
$pageTitle='Gerenciar comentários'; require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page"><div class="admin-top"><div><span class="section-kicker">COMUNIDADE</span><h1>Gerenciar comentários</h1><p>Exclua comentários que não devem permanecer no site.</p></div></div><?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?><div class="table-wrap reveal"><table><thead><tr><th>Usuário</th><th>Anime / Episódio</th><th>Comentário</th><th>Data</th><th>Ação</th></tr></thead><tbody><?php foreach($comments as $c): ?><tr><td><?= e($c['usuario_nome']) ?></td><td><?= e($c['anime_titulo']) ?> • E<?= str_pad((string)$c['episodio_numero'],2,'0',STR_PAD_LEFT) ?></td><td><?= e(mb_strimwidth($c['texto'],0,90,'…','UTF-8')) ?></td><td><?= e(date('d/m/Y H:i',strtotime((string)$c['criado_em']))) ?></td><td><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="btn-action delete" data-delete-label="este comentário" type="submit">Excluir</button></form></td></tr><?php endforeach; ?><?php if(!$comments): ?><tr><td colspan="5">Nenhum comentário cadastrado.</td></tr><?php endif; ?></tbody></table></div></main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
