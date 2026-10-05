<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => BASE_URL ?: '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
]);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . '/functions.php';
restore_remembered_user();
