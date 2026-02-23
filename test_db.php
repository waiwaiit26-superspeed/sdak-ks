<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>Database Connection Test</h3>";

$host = '127.0.0.1';
$port = 3306;
$dbname = 'obecin_sdakks';
$user = 'obecin_sdakks';
$pass = 'SdakKs@2026';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
    echo "DSN: {$dsn}<br>";
    echo "User: {$user}<br><br>";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    echo "<b style='color:green'>✅ Connected successfully!</b><br>";
    
    // ทดสอบ query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<br>Tables found: " . count($tables) . "<br>";
    foreach ($tables as $t) {
        echo "- {$t}<br>";
    }
    
} catch (PDOException $e) {
    echo "<b style='color:red'>❌ Connection failed!</b><br>";
    echo "Error: " . $e->getMessage() . "<br>";
    echo "Code: " . $e->getCode() . "<br>";
}
