<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: login.php"); exit();
}
$user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 0;
$user_role = $_SESSION['role'] ?? $_SESSION['user']['role'] ?? '';
if ($user_role !== 'farmer') {
    if ($user_role === 'buyer') {
        header("Location: index.php"); exit();
    }
}
$is_pdo = $conn instanceof PDO;
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? 'gulay');
    $description = trim($_POST['description'] ?? '');
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (in_array($ext, $allowed)) {
            $safe_name = strtolower(preg_replace('/[^a-z0-9]+/','_', $name)) . '_' . time() . '.' . $ext;
            $target_dir = __DIR__ . '/images/';
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
            $target = $target_dir . $safe_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_url = 'images/' . $safe_name;
            }
        }
    }
    if ($name !== '' && $price > 0) {
        try {
            if ($is_pdo) {
                try {
                    $stmt = $conn->prepare("INSERT INTO products (name, price, stock, category, description, image_url, farmer_id) VALUES (?,?,?,?,?,?,?)");
                    $stmt->execute([$name, $price, $stock, $category, $description, $image_url, $user_id]);
                } catch(Exception $e){
                    $stmt = $conn->prepare("INSERT INTO products (name, price, image_url) VALUES (?,?,?)");
                    $stmt->execute([$name, $price, $image_url]);
                }
                $message = "Product added!";
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, price, stock, category, description, image_url, farmer_id) VALUES (?,?,?,?,?,?,?)");
                if (!$stmt) {
                    $stmt = $conn->prepare("INSERT INTO products (name, price, image_url) VALUES (?,?,?)");
                    $stmt->bind_param("sds", $name, $price, $image_url);
                } else {
                    $stmt->bind_param("sdissii", $name, $price, $stock, $category, $description, $image_url, $user_id);
                    if (!$stmt) {
                        $stmt = $conn->prepare("INSERT INTO products (name, price, image_url) VALUES (?,?,?)");
                        $stmt->bind_param("sds", $name, $price, $image_url);
                    }
                }
                if ($stmt->execute()) $message = "Product added!";
                $stmt->close();
            }
        } catch(Exception $e){
            $message = "Error: " . $e->getMessage();
        }
    }
}
$my_products = [];
$total_products = 0;
try {
    if ($is_pdo) {
        try {
            $stmt = $conn->prepare("SELECT id, name, price, stock, image_url FROM products WHERE farmer_id = ? ORDER BY id DESC");
            $stmt->execute([$user_id]);
            $my_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            $stmt = $conn->query("SELECT id, name, price, image_url FROM products ORDER BY id DESC LIMIT 20");
            $my_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $total_products = count($my_products);
    } else {
        $stmt = $conn->prepare("SELECT id, name, price, stock, image_url FROM products WHERE farmer_id = ? ORDER BY id DESC");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $res = $stmt->get_result();
            while($r = $res->fetch_assoc()) $my_products[] = $r;
            $stmt->close();
        } else {
            $res = $conn->query("SELECT id, name, price, image_url FROM products ORDER BY id DESC LIMIT 20");
            while($r = $res->fetch_assoc()) $my_products[] = $r;
        }
        $total_products = count($my_products);
    }
} catch(Exception $e){}
include 'header.php';
?>
<style>
.dashboard{ max-width:1200px; margin:0 auto; padding:24px 16px; }
.stats{ display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:24px; }
.stat{ background:rgba(255,255,255,0.92); border-radius:14px; padding:18px; box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.form-card{ background:rgba(255,255,255,0.92); border-radius:16px; padding:20px; margin-bottom:24px; box-shadow:0 4px 16px rgba(0,0,0,0.06); }
.product-grid{ display:grid; grid-template-columns:repeat(2,1fr); gap:12px; }
.product-item{ background:#fff; border-radius:12px; padding:12px; display:flex; gap:10px; align-items:center; border:1px solid #eee; }
.product-item img{ width:60px; height:60px; object-fit:contain; background:#f1f8e9; border-radius:8px; }
@media(min-width:768px){ .product-grid{ grid-template-columns:repeat(3,1fr);} }
@media(max-width:600px){ .stats{ grid-template-columns:1fr; } }
</style>
<div class="dashboard">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:20px;">
        <div>
            <h1 style="margin:0; font-weight:900; color:#1a2e35;">Farmer / Seller Dashboard</h1>
            <p style="margin:4px 0 0 0; color:#666;">Manage your ani at benta</p>
        </div>
        <a href="index.php" style="background:#111; color:#fff; padding:10px 18px; border-radius:8px; text-decoration:none; font-weight:700;">View Shop</a>
    </div>
    <?php if($message): ?>
    <div style="background:#e6f7f4; border:1px solid #2a9d8f; color:#1a2e35; padding:12px; border-radius:8px; margin-bottom:16px;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    <div class="stats">
        <div class="stat">
            <div style="color:#666; font-size:0.85rem;">Total Products</div>
            <div style="font-size:1.8rem; font-weight:900; color:#234723;"><?php echo $total_products; ?></div>
        </div>
        <div class="stat">
            <div style="color:#666; font-size:0.85rem;">Pending Orders</div>
            <div style="font-size:1.8rem; font-weight:900; color:#d97706;">0</div>
        </div>
        <div class="stat">
            <div style="color:#666; font-size:0.85rem;">Total Sales</div>
            <div style="font-size:1.8rem; font-weight:900; color:#2a9d8f;">₱0.00</div>
        </div>
    </div>
    <div style="display:grid; grid-template-columns:1fr; gap:24px;">
        <div class="form-card">
            <h3 style="margin:0 0 14px 0;"><i class="fas fa-plus" style="color:#2a9d8f;"></i> Magdagdag ng Product</h3>
            <form method="post" enctype="multipart/form-data">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="grid-column:1/-1;">
                        <label style="font-weight:700; font-size:0.9rem;">Product Name</label>
                        <input type="text" name="name" required placeholder="Sili Labuyo, Eggplant, Bigas" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.9rem;">Price (₱)</label>
                        <input type="number" step="0.01" name="price" required placeholder="50.00" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.9rem;">Stock (kg/pcs)</label>
                        <input type="number" name="stock" required placeholder="100" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;">
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.9rem;">Category</label>
                        <select name="category" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;">
                            <option value="gulay">Gulay</option>
                            <option value="prutas">Prutas</option>
                            <option value="bigas">Bigas</option>
                            <option value="itlog">Itlog</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-weight:700; font-size:0.9rem;">Image</label>
                        <input type="file" name="image" accept="image/*" style="width:100%; padding:8px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;">
                    </div>
                    <div style="grid-column:1/-1;">
                        <label style="font-weight:700; font-size:0.9rem;">Description</label>
                        <textarea name="description" rows="2" placeholder="Fresh from farm..." style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin-top:6px;"></textarea>
                    </div>
                </div>
                <button type="submit" name="add_product" style="margin-top:14px; width:100%; background:#2a9d8f; color:#fff; padding:12px; border:none; border-radius:10px; font-weight:800; cursor:pointer;">Add Product</button>
            </form>
        </div>
        <div class="form-card">
            <h3 style="margin:0 0 14px 0;">My Products (<?php echo $total_products; ?>)</h3>
            <?php if(count($my_products)==0): ?>
                <p style="color:#666; text-align:center; padding:20px;">Wala ka pang product. Magdagdag ka sa taas.</p>
            <?php else: ?>
            <div class="product-grid">
                <?php foreach($my_products as $p):
                    $img = $p['image_url'] ?? '';
                    $src = $img ? (strpos($img,'http')===0 ? $img : '/'.ltrim($img,'/')) : '/images/sili.jpg';
                ?>
                <div class="product-item">
                    <img src="<?php echo htmlspecialchars($src); ?>" alt="">
                    <div style="flex:1;">
                        <div style="font-weight:700; font-size:0.9rem;"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div style="color:#2a9d8f; font-weight:800;">₱<?php echo number_format($p['price'] ?? 0,2); ?></div>
                        <?php if(isset($p['stock'])): ?><div style="font-size:0.75rem; color:#666;">Stock: <?php echo $p['stock']; ?></div><?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
