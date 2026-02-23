<?php $pageTitle = 'จัดการหน้าเพจ'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-file-earmark-text me-2"></i>จัดการหน้าเพจ</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">หน้าเพจ</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" onclick="openPageForm()">
                    <i class="bi bi-plus-lg me-1"></i> สร้างหน้าเพจ
                </button>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" id="searchInput" placeholder="ค้นหาหน้าเพจ..." onkeyup="debounceSearch()">
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="statusFilter" onchange="loadPagesList()">
                                <option value="">ทั้งหมด</option>
                                <option value="published">เผยแพร่</option>
                                <option value="draft">แบบร่าง</option>
                                <option value="archived">เก็บถาวร</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pages Table -->
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>ชื่อหน้า</th>
                                    <th>Slug</th>
                                    <th>สถานะ</th>
                                    <th>วันที่สร้าง</th>
                                    <th class="text-end">จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="pagesTableBody">
                                <tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <nav class="mt-3" id="pagesPagination"></nav>
        </div>
    </div>

<!-- Page Form Modal (Large) -->
<div class="modal fade" id="pageFormModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pageFormTitle">สร้างหน้าเพจ</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form id="pageForm">
                <div class="modal-body">
                    <input type="hidden" id="pgId" name="id">

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">ชื่อหน้า <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="pgTitle" name="title" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">เนื้อหา</label>
                                <textarea class="form-control" id="pgContent" name="content" rows="15"></textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">สถานะ</label>
                                <select class="form-control" id="pgStatus" name="status">
                                    <option value="draft">แบบร่าง</option>
                                    <option value="published">เผยแพร่</option>
                                    <option value="archived">เก็บถาวร</option>
                                </select>
                            </div>

                            <!-- Module Shortcodes -->
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-puzzle me-1"></i>แทรกโมดูล</label>
                                <div class="list-group list-group-flush">
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" onclick="insertShortcode('[news]')">
                                        <span><i class="bi bi-newspaper me-2 text-primary"></i>ข่าวสาร</span>
                                        <code class="small">[news]</code>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" onclick="insertShortcode('[news limit=4]')">
                                        <span><i class="bi bi-newspaper me-2 text-info"></i>ข่าวสาร 4 รายการ</span>
                                        <code class="small">[news limit=4]</code>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" onclick="insertShortcode('[activities]')">
                                        <span><i class="bi bi-calendar-event me-2 text-success"></i>กิจกรรม</span>
                                        <code class="small">[activities]</code>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" onclick="insertShortcode('[activities limit=4]')">
                                        <span><i class="bi bi-calendar-event me-2 text-info"></i>กิจกรรม 4 รายการ</span>
                                        <code class="small">[activities limit=4]</code>
                                    </button>
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2" onclick="insertShortcode('[activities limit=4 upcoming=1]')">
                                        <span><i class="bi bi-calendar-event me-2 text-warning"></i>กิจกรรมใกล้ถึง</span>
                                        <code class="small">[activities upcoming=1]</code>
                                    </button>
                                </div>
                                <small class="text-muted mt-1 d-block">คลิกเพื่อแทรกโค้ดลงในเนื้อหา</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Meta Description</label>
                                <textarea class="form-control" id="pgMeta" name="meta_description" rows="3" maxlength="160"></textarea>
                                <small class="text-muted"><span id="metaCount">0</span>/160</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รูปปก (Cover Image URL)</label>
                                <input type="text" class="form-control" id="pgCover" name="cover_image" placeholder="URL รูปภาพ">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
let currentPage = 1;
let searchTimer = null;

$(function() {
    App.requireAdmin();
    loadPagesList();
    initPageForm();

    $('#pgMeta').on('input', function() {
        $('#metaCount').text($(this).val().length);
    });
});

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadPagesList(), 400);
}

async function loadPagesList(pg) {
    currentPage = pg || 1;
    const $tbody = $('#pagesTableBody');
    $tbody.html('<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-primary"></div></td></tr>');

    try {
        const params = {
            page: currentPage,
            per_page: 15,
            search: $('#searchInput').val() || '',
            status: $('#statusFilter').val() || ''
        };

        const res = await API.getPages(params);
        if (res.success) {
            const items = res.data?.data || res.data || [];
            const pagination = res.data?.pagination;

            if (!items.length) {
                $tbody.html('<tr><td colspan="6" class="text-center py-5 text-muted">ไม่มีรายการ</td></tr>');
                $('#pagesPagination').empty();
                return;
            }

            let html = '';
            items.forEach((p, i) => {
                const statusBadge = getStatusBadge(p.status);
                const date = p.created_at ? new Date(p.created_at).toLocaleDateString('th-TH') : '-';
                const num = (pagination ? (pagination.current_page - 1) * pagination.per_page : 0) + i + 1;

                html += `<tr>
                    <td>${num}</td>
                    <td><strong>${App.escapeHtml(p.title)}</strong></td>
                    <td><code>${App.escapeHtml(p.slug || '')}</code></td>
                    <td>${statusBadge}</td>
                    <td>${date}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-info me-1" onclick="previewPage('${p.slug}')" title="ดูตัวอย่าง">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editPage(${p.id})" title="แก้ไข">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deletePage(${p.id}, '${App.escapeHtml(p.title)}')" title="ลบ">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });
            $tbody.html(html);

            if (pagination && pagination.total_pages > 1) {
                App.buildPagination(pagination, 'loadPagesList');
            } else {
                $('#pagesPagination').empty();
            }
        }
    } catch (e) {
        console.error(e);
        $tbody.html('<tr><td colspan="6" class="text-center py-5 text-danger">เกิดข้อผิดพลาด</td></tr>');
    }
}

function getStatusBadge(status) {
    const map = {
        published: '<span class="badge bg-success">เผยแพร่</span>',
        draft: '<span class="badge bg-warning text-dark">แบบร่าง</span>',
        archived: '<span class="badge bg-secondary">เก็บถาวร</span>'
    };
    return map[status] || `<span class="badge bg-light text-dark">${status}</span>`;
}

function openPageForm(data) {
    const isEdit = data && data.id;
    $('#pageFormTitle').text(isEdit ? 'แก้ไขหน้าเพจ' : 'สร้างหน้าเพจ');
    $('#pageForm')[0].reset();
    $('#pgId').val('');
    $('#metaCount').text('0');

    if (isEdit) {
        $('#pgId').val(data.id);
        $('#pgTitle').val(data.title || '');
        $('#pgContent').val(data.content || '');
        $('#pgStatus').val(data.status || 'draft');
        $('#pgMeta').val(data.meta_description || '');
        $('#pgCover').val(data.cover_image || '');
        $('#metaCount').text((data.meta_description || '').length);
    }

    $('#pageFormModal').modal('show');
}

async function editPage(id) {
    try {
        const res = await API.getPageDetail(id);
        if (res.success && res.data) {
            openPageForm(res.data);
        } else {
            App.error('ไม่พบข้อมูล');
        }
    } catch (e) {
        App.error('เกิดข้อผิดพลาด');
    }
}

function insertShortcode(code) {
    const $textarea = $('#pgContent');
    const textarea = $textarea[0];
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = $textarea.val();
    const before = text.substring(0, start);
    const after = text.substring(end);
    const newLine = before && !before.endsWith('\n') ? '\n' : '';
    $textarea.val(before + newLine + code + '\n' + after);
    textarea.selectionStart = textarea.selectionEnd = start + newLine.length + code.length + 1;
    textarea.focus();
    App.success('แทรก ' + code + ' แล้ว');
}

function previewPage(slug) {
    window.open(BASE_PATH + 'web/?page=dynamic&slug=' + slug, '_blank');
}

async function deletePage(id, title) {
    const result = await Swal.fire({
        title: 'ลบหน้าเพจ?',
        html: `คุณต้องการลบหน้าเพจ "<b>${title}</b>" ?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'ลบ',
        cancelButtonText: 'ยกเลิก'
    });

    if (result.isConfirmed) {
        try {
            const res = await API.deletePage(id);
            if (res.success) {
                App.success('ลบหน้าเพจเรียบร้อย');
                loadPagesList(currentPage);
            } else {
                App.error(res.message || 'เกิดข้อผิดพลาด');
            }
        } catch (e) {
            App.error('เกิดข้อผิดพลาด');
        }
    }
}

function initPageForm() {
    $('#pageForm').on('submit', async function(e) {
        e.preventDefault();

        const id = $('#pgId').val();
        const data = {
            title: $('#pgTitle').val().trim(),
            content: $('#pgContent').val(),
            status: $('#pgStatus').val(),
            meta_description: $('#pgMeta').val().trim(),
            cover_image: $('#pgCover').val().trim()
        };

        if (!data.title) {
            App.error('กรุณากรอกชื่อหน้าเพจ');
            return;
        }

        try {
            let res;
            if (id) {
                data.id = parseInt(id);
                res = await API.updatePage(data);
            } else {
                res = await API.createPage(data);
            }

            if (res.success) {
                $('#pageFormModal').modal('hide');
                App.success(id ? 'แก้ไขเรียบร้อย' : 'สร้างหน้าเพจเรียบร้อย');
                loadPagesList(currentPage);
            } else {
                App.error(res.message || 'เกิดข้อผิดพลาด');
            }
        } catch (e) {
            App.error('เกิดข้อผิดพลาด');
        }
    });
}
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
