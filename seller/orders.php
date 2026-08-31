<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');

$sellerId = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['item_id'])) {
    $itemId = (int)$_POST['item_id'];
    $newStatus = $_POST['new_status'];
    if (in_array($newStatus, ['pending', 'shipped', 'delivered'])) {
        $stmt = $conn->prepare("UPDATE order_items SET item_status = ? WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("sii", $newStatus, $itemId, $sellerId);
        $stmt->execute();
        $stmt->close();
        flash('success', 'Order item status updated.');
    }
    header('Location: /ecommerce/seller/orders.php');
    exit;
}

$stmt = $conn->prepare("SELECT oi.*, o.created_at, o.shipping_address, o.id AS order_id, u.name AS customer_name, u.phone AS customer_phone
                        FROM order_items oi
                        JOIN orders o ON oi.order_id = o.id
                        JOIN users u ON o.customer_id = u.id
                        WHERE oi.seller_id = ?
                        ORDER BY o.created_at DESC");
$stmt->bind_param("i", $sellerId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "Orders";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Orders for My Products</h2>

<div class="table-wrap">
<table>
<thead><tr><th>Order</th><th>Product</th><th>Qty</th><th>Amount</th><th>Customer</th><th>Ship To</th><th>Status</th><th>Update</th></tr></thead>
<tbody>
<?php if (empty($items)): ?>
    <tr><td colspan="8" class="empty-state">No orders yet.</td></tr>
<?php else: foreach ($items as $it): ?>
    <tr>
        <td>#<?= $it['order_id'] ?></td>
        <td><?= escape($it['product_name']) ?></td>
        <td><?= $it['quantity'] ?></td>
        <td><?= money($it['quantity'] * $it['price']) ?></td>
        <td><?= escape($it['customer_name']) ?><br><small style="color:var(--muted)"><?= escape($it['customer_phone']) ?></small></td>
        <td style="max-width:180px;"><?= escape($it['shipping_address']) ?></td>
        <td><span class="badge badge-<?= $it['item_status'] ?>"><?= escape($it['item_status']) ?></span></td>
        <td>
            <form method="POST" action="" style="display:flex; gap:6px;">
                <input type="hidden" name="item_id" value="<?= $it['id'] ?>">
                <select name="new_status">
                    <option value="pending" <?= $it['item_status']==='pending'?'selected':'' ?>>Pending</option>
                    <option value="shipped" <?= $it['item_status']==='shipped'?'selected':'' ?>>Shipped</option>
                    <option value="delivered" <?= $it['item_status']==='delivered'?'selected':'' ?>>Delivered</option>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Save</button>
            </form>
        </td>
    </tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
