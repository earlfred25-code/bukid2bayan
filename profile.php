<?php
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: /login.php");
    exit();
}
include __DIR__.'/db_connect.php';
$is_pdo = $conn instanceof PDO;
$uid = $_SESSION['user_id'];
try{
    if($is_pdo){
        $stmt = $conn->prepare("SELECT id, username, email, role, phone_number, is_verified, is_admin, google_id FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, role, phone_number, is_verified, is_admin, google_id FROM users WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
    }
}catch(Exception $e){
    $user = null;
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profile - Bukid2Bayan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">
<?php include __DIR__.'/header.php'; ?>
<div class="max-w-3xl mx-auto p-4 mt-6">
    <?php if(isset($db_error)): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-4 text-sm break-all"><?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>
    <?php if(!$user): ?>
        <div class="bg-white p-6 rounded-2xl shadow">Walang nahanap na user. Session ID: <?= htmlspecialchars($_SESSION['user_id']) ?></div>
    <?php else: ?>
        <div class="bg-white p-6 rounded-2xl shadow">
            <h1 class="text-xl font-extrabold text-green-800">Profile</h1>
            <div class="mt-4 space-y-3 text-sm">
                <div><span class="font-bold text-gray-600">Pangalan:</span> <?= htmlspecialchars($user['username']?? '') ?></div>
                <div><span class="font-bold text-gray-600">Email:</span> <?= htmlspecialchars($user['email']?? '') ?></div>
                <div><span class="font-bold text-gray-600">Number:</span> <?= htmlspecialchars($user['phone_number']?? 'Wala pa') ?></div>
                <div><span class="font-bold text-gray-600">Role:</span> <?= htmlspecialchars($user['role']?? 'buyer') ?></div>
                <div><span class="font-bold text-gray-600">Verified:</span> <?= !empty($user['is_verified']) ? 'Yes' : 'No' ?></div>
                <div><span class="font-bold text-gray-600">Google ID:</span> <?= htmlspecialchars($user['google_id']?? 'Hindi Google login') ?></div>
            </div>
            <a href="/auth/logout.php" class="inline-block mt-6 bg-green-600 text-white px-5 py-2.5 rounded-xl font-bold">Logout</a>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
