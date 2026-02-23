<?php
/**
 * API Entry Point — Plain PHP Router
 * URL: /api/?controller=auth&action=login
 */

// ─── CORS ───
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Auth-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ─── Autoloader & Config ───
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// ─── Helper: JSON response ───
function jsonResponse($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    $res = ['success' => $success, 'message' => $message];
    if ($data !== null) $res['data'] = $data;
    echo json_encode($res, JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── Read Parameters ───
$controller = $_GET['controller'] ?? '';
$action     = $_GET['action'] ?? 'index';

if (empty($controller)) {
    jsonResponse(false, 'Missing controller parameter', null, 400);
}

// ─── Controller Map ───
$controllerMap = [
    'auth'      => 'AuthController',
    'member'    => 'MemberController',
    'news'      => 'NewsController',
    'activity'  => 'ActivityController',
    'dashboard' => 'DashboardController',
    'upload'    => 'UploadController',
    'page'      => 'PageController',
    'nav'       => 'NavController',
    'log'       => 'LogController',
    'settings'  => 'SettingsController',
    'fee'       => 'FeeController',
    'receipt'   => 'ReceiptController',
    'finance'   => 'FinanceController',
];

if (!isset($controllerMap[$controller])) {
    jsonResponse(false, "Controller '{$controller}' not found", null, 404);
}

// ─── Get Auth Token ───
$token = null;
// 1) X-Auth-Token header (custom, Apache ไม่ดัก)
if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
    $token = $_SERVER['HTTP_X_AUTH_TOKEN'];
}
// 2) apache_request_headers fallback
if (!$token && function_exists('apache_request_headers')) {
    $ah = apache_request_headers();
    if (!empty($ah['X-Auth-Token'])) {
        $token = $ah['X-Auth-Token'];
    }
}
// 3) Query parameter fallback
if (!$token && !empty($_GET['token'])) {
    $token = $_GET['token'];
}

// ─── Validate Token → Get User ───
$currentUser = null;
if ($token) {
    $auth = new \App\Core\Auth();
    $currentUser = $auth->validateToken($token);
}

// ─── RBAC Check ───
if (!\App\Core\RoleAccess::canAccessApi($controller, $action, $currentUser)) {
    if (!$currentUser) {
        jsonResponse(false, 'กรุณาเข้าสู่ระบบ', null, 401);
    }
    jsonResponse(false, 'คุณไม่มีสิทธิ์เข้าถึงส่วนนี้', null, 403);
}

// ─── Check Active Status ───
// Pending/suspended users can still access:
//   - auth, upload controllers (registration setup)
//   - public endpoints (settings/list, nav/tree, news, etc.)
//   - essential member endpoints (profile, fees, notifications)
if ($currentUser && $currentUser['status'] !== 'active') {
    $allowedForPending = ['auth', 'upload'];
    $allowedActions = [
        'settings' => ['list'],
        'nav'      => ['tree'],
        'member'   => ['profile', 'update', 'notifications'],
        'fee'      => ['my-fees', 'my-current', 'upload-slip', 'create-my-fee'],
        'receipt'  => ['my-receipts', 'detail', 'find-by-ref'],
        'finance'  => ['my-permissions'],
        'news'     => ['list', 'detail'],
        'activity' => ['list', 'detail', 'register', 'cancel-registration', 'upload-slip', 'registrations', 'pending-payments'],
        'page'     => ['list', 'detail'],
        'dashboard'=> ['public_stats'],
    ];

    $isAllowed = in_array($controller, $allowedForPending)
        || (isset($allowedActions[$controller]) && in_array($action, $allowedActions[$controller]));

    if (!$isAllowed) {
        jsonResponse(false, 'บัญชีของคุณยังไม่ได้รับการอนุมัติหรือถูกระงับ', null, 403);
    }
}

// ─── Dispatch to Controller ───
$className = "App\\Controllers\\" . $controllerMap[$controller];

// Convert kebab-case action to camelCase method: cancel-registration → cancelRegistration
$method = lcfirst(str_replace('-', '', ucwords($action, '-')));

if (!class_exists($className)) {
    jsonResponse(false, "Controller class not found", null, 500);
}

$ctrl = new $className();
$ctrl->setUser($currentUser);

if (!method_exists($ctrl, $method)) {
    jsonResponse(false, "Action '{$action}' not found", null, 404);
}

$ctrl->$method();
