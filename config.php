<?php
/**
 * System Configuration
 * tyden-v-italii.eu
 */

// Prevent direct access
if (count(get_included_files()) === 1) {
    http_response_code(403);
    exit('Direct access forbidden.');
}

// Load environment variables from .env file
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $val = trim($parts[1]);
            // Remove wrapping quotes if present
            if (preg_match('/^"?(.*?)"?$/', $val, $matches)) {
                $val = $matches[1];
            }
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Database Credentials
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'tyden_v_italii');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// System Settings
define('DEV_MODE', ($_ENV['DEV_MODE'] ?? 'true') === 'true');
define('BASE_URL', $_ENV['BASE_URL'] ?? 'https://tyden-v-italii.eu');


// Localization Settings
define('DEFAULT_LANG', 'cs');
define('SUPPORTED_LANGS', ['cs', 'en', 'it']);

// Security Salts / Keys (Keep them unique)
define('APP_SECRET', 'd41d8cd98f00b204e9800998ecf8427e_italy_love_key');
