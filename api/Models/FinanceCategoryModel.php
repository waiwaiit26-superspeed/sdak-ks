<?php

namespace App\Models;

use App\Core\Model;

class FinanceCategoryModel extends Model
{
    protected string $table = 'finance_categories';

    /**
     * รายการประเภทรายรับ/รายจ่าย
     */
    public function getList(array $filters = [], int $page = 1, int $perPage = 50): array
    {
        $where = ['ORDER' => ['sort_order' => 'ASC', 'id' => 'ASC']];

        if (!empty($filters['type']) && in_array($filters['type'], ['income', 'expense'])) {
            $where['type'] = $filters['type'];
        }

        if (isset($filters['is_active'])) {
            $where['is_active'] = (int)$filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where['name[~]'] = $filters['search'];
        }

        $total = $this->db->count($this->table, array_diff_key($where, ['ORDER' => 1, 'LIMIT' => 1]));
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];

        $data = $this->db->select($this->table, '*', $where) ?: [];

        return compact('data', 'total', 'page', 'perPage');
    }

    /**
     * รายการประเภททั้งหมดที่ active
     */
    public function getActiveCategories(?string $type = null): array
    {
        $where = ['is_active' => 1, 'ORDER' => ['sort_order' => 'ASC']];
        if ($type && in_array($type, ['income', 'expense'])) {
            $where['type'] = $type;
        }
        return $this->db->select($this->table, '*', $where) ?: [];
    }
}
