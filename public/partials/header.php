<?php
require_once __DIR__ . '/../../auth.php';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc(APP_NAME) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= url('/assets/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?= url('/index.php') ?>"><?= esc(APP_NAME) ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbars" aria-controls="navbars" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbars">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if ($user): ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/books.php') ?>">My Books</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= url('/book_form.php') ?>">Add Book</a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto">
        <?php if ($user): ?>
          <li class="nav-item"><span class="navbar-text me-2">Hi, <?= esc($user['name']) ?></span></li>
          <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="<?= url('/logout.php') ?>">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= url('/login.php') ?>">Login</a></li>
          <li class="nav-item"><a class="btn btn-primary btn-sm ms-2" href="<?= url('/register.php') ?>">Sign up</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
