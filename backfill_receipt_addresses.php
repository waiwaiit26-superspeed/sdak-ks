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

// Find receipts with user_id but no payer_address
$receipts = $db->select('receipts', ['id', 'user_id', 'payer_name', 'payer_address'], [
    'user_id[>]' => 0,
    'OR' => [
        'payer_address' => null,
        'payer_address' => '',
    ],
    'ORDER' => ['id' => 'ASC'],
]);

if (!$receipts) {
    echo "✅ ไม่มีใบเสร็จที่ต้อง backfill\n";
    exit(0);
}

echo "📋 พบ " . count($receipts) . " ใบเสร็จที่ยังไม่มี payer_address\n\n";

$updated = 0;
$skipped = 0;

foreach ($receipts as $r) {
    $user = $db->get('users', ['id', 'full_name', 'work_address', 'school_organization'], [
        'id' => $r['user_id'],
    ]);

    if (!$user) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: ไม่พบสมาชิก user_id={$r['user_id']}\n";
        $skipped++;
        continue;
    }

    // Build address (same logic as FeeController::buildPayerAddress)
    $payerAddress = null;
    $workAddr = $user['work_address'] ?? null;

    if ($workAddr) {
        $addr = is_string($workAddr) ? json_decode($workAddr, true) : $workAddr;
        if (is_array($addr)) {
            $detail      = trim($addr['address'] ?? $addr['detail'] ?? '');
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
