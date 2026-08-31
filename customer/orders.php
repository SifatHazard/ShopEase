<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('customer');

$stmt = $conn->prepare("SELECT * FROM orders WHERE customer_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");

$pageTitle = "My Orders";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Orders</h2>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        You haven't placed any orders yet. <a href="/ecommerce/customer/shop.php" class="btn btn-primary mt-20">Start Shopping</a>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
        <?php
        $itemStmt->bind_param("i", $order['id']);
        $itemStmt->execute();
        $lineItems = $itemStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        ?>
        <div class="table-wrap mt-20">
            <div class="flex-between" style="padding:14px 16px; border-bottom:1px solid var(--border);">
                <div>
                    <strong>Order #<?= $order['id'] ?></strong>
                    <span style="color:var(--muted); font-size:13px;"> — <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?></span>
                </div>
                <div style="display:flex; gap:8px;">
                    <span class="badge badge-<?= $order['order_status'] ?>"><?= escape($order['order_status']) ?></span>
                    <span class="badge badge-<?= $order['delivery_status'] ?>">Delivery: <?= escape(str_replace('_',' ',$order['delivery_status'])) ?></span>
                </div>
            </div>
            <?php if ($order['delivery_otp'] && $order['delivery_status'] !== 'delivered'): ?>
                <div class="otp otp-info otp-persistent" style="margin:14px 16px; padding:10px 14px;">
                    Your delivery code (give this to your delivery agent to confirm handoff):
                    <strong style="font-size:16px; letter-spacing:2px;"><?= escape($order['delivery_otp']) ?></strong>
                </div>
            <?php endif; ?>
            <table>
                <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($lineItems as $li): ?>
                    <tr>
                        <td><?= escape($li['product_name']) ?></td>
                        <td><?= $li['quantity'] ?></td>
                        <td><?= money($li['price']) ?></td>
                        <td><span class="badge badge-<?= $li['item_status'] ?>"><?= escape($li['item_status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div style="padding:12px 16px; display:flex; justify-content:space-between; font-size:14px;">
                <span>Shipping to: <?= escape($order['shipping_address']) ?></span>
                <strong>Total: <?= money($order['total_amount']) ?></strong>
            </div>
        </div>
    <?php endforeach; ?>
    <?php $itemStmt->close(); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
