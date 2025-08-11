<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';
require_login();
$pdo = db();

$bookLanguages = [
    'ckb' => 'Kurdish',
    'en' => 'English',
    'ar' => 'Arabic',
    'fa' => 'Persian',
    'tr' => 'Turkish',
    'fr' => 'French',
    'de' => 'German',
    'es' => 'Spanish',
    'ru' => 'Russian',
    'zh' => 'Chinese'
];

// Filters
$q = trim($_GET['q'] ?? '');
$lang = $_GET['lang'] ?? '';
$genre = (int) ($_GET['genre'] ?? 0);
$yearFrom = (int) ($_GET['yf'] ?? 0);
$yearTo = (int) ($_GET['yt'] ?? 0);
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$where = [];
$args = [];
if ($q !== '') {
    $where[] = '(b.title LIKE ? OR b.author LIKE ? OR b.isbn LIKE ? OR b.tags LIKE ?)';
    $args = array_merge($args, ["%$q%", "%$q%", "%$q%", "%$q%"]);
}
if ($lang !== '' && isset($bookLanguages[$lang])) {
    $where[] = 'b.language_code=?';
    $args[] = $lang;
}
if ($genre > 0) {
    $where[] = 'b.genre_id=?';
    $args[] = $genre;
}
if ($yearFrom > 0) {
    $where[] = 'b.year>=?';
    $args[] = $yearFrom;
}
if ($yearTo > 0) {
    $where[] = 'b.year<=?';
    $args[] = $yearTo;
}

$sqlWhere = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$count = $pdo->prepare("SELECT COUNT(*) FROM books b $sqlWhere");
$count->execute($args);
$total = (int) $count->fetchColumn();

$stmt = $pdo->prepare("SELECT b.*, g.name AS genre_name
                       FROM books b
                       LEFT JOIN genres g ON g.id=b.genre_id
                       $sqlWhere
                       ORDER BY b.created_at DESC
                       LIMIT $perPage OFFSET $offset");
$stmt->execute($args);
$rows = $stmt->fetchAll();

$genres = $pdo->query('SELECT id,name FROM genres ORDER BY name')->fetchAll();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
$user = current_user();
?>
<style>
.cover-cell { width: 64px; }
.thumb { width:64px!important; height:96px!important; object-fit:cover; border-radius:6px; background:#e9ecef; }
.thumb.thumb--placeholder { display:inline-block; }
</style>

<div class="container py-4">
    <div class="d-flex flex-wrap gap-2 align-items-end mb-3" id="filterBar">
        <form class="row g-2 flex-grow-1" method="get">
            <div class="col-md-3">
                <label class="form-label"><?= t('search') ?></label>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control"
                    placeholder="Title / Author / ISBN / Tags">
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= t('language') ?></label>
                <select name="lang" class="form-select">
                    <option value="">—</option>
                    <?php foreach ($bookLanguages as $code => $label): ?>
                        <option value="<?= $code ?>" <?= $code === $lang ? 'selected' : '' ?>><?= htmlspecialchars($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= t('genre') ?></label>
                <select name="genre" class="form-select">
                    <option value="0">—</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?= $g['id'] ?>" <?= (int) $g['id'] === $genre ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= t('year') ?> (from)</label>
                <input type="number" name="yf" value="<?= $yearFrom > 0 ? $yearFrom : '' ?>" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label"><?= t('year') ?> (to)</label>
                <input type="number" name="yt" value="<?= $yearTo > 0 ? $yearTo : '' ?>" class="form-control">
            </div>
            <div class="col-md-1 d-grid">
                <label class="form-label opacity-0">.</label>
                <button class="btn btn-primary">OK</button>
            </div>
        </form>

        <div class="d-flex gap-2">
            <?php if ($user && in_array($user['role'], ['admin', 'editor'], true)): ?>
                <a class="btn btn-success" href="book_form.php">+ <?= t('add_book') ?></a>
            <?php endif; ?>

            <div class="btn-group">
                <button class="btn btn-outline-secondary dropdown-toggle"
                    data-bs-toggle="dropdown"><?= t('export') ?></button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" id="exportCsv"><?= t('export_csv') ?></a></li>
                    <li><a class="dropdown-item" href="#" id="exportXlsx"><?= t('export_xlsx') ?></a></li>
                    <li><a class="dropdown-item" href="#" id="exportPdf"><?= t('export_pdf') ?></a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle" id="booksTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="cover-cell"><?= t('cover') ?></th>
                    <th><?= t('title') ?></th>
                    <th><?= t('author') ?></th>
                    <th><?= t('isbn') ?></th>
                    <th><?= t('year') ?></th>
                    <th><?= t('language') ?></th>
                    <th><?= t('genre') ?></th>
                    <th><?= t('tags') ?></th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $i => $r): ?>
                    <tr>
                        <td><?= $offset + $i + 1 ?></td>
                        <td class="cover-cell">
                            <?php if (!empty($r['cover'])): ?>
                                <img src="<?= htmlspecialchars($r['cover']) ?>" alt="" class="thumb" loading="lazy">
                            <?php else: ?>
                                <div class="thumb thumb--placeholder"></div>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td><?= htmlspecialchars($r['author']) ?></td>
                        <td><?= htmlspecialchars($r['isbn']) ?></td>
                        <td><?= htmlspecialchars($r['year']) ?></td>
                        <td><?= htmlspecialchars($bookLanguages[$r['language_code']] ?? $r['language_code']) ?></td>
                        <td><?= htmlspecialchars($r['genre_name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($r['tags']) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <?php if ($user && in_array($user['role'], ['admin', 'editor'], true)): ?>
                                    <a class="btn btn-outline-primary"
                                        href="book_form.php?id=<?= $r['id'] ?>"><?= t('edit_book') ?></a>
                                <?php endif; ?>
                                <?php if ($user && $user['role'] === 'admin'): ?>
                                    <a class="btn btn-outline-danger" href="delete_book.php?id=<?= $r['id'] ?>"
                                        onclick="return confirm('Delete?');"><?= t('delete') ?></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php $totalPages = (int) ceil($total / $perPage);
    if ($totalPages > 1): ?>
        <nav aria-label="pagination" class="mt-3">
            <ul class="pagination">
                <?php for ($p = 1; $p <= $totalPages; $p++):
                    $active = $p === $page ? 'active' : ''; ?>
                    <li class="page-item <?= $active ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
