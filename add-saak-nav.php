<?php
/**
 * add-saak-nav.php — เพิ่มเมนู "กรรมการสมาคม" ให้ saak.obec.in
 * เรียก: https://saak.obec.in/add-saak-nav.php?key=xxx
 * สร้าง page + nav_item ให้เหมือน sdak แล้วลบตัวเอง
 */
require_once __DIR__ . '/webhook-secret.php';

if (($_GET['key'] ?? '') !== WEBHOOK_SECRET) {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

$results = [];

try {
    $db = new \Medoo\Medoo([
        'type'      => DB_TYPE,
        'host'      => DB_HOST,
        'port'      => DB_PORT,
        'database'  => DB_NAME,
        'username'  => DB_USER,
        'password'  => DB_PASS,
        'charset'   => DB_CHARSET,
    ]);

    // 1. สร้าง page "กรรมการสมาคม" (ถ้ายังไม่มี)
    $existing = $db->get('pages', 'id', ['slug' => 'committy']);
    if ($existing) {
        $pageId = $existing;
        $results[] = ['step' => 'page', 'status' => 'already exists', 'id' => $pageId];
    } else {
        $db->insert('pages', [
            'title'   => 'กรรมการสมาคม',
            'slug'    => 'committy',
            'content' => '<div class="text-center py-5"><h3>กรรมการสมาคม</h3><p class="text-muted">กำลังอัปเดตข้อมูล...</p></div>',
            'status'  => 'published',
        ]);
        $pageId = $db->id();
        $results[] = ['step' => 'page', 'status' => 'created', 'id' => $pageId];
    }

    // 2. สร้าง nav_item "กรรมการสมาคม" (ถ้ายังไม่มี)
    $existingNav = $db->get('nav_items', 'id', ['alias' => 'committy']);
    if ($existingNav) {
        $results[] = ['step' => 'nav_item', 'status' => 'already exists', 'id' => $existingNav];
    } else {
        $db->insert('nav_items', [
            'title'      => 'กรรมการสมาคม',
            'url'        => './web/?page=committy',
            'alias'      => 'committy',
            'page_id'    => $pageId,
            'target'     => '_self',
            'icon'       => 'bi bi-people-fill',
            'sort_order' => 4,
            'is_active'  => 1,
        ]);
        $navId = $db->id();
        $results[] = ['step' => 'nav_item', 'status' => 'created', 'id' => $navId];
    }

    $results[] = ['step' => 'database', 'name' => DB_NAME, 'status' => 'ok'];
} catch (\Throwable $e) {
    $results[] = ['step' => 'error', 'message' => $e->getMessage()];
}

// ลบตัวเอง
$selfDeleted = @unlink(__FILE__);
$results[] = ['step' => 'self-delete', 'status' => $selfDeleted ? 'deleted' : 'failed'];

echo json_encode(['results' => $results], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
