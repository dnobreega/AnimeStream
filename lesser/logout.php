<?php
require __DIR__ . '/includes/bootstrap.php';
if(!empty($_COOKIE[REMEMBER_COOKIE])){
    $parts=explode(':',(string)$_COOKIE[REMEMBER_COOKIE],2);
    if(!empty($parts[0])) db()->prepare('DELETE FROM tokens_lembrar WHERE selector=?')->execute([$parts[0]]);
}
setcookie(REMEMBER_COOKIE,'',['expires'=>time()-3600,'path'=>BASE_URL?:'/','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off','httponly'=>true,'samesite'=>'Lax']);
$_SESSION=[]; if(ini_get('session.use_cookies')){ $params=session_get_cookie_params(); setcookie(session_name(),'',['expires'=>time()-42000,'path'=>$params['path'],'domain'=>$params['domain'],'secure'=>$params['secure'],'httponly'=>$params['httponly'],'samesite'=>$params['samesite']??'Lax']); }
session_destroy(); header('Location: '.url()); exit;
