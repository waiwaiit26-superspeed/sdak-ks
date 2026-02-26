<?php
/**
 * Member Template — Navbar (AdminLTE 3)
 * Top navigation bar for member pages
 * Variables: $basePath
 */
$basePath = $basePath ?? './';
?>
<!-- Navbar -->
<nav class="main-header navbar navbar-expand-lg navbar-dark" style="background: linear-gradient(135deg, #1a5276 0%, #2980b9 100%);">
    <div class="container">
        <a class="navbar-brand" href="<?php echo $basePath; ?>">
            <i class="bi bi-mortarboard-fill me-1"></i>
            <span class="d-none d-sm-inline"><?php echo siteConfig('site_name_short'); ?></span>
            <span class="d-sm-none"><?php echo siteConfig('site_name_en'); ?></span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-toggle="collapse" data-target="#mainNav">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mr-auto" id="dynamic-nav">
                <!-- Dynamic nav items loaded via JS (loadDynamicNav) -->
            </ul>

            <ul class="navbar-nav ml-auto" id="auth-nav">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $basePath; ?>auth/?page=login">
                        <i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ
                    </a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light btn-sm ms-2 mt-1 mt-lg-0" href="<?php echo $basePath; ?>auth/?page=register">
                        <i class="bi bi-person-plus"></i> สมัครสมาชิก
                    </a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto" id="user-nav" style="display:none;">
                <li class="nav-item" id="nav-admin-link" style="display:none;">
                    <a class="nav-link" href="<?php echo $basePath; ?>admin/">
                        <i class="bi bi-speedometer2"></i> แอดมิน
                    </a>
                </li>

                <!-- Notification Bell -->
                <li class="nav-item dropdown" id="nav-notifications">
                    <a class="nav-link" href="#" role="button" data-toggle="dropdown" title="การแจ้งเตือน">
                        <i class="bi bi-bell" style="font-size:1.15rem;"></i>
                        <span class="badge badge-danger bg-danger badge-pill rounded-pill" id="notif-badge" style="display:none;position:relative;top:-8px;left:-5px;font-size:.65rem;">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" id="notif-dropdown" style="min-width:280px;max-width:320px;">
                        <h6 class="dropdown-header"><i class="bi bi-bell mr-1"></i>การแจ้งเตือน</h6>
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
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" data-toggle="dropdown">
                        <img id="nav-avatar" src="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'><rect fill='%23fff3' width='30' height='30' rx='15'/><text x='15' y='20' text-anchor='middle' fill='white' font-size='14'>👤</text></svg>" 
                             alt="" width="28" height="28" class="rounded-circle">
                        <span id="nav-username">ผู้ใช้</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=profile"><i class="bi bi-person me-2"></i>โปรไฟล์</a></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=fees"><i class="bi bi-cash-coin me-2"></i>ชำระค่าธรรมเนียม</a></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=receipts"><i class="bi bi-receipt me-2"></i>ใบเสร็จของฉัน</a></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=finance" id="nav-finance-link" style="display:none;"><i class="bi bi-wallet2 me-2"></i>บริหารการเงิน</a></li>
                        <li><a class="dropdown-item" href="<?php echo $basePath; ?>member/?page=payment-approval" id="nav-payment-approval-link" style="display:none;"><i class="bi bi-credit-card-2-front me-2"></i>ตรวจสอบการชำระเงิน <span class="badge badge-danger badge-pill" id="nav-pending-payments-badge" style="display:none;">0</span></a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="#" onclick="App.doLogout(); return false;"><i class="bi bi-box-arrow-left me-2"></i>ออกจากระบบ</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
