<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

/**
 * SettingsController — Admin site settings management
 */
class SettingsController extends Controller
{
    /**
     * GET  ?controller=settings&action=list
     * Returns all settings (admin) or public settings
     */
    public function list(): void
    {
        $settings = $this->model('SettingsModel');
        $all = $settings->getAll();

        // If not admin, return only public settings
        if (!$this->currentUser || $this->currentUser['role'] !== 'admin') {
            $publicKeys = [
                'site_name', 'site_name_short', 'site_name_en', 'site_description',
                'contact_email', 'contact_phone', 'contact_address',
                'registration_enabled',
                'member_directory_enabled',
                'logo_web', 'logo_login', 'logo_favicon', 'logo_receipt',
                'bank_name', 'bank_account_name', 'bank_account_number',
                'google_client_id',
                'login_title', 'login_subtitle',
                'hero_badge', 'hero_title', 'hero_subtitle', 'hero_tagline',
                'cta_title', 'cta_subtitle',
                'social_facebook', 'social_line', 'social_youtube',
                'social_tiktok', 'social_instagram', 'social_website',
                'embed_stats_code',
                'theme_color',
            ];
            $filtered = [];
            foreach ($publicKeys as $key) {
                if (isset($all[$key])) $filtered[$key] = $all[$key];
            }
            Response::success($filtered);
            return;
        }

        Response::success($all);
    }

    /**
     * POST  ?controller=settings&action=update
     * Admin: update settings
     */
    public function update(): void
    {
        $this->requirePost();
        $input = $this->input();

        if (empty($input['settings']) || !is_array($input['settings'])) {
            Response::error('กรุณาระบุข้อมูลตั้งค่า');
        }

        $settings = $this->model('SettingsModel');

        // Allowed settings keys
        $allowed = [
            'site_name', 'site_name_short', 'site_name_en', 'site_description',
            'contact_email', 'contact_phone', 'contact_address',
            'google_client_id', 'google_client_secret',
            'registration_enabled',
            'member_directory_enabled',
            'membership_fee_ordinary', 'membership_fee_associate',
            'membership_fee_affiliate', 'membership_fee_honorary',
            'membership_fee_mode_ordinary', 'membership_fee_mode_associate',
            'membership_fee_mode_affiliate', 'membership_fee_mode_honorary',
            'bank_name', 'bank_account_name', 'bank_account_number',
            'receipt_book_number', 'receipt_start_number',
            'receipt_organization_name', 'receipt_organization_address',
            'logo_web', 'logo_login', 'logo_favicon', 'logo_receipt',
            'signature_mode', 'signature_name', 'signature_position', 'signature_image',
            'signature_show_name', 'signature_show_position',
            'member_number_prefix', 'member_number_digits', 'member_start_number',
            'reset_confirm_code',
            'login_title', 'login_subtitle',
            'hero_badge', 'hero_title', 'hero_subtitle', 'hero_tagline',
            'cta_title', 'cta_subtitle',
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_from_email', 'smtp_from_name', 'smtp_encryption',
            'social_facebook', 'social_line', 'social_youtube',
            'social_tiktok', 'social_instagram', 'social_website',
            'embed_stats_code',
            'telegram_bot_token', 'telegram_chat_id', 'telegram_notify_new_member',
            'telegram_notify_fee_slip', 'telegram_notify_activity_reg',
            'member_bot_token', 'member_bot_username', 'member_bot_webhook_secret',
            'member_bot_enabled', 'member_bot_notify_personal',
            'theme_color',
        ];

        $toSave = [];
        foreach ($input['settings'] as $key => $value) {
            if (in_array($key, $allowed)) {
                $toSave[$key] = $value;
            }
        }

        if (empty($toSave)) {
            Response::error('ไม่มีข้อมูลที่ต้องบันทึก');
        }

        $settings->setMany($toSave);

        Auth::logActivity(
            (int)$this->currentUser['id'],
            'update',
            'settings',
            'อัปเดตการตั้งค่า: ' . implode(', ', array_keys($toSave))
        );

        Response::success(null, 'บันทึกการตั้งค่าสำเร็จ');
    }

    /**
     * POST  ?controller=settings&action=test-telegram
     * Admin: send test Telegram message
     */
    public function testTelegram(): void
    {
        $this->requirePost();

        $settings = $this->model('SettingsModel');
        $token = $settings->get('telegram_bot_token', '');
        $chatId = $settings->get('telegram_chat_id', '');

        if (empty($token) || empty($chatId)) {
            Response::error('กรุณากรอก Bot Token และ Chat ID และบันทึกก่อน');
        }

        $siteName = $settings->get('site_name_short', SITE_NAME_SHORT);
        $message  = "✅ <b>ทดสอบการแจ้งเตือน Telegram</b>\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "การเชื่อมต่อ Telegram Bot สำเร็จแล้ว!\n";
        $message .= "📅 " . date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') . " น.\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "📌 {$siteName}";

        $ok = \App\Core\Telegram::send($message, $token, $chatId);

        if ($ok) {
            Response::success(null, 'ส่งข้อความทดสอบสำเร็จ! กรุณาตรวจสอบที่ Telegram');
        } else {
            Response::error('ไม่สามารถส่งข้อความได้ กรุณาตรวจสอบ Bot Token และ Chat ID');
        }
    }

    /* ── MEMBER TYPES ── */

    /**
     * GET  ?controller=settings&action=member-types
     * Public: ดึงประเภทสมาชิกทั้งหมดที่เปิดใช้งาน
     */
    public function memberTypes(): void
    {
        $mt = $this->model('MemberTypeModel');
        $isAdmin = $this->currentUser && $this->currentUser['role'] === 'admin';
        $data = $isAdmin ? $mt->getAll() : $mt->getActive();
        Response::success($data);
    }

    /**
     * POST  ?controller=settings&action=update-member-type
     * Admin: อัปเดตประเภทสมาชิก (label, description, fee, icon, etc.)
     */
    public function updateMemberType(): void
    {
        $this->requirePost();
        $input = $this->input();

        if (empty($input['type_key'])) Response::error('กรุณาระบุ type_key');

        $mt = $this->model('MemberTypeModel');
        $existing = $mt->findByKey($input['type_key']);
        if (!$existing) Response::error('ไม่พบประเภทสมาชิก: ' . $input['type_key'], 404);

        $allowed = ['label', 'label_short', 'description', 'fee_mode', 'fee_amount', 'icon', 'icon_bg', 'icon_color', 'sort_order', 'is_active'];
        $updateData = [];
        foreach ($allowed as $key) {
            if (isset($input[$key])) {
                $updateData[$key] = $input[$key];
            }
        }

        if (empty($updateData)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $mt->update($updateData, ['id' => $existing['id']]);

        // Sync to site_settings for backward compatibility
        if (isset($updateData['fee_mode']) || isset($updateData['fee_amount'])) {
            $settings = $this->model('SettingsModel');
            $typeKey = $input['type_key'];
            if (isset($updateData['fee_mode'])) {
                $settings->set("membership_fee_mode_{$typeKey}", $updateData['fee_mode']);
            }
            if (isset($updateData['fee_amount'])) {
                $settings->set("membership_fee_{$typeKey}", (string)$updateData['fee_amount']);
            }
        }

        Auth::logActivity(
            (int)$this->currentUser['id'], 'update_member_type', 'settings',
            "อัปเดตประเภทสมาชิก: {$input['type_key']} - " . implode(', ', array_keys($updateData))
        );

        Response::success(null, 'อัปเดตประเภทสมาชิกสำเร็จ');
    }

    /**
     * POST  ?controller=settings&action=create-member-type
     * Admin: สร้างประเภทสมาชิกใหม่
     */
    public function createMemberType(): void
    {
        $this->requirePost();
        $input = $this->input();

        if (empty($input['type_key'])) Response::error('กรุณาระบุ type_key');
        if (empty($input['label'])) Response::error('กรุณาระบุชื่อประเภทสมาชิก');

        // Validate key format
        if (!preg_match('/^[a-z][a-z0-9_]{1,49}$/', $input['type_key'])) {
            Response::error('type_key ต้องเป็นภาษาอังกฤษตัวเล็ก ตัวเลข และ _ เท่านั้น (2-50 ตัวอักษร)');
        }

        $mt = $this->model('MemberTypeModel');
        if ($mt->findByKey($input['type_key'])) {
            Response::error('ประเภทสมาชิก ' . $input['type_key'] . ' มีอยู่แล้ว');
        }

        $maxOrder = $this->model('MemberTypeModel')->db->max('member_types', 'sort_order');
        $id = $mt->create([
            'type_key'    => $input['type_key'],
            'label'       => $input['label'],
            'label_short' => $input['label_short'] ?? null,
            'description' => $input['description'] ?? null,
            'fee_mode'    => $input['fee_mode'] ?? 'none',
            'fee_amount'  => (float)($input['fee_amount'] ?? 0),
            'icon'        => $input['icon'] ?? 'bi-person-fill',
            'icon_bg'     => $input['icon_bg'] ?? '#a78bfa',
            'icon_color'  => $input['icon_color'] ?? '#3b0764',
            'sort_order'  => ((int)$maxOrder) + 1,
            'is_active'   => 1,
        ]);

        Auth::logActivity(
            (int)$this->currentUser['id'], 'create_member_type', 'settings',
            "สร้างประเภทสมาชิกใหม่: {$input['type_key']} ({$input['label']})"
        );

        Response::success(['id' => $id], 'สร้างประเภทสมาชิกสำเร็จ', 201);
    }

    /**
     * POST  ?controller=settings&action=test-member-bot
     * ทดสอบการเชื่อมต่อ Member Bot
     */
    public function testMemberBot(): void
    {
        $this->requirePost();
        $settings = $this->model('SettingsModel');
        $token = $settings->get('member_bot_token', '');

        if (empty($token)) {
            Response::error('กรุณากำหนด Member Bot Token ในการตั้งค่าก่อน');
        }

        // ทดสอบ getMe API
        $url = "https://api.telegram.org/bot{$token}/getMe";
        
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
            Response::error('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error);
        }

        if ($httpCode !== 200) {
            Response::error("HTTP Error: {$httpCode}");
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['ok']) || !$data['ok']) {
            Response::error($data['description'] ?? 'Member Bot Token ไม่ถูกต้อง');
        }

        $botInfo = $data['result'];

        Response::success([
            'message' => 'Member Bot ใช้งานได้ปกติ',
            'bot_info' => [
                'id' => $botInfo['id'],
                'first_name' => $botInfo['first_name'],
                'username' => $botInfo['username'] ?? null,
                'can_join_groups' => $botInfo['can_join_groups'] ?? false,
                'can_read_all_group_messages' => $botInfo['can_read_all_group_messages'] ?? false,
                'supports_inline_queries' => $botInfo['supports_inline_queries'] ?? false
            ]
        ]);
    }

    /**
     * POST  ?controller=settings&action=set-member-bot-webhook
     * ตั้งค่า Member Bot Webhook
     */
    public function setMemberBotWebhook(): void
    {
        $this->requirePost();
        $input = $this->input();
        $settings = $this->model('SettingsModel');
        $token = $settings->get('member_bot_token', '');

        if (empty($token)) {
            Response::error('กรุณากำหนด Member Bot Token ในการตั้งค่าก่อน');
        }

        if (empty($input['webhook_url'])) {
            Response::error('กรุณาระบุ Webhook URL');
        }

        $webhookUrl = $input['webhook_url'];
        $url = "https://api.telegram.org/bot{$token}/setWebhook";
        
        $payload = [
            'url' => $webhookUrl,
            'max_connections' => 40,
            'allowed_updates' => ['message', 'callback_query']
        ];

        $secret = $settings->get('member_bot_webhook_secret', '');
        if (!empty($secret)) {
            $payload['secret_token'] = $secret;
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
            Response::error('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['ok']) && $data['ok']) {
            Response::success([
                'message' => 'ตั้งค่า Member Bot Webhook สำเร็จ',
                'description' => $data['description'] ?? 'Webhook is set'
            ]);
        } else {
            Response::error($data['description'] ?? 'ไม่สามารถตั้งค่า Webhook ได้');
        }
    }

    /**
     * POST  ?controller=settings&action=get-member-bot-webhook-info
     * ดึงข้อมูล Member Bot Webhook
     */
    public function getMemberBotWebhookInfo(): void
    {
        $this->requirePost();
        $settings = $this->model('SettingsModel');
        $token = $settings->get('member_bot_token', '');

        if (empty($token)) {
            Response::error('กรุณากำหนด Member Bot Token ในการตั้งค่าก่อน');
        }

        $url = "https://api.telegram.org/bot{$token}/getWebhookInfo";
        
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
            Response::error('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' . $error);
        }

        $data = json_decode($response, true);

        if ($httpCode === 200 && isset($data['ok']) && $data['ok']) {
            Response::success([
                'webhook_info' => $data['result']
            ]);
        } else {
            Response::error($data['description'] ?? 'ไม่สามารถดึงข้อมูล Webhook ได้');
        }
    }
}
