<?php
namespace App\Models;

use App\Core\Model;

/**
 * SettingsModel — manages `site_settings` table
 */
class SettingsModel extends Model
{
    protected string $table = 'site_settings';

    /**
     * Get a setting value by key
     */
    public function get(string $key, ?string $default = null): ?string
    {
        $row = $this->db->get($this->table, 'setting_value', ['setting_key' => $key]);
        return $row !== null ? $row : $default;
    }

    /**
     * Set a setting value (upsert)
     */
    public function set(string $key, ?string $value): void
    {
        if ($this->has(['setting_key' => $key])) {
            $this->db->update($this->table, ['setting_value' => $value], ['setting_key' => $key]);
        } else {
            $this->db->insert($this->table, ['setting_key' => $key, 'setting_value' => $value]);
        }
    }

    /**
     * Get multiple settings as key=>value array
     */
    public function getAll(): array
    {
        $rows = $this->db->select($this->table, ['setting_key', 'setting_value']);
        $result = [];
        foreach ($rows as $row) {
            $result[$row['setting_key']] = $row['setting_value'];
        }
        return $result;
    }

    /**
     * Set multiple settings at once
     */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Check if registration is enabled
     */
    public function isRegistrationEnabled(): bool
    {
        return $this->get('registration_enabled', '1') === '1';
    }
}
