<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../csrf.php';

if (current_user()) { header('Location: ' . url('/books.php')); exit; }
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if ($pass === '') $errors[] = 'Password is required.';

    if (!$errors) {
        $pdo = db();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pass, $u['password_hash'])) {
            $_SESSION['user'] = ['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email']];
            header('Location: ' . url('/books.php'));
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<?php include __DIR__ . '/partials/header.php'; ?>
<div class="row justify-content-center">
  <div class="col-md-6 col-lg-5">
    <h1 class="mb-3">Log in</h1>
    <?php if ($errors): ?>
      <div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $e) echo '<li>'.esc($e).'</li>'; ?></ul></div>
    <?php endif; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= esc(csrf_token()) ?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" value="<?= esc($email) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
      </div>
      <button class="btn btn-primary w-100">Log in</button>
      <p class="mt-3 text-center">No account? <a href="<?= url('/register.php') ?>">Create one</a></p>
    </form>
  </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>
