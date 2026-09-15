<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? $_SESSION['user']['name'] ?? 'Farmer';

// --- AUTO FIX PRODUCTS ---
$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    farmer_name VARCHAR(100),
    price DECIMAL(10,2),
    unit VARCHAR(20),
    image_url TEXT,
    farmer_id INT,
    user_id INT,
    stock INT DEFAULT 0
)");
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'farmer_id'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN farmer_id INT NULL");
}
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'user_id'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN user_id INT NULL");
}
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'farmer_name'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN farmer_name VARCHAR(100) NULL");
}
$check = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0");
}

// --- AUTO FIX ORDERS & ORDER_ITEMS + TRACKING ---
$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    total_amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending',
    tracking_number VARCHAR(50),
    courier VARCHAR(50) DEFAULT ' BUKID2BAYAN Xpress',
    farmer_lat DECIMAL(10,7) DEFAULT 14.3320,
    farmer_lng DECIMAL(10,7) DEFAULT 121.0850,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$conn->query("CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    price DECIMAL(10,2),
    quantity INT,
    farmer_id INT NULL
)");
$check = $conn->query("SHOW COLUMNS FROM order_items LIKE 'farmer_id'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE order_items ADD COLUMN farmer_id INT NULL");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'customer_name'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN customer_name VARCHAR(255)");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'phone'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN phone VARCHAR(50)");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'address'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN address TEXT");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'total_amount'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN total_amount DECIMAL(10,2)");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'pending'");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'tracking_number'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN tracking_number VARCHAR(50)");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'courier'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN courier VARCHAR(50) DEFAULT ' BUKID2BAYAN Xpress'");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'farmer_lat'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN farmer_lat DECIMAL(10,7) DEFAULT 14.3320");
}
$check = $conn->query("SHOW COLUMNS FROM orders LIKE 'farmer_lng'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE orders ADD COLUMN farmer_lng DECIMAL(10,7) DEFAULT 121.0850");
}

if (isset($_POST['add_product'])) {
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $unit = trim($_POST['unit']);
    $image = trim($_POST['image_url']);
    $stock = (int)($_POST['stock'] ?? 0);
    if ($name != '' && $price > 0) {
        $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, price, unit, image_url, farmer_id, user_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssiii", $name, $user_name, $price, $unit, $image, $user_id, $user_id, $stock);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash']['success'] = "$name na-add na sa Farmer Centre!";
        header('Location: farmer_centre.php');
        exit();
    }
}

if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND (farmer_id = ? OR user_id = ?)");
    $stmt->bind_param("iii", $del_id, $user_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: farmer_centre.php');
    exit();
}

// --- SHOPEE STYLE + TRACKING ACTION WITH LOGS ---
if (isset($_GET['action']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $act = $_GET['action'];
    if($act=='confirm'){
        $conn->query("UPDATE orders SET status='to_ship' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
        if(function_exists('addTracking')) addTracking($conn, $oid, 'to_ship', 'Binan Farmer Centre', 'Seller confirmed order - preparing to ship like Shopee SPX');
    }
    if($act=='ship'){
        $track = 'SPXPH'.rand(1000000000,9999999999);
        $conn->query("UPDATE orders SET status='shipped', tracking_number='$track', courier=' BUKID2BAYAN Xpress', farmer_lat=14.3320, farmer_lng=121.0850 WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
        if(function_exists('addTracking')){
            addTracking($conn, $oid, 'shipped', 'Binan Sorting Hub', "Parcel departed - tracking $track assigned");
            addTracking($conn, $oid, 'shipped', 'Calamba Hub - In Transit', 'Parcel inbounded at logistics facility');
        }
    }
    if($act=='complete'){
        $conn->query("UPDATE orders SET status='completed' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
        if(function_exists('addTracking')) addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered - Order Completed');
    }
    if($act=='cancel'){
        $conn->query("UPDATE orders SET status='cancelled' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
        if(function_exists('addTracking')) addTracking($conn, $oid, 'cancelled', 'Cancelled', 'Order cancelled by farmer');
    }
    header('Location: farmer_centre.php?filter='.$act);
    exit();
}
if (isset($_GET['complete'])) {
    $oid = (int)$_GET['complete'];
    $conn->query("UPDATE orders SET status='completed' WHERE id=$oid AND id IN (SELECT order_id FROM order_items WHERE farmer_id=$user_id)");
    if(function_exists('addTracking')) addTracking($conn, $oid, 'completed', 'Buyer Location', 'Parcel delivered');
    header('Location: farmer_centre.php');
    exit();
}

$total_products = 0;
$total_orders = 0;
$total_earnings = 0;

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM products WHERE farmer_id = ? OR user_id = ?");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res) $total_products = $res->fetch_assoc()['c'];
$stmt->close();

$res = $conn->query("SELECT COUNT(DISTINCT order_id) as c, SUM(price*quantity) as total FROM order_items WHERE farmer_id = $user_id");
if($res){
    $r=$res->fetch_assoc();
    $total_orders = $r['c'] ?? 0;
    $total_earnings = $r['total'] ?? 0;
}

$filter = $_GET['filter'] ?? 'all';
include 'header.php';
?>

<div class="container" style="max-width:1120px; margin:20px auto; padding:0 20px; margin-top:110px;">
    <?php if (isset($_SESSION['flash']['success'])): ?>
        <div style="background:#e6f7f5; border:1.5px solid #2a9d8f; color:#0f3d37; padding:14px; border-radius:10px; margin-bottom:18px; text-align:center; font-weight:700;">
            <?php echo $_SESSION['flash']['success']; unset($_SESSION['flash']['success']); ?>
        </div>
    <?php endif; ?>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:28px; margin-bottom:20px;">
        <h1 style="font-size:2.4rem; font-weight:900; margin:0;"><i class="fas fa-store" style="color:#2a9d8f;"></i> Farmer Centre</h1>
        <p style="color:#666; margin-top:8px;">Welcome, <?= htmlspecialchars($user_name) ?>! Parang Shopee Seller + Tracking System</p>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:16px;">
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

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:12px 14px; margin-bottom:14px; display:flex; gap:8px; flex-wrap:wrap;">
        <?php
        $tabs = ['all'=>'All','pending'=>'To Pay','to_ship'=>'To Ship','shipped'=>'Shipped','completed'=>'Completed','cancelled'=>'Cancelled'];
        foreach($tabs as $k=>$label){
            $active = $filter==$k ? 'background:#111; color:#fff;' : 'background:#f5f5f5; color:#333; border:1px solid #eee;';
            echo "<a href='farmer_centre.php?filter=$k' style='padding:8px 14px; border-radius:20px; text-decoration:none; font-weight:800; font-size:0.85rem; $active'>$label</a>";
        }
        ?>
    </div>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:20px; margin-bottom:20px;">
        <h3 style="font-weight:900; margin:0 0 12px 0;">Incoming Orders - with Google Maps Tracking</h3>
        <?php
        $where = $filter=='all' ? "" : "AND o.status='$filter'";
        if($filter=='pending') $where = "AND o.status IN ('pending')";
        $q = $conn->query("SELECT o.id, o.customer_name, o.phone, o.address, o.payment_method, o.status, o.tracking_number, o.courier, o.created_at, oi.product_name, oi.quantity, oi.price 
                           FROM order_items oi JOIN orders o ON o.id=oi.order_id 
                           WHERE oi.farmer_id=$user_id $where ORDER BY o.id DESC LIMIT 50");
        if(!$q || $q->num_rows==0){
            echo '<p style="color:#666; text-align:center; padding:16px;">Walang order sa '.$filter.' tab.</p>';
        } else {
            while($r=$q->fetch_assoc()){
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
        ?>
    </div>

    <div style="display:grid; grid-template-columns:380px 1fr; gap:20px;">
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
            $stmt = $conn->prepare("SELECT id, name, price, unit, image_url FROM products WHERE farmer_id = ? OR user_id = ? ORDER BY id DESC");
            $stmt->bind_param("ii", $user_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
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
            ?>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; $conn->close(); ?>