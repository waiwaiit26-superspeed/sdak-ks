<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\TelegramLinkModel;
use App\Models\SettingsModel;

class TelegramLinkController extends Controller {
    
    /**
     * สร้าง link token สำหรับ member ที่ล็อกอินอยู่
     * POST /api?controller=telegram-link&action=create-token
     */
    public function createToken() {
        $this->requirePost();

        if (!$this->currentUser) {
            Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = $this->currentUser['id'];
        $userRole = $this->currentUser['role'];

        if (!in_array($userRole, ['member', 'admin'])) {
            Response::forbidden('ไม่มีสิทธิ์ใช้งาน');
        }

        // ดึง bot username จาก Telegram API (ไม่ใช้ค่า default 'YourBot')
        $settings = $this->model('SettingsModel');
        $botToken = $settings->get('member_bot_token', '');
        $botUsername = $settings->get('member_bot_username', '');

        // ถ้ามี token ให้ดึง username จาก Telegram API
        if (!empty($botToken) && empty($botUsername)) {
            $botInfo = $this->getBotInfo($botToken);
            if ($botInfo && !empty($botInfo['username'])) {
                $botUsername = $botInfo['username'];
                // บันทึกไว้ใน settings เพื่อไม่ต้องเรียก API ซ้ำ
                $settings->set('member_bot_username', $botUsername);
            }
        }

        if (empty($botUsername)) {
            Response::error('กรุณาตั้งค่า Bot Token ในหน้า Admin Settings ก่อน');
            return;
        }

        $model = $this->model('TelegramLinkModel');
        $result = $model->createLinkToken($userId);

        if ($result['success']) {
            Response::success([
                'token' => $result['token'],
                'expires_at' => $result['expires_at'],
                'bot_link' => "https://t.me/{$botUsername}?start=link_{$result['token']}"
            ]);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * ดึงสถานะการเชื่อมต่อ Telegram ของผู้ใช้
     * GET /api?controller=telegram-link&action=status
     */
    public function status() {
        try {
            if (!$this->currentUser) {
                Response::unauthorized('กรุณาล็อกอินก่อน');
            }

            $userId = $this->currentUser['id'];
            $model = $this->model('TelegramLinkModel');
            $telegramInfo = null;
            try {
                $telegramInfo = $model->getTelegramInfo($userId);
            } catch (\Throwable $e) {
                error_log('TelegramLinkController getTelegramInfo failed: ' . $e->getMessage());
                $telegramInfo = ['telegram_chat_id' => null, 'telegram_linked_at' => null];
            }

            if (!is_array($telegramInfo)) {
                $telegramInfo = ['telegram_chat_id' => null, 'telegram_linked_at' => null];
            }

            $isLinked = !empty($telegramInfo['telegram_chat_id']);

            // ดึงข้อมูล Bot จาก settings
            $settings = $this->model('SettingsModel');
            $botToken = $settings->get('member_bot_token', '');
            $botUsername = $settings->get('member_bot_username', '');
            $botEnabled = $settings->get('member_bot_enabled', '0');

            // ดึงข้อมูล Bot จาก Telegram API (cache ได้)
            $botInfo = null;
            if (!empty($botToken)) {
                try {
                    $botInfo = $this->getBotInfo($botToken);
                } catch (\Throwable $e) {
                    error_log('TelegramLinkController getBotInfo failed: ' . $e->getMessage());
                    $botInfo = null;
                }
            }

            Response::success([
                'is_linked' => $isLinked,
                'chat_id' => $isLinked ? $telegramInfo['telegram_chat_id'] : null,
                'linked_at' => $telegramInfo['telegram_linked_at'] ?? null,
                'linked_at_thai' => !empty($telegramInfo['telegram_linked_at']) ? 
                    $this->formatThaiDate($telegramInfo['telegram_linked_at']) : null,
                'bot' => $botInfo ? [
                    'name' => $botInfo['first_name'] ?? '',
                    'username' => $botInfo['username'] ?? $botUsername,
                    'photo_url' => $botInfo['photo_url'] ?? null,
                    'enabled' => $botEnabled === '1',
                ] : null
            ]);
        } catch (\Throwable $e) {
            error_log('TelegramLinkController status error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            Response::success([
                'is_linked' => false,
                'chat_id' => null,
                'linked_at' => null,
                'linked_at_thai' => null,
                'bot' => null
            ]);
        }
    }

    /**
     * ยกเลิกการเชื่อมต่อ Telegram
     * POST /api?controller=telegram-link&action=unlink
     */
    public function unlink() {
        $this->requirePost();

        if (!$this->currentUser) {
            Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = $this->currentUser['id'];
        $model = $this->model('TelegramLinkModel');
        $result = $model->unlinkTelegramAccount($userId);

        if ($result['success']) {
            Response::success(['message' => 'ยกเลิกการเชื่อมต่อ Telegram เรียบร้อย']);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * API สำหรับ Bot เรียกเมื่อมี user กด start พร้อม token
     * POST /api?controller=telegram-link&action=process-link
     * Body: {"token": "tg_xxx", "chat_id": 123456789, "bot_secret": "xxx"}
     */
    public function processLink() {
        $data = $this->input();

        if (!$data) {
            Response::error('Invalid JSON data');
        }

        // ตรวจสอบ bot secret จาก settings
        $settings = $this->model('SettingsModel');
        $expectedSecret = $settings->get('member_bot_webhook_secret', '');
        if (empty($expectedSecret) || empty($data['bot_secret']) || $data['bot_secret'] !== $expectedSecret) {
            Response::forbidden('Invalid bot secret');
        }

        $token = $data['token'] ?? '';
        $chatId = $data['chat_id'] ?? 0;

        if (empty($token) || empty($chatId)) {
            Response::error('Token and chat_id are required');
        }

        $model = $this->model('TelegramLinkModel');
        $result = $model->linkTelegramAccount($token, $chatId);

        if ($result['success']) {
            Response::success([
                'user_id' => $result['user_id'],
                'user_name' => $result['user_name'],
                'user_email' => $result['user_email'],
                'message' => $result['message']
            ]);
        } else {
            Response::error($result['message']);
        }
    }

    /**
     * ดูรายการสมาชิกที่เชื่อมต่อ Telegram (สำหรับ admin)
     * GET /api?controller=telegram-link&action=linked-members
     */
    public function linkedMembers() {
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $search = $this->query('search', '');
        $page = max(1, intval($this->query('page', 1)));
        $limit = max(10, min(100, intval($this->query('limit', 20))));
        $offset = ($page - 1) * $limit;

        $model = $this->model('TelegramLinkModel');
        $result = $model->getLinkedMembers($search, $limit, $offset);

        foreach ($result['members'] as &$member) {
            $member['linked_at_thai'] = $this->formatThaiDate($member['telegram_linked_at']);
        }

        Response::success([
            'members' => $result['members'],
            'pagination' => [
                'total' => $result['total'],
                'per_page' => $limit,
                'current_page' => $page,
                'last_page' => ceil($result['total'] / $limit),
                'from' => $offset + 1,
                'to' => min($offset + $limit, $result['total'])
            ]
        ]);
    }

    /**
     * สร้าง link ไปที่ Telegram Bot พร้อม token
     */
    private function generateBotLink($token, $botUsername = null) {
        if (empty($botUsername)) {
            $settings = $this->model('SettingsModel');
            $botUsername = $settings->get('member_bot_username', '');
            
            // ถ้ายังไม่มี ดึงจาก Telegram API
            if (empty($botUsername)) {
                $botToken = $settings->get('member_bot_token', '');
                if (!empty($botToken)) {
                    $botInfo = $this->getBotInfo($botToken);
                    if ($botInfo && !empty($botInfo['username'])) {
                        $botUsername = $botInfo['username'];
                        $settings->set('member_bot_username', $botUsername);
                    }
                }
            }
        }
        return "https://t.me/{$botUsername}?start=link_{$token}";
    }

    /**
     * แปลงวันที่เป็นภาษาไทย
     */
    private function formatThaiDate($datetime) {
        if (!$datetime) return null;

        $thaiMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน', 
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];

        $timestamp = strtotime($datetime);
        $day = date('j', $timestamp);
        $month = $thaiMonths[intval(date('n', $timestamp))];
        $year = date('Y', $timestamp) + 543;
        $time = date('H:i', $timestamp);

        return "{$day} {$month} {$year} เวลา {$time} น.";
    }

    /**
     * ดึงข้อมูล Bot จาก Telegram API (getMe + profile photo)
     */
    private function getBotInfo($botToken) {
        // เรียก getMe
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.telegram.org/bot{$botToken}/getMe",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data || !isset($data['ok']) || !$data['ok']) {
            return null;
        }

        $bot = $data['result'];
        $bot['photo_url'] = null;

        // ดึง profile photo
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => "https://api.telegram.org/bot{$botToken}/getUserProfilePhotos?user_id={$bot['id']}&limit=1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        $photoResponse = curl_exec($ch);
        curl_close($ch);

        $photoData = json_decode($photoResponse, true);
        if ($photoData && $photoData['ok'] && $photoData['result']['total_count'] > 0) {
            $photos = $photoData['result']['photos'][0];
            // เอารูปขนาดเล็กสุด (index 0)
            $fileId = $photos[0]['file_id'] ?? null;
            if ($fileId) {
                // ดึง file path
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => "https://api.telegram.org/bot{$botToken}/getFile?file_id={$fileId}",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => true
                ]);
                $fileResponse = curl_exec($ch);
                curl_close($ch);

                $fileData = json_decode($fileResponse, true);
                if ($fileData && $fileData['ok']) {
                    $filePath = $fileData['result']['file_path'];
                    $bot['photo_url'] = "https://api.telegram.org/file/bot{$botToken}/{$filePath}";
                }
            }
        }

        return $bot;
    }
}