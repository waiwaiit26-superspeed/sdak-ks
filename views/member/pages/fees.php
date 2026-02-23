<?php $pageTitle = 'ค่าธรรมเนียมสมาชิก'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-cash-coin me-2"></i>ค่าธรรมเนียมสมาชิก</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">ค่าธรรมเนียม</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">

                    <!-- Membership Start Date Info -->
                    <div id="membershipStartInfo" class="alert alert-info align-items-center mb-4" style="display:none">
                        <i class="bi bi-person-badge me-2" style="font-size:1.3em"></i>
                        <span>สมาชิกตั้งแต่: <strong id="membershipStartDate">-</strong></span>
                    </div>

                    <!-- Current Fee Status -->
                    <div id="feeCurrentCard" class="mb-4" style="display:none">
                        <div class="card shadow-sm">
                            <div class="card-header bg-gradient-primary text-white">
                                <h5 class="card-title mb-0"><i class="bi bi-calendar-check me-1"></i> <span id="feeCardTitle">สถานะค่าธรรมเนียม</span></h5>
                            </div>
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center mb-3 mb-md-0">
                                        <h2 id="feeCurrentYear" class="text-primary mb-1">-</h2>
                                        <div id="feeCurrentStatus"></div>
                                    </div>
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <p class="mb-1 text-muted small">จำนวนที่ต้องชำระ</p>
                                        <h3 id="feeCurrentAmount" class="mb-0 text-dark">-</h3>
                                    </div>
                                    <div class="col-md-5 text-md-right">
                                        <div id="feeCurrentActions"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Not Required -->
                    <div id="feeNotRequired" class="text-center py-5" style="display:none">
                        <i class="bi bi-check-circle display-3 text-success"></i>
                        <p class="mt-3 h5 text-muted">ประเภทสมาชิกของคุณไม่ต้องชำระค่าธรรมเนียม</p>
                    </div>

                    <!-- One-time Paid -->
                    <div id="feeOnetimePaid" class="text-center py-5" style="display:none">
                        <i class="bi bi-patch-check display-3 text-success"></i>
                        <p class="mt-3 h5 text-success fw-bold">ชำระค่าธรรมเนียมเรียบร้อยแล้ว (จ่ายครั้งเดียว)</p>
                    </div>

                    <!-- Bank Info -->
                    <div id="feeBankInfo" class="mb-4" style="display:none">
                        <div class="card shadow-sm border-info">
                            <div class="card-header bg-gradient-info text-white">
                                <h5 class="card-title mb-0"><i class="bi bi-bank me-1"></i> ข้อมูลบัญชีสำหรับโอน</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">ธนาคาร</small>
                                        <strong id="bankName">-</strong>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">ชื่อบัญชี</small>
                                        <strong id="bankAccName">-</strong>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small class="text-muted d-block">เลขที่บัญชี</small>
                                        <strong id="bankAccNumber" class="text-primary" style="font-size:1.1em">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Upload Slip -->
                    <div id="uploadSlipSection" class="mb-4" style="display:none">
                        <div class="card shadow-sm border-primary">
                            <div class="card-header bg-gradient-primary text-white">
                                <h5 class="card-title mb-0"><i class="bi bi-upload me-1"></i> อัปโหลดหลักฐานการชำระ</h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <img id="slipPreview" src="" class="img-fluid rounded border" style="max-height:350px;display:none" alt="preview slip">
                                </div>
                                <div class="text-center mb-3">
                                    <label for="slipFileInput" class="btn btn-outline-primary btn-lg">
                                        <i class="bi bi-image me-2"></i>เลือกไฟล์สลิป
                                    </label>
                                    <input type="file" id="slipFileInput" accept="image/*" class="d-none">
                                </div>
                                <div class="text-center">
                                    <button class="btn btn-primary btn-lg px-5" id="btnSubmitSlip" disabled>
                                        <i class="bi bi-send me-1"></i> ส่งหลักฐานการชำระ
                                    </button>
                                </div>
                                <p class="text-center text-muted small mt-2 mb-0">รองรับไฟล์ JPG, PNG ขนาดไม่เกิน 10 MB</p>
                            </div>
                        </div>
                    </div>

                    <!-- Fee History -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-clock-history me-1"></i> ประวัติค่าธรรมเนียม</h5>
                        </div>
                        <div class="card-body p-0">
                            <div id="feeHistoryList">
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

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<script>
let currentFeeId = null;

$(function () {
    App.requireLogin();
    loadCurrentFee();
    loadMembershipStart();

    // ─ If redirected from registration, show welcome notification ─
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('from') === 'register') {
        Swal.fire({
            icon: 'info',
            title: 'ยินดีต้อนรับสมาชิกใหม่! 🎉',
            html: 'กรุณาอัปโหลดหลักฐานการชำระค่าธรรมเนียม<br>เพื่อให้ทางสมาคมตรวจสอบและอนุมัติบัญชี',
            confirmButtonText: 'เข้าใจแล้ว',
            confirmButtonColor: '#6d28d9',
        });
        // Clean URL
        window.history.replaceState({}, '', window.location.pathname + '?page=fees');
    }
});

async function loadMembershipStart() {
    const result = await API.getProfile();
    if (result.success && result.data) {
        const u = result.data;
        if (u.approved_at) {
            $('#membershipStartDate').text(App.formatDate(u.approved_at));
            $('#membershipStartInfo').css('display', 'flex');
        }
    }
}

async function loadCurrentFee() {
    const result = await API.getMyCurrentFee();
    if (!result.success) return;
    const fee = result.data;

    // Hide all sections first
    $('#feeNotRequired, #feeOnetimePaid, #feeCurrentCard, #feeBankInfo, #uploadSlipSection').hide();

    if (fee.status === 'not_required' || fee.fee_mode === 'none') {
        $('#feeNotRequired').show();
        loadFeeHistory();
        return;
    }

    // One-time: already paid & approved
    if (fee.fee_mode === 'onetime' && fee.status === 'paid' && fee.approved_at) {
        $('#feeOnetimePaid').show();
        loadFeeHistory();
        return;
    }

    // Set card title
    if (fee.fee_mode === 'onetime') {
        $('#feeCardTitle').text('ค่าธรรมเนียม (จ่ายครั้งเดียว)');
        $('#feeCurrentYear').text('ครั้งเดียว');
    } else {
        $('#feeCardTitle').text('สถานะค่าธรรมเนียมปีปัจจุบัน');
        $('#feeCurrentYear').text('พ.ศ. ' + fee.year);
    }

    $('#feeCurrentCard').show();
    $('#feeCurrentAmount').text(App.formatCurrency(fee.amount));

    // Bank info
    if (fee.bank_info && fee.bank_info.bank_name) {
        $('#bankName').text(fee.bank_info.bank_name);
        $('#bankAccName').text(fee.bank_info.account_name);
        $('#bankAccNumber').text(fee.bank_info.account_number);
        $('#feeBankInfo').show();
    }

    const statusMap = {
        'not_created': '<span class="badge badge-info badge-pill px-3 py-2"><i class="bi bi-info-circle me-1"></i>ยังไม่สร้างรายการ</span>',
        'pending': '<span class="badge badge-warning badge-pill px-3 py-2"><i class="bi bi-hourglass-split me-1"></i>รอชำระ</span>',
        'paid':    '<span class="badge badge-success badge-pill px-3 py-2"><i class="bi bi-check-circle me-1"></i>ชำระแล้ว</span>',
        'overdue': '<span class="badge badge-danger badge-pill px-3 py-2"><i class="bi bi-exclamation-triangle me-1"></i>ค้างชำระ</span>',
        'waived':  '<span class="badge badge-secondary badge-pill px-3 py-2"><i class="bi bi-dash-circle me-1"></i>ยกเว้น</span>',
    };
    $('#feeCurrentStatus').html(statusMap[fee.status] || fee.status);

    // Actions
    let actionsHtml = '';
    const fromRegister = new URLSearchParams(window.location.search).get('from') === 'register';
    if (fee.status === 'not_created') {
        // If from registration, auto-create fee record
        if (fromRegister) {
            actionsHtml = '<div class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span> กำลังสร้างรายการ...</div>';
            $('#feeCurrentActions').html(actionsHtml);
            createAndPay();
            return;
        }
        actionsHtml = `<button class="btn btn-primary" onclick="createAndPay()"><i class="bi bi-plus-circle me-1"></i> สร้างรายการชำระ</button>`;
    } else if (fee.status === 'pending' || fee.status === 'overdue') {
        currentFeeId = fee.id;
        actionsHtml = `<button class="btn btn-success" onclick="showUploadSlip()"><i class="bi bi-upload me-1"></i> อัปโหลดสลิป</button>`;
        // If from registration, auto-show upload section
        if (fromRegister) {
            setTimeout(function() { showUploadSlip(); }, 500);
        }
    } else if (fee.status === 'paid' && !fee.approved_at) {
        actionsHtml = '<div class="text-info"><i class="bi bi-hourglass-split me-1"></i> รอตรวจสอบจากเจ้าหน้าที่</div>';
    } else if (fee.status === 'paid') {
        actionsHtml = '<div class="text-success"><i class="bi bi-check-circle me-1"></i> อนุมัติแล้ว</div>';
    }
    $('#feeCurrentActions').html(actionsHtml);

    loadFeeHistory();
}

async function loadFeeHistory() {
    const container = $('#feeHistoryList');
    const result = await API.getMyFees();
    if (!result.success || !result.data || result.data.length === 0) {
        container.html('<p class="text-center text-muted py-4">ยังไม่มีประวัติค่าธรรมเนียม</p>');
        return;
    }

    const statusMap = {
        'pending': '<span class="badge badge-warning">รอชำระ</span>',
        'paid':    '<span class="badge badge-success">ชำระแล้ว</span>',
        'overdue': '<span class="badge badge-danger">ค้างชำระ</span>',
        'waived':  '<span class="badge badge-secondary">ยกเว้น</span>',
    };
    const feeTypeLabels = { 'onetime': 'ครั้งเดียว', 'annual': 'รายปี' };

    let html = '<div class="table-responsive"><table class="table table-sm table-hover mb-0"><thead class="table-light"><tr><th>ปี พ.ศ.</th><th>รูปแบบ</th><th>จำนวน</th><th>สถานะ</th><th>วันที่ชำระ</th><th>วันรับเงิน</th><th>สลิป</th><th>หมายเหตุ</th></tr></thead><tbody>';
    result.data.forEach(f => {
        // Slip column
        let slipHtml = '-';
        const isApproved = !!f.approved_at;
        if (f.payment_slip) {
            const slipSrc = f.payment_slip.startsWith('http') ? f.payment_slip : (BASE_PATH + f.payment_slip);
            slipHtml = `<img src="${slipSrc}" class="img-thumbnail" style="max-height:60px;cursor:pointer" onclick="viewSlip('${slipSrc}')" title="คลิกเพื่อดูขนาดเต็ม">`;
            if (!isApproved && f.status === 'paid') {
                slipHtml += `<br><button class="btn btn-outline-warning btn-xs mt-1" onclick="reuploadSlip(${f.id})"><i class="bi bi-arrow-repeat"></i> เปลี่ยนสลิป</button>`;
            }
        } else if (!isApproved && (f.status === 'pending' || f.status === 'overdue')) {
            slipHtml = `<button class="btn btn-outline-primary btn-xs" onclick="reuploadSlip(${f.id})"><i class="bi bi-upload"></i> อัปโหลด</button>`;
        }

        html += `<tr>
            <td>${f.year || '-'}</td>
            <td><span class="badge badge-${f.fee_type === 'onetime' ? 'info' : 'primary'}">${feeTypeLabels[f.fee_type] || f.fee_type}</span></td>
            <td>${App.formatCurrency(f.amount)}</td>
            <td>${statusMap[f.status] || f.status}</td>
            <td>${f.paid_at ? App.formatDate(f.paid_at) : '-'}</td>
            <td>${f.received_date ? App.formatDate(f.received_date) : '-'}</td>
            <td class="text-center">${slipHtml}</td>
            <td>${f.note || '-'}</td>
        </tr>`;
    });
    html += '</tbody></table></div>';
    container.html(html);
}

function showUploadSlip() {
    $('#uploadSlipSection').slideDown();
    $('html, body').animate({ scrollTop: $('#uploadSlipSection').offset().top - 80 }, 400);
}

async function createAndPay() {
    const result = await API.createMyFee();
    if (result.success) {
        currentFeeId = result.data.fee_id;
        App.success(result.message);
        await loadCurrentFee();
        showUploadSlip();
    } else {
        App.error(result.message);
    }
}

// ─── View slip full size ───
function viewSlip(src) {
    Swal.fire({
        imageUrl: src,
        imageAlt: 'สลิปการชำระเงิน',
        showConfirmButton: true,
        confirmButtonText: 'ปิด',
        width: 'auto',
        padding: '1rem',
    });
}

// ─── Re-upload slip for a specific fee ───
function reuploadSlip(feeId) {
    Swal.fire({
        title: 'อัปโหลดสลิปใหม่',
        html: `<p class="text-muted small mb-3">เลือกไฟล์สลิปใหม่เพื่อทดแทนสลิปเดิม</p>
               <input type="file" id="swalSlipFile" accept="image/jpeg,image/png,image/webp" class="form-control">
               <img id="swalSlipPreview" src="" class="img-fluid rounded mt-3" style="max-height:300px;display:none">`,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-upload"></i> อัปโหลด',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#3085d6',
        didOpen: () => {
            document.getElementById('swalSlipFile').addEventListener('change', function() {
                const file = this.files[0];
                if (!file) return;
                if (file.size > 10 * 1024 * 1024) {
                    App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)');
                    this.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = e => {
                    const prev = document.getElementById('swalSlipPreview');
                    prev.src = e.target.result;
                    prev.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });
        },
        preConfirm: async () => {
            const fileInput = document.getElementById('swalSlipFile');
            if (!fileInput.files[0]) {
                Swal.showValidationMessage('กรุณาเลือกไฟล์สลิป');
                return false;
            }
            Swal.showLoading();
            try {
                const uploadResult = await API.upload(fileInput.files[0], 'slips');
                if (!uploadResult.success) throw new Error(uploadResult.message);
                const result = await API.uploadFeeSlip(feeId, uploadResult.data.url);
                if (!result.success) throw new Error(result.message);
                return result;
            } catch (err) {
                Swal.showValidationMessage(err.message);
                return false;
            }
        }
    }).then(result => {
        if (result.isConfirmed && result.value) {
            App.success('อัปโหลดสลิปสำเร็จ รอการตรวจสอบ');
            loadCurrentFee();
        }
    });
}

// Slip upload handling
$('#slipFileInput').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) {
        App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)');
        this.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = e => {
        $('#slipPreview').attr('src', e.target.result).show();
    };
    reader.readAsDataURL(file);
    $('#btnSubmitSlip').prop('disabled', false);
});

$('#btnSubmitSlip').on('click', async function () {
    const fileInput = document.getElementById('slipFileInput');
    if (!fileInput.files[0] || !currentFeeId) return;

    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> กำลังอัปโหลด...');

    // Upload file first
    const uploadResult = await API.upload(fileInput.files[0], 'slips');
    if (!uploadResult.success) {
        App.error(uploadResult.message);
        btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> ส่งหลักฐานการชำระ');
        return;
    }

    // Submit slip to fee record
    const result = await API.uploadFeeSlip(currentFeeId, uploadResult.data.url);
    btn.prop('disabled', false).html('<i class="bi bi-send me-1"></i> ส่งหลักฐานการชำระ');

    if (result.success) {
        App.success('ส่งหลักฐานการชำระสำเร็จ กรุณารอการตรวจสอบ');
        $('#uploadSlipSection').slideUp();
        await loadCurrentFee();
    } else {
        App.error(result.message);
    }
});
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
