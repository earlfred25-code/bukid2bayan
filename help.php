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

$is_logged = isset($_SESSION['user_id']) || isset($_SESSION['user']) || isset($_SESSION['loggedin']) || isset($_COOKIE['user_id']);
$is_pdo = $conn instanceof PDO;

try {
    if($is_pdo){
        $conn->exec("CREATE TABLE IF NOT EXISTS help_messages (id SERIAL PRIMARY KEY, name VARCHAR(255), contact VARCHAR(255), message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS help_messages (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), contact VARCHAR(255), message TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    }
} catch(Exception $e){}

$flash = '';
if(isset($_POST['help_name'])){
    $n = trim($_POST['help_name'] ?? '');
    $c = trim($_POST['help_contact'] ?? '');
    $m = trim($_POST['help_message'] ?? '');
    if($n!='' && $c!='' && $m!=''){
        try {
            if($is_pdo){
                $s=$conn->prepare("INSERT INTO help_messages (name,contact,message) VALUES (?,?,?)");
                $s->execute([$n,$c,$m]);
            } else {
                $s=$conn->prepare("INSERT INTO help_messages (name,contact,message) VALUES (?,?,?)");
                $s->bind_param("sss",$n,$c,$m); $s->execute(); $s->close();
            }
            $flash = 'Thanks! We got your message and we will reply to '.$c.' soon.';
        } catch(Exception $e){ $flash = 'Sorry, we could not send your message. Try again.'; }
    }
}

include 'header.php';
$q = strtolower(trim($_GET['q'] ?? ''));
?>
<style>
.help-wrap{ max-width:900px; margin:20px auto; padding:0 16px; }
.help-hero{ background:#2a9d8f; color:#fff; border-radius:16px; padding:32px 24px; text-align:center; }
.help-grid3{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin:20px 0; }
.help-steps{ display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.help-contact{ display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:700px){
    .help-grid3{ grid-template-columns:1fr; }
    .help-steps{ grid-template-columns:1fr; }
    .help-contact{ grid-template-columns:1fr; }
    .help-wrap{ margin-top:10px; }
}
</style>

<div class="help-wrap">

    <div class="help-hero">
        <h1 style="font-size:clamp(1.8rem,5vw,2.6rem); font-weight:900; margin:0;">Help Center</h1>
        <p style="font-size:1.05rem; margin:10px 0 0 0; opacity:0.95;">New here? Here is how it works. Everything is on the website, no need to download an app.</p>
        <form action="help.php" method="get" style="margin-top:18px; display:flex; max-width:520px; margin-left:auto; margin-right:auto;">
            <input type="text" name="q" placeholder="Try: delivery, payment, how to order..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" style="flex:1; padding:14px 16px; border:none; border-radius:10px 0 0 10px; font-size:1rem; font-weight:600;">
            <button type="submit" style="background:#1a2e35; color:#fff; border:none; padding:0 22px; border-radius:0 10px 10px 0; font-weight:900; cursor:pointer;"><i class="fas fa-search"></i></button>
        </form>
        <?php if($flash!=''): ?><div style="background:#fff; color:#0f3d37; padding:12px; border-radius:10px; margin-top:16px; font-weight:800;"><?= htmlspecialchars($flash) ?></div><?php endif; ?>
    </div>

    <div class="help-grid3">
        <a href="#buy" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-shopping-basket" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">How to Order</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">Super easy</p>
        </a>
        <a href="#sell" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-seedling" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">Sell with us</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">For farmers</p>
        </a>
        <a href="#contact" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:18px; text-align:center; text-decoration:none; color:#111;">
            <i class="fas fa-headset" style="font-size:1.8rem; color:#2a9d8f;"></i>
            <p style="font-weight:900; margin:10px 0 0 0;">Need help?</p>
            <p style="font-size:0.85rem; color:#666; margin:4px 0 0 0;">Message us</p>
        </a>
    </div>

    <div id="buy" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px; margin-bottom:16px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">How to Order?</h2>
        <div class="help-steps">
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">1</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Browse as Guest</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Kahit hindi ka naka-login pwede ka mamili at mag-add to cart.</p>
            </div>
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">2</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Add to Cart</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Pili ka ng gulay, ilagay ilang kilo, add to cart.</p>
            </div>
            <div style="background:#f8fdfc; border:1px solid #cfe9e5; border-radius:12px; padding:16px; text-align:center;">
                <div style="background:#2a9d8f; color:#fff; width:32px; height:32px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-weight:900;">3</div>
                <p style="font-weight:800; margin:10px 0 4px 0;">Checkout</p>
                <p style="font-size:0.9rem; color:#555; margin:0;">Sa checkout lang hihingi ng login, tapos deliver na today pag before 3PM.</p>
            </div>
        </div>
    </div>

    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px; margin-bottom:16px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">Common Questions</h2>
        
        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;" open>
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Do I need an account to order?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Pwede ka mag-browse at mag-add to cart kahit guest. Sa checkout lang namin hihingiin login mo para sa delivery address.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">How much is delivery?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">If your order is ₱500 and up, delivery is free around Biñan and nearby areas. If it is less than that, it is just ₱50.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">Can I pay with COD?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Yes. We accept Cash on Delivery and GCash. You can pick one at checkout.</p>
        </details>

        <details style="border:1px solid #eee; border-radius:10px; padding:14px 16px; margin-bottom:10px;" id="sell">
            <summary style="font-weight:800; cursor:pointer; font-size:1.05rem;">I am a farmer, how do I sell here?</summary>
            <p style="margin:10px 0 0 0; color:#444; line-height:1.6;">Just click Sell on BUKID2BAYAN or go to Farmer Centre. Create a farmer account and you can start listing your harvest. No fees to list.</p>
            <a href="farmer_centre.php" style="display:inline-block; margin-top:10px; background:#2a9d8f; color:#fff; padding:10px 16px; border-radius:8px; text-decoration:none; font-weight:800;">Go to Farmer Centre</a>
        </details>
    </div>

    <div id="contact" style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:22px;">
        <h2 style="font-size:1.6rem; font-weight:900; margin:0 0 12px 0;">Talk to us</h2>
        <div class="help-contact">
            <div>
                <p style="margin:0 0 8px 0;"><i class="fas fa-phone" style="color:#2a9d8f;"></i> <b>Hotline:</b> 0912-345-6789 (8AM-6PM)</p>
                <p style="margin:0 0 8px 0;"><i class="fas fa-envelope" style="color:#2a9d8f;"></i> <b>Email:</b> help@BUKID2BAYAN.ph</p>
                <p style="margin:0 0 8px 0;"><i class="fas fa-map-marker-alt" style="color:#2a9d8f;"></i> <b>Biñan, Laguna</b></p>
                <p style="margin:12px 0 0 0; font-size:0.9rem; color:#666;">No app to install. Just message us here and we reply fast.</p>
            </div>
            <form action="help.php" method="post" style="display:grid; gap:8px;">
                <input type="text" name="help_name" placeholder="Your name" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600;">
                <input type="text" name="help_contact" placeholder="Phone or Email" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600;">
                <textarea name="help_message" placeholder="What is it about?" required style="padding:12px; border:2px solid #ddd; border-radius:10px; font-weight:600; min-height:80px;"></textarea>
                <button type="submit" style="background:#2a9d8f; color:#fff; border:none; padding:12px; border-radius:10px; font-weight:900; cursor:pointer;">Send</button>
            </form>
        </div>
    </div>

</div>

<?php include 'footer.php'; ?>
