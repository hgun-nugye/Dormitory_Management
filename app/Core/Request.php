<?php

namespace App\Core;

class Request {
    public function getMethod(): string {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function getUri(): string {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        // 1. Loại bỏ Query String (?key=value)
        $pos = strpos($uri, '?');
        if ($pos !== false) {
            $uri = substr($uri, 0, $pos);
        }

        // Chuẩn hóa dấu gạch chéo
        $uri = str_replace('\\', '/', $uri);

        // 2. Tự động bóc tách base path từ SCRIPT_NAME (ví dụ: /Dormitory_Management/public/index.php)
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/'); // /Dormitory_Management/public

        if (!empty($scriptDir) && $scriptDir !== '/' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        } else {
            // Khi rewrite qua .htaccess ở thư mục gốc (uri là /Dormitory_Management/login)
            $parentDir = rtrim(dirname($scriptDir), '/'); // /Dormitory_Management
            if (!empty($parentDir) && $parentDir !== '/' && str_starts_with($uri, $parentDir)) {
                $uri = substr($uri, strlen($parentDir));
            }
        }

        // 3. Fallback theo APP_URL cấu hình trong .env
        $appPath = parse_url(config('app.url', ''), PHP_URL_PATH);
        if (!empty($appPath) && $appPath !== '/' && str_starts_with($uri, $appPath)) {
            $uri = substr($uri, strlen($appPath));
        }

        // 4. Nếu còn prefix /public thì bóc tiếp
        if (str_starts_with($uri, '/public')) {
            $uri = substr($uri, 7);
        }

        $uri = '/' . trim($uri, '/');
        return $uri;
    }

    public function all(): array {
        $body = [];
        if ($this->getMethod() === 'GET') {
            $body = $_GET;
        } elseif ($this->getMethod() === 'POST') {
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (str_contains($contentType, 'application/json')) {
                $raw = file_get_contents('php://input');
                $body = json_decode($raw, true) ?? [];
            } else {
                $body = $_POST;
            }
        }
        return $body;
    }

    public function input(string $key, $default = null) {
        $data = $this->all();
        return $data[$key] ?? $default;
    }

    public function file(string $key): ?array {
        return $_FILES[$key] ?? null;
    }
}
