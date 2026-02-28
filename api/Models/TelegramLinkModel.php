<?php
namespace App\Models;

use App\Core\Model;
use Exception;

class TelegramLinkModel extends Model {

    /**
     * สร้าง one-time token สำหรับเชื่อมต่อ Telegram
     * @param int $userId
     * @return array ['success' => bool, 'token' => string|null, 'message' => string]
     */
    public function createLinkToken($userId) {
        try {
            // ลบ token เก่าที่หมดอายุ
            $this->db->delete('telegram_link_tokens', [
                'user_id' => $userId,
                'expires_at[<]' => date('Y-m-d H:i:s')
            ]);

            // ลบ token เก่าที่ยังไม่ได้ใช้
            $this->db->delete('telegram_link_tokens', [
                'user_id' => $userId,
                'used_at' => null
            ]);

            // สร้าง token ใหม่
            $token = 'tg_' . bin2hex(random_bytes(20));
            $expiresAt = date('Y-m-d H:i:s', time() + 600); // หมดอายุใน 10 นาทีี

            $result = $this->db->insert('telegram_link_tokens', [
                'user_id' => $userId,
                'token' => $token,
                'expires_at' => $expiresAt
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'token' => $token,
                    'expires_at' => $expiresAt,
                    'message' => 'Token created successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'token' => null,
                    'message' => 'Failed to create token'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'token' => null,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ตรวจสอบและใช้ token เพื่อเชื่อมต่อ Telegram
     * @param string $token
     * @param int $telegramChatId
     * @return array ['success' => bool, 'user_id' => int|null, 'message' => string]
     */
    public function linkTelegramAccount($token, $telegramChatId) {
        try {
            // หา token ที่ยังใช้ได้
            $tokenData = $this->db->get('telegram_link_tokens', [
                '[>]users' => ['user_id' => 'id']
            ], [
                'telegram_link_tokens.id',
                'telegram_link_tokens.user_id',
                'telegram_link_tokens.expires_at',
                'telegram_link_tokens.used_at',
                'users.full_name',
                'users.email'
            ], [
                'telegram_link_tokens.token' => $token,
                'telegram_link_tokens.used_at' => null
            ]);

            if (!$tokenData) {
                return [
                    'success' => false,
                    'user_id' => null,
                    'message' => 'Token not found or already used'
                ];
            }

            // ตรวจสอบว่า token หมดอายุหรือยัง
            if (strtotime($tokenData['expires_at']) < time()) {
                return [
                    'success' => false,
                    'user_id' => null,
                    'message' => 'Token expired'
                ];
            }

            $userId = $tokenData['user_id'];

            // ตรวจสอบว่า chat_id นี้เชื่อมกับบัญชีอื่นแล้วหรือไม่
            $existingUser = $this->db->get('users', 'id', [
                'telegram_chat_id' => $telegramChatId
            ]);

            if ($existingUser && $existingUser != $userId) {
                return [
                    'success' => false,
                    'user_id' => null,
                    'message' => 'This Telegram account is already linked to another user'
                ];
            }

            // เริ่ม transaction
            $this->db->pdo->beginTransaction();

            // อัปเดต user ให้เชื่อมต่อ Telegram
            $updateResult = $this->db->update('users', [
                'telegram_chat_id' => $telegramChatId,
                'telegram_linked_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $userId
            ]);

            if (!$updateResult) {
                $this->db->pdo->rollback();
                return [
                    'success' => false,
                    'user_id' => null,
                    'message' => 'Failed to update user'
                ];
            }

            // ทำเครื่องหมายว่า token ถูกใช้แล้ว
            $this->db->update('telegram_link_tokens', [
                'used_at' => date('Y-m-d H:i:s')
            ], [
                'id' => $tokenData['id']
            ]);

            $this->db->pdo->commit();

            return [
                'success' => true,
                'user_id' => $userId,
                'user_name' => $tokenData['full_name'],
                'user_email' => $tokenData['email'],
                'message' => 'Telegram account linked successfully'
            ];

        } catch (Exception $e) {
            if ($this->db->pdo->inTransaction()) {
                $this->db->pdo->rollback();
            }
            return [
                'success' => false,
                'user_id' => null,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ยกเลิกการเชื่อมต่อ Telegram
     * @param int $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function unlinkTelegramAccount($userId) {
        try {
            $result = $this->db->update('users', [
                'telegram_chat_id' => null,
                'telegram_linked_at' => null
            ], [
                'id' => $userId
            ]);

            if ($result) {
                // ลบ tokens ที่เก่า
                $this->db->delete('telegram_link_tokens', [
                    'user_id' => $userId
                ]);

                return [
                    'success' => true,
                    'message' => 'Telegram account unlinked successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Failed to unlink Telegram account'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ดึงข้อมูล Telegram ของผู้ใช้
     * @param int $userId
     * @return array|null
     */
    public function getTelegramInfo($userId) {
        return $this->db->get('users', [
            'telegram_chat_id',
            'telegram_linked_at'
        ], [
            'id' => $userId
        ]);
    }

    /**
     * ล้าง tokens ที่หมดอายุ (สำหรับ cleanup job)
     * @return int จำนวน tokens ที่ถูกลบ
     */
    public function cleanupExpiredTokens() {
        return $this->db->delete('telegram_link_tokens', [
            'expires_at[<]' => date('Y-m-d H:i:s')
        ])->rowCount();
    }

    /**
     * ดึงรายการสมาชิกที่เชื่อมต่อ Telegram (สำหรับ admin)
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getLinkedMembers($search = '', $limit = 20, $offset = 0) {
        $where = [
            'telegram_chat_id[!]' => null
        ];

        if (!empty($search)) {
            $where['OR'] = [
                'full_name[~]' => $search,
                'email[~]' => $search,
                'school_organization[~]' => $search
            ];
        }

        $members = $this->db->select('users', [
            'id',
            'full_name',
            'email',
            'school_organization', 
            'telegram_chat_id',
            'telegram_linked_at',
            'status'
        ], array_merge($where, [
            'ORDER' => ['telegram_linked_at' => 'DESC'],
            'LIMIT' => [$offset, $limit]
        ]));

        $total = $this->db->count('users', $where);

        return [
            'members' => $members,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset
        ];
    }
}