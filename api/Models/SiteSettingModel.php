<?php
namespace App\Models;

use App\Core\Model;

/**
 * SiteSettingModel — manages `site_settings` table
 */
class SiteSettingModel extends Model
{
    protected string $table = 'site_settings';

    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->findBy(['setting_key' => $key]);
        return $row ? $row['setting_value'] : $default;
    }

    public function set(string $key, ?string $value): void
    {
        if ($this->has(['setting_key' => $key])) {
            $this->update(['setting_value' => $value], ['setting_key' => $key]);
        } else {
            $this->create(['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    public function getAll(): array
    {
        $rows = $this->all(['setting_key', 'setting_value']);
        $result = [];
        foreach ($rows as $r) {
            $result[$r['setting_key']] = $r['setting_value'];
        }
        return $result;
    }
}
