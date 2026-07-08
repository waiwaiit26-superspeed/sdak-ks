<?php
namespace App\Models;

use App\Core\Model;

/**
 * UserModel — manages `users` table
 */
class UserModel extends Model
{
    protected string $table = 'users';

    /**
     * Fallback columns for older schemas that may not have newly added fields yet.
     */
    private const LEGACY_LIST_COLUMNS = [
        'id','username','email','role','member_type','status',
        'prefix','full_name','phone','school_organization','position',
        'approved_by','approved_at','cancelled_at','cancel_reason','created_at','updated_at'
    ];

    /**
     * Fallback profile columns for older schemas that may not have newly added fields yet.
     */
    private const LEGACY_PROFILE_COLUMNS = [
        'id','username','email','role','member_type','status',
        'prefix','full_name','phone','school_organization','position',
        'approved_at','created_at','updated_at'
    ];

    /** Columns safe to return (no password / google_id) */
    public const SAFE_COLUMNS = [
        'id','username','email','role','member_type','member_number','member_number_confirmed','status',
        'prefix','full_name','phone','school_organization','position','academic_rank',
        'profile_image','google_picture','bio',
        'national_id','first_name','last_name','birth_date',
        'home_address','work_address','education_area','region','work_phone',
        'approved_by','approved_at','cancelled_at',
        'cancel_reason','created_at','updated_at'
    ];

    public const PROFILE_COLUMNS = [
        'id','username','email','role','member_type','member_number','status',
        'prefix','full_name','phone','school_organization','position','academic_rank',
        'profile_image','google_picture','bio',
        'national_id','first_name','last_name','birth_date',
        'home_address','work_address','education_area','region','work_phone',
        'approved_at','created_at','updated_at'
    ];

    /**
     * Find profile by id with backward-compatible column fallback.
     */
    public function findProfileSafe(int $id): ?array
    {
        try {
            return $this->find($id, self::PROFILE_COLUMNS);
        } catch (\Throwable $e) {
            $profile = $this->find($id, self::LEGACY_PROFILE_COLUMNS);
            if ($profile) {
                foreach ([
                    'member_number','academic_rank','profile_image','bio','national_id','first_name','last_name',
                    'birth_date','home_address','work_address','education_area','region','work_phone'
                ] as $missingKey) {
                    if (!array_key_exists($missingKey, $profile)) {
                        $profile[$missingKey] = null;
                    }
                }
            }
            return $profile;
        }
    }

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

    public function getColumns(): array
    {
        static $columnsCache = [];
        if (!isset($columnsCache[$this->table])) {
            $stmt = $this->db->query("SHOW COLUMNS FROM `{$this->table}`");
            $columnsCache[$this->table] = $stmt ? $stmt->fetchAll(\PDO::FETCH_COLUMN) : [];
        }
        return $columnsCache[$this->table] ?: [];
    }

    public function hasColumn(string $column): bool
    {
        return in_array($column, $this->getColumns(), true);
    }

    public function filterColumns(array $data): array
    {
        // Use SAFE_COLUMNS constant to avoid SHOW COLUMNS prepared-statement issues
        // on older MySQL versions (< 8.0.22) with PDO EMULATE_PREPARES=false.
        return array_intersect_key($data, array_flip(self::SAFE_COLUMNS));
    }

    /**
     * Override create to filter out columns that don't exist in DB yet.
     * Prevents INSERT failures when new columns from pending migrations are passed.
     */
    public function create(array $data)
    {
        $filtered = $this->filterColumns($data);
        $this->db->insert($this->table, $filtered);
        return $this->db->id();
    }

    /**
     * Override update to filter out columns that don't exist in DB yet.
     */
    public function update(array $data, array $where): ?\PDOStatement
    {
        $filtered = $this->filterColumns($data);
        if (empty($filtered)) return null;
        return $this->db->update($this->table, $filtered, $where);
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
        if (!empty($filters['role'])) {
            // Backward compatibility: some older records may use role='user' for members.
            if ($filters['role'] === 'member') {
                $where['role'] = ['member', 'user'];
            } else {
                $where['role'] = $filters['role'];
            }
        }

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

        $allowedSortCols = ['full_name', 'member_number', 'member_type', 'position', 'school_organization', 'created_at'];
        if (!empty($filters['order_by']) && in_array($filters['order_by'], $allowedSortCols)) {
            $dir = strtoupper($filters['order_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
            $where['ORDER'] = [$filters['order_by'] => $dir];
        }

        try {
            return $this->paginate(self::SAFE_COLUMNS, $where, $page, $perPage);
        } catch (\Throwable $e) {
            // Backward compatibility for sites that have not applied the latest migrations.
            return $this->paginate(self::LEGACY_LIST_COLUMNS, $where, $page, $perPage);
        }
    }
}
