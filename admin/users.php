<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');


if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    if ($id !== (int)$_SESSION['user_id']) { 
        $stmt = $conn->prepare("UPDATE users SET status = IF(status='active','blocked','active') WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('success', 'User status updated.');
    } else {
        flash('error', "You can't block your own account.");
    }
    header('Location: /ecommerce/admin/users.php');
    exit;
}

$roleFilter = $_GET['role'] ?? '';

if ($roleFilter !== '') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
    $stmt->bind_param("s", $roleFilter);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
}

$pageTitle = "Manage Users";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-between" style="margin-bottom:16px;">
    <h2 class="section-title" style="margin-bottom:0;">Manage Users</h2>
    <form method="GET">
        <select name="role" onchange="this.form.submit()">
            <option value="">All Roles</option>
            <option value="admin" <?= $roleFilter==='admin'?'selected':'' ?>>Admin</option>
            <option value="seller" <?= $roleFilter==='seller'?'selected':'' ?>>Seller</option>
            <option value="customer" <?= $roleFilter==='customer'?'selected':'' ?>>Customer</option>
            <option value="delivery" <?= $roleFilter==='delivery'?'selected':'' ?>>Delivery</option>
        </select>
    </form>
</div>

<div class="table-wrap">
<table>
<thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Joined</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($users as $u): ?>
    <tr>
        <td><?= escape($u['name']) ?></td>
        <td><?= escape($u['email']) ?></td>
        <td><?= escape(ucfirst($u['role'])) ?></td>
        <td><?= escape($u['phone']) ?></td>
        <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
        <td><span class="badge badge-<?= $u['status'] ?>"><?= escape($u['status']) ?></span></td>
        <td>
            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                <a href="?toggle=<?= $u['id'] ?>&role=<?= escape($roleFilter) ?>"
                   class="btn btn-sm <?= $u['status']==='active' ? 'btn-danger' : 'btn-primary' ?>"
                   data-confirm="<?= $u['status']==='active' ? 'Block' : 'Unblock' ?> this user?">
                    <?= $u['status']==='active' ? 'Block' : 'Unblock' ?>
                </a>
            <?php else: ?>
                <span style="color:var(--muted); font-size:12px;">You</span>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
