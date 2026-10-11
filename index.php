<?php
if (ob_get_level() == 0) ob_start();
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT']==443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https');
    $secure=$is_https;
    if (PHP_VERSION_ID >= 70300) @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
}
if(isset($_COOKIE['user_id']) && $_COOKIE['user_id']!=''){
    if(!isset($_SESSION['user_id'])) $_SESSION['user_id']=(int)$_COOKIE['user_id'];
    if(!isset($_SESSION['user_name']) && isset($_COOKIE['user_name'])) $_SESSION['user_name']=$_COOKIE['user_name'];
    if(!isset($_SESSION['role']) && isset($_COOKIE['role'])) $_SESSION['role']=$_COOKIE['role'];
    @setcookie('user_id',(int)$_COOKIE['user_id'],['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    if (file_exists(__DIR__.'/db_connect.php')) {
        include_once __DIR__.'/db_connect.php';
        if(isset($conn)){
            $uid=(int)$_COOKIE['user_id'];
            try{
                if($conn instanceof PDO){ $st=$conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1"); $st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC); }
                else { $st=$conn->prepare("SELECT id,username,role,is_admin FROM users WHERE id=? LIMIT 1"); $st->bind_param("i",$uid); $st->execute(); $u=$st->get_result()->fetch_assoc(); $st->close(); }
                if($u){ $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['username']; $_SESSION['role']=$u['role']??'buyer'; $_SESSION['is_admin']=$u['is_admin']??0; }
            }catch(Exception $e){}
        }
    }
}
$current_page=strtolower(basename($_SERVER['PHP_SELF']??''));
$hide_categories=in_array($current_page,['login.php','register.php']);
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
body{margin:0;padding-top:84px;font-family:'Inter',Arial,sans-serif;background:#fdf8ec}
.top{background:#1f2a1f;color:#e8f0d8;padding:6px 20px;font-size:.78rem;display:flex;justify-content:space-between;max-width:100%}
.top a{color:#e8f0d8;text-decoration:none;font-weight:600;margin-left:14px}
.hdr{position:fixed;top:26px;left:0;right:0;z-index:999;background:#fffcf2;border-bottom:1px solid #e6dfb8}
.hdr-inner{max-width:1280px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;padding:12px 20px;gap:16px}
.logo{display:flex;align-items:center;gap:8px;text-decoration:none}
.logo-box{background:#2d7a3e;color:#fff;font-weight:900;padding:8px 12px;border-radius:10px;line-height:.9;font-size:.9rem}
.logo-text{font-weight:800;color:#1f2a1f;font-size:.85rem;letter-spacing:-.3px}
.nav{display:flex;gap:2px;background:#f5efe0;padding:4px;border-radius:30px;border:1px solid #e6dfb8}
.nav a{padding:7px 14px;border-radius:20px;text-decoration:none;color:#3a3a2a;font-weight:700;font-size:.84rem;transition:.2s}
.nav a:hover,.nav a.on{background:#1f2a1f;color:#fff}
.right{display:flex;align-items:center;gap:8px}
.search{position:relative}
.search input{width:240px;padding:8px 12px 8px 34px;border-radius:20px;border:1px solid #e6dfb8;background:#fdfaf0;outline:none;font-weight:600;font-size:.84rem}
.search input:focus{background:#fff;border-color:#2d7a3e}
.search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#8a7d5a;font-size:.85rem}
.icon{width:36px;height:36px;display:grid;place-items:center;border:1px solid #e6dfb8;border-radius:10px;background:#fff;color:#1f2a1f;text-decoration:none}
.cart{position:relative}.badge{position:absolute;top:-6px;right:-6px;background:#2d7a3e;color:#fff;min-width:18px;height:18px;border-radius:9px;display:grid;place-items:center;font-size:.65rem;font-weight:800}
.burger{display:none;width:36px;height:36px;border:0;border-radius:10px;background:#1f2a1f;color:#fff}
.mob{display:none;position:fixed;top:84px;left:12px;right:12px;background:#fffcf2;border:1px solid #e6dfb8;border-radius:16px;padding:12px;z-index:9998;box-shadow:0 10px 30px rgba(0,0,0,.08)}
.mob.active{display:block}
.mob a{display:block;padding:10px;border-radius:10px;text-decoration:none;color:#1f2a1f;font-weight:700}
.mob a:hover{background:#f5efe0}
@media(max-width:980px){.nav,.search{display:none}.burger{display:grid;place-items:center}body{padding-top:60px}.hdr{top:0}.top{display:none}}
</style></head><body>
<div class="top"><div>🌾 Direktang galing Biñan, Laguna • Araw-araw na ani</div><div><a href="my_orders.php">My Orders</a><a href="notifications.php">Notifications</a><?php if($is_logged){?><span>Hi, <?=htmlspecialchars($_SESSION['user_name']??'User')?></span><a href="logout.php">Logout</a><?php }else{?><a href="login.php">Login</a><?php }?></div></div>
<div class="hdr"><div class="hdr-inner">
  <a href="index.php" class="logo"><div class="logo-box">BUKID<br>2BAYAN</div><div class="logo-text hide">FROM BUKID<br>TO BAYAN</div></a>
  <?php if(!$hide_categories){?><nav class="nav"><a href="products.php?cat=fruits" class="<?=strpos($current_page,'fruits')!==false?'on':''?>">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php?cat=essentials">Essentials</a><a href="products.php">All Products</a></nav><?php }else{?><div></div><?php }?>
  <div class="right">
    <div class="search"><i class="fas fa-search"></i><input placeholder="Search gulay..." onkeydown="if(event.key==='Enter') location='products.php?q='+this.value"></div>
    <a href="cart.php" class="icon cart"><i class="fas fa-shopping-cart"></i><?php if($cart_count>0){?><span class="badge"><?=$cart_count?></span><?php }?></a>
    <a href="<?=$is_logged?'profile.php':'login.php'?>" class="icon"><i class="far fa-user"></i></a>
    <button class="burger" id="burger"><i class="fas fa-bars"></i></button>
  </div>
</div></div>
<div class="mob" id="mobileMenu"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php">All Products</a><a href="my_orders.php">My Orders</a><a href="cart.php">Cart (<?=$cart_count?>)</a><?php if($is_farmer){?><a href="farmer_dashboard.php">Farmer Dashboard</a><?php }?></div>
<script>document.getElementById('burger').onclick=e=>{e.stopPropagation();document.getElementById('mobileMenu').classList.toggle('active')};document.addEventListener('click',e=>{if(!e.target.closest('#mobileMenu')&&!e.target.closest('#burger'))document.getElementById('mobileMenu').classList.remove('active')});</script>
<main style="background:#fdf8ec">
