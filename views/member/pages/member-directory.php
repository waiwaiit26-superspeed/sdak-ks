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
                                    <th id="dirActionHeader" width="6%" style="display:none;"></th>
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

$(async function () {
    App.requireLogin();

    // Check if feature is enabled
    const sRes = await API.getSettings();
    if (sRes.success && sRes.data && sRes.data.member_directory_enabled === '0') {
        $('#dirTableBody').html('<tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-lock" style="font-size:2rem;"></i><br>ฟีเจอร์นี้ถูกปิดโดยผู้ดูแลระบบ</td></tr>');
        return;
    }

    // Check edit permission (admin or sub-admin with members.edit)
    const _u = API.getUser();
    if (_u && _u.role === 'admin') {
        canEdit = true;
    } else {
        try {
            const _pRes = await API.getMySubAdminPermissions();
            if (_pRes.success && _pRes.data?.areas?.members?.includes('edit')) canEdit = true;
        } catch (e) {}
    }
    if (canEdit) $('#dirActionHeader').show();

    // Populate member type filter
    API.getMemberTypes().then(res => {
        if (!res.success || !res.data) return;
        let opts = '<option value="">ทุกประเภท</option>';
        res.data.forEach(t => {
            opts += `<option value="${t.type_key}">${App.escapeHtml(t.label)}</option>`;
        });
        $('#dirType').html(opts);
    });

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
});

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
        const nameWithoutPrefix = (m.prefix && m.full_name && m.full_name.startsWith(m.prefix))
            ? m.full_name.slice(m.prefix.length).trim()
            : (m.full_name || '');
        const editBtn = canEdit
            ? `<button class="btn btn-xs btn-outline-secondary dir-edit-btn" title="แก้ไข"
                    data-id="${m.id}"
                    data-member-number="${App.escapeHtml(m.member_number_raw || '')}"
                    data-prefix="${App.escapeHtml(m.prefix || '')}"
                    data-fullname="${App.escapeHtml(nameWithoutPrefix)}"
                    data-position="${App.escapeHtml(m.position || '')}"
                    data-rank="${App.escapeHtml(m.academic_rank || '')}">
                    <i class="bi bi-pencil"></i></button>`
            : '';

        const avatar = `<img src="${App.getProfileImage(m)}" class="rounded-circle mr-2" width="34" height="34" style="object-fit:cover;flex-shrink:0;" alt="">`;
        html += `<tr data-member-id="${m.id}" data-prefix="${App.escapeHtml(m.prefix || '')}">
            <td>${startNum + i + 1}</td>
            <td class="dir-num-cell">${memNum}</td>
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
                    <input type="text" class="form-control" id="dirEditMemberNumber"
                        placeholder="เช่น 0042 หรือ SDAK-0042">
                    <small class="text-muted">ระบบจะดึงเฉพาะตัวเลขและเติม 0 นำหน้าโดยอัตโนมัติ อนุญาตให้กำหนดซ้ำได้</small>
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
        // member_number
        if (res.data.member_number_display !== undefined) {
            const disp = res.data.member_number_display || '';
            row.find('.dir-num-cell').html(disp
                ? `<span class="badge badge-light border">${App.escapeHtml(disp)}</span>`
                : '<span class="text-muted small">-</span>');
            $editBtn.data('member-number', res.data.member_number_raw || '');
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

    const displayName = (u.prefix || '') + (u.first_name && u.last_name ? u.first_name + ' ' + u.last_name : u.full_name);

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
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
