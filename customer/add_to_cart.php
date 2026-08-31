<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('customer');

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$redirect = $_POST['redirect'] ?? 'shop';

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
$stmt->bind_param("i", $productId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    flash('error', 'Product not available.');
} elseif ($product['stock'] < $quantity) {
    flash('error', 'Not enough stock available.');
} else {
    
    $check = $conn->prepare("SELECT * FROM cart WHERE customer_id = ? AND product_id = ?");
    $check->bind_param("ii", $_SESSION['user_id'], $productId);
    $check->execute();
    $existing = $check->get_result()->fetch_assoc();
    $check->close();

    if ($existing) {
        $newQty = min($product['stock'], $existing['quantity'] + $quantity);
        $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $upd->bind_param("ii", $newQty, $existing['id']);
        $upd->execute();
        $upd->close();
    } else {
        $ins = $conn->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $ins->bind_param("iii", $_SESSION['user_id'], $productId, $quantity);
        $ins->execute();
        $ins->close();
    }
    flash('success', 'Added to cart!');
}

if ($redirect === 'product') {
    header('Location: /ecommerce/customer/product.php?id=' . $productId);
} else {
    header('Location: /ecommerce/customer/shop.php');
}
exit;
