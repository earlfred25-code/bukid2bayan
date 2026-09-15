<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? $_SESSION['user']['name'] ?? 'Farmer';
$is_pdo = $conn instanceof PDO;
$is_pgsql = $is_pdo && $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

if (!function_exists('addTracking')) {
    function addTracking($conn, $oid, $status, $loc, $desc){
        try {
            $is_pdo = $conn instanceof PDO;
            if($is_pdo){
                $conn->exec("CREATE TABLE IF NOT EXISTS order_tracking (id SERIAL PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
                $s=$conn->prepare("INSERT INTO order_tracking (order_id,status,location,description) VALUES (?,?,?,?)");
                $s->execute([$oid,$status,$loc,$desc]);
            } else {
                $conn->query("CREATE TABLE IF NOT EXISTS order_tracking (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
                $s=$conn->prepare("INSERT INTO order_tracking (order_id,status,location,description) VALUES (?,?,?,?)");
                $s->bind_param("isss",$oid,$status,$loc,$desc);
                $s->execute(); $s->close();
            }
        } catch(Exception $e){}
    }
}

try {
    if ($is_pgsql) {
        $conn->exec("CREATE TABLE IF NOT EXISTS products (id SERIAL PRIMARY KEY, name VARCHAR(255), farmer_name VARCHAR(100), price DECIMAL(10,2), unit VARCHAR(20), image_url TEXT, farmer_id INT, user_id INT, stock INT DEFAULT 0)");
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (id SERIAL PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', farmer_lat DECIMAL(10,7) DEFAULT 14.3320, farmer_lng DECIMAL(10,7) DEFAULT 121.0850, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (id SERIAL PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), farmer_name VARCHAR(100), price DECIMAL(10,2), unit VARCHAR(20), image_url TEXT, farmer_id INT, user_id INT, stock INT DEFAULT 0)");
        $conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', farmer_lat DECIMAL(10,7) DEFAULT 14.3320, farmer_lng DECIMAL(10,7) DEFAULT 121.0850, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->query("CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
    }
} catch(Exception $e){}

if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $unit = trim($_POST['unit']);
    $image = trim($_POST['image_url']);
    $stock = (int)($_POST['stock'] ?? 0);
    if ($name != '' && $price > 0) {
        try {
            if($is_pdo){
                $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, price, unit, image_url, farmer_id, user_id, stock) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$name,$user_name,$price,$unit,$image,$user_id,$user_id,$stock]);
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, price, unit, image_url, farmer_id, user_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdssiii", $name, $user_name, $price, $unit, $image, $user_id, $user_id, $stock);
                $stmt->execute(); $stmt->close();
            }
            $_SESSION['flash']['success'] = "$name na-add na sa Farmer Centre!";
            header('Location: farmer_centre.php'); exit();
        } catch(Exception $e){}
    }
}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    try {
        if($is_pdo){
            $stmt=$conn->prepare("DELETE FROM products WHERE id=? AND (farmer_id=? OR user_id=?)");
            $stmt->execute([$del_id,$user_id,$user_id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND (farmer_id = ? OR user_id = ?)");
            $stmt->bind_param("iii", $del_id, $user_id, $user_id);
            $stmt->execute(); $stmt->close();
        }
    } catch(Exception $e){}
    header('Location: farmer_centre.php'); exit();
}

if (isset($_GET['action']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $act = $_GET['action'];
    try {
        if($act=='confirm'){
            $conn->query("UPDATE orders SET status='to_ship' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
            addTracking($conn, $oid, 'to_ship', 'Binan Farmer Centre', 'Seller confirmed order - preparing to ship');
        }
        if($act=='ship'){
            $track = 'SPXPH'.rand(1000000000,9999999999);
            $conn->query("UPDATE orders SET status='shipped', tracking_number='$track', courier='BUKID2BAYAN Xpress' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
            addTracking($conn, $oid, 'shipped', 'Binan Sorting Hub', "Parcel departed - tracking $track assigned");
            addTracking($conn, $oid, 'shipped', 'Calamba Hub - In Transit', 'Parcel inbounded at logistics facility');
        }
        if($act=='complete'){
            $conn->query("UPDATE orders SET status='completed' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
            addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered - Order Completed');
        }
        if($act=='cancel'){
            $conn->query("UPDATE orders SET status='cancelled' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
            addTracking($conn, $oid, 'cancelled', 'Cancelled', 'Order cancelled by farmer');
        }
    } catch(Exception $e){}
    header('Location: farmer_centre.php?filter='.$act); exit();
}

if (isset($_GET['complete'])) {
    $oid = (int)$_GET['complete'];
    try {
        $conn->query("UPDATE orders SET status='completed' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
        addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered');
    } catch(Exception $e){}
    header('Location: farmer_centre.php'); exit();
}

$total_products = 0;
$total_orders = 0;
$total_earnings = 0;

try {
    if($is_pdo){
        $stmt=$conn->prepare("SELECT COUNT(*) FROM products WHERE farmer_id=? OR user_id=?");
        $stmt->execute([$user_id,$user_id]);
        $total_products = (int)$stmt->fetchColumn();
        $stmt=$conn->prepare("SELECT COUNT(DISTINCT order_id) as c, SUM(price*quantity) as total FROM order_items WHERE farmer_id=?");
        $stmt->execute([$user_id]);
        $r=$stmt->fetch(PDO::FETCH_ASSOC);
        $total_orders = $r['c'] ?? 0;
        $total_earnings = $r['total'] ?? 0;
    } else {
        $stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE farmer_id = ? OR user_id = ?");
        $stmt->bind_param("ii", $user_id, $user_id);
        $stmt->execute(); $res=$stmt->get_result();
        if($res) $total_products=$res->fetch_assoc()['c']; $stmt->close();
        $res=$conn->query("SELECT COUNT(DISTINCT order_id) as c, SUM(price*quantity) as total FROM order_items WHERE farmer_id=$user_id");
        if($res){ $r=$res->fetch_assoc(); $total_orders=$r['c']??0; $total_earnings=$r['total']??0; }
    }
} catch(Exception $e){}

$filter = $_GET['filter'] ?? 'all';
include 'header.php';
?>
<style>
.fc-wrap{ max-width:1120px; margin:20px auto; padding:0 16px; }
.fc-stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:16px; }
.fc-tabs{ background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:12px 14px; margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap; overflow-x:auto; }
.fc-grid{ display:grid; grid-template-columns:380px 1fr; gap:20px; }
@media(max-width:900px){
    .fc-stats{ grid-template-columns:1fr; }
    .fc-grid{ grid-template-columns:1fr; }
    .fc-wrap{ margin-top:10px; }
}
</style>

<div class="fc-wrap">
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div style="background:#e6f7f5; border:1.5px solid #2a9d8f; color:#0f3d37; padding:14px; border-radius:10px; margin-bottom:18px; text-align:center; font-weight:700;">
            <?php echo htmlspecialchars($_SESSION['flash']['success']); unset($_SESSION['flash']['success']); ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:28px; margin-bottom:20px;">
        <h1 style="font-size:clamp(1.6rem,4vw,2.4rem); font-weight:900; margin:0;"><i class="fas fa-store" style="color:#2a9d8f;"></i> Farmer Centre</h1>
        <p style="color:#666; margin-top:8px;">Welcome, <?= htmlspecialchars($user_name) ?>! Parang Shopee Seller + Tracking System</p>
    </div>

    <div class="fc-stats">
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#2a9d8f;"><?= $total_products ?></p>
            <p style="font-weight:800; margin:4px 0 0 0;">My Products</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#e76f51;"><?= $total_orders ?></p>
            <p style="font-weight:800; margin:4px 0 0 0;">Total Orders Sayo</p>
        </div>
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; text-align:center;">
            <p style="font-size:2rem; font-weight:900; margin:0; color:#1a2e35;">₱<?= number_format($total_earnings, 2) ?></p>
            <p style="font-weight:800; margin:4px 0 0 0;">Earnings</p>
        </div>
    </div>

    <div class="fc-tabs">
        <?php
        $tabs = ['all'=>'All','pending'=>'To Pay','to_ship'=>'To Ship','shipped'=>'Shipped','completed'=>'Completed','cancelled'=>'Cancelled'];
        foreach($tabs as $k=>$label){
            $active = $filter==$k ? 'background:#111; color:#fff;' : 'background:#f5f5f5; color:#333; border:1px solid #eee;';
            echo "<a href='farmer_centre.php?filter=$k' style='padding:8px 14px; border-radius:20px; text-decoration:none; font-weight:800; font-size:0.85rem; $active'>$label</a>";
        }
        ?>
    </div>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; margin-bottom:20px;">
        <h3 style="font-weight:900; margin:0 0 14px 0;">Incoming Orders - with Tracking</h3>
        <?php
        $where = $filter=='all' ? "" : "AND o.status='$filter'";
        if($filter=='pending') $where = "AND o.status IN ('pending')";
        try {
            $q = $conn->query("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.courier, o.created_at, oi.product_name, oi.quantity, oi.price FROM order_items oi JOIN orders o ON o.id=oi.order_id WHERE oi.farmer_id=$user_id $where ORDER BY o.id DESC LIMIT 50");
            $rows=[];
            if($q){
                if(method_exists($q,'fetch_assoc')){ while($r=$q->fetch_assoc()) $rows[]=$r; }
                else { $rows=$q->fetchAll(PDO::FETCH_ASSOC); }
            }
            if(count($rows)==0){
                echo '<p style="color:#666; text-align:center; padding:16px;">Walang order sa '.$filter.' tab.</p>';
            } else {
                foreach($rows as $r){
                    $status=$r['status'];
                    $badge=$status=='pending'?'#ff9800':($status=='to_ship'?'#2196f3':($status=='shipped'?'#9c27b0':($status=='completed'?'#00b050':'#999')));
                    echo '<div style="display:flex; justify-content:space-between; gap:12px; border:1px solid #eee; border-radius:10px; padding:12px; margin-bottom:10px; flex-wrap:wrap;">
                            <div><b>Order #'.$r['id'].' - '.htmlspecialchars($r['product_name']).' x '.$r['quantity'].'</b> <span style="background:'.$badge.'; color:#fff; padding:2px 8px; border-radius:10px; font-size:0.7rem; font-weight:800; text-transform:uppercase;">'.$status.'</span>';
                    if($r['tracking_number']) echo '<span style="background:#111; color:#fff; padding:2px 8px; border-radius:8px; font-size:0.7rem; margin-left:4px;">'.$r['tracking_number'].'</span>';
                    echo '<br><span style="font-size:0.85rem; color:#333;">'.htmlspecialchars($r['customer_name']).' - '.htmlspecialchars($r['phone']).'<br>'.htmlspecialchars($r['address']).'</span><br><span style="font-size:0.8rem; color:#666;">'.$r['created_at'].' • '.$r['payment_method'].'</span></div>
                            <div style="text-align:right; min-width:160px;"><b>₱'.number_format($r['price']*$r['quantity'],2).'</b><br>';
                    if($status=='pending'){
                        echo '<a href="farmer_centre.php?action=confirm&id='.$r['id'].'" style="background:#2a9d8f; color:#fff; padding:8px 12px; border-radius:8px; text-decoration:none; font-weight:800; display:block; text-align:center; margin-top:6px;"><i class="fas fa-check"></i> Confirm Order</a>';
                        echo '<a href="farmer_centre.php?action=cancel&id='.$r['id'].'" onclick="return confirm(\'Cancel?\')" style="background:#fff; border:1px solid #fcc; color:#c00; padding:6px 10px; border-radius:8px; text-decoration:none; font-size:0.8rem; display:block; text-align:center; margin-top:6px;">Cancel</a>';
                    } elseif($status=='to_ship'){
                        echo '<a href="farmer_centre.php?action=ship&id='.$r['id'].'" style="background:#111; color:#fff; padding:10px 14px; border-radius:8px; text-decoration:none; font-weight:800; display:block; text-align:center; margin-top:6px;"><i class="fas fa-truck"></i> Ship Now + Tracking</a>';
                    } elseif($status=='shipped'){
                        echo '<div style="background:#f3e8ff; color:#7c3aed; padding:8px 10px; border-radius:8px; font-weight:700; text-align:center;"><i class="fas fa-box"></i> Shipped na</div>';
                        echo '<a href="order_tracking.php?id='.$r['id'].'" style="margin-top:6px; background:#fff; border:1.5px solid #111; color:#111; padding:6px 10px; border-radius:8px; text-decoration:none; font-size:0.8rem; display:block; text-align:center; font-weight:700;"><i class="fas fa-map-marker-alt"></i> Track Logistics</a>';
                        echo '<a href="farmer_centre.php?action=complete&id='.$r['id'].'" style="margin-top:6px; background:#00b050; color:#fff; padding:6px 10px; border-radius:8px; text-decoration:none; font-size:0.8rem; display:block; text-align:center;">Mark Completed</a>';
                    } else {
                        echo '<a href="order_tracking.php?id='.$r['id'].'" style="margin-top:6px; background:#fff; border:1px solid #111; padding:6px 10px; border-radius:8px; text-decoration:none; font-size:0.8rem; display:block; text-align:center; font-weight:700;"><i class="fas fa-map"></i> Track</a>';
                    }
                    echo '</div></div>';
                }
            }
        } catch(Exception $e){ echo '<p style="color:#666; text-align:center;">No orders found</p>'; }
        ?>
    </div>

    <div class="fc-grid">
        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; height:fit-content;">
            <h3 style="font-weight:900; margin:0 0 14px 0;">Add New Product</h3>
            <form method="post">
                <input type="text" name="name" placeholder="Product Name ex: Lettuce" required style="width:100%; padding:12px; border:2px solid #ddd; border-radius:10px; margin-bottom:10px; font-weight:700;">
                <div style="display:flex; gap:8px;">
                    <input type="number" step="0.01" name="price" placeholder="Price" required style="flex:1; padding:12px; border:2px solid #ddd; border-radius:10px; margin-bottom:10px; font-weight:700;">
                    <input type="text" name="unit" placeholder="kg / tray" value="kg" required style="width:90px; padding:12px; border:2px solid #ddd; border-radius:10px; margin-bottom:10px; font-weight:700;">
                </div>
                <input type="number" name="stock" placeholder="Stock ilan piraso" style="width:100%; padding:12px; border:2px solid #ddd; border-radius:10px; margin-bottom:10px; font-weight:700;">
                <input type="text" name="image_url" placeholder="Image URL https://..." style="width:100%; padding:12px; border:2px solid #ddd; border-radius:10px; margin-bottom:14px;">
                <button type="submit" name="add_product" style="width:100%; background:#2a9d8f; color:#fff; border:none; padding:14px; border-radius:10px; font-weight:900; cursor:pointer;"><i class="fas fa-plus"></i> Add Product</button>
            </form>
        </div>

        <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px;">
            <h3 style="font-weight:900; margin:0 0 14px 0;">My Products</h3>
            <div style="display:grid; gap:12px;">
            <?php
            try {
                if($is_pdo){
                    $stmt=$conn->prepare("SELECT id, name, price, unit, image_url FROM products WHERE farmer_id=? OR user_id=? ORDER BY id DESC");
                    $stmt->execute([$user_id,$user_id]);
                    $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach($result as $row){
                        echo '<div style="display:flex; gap:12px; align-items:center; border:1px solid #eee; border-radius:12px; padding:10px;">';
                        echo '<img src="'.htmlspecialchars($row['image_url']??'').'" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eee;" onerror="this.src=\'https://via.placeholder.com/60\'">';
                        echo '<div style="flex:1;"><b>'.htmlspecialchars($row['name']).'</b><br>₱'.number_format($row['price'],2).' / '.htmlspecialchars($row['unit']).'</div>';
                        echo '<a href="farmer_centre.php?delete='.$row['id'].'" onclick="return confirm(\'Tanggalin?\')" style="color:#c00; font-weight:800; text-decoration:none; padding:8px 12px; border:1px solid #fcc; border-radius:8px;">Delete</a>';
                        echo '</div>';
                    }
                    if(count($result)==0) echo '<p style="color:#666; text-align:center; padding:20px;">Wala ka pa product. Mag-add ka sa kaliwa.</p>';
                } else {
                    $stmt = $conn->prepare("SELECT id, name, price, unit, image_url FROM products WHERE farmer_id = ? OR user_id = ? ORDER BY id DESC");
                    $stmt->bind_param("ii", $user_id, $user_id);
                    $stmt->execute(); $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo '<div style="display:flex; gap:12px; align-items:center; border:1px solid #eee; border-radius:12px; padding:10px;">';
                            echo '<img src="'.htmlspecialchars($row['image_url']).'" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eee;" onerror="this.src=\'https://via.placeholder.com/60\'">';
                            echo '<div style="flex:1;"><b>'.htmlspecialchars($row['name']).'</b><br>₱'.number_format($row['price'],2).' / '.htmlspecialchars($row['unit']).'</div>';
                            echo '<a href="farmer_centre.php?delete='.$row['id'].'" onclick="return confirm(\'Tanggalin?\')" style="color:#c00; font-weight:800; text-decoration:none; padding:8px 12px; border:1px solid #fcc; border-radius:8px;">Delete</a>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p style="color:#666; text-align:center; padding:20px;">Wala ka pa product. Mag-add ka sa kaliwa.</p>';
                    }
                    $stmt->close();
                }
            } catch(Exception $e){ echo '<p style="color:#666;">No products</p>'; }
            ?>
            </div>
        </div>
    </div>
</div>

<?php 
include 'footer.php'; 
if($is_pdo){ $conn=null; } else { if(method_exists($conn,'close')) $conn->close(); }
?>
