<?php $pageTitle = 'จัดการใบเสร็จ'; $page = 'receipts'; ?>
<?php $extraCss = '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-receipt me-2"></i>จัดการใบเสร็จ</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">ใบเสร็จ</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">

            <!-- Summary + Create Button -->
            <div class="row mb-3">
                <div class="col-6">
                    <span id="receiptCount" class="text-muted"></span>
                </div>
                <div class="col-6 text-right">
                    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <i class="bi bi-plus-circle me-1"></i> ออกใบเสร็จใหม่
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <select id="filterType" class="form-control form-control-sm">
                                <option value="">ทุกประเภท</option>
                                <option value="membership_fee">ค่าธรรมเนียมสมาชิก</option>
                                <option value="activity_fee">ค่าลงทะเบียนกิจกรรม</option>
                                <option value="other">อื่นๆ</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="filterDateFrom" class="form-control form-control-sm" placeholder="จากวันที่">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="filterDateTo" class="form-control form-control-sm" placeholder="ถึงวันที่">
                        </div>
                        <div class="col-md-4">
                            <input type="text" id="searchReceipt" class="form-control form-control-sm" placeholder="ค้นหาชื่อ/หัวข้อ...">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="loadReceipts(1)"><i class="bi bi-search"></i> ค้นหา</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>เลขที่</th>
                                    <th>หัวข้อ</th>
                                    <th>ผู้ชำระ</th>
                                    <th>ประเภท</th>
                                    <th>จำนวนเงิน</th>
                                    <th>วันที่ออก</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="receiptsTable">
                                <tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><nav id="receiptPagination"></nav></div>
            </div>

        </div>
    </section>
</div>

<!-- Modal: Receipt Preview -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:1180px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>ใบเสร็จรับเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="receiptPreviewBody" style="background:#e9ecef;overflow-x:auto;">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" onclick="openEditReceiptNumber()" id="btnEditReceiptNum"><i class="bi bi-pencil-square me-1"></i> แก้ไขใบเสร็จ</button>
                <button type="button" class="btn btn-primary" onclick="downloadModalPDF()"><i class="bi bi-file-earmark-pdf me-1"></i> PDF</button>
                <button type="button" class="btn btn-success" onclick="downloadModalPNG()"><i class="bi bi-image me-1"></i> PNG</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create Receipt -->
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
                        <small class="text-muted">เว้นว่างเพื่อรันเลขอัตโนมัติ หรือกดปุ่มรันเลข</small>
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

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<!-- Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- html2canvas & jsPDF for export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<style>
.receipt-render {
    width: 1123px;
    height: 794px;
    font-family: 'Sarabun', sans-serif;
    color: #1a3c5e;
    line-height: 1.8;
    padding: 30px;
    background: #fff;
    margin: 0 auto;
    box-shadow: 0 4px 24px rgba(0,0,0,.15);
    overflow: hidden;
    position: relative;
}
.receipt-render .receipt-inner {
    border: 2px solid #1a3c5e;
    border-radius: 12px;
    padding: 40px 60px;
    position: absolute;
    top: 30px; left: 30px; right: 30px; bottom: 30px;
    display: flex;
    flex-direction: column;
}
.receipt-render .receipt-title { font-size: 28px; font-weight: 700; text-align: center; }
.receipt-render .receipt-org { font-size: 20px; font-weight: 600; text-align: center; }
.receipt-render .receipt-org-addr { font-size: 16px; text-align: center; margin-bottom: 10px; }
.receipt-render .receipt-body-section { font-size: 18px; flex-grow: 1; }
.receipt-render .dotted-line { border-bottom: 1px dotted #555; display: inline-block; min-width: 280px; margin: 0 5px; }
.receipt-render .receipt-amount-box { text-align: center; border: 1px solid #1a3c5e; border-radius: 8px; padding: 12px 20px; margin: 15px 0; font-size: 20px; }
.receipt-render .receipt-sign { display: flex; justify-content: flex-end; margin-top: 30px; font-size: 16px; }
</style>

<script>
let currentPage = 1;
let modalReceiptData = null;
let membersCache = [];
let categoriesCache = [];

$(function () {
    App.requireAdmin();
    loadReceipts();
    loadMembersList();
    loadCategories();

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

    // Set default issued date to today
    $('#createIssuedDate').val(new Date().toISOString().split('T')[0]);

    // Initialize Select2 for payer select
    initPayerSelect2();

    // When payer is selected via Select2
    $('#createPayerSelect').on('select2:select', function(e) {
        const data = e.params.data;
        if (data.id && String(data.id).startsWith('user_')) {
            // Existing member selected
            const userId = String(data.id).replace('user_', '');
            const member = membersCache.find(m => String(m.id) === userId);
            $('#createUserId').val(userId);
            $('#createPayerName').val(member ? member.full_name : data.text).prop('readonly', true);
            $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
        } else {
            // Custom text entered (non-member)
            const customName = String(data.id).replace(' (บุคคลภายนอก)', '');
            $('#createUserId').val('');
            $('#createPayerName').val(customName).prop('readonly', false);
            $('#payerNameHint').text('บุคคลภายนอก — แก้ไขชื่อได้');
        }
    });

    $('#createPayerSelect').on('select2:clear', function() {
        $('#createUserId').val('');
        $('#createPayerName').val('').prop('readonly', true);
        $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    });

    // When category is selected, auto-fill title
    $('#createCategory').on('change', function() {
        const catId = $(this).val();
        if (catId) {
            const cat = categoriesCache.find(c => String(c.id) === String(catId));
            if (cat) {
                $('#createTitle').val(cat.name);
            }
        }
    });

    // Create receipt form submit
    $('#createReceiptForm').on('submit', async function(e) {
        e.preventDefault();

        const userId = $('#createUserId').val();
        const payerName = $('#createPayerName').val().trim();
        if (!userId && !payerName) { App.error('กรุณาเลือกสมาชิก หรือระบุชื่อบุคคลภายนอก'); return; }

        const data = {
            title: $('#createTitle').val().trim(),
            receipt_type: 'other',
            amount: parseFloat($('#createAmount').val()),
            issued_date: $('#createIssuedDate').val() || undefined,
            description: $('#createDescription').val().trim() || undefined,
            receipt_number: $('#createReceiptNumber').val().trim() || undefined,
        };

        if (userId) {
            data.user_id = userId;
        } else {
            data.payer_name = payerName;
        }

        // Map category to title if not manually changed
        const catId = $('#createCategory').val();
        if (catId) {
            const cat = categoriesCache.find(c => String(c.id) === String(catId));
            if (cat && cat.name === 'ค่าธรรมเนียมสมาชิก') data.receipt_type = 'membership_fee';
            else if (cat && cat.name.includes('กิจกรรม')) data.receipt_type = 'activity_fee';
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
            loadReceipts(currentPage);
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

        const btn = $('#btnSaveReceiptNum');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        const result = await API.updateReceipt(updateData);
        btn.prop('disabled', false).html('<i class="bi bi-check me-1"></i> บันทึก');

        if (result.success) {
            App.success(result.message || 'แก้ไขเลขที่ใบเสร็จสำเร็จ');
            $('#editReceiptNumModal').modal('hide');
            loadReceipts(currentPage);
            // Refresh preview if open
            if (modalReceiptData && modalReceiptData.id == id) {
                viewReceipt(id);
            }
        } else {
            App.error(result.message || 'เกิดข้อผิดพลาด');
        }
    });
});

// Initialize Select2 for payer selector
function initPayerSelect2() {
    $('#createPayerSelect').select2({
        dropdownParent: $('#createReceiptModal'),
        placeholder: '-- พิมพ์ค้นหาสมาชิก หรือระบุชื่อบุคคลภายนอก --',
        allowClear: true,
        tags: true,
        createTag: function(params) {
            const term = $.trim(params.term);
            if (term === '') return null;
            return { id: term, text: term + ' (บุคคลภายนอก)', isCustom: true };
        },
        language: {
            noResults: function() { return 'ไม่พบข้อมูล — พิมพ์ชื่อเพื่อเพิ่มบุคคลภายนอก'; },
            searching: function() { return 'กำลังค้นหา...'; },
            inputTooShort: function() { return 'พิมพ์เพื่อค้นหา...'; }
        },
        templateResult: function(data) {
            if (data.loading) return data.text;
            if (data.isCustom) return $('<span><i class="bi bi-person-plus me-1"></i>' + App.escapeHtml(data.text) + '</span>');
            return $('<span>' + App.escapeHtml(data.text) + '</span>');
        },
    });
}

// Load members for Select2
async function loadMembersList() {
    const result = await API.getMembers({ per_page: 9999 });
    if (result.success && result.data) {
        membersCache = result.data;
        const select = $('#createPayerSelect');
        select.find('option:not(:first)').remove();
        result.data.forEach(m => {
            const opt = new Option(`${m.full_name} (${m.email || '-'})`, 'user_' + m.id, false, false);
            opt.dataset.fullName = m.full_name;
            select.append(opt);
        });
        select.trigger('change.select2');
    }
}

// Load finance categories (income only) for category selector
async function loadCategories() {
    const result = await API.getFinanceActiveCategories('income');
    if (result.success && result.data) {
        categoriesCache = result.data;
        const select = $('#createCategory');
        select.find('option:not(:first)').remove();
        result.data.forEach(c => {
            select.append(`<option value="${c.id}">${App.escapeHtml(c.name)}</option>`);
        });
    }
}

// Reset create form
function resetCreateForm() {
    $('#createReceiptForm')[0].reset();
    $('#createPayerSelect').val(null).trigger('change');
    $('#createUserId').val('');
    $('#createPayerName').val('').prop('readonly', true);
    $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    $('#createIssuedDate').val(new Date().toISOString().split('T')[0]);
    $('#createReceiptNumber').val('');
}

// Open create modal
function openCreateModal() {
    resetCreateForm();
    $('#createReceiptModal').modal('show');
}

// Auto-generate receipt number for create
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

// Auto-generate receipt number for edit
async function autoGenerateNumberForEdit() {
    if (!modalReceiptData) return;
    const result = await API.getNextReceiptNumber(modalReceiptData.issued_date);
    if (result.success && result.data) {
        $('#editReceiptNumber').val(result.data.receipt_number);
        App.success(`เลขที่ถัดไป: ${result.data.receipt_number}`);
    } else {
        App.error(result.message || 'ไม่สามารถดึงเลขที่ได้');
    }
}

// Open edit receipt number modal
function openEditReceiptNumber() {
    if (!modalReceiptData) return;
    $('#editReceiptId').val(modalReceiptData.id);
    $('#editReceiptNumber').val(modalReceiptData.receipt_number);
    $('#editPayerName').val(modalReceiptData.payer_name || '');
    $('#editReceiptInfo').text(`เล่มที่: ${modalReceiptData.book_number} | วันที่ออก: ${App.formatDate(modalReceiptData.issued_date)}`);
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

async function loadReceipts(page = 1) {
    currentPage = page;
    const tbody = $('#receiptsTable');
    tbody.html('<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const params = { page, per_page: 30 };
    const type = $('#filterType').val();
    const dateFrom = $('#filterDateFrom').val();
    const dateTo = $('#filterDateTo').val();
    const search = $('#searchReceipt').val().trim();

    if (type) params.receipt_type = type;
    if (dateFrom) params.date_from = dateFrom;
    if (dateTo) params.date_to = dateTo;
    if (search) params.search = search;

    const result = await API.getReceipts(params);

    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบใบเสร็จ</td></tr>');
        $('#receiptCount').text('');
        return;
    }

    if (result.pagination) {
        $('#receiptCount').text(`ทั้งหมด ${result.pagination.total} ใบ`);
    }

    const typeLabels = { 'membership_fee': 'ค่าธรรมเนียม', 'activity_fee': 'ค่ากิจกรรม', 'other': 'อื่นๆ' };
    const typeBadges = { 'membership_fee': 'bg-primary', 'activity_fee': 'bg-info', 'other': 'bg-secondary' };

    // Current Buddhist year for edit restriction
    const currentBuddhistYear = new Date().getFullYear() + 543;

    let html = '';
    result.data.forEach(r => {
        const issuedBuddhistYear = new Date(r.issued_date).getFullYear() + 543;
        const canEdit = true; // Admin can always edit
        html += `<tr>
            <td>${r.book_number} / ${r.receipt_number}</td>
            <td>${r.title}</td>
            <td>${r.payer_name || r.full_name || '-'}</td>
            <td><span class="badge ${typeBadges[r.receipt_type] || 'bg-secondary'}">${typeLabels[r.receipt_type] || r.receipt_type}</span></td>
            <td>${App.formatCurrency(r.amount)}</td>
            <td>${App.formatDate(r.issued_date)}</td>
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="viewReceipt(${r.id})" title="ดูใบเสร็จ">
                    <i class="bi bi-eye"></i>
                </button>
                ${canEdit ? `<button class="btn btn-outline-warning btn-sm" onclick="quickEditReceipt(${r.id}, '${App.escapeHtml(r.receipt_number)}', '${App.escapeHtml(r.book_number)}', '${r.issued_date}', '${App.escapeHtml(r.payer_name || r.full_name || '')}')" title="แก้ไข">
                    <i class="bi bi-pencil"></i>
                </button>` : ''}
            </td>
        </tr>`;
    });
    tbody.html(html);
    if (result.pagination) App.buildPagination('#receiptPagination', result.pagination, loadReceipts);
}

// Quick edit receipt from table row
function quickEditReceipt(id, currentNum, bookNum, issuedDate, payerName) {
    modalReceiptData = { id, receipt_number: currentNum, book_number: bookNum, issued_date: issuedDate, payer_name: payerName };
    openEditReceiptNumber();
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

    modalReceiptData = result.data;
    const r = modalReceiptData;

    // Show/hide edit button (admin always can)
    $('#btnEditReceiptNum').show();

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

    body.html(`<div id="modalReceiptCanvas" class="receipt-render">
        <div class="receipt-inner">
        <div style="display:flex;justify-content:space-between;font-size:16px;margin-bottom:10px;">
            <div>เล่มที่ ${App.escapeHtml(r.book_number)}</div>
            <div>เลขที่ ${r.receipt_number}</div>
        </div>
        <div style="text-align:center;margin-bottom:15px;">
            ${window._receiptLogoUrl ? `<div style="margin-bottom:8px;"><img src="${window._receiptLogoUrl}" alt="Logo" style="max-height:70px;"></div>` : ''}
            <div class="receipt-title">ใบเสร็จรับเงิน</div>
            <div class="receipt-org">${App.escapeHtml(r.organization_name)}</div>
            <div class="receipt-org-addr">${App.escapeHtml(r.organization_address)}</div>
        </div>
        <div style="text-align:right;font-size:16px;margin-bottom:12px;">${dateStr}</div>
        <div class="receipt-body-section">
            <div style="margin-bottom:8px;font-size:18px;"><strong>ได้รับเงินจาก</strong> <span class="dotted-line" style="min-width:500px">&nbsp;${App.escapeHtml(r.payer_name)}&nbsp;</span></div>
            ${r.payer_address ? `<div style="margin-bottom:8px;font-size:18px;"><strong>ที่อยู่</strong> <span class="dotted-line" style="min-width:540px">&nbsp;${App.escapeHtml(r.payer_address)}&nbsp;</span></div>` : ''}
            <div style="margin-bottom:8px;font-size:18px;"><strong>เป็น</strong> <span class="dotted-line" style="min-width:560px">&nbsp;${App.escapeHtml((r.description||'').replace(/\s*จำนวน\s*[\d,.]+\s*บาท/g,''))}&nbsp;</span></div>
            <div class="receipt-amount-box">
                <strong>จำนวน ${App.formatCurrency(r.amount)}</strong> (${App.escapeHtml(r.amount_text)}) ไว้ถูกต้องแล้ว
            </div>
        </div>
        <div class="receipt-sign">
        <div style="text-align:center;">
            ${r.signature_mode === 'electronic' && r.signature_image ? `<div style="margin-bottom:-25px;"><img src="${signatureImgSrc}" alt="ลายเซ็น" style="max-height:60px;"></div>` : '<div style="margin-bottom:30px;"></div>'}
            <div>(ลงชื่อ) ................................... ผู้รับเงิน</div>
            ${r.signature_show_name === '1' && r.signature_name ? `<div style="margin-top:5px;">(${App.escapeHtml(r.signature_name)})</div>` : ''}
            ${r.signature_show_position === '1' ? `<div style="margin-top:3px;">${App.escapeHtml(r.signature_position || 'เหรัญญิก')}</div>` : ''}
        </div>
        </div>
        </div>
    </div>`);
}

async function downloadModalPDF() {
    if (!modalReceiptData) return;
    try {
        const canvas = await html2canvas(document.getElementById('modalReceiptCanvas'), {
            scale: 2,
            backgroundColor: '#fff',
            useCORS: true,
            allowTaint: false,
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
        pdf.save(`receipt_${modalReceiptData.receipt_number}.pdf`);
    } catch (err) { console.error('PDF Error:', err); App.error('เกิดข้อผิดพลาดในการสร้าง PDF: ' + err.message); }
}

async function downloadModalPNG() {
    if (!modalReceiptData) return;
    try {
        const canvas = await html2canvas(document.getElementById('modalReceiptCanvas'), {
            scale: 2,
            backgroundColor: '#fff',
            useCORS: true,
            allowTaint: false,
        });
        const link = document.createElement('a');
        link.download = `receipt_${modalReceiptData.receipt_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (err) { console.error('PNG Error:', err); App.error('เกิดข้อผิดพลาดในการสร้างรูป: ' + err.message); }
}

$('#filterType').on('change', () => loadReceipts(1));
$('#filterDateFrom, #filterDateTo').on('change', () => loadReceipts(1));
let searchTimer;
$('#searchReceipt').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadReceipts(1), 400);
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
