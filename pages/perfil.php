<?php
require __DIR__.'/../includes/bootstrap.php';
require_login();

$userId = (int)current_user()['id'];
$stmt = db()->prepare('SELECT id,nome,email,senha,tipo,foto_perfil,criado_em FROM usuarios WHERE id=? LIMIT 1');
$stmt->execute([$userId]);
$profile = $stmt->fetch();
if (!$profile) { redirect('logout.php'); }

$errors = [];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nome = trim((string)($_POST['nome'] ?? ''));
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $senhaAtual = (string)($_POST['senha_atual'] ?? '');
    $novaSenha = (string)($_POST['nova_senha'] ?? '');
    $confirmSenha = (string)($_POST['confirm_senha'] ?? '');
    $removePhoto = !empty($_POST['remover_foto']);

    if (!verify_csrf($_POST['csrf'] ?? null)) $errors[]='Formulário inválido. Atualize a página e tente novamente.';
    if (mb_strlen($nome, 'UTF-8') < 3) $errors[]='O nome deve ter pelo menos 3 caracteres.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[]='Informe um e-mail válido.';

    $check = db()->prepare('SELECT id FROM usuarios WHERE email=? AND id<>? LIMIT 1');
    $check->execute([$email, $userId]);
    if ($check->fetch()) $errors[]='Este e-mail já está em uso.';

    if ($novaSenha !== '' || $confirmSenha !== '' || $senhaAtual !== '') {
        if ($senhaAtual === '') $errors[]='Informe sua senha atual para alterar a senha.';
        elseif (!password_verify($senhaAtual, (string)($profile['senha'] ?? ''))) $errors[]='A senha atual está incorreta.';
        if (strlen($novaSenha) < 8) $errors[]='A nova senha deve ter pelo menos 8 caracteres.';
        if ($novaSenha !== $confirmSenha) $errors[]='As novas senhas não coincidem.';
    }

    $newPhoto = (string)($profile['foto_perfil'] ?? '');
    if (!$errors && isset($_FILES['foto_perfil']) && ($_FILES['foto_perfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        try { $newPhoto = store_profile_photo($_FILES['foto_perfil'], $profile['foto_perfil'] ?? null); }
        catch (Throwable $e) { $errors[] = $e->getMessage(); }
    } elseif (!$errors && $removePhoto) {
        remove_profile_photo($profile['foto_perfil'] ?? null);
        $newPhoto = '';
    }

    if (!$errors) {
        if ($novaSenha !== '') {
            db()->prepare('UPDATE usuarios SET nome=?,email=?,senha=?,senha_criptografada=?,foto_perfil=? WHERE id=?')->execute([$nome,$email,password_hash($novaSenha,PASSWORD_DEFAULT),encrypt_password_for_admin($novaSenha),$newPhoto ?: null,$userId]);
        } else {
            db()->prepare('UPDATE usuarios SET nome=?,email=?,foto_perfil=? WHERE id=?')->execute([$nome,$email,$newPhoto ?: null,$userId]);
        }
        $_SESSION['user']['name'] = $nome;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['photo'] = $newPhoto ?: null;
        $profile['nome'] = $nome;
        $profile['email'] = $email;
        $profile['foto_perfil'] = $newPhoto ?: null;
        flash('success','Perfil atualizado com sucesso.');
        redirect('pages/perfil.php');
    }

    $profile['nome']=$nome;
    $profile['email']=$email;
}

$commentCount = user_comment_count($userId);
$pageTitle='Meu perfil';
require __DIR__.'/../includes/header.php';
$profilePhoto = trim((string)($profile['foto_perfil'] ?? ''));
?>
<main class="section container profile-page">
    <div class="profile-hero reveal">
        <div class="profile-avatar-wrap"><?= avatar_html($profilePhoto, $profile['nome'], 'profile-avatar') ?></div>
        <div>
            <span class="section-kicker">MINHA CONTA</span>
            <h1><?= e($profile['nome']) ?></h1>
            <p><?= e($profile['email']) ?></p>
        </div>
        <span class="role <?= e($profile['tipo']) ?>"><?= $profile['tipo']==='admin' ? 'ADMINISTRADOR' : 'USUÁRIO' ?></span>
    </div>

    <?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>

    <div class="profile-grid">
        <section class="form-card reveal">
            <span class="section-kicker">DADOS DA CONTA</span>
            <h2>Editar perfil</h2>
            <form method="post" enctype="multipart/form-data" class="form-stack">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="profile-photo-editor">
                    <div class="profile-photo-preview" id="profilePhotoPreview" aria-label="Pré-visualização da foto de perfil">
                        <?php if ($profilePhoto): ?>
                            <img class="avatar-image" src="<?= e(url($profilePhoto)) ?>" alt="Pré-visualização da foto de <?= e($profile['nome']) ?>">
                        <?php else: ?>
                            <span class="avatar-fallback profile-photo-initial" aria-hidden="true"><?= e(strtoupper(mb_substr($profile['nome'], 0, 1, 'UTF-8'))) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="profile-photo-controls">
                        <strong>Foto de perfil</strong>
                        <span>JPG, PNG ou WebP · até 2 MB</span>
                        <label class="upload-btn">Escolher foto<input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp"></label>
                        <?php if ($profilePhoto): ?>
                            <button type="button" class="remove-photo-btn" id="removeCurrentPhotoBtn">Remover foto atual</button>
                            <input type="hidden" name="remover_foto" id="removePhotoInput" value="0">
                        <?php endif; ?>
                    </div>
                </div>
                <label>Nome<input type="text" name="nome" value="<?= e($profile['nome']) ?>" minlength="3" required></label>
                <label>E-mail<input type="email" name="email" value="<?= e($profile['email']) ?>" required></label>
                <button class="btn btn-primary" type="submit">Salvar alterações</button>
            </form>
        </section>

        <section class="form-card reveal">
            <span class="section-kicker">SEGURANÇA</span>
            <h2>Alterar senha</h2>
            <form method="post" class="form-stack">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="hidden" name="nome" value="<?= e($profile['nome']) ?>">
                <input type="hidden" name="email" value="<?= e($profile['email']) ?>">
                <label>Senha atual<div class="password-field"><input id="currentPassword" type="password" name="senha_atual" autocomplete="current-password"><button type="button" class="password-toggle" data-target="currentPassword">Mostrar</button></div></label>
                <label>Nova senha<div class="password-field"><input id="newPassword" type="password" name="nova_senha" minlength="8" autocomplete="new-password"><button type="button" class="password-toggle" data-target="newPassword">Mostrar</button></div></label>
                <label>Confirmar nova senha<div class="password-field"><input id="confirmPassword" type="password" name="confirm_senha" minlength="8" autocomplete="new-password"><button type="button" class="password-toggle" data-target="confirmPassword">Mostrar</button></div></label>
                <button class="btn btn-ghost" type="submit">Alterar senha</button>
            </form>
        </section>
    </div>

    <section class="profile-stats reveal">
        <div><span>Comentários</span><strong><?= $commentCount ?></strong></div>
        <div><span>Conta criada</span><strong><?= e(date('d/m/Y', strtotime((string)$profile['criado_em']))) ?></strong></div>
    </section>
</main>
<?php require __DIR__.'/../includes/footer.php'; ?>
