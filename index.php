<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/auth.php';

if (!isset($_SESSION['student_id'])) {
    $view = 'login';
} else {
    require_once __DIR__ . '/includes/student-controller.php';

    $allowedViews = ['dashboard', 'profile', 'payments', 'activities', 'requests', 'registration'];
    $view = $_GET['view'] ?? 'dashboard';
    if (!in_array($view, $allowedViews, true)) {
        $view = 'dashboard';
    }
}

$title = match ($view) {
    'profile' => 'Thông tin cá nhân',
    'payments' => 'Thanh toán',
    'activities' => 'Hoạt động rèn luyện',
    'requests' => 'Yêu cầu hỗ trợ',
    'registration' => 'Đăng ký chỗ ở',
    'login' => 'Đăng nhập',
    default => 'Tổng quan sinh viên',
};

include __DIR__ . '/views/shell.php';
