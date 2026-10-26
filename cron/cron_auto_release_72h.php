<?php

/**
 * CRON JOB: Tự động thu hồi chỗ sau 72h quá hạn thanh toán
 * Chạy định kỳ 15 phút một lần trên hệ thống:
 * php cron/cron_auto_release_72h.php
 */

require_once __DIR__ . '/../app/Helpers/functions.php';
load_env(__DIR__ . '/../.env');

// Tự động nạp class
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

echo "[" . date('Y-m-d H:i:s') . "] Khởi chạy Cron Job kiểm tra thu hồi giường 72h...\n";

$result = \App\Services\AutoReleaseService::scanAndReleaseExpiredBeds();

echo "-> Tổng số hồ sơ quét: " . $result['total_scanned'] . "\n";
echo "-> Số giường đã giải phóng: " . $result['total_released'] . "\n";
if (!empty($result['released_beds'])) {
    echo "-> Danh sách mã giường đã mở lại: " . implode(', ', $result['released_beds']) . "\n";
}
echo "[" . date('Y-m-d H:i:s') . "] Hoàn tất tiến trình ngầm.\n";
