<?php
namespace App\Core;

use App\Models\UserModel;
use App\Models\AuthTokenModel;
use App\Models\MemberStatisticModel;
use App\Models\ActivityLogModel;

/**
 * Authentication service — token-based
 */
class Auth
{
    private UserModel $users;
    private AuthTokenModel $tokens;

    public function __construct()
    {
        $this->users  = new UserModel();
        $this->tokens = new AuthTokenModel();
    }

    /* ---- Token management ---- */

    public function generateToken(int $userId): array
    {
        $token        = bin2hex(random_bytes(64));
        $refreshToken = bin2hex(random_bytes(64));
        $expiresAt    = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);

        $this->tokens->delete(['user_id' => $userId]);
        $this->tokens->create([
            'user_id'       => $userId,
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'expires_at'    => $expiresAt
        ]);

        return [
            'token'         => $token,
            'refresh_token' => $refreshToken,
            'expires_at'    => $expiresAt,
        ];
    }

    public function validateToken(?string $token): ?array
    {
        if (empty($token)) return null;
        $token = str_replace('Bearer ', '', $token);

        $row = $this->tokens->findBy([
            'token'          => $token,
            'expires_at[>]'  => date('Y-m-d H:i:s')
        ]);
        if (!$row) return null;

        return $this->users->find((int)$row['user_id'], [
            'id','username','email','role','member_type','status',
            'prefix','full_name','phone','school_organization','position',
            'profile_image','bio','created_at'
        ]);
    }

    public function refreshToken(string $refreshToken): ?array
    {
        $row = $this->tokens->findBy(['refresh_token' => $refreshToken]);
        if (!$row) return null;
        $this->tokens->delete(['id' => $row['id']]);
        return $this->generateToken((int)$row['user_id']);
    }

    public function logout(string $token): void
    {
        $token = str_replace('Bearer ', '', $token);
        $this->tokens->delete(['token' => $token]);
    }

    /* ---- Password ---- */

    public static function hashPassword(string $pw): string
    {
        return password_hash($pw, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public static function verifyPassword(string $pw, string $hash): bool
    {
        return password_verify($pw, $hash);
    }

    /* ---- Statistics log ---- */

    public function logAction(int $userId, string $action, ?string $old = null, ?string $new = null, ?string $details = null, ?int $by = null): void
    {
        (new MemberStatisticModel())->create([
            'user_id'      => $userId,
            'action'       => $action,
            'old_value'    => $old,
            'new_value'    => $new,
            'details'      => $details,
            'performed_by' => $by
        ]);
    }

    /**
     * บันทึก Activity Log (ประวัติการใช้งานระบบ)
     */
    public static function logActivity(?int $userId, string $action, string $module, ?string $details = null, ?int $targetId = null, ?string $targetType = null, ?string $oldValue = null, ?string $newValue = null): void
    {
        (new ActivityLogModel())->log([
            'user_id'      => $userId,
            'action'       => $action,
            'module'       => $module,
            'target_id'    => $targetId,
            'target_type'  => $targetType,
            'details'      => $details,
            'old_value'    => $oldValue,
            'new_value'    => $newValue,
        ]);
    }

    /* ---- Read bearer from request ---- */

    public static function getBearerToken(): ?string
    {
        // 1) Custom header X-Auth-Token (works without .htaccess on Apache/MAMP)
        if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return $_SERVER['HTTP_X_AUTH_TOKEN'];
        }

        // 2) apache_request_headers fallback
        if (function_exists('apache_request_headers')) {
            $ah = apache_request_headers();
            if (!empty($ah['X-Auth-Token'])) return $ah['X-Auth-Token'];
            if (!empty($ah['Authorization'])) {
                if (preg_match('/Bearer\s(\S+)/', $ah['Authorization'], $m)) return $m[1];
            }
        }

        // 3) Standard Authorization header
        $h = $_SERVER['HTTP_AUTHORIZATION']
           ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
           ?? null;
        if ($h && preg_match('/Bearer\s(\S+)/', $h, $m)) return $m[1];

        // 4) Query parameter fallback
        return $_GET['token'] ?? null;
    }
}
