<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
if (!isset($_SESSION['user_id']) &&!isset($_SESSION['user']) &&!isset($_SESSION['loggedin'])) { header('Location: login.php'); exit(); }
$user_id = (int)($_SESSION['user_id']?? $_SESSION['user']['id']?? 0);
$user_name = $_SESSION['user_name'] ?? $_SESSION['user']['name'] ?? 'User';
$is_pdo = $conn instanceof PDO;
try {
    if($is_pdo){
        $conn->exec("CREATE TABLE IF NOT EXISTS notifications (id SERIAL PRIMARY KEY, user_id INT, title VARCHAR(255), message TEXT, is_read INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT, title VARCHAR(255), message TEXT, is_read INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    }
} catch(Exception $e){}
if (isset($_GET['read_all'])) {
    try {
        if($is_pdo){
            $s=$conn->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?");
            $s->execute([$user_id]);
        } else {
            $conn->query("UPDATE notifications SET is_read=1 WHERE user_id=$user_id");
        }
    } catch(Exception $e){}
    header('Location: notifications.php'); exit();
}
if (isset($_GET['read_id'])) {
    $rid = (int)$_GET['read_id'];
    try {
        if($is_pdo){
            $s=$conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?");
            $s->execute([$rid,$user_id]);
        } else {
            $conn->query("UPDATE notifications SET is_read=1 WHERE id=$rid AND user_id=$user_id");
        }
    } catch(Exception $e){}
    header('Location: notifications.php'); exit();
}
include 'header.php';
?>
<style>
.notif-wrap{ max-width:800px; margin:20px auto; padding:0 16px; }
@media(max-width:600px){ .notif-wrap{ margin-top:10px; padding:0 12px; } }
</style>
<div class="notif-wrap">
    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:24px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
        <div>
            <h1 style="font-size:clamp(1.4rem,4vw,2rem); font-weight:900; margin:0;"><i class="fas fa-bell" style="color:#2a9d8f;"></i> Notifications</h1>
            <p style="color:#666; margin:6px 0 0 0;">Hi <?= htmlspecialchars($user_name) ?>, eto mga updates mo</p>
        </div>
        <a href="notifications.php?read_all=1" style="background:#f8f8f8; border:1px solid #ddd; padding:10px 14px; border-radius:10px; font-weight:800; text-decoration:none; color:#111; white-space:nowrap;">Mark all as read</a>
    </div>
    <div style="display:grid; gap:12px;">
    <?php
    try {
        if($is_pdo){
            $s=$conn->prepare("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50");
            $s->execute([$user_id]);
            $rows=$s->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = $conn->query("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id=$user_id ORDER BY created_at DESC LIMIT 50");
            $rows=[]; if($result){ while($r=$result->fetch_assoc()) $rows[]=$r; }
        }
        if(count($rows)>0){
            foreach($rows as $row){
                $is_unread = $row['is_read']==0;
                $bg = $is_unread ? '#e6f7f5' : '#fff';
                $border = $is_unread ? '1.5px solid #2a9d8f' : '1px solid #e5e5e5';
                echo '<div style="background:'.$bg.'; border:'.$border.'; border-radius:14px; padding:16px 18px; display:flex; gap:14px; align-items:flex-start;">';
                echo '<div style="width:40px; height:40px; background:#fff; border:1px solid #eee; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-leaf" style="color:#2a9d8f;"></i></div>';
                echo '<div style="flex:1; min-width:0;">';
                echo '<div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;"><b style="font-size:1.05rem;">'.htmlspecialchars($row['title']).'</b><span style="font-size:0.8rem; color:#666; white-space:nowrap;">'.date('M d, h:i A', strtotime($row['created_at'])).'</span></div>';
                echo '<p style="margin:6px 0 0 0; color:#333; line-height:1.5; word-break:break-word;">'.htmlspecialchars($row['message']).'</p>';
                if ($is_unread) {
                    echo '<a href="notifications.php?read_id='.$row['id'].'" style="display:inline-block; margin-top:10px; font-weight:800; font-size:0.85rem; color:#2a9d8f; text-decoration:none;">Mark as read</a>';
                }
                echo '</div></div>';
            }
        } else {
            echo '<div style="background:#fff; border:1px solid #e5e5e5; border-radius:14px; padding:40px 20px; text-align:center;">';
            echo '<i class="fas fa-bell-slash" style="font-size:2.2rem; color:#bbb; margin-bottom:10px;"></i>';
            echo '<p style="font-weight:800; font-size:1.2rem; margin:0;">Wala pa notification</p>';
            echo '<p style="color:#666; margin:8px 0 0 0;">Dito lalabas pag may order update, pag may bumili sa products mo, o pag may promo.</p>';
            echo '<div style="margin-top:18px; display:flex; gap:8px; justify-content:center; flex-wrap:wrap;">';
            echo '<a href="products.php" style="background:#2a9d8f; color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;">Shop Now</a>';
            echo '<a href="farmer_centre.php" style="background:#fff; border:2px solid #2a9d8f; color:#2a9d8f; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;">Go to Farmer Centre</a>';
            echo '</div></div>';
        }
    } catch(Exception $e){
        echo '<div style="background:#fff; padding:20px; border-radius:12px; text-align:center; color:#666;">No notifications yet</div>';
    }
    if($is_pdo){ $conn=null; } else { if(method_exists($conn,'close')) $conn->close(); }
    ?>
    </div>
</div>
<?php include 'footer.php'; ?>
