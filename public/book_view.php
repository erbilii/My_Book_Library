<?php
// public/book_view.php — show a single book in detail (single Back button)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';

require_login();
$pdo = db();

// Helpers (PHP 8.1 safe)
function esc($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function show($v): string { $t = trim((string)($v ?? '')); return $t === '' ? '—' : esc($t); }

// Get id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(400); echo "Invalid book id."; exit; }

// Fetch book
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) { http_response_code(404); echo "Book not found."; exit; }

$bookLanguages = [
  'ckb'=>'Kurdish','en'=>'English','ar'=>'Arabic','fa'=>'Persian','tr'=>'Turkish',
  'fr'=>'French','de'=>'German','es'=>'Spanish','ru'=>'Russian','zh'=>'Chinese'
];

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container my-4">
  <div class="row g-4 align-items-start">
    <div class="col-md-4 col-lg-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <?php $coverExists = !empty($book['cover_path']) && file_exists(__DIR__ . '/' . $book['cover_path']); ?>
          <?php if ($coverExists): ?>
            <img src="<?= esc($book['cover_path']) ?>" alt="Cover" class="img-fluid rounded"
                 style="max-height:520px; object-fit:contain;">
          <?php else: ?>
            <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height:520px;">
              No Cover
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-md-8 col-lg-9">
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <h2 class="card-title mb-1"><?= show($book['title']) ?></h2>
            <div class="d-flex gap-2">
              <a href="book_edit.php?id=<?= (int)$book['id'] ?>" class="btn btn-primary">Edit</a>
              <a href="dashboard.php" class="btn btn-secondary">Back</a>
            </div>
          </div>

          <hr>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="small text-muted">Author</div>
              <div class="fs-5"><?= show($book['author']) ?></div>
            </div>

            <?php if (!empty($book['isbn'])): ?>
            <div class="col-md-6">
              <div class="small text-muted">ISBN</div>
              <div class="fs-5"><?= show($book['isbn']) ?></div>
            </div>
            <?php endif; ?>

            <div class="col-md-3">
              <div class="small text-muted">Year</div>
              <div class="fs-5"><?= show($book['year']) ?></div>
            </div>

            <div class="col-md-3">
              <div class="small text-muted">Language</div>
              <div class="fs-5">
                <?= show($bookLanguages[$book['language'] ?? ''] ?? ($book['language'] ?? '')) ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="small text-muted">Genre</div>
              <div class="fs-5"><?= show($book['genre']) ?></div>
            </div>

            <div class="col-12">
              <div class="small text-muted">Tags</div>
              <div class="fs-6">
                <?php
                  $tags = array_filter(array_map('trim', explode(',', (string)($book['tags'] ?? ''))));
                  if ($tags) {
                    foreach ($tags as $tg) {
                      echo '<span class="badge text-bg-secondary me-1 mb-1">'.esc($tg).'</span>';
                    }
                  } else {
                    echo '—';
                  }
                ?>
              </div>
            </div>

            <div class="col-12">
              <div class="small text-muted mb-1">Description</div>
              <p class="mb-0"><?= nl2br(show($book['description'])) ?></p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
