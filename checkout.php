<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
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


$is_pdo = $conn instanceof PDO;
$is_pgsql = $is_pdo && $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

try {
    if ($is_pgsql) {
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (
            id SERIAL PRIMARY KEY,
            user_id INT,
            customer_name VARCHAR(255),
            phone VARCHAR(50),
            address TEXT,
            total_amount DECIMAL(10,2),
            payment_method VARCHAR(50),
            status VARCHAR(20) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (
            id SERIAL PRIMARY KEY,
            order_id INT,
            product_id INT,
            product_name VARCHAR(255),
            price DECIMAL(10,2),
            quantity INT,
            farmer_id INT NULL
        )");
    } else {
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
    }
} catch(Exception $e){ /* ignore kung existing na */ }

$cart = $_SESSION['cart'];
$ids = array_map('intval', array_keys($cart));
$products = []; $subtotal = 0;

if(!empty($ids)){
    $in = implode(',', $ids);
    try {
        $res = $conn->query("SELECT id, name, price, farmer_id, user_id FROM products WHERE id IN ($in)");
        if($res){
            $rows = [];
            if(method_exists($res, 'fetch_assoc')){
                while($r=$res->fetch_assoc()) $rows[] = $r;
            } else {
                $rows = $res->fetchAll(PDO::FETCH_ASSOC);
            }
            foreach($rows as $r){ $products[$r['id']] = $r; }
        }
        foreach($cart as $pid=>$data){
            $qty = is_array($data) ? ($data['quantity'] ?? 1) : (int)$data;
            if(isset($products[$pid])) $subtotal += $products[$pid]['price'] * $qty;
        }
    } catch(Exception $e){ $products = []; }
}
$delivery = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $delivery;

if(isset($_POST['place_order'])){
    $name = trim($_POST['full_name']); 
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']); 
    $payment = $_POST['payment_method'];
    
    try {
        if($is_pdo){
            $stmt=$conn->prepare("INSERT INTO orders (user_id,customer_name,phone,address,total_amount,payment_method,status) VALUES (?,?,?,?,?,?,'pending')");
            $stmt->execute([$user_id,$name,$phone,$address,$total,$payment]);
            $order_id = $conn->lastInsertId();
        } else {
            $stmt=$conn->prepare("INSERT INTO orders (user_id,customer_name,phone,address,total_amount,payment_method,status) VALUES (?,?,?,?,?,?,'pending')");
            $stmt->bind_param("isssds",$user_id,$name,$phone,$address,$total,$payment);
            $stmt->execute(); 
            $order_id=$stmt->insert_id; 
            $stmt->close();
        }
        
        foreach($cart as $pid=>$data){
            if(!isset($products[$pid])) continue;
            $qty = is_array($data) ? ($data['quantity'] ?? 1) : (int)$data;
            $p=$products[$pid]; $fid=$p['farmer_id']??$p['user_id']??0;
            if($is_pdo){
                $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,farmer_id) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$order_id,$pid,$p['name'],$p['price'],$qty,$fid]);
            } else {
                $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,farmer_id) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("iisdii",$order_id,$pid,$p['name'],$p['price'],$qty,$fid);
                $stmt->execute(); $stmt->close();
            }
        }
        $_SESSION['cart']=[]; 
        header("Location: order_success.php?id=$order_id"); exit();
    } catch(Exception $e){
        $error = "Order failed: ".$e->getMessage();
    }
}

include 'header.php';
?>
<style>
.checkout-grid{ max-width:1120px; margin:20px auto 40px auto; padding:0 16px; display:grid; grid-template-columns:1.2fr 0.8fr; gap:20px; }
@media(max-width:900px){
    .checkout-grid{ grid-template-columns:1fr; margin-top:10px; }
}
.input-field{ width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 14px 0; font-size:0.95rem; }
</style>

<div class="checkout-grid">
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:22px;">
    <h2 style="font-weight:900; margin:0 0 16px 0; color:#111;">Delivery Details</h2>
    <?php if(isset($error)): ?><div style="background:#ffebee; color:#c62828; padding:12px; border-radius:10px; margin-bottom:14px; border:1px solid #ffcdd2;"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="post">
      <label style="font-weight:700; font-size:0.9rem;">Full Name</label>
      <input type="text" name="full_name" value="<?= htmlspecialchars($user_name) ?>" required class="input-field">
      <label style="font-weight:700; font-size:0.9rem;">Phone</label>
      <input type="text" name="phone" required placeholder="09xx xxx xxxx" class="input-field">
      <label style="font-weight:700; font-size:0.9rem;">Address</label>
      <textarea name="address" required placeholder="House, Street, Brgy, City" class="input-field" style="min-height:80px;"></textarea>
      <label style="font-weight:700; font-size:0.9rem;">Payment</label>
      <select name="payment_method" class="input-field">
        <option value="COD">Cash on Delivery</option><option value="GCash">GCash</option><option value="Pickup">Pick-up</option>
      </select>
      <button type="submit" name="place_order" style="width:100%; background:#2d7a3e; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:900; cursor:pointer; font-size:1rem;">Place Order - ₱<?= number_format($total,2) ?></button>
    </form>
  </div>
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:20px; height:fit-content;">
    <h3 style="font-weight:900; margin:0 0 14px 0;">Summary</h3>
    <?php foreach($cart as $pid=>$data): if(!isset($products[$pid])) continue; $qty=is_array($data)?($data['quantity']??1):(int)$data; ?>
      <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;"><span><?= htmlspecialchars($products[$pid]['name']) ?> x <?= $qty ?></span><b>₱<?= number_format($products[$pid]['price']*$qty,2) ?></b></div>
    <?php endforeach; ?>
    <hr style="border:none; border-top:1px solid #eee; margin:12px 0;">
    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>₱<?= number_format($subtotal,2) ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-top:6px;"><span>Delivery</span><span style="font-weight:700; color:<?= $delivery==0?'#2d7a3e':'#111' ?>"><?= $delivery==0?'FREE':'₱'.$delivery ?></span></div>
    <div style="display:flex; justify-content:space-between; margin-top:12px; font-weight:900; font-size:1.15rem;"><span>Total</span><span>₱<?= number_format($total,2) ?></span></div>
    <p style="font-size:0.8rem; color:#666; margin-top:12px; background:#f5f7f5; padding:10px; border-radius:8px;">📦 Free delivery pag ₱500 pataas! Direct from farmers.</p>
  </div>
</div>
<?php include 'footer.php'; ?>
