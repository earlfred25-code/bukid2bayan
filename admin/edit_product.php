<?php
$page_title = 'Edit Product';
include 'admin_header.php';

$product = ['id' => '', 'name' => '', 'farmer_name' => '', 'description' => '', 'price' => '', 'unit' => 'kg', 'image_url' => ''];
$is_edit_mode = false;

// Check if we are editing an existing product
if (isset($_GET['id'])) {
    $is_edit_mode = true;
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found.";
        exit;
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Collect and sanitize form data
    $name = $_POST['name'];
    $farmer_name = $_POST['farmer_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    $image_url = $_POST['image_url']; // For simplicity, we'll handle URL directly. File upload is a bigger topic.
    $product_id = $_POST['id'];

    if ($is_edit_mode) {
        // Update existing product
        $stmt = $conn->prepare("UPDATE products SET name=?, farmer_name=?, description=?, price=?, unit=?, image_url=? WHERE id=?");
        $stmt->bind_param("sssdssi", $name, $farmer_name, $description, $price, $unit, $image_url, $product_id);
    } else {
        // Insert new product
        $stmt = $conn->prepare("INSERT INTO products (name, farmer_name, description, price, unit, image_url) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdss", $name, $farmer_name, $description, $price, $unit, $image_url);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Product saved successfully!'); window.location.href='manage_products.php';</script>";
    } else {
        echo "<script>alert('Error saving product.');</script>";
    }
    exit;
}
?>

<style>
/* Additional styles for the form */
.form-card { background: white; padding: 2rem; border-radius: 8px; max-width: 800px; margin: 0 auto; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
.form-group { margin-bottom: 1.5rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; }
.form-group input, .form-group textarea { width: 100%; padding: 0.8rem; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
</style>

<div class="container">
    <div class="form-card">
        <h2><?php echo $is_edit_mode ? 'Edit Product' : 'Add New Product'; ?></h2>
        <form action="edit_product.php<?php echo $is_edit_mode ? '?id='.$product['id'] : ''; ?>" method="post">
            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
            
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="farmer_name">Farmer's Name</label>
                <input type="text" id="farmer_name" name="farmer_name" value="<?php echo htmlspecialchars($product['farmer_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="price">Price (₱)</label>
                <input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
            <div class="form-group">
                <label for="unit">Unit (e.g., kg, tali, piece)</label>
                <input type="text" id="unit" name="unit" value="<?php echo htmlspecialchars($product['unit']); ?>" required>
            </div>
            <div class="form-group">
                <label for="image_url">Image Path (e.g., images/product.jpg)</label>
                <input type="text" id="image_url" name="image_url" value="<?php echo htmlspecialchars($product['image_url']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary">Save Product</button>
            <a href="manage_products.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>