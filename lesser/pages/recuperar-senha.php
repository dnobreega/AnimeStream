<?php
require __DIR__.'/../includes/bootstrap.php'; $message=null; $error=null;
if($_SERVER['REQUEST_METHOD']==='POST'){
    $email=strtolower(trim((string)($_POST['email']??'')));
    if(!verify_csrf($_POST['csrf']??null) || !filter_var($email,FILTER_VALIDATE_EMAIL)) $error='Informe um e-mail válido.';
    else { $u=find_user_by_email($email); if($u){ $token=create_password_reset((int)$u['id']); $message='Link de demonstração: '.url('pages/redefinir-senha.php?token='.$token); } else $message='Se o e-mail existir, um link de recuperação será gerado.'; }
}
$pageTitle='Recuperar senha'; require __DIR__.'/../includes/header.php';
?>
<main class="auth-page"><div class="auth-card reveal"><span class="section-kicker">RECUPERAÇÃO</span><h1>Redefinir senha</h1><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?><?php if($message): ?><div class="alert success"><?= e($message) ?></div><?php endif; ?><form method="post" class="form-stack"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>E-mail<input type="email" name="email" required></label><button class="btn btn-primary">Gerar link</button></form><div class="auth-links"><a href="<?= e(url('pages/login.php')) ?>">Voltar para o login</a></div></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
