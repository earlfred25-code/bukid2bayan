<?php
session_start();
if(!isset($_SESSION['oauth_pending'])){
    header("Location: /register.php"); exit();
}
$oauth = $_SESSION['oauth_pending'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    include __DIR__.'/../db_connect.php';
    $is_pdo = $conn instanceof PDO;
    $role = $_POST['role']; // buyer o farmer
    if(!in_array($role, ['buyer','farmer','seller'])) $role='buyer';

    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $email = $oauth['email'];
    $google_id = $oauth['google_id'];
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

    try{
        if($is_pdo){
            $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$username,$email,$dummy_pass,$google_id,$role,$phone,true]);
            $uid=$conn->lastInsertId();
        } else {
            $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,?)");
            $is_verified=1; $stmt->bind_param("ssssssi",$username,$email,$dummy_pass,$google_id,$role,$phone,$is_verified);
            $stmt->execute(); $uid=$stmt->insert_id;
        }
        unset($_SESSION['oauth_pending']);
        $_SESSION['user_id']=$uid; $_SESSION['user_name']=$username; $_SESSION['role']=$role; $_SESSION['is_admin']=0;

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
<title>Kumpletuhin Profile - Bukid2Bayan</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-green-50 min-h-screen flex items-center justify-center p-4">
<div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6">
    <div class="text-center mb-6">
        <img src="<?= htmlspecialchars($oauth['picture'])?>" class="w-16 h-16 rounded-full mx-auto mb-2" onerror="this.style.display='none'">
        <h2 class="text-2xl font-bold text-green-800">Kumpletuhin mo muna</h2>
        <p class="text-sm text-gray-500">Hi <?= htmlspecialchars($oauth['name'])?>! Pili ka ng account type</p>
    </div>

    <?php if(isset($error)):?><div class="bg-red-100 text-red-700 p-2 rounded mb-3 text-sm"><?= htmlspecialchars($error)?></div><?php endif;?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="text-sm font-semibold">Full Name</label>
            <input name="username" value="<?= htmlspecialchars($oauth['name'])?>" required class="w-full border rounded-lg px-3 py-2 mt-1">
        </div>
        <div>
            <label class="text-sm font-semibold">Email (verified by Google)</label>
            <input value="<?= htmlspecialchars($oauth['email'])?>" disabled class="w-full border bg-gray-100 rounded-lg px-3 py-2 mt-1">
        </div>
        <div>
            <label class="text-sm font-semibold">Cellphone Number</label>
            <input name="phone" placeholder="09xxxxxxxxx" required class="w-full border rounded-lg px-3 py-2 mt-1">
        </div>

        <div>
            <label class="text-sm font-semibold mb-2 block">Anong gagawin mo sa Bukid2Bayan?</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="buyer" checked class="peer sr-only">
                    <div class="border-2 rounded-xl p-4 text-center peer-checked:border-green-600 peer-checked:bg-green-50">
                        <div class="text-2xl">🛒</div>
                        <div class="font-bold text-sm mt-1">Buyer</div>
                        <div class="text- text-gray-500">Bibili ng gulay/prutas</div>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="role" value="farmer" class="peer sr-only">
                    <div class="border-2 rounded-xl p-4 text-center peer-checked:border-green-600 peer-checked:bg-green-50">
                        <div class="text-2xl">👨‍🌾</div>
                        <div class="font-bold text-sm mt-1">Farmer / Seller</div>
                        <div class="text- text-gray-500">Magbebenta ng ani</div>
                    </div>
                </label>
            </div>
        </div>

        <div id="farmer_fields" class="hidden space-y-3 border-t pt-3">
            <input name="farm_name" placeholder="Pangalan ng Farm (optional)" class="w-full border rounded-lg px-3 py-2">
            <input name="farm_location" placeholder="Location ng Farm (Baryo, Bayan)" class="w-full border rounded-lg px-3 py-2">
        </div>

        <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-xl font-bold hover:bg-green-700">Magpatuloy at Mag-Login</button>
        <p class="text- text-center text-gray-400">✅ Google verified na yung email mo kaya safe na.</p>
    </form>
</div>
<script>
document.querySelectorAll('input[name=role]').forEach(r=>{
    r.addEventListener('change',()=>{
        document.getElementById('farmer_fields').classList.toggle('hidden', r.value==='buyer' && r.checked? false : r.value==='buyer');
        if(r.value!=='buyer' && r.checked) document.getElementById('farmer_fields').classList.remove('hidden');
        if(r.value==='buyer' && r.checked) document.getElementById('farmer_fields').classList.add('hidden');
    })
})
</script>
</body>
</html>
