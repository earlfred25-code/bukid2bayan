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
    $name = $userInfo['name']; 
    $google_id = $userInfo['id'];
    $dummy_pass = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    
    try{
        if($is_pdo){
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]); $u=$stmt->fetch(PDO::FETCH_ASSOC);
            if(!$u){
                $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,is_verified) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$name,$email,$dummy_pass,$google_id,'buyer',true]);
                $uid=$conn->lastInsertId(); $role='buyer'; $is_admin=0;
            } else { $uid=$u['id']; $role=$u['role']; $is_admin=$u['is_admin']??0; }
        } else {
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s",$email); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc();
            if(!$u){
                $stmt=$conn->prepare("INSERT INTO users (username,email,password,google_id,role,is_verified) VALUES (?,?,?,?,?,?)");
                $role='buyer'; $is_verified=1; $stmt->bind_param("sssssi",$name,$email,$dummy_pass,$google_id,$role,$is_verified); $stmt->execute(); $uid=$stmt->insert_id;
            } else { $uid=$u['id']; $role=$u['role']; $is_admin=$u['is_admin']??0; }
        }
        $_SESSION['user_id']=$uid; $_SESSION['user_name']=$name; $_SESSION['username']=$name;
        $_SESSION['is_admin']=$is_admin; $_SESSION['role']=$role;
        header("Location: /buyer_dashboard.php"); exit();
    }catch(Exception $e){ die($e->getMessage()); }
}
