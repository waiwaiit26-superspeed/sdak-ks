<?php
namespace App\Models;

use App\Core\Model;

class NavItemModel extends Model
{
    protected string $table = 'nav_items';

    /**
     * Get all nav items as a tree (parents with children)
     */
    public function getTree(bool $activeOnly = true): array
    {
        $where = ['ORDER' => ['sort_order' => 'ASC']];
        if ($activeOnly) $where['is_active'] = 1;

        $items = $this->db->select($this->table, [
            'id', 'parent_id', 'title', 'url', 'alias', 'page_id', 'target', 'icon', 'sort_order', 'is_active'
        ], $where) ?: [];

        // Resolve page URLs
        $pageIds = array_filter(array_column($items, 'page_id'));
        $pageMap = [];
        if ($pageIds) {
            $pages = (new PageModel())->all(['id', 'slug', 'title'], ['id' => $pageIds, 'status' => 'published']);
            foreach ($pages as $p) {
                $pageMap[$p['id']] = $p;
            }
        }

        // Build tree
        $tree = [];
        $childMap = [];
        foreach ($items as &$item) {
            // Resolve page URL — use alias if available
            if ($item['page_id'] && isset($pageMap[$item['page_id']])) {
                if (!empty($item['alias'])) {
                    $item['url'] = './web/?page=' . $item['alias'];
                } else {
                    $item['url'] = './web/?page=dynamic&slug=' . $pageMap[$item['page_id']]['slug'];
                }
                $item['page_title'] = $pageMap[$item['page_id']]['title'];
            }
            if ($item['parent_id']) {
                $childMap[$item['parent_id']][] = $item;
            } else {
                $tree[] = $item;
            }
        }

        // Attach children
        foreach ($tree as &$parent) {
            $parent['children'] = $childMap[$parent['id']] ?? [];
        }

        return $tree;
    }

    /**
     * Get flat list for admin
     */
    public function getAll(): array
    {
        return $this->db->select($this->table, '*', ['ORDER' => ['sort_order' => 'ASC']]) ?: [];
    }

    /**
     * Save sort order from array of {id, sort_order, parent_id}
     */
    public function saveOrder(array $items): void
    {
        foreach ($items as $item) {
            $this->update([
                'sort_order' => (int)$item['sort_order'],
                'parent_id'  => $item['parent_id'] ? (int)$item['parent_id'] : null,
            ], ['id' => (int)$item['id']]);
        }
    }

    /**
     * Get next sort order
     */
    public function nextSortOrder(): int
    {
        $max = $this->db->max($this->table, 'sort_order');
        return ($max ?? 0) + 1;
    }
}
