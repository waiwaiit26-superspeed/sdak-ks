<?php $pageTitle = 'จัดการกิจกรรม'; $page = 'activities'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-calendar-event me-2"></i>จัดการกิจกรรม</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">กิจกรรม</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" onclick="openActivityForm()">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มกิจกรรม
                </button>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="open">เปิดรับสมัคร</option>
                                <option value="closed">ปิดรับสมัคร</option>
                                <option value="draft">แบบร่าง</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="searchActivity" class="form-control form-control-sm" placeholder="ค้นหากิจกรรม...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="loadActivities(1)"><i class="bi bi-search"></i> ค้นหา</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อกิจกรรม</th>
                                    <th>วันที่</th>
                                    <th>สถานที่</th>
                                    <th>ค่าลงทะเบียน</th>
                                    <th>ผู้เข้าร่วม</th>
                                    <th>การเข้าถึง</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="activitiesTable">
                                <tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><nav id="activityPagination"></nav></div>
            </div>
        </div>
    </div>

<!-- Modal: Activity Form -->
<div class="modal fade" id="activityFormModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actFormTitle">เพิ่มกิจกรรม</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="activityForm" novalidate>
                    <input type="hidden" id="actId" name="id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="actTitle" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-control" name="status" id="actStatus">
                                <option value="draft">แบบร่าง</option>
                                <option value="open">เปิดรับสมัคร</option>
                                <option value="closed">ปิดรับสมัคร</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันเริ่มต้น <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_date" id="actStart" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันสิ้นสุด</label>
                            <input type="datetime-local" class="form-control" name="end_date" id="actEnd">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">สถานที่</label>
                            <input type="text" class="form-control" name="location" id="actLocation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">จำนวนรับ (0 = ไม่จำกัด)</label>
                            <input type="number" class="form-control" name="max_participants" id="actMax" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">การเข้าถึง</label>
                            <select class="form-control" name="visibility" id="actVisibility">
                                <option value="public">เปิดให้คนทั่วไป</option>
                                <option value="members_only">เฉพาะสมาชิกสมาคม</option>
                                <option value="custom">กำหนดเอง</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="actVisibilityTextWrap" style="display:none">
                            <label class="form-label">ข้อความแสดง (กำหนดเอง)</label>
                            <input type="text" class="form-control" name="visibility_text" id="actVisibilityText" placeholder="เช่น เฉพาะผู้บริหาร">
                        </div>
                        <div class="col-md-8 mb-3" id="actMemberTypesWrap">
                            <label class="form-label">ประเภทสมาชิกที่สมัครได้</label>
                            <div class="d-flex flex-wrap gap-3" id="memberTypeCheckboxes">
                                <!-- Populated by JS from DB -->
                            </div>
                            <small class="text-muted">ไม่เลือก = เปิดรับทุกประเภท</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ค่าลงทะเบียน (บาท)</label>
                            <input type="number" class="form-control" name="fee_amount" id="actFee" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">รายละเอียดค่าลงทะเบียน</label>
                            <input type="text" class="form-control" name="fee_description" id="actFeeDesc" placeholder="เช่น ค่าอาหาร ค่าเอกสาร ฯลฯ">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ภาพปก</label>
                            <!-- Tab: เลือกวิธีใส่รูป -->
                            <ul class="nav nav-tabs nav-tabs-sm mb-2" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#actCoverUploadTab">อัปโหลดรูป</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#actCoverUrlTab">ลิงก์รูปภาพ</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="actCoverUploadTab">
                                    <input type="file" class="form-control form-control-sm" id="actCoverFile" accept="image/*">
                                    <small class="text-muted">รองรับ JPEG, PNG, GIF, WEBP (สูงสุด 10 MB)</small>
                                </div>
                                <div class="tab-pane fade" id="actCoverUrlTab">
                                    <div class="input-group input-group-sm">
                                        <input type="url" class="form-control" id="actCoverLinkInput" placeholder="https://example.com/image.jpg">
                                        <button class="btn btn-outline-primary" type="button" id="btnActCoverLink"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="cover_image" id="actCoverUrl">
                            <div id="actCoverPreview" class="mt-2 position-relative" style="display:none">
                                <img id="actCoverImg" src="" class="img-fluid rounded" style="max-height:100px" alt="cover">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeCover('activity')" title="ลบรูปปก"><i class="bi bi-x"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="actShowRegs" name="show_registrations" value="1">
                                <label class="custom-control-label" for="actShowRegs">แสดงรายชื่อผู้ลงทะเบียนให้สมาชิกเห็น</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">รายละเอียด</label>
                            <textarea class="form-control" name="description" id="actDesc" rows="8"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveActivity">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrations -->
<div class="modal fade" id="regsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายชื่อผู้ลงทะเบียน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="regsModalBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer justify-content-between">
                <div id="regsAccessCodeSection">
                    <span class="text-muted small me-2"><i class="bi bi-key me-1"></i>รหัสเข้าดู:</span>
                    <code id="regsAccessCode" class="me-2" style="font-size:1.1em;">-</code>
                    <button class="btn btn-outline-primary btn-sm me-1" onclick="generateAccessCode()" title="สร้าง/รีเซ็ตรหัส"><i class="bi bi-arrow-repeat me-1"></i>สร้างรหัส</button>
                    <button class="btn btn-outline-success btn-sm me-1" onclick="copyAccessLink()" title="คัดลอกลิงก์" id="btnCopyLink" style="display:none"><i class="bi bi-link-45deg me-1"></i>คัดลอกลิงก์</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeAccessCode()" title="ลบรหัส" id="btnRemoveCode" style="display:none"><i class="bi bi-x-lg"></i></button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Slip Preview -->
<div class="modal fade" id="slipPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white py-2">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>สลิปการชำระเงิน</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="slipPreviewImg" src="" class="img-fluid rounded" style="max-height:70vh" alt="สลิป">
            </div>
            <div class="modal-footer py-2" id="slipPreviewFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-crop me-2"></i>ครอปรูปภาพ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div style="max-height:60vh;overflow:hidden">
                    <img id="cropperImage" src="" style="max-width:100%;display:block">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnCropConfirm">
                    <i class="bi bi-check-lg me-1"></i> ครอปและอัปโหลด
                </button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
let currentPage = 1;

$(function () {
    App.requireAdmin();

    // Populate member type checkboxes dynamically
    App.loadMemberTypes().then(() => {
        const labels = App._memberTypeLabelsShort || {};
        let cbHtml = '';
        Object.entries(labels).forEach(([k, v]) => {
            cbHtml += `<div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input member-type-cb" id="mt_${k}" value="${k}">
                <label class="custom-control-label" for="mt_${k}">${App.escapeHtml(v)}</label>
            </div>`;
        });
        $('#memberTypeCheckboxes').html(cbHtml);
    });

    loadActivities();
});

async function loadActivities(page = 1) {
    currentPage = page;
    const tbody = $('#activitiesTable');
    tbody.html('<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const params = { page, per_page: 20 };
    const status = $('#filterStatus').val();
    const search = $('#searchActivity').val().trim();
    if (status) params.status = status;
    if (search) params.search = search;

    const result = await API.getActivities(params);
    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted">ไม่พบกิจกรรม</td></tr>');
        return;
    }

    let html = '';
    result.data.forEach((a, i) => {
        const idx = (currentPage - 1) * 20 + i + 1;
        const statusMap = { 'open': '<span class="badge bg-success">เปิดรับ</span>', 'closed': '<span class="badge bg-danger">ปิดรับ</span>', 'draft': '<span class="badge bg-secondary">แบบร่าง</span>', 'cancelled': '<span class="badge bg-dark">ยกเลิก</span>' };
        const statusBadge = statusMap[a.status] || '<span class="badge bg-secondary">' + a.status + '</span>';
        const fee = a.has_fee && a.fee_amount > 0 ? App.formatCurrency(a.fee_amount) : 'ฟรี';
        const spots = a.max_participants > 0 ? `${a.approved_count || 0}/${a.max_participants}` : (a.approved_count || 0) + ' คน';
        const visBadge = a.visibility === 'members_only' ? '<span class="badge bg-warning text-dark"><i class="bi bi-lock me-1"></i>สมาชิก</span>'
            : a.visibility === 'custom' ? '<span class="badge bg-info"><i class="bi bi-shield me-1"></i>กำหนดเอง</span>'
            : '<span class="badge bg-success"><i class="bi bi-globe me-1"></i>สาธารณะ</span>';
        const memberTypeLabels = App._memberTypeLabelsShort || { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
        const mtBadge = a.allowed_member_types
            ? '<br><small class="text-muted">' + a.allowed_member_types.split(',').map(t => memberTypeLabels[t.trim()] || t.trim()).join(', ') + '</small>'
            : '';

        html += `<tr>
            <td>${idx}</td>
            <td>${a.title}</td>
            <td>${App.formatDate(a.start_date)}</td>
            <td>${a.location || '-'}</td>
            <td>${fee}</td>
            <td>
                <a href="#" onclick="viewRegistrations(${a.id});return false;" class="text-decoration-none">${spots}</a>
            </td>
            <td>${visBadge}${mtBadge}</td>
            <td>${statusBadge}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-info" onclick="viewRegistrations(${a.id})" title="ผู้ลงทะเบียน"><i class="bi bi-people"></i></button>
                    <button class="btn btn-outline-primary" onclick="editActivity(${a.id})" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteActivity(${a.id},'${a.title.replace(/'/g, "\\'")}')" title="ลบ"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    tbody.html(html);
    if (result.pagination) App.buildPagination('#activityPagination', result.pagination, loadActivities);
}

function openActivityForm(data = null) {
    const form = $('#activityForm')[0];
    form.reset();
    $('#actId').val('');
    $('#actCoverPreview').hide();
    $('#actCoverUrl').val('');
    // Reset member type checkboxes
    $('.member-type-cb').prop('checked', false);
    $('#actShowRegs').prop('checked', false);

    if (data) {
        $('#actFormTitle').text('แก้ไขกิจกรรม');
        $('#actId').val(data.id);
        $('#actTitle').val(data.title);
        $('#actDesc').val(data.description);
        $('#actLocation').val(data.location);
        $('#actStart').val(data.start_date ? data.start_date.replace(' ', 'T').substring(0, 16) : '');
        $('#actEnd').val(data.end_date ? data.end_date.replace(' ', 'T').substring(0, 16) : '');
        $('#actMax').val(data.max_participants || 0);
        $('#actFee').val(data.fee_amount || 0);
        $('#actFeeDesc').val(data.fee_description || '');
        $('#actStatus').val(data.status);
        $('#actVisibility').val(data.visibility || 'public');
        $('#actVisibilityText').val(data.visibility_text || '');
        $('#actShowRegs').prop('checked', !!parseInt(data.show_registrations));
        toggleVisibilityText();
        // Populate allowed member types
        if (data.allowed_member_types) {
            const types = data.allowed_member_types.split(',').map(t => t.trim());
            types.forEach(t => $(`#mt_${t}`).prop('checked', true));
        }
        if (data.cover_image) {
            $('#actCoverUrl').val(data.cover_image);
            $('#actCoverImg').attr('src', App.imgUrl(data.cover_image));
            $('#actCoverPreview').show();
        }
    } else {
        $('#actFormTitle').text('เพิ่มกิจกรรม');
        toggleVisibilityText();
    }

    $('#activityFormModal').modal('show');
}

async function editActivity(id) {
    const result = await API.getActivityDetail(id);
    if (result.success) openActivityForm(result.data);
    else App.error(result.message);
}

async function deleteActivity(id, title) {
    const ok = await App.confirm(`ต้องการลบกิจกรรม "${title}" หรือไม่?`);
    if (!ok) return;
    const result = await API.deleteActivity(id);
    if (result.success) { App.success('ลบกิจกรรมสำเร็จ'); loadActivities(currentPage); }
    else App.error(result.message);
}

// Upload cover with crop
$('#actCoverFile').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) { App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)'); this.value = ''; return; }
    showCropper(file, 'activities');
});

// Cover image URL
$('#btnActCoverLink').on('click', function () {
    const url = $('#actCoverLinkInput').val().trim();
    if (!url) { App.error('กรุณากรอก URL รูปภาพ'); return; }
    $('#actCoverUrl').val(url);
    $('#actCoverImg').attr('src', App.imgUrl(url));
    $('#actCoverPreview').show();
    App.success('ใส่ลิงก์รูปปกสำเร็จ');
});

function removeCover(type) {
    if (type === 'activity') {
        $('#actCoverUrl').val('');  $('#actCoverFile').val('');  $('#actCoverPreview').hide();
    }
}

// Save activity
$('#btnSaveActivity').on('click', async function () {
    const title = $('#actTitle').val().trim();
    const startDate = $('#actStart').val();
    if (!title) { App.error('กรุณากรอกชื่อกิจกรรม'); return; }
    if (!startDate) { App.error('กรุณาระบุวันเริ่มต้น'); return; }

    const btn = $(this);
    btn.prop('disabled', true);

    const data = {
        title,
        description: $('#actDesc').val(),
        location: $('#actLocation').val(),
        start_date: startDate.replace('T', ' ') + ':00',
        end_date: $('#actEnd').val() ? $('#actEnd').val().replace('T', ' ') + ':00' : null,
        max_participants: parseInt($('#actMax').val()) || 0,
        fee_amount: parseFloat($('#actFee').val()) || 0,
        fee_description: $('#actFeeDesc').val(),
        cover_image: $('#actCoverUrl').val(),
        status: $('#actStatus').val(),
        visibility: $('#actVisibility').val(),
        visibility_text: $('#actVisibilityText').val() || null,
        allowed_member_types: $('.member-type-cb:checked').map(function() { return $(this).val(); }).get().join(',') || null,
        show_registrations: $('#actShowRegs').is(':checked') ? 1 : 0
    };

    const actId = $('#actId').val();
    let result;
    if (actId) {
        data.id = parseInt(actId);
        result = await API.updateActivity(data);
    } else {
        result = await API.createActivity(data);
    }

    if (result.success) {
        $('#activityFormModal').modal('hide');
        App.success(result.message);
        loadActivities(currentPage);
    } else {
        App.error(result.message);
    }
    btn.prop('disabled', false);
});

// View registrations
let currentRegActivityId = null;
let currentRegActivityData = null;

async function viewRegistrations(activityId) {
    currentRegActivityId = activityId;
    $('#regsModal').modal('show');
    const body = $('#regsModalBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');

    // Load activity detail for access code
    const actResult = await API.getActivityDetail(activityId);
    if (actResult.success && actResult.data) {
        currentRegActivityData = actResult.data;
        updateAccessCodeUI(actResult.data.access_code);
    }

    const result = await API.getActivityRegistrations(activityId);
    if (!result.success || !result.data || result.data.length === 0) {
        body.html('<p class="text-center text-muted py-3">ยังไม่มีผู้ลงทะเบียน</p>');
        return;
    }

    let html = `<div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted">ทั้งหมด ${result.data.length} คน</span>
        <button class="btn btn-success btn-sm" onclick="exportRegistrationsExcel()"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</button>
    </div>`;
    html += `<div class="table-responsive"><table class="table table-sm table-hover" id="regsTable">
        <thead><tr><th>#</th><th>ชื่อ-สกุล</th><th>โรงเรียน/หน่วยงาน</th><th>อีเมล</th><th>การชำระเงิน</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody>`;

    result.data.forEach((r, i) => {
        const payBadge = r.payment_status === 'paid' ? '<span class="badge bg-success">ชำระแล้ว</span>'
            : r.payment_status === 'pending' ? '<span class="badge bg-warning text-dark">รอชำระ</span>'
            : r.payment_status === 'rejected' ? '<span class="badge bg-danger">ปฏิเสธ</span>'
            : '<span class="badge bg-secondary">ไม่ต้องชำระ</span>';
        const stBadge = App.getStatusBadge(r.status);
        const slip = r.payment_proof ? `<button class="btn btn-outline-info btn-sm" onclick="previewSlip('${App.escHtml(r.payment_proof)}', ${r.id}, '${r.status}', '${r.payment_status}', ${activityId})" title="ดูสลิป"><i class="bi bi-receipt"></i></button>` : '';

        html += `<tr>
            <td>${i + 1}</td>
            <td>${r.full_name || ''}</td>
            <td>${r.school_organization || '-'}</td>
            <td>${r.email || '-'}</td>
            <td>${payBadge} ${slip}</td>
            <td>${stBadge}</td>
            <td>
                ${r.status === 'pending' ? `
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success" onclick="approveReg(${r.id},'approved','paid',${activityId})" title="อนุมัติ"><i class="bi bi-check-lg"></i></button>
                        <button class="btn btn-outline-danger" onclick="approveReg(${r.id},'rejected',null,${activityId})" title="ปฏิเสธ"><i class="bi bi-x-lg"></i></button>
                    </div>
                ` : '-'}
            </td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    body.html(html);
}

function updateAccessCodeUI(code) {
    if (code) {
        $('#regsAccessCode').text(code);
        $('#btnCopyLink, #btnRemoveCode').show();
    } else {
        $('#regsAccessCode').text('ยังไม่มี');
        $('#btnCopyLink, #btnRemoveCode').hide();
    }
}

async function generateAccessCode() {
    if (!currentRegActivityId) return;
    const result = await API.resetAccessCode(currentRegActivityId);
    if (result.success && result.data) {
        updateAccessCodeUI(result.data.access_code);
        if (currentRegActivityData) currentRegActivityData.access_code = result.data.access_code;
        App.success('สร้างรหัสเข้าดูสำเร็จ: ' + result.data.access_code);
    } else {
        App.error(result.message);
    }
}

async function removeAccessCode() {
    if (!currentRegActivityId) return;
    if (!confirm('ต้องการลบรหัสเข้าดู? ลิงก์สาธารณะจะใช้ไม่ได้')) return;
    const result = await API.removeAccessCode(currentRegActivityId);
    if (result.success) {
        updateAccessCodeUI(null);
        if (currentRegActivityData) currentRegActivityData.access_code = null;
        App.success('ลบรหัสเข้าดูสำเร็จ');
    } else {
        App.error(result.message);
    }
}

function copyAccessLink() {
    if (!currentRegActivityId || !currentRegActivityData || !currentRegActivityData.access_code) return;
    const url = window.location.origin + '/web/?page=activity-participants&id=' + currentRegActivityId + '&code=' + currentRegActivityData.access_code;
    navigator.clipboard.writeText(url).then(() => {
        App.success('คัดลอกลิงก์สำเร็จ');
    }).catch(() => {
        prompt('คัดลอกลิงก์นี้:', url);
    });
}

function exportRegistrationsExcel() {
    const table = document.getElementById('regsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let csv = '\uFEFF'; // BOM for Excel UTF-8
    const actTitle = currentRegActivityData ? currentRegActivityData.title : 'กิจกรรม';

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        cells.forEach((cell, idx) => {
            if (idx === cells.length - 1) return; // Skip last column (จัดการ)
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'รายชื่อผู้ลงทะเบียน-' + actTitle + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

async function approveReg(regId, status, paymentStatus, activityId) {
    const result = await API.approveRegistration(regId, status, paymentStatus);
    if (result.success) {
        App.success(result.message);
        viewRegistrations(activityId);
        loadActivities(currentPage);
    } else {
        App.error(result.message);
    }
}

function previewSlip(url, regId, regStatus, paymentStatus, activityId) {
    const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
    $('#slipPreviewImg').attr('src', src);

    let footerHtml = '<button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>';
    if (regStatus === 'pending') {
        footerHtml = `<button class="btn btn-success btn-sm" onclick="$('#slipPreviewModal').modal('hide');approveReg(${regId},'approved','paid',${activityId})"><i class="bi bi-check-lg me-1"></i>อนุมัติ</button>
            <button class="btn btn-danger btn-sm" onclick="$('#slipPreviewModal').modal('hide');approveReg(${regId},'rejected',null,${activityId})"><i class="bi bi-x-lg me-1"></i>ปฏิเสธ</button>` + footerHtml;
    }
    $('#slipPreviewFooter').html(footerHtml);
    $('#slipPreviewModal').modal('show');
}

$('#filterStatus').on('change', () => loadActivities(1));
let searchTimer;
$('#searchActivity').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadActivities(1), 400);
});

// ─── Visibility Toggle ───
function toggleVisibilityText() {
    if ($('#actVisibility').val() === 'custom') {
        $('#actVisibilityTextWrap').show();
    } else {
        $('#actVisibilityTextWrap').hide();
        $('#actVisibilityText').val('');
    }
}
$('#actVisibility').on('change', toggleVisibilityText);

// ─── Cropper Logic ───
let cropper = null;
let cropFile = null;
let cropTarget = 'activities';

function showCropper(file, target) {
    cropFile = file;
    cropTarget = target;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('cropperImage');
        img.src = e.target.result;
        if (cropper) { cropper.destroy(); cropper = null; }
        $('#cropperModal').modal('show');
        $('#cropperModal').one('shown.bs.modal', function () {
            cropper = new Cropper(img, {
                aspectRatio: 1200 / 630,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                guides: true,
                background: true,
            });
        });
    };
    reader.readAsDataURL(file);
}

$('#btnCropConfirm').on('click', async function () {
    if (!cropper || !cropFile) return;
    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังอัปโหลด...');

    const cropData = cropper.getData(true);
    const formData = new FormData();
    formData.append('file', cropFile);
    formData.append('cropX', cropData.x);
    formData.append('cropY', cropData.y);
    formData.append('cropWidth', cropData.width);
    formData.append('cropHeight', cropData.height);

    const type = cropTarget;
    try {
        const token = API.getToken();
        const headers = {};
        if (token) headers['X-Auth-Token'] = token;
        const response = await fetch(API.baseUrl + API.apiUrl('upload', 'image', { type }), {
            method: 'POST', headers, body: formData
        });
        const result = await response.json();
        if (result.success) {
            $('#actCoverUrl').val(result.data.url);
            $('#actCoverImg').attr('src', App.imgUrl(result.data.url));
            $('#actCoverPreview').show();
            $('#cropperModal').modal('hide');
            App.success(`อัปโหลดสำเร็จ (${result.data.width}x${result.data.height}, ${(result.data.size/1024).toFixed(0)} KB)`);
        } else {
            App.error(result.message);
        }
    } catch (err) {
        App.error('เกิดข้อผิดพลาดในการอัปโหลด');
        console.error(err);
    }
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> ครอปและอัปโหลด');
});

$('#cropperModal').on('hidden.bs.modal', function () {
    if (cropper) { cropper.destroy(); cropper = null; }
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
