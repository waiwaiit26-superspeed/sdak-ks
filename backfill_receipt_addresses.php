<?php
/**
 * Backfill script: เติม payer_address ให้ใบเสร็จที่ยังไม่มี
 * ดึงข้อมูลที่อยู่จาก work_address / school_organization ของสมาชิก
 *
 * รันครั้งเดียว: php backfill_receipt_addresses.php
 */

$_SERVER['SCRIPT_FILENAME'] = 'index.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

// Find receipts with user_id (re-process all to include moo/road in detail)
// Use raw SQL because Medoo can't do OR on same column with NULL check easily
$pdo = $db->pdo;
$stmt = $pdo->query("SELECT id, user_id, payer_name, payer_address FROM receipts WHERE user_id > 0 ORDER BY id ASC");
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Also show total receipt count for diagnostics
$totalStmt = $pdo->query("SELECT COUNT(*) as cnt FROM receipts");
$total = $totalStmt->fetch(PDO::FETCH_ASSOC)['cnt'];
echo "📊 ใบเสร็จทั้งหมด: {$total} รายการ\n";

$withAddr = $pdo->query("SELECT COUNT(*) as cnt FROM receipts WHERE payer_address IS NOT NULL AND payer_address != ''")->fetch(PDO::FETCH_ASSOC)['cnt'];
echo "📊 มีที่อยู่แล้ว: {$withAddr} รายการ\n";
echo "📊 ไม่มีที่อยู่ (มี user_id): " . count($receipts) . " รายการ\n\n";

if (!$receipts) {
    echo "✅ ไม่มีใบเสร็จที่ต้อง backfill\n";
    exit(0);
}

echo "📋 พบ " . count($receipts) . " ใบเสร็จที่ยังไม่มี payer_address\n\n";

$updated = 0;
$skipped = 0;

foreach ($receipts as $r) {
    $user = $db->get('users', ['id', 'full_name', 'work_address', 'home_address', 'school_organization'], [
        'id' => $r['user_id'],
    ]);

    if (!$user) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: ไม่พบสมาชิก user_id={$r['user_id']}\n";
        $skipped++;
        continue;
    }

    // Build address (same logic as FeeController::buildPayerAddress)
    // Try work_address first, then home_address
    $payerAddress = null;
    foreach (['work_address', 'home_address'] as $field) {
        $raw = $user[$field] ?? null;
        if ($raw) {
            $addr = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($addr)) {
                // Build detail from individual parts (no, moo, soi, road) or combined address/detail
                $detail = trim($addr['address'] ?? $addr['detail'] ?? '');
                $no     = trim($addr['no'] ?? '');
                $moo    = trim($addr['moo'] ?? '');
                $soi    = trim($addr['soi'] ?? '');
                $road   = trim($addr['road'] ?? '');

                if (!$detail && $no) $detail = $no;
                if ($moo && $moo !== '-') $detail .= ' หมู่ ' . $moo;
                if ($soi && $soi !== '-') $detail .= ' ซอย ' . $soi;
                if ($road && $road !== '-') $detail .= ' ถนน ' . $road;
                $detail = trim($detail);

                $subdistrict = trim($addr['subdistrict'] ?? '');
                $district    = trim($addr['district'] ?? '');
                $province    = trim($addr['province'] ?? '');
                $zipcode     = trim($addr['zipcode'] ?? $addr['postal_code'] ?? '');

                if ($detail || $subdistrict || $district || $province) {
                    $payerAddress = json_encode([
                        'detail'      => $detail,
                        'subdistrict' => $subdistrict,
                        'district'    => $district,
                        'province'    => $province,
                        'zipcode'     => $zipcode,
                    ], JSON_UNESCAPED_UNICODE);
                    break;
                }
            }
        }
    }

    // Fallback to school_organization
    if (!$payerAddress) {
        $org = $user['school_organization'] ?? '';
        if ($org) {
            $payerAddress = $org;
        }
    }

    if (!$payerAddress) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: {$user['full_name']} — ไม่มีข้อมูลที่อยู่\n";
        $skipped++;
        continue;
    }

    $db->update('receipts', ['payer_address' => $payerAddress], ['id' => $r['id']]);
    $addrPreview = mb_substr($payerAddress, 0, 60) . (mb_strlen($payerAddress) > 60 ? '...' : '');
    echo "  ✅ ใบเสร็จ #{$r['id']}: {$user['full_name']} → {$addrPreview}\n";
    $updated++;
}

echo "\n📊 สรุป: อัปเดต {$updated} รายการ, ข้าม {$skipped} รายการ\n";
