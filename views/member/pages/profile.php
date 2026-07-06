<?php $pageTitle = 'โปรไฟล์'; ?>
<?php $extraCss = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">
<link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">โปรไฟล์</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">โปรไฟล์</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container">
            <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img id="profileAvatar" src="<?php echo ($basePath ?? './'); ?>assets/images/default-avatar.png"
                            data-default-avatar="<?php echo ($basePath ?? './'); ?>assets/images/default-avatar.png"
                            class="rounded-circle border" width="120" height="120"
                            style="object-fit:cover; cursor:pointer" alt="avatar"
                            onerror="this.onerror=null;this.src='<?php echo ($basePath ?? './'); ?>assets/images/default-avatar.png'"
                            onclick="$('#avatarOptionsModal').modal('show')" title="คลิกเพื่อเปลี่ยนรูป">
                        <span class="btn btn-sm btn-primary position-absolute rounded-circle"
                            style="width:32px;height:32px;padding:4px;bottom:0;right:0;cursor:pointer"
                            onclick="$('#avatarOptionsModal').modal('show')">
                            <i class="bi bi-camera"></i>
                        </span>
                    </div>
                    <h5 id="profileName" class="mb-1">-</h5>
                    <small id="profileMemberNumber" class="text-primary d-none"></small>
                    <div>
                    <span id="profileTypeBadge" class="badge bg-secondary">-</span>
                    <span id="profileStatusBadge" class="badge ms-1">-</span>
                    </div>
                    <div class="mt-3 text-muted small">
                        <p class="mb-1" id="profilePositionLine" style="display:none"><i class="bi bi-briefcase me-1"></i> <span id="profilePosition">-</span></p>
                        <p class="mb-1" id="profileAcademicRankLine" style="display:none"><i class="bi bi-award me-1"></i> <span id="profileAcademicRank">-</span></p>
                        <p class="mb-1"><i class="bi bi-envelope me-1"></i> <span id="profileEmail">-</span></p>
                        <p class="mb-1"><i class="bi bi-telephone me-1"></i> <span id="profilePhone">-</span></p>
                        <p class="mb-0"><i class="bi bi-calendar me-1"></i> สมาชิกตั้งแต่: <span id="profileSince">-</span></p>
                    </div>
                </div>
            </div>
            <!-- Telegram Connect Card -->
            <div class="card shadow-sm mt-3" id="telegramConnectCard">
                <div class="card-body text-center">
                    <!-- Bot Info Section -->
                    <div id="telegramBotInfo" class="mb-3" style="display:none;">
                        <div class="d-flex align-items-center justify-content-center mb-2">
                            <img id="telegramBotPhoto" src="" alt="Bot" width="48" height="48" class="rounded-circle mr-2" style="object-fit:cover; display:none;">
                            <div class="text-left">
                                <div class="font-weight-bold" id="telegramBotName" style="font-size:1rem;"></div>
                                <div class="text-muted small" id="telegramBotUsername"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Fallback header (no bot info) -->
                    <div id="telegramFallbackHeader" class="d-flex align-items-center justify-content-center mb-2">
                        <img src="https://telegram.org/img/t_logo.svg" alt="Telegram" width="32" height="32" class="mr-2">
                        <span class="h6 mb-0">เชื่อมต่อ Telegram</span>
                    </div>
                    <div id="telegramStatusSection">
                        <span class="badge bg-secondary" id="telegramStatusBadge">ยังไม่ได้เชื่อมต่อ</span>
                    </div>
                    <button type="button" class="btn btn-outline-primary mt-2" id="btnConnectTelegram">
                        <i class="bi bi-telegram me-1"></i> เชื่อมต่อบัญชี Telegram
                    </button>
                    <div class="mt-2 small text-muted" id="telegramConnectHint">
                        กดปุ่มเพื่อเชื่อมบัญชี Telegram ของคุณกับระบบ<br>เพื่อรับการแจ้งเตือนและใช้งานฟีเจอร์เพิ่มเติม
                    </div>
                </div>
            </div>
        </div>

<!-- Avatar Options Modal -->
<div class="modal fade" id="avatarOptionsModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title"><i class="bi bi-person-circle me-1"></i> เปลี่ยนรูปโปรไฟล์</h6>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <label for="avatarFileInput" class="btn btn-primary btn-block mb-2">
                    <i class="bi bi-upload me-1"></i> อัปโหลดรูปภาพ
                </label>
                <input type="file" id="avatarFileInput" accept="image/*" class="d-none">
                <button class="btn btn-outline-secondary btn-block" onclick="showAvatarLinkInput()">
                    <i class="bi bi-link-45deg me-1"></i> แนบลิงก์รูปภาพ
                </button>
                <div id="avatarLinkSection" class="mt-3" style="display:none">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="avatarLinkInput" placeholder="https://...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" onclick="saveAvatarLink()">บันทึก</button>
                        </div>
                    </div>
                    <small class="text-muted">วาง URL รูปภาพจากเว็บ</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Cropper Modal -->
<div class="modal fade" id="avatarCropperModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-crop me-2"></i>ครอปรูปโปรไฟล์</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div style="max-height:60vh;overflow:hidden">
                    <img id="avatarCropperImage" src="" style="max-width:100%;display:block">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnAvatarCropConfirm">
                    <i class="bi bi-check-lg me-1"></i> ครอปและบันทึก
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Main Content -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tabInfo">
                                <i class="bi bi-person me-1"></i> ข้อมูลส่วนตัว
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabPassword">
                                <i class="bi bi-shield-lock me-1"></i> เปลี่ยนรหัสผ่าน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tabActivities">
                                <i class="bi bi-calendar-event me-1"></i> กิจกรรมของฉัน
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>member/?page=fees">
                                <i class="bi bi-cash-coin me-1"></i> ค่าธรรมเนียม <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo $basePath; ?>member/?page=receipts">
                                <i class="bi bi-receipt me-1"></i> ใบเสร็จ <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Tab: Profile Info -->
                        <div class="tab-pane fade show active" id="tabInfo">
                            <form id="profileForm" novalidate>
                                <!-- ข้อมูลทั่วไป -->
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="w-auto px-2 small font-weight-bold text-primary">ข้อมูลทั่วไป</legend>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ประเภทสมาชิก</label>
                                            <select class="form-control" name="member_type" disabled>
                                                <option value="">-- เลือก --</option>
                                                <option value="ordinary">สมาชิกสามัญ</option>
                                                <option value="associate">สมาชิกวิสามัญ</option>
                                                <option value="affiliate">สมาชิกสมทบ</option>
                                                <option value="honorary">สมาชิกกิตติมศักดิ์</option>
                                            </select>
                                            <small class="text-muted">ประเภทสมาชิกเปลี่ยนได้โดย admin</small>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">เลขบัตรประชาชน</label>
                                            <input type="text" class="form-control" name="national_id" maxlength="13">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">มือถือ <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" name="phone" placeholder="0812345678">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 mb-3">
                                            <label class="form-label">คำนำหน้า</label>
                                            <select class="form-control" name="prefix">
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
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="first_name" required>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">นามสกุล</label>
                                            <input type="text" class="form-control" name="last_name">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">วันเกิด</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="prof_birth_date" placeholder="เลือกวันเกิด" readonly>
                                                <div class="input-group-append"><div class="input-group-text"><i class="fas fa-calendar-alt"></i></div></div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" name="email" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">ตำแหน่ง</label>
                                            <select class="form-control" id="prof_position" name="position">
                                                <option value="">-- เลือก --</option>
                                                <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                                                <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                                                <option value="other">อื่นๆ (กรอกเอง)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3" id="positionOtherWrap" style="display:none">
                                            <label class="form-label">ระบุตำแหน่ง</label>
                                            <input type="text" class="form-control" id="prof_position_other" placeholder="กรอกตำแหน่งของท่าน">
                                        </div>
                                        <div class="col-md-4 mb-3" id="academicRankWrap" style="display:none">
                                            <label class="form-label">วิทยฐานะ</label>
                                            <select class="form-control" id="prof_academic_rank" name="academic_rank">
                                                <option value="">-- เลือก --</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>

                                <!-- ที่อยู่ปัจจุบัน -->
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="w-auto px-2 small font-weight-bold text-primary">ที่อยู่ปัจจุบัน</legend>
                                    <div class="row">
                                        <div class="col-md-2 mb-2"><label>เลขที่</label><input type="text" class="form-control" id="h_no"></div>
                                        <div class="col-md-2 mb-2"><label>ซอย</label><input type="text" class="form-control" id="h_soi" placeholder="ไม่มีให้กรอก -"></div>
                                        <div class="col-md-2 mb-2"><label>หมู่ที่</label><input type="text" class="form-control" id="h_moo"></div>
                                        <div class="col-md-3 mb-2"><label>ถนน</label><input type="text" class="form-control" id="h_road"></div>
                                        <div class="col-md-3 mb-2">
                                            <label>ค้นหาที่อยู่</label>
                                            <input type="text" class="form-control" id="h_search" placeholder="พิมพ์ ตำบล/อำเภอ/จังหวัด">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-2"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="h_subdistrict"></div>
                                        <div class="col-md-3 mb-2"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="h_district"></div>
                                        <div class="col-md-3 mb-2"><label>จังหวัด</label><input type="text" class="form-control" id="h_province"></div>
                                        <div class="col-md-3 mb-2"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="h_postal" maxlength="5"></div>
                                    </div>
                                </fieldset>

                                <!-- สถานที่ทำงาน -->
                                <fieldset class="border rounded p-3 mb-3">
                                    <legend class="w-auto px-2 small font-weight-bold text-primary">สถานที่ทำงาน</legend>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label>โรงเรียน/หน่วยงาน</label>
                                            <div class="input-group">
                                                <select class="form-control" id="school_prefix" style="max-width:160px">
                                                    <option value="โรงเรียน">โรงเรียน</option>
                                                    <option value="สพม.">สพม.</option>
                                                    <option value="สพป.">สพป.</option>
                                                    <option value="สำนักงาน">สำนักงาน</option>
                                                    <option value="">อื่นๆ</option>
                                                </select>
                                                <input type="text" class="form-control" name="school_organization">
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>โทรศัพท์ (ที่ทำงาน)</label>
                                            <input type="tel" class="form-control" name="work_phone">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2 mb-2"><label>เลขที่</label><input type="text" class="form-control" id="w_no"></div>
                                        <div class="col-md-2 mb-2"><label>ซอย</label><input type="text" class="form-control" id="w_soi" placeholder="ไม่มีให้กรอก -"></div>
                                        <div class="col-md-2 mb-2"><label>หมู่</label><input type="text" class="form-control" id="w_moo"></div>
                                        <div class="col-md-3 mb-2"><label>ถนน</label><input type="text" class="form-control" id="w_road" placeholder="ไม่มีให้กรอก -"></div>
                                        <div class="col-md-3 mb-2">
                                            <label>ค้นหาที่อยู่</label>
                                            <input type="text" class="form-control" id="w_search" placeholder="พิมพ์ ตำบล/อำเภอ/จังหวัด">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-2"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="w_subdistrict"></div>
                                        <div class="col-md-3 mb-2"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="w_district"></div>
                                        <div class="col-md-3 mb-2"><label>จังหวัด</label><input type="text" class="form-control" id="w_province"></div>
                                        <div class="col-md-3 mb-2"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="w_postal" maxlength="5"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-2">
                                            <label>สังกัดเขตพื้นที่</label>
                                            <select class="form-control" name="education_area">
                                                <option value="">-- เลือก --</option>
                                                <option>สพป.</option><option>สพม.</option><option>สพอ.</option>
                                                <option>สช.</option><option>อื่นๆ</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-2">
                                            <label>ภาค</label>
                                            <select class="form-control" name="region">
                                                <option value="">-- เลือก --</option>
                                                <option>ภาคเหนือ</option><option>ภาคกลาง</option>
                                                <option>ภาคตะวันออกเฉียงเหนือ</option><option>ภาคใต้</option>
                                                <option>ภาคตะวันออก</option><option>ภาคตะวันตก</option>
                                            </select>
                                        </div>
                                    </div>
                                </fieldset>

                                <button type="submit" class="btn btn-primary" id="btnSaveProfile">
                                    <i class="bi bi-check-lg me-1"></i> บันทึกข้อมูล
                                </button>
                            </form>
                        </div>

                        <!-- Tab: Change Password -->
                        <div class="tab-pane fade" id="tabPassword">
                            <form id="passwordForm" novalidate>
                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านปัจจุบัน</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">รหัสผ่านใหม่</label>
                                    <input type="password" class="form-control" name="new_password" id="newPassword" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ยืนยันรหัสผ่านใหม่</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-warning" id="btnChangePassword">
                                    <i class="bi bi-shield-lock me-1"></i> เปลี่ยนรหัสผ่าน
                                </button>
                            </form>
                        </div>

                        <!-- Tab: My Activities -->
                        <div class="tab-pane fade" id="tabActivities">
                            <!-- Filter Tabs -->
                            <ul class="nav nav-pills mb-3" id="activityFilter">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#" data-filter="all"><i class="bi bi-grid me-1"></i>ทั้งหมด</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="registered"><i class="bi bi-check-circle me-1"></i>ที่ลงทะเบียน</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" data-filter="available"><i class="bi bi-plus-circle me-1"></i>เปิดรับสมัคร</a>
                                </li>
                            </ul>
                            <div id="myActivitiesList">
                                <div class="text-center text-muted py-4">
                                    <span class="spinner-border spinner-border-sm"></span> กำลังโหลด...
                                </div>
                            </div>
                        </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<!-- Activity Detail Modal -->
<div class="modal fade" id="activityDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-calendar-event me-1"></i> รายละเอียดกิจกรรม</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="activityDetailBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer" id="activityDetailFooter"></div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- jquery.Thailand.js -->
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
<script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>

<style>
/* Slip upload warning styles */
@keyframes slipPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
.slip-needed-pulse {
    animation: slipPulse 1.5s ease-in-out infinite;
    font-size: 0.85em !important;
}
.slip-upload-alert {
    border: 2px solid #dc3545 !important;
    border-radius: 10px;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%) !important;
}
.slip-upload-btn {
    font-size: 1.1rem;
    font-weight: bold;
    padding: 12px 20px;
    border-radius: 8px;
    animation: slipBtnPulse 2s ease-in-out infinite;
    box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4);
}
.slip-upload-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6);
}
@keyframes slipBtnPulse {
    0%, 100% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 4px 25px rgba(220, 53, 69, 0.7); }
}
</style>

<script>
$(function () {
    App.requireLogin();

    // ─── Flatpickr วันเกิด (Buddhist Era) ───
    const profBirthFp = flatpickr('#prof_birth_date', {
        locale: 'th',
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        allowInput: false,
        disableMobile: true,
        formatDate: function(date) {
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear() + 543;
            return d + '/' + m + '/' + y;
        },
        parseDate: function(dateStr) {
            const parts = dateStr.split('/');
            if (parts.length === 3) {
                return new Date(parseInt(parts[2]) - 543, parseInt(parts[1]) - 1, parseInt(parts[0]));
            }
            return new Date(dateStr);
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

    function updateAcademicRank(position, selectedValue) {
        const $wrap = $('#academicRankWrap');
        const $select = $('#prof_academic_rank');
        const options = academicRankOptions[position];
        if (options) {
            $select.html('<option value="">-- เลือก --</option>' + options.map(o => '<option value="' + o + '"' + (o === selectedValue ? ' selected' : '') + '>' + o + '</option>').join(''));
            $wrap.slideDown(200);
        } else {
            $select.html('<option value="">-- เลือก --</option>');
            $wrap.slideUp(200);
        }
    }

    // ─── Position toggle ───
    $('#prof_position').on('change', function () {
        const val = $(this).val();
        if (val === 'other') {
            $('#positionOtherWrap').slideDown(200);
            $('#prof_position_other').focus();
        } else {
            $('#positionOtherWrap').slideUp(200);
            $('#prof_position_other').val('');
        }
        updateAcademicRank(val);
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

    // Load profile
    async function loadProfile() {
        const result = await API.getProfile();
        if (!result.success) { App.error(result.message); return; }
        const u = result.data;

        $('#profileName').text((u.prefix || '') + (u.first_name || '') + (u.last_name ? ' ' + u.last_name : '') || u.full_name);
        if (u.member_number) {
            $('#profileMemberNumber').text('เลขสมาชิก: ' + u.member_number).removeClass('d-none');
        }
        $('#profileEmail').text(u.email || '-');
        $('#profilePhone').text(u.phone || '-');
        $('#profileSince').text(u.approved_at ? App.formatDate(u.approved_at) : 'รออนุมัติ');
        if (u.position) {
            $('#profilePosition').text(u.position);
            $('#profilePositionLine').show();
        }
        if (u.academic_rank) {
            $('#profileAcademicRank').text(u.academic_rank);
            $('#profileAcademicRankLine').show();
        }
        $('#profileTypeBadge').html(
            u.role === 'admin' ? App.getRoleBadge(u.role) : App.getMemberTypeBadge(u.member_type)
        );
        $('#profileStatusBadge').html(App.getStatusBadge(u.status));
        $('#profileAvatar').attr('src', App.getProfileImage(u, true));

        // Fill form
        const form = $('#profileForm');
        form.find('[name=member_type]').val(u.member_type);
        form.find('[name=national_id]').val(u.national_id);
        form.find('[name=prefix]').val(u.prefix);
        form.find('[name=first_name]').val(u.first_name);
        form.find('[name=last_name]').val(u.last_name);
        form.find('[name=email]').val(u.email);
        // Google users cannot change email
        if (u.is_google_user) {
            form.find('[name=email]').prop('readonly', true).css('background-color', '#e9ecef');
            form.find('[name=email]').closest('.mb-3').find('label').html('อีเมล <i class="bi bi-google text-danger" style="font-size:.85em"></i>');
            form.find('[name=email]').after('<small class="text-muted"><i class="bi bi-lock me-1"></i>อีเมลจาก Google ไม่สามารถเปลี่ยนแปลงได้</small>');
        }
        form.find('[name=phone]').val(u.phone);
        form.find('[name=work_phone]').val(u.work_phone);
        form.find('[name=education_area]').val(u.education_area);
        form.find('[name=region]').val(u.region);

        // Position — normalize old values
        const positionMap = {
            'รองผู้อำนวยการโรงเรียน': 'รองผู้อำนวยการสถานศึกษา',
            'ผู้อำนวยการโรงเรียน': 'ผู้อำนวยการสถานศึกษา'
        };
        const normalizedPosition = positionMap[u.position] || u.position;
        const knownPositions = ['ผู้อำนวยการสถานศึกษา', 'รองผู้อำนวยการสถานศึกษา'];
        if (normalizedPosition && knownPositions.includes(normalizedPosition)) {
            $('#prof_position').val(normalizedPosition);
        } else if (normalizedPosition) {
            $('#prof_position').val('other');
            $('#prof_position_other').val(normalizedPosition);
            $('#positionOtherWrap').show();
        }

        // Academic rank
        updateAcademicRank(normalizedPosition || '', u.academic_rank || '');

        // Birth date
        if (u.birth_date) {
            profBirthFp.setDate(u.birth_date, true);
        }

        // School
        splitSchoolPrefix('#school_prefix', '[name=school_organization]', u.school_organization || '');

        // Home address
        let ha = u.home_address || {};
        if (typeof ha === 'string') { try { ha = JSON.parse(ha); } catch(e) { ha = {}; } }
        $('#h_no').val(ha.no || '');
        $('#h_soi').val(ha.soi || '');
        $('#h_moo').val(ha.moo || '');
        $('#h_road').val(ha.road || '');
        $('#h_subdistrict').val(ha.subdistrict || '');
        $('#h_district').val(ha.district || '');
        $('#h_province').val(ha.province || '');
        $('#h_postal').val(ha.postal_code || '');

        // Work address
        let wa = u.work_address || {};
        if (typeof wa === 'string') { try { wa = JSON.parse(wa); } catch(e) { wa = {}; } }
        $('#w_no').val(wa.no || '');
        $('#w_soi').val(wa.soi || '');
        $('#w_moo').val(wa.moo || '');
        $('#w_road').val(wa.road || '');
        $('#w_subdistrict').val(wa.subdistrict || '');
        $('#w_district').val(wa.district || '');
        $('#w_province').val(wa.province || '');
        $('#w_postal').val(wa.postal_code || '');
    }

    function splitSchoolPrefix(selectSel, inputSel, fullValue) {
        const prefixes = ['โรงเรียน', 'สพม.', 'สพป.', 'สำนักงาน'];
        let matched = '';
        for (const p of prefixes) {
            if (fullValue.startsWith(p)) { matched = p; break; }
        }
        $(selectSel).val(matched);
        $(inputSel).val(matched ? fullValue.substring(matched.length) : fullValue);
    }

    loadProfile();

    // Check sessionStorage for auto-open tab & activity detail
    const autoTab = sessionStorage.getItem('openTab');
    if (autoTab) {
        sessionStorage.removeItem('openTab');
        $('a[href="#' + autoTab + '"]').tab('show');
    }
    const autoActivityId = sessionStorage.getItem('openActivityId');
    if (autoActivityId) {
        sessionStorage.removeItem('openActivityId');
        // Wait for activities tab to load, then open detail
        setTimeout(() => {
            viewActivityDetail(parseInt(autoActivityId));
        }, 500);
    }

    // Save profile
    $('#profileForm').validate({
        rules: {
            first_name: { required: true },
            email:      { required: true, email: true }
        },
        errorClass: 'is-invalid', validClass: 'is-valid',
        errorPlacement(e, el) { e.addClass('invalid-feedback').insertAfter(el); },
        highlight(el) { $(el).addClass('is-invalid'); },
        unhighlight(el) { $(el).removeClass('is-invalid'); },
        submitHandler: function () {
            const btn = $('#btnSaveProfile');
            btn.prop('disabled', true);
            const data = {};
            $('#profileForm').serializeArray().forEach(function (f) { if (f.value) data[f.name] = f.value; });

            // Position: resolve "other" + normalize old values
            let positionVal = $('#prof_position').val();
            if (positionVal === 'other') {
                positionVal = $('#prof_position_other').val().trim();
            }
            const savePositionMap = {
                'รองผู้อำนวยการโรงเรียน': 'รองผู้อำนวยการสถานศึกษา',
                'ผู้อำนวยการโรงเรียน': 'ผู้อำนวยการสถานศึกษา'
            };
            if (savePositionMap[positionVal]) positionVal = savePositionMap[positionVal];
            data.position = positionVal;
            data.academic_rank = $('#prof_academic_rank').val() || '';

            // Combine school prefix + name
            const schoolPrefix = $('#school_prefix').val() || '';
            const schoolName = $('[name=school_organization]').val().trim();
            data.school_organization = schoolPrefix + schoolName;

            // Birth date (ISO format)
            if (profBirthFp.selectedDates.length > 0) {
                const d = profBirthFp.selectedDates[0];
                data.birth_date = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
            }

            // Home address
            data.home_address = {
                no: $('#h_no').val().trim(), soi: $('#h_soi').val().trim(), moo: $('#h_moo').val().trim(),
                road: $('#h_road').val().trim(), subdistrict: $('#h_subdistrict').val().trim(),
                district: $('#h_district').val().trim(), province: $('#h_province').val().trim(),
                postal_code: $('#h_postal').val().trim()
            };

            // Work address
            data.work_address = {
                no: $('#w_no').val().trim(), soi: $('#w_soi').val().trim(), moo: $('#w_moo').val().trim(),
                road: $('#w_road').val().trim(), subdistrict: $('#w_subdistrict').val().trim(),
                district: $('#w_district').val().trim(), province: $('#w_province').val().trim(),
                postal_code: $('#w_postal').val().trim()
            };

            API.updateProfile(data)
                .then(function (result) {
                    if (result.success) {
                        App.success('บันทึกข้อมูลสำเร็จ');
                        const user = API.getUser();
                        if (user) {
                            Object.assign(user, data);
                            if (result.data && typeof result.data === 'object' && result.data.profile_image !== undefined) {
                                user.profile_image = result.data.profile_image;
                            }
                            localStorage.setItem('sdak_user', JSON.stringify(user));
                        }
                        if (result.data && result.data.profile_image) {
                            $('#profileAvatar').attr('src', App.imgUrl(result.data.profile_image, true));
                        }
                        App.updateNavbar();
                    } else {
                        App.error(result.message);
                    }
                    btn.prop('disabled', false);
                })
                .catch(function (err) {
                    console.error('Profile error:', err);
                    App.error('เกิดข้อผิดพลาด');
                    btn.prop('disabled', false);
                });

            return false;
        }
    });

    // Change password
    $('#passwordForm').validate({
        rules: {
            current_password: { required: true },
            new_password:     { required: true, minlength: 6 },
            confirm_password: { required: true, equalTo: '#newPassword' }
        },
        messages: {
            confirm_password: { equalTo: 'รหัสผ่านไม่ตรงกัน' }
        },
        errorClass: 'is-invalid', validClass: 'is-valid',
        errorPlacement(e, el) { e.addClass('invalid-feedback').insertAfter(el); },
        highlight(el) { $(el).addClass('is-invalid'); },
        unhighlight(el) { $(el).removeClass('is-invalid'); },
        submitHandler: function () {
            const btn = $('#btnChangePassword');
            btn.prop('disabled', true);

            API.updateProfile({
                current_password: $('[name=current_password]').val(),
                new_password:     $('[name=new_password]').val()
            })
                .then(function (result) {
                    if (result.success) {
                        App.success('เปลี่ยนรหัสผ่านสำเร็จ');
                        $('#passwordForm')[0].reset();
                    } else {
                        App.error(result.message);
                    }
                    btn.prop('disabled', false);
                })
                .catch(function (err) {
                    console.error('Password error:', err);
                    App.error('เกิดข้อผิดพลาด');
                    btn.prop('disabled', false);
                });

            return false;
        }
    });

    // ─── Avatar Upload with Cropper ───
    let avatarCropper = null;
    let avatarCropFile = null;

    // File input → open cropper
    $('#avatarFileInput').on('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (file.size > 10 * 1024 * 1024) { App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)'); this.value = ''; return; }
        avatarCropFile = file;
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('avatarCropperImage');
            img.src = e.target.result;
            if (avatarCropper) { avatarCropper.destroy(); avatarCropper = null; }
            $('#avatarOptionsModal').modal('hide');
            $('#avatarCropperModal').modal('show');
            $('#avatarCropperModal').one('shown.bs.modal', function () {
                avatarCropper = new Cropper(img, {
                    aspectRatio: 1,
                    viewMode: 2,
                    autoCropArea: 1,
                    responsive: true,
                    guides: true,
                    background: true,
                });
            });
        };
        reader.readAsDataURL(file);
    });

    // Crop confirm → upload with crop data
    $('#btnAvatarCropConfirm').on('click', async function () {
        if (!avatarCropper || !avatarCropFile) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังอัปโหลด...');

        const cropData = avatarCropper.getData(true);
        const formData = new FormData();
        formData.append('file', avatarCropFile);
        formData.append('cropX', cropData.x);
        formData.append('cropY', cropData.y);
        formData.append('cropWidth', cropData.width);
        formData.append('cropHeight', cropData.height);

        try {
            const token = API.getToken();
            const headers = {};
            if (token) headers['X-Auth-Token'] = token;
            const response = await fetch(API.baseUrl + API.apiUrl('upload', 'image', { type: 'profiles' }), {
                method: 'POST', headers, body: formData
            });
            const result = await response.json();
            if (result.success) {
                const url = result.data.url;
                const updateResult = await API.updateProfile({ profile_image: url });
                if (updateResult.success) {
                    const savedUrl = updateResult.data?.profile_image || url;
                    $('#profileAvatar').attr('src', App.imgUrl(savedUrl, true));
                    const user = API.getUser();
                    if (user) { user.profile_image = savedUrl; localStorage.setItem('sdak_user', JSON.stringify(user)); }
                    App.updateNavbar();
                    $('#avatarCropperModal').modal('hide');
                    App.success('อัปโหลดรูปโปรไฟล์สำเร็จ');
                } else {
                    App.error(updateResult.message || 'เกิดข้อผิดพลาดในการบันทึกรูปโปรไฟล์');
                }
            } else {
                App.error(result.message);
            }
        } catch (err) {
            App.error('เกิดข้อผิดพลาดในการอัปโหลด');
            console.error(err);
        }
        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> ครอปและบันทึก');
    });

    $('#avatarCropperModal').on('hidden.bs.modal', function () {
        if (avatarCropper) { avatarCropper.destroy(); avatarCropper = null; }
        $('#avatarFileInput').val('');
    });

    // Link input
    window.showAvatarLinkInput = function () {
        $('#avatarLinkSection').slideToggle();
    };

    window.saveAvatarLink = async function () {
        const url = $('#avatarLinkInput').val().trim();
        if (!url) { App.error('กรุณากรอก URL รูปภาพ'); return; }
        const result = await API.updateProfile({ profile_image: url });
        if (result.success) {
            const savedUrl = result.data?.profile_image || url;
            $('#profileAvatar').attr('src', App.imgUrl(savedUrl, true));
            const user = API.getUser();
            if (user) { user.profile_image = savedUrl; localStorage.setItem('sdak_user', JSON.stringify(user)); }
            App.updateNavbar();
            $('#avatarOptionsModal').modal('hide');
            App.success('บันทึกรูปโปรไฟล์สำเร็จ');
        } else {
            App.error(result.message);
        }
    };

    // Load my activities when tab shown
    $('a[href="#tabActivities"]').on('shown.bs.tab', function() {
        loadMyActivities();
    });

    // Activity filter tabs
    let currentActivityFilter = 'all';
    let cachedActivities = [];
    let bankInfo = { bank_name: '', account_name: '', account_number: '' };

    // Load bank info for slip upload modals
    (async function() {
        const res = await API.getSettings();
        if (res.success && res.data) {
            bankInfo = {
                bank_name: res.data.bank_name || '',
                account_name: res.data.bank_account_name || '',
                account_number: res.data.bank_account_number || ''
            };
        }
    })();

    function buildBankInfoHtml(feeAmount) {
        let html = '';
        if (feeAmount) {
            html += `<div class="alert alert-warning text-center mb-3 py-2">
                <i class="bi bi-cash-coin me-1"></i> <strong>ยอดที่ต้องชำระ: <span class="text-danger" style="font-size:1.3em">${App.formatCurrency(feeAmount)}</span></strong>
            </div>`;
        }
        if (bankInfo.bank_name || bankInfo.account_number) {
            html += `<div class="card border-info mb-3">
                <div class="card-header bg-info text-white py-2"><i class="bi bi-bank me-1"></i> ข้อมูลบัญชีสำหรับโอน</div>
                <div class="card-body py-2">
                    <table class="table table-sm table-borderless mb-0">
                        ${bankInfo.bank_name ? `<tr><td class="text-muted" style="width:35%">ธนาคาร</td><td><strong>${App.escapeHtml(bankInfo.bank_name)}</strong></td></tr>` : ''}
                        ${bankInfo.account_name ? `<tr><td class="text-muted">ชื่อบัญชี</td><td><strong>${App.escapeHtml(bankInfo.account_name)}</strong></td></tr>` : ''}
                        ${bankInfo.account_number ? `<tr><td class="text-muted">เลขที่บัญชี</td><td><strong class="text-primary" style="font-size:1.1em;letter-spacing:1px">${App.escapeHtml(bankInfo.account_number)}</strong></td></tr>` : ''}
                    </table>
                </div>
            </div>`;
        }
        return html;
    }

    $('#activityFilter').on('click', '.nav-link', function(e) {
        e.preventDefault();
        $('#activityFilter .nav-link').removeClass('active');
        $(this).addClass('active');
        currentActivityFilter = $(this).data('filter');
        renderActivities(cachedActivities);
    });

    async function loadMyActivities() {
        const container = $('#myActivitiesList');
        container.html('<div class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</div>');

        const result = await API.getActivities({});
        if (!result.success || !result.data || result.data.length === 0) {
            container.html('<p class="text-center text-muted py-3"><i class="bi bi-calendar-x" style="font-size:2rem;"></i><br>ยังไม่มีกิจกรรม</p>');
            cachedActivities = [];
            return;
        }

        cachedActivities = result.data;
        renderActivities(result.data);
    }

    function renderActivities(activities) {
        const container = $('#myActivitiesList');
        let filtered = activities;

        if (currentActivityFilter === 'registered') {
            filtered = activities.filter(a => a.my_registration);
        } else if (currentActivityFilter === 'available') {
            filtered = activities.filter(a => a.registration_open && a.status === 'open' && !a.my_registration);
        }

        if (filtered.length === 0) {
            const msgs = {
                all: 'ยังไม่มีกิจกรรม',
                registered: 'ยังไม่ได้ลงทะเบียนกิจกรรมใด',
                available: 'ไม่มีกิจกรรมที่เปิดรับสมัครขณะนี้'
            };
            container.html(`<p class="text-center text-muted py-3"><i class="bi bi-calendar-x" style="font-size:2rem;"></i><br>${msgs[currentActivityFilter]}</p>`);
            return;
        }

        let html = '<div class="row">';
        filtered.forEach(a => {
            const isPast = a.end_date && new Date(a.end_date) < new Date();
            const statusBadge = getActivityStatusBadge(a, isPast);
            const regBadge = a.my_registration ? getRegStatusBadge(a.my_registration.status) : '';
            const needsSlip = a.my_registration && a.has_fee && a.my_registration.payment_status === 'pending' && !a.my_registration.payment_proof;
            const slipWarning = needsSlip ? '<span class="badge badge-danger slip-needed-pulse d-block mt-1"><i class="bi bi-exclamation-triangle me-1"></i>ยังไม่อัพโหลดสลิป - กรุณาอัพโหลด</span>' : '';
            const coverImg = a.cover_image
                ? `<img src="${a.cover_image.startsWith('http') ? a.cover_image : (BASE_PATH + a.cover_image)}" class="card-img-top" style="height:160px;object-fit:cover;" alt="">`
                : `<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:160px;"><i class="bi bi-calendar-event" style="font-size:3rem;color:#ccc;"></i></div>`;
            const dateStr = App.formatDate(a.start_date) + (a.end_date && a.end_date !== a.start_date ? ' - ' + App.formatDate(a.end_date) : '');
            const descSnippet = a.description ? App.escapeHtml(a.description).substring(0, 80) + (a.description.length > 80 ? '...' : '') : '';
            const participantInfo = a.max_participants ? `${a.registration_count || 0}/${a.max_participants}` : (a.registration_count || 0) + ' คน';

            html += `
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-sm ${needsSlip ? 'border-danger' : ''}" style="cursor:pointer; transition:transform .15s;${needsSlip ? 'border-width:2px;' : ''}" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='none'" onclick="viewActivityDetail(${a.id})">
                    ${coverImg}
                    <div class="card-body pb-2">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="card-title mb-0" style="line-height:1.4;">${App.escapeHtml(a.title)}</h6>
                            ${statusBadge}
                        </div>
                        ${descSnippet ? `<p class="text-muted small mb-2">${descSnippet}</p>` : ''}
                        <div class="small text-muted">
                            <div><i class="bi bi-calendar3 me-1"></i>${dateStr}</div>
                            ${a.location ? `<div><i class="bi bi-geo-alt me-1"></i>${App.escapeHtml(a.location)}</div>` : ''}
                            <div><i class="bi bi-people me-1"></i>ผู้เข้าร่วม: ${participantInfo}</div>
                            ${a.has_fee ? `<div><i class="bi bi-cash me-1"></i>ค่าลงทะเบียน: ${App.formatCurrency(a.fee_amount)}</div>` : ''}
                        </div>
                    </div>
                    ${regBadge || slipWarning ? `<div class="card-footer bg-white border-top-0 pt-0"><small>${regBadge}</small>${slipWarning}</div>` : ''}
                </div>
            </div>`;
        });
        html += '</div>';
        container.html(html);
    }

    function getActivityStatusBadge(a, isPast) {
        if (isPast) return '<span class="badge badge-secondary">จบแล้ว</span>';
        if (a.status === 'closed') return '<span class="badge badge-secondary">ปิด</span>';
        if (a.registration_open) return '<span class="badge badge-success">เปิดรับสมัคร</span>';
        return '<span class="badge badge-info">กำลังดำเนินการ</span>';
    }

    function getRegStatusBadge(status) {
        const map = {
            pending: '<span class="badge badge-warning"><i class="bi bi-clock me-1"></i>รอการอนุมัติ</span>',
            approved: '<span class="badge badge-success"><i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว</span>',
            rejected: '<span class="badge badge-danger"><i class="bi bi-x-circle me-1"></i>ถูกปฏิเสธ</span>',
        };
        return map[status] || '';
    }

    // ─── Activity Detail Modal ───
    window.viewActivityDetail = async function(id) {
        const body = $('#activityDetailBody');
        const footer = $('#activityDetailFooter');
        body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');
        footer.html('');
        $('#activityDetailModal').modal('show');

        const result = await API.getActivityDetail(id);
        if (!result.success || !result.data) {
            body.html('<p class="text-center text-danger py-3">ไม่พบข้อมูลกิจกรรม</p>');
            return;
        }

        const a = result.data;
        const isPast = a.end_date && new Date(a.end_date) < new Date();
        const dateStr = App.formatDate(a.start_date) + (a.end_date && a.end_date !== a.start_date ? ' - ' + App.formatDate(a.end_date) : '');
        const participantInfo = a.max_participants ? `${a.registration_count || 0}/${a.max_participants}` : (a.registration_count || 0) + ' คน';
        const isFull = a.max_participants && a.approved_count >= a.max_participants;

        let coverHtml = '';
        if (a.cover_image) {
            const src = a.cover_image.startsWith('http') ? a.cover_image : (BASE_PATH + a.cover_image);
            coverHtml = `<div class="mb-3"><img src="${src}" class="img-fluid rounded" style="width:100%;max-height:300px;object-fit:cover;" alt=""></div>`;
        }

        body.html(`
            ${coverHtml}
            <h5 class="mb-2">${App.escapeHtml(a.title)}</h5>
            <div class="row mb-3">
                <div class="col-sm-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr><td class="text-muted" style="width:40%;"><i class="bi bi-calendar3 me-1"></i>วันที่</td><td>${dateStr}</td></tr>
                        ${a.location ? `<tr><td class="text-muted"><i class="bi bi-geo-alt me-1"></i>สถานที่</td><td>${App.escapeHtml(a.location)}</td></tr>` : ''}
                        <tr><td class="text-muted"><i class="bi bi-people me-1"></i>ผู้เข้าร่วม</td><td>${participantInfo}</td></tr>
                    </table>
                </div>
                <div class="col-sm-6">
                    <table class="table table-sm table-borderless mb-0">
                        ${a.has_fee ? `<tr><td class="text-muted" style="width:40%;"><i class="bi bi-cash me-1"></i>ค่าลงทะเบียน</td><td>${App.formatCurrency(a.fee_amount)}</td></tr>` : '<tr><td class="text-muted"><i class="bi bi-cash me-1"></i>ค่าลงทะเบียน</td><td class="text-success">ฟรี</td></tr>'}
                        ${a.has_fee && a.fee_description ? `<tr><td class="text-muted"><i class="bi bi-info-circle me-1"></i>รายละเอียดค่าใช้จ่าย</td><td>${App.escapeHtml(a.fee_description)}</td></tr>` : ''}
                        <tr><td class="text-muted"><i class="bi bi-flag me-1"></i>สถานะ</td><td>${isPast ? '<span class="badge badge-secondary">จบแล้ว</span>' : a.registration_open ? '<span class="badge badge-success">เปิดรับสมัคร</span>' : '<span class="badge badge-info">ปิดรับสมัคร</span>'}</td></tr>
                    </table>
                </div>
            </div>
            ${a.description ? `<div class="border-top pt-3"><h6><i class="bi bi-text-paragraph me-1"></i>รายละเอียด</h6><div class="text-muted" style="white-space:pre-line;">${App.escapeHtml(a.description)}</div></div>` : ''}
            ${a.my_registration ? `
            <div class="border-top pt-3 mt-3">
                <h6><i class="bi bi-person-check me-1"></i>การลงทะเบียนของฉัน</h6>
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted" style="width:35%;">สถานะ</td><td>${getRegStatusBadge(a.my_registration.status)}</td></tr>
                    <tr><td class="text-muted">วันที่ลงทะเบียน</td><td>${App.formatDate(a.my_registration.registered_at)}</td></tr>
                    ${a.my_registration.payment_status && a.my_registration.payment_status !== 'not_required' ? `<tr><td class="text-muted">การชำระเงิน</td><td>${a.my_registration.payment_status === 'paid' ? '<span class="badge badge-success">ชำระแล้ว</span>' : '<span class="badge badge-warning">รอชำระ</span>'}</td></tr>` : ''}
                    ${a.my_registration.payment_proof ? `<tr><td class="text-muted">สลิปโอนเงิน</td><td><a href="#" onclick="previewSlipImg('${a.my_registration.payment_proof}');return false;" class="btn btn-sm btn-outline-info"><i class="bi bi-image me-1"></i>ดูสลิป</a></td></tr>` : ''}
                    ${a.my_registration.payment_status === 'pending' && a.has_fee && !a.my_registration.payment_proof ? `
                    <tr><td colspan="2" class="pt-3">
                        <div class="alert alert-danger mb-0 slip-upload-alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill text-danger me-2" style="font-size:1.5rem;"></i>
                                <strong class="text-danger">กรุณาอัพโหลดสลิปโอนเงิน</strong>
                            </div>
                            <p class="mb-2 small">คุณได้ลงทะเบียนกิจกรรมนี้แล้ว แต่ยังไม่ได้อัพโหลดสลิปการโอนเงิน</p>
                            <button class="btn btn-danger btn-lg w-100 slip-upload-btn" onclick="uploadSlipForReg(${a.my_registration.id}, ${a.id}, ${a.fee_amount || 0})">
                                <i class="bi bi-cloud-arrow-up me-2" style="font-size:1.3rem;"></i>อัพโหลดสลิปโอนเงินตอนนี้
                            </button>
                        </div>
                    </td></tr>` : ''}
                    ${a.my_registration.note ? `<tr><td class="text-muted">หมายเหตุ</td><td>${App.escapeHtml(a.my_registration.note)}</td></tr>` : ''}
                </table>
            </div>` : ''}
        `);

        // Footer buttons
        let footerHtml = '<button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>';

        if (a.my_registration) {
            if (a.my_registration.status === 'pending') {
                footerHtml = `<button class="btn btn-danger" onclick="cancelRegistration(${a.my_registration.id}, ${a.id})"><i class="bi bi-x-circle me-1"></i>ยกเลิกลงทะเบียน</button>` + footerHtml;
            }
        } else if (a.registration_open && a.status === 'open' && !isPast && !isFull) {
            // Check member type eligibility
            const memberTypeLabels = App._memberTypeLabelsShort || { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
            let memberTypeOk = true;
            if (a.allowed_member_types) {
                const allowedTypes = a.allowed_member_types.split(',').map(t => t.trim());
                const myType = API.getUser()?.member_type || '';
                if (!allowedTypes.includes(myType)) {
                    memberTypeOk = false;
                    const typeNames = allowedTypes.map(t => memberTypeLabels[t] || t).join(', ');
                    footerHtml = `<button class="btn btn-secondary" disabled><i class="bi bi-lock me-1"></i>เฉพาะ: ${typeNames}</button>` + footerHtml;
                }
            }
            if (memberTypeOk) {
                footerHtml = `<button class="btn btn-primary" onclick="registerActivity(${a.id}, ${a.has_fee ? 1 : 0})"><i class="bi bi-check-circle me-1"></i>ลงทะเบียน</button>` + footerHtml;
            }
        } else if (isFull) {
            footerHtml = `<button class="btn btn-secondary" disabled><i class="bi bi-people-fill me-1"></i>เต็มแล้ว</button>` + footerHtml;
        }

        footer.html(footerHtml);
    };

    // ─── Register / Cancel ───
    window.registerActivity = async function(activityId, hasFee) {
        if (hasFee) {
            // Find activity to get fee_amount
            const act = cachedActivities.find(a => a.id == activityId);
            const feeAmount = act ? act.fee_amount : 0;

            // Show slip upload dialog for paid activities
            const { value: formValues } = await Swal.fire({
                title: 'ลงทะเบียนกิจกรรม',
                html: `
                    <p class="text-muted small mb-2">กิจกรรมนี้มีค่าธรรมเนียม กรุณาโอนเงินและอัพโหลดสลิป</p>
                    ${buildBankInfoHtml(feeAmount)}
                    <div class="form-group text-left">
                        <label class="font-weight-bold"><i class="bi bi-image me-1"></i>สลิปโอนเงิน <span class="text-danger">*</span></label>
                        <input type="file" id="swal-slip-file" class="form-control" accept="image/*">
                        <small class="text-muted">รองรับไฟล์รูปภาพ (JPG, PNG)</small>
                    </div>
                    <div id="swal-slip-preview" class="mt-2" style="display:none">
                        <img src="" class="img-fluid rounded border" style="max-height:200px">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> ลงทะเบียน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#28a745',
                didOpen: () => {
                    document.getElementById('swal-slip-file').addEventListener('change', function() {
                        const file = this.files[0]; if (!file) return;
                        const reader = new FileReader();
                        reader.onload = e => {
                            const prev = document.getElementById('swal-slip-preview');
                            prev.style.display = 'block';
                            prev.querySelector('img').src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    });
                },
                preConfirm: async () => {
                    const fileInput = document.getElementById('swal-slip-file');
                    const file = fileInput.files[0];
                    if (!file) {
                        Swal.showValidationMessage('กรุณาอัพโหลดสลิปโอนเงิน');
                        return false;
                    }
                    Swal.showLoading();
                    try {
                        const uploadRes = await API.upload(file, 'payment_slips');
                        if (!uploadRes.success) {
                            Swal.showValidationMessage(uploadRes.message || 'อัพโหลดสลิปไม่สำเร็จ');
                            return false;
                        }
                        return uploadRes.data.url;
                    } catch(e) {
                        Swal.showValidationMessage('เกิดข้อผิดพลาดในการอัพโหลด');
                        return false;
                    }
                }
            });
            if (!formValues) return;
            const result = await API.registerActivity(activityId, formValues);
            if (result.success) {
                App.success(result.message || 'ลงทะเบียนสำเร็จ พร้อมแนบสลิปโอนเงินแล้ว');
                await loadMyActivities();
                viewActivityDetail(activityId);
            } else {
                App.error(result.message || 'เกิดข้อผิดพลาด');
            }
        } else {
            // Free activity
            const confirmed = await Swal.fire({
                title: 'ยืนยันลงทะเบียน?',
                text: 'คุณต้องการลงทะเบียนเข้าร่วมกิจกรรมนี้',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: '<i class="bi bi-check-circle me-1"></i> ลงทะเบียน',
                cancelButtonText: 'ยกเลิก',
                confirmButtonColor: '#28a745'
            });
            if (!confirmed.isConfirmed) return;
            const result = await API.registerActivity(activityId);
            if (result.success) {
                App.success(result.message || 'ลงทะเบียนสำเร็จ');
                await loadMyActivities();
                viewActivityDetail(activityId);
            } else {
                App.error(result.message || 'เกิดข้อผิดพลาด');
            }
        }
    };

    // Upload slip for existing registration
    window.uploadSlipForReg = async function(registrationId, activityId, feeAmount) {
        const { value: formValues } = await Swal.fire({
            title: 'อัพโหลดสลิปโอนเงิน',
            html: `
                ${buildBankInfoHtml(feeAmount)}
                <div class="form-group text-left">
                    <label class="font-weight-bold"><i class="bi bi-image me-1"></i>สลิปโอนเงิน <span class="text-danger">*</span></label>
                    <input type="file" id="swal-slip-file2" class="form-control" accept="image/*">
                </div>
                <div id="swal-slip-preview2" class="mt-2" style="display:none">
                    <img src="" class="img-fluid rounded border" style="max-height:200px">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-upload me-1"></i> อัพโหลด',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745',
            didOpen: () => {
                document.getElementById('swal-slip-file2').addEventListener('change', function() {
                    const file = this.files[0]; if (!file) return;
                    const reader = new FileReader();
                    reader.onload = e => {
                        const prev = document.getElementById('swal-slip-preview2');
                        prev.style.display = 'block';
                        prev.querySelector('img').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            },
            preConfirm: async () => {
                const fileInput = document.getElementById('swal-slip-file2');
                const file = fileInput.files[0];
                if (!file) {
                    Swal.showValidationMessage('กรุณาเลือกไฟล์สลิป');
                    return false;
                }
                Swal.showLoading();
                try {
                    const uploadRes = await API.upload(file, 'payment_slips');
                    if (!uploadRes.success) {
                        Swal.showValidationMessage(uploadRes.message || 'อัพโหลดไม่สำเร็จ');
                        return false;
                    }
                    return uploadRes.data.url;
                } catch(e) {
                    Swal.showValidationMessage('เกิดข้อผิดพลาด');
                    return false;
                }
            }
        });
        if (!formValues) return;
        const result = await API.uploadPaymentSlip(registrationId, formValues);
        if (result.success) {
            App.success('อัพโหลดสลิปสำเร็จ');
            viewActivityDetail(activityId);
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    };

    // Preview slip image
    window.previewSlipImg = function(url) {
        const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
        Swal.fire({ imageUrl: src, imageAlt: 'สลิปโอนเงิน', showConfirmButton: false, showCloseButton: true });
    };

    window.cancelRegistration = async function(registrationId, activityId) {
        const confirmed = await Swal.fire({
            title: 'ยกเลิกลงทะเบียน?',
            text: 'คุณต้องการยกเลิกการลงทะเบียนจริงหรือไม่?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-x-circle me-1"></i> ยกเลิก',
            cancelButtonText: 'ไม่ใช่',
            confirmButtonColor: '#dc3545'
        });
        if (!confirmed.isConfirmed) return;

        const result = await API.cancelActivityRegistration(registrationId);
        if (result.success) {
            App.success(result.message || 'ยกเลิกลงทะเบียนแล้ว');
            await loadMyActivities();
            $('#activityDetailModal').modal('hide');
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    };

    // ========================
    // Telegram Link Functions
    // ========================
    
    let telegramLinkInProgress = false;

    // โหลดสถานะการเชื่อมต่อ Telegram
    async function loadTelegramStatus() {
        try {
            const result = await API.get(API.apiUrl('telegram-link', 'status'));
            if (result.success) {
                updateTelegramUI(result.data);
            }
        } catch (error) {
            console.warn('ไม่สามารถโหลดสถานะ Telegram ได้:', error);
        }
    }

    // อัปเดต UI ตามสถานะการเชื่อมต่อ
    function updateTelegramUI(status) {
        const statusBadge = $('#telegramStatusBadge');
        const connectBtn = $('#btnConnectTelegram');
        const hintText = $('#telegramConnectHint');
        const botInfoSection = $('#telegramBotInfo');
        const fallbackHeader = $('#telegramFallbackHeader');

        // แสดงข้อมูล Bot
        if (status.bot && status.bot.name) {
            fallbackHeader.hide();
            botInfoSection.show();
            $('#telegramBotName').text(status.bot.name);
            $('#telegramBotUsername').text('@' + status.bot.username);
            if (status.bot.photo_url) {
                $('#telegramBotPhoto').attr('src', status.bot.photo_url).show();
            } else {
                // ใช้ Telegram logo แทนถ้าไม่มีรูป
                $('#telegramBotPhoto').attr('src', 'https://telegram.org/img/t_logo.svg').show();
            }
        } else {
            botInfoSection.hide();
            fallbackHeader.show();
        }

        if (status.is_linked) {
            statusBadge.removeClass('bg-secondary').addClass('bg-success').text('เชื่อมต่อแล้ว');
            connectBtn.removeClass('btn-outline-primary').addClass('btn-outline-danger')
                     .html('<i class="bi bi-x-circle me-1"></i> ยกเลิกการเชื่อมต่อ');
            hintText.html(`เชื่อมต่อเมื่อ: ${status.linked_at_thai || status.linked_at}<br>Chat ID: <code>${status.chat_id}</code>`);
        } else {
            statusBadge.removeClass('bg-success').addClass('bg-secondary').text('ยังไม่ได้เชื่อมต่อ');
            connectBtn.removeClass('btn-outline-danger').addClass('btn-outline-primary')
                     .html('<i class="bi bi-telegram me-1"></i> เชื่อมต่อบัญชี Telegram');
            hintText.html('กดปุ่มเพื่อเชื่อมบัญชี Telegram ของคุณกับระบบ<br>เพื่อรับการแจ้งเตือนและใช้งานฟีเจอร์เพิ่มเติม');
        }
    }

    // การเชื่อมต่อ Telegram
    async function handleTelegramConnect() {
        if (telegramLinkInProgress) return;

        try {
            const result = await API.get(API.apiUrl('telegram-link', 'status'));
            if (result.success && result.data.is_linked) {
                // ยกเลิกการเชื่อมต่อ
                const confirmed = await Swal.fire({
                    title: 'ยกเลิกการเชื่อมต่อ Telegram?',
                    text: 'คุณจะไม่ได้รับการแจ้งเตือนผ่าน Telegram อีกต่อไป',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-x-circle me-1"></i> ยกเลิกการเชื่อมต่อ',
                    cancelButtonText: 'ไม่ยกเลิก',
                    confirmButtonColor: '#dc3545'
                });

                if (confirmed.isConfirmed) {
                    telegramLinkInProgress = true;
                    // แสดง loading ขณะยกเลิก
                    Swal.fire({
                        title: 'กำลังยกเลิกการเชื่อมต่อ...',
                        html: '<div class="d-flex justify-content-center"><div class="spinner-border text-danger" role="status"></div></div>',
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });
                    try {
                        const unlinkResult = await API.post(API.apiUrl('telegram-link', 'unlink'));
                        Swal.close();
                        if (unlinkResult.success) {
                            App.success('ยกเลิกการเชื่อมต่อ Telegram เรียบร้อย');
                            await loadTelegramStatus();
                        } else {
                            App.error(unlinkResult.message || 'เกิดข้อผิดพลาด');
                        }
                    } catch (err) {
                        Swal.close();
                        App.error('เกิดข้อผิดพลาดในการยกเลิกเชื่อมต่อ');
                    }
                    telegramLinkInProgress = false;
                }
            } else {
                // เชื่อมต่อ Telegram
                telegramLinkInProgress = true;
                
                const tokenResult = await API.post(API.apiUrl('telegram-link', 'create-token'));
                if (!tokenResult.success) {
                    App.error(tokenResult.message || 'ไม่สามารถสร้าง token ได้');
                    telegramLinkInProgress = false;
                    return;
                }

                const botLink = tokenResult.data.bot_link;
                const expiresAt = new Date(tokenResult.data.expires_at);
                
                const result = await Swal.fire({
                    title: '<i class="bi bi-telegram text-primary"></i> เชื่อมต่อ Telegram',
                    html: `
                        <div class="text-start">
                            <p><strong>ขั้นตอนการเชื่อมต่อ:</strong></p>
                            <ol class="small">
                                <li>กดปุ่ม "เปิด Telegram Bot" ด้านล่าง</li>
                                <li>กดปุ่ม "Start" ใน Telegram</li>
                                <li>รอสักครู่ ระบบจะเชื่อมต่ออัตโนมัติ</li>
                            </ol>
                            <div class="alert alert-warning mt-2 mb-0">
                                <small><i class="bi bi-clock me-1"></i> ลิงก์นี้หมดอายุเวลา: <strong>${expiresAt.toLocaleString('th-TH')}</strong></small>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-telegram me-1"></i> เปิด Telegram Bot',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#0088cc',
                    allowOutsideClick: false,
                    preConfirm: () => {
                        window.open(botLink, '_blank');
                        return true;
                    }
                });

                if (result.isConfirmed) {
                    // แสดง modal รอการเชื่อมต่อ
                    Swal.fire({
                        title: 'รอการเชื่อมต่อ...',
                        html: `
                            <div class="d-flex flex-column align-items-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-2">กรุณากดปุ่ม "Start" ใน Telegram</p>
                                <small class="text-muted">ระบบจะตรวจสอบการเชื่อมต่อทุก 3 วินาที</small>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            // ตรวจสอบสถานะทุก 3 วินาที
                            const checkInterval = setInterval(async () => {
                                try {
                                    const statusCheck = await API.get(API.apiUrl('telegram-link', 'status'));
                                    if (statusCheck.success && statusCheck.data.is_linked) {
                                        clearInterval(checkInterval);
                                        Swal.close();
                                        App.success('เชื่อมต่อ Telegram สำเร็จแล้ว! 🎉');
                                        await loadTelegramStatus();
                                        telegramLinkInProgress = false;
                                    }
                                } catch (error) {
                                    console.log('กำลังตรวจสอบ...');
                                }
                            }, 3000);

                            // หยุดตรวจสอบหลัง 2 นาที
                            setTimeout(() => {
                                clearInterval(checkInterval);
                                if (Swal.isVisible()) {
                                    Swal.close();
                                    App.info('หมดเวลารอการเชื่อมต่อ กรุณาลองใหม่อีกครั้ง');
                                    telegramLinkInProgress = false;
                                }
                            }, 120000);
                        }
                    });
                } else {
                    telegramLinkInProgress = false;
                }
            }
        } catch (error) {
            App.error('เกิดข้อผิดพลาด: ' + error.message);
            telegramLinkInProgress = false;
        }
    }

    // Event listeners
    $('#btnConnectTelegram').on('click', handleTelegramConnect);

    // โหลดสถานะ Telegram เมื่อโหลดหน้า
    loadTelegramStatus();
});
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
