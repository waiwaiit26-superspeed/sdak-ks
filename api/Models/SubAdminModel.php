<?php

namespace App\Models;

use App\Core\Model;

class SubAdminModel extends Model
{
    protected string $table = 'sub_admins';

    /**
     * Valid areas and their allowed permissions
     */
    public const AREAS = [
        'members'    => ['view', 'approve', 'create', 'edit', 'delete', 'fees'],
        'news'       => ['create', 'edit', 'delete'],
        'activities' => ['create', 'edit', 'delete'],
        'finance'    => ['view', 'create', 'edit', 'delete', 'export'],
    ];

    private array $join = [
        '[>]users'            => ['user_id' => 'id'],
        '[>]users(assigner)'  => ['assigned_by' => 'id'],
    ];

    private array $listCols = [
        'sub_admins.id',
        'sub_admins.user_id',
        'sub_admins.area',
        'sub_admins.permissions',
        'sub_admins.is_active',
        'sub_admins.note',
        'sub_admins.created_at',
        'sub_admins.updated_at',
        'sub_admins.assigned_by',
        'users.full_name(user_name)',
        'users.email(user_email)',
        'users.role(user_role)',
        'users.status(user_status)',
        'assigner.full_name(assigner_name)',
    ];

    // ──────────────────────────────────────────────────────
    // Query helpers
    // ──────────────────────────────────────────────────────

    /**
     * Get all sub-admin records, optionally filtered by area
     */
    public function getList(?string $area = null): array
    {
        $where = [];
        if ($area && array_key_exists($area, self::AREAS)) {
            $where['sub_admins.area'] = $area;
        }
        $where['ORDER'] = ['sub_admins.area' => 'ASC', 'users.full_name' => 'ASC'];

        $rows = $this->selectJoin($this->join, $this->listCols, $where);
        foreach ($rows as &$row) {
            $row['permissions'] = $this->decodePermissions($row['permissions'] ?? '[]');
        }
        return $rows;
    }

    /**
     * Check if a user is a sub-admin for any area (active record)
     */
    public function isSubAdmin(int $userId): bool
    {
        return $this->has(['user_id' => $userId, 'is_active' => 1]);
    }

    /**
     * Get all active sub-admin areas for a user
     */
    public function getMyAreas(int $userId): array
    {
        $rows = $this->all(['area', 'permissions'], ['user_id' => $userId, 'is_active' => 1]);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['area']] = $this->decodePermissions($row['permissions'] ?? '[]');
        }
        return $result;
    }

    /**
     * Check if a user has a specific permission in a specific area
     */
    public function hasPermission(int $userId, string $area, string $permission): bool
    {
        $row = $this->findBy(['user_id' => $userId, 'area' => $area, 'is_active' => 1]);
        if (!$row) return false;
        $perms = $this->decodePermissions($row['permissions'] ?? '[]');
        return in_array($permission, $perms, true);
    }

    // ──────────────────────────────────────────────────────
    // Mutations
    // ──────────────────────────────────────────────────────

    /**
     * Assign (or re-assign) a user as sub-admin for an area
     */
    public function assign(int $userId, string $area, int $assignedBy, array $permissions = [], string $note = ''): int
    {
        $data = [
            'area'        => $area,
            'permissions' => json_encode(array_values($permissions)),
            'is_active'   => 1,
            'assigned_by' => $assignedBy,
            'note'        => $note,
        ];

        $existing = $this->findBy(['user_id' => $userId, 'area' => $area]);
        if ($existing) {
            $this->update($data, ['user_id' => $userId, 'area' => $area]);
            return (int)$existing['id'];
        }

        $data['user_id'] = $userId;
        return $this->create($data);
    }

    /**
     * Deactivate (soft-delete) a user's sub-admin rights for an area
     */
    public function revoke(int $userId, string $area): void
    {
        $this->update(['is_active' => 0], ['user_id' => $userId, 'area' => $area]);
    }

    /**
     * Toggle active status
     */
    public function toggle(int $userId, string $area): bool
    {
        $row = $this->findBy(['user_id' => $userId, 'area' => $area]);
        if (!$row) return false;
        $newStatus = $row['is_active'] ? 0 : 1;
        $this->update(['is_active' => $newStatus], ['user_id' => $userId, 'area' => $area]);
        return (bool)$newStatus;
    }

    /**
     * Hard-delete a sub-admin record
     */
    public function deleteRecord(int $id): void
    {
        $this->delete(['id' => $id]);
    }

    /**
     * Update permissions for a specific record
     */
    public function updatePermissions(int $userId, string $area, array $permissions): void
    {
        $this->update(
            ['permissions' => json_encode(array_values($permissions))],
            ['user_id' => $userId, 'area' => $area]
        );
    }

    // ──────────────────────────────────────────────────────
    // Available members (not yet assigned to this area)
    // ──────────────────────────────────────────────────────

    public function getAvailableMembers(string $area): array
    {
        $sql = "SELECT u.id, u.full_name, u.email, u.role
                FROM users u
                WHERE u.status = 'active'
                AND u.id NOT IN (
                    SELECT sa.user_id FROM sub_admins sa
                    WHERE sa.area = :area AND sa.is_active = 1
                )
                ORDER BY u.full_name ASC";
        return $this->db->query($sql, [':area' => $area])->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    // ──────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────

    private function decodePermissions($raw): array
    {
        if (is_array($raw)) return $raw;
        return json_decode($raw, true) ?: [];
    }
}
