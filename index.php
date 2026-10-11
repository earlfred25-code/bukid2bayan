<?php
ob_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!== 'off') || ($_SERVER['SERVER_PORT']==443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https');
    $secure=$is_https;
    if (PHP_VERSION_ID >= 70300) @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
include 'db_connect.php';
function find_image($want){
    $dir=__DIR__.'/images';
    if(!is_dir($dir)) return '/images/'.basename($want);
    $files=scandir($dir); $wl=strtolower(basename($want));
    foreach($files as $f){ if(strtolower($f)===$wl) return '/images/'.$f; }
    $wn=pathinfo($wl,PATHINFO_FILENAME);
    foreach($files as $f){ if(strpos(strtolower($f),$wn)!==false) return '/images/'.$f; }
    return '/images/'.basename($want);
}
include 'header.php';
$is_pdo=$conn instanceof PDO;
?>
<style>
.hero{max-width:1280px;margin:0 auto;padding:48px 20px 30px;display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center;position:relative;overflow:hidden;background:radial-gradient(600px 300px at 10% 0%,#d8e9c8 0%,transparent 60%),radial-gradient(500px 300px at 90% 20%,#f7e6b5 0%,transparent 60%),linear-gradient(180deg,#f6f3e6 0%,#fdfaf0 100%)}
.badge{display:inline-flex;gap:6px;background:#fff;border:1.5px solid #c8e6c9;color:#2d7a3e;font-weight:800;font-size:.75rem;padding:6px 12px;border-radius:20px}
.hero h1{font-size:clamp(2.2rem,5vw,3.4rem);font-weight:900;line-height:.95;letter-spacing:-1.2px;margin:14px 0 12px;color:#1f2a1f}
.hero h1 em{font-style:normal;color:#2d7a3e}
.hero p{color:#5a5540;font-size:1.02rem;line-height:1.6;max-width:520px}
.btns{display:flex;gap:10px;margin-top:20px;flex-wrap:wrap}
.btn{padding:13px 22px;border-radius:30px;font-weight:800;text-decoration:none;transition:.2s}
.btn1{background:#1f2a1f;color:#fff}.btn1:hover{transform:translateY(-2px);box-shadow:0 10px 24px rgba(0,0,0,.15)}
.btn2{background:#fff;border:1.5px solid #d8d0a8;color:#1f2a1f}
.quick{display:flex;gap:8px;margin-top:18px;flex-wrap:wrap}
.quick a{background:#fff;border:1px solid #e6dfb8;padding:6px 12px;border-radius:20px;text-decoration:none;color:#5a4e2a;font-weight:700;font-size:.8rem}
.quick a:hover{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
.visual{background:#ffffff;border:1.5px solid #e6dfb8;border-radius:24px;padding:18px;box-shadow:0 20px 50px rgba(60,40,0,.08);position:relative}
.stat{display:flex;gap:12px;align-items:center;background:#fdfaf0;border:1px solid #ece6d1;border-radius:16px;padding:12px;margin-bottom:12px}
.stat.green{background:#e8f5d8;border-color:#c8e6c9}
.stat.yellow{background:#fff8d6;border-color:#f0e0a0}
.stat.dark{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:24px 16px}}
.suki{max-width:1280px;margin:0 auto;padding:28px 20px}
.suki-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:768px){.suki-grid{grid-template-columns:repeat(3,1fr)}}@media(min-width:1100px){.suki-grid{grid-template-columns:repeat(6,1fr)}}
.card{background:#ffffff;border:1.5px solid #e6dfb8;border-radius:18px;padding:12px;text-align:center;transition:.25s}
.card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.07);border-color:#a8c98a}
.card img{width:100%;height:130px;object-fit:contain;background:#fdfaf0;border-radius:12px}
</style>

<section class="hero">
  <div>
    <div class="badge">🌱 Direktang galing Biñan farmers</div>
    <h1>Fresh from the farm, <em>straight to your door.</em></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna papunta sa hapag mo.</p>
    <div class="btns"><a href="products.php" class="btn btn1">Mamili Na →</a><a href="my_orders.php" class="btn btn2">My Orders</a></div>
    <div class="quick"><a href="products.php?cat=fruits">🍎 Fruits</a><a href="products.php?cat=vegetables">🥬 Veggies</a><a href="products.php?cat=essentials">🧺 Essentials</a><a href="products.php">🔥 Best Sellers</a></div>
  </div>
  <div class="visual">
    <div style="font-weight:900;color:#1f2a1f">Ano meron ngayon?</div>
    <div style="font-size:.85rem;color:#6b6450;margin-bottom:14px">Bagong pitas ngayong umaga • May kulay na background</div>
    <div class="stat green"><div style="width:38px;height:38px;background:#fff;display:grid;place-items:center;border-radius:10px">🥬</div><div><b>50+ klase ng gulay</b><div style="font-size:.75rem;color:#5a6b4a">Diretso sa bukid, hindi palengke</div></div></div>
    <div class="stat yellow"><div style="width:38px;height:38px;background:#fff;display:grid;place-items:center;border-radius:10px">🍎</div><div><b>Matamis na prutas</b><div style="font-size:.75rem;color:#7a6b3a">Hinog sa puno, hindi sa pilit</div></div></div>
    <div class="stat dark"><div style="width:38px;height:38px;background:rgba(255,255,255,.2);display:grid;place-items:center;border-radius:10px">🚚</div><div><b style="color:#fff">Bukid2Bayan Express</b><div style="font-size:.75rem;color:#c8e6c9">On the way • Biñan Laguna • 30-60 mins</div></div></div>
  </div>
</section>

<div class="suki">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px">
    <h2 style="margin:0;font-weight:900;color:#1f2a1f;letter-spacing:-.5px">SUKI PICKS FOR 2026</h2>
    <a href="products.php" style="background:#fff;border:1.5px solid #e6dfb8;padding:6px 14px;border-radius:20px;text-decoration:none;color:#1f2a1f;font-weight:800;font-size:.8rem">See All →</a>
  </div>
  <div class="suki-grid">
  <?php
    try {
        $sql="SELECT id,name,image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
        $products=[]; if($is_pdo){$stmt=$conn->query($sql);$products=$stmt->fetchAll(PDO::FETCH_ASSOC);} else {$res=$conn->query($sql); if($res) while($r=$res->fetch_assoc()) $products[]=$r;}
        foreach($products as $row){
            $img=htmlspecialchars(find_image(trim($row['image_url']??'').''!==''?$row['image_url']:$row['name'].'.jpg'),ENT_QUOTES,'UTF-8');
            echo '<div class="card"><a href="products.php"><img src="'.$img.'" loading="lazy" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'"></a><p style="font-weight:800;margin:10px 0 0;font-size:.85rem;color:#1f2a1f">'.htmlspecialchars($row['name']).'</p><a href="products.php" style="display:inline-block;margin-top:8px;font-size:.72rem;font-weight:800;color:#fff;background:#1f2a1f;padding:6px 14px;border-radius:20px;text-decoration:none">View</a></div>';
        }
    } catch(Exception $e){ echo '<p style="color:red">'.htmlspecialchars($e->getMessage()).'</p>'; }
  ?>
  </div>
</div>
<?php include 'footer.php'; ?>
