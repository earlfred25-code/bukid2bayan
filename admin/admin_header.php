<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}
include_once '../db_connect.php';
$page_title = $page_title ?? 'Bukid2Bayan Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header class="admin-header">
        <div class="container">
            <h1><i class="fas fa-leaf"></i> Bukid2Bayan Admin</h1>
            <nav class="admin-nav">
                <a href="index.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="manage_products.php"><i class="fas fa-box"></i> Products</a>
                <a href="../index.php" target="_blank"><i class="fas fa-store"></i> View Shop</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>
    <main class="admin-container">
