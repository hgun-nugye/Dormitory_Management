<?php

namespace App\Middlewares;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Session;

class AuthMiddleware {
    public function handle(Request $request): void {
        if (!Auth::check() || !Auth::isUserActive()) {
            Session::set('intended_url', $request->getUri());
            if (!Session::has('__flash') || empty($_SESSION['__flash']['error'])) {
                Session::flash('error', 'Vui lòng đăng nhập để tiếp tục truy cập hệ thống.');
            }
            redirect('login');
            exit;
        }
    }
}
