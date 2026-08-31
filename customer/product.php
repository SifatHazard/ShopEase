<?php
require_once __DIR__ . '/../includes/functions.php';

$id = $_GET['id'] ?? 0;

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name, u.name AS seller_name
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        LEFT JOIN users u ON p.seller_id = u.id
                        WHERE p.id = ? AND p.status = 'active'");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    flash('error', 'Product not found.');
    header('Location: /ecommerce/customer/shop.php');
    exit;
}

$pageTitle = $product['name'];
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display:grid; grid-template-columns: 1fr 1.2fr; gap:30px;">
    <div>
        <img src="/ecommerce/assets/uploads/<?= escape($product['image']) ?>"
             onerror="this.src='https://via.placeholder.com/500x400?text=No+Image'"
             style="width:100%; border-radius: var(--radius); border:1px solid var(--border);">
    </div>
    <div>
        <span style="font-size:12px; color:var(--muted);"><?= escape($product['category_name'] ?? 'Uncategorized') ?></span>
        <h1 style="margin:6px 0;"><?= escape($product['name']) ?></h1>
        <p style="color:var(--muted); font-size:13px; margin-bottom:10px;">Sold by <?= escape($product['seller_name']) ?></p>
        <div class="price" style="font-size:26px; margin-bottom:14px;"><?= money($product['price']) ?></div>
        <p style="margin-bottom:16px;"><?= nl2br(escape($product['description'])) ?></p>
        <p class="stock" style="margin-bottom:16px;">
            <?= $product['stock'] > 0 ? $product['stock'] . ' units in stock' : 'Currently out of stock' ?>
        </p>

        <?php if ($user['role'] === 'customer' && $product['stock'] > 0): ?>
        <form method="POST" action="/ecommerce/customer/add_to_cart.php" style="display:flex; gap:10px; align-items:center;">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <input type="hidden" name="redirect" value="product">
            <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock'] ?>" class="qty-input">
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
        <?php elseif (!isLoggedIn()): ?>
            <a href="/ecommerce/auth/login.php" class="btn btn-primary">Login to Buy</a>
        <?php elseif ($product['stock'] <= 0): ?>
            <button class="btn btn-outline" disabled>Out of Stock</button>
        <?php endif; ?>

        <div class="mt-20">
            <a href="/ecommerce/customer/shop.php" class="btn btn-outline btn-sm">&larr; Back to Shop</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
