<?php
session_start();
include 'db_connect.php'; 
include 'header.php';

$search_term = '';
$is_search = false;
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search_term = trim($_GET['search']);
    $is_search = true;
    $search_param = "%" . $search_term . "%";
    $stmt = $conn->prepare("SELECT id, name, farmer_name, price, unit, image_url FROM products WHERE name LIKE ? OR farmer_name LIKE ? ORDER BY name ASC");
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT id, name, farmer_name, price, unit, image_url FROM products ORDER BY name ASC";
    $result = $conn->query($sql);
}
?>

<div class="page-title-header" style="background:#2a9d8f; padding:28px 0;">
    <div class="container" style="text-align:center; max-width:1120px; margin:0 auto; padding:0 20px;">
        <?php if ($is_search): ?>
            <h1 style="font-size:2.2rem; color:#fff; margin:0;">Search: "<?= htmlspecialchars($search_term) ?>"</h1>
            <p style="font-size:1.05rem; margin-top:8px; color:#fff;">Found <strong><?= $result ? $result->num_rows : 0 ?></strong> results <a href="products.php" style="color:#fff; background:rgba(0,0,0,0.2); padding:6px 14px; border-radius:8px; text-decoration:none; margin-left:8px;">Clear</a></p>
        <?php else: ?>
            <h1 style="font-size:2.6rem; color:#fff; margin:0; font-weight:900;">All Products</h1>
            <p style="font-size:1.1rem; margin-top:8px; color:#fff;">Fresh from farmers • <?= $result ? $result->num_rows : 0 ?> items available</p>
        <?php endif; ?>
    </div>
</div>

<div class="container" style="max-width:1120px; margin:20px auto; padding:0 20px;">
    <section class="product-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(270px, 1fr)); gap:20px;">
        <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $pid = $row["id"];
        ?>
            <div class="product-card" style="background:#fff; border:1px solid #ddd; border-radius:16px; padding:14px; display:flex; flex-direction:column;">
                <img src="<?= htmlspecialchars($row["image_url"]) ?>" alt="<?= htmlspecialchars($row["name"]) ?>" style="width:100%; height:200px; object-fit:cover; border-radius:12px; border:1px solid #eee;" onerror="this.src='https://via.placeholder.com/300x200?text=Fresh+Produce'">
                <h3 style="font-size:1.25rem; font-weight:900; margin:12px 0 4px 0; color:#111;"><?= htmlspecialchars($row["name"]) ?></h3>
                <p style="font-size:0.9rem; color:#444; margin:0 0 6px 0; font-weight:600;"><i class="fas fa-user" style="color:#2a9d8f;"></i> from <?= htmlspecialchars($row["farmer_name"]) ?></p>
                <p style="font-size:1.3rem; font-weight:900; margin:0 0 12px 0; color:#111;">₱<?= number_format($row["price"], 2) ?> <span style="font-size:0.85rem; font-weight:600; color:#555;">/ <?= htmlspecialchars($row["unit"]) ?></span></p>

                <form action="cart_handler.php" method="post" style="margin-top:auto;">
                    <input type="hidden" name="product_id" value="<?= $pid ?>">
                    <!-- QTY NA PWEDE I-EDIT -->
                    <div style="display:flex; align-items:center; gap:6px; background:#f8f8f8; border:1px solid #ddd; border-radius:10px; padding:6px 8px; margin-bottom:10px;">
                        <span style="font-weight:800; font-size:0.9rem;">Dami:</span>
                        <button type="button" onclick="let i=this.nextElementSibling; i.value=Math.max(1, parseInt(i.value||1)-1)" style="width:36px; height:36px; font-weight:900; background:#fff; border:2px solid #ccc; border-radius:8px; cursor:pointer;">−</button>
                        <input type="number" name="quantity" value="1" min="1" max="999" style="width:60px; text-align:center; font-weight:900; font-size:1.1rem; padding:6px; border:2px solid #2a9d8f; border-radius:8px;">
                        <button type="button" onclick="let i=this.previousElementSibling; i.value=parseInt(i.value||0)+1" style="width:36px; height:36px; font-weight:900; background:#2a9d8f; color:#fff; border:none; border-radius:8px; cursor:pointer;">+</button>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button type="submit" name="action" value="add" style="flex:1; background:#fff; border:2px solid #2a9d8f; color:#2a9d8f; padding:10px; border-radius:10px; font-weight:800; cursor:pointer;"><i class="fas fa-cart-plus"></i> Add</button>
                        <button type="submit" name="action" value="buy_now" style="flex:1; background:#2a9d8f; border:none; color:#fff; padding:10px; border-radius:10px; font-weight:900; cursor:pointer;">Buy Now</button>
                    </div>
                </form>
            </div>
        <?php
            }
        } else {
            echo '<div style="grid-column:1/-1; text-align:center; background:#fff; padding:40px; border-radius:14px; border:1px solid #ddd;"><h3>No products found</h3><a href="products.php" style="background:#2a9d8f; color:#fff; padding:10px 20px; border-radius:8px; text-decoration:none;">Browse All</a></div>';
        }
        if (isset($stmt)) $stmt->close();
        $conn->close();
        ?>
    </section>
</div>

<?php include 'footer.php'; ?>