<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

class FinanceController extends Controller
{
    // =============================================
    // Helper: ตรวจสอบสิทธิ์ admin หรือ finance manager
    // =============================================
    private function requireFinanceAccess(string $permission = 'create'): void
    {
        if (!$this->currentUser) {
            Response::error('กรุณาเข้าสู่ระบบ', 401);
        }
        if ($this->currentUser['role'] === 'admin') return;

        $uid = (int)$this->currentUser['id'];

        // Check legacy finance manager table
        $mgr = $this->model('FinanceManagerModel');
        if ($mgr->hasPermission($uid, $permission)) return;

        // Also allow sub-admin with finance area permission
        $sa = $this->model('SubAdminModel');
        if ($sa->hasPermission($uid, 'finance', $permission)) return;

        Response::error('คุณไม่มีสิทธิ์ดำเนินการนี้', 403);
    }

    private function isFinanceManager(): bool
    {
        if (!$this->currentUser) return false;
        if ($this->currentUser['role'] === 'admin') return true;
        $uid = (int)$this->currentUser['id'];
        $mgr = $this->model('FinanceManagerModel');
        if ($mgr->isFinanceManager($uid)) return true;
        $sa = $this->model('SubAdminModel');
        return $sa->isSubAdmin($uid) && !empty($sa->getMyAreas($uid)['finance'] ?? []);
    }

    // =============================================
    // CATEGORIES
    // =============================================

    /**
     * รายการประเภท
     */
    public function categories(): void
    {
        $model = $this->model('FinanceCategoryModel');
        $result = $model->getList(
            [
                'type' => $this->query('type'),
                'is_active' => $this->query('is_active'),
                'search' => $this->query('search'),
            ],
            $this->getPage(),
            $this->getPerPage(100)
        );
        Response::paginated($result['data'], $result['total'], $result['page'], $result['perPage']);
    }

    /**
     * ประเภทที่ active (สำหรับ dropdown)
     */
    public function activeCategories(): void
    {
        $model = $this->model('FinanceCategoryModel');
        $data = $model->getActiveCategories($this->query('type'));
        Response::success($data);
    }

    /**
     * สร้างประเภทใหม่
     */
    public function createCategory(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('create');
        $input = $this->input();

        if (empty(trim($input['name'] ?? ''))) Response::error('กรุณากรอกชื่อประเภท');
        if (empty($input['type']) || !in_array($input['type'], ['income', 'expense'])) {
            Response::error('กรุณาเลือกประเภท (รายรับ/รายจ่าย)');
        }

        $model = $this->model('FinanceCategoryModel');
        $id = $model->create([
            'name' => trim($input['name']),
            'type' => $input['type'],
            'description' => trim($input['description'] ?? ''),
            'is_active' => 1,
            'sort_order' => (int)($input['sort_order'] ?? 0),
        ]);

        Auth::logActivity((int)$this->currentUser['id'], 'create', 'finance', "สร้างประเภทการเงิน: {$input['name']}", $id, 'finance_category');
        Response::success(['id' => $id], 'สร้างประเภทสำเร็จ', 201);
    }

    /**
     * แก้ไขประเภท
     */
    public function updateCategory(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('edit');
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('FinanceCategoryModel');
        if (!$model->has(['id' => $id])) Response::error('ไม่พบประเภทที่ต้องการ', 404);

        $data = [];
        if (isset($input['name'])) $data['name'] = trim($input['name']);
        if (isset($input['type']) && in_array($input['type'], ['income', 'expense'])) $data['type'] = $input['type'];
        if (isset($input['description'])) $data['description'] = trim($input['description']);
        if (isset($input['is_active'])) $data['is_active'] = (int)$input['is_active'];
        if (isset($input['sort_order'])) $data['sort_order'] = (int)$input['sort_order'];

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $model->update($data, ['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'update', 'finance', "แก้ไขประเภทการเงิน ID: {$id}", $id, 'finance_category');
        Response::success(null, 'อัปเดตประเภทสำเร็จ');
    }

    /**
     * ลบประเภท
     */
    public function deleteCategory(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('delete');
        $id = (int)($this->input()['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('FinanceCategoryModel');
        if (!$model->has(['id' => $id])) Response::error('ไม่พบประเภทที่ต้องการ', 404);

        // ตรวจสอบว่ามีธุรกรรมใช้ประเภทนี้อยู่ไหม
        $txnModel = $this->model('FinanceTransactionModel');
        if ($txnModel->count(['category_id' => $id]) > 0) {
            Response::error('ไม่สามารถลบได้ เนื่องจากมีรายการธุรกรรมที่ใช้ประเภทนี้อยู่');
        }

        $model->delete(['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'finance', "ลบประเภทการเงิน ID: {$id}", $id, 'finance_category');
        Response::success(null, 'ลบประเภทสำเร็จ');
    }

    // =============================================
    // TRANSACTIONS
    // =============================================

    /**
     * รายการธุรกรรม
     */
    public function list(): void
    {
        $model = $this->model('FinanceTransactionModel');
        $filters = [
            'type' => $this->query('type'),
            'status' => $this->query('status'),
            'category_id' => $this->query('category_id'),
            'search' => $this->query('search'),
            'date_from' => $this->query('date_from'),
            'date_to' => $this->query('date_to'),
            'month' => $this->query('month'),
            'year' => $this->query('year'),
        ];

        $result = $model->getList($filters, $this->getPage(), $this->getPerPage(20));
        Response::paginated($result['data'], $result['total'], $result['page'], $result['perPage']);
    }

    /**
     * รายละเอียดธุรกรรม
     */
    public function detail(): void
    {
        $id = (int)$this->query('id');
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('FinanceTransactionModel');
        $item = $model->getDetail($id);
        if (!$item) Response::error('ไม่พบรายการที่ต้องการ', 404);

        Response::success($item);
    }

    /**
     * สร้างธุรกรรมใหม่
     */
    public function create(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('create');
        $input = $this->input();

        // Validate
        if (empty(trim($input['title'] ?? ''))) Response::error('กรุณากรอกรายการ');
        if (empty($input['type']) || !in_array($input['type'], ['income', 'expense'])) {
            Response::error('กรุณาเลือกประเภท (รายรับ/รายจ่าย)');
        }
        if (empty($input['category_id'])) Response::error('กรุณาเลือกหมวดหมู่');
        if (!isset($input['amount']) || (float)$input['amount'] <= 0) {
            Response::error('กรุณากรอกจำนวนเงินที่ถูกต้อง');
        }
        if (empty($input['transaction_date'])) Response::error('กรุณาเลือกวันที่ทำรายการ');

        // ตรวจสอบ category
        $catModel = $this->model('FinanceCategoryModel');
        if (!$catModel->has(['id' => (int)$input['category_id']])) {
            Response::error('ไม่พบหมวดหมู่ที่เลือก');
        }

        $model = $this->model('FinanceTransactionModel');
        $data = [
            'category_id' => (int)$input['category_id'],
            'type' => $input['type'],
            'title' => trim($input['title']),
            'description' => trim($input['description'] ?? ''),
            'amount' => (float)$input['amount'],
            'transaction_date' => $input['transaction_date'],
            'reference_no' => trim($input['reference_no'] ?? ''),
            'attachment' => $input['attachment'] ?? null,
            'created_by' => (int)$this->currentUser['id'],
            'status' => 'approved',
            'note' => trim($input['note'] ?? ''),
        ];

        $id = $model->create($data);
        $typeLabel = $input['type'] === 'income' ? 'รายรับ' : 'รายจ่าย';
        Auth::logActivity((int)$this->currentUser['id'], 'create', 'finance', "สร้าง{$typeLabel}: {$data['title']} จำนวน {$data['amount']} บาท", $id, 'finance_transaction');
        Response::success(['id' => $id], 'สร้างรายการสำเร็จ', 201);
    }

    /**
     * แก้ไขธุรกรรม
     */
    public function update(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('edit');
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('FinanceTransactionModel');
        $item = $model->find($id);
        if (!$item) Response::error('ไม่พบรายการที่ต้องการ', 404);

        $data = [];
        foreach (['title', 'description', 'amount', 'transaction_date', 'reference_no', 'attachment', 'note', 'type', 'category_id'] as $f) {
            if (isset($input[$f])) {
                $data[$f] = is_string($input[$f]) ? trim($input[$f]) : $input[$f];
            }
        }
        if (isset($data['amount'])) $data['amount'] = (float)$data['amount'];
        if (isset($data['category_id'])) $data['category_id'] = (int)$data['category_id'];

        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $model->update($data, ['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'update', 'finance', "แก้ไขรายการการเงิน: {$item['title']}", $id, 'finance_transaction');
        Response::success(null, 'อัปเดตรายการสำเร็จ');
    }

    /**
     * ลบธุรกรรม
     */
    public function delete(): void
    {
        $this->requirePost();
        $this->requireFinanceAccess('delete');
        $id = (int)($this->input()['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('FinanceTransactionModel');
        $item = $model->find($id);
        if (!$item) Response::error('ไม่พบรายการที่ต้องการ', 404);

        $model->delete(['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'finance', "ลบรายการการเงิน: {$item['title']}", $id, 'finance_transaction');
        Response::success(null, 'ลบรายการสำเร็จ');
    }

    // =============================================
    // SUMMARY & REPORTS
    // =============================================

    /**
     * สรุปรายรับ-รายจ่าย
     */
    public function summary(): void
    {
        $filters = [
            'date_from' => $this->query('date_from'),
            'date_to' => $this->query('date_to'),
            'month' => $this->query('month'),
            'year' => $this->query('year'),
        ];

        $model = $this->model('FinanceTransactionModel');
        $summary = $model->getSummary($filters);
        $byCategory = $model->getSummaryByCategory($filters);

        Response::success([
            'summary' => $summary,
            'by_category' => $byCategory,
        ]);
    }

    /**
     * สรุปรายเดือน
     */
    public function monthlySummary(): void
    {
        $year = $this->query('year', date('Y'));
        $model = $this->model('FinanceTransactionModel');
        $data = $model->getMonthlySummary($year);

        // จัดรูปแบบข้อมูล
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $months[$key] = ['month' => $key, 'income' => 0, 'expense' => 0, 'balance' => 0];
        }
        foreach ($data as $row) {
            if (isset($months[$row['month']])) {
                $months[$row['month']][$row['type']] = (float)$row['total'];
            }
        }
        foreach ($months as &$m) {
            $m['balance'] = $m['income'] - $m['expense'];
        }

        Response::success(array_values($months));
    }

    /**
     * Export ข้อมูลสำหรับรายงาน (JSON สำหรับ frontend สร้าง CSV/PDF)
     */
    public function export(): void
    {
        $this->requireFinanceAccess('export');

        $filters = [
            'type' => $this->query('type'),
            'status' => $this->query('status'),
            'date_from' => $this->query('date_from'),
            'date_to' => $this->query('date_to'),
            'year' => $this->query('year'),
            'category_id' => $this->query('category_id'),
        ];

        $model = $this->model('FinanceTransactionModel');
        $data = $model->getExportData($filters);
        $summary = $model->getSummary($filters);

        Response::success([
            'transactions' => $data,
            'summary' => $summary,
            'filters' => $filters,
            'exported_at' => date('Y-m-d H:i:s'),
            'exported_by' => $this->currentUser['full_name'] ?? $this->currentUser['username'] ?? '',
        ]);
    }

    // =============================================
    // FINANCE MANAGERS
    // =============================================

    /**
     * รายชื่อผู้จัดการการเงิน
     */
    public function managers(): void
    {
        $model = $this->model('FinanceManagerModel');
        $data = $model->getList();

        // decode permissions JSON
        foreach ($data as &$item) {
            if (!empty($item['permissions'])) {
                $item['permissions'] = json_decode($item['permissions'], true) ?: [];
            } else {
                $item['permissions'] = [];
            }
        }

        Response::success($data);
    }

    /**
     * สมาชิกที่สามารถมอบสิทธิ์ได้
     */
    public function availableMembers(): void
    {
        $model = $this->model('FinanceManagerModel');
        Response::success($model->getAvailableMembers());
    }

    /**
     * มอบสิทธิ์ผู้จัดการการเงิน
     */
    public function assignManager(): void
    {
        $this->requirePost();
        // เฉพาะ admin เท่านั้น
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะ admin เท่านั้นที่สามารถมอบสิทธิ์ได้', 403);
        }

        $input = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาเลือกสมาชิก');

        $permissions = $input['permissions'] ?? ['create', 'edit', 'delete', 'export'];
        if (!is_array($permissions)) $permissions = ['create', 'edit', 'delete', 'export'];

        $model = $this->model('FinanceManagerModel');
        $id = $model->assign($userId, (int)$this->currentUser['id'], $permissions);

        Auth::logActivity((int)$this->currentUser['id'], 'assign', 'finance', "มอบสิทธิ์ผู้จัดการการเงินให้ user ID: {$userId}", $id, 'finance_manager');
        Response::success(['id' => $id], 'มอบสิทธิ์สำเร็จ');
    }

    /**
     * ถอนสิทธิ์ผู้จัดการการเงิน
     */
    public function revokeManager(): void
    {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะ admin เท่านั้นที่สามารถถอนสิทธิ์ได้', 403);
        }

        $userId = (int)($this->input()['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $model = $this->model('FinanceManagerModel');
        if (!$model->isFinanceManager($userId)) {
            Response::error('ผู้ใช้นี้ไม่ได้เป็นผู้จัดการการเงิน');
        }

        $model->revoke($userId);
        Auth::logActivity((int)$this->currentUser['id'], 'revoke', 'finance', "ถอนสิทธิ์ผู้จัดการการเงิน user ID: {$userId}", $userId, 'finance_manager');
        Response::success(null, 'ถอนสิทธิ์สำเร็จ');
    }

    /**
     * อัปเดตสิทธิ์ผู้จัดการการเงิน
     */
    public function updateManagerPermissions(): void
    {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะ admin เท่านั้น', 403);
        }

        $input = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $permissions = $input['permissions'] ?? [];
        if (!is_array($permissions)) Response::error('permissions ต้องเป็น array');

        $model = $this->model('FinanceManagerModel');
        $existing = $model->findBy(['user_id' => $userId]);
        if (!$existing) {
            Response::error('ผู้ใช้นี้ไม่ได้เป็นผู้จัดการการเงิน');
        }

        $model->update(['permissions' => json_encode($permissions)], ['user_id' => $userId]);
        Auth::logActivity((int)$this->currentUser['id'], 'update', 'finance', "อัปเดตสิทธิ์ผู้จัดการการเงิน user ID: {$userId}", $userId, 'finance_manager');
        Response::success(null, 'อัปเดตสิทธิ์สำเร็จ');
    }

    /**
     * สลับสถานะใช้งาน/ล็อคผู้จัดการการเงิน
     */
    public function toggleManager(): void
    {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะ admin เท่านั้น', 403);
        }

        $userId = (int)($this->input()['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $model = $this->model('FinanceManagerModel');
        $existing = $model->findBy(['user_id' => $userId]);
        if (!$existing) {
            Response::error('ผู้ใช้นี้ไม่ได้เป็นผู้จัดการการเงิน');
        }

        $newStatus = $existing['is_active'] ? 0 : 1;
        $model->update(['is_active' => $newStatus], ['user_id' => $userId]);

        $label = $newStatus ? 'เปิดใช้งาน' : 'ล็อค';
        Auth::logActivity((int)$this->currentUser['id'], 'toggle', 'finance', "{$label}ผู้จัดการการเงิน user ID: {$userId}", $userId, 'finance_manager');
        Response::success(['is_active' => $newStatus], "{$label}สำเร็จ");
    }

    /**
     * ลบผู้จัดการการเงิน (hard delete)
     */
    public function deleteManager(): void
    {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะ admin เท่านั้น', 403);
        }

        $userId = (int)($this->input()['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $model = $this->model('FinanceManagerModel');
        $existing = $model->findBy(['user_id' => $userId]);
        if (!$existing) {
            Response::error('ไม่พบข้อมูลผู้จัดการการเงิน');
        }

        $this->db->delete('finance_managers', ['user_id' => $userId]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'finance', "ลบผู้จัดการการเงิน user ID: {$userId}", $userId, 'finance_manager');
        Response::success(null, 'ลบผู้จัดการการเงินสำเร็จ');
    }

    /**
     * ตรวจสอบสิทธิ์ของตัวเอง
     */
    public function myPermissions(): void
    {
        if (!$this->currentUser) Response::error('กรุณาเข้าสู่ระบบ', 401);

        if ($this->currentUser['role'] === 'admin') {
            Response::success([
                'is_admin' => true,
                'is_finance_manager' => true,
                'permissions' => ['create', 'edit', 'delete', 'approve', 'export', 'manage'],
            ]);
            return;
        }

        $model = $this->model('FinanceManagerModel');
        $manager = $model->getByUserId((int)$this->currentUser['id']);

        Response::success([
            'is_admin' => false,
            'is_finance_manager' => $manager !== null,
            'permissions' => $manager ? ($manager['permissions'] ?? []) : [],
        ]);
    }
}
