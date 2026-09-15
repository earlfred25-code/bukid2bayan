<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();
include 'db_connect.php';
include 'header.php';
$cart = $_SESSION['cart'] ?? [];
?>
<style>
.cart-wrap{ max-width:1120px; margin:20px auto 40px auto; padding:0 16px; }
.cart-title-box{ background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:18px 20px; margin-bottom:16px; }
.cart-card{ background:#fff; border:1px solid #e9e9e9; border-radius:16px; overflow:hidden; }
.cart-item{
    display:flex; gap:14px; padding:16px; border-bottom:1px solid #f0f0f0;
    align-items:center; flex-wrap:wrap;
}
.cart-item img{ width:86px; height:86px; object-fit:cover; border-radius:12px; border:1px solid #eee; flex-shrink:0; }
.cart-item-info{ flex:1; min-width:180px; }
.cart-actions{ display:flex; align-items:center; gap:8px; flex-wrap:wrap; width:100%; }
.qty-box{ display:flex; align-items:center; gap:6px; background:#fafafa; padding:6px; border-radius:12px; border:1px solid #e9e9e9; }
.qty-box button{ width:36px; height:38px; font-weight:900; border-radius:8px; cursor:pointer; }
.qty-box input{ width:56px; font-weight:800; padding:7px; border:1.5px solid #111; border-radius:8px; text-align:center; }

@media(max-width:600px){
    .cart-wrap{ padding:0 12px; margin-top:10px; }
    .cart-item{ flex-direction:row; align-items:flex-start; }
    .cart-item-info{ min-width:120px; }
    .cart-actions{ margin-top:8px; }
    .cart-total-bar{ flex-direction:column; align-items:stretch !important; }
    .cart-total-bar a{ width:100%; text-align:center; }
}
</style>

<div class="cart-wrap">
  <div class="cart-title-box">
    <h1 style="font-size:clamp(1.4rem, 4vw, 1.9rem); font-weight:900; margin:0; color:#111; letter-spacing:-0.5px;">Iyong Cart 🛒</h1>
    <p style="margin:6px 0 0 0; color:#666; font-size:0.9rem;">Review mo muna bago magbayad</p>
  </div>

  <?php if(empty($cart)): ?>
    <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:60px 20px; text-align:center;">
      <div style="font-size:3rem; margin-bottom:10px;">🥕</div>
      <p style="font-size:1.2rem; font-weight:800; color:#111; margin:0;">Wala pa laman</p>
      <p style="color:#666; margin:8px 0 18px 0;">Magdagdag ka muna ng gulay</p>
      <a href="products.php" style="background:#2d7a3e; color:#fff; padding:12px 24px; border-radius:12px; text-decoration:none; font-weight:800; display:inline-block;">Mamili Na</a>
    </div>
  <?php else:
    $ids = implode(',', array_map('intval', array_keys($cart)));
    if($ids == '') $ids = '0';
    
    try {
        $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
        $products = [];
        if($result){
            if(method_exists($result, 'fetch_assoc')){
                while($r = $result->fetch_assoc()){ $products[] = $r; }
            } else {
                $products = $result->fetchAll(PDO::FETCH_ASSOC);
            }
        }
        $total = 0;
    } catch(Exception $e){
        $products = [];
        $total = 0;
        echo '<p style="color:red;">Error: '.htmlspecialchars($e->getMessage()).'</p>';
    }
  ?>
  <div class="cart-card">
    <?php foreach($products as $p):
      $pid = $p['id']; 
      $qty = $cart[$pid]['quantity'] ?? $cart[$pid] ?? 1;
      if(is_array($qty)) $qty = $qty['quantity'] ?? 1;
      $qty = (int)$qty;
      $price = (float)($p['price'] ?? 0);
      $sub = $price * $qty; 
      $total += $sub;
      $img = $p['image_url'] ?? '';
      $name = $p['name'] ?? 'Product';
      $unit = $p['unit'] ?? 'pc';
    ?>
    <div class="cart-item">
      <img src="<?= !empty($img) ? htmlspecialchars($img) : 'https://via.placeholder.com/86?text=Gulay' ?>" onerror="this.src='https://via.placeholder.com/86?text=Gulay'" alt="">
      <div class="cart-item-info">
        <b style="font-size:1.05rem; color:#111;"><?= htmlspecialchars($name) ?></b><br>
        <span style="font-size:0.92rem; color:#333;">
          ₱<?= number_format($price,2) ?> × <b style="background:#f6f6f6; padding:3px 8px; border-radius:8px; border:1px solid #e9e9e9;"><?= $qty ?> <?= htmlspecialchars($unit) ?></b> = <b>₱<?= number_format($sub,2) ?></b>
        </span>
      </div>

      <div class="cart-actions">
        <form action="cart_handler.php" method="post" style="display:flex; align-items:center; margin:0;">
          <input type="hidden" name="product_id" value="<?= $pid ?>">
          <input type="hidden" name="action" value="update">
          <div class="qty-box">
            <button type="button" onclick="let i=this.nextElementSibling; i.value=Math.max(1, parseInt(i.value||1)-1)" style="background:#fff; border:1px solid #ddd;">−</button>
            <input type="number" name="quantity" value="<?= $qty ?>" min="1" max="999">
            <button type="button" onclick="let i=this.previousElementSibling; i.value=parseInt(i.value||0)+1" style="background:#111; color:#fff; border:none;">+</button>
            <button type="submit" style="background:#111; color:#fff; border:none; padding:9px 14px; border-radius:8px; font-weight:700; margin-left:4px;">Update</button>
          </div>
        </form>

        <form action="cart_handler.php" method="post" style="margin:0;">
          <input type="hidden" name="product_id" value="<?= $pid ?>"><input type="hidden" name="action" value="remove">
          <button style="color:#c00; background:#fff0f0; border:1px solid #ffcaca; padding:8px 12px; border-radius:8px; font-weight:700; cursor:pointer;">Tanggalin</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>
    
    <div class="cart-total-bar" style="display:flex; justify-content:space-between; align-items:center; padding:18px 16px; flex-wrap:wrap; gap:12px; background:#f9faf5;">
        <span style="font-size:0.9rem; color:#2d7a3e; font-weight:600; background:#e8f5e9; padding:6px 12px; border-radius:20px;">🚚 Free delivery pag ₱500 pataas</span>
        <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
            <span style="font-size:1.4rem; font-weight:900; color:#111;">Total: ₱<?= number_format($total,2) ?></span>
            <a href="checkout.php" style="background:#111; color:#fff; padding:13px 26px; border-radius:12px; text-decoration:none; font-weight:800;">Magbayad Na →</a>
        </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
