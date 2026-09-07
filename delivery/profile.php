<?php
require_once __DIR__ . '/../includes/functions.php';
requireRole('delivery');

$userId = $_SESSION['user_id'];
$errors = [];

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($name === '' || $email === '') {
        $errors[] = "Name and email are required.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check->bind_param("si", $email, $userId);
        $check->execute();
        $existing = $check->get_result()->fetch_assoc();
        $check->close();
        if ($existing) {
            $errors[] = "This email is already used by another account.";
        }
    }

    if (empty($errors)) {
        $upd = $conn->prepare("UPDATE users SET name=?, email=?, phone=?, address=? WHERE id=?");
        $upd->bind_param("ssssi", $name, $email, $phone, $address, $userId);
        $upd->execute();
        $upd->close();

        $_SESSION['user_name'] = $name;
        $_SESSION['user_email'] = $email;

        flash('success', 'Profile updated successfully.');
        header('Location: /ecommerce/delivery/profile.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!password_verify($currentPassword, $profile['password'])) {
        $errors[] = "Current password is incorrect.";
    }
    if (strlen($newPassword) < 6) {
        $errors[] = "New password must be at least 6 characters.";
    }
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match.";
    }

    if (empty($errors)) {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $upd->bind_param("si", $hash, $userId);
        $upd->execute();
        $upd->close();

        flash('success', 'Password changed successfully.');
        header('Location: /ecommerce/delivery/profile.php');
        exit;
    }
}

$pageTitle = "My Profile";
require_once __DIR__ . '/../includes/header.php';
?>

<h2 class="section-title">My Profile</h2>

<?php if (!empty($errors)): ?>
    <div class="alert alert-error"><?php foreach ($errors as $e) echo escape($e) . "<br>"; ?></div>
<?php endif; ?>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:24px;">
    <div class="form-card wide" style="margin:0;">
        <h3 style="margin-bottom:16px;">Personal Information</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="name" required value="<?= escape($profile['name']) ?>">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required value="<?= escape($profile['email']) ?>">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" value="<?= escape($profile['phone']) ?>">
            </div>
            <div class="form-group">
                <label>Address</label>
                <input type="text" name="address" value="<?= escape($profile['address']) ?>">
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary btn-block">Update Profile</button>
        </form>
    </div>

    <div class="form-card wide" style="margin:0;">
        <h3 style="margin-bottom:16px;">Change Password</h3>
        <form method="POST" action="">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" name="change_password" class="btn btn-primary btn-block">Change Password</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
