<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $allowedRoles = ['customer', 'seller', 'delivery']; 
    if (!in_array($role, $allowedRoles)) $role = 'customer';

    if ($name === '' || $email === '' || $password === '') {
        $errors[] = "Name, email and password are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }
    if ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    }

    if (empty($errors)) {
        
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();
        if ($checkResult->fetch_assoc()) {
            $errors[] = "An account with this email already exists.";
        }
        $check->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $name, $email, $hash, $role, $phone, $address);
        $stmt->execute();
        $stmt->close();

        flash('success', 'Account created successfully! Please log in.');
        header('Location: /ecommerce/auth/login.php');
        exit;
    }
}

$pageTitle = "Register";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h2 class="section-title" style="text-align:center;">Create Your Account</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e) echo escape($e) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <form id="registerForm" method="POST" action="">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required value="<?= escape($_POST['name'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= escape($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
            </div>
        </div>
        <div class="form-group">
            <label>I want to register as</label>
            <select name="role">
                <option value="customer">Customer (buy products)</option>
                <option value="seller">Seller (sell products)</option>
                <option value="delivery">Delivery Agent</option>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= escape($_POST['phone'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?= escape($_POST['address'] ?? '') ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Register</button>
    </form>
    <p class="form-footer-link">Already have an account? <a href="/ecommerce/auth/login.php">Login here</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
