<?php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../i18n.php';
$cfg = require __DIR__ . '/../../config.php';
$user = current_user();
$isAdmin = $user && $user['role'] === 'admin';
?>
<nav class="navbar navbar-expand-lg shadow-sm sticky-top" id="topbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php"><?= t('app_title') ?></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navmenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navmenu">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="dashboard.php"><?= t('books') ?></a></li>
                <?php if ($isAdmin): ?>
                    <li class="nav-item"><a class="nav-link" href="users.php"><?= t('users') ?></a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle"
                        data-bs-toggle="dropdown"><?= t('language_ui') ?></button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php foreach (($cfg['locales'] ?? []) as $code => $meta): ?>
                            <li><a class="dropdown-item"
                                    href="?lang=<?= $code ?>"><?= htmlspecialchars($meta['label']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <button id="themeToggle" class="btn btn-outline-secondary">🌙 <?= t('dark_mode') ?></button>
                <?php if ($user): ?>
                    <span class="navbar-text small">👤 <?= htmlspecialchars($user['name']) ?>
                        (<?= htmlspecialchars($user['role']) ?>)</span>
                    <a class="btn btn-danger" href="logout.php"><?= t('logout') ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>