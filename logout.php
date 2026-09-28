<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Clear all session data
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Delete session cookie
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    // Extra delete for Vercel https
    setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
}

// Delete all app cookies - both secure and non-secure variant
$cookies = ['user_id','user_name','role','is_admin','username'];
foreach($cookies as $c){
    // https version (Vercel)
    setcookie($c, '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>($c==='user_id'),'samesite'=>'Lax']);
    // http version (localhost)
    setcookie($c, '', ['expires'=>time()-42000,'path'=>'/','secure'=>false,'httponly'=>($c==='user_id'),'samesite'=>'Lax']);
    // legacy
    setcookie($c, '', time()-42000, '/');
}

session_destroy();
header("Location: index.php");
exit();
?>
