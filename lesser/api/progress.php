<?php
declare(strict_types=1);
require __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['ok'=>false, 'message'=>'Faça login para salvar seu progresso.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok'=>false, 'message'=>'Método não permitido.']);
    exit;
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null);
if (!verify_csrf($csrf)) {
    http_response_code(419);
    echo json_encode(['ok'=>false, 'message'=>'Token CSRF inválido.']);
    exit;
}

$episodeId = (int)($_POST['episode_id'] ?? 0);
$currentTime = (float)($_POST['current_time'] ?? 0);
$duration = (float)($_POST['duration'] ?? 0);

if ($episodeId <= 0 || !find_episode($episodeId)) {
    http_response_code(422);
    echo json_encode(['ok'=>false, 'message'=>'Episódio inválido.']);
    exit;
}

if (!is_finite($currentTime) || !is_finite($duration) || $currentTime < 0 || $duration < 0) {
    http_response_code(422);
    echo json_encode(['ok'=>false, 'message'=>'Progresso inválido.']);
    exit;
}

save_episode_progress($episodeId, (int)current_user()['id'], $currentTime, $duration);
$percent = $duration > 0 ? min(100, max(0, round(($currentTime / $duration) * 100, 1))) : 0;

echo json_encode([
    'ok' => true,
    'percentual' => $percent,
    'tempo_assistido' => round($currentTime, 2),
    'duracao' => round($duration, 2),
    'concluido' => ($duration > 0 && ($duration - $currentTime) <= 8),
]);
