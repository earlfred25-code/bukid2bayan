<?php
include 'db_connect.php';
$new = password_hash('admin123', PASSWORD_DEFAULT);
$is_pdo = $conn instanceof PDO;
if($is_pdo){
    $stmt=$conn->prepare("UPDATE users SET password=?, is_admin=1 WHERE email=?");
    $stmt->execute([$new,'admin@agriconnect.com']);
} else {
    $stmt=$conn->prepare("UPDATE users SET password=? WHERE email=?");
    $stmt->bind_param("ss",$new,$email);
    $email='admin@agriconnect.com';
    $stmt->execute();
}
echo "reset done to admin123";
?>
