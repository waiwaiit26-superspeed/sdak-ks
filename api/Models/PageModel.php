<?php
namespace App\Models;

use App\Core\Model;

class PageModel extends Model
{
    protected string $table = 'pages';

    public function findBySlug(string $slug)
    {
        return $this->findBy(['slug' => $slug, 'status' => 'published']);
    }

    public function listPublished(int $page = 1, int $perPage = 20, ?string $search = null): array
    {
        $where = ['status' => 'published', 'ORDER' => ['created_at' => 'DESC']];
        if ($search) {
            $where['title[~]'] = $search;
        }
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $total = $this->count(['status' => 'published']);
        $data = $this->db->select($this->table, '*', $where) ?: [];
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }

    public function listAll(int $page = 1, int $perPage = 20, ?string $status = null, ?string $search = null): array
    {
        $where = ['ORDER' => ['created_at' => 'DESC']];
        if ($status) $where['status'] = $status;
        if ($search) $where['title[~]'] = $search;

        $countWhere = [];
        if ($status) $countWhere['status'] = $status;
        if ($search) $countWhere['title[~]'] = $search;

        $total = $this->count($countWhere);
        $where['LIMIT'] = [($page - 1) * $perPage, $perPage];
        $data = $this->db->select($this->table, '*', $where) ?: [];
        return ['data' => $data, 'total' => $total, 'page' => $page, 'per_page' => $perPage];
    }
}
