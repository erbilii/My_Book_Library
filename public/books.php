<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../auth.php';
require_login();

$pdo = db();
$user = current_user();
$q = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Count
$params = [$user['id']];
$where = 'WHERE user_id = ?';
if ($q !== '') {
    $where .= ' AND (title LIKE ? OR author LIKE ? OR isbn LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$user['id'], $like, $like, $like];
}
$count = $pdo->prepare("SELECT COUNT(*) AS c FROM books $where");
$count->execute($params);
$total = (int)$count->fetchColumn();

list($page, $pages) = paginate($total, $page, $per_page);
$offset = ($page - 1) * $per_page;

$list = $pdo->prepare("SELECT * FROM books $where ORDER BY updated_at DESC LIMIT $per_page OFFSET $offset");
$list->execute($params);
$books = $list->fetchAll();
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="d-flex align-items-center justify-content-between mb-3">
  <h1 class="h3 mb-0">My Books</h1>
  <a class="btn btn-success" href="<?= url('/book_form.php') ?>">+ Add Book</a>
</div>

<form class="row g-2 mb-3" method="get">
  <div class="col-9 col-sm-10">
    <input name="q" class="form-control" placeholder="Search by title, author, or ISBN" value="<?= esc($q) ?>">
  </div>
  <div class="col-3 col-sm-2 d-grid">
    <button class="btn btn-outline-secondary">Search</button>
  </div>
</form>

<div class="table-responsive">
<table class="table table-hover align-middle">
  <thead>
    <tr>
      <th style="width:56px">Cover</th>
      <th>Title</th>
      <th>Author</th>
      <th class="d-none d-md-table-cell">Language</th>
      <th class="d-none d-md-table-cell">Year</th>
      <th class="actions-col">Actions</th>
    </tr>
  </thead>
  <tbody>
  <?php foreach ($books as $b): ?>
    <tr>
      <td><?php if ($b['cover_path']): ?><img class="cover-thumb" src="<?= url('/uploads/' . esc($b['cover_path'])) ?>" alt=""><?php endif; ?></td>
      <td>
        <strong><?= esc($b['title']) ?></strong><br>
        <small class="text-muted"><?= esc($b['genre']) ?></small>
      </td>
      <td><?= esc($b['author']) ?></td>
      <td class="d-none d-md-table-cell"><?= esc($b['language']) ?></td>
      <td class="d-none d-md-table-cell"><?= esc($b['published_year']) ?></td>
      <td>
        <a class="btn btn-sm btn-outline-primary" href="<?= url('/book_form.php?id=' . (int)$b['id']) ?>">Edit</a>
        <a class="btn btn-sm btn-outline-danger" href="<?= url('/book_delete.php?id=' . (int)$b['id']) ?>" onclick="return confirm('Delete this book?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$books): ?>
    <tr><td colspan="6" class="text-center text-muted">No books found.</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php if ($pages > 1): ?>
<nav aria-label="Page navigation">
  <ul class="pagination">
    <li class="page-item <?= $page<=1?'disabled':'' ?>">
      <a class="page-link" href="<?= url('/books.php?q='.urlencode($q).'&page='.max(1,$page-1)) ?>">Prev</a>
    </li>
    <?php for ($i=1;$i<=$pages;$i++): ?>
      <li class="page-item <?= $i==$page?'active':'' ?>">
        <a class="page-link" href="<?= url('/books.php?q='.urlencode($q).'&page='.$i) ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= $page>=$pages?'disabled':'' ?>">
      <a class="page-link" href="<?= url('/books.php?q='.urlencode($q).'&page='.min($pages,$page+1)) ?>">Next</a>
    </li>
  </ul>
</nav>
<?php endif; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>
