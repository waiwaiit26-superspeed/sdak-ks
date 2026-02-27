<?php
namespace App\Models;

use App\Core\Model;

/**
 * MemberTypeModel — manages `member_types` table
 * ประเภทสมาชิกเก็บในฐานข้อมูล (เปลี่ยนจาก hardcode)
 */
class MemberTypeModel extends Model
{
    protected string $table = 'member_types';

    /**
     * Get all active member types (sorted)
     */
    public function getActive(): array
    {
        return $this->all('*', [
            'is_active' => 1,
            'ORDER' => ['sort_order' => 'ASC'],
        ]);
    }

    /**
     * Get all member types (including inactive)
     */
    public function getAll(): array
    {
        return $this->all('*', [
            'ORDER' => ['sort_order' => 'ASC'],
        ]);
    }

    /**
     * Find by type_key (ordinary, associate, etc.)
     */
    public function findByKey(string $key): ?array
    {
        return $this->findBy(['type_key' => $key]);
    }

    /**
     * Get label map: ['ordinary' => 'สามัญ', 'associate' => 'วิสามัญ', ...]
     */
    public function getLabelMap(bool $short = false): array
    {
        $types = $this->getActive();
        $map = [];
        foreach ($types as $t) {
            $map[$t['type_key']] = $short ? ($t['label_short'] ?: $t['label']) : $t['label'];
        }
        return $map;
    }

    /**
     * Get fee config for a specific type
     * Returns ['mode' => 'annual', 'amount' => 500.00]
     */
    public function getFeeConfig(string $typeKey): array
    {
        $type = $this->findByKey($typeKey);
        if (!$type) return ['mode' => 'none', 'amount' => 0];
        return [
            'mode'   => $type['fee_mode'] ?? 'none',
            'amount' => (float)($type['fee_amount'] ?? 0),
        ];
    }

    /**
     * Get all fee configs keyed by type
     * Returns ['ordinary' => ['amount' => 500, 'mode' => 'annual'], ...]
     */
    public function getAllFeeConfigs(): array
    {
        $types = $this->getActive();
        $result = [];
        foreach ($types as $t) {
            $result[$t['type_key']] = [
                'amount' => (float)$t['fee_amount'],
                'mode'   => $t['fee_mode'],
            ];
        }
        return $result;
    }

    /**
     * Update fee config for a type
     */
    public function updateFeeConfig(string $typeKey, string $feeMode, float $feeAmount): bool
    {
        $type = $this->findByKey($typeKey);
        if (!$type) return false;
        $this->update([
            'fee_mode'   => $feeMode,
            'fee_amount' => $feeAmount,
        ], ['id' => $type['id']]);
        return true;
    }

    /**
     * Create or update a member type
     */
    public function upsert(array $data): int
    {
        $existing = $this->findByKey($data['type_key']);
        if ($existing) {
            unset($data['type_key']); // don't update key
            $this->update($data, ['id' => $existing['id']]);
            return (int)$existing['id'];
        }
        return (int)$this->create($data);
    }
}
