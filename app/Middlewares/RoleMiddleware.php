<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;

class RoleMiddleware {
    protected array $allowedRoles = [];

    public function __construct(array $roles = []) {
        $this->allowedRoles = $roles;
    }

    public function handle(Request $request): void {
        if (!Auth::check()) {
            redirect('login');
        }

        $currentRole = Auth::role();
        if (!empty($this->allowedRoles) && !in_array($currentRole, $this->allowedRoles)) {
            http_response_code(403);
            echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'>";
            echo "<h1 style='color:#dc2626;'>403 - Bạn không có quyền truy cập</h1>";
            echo "<p>Tài khoản với vai trò <b>{$currentRole}</b> không được phép truy cập chức năng này.</p>";
            echo "<a href='" . url('/') . "' style='display:inline-block;padding:10px 20px;background:#2563eb;color:#fff;text-decoration:none;border-radius:6px;'>Về trang chủ</a>";
            echo "</div>";
            exit;
        }
    }
}
