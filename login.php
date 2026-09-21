<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (isset($_SESSION['user_id'])) {
    $is_admin = $_SESSION['is_admin'] ?? 0;
    $role = $_SESSION['role'] ?? '';
    if ($is_admin == 1) { header("Location: admin/index.php"); }
    elseif ($role === 'farmer') { header("Location: farmer_dashboard.php"); }
    else { header("Location: buyer_dashboard.php"); }
    exit();
}
include 'db_connect.php';
$error_message = "";
$is_pdo = $conn instanceof PDO;

// function para mag-send ng bagong OTP pag mag-login yung hindi pa verified
function resendOTPForLogin($conn, $user, $is_pdo){
    $otp = rand(100000,999999);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    try{
        if($is_pdo){
            $conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?")->execute([$otp,$expires,$user['id']]);
        } else {
            $stmt=$conn->prepare("UPDATE users SET verification_code=?, verification_expires=? WHERE id=?");
            $stmt->bind_param("ssi",$otp,$expires,$user['id']); $stmt->execute();
        }
        // send email kung may config ka na
        if(file_exists(__DIR__.'/email_config.php')){
            $config = include __DIR__.'/email_config.php';
            if(file_exists(__DIR__.'/vendor/autoload.php')){
                require __DIR__.'/vendor/autoload.php';
                try{
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP(); $mail->Host=$config['host']; $mail->SMTPAuth=true;
                    $mail->Username=$config['username']; $mail->Password=$config['password'];
                    $mail->SMTPSecure='tls'; $mail->Port=$config['port'];
                    $mail->setFrom($config['from_email'],$config['from_name']);
                    $mail->addAddress($user['email'],$user['username']);
                    $mail->isHTML(true);
                    $mail->Subject='Bukid2Bayan - Your Login Code: '.$otp;
                    $mail->Body="<div style='font-family:Arial;padding:20px'><h2 style='color:#2a9d8f'>Bukid2Bayan</h2><p>Your new verification code is:</p><h1 style='letter-spacing:5px;background:#f5f7f4;padding:12px;border-radius:10px;text-align:center'>$otp</h1><p>Valid for 10 mins.</p></div>";
                    $mail->send();
                }catch(Exception $e){}
            }
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
                    $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role, is_verified, verification_code, verification_expires FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                } catch(Exception $e){
                    // fallback pag wala pa yung bagong columns
                    $stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE email = ? LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    $user['role'] = 'buyer'; $user['is_verified']=true;
                }
                if($user && password_verify($password, $user['password'])){
                    // CHECK VERIFICATION
                    $is_verified = $user['is_verified'] ?? true; // kung wala pa column, considered verified
                    if($is_verified==false || $is_verified==0 || $is_verified=='0'){
                        resendOTPForLogin($conn,$user,true);
                        header("Location: verify.php?email=".urlencode($user['email'])."&reason=not_verified");
                        exit();
                    }

                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                    $_SESSION['role'] = $user['role'] ?? 'buyer';
                    if (($_SESSION['is_admin'] ?? 0) == 1) { header("Location: admin/index.php"); }
                    elseif (($_SESSION['role'] ?? '') === 'farmer') { header("Location: farmer_dashboard.php"); }
                    else { header("Location: buyer_dashboard.php"); }
                    exit();
                } else {
                    $error_message = $user ? "Incorrect email or password." : "No account found with that email.";
                }
            } else {
                $stmt = $conn->prepare("SELECT id, username, password, email, is_admin, role, is_verified, verification_code, verification_expires FROM users WHERE email = ?");
                if (!$stmt) { $stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE email = ?"); }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $is_verified = $user['is_verified'] ?? true;
                        if($is_verified==0){
                            resendOTPForLogin($conn,$user,false);
                            header("Location: verify.php?email=".urlencode($user['email'])."&reason=not_verified");
                            exit();
                        }
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'] ?? 0;
                        $_SESSION['role'] = $user['role'] ?? 'buyer';
                        if (($_SESSION['is_admin'] ?? 0) == 1) { header("Location: admin/index.php"); }
                        elseif (($_SESSION['role'] ?? '') === 'farmer') { header("Location: farmer_dashboard.php"); }
                        else { header("Location: buyer_dashboard.php"); }
                        exit();
                    } else { $error_message = "Incorrect email or password."; }
                } else { $error_message = "No account found with that email."; }
                $stmt->close();
            }
        } catch(Exception $e){ $error_message = "Login error: ".$e->getMessage(); }
    }
}
include 'header.php';
?>
<style>
.login-page{
    min-height: 85vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px 16px;
    background:
        radial-gradient(600px 300px at 10% 10%, rgba(42,157,143,0.18), transparent),
        radial-gradient(800px 400px at 90% 90%, rgba(34,197,94,0.15), transparent),
        linear-gradient(180deg, #f7faf6 0%, #eef6f0 100%);
}
.form-box{
    background: rgba(255,255,255,0.88);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(0,0,0,0.06);
    max-width: 440px;
    width: 100%;
    border-radius: 20px;
    padding: 28px 24px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.12), 0 2px 10px rgba(0,0,0,0.06);
    animation: pop 0.4s ease;
}
@keyframes pop{ from{ transform: translateY(10px) scale(0.98); opacity:0; } to{ transform: translateY(0) scale(1); opacity:1; } }
.badge{
    display:inline-flex; align-items:center; gap:6px;
    background:#111; color:#fff; padding:6px 12px; border-radius:100px;
    font-size:0.7rem; font-weight:900; letter-spacing:0.5px; text-transform:uppercase;
}
.input-group{ position:relative; margin-bottom:18px; }
.input-group label{ font-size:0.85rem; font-weight:800; color:#1a2e35; margin-bottom:6px; display:flex; align-items:center; gap:6px; }
.input-group input{
    width:100%; padding:14px 44px 14px 14px; font-size:1rem;
    border:1.8px solid #d1d5db; border-radius:12px; outline:none;
    transition: all 0.2s; background:#fff;
}
.input-group input:focus{ border-color:#2a9d8f; box-shadow:0 0 0 4px rgba(42,157,143,0.15); }
.eye-btn{
    position:absolute; right:10px; top:50%; transform:translateY(-10%);
    background:#f3f4f6; border:none; width:36px; height:36px; border-radius:10px;
    cursor:pointer; color:#555; display:flex; align-items:center; justify-content:center;
}
.submit-btn{
    width:100%; padding:14px; border-radius:12px; border:none; background:#111; color:#fff;
    font-weight:900; font-size:1.05rem; cursor:pointer; transition: all 0.2s;
    display:flex; align-items:center; justify-content:center; gap:8px;
}
.submit-btn:hover{ background:#000; transform:translateY(-1px); box-shadow:0 8px 20px rgba(0,0,0,0.2); }
.divider{ height:1px; background:linear-gradient(to right, transparent, #e5e7eb, transparent); margin:20px 0; }
</style>

<div class="login-page">
    <div class="form-box">
        <div style="text-align:center; margin-bottom:22px;">
            <div class="badge"><i class="fas fa-leaf"></i> Bukid2Bayan</div>
            <div style="margin-top:14px; width:64px; height:64px; background:linear-gradient(135deg,#2a9d8f,#22c55e); border-radius:18px; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:1.8rem; box-shadow:0 10px 20px rgba(42,157,143,0.3);">
                <i class="fas fa-user"></i>
            </div>
            <h2 style="font-size:1.9rem; margin:12px 0 4px 0; font-weight:900; letter-spacing:-0.5px; color:#111;">Welcome Back</h2>
            <p style="font-size:0.9rem; color:#6b7280; margin:0;">Fresh gulay at bigas, diretso sa bayan</p>
        </div>

        <?php if(!empty($error_message)): ?>
            <div style="background:#fef2f2; border:1.5px solid #fecaca; color:#991b1b; padding:12px 14px; border-radius:12px; margin-bottom:18px; font-size:0.9rem; display:flex; gap:10px;">
                <i class="fas fa-exclamation-triangle" style="margin-top:2px;"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['verified'])): ?>
            <div style="background:#f0fdf4; border:1.5px solid #bbf7d0; color:#166534; padding:12px 14px; border-radius:12px; margin-bottom:18px; font-size:0.9rem; font-weight:700; display:flex; gap:10px;">
                <i class="fas fa-check-circle" style="margin-top:2px;"></i>
                <span>Email verified! You can now login.</span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="input-group">
                <label><i class="fas fa-envelope" style="color:#2a9d8f;"></i> Email Address</label>
                <div style="position:relative;">
                    <input type="email" name="email" required placeholder="you@gmail.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
            </div>
            <div class="input-group">
                <label><i class="fas fa-lock" style="color:#2a9d8f;"></i> Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" required placeholder="Enter your password" style="padding-right:50px;">
                    <button type="button" class="eye-btn" onclick="togglePass()"><i class="fas fa-eye" id="eyeIcon"></i></button>
                </div>
            </div>
            <button type="submit" class="submit-btn"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>

        <div class="divider"></div>
        <p style="margin:0; font-size:0.9rem; text-align:center; color:#6b7280;">
            Wala ka pa account? <a href="register.php" style="font-weight:900; color:#2a9d8f; text-decoration:none;">Gumawa ng bago</a>
        </p>
        <p style="text-align:center; font-size:0.75rem; color:#9ca3af; margin:16px 0 0 0;">
            <i class="fas fa-shield-halved"></i> Secure login • Bukid2Bayan
        </p>
    </div>
</div>

<script>
function togglePass(){
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if(input.type === 'password'){ input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
}
</script>
<?php include 'footer.php'; ?>
