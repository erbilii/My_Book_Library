<?php
// public/book_form.php

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

$genres = [
    "Literary Fiction",
    "Historical Fiction",
    "Contemporary Fiction",
    "Speculative Fiction",
    "Mystery",
    "Thriller",
    "Crime Fiction",
    "Adventure",
    "Romance",
    "Fantasy",
    "High/Epic Fantasy",
    "Urban Fantasy",
    "Dark Fantasy",
    "Science Fiction",
    "Hard Science Fiction",
    "Soft Science Fiction",
    "Space Opera",
    "Cyberpunk",
    "Dystopian",
    "Time Travel",
    "Horror",
    "Magical Realism",
    "Satire",
    "Paranormal",
    "Biography",
    "Autobiography",
    "Memoir",
    "Self-Help",
    "True Crime",
    "History",
    "Science & Technology",
    "Philosophy",
    "Psychology",
    "Business & Economics",
    "Politics & Current Affairs",
    "Travel & Exploration",
    "Health, Fitness & Nutrition",
    "Spirituality & Religion",
    "Education & Study Guides",
    "Art & Photography",
    "Poetry",
    "Drama",
    "Screenplay",
    "Picture Books",
    "Early Reader",
    "Middle Grade",
    "Young Adult",
    "Children’s Fantasy",
    "Children’s Adventure",
    "Children’s Educational",
    "Graphic Novels",
    "Comics",
    "Anthologies",
    "Short Stories",
    "Novellas",
    "Experimental Fiction"
];

$errors = [];
$title = $author = $year = $language = $genre = $tags = $description = "";
$coverPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $language = $_POST['language'] ?? '';
    $genre = $_POST['genre'] ?? '';
    $tags = trim($_POST['tags'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '')
        $errors['title'] = 'Title is required.';
    if ($author === '')
        $errors['author'] = 'Author is required.';
    if ($year === '' || !preg_match('/^\d{3,4}$/', $year))
        $errors['year'] = 'Enter a valid year.';
    if (!isset($bookLanguages[$language]))
        $errors['language'] = 'Select a valid language.';
    if (!in_array($genre, $genres, true))
        $errors['genre'] = 'Select a valid genre.';
    if ($tags === '')
        $errors['tags'] = 'Tags are required.';
    if ($description === '')
        $errors['description'] = 'Description is required.';

    // Cover upload (required)
    if (!isset($_FILES['cover']) || $_FILES['cover']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors['cover'] = 'Cover image is required.';
    } elseif ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
        $errors['cover'] = 'Failed to upload cover.';
    } else {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $mime = mime_content_type($_FILES['cover']['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors['cover'] = 'Cover must be JPG, PNG, or WEBP.';
        }
    }

    if (!$errors) {
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
            $coverPath = 'uploads/covers/' . $basename;

            $stmt = $pdo->prepare("
        INSERT INTO books
          (title, author, isbn, year, language, genre, tags, description, cover_path, created_at)
        VALUES
          (:title, :author, NULL, :year, :language, :genre, :tags, :description, :cover_path, NOW())
      ");
            $ok = $stmt->execute([
                ':title' => $title,
                ':author' => $author,
                ':year' => (int) $year,
                ':language' => $language,
                ':genre' => $genre,
                ':tags' => $tags,
                ':description' => $description,
                ':cover_path' => $coverPath,
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
                <div class="alert alert-danger"><?= htmlspecialchars($errors['general']) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title"
                            class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" required
                            value="<?= htmlspecialchars($title) ?>">
                        <div class="invalid-feedback"><?= $errors['title'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Cover</label>
                        <input type="file" name="cover" accept="image/*"
                            class="form-control <?= isset($errors['cover']) ? 'is-invalid' : '' ?>" required>
                        <div class="invalid-feedback"><?= $errors['cover'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Author</label>
                        <input type="text" name="author"
                            class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" required
                            value="<?= htmlspecialchars($author) ?>">
                        <div class="invalid-feedback"><?= $errors['author'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Year</label>
                        <input type="number" name="year" min="0" max="<?= date('Y') + 1 ?>" step="1"
                            class="form-control <?= isset($errors['year']) ? 'is-invalid' : '' ?>" required
                            value="<?= htmlspecialchars($year) ?>">
                        <div class="invalid-feedback"><?= $errors['year'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Language</label>
                        <select name="language" class="form-select <?= isset($errors['language']) ? 'is-invalid' : '' ?>"
                            required>
                            <?php foreach ($bookLanguages as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>" <?= $language === $k ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $errors['language'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Genre</label>
                        <select name="genre" class="form-select <?= isset($errors['genre']) ? 'is-invalid' : '' ?>"
                            required>
                            <option value="" disabled <?= $genre === '' ? 'selected' : '' ?>>—</option>
                            <?php foreach ($genres as $g): ?>
                                <option value="<?= htmlspecialchars($g) ?>" <?= $genre === $g ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback"><?= $errors['genre'] ?? '' ?></div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Tags</label>
                        <input type="text" name="tags" placeholder="comma,separated,keywords"
                            class="form-control <?= isset($errors['tags']) ? 'is-invalid' : '' ?>" required
                            value="<?= htmlspecialchars($tags) ?>">
                        <div class="invalid-feedback"><?= $errors['tags'] ?? '' ?></div>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="6"
                            class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>"
                            required><?= htmlspecialchars($description) ?></textarea>
                        <div class="invalid-feedback"><?= $errors['description'] ?? '' ?></div>
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