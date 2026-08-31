<?php
require_once __DIR__ . '/../includes/functions.php';

$search = trim($_GET['q'] ?? '');
$categoryId = $_GET['category'] ?? '';

$sql = "SELECT p.*, c.name AS category_name FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.status = 'active'";
$types = "";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $types .= "ss";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($categoryId !== '') {
    $sql .= " AND p.category_id = ?";
    $types .= "i";
    $params[] = $categoryId;
}
$sql .= " ORDER BY p.created_at DESC";




$stmt = $conn->prepare($sql);
if (!empty($params)) {
    bindParams($stmt, $types, $params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);

$pageTitle = "Shop";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="hero">
    <h1>Welcome to ShopEase</h1>
    <p>Quality products from trusted local sellers — delivered to your door.</p>
</div>

<div class="flex-between mt-20" style="margin-bottom:18px;">
    <h2 class="section-title" style="margin-bottom:0;">
        <?= $search !== '' ? 'Search results for "' . escape($search) . '"' : 'All Products' ?>
    </h2>
    <form method="GET" style="display:flex; gap:8px;">
        <?php if ($search !== ''): ?><input type="hidden" name="q" value="<?= escape($search) ?>"><?php endif; ?>
        <select name="category" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                    <?= escape($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($products)): ?>
    <div class="empty-state">No products found. Try a different search or category.</div>
<?php else: ?>
<div class="product-grid">
    <?php foreach ($products as $p): ?>
    <div class="product-card">
        <a href="/ecommerce/customer/product.php?id=<?= $p['id'] ?>">
            <img src="/ecommerce/assets/uploads/<?= escape($p['image']) ?>"
                 onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
        </a>
        <div class="info">
            <span style="font-size:11px; color:var(--muted);"><?= escape($p['category_name'] ?? 'Uncategorized') ?></span>
            <h3><a href="/ecommerce/customer/product.php?id=<?= $p['id'] ?>"><?= escape($p['name']) ?></a></h3>
            <div class="price"><?= money($p['price']) ?></div>
            <div class="stock"><?= $p['stock'] > 0 ? $p['stock'] . ' in stock' : 'Out of stock' ?></div>
            <div class="actions">
                <a href="/ecommerce/customer/product.php?id=<?= $p['id'] ?>" class="btn btn-outline btn-sm">View</a>
                <?php if ($user['role'] === 'customer' && $p['stock'] > 0): ?>
                <form method="POST" action="/ecommerce/customer/add_to_cart.php" style="flex:1;">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="quantity" value="1">
                    <input type="hidden" name="redirect" value="shop">
                    <button type="submit" class="btn btn-primary btn-sm btn-block">Add to Cart</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
