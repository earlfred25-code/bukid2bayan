<?php
// VERCEL SAFE - UNIQUE ECOMMERCE HEADER - SOFT FARMER CREAM HINDI PLAIN WHITE
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
                if($conn instanceof PDO){ $st=$conn->prepare("SELECT id,username,role FROM users WHERE id=? LIMIT 1"); $st->execute([$uid]); $u=$st->fetch(PDO::FETCH_ASSOC); }
                else { $st=$conn->prepare("SELECT id,username,role FROM users WHERE id=? LIMIT 1"); $st->bind_param("i",$uid); $st->execute(); $u=$st->get_result()->fetch_assoc(); }
                if($u){ $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['username']; $_SESSION['role']=$u['role']??'buyer'; }
            }catch(Exception $e){}
        }
    }
}
$cart_count=0; if(isset($_SESSION['cart'])&&is_array($_SESSION['cart'])) foreach($_SESSION['cart'] as $v) $cart_count+=is_array($v)&&isset($v['quantity'])?(int)$v['quantity']:1;
$is_logged=isset($_SESSION['user_id'])||isset($_COOKIE['user_id']);
$role=$_SESSION['role']??$_COOKIE['role']??'buyer';
$is_farmer=in_array(strtolower($role),['farmer','seller','admin']);
$current_page=strtolower(basename($_SERVER['PHP_SELF']??''));
$hide_categories=in_array($current_page,['login.php','register.php']);
?>
<!DOCTYPE html><html lang="tl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>BUKID2BAYAN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box} body{margin:0;padding-top:108px;font-family:'Inter',system-ui;background:#f6f1dc;color:#2b2b1f}
.header{position:fixed;top:0;left:0;right:0;z-index:9999;background:#fdfaf0;border-bottom:1px solid #e6dfb8}
.top-strip{max-width:1280px;margin:0 auto;display:flex;justify-content:space-between;padding:5px 20px;font-size:.72rem;color:#6b5f3a;background:#f0ead0}
.top-strip a{color:#6b5f3a;text-decoration:none;margin-left:12px;font-weight:700}
.mid{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:160px 1fr auto;gap:18px;align-items:center;padding:10px 20px;background:#fdfaf0}
.logo{background:#2d7a3e;color:#fff;font-weight:900;padding:7px 12px;border-radius:8px;line-height:.9;font-size:.9rem;text-decoration:none;display:inline-block}
.search-box{display:flex;border:1.5px solid #2d7a3e;border-radius:10px;overflow:hidden;background:#fff;max-width:600px;width:100%}
.search-box input{flex:1;border:0;padding:10px 14px;outline:none;font-weight:600;font-size:.9rem;background:#fff}
.search-box button{border:0;background:#2d7a3e;color:#fff;padding:0 18px;font-weight:800;cursor:pointer}
.icons{display:flex;gap:8px;align-items:center}
.ic{width:38px;height:38px;display:grid;place-items:center;border:1px solid #e6dfb8;border-radius:10px;background:#fff;color:#2b2b1f;text-decoration:none;position:relative}
.ic:hover{background:#2d7a3e;color:#fff;border-color:#2d7a3e}
.badge{position:absolute;top:-6px;right:-6px;background:#d94f30;color:#fff;min-width:17px;height:17px;border-radius:9px;display:grid;place-items:center;font-size:.6rem;font-weight:800}
.bot-nav{max-width:1280px;margin:0 auto;display:flex;gap:6px;padding:8px 20px;background:#fdfaf0;overflow-x:auto}
.bot-nav a{padding:6px 13px;border-radius:20px;text-decoration:none;color:#4a3f2a;font-weight:700;font-size:.82rem;background:#fff;border:1px solid #e6dfb8;white-space:nowrap}
.bot-nav a:hover,.bot-nav a.active{background:#1f2a1f;color:#fff;border-color:#1f2a1f}
.burger{display:none;width:38px;height:38px;border:0;border-radius:10px;background:#1f2a1f;color:#fff}
.mob{display:none;position:fixed;top:108px;left:0;right:0;background:#fdfaf0;border-top:1px solid #e6dfb8;padding:12px;z-index:9998}
.mob.active{display:block}.mob a{display:block;padding:11px;border-radius:10px;text-decoration:none;color:#1f2a1f;font-weight:700}
@media(max-width:900px){.mid{grid-template-columns:1fr auto}.top-strip,.bot-nav,.hide{display:none}.burger{display:grid;place-items:center}body{padding-top:62px}.header{border-bottom:1px solid #e6dfb8}}
</style></head><body>
<header class="header">
  <div class="top-strip"><div>🌾 Sariwa mula Biñan, Laguna — araw-araw na ani</div><div class="hide"><a href="my_orders.php">My Orders</a><a href="notifications.php">Notifications</a><?php if($is_logged){?><span style="margin-left:12px">Hi, <?=htmlspecialchars($_SESSION['user_name']??'User')?></span><a href="logout.php">Logout</a><?php }else{?><a href="login.php">Login</a><?php }?></div></div>
  <div class="mid">
    <a href="index.php" class="logo">BUKID 2BAYAN</a>
    <div class="search-box"><input id="s" placeholder="Maghanap ng gulay, prutas..." onkeydown="if(event.key==='Enter') location='products.php?q='+this.value"><button onclick="location='products.php?q='+document.getElementById('s').value"><i class="fas fa-search"></i></button></div>
    <div class="icons"><a href="profile.php" class="ic"><i class="far fa-user"></i></a><a href="cart.php" class="ic"><i class="fas fa-shopping-cart"></i><?php if($cart_count>0){?><span class="badge"><?=$cart_count?></span><?php }?></a><button class="burger" id="burger"><i class="fas fa-bars"></i></button></div>
  </div>
  <?php if(!$hide_categories){?><nav class="bot-nav"><a href="products.php?cat=fruits" class="active">🍎 Fruits</a><a href="products.php?cat=vegetables">🥬 Veggies</a><a href="products.php?cat=essentials">🧺 Essentials</a><a href="products.php">All Products</a><a href="products.php?q=best">🔥 Best Sellers</a><?php if($is_farmer){?><a href="farmer_dashboard.php" style="background:#e8f5d8;color:#2d7a3e;border-color:#c8e6c9">🌾 Farmer Dashboard</a><?php }?></nav><?php }?>
</header>
<div class="mob" id="mob"><a href="products.php?cat=fruits">Fruits</a><a href="products.php?cat=vegetables">Veggies</a><a href="products.php">All Products</a><a href="cart.php">Cart (<?=$cart_count?>)</a></div>
<script>document.getElementById('burger').onclick=e=>{e.stopPropagation();document.getElementById('mob').classList.toggle('active')};document.addEventListener('click',e=>{if(!e.target.closest('#mob')&&!e.target.closest('#burger'))document.getElementById('mob').classList.remove('active')});</script>
<main style="background:#f6f1dc">
