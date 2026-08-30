<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Invalid email or password.";
    } elseif ($user['status'] === 'blocked') {
        $error = "Your account has been blocked. Please contact support.";
    } else {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        redirectToDashboard($user['role']);
    }
}

$pageTitle = "Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div class="form-card">
    <h2 class="section-title" style="text-align:center;">Login to ShopEase</h2>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= escape($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required value="<?= escape($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Login</button>
    </form>
    <p class="form-footer-link">Don't have an account? <a href="/ecommerce/auth/register.php">Register here</a></p>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
