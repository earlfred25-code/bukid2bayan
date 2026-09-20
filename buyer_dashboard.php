<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
include 'header.php';
$is_logged = isset($_SESSION['user_id']) || isset($_SESSION['user']);
if(!$is_logged){
    header("Location: login.php"); exit();
}
$user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0;
$username = $_SESSION['username'] ?? $_SESSION['user']['username'] ?? 'Buyer';
$is_pdo = $conn instanceof PDO;
$orders = [];
try{
    if($is_pdo){
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 10");
        $stmt->execute([$user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC LIMIT 10");
        if($stmt){
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while($r=$res->fetch_assoc()) $orders[]=$r;
            $stmt->close();
        }
    }
}catch(Exception $e){}
?>
<style>
.dashboard{ max-width:1100px; margin:0 auto; padding:24px 16px; }
.card{ background:rgba(255,255,255,0.92); border-radius:14px; padding:18px; box-shadow:0 4px 16px rgba(0,0,0,0.06); margin-bottom:16px; }
</style>
<div class="dashboard">
    <h1 style="font-weight:900; margin:0;">Hello, <?php echo htmlspecialchars($username); ?>!</h1>
    <p style="color:#666; margin:6px 0 20px 0;">Buyer Dashboard - Fresh from farm to your door</p>
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:20px;">
        <div class="card" style="text-align:center;">
            <div style="font-size:1.6rem;">🛒</div>
            <div style="font-weight:800; margin-top:6px;">My Cart</div>
            <a href="cart.php" style="display:inline-block; margin-top:8px; background:#111; color:#fff; padding:8px 16px; border-radius:20px; text-decoration:none; font-size:0.85rem;">View Cart</a>
        </div>
        <div class="card" style="text-align:center;">
            <div style="font-size:1.6rem;">📦</div>
            <div style="font-weight:800; margin-top:6px;">My Orders</div>
            <div style="color:#666; font-size:0.85rem; margin-top:4px;"><?php echo count($orders); ?> orders</div>
        </div>
        <div class="card" style="text-align:center;">
            <div style="font-size:1.6rem;">🌱</div>
            <div style="font-weight:800; margin-top:6px;">Shop Fresh</div>
            <a href="products.php" style="display:inline-block; margin-top:8px; background:#2a9d8f; color:#fff; padding:8px 16px; border-radius:20px; text-decoration:none; font-size:0.85rem;">Browse Gulay</a>
        </div>
    </div>
    <div class="card">
        <h3 style="margin:0 0 12px 0;">Recent Orders</h3>
        <?php if(count($orders)==0): ?>
            <p style="color:#666; text-align:center; padding:20px;">Wala ka pang orders. <a href="products.php" style="color:#2a9d8f; font-weight:700;">Mamili na!</a></p>
        <?php else: ?>
            <?php foreach($orders as $o): ?>
            <div style="display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #eee;">
                <span>#<?php echo $o['id']; ?> - <?php echo $o['status'] ?? 'pending'; ?></span>
                <span style="font-weight:700;">₱<?php echo number_format($o['total'] ?? 0,2); ?></span>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php include 'footer.php'; ?>
