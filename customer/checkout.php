<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('customer');

$stmt = $conn->prepare("SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock, p.seller_id
                        FROM cart c JOIN products p ON c.product_id = p.id
                        WHERE c.customer_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($items)) {
    flash('error', 'Your cart is empty.');
    header('Location: /ecommerce/customer/cart.php');
    exit;
}

$total = 0;
foreach ($items as $item) $total += $item['price'] * $item['quantity'];

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = trim($_POST['shipping_address'] ?? '');
    $payment = $_POST['payment_method'] ?? 'COD';

    if ($address === '') {
        $errors[] = "Shipping address is required.";
    }

    
    foreach ($items as $item) {
        if ($item['quantity'] > $item['stock']) {
            $errors[] = "Sorry, \"{$item['name']}\" only has {$item['stock']} left in stock.";
        }
    }

    if (empty($errors)) {
        
        $conn->begin_transaction();
        try {
            $orderStmt = $conn->prepare("INSERT INTO orders (customer_id, total_amount, shipping_address, payment_method, order_status, delivery_status)
                                         VALUES (?, ?, ?, ?, 'confirmed', 'unassigned')");
            $orderStmt->bind_param("idss", $_SESSION['user_id'], $total, $address, $payment);
            $orderStmt->execute();
            $orderId = $conn->insert_id;
            $orderStmt->close();

            $itemStmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, seller_id, product_name, quantity, price)
                                        VALUES (?, ?, ?, ?, ?, ?)");
            $stockStmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

            foreach ($items as $item) {
                $itemStmt->bind_param("iiisid", $orderId, $item['product_id'], $item['seller_id'], $item['name'], $item['quantity'], $item['price']);
                $itemStmt->execute();

                $stockStmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $stockStmt->execute();
            }
            $itemStmt->close();
            $stockStmt->close();

            $clearCart = $conn->prepare("DELETE FROM cart WHERE customer_id = ?");
            $clearCart->bind_param("i", $_SESSION['user_id']);
            $clearCart->execute();
            $clearCart->close();

            $conn->commit();

            flash('success', "Order #$orderId placed successfully! You can track it under My Orders.");
            header('Location: /ecommerce/customer/orders.php');
            exit;

        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Something went wrong while placing your order. Please try again.";
        }
    }
}

$pageTitle = "Checkout";
require_once __DIR__ . '/../includes/header.php';


$u = $conn->prepare("SELECT address FROM users WHERE id = ?");
$u->bind_param("i", $_SESSION['user_id']);
$u->execute();
$savedAddress = $u->get_result()->fetch_assoc()['address'] ?? '';
$u->close();
?>

<h2 class="section-title">Checkout</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?php foreach ($errors as $e) echo escape($e) . "<br>"; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1.3fr 1fr; gap:24px;">
    <div class="form-card wide" style="margin:0;">
        <h3 style="margin-bottom:16px;">Delivery Details</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Shipping Address</label>
                <textarea name="shipping_address" rows="3" required><?= escape($_POST['shipping_address'] ?? $savedAddress) ?></textarea>
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="payment_method">
                    <option value="COD">Cash on Delivery</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Place Order</button>
        </form>
    </div>

    <div class="table-wrap" style="height:fit-content;">
        <table>
            <thead><tr><th>Item</th><th>Qty</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= escape($item['name']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= money($item['price'] * $item['quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight:700;">
                <td colspan="2">Total</td>
                <td><?= money($total) ?></td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
