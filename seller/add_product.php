<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('seller');

$categories = $conn->query("SELECT * FROM categories ORDER BY name")->fetch_all(MYSQLI_ASSOC);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $stock = $_POST['stock'] ?? '';
    $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $imageName = 'no-image.png';

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
        } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
            $errors[] = "Image must be under 3MB.";
        } elseif (is_uploaded_file($_FILES['image']['tmp_name'])) {
            $imageName = 'prod_' . time() . '_' . uniqid() . '.' . $ext;
            $destination = __DIR__ . '/../assets/uploads/' . $imageName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $errors[] = "Failed to upload image. Please try again.";
            }
        }
    }

    if (empty($errors)) {
        $sellerId = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO products (seller_id, category_id, name, description, price, stock, image)
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissdis", $sellerId, $categoryId, $name, $description, $price, $stock, $imageName);
        $stmt->execute();
        $stmt->close();

        flash('success', 'Product added successfully.');
        header('Location: /ecommerce/seller/products.php');
        exit;
    }
}

$pageTitle = "Add Product";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card wide">
    <h2 class="section-title">Add New Product</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error"><?php foreach ($errors as $e) echo escape($e) . "<br>"; ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" name="name" required value="<?= escape($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="4"><?= escape($_POST['description'] ?? '') ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Price (Tk)</label>
                <input type="number" step="0.01" name="price" required value="<?= escape($_POST['price'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="stock" required value="<?= escape($_POST['stock'] ?? '') ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Category</label>
            <select name="category_id">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= escape($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Product Image</label>
            <input type="file" name="image" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Add Product</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
