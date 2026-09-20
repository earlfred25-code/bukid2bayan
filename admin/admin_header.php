<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}
include_once '../db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Admin - AgriConnect'; ?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1>AgriConnect Admin</h1>
            <nav class="admin-nav">
                <a href="index.php">Dashboard</a>
                <a href="manage_products.php">Manage Products</a>
                <a href="#">View Orders</a>
                <a href="../index.php" target="_blank">View Site</a>
                <a href="../logout.php">Logout</a>
            </nav>
        </div>
    </header>
    <main class="admin-container">
