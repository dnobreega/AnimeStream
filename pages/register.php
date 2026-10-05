<?php
require __DIR__ . '/../includes/bootstrap.php';
if(is_logged_in()) redirect('index.php'); $errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome=trim((string)($_POST['nome']??'')); $email=strtolower(trim((string)($_POST['email']??''))); $senha=(string)($_POST['senha']??''); $confirm=(string)($_POST['confirm']??'');
    if(!verify_csrf($_POST['csrf']??null)) $errors[]='Sessão do formulário inválida.';
    if(mb_strlen($nome)<3) $errors[]='Nome muito curto.'; if(!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='E-mail inválido.'; if(strlen($senha)<8) $errors[]='A senha deve ter pelo menos 8 caracteres.'; if($senha!==$confirm) $errors[]='As senhas não coincidem.'; if(find_user_by_email($email)) $errors[]='Este e-mail já está cadastrado.';
    if(!$errors){ db()->prepare("INSERT INTO usuarios(nome,email,senha,senha_criptografada,tipo) VALUES(?,?,?,?, 'user')")->execute([$nome,$email,password_hash($senha,PASSWORD_DEFAULT),encrypt_password_for_admin($senha)]); flash('success','Conta criada com sucesso.'); redirect('pages/login.php'); }
}
$pageTitle='Criar conta'; require __DIR__.'/../includes/header.php';
?>
<main class="auth-page"><div class="auth-card reveal"><span class="section-kicker">NOVO PERFIL</span><h1>Criar conta</h1><?php foreach($errors as $er): ?><div class="alert error"><?= e($er) ?></div><?php endforeach; ?><form method="post" class="form-stack"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label>Nome<input type="text" name="nome" required value="<?= e($_POST['nome']??'') ?>"></label><label>E-mail<input type="email" name="email" required value="<?= e($_POST['email']??'') ?>"></label><label>Senha<div class="password-field"><input id="registerPassword" type="password" name="senha" minlength="8" required><button type="button" class="password-toggle" data-target="registerPassword">Mostrar</button></div></label><label>Confirmar senha<div class="password-field"><input id="registerConfirm" type="password" name="confirm" minlength="8" required><button type="button" class="password-toggle" data-target="registerConfirm">Mostrar</button></div></label><button class="btn btn-primary" type="submit">Cadastrar</button></form><div class="auth-links"><a href="<?= e(url('pages/login.php')) ?>">Já tenho conta</a></div></div></main>
<?php require __DIR__.'/../includes/footer.php'; ?>
