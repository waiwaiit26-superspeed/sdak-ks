<?php
define('ROOT_PATH', __DIR__ . '/');
require_once ROOT_PATH . 'config/config.php';
$pageTitle = 'หน้าแรก | ' . SITE_NAME_SHORT . ' ' . SITE_NAME_EN;
$currentPage = 'home';
include ROOT_PATH . 'templates/public/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center hero-content">
            <div class="col-lg-7">
                <span class="badge badge-hero mb-3" id="hero-badge"><i class="bi bi-mortarboard-fill me-1"></i> <span id="hero-badge-text"><?php echo SITE_NAME_EN; ?></span></span>
                <h1 class="mb-3" id="hero-title"><?php echo htmlspecialchars(SITE_NAME); ?></h1>
                <p class="lead mb-4" id="hero-subtitle"><?php echo htmlspecialchars(SITE_NAME_SHORT); ?> — <?php echo htmlspecialchars(SITE_NAME_EN); ?></p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="./auth/?page=login" class="btn btn-light btn-lg px-4 fw-bold text-primary-custom">
                        <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                    </a>
                    <a href="./auth/?page=register" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-person-plus me-2"></i>สมัครสมาชิก
                    </a>
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
            <p class="section-subtitle" id="about-subtitle"><?php echo htmlspecialchars(SITE_NAME_SHORT . ' ' . SITE_NAME); ?></p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="icon-wrapper bg-primary-custom text-white">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <h5>สมาชิกสามัญ</h5>
                    <p class="text-muted">บุคคล/นิติบุคคลที่มีคุณสมบัติตามข้อบังคับ มีสิทธิ์ออกเสียงลงคะแนน สามารถสมัครเป็นสมาชิกตลอดชีพได้</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="icon-wrapper" style="background:#8b5cf6;color:#fff;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h5>สมาชิกวิสามัญ</h5>
                    <p class="text-muted">บุคคลทั่วไปหรือผู้สนใจที่มีคุณสมบัติไม่ครบ แต่อยากเข้าร่วมกิจกรรม สมัครรายปี</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="icon-wrapper" style="background:#a78bfa;color:#fff;">
                        <i class="bi bi-heart-fill"></i>
                    </div>
                    <h5>สมาชิกสมทบ</h5>
                    <p class="text-muted">ผู้สนใจสนับสนุนกิจกรรมสมาคม หรือคู่สมรส/บุตรของสมาชิกสามัญ</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card h-100">
                    <div class="icon-wrapper" style="background:#c4b5fd;color:#5b21b6;">
                        <i class="bi bi-award-fill"></i>
                    </div>
                    <h5>สมาชิกกิตติมศักดิ์</h5>
                    <p class="text-muted">ผู้ทรงคุณวุฒิ ทรงเกียรติ หรือผู้มีอุปการคุณแก่สมาคม ซึ่งคณะกรรมการเชิญ</p>
                </div>
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

<!-- Upcoming Activities -->
<section class="section-padding">
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

<!-- CTA Section -->
<section class="section-padding bg-gradient-primary text-white text-center">
    <div class="container">
        <h2 class="fw-bold mb-3" id="cta-title">ร่วมเป็นส่วนหนึ่งของ <?php echo htmlspecialchars(SITE_NAME_SHORT); ?></h2>
        <p class="lead mb-4 opacity-75" id="cta-subtitle">สมัครสมาชิกวันนี้ เพื่อร่วมเป็นส่วนหนึ่งของ<?php echo htmlspecialchars(SITE_NAME); ?></p>
        <a href="./auth/?page=register" class="btn btn-light btn-lg px-5 fw-bold text-primary-custom">
            <i class="bi bi-person-plus me-2"></i>สมัครสมาชิก
        </a>
    </div>
</section>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(document).ready(function() {
    loadHeroFromSettings();
    loadLatestNews();
    loadUpcomingActivities();
    loadPublicStats();
});

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
    const result = await API.getNewsList({ per_page: 4 });
    const container = $("#latest-news");

    if (result.success && result.data && result.data.length > 0) {
        let html = "";
        result.data.forEach(function(news) {
            const img = news.cover_image ? App.imgUrl(news.cover_image) : App.defaultImage("news");
            html += `
            <div class="col-md-6 col-lg-3">
                <div class="card news-card h-100">
                    <img src="${img}" class="card-img-top" alt="${news.title}" onerror="this.src=App.defaultImage('news')">
                    <div class="card-body d-flex flex-column">
                        <div class="news-meta mb-2">
                            <i class="bi bi-calendar3 me-1"></i>${App.formatDate(news.published_at || news.created_at)}
                            <span class="ms-2"><i class="bi bi-eye me-1"></i>${news.views || 0}</span>
                        </div>
                        <h6 class="card-title">${news.title}</h6>
                        <p class="card-text flex-grow-1">${App.truncate(news.excerpt, 80)}</p>
                        <a href="./web/?page=news-detail&id=${news.id}" class="btn btn-sm btn-outline-primary mt-2">อ่านต่อ</a>
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
            const feeBadge = act.has_fee == 1
                ? `<span class="badge bg-warning activity-fee-badge"><i class="bi bi-cash me-1"></i>${App.formatCurrency(act.fee_amount)}</span>`
                : `<span class="badge bg-success activity-fee-badge"><i class="bi bi-check-circle me-1"></i>ฟรี</span>`;

            html += `
            <div class="col-md-6 col-lg-4">
                <div class="card activity-card h-100">
                    <div class="card-body">
                        <div class="d-flex gap-3">
                            <div class="activity-date flex-shrink-0">
                                <div class="day">${d.getDate()}</div>
                                <div class="month">${months[d.getMonth()]}</div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">${act.title}</h6>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt me-1"></i>${act.location || "ไม่ระบุ"}
                                </p>
                                <div class="d-flex gap-2 flex-wrap">
                                    ${feeBadge}
                                    <span class="badge bg-info activity-fee-badge">
                                        <i class="bi bi-people me-1"></i>${act.approved_count || 0}${act.max_participants ? "/" + act.max_participants : ""} คน
                                    </span>
                                </div>
                            </div>
                        </div>
                        <a href="./web/?page=activity-detail&id=${act.id}" class="btn btn-sm btn-primary w-100 mt-3">ดูรายละเอียด</a>
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
