<?php
$page_title = "Dashboard - Bukid2Bayan";
include 'admin_header.php';
$is_pdo = $conn instanceof PDO;
$total_users = 0; $total_products = 0; $total_farmers = 0; $total_buyers = 0; $total_orders = 0;
try {
    if ($is_pdo) {
        $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $total_products = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        try { $total_farmers = $conn->query("SELECT COUNT(*) FROM users WHERE role='farmer'")->fetchColumn(); } catch(Exception $e){}
        try { $total_buyers = $conn->query("SELECT COUNT(*) FROM users WHERE role='buyer'")->fetchColumn(); } catch(Exception $e){}
        try { $total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(); } catch(Exception $e){}
    } else {
        $r = $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc(); $total_users = $r['c'];
        $r = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc(); $total_products = $r['c'];
    }
} catch(Exception $e){}
?>
<div style="margin-bottom:8px;">
    <h1 style="margin:0;font-size:1.9rem;font-weight:900;letter-spacing:-0.5px;">Dashboard</h1>
    <p style="margin:4px 0 0 0;color:#666;font-size:0.9rem;">Welcome back, Admin - Here is what's happening on Bukid2Bayan today - <?php echo date('F j, Y'); ?></p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin:20px 0 24px 0;">
    <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="width:48px;height:48px;background:#e6f7f4;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2a9d8f;font-size:1.2rem;margin-bottom:10px;"><i class="fas fa-users"></i></div>
        <div style="color:#666;font-size:0.8rem;font-weight:700;">Total Users</div>
        <div style="font-size:2rem;font-weight:900;margin-top:2px;"><?php echo $total_users; ?></div>
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;margin-top:6px;"><i class="fas fa-arrow-trend-up"></i> +12% vs last month</div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="width:48px;height:48px;background:#e6f7f4;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2a9d8f;font-size:1.2rem;margin-bottom:10px;"><i class="fas fa-box"></i></div>
        <div style="color:#666;font-size:0.8rem;font-weight:700;">Total Products</div>
        <div style="font-size:2rem;font-weight:900;margin-top:2px;"><?php echo $total_products; ?></div>
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;margin-top:6px;"><i class="fas fa-arrow-trend-up"></i> +8% vs last month</div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="width:48px;height:48px;background:#e6f7f4;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2a9d8f;font-size:1.2rem;margin-bottom:10px;"><i class="fas fa-basket-shopping"></i></div>
        <div style="color:#666;font-size:0.8rem;font-weight:700;">Orders</div>
        <div style="font-size:2rem;font-weight:900;margin-top:2px;"><?php echo $total_orders; ?></div>
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;margin-top:6px;"><i class="fas fa-arrow-trend-up"></i> +5% vs last month</div>
    </div>
    <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="width:48px;height:48px;background:#e6f7f4;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#2a9d8f;font-size:1.2rem;margin-bottom:10px;"><i class="fas fa-peso-sign"></i></div>
        <div style="color:#666;font-size:0.8rem;font-weight:700;">Revenue</div>
        <div style="font-size:1.6rem;font-weight:900;margin-top:2px;">₱124,500</div>
        <div style="color:#16a34a;font-size:0.8rem;font-weight:700;margin-top:6px;"><i class="fas fa-arrow-trend-up"></i> +18% vs last month</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr;gap:14px;">
    <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border:1px solid #eee;display:flex;gap:10px;flex-wrap:wrap;">
        <a href="manage_products.php" style="background:#111;color:#fff;padding:12px 16px;border-radius:10px;text-decoration:none;font-weight:800;font-size:0.9rem;"><i class="fas fa-pen-to-square"></i> Manage Products</a>
        <a href="../farmer_dashboard.php" style="background:#2a9d8f;color:#fff;padding:12px 16px;border-radius:10px;text-decoration:none;font-weight:800;font-size:0.9rem;"><i class="fas fa-plus"></i> Add Product</a>
        <a href="../index.php" style="background:#fff;color:#111;border:1.5px solid #ddd;padding:12px 16px;border-radius:10px;text-decoration:none;font-weight:800;font-size:0.9rem;"><i class="fas fa-store"></i> View Shop</a>
    </div>
</div>

        </div>
    </div>
</body>
</html>
