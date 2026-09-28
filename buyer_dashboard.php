<?php
ob_start();
ini_set('session.save_path', sys_get_temp_dir());
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$is_pdo = $conn instanceof PDO;
$is_pgsql = $is_pdo && $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid_cookie = (int)$_COOKIE['user_id'];
    try{
        if($is_pdo){
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->execute(array($uid_cookie));
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->bind_param("i", $uid_cookie);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
            $st->close();
        }
        if($u){
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['username'];
            $_SESSION['role'] = isset($u['role']) ? $u['role'] : 'buyer';
            $_SESSION['is_admin'] = isset($u['is_admin']) ? $u['is_admin'] : 0;
        }
    }catch(Exception $e){}
}

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    header('Location: login.php'); exit();
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 0);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_COOKIE['user_name']) ? $_COOKIE['user_name'] : 'Buyer');
$role = isset($_SESSION['role']) ? $_SESSION['role'] : (isset($_COOKIE['role']) ? $_COOKIE['role'] : 'buyer');

if ($user_id === 0) { header('Location: login.php'); exit(); }

// FIXED: farmer_centre.php is deleted, redirect to new dashboard
if (strtolower($role) === 'farmer' || strtolower($role) === 'seller') { header('Location: farmer_dashboard.php'); exit(); }
if ((isset($_SESSION['is_admin']) ? $_SESSION['is_admin'] : 0) == 1) { header('Location: admin/index.php'); exit(); }

try {
    if ($is_pgsql) {
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (id SERIAL PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (id SERIAL PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->query("CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    }
} catch(Exception $e){}

$total_orders = 0; $total_spent = 0; $pending_orders = 0;
try {
    if($is_pdo){
        $s=$conn->prepare("SELECT COUNT(*) as c, SUM(total_amount) as total FROM orders WHERE user_id=?");
        $s->execute(array($user_id));
        $r=$s->fetch(PDO::FETCH_ASSOC);
        $total_orders = isset($r['c']) ? $r['c'] : 0; $total_spent = isset($r['total']) ? $r['total'] : 0;

        $s=$conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND status IN ('pending','to_ship')");
        $s->execute(array($user_id));
        $pending_orders = (int)$s->fetchColumn();
    } else {
        $s=$conn->prepare("SELECT COUNT(*) as c, SUM(total_amount) as total FROM orders WHERE user_id=?");
        $s->bind_param("i",$user_id); $s->execute(); $r=$s->get_result()->fetch_assoc();
        $total_orders = isset($r['c']) ? $r['c'] : 0; $total_spent = isset($r['total']) ? $r['total'] : 0; $s->close();

        $s=$conn->prepare("SELECT COUNT(*) as c FROM orders WHERE user_id=? AND status IN ('pending','to_ship')");
        $s->bind_param("i",$user_id); $s->execute(); $r=$s->get_result()->fetch_assoc();
        $pending_orders = isset($r['c']) ? $r['c'] : 0; $s->close();
    }
} catch(Exception $e){}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allowed = array('all','pending','to_ship','shipped','completed','cancelled');
if(!in_array($filter,$allowed)) $filter='all';

include 'header.php';
?>
<style>
:root{ --green:#2a9d8f; --dark:#1a2e35; }
.b-wrap{ max-width:1120px; margin:0 auto; padding:16px; }
.b-hero{ background: linear-gradient(135deg,#1a2e35 0%,#2a9d8f 100%); color:#fff; border-radius:20px; padding:24px; margin-bottom:16px; }
.b-hero h1{ margin:0; font-size:clamp(1.5rem,5vw,2rem); font-weight:900; }
.b-stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
.b-stat{ background:#fff; border:1px solid #eef2ee; border-radius:16px; padding:16px; text-align:center; box-shadow:0 2px 10px rgba(0,0,0,0.04); }
.b-tabs{ background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:10px; margin-bottom:16px; display:flex; gap:8px; flex-wrap:wrap; overflow-x:auto; }
.b-card{ background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:18px; }
.b-order{ border:1px solid #eee; border-radius:12px; padding:14px; margin-bottom:12px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
@media(max-width:900px){ .b-stats{ grid-template-columns:1fr; } .b-wrap{ padding:10px; } }
</style>
<div class="b-wrap">
    <div class="b-hero">
        <h1>🛒 Buyer Dashboard</h1>
        <p style="margin:6px 0 0 0; opacity:0.9;">Welcome, <?php echo htmlspecialchars($user_name); ?>! Track mo orders mo dito.</p>
        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="products.php" style="background:#fff; color:#1a2e35; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-store"></i> Browse Products</a>
            <a href="cart.php" style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.3); color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-shopping-cart"></i> My Cart</a>
        </div>
    </div>

    <div class="b-stats">
        <div class="b-stat">
            <div style="font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Total Orders</div>
            <div style="font-size:2rem; font-weight:900; color:#2a9d8f; margin-top:4px;"><?php echo $total_orders; ?></div>
        </div>
        <div class="b-stat">
            <div style="font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Pending / To Ship</div>
            <div style="font-size:2rem; font-weight:900; color:#e76f51; margin-top:4px;"><?php echo $pending_orders; ?></div>
        </div>
        <div class="b-stat">
            <div style="font-size:0.75rem; color:#6b7280; font-weight:700; text-transform:uppercase;">Total Spent</div>
            <div style="font-size:1.6rem; font-weight:900; color:#1a2e35; margin-top:4px;">₱<?php echo number_format($total_spent, 2); ?></div>
        </div>
    </div>

    <div class="b-tabs">
        <?php
        foreach($allowed as $k){
            $label = $k=='all' ? 'All' : ucwords(str_replace('_',' ',$k));
            $active = $filter==$k ? 'background:#111; color:#fff;' : 'background:#f5f5f5; color:#333; border:1px solid #eee;';
            echo "<a href='buyer_dashboard.php?filter=$k' style='padding:8px 14px; border-radius:20px; text-decoration:none; font-weight:800; font-size:0.85rem; $active'>$label</a>";
        }
        ?>
    </div>

    <div class="b-card">
        <h3 style="font-weight:900; margin:0 0 14px 0;">My Orders</h3>
        <?php
        try {
            $rows=array();
            if($is_pdo){
                if($filter=='all'){
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 100");
                    $s->execute(array($user_id));
                } else {
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? AND status=? ORDER BY id DESC LIMIT 100");
                    $s->execute(array($user_id,$filter));
                }
                $rows=$s->fetchAll(PDO::FETCH_ASSOC);
            } else {
                if($filter=='all'){
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 100");
                    $s->bind_param("i",$user_id);
                } else {
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? AND status=? ORDER BY id DESC LIMIT 100");
                    $s->bind_param("is",$user_id,$filter);
                }
                $s->execute(); $res=$s->get_result();
                if($res) while($r=$res->fetch_assoc()) $rows[]=$r;
                $s->close();
            }

            if(count($rows)==0){
                echo '<div style="text-align:center; padding:30px; color:#666;"><i class="fas fa-box-open" style="font-size:2rem;"></i><p>Wala ka pa order sa '.$filter.'.</p><a href="products.php" style="background:#2a9d8f; color:#fff; padding:10px 16px; border-radius:10px; text-decoration:none; font-weight:800; display:inline-block; margin-top:10px;">Mag Shopping</a></div>';
            } else {
                foreach($rows as $o){
                    $status=$o['status'];
                    $badge=$status=='pending'?'#ff9800':($status=='to_ship'?'#2196f3':($status=='shipped'?'#9c27b0':($status=='completed'?'#00b050':'#999')));
                    echo '<div class="b-order">';
                    echo '<div><b>Order #'.$o['id'].'</b> <span style="background:'.$badge.'; color:#fff; padding:3px 10px; border-radius:20px; font-size:0.7rem; font-weight:800;">'.$status.'</span>';
                    if(!empty($o['tracking_number'])) echo '<span style="background:#111; color:#fff; padding:3px 10px; border-radius:8px; font-size:0.7rem; margin-left:6px;">'.$o['tracking_number'].' - '.$o['courier'].'</span>';
                    echo '<br><span style="font-size:0.85rem; color:#666;">'.$o['created_at'].'</span></div>';
                    echo '<div style="text-align:right;"><b>₱'.number_format($o['total_amount'],2).'</b><br><a href="order_tracking.php?id='.$o['id'].'" style="background:#fff; border:1.5px solid #111; color:#111; padding:6px 12px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.8rem; display:inline-block; margin-top:6px;">Track Order</a></div>';
                    echo '</div>';
                }
            }
        } catch(Exception $e){ echo '<p style="color:#666;">No orders found: '.htmlspecialchars($e->getMessage()).'</p>'; }
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>
