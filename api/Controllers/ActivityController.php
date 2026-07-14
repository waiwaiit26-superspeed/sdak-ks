<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

/**
 * ActivityController
 * Uses: ActivityModel, ActivityRegistrationModel
 */
class ActivityController extends Controller
{
    // ── Sub-admin helper ─────────────────────────────────────────────────

    private function requireActivitiesAccess(string $permission): void
    {
        if (!$this->currentUser) Response::error('กรุณาเข้าสู่ระบบ', 401);
        if ($this->currentUser['role'] === 'admin') return;
        $sa = $this->model('SubAdminModel');
        if (!$sa->hasPermission((int)$this->currentUser['id'], 'activities', $permission)) {
            Response::error('คุณไม่มีสิทธิ์ดำเนินการนี้', 403);
        }
    }

    private function hasActivitiesManageAccess(): bool
    {
        if (!$this->currentUser) return false;
        if ($this->currentUser['role'] === 'admin') return true;
        $sa = $this->model('SubAdminModel');
        $uid = (int)$this->currentUser['id'];
        return $sa->hasPermission($uid, 'activities', 'create')
            || $sa->hasPermission($uid, 'activities', 'edit')
            || $sa->hasPermission($uid, 'activities', 'delete');
    }

    private function hasFinanceManageAccess(): bool
    {
        if (!$this->currentUser) return false;
        if ($this->currentUser['role'] === 'admin') return true;
        $fm = $this->model('FinanceManagerModel');
        $manager = $fm->getByUserId((int)$this->currentUser['id']);
        return (bool)($manager && !empty($manager['is_active']));
    }

    private function requireActivityOrFinanceManageAccess(): void
    {
        if ($this->hasActivitiesManageAccess() || $this->hasFinanceManageAccess()) {
            return;
        }
        Response::error('คุณไม่มีสิทธิ์เข้าถึงส่วนนี้', 403);
    }

    /* ------------------------------------------------------------------ */
    /*  PUBLIC                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * GET  ?controller=activity&action=list
     */
    public function list(): void
    {
        $activity = $this->model('ActivityModel');
        $isAdmin  = $this->currentUser && $this->currentUser['role'] === 'admin';

        // Sub-admin with any activities permission can see all statuses (like admin)
        if (!$isAdmin && $this->currentUser) {
            $sa = $this->model('SubAdminModel');
            $isAdmin = $sa->hasPermission((int)$this->currentUser['id'], 'activities', 'create')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'activities', 'edit')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'activities', 'delete');
        }

        $result = $activity->getList(
            [
                'status'   => $this->query('status'),
                'search'   => $this->query('search'),
                'upcoming' => $this->query('upcoming'),
                'past'     => $this->query('past'),
            ],
            $this->getPage(),
            $this->getPerPage(50),
            $isAdmin
        );

        // Attach registration info for logged-in user
        if ($this->currentUser && !empty($result['data'])) {
            $reg = $this->model('ActivityRegistrationModel');
            foreach ($result['data'] as &$item) {
                $myReg = $reg->findUserRegistration((int)$item['id'], (int)$this->currentUser['id']);
                $item['my_registration'] = $myReg ?: null;
                $item['registration_count'] = $reg->countByActivity((int)$item['id']);
                $item['approved_count'] = $reg->approvedCount((int)$item['id']);
            }
            unset($item);
        }

        Response::paginated($result['data'], $result['total'], $result['page'], $result['perPage']);
    }

    /**
     * GET  ?controller=activity&action=detail&id=X
     */
    public function detail(): void
    {
        $id = (int)$this->query('id');
        if (!$id) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        $item     = $activity->getDetail($id);
        if (!$item) Response::error('ไม่พบกิจกรรมที่ต้องการ', 404);

        $isAdmin = $this->currentUser && $this->currentUser['role'] === 'admin';
        // Sub-admin with activities permission can access draft/non-published activities
        if (!$isAdmin && $this->currentUser) {
            $sa = $this->model('SubAdminModel');
            $isAdmin = $sa->hasPermission((int)$this->currentUser['id'], 'activities', 'edit')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'activities', 'delete');
        }
        if (!in_array($item['status'], ['open', 'closed', 'cancelled']) && !$isAdmin) {
            Response::error('ไม่พบกิจกรรมที่ต้องการ', 404);
        }

        // Check visibility restrictions
        $isMember = $this->currentUser && in_array($this->currentUser['role'], ['member', 'admin']);
        if ($item['visibility'] === 'members_only' && !$isMember) {
            // Return limited info — hide description
            $item['is_restricted'] = true;
            $item['description'] = null;
            $item['fee_description'] = null;
        } elseif ($item['visibility'] === 'custom' && !$isMember) {
            $item['is_restricted'] = true;
            $item['restriction_text'] = $item['visibility_text'] ?? 'เฉพาะสมาชิกเท่านั้น';
            $item['description'] = null;
            $item['fee_description'] = null;
        } else {
            $item['is_restricted'] = false;
        }

        // Attach registration stats
        $reg = $this->model('ActivityRegistrationModel');
        $item['registration_count']  = $reg->countByActivity($id);
        $item['approved_count']      = $reg->approvedCount($id);

        // If logged-in user, check their registration
        if ($this->currentUser) {
            $myReg = $reg->findUserRegistration($id, $this->currentUser['id']);
            $item['my_registration'] = $myReg ?: null;
        }

        // Include registrations list if show_registrations is enabled and user is a member
        if (!empty($item['show_registrations']) && $isMember) {
            $item['registrations'] = $reg->getByActivity($id);
        }

        Response::success($item);
    }

    /* ------------------------------------------------------------------ */
    /*  ADMIN CRUD                                                        */
    /* ------------------------------------------------------------------ */

    /**
     * POST  ?controller=activity&action=create
     */
    public function create(): void
    {
        $this->requirePost();
        $this->requireActivitiesAccess('create');
        $input = $this->input();

        if (empty(trim($input['title'] ?? '')))      Response::error('กรุณากรอกชื่อกิจกรรม');
        if (empty(trim($input['start_date'] ?? '')))  Response::error('กรุณาระบุวันเริ่มต้น');

        $activity = $this->model('ActivityModel');

        $feeAmount = (float)($input['fee_amount'] ?? 0);
        $visibility = in_array($input['visibility'] ?? '', ['public','members_only','custom']) ? $input['visibility'] : 'public';
        $data = [
            'title'            => trim($input['title']),
            'description'      => $input['description'] ?? '',
            'location'         => $input['location'] ?? '',
            'start_date'       => $input['start_date'],
            'end_date'         => $input['end_date'] ?? null,
            'event_date'       => $input['event_date'] ?? null,
            'max_participants' => (int)($input['max_participants'] ?? 0) ?: null,
            'has_fee'          => $feeAmount > 0 ? 1 : 0,
            'fee_amount'       => $feeAmount,
            'fee_description'  => $input['fee_description'] ?? null,
            'cover_image'      => $input['cover_image'] ?? null,
            'created_by'       => $this->currentUser['id'],
            'status'           => in_array($input['status'] ?? '', ['draft','open','closed','cancelled']) ? $input['status'] : 'draft',
            'visibility'       => $visibility,
            'visibility_text'  => $visibility === 'custom' ? ($input['visibility_text'] ?? null) : null,
            'allowed_member_types' => !empty($input['allowed_member_types']) ? $input['allowed_member_types'] : null,
            'show_registrations' => !empty($input['show_registrations']) ? 1 : 0,
        ];

        $id = $activity->create($data);
        Auth::logActivity((int)$this->currentUser['id'], 'create', 'activity', "สร้างกิจกรรม: {$data['title']}", $id, 'activity');
        Response::success(['id' => $id], 'สร้างกิจกรรมสำเร็จ', 201);
    }

    /**
     * POST  ?controller=activity&action=update
     */
    public function update(): void
    {
        $this->requirePost();
        $this->requireActivitiesAccess('edit');
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        if (!$activity->has(['id' => $id])) Response::error('ไม่พบกิจกรรม', 404);

        $allowed = ['title','description','location','start_date','end_date','event_date',
                     'max_participants','has_fee','fee_amount','fee_description','cover_image','status',
                     'visibility','visibility_text','allowed_member_types','access_code','show_registrations'];
        $data = [];
        foreach ($allowed as $f) {
            if (isset($input[$f])) $data[$f] = $input[$f];
        }

        // Auto-set has_fee based on fee_amount
        if (isset($input['fee_amount'])) {
            $data['has_fee'] = (float)$input['fee_amount'] > 0 ? 1 : 0;
        }

        // Ensure max_participants null if 0
        if (isset($data['max_participants']) && (int)$data['max_participants'] === 0) {
            $data['max_participants'] = null;
        }

        // Validate visibility
        if (isset($data['visibility']) && !in_array($data['visibility'], ['public','members_only','custom'])) {
            $data['visibility'] = 'public';
        }
        if (isset($data['visibility']) && $data['visibility'] !== 'custom') {
            $data['visibility_text'] = null;
        }

        // Normalize allowed_member_types
        if (array_key_exists('allowed_member_types', $data)) {
            $data['allowed_member_types'] = !empty($data['allowed_member_types']) ? $data['allowed_member_types'] : null;
        }

        // Normalize show_registrations to 0/1
        if (array_key_exists('show_registrations', $data)) {
            $data['show_registrations'] = !empty($data['show_registrations']) ? 1 : 0;
        }

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $activity->update($data, ['id' => $id]);
        $item = $activity->find($id);
        Auth::logActivity((int)$this->currentUser['id'], 'update', 'activity', "แก้ไขกิจกรรม: " . ($item['title'] ?? $id), $id, 'activity');
        Response::success(null, 'อัปเดตกิจกรรมสำเร็จ');
    }

    /**
     * POST  ?controller=activity&action=delete
     */
    public function delete(): void
    {
        $this->requirePost();
        $this->requireActivitiesAccess('delete');
        $id = (int)($this->input()['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        $item = $activity->find($id);
        if (!$activity->has(['id' => $id])) Response::error('ไม่พบกิจกรรม', 404);

        // Delete related registrations first
        $this->model('ActivityRegistrationModel')->delete(['activity_id' => $id]);
        $activity->delete(['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'activity', "ลบกิจกรรม: " . ($item['title'] ?? $id), $id, 'activity');
        Response::success(null, 'ลบกิจกรรมสำเร็จ');
    }

    /* ------------------------------------------------------------------ */
    /*  MEMBER REGISTRATION                                               */
    /* ------------------------------------------------------------------ */

    /**
     * POST  ?controller=activity&action=register
     */
    public function register(): void
    {
        $this->requirePost();
        $input = $this->input();
        $actId = (int)($input['activity_id'] ?? 0);
        if (!$actId) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        $act = $activity->find($actId);
        if (!$act || !in_array($act['status'], ['open'])) {
            Response::error('ไม่พบกิจกรรม หรือกิจกรรมยังไม่เปิดรับสมัคร', 404);
        }

        $reg = $this->model('ActivityRegistrationModel');

        // Check member type eligibility
        if (!empty($act['allowed_member_types'])) {
            $allowedTypes = array_map('trim', explode(',', $act['allowed_member_types']));
            $userType = $this->currentUser['member_type'] ?? '';
            if (!in_array($userType, $allowedTypes)) {
                Response::error('กิจกรรมนี้ไม่เปิดรับสมัครสำหรับประเภทสมาชิกของคุณ');
            }
        }

        // Check duplicate
        if ($reg->findUserRegistration($actId, $this->currentUser['id'])) {
            Response::error('คุณลงทะเบียนกิจกรรมนี้แล้ว');
        }

        // Check capacity
        if ($act['max_participants'] > 0) {
            $count = $reg->approvedCount($actId);
            if ($count >= $act['max_participants']) {
                Response::error('กิจกรรมเต็มจำนวนแล้ว');
            }
        }

        $id = $reg->create([
            'activity_id'    => $actId,
            'user_id'        => $this->currentUser['id'],
            'payment_status' => $act['has_fee'] ? 'pending' : 'not_required',
            'payment_proof'  => $input['payment_proof'] ?? null,
            'status'         => 'pending',
        ]);

        Auth::logActivity((int)$this->currentUser['id'], 'register', 'activity', "ลงทะเบียนกิจกรรม: {$act['title']}", $actId, 'activity');

        // แจ้งเตือน Telegram
        try {
            \App\Core\Telegram::notifyActivityRegistration($this->currentUser, $act);
        } catch (\Throwable $e) { /* ไม่บล็อก flow หลัก */ }

        Response::success(['id' => $id], 'ลงทะเบียนสำเร็จ รอการอนุมัติ', 201);
    }

    /**
     * POST  ?controller=activity&action=cancel-registration
     */
    public function cancelRegistration(): void
    {
        $this->requirePost();
        $regId = (int)($this->input()['registration_id'] ?? 0);
        if (!$regId) Response::error('กรุณาระบุ id การลงทะเบียน');

        $reg  = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        // Only owner or admin
        $isAdmin = $this->currentUser['role'] === 'admin';
        if ($item['user_id'] != $this->currentUser['id'] && !$isAdmin) {
            Response::error('ไม่มีสิทธิ์ยกเลิกการลงทะเบียนนี้', 403);
        }

        $reg->delete(['id' => $regId]);
        Auth::logActivity((int)$this->currentUser['id'], 'cancel_registration', 'activity', "ยกเลิกลงทะเบียน #$regId", (int)$item['activity_id'], 'activity');
        Response::success(null, 'ยกเลิกการลงทะเบียนสำเร็จ');
    }

    /**
     * POST  ?controller=activity&action=approve-registration
     */
    public function approveRegistration(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();
        $input = $this->input();
        $regId  = (int)($input['registration_id'] ?? 0);
        $status = $input['status'] ?? '';

        if (!$regId)                                      Response::error('กรุณาระบุ id การลงทะเบียน');
        if (!in_array($status, ['approved','rejected']))  Response::error('สถานะไม่ถูกต้อง');

        $reg  = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        $data = ['status' => $status];
        if (isset($input['payment_status'])) {
            $data['payment_status'] = $input['payment_status'];
        }

        $reg->update($data, ['id' => $regId]);

        // Auto-generate receipt for approved paid registrations
        if ($status === 'approved' && ($input['payment_status'] ?? '') === 'paid') {
            $this->generateActivityReceipt($item);
            $this->generateActivityFinanceTransaction($item);
        }

        $actionLabel = $status === 'approved' ? 'อนุมัติ' : 'ปฏิเสธ';
        Auth::logActivity((int)$this->currentUser['id'], $status === 'approved' ? 'approve_registration' : 'reject_registration', 'activity', "{$actionLabel}การลงทะเบียน #$regId", (int)$item['activity_id'], 'activity');
        $msg = $status === 'approved' ? 'อนุมัติการลงทะเบียนสำเร็จ' : 'ปฏิเสธการลงทะเบียนสำเร็จ';
        Response::success(null, $msg);
    }

    /**
     * Auto-generate a receipt for activity fee payment
     */
    private function generateActivityReceipt(array $registration): void
    {
        $receipts = $this->model('ReceiptModel');

        // Don't duplicate
        $existing = $receipts->findByReference('activity_fee', (int)$registration['id']);
        if ($existing) return;

        $activity = $this->model('ActivityModel');
        $act = $activity->find((int)$registration['activity_id']);
        if (!$act || !$act['has_fee'] || $act['fee_amount'] <= 0) return;

        $users = $this->model('UserModel');
        $user = $users->find((int)$registration['user_id'], ['full_name', 'school_organization', 'work_address', 'home_address']);
        if (!$user) return;

        $payerAddress = FeeController::buildPayerAddress($user);

        $settings = $this->model('SettingsModel');
        $description = $act['fee_description']
            ? "ค่าลงทะเบียนเข้าร่วม \"{$act['title']}\" ({$act['fee_description']})"
            : "ค่าลงทะเบียนเข้าร่วม \"{$act['title']}\"";

        $receipts->createReceipt([
            'user_id'       => (int)$registration['user_id'],
            'receipt_type'  => 'activity_fee',
            'reference_id'  => (int)$registration['id'],
            'title'         => 'ค่าลงทะเบียนกิจกรรม',
            'payer_name'    => $user['full_name'],
            'payer_address' => $payerAddress,
            'description'   => $description,
            'amount'        => (float)$act['fee_amount'],
            'received_by'   => $settings->get('signature_name', ''),
            'issued_date'   => date('Y-m-d'),
        ]);
    }

    /**
     * Auto-create a finance transaction for approved activity fee payment
     */
    private function generateActivityFinanceTransaction(array $registration): void
    {
        $finTxn = $this->model('FinanceTransactionModel');
        $refNo  = 'ACT-REG-' . $registration['id'];

        // Prevent duplicate
        $existing = $finTxn->findBy(['reference_no' => $refNo]);
        if ($existing) return;

        $activity = $this->model('ActivityModel');
        $act = $activity->find((int)$registration['activity_id']);
        if (!$act || !$act['has_fee'] || $act['fee_amount'] <= 0) return;

        $users = $this->model('UserModel');
        $user = $users->find((int)$registration['user_id'], ['full_name']);

        // Category ID 3 = "รายรับจากกิจกรรม"
        $catModel = $this->model('FinanceCategoryModel');
        $cat = $catModel->findBy(['name[~]' => 'กิจกรรม', 'type' => 'income']);
        $categoryId = $cat ? (int)$cat['id'] : 3;

        $payerName = $user ? $user['full_name'] : 'สมาชิก';
        $description = $act['fee_description']
            ? "ค่าลงทะเบียน \"{$act['title']}\" ({$act['fee_description']}) - {$payerName}"
            : "ค่าลงทะเบียน \"{$act['title']}\" - {$payerName}";

        $finTxn->create([
            'category_id'      => $categoryId,
            'type'             => 'income',
            'title'            => "รับค่าลงทะเบียนกิจกรรม: {$act['title']}",
            'description'      => $description,
            'amount'           => (float)$act['fee_amount'],
            'transaction_date' => date('Y-m-d'),
            'reference_no'     => $refNo,
            'attachment'       => $registration['payment_proof'] ?? null,
            'created_by'       => (int)$this->currentUser['id'],
            'status'           => 'approved',
            'approved_by'      => (int)$this->currentUser['id'],
            'approved_at'      => date('Y-m-d H:i:s'),
            'note'             => "สร้างอัตโนมัติจากการอนุมัติการลงทะเบียน #{$registration['id']}",
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  ADMIN – Registration list for an activity                         */
    /* ------------------------------------------------------------------ */

    /**
     * GET  ?controller=activity&action=registrations&id=X
     * Accessible by admin or finance managers
     */
    public function registrations(): void
    {
        $this->requireActivityOrFinanceManageAccess();

        $actId = (int)$this->query('id');
        if (!$actId) Response::error('กรุณาระบุ id กิจกรรม');

        $reg  = $this->model('ActivityRegistrationModel');
        $list = $reg->getByActivity($actId);

        Response::success($list);
    }

    /**
     * GET  ?controller=activity&action=search-members&id=ACTIVITY_ID&q=...
     * Search approved members not yet registered in this activity
     */
    public function searchMembers(): void
    {
        $this->requireActivityOrFinanceManageAccess();

        $actId = (int)$this->query('id');
        if (!$actId) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        $act = $activity->find($actId);
        if (!$act) Response::error('ไม่พบกิจกรรม', 404);

        $users = $this->model('UserModel');
        $reg = $this->model('ActivityRegistrationModel');
        $db = $users->getDB();
        $q = trim((string)$this->query('q', ''));

        $alreadyRows = $reg->all(['user_id'], ['activity_id' => $actId]);
        $excludeIds = array_map(static function ($r) {
            return (int)($r['user_id'] ?? 0);
        }, $alreadyRows ?: []);

        $where = [
            'role' => 'member',
            'status' => 'active',
            'ORDER' => ['full_name' => 'ASC'],
            'LIMIT' => 30,
        ];

        if (!empty($excludeIds)) {
            $where['id[!]'] = $excludeIds;
        }

        if ($q !== '') {
            $where['OR'] = [
                'full_name[~]' => '%' . $q . '%',
                'email[~]' => '%' . $q . '%',
            ];
            if ($users->hasColumn('first_name')) {
                $where['OR']['first_name[~]'] = '%' . $q . '%';
            }
            if ($users->hasColumn('last_name')) {
                $where['OR']['last_name[~]'] = '%' . $q . '%';
            }
            if ($users->hasColumn('member_number')) {
                $where['OR']['member_number[~]'] = '%' . $q . '%';
            }
        }

        $columns = ['id', 'full_name', 'email', 'school_organization', 'member_type'];
        if ($users->hasColumn('member_number')) {
            $columns[] = 'member_number';
        }

        $data = $db->select('users', $columns, $where) ?: [];
        Response::success($data);
    }

    /**
     * POST  ?controller=activity&action=add-member-registration
     * Admin/sub-admin adds a member into activity registration list
     */
    public function addMemberRegistration(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();

        $input = $this->input();
        $actId = (int)($input['activity_id'] ?? 0);
        $userId = (int)($input['user_id'] ?? 0);
        if (!$actId || !$userId) Response::error('กรุณาระบุ activity_id และ user_id');

        $activity = $this->model('ActivityModel');
        $act = $activity->find($actId);
        if (!$act) Response::error('ไม่พบกิจกรรม', 404);

        $users = $this->model('UserModel');
        $user = $users->find($userId, ['id', 'full_name', 'role', 'status']);
        if (!$user || ($user['role'] ?? '') !== 'member') Response::error('ไม่พบสมาชิก', 404);
        if (($user['status'] ?? '') !== 'active') Response::error('สมาชิกยังไม่พร้อมลงทะเบียน', 400);

        $reg = $this->model('ActivityRegistrationModel');
        if ($reg->findUserRegistration($actId, $userId)) {
            Response::error('สมาชิกท่านนี้ลงทะเบียนกิจกรรมนี้แล้ว');
        }

        $paymentStatus = $act['has_fee'] ? 'pending' : 'not_required';
        $id = $reg->create([
            'activity_id' => $actId,
            'user_id' => $userId,
            'status' => 'pending',
            'payment_status' => $paymentStatus,
            'payment_proof' => null,
            'note' => $input['note'] ?? null,
        ]);

        Auth::logActivity(
            (int)$this->currentUser['id'],
            'add_registration',
            'activity',
            "เพิ่มสมาชิกเข้ากิจกรรม: {$user['full_name']}",
            $actId,
            'activity'
        );

        Response::success(['id' => $id], 'เพิ่มสมาชิกเข้าร่วมกิจกรรมสำเร็จ', 201);
    }

    /**
     * GET  ?controller=activity&action=registration-detail&registration_id=X
     */
    public function registrationDetail(): void
    {
        $this->requireActivityOrFinanceManageAccess();

        $regId = (int)$this->query('registration_id');
        if (!$regId) Response::error('กรุณาระบุ registration_id');

        $reg = $this->model('ActivityRegistrationModel');
        $row = $reg->getJoin(
            [
                '[>]users' => ['user_id' => 'id'],
                '[>]activities' => ['activity_id' => 'id'],
            ],
            [
                'activity_registrations.id',
                'activity_registrations.activity_id',
                'activity_registrations.user_id',
                'activity_registrations.status',
                'activity_registrations.payment_status',
                'activity_registrations.payment_proof',
                'activity_registrations.note',
                'activity_registrations.registered_at',
                'activity_registrations.approved_at',
                'users.full_name',
                'users.email',
                'users.school_organization',
                'users.work_address',
                'users.home_address',
                'activities.title(activity_title)',
                'activities.has_fee',
                'activities.fee_amount',
            ],
            ['activity_registrations.id' => $regId]
        );

        if (!$row) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        $receipts = $this->model('ReceiptModel');
        $receipt = $receipts->findByReference('activity_fee', (int)$row['id']);
        $row['receipt'] = $receipt ? [
            'id' => (int)$receipt['id'],
            'book_number' => $receipt['book_number'],
            'receipt_number' => $receipt['receipt_number'],
        ] : null;

        Response::success($row);
    }

    /**
     * POST  ?controller=activity&action=manage-registration
     * Update registration status/payment/slip/note by admin or assigned sub-admin
     */
    public function manageRegistration(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();

        $input = $this->input();
        $regId = (int)($input['registration_id'] ?? 0);
        if (!$regId) Response::error('กรุณาระบุ registration_id');

        $reg = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        $data = [];
        if (isset($input['status'])) {
            $status = trim((string)$input['status']);
            if (!in_array($status, ['pending', 'approved', 'rejected', 'cancelled'], true)) {
                Response::error('สถานะการเข้าร่วมไม่ถูกต้อง');
            }
            $data['status'] = $status;
            if (in_array($status, ['approved', 'rejected'], true)) {
                $data['approved_by'] = (int)$this->currentUser['id'];
                $data['approved_at'] = date('Y-m-d H:i:s');
            }
        }

        if (isset($input['payment_status'])) {
            $pay = trim((string)$input['payment_status']);
            if (!in_array($pay, ['not_required', 'pending', 'paid', 'refunded'], true)) {
                Response::error('สถานะการชำระเงินไม่ถูกต้อง');
            }
            $data['payment_status'] = $pay;
        }

        if (array_key_exists('payment_proof', $input)) {
            $data['payment_proof'] = $input['payment_proof'] ?: null;
        }

        if (array_key_exists('note', $input)) {
            $data['note'] = $input['note'] ?: null;
        }

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องบันทึก');

        $reg->update($data, ['id' => $regId]);

        Auth::logActivity(
            (int)$this->currentUser['id'],
            'manage_registration',
            'activity',
            "จัดการข้อมูลผู้ลงทะเบียน #{$regId}",
            (int)$item['activity_id'],
            'activity'
        );

        Response::success(null, 'บันทึกข้อมูลผู้ลงทะเบียนสำเร็จ');
    }

    /**
     * POST  ?controller=activity&action=update-registration-address
     * Update member address info for a registration
     */
    public function updateRegistrationAddress(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();

        $input = $this->input();
        $regId = (int)($input['registration_id'] ?? 0);
        if (!$regId) Response::error('กรุณาระบุ registration_id');

        $reg = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        $users = $this->model('UserModel');
        $user = $users->find((int)$item['user_id']);
        if (!$user) Response::error('ไม่พบสมาชิก', 404);

        $data = [];
        if (array_key_exists('school_organization', $input)) {
            $data['school_organization'] = trim((string)$input['school_organization']) ?: null;
        }
        if (array_key_exists('work_address', $input)) {
            $workAddress = is_array($input['work_address'])
                ? json_encode($input['work_address'], JSON_UNESCAPED_UNICODE)
                : (string)$input['work_address'];
            $data['work_address'] = $workAddress !== '' ? $workAddress : null;
        }
        if (array_key_exists('home_address', $input)) {
            $homeAddress = is_array($input['home_address'])
                ? json_encode($input['home_address'], JSON_UNESCAPED_UNICODE)
                : (string)$input['home_address'];
            $data['home_address'] = $homeAddress !== '' ? $homeAddress : null;
        }

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $users->update($data, ['id' => (int)$item['user_id']]);

        Auth::logActivity(
            (int)$this->currentUser['id'],
            'update_member_address',
            'activity',
            "แก้ไขที่อยู่สมาชิกจากกิจกรรม #{$regId}",
            (int)$item['activity_id'],
            'activity'
        );

        Response::success(null, 'บันทึกที่อยู่สมาชิกสำเร็จ');
    }

    /**
     * POST  ?controller=activity&action=create-registration-receipt
     * Create activity receipt independently from approval status
     */
    public function createRegistrationReceipt(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();

        $input = $this->input();
        $regId = (int)($input['registration_id'] ?? 0);
        if (!$regId) Response::error('กรุณาระบุ registration_id');

        $reg = $this->model('ActivityRegistrationModel');
        $registration = $reg->find($regId);
        if (!$registration) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        $receipts = $this->model('ReceiptModel');
        $existing = $receipts->findByReference('activity_fee', $regId);
        if ($existing) {
            Response::success([
                'receipt_id' => (int)$existing['id'],
                'receipt_book' => $existing['book_number'] ?? null,
                'receipt_number' => $existing['receipt_number'] ?? null,
            ], 'มีใบเสร็จสำหรับรายการนี้แล้ว');
            return;
        }

        $activity = $this->model('ActivityModel');
        $act = $activity->find((int)$registration['activity_id']);
        if (!$act) Response::error('ไม่พบกิจกรรม', 404);
        if (empty($act['has_fee']) || (float)($act['fee_amount'] ?? 0) <= 0) {
            Response::error('กิจกรรมนี้ไม่มีค่าลงทะเบียน จึงไม่สามารถออกใบเสร็จได้');
        }

        $users = $this->model('UserModel');
        $user = $users->find((int)$registration['user_id'], ['full_name', 'school_organization', 'work_address', 'home_address']);
        if (!$user) Response::error('ไม่พบข้อมูลสมาชิก', 404);

        $rawSource = strtolower(trim((string)($input['address_source'] ?? 'work')));
        $addressSource = in_array($rawSource, ['current', 'home', 'personal'], true) ? 'personal' : 'organization';
        $payerAddress = $input['payer_address'] ?? null;
        if (!$payerAddress) {
            $payerAddress = FeeController::buildPayerAddress($user, $addressSource);
        }

        $settings = $this->model('SettingsModel');
        $description = $act['fee_description']
            ? "ค่าลงทะเบียนเข้าร่วม \"{$act['title']}\" ({$act['fee_description']})"
            : "ค่าลงทะเบียนเข้าร่วม \"{$act['title']}\"";

        try {
            $receiptId = $receipts->createReceipt([
                'user_id'       => (int)$registration['user_id'],
                'receipt_type'  => 'activity_fee',
                'reference_id'  => (int)$registration['id'],
                'title'         => 'ค่าลงทะเบียนกิจกรรม',
                'payer_name'    => $user['full_name'],
                'payer_address' => $payerAddress,
                'description'   => $description,
                'amount'        => (float)$act['fee_amount'],
                'received_by'   => $settings->get('signature_name', ''),
                'issued_date'   => date('Y-m-d'),
            ]);
        } catch (\Throwable $e) {
            Response::error($e->getMessage() ?: 'ไม่สามารถสร้างใบเสร็จได้');
        }

        $newReceipt = $receipts->find((int)$receiptId);

        Auth::logActivity(
            (int)$this->currentUser['id'],
            'create_receipt',
            'activity',
            "ออกใบเสร็จกิจกรรมจากการลงทะเบียน #{$regId}",
            (int)$registration['activity_id'],
            'activity'
        );

        Response::success([
            'receipt_id' => (int)$receiptId,
            'receipt_book' => $newReceipt['book_number'] ?? null,
            'receipt_number' => $newReceipt['receipt_number'] ?? null,
        ], 'สร้างใบเสร็จสำเร็จ', 201);
    }

    /**
     * POST  ?controller=activity&action=upload-slip
     * Member uploads payment slip for an existing registration
     */
    public function uploadSlip(): void
    {
        $this->requirePost();
        $input = $this->input();
        $regId = (int)($input['registration_id'] ?? 0);
        $proof = trim($input['payment_proof'] ?? '');

        if (!$regId) Response::error('กรุณาระบุ id การลงทะเบียน');
        if (!$proof) Response::error('กรุณาแนบสลิปโอนเงิน');

        $reg  = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        // Only registration owner
        if ($item['user_id'] != $this->currentUser['id']) {
            Response::error('ไม่มีสิทธิ์อัพโหลดสลิปสำหรับการลงทะเบียนนี้', 403);
        }

        $reg->update([
            'payment_proof'  => $proof,
            'payment_status' => 'pending',
        ], ['id' => $regId]);

        Auth::logActivity((int)$this->currentUser['id'], 'upload_slip', 'activity', "อัพโหลดสลิปโอนเงิน #$regId", (int)$item['activity_id'], 'activity');
        Response::success(null, 'อัพโหลดสลิปสำเร็จ');
    }

    /**
     * GET  ?controller=activity&action=pending-payments
     * List all registrations with pending payment (for finance manager approval)
     */
    public function pendingPayments(): void
    {
        $this->requireActivityOrFinanceManageAccess();

        $reg = $this->model('ActivityRegistrationModel');
        $activity = $this->model('ActivityModel');

        $status = $this->query('status') ?: 'pending';
        $list = $reg->getPendingPayments($status);

        Response::success($list);
    }

    /**
     * POST  ?controller=activity&action=verify-payment
     * Finance manager verifies/rejects a payment slip
     */
    public function verifyPayment(): void
    {
        $this->requirePost();
        $this->requireActivityOrFinanceManageAccess();

        $input  = $this->input();
        $regId  = (int)($input['registration_id'] ?? 0);
        $action = $input['action'] ?? '';
        $note   = $input['note'] ?? null;

        if (!$regId) Response::error('กรุณาระบุ id การลงทะเบียน');
        if (!in_array($action, ['approve', 'reject'])) Response::error('action ไม่ถูกต้อง');

        $reg  = $this->model('ActivityRegistrationModel');
        $item = $reg->find($regId);
        if (!$item) Response::error('ไม่พบข้อมูลการลงทะเบียน', 404);

        if ($action === 'approve') {
            $reg->update([
                'payment_status' => 'paid',
                'status'         => 'approved',
                'approved_by'    => $this->currentUser['id'],
                'approved_at'    => date('Y-m-d H:i:s'),
                'note'           => $note,
            ], ['id' => $regId]);
            // Auto-generate receipt
            $this->generateActivityReceipt($item);
            // Auto-create finance transaction
            $this->generateActivityFinanceTransaction($item);
            Auth::logActivity((int)$this->currentUser['id'], 'verify_payment', 'activity', "อนุมัติการชำระเงิน #$regId", (int)$item['activity_id'], 'activity');
            Response::success(null, 'อนุมัติการชำระเงินสำเร็จ');
        } else {
            $reg->update([
                'payment_status' => 'pending',
                'status'         => 'rejected',
                'approved_by'    => $this->currentUser['id'],
                'approved_at'    => date('Y-m-d H:i:s'),
                'note'           => $note ?: 'สลิปไม่ถูกต้อง',
            ], ['id' => $regId]);
            Auth::logActivity((int)$this->currentUser['id'], 'reject_payment', 'activity', "ปฏิเสธการชำระเงิน #$regId", (int)$item['activity_id'], 'activity');
            Response::success(null, 'ปฏิเสธการชำระเงินแล้ว');
        }
    }

    /**
     * Check if current user is admin or finance manager
     */
    private function requireFinanceOrAdmin(): void
    {
        $isAdmin = $this->currentUser['role'] === 'admin';
        if ($isAdmin) return;

        $fm = $this->model('FinanceManagerModel');
        $manager = $fm->getByUserId($this->currentUser['id']);
        if (!$manager || !$manager['is_active']) {
            Response::error('คุณไม่มีสิทธิ์เข้าถึงส่วนนี้', 403);
        }
    }

    /* ------------------------------------------------------------------ */
    /*  PUBLIC REGISTRATIONS  (access code protected)                      */
    /* ------------------------------------------------------------------ */

    /**
     * GET  ?controller=activity&action=public-registrations
     * Public endpoint — requires access_code, no auth needed
     */
    public function publicRegistrations(): void
    {
        $actId = (int)$this->query('id');
        $code  = trim($this->query('code') ?? '');
        if (!$actId) Response::error('กรุณาระบุ id กิจกรรม');
        if (!$code)  Response::error('กรุณาระบุรหัสเข้าดู');

        $activity = $this->model('ActivityModel');
        $act = $activity->find($actId);
        if (!$act) Response::error('ไม่พบกิจกรรม', 404);

        if (empty($act['access_code'])) {
            Response::error('กิจกรรมนี้ยังไม่เปิดให้ดูรายชื่อ');
        }
        if ($act['access_code'] !== $code) {
            Response::error('รหัสเข้าดูไม่ถูกต้อง', 403);
        }

        $reg  = $this->model('ActivityRegistrationModel');
        $list = $reg->getByActivity($actId);

        Response::success([
            'activity' => [
                'id'               => $act['id'],
                'title'            => $act['title'],
                'location'         => $act['location'],
                'start_date'       => $act['start_date'],
                'end_date'         => $act['end_date'],
                'max_participants' => $act['max_participants'],
                'has_fee'          => $act['has_fee'],
                'fee_amount'       => $act['fee_amount'],
                'status'           => $act['status'],
                'cover_image'      => $act['cover_image'],
            ],
            'registrations' => $list,
        ]);
    }

    /**
     * POST  ?controller=activity&action=reset-access-code
     * Admin resets or generates a new access code for an activity
     */
    public function resetAccessCode(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        if (!$activity->has(['id' => $id])) Response::error('ไม่พบกิจกรรม', 404);

        // Generate 8-char alphanumeric code
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }

        $activity->update(['access_code' => $code], ['id' => $id]);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'update', 'activity',
            "รีเซ็ตรหัสเข้าดูกิจกรรม #{$id}",
            $id, 'activity'
        );

        Response::success(['access_code' => $code], 'สร้างรหัสเข้าดูสำเร็จ');
    }

    /**
     * POST  ?controller=activity&action=remove-access-code
     * Admin removes access code (disables public view)
     */
    public function removeAccessCode(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id กิจกรรม');

        $activity = $this->model('ActivityModel');
        if (!$activity->has(['id' => $id])) Response::error('ไม่พบกิจกรรม', 404);

        $activity->update(['access_code' => null], ['id' => $id]);

        Response::success(null, 'ลบรหัสเข้าดูสำเร็จ');
    }
}
