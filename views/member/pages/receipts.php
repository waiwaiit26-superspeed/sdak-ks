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
                                    <th>จำนวนเงิน</th>
                                    <th>วันที่ออก</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="receiptsTable">
                                <tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Receipt Detail / Preview -->
        <div id="receiptDetailSection" style="display:none">
            <div class="mb-3 d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-secondary" onclick="showList()">
                    <i class="bi bi-arrow-left me-1"></i> กลับ
                </button>
                <button class="btn btn-primary" onclick="downloadPDF()">
                    <i class="bi bi-file-earmark-pdf me-1"></i> ดาวน์โหลด PDF
                </button>
                <button class="btn btn-success" onclick="downloadPNG()">
                    <i class="bi bi-image me-1"></i> ดาวน์โหลด PNG
                </button>
                <button class="btn btn-warning" onclick="openEditReceiptNumber()" id="btnEditReceiptNumMember" style="display:none">
                    <i class="bi bi-pencil-square me-1"></i> แก้ไขใบเสร็จ
                </button>
            </div>

            <div class="receipt-a4-wrapper">
                <div id="receiptLoading" style="display:none;text-align:center;padding:60px 20px;">
                    <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                    <div class="mt-3 text-muted" style="font-size:16px;">กำลังโหลดใบเสร็จ... <span id="receiptLoadPercent">0</span>%</div>
                </div>
                <div class="receipt-scale-container" style="visibility:hidden;">
                    <div id="receiptCanvas">
                        <!-- Receipt will be rendered here -->
                    </div>
                </div>
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
                        <label class="form-label fw-bold">เลขที่ใบเสร็จ</label>
                        <div class="input-group">
                            <input type="text" id="createReceiptNumber" class="form-control" placeholder="อัตโนมัติ">
                            <button type="button" class="btn btn-outline-info" onclick="autoGenerateNumber()" title="รันเลขอัตโนมัติ">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                        <small class="text-muted">เว้นว่างเพื่อรันเลขอัตโนมัติ</small>
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

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- html2canvas & jsPDF for export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
/* === A4 Landscape Responsive Scaled Receipt === */
.receipt-a4-wrapper {
    padding: 20px;
    background: #e9ecef;
    overflow: hidden;
}
.receipt-scale-container {
    width: 1123px;
    height: 794px;
    transform-origin: top left;
}
#receiptCanvas {
    width: 1123px;  /* A4 landscape: 297mm */
    height: 794px;  /* A4 landscape: 210mm */
    background: #fff;
    font-family: 'Sarabun', sans-serif;
    color: #1a3c5e;
    line-height: 1.8;
    position: relative;
    margin: 0 auto;
    box-shadow: 0 4px 24px rgba(0,0,0,.15);
    overflow: hidden;
}
#receiptCanvas .receipt-watermark {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    width: 450px; height: 450px;
    opacity: 0.08;
    pointer-events: none;
    z-index: 0;
}
#receiptCanvas .receipt-border {
    border: 2px solid #1a3c5e;
    border-radius: 12px;
    padding: 40px 60px;
    position: absolute;
    top: 30px; left: 30px; right: 30px; bottom: 30px;
    display: flex;
    flex-direction: column;
    z-index: 1;
}
#receiptCanvas .receipt-header {
    text-align: center;
    margin-bottom: 15px;
}
#receiptCanvas .receipt-title {
    font-size: 28px;
    font-weight: 700;
    color: #1a3c5e;
}
#receiptCanvas .receipt-org {
    font-size: 20px;
    font-weight: 600;
}
#receiptCanvas .receipt-org-addr {
    font-size: 16px;
}
#receiptCanvas .receipt-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    font-size: 16px;
}
#receiptCanvas .receipt-body {
    font-size: 18px;
    padding: 10px 0;
    flex-grow: 1;
}
#receiptCanvas .receipt-body .row-label {
    display: inline-block;
    min-width: 140px;
}
#receiptCanvas .receipt-amount-box {
    margin-top: 15px;
    padding: 12px 20px;
    border: 1px solid #1a3c5e;
    border-radius: 8px;
    font-size: 20px;
    text-align: center;
}
#receiptCanvas .receipt-footer {
    margin-top: 30px;
    display: flex;
    justify-content: flex-end;
    font-size: 16px;
}
#receiptCanvas .dotted-line {
    border-bottom: 1px dotted #555;
    display: inline-block;
    min-width: 280px;
    margin: 0 5px;
}
@media print {
    .receipt-a4-wrapper { overflow: visible; padding: 0; background: #fff; }
    .receipt-scale-container { transform: none !important; }
    #receiptCanvas { box-shadow: none; }
}
</style>

<script>
let currentReceiptData = null;
let isFinanceManager = false;
let membersCache = [];

function scaleReceipt() {
    const wrapper = document.querySelector('.receipt-a4-wrapper');
    const container = document.querySelector('.receipt-scale-container');
    if (!wrapper || !container) return;
    const wrapperW = wrapper.clientWidth - 40; /* minus padding */
    if (wrapperW <= 0) return; /* element not visible yet */
    const scale = Math.min(wrapperW / 1123, 1);
    container.style.transform = `scale(${scale})`;
    wrapper.style.height = (794 * scale + 40) + 'px';
}

function showReceiptWithLoading() {
    const loading = document.getElementById('receiptLoading');
    const container = document.querySelector('.receipt-scale-container');
    const percentEl = document.getElementById('receiptLoadPercent');
    if (!loading || !container) return;

    loading.style.display = 'block';
    container.style.visibility = 'hidden';
    percentEl.textContent = '0';

    const images = container.querySelectorAll('img');
    const total = images.length || 1;
    let loaded = 0;

    function updateProgress() {
        loaded++;
        const pct = Math.round((loaded / total) * 100);
        percentEl.textContent = pct;
        if (loaded >= total) {
            requestAnimationFrame(() => {
                scaleReceipt();
                container.style.visibility = 'visible';
                loading.style.display = 'none';
            });
        }
    }

    if (images.length === 0) {
        percentEl.textContent = '100';
        requestAnimationFrame(() => {
            scaleReceipt();
            container.style.visibility = 'visible';
            loading.style.display = 'none';
        });
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

    /* Safety timeout - show after 5s max */
    setTimeout(() => {
        if (container.style.visibility === 'hidden') {
            percentEl.textContent = '100';
            scaleReceipt();
            container.style.visibility = 'visible';
            loading.style.display = 'none';
        }
    }, 5000);
}

$(function () {
    App.requireLogin();
    loadReceipts();
    $(window).on('resize', scaleReceipt);
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
}

function openCreateModal() {
    resetCreateForm();
    $('#createReceiptModal').modal('show');
}

async function autoGenerateNumber() {
    const issuedDate = $('#createIssuedDate').val() || '';
    const result = await API.getNextReceiptNumber(issuedDate);
    if (result.success && result.data) {
        $('#createReceiptNumber').val(result.data.receipt_number);
        App.success(`เลขที่ถัดไป: ${result.data.receipt_number} (เล่ม ${result.data.book_number})`);
    } else {
        App.error(result.message || 'ไม่สามารถดึงเลขที่ได้');
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
            const detail      = (wa.address || wa.detail || '').trim();
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
    if (!raw) return '';
    try {
        const a = JSON.parse(raw);
        if (a && typeof a === 'object' && (a.detail || a.subdistrict || a.district || a.province)) {
            let html = '';
            html += `<div style="margin-bottom:4px;font-size:${fontSize};">`;
            html += `<strong>ที่อยู่</strong> <span class="dotted-line" style="min-width:300px">&nbsp;${App.escapeHtml(a.detail || '')}&nbsp;</span>`;
            html += ` <strong>ตำบล</strong> <span class="dotted-line" style="min-width:180px">&nbsp;${App.escapeHtml(a.subdistrict || '')}&nbsp;</span>`;
            html += `</div>`;
            html += `<div style="margin-bottom:8px;font-size:${fontSize};">`;
            html += `<strong>อำเภอ</strong> <span class="dotted-line" style="min-width:250px">&nbsp;${App.escapeHtml(a.district || '')}&nbsp;</span>`;
            html += ` <strong>จังหวัด</strong> <span class="dotted-line" style="min-width:220px">&nbsp;${App.escapeHtml(a.province || '')}&nbsp;</span>`;
            html += `</div>`;
            return html;
        }
    } catch(e) {}
    // Plain text fallback
    return `<div style="margin-bottom:8px;font-size:${fontSize};"><strong>ที่อยู่</strong> <span class="dotted-line" style="min-width:540px">&nbsp;${App.escapeHtml(raw)}&nbsp;</span></div>`;
}

async function loadReceipts() {
    const tbody = $('#receiptsTable');
    const result = await API.getMyReceipts();

    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="5" class="text-center py-4 text-muted">ยังไม่มีใบเสร็จ</td></tr>');
        return;
    }

    let html = '';
    result.data.forEach(r => {
        html += `<tr>
            <td>${r.book_number} / ${r.receipt_number}</td>
            <td>${r.title}</td>
            <td>${App.formatCurrency(r.amount)}</td>
            <td>${App.formatDate(r.issued_date)}</td>
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="viewReceipt(${r.id})">
                    <i class="bi bi-eye me-1"></i> ดู
                </button>
            </td>
        </tr>`;
    });
    tbody.html(html);
}

async function viewReceipt(id) {
    const result = await API.getReceiptDetail(id);
    if (!result.success || !result.data) {
        App.error(result.message || 'ไม่พบใบเสร็จ');
        return;
    }

    currentReceiptData = result.data;

    // Show edit button for finance managers (year restriction handled by backend)
    if (isFinanceManager) {
        const issuedYear = new Date(currentReceiptData.issued_date).getFullYear() + 543;
        const currentYear = new Date().getFullYear() + 543;
        if (issuedYear >= currentYear) {
            $('#btnEditReceiptNumMember').show();
        } else {
            $('#btnEditReceiptNumMember').hide(); // Past year, member can't edit
        }
    } else {
        $('#btnEditReceiptNumMember').hide();
    }

    // Convert signature image to base64 to avoid CORS
    if (currentReceiptData.signature_mode === 'electronic' && currentReceiptData.signature_image && !currentReceiptData.signature_image.startsWith('data:')) {
        const sigUrl = currentReceiptData.signature_image.startsWith('http') ? currentReceiptData.signature_image : (BASE_PATH + currentReceiptData.signature_image);
        currentReceiptData._signatureBase64 = await toBase64(sigUrl).catch(() => sigUrl);
    }

    renderReceipt(currentReceiptData);

    $('#receiptListSection').hide();
    $('#receiptDetailSection').show();

    /* Now element is visible, scale with loading */
    showReceiptWithLoading();
}

function showList() {
    $('#receiptDetailSection').hide();
    $('#receiptListSection').show();
}

function renderReceipt(r) {
    const issuedDate = new Date(r.issued_date);
    const thaiMonths = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน',
                        'กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    const dateStr = `วันที่ ${issuedDate.getDate()} เดือน ${thaiMonths[issuedDate.getMonth()]} พ.ศ. ${issuedDate.getFullYear() + 543}`;

    const canvas = document.getElementById('receiptCanvas');
    canvas.innerHTML = `
        ${window._receiptLogoUrl ? `<img src="${window._receiptLogoUrl}" class="receipt-watermark" alt="">` : ''}
        <div class="receipt-border">
            <div class="receipt-meta">
                <div>เล่มที่ ${App.escapeHtml(r.book_number)}</div>
                <div>เลขที่ ${r.receipt_number}</div>
            </div>

            <div class="receipt-header">
                ${window._receiptLogoUrl ? `<div style="text-align:center;margin-bottom:8px;"><img src="${window._receiptLogoUrl}" alt="Logo" style="max-height:70px;"></div>` : ''}
                <div class="receipt-title">ใบเสร็จรับเงิน</div>
                <div class="receipt-org">${App.escapeHtml(r.organization_name)}</div>
                <div class="receipt-org-addr">${App.escapeHtml(r.organization_address)}</div>
            </div>

            <div style="text-align:left; margin-bottom:15px; font-size:16px; padding-left:50%;">
                ${dateStr}
            </div>

            <div class="receipt-body">
                <div style="margin-bottom:8px;">
                    <span class="row-label"><strong>ได้รับเงินจาก</strong></span>
                    <span class="dotted-line" style="min-width:500px">&nbsp;${App.escapeHtml(r.payer_name)}&nbsp;</span>
                </div>
                ${renderPayerAddressHtml(r.payer_address, '16px')}
                <div style="margin-bottom:8px;">
                    <span class="row-label"><strong>เป็น</strong></span>
                    <span class="dotted-line" style="min-width:560px">&nbsp;${App.escapeHtml(r.description)}&nbsp;</span>
                </div>
            </div>

            <div class="receipt-amount-box">
                <strong>จำนวน ${App.formatCurrency(r.amount)}</strong>
                (${App.escapeHtml(r.amount_text)}) ไว้ถูกต้องแล้ว
            </div>

            <div class="receipt-footer">
            <div style="text-align:center;">
                ${r.signature_mode === 'electronic' && r.signature_image ? `<div style="margin-bottom:-25px;"><img src="${r._signatureBase64 || (r.signature_image.startsWith('data:') || r.signature_image.startsWith('http') ? r.signature_image : (BASE_PATH + r.signature_image))}" alt="ลายเซ็น" style="max-height:60px;"></div>` : '<div style="margin-bottom:40px;"></div>'}
                <div>(ลงชื่อ) ................................... ผู้รับเงิน</div>
                ${r.signature_show_name === '1' && r.signature_name ? `<div style="margin-top:5px;">(${App.escapeHtml(r.signature_name)})</div>` : ''}
                ${r.signature_show_position === '1' ? `<div style="margin-top:3px;">${App.escapeHtml(r.signature_position || 'เหรัญญิก')}</div>` : ''}
            </div>
            </div>
        </div>
    `;
}

async function downloadPNG() {
    if (!currentReceiptData) return;
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังสร้าง...';

    const container = document.querySelector('.receipt-scale-container');
    const origTransform = container.style.transform;
    container.style.transform = 'none';

    try {
        const canvas = await html2canvas(document.getElementById('receiptCanvas'), {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
        });
        const link = document.createElement('a');
        link.download = `receipt_${currentReceiptData.receipt_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (err) {
        console.error(err);
        App.error('เกิดข้อผิดพลาดในการสร้างรูป');
    }

    container.style.transform = origTransform;
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-image me-1"></i> ดาวน์โหลด PNG';
}

async function downloadPDF() {
    if (!currentReceiptData) return;
    const btn = event.target.closest('button');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> กำลังสร้าง...';

    const container = document.querySelector('.receipt-scale-container');
    const origTransform = container.style.transform;
    container.style.transform = 'none';

    try {
        const canvas = await html2canvas(document.getElementById('receiptCanvas'), {
            scale: 2,
            backgroundColor: '#ffffff',
            useCORS: true,
        });

        const { jsPDF } = window.jspdf;
        const imgData = canvas.toDataURL('image/png');
        // A4 landscape: 297 x 210 mm
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pageW = 297, pageH = 210;
        const margin = 5;
        const imgWidth = pageW - margin * 2;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, Math.min(imgHeight, pageH - margin * 2));
        pdf.save(`receipt_${currentReceiptData.receipt_number}.pdf`);
    } catch (err) {
        console.error(err);
        App.error('เกิดข้อผิดพลาดในการสร้าง PDF');
    }

    container.style.transform = origTransform;
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-file-earmark-pdf me-1"></i> ดาวน์โหลด PDF';
}
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
