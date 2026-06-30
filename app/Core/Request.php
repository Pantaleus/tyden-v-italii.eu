<?php
declare(strict_types=1);

namespace App\Core;

class Request
{
    private array $routeParams = [];

    public function getMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        // Strip query string
        $position = strpos($uri, '?');
        if ($position !== false) {
            $uri = substr($uri, 0, $position);
        }
        return '/' . trim($uri, '/');
    }

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    /**
     * Get input parameters safely with sanitization
     */
    public function getParams(): array
    {
        $body = [];
        if ($this->isGet()) {
            foreach ($_GET as $key => $val) {
                $body[$key] = $this->sanitizeInput($val);
            }
        } elseif ($this->isPost()) {
            foreach ($_POST as $key => $val) {
                $body[$key] = $this->sanitizeInput($val);
            }
            // Also read raw JSON body if applicable
            $json = json_decode((string)file_get_contents('php://input'), true);
            if (is_array($json)) {
                foreach ($json as $key => $val) {
                    $body[$key] = $this->sanitizeInput($val);
                }
            }
        }
        return $body;
    }

    public function getParam(string $key, $default = null)
    {
        $params = $this->getParams();
        return $params[$key] ?? $default;
    }

    public function setRouteParams(array $params): self
    {
        $this->routeParams = $params;
        return $this;
    }

    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    public function getRouteParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $default;
    }

    /**
     * Recursive input sanitization (trimming whitespace)
     */
    private function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        if (is_string($input)) {
            return trim($input);
        }
        return $input;
    }

    /**
     * Get IP address, respecting reverse proxies if any
     */
    public function getIp(): string
    {
        $ipSources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipSources as $source) {
            if (!empty($_SERVER[$source])) {
                $ipList = explode(',', $_SERVER[$source]);
                $ip = trim(end($ipList));
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    public function getUserAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public function getReferer(): string
    {
        return $_SERVER['HTTP_REFERER'] ?? '';
    }
}
