<?php
/**
 * Temporary script to audit and clean saak finance data
 * DELETE THIS FILE AFTER USE
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json; charset=utf-8');

$key = $_GET['key'] ?? '';
if ($key !== 'sdak-ks-deploy-2026-to-my-serverx') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

define('SDAK_KS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();
$action = $_GET['action'] ?? 'audit';
$result = [];

if ($action === 'audit') {
    $result['approved_members'] = $db->select('users', ['id', 'full_name', 'member_type', 'status', 'member_number', 'approved_at'], [
        'status' => 'active',
        'role' => 'member'
    ]);

    $result['finance_transactions'] = $db->query(
        "SELECT ft.id, ft.title, ft.amount, ft.transaction_date, ft.reference_no, ft.status, fc.name AS category 
         FROM finance_transactions ft 
         LEFT JOIN finance_categories fc ON ft.category_id = fc.id 
         ORDER BY ft.id"
    )->fetchAll(\PDO::FETCH_ASSOC);

    $result['membership_fees'] = $db->query(
        "SELECT mf.id, mf.user_id, u.full_name, mf.year, mf.amount, mf.fee_type, mf.status, mf.paid_at, mf.approved_at
         FROM membership_fees mf 
         LEFT JOIN users u ON mf.user_id = u.id 
         ORDER BY mf.id"
    )->fetchAll(\PDO::FETCH_ASSOC);

    $result['receipts'] = $db->select('receipts', [
        'id', 'receipt_number', 'book_number', 'payer_name', 'title', 'amount', 'receipt_type', 'reference_id', 'issued_date'
    ]);

} elseif ($action === 'clean') {
    $deleted = [];

    // Get valid fee IDs (paid + approved)
    $validFees = $db->query("SELECT id FROM membership_fees WHERE status='paid' AND approved_at IS NOT NULL")->fetchAll(\PDO::FETCH_COLUMN);

    // Clean invalid FEE-* finance transactions
    $feeTxns = $db->query("SELECT id, reference_no FROM finance_transactions WHERE reference_no LIKE 'FEE-%'")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($feeTxns as $txn) {
        $feeId = (int)str_replace('FEE-', '', $txn['reference_no']);
        if (!in_array($feeId, $validFees)) {
            $db->delete('finance_transactions', ['id' => $txn['id']]);
            $deleted['finance_transactions'][] = $txn;
        }
    }

    // Clean orphaned receipts
    $allReceipts = $db->select('receipts', ['id', 'receipt_number', 'payer_name', 'reference_id', 'receipt_type'], ['receipt_type' => 'membership_fee']);
    foreach ($allReceipts as $r) {
        if (!in_array($r['reference_id'], $validFees)) {
            $db->delete('receipts', ['id' => $r['id']]);
            $deleted['receipts'][] = $r;
        }
    }

    // Clean non-approved membership fees for non-active members
    $allFees = $db->query("SELECT mf.id, mf.user_id, mf.status, mf.year, mf.amount, u.status AS user_status, u.full_name
        FROM membership_fees mf LEFT JOIN users u ON mf.user_id = u.id ORDER BY mf.id")->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($allFees as $f) {
        if (!in_array($f['id'], $validFees)) {
            $db->delete('membership_fees', ['id' => $f['id']]);
            $deleted['membership_fees'][] = ['id' => $f['id'], 'user' => $f['full_name'], 'status' => $f['status'], 'year' => $f['year']];
        }
    }

    $result['deleted'] = $deleted;
    $result['message'] = 'Cleanup complete';
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
