<?php
// This session start and security check will be on every admin page
session_start();

// SECURITY CHECK: Is the user logged in and are they an admin?
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php"); // Redirect to homepage if not admin
    exit();
}

// Connect to the database
include_once '../includes/db_connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- The title will be set by the page that includes this header -->
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