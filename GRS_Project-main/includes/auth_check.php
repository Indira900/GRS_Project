<?php
require_once 'config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin';
}

function is_student() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'student';
}

function is_lecturer() {
    return isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'lecturer';
}

// Check if user is logged in
if (!is_logged_in()) {
    redirect_with_message('../login.php', 'Please login to access this page.', 'error');
}

// For admin pages, check if user is admin
if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false && !is_admin()) {
    redirect_with_message('../login.php', 'You do not have permission to access this page.', 'error');
}

// For student pages, check if user is student or lecturer
if (strpos($_SERVER['PHP_SELF'], '/student/') !== false && !is_student() && !is_lecturer()) {
    redirect_with_message('../login.php', 'You do not have permission to access this page.', 'error');
}
?>
