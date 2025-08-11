<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';
require_login();
require_role(['admin']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $pass = $_POST['password'] ?? '';
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
        $stmt->execute([$name, $email, $hash, $role]);
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $pass = $_POST['password'] ?? '';
        if ($pass !== '') {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?');
            $stmt->execute([$name, $email, $role, $hash, $id]);
        } else {
            $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
            $stmt->execute([$name, $email, $role, $id]);
        }
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
        $stmt->execute([$id]);
    }
    header('Location: /public/users.php');
    exit;
}

$users = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll();
include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 m-0"><?= t('users') ?></h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userModal">+ New</button>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th><?= t('name') ?></th>
                    <th>Email</th>
                    <th><?= t('role') ?></th>
                    <th>Created</th>
                    <th><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['role']) ?></td>
                        <td><?= htmlspecialchars($u['created_at']) ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#editModal<?= $u['id'] ?>">Edit</button>
                            <form method="post" class="d-inline" onsubmit="return confirm('Delete?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal<?= $u['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <form method="post" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit User</h5><button type="button" class="btn-close"
                                        data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <div class="mb-2"><label class="form-label">Name</label><input name="name"
                                            class="form-control" value="<?= htmlspecialchars($u['name']) ?>" required></div>
                                    <div class="mb-2"><label class="form-label">Email</label><input name="email"
                                            type="email" class="form-control" value="<?= htmlspecialchars($u['email']) ?>"
                                            required></div>
                                    <div class="mb-2"><label class="form-label">Role</label>
                                        <select name="role" class="form-select">
                                            <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                            <option value="viewer" <?= $u['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                                        </select>
                                    </div>
                                    <div class="mb-2"><label class="form-label">New Password (optional)</label><input
                                            name="password" type="password" class="form-control"></div>
                                </div>
                                <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New User</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-2"><label class="form-label">Name</label><input name="name" class="form-control"
                            required></div>
                    <div class="mb-2"><label class="form-label">Email</label><input name="email" type="email"
                            class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="viewer">Viewer</option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label">Password</label><input name="password" type="password"
                            class="form-control" required></div>
                </div>
                <div class="modal-footer"><button class="btn btn-primary">Create</button></div>
            </form>
        </div>
    </div>
</div>
<?php include __DIR__ . '/partials/footer.php'; ?>