<?php
namespace App\Models;

use App\Core\Model;

/**
 * ActivityLogModel — manages `activity_logs` table
 * เก็บประวัติการใช้งานระบบทุกประเภท
 */
class ActivityLogModel extends Model
{
    protected string $table = 'activity_logs';

    /**
     * บันทึก log
     */
    public function log(array $data): void
    {
        $data['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? null;
        $data['user_agent'] = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500);
        $this->create($data);
    }

    /**
     * ดึง log ล่าสุดพร้อมชื่อผู้ใช้
     */
    public function recentLogs(int $limit = 50, array $filters = []): array
    {
        $where = [];

        if (!empty($filters['module'])) {
            $where['activity_logs.module'] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where['activity_logs.action'] = $filters['action'];
        }
        if (!empty($filters['user_id'])) {
            $where['activity_logs.user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'activity_logs.action[~]'  => $s,
                'activity_logs.details[~]' => $s,
                'u.full_name[~]'           => $s,
            ];
        }
        if (!empty($filters['date_from'])) {
            $where['activity_logs.created_at[>=]'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where['activity_logs.created_at[<=]'] = $filters['date_to'] . ' 23:59:59';
        }

        $where['ORDER'] = ['activity_logs.created_at' => 'DESC'];
        $where['LIMIT'] = $limit;

        return $this->selectJoin(
            ['[>]users(u)' => ['user_id' => 'id']],
            [
                'activity_logs.id',
                'activity_logs.action',
                'activity_logs.module',
                'activity_logs.target_id',
                'activity_logs.target_type',
                'activity_logs.details',
                'activity_logs.ip_address',
                'activity_logs.created_at',
                'u.full_name',
                'u.role',
                'u.username',
            ],
            $where
        );
    }

    /**
     * Paginated log list
     */
    public function getFilteredList(array $filters, int $page, int $perPage): array
    {
        $where = [];

        if (!empty($filters['module'])) {
            $where['activity_logs.module'] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where['activity_logs.action[~]'] = '%' . $filters['action'] . '%';
        }
        if (!empty($filters['user_id'])) {
            $where['activity_logs.user_id'] = (int)$filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'activity_logs.action[~]'  => $s,
                'activity_logs.details[~]' => $s,
                'u.full_name[~]'           => $s,
            ];
        }
        if (!empty($filters['date_from'])) {
            $where['activity_logs.created_at[>=]'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where['activity_logs.created_at[<=]'] = $filters['date_to'] . ' 23:59:59';
        }

        $where['ORDER'] = ['activity_logs.created_at' => 'DESC'];

        $countWhere = $where;
        unset($countWhere['ORDER']);
        $total = $this->countJoin(['[>]users(u)' => ['user_id' => 'id']], '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];

        $data = $this->selectJoin(
            ['[>]users(u)' => ['user_id' => 'id']],
            [
                'activity_logs.id',
                'activity_logs.action',
                'activity_logs.module',
                'activity_logs.target_id',
                'activity_logs.target_type',
                'activity_logs.details',
                'activity_logs.ip_address',
                'activity_logs.created_at',
                'u.full_name',
                'u.role',
                'u.username',
            ],
            $where
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
}
