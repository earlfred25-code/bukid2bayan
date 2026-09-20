<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include '../db_connect.php';
$is_pdo = $conn instanceof PDO;

try {
    // hanapin id ni earlfred
    if ($is_pdo) {
        $stmt = $conn->query("SELECT id FROM users WHERE username LIKE '%earlfred%' LIMIT 1");
        $earl_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        // ayusin lang yung walang may-ari at yung del/Admin
        $conn->query("UPDATE products SET farmer_id = $earl_id WHERE farmer_id IS NULL OR farmer_id NOT IN (SELECT id FROM users) OR farmer_id IN (SELECT id FROM users WHERE username IN ('del','Admin','admin','Del'))");
    } else {
        $res = $conn->query("SELECT id FROM users WHERE username LIKE '%earlfred%' LIMIT 1");
        $earl_id = $res->fetch_assoc()['id'];
        $conn->query("UPDATE products SET farmer_id = $earl_id WHERE farmer_id IS NULL OR farmer_id NOT IN (SELECT id FROM users) OR farmer_id IN (SELECT id FROM users WHERE username IN ('del','Admin','admin','Del'))");
    }
    echo "Naayos na! Yung del at Admin lang ginawang earlfred. ID: $earl_id";
} catch(Exception $e){ echo "Error: ".$e->getMessage(); }
?>
