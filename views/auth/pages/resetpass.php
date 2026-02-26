<?php $pageTitle = 'รีเซ็ตรหัสผ่าน'; $authBodyClass = 'login-page'; ?>
<?php include ROOT_PATH . 'templates/auth/header.php'; ?>

<div class="login-box">
    <div class="login-logo">
        <a href="<?php echo $basePath; ?>">
            <img id="logoImg" src="" alt="Logo" style="display:none;max-height:72px;" class="mb-2"><br>
            <span id="siteName"><i class="bi bi-mortarboard-fill"></i> <b><?php echo siteConfig('site_name_short'); ?></b></span>
        </a>
    </div>
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <h4><i class="bi bi-key-fill text-primary"></i> ตั้งรหัสผ่านใหม่</h4>
            <p class="text-muted mb-0" id="emailHint"></p>
        </div>
        <div class="card-body login-card-body">

            <!-- Loading -->
            <div id="stepLoading" class="text-center py-4">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">กำลังตรวจสอบลิงก์...</p>
            </div>

            <!-- Invalid Token -->
            <div id="stepInvalid" style="display:none">
                <div class="text-center">
                    <i class="bi bi-x-circle-fill text-danger" style="font-size:3rem;"></i>
                    <h5 class="mt-3 text-danger">ลิงก์ไม่ถูกต้องหรือหมดอายุ</h5>
                    <p class="text-muted">กรุณาขอลิงก์รีเซ็ตรหัสผ่านใหม่อีกครั้ง</p>
                    <a href="./?page=forgetpass" class="btn btn-warning mt-2">
                        <i class="bi bi-arrow-repeat mr-1"></i> ขอลิงก์ใหม่
                    </a>
                </div>
            </div>

            <!-- Reset Form -->
            <div id="stepForm" style="display:none">
                <form id="resetPassForm" novalidate>
                    <input type="hidden" id="resetToken" value="">

                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="newPassword" name="password" placeholder="รหัสผ่านใหม่ (อย่างน้อย 6 ตัว)" required minlength="6">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#newPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="input-group mb-3">
                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="ยืนยันรหัสผ่านใหม่" required>
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="#confirmPassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div id="passwordStrength" class="mb-3" style="display:none;">
                        <div class="progress" style="height:6px;">
                            <div class="progress-bar" id="strengthBar" role="progressbar" style="width:0%"></div>
                        </div>
                        <small id="strengthText" class="text-muted"></small>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block" id="btnReset">
                                <i class="bi bi-check-circle mr-1"></i> เปลี่ยนรหัสผ่าน
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Success -->
            <div id="stepSuccess" style="display:none">
                <div class="text-center">
                    <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                    <h5 class="mt-3 text-success">เปลี่ยนรหัสผ่านสำเร็จ!</h5>
                    <p class="text-muted">กรุณาเข้าสู่ระบบด้วยรหัสผ่านใหม่</p>
                    <a href="./?page=login" class="btn btn-primary mt-2">
                        <i class="bi bi-box-arrow-in-right mr-1"></i> เข้าสู่ระบบ
                    </a>
                </div>
            </div>

            <p class="mt-3 mb-1">
                <a href="./?page=login"><i class="bi bi-arrow-left"></i> กลับไปหน้าเข้าสู่ระบบ</a>
            </p>
            <p class="mb-0">
                <a href="<?php echo $basePath; ?>"><i class="bi bi-house"></i> กลับหน้าหลัก</a>
            </p>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/auth/scripts.php'; ?>

<script>
$(function () {
    // Already logged in?
    if (API.isLoggedIn()) {
        window.location.href = '../';
        return;
    }

    const token = new URLSearchParams(window.location.search).get('token') || '';
    $('#resetToken').val(token);

    // Load settings for logo
    (async function () {
        try {
            const result = await API.getSettings();
            if (result.success && result.data) {
                const s = result.data;
                const logo = s.logo_login || s.logo_web || '';
                if (logo) {
                    const src = logo.startsWith('http') ? logo : ('../' + logo);
                    $('#logoImg').attr('src', src).show();
                }
                const fav = s.logo_favicon || s.logo_web || '';
                if (fav) {
                    const favSrc = fav.startsWith('http') ? fav : ('../' + fav);
                    $('link[rel="icon"]').attr('href', favSrc);
                }
                const shortName = s.site_name_short || '<?php echo siteConfig('site_name_short'); ?>';
                $('#siteName b').text(shortName);
                document.title = 'รีเซ็ตรหัสผ่าน | ' + shortName;
            }
        } catch (e) { /* ignore */ }
    })();

    // Verify token
    if (!token) {
        $('#stepLoading').hide();
        $('#stepInvalid').show();
        return;
    }

    API.get(API.apiUrl('auth', 'verify-reset-token') + '&token=' + encodeURIComponent(token))
        .then(function (result) {
            if (result.success) {
                $('#stepLoading').hide();
                $('#stepForm').show();
                if (result.data && result.data.email) {
                    $('#emailHint').html('สำหรับบัญชี: <strong>' + result.data.email + '</strong>');
                }
            } else {
                $('#stepLoading').hide();
                $('#stepInvalid').show();
            }
        })
        .catch(function () {
            $('#stepLoading').hide();
            $('#stepInvalid').show();
        });

    // Toggle password visibility
    $(document).on('click', '.toggle-password', function () {
        const target = $($(this).data('target'));
        const icon = $(this).find('i');
        if (target.attr('type') === 'password') {
            target.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            target.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });

    // Password strength
    $('#newPassword').on('input', function () {
        const pw = $(this).val();
        const bar = $('#strengthBar');
        const txt = $('#strengthText');
        const wrap = $('#passwordStrength');

        if (!pw) { wrap.hide(); return; }
        wrap.show();

        let score = 0;
        if (pw.length >= 6) score++;
        if (pw.length >= 8) score++;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) score++;
        if (/\d/.test(pw)) score++;
        if (/[^a-zA-Z0-9]/.test(pw)) score++;

        const levels = [
            { w: 20, cls: 'bg-danger', text: 'อ่อนมาก' },
            { w: 40, cls: 'bg-warning', text: 'อ่อน' },
            { w: 60, cls: 'bg-info', text: 'ปานกลาง' },
            { w: 80, cls: 'bg-primary', text: 'แข็งแรง' },
            { w: 100, cls: 'bg-success', text: 'แข็งแรงมาก' },
        ];
        const level = levels[Math.min(score, levels.length - 1)];
        bar.css('width', level.w + '%').attr('class', 'progress-bar ' + level.cls);
        txt.text('ความแข็งแรงรหัสผ่าน: ' + level.text);
    });

    // Validate & Submit
    $('#resetPassForm').validate({
        rules: {
            password: { required: true, minlength: 6 },
            confirm_password: { required: true, equalTo: '#newPassword' }
        },
        messages: {
            password: { required: 'กรุณากรอกรหัสผ่านใหม่', minlength: 'อย่างน้อย 6 ตัวอักษร' },
            confirm_password: { required: 'กรุณายืนยันรหัสผ่าน', equalTo: 'รหัสผ่านไม่ตรงกัน' }
        },
        errorClass: 'is-invalid',
        validClass: 'is-valid',
        errorPlacement(error, element) {
            error.addClass('invalid-feedback');
            element.closest('.input-group').length
                ? error.insertAfter(element.closest('.input-group'))
                : error.insertAfter(element);
        },
        highlight(el) { $(el).addClass('is-invalid').removeClass('is-valid'); },
        unhighlight(el) { $(el).removeClass('is-invalid').addClass('is-valid'); },
        submitHandler: function () {
            const btn = $('#btnReset');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังดำเนินการ...');

            API.post(API.apiUrl('auth', 'reset-password'), {
                token: $('#resetToken').val(),
                password: $('#newPassword').val()
            })
            .then(function (result) {
                if (result.success) {
                    $('#stepForm').hide();
                    $('#stepSuccess').show();
                } else {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle mr-1"></i> เปลี่ยนรหัสผ่าน');
                    Swal.fire('ผิดพลาด', result.message || 'ไม่สามารถเปลี่ยนรหัสผ่านได้', 'error');
                }
            })
            .catch(function (err) {
                btn.prop('disabled', false).html('<i class="bi bi-check-circle mr-1"></i> เปลี่ยนรหัสผ่าน');
                const msg = (err.responseJSON && err.responseJSON.message) || 'ลิงก์หมดอายุหรือไม่ถูกต้อง กรุณาขอลิงก์ใหม่';
                Swal.fire('ผิดพลาด', msg, 'error');
            });

            return false;
        }
    });
});
</script>

<?php include ROOT_PATH . 'templates/auth/footer.php'; ?>
