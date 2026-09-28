<?php
$host = getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? "aws-0-ap-southeast-1.pooler.supabase.com");
$port = getenv('DB_PORT') ?: ($_ENV['DB_PORT'] ?? "6543");
$dbname = getenv('DB_NAME') ?: ($_ENV['DB_NAME'] ?? "postgres");
$user = getenv('DB_USER') ?: ($_ENV['DB_USER'] ?? "");
$password = getenv('DB_PASS') ?: ($_ENV['DB_PASS'] ?? "");

if(empty($user) || empty($password)){
    die("DB credentials missing - set DB_USER and DB_PASS in Vercel Env");
}

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $conn = $pdo;
} catch (PDOException $e) {
    die("Supabase failed: " . $e->getMessage());
}

if (!function_exists('addTracking')) {
    function addTracking($pdo, $order_id, $status, $location, $desc){
        try{
            $pdo->exec("CREATE TABLE IF NOT EXISTS order_tracking (id SERIAL PRIMARY KEY, order_id INT, status VARCHAR(50), location VARCHAR(255), description TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
            $stmt = $pdo->prepare("INSERT INTO order_tracking (order_id, status, location, description) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$order_id, $status, $location, $desc]);
        }catch(Exception $e){ return false; }
    }
}
?>
