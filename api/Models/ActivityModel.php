<?php
namespace App\Models;

use App\Core\Model;

/**
 * ActivityModel — manages `activities` table
 */
class ActivityModel extends Model
{
    protected string $table = 'activities';

    private array $listCols = [
        'activities.id', 'activities.title', 'activities.description',
        'activities.cover_image', 'activities.location',
        'activities.start_date', 'activities.end_date',
        'activities.max_participants', 'activities.has_fee',
        'activities.fee_amount', 'activities.registration_open',
        'activities.require_approval', 'activities.status',
        'activities.visibility', 'activities.visibility_text',
        'activities.allowed_member_types',
        'activities.created_at',
        'users.full_name(created_by_name)'
    ];

    private array $detailCols = [
        'activities.id', 'activities.title', 'activities.description',
        'activities.cover_image', 'activities.location',
        'activities.start_date', 'activities.end_date',
        'activities.max_participants', 'activities.has_fee',
        'activities.fee_amount', 'activities.fee_description',
        'activities.registration_open', 'activities.require_approval',
        'activities.status', 'activities.visibility', 'activities.visibility_text',
        'activities.allowed_member_types',
        'activities.created_by',
        'activities.created_at', 'activities.updated_at',
        'users.full_name(created_by_name)'
    ];

    private array $join = [
        '[>]users' => ['created_by' => 'id']
    ];

    public function getList(array $filters, int $page, int $perPage, bool $isAdmin = false): array
    {
        $where = [];

        if (!$isAdmin) {
            // Show all statuses except draft to public visitors
            $where['activities.status'] = ['open', 'closed', 'cancelled'];
        } elseif (!empty($filters['status'])) {
            $where['activities.status'] = $filters['status'];
        }

        if (!empty($filters['upcoming'])) {
            // Include activities whose end_date hasn't passed yet,
            // or start_date is still in the future
            $now = date('Y-m-d H:i:s');
            $where['AND #upcoming'] = [
                'OR' => [
                    'activities.start_date[>]' => $now,
                    'activities.end_date[>]'   => $now,
                ]
            ];
        }

        if (!empty($filters['past'])) {
            $now = date('Y-m-d H:i:s');
            $where['activities.end_date[<=]'] = $now;
            // Also include activities with no end_date where start_date has passed
            // but Medoo doesn't support complex OR easily, so just use end_date
        }

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'activities.title[~]'    => $s,
                'activities.location[~]' => $s,
            ];
        }

        $where['ORDER'] = ['activities.start_date' => 'DESC'];

        $countWhere = $where;
        unset($countWhere['ORDER']);
        $total = $this->countJoin($this->join, '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $data = $this->selectJoin($this->join, $this->listCols, $where);

        return compact('data', 'total', 'page', 'perPage');
    }

    public function getDetail(int $id): ?array
    {
        return $this->getJoin($this->join, $this->detailCols, ['activities.id' => $id]);
    }

    /**
     * Count open/upcoming activities the user hasn't registered for
     */
    public function countNewForUser(int $userId): int
    {
        $now = date('Y-m-d H:i:s');

        // Get all open activities that haven't ended yet
        $openActivities = $this->db->select($this->table, 'id', [
            'status'     => 'open',
            'end_date[>]' => $now,
            'registration_open' => 1,
        ]) ?: [];

        if (empty($openActivities)) return 0;

        // Get activities user already registered for
        $registered = $this->db->select('activity_registrations', 'activity_id', [
            'user_id'     => $userId,
            'activity_id' => $openActivities,
        ]) ?: [];

        return count(array_diff($openActivities, $registered));
    }
}
