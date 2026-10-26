<?php

/**
 * Load environment variables from .env file if not already loaded
 */
if (!function_exists('load_env')) {
    function load_env($filePath) {
        if (!file_exists($filePath)) {
            return;
        }
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                $value = trim($value, '"\'');
                if (getenv($name) === false) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }
}

/**
 * Get config value
 */
if (!function_exists('config')) {
    function config($key, $default = null) {
        static $configs = [];
        $parts = explode('.', $key);
        $file = $parts[0];
        
        if (!isset($configs[$file])) {
            $filePath = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (file_exists($filePath)) {
                $configs[$file] = require $filePath;
            } else {
                $configs[$file] = [];
            }
        }
        
        $value = $configs[$file];
        for ($i = 1; $i < count($parts); $i++) {
            if (isset($value[$parts[$i]])) {
                $value = $value[$parts[$i]];
            } else {
                return $default;
            }
        }
        
        return $value;
    }
}

/**
 * Render a view file
 */
if (!function_exists('view')) {
    function view($viewName, $data = []) {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $viewName) . '.php';
        if (!file_exists($viewPath)) {
            throw new Exception("View file not found: {$viewPath}");
        }
        require $viewPath;
    }
}

/**
 * Redirect to an internal or external URL
 */
if (!function_exists('redirect')) {
    function redirect($path) {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = url($path);
        }
        header("Location: {$path}");
        exit;
    }
}

/**
 * Generate full URL based on application base path
 */
if (!function_exists('url')) {
    function url($path = '') {
        $baseUrl = rtrim(config('app.url', 'http://localhost/Dormitory_Management'), '/');
        $path = ltrim($path, '/');
        return $baseUrl . ($path ? '/' . $path : '');
    }
}

/**
 * Asset URL helper
 */
if (!function_exists('asset')) {
    function asset($path) {
        return url('public/assets/' . ltrim($path, '/'));
    }
}

/**
 * Session helper
 */
if (!function_exists('session')) {
    function session($key = null, $default = null) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if ($key === null) {
            return $_SESSION;
        }
        return $_SESSION[$key] ?? $default;
    }
}

/**
 * Auth helper
 */
if (!function_exists('auth')) {
    function auth() {
        return \App\Core\Auth::user();
    }
}

/**
 * Format money in VND
 */
if (!function_exists('format_money')) {
    function format_money($amount) {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }
}

/**
 * CSRF token helper
 */
if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field() {
        return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
    }
}

/**
 * Response JSON
 */
if (!function_exists('json_response')) {
    function json_response($data, $statusCode = 200) {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}

/**
 * Debug and Die
 */
if (!function_exists('dd')) {
    function dd(...$vars) {
        echo '<style>body{background:#18181b;color:#e4e4e7;padding:20px;font-family:monospace;font-size:14px;}</style>';
        echo '<h2>Debugging:</h2>';
        foreach ($vars as $v) {
            echo '<pre style="background:#27272a;padding:15px;border-radius:8px;border:1px solid #3f3f46;">';
            var_dump($v);
            echo '</pre>';
        }
        exit;
    }
}
