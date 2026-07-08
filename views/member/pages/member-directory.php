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
                    <div class="row align-items-end">
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
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label small text-muted mb-1">ประเภทสมาชิก</label>
                            <select class="form-control" id="dirType">
                                <option value="">ทุกประเภท</option>
                            </select>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <label class="form-label small text-muted mb-1 d-block">&nbsp;</label>
                            <span class="text-muted small" id="dirResultInfo"></span>
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
                                    <th width="10%">รหัส</th>
                                    <th width="25%">ชื่อ-นามสกุล</th>
                                    <th width="12%">ประเภท</th>
                                    <th width="15%">ตำแหน่ง / วิทยฐานะ</th>
                                    <th>โรงเรียน / หน่วยงาน</th>
                                </tr>
                            </thead>
                            <tbody id="dirTableBody">
                                <tr><td colspan="6" class="text-center py-4 text-muted">
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
$(async function () {
    App.requireLogin();

    // Check if feature is enabled
    const sRes = await API.getSettings();
    if (sRes.success && sRes.data && sRes.data.member_directory_enabled === '0') {
        $('#dirTableBody').html('<tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-lock" style="font-size:2rem;"></i><br>ฟีเจอร์นี้ถูกปิดโดยผู้ดูแลระบบ</td></tr>');
        return;
    }

    // Populate member type filter
    API.getMemberTypes().then(res => {
        if (!res.success || !res.data) return;
        let opts = '<option value="">ทุกประเภท</option>';
        res.data.forEach(t => {
            opts += `<option value="${t.key}">${App.escapeHtml(t.label)}</option>`;
        });
        $('#dirType').html(opts);
    });

    loadDirectory(1);

    let searchTimer;
    $('#dirSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadDirectory(1), 400);
    });
    $('#dirType').on('change', () => loadDirectory(1));
});

async function loadDirectory(page = 1) {
    const search = $('#dirSearch').val().trim();
    const type   = $('#dirType').val();
    const params = { page, per_page: 30 };
    if (search) params.search = search;
    if (type)   params.member_type = type;

    $('#dirTableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const res = await API.getMemberDirectory(params);
    if (!res.success) {
        $('#dirTableBody').html(`<tr><td colspan="6" class="text-center text-danger py-4">${App.escapeHtml(res.message || 'โหลดข้อมูลล้มเหลว')}</td></tr>`);
        return;
    }

    const data = res.data || [];
    const pagination = res.pagination || {};
    const total = pagination.total || 0;

    $('#dirTotal').text(total);
    const page_ = pagination.current_page || 1;
    const perPage = pagination.per_page || 30;
    const start = (page_ - 1) * perPage + 1;
    const end   = Math.min(page_ * perPage, total);
    $('#dirResultInfo').text(total > 0 ? `แสดง ${start}–${end} จาก ${total} รายการ` : '');

    if (!data.length) {
        $('#dirTableBody').html('<tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-person-x" style="font-size:2rem;"></i><br>ไม่พบสมาชิก</td></tr>');
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

        html += `<tr>
            <td>${startNum + i + 1}</td>
            <td>${memNum}</td>
            <td>
                <strong>${App.escapeHtml((m.prefix || '') + m.full_name)}</strong>
            </td>
            <td>${App.getMemberTypeBadge(m.member_type)}</td>
            <td>${pos}${rank}</td>
            <td>${school}</td>
        </tr>`;
    });

    $('#dirTableBody').html(html);

    if (pagination) {
        App.buildPagination('#dirPagination', pagination, loadDirectory);
    }
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
