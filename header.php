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
        *{ box-sizing:border-box; }
        body{ margin:0; padding-top:112px; font-family:Inter, sans-serif; }
        .glass-header{
            position:fixed; top:0; left:0; width:100%; z-index:9999;
            background:rgba(255,255,255,0.96);
            backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px);
            border-bottom:1px solid rgba(0,0,0,0.06);
            box-shadow:0 1px 10px rgba(0,0,0,0.05);
        }
        .top-glass{ background:#f5f7f5; border-bottom:1px solid rgba(0,0,0,0.05); font-size:0.80rem; }
        .top-glass a{ color:#444; text-decoration:none; font-weight:500; }
        .top-glass-inner{ max-width:1280px; margin:0 auto; display:flex; justify-content:space-between; padding:7px 20px; }
        .ss-header-inner{ max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:12px 20px; }
        .ss-logo{ background:#2d7a3e; color:#fff; font-family:Montserrat; font-weight:900; line-height:0.95; padding:8px 14px; border-radius:8px; text-decoration:none; font-size:0.95rem; display:inline-block; letter-spacing:0.5px; }
        .ss-logo span{ font-size:0.48rem; font-weight:700; letter-spacing:1.2px; opacity:0.9; display:block; }
        .ss-nav{ display:flex; gap:32px; }
        .ss-nav a{ text-decoration:none; color:#1a1a1a; font-weight:600; font-size:0.94rem; position:relative; }
        .ss-nav a:hover{ color:#2d7a3e; }
        .ss-right{ display:flex; gap:18px; align-items:center; }
        .ss-right a{ color:#1a1a1a; font-weight:600; font-size:0.92rem; text-decoration:none; }
        .login-pill{ background:#111; color:#fff !important; padding:6px 16px; border-radius:20px; }
        .ss-cart{ position:relative; }
        .ss-badge{ position:absolute; top:-9px; right:-9px; background:#2d7a3e; color:#fff; font-size:0.65rem; min-width:18px; height:18px; border-radius:9px; display:flex; align-items:center; justify-content:center; font-weight:700; }
        
        /* Hamburger & Mobile Menu */
        .hamburger{ display:none; background:none; border:none; font-size:1.5rem; cursor:pointer; color:#1a1a1a; }
        .mobile-menu{
            display:none; position:fixed; top:112px; left:0; width:100%; height:calc(100vh - 112px);
            background:rgba(255,255,255,0.98); backdrop-filter:blur(15px);
            z-index:9998; padding:20px; overflow-y:auto;
        }
        .mobile-menu.active{ display:block; }
        .mobile-menu a{ display:block; padding:14px 0; border-bottom:1px solid #eee; color:#1a1a1a; text-decoration:none; font-weight:600; font-size:1rem; }
        .mobile-menu a i{ width:24px; }

        /* MOBILE RESPONSIVE */
        @media(max-width:900px){
            body{ padding-top:68px; }
            .top-glass{ display:none; }
            .ss-nav{ display:none; }
            .ss-right .hide-mobile{ display:none; }
            .hamburger{ display:block; }
            .mobile-menu{ top:68px; height:calc(100vh - 68px); }
            .ss-header-inner{ padding:10px 16px; }
            .ss-logo{ font-size:0.8rem; padding:6px 10px; }
        }
    </style>
</head>
<body>
    <div class="glass-header">
        <div class="top-glass">
            <div class="top-glass-inner">
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
                <a href="products.php" class="hide-mobile"><i class="fas fa-search" style="font-size:1rem;"></i></a>
                <a href="<?php echo $is_logged ? 'farmer_centre.php' : 'login.php'; ?>" class="hide-mobile"><i class="far fa-user"></i> Account</a>
                <a href="<?php echo $cart_link; ?>" class="ss-cart"><i class="fas fa-shopping-cart"></i> <span class="hide-mobile">Cart</span> <?php if ($is_logged && $cart_count > 0): ?><span class="ss-badge"><?php echo $cart_count; ?></span><?php endif; ?></a>
                <button class="hamburger" onclick="document.getElementById('mobileMenu').classList.toggle('active')"><i class="fas fa-bars"></i></button>
            </div>
        </div>
    </div>

    <!-- Mobile Drawer -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="products.php?cat=fruits"><i class="fas fa-apple-alt"></i> Fruits</a>
        <a href="products.php?cat=vegetables"><i class="fas fa-carrot"></i> Veggies</a>
        <a href="products.php?cat=essentials"><i class="fas fa-box"></i> Essentials</a>
        <a href="products.php"><i class="fas fa-search"></i> Search Products</a>
        <a href="<?php echo $farmer_link; ?>"><i class="fas fa-store"></i> Farmer Centre</a>
        <a href="<?php echo $is_logged ? 'sell.php' : 'login.php'; ?>"><i class="fas fa-seedling"></i> Sell on BUKID2BAYAN</a>
        <a href="<?php echo $cart_link; ?>"><i class="fas fa-shopping-cart"></i> Cart (<?php echo $cart_count; ?>)</a>
        <?php if($is_logged): ?>
        <a href="my_orders.php"><i class="fas fa-box"></i> My Orders</a>
        <a href="notifications.php"><i class="fas fa-bell"></i> Notifications</a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Sign Up</a>
        <?php endif; ?>
        <a href="help.php"><i class="fas fa-question-circle"></i> Help</a>
    </div>

    <script>
        // close mobile menu when clicking outside
        document.addEventListener('click', function(e){
            const menu = document.getElementById('mobileMenu');
            const burger = document.querySelector('.hamburger');
            if(!menu.contains(e.target) && !burger.contains(e.target)){
                menu.classList.remove('active');
            }
        });
    </script>

    <main>
