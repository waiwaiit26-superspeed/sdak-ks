<?php $pageTitle = 'กิจกรรม'; ?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-calendar-event me-2"></i>กิจกรรม</h2>
        <div class="d-flex gap-2">
            <select id="filterStatus" class="form-select form-select-sm" style="width:auto">
                <option value="">ทั้งหมด</option>
                <option value="upcoming">กำลังจะมาถึง</option>
                <option value="past">ที่ผ่านมาแล้ว</option>
            </select>
            <input type="text" id="searchActivity" class="form-control form-control-sm" placeholder="ค้นหา..." style="width:200px">
        </div>
    </div>

    <div id="activitiesContainer" class="row g-4">
        <div class="text-center py-5">
            <span class="spinner-border text-primary"></span>
            <p class="mt-2 text-muted">กำลังโหลดกิจกรรม...</p>
        </div>
    </div>

    <nav id="activityPagination" class="mt-4"></nav>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
let currentPage = 1;
const perPage = 12;

async function loadActivities(page = 1) {
    currentPage = page;
    const container = $('#activitiesContainer');
    container.html('<div class="col-12 text-center py-5"><span class="spinner-border text-primary"></span></div>');

    const params = { page, per_page: perPage };
    const search = $('#searchActivity').val().trim();
    const status = $('#filterStatus').val();
    if (search) params.search = search;
    if (status === 'upcoming') params.upcoming = 1;
    if (status === 'past') params.past = 1;

    const result = await API.getActivities(params);

    if (!result.success || !result.data || result.data.length === 0) {
        container.html(`<div class="col-12 text-center py-5">
            <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
            <p class="text-muted">ไม่พบกิจกรรม</p></div>`);
        $('#activityPagination').empty();
        return;
    }

    let html = '';
    result.data.forEach(act => {
        const img = act.cover_image || '';
        const imgSrc = img ? App.imgUrl(img) : App.defaultImage('activity');
        const startDate = App.formatDate(act.start_date);
        const endDate = act.end_date ? ' - ' + App.formatDate(act.end_date) : '';
        const fee = act.has_fee && act.fee_amount > 0 ? App.formatCurrency(act.fee_amount) : 'ฟรี';
        const spots = act.max_participants > 0
            ? `<span class="badge bg-info">${act.approved_count || 0}/${act.max_participants} คน</span>`
            : '<span class="badge bg-success">ไม่จำกัด</span>';

        // Status badge
        let statusBadge = '';
        if (act.status === 'open' && act.registration_open) {
            statusBadge = '<span class="badge bg-success"><i class="bi bi-door-open me-1"></i>เปิดรับสมัคร</span>';
        } else if (act.status === 'open' && !act.registration_open) {
            statusBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-pause-circle me-1"></i>ปิดรับสมัคร</span>';
        } else if (act.status === 'closed') {
            statusBadge = '<span class="badge bg-secondary"><i class="bi bi-check-circle me-1"></i>จบแล้ว</span>';
        } else if (act.status === 'cancelled') {
            statusBadge = '<span class="badge bg-dark"><i class="bi bi-x-circle me-1"></i>ยกเลิก</span>';
        }

        // Member type restriction
        const memberTypeLabels = { ordinary: 'สามัญ', associate: 'วิสามัญ', affiliate: 'สมทบ', honorary: 'กิตติมศักดิ์' };
        let restrictBadge = '';
        let memberTypeInfo = '';
        if (act.visibility === 'members_only') {
            restrictBadge = '<span class="badge bg-warning text-dark"><i class="bi bi-lock me-1"></i>เฉพาะสมาชิก</span>';
            memberTypeInfo = '<i class="bi bi-person-badge me-1"></i>เฉพาะสมาชิกสมาคม';
        }
        if (act.allowed_member_types) {
            const typeNames = act.allowed_member_types.split(',').map(t => memberTypeLabels[t.trim()] || t.trim()).join(', ');
            restrictBadge = `<span class="badge bg-info"><i class="bi bi-people me-1"></i>${typeNames}</span>`;
            memberTypeInfo = `<i class="bi bi-person-badge me-1"></i>รับสมาชิก: ${typeNames}`;
        } else if (!memberTypeInfo) {
            memberTypeInfo = '<i class="bi bi-person-badge me-1"></i>เปิดรับทุกประเภท';
        }

        html += `
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm activity-card" onclick="location.href='./?page=activity-detail&id=${act.id}'" style="cursor:pointer">
                <div class="position-relative">
                    <img src="${imgSrc}" class="card-img-top" style="height:200px;object-fit:cover"
                        alt="${App.escapeHtml(act.title)}" onerror="this.src=App.defaultImage('activity')">
                    <div class="position-absolute top-0 start-0 p-2 d-flex flex-wrap gap-1">
                        ${statusBadge}
                        ${restrictBadge}
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title">${App.escapeHtml(act.title)}</h5>
                    <p class="card-text small text-muted">
                        <i class="bi bi-calendar me-1"></i>${startDate}${endDate}<br>
                        ${act.location ? '<i class="bi bi-geo-alt me-1"></i>' + App.escapeHtml(act.location) + '<br>' : ''}
                        <i class="bi bi-cash me-1"></i>${fee}<br>
                        ${memberTypeInfo}
                    </p>
                </div>
                <div class="card-footer bg-transparent d-flex justify-content-between align-items-center">
                    <small class="text-muted">ผู้เข้าร่วม</small>
                    ${spots}
                </div>
            </div>
        </div>`;
    });

    container.html(html);

    if (result.pagination && result.pagination.total_pages > 1) {
        $('#activityPagination').html(App.buildPagination(result.pagination, 'loadActivities'));
    } else {
        $('#activityPagination').empty();
    }
}

$(function () {
    loadActivities();

    $('#filterStatus').on('change', () => loadActivities(1));
    let searchTimer;
    $('#searchActivity').on('input', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => loadActivities(1), 400);
    });
});
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
