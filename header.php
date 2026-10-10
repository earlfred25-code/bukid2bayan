<?php
// VERCEL SAFE HEADER - NO WARNING + MOBILE FRIENDLY
if (ob_get_level() == 0) ob_start();
if (session_status() == PHP_SESSION_NONE) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $secure = $is_https;
    @ini_set('session.save_path', sys_get_temp_dir());
    if (PHP_VERSION_ID >= 70300) {
        @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    }
    session_start();
} else {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $secure = $is_https;
}

// RESTORE FROM COOKIE - VERCEL FIX
if (isset($_COOKIE['user_id']) && $_COOKIE['user_id'] != '') {
    if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = (int)$_COOKIE['user_id'];
    if (!isset($_SESSION['user_name']) && isset($_COOKIE['user_name'])) $_SESSION['user_name'] = $_COOKIE['user_name'];
    if (!isset($_SESSION['role']) && isset($_COOKIE['role'])) $_SESSION['role'] = $_COOKIE['role'];
    
    @setcookie('user_id', (int)$_COOKIE['user_id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    if (isset($_COOKIE['user_name'])) @setcookie('user_name', $_COOKIE['user_name'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
    if (isset($_COOKIE['role'])) @setcookie('role', $_COOKIE['role'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);

    if (file_exists(__DIR__.'/db_connect.php')) {
        include_once __DIR__.'/db_connect.php';
        if (isset($conn)) {
            $uid_cookie = (int)$_COOKIE['user_id'];
            try{
                if($conn instanceof PDO){
                    $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
                    $st->execute([$uid_cookie]);
                    $u = $st->fetch(PDO::FETCH_ASSOC);
                } else {
                    $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
                    $st->bind_param("i", $uid_cookie);
                    $st->execute();
                    $u = $st->get_result()->fetch_assoc();
                    $st->close();
                }
                if($u){
                    $_SESSION['user_id'] = $u['id'];
                    $_SESSION['user_name'] = $u['username'];
                    $_SESSION['role'] = $u['role'] ?? 'buyer';
                    $_SESSION['is_admin'] = $u['is_admin'] ?? 0;
                }
            }catch(Exception $e){}
        }
    }
}

$current_page = strtolower(basename($_SERVER['PHP_SELF'] ?? ''));
$hide_categories = in_array($current_page, ['login.php','register.php']);
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $v) {
        $cart_count += is_array($v) && isset($v['quantity']) ? (int)$v['quantity'] : (is_numeric($v) ? (int)$v : 1);
    }
}
$is_logged = isset($_SESSION['user_id']) || isset($_COOKIE['user_id']);
$role = $_SESSION['role'] ?? $_COOKIE['role'] ?? 'buyer';
$is_farmer = in_array(strtolower($role), ['farmer','seller','admin']);
?>
<!DOCTYPE html>
<html lang="tl">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BUKID2BAYAN - From Bukid to Bayan</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box} body{margin:0;padding-top:88px;font-family:'Inter',system-ui;background:#f8faf6}
.glass-header{position:fixed;top:0;left:0;width:100%;z-index:9999;background:rgba(255,255,255,0.9);backdrop-filter:blur(16px);-webkit-backdrop-filter:blur(16px);border-bottom:1px solid #e9ece6}
.top-glass{background:#f1f5f1;border-bottom:1px solid #e9ece6;font-size:0.78rem}
.top-glass a{color:#444;text-decoration:none;font-weight:600}
.top-glass-inner{max-width:1280px;margin:0 auto;display:flex;justify-content:space-between;padding:6px 20px}
.ss-header-inner{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 20px}
.ss-logo{background:#111;color:#fff;font-weight:900;line-height:0.9;padding:9px 14px;border-radius:10px;text-decoration:none;font-size:0.9rem;letter-spacing:-0.3px}
.ss-logo span{font-size:0.45rem;font-weight:700;letter-spacing:1px;opacity:.8;display:block;margin-top:2px}
.ss-nav{display:flex;gap:6px}
.ss-nav a{text-decoration:none;color:#333;font-weight:700;font-size:0.88rem;padding:8px 14px;border-radius:20px;transition:.2s}
.ss-nav a:hover{background:#eef5ee;color:#1a3c22}
.ss-right{display:flex;gap:10px;align-items:center}
.ss-right a{color:#111;font-weight:700;font-size:0.88rem;text-decoration:none;padding:8px 12px;border-radius:20px}
.login-pill{background:#111;color:#fff !important}
.ss-cart{position:relative}
.ss-badge{position:absolute;top:-6px;right:-6px;background:#2d7a3e;color:#fff;font-size:0.65rem;min-width:18px;height:18px;border-radius:9px;display:grid;place-items:center;font-weight:800}
.hamburger{display:none;background:#111;color:#fff;border:0;width:38px;height:38px;border-radius:10px;cursor:pointer}
.mobile-menu{display:none;position:fixed;top:88px;left:12px;right:12px;background:#fff;border:1px solid #e9ece6;border-radius:20px;z-index:9998;padding:14px;box-shadow:0 20px 50px rgba(0,0,0,.12)}
.mobile-menu.active{display:block}
.mobile-menu a{display:flex;gap:10px;align-items:center;padding:12px;border-radius:12px;color:#111;text-decoration:none;font-weight:700}
.mobile-menu a:hover{background:#f5f7f5}
@media(max-width:900px){
  body{padding-top:68px}
  .top-glass{display:none}
  .ss-nav,.ss-right .hide-mobile{display:none}
  .hamburger{display:grid;place-items:center}
  .mobile-menu{top:68px}
}
</style>
</head>
<body>
<div class="glass-header">
  <div class="top-glass">
    <div class="top-glass-inner">
      <div><?php if($is_logged && $is_farmer): ?><a href="farmer_dashboard.php" style="font-weight:800;color:#2d7a3e;"><i class="fas fa-store"></i> Farmer Dashboard</a><?php endif; ?></div>
      <div style="display:flex;gap:16px">
        <?php if($is_logged){ ?><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a><?php } ?>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <?php if($is_logged){ ?><span>Hi, <?= htmlspecialchars($_SESSION['user_name']??$_COOKIE['user_name']??'User')?></span><a href="logout.php">Logout</a><?php } else { ?><a href="register.php">Sign Up</a><a href="login.php" class="login-pill">Login</a><?php } ?>
      </div>
    </div>
  </div>
  <div class="ss-header-inner">
    <a href="index.php" class="ss-logo">BUKID 2BAYAN<span>FROM BUKID TO BAYAN</span></a>
    <nav class="ss-nav">
      <a href="products.php?cat=fruits">Fruits</a>
      <a href="products.php?cat=vegetables">Veggies</a>
      <a href="products.php">All Products</a>
    </nav>
    <div class="ss-right">
      <a href="products.php" class="hide-mobile"><i class="fas fa-search"></i></a>
      <a href="<?= $is_logged?'profile.php':'login.php'?>" class="hide-mobile"><i class="far fa-user"></i></a>
      <a href="cart.php" class="ss-cart"><i class="fas fa-shopping-cart"></i> <?php if($cart_count>0){?><span class="ss-badge"><?= $cart_count?></span><?php }?></a>
      <button class="hamburger" id="burger"><i class="fas fa-bars"></i></button>
    </div>
  </div>
</div>
<div class="mobile-menu" id="mobileMenu">
  <a href="products.php?cat=fruits"><i class="fas fa-apple-whole"></i> Fruits</a>
  <a href="products.php?cat=vegetables"><i class="fas fa-carrot"></i> Veggies</a>
  <a href="products.php"><i class="fas fa-bag-shopping"></i> All Products</a>
  <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
  <a href="cart.php"><i class="fas fa-cart-shopping"></i> Cart (<?= $cart_count?>)</a>
  <?php if($is_logged && $is_farmer):?><a href="farmer_dashboard.php"><i class="fas fa-store"></i> Farmer Dashboard</a><?php endif;?>
  <?php if($is_logged):?><a href="logout.php" style="color:#ef4444"><i class="fas fa-right-from-bracket"></i> Logout</a><?php else:?><a href="login.php"><i class="fas fa-right-to-bracket"></i> Login</a><a href="register.php"><i class="fas fa-user-plus"></i> Sign Up</a><?php endif;?>
</div>
<script>
const burger=document.getElementById('burger'), menu=document.getElementById('mobileMenu');
burger.addEventListener('click',e=>{e.stopPropagation();menu.classList.toggle('active')});
document.addEventListener('click',e=>{if(!menu.contains(e.target)&&!burger.contains(e.target))menu.classList.remove('active')});
</script>
<main>
