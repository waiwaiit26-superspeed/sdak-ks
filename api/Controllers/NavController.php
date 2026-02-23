<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

class NavController extends Controller
{
    /**
     * GET ?controller=nav&action=tree
     * Public: returns active nav items as tree
     */
    public function tree(): void
    {
        $model = $this->model('NavItemModel');
        $tree = $model->getTree(true);
        Response::success($tree);
    }

    /**
     * GET ?controller=nav&action=list
     * Admin: returns all nav items flat
     */
    public function list(): void
    {
        $model = $this->model('NavItemModel');
        $items = $model->getAll();

        // Attach page info
        $pageModel = $this->model('PageModel');
        foreach ($items as &$item) {
            if ($item['page_id']) {
                $page = $pageModel->find((int)$item['page_id']);
                $item['page_title'] = $page ? $page['title'] : null;
                $item['page_slug'] = $page ? $page['slug'] : null;
            }
        }

        Response::success($items);
    }

    /**
     * POST ?controller=nav&action=create
     */
    public function create(): void
    {
        $this->requirePost();
        $input = $this->input();
        $title = trim($input['title'] ?? '');
        if ($title === '') Response::error('กรุณากรอกชื่อเมนู');

        $model = $this->model('NavItemModel');

        $data = [
            'title'      => $title,
            'url'        => $input['url'] ?? null,
            'alias'      => !empty($input['alias']) ? preg_replace('/[^a-z0-9\-_]/', '', strtolower($input['alias'])) : null,
            'page_id'    => !empty($input['page_id']) ? (int)$input['page_id'] : null,
            'parent_id'  => !empty($input['parent_id']) ? (int)$input['parent_id'] : null,
            'target'     => in_array($input['target'] ?? '', ['_self', '_blank']) ? $input['target'] : '_self',
            'icon'       => $input['icon'] ?? null,
            'sort_order' => $model->nextSortOrder(),
            'is_active'  => isset($input['is_active']) ? (int)$input['is_active'] : 1,
        ];

        // If "create_page" flag is set, auto-create a new page
        if (!empty($input['create_page']) && $data['alias']) {
            $pageModel = $this->model('PageModel');
            $slug = $data['alias'];
            // Check slug uniqueness
            if ($pageModel->findBy(['slug' => $slug])) {
                $slug .= '-' . time();
            }
            $pageId = $pageModel->create([
                'title'   => $title,
                'slug'    => $slug,
                'content' => '',
                'status'  => 'published',
                'created_by' => $this->currentUser['id'] ?? null,
            ]);
            $data['page_id'] = $pageId;
        }

        $id = $model->create($data);
        Auth::logActivity((int)$this->currentUser['id'], 'create', 'nav', "สร้างเมนู: {$data['title']}", $id, 'nav');
        Response::success(['id' => $id], 'สร้างเมนูสำเร็จ');
    }

    /**
     * POST ?controller=nav&action=update
     */
    public function update(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('ไม่ระบุ ID');

        $model = $this->model('NavItemModel');
        $item = $model->find($id);
        if (!$item) Response::error('ไม่พบเมนู', 404);

        $allowed = ['title', 'url', 'alias', 'page_id', 'parent_id', 'target', 'icon', 'is_active'];
        $data = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                if ($field === 'page_id' || $field === 'parent_id') {
                    $data[$field] = !empty($input[$field]) ? (int)$input[$field] : null;
                } elseif ($field === 'is_active') {
                    $data[$field] = (int)$input[$field];
                } elseif ($field === 'alias') {
                    $data[$field] = !empty($input[$field]) ? preg_replace('/[^a-z0-9\-_]/', '', strtolower($input[$field])) : null;
                } else {
                    $data[$field] = $input[$field];
                }
            }
        }

        if (!empty($data)) {
            $model->update($data, ['id' => $id]);
        }

        Auth::logActivity((int)$this->currentUser['id'], 'update', 'nav', "แก้ไขเมนู: {$item['title']}", $id, 'nav');
        Response::success(null, 'อัปเดตเมนูสำเร็จ');
    }

    /**
     * POST ?controller=nav&action=delete
     */
    public function delete(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('ไม่ระบุ ID');

        $model = $this->model('NavItemModel');
        $item = $model->find($id);
        $model->delete(['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'nav', "ลบเมนู: " . ($item['title'] ?? $id), $id, 'nav');
        Response::success(null, 'ลบเมนูสำเร็จ');
    }

    /**
     * POST ?controller=nav&action=reorder
     * Expects: { items: [{ id, sort_order, parent_id }, ...] }
     */
    public function reorder(): void
    {
        $this->requirePost();
        $input = $this->input();
        $items = $input['items'] ?? [];
        if (empty($items)) Response::error('ไม่มีข้อมูลการจัดเรียง');

        $model = $this->model('NavItemModel');
        $model->saveOrder($items);
        Auth::logActivity((int)$this->currentUser['id'], 'reorder', 'nav', 'จัดเรียงเมนูใหม่ (' . count($items) . ' รายการ)');
        Response::success(null, 'จัดเรียงเมนูสำเร็จ');
    }
}
