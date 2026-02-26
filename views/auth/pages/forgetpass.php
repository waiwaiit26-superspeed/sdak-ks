<?php $pageTitle = 'ลืมรหัสผ่าน'; $authBodyClass = 'login-page'; ?>
<?php include ROOT_PATH . 'templates/auth/header.php'; ?>

<div class="login-box">
    <div class="login-logo">
        <a href="<?php echo $basePath; ?>">
            <img id="logoImg" src="" alt="Logo" style="display:none;max-height:72px;" class="mb-2"><br>
            <span id="siteName"><i class="bi bi-mortarboard-fill"></i> <b><?php echo SITE_NAME_SHORT; ?></b></span>
        </a>
    </div>
    <div class="card card-outline card-warning">
        <div class="card-header text-center">
            <h4>ลืมรหัสผ่าน</h4>
            <p class="text-muted mb-0">กรอกอีเมลเพื่อรับลิงก์รีเซ็ตรหัสผ่าน</p>
        </div>
        <div class="card-body login-card-body">
            <!-- Step 1: Enter Email -->
            <div id="stepEmail">
                <form id="forgetPassForm" novalidate>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-warning btn-block" id="btnSubmit">
                                <i class="fas fa-paper-plane mr-1"></i> ส่งลิงก์รีเซ็ตรหัสผ่าน
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Step 2: Success Message -->
            <div id="stepSuccess" style="display:none">
                <div class="text-center">
                    <i class="fas fa-envelope-open-text fa-3x text-success mb-3"></i>
                    <h5>ส่งอีเมลเรียบร้อยแล้ว</h5>
                    <p class="text-muted">
                        กรุณาตรวจสอบอีเมลของคุณเพื่อรีเซ็ตรหัสผ่าน<br>
                        <small>หากไม่พบอีเมล กรุณาตรวจสอบในโฟลเดอร์สแปม</small>
                    </p>
                    <a href="./?page=login" class="btn btn-outline-primary mt-2">
                        <i class="fas fa-sign-in-alt mr-1"></i> กลับไปหน้าเข้าสู่ระบบ
                    </a>
                </div>
            </div>

            <p class="mt-3 mb-1">
                <a href="./?page=login"><i class="bi bi-arrow-left"></i> จำรหัสผ่านได้แล้ว? เข้าสู่ระบบ</a>
            </p>
            <p class="mb-1">
                <a href="./?page=register">ยังไม่มีบัญชี? สมัครสมาชิก</a>
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
    if (API.isLoggedIn()) {
        window.location.href = '../';
        return;
    }

    // Load dynamic logo from settings
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
                const shortName = s.site_name_short || '<?php echo SITE_NAME_SHORT; ?>';
                $('#siteName b').text(shortName);
                document.title = 'ลืมรหัสผ่าน | ' + shortName;
            }
        } catch (e) { /* ignore */ }
    })();

    $('#forgetPassForm').validate({
        rules: {
            email: { required: true, email: true }
        },
        messages: {
            email: { required: 'กรุณากรอกอีเมล', email: 'รูปแบบอีเมลไม่ถูกต้อง' }
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
            const btn = $('#btnSubmit');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');

            const email = $('#email').val().trim();
            API.post(API.apiUrl('auth', 'forget-password'), { email })
                .then(function () {
                    $('#stepEmail').hide();
                    $('#stepSuccess').show();
                })
                .catch(function () {
                    // Show success anyway for security
                    $('#stepEmail').hide();
                    $('#stepSuccess').show();
                });

            return false;
        }
    });
});
</script>

<?php include ROOT_PATH . 'templates/auth/footer.php'; ?>
