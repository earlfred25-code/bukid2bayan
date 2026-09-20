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
    if(!$is_pdo && isset($conn) && method_exists($conn,'close')) $conn->close();
}
include 'header.php';
?>
<style>
.form-wrapper{ padding:2rem 1rem; min-height:70vh; display:flex; align-items:center; justify-content:center; }
.form-box{ background:rgba(255,255,255,0.92); backdrop-filter:blur(14px); max-width:460px; width:100%; border-radius:16px; padding:24px; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
.role-grid{ display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:8px; }
.role-option{ border:1.
