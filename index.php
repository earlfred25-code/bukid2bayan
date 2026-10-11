<?php
ob_start();
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!== 'off') || ($_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $secure = $is_https;
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    }
    session_start();
}
include 'db_connect.php';
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid = (int)$_COOKIE['user_id'];
    try{
        if($conn instanceof PDO){ $st=$conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1"); $st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC); }
        else { $st=$conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1"); $st->bind_param("i",$uid); $st->execute(); $u=$st->get_result()->fetch_assoc(); }
        if($u){ $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['username']; $_SESSION['role']=$u['role']??'buyer'; }
    }catch(Exception $e){}
}
function find_image($want){
    $dir = __DIR__ . '/images';
    if(!is_dir($dir)) return '/images/'.basename($want);
    $files = scandir($dir);
    $want_low = strtolower(basename($want));
    foreach($files as $f){ if(strtolower($f) === $want_low) return '/images/'.$f; }
    $want_noext = pathinfo($want_low, PATHINFO_FILENAME);
    foreach($files as $f){ if(strpos(strtolower($f), $want_noext) !== false) return '/images/'.$f; }
    return '/images/'.basename($want);
}
include 'header.php';
$is_pdo = $conn instanceof PDO;
?>
<style>
.hero{max-width:1280px;margin:0 auto;padding:50px 20px;display:grid;grid-template-columns:1.1fr .9fr;gap:32px;align-items:center;background:linear-gradient(180deg,#fdfaf0 0%,#f5efe0 100%);position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;top:-60px;right:-60px;width:380px;height:380px;background:radial-gradient(circle,#d8e9c8 0%,transparent 70%);opacity:.6;border-radius:50%}
.hero::after{content:'';position:absolute;bottom:-80px;left:-40px;width:420px;height:420px;background:radial-gradient(circle,#f7e6b5 0%,transparent 70%);opacity:.6;border-radius:50%}
.hero-left{position:relative;z-index:2}
.badge{display:inline-flex;gap:6px;align-items:center;background:#ffffff;border:1px solid #e6dfc3;color:#5a4e2a;font-weight:800;font-size:0.75rem;padding:6px 12px;border-radius:20px}
.hero h1{font-size:clamp(2.2rem,5vw,3.4rem);font-weight:900;line-height:0.95;letter-spacing:-1.2px;margin:14px 0 12px;color:#1f2a1f}
.hero h1 em{font-style:normal;color:#2d7a3e;position:relative}
.hero p{color:#6b6450;font-size:clamp(0.95rem,2vw,1.05rem);line-height:1.6;max-width:520px}
.hero-cta{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
.btn{padding:13px 22px;border-radius:30px;font-weight:800;text-decoration:none;font-size:0.9rem;display:inline-block;transition:.2s}
.btn-primary{background:#1f2a1f;color:#fff}.btn-primary:hover{transform:translateY(-2px)}
.btn-ghost{background:#ffffff;border:1px solid #ddd8c2;color:#1f2a1f}
.visual{position:relative;z-index:2;background:#ffffff;border:1px solid #ece6d1;border-radius:24px;padding:20px;box-shadow:0 20px 50px rgba(60,50,20,.07)}
.stat{display:flex;gap:12px;align-items:center;background:#fdfaf0;border:1px solid #ece6d1;border-radius:16px;padding:12px;margin-bottom:12px}
.stat:last-child{margin:0;background:#2d7a3e;color:#fff;border-color:#2d7a3e}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding:28px 16px}}
.suki-section{max-width:1280px;margin:0 auto;padding:30px 20px;background:#fdfaf0}
.suki-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:10px}
.suki-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}
@media(min-width:600px){.suki-grid{grid-template-columns:repeat(3,1fr);gap:16px}}
@media(min-width:1024px){.suki-grid{grid-template-columns:repeat(6,1fr)}}
.suki-card{background:#ffffff;border:1px solid #ece6d1;border-radius:18px;padding:12px;text-align:center;transition:.25s}
.suki-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.06);border-color:#c8d9b8}
.suki-card img{width:100%;height:130px;object-fit:contain;background:#fdfaf0;border-radius:12px}
</style>

<section class="hero">
  <div class="hero-left">
    <div class="badge">🌾 Sariwa mula sa Biñan farmers</div>
    <h1>Fresh from the farm, <em>straight to your door.</em></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna papunta sa hapag mo.</p>
    <div class="hero-cta">
      <a href="products.php" class="btn btn-primary">Mamili Na →</a>
      <a href="my_orders.php" class="btn btn-ghost">My Orders</a>
    </div>
    <div style="display:flex;gap:16px;margin-top:18px;color:#6b6450;font-weight:700;font-size:0.8rem;flex-wrap:wrap">
      <span>✓ Cash on Delivery</span><span>✓ Same-day harvest</span><span>✓ No middleman</span>
    </div>
  </div>
  <div class="visual">
    <div style="font-weight:900;color:#1f2a1f;margin-bottom:4px">Ano meron ngayon?</div>
    <div style="font-size:.85rem;color:#6b6450;margin-bottom:14px">Bagong pitas ngayong umaga</div>
    <div class="stat"><div style="width:38px;height:38px;background:#e8f0d8;display:grid;place-items:center;border-radius:10px">🥬</div><div><b>50+ klase ng gulay</b><div style="font-size:.75rem;color:#6b6450">Diretso sa bukid</div></div></div>
    <div class="stat"><div style="width:38px;height:38px;background:#f7e6b5;display:grid;place-items:center;border-radius:10px">🍎</div><div><b>Matamis na prutas</b><div style="font-size:.75rem;color:#6b6450">Hinog sa puno</div></div></div>
    <div class="stat"><div style="width:38px;height:38px;background:rgba(255,255,255,.2);display:grid;place-items:center;border-radius:10px">🚚</div><div><b style="color:#fff">Bukid2Bayan Express</b><div style="font-size:.75rem;color:#d8e9c8">On the way • Biñan Laguna</div></div></div>
  </div>
</section>

<div class="suki-section">
  <div class="suki-head">
    <h2 style="margin:0;font-weight:900;letter-spacing:-0.5px;font-size:1.4rem;color:#1f2a1f">SUKI PICKS FOR 2026</h2>
    <a href="products.php" style="text-decoration:none;font-weight:800;color:#1f2a1f;background:#fff;border:1px solid #ddd8c2;padding:6px 14px;border-radius:20px;font-size:0.8rem">See All →</a>
  </div>
  <div class="suki-grid">
    <?php
    try {
        $sql = "SELECT id, name, image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
        $products = [];
        if($is_pdo){ $stmt = $conn->query($sql); $products = $stmt->fetchAll(PDO::FETCH_ASSOC); }
        else { $result = $conn->query($sql); if($result) while($r=$result->fetch_assoc()) $products[]=$r; }
        foreach($products as $row){
            $img = trim($row['image_url'] ?? ''); $name = $row['name'] ?? 'Product';
            $img_src = htmlspecialchars(find_image($img !== '' ? $img : $name.'.jpg'), ENT_QUOTES, 'UTF-8');
            echo '<div class="suki-card"><a href="products.php"><img src="'.$img_src.'" loading="lazy" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'"></a><p style="font-weight:800;margin:10px 0 0;font-size:0.85rem;color:#1a3c22">'.htmlspecialchars($name).'</p><a href="products.php" style="display:inline-block;margin-top:8px;font-size:0.72rem;font-weight:800;color:#fff;background:#1f2a1f;padding:6px 14px;border-radius:20px;text-decoration:none">View</a></div>';
        }
        if(count($products)==0) echo '<p style="grid-column:1/-1;text-align:center;color:#666">Wala pang products. <a href="sell.php" style="color:#111;font-weight:800">Magbenta ka muna</a></p>';
    } catch(Exception $e){ echo '<p style="color:red">'.htmlspecialchars($e->getMessage()).'</p>'; }
    ?>
  </div>
</div>
<?php include 'footer.php'; ?>
