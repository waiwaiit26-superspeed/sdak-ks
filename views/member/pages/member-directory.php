<?php $pageTitle = 'ทำเนียบสมาชิก'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-people-fill me-2"></i>ทำเนียบสมาชิก</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/?page=home">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ทำเนียบสมาชิก</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">

            <!-- Summary Badge -->
            <div class="d-flex align-items-center mb-3">
                <span class="badge badge-primary px-3 py-2 mr-2" style="font-size:.9rem;" id="dirTotalBadge">
                    <i class="bi bi-people me-1"></i> สมาชิกทั้งหมด: <strong id="dirTotal">-</strong> คน
                </span>
            </div>

            <!-- Search & Filter -->
            <div class="card shadow-sm mb-3">
                <div class="card-body py-3">
                    <div class="row align-items-end mb-2">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label class="form-label small text-muted mb-1">ค้นหาสมาชิก</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                </div>
                                <input type="text" class="form-control" id="dirSearch"
                                    placeholder="ชื่อ, โรงเรียน, ตำแหน่ง...">
                            </div>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="form-label small text-muted mb-1">ประเภทสมาชิก</label>
                            <select class="form-control" id="dirType">
                                <option value="">ทุกประเภท</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label small text-muted mb-1">แสดงต่อหน้า</label>
                            <select class="form-control" id="dirPerPage">
                                <option value="20">20 รายการ</option>
                                <option value="50" selected>50 รายการ</option>
                                <option value="100">100 รายการ</option>
                                <option value="9999">ทั้งหมด</option>
                            </select>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col">
                            <span class="text-muted small" id="dirResultInfo"></span>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-success btn-sm mr-2" id="btnAddMember" style="display:none;" onclick="openAddMemberModal()">
                                <i class="bi bi-person-plus-fill me-1"></i> เพิ่มสมาชิก
                            </button>
                            <button class="btn btn-outline-success btn-sm" id="btnExportDir" onclick="exportDirectory()">
                                <i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Directory Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="10%" data-sort="member_number" style="cursor:pointer;white-space:nowrap;">รหัส <i class="bi bi-sort-down text-primary sort-icon"></i></th>
                                    <th width="25%" data-sort="full_name" style="cursor:pointer;white-space:nowrap;">ชื่อ-นามสกุล <i class="bi bi-arrow-down-up text-muted sort-icon"></i></th>
                                    <th width="12%" data-sort="member_type" style="cursor:pointer;white-space:nowrap;">ประเภท <i class="bi bi-arrow-down-up text-muted sort-icon"></i></th>
                                    <th width="15%" data-sort="position" style="cursor:pointer;white-space:nowrap;">ตำแหน่ง / วิทยฐานะ <i class="bi bi-arrow-down-up text-muted sort-icon"></i></th>
                                    <th data-sort="school_organization" style="cursor:pointer;white-space:nowrap;">โรงเรียน / หน่วยงาน <i class="bi bi-arrow-down-up text-muted sort-icon"></i></th>
                                    <th id="dirActionHeader" width="9%" style="display:none;"></th>
                                </tr>
                            </thead>
                            <tbody id="dirTableBody">
                                <tr><td colspan="7" class="text-center py-4 text-muted">
                                    <span class="spinner-border spinner-border-sm"></span> กำลังโหลด...
                                </td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div id="dirPagination"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<script>
let canEdit = false;
let canViewFull = false;
let canCreate = false;
let _memberTypesList = [];

$(async function () {
    App.requireLogin();

    const _u = API.getUser();
    const isAdmin = _u?.role === 'admin';

    // Run ALL initialization calls in parallel (settings + types + permissions)
    const initCalls = [API.getSettings(), API.getMemberTypes()];
    if (!isAdmin) initCalls.push(API.getMySubAdminPermissions());

    const [sRes, typesRes, _pRes] = await Promise.all(initCalls);

    // Feature enabled check
    if (sRes.success && sRes.data?.member_directory_enabled === '0') {
        $('#dirTableBody').html('<tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-lock" style="font-size:2rem;"></i><br>ฟีเจอร์นี้ถูกปิดโดยผู้ดูแลระบบ</td></tr>');
        return;
    }

    // Set permission flags
    if (isAdmin) {
        canEdit = true;
        canViewFull = true;
        canCreate = true;
    } else if (_pRes?.success) {
        if (_pRes.data?.areas?.members?.includes('edit')) canEdit = true;
        if (_pRes.data?.areas?.members?.includes('view')) canViewFull = true;
        if (_pRes.data?.areas?.members?.includes('create')) canCreate = true;
    }
    if (canEdit) $('#dirActionHeader').show();
    if (canCreate) $('#btnAddMember').show();

    // Populate member type filter
    if (typesRes.success && typesRes.data) {
        _memberTypesList = typesRes.data;
        let opts = '<option value="">ทุกประเภท</option>';
        typesRes.data.forEach(t => {
            opts += `<option value="${t.type_key}">${App.escapeHtml(t.label)}</option>`;
        });
        $('#dirType').html(opts);
    }

    loadDirectory(1);

    let searchTimer;
    $('#dirSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadDirectory(1), 400);
    });
    $('#dirType, #dirPerPage').on('change', () => loadDirectory(1));

    $(document).on('click', 'th[data-sort]', function () {
        const col = $(this).data('sort');
        if (dirSort.col === col) {
            dirSort.dir = dirSort.dir === 'asc' ? 'desc' : 'asc';
        } else {
            dirSort.col = col;
            dirSort.dir = 'asc';
        }
        loadDirectory(1);
    });

    $(document).on('click', '.dir-edit-btn', function () {
        const $b = $(this);
        openEditModal(
            $b.data('id'),
            $b.data('member-number'),
            $b.data('prefix'),
            $b.data('fullname'),
            $b.data('position'),
            $b.data('rank')
        );
    });

    $(document).on('click', '.dir-reset-btn', function () {
        const $b = $(this);
        openResetPasswordModal($b.data('id'), $b.data('name'));
    });
});


async function toggleConfirmNumber(el) {
    const userId = $(el).data('id');
    $(el).css('pointer-events', 'none').find('i').css('opacity', '0.4');

    const res = await API.directoryConfirmNumber({ user_id: userId });

    // Re-query element after await to ensure fresh reference
    const $badge = $(`.dir-confirm-badge[data-id="${userId}"]`);
    $badge.css('pointer-events', '').find('i').css('opacity', '');

    if (!res.success) { App.error(res.message || 'เกิดข้อผิดพลาด'); return; }

    const newConfirmed = res.data.member_number_confirmed ? 1 : 0;
    $badge.data('confirmed', newConfirmed)
          .attr('title', newConfirmed ? 'ยืนยันเลขสมาชิกแล้ว' : 'ยังไม่ยืนยันเลขสมาชิก');

    $badge.find('i')
        .removeClass('bi-check-circle-fill bi-circle text-success text-secondary')
        .addClass(newConfirmed ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary');

    App.toast(res.message, newConfirmed ? 'success' : 'info');
}

async function exportDirectory() {
    const btn = $('#btnExportDir');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่งออก...');

    const params = {};
    const type   = $('#dirType').val();
    const search = $('#dirSearch').val().trim();
    if (type)   params.member_type = type;
    if (search) params.search      = search;

    const result = await API.exportMemberDirectory(params);
    btn.prop('disabled', false).html('<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel');

    if (!result.success) { App.error(result.message); return; }

    const { members, exported_at, exported_by } = result.data;
    const esc = (s) => `"${String(s || '').replace(/"/g, '""')}"`;
    const typeLabel = (key) => {
        const opt = $(`#dirType option[value="${key}"]`);
        return opt.length ? opt.text() : (key || '');
    };

    let csv = '\uFEFF';
    csv += 'ทำเนียบสมาชิก\n';
    csv += `ส่งออกเมื่อ: ${exported_at}\n`;
    csv += `ส่งออกโดย: ${exported_by}\n\n`;
    csv += 'ลำดับ,รหัสสมาชิก,ชื่อ-นามสกุล,ประเภทสมาชิก,วิทยฐานะ,ตำแหน่ง,โรงเรียน / หน่วยงาน\n';
    members.forEach((m, i) => {
        csv += [
            i + 1,
            esc(m.member_number),
            esc(m.full_name),
            esc(typeLabel(m.member_type)),
            esc(m.academic_rank),
            esc(m.position),
            esc(m.school_organization),
        ].join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `ทำเนียบสมาชิก_${new Date().toISOString().slice(0, 10)}.csv`;
    link.click();
    URL.revokeObjectURL(link.href);
    App.success(`ส่งออก ${members.length} รายการสำเร็จ`);
}

let dirSort = { col: 'member_number', dir: 'desc' };

async function loadDirectory(page = 1) {
    const search  = $('#dirSearch').val().trim();
    const type    = $('#dirType').val();
    const perPage = parseInt($('#dirPerPage').val()) || 50;
    const params  = { page, per_page: perPage };
    if (search)       params.search      = search;
    if (type)         params.member_type = type;
    if (dirSort.col)  { params.order_by = dirSort.col; params.order_dir = dirSort.dir; }

    $('#dirTableBody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const res = await API.getMemberDirectory(params);
    if (!res.success) {
        $('#dirTableBody').html(`<tr><td colspan="7" class="text-center text-danger py-4">${App.escapeHtml(res.message || 'โหลดข้อมูลล้มเหลว')}</td></tr>`);
        return;
    }

    const data = res.data || [];
    const pagination = res.pagination || {};
    const total = pagination.total || 0;

    $('#dirTotal').text(total);
    const page_ = pagination.current_page || 1;
    const start = (page_ - 1) * perPage + 1;
    const end   = Math.min(page_ * perPage, total);
    $('#dirResultInfo').text(total > 0 ? `แสดง ${start}–${end} จาก ${total} รายการ` : '');

    if (!data.length) {
        $('#dirTableBody').html('<tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-person-x" style="font-size:2rem;"></i><br>ไม่พบสมาชิก</td></tr>');
        $('#dirPagination').empty();
        return;
    }

    const startNum = (page_ - 1) * perPage;
    let html = '';
    data.forEach((m, i) => {
        const rank    = m.academic_rank ? `<br><small class="text-muted">${App.escapeHtml(m.academic_rank)}</small>` : '';
        const pos     = m.position ? App.escapeHtml(m.position) : '<span class="text-muted">-</span>';
        const school  = m.school_organization ? App.escapeHtml(m.school_organization) : '<span class="text-muted">-</span>';
        const memNum  = m.member_number ? `<span class="badge badge-light border">${App.escapeHtml(m.member_number)}</span>` : '<span class="text-muted small">-</span>';
        const confirmed = m.member_number_confirmed ? 1 : 0;
        const confirmIconClass = confirmed ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary';
        const confirmTitle     = confirmed ? 'ยืนยันเลขสมาชิกแล้ว' : 'ยังไม่ยืนยันเลขสมาชิก';
        const confirmEl = canEdit
            ? `<span class="dir-confirm-badge" data-id="${m.id}" data-confirmed="${confirmed}" style="cursor:pointer;" title="${confirmTitle}" onclick="toggleConfirmNumber(this)"><i class="bi ${confirmIconClass}" style="font-size:.8rem;"></i></span>`
            : `<span title="${confirmTitle}"><i class="bi ${confirmIconClass}" style="font-size:.8rem;"></i></span>`;
        const nameWithoutPrefix = (m.prefix && m.full_name && m.full_name.startsWith(m.prefix))
            ? m.full_name.slice(m.prefix.length).trim()
            : (m.full_name || '');
        const editBtn = canEdit
            ? `<button class="btn btn-xs btn-outline-secondary dir-edit-btn" title="แก้ไข"
                    data-id="${m.id}"
                    data-member-number="${parseInt(m.member_number_raw) || ''}"
                    data-prefix="${App.escapeHtml(m.prefix || '')}"
                    data-fullname="${App.escapeHtml(nameWithoutPrefix)}"
                    data-position="${App.escapeHtml(m.position || '')}"
                    data-rank="${App.escapeHtml(m.academic_rank || '')}">
                    <i class="bi bi-pencil"></i></button>
               <button class="btn btn-xs btn-outline-warning dir-reset-btn" title="รีเซ็ตรหัสผ่าน"
                    data-id="${m.id}" data-name="${App.escapeHtml((m.prefix || '') + nameWithoutPrefix)}">
                    <i class="bi bi-key"></i></button>`
            : '';

        const avatar = `<img src="${App.getProfileImage(m)}" class="rounded-circle mr-2" width="34" height="34" style="object-fit:cover;flex-shrink:0;" alt="">`;
        html += `<tr data-member-id="${m.id}" data-prefix="${App.escapeHtml(m.prefix || '')}">
            <td>${startNum + i + 1}</td>
            <td class="dir-num-cell"><div class="d-flex align-items-center gap-1">${memNum} ${confirmEl}</div></td>
            <td class="dir-name-cell" style="cursor:pointer;" onclick="viewDirMember(${m.id})">
                <div class="d-flex align-items-center">${avatar}<strong class="text-primary">${App.escapeHtml((m.prefix || '') + nameWithoutPrefix)}</strong></div>
            </td>
            <td>${App.getMemberTypeBadge(m.member_type)}</td>
            <td class="dir-pos-cell">${pos}${rank}</td>
            <td>${school}</td>
            <td>${editBtn}</td>
        </tr>`;
    });

    $('#dirTableBody').html(html);

    // Update sort icons
    $('th[data-sort] .sort-icon').attr('class', 'bi bi-arrow-down-up text-muted sort-icon');
    if (dirSort.col) {
        $(`th[data-sort="${dirSort.col}"] .sort-icon`).attr('class',
            `bi ${dirSort.dir === 'asc' ? 'bi-sort-down-alt' : 'bi-sort-down'} text-primary sort-icon`
        );
    }

    if (pagination) {
        App.buildPagination('#dirPagination', pagination, loadDirectory);
    }
}
</script>

<!-- View Member Modal -->
<div class="modal fade" id="dirMemberViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ข้อมูลสมาชิก</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="dirMemberViewBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Member Modal -->
<div class="modal fade" id="modalDirEdit" tabindex="-1" role="dialog" aria-labelledby="modalDirEditLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDirEditLabel"><i class="bi bi-pencil-square me-1"></i> แก้ไขข้อมูลสมาชิก</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">รหัสสมาชิก</label>
                    <input type="number" min="1" class="form-control" id="dirEditMemberNumber"
                        placeholder="เช่น 1, 50, 100">
                    <small class="text-muted">ใส่ตัวเลขเท่านั้น เช่น <code>1</code> หรือ <code>50</code> — ระบบจะจัดรูปแบบแสดงผลตามการตั้งค่าอัตโนมัติ</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">คำนำหน้า</label>
                        <select class="form-control" id="dirEditPrefix">
                            <option value="">— ไม่ระบุ —</option>
                            <option value="นาย">นาย</option>
                            <option value="นาง">นาง</option>
                            <option value="นางสาว">นางสาว</option>
                            <option value="ดร.">ดร.</option>
                            <option value="ผศ.ดร.">ผศ.ดร.</option>
                            <option value="รศ.ดร.">รศ.ดร.</option>
                            <option value="ศ.ดร.">ศ.ดร.</option>
                            <option value="ผศ.">ผศ.</option>
                            <option value="รศ.">รศ.</option>
                            <option value="ศ.">ศ.</option>
                            <option value="พันตำรวจเอก">พันตำรวจเอก</option>
                            <option value="พันตำรวจโท">พันตำรวจโท</option>
                            <option value="พันตำรวจตรี">พันตำรวจตรี</option>
                            <option value="ว่าที่ร้อยตรี">ว่าที่ร้อยตรี</option>
                            <option value="other">อื่นๆ (กรอกเอง)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">ชื่อ-นามสกุล <small class="text-muted font-weight-normal">(ไม่รวมคำนำหน้า)</small></label>
                        <input type="text" class="form-control" id="dirEditFullName" placeholder="ชื่อ-นามสกุล">
                    </div>
                </div>
                <div id="dirEditPrefixOtherWrap" style="display:none;" class="form-group">
                    <label class="font-weight-bold">คำนำหน้า (ระบุเอง)</label>
                    <input type="text" class="form-control" id="dirEditPrefixOther" placeholder="เช่น พ.ต.อ., ดเ็กชาย">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">ตำแหน่ง</label>
                        <select class="form-control" id="dirEditPosition">
                            <option value="">— ไม่ระบุ —</option>
                            <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                            <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                            <option value="other">อื่นๆ (กรอกเอง)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="dirEditAcademicRankWrap" style="display:none;">
                        <label class="font-weight-bold">วิทยฐานะ</label>
                        <select class="form-control" id="dirEditAcademicRank">
                            <option value="">— ไม่ระบุ —</option>
                        </select>
                    </div>
                </div>
                <div class="form-row" id="dirEditPositionOtherRow" style="display:none;">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">ระบุตำแหน่ง</label>
                        <input type="text" class="form-control" id="dirEditPositionOther" placeholder="เช่น ครู, ผู้ช่วยผู้อำนวยการโรงเรียน">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">วิทยฐานะ <small class="text-muted font-weight-normal">(ถ้ามี)</small></label>
                        <input type="text" class="form-control" id="dirEditAcademicRankOther" placeholder="เช่น ครูชำนาญการพิเศษ">
                    </div>
                </div>
                <!-- Summary preview -->
                <div class="callout callout-info py-2 px-3 mb-0" id="dirEditSummaryBox">
                    <small class="text-muted d-block mb-1">ตัวอย่างการแสดงผล</small>
                    <div id="dirEditSummary" class="small"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveDirEdit" onclick="saveDirectoryEdit()">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let _dirEditUserId = null;

// รายการวิทยฐานะตามตำแหน่ง (ตรงกับหน้า profile)
const _dirEditRankOpts = {
    'ผู้อำนวยการสถานศึกษา': ['ผู้อำนวยการชำนาญการ', 'ผู้อำนวยการชำนาญการพิเศษ', 'ผู้อำนวยการเชี่ยวชาญ'],
    'รองผู้อำนวยการสถานศึกษา': ['รองผู้อำนวยการชำนาญการ', 'รองผู้อำนวยการชำนาญการพิเศษ', 'รองผู้อำนวยการเชี่ยวชาญ'],
};

function _updateDirEditRankDropdown(position, selectedRank) {
    const opts = _dirEditRankOpts[position];
    if (opts) {
        $('#dirEditAcademicRank').html(
            '<option value="">— ไม่ระบุ —</option>' +
            opts.map(o => `<option value="${o}"${o === selectedRank ? ' selected' : ''}>${o}</option>`).join('')
        );
        $('#dirEditAcademicRankWrap').slideDown(150);
    } else {
        $('#dirEditAcademicRank').html('<option value="">— ไม่ระบุ —</option>');
        $('#dirEditAcademicRankWrap').slideUp(150);
    }
}

function _getEditPrefix() {
    const sel = $('#dirEditPrefix').val();
    return sel === 'other' ? $('#dirEditPrefixOther').val().trim() : (sel || '');
}

function _getEditPosition() {
    const sel = $('#dirEditPosition').val();
    return sel === 'other' ? $('#dirEditPositionOther').val().trim() : (sel || '');
}

function _getEditAcademicRank() {
    return $('#dirEditPosition').val() === 'other'
        ? $('#dirEditAcademicRankOther').val().trim()
        : ($('#dirEditAcademicRank').val() || '');
}

function updateDirEditSummary() {
    const prefix   = _getEditPrefix();
    const fullName = $('#dirEditFullName').val().trim();
    const position = _getEditPosition();
    const rank     = _getEditAcademicRank();
    const namePart = App.escapeHtml((prefix || '') + (fullName || ''));
    const posParts = [position, rank].filter(Boolean).map(App.escapeHtml).join(' &nbsp;/&nbsp; ');
    let html = namePart
        ? `<i class="bi bi-person me-1 text-primary"></i><strong>${namePart}</strong>`
        : '<span class="text-muted">&mdash;</span>';
    if (posParts) html += `&nbsp;&nbsp;<i class="bi bi-briefcase me-1 text-muted"></i><span class="text-muted">${posParts}</span>`;
    $('#dirEditSummary').html(html);
}

$('#dirEditPrefix, #dirEditFullName, #dirEditPrefixOther, #dirEditPosition, #dirEditAcademicRank, #dirEditPositionOther, #dirEditAcademicRankOther').on('input change', updateDirEditSummary);

$('#dirEditPrefix').on('change', function () {
    if ($(this).val() === 'other') {
        $('#dirEditPrefixOtherWrap').slideDown(150);
        $('#dirEditPrefixOther').focus();
    } else {
        $('#dirEditPrefixOtherWrap').slideUp(150);
        $('#dirEditPrefixOther').val('');
    }
});

$('#dirEditPosition').on('change', function () {
    const pos = $(this).val();
    if (pos === 'other') {
        $('#dirEditPositionOtherRow').slideDown(150);
        $('#dirEditAcademicRankWrap').slideUp(150);
        $('#dirEditPositionOther').focus();
    } else {
        $('#dirEditPositionOtherRow').slideUp(150);
        $('#dirEditPositionOther').val('');
        $('#dirEditAcademicRankOther').val('');
        _updateDirEditRankDropdown(pos, '');
    }
    updateDirEditSummary();
});

function openEditModal(userId, memberNumber, prefix, fullName, position, academicRank) {
    _dirEditUserId = userId;
    $('#dirEditMemberNumber').val(memberNumber);
    // Prefix dropdown
    if (prefix && !$('#dirEditPrefix option[value="' + prefix + '"]').length) {
        $('#dirEditPrefix').val('other');
        $('#dirEditPrefixOther').val(prefix);
        $('#dirEditPrefixOtherWrap').show();
    } else {
        $('#dirEditPrefix').val(prefix || '');
        $('#dirEditPrefixOtherWrap').hide();
        $('#dirEditPrefixOther').val('');
    }
    $('#dirEditFullName').val(fullName || '');
    // Position dropdown
    const posInList = position in _dirEditRankOpts;
    if (posInList) {
        $('#dirEditPosition').val(position);
        $('#dirEditPositionOtherRow').hide();
        $('#dirEditPositionOther').val('');
        $('#dirEditAcademicRankOther').val('');
        _updateDirEditRankDropdown(position, academicRank || '');
    } else if (position) {
        $('#dirEditPosition').val('other');
        $('#dirEditPositionOther').val(position);
        $('#dirEditAcademicRankOther').val(academicRank || '');
        $('#dirEditPositionOtherRow').show();
        $('#dirEditAcademicRankWrap').hide();
    } else {
        $('#dirEditPosition').val('');
        $('#dirEditPositionOtherRow').hide();
        $('#dirEditPositionOther').val('');
        $('#dirEditAcademicRankOther').val('');
        _updateDirEditRankDropdown('', '');
    }
    $('#btnSaveDirEdit').prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> บันทึก');
    updateDirEditSummary();
    $('#modalDirEdit').modal('show');
}

async function saveDirectoryEdit() {
    if (!_dirEditUserId) return;
    const memberNumber = $('#dirEditMemberNumber').val().trim();
    const prefixVal    = _getEditPrefix();
    const fullName     = $('#dirEditFullName').val().trim();
    const position     = _getEditPosition();
    const academicRank = _getEditAcademicRank();
    if (!fullName) { App.error('กรุณาระบุชื่อ-นามสกุล'); return; }

    const btn = $('#btnSaveDirEdit');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

    const res = await API.directoryEdit({
        user_id:       _dirEditUserId,
        member_number: memberNumber,
        prefix:        prefixVal,
        full_name:     fullName,
        position:      position,
        academic_rank: academicRank,
    });

    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> บันทึก');
    if (!res.success) { App.error(res.message || 'บันทึกล้มเหลว'); return; }

    App.success('อัปเดตข้อมูลสำเร็จ');
    $('#modalDirEdit').modal('hide');

    // In-place row update
    const row = $(`tr[data-member-id="${_dirEditUserId}"]`);
    if (row.length) {
        const $editBtn = row.find('.dir-edit-btn');
        // member_number — preserve confirm badge when rebuilding the cell
        if (res.data.member_number_display !== undefined) {
            const disp = res.data.member_number_display || '';
            const $numCell = row.find('.dir-num-cell');
            const confirmHtml = $numCell.find('span[title]').prop('outerHTML') || '';
            const memNumHtml = disp
                ? `<span class="badge badge-light border">${App.escapeHtml(disp)}</span>`
                : '<span class="text-muted small">-</span>';
            $numCell.html(`<div class="d-flex align-items-center gap-1">${memNumHtml} ${confirmHtml}</div>`);
            $editBtn.data('member-number', parseInt(res.data.member_number_raw) || '');
        }
        // prefix + full_name
        if (res.data.prefix !== undefined || res.data.full_name !== undefined) {
            const newPrefix   = res.data.prefix    !== undefined ? (res.data.prefix    || '') : (row.data('prefix') || '');
            const newFullName = res.data.full_name !== undefined ? (res.data.full_name || '') : $editBtn.data('fullname');
            row.data('prefix', newPrefix);
            row.find('.dir-name-cell strong').text(newPrefix + newFullName);
            $editBtn.data('prefix', newPrefix).data('fullname', newFullName);
        }
        // position / academic_rank
        if (res.data.position !== undefined || res.data.academic_rank !== undefined) {
            const newPos  = res.data.position      !== undefined ? (res.data.position      || '') : ($editBtn.data('position') || '');
            const newRank = res.data.academic_rank !== undefined ? (res.data.academic_rank || '') : ($editBtn.data('rank')     || '');
            $editBtn.data('position', newPos).data('rank', newRank);
            const posHtml  = newPos  ? App.escapeHtml(newPos)  : '<span class="text-muted">-</span>';
            const rankHtml = newRank ? `<br><small class="text-muted">${App.escapeHtml(newRank)}</small>` : '';
            row.find('.dir-pos-cell').html(posHtml + rankHtml);
        }
    }
    _dirEditUserId = null;
}

async function viewDirMember(id) {
    $('#dirMemberViewModal').modal('show');
    const body = $('#dirMemberViewBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');

    const result = await API.getProfile(id);
    if (!result.success) {
        body.html('<p class="text-danger text-center py-3">ไม่สามารถโหลดข้อมูลได้</p>');
        return;
    }
    const u = result.data;
    const displayName = (u.prefix || '') + (u.first_name && u.last_name ? u.first_name + ' ' + u.last_name : u.full_name);

    if (canViewFull) {
        // === Full view for admin / sub-admin with view permission ===
        let ha = u.home_address || {};
        if (typeof ha === 'string') { try { ha = JSON.parse(ha); } catch(e) { ha = {}; } }
        let wa = u.work_address || {};
        if (typeof wa === 'string') { try { wa = JSON.parse(wa); } catch(e) { wa = {}; } }
        const formatAddr = (a) => {
            if (!a || !Object.values(a).some(v => v)) return '-';
            return [a.no ? 'เลขที่ ' + a.no : '', a.soi && a.soi !== '-' ? 'ซอย ' + a.soi : '',
                    a.moo ? 'หมู่ ' + a.moo : '', a.road && a.road !== '-' ? 'ถ.' + a.road : '',
                    a.subdistrict ? 'ต.' + a.subdistrict : '', a.district ? 'อ.' + a.district : '',
                    a.province ? 'จ.' + a.province : '', a.postal_code || ''].filter(Boolean).join(' ');
        };
        body.html(
            '<div class="row">' +
                '<div class="col-md-4 text-center mb-3">' +
                    '<img src="' + App.getProfileImage(u) + '" class="rounded-circle mb-2" width="100" height="100" style="object-fit:cover">' +
                    '<h5>' + App.escapeHtml(displayName) + '</h5>' +
                    App.getRoleBadge(u.role) + ' ' + (u.member_type ? App.getMemberTypeBadge(u.member_type) : '') + ' ' + App.getStatusBadge(u.status) +
                '</div>' +
                '<div class="col-md-8">' +
                    '<table class="table table-sm table-bordered mb-2">' +
                        '<tr><th class="bg-light" colspan="4">ข้อมูลทั่วไป</th></tr>' +
                        '<tr><td class="text-muted" width="130">เลขสมาชิก</td><td><strong class="text-primary">' + (u.member_number || '<span class="text-muted">ยังไม่กำหนด</span>') + '</strong></td><td class="text-muted" width="130">อีเมล</td><td>' + (u.email || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">ชื่อผู้ใช้</td><td>' + (u.username || '-') + '</td><td class="text-muted">เลขบัตรประชาชน</td><td>' + (u.national_id || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">วันเกิด</td><td>' + (u.birth_date ? App.formatDate(u.birth_date) : '-') + '</td><td class="text-muted">มือถือ</td><td>' + (u.phone || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">ตำแหน่ง</td><td>' + (u.position || '-') + '</td><td class="text-muted">วิทยฐานะ</td><td>' + (u.academic_rank || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">โรงเรียน</td><td colspan="3">' + (u.school_organization || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">โทรศัพท์ (ร.ร.)</td><td>' + (u.work_phone || '-') + '</td><td class="text-muted">สังกัด</td><td>' + (u.education_area || '-') + ' ' + (u.region || '') + '</td></tr>' +
                        '<tr><th class="bg-light" colspan="4">ที่อยู่ปัจจุบัน</th></tr>' +
                        '<tr><td class="text-muted">ที่อยู่</td><td colspan="3">' + formatAddr(ha) + '</td></tr>' +
                        '<tr><th class="bg-light" colspan="4">ที่อยู่สถานที่ทำงาน</th></tr>' +
                        '<tr><td class="text-muted">ที่อยู่</td><td colspan="3">' + formatAddr(wa) + '</td></tr>' +
                        '<tr><td class="text-muted">วันที่สมัคร</td><td colspan="3">' + App.formatDateTime(u.created_at) + '</td></tr>' +
                        '<tr><td class="text-muted">วันเริ่มเป็นสมาชิก</td><td colspan="3">' + (u.approved_at ? App.formatDateTime(u.approved_at) : '<span class="text-muted">รออนุมัติ</span>') + '</td></tr>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );
    } else {
        // === Limited view for regular members (public info only) ===
        body.html(
            '<div class="row">' +
                '<div class="col-md-4 text-center mb-3">' +
                    '<img src="' + App.getProfileImage(u) + '" class="rounded-circle mb-3" width="110" height="110" style="object-fit:cover;border:3px solid #dee2e6;">' +
                    '<h5 class="mb-1">' + App.escapeHtml(displayName) + '</h5>' +
                    '<div class="mt-1">' + (u.member_type ? App.getMemberTypeBadge(u.member_type) : '') + '</div>' +
                '</div>' +
                '<div class="col-md-8">' +
                    '<table class="table table-sm table-bordered mb-2">' +
                        '<tr><th class="bg-light" colspan="4">ข้อมูลสมาชิก</th></tr>' +
                        '<tr><td class="text-muted" width="140">เลขสมาชิก</td><td colspan="3"><strong class="text-primary">' + (u.member_number || '<span class="text-muted">-</span>') + '</strong></td></tr>' +
                        '<tr><td class="text-muted">ตำแหน่ง</td><td>' + (u.position || '-') + '</td><td class="text-muted">วิทยฐานะ</td><td>' + (u.academic_rank || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">โรงเรียน / หน่วยงาน</td><td colspan="3">' + (u.school_organization || '-') + '</td></tr>' +
                        '<tr><td class="text-muted">โทรศัพท์ (ร.ร.)</td><td>' + (u.work_phone || '-') + '</td><td class="text-muted">สังกัด</td><td>' + ((u.education_area || '') + ' ' + (u.region || '')).trim() + '</td></tr>' +
                        '<tr><td class="text-muted">วันเริ่มเป็นสมาชิก</td><td colspan="3">' + (u.approved_at ? App.formatDate(u.approved_at) : '-') + '</td></tr>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );
    }
}
</script>

<!-- Add Member Modal -->
<div class="modal fade" id="modalDirAdd" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-1"></i> เพิ่มสมาชิกใหม่</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">รหัสสมาชิก</label>
                    <input type="number" min="1" class="form-control" id="dirAddMemberNumber" placeholder="เช่น 1, 50, 100">
                    <small class="text-muted">ใส่ตัวเลขเท่านั้น — ระบบจัดรูปแบบตามการตั้งค่าอัตโนมัติ</small>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">ประเภทสมาชิก <span class="text-danger">*</span></label>
                    <select class="form-control" id="dirAddMemberType"></select>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="font-weight-bold">คำนำหน้า</label>
                        <select class="form-control" id="dirAddPrefix">
                            <option value="">— ไม่ระบุ —</option>
                            <option value="นาย">นาย</option>
                            <option value="นาง">นาง</option>
                            <option value="นางสาว">นางสาว</option>
                            <option value="ดร.">ดร.</option>
                            <option value="ผศ.ดร.">ผศ.ดร.</option>
                            <option value="รศ.ดร.">รศ.ดร.</option>
                            <option value="ศ.ดร.">ศ.ดร.</option>
                            <option value="ผศ.">ผศ.</option>
                            <option value="รศ.">รศ.</option>
                            <option value="ศ.">ศ.</option>
                            <option value="พันตำรวจเอก">พันตำรวจเอก</option>
                            <option value="พันตำรวจโท">พันตำรวจโท</option>
                            <option value="พันตำรวจตรี">พันตำรวจตรี</option>
                            <option value="ว่าที่ร้อยตรี">ว่าที่ร้อยตรี</option>
                            <option value="other">อื่นๆ (กรอกเอง)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-8">
                        <label class="font-weight-bold">ชื่อ-นามสกุล <small class="text-muted font-weight-normal">(ไม่รวมคำนำหน้า)</small> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="dirAddFullName" placeholder="ชื่อ-นามสกุล">
                    </div>
                </div>
                <div id="dirAddPrefixOtherWrap" style="display:none;" class="form-group">
                    <label class="font-weight-bold">คำนำหน้า (ระบุเอง)</label>
                    <input type="text" class="form-control" id="dirAddPrefixOther" placeholder="เช่น พ.ต.อ., เด็กชาย">
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">ตำแหน่ง</label>
                        <select class="form-control" id="dirAddPosition">
                            <option value="">— ไม่ระบุ —</option>
                            <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                            <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                            <option value="other">อื่นๆ (กรอกเอง)</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6" id="dirAddAcademicRankWrap" style="display:none;">
                        <label class="font-weight-bold">วิทยฐานะ</label>
                        <select class="form-control" id="dirAddAcademicRank">
                            <option value="">— ไม่ระบุ —</option>
                        </select>
                    </div>
                </div>
                <div class="form-row" id="dirAddPositionOtherRow" style="display:none;">
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">ระบุตำแหน่ง</label>
                        <input type="text" class="form-control" id="dirAddPositionOther" placeholder="เช่น ครู, ผู้ช่วยผู้อำนวยการ">
                    </div>
                    <div class="form-group col-md-6">
                        <label class="font-weight-bold">วิทยฐานะ <small class="text-muted font-weight-normal">(ถ้ามี)</small></label>
                        <input type="text" class="form-control" id="dirAddAcademicRankOther" placeholder="เช่น ครูชำนาญการพิเศษ">
                    </div>
                </div>
                <div class="callout callout-info py-2 px-3 mb-0">
                    <small class="text-muted"><i class="bi bi-key me-1"></i>Username และ Password จะถูกสร้างอัตโนมัติ และแสดงให้ครั้งเดียวหลังบันทึก</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success" id="btnSaveDirAdd" onclick="saveAddMember()">
                    <i class="bi bi-person-plus-fill me-1"></i> เพิ่มสมาชิก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="modalDirResetPwd" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key me-1"></i> รีเซ็ตรหัสผ่าน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="resetPwdInfo" class="alert alert-warning mb-3"></div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold">รหัสผ่านใหม่ <small class="text-muted font-weight-normal">(เว้นว่างเพื่อสุ่มอัตโนมัติ)</small></label>
                    <input type="text" class="form-control" id="resetPwdInput" placeholder="ปล่อยว่างเพื่อสุ่มรหัสผ่าน">
                    <small class="text-muted">อย่างน้อย 6 ตัวอักษร หากระบุ</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-warning" id="btnDoResetPwd" onclick="doResetPassword()">
                    <i class="bi bi-key me-1"></i> รีเซ็ต
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Credentials Result Modal (shown after add / reset) -->
<div class="modal fade" id="modalDirCredResult" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="credResultTitle">ข้อมูลเข้าสู่ระบบ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="credResultBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Add Member ──────────────────────────────────────────────────────────
function openAddMemberModal() {
    if (_memberTypesList.length) {
        let opts = '';
        _memberTypesList.forEach(t => {
            opts += `<option value="${t.type_key}">${App.escapeHtml(t.label)}</option>`;
        });
        $('#dirAddMemberType').html(opts);
    }
    $('#dirAddMemberNumber, #dirAddFullName, #dirAddPrefixOther, #dirAddPositionOther, #dirAddAcademicRankOther').val('');
    $('#dirAddPrefix, #dirAddPosition').val('');
    $('#dirAddPrefixOtherWrap, #dirAddPositionOtherRow, #dirAddAcademicRankWrap').hide();
    $('#btnSaveDirAdd').prop('disabled', false).html('<i class="bi bi-person-plus-fill me-1"></i> เพิ่มสมาชิก');
    $('#modalDirAdd').modal('show');
}

$('#dirAddPrefix').on('change', function () {
    if ($(this).val() === 'other') {
        $('#dirAddPrefixOtherWrap').slideDown(150);
        $('#dirAddPrefixOther').focus();
    } else {
        $('#dirAddPrefixOtherWrap').slideUp(150);
        $('#dirAddPrefixOther').val('');
    }
});

$('#dirAddPosition').on('change', function () {
    const pos = $(this).val();
    if (pos === 'other') {
        $('#dirAddPositionOtherRow').slideDown(150);
        $('#dirAddAcademicRankWrap').slideUp(150);
        $('#dirAddPositionOther').focus();
    } else {
        $('#dirAddPositionOtherRow').slideUp(150);
        $('#dirAddPositionOther').val('');
        $('#dirAddAcademicRankOther').val('');
        _updateAddRankDropdown(pos, '');
    }
});

function _updateAddRankDropdown(position, selectedRank) {
    const opts = _dirEditRankOpts[position];
    if (opts) {
        $('#dirAddAcademicRank').html(
            '<option value="">— ไม่ระบุ —</option>' +
            opts.map(o => `<option value="${o}"${o === selectedRank ? ' selected' : ''}>${o}</option>`).join('')
        );
        $('#dirAddAcademicRankWrap').slideDown(150);
    } else {
        $('#dirAddAcademicRank').html('<option value="">— ไม่ระบุ —</option>');
        $('#dirAddAcademicRankWrap').slideUp(150);
    }
}

function _getAddPrefix() {
    const sel = $('#dirAddPrefix').val();
    return sel === 'other' ? $('#dirAddPrefixOther').val().trim() : (sel || '');
}
function _getAddPosition() {
    const sel = $('#dirAddPosition').val();
    return sel === 'other' ? $('#dirAddPositionOther').val().trim() : (sel || '');
}
function _getAddAcademicRank() {
    return $('#dirAddPosition').val() === 'other'
        ? $('#dirAddAcademicRankOther').val().trim()
        : ($('#dirAddAcademicRank').val() || '');
}

async function saveAddMember() {
    const memberNumber = $('#dirAddMemberNumber').val().trim();
    const memberType   = $('#dirAddMemberType').val();
    const prefix       = _getAddPrefix();
    const firstName    = $('#dirAddFullName').val().trim();
    const position     = _getAddPosition();
    const academicRank = _getAddAcademicRank();

    if (!firstName) { App.error('กรุณาระบุชื่อ-นามสกุล'); return; }
    if (!memberType) { App.error('กรุณาเลือกประเภทสมาชิก'); return; }

    const btn = $('#btnSaveDirAdd');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

    const res = await API.createMember({
        member_number: memberNumber,
        member_type:   memberType,
        prefix:        prefix,
        first_name:    firstName,
        last_name:     '',
        position:      position,
        academic_rank: academicRank,
    });

    btn.prop('disabled', false).html('<i class="bi bi-person-plus-fill me-1"></i> เพิ่มสมาชิก');
    if (!res.success) { App.error(res.message || 'เพิ่มสมาชิกล้มเหลว'); return; }

    $('#modalDirAdd').modal('hide');
    loadDirectory(1);

    const u = res.data?.username || '';
    const p = res.data?.password_plain || '';
    const displayName = prefix + firstName;
    $('#credResultTitle').html('<i class="bi bi-person-check-fill me-1 text-success"></i> เพิ่มสมาชิกสำเร็จ');
    showCredModal(displayName, u, p);
}

// ── Reset Password ──────────────────────────────────────────────────────
let _resetPwdUserId = null;

function openResetPasswordModal(userId, memberName) {
    _resetPwdUserId = userId;
    $('#resetPwdInfo').html('<i class="bi bi-person me-1"></i> รีเซ็ตรหัสผ่านของ <strong>' + App.escapeHtml(memberName) + '</strong>');
    $('#resetPwdInput').val('');
    $('#btnDoResetPwd').prop('disabled', false).html('<i class="bi bi-key me-1"></i> รีเซ็ต');
    $('#modalDirResetPwd').modal('show');
}

async function doResetPassword() {
    if (!_resetPwdUserId) return;
    const pwd = $('#resetPwdInput').val().trim();
    if (pwd && pwd.length < 6) { App.error('รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'); return; }

    const btn = $('#btnDoResetPwd');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังรีเซ็ต...');

    const res = await API.adminResetPassword(_resetPwdUserId, pwd);
    btn.prop('disabled', false).html('<i class="bi bi-key me-1"></i> รีเซ็ต');
    if (!res.success) { App.error(res.message || 'รีเซ็ตรหัสผ่านล้มเหลว'); return; }

    $('#modalDirResetPwd').modal('hide');

    const u = res.data?.username || '';
    const p = res.data?.password_plain || '';
    const n = res.data?.full_name || '';
    $('#credResultTitle').html('<i class="bi bi-key-fill me-1 text-warning"></i> รีเซ็ตรหัสผ่านสำเร็จ');
    showCredModal(n, u, p);
    _resetPwdUserId = null;
}

function showCredModal(fullName, username, password) {
    $('#credResultBody').html(
        '<table class="table table-sm table-bordered mb-2">' +
            '<tr><td class="text-muted" style="width:100px">ชื่อ-สกุล</td><td><strong>' + App.escapeHtml(fullName) + '</strong></td></tr>' +
            '<tr><td class="text-muted">Username</td><td><strong class="text-primary">' + App.escapeHtml(username) + '</strong></td></tr>' +
            '<tr><td class="text-muted">Password</td><td><strong class="text-danger">' + App.escapeHtml(password) + '</strong></td></tr>' +
        '</table>' +
        '<small class="text-danger d-block mb-2"><i class="bi bi-exclamation-triangle me-1"></i>กรุณาจดรหัสผ่านนี้ไว้ จะไม่แสดงอีก</small>' +
        '<button class="btn btn-sm btn-outline-secondary" id="btnCopyCred" onclick="copyCredentials(' +
            JSON.stringify(fullName) + ',' + JSON.stringify(username) + ',' + JSON.stringify(password) +
        ')">' +
        '<i class="bi bi-clipboard me-1"></i>คัดลอก</button>'
    );
    $('#modalDirCredResult').modal('show');
}

function copyCredentials(fullName, username, password) {
    const text = 'ชื่อ-สกุล: ' + fullName + '\nUsername: ' + username + '\nPassword: ' + password;
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const btn = document.getElementById('btnCopyCred');
            if (btn) { btn.innerHTML = '<i class="bi bi-clipboard-check me-1"></i>คัดลอกแล้ว!'; btn.classList.add('btn-success'); btn.classList.remove('btn-outline-secondary'); }
        }).catch(() => _copyFallback(text));
    } else {
        _copyFallback(text);
    }
}

function _copyFallback(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        const btn = document.getElementById('btnCopyCred');
        if (btn) { btn.innerHTML = '<i class="bi bi-clipboard-check me-1"></i>คัดลอกแล้ว!'; btn.classList.add('btn-success'); btn.classList.remove('btn-outline-secondary'); }
    } catch(e) { App.error('ไม่สามารถคัดลอกได้'); }
    document.body.removeChild(ta);
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
