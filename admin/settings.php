<?php
$page_title="Settings - Bukid2Bayan";
include 'admin_header.php';
$stats=['users'=>0,'orders'=>0,'products'=>0]; try{
if($is_pdo){ $stats['users']=(int)$conn->query("SELECT COUNT(*) FROM users")->fetchColumn(); $stats['orders']=(int)$conn->query("SELECT COUNT(*) FROM orders")->fetchColumn(); $stats['products']=(int)$conn->query("SELECT COUNT(*) FROM products")->fetchColumn(); }
else { $r=$conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc(); $stats['users']=$r['c']; $r=$conn->query("SELECT COUNT(*) as c FROM orders")->fetch_assoc(); $stats['orders']=$r['c']; $r=$conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc(); $stats['products']=$r['c']; }
}catch(Exception $e){}
?>
<h1 style="margin:0 0 6px 0;font-size:1.6rem;font-weight:900;">Settings</h1>
<div class="card"><p><b>Site:</b> Bukid2Bayan</p><p><b>Admin:</b> <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin');?></p><p><b>PHP:</b> <?php echo PHP_VERSION;?></p></div>
<div class="card"><h3>Stats</h3><p>Users: <?php echo $stats['users'];?> | Orders: <?php echo $stats['orders'];?> | Products: <?php echo $stats['products'];?></p>
<a href="users.php" class="btn primary">Manage Users</a> <a href="orders.php" class="btn primary">Manage Orders</a> <a href="index.php" class="btn">Dashboard</a>
</div></div></div></body></html>
