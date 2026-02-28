<?php
namespace App\Core;

/**
 * TelegramBot - Extended Telegram Bot API functions
 * รองรับ webhook, inline keyboards, และ callback queries
 */
class TelegramBot extends Telegram {
    
    /**
     * ส่งข้อความพร้อม inline keyboard
     * @param string $chatId
     * @param string $text
     * @param array $keyboard
     * @return array
     */
    public static function sendMessage($chatId, $text, $keyboard = null) {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ];
        
        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }
        
        return self::sendRequest($url, $payload);
    }
    
    /**
     * ตอบกลับ callback query
     * @param string $callbackQueryId
     * @param string $text
     * @param bool $showAlert
     * @return array
     */
    public static function answerCallbackQuery($callbackQueryId, $text = '', $showAlert = false) {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/answerCallbackQuery";
        
        $payload = [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
            'show_alert' => $showAlert
        ];
        
        return self::sendRequest($url, $payload);
    }
    
    /**
     * แก้ไขข้อความ
     * @param string $chatId
     * @param int $messageId
     * @param string $text
     * @param array $keyboard
     * @return array
     */
    public static function editMessage($chatId, $messageId, $text, $keyboard = null) {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/editMessageText";
        
        $payload = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ];
        
        if ($keyboard) {
            $payload['reply_markup'] = json_encode($keyboard);
        }
        
        return self::sendRequest($url, $payload);
    }
    
    /**
     * ส่ง approval message พร้อมปุ่ม
     * @param string $chatId
     * @param array $data
     * @return array
     */
    public static function sendApprovalMessage($chatId, $data) {
        $text = "🔔 *รายการรอการอนุมัติ*\n\n";
        $text .= "📋 ประเภท: {$data['type']}\n";
        $text .= "👤 ชื่อ: {$data['member_name']}\n";
        $text .= "💰 จำนวน: " . number_format($data['amount'], 2) . " บาท\n";
        $text .= "📅 วันที่: {$data['date']}\n";
        
        if (!empty($data['description'])) {
            $text .= "📝 รายละเอียด: {$data['description']}\n";
        }

        if (!empty($data['slip_url'])) {
            $text .= "\n🖼️ [ดูหลักฐาน]({$data['slip_url']})";
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => '✅ อนุมัติ',
                        'callback_data' => json_encode([
                            'action' => 'approve',
                            'type' => $data['type'],
                            'id' => $data['id']
                        ])
                    ],
                    [
                        'text' => '❌ ปฏิเสธ',
                        'callback_data' => json_encode([
                            'action' => 'reject',
                            'type' => $data['type'],
                            'id' => $data['id']
                        ])
                    ]
                ],
                [
                    [
                        'text' => '📱 ดูรายละเอียด',
                        'url' => $data['detail_url'] ?? '#'
                    ]
                ]
            ]
        ];

        return self::sendMessage($chatId, $text, $keyboard);
    }
    
    /**
     * ตั้งค่า webhook
     * @param string $webhookUrl
     * @return array
     */
    public static function setWebhook($webhookUrl) {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        
        $payload = [
            'url' => $webhookUrl,
            'max_connections' => 40,
            'allowed_updates' => ['message', 'callback_query']
        ];
        
        return self::sendRequest($url, $payload);
    }
    
    /**
     * ลบ webhook
     * @return array
     */
    public static function deleteWebhook() {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/deleteWebhook";
        
        return self::sendRequest($url, []);
    }
    
    /**
     * ดูข้อมูล webhook ปัจจุบัน
     * @return array
     */
    public static function getWebhookInfo() {
        $settings = new \App\Models\SettingsModel();
        $token = $settings->get('member_bot_token', '');
        
        if (empty($token)) {
            return ['success' => false, 'message' => 'Member bot token not configured'];
        }

        $url = "https://api.telegram.org/bot{$token}/getWebhookInfo";
        
        return self::sendRequest($url, []);
    }
    
    /**
     * ส่ง HTTP request ไปยัง Telegram API
     * @param string $url
     * @param array $payload
     * @return array
     */
    private static function sendRequest($url, $payload) {
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
                'message' => 'cURL Error: ' . $error
            ];
        }
        
        $data = json_decode($response, true);
        
        if ($httpCode === 200 && isset($data['ok']) && $data['ok'] === true) {
            return [
                'success' => true,
                'data' => $data['result'] ?? [],
                'message' => 'Success'
            ];
        } else {
            return [
                'success' => false,
                'message' => $data['description'] ?? 'Unknown error',
                'error_code' => $data['error_code'] ?? $httpCode,
                'response' => $response
            ];
        }
    }
    
    /**
     * ส่งการแจ้งเตือนไปยัง admin chat ids
     * @param string $message
     * @param array $keyboard
     * @return array
     */
    public static function notifyAdmins($message, $keyboard = null) {
        $adminChatIds = explode(',', defined('TELEGRAM_ADMIN_CHAT_IDS') ? TELEGRAM_ADMIN_CHAT_IDS : '');
        $results = [];
        
        foreach ($adminChatIds as $chatId) {
            $chatId = trim($chatId);
            if (!empty($chatId)) {
                $result = self::sendMessage($chatId, $message, $keyboard);
                $results[] = [
                    'chat_id' => $chatId,
                    'success' => $result['success'] ?? false,
                    'message' => $result['message'] ?? 'Unknown'
                ];
            }
        }
        
        return $results;
    }
}