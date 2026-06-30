<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Language;
use App\Core\Settings;

abstract class Controller
{
    /**
     * Renders a view wrapped in a layout
     */
    protected function render(string $__viewPath__, array $data = [], string $layout = 'public'): void
    {
        // Extract variables to be accessible directly in the view
        extract($data);

        // Get language helpers
        $lang = Language::get();
        $t = function(string $key, string $default = ''): string {
            return Language::translate($key, $default);
        };

        // Determine theme configuration
        $activeTheme = Settings::get('active_theme', 'warm_mediterranean');

        // Start capturing view content
        ob_start();
        $viewFullPath = __DIR__ . '/../Views/' . $__viewPath__ . '.php';
        if (file_exists($viewFullPath)) {
            require $viewFullPath;
        } else {
            echo "View file not found: $__viewPath__";
        }
        $content = ob_get_clean();

        // Load the main layout container
        $layoutFullPath = __DIR__ . '/../Views/layouts/' . $layout . '.php';
        if (file_exists($layoutFullPath)) {
            require $layoutFullPath;
        } else {
            echo $content; // Output raw view if layout is missing
        }
    }

    /**
     * Send JSON HTTP response
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Generate secure CSRF token
     */
    protected function generateCsrf(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Verify CSRF token
     */
    protected function verifyCsrf(string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $storedToken = $_SESSION['csrf_token'] ?? '';
        return hash_equals($storedToken, $token);
    }
}
