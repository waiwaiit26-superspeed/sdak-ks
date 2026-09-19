<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

/**
 * ReceiptController — Receipt management
 * ระบบใบเสร็จรับเงิน
 */
class ReceiptController extends Controller
{
    /* ── MEMBER ── */

    /**
     * GET  ?controller=receipt&action=my-receipts
     * All receipts for current user
     */
    public function myReceipts(): void
    {
        $receipts = $this->model('ReceiptModel');
        $data = $receipts->getUserReceipts((int)$this->currentUser['id']);
        Response::success($data);
    }

    /**
     * GET  ?controller=receipt&action=detail&id=X
     * Receipt detail (member can only see own, admin can see all)
     */
    public function detail(): void
    {
        $id = (int)$this->query('id');
        if (!$id) Response::error('กรุณาระบุ id ใบเสร็จ');

        $receipts = $this->model('ReceiptModel');
        $receipt = $receipts->getDetail($id);
        if (!$receipt) Response::error('ไม่พบใบเสร็จ', 404);

        // Only owner, full admin, or members-area sub-admin with fees permission
        $isAdmin = $this->currentUser['role'] === 'admin';
        $isFeeSubAdmin = false;
        if (!$isAdmin) {
            $sa = $this->model('SubAdminModel');
            $isFeeSubAdmin = $sa->hasPermission((int)$this->currentUser['id'], 'members', 'fees');
        }
        if ($receipt['user_id'] != $this->currentUser['id'] && !$isAdmin && !$isFeeSubAdmin) {
            Response::error('ไม่มีสิทธิ์ดูใบเสร็จนี้', 403);
        }

        // Resolve member_type_label from member_types table
        if (!empty($receipt['member_type'])) {
            $memberTypes = $this->model('MemberTypeModel');
            $mt = $memberTypes->findByKey($receipt['member_type']);
            $receipt['member_type_label'] = $mt ? $mt['label'] : $receipt['member_type'];
        }

        // Load reference data (source transaction)
        $refData = $receipts->getReferenceData(
            $receipt['receipt_type'] ?? '',
            !empty($receipt['reference_id']) ? (int)$receipt['reference_id'] : null
        );
        $receipt['reference_data'] = $refData;

        // Keep membership-fee receipt description aligned with current reference data.
        if (($receipt['receipt_type'] ?? '') === 'membership_fee' && is_array($refData)) {
            $isOnetime = ($refData['fee_type'] ?? 'annual') === 'onetime';
            $feeLabel = $isOnetime ? 'ครั้งเดียว' : ('ปี ' . (int)($refData['fee_year'] ?? 0));
            $memberTypeLabel = trim((string)($receipt['member_type_label'] ?? ''));
            $baseTitle = 'ค่าธรรมเนียมสมาชิก' . $memberTypeLabel;
            $receipt['description'] = trim($baseTitle . ' (' . $feeLabel . ')');
        }

        // Add receipt settings for rendering
        $settings = $this->model('SettingsModel');
        $receipt['organization_name'] = $settings->get('receipt_organization_name', 'สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์');
        $receipt['organization_address'] = $settings->get('receipt_organization_address', 'อำเภอเมือง จังหวัดกาฬสินธุ์');

        // Signature data
        $receipt['signature_mode']     = $settings->get('signature_mode', 'manual');
        $receipt['signature_name']     = $settings->get('signature_name', '');
        $receipt['signature_position'] = $settings->get('signature_position', 'เหรัญญิก');
        $receipt['signature_image']    = $settings->get('signature_image', '');
        $receipt['signature_show_name']     = $settings->get('signature_show_name', '1');
        $receipt['signature_show_position'] = $settings->get('signature_show_position', '1');

        Response::success($receipt);
    }

    /**
     * GET  ?controller=receipt&action=find-by-ref&reference_no=XXX
     */
    public function findByRef(): void
    {
        $refNo = trim($this->query('reference_no') ?? '');
        if (!$refNo) Response::error('กรุณาระบุ reference_no');

        $receipts = $this->model('ReceiptModel');
        $receipt = null;

        if (preg_match('/^FEE-(\d+)$/', $refNo, $m)) {
            $receipt = $receipts->findByReference('membership_fee', (int)$m[1]);
        } elseif (preg_match('/^ACT-REG-(\d+)$/', $refNo, $m)) {
            $receipt = $receipts->findByReference('activity_fee', (int)$m[1]);
        }

        if (!$receipt) {
            Response::error('ไม่พบใบเสร็จสำหรับรายการนี้', 404);
        }

        Response::success(['receipt_id' => $receipt['id']]);
    }

    /**
     * GET  ?controller=receipt&action=search-members&q=xxx
     * Search members for receipt creation (finance managers + admin)
     */
    public function searchMembers(): void
    {
        $q = trim($this->query('q') ?? '');

        // Members need finance permission
        if ($this->currentUser['role'] !== 'admin') {
            $fm = $this->model('FinanceManagerModel');
            $perms = $fm->getByUserId((int)$this->currentUser['id']);
            if (!$perms || !$perms['is_active']) {
                Response::error('ไม่มีสิทธิ์', 403);
            }
        }

        $users = $this->model('UserModel');
        $where = ['role' => 'member', 'status' => 'approved'];
        if ($q) {
            $where['OR'] = [
                'full_name[~]' => '%' . $q . '%',
                'email[~]' => '%' . $q . '%',
            ];
        }
        $where['LIMIT'] = 50;
        $where['ORDER'] = ['full_name' => 'ASC'];

        $data = $users->all(['id', 'full_name', 'email', 'school_organization', 'work_address', 'home_address'], $where);
        Response::success($data);
    }

    /**
     * GET  ?controller=receipt&action=list
     * Paginated receipts (admin or members-area sub-admin with fees permission)
     */
    public function list(): void
    {
        // Allow members-area sub-admin with fees permission
        if ($this->currentUser['role'] !== 'admin') {
            $sa = $this->model('SubAdminModel');
            if (!$sa->hasPermission((int)$this->currentUser['id'], 'members', 'fees')) {
                Response::error('คุณไม่มีสิทธิ์ดูรายการใบเสร็จ', 403);
            }
        }
        $receipts = $this->model('ReceiptModel');
        $result = $receipts->getFilteredList(
            [
                'receipt_type' => $this->query('receipt_type'),
                'user_id'      => $this->query('user_id'),
                'search'       => $this->query('search'),
                'date_from'    => $this->query('date_from'),
                'date_to'      => $this->query('date_to'),
            ],
            $this->getPage(),
            $this->getPerPage(30)
        );

        Response::paginated($result['data'], $result['total'], $result['page'], $result['per_page']);
    }

    /**
     * GET  ?controller=receipt&action=next-number
     * Get auto-generated next receipt number for current year
     */
    public function nextNumber(): void
    {
        $settings = $this->model('SettingsModel');
        $prefix = trim($settings->get('receipt_book_number', SITE_NAME_SHORT));

        $issuedDate = $this->query('issued_date') ?: date('Y-m-d');
        $receipts = $this->model('ReceiptModel');
        $bookNum = $receipts::buildBookNumber($prefix, $issuedDate);
        $startNumber = (int)$settings->get('receipt_start_number', '1');
        $nextNum = $receipts->getNextNumber($bookNum, $startNumber);

        Response::success([
            'book_number'    => $bookNum,
            'receipt_number' => $nextNum,
        ]);
    }

    /**
     * POST  ?controller=receipt&action=create
     * Create receipt (admin or member with finance permission)
     */
    public function create(): void
    {
        $this->requirePost();
        $input = $this->input();

        // Check finance permission for members
        if ($this->currentUser['role'] === 'member') {
            $fm = $this->model('FinanceManagerModel');
            $perms = $fm->getByUserId((int)$this->currentUser['id']);
            if (!$perms || !$perms['is_active']) {
                Response::error('คุณไม่มีสิทธิ์ออกใบเสร็จ', 403);
            }
        }

        if (empty($input['title'])) Response::error('กรุณาระบุหัวข้อใบเสร็จ');
        if (empty($input['amount']) || (float)$input['amount'] <= 0) Response::error('กรุณาระบุจำนวนเงิน');

        $users = $this->model('UserModel');
        $user = null;
        $userId = !empty($input['user_id']) ? (int)$input['user_id'] : null;
        $rawAddressSource = strtolower(trim((string)($input['address_source'] ?? 'work')));
        $addressSource = in_array($rawAddressSource, ['current', 'home', 'personal'], true)
            ? 'personal'
            : 'organization';

        if ($userId) {
            $user = $users->find($userId);
            if (!$user) Response::error('ไม่พบสมาชิก', 404);
        }

        // For non-member payers, payer_name is required
        if (!$user && empty($input['payer_name'])) {
            Response::error('กรุณาระบุชื่อผู้ชำระเงิน');
        }

        $receipts = $this->model('ReceiptModel');
        $settings = $this->model('SettingsModel');

        // Use full_name from user if available, otherwise payer_name input
        $payerName = $user ? $user['full_name'] : trim($input['payer_name']);

        // Auto-fill payer_address from member data if not provided
        $payerAddress = $input['payer_address'] ?? null;
        if (!$payerAddress && $user) {
            $payerAddress = FeeController::buildPayerAddress($user, $addressSource);
        }

        // Validate custom receipt_number for duplicate before create
        if (!empty($input['receipt_number'])) {
            $settings = $this->model('SettingsModel');
            $prefix = trim($settings->get('receipt_book_number', SITE_NAME_SHORT));
            $issuedDate = $input['issued_date'] ?? date('Y-m-d');
            $bookNum = $receipts::buildBookNumber($prefix, $issuedDate);
            $duplicate = $receipts->findDuplicate($bookNum, $input['receipt_number']);
            if ($duplicate) {
                Response::error('เลขที่ใบเสร็จ ' . $input['receipt_number'] . ' ในเล่ม ' . $bookNum . ' ซ้ำกับใบเสร็จที่มีอยู่แล้ว');
            }
        }

        $receiptData = [
            'user_id'       => $userId ?? 0,
            'receipt_type'  => $input['receipt_type'] ?? 'other',
            'reference_id'  => $input['reference_id'] ?? null,
            'title'         => trim($input['title']),
            'payer_name'    => $payerName,
            'payer_address' => $payerAddress,
            'description'   => $input['description'] ?? trim($input['title']),
            'amount'        => (float)$input['amount'],
            'amount_text'   => $input['amount_text'] ?? '',
            'received_by'   => $input['received_by'] ?? $settings->get('signature_name', ''),
            'issued_date'   => $input['issued_date'] ?? date('Y-m-d'),
        ];

        // Allow custom receipt_number if provided
        if (!empty($input['receipt_number'])) {
            $receiptData['receipt_number'] = $input['receipt_number'];
        }

        try {
            $id = $receipts->createReceipt($receiptData);
        } catch (\Throwable $e) {
            $msg = $e->getMessage() ?: 'ไม่สามารถออกใบเสร็จได้';
            if (stripos($msg, 'duplicate') !== false || stripos($msg, 'ซ้ำ') !== false) {
                Response::error('เลขที่ใบเสร็จซ้ำ กรุณาลองใหม่อีกครั้ง');
            }
            Response::error($msg);
        }

        Auth::logActivity(
            (int)$this->currentUser['id'], 'create_receipt', 'receipt',
            "ออกใบเสร็จ: {$input['title']} ให้ {$payerName}",
            $id, 'receipt'
        );

        Response::success(['id' => $id], 'ออกใบเสร็จสำเร็จ', 201);
    }

    /**
     * POST  ?controller=receipt&action=update
     * Update receipt (edit receipt_number, issued_date, etc.)
     * Past-year receipts: admin only
     */
    public function update(): void
    {
        $this->requirePost();
        $input = $this->input();

        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id ใบเสร็จ');

        $receipts = $this->model('ReceiptModel');
        $receipt = $receipts->find($id);
        if (!$receipt) Response::error('ไม่พบใบเสร็จ', 404);

        $isAdmin = $this->currentUser['role'] === 'admin';

        // Year-based restriction: past year → admin only
        $issuedYear = (int)date('Y', strtotime($receipt['issued_date'])) + 543;
        $currentBuddhistYear = (int)date('Y') + 543;
        if ($issuedYear < $currentBuddhistYear && !$isAdmin) {
            Response::error('ใบเสร็จปีก่อนหน้า เฉพาะ admin เท่านั้นที่แก้ไขได้', 403);
        }

        // Members need finance permission
        if (!$isAdmin) {
            $fm = $this->model('FinanceManagerModel');
            $perms = $fm->getByUserId((int)$this->currentUser['id']);
            if (!$perms || !$perms['is_active']) {
                Response::error('คุณไม่มีสิทธิ์แก้ไขใบเสร็จ', 403);
            }
        }

        $updateData = [];
        if (isset($input['receipt_number'])) {
            $updateData['receipt_number'] = $input['receipt_number'];
        }

        // Handle issued_date change → recalculate book_number
        if (!empty($input['issued_date'])) {
            $newIssuedDate = $input['issued_date'];
            $updateData['issued_date'] = $newIssuedDate;
            $settings = $this->model('SettingsModel');
            $prefix = trim($settings->get('receipt_book_number', SITE_NAME_SHORT));
            $newBookNum = $receipts::buildBookNumber($prefix, $newIssuedDate);
            $updateData['book_number'] = $newBookNum;
        }

        // Check for duplicate receipt_number + book_number
        if (isset($updateData['receipt_number']) || isset($updateData['book_number'])) {
            $checkBookNum = $updateData['book_number'] ?? $receipt['book_number'];
            $checkReceiptNum = $updateData['receipt_number'] ?? $receipt['receipt_number'];
            $duplicate = $receipts->findDuplicate($checkBookNum, $checkReceiptNum, $id);
            if ($duplicate) {
                Response::error('เลขที่ใบเสร็จ ' . $checkReceiptNum . ' ในเล่ม ' . $checkBookNum . ' ซ้ำกับใบเสร็จที่มีอยู่แล้ว');
            }
        }

        if (isset($input['payer_name'])) {
            $updateData['payer_name'] = $input['payer_name'];
        }
        if (isset($input['payer_address'])) {
            $updateData['payer_address'] = $input['payer_address'];
        }
        if (isset($input['description'])) {
            $updateData['description'] = $input['description'];
        }
        if (isset($input['amount_text'])) {
            $updateData['amount_text'] = $input['amount_text'];
        }

        $syncProfileAddress = !empty($input['sync_profile_address']);
        $addressSource = strtolower(trim((string)($input['address_source'] ?? 'work')));
        $addressSource = in_array($addressSource, ['current', 'home', 'personal'], true) ? 'current' : 'work';

        if (empty($updateData)) {
            Response::error('ไม่มีข้อมูลที่ต้องแก้ไข');
        }

        $receipts->update($updateData, ['id' => $id]);

        if ($syncProfileAddress && !empty($receipt['user_id']) && array_key_exists('payer_address', $updateData)) {
            $this->syncReceiptAddressToProfile((int)$receipt['user_id'], $updateData['payer_address'], $addressSource);
        }

        Auth::logActivity(
            (int)$this->currentUser['id'], 'update_receipt', 'receipt',
            "แก้ไขใบเสร็จ #" . ($receipt['receipt_number'] ?? $id),
            $id, 'receipt'
        );

        Response::success(null, 'แก้ไขใบเสร็จสำเร็จ');
    }

    private function syncReceiptAddressToProfile(int $userId, $payerAddress, string $addressSource): void
    {
        $users = $this->model('UserModel');
        $member = $users->find($userId);
        if (!$member) {
            return;
        }

        $addr = null;
        if (is_string($payerAddress) && $payerAddress !== '') {
            $decoded = json_decode($payerAddress, true);
            $addr = is_array($decoded) ? $decoded : null;
        } elseif (is_array($payerAddress)) {
            $addr = $payerAddress;
        }

        if (!$addr || !is_array($addr)) {
            return;
        }

        $targetField = $addressSource === 'current' ? 'home_address' : 'work_address';
        $existingRaw = $member[$targetField] ?? null;
        $existing = [];
        if (is_string($existingRaw) && $existingRaw !== '') {
            $existingDecoded = json_decode($existingRaw, true);
            if (is_array($existingDecoded)) {
                $existing = $existingDecoded;
            }
        } elseif (is_array($existingRaw)) {
            $existing = $existingRaw;
        }

        $detail = trim((string)($addr['detail'] ?? ''));
        $detailOriginal = $detail;
        $no = '';
        $moo = '';
        $soi = '';
        $road = '';

        if ($detailOriginal !== '') {
            if (preg_match('/\sหมู่\s+([^\s].*?)(?=\sซอย\s|\sถนน\s|$)/u', $detailOriginal, $m)) {
                $moo = trim($m[1]);
            }
            if (preg_match('/\sซอย\s+([^\s].*?)(?=\sถนน\s|$)/u', $detailOriginal, $m)) {
                $soi = trim($m[1]);
            }
            if (preg_match('/\sถนน\s+(.+)$/u', $detailOriginal, $m)) {
                $road = trim($m[1]);
            }

            $no = preg_replace('/\sหมู่\s+.*$/u', '', $detailOriginal);
            $no = preg_replace('/\sซอย\s+.*$/u', '', $no);
            $no = preg_replace('/\sถนน\s+.*$/u', '', $no);
            $no = trim((string)$no);
        }

        $profileAddress = [
            'address' => $existing['address'] ?? $no,
            'detail' => $detail,
            'no' => $no,
            'moo' => $moo,
            'soi' => $soi,
            'road' => $road,
            'subdistrict' => trim((string)($addr['subdistrict'] ?? '')),
            'district' => trim((string)($addr['district'] ?? '')),
            'province' => trim((string)($addr['province'] ?? '')),
            'zipcode' => trim((string)($addr['zipcode'] ?? '')),
        ];

        $updateMemberData = [
            $targetField => json_encode($profileAddress, JSON_UNESCAPED_UNICODE),
        ];

        if (array_key_exists('organization', $addr)) {
            $organization = trim((string)($addr['organization'] ?? ''));
            if ($organization !== '' || $addressSource === 'work') {
                $updateMemberData['school_organization'] = $organization;
            }
        }

        $users->update($updateMemberData, ['id' => $userId]);
    }

    /**
     * GET  ?controller=receipt&action=check-duplicate
     * Check if receipt_number + year already exists
     */
    public function checkDuplicate(): void
    {
        $receiptNumber = $this->query('receipt_number');
        $issuedDate = $this->query('issued_date');
        $excludeId = (int)($this->query('exclude_id') ?: 0);

        if (!$receiptNumber || !$issuedDate) {
            Response::error('กรุณาระบุเลขที่ใบเสร็จและวันที่');
        }

        $settings = $this->model('SettingsModel');
        $prefix = trim($settings->get('receipt_book_number', SITE_NAME_SHORT));

        $receipts = $this->model('ReceiptModel');
        $bookNum = $receipts::buildBookNumber($prefix, $issuedDate);

        $duplicate = $receipts->findDuplicate($bookNum, $receiptNumber, $excludeId);

        if ($duplicate) {
            Response::success([
                'duplicate'    => true,
                'existing_id'  => $duplicate['id'],
                'payer_name'   => $duplicate['payer_name'] ?? '',
                'book_number'  => $duplicate['book_number'],
            ]);
        } else {
            Response::success(['duplicate' => false]);
        }
    }

    /**
     * GET  ?controller=receipt&action=reference-data
     * Load reference/source data for a receipt (membership fee or activity registration)
     * Used by the "โหลดข้อมูลจากระบบ" button to populate receipt fields
     */
    public function referenceData(): void
    {
        $receiptType = trim($this->query('receipt_type') ?? '');
        $referenceId = (int)($this->query('reference_id') ?? 0);

        if (!$receiptType || !$referenceId) {
            Response::error('กรุณาระบุ receipt_type และ reference_id');
        }

        $receipts = $this->model('ReceiptModel');
        $refData = $receipts->getReferenceData($receiptType, $referenceId);

        if (!$refData) {
            Response::error('ไม่พบข้อมูลอ้างอิง', 404);
        }

        // Resolve member_type label
        if (!empty($refData['member_type'])) {
            $memberTypes = $this->model('MemberTypeModel');
            $mt = $memberTypes->findByKey($refData['member_type']);
            $refData['member_type_label'] = $mt ? $mt['label'] : $refData['member_type'];
        }

        Response::success($refData);
    }

    /**
     * GET  ?controller=receipt&action=search-reference
     * Search for membership fees or activity registrations to reference
     * Used when creating a receipt and want to link it to a source
     */
    public function searchReference(): void
    {
        $type = trim($this->query('type') ?? '');
        $q    = trim($this->query('q') ?? '');

        if (!$type) Response::error('กรุณาระบุประเภท (membership_fee / activity_fee)');

        $receipts = $this->model('ReceiptModel');
        $results = [];

        if ($type === 'membership_fee') {
            $results = $receipts->searchMembershipFeeReferences($q);
        } elseif ($type === 'activity_fee') {
            $results = $receipts->searchActivityFeeReferences($q);
        }

        Response::success($results);
    }

    /**
     * POST  ?controller=receipt&action=update-my-address
     * Member updates payer_address on their own receipt (address only)
     */
    public function updateMyAddress(): void
    {
        $this->requirePost();
        $input = $this->input();

        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id ใบเสร็จ');

        $receipts = $this->model('ReceiptModel');
        $receipt = $receipts->find($id);
        if (!$receipt) Response::error('ไม่พบใบเสร็จ', 404);

        // Must be own receipt
        if ((int)$receipt['user_id'] !== (int)$this->currentUser['id']) {
            Response::error('คุณไม่มีสิทธิ์แก้ไขใบเสร็จนี้', 403);
        }

        $payerAddress = $input['payer_address'] ?? null;
        if ($payerAddress === null) {
            Response::error('กรุณาระบุที่อยู่');
        }

        $receipts->update(['payer_address' => $payerAddress], ['id' => $id]);

        // Optional: sync the edited address back to the member's own profile (same approach as admin edit)
        $syncProfileAddress = !empty($input['sync_profile_address']);
        if ($syncProfileAddress) {
            $addressSource = strtolower(trim((string)($input['address_source'] ?? 'work')));
            $addressSource = in_array($addressSource, ['current', 'home', 'personal'], true) ? 'current' : 'work';
            $this->syncReceiptAddressToProfile((int)$receipt['user_id'], $payerAddress, $addressSource);
        }

        Auth::logActivity(
            (int)$this->currentUser['id'], 'update_receipt_address', 'receipt',
            "แก้ไขที่อยู่ใบเสร็จ #" . ($receipt['receipt_number'] ?? $id),
            $id, 'receipt'
        );

        Response::success(null, 'แก้ไขที่อยู่ใบเสร็จสำเร็จ');
    }
}
