<?php
/**
 * save-stats-code.php — บันทึก Histats embed code ลง site_settings
 * เรียก: https://sdak.obec.in/save-stats-code.php?key=xxx&site=sdak
 * เรียก: https://saak.obec.in/save-stats-code.php?key=xxx&site=saak
 * ลบตัวเองเมื่อเสร็จ
 */
require_once __DIR__ . '/webhook-secret.php';
if (($_GET['key'] ?? '') !== WEBHOOK_SECRET) { http_response_code(403); die('Forbidden'); }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/vendor/autoload.php';

$site = $_GET['site'] ?? '';

$codes = [
    'sdak' => '<!-- Histats.com  (div with counter) --><div id="histats_counter"></div>
<!-- Histats.com  START  (aync)-->
<script type="text/javascript">var _Hasync= _Hasync|| [];
_Hasync.push([\'Histats.start\', \'1,5009244,4,436,112,75,00011111\']);
_Hasync.push([\'Histats.fasi\', \'1\']);
_Hasync.push([\'Histats.track_hits\', \'\']);
(function() {
var hs = document.createElement(\'script\'); hs.type = \'text/javascript\'; hs.async = true;
hs.src = (\'//s10.histats.com/js15_as.js\');
(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(hs);
})();</script>
<noscript><a href="/" target="_blank"><img  src="//sstatic1.histats.com/0.gif?5009244&101" alt="counter hit make" border="0"></a></noscript>
<!-- Histats.com  END  -->',

    'saak' => '<!-- Histats.com  (div with counter) --><div id="histats_counter"></div>
<!-- Histats.com  START  (aync)-->
<script type="text/javascript">var _Hasync= _Hasync|| [];
_Hasync.push([\'Histats.start\', \'1,5011246,4,435,112,75,00011111\']);
_Hasync.push([\'Histats.fasi\', \'1\']);
_Hasync.push([\'Histats.track_hits\', \'\']);
(function() {
var hs = document.createElement(\'script\'); hs.type = \'text/javascript\'; hs.async = true;
hs.src = (\'//s10.histats.com/js15_as.js\');
(document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(hs);
})();</script>
<noscript><a href="/" target="_blank"><img  src="//sstatic1.histats.com/0.gif?5011246&101" alt="site stats" border="0"></a></noscript>
<!-- Histats.com  END  -->',
];

if (!isset($codes[$site])) {
    echo json_encode(['error' => 'site must be sdak or saak']); exit;
}

try {
    $db = new \Medoo\Medoo([
        'type' => DB_TYPE, 'host' => DB_HOST, 'port' => DB_PORT,
        'database' => DB_NAME, 'username' => DB_USER, 'password' => DB_PASS, 'charset' => DB_CHARSET,
    ]);

    $code = stripslashes($codes[$site]);
    $exists = $db->get('site_settings', 'setting_value', ['setting_key' => 'embed_stats_code']);
    if ($exists !== null) {
        $db->update('site_settings', ['setting_value' => $code], ['setting_key' => 'embed_stats_code']);
        $status = 'updated';
    } else {
        $db->insert('site_settings', ['setting_key' => 'embed_stats_code', 'setting_value' => $code]);
        $status = 'inserted';
    }

    $result = ['site' => $site, 'db' => DB_NAME, 'status' => $status, 'code_length' => strlen($code)];
} catch (\Throwable $e) {
    $result = ['error' => $e->getMessage()];
}

// ลบตัวเองเฉพาะเมื่อทำทั้ง 2 site แล้ว (เรียกครั้งที่ 2 ลบ)
if (isset($_GET['delete']) && $_GET['delete'] === '1') {
    @unlink(__FILE__);
    $result['self_delete'] = 'deleted';
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
