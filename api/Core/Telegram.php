<?php
namespace App\Core;

/**
 * Telegram — ระบบแจ้งเตือนผ่าน Telegram Bot
 * ใช้ Telegram Bot API ส่งข้อความไปยัง Chat ID ที่กำหนด
 */
class Telegram
{
    /**
     * ส่งข้อความแจ้งเตือนไปยัง Telegram
     *
     * @param string $message   ข้อความที่ต้องการส่ง (รองรับ HTML)
     * @param string|null $botToken   Bot Token (ถ้าไม่ระบุจะดึงจาก settings)
     * @param string|null $chatId     Chat ID (ถ้าไม่ระบุจะดึงจาก settings)
     * @return bool สำเร็จหรือไม่
     */
    public static function send(string $message, ?string $botToken = null, ?string $chatId = null): bool
    {
        $settings = new \App\Models\SettingsModel();

        $token  = $botToken ?: $settings->get('telegram_bot_token', '');
        $chat   = $chatId   ?: $settings->get('telegram_chat_id', '');

        if (empty($token) || empty($chat)) {
            return false; // ไม่ได้ตั้งค่า — ข้ามไปเงียบๆ
        }

        $url = "https://api.telegram.org/bot{$token}/sendMessage";

        $payload = [
            'chat_id'    => $chat,
            'text'       => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }

    /**
     * แจ้งเตือนสมาชิกสมัครใหม่
     */
    public static function notifyNewMember(array $userData): bool
    {
        $settings = new \App\Models\SettingsModel();
        $enabled = $settings->get('telegram_notify_new_member', '0');
        if ($enabled !== '1') return false;

        $siteName  = $settings->get('site_name_short', SITE_NAME_SHORT);
        $typeLabels = [
            'ordinary'  => 'สามัญ',
            'associate' => 'วิสามัญ',
            'affiliate' => 'สมทบ',
            'honorary'  => 'กิตติมศักดิ์',
        ];

        $name       = $userData['full_name'] ?? ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '');
        $memberType = $typeLabels[$userData['member_type'] ?? ''] ?? ($userData['member_type'] ?? '-');
        $email      = $userData['email'] ?? '-';
        $phone      = $userData['phone'] ?? '-';
        $school     = $userData['school_organization'] ?? '-';
        $date       = date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') . ' น.';

        $message  = "🔔 <b>สมาชิกใหม่สมัครเข้าระบบ</b>\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "👤 <b>{$name}</b>\n";
        $message .= "📋 ประเภท: {$memberType}\n";
        $message .= "📧 อีเมล: {$email}\n";
        if ($phone && $phone !== '-') {
            $message .= "📱 โทร: {$phone}\n";
        }
        if ($school && $school !== '-') {
            $message .= "🏫 สังกัด: {$school}\n";
        }
        $message .= "📅 วันที่: {$date}\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "🔗 <a href=\"" . (defined('BASE_URL') ? BASE_URL : '') . "admin/?page=members\">จัดการสมาชิก</a>\n";
        $message .= "📌 {$siteName}";

        return self::send($message);
    }

    /**
     * แจ้งเตือนอัปโหลดสลิปค่าสมาชิก
     */
    public static function notifyFeeSlipUpload(array $userData, array $feeData): bool
    {
        $settings = new \App\Models\SettingsModel();
        $enabled = $settings->get('telegram_notify_fee_slip', '0');
        if ($enabled !== '1') return false;

        $siteName = $settings->get('site_name_short', SITE_NAME_SHORT);
        $name     = $userData['full_name'] ?? ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '');
        $year     = $feeData['year'] ?? '-';
        $amount   = isset($feeData['amount']) ? number_format((float)$feeData['amount'], 2) : '-';
        $date     = date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') . ' น.';

        $message  = "💳 <b>สมาชิกอัปโหลดสลิปค่าธรรมเนียม</b>\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "👤 <b>{$name}</b>\n";
        $message .= "📅 ปี พ.ศ.: {$year}\n";
        $message .= "💰 จำนวน: {$amount} บาท\n";
        $message .= "🕐 เวลา: {$date}\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "🔗 <a href=\"" . (defined('BASE_URL') ? BASE_URL : '') . "admin/?page=fees\">ตรวจสอบค่าธรรมเนียม</a>\n";
        $message .= "📌 {$siteName}";

        return self::send($message);
    }

    /**
     * แจ้งเตือนสมัครกิจกรรม
     */
    public static function notifyActivityRegistration(array $userData, array $activityData): bool
    {
        $settings = new \App\Models\SettingsModel();
        $enabled = $settings->get('telegram_notify_activity_reg', '0');
        if ($enabled !== '1') return false;

        $siteName  = $settings->get('site_name_short', SITE_NAME_SHORT);
        $name      = $userData['full_name'] ?? ($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '');
        $actTitle  = $activityData['title'] ?? '-';
        $hasFee    = !empty($activityData['has_fee']) ? 'มีค่าใช้จ่าย' : 'ไม่มีค่าใช้จ่าย';
        $date      = date('d/m/') . (date('Y') + 543) . ' ' . date('H:i') . ' น.';

        $message  = "📝 <b>สมาชิกลงทะเบียนกิจกรรม</b>\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "👤 <b>{$name}</b>\n";
        $message .= "🎯 กิจกรรม: {$actTitle}\n";
        $message .= "💰 {$hasFee}\n";
        $message .= "🕐 เวลา: {$date}\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "🔗 <a href=\"" . (defined('BASE_URL') ? BASE_URL : '') . "admin/?page=activities\">จัดการกิจกรรม</a>\n";
        $message .= "📌 {$siteName}";

        return self::send($message);
    }

    /**
     * แจ้งเตือนทั่วไป (สำหรับใช้เพิ่มเติมในอนาคต)
     */
    public static function notifyAdmin(string $title, string $body): bool
    {
        $settings = new \App\Models\SettingsModel();
        $siteName = $settings->get('site_name_short', SITE_NAME_SHORT);

        $message  = "📢 <b>{$title}</b>\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= $body . "\n";
        $message .= "━━━━━━━━━━━━━━━\n";
        $message .= "📌 {$siteName}";

        return self::send($message);
    }
}
