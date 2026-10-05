<?php
require __DIR__.'/../includes/bootstrap.php'; require_admin();
$id=(int)($_GET['id']??$_POST['id']??0); $anime=$id?find_anime($id):null; $errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $titulo=trim((string)($_POST['titulo']??'')); $slug=slugify(trim((string)($_POST['slug']??'')) ?: $titulo); $tipo=trim((string)($_POST['tipo']??'Série')); $ano=(int)($_POST['ano']??0); $generos=trim((string)($_POST['generos']??'')); $estudio=trim((string)($_POST['estudio']??'')); $sinopse=trim((string)($_POST['sinopse']??'')); $poster=trim((string)($_POST['poster']??'')); $destaque=!empty($_POST['destaque'])?1:0;
    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Formulário inválido.'; if($titulo==='') $errors[]='Informe o título.'; if($slug==='') $errors[]='Informe um slug válido.'; if($poster==='' || !valid_media_url($poster)) $errors[]='Poster deve ser um caminho local ou URL válido.';
    $check=db()->prepare('SELECT id FROM animes WHERE slug=? AND id<>? LIMIT 1'); $check->execute([$slug,$id]); if($check->fetch()) $errors[]='Este slug já existe.';
    if(!$errors){ if($id && $anime){ db()->prepare('UPDATE animes SET titulo=?,slug=?,tipo=?,ano=?,generos=?,estudio=?,sinopse=?,poster=?,destaque=? WHERE id=?')->execute([$titulo,$slug,$tipo,$ano?:null,$generos,$estudio,$sinopse,$poster,$destaque,$id]); flash('success','Anime atualizado.'); } else { db()->prepare('INSERT INTO animes(titulo,slug,tipo,ano,generos,estudio,sinopse,poster,destaque) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$titulo,$slug,$tipo,$ano?:null,$generos,$estudio,$sinopse,$poster,$destaque]); flash('success','Anime criado.'); } redirect('admin/animes.php'); }
} else { if($anime){ $_POST=$anime; } }
$pageTitle=$id?'Editar anime':'Novo anime'; require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page"><div class="form-card reveal"><span class="section-kicker">CATÁLOGO</span><h1><?= $id?'Editar anime':'Novo anime' ?></h1><?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?><form method="post" class="form-grid"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= $id ?>"><label>Título<input name="titulo" required value="<?= e($_POST['titulo']??'') ?>"></label><label>Slug<input name="slug" value="<?= e($_POST['slug']??'') ?>" placeholder="ex.: black-clover"></label><label>Tipo<input name="tipo" value="<?= e($_POST['tipo']??'Série') ?>"></label><label>Ano<input type="number" name="ano" value="<?= e((string)($_POST['ano']??'')) ?>"></label><label>Gêneros<input name="generos" value="<?= e($_POST['generos']??'') ?>"></label><label>Estúdio<input name="estudio" value="<?= e($_POST['estudio']??'') ?>"></label><label class="full">Poster / URL<input name="poster" required value="<?= e($_POST['poster']??'') ?>" placeholder="assets/img/anime/black-clover.svg"></label><label class="full">Sinopse<textarea name="sinopse" rows="5"><?= e($_POST['sinopse']??'') ?></textarea></label><div class="full">
<input class="featured-control-input" type="checkbox" name="destaque" id="animeFeatured" value="1" <?= !empty($_POST['destaque'])?'checked':'' ?>>
<label class="featured-toggle" for="animeFeatured">
<span class="featured-toggle-icon">★</span>
<span class="featured-toggle-copy"><strong>Colocar em destaque</strong><small>O anime aparecerá na seção principal de destaques.</small></span>
<span class="featured-toggle-switch" aria-hidden="true"><i></i></span>
</label>
</div><div class="form-actions full"><a class="btn btn-ghost" href="<?= e(url('admin/animes.php')) ?>">Cancelar</a><button class="btn btn-primary">Salvar anime</button></div></form></div></main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
