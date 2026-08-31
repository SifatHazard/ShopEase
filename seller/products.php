<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');

$sellerId = $_SESSION['user_id'];


if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("ii", $id, $sellerId);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Product deleted.');
    } catch (mysqli_sql_exception $e) {
        flash('error', 'This product could not be deleted because it is linked to existing orders. Try setting it to Inactive instead.');
    }
    header('Location: /ecommerce/seller/products.php');
    exit;
}

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "My Products";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="flex-between" style="margin-bottom:16px;">
    <h2 class="section-title" style="margin-bottom:0;">My Products</h2>
    <a href="/ecommerce/seller/add_product.php" class="btn btn-primary">+ Add New Product</a>
</div>

<div class="table-wrap">
<table>
<thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php if (empty($products)): ?>
    <tr><td colspan="7" class="empty-state">You haven't added any products yet.</td></tr>
<?php else: foreach ($products as $p): ?>
    <tr>
        <td><img src="/ecommerce/assets/uploads/<?= escape($p['image']) ?>" onerror="this.src='https://via.placeholder.com/50'" style="width:44px;height:44px;object-fit:cover;border-radius:6px;"></td>
        <td><?= escape($p['name']) ?></td>
        <td><?= escape($p['category_name'] ?? '-') ?></td>
        <td><?= money($p['price']) ?></td>
        <td><?= $p['stock'] ?></td>
        <td><span class="badge badge-<?= $p['status'] ?>"><?= escape($p['status']) ?></span></td>
        <td style="display:flex; gap:6px;">
            <a href="/ecommerce/seller/edit_product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">Edit</a>
            <a href="/ecommerce/seller/products.php?delete=<?= $p['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Delete this product permanently?">Delete</a>
        </td>
    </tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
