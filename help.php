<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
include 'header.php';
$is_logged = isset($_SESSION['user_id']) || isset($_SESSION['user']) || isset($_SESSION['loggedin']);
?>
<div class="container" style="max-width:900px; margin:24px auto; padding:0 20px;">

    <div style="background:#2a9d8f; color:#fff; border-radius:16px; padding:32px 24px; text-align:center;">
        <h1 style="font-size:2.6rem; font-weight:900; margin:0;"><i class="fas fa-question-circle"></i> Help Center</h1>
        <p style="font-size:1.1rem; margin:10px 0 0 0;">Kumusta! Ano maitutulong namin? Website orders lang — no app needed.</p>
        <form action="help.php" method="get" style="margin-top:18px; display:flex; max-width:520px; margin-left:auto; margin-right:auto;">
            <input type="text" name="q" placeholder="Search: pano umorder, delivery, bayad..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex:1; padding:14px 16px; border:none; border-radius:10px 0 0 10px; font-size:1rem; font-weight:600;">
            <button type="submit" style="background:#1a2e35; color:#fff; border:none; padding:0 22px; border-radius:0 10px 10px 0; font-weight:900; cursor:pointer;"><i class="fas fa-search"></i></button>
        </form>
    </div>

    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px; margin:20px 0;">
        <a href="#buy" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-shopping-basket" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">Pano Umorder?</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">3 steps lang</p>
        </a>
        <a href="#sell" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-seedling" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">Magbenta</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">Farmer Centre</p>
        </a>
        <a href="#contact" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-headset" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">Contact Us</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">Chat / Call</p>
        </a>
    </div>

    <div id="buy" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px; margin-bottom:16px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">Pano Umorder?</h2>
        <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:12px;">
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">1</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Login</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Mag Login o Sign Up ka muna. Lahat ng Add to Cart at Cart ay pupunta sa login pag hindi pa naka-login.</p>
            </div>
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">2</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Add to Cart</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Pili ka sa All Products, lagay kung ilang kg, pindot Add to Cart o Buy Now.</p>
            </div>
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">3</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Checkout</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Punta sa Cart, check total, pindot Checkout. Same-day delivery pag before 3PM.</p>
            </div>
        </div>
    </div>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px; margin-bottom:16px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">Madalas na Tanong (FAQ)</h2>
        
        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;" open>
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Kailangan ba mag-login para umorder?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Opo. Lahat ng Shop Now, Add to Cart, Buy Now, at Cart ay rekta login muna pag hindi pa naka-login. Para secure ang orders mo.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Magkano delivery fee?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Free delivery sa orders above ₱500 sa Biñan at nearby. Below ₱500, ₱49 lang. Makikita mo sa Cart bago mag-checkout.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Pwede ba cash on delivery?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Opo, pwede COD at GCash. Piliin mo sa checkout.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;" id="sell">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Pano magbenta sa  BUKID2BAYAN?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Pindot Sell on  BUKID2BAYAN sa taas, o Farmer Centre. Mag-sign up as farmer, tapos add product ka na. Walang listing fee.</p>
            <a href="<?= $is_logged ? 'farmer_centre.php' : 'login.php' ?>" style="display:inline-block; margin-top:10px; background:#2a9d8f; color:#fff; padding:10px 16px; border-radius:8px; text-decoration:none; font-weight:800;">Punta sa Farmer Centre</a>
        </details>
    </div>

    <div id="contact" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">Contact Us</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
            <div>
                <p style="margin:0 0 8px 0;"><i class="fas fa-phone" style="color:#2a9d8f;"></i> <b>Hotline:</b> 0912-345-6789 (8AM-6PM)</p>
                <p style="margin:0 0 8px 0;"><i class="fas fa-envelope" style="color:#2a9d8f;"></i> <b>Email:</b> help@BUKID2BAYAN.ph</p>
                <p style="margin:0 0 8px 0;"><i class="fas fa-map-marker-alt" style="color:#2a9d8f;"></i> <b>Location:</b> Biñan, Laguna</p>
                <p style="margin:12px 0 0 0; font-size:0.9rem; color:#666;">Website only — no app needed. Message ka lang, sasagot kami agad.</p>
            </div>
            <form action="help.php" method="post" style="display:grid; gap:8px;">
                <input type="text" name="help_name" placeholder="Pangalan mo" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600;">
                <input type="text" name="help_contact" placeholder="Cellphone o Email" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600;">
                <textarea name="help_message" placeholder="Ano problema?" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600; min-height:80px;"></textarea>
                <button type="submit" style="background:#2a9d8f; color:#fff; border:none; padding:12px; border-radius:10px; font-weight:900; cursor:pointer;">Send Message</button>
            </form>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>