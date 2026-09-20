<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
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
        if (!$res) {
            $res = $conn->query("SELECT * FROM products ORDER BY id DESC");
        }
        while($r = $res->fetch_assoc()) $products[] = $r;
    }
} catch(Exception $e){}
include 'admin_header.php';
?>
<div style="padding:20px; max-width:1200px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2 style="margin:0;">Manage Products (<?php echo count($products); ?>)</h2>
        <a href="../farmer_dashboard.php" style="background:#2a9d8f; color:#fff; padding:8px 14px; border-radius:8px; text-decoration:none;">Add Product</a>
    </div>
    <div style="background:#fff; border-radius:12px; overflow:auto; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
        <table style="width:100%; border-collapse:collapse; font-size:0.9rem;">
            <tr style="background:#f5f5f5; text-align:left;">
                <th style="padding:10px;">Image</th>
                <th style="padding:10px;">Name</th>
                <th style="padding:10px;">Price</th>
                <th style="padding:10px;">Stock</th>
                <th style="padding:10px;">Farmer</th>
                <th style="padding:10px;">Action</th>
            </tr>
            <?php foreach($products as $p): 
                $img = $p['image_url'] ?? '';
                $src = $img ? (strpos($img,'http')===0 ? $img : '../'.ltrim($img,'/')) : '../images/sili.jpg';
            ?>
            <tr style="border-top:1px solid #eee;">
                <td style="padding:8px;"><img src="<?php echo htmlspecialchars($src); ?>" style="width:50px; height:50px; object-fit:contain; background:#f1f8e9; border-radius:6px;"></td>
                <td style="padding:8px; font-weight:700;"><?php echo htmlspecialchars($p['name']); ?></td>
                <td style="padding:8px;">₱<?php echo number_format($p['price'] ?? 0,2); ?></td>
                <td style="padding:8px;"><?php echo $p['stock'] ?? '0'; ?></td>
                <td style="padding:8px;"><?php echo htmlspecialchars($p['farmer_name'] ?? 'Admin'); ?></td>
                <td style="padding:8px;">
                    <a href="edit_product.php?id=<?php echo $p['id']; ?>" style="color:#2a9d8f; font-weight:700; text-decoration:none; margin-right:8px;">Edit</a>
                    <a href="manage_products.php?delete=<?php echo $p['id']; ?>" onclick="return confirm('Delete?')" style="color:#e74c3c; font-weight:700; text-decoration:none;">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>
