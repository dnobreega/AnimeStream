<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
if(!is_logged_in()){http_response_code(401);echo json_encode(['ok'=>false,'message'=>'Faça login para comentar.'],JSON_UNESCAPED_UNICODE);exit;}
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['ok'=>false,'message'=>'Método não permitido.'],JSON_UNESCAPED_UNICODE);exit;}
$csrf=$_SERVER['HTTP_X_CSRF_TOKEN']??($_POST['csrf']??null);
if(!verify_csrf($csrf)){http_response_code(419);echo json_encode(['ok'=>false,'message'=>'Token de segurança inválido.'],JSON_UNESCAPED_UNICODE);exit;}
$action=(string)($_POST['action']??'add');$user=current_user();$uid=(int)$user['id'];
try{
    if($action==='add'){
        $eid=(int)($_POST['episode_id']??0);$texto=trim((string)($_POST['texto']??''));
        if($eid<=0||!find_episode($eid)){http_response_code(422);echo json_encode(['ok'=>false,'message'=>'Episódio inválido.'],JSON_UNESCAPED_UNICODE);exit;}
        if(mb_strlen($texto,'UTF-8')<2){http_response_code(422);echo json_encode(['ok'=>false,'message'=>'Escreva pelo menos 2 caracteres.'],JSON_UNESCAPED_UNICODE);exit;}
        if(mb_strlen($texto,'UTF-8')>1000){http_response_code(422);echo json_encode(['ok'=>false,'message'=>'O comentário pode ter no máximo 1000 caracteres.'],JSON_UNESCAPED_UNICODE);exit;}
        db()->prepare('INSERT INTO comentarios(episodio_id,usuario_id,texto) VALUES(?,?,?)')->execute([$eid,$uid,$texto]);
        $cid=(int)db()->lastInsertId();
        $st=db()->prepare('SELECT c.id,c.texto,c.criado_em,u.nome usuario_nome,u.tipo usuario_tipo,u.foto_perfil usuario_foto FROM comentarios c INNER JOIN usuarios u ON u.id=c.usuario_id WHERE c.id=? LIMIT 1');$st->execute([$cid]);$row=$st->fetch();
        if(!$row)throw new RuntimeException('Comentário não encontrado.');
        echo json_encode(['ok'=>true,'action'=>'add','comment'=>['id'=>(int)$row['id'],'nome'=>$row['usuario_nome'],'tipo'=>$row['usuario_tipo'],'foto'=>$row['usuario_foto'],'texto'=>$row['texto'],'criado_em'=>date('d/m/Y H:i',strtotime((string)$row['criado_em']))]],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);exit;
    }
    if($action==='delete'){
        $cid=(int)($_POST['comment_id']??0);$comment=find_comment($cid);
        if(!$comment){http_response_code(404);echo json_encode(['ok'=>false,'message'=>'Comentário não encontrado.'],JSON_UNESCAPED_UNICODE);exit;}
        if(!can_delete_comment($comment,is_admin())){http_response_code(403);echo json_encode(['ok'=>false,'message'=>'Você não pode excluir este comentário.'],JSON_UNESCAPED_UNICODE);exit;}
        db()->prepare('DELETE FROM comentarios WHERE id=?')->execute([$cid]);
        echo json_encode(['ok'=>true,'action'=>'delete','comment_id'=>$cid],JSON_UNESCAPED_UNICODE);exit;
    }
    http_response_code(422);echo json_encode(['ok'=>false,'message'=>'Ação inválida.'],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){http_response_code(500);echo json_encode(['ok'=>false,'message'=>'Não foi possível concluir a ação.'],JSON_UNESCAPED_UNICODE);}
