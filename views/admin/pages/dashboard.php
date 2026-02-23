<?php $pageTitle = 'แผงควบคุม'; $page = 'dashboard'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><i class="bi bi-speedometer2 me-2"></i>แผงควบคุม</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item active" id="adminWelcome">Overview</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                <!-- Overview Cards -->
                <div class="row">
                    <div class="col-lg-3 col-6 mb-3">
                        <div class="overview-card bg-grad-members">
                            <i class="bi bi-people-fill ov-icon"></i>
                            <div class="ov-value" id="statMembers">-</div>
                            <div class="ov-label">สมาชิกทั้งหมด</div>
                            <div class="ov-sub"><i class="bi bi-clock me-1"></i><span id="statPending">0</span> รอการอนุมัติ</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 mb-3">
                        <div class="overview-card bg-grad-news">
                            <i class="bi bi-newspaper ov-icon"></i>
                            <div class="ov-value" id="statNews">-</div>
                            <div class="ov-label">ข่าวสาร</div>
                            <div class="ov-sub"><i class="bi bi-check-circle me-1"></i><span id="statPublishedNews">0</span> เผยแพร่แล้ว</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 mb-3">
                        <div class="overview-card bg-grad-activities">
                            <i class="bi bi-calendar-event ov-icon"></i>
                            <div class="ov-value" id="statActivities">-</div>
                            <div class="ov-label">กิจกรรม</div>
                            <div class="ov-sub"><i class="bi bi-arrow-up-circle me-1"></i><span id="statUpcoming">0</span> กำลังจะมาถึง</div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6 mb-3">
                        <div class="overview-card bg-grad-regs">
                            <i class="bi bi-clipboard-check ov-icon"></i>
                            <div class="ov-value" id="statRegs">-</div>
                            <div class="ov-label">ลงทะเบียนกิจกรรม</div>
                            <div class="ov-sub"><i class="bi bi-hourglass-split me-1"></i><span id="statPendingRegs">0</span> รอการอนุมัติ</div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="bi bi-pie-chart me-2"></i>สมาชิกแยกตามประเภท</h3>
                            </div>
                            <div class="card-body">
                                <div id="memberTypeBreakdown">
                                    <div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card chart-card">
                            <div class="card-header">
                                <h3 class="card-title"><i class="bi bi-clock-history me-2"></i>กิจกรรมล่าสุด</h3>
                            </div>
                            <div class="card-body p-0">
                                <div id="recentLogs">
                                    <div class="text-center text-muted py-3"><span class="spinner-border spinner-border-sm"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<script>
$(function () {
    App.requireAdmin();
    const user = API.getUser();
    if (user) $('#adminWelcome').text('สวัสดี, ' + user.full_name);

    // Load dashboard data
    async function loadDashboard() {
        const result = await API.getDashboard();
        if (!result.success) return;
        const d = result.data;

        $('#statMembers').text(d.members.total);
        $('#statPending').text(d.members.pending);
        $('#statNews').text(d.news.total);
        $('#statPublishedNews').text(d.news.published);
        $('#statActivities').text(d.activities.total);
        $('#statUpcoming').text(d.activities.upcoming);
        $('#statRegs').text(d.registrations.approved);
        $('#statPendingRegs').text(d.registrations.pending);
    }

    async function loadStatistics() {
        const result = await API.getDashboardStatistics();
        if (!result.success) return;
        const d = result.data;

        // Member type breakdown
        let html = '<ul class="list-group list-group-flush">';
        const typeLabels = { ordinary: 'สมาชิกสามัญ', associate: 'สมาชิกวิสามัญ', affiliate: 'สมาชิกสมทบ', honorary: 'สมาชิกกิตติมศักดิ์' };
        const typeColors = { ordinary: 'primary', associate: 'success', affiliate: 'info', honorary: 'warning' };
        for (const [type, count] of Object.entries(d.member_types)) {
            html += `<li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><span class="badge bg-${typeColors[type]} me-2">&nbsp;</span>${typeLabels[type]}</span>
                        <span class="badge bg-${typeColors[type]} rounded-pill">${count}</span>
                     </li>`;
        }
        html += '</ul>';
        html += `<div class="mt-3 small text-muted">
            <p class="mb-1">สมาชิกใหม่เดือนนี้: <strong>${d.new_members_this_month}</strong></p>
            <p class="mb-0">สมาชิกใหม่ปีนี้: <strong>${d.new_members_this_year}</strong></p>
        </div>`;
        $('#memberTypeBreakdown').html(html);

        // Recent logs – use activity_logs instead of member_statistics
        const logsResult = await API.getRecentLogs({ limit: 15 });
        if (logsResult.success && logsResult.data && logsResult.data.length > 0) {
            let logsHtml = '<div class="list-group list-group-flush" style="max-height:300px;overflow-y:auto">';
            logsResult.data.forEach(log => {
                logsHtml += `<div class="list-group-item small">
                    <div class="d-flex justify-content-between">
                        <span>${App.getModuleBadge(log.module)} ${App.getActionLabel(log.action)}</span>
                        <small class="text-muted">${App.formatDateTime(log.created_at)}</small>
                    </div>
                    <small class="text-muted">${App.escapeHtml(log.full_name || log.username || '')} — ${App.escapeHtml(log.details || '')}</small>
                </div>`;
            });
            logsHtml += '</div>';
            $('#recentLogs').html(logsHtml);
        } else {
            $('#recentLogs').html('<p class="text-center text-muted py-3">ไม่มีข้อมูล</p>');
        }
    }

    loadDashboard();
    loadStatistics();
});
</script>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
