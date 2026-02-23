<?php
namespace App\Models;

use App\Core\Model;

/**
 * PasswordResetModel — manages `password_resets` table
 */
class PasswordResetModel extends Model
{
    protected string $table = 'password_resets';

    /**
     * สร้าง token รีเซ็ตรหัสผ่าน
     * @param int $userId
     * @param int $expiryMinutes อายุ token (นาที) default 30 นาที
     * @return string token
     */
    public function createToken(int $userId, int $expiryMinutes = 30): string
    {
        // ลบ token เก่าที่ยังไม่ได้ใช้ของ user นี้
        $this->db->delete($this->table, [
            'user_id' => $userId,
            'used'    => 0,
        ]);

        $token     = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiryMinutes * 60));

        $this->create([
            'user_id'    => $userId,
            'token'      => $token,
            'expires_at' => $expiresAt,
            'used'       => 0,
        ]);

        return $token;
    }

    /**
     * ตรวจสอบ token ว่าถูกต้องและยังไม่หมดอายุ
     * @return array|null ข้อมูล reset row หรือ null
     */
    public function findValidToken(string $token): ?array
    {
        return $this->findBy([
            'token'          => $token,
            'used'           => 0,
            'expires_at[>]'  => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * ทำเครื่องหมายว่า token ถูกใช้แล้ว
     */
    public function markUsed(int $id): void
    {
        $this->update(['used' => 1], ['id' => $id]);
    }

    /**
     * ลบ token ที่หมดอายุแล้ว (cleanup)
     */
    public function deleteExpired(): void
    {
        $this->db->delete($this->table, [
            'expires_at[<]' => date('Y-m-d H:i:s'),
        ]);
    }
}
