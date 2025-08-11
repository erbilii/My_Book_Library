<?php
// public/book_form.php — Add Book (uses language_code column)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';

require_login();
$pdo = db();

// PHP 8.1–safe helpers
function esc($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

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

$errors = [];
$title = $author = $year = $language_code = $genre = $tags = $description = "";
$coverPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title         = trim($_POST['title'] ?? '');
  $author        = trim($_POST['author'] ?? '');
  $year          = trim($_POST['year'] ?? '');
  $language_code = $_POST['language_code'] ?? '';
  $genre         = $_POST['genre'] ?? '';
  $tags          = trim($_POST['tags'] ?? '');
  $description   = trim($_POST['description'] ?? '');

  if ($title === '')       $errors['title'] = 'Title is required.';
  if ($author === '')      $errors['author'] = 'Author is required.';
  if ($year === '' || !preg_match('/^\d{3,4}$/', $year)) $errors['year'] = 'Enter a valid year.';
  if (!isset($bookLanguages[$language_code])) $errors['language_code'] = 'Select a valid language.';
  if (!in_array($genre, $genres, true))  $errors['genre'] = 'Select a valid genre.';
  if ($tags === '')        $errors['tags'] = 'Tags are required.';
  if ($description === '') $errors['description'] = 'Description is required.';

  // Cover upload (required)
  if (!isset($_FILES['cover']) || $_FILES['cover']['error'] === UPLOAD_ERR_NO_FILE) {
    $errors['cover'] = 'Cover image is required.';
  } elseif ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
    $errors['cover'] = 'Failed to upload cover.';
  } else {
    $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    $mime = @mime_content_type($_FILES['cover']['tmp_name']);
    if (!isset($allowed[$mime])) {
      $errors['cover'] = 'Cover must be JPG, PNG, or WEBP.';
    }
  }

  if (!$errors) {
    $ext = $allowed[$mime];
    $dir = __DIR__ . '/uploads/covers';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);

    $basename = bin2hex(random_bytes(8)) . '.' . $ext;
    $dest = $dir . '/' . $basename;

    if (!move_uploaded_file($_FILES['cover']['tmp_name'], $dest)) {
      $errors['cover'] = 'Could not save uploaded file.';
    } else {
      $coverPath = 'uploads/covers/' . $basename;

      // NOTE: write to language_code, and ISBN is stored as NULL (not used)
      $stmt = $pdo->prepare("
        INSERT INTO books
          (title, author, isbn, year, language_code, genre, tags, description, cover_path, created_at)
        VALUES
          (:title, :author, NULL, :year, :language_code, :genre, :tags, :description, :cover_path, NOW())
      ");
      $ok = $stmt->execute([
        ':title'         => $title,
        ':author'        => $author,
        ':year'          => (int)$year,
        ':language_code' => $language_code,
        ':genre'         => $genre,
        ':tags'          => $tags,
        ':description'   => $description,
        ':cover_path'    => $coverPath,
      ]);

      if ($ok) {
        header('Location: dashboard.php?msg=book_added');
        exit;
      } else {
        $errors['general'] = 'Database error. Could not save the book.';
      }
    }
  }
}

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container my-4">
  <div class="card shadow-sm">
    <div class="card-body">
      <h3 class="card-title mb-3">Add Book</h3>

      <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-danger"><?= esc($errors['general']) ?></div>
      <?php endif; ?>

      <form method="post" enctype="multipart/form-data" novalidate>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control <?= isset($errors['title'])?'is-invalid':'' ?>" required value="<?= esc($title) ?>">
            <div class="invalid-feedback"><?= esc($errors['title'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Cover</label>
            <input type="file" name="cover" accept="image/*" class="form-control <?= isset($errors['cover'])?'is-invalid':'' ?>" required>
            <div class="invalid-feedback"><?= esc($errors['cover'] ?? '') ?></div>
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
            <select name="language_code" class="form-select <?= isset($errors['language_code'])?'is-invalid':'' ?>" required>
              <?php foreach ($bookLanguages as $k=>$v): ?>
                <option value="<?= esc($k) ?>" <?= $language_code===$k?'selected':'' ?>><?= esc($v) ?></option>
              <?php endforeach; ?>
            </select>
            <div class="invalid-feedback"><?= esc($errors['language_code'] ?? '') ?></div>
          </div>

          <div class="col-md-6">
            <label class="form-label">Genre</label>
            <select name="genre" class="form-select <?= isset($errors['genre'])?'is-invalid':'' ?>" required>
              <option value="" disabled <?= $genre===''?'selected':'' ?>>—</option>
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
          <button class="btn btn-primary" type="submit">Save</button>
          <a class="btn btn-secondary" href="dashboard.php">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/partials/footer.php'; ?>
