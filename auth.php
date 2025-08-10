<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/config.php';

function current_user() {
    return $_SESSION['user'] ?? null;
}
function require_login() {
    if (!current_user()) {
        header('Location: ' . url('/login.php'));
        exit;
    }
}
// URL helper
function url($path) {
    $base = BASE_URL ?: (dirname($_SERVER['SCRIPT_NAME']) !== '/' ? dirname($_SERVER['SCRIPT_NAME']) : '');
    return rtrim($base, '/') . $path;
}
?>
