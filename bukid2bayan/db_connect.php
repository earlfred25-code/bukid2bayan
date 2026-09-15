<?php
// BUKID2BAYAN - Supabase Connection
// Palitan mo lang yung 3 na to galing sa Supabase > Connect button

$host = "aws-0-ap-southeast-1.pooler.supabase.com"; // galing sa Connect > Transaction Pooler
$port = "6543";
$dbname = "postgres";
$user = "postgres.xxxxxxxxxxxx"; // palitan mo - yung buong user na nasa Connect
$password = "ILAGAY_MO_DITO_PASSWORD_NG_SUPABASE_PROJECT_MO"; // yung password na ginawa mo nung nag-create ka ng project

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;user=$user;password=$password;sslmode=require;options=--cluster=aws-0-ap-southeast-1";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Para gumana pa rin yung old code mo na $conn->query
    $conn = $pdo;

} catch (PDOException $e) {
    die("Supabase connection failed: " . $e->getMessage() . " - Check mo host/user/password sa Supabase > Connect");
}

// --- Supabase version ng tracking (PDO) ---
function addTracking($pdo, $order_id, $status, $location, $desc){
    try {
        $stmt = $pdo->prepare("INSERT INTO tracking_logs (order_id, status, location, description) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$order_id, $status, $location, $desc]);
    } catch (PDOException $e) {
        return false;
    }
}
?>