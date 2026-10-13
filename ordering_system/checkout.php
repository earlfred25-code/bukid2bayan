<?php
ob_start();
ini_set('session.save_path', sys_get_temp_dir());
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid_cookie = (int)$_COOKIE['user_id'];
    try{
        $is_tmp = $conn instanceof PDO;
        if($is_tmp){
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

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_COOKIE['user_id'])) {
    header('Location: login.php'); exit();
}
if (empty($_SESSION['cart'])) {
    header('Location: cart.php'); exit();
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (isset($_SESSION['user']['id']) ? $_SESSION['user']['id'] : (isset($_COOKIE['user_id']) ? (int)$_COOKIE['user_id'] : 0));
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : (isset($_COOKIE['user_name']) ? $_COOKIE['user_name'] : ''));
$is_pdo = $conn instanceof PDO;
$is_pgsql = $is_pdo && $conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';

try {
    if ($is_pgsql) {
        $conn->exec("CREATE TABLE IF NOT EXISTS orders (id SERIAL PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_items (id SERIAL PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
        $conn->exec("CREATE TABLE IF NOT EXISTS order_tracking (id SERIAL PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, customer_name VARCHAR(255), phone VARCHAR(50), address TEXT, total_amount DECIMAL(10,2), payment_method VARCHAR(50), status VARCHAR(20) DEFAULT 'pending', tracking_number VARCHAR(50), courier VARCHAR(50) DEFAULT 'BUKID2BAYAN Xpress', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
        $conn->query("CREATE TABLE IF NOT EXISTS order_items (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, product_id INT, product_name VARCHAR(255), price DECIMAL(10,2), quantity INT, farmer_id INT NULL)");
        $conn->query("CREATE TABLE IF NOT EXISTS order_tracking (id INT AUTO_INCREMENT PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    }
} catch(Exception $e){}

$cart = $_SESSION['cart'];
$ids = array_map('intval', array_keys($cart));
$products = array(); $subtotal = 0;

if(!empty($ids)){
    try {
        if($is_pdo){
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $conn->prepare("SELECT id, name, price, farmer_id, user_id FROM products WHERE id IN ($ph)");
            $stmt->execute($ids);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($rows as $r){ $products[$r['id']] = $r; }
        } else {
            $ph = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids));
            $stmt = $conn->prepare("SELECT id, name, price, farmer_id, user_id FROM products WHERE id IN ($ph)");
            if($stmt){
                $stmt->bind_param($types, ...$ids);
                $stmt->execute();
                $res = $stmt->get_result();
                while($r=$res->fetch_assoc()){ $products[$r['id']] = $r; }
                $stmt->close();
            }
        }
        foreach($cart as $pid=>$data){
            $qty = is_array($data) ? (isset($data['quantity']) ? $data['quantity'] : 1) : (int)$data;
            if(isset($products[$pid])) $subtotal += $products[$pid]['price'] * $qty;
        }
    } catch(Exception $e){ $products = array(); }
}
$delivery = $subtotal >= 500 ? 0 : 50;
$total = $subtotal + $delivery;

if(isset($_POST['place_order'])){
    $name = isset($_POST['full_name']) ? trim($_POST['full_name']) : $user_name;
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : ''; 
    $payment = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'COD';
    
    if($name=='' || $phone=='' || $address==''){
        $error = "Kumpletuhin mo delivery details.";
    } else {
        try {
            if($is_pdo){
                $stmt=$conn->prepare("INSERT INTO orders (user_id,customer_name,phone,address,total_amount,payment_method,status) VALUES (?,?,?,?,?,?,'pending')");
                $stmt->execute(array($user_id,$name,$phone,$address,$total,$payment));
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
                $qty = is_array($data) ? (isset($data['quantity']) ? $data['quantity'] : 1) : (int)$data;
                $p=$products[$pid]; 
                $fid = isset($p['farmer_id']) && $p['farmer_id'] ? (int)$p['farmer_id'] : (isset($p['user_id']) ? (int)$p['user_id'] : 0);
                if($is_pdo){
                    $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,farmer_id) VALUES (?,?,?,?,?,?)");
                    $stmt->execute(array($order_id,$pid,$p['name'],$p['price'],$qty,$fid));
                } else {
                    $stmt=$conn->prepare("INSERT INTO order_items (order_id,product_id,product_name,price,quantity,farmer_id) VALUES (?,?,?,?,?,?)");
                    $stmt->bind_param("iisdii",$order_id,$pid,$p['name'],$p['price'],$qty,$fid);
                    $stmt->execute(); $stmt->close();
                }
            }
            $_SESSION['cart']=array(); 
            header("Location: order_success.php?id=$order_id"); exit();
        } catch(Exception $e){
            $error = "Order failed: ".$e->getMessage();
        }
    }
}

include 'header.php';
?>
<style>
.checkout-wrap{ max-width:1120px; margin:20px auto 40px auto; padding:0 16px; }
.checkout-grid{ display:grid; grid-template-columns:1.2fr 0.8fr; gap:20px; }
.co-card{ background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:22px; }
.input-field{ width:100%; padding:12px; border:1.5px solid #ddd; border-radius:10px; margin:6px 0 14px 0; font-size:0.95rem; outline:none; }
.input-field:focus{ border-color:#2a9d8f; }
@media(max-width:900px){ .checkout-grid{ grid-template-columns:1fr; } .checkout-wrap{ margin-top:10px; padding:0 12px; } }
</style>

<div class="checkout-wrap">
  <div style="background: linear-gradient(135deg,#1a2e35 0%,#2a9d8f 100%); color:#fff; border-radius:20px; padding:20px; margin-bottom:16px;">
    <h1 style="margin:0; font-weight:900; font-size:1.6rem;">Checkout 🧺</h1>
    <p style="margin:6px 0 0 0; opacity:0.9; font-size:0.9rem;">Direct from farmer • Fresh • Free delivery pag ₱500+</p>
  </div>

  <div class="checkout-grid">
    <div class="co-card">
      <h2 style="font-weight:900; margin:0 0 16px 0; color:#111;">Delivery Details</h2>
      <?php if(isset($error)): ?><div style="background:#ffebee; color:#c62828; padding:12px; border-radius:10px; margin-bottom:14px; border:1px solid #ffcdd2;"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
      <form method="post">
        <label style="font-weight:700; font-size:0.9rem;">Full Name</label>
        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_name); ?>" required class="input-field">
        <label style="font-weight:700; font-size:0.9rem;">Phone *</label>
        <input type="text" name="phone" required placeholder="09xx xxx xxxx" class="input-field">
        <label style="font-weight:700; font-size:0.9rem;">Address *</label>
        <textarea name="address" required placeholder="House, Street, Brgy, City, Province" class="input-field" style="min-height:90px;"></textarea>
        <label style="font-weight:700; font-size:0.9rem;">Payment</label>
        <select name="payment_method" class="input-field">
          <option value="COD">Cash on Delivery</option><option value="GCash">GCash</option><option value="Pickup">Pick-up sa Farm</option>
        </select>
        <button type="submit" name="place_order" style="width:100%; background:#2d7a3e; color:#fff; border:none; padding:14px; border-radius:12px; font-weight:900; cursor:pointer; font-size:1rem;">Place Order - ₱<?php echo number_format($total,2); ?></button>
        <p style="font-size:0.8rem; color:#666; text-align:center; margin-top:10px;">Makikita agad ni farmer order mo sa Farmer Dashboard</p>
      </form>
    </div>
    <div class="co-card" style="height:fit-content;">
      <h3 style="font-weight:900; margin:0 0 14px 0;">Summary</h3>
      <?php foreach($cart as $pid=>$data): if(!isset($products[$pid])) continue; $qty=is_array($data)?(isset($data['quantity'])?$data['quantity']:1):(int)$data; ?>
        <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.92rem;"><span><?php echo htmlspecialchars($products[$pid]['name']); ?> x <?php echo $qty; ?></span><b>₱<?php echo number_format($products[$pid]['price']*$qty,2); ?></b></div>
      <?php endforeach; ?>
      <hr style="border:none; border-top:1px solid #eee; margin:12px 0;">
      <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><span>₱<?php echo number_format($subtotal,2); ?></span></div>
      <div style="display:flex; justify-content:space-between; margin-top:6px;"><span>Delivery</span><span style="font-weight:700; color:<?php echo $delivery==0?'#2d7a3e':'#111'; ?>"><?php echo $delivery==0?'FREE':'₱'.$delivery; ?></span></div>
      <div style="display:flex; justify-content:space-between; margin-top:12px; font-weight:900; font-size:1.15rem;"><span>Total</span><span>₱<?php echo number_format($total,2); ?></span></div>
      <div style="font-size:0.8rem; color:#2d7a3e; margin-top:12px; background:#e8f5e9; padding:10px; border-radius:8px; text-align:center;">🚚 <?php echo $delivery==0?'FREE delivery!':'Add ₱'.number_format(500-$subtotal,2).' pa para FREE delivery'; ?></div>
    </div>
  </div>
</div>
<?php include 'footer.php'; ?>
