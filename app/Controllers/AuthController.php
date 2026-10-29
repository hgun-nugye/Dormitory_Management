<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;

class AuthController extends Controller {
    public function showLogin(): void {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
            return;
        }

        $error = Session::flash('error');
        $this->view('auth.login', ['error' => $error]);
    }

    public function login(): void {
        $request = new Request();
        $username = trim($request->input('username', ''));
        $password = trim($request->input('password', ''));

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
