<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;

/**
 * MemberTypeController
 * GET /api/?controller=membertype&action=list
 */
class MemberTypeController extends Controller
{
    /**
     * GET /api/?controller=membertype&action=list
     * ดึงรายการประเภทสมาชิกที่เปิดใช้ เรียงตาม sort_order
     */
    public function list(): void
    {
        try {
            $model = $this->model('MemberTypeModel');
            $types = $model->getActive();
            Response::success($types, 'ดึงข้อมูลสำเร็จ');
        } catch (\Throwable $e) {
            error_log('MemberTypeController list error: ' . $e->getMessage());
            Response::error('เกิดข้อผิดพลาดในการดึงข้อมูล', 500);
        }
    }

    /**
     * GET /api/?controller=membertype&action=detail&id=ordinary
     * ดึงข้อมูลประเภทสมาชิกตามชื่อ
     */
    public function detail(): void
    {
        try {
            $key = $this->query('id') ?? '';
            if (!$key) Response::error('กรุณาระบุ id ของประเภทสมาชิก', 400);

            $model = $this->model('MemberTypeModel');
            $type = $model->findByKey($key);
            if (!$type) Response::error('ไม่พบประเภทสมาชิก', 404);

            Response::success($type, 'ดึงข้อมูลสำเร็จ');
        } catch (\Throwable $e) {
            error_log('MemberTypeController detail error: ' . $e->getMessage());
            Response::error('เกิดข้อผิดพลาดในการดึงข้อมูล', 500);
        }
    }
}
