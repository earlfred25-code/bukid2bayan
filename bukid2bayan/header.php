<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$cart_count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $k => $v) {
        if (is_array($v) && isset($v['quantity'])) $cart_count += (int)$v['quantity'];
        elseif (is_numeric($v)) $cart_count += (int)$v;
        else $cart_count += 1;
    }
}
$is_logged = isset($_SESSION['user_id']) || isset($_SESSION['user']) || isset($_SESSION['loggedin']);
$cart_link = $is_logged ? 'cart.php' : 'login.php';
$farmer_link = $is_logged ? 'farmer_centre.php' : 'login.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BUKID2BAYAN - From Bukid to Bayan</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&family=Inter:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .glass-header{
            position:fixed; top:0; left:0; width:100%; z-index:9999;
            background:rgba(255,255,255,0.92);
            backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(0,0,0,0.06);
            box-shadow:0 1px 3px rgba(0,0,0,0.04);
        }
        .top-glass{ background:#f5f7f5; border-bottom:1px solid rgba(0,0,0,0.05); font-size:0.80rem; font-family:Inter, sans-serif; }
        .top-glass a{ color:#444; text-decoration:none; font-weight:500; }
        .top-glass a:hover{ color:#111; }
        .ss-header-inner{ max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:12px 20px; font-family:Inter, sans-serif; }
        .ss-logo{ background:#2d7a3e; color:#fff; font-family:Montserrat; font-weight:900; line-height:0.95; padding:8px 14px; border-radius:8px; text-decoration:none; font-size:0.95rem; display:inline-block; letter-spacing:0.5px; }
        .ss-logo span{ font-size:0.48rem; font-weight:700; letter-spacing:1.2px; opacity:0.9; }
        .ss-nav{ display:flex; gap:32px; }
        .ss-nav a{ text-decoration:none; color:#1a1a1a; font-weight:600; font-size:0.94rem; position:relative; }
        .ss-nav a:hover{ color:#2d7a3e; }
        .ss-nav a::after{ content:''; position:absolute; left:0; bottom:-6px; width:0; height:2px; background:#2d7a3e; transition:0.2s; }
        .ss-nav a:hover::after{ width:100%; }
        .ss-right{ display:flex; gap:18px; align-items:center; }
        .ss-right a{ color:#1a1a1a; font-weight:600; font-size:0.92rem; text-decoration:none; }
        .login-pill{ background:#111; color:#fff !important; padding:6px 16px; border-radius:20px; font-weight:600 !important; }
        .ss-cart{ position:relative; }
        .ss-badge{ position:absolute; top:-9px; right:-9px; background:#2d7a3e; color:#fff; font-size:0.65rem; min-width:18px; height:18px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-weight:700; }
    </style>
</head>
<body>
    <div class="glass-header">
        <div class="top-glass">
            <div style="max-width:1280px; margin:0 auto; display:flex; justify-content:space-between; padding:7px 20px;">
                <div style="display:flex; gap:20px;">
                    <a href="<?php echo $farmer_link; ?>"><i class="fas fa-store" style="font-size:0.75rem;"></i> Farmer Centre</a>
                    <a href="<?php echo $is_logged ? 'sell.php' : 'login.php'; ?>"><i class="fas fa-seedling" style="font-size:0.75rem;"></i> Sell on BUKID2BAYAN</a>
                </div>
                <div style="display:flex; gap:18px; align-items:center;">
                    <?php if($is_logged): ?><a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a><?php endif; ?>
                    <a href="<?php echo $is_logged ? 'notifications.php' : 'login.php'; ?>"><i class="fas fa-bell"></i> Notifications</a>
                    <a href="help.php">Help</a>
                    <?php if ($is_logged): ?>
                        <span style="font-weight:600; color:#111;">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Farmer'); ?></span><a href="logout.php">Logout</a>
                    <?php else: ?>
                        <a href="register.php">Sign Up</a><a href="login.php" class="login-pill">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="ss-header-inner">
            <a href="index.php" class="ss-logo">BUKID<br>2BAYAN<br><span>FROM BUKID TO BAYAN</span></a>
            <nav class="ss-nav">
                <a href="products.php?cat=fruits">Fruits</a>
                <a href="products.php?cat=vegetables">Veggies</a>
                <a href="products.php?cat=essentials">Essentials</a>
            </nav>
            <div class="ss-right">
                <a href="products.php"><i class="fas fa-search" style="font-size:1rem;"></i></a>
                <a href="<?php echo $is_logged ? 'farmer_centre.php' : 'login.php'; ?>"><i class="far fa-user"></i> Account</a>
                <a href="<?php echo $cart_link; ?>" class="ss-cart"><i class="fas fa-shopping-cart"></i> Cart <?php if ($is_logged && $cart_count > 0): ?><span class="ss-badge"><?php echo $cart_count; ?></span><?php endif; ?></a>
            </div>
        </div>
    </div>
    <main>