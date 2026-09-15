<?php
// BUKID2BAYAN - Supabase + Vercel Final
$host = getenv('DB_HOST') ?: "aws-0-ap-southeast-1.pooler.supabase.com";
$port = getenv('DB_PORT') ?: "6543";
$dbname = getenv('DB_NAME') ?: "postgres";
$user = getenv('DB_USER') ?: "postgres.xxxxxxxxxxxx";
$password = getenv('DB_PASS') ?: "ILAGAY_MO_DITO_PASSWORD";

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn = $pdo;
} catch (PDOException $e) {
    die("Supabase failed: " . $e->getMessage());
}

function addTracking($pdo, $order_id, $status, $location, $desc){
    $stmt = $pdo->prepare("INSERT INTO tracking_logs (order_id, status, location, description) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$order_id, $status, $location, $desc]);
}
?>