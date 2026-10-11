<?php
// VERCEL SAFE HEADER - FARMER COLOR + EASY NAV
if (ob_get_level() == 0) ob_start();
if (session_status() == PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT']==443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https');
    $secure=$is_https;
    if (PHP_VERSION_ID >= 70300) @session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
    session_start();
} else {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT']==443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO']==='https');
    $secure=$is_https;
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
$account_link=$is_logged?'profile.php':'login.php';
?>
<!DOCTYPE html><html lang="tl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>BUKID2BAYAN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
*{box-sizing:border-box} body{margin:0;padding-top:96px;font-family:'Inter',system-ui;background:#f6f3e6;color:#2b2b1f}
.topbar{background:#2d7a3e;color:#e8f5d8;font-size:.78rem}
.topbar-inner{max-width:1280px;margin:0 auto;display:flex;justify-content:space-between;padding:6px 20px}
.topbar a{color:#e8f5d8;text-decoration:none;font-weight:600;margin-left:14px}
.mainbar{position:fixed;top:28px;left:0;right:0;z-index:9999;background:#fffcf0;border-bottom:2px solid #e6dfb8;box-shadow:0 4px 20px rgba(60,40,0,.06)}
.mainbar-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:auto 1fr auto;gap:16px;align-items:center;padding:10px 20px}
.logo{background:#2d7a3e;color:#fff;font-weight:900;line-height:.9;padding:8px 14px;border-radius:10px;text-decoration:none;font-size:.95rem}
.logo span{font-size:.45rem;display:block;opacity:.9}
.nav{display:flex;gap:4px;justify-content:center}
.nav a{padding:8px 14px;border-radius:20px;text-decoration:none;color:#2b2b1f;font-weight:700;font-size:.88rem;border:1px solid transparent}
.nav a:hover,.nav a.active{background:#f0ead0;border-color:#e6dfb8;color:#2d7a3e}
.search{position:relative;display:flex;align-items:center}
.search input{width:100%;max-width:320px;padding:9px 14px 9px 38px;border-radius:20px;border:1.5px solid #e6dfb8;background:#fdfaf0;font-weight:600;outline:none}
.search input:focus{border-color:#2d7a3e;background:#fff}
.search i{position:absolute;left:14px;color:#8a7d5a}
.right{display:flex;gap:8px;align-items:center}
.icon{width:40px;height:40px;display:grid;place-items:center;border:1px solid #e6dfb8;border-radius:12px;background:#fff;color:#2b2b1f;text-decoration:none;font-weight:700}
.icon:hover{background:#2d7a3e;color:#fff}
.cart{position:relative}.badge{position:absolute;top:-6px;right:-6px;background:#d94f30;color:#fff;font-size:.65rem;min-width:18px;height:18px;border-radius:9px;display:grid;place-items:center;font-weight:800}
.burger{display:none;width:40px;height:40px;border:0;border-radius:12px;background:#1f2a1f;color:#fff}
.mob{display:none;position:fixed;top:96px;left:0;right:0;background:#fffcf0;border-top:1px solid #e6dfb8;z-index:9998;padding:12px 16px;box-shadow:0 20px 40px rgba(0,0,0,.1)}
.mob.active{display:block}
.mob input{width:100%;padding:10px 14px 10px 38px;border-radius:12px;border:1.5px solid #e6dfb8;background:#fff;margin-bottom:10px}
.mob a{display:block;padding:12px;border-radius:12px;text-decoration:none;color:#1f2a1f;font-weight:700}
.mob a:hover{background:#f0ead0}
@media(max-width:1024px){.mainbar-inner{grid-template-columns:auto 1fr auto}.nav{display:none}.search{display:none}.burger{display:grid;place-items:center}body{padding-top:72px}.mainbar{top:0}.topbar{display:none}}
</style></head><body>
<div class="topbar"><div class="topbar-inner"><div>🌾 Sariwa mula Biñan, Laguna — araw-araw na ani</div><div><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a><a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a><?php if($is_logged){?><span>Hi, <?=htmlspecialchars($_SESSION['user_name']??'User')?></span><a href="logout.php">Logout</a><?php }else{?><a href="register.php">Sign Up</a><a href="login.php" style="background:#fff;color:#2d7a3e;padding:3px 10px;border-radius:12px">Login</a><?php }?></div></div></div>
<div class="mainbar"><div class="mainbar-inner">
<a href="index.php" class="logo">BUKID 2BAYAN<br><span>FROM BUKID TO BAYAN</span></a>
<?php if(!$hide_categories){?><nav class="nav"><a href="products.php?cat=fruits">🍎 Fruits</a><a href="products.php?cat=vegetables">🥬 Veggies</a><a href="products.php?cat=essentials">🧺 Essentials</a><a href="products.php">All Products</a></nav><?php }else{?><div></div><?php }?>
<div class="search"><i class="fas fa-search"></i><input id="pcSearch" placeholder="Maghanap ng gulay, prutas..." onkeydown="if(event.key==='Enter'){location='products.php?q='+this.value}"></div>
<div class="right"><a href="<?=$account_link?>" class="icon"><i class="far fa-user"></i></a><a href="cart.php" class="icon cart"><i class="fas fa-shopping-cart"></i><?php if($cart_count>0){?><span class="badge"><?=$cart_count?></span><?php }?></a><button class="burger" id="burger"><i class="fas fa-bars"></i></button></div>
</div></div>
<div class="mob" id="mobileMenu">
<div style="position:relative"><i class="fas fa-search" style="position:absolute;left:14px;top:14px;color:#8a7d5a"></i><input id="mobSearch" placeholder="Maghanap..." onkeydown="if(event.key==='Enter'){location='products.php?q='+this.value}"></div>
<a href="products.php?cat=fruits">🍎 Fruits</a><a href="products.php?cat=vegetables">🥬 Veggies</a><a href="products.php?cat=essentials">🧺 Essentials</a><a href="products.php">🛒 All Products</a><a href="my_orders.php">📦 My Orders</a><a href="cart.php">🛒 Cart (<?=$cart_count?>)</a><?php if($is_farmer){?><a href="farmer_dashboard.php">🌾 Farmer Dashboard</a><?php }?>
</div>
<script>document.getElementById('burger').onclick=e=>{e.stopPropagation();document.getElementById('mobileMenu').classList.toggle('active')};document.addEventListener('click',e=>{if(!e.target.closest('#mobileMenu')&&!e.target.closest('#burger'))document.getElementById('mobileMenu').classList.remove('active')});</script>
<main style="background:linear-gradient(180deg,#f6f3e6 0%,#fdfaf0 100%)">
