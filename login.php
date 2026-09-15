<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

include 'db_connect.php';
$error_message = "";
$is_pdo = $conn instanceof PDO;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        try {
            if($is_pdo){
                $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if($user){
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                        if ($_SESSION['is_admin'] == 1) {
                            header("Location: admin/index.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        $error_message = "Incorrect email or password. Please try again.";
                    }
                } else {
                    $error_message = "No account found with that email.";
                }
            } else {
                $stmt = $conn->prepare("SELECT id, username, password, is_admin FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows == 1) {
                    $user = $result->fetch_assoc();
                    if (password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['username'];
                        $_SESSION['is_admin'] = $user['is_admin'];
                        if ($_SESSION['is_admin'] == 1) {
                            header("Location: admin/index.php");
                        } else {
                            header("Location: index.php");
                        }
                        exit();
                    } else {
                        $error_message = "Incorrect email or password. Please try again.";
                    }
                } else {
                    $error_message = "No account found with that email.";
                }
                $stmt->close();
            }
        } catch(Exception $e){
            $error_message = "Login error: ".$e->getMessage();
        }
    }
    if(!$is_pdo && isset($conn) && method_exists($conn,'close')){ $conn->close(); }
}
include 'header.php';
?>

<style>
.form-wrapper{ padding:2rem 1rem; min-height:70vh; display:flex; align-items:center; justify-content:center; }
.form-box{ background:rgba(255,255,255,0.92); backdrop-filter:blur(14px); border:1px solid rgba(255,255,255,0.4); max-width:440px; width:100%; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
@media(max-width:480px){
    .form-wrapper{ padding:1rem 12px; }
    .form-box{ padding:20px 16px; border-radius:14px; }
}
</style>

<div class="form-wrapper">
    <div class="form-box">
        <div style="text-align:center; margin-bottom:20px;">
            <i class="fas fa-user-circle" style="font-size:3rem; color:#2a9d8f;"></i>
            <h2 style="font-size:1.9rem; margin-top:10px; color:#1a2e35; font-weight:900;">Welcome Back</h2>
            <p style="font-size:0.95rem; color:#555; margin-top:4px;">Login to continue shopping fresh produce</p>
        </div>

        <?php if(!empty($error_message)): ?>
            <div style="background:#ffeaea; border:1.5px solid #ffb3b3; color:#8a1a1a; padding:12px; border-radius:8px; margin-bottom:18px; font-size:0.95rem; display:flex; gap:8px; align-items:flex-start;">
                <i class="fas fa-exclamation-circle" style="margin-top:2px;"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form action="login.php" method="post" autocomplete="on">
            <div style="margin-bottom:16px;">
                <label for="email" style="font-size:0.92rem; font-weight:700; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-envelope" style="color:#2a9d8f;"></i> Email Address
                </label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="you@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" style="padding:0.85rem 1rem; font-size:1rem; border:1.5px solid #bbb; border-radius:8px; width:100%;">
            </div>
            
            <div style="margin-bottom:20px;">
                <label for="password" style="font-size:0.92rem; font-weight:700; margin-bottom:6px; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-lock" style="color:#2a9d8f;"></i> Password
                </label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="Enter your password" style="padding:0.85rem 1rem; font-size:1rem; border:1.5px solid #bbb; border-radius:8px; width:100%; padding-right:42px;">
                    <button type="button" onclick="togglePass()" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#666; font-size:1.1rem;">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <div style="text-align:right; margin-top:6px;">
                    <a href="#" style="font-size:0.85rem; color:#666; text-decoration:none;">Forgot password?</a>
                </div>
            </div>
            
            <button type="submit" style="width:100%; font-size:1.05rem; padding:0.9rem; min-height:48px; font-weight:800; border-radius:10px; background:#111; color:#fff; border:none; cursor:pointer;">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>
        
        <p style="margin-top:20px; font-size:0.92rem; text-align:center;">
            Don't have an account? <a href="register.php" style="font-weight:800; color:#2a9d8f; text-decoration:none;">Create one</a>
        </p>
        
        <p style="text-align:center; font-size:0.8rem; color:#888; margin-top:16px; border-top:1px solid #eee; padding-top:12px;">
            <i class="fas fa-shield-alt"></i> Secure login
        </p>
    </div>
</div>

<script>
function togglePass() {
    const input = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>

<?php include 'footer.php'; ?>
