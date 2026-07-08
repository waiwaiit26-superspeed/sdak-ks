<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Auth;
use App\Core\Mailer;

/**
 * AuthController
 * Uses: UserModel, AuthTokenModel, MemberStatisticModel (via Auth service)
 */
class AuthController extends Controller
{
    /**
     * POST  ?controller=auth&action=login
     */
    public function login(): void
    {
        $this->requirePost();
        $input    = $this->input();
        $login    = trim($input['login'] ?? '');
        $password = $input['password'] ?? '';

        if ($login === '' || $password === '') {
            Response::error('กรุณากรอกชื่อผู้ใช้/อีเมล และรหัสผ่าน');
        }

        $users = $this->model('UserModel');
        $user  = $users->findByLogin($login);

        if (!$user || !Auth::verifyPassword($password, $user['password'])) {
            Response::error('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง', 401);
        }
        if ($user['status'] === 'suspended') {
            Response::error('บัญชีของคุณถูกระงับ กรุณาติดต่อผู้ดูแลระบบ', 403);
        }
        if ($user['status'] === 'cancelled') {
            Response::error('บัญชีของคุณถูกยกเลิก กรุณาติดต่อผู้ดูแลระบบ', 403);
        }

        $auth  = new Auth();
        $token = $auth->generateToken((int)$user['id']);
        unset($user['password'], $user['google_id']);

        Auth::logActivity((int)$user['id'], 'login', 'auth', 'เข้าสู่ระบบ' . ($user['role'] === 'admin' ? ' (ผู้ดูแลระบบ)' : ''));

        Response::success([
            'user'          => $user,
            'token'         => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'expires_at'    => $token['expires_at'],
        ], 'เข้าสู่ระบบสำเร็จ');
    }

    /**
     * POST  ?controller=auth&action=register
     */
    public function register(): void
    {
        $this->requirePost();
        $input = $this->input();

        // Check if registration is enabled
        $settings = $this->model('SettingsModel');
        if (!$settings->isRegistrationEnabled()) {
            Response::error('ระบบปิดรับสมัครสมาชิกชั่วคราว กรุณาติดต่อผู้ดูแลระบบ', 403);
        }

        // validate
        $required = ['username','password','first_name','member_type'];
        $errors = [];
        foreach ($required as $f) {
            if (empty(trim($input[$f] ?? ''))) $errors[$f] = "กรุณากรอก {$f}";
        }
        if ($errors) Response::error('กรุณากรอกข้อมูลให้ครบถ้วน', 400, $errors);

        $username   = trim($input['username']);
        $email      = trim($input['email'] ?? '') ?: null;  // empty string → NULL (avoids UNIQUE constraint collision)
        $password   = $input['password'];
        $memberType = $input['member_type'];

        if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) Response::error('รูปแบบอีเมลไม่ถูกต้อง');
        if (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $username))   Response::error('ชื่อผู้ใช้ต้องเป็นตัวอักษรภาษาอังกฤษ ตัวเลข หรือ _ ยาว 3-50 ตัวอักษร');
        if (strlen($password) < 6)                                Response::error('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร');
        if (!in_array($memberType, ['ordinary','associate','affiliate'])) Response::error('ประเภทสมาชิกไม่ถูกต้อง');

        $users = $this->model('UserModel');
        if ($users->usernameExists($username)) Response::error('ชื่อผู้ใช้นี้ถูกใช้แล้ว');
        if ($email !== null && $users->emailExists($email)) Response::error('อีเมลนี้ถูกใช้แล้ว');

        $prefix    = trim($input['prefix'] ?? '');
        $firstName = trim($input['first_name'] ?? '');
        $lastName  = trim($input['last_name'] ?? '');
        $fullName  = self::buildFullName($prefix, $firstName, $lastName);

        $userId = $users->create([
            'username'            => $username,
            'email'               => $email,
            'password'            => Auth::hashPassword($password),
            'role'                => 'member',
            'member_type'         => $memberType,
            'status'              => 'pending',
            'prefix'              => $prefix,
            'full_name'           => $fullName,
            'first_name'          => $firstName,
            'last_name'           => $lastName,
            'phone'               => trim($input['phone'] ?? ''),
            'national_id'         => trim($input['national_id'] ?? ''),
            'birth_date'          => !empty($input['birth_date']) ? $input['birth_date'] : null,
            'school_organization' => trim($input['school_organization'] ?? ''),
            'position'            => trim($input['position'] ?? ''),
            'academic_rank'       => trim($input['academic_rank'] ?? ''),
            'bio'                 => trim($input['bio'] ?? ''),
            'education_area'      => trim($input['education_area'] ?? ''),
            'region'              => trim($input['region'] ?? ''),
            'work_phone'          => trim($input['work_phone'] ?? ''),
            'home_address'        => !empty($input['home_address']) ? json_encode($input['home_address'], JSON_UNESCAPED_UNICODE) : null,
            'work_address'        => !empty($input['work_address']) ? json_encode($input['work_address'], JSON_UNESCAPED_UNICODE) : null,
        ]);

        (new Auth())->logAction((int)$userId, 'registered', null, null, "สมัครสมาชิกใหม่ ประเภท: {$memberType}");
        Auth::logActivity((int)$userId, 'register', 'auth', "สมัครสมาชิกใหม่ ประเภท: {$memberType}", (int)$userId, 'user');

        // ── Telegram notification ──
        try {
            \App\Core\Telegram::notifyNewMember([
                'full_name'           => $fullName,
                'first_name'          => $firstName,
                'last_name'           => $lastName,
                'member_type'         => $memberType,
                'email'               => $email,
                'phone'               => trim($input['phone'] ?? ''),
                'school_organization' => trim($input['school_organization'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            // ไม่ block การสมัคร หากส่ง Telegram ไม่สำเร็จ
            error_log('Telegram notification error: ' . $e->getMessage());
        }

        // ── Auto-login: generate token for the new user ──
        $auth  = new Auth();
        $token = $auth->generateToken((int)$userId);
        $user  = $users->find((int)$userId);
        unset($user['password'], $user['google_id']);

        // ── Auto-create fee record ──
        $mt = $this->model('MemberTypeModel');
        $feeConf   = $mt->getFeeConfig($memberType);
        $feeMode   = $feeConf['mode']   ?? 'none';
        $feeAmount = $feeConf['amount'] ?? 0;
        $feeId = null;

        if ($feeMode !== 'none' && $feeAmount > 0) {
            $fees = $this->model('MembershipFeeModel');
            $buddhistYear = (int)date('Y') + 543;
            $feeId = $fees->upsertFee((int)$userId, $buddhistYear, $feeAmount, $feeMode === 'onetime' ? 'onetime' : 'annual');

            Auth::logActivity(
                (int)$userId, 'create_fee', 'fee',
                "สร้างรายการค่าธรรมเนียมอัตโนมัติ ({$feeMode}) จำนวน {$feeAmount} บาท",
                $feeId, 'fee'
            );
        }

        Response::success([
            'user_id'       => $userId,
            'user'          => $user,
            'token'         => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'expires_at'    => $token['expires_at'],
            'fee_id'        => $feeId,
        ], 'สมัครสมาชิกสำเร็จ', 201);
    }

    /**
     * POST  ?controller=auth&action=google-login
     */
    public function googleLogin(): void
    {
        $this->requirePost();
        $input = $this->input();
        $googleToken = $input['google_token'] ?? '';

        if ($googleToken === '') Response::error('กรุณาระบุ Google Token');

        $gUser = $this->verifyGoogleToken($googleToken);
        if (!$gUser) Response::error('Google Token ไม่ถูกต้อง', 401);

        $users = $this->model('UserModel');
        $auth  = new Auth();

        // ── ค้นหาผู้ใช้เดิม ──
        $user = $users->findByGoogleId($gUser['sub']);
        if (!$user) {
            $user = $users->findByEmail($gUser['email']);
        }

        if ($user) {
            // ── ผู้ใช้เดิม: sync Google ID + รูปโปรไฟล์จาก Google แล้ว login ปกติ ──
            $googlePicture = $this->sanitizeGooglePictureUrl($gUser['picture'] ?? '');
            $syncData = [
                'google_id'      => $gUser['sub'],
                'google_picture' => $googlePicture ?: null,  // sync Google pic เสมอ
            ];
            // ตั้ง profile_image จาก Google เฉพาะเมื่อ user ยังไม่มีรูปของตัวเอง
            if ($googlePicture !== '' && empty($user['profile_image'])) {
                $syncData['profile_image'] = $googlePicture;
            }

            // ไม่ block login หาก schema เก่ายังไม่มีบางคอลัมน์
            try {
                $users->update($syncData, ['id' => $user['id']]);
            } catch (\Throwable $e) {
                try {
                    $users->update(['google_id' => $gUser['sub']], ['id' => $user['id']]);
                } catch (\Throwable $ignore) {
                    // ignore sync error and continue login
                }
            }
            $user = $users->find((int)$user['id']);

            if ($user['status'] === 'suspended') Response::error('บัญชีของคุณถูกระงับ', 403);
            if ($user['status'] === 'cancelled') Response::error('บัญชีของคุณถูกยกเลิก กรุณาติดต่อผู้ดูแลระบบ', 403);

            $token = $auth->generateToken((int)$user['id']);
            unset($user['password'], $user['google_id']);

            Auth::logActivity((int)$user['id'], 'login', 'auth', 'เข้าสู่ระบบผ่าน Google');

            Response::success([
                'user'          => $user,
                'token'         => $token['token'],
                'refresh_token' => $token['refresh_token'],
                'expires_at'    => $token['expires_at'],
                'is_new'        => false,
                'needs_setup'   => false,
            ], 'เข้าสู่ระบบสำเร็จ');
        }

        // ── ผู้ใช้ใหม่: ยังไม่สร้าง record → ส่งข้อมูลกลับให้เลือกประเภทสมาชิกก่อน ──
        $mt       = $this->model('MemberTypeModel');
        $settings = $this->model('SettingsModel');
        $allFee   = $mt->getAllFeeConfigs();   // ['ordinary'=>[amount,mode], …]
        $types    = $mt->getActive();          // full rows with label, icon, etc.

        $feeInfo = $allFee;
        $feeInfo['bank_info'] = [
            'bank_name'      => $settings->get('bank_name', ''),
            'account_name'   => $settings->get('bank_account_name', ''),
            'account_number' => $settings->get('bank_account_number', ''),
        ];

        // แปลง member_types เป็น array keyed by type_key สำหรับ setup wizard
        $memberTypes = [];
        foreach ($types as $t) {
            $memberTypes[$t['type_key']] = [
                'label'       => $t['label'],
                'label_short' => $t['label_short'] ?? $t['label'],
                'description' => $t['description'] ?? '',
                'fee_mode'    => $t['fee_mode'],
                'fee_amount'  => (float)$t['fee_amount'],
                'icon'        => $t['icon'] ?? 'fas fa-user',
                'icon_bg'     => $t['icon_bg'] ?? 'bg-primary',
                'icon_color'  => $t['icon_color'] ?? 'text-white',
            ];
        }

        Response::success([
            'user'         => null,
            'is_new'       => true,
            'needs_setup'  => true,
            'fee_info'     => $feeInfo,
            'member_types' => $memberTypes,
            'google_user'  => [
                'sub'     => $gUser['sub'],
                'email'   => $gUser['email'],
                'name'    => $gUser['name'] ?? '',
                'picture' => $gUser['picture'] ?? '',
            ],
        ], 'กรุณาเลือกประเภทสมาชิก');
    }

    /**
     * POST  ?controller=auth&action=logout
     */
    public function logout(): void
    {
        $this->requirePost();
        if ($this->currentUser) {
            Auth::logActivity((int)$this->currentUser['id'], 'logout', 'auth', 'ออกจากระบบ');
        }
        $token = Auth::getBearerToken();
        if ($token) {
            (new Auth())->logout($token);
        }
        Response::success(null, 'ออกจากระบบสำเร็จ');
    }

    /**
     * POST  ?controller=auth&action=refresh
     */
    public function refresh(): void
    {
        $this->requirePost();
        $rt = $this->input()['refresh_token'] ?? '';
        if ($rt === '') Response::error('กรุณาระบุ refresh token');

        $new = (new Auth())->refreshToken($rt);
        if (!$new) Response::error('Refresh token ไม่ถูกต้องหรือหมดอายุ', 401);

        Response::success($new, 'รีเฟรช token สำเร็จ');
    }

    /**
     * GET  ?controller=auth&action=me
     */
    public function me(): void
    {
        Response::success($this->currentUser);
    }

    /**
     * POST  ?controller=auth&action=complete-google-register
     * ขั้นตอนที่ 2 หลัง Google Register: เลือกประเภทสมาชิก + อัปโหลดสลิป (optional)
     */
    public function completeGoogleRegister(): void
    {
        $this->requirePost();
        $input = $this->input();

        $googleToken = $input['google_token'] ?? '';
        $memberType  = $input['member_type'] ?? '';
        $slip        = $input['payment_slip'] ?? '';
        $inputPrefix   = trim($input['prefix'] ?? '');
        $inputFirstName = trim($input['first_name'] ?? '');
        $inputLastName  = trim($input['last_name'] ?? '');

        if ($googleToken === '') Response::error('กรุณาระบุ Google Token');
        if (!in_array($memberType, ['ordinary', 'associate', 'affiliate'])) {
            Response::error('กรุณาเลือกประเภทสมาชิกที่ถูกต้อง');
        }

        // ── Verify Google Token อีกครั้ง ──
        $gUser = $this->verifyGoogleToken($googleToken);
        if (!$gUser) Response::error('Google Token ไม่ถูกต้องหรือหมดอายุ กรุณาลองใหม่', 401);

        $users = $this->model('UserModel');
        $auth  = new Auth();

        // ── ตรวจซ้ำ: ถ้ามี user แล้ว (จาก google_id หรือ email) ให้ update member_type ──
        $user = $users->findByGoogleId($gUser['sub']);
        if (!$user) $user = $users->findByEmail($gUser['email']);

        if ($user) {
            // มีบัญชีแล้ว → update member_type + google_id + ชื่อ (ถ้ากรอกมา)
            $googlePicture = $this->sanitizeGooglePictureUrl($gUser['picture'] ?? '');
            $updateData = [
                'member_type'    => $memberType,
                'google_id'      => $gUser['sub'],
                'google_picture' => $googlePicture ?: null,
            ];
            // ตั้ง profile_image จาก Google เฉพาะเมื่อ user ยังไม่มีรูปของตัวเอง
            if ($googlePicture !== '' && empty($user['profile_image'])) {
                $updateData['profile_image'] = $googlePicture;
            }
            if ($inputFirstName !== '') {
                $updateData['prefix']     = $inputPrefix;
                $updateData['first_name'] = $inputFirstName;
                $updateData['last_name']  = $inputLastName;
                $updateData['full_name']  = self::buildFullName($inputPrefix, $inputFirstName, $inputLastName);
            }
            // ไม่ block register หาก schema เก่ายังไม่มี profile_image
            try {
                $users->update($updateData, ['id' => $user['id']]);
            } catch (\Throwable $e) {
                unset($updateData['profile_image'], $updateData['google_picture']);
                $users->update($updateData, ['id' => $user['id']]);
            }
            $userId = (int)$user['id'];
        } else {
            // ── สร้าง user ใหม่พร้อม member_type ──
            $uname = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $gUser['name'] ?? '')) . rand(100, 999);
            // ใช้ชื่อจาก input (ที่ผู้ใช้กรอกเอง) ถ้ามี, ไม่งั้นแยกจาก Google name
            if ($inputFirstName !== '') {
                $gFirstName = $inputFirstName;
                $gLastName  = $inputLastName;
                $gFullName  = self::buildFullName($inputPrefix, $gFirstName, $gLastName);
            } else {
                $gName = $gUser['name'] ?? '';
                $gNameParts = preg_split('/\s+/', trim($gName), 2);
                $gFirstName = $gNameParts[0] ?? '';
                $gLastName  = $gNameParts[1] ?? '';
                $gFullName  = self::buildFullName('', $gFirstName, $gLastName);
            }

            $googlePicture = $this->sanitizeGooglePictureUrl($gUser['picture'] ?? '');
            $userId = (int)$users->create([
                'username'       => $uname,
                'email'          => $gUser['email'],
                'google_id'      => $gUser['sub'],
                'role'           => 'member',
                'member_type'    => $memberType,
                'status'         => 'pending',
                'prefix'         => $inputPrefix,
                'full_name'      => $gFullName,
                'first_name'     => $gFirstName,
                'last_name'      => $gLastName,
                'google_picture' => $googlePicture ?: null,
                // profile_image starts as null — frontend resolves google_picture as fallback
            ]);
            $auth->logAction($userId, 'registered', null, null, "สมัครผ่าน Google ประเภท: {$memberType}");
            Auth::logActivity($userId, 'register', 'auth', "สมัครสมาชิกใหม่ผ่าน Google ประเภท: {$memberType}", $userId, 'user');

            // ── Telegram notification (Google register) ──
            try {
                \App\Core\Telegram::notifyNewMember([
                    'full_name'           => $gFullName,
                    'first_name'          => $gFirstName,
                    'last_name'           => $gLastName,
                    'member_type'         => $memberType,
                    'email'               => $gUser['email'],
                ]);
            } catch (\Throwable $e) {
                error_log('Telegram notification error: ' . $e->getMessage());
            }
        }

        // ── สร้างรายการค่าธรรมเนียม + อัปโหลดสลิป (ถ้ามี) ──
        $feeId = null;
        $mt = $this->model('MemberTypeModel');
        $feeConfig = $mt->getFeeConfig($memberType);
        $feeMode   = $feeConfig['mode'];
        $feeAmount = $feeConfig['amount'];

        if ($feeMode !== 'none' && $feeAmount > 0) {
            $fees = $this->model('MembershipFeeModel');
            $buddhistYear = (int)date('Y') + 543;
            $feeType = $feeMode === 'onetime' ? 'onetime' : 'annual';

            $existing = ($feeType === 'onetime')
                ? $fees->getOnetimeFee($userId)
                : $fees->getUserYearFee($userId, $buddhistYear);

            if (!$existing) {
                $feeId = $fees->upsertFee($userId, $buddhistYear, $feeAmount, $feeType);
            } else {
                $feeId = (int)$existing['id'];
            }

            if ($slip && $feeId) {
                // ถ้า slip เป็น base64 data URL → save เป็นไฟล์
                $slipPath = $slip;
                if (strpos($slip, 'data:image') === 0) {
                    $slipPath = $this->saveBase64Image($slip, 'payment_slips');
                }
                if ($slipPath) {
                    $fees->uploadSlip($feeId, $slipPath);
                    Auth::logActivity($userId, 'upload_slip', 'fee', 'อัปโหลดหลักฐานชำระค่าธรรมเนียม (จากขั้นตอนสมัคร Google)', $feeId, 'fee');
                }
            }
        }

        // ── Generate token & login ──
        $token = $auth->generateToken($userId);
        $user  = $users->find($userId, \App\Models\UserModel::SAFE_COLUMNS);

        Response::success([
            'user'          => $user,
            'token'         => $token['token'],
            'refresh_token' => $token['refresh_token'],
            'expires_at'    => $token['expires_at'],
            'fee_id'        => $feeId,
        ], 'สมัครสมาชิกสำเร็จ' . ($slip ? ' รอการตรวจสอบหลักฐาน' : ''));
    }

    /**
     * POST  ?controller=auth&action=forget-password
     * ส่งอีเมลรีเซ็ตรหัสผ่าน
     */
    public function forgetPassword(): void
    {
        $this->requirePost();
        $input = $this->input();
        $email = trim($input['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // ส่ง success เสมอเพื่อความปลอดภัย (ไม่เปิดเผยว่ามี email หรือไม่)
            Response::success(null, 'หากอีเมลนี้มีอยู่ในระบบ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านให้');
        }

        $users = $this->model('UserModel');
        $user  = $users->findByEmail($email);

        if ($user) {
            try {
                $resets   = $this->model('PasswordResetModel');
                $settings = $this->model('SettingsModel');

                // สร้าง token (หมดอายุ 30 นาที)
                $token = $resets->createToken((int)$user['id'], 30);

                // สร้าง reset URL
                $baseUrl = defined('BASE_URL') ? rtrim(BASE_URL, '/') : '.';
                $resetUrl = $baseUrl . '/auth/?page=resetpass&token=' . urlencode($token);

                $siteName = $settings->get('site_name_short', SITE_NAME_SHORT);
                $userName = $user['full_name'] ?: $user['username'];

                // สร้างเนื้อหาอีเมล
                $bodyContent = '
                    <p>สวัสดีคุณ <strong>' . htmlspecialchars($userName) . '</strong>,</p>
                    <p>เราได้รับคำขอรีเซ็ตรหัสผ่านของคุณ กรุณากดปุ่มด้านล่างเพื่อตั้งรหัสผ่านใหม่:</p>
                    <div style="text-align:center;margin:28px 0;">
                        <a href="' . htmlspecialchars($resetUrl) . '" style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;text-decoration:none;border-radius:10px;font-weight:700;font-size:1rem;box-shadow:0 4px 12px rgba(109,40,217,.3);">
                            🔑 รีเซ็ตรหัสผ่าน
                        </a>
                    </div>
                    <p style="color:#6b7280;font-size:.9rem;">หรือคัดลอกลิงก์นี้ไปวางในเบราว์เซอร์:</p>
                    <p style="background:#f3f4f6;padding:10px 14px;border-radius:8px;word-break:break-all;font-size:.85rem;color:#4b5563;">
                        ' . htmlspecialchars($resetUrl) . '
                    </p>
                    <div style="margin-top:24px;padding:14px;background:#fef3c7;border-radius:8px;font-size:.88rem;">
                        <strong>⚠️ สำคัญ:</strong>
                        <ul style="margin:6px 0 0;padding-left:20px;">
                            <li>ลิงก์นี้จะหมดอายุใน <strong>30 นาที</strong></li>
                            <li>ใช้ได้ครั้งเดียวเท่านั้น</li>
                            <li>หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน กรุณาเพิกเฉยอีเมลนี้</li>
                        </ul>
                    </div>';

                $htmlBody = Mailer::buildTemplate(
                    'รีเซ็ตรหัสผ่าน — ' . $siteName,
                    $bodyContent
                );

                Mailer::send($email, $userName, 'รีเซ็ตรหัสผ่าน — ' . $siteName, $htmlBody);

                Auth::logActivity(
                    (int)$user['id'],
                    'password_reset_request',
                    'auth',
                    'ขอรีเซ็ตรหัสผ่านทางอีเมล'
                );
            } catch (\Exception $e) {
                // Log error but don't expose to user
                error_log('Password reset email error: ' . $e->getMessage());
            }
        }

        // ส่ง success เสมอเพื่อความปลอดภัย
        Response::success(null, 'หากอีเมลนี้มีอยู่ในระบบ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านให้');
    }

    /**
     * POST  ?controller=auth&action=reset-password
     * รีเซ็ตรหัสผ่านด้วย token
     */
    public function resetPassword(): void
    {
        $this->requirePost();
        $input = $this->input();

        $token       = trim($input['token'] ?? '');
        $newPassword = $input['password'] ?? '';

        if (empty($token)) {
            Response::error('ไม่พบ Token สำหรับรีเซ็ตรหัสผ่าน');
        }
        if (strlen($newPassword) < 6) {
            Response::error('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
        }

        $resets = $this->model('PasswordResetModel');
        $reset  = $resets->findValidToken($token);

        if (!$reset) {
            Response::error('ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว กรุณาขอลิงก์ใหม่', 400);
        }

        $users = $this->model('UserModel');
        $user  = $users->find((int)$reset['user_id']);

        if (!$user) {
            Response::error('ไม่พบบัญชีผู้ใช้');
        }

        // อัปเดตรหัสผ่าน
        $users->update(
            ['password' => Auth::hashPassword($newPassword)],
            ['id' => $user['id']]
        );

        // ทำเครื่องหมาย token ว่าใช้แล้ว
        $resets->markUsed((int)$reset['id']);

        // ลบ token เก่าอื่นๆ ของ user นี้
        $resets->deleteExpired();

        Auth::logActivity(
            (int)$user['id'],
            'password_reset',
            'auth',
            'รีเซ็ตรหัสผ่านสำเร็จ'
        );

        Response::success(null, 'เปลี่ยนรหัสผ่านสำเร็จ กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่');
    }

    /**
     * GET  ?controller=auth&action=verify-reset-token
     * ตรวจสอบว่า token ยังใช้ได้อยู่หรือไม่
     */
    public function verifyResetToken(): void
    {
        $token = trim($_GET['token'] ?? '');

        if (empty($token)) {
            Response::error('ไม่พบ Token');
        }

        $resets = $this->model('PasswordResetModel');
        $reset  = $resets->findValidToken($token);

        if (!$reset) {
            Response::error('ลิงก์รีเซ็ตรหัสผ่านไม่ถูกต้องหรือหมดอายุแล้ว', 400);
        }

        // ส่งข้อมูลพื้นฐาน (ไม่เปิดเผยข้อมูลส่วนตัวมากเกินไป)
        $users = $this->model('UserModel');
        $user  = $users->find((int)$reset['user_id'], ['id', 'email', 'full_name', 'username']);

        $maskedEmail = '';
        if ($user && $user['email']) {
            $parts = explode('@', $user['email']);
            $name  = $parts[0];
            $maskedEmail = substr($name, 0, 2) . str_repeat('*', max(strlen($name) - 2, 2)) . '@' . $parts[1];
        }

        Response::success([
            'valid'        => true,
            'email'        => $maskedEmail,
            'expires_at'   => $reset['expires_at'],
        ]);
    }

    /**
     * POST  ?controller=auth&action=change-password
     * เปลี่ยนรหัสผ่านของตัวเอง (ต้องใส่รหัสผ่านเก่า)
     */
    public function changePassword(): void
    {
        $this->requirePost();
        $input = $this->input();

        $currentPassword = $input['current_password'] ?? '';
        $newPassword     = $input['new_password'] ?? '';
        $confirmPassword = $input['confirm_password'] ?? '';

        if ($currentPassword === '' || $newPassword === '') {
            Response::error('กรุณากรอกรหัสผ่านปัจจุบันและรหัสผ่านใหม่');
        }
        if (strlen($newPassword) < 6) {
            Response::error('รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร');
        }
        if ($newPassword !== $confirmPassword) {
            Response::error('รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน');
        }

        $users = $this->model('UserModel');
        $user  = $users->find((int)$this->currentUser['id']);

        if (!$user || !Auth::verifyPassword($currentPassword, $user['password'])) {
            Response::error('รหัสผ่านปัจจุบันไม่ถูกต้อง', 400);
        }

        $users->update(
            ['password' => Auth::hashPassword($newPassword)],
            ['id' => $user['id']]
        );

        Auth::logActivity(
            (int)$user['id'],
            'change_password',
            'auth',
            'เปลี่ยนรหัสผ่านด้วยตัวเอง'
        );

        Response::success(null, 'เปลี่ยนรหัสผ่านสำเร็จ');
    }

    /**
     * POST  ?controller=auth&action=test-smtp
     * Admin: ส่งอีเมลทดสอบ SMTP
     */
    public function testSmtp(): void
    {
        $this->requirePost();
        $input = $this->input();
        $email = trim($input['email'] ?? '');

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Response::error('กรุณาระบุอีเมลที่ถูกต้อง');
        }

        try {
            $settings = $this->model('SettingsModel');
            $siteName = $settings->get('site_name_short', SITE_NAME_SHORT);

            $bodyContent = '
                <p>สวัสดี,</p>
                <p>นี่คืออีเมลทดสอบจากระบบ <strong>' . htmlspecialchars($siteName) . '</strong></p>
                <p>หากคุณได้รับอีเมลนี้ แสดงว่าการตั้งค่า SMTP ถูกต้องเรียบร้อยแล้ว ✅</p>
                <div style="margin-top:20px;padding:14px;background:#d1fae5;border-radius:8px;text-align:center;">
                    <strong style="color:#065f46;">🎉 SMTP ทำงานปกติ!</strong>
                </div>';

            $htmlBody = Mailer::buildTemplate(
                'ทดสอบ SMTP — ' . $siteName,
                $bodyContent
            );

            Mailer::send($email, 'Admin', 'ทดสอบ SMTP — ' . $siteName, $htmlBody);

            Response::success(null, 'ส่งอีเมลทดสอบไปยัง ' . $email . ' สำเร็จ');
        } catch (\Exception $e) {
            Response::error('ส่งอีเมลไม่สำเร็จ: ' . $e->getMessage());
        }
    }

    /**
     * สร้าง full_name จาก prefix + first_name + last_name
     * คำนำหน้าชิดกับชื่อ เช่น "นางวราภรณ์ โพนะทา"
     */
    public static function buildFullName(string $prefix, string $firstName, string $lastName): string
    {
        $name = $prefix . $firstName;
        if ($lastName !== '') {
            $name .= ' ' . $lastName;
        }
        return trim($name) ?: '';
    }

    /* ---- internal ---- */

    /**
     * Save base64 data URL image to uploads folder
     */
    private function saveBase64Image(string $base64, string $subFolder = 'payment_slips'): ?string
    {
        if (!preg_match('/^data:image\/(jpeg|png|webp|gif);base64,(.+)$/i', $base64, $m)) {
            return null;
        }
        $ext  = strtolower($m[1]) === 'jpeg' ? 'jpg' : strtolower($m[1]);
        $data = base64_decode($m[2]);
        if (!$data) return null;

        $dir = defined('UPLOAD_DIR') ? UPLOAD_DIR : (__DIR__ . '/../../uploads/');
        $dir .= $subFolder . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $fileName = date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $filePath = $dir . $fileName;
        file_put_contents($filePath, $data);

        $siteDomain = defined('SITE_DOMAIN') ? SITE_DOMAIN : '';
        return $siteDomain ? 'uploads/' . $siteDomain . '/' . $subFolder . '/' . $fileName : 'uploads/' . $subFolder . '/' . $fileName;
    }

    private function verifyGoogleToken(string $token): ?array
    {
        $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($token));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => true]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200) return null;
        $d = json_decode($body, true);
        if (!$d || !isset($d['sub'], $d['email'])) return null;

        // Check audience against configured client ID (constant or DB)
        $clientId = GOOGLE_CLIENT_ID;
        if (!$clientId) {
            $settings = $this->model('SettingsModel');
            $clientId = $settings->get('google_client_id') ?? '';
        }
        if ($clientId && ($d['aud'] ?? '') !== $clientId) return null;

        return $d;
    }

    /**
     * Validate and normalize Google picture URL before saving.
     */
    private function sanitizeGooglePictureUrl(string $url): string
    {
        $url = trim($url);
        if ($url === '') return '';
        if (!filter_var($url, FILTER_VALIDATE_URL)) return '';

        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        if (!in_array($scheme, ['http', 'https'], true)) return '';

        return $url;
    }
}
