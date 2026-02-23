<?php
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Mailer — ระบบส่งอีเมล (SMTP)
 * ใช้ PHPMailer + ดึงค่า SMTP จาก site_settings
 */
class Mailer
{
    /**
     * ส่งอีเมล
     *
     * @param string $toEmail    อีเมลผู้รับ
     * @param string $toName     ชื่อผู้รับ
     * @param string $subject    หัวข้อ
     * @param string $htmlBody   เนื้อหา HTML
     * @return bool สำเร็จหรือไม่
     * @throws \Exception
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody): bool
    {
        $settings = new \App\Models\SettingsModel();

        $smtpHost = $settings->get('smtp_host', '');
        $smtpPort = (int)($settings->get('smtp_port', '587'));
        $smtpUser = $settings->get('smtp_username', '');
        $smtpPass = $settings->get('smtp_password', '');
        $smtpFrom = $settings->get('smtp_from_email', '') ?: $smtpUser;
        $smtpFromName = $settings->get('smtp_from_name', '') ?: ($settings->get('site_name_short', 'ส.ร.ม.ก.'));
        $smtpEncryption = $settings->get('smtp_encryption', 'tls'); // tls | ssl | none

        if (empty($smtpHost) || empty($smtpUser)) {
            throw new \Exception('ยังไม่ได้ตั้งค่า SMTP กรุณาตั้งค่าในหน้า Admin > ตั้งค่า');
        }

        $mail = new PHPMailer(true);

        // SMTP configuration
        $mail->isSMTP();
        $mail->Host       = $smtpHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = ($smtpEncryption === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : (($smtpEncryption === 'tls') ? PHPMailer::ENCRYPTION_STARTTLS : '');
        $mail->Port       = $smtpPort;
        $mail->CharSet    = 'UTF-8';

        // Sender & recipient
        $mail->setFrom($smtpFrom, $smtpFromName);
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        return $mail->send();
    }

    /**
     * สร้าง HTML Template สำหรับอีเมล
     */
    public static function buildTemplate(string $title, string $bodyContent, string $footerText = ''): string
    {
        $settings = new \App\Models\SettingsModel();
        $siteName = $settings->get('site_name_short', 'ส.ร.ม.ก.');
        $logo     = $settings->get('logo_web', '');

        if (!$footerText) {
            $footerText = $siteName . ' — ' . ($settings->get('site_name', '') ?: 'ระบบสมาชิก');
        }

        // Determine logo URL
        $logoHtml = '';
        if ($logo) {
            $baseUrl = defined('BASE_URL') ? BASE_URL : '';
            $logoUrl = (strpos($logo, 'http') === 0) ? $logo : rtrim($baseUrl, '/') . '/' . ltrim($logo, '/');
            $logoHtml = '<img src="' . htmlspecialchars($logoUrl) . '" alt="' . htmlspecialchars($siteName) . '" style="max-height:60px;max-width:200px;">';
        }

        return '
<!DOCTYPE html>
<html lang="th">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background:#f4f4f7;font-family:\'Sarabun\',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f7;padding:40px 20px;">
<tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.08);">
    <!-- Header -->
    <tr><td style="background:linear-gradient(135deg,#4c1d95,#7c3aed);padding:28px 32px;text-align:center;">
        ' . ($logoHtml ? '<div style="margin-bottom:8px;">' . $logoHtml . '</div>' : '') . '
        <h1 style="margin:0;color:#fff;font-size:1.4rem;font-weight:700;">' . htmlspecialchars($title) . '</h1>
    </td></tr>
    <!-- Body -->
    <tr><td style="padding:32px 32px 24px;color:#374151;font-size:1rem;line-height:1.7;">
        ' . $bodyContent . '
    </td></tr>
    <!-- Footer -->
    <tr><td style="padding:16px 32px 24px;text-align:center;color:#9ca3af;font-size:.82rem;border-top:1px solid #f0f0f0;">
        ' . htmlspecialchars($footerText) . '
    </td></tr>
</table>
</td></tr>
</table>
</body>
</html>';
    }
}
