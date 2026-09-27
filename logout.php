<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
setcookie('user_id', '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
setcookie('user_name', '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>false,'samesite'=>'Lax']);
setcookie('role', '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>false,'samesite'=>'Lax']);
setcookie('is_admin', '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>false,'samesite'=>'Lax']);
setcookie('username', '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>false,'samesite'=>'Lax']);
session_destroy();
header("Location: index.php");
exit();
?>
