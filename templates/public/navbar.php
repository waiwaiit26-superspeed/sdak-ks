<?php
/**
 * Public Template — Navbar
 * Dynamic navigation bar for public pages
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-main sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $basePath; ?>" id="navbar-brand-link">
            <span id="navbar-brand-icon"><i class="bi bi-mortarboard-fill"></i></span>
            <span class="d-none d-sm-inline" id="navbar-brand-text">ส.ร.ม.ก.</span>
            <span class="d-sm-none" id="navbar-brand-text-sm">SDAK-KS</span>
        </a>

        <button class="navbar-toggler border-0 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto" id="dynamic-nav">
                <!-- Dynamic nav items loaded via JS -->
            </ul>

            <div class="d-flex align-items-center gap-2 ms-lg-3 mt-3 mt-lg-0" id="auth-nav">
                <a class="btn btn-light btn-sm text-primary-custom fw-semibold" href="<?php echo $basePath; ?>auth/?page=login">
                    <i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ
                </a>
                <a class="btn btn-light btn-sm text-primary-custom fw-semibold" href="<?php echo $basePath; ?>auth/?page=register">
                    <i class="bi bi-person-plus me-1"></i>สมัครสมาชิก
                </a>
            </div>

            <ul class="navbar-nav" id="user-nav" style="display:none;">
                <li class="nav-item" id="nav-admin-link" style="display:none;">
                    <a class="nav-link" href="<?php echo $basePath; ?>admin/">
                        <i class="bi bi-speedometer2"></i> แอดมิน
                    </a>
                </li>

                <!-- Notification Bell -->
                <li class="nav-item dropdown" id="nav-notifications">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" title="การแจ้งเตือน">
                        <i class="bi bi-bell" style="font-size:1.15rem;"></i>
                        <span class="badge bg-danger rounded-pill" id="notif-badge" style="display:none;position:relative;top:-8px;left:-5px;font-size:.65rem;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" id="notif-dropdown" style="min-width:280px;max-width:320px;">
                        <h6 class="dropdown-header"><i class="bi bi-bell me-1"></i>การแจ้งเตือน</h6>
                        <div class="dropdown-divider"></div>
                        <div id="notif-list">
                            <span class="dropdown-item-text text-muted text-center py-2" style="font-size:.85rem;">ไม่มีการแจ้งเตือน</span>
                        </div>
                    </div>
                </li>

                <!-- Dashboard -->
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>member/?page=home" title="แดชบอร์ด">
                        <i class="bi bi-grid-1x2" style="font-size:1.15rem;"></i>
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                        <img id="nav-avatar" src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'><rect fill='%23fff3' width='30' height='30' rx='15'/><text x='15' y='20' text-anchor='middle' fill='white' font-size='14'>👤</text></svg>" 
                             alt="" width="28" height="28" class="rounded-circle">
                        <span id="nav-username">ผู้ใช้</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=profile"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="App.doLogout(); return false;"><i class="bi bi-box-arrow-left me-2"></i>ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
