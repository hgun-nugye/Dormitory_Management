<?php
header('Content-Type: text/html; charset=UTF-8');
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? '1' : '0');
session_start();
require_once __DIR__ . '/../config/db.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function validateCsrfToken(array $post): bool
{
    return isset($post['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $post['csrf_token']);
}

$pdo = dbConnect();
$message = '';
