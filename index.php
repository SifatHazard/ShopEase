<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
} else {
    header('Location: /ecommerce/customer/shop.php'); 
    exit;
}
