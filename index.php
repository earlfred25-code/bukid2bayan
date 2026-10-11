<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) { @ini_set('session.save_path', sys_get_temp_dir()); session_start(); }
include 'db_connect.php';
function find_image($w){$d=__DIR__.'/images'; if(!is_dir($d)) return '/images/'.basename($w); foreach(scandir($d) as $f){ if(strtolower($f)==strtolower(basename($w))) return '/images/'.$f; } return '/images/'.basename($w);}
include 'header.php'; $is_pdo=$conn instanceof PDO;
?>
<style>
.landing-wrap{
  background:linear-gradient(180deg,#e7f3d0 0%,#f6f1dc 42%,#fdf6d0 100%);
  padding-bottom:28px;
  position:relative;
  overflow:hidden;
}
.landing-wrap::before{
  content:'';position:absolute;top:-120px;right:-120px;width:380px;height:380px;
  background:radial-gradient(circle,rgba(45,122,62,0.13) 0%,transparent 70%);border-radius:50%;
}
.landing-wrap::after{
  content:'';position:absolute;bottom:-80px;left:-80px;width:420px;height:420px;
  background:radial-gradient(circle,rgba(240,192,64,0.18) 0%,transparent 70%);border-radius:50%;
}
.hero{max-width:1280px;margin:0 auto;padding:36px 20px;display:grid;grid-template-columns:1.15fr .85fr;gap:22px;align-items:start;position:relative;z-index:1}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:18px 14px}}

.hero-left{
  background:#fdfaf0;
  border:1.5px solid #c8c190;
  border-radius:22px;
  padding:28px 24px;
  display:grid;
  grid-template-columns:1fr 192px;
  gap:18px;
  align-items:center;
  box-shadow:0 12px 30px rgba(90,70,10,.10);
}
@media(max-width:600px){.hero-left{grid-template-columns:1fr}.hero-left .side-img{display:none}}
.hero-left .side-img{
  width:100%;height:218px;
  border-radius:16px;
  object-fit:cover;
  border:1.5px solid #d9cf9a;
  box-shadow:0 6px 16px rgba(0,0,0,.08);
}
.badge{border:1.5px solid #2d7a3e;background:#dff0d0;color:#1f4d22;font-weight:800;font-size:.72rem;padding:6px 12px;border-radius:20px;display:inline-block}
.hero h1{font-size:clamp(2.1rem,4.6vw,3.25rem);line-height:.93;letter-spacing:-1.2px;margin:14px 0;color:#1f2a1f;font-weight:900}
.hero h1 span{color:#2d7a3e}
.hero p{color:#5a5540;max-width:480px;line-height:1.55;font-size:.95rem}
.btns{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn{padding:12px 20px;border-radius:26px;font-weight:800;text-decoration:none;font-size:.88rem;transition:.2s}
.btn-black{background:#1f2a1f;color:#fff;box-shadow:0 4px 12px rgba(31,42,31,.25)}.btn-black:hover{transform:translateY(-1px);background:#000}
.btn-white{background:#fdfaf0;border:1.5px solid #2d7a3e;color:#1f2a1f}.btn-white:hover{background:#fff}
.pills{display:flex;gap:8px;margin-top:18px;flex-wrap:wrap}
.pills a{border:1.5px solid #d9cf9a;background:#fffdf0;padding:6px 13px;border-radius:20px;text-decoration:none;color:#3a3a2a;font-weight:700;font-size:.78rem;transition:.2s}
.pills a:hover{background:#2d7a3e;color:#fff;border-color:#2d7a3e}

.card-right{
  background:#fdfaf0;
  border:1.5px solid #c8c190;
  border-radius:22px;
  padding:16px;
  box-shadow:0 12px 30px rgba(90,70,10,.10);
}
.card-right .top-img{
  width:100%;height:124px;
  border-radius:14px;
  object-fit:cover;
  border:1px solid #d9cf9a;
  margin-bottom:12px;
}
.item{border:1.5px solid #e6dfb8;background:#fffdf0;border-radius:14px;padding:11px 12px;display:flex;gap:10px;align-items:center;margin-bottom:10px;transition:.2s}
.item:hover{transform:translateY(-1px);box-shadow:0 4px 10px rgba(0,0,0,.05)}
.item.green{background:#eaf5dc;border-color:#a8cfa0}
.item.dark{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
.item.dark small{color:#c8e9c8}

.suki{max-width:1280px;margin:22px auto 0;padding:20px 20px;background:#fdfaf0;border:1.5px solid #c8c190;border-radius:22px;box-shadow:0 12px 30px rgba(90,70,10,.08);position:relative;z-index:1}
.suki h2{letter-spacing:-.5px}
.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
@media(min-width:700px){.grid{grid-template-columns:repeat(3,1fr)}}@media(min-width:1100px){.grid{grid-template-columns:repeat(6,1fr)}}
.prod{border:1.5px solid #e6dfb8;background:#fffdf0;border-radius:16px;padding:10px;text-align:center;transition:.2s}
.prod:hover{transform:translateY(-4px);border-color:#2d7a3e;box-shadow:0 10px 20px rgba(45,122,62,.14)}
.prod img{width:100%;height:110px;object-fit:contain;background:#fdfaf0;border-radius:10px}
</style>

<div class="landing-wrap">
<section class="hero">
  <div class="hero-left">
    <div>
      <div class="badge">Sariwa mula sa Binan farmers</div>
      <h1>Fresh from the farm, <span>straight to your door.</span></h1>
      <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna.</p>
      <div class="btns"><a href="products.php" class="btn btn-black">Mamili Na</a><a href="my_orders.php" class="btn btn-white">My Orders</a></div>
      <div style="margin-top:12px;font-size:.78rem;color:#6b6450;font-weight:700">Cash on Delivery | Same-day harvest | No middleman</div>
      <div class="pills"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php?cat=essentials">Essentials</a><a href="products.php">Best Sellers</a></div>
    </div>
    <img class="side-img" src="https://images.unsplash.com/photo-1608198093002-ad4e005484ec?q=80&w=600&auto=format&fit=crop" alt="Baguio vegetables Philippines">
  </div>

  <div class="card-right">
    <img class="top-img" src="https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=800&auto=format&fit=crop" alt="Philippine farm field sunrise Laguna">
    <div style="font-weight:800;color:#1f2a1f">Ano meron ngayon?</div><small style="color:#6b6450">Bagong pitas ngayong umaga</small>
    <div style="margin-top:12px">
      <div class="item green"><div style="width:32px;height:32px;background:#fff;display:grid;place-items:center;border-radius:8px;font-weight:800;color:#2d7a3e">50+</div><div><b>klase ng gulay</b><br><small>Diretso sa bukid</small></div></div>
      <div class="item"><div style="width:32px;height:32px;background:#fff;border:1px solid #ece6d1;display:grid;place-items:center;border-radius:8px;font-weight:800">F</div><div><b>Matamis na prutas</b><br><small>Hinog sa puno</small></div></div>
      <div class="item dark"><div style="width:32px;height:32px;background:rgba(255,255,255,.15);display:grid;place-items:center;border-radius:8px">></div><div><b>Bukid2Bayan Express</b><br><small>Binan Laguna • 30-60 mins</small></div></div>
    </div>
  </div>
</section>

<div class="suki">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px"><h2 style="margin:0;font-size:1.18rem;font-weight:900;color:#1f2a1f">SUKI PICKS FOR 2026</h2><a href="products.php" style="border:1.5px solid #2d7a3e;background:#fffdf0;padding:7px 14px;border-radius:20px;text-decoration:none;color:#2d7a3e;font-weight:700;font-size:.82rem">See All</a></div>
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
