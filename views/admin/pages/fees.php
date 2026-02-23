<?php $pageTitle = 'ค่าธรรมเนียมสมาชิก'; $page = 'fees'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-cash-coin me-2"></i>ค่าธรรมเนียมสมาชิก</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                        <li class="breadcrumb-item active">ค่าธรรมเนียมสมาชิก</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Summary Cards -->
            <div class="row" id="summaryCards">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="summaryTotal">0</h3>
                            <p>รายการทั้งหมด</p>
                        </div>
                        <div class="icon"><i class="bi bi-receipt"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="summaryPending">0</h3>
                            <p>รอชำระ / รอตรวจสอบ</p>
                        </div>
                        <div class="icon"><i class="bi bi-hourglass-split"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="summaryPaid">0</h3>
                            <p>ชำระแล้ว</p>
                        </div>
                        <div class="icon"><i class="bi bi-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="summaryOverdue">0</h3>
                            <p>ค้างชำระ</p>
                        </div>
                        <div class="icon"><i class="bi bi-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>

            <!-- Filters & Actions -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label mb-0 small text-muted">ปี พ.ศ.</label>
                            <select class="form-control form-control-sm" id="filterYear">
                                <!-- populated by JS -->
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label mb-0 small text-muted">สถานะ</label>
                            <select class="form-control form-control-sm" id="filterStatus">
                                <option value="">ทั้งหมด</option>
                                <option value="pending">รอชำระ</option>
                                <option value="paid">ชำระแล้ว</option>
                                <option value="overdue">ค้างชำระ</option>
                                <option value="waived">ยกเว้น</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="form-label mb-0 small text-muted">ค้นหา</label>
                            <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="ชื่อ, อีเมล...">
                        </div>
                        <div class="col-md-3 text-md-right">
                            <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                            <button class="btn btn-primary btn-sm" onclick="showGenerateModal()">
                                <i class="bi bi-plus-circle me-1"></i> สร้างรายการค่าธรรมเนียม
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="16%">สมาชิก</th>
                                    <th width="8%">ประเภท</th>
                                    <th width="7%">รูปแบบ</th>
                                    <th width="7%">ปี พ.ศ.</th>
                                    <th width="8%" class="text-right">จำนวน (บาท)</th>
                                    <th width="8%">สถานะ</th>
                                    <th width="6%">หลักฐาน</th>
                                    <th width="9%">วันรับเงิน</th>
                                    <th width="10%">อนุมัติโดย</th>
                                    <th width="10%">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="feesTableBody">
                                <tr><td colspan="11" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <div id="feesPagination"></div>
                </div>
            </div>

        </div>
    </section>
</div>

<!-- Slip Preview Modal -->
<div class="modal fade" id="slipModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">หลักฐานการชำระ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="slipPreviewImg" src="" class="img-fluid rounded" style="max-height:500px" alt="slip">
            </div>
        </div>
    </div>
</div>

<!-- Generate Fees Modal -->
<div class="modal fade" id="generateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-1"></i> สร้างรายการค่าธรรมเนียมรายปี</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">ระบบจะสร้างรายการค่าธรรมเนียมให้สมาชิกที่ต้องชำระ (ตามประเภทสมาชิกที่มีค่าธรรมเนียม) สำหรับปีที่เลือก</p>
                <div class="form-group">
                    <label>ปี พ.ศ. ที่ต้องการสร้าง</label>
                    <select class="form-control" id="generateYear">
                        <!-- populated by JS -->
                    </select>
                </div>
                <div class="callout callout-info mt-3 mb-0">
                    <small><i class="bi bi-info-circle me-1"></i>
                    จำนวนเงินจะอ้างอิงจากหน้าตั้งค่าระบบ &mdash; สมาชิกที่มีรายการปีนี้แล้วจะไม่ถูกสร้างซ้ำ</small>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="btnGenerate" onclick="doGenerate()">
                    <i class="bi bi-check-lg me-1"></i> สร้างรายการ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject/Waive Modal -->
<div class="modal fade" id="actionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actionModalTitle">ดำเนินการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="actionInfo" class="mb-3"></div>
                <div class="form-group" id="receivedDateGroup" style="display:none">
                    <label>วันที่ได้รับเงิน <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="actionReceivedDate">
                </div>
                <div class="form-group">
                    <label>หมายเหตุ (ถ้ามี)</label>
                    <textarea class="form-control" id="actionNote" rows="3" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="btnDoAction">ยืนยัน</button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
$(function () {
    const currentBuddhistYear = new Date().getFullYear() + 543;

    // Populate year dropdowns
    function populateYears() {
        let opts = '';
        for (let y = currentBuddhistYear + 1; y >= currentBuddhistYear - 5; y--) {
            const sel = y === currentBuddhistYear ? 'selected' : '';
            opts += `<option value="${y}" ${sel}>${y}</option>`;
        }
        $('#filterYear').html('<option value="">ทุกปี</option>' + opts);
        $('#generateYear').html(opts);
    }
    populateYears();

    // Load data
    loadFees();
    loadSummary();

    // Filters
    $('#filterYear, #filterStatus').on('change', function () {
        loadFees(1);
        loadSummary();
    });

    let searchTimer;
    $('#filterSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadFees(1), 400);
    });
});

function getFeeBadge(status) {
    const map = {
        'pending': '<span class="badge badge-warning"><i class="bi bi-hourglass-split me-1"></i>รอชำระ</span>',
        'paid':    '<span class="badge badge-success"><i class="bi bi-check-circle me-1"></i>ชำระแล้ว</span>',
        'overdue': '<span class="badge badge-danger"><i class="bi bi-exclamation-triangle me-1"></i>ค้างชำระ</span>',
        'waived':  '<span class="badge badge-secondary"><i class="bi bi-dash-circle me-1"></i>ยกเว้น</span>',
    };
    return map[status] || `<span class="badge badge-light">${status}</span>`;
}

function getFeeTypeBadge(feeType) {
    const map = {
        'onetime': '<span class="badge badge-info"><i class="bi bi-1-circle me-1"></i>ครั้งเดียว</span>',
        'annual':  '<span class="badge badge-primary"><i class="bi bi-arrow-repeat me-1"></i>รายปี</span>',
    };
    return map[feeType] || `<span class="badge badge-light">${feeType}</span>`;
}

async function loadFees(page = 1) {
    const params = { page, per_page: 30 };
    const year = $('#filterYear').val();
    const status = $('#filterStatus').val();
    const search = $('#filterSearch').val().trim();
    if (year) params.year = year;
    if (status) params.status = status;
    if (search) params.search = search;

    const result = await API.getFees(params);
    const tbody = $('#feesTableBody');

    if (!result.success) {
        tbody.html(`<tr><td colspan="11" class="text-center text-danger py-3">${result.message}</td></tr>`);
        return;
    }

    if (!result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="11" class="text-center text-muted py-4">ไม่พบรายการค่าธรรมเนียม</td></tr>');
        $('#feesPagination').empty();
        return;
    }

    const startNum = ((result.pagination?.current_page || 1) - 1) * (result.pagination?.per_page || 30);
    let html = '';
    result.data.forEach((fee, i) => {
        const slipBtn = fee.payment_slip
            ? `<a href="#" onclick="previewSlip('${fee.payment_slip}'); return false;" class="btn btn-xs btn-outline-info"><i class="bi bi-image me-1"></i>ดูสลิป</a>`
            : '<span class="text-muted small">-</span>';

        const approvedBy = fee.approved_at
            ? `<small>${fee.approver_name || '-'}<br><span class="text-muted">${App.formatDate(fee.approved_at)}</span></small>`
            : '<span class="text-muted small">-</span>';

        let actions = '';
        if (fee.status === 'paid' && fee.payment_slip && !fee.approved_at) {
            // Has slip uploaded, waiting for admin approval
            actions += `<button class="btn btn-xs btn-success mr-1" onclick="feeAction(${fee.id}, 'approve', '${App.escHtml(fee.full_name)}')"><i class="bi bi-check-lg"></i> อนุมัติ</button>`;
            actions += `<button class="btn btn-xs btn-danger" onclick="feeAction(${fee.id}, 'reject', '${App.escHtml(fee.full_name)}')"><i class="bi bi-x-lg"></i></button>`;
        } else if (fee.status === 'pending' || fee.status === 'overdue') {
            actions += `<button class="btn btn-xs btn-success mr-1" onclick="feeAction(${fee.id}, 'approve', '${App.escHtml(fee.full_name)}')"><i class="bi bi-check-lg"></i> อนุมัติ</button>`;
            actions += `<button class="btn btn-xs btn-outline-secondary" onclick="feeAction(${fee.id}, 'waive', '${App.escHtml(fee.full_name)}')"><i class="bi bi-dash-circle"></i></button>`;
        }

        const receivedDate = fee.received_date ? App.formatDate(fee.received_date) : '<span class="text-muted small">-</span>';

        html += `<tr>
            <td>${startNum + i + 1}</td>
            <td>
                <strong>${fee.full_name || '-'}</strong>
                <br><small class="text-muted">${fee.email || ''}</small>
            </td>
            <td>${App.getMemberTypeBadge(fee.member_type)}</td>
            <td>${getFeeTypeBadge(fee.fee_type)}</td>
            <td>${fee.year || '-'}</td>
            <td class="text-right">${App.formatCurrency(fee.amount)}</td>
            <td>${getFeeBadge(fee.status)}</td>
            <td>${slipBtn}</td>
            <td><small>${receivedDate}</small></td>
            <td>${approvedBy}</td>
            <td>${actions}</td>
        </tr>`;
    });

    tbody.html(html);

    if (result.pagination) {
        App.buildPagination('#feesPagination', result.pagination, loadFees);
    }
}

async function loadSummary() {
    const year = $('#filterYear').val() || (new Date().getFullYear() + 543);
    const result = await API.getFeeSummary(year);
    if (!result.success) return;
    const s = result.data;
    const total = (s.pending || 0) + (s.paid || 0) + (s.overdue || 0) + (s.waived || 0);
    $('#summaryTotal').text(total);
    $('#summaryPending').text(s.pending || 0);
    $('#summaryPaid').text(s.paid || 0);
    $('#summaryOverdue').text(s.overdue || 0);
}

function previewSlip(url) {
    const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
    $('#slipPreviewImg').attr('src', src);
    $('#slipModal').modal('show');
}

function showGenerateModal() {
    $('#generateModal').modal('show');
}

async function doGenerate() {
    const year = parseInt($('#generateYear').val());
    const btn = $('#btnGenerate');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังสร้าง...');

    const result = await API.generateFees(year);
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> สร้างรายการ');

    if (result.success) {
        App.success(result.message);
        $('#generateModal').modal('hide');
        loadFees(1);
        loadSummary();
    } else {
        App.error(result.message);
    }
}

let currentFeeAction = {};
function feeAction(feeId, action, memberName) {
    currentFeeAction = { feeId, action };
    
    const titles = {
        'approve': 'อนุมัติการชำระ',
        'reject': 'ปฏิเสธการชำระ',
        'waive': 'ยกเว้นค่าธรรมเนียม'
    };
    const infos = {
        'approve': `<div class="alert alert-success"><i class="bi bi-check-circle me-1"></i> อนุมัติการชำระค่าธรรมเนียมของ <strong>${memberName}</strong></div>`,
        'reject': `<div class="alert alert-danger"><i class="bi bi-x-circle me-1"></i> ปฏิเสธการชำระค่าธรรมเนียมของ <strong>${memberName}</strong><br><small>รายการจะกลับเป็นสถานะรอชำระ</small></div>`,
        'waive': `<div class="alert alert-secondary"><i class="bi bi-dash-circle me-1"></i> ยกเว้นค่าธรรมเนียมของ <strong>${memberName}</strong><br><small>สมาชิกจะไม่ต้องชำระค่าธรรมเนียมในปีนี้</small></div>`,
    };
    const btnClasses = { 'approve': 'btn-success', 'reject': 'btn-danger', 'waive': 'btn-secondary' };

    $('#actionModalTitle').text(titles[action] || 'ดำเนินการ');
    $('#actionInfo').html(infos[action] || '');
    $('#actionNote').val('');
    // Show received_date field only for approve action
    if (action === 'approve') {
        $('#receivedDateGroup').show();
        $('#actionReceivedDate').val(new Date().toISOString().slice(0,10));
    } else {
        $('#receivedDateGroup').hide();
        $('#actionReceivedDate').val('');
    }
    $('#btnDoAction').attr('class', 'btn ' + (btnClasses[action] || 'btn-primary')).text('ยืนยัน');
    $('#actionModal').modal('show');
}

$('#btnDoAction').on('click', async function () {
    const btn = $(this);
    btn.prop('disabled', true);
    const note = $('#actionNote').val().trim();
    const receivedDate = $('#actionReceivedDate').val();
    if (currentFeeAction.action === 'approve' && !receivedDate) {
        App.error('กรุณาระบุวันที่ได้รับเงิน');
        btn.prop('disabled', false);
        return;
    }
    const result = await API.approveFee(currentFeeAction.feeId, currentFeeAction.action, note, receivedDate);
    btn.prop('disabled', false);

    if (result.success) {
        App.success(result.message);
        $('#actionModal').modal('hide');
        loadFees();
        loadSummary();
    } else {
        App.error(result.message);
    }
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
