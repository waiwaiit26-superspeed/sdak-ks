<?php
namespace App\Core;

/**
 * API Router
 *
 * Single entry-point receives  ?controller=xxx&action=yyy
 *
 * 1. Parse controller + action
 * 2. Authenticate (optional or required via RoleAccess)
 * 3. Check role permission
 * 4. Dispatch to Controller->action()
 */
class Router
{
    private static array $controllerMap = [
        'auth'          => 'AuthController',
        'member'        => 'MemberController',
        'news'          => 'NewsController',
        'activity'      => 'ActivityController',
        'dashboard'     => 'DashboardController',
        'upload'        => 'UploadController',
        'page'          => 'PageController',
        'nav'           => 'NavController',
        'log'           => 'LogController',
        'settings'      => 'SettingsController',
        'fee'           => 'FeeController',
        'receipt'       => 'ReceiptController',
        'finance'       => 'FinanceController',
        'telegram-link' => 'TelegramLinkController',
    ];

    /**
     * Map URL action name to method name on controller
     * (converts kebab-case to camelCase)
     */
    private static function actionMethod(string $action): string
    {
        // cancel-registration  ->  cancelRegistration
        return lcfirst(str_replace('-', '', ucwords($action, '-')));
    }

    /**
     * Dispatch the request
     */
    public static function dispatch(): void
    {
        Response::handleCORS();

        // Parse controller & action from URL  /api/?controller=xxx&action=yyy
        $module = $_GET['controller'] ?? '';
        $action = $_GET['action'] ?? 'index';

        if (empty($module)) {
            Response::error('Missing controller parameter', 400);
        }

        // ----- resolve controller class -----
        $ctrlName = self::$controllerMap[$module] ?? null;
        if (!$ctrlName) {
            Response::error("Module '{$module}' not found", 404);
        }

        $ctrlClass = "App\\Controllers\\{$ctrlName}";
        if (!class_exists($ctrlClass)) {
            Response::error("Controller '{$ctrlName}' not found", 500);
        }

        $method = self::actionMethod($action);

        // ----- authenticate -----
        $auth = new Auth();
        $token = Auth::getBearerToken();
        $user  = $auth->validateToken($token);

        // ----- check RBAC -----
        if (!RoleAccess::canAccessApi($module, $action, $user)) {
            if (!$user) {
                Response::error('กรุณาเข้าสู่ระบบ', 401);
            }
            Response::error('คุณไม่มีสิทธิ์เข้าถึงส่วนนี้', 403);
        }

        // check active status for protected routes
        if ($user && $user['status'] !== 'active') {
            // allow auth actions and fee actions (for new registrants to upload slip)
            $allowedForPending = ['auth', 'fee', 'upload', 'settings', 'member', 'nav'];
            if (!in_array($module, $allowedForPending)) {
                Response::error('บัญชีของคุณยังไม่ได้รับการอนุมัติหรือถูกระงับ', 403);
            }
        }

        // ----- instantiate & call -----
        /** @var Controller $controller */
        $controller = new $ctrlClass();
        $controller->setUser($user);

        if (!method_exists($controller, $method)) {
            Response::error("Action '{$action}' not found in '{$module}'", 404);
        }

        $controller->$method();
    }
}
