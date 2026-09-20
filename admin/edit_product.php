<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); exit();
}
include '../db_connect.php';
$is_pdo = $conn instanceof PDO;
$id = intval($_GET['id'] ?? 0);
$msg = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? 'gulay');
    $desc = trim($_POST['description'] ?? '');
    try {
        if ($is_pdo) {
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=? WHERE id=?");
            $stmt->execute([$name,$price,$stock,$category,$desc,$id]);
        } else {
            $stmt = $conn->prepare("UPDATE products SET name=?, price=?, stock=?, category=?, description=? WHERE id=?");
            $stmt->bind_param("sdissi", $name,$price,$stock,$category,$desc,$id);
            $stmt->execute();
            $stmt->close();
        }
        $msg = "Updated!";
    } catch(Exception $e){ $msg = "Error: ".$e->getMessage(); }
}
$product = null;
try {
    if ($is_pdo) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $res = $stmt->get_result();
        $product = $res->fetch_assoc();
        $stmt->close();
    }
} catch(Exception $e){}
$page_title = "Edit Product - Bukid2Bayan";
include 'admin_header.php';
?>
<div style="max-width:640px;margin:0 auto;padding:24px 20px;">
    <div style="margin-bottom:18px;">
        <a href="manage_products.php" style="text-decoration:none;color:#666;font-weight:700;font-size:0.9rem;"><i class="fas fa-arrow-left"></i> Back to Products</a>
        <h2 style="margin:10px 0 4px 0;font-size:1.8rem;font-weight:900;letter-spacing:-0.5px;"><i class="fas fa-pen-to-square" style="color:#2a9d8f;"></i> Edit Product</h2>
        <p style="margin:0;color:#888;font-size:0.9rem;">ID #<?php echo $id; ?> - Bukid2Bayan</p>
    </div>

    <?php if($msg): ?>
        <div style="background:<?php echo strpos($msg,'Error')!==false?'#fef2f2':'#e6f7f4'; ?>;border:1.5px solid <?php echo strpos($msg,'Error')!==false?'#fecaca':'#b2dfd8'; ?>;color:<?php echo strpos($msg,'Error')!==false?'#991b1b':'#134e4a'; ?>;padding:12px 14px;border-radius:12px;margin-bottom:16px;font-weight:700;font-size:0.9rem;display:flex;gap:8px;align-items:center;">
            <i class="fas <?php echo strpos($msg,'Error')!==false?'fa-exclamation-triangle':'fa-check-circle'; ?>"></i>
            <span><?php echo htmlspecialchars($msg); ?></span>
        </div>
    <?php endif; ?>

    <?php if($product): ?>
    <form method="post" style="background:#fff;padding:22px;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="margin-bottom:14px;">
            <label style="font-weight:800;font-size:0.85rem;color:#111;display:block;margin-bottom:6px;">Product Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width:100%;padding:12px 14px;border:1.8px solid #d1d5db;border-radius:10px;font-size:1rem;outline:none;box-sizing:border-box;">
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
            <div>
                <label style="font-weight:800;font-size:0.85rem;color:#111;display:block;margin-bottom:6px;">Price (₱)</label>
                <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required style="width:100%;padding:12px 14px;border:1.8px solid #d1d5db;border-radius:10px;font-size:1rem;outline:none;box-sizing:border-box;">
            </div>
            <div>
                <label style="font-weight:800;font-size:0.85rem;color:#111;display:block;margin-bottom:6px;">Stock</label>
                <input type="number" name="stock" value="<?php echo $product['stock'] ?? 0; ?>" style="width:100%;padding:12px 14px;border:1.8px solid #d1d5db;border-radius:10px;font-size:1rem;outline:none;box-sizing:border-box;">
            </div>
        </div>
        <div style="margin-bottom:14px;">
            <label style="font-weight:800;font-size:0.85rem;color:#111;display:block;margin-bottom:6px;">Category</label>
            <select name="category" style="width:100%;padding:12px 14px;border:1.8px solid #d1d5db;border-radius:10px;font-size:1rem;outline:none;background:#fff;box-sizing:border-box;">
                <option value="gulay" <?php echo ($product['category']??'')=='gulay'?'selected':''; ?>>Gulay</option>
                <option value="prutas" <?php echo ($product['category']??'')=='prutas'?'selected':''; ?>>Prutas</option>
                <option value="bigas" <?php echo ($product['category']??'')=='bigas'?'selected':''; ?>>Bigas</option>
                <option value="itlog" <?php echo ($product['category']??'')=='itlog'?'selected':''; ?>>Itlog</option>
            </select>
        </div>
        <div style="margin-bottom:18px;">
            <label style="font-weight:800;font-size:0.85rem;color:#111;display:block;margin-bottom:6px;">Description</label>
            <textarea name="description" rows="3" style="width:100%;padding:12px 14px;border:1.8px solid #d1d5db;border-radius:10px;font-size:1rem;outline:none;resize:vertical;box-sizing:border-box;"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" style="width:100%;background:#111;color:#fff;padding:14px;border:none;border-radius:12px;font-weight:900;font-size:1rem;cursor:pointer;"><i class="fas fa-save"></i> Save Changes</button>
        <a href="manage_products.php" style="display:block;text-align:center;margin-top:12px;color:#888;text-decoration:none;font-weight:700;font-size:0.9rem;">Cancel</a>
    </form>
    <?php else: ?>
        <div style="background:#fff;padding:24px;border-radius:14px;text-align:center;box-shadow:0 4px 12px rgba(0,0,0,0.05);">Product not found</div>
    <?php endif; ?>
</div>
</main>
</body>
</html>
