<?php
namespace App\Models;

use App\Core\Model;

/**
 * UserModel — manages `users` table
 */
class UserModel extends Model
{
    protected string $table = 'users';

    /** Columns safe to return (no password / google_id) */
    public const SAFE_COLUMNS = [
        'id','username','email','role','member_type','member_number','status',
        'prefix','full_name','phone','school_organization','position','academic_rank',
        'profile_image','bio',
        'national_id','first_name','last_name','birth_date',
        'home_address','work_address','education_area','region','work_phone',
        'approved_by','approved_at','cancelled_at',
        'cancel_reason','created_at','updated_at'
    ];

    public const PROFILE_COLUMNS = [
        'id','username','email','role','member_type','member_number','status',
        'prefix','full_name','phone','school_organization','position','academic_rank',
        'profile_image','bio',
        'national_id','first_name','last_name','birth_date',
        'home_address','work_address','education_area','region','work_phone',
        'approved_at','created_at','updated_at'
    ];

    public function findByLogin(string $login): ?array
    {
        return $this->db->get($this->table, '*', [
            'OR' => [
                'username' => $login,
                'email'    => $login
            ]
        ]);
    }

    public function findByGoogleId(string $googleId): ?array
    {
        return $this->db->get($this->table, '*', ['google_id' => $googleId]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->db->get($this->table, '*', ['email' => $email]);
    }

    public function usernameExists(string $username): bool
    {
        return $this->has(['username' => $username]);
    }

    public function emailExists(string $email): bool
    {
        return $this->has(['email' => $email]);
    }

    /**
     * Check if a member number already exists
     */
    public function memberNumberExists(string $memberNumber, ?int $excludeUserId = null): bool
    {
        $where = ['member_number' => $memberNumber];
        if ($excludeUserId) $where['id[!]'] = $excludeUserId;
        return $this->has($where);
    }

    /**
     * Get next available member number (numeric part only)
     * Respects member_start_number setting
     */
    public function getNextMemberNumber(int $digits = 4, int $startNumber = 1): string
    {
        $all = $this->db->select($this->table, 'member_number', [
            'member_number[!]' => null,
            'ORDER' => ['member_number' => 'DESC']
        ]) ?: [];

        $maxNum = 0;
        foreach ($all as $mn) {
            $num = (int)preg_replace('/[^0-9]/', '', $mn);
            if ($num > $maxNum) $maxNum = $num;
        }

        // If no members yet, use start number; otherwise max + 1
        if ($maxNum === 0) {
            $next = max($startNumber, 1);
        } else {
            $next = $maxNum + 1;
        }

        return str_pad($next, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Normalize member number input to numeric-only padded string
     * e.g. "SDAK-0042" -> "0042", "42" -> "0042", "0042" -> "0042"
     */
    public static function normalizeMemberNumber(string $input, int $digits = 4): string
    {
        // Strip any non-numeric characters
        $num = preg_replace('/[^0-9]/', '', $input);
        if ($num === '' || $num === '0') return '';
        return str_pad((int)$num, $digits, '0', STR_PAD_LEFT);
    }

    /**
     * Format member number for display: prefix + padded number
     */
    public static function formatMemberNumber(?string $memberNumber, string $prefix = '', int $digits = 4): string
    {
        if (!$memberNumber) return '';
        $padded = str_pad((int)preg_replace('/[^0-9]/', '', $memberNumber), $digits, '0', STR_PAD_LEFT);
        return $prefix . $padded;
    }

    /**
     * Filtered paginated list with search
     */
    public function getFilteredList(array $filters, int $page, int $perPage): array
    {
        $where = [];

        if (!empty($filters['status']))      $where['status'] = $filters['status'];
        if (!empty($filters['member_type'])) $where['member_type'] = $filters['member_type'];
        if (!empty($filters['role']))        $where['role'] = $filters['role'];

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'full_name[~]'          => $s,
                'username[~]'           => $s,
                'email[~]'              => $s,
                'school_organization[~]'=> $s,
                'member_number[~]'      => $s,
            ];
        }

        $where['ORDER'] = ['created_at' => 'DESC'];
        return $this->paginate(self::SAFE_COLUMNS, $where, $page, $perPage);
    }
}
