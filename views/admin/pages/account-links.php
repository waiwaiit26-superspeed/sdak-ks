<?php $pageTitle = 'คำขอผูกบัญชี'; $page = 'account-links'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-link-45deg me-2"></i>คำขอผูกบัญชี</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                        <li class="breadcrumb-item active">คำขอผูกบัญชี</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Filter -->
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <label class="mb-0 small text-muted mr-2">สถานะ:</label>
                        <select class="form-control form-control-sm" id="filterLinkStatus" style="width:150px;">
                            <option value="">ทั้งหมด</option>
                            <option value="pending" selected>รออนุมัติ</option>
                            <option value="approved">อนุมัติแล้ว</option>
                            <option value="rejected">ปฏิเสธแล้ว</option>
                        </select>
                    </div>
                    <small class="text-muted" id="linksTotalInfo"></small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">สมาชิก (เป้าหมาย)</th>
                                    <th width="10%">ประเภท</th>
                                    <th width="20%">อีเมล / บัญชี</th>
                                    <th width="13%">ชื่อผู้ใช้ที่เสนอ</th>
                                    <th width="10%">วันที่ขอ</th>
                                    <th width="10%">สถานะ</th>
                                    <th width="12%">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="linksTableBody">
                                <tr><td colspan="8" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div id="linksPagination"></div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Action Modal (Approve / Reject) -->
<div class="modal fade" id="linkActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="linkActionTitle">ดำเนินการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="linkActionInfo" class="mb-3"></div>
                <div class="form-group">
                    <label>หมายเหตุ (ถ้ามี)</label>
                    <textarea class="form-control" id="linkActionNote" rows="3" placeholder="หมายเหตุสำหรับผู้ขอ..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="btnDoLinkAction">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>
<script>
let _currentLinkAction = {};
let _canManageLinks    = false;

function getLinkTypeBadge(t) {
    return t === 'google'
        ? '<span class="badge badge-info"><i class="bi bi-google me-1"></i>Google</span>'
        : '<span class="badge badge-secondary"><i class="bi bi-envelope me-1"></i>อีเมล</span>';
}

function getLinkStatusBadge(s) {
    const map = {
        pending:  '<span class="badge badge-warning"><i class="bi bi-hourglass-split me-1"></i>รออนุมัติ</span>',
        approved: '<span class="badge badge-success"><i class="bi bi-check-circle me-1"></i>อนุมัติแล้ว</span>',
        rejected: '<span class="badge badge-danger"><i class="bi bi-x-circle me-1"></i>ปฏิเสธ</span>',
    };
    return map[s] || `<span class="badge badge-light">${s}</span>`;
}

async function loadLinks(page = 1) {
    const status = $('#filterLinkStatus').val();
    const params = { page, per_page: 30 };
    const res = await API.listPendingLinks(params);
    const tbody = $('#linksTableBody');

    if (!res.success) {
        tbody.html(`<tr><td colspan="8" class="text-center text-danger py-3">${res.message}</td></tr>`);
        return;
    }

    let rows = res.data || [];
    if (status) rows = rows.filter(r => r.status === status);

    if (rows.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>');
        $('#linksTotalInfo').text('ไม่พบรายการ');
        $('#linksPagination').empty();
        return;
    }

    const startNum = ((res.pagination?.current_page || 1) - 1) * (res.pagination?.per_page || 30);
    let html = '';
    rows.forEach((r, i) => {
        const targetSchool = r.target_school ? `<br><small class="text-muted">${App.escapeHtml(r.target_school)}</small>` : '';
        let actions = '';
        if (r.status === 'pending' && _canManageLinks) {
            actions = `
                <button class="btn btn-xs btn-success mr-1" onclick="linkAction(${r.id},'approve','${App.escHtml(r.target_full_name || '')}')">
                    <i class="bi bi-check-lg"></i> อนุมัติ
                </button>
                <button class="btn btn-xs btn-danger" onclick="linkAction(${r.id},'reject','${App.escHtml(r.target_full_name || '')}')">
                    <i class="bi bi-x-lg"></i>
                </button>`;
        } else {
            actions = `<small class="text-muted">โดย ${App.escapeHtml(r.approver_name || '-')}<br>${App.formatDate(r.approved_at)}</small>`;
        }

        html += `<tr>
            <td>${startNum + i + 1}</td>
            <td>
                <strong>${App.escapeHtml(r.target_full_name || '-')}</strong>
                ${targetSchool}
                <br><small class="text-muted">${App.getMemberTypeBadge(r.target_member_type)}</small>
            </td>
            <td>${getLinkTypeBadge(r.request_type)}</td>
            <td><small>${App.escapeHtml(r.email || '-')}</small></td>
            <td><small>${App.escapeHtml(r.proposed_username || '-')}</small></td>
            <td><small>${App.formatDate(r.requested_at)}</small></td>
            <td>${getLinkStatusBadge(r.status)}</td>
            <td>${actions}</td>
        </tr>`;
    });

    tbody.html(html);
    const total = res.pagination?.total || rows.length;
    $('#linksTotalInfo').text(`พบ ${total} รายการ`);
    if (res.pagination) App.buildPagination('#linksPagination', res.pagination, loadLinks);
}

function linkAction(requestId, action, memberName) {
    _currentLinkAction = { requestId, action };
    const titles = { approve: 'อนุมัติคำขอผูกบัญชี', reject: 'ปฏิเสธคำขอผูกบัญชี' };
    const infos = {
        approve: `<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> อนุมัติคำขอผูกบัญชีของ <strong>${memberName}</strong></div>`,
        reject:  `<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> ปฏิเสธคำขอผูกบัญชีของ <strong>${memberName}</strong></div>`,
    };
    const btnCls = { approve: 'btn-success', reject: 'btn-danger' };
    $('#linkActionTitle').text(titles[action] || 'ดำเนินการ');
    $('#linkActionInfo').html(infos[action] || '');
    $('#linkActionNote').val('');
    $('#btnDoLinkAction').attr('class', 'btn ' + (btnCls[action] || 'btn-primary')).text('ยืนยัน');
    $('#linkActionModal').modal('show');
}

$('#btnDoLinkAction').on('click', async function () {
    const btn = $(this);
    btn.prop('disabled', true);
    const note = $('#linkActionNote').val().trim();
    let res;
    if (_currentLinkAction.action === 'approve') {
        res = await API.approveAccountLink(_currentLinkAction.requestId, note);
    } else {
        res = await API.rejectAccountLink(_currentLinkAction.requestId, note);
    }
    btn.prop('disabled', false);
    if (res.success) {
        App.success(res.message);
        $('#linkActionModal').modal('hide');
        loadLinks(1);
    } else {
        App.error(res.message);
    }
});

$(async function () {
    if (API.isAdmin()) {
        _canManageLinks = true;
    } else {
        const permsRes = await API.getMySubAdminPermissions();
        if (permsRes.success && permsRes.data?.areas?.members?.includes('approve')) {
            _canManageLinks = true;
        } else {
            // Sub-admin without approve permission: redirect away
            window.location.href = './?page=dashboard';
            return;
        }
    }
    loadLinks(1);
    $('#filterLinkStatus').on('change', () => loadLinks(1));
});
</script>
<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
