<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
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
include 'admin_header.php';
?>
<div style="padding:20px; max-width:600px; margin:0 auto;">
    <h2>Edit Product</h2>
    <?php if($msg): ?><div style="background:#e6f7f4; padding:10px; border-radius:8px; margin-bottom:12px;"><?php echo $msg; ?></div><?php endif; ?>
    <?php if($product): ?>
    <form method="post" style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.06);">
        <label style="font-weight:700;">Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin:6px 0 12px 0;">
        <label style="font-weight:700;">Price</label>
        <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin:6px 0 12px 0;">
        <label style="font-weight:700;">Stock</label>
        <input type="number" name="stock" value="<?php echo $product['stock'] ?? 0; ?>" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin:6px 0 12px 0;">
        <label style="font-weight:700;">Category</label>
        <select name="category" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin:6px 0 12px 0;">
            <option value="gulay" <?php echo ($product['category']??'')=='gulay'?'selected':''; ?>>Gulay</option>
            <option value="prutas" <?php echo ($product['category']??'')=='prutas'?'selected':''; ?>>Prutas</option>
            <option value="bigas" <?php echo ($product['category']??'')=='bigas'?'selected':''; ?>>Bigas</option>
            <option value="itlog" <?php echo ($product['category']??'')=='itlog'?'selected':''; ?>>Itlog</option>
        </select>
        <label style="font-weight:700;">Description</label>
        <textarea name="description" rows="3" style="width:100%; padding:10px; border:1.5px solid #ccc; border-radius:8px; margin:6px 0 12px 0;"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
        <button type="submit" style="width:100%; background:#111; color:#fff; padding:12px; border:none; border-radius:8px; font-weight:800;">Save Changes</button>
        <a href="manage_products.php" style="display:block; text-align:center; margin-top:10px; color:#666; text-decoration:none;">Back</a>
    </form>
    <?php else: echo "Product not found"; endif; ?>
</div>
