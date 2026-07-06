<?php $pageTitle = 'ตรวจสอบการชำระเงิน'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-credit-card-2-front me-2"></i>ตรวจสอบการชำระเงิน</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ตรวจสอบการชำระเงิน</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">

            <!-- Permission Check -->
            <div id="paNoPermission" class="text-center py-5" style="display:none;">
                <i class="bi bi-shield-lock" style="font-size:4rem;color:#ccc;"></i>
                <h4 class="mt-3 text-muted">คุณไม่มีสิทธิ์เข้าถึงส่วนนี้</h4>
                <p class="text-muted">เฉพาะผู้ที่ได้รับมอบสิทธิ์เป็นผู้จัดการการเงินเท่านั้น</p>
                <a href="<?php echo $basePath; ?>member/" class="btn btn-primary mt-2">
                    <i class="bi bi-house me-1"></i>กลับหน้าหลัก
                </a>
            </div>

            <!-- Main Content -->
            <div id="paContent" style="display:none;">

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-md-4 col-sm-6 col-12 mb-3">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3 id="paPendingCount">0</h3>
                                <p>รอตรวจสอบ</p>
                            </div>
                            <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 mb-3">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3 id="paApprovedCount">0</h3>
                                <p>อนุมัติแล้ว</p>
                            </div>
                            <div class="icon"><i class="bi bi-check-circle"></i></div>
                        </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-12 mb-3">
                        <div class="small-box bg-secondary">
                            <div class="inner">
                                <h3 id="paTotalCount">0</h3>
                                <p>ทั้งหมด</p>
                            </div>
                            <div class="icon"><i class="bi bi-list-check"></i></div>
                        </div>
                    </div>
                </div>

                <!-- Filter & Table -->
                <div class="card shadow-sm">
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-3 mb-2 mb-md-0">
                                <label class="form-label mb-0 small text-muted">สถานะ</label>
                                <select class="form-control form-control-sm" id="paFilterStatus">
                                    <option value="pending">รอตรวจสอบ</option>
                                    <option value="paid">อนุมัติแล้ว</option>
                                    <option value="all">ทั้งหมด</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-2 mb-md-0">
                                <label class="form-label mb-0 small text-muted">ค้นหา</label>
                                <input type="text" class="form-control form-control-sm" id="paSearch" placeholder="ค้นหาชื่อ/กิจกรรม...">
                            </div>
                            <div class="col-md-5 text-md-right">
                                <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                <button class="btn btn-outline-primary btn-sm" onclick="paLoadList()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>รีเฟรช
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
                                        <th width="15%">ผู้ลงทะเบียน</th>
                                        <th width="20%">กิจกรรม</th>
                                        <th width="10%">จำนวนเงิน</th>
                                        <th width="10%">วันที่ลงทะเบียน</th>
                                        <th width="8%">สลิป</th>
                                        <th width="10%">สถานะการชำระ</th>
                                        <th width="10%">สถานะลงทะเบียน</th>
                                        <th width="13%">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody id="paTableBody">
                                    <tr><td colspan="9" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Slip Preview Modal -->
<div class="modal fade" id="paSlipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-image me-1"></i>สลิปโอนเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="paSlipImg" src="" class="img-fluid rounded" style="max-height:500px" alt="สลิป">
            </div>
            <div class="modal-footer" id="paSlipFooter">
                <button class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<script>
var paAllData = [];

$(function() {
    App.requireLogin();
    paCheckPermission();
});

async function paCheckPermission() {
    var res = await API.getMyFinancePermissions();
    if (!res.success || (!res.data.is_admin && !res.data.is_finance_manager)) {
        $('#paNoPermission').show();
        $('#paContent').hide();
        return;
    }
    $('#paContent').show();
    $('#paNoPermission').hide();
    paLoadList();
    paLoadCounts();

    $('#paFilterStatus').on('change', function() { paLoadList(); });
    var timer;
    $('#paSearch').on('input', function() { clearTimeout(timer); timer = setTimeout(paRenderFiltered, 400); });
}

async function paLoadCounts() {
    try {
        var pRes = await API.getPendingPayments({ status: 'pending' });
        if (pRes.success) $('#paPendingCount').text(pRes.data ? pRes.data.length : 0);

        var aRes = await API.getPendingPayments({ status: 'paid' });
        if (aRes.success) $('#paApprovedCount').text(aRes.data ? aRes.data.length : 0);

        var allRes = await API.getPendingPayments({ status: 'all' });
        if (allRes.success) $('#paTotalCount').text(allRes.data ? allRes.data.length : 0);
    } catch(e) {}
}

async function paLoadList() {
    var status = $('#paFilterStatus').val();
    var tbody = $('#paTableBody');
    tbody.html('<tr><td colspan="9" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</td></tr>');

    var res = await API.getPendingPayments({ status: status });
    if (!res.success) {
        tbody.html('<tr><td colspan="9" class="text-center text-danger py-3">' + (res.message || 'เกิดข้อผิดพลาด') + '</td></tr>');
        return;
    }
    paAllData = res.data || [];
    paRenderFiltered();
}

function paRenderFiltered() {
    var search = ($('#paSearch').val() || '').trim().toLowerCase();
    var filtered = paAllData;
    if (search) {
        filtered = paAllData.filter(function(r) {
            return (r.full_name && r.full_name.toLowerCase().indexOf(search) >= 0) ||
                   (r.activity_title && r.activity_title.toLowerCase().indexOf(search) >= 0) ||
                   (r.email && r.email.toLowerCase().indexOf(search) >= 0);
        });
    }
    paRenderTable(filtered);
}

function paRenderTable(data) {
    var tbody = $('#paTableBody');
    if (!data || data.length === 0) {
        tbody.html('<tr><td colspan="9" class="text-center text-muted py-4"><i class="bi bi-inbox" style="font-size:2rem;"></i><br>ไม่พบรายการ</td></tr>');
        return;
    }

    var html = '';
    data.forEach(function(r, i) {
        var payBadge = '';
        if (r.payment_status === 'paid') payBadge = '<span class="badge badge-success">ชำระแล้ว</span>';
        else if (r.payment_status === 'pending') payBadge = '<span class="badge badge-warning">รอตรวจสอบ</span>';
        else if (r.payment_status === 'refunded') payBadge = '<span class="badge badge-info">คืนเงิน</span>';
        else payBadge = '<span class="badge badge-secondary">ไม่ต้องชำระ</span>';

        var regBadge = '';
        if (r.status === 'approved') regBadge = '<span class="badge badge-success">อนุมัติ</span>';
        else if (r.status === 'pending') regBadge = '<span class="badge badge-warning">รอ</span>';
        else if (r.status === 'rejected') regBadge = '<span class="badge badge-danger">ปฏิเสธ</span>';
        else regBadge = '<span class="badge badge-secondary">' + r.status + '</span>';

        var slipBtn = r.payment_proof
            ? '<button class="btn btn-xs btn-outline-info" onclick="paPreviewSlip(\'' + r.payment_proof + '\', ' + r.id + ', \'' + r.payment_status + '\')" title="ดูสลิป"><i class="bi bi-image"></i> ดูสลิป</button>'
            : '<span class="text-muted small">ยังไม่แนบ</span>';

        var actions = '';
        if (r.payment_status === 'pending' && r.payment_proof) {
            actions = '<div class="btn-group btn-group-sm">'
                + '<button class="btn btn-outline-success" onclick="paVerify(' + r.id + ', \'approve\')" title="อนุมัติ"><i class="bi bi-check-lg"></i> อนุมัติ</button>'
                + '<button class="btn btn-outline-danger" onclick="paVerify(' + r.id + ', \'reject\')" title="ปฏิเสธ"><i class="bi bi-x-lg"></i></button>'
                + '</div>';
        } else if (r.payment_status === 'pending' && !r.payment_proof) {
            actions = '<span class="text-muted small">รอสลิป</span>';
        } else {
            actions = '<span class="text-muted">-</span>';
        }

        var avatarUrl = App.getProfileImage(r);

        html += '<tr>' +
            '<td>' + (i + 1) + '</td>' +
            '<td><div class="d-flex align-items-center"><img src="' + avatarUrl + '" class="rounded-circle mr-2" width="30" height="30" style="object-fit:cover"><div><strong class="small">' + App.escHtml(r.full_name || '-') + '</strong><br><small class="text-muted">' + App.escHtml(r.email || '') + '</small></div></div></td>' +
            '<td><small><strong>' + App.escHtml(r.activity_title || '-') + '</strong></small></td>' +
            '<td class="text-right"><strong class="text-primary">' + parseFloat(r.fee_amount || 0).toLocaleString('th-TH', {minimumFractionDigits: 2}) + '</strong></td>' +
            '<td><small>' + App.formatDate(r.registered_at) + '</small></td>' +
            '<td class="text-center">' + slipBtn + '</td>' +
            '<td>' + payBadge + '</td>' +
            '<td>' + regBadge + '</td>' +
            '<td>' + actions + '</td>' +
            '</tr>';
    });
    tbody.html(html);
}

function paPreviewSlip(url, regId, paymentStatus) {
    var src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
    $('#paSlipImg').attr('src', src);

    var footerHtml = '<button class="btn btn-secondary" data-dismiss="modal">ปิด</button>';
    if (paymentStatus === 'pending') {
        footerHtml = '<button class="btn btn-success" onclick="paVerify(' + regId + ', \'approve\');$(\'#paSlipModal\').modal(\'hide\');"><i class="bi bi-check-lg me-1"></i>อนุมัติ</button>'
            + '<button class="btn btn-danger" onclick="paVerify(' + regId + ', \'reject\');$(\'#paSlipModal\').modal(\'hide\');"><i class="bi bi-x-lg me-1"></i>ปฏิเสธ</button>'
            + footerHtml;
    }
    $('#paSlipFooter').html(footerHtml);
    $('#paSlipModal').modal('show');
}

async function paVerify(regId, action) {
    if (action === 'approve') {
        var c = await Swal.fire({
            title: 'อนุมัติการชำระเงิน?',
            text: 'ยืนยันว่าได้ตรวจสอบสลิปแล้วและถูกต้อง',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-lg me-1"></i> อนุมัติ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745'
        });
        if (!c.isConfirmed) return;
        var res = await API.verifyPayment(regId, 'approve');
        if (res.success) { App.success(res.message); paLoadList(); paLoadCounts(); }
        else App.error(res.message);
    } else {
        var c = await Swal.fire({
            title: 'ปฏิเสธการชำระเงิน?',
            input: 'textarea',
            inputLabel: 'เหตุผลในการปฏิเสธ',
            inputPlaceholder: 'สลิปไม่ชัดเจน / ยอดเงินไม่ตรง...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-x-lg me-1"></i> ปฏิเสธ',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#dc3545'
        });
        if (!c.isConfirmed) return;
        var res = await API.verifyPayment(regId, 'reject', c.value || null);
        if (res.success) { App.success(res.message); paLoadList(); paLoadCounts(); }
        else App.error(res.message);
    }
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
