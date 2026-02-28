<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

class TelegramMessageController extends Controller {

    /**
     * ดึงรายชื่อสมาชิกพร้อมสถานะ Telegram
     * GET /api?controller=telegram-message&action=members
     */
    public function members() {
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $model = $this->model('TelegramMessageModel');
        $result = $model->getMembers([
            'search' => $this->query('search', ''),
            'status' => $this->query('status', ''),
            'telegram_status' => $this->query('telegram_status', ''),
            'member_type' => $this->query('member_type', ''),
            'page' => $this->query('page', 1),
            'limit' => $this->query('limit', 20),
        ]);

        Response::success($result);
    }

    /**
     * ส่งข้อความ (text)
     * POST /api?controller=telegram-message&action=send-text
     */
    public function sendText() {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $body = $this->input();
        $memberIds = $body['member_ids'] ?? [];
        $message = trim($body['message'] ?? '');
        $parseMode = $body['parse_mode'] ?? 'HTML'; // HTML or Markdown

        if (empty($memberIds) || !is_array($memberIds)) {
            Response::error('กรุณาเลือกสมาชิกอย่างน้อย 1 คน');
        }
        if (empty($message)) {
            Response::error('กรุณาใส่ข้อความ');
        }

        $settings = $this->model('SettingsModel');
        $botToken = $settings->get('member_bot_token', '');
        if (empty($botToken)) {
            Response::error('ยังไม่ได้ตั้งค่า Member Bot Token');
        }

        $model = $this->model('TelegramMessageModel');
        $recipients = $model->getChatIdsByMemberIds($memberIds);

        if (empty($recipients)) {
            Response::error('ไม่พบสมาชิกที่เชื่อมต่อ Telegram');
        }

        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($recipients as $r) {
            $result = $this->telegramSendMessage($botToken, $r['telegram_chat_id'], $message, $parseMode);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = ['name' => $r['full_name'], 'error' => $result['message']];
            }
        }

        // บันทึก log
        $model->logMessage(
            $this->currentUser['id'],
            'text',
            count($recipients),
            $successCount,
            $failCount,
            $message
        );

        Response::success([
            'total' => count($recipients),
            'success' => $successCount,
            'failed' => $failCount,
            'errors' => $errors,
        ], "ส่งสำเร็จ {$successCount}/{$successCount + $failCount} คน");
    }

    /**
     * ส่งรูปภาพ
     * POST /api?controller=telegram-message&action=send-photo
     */
    public function sendPhoto() {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $memberIds = json_decode($_POST['member_ids'] ?? '[]', true);
        $caption = trim($_POST['caption'] ?? '');
        $parseMode = $_POST['parse_mode'] ?? 'HTML';

        if (empty($memberIds) || !is_array($memberIds)) {
            Response::error('กรุณาเลือกสมาชิกอย่างน้อย 1 คน');
        }

        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            Response::error('กรุณาเลือกรูปภาพ');
        }

        $settings = $this->model('SettingsModel');
        $botToken = $settings->get('member_bot_token', '');
        if (empty($botToken)) {
            Response::error('ยังไม่ได้ตั้งค่า Member Bot Token');
        }

        $model = $this->model('TelegramMessageModel');
        $recipients = $model->getChatIdsByMemberIds($memberIds);

        if (empty($recipients)) {
            Response::error('ไม่พบสมาชิกที่เชื่อมต่อ Telegram');
        }

        $photoPath = $_FILES['photo']['tmp_name'];
        $photoName = $_FILES['photo']['name'];
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($recipients as $r) {
            $result = $this->telegramSendPhoto($botToken, $r['telegram_chat_id'], $photoPath, $photoName, $caption, $parseMode);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = ['name' => $r['full_name'], 'error' => $result['message']];
            }
        }

        $model->logMessage(
            $this->currentUser['id'],
            'photo',
            count($recipients),
            $successCount,
            $failCount,
            $caption ?: '[รูปภาพ]'
        );

        Response::success([
            'total' => count($recipients),
            'success' => $successCount,
            'failed' => $failCount,
            'errors' => $errors,
        ], "ส่งรูปภาพสำเร็จ {$successCount}/{$successCount + $failCount} คน");
    }

    /**
     * ส่งเอกสาร/ไฟล์
     * POST /api?controller=telegram-message&action=send-document
     */
    public function sendDocument() {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $memberIds = json_decode($_POST['member_ids'] ?? '[]', true);
        $caption = trim($_POST['caption'] ?? '');
        $parseMode = $_POST['parse_mode'] ?? 'HTML';

        if (empty($memberIds) || !is_array($memberIds)) {
            Response::error('กรุณาเลือกสมาชิกอย่างน้อย 1 คน');
        }

        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            Response::error('กรุณาเลือกไฟล์');
        }

        $settings = $this->model('SettingsModel');
        $botToken = $settings->get('member_bot_token', '');
        if (empty($botToken)) {
            Response::error('ยังไม่ได้ตั้งค่า Member Bot Token');
        }

        $model = $this->model('TelegramMessageModel');
        $recipients = $model->getChatIdsByMemberIds($memberIds);

        if (empty($recipients)) {
            Response::error('ไม่พบสมาชิกที่เชื่อมต่อ Telegram');
        }

        $filePath = $_FILES['document']['tmp_name'];
        $fileName = $_FILES['document']['name'];
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($recipients as $r) {
            $result = $this->telegramSendDocument($botToken, $r['telegram_chat_id'], $filePath, $fileName, $caption, $parseMode);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = ['name' => $r['full_name'], 'error' => $result['message']];
            }
        }

        $model->logMessage(
            $this->currentUser['id'],
            'document',
            count($recipients),
            $successCount,
            $failCount,
            $caption ?: "[ไฟล์: {$fileName}]"
        );

        Response::success([
            'total' => count($recipients),
            'success' => $successCount,
            'failed' => $failCount,
            'errors' => $errors,
        ], "ส่งไฟล์สำเร็จ {$successCount}/{$successCount + $failCount} คน");
    }

    /**
     * ส่งวิดีโอ  
     * POST /api?controller=telegram-message&action=send-video
     */
    public function sendVideo() {
        $this->requirePost();
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $memberIds = json_decode($_POST['member_ids'] ?? '[]', true);
        $caption = trim($_POST['caption'] ?? '');
        $parseMode = $_POST['parse_mode'] ?? 'HTML';

        if (empty($memberIds) || !is_array($memberIds)) {
            Response::error('กรุณาเลือกสมาชิกอย่างน้อย 1 คน');
        }

        if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
            Response::error('กรุณาเลือกวิดีโอ');
        }

        // จำกัดขนาด 50MB
        if ($_FILES['video']['size'] > 50 * 1024 * 1024) {
            Response::error('ไฟล์วิดีโอต้องไม่เกิน 50MB');
        }

        $settings = $this->model('SettingsModel');
        $botToken = $settings->get('member_bot_token', '');
        if (empty($botToken)) {
            Response::error('ยังไม่ได้ตั้งค่า Member Bot Token');
        }

        $model = $this->model('TelegramMessageModel');
        $recipients = $model->getChatIdsByMemberIds($memberIds);

        if (empty($recipients)) {
            Response::error('ไม่พบสมาชิกที่เชื่อมต่อ Telegram');
        }

        $filePath = $_FILES['video']['tmp_name'];
        $fileName = $_FILES['video']['name'];
        $successCount = 0;
        $failCount = 0;
        $errors = [];

        foreach ($recipients as $r) {
            $result = $this->telegramSendVideo($botToken, $r['telegram_chat_id'], $filePath, $fileName, $caption, $parseMode);
            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
                $errors[] = ['name' => $r['full_name'], 'error' => $result['message']];
            }
        }

        $model->logMessage(
            $this->currentUser['id'],
            'video',
            count($recipients),
            $successCount,
            $failCount,
            $caption ?: "[วิดีโอ: {$fileName}]"
        );

        Response::success([
            'total' => count($recipients),
            'success' => $successCount,
            'failed' => $failCount,
            'errors' => $errors,
        ], "ส่งวิดีโอสำเร็จ {$successCount}/{$successCount + $failCount} คน");
    }

    /**
     * ดึงประวัติการส่งข้อความ
     * GET /api?controller=telegram-message&action=logs
     */
    public function logs() {
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            Response::forbidden('เฉพาะ admin เท่านั้น');
        }

        $model = $this->model('TelegramMessageModel');
        $result = $model->getMessageLogs(
            intval($this->query('page', 1)),
            intval($this->query('limit', 20))
        );

        Response::success($result);
    }

    // ==========================================
    // Private: Telegram API helpers
    // ==========================================

    private function telegramSendMessage($token, $chatId, $text, $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$token}/sendMessage";
        $payload = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => $parseMode,
            'disable_web_page_preview' => false,
        ];

        return $this->telegramRequest($url, $payload);
    }

    private function telegramSendPhoto($token, $chatId, $filePath, $fileName, $caption = '', $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$token}/sendPhoto";

        $postFields = [
            'chat_id' => $chatId,
            'photo' => new \CURLFile($filePath, '', $fileName),
        ];
        if (!empty($caption)) {
            $postFields['caption'] = $caption;
            $postFields['parse_mode'] = $parseMode;
        }

        return $this->telegramMultipartRequest($url, $postFields);
    }

    private function telegramSendDocument($token, $chatId, $filePath, $fileName, $caption = '', $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$token}/sendDocument";

        $postFields = [
            'chat_id' => $chatId,
            'document' => new \CURLFile($filePath, '', $fileName),
        ];
        if (!empty($caption)) {
            $postFields['caption'] = $caption;
            $postFields['parse_mode'] = $parseMode;
        }

        return $this->telegramMultipartRequest($url, $postFields);
    }

    private function telegramSendVideo($token, $chatId, $filePath, $fileName, $caption = '', $parseMode = 'HTML') {
        $url = "https://api.telegram.org/bot{$token}/sendVideo";

        $postFields = [
            'chat_id' => $chatId,
            'video' => new \CURLFile($filePath, '', $fileName),
        ];
        if (!empty($caption)) {
            $postFields['caption'] = $caption;
            $postFields['parse_mode'] = $parseMode;
        }

        return $this->telegramMultipartRequest($url, $postFields);
    }

    private function telegramRequest($url, $payload) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return ['success' => false, 'message' => 'cURL: ' . $error];

        $data = json_decode($response, true);
        if ($data && ($data['ok'] ?? false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => $data['description'] ?? 'Unknown error'];
    }

    private function telegramMultipartRequest($url, $postFields) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) return ['success' => false, 'message' => 'cURL: ' . $error];

        $data = json_decode($response, true);
        if ($data && ($data['ok'] ?? false)) {
            return ['success' => true];
        }
        return ['success' => false, 'message' => $data['description'] ?? 'Unknown error'];
    }
}
