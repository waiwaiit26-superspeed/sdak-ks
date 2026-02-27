<?php
/**
 * Admin — จัดการประเภทสมาชิก
 */
$pageTitle = 'จัดการประเภทสมาชิก';
$page = 'member-types';
include ROOT_PATH . 'templates/admin/header.php';
include ROOT_PATH . 'templates/admin/sidebar.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1><i class="bi bi-people-fill me-2"></i>จัดการประเภทสมาชิก</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">หน้าแรก</a></li>
                        <li class="breadcrumb-item active">ประเภทสมาชิก</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title"><i class="bi bi-list-ul me-2"></i>รายการประเภทสมาชิก</h3>
                    <button class="btn btn-sm btn-success" onclick="openCreateMemberTypeModal()">
                        <i class="bi bi-plus-circle me-1"></i> เพิ่มประเภทใหม่
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width:40px">#</th>
                                    <th style="width:50px">ไอคอน</th>
                                    <th>ชื่อประเภท</th>
                                    <th>Type Key</th>
                                    <th>ค่าธรรมเนียม</th>
                                    <th style="width:60px">ลำดับ</th>
                                    <th style="width:90px">สถานะ</th>
                                    <th style="width:60px"></th>
                                </tr>
                            </thead>
                            <tbody id="memberTypesTableBody">
                                <tr><td colspan="8" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span> กำลังโหลด...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Create / Edit Member Type Modal -->
<div class="modal fade" id="memberTypeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="memberTypeModalTitle"><i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทสมาชิก</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="memberTypeForm">
                <div class="modal-body">
                    <input type="hidden" id="mtEditKey">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Type Key <span class="text-danger" id="mtKeyRequired">*</span></label>
                                <input type="text" id="mtTypeKey" class="form-control" placeholder="เช่น regular, associate" pattern="[a-z0-9_]+" title="ภาษาอังกฤษตัวเล็ก ตัวเลข และ _ เท่านั้น">
                                <small class="text-muted">ใช้สำหรับอ้างอิงในระบบ (เปลี่ยนภายหลังไม่ได้)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อประเภท <span class="text-danger">*</span></label>
                                <input type="text" id="mtLabel" class="form-control" placeholder="เช่น สมาชิกสามัญ" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ชื่อย่อ</label>
                                <input type="text" id="mtLabelShort" class="form-control" placeholder="เช่น สามัญ">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>คำอธิบาย</label>
                                <input type="text" id="mtDescription" class="form-control" placeholder="คำอธิบายสั้นๆ">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="bi bi-cash-coin me-1"></i> ค่าธรรมเนียม</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>รูปแบบการเก็บ</label>
                                <select id="mtFeeMode" class="form-control">
                                    <option value="none">ไม่เก็บค่าใช้จ่าย</option>
                                    <option value="onetime">จ่ายครั้งเดียว</option>
                                    <option value="annual">จ่ายรายปี</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="mtFeeAmountGroup">
                            <div class="form-group">
                                <label>จำนวนเงิน (บาท)</label>
                                <input type="number" id="mtFeeAmount" class="form-control" min="0" step="0.01" value="0">
                            </div>
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="bi bi-palette me-1"></i> ไอคอนและสี</h6>
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Bootstrap Icon</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text" id="mtIconPreview"><i class="bi bi-person-fill"></i></span>
                                    </div>
                                    <input type="text" id="mtIcon" class="form-control" value="bi-person-fill" placeholder="bi-person-fill">
                                </div>
                                <small class="text-muted">ดูรายการที่ <a href="https://icons.getbootstrap.com/" target="_blank">icons.getbootstrap.com</a></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>สีพื้นหลัง</label>
                                <div class="d-flex gap-2">
                                    <input type="color" id="mtIconBg" class="form-control form-control-color" value="#a78bfa" style="width:50px;padding:2px;">
                                    <input type="text" id="mtIconBgHex" class="form-control" value="#a78bfa" maxlength="7" style="font-size:13px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>สีไอคอน</label>
                                <div class="d-flex gap-2">
                                    <input type="color" id="mtIconColor" class="form-control form-control-color" value="#3b0764" style="width:50px;padding:2px;">
                                    <input type="text" id="mtIconColorHex" class="form-control" value="#3b0764" maxlength="7" style="font-size:13px;">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="form-group">
                                <label>ตัวอย่าง</label>
                                <div>
                                    <span id="mtIconLivePreview" class="d-inline-flex align-items-center justify-content-center rounded-circle"
                                          style="width:48px;height:48px;background:#a78bfa;color:#3b0764;font-size:24px;">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>ลำดับการแสดงผล</label>
                                <input type="number" id="mtSortOrder" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center pt-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="mtIsActive" checked>
                                <label class="custom-control-label" for="mtIsActive">เปิดใช้งาน</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" id="btnSaveMemberType"><i class="bi bi-check-circle me-1"></i> สร้าง</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
let _allMemberTypes = [];

// ═══════════════════════════════════════════════════════════════
// Load & Render
// ═══════════════════════════════════════════════════════════════
async function loadMemberTypesTable() {
    const res = await API.getMemberTypes();
    if (!res.success) {
        $('#memberTypesTableBody').html('<tr><td colspan="8" class="text-center text-danger py-3">โหลดข้อมูลไม่สำเร็จ</td></tr>');
        return;
    }
    _allMemberTypes = res.data || [];
    renderMemberTypesTable();
}

function renderMemberTypesTable() {
    if (!_allMemberTypes.length) {
        $('#memberTypesTableBody').html('<tr><td colspan="8" class="text-center text-muted py-3">ยังไม่มีประเภทสมาชิก</td></tr>');
        return;
    }
    const feeModeLabels = { none: 'ไม่เก็บ', onetime: 'ครั้งเดียว', annual: 'รายปี' };
    let html = '';
    _allMemberTypes.forEach((t, i) => {
        const feeText = t.fee_mode === 'none'
            ? '<span class="text-muted">-</span>'
            : `${feeModeLabels[t.fee_mode] || t.fee_mode} / ${App.formatCurrency(t.fee_amount)}`;
        const statusBadge = t.is_active == 1
            ? '<span class="badge bg-success">เปิดใช้งาน</span>'
            : '<span class="badge bg-secondary">ปิดใช้งาน</span>';
        html += `<tr class="${t.is_active == 1 ? '' : 'table-secondary'}">
            <td>${i + 1}</td>
            <td>
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle"
                      style="width:32px;height:32px;background:${App.escapeHtml(t.icon_bg || '#a78bfa')};color:${App.escapeHtml(t.icon_color || '#3b0764')};font-size:16px;">
                    <i class="bi ${App.escapeHtml(t.icon || 'bi-person-fill')}"></i>
                </span>
            </td>
            <td>
                <strong>${App.escapeHtml(t.label)}</strong>
                ${t.label_short ? '<br><small class="text-muted">' + App.escapeHtml(t.label_short) + '</small>' : ''}
                ${t.description ? '<br><small class="text-muted">' + App.escapeHtml(t.description) + '</small>' : ''}
            </td>
            <td><code>${App.escapeHtml(t.type_key)}</code></td>
            <td>${feeText}</td>
            <td>${t.sort_order}</td>
            <td>${statusBadge}</td>
            <td>
                <button class="btn btn-outline-warning btn-sm" onclick="openEditMemberType('${App.escapeHtml(t.type_key)}')" title="แก้ไข">
                    <i class="bi bi-pencil"></i>
                </button>
            </td>
        </tr>`;
    });
    $('#memberTypesTableBody').html(html);
}

// ═══════════════════════════════════════════════════════════════
// Create / Edit Modal
// ═══════════════════════════════════════════════════════════════
function openCreateMemberTypeModal() {
    $('#mtEditKey').val('');
    $('#mtTypeKey').val('').prop('readonly', false);
    $('#mtKeyRequired').show();
    $('#mtLabel').val('');
    $('#mtLabelShort').val('');
    $('#mtDescription').val('');
    $('#mtFeeMode').val('none');
    $('#mtFeeAmount').val(0);
    $('#mtSortOrder').val(0);
    $('#mtIcon').val('bi-person-fill');
    $('#mtIconBg').val('#a78bfa');
    $('#mtIconBgHex').val('#a78bfa');
    $('#mtIconColor').val('#3b0764');
    $('#mtIconColorHex').val('#3b0764');
    $('#mtIsActive').prop('checked', true);
    updateMtIconPreview();
    toggleMtFeeAmount();
    $('#memberTypeModalTitle').html('<i class="bi bi-plus-circle me-2"></i>เพิ่มประเภทสมาชิก');
    $('#btnSaveMemberType').html('<i class="bi bi-check-circle me-1"></i> สร้าง');
    $('#memberTypeModal').modal('show');
}

function openEditMemberType(typeKey) {
    const t = _allMemberTypes.find(x => x.type_key === typeKey);
    if (!t) return;
    $('#mtEditKey').val(t.type_key);
    $('#mtTypeKey').val(t.type_key).prop('readonly', true);
    $('#mtKeyRequired').hide();
    $('#mtLabel').val(t.label);
    $('#mtLabelShort').val(t.label_short || '');
    $('#mtDescription').val(t.description || '');
    $('#mtFeeMode').val(t.fee_mode || 'none');
    $('#mtFeeAmount').val(parseFloat(t.fee_amount) || 0);
    $('#mtSortOrder').val(t.sort_order || 0);
    $('#mtIcon').val(t.icon || 'bi-person-fill');
    $('#mtIconBg').val(t.icon_bg || '#a78bfa');
    $('#mtIconBgHex').val(t.icon_bg || '#a78bfa');
    $('#mtIconColor').val(t.icon_color || '#3b0764');
    $('#mtIconColorHex').val(t.icon_color || '#3b0764');
    $('#mtIsActive').prop('checked', t.is_active == 1);
    updateMtIconPreview();
    toggleMtFeeAmount();
    $('#memberTypeModalTitle').html('<i class="bi bi-pencil-square me-2"></i>แก้ไขประเภทสมาชิก: ' + App.escapeHtml(t.label));
    $('#btnSaveMemberType').html('<i class="bi bi-check-circle me-1"></i> บันทึก');
    $('#memberTypeModal').modal('show');
}

// ═══════════════════════════════════════════════════════════════
// Icon & Fee UI Helpers
// ═══════════════════════════════════════════════════════════════
function updateMtIconPreview() {
    const icon = $('#mtIcon').val() || 'bi-person-fill';
    const bg   = $('#mtIconBg').val() || '#a78bfa';
    const clr  = $('#mtIconColor').val() || '#3b0764';
    $('#mtIconPreview').html('<i class="bi ' + App.escapeHtml(icon) + '"></i>');
    $('#mtIconLivePreview').css({ background: bg, color: clr }).html('<i class="bi ' + App.escapeHtml(icon) + '"></i>');
}

function toggleMtFeeAmount() {
    if ($('#mtFeeMode').val() === 'none') {
        $('#mtFeeAmountGroup').slideUp(150);
    } else {
        $('#mtFeeAmountGroup').slideDown(150);
    }
}

$('#mtIcon').on('input', updateMtIconPreview);
$('#mtIconBg').on('input', function(){ $('#mtIconBgHex').val(this.value); updateMtIconPreview(); });
$('#mtIconBgHex').on('input', function(){ const v = this.value.trim(); if(/^#[0-9a-fA-F]{6}$/.test(v)){ $('#mtIconBg').val(v); updateMtIconPreview(); }});
$('#mtIconColor').on('input', function(){ $('#mtIconColorHex').val(this.value); updateMtIconPreview(); });
$('#mtIconColorHex').on('input', function(){ const v = this.value.trim(); if(/^#[0-9a-fA-F]{6}$/.test(v)){ $('#mtIconColor').val(v); updateMtIconPreview(); }});
$('#mtFeeMode').on('change', toggleMtFeeAmount);

// ═══════════════════════════════════════════════════════════════
// Form Submit
// ═══════════════════════════════════════════════════════════════
$('#memberTypeForm').on('submit', async function(e) {
    e.preventDefault();
    const editKey = $('#mtEditKey').val();
    const isCreate = !editKey;

    const data = {
        type_key:    isCreate ? $('#mtTypeKey').val().trim() : editKey,
        label:       $('#mtLabel').val().trim(),
        label_short: $('#mtLabelShort').val().trim() || null,
        description: $('#mtDescription').val().trim() || null,
        fee_mode:    $('#mtFeeMode').val(),
        fee_amount:  parseFloat($('#mtFeeAmount').val()) || 0,
        sort_order:  parseInt($('#mtSortOrder').val()) || 0,
        icon:        $('#mtIcon').val().trim() || 'bi-person-fill',
        icon_bg:     $('#mtIconBg').val() || '#a78bfa',
        icon_color:  $('#mtIconColor').val() || '#3b0764',
        is_active:   $('#mtIsActive').is(':checked') ? 1 : 0,
    };

    if (!data.type_key) { App.error('กรุณาระบุ Type Key'); return; }
    if (!data.label) { App.error('กรุณาระบุชื่อประเภท'); return; }

    const btn = $('#btnSaveMemberType');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

    let result;
    if (isCreate) {
        result = await API.createMemberType(data);
    } else {
        result = await API.updateMemberType(data);
    }

    btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> บันทึก');

    if (result.success) {
        App.success(result.message || (isCreate ? 'สร้างสำเร็จ' : 'บันทึกสำเร็จ'));
        $('#memberTypeModal').modal('hide');
        await loadMemberTypesTable();
    } else {
        App.error(result.message || 'เกิดข้อผิดพลาด');
    }
});

// ═══════════════════════════════════════════════════════════════
// Init
// ═══════════════════════════════════════════════════════════════
$(function() {
    loadMemberTypesTable();
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
