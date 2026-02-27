<?php $pageTitle = 'บริหารการเงิน'; $page = 'finance'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-wallet2 me-2"></i>บริหารการเงินสมาคม</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                        <li class="breadcrumb-item active">บริหารการเงิน</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Tabs Navigation -->
            <ul class="nav nav-tabs mb-3" id="financeTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="tab-overview" data-toggle="tab" href="#pane-overview" role="tab">
                        <i class="bi bi-graph-up me-1"></i>ภาพรวม
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-transactions" data-toggle="tab" href="#pane-transactions" role="tab">
                        <i class="bi bi-list-ul me-1"></i>รายการธุรกรรม
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-categories" data-toggle="tab" href="#pane-categories" role="tab">
                        <i class="bi bi-tags me-1"></i>หมวดหมู่
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="tab-managers" data-toggle="tab" href="#pane-managers" role="tab">
                        <i class="bi bi-person-gear me-1"></i>ผู้จัดการการเงิน
                    </a>
                </li>
            </ul>

            <div class="tab-content" id="financeTabContent">

                <!-- ============================================================ -->
                <!-- TAB 1: OVERVIEW -->
                <!-- ============================================================ -->
                <div class="tab-pane fade show active" id="pane-overview" role="tabpanel">
                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="totalIncome">0</h3>
                                    <p>รายรับทั้งหมด (บาท)</p>
                                </div>
                                <div class="icon"><i class="bi bi-arrow-down-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="totalExpense">0</h3>
                                    <p>รายจ่ายทั้งหมด (บาท)</p>
                                </div>
                                <div class="icon"><i class="bi bi-arrow-up-circle"></i></div>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="totalBalance">0</h3>
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
                                    <select class="form-control form-control-sm" id="overviewYear"></select>
                                </div>
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">ช่วงเวลา</label>
                                    <select class="form-control form-control-sm" id="overviewPeriod">
                                        <option value="year">ทั้งปี</option>
                                        <option value="month">รายเดือน</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-md-right">
                                    <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                    <div class="btn-group">
                                        <button class="btn btn-outline-success btn-sm" onclick="exportReport('csv')">
                                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="exportReport('pdf')">
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
                                            <tr><th>หมวดหมู่</th><th class="text-right">จำนวนรายการ</th><th class="text-right">ยอดรวม (บาท)</th></tr>
                                        </thead>
                                        <tbody id="incomeByCategoryBody">
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
                                            <tr><th>หมวดหมู่</th><th class="text-right">จำนวนรายการ</th><th class="text-right">ยอดรวม (บาท)</th></tr>
                                        </thead>
                                        <tbody id="expenseByCategoryBody">
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
                                <select class="form-control form-control-sm" id="monthlyFilter" style="width:auto;min-width:140px" onchange="renderMonthlySummary(window._monthlyData)">
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
                                    <tbody id="monthlyBody">
                                        <tr><td colspan="4" class="text-center text-muted py-3">กำลังโหลด...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 2: TRANSACTIONS -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="pane-transactions" role="tabpanel">
                    <!-- Filters -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">ประเภท</label>
                                    <select class="form-control form-control-sm" id="txnFilterType">
                                        <option value="">ทั้งหมด</option>
                                        <option value="income">รายรับ</option>
                                        <option value="expense">รายจ่าย</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">หมวดหมู่</label>
                                    <select class="form-control form-control-sm" id="txnFilterCategory">
                                        <option value="">ทั้งหมด</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">จากวันที่</label>
                                    <input type="date" class="form-control form-control-sm" id="txnFilterDateFrom">
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">ถึงวันที่</label>
                                    <input type="date" class="form-control form-control-sm" id="txnFilterDateTo">
                                </div>
                                <div class="col-md-2 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">ค้นหา</label>
                                    <input type="text" class="form-control form-control-sm" id="txnFilterSearch" placeholder="ชื่อรายการ, เลขอ้างอิง...">
                                </div>
                                <div class="col-md-2 text-md-right">
                                    <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                    <div class="btn-group">
                                        <button class="btn btn-primary btn-sm" onclick="showCreateTxnModal()">
                                            <i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"></button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="#" onclick="exportTransactions('csv'); return false;"><i class="bi bi-file-earmark-spreadsheet me-2"></i>ส่งออก CSV</a>
                                            <a class="dropdown-item" href="#" onclick="exportTransactions('pdf'); return false;"><i class="bi bi-file-earmark-pdf me-2"></i>ส่งออก PDF</a>
                                        </div>
                                    </div>
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
                                            <th width="10%">เอกสาร</th>
                                            <th width="8%">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="txnTableBody">
                                        <tr><td colspan="10" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer clearfix">
                            <div class="float-left">
                                <div class="btn-group">
                                    <button class="btn btn-outline-success btn-sm" onclick="exportTransactions('csv')">
                                        <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                                    </button>
                                    <button class="btn btn-outline-danger btn-sm" onclick="exportTransactions('pdf')">
                                        <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                                    </button>
                                </div>
                            </div>
                            <div id="txnPagination"></div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 3: CATEGORIES -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="pane-categories" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-3 mb-2 mb-md-0">
                                    <label class="form-label mb-0 small text-muted">กรองตามประเภท</label>
                                    <select class="form-control form-control-sm" id="catFilterType">
                                        <option value="">ทั้งหมด</option>
                                        <option value="income">รายรับ</option>
                                        <option value="expense">รายจ่าย</option>
                                    </select>
                                </div>
                                <div class="col-md-9 text-md-right">
                                    <label class="form-label mb-0 small text-muted d-block">&nbsp;</label>
                                    <button class="btn btn-primary btn-sm" onclick="showCreateCategoryModal()">
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
                                    <tbody id="catTableBody">
                                        <tr><td colspan="7" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- TAB 4: MANAGERS -->
                <!-- ============================================================ -->
                <div class="tab-pane fade" id="pane-managers" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h3 class="card-title"><i class="bi bi-person-gear me-1"></i>ผู้จัดการการเงิน</h3>
                                    <p class="mb-0 small text-muted">มอบสิทธิ์ให้สมาชิกเป็นผู้จัดการการเงินของสมาคม</p>
                                </div>
                                <div class="col-md-6 text-md-right">
                                    <button class="btn btn-primary btn-sm" onclick="showAssignManagerModal()">
                                        <i class="bi bi-person-plus me-1"></i>มอบสิทธิ์
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
                                            <th width="20%">ชื่อ-สกุล</th>
                                            <th width="15%">อีเมล</th>
                                            <th width="10%">สิทธิ์</th>
                                            <th width="15%">สถานะ</th>
                                            <th width="15%">มอบสิทธิ์โดย</th>
                                            <th width="10%">วันที่</th>
                                            <th width="10%">จัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mgrTableBody">
                                        <tr><td colspan="8" class="text-center py-4 text-muted">กำลังโหลด...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="callout callout-info">
                        <h5><i class="bi bi-info-circle me-1"></i>เกี่ยวกับสิทธิ์ผู้จัดการการเงิน</h5>
                        <ul class="mb-0">
                            <li><strong>สร้าง (create)</strong> — สามารถเพิ่มรายการรายรับ/รายจ่ายใหม่ได้</li>
                            <li><strong>แก้ไข (edit)</strong> — สามารถแก้ไขรายการที่มีอยู่ได้</li>
                            <li><strong>ลบ (delete)</strong> — สามารถลบรายการได้</li>
                            <li><strong>ส่งออก (export)</strong> — สามารถส่งออกรายงานการเงินได้</li>
                        </ul>
                    </div>
                </div>

            </div><!-- /tab-content -->

        </div>
    </section>
</div>

<!-- ============================================================ -->
<!-- MODALS -->
<!-- ============================================================ -->

<!-- Create/Edit Transaction Modal -->
<div class="modal fade" id="txnModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="txnModalTitle"><i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="txnId">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>ประเภท <span class="text-danger">*</span></label>
                            <select class="form-control" id="txnType" onchange="loadCategoryDropdown()">
                                <option value="income">รายรับ</option>
                                <option value="expense">รายจ่าย</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>หมวดหมู่ <span class="text-danger">*</span></label>
                            <select class="form-control" id="txnCategory">
                                <option value="">-- เลือกหมวดหมู่ --</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>รายการ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="txnTitle" placeholder="ชื่อรายการ">
                </div>
                <div class="form-group">
                    <label>รายละเอียด</label>
                    <textarea class="form-control" id="txnDescription" rows="3" placeholder="รายละเอียดเพิ่มเติม..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>จำนวนเงิน (บาท) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="txnAmount" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>วันที่ทำรายการ <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="txnDate">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>เลขอ้างอิง</label>
                            <input type="text" class="form-control" id="txnRefNo" placeholder="หมายเลขเอกสาร/อ้างอิง">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>เอกสารแนบ</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="txnAttachmentFile" accept="image/*,.pdf">
                        <label class="custom-file-label" for="txnAttachmentFile">เลือกไฟล์...</label>
                    </div>
                    <input type="hidden" id="txnAttachment">
                    <div id="txnAttachmentPreview" class="mt-2" style="display:none;">
                        <img src="" class="img-thumbnail" style="max-height:100px" alt="preview">
                        <button type="button" class="btn btn-sm btn-outline-danger ml-2" onclick="removeTxnAttachment()">
                            <i class="bi bi-x"></i> ลบ
                        </button>
                    </div>
                </div>
                <div class="form-group">
                    <label>หมายเหตุ</label>
                    <textarea class="form-control" id="txnNote" rows="2" placeholder="หมายเหตุ (ถ้ามี)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="btnSaveTxn" onclick="saveTxn()">
                    <i class="bi bi-check-lg me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Category Modal -->
<div class="modal fade" id="catModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catModalTitle"><i class="bi bi-plus-circle me-1"></i>เพิ่มหมวดหมู่</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="catId">
                <div class="form-group">
                    <label>ชื่อหมวดหมู่ <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="catName" placeholder="ชื่อหมวดหมู่">
                </div>
                <div class="form-group">
                    <label>ประเภท <span class="text-danger">*</span></label>
                    <select class="form-control" id="catType">
                        <option value="income">รายรับ</option>
                        <option value="expense">รายจ่าย</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>คำอธิบาย</label>
                    <textarea class="form-control" id="catDescription" rows="2" placeholder="คำอธิบาย"></textarea>
                </div>
                <div class="form-group">
                    <label>ลำดับการแสดง</label>
                    <input type="number" class="form-control" id="catSortOrder" value="0" min="0">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="btnSaveCat" onclick="saveCategory()">
                    <i class="bi bi-check-lg me-1"></i>บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assign/Edit Manager Modal -->
<div class="modal fade" id="mgrModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mgrModalTitle"><i class="bi bi-person-plus me-1"></i>มอบสิทธิ์ผู้จัดการการเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="mgrEditUserId" value="">
                <div class="form-group" id="mgrUserSelectGroup">
                    <label>เลือกสมาชิก <span class="text-danger">*</span></label>
                    <select class="form-control" id="mgrUserId">
                        <option value="">-- เลือกสมาชิก --</option>
                    </select>
                </div>
                <div class="form-group" id="mgrUserInfoGroup" style="display:none;">
                    <label>สมาชิก</label>
                    <p class="form-control-plaintext font-weight-bold" id="mgrUserInfoName"></p>
                </div>
                <div class="form-group">
                    <label>สิทธิ์ที่มอบให้</label>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="permCreate" value="create" checked>
                        <label class="custom-control-label" for="permCreate">สร้างรายการ (create)</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="permEdit" value="edit" checked>
                        <label class="custom-control-label" for="permEdit">แก้ไขรายการ (edit)</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="permDelete" value="delete" checked>
                        <label class="custom-control-label" for="permDelete">ลบรายการ (delete)</label>
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="permExport" value="export" checked>
                        <label class="custom-control-label" for="permExport">ส่งออกรายงาน (export)</label>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button class="btn btn-primary" id="mgrModalSaveBtn" onclick="assignManager()">
                    <i class="bi bi-check-lg me-1"></i>มอบสิทธิ์
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Attachment Preview Modal -->
<div class="modal fade" id="attachModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">เอกสารแนบ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <img id="attachPreviewImg" src="" class="img-fluid rounded" style="max-height:500px" alt="attachment">
            </div>
        </div>
    </div>
</div>

<!-- Transaction Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-text me-1"></i>รายละเอียดรายการ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="detailModalBody">
            </div>
        </div>
    </div>
</div>

<!-- Receipt Preview Modal -->
<div class="modal fade" id="receiptPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>ใบเสร็จรับเงิน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="receiptPreviewBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" onclick="downloadReceiptPDF()"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</button>
                <button type="button" class="btn btn-success btn-sm" onclick="downloadReceiptPNG()"><i class="bi bi-image me-1"></i>PNG</button>
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Hidden PDF render area -->
<div id="pdfRenderArea" style="position:absolute;left:-9999px;top:0;width:800px;background:#fff;padding:30px;font-family:'Sarabun',sans-serif;color:#333;"></div>

<!-- html2canvas & jsPDF for PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<!-- Hidden PDF render area -->
<div id="pdfRenderArea" style="position:absolute;left:-9999px;top:0;width:800px;background:#fff;padding:30px;font-family:'Sarabun',sans-serif;color:#333;"></div>

<script>
// === CONSTANTS ===
const MONTHS_TH = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
const PERM_LABELS = { create: 'สร้าง', edit: 'แก้ไข', delete: 'ลบ', export: 'ส่งออก', approve: 'อนุมัติ' };
let allCategories = [];

$(function () {
    // Populate year dropdown
    const currentYear = new Date().getFullYear();
    let yearOpts = '';
    for (let y = currentYear + 1; y >= currentYear - 5; y--) {
        yearOpts += `<option value="${y}" ${y === currentYear ? 'selected' : ''}>${y}</option>`;
    }
    $('#overviewYear').html(yearOpts);
    $('#txnDate').val(new Date().toISOString().slice(0, 10));

    // Load initial data
    loadOverview();
    loadCategories();

    // Tab change events
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        const target = $(e.target).attr('href');
        if (target === '#pane-transactions') loadTransactions(1);
        if (target === '#pane-categories') loadCategories();
        if (target === '#pane-managers') loadManagers();
        if (target === '#pane-overview') loadOverview();
    });

    // Transaction filters
    $('#txnFilterType, #txnFilterCategory').on('change', () => loadTransactions(1));
    $('#txnFilterDateFrom, #txnFilterDateTo').on('change', () => loadTransactions(1));
    let searchTimer;
    $('#txnFilterSearch').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadTransactions(1), 400);
    });

    // Category filter
    $('#catFilterType').on('change', () => loadCategories());

    // Overview filter
    $('#overviewYear, #overviewPeriod').on('change', () => loadOverview());

    // File upload
    $('#txnAttachmentFile').on('change', async function () {
        const file = this.files[0];
        if (!file) return;
        $(this).next('.custom-file-label').text(file.name);
        try {
            const result = await API.upload(file, 'finance');
            if (result.success) {
                $('#txnAttachment').val(result.data.url);
                if (file.type.startsWith('image/')) {
                    const src = (result.data.url.startsWith('http') || result.data.url.startsWith('//')) ? result.data.url : (BASE_PATH + result.data.url);
                    $('#txnAttachmentPreview').show().find('img').attr('src', src);
                }
            } else {
                App.error(result.message || 'อัพโหลดไม่สำเร็จ');
            }
        } catch (e) {
            App.error('เกิดข้อผิดพลาดในการอัพโหลด');
        }
    });
});

// === OVERVIEW ===
async function loadOverview() {
    const year = $('#overviewYear').val();
    const params = { year };

    // Load summary
    const summaryRes = await API.getFinanceSummary(params);
    if (summaryRes.success) {
        const s = summaryRes.data.summary;
        $('#totalIncome').text(formatMoney(s.total_income));
        $('#totalExpense').text(formatMoney(s.total_expense));
        $('#totalBalance').text(formatMoney(s.balance));

        // By category
        const byCat = summaryRes.data.by_category || [];
        renderCategorySummary(byCat);
    }

    // Monthly summary
    const monthlyRes = await API.getFinanceMonthlySummary(year);
    if (monthlyRes.success) {
        window._monthlyData = monthlyRes.data;
        renderMonthlySummary(monthlyRes.data);
    }
}

function renderCategorySummary(data) {
    const incomeRows = data.filter(c => c.category_type === 'income');
    const expenseRows = data.filter(c => c.category_type === 'expense');

    let incomeHtml = '';
    let incomeTotalAmount = 0;
    if (incomeRows.length === 0) {
        incomeHtml = '<tr><td colspan="3" class="text-center text-muted py-2">ไม่มีข้อมูล</td></tr>';
    } else {
        incomeRows.forEach(r => {
            incomeTotalAmount += parseFloat(r.total_amount);
            incomeHtml += `<tr>
                <td>${r.category_name}</td>
                <td class="text-right">${r.transaction_count}</td>
                <td class="text-right text-success font-weight-bold">${formatMoney(r.total_amount)}</td>
            </tr>`;
        });
        incomeHtml += `<tr class="font-weight-bold bg-light"><td>รวม</td><td></td><td class="text-right text-success">${formatMoney(incomeTotalAmount)}</td></tr>`;
    }
    $('#incomeByCategoryBody').html(incomeHtml);

    let expenseHtml = '';
    let expenseTotalAmount = 0;
    if (expenseRows.length === 0) {
        expenseHtml = '<tr><td colspan="3" class="text-center text-muted py-2">ไม่มีข้อมูล</td></tr>';
    } else {
        expenseRows.forEach(r => {
            expenseTotalAmount += parseFloat(r.total_amount);
            expenseHtml += `<tr>
                <td>${r.category_name}</td>
                <td class="text-right">${r.transaction_count}</td>
                <td class="text-right text-danger font-weight-bold">${formatMoney(r.total_amount)}</td>
            </tr>`;
        });
        expenseHtml += `<tr class="font-weight-bold bg-light"><td>รวม</td><td></td><td class="text-right text-danger">${formatMoney(expenseTotalAmount)}</td></tr>`;
    }
    $('#expenseByCategoryBody').html(expenseHtml);
}

function renderMonthlySummary(data) {
    if (!data || data.length === 0) {
        $('#monthlyBody').html('<tr><td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูล</td></tr>');
        return;
    }
    const filterMonth = $('#monthlyFilter').val();
    let filtered = data;
    if (filterMonth) {
        filtered = data.filter(m => {
            const monthNum = parseInt(m.month.split('-')[1]);
            return monthNum === parseInt(filterMonth);
        });
    }
    if (filtered.length === 0) {
        $('#monthlyBody').html('<tr><td colspan="4" class="text-center text-muted py-3">ไม่มีข้อมูลในเดือนที่เลือก</td></tr>');
        return;
    }
    let html = '';
    let totalIncome = 0, totalExpense = 0;
    filtered.forEach(m => {
        const parts = m.month.split('-');
        const monthNum = parseInt(parts[1]);
        const monthName = MONTHS_TH[monthNum] + ' ' + parts[0];
        totalIncome += m.income;
        totalExpense += m.expense;
        const balanceClass = m.balance >= 0 ? 'text-info' : 'text-danger';
        html += `<tr>
            <td>${monthName}</td>
            <td class="text-right text-success">${formatMoney(m.income)}</td>
            <td class="text-right text-danger">${formatMoney(m.expense)}</td>
            <td class="text-right ${balanceClass} font-weight-bold">${formatMoney(m.balance)}</td>
        </tr>`;
    });
    const totalBalance = totalIncome - totalExpense;
    const totalBalanceClass = totalBalance >= 0 ? 'text-info' : 'text-danger';
    const summaryLabel = filterMonth ? 'รวมเดือนที่เลือก' : 'รวมทั้งปี';
    html += `<tr class="font-weight-bold bg-light">
        <td>${summaryLabel}</td>
        <td class="text-right text-success">${formatMoney(totalIncome)}</td>
        <td class="text-right text-danger">${formatMoney(totalExpense)}</td>
        <td class="text-right ${totalBalanceClass}">${formatMoney(totalBalance)}</td>
    </tr>`;
    $('#monthlyBody').html(html);
}

// === TRANSACTIONS ===
async function loadTransactions(page = 1) {
    const params = { page, per_page: 20 };
    const type = $('#txnFilterType').val();
    const categoryId = $('#txnFilterCategory').val();
    const dateFrom = $('#txnFilterDateFrom').val();
    const dateTo = $('#txnFilterDateTo').val();
    const search = $('#txnFilterSearch').val().trim();

    if (type) params.type = type;
    if (categoryId) params.category_id = categoryId;
    if (dateFrom) params.date_from = dateFrom;
    if (dateTo) params.date_to = dateTo;
    if (search) params.search = search;

    const result = await API.getFinanceTransactions(params);
    const tbody = $('#txnTableBody');

    if (!result.success) {
        tbody.html(`<tr><td colspan="10" class="text-center text-danger py-3">${result.message}</td></tr>`);
        return;
    }

    if (!result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>');
        $('#txnPagination').empty();
        return;
    }

    const startNum = ((result.pagination?.current_page || 1) - 1) * (result.pagination?.per_page || 20);
    let html = '';
    result.data.forEach((txn, i) => {
        const typeBadge = txn.type === 'income'
            ? '<span class="badge badge-success"><i class="bi bi-arrow-down-circle me-1"></i>รายรับ</span>'
            : '<span class="badge badge-danger"><i class="bi bi-arrow-up-circle me-1"></i>รายจ่าย</span>';

        const amountClass = txn.type === 'income' ? 'text-success' : 'text-danger';
        const amountPrefix = txn.type === 'income' ? '+' : '-';

        const attachBtn = txn.attachment
            ? `<a href="#" onclick="previewAttachment('${txn.attachment}'); return false;" class="btn btn-xs btn-outline-info"><i class="bi bi-paperclip"></i></a>`
            : '<span class="text-muted">-</span>';

        html += `<tr>
            <td>${startNum + i + 1}</td>
            <td><small>${App.formatDate(txn.transaction_date)}</small></td>
            <td>${typeBadge}</td>
            <td><small>${App.escHtml(txn.category_name || '-')}</small></td>
            <td>
                <a href="#" onclick="showTxnDetail(${txn.id}); return false;" class="text-primary">
                    <strong>${App.escHtml(txn.title)}</strong>
                </a>
                ${txn.description ? '<br><small class="text-muted">' + App.escHtml(txn.description).substring(0, 50) + '</small>' : ''}
            </td>
            <td class="text-right ${amountClass} font-weight-bold">${amountPrefix}${formatMoney(txn.amount)}</td>
            <td><small>${App.escHtml(txn.reference_no || '-')}</small></td>
            <td><small>${App.escHtml(txn.creator_name || '-')}</small></td>
            <td class="text-center">${attachBtn}</td>
            <td class="text-nowrap">
                ${txn.reference_no && /^(FEE-|ACT-REG-)/.test(txn.reference_no) ? `<button class="btn btn-xs btn-outline-success mr-1" onclick="viewTxnReceipt('${App.escHtml(txn.reference_no)}')" title="ใบเสร็จ"><i class="bi bi-receipt"></i></button>` : ''}
                <button class="btn btn-xs btn-outline-primary mr-1" onclick="editTxn(${txn.id})" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-xs btn-outline-danger" onclick="deleteTxn(${txn.id}, '${App.escHtml(txn.title)}')" title="ลบ"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });
    tbody.html(html);

    if (result.pagination) {
        App.buildPagination('#txnPagination', result.pagination, loadTransactions);
    }
}

function showCreateTxnModal() {
    $('#txnModalTitle').html('<i class="bi bi-plus-circle me-1"></i>เพิ่มรายการ');
    $('#txnId').val('');
    $('#txnType').val('income');
    $('#txnTitle').val('');
    $('#txnDescription').val('');
    $('#txnAmount').val('');
    $('#txnDate').val(new Date().toISOString().slice(0, 10));
    $('#txnRefNo').val('');
    $('#txnAttachment').val('');
    $('#txnAttachmentPreview').hide();
    $('#txnNote').val('');
    $('#txnAttachmentFile').val('').next('.custom-file-label').text('เลือกไฟล์...');
    loadCategoryDropdown();
    $('#txnModal').modal('show');
}

async function editTxn(id) {
    const result = await API.getFinanceDetail(id);
    if (!result.success) { App.error(result.message); return; }
    const txn = result.data;

    $('#txnModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขรายการ');
    $('#txnId').val(txn.id);
    $('#txnType').val(txn.type);
    await loadCategoryDropdown();
    $('#txnCategory').val(txn.category_id);
    $('#txnTitle').val(txn.title);
    $('#txnDescription').val(txn.description || '');
    $('#txnAmount').val(txn.amount);
    $('#txnDate').val(txn.transaction_date);
    $('#txnRefNo').val(txn.reference_no || '');
    $('#txnAttachment').val(txn.attachment || '');
    $('#txnNote').val(txn.note || '');

    if (txn.attachment) {
        const src = (txn.attachment.startsWith('http') || txn.attachment.startsWith('//')) ? txn.attachment : (BASE_PATH + txn.attachment);
        $('#txnAttachmentPreview').show().find('img').attr('src', src);
    } else {
        $('#txnAttachmentPreview').hide();
    }

    $('#txnModal').modal('show');
}

async function saveTxn() {
    const id = $('#txnId').val();
    const data = {
        type: $('#txnType').val(),
        category_id: $('#txnCategory').val(),
        title: $('#txnTitle').val().trim(),
        description: $('#txnDescription').val().trim(),
        amount: parseFloat($('#txnAmount').val()) || 0,
        transaction_date: $('#txnDate').val(),
        reference_no: $('#txnRefNo').val().trim(),
        attachment: $('#txnAttachment').val(),
        note: $('#txnNote').val().trim(),
    };

    if (!data.title) { App.error('กรุณากรอกรายการ'); return; }
    if (!data.category_id) { App.error('กรุณาเลือกหมวดหมู่'); return; }
    if (data.amount <= 0) { App.error('กรุณากรอกจำนวนเงินที่ถูกต้อง'); return; }
    if (!data.transaction_date) { App.error('กรุณาเลือกวันที่'); return; }

    const btn = $('#btnSaveTxn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

    let result;
    if (id) {
        data.id = parseInt(id);
        result = await API.updateFinanceTransaction(data);
    } else {
        result = await API.createFinanceTransaction(data);
    }

    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>บันทึก');

    if (result.success) {
        App.success(result.message);
        $('#txnModal').modal('hide');
        loadTransactions(1);
        loadOverview();
    } else {
        App.error(result.message);
    }
}

async function deleteTxn(id, title) {
    const confirm = await Swal.fire({
        title: 'ยืนยันการลบ',
        html: `ต้องการลบรายการ <strong>${title}</strong> ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });
    if (!confirm.isConfirmed) return;

    const result = await API.deleteFinanceTransaction(id);
    if (result.success) {
        App.success(result.message);
        loadTransactions(1);
        loadOverview();
    } else {
        App.error(result.message);
    }
}

async function showTxnDetail(id) {
    const result = await API.getFinanceDetail(id);
    if (!result.success) { App.error(result.message); return; }
    const t = result.data;

    const typeBadge = t.type === 'income'
        ? '<span class="badge badge-success badge-lg"><i class="bi bi-arrow-down-circle me-1"></i>รายรับ</span>'
        : '<span class="badge badge-danger badge-lg"><i class="bi bi-arrow-up-circle me-1"></i>รายจ่าย</span>';

    const attachImg = t.attachment
        ? `<div class="mt-3"><strong>เอกสารแนบ:</strong><br><img src="${(t.attachment.startsWith('http') ? t.attachment : BASE_PATH + t.attachment)}" class="img-fluid rounded mt-1" style="max-height:300px"></div>`
        : '';

    $('#detailModalBody').html(`
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr><th width="35%">ประเภท</th><td>${typeBadge}</td></tr>
                    <tr><th>หมวดหมู่</th><td>${App.escHtml(t.category_name || '-')}</td></tr>
                    <tr><th>รายการ</th><td><strong>${App.escHtml(t.title)}</strong></td></tr>
                    <tr><th>จำนวนเงิน</th><td class="font-weight-bold ${t.type === 'income' ? 'text-success' : 'text-danger'}" style="font-size:1.2em">${formatMoney(t.amount)} บาท</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless table-sm">
                    <tr><th width="35%">วันที่ทำรายการ</th><td>${App.formatDate(t.transaction_date)}</td></tr>
                    <tr><th>เลขอ้างอิง</th><td>${App.escHtml(t.reference_no || '-')}</td></tr>
                    <tr><th>บันทึกโดย</th><td>${App.escHtml(t.creator_name || '-')}</td></tr>
                    <tr><th>วันที่บันทึก</th><td>${App.formatDate(t.created_at)}</td></tr>
                </table>
            </div>
        </div>
        ${t.description ? '<div class="mt-2"><strong>รายละเอียด:</strong><p class="text-muted">' + App.escHtml(t.description) + '</p></div>' : ''}
        ${t.note ? '<div class="mt-2"><strong>หมายเหตุ:</strong><p class="text-muted">' + App.escHtml(t.note) + '</p></div>' : ''}
        ${attachImg}
    `);
    $('#detailModal').modal('show');
}

function removeTxnAttachment() {
    $('#txnAttachment').val('');
    $('#txnAttachmentPreview').hide();
    $('#txnAttachmentFile').val('').next('.custom-file-label').text('เลือกไฟล์...');
}

function previewAttachment(url) {
    const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
    $('#attachPreviewImg').attr('src', src);
    $('#attachModal').modal('show');
}

// === CATEGORIES ===
async function loadCategories() {
    const params = {};
    const type = $('#catFilterType').val();
    if (type) params.type = type;

    const result = await API.getFinanceCategories(params);
    const tbody = $('#catTableBody');

    if (!result.success) {
        tbody.html(`<tr><td colspan="7" class="text-center text-danger py-3">${result.message}</td></tr>`);
        return;
    }

    allCategories = result.data || [];

    // Also populate filter dropdowns
    populateCategoryFilter();

    if (allCategories.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center text-muted py-4">ไม่พบหมวดหมู่</td></tr>');
        return;
    }

    let html = '';
    allCategories.forEach((cat, i) => {
        const typeBadge = cat.type === 'income'
            ? '<span class="badge badge-success">รายรับ</span>'
            : '<span class="badge badge-danger">รายจ่าย</span>';
        const statusBadge = cat.is_active == 1
            ? '<span class="badge badge-success">ใช้งาน</span>'
            : '<span class="badge badge-secondary">ปิดใช้งาน</span>';

        html += `<tr>
            <td>${i + 1}</td>
            <td><strong>${App.escHtml(cat.name)}</strong></td>
            <td>${typeBadge}</td>
            <td><small class="text-muted">${App.escHtml(cat.description || '-')}</small></td>
            <td>${statusBadge}</td>
            <td>${cat.sort_order}</td>
            <td>
                <button class="btn btn-xs btn-outline-primary mr-1" onclick="editCategory(${cat.id})" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-xs btn-outline-danger" onclick="deleteCategory(${cat.id}, '${App.escHtml(cat.name)}')" title="ลบ"><i class="bi bi-trash"></i></button>
            </td>
        </tr>`;
    });
    tbody.html(html);
}

function populateCategoryFilter() {
    let opts = '<option value="">ทั้งหมด</option>';
    allCategories.forEach(c => {
        if (c.is_active == 1) {
            const prefix = c.type === 'income' ? '📥 ' : '📤 ';
            opts += `<option value="${c.id}">${prefix}${c.name}</option>`;
        }
    });
    $('#txnFilterCategory').html(opts);
}

async function loadCategoryDropdown() {
    const type = $('#txnType').val();
    const result = await API.getFinanceActiveCategories(type);
    let opts = '<option value="">-- เลือกหมวดหมู่ --</option>';
    if (result.success && result.data) {
        result.data.forEach(c => {
            opts += `<option value="${c.id}">${c.name}</option>`;
        });
    }
    $('#txnCategory').html(opts);
}

function showCreateCategoryModal() {
    $('#catModalTitle').html('<i class="bi bi-plus-circle me-1"></i>เพิ่มหมวดหมู่');
    $('#catId').val('');
    $('#catName').val('');
    $('#catType').val('income');
    $('#catDescription').val('');
    $('#catSortOrder').val(0);
    $('#catModal').modal('show');
}

function editCategory(id) {
    const cat = allCategories.find(c => c.id == id);
    if (!cat) return;
    $('#catModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขหมวดหมู่');
    $('#catId').val(cat.id);
    $('#catName').val(cat.name);
    $('#catType').val(cat.type);
    $('#catDescription').val(cat.description || '');
    $('#catSortOrder').val(cat.sort_order || 0);
    $('#catModal').modal('show');
}

async function saveCategory() {
    const id = $('#catId').val();
    const data = {
        name: $('#catName').val().trim(),
        type: $('#catType').val(),
        description: $('#catDescription').val().trim(),
        sort_order: parseInt($('#catSortOrder').val()) || 0,
    };

    if (!data.name) { App.error('กรุณากรอกชื่อหมวดหมู่'); return; }

    const btn = $('#btnSaveCat');
    btn.prop('disabled', true);

    let result;
    if (id) {
        data.id = parseInt(id);
        result = await API.updateFinanceCategory(data);
    } else {
        result = await API.createFinanceCategory(data);
    }

    btn.prop('disabled', false);

    if (result.success) {
        App.success(result.message);
        $('#catModal').modal('hide');
        loadCategories();
    } else {
        App.error(result.message);
    }
}

async function deleteCategory(id, name) {
    const confirm = await Swal.fire({
        title: 'ยืนยันการลบ',
        html: `ต้องการลบหมวดหมู่ <strong>${name}</strong> ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });
    if (!confirm.isConfirmed) return;

    const result = await API.deleteFinanceCategory(id);
    if (result.success) {
        App.success(result.message);
        loadCategories();
    } else {
        App.error(result.message);
    }
}

// === MANAGERS ===
async function loadManagers() {
    const result = await API.getFinanceManagers();
    const tbody = $('#mgrTableBody');

    if (!result.success) {
        tbody.html(`<tr><td colspan="8" class="text-center text-danger py-3">${result.message}</td></tr>`);
        return;
    }

    if (!result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="8" class="text-center text-muted py-4">ยังไม่มีผู้จัดการการเงิน</td></tr>');
        return;
    }

    let html = '';
    result.data.forEach((mgr, i) => {
        const permList = mgr.permissions || [];
        const perms = permList.map(p => PERM_LABELS[p] || p).join(' · ');
        const isActive = mgr.is_active == 1;
        const statusBadge = isActive
            ? '<span class="badge badge-success"><i class="bi bi-check-circle mr-1"></i>ใช้งาน</span>'
            : '<span class="badge badge-secondary"><i class="bi bi-lock mr-1"></i>ถูกล็อค</span>';

        const toggleIcon = isActive ? 'bi-lock' : 'bi-unlock';
        const toggleColor = isActive ? 'warning' : 'success';
        const toggleTitle = isActive ? 'ล็อค' : 'ปลดล็อค';
        const permsEncoded = encodeURIComponent(JSON.stringify(permList));

        html += `<tr class="${!isActive ? 'table-secondary' : ''}">
            <td>${i + 1}</td>
            <td><strong>${App.escHtml(mgr.user_name || '-')}</strong></td>
            <td><small>${App.escHtml(mgr.user_email || '-')}</small></td>
            <td><small class="text-secondary">${perms || '-'}</small></td>
            <td>${statusBadge}</td>
            <td><small>${App.escHtml(mgr.assigner_name || '-')}</small></td>
            <td><small>${App.formatDate(mgr.created_at)}</small></td>
            <td class="text-nowrap">
                <button class="btn btn-xs btn-outline-primary mr-1" data-perms="${permsEncoded}" onclick="showEditManagerModal(${mgr.user_id}, '${App.escHtml(mgr.user_name)}', this)" title="แก้ไขสิทธิ์">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-xs btn-outline-${toggleColor} mr-1" onclick="toggleManager(${mgr.user_id}, '${App.escHtml(mgr.user_name)}', ${isActive ? 1 : 0})" title="${toggleTitle}">
                    <i class="bi ${toggleIcon}"></i>
                </button>
                <button class="btn btn-xs btn-outline-danger" onclick="deleteManager(${mgr.user_id}, '${App.escHtml(mgr.user_name)}')" title="ลบ">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>`;
    });
    tbody.html(html);
}

async function showAssignManagerModal() {
    // Reset to create mode
    $('#mgrEditUserId').val('');
    $('#mgrModalTitle').html('<i class="bi bi-person-plus me-1"></i>มอบสิทธิ์ผู้จัดการการเงิน');
    $('#mgrUserSelectGroup').show();
    $('#mgrUserInfoGroup').hide();
    $('#mgrModalSaveBtn').html('<i class="bi bi-check-lg me-1"></i>มอบสิทธิ์').attr('onclick', 'assignManager()');

    const result = await API.getFinanceAvailableMembers();
    let opts = '<option value="">-- เลือกสมาชิก --</option>';
    if (result.success && result.data) {
        result.data.forEach(m => {
            const roleLabel = m.role === 'admin' ? ' (admin)' : '';
            opts += `<option value="${m.id}">${m.full_name || m.email}${roleLabel}</option>`;
        });
    }
    $('#mgrUserId').html(opts);
    $('#permCreate, #permEdit, #permDelete, #permExport').prop('checked', true);
    $('#mgrModal').modal('show');
}

function showEditManagerModal(userId, userName, btnEl) {
    // Set to edit mode — read permissions from data attribute
    let perms = [];
    try {
        const raw = $(btnEl).attr('data-perms');
        perms = JSON.parse(decodeURIComponent(raw));
    } catch(e) { perms = []; }

    $('#mgrEditUserId').val(userId);
    $('#mgrModalTitle').html('<i class="bi bi-pencil me-1"></i>แก้ไขสิทธิ์ผู้จัดการการเงิน');
    $('#mgrUserSelectGroup').hide();
    $('#mgrUserInfoGroup').show();
    $('#mgrUserInfoName').text(userName);
    $('#mgrModalSaveBtn').html('<i class="bi bi-save me-1"></i>บันทึก').attr('onclick', 'updateManagerPermissions()');

    $('#permCreate').prop('checked', perms.includes('create'));
    $('#permEdit').prop('checked', perms.includes('edit'));
    $('#permDelete').prop('checked', perms.includes('delete'));
    $('#permExport').prop('checked', perms.includes('export'));

    $('#mgrModal').modal('show');
}

async function assignManager() {
    const userId = $('#mgrUserId').val();
    if (!userId) { App.error('กรุณาเลือกสมาชิก'); return; }

    const permissions = getSelectedPermissions();

    const result = await API.assignFinanceManager(parseInt(userId), permissions);
    if (result.success) {
        App.success(result.message);
        $('#mgrModal').modal('hide');
        loadManagers();
    } else {
        App.error(result.message);
    }
}

async function updateManagerPermissions() {
    const userId = $('#mgrEditUserId').val();
    if (!userId) { App.error('ไม่พบข้อมูลผู้ใช้'); return; }

    const permissions = getSelectedPermissions();

    const result = await API.updateFinanceManagerPermissions(parseInt(userId), permissions);
    if (result.success) {
        App.success(result.message);
        $('#mgrModal').modal('hide');
        loadManagers();
    } else {
        App.error(result.message);
    }
}

function getSelectedPermissions() {
    const permissions = [];
    if ($('#permCreate').is(':checked')) permissions.push('create');
    if ($('#permEdit').is(':checked')) permissions.push('edit');
    if ($('#permDelete').is(':checked')) permissions.push('delete');
    if ($('#permExport').is(':checked')) permissions.push('export');
    return permissions;
}

async function toggleManager(userId, name, currentActive) {
    const action = currentActive ? 'ล็อค' : 'ปลดล็อค';
    const icon = currentActive ? 'warning' : 'question';
    const msg = currentActive
        ? `ต้องการล็อคผู้จัดการการเงิน <strong>${name}</strong> ใช่หรือไม่?<br><small class="text-muted">ผู้ใช้จะไม่สามารถใช้งานฟังก์ชันการเงินได้</small>`
        : `ต้องการปลดล็อคผู้จัดการการเงิน <strong>${name}</strong> ใช่หรือไม่?<br><small class="text-muted">ผู้ใช้จะสามารถใช้งานฟังก์ชันการเงินได้อีกครั้ง</small>`;

    const confirm = await Swal.fire({
        title: `ยืนยันการ${action}`,
        html: msg,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: currentActive ? '#ffc107' : '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: action,
        cancelButtonText: 'ยกเลิก'
    });
    if (!confirm.isConfirmed) return;

    const result = await API.toggleFinanceManager(userId);
    if (result.success) {
        App.success(result.message);
        loadManagers();
    } else {
        App.error(result.message);
    }
}

async function deleteManager(userId, name) {
    const confirm = await Swal.fire({
        title: 'ยืนยันการลบ',
        html: `ต้องการลบผู้จัดการการเงิน <strong>${name}</strong> ออกจากระบบใช่หรือไม่?<br><small class="text-danger">การดำเนินการนี้ไม่สามารถย้อนกลับได้</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="bi bi-trash mr-1"></i>ลบ',
        cancelButtonText: 'ยกเลิก'
    });
    if (!confirm.isConfirmed) return;

    const result = await API.deleteFinanceManager(userId);
    if (result.success) {
        App.success(result.message);
        loadManagers();
    } else {
        App.error(result.message);
    }
}

async function revokeManager(userId, name) {
    const confirm = await Swal.fire({
        title: 'ยืนยันการถอนสิทธิ์',
        html: `ต้องการถอนสิทธิ์ผู้จัดการการเงินของ <strong>${name}</strong> ใช่หรือไม่?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'ถอนสิทธิ์',
        cancelButtonText: 'ยกเลิก'
    });
    if (!confirm.isConfirmed) return;

    const result = await API.revokeFinanceManager(userId);
    if (result.success) {
        App.success(result.message);
        loadManagers();
    } else {
        App.error(result.message);
    }
}

// === EXPORT ===
async function exportReport(format = 'csv') {
    const year = $('#overviewYear').val();
    const result = await API.exportFinance({ year });
    if (!result.success) { App.error(result.message); return; }
    const { transactions, summary, exported_at, exported_by } = result.data;
    if (format === 'pdf') {
        await generateFinancePDF(transactions, summary, `ปี ${year}`, exported_at, exported_by, `รายงานการเงิน_${year}`);
    } else {
        generateFinanceCSV(transactions, summary, `ปี ${year}`, exported_at, exported_by, `รายงานการเงิน_${year}.csv`);
    }
}

async function exportTransactions(format = 'csv') {
    const params = {};
    if ($('#txnFilterType').val()) params.type = $('#txnFilterType').val();
    if ($('#txnFilterCategory').val()) params.category_id = $('#txnFilterCategory').val();
    if ($('#txnFilterDateFrom').val()) params.date_from = $('#txnFilterDateFrom').val();
    if ($('#txnFilterDateTo').val()) params.date_to = $('#txnFilterDateTo').val();
    if (!params.date_from && !params.date_to) params.year = $('#overviewYear').val();
    const result = await API.exportFinance(params);
    if (!result.success) { App.error(result.message); return; }
    const { transactions, summary, exported_at, exported_by } = result.data;
    const dateStr = new Date().toISOString().slice(0, 10);
    if (format === 'pdf') {
        await generateFinancePDF(transactions, summary, 'รายการธุรกรรม', exported_at, exported_by, `รายการธุรกรรม_${dateStr}`);
    } else {
        generateFinanceCSV(transactions, summary, 'รายการธุรกรรม', exported_at, exported_by, `รายการธุรกรรม_${dateStr}.csv`);
    }
}

function generateFinanceCSV(transactions, summary, label, exported_at, exported_by, filename) {
    const esc = (s) => `"${(s || '').replace(/"/g, '""')}"`;
    let csv = '\uFEFF';
    csv += 'รายงานการเงินสมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์\n';
    csv += `${label}\n`;
    csv += `ส่งออกเมื่อ: ${exported_at}\n`;
    csv += `ส่งออกโดย: ${exported_by}\n\n`;
    csv += `สรุป\n`;
    csv += `รายรับทั้งหมด,${summary.total_income}\n`;
    csv += `รายจ่ายทั้งหมด,${summary.total_expense}\n`;
    csv += `คงเหลือ,${summary.balance}\n\n`;
    csv += 'ลำดับ,วันที่,ประเภท,หมวดหมู่,รายการ,รายละเอียด,จำนวนเงิน,เลขอ้างอิง,บันทึกโดย,หมายเหตุ\n';
    transactions.forEach((t, i) => {
        const typeLabel = t.type === 'income' ? 'รายรับ' : 'รายจ่าย';
        csv += `${i+1},${t.transaction_date},${typeLabel},${esc(t.category_name)},${esc(t.title)},${esc(t.description)},${t.amount},${esc(t.reference_no)},${esc(t.creator_name)},${esc(t.note)}\n`;
    });
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
    URL.revokeObjectURL(link.href);
    App.success('ส่งออก CSV สำเร็จ');
}

async function generateFinancePDF(transactions, summary, label, exported_at, exported_by, filename) {
    const area = document.getElementById('pdfRenderArea');
    const fmtNum = (v) => parseFloat(v || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    let html = `
        <div style="text-align:center;margin-bottom:18px;">
            <div style="font-size:20px;font-weight:700;color:#1a3c5e;">รายงานการเงิน</div>
            <div style="font-size:16px;font-weight:600;">สมาคมรองผู้อำนวยการโรงเรียนมัธยมศึกษาจังหวัดกาฬสินธุ์</div>
            <div style="font-size:13px;color:#666;">${label} — ส่งออกเมื่อ ${exported_at} โดย ${exported_by}</div>
        </div>
        <div style="display:flex;gap:20px;margin-bottom:18px;">
            <div style="flex:1;background:#d4edda;padding:12px;border-radius:6px;text-align:center;">
                <div style="font-size:12px;color:#155724;">รายรับทั้งหมด</div>
                <div style="font-size:18px;font-weight:700;color:#155724;">+${fmtNum(summary.total_income)}</div>
            </div>
            <div style="flex:1;background:#f8d7da;padding:12px;border-radius:6px;text-align:center;">
                <div style="font-size:12px;color:#721c24;">รายจ่ายทั้งหมด</div>
                <div style="font-size:18px;font-weight:700;color:#721c24;">-${fmtNum(summary.total_expense)}</div>
            </div>
            <div style="flex:1;background:#cce5ff;padding:12px;border-radius:6px;text-align:center;">
                <div style="font-size:12px;color:#004085;">คงเหลือ</div>
                <div style="font-size:18px;font-weight:700;color:#004085;">${fmtNum(summary.balance)}</div>
            </div>
        </div>
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
            <thead>
                <tr style="background:#1a5276;color:#fff;">
                    <th style="padding:6px;border:1px solid #ddd;text-align:center;width:30px;">#</th>
                    <th style="padding:6px;border:1px solid #ddd;">วันที่</th>
                    <th style="padding:6px;border:1px solid #ddd;text-align:center;">ประเภท</th>
                    <th style="padding:6px;border:1px solid #ddd;">หมวดหมู่</th>
                    <th style="padding:6px;border:1px solid #ddd;">รายการ</th>
                    <th style="padding:6px;border:1px solid #ddd;text-align:right;">จำนวน (บาท)</th>
                    <th style="padding:6px;border:1px solid #ddd;">เลขอ้างอิง</th>
                    <th style="padding:6px;border:1px solid #ddd;">บันทึกโดย</th>
                </tr>
            </thead>
            <tbody>`;
    transactions.forEach((t, i) => {
        const isIncome = t.type === 'income';
        const amtColor = isIncome ? '#155724' : '#dc3545';
        const amtPrefix = isIncome ? '+' : '-';
        html += `<tr style="background:${i % 2 === 0 ? '#fff' : '#f8f9fa'};">
            <td style="padding:5px;border:1px solid #eee;text-align:center;">${i+1}</td>
            <td style="padding:5px;border:1px solid #eee;">${t.transaction_date}</td>
            <td style="padding:5px;border:1px solid #eee;text-align:center;">
                <span style="background:${isIncome ? '#d4edda' : '#f8d7da'};color:${amtColor};padding:2px 6px;border-radius:3px;font-size:10px;">${isIncome ? 'รายรับ' : 'รายจ่าย'}</span>
            </td>
            <td style="padding:5px;border:1px solid #eee;">${t.category_name || '-'}</td>
            <td style="padding:5px;border:1px solid #eee;font-weight:600;">${t.title || '-'}</td>
            <td style="padding:5px;border:1px solid #eee;text-align:right;color:${amtColor};font-weight:600;">${amtPrefix}${fmtNum(t.amount)}</td>
            <td style="padding:5px;border:1px solid #eee;">${t.reference_no || '-'}</td>
            <td style="padding:5px;border:1px solid #eee;">${t.creator_name || '-'}</td>
        </tr>`;
    });
    html += `</tbody></table>
        <div style="margin-top:12px;font-size:10px;color:#999;text-align:center;">จำนวนรายการทั้งหมด ${transactions.length} รายการ</div>`;
    area.innerHTML = html;

    try {
        const canvas = await html2canvas(area, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('l', 'mm', 'a4');
        const pageW = pdf.internal.pageSize.getWidth() - 20;
        const imgH = (canvas.height * pageW) / canvas.width;
        const pageH = pdf.internal.pageSize.getHeight() - 20;
        const imgData = canvas.toDataURL('image/png');

        if (imgH <= pageH) {
            pdf.addImage(imgData, 'PNG', 10, 10, pageW, imgH);
        } else {
            const totalPages = Math.ceil(imgH / pageH);
            for (let p = 0; p < totalPages; p++) {
                if (p > 0) pdf.addPage();
                const srcY = p * (canvas.height / totalPages);
                const sliceH = canvas.height / totalPages;
                const tmpCanvas = document.createElement('canvas');
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
function renderPayerAddressFinance(raw, fontSize) {
    fontSize = fontSize || '13px';
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
                return `<div style="margin-bottom:2px;"><strong>ที่อยู่</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:390px;">&nbsp;${App.escHtml(raw)}&nbsp;</span></div>`
                    + `<div style="margin-bottom:4px;"><strong>อำเภอ</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:180px;">&nbsp;-&nbsp;</span> <strong>จังหวัด</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:160px;">&nbsp;-&nbsp;</span></div>`;
            }
        } catch(e) {
            return `<div style="margin-bottom:2px;"><strong>ที่อยู่</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:390px;">&nbsp;${App.escHtml(raw)}&nbsp;</span></div>`
                + `<div style="margin-bottom:4px;"><strong>อำเภอ</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:180px;">&nbsp;-&nbsp;</span> <strong>จังหวัด</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:160px;">&nbsp;-&nbsp;</span></div>`;
        }
    }
    let html = '';
    html += `<div style="margin-bottom:2px;"><strong>ที่อยู่</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:220px;">&nbsp;${App.escHtml(detail)}&nbsp;</span>`;
    html += ` <strong>ตำบล</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:130px;">&nbsp;${App.escHtml(sub)}&nbsp;</span></div>`;
    html += `<div style="margin-bottom:4px;"><strong>อำเภอ</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:180px;">&nbsp;${App.escHtml(dist)}&nbsp;</span>`;
    html += ` <strong>จังหวัด</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:160px;">&nbsp;${App.escHtml(prov)}&nbsp;</span></div>`;
    return html;
}

// === RECEIPT PREVIEW ===
let _currentReceiptData = null;

async function viewTxnReceipt(referenceNo) {
    const body = $('#receiptPreviewBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');
    $('#receiptPreviewModal').modal('show');

    // Find receipt ID by reference_no
    const refResult = await API.findReceiptByRef(referenceNo);
    if (!refResult.success || !refResult.data) {
        body.html('<p class="text-center text-danger py-3">ไม่พบใบเสร็จสำหรับรายการนี้</p>');
        return;
    }

    const result = await API.getReceiptDetail(refResult.data.receipt_id);
    if (!result.success || !result.data) {
        body.html('<p class="text-center text-danger py-3">ไม่พบใบเสร็จ</p>');
        return;
    }

    _currentReceiptData = result.data;
    const r = _currentReceiptData;

    // Convert signature to base64
    let sigSrc = '';
    if (r.signature_mode === 'electronic' && r.signature_image) {
        if (r.signature_image.startsWith('data:')) { sigSrc = r.signature_image; }
        else { const url = r.signature_image.startsWith('http') ? r.signature_image : (BASE_PATH + r.signature_image); sigSrc = await toBase64(url).catch(() => url); }
    }

    const d = new Date(r.issued_date);
    const thM = ['มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
    const dateStr = `วันที่ ${d.getDate()} เดือน ${thM[d.getMonth()]} พ.ศ. ${d.getFullYear() + 543}`;

    body.html(`<div id="modalReceiptCanvas" style="font-family:'Sarabun',sans-serif;color:#1a3c5e;line-height:1.5;padding:30px;border:2px solid #1a3c5e;border-radius:12px;">
        <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;">
            <div>เล่มที่ ${App.escHtml(r.book_number)}</div>
            <div>เลขที่ ${r.receipt_number}</div>
        </div>
        <div style="text-align:center;margin-bottom:10px;">
            <div style="font-size:24px;font-weight:700;">ใบเสร็จรับเงิน</div>
            <div style="font-size:18px;font-weight:600;">${App.escHtml(r.organization_name)}</div>
            <div style="font-size:15px;">${App.escHtml(r.organization_address)}</div>
        </div>
        <div style="text-align:left;font-size:13px;margin-bottom:8px;padding-left:50%;">${dateStr}</div>
        <div style="margin-bottom:4px;"><strong>ได้รับเงินจาก</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:350px;">&nbsp;${App.escHtml(r.payer_name)}&nbsp;</span></div>
        ${renderPayerAddressFinance(r.payer_address, '13px')}
        <div style="margin-bottom:4px;"><strong>เป็น</strong> <span style="border-bottom:1px dotted #555;display:inline-block;min-width:410px;">&nbsp;${App.escHtml((r.description||'').replace(/\s*จำนวน\s*[\d,.]+\s*บาท/g,''))}&nbsp;</span></div>
        <div style="text-align:center;border:1px solid #1a3c5e;border-radius:8px;padding:8px;margin:15px 0;">
            <strong>จำนวน ${App.formatCurrency(r.amount)}</strong> (${App.escHtml(r.amount_text)}) ไว้ถูกต้องแล้ว
        </div>
        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
            <div style="text-align:center;">
                ${r.signature_mode === 'electronic' && sigSrc ? `<div style="margin-bottom:-25px;"><img src="${sigSrc}" alt="ลายเซ็น" style="max-height:60px;"></div>` : '<div style="margin-bottom:20px;"></div>'}
                <div>(ลงชื่อ) ................................... ผู้รับเงิน</div>
                ${r.signature_show_name === '1' && r.signature_name ? `<div style="margin-top:2px;">(${App.escHtml(r.signature_name)})</div>` : ''}
                ${r.signature_show_position === '1' ? `<div style="margin-top:1px;">${App.escHtml(r.signature_position || 'เหรัญญิก')}</div>` : ''}
            </div>
        </div>
    </div>`);
}

function toBase64(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = img.width; c.height = img.height;
            c.getContext('2d').drawImage(img, 0, 0);
            resolve(c.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url;
    });
}

async function downloadReceiptPDF() {
    const el = document.getElementById('modalReceiptCanvas');
    if (!el) return;
    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const { jsPDF } = window.jspdf;
    const pdf = new jsPDF('p', 'mm', 'a4');
    const w = pdf.internal.pageSize.getWidth() - 20;
    const h = (canvas.height * w) / canvas.width;
    pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 10, 10, w, h);
    const num = _currentReceiptData ? _currentReceiptData.receipt_number : '';
    pdf.save(`ใบเสร็จ_${num}.pdf`);
}

async function downloadReceiptPNG() {
    const el = document.getElementById('modalReceiptCanvas');
    if (!el) return;
    const canvas = await html2canvas(el, { scale: 2, useCORS: true, backgroundColor: '#ffffff' });
    const link = document.createElement('a');
    link.download = `ใบเสร็จ_${_currentReceiptData ? _currentReceiptData.receipt_number : ''}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
}

// === HELPERS ===
function formatMoney(amount) {
    return parseFloat(amount || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
