<?php $pageTitle = 'บริหารการเงินสมาคม'; ?>
<?php include ROOT_PATH . 'templates/member/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-wallet2 me-2"></i>บริหารการเงินสมาคม</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>member/">หน้าหลัก</a></li>
                        <li class="breadcrumb-item active">การเงิน</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container">

            <!-- Permission Check -->
            <div id="noPermission" class="text-center py-5" style="display:none;">
                <i class="bi bi-shield-lock" style="font-size:4rem;color:#ccc;"></i>
                <h4 class="mt-3 text-muted">คุณไม่มีสิทธิ์เข้าถึงส่วนนี้</h4>
                <p class="text-muted">เฉพาะผู้ที่ได้รับมอบสิทธิ์เป็นผู้จัดการการเงินเท่านั้น</p>
                <a href="<?php echo $basePath; ?>member/" class="btn btn-primary mt-2">
                    <i class="bi bi-house me-1"></i>กลับหน้าหลัก
                </a>
            </div>

            <!-- Main Content (shown only if has permission) -->
            <div id="financeContent" style="display:none;">

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-3" id="mFinanceTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="m-tab-overview" data-toggle="tab" href="#m-pane-overview" role="tab">
                            <i class="bi bi-graph-up me-1"></i>ภาพรวม
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="m-tab-transactions" data-toggle="tab" href="#m-pane-transactions" role="tab">
                            <i class="bi bi-list-ul me-1"></i>รายการธุรกรรม
                        </a>
                    </li>
                    <li class="nav-item" id="m-tab-categories-li">
                        <a class="nav-link" id="m-tab-categories" data-toggle="tab" href="#m-pane-categories" role="tab">
                            <i class="bi bi-tags me-1"></i>หมวดหมู่
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- TAB 1: OVERVIEW -->
                    <div class="tab-pane fade show active" id="m-pane-overview" role="tabpanel">
                        <!-- Summary Cards -->
                        <div class="row">
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3 id="mTotalIncome">0</h3>
                                        <p>รายรับทั้งหมด (บาท)</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-arrow-down-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3 id="mTotalExpense">0</h3>
                                        <p>รายจ่ายทั้งหมด (บาท)</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-arrow-up-circle"></i></div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-md-6 col-12">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3 id="mTotalBalance">0</h3>
                                        <p>คงเหลือ (บาท)</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-piggy-bank"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- Overview Filters -->
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label class="form-label mb-0 small text-muted">ปี ค.ศ.</label>
                                        <select class="form-control form-control-sm" id="mOverviewYear"></select>
                                    </div>
                                    <div class="col-md-3 text-md-right" id="mExportBtnWrap">
                                        <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                        <div class="btn-group perm-export">
                                            <button class="btn btn-outline-success btn-sm" onclick="mExport('csv')">
                                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                                            </button>
                                            <button class="btn btn-outline-danger btn-sm" onclick="mExport('pdf')">
                                                <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Summary by Category -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-success text-white">
                                        <h3 class="card-title"><i class="bi bi-arrow-down-circle me-1"></i>สรุปรายรับตามหมวดหมู่</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr><th>หมวดหมู่</th><th class="text-right">จำนวน</th><th class="text-right">ยอดรวม (บาท)</th></tr>
                                            </thead>
                                            <tbody id="mIncomeByCategoryBody">
                                                <tr><td colspan="3" class="text-center text-muted py-3">กำลังโหลด...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-danger text-white">
                                        <h3 class="card-title"><i class="bi bi-arrow-up-circle me-1"></i>สรุปรายจ่ายตามหมวดหมู่</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr><th>หมวดหมู่</th><th class="text-right">จำนวน</th><th class="text-right">ยอดรวม (บาท)</th></tr>
                                            </thead>
                                            <tbody id="mExpenseByCategoryBody">
                                                <tr><td colspan="3" class="text-center text-muted py-3">กำลังโหลด...</td></tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Monthly Summary -->
                        <div class="card shadow-sm">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <h3 class="card-title mb-0"><i class="bi bi-calendar3 me-1"></i>สรุปรายเดือน</h3>
                                <div class="d-flex align-items-center" style="gap:.5rem">
                                    <select class="form-control form-control-sm" id="mMonthlyFilter" style="width:auto;min-width:140px" onchange="mRenderMonthlySummary(window._mMonthlyData)">
                                        <option value="">ทั้งปี (ทุกเดือน)</option>
                                        <option value="1">มกราคม</option>
                                        <option value="2">กุมภาพันธ์</option>
                                        <option value="3">มีนาคม</option>
                                        <option value="4">เมษายน</option>
                                        <option value="5">พฤษภาคม</option>
                                        <option value="6">มิถุนายน</option>
                                        <option value="7">กรกฎาคม</option>
                                        <option value="8">สิงหาคม</option>
                                        <option value="9">กันยายน</option>
                                        <option value="10">ตุลาคม</option>
                                        <option value="11">พฤศจิกายน</option>
                                        <option value="12">ธันวาคม</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>เดือน</th>
                                                <th class="text-right text-success">รายรับ (บาท)</th>
                                                <th class="text-right text-danger">รายจ่าย (บาท)</th>
                                                <th class="text-right text-info">คงเหลือ (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mMonthlyBody">
                                            <tr><td colspan="4" class="text-center text-muted py-3">กำลังโหลด...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: TRANSACTIONS -->
                    <div class="tab-pane fade" id="m-pane-transactions" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <select class="form-control form-control-sm" id="mTxnType">
                                            <option value="">ทั้งหมด</option>
                                            <option value="income">รายรับ</option>
                                            <option value="expense">รายจ่าย</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <select class="form-control form-control-sm" id="mTxnCategory">
                                            <option value="">ทุกหมวดหมู่</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <input type="date" class="form-control form-control-sm" id="mTxnDateFrom">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <input type="date" class="form-control form-control-sm" id="mTxnDateTo">
                                    </div>
                                    <div class="col-md-2 mb-2 mb-md-0">
                                        <input type="text" class="form-control form-control-sm" id="mTxnSearch" placeholder="ค้นหา...">
                                    </div>
                                    <div class="col-md-2 text-md-right">
                                        <button class="btn btn-primary btn-sm perm-create" id="btnAddTxn" onclick="mShowCreateTxn()">
                                            <i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ
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
                                                <th width="9%">วันที่</th>
                                                <th width="7%">ประเภท</th>
                                                <th width="12%">หมวดหมู่</th>
                                                <th width="20%">รายการ</th>
                                                <th width="10%" class="text-right">จำนวน (บาท)</th>
                                                <th width="10%">เลขอ้างอิง</th>
                                                <th width="10%">บันทึกโดย</th>
                                                <th width="8%">เอกสาร</th>
                                                <th width="10%">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mTxnBody">
                                            <tr><td colspan="10" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer clearfix">
                                <div class="float-left">
                                    <div class="btn-group perm-export">
                                        <button class="btn btn-outline-success btn-sm" onclick="mExportTxn('csv')">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="mExportTxn('pdf')">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                        </button>
                                    </div>
                                </div>
                                <div id="mTxnPagination"></div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: CATEGORIES -->
                    <div class="tab-pane fade" id="m-pane-categories" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col-md-3 mb-2 mb-md-0">
                                        <label class="form-label mb-0 small text-muted">กรองตามประเภท</label>
                                        <select class="form-control form-control-sm" id="mCatFilterType">
                                            <option value="">ทั้งหมด</option>
                                            <option value="income">รายรับ</option>
                                            <option value="expense">รายจ่าย</option>
                                        </select>
                                    </div>
                                    <div class="col-md-9 text-md-right">
                                        <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                        <button class="btn btn-primary btn-sm perm-create" id="btnAddCategory" onclick="mShowCreateCategoryModal()">
                                            <i class="bi bi-plus-circle me-1"></i>เพิ่มหมวดหมู่
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
                                                <th width="25%">ชื่อหมวดหมู่</th>
                                                <th width="10%">ประเภท</th>
                                                <th width="30%">คำอธิบาย</th>
                                                <th width="10%">สถานะ</th>
                                                <th width="8%">ลำดับ</th>
                                                <th width="12%">จัดการ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="mCatTableBody">
                                            <tr><td colspan="7" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<!-- Create/Edit Transaction Modal -->
<div class="modal fade" id="mTxnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mTxnModalTitle"><i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mTxnId">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ประเภท <span class="text-danger">*</span></label>
                            <select class="form-control" id="mTxnTypeInput" onchange="mLoadCategoryDropdown()">
                                <option value="income">รายรับ</option>
                                <option value="expense">รายจ่าย</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>หมวดหมู่ <span class="text-danger">*</span></label>
                            <select class="form-control" id="mTxnCategoryInput">
                                <option value="">-- เลือกหมวดหมู่ --</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>รายการ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="mTxnTitle" placeholder="ชื่อรายการ">
                </div>
                <div class="form-group">
                    <label>รายละเอียด</label>
                    <textarea class="form-control" id="mTxnDesc" rows="3" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="mTxnAmount" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>วันที่ทำรายการ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="mTxnDate">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>เลขอ้างอิง</label>
                            <input type="text" class="form-control" id="mTxnRefNo" placeholder="หมายเลขเอกสาร/อ้างอิง">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>เอกสารแนบ</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="mTxnFile" accept="image/*,.pdf">
                        <label class="custom-file-label" for="mTxnFile">เลือกไฟล์...</label>
                    </div>
                    <input type="hidden" id="mTxnAttachment">
                    <div id="mTxnAttachPreview" class="mt-2" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-height:80px" alt="">
                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="mRemoveAttachment()">
                            <i class="bi bi-x"></i> ลบ
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>หมายเหตุ</label>
                    <textarea class="form-control" id="mTxnNote" rows="2" placeholder="หมายเหตุ (ถ้ามี)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="mBtnSaveTxn" onclick="mSaveTxn()">
                    <i class="bi bi-check-lg me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Category Modal -->
<div class="modal fade" id="mCatModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mCatModalTitle"><i class="bi bi-plus-circle me-1"></i>เพิ่มหมวดหมู่</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mCatId">
                <div class="form-group">
                    <label>ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="mCatName" placeholder="ชื่อหมวดหมู่">
                </div>
                <div class="form-group">
                    <label>ประเภท <span class="text-danger">*</span></label>
                    <select class="form-control" id="mCatType">
                        <option value="income">รายรับ</option>
                        <option value="expense">รายจ่าย</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>คำอธิบาย</label>
                    <textarea class="form-control" id="mCatDescription" rows="2" placeholder="คำอธิบาย"></textarea>
                </div>
                <div class="form-group">
                    <label>ลำดับการแสดง</label>
                    <input type="number" class="form-control" id="mCatSortOrder" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="mBtnSaveCat" onclick="mSaveCategory()">
                    <i class="bi bi-check-lg me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="mDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-text me-1"></i>รายละเอียดรายการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="mDetailBody"></div>
        </div>
    </div>
</div>

<!-- Attachment Preview Modal -->
<div class="modal fade" id="mAttachModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เอกสารแนบ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="mAttachImg" src="" class="img-fluid rounded" style="max-height:500px" alt="">
            </div>
        </div>
    </div>
</div>

<!-- Receipt Preview Modal -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1">
    <div class="modal-dialog" style="max-width:95vw;width:1200px;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>ใบเสร็จรับเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="receiptPreviewBody" style="background:#e9ecef;overflow:hidden;">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" onclick="mDownloadReceiptPDF()"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                <button type="button" class="btn btn-success btn-sm" onclick="mDownloadReceiptPNG()"><i class="bi bi-image me-1"></i>PNG</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/member/scripts.php'; ?>

<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Hidden PDF render area -->
<div id="pdfRenderArea" style="position:absolute;left:-9999px;top:0;width:800px;background:#fff;padding:30px;font-family:'Sarabun',sans-serif;color:#333;"></div>

<script>
var MONTHS_TH = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
var myPerms = [];
var mAllCategories = [];

$(function () { checkPermissions(); });

async function checkPermissions() {
    var result = await API.getMyFinancePermissions();
    if (!result.success || (!result.data.is_admin && !result.data.is_finance_manager)) {
        $('#noPermission').show(); $('#financeContent').hide(); return;
    }
    myPerms = result.data.permissions || [];
    $('#financeContent').show(); $('#noPermission').hide();
    if (!myPerms.includes('create')) $('.perm-create').hide();
    if (!myPerms.includes('export')) { $('.perm-export').hide(); $('#mExportBtnWrap').hide(); }
    var hasCatPerm = myPerms.includes('create') || myPerms.includes('edit') || myPerms.includes('delete');
    if (!hasCatPerm) $('#m-tab-categories-li').hide();

    var currentYear = new Date().getFullYear();
    var yearOpts = '';
    for (var y = currentYear + 1; y >= currentYear - 5; y--) {
        yearOpts += '<option value="' + y + '"' + (y === currentYear ? ' selected' : '') + '>' + y + '</option>';
    }
    $('#mOverviewYear').html(yearOpts);
    $('#mTxnDate').val(new Date().toISOString().slice(0, 10));

    mLoadOverview(); mLoadCategoriesList();

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var target = $(e.target).attr('href');
        if (target === '#m-pane-transactions') mLoadTransactions(1);
        if (target === '#m-pane-categories') mLoadCategoriesList();
        if (target === '#m-pane-overview') mLoadOverview();
    });
    $('#mTxnType, #mTxnCategory, #mTxnDateFrom, #mTxnDateTo').on('change', function() { mLoadTransactions(1); });
    var timer;
    $('#mTxnSearch').on('input', function () { clearTimeout(timer); timer = setTimeout(function() { mLoadTransactions(1); }, 400); });
    $('#mCatFilterType').on('change', function() { mLoadCategoriesList(); });
    $('#mOverviewYear').on('change', function() { mLoadOverview(); });

    $('#mTxnFile').on('change', async function () {
        var file = this.files[0]; if (!file) return;
        $(this).next('.custom-file-label').text(file.name);
        try {
            var res = await API.upload(file, 'finance');
            if (res.success) {
                $('#mTxnAttachment').val(res.data.url);
                if (file.type.startsWith('image/')) {
                    var src = res.data.url.startsWith('http') ? res.data.url : BASE_PATH + res.data.url;
                    $('#mTxnAttachPreview').show().find('img').attr('src', src);
                }
            } else { App.error(res.message); }
        } catch (e) { App.error('อัพโหลดผิดพลาด'); }
    });
}

async function mLoadOverview() {
    var year = $('#mOverviewYear').val();
    var summaryRes = await API.getFinanceSummary({ year: year });
    if (summaryRes.success) {
        var s = summaryRes.data.summary;
        $('#mTotalIncome').text(fmtMoney(s.total_income));
        $('#mTotalExpense').text(fmtMoney(s.total_expense));
        $('#mTotalBalance').text(fmtMoney(s.balance));
        mRenderCategorySummary(summaryRes.data.by_category || []);
    }
    var monthlyRes = await API.getFinanceMonthlySummary(year);
    if (monthlyRes.success) { window._mMonthlyData = monthlyRes.data; mRenderMonthlySummary(monthlyRes.data); }
}

function mRenderCategorySummary(data) {
    var incomeRows = data.filter(function(c) { return c.category_type === 'income'; });
    var expenseRows = data.filter(function(c) { return c.category_type === 'expense'; });
    var incomeHtml = '', incomeTotalAmount = 0;
    if (incomeRows.length === 0) { incomeHtml = '<tr><td colspan="3" class="text-center text-muted py-2">ไม่มีข้อมูล</td></tr>'; }
    else {
        incomeRows.forEach(function(r) {
            incomeTotalAmount += parseFloat(r.total_amount);
            incomeHtml += '<tr><td>' + r.category_name + '</td><td class="text-right">' + r.transaction_count + '</td><td class="text-right text-success font-weight-bold">' + fmtMoney(r.total_amount) + '</td></tr>';
        });
        incomeHtml += '<tr class="font-weight-bold bg-light"><td>รวม</td><td></td><td class="text-right text-success">' + fmtMoney(incomeTotalAmount) + '</td></tr>';
    }
    $('#mIncomeByCategoryBody').html(incomeHtml);

    var expenseHtml = '', expenseTotalAmount = 0;
    if (expenseRows.length === 0) { expenseHtml = '<tr><td colspan="3" class="text-center text-muted py-2">ไม่มีข้อมูล</td></tr>'; }
    else {
        expenseRows.forEach(function(r) {
            expenseTotalAmount += parseFloat(r.total_amount);
            expenseHtml += '<tr><td>' + r.category_name + '</td><td class="text-right">' + r.transaction_count + '</td><td class="text-right text-danger font-weight-bold">' + fmtMoney(r.total_amount) + '</td></tr>';
        });
        expenseHtml += '<tr class="font-weight-bold bg-light"><td>รวม</td><td></td><td class="text-right text-danger">' + fmtMoney(expenseTotalAmount) + '</td></tr>';
    }
    $('#mExpenseByCategoryBody').html(expenseHtml);
}

function mRenderMonthlySummary(data) {
    if (!data || data.length === 0) { $('#mMonthlyBody').html('<tr><td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>'); return; }
    var filterMonth = $('#mMonthlyFilter').val();
    var filtered = data;
    if (filterMonth) {
        filtered = data.filter(function(m) {
            var monthNum = parseInt(m.month.split('-')[1]);
            return monthNum === parseInt(filterMonth);
        });
    }
    if (filtered.length === 0) { $('#mMonthlyBody').html('<tr><td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูลในเดือนที่เลือก</td></tr>'); return; }
    var html = '', totI = 0, totE = 0;
    filtered.forEach(function(m) {
        var parts = m.month.split('-');
        var mn = MONTHS_TH[parseInt(parts[1])] + ' ' + parts[0];
        totI += m.income; totE += m.expense;
        var balClass = m.balance >= 0 ? 'text-info' : 'text-danger';
        html += '<tr><td>' + mn + '</td><td class="text-right text-success">' + fmtMoney(m.income) + '</td><td class="text-right text-danger">' + fmtMoney(m.expense) + '</td><td class="text-right ' + balClass + ' font-weight-bold">' + fmtMoney(m.balance) + '</td></tr>';
    });
    var b = totI - totE;
    var summaryLabel = filterMonth ? 'รวมเดือนที่เลือก' : 'รวมทั้งปี';
    html += '<tr class="font-weight-bold bg-light"><td>' + summaryLabel + '</td><td class="text-right text-success">' + fmtMoney(totI) + '</td><td class="text-right text-danger">' + fmtMoney(totE) + '</td><td class="text-right ' + (b >= 0 ? 'text-info' : 'text-danger') + '">' + fmtMoney(b) + '</td></tr>';
    $('#mMonthlyBody').html(html);
}

async function mLoadTransactions(page) {
    page = page || 1;
    var params = { page: page, per_page: 20 };
    var v;
    if (v = $('#mTxnType').val()) params.type = v;
    if (v = $('#mTxnCategory').val()) params.category_id = v;
    if (v = $('#mTxnDateFrom').val()) params.date_from = v;
    if (v = $('#mTxnDateTo').val()) params.date_to = v;
    v = $('#mTxnSearch').val().trim(); if (v) params.search = v;

    var res = await API.getFinanceTransactions(params);
    var tbody = $('#mTxnBody');
    if (!res.success) { tbody.html('<tr><td colspan="10" class="text-center text-danger py-3">' + res.message + '</td></tr>'); return; }
    if (!res.data || res.data.length === 0) { tbody.html('<tr><td colspan="10" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>'); $('#mTxnPagination').empty(); return; }

    var startNum = ((res.pagination && res.pagination.current_page || 1) - 1) * (res.pagination && res.pagination.per_page || 20);
    var html = '';
    res.data.forEach(function(t, i) {
        var typeBadge = t.type === 'income' ? '<span class="badge badge-success">รายรับ</span>' : '<span class="badge badge-danger">รายจ่าย</span>';
        var amtClass = t.type === 'income' ? 'text-success' : 'text-danger';
        var prefix = t.type === 'income' ? '+' : '-';
        var attach = t.attachment ? '<a href="#" onclick="mPreviewAttach(\'' + t.attachment + '\');return false;" class="btn btn-xs btn-outline-info"><i class="bi bi-paperclip"></i></a>' : '<span class="text-muted">-</span>';
        var actions = '';
        if (t.reference_no && /^(FEE-|ACT-REG-)/.test(t.reference_no)) actions += '<button class="btn btn-xs btn-outline-success mr-1" onclick="mViewTxnReceipt(\'' + App.escHtml(t.reference_no) + '\')" title="ใบเสร็จ"><i class="bi bi-receipt"></i></button>';
        actions += '<button class="btn btn-xs btn-outline-info mr-1" onclick="mShowDetail(' + t.id + ')" title="ดู"><i class="bi bi-eye"></i></button>';
        if (myPerms.includes('edit')) actions += '<button class="btn btn-xs btn-outline-primary mr-1" onclick="mEditTxn(' + t.id + ')" title="แก้ไข"><i class="bi bi-pencil"></i></button>';
        if (myPerms.includes('delete')) actions += '<button class="btn btn-xs btn-outline-danger" onclick="mDeleteTxn(' + t.id + ',\'' + App.escHtml(t.title) + '\')" title="ลบ"><i class="bi bi-trash"></i></button>';
        var descSnippet = t.description ? '<br><small class="text-muted">' + App.escHtml(t.description).substring(0, 50) + '</small>' : '';
        html += '<tr><td>' + (startNum + i + 1) + '</td><td><small>' + App.formatDate(t.transaction_date) + '</small></td><td>' + typeBadge + '</td><td><small>' + App.escHtml(t.category_name || '-') + '</small></td><td><a href="#" onclick="mShowDetail(' + t.id + ');return false;" class="text-primary"><strong>' + App.escHtml(t.title) + '</strong></a>' + descSnippet + '</td><td class="text-right ' + amtClass + ' font-weight-bold">' + prefix + fmtMoney(t.amount) + '</td><td><small>' + App.escHtml(t.reference_no || '-') + '</small></td><td><small>' + App.escHtml(t.creator_name || '-') + '</small></td><td class="text-center">' + attach + '</td><td>' + actions + '</td></tr>';
    });
    tbody.html(html);
    if (res.pagination) App.buildPagination('#mTxnPagination', res.pagination, mLoadTransactions);
}

function mShowCreateTxn() {
    $('#mTxnModalTitle').html('<i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ');
    $('#mTxnId, #mTxnTitle, #mTxnDesc, #mTxnAmount, #mTxnRefNo, #mTxnAttachment, #mTxnNote').val('');
    $('#mTxnTypeInput').val('income');
    $('#mTxnDate').val(new Date().toISOString().slice(0, 10));
    $('#mTxnAttachPreview').hide();
    $('#mTxnFile').val('').next('.custom-file-label').text('เลือกไฟล์...');
    mLoadCategoryDropdown();
    $('#mTxnModal').modal('show');
}

async function mEditTxn(id) {
    var res = await API.getFinanceDetail(id);
    if (!res.success) { App.error(res.message); return; }
    var t = res.data;
    $('#mTxnModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขรายการ');
    $('#mTxnId').val(t.id); $('#mTxnTypeInput').val(t.type);
    await mLoadCategoryDropdown();
    $('#mTxnCategoryInput').val(t.category_id);
    $('#mTxnTitle').val(t.title); $('#mTxnDesc').val(t.description || '');
    $('#mTxnAmount').val(t.amount); $('#mTxnDate').val(t.transaction_date);
    $('#mTxnRefNo').val(t.reference_no || ''); $('#mTxnAttachment').val(t.attachment || '');
    $('#mTxnNote').val(t.note || '');
    if (t.attachment) { var src = t.attachment.startsWith('http') ? t.attachment : BASE_PATH + t.attachment; $('#mTxnAttachPreview').show().find('img').attr('src', src); }
    else { $('#mTxnAttachPreview').hide(); }
    $('#mTxnModal').modal('show');
}

async function mLoadCategoryDropdown() {
    var type = $('#mTxnTypeInput').val();
    var res = await API.getFinanceActiveCategories(type);
    var opts = '<option value="">-- เลือกหมวดหมู่ --</option>';
    if (res.success && res.data) res.data.forEach(function(c) { opts += '<option value="' + c.id + '">' + c.name + '</option>'; });
    $('#mTxnCategoryInput').html(opts);
}

async function mSaveTxn() {
    var id = $('#mTxnId').val();
    var data = { type: $('#mTxnTypeInput').val(), category_id: $('#mTxnCategoryInput').val(), title: $('#mTxnTitle').val().trim(), description: $('#mTxnDesc').val().trim(), amount: parseFloat($('#mTxnAmount').val()) || 0, transaction_date: $('#mTxnDate').val(), reference_no: $('#mTxnRefNo').val().trim(), attachment: $('#mTxnAttachment').val(), note: $('#mTxnNote').val().trim() };
    if (!data.title) { App.error('กรุณากรอกรายการ'); return; }
    if (!data.category_id) { App.error('กรุณาเลือกหมวดหมู่'); return; }
    if (data.amount <= 0) { App.error('กรุณากรอกจำนวนเงินที่ถูกต้อง'); return; }
    if (!data.transaction_date) { App.error('กรุณาเลือกวันที่'); return; }
    var btn = $('#mBtnSaveTxn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');
    var res;
    if (id) { data.id = parseInt(id); res = await API.updateFinanceTransaction(data); }
    else { res = await API.createFinanceTransaction(data); }
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>บันทึก');
    if (res.success) { App.success(res.message); $('#mTxnModal').modal('hide'); mLoadTransactions(1); mLoadOverview(); }
    else { App.error(res.message); }
}

async function mDeleteTxn(id, title) {
    var c = await Swal.fire({ title: 'ยืนยันการลบ', html: 'ต้องการลบรายการ <strong>' + title + '</strong> ใช่หรือไม่?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก' });
    if (!c.isConfirmed) return;
    var res = await API.deleteFinanceTransaction(id);
    if (res.success) { App.success(res.message); mLoadTransactions(1); mLoadOverview(); }
    else App.error(res.message);
}

async function mShowDetail(id) {
    var res = await API.getFinanceDetail(id);
    if (!res.success) { App.error(res.message); return; }
    var t = res.data;
    var typeBadge = t.type === 'income' ? '<span class="badge badge-success badge-lg">รายรับ</span>' : '<span class="badge badge-danger badge-lg">รายจ่าย</span>';
    var attachImg = t.attachment ? '<div class="mt-3"><strong>เอกสารแนบ:</strong><br><img src="' + (t.attachment.startsWith('http') ? t.attachment : BASE_PATH + t.attachment) + '" class="img-fluid rounded mt-1" style="max-height:300px"></div>' : '';
    $('#mDetailBody').html('<div class="row"><div class="col-md-6"><table class="table table-borderless table-sm"><tr><th width="35%">ประเภท</th><td>' + typeBadge + '</td></tr><tr><th>หมวดหมู่</th><td>' + App.escHtml(t.category_name || '-') + '</td></tr><tr><th>รายการ</th><td><strong>' + App.escHtml(t.title) + '</strong></td></tr><tr><th>จำนวนเงิน</th><td class="font-weight-bold ' + (t.type === 'income' ? 'text-success' : 'text-danger') + '" style="font-size:1.2em">' + fmtMoney(t.amount) + ' บาท</td></tr></table></div><div class="col-md-6"><table class="table table-borderless table-sm"><tr><th width="35%">วันที่ทำรายการ</th><td>' + App.formatDate(t.transaction_date) + '</td></tr><tr><th>เลขอ้างอิง</th><td>' + App.escHtml(t.reference_no || '-') + '</td></tr><tr><th>บันทึกโดย</th><td>' + App.escHtml(t.creator_name || '-') + '</td></tr><tr><th>วันที่บันทึก</th><td>' + App.formatDate(t.created_at) + '</td></tr></table></div></div>' + (t.description ? '<div class="mt-2"><strong>รายละเอียด:</strong><p class="text-muted">' + App.escHtml(t.description) + '</p></div>' : '') + (t.note ? '<div class="mt-2"><strong>หมายเหตุ:</strong><p class="text-muted">' + App.escHtml(t.note) + '</p></div>' : '') + attachImg);
    $('#mDetailModal').modal('show');
}

function mRemoveAttachment() { $('#mTxnAttachment').val(''); $('#mTxnAttachPreview').hide(); $('#mTxnFile').val('').next('.custom-file-label').text('เลือกไฟล์...'); }
function mPreviewAttach(url) { var src = (url.startsWith('http') || url.startsWith('//')) ? url : BASE_PATH + url; $('#mAttachImg').attr('src', src); $('#mAttachModal').modal('show'); }

async function mLoadCategoriesList() {
    var params = {};
    var type = $('#mCatFilterType').val(); if (type) params.type = type;
    var res = await API.getFinanceCategories(params);
    var tbody = $('#mCatTableBody');
    if (!res.success) { tbody.html('<tr><td colspan="7" class="text-center text-danger py-3">' + res.message + '</td></tr>'); return; }
    mAllCategories = res.data || [];
    mPopulateCategoryFilter();
    if (mAllCategories.length === 0) { tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">ไม่พบหมวดหมู่</td></tr>'); return; }
    var html = '';
    mAllCategories.forEach(function(cat, i) {
        var typeBadge = cat.type === 'income' ? '<span class="badge badge-success">รายรับ</span>' : '<span class="badge badge-danger">รายจ่าย</span>';
        var statusBadge = cat.is_active == 1 ? '<span class="badge badge-success">ใช้งาน</span>' : '<span class="badge badge-secondary">ปิดใช้งาน</span>';
        var actions = '';
        if (myPerms.includes('edit')) actions += '<button class="btn btn-xs btn-outline-primary mr-1" onclick="mEditCategory(' + cat.id + ')" title="แก้ไข"><i class="bi bi-pencil"></i></button>';
        if (myPerms.includes('delete')) actions += '<button class="btn btn-xs btn-outline-danger" onclick="mDeleteCategory(' + cat.id + ', \'' + App.escHtml(cat.name) + '\')" title="ลบ"><i class="bi bi-trash"></i></button>';
        if (!actions) actions = '<span class="text-muted">-</span>';
        html += '<tr><td>' + (i + 1) + '</td><td><strong>' + App.escHtml(cat.name) + '</strong></td><td>' + typeBadge + '</td><td><small class="text-muted">' + App.escHtml(cat.description || '-') + '</small></td><td>' + statusBadge + '</td><td>' + cat.sort_order + '</td><td>' + actions + '</td></tr>';
    });
    tbody.html(html);
}

function mPopulateCategoryFilter() {
    var opts = '<option value="">ทุกหมวดหมู่</option>';
    mAllCategories.forEach(function(c) { if (c.is_active == 1) { var pre = c.type === 'income' ? '📥 ' : '📤 '; opts += '<option value="' + c.id + '">' + pre + c.name + '</option>'; } });
    $('#mTxnCategory').html(opts);
}

function mShowCreateCategoryModal() {
    $('#mCatModalTitle').html('<i class="bi bi-plus-circle me-1"></i>เพิ่มหมวดหมู่');
    $('#mCatId, #mCatName, #mCatDescription').val('');
    $('#mCatType').val('income'); $('#mCatSortOrder').val(0);
    $('#mCatModal').modal('show');
}

function mEditCategory(id) {
    var cat = mAllCategories.find(function(c) { return c.id == id; });
    if (!cat) return;
    $('#mCatModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขหมวดหมู่');
    $('#mCatId').val(cat.id); $('#mCatName').val(cat.name); $('#mCatType').val(cat.type);
    $('#mCatDescription').val(cat.description || ''); $('#mCatSortOrder').val(cat.sort_order || 0);
    $('#mCatModal').modal('show');
}

async function mSaveCategory() {
    var id = $('#mCatId').val();
    var data = { name: $('#mCatName').val().trim(), type: $('#mCatType').val(), description: $('#mCatDescription').val().trim(), sort_order: parseInt($('#mCatSortOrder').val()) || 0 };
    if (!data.name) { App.error('กรุณากรอกชื่อหมวดหมู่'); return; }
    var btn = $('#mBtnSaveCat');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');
    var res;
    if (id) { data.id = parseInt(id); res = await API.updateFinanceCategory(data); }
    else { res = await API.createFinanceCategory(data); }
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>บันทึก');
    if (res.success) { App.success(res.message); $('#mCatModal').modal('hide'); mLoadCategoriesList(); }
    else { App.error(res.message); }
}

async function mDeleteCategory(id, name) {
    var c = await Swal.fire({ title: 'ยืนยันการลบ', html: 'ต้องการลบหมวดหมู่ <strong>' + name + '</strong> ใช่หรือไม่?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d', confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก' });
    if (!c.isConfirmed) return;
    var res = await API.deleteFinanceCategory(id);
    if (res.success) { App.success(res.message); mLoadCategoriesList(); }
    else App.error(res.message);
}

async function mExport(format) {
    var year = $('#mOverviewYear').val() || new Date().getFullYear();
    var res = await API.exportFinance({ year: year });
    if (!res.success) { App.error(res.message); return; }
    if (format === 'pdf') {
        await mGeneratePDF(res.data.transactions, res.data.summary, 'ปี ' + year, res.data.exported_at, res.data.exported_by, 'รายงานการเงิน_' + year);
    } else {
        mGenerateCSV(res.data.transactions, res.data.summary, 'ปี ' + year, res.data.exported_at, res.data.exported_by, 'รายงานการเงิน_' + year + '.csv');
    }
}

async function mExportTxn(format) {
    var params = {};
    if ($('#mTxnType').val()) params.type = $('#mTxnType').val();
    if ($('#mTxnCategory').val()) params.category_id = $('#mTxnCategory').val();
    if ($('#mTxnDateFrom').val()) params.date_from = $('#mTxnDateFrom').val();
    if ($('#mTxnDateTo').val()) params.date_to = $('#mTxnDateTo').val();
    if (!params.date_from && !params.date_to) params.year = $('#mOverviewYear').val() || new Date().getFullYear();
    var res = await API.exportFinance(params);
    if (!res.success) { App.error(res.message); return; }
    var dateStr = new Date().toISOString().slice(0, 10);
    if (format === 'pdf') {
        await mGeneratePDF(res.data.transactions, res.data.summary, 'รายการธุรกรรม', res.data.exported_at, res.data.exported_by, 'รายการธุรกรรม_' + dateStr);
    } else {
        mGenerateCSV(res.data.transactions, res.data.summary, 'รายการธุรกรรม', res.data.exported_at, res.data.exported_by, 'รายการธุรกรรม_' + dateStr + '.csv');
    }
}

function mGenerateCSV(transactions, summary, label, exported_at, exported_by, filename) {
    var esc = function(s) { return '"' + (s || '').replace(/"/g, '""') + '"'; };
    var csv = '\uFEFF';
    csv += 'รายงานการเงินสมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์\n';
    csv += label + '\n';
    csv += 'ส่งออกเมื่อ: ' + exported_at + '\n';
    csv += 'ส่งออกโดย: ' + exported_by + '\n\n';
    csv += 'สรุป\n';
    csv += 'รายรับทั้งหมด,' + summary.total_income + '\n';
    csv += 'รายจ่ายทั้งหมด,' + summary.total_expense + '\n';
    csv += 'คงเหลือ,' + summary.balance + '\n\n';
    csv += 'ลำดับ,วันที่,ประเภท,หมวดหมู่,รายการ,รายละเอียด,จำนวนเงิน,เลขอ้างอิง,บันทึกโดย,หมายเหตุ\n';
    transactions.forEach(function(t, i) {
        csv += (i+1) + ',' + t.transaction_date + ',' + (t.type === 'income' ? 'รายรับ' : 'รายจ่าย') + ',' + esc(t.category_name) + ',' + esc(t.title) + ',' + esc(t.description) + ',' + t.amount + ',' + esc(t.reference_no) + ',' + esc(t.creator_name) + ',' + esc(t.note) + '\n';
    });
    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    link.href = URL.createObjectURL(blob); link.download = filename;
    link.click(); URL.revokeObjectURL(link.href);
    App.success('ส่งออก CSV สำเร็จ');
}

async function mGeneratePDF(transactions, summary, label, exported_at, exported_by, filename) {
    var area = document.getElementById('pdfRenderArea');
    var fmtNum = function(v) { return parseFloat(v || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var html = '<div style="text-align:center;margin-bottom:18px;">' +
        '<div style="font-size:20px;font-weight:700;color:#1a3c5e;">รายงานการเงิน</div>' +
        '<div style="font-size:16px;font-weight:600;">สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์</div>' +
        '<div style="font-size:13px;color:#666;">' + label + ' — ส่งออกเมื่อ ' + exported_at + ' โดย ' + exported_by + '</div>' +
        '</div>' +
        '<div style="display:flex;gap:20px;margin-bottom:18px;">' +
            '<div style="flex:1;background:#d4edda;padding:12px;border-radius:6px;text-align:center;"><div style="font-size:12px;color:#155724;">รายรับทั้งหมด</div><div style="font-size:18px;font-weight:700;color:#155724;">+' + fmtNum(summary.total_income) + '</div></div>' +
            '<div style="flex:1;background:#f8d7da;padding:12px;border-radius:6px;text-align:center;"><div style="font-size:12px;color:#721c24;">รายจ่ายทั้งหมด</div><div style="font-size:18px;font-weight:700;color:#721c24;">-' + fmtNum(summary.total_expense) + '</div></div>' +
            '<div style="flex:1;background:#cce5ff;padding:12px;border-radius:6px;text-align:center;"><div style="font-size:12px;color:#004085;">คงเหลือ</div><div style="font-size:18px;font-weight:700;color:#004085;">' + fmtNum(summary.balance) + '</div></div>' +
        '</div>' +
        '<table style="width:100%;border-collapse:collapse;font-size:11px;"><thead><tr style="background:#1a5276;color:#fff;">' +
            '<th style="padding:6px;border:1px solid #ddd;text-align:center;width:30px;">#</th>' +
            '<th style="padding:6px;border:1px solid #ddd;">วันที่</th>' +
            '<th style="padding:6px;border:1px solid #ddd;text-align:center;">ประเภท</th>' +
            '<th style="padding:6px;border:1px solid #ddd;">หมวดหมู่</th>' +
            '<th style="padding:6px;border:1px solid #ddd;">รายการ</th>' +
            '<th style="padding:6px;border:1px solid #ddd;text-align:right;">จำนวน (บาท)</th>' +
            '<th style="padding:6px;border:1px solid #ddd;">เลขอ้างอิง</th>' +
            '<th style="padding:6px;border:1px solid #ddd;">บันทึกโดย</th>' +
        '</tr></thead><tbody>';
    transactions.forEach(function(t, i) {
        var isIncome = t.type === 'income';
        var amtColor = isIncome ? '#155724' : '#dc3545';
        var amtPrefix = isIncome ? '+' : '-';
        html += '<tr style="background:' + (i % 2 === 0 ? '#fff' : '#f8f9fa') + ';">' +
            '<td style="padding:5px;border:1px solid #eee;text-align:center;">' + (i+1) + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;">' + t.transaction_date + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;text-align:center;"><span style="background:' + (isIncome ? '#d4edda' : '#f8d7da') + ';color:' + amtColor + ';padding:2px 6px;border-radius:3px;font-size:10px;">' + (isIncome ? 'รายรับ' : 'รายจ่าย') + '</span></td>' +
            '<td style="padding:5px;border:1px solid #eee;">' + (t.category_name || '-') + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;font-weight:600;">' + (t.title || '-') + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;text-align:right;color:' + amtColor + ';font-weight:700;">' + amtPrefix + fmtNum(t.amount) + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;">' + (t.reference_no || '-') + '</td>' +
            '<td style="padding:5px;border:1px solid #eee;">' + (t.creator_name || '-') + '</td>' +
        '</tr>';
    });
    html += '</tbody></table><div style="margin-top:12px;font-size:10px;color:#999;text-align:center;">จำนวนรายการทั้งหมด ' + transactions.length + ' รายการ</div>';
    area.innerHTML = html;

    try {
        var canvas = await html2canvas(area, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
        var jsPDF = window.jspdf.jsPDF;
        var pdf = new jsPDF('l', 'mm', 'a4');
        var pageW = pdf.internal.pageSize.getWidth() - 20;
        var imgH = (canvas.height * pageW) / canvas.width;
        var pageH = pdf.internal.pageSize.getHeight() - 20;
        var imgData = canvas.toDataURL('image/png');

        if (imgH <= pageH) {
            pdf.addImage(imgData, 'PNG', 10, 10, pageW, imgH);
        } else {
            var totalPages = Math.ceil(imgH / pageH);
            for (var p = 0; p < totalPages; p++) {
                if (p > 0) pdf.addPage();
                var srcY = p * (canvas.height / totalPages);
                var sliceH = canvas.height / totalPages;
                var tmpCanvas = document.createElement('canvas');
                tmpCanvas.width = canvas.width;
                tmpCanvas.height = sliceH;
                tmpCanvas.getContext('2d').drawImage(canvas, 0, srcY, canvas.width, sliceH, 0, 0, canvas.width, sliceH);
                pdf.addImage(tmpCanvas.toDataURL('image/png'), 'PNG', 10, 10, pageW, pageH);
            }
        }
        pdf.save(filename + '.pdf');
        App.success('ส่งออก PDF สำเร็จ');
    } catch (e) {
        console.error(e);
        App.error('ไม่สามารถสร้าง PDF ได้');
    } finally {
        area.innerHTML = '';
    }
}

// === RECEIPT ADDRESS HELPER ===
function mRenderPayerAddress(raw) {
    var detail = '-', sub = '-', dist = '-', prov = '-';
    if (raw) {
        try {
            var a = JSON.parse(raw);
            if (a && typeof a === 'object') {
                detail = a.detail || '-';
                sub = a.subdistrict || '-';
                dist = a.district || '-';
                prov = a.province || '-';
            } else {
                return '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">ที่อยู่</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">' + App.escHtml(raw) + '</span><strong style="white-space:nowrap">ตำบล</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">-</span></div>'
                    + '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">อำเภอ</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">-</span><strong style="white-space:nowrap">จังหวัด</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">-</span></div>';
            }
        } catch(e) {
            return '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">ที่อยู่</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">' + App.escHtml(raw) + '</span><strong style="white-space:nowrap">ตำบล</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">-</span></div>'
                + '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">อำเภอ</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">-</span><strong style="white-space:nowrap">จังหวัด</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">-</span></div>';
        }
    }
    var html = '';
    html += '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">ที่อยู่</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">' + App.escHtml(detail) + '</span>';
    html += '<strong style="white-space:nowrap">ตำบล</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">' + App.escHtml(sub) + '</span></div>';
    html += '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">อำเภอ</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:3;padding:0 2px 0 1.5em;">' + App.escHtml(dist) + '</span>';
    html += '<strong style="white-space:nowrap">จังหวัด</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:2;padding:0 2px 0 1.5em;">' + App.escHtml(prov) + '</span></div>';
    return html;
}

// === RECEIPT PREVIEW ===
var _mReceiptData = null;

async function mViewTxnReceipt(referenceNo) {
    var body = $('#receiptPreviewBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');
    $('#receiptPreviewModal').modal('show');

    var refRes = await API.findReceiptByRef(referenceNo);
    if (!refRes.success || !refRes.data) {
        body.html('<p class="text-center text-danger py-3">ไม่พบใบเสร็จสำหรับรายการนี้</p>');
        return;
    }

    var res = await API.getReceiptDetail(refRes.data.receipt_id);
    if (!res.success || !res.data) {
        body.html('<p class="text-center text-danger py-3">ไม่พบใบเสร็จ</p>');
        return;
    }

    _mReceiptData = res.data;
    var r = _mReceiptData;

    var sigSrc = '';
    if (r.signature_mode === 'electronic' && r.signature_image) {
        if (r.signature_image.startsWith('data:')) { sigSrc = r.signature_image; }
        else { var url = r.signature_image.startsWith('http') ? r.signature_image : (BASE_PATH + r.signature_image); sigSrc = await mToBase64(url).catch(function() { return url; }); }
    }

    var d = new Date(r.issued_date);
    var thM = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    var dateStr = 'วันที่ ' + d.getDate() + ' เดือน ' + thM[d.getMonth()] + ' พ.ศ. ' + (d.getFullYear() + 543);

    body.html('<div id="mReceiptLoading" style="text-align:center;padding:60px 20px;">' +
        '<div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>' +
        '<div class="mt-3 text-muted" style="font-size:16px;">กำลังโหลดใบเสร็จ... <span id="mReceiptPercent">0</span>%</div>' +
    '</div>' +
    '<div id="modalReceiptCanvas" style="width:1123px;height:794px;font-family:\'Sarabun\',sans-serif;color:#1a3c5e;line-height:1.5;background:#fff;box-shadow:0 4px 24px rgba(0,0,0,.15);position:relative;overflow:hidden;transform-origin:top left;visibility:hidden;">' +
        (window._receiptLogoUrl ? '<img src="' + window._receiptLogoUrl + '" alt="" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:450px;height:450px;opacity:0.08;pointer-events:none;z-index:0;">' : '') +
        '<div style="border:2px solid #1a3c5e;border-radius:12px;padding:30px 50px;position:absolute;top:30px;left:30px;right:30px;bottom:30px;display:flex;flex-direction:column;z-index:1;">' +
        '<div style="display:flex;justify-content:space-between;font-size:16px;margin-bottom:6px;"><div>เล่มที่ ' + App.escHtml(r.book_number) + '</div><div>เลขที่ ' + r.receipt_number + '</div></div>' +
        '<div style="text-align:center;margin-bottom:10px;"><div style="font-size:28px;font-weight:700;">ใบเสร็จรับเงิน</div><div style="font-size:20px;font-weight:600;">' + App.escHtml(r.organization_name) + '</div><div style="font-size:16px;">' + App.escHtml(r.organization_address) + '</div></div>' +
        '<div style="text-align:left;font-size:16px;margin-bottom:8px;padding-left:50%;">' + dateStr + '</div>' +
        '<div style="font-size:18px;flex-grow:1;">' +
        '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">ได้รับเงินจาก</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:1;padding:0 2px 0 1.5em;">' + App.escHtml(r.payer_name) + '</span></div>' +
        mRenderPayerAddress(r.payer_address) +
        '<div style="display:flex;align-items:baseline;margin-bottom:8px;"><strong style="white-space:nowrap">เป็น</strong><span style="border-bottom:1px dotted #555;display:inline-block;flex:1;padding:0 2px 0 1.5em;">' + App.escHtml((r.description||'').replace(/\s*จำนวน\s*[\d,.]+\s*บาท/g,'')) + '</span></div>' +
        '<div style="text-align:center;border:1px solid #1a3c5e;border-radius:8px;padding:8px 20px;margin:10px 0;font-size:20px;"><strong>จำนวน ' + App.formatCurrency(r.amount) + '</strong> (' + App.escHtml(r.amount_text) + ') ไว้ถูกต้องแล้ว</div>' +
        '</div>' +
        '<div style="display:flex;justify-content:flex-end;margin-top:2px;font-size:16px;"><div style="text-align:center;">' +
            (r.signature_mode === 'electronic' && sigSrc ? '<div style="margin-bottom:-25px;"><img src="' + sigSrc + '" alt="ลายเซ็น" style="max-height:60px;"></div>' : '<div style="margin-bottom:20px;"></div>') +
            '<div>(ลงชื่อ) ................................... ผู้รับเงิน</div>' +
            (r.signature_show_name === '1' && r.signature_name ? '<div style="margin-top:2px;">(' + App.escHtml(r.signature_name) + ')</div>' : '') +
            (r.signature_show_position === '1' ? '<div style="margin-top:1px;">' + App.escHtml(r.signature_position || 'เหรัญญิก') + '</div>' : '') +
        '</div></div>' +
        '</div>' +
    '</div>');

    // Wait for images then scale
    mScaleModalReceipt();
}

function mToBase64(url) {
    return new Promise(function(resolve, reject) {
        var img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = function() { var c = document.createElement('canvas'); c.width = img.width; c.height = img.height; c.getContext('2d').drawImage(img, 0, 0); resolve(c.toDataURL('image/png')); };
        img.onerror = reject;
        img.src = url;
    });
}

function mScaleModalReceipt() {
    var modalBody = document.getElementById('receiptPreviewBody');
    var receipt = document.getElementById('modalReceiptCanvas');
    var loading = document.getElementById('mReceiptLoading');
    var percentEl = document.getElementById('mReceiptPercent');
    if (!modalBody || !receipt) return;

    var images = receipt.querySelectorAll('img');
    var total = images.length || 1;
    var loaded = 0;

    function done() {
        setTimeout(function() {
            var bodyW = modalBody.clientWidth - 30;
            if (bodyW <= 0) return;
            var scale = Math.min(bodyW / 1123, 1);
            receipt.style.transform = 'scale(' + scale + ')';
            modalBody.style.height = (794 * scale + 20) + 'px';
            receipt.style.visibility = 'visible';
            if (loading) loading.style.display = 'none';
        }, 100);
    }

    function updateProgress() {
        loaded++;
        var pct = Math.round((loaded / total) * 100);
        if (percentEl) percentEl.textContent = pct;
        if (loaded >= total) done();
    }

    if (images.length === 0) {
        if (percentEl) percentEl.textContent = '100';
        done();
        return;
    }

    images.forEach(function(img) {
        if (img.complete && img.naturalWidth > 0) {
            updateProgress();
        } else {
            img.addEventListener('load', updateProgress, { once: true });
            img.addEventListener('error', updateProgress, { once: true });
        }
    });

    setTimeout(function() {
        if (receipt.style.visibility === 'hidden') done();
    }, 5000);
}

async function mDownloadReceiptPDF() {
    var el = document.getElementById('modalReceiptCanvas');
    if (!el) return;
    var origTransform = el.style.transform;
    el.style.transform = 'none';
    var canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    el.style.transform = origTransform;
    var jsPDF = window.jspdf.jsPDF;
    var pdf = new jsPDF('l', 'mm', 'a4');
    var pageW = 297, pageH = 210, margin = 5;
    var w = pageW - margin * 2;
    var h = (canvas.height * w) / canvas.width;
    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', margin, margin, w, Math.min(h, pageH - margin * 2));
    pdf.save('ใบเสร็จ_' + (_mReceiptData ? _mReceiptData.receipt_number : '') + '.pdf');
}

async function mDownloadReceiptPNG() {
    var el = document.getElementById('modalReceiptCanvas');
    if (!el) return;
    var origTransform = el.style.transform;
    el.style.transform = 'none';
    var canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    el.style.transform = origTransform;
    var link = document.createElement('a');
    link.download = 'ใบเสร็จ_' + (_mReceiptData ? _mReceiptData.receipt_number : '') + '.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}

function fmtMoney(v) { return parseFloat(v || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
</script>

<?php include ROOT_PATH . 'templates/member/footer.php'; ?>
