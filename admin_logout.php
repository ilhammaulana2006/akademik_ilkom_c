<?php
session_start();

// Destroy session
session_unset();
session_destroy();

// Redirect ke halaman login admin
header("Location: admin_login.php");
exit;
?>