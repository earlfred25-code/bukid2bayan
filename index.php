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
    try{ $st=$conn instanceof PDO ? $conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1") : $conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1");
         if($conn instanceof PDO){ $st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC);} else {$st->bind_param("i",$uid); $st->execute(); $u=$st->get_result()->fetch_assoc();}
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
.hero{max-width:1280px;margin:0 auto;padding:40px 16px 20px;display:grid;grid-template-columns:1.1fr 0.9fr;gap:32px;align-items:center}
@media(max-width:900px){.hero{grid-template-columns:1fr;padding-top:24px}}
.badge{display:inline-flex;gap:6px;align-items:center;background:#e8f5e9;color:#1a3c22;font-weight:800;font-size:0.75rem;padding:6px 12px;border-radius:20px;border:1px solid #c8e6c9}
.hero h1{font-size:clamp(2rem,5vw,3.2rem);font-weight:900;line-height:0.95;letter-spacing:-1.5px;margin:14px 0 12px;color:#111}
.hero h1 em{font-style:normal;color:#2d7a3e}
.hero p{color:#5a635a;font-size:clamp(0.95rem,2vw,1.05rem);line-height:1.6;max-width:520px}
.hero-cta{margin-top:20px;display:flex;gap:10px;flex-wrap:wrap}
.btn{padding:13px 22px;border-radius:30px;font-weight:800;text-decoration:none;font-size:0.9rem;display:inline-block;transition:.2s}
.btn-primary{background:#111;color:#fff}.btn-primary:hover{transform:translateY(-2px);background:#000}
.btn-ghost{background:#fff;border:1px solid #ddd;color:#111}.btn-ghost:hover{background:#f5f5f5}
.hero-visual{position:relative;background:#eef6ee;border-radius:32px;padding:16px;min-height:420px;display:grid;grid-template-columns:1fr 1fr;gap:12px}
.hero-visual div{border-radius:20px;background-size:cover;background-position:center}
.float-card{position:absolute;bottom:18px;left:18px;background:#fff;border:1px solid #eee;border-radius:16px;padding:12px 14px;box-shadow:0 10px 30px rgba(0,0,0,.08);display:flex;gap:10px;align-items:center}
.suki-section{max-width:1280px;margin:10px auto 60px;padding:0 16px}
.suki-head{display:flex;justify-content:space-between;align-items:end;margin:30px 0 16px;flex-wrap:wrap;gap:10px}
.suki-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
@media(min-width:600px){.suki-grid{grid-template-columns:repeat(3,1fr);gap:16px}}
@media(min-width:1024px){.suki-grid{grid-template-columns:repeat(6,1fr)}}
.suki-card{background:#fff;border:1px solid #e9ece6;border-radius:20px;padding:14px;text-align:center;transition:.2s}
.suki-card:hover{transform:translateY(-4px);box-shadow:0 12px 24px rgba(0,0,0,.06)}
.suki-card img{width:100%;height:130px;object-fit:contain;background:#f6faf6;border-radius:14px}
</style>

<section class="hero">
  <div>
    <div class="badge">🌱 Direktang galing Biñan farmers</div>
    <h1>Fresh from the farm, <em>straight to your door.</em></h1>
    <p>Gulay at prutas lang — walang meat, walang isda. Sariwa araw-araw, diretso galing sa local farmers ng Laguna papunta sa hapag mo.</p>
    <div class="hero-cta">
      <a href="products.php" class="btn btn-primary">Mamili Na →</a>
      <a href="my_orders.php" class="btn btn-ghost">My Orders</a>
    </div>
    <div style="display:flex;gap:18px;margin-top:22px;color:#6b7a6b;font-weight:700;font-size:0.8rem">
      <span>✓ Cash on Delivery</span><span>✓ Same-day harvest</span><span>✓ No middleman</span>
    </div>
  </div>
  <div class="hero-visual">
    <div style="background-image:url('https://images.unsplash.com/photo-1488459716781-31db52582fe9?q=80&w=600')"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=600');margin-top:20px"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=600');margin-top:-20px"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1506808547685-e2ba962ded60?q=80&w=600')"></div>
    <div class="float-card"><div style="width:36px;height:36px;background:#e8f5e9;border-radius:10px;display:grid;place-items:center">🚚</div><div><b style="font-size:0.85rem">Bukid2Bayan Express</b><div style="font-size:0.7rem;color:#666">On the way • Biñan Laguna</div></div></div>
  </div>
</section>

<div class="suki-section">
  <div class="suki-head">
    <h2 style="margin:0;font-weight:900;letter-spacing:-0.5px;font-size:1.4rem">SUKI PICKS FOR 2026</h2>
    <a href="products.php" style="text-decoration:none;font-weight:800;color:#111;background:#fff;border:1px solid #ddd;padding:6px 14px;border-radius:20px;font-size:0.8rem">See All →</a>
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
            echo '<div class="suki-card"><a href="products.php"><img src="'.$img_src.'" loading="lazy" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'"></a><p style="font-weight:800;margin:10px 0 0;font-size:0.85rem;color:#1a3c22">'.htmlspecialchars($name).'</p><a href="products.php" style="display:inline-block;margin-top:8px;font-size:0.72rem;font-weight:800;color:#fff;background:#111;padding:6px 14px;border-radius:20px;text-decoration:none">View</a></div>';
        }
        if(count($products)==0) echo '<p style="grid-column:1/-1;text-align:center;color:#666">Wala pang products. <a href="sell.php" style="color:#111;font-weight:800">Magbenta ka muna</a></p>';
    } catch(Exception $e){ echo '<p style="color:red">'.htmlspecialchars($e->getMessage()).'</p>'; }
    ?>
  </div>
</div>
<?php include 'footer.php'; ?>
