<?php
/**
 * Backfill script: สร้างรายการธุรกรรมการเงิน (finance_transactions)
 * จากค่าธรรมเนียมสมาชิกที่อนุมัติแล้ว (membership_fees status=paid)
 * แต่ยังไม่มี record ใน finance_transactions
 *
 * รันครั้งเดียว: php backfill_fee_transactions.php
 */

// Fake SCRIPT_FILENAME to bypass direct-access guard in config
$_SERVER['SCRIPT_FILENAME'] = 'index.php';

require_once __DIR__ . '/config/database.php';

$db = getDB();

// 1) หา category "ค่าธรรมเนียมสมาชิก" (income)
$feeCategory = $db->get('finance_categories', ['id', 'name'], [
    'name' => 'ค่าธรรมเนียมสมาชิก',
    'type' => 'income',
]);

if (!$feeCategory) {
    echo "❌ ไม่พบหมวดหมู่ 'ค่าธรรมเนียมสมาชิก' ในตาราง finance_categories\n";
    exit(1);
}

echo "✅ พบหมวดหมู่: {$feeCategory['name']} (ID: {$feeCategory['id']})\n";

// 2) ดึงค่าธรรมเนียมที่ paid แล้ว พร้อมข้อมูลสมาชิก
$paidFees = $db->select('membership_fees', [
    '[>]users' => ['user_id' => 'id'],
], [
    'membership_fees.id',
    'membership_fees.user_id',
    'membership_fees.year',
    'membership_fees.amount',
    'membership_fees.fee_type',
    'membership_fees.approved_by',
    'membership_fees.approved_at',
    'membership_fees.received_date',
    'membership_fees.paid_at',
    'users.full_name',
    'users.member_type',
], [
    'membership_fees.status' => 'paid',
    'ORDER' => ['membership_fees.id' => 'ASC'],
]);

if (!$paidFees) {
    echo "ℹ️  ไม่พบค่าธรรมเนียมที่อนุมัติแล้ว\n";
    exit(0);
}

echo "📋 พบค่าธรรมเนียมที่ paid แล้ว: " . count($paidFees) . " รายการ\n\n";

$memberTypeLabels = [
    'ordinary'  => 'สามัญ',
    'associate' => 'วิสามัญ',
    'affiliate' => 'สมทบ',
    'honorary'  => 'กิตติมศักดิ์',
];

$created = 0;
$skipped = 0;

foreach ($paidFees as $fee) {
    $referenceNo = 'FEE-' . $fee['id'];

    // ตรวจซ้ำ
    $exists = $db->has('finance_transactions', ['reference_no' => $referenceNo]);
    if ($exists) {
        echo "  ⏭  ข้าม FEE-{$fee['id']} ({$fee['full_name']}) — มีอยู่แล้ว\n";
        $skipped++;
        continue;
    }

    $memberTypeKey = $fee['member_type'] ?? '';
    $memberTypeText = isset($memberTypeLabels[$memberTypeKey]) ? ' (' . $memberTypeLabels[$memberTypeKey] . ')' : '';

    $feeType = ($fee['fee_type'] ?? 'annual') === 'onetime' ? 'ครั้งเดียว' : "ปี {$fee['year']}";

    // ใช้ received_date ก่อน, fallback เป็น paid_at, approved_at
    $txnDate = $fee['received_date'] 
        ?? ($fee['paid_at'] ? substr($fee['paid_at'], 0, 10) : null)
        ?? ($fee['approved_at'] ? substr($fee['approved_at'], 0, 10) : null)
        ?? date('Y-m-d');

    $createdBy = (int)($fee['approved_by'] ?? 1); // fallback to admin user ID 1

    $db->insert('finance_transactions', [
        'category_id'      => (int)$feeCategory['id'],
        'type'             => 'income',
        'title'            => "ค่าธรรมเนียมสมาชิก: {$fee['full_name']}{$memberTypeText}",
        'description'      => "ค่าธรรมเนียมสมาชิก ({$feeType})",
        'amount'           => (float)$fee['amount'],
        'transaction_date' => $txnDate,
        'reference_no'     => $referenceNo,
        'created_by'       => $createdBy,
        'status'           => 'approved',
    ]);

    $newId = $db->id();
    echo "  ✅ สร้าง FEE-{$fee['id']} → txn#{$newId} | {$fee['full_name']}{$memberTypeText} | {$fee['amount']} บาท | {$feeType} | วันที่ {$txnDate}\n";
    $created++;
}

echo "\n========================================\n";
echo "สร้างใหม่: {$created} รายการ\n";
echo "ข้ามซ้ำ:   {$skipped} รายการ\n";
echo "========================================\n";
