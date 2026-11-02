<?php

return [
    'name' => getenv('APP_NAME') ?: 'Hệ thống Quản lý KTX ĐHQG-HCM',
    'env' => getenv('APP_ENV') ?: 'development',
    'debug' => (bool)(getenv('APP_DEBUG') ?: true),
    'url' => getenv('APP_URL') ?: 'http://localhost/Dormitory_Management',
    'timezone' => 'Asia/Ho_Chi_Minh',
    'locale' => 'vi',
    
    // Tham số nghiệp vụ
    'holding_hours' => (int)(getenv('HOLDING_HOURS') ?: 72),
    'absence_warning_days' => (int)(getenv('ABSENCE_WARNING_DAYS') ?: 30),
];
