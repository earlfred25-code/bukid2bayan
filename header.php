<?php
if (ob_get_level() == 0) ob_start();
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']!== 'off') || $_SERVER['SERVER_PORT']==443 || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https');
    $secure=$is_https;
    if (PHP_VERSION_ID >= 70300) @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
if(isset($_COOKIE['user_id']) && $_COOKIE['user_id']!=''){
    if(!isset($_SESSION['user_id'])) $_SESSION['user_id']=(int)$_COOKIE['user_id'];
    if(!isset($_SESSION['user_name']) && isset($_COOKIE['user_name'])) $_SESSION['user_name']=$_COOKIE['user_name'];
    if(!isset($_SESSION['role']) && isset($_COOKIE['role'])) $_SESSION['role']=$_COOKIE['role'];
    @setcookie('user_id',(int)$_COOKIE['user_id'],['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
}
$cart_count=0; if(isset($_SESSION['cart'])&&is_array($_SESSION['cart'])) foreach($_SESSION['cart'] as $v) $cart_count+=is_array($v)&&isset($v['quantity'])?(int)$v['quantity']:(is_numeric($v)?(int)$v:1);
$is_logged=isset($_SESSION['user_id'])||isset($_COOKIE['user_id']);
$role=$_SESSION['role']??$_COOKIE['role']??'buyer';
$is_farmer=in_array(strtolower($role),['farmer','seller','admin']);
?>
<!DOCTYPE html><html lang="tl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>BUKID2BAYAN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{margin:0;padding-top:88px;font-family:'Inter',system-ui;background:#fdfaf0;color:#2b2b1f}
.glass-header{position:fixed;top:0;left:0;right:0;z-index:9999;background:#fffcf2;border-bottom:1px solid #ece6d1;backdrop-filter:blur(14px)}
.top-glass{background:#f5efe0;border-bottom:1px solid #ece6d1;font-size:.78rem}
.top-glass a{color:#5a4e2a;text-decoration:none;font-weight:700}
.top-glass-inner{max-width:1280px;margin:0 auto;display:flex;justify-content:space-between;padding:6px 20px}
.ss-header-inner{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 20px}
.ss-logo{background:#2d7a3e;color:#fff;font-weight:900;line-height:.9;padding:9px 14px;border-radius:10px;text-decoration:none}
.ss-nav a{padding:8px 14px;border-radius:20px;text-decoration:none;color:#3a3a2a;font-weight:700;font-size:.88rem}
.ss-nav a:hover{background:#f0ead6}
.ss-right{display:flex;gap:8px;align-items:center}
.icon{width:38px;height:38px;display:grid;place-items:center;border:1px solid #ece6d1;border-radius:12px;background:#fff;color:#2d7a3e}
.hamburger{display:none;background:#2d7a3e;color:#fff;border:0;width:38px;height:38px;border-radius:10px}
.mobile-menu{display:none;position:fixed;top:88px;left:12px;right:12px;background:#fffcf2;border:1px solid #ece6d1;border-radius:18px;padding:10px;z-index:9998}
.mobile-menu.active{display:block}.mobile-menu a{display:block;padding:12px;border-radius:10px;text-decoration:none;color:#111;font-weight:700}
@media(max-width:900px){.ss-nav,.top-glass{display:none}.hamburger{display:grid;place-items:center}body{padding-top:66px}}
</style></head><body>
<div class="glass-header">
  <div class="top-glass"><div class="top-glass-inner">
    <div><?php if($is_logged && $is_farmer):?><a href="farmer_dashboard.php" style="color:#2d7a3e">🌾 Farmer Dashboard</a><?php endif;?></div>
    <div style="display:flex;gap:14px"><?php if($is_logged){?><a href="my_orders.php">My Orders</a><span>Hi, <?=htmlspecialchars($_SESSION['user_name']??$_COOKIE['user_name']??'User')?></span><a href="logout.php">Logout</a><?php }else{?><a href="register.php">Sign Up</a><a href="login.php" style="background:#111;color:#fff;padding:4px 12px;border-radius:20px">Login</a><?php }?></div>
  </div></div>
  <div class="ss-header-inner">
    <a href="index.php" class="ss-logo">BUKID 2BAYAN</a>
    <nav class="ss-nav"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php">All Products</a></nav>
    <div class="ss-right"><a href="products.php" class="icon"><i class="fas fa-search"></i></a><a href="cart.php" class="icon"><i class="fas fa-shopping-cart"></i></a><button class="hamburger" id="burger"><i class="fas fa-bars"></i></button></div>
  </div>
</div>
<div class="mobile-menu" id="mobileMenu"><a href="products.php">All Products</a><a href="my_orders.php">My Orders</a><a href="cart.php">Cart</a></div>
<script>document.getElementById('burger').onclick=e=>{e.stopPropagation();document.getElementById('mobileMenu').classList.toggle('active')};document.addEventListener('click',e=>{if(!e.target.closest('#mobileMenu')&&!e.target.closest('#burger'))document.getElementById('mobileMenu').classList.remove('active')});</script>
<main style="background:#fdfaf0">
