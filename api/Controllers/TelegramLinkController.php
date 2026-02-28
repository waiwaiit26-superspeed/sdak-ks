<?php
namespace App\Controllers;

use App\Core\Response;
use App\Models\TelegramLinkModel;
use App\Models\SettingsModel;
use App\Core\Auth;

class TelegramLinkController {
    private $model;

    public function __construct() {
        $this->model = new TelegramLinkModel();
    }

    /**
     * สร้าง link token สำหรับ member ที่ล็อกอินอยู่
     * GET/POST /api?controller=telegram-link&action=create-token
     */
    public function createToken() {
        // ตรวจสอบการล็อกอิน
        if (!Auth::isLoggedIn()) {
            return Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = Auth::getUserId();
        $userRole = Auth::getUserRole();

        // เฉพาะ member และ admin เท่านั้น
        if (!in_array($userRole, ['member', 'admin'])) {
            return Response::forbidden('ไม่มีสิทธิ์ใช้งาน');
        }

        $result = $this->model->createLinkToken($userId);

        if ($result['success']) {
            return Response::success([
                'token' => $result['token'],
                'expires_at' => $result['expires_at'],
                'bot_link' => $this->generateBotLink($result['token'])
            ]);
        } else {
            return Response::error($result['message']);
        }
    }

    /**
     * ดึงสถานะการเชื่อมต่อ Telegram ของผู้ใช้
     * GET /api?controller=telegram-link&action=status
     */
    public function status() {
        if (!Auth::isLoggedIn()) {
            return Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = Auth::getUserId();
        $telegramInfo = $this->model->getTelegramInfo($userId);

        $isLinked = !empty($telegramInfo['telegram_chat_id']);

        return Response::success([
            'is_linked' => $isLinked,
            'chat_id' => $isLinked ? $telegramInfo['telegram_chat_id'] : null,
            'linked_at' => $telegramInfo['telegram_linked_at'],
            'linked_at_thai' => $telegramInfo['telegram_linked_at'] ? 
                $this->formatThaiDate($telegramInfo['telegram_linked_at']) : null
        ]);
    }

    /**
     * ยกเลิกการเชื่อมต่อ Telegram
     * POST /api?controller=telegram-link&action=unlink
     */
    public function unlink() {
        if (!Auth::isLoggedIn()) {
            return Response::unauthorized('กรุณาล็อกอินก่อน');
        }

        $userId = Auth::getUserId();
        $result = $this->model->unlinkTelegramAccount($userId);

        if ($result['success']) {
            return Response::success(['message' => 'ยกเลิกการเชื่อมต่อ Telegram เรียบร้อย']);
        } else {
            return Response::error($result['message']);
        }
    }

    /**
     * API สำหรับ Bot เรียกเมื่อมี user กด start พร้อม token
     * POST /api?controller=telegram-link&action=process-link
     * Body: {"token": "tg_xxx", "chat_id": 123456789, "bot_secret": "xxx"}
     */
    public function processLink() {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!$data) {
            return Response::error('Invalid JSON data');
        }

        // ตรวจสอบ bot secret (ป้องกันการเรียก API จากภายนอก)
        $expectedSecret = defined('TELEGRAM_BOT_SECRET') ? TELEGRAM_BOT_SECRET : 'default_secret';
        if (empty($data['bot_secret']) || $data['bot_secret'] !== $expectedSecret) {
            return Response::forbidden('Invalid bot secret');
        }

        $token = $data['token'] ?? '';
        $chatId = $data['chat_id'] ?? 0;

        if (empty($token) || empty($chatId)) {
            return Response::error('Token and chat_id are required');
        }

        $result = $this->model->linkTelegramAccount($token, $chatId);

        if ($result['success']) {
            return Response::success([
                'user_id' => $result['user_id'],
                'user_name' => $result['user_name'],
                'user_email' => $result['user_email'],
                'message' => $result['message']
            ]);
        } else {
            return Response::error($result['message']);
        }
    }

    /**
     * ดูรายการสมาชิกที่เชื่อมต่อ Telegram (สำหรับ admin)
     * GET /api?controller=telegram-link&action=linked-members
     */
    public function linkedMembers() {
        if (!Auth::isLoggedIn() || Auth::getUserRole() !== 'admin') {
            return Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $search = $_GET['search'] ?? '';
        $page = max(1, intval($_GET['page'] ?? 1));
        $limit = max(10, min(100, intval($_GET['limit'] ?? 20)));
        $offset = ($page - 1) * $limit;

        $result = $this->model->getLinkedMembers($search, $limit, $offset);

        // Format dates for display
        foreach ($result['members'] as &$member) {
            $member['linked_at_thai'] = $this->formatThaiDate($member['telegram_linked_at']);
        }

        return Response::success([
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
     * @param string $token
     * @return string
     */
    private function generateBotLink($token) {
        $settings = new SettingsModel();
        $botUsername = $settings->get('member_bot_username', 'YourBot');
        return "https://t.me/{$botUsername}?start=link_{$token}";
    }

    /**
     * แปลงวันที่เป็นภาษาไทย
     * @param string $datetime
     * @return string
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