<?php
declare(strict_types=1);

namespace App\Core;

class Language
{
    private static string $currentLang = 'cs';
    private static array $translations = [];

    public static function init(): void
    {
        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 1. Get from URL param
        if (isset($_GET['lang'])) {
            $lang = strtolower((string)$_GET['lang']);
            if (in_array($lang, SUPPORTED_LANGS, true)) {
                $_SESSION['lang'] = $lang;
                setcookie('lang', $lang, time() + (365 * 24 * 60 * 60), '/');
            }
        }

        // 2. Get from Session
        if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], SUPPORTED_LANGS, true)) {
            self::$currentLang = $_SESSION['lang'];
        }
        // 3. Get from Cookie
        elseif (isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], SUPPORTED_LANGS, true)) {
            self::$currentLang = $_COOKIE['lang'];
        }
        // 4. Default
        else {
            self::$currentLang = DEFAULT_LANG;
        }

        self::loadTranslations();
    }

    public static function get(): string
    {
        return self::$currentLang;
    }

    private static function loadTranslations(): void
    {
        $langFile = __DIR__ . '/../Views/lang/' . self::$currentLang . '.php';
        if (file_exists($langFile)) {
            self::$translations = require $langFile;
        } else {
            self::$translations = [];
        }
    }

    public static function translate(string $key, string $default = ''): string
    {
        return self::$translations[$key] ?? ($default ?: $key);
    }
}
