<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$is_pdo = $conn instanceof PDO;
$error_message = "";
$success_message = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $username = trim($_POST['username']??'');
    $phone = trim($_POST['phone_number']??'');
    $email = trim($_POST['email']??'');
    $password = trim($_POST['password']??'');
    $password_confirm = trim($_POST['password_confirm']??'');
    $role = trim($_POST['role']??'');

    if(empty($username)||empty($phone)||empty($email)||empty($password)||empty($password_confirm)||empty($role)){
        $error_message="Please fill in all fields.";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $error_message="Invalid email format.";
    } elseif(!preg_match('/^[0-9+ ]{10,15}$/', $phone)){
        $error_message="Invalid phone number.";
    } elseif(strlen($password)<6){
        $error_message="Password too short - at least 6 characters.";
    } elseif($password!==$password_confirm){
        $error_message="Passwords don't match.";
    } else {
        try{
            if($is_pdo){
                $stmt=$conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                $stmt->execute([$email]);
                if($stmt->fetch()){ $error_message="Email already exists."; }
                else{
                    $hashed=password_hash($password, PASSWORD_DEFAULT);
                    try{
                        $ins=$conn->prepare("INSERT INTO users (username, phone_number, email, password, role, is_verified) VALUES (?,?,?,?,?,?)");
                        $ins->execute([$username,$phone,$email,$hashed,$role,true]);
                    }catch(Exception $e){
                        // fallback kung wala pa phone_number column
                        $ins=$conn->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?,?,?,?,?)");
                        $ins->execute([$username,$email,$hashed,$role,true]);
                    }
                    header("Location: login.php?registered=1"); exit();
                }
            } else {
                $stmt=$conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                $stmt->bind_param("s",$email); $stmt->execute(); $stmt->store_result();
                if($stmt->num_rows>0){ $error_message="Email already exists."; }
                else{
                    $hashed=password_hash($password, PASSWORD_DEFAULT); $is_verified=1;
                    try{
                        $ins=$conn->prepare("INSERT INTO users (username, phone_number, email, password, role, is_verified) VALUES (?,?,?,?,?,?)");
                        $ins->bind_param("sssssi",$username,$phone,$email,$hashed,$role,$is_verified);
                    }catch(Exception $e){
                        $ins=$conn->prepare("INSERT INTO users (username, email, password, role, is_verified) VALUES (?,?,?,?,?)");
                        $ins->bind_param("ssssi",$username,$email,$hashed,$role,$is_verified);
                    }
                    if($ins->execute()){ header("Location: login.php?registered=1"); exit(); }
                }
            }
        }catch(Exception $e){ $error_message="Error: ".$e->getMessage(); }
    }
}
include 'header.php';
?>
<div style="padding:2rem 1rem; min-height:85vh; background:#f5f7f4; display:flex; flex-direction:column; align-items:center; gap:16px;">

    <?php if($error_message):?>
    <div style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:12px;max-width:460px;width:100%;font-weight:700;font-size:0.9rem;"><?php echo htmlspecialchars($error_message);?></div>
    <?php endif;?>

    <div style="background:#fff; max-width:460px; width:100%; border-radius:20px; padding:26px; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
        <h2 style="margin:0 0 4px 0; font-weight:900; font-size:1.4rem; text-align:center;">Create Account</h2>
        <p style="margin:0 0 18px 0; color:#6b7280; font-size:0.85rem; text-align:center;">Sign in for your best experience</p>

        <!-- FACEBOOK AT GMAIL LANG - GAYA NUNG SCREENSHOT MO -->
        <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:18px;">
            <a href="auth/google.php" style="display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:12px; background:#fff; color:#111; text-decoration:none; font-weight:700; font-size:0.9rem;">
                <img src="https://www.svgrepo.com/show/475656/google-color.svg" style="width:18px; height:18px;"> Continue with Google
            </a>
            <a href="auth/facebook.php" style="display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:12px; border:none; border-radius:12px; background:#1877F2; color:#fff; text-decoration:none; font-weight:800; font-size:0.9rem;">
                <span style="background:#fff; color:#1877F2; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:900; font-size:14px;">f</span> Continue with Facebook
            </a>
        </div>

        <div style="display:flex; align-items:center; gap:12px; margin:18px 0;">
            <div style="height:1px; background:#e5e7eb; flex:1;"></div>
            <span style="color:#9ca3af; font-size:0.8rem;">or continue with your email address</span>
            <div style="height:1px; background:#e5e7eb; flex:1;"></div>
        </div>

        <!-- USERNAME - PHONE - PASSWORD -->
        <form method="POST" autocomplete="off" style="display:flex; flex-direction:column; gap:12px;">
            <input type="text" name="username" required placeholder="Username" style="padding:13px 14px; border:1.8px solid #d1d5db; border-radius:12px; width:100%; box-sizing:border-box;">
            <input type="tel" name="phone_number" required placeholder="Phone Number" style="padding:13px 14px; border:1.8px solid #d1d5db; border-radius:12px; width:100%; box-sizing:border-box;">
            <input type="email" name="email" required placeholder="Email Address" style="padding:13px 14px; border:1.8px solid #d1d5db; border-radius:12px; width:100%; box-sizing:border-box;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <input type="password" name="password" required placeholder="Password" style="padding:13px 14px; border:1.8px solid #d1d5db; border-radius:12px;">
                <input type="password" name="password_confirm" required placeholder="Confirm Password" style="padding:13px 14px; border:1.8px solid #d1d5db; border-radius:12px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <label style="border:1.8px solid #d1d5db; border-radius:12px; padding:12px; font-weight:700; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; gap:8px;"><input type="radio" name="role" value="buyer" required> Buyer</label>
                <label style="border:1.8px solid #d1d5db; border-radius:12px; padding:12px; font-weight:700; font-size:0.9rem; cursor:pointer; display:flex; align-items:center; gap:8px;"><input type="radio" name="role" value="farmer" required> Farmer</label>
            </div>
            <button type="submit" style="width:100%; background:#111; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900; cursor:pointer; margin-top:4px;">Sign Up</button>
            <p style="text-align:center; font-size:0.85rem; color:#666; margin:6px 0 0 0;">Already have account? <a href="login.php" style="color:#2a9d8f; font-weight:800; text-decoration:none;">Login</a></p>
        </form>
    </div>
</div>
<?php include 'footer.php';?>
