<?php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: ../categories.php');
    exit;
}

// Include this at the top of all admin pages
?>