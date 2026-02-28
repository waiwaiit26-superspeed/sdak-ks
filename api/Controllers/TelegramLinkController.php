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

        $model = $this->model('TelegramLinkModel');
        $result = $model->createLinkToken($userId);

        if ($result['success']) {
            Response::success([
                'token' => $result['token'],
                'expires_at' => $result['expires_at'],
                'bot_link' => $this->generateBotLink($result['token'])
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
        if (!$this->currentUser) {
            Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = $this->currentUser['id'];
        $model = $this->model('TelegramLinkModel');
        $telegramInfo = $model->getTelegramInfo($userId);

        $isLinked = !empty($telegramInfo['telegram_chat_id']);

        Response::success([
            'is_linked' => $isLinked,
            'chat_id' => $isLinked ? $telegramInfo['telegram_chat_id'] : null,
            'linked_at' => $telegramInfo['telegram_linked_at'] ?? null,
            'linked_at_thai' => !empty($telegramInfo['telegram_linked_at']) ? 
                $this->formatThaiDate($telegramInfo['telegram_linked_at']) : null
        ]);
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
    private function generateBotLink($token) {
        $settings = $this->model('SettingsModel');
        $botUsername = $settings->get('member_bot_username', 'YourBot');
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
}