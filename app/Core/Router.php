<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private $notFoundHandler = null;

    public function addRoute(string $method, string $path, $handler): void
    {
        // Convert route variables like {id} or {slug} to regex groups
        // e.g. /blog/{slug} -> ^/blog/(?P<slug>[^/]+)$
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        $pattern = '#^' . $pattern . '$#';

        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }

    public function get(string $path, $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function setNotFound($handler): void
    {
        $this->notFoundHandler = $handler;
    }

    public function resolve(Request $request)
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['pattern'], $uri, $matches)) {
                // Extract named parameter groups
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = urldecode($value);
                    }
                }
                $request->setRouteParams($params);

                $handler = $route['handler'];
                if (is_array($handler)) {
                    [$controllerClass, $methodName] = $handler;
                    $controller = new $controllerClass();
                    return $controller->$methodName($request);
                }

                if (is_callable($handler)) {
                    return call_user_func($handler, $request);
                }
            }
        }

        // Handle 404 Not Found
        if ($this->notFoundHandler) {
            if (is_array($this->notFoundHandler)) {
                [$controllerClass, $methodName] = $this->notFoundHandler;
                $controller = new $controllerClass();
                return $controller->$methodName($request);
            }
            if (is_callable($this->notFoundHandler)) {
                return call_user_func($this->notFoundHandler, $request);
            }
        }

        http_response_code(404);
        exit('Stránka nenalezena (404)');
    }
}
