<?php $pageTitle = 'รายละเอียดกิจกรรม'; ?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<style>
@keyframes slipBtnPulse {
    0%, 100% { box-shadow: 0 4px 15px rgba(220, 53, 69, 0.4); }
    50% { box-shadow: 0 4px 25px rgba(220, 53, 69, 0.7); }
}
.slip-upload-btn-web {
    animation: slipBtnPulse 2s ease-in-out infinite;
    font-weight: bold;
}
.slip-upload-btn-web:hover {
    transform: scale(1.03);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.6) !important;
}
.slip-alert-box {
    border: 2px solid #dc3545 !important;
    border-radius: 10px;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%) !important;
}
</style>

<div class="container py-5">
    <div id="activityDetail">
        <div class="text-center py-5">
            <span class="spinner-border text-primary"></span>
            <p class="mt-2 text-muted">กำลังโหลดกิจกรรม...</p>
        </div>
    </div>
</div>

<!-- Modal: Payment Slip Upload -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">อัปโหลดสลิปการชำระเงิน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="paymentFeeAmount" class="alert alert-warning text-center mb-3 py-2" style="display:none"></div>
                <div id="paymentBankInfo" class="card border-info mb-3" style="display:none">
                    <div class="card-header bg-info text-white py-2"><i class="bi bi-bank me-1"></i> ข้อมูลบัญชีสำหรับโอน</div>
                    <div class="card-body py-2">
                        <table class="table table-sm table-borderless mb-0">
                            <tr id="payBankNameRow" style="display:none"><td class="text-muted" style="width:35%">ธนาคาร</td><td><strong id="payBankName"></strong></td></tr>
                            <tr id="payAccNameRow" style="display:none"><td class="text-muted">ชื่อบัญชี</td><td><strong id="payAccName"></strong></td></tr>
                            <tr id="payAccNumRow" style="display:none"><td class="text-muted">เลขที่บัญชี</td><td><strong id="payAccNum" class="text-primary" style="font-size:1.1em;letter-spacing:1px"></strong></td></tr>
                        </table>
                    </div>
                </div>
                <p class="text-muted small">กรุณาอัปโหลดสลิปการโอนเงินค่าลงทะเบียน</p>
                <input type="file" id="paymentSlipInput" class="form-control" accept="image/*">
                <div id="slipPreview" class="mt-3 text-center" style="display:none">
                    <img id="slipImg" src="" class="img-fluid rounded" style="max-height:300px" alt="slip">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnConfirmRegister">
                    <i class="bi bi-check-lg me-1"></i> ลงทะเบียน
                </button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const actId = urlParams.get('id');
    if (!actId) { window.location.href = './?page=activities'; return; }

    let activityData = null;
    let uploadedSlip = null;
    let bankInfo = { bank_name: '', account_name: '', account_number: '' };

    // Load bank info
    (async function() {
        const res = await API.getSettings();
        if (res.success && res.data) {
            bankInfo = {
                bank_name: res.data.bank_name || '',
                account_name: res.data.bank_account_name || '',
                account_number: res.data.bank_account_number || ''
            };
        }
    })();

    async function loadDetail() {
        const result = await API.getActivityDetail(actId);
        const container = $('#activityDetail');

        if (!result.success || !result.data) {
            container.html(`
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-circle fs-1 text-muted"></i>
                    <p class="mt-2">ไม่พบกิจกรรมที่ต้องการ</p>
                    <a href="./?page=activities" class="btn btn-outline-primary">กลับไปหน้ากิจกรรม</a>
                </div>
            `);
            return;
        }

        activityData = result.data;
        const a = activityData;
        const startDate = App.formatDateTime(a.start_date);
        const endDate = a.end_date ? App.formatDateTime(a.end_date) : '-';
        const fee = a.has_fee && a.fee_amount > 0 ? App.formatCurrency(a.fee_amount) : 'ฟรี';
        const coverImg = a.cover_image ? `<img src="${App.imgUrl(a.cover_image)}" class="img-fluid rounded mb-4" alt="${a.title}" onerror="this.style.display='none'">` : '';

        // ─── Restricted content for non-members ───
        if (a.is_restricted) {
            const restrictionMsg = a.restriction_text || 'กิจกรรมนี้สำหรับสมาชิกสมาคมเท่านั้น';
            container.html(`
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../">หน้าแรก</a></li>
                        <li class="breadcrumb-item"><a href="./?page=activities">กิจกรรม</a></li>
                        <li class="breadcrumb-item active">${a.title}</li>
                    </ol>
                </nav>
                ${coverImg}
                <h1 class="mb-3">${a.title}</h1>

                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card mb-3 border-warning">
                            <div class="card-body text-center py-5">
                                <i class="bi bi-lock-fill display-3 text-warning mb-3"></i>
                                <h4 class="text-warning mb-3">${App.escapeHtml(restrictionMsg)}</h4>
                                <p class="text-muted mb-4">กรุณาสมัครสมาชิกเพื่อดูรายละเอียดและลงทะเบียนเข้าร่วมกิจกรรม</p>
                                <div class="d-flex justify-content-center gap-2">
                                    ${API.isLoggedIn()
                                        ? '<span class="text-muted">คุณยังไม่ได้เป็นสมาชิกสมาคม</span>'
                                        : `<a href="../auth/?page=login&redirect=${encodeURIComponent('web/?page=activity-detail&id=' + a.id)}" class="btn btn-outline-primary">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบ
                                          </a>
                                          <a href="../auth/?page=register" class="btn btn-primary">
                                            <i class="bi bi-person-plus me-1"></i> สมัครสมาชิก
                                          </a>`
                                    }
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h6><i class="bi bi-info-circle me-1"></i> ข้อมูลกิจกรรม</h6>
                                <ul class="list-unstyled mb-0">
                                    <li class="mb-2"><i class="bi bi-calendar text-primary me-2"></i><strong>เริ่ม:</strong> ${startDate}</li>
                                    <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i><strong>สิ้นสุด:</strong> ${endDate}</li>
                                    <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><strong>สถานที่:</strong> ${a.location || '-'}</li>
                                    <li class="mb-2"><i class="bi bi-person-badge text-primary me-2"></i><strong>รับสมาชิก:</strong> ${(() => {
                                        const mtl = { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
                                        if (a.allowed_member_types) return a.allowed_member_types.split(',').map(t => mtl[t.trim()] || t.trim()).join(', ');
                                        if (a.visibility === 'members_only') return 'เฉพาะสมาชิก (ทุกประเภท)';
                                        return 'เปิดรับทุกประเภท';
                                    })()}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="./?page=activities" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> กลับไปหน้ากิจกรรม
                </a>
            `);
            document.title = a.title + ' - <?php echo SITE_NAME_SHORT; ?>';
            return;
        }

        // Registration button logic
        let regBtn = '';
        const canRegister = a.status === 'open' && a.registration_open;

        if (!canRegister) {
            // Activity not open for registration
            if (a.status === 'closed') {
                regBtn = '<button class="btn btn-secondary btn-lg" disabled><i class="bi bi-check-circle me-1"></i> กิจกรรมจบแล้ว</button>';
            } else if (a.status === 'cancelled') {
                regBtn = '<button class="btn btn-dark btn-lg" disabled><i class="bi bi-x-circle me-1"></i> กิจกรรมถูกยกเลิก</button>';
            } else if (a.status === 'open' && !a.registration_open) {
                regBtn = '<button class="btn btn-warning btn-lg" disabled><i class="bi bi-pause-circle me-1"></i> ปิดรับสมัครแล้ว</button>';
            } else {
                regBtn = '<button class="btn btn-secondary btn-lg" disabled><i class="bi bi-info-circle me-1"></i> ยังไม่เปิดรับสมัคร</button>';
            }
        } else if (API.isLoggedIn()) {
            if (a.my_registration) {
                const st = a.my_registration.status;
                const needsSlip = a.has_fee && a.my_registration.payment_status === 'pending' && !a.my_registration.payment_proof;
                if (st === 'pending') {
                    if (needsSlip) {
                        regBtn = `<div class="slip-alert-box p-3 mb-2">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-exclamation-triangle-fill text-danger me-2" style="font-size:1.5rem;"></i>
                                        <strong class="text-danger">ยังไม่ได้อัพโหลดสลิปโอนเงิน</strong>
                                    </div>
                                    <p class="small text-muted mb-2">คุณลงทะเบียนแล้ว แต่ยังไม่ได้แนบหลักฐานการโอนเงิน กรุณาอัพโหลดสลิป</p>
                                    <a href="../member/?page=profile" class="btn btn-danger btn-lg w-100 slip-upload-btn-web" id="btnGoUploadSlip">
                                        <i class="bi bi-cloud-arrow-up me-2" style="font-size:1.3rem;"></i> อัพโหลดสลิปโอนเงินตอนนี้
                                    </a>
                                  </div>
                                  <button class="btn btn-outline-danger btn-sm mt-2" id="btnCancelReg"><i class="bi bi-x-circle me-1"></i> ยกเลิกการลงทะเบียน</button>`;
                    } else {
                        regBtn = `<button class="btn btn-warning" disabled><i class="bi bi-clock me-1"></i> รอการอนุมัติ</button>
                                  <button class="btn btn-outline-danger btn-sm ms-2" id="btnCancelReg">ยกเลิกการลงทะเบียน</button>`;
                    }
                } else if (st === 'approved') {
                    regBtn = `<button class="btn btn-success" disabled><i class="bi bi-check-circle me-1"></i> ลงทะเบียนแล้ว</button>`;
                } else {
                    regBtn = `<button class="btn btn-secondary" disabled><i class="bi bi-x-circle me-1"></i> การลงทะเบียนถูกปฏิเสธ</button>`;
                }
            } else {
                const isFull = a.max_participants > 0 && a.approved_count >= a.max_participants;
                // Check member type eligibility
                const memberTypeLabels = { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
                let memberTypeRestricted = false;
                if (a.allowed_member_types) {
                    const allowedTypes = a.allowed_member_types.split(',').map(t => t.trim());
                    const userType = API.getUser()?.member_type || '';
                    if (!allowedTypes.includes(userType)) {
                        memberTypeRestricted = true;
                    }
                }
                if (memberTypeRestricted) {
                    const typeNames = a.allowed_member_types.split(',').map(t => memberTypeLabels[t.trim()] || t.trim()).join(', ');
                    regBtn = `<button class="btn btn-secondary" disabled><i class="bi bi-lock me-1"></i> เฉพาะสมาชิกประเภท: ${typeNames}</button>`;
                } else if (isFull) {
                    regBtn = `<button class="btn btn-secondary" disabled><i class="bi bi-x-circle me-1"></i> เต็มจำนวนแล้ว</button>`;
                } else {
                    regBtn = `<button class="btn btn-primary btn-lg" id="btnRegister"><i class="bi bi-pencil-square me-1"></i> ลงทะเบียนเข้าร่วม</button>`;
                }
            }
        } else {
            regBtn = `<a href="../auth/?page=login&redirect=${encodeURIComponent('web/?page=activity-detail&id=' + a.id)}" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-1"></i> เข้าสู่ระบบเพื่อลงทะเบียน
                      </a>`;
        }

        // Status badge for header
        let headerStatusBadge = '';
        if (a.status === 'open' && a.registration_open) {
            headerStatusBadge = '<span class="badge bg-success fs-6 ms-2"><i class="bi bi-door-open me-1"></i>เปิดรับสมัคร</span>';
        } else if (a.status === 'open' && !a.registration_open) {
            headerStatusBadge = '<span class="badge bg-warning text-dark fs-6 ms-2"><i class="bi bi-pause-circle me-1"></i>ปิดรับสมัคร</span>';
        } else if (a.status === 'closed') {
            headerStatusBadge = '<span class="badge bg-secondary fs-6 ms-2"><i class="bi bi-check-circle me-1"></i>จบแล้ว</span>';
        } else if (a.status === 'cancelled') {
            headerStatusBadge = '<span class="badge bg-dark fs-6 ms-2"><i class="bi bi-x-circle me-1"></i>ยกเลิก</span>';
        }

        // Member type info line for sidebar
        const memberTypeLabelsDetail = { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
        let memberTypeLine = '';
        if (a.visibility === 'members_only' && !a.allowed_member_types) {
            memberTypeLine = 'เฉพาะสมาชิกสมาคม (ทุกประเภท)';
        } else if (a.allowed_member_types) {
            memberTypeLine = a.allowed_member_types.split(',').map(t => memberTypeLabelsDetail[t.trim()] || t.trim()).join(', ');
        } else {
            memberTypeLine = 'เปิดรับทุกประเภท';
        }

        container.html(`
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../">หน้าแรก</a></li>
                    <li class="breadcrumb-item"><a href="./?page=activities">กิจกรรม</a></li>
                    <li class="breadcrumb-item active">${a.title}</li>
                </ol>
            </nav>
            ${coverImg}
            <h1 class="mb-3">${a.title} ${headerStatusBadge}</h1>

            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title mb-3">รายละเอียดกิจกรรม</h5>
                            <div class="activity-content">${a.description || '<p class="text-muted">ไม่มีรายละเอียด</p>'}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6><i class="bi bi-info-circle me-1"></i> ข้อมูลกิจกรรม</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2"><i class="bi bi-calendar text-primary me-2"></i><strong>เริ่ม:</strong> ${startDate}</li>
                                <li class="mb-2"><i class="bi bi-calendar-check text-primary me-2"></i><strong>สิ้นสุด:</strong> ${endDate}</li>
                                <li class="mb-2"><i class="bi bi-geo-alt text-primary me-2"></i><strong>สถานที่:</strong> ${a.location || '-'}</li>
                                <li class="mb-2"><i class="bi bi-cash text-primary me-2"></i><strong>ค่าลงทะเบียน:</strong> ${fee}</li>
                                <li class="mb-2"><i class="bi bi-people text-primary me-2"></i><strong>ผู้เข้าร่วม:</strong> ${a.approved_count || 0}${a.max_participants > 0 ? '/' + a.max_participants : ''} คน</li>
                                <li class="mb-2"><i class="bi bi-person-badge text-primary me-2"></i><strong>รับสมาชิก:</strong> ${memberTypeLine}</li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        ${regBtn}
                    </div>
                </div>
            </div>

            <a href="./?page=activities" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> กลับไปหน้ากิจกรรม
            </a>
        `);

        document.title = a.title + ' - <?php echo SITE_NAME_SHORT; ?>';
        bindEvents();
    }

    function bindEvents() {
        // Register
        $(document).off('click', '#btnRegister').on('click', '#btnRegister', function () {
            if (activityData.has_fee && activityData.fee_amount > 0) {
                // Populate modal with fee amount and bank info
                const feeAmt = App.formatCurrency(activityData.fee_amount);
                $('#paymentFeeAmount').html('<i class="bi bi-cash-coin me-1"></i> <strong>\u0e22\u0e2d\u0e14\u0e17\u0e35\u0e48\u0e15\u0e49\u0e2d\u0e07\u0e0a\u0e33\u0e23\u0e30: <span class="text-danger" style="font-size:1.3em">' + feeAmt + '</span></strong>').show();

                if (bankInfo.bank_name || bankInfo.account_number) {
                    if (bankInfo.bank_name) { $('#payBankName').text(bankInfo.bank_name); $('#payBankNameRow').show(); } else { $('#payBankNameRow').hide(); }
                    if (bankInfo.account_name) { $('#payAccName').text(bankInfo.account_name); $('#payAccNameRow').show(); } else { $('#payAccNameRow').hide(); }
                    if (bankInfo.account_number) { $('#payAccNum').text(bankInfo.account_number); $('#payAccNumRow').show(); } else { $('#payAccNumRow').hide(); }
                    $('#paymentBankInfo').show();
                } else {
                    $('#paymentBankInfo').hide();
                }

                // Show payment modal
                new bootstrap.Modal('#paymentModal').show();
            } else {
                doRegister(null);
            }
        });

        // Go to member profile to upload slip
        $(document).off('click', '#btnGoUploadSlip').on('click', '#btnGoUploadSlip', function () {
            sessionStorage.setItem('openTab', 'tabActivities');
            sessionStorage.setItem('openActivityId', activityData.id);
        });

        // Cancel registration
        $(document).off('click', '#btnCancelReg').on('click', '#btnCancelReg', async function () {
            const ok = await App.confirm('ต้องการยกเลิกการลงทะเบียนหรือไม่?');
            if (!ok) return;
            const result = await API.cancelActivityRegistration(activityData.my_registration.id);
            if (result.success) {
                App.success('ยกเลิกการลงทะเบียนสำเร็จ');
                loadDetail();
            } else {
                App.error(result.message);
            }
        });
    }

    // Payment slip preview
    $('#paymentSlipInput').on('change', async function () {
        const file = this.files[0];
        if (!file) return;
        // Preview
        const reader = new FileReader();
        reader.onload = e => {
            $('#slipImg').attr('src', e.target.result);
            $('#slipPreview').show();
        };
        reader.readAsDataURL(file);

        // Upload
        const result = await API.upload(file, 'payments');
        if (result.success) {
            uploadedSlip = result.data.url;
        } else {
            App.error('อัปโหลดสลิปไม่สำเร็จ: ' + result.message);
        }
    });

    // Confirm register with payment
    $('#btnConfirmRegister').on('click', function () {
        bootstrap.Modal.getInstance('#paymentModal').hide();
        doRegister(uploadedSlip);
    });

    async function doRegister(paymentSlip) {
        const result = await API.registerActivity(actId, paymentSlip);
        if (result.success) {
            App.success('ลงทะเบียนสำเร็จ รอการอนุมัติ');
            loadDetail();
        } else {
            App.error(result.message);
        }
    }

    loadDetail();
});
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
