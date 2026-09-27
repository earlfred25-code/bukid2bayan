<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
setcookie('user_id', '', time()-42000, '/');
setcookie('user_name', '', time()-42000, '/');
setcookie('role', '', time()-42000, '/');
setcookie('is_admin', '', time()-42000, '/');
setcookie('username', '', time()-42000, '/');
session_destroy();
header("Location: index.php");
exit();
?>
