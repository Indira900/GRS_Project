<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Grievance Redressal System</title>
    <link rel="stylesheet" href="<?php echo isset($is_dashboard) ? '../' : ''; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php if (isset($_SESSION['message'])): ?>
    <div class="flash-message <?php echo $_SESSION['message_type']; ?>">
        <div class="flash-content">
            <span><?php echo $_SESSION['message']; ?></span>
            <button class="flash-close">&times;</button>
        </div>
    </div>
    <?php 
    // Clear the message after displaying
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    endif; 
    ?>
