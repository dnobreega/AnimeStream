<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();

$pageTitle = 'Gerenciar banners';
$errors = [];

if (!banners_table_exists()) {
    $errors[] = 'A tabela de banners ainda não foi instalada. Execute database/migration_banners.sql no banco anime_stream.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && banners_table_exists()) {
    if (!verify_csrf($_POST['csrf'] ?? null)) {
        flash('error', 'Ação inválida.');
        redirect('admin/banners.php');
    }

    if (isset($_POST['delete_id'])) {
        $id = (int)$_POST['delete_id'];
        $banner = find_banner($id);
        if ($banner) {
            db()->prepare('DELETE FROM banners WHERE id=?')->execute([$id]);
            remove_banner_image($banner['imagem'] ?? null);
            flash('success', 'Banner excluído.');
        }
        redirect('admin/banners.php');
    }

    if (isset($_POST['toggle_id'])) {
        $id = (int)$_POST['toggle_id'];
        db()->prepare('UPDATE banners SET ativo = IF(ativo=1,0,1) WHERE id=?')->execute([$id]);
        flash('success', 'Status do banner atualizado.');
        redirect('admin/banners.php');
    }
}

$banners = banners_table_exists() ? all_banners() : [];
require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page banner-admin-page">
    <div class="admin-top banner-admin-top">
        <div>
            <span class="section-kicker">HOME • CARROSSEL</span>
            <h1>Gerenciar banners</h1>
            <p>Cadastre, troque as imagens, organize a ordem e escolha quais banners aparecem na página inicial.</p>
        </div>
        <?php if (banners_table_exists()): ?>
            <a class="btn btn-primary" href="<?= e(url('admin/banner-form.php')) ?>">+ Novo banner</a>
        <?php endif; ?>
    </div>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if (banners_table_exists()): ?>
        <section class="banner-admin-intro">
            <div>
                <span class="section-kicker">CARROSSEL PRINCIPAL</span>
                <h2>Controle os banners da Home</h2>
                <p>Cada banner pode usar uma imagem própria, estar ligado a um anime e ter título, descrição, ordem e status independentes.</p>
            </div>
            <div class="banner-summary">
                <strong><?= count($banners) ?></strong>
                <span>banner<?= count($banners) === 1 ? '' : 's' ?> cadastrado<?= count($banners) === 1 ? '' : 's' ?></span>
            </div>
        </section>

        <?php if (!$banners): ?>
            <div class="empty-state banner-empty">
                <strong>Nenhum banner cadastrado.</strong>
                <span>Crie o primeiro banner para começar o carrossel da Home.</span>
                <a class="btn btn-primary" href="<?= e(url('admin/banner-form.php')) ?>">Cadastrar primeiro banner</a>
            </div>
        <?php else: ?>
            <section class="banner-admin-grid">
                <?php foreach ($banners as $banner): ?>
                    <article class="banner-admin-card <?= !empty($banner['ativo']) ? 'is-active' : 'is-inactive' ?>">
                        <div class="banner-admin-image-wrap">
                            <img src="<?= e(url($banner['imagem'])) ?>" alt="Banner de <?= e($banner['titulo']) ?>">
                            <div class="banner-admin-overlay">
                                <span class="banner-order">#<?= (int)$banner['ordem'] ?></span>
                                <span class="featured-status <?= !empty($banner['ativo']) ? 'on' : 'off' ?>"><?= !empty($banner['ativo']) ? 'Ativo' : 'Inativo' ?></span>
                            </div>
                        </div>
                        <div class="banner-admin-content">
                            <span class="banner-admin-anime"><?= e($banner['anime_titulo']) ?></span>
                            <h3><?= e($banner['titulo']) ?></h3>
                            <p><?= e($banner['descricao'] ?: 'Sem descrição personalizada.') ?></p>
                            <div class="banner-admin-meta">
                                <span><?= $banner['ano'] ? e((string)$banner['ano']) : 'Sem ano' ?></span>
                                <span><?= e($banner['video'] ?? '') ?></span>
                            </div>
                            <div class="banner-admin-actions">
                                <a class="btn-action edit" href="<?= e(url('admin/banner-form.php?id='.(int)$banner['id'])) ?>">Editar</a>
                                <form method="post" class="action-inline">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="toggle_id" value="<?= (int)$banner['id'] ?>">
                                    <button class="btn-action banner-toggle-btn <?= !empty($banner['ativo']) ? 'off' : 'on' ?>" type="submit"><?= !empty($banner['ativo']) ? 'Desativar' : 'Ativar' ?></button>
                                </form>
                                <form method="post" class="action-inline">
                                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                    <input type="hidden" name="delete_id" value="<?= (int)$banner['id'] ?>">
                                    <button class="btn-action delete" type="submit" data-delete-label="este banner">Excluir</button>
                                </form>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
