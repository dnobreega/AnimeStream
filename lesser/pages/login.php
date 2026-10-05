<?php
require __DIR__ . '/../includes/bootstrap.php';
if(is_logged_in()) redirect('index.php');
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=strtolower(trim((string)($_POST['email']??''))); $senha=(string)($_POST['senha']??''); $remember=!empty($_POST['remember']);
    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Sessão do formulário inválida.';
    if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Informe um e-mail válido.';
    if($senha==='') $errors[]='Informe sua senha.';
    if(!$errors){ $user=find_user_by_email($email); if(!$user || !password_verify($senha,$user['senha'])) $errors[]='E-mail ou senha inválidos.'; else { login_user($user); if($remember) remember_user((int)$user['id']); redirect('index.php'); } }
}
$pageTitle='Entrar'; require __DIR__.'/../includes/header.php';
?>
<main class="auth-page"><div class="auth-card reveal"><span class="section-kicker">BEM-VINDO DE VOLTA</span><h1>Entrar</h1><?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?><form method="post" class="form-stack"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>E-mail<input type="email" name="email" required value="<?= e($_POST['email']??'') ?>"></label><label>Senha<div class="password-field"><input id="loginPassword" type="password" name="senha" required><button type="button" class="password-toggle" data-target="loginPassword">Mostrar</button></div></label><label class="check"><input type="checkbox" name="remember" value="1"> Lembrar de mim</label><button class="btn btn-primary" type="submit">Entrar</button></form><div class="auth-links"><a href="<?= e(url('pages/recuperar-senha.php')) ?>">Esqueci minha senha</a><a href="<?= e(url('pages/register.php')) ?>">Criar uma conta</a></div></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
