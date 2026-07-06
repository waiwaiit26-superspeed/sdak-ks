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
                <button type="button" class="btn btn-warning" onclick="openEditReceiptNumber()" id="btnEditReceiptNum"><i class="bi bi-pencil-square me-1"></i> แก้ไขใบเสร็จ</button>
                <button type="button" class="btn btn-info text-white" onclick="showReferenceInfo()" id="btnRefInfo" style="display:none;"><i class="bi bi-link-45deg me-1"></i> ข้อมูลอ้างอิง</button>
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
                <!-- Reference Source Section -->
                <div class="card bg-light mb-3" id="referenceSourceSection">
                    <div class="card-body py-2">
                        <h6 class="mb-2"><i class="bi bi-link-45deg me-1"></i>โหลดข้อมูลจากระบบ <small class="text-muted">(เลือกได้)</small></h6>
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <select id="createRefType" class="form-control form-control-sm">
                                    <option value="">-- ไม่อ้างอิง (ออกใบเสร็จด้วยตัวเอง) --</option>
                                    <option value="membership_fee">ค่าธรรมเนียมสมาชิก</option>
                                    <option value="activity_fee">ค่าลงทะเบียนกิจกรรม</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <select id="createRefSelect" class="form-control form-control-sm" style="width:100%" disabled>
                                    <option value="">-- เลือกรายการอ้างอิง --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-outline-info btn-sm w-100" onclick="loadReferenceData()" id="btnLoadRef" disabled>
                                    <i class="bi bi-download me-1"></i> โหลดข้อมูล
                                </button>
                            </div>
                        </div>
                        <div id="refLoadedInfo" class="mt-2" style="display:none;">
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>โหลดข้อมูลจากระบบแล้ว</span>
                            <small class="text-muted ms-2" id="refLoadedLabel"></small>
                            <input type="hidden" id="createRefId">
                        </div>
                    </div>
                </div>

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
                <!-- Reference reload button -->
                <div id="editRefReloadSection" class="mb-3" style="display:none;">
                    <div class="alert alert-info py-2 mb-0">
                        <i class="bi bi-link-45deg me-1"></i>
                        <span id="editRefLabel">ใบเสร็จนี้อ้างอิงจากระบบ</span>
                        <button type="button" class="btn btn-outline-info btn-sm ms-2" onclick="reloadRefDataIntoEditFromAPI()">
                            <i class="bi bi-download me-1"></i> โหลดข้อมูลจากระบบ
                        </button>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label fw-bold">เลขที่ใบเสร็จ</label>
                        <div class="input-group">
                            <input type="number" id="editReceiptNumber" class="form-control" min="1" required>
                            <button type="button" class="btn btn-outline-info" onclick="autoGenerateNumberForEdit()" title="รันเลขอัตโนมัติ">
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-bold">วันที่ออก (ปี)</label>
                        <input type="date" id="editIssuedDate" class="form-control" required>
                    </div>
                </div>
                <div id="editDuplicateWarning" class="alert alert-danger py-2 mb-3" style="display:none;">
                    <i class="bi bi-exclamation-triangle me-1"></i> <span id="editDuplicateMsg"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">ชื่อผู้ชำระ</label>
                    <input type="text" id="editPayerName" class="form-control" placeholder="ชื่อผู้ชำระเงิน">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">รายละเอียด (เป็น)</label>
                    <textarea id="editDescription" class="form-control" rows="2" placeholder="รายละเอียด"></textarea>
                </div>
                <hr>
                <h6 class="mb-3"><i class="bi bi-geo-alt me-1"></i>ที่อยู่ผู้ชำระเงิน</h6>
                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label small text-muted">เลขที่</label>
                        <input type="text" id="editAddrNo" class="form-control form-control-sm" placeholder="เลขที่">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted">หมู่ที่</label>
                        <input type="text" id="editAddrMoo" class="form-control form-control-sm" placeholder="หมู่">
                    </div>
                    <div class="col-4">
                        <label class="form-label small text-muted">ซอย</label>
                        <input type="text" id="editAddrSoi" class="form-control form-control-sm" placeholder="ซอย">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small text-muted">ถนน</label>
                    <input type="text" id="editAddrRoad" class="form-control form-control-sm" placeholder="ถนน">
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small text-muted">ตำบล</label>
                        <input type="text" id="editAddrSub" class="form-control form-control-sm" placeholder="ตำบล">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">อำเภอ</label>
                        <input type="text" id="editAddrDist" class="form-control form-control-sm" placeholder="อำเภอ">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label small text-muted">จังหวัด</label>
                        <input type="text" id="editAddrProv" class="form-control form-control-sm" placeholder="จังหวัด">
                    </div>
                    <div class="col-6">
                        <label class="form-label small text-muted">รหัสไปรษณีย์</label>
                        <input type="text" id="editAddrZip" class="form-control form-control-sm" placeholder="รหัสไปรษณีย์">
                    </div>
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
let currentPage = 1;
let modalReceiptData = null;
let membersCache = [];
let categoriesCache = [];

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

    /* Safety timeout */
    setTimeout(() => {
        if (receipt.style.visibility === 'hidden') done();
    }, 5000);
}

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
            // Auto-fill payer address
            if (member) {
                const addrJson = buildPayerAddress(member);
                $('#createPayerAddress').val(flatPayerAddress(addrJson)).data('addrJson', addrJson);
            }
        } else {
            // Custom text entered (non-member)
            const customName = String(data.id).replace(' (บุคคลภายนอก)', '');
            $('#createUserId').val('');
            $('#createPayerName').val(customName).prop('readonly', false);
            $('#createPayerAddress').val('').removeData('addrJson');
            $('#payerNameHint').text('บุคคลภายนอก — แก้ไขชื่อได้');
        }
    });

    $('#createPayerSelect').on('select2:clear', function() {
        $('#createUserId').val('');
        $('#createPayerName').val('').prop('readonly', true);
        $('#createPayerAddress').val('').removeData('addrJson');
        $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    });

    // When date changes, re-generate receipt number for new year
    $('#createIssuedDate').on('change', function() {
        autoGenerateNumber();
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

        // Add reference data if loaded from system
        const refType = $('#createRefType').val();
        const refId   = $('#createRefId').val();
        if (refType && refId) {
            data.receipt_type = refType;
            data.reference_id = parseInt(refId);
        }

        const payerAddress = $('#createPayerAddress').data('addrJson') || $('#createPayerAddress').val().trim();
        if (payerAddress) data.payer_address = payerAddress;

        if (userId) {
            data.user_id = userId;
        } else {
            data.payer_name = payerName;
        }

        // Map category to receipt_type only if not already set by reference
        if (!refType || !refId) {
            const catId = $('#createCategory').val();
            if (catId) {
                const cat = categoriesCache.find(c => String(c.id) === String(catId));
                if (cat && cat.name === 'ค่าธรรมเนียมสมาชิก') data.receipt_type = 'membership_fee';
                else if (cat && cat.name.includes('กิจกรรม')) data.receipt_type = 'activity_fee';
            }
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

    // Edit receipt form submit
    $('#editReceiptNumForm').on('submit', async function(e) {
        e.preventDefault();

        const id = $('#editReceiptId').val();
        const receiptNumber = $('#editReceiptNumber').val().trim();
        const issuedDate = $('#editIssuedDate').val();
        const payerName = $('#editPayerName').val().trim();
        if (!receiptNumber) { App.error('กรุณาระบุเลขที่ใบเสร็จ'); return; }
        if (!issuedDate) { App.error('กรุณาระบุวันที่ออก'); return; }

        // Check duplicate before saving
        if ($('#editDuplicateWarning').is(':visible')) {
            App.error('เลขที่ใบเสร็จซ้ำกับที่มีอยู่แล้ว กรุณาเปลี่ยนเลขที่หรือวันที่');
            return;
        }

        const updateData = { id: id, receipt_number: receiptNumber, issued_date: issuedDate };
        if (payerName) updateData.payer_name = payerName;

        // Include description
        const desc = $('#editDescription').val().trim();
        if (desc !== (modalReceiptData.description || '').trim()) {
            updateData.description = desc;
        }

        // Build structured address JSON
        const no   = $('#editAddrNo').val().trim();
        const moo  = $('#editAddrMoo').val().trim();
        const soi  = $('#editAddrSoi').val().trim();
        const road = $('#editAddrRoad').val().trim();
        const sub  = $('#editAddrSub').val().trim();
        const dist = $('#editAddrDist').val().trim();
        const prov = $('#editAddrProv').val().trim();
        const zip  = $('#editAddrZip').val().trim();

        let detail = no && no !== '-' ? no : '';
        if (moo && moo !== '-') detail += '   หมู่ ' + moo;
        if (soi && soi !== '-') detail += '   ซอย ' + soi;
        if (road && road !== '-') detail += '   ถนน ' + road;
        detail = detail.trim();

        if (detail || sub || dist || prov) {
            updateData.payer_address = JSON.stringify({ detail, subdistrict: sub, district: dist, province: prov, zipcode: zip });
        } else {
            updateData.payer_address = null;
        }

        const btn = $('#btnSaveReceiptNum');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

        const result = await API.updateReceipt(updateData);
        btn.prop('disabled', false).html('<i class="bi bi-check me-1"></i> บันทึก');

        if (result.success) {
            App.success(result.message || 'แก้ไขใบเสร็จสำเร็จ');
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

    // Duplicate check when receipt number or date changes in edit form
    let editDupTimer = null;
    $('#editReceiptNumber, #editIssuedDate').on('input change', function() {
        clearTimeout(editDupTimer);
        editDupTimer = setTimeout(checkEditDuplicate, 400);
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
    $('#createPayerAddress').val('').removeData('addrJson');
    $('#payerNameHint').text('ดึงจากชื่อสมาชิกอัตโนมัติ');
    $('#createIssuedDate').val(new Date().toISOString().split('T')[0]);
    $('#createReceiptNumber').val('');
    $('#createBookNumberDisplay').text('-');
    // Clear reference fields
    $('#createRefType').val('');
    $('#createRefSelect').prop('disabled', true).find('option:not(:first)').remove();
    $('#btnLoadRef').prop('disabled', true);
    clearRefLoaded();
}

// Open create modal
function openCreateModal() {
    resetCreateForm();
    $('#createReceiptModal').modal('show');
    autoGenerateNumber();
}

// Auto-generate receipt number for create
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

// Auto-generate receipt number for edit
async function autoGenerateNumberForEdit() {
    if (!modalReceiptData) return;
    const issuedDate = $('#editIssuedDate').val() || modalReceiptData.issued_date;
    const result = await API.getNextReceiptNumber(issuedDate);
    if (result.success && result.data) {
        $('#editReceiptNumber').val(result.data.receipt_number);
        App.success(`เลขที่ถัดไป: ${result.data.receipt_number}`);
        checkEditDuplicate();
    } else {
        App.error(result.message || 'ไม่สามารถดึงเลขที่ได้');
    }
}

// Check for duplicate receipt number + book_number (year)
async function checkEditDuplicate() {
    const receiptNumber = $('#editReceiptNumber').val().trim();
    const issuedDate = $('#editIssuedDate').val();
    const id = $('#editReceiptId').val();
    if (!receiptNumber || !issuedDate) {
        $('#editDuplicateWarning').hide();
        return;
    }
    try {
        const result = await API.checkReceiptDuplicate(receiptNumber, issuedDate, id);
        if (result.success && result.data && result.data.duplicate) {
            $('#editDuplicateMsg').text(`เลขที่ใบเสร็จ ${receiptNumber} ในปีนี้ซ้ำกับใบเสร็จ #${result.data.existing_id} (${result.data.payer_name || '-'})`);
            $('#editDuplicateWarning').show();
        } else {
            $('#editDuplicateWarning').hide();
        }
    } catch(e) {
        $('#editDuplicateWarning').hide();
    }
}

// Open edit receipt modal
function openEditReceiptNumber() {
    if (!modalReceiptData) return;
    $('#editReceiptId').val(modalReceiptData.id);
    $('#editReceiptNumber').val(modalReceiptData.receipt_number);
    $('#editIssuedDate').val(modalReceiptData.issued_date || '');
    $('#editPayerName').val(modalReceiptData.payer_name || '');
    $('#editDescription').val(modalReceiptData.description || '');

    // Parse payer_address into structured fields
    let addr = {};
    try { addr = JSON.parse(modalReceiptData.payer_address); } catch(e) {}
    if (!addr || typeof addr !== 'object') addr = {};

    let detail = addr.detail || '';
    let no = '', moo = '', soi = '', road = '';
    const roadMatch = detail.match(/\s+ถนน\s*(.+?)$/);
    if (roadMatch) { road = roadMatch[1].trim(); detail = detail.replace(roadMatch[0], ''); }
    const soiMatch = detail.match(/\s+ซอย\s*(.+?)$/);
    if (soiMatch) { soi = soiMatch[1].trim(); detail = detail.replace(soiMatch[0], ''); }
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

    // Update book number info display
    updateEditReceiptInfo();
    $('#editDuplicateWarning').hide();

    // Show reference reload button if receipt has reference
    if (modalReceiptData.reference_id && modalReceiptData.receipt_type && modalReceiptData.receipt_type !== 'other') {
        const typeLabel = modalReceiptData.receipt_type === 'membership_fee' ? 'ค่าธรรมเนียมสมาชิก' : 'ค่าลงทะเบียนกิจกรรม';
        $('#editRefLabel').html(`<strong>อ้างอิง:</strong> ${typeLabel} (REF #${modalReceiptData.reference_id})`);
        $('#editRefReloadSection').show();
    } else {
        $('#editRefReloadSection').hide();
    }

    $('#editReceiptNumModal').modal('show');
}

// Update receipt info text when date changes
function updateEditReceiptInfo() {
    const issuedDate = $('#editIssuedDate').val();
    if (issuedDate) {
        const ceYear = new Date(issuedDate).getFullYear();
        const buddhistYear2 = String(ceYear + 543).slice(-2);
        const prefix = modalReceiptData ? (modalReceiptData.book_number || '').replace(/\s*\d{2}$/, '').trim() : '';
        const bookNum = prefix ? prefix + ' ' + buddhistYear2 : buddhistYear2;
        $('#editReceiptInfo').html(`<i class="bi bi-book me-1"></i>เล่มที่: <strong>${bookNum}</strong> | วันที่ออก: <strong>${App.formatDate(issuedDate)}</strong>`);
    }
}

// Listen for date change on edit form
$('#editIssuedDate').on('change', function() {
    updateEditReceiptInfo();
});

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
                // Plain text fallback
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
        const refBadge = r.reference_id ? `<span class="badge bg-light text-dark border" style="font-size:10px;" title="อ้างอิง #${r.reference_id}"><i class="bi bi-link-45deg"></i> REF-${r.reference_id}</span>` : '';
        html += `<tr>
            <td>${r.book_number} / ${r.receipt_number}</td>
            <td>${r.title}</td>
            <td>${r.payer_name || r.full_name || '-'}</td>
            <td><span class="badge ${typeBadges[r.receipt_type] || 'bg-secondary'}">${typeLabels[r.receipt_type] || r.receipt_type}</span> ${refBadge}</td>
            <td>${App.formatCurrency(r.amount)}</td>
            <td>${App.formatDate(r.issued_date)}</td>
            <td>
                <button class="btn btn-outline-primary btn-sm" onclick="viewReceipt(${r.id})" title="ดูใบเสร็จ">
                    <i class="bi bi-eye"></i>
                </button>
                ${canEdit ? `<button class="btn btn-outline-warning btn-sm" onclick="quickEditReceipt(${r.id})" title="แก้ไข" data-receipt='${JSON.stringify({id:r.id, receipt_number:r.receipt_number, book_number:r.book_number, issued_date:r.issued_date, payer_name:r.payer_name||r.full_name||'', payer_address:r.payer_address||'', receipt_type:r.receipt_type||'other', reference_id:r.reference_id||null, description:r.description||''}).replace(/'/g, "&#39;")}'>
                    <i class="bi bi-pencil"></i>
                </button>` : ''}
            </td>
        </tr>`;
    });
    tbody.html(html);
    if (result.pagination) App.buildPagination('#receiptPagination', result.pagination, loadReceipts);
}

// Quick edit receipt from table row
function quickEditReceipt(id) {
    const btn = $(`button[onclick="quickEditReceipt(${id})"]`);
    try {
        modalReceiptData = JSON.parse(btn.attr('data-receipt'));
    } catch(e) {
        // Fallback: load from API
        viewReceipt(id);
        return;
    }
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

    // Show reference info button if receipt has reference data
    if (r.reference_data) {
        $('#btnRefInfo').show();
    } else {
        $('#btnRefInfo').hide();
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

    body.html(`${r.reference_data ? `<div class="alert alert-info py-2 mb-2" style="font-size:13px;">
        <i class="bi bi-link-45deg me-1"></i><strong>อ้างอิงจาก:</strong>
        ${r.reference_data.source_label}
        — ${App.escapeHtml(r.reference_data.full_name)}
        ${r.reference_data.source_type === 'membership_fee' ? '(ปี ' + r.reference_data.fee_year + ')' : ''}
        ${r.reference_data.source_type === 'activity_fee' && r.reference_data.activity_title ? '(' + App.escapeHtml(r.reference_data.activity_title) + ')' : ''}
        <span class="badge bg-light text-dark border ms-2">REF #${r.reference_data.source_id}</span>
        <button class="btn btn-outline-info btn-sm ms-2 py-0 px-1" onclick="showReferenceInfo()" title="ดูรายละเอียด"><i class="bi bi-info-circle"></i></button>
    </div>` : ''}
    <div id="receiptModalLoading" style="text-align:center;padding:60px 20px;">
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
            <div style="margin-bottom:8px;font-size:18px;"><strong style="white-space:nowrap">เป็น</strong><span class="dotted-line" style="flex:1">${App.escapeHtml((r.description||'').replace(/\s*จำนวน\s*[\d,.]+\s*บาท/g,''))}</span></div>
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

    // Wait for images then scale
    scaleModalReceipt('receiptPreviewBody', 'modalReceiptCanvas', 'receiptModalLoading', 'receiptModalPercent');
}

async function downloadModalPDF() {
    if (!modalReceiptData) return;
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
        // A4 landscape: 297 x 210 mm
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pageW = 297, pageH = 210;
        const margin = 5;
        const imgWidth = pageW - margin * 2;
        const imgHeight = (canvas.height * imgWidth) / canvas.width;
        pdf.addImage(imgData, 'PNG', margin, margin, imgWidth, Math.min(imgHeight, pageH - margin * 2));
        pdf.save(`receipt_${modalReceiptData.receipt_number}.pdf`);
    } catch (err) { console.error('PDF Error:', err); App.error('เกิดข้อผิดพลาดในการสร้าง PDF: ' + err.message); }
    el.style.transform = origTransform;
}

async function downloadModalPNG() {
    if (!modalReceiptData) return;
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
        link.download = `receipt_${modalReceiptData.receipt_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (err) { console.error('PNG Error:', err); App.error('เกิดข้อผิดพลาดในการสร้างรูป: ' + err.message); }
    el.style.transform = origTransform;
}

$('#filterType').on('change', () => loadReceipts(1));
$('#filterDateFrom, #filterDateTo').on('change', () => loadReceipts(1));
let searchTimer;
$('#searchReceipt').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadReceipts(1), 400);
});

/* ══════════════════════════════════════════════
   Reference / Source Data Functions
   ══════════════════════════════════════════════ */

// When reference type changes in create modal
$('#createRefType').on('change', async function() {
    const type = $(this).val();
    const sel = $('#createRefSelect');
    sel.find('option:not(:first)').remove();

    if (!type) {
        sel.prop('disabled', true);
        $('#btnLoadRef').prop('disabled', true);
        clearRefLoaded();
        return;
    }

    sel.prop('disabled', false);
    sel.append('<option disabled>กำลังโหลด...</option>');

    const result = await API.searchReceiptReference(type);
    sel.find('option:disabled').remove();

    if (result.success && result.data) {
        result.data.forEach(r => {
            const hasReceipt = r.has_receipt ? ' [มีใบเสร็จแล้ว]' : '';
            sel.append(`<option value="${r.reference_id}" data-has-receipt="${r.has_receipt ? 1 : 0}">${App.escapeHtml(r.label)}${hasReceipt}</option>`);
        });
    }
});

$('#createRefSelect').on('change', function() {
    const val = $(this).val();
    $('#btnLoadRef').prop('disabled', !val);
    clearRefLoaded();
});

function clearRefLoaded() {
    $('#refLoadedInfo').hide();
    $('#refLoadedLabel').text('');
    $('#createRefId').val('');
}

// Load reference data and auto-fill the create form
async function loadReferenceData() {
    const refType = $('#createRefType').val();
    const refId   = $('#createRefSelect').val();
    if (!refType || !refId) { App.error('กรุณาเลือกรายการอ้างอิง'); return; }

    const btn = $('#btnLoadRef');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    const result = await API.getReceiptReferenceData(refType, refId);
    btn.prop('disabled', false).html('<i class="bi bi-download me-1"></i> โหลดข้อมูล');

    if (!result.success || !result.data) {
        App.error(result.message || 'ไม่พบข้อมูลอ้างอิง');
        return;
    }

    const ref = result.data;

    // Auto-fill create form with reference data
    // Select or display member
    if (ref.user_id) {
        const userOpt = $(`#createPayerSelect option[value="user_${ref.user_id}"]`);
        if (userOpt.length) {
            $('#createPayerSelect').val('user_' + ref.user_id).trigger('change');
        }
        $('#createUserId').val(ref.user_id);
        $('#createPayerName').val(ref.full_name).prop('readonly', true);
        $('#payerNameHint').text('โหลดจากข้อมูลอ้างอิง');

        // Build address
        const fakeMember = {
            full_name: ref.full_name,
            work_address: ref.work_address || '',
            home_address: ref.home_address || '',
            school_organization: ref.school_organization || ''
        };
        const addrJson = buildPayerAddress(fakeMember);
        if (addrJson) {
            $('#createPayerAddress').val(flatPayerAddress(addrJson)).data('addrJson', addrJson);
        }
    }

    // Fill amount
    if (ref.source_type === 'membership_fee' && ref.fee_amount) {
        $('#createAmount').val(ref.fee_amount);
    } else if (ref.source_type === 'activity_fee' && ref.activity_fee_amount) {
        $('#createAmount').val(ref.activity_fee_amount);
    }

    // Fill title and description
    if (ref.source_type === 'membership_fee') {
        const feeLabel = ref.fee_type === 'onetime' ? 'ครั้งเดียว' : 'ปี ' + ref.fee_year;
        const typeLabel = ref.member_type_label ? ref.member_type_label : '';
        const title = 'ค่าธรรมเนียมสมาชิก' + (typeLabel ? typeLabel : '');
        $('#createTitle').val(title);
        $('#createDescription').val(title + ' (' + feeLabel + ')');

        // Set category to membership_fee
        const mfCat = categoriesCache.find(c => c.name === 'ค่าธรรมเนียมสมาชิก');
        if (mfCat) $('#createCategory').val(mfCat.id);
    } else if (ref.source_type === 'activity_fee') {
        const title = 'ค่าลงทะเบียนกิจกรรม';
        const desc = ref.activity_fee_description
            ? `ค่าลงทะเบียนเข้าร่วม "${ref.activity_title}" (${ref.activity_fee_description})`
            : `ค่าลงทะเบียนเข้าร่วม "${ref.activity_title}"`;
        $('#createTitle').val(title);
        $('#createDescription').val(desc);

        // Set category to activity_fee if exists
        const afCat = categoriesCache.find(c => c.name.includes('กิจกรรม'));
        if (afCat) $('#createCategory').val(afCat.id);
    }

    // Store reference info
    $('#createRefId').val(refId);
    $('#refLoadedInfo').show();
    $('#refLoadedLabel').text(ref.source_label + ' — ' + ref.full_name);

    App.success('โหลดข้อมูลจากระบบเรียบร้อย');
}

// Show reference info panel for current receipt
async function showReferenceInfo() {
    if (!modalReceiptData) return;

    const refType = modalReceiptData.receipt_type;
    const refId   = modalReceiptData.reference_id;
    const refData = modalReceiptData.reference_data;

    if (!refData) {
        App.error('ไม่พบข้อมูลอ้างอิง');
        return;
    }

    let html = '<div class="p-3">';
    html += '<h5 class="mb-3"><i class="bi bi-link-45deg me-2"></i>ข้อมูลอ้างอิงใบเสร็จ</h5>';
    html += '<div class="card">';
    html += '<div class="card-body">';

    // Source type badge
    const sourceBadge = refData.source_type === 'membership_fee'
        ? '<span class="badge bg-primary">ค่าธรรมเนียมสมาชิก</span>'
        : '<span class="badge bg-info">ค่าลงทะเบียนกิจกรรม</span>';
    html += `<div class="mb-3">${sourceBadge} <small class="text-muted">Reference ID: ${refData.source_id}</small></div>`;

    // Member info
    html += '<div class="row mb-3">';
    html += '<div class="col-md-2 text-center">';
    if (refData.profile_image || refData.google_picture) {
        html += `<img src="${App.getProfileImage(refData)}" class="rounded-circle mb-2" style="width:60px;height:60px;object-fit:cover;">`;
    } else {
        html += '<i class="bi bi-person-circle" style="font-size:48px;color:#adb5bd;"></i>';
    }
    html += '</div>';
    html += '<div class="col-md-10">';
    html += `<h6 class="mb-1">${App.escapeHtml(refData.full_name)}</h6>`;
    html += `<small class="text-muted">${App.escapeHtml(refData.email || '-')}</small>`;
    if (refData.phone) html += `<br><small class="text-muted"><i class="bi bi-telephone me-1"></i>${App.escapeHtml(refData.phone)}</small>`;
    if (refData.school_organization) html += `<br><small class="text-muted"><i class="bi bi-building me-1"></i>${App.escapeHtml(refData.school_organization)}</small>`;
    if (refData.member_type_label) html += `<br><span class="badge bg-outline-secondary border">${App.escapeHtml(refData.member_type_label)}</span>`;
    html += '</div>';
    html += '</div>';

    // Source-specific info
    if (refData.source_type === 'membership_fee') {
        const feeLabel = refData.fee_type === 'onetime' ? 'ครั้งเดียว' : 'ประจำปี';
        const statusBadge = refData.fee_status === 'paid' ? '<span class="badge bg-success">ชำระแล้ว</span>' : `<span class="badge bg-warning">${refData.fee_status}</span>`;
        html += '<table class="table table-sm table-bordered">';
        html += `<tr><td class="fw-bold" width="35%">ประเภทค่าธรรมเนียม</td><td>${feeLabel}</td></tr>`;
        html += `<tr><td class="fw-bold">ปี พ.ศ.</td><td>${refData.fee_year}</td></tr>`;
        html += `<tr><td class="fw-bold">จำนวนเงิน</td><td>${App.formatCurrency(refData.fee_amount)}</td></tr>`;
        html += `<tr><td class="fw-bold">สถานะ</td><td>${statusBadge}</td></tr>`;
        if (refData.paid_at) html += `<tr><td class="fw-bold">วันที่ชำระ</td><td>${App.formatDate(refData.paid_at)}</td></tr>`;
        html += '</table>';

        if (refData.payment_slip) {
            const slipUrl = refData.payment_slip.startsWith('http') ? refData.payment_slip : (BASE_PATH + refData.payment_slip);
            html += `<div class="text-center"><a href="${slipUrl}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-image me-1"></i>ดูหลักฐานการชำระ</a></div>`;
        }
    } else if (refData.source_type === 'activity_fee') {
        const payBadge = refData.payment_status === 'paid' ? '<span class="badge bg-success">ชำระแล้ว</span>' : `<span class="badge bg-warning">${refData.payment_status}</span>`;
        html += '<table class="table table-sm table-bordered">';
        html += `<tr><td class="fw-bold" width="35%">กิจกรรม</td><td>${App.escapeHtml(refData.activity_title)}</td></tr>`;
        if (refData.activity_location) html += `<tr><td class="fw-bold">สถานที่</td><td>${App.escapeHtml(refData.activity_location)}</td></tr>`;
        if (refData.activity_start_date) html += `<tr><td class="fw-bold">วันที่จัดกิจกรรม</td><td>${App.formatDate(refData.activity_start_date)}${refData.activity_end_date ? ' - ' + App.formatDate(refData.activity_end_date) : ''}</td></tr>`;
        html += `<tr><td class="fw-bold">ค่าลงทะเบียน</td><td>${App.formatCurrency(refData.activity_fee_amount)}</td></tr>`;
        html += `<tr><td class="fw-bold">สถานะการชำระ</td><td>${payBadge}</td></tr>`;
        if (refData.registered_at) html += `<tr><td class="fw-bold">วันที่ลงทะเบียน</td><td>${App.formatDate(refData.registered_at)}</td></tr>`;
        html += '</table>';

        if (refData.payment_proof) {
            const proofUrl = refData.payment_proof.startsWith('http') ? refData.payment_proof : (BASE_PATH + refData.payment_proof);
            html += `<div class="text-center"><a href="${proofUrl}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-image me-1"></i>ดูหลักฐานการชำระ</a></div>`;
        }
    }

    html += '</div></div>';

    // Button to reload data into edit form
    html += '<div class="text-center mt-3">';
    html += `<button class="btn btn-warning btn-sm" onclick="reloadRefDataIntoEdit()"><i class="bi bi-arrow-repeat me-1"></i> โหลดข้อมูลซ้ำเข้าแบบแก้ไข</button>`;
    html += '</div>';

    html += '</div>';

    // Show in a sub-modal or replace the preview body temporarily
    if ($('#referenceInfoModal').length === 0) {
        $('body').append(`
            <div class="modal fade" id="referenceInfoModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title"><i class="bi bi-link-45deg me-2"></i>ข้อมูลอ้างอิงใบเสร็จ</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body" id="referenceInfoBody"></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }
    $('#referenceInfoBody').html(html);
    $('#referenceInfoModal').modal('show');
}

// Reload reference data into the edit form
async function reloadRefDataIntoEdit() {
    if (!modalReceiptData || !modalReceiptData.reference_data) return;

    const refData = modalReceiptData.reference_data;

    // Close the reference info modal
    $('#referenceInfoModal').modal('hide');

    // Open edit modal with data pre-filled from reference
    openEditReceiptNumber();

    applyRefDataToEditForm(refData);

    App.success('โหลดข้อมูลจากอ้างอิงเข้าฟอร์มแก้ไขแล้ว');
}

// Reload reference data from API directly into edit form
async function reloadRefDataIntoEditFromAPI() {
    if (!modalReceiptData) return;
    const refType = modalReceiptData.receipt_type;
    const refId   = modalReceiptData.reference_id;
    if (!refType || !refId) { App.error('ไม่พบข้อมูลอ้างอิง'); return; }

    const result = await API.getReceiptReferenceData(refType, refId);
    if (!result.success || !result.data) {
        App.error(result.message || 'ไม่พบข้อมูลอ้างอิง');
        return;
    }

    applyRefDataToEditForm(result.data);
    App.success('โหลดข้อมูลจากระบบเข้าฟอร์มแก้ไขแล้ว');
}

// Apply reference data to the edit form fields
function applyRefDataToEditForm(refData) {
    // Overwrite fields with reference data
    if (refData.full_name) {
        $('#editPayerName').val(refData.full_name);
    }

    // Fill description from reference
    if (refData.source_type === 'membership_fee') {
        const feeLabel = refData.fee_type === 'onetime' ? 'ครั้งเดียว' : 'ปี ' + refData.fee_year;
        const typeLabel = refData.member_type_label || '';
        const title = 'ค่าธรรมเนียมสมาชิก' + (typeLabel ? typeLabel : '');
        $('#editDescription').val(title + ' (' + feeLabel + ')');
    } else if (refData.source_type === 'activity_fee') {
        const desc = refData.activity_fee_description
            ? `ค่าลงทะเบียนเข้าร่วม "${refData.activity_title}" (${refData.activity_fee_description})`
            : `ค่าลงทะเบียนเข้าร่วม "${refData.activity_title}"`;
        $('#editDescription').val(desc);
    }

    // Build address from reference
    const fakeMember = {
        full_name: refData.full_name,
        work_address: refData.work_address || '',
        home_address: refData.home_address || '',
        school_organization: refData.school_organization || ''
    };
    const addrJson = buildPayerAddress(fakeMember);
    if (addrJson) {
        try {
            const addr = JSON.parse(addrJson);
            let detail = addr.detail || '';
            let no = '', moo = '', soi = '', road = '';

            const roadMatch = detail.match(/\s+ถนน\s*(.+?)$/);
            if (roadMatch) { road = roadMatch[1].trim(); detail = detail.replace(roadMatch[0], ''); }
            const soiMatch = detail.match(/\s+ซอย\s*(.+?)$/);
            if (soiMatch) { soi = soiMatch[1].trim(); detail = detail.replace(soiMatch[0], ''); }
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
        } catch(e) {}
    }
}

</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
