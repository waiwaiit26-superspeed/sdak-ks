<?php $pageTitle = 'หน้าหลัก'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="bi bi-house-door me-2"></i>สวัสดี, <span id="dashUserName">สมาชิก</span></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">หน้าหลัก</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">
            <!-- Quick Stats Row -->
            <div class="row mb-4" id="dashStats" style="display:none;">
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner">
                            <h3 id="statActivities">0</h3>
                            <p>กิจกรรมที่ลงทะเบียน</p>
                        </div>
                        <div class="icon"><i class="bi bi-calendar-event"></i></div>
                        <a href="<?php echo $basePath; ?>member/?page=profile#tabActivities" class="small-box-footer">
                            ดูกิจกรรม <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner">
                            <h3 id="statReceipts">0</h3>
                            <p>ใบเสร็จ</p>
                        </div>
                        <div class="icon"><i class="bi bi-receipt"></i></div>
                        <a href="<?php echo $basePath; ?>member/?page=receipts" class="small-box-footer">
                            ดูใบเสร็จ <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                    <div class="small-box bg-warning shadow-sm">
                        <div class="inner">
                            <h3 id="statPendingFees">0</h3>
                            <p>ค่าธรรมเนียมค้างชำระ</p>
                        </div>
                        <div class="icon"><i class="bi bi-cash-coin"></i></div>
                        <a href="<?php echo $basePath; ?>member/?page=fees" class="small-box-footer">
                            ดูค่าธรรมเนียม <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3" id="statPendingPaymentsBox" style="display:none;">
                    <div class="small-box bg-danger shadow-sm">
                        <div class="inner">
                            <h3 id="statPendingPayments">0</h3>
                            <p>สลิปรอตรวจสอบ</p>
                        </div>
                        <div class="icon"><i class="bi bi-credit-card-2-front"></i></div>
                        <a href="<?php echo $basePath; ?>member/?page=payment-approval" class="small-box-footer">
                            ตรวจสอบสลิป <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Module Cards -->
            <h5 class="mb-3"><i class="bi bi-grid-3x3-gap me-2"></i>เมนูหลัก</h5>
            <div class="row" id="dashModules">

                <!-- Profile -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                    <a href="<?php echo $basePath; ?>member/?page=profile" class="text-decoration-none">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #007bff;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-person-circle" style="font-size:2.5rem;color:#007bff;"></i></div>
                                <h6 class="card-title text-dark mb-1">โปรไฟล์</h6>
                                <small class="text-muted">ข้อมูลส่วนตัว & เปลี่ยนรหัสผ่าน</small>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Activities -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                    <a href="<?php echo $basePath; ?>member/?page=profile" class="text-decoration-none" onclick="sessionStorage.setItem('openTab','tabActivities');">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #17a2b8;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-calendar-event" style="font-size:2.5rem;color:#17a2b8;"></i></div>
                                <h6 class="card-title text-dark mb-1">กิจกรรม</h6>
                                <small class="text-muted">ลงทะเบียน & ดูกิจกรรม</small>
                                <div class="mt-2">
                                    <span class="badge badge-info" id="modBadgeActivities" style="display:none;">0 กิจกรรมเปิดรับ</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Fees -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                    <a href="<?php echo $basePath; ?>member/?page=fees" class="text-decoration-none">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #ffc107;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-cash-coin" style="font-size:2.5rem;color:#ffc107;"></i></div>
                                <h6 class="card-title text-dark mb-1">ค่าธรรมเนียม</h6>
                                <small class="text-muted">ชำระค่าธรรมเนียมสมาชิก</small>
                                <div class="mt-2">
                                    <span class="badge badge-warning" id="modBadgeFees" style="display:none;">0 รายการค้าง</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Receipts -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3">
                    <a href="<?php echo $basePath; ?>member/?page=receipts" class="text-decoration-none">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #28a745;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-receipt" style="font-size:2.5rem;color:#28a745;"></i></div>
                                <h6 class="card-title text-dark mb-1">ใบเสร็จ</h6>
                                <small class="text-muted">ดาวน์โหลด & พิมพ์ใบเสร็จ</small>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Finance Management (only for finance managers) -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3" id="modFinance" style="display:none;">
                    <a href="<?php echo $basePath; ?>member/?page=finance" class="text-decoration-none">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #6f42c1;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-wallet2" style="font-size:2.5rem;color:#6f42c1;"></i></div>
                                <h6 class="card-title text-dark mb-1">บริหารการเงิน</h6>
                                <small class="text-muted">จัดการรายรับ-รายจ่ายสมาคม</small>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Payment Approval (only for finance managers) -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-12 mb-3" id="modPaymentApproval" style="display:none;">
                    <a href="<?php echo $basePath; ?>member/?page=payment-approval" class="text-decoration-none">
                        <div class="card shadow-sm h-100 dash-card" style="border-left: 4px solid #dc3545;">
                            <div class="card-body text-center py-4">
                                <div class="mb-3"><i class="bi bi-credit-card-2-front" style="font-size:2.5rem;color:#dc3545;"></i></div>
                                <h6 class="card-title text-dark mb-1">ตรวจสอบการชำระเงิน</h6>
                                <small class="text-muted">อนุมัติ/ปฏิเสธสลิปโอนเงิน</small>
                                <div class="mt-2">
                                    <span class="badge badge-danger" id="modBadgePending" style="display:none;">0 รอตรวจสอบ</span>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

            </div>

            <!-- Recent Activities Section -->
            <h5 class="mb-3 mt-2"><i class="bi bi-clock-history me-2"></i>กิจกรรมที่เปิดรับสมัคร</h5>
            <div class="row" id="dashRecentActivities">
                <div class="col-12 text-center py-4 text-muted">
                    <span class="spinner-border spinner-border-sm"></span> กำลังโหลด...
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.dash-card {
    transition: transform .15s ease, box-shadow .15s ease;
    cursor: pointer;
}
.dash-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15) !important;
}
.dash-card.border-danger {
    border: 2px solid #dc3545 !important;
}
@keyframes slipPulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.6; }
}
.slip-needed-pulse {
    animation: slipPulse 1.5s ease-in-out infinite;
}
</style>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<script>
$(function() {
    App.requireLogin();
    const user = API.getUser();
    if (user) {
        $('#dashUserName').text(user.full_name || user.username);
    }
    loadDashboardStats();
    loadRecentActivities();
    checkFinanceManagerAccess();
});

async function loadDashboardStats() {
    try {
        // Load activities count
        const actRes = await API.getActivities({});
        if (actRes.success && actRes.data) {
            const myRegs = actRes.data.filter(a => a.my_registration);
            $('#statActivities').text(myRegs.length);
            const openActs = actRes.data.filter(a => a.registration_open && a.status === 'open' && !a.my_registration);
            if (openActs.length > 0) {
                $('#modBadgeActivities').text(openActs.length + ' กิจกรรมเปิดรับ').show();
            }
        }

        // Load receipts count
        const rcpRes = await API.getMyReceipts();
        if (rcpRes.success && rcpRes.data) {
            $('#statReceipts').text(rcpRes.data.length);
        }

        // Load pending fees count
        const feeRes = await API.getMyFees();
        if (feeRes.success && feeRes.data) {
            const pending = feeRes.data.filter(f => f.status === 'pending' || f.status === 'overdue');
            $('#statPendingFees').text(pending.length);
            if (pending.length > 0) {
                $('#modBadgeFees').text(pending.length + ' รายการค้าง').show();
            }
        }

        $('#dashStats').show();
    } catch(e) {
        console.error('Dashboard stats error:', e);
        $('#dashStats').show();
    }
}

async function checkFinanceManagerAccess() {
    try {
        const res = await API.getMyFinancePermissions();
        if (res.success && res.data && (res.data.is_admin || res.data.is_finance_manager)) {
            $('#modFinance').show();
            $('#modPaymentApproval').show();

            // Load pending payments count
            const ppRes = await API.getPendingPayments({ status: 'pending' });
            if (ppRes.success && ppRes.data) {
                const count = ppRes.data.length;
                $('#statPendingPayments').text(count);
                if (count > 0) {
                    $('#statPendingPaymentsBox').show();
                    $('#modBadgePending').text(count + ' รอตรวจสอบ').show();
                }
            }
        }
    } catch(e) {}
}

async function loadRecentActivities() {
    const container = $('#dashRecentActivities');
    try {
        const result = await API.getActivities({});
        if (!result.success || !result.data || result.data.length === 0) {
            container.html('<div class="col-12"><p class="text-center text-muted py-3"><i class="bi bi-calendar-x" style="font-size:2rem;"></i><br>ยังไม่มีกิจกรรม</p></div>');
            return;
        }

        // Show only open activities (max 4)
        const openActs = result.data.filter(a => a.registration_open && a.status === 'open').slice(0, 4);
        if (openActs.length === 0) {
            container.html('<div class="col-12"><p class="text-center text-muted py-3"><i class="bi bi-calendar-check" style="font-size:2rem;"></i><br>ไม่มีกิจกรรมที่เปิดรับสมัครขณะนี้</p></div>');
            return;
        }

        let html = '';
        openActs.forEach(a => {
            const coverImg = a.cover_image
                ? `<img src="${a.cover_image.startsWith('http') ? a.cover_image : (BASE_PATH + a.cover_image)}" class="card-img-top" style="height:140px;object-fit:cover;" alt="">`
                : `<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:140px;"><i class="bi bi-calendar-event" style="font-size:2.5rem;color:#ccc;"></i></div>`;
            const dateStr = App.formatDate(a.start_date) + (a.end_date && a.end_date !== a.start_date ? ' - ' + App.formatDate(a.end_date) : '');
            const feeStr = a.has_fee ? App.formatCurrency(a.fee_amount) : '<span class="text-success">ฟรี</span>';

            // Determine registration badge
            let regBadge = '<span class="badge badge-primary">เปิดรับสมัคร</span>';
            const needsSlip = a.my_registration && a.has_fee && a.my_registration.payment_status === 'pending' && !a.my_registration.payment_proof;
            if (a.my_registration) {
                if (needsSlip) {
                    regBadge = '<span class="badge badge-danger slip-needed-pulse"><i class="bi bi-exclamation-triangle me-1"></i>ยังไม่อัพโหลดสลิป</span>';
                } else {
                    regBadge = '<span class="badge badge-success"><i class="bi bi-check me-1"></i>ลงทะเบียนแล้ว</span>';
                }
            }

            // Click action: go to profile and auto-open activity detail
            const clickAction = `sessionStorage.setItem('openTab','tabActivities'); sessionStorage.setItem('openActivityId','${a.id}');`;

            html += `
            <div class="col-lg-3 col-md-6 col-sm-6 col-12 mb-3">
                <a href="${BASE_PATH}member/?page=profile" class="text-decoration-none" onclick="${clickAction}">
                    <div class="card shadow-sm h-100 dash-card ${needsSlip ? 'border-danger' : ''}">
                        ${coverImg}
                        <div class="card-body pb-2">
                            <h6 class="card-title text-dark mb-1" style="line-height:1.4;">${App.escapeHtml(a.title)}</h6>
                            <div class="small text-muted">
                                <div><i class="bi bi-calendar3 me-1"></i>${dateStr}</div>
                                <div><i class="bi bi-cash me-1"></i>ค่าลงทะเบียน: ${feeStr}</div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0 pt-0">
                            <small>${regBadge}</small>
                        </div>
                    </div>
                </a>
            </div>`;
        });
        container.html(html);
    } catch(e) {
        container.html('<div class="col-12 text-center text-danger py-3">เกิดข้อผิดพลาด</div>');
    }
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
