<?php
// This MUST be the very first line
session_start();

// Unset all of the session variables (clears the session data)
$_SESSION = array();

// Destroy the session completely
session_destroy();

// Redirect the user back to the homepage
header("Location: index.php");
exit();
?>