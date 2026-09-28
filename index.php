<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $uid_cookie = $_COOKIE['user_id'];
    try{
        $is_tmp = $conn instanceof PDO;
        if($is_tmp){
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->execute([$uid_cookie]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->bind_param("i", $uid_cookie);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
        }
        if($u){
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['username'];
            $_SESSION['role'] = $u['role']?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin']?? 0;
        }
    }catch(Exception $e){}
}

function find_image($want){
    $dir = __DIR__ . '/images';
    if(!is_dir($dir)) return '/images/'.basename($want);
    $files = scandir($dir);
    $want_low = strtolower(basename($want));
    foreach($files as $f){
        if(strtolower($f) === $want_low){
            return '/images/'.$f;
        }
    }
    $want_noext = pathinfo($want_low, PATHINFO_FILENAME);
    foreach($files as $f){
        $f_low = strtolower($f);
        if(strpos($f_low, $want_noext) !== false){
            return '/images/'.$f;
        }
    }
    return '/images/'.basename($want);
}

include 'header.php';
$shop_link = 'products.php';
$is_pdo = $conn instanceof PDO;
?>
<style>
body{ background:url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1920') no-repeat center fixed; background-size:cover; }
.hero-palengke{ position:relative; width:100%; height:100vh; min-height:600px; display:grid; grid-template-columns:1fr 1fr 1fr; overflow:hidden; }
.hero-palengke > div{ background-size:cover; background-position:center; opacity:0.78; }
.hero-overlay{ position:absolute; inset:0; background:linear-gradient(to bottom, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.25) 100%); display:flex; flex-direction:column; align-items:center; justify-content:center; text-align:center; color:#fff; padding:20px; padding-top:100px; }
.hero-overlay h1{ font-size:clamp(1.8rem, 5vw, 2.9rem); font-weight:900; margin:0; line-height:1.15; text-shadow:0 4px 20px rgba(0,0,0,0.4); }
.suki-section{ background:rgba(255,255,255,0.92); backdrop-filter:blur(12px); position:relative; z-index:5; padding:40px 0 60px 0; border-radius:24px 24px 0 0; margin-top:-30px; box-shadow:0 -10px 40px rgba(0,0,0,0.1); }
.suki-grid{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
.suki-card{ background:rgba(255,255,255,0.9); border:1px solid rgba(0,0,0,0.06); border-radius:16px; padding:16px 10px; text-align:center; backdrop-filter:blur(6px); transition:transform 0.2s; }
.suki-card:hover{ transform:translateY(-4px); }
.suki-card img{ width:100%; max-width:120px; height:120px; object-fit:contain; display:block; margin:0 auto; background:#f1f8e9; border-radius:12px; }
@media(min-width:600px){ .suki-grid{ grid-template-columns:repeat(3, 1fr); gap:16px; } }
@media(min-width:1024px){ .suki-grid{ grid-template-columns:repeat(6, 1fr); gap:20px; } }
@media(max-width:768px){ .hero-palengke{ height:70vh; min-height:500px; } }
</style>

<section class="hero-palengke">
    <div style="background-image:url('https://images.unsplash.com/photo-1488459716781-31db52582fe9?q=80&w=800');"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=800');"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=800');"></div>
    <div class="hero-overlay">
        <h1>Fresh from the Farm,<br>Straight to Your Door.</h1>
        <p style="max-width:620px; margin:16px 0 26px 0; font-weight:600; background:rgba(255,255,255,0.25); backdrop-filter:blur(10px); padding:8px 18px; border-radius:30px; border:1px solid rgba(255,255,255,0.3); font-size:clamp(0.85rem, 2.5vw, 1rem);">Gulay at prutas lang — diretso galing sa local farmers.</p>
        <a href="products.php" style="background:rgba(45,122,62,0.95); backdrop-filter:blur(8px); color:#fff; padding:14px 30px; border-radius:30px; font-weight:900; text-decoration:none; border:1px solid rgba(255,255,255,0.3); box-shadow:0 8px 20px rgba(0,0,0,0.2);">SHOP ALL PRODUCTS</a>
    </div>
</section>

<div class="suki-section">
    <div style="max-width:1280px; margin:0 auto; padding:0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:10px;">
            <h2 style="font-weight:800; letter-spacing:2px; color:#234723; margin:0; font-size:clamp(1.1rem, 3vw, 1.5rem); text-align:center; width:100%;">SUKI PICKS FOR 2026!</h2>
            <div style="width:100%; text-align:center; margin-top:8px;"><a href="products.php" style="color:#234723; text-decoration:none; font-weight:700; background:#e8f5e9; padding:6px 14px; border-radius:20px;">See All →</a></div>
        </div>
        <div class="suki-grid">
            <?php
            try {
                $sql = "SELECT id, name, image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
                $products = [];
                if($is_pdo){
                    $stmt = $conn->query($sql);
                    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                } else {
                    $result = $conn->query($sql);
                    if ($result) {
                        while($r = $result->fetch_assoc()){ $products[] = $r; }
                    }
                }
                if(count($products) > 0){
                    foreach($products as $row){
                        $img = trim($row['image_url'] ?? '');
                        $name = $row['name'] ?? 'Product';
                        $id = (int)$row['id'];
                        $img_src = find_image($img !== '' ? $img : $name.'.jpg');
                        $img_src = htmlspecialchars($img_src, ENT_QUOTES, 'UTF-8');
                        echo '<div class="suki-card"><a href="products.php"><img src="'.$img_src.'" loading="lazy" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'"></a><p style="font-weight:700; margin:10px 0 0 0; font-size:0.85rem; color:#234723;">'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</p><a href="products.php" style="display:inline-block; margin-top:8px; font-size:0.75rem; font-weight:800; color:#fff; background:#2d7a3e; padding:5px 12px; border-radius:20px; text-decoration:none;">View</a></div>';
                    }
                } else {
                    echo '<p style="grid-column:1/-1; text-align:center; color:#666;">Wala pang products. Mag-add ka muna sa <a href="sell.php" style="color:#2d7a3e; font-weight:800;">Sell</a></p>';
                }
            } catch(Exception $e){
                echo '<p style="grid-column:1/-1; text-align:center; color:red;">Error: '.htmlspecialchars($e->getMessage()).'</p>';
            }
            ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
