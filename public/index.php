<?php
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../utils.php';
if (current_user()) {
    header('Location: ' . url('/books.php'));
    exit;
} else {
    header('Location: ' . url('/login.php'));
    exit;
}
