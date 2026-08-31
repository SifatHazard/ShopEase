<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('delivery');

$agentId = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT o.*, u.name AS customer_name, u.phone AS customer_phone
                        FROM orders o JOIN users u ON o.customer_id = u.id
                        WHERE o.delivery_agent_id = ?
                        ORDER BY FIELD(o.delivery_status,'assigned','accepted','out_for_delivery','failed','delivered','rejected'), o.created_at DESC");
$stmt->bind_param("i", $agentId);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = "My Deliveries";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Assigned Deliveries</h2>

<?php if (empty($orders)): ?>
    <div class="empty-state">No deliveries assigned to you yet.</div>
<?php else: ?>

<div class="table-wrap">
<table>
<thead><tr><th>Order</th><th>Customer</th><th>Phone</th><th>Address</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach ($orders as $o): ?>
    <tr>
        <td>#<?= $o['id'] ?></td>
        <td><?= escape($o['customer_name']) ?></td>
        <td><?= escape($o['customer_phone']) ?></td>
        <td style="max-width:200px;"><?= escape($o['shipping_address']) ?></td>
        <td><?= money($o['total_amount']) ?></td>
        <td>
            <span class="badge badge-<?= $o['delivery_status'] ?>"><?= escape(str_replace('_',' ',$o['delivery_status'])) ?></span>
            <?php if ($o['delivery_note']): ?>
                <div style="font-size:11px; color:var(--danger); margin-top:4px;">Note: <?= escape($o['delivery_note']) ?></div>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($o['delivery_status'] === 'assigned'): ?>
                <form method="POST" action="/ecommerce/delivery/update_status.php" style="display:flex; gap:6px;">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <button type="submit" name="action" value="accept" class="btn btn-primary btn-sm">Accept</button>
                    <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm" data-confirm="Reject this delivery? Admin will need to reassign it.">Reject</button>
                </form>

            <?php elseif ($o['delivery_status'] === 'accepted'): ?>
                <form method="POST" action="/ecommerce/delivery/update_status.php">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <button type="submit" name="action" value="out_for_delivery" class="btn btn-primary btn-sm">Start Delivery</button>
                </form>

            <?php elseif ($o['delivery_status'] === 'out_for_delivery'): ?>
                <form method="POST" action="/ecommerce/delivery/update_status.php" style="display:flex; flex-direction:column; gap:6px;">
                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                    <input type="text" name="otp" placeholder="Enter OTP from customer" style="padding:5px; font-size:12px; border:1px solid var(--border); border-radius:5px;">
                    <div style="display:flex; gap:6px;">
                        <button type="submit" name="action" value="delivered" class="btn btn-primary btn-sm">Mark Delivered</button>
                        <button type="submit" name="action" value="failed" class="btn btn-danger btn-sm" data-confirm="Report this delivery as failed?">Failed</button>
                    </div>
                </form>

            <?php else: ?>
                <span style="color:var(--muted); font-size:12px;">No action needed</span>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
