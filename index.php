<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) { @ini_set('session.save_path', sys_get_temp_dir()); session_start(); }
include 'db_connect.php';
function find_image($w){$d=__DIR__.'/images'; if(!is_dir($d)) return '/images/'.basename($w); foreach(scandir($d) as $f){ if(strtolower($f)==strtolower(basename($w))) return '/images/'.$f; } return '/images/'.basename($w);}
include 'header.php'; $is_pdo=$conn instanceof PDO;
?>
<style>
.hero{max-width:1280px;margin:0 auto;padding:40px 20px;display:grid;grid-template-columns:1.1fr .9fr;gap:28px;align-items:center}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:24px 16px}}
.badge{border:1px solid #c8e6c9;background:#e8f5d8;color:#1f4d22;font-weight:800;font-size:.72rem;padding:5px 10px;border-radius:20px;display:inline-block}
.hero h1{font-size:clamp(2rem,4.5vw,3.2rem);line-height:.95;letter-spacing:-1px;margin:12px 0;color:#1f2a1f;font-weight:900}
.hero h1 span{color:#2d7a3e}
.hero p{color:#5a5540;max-width:500px;line-height:1.5;font-size:.98rem}
.btns{display:flex;gap:10px;margin-top:18px;flex-wrap:wrap}
.btn{padding:11px 18px;border-radius:24px;font-weight:800;text-decoration:none;font-size:.88rem}
.btn-black{background:#1f2a1f;color:#fff}.btn-white{background:#fff;border:1px solid #d8d0a8;color:#1f2a1f}
.pills{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap}
.pills a{border:1px solid #e6dfb8;background:#fff;padding:5px 12px;border-radius:20px;text-decoration:none;color:#3a3a2a;font-weight:700;font-size:.78rem}
.pills a:hover{border-color:#2d7a3e;color:#2d7a3e}
.card-right{background:#fff;border:1px solid #e6dfb8;border-radius:20px;padding:16px}
.item{border:1px solid #ece6d1;background:#fdfaf0;border-radius:14px;padding:10px 12px;display:flex;gap:10px;align-items:center;margin-bottom:10px}
.item.green{background:#eef6e2;border-color:#c8e6c9}
.item.dark{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
.item.dark small{color:#c8e6c9}
.suki{max-width:1280px;margin:20px auto;padding:0 20px}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
@media(min-width:700px){.grid{grid-template-columns:repeat(3,1fr)}}@media(min-width:1100px){.grid{grid-template-columns:repeat(6,1fr)}}
.prod{border:1px solid #e6dfb8;background:#fff;border-radius:16px;padding:10px;text-align:center;transition:.2s}
.prod:hover{transform:translateY(-3px);border-color:#2d7a3e}
.prod img{width:100%;height:110px;object-fit:contain;background:#fdfaf0;border-radius:10px}
</style>

<section class="hero">
  <div>
    <div class="badge">🌱 Sariwa mula sa Biñan farmers</div>
    <h1>Fresh from the farm, <span>straight to your door.</span></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna.</p>
    <div class="btns"><a href="products.php" class="btn btn-black">Mamili Na →</a><a href="my_orders.php" class="btn btn-white">My Orders</a></div>
    <div style="margin-top:12px;font-size:.78rem;color:#6b6450;font-weight:700">✓ Cash on Delivery &nbsp; ✓ Same-day harvest &nbsp; ✓ No middleman</div>
    <div class="pills"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php?cat=essentials">Essentials</a><a href="products.php">Best Sellers</a></div>
  </div>
  <div class="card-right">
    <div style="font-weight:800;color:#1f2a1f">Ano meron ngayon?</div><small style="color:#6b6450">Bagong pitas ngayong umaga</small>
    <div style="margin-top:14px">
      <div class="item green"><div style="width:32px;height:32px;background:#fff;display:grid;place-items:center;border-radius:8px">🥬</div><div><b>50+ klase ng gulay</b><br><small>Diretso sa bukid</small></div></div>
      <div class="item"><div style="width:32px;height:32px;background:#fff;border:1px solid #ece6d1;display:grid;place-items:center;border-radius:8px">🍎</div><div><b>Matamis na prutas</b><br><small>Hinog sa puno</small></div></div>
      <div class="item dark"><div style="width:32px;height:32px;background:rgba(255,255,255,.15);display:grid;place-items:center;border-radius:8px">🚚</div><div><b>Bukid2Bayan Express</b><br><small>Biñan Laguna • 30-60 mins</small></div></div>
    </div>
  </div>
</section>

<div class="suki">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h2 style="margin:0;font-size:1.2rem;font-weight:900;color:#1f2a1f">SUKI PICKS FOR 2026</h2><a href="products.php" style="border:1px solid #e6dfb8;background:#fff;padding:6px 12px;border-radius:20px;text-decoration:none;color:#1f2a1f;font-weight:700;font-size:.8rem">See All →</a></div>
  <div class="grid">
  <?php
    $sql="SELECT id,name,image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
    $ps=[]; if($is_pdo){$s=$conn->query($sql);$ps=$s->fetchAll(PDO::FETCH_ASSOC);} else {$r=$conn->query($sql); if($r) while($x=$r->fetch_assoc()) $ps[]=$x;}
    foreach($ps as $row){ $img=htmlspecialchars(find_image($row['image_url']??$row['name'].'.jpg')); echo '<div class="prod"><img src="'.$img.'" onerror="this.src=\'https://via.placeholder.com/100?text=Gulay\'"><div style="font-weight:800;margin-top:8px;font-size:.85rem">'.htmlspecialchars($row['name']).'</div><a href="products.php" style="display:inline-block;margin-top:6px;background:#1f2a1f;color:#fff;padding:5px 10px;border-radius:12px;text-decoration:none;font-size:.7rem;font-weight:800">View</a></div>'; }
  ?>
  </div>
</div>
<?php include 'footer.php'; ?>
