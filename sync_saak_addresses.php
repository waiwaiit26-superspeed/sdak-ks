<?php
/**
 * Cross-site address sync: ดึงข้อมูลที่อยู่จาก sdak → saak
 * สำหรับสมาชิกที่มีอยู่ในทั้ง 2 ฐานข้อมูล (match ด้วย full_name)
 * แล้ว backfill ที่อยู่ในใบเสร็จ
 * 
 * รันบน saak.obec.in เท่านั้น
 */

$_SERVER['SCRIPT_FILENAME'] = 'index.php';
require_once __DIR__ . '/config/database.php';

// Current DB = saak
$saakDb = getDB();
$saakPdo = $saakDb->pdo;

// Connect to sdak DB
try {
    $sdakPdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=obecin_sdakks;charset=utf8mb4',
        'obecin_sdakks',
        'SdakKs@2026',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    echo "❌ ไม่สามารถเชื่อมต่อ sdak DB: " . $e->getMessage() . "\n";
    exit(1);
}

echo "=== STEP 1: Sync address from sdak → saak ===\n\n";

// Get saak users with empty addresses
$saakUsers = $saakPdo->query("SELECT id, full_name, work_address, home_address, school_organization FROM users WHERE role != 'admin' ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$synced = 0;
$skipped = 0;

foreach ($saakUsers as $su) {
    // Check if user has any meaningful address data
    $hasAddr = false;
    foreach (['work_address', 'home_address'] as $field) {
        $raw = $su[$field] ?? null;
        if ($raw) {
            $addr = json_decode($raw, true);
            if (is_array($addr)) {
                foreach (['subdistrict', 'district', 'province', 'no', 'road'] as $k) {
                    if (!empty(trim($addr[$k] ?? ''))) {
                        $hasAddr = true;
                        break 2;
                    }
                }
            }
        }
    }
    if (!empty($su['school_organization'])) $hasAddr = true;

    if ($hasAddr) {
        echo "  ✓ {$su['full_name']} — มีที่อยู่แล้ว\n";
        $skipped++;
        continue;
    }

    // Find matching user in sdak by full_name
    $cleanName = trim(preg_replace('/\s+/', ' ', $su['full_name']));
    // Try exact match first, then loose match
    $sdakUser = $sdakPdo->prepare("SELECT id, full_name, work_address, home_address, school_organization FROM users WHERE REPLACE(REPLACE(full_name, '  ', ' '), 'นาย', 'นาย') = :name OR full_name LIKE :like_name LIMIT 1");
    $sdakUser->execute([':name' => $cleanName, ':like_name' => '%' . mb_substr($cleanName, -10) . '%']);
    $sdakMatch = $sdakUser->fetch(PDO::FETCH_ASSOC);

    // Also try without prefix
    if (!$sdakMatch) {
        $shortName = preg_replace('/^(นาย|นาง|นางสาว)\s*/', '', $cleanName);
        if ($shortName !== $cleanName) {
            $sdakUser2 = $sdakPdo->prepare("SELECT id, full_name, work_address, home_address, school_organization FROM users WHERE full_name LIKE :name LIMIT 1");
            $sdakUser2->execute([':name' => '%' . $shortName . '%']);
            $sdakMatch = $sdakUser2->fetch(PDO::FETCH_ASSOC);
        }
    }

    if (!$sdakMatch) {
        echo "  ⏭ {$su['full_name']} — ไม่พบใน sdak\n";
        $skipped++;
        continue;
    }

    // Copy address data from sdak to saak
    $updates = [];
    $params = ['id' => $su['id']];

    // Copy work_address if sdak has it
    $sdakWork = $sdakMatch['work_address'] ?? null;
    if ($sdakWork) {
        $wa = json_decode($sdakWork, true);
        if (is_array($wa) && (!empty($wa['subdistrict'] ?? '') || !empty($wa['district'] ?? '') || !empty($wa['no'] ?? ''))) {
            $updates[] = 'work_address = :work_addr';
            $params['work_addr'] = $sdakWork;
        }
    }

    // Copy home_address if sdak has it
    $sdakHome = $sdakMatch['home_address'] ?? null;
    if ($sdakHome) {
        $ha = json_decode($sdakHome, true);
        if (is_array($ha) && (!empty($ha['subdistrict'] ?? '') || !empty($ha['district'] ?? '') || !empty($ha['no'] ?? ''))) {
            $updates[] = 'home_address = :home_addr';
            $params['home_addr'] = $sdakHome;
        }
    }

    // Copy school_organization if saak doesn't have it
    if (empty($su['school_organization']) && !empty($sdakMatch['school_organization'])) {
        $updates[] = 'school_organization = :org';
        $params['org'] = $sdakMatch['school_organization'];
    }

    if (empty($updates)) {
        echo "  ⏭ {$su['full_name']} — sdak ก็ไม่มีที่อยู่\n";
        $skipped++;
        continue;
    }

    $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = :id";
    $stmt = $saakPdo->prepare($sql);
    $stmt->execute($params);

    echo "  ✅ {$su['full_name']} ← copied from sdak ({$sdakMatch['full_name']})\n";
    $synced++;
}

echo "\n📊 Sync: อัปเดต {$synced} คน, ข้าม {$skipped} คน\n";

// === STEP 2: Backfill receipt addresses ===
echo "\n=== STEP 2: Backfill receipt payer_address ===\n\n";

$stmt = $saakPdo->query("SELECT id, user_id, payer_name, payer_address FROM receipts WHERE user_id > 0 AND (payer_address IS NULL OR payer_address = '') ORDER BY id ASC");
$receipts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalReceipts = $saakPdo->query("SELECT COUNT(*) as cnt FROM receipts")->fetch(PDO::FETCH_ASSOC)['cnt'];
echo "📊 ใบเสร็จทั้งหมด: {$totalReceipts} รายการ\n";
echo "📊 ไม่มีที่อยู่: " . count($receipts) . " รายการ\n\n";

if (!$receipts) {
    echo "✅ ไม่มีใบเสร็จที่ต้อง backfill\n";
    exit(0);
}

$updated = 0;
$rSkipped = 0;

foreach ($receipts as $r) {
    $user = $saakDb->get('users', ['id', 'full_name', 'work_address', 'home_address', 'school_organization'], [
        'id' => $r['user_id'],
    ]);

    if (!$user) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: ไม่พบสมาชิก user_id={$r['user_id']}\n";
        $rSkipped++;
        continue;
    }

    // Build address (same logic as FeeController::buildPayerAddress)
    $payerAddress = null;
    foreach (['work_address', 'home_address'] as $field) {
        $raw = $user[$field] ?? null;
        if ($raw) {
            $addr = is_string($raw) ? json_decode($raw, true) : $raw;
            if (is_array($addr)) {
                $detail      = trim(($addr['no'] ?? '') . ' ' . ($addr['road'] ?? '') . ' ' . ($addr['address'] ?? $addr['detail'] ?? ''));
                $detail      = trim($detail);
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
        $rSkipped++;
        continue;
    }

    $saakDb->update('receipts', ['payer_address' => $payerAddress], ['id' => $r['id']]);
    $addrPreview = mb_substr($payerAddress, 0, 80) . (mb_strlen($payerAddress) > 80 ? '...' : '');
    echo "  ✅ ใบเสร็จ #{$r['id']}: {$user['full_name']} → {$addrPreview}\n";
    $updated++;
}

echo "\n📊 Backfill สรุป: อัปเดต {$updated} รายการ, ข้าม {$rSkipped} รายการ\n";
