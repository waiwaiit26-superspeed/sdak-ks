<?php
/**
 * Telegram Bot Webhook Handler
 * รับและประมวลผลข้อความจาก Telegram Bot
 * 
 * รองรับ 2 ประเภท:
 * - ?type=member  → Member Bot (เชื่อมต่อบัญชีสมาชิก)
 * - ไม่มี type    → Admin Bot (แจ้งเตือน admin)
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

use App\Core\TelegramBot;
use App\Models\TelegramLinkModel;
use App\Models\SettingsModel;

// ตรวจสอบว่าเป็น POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// อ่านข้อมูลจาก Telegram
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    exit('Invalid JSON');
}

// Log ข้อมูลที่เข้ามา (สำหรับ debug)
$settings = new SettingsModel();
$debugMode = $settings->get('telegram_webhook_debug', '');
if ($debugMode) {
    error_log('Telegram Webhook [' . ($type ?? 'admin') . ']: ' . json_encode($data));
}

try {
    $telegramLinkModel = new TelegramLinkModel();
    
    // ประมวลผลข้อความ
    if (isset($data['message'])) {
        handleMessage($data['message'], $telegramLinkModel);
    }
    
    // ประมวลผล callback query (ปุ่ม inline)
    if (isset($data['callback_query'])) {
        handleCallbackQuery($data['callback_query'], $telegramLinkModel);
    }
    
    http_response_code(200);
    echo 'OK';
    
} catch (Exception $e) {
    error_log('Telegram Webhook Error: ' . $e->getMessage());
    http_response_code(500);
    exit('Internal Server Error');
}

/**
 * ประมวลผลข้อความที่ส่งมา
 */
function handleMessage($message, $telegramLinkModel) {
    $chatId = $message['chat']['id'];
    $text = $message['text'] ?? '';
    $firstName = $message['from']['first_name'] ?? '';
    $lastName = $message['from']['last_name'] ?? '';
    $username = $message['from']['username'] ?? null;

    // คำสั่ง /start
    if (strpos($text, '/start') === 0) {
        $parts = explode(' ', $text);
        
        if (count($parts) > 1 && strpos($parts[1], 'link_') === 0) {
            // เป็นการเชื่อมต่อบัญชี
            $token = substr($parts[1], 5); // ตัด link_ ออก
            handleAccountLinking($chatId, $token, $firstName, $lastName, $username, $telegramLinkModel);
        } else {
            // คำสั่ง /start ทั่วไป
            sendWelcomeMessage($chatId, $firstName);
        }
    }
    // คำสั่ง /help
    else if ($text === '/help') {
        sendHelpMessage($chatId);
    }
    // คำสั่งอื่นๆ
    else {
        sendUnknownCommand($chatId, $firstName);
    }
}

/**
 * ประมวลผล callback query (ปุ่มที่กด)
 */
function handleCallbackQuery($callbackQuery, $telegramLinkModel) {
    $chatId = $callbackQuery['message']['chat']['id'];
    $callbackId = $callbackQuery['id'];
    $data = $callbackQuery['data'];
    
    // ตรวจสอบสิทธิ์ admin
    $settings = new SettingsModel();
    $adminChatIds = array_filter(array_map('trim', explode(',', $settings->get('telegram_chat_id', ''))));
    if (!in_array((string)$chatId, $adminChatIds)) {
        TelegramBot::answerCallbackQuery($callbackId, '🚫 คุณไม่มีสิทธิ์ดำเนินการนี้');
        return;
    }
    
    // แปลง callback data เป็น array
    $callbackData = json_decode($data, true);
    
    if ($callbackData && isset($callbackData['action'], $callbackData['type'], $callbackData['id'])) {
        // เป็นการ approve/reject
        handleApprovalCallback($callbackQuery, $callbackData);
    } else if ($data === 'help') {
        // ปุ่มช่วยเหลือ
        TelegramBot::answerCallbackQuery($callbackId, 'กำลังแสดงความช่วยเหลือ');
        sendHelpMessage($chatId);
    } else {
        // คำสั่งไม่รู้จัก
        TelegramBot::answerCallbackQuery($callbackId, '❌ ไม่รู้จักคำสั่งนี้');
    }
}

/**
 * จัดการการ approve/reject ผ่าน callback
 */
function handleApprovalCallback($callbackQuery, $callbackData) {
    $chatId = $callbackQuery['message']['chat']['id'];
    $messageId = $callbackQuery['message']['message_id'];
    $callbackId = $callbackQuery['id'];
    
    $action = $callbackData['action']; // approve หรือ reject
    $type = $callbackData['type'];     // membership_fee หรือ activity_registration
    $id = (int)$callbackData['id'];
    
    try {
        $result = processApprovalAction($type, $id, $action);
        
        if ($result['success']) {
            $emoji = $action === 'approve' ? '✅' : '❌';
            $statusText = $action === 'approve' ? 'อนุมัติแล้ว' : 'ปฏิเสธแล้ว';
            
            // อัพเดทข้อความเดิม
            $originalText = $callbackQuery['message']['text'];
            $updatedText = $originalText . "\n\n{$emoji} **{$statusText}** โดย " . getUserName($callbackQuery['from']);
            
            TelegramBot::editMessage($chatId, $messageId, $updatedText);
            TelegramBot::answerCallbackQuery($callbackId, "{$emoji} {$statusText} เรียบร้อย");
            
            // แจ้งผู้ใช้ (ถ้ามี chat_id)
            notifyUserApprovalResult($type, $id, $action, $result['user_data'] ?? []);
            
        } else {
            TelegramBot::answerCallbackQuery($callbackId, '❌ ' . ($result['message'] ?? 'เกิดข้อผิดพลาด'), true);
        }
        
    } catch (Exception $e) {
        error_log('Approval Callback Error: ' . $e->getMessage());
        TelegramBot::answerCallbackQuery($callbackId, '❌ เกิดข้อผิดพลาดในระบบ', true);
    }
}

/**
 * ประมวลผลการ approve/reject
 */
function processApprovalAction($type, $id, $action) {
    $status = $action === 'approve' ? 'approved' : 'rejected';
    
    try {
        switch ($type) {
            case 'membership_fee':
                require_once __DIR__ . '/api/Models/MembershipFeeModel.php';
                $model = new \App\Models\MembershipFeeModel();
                return $model->updatePaymentStatus($id, $status);
                
            case 'activity_registration':
                require_once __DIR__ . '/api/Models/ActivityRegistrationModel.php';
                $model = new \App\Models\ActivityRegistrationModel();
                return $model->updatePaymentStatus($id, $status);
                
            default:
                return ['success' => false, 'message' => 'ประเภทข้อมูลไม่ถูกต้อง'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
    }
}

/**
 * แจ้งผลการอนุมัติให้ผู้ใช้ทราบ
 */
function notifyUserApprovalResult($type, $id, $action, $userData) {
    if (empty($userData['telegram_chat_id'])) {
        return; // ผู้ใช้ไม่ได้เชื่อมต่อ Telegram
    }
    
    $emoji = $action === 'approve' ? '✅' : '❌';
    $statusText = $action === 'approve' ? 'อนุมัติ' : 'ปฏิเสธ';
    $typeText = $type === 'membership_fee' ? 'ค่าธรรมเนียมสมาชิก' : 'ค่าลงทะเบียนกิจกรรม';
    
    $message = "{$emoji} **การ{$statusText}{$typeText}**\n\n";
    $message .= "📋 รายการของคุณได้รับการ{$statusText}แล้ว\n";
    
    if (isset($userData['amount'])) {
        $message .= "💰 จำนวนเงิน: " . number_format($userData['amount'], 2) . " บาท\n";
    }
    
    $message .= "📅 วันที่: " . date('d/m/Y H:i') . " น.\n\n";
    
    if ($action === 'approve') {
        $message .= "ขอบคุณที่ใช้บริการ ✨";
    } else {
        $message .= "หากมีข้อสงสัย กรุณาติดต่อเจ้าหน้าที่";
    }
    
    TelegramBot::sendMessage($userData['telegram_chat_id'], $message);
}

/**
 * ดึงชื่อผู้ใช้จาก Telegram user object
 */
function getUserName($user) {
    $name = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
    if (!empty($user['username'])) {
        $name .= " (@{$user['username']})";
    }
    return $name ?: 'ไม่ทราบชื่อ';
}

/**
 * จัดการการเชื่อมต่อบัญชี
 */
function handleAccountLinking($chatId, $token, $firstName, $lastName, $username, $telegramLinkModel) {
    $result = $telegramLinkModel->linkTelegramAccount($token, $chatId);
    
    if ($result['success']) {
        $userName = $result['user_name'];
        $welcomeText = "🎉 *เชื่อมต่อบัญชีสำเร็จแล้ว!*\n\n";
        $welcomeText .= "สวัสดี {$firstName}! 👋\n";
        $welcomeText .= "บัญชี Telegram ของคุณได้เชื่อมต่อกับบัญชี *{$userName}* แล้ว\n\n";
        $welcomeText .= "📋 *ฟีเจอร์ที่คุณจะได้รับ:*\n";
        $welcomeText .= "• 🔔 การแจ้งเตือนค่าธรรมเนียม\n";
        $welcomeText .= "• 📈 การแจ้งเตือนกิจกรรมใหม่\n";
        $welcomeText .= "• ✅ การอนุมัติเอกสารต่างๆ\n";
        $welcomeText .= "• 📊 สรุปข้อมูลสำคัญ\n\n";
        $welcomeText .= "พิมพ์ /help เพื่อดูคำสั่งทั้งหมด";
        
        TelegramBot::sendMessage($chatId, $welcomeText);
        
        // ส่งข้อความแจ้งให้ admin รู้ (ถ้าต้องการ)
        notifyAdminNewLink($result['user_name'], $result['user_email'], $firstName, $lastName, $username);
        
    } else {
        $errorText = "❌ *ไม่สามารถเชื่อมต่อบัญชีได้*\n\n";
        
        if ($result['message'] === 'Token not found or already used') {
            $errorText .= "🔗 ลิงก์นี้ถูกใช้งานแล้วหรือไม่ถูกต้อง\n";
            $errorText .= "กรุณาสร้างลิงก์ใหม่จากหน้าโปรไฟล์ในเว็บไซต์";
        } else if ($result['message'] === 'Token expired') {
            $errorText .= "⏰ ลิงก์นี้หมดอายุแล้ว\n";
            $errorText .= "กรุณาสร้างลิงก์ใหม่จากหน้าโปรไฟล์ในเว็บไซต์";
        } else {
            $errorText .= "กรุณาลองใหม่อีกครั้ง หรือติดต่อเจ้าหน้าที่\n";
            $errorText .= "รายละเอียด: " . $result['message'];
        }
        
        TelegramBot::sendMessage($chatId, $errorText);
    }
}

/**
 * ส่งข้อความต้อนรับ
 */
function sendWelcomeMessage($chatId, $firstName) {
    $text = "👋 สวัสดี {$firstName}!\n\n";
    $text .= "🏢 *สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษา จังหวัดกาฬสินธุ์*\n\n";
    $text .= "เพื่อใช้งานฟีเจอร์ทั้งหมด กรุณาเชื่อมต่อบัญชีของคุณก่อน:\n";
    $text .= "1. 🌐 เข้าสู่ระบบในเว็บไซต์\n";
    $text .= "2. 👤 ไปที่หน้าโปรไฟล์\n";
    $text .= "3. 🔗 กดปุ่ม \"เชื่อมต่อ Telegram\"\n\n";
    $text .= "พิมพ์ /help เพื่อดูคำสั่งทั้งหมด";
    
    $keyboard = [
        'inline_keyboard' => [
            [
                [
                    'text' => '❓ ช่วยเหลือ',
                    'callback_data' => 'help'
                ]
            ]
        ]
    ];
    
    TelegramBot::sendMessage($chatId, $text, $keyboard);
}

/**
 * ส่งข้อความช่วยเหลือ
 */
function sendHelpMessage($chatId) {
    $text = "📋 *คำสั่งที่ใช้ได้:*\n\n";
    $text .= "/start - เริ่มต้นใช้งาน\n";
    $text .= "/help - แสดงความช่วยเหลือ\n\n";
    $text .= "🔗 *การเชื่อมต่อบัญชี:*\n";
    $text .= "1. เข้าสู่ระบบในเว็บไซต์\n";
    $text .= "2. ไปที่หน้าโปรไฟล์\n";
    $text .= "3. กดปุ่ม \"เชื่อมต่อ Telegram\"\n";
    $text .= "4. กดปุ่ม \"เปิด Telegram Bot\"\n";
    $text .= "5. กดปุ่ม \"Start\" ใน chat นี้\n\n";
    $settings = new SettingsModel();
    $contactEmail = $settings->get('contact_email', 'admin@example.com');
    $siteUrl = $settings->get('site_url', 'https://sdak.obec.in');
    
    $text .= "📞 *ติดต่อขอความช่วยเหลือ:*\n";
    $text .= "📧 อีเมล: {$contactEmail}\n";
    $text .= "🌐 เว็บไซต์: {$siteUrl}";
    
    TelegramBot::sendMessage($chatId, $text);
}

/**
 * ส่งข้อความเมื่อไม่รู้จักคำสั่ง
 */
function sendUnknownCommand($chatId, $firstName) {
    $text = "🤔 ขออภัย {$firstName} ฉันไม่เข้าใจคำสั่งนี้\n\n";
    $text .= "พิมพ์ /help เพื่อดูคำสั่งทั้งหมด";
    
    TelegramBot::sendMessage($chatId, $text);
}

/**
 * แจ้ง admin เมื่อมีการเชื่อมต่อใหม่
 */
function notifyAdminNewLink($userName, $userEmail, $firstName, $lastName, $username) {
    $settings = new SettingsModel();
    $adminChatIdsStr = $settings->get('telegram_chat_id', '');
    $adminChatIds = array_filter(array_map('trim', explode(',', $adminChatIdsStr)));
    
    if (empty($adminChatIds)) {
        return; // ไม่มี admin chat id
    }
    
    $fullTelegramName = trim($firstName . ' ' . $lastName);
    $telegramHandle = $username ? "@{$username}" : 'ไม่มี username';
    
    $text = "🔗 *มีการเชื่อมต่อ Telegram ใหม่*\n\n";
    $text .= "👤 *สมาชิก:* {$userName}\n";
    $text .= "📧 *อีเมล:* {$userEmail}\n";
    $text .= "💬 *Telegram:* {$fullTelegramName} ({$telegramHandle})\n";
    $text .= "📅 *เวลา:* " . date('d/m/Y H:i:s');
    
    foreach ($adminChatIds as $adminChatId) {
        $adminChatId = trim($adminChatId);
        if (!empty($adminChatId)) {
            TelegramBot::sendMessage($adminChatId, $text);
        }
    }
}