<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;

class StudentMiddleware {
    public function handle(Request $request): void {
        if (!Auth::check()) {
            redirect('login');
            return;
        }
        if (!Auth::isSinhVien()) {
            if (Auth::isNhanVien()) {
                redirect('staff/dashboard');
            } elseif (Auth::isQuanTriVien()) {
                redirect('admin/dashboard');
            } else {
                redirect('/');
            }
        }
    }
}
