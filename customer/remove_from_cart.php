<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('customer');

$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
$stmt->bind_param("ii", $id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

flash('success', 'Item removed from cart.');
header('Location: /ecommerce/customer/cart.php');
exit;
