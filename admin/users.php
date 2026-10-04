<?php
$page_title="Users - Bukid2Bayan";
include 'admin_header.php';
if (isset($_GET['delete_user'])) {
    $del=(int)$_GET['delete_user']; if($del!=($_SESSION['user_id']??0)){
        if($is_pdo){ $s=$conn->prepare("DELETE FROM users WHERE id=?"); $s->execute([$del]); }
        else { $s=$conn->prepare("DELETE FROM users WHERE id=?"); $s->bind_param("i",$del); $s->execute(); $s->close(); }
    } header("Location: users.php?msg=Deleted"); exit();
}
if (isset($_GET['toggle_admin'])) {
    $tid=(int)$_GET['toggle_admin'];
    if($is_pdo){ $s=$conn->prepare("UPDATE users SET is_admin = CASE WHEN is_admin=1 THEN 0 ELSE 1 END WHERE id=?"); $s->execute([$tid]); }
    else { $s=$conn->prepare("UPDATE users SET is_admin = CASE WHEN is_admin=1 THEN 0 ELSE 1 END WHERE id=?"); $s->bind_param("i",$tid); $s->execute(); $s->close(); }
    header("Location: users.php?msg=Admin+updated"); exit();
}
$users=[]; try{
if($is_pdo){ $s=$conn->query("SELECT id, username, email, role, is_admin, created_at FROM users ORDER BY id DESC"); $users=$s->fetchAll(PDO::FETCH_ASSOC); }
else { $res=$conn->query("SELECT id, username, email, role, is_admin, created_at FROM users ORDER BY id DESC"); while($r=$res->fetch_assoc()) $users[]=$r; }
}catch(Exception $e){}
?>
<h1 style="margin:0 0 6px 0;font-size:1.6rem;font-weight:900;">Users (<?php echo count($users);?>)</h1>
<?php if(isset($_GET['msg'])): ?><div class="card" style="background:#ecfdf5;color:#065f46;font-weight:800;text-align:center;"><?php echo htmlspecialchars($_GET['msg']);?></div><?php endif;?>
<div class="card"><div class="table-wrap"><table><tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Admin</th><th>Action</th></tr>
<?php foreach($users as $u):?><tr><td><?php echo $u['id'];?></td><td><?php echo htmlspecialchars($u['username']);?></td><td><?php echo htmlspecialchars($u['email']);?></td><td><span class="badge" style="background:#111;"><?php echo $u['role'];?></span></td><td><?php echo $u['is_admin']?'Yes':'No';?></td><td><a href="users.php?toggle_admin=<?php echo $u['id'];?>" class="btn">Toggle Admin</a> <a href="users.php?delete_user=<?php echo $u['id'];?>" onclick="return confirm('Delete?')" class="btn danger">Delete</a></td></tr><?php endforeach;?>
</table></div></div></div></div></body></html>
