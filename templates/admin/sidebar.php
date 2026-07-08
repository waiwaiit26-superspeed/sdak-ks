<?php
/**
 * Admin Template — Sidebar (AdminLTE 3)
 * Admin navigation sidebar
 * Variables: $page (current page for active state)
 */
$page = $page ?? '';
?>
<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="./?page=dashboard" class="brand-link">
        <i class="bi bi-mortarboard-fill brand-image" style="font-size:1.5rem;margin:0 .5rem 0 .3rem;color:#fff;"></i>
        <span class="brand-text font-weight-bold"><?php echo siteConfig('site_name_short'); ?> Admin</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent" data-widget="treeview" role="menu">
                <li class="nav-item">
                    <a href="./?page=dashboard" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>แผงควบคุม</p>
                    </a>
                </li>

                <li class="nav-header" id="nav-header-content">จัดการเนื้อหา</li>
                <li class="nav-item" id="nav-members">
                    <a href="./?page=members" class="nav-link <?= $page === 'members' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-people"></i>
                        <p>จัดการสมาชิก</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-member-types">
                    <a href="./?page=member-types" class="nav-link <?= $page === 'member-types' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>ประเภทสมาชิก</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-news">
                    <a href="./?page=news" class="nav-link <?= $page === 'news' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-newspaper"></i>
                        <p>จัดการข่าวสาร</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-activities">
                    <a href="./?page=activities" class="nav-link <?= $page === 'activities' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-calendar-event"></i>
                        <p>จัดการกิจกรรม</p>
                    </a>
                </li>

                <li class="nav-header" id="nav-header-settings">ตั้งค่าเว็บไซต์</li>
                <li class="nav-item" id="nav-navigation">
                    <a href="./?page=navigation" class="nav-link <?= $page === 'navigation' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-list"></i>
                        <p>จัดการเมนู</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-pages">
                    <a href="./?page=pages" class="nav-link <?= $page === 'pages' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-file-earmark-text"></i>
                        <p>จัดการหน้าเพจ</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-settings">
                    <a href="./?page=settings" class="nav-link <?= $page === 'settings' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>ตั้งค่าระบบ</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-logo">
                    <a href="./?page=logo" class="nav-link <?= $page === 'logo' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-image"></i>
                        <p>จัดการโลโก้</p>
                    </a>
                </li>

                <li class="nav-header" id="nav-header-finance">การเงิน</li>
                <li class="nav-item" id="nav-fees">
                    <a href="./?page=fees" class="nav-link <?= $page === 'fees' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-cash-coin"></i>
                        <p>ค่าธรรมเนียมสมาชิก</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-receipts">
                    <a href="./?page=receipts" class="nav-link <?= $page === 'receipts' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-receipt"></i>
                        <p>ใบเสร็จรับเงิน</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-finance">
                    <a href="./?page=finance" class="nav-link <?= $page === 'finance' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-wallet2"></i>
                        <p>บริหารการเงิน</p>
                    </a>
                </li>
                <li class="nav-item" id="nav-sub-admins">
                    <a href="./?page=sub-admins" class="nav-link <?= $page === 'sub-admins' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-person-gear"></i>
                        <p>จัดการสิทธิ์ผู้ดูแล</p>
                    </a>
                </li>

                <li class="nav-header" id="nav-header-telegram">Telegram</li>
                <li class="nav-item" id="nav-telegram-send">
                    <a href="./?page=telegram-send" class="nav-link <?= $page === 'telegram-send' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-send"></i>
                        <p>ส่งข่าว Telegram</p>
                    </a>
                </li>

                <li class="nav-header">อื่นๆ</li>
                <li class="nav-item" id="nav-logs">
                    <a href="./?page=logs" class="nav-link <?= $page === 'logs' ? 'active' : '' ?>">
                        <i class="nav-icon bi bi-clock-history"></i>
                        <p>ประวัติการใช้งาน</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $basePath; ?>" class="nav-link">
                        <i class="nav-icon bi bi-house"></i>
                        <p>กลับหน้าเว็บ</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="App.doLogout(); return false;">
                        <i class="nav-icon bi bi-box-arrow-right"></i>
                        <p>ออกจากระบบ</p>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>
