<?php
session_start();
include 'db_connect.php';
include 'header.php';
$cart = $_SESSION['cart'] ?? [];
?>

<div style="max-width:1120px; margin:110px auto 40px auto; padding:0 20px;">
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:22px; margin-bottom:16px;">
    <h1 style="font-size:1.9rem; font-weight:900; margin:0; color:#111; letter-spacing:-0.5px;">Iyong Cart</h1>
  </div>

  <?php if(empty($cart)): ?>
    <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:60px 20px; text-align:center;">
      <p style="font-size:1.2rem; font-weight:800; color:#111;">Wala pa laman</p>
      <p style="color:#666; margin-bottom:18px;">Magdag ka muna ng gulay</p>
      <a href="products.php" style="background:#111; color:#fff; padding:12px 24px; border-radius:10px; text-decoration:none; font-weight:800;">Mamili Na</a>
    </div>
  <?php else:
    $ids = implode(',', array_map('intval', array_keys($cart)));
    if($ids == '') $ids = '0';
    $result = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
    $total = 0;
  ?>
  <div style="background:#fff; border:1px solid #e9e9e9; border-radius:16px; padding:12px 16px;">
    <?php while($p=$result->fetch_assoc()):
      $pid=$p['id']; 
      $qty=$cart[$pid]['quantity']??1; 
      $sub=$p['price']*$qty; 
      $total+=$sub;
    ?>
    <div style="display:flex; gap:16px; padding:18px 6px; border-bottom:1px solid #f0f0f0; align-items:center; flex-wrap:wrap;">
      <img src="<?= htmlspecialchars($p['image_url']) ?>" style="width:86px; height:86px; object-fit:cover; border-radius:12px; border:1px solid #eee;" onerror="this.src='https://via.placeholder.com/86'">
      <div style="flex:1; min-width:180px;">
        <b style="font-size:1.05rem; color:#111;"><?= htmlspecialchars($p['name']) ?></b><br>
        <span style="font-size:0.95rem; color:#333;">
          ₱<?= number_format($p['price'],2) ?> × <b style="background:#f6f6f6; padding:3px 8px; border-radius:8px; border:1px solid #e9e9e9;"><?= $qty ?> <?= htmlspecialchars($p['unit']) ?></b> = <b>₱<?= number_format($sub,2) ?></b>
        </span>
      </div>

      <form action="cart_handler.php" method="post" style="display:flex; align-items:center; gap:6px; background:#fafafa; padding:6px; border-radius:12px; border:1px solid #e9e9e9; margin:0;">
        <input type="hidden" name="product_id" value="<?= $pid ?>">
        <input type="hidden" name="action" value="update">
        <button type="button" onclick="let i=this.nextElementSibling; i.value=Math.max(1, parseInt(i.value||1)-1)" style="width:36px; height:38px; font-weight:900; background:#fff; border:1px solid #ddd; border-radius:8px; cursor:pointer;">−</button>
        <input type="number" name="quantity" value="<?= $qty ?>" min="1" max="999" style="width:56px; font-weight:800; padding:7px; border:1.5px solid #111; border-radius:8px; text-align:center;">
        <button type="button" onclick="let i=this.previousElementSibling; i.value=parseInt(i.value||0)+1" style="width:36px; height:38px; font-weight:900; background:#111; color:#fff; border:none; border-radius:8px; cursor:pointer;">+</button>
        <button type="submit" style="background:#111; color:#fff; border:none; padding:9px 12px; border-radius:8px; font-weight:700; margin-left:4px; cursor:pointer;">Update</button>
      </form>

      <form action="cart_handler.php" method="post" style="margin:0;">
        <input type="hidden" name="product_id" value="<?= $pid ?>"><input type="hidden" name="action" value="remove">
        <button style="color:#c00; background:none; border:none; font-weight:700; cursor:pointer;">Tanggalin</button>
      </form>
    </div>
    <?php endwhile; ?>
    <div style="display:flex; justify-content:space-between; align-items:center; padding:18px 6px 6px 6px; flex-wrap:wrap; gap:12px;">
        <span style="font-size:0.95rem; color:#666;">Free delivery pag ₱500 pataas</span>
        <div style="display:flex; align-items:center; gap:14px;">
            <span style="font-size:1.4rem; font-weight:900; color:#111;">Total: ₱<?= number_format($total,2) ?></span>
            <a href="checkout.php" style="background:#111; color:#fff; padding:13px 26px; border-radius:12px; text-decoration:none; font-weight:800;">Magbayad Na →</a>
        </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php include 'footer.php'; ?>