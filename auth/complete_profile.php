<?php
session_start();
$config = include __DIR__.'/config.php';
if(!isset($_SESSION['oauth_pending'])){
    header("Location: /register.php");
    exit();
}
$oauth = $_SESSION['oauth_pending'];
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $recaptcha = $_POST['g-recaptcha-response']?? '';
    if(empty($recaptcha)){
        $error = "Check mo muna yung I'm not a robot sa baba.";
    } else {
        $secret = $config['recaptcha']['secret_key']?? $_ENV['RECAPTCHA_SECRET_KEY']?? '6LethNEtAAAAALpakvndTVHdialFIBgv5JO8RMsE';
        $verify_url = "https://www.google.com/recaptcha/api/siteverify";
        $data = http_build_query(['secret' => $secret, 'response' => $recaptcha, 'remoteip' => $_SERVER['REMOTE_ADDR']]);
        $options = ['http' => ['method' => 'POST', 'header' => 'Content-type: application/x-www-form-urlencoded', 'content' => $data]];
        $context = stream_context_create($options);
        $result = file_get_contents($verify_url, false, $context);
        $verify = json_decode($result, true);
        if(!$verify['success']){
            $error = "Check mo muna yung I'm not a robot sa baba.";
        } else {
            include __DIR__.'/../db_connect.php';
            $is_pdo = $conn instanceof PDO;
            $role = $_POST['role'];
            if(!in_array($role,['buyer','farmer','seller'])) $role='buyer';
            $username = trim($_POST['username']);
            $phone = trim($_POST['phone']);
            $email = $oauth['email'];
            $google_id = $oauth['google_id'];
            $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
            if(empty($username) || empty($phone)){
                $error = "Kumpletohin mo info mo.";
            } else {
                try{
                    if($is_pdo){
                        $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,1)");
                        $stmt->execute([$username,$email,$dummy_pass,$google_id,$role,$phone]);
                        $uid=$conn->lastInsertId();
                    } else {
                        $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,phone_number,is_verified) VALUES (?,?,?,?,?,?,?)");
                        $is_verified=1;
                        $stmt->bind_param("ssssssi",$username,$email,$dummy_pass,$google_id,$role,$phone,$is_verified);
                        $stmt->execute();
                        $uid=$stmt->insert_id;
                    }
                    unset($_SESSION['oauth_pending']);
                    $_SESSION['user_id']=$uid;
                    $_SESSION['user_name']=$username;
                    $_SESSION['role']=$role;
                    $_SESSION['is_admin']=0;
                    header("Location: /".($role=='farmer'||$role=='seller'?'farmer_centre.php':'index.php'));
                    exit();
                }catch(Exception $e){
                    $error = "May kamukha ka nang email/username. Try iba.";
                }
            }
        }
    }
}
$site_key = $config['recaptcha']['site_key']?? $_ENV['RECAPTCHA_SITE_KEY']?? '6LethNEtAAAAAL5PWr5m0eVNyr81u6b-EpAs3jwp';
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tapusin Profile - Bukid2Bayan</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative bg-cover bg-center bg-no-repeat bg-fixed" style="background-image: url('/images/farm.jpg'); background-color: #14532d;">
<div class="absolute inset-0 bg-green-900/70 backdrop-blur-"></div>
<div class="w-full max-w-md relative z-10">
    <div class="bg-white/95 backdrop-blur-xl rounded- shadow-[0_25px_70px_-20px_rgba(0,0,0,0.5)] overflow-hidden">
        <div class="px-6 pt-6 pb-4 text-center">
            <img src="<?= htmlspecialchars($oauth['picture']?? '')?>" class="w-16 h-16 rounded-full mx-auto border-2 border-green-500 shadow-md" onerror="this.style.display='none'">
            <h2 class="text-xl font-extrabold text-green-800 mt-3">Konting info nalang</h2>
            <p class="text-gray-500 text-sm">Hi <?= htmlspecialchars(explode(' ', $oauth['name'])[0]?? 'kap')?>, last step na to</p>
        </div>
        <div class="px-6 pb-6">
            <?php if(isset($error)):?><div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl mb-4 text-sm"><?= htmlspecialchars($error)?></div><?php endif;?>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="text-xs font-bold text-gray-600">Pangalan</label>
                    <input name="username" value="<?= htmlspecialchars($oauth['name']?? '')?>" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Email</label>
                    <input value="<?= htmlspecialchars($oauth['email']?? '')?>" disabled class="w-full bg-gray-100 border rounded-xl px-4 py-3 mt-1 text-sm text-gray-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Number mo</label>
                    <input name="phone" placeholder="09xxxxxxxxx" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-4 py-3 mt-1 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="text-xs font-bold text-gray-600">Ano ka sa Bukid2Bayan?</label>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="buyer" checked class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-2xl p-4 text-center peer-checked:border-green-600 peer-checked:bg-green-50 transition">
                                <div class="text-2xl">🛒</div>
                                <div class="font-bold text-sm">Mamimili</div>
                                <div class="text-xs text-gray-500">Bibili lang</div>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="role" value="farmer" class="peer sr-only">
                            <div class="border-2 border-gray-200 rounded-2xl p-4 text-center peer-checked:border-green-600 peer-checked:bg-green-50 transition">
                                <div class="text-2xl">🌾</div>
                                <div class="font-bold text-sm">Magsasaka</div>
                                <div class="text-xs text-gray-500">Magbebenta</div>
                            </div>
                        </label>
                    </div>
                </div>
                <div class="flex justify-center pt-2">
                    <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($site_key)?>"></div>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-green-600/30 transition">Tuloy</button>
            </form>
        </div>
    </div>
    <p class="text-center text-xs text-white/70 mt-4">Bukid2Bayan • Fresh from farm</p>
</div>
</body>
</html>
