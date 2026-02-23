<?php

namespace App\Models;

use App\Core\Model;

class FinanceTransactionModel extends Model
{
    protected string $table = 'finance_transactions';

    private array $join = [
        '[>]finance_categories' => ['category_id' => 'id'],
        '[>]users(creator)' => ['created_by' => 'id'],
        '[>]users(approver)' => ['approved_by' => 'id'],
    ];

    private array $listCols = [
        'finance_transactions.id',
        'finance_transactions.category_id',
        'finance_transactions.type',
        'finance_transactions.title',
        'finance_transactions.description',
        'finance_transactions.amount',
        'finance_transactions.transaction_date',
        'finance_transactions.reference_no',
        'finance_transactions.attachment',
        'finance_transactions.status',
        'finance_transactions.note',
        'finance_transactions.created_by',
        'finance_transactions.approved_by',
        'finance_transactions.approved_at',
        'finance_transactions.created_at',
        'finance_categories.name(category_name)',
        'finance_categories.type(category_type)',
        'creator.full_name(creator_name)',
        'approver.full_name(approver_name)',
    ];

    /**
     * รายการธุรกรรมทั้งหมด
     */
    public function getList(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where = ['ORDER' => ['finance_transactions.transaction_date' => 'DESC', 'finance_transactions.id' => 'DESC']];

        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $where['finance_transactions.type'] = $filters['type'];
        }

        if (!empty($filters['status']) && in_array($filters['status'], ['pending', 'approved', 'rejected'])) {
            $where['finance_transactions.status'] = $filters['status'];
        }

        if (!empty($filters['category_id'])) {
            $where['finance_transactions.category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $where['OR'] = [
                'finance_transactions.title[~]' => $filters['search'],
                'finance_transactions.description[~]' => $filters['search'],
                'finance_transactions.reference_no[~]' => $filters['search'],
            ];
        }

        if (!empty($filters['date_from'])) {
            $where['finance_transactions.transaction_date[>=]'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $where['finance_transactions.transaction_date[<=]'] = $filters['date_to'];
        }

        if (!empty($filters['month'])) {
            // Format: YYYY-MM
            $where['finance_transactions.transaction_date[~]'] = $filters['month'] . '%';
        }

        if (!empty($filters['year'])) {
            $where['finance_transactions.transaction_date[~]'] = $filters['year'] . '%';
        }

        if (!empty($filters['created_by'])) {
            $where['finance_transactions.created_by'] = (int)$filters['created_by'];
        }

        $countWhere = array_diff_key($where, ['ORDER' => 1, 'LIMIT' => 1]);
        $total = $this->countJoin($this->join, '*', $countWhere);

        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $data = $this->selectJoin($this->join, $this->listCols, $where);

        return compact('data', 'total', 'page', 'perPage');
    }

    /**
     * รายละเอียดธุรกรรม
     */
    public function getDetail(int $id): ?array
    {
        $result = $this->getJoin($this->join, $this->listCols, [
            'finance_transactions.id' => $id,
        ]);
        return $result ?: null;
    }

    /**
     * สรุปรายรับ-รายจ่าย
     */
    public function getSummary(array $filters = []): array
    {
        $where = ['status' => 'approved'];

        if (!empty($filters['date_from'])) {
            $where['transaction_date[>=]'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where['transaction_date[<=]'] = $filters['date_to'];
        }
        if (!empty($filters['month'])) {
            $where['transaction_date[~]'] = $filters['month'] . '%';
        }
        if (!empty($filters['year'])) {
            $where['transaction_date[~]'] = $filters['year'] . '%';
        }

        $incomeWhere = array_merge($where, ['type' => 'income']);
        $expenseWhere = array_merge($where, ['type' => 'expense']);

        $totalIncome = $this->db->sum($this->table, 'amount', $incomeWhere) ?: 0;
        $totalExpense = $this->db->sum($this->table, 'amount', $expenseWhere) ?: 0;
        $balance = $totalIncome - $totalExpense;
        $incomeCount = $this->db->count($this->table, $incomeWhere);
        $expenseCount = $this->db->count($this->table, $expenseWhere);

        return [
            'total_income' => (float)$totalIncome,
            'total_expense' => (float)$totalExpense,
            'balance' => (float)$balance,
            'income_count' => $incomeCount,
            'expense_count' => $expenseCount,
        ];
    }

    /**
     * สรุปรายรับ-รายจ่ายตามประเภท
     */
    public function getSummaryByCategory(array $filters = []): array
    {
        $where = ['finance_transactions.status' => 'approved'];

        if (!empty($filters['date_from'])) {
            $where['finance_transactions.transaction_date[>=]'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where['finance_transactions.transaction_date[<=]'] = $filters['date_to'];
        }
        if (!empty($filters['month'])) {
            $where['finance_transactions.transaction_date[~]'] = $filters['month'] . '%';
        }
        if (!empty($filters['year'])) {
            $where['finance_transactions.transaction_date[~]'] = $filters['year'] . '%';
        }
        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $where['finance_transactions.type'] = $filters['type'];
        }

        $sql = "SELECT 
                    fc.id as category_id,
                    fc.name as category_name,
                    fc.type as category_type,
                    COUNT(ft.id) as transaction_count,
                    COALESCE(SUM(ft.amount), 0) as total_amount
                FROM finance_categories fc
                LEFT JOIN finance_transactions ft ON ft.category_id = fc.id 
                    AND ft.status = 'approved'";

        $params = [];

        if (!empty($filters['date_from'])) {
            $sql .= " AND ft.transaction_date >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND ft.transaction_date <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(ft.transaction_date) = :year";
            $params[':year'] = $filters['year'];
        }

        $sql .= " WHERE fc.is_active = 1";

        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $sql .= " AND fc.type = :type";
            $params[':type'] = $filters['type'];
        }

        $sql .= " GROUP BY fc.id, fc.name, fc.type ORDER BY fc.sort_order ASC";

        return $this->db->query($sql, $params)->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * สรุปรายเดือน
     */
    public function getMonthlySummary(string $year): array
    {
        $sql = "SELECT 
                    DATE_FORMAT(transaction_date, '%Y-%m') as month,
                    type,
                    COUNT(id) as count,
                    COALESCE(SUM(amount), 0) as total
                FROM finance_transactions
                WHERE status = 'approved'
                AND YEAR(transaction_date) = :year
                GROUP BY DATE_FORMAT(transaction_date, '%Y-%m'), type
                ORDER BY month ASC";

        return $this->db->query($sql, [':year' => $year])->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Export data
     */
    public function getExportData(array $filters = []): array
    {
        $where = ['ORDER' => ['finance_transactions.transaction_date' => 'ASC', 'finance_transactions.id' => 'ASC']];

        if (!empty($filters['type'])) {
            $where['finance_transactions.type'] = $filters['type'];
        }
        if (!empty($filters['status'])) {
            $where['finance_transactions.status'] = $filters['status'];
        } else {
            $where['finance_transactions.status'] = 'approved';
        }
        if (!empty($filters['date_from'])) {
            $where['finance_transactions.transaction_date[>=]'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where['finance_transactions.transaction_date[<=]'] = $filters['date_to'];
        }
        if (!empty($filters['year'])) {
            $where['finance_transactions.transaction_date[~]'] = $filters['year'] . '%';
        }
        if (!empty($filters['category_id'])) {
            $where['finance_transactions.category_id'] = (int)$filters['category_id'];
        }

        return $this->selectJoin($this->join, $this->listCols, $where);
    }
}
