<?php
require_once __DIR__ . '/functions.php';
$user = currentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? escape($pageTitle) . ' - ShopEase' : 'ShopEase' ?></title>
<link rel="stylesheet" href="/ecommerce/assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="navbar-inner">
        <a href="/ecommerce/index.php" class="logo">Shop<span>Ease</span></a>

        <?php if ($user['role'] === 'customer'): ?>
        <form class="nav-search" action="/ecommerce/customer/shop.php" method="GET">
            <input type="text" name="q" placeholder="Search products..." value="<?= escape($_GET['q'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>
        <?php endif; ?>

        <nav class="nav-links">
            <?php if (!$user['id']): ?>
                <a href="/ecommerce/auth/login.php">Login</a>
                <a href="/ecommerce/auth/register.php" class="btn-nav">Register</a>
            <?php else: ?>
                <?php if ($user['role'] === 'admin'): ?>
                    <a href="/ecommerce/admin/dashboard.php">Dashboard</a>
                    <a href="/ecommerce/admin/products.php">Products</a>
                    <a href="/ecommerce/admin/orders.php">Orders</a>
                    <a href="/ecommerce/admin/users.php">Users</a>
                <?php elseif ($user['role'] === 'seller'): ?>
                    <a href="/ecommerce/seller/dashboard.php">Dashboard</a>
                    <a href="/ecommerce/seller/products.php">My Products</a>
                    <a href="/ecommerce/seller/orders.php">Orders</a>
                <?php elseif ($user['role'] === 'customer'): ?>
                    <a href="/ecommerce/customer/shop.php">Shop</a>
                    <a href="/ecommerce/customer/cart.php">Cart
                        <?php
                        global $conn;
                        $c = cartCount($conn, $user['id']);
                        if ($c > 0) echo "<span class='cart-badge'>$c</span>";
                        ?>
                    </a>
                    <a href="/ecommerce/customer/orders.php">My Orders</a>
                <?php elseif ($user['role'] === 'delivery'): ?>
                    <a href="/ecommerce/delivery/dashboard.php">My Deliveries</a>
                <?php endif; ?>
                <span class="nav-user">Hi, <?= escape($user['name']) ?></span>
                <a href="/ecommerce/auth/logout.php" class="btn-nav-outline">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page-wrap">
<?php if ($flash): ?>
    <div class="alert alert-<?= escape($flash['type']) ?>"><?= escape($flash['message']) ?></div>
<?php endif; ?>
