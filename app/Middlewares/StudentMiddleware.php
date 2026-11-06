<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class StudentMiddleware {
    public function handle(Request $request): void {
        // 1. Kiểm tra đăng nhập và tài khoản hợp lệ
        if (!Auth::check() || !Auth::isUserActive()) {
            Session::set('intended_url', $request->getUri());
            if (!Session::has('__flash') || empty($_SESSION['__flash']['error'])) {
                Session::flash('error', 'Vui lòng đăng nhập tài khoản Sinh viên để tiếp tục.');
            }
            redirect('login');
            exit;
        }

        // 2. Chặn Cán bộ KTX và Quản trị viên truy cập Cổng Sinh viên qua URL
        if (!Auth::isSinhVien()) {
            if (Auth::isQuanTriVien()) {
                Session::flash('warning', 'Bạn đang đăng nhập với vai trò Quản trị viên, đã tự động chuyển hướng về Bảng điều khiển Quản trị.');
                redirect('admin/dashboard');
                exit;
            } elseif (Auth::isNhanVien()) {
                Session::flash('warning', 'Bạn đang đăng nhập với vai trò Cán bộ KTX, đã tự động chuyển hướng về Bảng điều khiển Cán bộ.');
                redirect('staff/dashboard');
                exit;
            } else {
                Session::flash('error', 'Truy cập bị từ chối: Tài khoản hiện tại không có quyền truy cập Cổng Sinh viên.');
                redirect('/');
                exit;
            }
        }
    }
}
