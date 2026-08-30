<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $orderId = (int)$_POST['order_id'];
    $agentId = $_POST['agent_id'];

    if ($agentId === '') {
        flash('error', 'Please select a delivery agent.');
    } else {
        $agentId = (int)$agentId;
        $stmt = $conn->prepare("UPDATE orders SET delivery_agent_id = ?, delivery_status = 'assigned' WHERE id = ?");
        $stmt->bind_param("ii", $agentId, $orderId);
        $stmt->execute();
        $stmt->close();
        flash('success', "Order #$orderId assigned to delivery agent.");
    }
    header('Location: /ecommerce/admin/orders.php');
    exit;
}


if (isset($_GET['cancel'])) {
    $id = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE orders SET order_status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    flash('success', "Order #$id cancelled.");
    header('Location: /ecommerce/admin/orders.php');
    exit;
}

$orders = $conn->query("SELECT o.*, u.name AS customer_name, d.name AS agent_name
                        FROM orders o
                        JOIN users u ON o.customer_id = u.id
                        LEFT JOIN users d ON o.delivery_agent_id = d.id
                        ORDER BY o.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$agents = $conn->query("SELECT id, name FROM users WHERE role = 'delivery' AND status = 'active' ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Manage Orders";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Manage Orders</h2>

<div class="table-wrap">
<table>
<thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Delivery Agent</th><th>Delivery Status</th><th>Assign</th><th></th></tr></thead>
<tbody>
<?php if (empty($orders)): ?>
    <tr><td colspan="8" class="empty-state">No orders yet.</td></tr>
<?php else: foreach ($orders as $o): ?>
    <tr>
        <td>#<?= $o['id'] ?></td>
        <td><?= escape($o['customer_name']) ?></td>
        <td><?= money($o['total_amount']) ?></td>
        <td><span class="badge badge-<?= $o['order_status'] ?>"><?= escape($o['order_status']) ?></span></td>
        <td><?= $o['agent_name'] ? escape($o['agent_name']) : '<span style="color:var(--muted)">Unassigned</span>' ?></td>
        <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= escape(str_replace('_',' ',$o['delivery_status'])) ?></span></td>
        <td>
            <?php if ($o['order_status'] !== 'cancelled'): ?>
            <form method="POST" action="" style="display:flex; gap:6px;">
                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                <select name="agent_id">
                    <option value="">Select agent</option>
                    <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= $o['delivery_agent_id']==$a['id']?'selected':'' ?>><?= escape($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Assign</button>
            </form>
            <?php endif; ?>
        </td>
        <td>
            <?php if ($o['order_status'] !== 'cancelled'): ?>
            <a href="?cancel=<?= $o['id'] ?>" class="btn btn-danger btn-sm" data-confirm="Cancel this order?">Cancel</a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
