<?php
session_start();

// SECURITY CHECK
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../index.php");
    exit();
}

include '../db_connect.php';

// Get quick stats for 35-69 admin - kita agad numbers
$product_count = $conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'] ?? 0;
$user_count = $conn->query("SELECT COUNT(*) as c FROM users WHERE is_admin = 0")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard -  BUKID2BAYAN</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root { --primary: #2a9d8f; --dark: #1a2e35; --bg: #f4f6f8; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Lato', sans-serif; background: var(--bg); color: #333; font-size: 16px; line-height: 1.6; }
        
        .admin-header { 
            background: var(--dark); color: white; 
            padding: 1rem 2rem; 
            display: flex; justify-content: space-between; align-items: center; 
            flex-wrap: wrap; gap: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
        }
        .admin-header h1 { font-family: 'Montserrat', sans-serif; font-size: 1.6rem; }
        .admin-header h1 i { color: var(--primary); }
        .admin-nav { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; }
        .admin-nav a { color: #ddd; text-decoration: none; font-size: 0.9rem; font-weight: 700; padding: 8px 12px; border-radius: 6px; transition: 0.2s; }
        .admin-nav a:hover, .admin-nav a.active { background: rgba(255,255,255,0.12); color: white; }
        .admin-nav a.logout { background: #e76f51; color: white; }
        
        .admin-container { max-width: 1100px; margin: 0 auto; padding: 2rem 1.5rem; }
        
        .admin-welcome { 
            background: white; padding: 2rem; border-radius: 14px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e8e8e8;
            margin-bottom: 2rem;
        }
        .admin-welcome h2 { font-family: 'Montserrat', sans-serif; font-size: 1.7rem; color: var(--dark); margin-bottom: 8px; }
        .admin-welcome p { color: #555; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin: 20px 0 28px 0; }
        .stat-card { background: white; padding: 18px; border-radius: 12px; border: 1px solid #e8e8e8; text-align: center; }
        .stat-card i { font-size: 1.8rem; color: var(--primary); margin-bottom: 8px; }
        .stat-card h3 { font-size: 1.8rem; font-weight: 800; color: var(--dark); }
        .stat-card p { font-size: 0.85rem; color: #666; font-weight: 700; }
        
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 18px; }
        .action-card { 
            background: white; padding: 22px; border-radius: 14px; 
            border: 1px solid #e8e8e8; box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            transition: 0.2s; text-decoration: none; color: inherit; display: block;
        }
        .action-card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); border-color: var(--primary); }
        .action-card i { font-size: 2rem; color: var(--primary); margin-bottom: 12px; }
        .action-card h3 { font-size: 1.15rem; margin-bottom: 6px; color: var(--dark); }
        .action-card p { font-size: 0.9rem; color: #666; margin-bottom: 14px; }
        .action-card .btn { display: inline-block; background: var(--primary); color: white; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; }
    </style>
</head>
<body>

    <header class="admin-header">
        <h1><i class="fas fa-leaf"></i>  BUKID2BAYAN Admin</h1>
        <nav class="admin-nav">
            <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="products.php"><i class="fas fa-box"></i> Products (<?php echo $product_count; ?>)</a>
            <a href="#"><i class="fas fa-shopping-cart"></i> Orders</a>
            <a href="#"><i class="fas fa-users"></i> Users (<?php echo $user_count; ?>)</a>
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
            <a href="../logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </header>

    <div class="admin-container">
        <div class="admin-welcome">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! 👋</h2>
            <p>Here's what's happening with your  BUKID2BAYAN store today. Everything is designed for easy management — big buttons, clear numbers.</p>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-seedling"></i>
                    <h3><?php echo $product_count; ?></h3>
                    <p>Total Products</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $user_count; ?></h3>
                    <p>Registered Customers</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shopping-basket"></i>
                    <h3>0</h3>
                    <p>Pending Orders</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-chart-line"></i>
                    <h3>₱0</h3>
                    <p>Today's Sales</p>
                </div>
            </div>
        </div>

        <div class="actions-grid">
            <a href="products.php" class="action-card">
                <i class="fas fa-plus-circle"></i>
                <h3>Manage Products</h3>
                <p>Add new vegetables, update prices, change photos. Your 6 featured products are shown on homepage.</p>
                <span class="btn"><i class="fas fa-arrow-right"></i> Go to Products</span>
            </a>
            
            <a href="#" class="action-card">
                <i class="fas fa-clipboard-list"></i>
                <h3>View Orders</h3>
                <p>See new orders from customers, update delivery status, and contact buyers.</p>
                <span class="btn"><i class="fas fa-arrow-right"></i> View Orders</span>
            </a>
            
            <a href="../products.php" target="_blank" class="action-card">
                <i class="fas fa-eye"></i>
                <h3>Preview Live Site</h3>
                <p>See how customers (age 35-69) experience your store. Check if text is clear and buttons are easy.</p>
                <span class="btn"><i class="fas fa-external-link-alt"></i> Open Store</span>
            </a>
        </div>

        <div style="margin-top: 28px; background: #e6f7f5; border: 1px solid #bde5df; padding: 16px; border-radius: 10px; font-size: 0.9rem;">
            <strong><i class="fas fa-lightbulb" style="color: var(--primary);"></i> Tip for 35-69 admin:</strong> Keep product names short (e.g., "Fresh Tomato" not "Premium Organic Vine-Ripened Tomato") and prices with peso sign. Customers scan quickly.
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>