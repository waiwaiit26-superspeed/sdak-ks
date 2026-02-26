<?php
/**
 * Login Page — Modern design (no AdminLTE)
 */
$basePath = $basePath ?? './';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ</title>
    <link id="dynamic-favicon" rel="icon" type="image/png" href="">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Sarabun', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gradient-auth, linear-gradient(135deg, #4c1d95 0%, #7c3aed 40%, #a78bfa 70%, #c4b5fd 100%));
            background-size: 400% 400%;
            animation: gradientMove 15s ease infinite;
            overflow: hidden;
            position: relative;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Floating particles */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s ease-in-out infinite;
        }
        body::before {
            width: 400px; height: 400px;
            top: -100px; right: -100px;
            animation-delay: 0s;
        }
        body::after {
            width: 300px; height: 300px;
            bottom: -80px; left: -80px;
            animation-delay: -7s;
        }
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }

        .login-wrapper {
            position: relative;
            z-index: 10;
            width: 420px;
            max-width: 92vw;
            padding: 20px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo img {
            max-height: 80px;
            max-width: 200px;
            filter: drop-shadow(0 4px 20px rgba(0,0,0,.3));
            transition: transform .3s;
        }
        .login-logo img:hover { transform: scale(1.05); }

        .login-logo .logo-text {
            display: block;
            color: #fff;
            font-size: 1.8rem;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0,0,0,.3);
            letter-spacing: 2px;
            margin-top: 8px;
        }
        .login-logo .logo-text i {
            font-size: 2rem;
            vertical-align: middle;
            margin-right: 4px;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 40px 36px 32px;
            box-shadow:
                0 20px 60px rgba(0, 0, 0, 0.15),
                0 8px 20px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            animation: slideUp .6s cubic-bezier(.16, 1, .3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h2 {
            text-align: center;
            font-weight: 700;
            font-size: 1.6rem;
            color: #1e1b4b;
            margin-bottom: 4px;
        }
        .login-card .subtitle {
            text-align: center;
            color: #6b7280;
            font-size: .9rem;
            margin-bottom: 28px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }
        .form-group input {
            width: 100%;
            padding: 14px 48px 14px 18px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-family: inherit;
            background: #fafafa;
            transition: all .3s;
            outline: none;
            color: #1f2937;
        }
        .form-group input::placeholder {
            color: #9ca3af;
        }
        .form-group input:focus {
            border-color: var(--primary, #7c3aed);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(124, 58, 237, .12);
        }
        .form-group input.is-invalid {
            border-color: #ef4444;
        }
        .form-group input.is-invalid:focus {
            box-shadow: 0 0 0 4px rgba(239, 68, 68, .12);
        }
        .form-group .field-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 1.1rem;
            pointer-events: none;
        }
        .form-group .field-icon.clickable {
            pointer-events: auto;
            cursor: pointer;
            transition: color .2s;
        }
        .form-group .field-icon.clickable:hover {
            color: var(--primary, #7c3aed);
        }

        .invalid-feedback {
            display: none;
            font-size: .82rem;
            color: #ef4444;
            margin-top: 4px;
            padding-left: 4px;
        }
        .form-group input.is-invalid ~ .invalid-feedback,
        .form-group input.is-invalid + .field-icon + .invalid-feedback {
            display: block;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 1.05rem;
            font-weight: 700;
            font-family: inherit;
            color: #fff;
            background: var(--gradient-component, linear-gradient(135deg, #7c3aed 0%, #6d28d9 50%, #5b21b6 100%));
            cursor: pointer;
            transition: all .3s;
            box-shadow: 0 4px 15px rgba(109, 40, 217, .4);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(109, 40, 217, .5);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .btn-login:disabled {
            opacity: .7;
            cursor: not-allowed;
            transform: none;
        }
        .btn-login .spinner-border {
            width: 1.1rem;
            height: 1.1rem;
            border-width: 2px;
        }

        .login-links {
            margin-top: 24px;
            text-align: center;
        }
        .login-links a {
            color: var(--primary, #7c3aed);
            text-decoration: none;
            font-weight: 500;
            font-size: .92rem;
            transition: color .2s;
        }
        .login-links a:hover {
            color: var(--primary-dark, #5b21b6);
            text-decoration: underline;
        }
        .login-links .divider {
            display: inline-block;
            width: 4px;
            height: 4px;
            background: #d1d5db;
            border-radius: 50%;
            margin: 0 12px;
            vertical-align: middle;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card { padding: 32px 24px 24px; border-radius: 16px; }
            .login-card h2 { font-size: 1.4rem; }
            .login-wrapper { padding: 16px; }
        }

        /* SweetAlert z-index */
        .swal2-container { z-index: 9999 !important; }

        /* hide logo text when image is loaded */
        .login-logo.has-image .logo-text { display: none; }

        /* Divider */
        .or-divider {
            display: flex;
            align-items: center;
            margin: 20px 0 16px;
            gap: 12px;
        }
        .or-divider::before, .or-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }
        .or-divider span {
            color: #9ca3af;
            font-size: .85rem;
            font-weight: 500;
            white-space: nowrap;
        }

        /* Google Sign-In button */
        .btn-google {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            color: #374151;
            background: #fff;
            cursor: pointer;
            transition: all .3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-google:hover {
            border-color: var(--primary, #7c3aed);
            background: #faf5ff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,.08);
        }
        .btn-google:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .btn-google svg { width: 20px; height: 20px; flex-shrink: 0; }

        #googleBtnWrap { display: none; }

        .google-btn-container {
            position: relative;
        }
        #gsiButtonOverlay {
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            opacity: 0.001;
            z-index: 2;
        }
        #gsiButtonOverlay iframe {
            width: 100% !important;
            height: 100% !important;
        }

        /* ─── Google Setup Modal ─── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.55);
            z-index: 1050;
            align-items: center;
            justify-content: center;
            animation: fadeIn .3s;
        }
        .modal-overlay.show { display: flex; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .setup-modal {
            background: #fff;
            border-radius: 20px;
            width: 480px;
            max-width: 94vw;
            max-height: 90vh;
            overflow-y: auto;
            padding: 0;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
            animation: slideUp .4s cubic-bezier(.16,1,.3,1);
        }
        .setup-modal-header {
            background: var(--gradient-component, linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%));
            color: #fff;
            padding: 24px 28px 20px;
            border-radius: 20px 20px 0 0;
        }
        .setup-modal-header h3 { margin: 0 0 4px; font-size: 1.3rem; font-weight: 700; }
        .setup-modal-header p { margin: 0; font-size: .88rem; opacity: .85; }
        .setup-modal-body { padding: 24px 28px 28px; }
        .setup-modal-body .step { display: none; }
        .setup-modal-body .step.active { display: block; }

        /* Step indicators */
        .step-indicators { display: flex; gap: 8px; margin-bottom: 24px; }
        .step-dot {
            flex: 1; height: 4px; border-radius: 4px; background: #e5e7eb; transition: background .3s;
        }
        .step-dot.active { background: var(--primary, #7c3aed); }
        .step-dot.done { background: #10b981; }

        /* Member type cards */
        .member-type-options { display: flex; flex-direction: column; gap: 12px; }
        .member-type-card {
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            padding: 16px 18px;
            cursor: pointer;
            transition: all .25s;
            display: flex;
            align-items: center;
            gap: 14px;
            position: relative;
        }
        .member-type-card:hover { border-color: var(--primary-light, #a78bfa); background: #faf5ff; }
        .member-type-card.selected { border-color: var(--primary, #7c3aed); background: #f5f3ff; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
        .member-type-card .type-icon {
            width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;
        }
        .member-type-card .type-info h4 { margin: 0 0 2px; font-size: 1rem; font-weight: 600; color: #1e1b4b; }
        .member-type-card .type-info p { margin: 0; font-size: .82rem; color: #6b7280; }
        .member-type-card .type-fee {
            margin-left: auto; font-weight: 700; font-size: .95rem; color: var(--primary, #7c3aed); white-space: nowrap;
        }
        .member-type-card .check-mark {
            display: none; position: absolute; top: 10px; right: 12px; color: var(--primary, #7c3aed); font-size: 1.1rem;
        }
        .member-type-card.selected .check-mark { display: block; }

        /* Slip upload area */
        .slip-upload-area {
            border: 2px dashed #d1d5db;
            border-radius: 14px;
            padding: 32px 20px;
            text-align: center;
            cursor: pointer;
            transition: all .25s;
            background: #fafafa;
            position: relative;
        }
        .slip-upload-area:hover { border-color: var(--primary, #7c3aed); background: #faf5ff; }
        .slip-upload-area.has-file { border-color: #10b981; background: #f0fdf4; }
        .slip-upload-area .upload-icon { font-size: 2.5rem; color: #9ca3af; margin-bottom: 8px; }
        .slip-upload-area.has-file .upload-icon { color: #10b981; }
        .slip-upload-area input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .slip-preview-img { max-height: 200px; border-radius: 8px; margin-top: 12px; }

        .btn-step {
            padding: 12px 24px;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all .2s;
        }
        .btn-step-primary {
            color: #fff;
            background: var(--gradient-component, linear-gradient(135deg, #7c3aed, #5b21b6));
            box-shadow: 0 4px 12px rgba(109,40,217,.3);
        }
        .btn-step-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(109,40,217,.4); }
        .btn-step-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
        .btn-step-outline {
            color: #6b7280;
            background: transparent;
            border: 2px solid #e5e7eb;
        }
        .btn-step-outline:hover { border-color: #9ca3af; color: #374151; }

        .bank-info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .bank-info-box .bi { color: #0284c7; margin-right: 6px; }
        .bank-info-box strong { color: #0c4a6e; }
        .bank-info-box .bank-row { margin-bottom: 4px; font-size: .92rem; }

        /* ─── Setup Modal Form Styles (AdminLTE-like) ─── */
        .setup-modal .setup-form-group {
            margin-bottom: 16px;
        }
        .setup-modal .setup-form-group label {
            display: block;
            font-weight: 600;
            font-size: .88rem;
            color: #374151;
            margin-bottom: 6px;
        }
        .setup-modal .setup-form-group label .text-danger { color: #dc3545; }
        .setup-modal .setup-input-group {
            display: flex;
            align-items: stretch;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            transition: border-color .2s, box-shadow .2s;
            background: #fff;
        }
        .setup-modal .setup-input-group:focus-within {
            border-color: var(--primary, #7c3aed);
            box-shadow: 0 0 0 3px rgba(124,58,237,.12);
        }
        .setup-modal .setup-input-group .input-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            background: #f8f5ff;
            color: var(--primary, #7c3aed);
            font-size: 1.1rem;
            flex-shrink: 0;
            border-right: 1px solid #e5e7eb;
        }
        .setup-modal .setup-input-group input,
        .setup-modal .setup-input-group select {
            flex: 1;
            border: none;
            outline: none;
            padding: 10px 14px;
            font-size: .95rem;
            font-family: inherit;
            background: transparent;
            color: #1e1b4b;
            min-width: 0;
        }
        .setup-modal .setup-input-group input::placeholder { color: #9ca3af; }
        .setup-modal .setup-input-group select { -webkit-appearance: auto; appearance: auto; cursor: pointer; }
        .setup-modal .setup-name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        @media (max-width: 480px) {
            .setup-modal .setup-name-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-logo" id="loginLogo">
        <a href="<?php echo $basePath; ?>">
            <img id="logoImg" src="" alt="<?php echo siteConfig('site_name_short'); ?>" style="display:none;">
            <span class="logo-text"><i class="bi bi-mortarboard-fill"></i> <?php echo siteConfig('site_name_short'); ?></span>
        </a>
    </div>

    <div class="login-card">
        <h2 id="loginTitle">เข้าสู่ระบบ</h2>
        <p class="subtitle" id="loginSubtitle"></p>

        <form id="loginForm" novalidate>
            <div class="form-group">
                <input type="text" id="login" name="login" placeholder="ชื่อผู้ใช้ หรือ อีเมล" autocomplete="username" required>
                <i class="bi bi-person field-icon"></i>
            </div>

            <div class="form-group">
                <input type="password" id="password" name="password" placeholder="รหัสผ่าน" autocomplete="current-password" required>
                <i class="bi bi-eye field-icon clickable toggle-password"></i>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
            </button>
        </form>

        <div id="googleBtnWrap">
            <div class="or-divider"><span>หรือ</span></div>
            <div class="google-btn-container">
                <button type="button" class="btn-google" id="btnGoogleDisplay">
                    <svg viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    เข้าสู่ระบบด้วย Google
                </button>
                <div id="gsiButtonOverlay"></div>
            </div>
        </div>

        <div class="login-links">
            <a href="<?php echo $basePath; ?>"><i class="bi bi-house-door"></i> หน้าหลัก</a>
            <span class="divider"></span>
            <a href="./?page=forgetpass">ลืมรหัสผ่าน?</a>
            <span class="divider"></span>
            <a href="./?page=register">สมัครสมาชิกใหม่</a>
        </div>
    </div>
</div>

<!-- Google Registration Setup Modal -->
<div class="modal-overlay" id="setupModal">
    <div class="setup-modal">
        <div class="setup-modal-header">
            <h3><i class="bi bi-person-plus me-1"></i> ตั้งค่าบัญชีสมาชิก</h3>
            <p>กรุณากรอกข้อมูล เลือกประเภทสมาชิก และชำระค่าธรรมเนียม</p>
        </div>
        <div class="setup-modal-body">
            <div class="step-indicators">
                <div class="step-dot active" data-step="1"></div>
                <div class="step-dot" data-step="2"></div>
                <div class="step-dot" data-step="3"></div>
            </div>

            <!-- Step 1: กรอกชื่อ -->
            <div class="step active" id="setupStep1">
                <h4 style="margin-bottom:4px;font-weight:700;color:#1e1b4b;"><i class="bi bi-person-vcard me-1" style="color:var(--primary, #7c3aed)"></i> กรอกข้อมูลชื่อ</h4>
                <p style="color:#6b7280;font-size:.88rem;margin-bottom:20px;">กรุณากรอกคำนำหน้า ชื่อ และนามสกุล เพื่อใช้งานในระบบ</p>

                <div class="setup-form-group">
                    <label><i class="bi bi-tag me-1"></i> คำนำหน้า <span class="text-danger">*</span></label>
                    <div class="setup-input-group">
                        <span class="input-icon"><i class="bi bi-chevron-down"></i></span>
                        <select id="setupPrefix" required>
                            <option value="">-- เลือกคำนำหน้า --</option>
                            <option value="นาย">นาย</option>
                            <option value="นาง">นาง</option>
                            <option value="นางสาว">นางสาว</option>
                            <option value="ดร.">ดร.</option>
                            <option value="ผศ.">ผศ.</option>
                            <option value="รศ.">รศ.</option>
                            <option value="พันตำรวจเอก">พันตำรวจเอก</option>
                            <option value="พันตำรวจโท">พันตำรวจโท</option>
                            <option value="พันตำรวจตรี">พันตำรวจตรี</option>
                            <option value="ว่าที่ร้อยตรี">ว่าที่ร้อยตรี</option>
                            <option value="other">อื่นๆ (กรอกเอง)</option>
                        </select>
                    </div>
                    <div class="setup-input-group" id="setupPrefixOtherWrap" style="display:none;margin-top:8px;">
                        <span class="input-icon"><i class="bi bi-pencil"></i></span>
                        <input type="text" id="setupPrefixOther" placeholder="กรอกคำนำหน้า เช่น พ.ต.อ.">
                    </div>
                </div>

                <div class="setup-name-row">
                    <div class="setup-form-group">
                        <label><i class="bi bi-person me-1"></i> ชื่อ <span class="text-danger">*</span></label>
                        <div class="setup-input-group">
                            <span class="input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" id="setupFirstName" placeholder="กรอกชื่อ" required>
                        </div>
                    </div>
                    <div class="setup-form-group">
                        <label><i class="bi bi-person me-1"></i> นามสกุล <span class="text-danger">*</span></label>
                        <div class="setup-input-group">
                            <span class="input-icon"><i class="bi bi-person"></i></span>
                            <input type="text" id="setupLastName" placeholder="กรอกนามสกุล" required>
                        </div>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;">
                    <button type="button" class="btn-step btn-step-primary" id="btnSetupNameNext" disabled>ถัดไป <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 2: เลือกประเภทสมาชิก -->
            <div class="step" id="setupStep2">
                <h4 style="margin-bottom:4px;font-weight:700;color:#1e1b4b;">เลือกประเภทสมาชิก</h4>
                <p style="color:#6b7280;font-size:.88rem;margin-bottom:18px;">ค่าธรรมเนียมจะแตกต่างกันตามประเภทสมาชิก</p>
                <div class="member-type-options" id="memberTypeOptions">
                    <!-- Generated by JS -->
                </div>
                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:24px;">
                    <button type="button" class="btn-step btn-step-outline" id="btnSetupNameBack"><i class="bi bi-arrow-left"></i> ย้อนกลับ</button>
                    <button type="button" class="btn-step btn-step-primary" id="btnSetupNext" disabled>ถัดไป <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 3: อัปโหลดสลิป -->
            <div class="step" id="setupStep3">
                <h4 style="margin-bottom:4px;font-weight:700;color:#1e1b4b;">อัปโหลดหลักฐานการชำระ</h4>
                <p style="color:#6b7280;font-size:.88rem;margin-bottom:14px;">
                    ค่าธรรมเนียม: <strong id="setupFeeAmount" class="text-primary">-</strong> บาท
                    <span id="setupFeeType" style="font-size:.8rem;color:#9ca3af;"></span>
                </p>

                <div class="bank-info-box" id="setupBankInfo" style="display:none;">
                    <div class="bank-row"><i class="bi bi-bank"></i> ธนาคาร: <strong id="setupBankName">-</strong></div>
                    <div class="bank-row"><i class="bi bi-person"></i> ชื่อบัญชี: <strong id="setupBankAccName">-</strong></div>
                    <div class="bank-row"><i class="bi bi-credit-card"></i> เลขที่บัญชี: <strong id="setupBankAccNo" style="font-size:1.05rem;">-</strong></div>
                </div>

                <div class="slip-upload-area" id="setupSlipArea">
                    <div class="upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                    <div class="upload-text">คลิกหรือลากไฟล์สลิปมาวางที่นี่</div>
                    <div style="font-size:.8rem;color:#9ca3af;margin-top:4px;">รองรับ JPG, PNG, WEBP ขนาดไม่เกิน 5 MB</div>
                    <input type="file" id="setupSlipFile" accept="image/jpeg,image/png,image/webp">
                    <img id="setupSlipPreview" class="slip-preview-img" src="" style="display:none;" alt="preview">
                </div>

                <div style="display:flex;justify-content:space-between;gap:10px;margin-top:24px;">
                    <button type="button" class="btn-step btn-step-outline" id="btnSetupBack"><i class="bi bi-arrow-left"></i> ย้อนกลับ</button>
                    <div style="display:flex;gap:10px;">
                        <button type="button" class="btn-step btn-step-outline" id="btnSetupSkipSlip">ข้ามอัปโหลด</button>
                        <button type="button" class="btn-step btn-step-primary" id="btnSetupComplete">
                            <i class="bi bi-check-lg"></i> เสร็จสิ้น
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>const BASE_PATH = '<?php echo $basePath; ?>';</script>
<script src="<?php echo $basePath; ?>assets/js/api.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
$(function () {
    // ─── Load dynamic logo from settings ───
    (async function () {
        try {
            const result = await API.getSettings();
            if (result.success && result.data) {
                const s = result.data;
                // Login logo
                const loginLogo = s.logo_login || s.logo_web || '';
                if (loginLogo) {
                    const src = loginLogo.startsWith('http') ? loginLogo : (BASE_PATH + loginLogo);
                    $('#logoImg').attr('src', src).show();
                    $('#loginLogo').addClass('has-image');
                }
                // Favicon
                const fav = s.logo_favicon || s.logo_web || '';
                if (fav) {
                    const favSrc = fav.startsWith('http') ? fav : (BASE_PATH + fav);
                    $('#dynamic-favicon').attr('href', favSrc);
                }

                // Dynamic title & subtitle
                if (s.login_title) {
                    $('#loginTitle').text(s.login_title);
                }
                const subtitle = s.login_subtitle || s.site_name || '';
                if (subtitle) {
                    $('#loginSubtitle').text(subtitle);
                }
                // Page title
                const shortName = s.site_name_short || '<?php echo siteConfig('site_name_short'); ?>';
                document.title = 'เข้าสู่ระบบ | ' + shortName;

                // Google Sign-In
                const gClientId = s.google_client_id || '';
                if (gClientId) {
                    $('#googleBtnWrap').show();
                    window._gClientId = gClientId;
                    // Init Google Identity Services when SDK is loaded
                    function initGSI() {
                        if (typeof google === 'undefined' || !google.accounts) {
                            setTimeout(initGSI, 200);
                            return;
                        }
                        google.accounts.id.initialize({
                            client_id: gClientId,
                            callback: handleGoogleCredential,
                            auto_select: false,
                            ux_mode: 'popup',
                        });
                        // Render an invisible Google button overlaying the custom button
                        // This ensures popup flow works on all platforms (Android Chrome, etc.)
                        const overlayEl = document.getElementById('gsiButtonOverlay');
                        if (overlayEl) {
                            google.accounts.id.renderButton(overlayEl, {
                                type: 'standard',
                                theme: 'outline',
                                size: 'large',
                                width: overlayEl.parentElement.offsetWidth || 380,
                                text: 'signin_with',
                                logo_alignment: 'center',
                            });
                        }
                    }
                    initGSI();
                }
            }
        } catch (e) { /* ignore */ }
    })();

    // ─── Already logged in? ───
    if (API.isLoggedIn()) {
        API.get(API.apiUrl('auth', 'me')).then(function(result) {
            if (result.success) {
                const user = result.data || API.getUser();
                window.location.href = (user && user.role === 'admin') ? '../admin/' : '../member/';
            } else {
                API.clearAuth();
                window.location.reload(); // reload to re-init everything properly
            }
        }).catch(function() {
            API.clearAuth();
            window.location.reload();
        });
        return;
    }

    // ─── Toggle password ───
    $(document).on('click', '.toggle-password', function () {
        const input = $(this).closest('.form-group').find('input');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // ─── Google Sign-In ───
    // Shared state for setup flow
    let _setupData = null;
    let _selectedMemberType = null;
    let _uploadedSlipUrl = null;
    let _googleToken = null; // เก็บ google credential ไว้ส่งตอน complete

    function handleGoogleCredential(response) {
        const btn = $('#btnGoogleDisplay');
        _googleToken = response.credential; // เก็บไว้ใช้ตอน complete
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังเข้าสู่ระบบ...');
        API.googleLogin(response.credential)
            .then(function(result) {
                if (result.success) {
                    // ─── ผู้ใช้ใหม่: ยังไม่มี record → ต้องเลือกประเภทสมาชิกก่อน ───
                    if (result.data.needs_setup) {
                        _setupData = result.data;
                        openSetupModal(result.data);
                        btn.prop('disabled', false).html(googleBtnHTML());
                        return;
                    }

                    // ─── ผู้ใช้เดิม: มี record แล้ว → saveAuth & login ───
                    API.saveAuth(result.data);
                    Swal.fire({
                        icon: 'success',
                        title: 'เข้าสู่ระบบสำเร็จ',
                        text: 'กำลังนำท่านเข้าสู่ระบบ...',
                        timer: 1200,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    });
                    setTimeout(function() {
                        const redirect = new URLSearchParams(window.location.search).get('redirect');
                        if (redirect) { window.location.href = '../' + redirect; }
                        else if (result.data.user.role === 'admin') { window.location.href = '../admin/'; }
                        else { window.location.href = '../member/'; }
                    }, 1000);
                } else {
                    Swal.fire({ icon: 'error', title: 'เข้าสู่ระบบไม่สำเร็จ', text: result.message });
                    btn.prop('disabled', false).html(googleBtnHTML());
                }
            })
            .catch(function(err) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message });
                btn.prop('disabled', false).html(googleBtnHTML());
            });
    }

    function googleBtnHTML() {
        return '<svg viewBox="0 0 24 24" style="width:20px;height:20px"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg> เข้าสู่ระบบด้วย Google';
    }

    // ─── Setup Modal Logic ───
    function openSetupModal(data) {
        _selectedMemberType = null;
        _uploadedSlipUrl = null;
        const feeInfo = data.fee_info || {};
        const gUser = data.google_user || {};

        // Pre-fill name from Google
        const gName = gUser.name || '';
        const gParts = gName.trim().split(/\s+/);
        $('#setupFirstName').val(gParts[0] || '');
        $('#setupLastName').val(gParts.slice(1).join(' ') || '');
        $('#setupPrefix').val('');
        validateNameStep();

        const types = [
            { key: 'ordinary',  label: 'สมาชิกสามัญ',    desc: 'รองผู้อำนวยการ/อดีตรองผู้อำนวยการ', icon: 'bi-star-fill',    iconBg: '#fbbf24', iconColor: '#92400e' },
            { key: 'associate', label: 'สมาชิกวิสามัญ',  desc: 'ผู้สนับสนุนสมาคม',                    icon: 'bi-people-fill',  iconBg: '#60a5fa', iconColor: '#1e3a5f' },
            { key: 'affiliate', label: 'สมาชิกสมทบ',     desc: 'สมาชิกทั่วไป',                        icon: 'bi-person-fill',  iconBg: '#a78bfa', iconColor: '#3b0764' },
        ];

        let html = '';
        types.forEach(function(t) {
            const fi = feeInfo[t.key] || { amount: 0, mode: 'none' };
            let feeText = 'ไม่มีค่าธรรมเนียม';
            if (fi.mode !== 'none' && fi.amount > 0) {
                feeText = new Intl.NumberFormat('th-TH').format(fi.amount) + ' ฿';
                if (fi.mode === 'onetime') feeText += ' <small style="color:#9ca3af">(ครั้งเดียว)</small>';
                else if (fi.mode === 'annual') feeText += ' <small style="color:#9ca3af">(/ปี)</small>';
            }
            html += `
                <div class="member-type-card" data-type="${t.key}" data-amount="${fi.amount}" data-mode="${fi.mode}">
                    <div class="type-icon" style="background:${t.iconBg}30;color:${t.iconColor}"><i class="bi ${t.icon}"></i></div>
                    <div class="type-info"><h4>${t.label}</h4><p>${t.desc}</p></div>
                    <div class="type-fee">${feeText}</div>
                    <i class="bi bi-check-circle-fill check-mark"></i>
                </div>`;
        });
        $('#memberTypeOptions').html(html);

        // Show bank info
        const bi = feeInfo.bank_info || {};
        if (bi.bank_name || bi.account_number) {
            $('#setupBankName').text(bi.bank_name || '-');
            $('#setupBankAccName').text(bi.account_name || '-');
            $('#setupBankAccNo').text(bi.account_number || '-');
        }

        // Reset to step 1
        goToSetupStep(1);
        $('#setupModal').addClass('show');
    }

    function goToSetupStep(n) {
        $('.setup-modal-body .step').removeClass('active');
        $('#setupStep' + n).addClass('active');
        $('.step-dot').removeClass('active done');
        for (let i = 1; i <= 3; i++) {
            if (i < n) $(".step-dot[data-step='" + i + "']").addClass('done');
            else if (i === n) $(".step-dot[data-step='" + i + "']").addClass('active');
        }
    }

    // Toggle "other" prefix input
    $(document).on('change', '#setupPrefix', function() {
        if ($(this).val() === 'other') {
            $('#setupPrefixOtherWrap').slideDown(200);
            $('#setupPrefixOther').focus();
        } else {
            $('#setupPrefixOtherWrap').slideUp(200);
            $('#setupPrefixOther').val('');
        }
    });

    // Helper: get resolved prefix value
    function getResolvedPrefix() {
        const sel = $('#setupPrefix').val();
        if (sel === 'other') return $('#setupPrefixOther').val().trim();
        return sel || '';
    }

    // Validate name step fields
    function validateNameStep() {
        const prefix = getResolvedPrefix();
        const firstName = $('#setupFirstName').val().trim();
        const lastName = $('#setupLastName').val().trim();
        const valid = prefix && firstName && lastName;
        $('#btnSetupNameNext').prop('disabled', !valid);
    }
    $(document).on('input change', '#setupPrefix, #setupPrefixOther, #setupFirstName, #setupLastName', validateNameStep);

    // Name step → next to member type
    $('#btnSetupNameNext').on('click', function() {
        if (!getResolvedPrefix() || !$('#setupFirstName').val().trim() || !$('#setupLastName').val().trim()) return;
        goToSetupStep(2);
    });

    // Back from member type → name step
    $('#btnSetupNameBack').on('click', function() { goToSetupStep(1); });

    // Select member type
    $(document).on('click', '.member-type-card', function() {
        $('.member-type-card').removeClass('selected');
        $(this).addClass('selected');
        _selectedMemberType = $(this).data('type');
        $('#btnSetupNext').prop('disabled', false);
    });

    // Next → Step 2
    $('#btnSetupNext').on('click', function() {
        if (!_selectedMemberType) return;
        const card = $(`.member-type-card[data-type="${_selectedMemberType}"]`);
        const amount = parseFloat(card.data('amount')) || 0;
        const mode = card.data('mode');

        if (mode === 'none' || amount <= 0) {
            // ไม่มีค่าธรรมเนียม → ดำเนินการเลย
            submitSetup(null);
            return;
        }

        // แสดงข้อมูลค่าธรรมเนียมใน step 2
        $('#setupFeeAmount').text(new Intl.NumberFormat('th-TH').format(amount));
        $('#setupFeeType').text(mode === 'onetime' ? '(ชำระครั้งเดียว)' : '(ชำระรายปี)');

        const bi = (_setupData && _setupData.fee_info) ? _setupData.fee_info.bank_info : {};
        if (bi && (bi.bank_name || bi.account_number)) {
            $('#setupBankInfo').show();
        }

        // Reset upload
        _uploadedSlipUrl = null;
        $('#setupSlipFile').val('');
        $('#setupSlipPreview').hide();
        $('#setupSlipArea').removeClass('has-file');
        $('.upload-text').text('คลิกหรือลากไฟล์สลิปมาวางที่นี่');

        goToSetupStep(3);
    });

    // Back → Step 2
    $('#btnSetupBack').on('click', function() { goToSetupStep(2); });

    // Slip file change
    $('#setupSlipFile').on('change', function() {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'ไฟล์ใหญ่เกินไป', text: 'ขนาดไม่เกิน 5 MB' });
            $(this).val('');
            return;
        }
        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#setupSlipPreview').attr('src', e.target.result).show();
            $('#setupSlipArea').addClass('has-file');
            $('.upload-text').text(file.name);
        };
        reader.readAsDataURL(file);
    });

    // Skip → ไม่อนุญาต ต้องเลือกประเภทสมาชิก
    $('#btnSetupSkip').on('click', function() {
        Swal.fire({
            icon: 'info',
            title: 'กรุณาเลือกประเภทสมาชิก',
            text: 'ท่านจำเป็นต้องเลือกประเภทสมาชิกเพื่อดำเนินการสมัครให้เสร็จสมบูรณ์',
            confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#7c3aed',
        });
    });

    // Skip slip only
    $('#btnSetupSkipSlip').on('click', function() {
        submitSetup(null);
    });

    // Complete with slip
    $('#btnSetupComplete').on('click', function() {
        const fileInput = document.getElementById('setupSlipFile');
        if (!fileInput.files.length) {
            submitSetup(null);
            return;
        }

        // Upload file first — ใช้ google_token ส่ง auth แบบ temporary
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

        // อ่านไฟล์เป็น base64 แล้วส่งพร้อมกัน (ไม่ต้อง login ก่อน upload)
        const reader = new FileReader();
        reader.onload = function(e) {
            submitSetup(e.target.result); // ส่ง base64 data URL
        };
        reader.onerror = function() {
            Swal.fire({ icon: 'error', title: 'อ่านไฟล์ไม่ได้', text: 'กรุณาลองใหม่' });
            btn.prop('disabled', false).html('<i class="bi bi-check-lg"></i> เสร็จสิ้น');
        };
        reader.readAsDataURL(fileInput.files[0]);
    });

    function submitSetup(slipUrl) {
        const btn = $('#btnSetupComplete, #btnSetupSkipSlip, #btnSetupNext');
        btn.prop('disabled', true);

        if (!_googleToken) {
            Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่พบ Google Token กรุณาลองใหม่' });
            btn.prop('disabled', false);
            return;
        }

        API.completeGoogleRegister(_googleToken, _selectedMemberType || 'associate', slipUrl, {
            prefix: getResolvedPrefix(),
            first_name: $('#setupFirstName').val().trim(),
            last_name: $('#setupLastName').val().trim()
        })
            .then(function(result) {
                if (result.success) {
                    // สร้าง user สำเร็จ → saveAuth & redirect
                    API.saveAuth(result.data);
                    $('#setupModal').removeClass('show');
                    const hasSlip = !!slipUrl;
                    Swal.fire({
                        icon: 'success',
                        title: 'สมัครสมาชิกสำเร็จ!',
                        html: hasSlip
                            ? 'บัญชีของท่านอยู่ระหว่างรอการตรวจสอบหลักฐาน<br>และอนุมัติจากผู้ดูแลระบบ'
                            : 'บัญชีของท่านอยู่ระหว่างรอการอนุมัติ<br>คุณสามารถอัปโหลดสลิปได้ภายหลังที่หน้าค่าธรรมเนียม',
                        confirmButtonText: 'เข้าสู่ระบบ',
                        confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--primary').trim() || '#7c3aed',
                        allowOutsideClick: false
                    }).then(function() {
                        redirectAfterSetup();
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: result.message });
                    btn.prop('disabled', false);
                }
            })
            .catch(function(err) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message });
                btn.prop('disabled', false);
            });
    }

    function redirectAfterSetup() {
        $('#setupModal').removeClass('show');
        const user = API.getUser();
        const redirect = new URLSearchParams(window.location.search).get('redirect');
        if (redirect) { window.location.href = '../' + redirect; }
        else if (user && user.role === 'admin') { window.location.href = '../admin/'; }
        else { window.location.href = '../member/'; }
    }

    // Google button click is now handled by the invisible renderButton overlay
    // The rendered Google button sits on top of the custom button with opacity ~0
    // so the user sees the pretty custom button but clicks the real Google button.
    // No need for prompt() or manual fallback — renderButton popup works everywhere.

    // ─── Validate & submit ───
    $('#loginForm').validate({
        rules: {
            login:    { required: true, minlength: 3 },
            password: { required: true, minlength: 4 }
        },
        messages: {
            login:    { required: 'กรุณากรอกชื่อผู้ใช้หรืออีเมล', minlength: 'อย่างน้อย 3 ตัวอักษร' },
            password: { required: 'กรุณากรอกรหัสผ่าน', minlength: 'อย่างน้อย 4 ตัวอักษร' }
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorPlacement(error, element) {
            error.addClass('invalid-feedback');
            error.insertAfter(element.siblings('.field-icon'));
        },
        highlight(el) { $(el).addClass('is-invalid').removeClass('is-valid'); },
        unhighlight(el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
        submitHandler: function () {
            const btn = $('#btnLogin');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังเข้าสู่ระบบ...');

            API.login($('#login').val().trim(), $('#password').val())
                .then(function (result) {
                    if (result.success) {
                        API.saveAuth(result.data);
                        Swal.fire({
                            icon: 'success',
                            title: 'เข้าสู่ระบบสำเร็จ',
                            text: 'กำลังนำท่านเข้าสู่ระบบ...',
                            timer: 1200,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                        setTimeout(function () {
                            const redirect = new URLSearchParams(window.location.search).get('redirect');
                            if (redirect) {
                                window.location.href = '../' + redirect;
                            } else if (result.data.user.role === 'admin') {
                                window.location.href = '../admin/';
                            } else {
                                window.location.href = '../member/';
                            }
                        }, 1000);
                    } else {
                        Swal.fire({ icon: 'error', title: 'เข้าสู่ระบบไม่สำเร็จ', text: result.message || 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง' });
                        btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ');
                    }
                })
                .catch(function (err) {
                    Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: 'ไม่สามารถเชื่อมต่อได้: ' + err.message });
                    btn.prop('disabled', false).html('<i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ');
                });

            return false;
        }
    });
});
</script>

</body>
</html>
