<?php

namespace App\Core;

use Exception;

class Router {
    private static array $routes = [];

    public static function get(string $uri, $action, array $middlewares = []): void {
        self::addRoute('GET', $uri, $action, $middlewares);
    }

    public static function post(string $uri, $action, array $middlewares = []): void {
        self::addRoute('POST', $uri, $action, $middlewares);
    }

    private static function addRoute(string $method, string $uri, $action, array $middlewares): void {
        $uri = '/' . trim($uri, '/');
        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(): void {
        $request = new Request();
        $method = $request->getMethod();
        $uri = $request->getUri();

        foreach (self::$routes as $route) {
            if ($route['method'] === $method && self::matchUri($route['uri'], $uri, $params)) {
                // Execute Middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    $middlewareInstance->handle($request);
                }

                $action = $route['action'];

                if (is_callable($action)) {
                    call_user_func_array($action, $params);
                    return;
                }

                if (is_string($action)) {
                    list($controllerName, $methodName) = explode('@', $action);
                    $fullController = "App\\Controllers\\{$controllerName}";

                    if (!class_exists($fullController)) {
                        throw new Exception("Controller not found: {$fullController}");
                    }

                    $controller = new $fullController();

                    if (!method_exists($controller, $methodName)) {
                        throw new Exception("Method {$methodName} not found in {$fullController}");
                    }

                    call_user_func_array([$controller, $methodName], $params);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'>";
        echo "<h1>404 - Không tìm thấy trang</h1>";
        echo "<p>Đường dẫn yêu cầu <code>{$uri}</code> không tồn tại trên hệ thống.</p>";
        echo "<a href='" . url('/') . "' style='display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;'>Về trang chủ</a>";
        echo "</div>";
    }

    private static function matchUri(string $routeUri, string $requestUri, &$params): bool {
        $params = [];
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $routeUri);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestUri, $matches)) {
            foreach ($matches as $key => $val) {
                if (is_string($key)) {
                    $params[$key] = $val;
                }
            }
            return true;
        }
        return false;
    }
}
