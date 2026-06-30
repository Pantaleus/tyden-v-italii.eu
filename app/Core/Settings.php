<?php
declare(strict_types=1);

namespace App\Core;

class Settings
{
    private static array $cache = [];

    public static function get(string $key, string $default = ''): string
    {
        if (empty(self::$cache)) {
            self::load();
        }
        return self::$cache[$key] ?? $default;
    }

    public static function set(string $key, string $value): void
    {
        try {
            DB::query(
                "INSERT INTO `settings` (`setting_key`, `setting_value`) 
                 VALUES (?, ?) 
                 ON DUPLICATE KEY UPDATE `setting_value` = ?",
                [$key, $value, $value]
            );
            self::$cache[$key] = $value;
        } catch (\Exception $e) {
            error_log('Settings write error: ' . $e->getMessage());
        }
    }

    public static function getAll(): array
    {
        if (empty(self::$cache)) {
            self::load();
        }
        return self::$cache;
    }

    private static function load(): void
    {
        try {
            $rows = DB::fetchAll("SELECT `setting_key`, `setting_value` FROM `settings`");
            foreach ($rows as $row) {
                self::$cache[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Exception $e) {
            // DB may not be seeded or created yet
            error_log('Settings load error: ' . $e->getMessage());
        }
    }
}
