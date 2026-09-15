<?php
session_start();
include 'db_connect.php'; 
$error_message = "";
$success_message = "";

// Keep values when error
$keep_username = '';
$keep_email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $password_confirm = trim($_POST['password_confirm']);

    $keep_username = $username;
    $keep_email = $email;

    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error_message = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Please enter a valid email address (e.g., name@gmail.com).";
    } elseif (strlen($password) < 6) {
        $error_message = "Password is too short — at least 6 characters needed.";
    } elseif ($password !== $password_confirm) {
        $error_message = "Passwords don't match. Please re-type.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "An account with this email already exists. Try logging in instead.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $username, $email, $hashed_password);

            if ($insert_stmt->execute()) {
                header("Location: login.php?registration=success");
                exit();
            } else {
                $error_message = "Something went wrong. Please try again.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<?php include 'header.php'; ?>

<div class="form-wrapper" style="padding: 3rem 1rem;">
    <div class="form-box" style="background-color: rgba(255,255,255,0.88); backdrop-filter: blur(14px); max-width: 460px; text-align: left;">
        <div style="text-align: center; margin-bottom: 18px;">
            <i class="fas fa-user-plus" style="font-size: 2.8rem; color: var(--primary-color);"></i>
            <h2 style="font-size: 1.8rem; margin-top: 10px;">Create an Account</h2>
            <p style="font-size: 0.92rem; color: #555; margin-top: 4px;">Join us and shop fresh produce direct from farmers</p>
        </div>

        <?php if(!empty($error_message)): ?>
            <div class="message error" style="background: #ffeaea; border: 1.5px solid #ffb3b3; color: #8a1a1a; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.92rem; display: flex; gap: 8px;">
                <i class="fas fa-exclamation-circle" style="margin-top: 2px;"></i>
                <span><?php echo htmlspecialchars($error_message); ?></span>
            </div>
        <?php endif; ?>

        <form action="register.php" method="post" autocomplete="on">
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="username" style="font-size: 0.9rem; font-weight: 700;"><i class="fas fa-user" style="color: var(--primary-color);"></i> Full Name</label>
                <input type="text" id="username" name="username" required autocomplete="name" placeholder="Juan Dela Cruz" value="<?php echo htmlspecialchars($keep_username); ?>" style="padding: 0.8rem 1rem; font-size: 1rem;">
            </div>
            
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="email" style="font-size: 0.9rem; font-weight: 700;"><i class="fas fa-envelope" style="color: var(--primary-color);"></i> Email Address</label>
                <input type="email" id="email" name="email" required autocomplete="email" placeholder="you@gmail.com" value="<?php echo htmlspecialchars($keep_email); ?>" style="padding: 0.8rem 1rem; font-size: 1rem;">
            </div>
            
            <div class="form-group" style="margin-bottom: 14px;">
                <label for="password" style="font-size: 0.9rem; font-weight: 700;"><i class="fas fa-lock" style="color: var(--primary-color);"></i> Password <span style="font-weight: 400; color: #777;">(at least 6 characters)</span></label>
                <div style="position: relative;">
                    <input type="password" id="password" name="password" required autocomplete="new-password" placeholder="Create a password" style="padding: 0.8rem 1rem; padding-right: 42px; font-size: 1rem; width: 100%;">
                    <button type="button" onclick="togglePass('password','eye1')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;"><i class="fas fa-eye" id="eye1"></i></button>
                </div>
                <small style="font-size: 0.8rem; color: #666; display: block; margin-top: 4px;" id="passHint">Use 6+ characters with letters & numbers</small>
            </div>
            
            <div class="form-group" style="margin-bottom: 20px;">
                <label for="password_confirm" style="font-size: 0.9rem; font-weight: 700;"><i class="fas fa-check-double" style="color: var(--primary-color);"></i> Confirm Password</label>
                <div style="position: relative;">
                    <input type="password" id="password_confirm" name="password_confirm" required autocomplete="new-password" placeholder="Re-type your password" style="padding: 0.8rem 1rem; padding-right: 42px; font-size: 1rem; width: 100%;">
                    <button type="button" onclick="togglePass('password_confirm','eye2')" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666;"><i class="fas fa-eye" id="eye2"></i></button>
                </div>
                <small id="matchHint" style="font-size: 0.8rem; color: #666; display: block; margin-top: 4px;"></small>
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%; font-size: 1.05rem; padding: 0.9rem; min-height: 48px; font-weight: 700; border-radius: 8px;">
                <i class="fas fa-user-plus"></i> Create Account
            </button>
        </form>
        
        <p class="form-link" style="margin-top: 18px; font-size: 0.9rem; text-align: center;">
            Already have an account? <a href="login.php" style="font-weight: 700; color: var(--primary-color);">Login here</a>
        </p>
    </div>
</div>

<script>
function togglePass(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(eyeId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
    }
}

// Live check for 35-69 - para alam agad kung match
document.getElementById('password_confirm').addEventListener('input', function() {
    const p1 = document.getElementById('password').value;
    const p2 = this.value;
    const hint = document.getElementById('matchHint');
    if (p2.length === 0) { hint.textContent = ''; return; }
    if (p1 === p2) {
        hint.textContent = '✓ Passwords match';
        hint.style.color = '#2a9d8f';
    } else {
        hint.textContent = '✗ Passwords do not match';
        hint.style.color = '#e76f51';
    }
});
</script>

<?php include 'footer.php'; ?>