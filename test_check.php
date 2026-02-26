<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h3>System Check</h3>";

// เช็ค vendor
$vendorPath = __DIR__ . '/vendor/autoload.php';
echo "<b>vendor/autoload.php:</b> ";
echo file_exists($vendorPath) 
    ? "<span style='color:green'>✅ Found</span>" 
    : "<span style='color:red'>❌ Not found</span>";
echo "<br>";

// เช็ค Medoo
if (file_exists($vendorPath)) {
    require_once $vendorPath;
    echo "<b>Medoo class:</b> ";
    echo class_exists('Medoo\Medoo') 
        ? "<span style='color:green'>✅ Loaded</span>" 
        : "<span style='color:red'>❌ Not found</span>";
    echo "<br>";
}

// เช็ค config
$configPath = __DIR__ . '/config/config.php';
echo "<b>config/config.php:</b> ";
echo file_exists($configPath)
    ? "<span style='color:green'>✅ Found</span>"
    : "<span style='color:red'>❌ Not found</span>";
echo "<br>";

// เช็ค database.php
$dbPath = __DIR__ . '/config/database.php';
echo "<b>config/database.php:</b> ";
echo file_exists($dbPath)
    ? "<span style='color:green'>✅ Found</span>"
    : "<span style='color:red'>❌ Not found</span>";
echo "<br><br>";

// ทดสอบ DB connection ผ่าน Medoo
if (file_exists($vendorPath) && file_exists($configPath)) {
    require_once $configPath;
    try {
        $db = new Medoo\Medoo([
            'type' => DB_TYPE,
            'host' => DB_HOST,
            'port' => DB_PORT,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASS,
            'charset' => DB_CHARSET,
            'collation' => 'utf8mb4_unicode_ci',
            'option' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        ]);
        echo "<b>Medoo connection:</b> <span style='color:green'>✅ Connected!</span><br>";
        
        // Check users table columns
        echo "<br><b>Users table columns:</b><br>";
        $cols = $db->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cols as $col) {
            echo "  - {$col['Field']} ({$col['Type']})<br>";
        }
        
        // Check all tables
        echo "<br><b>All tables:</b><br>";
        $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $t) {
            echo "  - {$t}<br>";
        }
    } catch (Exception $e) {
        echo "<b>Medoo connection:</b> <span style='color:red'>❌ " . $e->getMessage() . "</span><br>";
    }
}
