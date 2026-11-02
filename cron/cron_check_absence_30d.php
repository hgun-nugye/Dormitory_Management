<?php

/**
 * CRON JOB: Giám sát sinh viên vắng mặt 30 ngày liên tục không phép
 * Chạy hàng ngày vào lúc 02:00 sáng:
 * php cron/cron_check_absence_30d.php
 */

require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

echo "[" . date('Y-m-d H:i:s') . "] Khởi chạy Cron Job kiểm tra sinh viên vắng mặt 30 ngày...\n";

$result = \App\Services\AttendanceDisciplineService::detectLongTermAbsence(30);

echo "-> Tổng số sinh viên nội trú đã kiểm tra: " . $result['total_checked'] . "\n";
echo "-> Số sinh viên vi phạm / vắng mặt quá 30 ngày: " . $result['total_warnings'] . "\n";

foreach ($result['warnings'] as $w) {
    echo "  [CẢNH BÁO] MSSV: {$w['maSinhVien']} | Họ tên: {$w['hoVaTen']} | Phòng: {$w['maPhong']} | Số ngày vắng: {$w['soNgayVang']} ngày | SĐT PH: {$w['sdtPhuHuynh']}\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Hoàn tất đối soát điểm danh.\n";
