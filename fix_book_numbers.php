<?php
/**
 * Fix book_number สำหรับใบเสร็จที่ book_number ไม่ถูกต้อง
 * จะสร้าง book_number ใหม่จาก prefix ปัจจุบัน + ปี พ.ศ. ของ issued_date
 *
 * รัน: php fix_book_numbers.php
 * หรือ: curl https://saak.obec.in/fix_book_numbers.php?key=sdak-ks-deploy-2026-to-my-serverx
 */

// Allow web access with key
if (php_sapi_name() !== 'cli') {
    if (($_GET['key'] ?? '') !== 'sdak-ks-deploy-2026-to-my-serverx') {
        http_response_code(403);
        die('Forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

error_reporting(E_ALL);
ini_set('display_errors', '1');

$_SERVER['SCRIPT_FILENAME'] = 'index.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$pdo = $db->pdo;

// Get current prefix from settings
$prefixRow = $pdo->query("SELECT setting_value FROM settings WHERE setting_key = 'receipt_book_number'")->fetch(PDO::FETCH_ASSOC);
$prefix = $prefixRow ? trim($prefixRow['setting_value']) : (defined('SITE_NAME_SHORT') ? SITE_NAME_SHORT : '');

echo "📋 Prefix ปัจจุบัน: '{$prefix}'\n";

if (empty($prefix)) {
    echo "❌ ไม่มี prefix! กรุณาตั้งค่า receipt_book_number ใน settings ก่อน\n";
    exit(1);
}

// Get all receipts
$stmt = $pdo->query("SELECT id, book_number, receipt_number, issued_date FROM receipts ORDER BY id ASC");
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "📊 ใบเสร็จทั้งหมด: " . count($receipts) . " รายการ\n\n";

$updated = 0;
$skipped = 0;

foreach ($receipts as $r) {
    // Build correct book_number from prefix + issued_date year
    $ceYear = (int)date('Y', strtotime($r['issued_date']));
    $buddhistYear2 = substr((string)($ceYear + 543), -2);
    $correctBookNum = $prefix . ' ' . $buddhistYear2;

    if ($r['book_number'] === $correctBookNum) {
        $skipped++;
        continue;
    }

    echo "🔧 ID {$r['id']}: '{$r['book_number']}' → '{$correctBookNum}'\n";

    $stmt2 = $pdo->prepare("UPDATE receipts SET book_number = ? WHERE id = ?");
    $stmt2->execute([$correctBookNum, $r['id']]);
    $updated++;
}

echo "\n✅ เสร็จสิ้น: อัพเดต {$updated} รายการ, ข้ามไป {$skipped} รายการ (ถูกต้องแล้ว)\n";
