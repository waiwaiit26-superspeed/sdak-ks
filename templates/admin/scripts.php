<?php
/**
 * Admin Template — Scripts (AdminLTE 3)
 * Loads jQuery, Bootstrap, AdminLTE JS, app scripts, admin-specific libraries
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>

<!-- jQuery 3.7.1 -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

<!-- jQuery Validate -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.20.0/dist/additional-methods.min.js"></script>

<!-- Bootstrap 4.6 JS (AdminLTE 3 dependency) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- AdminLTE 3.2 JS -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- App Config & Scripts -->
<script>const BASE_PATH = '<?php echo $basePath; ?>';</script>
<script src="<?php echo $basePath; ?>assets/js/api.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/app.js?v=<?php echo time(); ?>"></script>
<script src="<?php echo $basePath; ?>assets/js/modules.js?v=<?php echo time(); ?>"></script>

<!-- Sortable.js (drag & drop for admin) -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<!-- Select2 (searchable dropdowns) -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Cropper.js (image cropping for admin) -->
<link href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>

<script>

async function adjustSidebarForRole() {
    if (API.isAdmin()) return; // Full admin: show everything

    const res = await API.getMySubAdminPermissions();
    if (!res.success || !res.data || !res.data.is_sub_admin) return;

    const areas = res.data.areas || {};

    // Admin-only items: hide for sub-admins
    $('#nav-member-types, #nav-header-settings, #nav-navigation, #nav-pages, #nav-settings, #nav-logo').hide();
    $('#nav-header-finance, #nav-fees, #nav-receipts, #nav-finance, #nav-sub-admins').hide();
    $('#nav-header-telegram, #nav-telegram-send, #nav-logs').hide();

    // Content items: show only permitted areas
    if (!areas.members)    $('#nav-members').hide();
    if (!areas.news)       $('#nav-news').hide();
    if (!areas.activities) $('#nav-activities').hide();

    // Finance area: show finance menus if permitted
    if (areas.finance && areas.finance.length) {
        $('#nav-header-finance').show();
        $('#nav-fees').show();
        $('#nav-receipts').show();
        $('#nav-finance').show();
    }

    // Hide content header if no content area is permitted
    if (!areas.members && !areas.news && !areas.activities) {
        $('#nav-header-content').hide();
    }
}

$(function() {
    const user = API.getUser();
    if (user) {
        $('#topNavUsername').text(user.full_name || user.username);
    }
    // Load dynamic favicon & sidebar logo
    API.getSettings().then(function(result) {
        if (!result.success || !result.data) return;
        const s = result.data;
        const fav = s.logo_favicon || s.logo_web || '';
        if (fav) {
            const favSrc = fav.startsWith('http') ? fav : (BASE_PATH + fav);
            $('#dynamic-favicon').attr('href', favSrc);
        }
        const webLogo = s.logo_web || '';
        if (webLogo) {
            const logoSrc = webLogo.startsWith('http') ? webLogo : (BASE_PATH + webLogo);
            $('.brand-link .brand-image').replaceWith('<img src="' + logoSrc + '" alt="Logo" class="brand-image" style="max-height:30px;margin:0 .5rem 0 .3rem;border-radius:4px;">');
        }
        // Dynamic page title
        const shortName = s.site_name_short || '';
        if (shortName) {
            const titleParts = document.title.split('|');
            const pagePart = (titleParts[0] || '').trim();
            document.title = pagePart + ' | ' + shortName + ' Admin';
        }
    });

    // Toggle password visibility
    $(document).on('click', '.toggle-password', function() {
        const targetName = $(this).data('target');
        const input = $('input[name="' + targetName + '"]');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Change Password Form
    $('#changePasswordForm').on('submit', function(e) {
        e.preventDefault();
        const curr    = $('input[name="current_password"]').val().trim();
        const newPw   = $('input[name="new_password"]').val().trim();
        const confirm = $('input[name="confirm_password"]').val().trim();

        if (!curr || !newPw) {
            return Swal.fire('', 'กรุณากรอกข้อมูลให้ครบ', 'warning');
        }
        if (newPw.length < 6) {
            return Swal.fire('', 'รหัสผ่านใหม่ต้องมีอย่างน้อย 6 ตัวอักษร', 'warning');
        }
        if (newPw !== confirm) {
            return Swal.fire('', 'รหัสผ่านใหม่และยืนยันรหัสผ่านไม่ตรงกัน', 'warning');
        }

        const btn = $('#btnChangePassword');
        btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>กำลังดำเนินการ...');

        API.post(API.apiUrl('auth', 'change-password'), {
            current_password: curr,
            new_password: newPw,
            confirm_password: confirm
        }).then(function(res) {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>เปลี่ยนรหัสผ่าน');
            if (res.success) {
                $('#changePasswordModal').modal('hide');
                $('#changePasswordForm')[0].reset();
                Swal.fire({
                    icon: 'success',
                    title: 'สำเร็จ',
                    text: res.message || 'เปลี่ยนรหัสผ่านสำเร็จ',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('ผิดพลาด', res.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
            }
        }).catch(function() {
            btn.prop('disabled', false).html('<i class="bi bi-check-lg me-1"></i>เปลี่ยนรหัสผ่าน');
            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาด กรุณาลองใหม่', 'error');
        });
    });

    // Reset form when modal closes
    $('#changePasswordModal').on('hidden.bs.modal', function() {
        $('#changePasswordForm')[0].reset();
        $(this).find('input').attr('type', 'password');
        $(this).find('.toggle-password i').removeClass('bi-eye-slash').addClass('bi-eye');
    });

    // Adjust sidebar based on role (sub-admin sees only permitted menus)
    adjustSidebarForRole();
});
</script>
