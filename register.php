<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
$error_message = "";
$keep_username = '';
$keep_email = '';
$keep_role = '';
$is_pdo = $conn instanceof PDO;
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $password_confirm = trim($_POST['password_confirm'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $keep_username = $username;
    $keep_email = $email;
    $keep_role = $role;
    if (empty($username) || empty($email) || empty($password) || empty($password_confirm) || empty($role)) {
        $error_message = "Please fill in all fields and select your account type.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address (e.g., name@gmail.com).";
    } elseif (strlen($password) < 6) {
        $error_message = "Password is too short — at least 6 characters needed.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords don't match. Please re-type.";
    } elseif (!in_array($role, ['buyer','farmer'])) {
        $error_message = "Please select a valid account type.";
    } else {
        try {
            if($is_pdo){
                $stmt=$conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
                $stmt->execute([$email]);
                $exists=$stmt->fetch(PDO::FETCH_ASSOC);
                if($exists){
                    $error_message = "An account with this email already exists. Try logging in instead.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $ins=$conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?,?,?,?)");
                        $ok = $ins->execute([$username,$email,$hashed_password,$role]);
                    } catch(Exception $e){
                        $ins=$conn->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
                        $ok = $ins->execute([$username,$email,$hashed_password]);
                    }
                    if($ok){
                        header("Location: login.php?registration=success"); exit();
                    } else {
                        $error_message = "Something went wrong. Please try again.";
                    }
                }
            } else {
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();
                if ($stmt->num_rows > 0) {
                    $error_message = "An account with this email already exists. Try logging in instead.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                    if(!$insert_stmt){
                        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                        $insert_stmt->bind_param("sss", $username, $email, $hashed_password);
                    } else {
                        $insert_stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
                    }
                    if ($insert_stmt->execute()) {
                        header("Location: login.php?registration=success"); exit();
                    } else {
                        $error_message = "Something went wrong. Please try again.";
                    }
                    $insert_stmt->close();
                }
                $stmt->close();
            }
        } catch(Exception $e){
            $error_message = "Error: ".$e->getMessage();
        }
    }
}
include 'header.php';
?>
<div style="padding:2rem 1rem; min-height:70vh; display:flex; align-items:center; justify-content:center; background:#f5f7f4;">
    <div style="background:#fff; max-width:460px; width:100%; border-radius:16px; padding:26px; box-shadow:0 10px 30px rgba(0,0,0,0.08); border:1px solid #eee;">
        <h2 style="margin:0 0 6px 0; font-size:1.6rem; font-weight:900; letter-spacing:-0.5px;">Create Account</h2>
        <p style="margin:0 0 18px 0; color:#666; font-size:0.9rem;">Join Bukid2Bayan - From Bukid to Bayan</p>

        <?php if($error_message): ?>
        <div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:10px 12px; border-radius:10px; font-size:0.85rem; font-weight:700; margin-bottom:14px;"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" style="display:flex; flex-direction:column; gap:12px;">
            <div>
                <label style="font-weight:800; font-size:0.85rem; display:block; margin-bottom:6px;">Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($keep_username); ?>" required placeholder="earlfred" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px; box-sizing:border-box;">
            </div>
            <div>
                <label style="font-weight:800; font-size:0.85rem; display:block; margin-bottom:6px;">Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($keep_email); ?>" required placeholder="name@gmail.com" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px; box-sizing:border-box;">
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                <div>
                    <label style="font-weight:800; font-size:0.85rem; display:block; margin-bottom:6px;">Password</label>
                    <input type="password" name="password" required placeholder="Min. 6 chars" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-weight:800; font-size:0.85rem; display:block; margin-bottom:6px;">Confirm</label>
                    <input type="password" name="password_confirm" required placeholder="Re-type" style="width:100%; padding:12px; border:1.8px solid #d1d5db; border-radius:10px; box-sizing:border-box;">
                </div>
            </div>

            <div>
                <label style="font-weight:800; font-size:0.85rem; display:block; margin-bottom:6px;">Account Type</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:4px;">
                    <label style="border:1.8px solid <?php echo $keep_role=='buyer'?'#2a9d8f':'#d1d5db'; ?>; background:<?php echo $keep_role=='buyer'?'#e6f7f4':'#fff'; ?>; border-radius:10px; padding:12px; cursor:pointer; display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem;">
                        <input type="radio" name="role" value="buyer" <?php echo $keep_role=='buyer'?'checked':''; ?> required> Buyer
                    </label>
                    <label style="border:1.8px solid <?php echo $keep_role=='farmer'?'#2a9d8f':'#d1d5db'; ?>; background:<?php echo $keep_role=='farmer'?'#e6f7f4':'#fff'; ?>; border-radius:10px; padding:12px; cursor:pointer; display:flex; align-items:center; gap:8px; font-weight:700; font-size:0.9rem;">
                        <input type="radio" name="role" value="farmer" <?php echo $keep_role=='farmer'?'checked':''; ?> required> Farmer
                    </label>
                </div>
            </div>

            <button type="submit" style="width:100%; background:#111; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900; font-size:1rem; cursor:pointer; margin-top:6px;">Sign Up</button>

            <p style="text-align:center; font-size:0.85rem; color:#666; margin:6px 0 0 0;">Already have account? <a href="login.php" style="color:#2a9d8f; font-weight:800; text-decoration:none;">Login</a></p>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>
