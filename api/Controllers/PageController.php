<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

class PageController extends Controller
{
    /**
     * GET ?controller=page&action=list
     */
    public function list(): void
    {
        $model = $this->model('PageModel');
        $page    = (int)($this->query('page', 1));
        $perPage = (int)($this->query('per_page', 20));
        $status  = $this->query('status');
        $search  = $this->query('search');

        // Admin gets all, public gets only published
        if ($this->currentUser && $this->currentUser['role'] === 'admin') {
            $result = $model->listAll($page, $perPage, $status, $search);
        } else {
            $result = $model->listPublished($page, $perPage, $search);
        }

        Response::success([
            'data' => $result['data'],
            'pagination' => [
                'total'        => $result['total'],
                'current_page' => $result['page'],
                'per_page'     => $result['per_page'],
                'total_pages'  => ceil($result['total'] / max($result['per_page'], 1)),
            ]
        ]);
    }

    /**
     * GET ?controller=page&action=detail
     */
    public function detail(): void
    {
        $model = $this->model('PageModel');
        $id   = $this->query('id');
        $slug = $this->query('slug');

        $page = null;
        if ($id) {
            $page = $model->find((int)$id);
        } elseif ($slug) {
            $page = $model->findBySlug($slug);
        }

        if (!$page) {
            Response::error('ไม่พบหน้าที่ต้องการ', 404);
        }

        // Non-admin can only see published
        if ((!$this->currentUser || $this->currentUser['role'] !== 'admin') && $page['status'] !== 'published') {
            Response::error('ไม่พบหน้าที่ต้องการ', 404);
        }

        Response::success($page);
    }

    /**
     * POST ?controller=page&action=create
     */
    public function create(): void
    {
        $this->requirePost();
        $input = $this->input();
        $title = trim($input['title'] ?? '');
        if ($title === '') Response::error('กรุณากรอกชื่อหน้า');

        $slug = $input['slug'] ?? $this->generateSlug($title);
        $slug = preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $slug)));
        if (!$slug) $slug = 'page-' . time();

        // Check unique slug
        $model = $this->model('PageModel');
        if ($model->findBy(['slug' => $slug])) {
            $slug .= '-' . time();
        }

        $id = $model->create([
            'title'            => $title,
            'slug'             => $slug,
            'content'          => $input['content'] ?? '',
            'cover_image'      => $input['cover_image'] ?? null,
            'meta_description' => $input['meta_description'] ?? null,
            'status'           => in_array($input['status'] ?? '', ['draft', 'published', 'archived']) ? $input['status'] : 'draft',
            'created_by'       => $this->currentUser['id'],
        ]);

        Auth::logActivity((int)$this->currentUser['id'], 'create', 'page', "สร้างหน้า: {$title}", $id, 'page');
        Response::success(['id' => $id, 'slug' => $slug], 'สร้างหน้าสำเร็จ');
    }

    /**
     * POST ?controller=page&action=update
     */
    public function update(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('ไม่ระบุ ID');

        $model = $this->model('PageModel');
        $page = $model->find($id);
        if (!$page) Response::error('ไม่พบหน้าที่ต้องการ', 404);

        $allowed = ['title', 'slug', 'content', 'cover_image', 'meta_description', 'status'];
        $data = [];
        foreach ($allowed as $field) {
            if (isset($input[$field])) {
                $data[$field] = $input[$field];
            }
        }

        if (isset($data['status']) && !in_array($data['status'], ['draft', 'published', 'archived'])) {
            unset($data['status']);
        }

        if (!empty($data)) {
            $model->update($data, ['id' => $id]);
        }

        Auth::logActivity((int)$this->currentUser['id'], 'update', 'page', "แก้ไขหน้า: {$page['title']}", $id, 'page');
        Response::success(null, 'อัปเดตหน้าสำเร็จ');
    }

    /**
     * POST ?controller=page&action=delete
     */
    public function delete(): void
    {
        $this->requirePost();
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('ไม่ระบุ ID');

        $model = $this->model('PageModel');
        $page = $model->find($id);
        $model->delete(['id' => $id]);

        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'page', "ลบหน้า: " . ($page['title'] ?? $id), $id, 'page');
        Response::success(null, 'ลบหน้าสำเร็จ');
    }

    private function generateSlug(string $title): string
    {
        $slug = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $title);
        $slug = preg_replace('/\s+/', '-', trim($slug));
        return strtolower($slug) ?: 'page-' . time();
    }
}
