<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/db.php'; 


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function currentUser() {
    return [
        'id'    => $_SESSION['user_id'] ?? null,
        'name'  => $_SESSION['user_name'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'role'  => $_SESSION['user_role'] ?? null,
    ];
}

function requireRole($role) {
    if (!isLoggedIn()) {
        header('Location: /ecommerce/auth/login.php');
        exit;
    }
    if ($_SESSION['user_role'] !== $role) {
        header('Location: /ecommerce/access_denied.php');
        exit;
    }
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /ecommerce/auth/login.php');
        exit;
    }
}

function redirectToDashboard($role) {
    switch ($role) {
        case 'admin':    header('Location: /ecommerce/admin/dashboard.php'); break;
        case 'seller':   header('Location: /ecommerce/seller/dashboard.php'); break;
        case 'delivery': header('Location: /ecommerce/delivery/dashboard.php'); break;
        default:         header('Location: /ecommerce/customer/shop.php'); break;
    }
    exit;
}


function money($amount) {
    return 'Tk ' . number_format((float)$amount, 2);
}

function escape($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}


function cartCount($conn, $customerId) {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) AS c FROM cart WHERE customer_id = ?");
    $stmt->bind_param("i", $customerId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return (int)$row['c'];
}






function bindParams($stmt, $types, $params) {
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    array_unshift($refs, $types);
    call_user_func_array([$stmt, 'bind_param'], $refs);
}

function flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
