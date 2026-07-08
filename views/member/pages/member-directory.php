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
        const editBtn = canEdit
            ? `<button class="btn btn-xs btn-outline-secondary" title="แก้ไข" onclick="openEditModal(${m.id}, '${(m.member_number_raw || '').replace(/'/g,'')}', '${App.escapeHtml(m.full_name || '').replace(/'/g, '&#39;')}')"><i class="bi bi-pencil"></i></button>`
            : '';

        html += `<tr data-member-id="${m.id}" data-prefix="${App.escapeHtml(m.prefix || '')}">
            <td>${startNum + i + 1}</td>
            <td class="dir-num-cell">${memNum}</td>
            <td class="dir-name-cell">
                <strong>${App.escapeHtml((m.prefix || '') + m.full_name)}</strong>
            </td>
            <td>${App.getMemberTypeBadge(m.member_type)}</td>
            <td>${pos}${rank}</td>
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
                <div class="form-group">
                    <label class="font-weight-bold">ชื่อ-นามสกุล (ไม่รวมคำนำหน้า)</label>
                    <input type="text" class="form-control" id="dirEditFullName"
                        placeholder="ชื่อ-นามสกุล">
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

function openEditModal(userId, memberNumber, fullName) {
    _dirEditUserId = userId;
    $('#dirEditMemberNumber').val(memberNumber);
    $('#dirEditFullName').val(fullName);
    $('#btnSaveDirEdit').prop('disabled', false);
    $('#modalDirEdit').modal('show');
}

async function saveDirectoryEdit() {
    if (!_dirEditUserId) return;
    const memberNumber = $('#dirEditMemberNumber').val().trim();
    const fullName     = $('#dirEditFullName').val().trim();
    if (!fullName) { App.error('กรุณาระบุชื่อ-นามสกุล'); return; }

    const btn = $('#btnSaveDirEdit');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

    const res = await API.directoryEdit({
        user_id:       _dirEditUserId,
        member_number: memberNumber,
        full_name:     fullName,
    });

    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> บันทึก');

    if (!res.success) { App.error(res.message || 'บันทึกล้มเหลว'); return; }

    App.success('อัปเดตข้อมูลสำเร็จ');
    $('#modalDirEdit').modal('hide');

    // Update the row in-place
    const row = $(`tr[data-member-id="${_dirEditUserId}"]`);
    if (row.length) {
        if (res.data.member_number_display !== undefined) {
            const disp = res.data.member_number_display || '';
            row.find('.dir-num-cell').html(disp ? `<span class="badge badge-light border">${App.escapeHtml(disp)}</span>` : '<span class="text-muted small">-</span>');
        }
        if (res.data.full_name !== undefined) {
            const prefix = row.data('prefix') || '';
            row.find('.dir-name-cell strong').text(prefix + res.data.full_name);
        }
    }
    _dirEditUserId = null;
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
