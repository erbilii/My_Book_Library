<?php
// public/book_edit.php — edit existing book (no ISBN)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';

require_login();
$pdo = db();

// Helpers (PHP 8.1-safe)
function esc($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function dash($v): string { $t = trim((string)($v ?? '')); return $t === '' ? '—' : esc($t); }

// Languages + Genres (same as form)
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

$genres = [
  "Literary Fiction","Historical Fiction","Contemporary Fiction","Speculative Fiction","Mystery","Thriller",
  "Crime Fiction","Adventure","Romance","Fantasy","High/Epic Fantasy","Urban Fantasy","Dark Fantasy",
  "Science Fiction","Hard Science Fiction","Soft Science Fiction","Space Opera","Cyberpunk","Dystopian","Time Travel",
  "Horror","Magical Realism","Satire","Paranormal","Biography","Autobiography","Memoir","Self-Help","True Crime",
  "History","Science & Technology","Philosophy","Psychology","Business & Economics","Politics & Current Affairs",
  "Travel & Exploration","Health, Fitness & Nutrition","Spirituality & Religion","Education & Study Guides",
  "Art & Photography","Poetry","Drama","Screenplay","Picture Books","Early Reader","Middle Grade","Young Adult",
  "Children’s Fantasy","Children’s Adventure","Children’s Educational","Graphic Novels","Comics","Anthologies",
  "Short Stories","Novellas","Experimental Fiction"
];

// -------- load book --------
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "Invalid book id.";
  exit;
}

$book = $pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
$book->execute([':id' => $id]);
$book = $book->fetch(PDO::FETCH_ASSOC);
if (!$book) {
  http_response_code(404);
  echo "Book not found.";
  exit;
}

// seed form values
$title       = $book['title'] ?? '';
$author      = $book['author'] ?? '';
$year        = (string)($book['year'] ?? '');
$language    = $book['language'] ?? '';
$genre       = $book['genre'] ?? '';
$tags        = $book['tags'] ?? '';
$description = $book['description'] ?? '';
$currentCover = $book['cover_path'] ?? '';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title       = trim($_POST['title'] ?? '');
  $author      = trim($_POST['author'] ?? '');
  $year        = trim($_POST['year'] ?? '');
  $language    = $_POST['language'] ?? '';
  $genre       = $_POST['genre'] ?? '';
  $tags        = trim($_POST['tags'] ?? '');
  $description = trim($_POST['description'] ?? '');

  if ($title === '')       $errors['title'] = 'Title is required.';
  if ($author === '')      $errors['author'] = 'Author is required.';
  if ($year === '' || !preg_match('/^\d{3,4}$/', $year)) $errors['year'] = 'Enter a valid year.';
  if (!isset($bookLanguages[$language])) $errors['language'] = 'Select a valid language.';
  if (!in_array($genre, $genres, true))  $errors['genre'] = 'Select a valid genre.';
  if ($tags === '')        $errors['tags'] = 'Tags are required.';
  if ($description === '') $errors['description'] = 'Description is required.';

  // Cover upload is OPTIONAL on edit; validate only if provided
  $newCoverPath = $currentCover;
  $uploadProvided = isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE;

  if ($uploadProvided) {
    if ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
      $errors['cover'] = 'Failed to upload cover.';
    } else {
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
      $mime = mime_content_type($_FILES['cover']['tmp_name']);
      if (!isset($allowed[$mime])) {
        $errors['cover'] = 'Cover must be JPG, PNG, or WEBP.';
      }
    }
  }

  if (!$errors) {
    if ($uploadProvided) {
      $ext = $allowed[$mime];
      $dir = __DIR__ . '/uploads/covers';
      if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
      }
      $basename = bin2hex(random_bytes(8)) . '.' . $ext;
      $dest = $dir . '/' . $basename;

      if (!move_uploaded_file($_FILES['cover']['tmp_name'], $dest)) {
        $errors['cover'] = 'Could not save uploaded file.';
      } else {
        $newCoverPath = 'uploads/covers/' . $basename;

        // remove old cover file if exists and is different
        if ($currentCover && $currentCover !== $newCoverPath) {
          $old = __DIR__ . '/' . $currentCover;
          if (is_file($old)) @unlink($old);
        }
      }
    }
  }

  if (!$errors) {
    $stmt = $pdo->prepare("
      UPDATE books
      SET title = :title,
          author = :author,
          year = :year,
          language = :language,
          genre = :genre,
          tags = :tags,
          description = :description,
          cover_path = :cover_path
      WHERE id = :id
      LIMIT 1
    ");
    $ok = $stmt->execute([
      ':title'       => $title,
      ':author'      => $author,
      ':year'        => (int)$year,
      ':language'    => $language,
      ':genre'       => $genre,
      ':tags'        => $tags,
      ':description' => $description,
      ':cover_path'  => $newCoverPath,
      ':id'          => $id,
    ]);

    if ($ok) {
      header('Location: book_view.php?id='.(int)$id);
      exit;
    } else {
      $errors['general'] = 'Database error. Could not update the book.';
    }
  }
}

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h3 class="card-title mb-0">Edit Book</h3>
        <div class="d-flex gap-2">
          <a href="book_view.php?id=<?= (int)$id ?>" class="btn btn-secondary">View</a>
          <a href="dashboard.php" class="btn btn-outline-secondary">Back to list</a>
        </div>
      </div>

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger mt-3"><?= esc($errors['general']) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" class="mt-3" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control <?= isset($errors['title'])?'is-invalid':'' ?>" required value="<?= esc($title) ?>">
            <div class="invalid-feedback"><?= esc($errors['title'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Cover (leave empty to keep current)</label>
            <input type="file" name="cover" accept="image/*" class="form-control <?= isset($errors['cover'])?'is-invalid':'' ?>">
            <div class="invalid-feedback"><?= esc($errors['cover'] ?? '') ?></div>
            <?php if ($currentCover): ?>
              <div class="mt-2">
                <img src="<?= esc($currentCover) ?>" alt="Current cover" style="height:100px" class="rounded border">
              </div>
            <?php endif; ?>
          </div>

          <div class="col-md-6">
            <label class="form-label">Author</label>
            <input type="text" name="author" class="form-control <?= isset($errors['author'])?'is-invalid':'' ?>" required value="<?= esc($author) ?>">
            <div class="invalid-feedback"><?= esc($errors['author'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Year</label>
            <input type="number" name="year" min="0" max="<?= date('Y')+1 ?>" step="1"
                   class="form-control <?= isset($errors['year'])?'is-invalid':'' ?>" required
                   value="<?= esc($year) ?>">
            <div class="invalid-feedback"><?= esc($errors['year'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Language</label>
            <select name="language" class="form-select <?= isset($errors['language'])?'is-invalid':'' ?>" required>
              <?php foreach ($bookLanguages as $k=>$v): ?>
                <option value="<?= esc($k) ?>" <?= $language===$k?'selected':'' ?>><?= esc($v) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= esc($errors['language'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Genre</label>
            <select name="genre" class="form-select <?= isset($errors['genre'])?'is-invalid':'' ?>" required>
              <?php foreach ($genres as $g): ?>
                <option value="<?= esc($g) ?>" <?= $genre===$g?'selected':'' ?>><?= esc($g) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= esc($errors['genre'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Tags</label>
            <input type="text" name="tags" placeholder="comma,separated,keywords"
                   class="form-control <?= isset($errors['tags'])?'is-invalid':'' ?>" required
                   value="<?= esc($tags) ?>">
            <div class="invalid-feedback"><?= esc($errors['tags'] ?? '') ?></div>
          </div>

          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="6" class="form-control <?= isset($errors['description'])?'is-invalid':'' ?>" required><?= esc($description) ?></textarea>
            <div class="invalid-feedback"><?= esc($errors['description'] ?? '') ?></div>
          </div>
        </div>

        <div class="mt-4 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Save Changes</button>
          <a class="btn btn-secondary" href="book_view.php?id=<?= (int)$id ?>">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
