<?php $pageTitle = 'ตั้งค่าเว็บไซต์'; $page = 'settings'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-gear me-2"></i>ตั้งค่าเว็บไซต์</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">ตั้งค่า</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <form id="settingsForm">
                <div class="row">
                    <!-- ข้อมูลเว็บไซต์ -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-globe me-2"></i>ข้อมูลเว็บไซต์</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อเว็บไซต์</label>
                                    <input type="text" class="form-control" name="site_name">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อย่อ</label>
                                        <input type="text" class="form-control" name="site_name_short">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อภาษาอังกฤษ</label>
                                        <input type="text" class="form-control" name="site_name_en">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">คำอธิบายเว็บไซต์</label>
                                    <textarea class="form-control" name="site_description" rows="3"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-telephone me-2"></i>ข้อมูลติดต่อ</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">อีเมลติดต่อ</label>
                                    <input type="email" class="form-control" name="contact_email">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">เบอร์โทรศัพท์</label>
                                    <input type="text" class="form-control" name="contact_phone">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ที่อยู่</label>
                                    <textarea class="form-control" name="contact_address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-share me-2"></i>Social Links</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-facebook text-primary me-1"></i>Facebook</label>
                                    <input type="url" class="form-control" name="social_facebook" placeholder="https://www.facebook.com/...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-line text-success me-1"></i>LINE</label>
                                    <input type="url" class="form-control" name="social_line" placeholder="https://line.me/... หรือ LINE ID URL">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-youtube text-danger me-1"></i>YouTube</label>
                                    <input type="url" class="form-control" name="social_youtube" placeholder="https://www.youtube.com/...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-tiktok me-1"></i>TikTok</label>
                                    <input type="url" class="form-control" name="social_tiktok" placeholder="https://www.tiktok.com/@...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-instagram text-danger me-1"></i>Instagram</label>
                                    <input type="url" class="form-control" name="social_instagram" placeholder="https://www.instagram.com/...">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label"><i class="bi bi-globe me-1"></i>เว็บไซต์ภายนอก</label>
                                    <input type="url" class="form-control" name="social_website" placeholder="https://...">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-receipt me-2"></i>ระบบใบเสร็จ</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">คำนำหน้าเล่มที่ (Prefix)</label>
                                        <input type="text" class="form-control" name="receipt_book_number" id="receiptBookPrefix" placeholder="เช่น ส.ร.ม.ก.">
                                        <small class="text-muted">ระบบจะเติมปี พ.ศ. (2 หลักท้าย) ต่อท้ายให้อัตโนมัติ</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ตัวอย่างเลขใบเสร็จ</label>
                                        <div id="receiptPreview" class="d-flex flex-wrap align-items-center" style="gap:.5rem; min-height:38px;">
                                            <span class="badge badge-info px-3 py-2" style="font-size:.95em;white-space:normal;word-break:break-word;" id="rcpPreview1"></span>
                                            <span class="badge badge-info px-3 py-2" style="font-size:.95em;white-space:normal;word-break:break-word;" id="rcpPreview2"></span>
                                        </div>
                                        <small class="text-muted">เลขที่ใบเสร็จเริ่มนับใหม่ทุกปี ตามปี พ.ศ. ของวันที่รับเงิน</small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ชื่อองค์กร (ในใบเสร็จ)</label>
                                    <input type="text" class="form-control" name="receipt_organization_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ที่อยู่องค์กร (ในใบเสร็จ)</label>
                                    <textarea class="form-control" name="receipt_organization_address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- ลายเซ็นผู้รับเงิน -->
                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-pen me-2"></i>ลายเซ็นผู้รับเงิน</h3></div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้เซ็นเอกสาร</label>
                                        <input type="text" class="form-control" name="signature_name" placeholder="ชื่อ-นามสกุล">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ตำแหน่ง</label>
                                        <input type="text" class="form-control" name="signature_position" placeholder="เช่น เหรัญญิก">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="signatureShowName" name="signature_show_name" checked>
                                            <label class="custom-control-label" for="signatureShowName">แสดงชื่อในใบเสร็จ</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input" id="signatureShowPosition" name="signature_show_position" checked>
                                            <label class="custom-control-label" for="signatureShowPosition">แสดงตำแหน่งในใบเสร็จ</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">รูปแบบลายเซ็น</label>
                                    <select class="form-control" name="signature_mode" id="signatureMode">
                                        <option value="manual">เซ็นสด (ไม่แสดงลายเซ็นในใบเสร็จ)</option>
                                        <option value="electronic">ลายเซ็นอิเล็กทรอนิกส์ (แสดงในใบเสร็จ)</option>
                                    </select>
                                </div>
                                <div id="signatureElectronicSection">
                                    <label class="form-label">ลายเซ็น</label>
                                    <input type="hidden" name="signature_image" id="signatureImageValue">
                                    <div class="d-flex align-items-start" style="gap:1rem;">
                                        <div id="signaturePreviewBox" class="border rounded text-center d-flex align-items-center justify-content-center" style="width:200px;height:80px;background:repeating-conic-gradient(#f0f0f0 0% 25%, #fff 0% 50%) 50% / 16px 16px;">
                                            <img id="signaturePreviewImg" src="" alt="" style="max-width:100%;max-height:100%;display:none;">
                                            <span id="signaturePreviewEmpty" class="text-muted small">ยังไม่มีลายเซ็น</span>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="openSignatureModal()">
                                                <i class="bi bi-pen me-1"></i> จัดการลายเซ็น
                                            </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm mb-1" id="btnClearSignature" onclick="clearSignature()" style="display:none;">
                                                <i class="bi bi-trash me-1"></i> ลบ
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ตั้งค่าระบบ -->
                    <div class="col-lg-6">
                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-person-plus me-2"></i>ระบบสมาชิก</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="registrationEnabled" name="registration_enabled">
                                        <label class="custom-control-label" for="registrationEnabled">
                                            <strong>เปิดรับสมัครสมาชิก</strong>
                                        </label>
                                    </div>
                                    <small class="text-muted">เมื่อปิด ผู้ใช้จะไม่สามารถสมัครสมาชิกใหม่ได้</small>
                                </div>
                                <hr>

                                <h6 class="mb-3"><i class="bi bi-hash me-1"></i>กำหนดรหัสสมาชิก</h6>
                                <div class="card card-outline card-dark mb-3">
                                    <div class="card-body py-2">
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">คำนำหน้าเลขสมาชิก (Prefix)</label>
                                            <input type="text" class="form-control form-control-sm" name="member_number_prefix" id="mnPrefix" placeholder="เช่น SDAK- หรือ สมช.">
                                            <small class="text-muted">หากว่างไว้ จะแสดงเฉพาะตัวเลข เช่น 0001</small>
                                        </div>
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">รูปแบบตัวเลข</label>
                                            <select class="form-control form-control-sm" name="member_number_digits" id="mnDigits">
                                                <option value="3">3 หลัก (001)</option>
                                                <option value="4" selected>4 หลัก (0001)</option>
                                                <option value="5">5 หลัก (00001)</option>
                                                <option value="6">6 หลัก (000001)</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="form-label small text-muted mb-1">ตัวอย่างเลขสมาชิก</label>
                                            <div class="d-flex align-items-center" style="gap:.5rem">
                                                <span id="mnPreview1" class="badge badge-primary px-3 py-2" style="font-size:.95em">0001</span>
                                                <span id="mnPreview2" class="badge badge-primary px-3 py-2" style="font-size:.95em">0042</span>
                                                <span id="mnPreview3" class="badge badge-primary px-3 py-2" style="font-size:.95em">0100</span>
                                            </div>
                                            <small class="text-muted mt-1 d-block">ระบบเก็บเฉพาะตัวเลข นำเข้าเลข 0001 ได้ แสดงผลตาม Prefix อัตโนมัติ</small>
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h6 class="mb-3">ค่าธรรมเนียมสมาชิก</h6>

                                <!-- สมาชิกสามัญ -->
                                <div class="card card-outline card-primary mb-3">
                                    <div class="card-header py-2"><strong>สมาชิกสามัญ</strong></div>
                                    <div class="card-body py-2">
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">รูปแบบการเก็บ</label>
                                            <select class="form-control form-control-sm fee-mode-select" name="membership_fee_mode_ordinary" data-target="fee_ordinary_amount">
                                                <option value="none">ไม่เก็บค่าใช้จ่าย</option>
                                                <option value="onetime">จ่ายครั้งเดียว</option>
                                                <option value="annual">จ่ายรายปี</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="fee_ordinary_amount" style="display:none">
                                            <label class="form-label small text-muted mb-1">จำนวนเงิน (บาท)</label>
                                            <input type="number" class="form-control form-control-sm" name="membership_fee_ordinary" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- สมาชิกวิสามัญ -->
                                <div class="card card-outline card-info mb-3">
                                    <div class="card-header py-2"><strong>สมาชิกวิสามัญ</strong></div>
                                    <div class="card-body py-2">
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">รูปแบบการเก็บ</label>
                                            <select class="form-control form-control-sm fee-mode-select" name="membership_fee_mode_associate" data-target="fee_associate_amount">
                                                <option value="none">ไม่เก็บค่าใช้จ่าย</option>
                                                <option value="onetime">จ่ายครั้งเดียว</option>
                                                <option value="annual">จ่ายรายปี</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="fee_associate_amount" style="display:none">
                                            <label class="form-label small text-muted mb-1">จำนวนเงิน (บาท)</label>
                                            <input type="number" class="form-control form-control-sm" name="membership_fee_associate" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- สมาชิกสมทบ -->
                                <div class="card card-outline card-warning mb-3">
                                    <div class="card-header py-2"><strong>สมาชิกสมทบ</strong></div>
                                    <div class="card-body py-2">
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">รูปแบบการเก็บ</label>
                                            <select class="form-control form-control-sm fee-mode-select" name="membership_fee_mode_affiliate" data-target="fee_affiliate_amount">
                                                <option value="none">ไม่เก็บค่าใช้จ่าย</option>
                                                <option value="onetime">จ่ายครั้งเดียว</option>
                                                <option value="annual">จ่ายรายปี</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="fee_affiliate_amount" style="display:none">
                                            <label class="form-label small text-muted mb-1">จำนวนเงิน (บาท)</label>
                                            <input type="number" class="form-control form-control-sm" name="membership_fee_affiliate" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>

                                <!-- สมาชิกกิตติมศักดิ์ -->
                                <div class="card card-outline card-secondary mb-3">
                                    <div class="card-header py-2"><strong>สมาชิกกิตติมศักดิ์</strong></div>
                                    <div class="card-body py-2">
                                        <div class="form-group mb-2">
                                            <label class="form-label small text-muted mb-1">รูปแบบการเก็บ</label>
                                            <select class="form-control form-control-sm fee-mode-select" name="membership_fee_mode_honorary" data-target="fee_honorary_amount">
                                                <option value="none">ไม่เก็บค่าใช้จ่าย</option>
                                                <option value="onetime">จ่ายครั้งเดียว</option>
                                                <option value="annual">จ่ายรายปี</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0" id="fee_honorary_amount" style="display:none">
                                            <label class="form-label small text-muted mb-1">จำนวนเงิน (บาท)</label>
                                            <input type="number" class="form-control form-control-sm" name="membership_fee_honorary" min="0" step="0.01">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-bank me-2"></i>ข้อมูลบัญชีธนาคาร</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อธนาคาร</label>
                                    <input type="text" class="form-control" name="bank_name" placeholder="เช่น ธนาคารกรุงไทย">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ชื่อบัญชี</label>
                                    <input type="text" class="form-control" name="bank_account_name">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">เลขที่บัญชี</label>
                                    <input type="text" class="form-control" name="bank_account_number">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-box-arrow-in-right me-2"></i>หน้าเข้าสู่ระบบ / สมัครสมาชิก</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">หัวข้อ (Login Title)</label>
                                    <input type="text" class="form-control" name="login_title" placeholder="เช่น เข้าสู่ระบบ">
                                    <small class="text-muted">แสดงเป็นชื่อหลักในหน้า Login/Register หากว่างจะใช้ค่าเริ่มต้น</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">คำอธิบาย (Subtitle)</label>
                                    <input type="text" class="form-control" name="login_subtitle" placeholder="เช่น สมาคมรองผู้อำนวยการฯ กาฬสินธุ์">
                                    <small class="text-muted">แสดงใต้หัวข้อหลัก หากว่างจะใช้ชื่อเว็บไซต์แทน</small>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header"><h3 class="card-title"><i class="bi bi-google me-2"></i>Google Login</h3></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Google Client ID</label>
                                    <input type="text" class="form-control" name="google_client_id">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Google Client Secret</label>
                                    <input type="password" class="form-control" name="google_client_secret">
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0"><i class="bi bi-envelope-at me-2"></i>ตั้งค่า SMTP (ส่งอีเมล)</h3>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="fillGmailSmtp()" title="ใส่ค่า Gmail อัตโนมัติ">
                                    <i class="bi bi-google me-1"></i>ใช้ Gmail
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    ใช้สำหรับส่งอีเมลรีเซ็ตรหัสผ่าน และแจ้งเตือนต่างๆ ผ่านอีเมล
                                </div>
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label">SMTP Host</label>
                                        <input type="text" class="form-control" name="smtp_host" placeholder="เช่น smtp.gmail.com">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" name="smtp_port" placeholder="587">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Username</label>
                                    <input type="text" class="form-control" name="smtp_username" placeholder="เช่น your-email@gmail.com">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password / App Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" name="smtp_password" id="smtpPassword" placeholder="รหัสผ่านหรือ App Password">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleSmtpPassword()">
                                            <i class="bi bi-eye" id="smtpPwdIcon"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">สำหรับ Gmail ให้ใช้ <a href="https://myaccount.google.com/apppasswords" target="_blank">App Password</a></small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">ชื่อผู้ส่ง (From Name)</label>
                                        <input type="text" class="form-control" name="smtp_from_name" placeholder="เช่น ส.ร.ม.ก.">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">อีเมลผู้ส่ง (From Email)</label>
                                        <input type="email" class="form-control" name="smtp_from_email" placeholder="เช่น noreply@example.com">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">การเข้ารหัส (Encryption)</label>
                                    <select class="form-control" name="smtp_encryption">
                                        <option value="tls">TLS (แนะนำ)</option>
                                        <option value="ssl">SSL</option>
                                        <option value="">ไม่เข้ารหัส</option>
                                    </select>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="testSmtpEmail()">
                                        <i class="bi bi-send me-1"></i>ส่งอีเมลทดสอบ
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-info">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title mb-0"><i class="bi bi-telegram me-2"></i>แจ้งเตือน Telegram</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info py-2 mb-3">
                                    <i class="bi bi-info-circle me-1"></i>
                                    ใช้สำหรับรับแจ้งเตือนเมื่อมีสมาชิกสมัครใหม่ ผ่าน Telegram Bot
                                    <br><small class="text-muted">
                                        วิธีสร้าง Bot: ค้นหา <strong>@BotFather</strong> ใน Telegram → <code>/newbot</code> → จะได้รับ Bot Token<br>
                                        วิธีหา Chat ID: ค้นหา <strong>@userinfobot</strong> ใน Telegram แล้วพิมพ์ <code>/start</code>
                                    </small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Bot Token</label>
                                    <input type="text" class="form-control" name="telegram_bot_token" placeholder="เช่น 123456789:ABCdefGHIjklMNOpqrsTUVwxyz">
                                    <small class="text-muted">ได้จาก @BotFather เมื่อสร้าง Bot</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Chat ID</label>
                                    <input type="text" class="form-control" name="telegram_chat_id" placeholder="เช่น 123456789 หรือ -100123456789">
                                    <small class="text-muted">Chat ID ของคุณ หรือ Group ID ที่ต้องการรับแจ้งเตือน</small>
                                </div>
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="tgNotifyNewMember" name="telegram_notify_new_member" value="1">
                                        <label class="custom-control-label" for="tgNotifyNewMember">
                                            <i class="bi bi-bell me-1"></i> แจ้งเตือนเมื่อมีสมาชิกสมัครใหม่
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="tgNotifyFeeSlip" name="telegram_notify_fee_slip" value="1">
                                        <label class="custom-control-label" for="tgNotifyFeeSlip">
                                            <i class="bi bi-bell me-1"></i> แจ้งเตือนเมื่อสมาชิกอัปโหลดสลิปค่าธรรมเนียม
                                        </label>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="tgNotifyActivityReg" name="telegram_notify_activity_reg" value="1">
                                        <label class="custom-control-label" for="tgNotifyActivityReg">
                                            <i class="bi bi-bell me-1"></i> แจ้งเตือนเมื่อสมาชิกลงทะเบียนกิจกรรม
                                        </label>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="testTelegram()">
                                        <i class="bi bi-send me-1"></i>ส่งข้อความทดสอบ
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-danger">
                            <div class="card-header bg-danger text-white"><h3 class="card-title mb-0"><i class="bi bi-shield-lock me-2"></i>รหัสยืนยันลบข้อมูล (Reset)</h3></div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <label class="form-label">รหัสยืนยัน</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="reset_confirm_code" id="resetConfirmCode" placeholder="เช่น SDAK2026">
                                        <button type="button" class="btn btn-outline-danger" onclick="randomizeResetCode()" title="สุ่มรหัสใหม่">
                                            <i class="bi bi-shuffle me-1"></i>สุ่ม
                                        </button>
                                    </div>
                                    <small class="text-muted">รหัสนี้ใช้สำหรับยืนยันการลบข้อมูลสมาชิกทั้งหมดในหน้า Reset Members หากไม่กำหนดจะใช้ชื่อย่อเว็บไซต์แทน</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col text-center">
                        <button type="submit" class="btn btn-primary btn-lg px-5" id="btnSaveSettings">
                            <i class="bi bi-check-lg me-2"></i>บันทึกการตั้งค่า
                        </button>
                    </div>
                </div>
                </form>
            </div>
        </section>
    </div>

<!-- Signature Modal -->
<div class="modal fade" id="signatureModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pen me-1"></i> จัดการลายเซ็น</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabDraw" role="tab"><i class="bi bi-pencil me-1"></i>เซ็นสด</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabUpload" role="tab"><i class="bi bi-upload me-1"></i>อัปโหลด</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabUrl" role="tab"><i class="bi bi-link-45deg me-1"></i>URL</a></li>
                </ul>
                <div class="tab-content">
                    <!-- Tab: Draw -->
                    <div class="tab-pane fade show active" id="tabDraw" role="tabpanel">
                        <p class="text-muted small mb-2">ใช้เมาส์หรือนิ้วเซ็นลายเซ็นด้านล่าง</p>
                        <div class="border rounded mb-2" style="background:repeating-conic-gradient(#f0f0f0 0% 25%, #fff 0% 50%) 50% / 16px 16px;">
                            <canvas id="sigCanvas" width="600" height="200" style="width:100%;cursor:crosshair;touch-action:none;"></canvas>
                        </div>
                        <div class="d-flex" style="gap:.5rem;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSigCanvas()"><i class="bi bi-eraser me-1"></i>ล้าง</button>
                            <select class="form-control form-control-sm" id="sigPenSize" style="width:120px;" onchange="updatePenSize()">
                                <option value="2">เส้นบาง</option>
                                <option value="3" selected>ปกติ</option>
                                <option value="5">เส้นหนา</option>
                            </select>
                            <select class="form-control form-control-sm" id="sigPenColor" style="width:120px;" onchange="updatePenColor()">
                                <option value="#1a3c5e" selected>น้ำเงินเข้ม</option>
                                <option value="#000000">ดำ</option>
                                <option value="#1565c0">น้ำเงิน</option>
                            </select>
                        </div>
                        <div class="text-right mt-3">
                            <button type="button" class="btn btn-primary" onclick="useDrawnSignature()"><i class="bi bi-check-lg me-1"></i>ใช้ลายเซ็นนี้</button>
                        </div>
                    </div>
                    <!-- Tab: Upload -->
                    <div class="tab-pane fade" id="tabUpload" role="tabpanel">
                        <p class="text-muted small mb-2">อัปโหลดไฟล์รูปลายเซ็น (แนะนำ PNG พื้นหลังโปร่งใส)</p>
                        <input type="file" class="form-control mb-3" id="sigFileInput" accept="image/*">
                        <div id="sigCropperArea" style="display:none;">
                            <div class="border rounded mb-2" style="max-height:350px;overflow:hidden;background:repeating-conic-gradient(#f0f0f0 0% 25%, #fff 0% 50%) 50% / 16px 16px;">
                                <img id="sigCropperImage" src="" style="max-width:100%;display:block;">
                            </div>
                            <div class="text-right mt-2">
                                <button type="button" class="btn btn-primary" onclick="useCroppedSignature()"><i class="bi bi-check-lg me-1"></i>ใช้ลายเซ็นนี้</button>
                            </div>
                        </div>
                    </div>
                    <!-- Tab: URL -->
                    <div class="tab-pane fade" id="tabUrl" role="tabpanel">
                        <p class="text-muted small mb-2">วาง URL ของรูปลายเซ็น (แนะนำ PNG พื้นหลังโปร่งใส)</p>
                        <div class="input-group mb-3">
                            <input type="url" class="form-control" id="sigUrlInput" placeholder="https://example.com/signature.png">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" onclick="useUrlSignature()"><i class="bi bi-check-lg me-1"></i>ใช้</button>
                            </div>
                        </div>
                        <div id="sigUrlPreview" style="display:none;" class="text-center">
                            <div class="border rounded p-2 d-inline-block" style="background:repeating-conic-gradient(#f0f0f0 0% 25%, #fff 0% 50%) 50% / 16px 16px;">
                                <img id="sigUrlPreviewImg" src="" alt="" style="max-height:100px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<style>
@keyframes pulse-save {
    0%, 100% { transform: scale(1); box-shadow: none; }
    50% { transform: scale(1.08); box-shadow: 0 0 20px rgba(255,193,7,.6); }
}
</style>

<script>
$(function () {
    App.requireAdmin();
    loadSettings();
});

async function loadSettings() {
    const result = await API.getSettings();
    if (!result.success) return;
    const s = result.data;

    const form = $('#settingsForm');
    Object.keys(s).forEach(key => {
        const el = form.find(`[name="${key}"]`);
        if (el.length) {
            if (el.is(':checkbox')) {
                el.prop('checked', s[key] === '1' || s[key] === 1);
            } else {
                el.val(s[key]);
            }
        }
    });

    // Toggle fee amount visibility based on mode
    $('.fee-mode-select').each(function () {
        toggleFeeAmount($(this));
    });

    // Update member number preview
    updateMnPreview();

    // Update receipt preview
    updateReceiptPreview();

    // Signature
    toggleSignatureSection();
    updateSignaturePreview();

    // Random default for reset_confirm_code if empty
    if (!$('[name="reset_confirm_code"]').val()) {
        randomizeResetCode();
    }
}

function toggleFeeAmount($select) {
    const target = $select.data('target');
    if ($select.val() === 'none') {
        $('#' + target).slideUp(150);
    } else {
        $('#' + target).slideDown(150);
    }
}

$(document).on('change', '.fee-mode-select', function () {
    toggleFeeAmount($(this));
});

// Member number preview
function updateMnPreview() {
    const prefix = $('#mnPrefix').val() || '';
    const digits = parseInt($('#mnDigits').val()) || 4;
    const pad = (n) => String(n).padStart(digits, '0');
    $('#mnPreview1').text(prefix + pad(1));
    $('#mnPreview2').text(prefix + pad(42));
    $('#mnPreview3').text(prefix + pad(100));
}
$('#mnPrefix, #mnDigits').on('input change', updateMnPreview);

function randomizeResetCode() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    let code = '';
    for (let i = 0; i < 6; i++) code += chars.charAt(Math.floor(Math.random() * chars.length));
    $('#resetConfirmCode').val(code).css('color','#dc3545');
    setTimeout(() => $('#resetConfirmCode').css('color',''), 500);
}

// Receipt number preview
function updateReceiptPreview() {
    const prefix = ($('#receiptBookPrefix').val() || '').trim();
    const buddhistYear2 = String((new Date()).getFullYear() + 543).slice(-2);
    const bookNum = prefix ? prefix + ' ' + buddhistYear2 : buddhistYear2;
    $('#rcpPreview1').text('เล่มที่ ' + bookNum + ' / เลขที่ 1');
    $('#rcpPreview2').text('เล่มที่ ' + bookNum + ' / เลขที่ 2');
}
$('#receiptBookPrefix').on('input', updateReceiptPreview);

// ─── Signature Mode Toggle ───
$('#signatureMode').on('change', function () {
    toggleSignatureSection();
});

function toggleSignatureSection() {
    if ($('#signatureMode').val() === 'electronic') {
        $('#signatureElectronicSection').slideDown(150);
    } else {
        $('#signatureElectronicSection').slideUp(150);
    }
}

function updateSignaturePreview() {
    const val = $('#signatureImageValue').val();
    if (val) {
        const src = val.startsWith('data:') || val.startsWith('http') ? val : (BASE_PATH + val);
        $('#signaturePreviewImg').attr('src', src).show();
        $('#signaturePreviewEmpty').hide();
        $('#btnClearSignature').show();
    } else {
        $('#signaturePreviewImg').hide().attr('src', '');
        $('#signaturePreviewEmpty').show();
        $('#btnClearSignature').hide();
    }
}

function clearSignature() {
    $('#signatureImageValue').val('');
    updateSignaturePreview();
}

// ─── Signature Drawing Canvas ───
let sigCtx, sigDrawing = false, sigPenColor = '#1a3c5e', sigPenSize = 3;
let sigCanvasInitialized = false;

function initSigCanvas() {
    const c = document.getElementById('sigCanvas');
    if (!c) return;
    sigCtx = c.getContext('2d');
    sigCtx.lineCap = 'round';
    sigCtx.lineJoin = 'round';
    sigCtx.strokeStyle = sigPenColor;
    sigCtx.lineWidth = sigPenSize;

    if (sigCanvasInitialized) return;
    sigCanvasInitialized = true;

    function getPos(e) {
        const rect = c.getBoundingClientRect();
        const scaleX = c.width / rect.width;
        const scaleY = c.height / rect.height;
        const touch = e.touches ? e.touches[0] : e;
        return { x: (touch.clientX - rect.left) * scaleX, y: (touch.clientY - rect.top) * scaleY };
    }
    function startDraw(e) { e.preventDefault(); sigDrawing = true; const p = getPos(e); sigCtx.beginPath(); sigCtx.moveTo(p.x, p.y); }
    function draw(e) { if (!sigDrawing) return; e.preventDefault(); const p = getPos(e); sigCtx.lineTo(p.x, p.y); sigCtx.stroke(); }
    function stopDraw() { sigDrawing = false; }

    c.addEventListener('mousedown', startDraw);
    c.addEventListener('mousemove', draw);
    c.addEventListener('mouseup', stopDraw);
    c.addEventListener('mouseleave', stopDraw);
    c.addEventListener('touchstart', startDraw);
    c.addEventListener('touchmove', draw);
    c.addEventListener('touchend', stopDraw);
}

function clearSigCanvas() {
    if (!sigCtx) return;
    const c = document.getElementById('sigCanvas');
    sigCtx.clearRect(0, 0, c.width, c.height);
}

function updatePenSize() { sigPenSize = parseInt($('#sigPenSize').val()) || 3; if (sigCtx) sigCtx.lineWidth = sigPenSize; }
function updatePenColor() { sigPenColor = $('#sigPenColor').val() || '#1a3c5e'; if (sigCtx) sigCtx.strokeStyle = sigPenColor; }

function useDrawnSignature() {
    const c = document.getElementById('sigCanvas');
    // Check if canvas is blank
    const blank = document.createElement('canvas');
    blank.width = c.width; blank.height = c.height;
    if (c.toDataURL() === blank.toDataURL()) {
        App.error('กรุณาเซ็นลายเซ็นก่อน');
        return;
    }
    // Export as transparent PNG
    const dataUrl = c.toDataURL('image/png');
    $('#signatureImageValue').val(dataUrl);
    updateSignaturePreview();
    $('#signatureModal').modal('hide');
    App.success('ใช้ลายเซ็นเรียบร้อย');
    highlightSaveButton();
}

// ─── Signature Cropper ───
let sigCropper = null;

$('#sigFileInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('sigCropperImage');
        img.src = e.target.result;
        if (sigCropper) { sigCropper.destroy(); sigCropper = null; }
        $('#sigCropperArea').show();
        setTimeout(() => {
            sigCropper = new Cropper(img, {
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.9,
                background: false,
                responsive: true,
            });
        }, 200);
    };
    reader.readAsDataURL(file);
});

function useCroppedSignature() {
    if (!sigCropper) { App.error('กรุณาเลือกไฟล์ก่อน'); return; }
    // getCroppedCanvas without fillColor keeps original alpha/transparency
    const canvas = sigCropper.getCroppedCanvas();
    const dataUrl = canvas.toDataURL('image/png');
    $('#signatureImageValue').val(dataUrl);
    updateSignaturePreview();
    $('#signatureModal').modal('hide');
    App.success('ใช้ลายเซ็นเรียบร้อย');
    highlightSaveButton();
}

function useUrlSignature() {
    const url = $('#sigUrlInput').val().trim();
    if (!url) { App.error('กรุณากรอก URL'); return; }
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        App.error('กรุณากรอก URL ที่ขึ้นต้นด้วย http:// หรือ https://');
        return;
    }
    // Save URL directly (no CORS validation — external images may block crossOrigin)
    $('#signatureImageValue').val(url);
    updateSignaturePreview();
    $('#signatureModal').modal('hide');
    App.success('ใช้ลายเซ็นเรียบร้อย');
    highlightSaveButton();
}

$('#sigUrlInput').on('input', function () {
    const url = $(this).val().trim();
    if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
        $('#sigUrlPreviewImg').attr('src', url);
        $('#sigUrlPreview').show();
    } else {
        $('#sigUrlPreview').hide();
    }
});

function openSignatureModal() {
    clearSigCanvas();
    $('#sigFileInput').val('');
    $('#sigCropperArea').hide();
    if (sigCropper) { sigCropper.destroy(); sigCropper = null; }
    $('#sigUrlInput').val('');
    $('#sigUrlPreview').hide();
    $('#signatureModal').modal('show');
}

$('#signatureModal').on('shown.bs.modal', function () {
    initSigCanvas();
});
$('#signatureModal').on('hidden.bs.modal', function () {
    if (sigCropper) { sigCropper.destroy(); sigCropper = null; }
});

// ─── Highlight Save Button ───
function highlightSaveButton() {
    const btn = $('#btnSaveSettings');
    btn.removeClass('btn-primary').addClass('btn-warning');
    btn.html('<i class="bi bi-exclamation-triangle me-2"></i>กดบันทึกเพื่อเซฟลายเซ็น');
    $('html, body').animate({ scrollTop: btn.offset().top - 200 }, 400);
    btn.css('animation', 'pulse-save .8s ease-in-out 3');
    setTimeout(() => {
        btn.css('animation', '');
        btn.removeClass('btn-warning').addClass('btn-primary');
        btn.html('<i class="bi bi-check-lg me-2"></i>บันทึกการตั้งค่า');
    }, 5000);
}

// ─── SMTP Toggle & Test ───
function fillGmailSmtp() {
    $('[name="smtp_host"]').val('smtp.gmail.com');
    $('[name="smtp_port"]').val('587');
    $('[name="smtp_encryption"]').val('tls');
    // If username is empty, hint with placeholder
    if (!$('[name="smtp_username"]').val()) {
        $('[name="smtp_username"]').attr('placeholder', 'your-email@gmail.com').focus();
    }
    App.success('ใส่ค่า Gmail SMTP เรียบร้อย — กรุณากรอก Username (อีเมล Gmail) และ App Password');
}

function toggleSmtpPassword() {
    const inp = document.getElementById('smtpPassword');
    const icon = document.getElementById('smtpPwdIcon');
    if (inp.type === 'password') {
        inp.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        inp.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

async function testSmtpEmail() {
    const host = $('[name="smtp_host"]').val();
    const port = $('[name="smtp_port"]').val();
    const user = $('[name="smtp_username"]').val();
    const pass = $('[name="smtp_password"]').val();
    if (!host || !user || !pass) {
        App.error('กรุณากรอก SMTP Host, Username, Password ก่อน');
        return;
    }

    // Save settings first, then send test
    const { value: testEmail } = await Swal.fire({
        title: 'ส่งอีเมลทดสอบ',
        input: 'email',
        inputLabel: 'ส่งไปยังอีเมล:',
        inputValue: user,
        inputPlaceholder: 'test@example.com',
        showCancelButton: true,
        confirmButtonText: 'ส่งทดสอบ',
        cancelButtonText: 'ยกเลิก',
        inputValidator: (v) => !v ? 'กรุณากรอกอีเมล' : null,
    });
    if (!testEmail) return;

    Swal.fire({ title: 'กำลังส่ง...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const result = await API.post(API.apiUrl('auth', 'test-smtp'), { email: testEmail });
        Swal.close();
        if (result.success) {
            App.success('ส่งอีเมลทดสอบสำเร็จ! กรุณาตรวจสอบกล่องจดหมาย');
        } else {
            App.error(result.message || 'ไม่สามารถส่งอีเมลได้');
        }
    } catch (err) {
        Swal.close();
        const msg = (err.responseJSON && err.responseJSON.message) || 'เกิดข้อผิดพลาดในการส่งอีเมล';
        App.error(msg);
    }
}

async function testTelegram() {
    const token = $('[name="telegram_bot_token"]').val();
    const chatId = $('[name="telegram_chat_id"]').val();
    if (!token || !chatId) {
        App.error('กรุณากรอก Bot Token และ Chat ID แล้วบันทึกก่อน');
        return;
    }

    const confirmed = await Swal.fire({
        title: 'ทดสอบ Telegram',
        text: 'ส่งข้อความทดสอบไปยัง Telegram?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-send me-1"></i> ส่งทดสอบ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#17a2b8',
    });
    if (!confirmed.isConfirmed) return;

    Swal.fire({ title: 'กำลังส่ง...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const result = await API.post(API.apiUrl('settings', 'test-telegram'), {});
        Swal.close();
        if (result.success) {
            App.success(result.message || 'ส่งข้อความทดสอบสำเร็จ! กรุณาตรวจสอบที่ Telegram');
        } else {
            App.error(result.message || 'ไม่สามารถส่งข้อความได้');
        }
    } catch (err) {
        Swal.close();
        const msg = (err.responseJSON && err.responseJSON.message) || 'เกิดข้อผิดพลาดในการส่ง';
        App.error(msg);
    }
}

$('#settingsForm').on('submit', async function(e) {
    e.preventDefault();
    const btn = $('#btnSaveSettings');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังบันทึก...');
    btn.removeClass('btn-warning').addClass('btn-primary');

    const settings = {};
    $(this).find('input, textarea, select').each(function () {
        const name = $(this).attr('name');
        if (!name) return;
        if ($(this).is(':checkbox')) {
            settings[name] = $(this).is(':checked') ? '1' : '0';
        } else {
            settings[name] = $(this).val();
        }
    });

    // Debug: log signature info
    if (settings.signature_image) {
        console.log('[Settings] Saving signature_image: type=' + (settings.signature_image.startsWith('data:') ? 'base64' : 'url') + ', length=' + settings.signature_image.length);
    } else {
        console.log('[Settings] signature_image is empty');
    }

    const result = await API.updateSettings(settings);
    if (result.success) {
        App.success(result.message);
    } else {
        App.error(result.message);
    }
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-2"></i>บันทึกการตั้งค่า');
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
