<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Category added.');
    }
    header('Location: /ecommerce/admin/products.php');
    exit;
}


if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("UPDATE products SET status = IF(status='active','inactive','active') WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', 'Product status updated.');
    header('Location: /ecommerce/admin/products.php');
    exit;
}


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Product deleted.');
    } catch (mysqli_sql_exception $e) {
        flash('error', 'This product could not be deleted because it is linked to existing data. Try deactivating it instead.');
    }
    header('Location: /ecommerce/admin/products.php');
    exit;
}

$products = $conn->query("SELECT p.*, c.name AS category_name, u.name AS seller_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          LEFT JOIN users u ON p.seller_id = u.id
                          ORDER BY p.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Manage Products";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Manage Products &amp; Categories</h2>

<div style="display:grid; grid-template-columns: 1fr 320px; gap:24px; align-items:start;">
    <div class="table-wrap">
    <table>
    <thead><tr><th>Image</th><th>Name</th><th>Seller</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr>
            <td><img src="/ecommerce/assets/uploads/<?= escape($p['image']) ?>" onerror="this.src='https://via.placeholder.com/44'" style="width:44px;height:44px;object-fit:cover;border-radius:6px;"></td>
            <td><?= escape($p['name']) ?></td>
            <td><?= escape($p['seller_name']) ?></td>
            <td><?= escape($p['category_name'] ?? '-') ?></td>
            <td><?= money($p['price']) ?></td>
            <td><?= $p['stock'] ?></td>
            <td><span class="badge badge-<?= $p['status'] ?>"><?= escape($p['status']) ?></span></td>
            <td style="display:flex; gap:6px;">
                <a href="?toggle=<?= $p['id'] ?>" class="btn btn-outline btn-sm"><?= $p['status']==='active' ? 'Deactivate' : 'Activate' ?></a>
                <a href="?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Delete this product permanently?">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>

    <div class="form-card" style="margin:0;">
        <h3 style="margin-bottom:14px;">Add Category</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="category_name" required>
            </div>
            <button type="submit" name="add_category" class="btn btn-primary btn-block">Add Category</button>
        </form>
        <hr style="margin:16px 0; border-color:var(--border);">
        <h4 style="margin-bottom:10px; font-size:14px;">Existing Categories</h4>
        <ul style="list-style:none; font-size:14px; color:var(--muted);">
            <?php foreach ($categories as $c): ?>
                <li style="padding:4px 0;">• <?= escape($c['name']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
