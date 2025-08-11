<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../i18n.php';
$cfg   = require __DIR__ . '/../../config.php';
$user  = current_user();
$isAdmin = $user && $user['role'] === 'admin';
?>
<nav class="navbar navbar-expand-lg shadow-sm sticky-top" id="topbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><?= t('app_title') ?></a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu" aria-controls="navmenu" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><?= t('books') ?></a></li>
        <?php if ($isAdmin): ?>
          <li class="nav-item"><a class="nav-link" href="users.php"><?= t('users') ?></a></li>
        <?php endif; ?>
      </ul>

      <?php if ($user): ?>
        <div class="d-flex align-items-center gap-2">
          <span class="navbar-text small">👤 <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
          <a class="btn btn-danger" href="logout.php"><?= t('logout') ?></a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</nav>
