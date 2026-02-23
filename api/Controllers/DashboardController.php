<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

/**
 * DashboardController  (admin)
 * Uses: UserModel, NewsModel, ActivityModel, ActivityRegistrationModel, MemberStatisticModel
 */
class DashboardController extends Controller
{
    /**
     * GET  ?controller=dashboard&action=index
     * Returns overview counts
     */
    public function index(): void
    {
        $users      = $this->model('UserModel');
        $news       = $this->model('NewsModel');
        $activities = $this->model('ActivityModel');
        $regs       = $this->model('ActivityRegistrationModel');

        $data = [
            'members' => [
                'total'    => $users->count(['role' => 'member']),
                'pending'  => $users->count(['role' => 'member', 'status' => 'pending']),
                'active'   => $users->count(['role' => 'member', 'status' => 'active']),
            ],
            'news' => [
                'total'     => $news->count([]),
                'published' => $news->count(['status' => 'published']),
                'draft'     => $news->count(['status' => 'draft']),
            ],
            'activities' => [
                'total'     => $activities->count([]),
                'open'      => $activities->count(['status' => 'open']),
                'upcoming'  => $activities->count([
                    'status'     => 'open',
                    'start_date[>=]' => date('Y-m-d'),
                ]),
            ],
            'registrations' => [
                'pending'  => $regs->count(['status' => 'pending']),
                'approved' => $regs->count(['status' => 'approved']),
            ],
        ];

        Response::success($data);
    }

    /**
     * GET  ?controller=dashboard&action=public_stats
     * Public stats for homepage (no auth required)
     */
    public function public_stats(): void
    {
        $users      = $this->model('UserModel');
        $news       = $this->model('NewsModel');
        $activities = $this->model('ActivityModel');

        $db = $users->getDB();

        // Count unique schools
        $schoolCount = 0;
        $schoolResult = $db->query(
            "SELECT COUNT(DISTINCT school_organization) as cnt FROM users WHERE role='member' AND status='active' AND school_organization IS NOT NULL AND school_organization != ''"
        )->fetch(\PDO::FETCH_ASSOC);
        if ($schoolResult) {
            $schoolCount = (int)$schoolResult['cnt'];
        }

        $data = [
            'members'    => $users->count(['role' => 'member', 'status' => 'active']),
            'news'       => $news->count(['status' => 'published']),
            'activities' => $activities->count(['status' => 'open']),
            'schools'    => $schoolCount,
        ];

        Response::success($data);
    }

    /**
     * GET  ?controller=dashboard&action=statistics
     * Returns charts / member-type breakdown / recent logs
     */
    public function statistics(): void
    {
        $users = $this->model('UserModel');
        $stats = $this->model('MemberStatisticModel');

        // Member-type breakdown
        $memberTypes = [];
        foreach (['ordinary','associate','affiliate','honorary'] as $type) {
            $memberTypes[$type] = $users->count([
                'role'        => 'member',
                'member_type' => $type,
                'status'      => 'active',
            ]);
        }

        // Recent member logs
        $recentLogs = $stats->recentLogs(20);

        // Registrations this month / this year
        $now   = new \DateTime();
        $monthStart = $now->format('Y-m-01 00:00:00');
        $yearStart  = $now->format('Y-01-01 00:00:00');
        $end        = $now->format('Y-m-d 23:59:59');

        $data = [
            'member_types'          => $memberTypes,
            'recent_logs'           => $recentLogs,
            'new_members_this_month'=> $users->count([
                'role'   => 'member',
                'created_at[>=]' => $monthStart,
                'created_at[<=]' => $end,
            ]),
            'new_members_this_year' => $users->count([
                'role'   => 'member',
                'created_at[>=]' => $yearStart,
                'created_at[<=]' => $end,
            ]),
        ];

        Response::success($data);
    }
}
