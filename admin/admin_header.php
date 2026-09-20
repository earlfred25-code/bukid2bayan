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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f5f7f4;margin:0;color:#222;}
        .container{width:95%;max-width:1400px;margin:0 auto;}
        .admin-header{background:#111;color:#fff;padding:1rem 0;position:sticky;top:0;z-index:10;}
        .admin-header .container{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
        .admin-header h1{margin:0;font-size:1.4rem;font-weight:900;letter-spacing:-0.5px;}
        .admin-nav{display:flex;gap:18px;flex-wrap:wrap;}
        .admin-nav a{color:#ddd;text-decoration:none;font-size:0.9rem;font-weight:700;}
        .admin-nav a:hover{color:#2a9d8f;}
        .admin-container{padding:24px 0;width:95%;max-width:1400px;margin:0 auto;}
        .admin-wrap{max-width:1200px;margin:0 auto;padding:20px;}
        .page-title-block{margin-bottom:24px;}
        .main-title{margin:0;font-size:2rem;font-weight:900;letter-spacing:-1px;}
        .sub-title{margin:6px 0 0 0;color:#666;font-size:0.95rem;}
        .stats{display:grid;grid-template-columns:repeat(2,1fr);gap:14px;margin-bottom:24px;}
        .stat{background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border-left:4px solid #2a9d8f;}
        .stat-blue{border-color:#3b82f6;}
        .stat-yellow{border-color:#f59e0b;}
        .stat-green{border-color:#16a34a;}
        .stat-flex{display:flex;justify-content:space-between;align-items:center;}
        .stat-label{color:#666;font-size:0.8rem;font-weight:700;text-transform:uppercase;}
        .stat-value{font-size:2.2rem;font-weight:900;margin-top:4px;}
        .stat-small{font-size:0.75rem;color:#888;margin-top:4px;}
        .stat-icon{width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;}
        .icon-bg-green{background:#e6f7f4;color:#2a9d8f;}
        .icon-bg-blue{background:#dbeafe;color:#3b82f6;}
        .icon-bg-yellow{background:#fef3c7;color:#f59e0b;}
        .icon-bg-light-green{background:#dcfce7;color:#16a34a;}
        .icon-green{color:#2a9d8f;}
        .status-online{font-size:1.3rem;color:#16a34a;margin-top:8px;font-weight:900;}
        .dot{font-size:0.6rem;}
        .quick{display:grid;grid-template-columns:1fr;gap:14px;}
        .quick a{padding:16px;border-radius:12px;text-decoration:none;font-weight:800;text-align:center;display:block;}
        .quick-black{background:#111;color:#fff;}
        .quick-green{background:#2a9d8f;color:#fff;}
        .quick-white{background:#fff;color:#111;border:1.5px solid #ddd;}
        table{width:100%;border-collapse:collapse;background:white;box-shadow:0 4px 15px rgba(0,0,0,0.06);border-radius:12px;overflow:hidden;}
        th,td{padding:1rem;border-bottom:1px solid #eee;text-align:left;font-size:0.9rem;}
        th{background:#f8faf8;font-weight:800;color:#555;}
        @media(min-width:768px){.stats{grid-template-columns:repeat(4,1fr);}.quick{grid-template-columns:repeat(3,1fr);}}
    </style>
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
