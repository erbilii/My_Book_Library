<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../i18n.php';
require_login();
require_role(['admin']);
$pdo = db();

// Handle create/update/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $pass = $_POST['password'] ?? '';
        if ($name && $email && $pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users(name,email,password_hash,role) VALUES(?,?,?,?)');
            $stmt->execute([$name, $email, $hash, $role]);
        }
    } elseif ($action === 'update') {
        $id = (int) $_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'viewer';
        $pass = $_POST['password'] ?? '';
        if ($id > 0) {
            if ($pass !== '') {
                $hash = password_hash($pass, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=?, password_hash=? WHERE id=?');
                $stmt->execute([$name, $email, $role, $hash, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?');
                $stmt->execute([$name, $email, $role, $id]);
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) $_POST['id'];
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
            $stmt->execute([$id]);
        }
    }
    header('Location: users.php');
    exit;
}

$users = $pdo->query('SELECT id,name,email,role,created_at FROM users ORDER BY created_at DESC')->fetchAll();

include __DIR__ . '/partials/header.php';
include __DIR__ . '/partials/nav.php';
?>
<style>
    .role-badge {
        text-transform: capitalize;
        padding: .35rem .5rem;
        border-radius: .5rem;
    }

    .role-badge.admin {
        background: #fee2e2;
        color: #991b1b;
    }

    .role-badge.editor {
        background: #e0e7ff;
        color: #1e3a8a;
    }

    .role-badge.viewer {
        background: #e5e7eb;
        color: #111827;
    }

    .table td,
    .table th {
        vertical-align: middle;
    }
</style>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 m-0"><?= t('users') ?></h1>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#userCreateModal">+ New</button>
    </div>

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th style="width:70px">#</th>
                    <th><?= t('name') ?></th>
                    <th>Email</th>
                    <th style="width:140px"><?= t('role') ?></th>
                    <th style="width:220px">Created</th>
                    <th style="width:200px"><?= t('actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span
                                class="badge role-badge <?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($u['role']) ?></span>
                        </td>
                        <td><small class="text-muted"><?= htmlspecialchars($u['created_at']) ?></small></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                    data-bs-target="#userEditModal<?= $u['id'] ?>">Edit</button>
                                <form method="post" onsubmit="return confirm('Delete this user?')" class="d-inline">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Modal (outside the table) -->
<div class="modal fade" id="userCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" value="create">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label"><?= t('name') ?></label>
                        <input name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input name="email" type="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= t('role') ?></label>
                        <select name="role" class="form-select">
                            <option value="admin">Admin</option>
                            <option value="editor">Editor</option>
                            <option value="viewer" selected>Viewer</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input name="password" type="password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php foreach ($users as $u): ?>
    <!-- Edit Modal (outside the table) -->
    <div class="modal fade" id="userEditModal<?= $u['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <form method="post" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label"><?= t('name') ?></label>
                            <input name="name" class="form-control" value="<?= htmlspecialchars($u['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input name="email" type="email" class="form-control"
                                value="<?= htmlspecialchars($u['email']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?= t('role') ?></label>
                            <select name="role" class="form-select">
                                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="editor" <?= $u['role'] === 'editor' ? 'selected' : '' ?>>Editor</option>
                                <option value="viewer" <?= $u['role'] === 'viewer' ? 'selected' : '' ?>>Viewer</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password (optional)</label>
                            <input name="password" type="password" class="form-control"
                                placeholder="Leave blank to keep current">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>

<?php include __DIR__ . '/partials/footer.php'; ?>