<?php
session_start();
$config = include __DIR__.'/config.php';
$g = $config['google'];

if(!isset($_GET['code'])){
    // Step 1: Redirect kay Google
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
    // Step 2: Kunin token
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
    
    if(!isset($res['access_token'])){ die("Google login failed"); }
    
    // Step 3: Kunin info ni user
    $userInfo = json_decode(file_get_contents("https://www.googleapis.com/oauth2/v2/userinfo?access_token=".$res['access_token']), true);
    
    include __DIR__.'/../db_connect.php';
    $is_pdo = $conn instanceof PDO;
    $email = $userInfo['email']; $name = $userInfo['name']; $google_id = $userInfo['id'];
    
    try{
        if($is_pdo){
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]); $u=$stmt->fetch();
            if(!$u){
                $stmt=$conn->prepare("INSERT INTO users (username,email,google_id,role,is_verified) VALUES (?,?,?,?,?)");
                $stmt->execute([$name,$email,$google_id,'buyer',true]);
                $uid=$conn->lastInsertId();
            } else { $uid=$u['id']; }
        } else {
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s",$email); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc();
            if(!$u){
                $stmt=$conn->prepare("INSERT INTO users (username,email,google_id,role,is_verified) VALUES (?,?,?,?,?)");
                $role='buyer'; $is_verified=1; $stmt->bind_param("ssssi",$name,$email,$google_id,$role,$is_verified); $stmt->execute(); $uid=$stmt->insert_id;
            } else { $uid=$u['id']; }
        }
        $_SESSION['user_id']=$uid; $_SESSION['user_name']=$name; $_SESSION['username']=$name;
        $_SESSION['is_admin']=$u['is_admin']??0; $_SESSION['role']=$u['role']??'buyer';
        header("Location: /buyer_dashboard.php"); exit();
    }catch(Exception $e){ die($e->getMessage()); }
}
