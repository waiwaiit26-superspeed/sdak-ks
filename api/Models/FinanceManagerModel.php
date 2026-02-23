<?php

namespace App\Models;

use App\Core\Model;

class FinanceManagerModel extends Model
{
    protected string $table = 'finance_managers';

    private array $join = [
        '[>]users' => ['user_id' => 'id'],
        '[>]users(assigner)' => ['assigned_by' => 'id'],
    ];

    private array $listCols = [
        'finance_managers.id',
        'finance_managers.user_id',
        'finance_managers.assigned_by',
        'finance_managers.permissions',
        'finance_managers.is_active',
        'finance_managers.created_at',
        'finance_managers.updated_at',
        'users.full_name(user_name)',
        'users.email(user_email)',
        'users.role(user_role)',
        'users.status(user_status)',
        'assigner.full_name(assigner_name)',
    ];

    /**
     * รายชื่อผู้จัดการการเงิน
     */
    public function getList(): array
    {
        return $this->selectJoin($this->join, $this->listCols, [
            'ORDER' => ['finance_managers.created_at' => 'DESC'],
        ]);
    }

    /**
     * ตรวจสอบว่า user เป็นผู้จัดการการเงินหรือไม่
     */
    public function isFinanceManager(int $userId): bool
    {
        return $this->has(['user_id' => $userId, 'is_active' => 1]);
    }

    /**
     * ดึงข้อมูลผู้จัดการการเงินจาก user_id
     */
    public function getByUserId(int $userId): ?array
    {
        $result = $this->findBy(['user_id' => $userId, 'is_active' => 1]);
        if ($result && !empty($result['permissions'])) {
            $result['permissions'] = json_decode($result['permissions'], true) ?: [];
        }
        return $result ?: null;
    }

    /**
     * ตรวจสอบสิทธิ์เฉพาะด้าน
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        $manager = $this->getByUserId($userId);
        if (!$manager) return false;
        $perms = $manager['permissions'] ?? [];
        return in_array($permission, $perms, true);
    }

    /**
     * มอบสิทธิ์ผู้จัดการการเงิน
     */
    public function assign(int $userId, int $assignedBy, array $permissions = []): int
    {
        if (empty($permissions)) {
            $permissions = ['create', 'edit', 'delete', 'export'];
        }

        // Check if already exists
        $existing = $this->findBy(['user_id' => $userId]);
        if ($existing) {
            $this->update([
                'assigned_by' => $assignedBy,
                'permissions' => json_encode($permissions),
                'is_active' => 1,
            ], ['user_id' => $userId]);
            return $existing['id'];
        }

        return $this->create([
            'user_id' => $userId,
            'assigned_by' => $assignedBy,
            'permissions' => json_encode($permissions),
            'is_active' => 1,
        ]);
    }

    /**
     * ถอนสิทธิ์
     */
    public function revoke(int $userId): void
    {
        $this->update(['is_active' => 0], ['user_id' => $userId]);
    }

    /**
     * ดึงรายชื่อ user ที่เป็น member แต่ยังไม่ได้เป็นผู้จัดการการเงิน
     */
    public function getAvailableMembers(): array
    {
        $sql = "SELECT u.id, u.full_name, u.email, u.role
                FROM users u
                WHERE u.status = 'active'
                AND u.id NOT IN (
                    SELECT fm.user_id FROM finance_managers fm WHERE fm.is_active = 1
                )
                ORDER BY u.full_name ASC";
        return $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }
}
