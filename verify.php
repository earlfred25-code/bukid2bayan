<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'db_connect.php';
include 'header.php';
$is_pdo = $conn instanceof PDO;
$email = trim($_GET['email']?? $_POST['email']?? '');
$msg = ""; $success = false;

if($_SERVER['REQUEST_METHOD']=='POST'){
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);
    try{
        if($is_pdo){
            $stmt=$conn->prepare("SELECT id, verification_code, verification_expires FROM users WHERE email=? LIMIT 1");
            $stmt->execute([$email]);
            $u=$stmt->fetch(PDO::FETCH_ASSOC);
            if(!$u){ $msg="User not found."; }
            elseif($u['verification_code']!=$code){ $msg="Wrong code. Check your Gmail."; }
            elseif(strtotime($u['verification_expires']) < time()){ $msg="Code expired. Register again."; }
            else {
                $conn->prepare("UPDATE users SET is_verified=true, verification_code=NULL WHERE id=?")->execute([$u['id']]);
                $success=true;
            }
        } else {
            $stmt=$conn->prepare("SELECT id, verification_code, verification_expires FROM users WHERE email=? LIMIT 1");
            $stmt->bind_param("s",$email); $stmt->execute(); $res=$stmt->get_result(); $u=$res->fetch_assoc();
            if(!$u){ $msg="User not found."; }
            elseif($u['verification_code']!=$code){ $msg="Wrong code."; }
            elseif(strtotime($u['verification_expires']) < time()){ $msg="Code expired."; }
            else {
                $stmt2=$conn->prepare("UPDATE users SET is_verified=1, verification_code=NULL WHERE id=?");
                $stmt2->bind_param("i",$u['id']); $stmt2->execute();
                $success=true;
            }
        }
    }catch(Exception $e){ $msg=$e->getMessage(); }
}
?>
<div style="padding:2rem 1rem; min-height:70vh; display:flex; align-items:center; justify-content:center; background:#f5f7f4;">
    <div style="background:#fff; max-width:400px; width:100%; border-radius:16px; padding:26px; box-shadow:0 10px 30px rgba(0,0,0,0.08); text-align:center;">
        <?php if($success):?>
            <h2 style="color:#16a34a;">Verified! 🎉</h2>
            <p>Your Gmail is legit. You can now login.</p>
            <a href="login.php" style="display:block; background:#111; color:#fff; padding:12px; border-radius:10px; text-decoration:none; font-weight:800; margin-top:12px;">Go to Login</a>
        <?php else:?>
            <h2 style="margin:0 0 6px 0; font-weight:900;">Check your Gmail</h2>
            <p style="color:#666; font-size:0.9rem; margin:0 0 16px 0;">We sent a 6-digit code to <b><?php echo htmlspecialchars($email);?></b></p>
            <?php if($msg):?><div style="background:#fef2f2; border:1px solid #fecaca; color:#991b1b; padding:10px; border-radius:10px; font-size:0.85rem; font-weight:700; margin-bottom:12px;"><?php echo htmlspecialchars($msg);?></div><?php endif;?>
            <form method="POST" style="display:flex; flex-direction:column; gap:12px;">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email);?>">
                <input type="text" name="code" required placeholder="Enter 6-digit code" maxlength="6" style="width:100%; padding:16px; border:2px solid #111; border-radius:12px; font-size:1.4rem; letter-spacing:6px; text-align:center; font-weight:900;">
                <button type="submit" style="width:100%; background:#2a9d8f; color:#fff; padding:14px; border:none; border-radius:12px; font-weight:900; cursor:pointer;">Verify Gmail</button>
            </form>
            <p style="font-size:0.8rem; color:#888; margin-top:12px;">For testing, check your server logs or Supabase table <b>users.verification_code</b> if email didn't arrive.</p>
        <?php endif;?>
    </div>
</div>
<?php include 'footer.php';?>
