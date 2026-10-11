<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) { @ini_set('session.save_path', sys_get_temp_dir()); session_start(); }
include 'db_connect.php';
function find_image($w){$d=__DIR__.'/images'; if(!is_dir($d)) return '/images/'.basename($w); foreach(scandir($d) as $f){if(strtolower($f)==strtolower(basename($w))) return '/images/'.$f;} return '/images/'.basename($w);}
include 'header.php'; $is_pdo=$conn instanceof PDO;
?>
<style>
.hero{max-width:1280px;margin:0 auto;padding:50px 20px;display:grid;grid-template-columns:1.1fr .9fr;gap:30px;align-items:center;background:linear-gradient(180deg,#fdfaf0 0%,#f5efe0 100%);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-60px;right:-60px;width:380px;height:380px;background:radial-gradient(circle,#d8e9c8 0%,transparent 70%);opacity:.6;border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-80px;left:-40px;width:420px;height:420px;background:radial-gradient(circle,#f7e6b5 0%,transparent 70%);opacity:.6;border-radius:50%}
.hero-left{position:relative;z-index:2}
.badge{display:inline-flex;background:#ffffff;border:1px solid #e6dfc3;color:#5a4e2a;font-weight:800;font-size:.72rem;padding:6px 12px;border-radius:20px}
.hero h1{font-size:clamp(2.2rem,5vw,3.4rem);line-height:.95;letter-spacing:-1.2px;margin:14px 0;color:#1f2a1f;font-weight:900}
.hero h1 em{font-style:normal;color:#2d7a3e;position:relative}
.hero p{color:#6b6450;max-width:520px;line-height:1.6}
.btns{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn{padding:13px 22px;border-radius:30px;font-weight:800;text-decoration:none;transition:.2s}
.btn1{background:#1f2a1f;color:#fff}.btn1:hover{transform:translateY(-2px)}
.btn2{background:#fff;border:1px solid #ddd8c2;color:#1f2a1f}
.visual{position:relative;z-index:2;background:#ffffff;border:1px solid #ece6d1;border-radius:24px;padding:20px;box-shadow:0 20px 50px rgba(60,50,20,.07)}
.stat{display:flex;gap:12px;align-items:center;background:#fdfaf0;border:1px solid #ece6d1;border-radius:16px;padding:12px;margin-bottom:12px}
.stat:last-child{margin:0;background:#2d7a3e;color:#fff;border-color:#2d7a3e}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:28px 16px}}
.suki{max-width:1280px;margin:0 auto;padding:30px 20px;background:#fdfaf0}
.suki-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:768px){.suki-grid{grid-template-columns:repeat(3,1fr)}}@media(min-width:1100px){.suki-grid{grid-template-columns:repeat(6,1fr)}}
.card{background:#ffffff;border:1px solid #ece6d1;border-radius:18px;padding:12px;text-align:center;transition:.25s}
.card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.06);border-color:#c8d9b8}
.card img{width:100%;height:130px;object-fit:contain;background:#fdfaf0;border-radius:12px}
</style>

<section class="hero">
  <div class="hero-left">
    <div class="badge">🌾 Sariwa mula sa Biñan farmers</div>
    <h1>Fresh from the farm, <em>straight to your door.</em></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna papunta sa hapag mo.</p>
    <div class="btns">
      <a href="products.php" class="btn btn1">Mamili Na →</a>
      <a href="my_orders.php" class="btn btn2">My Orders</a>
    </div>
    <div style="display:flex;gap:16px;margin-top:18px;font-size:.8rem;font-weight:700;color:#6b6450"><span>✓ COD</span><span>✓ Same-day harvest</span><span>✓ No middleman</span></div>
  </div>
  <div class="visual">
    <div style="font-weight:900;color:#1f2a1f;margin-bottom:4px">Ano meron ngayon?</div>
    <div style="font-size:.85rem;color:#6b6450;margin-bottom:14px">Bagong pitas ngayong umaga</div>
    <div class="stat"><div style="width:38px;height:38px;background:#e8f0d8;display:grid;place-items:center;border-radius:10px">🥬</div><div><b>50+ klase ng gulay</b><div style="font-size:.75rem;color:#6b6450">Diretso sa bukid</div></div></div>
    <div class="stat"><div style="width:38px;height:38px;background:#f7e6b5;display:grid;place-items:center;border-radius:10px">🍎</div><div><b>Matamis na prutas</b><div style="font-size:.75rem;color:#6b6450">Hinog sa puno</div></div></div>
    <div class="stat"><div style="width:38px;height:38px;background:rgba(255,255,255,.2);display:grid;place-items:center;border-radius:10px">🚚</div><div><b style="color:#fff">Bukid2Bayan Express</b><div style="font-size:.75rem;color:#d8e9c8">On the way • Biñan Laguna</div></div></div>
  </div>
</section>

<div class="suki">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px"><h2 style="margin:0;font-weight:900;color:#1f2a1f">SUKI PICKS FOR 2026</h2><a href="products.php" style="background:#fff;border:1px solid #ddd8c2;padding:6px 14px;border-radius:20px;text-decoration:none;color:#1f2a1f;font-weight:800;font-size:.8rem">See All →</a></div>
  <div class="suki-grid">
  <?php
    $sql="SELECT id,name,image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' ORDER BY id DESC LIMIT 6";
    $ps=[]; if($is_pdo){$s=$conn->query($sql);$ps=$s->fetchAll(PDO::FETCH_ASSOC);} else {$r=$conn->query($sql); if($r) while($x=$r->fetch_assoc()) $ps[]=$x;}
    foreach($ps as $row){ $img=htmlspecialchars(find_image($row['image_url']??$row['name'].'.jpg')); echo '<div class="card"><img src="'.$img.'" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'"><p style="font-weight:800;margin:8px 0 0;font-size:.85rem">'.htmlspecialchars($row['name']).'</p></div>'; }
  ?>
  </div>
</div>
<?php include 'footer.php'; ?>
