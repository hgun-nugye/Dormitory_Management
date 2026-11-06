<?php

namespace App\Core;

class Session {
    /**
     * Khởi tạo Session an toàn theo chuẩn OWASP
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            if (!headers_sent()) {
                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

                // 1. Chống session hijacking và fixation qua PHP ini
                @ini_set('session.use_strict_mode', '1');
                @ini_set('session.use_only_cookies', '1');

                // 2. Cấu hình tham số cookie an toàn
                session_set_cookie_params([
                    'lifetime' => 0,              // Hết hạn khi đóng trình duyệt
                    'path' => '/',
                    'domain' => '',
                    'secure' => $isHttps,         // Chỉ gửi qua HTTPS khi có SSL
                    'httponly' => true,           // Chống XSS đọc cookie session
                    'samesite' => 'Lax'           // Chống CSRF tấn công từ website khác
                ]);
            }

            @session_start();
        }
    }

    /**
     * Tái tạo Session ID chống Session Fixation
     */
    public static function regenerate(bool $deleteOldSession = true): bool {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            return session_regenerate_id($deleteOldSession);
        }
        return false;
    }

    /**
     * Khởi tạo chữ ký bảo mật phiên khi người dùng đăng nhập
     */
    public static function initSecurityFingerprint(): void {
        self::start();
        $_SESSION['_security_agent_hash'] = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_AGENT');
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
    }

    /**
     * Xác thực tính toàn vẹn và thời gian sống của phiên làm việc
     * @param int $timeoutSeconds Mặc định 3600 giây (60 phút bất hoạt)
     */
    public static function validateSecurity(int $timeoutSeconds = 3600): bool {
        self::start();
        if (!isset($_SESSION['user'])) {
            return true;
        }

        // 1. Chống Session Hijacking: Kiểm tra User-Agent
        $currentAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN_AGENT');
        $savedAgentHash = $_SESSION['_security_agent_hash'] ?? null;
        if ($savedAgentHash !== null && !hash_equals($savedAgentHash, $currentAgentHash)) {
            self::destroy();
            return false;
        }

        // 2. Kiểm tra thời gian bất hoạt (Idle timeout)
        $lastActivity = $_SESSION['last_activity'] ?? null;
        if ($lastActivity !== null && (time() - (int)$lastActivity > $timeoutSeconds)) {
            self::destroy();
            return false;
        }

        // Cập nhật lại thời gian hoạt động gần nhất
        $_SESSION['last_activity'] = time();
        return true;
    }

    public static function set(string $key, $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, $value = null) {
        self::start();
        if ($value !== null) {
            $_SESSION['__flash'][$key] = $value;
            return;
        }

        $val = $_SESSION['__flash'][$key] ?? null;
        unset($_SESSION['__flash'][$key]);
        return $val;
    }

    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (!headers_sent() && ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
