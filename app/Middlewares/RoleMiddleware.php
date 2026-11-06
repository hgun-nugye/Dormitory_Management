<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class RoleMiddleware {
    protected array $allowedRoles = [];

    public function __construct(array $roles = []) {
        $this->allowedRoles = $roles;
    }

    public function handle(Request $request, ...$roles): void {
        $allowed = !empty($roles) ? $roles : $this->allowedRoles;

        if (!Auth::check() || !Auth::isUserActive()) {
            Session::set('intended_url', $request->getUri());
            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            redirect('login');
            exit;
        }

        $currentRole = (string)Auth::role();

        if (!empty($allowed) && !in_array($currentRole, $allowed, true)) {
            // Chuyển hướng thân thiện theo vai trò nếu bị từ chối
            if (Auth::isSinhVien()) {
                Session::flash('error', 'Truy cập bị từ chối: Tài khoản của bạn không có quyền truy cập chức năng này.');
                redirect('student/dashboard');
                exit;
            }

            if (Auth::isNhanVien()) {
                Session::flash('error', 'Truy cập bị từ chối: Chức năng không thuộc phạm vi quyền hạn của bạn.');
                redirect('staff/dashboard');
                exit;
            }

            if (Auth::isQuanTriVien()) {
                Session::flash('error', 'Truy cập bị từ chối: Quản trị viên không có quyền truy cập chức năng này.');
                redirect('admin/dashboard');
                exit;
            }

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
