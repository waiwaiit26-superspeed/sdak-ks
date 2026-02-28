<?php
namespace App\Models;

use App\Core\Model;
use Exception;

class TelegramBotModel extends Model {

    /**
     * ดึงข้อมูล Bot ทั้งหมด
     * @return array
     */
    public function getAllBots() {
        return $this->db->select('telegram_bots', '*', [
            'ORDER' => ['type' => 'ASC']
        ]);
    }

    /**
     * ดึงข้อมูล Bot ตามประเภท
     * @param string $type
     * @return array|null
     */
    public function getBotByType($type) {
        return $this->db->get('telegram_bots', '*', [
            'type' => $type
        ]);
    }

    /**
     * ดึงข้อมูล Bot ที่เปิดใช้งานตามประเภท
     * @param string $type
     * @return array|null
     */
    public function getActiveBotByType($type) {
        return $this->db->get('telegram_bots', '*', [
            'type' => $type,
            'is_active' => true
        ]);
    }

    /**
     * อัปเดตข้อมูล Bot
     * @param string $type
     * @param array $data
     * @return array
     */
    public function updateBot($type, $data) {
        try {
            // ทำความสะอาดข้อมูล
            $cleanData = [];
            
            $allowedFields = [
                'name', 'bot_token', 'bot_username', 'webhook_url', 
                'webhook_secret', 'chat_id', 'admin_chat_ids', 
                'is_active', 'description', 'settings'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $cleanData[$field] = $data[$field];
                }
            }

            // แปลง admin_chat_ids เป็น JSON ถ้าเป็น array
            if (isset($cleanData['admin_chat_ids']) && is_array($cleanData['admin_chat_ids'])) {
                $cleanData['admin_chat_ids'] = json_encode($cleanData['admin_chat_ids']);
            }

            // แปลง settings เป็น JSON ถ้าเป็น array
            if (isset($cleanData['settings']) && is_array($cleanData['settings'])) {
                $cleanData['settings'] = json_encode($cleanData['settings'], JSON_UNESCAPED_UNICODE);
            }

            // แปลง is_active เป็น boolean
            if (isset($cleanData['is_active'])) {
                $cleanData['is_active'] = (bool) $cleanData['is_active'];
            }

            $result = $this->db->update('telegram_bots', $cleanData, [
                'type' => $type
            ]);

            if ($result) {
                return [
                    'success' => true,
                    'message' => 'อัปเดตข้อมูล Bot สำเร็จ',
                    'data' => $this->getBotByType($type)
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถอัปเดตข้อมูลได้'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * เปิด/ปิดการใช้งาน Bot
     * @param string $type
     * @param bool $isActive
     * @return array
     */
    public function toggleBotStatus($type, $isActive) {
        try {
            $result = $this->db->update('telegram_bots', [
                'is_active' => (bool) $isActive
            ], [
                'type' => $type
            ]);

            if ($result) {
                $status = $isActive ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
                return [
                    'success' => true,
                    'message' => "{$status} Bot เรียบร้อย"
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'ไม่สามารถเปลี่ยนสถานะได้'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ทดสอบการเชื่อมต่อ Bot
     * @param string $type
     * @return array
     */
    public function testBot($type) {
        try {
            $bot = $this->getBotByType($type);
            
            if (!$bot || empty($bot['bot_token'])) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบข้อมูล Bot หรือไม่ได้ตั้งค่า Token'
                ];
            }

            // ทดสอบ getMe API
            $url = "https://api.telegram.org/bot{$bot['bot_token']}/getMe";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error
                ];
            }

            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => "HTTP Error: {$httpCode}"
                ];
            }

            $data = json_decode($response, true);

            if (!$data || !isset($data['ok']) || !$data['ok']) {
                return [
                    'success' => false,
                    'message' => $data['description'] ?? 'Token ไม่ถูกต้อง'
                ];
            }

            $botInfo = $data['result'];

            return [
                'success' => true,
                'message' => 'เชื่อมต่อ Bot สำเร็จ',
                'bot_info' => [
                    'id' => $botInfo['id'],
                    'first_name' => $botInfo['first_name'],
                    'username' => $botInfo['username'] ?? null,
                    'can_join_groups' => $botInfo['can_join_groups'] ?? false,
                    'can_read_all_group_messages' => $botInfo['can_read_all_group_messages'] ?? false,
                    'supports_inline_queries' => $botInfo['supports_inline_queries'] ?? false
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ตั้งค่า Webhook
     * @param string $type
     * @param string $webhookUrl
     * @return array
     */
    public function setWebhook($type, $webhookUrl) {
        try {
            $bot = $this->getBotByType($type);
            
            if (!$bot || empty($bot['bot_token'])) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบข้อมูl Bot หรือไม่ได้ตั้งค่า Token'
                ];
            }

            $url = "https://api.telegram.org/bot{$bot['bot_token']}/setWebhook";
            
            $payload = [
                'url' => $webhookUrl,
                'max_connections' => 40,
                'allowed_updates' => ['message', 'callback_query']
            ];

            if (!empty($bot['webhook_secret'])) {
                $payload['secret_token'] = $bot['webhook_secret'];
            }

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error
                ];
            }

            $data = json_decode($response, true);

            if ($httpCode === 200 && isset($data['ok']) && $data['ok']) {
                // อัปเดต webhook_url ในฐานข้อมูล
                $this->db->update('telegram_bots', [
                    'webhook_url' => $webhookUrl
                ], [
                    'type' => $type
                ]);

                return [
                    'success' => true,
                    'message' => 'ตั้งค่า Webhook สำเร็จ',
                    'description' => $data['description'] ?? 'Webhook is set'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $data['description'] ?? 'ไม่สามารถตั้งค่า Webhook ได้'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ลบ Webhook
     * @param string $type
     * @return array
     */
    public function deleteWebhook($type) {
        try {
            $bot = $this->getBotByType($type);
            
            if (!$bot || empty($bot['bot_token'])) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบข้อมูล Bot หรือไม่ได้ตั้งค่า Token'
                ];
            }

            $url = "https://api.telegram.org/bot{$bot['bot_token']}/deleteWebhook";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode([]),
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error
                ];
            }

            $data = json_decode($response, true);

            if ($httpCode === 200 && isset($data['ok']) && $data['ok']) {
                // ล้าง webhook_url ในฐานข้อมูล
                $this->db->update('telegram_bots', [
                    'webhook_url' => null
                ], [
                    'type' => $type
                ]);

                return [
                    'success' => true,
                    'message' => 'ลบ Webhook สำเร็จ'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $data['description'] ?? 'ไม่สามารถลบ Webhook ได้'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ดึงข้อมูล Webhook ปัจจุบัน
     * @param string $type
     * @return array
     */
    public function getWebhookInfo($type) {
        try {
            $bot = $this->getBotByType($type);
            
            if (!$bot || empty($bot['bot_token'])) {
                return [
                    'success' => false,
                    'message' => 'ไม่พบข้อมูล Bot หรือไม่ได้ตั้งค่า Token'
                ];
            }

            $url = "https://api.telegram.org/bot{$bot['bot_token']}/getWebhookInfo";
            
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_SSL_VERIFYPEER => true
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error
                ];
            }

            $data = json_decode($response, true);

            if ($httpCode === 200 && isset($data['ok']) && $data['ok']) {
                return [
                    'success' => true,
                    'webhook_info' => $data['result']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $data['description'] ?? 'ไม่สามารถดึงข้อมูล Webhook ได้'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ดึงข้อมูล Admin Chat IDs เป็น array
     * @param string $type
     * @return array
     */
    public function getAdminChatIds($type) {
        $bot = $this->getBotByType($type);
        
        if (!$bot || empty($bot['admin_chat_ids'])) {
            return [];
        }

        $chatIds = json_decode($bot['admin_chat_ids'], true);
        
        if (!is_array($chatIds)) {
            // ถ้าไม่ใช่ JSON ให้แยกด้วย comma
            $chatIds = explode(',', $bot['admin_chat_ids']);
            $chatIds = array_map('trim', $chatIds);
            $chatIds = array_filter($chatIds);
        }

        return $chatIds;
    }
}