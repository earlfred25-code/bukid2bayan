<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include __DIR__.'/db_connect.php';

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    try{
        $is_tmp = $conn instanceof PDO;
        $uid_cookie = (int)$_COOKIE['user_id'];
        if($is_tmp){
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id =? LIMIT 1");
            $st->execute([$uid_cookie]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id =? LIMIT 1");
            $st->bind_param("i", $uid_cookie);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
        }
        if($u){
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['username'];
            $_SESSION['role'] = $u['role']?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin']?? 0;
        }
    }catch(Exception $e){}
}

if(!isset($_SESSION['user_id']) &&!isset($_COOKIE['user_id'])){
    header("Location: login.php");
    exit();
}

$is_pdo = $conn instanceof PDO;
$uid = (int)($_SESSION['user_id']?? $_COOKIE['user_id']?? 0);
$user = null;
$db_error = null;

try{
    if($is_pdo){
        $stmt = $conn->prepare("SELECT id, username, email, role, phone_number, is_verified, is_admin, google_id, created_at FROM users WHERE id =? LIMIT 1");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt = $conn->prepare("SELECT id, username, email, role, phone_number, is_verified, is_admin, google_id, created_at FROM users WHERE id =? LIMIT 1");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        $stmt->close();
    }
}catch(Exception $e){
    $db_error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Account - Bukid2Bayan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-[#f5f5f5] min-h-screen">
<?php include __DIR__.'/header.php';?>
<div class="max-w- mx-auto px-4 py-6 flex gap-6">
    <div class="w- hidden md:block shrink-0">
        <div class="flex gap-3 items-center py-4 border-b border-black/5">
            <div class="w-12 h-12 rounded-full bg-[#2d7a3e] text-white flex items-center justify-center font-black text-lg">
                <?= strtoupper(substr($user['username']?? 'U',0,1))?>
            </div>
            <div class="leading-tight">
                <div class="font-bold text-sm truncate w-"><?= htmlspecialchars($user['username']?? 'User')?></div>
                <div class="text-xs text-black/50"><i class="fas fa-pen text-"></i> Edit Profile</div>
            </div>
        </div>
        <div class="mt-6 space-y-6 text-sm">
            <div>
                <div class="flex items-center gap-2 font-bold"><i class="far fa-user text-blue-500"></i> My Account</div>
                <div class="ml-7 mt-2 space-y-2 text- text-black/70">
                    <div class="text-[#2d7a3e] font-semibold">Profile</div>
                    <a href="#" class="block hover:text-[#2d7a3e]">Banks & Cards</a>
                    <a href="#" class="block hover:text-[#2d7a3e]">Addresses</a>
                    <a href="#" class="block hover:text-[#2d7a3e]">Change Password</a>
                </div>
            </div>
            <a href="my_orders.php" class="flex items-center gap-2 hover:text-[#2d7a3e]"><i class="fas fa-receipt text-blue-400"></i> My Purchase</a>
            <a href="notifications.php" class="flex items-center gap-2 hover:text-[#2d7a3e]"><i class="far fa-bell text-orange-400"></i> Notifications</a>
            <a href="farmer_centre.php" class="flex items-center gap-2 hover:text-[#2d7a3e]"><i class="fas fa-store text-green-500"></i> Farmer Centre</a>
        </div>
    </div>
    <div class="flex-1 bg-white rounded-sm shadow-sm">
        <div class="p-6 border-b">
            <div class="text- font-medium">My Profile</div>
            <div class="text- text-black/60 mt-1">Manage and protect your account</div>
        </div>
        <?php if(isset($db_error)):?>
            <div class="m-6 bg-red-50 border border-red-200 text-red-700 p-3 rounded text-sm"><?= htmlspecialchars($db_error)?></div>
        <?php endif;?>
        <?php if(!$user):?>
            <div class="p-6">Walang nahanap na user. Session ID: <?= htmlspecialchars($_SESSION['user_id']?? $_COOKIE['user_id']?? '')?></div>
        <?php else:?>
        <div class="p-6 flex flex-col lg:flex-row gap-8">
            <div class="flex-1">
                <div class="space-y-6">
                    <div class="flex items-center">
                        <div class="w- text-right pr-6 text-sm text-black/60">Username</div>
                        <div class="flex-1 text-sm font-medium"><?= htmlspecialchars($user['username'])?></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w- text-right pr-6 text-sm text-black/60">Pangalan</div>
                        <div class="flex-1"><input value="<?= htmlspecialchars($user['username'])?>" class="w-full max-w- border border-black/10 rounded-sm px-3 py-2 text-sm outline-none focus:border-black/20"></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w- text-right pr-6 text-sm text-black/60">Email</div>
                        <div class="flex-1 flex items-center gap-3">
                            <div class="text-sm"><?= htmlspecialchars($user['email'])?></div>
                            <?php if(!empty($user['is_verified'])):?><span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded"><i class="fas fa-check"></i> Verified</span><?php endif;?>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="w- text-right pr-6 text-sm text-black/60">Phone</div>
                        <div class="flex-1 text-sm"><?= htmlspecialchars($user['phone_number']?? 'Wala pa')?></div>
                    </div>
                    <div class="flex items-center">
                        <div class="w- text-right pr-6 text-sm text-black/60">Role</div>
                        <div class="flex-1"><span class="bg-[#f5f7f5] border px-3 py-1 rounded-full text-xs capitalize"><?= htmlspecialchars($user['role']?? 'buyer')?></span></div>
                    </div>
                    <div class="flex items-center pt-4">
                        <div class="w-"></div>
                        <button class="bg-[#2d7a3e] hover:bg-[#236332] text-white px-6 py-2 rounded-sm text-sm">Save</button>
                    </div>
                </div>
            </div>
            <div class="w-full lg:w- border-t lg:border-t-0 lg:border-l border-black/5 flex flex-col items-center pt-6">
                <div class="w-24 h-24 rounded-full bg-gray-100 border flex items-center justify-center text-3xl font-black text-[#2d7a3e]">
                    <?= strtoupper(substr($user['username']?? 'U',0,1))?>
                </div>
                <button class="mt-4 border border-black/10 px-6 py-1.5 text-sm rounded-sm hover:bg-black/5">Select Image</button>
                <div class="text-xs text-black/40 mt-3 text-center px-6 leading-4">File size max 1MB<br>Format: JPEG, PNG</div>
                <a href="logout.php" class="mt-8 w-full bg-[#ee4d2d] hover:bg-[#d73211] text-white py-2 rounded-sm text-sm text-center">Logout</a>
            </div>
        </div>
        <?php endif;?>
    </div>
</div>
<?php include __DIR__.'/footer.php';?>
</body>
</html>
