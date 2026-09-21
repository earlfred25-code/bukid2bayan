<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$is_pdo = $conn instanceof PDO;
$error_message = ""; $success_message = ""; $keep_email_for_verify = "";

function sendOTPEmail($to_email, $to_name, $otp){
    if(!file_exists(__DIR__.'/email_config.php')) return false;
    $config = include __DIR__.'/email_config.php';
    if(file_exists(__DIR__.'/vendor/autoload.php')){
        require __DIR__.'/vendor/autoload.php';
        try{
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP(); $mail->Host=$config['host']; $mail->SMTPAuth=true;
            $mail->Username=$config['username']; $mail->Password=$config['password'];
            $mail->SMTPSecure='tls'; $mail->Port=$config['port'];
            $mail->setFrom($config['from_email'],$config['from_name']);
            $mail->addAddress($to_email,$to_name); $mail->isHTML(true);
            $mail->Subject='Bukid2Bayan - Verification Code: '.$otp;
            $mail->Body="<div style='font-family:Arial;padding:20px'><h2 style='color:#2a9d8f'>Bukid2Bayan</h2><h1 style='letter-spacing:6px;background:#f5f7f4;padding:12px;border-radius:10px;text-align:center'>$otp</h1><p>Valid for 10 minutes.</p></div>";
            $mail->send(); return true;
        }catch(Exception $e){ return false; }
    } return false;
}

if($_SERVER["REQUEST_METHOD"]=="POST"){
    $action=$_POST['action']??'signup';
    if($action=='signup'){
        $username=trim($_POST['username']??''); $email=trim($_POST['email']??''); $password=trim($_POST['password']??''); $password_confirm=trim($_POST['password_confirm']??''); $role=trim($_POST['role']??'');
        if(empty($username)||empty($email)||empty($password)||empty($password_confirm)||empty($role)){ $error_message="Please fill in all fields."; }
        elseif(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $error_message="Invalid email format."; }
        elseif(strlen($password)<6){ $error_message="Password too short."; }
        elseif($password!==$password_confirm){ $error_message="Passwords don't match."; }
        else {
            try{
                if($is_pdo){
                    $stmt=$conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]);
                    if($stmt->fetch()){ $error_message="Email already exists."; }
                    else{ $hashed=password_hash($password,PASSWORD_DEFAULT); $ins=$conn->prepare("INSERT INTO users (username,email,password,role,is_verified) VALUES (?,?,?,?,?)"); $ins->execute([$username,$email,$hashed,$role,false]); $success_message="Account created! Please verify your email below."; $keep_email_for_verify=$email; }
                } else {
                    $stmt=$conn->prepare("SELECT id FROM users WHERE email=?"); $stmt->bind_param("s",$email); $stmt->execute(); $stmt->store_result();
                    if($stmt->num_rows>0){ $error_message="Email already exists."; }
                    else{ $hashed=password_hash($password,PASSWORD_DEFAULT); $is_verified=0; $ins=$conn->prepare("INSERT INTO users (username,email,password,role,is_verified) VALUES (?,?,?,?,?)"); $ins->bind_param("ssssi",$username,$email,$hashed,$role,$is_verified); if($ins->execute()){ $success_message="Account created! Please verify your email below."; $keep_email_for_verify=$email; } }
                }
            }catch(Exception $e){ $error_message=$e->getMessage(); }
        }
    }
    if($action=='send_code'){
        $email=trim($_POST['verify_email']??''); $keep_email_for_verify=$email;
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){ $error_message="Please enter a valid email."; }
        else{
            $otp=rand(100000,999999); $expires=date('Y-m-d H:i:s',strtotime('+10 minutes'));
            try{
                if($is_pdo){
                    $stmt=$conn->prepare("SELECT id,username FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $u=$stmt->fetch(PDO::FETCH_ASSOC);
                    if(!$u){ $error_message="No account found. Please sign up first."; }
                    else{ $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")->execute([$otp,$expires,$u['id']]); sendOTPEmail($email,$u['username'],$otp); $success_message="Code sent to $email. Code: $otp (for testing)"; }
                } else {
                    $stmt=$conn->prepare("SELECT id,username FROM users WHERE email=? LIMIT 1"); $stmt->bind_param("s",$email); $stmt->execute(); $res=$stmt->get_result(); $u=$res->fetch_assoc();
                    if(!$u){ $error_message="No account found."; }
                    else{ $stmt2=$conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?"); $stmt2->bind_param("ssi",$otp,$expires,$u['id']); $stmt2->execute(); sendOTPEmail($email,$u['username'],$otp); $success_message="Code sent to $email. Code: $otp"; }
                }
            }catch(Exception $e){ $error_message=$e->getMessage(); }
        }
    }
    if($action=='verify_code'){
        $email=trim($_POST['verify_email2']??''); $code=trim($_POST['code']??''); $keep_email_for_verify=$email;
        try{
            if($is_pdo){
                $stmt=$conn->prepare("SELECT id, verification_code, verification_expires FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $u=$stmt->fetch(PDO::FETCH_ASSOC);
                if(!$u){ $error_message="User not found."; } elseif($u['verification_code']!=$code){ $error_message="Incorrect code."; } elseif(strtotime($u['verification_expires'])<time()){ $error_message="Code expired. Send new code."; }
                else{ $conn->prepare("UPDATE users SET is_verified=true, verification_code=NULL WHERE id=?")->execute([$u['id']]); $success_message="Email verified! You can now login."; }
            } else {
                $stmt=$conn->prepare("SELECT id, verification_code, verification_expires FROM users WHERE email=? LIMIT 1"); $stmt->bind_param("s",$email); $stmt->execute(); $res=$stmt->get_result(); $u=$res->fetch_assoc();
                if(!$u){ $error_message="User not found."; } elseif($u['verification_code']!=$code){ $error_message="Incorrect code."; } elseif(strtotime($u['verification_expires'])<time()){ $error_message="Code expired."; }
                else{ $stmt2=$conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE id=?"); $stmt2->bind_param("i",$u['id']); $stmt2->execute(); $success_message="Email verified! You can now login."; }
            }
        }catch(Exception $e){ $error_message=$e->getMessage(); }
    }
}
include 'header.php';
?>
<div style="padding:2rem 1rem; min-height:80vh; background:#f5f7f4; display:flex; flex-direction:column; align-items:center; gap:18px;">

    <?php if($error_message):?><div style="background:#fef2f2;border:1.5px solid #fecaca;color:#991b1b;padding:12px 14px;border-radius:12px;max-width:460px;width:100%;font-weight:700;font-size:0.9rem;"><?php echo htmlspecialchars($error_message);?></div><?php endif;?>
    <?php if($success_message):?><div style="background:#f0fdf4;border:1.5px solid #bbf7d0;color:#166534;padding:12px 14px;border-radius:12px;max-width:460px;width:100%;font-weight:700;font-size:0.9rem;"><?php echo htmlspecialchars($success_message);?></div><?php endif;?>

    <!-- CREATE ACCOUNT -->
    <div style="background:#fff; max-width:460px; width:100%; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.08);">
        <h2 style="margin:0 0 14px 0; font-weight:900; font-size:1.4rem;">Create Account</h2>
        <form method="POST" autocomplete="off" style="display:flex; flex-direction:column; gap:12px;">
            <input type="hidden" name="action" value="signup">
            <input type="text" name="username" required placeholder="Username" style="padding:12px 14px; border:1.8px solid #d1d5db; border-radius:12px; width:100%; box-sizing:border-box;">
            <input type="email" name="email" required placeholder="Email Address" style="padding:12px 14px; border:1.8px solid #d1d5db; border-radius:12px; width:100%; box-sizing:border-box;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <input type="password" name="password" required placeholder="Password" style="padding:12px 14px; border:1.8px solid #d1d5db; border-radius:12px;">
                <input type="password" name="password_confirm" required placeholder="Confirm Password" style="padding:12px 14px; border:1.8px solid #d1d5db; border-radius:12px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <label style="border:1.8px solid #d1d5db; border-radius:12px; padding:12px; font-weight:700; font-size:0.9rem; cursor:pointer;"><input type="radio" name="role" value="buyer" required> Buyer</label>
                <label style="border:1.8px solid #d1d5db; border-radius:12px; padding:12px; font-weight:700; font-size:0.9rem; cursor:pointer;"><input type="radio" name="role" value="farmer" required> Farmer</label>
            </div>
            <button type="submit" style="width:100%; background:#111; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900; cursor:pointer;">Sign Up</button>
        </form>
    </div>

    <!-- EMAIL VERIFICATION - MALINIS NA -->
    <div style="background:#fff; max-width:460px; width:100%; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.08); border:1.5px solid #e5e7eb;">
        <h3 style="margin:0 0 2px 0; font-weight:900; color:#111; font-size:1.1rem;">Email Verification</h3>
        <p style="margin:0 0 16px 0; color:#6b7280; font-size:0.85rem;">Verify your email to activate your account</p>
        
        <form method="POST" style="display:flex; gap:8px; margin-bottom:14px;">
            <input type="hidden" name="action" value="send_code">
            <input type="email" name="verify_email" value="<?php echo htmlspecialchars($keep_email_for_verify);?>" required placeholder="Email Address" style="flex:1; padding:12px 14px; border:1.8px solid #d1d5db; border-radius:12px;">
            <button type="submit" style="background:#2a9d8f; color:#fff; border:none; padding:12px 18px; border-radius:12px; font-weight:800; cursor:pointer; white-space:nowrap;">Send Code</button>
        </form>

        <form method="POST" style="display:flex; gap:8px;">
            <input type="hidden" name="action" value="verify_code">
            <input type="hidden" name="verify_email2" value="<?php echo htmlspecialchars($keep_email_for_verify);?>">
            <input type="text" name="code" required placeholder="6-digit code" maxlength="6" style="flex:1; padding:12px 14px; border:1.8px solid #111; border-radius:12px; font-weight:900; letter-spacing:4px; text-align:center;">
            <button type="submit" style="background:#111; color:#fff; border:none; padding:12px 22px; border-radius:12px; font-weight:800; cursor:pointer;">Verify</button>
        </form>
    </div>
</div>
<?php include 'footer.php';?>
