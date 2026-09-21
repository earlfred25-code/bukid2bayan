<?php
session_start();
$config = include __DIR__.'/config.php';
$f = $config['facebook'];

if(!isset($_GET['code'])){
    $params = http_build_query([
        'client_id' => $f['app_id'],
        'redirect_uri' => $f['redirect_uri'],
        'scope' => 'email,public_profile',
        'response_type' => 'code'
    ]);
    header("Location: https://www.facebook.com/v18.0/dialog/oauth?$params"); exit();
} else {
    $code=$_GET['code'];
    $tokenUrl = "https://graph.facebook.com/v18.0/oauth/access_token?client_id={$f['app_id']}&redirect_uri=".urlencode($f['redirect_uri'])."&client_secret={$f['app_secret']}&code=$code";
    $res = json_decode(file_get_contents($tokenUrl), true);
    if(!isset($res['access_token'])){ die("Facebook login failed"); }
    
    $me = json_decode(file_get_contents("https://graph.facebook.com/me?fields=id,name,email&access_token=".$res['access_token']), true);
    
    include __DIR__.'/../db_connect.php';
    $is_pdo=$conn instanceof PDO;
    $email=$me['email']??$me['id']."@facebook.com"; $name=$me['name']; $fb_id=$me['id'];
    
    try{
        if($is_pdo){
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]); $u=$stmt->fetch();
            if(!$u){ $stmt=$conn->prepare("INSERT INTO users (username,email,facebook_id,role,is_verified) VALUES (?,?,?,?,?)"); $stmt->execute([$name,$email,$fb_id,'buyer',true]); $uid=$conn->lastInsertId(); }
            else $uid=$u['id'];
        } else {
            $stmt=$conn->prepare("SELECT id, role, is_admin FROM users WHERE email=? LIMIT 1"); $stmt->bind_param("s",$email); $stmt->execute(); $u=$stmt->get_result()->fetch_assoc();
            if(!$u){ $stmt=$conn->prepare("INSERT INTO users (username,email,facebook_id,role,is_verified) VALUES (?,?,?,?,?)"); $role='buyer'; $is_verified=1; $stmt->bind_param("ssssi",$name,$email,$fb_id,$role,$is_verified); $stmt->execute(); $uid=$stmt->insert_id; }
            else $uid=$u['id'];
        }
        $_SESSION['user_id']=$uid; $_SESSION['user_name']=$name; $_SESSION['username']=$name; $_SESSION['role']=$u['role']??'buyer'; $_SESSION['is_admin']=$u['is_admin']??0;
        header("Location: /buyer_dashboard.php"); exit();
    }catch(Exception $e){ die($e->getMessage()); }
}
