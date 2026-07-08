<?php $pageTitle = 'สมัครสมาชิก'; $authBodyClass = 'register-page'; ?>
<?php
// ── Feature flag: ปิด/เปิด การสมัครด้วยฟอร์ม ──
// กำหนดใน config/sites/xxx.php → define('FORM_REGISTRATION_ENABLED', false/true);
// false = Google เป็นหลัก, ฟอร์มซ่อนไว้ (กดเปิดได้)
// true  = แสดงฟอร์มปกติ (พฤติกรรมเดิม)
$formRegEnabled = defined('FORM_REGISTRATION_ENABLED') ? FORM_REGISTRATION_ENABLED : true;
?>
<?php
$extraCss = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
<style>
    .modal-overlay {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,.55);
        z-index: 1050; align-items: center; justify-content: center; animation: fadeInSetup .3s;
    }
    .modal-overlay.show { display: flex; }
    @keyframes fadeInSetup { from { opacity: 0; } to { opacity: 1; } }
    @keyframes slideUpSetup { from { transform: translateY(40px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .setup-modal {
        background: #fff; border-radius: 20px; width: 480px; max-width: 94vw;
        max-height: 90vh; overflow-y: auto; padding: 0;
        box-shadow: 0 20px 60px rgba(0,0,0,.25); animation: slideUpSetup .4s cubic-bezier(.16,1,.3,1);
    }
    .setup-modal-header {
        background: var(--gradient-component, linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%));
        color: #fff; padding: 24px 28px 20px; border-radius: 20px 20px 0 0;
    }
    .setup-modal-header h3 { margin: 0 0 4px; font-size: 1.3rem; font-weight: 700; }
    .setup-modal-header p { margin: 0; font-size: .88rem; opacity: .85; }
    .setup-modal-body { padding: 24px 28px 28px; }
    .setup-modal-body .step { display: none; }
    .setup-modal-body .step.active { display: block; }
    .step-indicators { display: flex; gap: 8px; margin-bottom: 24px; }
    .step-dot { flex: 1; height: 4px; border-radius: 4px; background: #e5e7eb; transition: background .3s; }
    .step-dot.active { background: var(--primary, #7c3aed); }
    .step-dot.done { background: #10b981; }
    .member-type-options { display: flex; flex-direction: column; gap: 12px; }
    .member-type-card {
        border: 2px solid #e5e7eb; border-radius: 14px; padding: 16px 18px;
        cursor: pointer; transition: all .25s; display: flex; align-items: center;
        gap: 14px; position: relative;
    }
    .member-type-card:hover { border-color: var(--primary-light, #a78bfa); background: #faf5ff; }
    .member-type-card.selected { border-color: var(--primary, #7c3aed); background: #f5f3ff; box-shadow: 0 0 0 3px rgba(124,58,237,.15); }
    .member-type-card .type-icon {
        width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center;
        justify-content: center; font-size: 1.3rem; flex-shrink: 0;
    }
    .member-type-card .type-info h4 { margin: 0 0 2px; font-size: 1rem; font-weight: 600; color: #1e1b4b; }
    .member-type-card .type-info p { margin: 0; font-size: .82rem; color: #6b7280; }
    .member-type-card .type-fee { margin-left: auto; font-weight: 700; font-size: .95rem; color: var(--primary, #7c3aed); white-space: nowrap; }
    .member-type-card .check-mark { display: none; position: absolute; top: 10px; right: 12px; color: var(--primary, #7c3aed); font-size: 1.1rem; }
    .member-type-card.selected .check-mark { display: block; }
    .slip-upload-area {
        border: 2px dashed #d1d5db; border-radius: 14px; padding: 32px 20px;
        text-align: center; cursor: pointer; transition: all .25s; background: #fafafa; position: relative;
    }
    .slip-upload-area:hover { border-color: var(--primary, #7c3aed); background: #faf5ff; }
    .slip-upload-area.has-file { border-color: #10b981; background: #f0fdf4; }
    .slip-upload-area .upload-icon { font-size: 2.5rem; color: #9ca3af; margin-bottom: 8px; }
    .slip-upload-area.has-file .upload-icon { color: #10b981; }
    .slip-upload-area input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .slip-preview-img { max-height: 200px; border-radius: 8px; margin-top: 12px; }
    .btn-step {
        padding: 12px 24px; border: none; border-radius: 12px; font-size: 1rem;
        font-weight: 600; font-family: inherit; cursor: pointer; transition: all .2s;
    }
    .btn-step-primary {
        color: #fff; background: var(--gradient-component, linear-gradient(135deg, #7c3aed, #5b21b6));
        box-shadow: 0 4px 12px rgba(109,40,217,.3);
    }
    .btn-step-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(109,40,217,.4); }
    .btn-step-primary:disabled { opacity: .6; cursor: not-allowed; transform: none; }
    .btn-step-outline { color: #6b7280; background: transparent; border: 2px solid #e5e7eb; }
    .btn-step-outline:hover { border-color: #9ca3af; color: #374151; }
    .bank-info-box {
        background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 12px; padding: 16px; margin-bottom: 20px;
    }
    .bank-info-box .bi { color: #0284c7; margin-right: 6px; }
    .bank-info-box strong { color: #0c4a6e; }
    .bank-info-box .bank-row { margin-bottom: 4px; font-size: .92rem; }

    /* ─── Google Primary Mode ─── */
    .reg-google-primary { padding: 8px 0 4px; }
    .reg-google-badge {
        display: flex; align-items: center; justify-content: center; gap: 6px;
        font-size: .82rem; color: #6b7280; margin-bottom: 12px;
    }
    .reg-google-btn-primary {
        display: flex !important; align-items: center; justify-content: center;
        gap: 10px; padding: 13px 20px !important; font-size: 1rem !important;
        font-weight: 600 !important; border-radius: 10px !important;
        border: 2px solid #dadce0 !important; background: #fff !important;
        color: #3c4043 !important; transition: box-shadow .2s, border-color .2s !important;
        box-shadow: 0 1px 3px rgba(0,0,0,.08) !important;
    }
    .reg-google-btn-primary:hover {
        border-color: #4285F4 !important; box-shadow: 0 2px 8px rgba(66,133,244,.2) !important;
        background: #f8fbff !important;
    }
    .reg-form-toggle-wrap { padding: 4px 0; }
    #btnShowFormReg {
        color: #9ca3af; text-decoration: none; font-size: .82rem;
        border: 1px dashed #d1d5db; border-radius: 8px;
        padding: 7px 18px; transition: all .2s;
    }
    #btnShowFormReg:hover { color: #374151; border-color: #9ca3af; background: #f9fafb; text-decoration: none; }

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
';
?>
<?php include ROOT_PATH . 'templates/auth/header.php'; ?>

<div class="register-box" style="width:800px; max-width:95vw; margin-top:30px;">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <div class="register-logo mb-2" id="registerLogo">
                <a href="<?php echo $basePath; ?>">
                    <img id="regLogoImg" src="" alt="Logo" style="display:none;max-height:70px;">
                    <span class="reg-logo-text"><i class="bi bi-mortarboard-fill"></i> <b><?php echo siteConfig('site_name_short'); ?></b></span>
                </a>
            </div>
            <h4>สมัครสมาชิก</h4>
            <p class="text-muted mb-0" id="regSubtitle"></p>
        </div>
        <div class="card-body register-card-body">
            <?php if (!$formRegEnabled): ?>
            <!-- ── Google Primary Section (FORM_REGISTRATION_ENABLED = false) ── -->
            <div id="googleRegBtnWrap" style="display:none;">
                <div class="reg-google-primary">
                    <div class="reg-google-badge">
                        <i class="bi bi-shield-check" style="color:#34A853;font-size:1rem;"></i>
                        แนะนำ: สมัครด้วยบัญชี Google เพื่อความสะดวกและปลอดภัย
                    </div>
                    <button type="button" class="btn btn-outline-secondary btn-block reg-google-btn-primary" id="btnGoogleReg">
                        <svg viewBox="0 0 24 24" style="width:22px;height:22px;flex-shrink:0"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                        สมัครด้วย Google
                    </button>
                </div>
            </div>
            <div class="text-center mt-3 reg-form-toggle-wrap" id="formRegToggleWrap">
                <small class="text-muted d-block mb-2">หรือ</small>
                <button type="button" class="btn" id="btnShowFormReg">
                    <i class="fas fa-keyboard mr-1"></i> สมัครด้วยการกรอกฟอร์ม
                </button>
            </div>
            <div id="formRegSection" style="display:none;">
                <hr class="mt-3 mb-3">
            <?php else: ?>
            <!-- ── Form Primary Section (FORM_REGISTRATION_ENABLED = true) ── -->
            <div id="formRegSection">
            <?php endif; ?>

            <form id="registerForm" novalidate>
                <!-- ข้อมูลทั่วไป -->
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="w-auto px-2 small font-weight-bold text-primary">ข้อมูลทั่วไป</legend>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="member_type">ประเภทสมาชิก <span class="text-danger">*</span></label>
                                <select class="form-control" id="member_type" name="member_type" required>
                                    <option value="">-- เลือก --</option>
                                    <option value="ordinary">สมาชิกสามัญ</option>
                                    <option value="associate">สมาชิกวิสามัญ</option>
                                    <option value="affiliate">สมาชิกสมทบ</option>
                                </select>
                                <small class="text-muted">วิสามัญ = เกษียณ/เปลี่ยนตำแหน่ง</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="national_id">เลขบัตรประชาชน</label>
                                <input type="text" class="form-control" id="national_id" name="national_id" maxlength="13">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone">มือถือ</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="0812345678">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="prefix">คำนำหน้า <span class="text-danger">*</span></label>
                                <select class="form-control" id="prefix" name="prefix" required>
                                    <option value="">-- เลือก --</option>
                                    <option value="นาย">นาย</option>
                                    <option value="นาง">นาง</option>
                                    <option value="นางสาว">นางสาว</option>
                                    <option value="ดร.">ดร.</option>
                                    <option value="ผศ.">ผศ.</option>
                                    <option value="รศ.">รศ.</option>
                                    <option value="ศ.">ศ.</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="first_name">ชื่อ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="กรุณากรอกคำนำหน้า" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="last_name">นามสกุล <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                    </div>
                    <!-- ── Name Match Panel ── -->
                    <div id="nameMatchPanel" style="display:none;" class="mt-1 mb-2">
                        <div style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:0 6px 6px 0;padding:9px 14px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;">
                            <span style="color:#92400e;font-size:1rem;"><i class="bi bi-people me-1"></i><strong>พบชื่อที่ตรงกันในระบบ</strong> — ท่านเป็นคนนี้หรือไม่?</span>
                            <button type="button" onclick="$('#nameMatchPanel').hide()" style="background:none;border:none;color:#9ca3af;cursor:pointer;padding:0;font-size:1.1rem;line-height:1;">&times;</button>
                        </div>
                        <div id="nameMatchList"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="birth_date">วันเกิด</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="birth_date" name="birth_date" placeholder="เลือกวันเกิด" readonly>
                                    <div class="input-group-append"><div class="input-group-text"><i class="fas fa-calendar-alt"></i></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="position">ตำแหน่ง</label>
                                <select class="form-control" id="position" name="position">
                                    <option value="">-- เลือก --</option>
                                    <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                                    <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                                    <option value="other">อื่นๆ (กรอกเอง)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4" id="positionOtherWrap" style="display:none">
                            <div class="form-group">
                                <label for="position_other">ระบุตำแหน่ง <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="position_other" name="position_other" placeholder="กรอกตำแหน่งของท่าน">
                            </div>
                        </div>
                        <div class="col-md-4" id="regAcademicRankWrap" style="display:none">
                            <div class="form-group">
                                <label>วิทยฐานะ</label>
                                <select class="form-control" id="reg_academic_rank" name="academic_rank">
                                    <option value="">-- เลือก --</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- ที่อยู่ปัจจุบัน -->
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="w-auto px-2 small font-weight-bold text-primary">ที่อยู่ปัจจุบัน</legend>
                    <div class="row">
                        <div class="col-md-2"><div class="form-group"><label>เลขที่</label><input type="text" class="form-control" id="h_no"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>ซอย</label><input type="text" class="form-control" id="h_soi" placeholder="ไม่มีให้กรอก -"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>หมู่ที่</label><input type="text" class="form-control" id="h_moo"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>ถนน</label><input type="text" class="form-control" id="h_road"></div></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>ค้นหาที่อยู่</label>
                                <input type="text" class="form-control" id="h_search" placeholder="พิมพ์ ตำบล/อำเภอ/จังหวัด">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="h_subdistrict" name="h_subdistrict"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="h_district" name="h_district"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>จังหวัด</label><input type="text" class="form-control" id="h_province" name="h_province"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="h_postal" name="h_postal" maxlength="5"></div></div>
                    </div>
                </fieldset>

                <!-- สถานที่ทำงาน -->
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="w-auto px-2 small font-weight-bold text-primary">สถานที่ทำงาน</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="school_organization">โรงเรียน/หน่วยงาน</label>
                                <div class="input-group">
                                    <select class="form-control" id="school_prefix" style="max-width:160px">
                                        <option value="โรงเรียน">โรงเรียน</option>
                                        <option value="สพม.">สพม.</option>
                                        <option value="สพป.">สพป.</option>
                                        <option value="สำนักงาน">สำนักงาน</option>
                                        <option value="">อื่นๆ (ไม่มีคำนำหน้า)</option>
                                    </select>
                                    <input type="text" class="form-control" id="school_organization" name="school_organization" placeholder="ชื่อโรงเรียน/หน่วยงาน">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="work_phone">โทรศัพท์ (โรงเรียน)</label>
                                <input type="tel" class="form-control" id="work_phone">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>สังกัดเขตพื้นที่</label>
                                <select class="form-control" id="education_area">
                                    <option value="">-- เลือก --</option>
                                    <option>สพป.</option><option>สพม.</option><option>สพอ.</option>
                                    <option>สช.</option><option>อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2"><div class="form-group"><label>เลขที่</label><input type="text" class="form-control" id="w_no"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>ซอย</label><input type="text" class="form-control" id="w_soi" placeholder="ไม่มีให้กรอก -"></div></div>
                        <div class="col-md-2"><div class="form-group"><label>หมู่</label><input type="text" class="form-control" id="w_moo"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>ถนน</label><input type="text" class="form-control" id="w_road" placeholder="ไม่มีให้กรอก -"></div></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>ค้นหาที่อยู่</label>
                                <input type="text" class="form-control" id="w_search" placeholder="พิมพ์ ตำบล/อำเภอ/จังหวัด">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3"><div class="form-group"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="w_subdistrict" name="w_subdistrict"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="w_district" name="w_district"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>จังหวัด</label><input type="text" class="form-control" id="w_province" name="w_province"></div></div>
                        <div class="col-md-3"><div class="form-group"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="w_postal" name="w_postal" maxlength="5"></div></div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>ภาค</label>
                                <select class="form-control" id="region">
                                    <option value="">-- เลือก --</option>
                                    <option>ภาคเหนือ</option><option>ภาคกลาง</option>
                                    <option>ภาคตะวันออกเฉียงเหนือ</option><option>ภาคใต้</option>
                                    <option>ภาคตะวันออก</option><option>ภาคตะวันตก</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- บัญชีผู้ใช้ -->
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="w-auto px-2 small font-weight-bold text-primary">บัญชีผู้ใช้</legend>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="username" name="username" required>
                                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-user"></span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">อีเมล</label>
                                <div class="input-group">
                                    <input type="email" class="form-control" id="email" name="email">
                                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-envelope"></span></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reg_password">รหัสผ่าน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="reg_password" name="password" required>
                                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirm">ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                                    <div class="input-group-append"><div class="input-group-text"><span class="fas fa-lock"></span></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="callout callout-info">
                    <p class="mb-0"><i class="fas fa-info-circle mr-1"></i>
                    หลังจากสมัครสมาชิก ระบบจะนำท่านไปชำระค่าธรรมเนียมสมาชิกทันที</p>
                </div>

                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block" id="btnRegister">
                            <i class="fas fa-user-plus mr-1"></i> สมัครสมาชิก
                        </button>
                    </div>
                </div>
            </form>

            </div><!-- /#formRegSection -->

            <?php if ($formRegEnabled): ?>
            <div id="googleRegBtnWrap" style="display:none;">
                <div class="text-center my-2"><small class="text-muted">หรือ</small></div>
                <button type="button" class="btn btn-outline-secondary btn-block" id="btnGoogleReg" style="display:flex;align-items:center;justify-content:center;gap:8px;">
                    <svg viewBox="0 0 24 24" style="width:18px;height:18px"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                    สมัครด้วย Google
                </button>
            </div>
            <?php endif; ?>

            <p class="mt-3 mb-0 text-center">
                <a href="<?php echo $basePath; ?>"><i class="bi bi-house-door"></i> หน้าหลัก</a>
                <span class="mx-2">|</span>
                มีบัญชีอยู่แล้ว? <a href="./?page=login">เข้าสู่ระบบ</a>
            </p>
        </div>
    </div>
</div>

<!-- Account Link Request Modal -->
<div class="modal-overlay" id="linkRequestModal">
    <div class="setup-modal" style="max-width:460px;">
        <div class="setup-modal-header" style="background:linear-gradient(135deg,#0369a1,#0284c7);">
            <h3><i class="bi bi-link-45deg me-1"></i> ยื่นคำขอผูกบัญชี</h3>
            <p id="linkReqTargetName" style="font-weight:600;font-size:.95rem;margin-bottom:2px;"></p>
            <p style="opacity:.8;font-size:.82rem;margin:0;">รอ admin อนุมัติก่อนจึงจะเข้าสู่ระบบได้</p>
        </div>
        <div class="setup-modal-body">
            <!-- Email link form -->
            <div id="linkEmailForm">
                <div class="setup-form-group">
                    <label><i class="bi bi-envelope me-1"></i> อีเมล <span class="text-danger">*</span></label>
                    <div class="setup-input-group">
                        <span class="input-icon"><i class="bi bi-envelope"></i></span>
                        <input type="email" id="linkEmail" placeholder="example@email.com" autocomplete="off">
                    </div>
                </div>
                <div class="setup-form-group">
                    <label><i class="bi bi-person me-1"></i> ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                    <div class="setup-input-group">
                        <span class="input-icon"><i class="bi bi-person"></i></span>
                        <input type="text" id="linkUsername" placeholder="a-z 0-9 _ ยาว 3-50 ตัว" autocomplete="off">
                    </div>
                </div>
                <div class="setup-form-group">
                    <label><i class="bi bi-lock me-1"></i> รหัสผ่าน <span class="text-danger">*</span></label>
                    <div class="setup-input-group">
                        <span class="input-icon"><i class="bi bi-lock"></i></span>
                        <input type="password" id="linkPassword" placeholder="อย่างน้อย 6 ตัวอักษร">
                    </div>
                </div>
                <div class="setup-form-group">
                    <label><i class="bi bi-lock-fill me-1"></i> ยืนยันรหัสผ่าน <span class="text-danger">*</span></label>
                    <div class="setup-input-group">
                        <span class="input-icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" id="linkPasswordConfirm" placeholder="กรอกรหัสผ่านอีกครั้ง">
                    </div>
                </div>
            </div>
            <!-- Google link (hidden — handled in wizard) -->
            <div id="linkGoogleForm" style="display:none;">
                <div class="callout callout-info py-2 px-3">
                    <small><i class="bi bi-google me-1"></i> ระบบจะผูกบัญชี Google ของท่านกับสมาชิกนี้</small>
                </div>
            </div>
            <div id="linkReqError" class="alert alert-danger py-2 mt-2" style="display:none;font-size:.88rem;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;gap:10px;padding:0 28px 24px;">
            <button type="button" class="btn-step btn-step-outline" onclick="closeLinkModal()">ยกเลิก</button>
            <button type="button" class="btn-step btn-step-primary" id="btnSubmitLink">
                <i class="bi bi-send me-1"></i> ส่งคำขอ
            </button>
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

                <!-- Name match results inside wizard -->
                <div id="setupNameMatchPanel" style="display:none;margin-top:16px;">
                    <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:10px;padding:12px 14px;margin-bottom:10px;">
                        <small style="color:#92400e;"><i class="bi bi-search me-1"></i> พบชื่อที่ตรงกันในระบบ — ท่านเป็นคนนี้หรือไม่?</small>
                    </div>
                    <div id="setupNameMatchList"></div>
                    <div style="margin-top:10px;">
                        <button type="button" class="btn-step btn-step-outline" style="font-size:.85rem;padding:8px 16px;" onclick="skipNameMatch()">ไม่ใช่คนเหล่านี้ → ดำเนินการต่อ</button>
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;" id="setupNameNextRow">
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

<?php include ROOT_PATH . 'templates/auth/scripts.php'; ?>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- jquery.Thailand.js -->
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
$(function () {
    if (API.isLoggedIn()) { window.location.href = '../'; return; }

    // Load dynamic logo & favicon
    (async function () {
        try {
            const result = await API.getSettings();
            if (result.success && result.data) {
                const s = result.data;
                const logo = s.logo_login || s.logo_web || '';
                if (logo) {
                    const src = logo.startsWith('http') ? logo : (BASE_PATH + logo);
                    $('#regLogoImg').attr('src', src).show();
                    $('.reg-logo-text').hide();
                }
                const fav = s.logo_favicon || s.logo_web || '';
                if (fav) {
                    const favSrc = fav.startsWith('http') ? fav : (BASE_PATH + fav);
                    $('#dynamic-favicon').attr('href', favSrc);
                }

                // Dynamic subtitle
                const subtitle = s.login_subtitle || s.site_name || '';
                if (subtitle) {
                    $('#regSubtitle').text(subtitle);
                }
                // Page title
                const shortName = s.site_name_short || '<?php echo siteConfig('site_name_short'); ?>';
                document.title = 'สมัครสมาชิก | ' + shortName;

                // Google Sign-In
                const gClientId = s.google_client_id || '';
                if (gClientId) {
                    $('#googleRegBtnWrap').show();
                    window._gClientId = gClientId;
                    function initGSI() {
                        if (typeof google === 'undefined' || !google.accounts) {
                            setTimeout(initGSI, 200);
                            return;
                        }
                        google.accounts.id.initialize({
                            client_id: gClientId,
                            callback: handleGoogleRegCredential,
                            auto_select: false,
                        });
                    }
                    initGSI();
                }<?php if (!$formRegEnabled): ?> else {
                    // ไม่มี Google Client ID — แสดงฟอร์มโดยตรง
                    $('#formRegToggleWrap').hide();
                    $('#formRegSection').show();
                }<?php endif; ?>
            }
        } catch (e) { /* ignore */ }
    })();

    // Populate member_type <select> from API
    (async function() {
        try {
            const res = await API.getMemberTypes();
            if (res.success && Array.isArray(res.data) && res.data.length) {
                const sel = $('#member_type');
                sel.find('option:not(:first)').remove();
                res.data.forEach(t => {
                    if (t.type_key !== 'honorary') { // honorary = admin only
                        sel.append(`<option value="${t.type_key}">${t.label}</option>`);
                    }
                });
            }
        } catch(e) {}
    })();

    // ─── Google Sign-In handler ───
    let _setupData = null;

<?php if (!$formRegEnabled): ?>
    // ─── Form Registration Toggle (FORM_REGISTRATION_ENABLED = false) ───
    $('#btnShowFormReg').on('click', function() {
        $('#formRegToggleWrap').slideUp(200, function() {
            $('#formRegSection').slideDown(300);
            $('html, body').animate({ scrollTop: $('.register-box').offset().top - 20 }, 300);
        });
    });
<?php endif; ?>

    let _selectedMemberType = null;
    let _uploadedSlipUrl = null;
    let _googleToken = null;

    function googleRegBtnHTML() {
        return '<svg viewBox="0 0 24 24" style="width:18px;height:18px"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.27-4.74 3.27-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18A10.97 10.97 0 0 0 1 12c0 1.77.42 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg> สมัครด้วย Google';
    }

    window.handleGoogleRegCredential = function(response) {
        const btn = $('#btnGoogleReg');
        _googleToken = response.credential;
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');
        API.googleLogin(response.credential)
            .then(function(result) {
                if (result.success) {
                    // ─── ผู้ใช้ใหม่: ยังไม่มี record → ต้องเลือกประเภทสมาชิกก่อน ───
                    if (result.data.needs_setup) {
                        _setupData = result.data;
                        openSetupModal(result.data);
                        btn.prop('disabled', false).html(googleRegBtnHTML());
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
                        if (result.data.user.role === 'admin') { window.location.href = '../admin/'; }
                        else { window.location.href = '../member/'; }
                    }, 1000);
                } else {
                    Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: result.message });
                }
                btn.prop('disabled', false).html(googleRegBtnHTML());
            })
            .catch(function(err) {
                Swal.fire({ icon: 'error', title: 'เกิดข้อผิดพลาด', text: err.message });
                btn.prop('disabled', false).html(googleRegBtnHTML());
            });
    };

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

        // Build types from DB data (member_types) with fallback
        const memberTypes = data.member_types || {};
        const defaultTypes = [
            { key: 'ordinary',  label: 'สมาชิกสามัญ',    desc: '', icon: 'bi-star-fill',    iconBg: '#fbbf24', iconColor: '#92400e' },
            { key: 'associate', label: 'สมาชิกวิสามัญ',  desc: '', icon: 'bi-people-fill',  iconBg: '#60a5fa', iconColor: '#1e3a5f' },
            { key: 'affiliate', label: 'สมาชิกสมทบ',     desc: '', icon: 'bi-person-fill',  iconBg: '#a78bfa', iconColor: '#3b0764' },
        ];
        let types;
        if (Object.keys(memberTypes).length > 0) {
            types = Object.entries(memberTypes).map(([key, t]) => ({
                key,
                label:     t.label || key,
                desc:      t.description || '',
                icon:      (t.icon || 'fas fa-user').replace(/^fas?\s+fa-/, 'bi-'),
                iconBg:    t.icon_bg  || '#6b7280',
                iconColor: t.icon_color || '#fff',
            }));
        } else {
            types = defaultTypes;
        }

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
            if (i < n) $(`.step-dot[data-step="${i}"]`).addClass('done');
            else if (i === n) $(`.step-dot[data-step="${i}"]`).addClass('active');
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

    // ── Name Match Search (Form Register) ─────────────────────────────────────
    let _nameSearchTimer   = null;
    let _linkTargetId      = null;
    let _linkGoogleToken   = null; // set when triggered from Google wizard
    const _searchGenMap    = {};   // race-condition guard: per-containerSelector generation counter

    function buildMatchCard(m, mode) {
        const hasEmail = m.has_email;
        const school   = m.school_organization ? `<small class="text-muted">${App.escapeHtml(m.school_organization)}</small>` : '';
        const pos      = m.position ? `<span class="badge badge-light border mr-1">${App.escapeHtml(m.position)}</span>` : '';
        let actionHtml = '';
        if (hasEmail) {
            actionHtml = `<div class="mt-2 alert alert-info py-2 px-3 mb-0" style="font-size:.85rem;">
                <i class="bi bi-envelope me-1"></i> มีอีเมลในระบบ: <strong>${App.escapeHtml(m.email_hint)}</strong>
                <br><small>หากนี่คือท่าน กรุณา <a href="./?page=login">เข้าสู่ระบบด้วยอีเมลนี้</a> แทน</small>
            </div>`;
        } else {
            if (mode === 'google') {
                actionHtml = `<button type="button" class="btn btn-sm btn-outline-primary mt-2"
                    onclick="openLinkModal(${m.id}, '${App.escapeHtml(m.full_name)}', 'google')">
                    <i class="bi bi-link-45deg me-1"></i> ใช่คนนี้ — ผูกบัญชี Google
                </button>`;
            } else {
                actionHtml = `<button type="button" class="btn btn-sm btn-outline-primary mt-2"
                    onclick="openLinkModal(${m.id}, '${App.escapeHtml(m.full_name)}', 'email')">
                    <i class="bi bi-link-45deg me-1"></i> ใช่คนนี้ — ผูกอีเมลและรหัสผ่าน
                </button>`;
            }
        }
        return `<div class="border rounded p-3 mb-2 bg-white">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-1">
                <div>
                    <strong style="font-size:1.1rem;">${App.escapeHtml(m.full_name)}</strong><br>
                    ${pos}${school}
                </div>
            </div>
            ${actionHtml}
        </div>`;
    }

    // params = string หรือ { first, last } หรือ { q }
    // callbacks = { onNoResults: fn }  (optional)
    async function runNameSearch(params, containerSelector, mode, callbacks) {
        const $list  = $(containerSelector);
        const $panel = $list.parent(); // ใช้ .parent() ตรงๆ แทน closest()

        // ── Race-condition guard: นับ generation ไว้, ถ้า response กลับมาช้า
        //    แต่มี request ใหม่กว่าแล้ว → ทิ้ง response เก่าทิ้ง
        if (!_searchGenMap[containerSelector]) _searchGenMap[containerSelector] = 0;
        const myGen = ++_searchGenMap[containerSelector];

        let apiParams;
        if (typeof params === 'string') {
            if (params.length < 2) { $panel.hide(); return; }
            apiParams = { q: params };
        } else {
            apiParams = {};
            if (params.first && params.first.length >= 2) apiParams.first = params.first;
            if (params.last  && params.last.length  >= 2) apiParams.last  = params.last;
            if (!apiParams.first && !apiParams.last) {
                $panel.hide();
                if (callbacks && callbacks.onNoResults) callbacks.onNoResults();
                return;
            }
        }
        const res = await API.searchMembersByName(apiParams);

        // ถ้า response นี้ล้าสมัยแล้ว (มี request ใหม่กว่าตาม gen) → ทิ้งเงียบๆ
        if (_searchGenMap[containerSelector] !== myGen) return;

        if (!res.success || !res.data || res.data.length === 0) {
            $panel.hide();
            if (callbacks && callbacks.onNoResults) callbacks.onNoResults();
            return;
        }
        $list.html(res.data.map(m => buildMatchCard(m, mode)).join(''));
        $panel.show();
    }

    // Form register: debounce on first_name + last_name input (OR search)
    $('#first_name, #last_name').on('input', function () {
        clearTimeout(_nameSearchTimer);
        const first = $('#first_name').val().trim();
        const last  = $('#last_name').val().trim();
        if (!first && !last) { $('#nameMatchPanel').hide(); return; }
        // แสดง loading ทันที ไม่ต้องรอ debounce
        if (first.length >= 2 || last.length >= 2) {
            $('#nameMatchList').html('<div class="text-muted py-2 text-center" style="font-size:.85rem;"><span class="spinner-border spinner-border-sm mr-1"></span> กำลังค้นหา...</div>');
            $('#nameMatchPanel').show();
        }
        _nameSearchTimer = setTimeout(function () {
            runNameSearch({ first, last }, '#nameMatchList', 'email');
        }, 350);
    });

    // ── Name Match Search (Google Wizard Step 1) ─────────────────────────────
    let _wizardNameSearchTimer = null;
    $('#setupFirstName, #setupLastName').on('input', function () {
        clearTimeout(_wizardNameSearchTimer);
        const first = $('#setupFirstName').val().trim();
        const last  = $('#setupLastName').val().trim();
        if (!first && !last) { $('#setupNameMatchPanel').hide(); $('#setupNameNextRow').show(); return; }
        // แสดง loading ทันที
        if (first.length >= 2 || last.length >= 2) {
            $('#setupNameMatchList').html('<div class="text-muted py-2 text-center" style="font-size:.85rem;"><span class="spinner-border spinner-border-sm mr-1"></span> กำลังค้นหา...</div>');
            $('#setupNameMatchPanel').show();
            $('#setupNameNextRow').hide();
        }
        _wizardNameSearchTimer = setTimeout(function () {
            runNameSearch({ first, last }, '#setupNameMatchList', 'google', {
                onNoResults: function() { $('#setupNameNextRow').show(); }
            });
        }, 350);
    });

    function skipNameMatch() {
        $('#setupNameMatchPanel').hide();
        goToSetupStep(2);
    }

    // ── Link Modal ─────────────────────────────────────────────────────────────
    window.openLinkModal = function openLinkModal(targetId, targetName, requestType) {
        _linkTargetId = targetId;
        // If triggered from Google wizard, use the current google token
        if (requestType === 'google') {
            _linkGoogleToken = _googleToken || null;
        }
        $('#linkReqTargetName').text(targetName);
        $('#linkReqError').hide();
        if (requestType === 'google') {
            $('#linkEmailForm').hide();
            $('#linkGoogleForm').show();
        } else {
            $('#linkEmailForm').show();
            $('#linkGoogleForm').hide();
            $('#linkEmail, #linkUsername, #linkPassword, #linkPasswordConfirm').val('');
        }
        $('#btnSubmitLink').data('type', requestType).prop('disabled', false)
            .html('<i class="bi bi-send me-1"></i> ส่งคำขอ');
        $('#linkRequestModal').addClass('show');
    }

    window.closeLinkModal = function closeLinkModal() {
        $('#linkRequestModal').removeClass('show');
        _linkTargetId = null;
        _linkGoogleToken = null;
    }
    $('#linkRequestModal').on('click', function (e) {
        if ($(e.target).is('#linkRequestModal')) closeLinkModal();
    });

    $('#btnSubmitLink').on('click', async function () {
        const btn = $(this);
        const requestType = btn.data('type');
        if (!_linkTargetId) return;
        $('#linkReqError').hide();

        let payload = { target_user_id: _linkTargetId, request_type: requestType };

        if (requestType === 'email') {
            const email   = $('#linkEmail').val().trim();
            const uname   = $('#linkUsername').val().trim();
            const pass    = $('#linkPassword').val();
            const confirm = $('#linkPasswordConfirm').val();
            if (!email || !uname || !pass || !confirm) {
                $('#linkReqError').text('กรุณากรอกข้อมูลให้ครบทุกช่อง').show(); return;
            }
            if (pass !== confirm) {
                $('#linkReqError').text('รหัสผ่านไม่ตรงกัน').show(); return;
            }
            if (pass.length < 6) {
                $('#linkReqError').text('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร').show(); return;
            }
            payload.email = email;
            payload.proposed_username = uname;
            payload.proposed_password = pass;
        } else {
            // Google: use token stored from wizard
            if (!_linkGoogleToken) {
                $('#linkReqError').text('ไม่พบข้อมูล Google Token กรุณาลองใหม่').show(); return;
            }
            payload.google_token = _linkGoogleToken;
        }

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');
        const res = await API.requestAccountLink(payload);
        btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> ส่งคำขอ');

        if (res.success) {
            closeLinkModal();
            Swal.fire({
                icon: 'success',
                title: 'ส่งคำขอสำเร็จ!',
                html: 'ระบบได้รับคำขอของท่านแล้ว<br><small class="text-muted">กรุณารอ admin อนุมัติก่อนจึงจะเข้าสู่ระบบได้</small>',
                confirmButtonText: 'รับทราบ',
            });
        } else {
            $('#linkReqError').text(res.message || 'เกิดข้อผิดพลาด').show();
        }
    });

    // ── Name Match Search (Form Register) end ─────────────────────────────────

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

    // Name step → check for matches first, then proceed
    $('#btnSetupNameNext').on('click', async function() {
        if (!getResolvedPrefix() || !$('#setupFirstName').val().trim() || !$('#setupLastName').val().trim()) return;
        const q = ($('#setupFirstName').val().trim() + ' ' + $('#setupLastName').val().trim()).trim();
        // Check for name matches before proceeding
        const res = await API.searchMembersByName(q);
        if (res.success && res.data && res.data.length > 0) {
            $('#setupNameMatchList').html(res.data.map(m => buildMatchCard(m, 'google')).join(''));
            $('#setupNameMatchPanel').show();
            $('#setupNameNextRow').hide();
        } else {
            $('#setupNameMatchPanel').hide();
            goToSetupStep(2);
        }
    });

    // Back from member type → name step
    $('#btnSetupNameBack').on('click', function() {
        $('#setupNameNextRow').show();
        $('#setupNameMatchPanel').hide();
        goToSetupStep(1);
    });

    // Select member type
    $(document).on('click', '.member-type-card', function() {
        $('.member-type-card').removeClass('selected');
        $(this).addClass('selected');
        _selectedMemberType = $(this).data('type');
        $('#btnSetupNext').prop('disabled', false);
    });

    // Next → Step 3
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

        // แสดงข้อมูลค่าธรรมเนียมใน step 3
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
        const reader = new FileReader();
        reader.onload = function(e) {
            $('#setupSlipPreview').attr('src', e.target.result).show();
            $('#setupSlipArea').addClass('has-file');
            $('.upload-text').text(file.name);
        };
        reader.readAsDataURL(file);
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

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

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
        if (user && user.role === 'admin') { window.location.href = '../admin/'; }
        else { window.location.href = '../member/'; }
    }

    $('#btnGoogleReg').on('click', function() {
        if (typeof google === 'undefined' || !google.accounts) {
            Swal.fire({ icon: 'warning', title: 'Google SDK', text: 'กำลังโหลด กรุณาลองใหม่อีกครั้ง' });
            return;
        }
        google.accounts.id.prompt(function(notification) {
            if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                // One Tap not available, fallback info
                Swal.fire({ icon: 'info', title: 'Google Login', text: 'กรุณาอนุญาต popup หรือลองเข้าจากหน้า Login', timer: 3000 });
            }
        });
    });

    // ─── Flatpickr วันเกิด (Buddhist Era) ───
    const fp = flatpickr('#birth_date', {
        locale: 'th',
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        allowInput: false,
        disableMobile: true,
        formatDate: function(date, format) {
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear() + 543;
            return d + '/' + m + '/' + y;
        },
        parseDate: function(dateStr) {
            const parts = dateStr.split('/');
            if (parts.length !== 3) return null;
            return new Date(parseInt(parts[2]) - 543, parseInt(parts[1]) - 1, parseInt(parts[0]));
        }
    });

    // ─── Academic rank options by position ───
    const academicRankOptions = {
        'รองผู้อำนวยการสถานศึกษา': [
            'รองผู้อำนวยการชำนาญการ',
            'รองผู้อำนวยการชำนาญการพิเศษ',
            'รองผู้อำนวยการเชี่ยวชาญ'
        ],
        'ผู้อำนวยการสถานศึกษา': [
            'ผู้อำนวยการชำนาญการ',
            'ผู้อำนวยการชำนาญการพิเศษ',
            'ผู้อำนวยการเชี่ยวชาญ'
        ]
    };

    function updateRegAcademicRank(position) {
        const $wrap = $('#regAcademicRankWrap');
        const $select = $('#reg_academic_rank');
        const options = academicRankOptions[position];
        if (options) {
            $select.html('<option value="">-- เลือก --</option>' + options.map(o => '<option value="' + o + '">' + o + '</option>').join(''));
            $wrap.slideDown(200);
        } else {
            $select.html('<option value="">-- เลือก --</option>');
            $wrap.slideUp(200);
        }
    }

    // ─── Position toggle ───
    $('#position').on('change', function () {
        const val = $(this).val();
        if (val === 'other') {
            $('#positionOtherWrap').slideDown(200);
            $('#position_other').prop('required', true).focus();
        } else {
            $('#positionOtherWrap').slideUp(200);
            $('#position_other').val('').prop('required', false).removeClass('is-invalid');
        }
        updateRegAcademicRank(val);
    });

    // ─── jquery.Thailand.js — Home Address ───
    $.Thailand({
        $search: $('#h_search'),
        $district: $('#h_subdistrict'),
        $amphoe: $('#h_district'),
        $province: $('#h_province'),
        $zipcode: $('#h_postal'),
        onDataFill: function(data) {
            $('#h_subdistrict').val(data.district);
            $('#h_district').val(data.amphoe);
            $('#h_province').val(data.province);
            $('#h_postal').val(data.zipcode);
        }
    });

    // ─── jquery.Thailand.js — Work Address ───
    $.Thailand({
        $search: $('#w_search'),
        $district: $('#w_subdistrict'),
        $amphoe: $('#w_district'),
        $province: $('#w_province'),
        $zipcode: $('#w_postal'),
        onDataFill: function(data) {
            $('#w_subdistrict').val(data.district);
            $('#w_district').val(data.amphoe);
            $('#w_province').val(data.province);
            $('#w_postal').val(data.zipcode);
        }
    });

    $('#registerForm').validate({
        rules: {
            first_name:       { required: true, minlength: 2 },
            last_name:        { required: true, minlength: 2 },
            username:         { required: true, minlength: 4 },
            email:            { email: true },
            password:         { required: true, minlength: 6 },
            password_confirm: { required: true, equalTo: '#reg_password' },
            member_type:      { required: true },
            phone:            { },
            prefix:           { required: true },
            national_id:      { minlength: 13, maxlength: 13, digits: true },
            birth_date:       { },
            position:         { },
            position_other:   { required: { depends: function() { return $('#position').val() === 'other'; } }, minlength: 2 },
            school_organization: { },
            h_subdistrict:    { },
            h_district:       { },
            h_province:       { },
            h_postal:         { minlength: 5, maxlength: 5, digits: true },
            w_subdistrict:    { },
            w_district:       { },
            w_province:       { },
            w_postal:         { minlength: 5, maxlength: 5, digits: true }
        },
        messages: {
            first_name:       { required: 'กรุณากรอกชื่อ', minlength: 'อย่างน้อย 2 ตัวอักษร' },
            last_name:        { required: 'กรุณากรอกนามสกุล', minlength: 'อย่างน้อย 2 ตัวอักษร' },
            username:         { required: 'กรุณากรอกชื่อผู้ใช้', minlength: 'อย่างน้อย 4 ตัวอักษร' },
            email:            { email: 'รูปแบบอีเมลไม่ถูกต้อง' },
            password:         { required: 'กรุณากรอกรหัสผ่าน', minlength: 'อย่างน้อย 6 ตัวอักษร' },
            password_confirm: { required: 'กรุณายืนยันรหัสผ่าน', equalTo: 'รหัสผ่านไม่ตรงกัน' },
            member_type:      { required: 'กรุณาเลือกประเภทสมาชิก' },
            prefix:           { required: 'กรุณาเลือกคำนำหน้า' },
            national_id:      { minlength: 'ต้อง 13 หลัก', maxlength: 'ต้อง 13 หลัก', digits: 'กรอกเฉพาะตัวเลข' },
            position_other:   { required: 'กรุณาระบุตำแหน่ง', minlength: 'อย่างน้อย 2 ตัวอักษร' },
            h_postal:         { minlength: 'ต้อง 5 หลัก', maxlength: 'ต้อง 5 หลัก', digits: 'กรอกเฉพาะตัวเลข' },
            w_postal:         { minlength: 'ต้อง 5 หลัก', maxlength: 'ต้อง 5 หลัก', digits: 'กรอกเฉพาะตัวเลข' }
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorPlacement(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.input-group').length
                ? error.insertAfter(element.closest('.input-group'))
                : error.insertAfter(element);
        },
        highlight(el) { $(el).addClass('is-invalid').removeClass('is-valid'); },
        unhighlight(el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
        submitHandler: function () {
            const btn = $('#btnRegister');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังสมัคร...');

            /* ---- compose birth_date from flatpickr ---- */
            let birth_date = '';
            if (fp.selectedDates.length > 0) {
                const d = fp.selectedDates[0];
                birth_date = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }

            /* ---- compose position ---- */
            let positionVal = $('#position').val();
            if (positionVal === 'other') {
                positionVal = $('#position_other').val().trim();
            }
            const regPositionMap = {
                'รองผู้อำนวยการโรงเรียน': 'รองผู้อำนวยการสถานศึกษา',
                'ผู้อำนวยการโรงเรียน': 'ผู้อำนวยการสถานศึกษา'
            };
            if (regPositionMap[positionVal]) positionVal = regPositionMap[positionVal];

            /* ---- compose addresses ---- */
            const homeAddr = {
                no: $('#h_no').val().trim(), soi: $('#h_soi').val().trim(), moo: $('#h_moo').val().trim(),
                road: $('#h_road').val().trim(), subdistrict: $('#h_subdistrict').val().trim(),
                district: $('#h_district').val().trim(), province: $('#h_province').val().trim(),
                postal_code: $('#h_postal').val().trim()
            };
            const workAddr = {
                no: $('#w_no').val().trim(), soi: $('#w_soi').val().trim(), moo: $('#w_moo').val().trim(),
                road: $('#w_road').val().trim(), subdistrict: $('#w_subdistrict').val().trim(),
                district: $('#w_district').val().trim(), province: $('#w_province').val().trim(),
                postal_code: $('#w_postal').val().trim()
            };

            const firstName = $('#first_name').val().trim();
            const lastName  = $('#last_name').val().trim();

            const data = {
                prefix:              $('#prefix').val(),
                first_name:          firstName,
                last_name:           lastName,
                national_id:         $('#national_id').val().trim(),
                birth_date:          birth_date,
                username:            $('#username').val().trim(),
                email:               $('#email').val().trim(),
                password:            $('#reg_password').val(),
                phone:               $('#phone').val().trim(),
                member_type:         $('#member_type').val(),
                position:            positionVal,
                academic_rank:       $('#reg_academic_rank').val() || '',
                school_organization: (function() {
                    const p = $('#school_prefix').val();
                    const n = $('#school_organization').val().trim();
                    return p ? p + n : n;
                })(),
                work_phone:          $('#work_phone').val().trim(),
                education_area:      $('#education_area').val(),
                region:              $('#region').val(),
                home_address:        homeAddr,
                work_address:        workAddr
            };

            API.register(data)
                .then(function (result) {
                    if (result.success) {
                        // Auto-login: save auth data
                        if (result.data.token) {
                            API.saveAuth(result.data);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'สมัครสมาชิกสำเร็จ!',
                            html: 'ระบบกำลังนำท่านไปชำระค่าธรรมเนียม...',
                            timer: 2000,
                            showConfirmButton: false,
                            allowOutsideClick: false
                        });
                        setTimeout(function () {
                            window.location.href = '../member/?page=fees&from=register';
                        }, 1800);
                    } else {
                        App.error(result.message || 'ไม่สามารถสมัครสมาชิกได้');
                        btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> สมัครสมาชิก');
                    }
                })
                .catch(function (err) {
                    console.error('Register error:', err);
                    App.error('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + err.message);
                    btn.prop('disabled', false).html('<i class="fas fa-user-plus mr-1"></i> สมัครสมาชิก');
                });

            return false;
        }
    });
});
</script>

<?php include ROOT_PATH . 'templates/auth/footer.php'; ?>
