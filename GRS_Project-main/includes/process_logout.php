<?php
require_once 'auth_check.php';

if (!is_logged_in()) {
    header('Location: ../login.php');
    exit();
}

// Destroy session and logout
session_start();
session_unset();
session_destroy();

header('Location: ../login.php');
exit();
?>
