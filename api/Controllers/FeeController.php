<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

/**
 * FeeController — Membership annual fee management
 * ระบบจัดเก็บค่าธรรมเนียมสมาชิกรายปี
 */
class FeeController extends Controller
{
    /* ------------------------------------------------------------------ */
    /*  MEMBER: ดูค่าธรรมเนียมของตนเอง                                      */
    /* ------------------------------------------------------------------ */

    /**
     * GET  ?controller=fee&action=my-fees
     * สมาชิกดูค่าธรรมเนียมทั้งหมดของตนเอง
     */
    public function myFees(): void
    {
        $fees = $this->model('MembershipFeeModel');
        $data = $fees->getUserFees((int)$this->currentUser['id']);
        Response::success($data);
    }

    /**
     * GET  ?controller=fee&action=my-current
     * สมาชิกดูสถานะปีปัจจุบัน
     */
    public function myCurrent(): void
    {
        $fees = $this->model('MembershipFeeModel');
        $settings = $this->model('SettingsModel');
        $memberType = $this->currentUser['member_type'] ?? '';

        // If no member_type set (e.g. admin), treat as no fee required
        if (empty($memberType)) {
            $buddhistYear = (int)date('Y') + 543;
            Response::success([
                'id' => null,
                'year' => $buddhistYear,
                'amount' => 0,
                'fee_type' => 'none',
                'fee_mode' => 'none',
                'status' => 'not_required',
                'payment_slip' => null,
                'paid_at' => null,
                'note' => null,
                'bank_info' => [
                    'bank_name' => $settings->get('bank_name', ''),
                    'account_name' => $settings->get('bank_account_name', ''),
                    'account_number' => $settings->get('bank_account_number', ''),
                ],
            ]);
            return;
        }

        // Get fee config from member_types table
        $mt = $this->model('MemberTypeModel');
        $feeConfig = $mt->getFeeConfig($memberType);
        $feeMode = $feeConfig['mode'];
        $feeAmount = $feeConfig['amount'];
        $buddhistYear = (int)date('Y') + 543;

        // Bank info for payment
        $bankInfo = [
            'bank_name' => $settings->get('bank_name', ''),
            'account_name' => $settings->get('bank_account_name', ''),
            'account_number' => $settings->get('bank_account_number', ''),
        ];

        // Mode: none — ไม่เก็บค่าใช้จ่าย
        if ($feeMode === 'none' || $feeAmount <= 0) {
            Response::success([
                'id' => null,
                'year' => $buddhistYear,
                'amount' => 0,
                'fee_type' => 'none',
                'fee_mode' => 'none',
                'status' => 'not_required',
                'payment_slip' => null,
                'paid_at' => null,
                'note' => null,
                'bank_info' => $bankInfo,
            ]);
            return;
        }

        // Mode: onetime — จ่ายครั้งเดียว
        if ($feeMode === 'onetime') {
            $onetimeFee = $fees->getOnetimeFee((int)$this->currentUser['id']);
            if ($onetimeFee) {
                $onetimeFee['fee_mode'] = 'onetime';
                $onetimeFee['bank_info'] = $bankInfo;
                Response::success($onetimeFee);
                return;
            }
            // No record yet
            Response::success([
                'id' => null,
                'year' => $buddhistYear,
                'amount' => $feeAmount,
                'fee_type' => 'onetime',
                'fee_mode' => 'onetime',
                'status' => 'not_created',
                'payment_slip' => null,
                'paid_at' => null,
                'note' => null,
                'bank_info' => $bankInfo,
            ]);
            return;
        }

        // Mode: annual — จ่ายรายปี
        $current = $fees->getCurrentFee((int)$this->currentUser['id']);
        if ($current) {
            $current['fee_mode'] = 'annual';
            $current['bank_info'] = $bankInfo;
            Response::success($current);
            return;
        }

        Response::success([
            'id' => null,
            'year' => $buddhistYear,
            'amount' => $feeAmount,
            'fee_type' => 'annual',
            'fee_mode' => 'annual',
            'status' => 'not_created',
            'payment_slip' => null,
            'paid_at' => null,
            'note' => null,
            'bank_info' => $bankInfo,
        ]);
    }

    /**
     * POST  ?controller=fee&action=upload-slip
     * สมาชิกอัปโหลดหลักฐานการชำระ
     */
    public function uploadSlip(): void
    {
        $this->requirePost();
        $input = $this->input();
        $feeId = (int)($input['fee_id'] ?? 0);
        $slip  = $input['payment_slip'] ?? '';

        if (!$feeId) Response::error('กรุณาระบุ fee_id');
        if (!$slip) Response::error('กรุณาอัปโหลดหลักฐานการชำระ');

        $fees = $this->model('MembershipFeeModel');
        $fee = $fees->find($feeId);
        if (!$fee) Response::error('ไม่พบข้อมูลค่าธรรมเนียม', 404);

        // Only owner or admin
        if ($fee['user_id'] != $this->currentUser['id'] && $this->currentUser['role'] !== 'admin') {
            Response::error('ไม่มีสิทธิ์ดำเนินการ', 403);
        }

        // Block re-upload if already approved/verified
        if (!empty($fee['approved_at'])) {
            Response::error('ค่าธรรมเนียมนี้ได้รับการตรวจสอบแล้ว ไม่สามารถเปลี่ยนสลิปได้');
        }

        $fees->uploadSlip($feeId, $slip);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'upload_slip', 'fee',
            "อัปโหลดหลักฐานชำระค่าธรรมเนียม ปี {$fee['year']}",
            $feeId, 'fee'
        );

        // แจ้งเตือน Telegram
        try {
            \App\Core\Telegram::notifyFeeSlipUpload($this->currentUser, $fee);
        } catch (\Throwable $e) { /* ไม่บล็อก flow หลัก */ }

        Response::success(null, 'อัปโหลดหลักฐานสำเร็จ รอการตรวจสอบ');
    }

    /**
     * POST  ?controller=fee&action=create-my-fee
     * สมาชิกสร้างรายการค่าธรรมเนียม (ครั้งเดียว หรือ รายปี)
     */
    public function createMyFee(): void
    {
        $this->requirePost();

        $mt = $this->model('MemberTypeModel');
        $memberType = $this->currentUser['member_type'];
        $feeConfig = $mt->getFeeConfig($memberType);
        $feeMode = $feeConfig['mode'];
        $feeAmount = $feeConfig['amount'];

        if ($feeMode === 'none' || $feeAmount <= 0) {
            Response::error('ประเภทสมาชิกของคุณไม่ต้องชำระค่าธรรมเนียม');
        }

        $buddhistYear = (int)date('Y') + 543;
        $fees = $this->model('MembershipFeeModel');

        if ($feeMode === 'onetime') {
            // Check if already has one-time record
            $existing = $fees->getOnetimeFee((int)$this->currentUser['id']);
            if ($existing) {
                Response::success(['fee_id' => (int)$existing['id']], 'มีรายการค่าธรรมเนียมแล้ว');
                return;
            }
            $id = $fees->upsertFee((int)$this->currentUser['id'], $buddhistYear, $feeAmount, 'onetime');

            Auth::logActivity(
                (int)$this->currentUser['id'], 'create_fee', 'fee',
                "สร้างรายการค่าธรรมเนียม (ครั้งเดียว) จำนวน {$feeAmount} บาท",
                $id, 'fee'
            );
        } else {
            // annual
            $existing = $fees->getUserYearFee((int)$this->currentUser['id'], $buddhistYear);
            if ($existing) {
                Response::success(['fee_id' => (int)$existing['id']], 'มีรายการค่าธรรมเนียมปีนี้แล้ว');
                return;
            }
            $id = $fees->upsertFee((int)$this->currentUser['id'], $buddhistYear, $feeAmount, 'annual');

            Auth::logActivity(
                (int)$this->currentUser['id'], 'create_fee', 'fee',
                "สร้างรายการค่าธรรมเนียม ปี {$buddhistYear} จำนวน {$feeAmount} บาท",
                $id, 'fee'
            );
        }

        Response::success(['fee_id' => $id], 'สร้างรายการค่าธรรมเนียมสำเร็จ');
    }

    /* ------------------------------------------------------------------ */
    /*  ADMIN: จัดการค่าธรรมเนียม                                          */
    /* ------------------------------------------------------------------ */

    /**
     * GET  ?controller=fee&action=list
     * Admin: รายการค่าธรรมเนียมพร้อม filter
     */
    public function list(): void
    {
        $fees = $this->model('MembershipFeeModel');
        $result = $fees->getFilteredList(
            [
                'year' => $this->query('year'),
                'status' => $this->query('status'),
                'user_id' => $this->query('user_id'),
                'search' => $this->query('search'),
            ],
            $this->getPage(),
            $this->getPerPage(30)
        );

        Response::paginated($result['data'], $result['total'], $result['page'], $result['per_page']);
    }

    /**
     * GET  ?controller=fee&action=summary
     * Admin: สรุปสถิติค่าธรรมเนียมตามปี
     */
    public function summary(): void
    {
        $year = (int)($this->query('year') ?: (date('Y') + 543));
        $fees = $this->model('MembershipFeeModel');
        $summary = $fees->getYearSummary($year);
        $summary['year'] = $year;

        Response::success($summary);
    }

    /**
     * POST  ?controller=fee&action=generate
     * Admin: สร้างรายการค่าธรรมเนียมให้สมาชิกทั้งหมดตามปี
     */
    public function generate(): void
    {
        $this->requirePost();
        $input = $this->input();
        $year = (int)($input['year'] ?? (date('Y') + 543));

        // Get fee configs from member_types table
        $mt = $this->model('MemberTypeModel');
        $allConfigs = $mt->getAllFeeConfigs();
        $feeAmounts = [];
        $feeModes = [];
        foreach ($allConfigs as $key => $cfg) {
            $feeAmounts[$key] = $cfg['amount'];
            $feeModes[$key]   = $cfg['mode'];
        }

        $fees = $this->model('MembershipFeeModel');
        $generated = $fees->generateYearlyFees($year, $feeAmounts, $feeModes);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'generate_fees', 'fee',
            "สร้างรายการค่าธรรมเนียมปี {$year} จำนวน {$generated} รายการ"
        );

        Response::success(['generated' => $generated], "สร้างรายการค่าธรรมเนียม {$generated} รายการสำเร็จ");
    }

    /**
     * POST  ?controller=fee&action=approve
     * Admin: อนุมัติการชำระ
     */
    public function approve(): void
    {
        $this->requirePost();
        $input = $this->input();
        $feeId = (int)($input['fee_id'] ?? 0);
        $action = $input['action'] ?? '';
        $note = $input['note'] ?? '';
        $receivedDate = $input['received_date'] ?? null;

        if (!$feeId) Response::error('กรุณาระบุ fee_id');

        $fees = $this->model('MembershipFeeModel');
        $fee = $fees->find($feeId);
        if (!$fee) Response::error('ไม่พบรายการค่าธรรมเนียม', 404);

        $users = $this->model('UserModel');
        $member = $users->find((int)$fee['user_id'], ['full_name', 'member_type', 'school_organization', 'work_address', 'home_address']);

        switch ($action) {
            case 'approve':
                if (empty($receivedDate)) {
                    Response::error('กรุณาระบุวันที่ได้รับเงิน');
                }
                $fees->approve($feeId, (int)$this->currentUser['id'], $note, $receivedDate);

                // Auto-generate receipt (use received_date as issued_date)
                $this->generateFeeReceipt($fee, $member, $receivedDate);

                // Auto-create finance transaction for the approved fee
                $this->generateFeeTransaction($fee, $member, $receivedDate);

                Auth::logActivity(
                    (int)$this->currentUser['id'], 'approve_fee', 'fee',
                    "อนุมัติค่าธรรมเนียม: {$member['full_name']} ปี {$fee['year']}",
                    $feeId, 'fee'
                );
                Response::success(null, 'อนุมัติการชำระสำเร็จ');
                break;

            case 'reject':
                $fees->reject($feeId, $note);
                Auth::logActivity(
                    (int)$this->currentUser['id'], 'reject_fee', 'fee',
                    "ปฏิเสธค่าธรรมเนียม: {$member['full_name']} ปี {$fee['year']} - {$note}",
                    $feeId, 'fee'
                );
                Response::success(null, 'ปฏิเสธการชำระสำเร็จ รายการกลับเป็นรอชำระ');
                break;

            case 'waive':
                $fees->waive($feeId, (int)$this->currentUser['id'], $note);
                Auth::logActivity(
                    (int)$this->currentUser['id'], 'waive_fee', 'fee',
                    "ยกเว้นค่าธรรมเนียม: {$member['full_name']} ปี {$fee['year']}",
                    $feeId, 'fee'
                );
                Response::success(null, 'ยกเว้นค่าธรรมเนียมสำเร็จ');
                break;

            default:
                Response::error('การดำเนินการไม่ถูกต้อง');
        }
    }

    /**
     * Auto-generate a receipt when a membership fee is approved
     * Uses received_date as issued_date to determine the year for receipt numbering
     */
    private function generateFeeReceipt(array $fee, array $member, ?string $receivedDate = null): void
    {
        $receipts = $this->model('ReceiptModel');

        // Don't create duplicate receipts
        $existing = $receipts->findByReference('membership_fee', (int)$fee['id']);
        if ($existing) return;

        $settings = $this->model('SettingsModel');

        // Get member type label from DB
        $mt = $this->model('MemberTypeModel');
        $memberTypeKey = $member['member_type'] ?? '';
        $typeData = $mt->findByKey($memberTypeKey);
        $memberTypeSuffix = $typeData ? ($typeData['label_short'] ?: $typeData['label']) : '';

        $feeType = ($fee['fee_type'] ?? 'annual') === 'onetime' ? 'ครั้งเดียว' : "ปี {$fee['year']}";
        $title = 'ค่าธรรมเนียมสมาชิก' . ($memberTypeSuffix ? $memberTypeSuffix : '');
        $description = $title . " ({$feeType})";

        // Build payer address from work_address or school_organization
        $payerAddress = $this->buildPayerAddress($member);

        $receipts->createReceipt([
            'user_id'       => (int)$fee['user_id'],
            'receipt_type'  => 'membership_fee',
            'reference_id'  => (int)$fee['id'],
            'title'         => $title,
            'payer_name'    => $member['full_name'],
            'payer_address' => $payerAddress,
            'description'   => $description,
            'amount'        => (float)$fee['amount'],
            'received_by'   => $settings->get('signature_name', ''),
            'issued_date'   => $receivedDate ?: date('Y-m-d'),
        ]);
    }

    /**
     * Auto-create a finance transaction when a membership fee is approved
     */
    /**
     * Build payer address string from member's work_address or school_organization
     */
    public static function buildPayerAddress(array $member): ?string
    {
        // Try work_address JSON first, then home_address as fallback
        foreach (['work_address', 'home_address'] as $field) {
            $raw = $member[$field] ?? null;
            if ($raw) {
                $addr = is_string($raw) ? json_decode($raw, true) : $raw;
                if (is_array($addr)) {
                    // Build detail from individual parts (no, moo, soi, road) or combined address/detail
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

                    $subdistrict = trim($addr['subdistrict'] ?? '');
                    $district    = trim($addr['district'] ?? '');
                    $province    = trim($addr['province'] ?? '');
                    $zipcode     = trim($addr['zipcode'] ?? $addr['postal_code'] ?? '');

                    // Return structured JSON so receipt renderer can display multi-line
                    if ($detail || $subdistrict || $district || $province) {
                        return json_encode([
                            'detail'      => $detail,
                            'subdistrict' => $subdistrict,
                            'district'    => $district,
                            'province'    => $province,
                            'zipcode'     => $zipcode,
                        ], JSON_UNESCAPED_UNICODE);
                    }
                }
            }
        }
        // Fallback to school/organization name (plain string)
        $org = $member['school_organization'] ?? '';
        return $org ?: null;
    }

    private function generateFeeTransaction(array $fee, array $member, ?string $receivedDate = null): void
    {
        $txnModel = $this->model('FinanceTransactionModel');
        $referenceNo = 'FEE-' . $fee['id'];

        // Don't create duplicate transactions
        $existing = $txnModel->findBy(['reference_no' => $referenceNo]);
        if ($existing) return;

        // Find the "ค่าธรรมเนียมสมาชิก" income category
        $catModel = $this->model('FinanceCategoryModel');
        $feeCategory = $catModel->findBy(['name' => 'ค่าธรรมเนียมสมาชิก', 'type' => 'income']);

        if (!$feeCategory) return; // category not found, skip

        // Get member type label from DB
        $mt = $this->model('MemberTypeModel');
        $memberTypeKey = $member['member_type'] ?? '';
        $typeData = $mt->findByKey($memberTypeKey);
        $memberTypeText = $typeData ? ' (' . ($typeData['label_short'] ?: $typeData['label']) . ')' : '';

        $feeType = ($fee['fee_type'] ?? 'annual') === 'onetime' ? 'ครั้งเดียว' : "ปี {$fee['year']}";

        $txnModel->create([
            'category_id'      => (int)$feeCategory['id'],
            'type'             => 'income',
            'title'            => "ค่าธรรมเนียมสมาชิก: {$member['full_name']}{$memberTypeText}",
            'description'      => "ค่าธรรมเนียมสมาชิก ({$feeType})",
            'amount'           => (float)$fee['amount'],
            'transaction_date' => $receivedDate ?: date('Y-m-d'),
            'reference_no'     => $referenceNo,
            'created_by'       => (int)$this->currentUser['id'],
            'status'           => 'approved',
        ]);
    }
}
