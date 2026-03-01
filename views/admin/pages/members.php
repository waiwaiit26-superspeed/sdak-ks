<?php $pageTitle = 'จัดการสมาชิก'; $page = 'members'; ?>
<?php $extraCss = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/material_blue.css">'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
<style>
@media (max-width: 768px) {
    #membersDataTable { font-size: .82rem; }
    #membersDataTable td, #membersDataTable th { padding: .4rem .5rem; }
    table.dataTable.dtr-inline.collapsed > tbody > tr > td.dtr-control::before,
    table.dataTable.dtr-inline.collapsed > tbody > tr > th.dtr-control::before {
        top: 50%; transform: translateY(-50%); background-color: var(--adminlte-primary, #7c3aed);
    }
    .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_filter { text-align: left !important; }
}
</style>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-people me-2"></i>จัดการสมาชิก</h1></div>
                <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">สมาชิก</li></ol></div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">

        <!-- Action Bar -->
        <div class="card shadow-sm mb-3">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap align-items-center justify-content-between">
                    <div class="d-flex flex-wrap mb-1" style="gap:.4rem">
                        <button class="btn btn-primary btn-sm" onclick="openAddMember()">
                            <i class="bi bi-person-plus me-1"></i> เพิ่มสมาชิก
                        </button>
                        <button class="btn btn-success btn-sm" onclick="openImportModal()">
                            <i class="bi bi-file-earmark-arrow-up me-1"></i> นำเข้าข้อมูล
                        </button>
                        <button class="btn btn-outline-info btn-sm" onclick="downloadTemplate()">
                            <i class="bi bi-download me-1"></i> ดาวน์โหลด Template
                        </button>
                    </div>
                    <div class="d-flex flex-wrap mb-1" style="gap:.4rem">
                        <select id="filterRole" class="form-control form-control-sm" style="width:auto">
                            <option value="">บทบาท: ทั้งหมด</option>
                            <option value="member" selected>สมาชิก</option>
                            <option value="admin">ผู้ดูแลระบบ</option>
                        </select>
                        <select id="filterStatus" class="form-control form-control-sm" style="width:auto">
                            <option value="">สถานะ: ทั้งหมด</option>
                            <option value="pending">รอการอนุมัติ</option>
                            <option value="active">อนุมัติแล้ว</option>
                            <option value="suspended">ระงับ</option>
                            <option value="cancelled">ถูกปฏิเสธ/ยกเลิก</option>
                        </select>
                        <select id="filterType" class="form-control form-control-sm" style="width:auto">
                            <option value="">ประเภท: ทั้งหมด</option>
                        </select>
                        <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()" title="ล้างตัวกรอง">
                            <i class="bi bi-x-circle"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="card shadow-sm">
            <div class="card-body p-0 p-md-3">
                <div class="table-responsive">
                <table id="membersDataTable" class="table table-hover table-striped" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>ชื่อ-นามสกุล</th>
                            <th>เลขสมาชิก</th>
                            <th>อีเมล</th>
                            <th>โทรศัพท์</th>
                            <th>ประเภท</th>
                            <th>ตำแหน่ง/วิทยฐานะ</th>
                            <th>โรงเรียน/สังกัด</th>
                            <th>สถานะ</th>
                            <th>วันที่สมัคร</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                </table>
                </div>
            </div>
        </div>
        </div>
    </section>
</div>

<!-- ========== Modal: Add/Edit Member ========== -->
<div class="modal fade" id="memberFormModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="memberFormTitle">เพิ่มสมาชิก</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="memberForm" novalidate>
                    <input type="hidden" id="mf_user_id" value="">

                    <!-- ข้อมูลทั่วไป -->
                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="w-auto px-2 small font-weight-bold text-primary">ข้อมูลทั่วไป</legend>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ประเภทสมาชิก <span class="text-danger">*</span></label>
                                    <select class="form-control" id="mf_member_type" name="member_type" required>
                                    </select>
                                    <small class="text-muted">วิสามัญ = เกษียณ/เปลี่ยนตำแหน่ง</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>เลขสมาชิก</label>
                                    <input type="text" class="form-control" id="mf_member_number" placeholder="เช่น 0001 (ระบบเติม prefix อัตโนมัติ)">
                                    <small class="text-muted">กรอกเฉพาะตัวเลข ระบบจะเติม prefix ให้อัตโนมัติ</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>เลขบัตรประชาชน</label>
                                    <input type="text" class="form-control" id="mf_national_id" maxlength="13" placeholder="เลข 13 หลัก">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>อีเมล</label>
                                    <input type="email" class="form-control" id="mf_email" placeholder="email@example.com">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>คำนำหน้า</label>
                                    <select class="form-control" id="mf_prefix">
                                        <option value="">-- เลือก --</option>
                                        <option>นาย</option><option>นาง</option><option>นางสาว</option>
                                        <option>ดร.</option><option>ผศ.</option><option>รศ.</option><option>ศ.</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>ชื่อ <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mf_first_name" required placeholder="กรุณากรอกคำนำหน้า">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="mf_last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>วันเกิด</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="mf_birth_date" placeholder="เลือกวันเกิด" readonly>
                                        <div class="input-group-append"><div class="input-group-text"><i class="fas fa-calendar-alt"></i></div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>มือถือ <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="mf_phone" placeholder="0812345678">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ตำแหน่ง</label>
                                    <select class="form-control" id="mf_position">
                                        <option value="">-- เลือก --</option>
                                        <option value="ผู้อำนวยการสถานศึกษา">ผู้อำนวยการสถานศึกษา</option>
                                        <option value="รองผู้อำนวยการสถานศึกษา">รองผู้อำนวยการสถานศึกษา</option>
                                        <option value="other">อื่นๆ (กรอกเอง)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3" id="mf_positionOtherWrap" style="display:none">
                                <div class="form-group">
                                    <label>ระบุตำแหน่ง</label>
                                    <input type="text" class="form-control" id="mf_position_other" placeholder="กรอกตำแหน่ง">
                                </div>
                            </div>
                            <div class="col-md-3" id="mf_academicRankWrap" style="display:none">
                                <div class="form-group">
                                    <label>วิทยฐานะ</label>
                                    <select class="form-control" id="mf_academic_rank">
                                        <option value="">-- เลือก --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- ที่อยู่ปัจจุบัน -->
                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="w-auto px-2 small font-weight-bold text-primary">ที่อยู่ปัจจุบัน</legend>
                        <div class="row">
                            <div class="col-md-2"><div class="form-group"><label>เลขที่</label><input type="text" class="form-control" id="mf_h_no"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>ซอย</label><input type="text" class="form-control" id="mf_h_soi" placeholder="ไม่มีให้กรอก -"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>หมู่ที่</label><input type="text" class="form-control" id="mf_h_moo"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>ถนน</label><input type="text" class="form-control" id="mf_h_road"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="mf_h_subdistrict"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="mf_h_district"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>จังหวัด</label><input type="text" class="form-control" id="mf_h_province"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="mf_h_postal" maxlength="5"></div></div>
                        </div>
                    </fieldset>

                    <!-- สถานที่ทำงาน -->
                    <fieldset class="border rounded p-3 mb-3">
                        <legend class="w-auto px-2 small font-weight-bold text-primary">สถานที่ทำงาน</legend>
                        <div class="row">
                            <div class="col-md-6"><div class="form-group"><label>โรงเรียน/หน่วยงาน</label>
                                <div class="input-group">
                                    <select class="form-control" id="mf_school_prefix" style="max-width:160px">
                                        <option value="โรงเรียน">โรงเรียน</option>
                                        <option value="สพม.">สพม.</option>
                                        <option value="สพป.">สพป.</option>
                                        <option value="สำนักงาน">สำนักงาน</option>
                                        <option value="">อื่นๆ (ไม่มีคำนำหน้า)</option>
                                    </select>
                                    <input type="text" class="form-control" id="mf_school" placeholder="ชื่อโรงเรียน/หน่วยงาน">
                                </div>
                            </div></div>
                            <div class="col-md-3"><div class="form-group"><label>โทรศัพท์ (โรงเรียน)</label><input type="tel" class="form-control" id="mf_work_phone"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>ตำแหน่ง/หน้าที่</label><input type="text" class="form-control" id="mf_w_position" placeholder="เช่น รอง ผอ."></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-2"><div class="form-group"><label>เลขที่</label><input type="text" class="form-control" id="mf_w_no"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>ซอย</label><input type="text" class="form-control" id="mf_w_soi" placeholder="ไม่มีให้กรอก -"></div></div>
                            <div class="col-md-2"><div class="form-group"><label>หมู่</label><input type="text" class="form-control" id="mf_w_moo"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>ถนน</label><input type="text" class="form-control" id="mf_w_road" placeholder="ไม่มีให้กรอก -"></div></div>
                            <div class="col-md-3"><div class="form-group"><label>แขวง/ตำบล</label><input type="text" class="form-control" id="mf_w_subdistrict"></div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>เขต/อำเภอ</label><input type="text" class="form-control" id="mf_w_district"></div></div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>จังหวัด</label>
                                    <input type="text" class="form-control" id="mf_w_province" placeholder="เช่น กาฬสินธุ์">
                                </div>
                            </div>
                            <div class="col-md-3"><div class="form-group"><label>รหัสไปรษณีย์</label><input type="text" class="form-control" id="mf_w_postal" maxlength="5"></div></div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>สังกัดเขตพื้นที่</label>
                                    <select class="form-control" id="mf_education_area">
                                        <option value="">-- เลือก --</option>
                                        <option>สพป.</option><option>สพม.</option><option>สพอ.</option>
                                        <option>สช.</option><option>อื่นๆ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ภาค</label>
                                    <select class="form-control" id="mf_region">
                                        <option value="">-- เลือก --</option>
                                        <option>ภาคเหนือ</option><option>ภาคกลาง</option>
                                        <option>ภาคตะวันออกเฉียงเหนือ</option><option>ภาคใต้</option>
                                        <option>ภาคตะวันออก</option><option>ภาคตะวันตก</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <!-- บัญชีผู้ใช้ -->
                    <fieldset class="border rounded p-3 mb-3" id="accountFieldset">
                        <legend class="w-auto px-2 small font-weight-bold text-primary">บัญชีผู้ใช้ (ไม่จำเป็น)</legend>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>ชื่อผู้ใช้</label><input type="text" class="form-control" id="mf_username" placeholder="ระบบจะสร้างให้อัตโนมัติ"></div></div>
                            <div class="col-md-4"><div class="form-group"><label>รหัสผ่าน</label><input type="password" class="form-control" id="mf_password" placeholder="ระบบจะสร้างให้อัตโนมัติ"></div></div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>สถานะ</label>
                                    <select class="form-control" id="mf_status">
                                        <option value="active">อนุมัติแล้ว</option>
                                        <option value="pending">รอการอนุมัติ</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveMember" onclick="saveMember()">
                    <i class="bi bi-save me-1"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== Modal: View Member ========== -->
<div class="modal fade" id="memberViewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ข้อมูลสมาชิก</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="memberViewBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer" id="memberViewFooter"></div>
        </div>
    </div>
</div>

<!-- ========== Modal: Import ========== -->
<div class="modal fade" id="importModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-1"></i> นำเข้าข้อมูลสมาชิก</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="importTabs">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tabCSV">อัปโหลดไฟล์ CSV</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tabPaste">วางข้อมูลจาก Excel/Sheet</a></li>
                </ul>
                <div class="tab-content">
                    <!-- Tab: CSV Upload -->
                    <div class="tab-pane fade show active" id="tabCSV">
                        <div class="form-group">
                            <label>เลือกไฟล์ CSV</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="csvFileInput" accept=".csv">
                                <label class="custom-file-label" for="csvFileInput">เลือกไฟล์...</label>
                            </div>
                            <small class="form-text text-muted">รองรับไฟล์ .csv (UTF-8) | <a href="javascript:void(0)" onclick="downloadTemplate()">ดาวน์โหลด Template</a></small>
                        </div>
                    </div>
                    <!-- Tab: Paste -->
                    <div class="tab-pane fade" id="tabPaste">
                        <div class="form-group">
                            <label>วางข้อมูลจาก Excel / Google Sheets</label>
                            <textarea class="form-control" id="pasteArea" rows="6" placeholder="คัดลอกข้อมูลจาก Excel/Sheets แล้ววางที่นี่ (Tab-separated)&#10;&#10;หัวคอลัมน์: เลขสมาชิก, คำนำหน้า, ชื่อ, นามสกุล, ประเภทสมาชิก, เลขบัตรประชาชน, มือถือ, อีเมล, โรงเรียน, ตำแหน่ง, วิทยฐานะ, สังกัดเขตพื้นที่, ภาค"></textarea>
                            <small class="form-text text-muted">คอลัมน์ต้องเรียงตาม Template (ดู<a href="javascript:void(0)" onclick="downloadTemplate()">ตัวอย่าง</a>)</small>
                        </div>
                        <button class="btn btn-info btn-sm" onclick="parsePasteData()"><i class="bi bi-table me-1"></i> แปลงข้อมูล</button>
                    </div>
                </div>

                <!-- Preview table -->
                <div id="importPreview" class="mt-3" style="display:none">
                    <h6><i class="bi bi-eye me-1"></i> ตัวอย่างข้อมูล (<span id="importCount">0</span> รายการ)</h6>
                    <div class="table-responsive" style="max-height:300px; overflow-y:auto">
                        <table class="table table-sm table-bordered" id="previewTable">
                            <thead class="table-light"><tr></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success" id="btnImport" onclick="doImport()" disabled>
                    <i class="bi bi-cloud-upload me-1"></i> นำเข้าข้อมูล
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========== Modal: Approve Member ========== -->
<div class="modal fade" id="approveModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle me-1"></i> อนุมัติสมาชิก</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="approveModalBody">
                <div class="text-center py-3"><span class="spinner-border"></span> กำลังตรวจสอบ...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-success" id="btnConfirmApprove" disabled>
                    <i class="bi bi-check-lg me-1"></i> ยืนยันอนุมัติ
                </button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>

<script>
let membersTable;
let importData = [];
let mfBirthFp = null;

// CSV Template columns
const TEMPLATE_HEADERS = [
    'เลขสมาชิก','คำนำหน้า','ชื่อ','นามสกุล','ประเภทสมาชิก','เลขบัตรประชาชน',
    'มือถือ','อีเมล','โรงเรียน/หน่วยงาน','ตำแหน่ง','วิทยฐานะ',
    'สังกัดเขตพื้นที่','ภาค',
    'ที่อยู่เลขที่','ซอย','หมู่ที่','ถนน','แขวง/ตำบล','เขต/อำเภอ','จังหวัด','รหัสไปรษณีย์'
];
const FIELD_MAP = [
    'member_number','prefix','first_name','last_name','member_type','national_id',
    'phone','email','school_organization','position','academic_rank',
    'education_area','region',
    'h_no','h_soi','h_moo','h_road','h_subdistrict','h_district','h_province','h_postal'
];

$(function () {
    App.requireAdmin();

    // Populate member type selects from cached data
    App.loadMemberTypes().then(() => {
        const shortLabels = App._memberTypeLabelsShort || {};
        const fullLabels  = App._memberTypeLabels || {};
        // Filter dropdown
        const $filter = $('#filterType');
        Object.entries(shortLabels).forEach(([k,v]) => {
            $filter.append(`<option value="${k}">${v}</option>`);
        });
        // Edit form dropdown
        const $edit = $('#mf_member_type');
        Object.entries(shortLabels).forEach(([k,v]) => {
            $edit.append(`<option value="${k}">${v}</option>`);
        });
    });

    initDataTable();

    // Init flatpickr for birth date
    mfBirthFp = flatpickr('#mf_birth_date', {
        locale: 'th',
        dateFormat: 'd/m/Y',
        maxDate: 'today',
        allowInput: false,
        disableMobile: true,
        formatDate: function(date) {
            const d = String(date.getDate()).padStart(2, '0');
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const y = date.getFullYear() + 543;
            return d + '/' + m + '/' + y;
        },
        parseDate: function(dateStr) {
            const parts = dateStr.split('/');
            if (parts.length === 3) {
                return new Date(parseInt(parts[2]) - 543, parseInt(parts[1]) - 1, parseInt(parts[0]));
            }
            // ISO format (from setDate)
            return new Date(dateStr);
        }
    });

    // Filters
    $('#filterRole, #filterStatus, #filterType').on('change', () => membersTable.ajax.reload());

    // CSV file
    $('#csvFileInput').on('change', handleCSVFile);
});

/* =========================================================================
   DataTable
   ========================================================================= */
function initDataTable() {
    membersTable = $('#membersDataTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 20,
        responsive: true,
        lengthMenu: [10, 20, 50, 100],
        ajax: function (data, callback) {
            const params = {
                page: Math.floor(data.start / data.length) + 1,
                per_page: data.length
            };
            if (data.search && data.search.value) params.search = data.search.value;
            const role = $('#filterRole').val();
            const status = $('#filterStatus').val();
            const type = $('#filterType').val();
            if (role) params.role = role;
            if (status) params.status = status;
            if (type) params.member_type = type;

            API.getMembers(params).then(json => {
                callback({
                    draw: data.draw,
                    recordsTotal: json.pagination ? json.pagination.total : 0,
                    recordsFiltered: json.pagination ? json.pagination.total : 0,
                    data: json.data || []
                });
            }).catch(() => callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] }));
        },
        columns: [
            {
                data: 'full_name', responsivePriority: 1,
                render: (d, t, row) => {
                    const name = App.escapeHtml(d);
                    const role = App.getRoleBadge(row.role);
                    return '<strong>' + name + '</strong><br><small class="text-muted">' + (row.username || '') + '</small> ' + role;
                }
            },
            { data: 'member_number', responsivePriority: 5, render: d => d ? '<span class="badge badge-outline-primary">' + App.escapeHtml(d) + '</span>' : '<span class="text-muted">-</span>' },
            { data: 'email', responsivePriority: 7, render: d => '<small>' + App.escapeHtml(d || '-') + '</small>' },
            { data: 'phone', responsivePriority: 6, render: d => d || '-' },
            {
                data: 'member_type', responsivePriority: 3,
                render: d => d ? App.getMemberTypeBadge(d) : '<span class="text-muted">-</span>'
            },
            {
                data: 'position', responsivePriority: 8,
                render: (d, t, row) => {
                    let html = '<small>' + App.escapeHtml(d || '-') + '</small>';
                    if (row.academic_rank) html += '<br><small class="text-primary">' + App.escapeHtml(row.academic_rank) + '</small>';
                    return html;
                }
            },
            { data: 'school_organization', responsivePriority: 9, render: d => '<small>' + App.escapeHtml(d || '-') + '</small>' },
            { data: 'status', responsivePriority: 2, render: d => App.getStatusBadge(d) },
            { data: 'created_at', responsivePriority: 9, render: d => '<small>' + App.formatDate(d) + '</small>' },
            {
                data: 'id', responsivePriority: 1,
                render: function (id, t, row) {
                    let b = '<button class="btn btn-outline-info btn-xs" onclick="viewMember(' + id + ')" title="ดู"><i class="bi bi-eye"></i></button> ';
                    b += '<button class="btn btn-outline-primary btn-xs" onclick="editMember(' + id + ')" title="แก้ไข"><i class="bi bi-pencil"></i></button> ';
                    if (row.status === 'pending') {
                        b += '<button class="btn btn-outline-success btn-xs" onclick="approveMember(' + id + ',\'approve\')" title="อนุมัติ"><i class="bi bi-check-lg"></i></button> ';
                        b += '<button class="btn btn-outline-danger btn-xs" onclick="approveMember(' + id + ',\'reject\')" title="ปฏิเสธ"><i class="bi bi-x-lg"></i></button> ';
                    }
                    if (row.status === 'active') b += '<button class="btn btn-outline-warning btn-xs" onclick="approveMember(' + id + ',\'suspend\')" title="ระงับ"><i class="bi bi-pause-circle"></i></button> ';
                    if (row.status === 'suspended' || row.status === 'cancelled') b += '<button class="btn btn-outline-success btn-xs" onclick="approveMember(' + id + ',\'activate\')" title="เปิดใช้"><i class="bi bi-play-circle"></i></button> ';
                    b += '<button class="btn btn-outline-secondary btn-xs" onclick="openResetPassword(' + id + ',\'' + App.escapeHtml(row.full_name).replace(/'/g, "\\'") + '\')" title="รีเซ็ตรหัสผ่าน"><i class="bi bi-key"></i></button> ';
                    b += '<button class="btn btn-outline-danger btn-xs" onclick="deleteMember(' + id + ',\'' + App.escapeHtml(row.full_name).replace(/'/g, "\\'") + '\')" title="ลบ"><i class="bi bi-trash"></i></button>';
                    return b;
                }
            }
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm"></span> กำลังโหลด...',
            lengthMenu: 'แสดง _MENU_ รายการ',
            zeroRecords: 'ไม่พบข้อมูลสมาชิก',
            info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
            infoEmpty: 'ไม่มีข้อมูล',
            infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
            search: '<i class="bi bi-search"></i> ค้นหา:',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
    });
}

function clearFilters() {
    $('#filterRole').val('');
    $('#filterStatus').val('');
    $('#filterType').val('');
    membersTable.search('').ajax.reload();
}

function splitSchoolPrefix(selectSel, inputSel, fullValue) {
    const prefixes = ['โรงเรียน', 'สพม.', 'สพป.', 'สำนักงาน'];
    let matched = '';
    for (const p of prefixes) {
        if (fullValue.startsWith(p)) { matched = p; break; }
    }
    $(selectSel).val(matched);
    $(inputSel).val(matched ? fullValue.substring(matched.length) : fullValue);
}

/* =========================================================================
   Academic Rank (วิทยฐานะ) - position-dependent options
   ========================================================================= */
const mfAcademicRankOptions = {
    'รองผู้อำนวยการสถานศึกษา': [
        'รองผู้อำนวยการชำนาญการ',
        'รองผู้อำนวยการชำนาญการพิเศษ',
        'รองผู้อำนวยการเชี่ยวชาญ'
    ],
    'ผู้อำนวยการสถานศึกษา': [
        'ผู้อำนวยการชำนาญการ',
        'ผู้อำนวยการชำนาญการพิเศษ',
        'ผู้อำนวยการเชี่ยวชาญ'
    ]
};

function updateMfAcademicRank(position, selectedValue) {
    const $wrap = $('#mf_academicRankWrap');
    const $select = $('#mf_academic_rank');
    const options = mfAcademicRankOptions[position];
    if (options) {
        $select.html('<option value="">-- เลือก --</option>' + options.map(o => '<option value="' + o + '"' + (o === selectedValue ? ' selected' : '') + '>' + o + '</option>').join(''));
        $wrap.slideDown(200);
    } else {
        $select.html('<option value="">-- เลือก --</option>');
        $wrap.slideUp(200);
    }
}

$('#mf_position').on('change', function () {
    const val = $(this).val();
    if (val === 'other') {
        $('#mf_positionOtherWrap').slideDown(200);
        $('#mf_position_other').focus();
    } else {
        $('#mf_positionOtherWrap').slideUp(200);
        $('#mf_position_other').val('');
    }
    updateMfAcademicRank(val);
});

/* =========================================================================
   Add / Edit Member
   ========================================================================= */
function openAddMember() {
    $('#memberForm')[0].reset();
    $('#mf_user_id').val('');
    $('#mf_school_prefix').val('โรงเรียน');
    if (mfBirthFp) mfBirthFp.clear();
    $('#mf_positionOtherWrap').hide();
    $('#mf_academicRankWrap').hide();
    $('#memberFormTitle').text('เพิ่มสมาชิก');
    $('#accountFieldset').show();
    $('#memberFormModal').modal('show');
}

async function editMember(id) {
    const result = await API.getProfile(id);
    if (!result.success) return App.error('ไม่สามารถโหลดข้อมูลได้');
    const u = result.data;

    $('#memberForm')[0].reset();
    $('#mf_user_id').val(u.id);
    $('#memberFormTitle').text('แก้ไขข้อมูลสมาชิก');
    $('#accountFieldset').hide();

    // General info
    $('#mf_member_type').val(u.member_type || 'ordinary');
    $('#mf_member_number').val(u.member_number || '');
    $('#mf_national_id').val(u.national_id || '');
    $('#mf_email').val(u.email || '');
    $('#mf_prefix').val(u.prefix || '');
    $('#mf_first_name').val(u.first_name || u.full_name || '');
    $('#mf_last_name').val(u.last_name || '');
    $('#mf_phone').val(u.phone || '');
    $('#mf_position').val(u.position || '');
    // Position: check if known or other
    const mfKnownPositions = ['ผู้อำนวยการสถานศึกษา', 'รองผู้อำนวยการสถานศึกษา'];
    if (u.position && mfKnownPositions.includes(u.position)) {
        $('#mf_position').val(u.position);
    } else if (u.position) {
        $('#mf_position').val('other');
        $('#mf_position_other').val(u.position);
        $('#mf_positionOtherWrap').show();
    }
    updateMfAcademicRank(u.position || '', u.academic_rank || '');
    $('#mf_school').val(u.school_organization || '');
    splitSchoolPrefix('#mf_school_prefix', '#mf_school', u.school_organization || '');
    $('#mf_work_phone').val(u.work_phone || '');
    $('#mf_education_area').val(u.education_area || '');
    $('#mf_region').val(u.region || '');

    // Birth date
    if (u.birth_date) {
        if (mfBirthFp) {
            mfBirthFp.setDate(u.birth_date, true);
        }
    } else {
        if (mfBirthFp) mfBirthFp.clear();
    }

    // Home address
    let ha = u.home_address || {};
    if (typeof ha === 'string') { try { ha = JSON.parse(ha); } catch(e) { ha = {}; } }
    $('#mf_h_no').val(ha.no || '');
    $('#mf_h_soi').val(ha.soi || '');
    $('#mf_h_moo').val(ha.moo || '');
    $('#mf_h_road').val(ha.road || '');
    $('#mf_h_subdistrict').val(ha.subdistrict || '');
    $('#mf_h_district').val(ha.district || '');
    $('#mf_h_province').val(ha.province || '');
    $('#mf_h_postal').val(ha.postal_code || '');

    // Work address
    let wa = u.work_address || {};
    if (typeof wa === 'string') { try { wa = JSON.parse(wa); } catch(e) { wa = {}; } }
    $('#mf_w_no').val(wa.no || '');
    $('#mf_w_soi').val(wa.soi || '');
    $('#mf_w_moo').val(wa.moo || '');
    $('#mf_w_road').val(wa.road || '');
    $('#mf_w_subdistrict').val(wa.subdistrict || '');
    $('#mf_w_district').val(wa.district || '');
    $('#mf_w_province').val(wa.province || '');
    $('#mf_w_postal').val(wa.postal_code || '');

    $('#memberFormModal').modal('show');
}

async function saveMember() {
    const firstName = $('#mf_first_name').val().trim();
    const lastName = $('#mf_last_name').val().trim();
    if (!firstName) return App.error('กรุณากรอกชื่อ');

    const btn = $('#btnSaveMember');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');

    // Compose birth_date
    let birthDate = null;
    if (mfBirthFp && mfBirthFp.selectedDates.length > 0) {
        const d = mfBirthFp.selectedDates[0];
        birthDate = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }

    const data = {
        prefix: $('#mf_prefix').val(),
        first_name: firstName,
        last_name: lastName,
        member_type: $('#mf_member_type').val(),
        member_number: $('#mf_member_number').val().trim(),
        national_id: $('#mf_national_id').val().trim(),
        email: $('#mf_email').val().trim(),
        phone: $('#mf_phone').val().trim(),
        position: (function() {
            const v = $('#mf_position').val();
            if (v === 'other') return $('#mf_position_other').val().trim();
            return v || $('#mf_w_position').val().trim();
        })(),
        academic_rank: $('#mf_academic_rank').val() || '',
        school_organization: (function() {
            const p = $('#mf_school_prefix').val();
            const n = $('#mf_school').val().trim();
            return p ? p + n : n;
        })(),
        work_phone: $('#mf_work_phone').val().trim(),
        education_area: $('#mf_education_area').val(),
        region: $('#mf_region').val(),
        birth_date: birthDate,
        home_address: {
            no: $('#mf_h_no').val(), soi: $('#mf_h_soi').val(), moo: $('#mf_h_moo').val(),
            road: $('#mf_h_road').val(), subdistrict: $('#mf_h_subdistrict').val(),
            district: $('#mf_h_district').val(), province: $('#mf_h_province').val(),
            postal_code: $('#mf_h_postal').val()
        },
        work_address: {
            no: $('#mf_w_no').val(), soi: $('#mf_w_soi').val(), moo: $('#mf_w_moo').val(),
            road: $('#mf_w_road').val(), subdistrict: $('#mf_w_subdistrict').val(),
            district: $('#mf_w_district').val(), province: $('#mf_w_province').val(),
            postal_code: $('#mf_w_postal').val()
        }
    };

    const userId = $('#mf_user_id').val();
    let result;

    if (userId) {
        // Edit
        data.user_id = userId;
        result = await API.updateProfile(data);
    } else {
        // Create
        data.username = $('#mf_username').val().trim();
        data.password = $('#mf_password').val();
        data.status = $('#mf_status').val();
        result = await API.createMember(data);
    }

    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> บันทึก');

    if (result.success) {
        App.success(result.message);
        $('#memberFormModal').modal('hide');
        membersTable.ajax.reload(null, false);
    } else {
        App.error(result.message);
    }
}

/* =========================================================================
   View Member Detail
   ========================================================================= */
async function viewMember(id) {
    $('#memberViewModal').modal('show');
    const body = $('#memberViewBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');

    const result = await API.getProfile(id);
    if (!result.success) { body.html('<p class="text-danger">ไม่สามารถโหลดข้อมูลได้</p>'); return; }
    const u = result.data;
    let ha = u.home_address || {};
    if (typeof ha === 'string') { try { ha = JSON.parse(ha); } catch(e) { ha = {}; } }
    let wa = u.work_address || {};
    if (typeof wa === 'string') { try { wa = JSON.parse(wa); } catch(e) { wa = {}; } }

    const formatAddr = (a) => {
        if (!a || !Object.values(a).some(v => v)) return '-';
        return [a.no ? 'เลขที่ ' + a.no : '', a.soi && a.soi !== '-' ? 'ซอย ' + a.soi : '',
                a.moo ? 'หมู่ ' + a.moo : '', a.road && a.road !== '-' ? 'ถ.' + a.road : '',
                a.subdistrict ? 'ต.' + a.subdistrict : '', a.district ? 'อ.' + a.district : '',
                a.province ? 'จ.' + a.province : '', a.postal_code || ''].filter(Boolean).join(' ');
    };

    const displayName = (u.prefix || '') + (u.first_name && u.last_name ? u.first_name + ' ' + u.last_name : u.full_name);

    body.html(
        '<div class="row">' +
            '<div class="col-md-4 text-center mb-3">' +
                '<img src="' + (u.profile_image ? App.imgUrl(u.profile_image) : '../assets/images/default-avatar.png') + '" class="rounded-circle mb-2" width="100" height="100" style="object-fit:cover">' +
                '<h5>' + App.escapeHtml(displayName) + '</h5>' +
                App.getRoleBadge(u.role) + ' ' + (u.member_type ? App.getMemberTypeBadge(u.member_type) : '') + ' ' + App.getStatusBadge(u.status) +
                '<div class="mt-2"><button class="btn btn-sm btn-outline-primary" onclick="$(\'#memberViewModal\').modal(\'hide\');editMember(' + u.id + ')"><i class="bi bi-pencil"></i> แก้ไข</button></div>' +
            '</div>' +
            '<div class="col-md-8">' +
                '<table class="table table-sm table-bordered mb-2">' +
                    '<tr><th class="bg-light" colspan="4">ข้อมูลทั่วไป</th></tr>' +
                    '<tr><td class="text-muted" width="130">เลขสมาชิก</td><td><strong class="text-primary">' + (u.member_number || '<span class="text-muted">ยังไม่กำหนด</span>') + '</strong></td><td class="text-muted" width="130">อีเมล</td><td>' + (u.email || '-') + '</td></tr>' +
                    '<tr><td class="text-muted">ชื่อผู้ใช้</td><td>' + (u.username || '-') + '</td><td class="text-muted">เลขบัตรประชาชน</td><td>' + (u.national_id || '-') + '</td></tr>' +
                    '<tr><td class="text-muted">วันเกิด</td><td>' + (u.birth_date ? App.formatDate(u.birth_date) : '-') + '</td><td class="text-muted">มือถือ</td><td>' + (u.phone || '-') + '</td></tr>' +
                    '<tr><td class="text-muted">ตำแหน่ง</td><td>' + (u.position || '-') + '</td><td class="text-muted">วิทยฐานะ</td><td>' + (u.academic_rank || '-') + '</td></tr>' +
                    '<tr><td class="text-muted">โรงเรียน</td><td colspan="3">' + (u.school_organization || '-') + '</td></tr>' +
                    '<tr><td class="text-muted">โทรศัพท์ (ร.ร.)</td><td>' + (u.work_phone || '-') + '</td><td class="text-muted">สังกัด</td><td>' + (u.education_area || '-') + ' ' + (u.region || '') + '</td></tr>' +
                    '<tr><th class="bg-light" colspan="4">ที่อยู่ปัจจุบัน</th></tr>' +
                    '<tr><td class="text-muted">ที่อยู่</td><td colspan="3">' + formatAddr(ha) + '</td></tr>' +
                    '<tr><th class="bg-light" colspan="4">ที่อยู่สถานที่ทำงาน</th></tr>' +
                    '<tr><td class="text-muted">ที่อยู่</td><td colspan="3">' + formatAddr(wa) + '</td></tr>' +
                    '<tr><td class="text-muted">วันที่สมัคร</td><td colspan="3">' + App.formatDateTime(u.created_at) + '</td></tr>' +
                    '<tr><td class="text-muted">วันเริ่มเป็นสมาชิก</td><td colspan="3">' + (u.approved_at ? App.formatDateTime(u.approved_at) : '<span class="text-muted">รออนุมัติ</span>') + '</td></tr>' +
                '</table>' +
            '</div>' +
        '</div>'
    );

    // Footer actions
    let footer = '<button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>';
    if (u.status === 'pending') {
        footer = '<button class="btn btn-success" onclick="$(\'#memberViewModal\').modal(\'hide\');approveMember(' + u.id + ',\'approve\')"><i class="bi bi-check-lg me-1"></i>อนุมัติ</button> ' +
                 '<button class="btn btn-danger" onclick="$(\'#memberViewModal\').modal(\'hide\');approveMember(' + u.id + ',\'reject\')"><i class="bi bi-x-lg me-1"></i>ปฏิเสธ</button> ' + footer;
    }
    if (u.status === 'active') footer = '<button class="btn btn-warning" onclick="$(\'#memberViewModal\').modal(\'hide\');approveMember(' + u.id + ',\'suspend\')"><i class="bi bi-pause-circle me-1"></i>ระงับ</button> ' + footer;
    if (u.status === 'suspended' || u.status === 'cancelled') footer = '<button class="btn btn-success" onclick="$(\'#memberViewModal\').modal(\'hide\');approveMember(' + u.id + ',\'activate\')"><i class="bi bi-play-circle me-1"></i>เปิดใช้</button> ' + footer;
    $('#memberViewFooter').html(footer);
}

/* =========================================================================
   Approve / Reject / Suspend / Activate
   ========================================================================= */
let pendingApproveUserId = null;

async function approveMember(userId, action) {
    const labels = { approve: 'อนุมัติ', reject: 'ปฏิเสธ', suspend: 'ระงับ', activate: 'เปิดใช้งาน', cancel: 'ยกเลิก' };
    let reason = '';

    // For approve action — show full approval modal with fee check + member number
    if (action === 'approve') {
        pendingApproveUserId = userId;
        const body = $('#approveModalBody');
        body.html('<div class="text-center py-3"><span class="spinner-border"></span> กำลังตรวจสอบ...</div>');
        $('#btnConfirmApprove').prop('disabled', true);
        $('#approveModal').modal('show');

        // Fetch fee status + member info
        const [profileResult, feeResult] = await Promise.all([
            API.getProfile(userId),
            API.checkFeeStatus(userId)
        ]);

        if (!profileResult.success || !feeResult.success) {
            body.html('<div class="alert alert-danger">ไม่สามารถโหลดข้อมูลได้</div>');
            return;
        }

        const u = profileResult.data;
        const f = feeResult.data;
        const displayName = (u.prefix || '') + (u.first_name && u.last_name ? u.first_name + ' ' + u.last_name : u.full_name);
        const typeLabels = App._memberTypeLabelsShort || { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
        const feeLabels = { none: 'ไม่ต้องชำระ', onetime: 'จ่ายครั้งเดียว', annual: 'จ่ายรายปี' };

        let feeStatusHtml = '';
        if (f.requires_fee) {
            const amountText = f.fee_amount ? number_format(f.fee_amount) + ' บาท' : '-';
            const feeTypeText = f.fee_mode === 'onetime' ? 'ชำระครั้งเดียว' : 'รายปี';

            if (f.fee_approved) {
                feeStatusHtml = '<div class="alert alert-success py-2 mb-3">' +
                    '<i class="bi bi-check-circle me-1"></i> ค่าธรรมเนียม <strong>' + amountText + '</strong> (' + feeTypeText + ') — ได้รับอนุมัติแล้ว</div>';
            } else {
                // Show fee amount card + confirm payment button
                const feeStatusLabel = f.has_fee_record
                    ? (f.fee_status === 'paid' ? '<span class="badge badge-info">ชำระแล้ว รออนุมัติ</span>' : '<span class="badge badge-warning">รอชำระ</span>')
                    : '<span class="badge badge-secondary">ยังไม่มีรายการ</span>';
                const slipHtml = f.fee_payment_slip
                    ? '<br><a href="' + App.imgUrl(f.fee_payment_slip) + '" target="_blank" class="text-primary"><i class="bi bi-image me-1"></i>ดูหลักฐานการชำระ</a>'
                    : '';

                feeStatusHtml = '<div class="card border-warning mb-3">' +
                    '<div class="card-body py-2 px-3">' +
                        '<div class="d-flex justify-content-between align-items-center mb-2">' +
                            '<div>' +
                                '<i class="bi bi-cash-coin text-warning me-1"></i> <strong>ค่าธรรมเนียม</strong> ' + feeStatusLabel +
                            '</div>' +
                            '<span class="h5 mb-0 text-primary">' + amountText + '</span>' +
                        '</div>' +
                        '<small class="text-muted">ประเภท: ' + feeTypeText + '</small>' + slipHtml +
                        '<hr class="my-2">' +
                        '<div class="text-center">' +
                            '<button type="button" class="btn btn-warning btn-sm" id="btnConfirmFeePayment" onclick="confirmFeePayment(' + userId + ')">' +
                                '<i class="bi bi-check2-circle me-1"></i> ยืนยันว่าจ่ายเงินแล้ว (' + amountText + ')' +
                            '</button>' +
                        '</div>' +
                        '<div class="custom-control custom-checkbox mt-2 text-center">' +
                            '<input type="checkbox" class="custom-control-input" id="chkIssueReceipt">' +
                            '<label class="custom-control-label" for="chkIssueReceipt">ออกใบเสร็จรับเงินด้วย</label>' +
                        '</div>' +
                        '<small class="d-block text-center text-muted mt-1">กดเพื่อยืนยันว่าสมาชิกชำระค่าธรรมเนียมแล้ว</small>' +
                    '</div>' +
                '</div>';
            }
        } else {
            feeStatusHtml = '<div class="alert alert-info py-2 mb-3"><i class="bi bi-info-circle me-1"></i> ประเภทนี้ไม่ต้องชำระค่าธรรมเนียม</div>';
        }

        const canApprove = !f.requires_fee || f.fee_approved;

        body.html(
            '<div class="mb-3">' +
                '<table class="table table-sm table-bordered mb-0">' +
                    '<tr><td class="text-muted" width="120">ชื่อ-นามสกุล</td><td><strong>' + App.escapeHtml(displayName) + '</strong></td></tr>' +
                    '<tr><td class="text-muted">ประเภทสมาชิก</td><td>' + App.getMemberTypeBadge(u.member_type) + ' (' + (typeLabels[u.member_type] || u.member_type) + ')</td></tr>' +
                    '<tr><td class="text-muted">การชำระเงิน</td><td>' + (feeLabels[f.fee_mode] || f.fee_mode) + '</td></tr>' +
                '</table>' +
            '</div>' +
            feeStatusHtml +
            (canApprove ? '<div class="form-group">' +
                '<label><strong>เลขสมาชิก</strong> <span class="text-danger">*</span></label>' +
                '<div class="input-group">' +
                    '<div class="input-group-prepend"><span class="input-group-text">' + App.escapeHtml(f.member_number_prefix || '') + '</span></div>' +
                    '<input type="text" class="form-control" id="approveMemberNumber" value="' + App.escapeHtml(f.next_member_number || '') + '" data-digits="' + (f.member_number_digits || 4) + '" placeholder="เลขสมาชิก (ตัวเลขเท่านั้น)">' +
                    '<div class="input-group-append">' +
                        '<button class="btn btn-outline-secondary" type="button" onclick="adjustMemberNumber(-1)" title="ลด"><i class="bi bi-dash"></i></button>' +
                        '<button class="btn btn-outline-secondary" type="button" onclick="adjustMemberNumber(1)" title="เพิ่ม"><i class="bi bi-plus"></i></button>' +
                    '</div>' +
                '</div>' +
                '<small class="text-muted">กรอกเฉพาะตัวเลข ระบบจะเติม prefix อัตโนมัติ (เลขสมาชิกห้ามซ้ำ)</small>' +
            '</div>' : '')
        );

        $('#btnConfirmApprove').prop('disabled', !canApprove);
        return;
    }

    // For reject, suspend, cancel — prompt for reason
    if (['reject', 'suspend', 'cancel'].includes(action)) {
        const { value, isConfirmed } = await Swal.fire({
            title: labels[action] + 'สมาชิก',
            input: 'textarea', inputLabel: 'เหตุผล', inputPlaceholder: 'กรุณาระบุเหตุผล...',
            showCancelButton: true, confirmButtonText: 'ยืนยัน' + labels[action], cancelButtonText: 'ยกเลิก',
            confirmButtonColor: action === 'reject' || action === 'cancel' ? '#dc3545' : '#ffc107',
            inputValidator: function(v) { if (!v || !v.trim()) return 'กรุณาระบุเหตุผล'; }
        });
        if (!isConfirmed) return;
        reason = value;
    } else {
        if (!await App.confirm('ต้องการ' + labels[action] + 'สมาชิกนี้หรือไม่?')) return;
    }

    const result = await API.approveMember(userId, action, reason);
    if (result.success) { App.success(result.message); membersTable.ajax.reload(null, false); }
    else App.error(result.message);
}

function number_format(n) {
    return parseFloat(n || 0).toLocaleString('th-TH', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
}

async function confirmFeePayment(userId) {
    const btn = $('#btnConfirmFeePayment');
    const ok = await Swal.fire({
        title: 'ยืนยันว่าจ่ายเงินแล้ว?',
        html: 'ระบบจะบันทึกว่าสมาชิกชำระค่าธรรมเนียมแล้ว<br>และออกใบเสร็จอัตโนมัติ',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#28a745'
    });
    if (!ok.isConfirmed) return;

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

    const issueReceipt = $('#chkIssueReceipt').is(':checked');
    const result = await API.confirmFeePayment(userId, issueReceipt);
    if (result.success) {
        App.success(result.message);
        // Refresh the approval modal to show updated fee status
        approveMember(pendingApproveUserId, 'approve');
    } else {
        App.error(result.message);
        btn.prop('disabled', false).html('<i class="bi bi-check2-circle me-1"></i> ยืนยันว่าจ่ายเงินแล้ว');
    }
}

function adjustMemberNumber(delta) {
    const input = $('#approveMemberNumber');
    const val = input.val().replace(/[^0-9]/g, '');
    let num = (parseInt(val) || 0) + delta;
    if (num < 1) num = 1;
    const digits = parseInt(input.data('digits')) || 4;
    input.val(String(num).padStart(digits, '0'));
}

$('#btnConfirmApprove').on('click', async function () {
    const btn = $(this);
    const memberNumber = ($('#approveMemberNumber').val() || '').trim();

    if (!memberNumber) {
        App.error('กรุณาระบุเลขสมาชิก');
        return;
    }

    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

    const result = await API.approveMember(pendingApproveUserId, 'approve', '', memberNumber);
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> ยืนยันอนุมัติ');

    if (result.success) {
        App.success(result.message);
        $('#approveModal').modal('hide');
        membersTable.ajax.reload(null, false);
    } else {
        App.error(result.message);
    }
});

/* =========================================================================
   Delete Member
   ========================================================================= */
async function deleteMember(userId, name) {
    const ok = await Swal.fire({
        title: 'ยืนยันการลบ?',
        html: 'ต้องการลบสมาชิก <strong>' + name + '</strong> หรือไม่?<br><small class="text-danger">การลบจะไม่สามารถกู้คืนได้</small>',
        icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545',
        confirmButtonText: 'ลบ', cancelButtonText: 'ยกเลิก'
    });
    if (!ok.isConfirmed) return;

    const result = await API.deleteMember(userId);
    if (result.success) { App.success(result.message); membersTable.ajax.reload(null, false); }
    else App.error(result.message);
}

/* =========================================================================
   CSV Import
   ========================================================================= */
function openImportModal() {
    importData = [];
    $('#csvFileInput').val('');
    $('.custom-file-label').text('เลือกไฟล์...');
    $('#pasteArea').val('');
    $('#importPreview').hide();
    $('#btnImport').prop('disabled', true);
    $('#importModal').modal('show');
}

function downloadTemplate() {
    const BOM = '\uFEFF';
    const csv = BOM + TEMPLATE_HEADERS.join(',') + '\n' +
        'SDAK-0001,นาย,สมชาย,ใจดี,สามัญ,1234567890123,0812345678,somchai@email.com,กาฬสินธุ์พิทยาสรรพ์,รองผู้อำนวยการ,สพป.,ภาคตะวันออกเฉียงเหนือ,123,ซอยสุข,5,ถนนมิตรภาพ,ในเมือง,เมืองกาฬสินธุ์,กาฬสินธุ์,46000\n' +
        '0002,นาง,สมใจ,รักดี,สามัญ,9876543210123,0898765432,somjai@email.com,กาฬสินธุ์พิทยาสรรพ์,ครู,สพป.,ภาคตะวันออกเฉียงเหนือ,,,,,,เมืองกาฬสินธุ์,กาฬสินธุ์,46000\n';
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'template_import_members.csv';
    link.click();
}

function handleCSVFile(e) {
    const file = e.target.files[0];
    if (!file) return;
    $('.custom-file-label').text(file.name);

    const reader = new FileReader();
    reader.onload = function (evt) {
        parseCSVText(evt.target.result);
    };
    reader.readAsText(file, 'UTF-8');
}

function parseCSVText(text) {
    const lines = text.trim().split(/\r?\n/);
    if (lines.length < 2) return App.error('ไฟล์ไม่มีข้อมูล');

    importData = [];
    for (let i = 1; i < lines.length; i++) {
        const cols = parseCSVLine(lines[i]);
        if (cols.length < 3 || !cols[1].trim()) continue;
        const m = {};
        FIELD_MAP.forEach((field, idx) => { m[field] = (cols[idx] || '').trim(); });
        importData.push(m);
    }
    showPreview();
}

function parseCSVLine(line) {
    const result = [];
    let current = '', inQuotes = false;
    for (let i = 0; i < line.length; i++) {
        const ch = line[i];
        if (inQuotes) {
            if (ch === '"' && line[i + 1] === '"') { current += '"'; i++; }
            else if (ch === '"') inQuotes = false;
            else current += ch;
        } else {
            if (ch === '"') inQuotes = true;
            else if (ch === ',') { result.push(current); current = ''; }
            else current += ch;
        }
    }
    result.push(current);
    return result;
}

function parsePasteData() {
    const text = $('#pasteArea').val().trim();
    if (!text) return App.error('กรุณาวางข้อมูล');

    const lines = text.split(/\r?\n/);
    importData = [];

    // Check if first line is header
    const firstLine = lines[0].split('\t');
    let startIdx = 0;
    if (firstLine[0].match(/เลขสมาชิก|คำนำหน้า|prefix|หัว|member/i)) startIdx = 1;

    for (let i = startIdx; i < lines.length; i++) {
        const cols = lines[i].split('\t');
        if (cols.length < 3 || !cols[1].trim()) continue;
        const m = {};
        FIELD_MAP.forEach((field, idx) => { m[field] = (cols[idx] || '').trim(); });
        importData.push(m);
    }
    showPreview();
}

function showPreview() {
    if (importData.length === 0) return App.error('ไม่พบข้อมูลที่สามารถนำเข้าได้');

    $('#importCount').text(importData.length);

    // Show first 12 columns only
    const showHeaders = TEMPLATE_HEADERS.slice(0, 13);
    const showFields = FIELD_MAP.slice(0, 13);

    let thead = '<tr>' + showHeaders.map(function(h) { return '<th class="small">' + h + '</th>'; }).join('') + '</tr>';
    let tbody = '';
    importData.slice(0, 20).forEach(function(m) {
        tbody += '<tr>' + showFields.map(function(f) { return '<td class="small">' + App.escapeHtml(m[f] || '') + '</td>'; }).join('') + '</tr>';
    });
    if (importData.length > 20) tbody += '<tr><td colspan="' + showHeaders.length + '" class="text-center text-muted">... และอีก ' + (importData.length - 20) + ' รายการ</td></tr>';

    $('#previewTable thead').html(thead);
    $('#previewTable tbody').html(tbody);
    $('#importPreview').show();
    $('#btnImport').prop('disabled', false);
}

async function doImport() {
    if (importData.length === 0) return;

    if (!await App.confirm('ต้องการนำเข้าสมาชิก ' + importData.length + ' รายการ หรือไม่?')) return;

    const btn = $('#btnImport');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังนำเข้า...');

    // Build members array for API
    const members = importData.map(function(m) {
        const entry = {
            member_number: m.member_number,
            prefix: m.prefix,
            first_name: m.first_name,
            last_name: m.last_name,
            member_type: m.member_type,
            national_id: m.national_id,
            phone: m.phone,
            email: m.email,
            school_organization: m.school_organization,
            position: m.position,
            academic_rank: m.academic_rank,
            education_area: m.education_area,
            region: m.region
        };
        if (m.h_no || m.h_province) {
            entry.home_address = {
                no: m.h_no, soi: m.h_soi, moo: m.h_moo, road: m.h_road,
                subdistrict: m.h_subdistrict, district: m.h_district,
                province: m.h_province, postal_code: m.h_postal
            };
        }
        return entry;
    });

    const result = await API.importMembers(members);
    btn.prop('disabled', false).html('<i class="bi bi-cloud-upload me-1"></i> นำเข้าข้อมูล');

    if (result.success) {
        let msg = result.message;
        if (result.data && result.data.errors && result.data.errors.length > 0) {
            msg += '<br><br><strong>รายการที่ไม่สำเร็จ:</strong><br>' +
                result.data.errors.slice(0, 10).map(function(e) { return '<small class="text-danger">• ' + App.escapeHtml(e) + '</small>'; }).join('<br>');
        }
        Swal.fire({ icon: result.data.failed_count > 0 ? 'warning' : 'success', title: 'ผลการนำเข้า', html: msg });
        $('#importModal').modal('hide');
        membersTable.ajax.reload();
    } else {
        App.error(result.message);
    }
}
</script>

<!-- ========== Modal: Reset Password ========== -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-key-fill me-2"></i>รีเซ็ตรหัสผ่าน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="rpUserId">
                <div class="mb-3">
                    <label class="form-label fw-bold">สมาชิก:</label>
                    <span id="rpMemberName" class="text-primary fw-bold"></span>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">รหัสผ่านใหม่</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="rpPassword" placeholder="กรอกรหัสผ่านใหม่ (อย่างน้อย 6 ตัว)" style="font-family:monospace;font-size:1.1rem;letter-spacing:1px;">
                        <button class="btn btn-outline-info" type="button" onclick="randomPassword()" title="สุ่มรหัสผ่าน">
                            <i class="bi bi-shuffle"></i> สุ่ม
                        </button>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()" title="คัดลอก">
                            <i class="bi bi-clipboard" id="rpCopyIcon"></i>
                        </button>
                    </div>
                    <small class="text-muted">กดปุ่ม "สุ่ม" เพื่อสร้างรหัสผ่านอัตโนมัติ หรือพิมพ์เอง</small>
                </div>
                <div id="rpCopiedAlert" class="alert alert-success py-2 d-none">
                    <i class="bi bi-check-circle me-1"></i> คัดลอกรหัสผ่านแล้ว: <code id="rpCopiedText"></code>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-warning" id="btnResetPassword" onclick="submitResetPassword()">
                    <i class="bi bi-check-lg me-1"></i>รีเซ็ตรหัสผ่าน
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.btn-xs { padding: 2px 6px; font-size: 12px; }
#membersDataTable_wrapper .dataTables_filter input { border: 1px solid #ced4da; border-radius: 4px; padding: 4px 8px; }
#membersDataTable_wrapper .dataTables_length select { border: 1px solid #ced4da; border-radius: 4px; }
div.dataTables_processing { background: rgba(255,255,255,0.9) !important; border: none !important; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 8px; }
fieldset legend { font-size: 0.9rem; }
</style>

<script>
// ─── Reset Password Functions ───
function openResetPassword(userId, memberName) {
    $('#rpUserId').val(userId);
    $('#rpMemberName').text(memberName);
    $('#rpPassword').val('');
    $('#rpCopiedAlert').addClass('d-none');
    randomPassword();
    $('#resetPasswordModal').modal('show');
}

function randomPassword() {
    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
    const lower = 'abcdefghjkmnpqrstuvwxyz';
    const digits = '23456789';
    const special = '!@#$%&*';
    let pw = '';
    // Ensure at least one of each type
    pw += upper.charAt(Math.floor(Math.random() * upper.length));
    pw += lower.charAt(Math.floor(Math.random() * lower.length));
    pw += digits.charAt(Math.floor(Math.random() * digits.length));
    pw += special.charAt(Math.floor(Math.random() * special.length));
    // Fill remaining
    const all = upper + lower + digits + special;
    for (let i = 0; i < 6; i++) pw += all.charAt(Math.floor(Math.random() * all.length));
    // Shuffle
    pw = pw.split('').sort(() => Math.random() - 0.5).join('');
    $('#rpPassword').val(pw).css('color', '#e67e22');
    setTimeout(() => $('#rpPassword').css('color', ''), 600);
    $('#rpCopiedAlert').addClass('d-none');
}

function copyPassword() {
    const pw = $('#rpPassword').val();
    if (!pw) { App.error('ยังไม่มีรหัสผ่าน'); return; }
    navigator.clipboard.writeText(pw).then(() => {
        $('#rpCopiedText').text(pw);
        $('#rpCopiedAlert').removeClass('d-none');
        const icon = $('#rpCopyIcon');
        icon.removeClass('bi-clipboard').addClass('bi-clipboard-check text-success');
        setTimeout(() => icon.removeClass('bi-clipboard-check text-success').addClass('bi-clipboard'), 2000);
        App.success('คัดลอกรหัสผ่านแล้ว');
    }).catch(() => {
        // Fallback
        const tmp = $('<input>').val(pw).appendTo('body').select();
        document.execCommand('copy');
        tmp.remove();
        $('#rpCopiedText').text(pw);
        $('#rpCopiedAlert').removeClass('d-none');
        App.success('คัดลอกรหัสผ่านแล้ว');
    });
}

async function submitResetPassword() {
    const userId = $('#rpUserId').val();
    const password = $('#rpPassword').val().trim();
    if (!password || password.length < 6) {
        App.error('กรุณากรอกรหัสผ่านอย่างน้อย 6 ตัวอักษร');
        return;
    }
    const btn = $('#btnResetPassword');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

    const result = await API.post(API.apiUrl('member', 'admin-reset-password'), {
        user_id: userId,
        password: password
    });

    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>รีเซ็ตรหัสผ่าน');

    if (result.success) {
        App.success(result.message || 'รีเซ็ตรหัสผ่านสำเร็จ');
        // Keep modal open so admin can copy the password
    } else {
        App.error(result.message || 'เกิดข้อผิดพลาด');
    }
}
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
