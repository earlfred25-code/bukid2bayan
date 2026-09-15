<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';

if (!isset($_SESSION['user_id']) && !isset($_SESSION['user']) && !isset($_SESSION['loggedin'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'User';

if (isset($_GET['read_all'])) {
    $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $user_id");
    header('Location: notifications.php');
    exit();
}

if (isset($_GET['read_id'])) {
    $rid = (int)$_GET['read_id'];
    $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $rid AND user_id = $user_id");
    header('Location: notifications.php');
    exit();
}

include 'header.php';
?>

<div class="container" style="max-width:800px; margin:24px auto; padding:0 20px;">
    <div style="background:#fff; border:1px solid #e5e5e5; border-radius:16px; padding:24px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 style="font-size:2rem; font-weight:900; margin:0;"><i class="fas fa-bell" style="color:#2a9d8f;"></i> Notifications</h1>
            <p style="color:#666; margin:6px 0 0 0;">Hi <?= htmlspecialchars($user_name) ?>, eto mga updates mo</p>
        </div>
        <a href="notifications.php?read_all=1" style="background:#f8f8f8; border:1px solid #ddd; padding:10px 14px; border-radius:10px; font-weight:800; text-decoration:none; color:#111;">Mark all as read</a>
    </div>

    <div style="display:grid; gap:12px;">
    <?php
    $result = $conn->query("SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 50");
    
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $is_unread = $row['is_read'] == 0;
            $bg = $is_unread ? '#e6f7f5' : '#fff';
            $border = $is_unread ? '1.5px solid #2a9d8f' : '1px solid #e5e5e5';
            echo '<div style="background:'.$bg.'; border:'.$border.'; border-radius:14px; padding:16px 18px; display:flex; gap:14px; align-items:flex-start;">';
            echo '<div style="width:40px; height:40px; background:#fff; border:1px solid #eee; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"><i class="fas fa-leaf" style="color:#2a9d8f;"></i></div>';
            echo '<div style="flex:1;">';
            echo '<div style="display:flex; justify-content:space-between; gap:10px;"><b style="font-size:1.05rem;">'.htmlspecialchars($row['title']).'</b><span style="font-size:0.8rem; color:#666;">'.date('M d, h:i A', strtotime($row['created_at'])).'</span></div>';
            echo '<p style="margin:6px 0 0 0; color:#333; line-height:1.5;">'.htmlspecialchars($row['message']).'</p>';
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
        echo '<div style="margin-top:18px; display:flex; gap:8px; justify-content:center;">';
        echo '<a href="products.php" style="background:#2a9d8f; color:#fff; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;">Shop Now</a>';
        echo '<a href="farmer_centre.php" style="background:#fff; border:2px solid #2a9d8f; color:#2a9d8f; padding:10px 18px; border-radius:10px; text-decoration:none; font-weight:800;">Go to Farmer Centre</a>';
        echo '</div></div>';
    }
    $conn->close();
    ?>
    </div>
</div>

<?php include 'footer.php'; ?>