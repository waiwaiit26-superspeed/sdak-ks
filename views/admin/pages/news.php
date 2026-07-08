<?php $pageTitle = 'จัดการข่าวสาร'; $page = 'news'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-newspaper me-2"></i>จัดการข่าวสาร</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">ข่าวสาร</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">
            <div class="d-flex justify-content-end mb-3">
                <button class="btn btn-primary" onclick="openNewsForm()">
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มข่าว
                </button>
            </div>

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <select id="filterStatus" class="form-control form-control-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="published">เผยแพร่แล้ว</option>
                                <option value="draft">แบบร่าง</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <input type="text" id="searchNews" class="form-control form-control-sm" placeholder="ค้นหาข่าว...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-sm w-100" onclick="loadNews(1)"><i class="bi bi-search"></i> ค้นหา</button>
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
                                    <th>หัวข้อ</th>
                                    <th>สถานะ</th>
                                    <th>ผู้เขียน</th>
                                    <th>เข้าชม</th>
                                    <th>วันที่</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody id="newsTable">
                                <tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer"><nav id="newsPagination"></nav></div>
            </div>
        </div>
    </div>

<!-- Modal: News Form -->
<div class="modal fade" id="newsFormModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newsFormTitle">เพิ่มข่าว</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="newsForm" novalidate>
                    <input type="hidden" id="newsId" name="id">
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <label class="form-label">หัวข้อข่าว <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="newsTitle" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">สถานะ</label>
                            <select class="form-control" name="status" id="newsStatus">
                                <option value="draft">แบบร่าง</option>
                                <option value="published">เผยแพร่</option>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">ข้อความย่อ</label>
                            <textarea class="form-control" name="excerpt" id="newsExcerpt" rows="2" maxlength="300"></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">เนื้อหา <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="content" id="newsContent" rows="12" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">ภาพปก</label>
                            <!-- Tab: เลือกวิธีใส่รูป -->
                            <ul class="nav nav-tabs nav-tabs-sm mb-2" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#newsCoverUploadTab">อัปโหลดรูป</a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#newsCoverUrlTab">ลิงก์รูปภาพ</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="newsCoverUploadTab">
                                    <input type="file" class="form-control form-control-sm" id="newsCoverFile" accept="image/*">
                                    <small class="text-muted">รองรับ JPEG, PNG, GIF, WEBP (สูงสุด 10 MB) จะถูก crop และบีบอัดอัตโนมัติ</small>
                                </div>
                                <div class="tab-pane fade" id="newsCoverUrlTab">
                                    <div class="input-group input-group-sm">
                                        <input type="url" class="form-control" id="newsCoverLinkInput" placeholder="https://example.com/image.jpg">
                                        <button class="btn btn-outline-primary" type="button" id="btnNewsCoverLink"><i class="bi bi-check-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="cover_image" id="newsCoverUrl">
                            <div id="coverPreview" class="mt-2 position-relative" style="display:none">
                                <img id="coverImg" src="" class="img-fluid rounded" style="max-height:150px" alt="cover">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" onclick="removeCover('news')" title="ลบรูปปก"><i class="bi bi-x"></i></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnSaveNews">
                    <i class="bi bi-check-lg me-1"></i> บันทึก
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
    loadNews();
});

async function loadNews(page = 1) {
    currentPage = page;
    const tbody = $('#newsTable');
    tbody.html('<tr><td colspan="7" class="text-center py-4"><span class="spinner-border spinner-border-sm"></span></td></tr>');

    const params = { page, per_page: 20 };
    const status = $('#filterStatus').val();
    const search = $('#searchNews').val().trim();
    if (status) params.status = status;
    if (search) params.search = search;

    const result = await API.getNewsList(params);
    if (!result.success || !result.data || result.data.length === 0) {
        tbody.html('<tr><td colspan="7" class="text-center py-4 text-muted">ไม่พบข่าวสาร</td></tr>');
        return;
    }

    let html = '';
    result.data.forEach((n, i) => {
        const idx = (currentPage - 1) * 20 + i + 1;
        const statusBadge = n.status === 'published'
            ? '<span class="badge bg-success">เผยแพร่</span>'
            : '<span class="badge bg-secondary">แบบร่าง</span>';
        html += `<tr>
            <td>${idx}</td>
            <td><a href="../web/?page=news-detail&id=${n.id}" target="_blank">${n.title}</a></td>
            <td>${statusBadge}</td>
            <td>${n.author_name || '-'}</td>
            <td>${App.formatNumber(n.views || 0)}</td>
            <td>${App.formatDate(n.created_at)}</td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" onclick="editNews(${n.id})" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-outline-danger" onclick="deleteNews(${n.id},'${n.title.replace(/'/g, "\\'")}')" title="ลบ"><i class="bi bi-trash"></i></button>
                </div>
            </td>
        </tr>`;
    });
    tbody.html(html);
    if (result.pagination) App.buildPagination('#newsPagination', result.pagination, loadNews);
}

function openNewsForm(data = null) {
    const form = $('#newsForm')[0];
    form.reset();
    $('#newsId').val('');
    $('#coverPreview').hide();
    $('#newsCoverUrl').val('');

    if (data) {
        $('#newsFormTitle').text('แก้ไขข่าว');
        $('#newsId').val(data.id);
        $('#newsTitle').val(data.title);
        $('#newsExcerpt').val(data.excerpt);
        $('#newsContent').val(data.content);
        $('#newsStatus').val(data.status);
        if (data.cover_image) {
            $('#newsCoverUrl').val(data.cover_image);
            $('#coverImg').attr('src', App.imgUrl(data.cover_image));
            $('#coverPreview').show();
        }
    } else {
        $('#newsFormTitle').text('เพิ่มข่าว');
    }

    $('#newsFormModal').modal('show');
}

async function editNews(id) {
    const result = await API.getNewsDetail(id);
    if (result.success) openNewsForm(result.data);
    else App.error(result.message);
}

async function deleteNews(id, title) {
    const ok = await App.confirm(`ต้องการลบข่าว "${title}" หรือไม่?`);
    if (!ok) return;
    const result = await API.deleteNews(id);
    if (result.success) { App.success('ลบข่าวสำเร็จ'); loadNews(currentPage); }
    else App.error(result.message);
}

// Upload cover image with crop
$('#newsCoverFile').on('change', function () {
    const file = this.files[0];
    if (!file) return;
    if (file.size > 10 * 1024 * 1024) { App.error('ไฟล์ใหญ่เกินไป (สูงสุด 10 MB)'); this.value = ''; return; }
    showCropper(file, 'news');
});

// Cover image URL
$('#btnNewsCoverLink').on('click', function () {
    const url = $('#newsCoverLinkInput').val().trim();
    if (!url) { App.error('กรุณากรอก URL รูปภาพ'); return; }
    $('#newsCoverUrl').val(url);
    $('#coverImg').attr('src', App.imgUrl(url));
    $('#coverPreview').show();
    App.success('ใส่ลิงก์รูปปกสำเร็จ');
});

function removeCover(type) {
    if (type === 'news') {
        $('#newsCoverUrl').val('');
        $('#newsCoverFile').val('');
        $('#coverPreview').hide();
    } else {
        $('#actCoverUrl').val('');
        $('#actCoverFile').val('');
        $('#actCoverPreview').hide();
    }
}

// Save news
$('#btnSaveNews').on('click', async function () {
    const title = $('#newsTitle').val().trim();
    const content = $('#newsContent').val().trim();
    if (!title) { App.error('กรุณากรอกหัวข้อข่าว'); return; }
    if (!content) { App.error('กรุณากรอกเนื้อหา'); return; }

    const btn = $(this);
    btn.prop('disabled', true);

    const data = {
        title,
        excerpt: $('#newsExcerpt').val().trim(),
        content,
        status: $('#newsStatus').val(),
        cover_image: $('#newsCoverUrl').val()
    };

    const newsId = $('#newsId').val();
    let result;
    if (newsId) {
        data.id = parseInt(newsId);
        result = await API.updateNews(data);
    } else {
        result = await API.createNews(data);
    }

    if (result.success) {
        $('#newsFormModal').modal('hide');
        App.success(result.message);
        loadNews(currentPage);
    } else {
        App.error(result.message);
    }
    btn.prop('disabled', false);
});

$('#filterStatus').on('change', () => loadNews(1));
let searchTimer;
$('#searchNews').on('input', function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => loadNews(1), 400);
});

// ─── Cropper Logic ───
let cropper = null;
let cropFile = null;
let cropTarget = 'news'; // 'news' or 'activities'

function showCropper(file, target) {
    cropFile = file;
    cropTarget = target;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('cropperImage');
        img.src = e.target.result;

        // Destroy old cropper
        if (cropper) { cropper.destroy(); cropper = null; }

        $('#cropperModal').modal('show');

        // Init cropper after modal is shown
        $('#cropperModal').one('shown.bs.modal', function () {
            cropper = new Cropper(img, {
                aspectRatio: 1200 / 630,  // 1.91:1 cover ratio
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

    const cropData = cropper.getData(true); // rounded pixel values

    const formData = new FormData();
    formData.append('file', cropFile);
    formData.append('cropX', cropData.x);
    formData.append('cropY', cropData.y);
    formData.append('cropWidth', cropData.width);
    formData.append('cropHeight', cropData.height);

    const type = cropTarget === 'news' ? 'news' : 'activities';

    try {
        const token = API.getToken();
        const headers = {};
        if (token) headers['X-Auth-Token'] = token;

        const response = await fetch(API.baseUrl + API.apiUrl('upload', 'image', { type }), {
            method: 'POST',
            headers,
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            if (cropTarget === 'news') {
                $('#newsCoverUrl').val(result.data.url);
                $('#coverImg').attr('src', App.imgUrl(result.data.url));
                $('#coverPreview').show();
            } else {
                $('#actCoverUrl').val(result.data.url);
                $('#actCoverImg').attr('src', App.imgUrl(result.data.url));
                $('#actCoverPreview').show();
            }
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

// Cleanup cropper on modal close
$('#cropperModal').on('hidden.bs.modal', function () {
    if (cropper) { cropper.destroy(); cropper = null; }
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
