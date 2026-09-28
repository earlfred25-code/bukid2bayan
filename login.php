<?php
ob_start();
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$secure = $is_https;
ini_set('session.save_path', sys_get_temp_dir());
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
} else {
    session_set_cookie_params(0, '/', '', $secure, true);
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$is_pdo = $conn instanceof PDO;

if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    try{
        $uid = (int)$_COOKIE['user_id'];
        if($is_pdo){
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->execute([$uid]);
            $u = $st->fetch(PDO::FETCH_ASSOC);
        } else {
            $st = $conn->prepare("SELECT id, username, role, is_admin FROM users WHERE id = ? LIMIT 1");
            $st->bind_param("i", $uid);
            $st->execute();
            $u = $st->get_result()->fetch_assoc();
            $st->close();
        }
        if($u){
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['username'];
            $_SESSION['username'] = $u['username'];
            $_SESSION['role'] = $u['role'] ?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin'] ?? 0;
        }
    }catch(Exception $e){}
}

if (isset($_SESSION['user_id'])) {
    $is_admin = $_SESSION['is_admin'] ?? 0;
    $role = strtolower($_SESSION['role'] ?? 'buyer');
    if ($is_admin == 1) { header("Location: admin/index.php"); exit(); }
    elseif (in_array($role, ['farmer','seller'])) { header("Location: farmer_dashboard.php"); exit(); }
    else { header("Location: buyer_dashboard.php"); exit(); }
}

$error_message = "";
function resendOTPForLogin($conn, $user, $is_pdo){
    $otp = rand(100000,999999);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    try{
        if($is_pdo){
            $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")->execute([$otp,$expires,$user['id']]);
        } else {
            $stmt=$conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?");
            $stmt->bind_param("ssi",$otp,$expires,$user['id']); $stmt->execute(); $stmt->close();
        }
    }catch(Exception $e){}
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            if($is_pdo){
                try {
                    $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role, is_verified FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(Exception $e){
                    $stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if($user){ $user['role'] = 'buyer'; $user['is_verified']=1; }
                }
                if($user && password_verify($password, $user['password'])){
                    if(($user['is_verified'] ?? 1)==0){
                        resendOTPForLogin($conn,$user,true);
                        header("Location: verify.php?email=".urlencode($user['email'])."&reason=not_verified"); exit();
                    }
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                    $_SESSION['role'] = $user['role'] ?? 'buyer';
                    
                    setcookie('user_id', $user['id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
                    setcookie('user_name', $user['username'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                    setcookie('role', $user['role']??'buyer', ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                    setcookie('is_admin', $user['is_admin']??0, ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);

                    $role = strtolower($_SESSION['role'] ?? 'buyer');
                    if (($_SESSION['is_admin'] ?? 0) == 1) { header("Location: admin/index.php"); exit(); }
                    elseif (in_array($role, ['farmer','seller'])) { header("Location: farmer_dashboard.php"); exit(); }
                    else { header("Location: buyer_dashboard.php"); exit(); }
                } else {
                    $error_message = $user ? "Incorrect email or password." : "No account found with that email.";
                }
            } else {
                $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role, is_verified FROM users WHERE email = ? LIMIT 1");
                if (!$stmt) { $stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE email = ? LIMIT 1"); }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (!isset($user['role'])) $user['role'] = 'buyer';
                    if (password_verify($password, $user['password'])) {
                        if(($user['is_verified'] ?? 1)==0){
                            resendOTPForLogin($conn,$user,false);
                            header("Location: verify.php?email=".urlencode($user['email'])."&reason=not_verified"); exit();
                        }
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                        $_SESSION['role'] = $user['role'] ?? 'buyer';
                        
                        setcookie('user_id', $user['id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
                        setcookie('user_name', $user['username'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                        setcookie('role', $user['role']??'buyer', ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                        setcookie('is_admin', $user['is_admin']??0, ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                        
                        $role = strtolower($_SESSION['role'] ?? 'buyer');
                        if (($_SESSION['is_admin'] ?? 0) == 1) { header("Location: admin/index.php"); exit(); }
                        elseif (in_array($role, ['farmer','seller'])) { header("Location: farmer_dashboard.php"); exit(); }
                        else { header("Location: buyer_dashboard.php"); exit(); }
                    } else { $error_message = "Incorrect email or password."; }
                } else { $error_message = "No account found with that email."; }
                $stmt->close();
            }
        } catch(Exception $e){ $error_message = "Login error: ".$e->getMessage(); }
    }
}
include 'header.php';
?>
<!-- yung HTML mo sa baba same lang, wag mo na palitan -->
