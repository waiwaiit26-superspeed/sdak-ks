<?php
namespace App\Core;

/**
 * Role-Based Access Control
 *
 * Defines which roles can access which module + action (API)
 * and which roles can view which pages (frontend).
 *
 *  '*'     = anyone (no auth required)
 *  'guest' = only guests (not logged in)
 *  'member'= logged-in member
 *  'admin' = admin only
 */
class RoleAccess
{
    /**
     * API module → action → allowed roles
     */
    private static array $api = [
        'auth' => [
            'login'        => ['*'],
            'register'     => ['*'],
            'google-login'             => ['*'],
            'complete-google-register' => ['*'],
            'forget-password'          => ['*'],
            'reset-password'           => ['*'],
            'verify-reset-token'       => ['*'],
            'test-smtp'                => ['admin'],
            'change-password'          => ['member', 'admin'],
            'logout'                   => ['member', 'admin'],
            'refresh'                  => ['*'],
            'me'                       => ['member', 'admin'],
        ],
        'member' => [
            'profile'             => ['member', 'admin'],
            'update'              => ['member', 'admin'],
            'notifications'       => ['member', 'admin'],
            'list'                => ['admin'],
            'approve'             => ['admin'],
            'create'              => ['admin'],
            'delete'              => ['admin'],
            'import'              => ['admin'],
            'statistics'          => ['admin'],
            'next-member-number'      => ['admin'],
            'check-fee-status'        => ['admin'],
            'confirm-fee-payment'     => ['admin'],
            'admin-reset-password'    => ['admin'],
        ],
        'news' => [
            'list'   => ['*'],
            'detail' => ['*'],
            'create' => ['admin'],
            'update' => ['admin'],
            'delete' => ['admin'],
        ],
        'activity' => [
            'list'                => ['*'],
            'detail'              => ['*'],
            'public-registrations'=> ['*'],
            'create'              => ['admin'],
            'update'              => ['admin'],
            'delete'              => ['admin'],
            'reset-access-code'   => ['admin'],
            'remove-access-code'  => ['admin'],
            'register'            => ['member', 'admin'],
            'cancel-registration' => ['member', 'admin'],
            'upload-slip'         => ['member', 'admin'],
            'approve-registration'=> ['admin', 'member'],
            'registrations'       => ['admin', 'member'],
            'pending-payments'    => ['admin', 'member'],
            'verify-payment'      => ['admin', 'member'],
        ],
        'dashboard' => [
            'index'        => ['admin'],
            'statistics'   => ['admin'],
            'public_stats' => ['*'],
        ],
        'upload' => [
            'image' => ['member', 'admin'],
            'logo'  => ['admin'],
        ],
        'page' => [
            'list'   => ['*'],
            'detail' => ['*'],
            'create' => ['admin'],
            'update' => ['admin'],
            'delete' => ['admin'],
        ],
        'nav' => [
            'tree'    => ['*'],
            'list'    => ['admin'],
            'create'  => ['admin'],
            'update'  => ['admin'],
            'delete'  => ['admin'],
            'reorder' => ['admin'],
        ],
        'log' => [
            'list'   => ['admin'],
            'recent' => ['admin'],
        ],
        'settings' => [
            'list'               => ['*'],
            'update'             => ['admin'],
            'test-telegram'      => ['admin'],
            'member-types'       => ['*'],
            'update-member-type' => ['admin'],
            'create-member-type' => ['admin'],
        ],
        'fee' => [
            'list'          => ['admin'],
            'summary'       => ['admin'],
            'generate'      => ['admin'],
            'approve'       => ['admin'],
            'my-fees'       => ['member', 'admin'],
            'my-current'    => ['member', 'admin'],
            'upload-slip'   => ['member', 'admin'],
            'create-my-fee' => ['member', 'admin'],
        ],
        'receipt' => [
            'list'              => ['admin'],
            'detail'            => ['member', 'admin'],
            'find-by-ref'       => ['member', 'admin'],
            'reference-data'    => ['member', 'admin'],
            'search-reference'  => ['member', 'admin'],
            'create'            => ['member', 'admin'],
            'update'            => ['member', 'admin'],
            'next-number'       => ['member', 'admin'],
            'check-duplicate'   => ['member', 'admin'],
            'search-members'    => ['member', 'admin'],
            'my-receipts'       => ['member', 'admin'],
            'update-my-address' => ['member', 'admin'],
        ],
        'finance' => [
            'categories'                => ['member', 'admin'],
            'active-categories'         => ['member', 'admin'],
            'create-category'           => ['member', 'admin'],
            'update-category'           => ['member', 'admin'],
            'delete-category'           => ['member', 'admin'],
            'list'                      => ['member', 'admin'],
            'detail'                    => ['member', 'admin'],
            'create'                    => ['member', 'admin'],
            'update'                    => ['member', 'admin'],
            'delete'                    => ['member', 'admin'],
            'summary'                   => ['member', 'admin'],
            'monthly-summary'           => ['member', 'admin'],
            'export'                    => ['member', 'admin'],
            'managers'                  => ['admin'],
            'available-members'         => ['admin'],
            'assign-manager'            => ['admin'],
            'revoke-manager'            => ['admin'],
            'toggle-manager'            => ['admin'],
            'delete-manager'            => ['admin'],
            'update-manager-permissions'=> ['admin'],
            'my-permissions'            => ['member', 'admin'],
        ],
        'telegram-link' => [
            'create-token'       => ['member', 'admin'],
            'status'             => ['member', 'admin'],
            'unlink'             => ['member', 'admin'],
            'process-link'       => ['*'], // สำหรับ Bot เรียก (ตรวจสอบ secret ใน Controller)
            'linked-members'     => ['admin'],
        ],
    ];

    /**
     * Frontend page → allowed roles
     */
    private static array $pages = [
        'home'             => ['*'],
        'login'            => ['*'],
        'register'         => ['*'],
        'profile'          => ['member', 'admin'],
        'news'             => ['*'],
        'news-detail'      => ['*'],
        'activities'       => ['*'],
        'activity-detail'  => ['*'],
        'admin'            => ['admin'],
        'admin/members'    => ['admin'],
        'admin/news'       => ['admin'],
        'admin/activities' => ['admin'],
        'admin/settings'   => ['admin'],
        'admin/fees'       => ['admin'],
        'admin/logs'       => ['admin'],
        'admin/receipts'   => ['admin'],
        'admin/finance'    => ['admin'],
        'admin/pages'      => ['admin'],
        'admin/navigation' => ['admin'],
        'admin/logo'       => ['admin'],
        'receipts'         => ['member', 'admin'],
        'finance'          => ['member', 'admin'],
    ];

    /**
     * Check API access.  Returns true if allowed.
     */
    public static function canAccessApi(string $module, string $action, ?array $user): bool
    {
        $roles = self::$api[$module][$action] ?? null;
        if ($roles === null) return false;            // route not defined
        if (in_array('*', $roles, true)) return true; // public

        if (!$user) return false;                     // login required
        return in_array($user['role'], $roles, true);
    }

    /**
     * Check page access.
     */
    public static function canAccessPage(string $page, ?array $user): bool
    {
        $roles = self::$pages[$page] ?? ['*'];
        if (in_array('*', $roles, true)) return true;
        if (in_array('guest', $roles, true) && !$user) return true;
        if (!$user) return false;
        return in_array($user['role'], $roles, true);
    }

    /**
     * Get all allowed modules for a role (for frontend menus)
     */
    public static function getAllowedModules(?string $role): array
    {
        $result = [];
        foreach (self::$api as $module => $actions) {
            $allowed = [];
            foreach ($actions as $action => $roles) {
                if (in_array('*', $roles, true) || ($role && in_array($role, $roles, true))) {
                    $allowed[] = $action;
                }
            }
            if (!empty($allowed)) {
                $result[$module] = $allowed;
            }
        }
        return $result;
    }
}
