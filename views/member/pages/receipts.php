<?php $pageTitle = 'ใบเสร็จรับเงิน'; ?>
<?php $extraCss = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-receipt me-2"></i>ใบเสร็จรับเงิน</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/">โปรไฟล์</a></li>
                        <li class="breadcrumb-item active">ใบเสร็จ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">

        <!-- Finance Manager Actions (shown only for assigned members) -->
        <div id="financeManagerActions" style="display:none" class="mb-3">
            <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                <i class="bi bi-plus-circle me-1"></i> ออกใบเสร็จใหม่
            </button>
        </div>

        <!-- Receipt List -->
        <div id="receiptListSection">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="bi bi-receipt-cutoff me-2"></i>ใบเสร็จรับเงินทั้งหมด</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>เลขที่</th>
                                    <th>หัวข้อ</th>
                                    <th>ประเภท</th>
                                    <th>จำนวนเงิน</th>
                                    <th>วันที่ออก</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="receiptsTable">
                                <tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        </div>
    </div>

<!-- Modal: Receipt Preview -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:95vw;width:1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>ใบเสร็จรับเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="receiptPreviewBody" style="background:#e9ecef;overflow-x:hidden;overflow-y:auto;">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="openEditReceiptNumber()" id="btnEditReceiptNum" style="display:none"><i class="bi bi-pencil-square me-1"></i> แก้ไขใบเสร็จ</button>
                <button type="button" class="btn btn-info" onclick="openEditAddress()" id="btnEditAddress"><i class="bi bi-geo-alt me-1"></i> แก้ไขที่อยู่</button>
                <button type="button" class="btn btn-primary" onclick="downloadModalPDF()"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                <button type="button" class="btn btn-success" onclick="downloadModalPNG()"><i class="bi bi-image me-1"></i> PNG</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create Receipt (for finance managers) -->
<div class="modal fade" id="createReceiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>ออกใบเสร็จใหม่</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="createReceiptForm">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ได้รับเงินจาก <span class="text-danger">*</span></label>
                        <select id="createPayerSelect" class="form-control" style="width:100%">
                            <option value="">-- พิมพ์ค้นหาสมาชิก หรือระบุชื่อบุคคลภายนอก --</option>
                        </select>
                        <input type="hidden" id="createUserId">
                        <small class="text-muted">เลือกสมาชิกจากรายการ หรือพิมพ์ชื่อบุคคลภายนอกที่ไม่มีในระบบ</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">ชื่อผู้ชำระเงิน</label>
                        <input type="text" id="createPayerName" class="form-control" placeholder="อัตโนมัติจากชื่อสมาชิก" readonly>
                        <small class="text-muted" id="payerNameHint">ดึงจากชื่อสมาชิกอัตโนมัติ</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ที่อยู่ผู้ชำระเงิน</label>
                    <input type="text" id="createPayerAddress" class="form-control" placeholder="ดึงจากข้อมูลสมาชิกอัตโนมัติ หรือพิมพ์เอง">
                    <small class="text-muted">ที่อยู่จะแสดงในใบเสร็จ (ไม่ระบุก็ได้)</small>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">หมวดหมู่ <span class="text-danger">*</span></label>
                        <select id="createCategory" class="form-control">
                            <option value="">-- เลือกหมวดหมู่ --</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">หัวข้อ / รายการ <span class="text-danger">*</span></label>
                        <input type="text" id="createTitle" class="form-control" required placeholder="เช่น ค่าธรรมเนียมสมาชิก">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                        <input type="number" id="createAmount" class="form-control" required min="1" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">วันที่ออกใบเสร็จ</label>
                        <input type="date" id="createIssuedDate" class="form-control">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">เล่มที่ / เลขที่ใบเสร็จ</label>
                        <div class="input-group">
                            <span class="input-group-text" id="createBookNumberDisplay" style="font-size:13px;white-space:nowrap;">-</span>
                            <input type="text" id="createReceiptNumber" class="form-control" placeholder="อัตโนมัติ" readonly>
                            <button type="button" class="btn btn-outline-info" onclick="autoGenerateNumber()" title="รันเลขอัตโนมัติ">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <small class="text-muted">เลขที่รันอัตโนมัติตามปี (เริ่ม 1 ทุกปีใหม่)</small>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">รายละเอียด</label>
                    <textarea id="createDescription" class="form-control" rows="2" placeholder="รายละเอียดเพิ่มเติม"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" id="btnCreateReceipt"><i class="bi bi-check-circle me-1"></i> ออกใบเสร็จ</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Receipt -->
<div class="modal fade" id="editReceiptNumModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>แก้ไขใบเสร็จ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editReceiptNumForm">
            <div class="modal-body">
                <input type="hidden" id="editReceiptId">
                <div class="mb-3">
                    <label class="form-label fw-bold">เลขที่ใบเสร็จ</label>
                    <div class="input-group">
                        <input type="text" id="editReceiptNumber" class="form-control" required>
                        <button type="button" class="btn btn-outline-info" onclick="autoGenerateNumberForEdit()" title="รันเลขอัตโนมัติ">
                            <i class="bi bi-arrow-repeat"></i>
                        </button>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อผู้ชำระ</label>
                    <input type="text" id="editPayerName" class="form-control" placeholder="ชื่อผู้ชำระเงิน">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ที่อยู่ผู้ชำระเงิน</label>
                    <input type="text" id="editPayerAddress" class="form-control" placeholder="ที่อยู่ (ไม่ระบุก็ได้)">
                </div>
                <div class="mb-2">
                    <small class="text-muted" id="editReceiptInfo"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning" id="btnSaveReceiptNum"><i class="bi bi-check me-1"></i> บันทึก</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Address -->
<div class="modal fade" id="editAddressModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="bi bi-geo-alt me-2"></i>แก้ไขที่อยู่ใบเสร็จ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="editAddressForm">
            <div class="modal-body">
                <input type="hidden" id="editAddrReceiptId">
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label fw-bold">เลขที่</label>
                        <input type="text" id="editAddrNo" class="form-control" placeholder="เลขที่">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold">หมู่ที่</label>
                        <input type="text" id="editAddrMoo" class="form-control" placeholder="หมู่">
                    </div>
                    <div class="col-4">
                        <label class="form-label fw-bold">ซอย</label>
                        <input type="text" id="editAddrSoi" class="form-control" placeholder="ซอย">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ถนน</label>
                    <input type="text" id="editAddrRoad" class="form-control" placeholder="ถนน">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">ตำบล</label>
                        <input type="text" id="editAddrSub" class="form-control" placeholder="ตำบล">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">อำเภอ</label>
                        <input type="text" id="editAddrDist" class="form-control" placeholder="อำเภอ">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">จังหวัด</label>
                        <input type="text" id="editAddrProv" class="form-control" placeholder="จังหวัด">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">รหัสไปรษณีย์</label>
                        <input type="text" id="editAddrZip" class="form-control" placeholder="รหัสไปรษณีย์">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info" id="btnSaveAddr"><i class="bi bi-check me-1"></i> บันทึก</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
            </div>
            </form>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- html2canvas & jsPDF for export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
.receipt-canvas-wrapper {
    margin: 0 auto;
    overflow: hidden;
    border-radius: 6px;
    box-shadow: 0 4px 24px rgba(0,0,0,.15);
}
.receipt-render {
    width: 1123px;
    height: 794px;
    font-family: 'Sarabun', sans-serif;
    color: #1a3c5e;
    line-height: 1.5;
    padding: 30px;
    background: #fff;
    overflow: hidden;
    position: relative;
    transform-origin: top left;
}
.receipt-render .receipt-watermark {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 450px; height: 450px;
    opacity: 0.08;
    pointer-events: none;
    z-index: 0;
}
.receipt-render .receipt-inner {
    border: 2px solid #1a3c5e;
    border-radius: 12px;
    padding: 30px 50px;
    position: absolute;
    top: 30px; left: 30px; right: 30px; bottom: 30px;
    display: flex;
    flex-direction: column;
    z-index: 1;
}
.receipt-render .receipt-title { font-size: 28px; font-weight: 700; text-align: center; }
.receipt-render .receipt-org { font-size: 20px; font-weight: 600; text-align: center; }
.receipt-render .receipt-org-addr { font-size: 16px; text-align: center; margin-bottom: 10px; }
.receipt-render .receipt-body-section { font-size: 18px; flex-grow: 1; }
.receipt-render .receipt-body-section > div { display: flex; align-items: baseline; margin-bottom: 12px !important; }
.receipt-render .dotted-line { border-bottom: 1px dotted #555; display: inline-block; min-width: 80px; margin: 0; padding: 0 2px 0 1.5em; }
.receipt-render .receipt-amount-box { text-align: center; border: 1px solid #1a3c5e; border-radius: 8px; padding: 10px 24px; margin: 14px 0; font-size: 22px; }
.receipt-render .receipt-sign { display: flex; justify-content: flex-end; margin-top: auto; padding-top: 8px; font-size: 16px; }
</style>

<script>
let currentReceiptData = null;
let isFinanceManager = false;
let membersCache = [];

function scaleModalReceipt(bodyId, canvasId, loadingId, percentId) {
    const modalBody = document.getElementById(bodyId);
    const receipt = document.getElementById(canvasId);
    const loading = document.getElementById(loadingId);
    const percentEl = document.getElementById(percentId);
    if (!modalBody || !receipt) return;

    const images = receipt.querySelectorAll('img');
    const total = images.length || 1;
    let loaded = 0;

    function done() {
        setTimeout(function() {
            const bodyW = modalBody.clientWidth - 16;
            if (bodyW <= 0) return;
            // Scale to fit both width and available viewport height
            const modalContent = modalBody.closest('.modal-content');
            const headerH = modalContent?.querySelector('.modal-header')?.offsetHeight || 56;
            const footerH = modalContent?.querySelector('.modal-footer')?.offsetHeight || 56;
            const alertEl = modalBody.querySelector('.alert');
            const alertH = alertEl ? alertEl.offsetHeight + 12 : 0;
            const availH = window.innerHeight - headerH - footerH - alertH - 60;
            const scaleW = bodyW / 1123;
            const scaleH = availH / 794;
            const scale = Math.min(scaleW, scaleH, 1);
            receipt.style.transform = `scale(${scale})`;
            // Size wrapper to match scaled receipt and center it
            const wrapper = receipt.closest('.receipt-canvas-wrapper');
            if (wrapper) {
                wrapper.style.width = Math.ceil(1123 * scale) + 'px';
                wrapper.style.height = Math.ceil(794 * scale) + 'px';
            }
            receipt.style.visibility = 'visible';
            if (loading) loading.style.display = 'none';
        }, 100);
    }

    function updateProgress() {
        loaded++;
        const pct = Math.round((loaded / total) * 100);
        if (percentEl) percentEl.textContent = pct;
        if (loaded >= total) done();
    }

    if (images.length === 0) {
        if (percentEl) percentEl.textContent = '100';
        done();
        return;
    }

    images.forEach(img => {
        if (img.complete && img.naturalWidth > 0) {
            updateProgress();
        } else {
            img.addEventListener('load', updateProgress, { once: true });
            img.addEventListener('error', updateProgress, { once: true });
        }
    });

    setTimeout(() => {
        if (receipt.style.visibility === 'hidden') done();
    }, 5000);
}

$(function () {
    App.requireLogin();
    loadReceipts();
    checkFinancePermission();

    // Load receipt logo as base64 to avoid CORS
    API.getSettings().then(async function(result) {
        if (result.success && result.data) {
            const logo = result.data.logo_receipt || result.data.logo_web || '';
            if (logo) {
                const logoUrl = logo.startsWith('http') ? logo : (BASE_PATH + logo);
                window._receiptLogoUrl = await toBase64(logoUrl).catch(() => logoUrl);
            }
        }
    });

    // Check if specific receipt ID is in URL
    const urlParams = new URLSearchParams(window.location.search);
    const receiptId = urlParams.get('receipt_id');
    if (receiptId) {
        viewReceipt(parseInt(receiptId));
    }

    // Init Select2 for payer selector
    initPayerSelect2();

    // Set default issued date
    $('#createIssuedDate').val(new Date().toISOString().split('T')[0]);

    // When date changes, re-generate receipt number for new year
    $('#createIssuedDate').on('change', function() {
        autoGenerateNumber();
    });

    // When category changes, auto-fill title
    $('#createCategory').on('change', function() {
        const selectedText = $(this).find('option:selected').text();
        if (selectedText && selectedText !== '-- เลือกหมวดหมู่ --') {
            $('#createTitle').val(selectedText);
        }
    });

    // Create receipt form submit
    $('#createReceiptForm').on('submit', async function(e) {
        e.preventDefault();
        const userId = $('#createUserId').val();
        const payerName = $('#createPayerName').val().trim();
        const categoryName = $('#createCategory').find('option:selected').text().trim();

        if (!userId && !payerName) {
            App.error('กรุณาเลือกสมาชิก หรือระบุชื่อบุคคลภายนอก');
            return;
        }

        // Map category to receipt_type
        let receiptType = 'other';
        if (categoryName) {
            const lower = categoryName.toLowerCase();
            if (lower.includes('ธรรมเนียม') || lower.includes('สมาชิก')) receiptType = 'membership_fee';
            else if (lower.includes('กิจกรรม') || lower.includes('ลงทะเบียน')) receiptType = 'activity_fee';
        }

        const data = {
            title: $('#createTitle').val().trim(),
            receipt_type: receiptType,
            amount: parseFloat($('#createAmount').val()),
            issued_date: $('#createIssuedDate').val() || undefined,
            description: $('#createDescription').val().trim() || undefined,
            receipt_number: $('#createReceiptNumber').val().trim() || undefined,
        };

        const payerAddress = $('#createPayerAddress').data('addrJson') || $('#createPayerAddress').val().trim();
        if (payerAddress) data.payer_address = payerAddress;

        if (userId) {
            data.user_id = userId;
        } else {
            data.payer_name = payerName;
        }

        if (!data.title) { App.error('กรุณาระบุหัวข้อ'); return; }
        if (!data.amount || data.amount <= 0) { App.error('กรุณาระบุจำนวนเงิน'); return; }

        const btn = $('#btnCreateReceipt');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

        const result = await API.createReceipt(data);
        btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> ออกใบเสร็จ');

        if (result.success) {
            App.success(result.message || 'ออกใบเสร็จสำเร็จ');
            $('#createReceiptModal').modal('hide');
            resetCreateForm();
            loadReceipts();
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    });

    // Edit receipt number form submit
    $('#editReceiptNumForm').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#editReceiptId').val();
        const receiptNumber = $('#editReceiptNumber').val().trim();
        const payerName = $('#editPayerName').val().trim();
        if (!receiptNumber) { App.error('กรุณาระบุเลขที่ใบเสร็จ'); return; }

        const updateData = { id: id, receipt_number: receiptNumber };
        if (payerName) updateData.payer_name = payerName;
        const editAddress = $('#editPayerAddress').val().trim();
        updateData.payer_address = editAddress || null;

        const btn = $('#btnSaveReceiptNum');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        const result = await API.updateReceipt(updateData);
        btn.prop('disabled', false).html('<i class="bi bi-check me-1"></i> บันทึก');

        if (result.success) {
            App.success(result.message || 'แก้ไขเลขที่ใบเสร็จสำเร็จ');
            $('#editReceiptNumModal').modal('hide');
            loadReceipts();
            if (currentReceiptData && currentReceiptData.id == id) {
                viewReceipt(id);
            }
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    });

    // Edit address form submit
    $('#editAddressForm').on('submit', async function(e) {
        e.preventDefault();
        const id = $('#editAddrReceiptId').val();
        const no   = $('#editAddrNo').val().trim();
        const moo  = $('#editAddrMoo').val().trim();
        const soi  = $('#editAddrSoi').val().trim();
        const road = $('#editAddrRoad').val().trim();
        const sub  = $('#editAddrSub').val().trim();
        const dist = $('#editAddrDist').val().trim();
        const prov = $('#editAddrProv').val().trim();
        const zip  = $('#editAddrZip').val().trim();

        // Build detail like buildPayerAddress
        let detail = no && no !== '-' ? no : '';
        if (moo && moo !== '-') detail += '   หมู่ ' + moo;
        if (soi && soi !== '-') detail += '   ซอย ' + soi;
        if (road && road !== '-') detail += '   ถนน ' + road;
        detail = detail.trim();

        const addrJson = JSON.stringify({ detail, subdistrict: sub, district: dist, province: prov, zipcode: zip });

        const btn = $('#btnSaveAddr');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        const result = await API.post(API.apiUrl('receipt', 'update-my-address'), { id, payer_address: addrJson });
        btn.prop('disabled', false).html('<i class="bi bi-check me-1"></i> บันทึก');

        if (result.success) {
            App.success(result.message || 'แก้ไขที่อยู่สำเร็จ');
            $('#editAddressModal').modal('hide');
            loadReceipts();
            if (currentReceiptData && currentReceiptData.id == id) {
                viewReceipt(id);
            }
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    });
});

// Check if current member is a finance manager
async function checkFinancePermission() {
    try {
        const result = await API.get(API.apiUrl('finance', 'my-permissions'));
        if (result.success && result.data && result.data.is_active) {
            isFinanceManager = true;
            $('#financeManagerActions').show();
            loadMembersList();
            loadCategories();
        }
    } catch (e) {
        // Not a finance manager, that's ok
    }
}

// Init Select2 for payer selector
function initPayerSelect2() {
    $('#createPayerSelect').select2({
        theme: 'bootstrap-5',
        placeholder: '-- พิมพ์ค้นหาสมาชิก หรือระบุชื่อบุคคลภายนอก --',
        allowClear: true,
        tags: true,
        dropdownParent: $('#createReceiptModal'),
        createTag: function(params) {
            const term = $.trim(params.term);
            if (term === '') return null;
            return { id: term, text: term + ' (บุคคลภายนอก)', isCustom: true };
        }
    });

    // When an option is selected
    $('#createPayerSelect').on('select2:select', function(e) {
        const data = e.params.data;
        if (data.id && String(data.id).startsWith('user_')) {
            // Member selected
            const userId = data.id.replace('user_', '');
            $('#createUserId').val(userId);
            const member = membersCache.find(m => String(m.id) === String(userId));
            if (member) {
                $('#createPayerName').val(member.full_name).prop('readonly', true);
                $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
                const addrJson = buildPayerAddress(member);
                $('#createPayerAddress').val(flatPayerAddress(addrJson)).data('addrJson', addrJson);
            }
        } else {
            // Non-member (custom tag)
            $('#createUserId').val('');
            const cleanName = data.text.replace(' (บุคคลภายนอก)', '');
            $('#createPayerName').val(cleanName).prop('readonly', false);
            $('#createPayerAddress').val('').removeData('addrJson');
            $('#payerNameHint').text('บุคคลภายนอก - แก้ไขชื่อได้');
        }
    });

    // When cleared
    $('#createPayerSelect').on('select2:clear', function() {
        $('#createUserId').val('');
        $('#createPayerName').val('').prop('readonly', true);
        $('#createPayerAddress').val('').removeData('addrJson');
        $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    });
}

// Load members for create modal (using receipt-specific search endpoint)
async function loadMembersList() {
    const result = await API.searchReceiptMembers('');
    if (result.success && result.data) {
        membersCache = result.data;
        const select = $('#createPayerSelect');
        select.find('option:not(:first)').remove();
        result.data.forEach(m => {
            select.append(`<option value="user_${m.id}">${App.escapeHtml(m.full_name)} (${App.escapeHtml(m.email || '-')})</option>`);
        });
    }
}

// Load categories from finance_categories
async function loadCategories() {
    const result = await API.getFinanceActiveCategories('income');
    if (result.success && result.data) {
        const select = $('#createCategory');
        select.find('option:not(:first)').remove();
        result.data.forEach(c => {
            select.append(`<option value="${c.id}">${App.escapeHtml(c.name)}</option>`);
        });
    }
}

function resetCreateForm() {
    $('#createReceiptForm')[0].reset();
    $('#createPayerSelect').val(null).trigger('change');
    $('#createUserId').val('');
    $('#createPayerName').val('').prop('readonly', true);
    $('#createPayerAddress').val('').removeData('addrJson');
    $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    $('#createIssuedDate').val(new Date().toISOString().split('T')[0]);
    $('#createBookNumberDisplay').text('-');
}

function openCreateModal() {
    resetCreateForm();
    $('#createReceiptModal').modal('show');
    autoGenerateNumber();
}

async function autoGenerateNumber() {
    const issuedDate = $('#createIssuedDate').val() || '';
    const result = await API.getNextReceiptNumber(issuedDate);
    if (result.success && result.data) {
        $('#createBookNumberDisplay').text(result.data.book_number || '-');
        $('#createReceiptNumber').val(result.data.receipt_number);
    } else {
        $('#createBookNumberDisplay').text('-');
        $('#createReceiptNumber').val('');
    }
}

async function autoGenerateNumberForEdit() {
    if (!currentReceiptData) return;
    const result = await API.getNextReceiptNumber(currentReceiptData.issued_date);
    if (result.success && result.data) {
        $('#editReceiptNumber').val(result.data.receipt_number);
        App.success(`เลขที่ถัดไป: ${result.data.receipt_number}`);
    } else {
        App.error(result.message || 'ไม่สามารถดึงเลขที่ได้');
    }
}

function openEditReceiptNumber() {
    if (!currentReceiptData) return;
    $('#editReceiptId').val(currentReceiptData.id);
    $('#editReceiptNumber').val(currentReceiptData.receipt_number);
    $('#editPayerName').val(currentReceiptData.payer_name || '');
    $('#editPayerAddress').val(flatPayerAddress(currentReceiptData.payer_address || ''));
    $('#editReceiptInfo').text(`เล่มที่: ${currentReceiptData.book_number} | วันที่ออก: ${App.formatDate(currentReceiptData.issued_date)}`);
    $('#editReceiptNumModal').modal('show');
}

function openEditAddress() {
    if (!currentReceiptData) return;
    $('#editAddrReceiptId').val(currentReceiptData.id);
    // Parse existing address into fields
    let addr = {};
    try { addr = JSON.parse(currentReceiptData.payer_address); } catch(e) {}
    if (!addr || typeof addr !== 'object') addr = {};

    // Try to parse detail back into no/moo/soi/road
    let detail = addr.detail || '';
    let no = '', moo = '', soi = '', road = '';
    // Extract road
    const roadMatch = detail.match(/\s+ถนน\s*(.+?)$/);
    if (roadMatch) { road = roadMatch[1].trim(); detail = detail.replace(roadMatch[0], ''); }
    // Extract soi
    const soiMatch = detail.match(/\s+ซอย\s*(.+?)$/);
    if (soiMatch) { soi = soiMatch[1].trim(); detail = detail.replace(soiMatch[0], ''); }
    // Extract moo
    const mooMatch = detail.match(/\s+หมู่\s*(.+?)$/);
    if (mooMatch) { moo = mooMatch[1].trim(); detail = detail.replace(mooMatch[0], ''); }
    no = detail.trim();

    $('#editAddrNo').val(no);
    $('#editAddrMoo').val(moo);
    $('#editAddrSoi').val(soi);
    $('#editAddrRoad').val(road);
    $('#editAddrSub').val(addr.subdistrict || '');
    $('#editAddrDist').val(addr.district || '');
    $('#editAddrProv').val(addr.province || '');
    $('#editAddrZip').val(addr.zipcode || '');
    $('#editAddressModal').modal('show');
}

// Convert image URL to base64
function toBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() {
            const c = document.createElement('canvas');
            c.width = img.naturalWidth;
            c.height = img.naturalHeight;
            c.getContext('2d').drawImage(img, 0, 0);
            resolve(c.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url + (url.includes('?') ? '&' : '?') + '_t=' + Date.now();
    });
}

// Build payer address from member data → returns JSON string (mirrors PHP FeeController::buildPayerAddress)
function buildPayerAddress(member) {
    if (!member) return '';
    // Try work_address first, then home_address
    for (const field of ['work_address', 'home_address']) {
        let wa = member[field];
        if (typeof wa === 'string' && wa) {
            try { wa = JSON.parse(wa); } catch(e) { if (field === 'work_address') return wa; continue; }
        }
        if (wa && typeof wa === 'object') {
            // Build detail from individual parts (no, moo, soi, road) or combined address/detail
            let detail = (wa.address || wa.detail || '').trim();
            const no   = (wa.no || '').trim();
            const moo  = (wa.moo || '').trim();
            const soi  = (wa.soi || '').trim();
            const road = (wa.road || '').trim();

            if (!detail && no && no !== '-') detail = no;
            if (moo && moo !== '-') detail += '   หมู่ ' + moo;
            if (soi && soi !== '-') detail += '   ซอย ' + soi;
            if (road && road !== '-') detail += '   ถนน ' + road;
            detail = detail.trim();

            const subdistrict = (wa.subdistrict || '').trim();
            const district    = (wa.district || '').trim();
            const province    = (wa.province || '').trim();
            const zipcode     = (wa.zipcode || '').trim();
            if (detail || subdistrict || district || province) {
                return JSON.stringify({ detail, subdistrict, district, province, zipcode });
            }
        }
    }
    return member.school_organization || '';
}

// Flat display for form inputs
function flatPayerAddress(val) {
    if (!val) return '';
    try {
        const a = typeof val === 'string' ? JSON.parse(val) : val;
        if (a && typeof a === 'object' && (a.detail || a.subdistrict)) {
            const parts = [];
            if (a.detail) parts.push(a.detail);
            if (a.subdistrict) parts.push('ต.' + a.subdistrict);
            if (a.district) parts.push('อ.' + a.district);
            if (a.province) parts.push('จ.' + a.province);
            if (a.zipcode) parts.push(a.zipcode);
            return parts.join(' ');
        }
    } catch(e) {}
    return val;
}

// Render structured address for receipt preview (multi-line like SAAK paper receipt)
function renderPayerAddressHtml(raw, fontSize) {
    fontSize = fontSize || '18px';
    let detail = '-', sub = '-', dist = '-', prov = '-';
    if (raw) {
        try {
            const a = JSON.parse(raw);
            if (a && typeof a === 'object') {
                detail = a.detail || '-';
                sub = a.subdistrict || '-';
                dist = a.district || '-';
                prov = a.province || '-';
            } else {
                return `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};"><strong style="white-space:nowrap">ที่อยู่</strong><span class="dotted-line" style="flex:3">${App.escapeHtml(raw)}</span><strong style="white-space:nowrap">ตำบล</strong><span class="dotted-line" style="flex:2">-</span></div>`
                    + `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};"><strong style="white-space:nowrap">อำเภอ</strong><span class="dotted-line" style="flex:3">-</span><strong style="white-space:nowrap">จังหวัด</strong><span class="dotted-line" style="flex:2">-</span></div>`;
            }
        } catch(e) {
            return `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};"><strong style="white-space:nowrap">ที่อยู่</strong><span class="dotted-line" style="flex:3">${App.escapeHtml(raw)}</span><strong style="white-space:nowrap">ตำบล</strong><span class="dotted-line" style="flex:2">-</span></div>`
                + `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};"><strong style="white-space:nowrap">อำเภอ</strong><span class="dotted-line" style="flex:3">-</span><strong style="white-space:nowrap">จังหวัด</strong><span class="dotted-line" style="flex:2">-</span></div>`;
        }
    }
    let html = '';
    html += `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};">`;
    html += `<strong style="white-space:nowrap">ที่อยู่</strong><span class="dotted-line" style="flex:3">${App.escapeHtml(detail)}</span>`;
    html += `<strong style="white-space:nowrap">ตำบล</strong><span class="dotted-line" style="flex:2">${App.escapeHtml(sub)}</span>`;
    html += `</div>`;
    html += `<div style="display:flex;align-items:baseline;margin-bottom:8px;font-size:${fontSize};">`;
    html += `<strong style="white-space:nowrap">อำเภอ</strong><span class="dotted-line" style="flex:3">${App.escapeHtml(dist)}</span>`;
    html += `<strong style="white-space:nowrap">จังหวัด</strong><span class="dotted-line" style="flex:2">${App.escapeHtml(prov)}</span>`;
    html += `</div>`;
    return html;
}

async function loadReceipts() {
    const tbody = $('#receiptsTable');
    tbody.html('<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');
    const result = await API.getMyReceipts();

    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="6" class="text-center py-4 text-muted">ยังไม่มีใบเสร็จ</td></tr>');
        return;
    }

    const typeLabels = { 'membership_fee': 'ค่าธรรมเนียม', 'activity_fee': 'ค่ากิจกรรม', 'other': 'อื่นๆ' };
    const typeBadges = { 'membership_fee': 'bg-primary', 'activity_fee': 'bg-info', 'other': 'bg-secondary' };

    let html = '';
    result.data.forEach(r => {
        html += `<tr>
            <td>${r.book_number} / ${r.receipt_number}</td>
            <td>${r.title}</td>
            <td><span class="badge ${typeBadges[r.receipt_type] || 'bg-secondary'}">${typeLabels[r.receipt_type] || r.receipt_type}</span></td>
            <td>${App.formatCurrency(r.amount)}</td>
            <td>${App.formatDate(r.issued_date)}</td>
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="viewReceipt(${r.id})" title="ดูใบเสร็จ">
                    <i class="bi bi-eye"></i>
                </button>
            </td>
        </tr>`;
    });
    tbody.html(html);
}

async function viewReceipt(id) {
    const body = $('#receiptPreviewBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');
    $('#receiptPreviewModal').modal('show');

    const result = await API.getReceiptDetail(id);
    if (!result.success || !result.data) {
        body.html('<p class="text-center text-danger">ไม่พบใบเสร็จ</p>');
        return;
    }

    currentReceiptData = result.data;
    const r = currentReceiptData;

    // Show edit button for finance managers
    if (isFinanceManager) {
        const issuedYear = new Date(r.issued_date).getFullYear() + 543;
        const currentYear = new Date().getFullYear() + 543;
        $('#btnEditReceiptNum').toggle(issuedYear >= currentYear);
    } else {
        $('#btnEditReceiptNum').hide();
    }

    // Convert signature image to base64 to avoid CORS
    let signatureImgSrc = '';
    if (r.signature_mode === 'electronic' && r.signature_image) {
        if (r.signature_image.startsWith('data:')) {
            signatureImgSrc = r.signature_image;
        } else {
            const sigUrl = r.signature_image.startsWith('http') ? r.signature_image : (BASE_PATH + r.signature_image);
            signatureImgSrc = await toBase64(sigUrl).catch(() => sigUrl);
        }
    }

    const issuedDate = new Date(r.issued_date);
    const thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                        'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    const dateStr = `วันที่ ${issuedDate.getDate()} เดือน ${thaiMonths[issuedDate.getMonth()]} พ.ศ. ${issuedDate.getFullYear() + 543}`;

    body.html(`<div id="receiptModalLoading" style="text-align:center;padding:60px 20px;">
        <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
        <div class="mt-3 text-muted" style="font-size:16px;">กำลังโหลดใบเสร็จ... <span id="receiptModalPercent">0</span>%</div>
    </div>
    <div class="receipt-canvas-wrapper" id="receiptCanvasWrapper">
    <div id="modalReceiptCanvas" class="receipt-render" style="visibility:hidden;">
        ${window._receiptLogoUrl ? `<img src="${window._receiptLogoUrl}" class="receipt-watermark" alt="">` : ''}
        <div class="receipt-inner">
        <div style="display:flex;justify-content:space-between;font-size:16px;margin-bottom:6px;">
            <div>เล่มที่ ${App.escapeHtml(r.book_number)}</div>
            <div>เลขที่ ${r.receipt_number}</div>
        </div>
        <div style="text-align:center;margin-bottom:10px;">
            ${window._receiptLogoUrl ? `<div style="margin-bottom:8px;"><img src="${window._receiptLogoUrl}" alt="Logo" style="max-height:70px;"></div>` : ''}
            <div class="receipt-title">ใบเสร็จรับเงิน</div>
            <div class="receipt-org">${App.escapeHtml(r.organization_name)}</div>
            <div class="receipt-org-addr">${App.escapeHtml(r.organization_address)}</div>
        </div>
        <div style="text-align:left;font-size:16px;margin-bottom:8px;padding-left:50%;">${dateStr}</div>
        <div class="receipt-body-section">
            <div style="margin-bottom:8px;font-size:18px;"><strong style="white-space:nowrap">ได้รับเงินจาก</strong><span class="dotted-line" style="flex:1">${App.escapeHtml(r.payer_name)}</span></div>
            ${renderPayerAddressHtml(r.payer_address, '18px')}
            <div style="margin-bottom:8px;font-size:18px;"><strong style="white-space:nowrap">เป็น</strong><span class="dotted-line" style="flex:1">${App.escapeHtml((r.description||'').replace(/\\s*จำนวน\\s*[\\d,.]+\\s*บาท/g,''))}</span></div>
            ${r.receipt_type === 'membership_fee' && r.member_type_label ? `<div style="margin-bottom:8px;font-size:18px;"><strong style="white-space:nowrap">ประเภทสมาชิก</strong><span class="dotted-line" style="flex:1">${App.escapeHtml(r.member_type_label)}</span></div>` : ''}
            <div class="receipt-amount-box">
                <strong>จำนวน ${App.formatCurrency(r.amount)}</strong> (${App.escapeHtml(r.amount_text)}) ไว้ถูกต้องแล้ว
            </div>
        </div>
        <div class="receipt-sign">
        <div style="text-align:center;">
            ${r.signature_mode === 'electronic' && r.signature_image ? `<div style="margin-bottom:-25px;"><img src="${signatureImgSrc}" alt="ลายเซ็น" style="max-height:60px;"></div>` : '<div style="margin-bottom:20px;"></div>'}
            <div>(ลงชื่อ) ................................... ผู้รับเงิน</div>
            ${r.signature_show_name === '1' && r.signature_name ? `<div style="margin-top:2px;">(${App.escapeHtml(r.signature_name)})</div>` : ''}
            ${r.signature_show_position === '1' ? `<div style="margin-top:1px;">${App.escapeHtml(r.signature_position || 'เหรัญญิก')}</div>` : ''}
        </div>
        </div>
        </div>
    </div>
    </div>`);

    scaleModalReceipt('receiptPreviewBody', 'modalReceiptCanvas', 'receiptModalLoading', 'receiptModalPercent');
}

async function downloadModalPDF() {
    if (!currentReceiptData) return;
    const el = document.getElementById('modalReceiptCanvas');
    const origTransform = el.style.transform;
    el.style.transform = 'none';
    try {
        const canvas = await html2canvas(el, {
            scale: 2,
            backgroundColor: '#fff',
            useCORS: true,
            allowTaint: false,
        });
        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/png');
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pageW = 297, pageH = 210;
        const margin = 5;
        const imgWidth = pageW - margin * 2;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, Math.min(imgHeight, pageH - margin * 2));
        pdf.save(`receipt_${currentReceiptData.receipt_number}.pdf`);
    } catch (err) { console.error('PDF Error:', err); App.error('เกิดข้อผิดพลาดในการสร้าง PDF: ' + err.message); }
    el.style.transform = origTransform;
}

async function downloadModalPNG() {
    if (!currentReceiptData) return;
    const el = document.getElementById('modalReceiptCanvas');
    const origTransform = el.style.transform;
    el.style.transform = 'none';
    try {
        const canvas = await html2canvas(el, {
            scale: 2,
            backgroundColor: '#fff',
            useCORS: true,
            allowTaint: false,
        });
        const link = document.createElement('a');
        link.download = `receipt_${currentReceiptData.receipt_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (err) { console.error('PNG Error:', err); App.error('เกิดข้อผิดพลาดในการสร้างรูป: ' + err.message); }
    el.style.transform = origTransform;
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
