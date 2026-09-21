<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$error_message = "";
$is_pdo = $conn instanceof PDO;

function isDisposableDomain($domain){
    $disposable = ['tempmail.com','temp-mail.org','10minutemail.com','guerrillamail.com','mailinator.com','yopmail.com','getnada.com','tempmail.net','fakeinbox.com'];
    return in_array(strtolower($domain), $disposable);
}
function isLegitGmail($email){
    $email = strtolower(trim($email));
    $parts = explode('@', $email);
    if(count($parts)!=2) return "Invalid email format.";
    $user = $parts[0]; $domain = $parts[1];
    if(isDisposableDomain($domain)) return "Disposable / fake email not allowed.";
    if(function_exists('checkdnsrr') &&!checkdnsrr($domain, 'MX')){
        return "Email domain doesn't exist.";
    }
    if($domain=='gmail.com' || $domain=='googlemail.com'){
        if(strlen($user) < 6) return "Gmail too short - use real account.";
        if(preg_match('/^(test|asdf|qwerty|abc|123|fake|temp|admin)/', $user)){
            return "Please use your real Gmail.";
        }
    }
    return true;
}

function sendOTPEmail($to_email, $to_name, $otp){
    $config = include 'email_config.php';
    // Try PHPMailer kung meron
    if(file_exists(__DIR__.'/vendor/autoload.php')){
        require __DIR__.'/vendor/autoload.php';
        try{
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $config['username'];
            $mail->Password = $config['password'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $config['port'];
            $mail->setFrom($config['from_email'], $config['from_name']);
            $mail->addAddress($to_email, $to_name);
            $mail->isHTML(true);
            $mail->Subject = 'Bukid2Bayan - Verification Code: '.$otp;
            $mail->Body = "<div style='font-family:Arial;padding:20px'><h2 style='color:#2a9d8f'>Bukid2Bayan</h2><p>Hi $to_name,</p><p>Your verification code is:</p><h1 style='letter-spacing:5px;background:#f5f7f4;padding:12px;border-radius:10px;text-align:center'>$otp</h1><p>Valid for 10 minutes.</p></div>";
            $mail->send();
            return true;
        }catch(Exception $e){ error_log($e->getMessage()); return false; }
    } else {
        // Fallback - mail() (gagana sa ibang hosting, sa Vercel hindi palagi)
        $subject = "Bukid2Bayan Code: $otp";
        $message = "Your Bukid2Bayan verification code is: $otp (valid 10 mins)";
        $headers = "From: ".$config['from_email'];
        @mail($to_email, $subject, $message, $headers);
        return true; // kahit hindi na-send, ipakita natin sa screen for testing
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']?? '');
    $email = trim($_POST['email']?? '');
    $password = trim($_POST['password']?? '');
    $password_confirm = trim($_POST['password_confirm']?? '');
    $role = trim($_POST['role']?? '');
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm) || empty($role)) {
        $error_message = "Please fill in all fields.";
    } else {
        $check = isLegitGmail($email);
        if($check!==true){
            $error_message = $check;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Please enter a valid email.";
        } elseif (strlen($password) < 6) {
            $error_message = "Password too short - min 6 chars.";
        } elseif ($password!== $password_confirm) {
            $error_message = "Passwords don't match.";
        } elseif (!in_array($role, ['buyer','farmer'])) {
            $error_message = "Invalid account type.";
        } else {
            try {
                if($is_pdo){
                    $stmt=$conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                    $stmt->execute([$email]);
                    if($stmt->fetch()){
                        $error_message = "Email already exists. Login instead.";
                    } else {
                        $hashed = password_hash($password, PASSWORD_DEFAULT);
                        $otp = rand(100000,999999);
                        $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                        $ins=$conn->prepare("INSERT INTO users (username, email, password, role, is_verified, verification_code, verification_expires) VALUES (?,?,?,?,?,?,?)");
                        $ok = $ins->execute([$username,$email,$hashed,$role,false,$otp,$expires]);
                        if($ok){
                            sendOTPEmail($email,$username,$otp);
                            header("Location: verify.php?email=".urlencode($email)); exit();
                        }
                    }
                } else {
                    $stmt=$conn->prepare("SELECT id FROM users WHERE email=?");
                    $stmt->bind_param("s",$email); $stmt->execute(); $stmt->store_result();
                    if($stmt->num_rows>0){
                        $error_message="Email already exists.";
                    } else {
                        $hashed=password_hash($password,PASSWORD_DEFAULT);
                        $otp=rand(100000,999999);
                        $expires=date('Y-m-d H:i:s',strtotime('+10 minutes'));
                        $ins=$conn->prepare("INSERT INTO users (username,email,password,role,is_verified,verification_code,verification_expires) VALUES (?,?,?,?,?,?,?)");
                        $is_verified=0; $ins->bind_param("ssssiss",$username,$email,$hashed,$role,$is_verified,$otp,$expires);
                        if($ins->execute()){
                            sendOTPEmail($email,$username,$otp);
                            header("Location: verify.php?email=".urlencode($email)); exit();
                        }
                    }
                }
            }catch(Exception $e){ $error_message="Error: ".$e->getMessage(); }
        }
    }
}
include 'header.php';
?>
<div style="padding:2rem 1rem; min-height:70vh; display:flex; align-items:center; justify-content:center; background:#f5f7f4;">
    <div style="background:#fff; max-width:460px; width:100%; border-radius:16px; padding:26px; box-shadow:0 10px 30px rgba(0,0,0,0.08); border:1px solid #eee;">
        <h2 style="margin:0 0 6px 0; font-size:1.6rem; font-weight:900;">Create Account</h2>
        <p style="margin:0 0 18px 0; color:#666; font-size:0.9rem;">We'll send a verification code to your Gmail</p>
        <?php if($error_message):?><div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:10px 12px;border-radius:10px;font-size:0.85rem;font-weight:700;margin-bottom:14px;"><?php echo htmlspecialchars($error_message);?></div><?php endif;?>
        <form method="POST" autocomplete="off" style="display:flex; flex-direction:column; gap:12px;">
            <input type="text" name="username" value="" required placeholder="Username" autocomplete="off" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px;">
            <input type="email" name="email" value="" required placeholder="Real Gmail (e.g. name@gmail.com)" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <input type="password" name="password" required placeholder="Password" style="padding:12px; border:1.8px solid #d1d5db; border-radius:10px;">
                <input type="password" name="password_confirm" required placeholder="Confirm" style="padding:12px; border:1.8px solid #d1d5db; border-radius:10px;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <label style="border:1.8px solid #d1d5db; border-radius:10px; padding:12px; cursor:pointer; font-weight:700; font-size:0.9rem;"><input type="radio" name="role" value="buyer" required> Buyer</label>
                <label style="border:1.8px solid #d1d5db; border-radius:10px; padding:12px; cursor:pointer; font-weight:700; font-size:0.9rem;"><input type="radio" name="role" value="farmer" required> Farmer</label>
            </div>
            <button type="submit" style="width:100%; background:#111; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900; cursor:pointer;">Sign Up & Send Code</button>
        </form>
    </div>
</div>
<?php include 'footer.php';?>
