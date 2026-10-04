<?php
$page_title="Orders - Bukid2Bayan";
include 'admin_header.php';
if(isset($_GET['delete_order'])){ $oid=(int)$_GET['delete_order']; if($is_pdo){ $conn->prepare("DELETE FROM order_items WHERE order_id=?")->execute([$oid]); $conn->prepare("DELETE FROM orders WHERE id=?")->execute([$oid]); } else { $s=$conn->prepare("DELETE FROM order_items WHERE order_id=?"); $s->bind_param("i",$oid); $s->execute(); $s->close(); $s=$conn->prepare("DELETE FROM orders WHERE id=?"); $s->bind_param("i",$oid); $s->execute(); $s->close(); } header("Location: orders.php?msg=Deleted"); exit(); }
if(isset($_GET['status']) && isset($_GET['oid'])){ $nst=$_GET['status']; $oid=(int)$_GET['oid']; $allowed=['pending','to_ship','shipped','completed','cancelled']; if(in_array($nst,$allowed)){ if($is_pdo){ $s=$conn->prepare("UPDATE orders SET status=? WHERE id=?"); $s->execute([$nst,$oid]); } else { $s=$conn->prepare("UPDATE orders SET status=? WHERE id=?"); $s->bind_param("si",$nst,$oid); $s->execute(); $s->close(); } } header("Location: orders.php"); exit(); }
$orders=[]; try{
if($is_pdo){ $s=$conn->query("SELECT id, customer_name, phone, total_amount, payment_method, status, tracking_number, created_at FROM orders ORDER BY id DESC LIMIT 200"); $orders=$s->fetchAll(PDO::FETCH_ASSOC); }
else { $res=$conn->query("SELECT id, customer_name, phone, total_amount, payment_method, status, tracking_number, created_at FROM orders ORDER BY id DESC LIMIT 200"); while($r=$res->fetch_assoc()) $orders[]=$r; }
}catch(Exception $e){}
?>
<h1 style="margin:0 0 6px 0;font-size:1.6rem;font-weight:900;">Orders (<?php echo count($orders);?>)</h1>
<div class="card"><div class="table-wrap"><table><tr><th>ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Action</th></tr>
<?php foreach($orders as $o):?><tr><td>#<?php echo $o['id'];?></td><td><?php echo htmlspecialchars($o['customer_name']);?><br><small><?php echo htmlspecialchars($o['phone']);?></small></td><td>₱<?php echo number_format($o['total_amount'],2);?></td><td><span class="badge" style="background:<?php echo $o['status']=='pending'?'#f59e0b':($o['status']=='completed'?'#10b981':'#3b82f6');?>"><?php echo $o['status'];?></span></td><td><select onchange="location='orders.php?oid=<?php echo $o['id'];?>&status='+this.value"><option>Change</option><option value="pending">pending</option><option value="to_ship">to_ship</option><option value="shipped">shipped</option><option value="completed">completed</option><option value="cancelled">cancelled</option></select> <a href="orders.php?delete_order=<?php echo $o['id'];?>" class="btn danger">Delete</a></td></tr><?php endforeach;?>
</table></div></div></div></div></body></html>
