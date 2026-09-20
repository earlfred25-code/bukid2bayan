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
        $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='farmer'"); if($res){ $r=$res->fetch_assoc(); $total_farmers=$r['c']; }
        $res = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='buyer'"); if($res){ $r=$res->fetch_assoc(); $total_buyers=$r['c']; }
        $res = $conn->query("SELECT COUNT(*) as c FROM orders"); if($res){ $r=$res->fetch_assoc(); $total_orders=$r['c']; }
    }
} catch(Exception $e){}
?>
<div class="admin-wrap">
    <div class="page-title-block">
        <h1 class="main-title"><i class="fas fa-seedling icon-green"></i> Bukid2Bayan</h1>
        <p class="sub-title">Admin Overview - Lahat ng galaw ng buyers at farmers</p>
    </div>
    <div class="stats">
        <div class="stat">
            <div class="stat-flex">
                <div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                    <div class="stat-small"><?php echo $total_farmers; ?> Farmers / <?php echo $total_buyers; ?> Buyers</div>
                </div>
                <div class="stat-icon icon-bg-green"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="stat stat-blue">
            <div class="stat-flex">
                <div>
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $total_products; ?></div>
                    <div class="stat-small">Gulay, Bigas, Itlog</div>
                </div>
                <div class="stat-icon icon-bg-blue"><i class="fas fa-box-open"></i></div>
            </div>
        </div>
        <div class="stat stat-yellow">
            <div class="stat-flex">
                <div>
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                    <div class="stat-small">From buyers</div>
                </div>
                <div class="stat-icon icon-bg-yellow"><i class="fas fa-shopping-basket"></i></div>
            </div>
        </div>
        <div class="stat stat-green">
            <div class="stat-flex">
                <div>
                    <div class="stat-label">System Status</div>
                    <div class="stat-value status-online"><i class="fas fa-circle dot"></i> Online</div>
                    <div class="stat-small">Vercel + Supabase</div>
                </div>
                <div class="stat-icon icon-bg-light-green"><i class="fas fa-server"></i></div>
            </div>
        </div>
    </div>
    <div class="quick">
        <a href="manage_products.php" class="quick-black"><i class="fas fa-pen-to-square"></i><br>Manage Products</a>
        <a href="../farmer_dashboard.php" class="quick-green"><i class="fas fa-plus"></i><br>Add Product as Admin</a>
        <a href="../index.php" class="quick-white"><i class="fas fa-store"></i><br>View Shop</a>
    </div>
</div>
</main>
</body>
</html>
