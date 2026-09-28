<?php
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
            $st->execute([$uid_cookie]);
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
            $_SESSION['role'] = $u['role'] ?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin'] ?? 0;
        }
    }catch(Exception $e){}
}

if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    header('Location: login.php'); exit();
}

$user_id = (int)($_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? $_COOKIE['user_name'] ?? 'Buyer';
$role = $_SESSION['role'] ?? $_COOKIE['role'] ?? 'buyer';

if ($user_id === 0) { header('Location: login.php'); exit(); }

// kung farmer ka, dun ka sa farmer centre
if ($role === 'farmer') { header('Location: farmer_centre.php'); exit(); }
if (($_SESSION['is_admin'] ?? 0) == 1) { header('Location: admin/index.php'); exit(); }

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
        $s->execute([$user_id]);
        $r=$s->fetch(PDO::FETCH_ASSOC);
        $total_orders = $r['c'] ?? 0; $total_spent = $r['total'] ?? 0;

        $s=$conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id=? AND status IN ('pending','to_ship')");
        $s->execute([$user_id]);
        $pending_orders = (int)$s->fetchColumn();
    } else {
        $s=$conn->prepare("SELECT COUNT(*) as c, SUM(total_amount) as total FROM orders WHERE user_id=?");
        $s->bind_param("i",$user_id); $s->execute(); $r=$s->get_result()->fetch_assoc();
        $total_orders = $r['c']??0; $total_spent = $r['total']??0; $s->close();

        $s=$conn->prepare("SELECT COUNT(*) as c FROM orders WHERE user_id=? AND status IN ('pending','to_ship')");
        $s->bind_param("i",$user_id); $s->execute(); $r=$s->get_result()->fetch_assoc();
        $pending_orders = $r['c']??0; $s->close();
    }
} catch(Exception $e){}

$filter = $_GET['filter'] ?? 'all';
$allowed = ['all','pending','to_ship','shipped','completed','cancelled'];
if(!in_array($filter,$allowed)) $filter='all';

include 'header.php';
?>
<style>
.b-wrap{ max-width:1120px; margin:20px auto; padding:0 16px; }
.b-stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px; }
.b-tabs{ background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:12px 14px; margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap; }
@media(max-width:900px){ .b-stats{ grid-template-columns:1fr; } }
</style>
<div class="b-wrap">
    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:28px; margin-bottom:20px;">
        <h1 style="font-size:clamp(1.6rem,4vw,2.4rem); font-weight:900; margin:0;"><i class="fas fa-shopping-bag" style="color:#2a9d8f;"></i> Buyer Dashboard</h1>
        <p style="color:#666; margin-top:8px;">Welcome, <?= htmlspecialchars($user_name) ?>! Track mo orders mo dito.</p>
        <div style="margin-top:14px; display:flex; gap:10px; flex-wrap:wrap;">
            <a href="products.php" style="background:#111; color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-store"></i> Browse Products</a>
            <a href="cart.php" style="background:#fff; border:1.5px solid #111; color:#111; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-shopping-cart"></i> My Cart</a>
        </div>
    </div>

    <div class="b-stats">
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#2a9d8f;"><?= $total_orders ?></p><p style="font-weight:800;">Total Orders</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#e76f51;"><?= $pending_orders ?></p><p style="font-weight:800;">Pending / To Ship</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#1a2e35;">₱<?= number_format($total_spent, 2) ?></p><p style="font-weight:800;">Total Spent</p>
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

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px;">
        <h3 style="font-weight:900; margin:0 0 14px 0;">My Orders</h3>
        <?php
        try {
            $rows=[];
            if($is_pdo){
                if($filter=='all'){
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? ORDER BY id DESC LIMIT 100");
                    $s->execute([$user_id]);
                } else {
                    $s=$conn->prepare("SELECT id, total_amount, status, tracking_number, courier, created_at FROM orders WHERE user_id=? AND status=? ORDER BY id DESC LIMIT 100");
                    $s->execute([$user_id,$filter]);
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
                    echo '<div style="border:1px solid #eee; border-radius:12px; padding:14px; margin-bottom:12px; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;">';
                    echo '<div><b>Order #'.$o['id'].'</b> <span style="background:'.$badge.'; color:#fff; padding:3px 10px; border-radius:20px; font-size:0.7rem; font-weight:800;">'.$status.'</span>';
                    if(!empty($o['tracking_number'])) echo '<span style="background:#111; color:#fff; padding:3px 10px; border-radius:8px; font-size:0.7rem; margin-left:6px;">'.$o['tracking_number'].' - '.$o['courier'].'</span>';
                    echo '<br><span style="font-size:0.85rem; color:#666;">'.$o['created_at'].'</span></div>';
                    echo '<div style="text-align:right;"><b>₱'.number_format($o['total_amount'],2).'</b><br><a href="order_tracking.php?id='.$o['id'].'" style="background:#fff; border:1.5px solid #111; color:#111; padding:6px 12px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.8rem; display:inline-block; margin-top:6px;">Track Order</a></div>';
                    echo '</div>';
                }
            }
        } catch(Exception $e){ echo '<p style="color:#666;">No orders found</p>'; }
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>
