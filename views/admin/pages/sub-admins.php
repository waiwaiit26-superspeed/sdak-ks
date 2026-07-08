<?php $pageTitle = 'จัดการสิทธิ์ผู้ดูแล'; $page = 'sub-admins'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-person-gear me-2"></i>จัดการสิทธิ์ผู้ดูแลย่อย</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                        <li class="breadcrumb-item active">จัดการสิทธิ์ผู้ดูแล</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <ul class="nav nav-tabs mb-3" id="subAdminTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-members" data-toggle="tab" href="#pane-members" role="tab">
                        <i class="bi bi-people me-1"></i>ข้อมูลสมาชิก
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-news" data-toggle="tab" href="#pane-news" role="tab">
                        <i class="bi bi-newspaper me-1"></i>ข่าวสาร
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-activities" data-toggle="tab" href="#pane-activities" role="tab">
                        <i class="bi bi-calendar-event me-1"></i>กิจกรรม
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-finance" data-toggle="tab" href="#pane-finance" role="tab">
                        <i class="bi bi-wallet2 me-1"></i>การเงิน
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="subAdminTabContent">

                <!-- ======================================================= -->
                <!-- TAB: MEMBERS                                             -->
                <!-- ======================================================= -->
                <div class="tab-pane fade show active" id="pane-members" role="tabpanel">
                    <?php echo buildAreaPane('members', 'บริหารจัดการสมาชิก',
                        '<i class="bi bi-people me-1"></i>',
                        'มอบสิทธิ์ให้สมาชิกช่วยบริหารจัดการข้อมูลสมาชิก',
                        [
                            'view'    => 'ดูรายชื่อสมาชิก',
                            'approve' => 'อนุมัติ/ระงับสมาชิก',
                            'create'  => 'เพิ่มสมาชิกใหม่',
                            'edit'    => 'แก้ไขข้อมูลสมาชิก',
                            'delete'  => 'ลบสมาชิก',
                        ]
                    ); ?>
                </div>

                <!-- ======================================================= -->
                <!-- TAB: NEWS                                                -->
                <!-- ======================================================= -->
                <div class="tab-pane fade" id="pane-news" role="tabpanel">
                    <?php echo buildAreaPane('news', 'จัดการข่าวสาร',
                        '<i class="bi bi-newspaper me-1"></i>',
                        'มอบสิทธิ์ให้สมาชิกช่วยดูแลเนื้อหาข่าวสาร',
                        [
                            'create' => 'สร้างข่าวใหม่',
                            'edit'   => 'แก้ไขข่าว',
                            'delete' => 'ลบข่าว',
                        ]
                    ); ?>
                </div>

                <!-- ======================================================= -->
                <!-- TAB: ACTIVITIES                                          -->
                <!-- ======================================================= -->
                <div class="tab-pane fade" id="pane-activities" role="tabpanel">
                    <?php echo buildAreaPane('activities', 'จัดการกิจกรรม',
                        '<i class="bi bi-calendar-event me-1"></i>',
                        'มอบสิทธิ์ให้สมาชิกช่วยดูแลกิจกรรมของสมาคม',
                        [
                            'create' => 'สร้างกิจกรรมใหม่',
                            'edit'   => 'แก้ไขกิจกรรม',
                            'delete' => 'ลบกิจกรรม',
                        ]
                    ); ?>
                </div>

                <!-- ======================================================= -->
                <!-- TAB: FINANCE                                             -->
                <!-- ======================================================= -->
                <div class="tab-pane fade" id="pane-finance" role="tabpanel">
                    <?php echo buildAreaPane('finance', 'บริหารการเงิน',
                        '<i class="bi bi-wallet2 me-1"></i>',
                        'มอบสิทธิ์ให้สมาชิกช่วยดูแลการเงินของสมาคม',
                        [
                            'view'   => 'ดูรายการการเงิน',
                            'create' => 'บันทึกรายการใหม่',
                            'edit'   => 'แก้ไขรายการ',
                            'delete' => 'ลบรายการ',
                            'export' => 'ส่งออกข้อมูล',
                        ]
                    ); ?>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- ============================================================ -->
<!-- MODAL: Assign / Edit Sub-Admin                              -->
<!-- ============================================================ -->
<div class="modal fade" id="saModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="saModalTitle"><i class="bi bi-person-plus me-1"></i>มอบสิทธิ์</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="saArea">
                <input type="hidden" id="saEditUserId">

                <div class="form-group" id="saUserSelectGroup">
                    <label>เลือกสมาชิก <span class="text-danger">*</span></label>
                    <select class="form-control" id="saUserId">
                        <option value="">-- เลือกสมาชิก --</option>
                    </select>
                </div>
                <div class="form-group" id="saUserInfoGroup" style="display:none;">
                    <label>สมาชิก</label>
                    <p class="form-control-plaintext font-weight-bold mb-0" id="saUserInfoName"></p>
                </div>

                <div class="form-group">
                    <label>สิทธิ์ที่มอบให้</label>
                    <div id="saPermissionsWrap"></div>
                </div>

                <div class="form-group">
                    <label>หมายเหตุ</label>
                    <input type="text" class="form-control" id="saNote" placeholder="หมายเหตุ (ถ้ามี)">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="saModalSaveBtn" onclick="saveSaModal()">
                    <i class="bi bi-check-lg me-1"></i>มอบสิทธิ์
                </button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>
<script>
// ================================================================
// AREA CONFIG
// ================================================================
const SA_AREAS = {
    members: {
        label: 'บริหารจัดการสมาชิก',
        permissions: {
            view:    'ดูรายชื่อสมาชิก',
            approve: 'อนุมัติ/ระงับสมาชิก',
            create:  'เพิ่มสมาชิกใหม่',
            edit:    'แก้ไขข้อมูลสมาชิก',
            delete:  'ลบสมาชิก',
        }
    },
    news: {
        label: 'จัดการข่าวสาร',
        permissions: {
            create: 'สร้างข่าวใหม่',
            edit:   'แก้ไขข่าว',
            delete: 'ลบข่าว',
        }
    },
    activities: {
        label: 'จัดการกิจกรรม',
        permissions: {
            create: 'สร้างกิจกรรมใหม่',
            edit:   'แก้ไขกิจกรรม',
            delete: 'ลบกิจกรรม',
        }
    },
    finance: {
        label: 'บริหารการเงิน',
        permissions: {
            view:   'ดูรายการการเงิน',
            create: 'บันทึกรายการใหม่',
            edit:   'แก้ไขรายการ',
            delete: 'ลบรายการ',
            export: 'ส่งออกข้อมูล',
        }
    }
};

// ================================================================
// LOAD LIST
// ================================================================
async function loadSaList(area) {
    const tbody = document.getElementById('sa-tbody-' + area);
    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-3"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</td></tr>';

    const res = await API.getSubAdmins(area);
    if (!res.success) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-3">' + (res.message || 'โหลดข้อมูลล้มเหลว') + '</td></tr>';
        return;
    }

    const rows = res.data || [];
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">ยังไม่มีผู้ดูแลในส่วนนี้</td></tr>';
        return;
    }

    const areaPerms = SA_AREAS[area]?.permissions || {};
    tbody.innerHTML = rows.map((r, i) => {
        const perms = (r.permissions || []).map(p => {
            const label = areaPerms[p] || p;
            return `<span class="badge badge-light border mr-1">${label}</span>`;
        }).join('');

        const statusBadge = r.is_active
            ? '<span class="badge badge-success">ใช้งาน</span>'
            : '<span class="badge badge-secondary">ระงับ</span>';

        const date = r.created_at ? r.created_at.split(' ')[0] : '-';

        return `<tr>
            <td>${i + 1}</td>
            <td><strong>${App.escapeHtml(r.user_name || '-')}</strong><br><small class="text-muted">${App.escapeHtml(r.user_email || '')}</small></td>
            <td>${perms || '<span class="text-muted">-</span>'}</td>
            <td>${statusBadge}</td>
            <td><small>${App.escapeHtml(r.assigner_name || '-')}</small></td>
            <td><small>${date}</small></td>
            <td>
                <button class="btn btn-outline-primary btn-xs mr-1" onclick="showEditSaModal('${area}', ${r.user_id}, ${JSON.stringify(r).replace(/"/g,'&quot;')})" title="แก้ไขสิทธิ์"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-outline-${r.is_active ? 'warning' : 'success'} btn-xs mr-1" onclick="toggleSa('${area}', ${r.user_id})" title="${r.is_active ? 'ระงับ' : 'เปิดใช้งาน'}"><i class="bi bi-${r.is_active ? 'pause-circle' : 'play-circle'}"></i></button>
                <button class="btn btn-outline-danger btn-xs" onclick="deleteSa(${r.id}, '${area}')" title="ลบ"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    }).join('');
}

// ================================================================
// SHOW ASSIGN MODAL
// ================================================================
async function showAssignSaModal(area) {
    $('#saArea').val(area);
    $('#saEditUserId').val('');
    $('#saUserSelectGroup').show();
    $('#saUserInfoGroup').hide();
    $('#saNote').val('');
    $('#saModalTitle').html('<i class="bi bi-person-plus me-1"></i>มอบสิทธิ์ — ' + (SA_AREAS[area]?.label || area));
    $('#saModalSaveBtn').text('มอบสิทธิ์').removeClass('btn-warning').addClass('btn-primary');

    // Load available members
    const res = await API.getSubAdminAvailableMembers(area);
    const sel = document.getElementById('saUserId');
    sel.innerHTML = '<option value="">-- เลือกสมาชิก --</option>';
    (res.data || []).forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.id;
        opt.textContent = (m.full_name || '') + (m.email ? ' (' + m.email + ')' : '');
        sel.appendChild(opt);
    });

    // Build permissions checkboxes (all checked by default)
    buildPermCheckboxes(area, Object.keys(SA_AREAS[area]?.permissions || {}));
    $('#saModal').modal('show');
}

async function showEditSaModal(area, userId, row) {
    $('#saArea').val(area);
    $('#saEditUserId').val(userId);
    $('#saUserSelectGroup').hide();
    $('#saUserInfoGroup').show();
    $('#saUserInfoName').text(row.user_name + (row.user_email ? ' (' + row.user_email + ')' : ''));
    $('#saNote').val(row.note || '');
    $('#saModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขสิทธิ์ — ' + (SA_AREAS[area]?.label || area));
    $('#saModalSaveBtn').html('<i class="bi bi-check-lg me-1"></i>บันทึก').removeClass('btn-primary').addClass('btn-warning');

    buildPermCheckboxes(area, row.permissions || []);
    $('#saModal').modal('show');
}

function buildPermCheckboxes(area, checkedPerms) {
    const wrap = document.getElementById('saPermissionsWrap');
    const areaPerms = SA_AREAS[area]?.permissions || {};
    wrap.innerHTML = Object.entries(areaPerms).map(([key, label]) => `
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input sa-perm-check" id="saperm_${key}" value="${key}" ${checkedPerms.includes(key) ? 'checked' : ''}>
            <label class="custom-control-label" for="saperm_${key}">${label}</label>
        </div>
    `).join('');
}

// ================================================================
// SAVE
// ================================================================
async function saveSaModal() {
    const area    = $('#saArea').val();
    const editId  = $('#saEditUserId').val();
    const userId  = editId || $('#saUserId').val();
    const note    = $('#saNote').val().trim();
    const perms   = [...document.querySelectorAll('.sa-perm-check:checked')].map(c => c.value);

    if (!userId) { App.error('กรุณาเลือกสมาชิก'); return; }
    if (!perms.length) { App.error('กรุณาเลือกสิทธิ์อย่างน้อย 1 รายการ'); return; }

    const btn = document.getElementById('saModalSaveBtn');
    btn.disabled = true;

    let res;
    if (editId) {
        res = await API.updateSubAdminPermissions(userId, area, perms);
    } else {
        res = await API.assignSubAdmin(userId, area, perms, note);
    }

    btn.disabled = false;
    if (res.success) {
        App.success(res.message || 'บันทึกสำเร็จ');
        $('#saModal').modal('hide');
        loadSaList(area);
    } else {
        App.error(res.message || 'เกิดข้อผิดพลาด');
    }
}

// ================================================================
// TOGGLE / DELETE
// ================================================================
async function toggleSa(area, userId) {
    const res = await API.toggleSubAdmin(userId, area);
    if (res.success) {
        App.success(res.message || 'เปลี่ยนสถานะสำเร็จ');
        loadSaList(area);
    } else {
        App.error(res.message || 'เกิดข้อผิดพลาด');
    }
}

async function deleteSa(id, area) {
    const confirmed = await App.confirm('ลบระเบียนสิทธิ์', 'ต้องการลบระเบียนสิทธิ์นี้หรือไม่?', 'warning');
    if (!confirmed) return;
    const res = await API.deleteSubAdminRecord(id);
    if (res.success) {
        App.success('ลบสำเร็จ');
        loadSaList(area);
    } else {
        App.error(res.message || 'เกิดข้อผิดพลาด');
    }
}

// ================================================================
// INIT — load first tab, lazy-load others
// ================================================================
$(function() {
    loadSaList('members');

    $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
        const target = $(e.target).attr('href');
        if (target === '#pane-news')       loadSaList('news');
        if (target === '#pane-activities') loadSaList('activities');
        if (target === '#pane-finance')    loadSaList('finance');
    });
});
</script>

<?php
/**
 * Build HTML for one area pane
 */
function buildAreaPane(string $area, string $title, string $icon, string $desc, array $permLabels): string {
    $html = <<<HTML
<div class="card shadow-sm">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h3 class="card-title">{$icon}{$title}</h3>
                <p class="mb-0 small text-muted">{$desc}</p>
            </div>
            <div class="col-md-5 text-md-right">
                <button class="btn btn-primary btn-sm" onclick="showAssignSaModal('{$area}')">
                    <i class="bi bi-person-plus me-1"></i>มอบสิทธิ์
                </button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="thead-light">
                    <tr>
                        <th width="4%">#</th>
                        <th width="25%">ชื่อ-นามสกุล / อีเมล</th>
                        <th width="28%">สิทธิ์ที่ได้รับ</th>
                        <th width="10%">สถานะ</th>
                        <th width="15%">มอบสิทธิ์โดย</th>
                        <th width="8%">วันที่</th>
                        <th width="10%">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="sa-tbody-{$area}">
                    <tr><td colspan="7" class="text-center py-3 text-muted">กำลังโหลด...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="callout callout-info mt-3">
    <h5><i class="bi bi-info-circle me-1"></i>สิทธิ์ที่สามารถมอบได้</h5>
    <ul class="mb-0">
HTML;
    foreach ($permLabels as $perm => $label) {
        $html .= "<li><strong>{$perm}</strong> — {$label}</li>\n";
    }
    $html .= <<<HTML
    </ul>
</div>
HTML;
    return $html;
}
?>
<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
