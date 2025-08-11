<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';
require_login();
require_role(['admin', 'editor']);
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

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$book = [
    'title' => '',
    'author' => '',
    'isbn' => '',
    'year' => '',
    'language_code' => 'en',
    'genre_id' => '',
    'tags' => '',
    'description' => ''
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM books WHERE id=?');
    $stmt->execute([$id]);
    $book = $stmt->fetch() ?: $book;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title'] ?? ''),
        'author' => trim($_POST['author'] ?? ''),
        'isbn' => trim($_POST['isbn'] ?? ''),
        'year' => (int) ($_POST['year'] ?? 0),
        'language_code' => $_POST['language_code'] ?? 'en',
        'genre_id' => ($_POST['genre_id'] ?? '') !== '' ? (int) $_POST['genre_id'] : null,
        'tags' => trim($_POST['tags'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
    ];

    if ($isEdit) {
        $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, isbn=?, year=?, language_code=?, genre_id=?, tags=?, description=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$data['title'], $data['author'], $data['isbn'], $data['year'], $data['language_code'], $data['genre_id'], $data['tags'], $data['description'], $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO books(title,author,isbn,year,language_code,genre_id,tags,description) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute([$data['title'], $data['author'], $data['isbn'], $data['year'], $data['language_code'], $data['genre_id'], $data['tags'], $data['description']]);
    }
    header('Location: /public/dashboard.php');
    exit;
}

$genres = $pdo->query('SELECT id,name FROM genres ORDER BY name')->fetchAll();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h5 mb-3"><?= $isEdit ? t('edit_book') : t('add_book') ?></h1>
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><?= t('title') ?></label>
                    <input name="title" class="form-control" required value="<?= htmlspecialchars($book['title']) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= t('author') ?></label>
                    <input name="author" class="form-control" required value="<?= htmlspecialchars($book['author']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= t('isbn') ?></label>
                    <input name="isbn" class="form-control" value="<?= htmlspecialchars($book['isbn']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= t('year') ?></label>
                    <input type="number" name="year" class="form-control"
                        value="<?= htmlspecialchars($book['year']) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= t('language') ?></label>
                    <select name="language_code" class="form-select">
                        <?php foreach ($bookLanguages as $code => $label): ?>
                            <option value="<?= $code ?>" <?= $book['language_code'] === $code ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= t('genre') ?></label>
                    <select name="genre_id" class="form-select">
                        <option value="">—</option>
                        <?php foreach ($genres as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= (int) $book['genre_id'] === (int) $g['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= t('tags') ?></label>
                    <input name="tags" class="form-control" value="<?= htmlspecialchars($book['tags']) ?>"
                        placeholder="comma,separated,keywords">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= t('description') ?></label>
                    <textarea name="description" class="form-control"
                        rows="4"><?= htmlspecialchars($book['description']) ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-primary"><?= t('save') ?></button>
                    <a class="btn btn-secondary" href="/public/dashboard.php"><?= t('cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>