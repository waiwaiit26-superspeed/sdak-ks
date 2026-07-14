<?php
/**
 * Backfill script: ปรับรูปแบบ payer_address สำหรับใบเสร็จเดิม
 * - ค่าเริ่มต้น: ใช้ที่อยู่ที่ทำงาน + ชื่อหน่วยงาน
 * - ถ้าใบเสร็จถูกกำหนดให้ใช้ที่อยู่ปัจจุบัน: คงไว้เป็นที่อยู่ปัจจุบัน (organization ว่าง)
 * - รองรับการแปลงข้อมูลเดิมทั้งแบบ plain text และ JSON เก่า
 *
 * รันครั้งเดียว: php backfill_receipt_addresses.php
 * หรือ: https://<domain>/backfill_receipt_addresses.php?key=<WEBHOOK_SECRET>
 */

if (php_sapi_name() !== 'cli') {
    $allowedKey = '';
    $secretFile = __DIR__ . '/webhook-secret.php';
    if (file_exists($secretFile)) {
        require_once $secretFile;
        if (defined('WEBHOOK_SECRET')) {
            $allowedKey = WEBHOOK_SECRET;
        }
    }

    if (!$allowedKey || (($_GET['key'] ?? '') !== $allowedKey)) {
        http_response_code(403);
        die('Forbidden');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

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
echo "📊 ตรวจทั้งหมด (มี user_id): " . count($receipts) . " รายการ\n\n";

if (!$receipts) {
    echo "✅ ไม่มีใบเสร็จที่ต้อง backfill\n";
    exit(0);
}

echo "📋 เริ่ม normalize ข้อมูลที่อยู่ใบเสร็จ\n\n";

$updated = 0;
$skipped = 0;

function parseAddressArray($raw): array {
    if (!$raw) return [];
    if (is_array($raw)) return $raw;
    if (!is_string($raw)) return [];
    $parsed = json_decode($raw, true);
    return is_array($parsed) ? $parsed : [];
}

function buildAddressParts(array $addr): array {
    $detail = trim($addr['address'] ?? $addr['detail'] ?? '');
    $no     = trim($addr['no'] ?? '');
    $moo    = trim($addr['moo'] ?? '');
    $soi    = trim($addr['soi'] ?? '');
    $road   = trim($addr['road'] ?? '');

    if (!$detail && $no && $no !== '-') $detail = $no;
    if ($moo && $moo !== '-') $detail .= '   หมู่ ' . $moo;
    if ($soi && $soi !== '-') $detail .= '   ซอย ' . $soi;
    if ($road && $road !== '-') $detail .= '   ถนน ' . $road;
    $detail = trim($detail);

    return [
        'detail'      => $detail,
        'subdistrict' => trim($addr['subdistrict'] ?? ''),
        'district'    => trim($addr['district'] ?? ''),
        'province'    => trim($addr['province'] ?? ''),
        'zipcode'     => trim($addr['zipcode'] ?? $addr['postal_code'] ?? ''),
    ];
}

function buildReceiptAddressJson(array $parts, string $organization = ''): ?string {
    if (empty($parts['detail']) && empty($parts['subdistrict']) && empty($parts['district']) && empty($parts['province']) && $organization === '') {
        return null;
    }

    return json_encode([
        'organization' => $organization,
        'detail'       => $parts['detail'] ?? '',
        'subdistrict'  => $parts['subdistrict'] ?? '',
        'district'     => $parts['district'] ?? '',
        'province'     => $parts['province'] ?? '',
        'zipcode'      => $parts['zipcode'] ?? '',
    ], JSON_UNESCAPED_UNICODE);
}

function looksSameAddress(array $a, array $b): bool {
    return trim((string)($a['detail'] ?? '')) === trim((string)($b['detail'] ?? ''))
        && trim((string)($a['subdistrict'] ?? '')) === trim((string)($b['subdistrict'] ?? ''))
        && trim((string)($a['district'] ?? '')) === trim((string)($b['district'] ?? ''))
        && trim((string)($a['province'] ?? '')) === trim((string)($b['province'] ?? ''))
        && trim((string)($a['zipcode'] ?? '')) === trim((string)($b['zipcode'] ?? ''));
}

foreach ($receipts as $r) {
    $user = $db->get('users', ['id', 'full_name', 'work_address', 'home_address', 'school_organization'], [
        'id' => $r['user_id'],
    ]);

    if (!$user) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: ไม่พบสมาชิก user_id={$r['user_id']}\n";
        $skipped++;
        continue;
    }

    $org = trim((string)($user['school_organization'] ?? ''));
    $workParts = buildAddressParts(parseAddressArray($user['work_address'] ?? null));
    $homeParts = buildAddressParts(parseAddressArray($user['home_address'] ?? null));

    $currentRaw = $r['payer_address'] ?? '';
    $currentParsed = is_string($currentRaw) ? json_decode($currentRaw, true) : null;
    $currentParts = is_array($currentParsed) ? [
        'detail'      => trim((string)($currentParsed['detail'] ?? '')),
        'subdistrict' => trim((string)($currentParsed['subdistrict'] ?? '')),
        'district'    => trim((string)($currentParsed['district'] ?? '')),
        'province'    => trim((string)($currentParsed['province'] ?? '')),
        'zipcode'     => trim((string)($currentParsed['zipcode'] ?? '')),
    ] : [];

    $payerAddress = null;

    // If current receipt address equals member home address, preserve as current-address choice.
    if (!empty($currentParts) && !empty($homeParts['detail']) && looksSameAddress($currentParts, $homeParts)) {
        $payerAddress = buildReceiptAddressJson($homeParts, '');
    }

    // Otherwise default to work-address choice (show organization on receipt)
    if (!$payerAddress) {
        $payerAddress = buildReceiptAddressJson($workParts, $org);
    }

    // If no work address, fallback to home address (keep organization for default behavior)
    if (!$payerAddress) {
        $payerAddress = buildReceiptAddressJson($homeParts, $org);
    }

    // Last fallback: only organization
    if (!$payerAddress && $org !== '') {
        $payerAddress = buildReceiptAddressJson([
            'detail' => '', 'subdistrict' => '', 'district' => '', 'province' => '', 'zipcode' => ''
        ], $org);
    }

    if (!$payerAddress) {
        echo "  ⏭ ใบเสร็จ #{$r['id']}: {$user['full_name']} — ไม่มีข้อมูลที่อยู่\n";
        $skipped++;
        continue;
    }

    if ((string)$currentRaw === (string)$payerAddress) {
        $skipped++;
        continue;
    }

    $db->update('receipts', ['payer_address' => $payerAddress], ['id' => $r['id']]);
    $addrPreview = mb_substr($payerAddress, 0, 60) . (mb_strlen($payerAddress) > 60 ? '...' : '');
    echo "  ✅ ใบเสร็จ #{$r['id']}: {$user['full_name']} → {$addrPreview}\n";
    $updated++;
}

echo "\n📊 สรุป: อัปเดต {$updated} รายการ, ข้าม {$skipped} รายการ\n";
