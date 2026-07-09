<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;
use App\Models\SubAdminModel;

class SubAdminController extends Controller
{
    // ──────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────

    private function requireAdmin(): void
    {
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::error('เฉพาะผู้ดูแลระบบเท่านั้นที่สามารถจัดการสิทธิ์ได้', 403);
        }
    }

    private function validArea(string $area): bool
    {
        return array_key_exists($area, SubAdminModel::AREAS);
    }

    // ──────────────────────────────────────────────────────
    // LIST  — GET  (admin only)
    // ──────────────────────────────────────────────────────

    /**
     * GET sub-admin list, optionally filtered by ?area=members|news|activities
     */
    public function list(): void
    {
        $this->requireAdmin();
        $area = $this->query('area') ?: null;
        $model = $this->model('SubAdminModel');
        $data  = $model->getList($area);

        // Attach area label
        $labels = [
            'members'    => 'บริหารจัดการสมาชิก',
            'news'       => 'จัดการข่าวสาร',
            'activities' => 'จัดการกิจกรรม',
        ];
        foreach ($data as &$row) {
            $row['area_label'] = $labels[$row['area']] ?? $row['area'];
        }

        Response::success($data);
    }

    // ──────────────────────────────────────────────────────
    // AVAILABLE MEMBERS  — GET  (admin only)
    // ──────────────────────────────────────────────────────

    public function availableMembers(): void
    {
        $this->requireAdmin();
        $area = $this->query('area');
        if (!$area || !$this->validArea($area)) {
            Response::error('กรุณาระบุ area ที่ถูกต้อง (members | news | activities)');
        }
        $model = $this->model('SubAdminModel');
        Response::success($model->getAvailableMembers($area));
    }

    // ──────────────────────────────────────────────────────
    // ASSIGN  — POST  (admin only)
    // ──────────────────────────────────────────────────────

    public function assign(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        $area   = $input['area'] ?? '';
        $note   = trim($input['note'] ?? '');

        if (!$userId) Response::error('กรุณาเลือกผู้ใช้');
        if (!$this->validArea($area)) {
            Response::error('area ไม่ถูกต้อง');
        }

        $permissions = $input['permissions'] ?? SubAdminModel::AREAS[$area];
        if (!is_array($permissions)) {
            Response::error('permissions ต้องเป็น array');
        }

        // Validate permissions against allowed list for area
        $valid = array_intersect($permissions, SubAdminModel::AREAS[$area]);
        if (empty($valid)) {
            Response::error('ไม่มีสิทธิ์ที่ถูกต้องสำหรับ area นี้');
        }

        $model = $this->model('SubAdminModel');
        $id    = $model->assign($userId, $area, (int)$this->currentUser['id'], array_values($valid), $note);

        $areaLabel = $this->areaLabel($area);
        Auth::logActivity(
            (int)$this->currentUser['id'], 'assign', 'sub_admin',
            "มอบสิทธิ์ {$areaLabel} ให้ user ID: {$userId}",
            $id, 'sub_admin'
        );
        Response::success(['id' => $id], 'มอบสิทธิ์สำเร็จ');
    }

    // ──────────────────────────────────────────────────────
    // REVOKE  — POST  (admin only)
    // ──────────────────────────────────────────────────────

    public function revoke(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        $area   = $input['area'] ?? '';

        if (!$userId) Response::error('กรุณาระบุ user_id');
        if (!$this->validArea($area)) Response::error('area ไม่ถูกต้อง');

        $model = $this->model('SubAdminModel');
        $model->revoke($userId, $area);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'revoke', 'sub_admin',
            "ถอนสิทธิ์ {$this->areaLabel($area)} จาก user ID: {$userId}",
            $userId, 'sub_admin'
        );
        Response::success(null, 'ถอนสิทธิ์สำเร็จ');
    }

    // ──────────────────────────────────────────────────────
    // TOGGLE  — POST  (admin only)
    // ──────────────────────────────────────────────────────

    public function toggle(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        $area   = $input['area'] ?? '';

        if (!$userId) Response::error('กรุณาระบุ user_id');
        if (!$this->validArea($area)) Response::error('area ไม่ถูกต้อง');

        $model     = $this->model('SubAdminModel');
        $newStatus = $model->toggle($userId, $area);
        $label     = $newStatus ? 'เปิดใช้งาน' : 'ระงับ';

        Auth::logActivity(
            (int)$this->currentUser['id'], 'toggle', 'sub_admin',
            "{$label}สิทธิ์ {$this->areaLabel($area)} ของ user ID: {$userId}",
            $userId, 'sub_admin'
        );
        Response::success(['is_active' => (int)$newStatus], "{$label}สิทธิ์สำเร็จ");
    }

    // ──────────────────────────────────────────────────────
    // DELETE  — POST  (admin only)
    // ──────────────────────────────────────────────────────

    public function deleteRecord(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $id = (int)($this->input()['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id');

        $model = $this->model('SubAdminModel');
        if (!$model->has(['id' => $id])) Response::error('ไม่พบข้อมูลที่ต้องการลบ', 404);

        $model->deleteRecord($id);
        Auth::logActivity(
            (int)$this->currentUser['id'], 'delete', 'sub_admin',
            "ลบระเบียนสิทธิ์ผู้ดูแลย่อย ID: {$id}",
            $id, 'sub_admin'
        );
        Response::success(null, 'ลบสำเร็จ');
    }

    // ──────────────────────────────────────────────────────
    // UPDATE PERMISSIONS  — POST  (admin only)
    // ──────────────────────────────────────────────────────

    public function updatePermissions(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $input  = $this->input();
        $userId = (int)($input['user_id'] ?? 0);
        $area   = $input['area'] ?? '';

        if (!$userId) Response::error('กรุณาระบุ user_id');
        if (!$this->validArea($area)) Response::error('area ไม่ถูกต้อง');

        $permissions = $input['permissions'] ?? [];
        if (!is_array($permissions)) Response::error('permissions ต้องเป็น array');

        $valid = array_values(array_intersect($permissions, SubAdminModel::AREAS[$area]));

        $model = $this->model('SubAdminModel');
        if (!$model->findBy(['user_id' => $userId, 'area' => $area])) {
            Response::error('ไม่พบระเบียนสิทธิ์ที่ต้องการแก้ไข', 404);
        }

        $model->updatePermissions($userId, $area, $valid);
        Auth::logActivity(
            (int)$this->currentUser['id'], 'update', 'sub_admin',
            "แก้ไขสิทธิ์ {$this->areaLabel($area)} ของ user ID: {$userId}",
            $userId, 'sub_admin'
        );
        Response::success(null, 'อัปเดตสิทธิ์สำเร็จ');
    }

    // ──────────────────────────────────────────────────────
    // MY PERMISSIONS  — GET  (member | admin)
    // ──────────────────────────────────────────────────────

    /**
     * Returns all areas where the current user has active sub-admin rights.
     * Also used by the frontend to decide whether to show admin nav links.
     */
    public function myPermissions(): void
    {
        if (!$this->currentUser) {
            Response::error('กรุณาเข้าสู่ระบบ', 401);
        }

        if ($this->currentUser['role'] === 'admin') {
            // Admin has all permissions
            $areas = [];
            foreach (SubAdminModel::AREAS as $area => $perms) {
                $areas[$area] = $perms;
            }
            Response::success([
                'is_sub_admin' => true,
                'areas'        => $areas,
            ]);
            return;
        }

        $model = $this->model('SubAdminModel');
        $areas = $model->getMyAreas((int)$this->currentUser['id']);

        Response::success([
            'is_sub_admin' => !empty($areas),
            'areas'        => $areas,
        ]);
    }

    // ──────────────────────────────────────────────────────
    // AREA DEFINITIONS  — GET  (admin only, for UI)
    // ──────────────────────────────────────────────────────

    public function areaDefinitions(): void
    {
        $this->requireAdmin();

        $defs = [];
        $labels = [
            'members'    => 'บริหารจัดการสมาชิก',
            'news'       => 'จัดการข่าวสาร',
            'activities' => 'จัดการกิจกรรม',
        ];
        $permLabels = [
            'view'    => 'ดูข้อมูล',
            'approve' => 'อนุมัติ/ระงับ',
            'create'  => 'สร้าง',
            'edit'    => 'แก้ไข',
            'delete'  => 'ลบ',
        ];

        foreach (SubAdminModel::AREAS as $area => $perms) {
            $defs[] = [
                'area'        => $area,
                'label'       => $labels[$area] ?? $area,
                'permissions' => array_map(fn($p) => [
                    'key'   => $p,
                    'label' => $permLabels[$p] ?? $p,
                ], $perms),
            ];
        }

        Response::success($defs);
    }

    // ──────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────

    private function areaLabel(string $area): string
    {
        return [
            'members'    => 'บริหารจัดการสมาชิก',
            'news'       => 'จัดการข่าวสาร',
            'activities' => 'จัดการกิจกรรม',
        ][$area] ?? $area;
    }

    // ──────────────────────────────────────────────────────
    // STAFF USERS — CREATE  (admin only)
    // ──────────────────────────────────────────────────────

    /**
     * POST  ?controller=sub-admin&action=create-staff-user
     * สร้างบัญชีผู้ดูแล (is_staff=1) โดยไม่ต้องสมัครสมาชิก
     */
    public function createStaffUser(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $input    = $this->input();
        $fullName = trim($input['full_name'] ?? '');
        $email    = trim($input['email'] ?? '');

        if ($fullName === '') Response::error('กรุณาระบุชื่อ-นามสกุล');
        if ($email === '')    Response::error('กรุณาระบุอีเมล');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) Response::error('รูปแบบอีเมลไม่ถูกต้อง');

        $users = $this->model('UserModel');

        // Reject duplicates
        if ($users->findByEmail($email)) Response::error('อีเมลนี้ถูกใช้งานแล้ว');

        // Generate unique username from email prefix
        $emailPrefix = strtolower(preg_replace('/[^a-z0-9]/i', '', explode('@', $email)[0]));
        if ($emailPrefix === '') $emailPrefix = 'staff';
        $username = $emailPrefix;
        $suffix   = 1;
        while ($users->findBy(['username' => $username])) {
            $username = $emailPrefix . $suffix++;
        }

        $userId = $users->create([
            'username'  => $username,
            'email'     => $email,
            'full_name' => $fullName,
            'role'      => 'member',
            'status'    => 'active',
            'is_staff'  => 1,
        ]);

        // Set password (provided or auto-generated)
        $passwordPlain = trim($input['password'] ?? '');
        if ($passwordPlain && strlen($passwordPlain) < 6) Response::error('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        if (!$passwordPlain) {
            $chars = 'ABCDEFGHJKMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
            $passwordPlain = '';
            for ($i = 0; $i < 10; $i++) {
                $passwordPlain .= $chars[random_int(0, strlen($chars) - 1)];
            }
        }
        $users->setPassword($userId, Auth::hashPassword($passwordPlain));

        Auth::logActivity(
            (int)$this->currentUser['id'], 'create', 'staff_user',
            "สร้างบัญชีผู้ดูแล: {$fullName} ({$email})",
            $userId, 'user'
        );
        Response::success([
            'id'             => $userId,
            'full_name'      => $fullName,
            'username'       => $username,
            'password_plain' => $passwordPlain,
        ], 'สร้างบัญชีผู้ดูแลสำเร็จ');
    }

    // ──────────────────────────────────────────────────────
    // STAFF USERS — LIST  (admin only)
    // ──────────────────────────────────────────────────────

    /**
     * GET  ?controller=sub-admin&action=list-staff-users
     */
    public function listStaffUsers(): void
    {
        $this->requireAdmin();
        $model = $this->model('SubAdminModel');
        Response::success($model->getStaffUsers());
    }

    // ──────────────────────────────────────────────────────
    // STAFF USERS — DELETE  (admin only)
    // ──────────────────────────────────────────────────────

    /**
     * POST  ?controller=sub-admin&action=delete-staff-user
     */
    public function deleteStaffUser(): void
    {
        $this->requirePost();
        $this->requireAdmin();

        $userId = (int)($this->input()['user_id'] ?? 0);
        if (!$userId) Response::error('กรุณาระบุ user_id');

        $users = $this->model('UserModel');
        $user  = $users->find($userId, ['id', 'full_name', 'email', 'is_staff']);
        if (!$user || !(int)($user['is_staff'] ?? 0)) Response::error('ไม่พบบัญชีผู้ดูแล', 404);

        // Remove all sub-admin assignments first, then delete user
        $this->model('SubAdminModel')->deleteAllForUser($userId);
        $users->delete(['id' => $userId]);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'delete', 'staff_user',
            "ลบบัญชีผู้ดูแล: " . ($user['full_name'] ?? '') . " ({$userId})",
            $userId, 'user'
        );
        Response::success(null, 'ลบบัญชีผู้ดูแลสำเร็จ');
    }
}
