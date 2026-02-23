<?php
/**
 * One-time script: Fix full_name to have prefix attached to first_name without space
 * e.g. "นาง วราภรณ์ โพนะทา" → "นางวราภรณ์ โพนะทา"
 * 
 * Run locally:     php fix_fullnames.php
 * Run production:  php fix_fullnames.php --production
 */

define('SDAK_KS', true);
require_once __DIR__ . '/vendor/autoload.php';

$mode = in_array('--production', $argv ?? []) ? 'production' : 'local';
echo "Mode: {$mode}\n";

if ($mode === 'local') {
    $dbConfig = [
        'type' => 'mysql', 'host' => '127.0.0.1', 'port' => 8889,
        'database' => 'sdak_ks', 'username' => 'root', 'password' => 'root', 'charset' => 'utf8mb4',
    ];
} else {
    $dbConfig = [
        'type' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306,
        'database' => 'obecin_sdakks', 'username' => 'obecin_sdakks', 'password' => 'SdakKs@2026', 'charset' => 'utf8mb4',
    ];
}

$db = new Medoo\Medoo($dbConfig);

// Update full_name = CONCAT(prefix, first_name, ' ', last_name) where prefix is not empty
$stmt = $db->pdo->prepare("
    UPDATE users 
    SET full_name = CASE 
        WHEN prefix IS NOT NULL AND prefix != '' AND last_name IS NOT NULL AND last_name != ''
            THEN CONCAT(prefix, first_name, ' ', last_name)
        WHEN prefix IS NOT NULL AND prefix != ''
            THEN CONCAT(prefix, first_name)
        WHEN last_name IS NOT NULL AND last_name != ''
            THEN CONCAT(first_name, ' ', last_name)
        ELSE first_name
    END
    WHERE first_name IS NOT NULL AND first_name != ''
");
$stmt->execute();
$count = $stmt->rowCount();

echo "Updated {$count} records.\n";

// Show some samples using raw PDO
$rows = $db->pdo->query("SELECT id, prefix, first_name, last_name, full_name FROM users WHERE prefix IS NOT NULL AND prefix != '' LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
echo "\nSamples:\n";
foreach ($rows as $s) {
    echo "  [{$s['id']}] {$s['prefix']}|{$s['first_name']}|{$s['last_name']} → {$s['full_name']}\n";
}
echo "\nDone.\n";
