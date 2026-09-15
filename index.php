<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
session_start();
include 'db_connect.php';
include 'header.php';
$is_logged = isset($_SESSION['user_id']) || isset($_SESSION['user']) || isset($_SESSION['loggedin']);
$shop_link = $is_logged ? 'products.php' : 'login.php';
?>
<style>
body{ 
    background:url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1920') no-repeat center fixed;
    background-size:cover; 
}
.hero-palengke{ position:relative; width:100%; height:100vh; min-height:600px; display:grid; grid-template-columns:1fr 1fr 1fr; overflow:hidden; }
.hero-palengke > div{ background-size:cover; background-position:center; opacity:0.78; }
.hero-overlay{ 
    position:absolute; inset:0; 
    background:linear-gradient(to bottom, rgba(255,255,255,0.15) 0%, rgba(0,0,0,0.25) 100%);
    display:flex; flex-direction:column; align-items:center; justify-content:center; 
    text-align:center; color:#fff; padding:20px; padding-top:100px;
}
.hero-overlay h1{ font-size:clamp(1.8rem, 5vw, 2.9rem); font-weight:900; margin:0; line-height:1.15; text-shadow:0 4px 20px rgba(0,0,0,0.4); }
.suki-section{ background:rgba(255,255,255,0.92); backdrop-filter:blur(12px); position:relative; z-index:5; padding:40px 0 60px 0; border-radius:24px 24px 0 0; margin-top:-30px; box-shadow:0 -10px 40px rgba(0,0,0,0.1); }
.suki-grid{ display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
.suki-card{ background:rgba(255,255,255,0.9); border:1px solid rgba(0,0,0,0.06); border-radius:16px; padding:16px 10px; text-align:center; backdrop-filter:blur(6px); transition:transform 0.2s; }
.suki-card:hover{ transform:translateY(-4px); }
.suki-card img{ width:100%; max-width:120px; height:120px; object-fit:contain; display:block; margin:0 auto; }

/* Tablet & Desktop */
@media(min-width:600px){
    .suki-grid{ grid-template-columns:repeat(3, 1fr); gap:16px; }
}
@media(min-width:1024px){
    .suki-grid{ grid-template-columns:repeat(6, 1fr); gap:20px; }
}
@media(max-width:768px){
    .hero-palengke{ height:70vh; min-height:500px; }
}
</style>

<section class="hero-palengke">
    <div style="background-image:url('https://images.unsplash.com/photo-1488459716781-31db52582fe9?q=80&w=800');"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1518843875459-f738682238a6?q=80&w=800');"></div>
    <div style="background-image:url('https://images.unsplash.com/photo-1540420773420-3366772f4999?q=80&w=800');"></div>
    <div class="hero-overlay">
        <h1>Fresh from the Farm,<br>Straight to Your Door.</h1>
        <p style="max-width:620px; margin:16px 0 26px 0; font-weight:600; background:rgba(255,255,255,0.25); backdrop-filter:blur(10px); padding:8px 18px; border-radius:30px; border:1px solid rgba(255,255,255,0.3); font-size:clamp(0.85rem, 2.5vw, 1rem);">Gulay at prutas lang — diretso galing sa local farmers.</p>
        <a href="<?php echo $shop_link; ?>" style="background:rgba(123,79,207,0.9); backdrop-filter:blur(8px); color:#fff; padding:14px 30px; border-radius:30px; font-weight:900; text-decoration:none; border:1px solid rgba(255,255,255,0.3); box-shadow:0 8px 20px rgba(0,0,0,0.2);">SHOP ALL PRODUCTS</a>
    </div>
</section>

<div class="suki-section">
    <div style="max-width:1280px; margin:0 auto; padding:0 16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px; flex-wrap:wrap; gap:10px;">
            <h2 style="font-weight:800; letter-spacing:2px; color:#234723; margin:0; font-size:clamp(1.1rem, 3vw, 1.5rem); text-align:center; width:100%;">SUKI PICKS FOR 2026!</h2>
            <div style="width:100%; text-align:center; margin-top:8px;"><a href="products.php" style="color:#234723; text-decoration:none; font-weight:700; background:#e8f5e9; padding:6px 14px; border-radius:20px;">See All →</a></div>
        </div>
        <div class="suki-grid">
            <?php
            try {
                $sql = "SELECT id, name, image_url FROM products WHERE LOWER(name) NOT LIKE '%fish%' AND LOWER(name) NOT LIKE '%meat%' AND LOWER(name) NOT LIKE '%pork%' AND LOWER(name) NOT LIKE '%beef%' AND LOWER(name) NOT LIKE '%chicken%' ORDER BY id DESC LIMIT 6";
                $result = $conn->query($sql);
                
                $products = [];
                if ($result) {
                    if (method_exists($result, 'fetch_assoc')) {
                        while($r = $result->fetch_assoc()){ $products[] = $r; }
                    } else {
                        $products = $result->fetchAll(PDO::FETCH_ASSOC);
                    }
                }

                if(count($products) > 0){
                    foreach($products as $row){
                        $img = $row['image_url'] ?? '';
                        $name = $row['name'] ?? 'Product';
                        // pag null or empty yung image, placeholder agad
                        $img_src = !empty($img) ? htmlspecialchars($img, ENT_QUOTES, 'UTF-8') : 'https://via.placeholder.com/120?text=Gulay';
                        echo '<div class="suki-card"><a href="'.$shop_link.'"><img src="'.$img_src.'" onerror="this.src=\'https://via.placeholder.com/120?text=Gulay\'" loading="lazy"></a><p style="font-weight:700; margin:10px 0 0 0; font-size:0.85rem; color:#234723;">'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'</p></div>';
                    }
                } else {
                    echo '<p style="grid-column:1/-1; text-align:center; color:#666;">Wala pang products. Add ka muna sa Supabase!</p>';
                }
            } catch(Exception $e){
                echo '<p style="grid-column:1/-1; text-align:center; color:red;">Error: '.htmlspecialchars($e->getMessage()).'</p>';
            }

            if (isset($conn)) {
                if (method_exists($conn, 'close')) { $conn->close(); } 
                else { $conn = null; }
            }
            ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
