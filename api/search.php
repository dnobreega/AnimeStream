<?php
require __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$q = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($q, 'UTF-8') < 1) {
    echo json_encode(['items'=>[]], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $items = array_map(static function(array $anime): array {
        return [
            'id' => (int)$anime['id'],
            'titulo' => $anime['titulo'],
            'ano' => $anime['ano'] ? (int)$anime['ano'] : null,
            'generos' => $anime['generos'],
            'estudio' => $anime['estudio'],
            'tipo' => $anime['tipo'],
            'poster' => url($anime['poster']),
            'url' => url('pages/anime.php?slug=' . urlencode($anime['slug'])),
        ];
    }, search_animes($q, 8));

    echo json_encode(['items'=>$items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['items'=>[], 'error'=>'Não foi possível realizar a pesquisa.'], JSON_UNESCAPED_UNICODE);
}
