<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Core\Database;
use App\Services\MailService;

class AuthController extends Controller {
    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
            return;
        }

        $error = Session::flash('error');
        $success = Session::flash('success');
        $this->view('auth.login', ['error' => $error, 'success' => $success]);
    }

    public function login(): void {
        $request = new Request();
        $username = trim($request->input('username', ''));
        $password = (string) $request->input('password', '');

        $submittedToken = (string) $request->input('csrf_token', '');
        $sessionToken = (string) Session::get('csrf_token', '');
        if ($submittedToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $submittedToken)) {
            Session::flash('error', 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.');
            $this->redirect('login');
            return;
        }

        if (empty($username) || empty($password)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu.');
            $this->redirect('login');
            return;
        }

        if (Auth::login($username, $password)) {
            $this->redirectBasedOnRole();
        } else {
            Session::flash('error', 'Tên đăng nhập hoặc mật khẩu không chính xác.');
            $this->redirect('login');
        }
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('login');
    }


    private function redirectBasedOnRole(): void {
        if (Auth::isSinhVien()) {
            $this->redirect('student/dashboard');
        } elseif (Auth::isNhanVien()) {
            $this->redirect('staff/dashboard');
        } elseif (Auth::isQuanTriVien()) {
            $this->redirect('admin/dashboard');
        } else {
            $this->redirect('/');
        }
    }
}
