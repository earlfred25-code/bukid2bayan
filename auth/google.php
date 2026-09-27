<?php
session_start();
$config = include __DIR__.'/config.php';
$g = $config['google'];

if(!isset($_GET['code'])){
    $params = http_build_query([
        'client_id' => $g['client_id'],
        'redirect_uri' => $g['redirect_uri'],
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    header("Location: https://accounts.google.com/o/oauth2/v2/auth?$params"); exit();
} else {
    $code = $_GET['code'];
    $ch = curl_init('https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'code' => $code,
        'client_id' => $g['client_id'],
        'client_secret' => $g['client_secret'],
        'redirect_uri' => $g['redirect_uri'],
        'grant_type' => 'authorization_code'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = json_decode(curl_exec($ch), true); curl_close($ch);

    if(!isset($res['access_token'])){
        echo "<pre>"; print_r($res); echo "</pre>"; die();
    }

    $userInfo = json_decode(file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=".$res['access_token']), true);
    include __DIR__.'/../db_connect.php';
    $is_pdo = $conn instanceof PDO;
    $email = $userInfo['email'];

    // Check kung existing na
    if($is_pdo){
        $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
        $stmt->execute([$email]); $u=$stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc();
    }

    if($u){
        // Luma na user - rekta login na
        $_SESSION['user_id']=$u['id']; $_SESSION['user_name']=$u['id']; $_SESSION['role']=$u['role']; $_SESSION['is_admin']=$u['is_admin']??0;
        if($u['role'] == 'farmer' || $u['role'] == 'seller'){
            header("Location: /farmer_dashboard.php");
        } else {
            header("Location: /buyer_dashboard.php");
        }
        exit();
    } else {
        // BAGONG USER - dalhin sa fill up page
        $_SESSION['oauth_pending'] = [
            'email' => $userInfo['email'],
            'name' => $userInfo['name'],
            'google_id' => $userInfo['id'],
            'picture' => $userInfo['picture']?? ''
        ];
        header("Location: /auth/complete_profile.php"); exit();
    }
}
