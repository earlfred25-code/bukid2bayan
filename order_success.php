<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid_cookie = $_COOKIE['user_id'];
    try{
        $is_tmp = $conn instanceof PDO;
        if($is_tmp){
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->execute([$uid_cookie]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->bind_param("i", $uid_cookie);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
        }
        if($u){
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['username'];
            $_SESSION['role'] = $u['role']?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin']?? 0;
        }
    }catch(Exception $e){}
}

$order_id = (int)($_GET['id'] ?? 0);
$user_id = (int)($_SESSION['user_id'] ?? $_COOKIE['user_id'] ?? 0);
$is_pdo = $conn instanceof PDO;
$order = null;
$items = [];

if($order_id > 0 && $user_id > 0){
    try {
        if($is_pdo){
            $s=$conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
            $s->execute([$order_id,$user_id]);
            $order=$s->fetch(PDO::FETCH_ASSOC);
            $s=$conn->prepare("SELECT * FROM order_items WHERE order_id=?");
            $s->execute([$order_id]);
            $items=$s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $s=$conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
            $s->bind_param("ii",$order_id,$user_id);
            $s->execute();
            $res=$s->get_result();
            if($res) $order=$res->fetch_assoc();
            $s->close();

            $s2=$conn->prepare("SELECT * FROM order_items WHERE order_id=?");
            $s2->bind_param("i",$order_id);
            $s2->execute();
            $res2=$s2->get_result();
            if($res2) while($r=$res2->fetch_assoc()) $items[]=$r;
            $s2->close();
        }
    } catch(Exception $e){}
}

include 'header.php';
?>
<style>
.success-wrap{ max-width:640px; margin:20px auto 40px auto; padding:0 16px; }
@media(max-width:600px){ .success-wrap{ margin-top:10px; } }
</style>
<div class="success-wrap">
<div style="background:#fff; border:1px solid #e9e9e9; border-radius:20px; padding:32px; text-align:center;">
    <div style="width:72px; height:72px; background:#e6f7f5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px auto; font-size:2rem; color:#2a9d8f;">✓</div>
    <h1 style="font-weight:900; margin:0; color:#111; font-size:clamp(1.5rem,5vw,2rem);">Order Placed!</h1>
    <p style="color:#666; margin:8px 0 0 0;">Order <b>#<?= $order_id ?></b> - Na-receive na namin. I-prepare na ng farmer yung gulay mo.</p>
    <?php if($order): ?>
    <div style="text-align:left; background:#fafafa; border:1px solid #eee; border-radius:12px; padding:14px; margin-top:20px;">
        <div style="font-size:0.9rem; color:#444; margin-bottom:8px;"><b><?= htmlspecialchars($order['customer_name']??'') ?></b> • <?= htmlspecialchars($order['phone']??'') ?><br><?= htmlspecialchars($order['address']??'') ?></div>
        <?php foreach($items as $it): ?>
        <div style="display:flex; justify-content:space-between; font-size:0.9rem; padding:5px 0; border-top:1px solid #eee;">
            <span><?= htmlspecialchars($it['product_name']) ?> x <?= (int)$it['quantity'] ?></span>
            <span>₱<?= number_format($it['price']*$it['quantity'],2) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex; justify-content:space-between; font-weight:900; margin-top:10px; padding-top:10px; border-top:1.5px solid #111;">
            <span>Total</span><span>₱<?= number_format($order['total_amount']??0,2) ?></span>
        </div>
    </div>
    <?php else: ?>
    <div style="margin-top:20px; background:#fff8e1; border:1px solid #ffe082; border-radius:12px; padding:12px; font-size:0.9rem; color:#6d4c00;">
        Hindi namin mahanap details ng order mo, pero na-place na yan. Check mo sa <a href="my_orders.php" style="font-weight:800; color:#111;">My Orders</a>.
    </div>
    <?php endif; ?>
    <div style="margin-top:22px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
        <a href="my_orders.php" style="background:#111; color:#fff; padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-box"></i> Tignan Order Ko</a>
        <a href="products.php" style="background:#fff; border:1.5px solid #111; color:#111; padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:800;">Continue Shopping</a>
    </div>
</div>
</div>
<?php include 'footer.php'; ?>
