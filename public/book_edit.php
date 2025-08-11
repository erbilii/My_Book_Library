<?php
// public/book_edit.php — edit an existing book (role-gated)

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';

require_login();
$me = current_user();
if (($me['role'] ?? 'viewer') === 'viewer') {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$pdo = db();

// Helpers
function esc($v): string { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function showv($v): string { $t = trim((string)($v ?? '')); return $t === '' ? '' : esc($t); }

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

// Get book ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Invalid book id.";
    exit;
}

// Fetch book
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$book) {
    http_response_code(404);
    echo "Book not found.";
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $author      = trim($_POST['author'] ?? '');
    $isbn        = trim($_POST['isbn'] ?? '');
    $year        = trim($_POST['year'] ?? '');
    $language    = trim($_POST['language'] ?? '');
    $genre       = trim($_POST['genre'] ?? '');
    $tags        = trim($_POST['tags'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $cover_path  = $book['cover_path'];

    // Upload cover if provided
    if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($ext, $allowed)) {
            $newName = 'uploads/' . uniqid('cover_', true) . '.' . $ext;
            if (move_uploaded_file($_FILES['cover']['tmp_name'], __DIR__ . '/' . $newName)) {
                $cover_path = $newName;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE books SET 
        title = :title,
        author = :author,
        isbn = :isbn,
        year = :year,
        language = :language,
        genre = :genre,
        tags = :tags,
        description = :description,
        cover_path = :cover_path
        WHERE id = :id
    ");
    $stmt->execute([
        ':title'       => $title,
        ':author'      => $author,
        ':isbn'        => $isbn,
        ':year'        => $year,
        ':language'    => $language,
        ':genre'       => $genre,
        ':tags'        => $tags,
        ':description' => $description,
        ':cover_path'  => $cover_path,
        ':id'          => $id
    ]);

    header("Location: book_view.php?id=" . $id);
    exit;
}

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container my-4">
    <h2>Edit Book</h2>
    <form method="post" enctype="multipart/form-data" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= showv($book['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Author</label>
            <input type="text" name="author" class="form-control" value="<?= showv($book['author']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control" value="<?= showv($book['isbn']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" value="<?= showv($book['year']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Language</label>
            <select name="language" class="form-select">
                <option value="">—</option>
                <?php foreach ($bookLanguages as $code => $name): ?>
                    <option value="<?= esc($code) ?>" <?= ($book['language'] === $code) ? 'selected' : '' ?>>
                        <?= esc($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Genre</label>
            <input type="text" name="genre" class="form-control" value="<?= showv($book['genre']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Tags (comma separated)</label>
            <input type="text" name="tags" class="form-control" value="<?= showv($book['tags']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" rows="5" class="form-control"><?= showv($book['description']) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Cover</label>
            <?php if (!empty($book['cover_path']) && file_exists(__DIR__ . '/' . $book['cover_path'])): ?>
                <div class="mb-2">
                    <img src="<?= esc($book['cover_path']) ?>" alt="Cover" style="max-height:150px;">
                </div>
            <?php endif; ?>
            <input type="file" name="cover" class="form-control">
        </div>
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Save</button>
            <a href="book_view.php?id=<?= (int)$book['id'] ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
