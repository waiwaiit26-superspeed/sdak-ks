<?php $pageTitle = 'จัดการโลโก้'; $page = 'logo'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

<!-- Cropper Modal -->
<div class="modal fade" id="cropperModal" tabindex="-1" data-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-gradient-purple">
                <h5 class="modal-title"><i class="bi bi-crop me-2"></i>ครอปรูปภาพ</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0" style="max-height:60vh;overflow:hidden;">
                <img id="cropperImage" src="" style="max-width:100%;display:block">
            </div>
            <div class="modal-footer">
                <div class="mr-auto">
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCropFree" title="อิสระ"><i class="bi bi-aspect-ratio"></i> อิสระ</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCrop1to1" title="1:1"><i class="bi bi-square"></i> 1:1</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCrop16to9" title="16:9"><i class="bi bi-display"></i> 16:9</button>
                </div>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="btnCropConfirm"><i class="bi bi-check-lg me-1"></i>ครอปและอัปโหลด</button>
            </div>
        </div>
    </div>
</div>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-image me-2"></i>จัดการโลโก้สมาคม</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li>
                        <li class="breadcrumb-item active">จัดการโลโก้</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="callout callout-info">
                <h5><i class="bi bi-info-circle me-1"></i>คำแนะนำ</h5>
                <p class="mb-0">อัปโหลดหรือระบุ URL ของโลโก้สมาคม โลโก้จะถูกบันทึกเป็นไฟล์ PNG (รองรับพื้นหลังโปร่งใส) สามารถครอปรูปก่อนอัปโหลดได้</p>
            </div>

            <div class="row">
                <!-- Logo เว็บไซต์ -->
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-globe me-1"></i>โลโก้เว็บไซต์</h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted small">ใช้แสดงบนเว็บไซต์และ Sidebar ของ Admin</p>
                            <div class="logo-preview mb-3" id="preview_logo_web">
                                <img src="" class="img-fluid rounded" style="max-height:150px;background:#f8f9fa;padding:10px;" alt="Logo Web">
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="url_logo_web" placeholder="URL ของโลโก้ หรืออัปโหลดไฟล์">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary btn-apply-url" data-target="logo_web" type="button"><i class="bi bi-check-lg"></i></button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <label class="btn btn-primary btn-sm mb-0 mr-2">
                                    <i class="bi bi-upload me-1"></i>อัปโหลด
                                    <input type="file" accept="image/*" class="d-none logo-file-input" data-target="logo_web">
                                </label>
                                <button class="btn btn-danger btn-sm btn-remove-logo" data-target="logo_web"><i class="bi bi-trash me-1"></i>ลบ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo หน้า Login -->
                <div class="col-lg-6">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-box-arrow-in-right me-1"></i>โลโก้หน้า Login</h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted small">แสดงบนหน้าเข้าสู่ระบบและสมัครสมาชิก</p>
                            <div class="logo-preview mb-3" id="preview_logo_login">
                                <img src="" class="img-fluid rounded" style="max-height:150px;background:#f8f9fa;padding:10px;" alt="Logo Login">
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="url_logo_login" placeholder="URL ของโลโก้ หรืออัปโหลดไฟล์">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary btn-apply-url" data-target="logo_login" type="button"><i class="bi bi-check-lg"></i></button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <label class="btn btn-success btn-sm mb-0 mr-2">
                                    <i class="bi bi-upload me-1"></i>อัปโหลด
                                    <input type="file" accept="image/*" class="d-none logo-file-input" data-target="logo_login">
                                </label>
                                <button class="btn btn-danger btn-sm btn-remove-logo" data-target="logo_login"><i class="bi bi-trash me-1"></i>ลบ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Favicon -->
                <div class="col-lg-6">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-star me-1"></i>Favicon (ไอคอนแท็บ)</h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted small">ไอคอนเล็กบนแท็บเบราว์เซอร์ แนะนำขนาดเล็ก สี่เหลี่ยมจัตุรัส</p>
                            <div class="logo-preview mb-3" id="preview_logo_favicon">
                                <img src="" class="img-fluid rounded" style="max-height:100px;background:#f8f9fa;padding:10px;" alt="Favicon">
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="url_logo_favicon" placeholder="URL ของ favicon หรืออัปโหลดไฟล์">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary btn-apply-url" data-target="logo_favicon" type="button"><i class="bi bi-check-lg"></i></button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <label class="btn btn-warning btn-sm mb-0 mr-2">
                                    <i class="bi bi-upload me-1"></i>อัปโหลด
                                    <input type="file" accept="image/*" class="d-none logo-file-input" data-target="logo_favicon">
                                </label>
                                <button class="btn btn-danger btn-sm btn-remove-logo" data-target="logo_favicon"><i class="bi bi-trash me-1"></i>ลบ</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logo ใบเสร็จ -->
                <div class="col-lg-6">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title"><i class="bi bi-receipt me-1"></i>โลโก้ใบเสร็จ</h3>
                        </div>
                        <div class="card-body text-center">
                            <p class="text-muted small">แสดงบนใบเสร็จรับเงินของสมาคม</p>
                            <div class="logo-preview mb-3" id="preview_logo_receipt">
                                <img src="" class="img-fluid rounded" style="max-height:150px;background:#f8f9fa;padding:10px;" alt="Logo Receipt">
                            </div>
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" id="url_logo_receipt" placeholder="URL ของโลโก้ หรืออัปโหลดไฟล์">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary btn-apply-url" data-target="logo_receipt" type="button"><i class="bi bi-check-lg"></i></button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <label class="btn btn-danger btn-sm mb-0 mr-2">
                                    <i class="bi bi-upload me-1"></i>อัปโหลด
                                    <input type="file" accept="image/*" class="d-none logo-file-input" data-target="logo_receipt">
                                </label>
                                <button class="btn btn-danger btn-sm btn-remove-logo" data-target="logo_receipt"><i class="bi bi-trash me-1"></i>ลบ</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<style>
.logo-preview { min-height: 80px; display:flex; align-items:center; justify-content:center; }
.logo-preview img { transition: opacity .3s; }
.logo-preview img[src=""], .logo-preview img:not([src]) { display:none; }
.logo-preview:not(:has(img[src]:not([src=""])))::after {
    content: 'ยังไม่มีโลโก้';
    color: #adb5bd;
    font-size: .9rem;
}
</style>

<script>
$(function () {
    App.requireAdmin();

    const LOGO_KEYS = ['logo_web', 'logo_login', 'logo_favicon', 'logo_receipt'];
    let cropper = null;
    let cropTarget = null;
    let cropFile = null;

    // ─── Load current logos from settings ───
    async function loadLogos() {
        const result = await API.getSettings();
        if (!result.success) return;
        const s = result.data;
        LOGO_KEYS.forEach(key => {
            const val = s[key] || '';
            $(`#url_${key}`).val(val);
            updatePreview(key, val);
        });
    }

    function updatePreview(key, url) {
        const img = $(`#preview_${key} img`);
        if (url) {
            // If it starts with http or //, use as-is; otherwise prepend base path
            const src = (url.startsWith('http') || url.startsWith('//')) ? url : (BASE_PATH + url);
            img.attr('src', src).show();
        } else {
            img.attr('src', '').hide();
        }
    }

    // ─── Save a single logo setting ───
    async function saveLogo(key, url) {
        const data = {};
        data[key] = url;
        const result = await API.updateSettings(data);
        if (result.success) {
            App.success('บันทึกโลโก้สำเร็จ');
            updatePreview(key, url);
        } else {
            App.error(result.message || 'ไม่สามารถบันทึกได้');
        }
    }

    // ─── Apply URL button ───
    $(document).on('click', '.btn-apply-url', function () {
        const key = $(this).data('target');
        const url = $(`#url_${key}`).val().trim();
        saveLogo(key, url);
    });

    // ─── Remove logo ───
    $(document).on('click', '.btn-remove-logo', function () {
        const key = $(this).data('target');
        Swal.fire({
            title: 'ลบโลโก้?',
            text: 'ต้องการลบโลโก้นี้ออก?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก'
        }).then(r => {
            if (r.isConfirmed) {
                $(`#url_${key}`).val('');
                saveLogo(key, '');
            }
        });
    });

    // ─── File upload → open cropper ───
    $(document).on('change', '.logo-file-input', function () {
        const file = this.files[0];
        if (!file) return;
        cropTarget = $(this).data('target');
        cropFile = file;
        openCropper(file);
        $(this).val(''); // reset
    });

    function openCropper(file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            const img = document.getElementById('cropperImage');
            img.src = e.target.result;
            if (cropper) { cropper.destroy(); cropper = null; }
            $('#cropperModal').modal('show');
            $('#cropperModal').one('shown.bs.modal', function () {
                cropper = new Cropper(img, {
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 1,
                    responsive: true,
                    background: true,
                    guides: true,
                });
            });
        };
        reader.readAsDataURL(file);
    }

    // Aspect ratio buttons
    $('#btnCropFree').click(function () { if (cropper) cropper.setAspectRatio(NaN); });
    $('#btnCrop1to1').click(function () { if (cropper) cropper.setAspectRatio(1); });
    $('#btnCrop16to9').click(function () { if (cropper) cropper.setAspectRatio(16/9); });

    // ─── Crop confirm → upload ───
    $('#btnCropConfirm').on('click', async function () {
        if (!cropper || !cropFile) return;
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังอัปโหลด...');

        const cropData = cropper.getData(true);
        const result = await API.uploadLogo(cropFile, cropData);

        if (result.success) {
            const url = result.data.url;
            $(`#url_${cropTarget}`).val(url);
            await saveLogo(cropTarget, url);
            $('#cropperModal').modal('hide');
        } else {
            App.error(result.message || 'อัปโหลดไม่สำเร็จ');
        }
        btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>ครอปและอัปโหลด');
    });

    $('#cropperModal').on('hidden.bs.modal', function () {
        if (cropper) { cropper.destroy(); cropper = null; }
    });

    // ─── Init ───
    loadLogos();
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
