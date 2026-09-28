<?php
ob_start();
ini_set('session.save_path', sys_get_temp_dir());
session_set_cookie_params(0, '/', '', false, true);
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$is_pdo = $conn instanceof PDO;
$is_pgsql = $is_pdo && $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid_cookie = (int)$_COOKIE['user_id'];
    $_SESSION['user_id'] = $uid_cookie;
    $_SESSION['user_name'] = isset($_COOKIE['user_name']) ? $_COOKIE['user_name'] : 'Farmer';
    $_SESSION['role'] = isset($_COOKIE['role']) ? $_COOKIE['role'] : 'farmer';
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
            $_SESSION['role'] = isset($u['role']) ? $u['role'] : 'farmer';
            $_SESSION['is_admin'] = isset($u['is_admin']) ? $u['is_admin'] : 0;
        }
    }catch(Exception $e){}
}
if (!isset($_SESSION['user_id']) && !isset($_COOKIE['user_id'])) {
    header("Location: login.php"); exit();
}
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 0);
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_COOKIE['user_name']) ? $_COOKIE['user_name'] : 'Farmer');
if ($user_id === 0) {
    setcookie('user_id','',time()-3600,'/');
    header("Location: login.php"); exit();
}
function addTracking($conn, $oid, $status, $loc, $desc){
    try {
        $is_pdo = $conn instanceof PDO;
        if($is_pdo){
            $conn->exec("CREATE TABLE IF NOT EXISTS order_tracking (id SERIAL PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $s=$conn->prepare("INSERT INTO order_tracking (order_id,status,location,description) VALUES (?,?,?,?)");
            $s->execute(array($oid,$status,$loc,$desc));
        } else {
            $conn->query("CREATE TABLE IF NOT EXISTS order_tracking (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $s=$conn->prepare("INSERT INTO order_tracking (order_id,status,location,description) VALUES (?,?,?,?)");
            $s->bind_param("isss",$oid,$status,$loc,$desc);
            $s->execute(); $s->close();
        }
    } catch(Exception $e){}
}
try {
    if ($is_pgsql) {
        $conn->exec("CREATE TABLE IF NOT EXISTS products (id SERIAL PRIMARY KEY, name VARCHAR(255), farmer_name VARCHAR(100), price DECIMAL(10,2), unit VARCHAR(20), image_url TEXT, farmer_id INT, user_id INT, stock INT DEFAULT 0, category VARCHAR(50) DEFAULT 'Gulay', description TEXT)");
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (id SERIAL PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (id SERIAL PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), farmer_name VARCHAR(100), price DECIMAL(10,2), unit VARCHAR(20), image_url TEXT, farmer_id INT, user_id INT, stock INT DEFAULT 0, category VARCHAR(50) DEFAULT 'Gulay', description TEXT)");
        $conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->query("CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    }
} catch(Exception $e){}
$message = "";
if (isset($_POST['add_product'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $unit = isset($_POST['unit']) ? trim($_POST['unit']) : 'kg';
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $category = isset($_POST['category']) ? trim($_POST['category']) : 'Gulay';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $image = isset($_POST['image_url']) ? trim($_POST['image_url']) : '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if(in_array($ext, array('jpg','jpeg','png','webp'))){
            $uploadDir = __DIR__.'/uploads'; @mkdir($uploadDir, 0777, true);
            $newName = 'prod_'.$user_id.'_'.time().'_'.rand(100,999).'.'.$ext;
            $dest = $uploadDir.'/'.$newName;
            if(@move_uploaded_file($_FILES['image']['tmp_name'], $dest)){ $image = 'uploads/'.$newName; }
            if(empty($image)){
                $tmpData = @file_get_contents($_FILES['image']['tmp_name']);
                if($tmpData && strlen($tmpData) < 3000000){
                    $b64 = base64_encode($tmpData);
                    $d1 = 'data:';
                    $d2 = 'image/';
                    $d3 = ';base64,';
                    $image = $d1 . $d2 . $ext . $d3 . $b64;
                }
            }
        }
    }
    if ($name!== '' && $price > 0) {
        try {
            if($is_pdo){
                $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, price, unit, image_url, farmer_id, user_id, stock, category, description) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute(array($name,$user_name,$price,$unit,$image,$user_id,$user_id,$stock,$category,$description));
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, price, unit, image_url, farmer_id, user_id, stock, category, description) VALUES (?,?,?,?,?,?,?,?,?,?)");
                $stmt->bind_param("ssdssiiiss", $name, $user_name, $price, $unit, $image, $user_id, $user_id, $stock, $category, $description);
                $stmt->execute(); $stmt->close();
            }
            header('Location: farmer_dashboard.php?msg='.urlencode($name.' na-add na!')); exit();
        } catch(Exception $e){ $message = "Error: ".$e->getMessage(); }
    }
}
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        if($is_pdo){ $stmt=$conn->prepare("DELETE FROM products WHERE id=? AND (farmer_id=? OR user_id=?)"); $stmt->execute(array($del_id,$user_id,$user_id)); }
        else { $stmt = $conn->prepare("DELETE FROM products WHERE id=? AND (farmer_id=? OR user_id=?)"); $stmt->bind_param("iii", $del_id, $user_id, $user_id); $stmt->execute(); $stmt->close(); }
    } catch(Exception $e){}
    header('Location: farmer_dashboard.php?tab=products'); exit();
}
if (isset($_GET['action']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id']; $act = $_GET['action'];
    try {
        if($is_pdo){
            if($act=='confirm'){
                $s=$conn->prepare("UPDATE orders SET status='to_ship' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->execute(array($oid,$user_id,$user_id,$user_id));
                addTracking($conn, $oid, 'to_ship', 'Binan Farmer Centre', 'Seller confirmed order');
            } elseif($act=='ship'){
                $track = 'B2B'.date('ymd').rand(10000,99999);
                $s=$conn->prepare("UPDATE orders SET status='shipped', tracking_number=?, courier='BUKID2BAYAN Xpress' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->execute(array($track,$oid,$user_id,$user_id,$user_id));
                addTracking($conn, $oid, 'shipped', 'Binan Sorting Hub', "Parcel $track shipped");
            } elseif($act=='complete'){
                $s=$conn->prepare("UPDATE orders SET status='completed' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->execute(array($oid,$user_id,$user_id,$user_id));
                addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered');
            } elseif($act=='cancel'){
                $s=$conn->prepare("UPDATE orders SET status='cancelled' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->execute(array($oid,$user_id,$user_id,$user_id));
                addTracking($conn, $oid, 'cancelled', 'Cancelled', 'Order cancelled by farmer');
            }
        } else {
            if($act=='confirm'){
                $s=$conn->prepare("UPDATE orders SET status='to_ship' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->bind_param("iiii",$oid,$user_id,$user_id,$user_id); $s->execute(); $s->close();
                addTracking($conn, $oid, 'to_ship', 'Binan Farmer Centre', 'Seller confirmed order');
            } elseif($act=='ship'){
                $track = 'B2B'.date('ymd').rand(10000,99999);
                $s=$conn->prepare("UPDATE orders SET status='shipped', tracking_number=?, courier='BUKID2BAYAN Xpress' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->bind_param("siiii",$track,$oid,$user_id,$user_id,$user_id); $s->execute(); $s->close();
                addTracking($conn, $oid, 'shipped', 'Binan Sorting Hub', "Parcel $track shipped");
            } elseif($act=='complete'){
                $s=$conn->prepare("UPDATE orders SET status='completed' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->bind_param("iiii",$oid,$user_id,$user_id,$user_id); $s->execute(); $s->close();
                addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered');
            } elseif($act=='cancel'){
                $s=$conn->prepare("UPDATE orders SET status='cancelled' WHERE id=? AND (id IN (SELECT order_id FROM order_items WHERE farmer_id=?) OR id IN (SELECT oi.order_id FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=?))");
                $s->bind_param("iiii",$oid,$user_id,$user_id,$user_id); $s->execute(); $s->close();
                addTracking($conn, $oid, 'cancelled', 'Cancelled', 'Order cancelled by farmer');
            }
        }
    } catch(Exception $e){}
    header('Location: farmer_dashboard.php?tab=orders&filter='.$act); exit();
}
$total_products = 0; $total_orders = 0; $total_earnings = 0; $pending_orders = 0; $my_products = array();
try {
    if($is_pdo){
        $stmt=$conn->prepare("SELECT COUNT(*) FROM products WHERE farmer_id=? OR user_id=?"); $stmt->execute(array($user_id,$user_id)); $total_products = (int)$stmt->fetchColumn();
        $stmt=$conn->prepare("SELECT COUNT(DISTINCT oi.order_id) as c FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?"); $stmt->execute(array($user_id,$user_id,$user_id)); $r=$stmt->fetch(PDO::FETCH_ASSOC); $total_orders = isset($r['c']) ? (int)$r['c'] : 0;
        $stmt=$conn->prepare("SELECT SUM(oi.price*oi.quantity) as total FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status!='cancelled'"); $stmt->execute(array($user_id,$user_id,$user_id)); $r=$stmt->fetch(PDO::FETCH_ASSOC); $total_earnings = isset($r['total']) ? (float)$r['total'] : 0;
        $stmt=$conn->prepare("SELECT COUNT(DISTINCT oi.order_id) as c FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status='pending'"); $stmt->execute(array($user_id,$user_id,$user_id)); $r=$stmt->fetch(PDO::FETCH_ASSOC); $pending_orders = isset($r['c']) ? (int)$r['c'] : 0;
        $stmt=$conn->prepare("SELECT id, name, price, unit, stock, category, image_url FROM products WHERE farmer_id=? OR user_id=? ORDER BY id DESC"); $stmt->execute(array($user_id,$user_id)); $my_products=$stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE farmer_id=? OR user_id=?"); $stmt->bind_param("ii", $user_id, $user_id); $stmt->execute(); $res=$stmt->get_result(); if($res) $total_products=$res->fetch_assoc()['c']; $stmt->close();
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT oi.order_id) as c FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?"); $stmt->bind_param("iii", $user_id, $user_id, $user_id); $stmt->execute(); $res=$stmt->get_result(); if($res) $total_orders=$res->fetch_assoc()['c']; $stmt->close();
        $stmt = $conn->prepare("SELECT SUM(oi.price*oi.quantity) as total FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status!='cancelled'"); $stmt->bind_param("iii", $user_id, $user_id, $user_id); $stmt->execute(); $res=$stmt->get_result(); if($res) $total_earnings=$res->fetch_assoc()['total']; $stmt->close();
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT oi.order_id) as c FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id JOIN orders o ON o.id=oi.order_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status='pending'"); $stmt->bind_param("iii", $user_id, $user_id, $user_id); $stmt->execute(); $res=$stmt->get_result(); if($res) $pending_orders=$res->fetch_assoc()['c']; $stmt->close();
        $stmt = $conn->prepare("SELECT id, name, price, unit, stock, category, image_url FROM products WHERE farmer_id=? OR user_id=? ORDER BY id DESC"); $stmt->bind_param("ii", $user_id, $user_id); $stmt->execute(); $res=$stmt->get_result(); while($r=$res->fetch_assoc()) $my_products[]=$r; $stmt->close();
    }
} catch(Exception $e){}
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$allowed = array('all','pending','to_ship','shipped','completed','cancelled');
if(!in_array($filter,$allowed)) $filter='all';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'products';
if(!in_array($tab, array('products','orders','add'))) $tab='products';
if(isset($_GET['msg'])) { $message = $_GET['msg']; }
include 'header.php';
?>
<div style="max-width:1220px; margin:0 auto; padding:16px;">
    <?php if($message):?><div style="background:#e6f7f5; border:1.5px solid #2a9d8f; padding:14px; border-radius:12px; margin-bottom:14px; text-align:center; font-weight:800;"><?php echo htmlspecialchars($message);?></div><?php endif;?>
    <div style="background: linear-gradient(135deg,#1a2e35 0%,#2a9d8f 100%); color:#fff; border-radius:20px; padding:24px; margin-bottom:16px;"><h1>Hi, <?php echo htmlspecialchars($user_name);?>!</h1><p>ID: <?php echo $user_id;?></p></div>
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin-bottom:16px;">
        <div style="background:#fff; border-radius:16px; padding:16px; text-align:center; border:1px solid #eee;">Products<br><b style="font-size:1.7rem; color:#2a9d8f;"><?php echo $total_products;?></b></div>
        <div style="background:#fff; border-radius:16px; padding:16px; text-align:center; border:1px solid #eee;">Pending<br><b style="font-size:1.7rem; color:#f59e0b;"><?php echo $pending_orders;?></b></div>
        <div style="background:#fff; border-radius:16px; padding:16px; text-align:center; border:1px solid #eee;">Orders<br><b style="font-size:1.7rem; color:#e76f51;"><?php echo $total_orders;?></b></div>
        <div style="background:#fff; border-radius:16px; padding:16px; text-align:center; border:1px solid #eee;">Earnings<br><b>₱<?php echo number_format($total_earnings,2);?></b></div>
    </div>
    <div style="background:#fff; border-radius:16px; padding:8px; display:flex; gap:8px; margin-bottom:16px; border:1px solid #e5e5e5;">
        <a href="farmer_dashboard.php?tab=products" style="flex:1; text-align:center; padding:12px; border-radius:12px; text-decoration:none; font-weight:800; <?php echo $tab=='products'?'background:#111; color:#fff;':'background:#f5f7f5; color:#333;';?>">Products</a>
        <a href="farmer_dashboard.php?tab=orders&filter=all" style="flex:1; text-align:center; padding:12px; border-radius:12px; text-decoration:none; font-weight:800; <?php echo $tab=='orders'?'background:#111; color:#fff;':'background:#f5f7f5; color:#333;';?>">Orders <?php if($pending_orders>0) echo '('.$pending_orders.')';?></a>
        <a href="farmer_dashboard.php?tab=add" style="flex:1; text-align:center; padding:12px; border-radius:12px; text-decoration:none; font-weight:800; <?php echo $tab=='add'?'background:#111; color:#fff;':'background:#f5f7f5; color:#333;';?>">Add Product</a>
    </div>
    <?php if($tab=='products'):?>
    <div style="background:#fff; border-radius:16px; border:1px solid #e5e5e5; padding:18px;">
        <h3>My Products (<?php echo $total_products;?>)</h3>
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:12px;">
        <?php foreach($my_products as $p): $img = $p['image_url']; if(empty($img)) $img = 'https://via.placeholder.com/300?text='.urlencode($p['name']);?>
            <div style="border:1px solid #eee; border-radius:14px; overflow:hidden;">
                <img src="<?php echo htmlspecialchars($img);?>" style="width:100%; height:140px; object-fit:cover;">
                <div style="padding:10px;"><b><?php echo htmlspecialchars($p['name']);?></b><br><span style="color:#2a9d8f; font-weight:900;">₱<?php echo number_format($p['price'],2);?></span></div>
                <div style="padding:8px;"><a href="farmer_dashboard.php?delete=<?php echo $p['id'];?>" onclick="return confirm('Tanggalin?')" style="display:block; text-align:center; color:#c00; border:1px solid #fcc; border-radius:8px; padding:6px; text-decoration:none; font-weight:800;">Delete</a></div>
            </div>
        <?php endforeach;?>
        </div>
    </div>
    <?php endif;?>
    <?php if($tab=='orders'):?>
    <div style="background:#fff; border-radius:16px; border:1px solid #e5e5e5; padding:18px;">
        <h3>Incoming Orders</h3>
        <div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:14px;">
            <?php foreach($allowed as $k){ $label = $k=='all' ? 'All' : ucwords(str_replace('_',' ',$k)); $active = $filter==$k ? 'background:#111; color:#fff;' : 'background:#f5f5f5; color:#333; border:1px solid #eee;'; echo "<a href='farmer_dashboard.php?tab=orders&filter=$k' style='padding:8px 14px; border-radius:20px; text-decoration:none; font-weight:800; font-size:0.8rem; $active'>$label</a>"; }?>
        </div>
        <?php
        try {
            $rows=array();
            if($is_pdo){
                if($filter=='all'){
                    $s=$conn->prepare("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.created_at, oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=? OR oi.farmer_id=? ORDER BY o.id DESC LIMIT 50");
                    $s->execute(array($user_id,$user_id,$user_id));
                } else {
                    $s=$conn->prepare("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.created_at, oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN products p ON p.id=oi.product_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status=? ORDER BY o.id DESC LIMIT 50");
                    $s->execute(array($user_id,$user_id,$user_id,$filter));
                }
                $rows=$s->fetchAll(PDO::FETCH_ASSOC);
            } else {
                if($filter=='all'){
                    $s=$conn->prepare("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.created_at, oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN products p ON p.id=oi.product_id WHERE p.farmer_id=? OR p.user_id=? OR oi.farmer_id=? ORDER BY o.id DESC LIMIT 50");
                    $s->bind_param("iii",$user_id,$user_id,$user_id);
                } else {
                    $s=$conn->prepare("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.created_at, oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON o.id=oi.order_id LEFT JOIN products p ON p.id=oi.product_id WHERE (p.farmer_id=? OR p.user_id=? OR oi.farmer_id=?) AND o.status=? ORDER BY o.id DESC LIMIT 50");
                    $s->bind_param("iiis",$user_id,$user_id,$user_id,$filter);
                }
                $s->execute(); $res=$s->get_result(); if($res) while($r=$res->fetch_assoc()) $rows[]=$r; $s->close();
            }
            if(count($rows)==0){ echo '<p style="text-align:center; color:#666;">Walang order sa '.$filter.' tab.</p>'; }
            else {
                foreach($rows as $r){
                    $status=$r['status']; $badge=$status=='pending'?'#ff9800':($status=='to_ship'?'#2196f3':($status=='shipped'?'#9c27b0':($status=='completed'?'#00b050':'#999')));
                    echo '<div style="border:1px solid #eee; border-radius:12px; padding:12px; margin-bottom:10px; display:flex; justify-content:space-between;"><div><b>Order #'.$r['id'].' - '.htmlspecialchars($r['product_name']).' x '.$r['quantity'].'</b> <span style="background:'.$badge.'; color:#fff; padding:3px 10px; border-radius:20px; font-size:0.7rem;">'.$status.'</span><br><span style="font-size:0.85rem;">'.htmlspecialchars($r['customer_name']).' - '.htmlspecialchars($r['phone']).'</span></div><div><b>P'.number_format($r['price']*$r['quantity'],2).'</b><br>';
                    if($status=='pending') echo '<a href="farmer_dashboard.php?action=confirm&id='.$r['id'].'" style="background:#2a9d8f; color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:800; display:block; text-align:center; margin-top:6px;">Confirm Order</a>';
                    elseif($status=='to_ship') echo '<a href="farmer_dashboard.php?action=ship&id='.$r['id'].'" style="background:#111; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:800; display:block; text-align:center; margin-top:6px;">Ship Now</a>';
                    elseif($status=='shipped') echo '<a href="farmer_dashboard.php?action=complete&id='.$r['id'].'" style="background:#00b050; color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; display:block; text-align:center; margin-top:6px;">Mark Delivered</a>';
                    echo '</div></div>';
                }
            }
        } catch(Exception $e){ echo '<p>No orders: '.htmlspecialchars($e->getMessage()).'</p>'; }
       ?>
    </div>
    <?php endif;?>
    <?php if($tab=='add'):?>
    <div style="background:#fff; border-radius:16px; border:1px solid #e5e5e5; padding:18px;">
        <h3>Magdagdag ng Bagong Product</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="name" required placeholder="Product Name" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin-bottom:10px;">
            <input type="number" step="0.01" name="price" required placeholder="Price" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin-bottom:10px;">
            <input type="text" name="unit" value="kg" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin-bottom:10px;">
            <input type="number" name="stock" value="100" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin-bottom:10px;">
            <input type="file" name="image" accept="image/*" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin-bottom:10px;">
            <button type="submit" name="add_product" style="width:100%; background:#2a9d8f; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900;">I-save Product</button>
        </form>
    </div>
    <?php endif;?>
</div>
<?php include 'footer.php';?>
