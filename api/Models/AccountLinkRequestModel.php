<?php
namespace App\Models;

use App\Core\Model;

/**
 * AccountLinkRequestModel — manages `account_link_requests` table
 */
class AccountLinkRequestModel extends Model
{
    protected string $table = 'account_link_requests';

    /**
     * Get pending requests with target user info joined
     */
    public function getPendingList(int $page = 1, int $perPage = 30): array
    {
        $offset = ($page - 1) * $perPage;
        $rows = $this->db->query("
            SELECT r.*, u.full_name AS target_full_name, u.position AS target_position,
                   u.school_organization AS target_school, u.member_type AS target_member_type,
                   a.full_name AS approver_name
            FROM account_link_requests r
            LEFT JOIN users u ON u.id = r.target_user_id
            LEFT JOIN users a ON a.id = r.approved_by
            ORDER BY r.requested_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ")->fetchAll(\PDO::FETCH_ASSOC);

        $total = (int)$this->db->query("SELECT COUNT(*) FROM account_link_requests")->fetchColumn();
        return ['data' => $rows ?: [], 'total' => $total];
    }

    /**
     * Get single request with joined user info
     */
    public function getWithUser(int $requestId): ?array
    {
        $row = $this->db->query("
            SELECT r.*, u.full_name AS target_full_name, u.email AS target_email,
                   u.google_id AS target_google_id, u.status AS target_status
            FROM account_link_requests r
            LEFT JOIN users u ON u.id = r.target_user_id
            WHERE r.id = {$requestId}
            LIMIT 1
        ")->fetch(\PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Check if a pending request already exists for this target user
     */
    public function hasPending(int $targetUserId): bool
    {
        return $this->has(['target_user_id' => $targetUserId, 'status' => 'pending']);
    }
}
