<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

/**
 * LogController — Admin: view activity logs
 */
class LogController extends Controller
{
    /**
     * GET  ?controller=log&action=list
     * Paginated activity logs with filters
     */
    public function list(): void
    {
        $logs = $this->model('ActivityLogModel');

        $result = $logs->getFilteredList(
            [
                'module'    => $this->query('module'),
                'action'    => $this->query('log_action'),
                'user_id'   => $this->query('user_id'),
                'search'    => $this->query('search'),
                'date_from' => $this->query('date_from'),
                'date_to'   => $this->query('date_to'),
            ],
            $this->getPage(),
            $this->getPerPage(50)
        );

        Response::paginated($result['data'], $result['total'], $result['page'], $result['per_page']);
    }

    /**
     * GET  ?controller=log&action=recent
     * Quick recent logs (for dashboard widget)
     */
    public function recent(): void
    {
        $logs = $this->model('ActivityLogModel');
        $limit = min((int)($this->query('limit') ?: 30), 100);

        $data = $logs->recentLogs($limit, [
            'module' => $this->query('module'),
        ]);

        Response::success($data);
    }
}
