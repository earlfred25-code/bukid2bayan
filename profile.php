<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__.'/../db_connect.php';

$is_pdo = $conn instanceof PDO;

function find_image($w){
  if(empty($w)) return 'https://via.placeholder.com/300?text=Gulay';
  $w = trim($w);
  $base = basename($w);
  $check1 = __DIR__.'/../images/'.$base;
  $check2 = __DIR__.'/../images/'.$w;
  $check3 = __DIR__.'/images/'.$base;
  if(file_exists($check1)) return '../images/'.$base;
  if(file_exists($check2)) return '../images/'.$w;
  if(file_exists($check3)) return 'images/'.$base;
  if(str_starts_with($w, '/images/')) return '..'.$w;
  if(str_starts_with($w, 'images/')) return '../'.$w;
  return '../images/'.$base;
}

include __DIR__.'/../header.php';

$cat = $_GET['cat'] ?? '';
$where = "WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%'";
if($cat){
  $cat = strtolower($cat);
  if($is_pdo){
    $where .= " AND LOWER(category) = ".$conn->quote($cat)."";
  } else {
    $where .= " AND LOWER(category) = '".$conn->real_escape_string($cat)."'";
  }
}
$sql = "SELECT id, name, price, stock, image_url, category FROM products $where ORDER BY id DESC";

$products = [];
if($is_pdo){ $stmt=$conn->query($sql); $products=$stmt->fetchAll(PDO::FETCH_ASSOC); }
else { $res=$conn->query($sql); if($res) while($r=$res->fetch_assoc()) $products[]=$r; }
?>
<style>
.landing-wrap{background:linear-gradient(180deg,#e7f3d0 0%,#f6f1dc 42%,#fdf6d0 100%);min-height:100vh;padding-bottom:30px}
.head-bar{max-width:1280px;margin:0 auto 18px;background:#fdfaf0;border:1.5px solid #c8c190;border-radius:22px;padding:18px 20px;box-shadow:0 12px 30px rgba(90,70,10,.08);text-align:center}
.head-bar h1{margin:0;font-size:1.7rem;font-weight:900;color:#1f2a1f;letter-spacing:-.5px}
.head-bar p{margin:4px 0 0;color:#6b6450;font-weight:700;font-size:.85rem}
.grid-wrap{max-width:1280px;margin:0 auto;padding:0 20px;display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:700px){.grid-wrap{grid-template-columns:repeat(3,1fr)}}
@media(min-width:1100px){.grid-wrap{grid-template-columns:repeat(4,1fr)}}
.prod-card{background:#fdfaf0;border:1.5px solid #c8c190;border-radius:22px;padding:12px;box-shadow:0 12px 30px rgba(90,70,10,.08);transition:.2s;display:flex;flex-direction:column}
.prod-card:hover{transform:translateY(-4px);border-color:#2d7a3e}
.prod-card .img-box{width:100%;height:170px;background:#fffdf0;border:1px solid #e6dfb8;border-radius:14px;overflow:hidden;display:grid;place-items:center}
.prod-card .img-box img{width:100%;height:100%;object-fit:contain}
.prod-card h3{margin:10px 0 2px;font-size:.95rem;font-weight:900;color:#1f2a1f;text-transform:capitalize}
.meta{font-size:.78rem;color:#2d7a3e;font-weight:700;display:flex;gap:4px;align-items:center}
.price{font-weight:900;color:#1f2a1f;margin-top:4px}
.price span{font-weight:700;color:#6b6450;font-size:.8rem}
.qty-box{margin-top:10px;background:#fffdf0;border:1.5px solid #e6dfb8;border-radius:12px;padding:6px 8px;display:flex;align-items:center;justify-content:space-between}
.qty-btn{width:28px;height:28px;border-radius:8px;border:1.5px solid #c8c190;background:#fdfaf0;font-weight:900;cursor:pointer}
.qty-input{width:42px;text-align:center;border:1.5px solid #2d7a3e;border-radius:8px;padding:4px 0;font-weight:800;background:#fff}
.actions{display:flex;gap:8px;margin-top:10px}
.btn-add{flex:1;border:1.5px solid #2d7a3e;background:#fffdf0;color:#2d7a3e;padding:8px 0;border-radius:12px;font-weight:800;font-size:.8rem;cursor:pointer}
.btn-buy{flex:1;background:#2d7a3e;color:#fff;border:1.5px solid #2d7a3e;padding:8px 0;border-radius:12px;font-weight:800;font-size:.8rem;cursor:pointer}
.btn-add:hover{background:#eaf5dc}.btn-buy:hover{background:#1f4d22}
</style>

<div class="landing-wrap">
  <div class="head-bar">
    <h1>All Products</h1>
    <p>Fresh from farmers - <?= count($products) ?> items available • Sariwa mula Binan</p>
  </div>

  <div class="grid-wrap">
  <?php foreach($products as $p):
    $img = find_image($p['image_url'] ?? $p['name'].'.jpg');
    $price = number_format((float)($p['price'] ?? 0),2);
    $stock = (int)($p['stock'] ?? 10);
  ?>
    <div class="prod-card">
      <div class="img-box">
        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['name']) ?>" onerror="this.src='https://via.placeholder.com/300?text=<?= urlencode($p['name']) ?>'">
      </div>
      <h3><?= htmlspecialchars($p['name']) ?></h3>
      <div class="meta">from <?= $stock ?></div>
      <div class="price">₱<?= $price ?> <span>/ kg</span></div>
      
      <div class="qty-box">
        <span style="font-size:.78rem;font-weight:700;color:#5a5540">Dami:</span>
        <div style="display:flex;gap:6px;align-items:center">
          <button class="qty-btn" onclick="this.nextElementSibling.value=Math.max(1,parseInt(this.nextElementSibling.value)-1)">-</button>
          <input class="qty-input" type="text" value="1" id="qty-<?= $p['id'] ?>">
          <button class="qty-btn" onclick="this.previousElementSibling.value=parseInt(this.previousElementSibling.value)+1">+</button>
        </div>
      </div>

      <div class="actions">
        <button class="btn-add" onclick="addCart(<?= $p['id'] ?>)">Add</button>
        <button class="btn-buy" onclick="buyNow(<?= $p['id'] ?>)">Buy Now</button>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>

<script>
function addCart(id){
  let qty = document.getElementById('qty-'+id)?.value || 1;
  fetch('cart_handler.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'action=add&id='+id+'&qty='+qty})
  .then(r=>r.text()).then(()=>{ alert('Na-add sa cart!'); });
}
function buyNow(id){
  let qty = document.getElementById('qty-'+id)?.value || 1;
  window.location.href = 'checkout.php?buy='+id+'&qty='+qty;
}
</script>

<?php include __DIR__.'/../footer.php'; ?>
