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

        // Only owner or admin
        $isAdmin = $this->currentUser['role'] === 'admin';
        if ($receipt['user_id'] != $this->currentUser['id'] && !$isAdmin) {
            Response::error('ไม่มีสิทธิ์ดูใบเสร็จนี้', 403);
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
     * Paginated receipts (admin)
     */
    public function list(): void
    {
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
        $nextNum = $receipts->getNextNumber($bookNum);

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
            $payerAddress = FeeController::buildPayerAddress($user);
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

        $id = $receipts->createReceipt($receiptData);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'create_receipt', 'receipt',
            "ออกใบเสร็จ: {$input['title']} ให้ {$payerName}",
            $id, 'receipt'
        );

        Response::success(['id' => $id], 'ออกใบเสร็จสำเร็จ', 201);
    }

    /**
     * POST  ?controller=receipt&action=update
     * Update receipt (edit receipt_number, etc.)
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

        if (empty($updateData)) {
            Response::error('ไม่มีข้อมูลที่ต้องแก้ไข');
        }

        $receipts->update($updateData, ['id' => $id]);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'update_receipt', 'receipt',
            "แก้ไขใบเสร็จ #" . ($receipt['receipt_number'] ?? $id),
            $id, 'receipt'
        );

        Response::success(null, 'แก้ไขใบเสร็จสำเร็จ');
    }
}
