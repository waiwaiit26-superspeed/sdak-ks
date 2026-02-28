<?php
namespace App\Models;

use App\Core\Model;

class TelegramMessageModel extends Model {

    /**
     * ดึงรายชื่อสมาชิกพร้อมสถานะ Telegram
     * @param array $filters ['search', 'status', 'telegram_status', 'member_type', 'page', 'limit']
     * @return array ['members' => [], 'total' => int, 'total_telegram' => int]
     */
    public function getMembers(array $filters = []) {
        $search = $filters['search'] ?? '';
        $status = $filters['status'] ?? '';
        $telegramStatus = $filters['telegram_status'] ?? ''; // linked, not_linked
        $memberType = $filters['member_type'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $limit = max(10, min(100, intval($filters['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $where = ['AND' => []];

        // ค้นหา
        if (!empty($search)) {
            $where['AND']['OR'] = [
                'users.full_name[~]' => $search,
                'users.email[~]' => $search,
                'users.phone[~]' => $search,
                'users.member_number[~]' => $search,
            ];
        }

        // สถานะสมาชิก
        if (!empty($status)) {
            $where['AND']['users.status'] = $status;
        }

        // สถานะ Telegram
        if ($telegramStatus === 'linked') {
            $where['AND']['users.telegram_chat_id[!]'] = null;
        } elseif ($telegramStatus === 'not_linked') {
            $where['AND']['users.telegram_chat_id'] = null;
        }

        // ประเภทสมาชิก
        if (!empty($memberType)) {
            $where['AND']['users.member_type'] = $memberType;
        }

        // ถ้าไม่มีเงื่อนไข
        if (empty($where['AND'])) {
            $where = [];
        }

        // นับจำนวน
        $total = $this->db->count('users', $where ?: null);

        // นับสมาชิกที่มี Telegram
        $telegramWhere = $where;
        if (empty($telegramWhere)) {
            $telegramWhere = ['telegram_chat_id[!]' => null];
        } else {
            $telegramWhere['AND']['telegram_chat_id[!]'] = null;
        }
        $totalTelegram = $this->db->count('users', $telegramWhere);

        // ดึงข้อมูล
        $queryWhere = $where ?: [];
        $queryWhere['LIMIT'] = [$offset, $limit];
        $queryWhere['ORDER'] = ['users.full_name' => 'ASC'];

        $members = $this->db->select('users', [
            'id',
            'full_name',
            'email',
            'phone',
            'member_number',
            'member_type',
            'status',
            'avatar',
            'telegram_chat_id',
            'telegram_linked_at',
        ], $queryWhere);

        return [
            'members' => $members ?: [],
            'total' => $total,
            'total_telegram' => $totalTelegram,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
            ]
        ];
    }

    /**
     * ดึง chat_id ของสมาชิกที่ระบุ
     * @param array $memberIds
     * @return array [['id' => x, 'full_name' => x, 'telegram_chat_id' => x], ...]
     */
    public function getChatIdsByMemberIds(array $memberIds) {
        if (empty($memberIds)) return [];

        return $this->db->select('users', [
            'id',
            'full_name',
            'telegram_chat_id',
        ], [
            'id' => $memberIds,
            'telegram_chat_id[!]' => null,
        ]);
    }

    /**
     * บันทึกประวัติการส่งข้อความ
     */
    public function logMessage($adminId, $type, $recipientCount, $successCount, $failCount, $messagePreview) {
        $this->db->insert('telegram_message_logs', [
            'admin_id' => $adminId,
            'message_type' => $type,
            'recipient_count' => $recipientCount,
            'success_count' => $successCount,
            'fail_count' => $failCount,
            'message_preview' => mb_substr($messagePreview, 0, 500),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * ดึงประวัติการส่งข้อความ
     */
    public function getMessageLogs($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $total = $this->db->count('telegram_message_logs');

        $logs = $this->db->select('telegram_message_logs', [
            '[>]users' => ['admin_id' => 'id'],
        ], [
            'telegram_message_logs.id',
            'telegram_message_logs.message_type',
            'telegram_message_logs.recipient_count',
            'telegram_message_logs.success_count',
            'telegram_message_logs.fail_count',
            'telegram_message_logs.message_preview',
            'telegram_message_logs.created_at',
            'users.full_name(admin_name)',
        ], [
            'ORDER' => ['telegram_message_logs.created_at' => 'DESC'],
            'LIMIT' => [$offset, $limit],
        ]);

        return [
            'logs' => $logs ?: [],
            'total' => $total,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($total / $limit),
            ]
        ];
    }
}
