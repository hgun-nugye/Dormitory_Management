<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class AdminMiddleware {
    public function handle(Request $request): void {
        // 1. Kiểm tra đăng nhập và tài khoản hợp lệ
        if (!Auth::check() || !Auth::isUserActive()) {
            Session::set('intended_url', $request->getUri());
            if (!Session::has('__flash') || empty($_SESSION['__flash']['error'])) {
                Session::flash('error', 'Vui lòng đăng nhập tài khoản Quản trị viên để tiếp tục.');
            }
            redirect('login');
            exit;
        }

        // 2. Chống vượt role qua URL vào khu vực Quản trị viên
        if (!Auth::isQuanTriVien()) {
            if (Auth::isSinhVien()) {
                Session::flash('error', 'Truy cập bị từ chối: Tài khoản Sinh viên không có quyền truy cập khu vực Quản trị viên hệ thống!');
                redirect('student/dashboard');
                exit;
            } elseif (Auth::isNhanVien()) {
                Session::flash('error', 'Truy cập bị từ chối: Cán bộ KTX không có quyền truy cập khu vực Quản trị viên hệ thống!');
                redirect('staff/dashboard');
                exit;
            } else {
                Session::flash('error', 'Truy cập bị từ chối: Yêu cầu quyền Quản trị viên hệ thống.');
                redirect('/');
                exit;
            }
        }
    }
}

