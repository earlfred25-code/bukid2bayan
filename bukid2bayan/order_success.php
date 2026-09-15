<?php
session_start();
include 'db_connect.php';
$order_id = (int)($_GET['id'] ?? 0);
$user_id = (int)($_SESSION['user_id'] ?? 0);

$order = null;
$items = [];
if($order_id > 0){
    $res = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
    if($res) $order = $res->fetch_assoc();
    $res2 = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
    if($res2) while($r=$res2->fetch_assoc()) $items[]=$r;
}

include 'header.php';
?>
<div style="max-width:640px; margin:130px auto 40px auto; background:#fff; border:1px solid #e9e9e9; border-radius:20px; padding:32px; text-align:center;">
    <div style="width:72px; height:72px; background:#e6f7f5; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 14px auto; font-size:2rem; color:#2a9d8f;">✓</div>
    <h1 style="font-weight:900; margin:0; color:#111;">Order Placed!</h1>
    <p style="color:#666; margin:8px 0 0 0;">Order <b>#<?= $order_id ?></b> - Na-receive na namin. I-prepare na ng farmer yung gulay mo.</p>

    <?php if($order): ?>
    <div style="text-align:left; background:#fafafa; border:1px solid #eee; border-radius:12px; padding:14px; margin-top:20px;">
        <div style="font-size:0.9rem; color:#444; margin-bottom:8px;"><b><?= htmlspecialchars($order['customer_name']) ?></b> • <?= htmlspecialchars($order['phone']) ?><br><?= htmlspecialchars($order['address']) ?></div>
        <?php foreach($items as $it): ?>
        <div style="display:flex; justify-content:space-between; font-size:0.9rem; padding:5px 0; border-top:1px solid #eee;">
            <span><?= htmlspecialchars($it['product_name']) ?> x <?= $it['quantity'] ?></span>
            <span>₱<?= number_format($it['price']*$it['quantity'],2) ?></span>
        </div>
        <?php endforeach; ?>
        <div style="display:flex; justify-content:space-between; font-weight:900; margin-top:10px; padding-top:10px; border-top:1.5px solid #111;">
            <span>Total</span><span>₱<?= number_format($order['total_amount'],2) ?></span>
        </div>
    </div>
    <?php endif; ?>

    <div style="margin-top:22px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
        <a href="my_orders.php" style="background:#111; color:#fff; padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:800;"><i class="fas fa-box"></i> Tignan Order Ko</a>
        <a href="products.php" style="background:#fff; border:1.5px solid #111; color:#111; padding:12px 20px; border-radius:10px; text-decoration:none; font-weight:800;">Continue Shopping</a>
    </div>
</div>
<?php include 'footer.php'; ?>