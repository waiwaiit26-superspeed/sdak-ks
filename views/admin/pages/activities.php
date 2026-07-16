<?php $pageTitle = 'จัดการกิจกรรม'; $page = 'activities'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<style>
#externalParticipantModal .receipt-address-shell,
#regManageModal .receipt-address-shell {
    border: 1px solid #d8dee6;
    border-radius: 14px;
    overflow: hidden;
}

#externalParticipantModal .receipt-address-shell .card-header,
#regManageModal .receipt-address-shell .card-header {
    background: linear-gradient(135deg, #f7fafc 0%, #eef3f8 100%);
    border-bottom: 1px solid #d8dee6;
}

.address-unified-card {
    border: 1px solid #dbe2ea;
    border-radius: 12px;
    padding: 12px;
    background: #fbfdff;
}

.address-unified-card .form-label {
    font-weight: 600;
    color: #2f3e4d;
}

.address-unified-card .input-group {
    border-radius: 10px;
    overflow: hidden;
}

.address-unified-card .input-group-text {
    min-width: 96px;
    justify-content: center;
    font-weight: 600;
    color: #495869;
    background: #edf2f7;
    border-color: #cfd8e3;
    border-right: 0;
}

.address-unified-card .input-group .form-control {
    border-left: 0;
}

.address-unified-card .form-control {
    border-color: #cfd8e3;
}
</style>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-calendar-event me-2"></i>จัดการกิจกรรม</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">กิจกรรม</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" onclick="openActivityForm()">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มกิจกรรม
                </button>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="open">เปิดรับสมัคร</option>
                                <option value="closed">ปิดรับสมัคร</option>
                                <option value="draft">แบบร่าง</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="searchActivity" class="form-control form-control-sm" placeholder="ค้นหากิจกรรม...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="loadActivities(1)"><i class="bi bi-search"></i> ค้นหา</button>
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
                                    <th>#</th>
                                    <th>ชื่อกิจกรรม</th>
                                    <th>วันจัดกิจกรรม</th>
                                    <th>วันที่</th>
                                    <th>สถานที่</th>
                                    <th>ค่าลงทะเบียน</th>
                                    <th>ผู้เข้าร่วม</th>
                                    <th>การเข้าถึง</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="activitiesTable">
                                <tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><nav id="activityPagination"></nav></div>
            </div>
        </div>
    </div>

<!-- Modal: Activity Form -->
<div class="modal fade" id="activityFormModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="actFormTitle">เพิ่มกิจกรรม</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="activityForm" novalidate>
                    <input type="hidden" id="actId" name="id">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="actTitle" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-control" name="status" id="actStatus">
                                <option value="draft">แบบร่าง</option>
                                <option value="open">เปิดรับสมัคร</option>
                                <option value="closed">ปิดรับสมัคร</option>
                                <option value="cancelled">ยกเลิก</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันเริ่มต้น <span class="text-danger">*</span></label>
                            <input type="datetime-local" class="form-control" name="start_date" id="actStart" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันสิ้นสุด</label>
                            <input type="datetime-local" class="form-control" name="end_date" id="actEnd">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">วันจัดกิจกรรม</label>
                            <input type="datetime-local" class="form-control" name="event_date" id="actEventDate">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">สถานที่</label>
                            <input type="text" class="form-control" name="location" id="actLocation">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">จำนวนรับ (0 = ไม่จำกัด)</label>
                            <input type="number" class="form-control" name="max_participants" id="actMax" min="0" value="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">การเข้าถึง</label>
                            <select class="form-control" name="visibility" id="actVisibility">
                                <option value="public">เปิดให้คนทั่วไป</option>
                                <option value="members_only">เฉพาะสมาชิกสมาคม</option>
                                <option value="custom">กำหนดเอง</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3" id="actVisibilityTextWrap" style="display:none">
                            <label class="form-label">ข้อความแสดง (กำหนดเอง)</label>
                            <input type="text" class="form-control" name="visibility_text" id="actVisibilityText" placeholder="เช่น เฉพาะผู้บริหาร">
                        </div>
                        <div class="col-md-8 mb-3" id="actMemberTypesWrap">
                            <label class="form-label">ประเภทสมาชิกที่สมัครได้</label>
                            <div class="d-flex flex-wrap gap-3" id="memberTypeCheckboxes">
                                <!-- Populated by JS from DB -->
                            </div>
                            <small class="text-muted">ไม่เลือก = เปิดรับทุกประเภท</small>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ค่าลงทะเบียน (บาท)</label>
                            <input type="number" class="form-control" name="fee_amount" id="actFee" min="0" step="0.01" value="0">
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">รายละเอียดค่าลงทะเบียน</label>
                            <input type="text" class="form-control" name="fee_description" id="actFeeDesc" placeholder="เช่น ค่าอาหาร ค่าเอกสาร ฯลฯ">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ภาพปก</label>
                            <!-- Tab: เลือกวิธีใส่รูป -->
                            <ul class="nav nav-tabs nav-tabs-sm mb-2" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#actCoverUploadTab">อัปโหลดรูป</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#actCoverUrlTab">ลิงก์รูปภาพ</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="actCoverUploadTab">
                                    <input type="file" class="form-control form-control-sm" id="actCoverFile" accept="image/*">
                                    <small class="text-muted">รองรับ JPEG, PNG, GIF, WEBP (สูงสุด 10 MB)</small>
                                </div>
                                <div class="tab-pane fade" id="actCoverUrlTab">
                                    <div class="input-group input-group-sm">
                                        <input type="url" class="form-control" id="actCoverLinkInput" placeholder="https://example.com/image.jpg">
                                        <button class="btn btn-outline-primary" type="button" id="btnActCoverLink"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="cover_image" id="actCoverUrl">
                            <div id="actCoverPreview" class="mt-2 position-relative" style="display:none">
                                <img id="actCoverImg" src="" class="img-fluid rounded" style="max-height:100px" alt="cover">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeCover('activity')" title="ลบรูปปก"><i class="bi bi-x"></i></button>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 d-flex align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="actShowRegs" name="show_registrations" value="1">
                                <label class="custom-control-label" for="actShowRegs">แสดงรายชื่อผู้ลงทะเบียนให้สมาชิกเห็น</label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">รายละเอียด</label>
                            <textarea class="form-control" name="description" id="actDesc" rows="8"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveActivity">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Registrations -->
<div class="modal fade" id="regsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">รายชื่อผู้ลงทะเบียน</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="regsModalBody">
                <div class="text-center py-4"><span class="spinner-border"></span></div>
            </div>
            <div class="modal-footer justify-content-between">
                <div id="regsAccessCodeSection">
                    <span class="text-muted small me-2"><i class="bi bi-key me-1"></i>รหัสเข้าดู:</span>
                    <code id="regsAccessCode" class="me-2" style="font-size:1.1em;">-</code>
                    <button class="btn btn-outline-primary btn-sm me-1" onclick="generateAccessCode()" title="สร้าง/รีเซ็ตรหัส"><i class="bi bi-arrow-repeat me-1"></i>สร้างรหัส</button>
                    <button class="btn btn-outline-success btn-sm me-1" onclick="copyAccessLink()" title="คัดลอกลิงก์" id="btnCopyLink" style="display:none"><i class="bi bi-link-45deg me-1"></i>คัดลอกลิงก์</button>
                    <button class="btn btn-outline-danger btn-sm" onclick="removeAccessCode()" title="ลบรหัส" id="btnRemoveCode" style="display:none"><i class="bi bi-x-lg"></i></button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Slip Preview -->
<div class="modal fade" id="slipPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white py-2">
                <h5 class="modal-title"><i class="bi bi-receipt me-2"></i>สลิปการชำระเงิน</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="slipPreviewImg" src="" class="img-fluid rounded" style="max-height:70vh" alt="สลิป">
            </div>
            <div class="modal-footer py-2" id="slipPreviewFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Select Member for Activity -->
<div class="modal fade" id="memberPickerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>เลือกสมาชิกเข้าร่วมกิจกรรม</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="memberPickerSearch" placeholder="ค้นหา ชื่อ, นามสกุล, เลขสมาชิก">
                    <button class="btn btn-outline-primary" type="button" onclick="searchMembersForActivity()"><i class="bi bi-search me-1"></i>ค้นหา</button>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="8%">#</th>
                                <th width="38%">ชื่อ-สกุล</th>
                                <th width="18%">เลขสมาชิก</th>
                                <th width="26%">หน่วยงาน</th>
                                <th width="10%">เลือก</th>
                            </tr>
                        </thead>
                        <tbody id="memberPickerTable">
                            <tr><td colspan="5" class="text-center text-muted py-3">พิมพ์เพื่อค้นหาสมาชิก</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add External (Non-member) Participant -->
<div class="modal fade" id="externalParticipantModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus-fill me-2"></i>เพิ่มบุคคลที่ไม่ได้เป็นสมาชิก</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="externalParticipantForm" novalidate>
                    <div class="card border mb-3">
                        <div class="card-body py-2">
                            <label class="form-label mb-1">ค้นหาจากประวัติบุคคลภายนอก (ถ้ามี)</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="extHistorySearch" placeholder="พิมพ์ชื่อหรือต้นสังกัด">
                                <button type="button" class="btn btn-outline-info" onclick="searchExternalParticipantHistory()"><i class="bi bi-search"></i> ค้นหา</button>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="btnUseLatestExternal" onclick="useLatestExternalHistory()">
                                    <i class="bi bi-clock-history me-1"></i>ใช้ข้อมูลล่าสุด
                                </button>
                                <select id="extHistoryRecentSelect" class="form-control form-control-sm" style="max-width:320px;">
                                    <option value="">เลือกรายการล่าสุด (5 คน)</option>
                                </select>
                            </div>
                            <div id="extHistoryResults" class="mt-2"></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">คำนำหน้า</label>
                            <input type="text" class="form-control" id="extPrefix" placeholder="นาย/นาง/น.ส.">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="extFirstName" required>
                        </div>
                        <div class="col-md-5 mb-3">
                            <label class="form-label">นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="extLastName" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">หน่วยงาน/สถานที่ทำงาน</label>
                        <input type="text" class="form-control" id="extSchoolOrg" placeholder="ถ้ามี">
                    </div>

                    <div class="card border mb-2 receipt-address-shell">
                        <div class="card-header py-2"><strong>ที่อยู่สำหรับออกใบเสร็จ</strong></div>
                        <div class="card-body">
                            <div class="address-unified-card">
                                <div class="mb-2">
                                    <input type="text" id="extAddrDetail" class="form-control" placeholder="บ้านเลขที่/หมู่/ซอย/ถนน">
                                </div>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">ตำบล</span>
                                            <input type="text" id="extAddrSubdistrict" class="form-control" placeholder="ตำบล">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text">อำเภอ</span>
                                            <input type="text" id="extAddrDistrict" class="form-control" placeholder="อำเภอ">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="input-group">
                                            <span class="input-group-text">จังหวัด</span>
                                            <input type="text" id="extAddrProvince" class="form-control" placeholder="จังหวัด">
                                        </div>
                                    </div>
                                    <div class="col-md-6 mt-2">
                                        <div class="input-group">
                                            <span class="input-group-text">รหัสไปรษณีย์</span>
                                            <input type="text" id="extAddrZipcode" class="form-control" placeholder="รหัสไปรษณีย์">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <small class="text-muted">บันทึกข้อมูลไว้ก่อน แล้วค่อยสร้างใบเสร็จภายหลังจากหน้าจัดการผู้เข้าร่วมได้</small>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnAddExternalParticipant" onclick="submitExternalParticipant()">
                    <i class="bi bi-check-lg me-1"></i>เพิ่มเข้ากิจกรรม
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Manage Activity Registration -->
<div class="modal fade" id="regManageModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-gear me-2"></i>จัดการผู้เข้าร่วมกิจกรรม</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="manageRegId">
                <div class="alert alert-light border small mb-3" id="manageRegInfo">-</div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">สถานะการเข้าร่วม</label>
                        <select class="form-control" id="manageRegStatus">
                            <option value="pending">รออนุมัติ</option>
                            <option value="approved">อนุมัติ</option>
                            <option value="rejected">ปฏิเสธ</option>
                            <option value="cancelled">ยกเลิก</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">สถานะการชำระเงิน</label>
                        <select class="form-control" id="managePaymentStatus">
                            <option value="pending">รอตรวจสอบ</option>
                            <option value="paid">ชำระแล้ว</option>
                            <option value="not_required">ไม่ต้องชำระ</option>
                            <option value="refunded">คืนเงินแล้ว</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">ที่อยู่ผู้ชำระเงิน</label>
                        <div id="manageAddressSourceWrap" class="pt-1">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="manageAddressSource" id="manageAddressSourceWork" value="work" checked>
                                <label class="form-check-label" for="manageAddressSourceWork">ใช้ที่อยู่ที่ทำงาน</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="manageAddressSource" id="manageAddressSourceCurrent" value="current">
                                <label class="form-check-label" for="manageAddressSourceCurrent">ใช้ที่อยู่ปัจจุบัน</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <label class="form-label">หมายเหตุ</label>
                        <textarea id="manageRegNote" class="form-control" rows="2" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)"></textarea>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">อัปโหลดสลิปใหม่</label>
                        <input type="file" id="manageRegSlipFile" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted">แนบใหม่เพื่อแทนไฟล์เดิม</small>
                    </div>
                </div>

                <div class="card border mb-3 receipt-address-shell">
                    <div class="card-header py-2"><strong>ข้อมูลที่อยู่ออกใบเสร็จ</strong></div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                            <button type="button" class="btn btn-outline-info btn-sm" id="btnPullRegAddressFromProfile" onclick="pullRegistrationAddressFromProfile()">
                                <i class="bi bi-person-lines-fill me-1"></i>ดึงจากโปรไฟล์สมาชิก
                            </button>
                            <small class="text-muted">ใช้ข้อมูลที่อยู่จากหน้าโปรไฟล์สมาชิกอัตโนมัติ หรือกดดึงใหม่ได้</small>
                        </div>
                        <div class="mb-3" id="manageProfileSyncWrap">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="manageSyncProfileAddress">
                                <label class="form-check-label" for="manageSyncProfileAddress">บันทึกการแก้ไขที่อยู่นี้กลับไปที่โปรไฟล์สมาชิกด้วย</label>
                            </div>
                            <small class="text-muted">ถ้าติ๊กไว้ ระบบจะอัปเดตข้อมูลในโปรไฟล์สมาชิกตามข้อมูลที่กรอก</small>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">หน่วยงาน/สถานที่ทำงาน</label>
                                <input type="text" id="manageSchoolOrg" class="form-control" placeholder="โรงเรียน/หน่วยงาน">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="address-unified-card h-100">
                                    <label class="form-label">ที่อยู่ที่ทำงาน</label>
                                    <input type="text" id="workDetail" class="form-control mb-2" placeholder="บ้านเลขที่/หมู่/ซอย/ถนน">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">ตำบล</span>
                                                <input type="text" id="workSubdistrict" class="form-control" placeholder="ตำบล">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">อำเภอ</span>
                                                <input type="text" id="workDistrict" class="form-control" placeholder="อำเภอ">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="input-group">
                                                <span class="input-group-text">จังหวัด</span>
                                                <input type="text" id="workProvince" class="form-control" placeholder="จังหวัด">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="input-group">
                                                <span class="input-group-text">รหัสไปรษณีย์</span>
                                                <input type="text" id="workZipcode" class="form-control" placeholder="รหัสไปรษณีย์">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="address-unified-card h-100">
                                    <label class="form-label">ที่อยู่ปัจจุบัน</label>
                                    <input type="text" id="homeDetail" class="form-control mb-2" placeholder="บ้านเลขที่/หมู่/ซอย/ถนน">
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">ตำบล</span>
                                                <input type="text" id="homeSubdistrict" class="form-control" placeholder="ตำบล">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="input-group">
                                                <span class="input-group-text">อำเภอ</span>
                                                <input type="text" id="homeDistrict" class="form-control" placeholder="อำเภอ">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="input-group">
                                                <span class="input-group-text">จังหวัด</span>
                                                <input type="text" id="homeProvince" class="form-control" placeholder="จังหวัด">
                                            </div>
                                        </div>
                                        <div class="col-6 mt-2">
                                            <div class="input-group">
                                                <span class="input-group-text">รหัสไปรษณีย์</span>
                                                <input type="text" id="homeZipcode" class="form-control" placeholder="รหัสไปรษณีย์">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-primary" id="btnSaveAddressOnly" onclick="saveRegistrationAddressOnly()">
                        <i class="bi bi-house-gear me-1"></i>บันทึกที่อยู่สมาชิก
                    </button>
                    <button type="button" class="btn btn-outline-success" id="btnCreateRegReceipt" onclick="createRegistrationReceiptFromModal()">
                        <i class="bi bi-receipt me-1"></i>สร้างใบเสร็จ
                    </button>
                    <a href="#" class="btn btn-outline-secondary" id="btnOpenRegReceipt" style="display:none" target="_blank">
                        <i class="bi bi-box-arrow-up-right me-1"></i>เปิดใบเสร็จ
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-primary" id="btnSaveRegManage" onclick="saveRegistrationManagement()">
                    <i class="bi bi-check-lg me-1"></i>บันทึกข้อมูลการเข้าร่วม
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-crop me-2"></i>ครอปรูปภาพ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body text-center">
                <div style="max-height:60vh;overflow:hidden">
                    <img id="cropperImage" src="" style="max-width:100%;display:block">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnCropConfirm">
                    <i class="bi bi-check-lg me-1"></i> ครอปและอัปโหลด
                </button>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
let currentPage = 1;

$(async function () {
    if (!await App.requireAdminOrSubAdmin()) return;

    // Populate member type checkboxes dynamically
    App.loadMemberTypes().then(() => {
        const labels = App._memberTypeLabelsShort || {};
        let cbHtml = '';
        Object.entries(labels).forEach(([k, v]) => {
            cbHtml += `<div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input member-type-cb" id="mt_${k}" value="${k}">
                <label class="custom-control-label" for="mt_${k}">${App.escapeHtml(v)}</label>
            </div>`;
        });
        $('#memberTypeCheckboxes').html(cbHtml);
    });

    loadActivities();
});

async function loadActivities(page = 1) {
    currentPage = page;
    const tbody = $('#activitiesTable');
    tbody.html('<tr><td colspan="10" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const params = { page, per_page: 20 };
    const status = $('#filterStatus').val();
    const search = $('#searchActivity').val().trim();
    if (status) params.status = status;
    if (search) params.search = search;

    const result = await API.getActivities(params);
    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="10" class="text-center py-4 text-muted">ไม่พบกิจกรรม</td></tr>');
        return;
    }

    let html = '';
    result.data.forEach((a, i) => {
        const idx = (currentPage - 1) * 20 + i + 1;
        const publicActivityUrl = `${BASE_PATH}web/?page=activity-detail&id=${a.id}`;
        const statusMap = { 'open': '<span class="badge bg-success">เปิดรับ</span>', 'closed': '<span class="badge bg-danger">ปิดรับ</span>', 'draft': '<span class="badge bg-secondary">แบบร่าง</span>', 'cancelled': '<span class="badge bg-dark">ยกเลิก</span>' };
        const statusBadge = statusMap[a.status] || '<span class="badge bg-secondary">' + a.status + '</span>';
        const eventDate = a.event_date ? App.formatDateTime(a.event_date) : App.formatDateTime(a.start_date);
        const fee = a.has_fee && a.fee_amount > 0 ? App.formatCurrency(a.fee_amount) : 'ฟรี';
        const spots = a.max_participants > 0 ? `${a.approved_count || 0}/${a.max_participants}` : (a.approved_count || 0) + ' คน';
        const visBadge = a.visibility === 'members_only' ? '<span class="badge bg-warning text-dark"><i class="bi bi-lock me-1"></i>สมาชิก</span>'
            : a.visibility === 'custom' ? '<span class="badge bg-info"><i class="bi bi-shield me-1"></i>กำหนดเอง</span>'
            : '<span class="badge bg-success"><i class="bi bi-globe me-1"></i>สาธารณะ</span>';
        const memberTypeLabels = App._memberTypeLabelsShort || { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
        const mtBadge = a.allowed_member_types
            ? '<br><small class="text-muted">' + a.allowed_member_types.split(',').map(t => memberTypeLabels[t.trim()] || t.trim()).join(', ') + '</small>'
            : '';

        html += `<tr>
            <td>${idx}</td>
            <td><a href="${publicActivityUrl}" target="_blank" rel="noopener" class="text-decoration-none">${App.escapeHtml(a.title || '-')}</a></td>
            <td>${eventDate}</td>
            <td>${App.formatDate(a.start_date)}</td>
            <td>${a.location || '-'}</td>
            <td>${fee}</td>
            <td>
                <a href="#" onclick="viewRegistrations(${a.id});return false;" class="text-decoration-none">${spots}</a>
            </td>
            <td>${visBadge}${mtBadge}</td>
            <td>${statusBadge}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <a class="btn btn-outline-secondary" href="${publicActivityUrl}" target="_blank" rel="noopener" title="เปิดหน้ากิจกรรม"><i class="bi bi-box-arrow-up-right"></i></a>
                    <button class="btn btn-outline-info" onclick="viewRegistrations(${a.id})" title="ผู้ลงทะเบียน"><i class="bi bi-people"></i></button>
                    <button class="btn btn-outline-primary" onclick="editActivity(${a.id})" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteActivity(${a.id},'${a.title.replace(/'/g, "\\'")}')" title="ลบ"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    tbody.html(html);
    if (result.pagination) App.buildPagination('#activityPagination', result.pagination, loadActivities);
}

function openActivityForm(data = null) {
    const form = $('#activityForm')[0];
    form.reset();
    $('#actId').val('');
    $('#actCoverPreview').hide();
    $('#actCoverUrl').val('');
    // Reset member type checkboxes
    $('.member-type-cb').prop('checked', false);
    $('#actShowRegs').prop('checked', false);

    if (data) {
        $('#actFormTitle').text('แก้ไขกิจกรรม');
        $('#actId').val(data.id);
        $('#actTitle').val(data.title);
        $('#actDesc').val(data.description);
        $('#actLocation').val(data.location);
        $('#actStart').val(data.start_date ? data.start_date.replace(' ', 'T').substring(0, 16) : '');
        $('#actEnd').val(data.end_date ? data.end_date.replace(' ', 'T').substring(0, 16) : '');
        $('#actEventDate').val(data.event_date ? data.event_date.replace(' ', 'T').substring(0, 16) : '');
        $('#actMax').val(data.max_participants || 0);
        $('#actFee').val(data.fee_amount || 0);
        $('#actFeeDesc').val(data.fee_description || '');
        $('#actStatus').val(data.status);
        $('#actVisibility').val(data.visibility || 'public');
        $('#actVisibilityText').val(data.visibility_text || '');
        $('#actShowRegs').prop('checked', !!parseInt(data.show_registrations));
        toggleVisibilityText();
        // Populate allowed member types
        if (data.allowed_member_types) {
            const types = data.allowed_member_types.split(',').map(t => t.trim());
            types.forEach(t => $(`#mt_${t}`).prop('checked', true));
        }
        if (data.cover_image) {
            $('#actCoverUrl').val(data.cover_image);
            $('#actCoverImg').attr('src', App.imgUrl(data.cover_image));
            $('#actCoverPreview').show();
        }
    } else {
        $('#actFormTitle').text('เพิ่มกิจกรรม');
        toggleVisibilityText();
    }

    $('#activityFormModal').modal('show');
}

async function editActivity(id) {
    const result = await API.getActivityDetail(id);
    if (result.success) openActivityForm(result.data);
    else App.error(result.message);
}

async function deleteActivity(id, title) {
    const ok = await App.confirm(`ต้องการลบกิจกรรม "${title}" หรือไม่?`);
    if (!ok) return;
    const result = await API.deleteActivity(id);
    if (result.success) { App.success('ลบกิจกรรมสำเร็จ'); loadActivities(currentPage); }
    else App.error(result.message);
}

// Upload cover with crop
$('#actCoverFile').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) { App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)'); this.value = ''; return; }
    showCropper(file, 'activities');
});

// Cover image URL
$('#btnActCoverLink').on('click', function () {
    const url = $('#actCoverLinkInput').val().trim();
    if (!url) { App.error('กรุณากรอก URL รูปภาพ'); return; }
    $('#actCoverUrl').val(url);
    $('#actCoverImg').attr('src', App.imgUrl(url));
    $('#actCoverPreview').show();
    App.success('ใส่ลิงก์รูปปกสำเร็จ');
});

function removeCover(type) {
    if (type === 'activity') {
        $('#actCoverUrl').val('');  $('#actCoverFile').val('');  $('#actCoverPreview').hide();
    }
}

// Save activity
$('#btnSaveActivity').on('click', async function () {
    const title = $('#actTitle').val().trim();
    const startDate = $('#actStart').val();
    if (!title) { App.error('กรุณากรอกชื่อกิจกรรม'); return; }
    if (!startDate) { App.error('กรุณาระบุวันเริ่มต้น'); return; }

    const btn = $(this);
    btn.prop('disabled', true);

    const data = {
        title,
        description: $('#actDesc').val(),
        location: $('#actLocation').val(),
        start_date: startDate.replace('T', ' ') + ':00',
        end_date: $('#actEnd').val() ? $('#actEnd').val().replace('T', ' ') + ':00' : null,
        event_date: $('#actEventDate').val() ? $('#actEventDate').val().replace('T', ' ') + ':00' : null,
        max_participants: parseInt($('#actMax').val()) || 0,
        fee_amount: parseFloat($('#actFee').val()) || 0,
        fee_description: $('#actFeeDesc').val(),
        cover_image: $('#actCoverUrl').val(),
        status: $('#actStatus').val(),
        visibility: $('#actVisibility').val(),
        visibility_text: $('#actVisibilityText').val() || null,
        allowed_member_types: $('.member-type-cb:checked').map(function() { return $(this).val(); }).get().join(',') || null,
        show_registrations: $('#actShowRegs').is(':checked') ? 1 : 0
    };

    const actId = $('#actId').val();
    let result;
    if (actId) {
        data.id = parseInt(actId);
        result = await API.updateActivity(data);
    } else {
        result = await API.createActivity(data);
    }

    if (result.success) {
        $('#activityFormModal').modal('hide');
        App.success(result.message);
        loadActivities(currentPage);
    } else {
        App.error(result.message);
    }
    btn.prop('disabled', false);
});

// View registrations
let currentRegActivityId = null;
let currentRegActivityData = null;
let currentRegistrationsData = [];
let currentRegPaymentFilter = 'all';
let extHistoryRows = [];

function getPaymentStatusBadge(paymentStatus) {
    if (paymentStatus === 'paid') return '<span class="badge bg-success">ชำระแล้ว</span>';
    if (paymentStatus === 'pending') return '<span class="badge bg-warning text-dark">รอตรวจสอบ</span>';
    if (paymentStatus === 'refunded') return '<span class="badge bg-danger">คืนเงินแล้ว</span>';
    return '<span class="badge bg-secondary">ไม่ต้องชำระ</span>';
}

function applyRegistrationPaymentFilter() {
    currentRegPaymentFilter = $('#regPaymentFilter').val() || 'all';
    renderRegistrationsTable(currentRegActivityId);
}

function toggleSelectAllRegs() {
    const checked = $('#regSelectAll').is(':checked');
    $('.reg-select').prop('checked', checked);
}

function getSelectedPendingRegIds() {
    const ids = [];
    $('.reg-select:checked').each(function () {
        const status = String($(this).data('status') || '');
        if (status === 'pending') {
            ids.push(parseInt($(this).val(), 10));
        }
    });
    return ids.filter(Boolean);
}

async function bulkApproveSelected() {
    const ids = getSelectedPendingRegIds();
    if (ids.length === 0) {
        App.error('กรุณาเลือกรายการที่สถานะรออนุมัติ');
        return;
    }

    const ok = await App.confirm('ยืนยันการอนุมัติหลายรายการ', `ต้องการอนุมัติ ${ids.length} รายการที่เลือกใช่หรือไม่?`, 'question');
    if (!ok) return;

    const btn = $('#btnBulkApprove');
    btn.prop('disabled', true);

    let successCount = 0;
    for (const regId of ids) {
        const result = await API.approveRegistration(regId, 'approved', 'paid');
        if (result.success) successCount += 1;
    }

    btn.prop('disabled', false);
    if (successCount > 0) {
        App.success(`อนุมัติสำเร็จ ${successCount} รายการ`);
        await viewRegistrations(currentRegActivityId);
        loadActivities(currentPage);
    } else {
        App.error('ไม่สามารถอนุมัติรายการที่เลือกได้');
    }
}

function renderRegistrationsTable(activityId) {
    const body = $('#regsModalBody');
    const allRows = currentRegistrationsData || [];
    const rows = currentRegPaymentFilter === 'all'
        ? allRows
        : allRows.filter(r => (r.payment_status || '') === currentRegPaymentFilter);

    if (rows.length === 0) {
        body.html('<p class="text-center text-muted py-3">ไม่พบรายการตามตัวกรองที่เลือก</p>');
        return;
    }

    let html = `<div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-muted">ทั้งหมด ${rows.length} คน</span>
        <div class="d-flex gap-2">
            <select id="regPaymentFilter" class="form-control form-control-sm" style="min-width:180px" onchange="applyRegistrationPaymentFilter()">
                <option value="all">การชำระทั้งหมด</option>
                <option value="pending">รอตรวจสอบ</option>
                <option value="paid">ชำระแล้ว</option>
                <option value="not_required">ไม่ต้องชำระ</option>
                <option value="refunded">คืนเงินแล้ว</option>
            </select>
            <button class="btn btn-outline-primary btn-sm" onclick="openMemberPicker()"><i class="bi bi-person-plus me-1"></i>เลือกสมาชิก</button>
            <button class="btn btn-outline-secondary btn-sm" onclick="openExternalParticipantModal()"><i class="bi bi-person-plus-fill me-1"></i>เพิ่มบุคคลที่ไม่ได้เป็นสมาชิก</button>
            <button class="btn btn-outline-success btn-sm" id="btnBulkApprove" onclick="bulkApproveSelected()"><i class="bi bi-check2-all me-1"></i>ยืนยันหลายคน</button>
            <button class="btn btn-success btn-sm" onclick="exportRegistrationsExcel()"><i class="bi bi-file-earmark-excel me-1"></i>Export Excel</button>
        </div>
    </div>`;
    html += `<div class="table-responsive"><table class="table table-sm table-hover" id="regsTable">
        <thead><tr><th width="4%"><input type="checkbox" id="regSelectAll" onchange="toggleSelectAllRegs()"></th><th>#</th><th>ชื่อ-สกุล</th><th>เลขสมาชิก</th><th>โรงเรียน/หน่วยงาน</th><th>การชำระเงิน</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody>`;

    rows.forEach((r, i) => {
        const payBadge = getPaymentStatusBadge(r.payment_status);
        const stBadge = App.getStatusBadge(r.status);
        const slip = r.payment_proof ? `<button class="btn btn-outline-info btn-sm" onclick="previewSlip('${App.escHtml(r.payment_proof)}', ${r.id}, '${r.status}', '${r.payment_status}', ${activityId})" title="ดูสลิป"><i class="bi bi-receipt"></i></button>` : '';
        const displayName = r.full_name || r.external_full_name || `${r.external_prefix || ''}${r.external_first_name || ''} ${r.external_last_name || ''}`.trim() || '-';
        const orgName = r.school_organization || r.external_school_organization || '-';
        const memberNo = r.member_number ? App.escapeHtml(r.member_number) : '-';
        const canSelect = r.status === 'pending';
        const canDelete = API.isAdmin() || (r.status === 'pending' && r.payment_status !== 'paid');
        const deleteBlockedReason = r.payment_status === 'paid'
            ? 'รายการชำระแล้ว ลบได้เฉพาะ admin'
            : (r.status !== 'pending' ? 'ลบได้เฉพาะสถานะรออนุมัติ (pending)' : 'ไม่สามารถลบรายการนี้ได้');

        html += `<tr>
            <td>${canSelect ? `<input type="checkbox" class="reg-select" value="${r.id}" data-status="${r.status}">` : ''}</td>
            <td>${i + 1}</td>
            <td>${App.escapeHtml(displayName)}${r.is_external ? ' <span class="badge bg-secondary ms-1">บุคคลภายนอก</span>' : ''}</td>
            <td>${memberNo}</td>
            <td>${App.escapeHtml(orgName)}</td>
            <td>${payBadge} ${slip}</td>
            <td>${stBadge}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="openRegistrationManager(${r.id})" title="จัดการ"><i class="bi bi-sliders"></i></button>
                    ${canDelete
                        ? `<button class="btn btn-outline-danger" onclick="removeRegistrationFromActivity(${r.id}, '${App.escapeHtml((r.full_name || '').replace(/'/g, "\\'"))}', ${activityId})" title="ลบออกจากกิจกรรม"><i class="bi bi-trash"></i></button>`
                        : `<button class="btn btn-outline-secondary" disabled title="${deleteBlockedReason}"><i class="bi bi-trash"></i></button>`}
                    ${r.status === 'pending' ? `<button class="btn btn-outline-success" onclick="approveReg(${r.id},'approved','paid',${activityId})" title="อนุมัติทันที"><i class="bi bi-check-lg"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    body.html(html);
    $('#regPaymentFilter').val(currentRegPaymentFilter);
}

async function viewRegistrations(activityId) {
    currentRegActivityId = activityId;
    $('#regsModal').modal('show');
    const body = $('#regsModalBody');
    body.html('<div class="text-center py-4"><span class="spinner-border"></span></div>');

    // Load activity detail for access code
    const actResult = await API.getActivityDetail(activityId);
    if (actResult.success && actResult.data) {
        currentRegActivityData = actResult.data;
        updateAccessCodeUI(actResult.data.access_code);
    }

    const result = await API.getActivityRegistrations(activityId);
    if (!result.success || !result.data || result.data.length === 0) {
        currentRegistrationsData = [];
        body.html('<p class="text-center text-muted py-3">ยังไม่มีผู้ลงทะเบียน</p>');
        return;
    }
    currentRegistrationsData = result.data;
    currentRegPaymentFilter = 'all';
    renderRegistrationsTable(activityId);
}

function updateAccessCodeUI(code) {
    if (code) {
        $('#regsAccessCode').text(code);
        $('#btnCopyLink, #btnRemoveCode').show();
    } else {
        $('#regsAccessCode').text('ยังไม่มี');
        $('#btnCopyLink, #btnRemoveCode').hide();
    }
}

async function generateAccessCode() {
    if (!currentRegActivityId) return;
    const result = await API.resetAccessCode(currentRegActivityId);
    if (result.success && result.data) {
        updateAccessCodeUI(result.data.access_code);
        if (currentRegActivityData) currentRegActivityData.access_code = result.data.access_code;
        App.success('สร้างรหัสเข้าดูสำเร็จ: ' + result.data.access_code);
    } else {
        App.error(result.message);
    }
}

async function removeAccessCode() {
    if (!currentRegActivityId) return;
    if (!confirm('ต้องการลบรหัสเข้าดู? ลิงก์สาธารณะจะใช้ไม่ได้')) return;
    const result = await API.removeAccessCode(currentRegActivityId);
    if (result.success) {
        updateAccessCodeUI(null);
        if (currentRegActivityData) currentRegActivityData.access_code = null;
        App.success('ลบรหัสเข้าดูสำเร็จ');
    } else {
        App.error(result.message);
    }
}

function copyAccessLink() {
    if (!currentRegActivityId || !currentRegActivityData || !currentRegActivityData.access_code) return;
    const url = window.location.origin + '/web/?page=activity-participants&id=' + currentRegActivityId + '&code=' + currentRegActivityData.access_code;
    navigator.clipboard.writeText(url).then(() => {
        App.success('คัดลอกลิงก์สำเร็จ');
    }).catch(() => {
        prompt('คัดลอกลิงก์นี้:', url);
    });
}

function exportRegistrationsExcel() {
    const table = document.getElementById('regsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    let csv = '\uFEFF'; // BOM for Excel UTF-8
    const actTitle = currentRegActivityData ? currentRegActivityData.title : 'กิจกรรม';

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        cells.forEach((cell, idx) => {
            if (idx === 0 || idx === cells.length - 1) return; // Skip select + action columns
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'รายชื่อผู้ลงทะเบียน-' + actTitle + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}

async function approveReg(regId, status, paymentStatus, activityId) {
    const result = await API.approveRegistration(regId, status, paymentStatus);
    if (result.success) {
        App.success(result.message);
        viewRegistrations(activityId);
        loadActivities(currentPage);
    } else {
        App.error(result.message);
    }
}

async function confirmRemoveRegistrationWithCode(fullName) {
    const code = String(Math.floor(100000 + Math.random() * 900000));
    const title = 'ยืนยันลบผู้เข้าร่วมกิจกรรม';
    const msg = `ต้องการลบ ${fullName || 'สมาชิก'} ออกจากกิจกรรมใช่หรือไม่`;

    if (typeof Swal === 'undefined') {
        const input = window.prompt(`${msg}\n\nพิมพ์รหัสยืนยัน 6 หลักนี้: ${code}`);
        return input === code;
    }

    const result = await Swal.fire({
        icon: 'warning',
        title,
        html: `${App.escapeHtml(msg)}<br><br>กรุณาพิมพ์รหัสนี้เพื่อยืนยัน: <strong style="font-size:1.15rem;letter-spacing:2px;">${code}</strong>`,
        input: 'text',
        inputPlaceholder: 'พิมพ์รหัส 6 หลัก',
        showCancelButton: true,
        confirmButtonText: 'ยืนยันลบ',
        cancelButtonText: 'ยกเลิก',
        reverseButtons: true,
        preConfirm: (value) => {
            if ((value || '').trim() !== code) {
                Swal.showValidationMessage('รหัสยืนยันไม่ถูกต้อง');
                return false;
            }
            return true;
        }
    });

    return !!result.isConfirmed;
}

async function removeRegistrationFromActivity(regId, fullName, activityId) {
    const ok = await confirmRemoveRegistrationWithCode(fullName);
    if (!ok) return;

    const result = await API.removeActivityRegistration(regId);
    if (!result.success) {
        App.error(result.message || 'ไม่สามารถลบผู้เข้าร่วมได้');
        return;
    }

    App.success(result.message || 'ลบผู้เข้าร่วมกิจกรรมสำเร็จ');
    await viewRegistrations(activityId);
    loadActivities(currentPage);
}

function previewSlip(url, regId, regStatus, paymentStatus, activityId) {
    const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
    $('#slipPreviewImg').attr('src', src);

    let footerHtml = '<button type="button" class="btn btn-secondary" data-dismiss="modal">ปิด</button>';
    if (regStatus === 'pending') {
        footerHtml = `<button class="btn btn-success btn-sm" onclick="$('#slipPreviewModal').modal('hide');approveReg(${regId},'approved','paid',${activityId})"><i class="bi bi-check-lg me-1"></i>อนุมัติ</button>
            <button class="btn btn-danger btn-sm" onclick="$('#slipPreviewModal').modal('hide');approveReg(${regId},'rejected',null,${activityId})"><i class="bi bi-x-lg me-1"></i>ปฏิเสธ</button>` + footerHtml;
    }
    $('#slipPreviewFooter').html(footerHtml);
    $('#slipPreviewModal').modal('show');
}

let currentManageRegData = null;
const regProfileAddressCache = {};

function parseAddressObject(raw) {
    if (!raw) return {};
    if (typeof raw === 'object') return raw;
    try {
        return JSON.parse(raw) || {};
    } catch (e) {
        return {};
    }
}

function fillAddress(prefix, data) {
    const d = data || {};
    $(`#${prefix}Detail`).val(d.detail || d.address || '');
    $(`#${prefix}Subdistrict`).val(d.subdistrict || '');
    $(`#${prefix}District`).val(d.district || '');
    $(`#${prefix}Province`).val(d.province || '');
    $(`#${prefix}Zipcode`).val(d.zipcode || d.postal_code || '');
}

function readAddress(prefix) {
    return {
        detail: $(`#${prefix}Detail`).val().trim(),
        subdistrict: $(`#${prefix}Subdistrict`).val().trim(),
        district: $(`#${prefix}District`).val().trim(),
        province: $(`#${prefix}Province`).val().trim(),
        zipcode: $(`#${prefix}Zipcode`).val().trim(),
    };
}

function getManageAddressSource() {
    return $('input[name="manageAddressSource"]:checked').val() || 'work';
}

function setManageProfileControls(enabled, hintText = '') {
    $('#btnPullRegAddressFromProfile').prop('disabled', !enabled);
    $('#manageSyncProfileAddress').prop('disabled', !enabled);
    if (!enabled) {
        $('#manageSyncProfileAddress').prop('checked', false);
    }
    const text = hintText || (enabled
        ? 'ถ้าติ๊กไว้ ระบบจะอัปเดตข้อมูลในโปรไฟล์สมาชิกตามข้อมูลที่กรอก'
        : 'รายการนี้เป็นบุคคลภายนอก จึงไม่มีการซิงก์กลับโปรไฟล์สมาชิก');
    $('#manageProfileSyncWrap small').text(text);
}

function buildManagePayerAddressJson(source) {
    const useCurrent = source === 'current';
    const addr = useCurrent ? readAddress('home') : readAddress('work');
    const orgName = useCurrent ? '' : $('#manageSchoolOrg').val().trim();
    const hasData = !!(orgName || addr.detail || addr.subdistrict || addr.district || addr.province || addr.zipcode);
    if (!hasData) return null;
    return JSON.stringify({
        organization: orgName,
        detail: addr.detail,
        subdistrict: addr.subdistrict,
        district: addr.district,
        province: addr.province,
        zipcode: addr.zipcode,
    });
}

function getExternalAddressPayloadForManageSave() {
    const source = getManageAddressSource();
    return {
        external_school_organization: $('#manageSchoolOrg').val().trim(),
        external_payer_address: buildManagePayerAddressJson(source),
    };
}

async function getRegistrationMemberProfile(userId) {
    if (!userId) return null;
    const key = String(userId);
    if (regProfileAddressCache[key]) return regProfileAddressCache[key];

    const result = await API.getProfile(parseInt(userId, 10));
    if (result.success && result.data) {
        regProfileAddressCache[key] = result.data;
        return result.data;
    }
    return null;
}

async function pullRegistrationAddressFromProfile(showSuccess = true) {
    if (!currentManageRegData || !currentManageRegData.user_id) {
        App.error('ไม่พบสมาชิกของรายการลงทะเบียนนี้');
        return;
    }

    const member = await getRegistrationMemberProfile(currentManageRegData.user_id);
    if (!member) {
        App.error('ไม่สามารถโหลดข้อมูลจากโปรไฟล์สมาชิกได้');
        return;
    }

    $('#manageSchoolOrg').val(member.school_organization || '');
    fillAddress('work', parseAddressObject(member.work_address));
    fillAddress('home', parseAddressObject(member.home_address));

    if (showSuccess) App.success('ดึงข้อมูลที่อยู่จากโปรไฟล์สมาชิกแล้ว');
}

function openMemberPicker() {
    if (!currentRegActivityId) {
        App.error('ไม่พบกิจกรรมที่กำลังจัดการ');
        return;
    }
    $('#memberPickerSearch').val('');
    $('#memberPickerTable').html('<tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></td></tr>');
    $('#memberPickerModal').modal('show');
    searchMembersForActivity();
}

async function searchMembersForActivity() {
    if (!currentRegActivityId) return;
    const q = $('#memberPickerSearch').val().trim();
    const table = $('#memberPickerTable');
    table.html('<tr><td colspan="5" class="text-center py-3"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const result = await API.searchActivityMembers(currentRegActivityId, q);
    if (!result.success) {
        table.html(`<tr><td colspan="5" class="text-center text-danger py-3">${App.escapeHtml(result.message || 'เกิดข้อผิดพลาด')}</td></tr>`);
        return;
    }
    const rows = result.data || [];
    if (rows.length === 0) {
        table.html('<tr><td colspan="5" class="text-center text-muted py-3">ไม่พบสมาชิกที่เลือกได้</td></tr>');
        return;
    }

    let html = '';
    rows.forEach((m, i) => {
        html += `<tr>
            <td>${i + 1}</td>
            <td>
                <strong>${App.escapeHtml(m.full_name || '-')}</strong><br>
                <small class="text-muted">${App.escapeHtml(m.email || '-')}</small>
            </td>
            <td>${App.escapeHtml(m.member_number || '-')}</td>
            <td>${App.escapeHtml(m.school_organization || '-')}</td>
            <td><button class="btn btn-outline-primary btn-sm" onclick="addMemberToActivity(${m.id}, '${App.escapeHtml((m.full_name || '').replace(/'/g, "\\'"))}')"><i class="bi bi-plus-lg"></i></button></td>
        </tr>`;
    });
    table.html(html);
}

async function addMemberToActivity(userId, fullName) {
    if (!currentRegActivityId) return;
    const ok = await App.confirm('เพิ่มสมาชิกเข้าร่วมกิจกรรม', `ต้องการเพิ่ม ${fullName || 'สมาชิก'} ใช่หรือไม่?`, 'question');
    if (!ok) return;

    const result = await API.addActivityMemberRegistration(currentRegActivityId, userId);
    if (result.success) {
        App.success(result.message || 'เพิ่มสมาชิกสำเร็จ');
        await viewRegistrations(currentRegActivityId);
        await searchMembersForActivity();
    } else {
        App.error(result.message || 'ไม่สามารถเพิ่มสมาชิกได้');
    }
}

function buildExternalPayerAddressPayload() {
    const organization = $('#extSchoolOrg').val().trim();
    const detail = $('#extAddrDetail').val().trim();
    const subdistrict = $('#extAddrSubdistrict').val().trim();
    const district = $('#extAddrDistrict').val().trim();
    const province = $('#extAddrProvince').val().trim();
    const zipcode = $('#extAddrZipcode').val().trim();

    const hasAny = !!(organization || detail || subdistrict || district || province || zipcode);
    if (!hasAny) return null;

    return {
        organization,
        detail,
        subdistrict,
        district,
        province,
        zipcode,
    };
}

function openExternalParticipantModal() {
    if (!currentRegActivityId) {
        App.error('ไม่พบกิจกรรมที่กำลังจัดการ');
        return;
    }
    $('#externalParticipantForm')[0].reset();
    $('#extHistorySearch').val('');
    $('#extHistoryResults').html('');
    $('#extHistoryRecentSelect').html('<option value="">เลือกรายการล่าสุด (5 คน)</option>');
    $('#btnUseLatestExternal').prop('disabled', true);
    extHistoryRows = [];
    $('#externalParticipantModal').modal('show');
    searchExternalParticipantHistory();
}

function fillExternalFormFromHistoryRow(row) {
    if (!row) return;
    $('#extPrefix').val(row.prefix || '');
    $('#extFirstName').val(row.first_name || '');
    $('#extLastName').val(row.last_name || '');
    $('#extSchoolOrg').val(row.school_organization || '');

    const addr = parseAddressObject(row.payer_address);
    $('#extAddrDetail').val(addr.detail || addr.address || '');
    $('#extAddrSubdistrict').val(addr.subdistrict || '');
    $('#extAddrDistrict').val(addr.district || '');
    $('#extAddrProvince').val(addr.province || '');
    $('#extAddrZipcode').val(addr.zipcode || addr.postal_code || '');
}

function renderExternalHistoryResults() {
    const box = $('#extHistoryResults');
    const recentSelect = $('#extHistoryRecentSelect');
    recentSelect.html('<option value="">เลือกรายการล่าสุด (5 คน)</option>');

    if (!extHistoryRows.length) {
        box.html('<small class="text-muted">ไม่พบข้อมูลจากประวัติ</small>');
        $('#btnUseLatestExternal').prop('disabled', true);
        return;
    }

    $('#btnUseLatestExternal').prop('disabled', false);

    extHistoryRows.slice(0, 5).forEach((r, idx) => {
        const org = r.school_organization ? ` | ${r.school_organization}` : '';
        recentSelect.append(`<option value="${idx}">${App.escapeHtml(r.full_name || '-')} ${App.escapeHtml(org)}</option>`);
    });

    let html = '<div class="list-group">';
    extHistoryRows.forEach((r, idx) => {
        const org = r.school_organization ? `<small class="text-muted">${App.escapeHtml(r.school_organization)}</small>` : '<small class="text-muted">-</small>';
        html += `<button type="button" class="list-group-item list-group-item-action py-2" onclick="useExternalHistory(${idx})">
            <div class="d-flex justify-content-between align-items-center">
                <strong>${App.escapeHtml(r.full_name || '-')}</strong>
                <small class="text-muted">ดึงข้อมูล</small>
            </div>
            ${org}
        </button>`;
    });
    html += '</div>';
    box.html(html);
}

function useLatestExternalHistory() {
    if (!extHistoryRows.length) {
        App.error('ไม่พบข้อมูลล่าสุดให้ใช้งาน');
        return;
    }
    useExternalHistory(0);
}

async function searchExternalParticipantHistory() {
    const q = $('#extHistorySearch').val().trim();
    const box = $('#extHistoryResults');
    box.html('<small class="text-muted"><span class="spinner-border spinner-border-sm me-1"></span>กำลังค้นหา...</small>');

    const result = await API.searchActivityExternalParticipants(q);
    if (!result.success) {
        box.html(`<small class="text-danger">${App.escapeHtml(result.message || 'ค้นหาไม่สำเร็จ')}</small>`);
        return;
    }

    extHistoryRows = result.data || [];
    renderExternalHistoryResults();
}

function useExternalHistory(idx) {
    const row = extHistoryRows[idx];
    if (!row) return;
    fillExternalFormFromHistoryRow(row);
    App.success('ดึงข้อมูลจากประวัติแล้ว');
}

$('#extHistoryRecentSelect').on('change', function() {
    const idx = parseInt($(this).val(), 10);
    if (Number.isNaN(idx)) return;
    useExternalHistory(idx);
});

$('#extHistorySearch').on('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchExternalParticipantHistory();
    }
});

async function submitExternalParticipant() {
    if (!currentRegActivityId) return;

    const prefix = $('#extPrefix').val().trim();
    const firstName = $('#extFirstName').val().trim();
    const lastName = $('#extLastName').val().trim();
    const schoolOrg = $('#extSchoolOrg').val().trim();

    if (!firstName || !lastName) {
        App.error('กรุณากรอกชื่อและนามสกุล');
        return;
    }

    const btn = $('#btnAddExternalParticipant');
    btn.prop('disabled', true);

    const result = await API.addActivityExternalRegistration({
        activity_id: currentRegActivityId,
        external_prefix: prefix,
        external_first_name: firstName,
        external_last_name: lastName,
        external_school_organization: schoolOrg,
        external_payer_address: buildExternalPayerAddressPayload(),
    });

    btn.prop('disabled', false);

    if (!result.success) {
        App.error(result.message || 'ไม่สามารถเพิ่มบุคคลภายนอกได้');
        return;
    }

    $('#externalParticipantModal').modal('hide');
    App.success(result.message || 'เพิ่มบุคคลภายนอกสำเร็จ');
    await viewRegistrations(currentRegActivityId);
}

$('#memberPickerSearch').on('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        searchMembersForActivity();
    }
});

function setRegistrationReceiptLink(receiptId) {
    if (receiptId) {
        $('#btnOpenRegReceipt').attr('href', `./?page=receipts&receipt_id=${receiptId}`).show();
    } else {
        $('#btnOpenRegReceipt').hide().attr('href', '#');
    }
}

async function openRegistrationManager(registrationId) {
    const result = await API.getActivityRegistrationDetail(registrationId);
    if (!result.success || !result.data) {
        App.error(result.message || 'ไม่พบข้อมูลการลงทะเบียน');
        return;
    }
    const d = result.data;
    currentManageRegData = d;
    const isExternal = parseInt(d.is_external || 0, 10) === 1 || !d.user_id;

    $('#manageRegId').val(d.id);
    $('#manageRegStatus').val(d.status || 'pending');
    $('#managePaymentStatus').val(d.payment_status || 'pending');
    $('#manageAddressSourceWork').prop('checked', true);
    $('#manageAddressSourceCurrent').prop('disabled', false);
    $('#manageRegNote').val(d.note || '');
    $('#manageRegSlipFile').val('');
    $('#manageSyncProfileAddress').prop('checked', false);
    if (isExternal) {
        const extAddr = parseAddressObject(d.external_payer_address);
        $('#manageSchoolOrg').val(d.external_school_organization || extAddr.organization || '');
        fillAddress('work', {
            detail: extAddr.detail || extAddr.address || '',
            subdistrict: extAddr.subdistrict || '',
            district: extAddr.district || '',
            province: extAddr.province || '',
            zipcode: extAddr.zipcode || '',
        });
        fillAddress('home', {});
        $('#manageAddressSourceWork').prop('checked', true);
        $('#manageAddressSourceCurrent').prop('disabled', true);
        setManageProfileControls(false);
    } else {
        $('#manageSchoolOrg').val(d.school_organization || '');
        fillAddress('work', parseAddressObject(d.work_address));
        fillAddress('home', parseAddressObject(d.home_address));
        setManageProfileControls(true);
    }

    const displayName = d.full_name || d.external_full_name || `${d.external_prefix || ''}${d.external_first_name || ''} ${d.external_last_name || ''}`.trim() || '-';
    const infoText = `${displayName} | ${d.activity_title || '-'} | ค่าลงทะเบียน ${App.formatCurrency(d.fee_amount || 0)}`;
    $('#manageRegInfo').text(infoText);

    setRegistrationReceiptLink(d.receipt ? d.receipt.id : null);
    $('#regManageModal').modal('show');
}

async function uploadRegistrationSlipIfNeeded() {
    const file = document.getElementById('manageRegSlipFile').files[0];
    if (!file) return null;
    if (file.size > 10 * 1024 * 1024) {
        App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)');
        return false;
    }

    const fd = new FormData();
    fd.append('file', file);

    const token = API.getToken();
    const headers = {};
    if (token) headers['X-Auth-Token'] = token;

    try {
        const response = await fetch(API.baseUrl + API.apiUrl('upload', 'image', { type: 'general' }), {
            method: 'POST',
            headers,
            body: fd,
        });
        const json = await response.json();
        if (!json.success || !json.data || !json.data.url) {
            App.error(json.message || 'อัปโหลดสลิปไม่สำเร็จ');
            return false;
        }
        return json.data.url;
    } catch (e) {
        App.error('เกิดข้อผิดพลาดในการอัปโหลดสลิป');
        return false;
    }
}

function getAddressPayloadForSave() {
    return {
        school_organization: $('#manageSchoolOrg').val().trim(),
        work_address: readAddress('work'),
        home_address: readAddress('home'),
    };
}

async function saveRegistrationAddressOnly() {
    const regId = parseInt($('#manageRegId').val(), 10);
    if (!regId) return;

    const isExternal = currentManageRegData && (parseInt(currentManageRegData.is_external || 0, 10) === 1 || !currentManageRegData.user_id);

    if (isExternal) {
        const extResult = await API.manageActivityRegistration({
            registration_id: regId,
            ...getExternalAddressPayloadForManageSave(),
        });
        if (extResult.success) App.success(extResult.message || 'บันทึกที่อยู่สำเร็จ');
        else App.error(extResult.message || 'บันทึกที่อยู่ไม่สำเร็จ');
        return;
    }

    const payload = getAddressPayloadForSave();
    const result = await API.updateActivityRegistrationAddress({
        registration_id: regId,
        ...payload,
    });

    if (result.success) {
        App.success(result.message || 'บันทึกที่อยู่สำเร็จ');
    } else {
        App.error(result.message || 'บันทึกที่อยู่ไม่สำเร็จ');
    }
}

async function saveRegistrationManagement() {
    const regId = parseInt($('#manageRegId').val(), 10);
    if (!regId) return;
    const isExternal = currentManageRegData && (parseInt(currentManageRegData.is_external || 0, 10) === 1 || !currentManageRegData.user_id);

    const btn = $('#btnSaveRegManage');
    btn.prop('disabled', true);

    let paymentProof = currentManageRegData ? currentManageRegData.payment_proof : null;
    const uploaded = await uploadRegistrationSlipIfNeeded();
    if (uploaded === false) {
        btn.prop('disabled', false);
        return;
    }
    if (uploaded) paymentProof = uploaded;

    const syncProfile = $('#manageSyncProfileAddress').is(':checked');
    if (isExternal) {
        const extAddrResult = await API.manageActivityRegistration({
            registration_id: regId,
            ...getExternalAddressPayloadForManageSave(),
        });
        if (!extAddrResult.success) {
            btn.prop('disabled', false);
            App.error(extAddrResult.message || 'ไม่สามารถบันทึกที่อยู่บุคคลภายนอกได้');
            return;
        }
    } else if (syncProfile) {
        const addrPayload = getAddressPayloadForSave();
        const saveAddrResult = await API.updateActivityRegistrationAddress({
            registration_id: regId,
            ...addrPayload,
        });
        if (!saveAddrResult.success) {
            btn.prop('disabled', false);
            App.error(saveAddrResult.message || 'ไม่สามารถบันทึกที่อยู่สมาชิกได้');
            return;
        }
    }

    const regPayload = {
        registration_id: regId,
        status: $('#manageRegStatus').val(),
        payment_status: $('#managePaymentStatus').val(),
        note: $('#manageRegNote').val().trim(),
        payment_proof: paymentProof,
    };

    const result = await API.manageActivityRegistration(regPayload);
    btn.prop('disabled', false);

    if (result.success) {
        App.success(result.message || 'บันทึกสำเร็จ');
        $('#regManageModal').modal('hide');
        await viewRegistrations(currentRegActivityId);
        loadActivities(currentPage);
    } else {
        App.error(result.message || 'ไม่สามารถบันทึกข้อมูลได้');
    }
}

async function createRegistrationReceiptFromModal() {
    const regId = parseInt($('#manageRegId').val(), 10);
    if (!regId) return;
    const isExternal = currentManageRegData && (parseInt(currentManageRegData.is_external || 0, 10) === 1 || !currentManageRegData.user_id);

    const btn = $('#btnCreateRegReceipt');
    btn.prop('disabled', true);

    let paymentProof = currentManageRegData ? currentManageRegData.payment_proof : null;
    const uploaded = await uploadRegistrationSlipIfNeeded();
    if (uploaded === false) {
        btn.prop('disabled', false);
        return;
    }
    if (uploaded) {
        paymentProof = uploaded;
        const proofResult = await API.manageActivityRegistration({
            registration_id: regId,
            payment_proof: paymentProof,
        });
        if (!proofResult.success) {
            btn.prop('disabled', false);
            App.error(proofResult.message || 'ไม่สามารถบันทึกสลิปก่อนสร้างใบเสร็จได้');
            return;
        }
        if (currentManageRegData) currentManageRegData.payment_proof = paymentProof;
    }

    const syncProfile = $('#manageSyncProfileAddress').is(':checked');
    if (isExternal) {
        const extAddrResult = await API.manageActivityRegistration({
            registration_id: regId,
            ...getExternalAddressPayloadForManageSave(),
        });
        if (!extAddrResult.success) {
            btn.prop('disabled', false);
            App.error(extAddrResult.message || 'ไม่สามารถบันทึกที่อยู่ก่อนสร้างใบเสร็จได้');
            return;
        }
    } else if (syncProfile) {
        const addrPayload = getAddressPayloadForSave();
        const saveAddrResult = await API.updateActivityRegistrationAddress({
            registration_id: regId,
            ...addrPayload,
        });
        if (!saveAddrResult.success) {
            btn.prop('disabled', false);
            App.error(saveAddrResult.message || 'ไม่สามารถบันทึกที่อยู่ก่อนสร้างใบเสร็จได้');
            return;
        }
    }

    const source = getManageAddressSource();
    const payerAddress = buildManagePayerAddressJson(source);
    const result = await API.createActivityRegistrationReceipt(regId, source, payerAddress);
    btn.prop('disabled', false);

    if (result.success) {
        App.success(result.message || 'สร้างใบเสร็จสำเร็จ');
        if (result.data && result.data.receipt_id) {
            setRegistrationReceiptLink(result.data.receipt_id);
        }
    } else {
        App.error(result.message || 'ไม่สามารถสร้างใบเสร็จได้');
    }
}

$('#filterStatus').on('change', () => loadActivities(1));
let searchTimer;
$('#searchActivity').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadActivities(1), 400);
});

// ─── Visibility Toggle ───
function toggleVisibilityText() {
    if ($('#actVisibility').val() === 'custom') {
        $('#actVisibilityTextWrap').show();
    } else {
        $('#actVisibilityTextWrap').hide();
        $('#actVisibilityText').val('');
    }
}
$('#actVisibility').on('change', toggleVisibilityText);

// ─── Cropper Logic ───
let cropper = null;
let cropFile = null;
let cropTarget = 'activities';

function showCropper(file, target) {
    cropFile = file;
    cropTarget = target;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('cropperImage');
        img.src = e.target.result;
        if (cropper) { cropper.destroy(); cropper = null; }
        $('#cropperModal').modal('show');
        $('#cropperModal').one('shown.bs.modal', function () {
            cropper = new Cropper(img, {
                aspectRatio: 1200 / 630,
                viewMode: 2,
                autoCropArea: 1,
                responsive: true,
                guides: true,
                background: true,
            });
        });
    };
    reader.readAsDataURL(file);
}

$('#btnCropConfirm').on('click', async function () {
    if (!cropper || !cropFile) return;
    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังอัปโหลด...');

    const cropData = cropper.getData(true);
    const formData = new FormData();
    formData.append('file', cropFile);
    formData.append('cropX', cropData.x);
    formData.append('cropY', cropData.y);
    formData.append('cropWidth', cropData.width);
    formData.append('cropHeight', cropData.height);

    const type = cropTarget;
    try {
        const token = API.getToken();
        const headers = {};
        if (token) headers['X-Auth-Token'] = token;
        const response = await fetch(API.baseUrl + API.apiUrl('upload', 'image', { type }), {
            method: 'POST', headers, body: formData
        });
        const result = await response.json();
        if (result.success) {
            $('#actCoverUrl').val(result.data.url);
            $('#actCoverImg').attr('src', App.imgUrl(result.data.url));
            $('#actCoverPreview').show();
            $('#cropperModal').modal('hide');
            App.success(`อัปโหลดสำเร็จ (${result.data.width}x${result.data.height}, ${(result.data.size/1024).toFixed(0)} KB)`);
        } else {
            App.error(result.message);
        }
    } catch (err) {
        App.error('เกิดข้อผิดพลาดในการอัปโหลด');
        console.error(err);
    }
    btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i> ครอปและอัปโหลด');
});

$('#cropperModal').on('hidden.bs.modal', function () {
    if (cropper) { cropper.destroy(); cropper = null; }
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
