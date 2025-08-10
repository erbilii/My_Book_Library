<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';
require_login();

$pdo = db();
$user = current_user();
$id = (int)($_GET['id'] ?? 0);
$editing = $id > 0;

$book = [
  'title' => '',
  'author' => '',
  'language' => '',
  'genre' => '',
  'isbn' => '',
  'published_year' => '',
  'pages' => '',
  'notes' => '',
  'cover_path' => ''
];

if ($editing) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $user['id']]);
    $book = $stmt->fetch();
    if (!$book) { http_response_code(404); die('Book not found.'); }
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $book['title'] = trim($_POST['title'] ?? '');
    $book['author'] = trim($_POST['author'] ?? '');
    $book['language'] = trim($_POST['language'] ?? '');
    $book['genre'] = trim($_POST['genre'] ?? '');
    $book['isbn'] = trim($_POST['isbn'] ?? '');
    $book['published_year'] = (int)($_POST['published_year'] ?? 0);
    $book['pages'] = (int)($_POST['pages'] ?? 0);
    $book['notes'] = trim($_POST['notes'] ?? '');

    if ($book['title'] === '') $errors[] = 'Title is required.';

    // Handle cover upload
    if (!empty($_FILES['cover']['name'])) {
        $f = $_FILES['cover'];
        if ($f['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp','gif'];
            if (!in_array($ext, $allowed)) {
                $errors[] = 'Cover must be an image (jpg, png, webp, gif).';
            } elseif ($f['size'] > 2*1024*1024) {
                $errors[] = 'Cover image must be <= 2MB.';
            } else {
                $new = uniqid('cover_', true) . '.' . $ext;
                $dest = __DIR__ . '/uploads/' . $new;
                if (!move_uploaded_file($f['tmp_name'], $dest)) {
                    $errors[] = 'Failed to upload cover.';
                } else {
                    $book['cover_path'] = $new;
                }
            }
        } else {
            $errors[] = 'Upload error code: ' . (int)$f['error'];
        }
    }

    if (!$errors) {
        if ($editing) {
            $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, language=?, genre=?, isbn=?, published_year=?, pages=?, notes=?, cover_path=IF(?<>\'\',?,cover_path) WHERE id=? AND user_id=?');
            $stmt->execute([$book['title'],$book['author'],$book['language'],$book['genre'],$book['isbn'],$book['published_year']?:null,$book['pages']?:null,$book['notes'],$book['cover_path'] ?? '',$book['cover_path'] ?? '',$id,$user['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO books (user_id, title, author, language, genre, isbn, published_year, pages, notes, cover_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$user['id'],$book['title'],$book['author'],$book['language'],$book['genre'],$book['isbn'],$book['published_year']?:null,$book['pages']?:null,$book['notes'],$book['cover_path'] ?? null]);
            $id = $pdo->lastInsertId();
            $editing = true;
        }
        header('Location: ' . url('/books.php'));
        exit;
    }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<h1 class="mb-3"><?= $editing ? 'Edit Book' : 'Add Book' ?></h1>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.esc($e).'</li>'; ?></ul></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-8">
      <div class="mb-3">
        <label class="form-label">Title *</label>
        <input class="form-control" name="title" value="<?= esc($book['title']) ?>" required>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Author</label>
          <input class="form-control" name="author" value="<?= esc($book['author']) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Language</label>
          <input class="form-control" name="language" value="<?= esc($book['language']) ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Genre</label>
          <input class="form-control" name="genre" value="<?= esc($book['genre']) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">ISBN</label>
          <input class="form-control" name="isbn" value="<?= esc($book['isbn']) ?>">
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Published Year</label>
          <input type="number" class="form-control" name="published_year" value="<?= esc($book['published_year']) ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Pages</label>
          <input type="number" class="form-control" name="pages" value="<?= esc($book['pages']) ?>">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Notes</label>
        <textarea class="form-control" rows="4" name="notes"><?= esc($book['notes']) ?></textarea>
      </div>
    </div>
    <div class="col-md-4">
      <div class="mb-3">
        <label class="form-label">Cover (max 2MB)</label>
        <input class="form-control" type="file" name="cover" accept="image/*">
        <?php if (!empty($book['cover_path'])): ?>
          <div class="mt-2">
            <img src="<?= url('/uploads/' . esc($book['cover_path'])) ?>" alt="" class="img-fluid rounded">
          </div>
        <?php endif; ?>
      </div>
      <div class="d-grid gap-2">
        <button class="btn btn-primary"><?= $editing ? 'Save changes' : 'Add book' ?></button>
        <a class="btn btn-outline-secondary" href="<?= url('/books.php') ?>">Cancel</a>
      </div>
    </div>
  </div>
</form>

<?php include __DIR__ . '/partials/footer.php'; ?>
