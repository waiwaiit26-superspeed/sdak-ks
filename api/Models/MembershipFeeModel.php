<?php
namespace App\Models;

use App\Core\Model;

/**
 * MembershipFeeModel — manages `membership_fees` table
 * ค่าธรรมเนียมสมาชิกรายปี
 */
class MembershipFeeModel extends Model
{
    protected string $table = 'membership_fees';

    /**
     * Get fees for a specific user
     */
    public function getUserFees(int $userId): array
    {
        return $this->db->select($this->table, '*', [
            'user_id' => $userId,
            'ORDER' => ['year' => 'DESC']
        ]) ?: [];
    }

    /**
     * Get fee for a specific user and year
     */
    public function getUserYearFee(int $userId, int $year): ?array
    {
        return $this->db->get($this->table, '*', [
            'user_id' => $userId,
            'year' => $year
        ]);
    }

    /**
     * Get current year (พ.ศ.) fee status for a user
     */
    public function getCurrentFee(int $userId): ?array
    {
        $buddhistYear = (int)date('Y') + 543;
        return $this->getUserYearFee($userId, $buddhistYear);
    }

    /**
     * Check if user has already paid a one-time fee
     */
    public function hasOnetimePaid(int $userId): bool
    {
        $record = $this->db->get($this->table, '*', [
            'user_id' => $userId,
            'fee_type' => 'onetime',
            'status' => 'paid',
        ]);
        return (bool)$record;
    }

    /**
     * Get one-time fee record for a user (if any)
     */
    public function getOnetimeFee(int $userId): ?array
    {
        return $this->db->get($this->table, '*', [
            'user_id' => $userId,
            'fee_type' => 'onetime',
            'ORDER' => ['created_at' => 'DESC'],
        ]);
    }

    /**
     * Create or update fee record for a user/year
     */
    public function upsertFee(int $userId, int $year, float $amount, string $feeType = 'annual'): int
    {
        // For one-time: check if record already exists (any year)
        if ($feeType === 'onetime') {
            $existing = $this->getOnetimeFee($userId);
            if ($existing) {
                $this->update(['amount' => $amount], ['id' => $existing['id']]);
                return (int)$existing['id'];
            }
            return (int)$this->create([
                'user_id' => $userId,
                'year' => $year,
                'amount' => $amount,
                'fee_type' => 'onetime',
                'status' => 'pending',
            ]);
        }

        // For annual:
        $existing = $this->getUserYearFee($userId, $year);
        if ($existing) {
            $this->update(['amount' => $amount], ['id' => $existing['id']]);
            return (int)$existing['id'];
        }
        return (int)$this->create([
            'user_id' => $userId,
            'year' => $year,
            'amount' => $amount,
            'fee_type' => 'annual',
            'status' => 'pending',
        ]);
    }

    /**
     * Upload payment slip
     */
    public function uploadSlip(int $feeId, string $slipUrl): void
    {
        $this->update([
            'payment_slip' => $slipUrl,
            'paid_at' => date('Y-m-d H:i:s'),
            'status' => 'paid',
        ], ['id' => $feeId]);
    }

    /**
     * Admin approve payment
     */
    public function approve(int $feeId, int $adminId, ?string $note = null, ?string $receivedDate = null): void
    {
        $data = [
            'status' => 'paid',
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s'),
        ];
        if ($receivedDate) $data['received_date'] = $receivedDate;
        if ($note) $data['note'] = $note;
        $this->update($data, ['id' => $feeId]);
    }

    /**
     * Admin reject/mark as pending
     */
    public function reject(int $feeId, ?string $note = null): void
    {
        $data = [
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
        ];
        if ($note) $data['note'] = $note;
        $this->update($data, ['id' => $feeId]);
    }

    /**
     * Waive fee
     */
    public function waive(int $feeId, int $adminId, ?string $note = null): void
    {
        $data = [
            'status' => 'waived',
            'approved_by' => $adminId,
            'approved_at' => date('Y-m-d H:i:s'),
        ];
        if ($note) $data['note'] = $note;
        $this->update($data, ['id' => $feeId]);
    }

    /**
     * Paginated list with user info (admin)
     */
    public function getFilteredList(array $filters, int $page, int $perPage): array
    {
        $where = [];

        // Only show member fees (exclude admin)
        $where['u.role'] = 'member';

        if (!empty($filters['year']))   $where['membership_fees.year'] = (int)$filters['year'];
        if (!empty($filters['status'])) $where['membership_fees.status'] = $filters['status'];
        if (!empty($filters['user_id'])) $where['membership_fees.user_id'] = (int)$filters['user_id'];

        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $where['OR'] = [
                'u.full_name[~]' => $s,
                'u.email[~]' => $s,
                'u.school_organization[~]' => $s,
            ];
        }

        $where['ORDER'] = ['membership_fees.year' => 'DESC', 'membership_fees.created_at' => 'DESC'];

        $countWhere = $where;
        unset($countWhere['ORDER']);
        $total = $this->countJoin([
            '[>]users(u)' => ['user_id' => 'id'],
        ], '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];

        $data = $this->selectJoin(
            [
                '[>]users(u)' => ['user_id' => 'id'],
                '[>]users(approver)' => ['approved_by' => 'id'],
            ],
            [
                'membership_fees.id',
                'membership_fees.user_id',
                'membership_fees.year',
                'membership_fees.amount',
                'membership_fees.fee_type',
                'membership_fees.status',
                'membership_fees.payment_slip',
                'membership_fees.paid_at',
                'membership_fees.received_date',
                'membership_fees.approved_by',
                'membership_fees.approved_at',
                'membership_fees.note',
                'membership_fees.created_at',
                'u.full_name',
                'u.email',
                'u.member_type',
                'u.school_organization',
                'approver.full_name(approver_name)',
            ],
            $where
        );

        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    /**
     * Generate fee records for all eligible members for a given year.
     * Supports both 'annual' and 'onetime' fee modes.
     * @param array $feeAmounts  ['ordinary' => 500, ...]
     * @param array $feeModes    ['ordinary' => 'annual'|'onetime'|'none', ...]
     */
    public function generateYearlyFees(int $year, array $feeAmounts, array $feeModes = []): int
    {
        $db = $this->getDB();
        $members = $db->select('users', ['id', 'member_type'], [
            'role' => 'member',
            'status' => 'active',
            'member_type[!]' => null,
        ]);

        $generated = 0;
        foreach ($members as $member) {
            $type = $member['member_type'];
            $mode = $feeModes[$type] ?? 'none';
            $amount = (float)($feeAmounts[$type] ?? 0);

            if ($mode === 'none' || $amount <= 0) continue;

            if ($mode === 'onetime') {
                // Skip if already has a one-time record
                $existingOnetime = $this->getOnetimeFee((int)$member['id']);
                if ($existingOnetime) continue;

                $this->create([
                    'user_id' => (int)$member['id'],
                    'year' => $year,
                    'amount' => $amount,
                    'fee_type' => 'onetime',
                    'status' => 'pending',
                ]);
                $generated++;
            } else {
                // annual
                $existing = $this->getUserYearFee((int)$member['id'], $year);
                if ($existing) continue;

                $this->create([
                    'user_id' => (int)$member['id'],
                    'year' => $year,
                    'amount' => $amount,
                    'fee_type' => 'annual',
                    'status' => 'pending',
                ]);
                $generated++;
            }
        }

        return $generated;
    }

    /**
     * Get summary stats for a year
     */
    public function getYearSummary(int $year): array
    {
        $db = $this->getDB();
        $join = ['[>]users(u)' => ['user_id' => 'id']];
        $base = ['membership_fees.year' => $year, 'u.role' => 'member'];

        return [
            'total'   => $this->countJoin($join, '*', $base),
            'pending' => $this->countJoin($join, '*', array_merge($base, ['membership_fees.status' => 'pending'])),
            'paid'    => $this->countJoin($join, '*', array_merge($base, ['membership_fees.status' => 'paid'])),
            'overdue' => $this->countJoin($join, '*', array_merge($base, ['membership_fees.status' => 'overdue'])),
            'waived'  => $this->countJoin($join, '*', array_merge($base, ['membership_fees.status' => 'waived'])),
        ];
    }

    /**
     * Count unpaid (pending / overdue) fees for a user
     */
    public function countUnpaid(int $userId): int
    {
        return $this->count([
            'user_id' => $userId,
            'status'  => ['pending', 'overdue'],
        ]);
    }
}
