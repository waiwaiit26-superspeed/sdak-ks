<?php
namespace App\Models;

use App\Core\Model;

/**
 * MemberStatisticModel — manages `member_statistics` table (audit log)
 */
class MemberStatisticModel extends Model
{
    protected string $table = 'member_statistics';

    /**
     * Recent logs with user names (joined)
     */
    public function recentLogs(int $limit = 20): array
    {
        return $this->selectJoin(
            [
                '[>]users(u)' => ['user_id' => 'id'],
                '[>]users(p)' => ['performed_by' => 'id'],
            ],
            [
                'member_statistics.id',
                'member_statistics.action',
                'member_statistics.details',
                'member_statistics.created_at',
                'u.full_name(user_name)',
                'p.full_name(performed_by_name)',
            ],
            [
                'ORDER' => ['member_statistics.created_at' => 'DESC'],
                'LIMIT' => $limit,
            ]
        );
    }

    /**
     * Count actions in a date range
     */
    public function countInRange(string $action, string $from, string $to): int
    {
        return $this->count([
            'action'        => $action,
            'created_at[<>]'=> [$from, $to],
        ]);
    }
}
