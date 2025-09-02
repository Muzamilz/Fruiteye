<?php
require_once 'config.php';

// Use the logout_user function which now handles JWT
logout_user();

// Set a logout success message
$_SESSION['logout_message'] = 'تم تسجيل الخروج بنجاح';

// Redirect to login page
redirect('login.php');
?>
