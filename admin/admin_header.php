<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $secure = $is_https;
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    }
    session_start();
}
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$secure = $is_https;

// VERCEL FIX - RESTORE FROM COOKIE
if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] !== '') {
    if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = (int)$_COOKIE['user_id'];
    if (!isset($_SESSION['user_name']) && isset($_COOKIE['user_name'])) $_SESSION['user_name'] = $_COOKIE['user_name'];
    if (!isset($_SESSION['is_admin']) && isset($_COOKIE['is_admin'])) $_SESSION['is_admin'] = (int)$_COOKIE['is_admin'];
    if (!isset($_SESSION['role']) && isset($_COOKIE['role'])) $_SESSION['role'] = $_COOKIE['role'];
    @setcookie('user_id', (int)$_COOKIE['user_id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); exit();
}
include_once '../db_connect.php';
$is_pdo = $conn instanceof PDO;
$page_title = $page_title ?? 'Dashboard - Bukid2Bayan';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        *{box-sizing:border-box;} body{margin:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;background:#f5f7f4;color:#222;display:flex;min-height:100vh;}
        .sidebar{width:260px;background:#111;color:#fff;display:flex;flex-direction:column;position:fixed;left:0;top:0;bottom:0;z-index:20;}
        .sidebar-top{padding:20px;border-bottom:1px solid #222;}
        .logo{font-weight:900;font-size:1.25rem;display:flex;align-items:center;gap:8px;letter-spacing:-0.5px;}
        .logo small{display:block;font-size:0.7rem;color:#2a9d8f;font-weight:700;letter-spacing:0.5px;}
        .nav{padding:12px;display:flex;flex-direction:column;gap:6px;flex:1;}
        .nav a{color:#aaa;text-decoration:none;padding:10px 12px;border-radius:10px;display:flex;align-items:center;gap:10px;font-weight:700;font-size:0.9rem;}
        .nav a:hover{background:#1a1a1a;color:#fff;}
        .nav a.active{background:#2a9d8f;color:#fff;}
        .sidebar-bottom{padding:12px;border-top:1px solid #222;}
        .main{flex:1;margin-left:260px;min-width:0;}
        .topbar{background:#fff;border-bottom:1px solid #eee;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;gap:12px;position:sticky;top:0;z-index:10;}
        .search{flex:1;max-width:400px;background:#f5f5f5;border-radius:10px;padding:8px 12px;display:flex;align-items:center;gap:8px;}
        .search input{border:none;background:transparent;outline:none;width:100%;font-size:0.9rem;}
        .content{padding:24px 20px;max-width:1200px;margin:0 auto;}
        .card{background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;margin-bottom:16px}
        .table-wrap{overflow:auto} table{width:100%;border-collapse:collapse;font-size:0.9rem} th,td{padding:10px 12px;border-bottom:1px solid #eee;text-align:left} th{background:#f9fafb;font-weight:800}
        .badge{padding:4px 10px;border-radius:20px;font-size:0.7rem;font-weight:800;color:#fff}
        .btn{padding:6px 12px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;text-decoration:none;font-weight:700;font-size:0.8rem;display:inline-block}
        .btn.danger{color:#dc2626;border-color:#fecaca}.btn.primary{background:#111;color:#fff}
        @media(max-width:900px){.sidebar{width:100%;position:relative;}.main{margin-left:0;}.topbar{flex-wrap:wrap;}}
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="logo"><i class="fas fa-leaf" style="color:#2a9d8f;"></i> Bukid2Bayan<small>Agricultural E-commerce</small></div>
        </div>
        <nav class="nav">
            <a href="index.php" class="<?php echo $current=='index.php'?'active':''; ?>"><i class="fas fa-house"></i> Dashboard</a>
            <a href="manage_products.php" class="<?php echo $current=='manage_products.php'?'active':''; ?>"><i class="fas fa-box"></i> Products</a>
            <a href="users.php" class="<?php echo $current=='users.php'?'active':''; ?>"><i class="fas fa-users"></i> Users</a>
            <a href="orders.php" class="<?php echo $current=='orders.php'?'active':''; ?>"><i class="fas fa-basket-shopping"></i> Orders</a>
            <a href="settings.php" class="<?php echo $current=='settings.php'?'active':''; ?>"><i class="fas fa-gear"></i> Settings</a>
        </nav>
        <div class="sidebar-bottom">
            <a href="../index.php" target="_blank" style="color:#aaa;text-decoration:none;font-weight:700;font-size:0.85rem;display:flex;align-items:center;gap:8px;padding:8px;"><i class="fas fa-store"></i> View Shop</a>
            <a href="../logout.php" style="color:#ff6b6b;text-decoration:none;font-weight:700;font-size:0.85rem;display:flex;align-items:center;gap:8px;padding:8px;"><i class="fas fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>
    <div class="main">
        <div class="topbar">
            <div class="search"><i class="fas fa-magnifying-glass" style="color:#888;"></i><input placeholder="Search products, users, orders..."></div>
            <div style="display:flex;align-items:center;gap:12px;">
                <i class="fas fa-bell" style="font-size:1.2rem;color:#555;"></i>
                <div style="width:32px;height:32px;background:#2a9d8f;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:900;font-size:0.8rem;">A</div>
                <span style="font-weight:800;font-size:0.9rem;">Admin</span>
            </div>
        </div>
        <div class="content">
