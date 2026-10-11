<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) { @ini_set('session.save_path', sys_get_temp_dir()); session_start(); }
include 'db_connect.php';
function find_image($w){$d=__DIR__.'/images'; if(!is_dir($d)) return '/images/'.basename($w); foreach(scandir($d) as $f){ if(strtolower($f)==strtolower(basename($w))) return '/images/'.$f; } return '/images/'.basename($w);}
include 'header.php'; $is_pdo=$conn instanceof PDO;
?>
<style>
/* FARM THEME - REAL VEGETABLE IMAGES BLENDED */
.landing-wrap{
  background-color:#f6f1dc;
  background-image:
    linear-gradient(rgba(246,241,220,0.92), rgba(246,241,220,0.88)),
    url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=2000&auto=format&fit=crop');
  background-size:cover;
  background-position:center;
  position:relative;
  padding-bottom:30px;
}
.hero{max-width:1280px;margin:0 auto;padding:36px 20px;display:grid;grid-template-columns:1.1fr .9fr;gap:24px;align-items:center;position:relative;z-index:1}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:20px 14px}}

.hero-left{
  background:rgba(253,250,240,0.92);
  border:1.5px solid #d9cf9a;
  border-radius:20px;
  padding:28px 22px;
  position:relative;
  overflow:hidden;
  box-shadow:0 10px 24px rgba(60,40,0,.08);
  backdrop-filter:blur(2px);
}
.hero-left .farm-accent{
  position:absolute;bottom:0;right:0;
  width:42%;height:100%;
  background-image:url('https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=800&auto=format&fit=crop');
  background-size:cover;
  background-position:center;
  opacity:0.13;
  mix-blend-mode:multiply;
  pointer-events:none;
  border-radius:0 20px 20px 0;
}
.hero-left::after{
  content:'';position:absolute;bottom:0;left:0;right:0;height:6px;
  background:repeating-linear-gradient(90deg,#2d7a3e 0 30px,#8bc34a 30px 60px,#f0c040 60px 90px);
}

.badge{border:1.5px solid #2d7a3e;background:#e8f5d8;color:#1f4d22;font-weight:800;font-size:.72rem;padding:5px 12px;border-radius:20px;display:inline-block;position:relative;z-index:1}
.hero h1{font-size:clamp(2rem,4.5vw,3.2rem);line-height:.95;letter-spacing:-1px;margin:14px 0;color:#1f2a1f;font-weight:900;position:relative;z-index:1}
.hero h1 span{color:#2d7a3e}
.hero p{color:#5a5540;max-width:480px;line-height:1.5;font-size:.96rem;position:relative;z-index:1}
.btns{display:flex;gap:10px;margin-top:18px;flex-wrap:wrap;position:relative;z-index:1}
.btn{padding:11px 18px;border-radius:24px;font-weight:800;text-decoration:none;font-size:.88rem}
.btn-black{background:#1f2a1f;color:#fff}.btn-white{background:#fff;border:1.5px solid #2d7a3e;color:#1f2a1f}
.pills{display:flex;gap:8px;margin-top:16px;flex-wrap:wrap;position:relative;z-index:1}
.pills a{border:1.5px solid #d9cf9a;background:#fff;padding:6px 12px;border-radius:20px;text-decoration:none;color:#3a3a2a;font-weight:700;font-size:.78rem}

.card-right{
  background:rgba(255,255,255,0.94);
  border:1.5px solid #d9cf9a;
  border-radius:20px;
  padding:16px;
  position:relative;
  overflow:hidden;
  backdrop-filter:blur(2px);
}
.card-right .veg-bg{
  position:absolute;inset:0;
  background-image:url('https://images.unsplash.com/photo-1597362925123-77861d3fbac7?q=80&w=800&auto=format&fit=crop');
  background-size:cover;
  background-position:center;
  opacity:0.08;
  pointer-events:none;
}
.item{border:1.5px solid #e6dfb8;background:rgba(253,250,240,0.9);border-radius:14px;padding:10px 12px;display:flex;gap:10px;align-items:center;margin-bottom:10px;position:relative;z-index:1}
.item.green{background:#eef6e2;border-color:#2d7a3e}
.item.dark{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
.item.dark small{color:#c8e9c8}

.suki{max-width:1280px;margin:20px auto 0;padding:20px;background:rgba(253,250,240,0.92);border:1.5px solid #d9cf9a;border-radius:20px;position:relative;z-index:1;backdrop-filter:blur(2px)}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
@media(min-width:700px){.grid{grid-template-columns:repeat(3,1fr)}}@media(min-width:1100px){.grid{grid-template-columns:repeat(6,1fr)}}
.prod{border:1.5px solid #e6dfb8;background:#fff;border-radius:16px;padding:10px;text-align:center;transition:.2s}
.prod:hover{transform:translateY(-3px);border-color:#2d7a3e;box-shadow:0 8px 18px rgba(45,122,62,.15)}
.prod img{width:100%;height:110px;object-fit:contain;background:#fdfaf0;border-radius:10px}
</style>

<div class="landing-wrap">
<section class="hero">
  <div class="hero-left">
    <div class="farm-accent"></div>
    <div class="badge">Sariwa mula sa Binan farmers</div>
    <h1>Fresh from the farm, <span>straight to your door.</span></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna.</p>
    <div class="btns"><a href="products.php" class="btn btn-black">Mamili Na</a><a href="my_orders.php" class="btn btn-white">My Orders</a></div>
    <div style="margin-top:12px;font-size:.78rem;color:#6b6450;font-weight:700;position:relative;z-index:1">Cash on Delivery &nbsp;|&nbsp; Same-day harvest &nbsp;|&nbsp; No middleman</div>
    <div class="pills"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php?cat=essentials">Essentials</a><a href="products.php">Best Sellers</a></div>
  </div>
  <div class="card-right">
    <div class="veg-bg"></div>
    <div style="font-weight:800;color:#1f2a1f;position:relative;z-index:1">Ano meron ngayon?</div><small style="color:#6b6450;position:relative;z-index:1">Bagong pitas ngayong umaga</small>
    <div style="margin-top:14px;position:relative;z-index:1">
      <div class="item green"><div style="width:32px;height:32px;background:#fff;display:grid;place-items:center;border-radius:8px;font-weight:800;color:#2d7a3e">50+</div><div><b>klase ng gulay</b><br><small>Diretso sa bukid</small></div></div>
      <div class="item"><div style="width:32px;height:32px;background:#fff;border:1px solid #ece6d1;display:grid;place-items:center;border-radius:8px;font-weight:800">F</div><div><b>Matamis na prutas</b><br><small>Hinog sa puno</small></div></div>
      <div class="item dark"><div style="width:32px;height:32px;background:rgba(255,255,255,.15);display:grid;place-items:center;border-radius:8px">></div><div><b>Bukid2Bayan Express</b><br><small>Binan Laguna • 30-60 mins</small></div></div>
    </div>
  </div>
</section>

<div class="suki">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px"><h2 style="margin:0;font-size:1.15rem;font-weight:900;color:#1f2a1f">SUKI PICKS FOR 2026</h2><a href="products.php" style="border:1.5px solid #2d7a3e;background:#fff;padding:6px 12px;border-radius:20px;text-decoration:none;color:#2d7a3e;font-weight:700;font-size:.8rem">See All</a></div>
  <div class="grid">
  <?php
    $sql="SELECT id,name,image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
    $ps=[]; if($is_pdo){$s=$conn->query($sql);$ps=$s->fetchAll(PDO::FETCH_ASSOC);} else {$r=$conn->query($sql); if($r) while($x=$r->fetch_assoc()) $ps[]=$x;}
    foreach($ps as $row){ $img=htmlspecialchars(find_image($row['image_url']??$row['name'].'.jpg')); echo '<div class="prod"><img src="'.$img.'" onerror="this.src=\'https://via.placeholder.com/100?text=Gulay\'"><div style="font-weight:800;margin-top:8px;font-size:.85rem">'.htmlspecialchars($row['name']).'</div><a href="products.php" style="display:inline-block;margin-top:6px;background:#1f2a1f;color:#fff;padding:5px 10px;border-radius:12px;text-decoration:none;font-size:.7rem;font-weight:800">View</a></div>'; }
  ?>
  </div>
</div>
</div>
<?php include 'footer.php'; ?>
