<?php $pageTitle = 'ประวัติการใช้งาน'; $page = 'logs'; ?>
<?php include ROOT_PATH . 'templates/admin/header.php'; ?>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"><h1 class="m-0"><i class="bi bi-clock-history me-2"></i>ประวัติการใช้งาน</h1></div>
                    <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="./?page=dashboard">แผงควบคุม</a></li><li class="breadcrumb-item active">ประวัติการใช้งาน</li></ol></div>
                </div>
            </div>
        </div>
        <section class="content">
            <div class="container-fluid">

            <!-- Filters -->
            <div class="card shadow-sm mb-4">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">โมดูล</label>
                            <select id="filterModule" class="form-control form-control-sm">
                                <option value="">ทั้งหมด</option>
                                <option value="auth">ระบบยืนยันตัวตน</option>
                                <option value="member">สมาชิก</option>
                                <option value="news">ข่าวสาร</option>
                                <option value="activity">กิจกรรม</option>
                                <option value="page">หน้าเพจ</option>
                                <option value="nav">เมนู</option>
                                <option value="settings">ตั้งค่า</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">จากวันที่</label>
                            <input type="date" id="filterDateFrom" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">ถึงวันที่</label>
                            <input type="date" id="filterDateTo" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary btn-sm w-100" onclick="clearFilters()">
                                <i class="bi bi-x-circle me-1"></i> ล้างตัวกรอง
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <table id="logsDataTable" class="table table-hover table-striped" style="width:100%">
                        <thead class="table-light">
                            <tr>
                                <th>วันเวลา</th>
                                <th>ผู้ใช้</th>
                                <th>โมดูล</th>
                                <th>การกระทำ</th>
                                <th>รายละเอียด</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>

            </div>
        </section>
    </div>

<?php include ROOT_PATH . 'templates/admin/scripts.php'; ?>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
let logsTable;

$(function () {
    App.requireAdmin();
    initDataTable();

    // Reload on filter change
    $('#filterModule').on('change', () => logsTable.ajax.reload());
    $('#filterDateFrom, #filterDateTo').on('change', () => logsTable.ajax.reload());
});

function initDataTable() {
    logsTable = $('#logsDataTable').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        pageLength: 30,
        lengthMenu: [15, 30, 50, 100],
        ajax: function (data, callback, settings) {
            const params = {
                page: Math.floor(data.start / data.length) + 1,
                per_page: data.length
            };

            // Search from DataTables search box
            if (data.search && data.search.value) {
                params.search = data.search.value;
            }

            // Custom filters
            const module = $('#filterModule').val();
            const dateFrom = $('#filterDateFrom').val();
            const dateTo = $('#filterDateTo').val();
            if (module) params.module = module;
            if (dateFrom) params.date_from = dateFrom;
            if (dateTo) params.date_to = dateTo;

            API.getLogs(params).then(function (json) {
                callback({
                    draw: data.draw,
                    recordsTotal: json.pagination ? json.pagination.total : 0,
                    recordsFiltered: json.pagination ? json.pagination.total : 0,
                    data: json.data || []
                });
            }).catch(function () {
                callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
            });
        },
        columns: [
            {
                data: 'created_at',
                width: '150px',
                render: function (data) {
                    return '<small>' + App.formatDateTime(data) + '</small>';
                }
            },
            {
                data: 'full_name',
                width: '140px',
                render: function (data, type, row) {
                    let html = '<strong>' + App.escapeHtml(data || row.username || '-') + '</strong>';
                    if (row.role) html += '<br>' + App.getRoleBadge(row.role);
                    return html;
                }
            },
            {
                data: 'module',
                width: '100px',
                render: function (data) {
                    return App.getModuleBadge(data);
                }
            },
            {
                data: 'action',
                width: '130px',
                render: function (data) {
                    return App.getActionLabel(data);
                }
            },
            {
                data: 'details',
                render: function (data) {
                    const text = App.escapeHtml(data || '-');
                    if (text.length > 80) {
                        return '<small title="' + text + '">' + text.substring(0, 80) + '…</small>';
                    }
                    return '<small>' + text + '</small>';
                }
            },
            {
                data: 'ip_address',
                width: '110px',
                render: function (data) {
                    return '<small class="text-muted">' + (data || '-') + '</small>';
                }
            }
        ],
        language: {
            processing: '<span class="spinner-border spinner-border-sm"></span> กำลังโหลด...',
            lengthMenu: 'แสดง _MENU_ รายการ',
            zeroRecords: 'ไม่พบประวัติการใช้งาน',
            info: 'แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ',
            infoEmpty: 'ไม่มีข้อมูล',
            infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
            search: '<i class="bi bi-search"></i> ค้นหา:',
            paginate: {
                first: '<i class="bi bi-chevron-double-left"></i>',
                last: '<i class="bi bi-chevron-double-right"></i>',
                next: '<i class="bi bi-chevron-right"></i>',
                previous: '<i class="bi bi-chevron-left"></i>'
            }
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        drawCallback: function () {
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

function clearFilters() {
    $('#filterModule').val('');
    $('#filterDateFrom').val('');
    $('#filterDateTo').val('');
    logsTable.search('').ajax.reload();
}
</script>

<style>
#logsDataTable_wrapper .dataTables_filter input {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 4px 8px;
}
#logsDataTable_wrapper .dataTables_length select {
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 2px 6px;
}
div.dataTables_processing {
    background: rgba(255,255,255,0.9) !important;
    border: none !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-radius: 8px;
    padding: 10px 20px !important;
}
</style>

<?php include ROOT_PATH . 'templates/admin/footer.php'; ?>
