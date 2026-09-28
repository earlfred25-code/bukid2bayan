<?php
ob_start();
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
$secure = $is_https;
ini_set('session.save_path', sys_get_temp_dir());
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
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
            $_SESSION['role'] = $u['role'] ?? 'buyer';
            $_SESSION['is_admin'] = $u['is_admin'] ?? 0;
        }
    }catch(Exception $e){}
}

if (isset($_SESSION['user_id'])) {
    $role = strtolower($_SESSION['role'] ?? 'buyer');
    $is_admin = $_SESSION['is_admin'] ?? 0;
    if ($is_admin == 1) { header("Location: admin/index.php"); exit(); }
    elseif (in_array($role, ['farmer','seller'])) { header("Location: farmer_dashboard.php"); exit(); }
    else { header("Location: buyer_dashboard.php"); exit(); }
}

$error_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            if($is_pdo){
                $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role, is_verified FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if($user && password_verify($password, $user['password'])){
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                    $_SESSION['role'] = $user['role'] ?? 'buyer';
                    setcookie('user_id', $user['id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
                    setcookie('user_name', $user['username'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                    setcookie('role', $user['role']??'buyer', ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                    $role = strtolower($user['role'] ?? 'buyer');
                    if (($user['is_admin'] ?? 0) == 1) header("Location: admin/index.php");
                    elseif (in_array($role, ['farmer','seller'])) header("Location: farmer_dashboard.php");
                    else header("Location: buyer_dashboard.php");
                    exit();
                } else {
                    $error_message = "Mali email o password.";
                }
            } else {
                $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role FROM users WHERE email = ? LIMIT 1");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                        $_SESSION['role'] = $user['role'] ?? 'buyer';
                        setcookie('user_id', $user['id'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>true,'samesite'=>'Lax']);
                        setcookie('user_name', $user['username'], ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                        setcookie('role', $user['role']??'buyer', ['expires'=>time()+86400*30,'path'=>'/','secure'=>$secure,'httponly'=>false,'samesite'=>'Lax']);
                        $role = strtolower($user['role'] ?? 'buyer');
                        if (($user['is_admin'] ?? 0) == 1) header("Location: admin/index.php");
                        elseif (in_array($role, ['farmer','seller'])) header("Location: farmer_dashboard.php");
                        else header("Location: buyer_dashboard.php");
                        exit();
                    } else { $error_message = "Mali email o password."; }
                } else { $error_message = "Walang account sa email na yan."; }
                $stmt->close();
            }
        } catch(Exception $e){ $error_message = "Login error: ".$e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Login - Bukid2Bayan</title>
<style>
body{margin:0;font-family:system-ui;background:#f6faf6}
.login-page{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.form-box{background:#fff;max-width:420px;width:100%;border-radius:20px;padding:28px 24px;box-shadow:0 20px 60px rgba(0,0,0,.12)}
.input-group{margin-bottom:16px}
.input-group label{font-weight:800;font-size:.9rem;display:block;margin-bottom:6px}
.input-group input{width:100%;padding:14px;border:1.8px solid #d1d5db;border-radius:12px;box-sizing:border-box}
.submit-btn{width:100%;padding:14px;border-radius:12px;border:none;background:#111;color:#fff;font-weight:900;font-size:1rem;cursor:pointer}
</style></head><body>
<div class="login-page">
<div class="form-box">
<h2 style="text-align:center;margin:0 0 6px 0;">Welcome Back</h2>
<p style="text-align:center;color:#666;margin:0 0 18px 0;">Fresh gulay at bigas, diretso sa bayan</p>
<?php if(!empty($error_message)): ?><div style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:12px;border-radius:12px;margin-bottom:16px;"><?php echo htmlspecialchars($error_message); ?></div><?php endif; ?>
<form method="post">
<div class="input-group"><label>Email</label><input type="email" name="email" required value="<?php echo isset($_POST['email'])?htmlspecialchars($_POST['email']):''; ?>"></div>
<div class="input-group"><label>Password</label><input type="password" name="password" required></div>
<button type="submit" class="submit-btn">Login</button>
</form>
<p style="text-align:center;margin-top:16px;"><a href="register.php" style="color:#2a9d8f;font-weight:800;text-decoration:none;">Gumawa ng account</a></p>
</div>
</div>
</body></html>
