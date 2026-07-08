<?php
namespace App\Core;

use Medoo\Medoo;

/**
 * Base Model — every model maps to one DB table
 */
abstract class Model
{
    protected Medoo $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = \getDB();
    }

    /* ---------- CRUD helpers ---------- */

    public function find(int $id, $columns = '*')
    {
        return $this->db->get($this->table, $columns, [$this->primaryKey => $id]);
    }

    public function findBy(array $where, $columns = '*')
    {
        return $this->db->get($this->table, $columns, $where);
    }

    public function all($columns = '*', array $where = []): array
    {
        return $this->db->select($this->table, $columns, $where) ?: [];
    }

    public function create(array $data)
    {
        $this->db->insert($this->table, $data);
        return $this->db->id();
    }

    public function update(array $data, array $where): ?\PDOStatement
    {
        return $this->db->update($this->table, $data, $where);
    }

    public function delete(array $where): \PDOStatement
    {
        return $this->db->delete($this->table, $where);
    }

    public function count(array $where = []): int
    {
        return $this->db->count($this->table, $where);
    }

    public function has(array $where): bool
    {
        return $this->db->has($this->table, $where);
    }

    public function paginate($columns = '*', array $where = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $countWhere = $where;
        unset($countWhere['ORDER'], $countWhere['LIMIT']);
        $total = $this->db->count($this->table, $countWhere);

        $where['LIMIT'] = [$offset, $perPage];
        $data = $this->db->select($this->table, $columns, $where) ?: [];

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage
        ];
    }

    /**
     * Join select with pagination
     */
    public function selectJoin(array $join, $columns, array $where): array
    {
        return $this->db->select($this->table, $join, $columns, $where) ?: [];
    }

    public function getJoin(array $join, $columns, array $where)
    {
        return $this->db->get($this->table, $join, $columns, $where);
    }

    public function countJoin(array $join, $column = '*', array $where = []): int
    {
        return $this->db->count($this->table, $join, $column, $where);
    }

    public function getDB(): Medoo
    {
        return $this->db;
    }
}
