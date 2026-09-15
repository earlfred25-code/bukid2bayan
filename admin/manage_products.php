
<?php 
$page_title = 'Manage Products';
include 'admin_header.php'; 

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $product_id_to_delete = $_GET['id'];
    $delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $delete_stmt->bind_param("i", $product_id_to_delete);
    if ($delete_stmt->execute()) {
        echo "<script>alert('Product deleted successfully!'); window.location.href='manage_products.php';</script>";
    } else {
        echo "<script>alert('Error deleting product.');</script>";
    }
}
?>

<div class="container">
    <div class="page-header">
        <h2>Products List</h2>
        <a href="edit_product.php" class="btn btn-primary">Add New Product</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Unit</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = $conn->query("SELECT id, name, price, unit, image_url FROM products ORDER BY id DESC");
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td><img src='../" . htmlspecialchars($row['image_url']) . "' alt='" . htmlspecialchars($row['name']) . "' width='60'></td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>₱" . number_format($row['price'], 2) . "</td>";
                    echo "<td>" . htmlspecialchars($row['unit']) . "</td>";
                    echo "<td class='actions'>";
                    echo "    <a href='edit_product.php?id=" . $row['id'] . "' class='btn btn-secondary'>Edit</a>";
                    // Added a confirmation dialog for deleting
                    echo "    <a href='manage_products.php?action=delete&id=" . $row['id'] . "' class='btn btn-danger' onclick='return confirm(\"Are you sure you want to delete this product?\");'>Delete</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No products found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; // You can create a simple admin_footer.php if you prefer ?>