<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); exit();
}
include '../db_connect.php';
$is_pdo = $conn instanceof PDO;
$total_users = 0;
$total_products = 0;
$total_farmers = 0;
$total_buyers = 0;
$total_orders = 0;
try {
    if ($is_pdo) {
        $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        try { $total_farmers = $conn->query("SELECT COUNT(*) FROM users WHERE role='farmer'")->fetchColumn(); } catch(Exception $e){ $total_farmers = 0; }
        try { $total_buyers = $conn->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn(); } catch(Exception $e){ $total_buyers = 0; }
        try { $total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(); } catch(Exception $e){ $total_orders = 0; }
    } else {
        $r = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc(); $total_users = $r['c'];
        $r = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc(); $total_products = $r['c'];
        $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='farmer'"); if($res){ $r=$res->fetch_assoc(); $total_farmers=$r['c']; }
        $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='buyer'"); if($res){ $r=$res->fetch_assoc(); $total_buyers=$r['c']; }
        $res = $conn->query("SELECT COUNT(*) as c FROM orders"); if($res){ $r=$res->fetch_assoc(); $total_orders=$r['c']; }
    }
} catch(Exception $e){}
include 'admin_header.php';
?>
<style>
.admin-wrap{ max-width:1200px; margin:0 auto; padding:20px; }
.stats{ display:grid; grid-template-columns:repeat(2,1fr); gap:14px; margin-bottom:24px; }
@media(min-width:768px){ .stats{ grid-template-columns:repeat(4,1fr); } }
.stat{ background:#fff; border-radius:14px; padding:18px; box-shadow:0 4px 12px rgba(0,0,0,0.06); border-left:4px solid #2a9d8f; }
.stat.green{ border-color:#2a9d8f; }
.stat.orange{ border-color:#f59e0b; }
.stat.blue{ border-color:#3b82f6; }
.stat.red{ border-color:#ef4444; }
.quick{ display:grid; grid-template-columns:1fr; gap:14px; }
@media(min-width:768px){ .quick{ grid-template-columns:repeat(3,1fr); } }
.quick a{ background:#111; color:#fff; padding:16px; border-radius:12px; text-decoration:none; font-weight:800; text-align:center; display:block; }
.quick a:nth-child(2){ background:#2a9d8f; }
.quick a:nth-child(3){ background:#fff; color:#111; border:1.5px solid #ddd; }
</style>
<div class="admin-wrap">
    <h1 style="margin:0 0 6px 0; font-weight:900;">Admin Dashboard</h1>
    <p style="color:#666; margin:0 0 20px 0;">Bukid2Bayan - Overview ng buong system</p>
    <div class="stats">
        <div class="stat green">
            <div style="color:#666; font-size:0.85rem;">Total Users</div>
            <div style="font-size:2rem; font-weight:900;"><?php echo $total_users; ?></div>
            <div style="font-size:0.75rem; color:#666; margin-top:4px;"><?php echo $total_farmers; ?> Farmers / <?php echo $total_buyers; ?> Buyers</div>
        </div>
        <div class="stat blue">
            <div style="color:#666; font-size:0.85rem;">Total Products</div>
            <div style="font-size:2rem; font-weight:900;"><?php echo $total_products; ?></div>
            <div style="font-size:0.75rem; color:#666; margin-top:4px;">Gulay, Bigas, Itlog</div>
        </div>
        <div class="stat orange">
            <div style="color:#666; font-size:0.85rem;">Pending Orders</div>
            <div style="font-size:2rem; font-weight:900;"><?php echo $total_orders; ?></div>
            <div style="font-size:0.75rem; color:#666; margin-top:4px;">From buyers</div>
        </div>
        <div class="stat red">
            <div style="color:#666; font-size:0.85rem;">System Status</div>
            <div style="font-size:1.2rem; font-weight:900; color:#16a34a;">● Online</div>
            <div style="font-size:0.75rem; color:#666; margin-top:4px;">Vercel + Supabase</div>
        </div>
    </div>
    <div class="quick">
        <a href="manage_products.php"><i class="fas fa-box"></i><br>Manage Products</a>
        <a href="../farmer_dashboard.php"><i class="fas fa-plus"></i><br>Add Product as Admin</a>
        <a href="../index.php"><i class="fas fa-store"></i><br>View Shop</a>
    </div>
    <div style="margin-top:24px; background:#fff; border-radius:12px; padding:18px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
        <h3 style="margin:0 0 12px 0;">Quick Guide</h3>
        <div style="font-size:0.9rem; color:#444; line-height:1.6;">
            <p><strong>Buyer</strong> nag-login -> pupunta sa <code>buyer_dashboard.php</code></p>
            <p><strong>Farmer/Seller</strong> nag-login -> pupunta sa <code>farmer_dashboard.php</code> at makakapag-add ng products</p>
            <p><strong>Admin (ikaw)</strong> -> dito sa dashboard na to, pwede mo i-edit at i-delete lahat ng products sa <code>Manage Products</code></p>
        </div>
    </div>
</div>
