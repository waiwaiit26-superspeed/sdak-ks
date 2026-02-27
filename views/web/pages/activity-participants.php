<?php $pageTitle = 'รายชื่อผู้ลงทะเบียนกิจกรรม'; ?>
<?php include ROOT_PATH . 'templates/public/header.php'; ?>

<style>
.participants-hero {
    background: linear-gradient(135deg, var(--primary-teal, #009688) 0%, var(--secondary-teal, #00796B) 100%);
    color: #fff;
    padding: 2rem 0;
    margin-bottom: 2rem;
}
.code-input-card {
    max-width: 500px;
    margin: 3rem auto;
}
.stat-mini {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,.15);
    border-radius: 20px;
    padding: 4px 14px;
    margin: 2px 4px;
    font-size: .9em;
}
#participantsTable th {
    white-space: nowrap;
    background: #f8f9fa;
}
</style>

<div id="codeSection">
    <div class="container">
        <div class="code-input-card">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="bi bi-people-fill text-primary" style="font-size:3rem;"></i>
                    <h4 class="mt-3 mb-4">ดูรายชื่อผู้ลงทะเบียนกิจกรรม</h4>
                    <p class="text-muted mb-4">กรุณากรอกรหัสเข้าดูที่ได้รับจากผู้จัดกิจกรรม</p>
                    <form id="codeForm" class="d-flex gap-2 justify-content-center">
                        <input type="hidden" id="activityIdInput" value="<?php echo (int)($_GET['id'] ?? 0); ?>">
                        <input type="text" id="accessCodeInput" class="form-control text-center" style="max-width:200px;font-size:1.2em;letter-spacing:2px;" placeholder="รหัสเข้าดู" maxlength="20" required autofocus
                               value="<?php echo htmlspecialchars($_GET['code'] ?? ''); ?>">
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-unlock me-1"></i>เข้าดู</button>
                    </form>
                    <div id="codeError" class="text-danger mt-3" style="display:none;"></div>
                    <?php if (empty($_GET['id'])): ?>
                    <div class="alert alert-warning mt-4 mb-0 small">
                        <i class="bi bi-info-circle me-1"></i>ลิงก์นี้ต้องมี id กิจกรรม กรุณาใช้ลิงก์ที่ได้รับจากผู้จัด
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="resultSection" style="display:none;">
    <!-- Activity Info -->
    <div class="participants-hero" id="activityHero">
        <div class="container">
            <h3 class="mb-2" id="actTitle"></h3>
            <div class="d-flex flex-wrap gap-2 mt-3" id="actStats"></div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span class="text-muted" id="regCount"></span>
            </div>
            <button class="btn btn-success btn-sm" onclick="exportExcel()"><i class="bi bi-file-earmark-excel me-1"></i>ดาวน์โหลด Excel</button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0" id="participantsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อ-สกุล</th>
                                <th>โรงเรียน/หน่วยงาน</th>
                                <th>การชำระเงิน</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="participantsBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ROOT_PATH . 'templates/public/scripts.php'; ?>

<script>
$(function() {
    const actId = $('#activityIdInput').val();
    const preCode = $('#accessCodeInput').val().trim();

    // Auto-load if both id and code are provided via URL
    if (actId && preCode) {
        loadParticipants(actId, preCode);
    }

    $('#codeForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#activityIdInput').val();
        const code = $('#accessCodeInput').val().trim();
        if (!id) {
            $('#codeError').text('ลิงก์ไม่ถูกต้อง กรุณาใช้ลิงก์ที่ได้รับจากผู้จัด').show();
            return;
        }
        if (!code) {
            $('#codeError').text('กรุณากรอกรหัสเข้าดู').show();
            return;
        }
        loadParticipants(id, code);
    });
});

async function loadParticipants(actId, code) {
    $('#codeError').hide();

    const result = await API.getPublicRegistrations(actId, code);
    if (!result.success) {
        $('#codeError').text(result.message || 'เกิดข้อผิดพลาด').show();
        return;
    }

    const data = result.data;
    const act = data.activity;
    const regs = data.registrations || [];

    // Show result section
    $('#codeSection').hide();
    $('#resultSection').show();

    // Activity info
    $('#actTitle').text(act.title);

    const thaiMonths = ['ม.ค.','ก.พ.','มี.ค.','เม.ย.','พ.ค.','มิ.ย.','ก.ค.','ส.ค.','ก.ย.','ต.ค.','พ.ย.','ธ.ค.'];
    function fmtDate(d) {
        if (!d) return '-';
        const dt = new Date(d);
        return dt.getDate() + ' ' + thaiMonths[dt.getMonth()] + ' ' + (dt.getFullYear() + 543);
    }

    let stats = '';
    if (act.location) stats += `<span class="stat-mini"><i class="bi bi-geo-alt me-1"></i>${App.escapeHtml(act.location)}</span>`;
    stats += `<span class="stat-mini"><i class="bi bi-calendar me-1"></i>${fmtDate(act.start_date)}`;
    if (act.end_date && act.end_date !== act.start_date) stats += ` ~ ${fmtDate(act.end_date)}`;
    stats += '</span>';
    if (act.has_fee && act.fee_amount > 0) stats += `<span class="stat-mini"><i class="bi bi-cash me-1"></i>${App.formatCurrency(act.fee_amount)}</span>`;
    else stats += '<span class="stat-mini"><i class="bi bi-gift me-1"></i>ฟรี</span>';
    const spotsText = act.max_participants ? `${regs.filter(r => r.status === 'approved').length}/${act.max_participants} คน` : `${regs.filter(r => r.status === 'approved').length} คน`;
    stats += `<span class="stat-mini"><i class="bi bi-people me-1"></i>${spotsText}</span>`;
    const statusMap = { 'open': 'เปิดรับสมัคร', 'closed': 'ปิดรับ', 'cancelled': 'ยกเลิก' };
    stats += `<span class="stat-mini"><i class="bi bi-info-circle me-1"></i>${statusMap[act.status] || act.status}</span>`;
    $('#actStats').html(stats);

    // Registrations table
    $('#regCount').text(`ผู้ลงทะเบียนทั้งหมด ${regs.length} คน`);

    let html = '';
    regs.forEach((r, i) => {
        const payBadge = r.payment_status === 'paid' ? '<span class="badge bg-success">ชำระแล้ว</span>'
            : r.payment_status === 'pending' ? '<span class="badge bg-warning text-dark">รอชำระ</span>'
            : r.payment_status === 'rejected' ? '<span class="badge bg-danger">ปฏิเสธ</span>'
            : '<span class="badge bg-secondary">ไม่ต้องชำระ</span>';
        const stBadge = r.status === 'approved' ? '<span class="badge bg-success">อนุมัติ</span>'
            : r.status === 'pending' ? '<span class="badge bg-warning text-dark">รออนุมัติ</span>'
            : r.status === 'rejected' ? '<span class="badge bg-danger">ไม่อนุมัติ</span>'
            : '<span class="badge bg-secondary">' + r.status + '</span>';

        html += `<tr>
            <td>${i + 1}</td>
            <td>${App.escapeHtml(r.full_name || '-')}</td>
            <td>${App.escapeHtml(r.school_organization || '-')}</td>
            <td>${payBadge}</td>
            <td>${stBadge}</td>
        </tr>`;
    });

    if (regs.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีผู้ลงทะเบียน</td></tr>';
    }

    $('#participantsBody').html(html);
}

function exportExcel() {
    const table = document.getElementById('participantsTable');
    if (!table) return;

    const rows = table.querySelectorAll('tr');
    const actTitle = $('#actTitle').text() || 'กิจกรรม';
    let csv = '\uFEFF'; // BOM for Excel UTF-8

    rows.forEach(row => {
        const cells = row.querySelectorAll('th, td');
        const rowData = [];
        cells.forEach(cell => {
            rowData.push('"' + cell.textContent.trim().replace(/"/g, '""') + '"');
        });
        csv += rowData.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'รายชื่อผู้ลงทะเบียน-' + actTitle + '.csv';
    link.click();
    URL.revokeObjectURL(link.href);
}
</script>

<?php include ROOT_PATH . 'templates/public/footer.php'; ?>
