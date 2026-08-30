<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('admin');

$totalUsers = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$totalSellers = $conn->query("SELECT COUNT(*) c FROM users WHERE role='seller'")->fetch_assoc()['c'];
$totalCustomers = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$totalProducts = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$totalOrders = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$totalRevenue = $conn->query("SELECT COALESCE(SUM(total_amount),0) r FROM orders WHERE order_status != 'cancelled'")->fetch_assoc()['r'];
$unassigned = $conn->query("SELECT COUNT(*) c FROM orders WHERE delivery_status = 'unassigned'")->fetch_assoc()['c'];

$recentOrders = $conn->query("SELECT o.*, u.name AS customer_name FROM orders o
                              JOIN users u ON o.customer_id = u.id
                              ORDER BY o.created_at DESC LIMIT 6")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">Admin Dashboard</h2>

<div class="stats-grid">
    <div class="stat-card"><div class="num"><?= $totalUsers ?></div><div class="label">Total Users</div></div>
    <div class="stat-card"><div class="num"><?= $totalSellers ?></div><div class="label">Sellers</div></div>
    <div class="stat-card"><div class="num"><?= $totalCustomers ?></div><div class="label">Customers</div></div>
    <div class="stat-card"><div class="num"><?= $totalProducts ?></div><div class="label">Total Products</div></div>
    <div class="stat-card"><div class="num"><?= $totalOrders ?></div><div class="label">Total Orders</div></div>
    <div class="stat-card"><div class="num"><?= money($totalRevenue) ?></div><div class="label">Total Revenue</div></div>
    <div class="stat-card"><div class="num"><?= $unassigned ?></div><div class="label">Orders Needing Delivery Agent</div></div>
</div>

<div class="flex-between mt-20" style="margin-bottom:16px;">
    <h3>Recent Orders</h3>
    <a href="/ecommerce/admin/orders.php" class="btn btn-primary btn-sm">Manage All Orders</a>
</div>

<div class="table-wrap">
<table>
<thead><tr><th>Order</th><th>Customer</th><th>Amount</th><th>Status</th><th>Delivery</th><th>Date</th></tr></thead>
<tbody>
<?php foreach ($recentOrders as $o): ?>
    <tr>
        <td>#<?= $o['id'] ?></td>
        <td><?= escape($o['customer_name']) ?></td>
        <td><?= money($o['total_amount']) ?></td>
        <td><span class="badge badge-<?= $o['order_status'] ?>"><?= escape($o['order_status']) ?></span></td>
        <td><span class="badge badge-<?= $o['delivery_status'] ?>"><?= escape(str_replace('_',' ',$o['delivery_status'])) ?></span></td>
        <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
