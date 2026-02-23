<?php
namespace App\Models;

use App\Core\Model;

/**
 * ActivityRegistrationModel — manages `activity_registrations` table
 */
class ActivityRegistrationModel extends Model
{
    protected string $table = 'activity_registrations';

    public function findUserRegistration(int $activityId, int $userId): ?array
    {
        return $this->findBy([
            'activity_id' => $activityId,
            'user_id'     => $userId,
        ]);
    }

    public function countByActivity(int $activityId, $statuses = ['pending', 'approved']): int
    {
        return $this->count([
            'activity_id' => $activityId,
            'status'      => $statuses,
        ]);
    }

    public function approvedCount(int $activityId): int
    {
        return $this->count([
            'activity_id' => $activityId,
            'status'      => 'approved',
        ]);
    }

    /**
     * Get registrations list for an activity (admin view)
     */
    public function getByActivity(int $activityId): array
    {
        return $this->selectJoin(
            ['[>]users' => ['user_id' => 'id']],
            [
                'activity_registrations.id',
                'activity_registrations.user_id',
                'activity_registrations.status',
                'activity_registrations.payment_status',
                'activity_registrations.payment_proof',
                'activity_registrations.note',
                'activity_registrations.registered_at',
                'activity_registrations.approved_at',
                'users.full_name', 'users.email', 'users.phone',
                'users.school_organization', 'users.profile_image',
            ],
            [
                'activity_registrations.activity_id' => $activityId,
                'ORDER' => ['activity_registrations.registered_at' => 'DESC'],
            ]
        );
    }

    /**
     * Count registrations pending approval for a user
     */
    public function countPendingForUser(int $userId): int
    {
        return $this->count([
            'user_id' => $userId,
            'status'  => 'pending',
        ]);
    }

    /**
     * Get all registrations with pending/paid/all payment status for finance manager review
     */
    public function getPendingPayments(string $status = 'pending'): array
    {
        $where = [
            'ORDER' => ['activity_registrations.registered_at' => 'DESC'],
        ];

        if ($status === 'pending') {
            $where['activity_registrations.payment_status'] = 'pending';
            $where['activities.has_fee'] = 1;
        } elseif ($status === 'paid') {
            $where['activity_registrations.payment_status'] = 'paid';
        } elseif ($status === 'all') {
            $where['activities.has_fee'] = 1;
        }

        return $this->db->select(
            'activity_registrations',
            [
                '[>]users'      => ['user_id' => 'id'],
                '[>]activities' => ['activity_id' => 'id'],
            ],
            [
                'activity_registrations.id',
                'activity_registrations.activity_id',
                'activity_registrations.user_id',
                'activity_registrations.status',
                'activity_registrations.payment_status',
                'activity_registrations.payment_proof',
                'activity_registrations.note',
                'activity_registrations.registered_at',
                'activity_registrations.approved_by',
                'activity_registrations.approved_at',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.profile_image',
                'activities.title(activity_title)',
                'activities.fee_amount',
                'activities.fee_description',
                'activities.start_date(activity_start_date)',
            ],
            $where
        ) ?: [];
    }

    /**
     * Count pending payments
     */
    public function countPendingPayments(): int
    {
        return (int)$this->db->count(
            'activity_registrations',
            ['[>]activities' => ['activity_id' => 'id']],
            'activity_registrations.id',
            [
                'activity_registrations.payment_status' => 'pending',
                'activities.has_fee' => 1,
            ]
        );
    }
}
