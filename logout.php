<?php
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// 1. Clear session data
$_SESSION = [];

// 2. Delete PHP session cookie - both http at https variant
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // default params
    setcookie(session_name(), '', time()-42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    // extra delete para sure sa Vercel (https) at localhost (http)
    setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
    setcookie(session_name(), '', ['expires'=>time()-42000,'path'=>'/','secure'=>false,'httponly'=>true,'samesite'=>'Lax']);
    setcookie(session_name(), '', time()-42000, '/');
}

// 3. Delete lahat ng app cookies - buburahin ko both secure=true at secure=false
$cookies = ['user_id','user_name','username','role','is_admin'];
foreach($cookies as $c){
    $is_httponly = ($c === 'user_id'); // user_id lang httponly=true, yung iba false para mabasa sa JS kung kailangan
    
    // Vercel https version
    setcookie($c, '', ['expires'=>time()-42000,'path'=>'/','secure'=>true,'httponly'=>$is_httponly,'samesite'=>'Lax']);
    // localhost http version
    setcookie($c, '', ['expires'=>time()-42000,'path'=>'/','secure'=>false,'httponly'=>$is_httponly,'samesite'=>'Lax']);
    // legacy fallback
    setcookie($c, '', time()-42000, '/');
    setcookie($c, '', time()-42000, '/', '', false, $is_httponly);
}

session_destroy();

header("Location: index.php");
exit();
