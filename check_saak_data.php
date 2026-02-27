<?php
/**
 * Temporary diagnostic: check saak member address data + receipts
 */
$_SERVER['SCRIPT_FILENAME'] = 'index.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$pdo = $db->pdo;

echo "=== RECEIPTS ===\n";
$stmt = $pdo->query("SELECT r.id, r.user_id, r.payer_name, r.payer_address, r.receipt_type FROM receipts r ORDER BY r.id");
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total receipts: " . count($receipts) . "\n";
foreach ($receipts as $r) {
    echo "  #{$r['id']} user_id={$r['user_id']} type={$r['receipt_type']} name={$r['payer_name']} addr=" . ($r['payer_address'] ?: '(NULL)') . "\n";
}

echo "\n=== USERS WITH ADDRESS DATA ===\n";
$stmt = $pdo->query("SELECT id, full_name, school_organization, work_address, home_address FROM users ORDER BY id");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "Total users: " . count($users) . "\n";
foreach ($users as $u) {
    $hasWork = !empty($u['work_address']) && $u['work_address'] !== 'null';
    $hasHome = !empty($u['home_address']) && $u['home_address'] !== 'null';
    $hasOrg = !empty($u['school_organization']);
    echo "  #{$u['id']} {$u['full_name']}";
    echo " work=" . ($hasWork ? 'YES' : 'no');
    echo " home=" . ($hasHome ? 'YES' : 'no');
    echo " org=" . ($hasOrg ? mb_substr($u['school_organization'], 0, 30) : '(none)');
    echo "\n";
    if ($hasWork) echo "    work_address: " . mb_substr($u['work_address'], 0, 100) . "\n";
    if ($hasHome) echo "    home_address: " . mb_substr($u['home_address'], 0, 100) . "\n";
}
