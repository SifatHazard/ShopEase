<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');

$sellerId = $_SESSION['user_id'];
$id = (int)($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->bind_param("ii", $id, $sellerId);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    flash('error', 'Product not found.');
    header('Location: /ecommerce/seller/products.php');
    exit;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $status = $_POST['status'] ?? 'active';
    $imageName = $product['image'];

    if ($name === '' || $price === '' || $stock === '') {
        $errors[] = "Name, price and stock are required.";
    }
    if (!is_numeric($price) || $price <= 0) {
        $errors[] = "Price must be a positive number.";
    }
    if (!is_numeric($stock) || $stock < 0) {
        $errors[] = "Stock must be a valid number.";
    }

    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Image must be jpg, jpeg, png, gif or webp.";
        } elseif (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $imageName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../assets/uploads/' . $imageName);
        }
    }

    if (empty($errors)) {
        $upd = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=?, status=? WHERE id=? AND seller_id=?");
        $upd->bind_param("ssdiissii", $name, $description, $price, $stock, $categoryId, $imageName, $status, $id, $sellerId);
        $upd->execute();
        $upd->close();

        flash('success', 'Product updated.');
        header('Location: /ecommerce/seller/products.php');
        exit;
    }
}

$pageTitle = "Edit Product";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card wide">
    <h2 class="section-title">Edit Product</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php foreach ($errors as $e) echo escape($e) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" required value="<?= escape($_POST['name'] ?? $product['name']) ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?= escape($_POST['description'] ?? $product['description']) ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price (Tk)</label>
                <input type="number" step="0.01" name="price" required value="<?= escape($_POST['price'] ?? $product['price']) ?>">
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" required value="<?= escape($_POST['stock'] ?? $product['stock']) ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $cat['id'] == $product['category_id'] ? 'selected' : '' ?>><?= escape($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status">
                    <option value="active" <?= $product['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $product['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>Current Image</label><br>
            <img src="/ecommerce/assets/uploads/<?= escape($product['image']) ?>" onerror="this.src='https://via.placeholder.com/80'" style="width:80px;height:80px;object-fit:cover;border-radius:8px;">
        </div>
        <div class="form-group">
            <label>Replace Image (optional)</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Update Product</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
