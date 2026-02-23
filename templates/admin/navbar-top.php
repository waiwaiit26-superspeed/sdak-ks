<?php
/**
 * Admin Template — Top Navbar (AdminLTE 3)
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="<?php echo $basePath; ?>" class="nav-link" target="_blank">
                <i class="bi bi-house-door me-1"></i> ดูหน้าเว็บ
            </a>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-1"></i> <span id="topNavUsername">Admin</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right">
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#changePasswordModal">
                    <i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-danger" href="#" onclick="App.doLogout(); return false;">
                    <i class="bi bi-box-arrow-right me-2"></i>ออกจากระบบ
                </a>
            </div>
        </li>
    </ul>
</nav>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="changePasswordModalLabel"><i class="bi bi-key me-2"></i>เปลี่ยนรหัสผ่าน</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <form id="changePasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสผ่านปัจจุบัน <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="current_password" required minlength="1">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="current_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">รหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="new_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                        <small class="text-muted">อย่างน้อย 6 ตัวอักษร</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">ยืนยันรหัสผ่านใหม่ <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirm_password" tabindex="-1"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary" id="btnChangePassword">
                        <i class="bi bi-check-lg me-1"></i>เปลี่ยนรหัสผ่าน
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
