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
        $telegramStatus = $filters['telegram_status'] ?? '';
        $memberType = $filters['member_type'] ?? '';
        $page = max(1, intval($filters['page'] ?? 1));
        $limit = max(10, min(100, intval($filters['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        // สร้าง WHERE clause
        $where = [];

        if (!empty($search)) {
            $where['OR'] = [
                'full_name[~]' => $search,
                'email[~]' => $search,
                'phone[~]' => $search,
                'member_number[~]' => $search,
            ];
        }

        if (!empty($status)) {
            $where['status'] = $status;
        }

        if ($telegramStatus === 'linked') {
            $where['telegram_chat_id[!]'] = null;
        } elseif ($telegramStatus === 'not_linked') {
            $where['telegram_chat_id'] = null;
        }

        if (!empty($memberType)) {
            $where['member_type'] = $memberType;
        }

        // นับจำนวนทั้งหมด (ตาม filter)
        $total = $this->db->count('users', $where ?: null);

        // นับสมาชิกที่มี Telegram (ตาม filter)
        $tgWhere = $where;
        $tgWhere['telegram_chat_id[!]'] = null;
        $totalTelegram = $this->db->count('users', $tgWhere);

        // ดึงข้อมูล
        $members = $this->db->select('users', [
            'id',
            'full_name',
            'email',
            'phone',
            'member_number',
            'member_type',
            'status',
            'profile_image',
            'telegram_chat_id',
            'telegram_linked_at',
        ], array_merge($where, [
            'ORDER' => ['full_name' => 'ASC'],
            'LIMIT' => [$offset, $limit],
        ]));

        return [
            'members' => $members ?: [],
            'total' => $total,
            'total_telegram' => $totalTelegram,
            'pagination' => [
                'total' => $total,
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => max(1, ceil($total / $limit)),
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
