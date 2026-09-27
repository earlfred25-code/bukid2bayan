<?php
session_start();
if(!isset($_SESSION['oauth_pending'])){
    header("Location: /register.php"); exit();
}
$oauth = $_SESSION['oauth_pending'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    include __DIR__.'/../db_connect.php';
    $is_pdo = $conn instanceof PDO;
    $role = $_POST['role'];
    if(!in_array($role, ['buyer','farmer','seller'])) $role='buyer';

    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $farm_name = trim($_POST['farm_name']?? '');
    $farm_location = trim($_POST['farm_location']?? '');
    $email = $oauth['email'];
    $google_id = $oauth['google_id'];
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    try{
        if($is_pdo){
            $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$username,$email,$dummy_pass,$google_id,$role,$phone,true]);
            $uid=$conn->lastInsertId();
            // try save farm info kung may column
            try{
                if($farm_name || $farm_location){
                    $conn->prepare("UPDATE users SET farm_name=?, farm_location=? WHERE id=?")->execute([$farm_name, $farm_location, $uid]);
                }
            }catch(Exception $e){}
        } else {
            $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,?)");
            $is_verified=1;
            $stmt->bind_param("ssssssi",$username,$email,$dummy_pass,$google_id,$role,$phone,$is_verified);
            $stmt->execute(); $uid=$stmt->insert_id;
        }
        unset($_SESSION['oauth_pending']);
        $_SESSION['user_id']=$uid;
        $_SESSION['user_name']=$username;
        $_SESSION['role']=$role;
        $_SESSION['is_admin']=0;

        if($role == 'farmer' || $role == 'seller'){
            header("Location: /farmer_dashboard.php");
        } else {
            header("Location: /buyer_dashboard.php");
        }
        exit();
    }catch(Exception $e){ $error=$e->getMessage(); }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tapusin Profile - Bukid2Bayan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white w-full max-w-md rounded-xl shadow p-5">
    <div class="text-center mb-5">
        <img src="<?= htmlspecialchars($oauth['picture'])?>" class="w-14 h-14 rounded-full mx-auto mb-2" onerror="this.style.display='none'">
        <h2 class="text-xl font-bold text-green-800">Konting info nalang</h2>
        <p class="text-sm text-gray-500">Hi <?= htmlspecialchars(explode(' ', $oauth['name'])[0])?>, last step na</p>
    </div>

    <?php if(isset($error)):?><div class="bg-red-100 text-red-700 p-2 rounded mb-3 text-sm"><?= htmlspecialchars($error)?></div><?php endif;?>

    <form method="POST" class="space-y-3">
        <div>
            <label class="text-sm">Pangalan</label>
            <input name="username" value="<?= htmlspecialchars($oauth['name'])?>" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
        </div>
        <div>
            <label class="text-sm">Email</label>
            <input value="<?= htmlspecialchars($oauth['email'])?>" disabled class="w-full border bg-gray-100 rounded-lg px-3 py-2 mt-1 text-sm">
        </div>
        <div>
            <label class="text-sm">Number mo</label>
            <input name="phone" placeholder="09xxxxxxxxx" required class="w-full border rounded-lg px-3 py-2 mt-1 text-sm">
        </div>

        <div>
            <label class="text-sm block mb-2">Ano ka sa Bukid2Bayan?</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="buyer" checked class="peer sr-only">
                    <div class="border rounded-xl p-3 text-center peer-checked:border-green-600 peer-checked:bg-green-50">
                        <div class="text-xl">🛒</div>
                        <div class="font-semibold text-sm">Mamimili</div>
                        <div class="text-xs text-gray-500">Bibili lang</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="farmer" class="peer sr-only">
                    <div class="border rounded-xl p-3 text-center peer-checked:border-green-600 peer-checked:bg-green-50">
                        <div class="text-xl">🌾</div>
                        <div class="font-semibold text-sm">Magsasaka</div>
                        <div class="text-xs text-gray-500">Magbebenta</div>
                    </div>
                </label>
            </div>
        </div>

        <div id="farmer_fields" class="hidden space-y-2 border-t pt-3">
            <input name="farm_name" placeholder="Pangalan ng farm (optional)" class="w-full border rounded-lg px-3 py-2 text-sm">
            <input name="farm_location" placeholder="Saan yung farm mo?" class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-2.5 rounded-lg font-semibold">Tuloy</button>
    </form>
</div>
<script>
const farmerDiv = document.getElementById('farmer_fields');
document.querySelectorAll('input[name=role]').forEach(r=>{
    r.addEventListener('change',()=>{
        if(r.value === 'farmer' && r.checked){
            farmerDiv.classList.remove('hidden');
        } else if(r.value === 'buyer' && r.checked){
            farmerDiv.classList.add('hidden');
        }
    });
});
</script>
</body>
</html>
