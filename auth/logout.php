<?php
session_start();
session_unset();
session_destroy();
header('Location: /ecommerce/auth/login.php');
exit;
