<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_SESSION['loggedin'])) {
    header('Location: login.php'); exit();
}
if (empty($_SESSION['cart'])) {
    header('Location: cart.php'); exit();
}

$user_id = (int)($_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0);
$user_name = $_SESSION['user_name'] ?? $_SESSION['user']['name'] ?? '';

// --- AUTO FIX ORDERS TABLE KAHIT LUMA ---
function ensureColumn($conn, $table, $column, $def){
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if($res && $res->num_rows==0){
        $conn->query("ALTER TABLE `$table` ADD COLUMN `$column` $def");
    }
}

$conn->query("CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_name VARCHAR(255),
    phone VARCHAR(50),
    address TEXT,
    total_amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    status VARCHAR(20) DEFAULT 'pending',
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

// siguraduhin na meron lahat ng column kahit luma na yung table mo
ensureColumn($conn, 'orders', 'user_id', 'INT');
ensureColumn($conn, 'orders', 'customer_name', 'VARCHAR(255)');
ensureColumn($conn, 'orders', 'phone', 'VARCHAR(50)');
ensureColumn($conn, 'orders', 'address', 'TEXT');
ensureColumn($conn, 'orders', 'total_amount', 'DECIMAL(10,2)');
ensureColumn($conn, 'orders', 'payment_method', 'VARCHAR(50) DEFAULT \'COD\'');
ensureColumn($conn, 'orders', 'status', 'VARCHAR(20) DEFAULT \'pending\'');
ensureColumn($conn, 'orders', 'farmer_id', 'INT NULL');
ensureColumn($conn, 'orders', 'created_at', 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP');

ensureColumn($conn, 'order_items', 'order_id', 'INT');
ensureColumn($conn, 'order_items', 'product_id', 'INT');
ensureColumn($conn, 'order_items', 'product_name', 'VARCHAR(255)');
ensureColumn($conn, 'order_items', 'price', 'DECIMAL(10,2)');
ensureColumn($conn, 'order_items', 'quantity', 'INT');
ensureColumn($conn, 'order_items', 'farmer_id', 'INT NULL');

$cart = $_SESSION['cart'];
$ids = array_map('intval', array_keys($cart));
$products = []; $subtotal = 0;

if(!empty($ids)){
    $in = implode(',', $ids);
    $res = $conn->query("SELECT id, name, price, farmer_id, user_id FROM products WHERE id IN ($in)");
    while($r=$res->fetch_assoc()) $products[$r['id']] = $r;
    foreach($cart as $pid=>$data){
        $qty = $data['quantity'] ?? 1;
        if(isset($products[$pid])) $subtotal += $products[$pid]['price'] * $qty;
    }
}
$delivery = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $delivery;

if(isset($_POST['place_order'])){
    $name = trim($_POST['full_name']); 
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']); 
    $payment = $_POST['payment_method'];
    
    $stmt=$conn->prepare("INSERT INTO orders (user_id,customer_name,phone,address,total_amount,payment_method,status) VALUES (?,?,?,?,?,?,'pending')");
    $stmt->bind_param("isssds",$user_id,$name,$phone,$address,$total,$payment);
    $stmt->execute(); 
    $order_id=$stmt->insert_id; 
    $stmt->close();
    
    foreach($cart as $pid=>$data){
        if(!isset($products[$pid])) continue;
        $qty=$data['quantity']??1; $p=$products[$pid]; $fid=$p['farmer_id']??$p['user_id']??0;
        $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,farmer_id) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("iisdii",$order_id,$pid,$p['name'],$p['price'],$qty,$fid);
        $stmt->execute(); $stmt->close();
    }
    $_SESSION['cart']=[]; 
    header("Location: order_success.php?id=$order_id"); exit();
}

include 'header.php';
?>
<div style="max-width:1120px; margin:110px auto 40px auto; padding:0 20px; display:grid; grid-template-columns:1.2fr 0.8fr; gap:20px;">
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:22px;">
    <h2 style="font-weight:900; margin:0 0 16px 0; color:#111;">Delivery Details</h2>
    <form method="post">
      <label style="font-weight:700; font-size:0.9rem;">Full Name</label>
      <input type="text" name="full_name" value="<?= htmlspecialchars($user_name) ?>" required style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 14px 0;">
      <label style="font-weight:700; font-size:0.9rem;">Phone</label>
      <input type="text" name="phone" required placeholder="09xx xxx xxxx" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 14px 0;">
      <label style="font-weight:700; font-size:0.9rem;">Address</label>
      <textarea name="address" required placeholder="House, Street, Brgy, City" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 14px 0; min-height:80px;"></textarea>
      <label style="font-weight:700; font-size:0.9rem;">Payment</label>
      <select name="payment_method" style="width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 20px 0;">
        <option value="COD">Cash on Delivery</option><option value="GCash">GCash</option><option value="Pickup">Pick-up</option>
      </select>
      <button type="submit" name="place_order" style="width:100%; background:#111; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:900; cursor:pointer;">Place Order - ₱<?= number_format($total,2) ?></button>
    </form>
  </div>
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:20px; height:fit-content;">
    <h3 style="font-weight:900; margin:0 0 14px 0;">Summary</h3>
    <?php foreach($cart as $pid=>$data): if(!isset($products[$pid])) continue; $qty=$data['quantity']??1; ?>
      <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;"><span><?= htmlspecialchars($products[$pid]['name']) ?> x <?= $qty ?></span><b>₱<?= number_format($products[$pid]['price']*$qty,2) ?></b></div>
    <?php endforeach; ?>
    <hr style="border:none; border-top:1px solid #eee; margin:12px 0;">
    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>₱<?= number_format($subtotal,2) ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-top:6px;"><span>Delivery</span><span><?= $delivery==0?'FREE':'₱'.$delivery ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-top:12px; font-weight:900; font-size:1.15rem;"><span>Total</span><span>₱<?= number_format($total,2) ?></span></div>
  </div>
</div>
<?php include 'footer.php'; ?>