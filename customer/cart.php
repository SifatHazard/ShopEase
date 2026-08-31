<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('customer');


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $cartId => $qty) {
        $cartId = (int)$cartId;
        $qty = max(1, (int)$qty);

        
        $stmt = $conn->prepare("SELECT c.id, p.stock FROM cart c JOIN products p ON c.product_id = p.id
                                WHERE c.id = ? AND c.customer_id = ?");
        $stmt->bind_param("ii", $cartId, $_SESSION['user_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            $qty = min($qty, $row['stock']);
            $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $upd->bind_param("ii", $qty, $cartId);
            $upd->execute();
            $upd->close();
        }
    }
    flash('success', 'Cart updated.');
    header('Location: /ecommerce/customer/cart.php');
    exit;
}

$stmt = $conn->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.image, p.stock
                        FROM cart c JOIN products p ON c.product_id = p.id
                        WHERE c.customer_id = ?
                        ORDER BY c.created_at DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];

$pageTitle = "My Cart";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Cart</h2>

<?php if (empty($items)): ?>
    <div class="empty-state">
        Your cart is empty. <a href="/ecommerce/customer/shop.php" class="btn btn-primary mt-20">Browse Products</a>
    </div>
<?php else: ?>
<form method="POST" action="">
<div class="table-wrap">
<table>
<thead>
<tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th></th></tr>
</thead>
<tbody>
<?php foreach ($items as $item): ?>
<tr data-price="<?= $item['price'] ?>">
    <td style="display:flex; align-items:center; gap:10px;">
        <img src="/ecommerce/assets/uploads/<?= escape($item['image']) ?>" onerror="this.src='https://via.placeholder.com/50'" style="width:50px; height:50px; object-fit:cover; border-radius:6px;">
        <?= escape($item['name']) ?>
    </td>
    <td><?= money($item['price']) ?></td>
    <td>
        <input type="number" class="qty-input" name="qty[<?= $item['cart_id'] ?>]" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>">
    </td>
    <td class="line-total"><?= money($item['price'] * $item['quantity']) ?></td>
    <td>
        <a href="/ecommerce/customer/remove_from_cart.php?id=<?= $item['cart_id'] ?>"
           class="btn btn-danger btn-sm" data-confirm="Remove this item from cart?">Remove</a>
    </td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<div class="flex-between mt-20">
    <button type="submit" name="update_cart" class="btn btn-outline">Update Cart</button>
    <div style="text-align:right;">
        <div style="font-size:20px; font-weight:700;">Total: <?= money($total) ?></div>
        <a href="/ecommerce/customer/checkout.php" class="btn btn-primary mt-20">Proceed to Checkout</a>
    </div>
</div>
</form>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
