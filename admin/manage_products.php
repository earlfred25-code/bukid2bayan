<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../login.php"); exit();
}
include '../db_connect.php';
$is_pdo = $conn instanceof PDO;
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        if ($is_pdo) {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    } catch(Exception $e){}
    header("Location: manage_products.php"); exit();
}
$products = [];
try {
    if ($is_pdo) {
        try {
            $stmt = $conn->query("SELECT p.*, u.username as farmer_name FROM products p LEFT JOIN users u ON p.farmer_id = u.id ORDER BY p.id DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(Exception $e){
            $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } else {
        $res = $conn->query("SELECT p.*, u.username as farmer_name FROM products p LEFT JOIN users u ON p.farmer_id = u.id ORDER BY p.id DESC");
        if (!$res) { $res = $conn->query("SELECT * FROM products ORDER BY id DESC"); }
        while($r = $res->fetch_assoc()) $products[] = $r;
    }
} catch(Exception $e){}
$page_title = "Manage Products - Bukid2Bayan";
include 'admin_header.php';
?>
<div style="max-width:1200px;margin:0 auto;padding:24px 20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <div>
            <h2 style="margin:0;font-size:1.8rem;font-weight:900;letter-spacing:-0.5px;"><i class="fas fa-box" style="color:#2a9d8f;"></i> Manage Products</h2>
            <p style="margin:4px 0 0 0;color:#888;font-size:0.9rem;">Total <?php echo count($products); ?> products - Gulay, Bigas, Itlog</p>
        </div>
        <a href="../farmer_dashboard.php" style="background:#2a9d8f;color:#fff;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:800;font-size:0.9rem;display:inline-flex;align-items:center;gap:6px;"><i class="fas fa-plus"></i> Add Product</a>
    </div>

    <div style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(0,0,0,0.06);border:1px solid #eee;">
        <div style="overflow:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.9rem;min-width:700px;">
                <tr style="background:#f8faf8;text-align:left;border-bottom:1px solid #eee;">
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Image</th>
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Name</th>
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Price</th>
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Stock</th>
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Farmer</th>
                    <th style="padding:12px 14px;font-weight:800;color:#555;">Action</th>
                </tr>
                <?php foreach($products as $p): 
                    $img = $p['image_url'] ?? '';
                    $src = $img ? (strpos($img,'http')===0 ? $img : '../'.ltrim($img,'/')) : '../images/sili.jpg';
                ?>
                <tr style="border-top:1px solid #f0f0f0;">
                    <td style="padding:10px 14px;"><img src="<?php echo htmlspecialchars($src); ?>" style="width:52px;height:52px;object-fit:cover;background:#f1f8e9;border-radius:10px;border:1px solid #e5e7eb;"></td>
                    <td style="padding:10px 14px;font-weight:800;color:#111;"><?php echo htmlspecialchars($p['name']); ?></td>
                    <td style="padding:10px 14px;"><span style="background:#e6f7f4;color:#134e4a;padding:4px 8px;border-radius:8px;font-weight:800;">₱<?php echo number_format($p['price'] ?? 0,2); ?></span></td>
                    <td style="padding:10px 14px;"><?php echo $p['stock'] ?? '0'; ?></td>
                    <td style="padding:10px 14px;color:#666;"><?php echo htmlspecialchars($p['farmer_name'] ?? 'Admin'); ?></td>
                    <td style="padding:10px 14px;">
                        <a href="edit_product.php?id=<?php echo $p['id']; ?>" style="background:#111;color:#fff;padding:6px 10px;border-radius:8px;font-weight:700;text-decoration:none;margin-right:6px;font-size:0.8rem;display:inline-block;"><i class="fas fa-pen"></i> Edit</a>
                        <a href="manage_products.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete this product?')" style="background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:6px 10px;border-radius:8px;font-weight:700;text-decoration:none;font-size:0.8rem;display:inline-block;"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($products)): ?>
                <tr><td colspan="6" style="padding:24px;text-align:center;color:#888;">No products found</td></tr>
                <?php endif; ?>
            </table>
        </div>
    </div>
</div>
</main>
</body>
</html>
