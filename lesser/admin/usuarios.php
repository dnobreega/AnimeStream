<?php
require __DIR__.'/../includes/bootstrap.php';
require_admin();
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $id=(int)($_POST['id']??0);
    $acao=(string)($_POST['acao']??'');
    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Ação inválida.';
    $u=$id?db()->prepare('SELECT * FROM usuarios WHERE id=? LIMIT 1'):null;
    if($u){$u->execute([$id]);$user=$u->fetch();} else $user=null;
    if(!$errors && !$user) $errors[]='Usuário não encontrado.';
    if(!$errors && $acao==='salvar'){
        $nome=trim((string)($_POST['nome']??''));
        $email=strtolower(trim((string)($_POST['email']??'')));
        $tipo=($_POST['tipo']??'user')==='admin'?'admin':'user';
        $senha=(string)($_POST['senha']??'');
        if(mb_strlen($nome,'UTF-8')<3) $errors[]='Nome inválido.';
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='E-mail inválido.';
        $check=db()->prepare('SELECT id FROM usuarios WHERE email=? AND id<>? LIMIT 1');
        $check->execute([$email,$id]);
        if($check->fetch()) $errors[]='E-mail já utilizado.';
        if($tipo==='user' && $user['tipo']==='admin' && admin_count()<=1) $errors[]='O sistema precisa manter ao menos um administrador.';
        if(!$errors){
            if($senha!=='') db()->prepare('UPDATE usuarios SET nome=?,email=?,tipo=?,senha=?,senha_criptografada=? WHERE id=?')->execute([$nome,$email,$tipo,password_hash($senha,PASSWORD_DEFAULT),encrypt_password_for_admin($senha),$id]);
            else db()->prepare('UPDATE usuarios SET nome=?,email=?,tipo=? WHERE id=?')->execute([$nome,$email,$tipo,$id]);
            if((int)current_user()['id']===$id){
                $_SESSION['user']['name']=$nome;
                $_SESSION['user']['email']=$email;
                $_SESSION['user']['type']=$tipo;
            }
            flash('success','Usuário atualizado.');
            redirect('admin/usuarios.php');
        }
    } elseif(!$errors && $acao==='excluir'){
        if((int)current_user()['id']===$id) $errors[]='Você não pode excluir a própria conta.';
        elseif($user['tipo']==='admin' && admin_count()<=1) $errors[]='Não é possível excluir o último administrador.';
        else { remove_profile_photo($user['foto_perfil'] ?? null); db()->prepare('DELETE FROM usuarios WHERE id=?')->execute([$id]); flash('success','Usuário excluído.'); redirect('admin/usuarios.php'); }
    }
}
$users=all_users();
$pageTitle='Gerenciar usuários';
require __DIR__.'/../includes/admin_header.php';
?>
<main class="section container admin-page">
    <div class="admin-top"><div><span class="section-kicker">CONTAS</span><h1>Gerenciar usuários</h1><p>Altere dados, permissões e senha sem sair da página.</p></div></div>
    <?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?>
    <div class="user-grid">
        <?php foreach($users as $u): ?>
            <article class="user-card reveal">
                <div class="user-card-top">
                    <?= avatar_html($u['foto_perfil'] ?? null, $u['nome'], 'avatar') ?>
                    <div><strong><?= e($u['nome']) ?></strong><small><?= e($u['email']) ?></small></div>
                    <span class="role <?= e($u['tipo']) ?>"><?= $u['tipo']==='admin'?'ADMIN':'USUÁRIO' ?></span>
                </div>
                <form method="post" class="user-edit">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="acao" value="salvar">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <label>Nome<input name="nome" value="<?= e($u['nome']) ?>" required></label>
                    <label>E-mail<input type="email" name="email" value="<?= e($u['email']) ?>" required></label>
                    <label>Permissão<select name="tipo"><option value="user" <?= $u['tipo']==='user'?'selected':'' ?>>Usuário normal</option><option value="admin" <?= $u['tipo']==='admin'?'selected':'' ?>>Administrador</option></select></label>
                    <div class="password-grid admin-password-edit">
                        <label>Senha cadastrada
                            <?php $visiblePassword = decrypt_password_for_admin($u['senha_criptografada'] ?? null); ?>
                            <div class="admin-password-view">
                                <div class="password-field">
                                    <input
                                        type="password"
                                        class="admin-visible-password"
                                        value="<?= e($visiblePassword ?? '') ?>"
                                        placeholder="Senha: não disponível"
                                        readonly
                                        autocomplete="off"
                                        aria-label="Senha cadastrada"
                                    >
                                    <button type="button" class="password-toggle" data-password-value="<?= e($visiblePassword ?? '') ?>">Mostrar</button>
                                </div>
                                <small class="field-hint"><?= $visiblePassword !== null ? 'A senha pode ser visualizada somente nesta área administrativa.' : 'Esta conta foi criada antes do sistema de visualização. Defina uma nova senha para armazená-la de forma criptografada.' ?></small>
                            </div>
                        </label>
                        <label>Nova senha
                            <div class="password-field"><input type="password" name="senha" placeholder="Digite uma nova senha" autocomplete="new-password"><button type="button" class="password-toggle">Mostrar</button></div>
                            <small class="field-hint">O administrador pode redefinir a senha sem informar a senha atual.</small>
                        </label>
                    </div>
                    <div class="user-form-actions"><button class="btn-action edit" type="submit">Salvar alterações</button></div>
                </form>
                <form method="post" class="user-delete-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button class="btn-action delete" data-delete-label="este usuário" type="submit">Excluir usuário</button>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
</main>
<?php require __DIR__.'/../includes/admin_footer.php'; ?>
