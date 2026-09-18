<?php
define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'config/database.php';
$pageTitle = 'หน้าแรก | ' . siteConfig('site_name_short') . ' ' . siteConfig('site_name_en');
$currentPage = 'home';
include ROOT_PATH . 'templates/public/header.php';
?>

<style>
.activity-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.activity-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1) !important;
}
.activity-cover {
    position: relative;
    overflow: hidden;
}
.activity-date {
    background: linear-gradient(135deg, var(--primary-custom), var(--primary-light));
    color: white;
    border-radius: 8px;
    padding: 8px 12px;
    text-align: center;
    min-width: 50px;
    font-weight: bold;
}
.activity-date .day {
    font-size: 1.2rem;
    line-height: 1;
}
.activity-date .month {
    font-size: 0.7rem;
    opacity: 0.9;
    margin-top: 2px;
}
</style>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center hero-content">
            <div class="col-lg-7">
                <span class="badge badge-hero mb-3" id="hero-badge"><i class="bi bi-mortarboard-fill me-1"></i> <span id="hero-badge-text"><?php echo siteConfig('site_name_en'); ?></span></span>
                <h1 class="mb-3" id="hero-title"><?php echo htmlspecialchars(siteConfig('site_name')); ?></h1>
                <p class="lead mb-4" id="hero-subtitle"><?php echo htmlspecialchars(siteConfig('site_name_short')); ?> — <?php echo htmlspecialchars(siteConfig('site_name_en')); ?></p>
                <div class="d-flex gap-3 flex-wrap" id="homeHeroGuestActions">
                    <a href="./auth/?page=login" class="btn btn-light btn-lg px-4 fw-bold text-primary-custom">
                        <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                    </a>
                    <a href="./auth/?page=register" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-person-plus me-2"></i>สมัครสมาชิก
                    </a>
                </div>
                <div class="d-flex gap-3 flex-wrap mt-3" id="homeHeroCommonActions">
                    <a href="#about" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-info-circle me-2"></i>เกี่ยวกับเรา
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center mt-4 mt-lg-0">
                <div class="p-4">
                    <i class="bi bi-building text-white" style="font-size:8rem;opacity:.15;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-4" style="margin-top:-3rem;position:relative;z-index:2;">
    <div class="container">
        <div class="row g-3" id="home-stats">
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <div class="stats-number" id="stat-members">-</div>
                    <div class="stats-label">สมาชิกทั้งหมด</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <div class="stats-number" id="stat-news">-</div>
                    <div class="stats-label">ข่าวประชาสัมพันธ์</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <div class="stats-number" id="stat-activities">-</div>
                    <div class="stats-label">กิจกรรม</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stats-card">
                    <div class="stats-number" id="stat-schools">-</div>
                    <div class="stats-label">โรงเรียน/หน่วยงาน</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">เกี่ยวกับสมาคม</h2>
            <div class="section-divider mx-auto"></div>
            <p class="section-subtitle" id="about-subtitle"><?php echo htmlspecialchars(siteConfig('site_name_short') . ' ' . siteConfig('site_name')); ?></p>
        </div>

        <div class="row g-4" id="member-types-container">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</section>

<!-- Upcoming Activities -->
<section class="section-padding bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title mb-1">กิจกรรมที่กำลังจะมาถึง</h2>
                <div class="section-divider"></div>
            </div>
            <a href="./web/?page=activities" class="btn btn-outline-primary">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4" id="upcoming-activities">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</section>

<!-- Latest News -->
<section class="section-padding bg-white">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title mb-1">ข่าวประชาสัมพันธ์</h2>
                <div class="section-divider"></div>
            </div>
            <a href="./web/?page=news" class="btn btn-outline-primary">ดูทั้งหมด <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="row g-4" id="latest-news">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section-padding bg-gradient-primary text-white text-center">
    <div class="container">
        <h2 class="fw-bold mb-3" id="cta-title">ร่วมเป็นส่วนหนึ่งของ <?php echo htmlspecialchars(siteConfig('site_name_short')); ?></h2>
        <p class="lead mb-4 opacity-75" id="cta-subtitle">สมัครสมาชิกวันนี้ เพื่อร่วมเป็นส่วนหนึ่งของ<?php echo htmlspecialchars(siteConfig('site_name')); ?></p>
        <a href="./auth/?page=register" class="btn btn-light btn-lg px-5 fw-bold text-primary-custom" id="homeCtaRegisterBtn">
            <i class="bi bi-person-plus me-2"></i>สมัครสมาชิก
        </a>
        <a href="./member/?page=home" class="btn btn-light btn-lg px-5 fw-bold text-primary-custom" id="homeCtaDashboardBtn" style="display:none;">
            <i class="bi bi-speedometer2 me-2"></i>ไปยังแดชบอร์ด
        </a>
    </div>
</section>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(document).ready(function() {
    toggleHomeGuestCtas();
    loadHeroFromSettings();
    loadMemberTypesSection();
    loadLatestNews();
    loadUpcomingActivities();
    loadPublicStats();
});

function toggleHomeGuestCtas() {
    // Token exists but cached user is missing → recover from server, then re-check
    if (!API.getUser() && API.isLoggedIn()) {
        API.get(API.apiUrl('auth', 'me')).then(res => {
            if (res.success && res.data) {
                localStorage.setItem('sdak_user', JSON.stringify(res.data));
                toggleHomeGuestCtas();
            }
        }).catch(() => {});
    }

    const isLoggedIn = !!(API.getUser() || API.isLoggedIn());
    if (isLoggedIn) {
        $('#homeHeroGuestActions').hide();
        $('#homeCtaRegisterBtn').hide();
        $('#homeCtaDashboardBtn').show();
    } else {
        $('#homeHeroGuestActions').show();
        $('#homeCtaRegisterBtn').show();
        $('#homeCtaDashboardBtn').hide();
    }
}

async function loadMemberTypesSection() {
    try {
        const res = await API.getMemberTypes();
        if (!res.success || !Array.isArray(res.data)) return;
        
        let html = '';
        res.data.forEach(function(type) {
            html += `
                <div class="col-md-6 col-lg-3">
                    <div class="feature-card h-100">
                        <div class="icon-wrapper" style="background:${type.icon_bg || '#a78bfa'};color:${type.icon_color || '#3b0764'};">
                            <i class="bi ${type.icon || 'bi-person-fill'}"></i>
                        </div>
                        <h5>${type.label}</h5>
                        <p class="text-muted">${type.description || ''}</p>
                    </div>
                </div>
            `;
        });
        
        const container = document.getElementById('member-types-container');
        if (container) container.innerHTML = html;
    } catch (e) {
        console.error('Error loading member types:', e);
    }
}

async function loadHeroFromSettings() {
    try {
        const res = await API.getSettings();
        if (!res.success || !res.data) return;
        const s = res.data;

        // Hero badge
        if (s.hero_badge) {
            $('#hero-badge-text').text(s.hero_badge);
        } else if (s.site_name_en) {
            $('#hero-badge-text').text(s.site_name_en);
        }

        // Hero title (supports \n for line breaks)
        if (s.hero_title) {
            $('#hero-title').html(s.hero_title.replace(/\n/g, '<br>'));
        } else if (s.site_name) {
            $('#hero-title').text(s.site_name);
        }

        // Hero subtitle (supports \n for line breaks)
        if (s.hero_subtitle) {
            $('#hero-subtitle').html(s.hero_subtitle.replace(/\n/g, '<br>'));
        } else {
            const short = s.site_name_short || '';
            const en = s.site_name_en || '';
            const parts = [short, en].filter(Boolean);
            if (parts.length) $('#hero-subtitle').text(parts.join(' \u2014 '));
        }

        // About section subtitle
        const shortName = s.site_name_short || '';
        const siteName = s.site_name || '';
        if (shortName || siteName) {
            $('#about-subtitle').text((shortName ? shortName + ' ' : '') + siteName);
        }

        // CTA section
        if (s.cta_title) {
            $('#cta-title').text(s.cta_title);
        } else if (shortName) {
            $('#cta-title').text('ร่วมเป็นส่วนหนึ่งของ ' + shortName);
        }

        if (s.cta_subtitle) {
            $('#cta-subtitle').text(s.cta_subtitle);
        } else if (siteName) {
            $('#cta-subtitle').text('สมัครสมาชิกวันนี้ เพื่อร่วมเป็นส่วนหนึ่งของ' + siteName);
        }
    } catch(e) { console.error(e); }
}

async function loadPublicStats() {
    try {
        const res = await API.getPublicStats();
        if (res.success && res.data) {
            $("#stat-members").text(res.data.members || 0);
            $("#stat-news").text(res.data.news || 0);
            $("#stat-activities").text(res.data.activities || 0);
            $("#stat-schools").text(res.data.schools || 0);
        }
    } catch(e) { console.error(e); }
}

async function loadLatestNews() {
    const result = await API.getNewsList({ per_page: 3 });
    const container = $("#latest-news");

    if (result.success && result.data && result.data.length > 0) {
        let html = "";
        result.data.forEach(function(news) {
            const img = news.cover_image ? App.imgUrl(news.cover_image) : App.defaultImage("news");
            const date = App.formatDate(news.published_at || news.created_at);
            const excerpt = news.excerpt || (news.content ? news.content.substring(0, 120) + '...' : '');

            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm news-card" onclick="location.href='./web/?page=news-detail&id=${news.id}'" style="cursor:pointer">
                    <img src="${img}" class="card-img-top" style="height:200px;object-fit:cover"
                        alt="${news.title}" onerror="App.defaultImage(this,'news')">
                    <div class="card-body">
                        <h5 class="card-title">${news.title}</h5>
                        <p class="card-text text-muted small">${excerpt}</p>
                    </div>
                    <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-calendar me-1"></i>${date}
                            <i class="bi bi-eye ms-2 me-1"></i>${App.formatNumber(news.views || 0)}
                        </small>
                        <span class="text-primary fw-semibold small">รายละเอียด <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>`;
        });
        container.html(html);
    } else {
        container.html('<div class="col-12"><div class="empty-state"><i class="bi bi-newspaper d-block"></i><p>ยังไม่มีข่าวประชาสัมพันธ์</p></div></div>');
    }
}

async function loadUpcomingActivities() {
    const result = await API.getActivities({ per_page: 3, upcoming: 1 });
    const container = $("#upcoming-activities");

    if (result.success && result.data && result.data.length > 0) {
        let html = "";
        result.data.forEach(function(act) {
            const d = new Date(act.start_date);
            const months = ["ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.","มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."];
            const coverImg = act.cover_image ? App.imgUrl(act.cover_image) : '';
            const detailUrl = `./web/?page=activity-detail&id=${act.id}`;
            const eventDateText = act.event_date ? App.formatDateTime(act.event_date) : App.formatDateTime(act.start_date);
            const startDateText = App.formatDateTime(act.start_date);
            const endDateText = act.end_date ? App.formatDateTime(act.end_date) : '-';
            const regStatusText = (act.status === 'open' && act.registration_open)
                ? 'เปิดรับสมัคร'
                : ((act.status === 'open' && !act.registration_open) ? 'ปิดรับสมัคร' : (act.status === 'closed' ? 'จบกิจกรรม' : 'ยกเลิก'));
            const regStatusClass = (act.status === 'open' && act.registration_open)
                ? 'text-success'
                : ((act.status === 'open' && !act.registration_open) ? 'text-warning' : 'text-secondary');
            const feeBadge = act.has_fee == 1
                ? `<span class="badge bg-warning"><i class="bi bi-cash me-1"></i>${App.formatCurrency(act.fee_amount)}</span>`
                : `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>ฟรี</span>`;

            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card activity-card h-100 shadow-sm border-0 overflow-hidden">
                    ${coverImg ? `<a href="${detailUrl}" class="d-block" aria-label="ดูรายละเอียดกิจกรรม ${App.escapeHtml(act.title)}"><div class="activity-cover" style="height:200px;background-image:url('${coverImg}');background-size:cover;background-position:center;position:relative;overflow:hidden;"></div></a>` : `<a href="${detailUrl}" class="d-block" aria-label="ดูรายละเอียดกิจกรรม ${App.escapeHtml(act.title)}"><div class="activity-cover" style="height:200px;background:linear-gradient(135deg, var(--primary-light), var(--primary-custom));display:flex;align-items:center;justify-content:center;position:relative;"><i class="bi bi-calendar-event text-white" style="font-size:3rem;opacity:0.3;"></i></div></a>`}
                    <div class="card-body">
                        <a href="${detailUrl}" class="text-decoration-none text-dark" aria-label="ดูรายละเอียดกิจกรรม ${App.escapeHtml(act.title)}">
                            <h6 class="fw-bold mb-2" style="font-size:1.5rem;line-height:1.3;">${act.title}</h6>
                        </a>
                        <div class="mb-3 p-3 rounded-3" style="background:linear-gradient(135deg,#eef2ff,#dbeafe);border:1px solid rgba(79,70,229,0.22);box-shadow:0 8px 24px rgba(79,70,229,0.12);">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge rounded-pill" style="background:#4f46e5;padding:0.45rem 0.85rem;font-size:0.8rem;">วันกิจกรรม</span>
                                <span class="fw-bold" style="font-size:1.45rem;color:#111827;line-height:1.2;letter-spacing:0.01em;">
                                    <i class="bi bi-calendar-event me-1"></i>${eventDateText}
                                </span>
                            </div>
                        </div>
                        <p class="small mb-2" style="color:#374151;">
                            <span class="d-block mb-1"><i class="bi bi-calendar-plus me-1"></i><strong>วันเริ่มต้น:</strong> ${startDateText}</span>
                            <span class="d-block mb-1"><i class="bi bi-calendar-check me-1"></i><strong>วันสิ้นสุด:</strong> ${endDateText}</span>
                            <span class="d-block ${regStatusClass}"><i class="bi bi-door-open me-1"></i><strong>การรับสมัคร:</strong> ${regStatusText}</span>
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt me-1"></i>${act.location || "ไม่ระบุ"}
                        </p>
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            ${feeBadge}
                            <span class="badge bg-info">
                                <i class="bi bi-people me-1"></i>${act.approved_count || 0}${act.max_participants ? "/" + act.max_participants : ""} คน
                            </span>
                        </div>
                        <a href="${detailUrl}" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-arrow-right me-1"></i>ดูรายละเอียด
                        </a>
                    </div>
                </div>
            </div>`;
        });
        container.html(html);
    } else {
        container.html('<div class="col-12"><div class="empty-state"><i class="bi bi-calendar-x d-block"></i><p>ยังไม่มีกิจกรรมที่กำลังจะมาถึง</p></div></div>');
    }
}
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
