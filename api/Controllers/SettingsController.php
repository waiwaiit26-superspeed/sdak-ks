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
                'logo_web', 'logo_login', 'logo_favicon', 'logo_receipt',
                'bank_name', 'bank_account_name', 'bank_account_number',
                'google_client_id',
                'login_title', 'login_subtitle',
                'social_facebook', 'social_line', 'social_youtube',
                'social_tiktok', 'social_instagram', 'social_website',
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
            'membership_fee_ordinary', 'membership_fee_associate',
            'membership_fee_affiliate', 'membership_fee_honorary',
            'membership_fee_mode_ordinary', 'membership_fee_mode_associate',
            'membership_fee_mode_affiliate', 'membership_fee_mode_honorary',
            'bank_name', 'bank_account_name', 'bank_account_number',
            'receipt_book_number',
            'receipt_organization_name', 'receipt_organization_address',
            'logo_web', 'logo_login', 'logo_favicon', 'logo_receipt',
            'signature_mode', 'signature_name', 'signature_position', 'signature_image',
            'signature_show_name', 'signature_show_position',
            'member_number_prefix', 'member_number_digits',
            'reset_confirm_code',
            'login_title', 'login_subtitle',
            'smtp_host', 'smtp_port', 'smtp_username', 'smtp_password',
            'smtp_from_email', 'smtp_from_name', 'smtp_encryption',
            'social_facebook', 'social_line', 'social_youtube',
            'social_tiktok', 'social_instagram', 'social_website',
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
}
