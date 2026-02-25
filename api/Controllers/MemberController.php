<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Auth;
use App\Models\UserModel;

/**
 * MemberController
 * Uses: UserModel, ActivityRegistrationModel, MemberStatisticModel
 */
class MemberController extends Controller
{
    /** All extended profile fields (full_name is auto-generated, not editable directly) */
    private const PROFILE_FIELDS = [
        'prefix','phone','school_organization','position','bio','profile_image',
        'national_id','first_name','last_name','birth_date',
        'home_address','work_address','education_area','region','work_phone','member_number'
    ];
    /**
     * GET  ?controller=member&action=profile[&id=X]
     */
    public function profile(): void
    {
        $userId = (int)($this->query('id') ?: $this->currentUser['id']);

        if ($userId !== (int)$this->currentUser['id'] && $this->currentUser['role'] !== 'admin') {
            Response::error('คุณไม่มีสิทธิ์ดูข้อมูลผู้ใช้อื่น', 403);
        }

        $users   = $this->model('UserModel');
        $profile = $users->find($userId, $users::PROFILE_COLUMNS);
        if (!$profile) Response::error('ไม่พบข้อมูลผู้ใช้', 404);

        // Format member_number for display
        if (!empty($profile['member_number'])) {
            $settings = $this->model('SettingsModel');
            $prefix = $settings->get('member_number_prefix', '');
            $digits = (int)$settings->get('member_number_digits', '4');
            $profile['member_number'] = UserModel::formatMemberNumber($profile['member_number'], $prefix, $digits);
        }

        $regs = $this->model('ActivityRegistrationModel');
        $profile['activity_count'] = $regs->count(['user_id' => $userId, 'status' => 'approved']);

        // Check if user registered via Google
        $fullUser = $users->find($userId);
        $profile['is_google_user'] = !empty($fullUser['google_id']);

        Response::success($profile);
    }

    /**
     * POST  ?controller=member&action=update
     */
    public function update(): void
    {
        $this->requirePost();
        $input  = $this->input();
        $auth   = new Auth();
        $users  = $this->model('UserModel');
        $userId = (int)$this->currentUser['id'];

        if (isset($input['user_id']) && $this->currentUser['role'] === 'admin') {
            $userId = (int)$input['user_id'];
        }

        $data = [];
        foreach (self::PROFILE_FIELDS as $f) {
            if (isset($input[$f])) {
                $val = $input[$f];
                // JSON fields — accept array or string
                if (in_array($f, ['home_address','work_address'])) {
                    $data[$f] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
                } else {
                    $data[$f] = is_string($val) ? trim($val) : $val;
                }
            }
        }

        // admin-only fields
        if ($this->currentUser['role'] === 'admin') {
            foreach (['role','member_type','status'] as $f) {
                if (isset($input[$f])) {
                    $old = $users->find($userId, [$f]);
                    $data[$f] = $input[$f];
                    if ($f === 'member_type') {
                        $auth->logAction($userId, 'type_changed', $old[$f], $input[$f], null, (int)$this->currentUser['id']);
                    }
                }
            }
        }

        // password change
        if (!empty($input['new_password'])) {
            if (strlen($input['new_password']) < 6) Response::error('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
            if ($userId === (int)$this->currentUser['id'] && !empty($input['current_password'])) {
                $cur = $users->find($userId, ['password']);
                if (!Auth::verifyPassword($input['current_password'], $cur['password'])) {
                    Response::error('รหัสผ่านปัจจุบันไม่ถูกต้อง');
                }
            }
            $data['password'] = Auth::hashPassword($input['new_password']);
        }

        // Auto-generate full_name จาก prefix + first_name + last_name
        if (isset($data['first_name']) || isset($data['last_name']) || isset($data['prefix'])) {
            $existing = $users->find($userId, ['prefix', 'first_name', 'last_name']);
            $prefix    = $data['prefix']     ?? $existing['prefix']     ?? '';
            $firstName = $data['first_name'] ?? $existing['first_name'] ?? '';
            $lastName  = $data['last_name']  ?? $existing['last_name']  ?? '';
            $data['full_name'] = AuthController::buildFullName($prefix, $firstName, $lastName);
        }

        // Google users cannot change email (unless admin is editing)
        if (isset($data['email']) && $this->currentUser['role'] !== 'admin') {
            $fullUser = $users->find($userId);
            if (!empty($fullUser['google_id'])) {
                unset($data['email']);
            }
        }

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        // Normalize & check member_number uniqueness
        if (isset($data['member_number']) && $data['member_number'] !== '') {
            $settings = $this->model('SettingsModel');
            $digits = (int)$settings->get('member_number_digits', '4');
            $data['member_number'] = UserModel::normalizeMemberNumber($data['member_number'], $digits);
            if ($data['member_number'] === '') {
                unset($data['member_number']);
            } elseif ($users->memberNumberExists($data['member_number'], $userId)) {
                Response::error('เลขสมาชิก "' . $data['member_number'] . '" ถูกใช้แล้ว กรุณาระบุเลขอื่น');
            }
        }

        $users->update($data, ['id' => $userId]);
        $auth->logAction($userId, 'profile_updated', null, json_encode(array_keys($data)), null, (int)$this->currentUser['id']);

        $changedFields = implode(', ', array_keys($data));
        $targetUser = $users->find($userId, ['full_name']);
        $isAdminEdit = $userId !== (int)$this->currentUser['id'];
        Auth::logActivity(
            (int)$this->currentUser['id'],
            $isAdminEdit ? 'update_member' : 'update_profile',
            'member',
            ($isAdminEdit ? "แก้ไขข้อมูลสมาชิก: {$targetUser['full_name']}" : 'แก้ไขโปรไฟล์') . " ({$changedFields})",
            $userId, 'user'
        );

        $updated = $users->find($userId, $users::PROFILE_COLUMNS);
        // Format member_number for display
        if (!empty($updated['member_number'])) {
            if (!isset($settings)) $settings = $this->model('SettingsModel');
            $pfx = $settings->get('member_number_prefix', '');
            $dgt = (int)$settings->get('member_number_digits', '4');
            $updated['member_number'] = UserModel::formatMemberNumber($updated['member_number'], $pfx, $dgt);
        }
        Response::success($updated, 'อัปเดตข้อมูลสำเร็จ');
    }

    /**
     * GET  ?controller=member&action=list
     */
    public function list(): void
    {
        $users  = $this->model('UserModel');
        $result = $users->getFilteredList([
            'status'      => $this->query('status'),
            'member_type' => $this->query('member_type'),
            'role'        => $this->query('role'),
            'search'      => $this->query('search'),
        ], $this->getPage(), $this->getPerPage());

        // Format member_number for display
        $settings = $this->model('SettingsModel');
        $prefix = $settings->get('member_number_prefix', '');
        $digits = (int)$settings->get('member_number_digits', '4');
        foreach ($result['data'] as &$row) {
            if (!empty($row['member_number'])) {
                $row['member_number'] = UserModel::formatMemberNumber($row['member_number'], $prefix, $digits);
            }
        }
        unset($row);

        Response::paginated($result['data'], $result['total'], $result['page'], $result['per_page']);
    }

    /**
     * POST  ?controller=member&action=approve
     * body: { user_id, action, reason }
     */
    public function approve(): void
    {
        $this->requirePost();
        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        $act    = $input['action'] ?? '';
        $reason = $input['reason'] ?? '';

        if (!$userId) Response::error('กรุณาระบุ user_id');

        $users = $this->model('UserModel');
        $target = $users->find($userId);
        if (!$target) Response::error('ไม่พบผู้ใช้', 404);

        $auth    = new Auth();
        $adminId = (int)$this->currentUser['id'];
        $adminName = $this->currentUser['full_name'];

        switch ($act) {
            case 'approve':
                // Check if member type requires fee payment
                $memberType = $target['member_type'] ?? 'ordinary';
                $settings = $this->model('SettingsModel');
                $feeMode = $settings->get("membership_fee_mode_{$memberType}", 'none');

                if ($feeMode !== 'none') {
                    // Check if fee has been paid & approved
                    $fees = $this->model('MembershipFeeModel');
                    $buddhistYear = (int)date('Y') + 543;
                    $hasPaidFee = false;

                    if ($feeMode === 'onetime') {
                        $allFees = $fees->getUserFees($userId);
                        foreach ($allFees as $f) {
                            if ($f['fee_type'] === 'onetime' && $f['status'] === 'paid' && $f['approved_at']) {
                                $hasPaidFee = true;
                                break;
                            }
                        }
                    } else {
                        $yearFee = $fees->getUserYearFee($userId, $buddhistYear);
                        if ($yearFee && $yearFee['status'] === 'paid' && $yearFee['approved_at']) {
                            $hasPaidFee = true;
                        }
                    }

                    if (!$hasPaidFee) {
                        Response::error('สมาชิกประเภทนี้ต้องชำระค่าธรรมเนียมและได้รับอนุมัติก่อน จึงจะอนุมัติสมาชิกได้');
                    }
                }

                // Handle member number — normalize to numeric-only
                $memberNumber = trim($input['member_number'] ?? '');
                if ($memberNumber) {
                    $mnDigits = (int)$settings->get('member_number_digits', '4');
                    $memberNumber = UserModel::normalizeMemberNumber($memberNumber, $mnDigits);
                }
                if ($memberNumber && $users->memberNumberExists($memberNumber, $userId)) {
                    Response::error('เลขสมาชิก "' . $memberNumber . '" ถูกใช้แล้ว กรุณาระบุเลขอื่น');
                }

                $updateData = [
                    'status' => 'active',
                    'approved_by' => $adminId,
                    'approved_at' => date('Y-m-d H:i:s'),
                ];
                if ($memberNumber) {
                    $updateData['member_number'] = $memberNumber;
                }
                $users->update($updateData, ['id' => $userId]);
                $auth->logAction($userId, 'approved', 'pending', 'active', "อนุมัติโดย {$adminName}" . ($memberNumber ? " เลขสมาชิก: {$memberNumber}" : ''), $adminId);
                Auth::logActivity($adminId, 'approve_member', 'member', "อนุมัติสมาชิก: {$target['full_name']}" . ($memberNumber ? " เลขสมาชิก: {$memberNumber}" : ''), $userId, 'user');
                Response::success(null, 'อนุมัติสมาชิกสำเร็จ');
                break;
            case 'reject':
                $users->update(['status'=>'cancelled','cancel_reason'=>$reason], ['id'=>$userId]);
                $auth->logAction($userId, 'cancelled', $target['status'], 'cancelled', "ปฏิเสธ: {$reason}", $adminId);
                Auth::logActivity($adminId, 'reject_member', 'member', "ปฏิเสธสมาชิก: {$target['full_name']} - {$reason}", $userId, 'user');
                Response::success(null, 'ปฏิเสธสมาชิกสำเร็จ');
                break;
            case 'suspend':
                $users->update(['status'=>'suspended','cancel_reason'=>$reason], ['id'=>$userId]);
                $auth->logAction($userId, 'suspended', $target['status'], 'suspended', "ระงับ: {$reason}", $adminId);
                Auth::logActivity($adminId, 'suspend_member', 'member', "ระงับสมาชิก: {$target['full_name']} - {$reason}", $userId, 'user');
                Response::success(null, 'ระงับสมาชิกสำเร็จ');
                break;
            case 'cancel':
                $users->update(['status'=>'cancelled','cancelled_at'=>date('Y-m-d H:i:s'),'cancel_reason'=>$reason], ['id'=>$userId]);
                $auth->logAction($userId, 'cancelled', $target['status'], 'cancelled', "ยกเลิก: {$reason}", $adminId);
                Auth::logActivity($adminId, 'cancel_member', 'member', "ยกเลิกสมาชิก: {$target['full_name']} - {$reason}", $userId, 'user');
                Response::success(null, 'ยกเลิกสมาชิกสำเร็จ');
                break;
            case 'activate':
            case 'reactivate':
                $users->update(['status'=>'active','cancelled_at'=>null,'cancel_reason'=>null], ['id'=>$userId]);
                $auth->logAction($userId, 'reactivated', $target['status'], 'active', 'เปิดใช้งานใหม่', $adminId);
                Auth::logActivity($adminId, 'activate_member', 'member', "เปิดใช้งานสมาชิก: {$target['full_name']}", $userId, 'user');
                Response::success(null, 'เปิดใช้งานสมาชิกสำเร็จ');
                break;
            default:
                Response::error('การดำเนินการไม่ถูกต้อง');
        }
    }

    /**
     * GET  ?controller=member&action=next-member-number
     * Get next available member number
     */
    public function nextMemberNumber(): void
    {
        $settings = $this->model('SettingsModel');
        $prefix = $settings->get('member_number_prefix', '');
        $digits = (int)$settings->get('member_number_digits', '4');
        $users = $this->model('UserModel');
        $nextNumber = $users->getNextMemberNumber($digits);
        Response::success([
            'next_number' => $nextNumber,
            'formatted'   => UserModel::formatMemberNumber($nextNumber, $prefix, $digits),
            'prefix'      => $prefix,
            'digits'      => $digits,
        ]);
    }

    /**
     * GET  ?controller=member&action=check-fee-status&user_id=X
     * Check if member has approved fees (for approval flow)
     */
    public function checkFeeStatus(): void
    {
        $userId = (int)$this->query('user_id');
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $users = $this->model('UserModel');
        $target = $users->find($userId);
        if (!$target) Response::error('ไม่พบผู้ใช้', 404);

        $memberType = $target['member_type'] ?? 'ordinary';
        $settings = $this->model('SettingsModel');
        $feeMode = $settings->get("membership_fee_mode_{$memberType}", 'none');

        // Get fee amount from settings
        $feeAmount = 0;
        if ($feeMode !== 'none') {
            $feeAmount = (float)$settings->get("membership_fee_{$memberType}", '0');
        }

        $result = [
            'member_type' => $memberType,
            'fee_mode' => $feeMode,
            'requires_fee' => $feeMode !== 'none',
            'fee_approved' => false,
            'fee_amount' => $feeAmount,
            'fee_id' => null,
            'fee_status' => null,
            'fee_paid_at' => null,
            'fee_payment_slip' => null,
            'has_fee_record' => false,
        ];

        if ($feeMode !== 'none') {
            $fees = $this->model('MembershipFeeModel');
            $buddhistYear = (int)date('Y') + 543;

            if ($feeMode === 'onetime') {
                $allFees = $fees->getUserFees($userId);
                foreach ($allFees as $f) {
                    if ($f['fee_type'] === 'onetime') {
                        $result['fee_id'] = (int)$f['id'];
                        $result['fee_status'] = $f['status'];
                        $result['fee_paid_at'] = $f['paid_at'];
                        $result['fee_payment_slip'] = $f['payment_slip'] ?? null;
                        $result['fee_amount'] = (float)$f['amount'];
                        $result['has_fee_record'] = true;
                        if ($f['status'] === 'paid' && $f['approved_at']) {
                            $result['fee_approved'] = true;
                        }
                        break;
                    }
                }
            } else {
                $yearFee = $fees->getUserYearFee($userId, $buddhistYear);
                if ($yearFee) {
                    $result['fee_id'] = (int)$yearFee['id'];
                    $result['fee_status'] = $yearFee['status'];
                    $result['fee_paid_at'] = $yearFee['paid_at'];
                    $result['fee_payment_slip'] = $yearFee['payment_slip'] ?? null;
                    $result['fee_amount'] = (float)$yearFee['amount'];
                    $result['has_fee_record'] = true;
                    if ($yearFee['status'] === 'paid' && $yearFee['approved_at']) {
                        $result['fee_approved'] = true;
                    }
                }
            }
        }

        // Get next member number (numeric only) + prefix for display
        $prefix = $settings->get('member_number_prefix', '');
        $digits = (int)$settings->get('member_number_digits', '4');
        $result['next_member_number'] = $users->getNextMemberNumber($digits);
        $result['member_number_prefix'] = $prefix;
        $result['member_number_digits'] = $digits;
        $rawMn = $target['member_number'] ?? '';
        $result['current_member_number'] = $rawMn ? UserModel::formatMemberNumber($rawMn, $prefix, $digits) : '';

        Response::success($result);
    }

    /**
     * POST  ?controller=member&action=confirm-fee-payment
     * Admin confirms that the member has paid the fee (create fee record if needed, mark paid+approved)
     */
    public function confirmFeePayment(): void
    {
        $this->requirePost();
        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $users = $this->model('UserModel');
        $target = $users->find($userId);
        if (!$target) Response::error('ไม่พบผู้ใช้', 404);

        $memberType = $target['member_type'] ?? 'ordinary';
        $settings   = $this->model('SettingsModel');
        $feeMode    = $settings->get("membership_fee_mode_{$memberType}", 'none');

        if ($feeMode === 'none') {
            Response::error('ประเภทสมาชิกนี้ไม่ต้องชำระค่าธรรมเนียม');
        }

        $feeAmount   = (float)$settings->get("membership_fee_{$memberType}", '0');
        $feeType     = $feeMode === 'onetime' ? 'onetime' : 'annual';
        $buddhistYear = (int)date('Y') + 543;

        $fees  = $this->model('MembershipFeeModel');
        $feeId = $fees->upsertFee($userId, $buddhistYear, $feeAmount, $feeType);

        // Mark as paid + approved by admin
        $adminId = (int)$this->currentUser['id'];
        $now     = date('Y-m-d H:i:s');
        $today   = date('Y-m-d');

        $fees->update([
            'status'       => 'paid',
            'paid_at'      => $now,
            'approved_by'  => $adminId,
            'approved_at'  => $now,
            'received_date'=> $today,
            'note'         => 'ยืนยันโดยผู้ดูแลระบบ (จากหน้าอนุมัติสมาชิก)',
        ], ['id' => $feeId]);

        // Auto-generate receipt
        $fee = $fees->find($feeId);
        if ($fee) {
            $receipts = $this->model('ReceiptModel');
            $existing = $receipts->findByReference('membership_fee', $feeId);
            if (!$existing) {
                $feeLabel = $feeType === 'onetime' ? 'ครั้งเดียว' : "ปี {$buddhistYear}";
                $description = "ค่าธรรมเนียมสมาชิก ({$feeLabel})";
                $receipts->createReceipt([
                    'user_id'       => $userId,
                    'receipt_type'  => 'membership_fee',
                    'reference_id'  => $feeId,
                    'title'         => 'ค่าธรรมเนียมสมาชิก',
                    'payer_name'    => $target['full_name'],
                    'payer_address' => null,
                    'description'   => $description,
                    'amount'        => $feeAmount,
                    'received_by'   => $settings->get('signature_name', ''),
                    'issued_date'   => $today,
                ]);
            }
        }

        // Auto-create finance transaction for the confirmed fee
        if ($fee) {
            $txnModel = $this->model('FinanceTransactionModel');
            $referenceNo = 'FEE-' . $feeId;
            $existingTxn = $txnModel->findBy(['reference_no' => $referenceNo]);
            if (!$existingTxn) {
                $catModel = $this->model('FinanceCategoryModel');
                $feeCategory = $catModel->findBy(['name' => 'ค่าธรรมเนียมสมาชิก', 'type' => 'income']);
                if ($feeCategory) {
                    $memberTypeLabels = [
                        'ordinary'  => 'สามัญ',
                        'associate' => 'วิสามัญ',
                        'affiliate' => 'สมทบ',
                        'honorary'  => 'กิตติมศักดิ์',
                    ];
                    $memberTypeText = isset($memberTypeLabels[$memberType]) ? ' (' . $memberTypeLabels[$memberType] . ')' : '';
                    $feeLabel = $feeType === 'onetime' ? 'ครั้งเดียว' : "ปี {$buddhistYear}";

                    $txnModel->create([
                        'category_id'      => (int)$feeCategory['id'],
                        'type'             => 'income',
                        'title'            => "ค่าธรรมเนียมสมาชิก: {$target['full_name']}{$memberTypeText}",
                        'description'      => "ค่าธรรมเนียมสมาชิก ({$feeLabel})",
                        'amount'           => $feeAmount,
                        'transaction_date' => $today,
                        'reference_no'     => $referenceNo,
                        'created_by'       => $adminId,
                        'status'           => 'approved',
                    ]);
                }
            }
        }

        Auth::logActivity($adminId, 'confirm_fee_payment', 'fee',
            "ยืนยันชำระค่าธรรมเนียม: {$target['full_name']} จำนวน " . number_format($feeAmount, 2) . " บาท",
            $feeId, 'fee');

        Response::success(['fee_id' => $feeId, 'amount' => $feeAmount], 'ยืนยันการชำระค่าธรรมเนียมสำเร็จ');
    }

    /**
     * POST  ?controller=member&action=create
     * Admin creates a new member directly (auto-approved)
     */
    public function create(): void
    {
        $this->requirePost();
        if ($this->currentUser['role'] !== 'admin') Response::error('ไม่มีสิทธิ์', 403);

        $input = $this->input();
        $users = $this->model('UserModel');

        // Required: first_name (+ last_name)
        $prefix    = trim($input['prefix'] ?? '');
        $firstName = trim($input['first_name'] ?? '');
        $lastName  = trim($input['last_name'] ?? '');
        if (!$firstName) Response::error('กรุณากรอกชื่อ');
        $fullName = AuthController::buildFullName($prefix, $firstName, $lastName);

        // Auto-generate username if not provided
        $username = trim($input['username'] ?? '');
        if (!$username) {
            $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
            if (strlen($base) < 3) $base = 'member';
            $username = $base . rand(100, 9999);
            while ($users->usernameExists($username)) {
                $username = $base . rand(100, 9999);
            }
        } else {
            if ($users->usernameExists($username)) Response::error('ชื่อผู้ใช้นี้ถูกใช้แล้ว');
        }

        // Email — optional for admin-created
        $email = trim($input['email'] ?? '');
        if ($email) {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Response::error('รูปแบบอีเมลไม่ถูกต้อง');
            if ($users->emailExists($email)) Response::error('อีเมลนี้ถูกใช้แล้ว');
        } else {
            $email = $username . '@temp.local';
            while ($users->emailExists($email)) {
                $email = $username . rand(10,99) . '@temp.local';
            }
        }

        $memberType = $input['member_type'] ?? 'ordinary';
        if (!in_array($memberType, ['ordinary', 'associate', 'affiliate', 'honorary'])) {
            $memberType = 'ordinary';
        }

        $password = trim($input['password'] ?? '');
        $hashedPw = Auth::hashPassword($password ?: 'sdak' . rand(1000, 9999));

        $data = [
            'username'    => $username,
            'email'       => $email,
            'password'    => $hashedPw,
            'role'        => 'member',
            'member_type' => $memberType,
            'status'      => 'active',
            'full_name'   => $fullName,
            'prefix'      => trim($input['prefix'] ?? ''),
            'approved_by' => (int)$this->currentUser['id'],
            'approved_at' => date('Y-m-d H:i:s'),
        ];

        // Member number — normalize to numeric-only
        $memberNumber = trim($input['member_number'] ?? '');
        if ($memberNumber) {
            $settings = $this->model('SettingsModel');
            $mnDigits = (int)$settings->get('member_number_digits', '4');
            $memberNumber = UserModel::normalizeMemberNumber($memberNumber, $mnDigits);
            if ($memberNumber && $users->memberNumberExists($memberNumber)) {
                Response::error('เลขสมาชิก "' . $memberNumber . '" ถูกใช้แล้ว');
            }
            if ($memberNumber) $data['member_number'] = $memberNumber;
        }

        // Extra profile fields
        foreach (self::PROFILE_FIELDS as $f) {
            if (in_array($f, ['full_name', 'prefix', 'profile_image', 'member_number'])) continue;
            if (isset($input[$f]) && $input[$f] !== '') {
                $val = $input[$f];
                if (in_array($f, ['home_address', 'work_address'])) {
                    $data[$f] = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
                } else {
                    $data[$f] = is_string($val) ? trim($val) : $val;
                }
            }
        }

        $userId = $users->create($data);
        $auth = new Auth();
        Auth::logActivity(
            (int)$this->currentUser['id'], 'create_member', 'member',
            "เพิ่มสมาชิก: {$fullName}", (int)$userId, 'user'
        );

        Response::success(['user_id' => $userId], 'เพิ่มสมาชิกสำเร็จ', 201);
    }

    /**
     * POST  ?controller=member&action=delete
     * Admin deletes a member
     */
    public function delete(): void
    {
        $this->requirePost();
        if ($this->currentUser['role'] !== 'admin') Response::error('ไม่มีสิทธิ์', 403);

        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');
        if ($userId === (int)$this->currentUser['id']) Response::error('ไม่สามารถลบตัวเองได้');

        $users  = $this->model('UserModel');
        $target = $users->find($userId);
        if (!$target) Response::error('ไม่พบผู้ใช้', 404);
        if ($target['role'] === 'admin') Response::error('ไม่สามารถลบผู้ดูแลระบบได้');

        $users->delete(['id' => $userId]);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'delete_member', 'member',
            "ลบสมาชิก: {$target['full_name']} ({$target['username']})", $userId, 'user'
        );

        Response::success(null, 'ลบสมาชิกสำเร็จ');
    }

    /**
     * POST  ?controller=member&action=admin-reset-password
     * Admin resets a member's password
     */
    public function adminResetPassword(): void
    {
        $this->requirePost();
        if ($this->currentUser['role'] !== 'admin') Response::error('ไม่มีสิทธิ์', 403);

        $input    = $this->input();
        $userId   = (int)($input['user_id'] ?? 0);
        $password = $input['password'] ?? '';

        if (!$userId) Response::error('กรุณาระบุ user_id');
        if (strlen($password) < 6) Response::error('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');

        $users  = $this->model('UserModel');
        $target = $users->find($userId);
        if (!$target) Response::error('ไม่พบผู้ใช้', 404);

        $users->update(
            ['password' => Auth::hashPassword($password)],
            ['id' => $userId]
        );

        Auth::logActivity(
            (int)$this->currentUser['id'], 'admin_reset_password', 'member',
            "รีเซ็ตรหัสผ่านสมาชิก: {$target['full_name']} ({$target['username']})", $userId, 'user'
        );

        Response::success(null, 'รีเซ็ตรหัสผ่านสำเร็จ');
    }

    /**
     * POST  ?controller=member&action=import
     * Admin bulk import members from CSV/paste data
     */
    public function import(): void
    {
        $this->requirePost();
        if ($this->currentUser['role'] !== 'admin') Response::error('ไม่มีสิทธิ์', 403);

        $input   = $this->input();
        $members = $input['members'] ?? [];
        if (empty($members) || !is_array($members)) Response::error('ไม่มีข้อมูลสมาชิกที่จะนำเข้า');

        $users   = $this->model('UserModel');
        $adminId = (int)$this->currentUser['id'];
        $settings = $this->model('SettingsModel');
        $importDigits = (int)$settings->get('member_number_digits', '4');
        $success = 0;
        $failed  = 0;
        $errors  = [];

        foreach ($members as $i => $m) {
            $row = $i + 1;
            try {
                $prefix    = trim($m['prefix'] ?? '');
                $firstName = trim($m['first_name'] ?? '');
                $lastName  = trim($m['last_name'] ?? '');
                $fullName  = AuthController::buildFullName($prefix, $firstName, $lastName);
                if (!$fullName && !$firstName) {
                    $errors[] = "แถวที่ {$row}: ไม่มีชื่อ-นามสกุล";
                    $failed++;
                    continue;
                }

                // Auto username
                $username = trim($m['username'] ?? '');
                if (!$username) {
                    $base = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $fullName));
                    if (strlen($base) < 3) $base = 'member';
                    $username = $base . rand(100, 9999);
                    while ($users->usernameExists($username)) {
                        $username = $base . rand(100, 9999);
                    }
                } elseif ($users->usernameExists($username)) {
                    $errors[] = "แถวที่ {$row}: ชื่อผู้ใช้ '{$username}' ซ้ำ";
                    $failed++;
                    continue;
                }

                // Email
                $email = trim($m['email'] ?? '');
                if ($email) {
                    if ($users->emailExists($email)) {
                        $errors[] = "แถวที่ {$row}: อีเมล '{$email}' ซ้ำ";
                        $failed++;
                        continue;
                    }
                } else {
                    $email = $username . '@temp.local';
                    while ($users->emailExists($email)) {
                        $email = $username . rand(10, 99) . '@temp.local';
                    }
                }

                $memberType = trim($m['member_type'] ?? 'ordinary');
                // Map Thai type names to enum values
                $typeMap = [
                    'สามัญ' => 'ordinary', 'วิสามัญ' => 'associate',
                    'สมทบ' => 'affiliate', 'กิตติมศักดิ์' => 'honorary'
                ];
                if (isset($typeMap[$memberType])) $memberType = $typeMap[$memberType];
                if (!in_array($memberType, ['ordinary','associate','affiliate','honorary'])) {
                    $memberType = 'ordinary';
                }

                $data = [
                    'username'    => $username,
                    'email'       => $email,
                    'password'    => Auth::hashPassword('sdak' . rand(1000, 9999)),
                    'role'        => 'member',
                    'member_type' => $memberType,
                    'status'      => 'active',
                    'full_name'   => $fullName,
                    'prefix'      => trim($m['prefix'] ?? ''),
                    'first_name'  => $firstName,
                    'last_name'   => $lastName,
                    'approved_by' => $adminId,
                    'approved_at' => date('Y-m-d H:i:s'),
                ];

                // Member number — normalize to numeric-only
                $memberNumber = trim($m['member_number'] ?? '');
                if ($memberNumber) {
                    $memberNumber = UserModel::normalizeMemberNumber($memberNumber, $importDigits);
                    if ($memberNumber && $users->memberNumberExists($memberNumber)) {
                        $errors[] = "แถวที่ {$row}: เลขสมาชิก '{$memberNumber}' ซ้ำ";
                        $failed++;
                        continue;
                    }
                    if ($memberNumber) $data['member_number'] = $memberNumber;
                }

                // Optional fields
                $optionalFields = [
                    'phone','school_organization','position','national_id',
                    'birth_date','education_area','region','work_phone'
                ];
                foreach ($optionalFields as $f) {
                    if (!empty(trim($m[$f] ?? ''))) $data[$f] = trim($m[$f]);
                }

                // Address JSON fields
                foreach (['home_address', 'work_address'] as $af) {
                    if (isset($m[$af]) && is_array($m[$af])) {
                        $data[$af] = json_encode($m[$af], JSON_UNESCAPED_UNICODE);
                    }
                }

                $users->create($data);
                $success++;
            } catch (\Exception $e) {
                $errors[] = "แถวที่ {$row}: " . $e->getMessage();
                $failed++;
            }
        }

        Auth::logActivity(
            $adminId, 'import_members', 'member',
            "นำเข้าสมาชิก: สำเร็จ {$success} รายการ, ไม่สำเร็จ {$failed} รายการ"
        );

        Response::success([
            'success_count' => $success,
            'failed_count'  => $failed,
            'errors'        => $errors,
        ], "นำเข้าสำเร็จ {$success} รายการ" . ($failed > 0 ? ", ไม่สำเร็จ {$failed} รายการ" : ''));
    }

    /**
     * GET  ?controller=member&action=statistics
     */
    public function statistics(): void
    {
        $users = $this->model('UserModel');
        $stats = $this->model('MemberStatisticModel');

        $s = [
            'total_members'     => $users->count(['role' => 'member']),
            'active_members'    => $users->count(['status' => 'active', 'role' => 'member']),
            'pending_members'   => $users->count(['status' => 'pending', 'role' => 'member']),
            'cancelled_members' => $users->count(['status' => 'cancelled', 'role' => 'member']),
            'suspended_members' => $users->count(['status' => 'suspended', 'role' => 'member']),
            'by_type' => [
                'ordinary'  => $users->count(['member_type'=>'ordinary',  'status'=>'active', 'role'=>'member']),
                'associate' => $users->count(['member_type'=>'associate', 'status'=>'active', 'role'=>'member']),
                'affiliate' => $users->count(['member_type'=>'affiliate', 'status'=>'active', 'role'=>'member']),
                'honorary'  => $users->count(['member_type'=>'honorary',  'status'=>'active', 'role'=>'member']),
            ],
        ];

        // monthly (last 12 months)
        $monthly = [];
        for ($i = 11; $i >= 0; $i--) {
            $from  = date('Y-m-01', strtotime("-{$i} months"));
            $to    = date('Y-m-t 23:59:59', strtotime("-{$i} months"));
            $monthly[] = [
                'month'      => date('Y-m', strtotime("-{$i} months")),
                'registered' => $stats->countInRange('registered', $from, $to),
                'approved'   => $stats->countInRange('approved',   $from, $to),
                'cancelled'  => $stats->countInRange('cancelled',  $from, $to),
            ];
        }
        $s['monthly'] = $monthly;
        $s['recent_logs'] = $stats->recentLogs(20);

        $newsModel = $this->model('NewsModel');
        $actModel  = $this->model('ActivityModel');
        $s['total_news']          = $newsModel->count();
        $s['total_activities']    = $actModel->count();
        $s['upcoming_activities'] = $actModel->count(['start_date[>]'=>date('Y-m-d H:i:s'),'status'=>'open']);

        Response::success($s);
    }

    /* ================================================================== */
    /*  MEMBER: Notification counts for badge                             */
    /* ================================================================== */

    /**
     * GET  ?controller=member&action=notifications
     * Returns notification counts for the logged-in member
     */
    public function notifications(): void
    {
        $userId = (int)$this->currentUser['id'];
        $fees   = $this->model('MembershipFeeModel');
        $acts   = $this->model('ActivityModel');
        $regs   = $this->model('ActivityRegistrationModel');

        // 1) Unpaid / pending fees
        $unpaidFees = $fees->countUnpaid($userId);

        // 2) New activities (open, upcoming, that user hasn't registered for)
        $newActivities = $acts->countNewForUser($userId);

        // 3) Activity registrations pending approval
        $pendingRegistrations = $regs->countPendingForUser($userId);

        $total = $unpaidFees + $newActivities + $pendingRegistrations;

        Response::success([
            'total'       => $total,
            'unpaid_fees' => $unpaidFees,
            'new_activities'       => $newActivities,
            'pending_registrations' => $pendingRegistrations,
        ]);
    }
}
