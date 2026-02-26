<?php
/**
 * RESET DATA — ลบข้อมูลต่างๆ ตามที่เลือก
 * *** ไม่ลบ admin users ***
 * *** ต้อง login เป็น admin เท่านั้นจึงจะเข้าถึงได้ ***
 * 
 * วิธีใช้: เปิดผ่าน browser → ติ๊กเลือกรายการ → กดยืนยัน → ลบข้อมูล
 */

// ─── Bootstrap ───
define('SDAK_KS', true);
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$db = getDB();

// ─── ดึง Logo & Site Name (ใช้ทุกหน้า) ───
$logoWeb   = $db->get('site_settings', 'setting_value', ['setting_key' => 'logo_web']) ?: '';
$logoLogin = $db->get('site_settings', 'setting_value', ['setting_key' => 'logo_login']) ?: '';
$logo      = $logoLogin ?: $logoWeb;
$siteName  = $db->get('site_settings', 'setting_value', ['setting_key' => 'site_name_short']) ?: SITE_NAME_SHORT;

// ─── ตรวจสอบ Auth Token ───
$authToken = $_POST['_token'] ?? $_GET['_token'] ?? '';
$authUser  = null;

if ($authToken) {
    // ตรวจสอบ token จาก auth_tokens + users
    $authUser = $db->get('auth_tokens', [
        '[>]users' => ['user_id' => 'id']
    ], [
        'users.id',
        'users.username',
        'users.full_name',
        'users.role',
    ], [
        'auth_tokens.token' => $authToken,
        'auth_tokens.expires_at[>]' => date('Y-m-d H:i:s'),
    ]);
}

// ─── ถ้ายังไม่มี token → หน้า JS อ่าน localStorage แล้ว forward ───
if (empty($authToken)):
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Data — ตรวจสอบสิทธิ์</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body{background:#f8f9fa;font-family:'Sarabun',sans-serif;}</style>
</head>
<body>
<div class="container" style="max-width:480px;margin-top:80px;">
    <div class="text-center">
        <?php if ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteName) ?>" style="max-height:60px;" class="mb-3">
        <?php endif; ?>
        <div class="spinner-border text-danger mb-3" role="status"></div>
        <p class="text-muted" id="auth-status">กำลังตรวจสอบสิทธิ์การเข้าถึง...</p>
    </div>
    <form method="POST" id="tokenForm" style="display:none;">
        <input type="hidden" name="_token" id="tokenInput">
    </form>
</div>
<script>
(function() {
    const token = localStorage.getItem('sdak_token');
    if (!token) {
        document.getElementById('auth-status').innerHTML =
            '<span class="text-danger"><i class="bi bi-shield-x me-1"></i>คุณยังไม่ได้เข้าสู่ระบบ</span>' +
            '<br><a href="auth/?page=login" class="btn btn-primary btn-sm mt-3"><i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบ</a>';
        document.querySelector('.spinner-border').style.display = 'none';
        return;
    }
    document.getElementById('tokenInput').value = token;
    document.getElementById('tokenForm').submit();
})();
</script>
</body>
</html>
<?php exit; endif; ?>

<?php
// ─── ถ้า token ไม่ถูกต้อง หรือไม่ใช่ admin → แสดง Access Denied ───
if (!$authUser || $authUser['role'] !== 'admin'):
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Data — ไม่มีสิทธิ์เข้าถึง</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>body{background:#f8f9fa;font-family:'Sarabun',sans-serif;}</style>
</head>
<body>
<div class="container" style="max-width:480px;margin-top:80px;">
    <div class="text-center">
        <?php if ($logo): ?>
            <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteName) ?>" style="max-height:60px;" class="mb-3">
        <?php endif; ?>
        <div class="mb-3">
            <i class="bi bi-shield-lock text-danger" style="font-size:3rem;"></i>
        </div>
        <h4 class="text-danger">ไม่มีสิทธิ์เข้าถึง</h4>
        <?php if (!$authUser): ?>
            <p class="text-muted">Token หมดอายุหรือไม่ถูกต้อง กรุณาเข้าสู่ระบบใหม่</p>
        <?php else: ?>
            <p class="text-muted">เฉพาะ <strong>Admin</strong> เท่านั้นที่สามารถรีเซ็ตข้อมูลได้<br>
            <small>ล็อกอินอยู่เป็น: <strong><?= htmlspecialchars($authUser['full_name'] ?: $authUser['username']) ?></strong> (<?= htmlspecialchars($authUser['role']) ?>)</small></p>
        <?php endif; ?>
        <div class="mt-3">
            <a href="auth/?page=login" class="btn btn-primary btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i>เข้าสู่ระบบใหม่</a>
            <a href="admin/" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>กลับ Admin</a>
        </div>
    </div>
</div>
</body>
</html>
<?php exit; endif; ?>

<?php
// ═══════════════════════════════════════════════
// ✅ ผ่านการตรวจสอบ — Admin ที่ login อยู่
// ═══════════════════════════════════════════════

// ─── ดึงรหัสยืนยันจาก settings ───
$confirmCode = $db->get('site_settings', 'setting_value', ['setting_key' => 'reset_confirm_code']);
if (empty($confirmCode)) {
    $confirmCode = $db->get('site_settings', 'setting_value', ['setting_key' => 'site_name_short']) ?: 'SDAK';
}

// ─── นับข้อมูล ───
$counts = [
    'members'       => $db->count('users', ['role' => 'member']),
    'tokens'        => $db->count('auth_tokens'),
    'fees'          => $db->count('membership_fees'),
    'receipts'      => $db->count('receipts'),
    'logs'          => $db->count('activity_logs'),
    'registrations' => $db->count('activity_registrations'),
    'statistics'    => $db->count('member_statistics'),
    'pages'         => $db->count('pages'),
    'nav_items'     => $db->count('nav_items'),
];

// ─── รายการที่เลือกได้ ───
$items = [
    'members'       => ['label' => 'Users (member)',           'desc' => 'ผู้ใช้ที่เป็นสมาชิก (Admin ไม่ถูกลบ)',     'icon' => 'bi-people',            'count' => $counts['members'],       'unit' => 'คน',    'color' => 'danger',    'checked' => true],
    'tokens'        => ['label' => 'Auth Tokens',              'desc' => 'Token เข้าสู่ระบบทั้งหมด (ต้อง login ใหม่)', 'icon' => 'bi-key',               'count' => $counts['tokens'],        'unit' => 'รายการ', 'color' => 'warning',   'checked' => true],
    'fees'          => ['label' => 'Membership Fees',          'desc' => 'ค่าธรรมเนียมสมาชิก',                         'icon' => 'bi-cash-coin',         'count' => $counts['fees'],          'unit' => 'รายการ', 'color' => 'dark',      'checked' => true],
    'receipts'      => ['label' => 'Receipts',                 'desc' => 'ใบเสร็จรับเงิน',                              'icon' => 'bi-receipt',           'count' => $counts['receipts'],      'unit' => 'ใบ',    'color' => 'dark',      'checked' => true],
    'logs'          => ['label' => 'Activity Logs',            'desc' => 'Log การใช้งานระบบ',                           'icon' => 'bi-journal-text',      'count' => $counts['logs'],          'unit' => 'รายการ', 'color' => 'dark',      'checked' => true],
    'registrations' => ['label' => 'Activity Registrations',   'desc' => 'ลงทะเบียนกิจกรรม',                            'icon' => 'bi-calendar-check',    'count' => $counts['registrations'], 'unit' => 'รายการ', 'color' => 'dark',      'checked' => true],
    'statistics'    => ['label' => 'Member Statistics',        'desc' => 'สถิติสมาชิก',                                 'icon' => 'bi-bar-chart',         'count' => $counts['statistics'],    'unit' => 'รายการ', 'color' => 'dark',      'checked' => true],
    'uploads'       => ['label' => 'Uploaded Files',           'desc' => 'สลิป/รูปโปรไฟล์ (slips, payments, profiles)', 'icon' => 'bi-image',             'count' => null,                     'unit' => '',       'color' => 'secondary', 'checked' => true],
    'pages'         => ['label' => 'Pages (เพจ)',              'desc' => 'หน้าเพจที่สร้างไว้',                           'icon' => 'bi-file-earmark-text', 'count' => $counts['pages'],         'unit' => 'หน้า',  'color' => 'info',      'checked' => false],
    'nav_items'     => ['label' => 'Nav Items (เมนู)',          'desc' => 'เมนูนำทาง',                                   'icon' => 'bi-list',              'count' => $counts['nav_items'],     'unit' => 'รายการ', 'color' => 'info',      'checked' => false],
];

// ─── ดำเนินการลบ ───
$deleted = false;
$results = [];
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['confirm'] ?? '') === $confirmCode) {
    $selected = $_POST['delete'] ?? [];
    if (empty($selected)) {
        $error = 'กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ';
    } else {
        try {
            $db->query('SET FOREIGN_KEY_CHECKS = 0');

            if (in_array('tokens', $selected)) {
                $db->delete('auth_tokens', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ auth_tokens — ลบ token ทั้งหมด ({$counts['tokens']} รายการ)";
            }

            if (in_array('logs', $selected)) {
                $db->delete('activity_logs', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ activity_logs — ลบ log ทั้งหมด ({$counts['logs']} รายการ)";
            }

            if (in_array('fees', $selected)) {
                $db->delete('membership_fees', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ membership_fees — ลบค่าธรรมเนียม ({$counts['fees']} รายการ)";
            }

            if (in_array('receipts', $selected)) {
                $db->delete('receipts', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ receipts — ลบใบเสร็จ ({$counts['receipts']} ใบ)";
            }

            if (in_array('registrations', $selected)) {
                $db->delete('activity_registrations', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ activity_registrations — ลบการลงทะเบียนกิจกรรม ({$counts['registrations']} รายการ)";
            }

            if (in_array('statistics', $selected)) {
                $db->delete('member_statistics', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ member_statistics — ลบสถิติสมาชิก ({$counts['statistics']} รายการ)";
            }

            if (in_array('pages', $selected)) {
                $db->delete('pages', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ pages — ลบหน้าเพจ ({$counts['pages']} หน้า)";
            }

            if (in_array('nav_items', $selected)) {
                $db->delete('nav_items', Medoo\Medoo::raw('WHERE 1'));
                $results[] = "✅ nav_items — ลบเมนูนำทาง ({$counts['nav_items']} รายการ)";
            }

            if (in_array('members', $selected)) {
                $db->delete('users', ['role' => 'member']);
                // Reset AUTO_INCREMENT ต่อจาก admin คนสุดท้าย
                $maxAdminId = $db->max('users', 'id');
                $nextId = ($maxAdminId ?: 0) + 1;
                $db->query("ALTER TABLE users AUTO_INCREMENT = {$nextId}");
                $results[] = "✅ users (member) — ลบสมาชิก ({$counts['members']} คน) — admin ยังอยู่ / AUTO_INCREMENT = {$nextId}";
            }

            if (in_array('uploads', $selected)) {
                $slipDirs = ['uploads/slips', 'uploads/payment_slips', 'uploads/payments', 'uploads/profiles'];
                $totalFiles = 0;
                foreach ($slipDirs as $dir) {
                    $fullPath = __DIR__ . '/' . $dir;
                    if (is_dir($fullPath)) {
                        $files = glob($fullPath . '/*');
                        foreach ($files as $file) {
                            if (is_file($file) && basename($file) !== '.gitkeep') {
                                unlink($file);
                                $totalFiles++;
                            }
                        }
                    }
                }
                $results[] = "✅ uploads — ลบไฟล์ {$totalFiles} ไฟล์ (slips, payments, profiles)";
            }

            $db->query('SET FOREIGN_KEY_CHECKS = 1');
            $deleted = true;
        } catch (Exception $e) {
            $db->query('SET FOREIGN_KEY_CHECKS = 1');
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Data — <?= htmlspecialchars($siteName) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; font-family: 'Sarabun', sans-serif; }
        .container { max-width: 720px; margin-top: 30px; margin-bottom: 40px; }
        .danger-zone { border: 2px solid #dc3545; border-radius: 12px; padding: 24px; background: #fff; }
        .logo-area img { max-height: 70px; }
        .item-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px; margin-bottom: 8px;
            background: #fff; border: 1px solid #e9ecef;
            transition: all .2s; cursor: pointer;
        }
        .item-row:hover { border-color: #dc3545; background: #fff5f5; }
        .item-row.checked { border-color: #dc3545; background: #fff1f2; }
        .item-row .form-check-input { width: 20px; height: 20px; flex-shrink: 0; cursor: pointer; }
        .item-row .form-check-input:checked { background-color: #dc3545; border-color: #dc3545; }
        .item-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
        }
        .item-info { flex: 1; }
        .item-info strong { font-size: .95rem; }
        .item-info small { display: block; color: #6b7280; font-size: .8rem; }
        .item-count { font-weight: 700; font-size: .95rem; white-space: nowrap; }
        .select-btns { font-size: .85rem; }
        .select-btns a { cursor: pointer; text-decoration: underline; color: #dc3545; }
        .select-btns a:hover { color: #a71d2a; }
        .separator-label {
            font-size: .75rem; font-weight: 600; text-transform: uppercase;
            color: #6b7280; letter-spacing: 0.5px; margin: 12px 0 4px 4px;
        }
    </style>
</head>
<body>
<div class="container">

    <!-- Header with Logo -->
    <div class="text-center mb-4">
        <?php if ($logo): ?>
            <div class="logo-area mb-2">
                <img src="<?= htmlspecialchars($logo) ?>" alt="<?= htmlspecialchars($siteName) ?>">
            </div>
        <?php endif; ?>
        <h2 class="text-danger mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Reset Data</h2>
        <p class="text-muted mb-1">เลือกรายการที่ต้องการลบ — <?= htmlspecialchars($siteName) ?></p>
        <small class="text-success"><i class="bi bi-shield-check me-1"></i>เข้าสู่ระบบเป็น: <strong><?= htmlspecialchars($authUser['full_name'] ?: $authUser['username']) ?></strong> (Admin)</small>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="bi bi-x-circle me-1"></i> <strong>เกิดข้อผิดพลาด:</strong> <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($deleted): ?>
        <!-- ─── ผลลัพธ์การลบ ─── -->
        <div class="card border-success shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-check-circle me-1"></i> ลบข้อมูลสำเร็จ</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <?php foreach ($results as $r): ?>
                        <li class="list-group-item"><?= $r ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php if (in_array('tokens', $_POST['delete'] ?? [])): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    Token ถูกลบ → Admin ต้อง<strong>เข้าสู่ระบบใหม่</strong>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="text-center mt-3">
            <a href="admin/" class="btn btn-primary"><i class="bi bi-box-arrow-in-right me-1"></i> ไปหน้า Admin</a>
            <a href="auth/?page=login" class="btn btn-outline-secondary"><i class="bi bi-door-open me-1"></i> Login</a>
            <a href="reset_members.php" class="btn btn-outline-danger"><i class="bi bi-arrow-repeat me-1"></i> Reset อีกครั้ง</a>
        </div>

    <?php else: ?>
        <!-- ─── เลือกรายการที่จะลบ ─── -->
        <div class="danger-zone">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="text-danger mb-0"><i class="bi bi-trash3 me-1"></i> เลือกข้อมูลที่ต้องการลบ</h5>
                <div class="select-btns">
                    <a onclick="selectAll(true)">เลือกทั้งหมด</a> | <a onclick="selectAll(false)">ยกเลิกทั้งหมด</a>
                </div>
            </div>

            <form method="POST" onsubmit="return confirmDelete();" id="resetForm">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($authToken) ?>">
                <input type="hidden" name="confirm" id="confirmInput" value="">

                <div class="separator-label"><i class="bi bi-database me-1"></i> ข้อมูลสมาชิก</div>
                <?php
                $memberItems = ['members','tokens','fees','receipts','logs','registrations','statistics','uploads'];
                foreach ($memberItems as $key):
                    $item = $items[$key];
                ?>
                <label class="item-row <?= $item['checked'] ? 'checked' : '' ?>" id="row_<?= $key ?>">
                    <input type="checkbox" class="form-check-input item-cb" name="delete[]" value="<?= $key ?>" <?= $item['checked'] ? 'checked' : '' ?>>
                    <div class="item-icon bg-<?= $item['color'] ?>-subtle text-<?= $item['color'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i>
                    </div>
                    <div class="item-info">
                        <strong><?= $item['label'] ?></strong>
                        <small><?= $item['desc'] ?></small>
                    </div>
                    <?php if ($item['count'] !== null): ?>
                        <div class="item-count text-<?= $item['color'] ?>"><?= number_format($item['count']) ?> <?= $item['unit'] ?></div>
                    <?php else: ?>
                        <div class="item-count text-secondary">ทั้งหมด</div>
                    <?php endif; ?>
                </label>
                <?php endforeach; ?>

                <div class="separator-label mt-2"><i class="bi bi-layout-text-sidebar me-1"></i> เพจ &amp; เมนู</div>
                <?php
                $contentItems = ['pages','nav_items'];
                foreach ($contentItems as $key):
                    $item = $items[$key];
                ?>
                <label class="item-row <?= $item['checked'] ? 'checked' : '' ?>" id="row_<?= $key ?>">
                    <input type="checkbox" class="form-check-input item-cb" name="delete[]" value="<?= $key ?>" <?= $item['checked'] ? 'checked' : '' ?>>
                    <div class="item-icon bg-<?= $item['color'] ?>-subtle text-<?= $item['color'] ?>">
                        <i class="bi <?= $item['icon'] ?>"></i>
                    </div>
                    <div class="item-info">
                        <strong><?= $item['label'] ?></strong>
                        <small><?= $item['desc'] ?></small>
                    </div>
                    <div class="item-count text-<?= $item['color'] ?>"><?= number_format($item['count']) ?> <?= $item['unit'] ?></div>
                </label>
                <?php endforeach; ?>

                <div class="alert alert-info mt-3 mb-3 small">
                    <i class="bi bi-shield-check me-1"></i> <strong>จะไม่ถูกลบ:</strong>
                    Admin users, Settings, News, Activities, Finance Categories/Transactions
                </div>

                <div class="text-center">
                    <p class="text-danger mb-2 small">พิมพ์ <strong>"รหัสยืนยัน"</strong> เพื่อยืนยันการลบ (ดูรหัสได้ในหน้าตั้งค่า)</p>
                    <button type="submit" class="btn btn-danger btn-lg px-5" id="btnDelete">
                        <i class="bi bi-trash3 me-1"></i> ยืนยันลบข้อมูลที่เลือก
                    </button>
                </div>
            </form>
        </div>

        <div class="text-center mt-3">
            <a href="admin/" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> กลับ Admin</a>
        </div>

    <?php endif; ?>

</div>

<script>
// Toggle row highlight on checkbox change
document.querySelectorAll('.item-cb').forEach(cb => {
    cb.addEventListener('change', function() {
        this.closest('.item-row').classList.toggle('checked', this.checked);
        updateCount();
    });
});

function selectAll(state) {
    document.querySelectorAll('.item-cb').forEach(cb => {
        cb.checked = state;
        cb.closest('.item-row').classList.toggle('checked', state);
    });
    updateCount();
}

function updateCount() {
    const total = document.querySelectorAll('.item-cb:checked').length;
    const btn = document.getElementById('btnDelete');
    btn.disabled = total === 0;
    btn.querySelector('.bi').className = total === 0 ? 'bi bi-slash-circle me-1' : 'bi bi-trash3 me-1';
}

function confirmDelete() {
    const checked = document.querySelectorAll('.item-cb:checked');
    if (checked.length === 0) {
        alert('กรุณาเลือกรายการที่ต้องการลบอย่างน้อย 1 รายการ');
        return false;
    }

    const names = Array.from(checked).map(cb => {
        return cb.closest('.item-row').querySelector('.item-info strong').textContent;
    });

    const code = <?= json_encode($confirmCode) ?>;
    const input = prompt(
        '⚠️ คุณกำลังจะลบ ' + checked.length + ' รายการ:\n' +
        '• ' + names.join('\n• ') + '\n\n' +
        'พิมพ์ "รหัสยืนยัน" เพื่อยืนยัน\n(ดูรหัสได้ในหน้าตั้งค่าระบบ)\n(การกระทำนี้ไม่สามารถย้อนกลับได้)'
    );
    if (input !== code) {
        alert('ยกเลิกการลบ — พิมพ์รหัสไม่ตรง');
        return false;
    }
    document.getElementById('confirmInput').value = code;
    document.getElementById('btnDelete').disabled = true;
    document.getElementById('btnDelete').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> กำลังลบ...';
    return true;
}

updateCount();
</script>
</body>
</html>
