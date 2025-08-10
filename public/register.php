<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

if (current_user()) { header('Location: ' . url('/books.php')); exit; }
$errors = [];
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($name === '') $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($pass !== $pass2) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)'); 
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user'] = ['id' => $pdo->lastInsertId(), 'name' => $name, 'email' => $email];
            header('Location: ' . url('/books.php'));
            exit;
        }
    }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="mb-3">Create your account</h1>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.esc($e).'</li>'; ?></ul></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input class="form-control" name="name" value="<?= esc($name) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= esc($email) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
        <div class="form-text">At least 6 characters.</div>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" class="form-control" name="password2" required>
      </div>
      <button class="btn btn-primary w-100">Sign up</button>
      <p class="mt-3 text-center">Already have an account? <a href="<?= url('/login.php') ?>">Log in</a></p>
    </form>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
