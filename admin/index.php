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
<div style="max-width:1200px;margin:0 auto;padding:20px;">
    <div style="margin-bottom:24px;">
        <h1 style="margin:0;font-size:2rem;font-weight:900;letter-spacing:-1px;"><i class="fas fa-seedling" style="color:#2a9d8f;"></i> Bukid2Bayan</h1>
        <p style="margin:6px 0 0 0;color:#666;font-size:0.95rem;">Admin Overview - Lahat ng galaw ng buyers at farmers</p>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;margin-bottom:24px;">
        <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border-left:4px solid #2a9d8f;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#666;font-size:0.8rem;font-weight:700;text-transform:uppercase;">Total Users</div>
                    <div style="font-size:2.2rem;font-weight:900;margin-top:4px;"><?php echo $total_users; ?></div>
                    <div style="font-size:0.75rem;color:#888;margin-top:4px;"><?php echo $total_farmers; ?> Farmers / <?php echo $total_buyers; ?> Buyers</div>
                </div>
                <div style="width:48px;height:48px;background:#e6f7f4;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#2a9d8f;font-size:1.4rem;"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border-left:4px solid #3b82f6;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#666;font-size:0.8rem;font-weight:700;text-transform:uppercase;">Total Products</div>
                    <div style="font-size:2.2rem;font-weight:900;margin-top:4px;"><?php echo $total_products; ?></div>
                    <div style="font-size:0.75rem;color:#888;margin-top:4px;">Gulay, Bigas, Itlog</div>
                </div>
                <div style="width:48px;height:48px;background:#dbeafe;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#3b82f6;font-size:1.4rem;"><i class="fas fa-box-open"></i></div>
            </div>
        </div>

        <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border-left:4px solid #f59e0b;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#666;font-size:0.8rem;font-weight:700;text-transform:uppercase;">Pending Orders</div>
                    <div style="font-size:2.2rem;font-weight:900;margin-top:4px;"><?php echo $total_orders; ?></div>
                    <div style="font-size:0.75rem;color:#888;margin-top:4px;">From buyers</div>
                </div>
                <div style="width:48px;height:48px;background:#fef3c7;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#f59e0b;font-size:1.4rem;"><i class="fas fa-shopping-basket"></i></div>
            </div>
        </div>

        <div style="background:#fff;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(0,0,0,0.06);border-left:4px solid #16a34a;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="color:#666;font-size:0.8rem;font-weight:700;text-transform:uppercase;">System Status</div>
                    <div style="font-size:1.3rem;font-weight:900;margin-top:8px;color:#16a34a;"><i class="fas fa-circle" style="font-size:0.6rem;"></i> Online</div>
                    <div style="font-size:0.75rem;color:#888;margin-top:4px;">Vercel + Supabase</div>
                </div>
                <div style="width:48px;height:48px;background:#dcfce7;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:1.4rem;"><i class="fas fa-server"></i></div>
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;">
        <a href="manage_products.php" style="background:#111;color:#fff;padding:16px;border-radius:12px;text-decoration:none;font-weight:800;text-align:center;display:block;"><i class="fas fa-pen-to-square"></i><br>Manage Products</a>
        <a href="../farmer_dashboard.php" style="background:#2a9d8f;color:#fff;padding:16px;border-radius:12px;text-decoration:none;font-weight:800;text-align:center;display:block;"><i class="fas fa-plus"></i><br>Add Product as Admin</a>
        <a href="../index.php" style="background:#fff;color:#111;border:1.5px solid #ddd;padding:16px;border-radius:12px;text-decoration:none;font-weight:800;text-align:center;display:block;"><i class="fas fa-store"></i><br>View Shop</a>
    </div>
</div>
</main>
</body>
</html>
