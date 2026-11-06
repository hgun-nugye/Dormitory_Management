<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class StaffMiddleware {
    public function handle(Request $request): void {
        // 1. Kiểm tra đăng nhập và tài khoản hợp lệ
        if (!Auth::check() || !Auth::isUserActive()) {
            Session::set('intended_url', $request->getUri());
            if (!Session::has('__flash') || empty($_SESSION['__flash']['error'])) {
                Session::flash('error', 'Vui lòng đăng nhập tài khoản Cán bộ KTX để tiếp tục.');
            }
            redirect('login');
            exit;
        }

        // 2. Chống sinh viên vượt role qua URL vào khu vực Cán bộ KTX
        if (Auth::isSinhVien()) {
            Session::flash('error', 'Truy cập bị từ chối: Tài khoản Sinh viên không có quyền truy cập khu vực nghiệp vụ Cán bộ KTX!');
            redirect('student/dashboard');
            exit;
        }

        // 3. Chống Quản trị viên (ADMIN) vượt role qua URL vào khu vực Cán bộ KTX
        if (Auth::isQuanTriVien()) {
            Session::flash('error', 'Truy cập bị từ chối: Quản trị viên hệ thống (Admin) không có quyền truy cập khu vực vận hành của Cán bộ KTX!');
            redirect('admin/dashboard');
            exit;
        }

        // 4. Phải có vai trò Cán bộ KTX
        if (!Auth::isNhanVien()) {
            Session::flash('error', 'Truy cập bị từ chối: Yêu cầu quyền Cán bộ Quản lý KTX.');
            redirect('/');
            exit;
        }
    }
}
