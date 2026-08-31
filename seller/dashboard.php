<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');

$sellerId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) c FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$productCount = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(DISTINCT order_id) c, COALESCE(SUM(quantity*price),0) revenue
                        FROM order_items WHERE seller_id = ?");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$orderStats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) c FROM order_items WHERE seller_id = ? AND item_status = 'pending'");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$pendingItems = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) c FROM products WHERE seller_id = ? AND stock <= 5");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$lowStock = $stmt->get_result()->fetch_assoc()['c'];
$stmt->close();

$stmt = $conn->prepare("SELECT oi.*, o.created_at, o.id AS order_id FROM order_items oi
                        JOIN orders o ON oi.order_id = o.id
                        WHERE oi.seller_id = ? ORDER BY o.created_at DESC LIMIT 5");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$recentItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Seller Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Welcome, <?= escape($user['name']) ?></h2>

<div class="stats-grid">
    <div class="stat-card"><div class="num"><?= $productCount ?></div><div class="label">My Products</div></div>
    <div class="stat-card"><div class="num"><?= $orderStats['c'] ?></div><div class="label">Orders Received</div></div>
    <div class="stat-card"><div class="num"><?= money($orderStats['revenue']) ?></div><div class="label">Total Revenue</div></div>
    <div class="stat-card"><div class="num"><?= $pendingItems ?></div><div class="label">Pending Shipments</div></div>
    <div class="stat-card"><div class="num"><?= $lowStock ?></div><div class="label">Low Stock (&le;5)</div></div>
</div>

<div class="flex-between mt-20" style="margin-bottom:16px;">
    <h3>Recent Order Items</h3>
    <a href="/ecommerce/seller/products.php" class="btn btn-primary btn-sm">+ Add / Manage Products</a>
</div>

<div class="table-wrap">
<table>
<thead><tr><th>Order</th><th>Product</th><th>Qty</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
<tbody>
<?php if (empty($recentItems)): ?>
    <tr><td colspan="6" class="empty-state">No orders yet.</td></tr>
<?php else: foreach ($recentItems as $it): ?>
    <tr>
        <td>#<?= $it['order_id'] ?></td>
        <td><?= escape($it['product_name']) ?></td>
        <td><?= $it['quantity'] ?></td>
        <td><?= money($it['quantity'] * $it['price']) ?></td>
        <td><span class="badge badge-<?= $it['item_status'] ?>"><?= escape($it['item_status']) ?></span></td>
        <td><?= date('d M Y', strtotime($it['created_at'])) ?></td>
    </tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
