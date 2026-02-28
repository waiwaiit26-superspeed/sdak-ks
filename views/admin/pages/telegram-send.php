<?php $pageTitle = 'ส่งข่าว Telegram'; $page = 'telegram-send'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>
<?php include ROOT_PATH . 'templates/admin/navbar-top.php'; ?>
<?php include ROOT_PATH . 'templates/admin/sidebar.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-send me-2"></i>ส่งข่าว Telegram</h1></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                            <li class="breadcrumb-item active">ส่งข่าว Telegram</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <!-- ============ ซ้าย: เลือกสมาชิก ============ -->
                    <div class="col-lg-7">
                        <!-- สถิติ -->
                        <div class="row mb-3">
                            <div class="col-6 col-md-3">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h4 id="statTotal">0</h4>
                                        <p>สมาชิกทั้งหมด</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-people"></i></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h4 id="statTelegram">0</h4>
                                        <p>มี Telegram</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-telegram"></i></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h4 id="statNoTelegram">0</h4>
                                        <p>ไม่มี Telegram</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-person-x"></i></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h4 id="statSelected">0</h4>
                                        <p>เลือกแล้ว</p>
                                    </div>
                                    <div class="icon"><i class="bi bi-check2-square"></i></div>
                                </div>
                            </div>
                        </div>

                        <!-- ตัวกรอง -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-body py-2">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-4">
                                        <input type="text" id="filterSearch" class="form-control form-control-sm" placeholder="ค้นหาชื่อ, อีเมล, เบอร์โทร...">
                                    </div>
                                    <div class="col-md-2">
                                        <select id="filterTelegram" class="form-control form-control-sm">
                                            <option value="">Telegram ทั้งหมด</option>
                                            <option value="linked">มี Telegram</option>
                                            <option value="not_linked">ไม่มี Telegram</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="filterStatus" class="form-control form-control-sm">
                                            <option value="">สถานะทั้งหมด</option>
                                            <option value="active">Active</option>
                                            <option value="pending">Pending</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select id="filterMemberType" class="form-control form-control-sm">
                                            <option value="">ประเภททั้งหมด</option>
                                            <option value="ordinary">สามัญ</option>
                                            <option value="associate">วิสามัญ</option>
                                            <option value="affiliate">สมทบ</option>
                                            <option value="honorary">กิตติมศักดิ์</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-outline-primary btn-sm w-100" onclick="loadMembers(1)">
                                            <i class="bi bi-search"></i> ค้นหา
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ปุ่มเลือกลด -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <button class="btn btn-sm btn-outline-success" onclick="selectAllTelegram()" title="เลือกเฉพาะคนที่มี Telegram ในหน้านี้">
                                    <i class="bi bi-check-all"></i> เลือกทั้งหมด (เฉพาะมี TG)
                                </button>
                                <button class="btn btn-sm btn-outline-secondary ml-1" onclick="deselectAll()">
                                    <i class="bi bi-x-lg"></i> ยกเลิกทั้งหมด
                                </button>
                            </div>
                            <div>
                                <span class="badge badge-primary" id="selectedBadge" style="font-size:0.9rem;">เลือกแล้ว: 0 คน</span>
                            </div>
                        </div>

                        <!-- ตารางสมาชิก -->
                        <div class="card shadow-sm">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-sm mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="40"><input type="checkbox" id="checkAll" title="เลือก/ยกเลิกทั้งหมดในหน้านี้"></th>
                                                <th>สมาชิก</th>
                                                <th>เบอร์สมาชิก</th>
                                                <th class="text-center">Telegram</th>
                                                <th class="text-center">สถานะ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="memberTableBody">
                                            <tr><td colspan="5" class="text-center text-muted py-4">กำลังโหลด...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <small class="text-muted" id="paginationInfo">-</small>
                                <div id="paginationNav"></div>
                            </div>
                        </div>

                        <!-- รายชื่อที่เลือก -->
                        <div class="card shadow-sm mt-3" id="selectedMembersCard" style="display:none;">
                            <div class="card-header bg-primary text-white py-2">
                                <h6 class="mb-0"><i class="bi bi-people-fill me-1"></i> สมาชิกที่เลือก (<span id="selectedCountHeader">0</span> คน)</h6>
                            </div>
                            <div class="card-body p-2" id="selectedMembersList" style="max-height:200px;overflow-y:auto;">
                            </div>
                        </div>
                    </div>

                    <!-- ============ ขวา: ส่งข้อความ ============ -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm sticky-top" style="top:10px;z-index:10;">
                            <div class="card-header bg-success text-white">
                                <h5 class="card-title mb-0"><i class="bi bi-telegram me-2"></i>ส่งข้อความ</h5>
                            </div>
                            <div class="card-body">
                                <!-- เลือกประเภท -->
                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">ประเภทข้อความ</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <input type="radio" class="btn-check" name="msgType" id="typeText" value="text" checked>
                                        <label class="btn btn-outline-primary" for="typeText"><i class="bi bi-chat-text me-1"></i>ข้อความ</label>

                                        <input type="radio" class="btn-check" name="msgType" id="typePhoto" value="photo">
                                        <label class="btn btn-outline-primary" for="typePhoto"><i class="bi bi-image me-1"></i>รูปภาพ</label>

                                        <input type="radio" class="btn-check" name="msgType" id="typeDocument" value="document">
                                        <label class="btn btn-outline-primary" for="typeDocument"><i class="bi bi-file-earmark me-1"></i>ไฟล์</label>

                                        <input type="radio" class="btn-check" name="msgType" id="typeVideo" value="video">
                                        <label class="btn btn-outline-primary" for="typeVideo"><i class="bi bi-camera-video me-1"></i>วิดีโอ</label>
                                    </div>
                                </div>

                                <!-- ส่วน text -->
                                <div id="sectionText">
                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">ข้อความ</label>
                                        <textarea id="msgText" class="form-control" rows="8" placeholder="พิมพ์ข้อความที่ต้องการส่ง...&#10;&#10;รองรับ HTML เช่น &lt;b&gt;ตัวหนา&lt;/b&gt;, &lt;i&gt;ตัวเอียง&lt;/i&gt;, &lt;a href=&quot;URL&quot;&gt;ลิงก์&lt;/a&gt;"></textarea>
                                        <small class="text-muted">รองรับ HTML: <code>&lt;b&gt;</code> <code>&lt;i&gt;</code> <code>&lt;u&gt;</code> <code>&lt;a&gt;</code> <code>&lt;code&gt;</code> <code>&lt;pre&gt;</code></small>
                                    </div>
                                </div>

                                <!-- ส่วน photo -->
                                <div id="sectionPhoto" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">เลือกรูปภาพ</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="msgPhoto" accept="image/*">
                                            <label class="custom-file-label" for="msgPhoto" data-browse="เลือกไฟล์">ยังไม่ได้เลือกรูป</label>
                                        </div>
                                        <div id="photoPreview" class="mt-2" style="display:none;">
                                            <img id="photoPreviewImg" src="" class="img-fluid rounded" style="max-height:200px;">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">แคปชั่น (ไม่บังคับ)</label>
                                        <textarea id="msgPhotoCaption" class="form-control" rows="3" placeholder="คำอธิบายรูปภาพ..."></textarea>
                                    </div>
                                </div>

                                <!-- ส่วน document -->
                                <div id="sectionDocument" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">เลือกไฟล์</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="msgDocument">
                                            <label class="custom-file-label" for="msgDocument" data-browse="เลือกไฟล์">ยังไม่ได้เลือกไฟล์</label>
                                        </div>
                                        <small class="text-muted">รองรับ PDF, Word, Excel, ZIP และอื่นๆ (ไม่เกิน 50MB)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">แคปชั่น (ไม่บังคับ)</label>
                                        <textarea id="msgDocCaption" class="form-control" rows="3" placeholder="คำอธิบายไฟล์..."></textarea>
                                    </div>
                                </div>

                                <!-- ส่วน video -->
                                <div id="sectionVideo" style="display:none;">
                                    <div class="mb-3">
                                        <label class="form-label font-weight-bold">เลือกวิดีโอ</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="msgVideo" accept="video/*">
                                            <label class="custom-file-label" for="msgVideo" data-browse="เลือกไฟล์">ยังไม่ได้เลือกวิดีโอ</label>
                                        </div>
                                        <small class="text-muted">รองรับ MP4, MOV และอื่นๆ (ไม่เกิน 50MB)</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">แคปชั่น (ไม่บังคับ)</label>
                                        <textarea id="msgVideoCaption" class="form-control" rows="3" placeholder="คำอธิบายวิดีโอ..."></textarea>
                                    </div>
                                </div>

                                <!-- สรุปก่อนส่ง -->
                                <div class="alert alert-info py-2 mb-3" id="sendSummary">
                                    <i class="bi bi-info-circle me-1"></i>
                                    เลือกสมาชิกที่ต้องการส่งข้อความก่อน
                                </div>

                                <!-- ปุ่มส่ง -->
                                <button class="btn btn-success btn-lg w-100" id="btnSend" onclick="sendMessage()" disabled>
                                    <i class="bi bi-send me-2"></i> ส่งข้อความ
                                </button>
                            </div>
                        </div>

                        <!-- ประวัติการส่ง -->
                        <div class="card shadow-sm mt-3">
                            <div class="card-header py-2">
                                <h6 class="card-title mb-0"><i class="bi bi-clock-history me-1"></i> ประวัติการส่ง</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>วันที่</th>
                                                <th>ประเภท</th>
                                                <th>ผลลัพธ์</th>
                                                <th>ข้อความ</th>
                                            </tr>
                                        </thead>
                                        <tbody id="logTableBody">
                                            <tr><td colspan="4" class="text-center text-muted py-3">กำลังโหลด...</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<style>
.small-box { border-radius: 8px; }
.small-box .inner h4 { font-size: 1.5rem; margin-bottom: 0; }
.small-box .inner p { font-size: 0.8rem; margin-bottom: 0; }
.small-box .icon i { font-size: 3rem; }
.member-row.no-telegram { opacity: 0.6; }
.member-row.selected { background-color: #e8f5e9 !important; }
.selected-chip { display: inline-flex; align-items: center; background: #e3f2fd; border-radius: 16px; padding: 2px 10px 2px 6px; margin: 2px; font-size: 0.8rem; }
.selected-chip .remove-chip { cursor: pointer; margin-left: 4px; color: #d32f2f; font-weight: bold; }
.btn-check { position: absolute; clip: rect(0,0,0,0); pointer-events: none; }
.btn-check:checked + .btn-outline-primary { background-color: #007bff; color: #fff; border-color: #007bff; }
#msgText { font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.9rem; }
</style>

<script>
$(function() {

    // ==========================================
    // State
    // ==========================================
    let allMembers = [];
    let selectedMembers = new Map(); // id => {id, full_name, telegram_chat_id}
    let currentPage = 1;

    // ==========================================
    // โหลดสมาชิก
    // ==========================================
    window.loadMembers = async function(page = 1) {
        currentPage = page;
        const params = new URLSearchParams({
            search: $('#filterSearch').val(),
            telegram_status: $('#filterTelegram').val(),
            status: $('#filterStatus').val(),
            member_type: $('#filterMemberType').val(),
            page: page,
            limit: 20,
        });

        try {
            const result = await API.get(API.apiUrl('telegram-message', 'members') + '&' + params.toString());
            if (!result.success) {
                App.error(result.message);
                return;
            }

            const data = result.data;
            allMembers = data.members;

            // อัปเดตสถิติ
            $('#statTotal').text(data.total);
            $('#statTelegram').text(data.total_telegram);
            $('#statNoTelegram').text(data.total - data.total_telegram);

            renderMemberTable(data.members);
            renderPagination(data.pagination);
            updateSelectedUI();
        } catch (error) {
            App.error('ไม่สามารถโหลดข้อมูลได้: ' + error.message);
        }
    };

    function renderMemberTable(members) {
        const tbody = $('#memberTableBody');
        if (!members.length) {
            tbody.html('<tr><td colspan="5" class="text-center text-muted py-4">ไม่พบสมาชิก</td></tr>');
            return;
        }

        const typeLabels = {
            ordinary: '<span class="badge badge-info">สามัญ</span>',
            associate: '<span class="badge badge-secondary">วิสามัญ</span>',
            affiliate: '<span class="badge badge-warning text-dark">สมทบ</span>',
            honorary: '<span class="badge badge-dark">กิตติมศักดิ์</span>',
        };

        const statusLabels = {
            active: '<span class="badge badge-success">Active</span>',
            pending: '<span class="badge badge-warning text-dark">Pending</span>',
            suspended: '<span class="badge badge-danger">Suspended</span>',
        };

        let html = '';
        members.forEach(m => {
            const hasTg = !!m.telegram_chat_id;
            const isSelected = selectedMembers.has(String(m.id));
            const rowClass = `member-row ${!hasTg ? 'no-telegram' : ''} ${isSelected ? 'selected' : ''}`;

            html += `<tr class="${rowClass}" data-id="${m.id}">
                <td>
                    <input type="checkbox" class="member-check" data-id="${m.id}" 
                           data-name="${escapeHtml(m.full_name)}" 
                           data-chat="${m.telegram_chat_id || ''}"
                           ${isSelected ? 'checked' : ''}
                           ${!hasTg ? 'disabled title="ยังไม่ได้เชื่อมต่อ Telegram"' : ''}>
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div>
                            <div class="font-weight-bold">${escapeHtml(m.full_name)}</div>
                            <small class="text-muted">${escapeHtml(m.email || '')}</small>
                        </div>
                    </div>
                </td>
                <td><small>${escapeHtml(m.member_number || '-')}</small></td>
                <td class="text-center">
                    ${hasTg 
                        ? '<span class="badge badge-success"><i class="bi bi-telegram"></i> เชื่อมต่อแล้ว</span>' 
                        : '<span class="badge badge-secondary">ยังไม่มี</span>'}
                </td>
                <td class="text-center">${statusLabels[m.status] || m.status}</td>
            </tr>`;
        });
        tbody.html(html);
    }

    function renderPagination(pg) {
        if (!pg) return;
        $('#paginationInfo').text(`แสดง ${((pg.current_page - 1) * pg.per_page) + 1}-${Math.min(pg.current_page * pg.per_page, pg.total)} จาก ${pg.total} รายการ`);

        let nav = '';
        if (pg.last_page > 1) {
            nav += `<nav><ul class="pagination pagination-sm mb-0">`;
            if (pg.current_page > 1) nav += `<li class="page-item"><a class="page-link" href="#" onclick="loadMembers(${pg.current_page - 1});return false;">&laquo;</a></li>`;
            for (let i = Math.max(1, pg.current_page - 2); i <= Math.min(pg.last_page, pg.current_page + 2); i++) {
                nav += `<li class="page-item ${i === pg.current_page ? 'active' : ''}"><a class="page-link" href="#" onclick="loadMembers(${i});return false;">${i}</a></li>`;
            }
            if (pg.current_page < pg.last_page) nav += `<li class="page-item"><a class="page-link" href="#" onclick="loadMembers(${pg.current_page + 1});return false;">&raquo;</a></li>`;
            nav += `</ul></nav>`;
        }
        $('#paginationNav').html(nav);
    }

    // ==========================================
    // Checkbox events
    // ==========================================
    $(document).on('change', '.member-check', function() {
        const id = String($(this).data('id'));
        const name = $(this).data('name');
        const chat = $(this).data('chat');

        if ($(this).is(':checked')) {
            if (chat) {
                selectedMembers.set(id, { id, full_name: name, telegram_chat_id: chat });
            }
        } else {
            selectedMembers.delete(id);
        }
        updateSelectedUI();
    });

    $('#checkAll').on('change', function() {
        const checked = $(this).is(':checked');
        $('.member-check:not(:disabled)').each(function() {
            $(this).prop('checked', checked).trigger('change');
        });
    });

    window.selectAllTelegram = function() {
        // เลือกเฉพาะคนที่มี Telegram ในหน้านี้
        $('.member-check:not(:disabled)').each(function() {
            if (!$(this).is(':checked')) {
                $(this).prop('checked', true).trigger('change');
            }
        });
    };

    window.deselectAll = function() {
        selectedMembers.clear();
        $('.member-check').prop('checked', false);
        $('#checkAll').prop('checked', false);
        updateSelectedUI();
    };

    function removeSelected(id) {
        selectedMembers.delete(String(id));
        $(`.member-check[data-id="${id}"]`).prop('checked', false);
        updateSelectedUI();
    }

    // ==========================================
    // UI อัปเดต
    // ==========================================
    function updateSelectedUI() {
        const count = selectedMembers.size;
        $('#statSelected').text(count);
        $('#selectedBadge').text(`เลือกแล้ว: ${count} คน`);
        $('#selectedCountHeader').text(count);

        // ปุ่มส่ง
        const hasMsgContent = checkMessageContent();
        $('#btnSend').prop('disabled', count === 0 || !hasMsgContent);

        // สรุป
        if (count > 0) {
            $('#sendSummary').html(`<i class="bi bi-check-circle text-success me-1"></i> จะส่งไปยังสมาชิก <strong>${count}</strong> คนที่เลือก`);
        } else {
            $('#sendSummary').html(`<i class="bi bi-info-circle me-1"></i> เลือกสมาชิกที่ต้องการส่งข้อความก่อน`);
        }

        // แสดง/ซ่อนรายชื่อ
        if (count > 0) {
            $('#selectedMembersCard').show();
            let chips = '';
            selectedMembers.forEach((m, id) => {
                chips += `<span class="selected-chip">
                    <i class="bi bi-telegram text-primary mr-1"></i>
                    ${escapeHtml(m.full_name)}
                    <span class="remove-chip" onclick="window.removeSelectedMember('${id}')">&times;</span>
                </span>`;
            });
            $('#selectedMembersList').html(chips);
        } else {
            $('#selectedMembersCard').hide();
        }

        // Highlight selected rows
        $('.member-row').each(function() {
            const id = String($(this).data('id'));
            $(this).toggleClass('selected', selectedMembers.has(id));
        });
    }

    window.removeSelectedMember = function(id) {
        removeSelected(id);
    };

    // ==========================================
    // ประเภทข้อความ
    // ==========================================
    $('input[name="msgType"]').on('change', function() {
        const type = $(this).val();
        $('#sectionText, #sectionPhoto, #sectionDocument, #sectionVideo').hide();
        $(`#section${type.charAt(0).toUpperCase() + type.slice(1)}`).show();
        updateSelectedUI();
    });

    // Bootstrap 4 custom file input label
    $(document).on('change', '.custom-file-input', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).siblings('.custom-file-label').text(fileName || 'เลือกไฟล์');
    });

    // Photo preview
    $('#msgPhoto').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = e => {
                $('#photoPreviewImg').attr('src', e.target.result);
                $('#photoPreview').show();
            };
            reader.readAsDataURL(file);
        } else {
            $('#photoPreview').hide();
        }
        updateSelectedUI();
    });

    // Text change updates send button
    $('#msgText, #msgPhotoCaption, #msgDocCaption, #msgVideoCaption').on('input', function() {
        updateSelectedUI();
    });
    $('#msgDocument, #msgVideo').on('change', function() {
        updateSelectedUI();
    });

    function checkMessageContent() {
        const type = $('input[name="msgType"]:checked').val();
        switch (type) {
            case 'text': return !!$('#msgText').val().trim();
            case 'photo': return !!$('#msgPhoto')[0].files.length;
            case 'document': return !!$('#msgDocument')[0].files.length;
            case 'video': return !!$('#msgVideo')[0].files.length;
        }
        return false;
    }

    // ==========================================
    // ส่งข้อความ
    // ==========================================
    window.sendMessage = async function() {
        const type = $('input[name="msgType"]:checked').val();
        const memberIds = Array.from(selectedMembers.keys()).map(Number);
        
        if (memberIds.length === 0) {
            App.error('กรุณาเลือกสมาชิกอย่างน้อย 1 คน');
            return;
        }

        // ยืนยัน
        const confirmed = await Swal.fire({
            title: 'ยืนยันการส่งข้อความ?',
            html: `จะส่ง<strong>${type === 'text' ? 'ข้อความ' : type === 'photo' ? 'รูปภาพ' : type === 'document' ? 'ไฟล์' : 'วิดีโอ'}</strong>ไปยังสมาชิก <strong>${memberIds.length}</strong> คน`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-send me-1"></i> ส่งเลย',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#28a745',
        });

        if (!confirmed.isConfirmed) return;

        // แสดง loading
        Swal.fire({
            title: 'กำลังส่ง...',
            html: '<div class="d-flex flex-column align-items-center"><div class="spinner-border text-success mb-3"></div><p class="mb-0">กรุณารอสักครู่ กำลังส่งไปยังสมาชิก...</p></div>',
            allowOutsideClick: false,
            showConfirmButton: false,
        });

        try {
            let result;

            if (type === 'text') {
                result = await API.post(API.apiUrl('telegram-message', 'send-text'), {
                    member_ids: memberIds,
                    message: $('#msgText').val().trim(),
                    parse_mode: 'HTML',
                });
            } else {
                // ใช้ FormData สำหรับ file upload
                const formData = new FormData();
                formData.append('member_ids', JSON.stringify(memberIds));
                formData.append('parse_mode', 'HTML');

                if (type === 'photo') {
                    formData.append('photo', $('#msgPhoto')[0].files[0]);
                    formData.append('caption', $('#msgPhotoCaption').val().trim());
                    result = await sendFormData('send-photo', formData);
                } else if (type === 'document') {
                    formData.append('document', $('#msgDocument')[0].files[0]);
                    formData.append('caption', $('#msgDocCaption').val().trim());
                    result = await sendFormData('send-document', formData);
                } else if (type === 'video') {
                    formData.append('video', $('#msgVideo')[0].files[0]);
                    formData.append('caption', $('#msgVideoCaption').val().trim());
                    result = await sendFormData('send-video', formData);
                }
            }

            Swal.close();

            if (result && result.success) {
                const d = result.data;
                let html = `<div class="text-left">`;
                html += `<p><i class="bi bi-check-circle text-success"></i> ส่งสำเร็จ: <strong>${d.success}</strong> คน</p>`;
                if (d.failed > 0) {
                    html += `<p><i class="bi bi-x-circle text-danger"></i> ส่งล้มเหลว: <strong>${d.failed}</strong> คน</p>`;
                    if (d.errors && d.errors.length) {
                        html += '<ul class="small text-danger">';
                        d.errors.forEach(e => html += `<li>${escapeHtml(e.name)}: ${escapeHtml(e.error)}</li>`);
                        html += '</ul>';
                    }
                }
                html += '</div>';

                Swal.fire({
                    title: 'ผลการส่ง',
                    html: html,
                    icon: d.failed > 0 ? 'warning' : 'success',
                    confirmButtonText: 'ตกลง',
                });

                // เคลียร์ form
                clearForm();
                loadLogs();
            } else {
                App.error(result?.message || 'เกิดข้อผิดพลาด');
            }
        } catch (error) {
            Swal.close();
            App.error('เกิดข้อผิดพลาด: ' + error.message);
        }
    };

    async function sendFormData(action, formData) {
        const token = localStorage.getItem('token');
        const url = API.apiUrl('telegram-message', action);
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-Auth-Token': token,
            },
            body: formData,
        });
        return await response.json();
    }

    function clearForm() {
        $('#msgText').val('');
        $('#msgPhotoCaption, #msgDocCaption, #msgVideoCaption').val('');
        $('#msgPhoto, #msgDocument, #msgVideo').val('');
        $('.custom-file-label').text('เลือกไฟล์');
        $('#photoPreview').hide();
        selectedMembers.clear();
        $('.member-check, #checkAll').prop('checked', false);
        updateSelectedUI();
    }

    // ==========================================
    // ประวัติการส่ง
    // ==========================================
    async function loadLogs() {
        try {
            const result = await API.get(API.apiUrl('telegram-message', 'logs') + '&limit=10');
            if (!result.success) return;

            const logs = result.data.logs;
            if (!logs.length) {
                $('#logTableBody').html('<tr><td colspan="4" class="text-center text-muted py-3">ยังไม่มีประวัติ</td></tr>');
                return;
            }

            const typeIcons = {
                text: '<i class="bi bi-chat-text text-primary"></i>',
                photo: '<i class="bi bi-image text-success"></i>',
                document: '<i class="bi bi-file-earmark text-warning"></i>',
                video: '<i class="bi bi-camera-video text-info"></i>',
            };

            let html = '';
            logs.forEach(l => {
                const date = new Date(l.created_at);
                const dateStr = date.toLocaleDateString('th-TH', { day: '2-digit', month: 'short' }) + ' ' + date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' });
                const preview = (l.message_preview || '').substring(0, 40);
                html += `<tr>
                    <td><small>${dateStr}</small></td>
                    <td>${typeIcons[l.message_type] || l.message_type}</td>
                    <td><span class="text-success">${l.success_count}</span>/<span class="text-danger">${l.fail_count + l.success_count}</span></td>
                    <td><small class="text-truncate d-inline-block" style="max-width:150px;" title="${escapeAttr(l.message_preview)}">${escapeHtml(preview)}</small></td>
                </tr>`;
            });
            $('#logTableBody').html(html);
        } catch (e) {
            console.warn('ไม่สามารถโหลดประวัติ:', e);
        }
    }

    // ==========================================
    // Helpers
    // ==========================================
    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
    function escapeAttr(str) {
        return escapeHtml(str).replace(/'/g, '&#39;');
    }

    // Enter key search
    $('#filterSearch').on('keypress', function(e) {
        if (e.which === 13) loadMembers(1);
    });

    // Init
    loadMembers(1);
    loadLogs();
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
