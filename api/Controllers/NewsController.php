<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Response;

/**
 * NewsController
 * Uses: NewsModel
 */
class NewsController extends Controller
{
    // ── Sub-admin helper ─────────────────────────────────────────────────

    private function requireNewsAccess(string $permission): void
    {
        if (!$this->currentUser) Response::error('กรุณาเข้าสู่ระบบ', 401);
        if ($this->currentUser['role'] === 'admin') return;
        $sa = $this->model('SubAdminModel');
        if (!$sa->hasPermission((int)$this->currentUser['id'], 'news', $permission)) {
            Response::error('คุณไม่มีสิทธิ์ดำเนินการนี้', 403);
        }
    }

    /**
     * GET  ?controller=news&action=list
     */
    public function list(): void
    {
        $news    = $this->model('NewsModel');
        $isAdmin = $this->currentUser && $this->currentUser['role'] === 'admin';

        // Sub-admin with any news permission can see drafts
        if (!$isAdmin && $this->currentUser) {
            $sa = $this->model('SubAdminModel');
            $isAdmin = $sa->hasPermission((int)$this->currentUser['id'], 'news', 'create')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'news', 'edit')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'news', 'delete');
        }

        $result = $news->getList(
            ['status' => $this->query('status'), 'search' => $this->query('search')],
            $this->getPage(),
            $this->getPerPage(50),
            $isAdmin
        );

        Response::paginated($result['data'], $result['total'], $result['page'], $result['perPage']);
    }

    /**
     * GET  ?controller=news&action=detail&id=X
     */
    public function detail(): void
    {
        $id = (int)$this->query('id');
        if (!$id) Response::error('กรุณาระบุ id ข่าว');

        $news = $this->model('NewsModel');
        $item = $news->getDetail($id);
        if (!$item) Response::error('ไม่พบข่าวที่ต้องการ', 404);

        $isAdmin = $this->currentUser && $this->currentUser['role'] === 'admin';
        // Sub-admin with news edit/delete can access draft news
        if (!$isAdmin && $this->currentUser) {
            $sa = $this->model('SubAdminModel');
            $isAdmin = $sa->hasPermission((int)$this->currentUser['id'], 'news', 'edit')
                    || $sa->hasPermission((int)$this->currentUser['id'], 'news', 'delete');
        }
        if ($item['status'] !== 'published' && !$isAdmin) {
            Response::error('ไม่พบข่าวที่ต้องการ', 404);
        }

        $news->incrementViews($id);
        Response::success($item);
    }

    /**
     * POST  ?controller=news&action=create
     */
    public function create(): void
    {
        $this->requirePost();
        $this->requireNewsAccess('create');
        $input = $this->input();

        if (empty(trim($input['title'] ?? '')))   Response::error('กรุณากรอกหัวข้อข่าว');
        if (empty(trim($input['content'] ?? '')))  Response::error('กรุณากรอกเนื้อหาข่าว');

        $news = $this->model('NewsModel');
        $data = [
            'title'        => trim($input['title']),
            'slug'         => $news::makeSlug($input['title']),
            'excerpt'      => trim($input['excerpt'] ?? ''),
            'content'      => $input['content'],
            'cover_image'  => $input['cover_image'] ?? null,
            'author_id'    => $this->currentUser['id'],
            'status'       => in_array($input['status'] ?? '', ['draft','published']) ? $input['status'] : 'draft',
            'published_at' => ($input['status'] ?? '') === 'published' ? date('Y-m-d H:i:s') : null,
        ];

        $id = $news->create($data);
        Auth::logActivity((int)$this->currentUser['id'], 'create', 'news', "สร้างข่าว: {$data['title']}", $id, 'news');
        Response::success(['id' => $id], 'สร้างข่าวสำเร็จ', 201);
    }

    /**
     * POST  ?controller=news&action=update
     */
    public function update(): void
    {
        $this->requirePost();
        $this->requireNewsAccess('edit');
        $input = $this->input();
        $id = (int)($input['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id ข่าว');

        $news = $this->model('NewsModel');
        $item = $news->find($id);
        if (!$item) Response::error('ไม่พบข่าวที่ต้องการ', 404);

        $data = [];
        foreach (['title','excerpt','content','cover_image','status'] as $f) {
            if (isset($input[$f])) $data[$f] = $input[$f];
        }
        if (isset($data['title'])) $data['slug'] = $news::makeSlug($data['title']);
        if (isset($data['status']) && $data['status'] === 'published' && !$item['published_at']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        if (empty($data)) Response::error('ไม่มีข้อมูลที่ต้องอัปเดต');

        $news->update($data, ['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'update', 'news', "แก้ไขข่าว: {$item['title']}", $id, 'news');
        Response::success(null, 'อัปเดตข่าวสำเร็จ');
    }

    /**
     * POST  ?controller=news&action=delete
     */
    public function delete(): void
    {
        $this->requirePost();
        $this->requireNewsAccess('delete');
        $id = (int)($this->input()['id'] ?? 0);
        if (!$id) Response::error('กรุณาระบุ id ข่าว');

        $news = $this->model('NewsModel');
        if (!$news->has(['id' => $id])) Response::error('ไม่พบข่าวที่ต้องการ', 404);

        $item = $news->find($id);
        $news->delete(['id' => $id]);
        Auth::logActivity((int)$this->currentUser['id'], 'delete', 'news', "ลบข่าว: " . ($item['title'] ?? $id), $id, 'news');
        Response::success(null, 'ลบข่าวสำเร็จ');
    }
}
