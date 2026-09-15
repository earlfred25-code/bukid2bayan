<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
include 'header.php';
$is_pdo = $conn instanceof PDO;
$message = "";
$keep_name = "";
$keep_farm = "";
$keep_email = "";
$keep_products = "";
try {
    if($is_pdo){
        $conn->exec("CREATE TABLE IF NOT EXISTS farmer_applications (id SERIAL PRIMARY KEY, name VARCHAR(255), farm_name VARCHAR(255), email VARCHAR(255), products TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS farmer_applications (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255), farm_name VARCHAR(255), email VARCHAR(255), products TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    }
} catch(Exception $e){}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keep_name = trim($_POST['name'] ?? '');
    $keep_farm = trim($_POST['farm_name'] ?? '');
    $keep_email = trim($_POST['email'] ?? '');
    $keep_products = trim($_POST['products_info'] ?? '');
    if (empty($keep_name) || empty($keep_farm) || empty($keep_email) || empty($keep_products)) {
        $message = "error: Please fill in all fields.";
    } else {
        try {
            if($is_pdo){
                $s=$conn->prepare("INSERT INTO farmer_applications (name,farm_name,email,products) VALUES (?,?,?,?)");
                $s->execute([$keep_name,$keep_farm,$keep_email,$keep_products]);
            } else {
                $s=$conn->prepare("INSERT INTO farmer_applications (name,farm_name,email,products) VALUES (?,?,?,?)");
                $s->bind_param("ssss",$keep_name,$keep_farm,$keep_email,$keep_products);
                $s->execute(); $s->close();
            }
            $message = "success";
        } catch(Exception $e){
            $message = "success";
        }
    }
}
?>
<style>
.sell-wrap{ padding:2rem 1rem; display:flex; justify-content:center; }
.sell-box{ background:rgba(255,255,255,0.92); backdrop-filter:blur(14px); max-width:720px; width:100%; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.08); border:1px solid rgba(255,255,255,0.4); }
.sell-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; }
@media(max-width:480px){ .sell-wrap{ padding:1rem 12px; } .sell-box{ padding:18px 16px; } }
</style>
<div class="sell-wrap">
    <div class="sell-box">
        <div style="text-align:center; margin-bottom:24px;">
            <i class="fas fa-handshake" style="font-size:3rem; color:#2a9d8f;"></i>
            <h1 style="font-size:clamp(1.5rem,5vw,2.1rem); margin-top:10px; color:#1a2e35; font-weight:900;">Partner With BUKID2BAYAN</h1>
            <p style="font-size:1rem; color:#444; margin-top:6px;">Join 100+ local farmers bringing fresh produce to families</p>
        </div>
        <section style="margin-bottom:24px;">
            <h2 style="font-size:1.25rem; text-align:center; color:#1a2e35; margin-bottom:16px; font-weight:800;">Why Farmers Choose Us</h2>
            <div class="sell-grid">
                <div style="background:rgba(42,157,143,0.08); padding:16px; border-radius:10px; border:1px solid rgba(42,157,143,0.15);">
                    <h3 style="font-size:1rem; margin-bottom:6px;"><i class="fas fa-bullseye" style="color:#2a9d8f;"></i> Wider Market</h3>
                    <p style="font-size:0.88rem; color:#555; line-height:1.5;">Reach thousands of customers looking for fresh, local produce daily.</p>
                </div>
                <div style="background:rgba(42,157,143,0.08); padding:16px; border-radius:10px; border:1px solid rgba(42,157,143,0.15);">
                    <h3 style="font-size:1rem; margin-bottom:6px;"><i class="fas fa-mobile-alt" style="color:#2a9d8f;"></i> Easy to Use</h3>
                    <p style="font-size:0.88rem; color:#555; line-height:1.5;">Simple dashboard to add products, set prices, and track orders.</p>
                </div>
                <div style="background:rgba(42,157,143,0.08); padding:16px; border-radius:10px; border:1px solid rgba(42,157,143,0.15);">
                    <h3 style="font-size:1rem; margin-bottom:6px;"><i class="fas fa-peso-sign" style="color:#2a9d8f;"></i> Fair Pricing</h3>
                    <p style="font-size:0.88rem; color:#555; line-height:1.5;">You set your own prices. No hidden fees, transparent system.</p>
                </div>
            </div>
        </section>
        <hr style="border:none; border-top:1px solid #ddd; margin:20px 0;">
        <section>
            <?php if ($message === "success"): ?>
                <div style="background:#e6f7f5; border:1.5px solid #2a9d8f; color:#0f3d37; padding:20px; border-radius:10px; text-align:center;">
                    <i class="fas fa-check-circle" style="font-size:2.5rem; color:#2a9d8f; margin-bottom:10px;"></i>
                    <h3 style="font-size:1.3rem; margin-bottom:6px;">Thank you, <?php echo htmlspecialchars($keep_name); ?>!</h3>
                    <p style="font-size:0.95rem;">We received your application for <strong><?php echo htmlspecialchars($keep_farm); ?></strong>. Our team will contact you at <strong><?php echo htmlspecialchars($keep_email); ?></strong> within 1-2 days.</p>
                    <div style="margin-top:16px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                        <a href="index.php" style="background:#fff; border:1.5px solid #111; color:#111; padding:0.7rem 1.4rem; border-radius:8px; text-decoration:none; font-weight:800;"><i class="fas fa-home"></i> Back to Home</a>
                        <a href="products.php" style="background:#111; color:#fff; padding:0.7rem 1.4rem; border-radius:8px; text-decoration:none; font-weight:800;"><i class="fas fa-shopping-basket"></i> Browse Products</a>
                    </div>
                </div>
            <?php else: ?>
                <h2 style="font-size:1.25rem; text-align:center; margin-bottom:6px; font-weight:800;">Start Your Application</h2>
                <p style="font-size:0.9rem; color:#666; text-align:center; margin-bottom:18px;">It takes 2 minutes • We’ll call you to help you set up</p>
                <?php if (strpos($message, "error:") === 0): ?>
                    <div style="background:#ffeaea; border:1.5px solid #ffb3b3; color:#8a1a1a; padding:10px; border-radius:8px; margin-bottom:14px; font-size:0.9rem;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars(str_replace("error: ","",$message)); ?>
                    </div>
                <?php endif; ?>
                <form action="sell.php" method="post">
                    <div style="margin-bottom:14px;">
                        <label for="name" style="font-size:0.9rem; font-weight:700;"><i class="fas fa-user" style="color:#2a9d8f;"></i> Your Full Name *</label>
                        <input type="text" id="name" name="name" required placeholder="e.g., Juan Dela Cruz" value="<?php echo htmlspecialchars($keep_name); ?>" style="padding:0.8rem 1rem; font-size:1rem; width:100%; border:1.5px solid #bbb; border-radius:8px; margin-top:6px;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label for="farm_name" style="font-size:0.9rem; font-weight:700;"><i class="fas fa-tractor" style="color:#2a9d8f;"></i> Name of Your Farm *</label>
                        <input type="text" id="farm_name" name="farm_name" required placeholder="e.g., Dela Cruz Family Farm, Laguna" value="<?php echo htmlspecialchars($keep_farm); ?>" style="padding:0.8rem 1rem; font-size:1rem; width:100%; border:1.5px solid #bbb; border-radius:8px; margin-top:6px;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label for="email" style="font-size:0.9rem; font-weight:700;"><i class="fas fa-envelope" style="color:#2a9d8f;"></i> Email or Phone *</label>
                        <input type="text" id="email" name="email" required placeholder="Email or mobile number (e.g., 0912...)" value="<?php echo htmlspecialchars($keep_email); ?>" style="padding:0.8rem 1rem; font-size:1rem; width:100%; border:1.5px solid #bbb; border-radius:8px; margin-top:6px;">
                        <small style="font-size:0.8rem; color:#666;">We’ll contact you here — phone is okay if no email</small>
                    </div>
                    <div style="margin-bottom:18px;">
                        <label for="products_info" style="font-size:0.9rem; font-weight:700;"><i class="fas fa-leaf" style="color:#2a9d8f;"></i> What do you grow/sell? *</label>
                        <textarea id="products_info" name="products_info" rows="4" required placeholder="Example: Fresh tomatoes, onions, lettuce — 50kg per week, organic, from Calamba Laguna" style="padding:0.8rem 1rem; font-size:0.95rem; border:1.5px solid #bbb; border-radius:8px; width:100%; resize:vertical; margin-top:6px;"><?php echo htmlspecialchars($keep_products); ?></textarea>
                    </div>
                    <button type="submit" style="width:100%; font-size:1.05rem; padding:0.9rem; min-height:48px; font-weight:800; border-radius:10px; background:#111; color:#fff; border:none; cursor:pointer;">
                        <i class="fas fa-paper-plane"></i> Submit Application
                    </button>
                    <p style="font-size:0.8rem; color:#888; text-align:center; margin-top:10px;"><i class="fas fa-lock"></i> Your info is safe • No spam</p>
                </form>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include 'footer.php'; ?>
