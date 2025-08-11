<?php
// public/dashboard.php — list books with filters, safe escaping, role-based actions

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';

require_login();
$pdo = db();

// ---- user role (viewer/editor/admin) ----
$me = current_user();                  // expects ['role' => 'admin'|'editor'|'viewer', ...]
$userRole = $me['role'] ?? 'viewer';   // default to viewer if missing

$bookLanguages = [
  'ckb' => 'Kurdish',
  'en'  => 'English',
  'ar'  => 'Arabic',
  'fa'  => 'Persian',
  'tr'  => 'Turkish',
  'fr'  => 'French',
  'de'  => 'German',
  'es'  => 'Spanish',
  'ru'  => 'Russian',
  'zh'  => 'Chinese'
];

// Helpers: escape & show-dash (avoid null warnings on PHP 8.1+)
function esc($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function showdash($v): string { $t = trim((string)($v ?? '')); return $t === '' ? '—' : esc($t); }

// ---------- filters ----------
$q        = trim($_GET['q'] ?? '');
$lang     = $_GET['lang'] ?? '';
$genre    = trim($_GET['genre'] ?? '');
$yFrom    = trim($_GET['yearFrom'] ?? '');
$yTo      = trim($_GET['yearTo'] ?? '');
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 20;

$where = [];
$params = [];

if ($q !== '') {
  $where[] = "(title LIKE :q OR author LIKE :q OR tags LIKE :q)";
  $params[':q'] = "%$q%";
}
if ($lang && isset($bookLanguages[$lang])) {
  $where[] = "language = :lang";
  $params[':lang'] = $lang;
}
if ($genre !== '') {
  $where[] = "genre = :genre";
  $params[':genre'] = $genre;
}
if ($yFrom !== '' && ctype_digit($yFrom)) {
  $where[] = "year >= :yf";
  $params[':yf'] = (int)$yFrom;
}
if ($yTo !== '' && ctype_digit($yTo)) {
  $where[] = "year <= :yt";
  $params[':yt'] = (int)$yTo;
}
$whereSql = $where ? ('WHERE '.implode(' AND ', $where)) : '';

// Count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM books $whereSql");
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();
$pages = max(1, (int)ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// Distinct genres for filter (skip null/empty)
$genreRows = $pdo->query("SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre")->fetchAll(PDO::FETCH_COLUMN);

// Fetch books
$sql = "SELECT * FROM books $whereSql ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $k=>$v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>

<div class="container my-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
    <h3 class="m-0">Books</h3>
    <div class="d-flex gap-2">
      <?php if ($userRole !== 'viewer'): ?>
        <a href="book_form.php" class="btn btn-success">+ Add Book</a>
      <?php endif; ?>

      <div class="btn-group">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          Export
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#" id="exportCsv">CSV</a></li>
          <li><a class="dropdown-item" href="#" id="exportXlsx">Excel</a></li>
          <li><a class="dropdown-item" href="#" id="exportPdf">PDF</a></li>
        </ul>
      </div>
    </div>
  </div>

  <form class="row g-2 mb-3" method="get">
    <div class="col-md-3">
      <input type="text" class="form-control" name="q" placeholder="Title / Author / Tags" value="<?= esc($q) ?>">
    </div>

    <div class="col-md-2">
      <select name="lang" class="form-select">
        <option value="">—</option>
        <?php foreach ($bookLanguages as $k=>$v): ?>
          <option value="<?= esc($k) ?>" <?= $lang===$k?'selected':'' ?>><?= esc($v) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-3">
      <select name="genre" class="form-select">
        <option value="">—</option>
        <?php foreach ($genreRows as $g): ?>
          <option value="<?= esc($g) ?>" <?= $genre===$g?'selected':'' ?>><?= esc($g) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2">
      <input type="number" class="form-control" name="yearFrom" placeholder="Year (from)" value="<?= esc($yFrom) ?>">
    </div>
    <div class="col-md-2">
      <div class="input-group">
        <input type="number" class="form-control" name="yearTo" placeholder="Year (to)" value="<?= esc($yTo) ?>">
        <button class="btn btn-primary">OK</button>
      </div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table align-middle" id="booksTable">
      <thead>
        <tr>
          <th>#</th>
          <th>cover</th>
          <th>Title</th>
          <th>Author</th>
          <th>Year</th>
          <th>language</th>
          <th>Genre</th>
          <th>Tags</th>
          <?php if ($userRole !== 'viewer'): ?>
            <th>Actions</th>
          <?php endif; ?>
        </tr>
      </thead>
      <tbody>
      <?php if (!$books): ?>
        <tr><td colspan="<?= $userRole !== 'viewer' ? '10' : '9' ?>" class="text-center text-muted py-4">No books found.</td></tr>
      <?php endif; ?>

      <?php foreach ($books as $idx => $b): ?>
        <tr data-href="book_view.php?id=<?= (int)$b['id'] ?>">
          <td><?= ($offset + $idx + 1) ?></td>
          <td class="cover-cell">
            <a href="book_view.php?id=<?= (int)$b['id'] ?>">
              <?php if (!empty($b['cover_path'])): ?>
                <img src="<?= esc($b['cover_path']) ?>" class="thumb" alt="cover">
              <?php else: ?>
                <span class="thumb thumb--placeholder"></span>
              <?php endif; ?>
            </a>
          </td>
          <td>
            <a href="book_view.php?id=<?= (int)$b['id'] ?>" class="fw-semibold text-decoration-none">
              <?= showdash($b['title']) ?>
            </a>
          </td>
          <td><?= showdash($b['author']) ?></td>
          <td><?= showdash($b['year']) ?></td>
          <td>
            <?php
              $lk = $b['language'] ?? '';
              echo showdash($bookLanguages[$lk] ?? $lk);
            ?>
          </td>
          <td><?= showdash($b['genre']) ?></td>
          <td><?= showdash($b['tags']) ?></td>
          <?php if ($userRole !== 'viewer'): ?>
            <td class="text-nowrap">
              <a class="btn btn-sm btn-outline-primary" href="book_edit.php?id=<?= (int)$b['id'] ?>">Edit<br>Book</a>
              <form class="d-inline" method="post" action="book_delete.php" onsubmit="return confirm('Delete this book?')">
                <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
                <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
              </form>
            </td>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if ($pages > 1): ?>
    <nav class="mt-3">
      <ul class="pagination">
        <?php for ($p=1; $p <= $pages; $p++): ?>
          <li class="page-item <?= $p===$page?'active':'' ?>">
            <a class="page-link"
               href="?<?= http_build_query(['q'=>$q,'lang'=>$lang,'genre'=>$genre,'yearFrom'=>$yFrom,'yearTo'=>$yTo,'page'=>$p]) ?>">
              <?= $p ?>
            </a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
