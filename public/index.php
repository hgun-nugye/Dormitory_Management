<?php

// Front Controller - KTX ĐHQG-HCM

// Session Start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Load Helpers & Environment
require_once dirname(__DIR__) . '/app/Helpers/functions.php';
load_env(dirname(__DIR__) . '/.env');
date_default_timezone_set(config('app.timezone', 'Asia/Ho_Chi_Minh'));

// Cấu hình báo lỗi theo môi trường (.env)
$isDebug = function_exists('config') ? (bool)config('app.debug') : (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

if ($isDebug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(0);
}

// 2. Autoloader
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    spl_autoload_register(function ($class) {
        $prefix = 'App\\';
        $base_dir = dirname(__DIR__) . '/app/';
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
}

use App\Core\Router;
use App\Middlewares\AuthMiddleware;

// 3. Define Routes
Router::get('/', 'HomeController@index');
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login');
Router::get('/logout', 'AuthController@logout');
Router::get('/register', 'RegistrationController@show');
Router::post('/register', 'RegistrationController@submit');
Router::get('/register/options', 'RegistrationController@options');

// Student Routes - Cổng thông tin Sinh viên
Router::get('/student/dashboard', 'Student\\DashboardController@index', [AuthMiddleware::class]);
Router::get('/student/profile', 'Student\\ProfileController@index', [AuthMiddleware::class]);
Router::post('/student/profile/update', 'Student\\ProfileController@update', [AuthMiddleware::class]);
Router::get('/student/payments', 'Student\\PaymentController@index', [AuthMiddleware::class]);

// Student - Dịch vụ một cửa (Yêu cầu & Đơn từ trực tuyến)
Router::get('/student/requests', 'Student\\RequestController@index', [AuthMiddleware::class]);
Router::get('/student/requests/transfer', 'Student\\RequestController@transfer', [AuthMiddleware::class]);
Router::get('/student/requests/repair', 'Student\\RequestController@repair', [AuthMiddleware::class]);
Router::get('/student/requests/leave', 'Student\\RequestController@leave', [AuthMiddleware::class]);
Router::get('/student/requests/return', 'Student\\RequestController@returnRoom', [AuthMiddleware::class]);
Router::get('/student/requests/extension', 'Student\\RequestController@extension', [AuthMiddleware::class]);
Router::post('/student/requests', 'Student\\RequestController@create', [AuthMiddleware::class]);
Router::post('/student/requests/create', 'Student\\RequestController@create', [AuthMiddleware::class]);

// Staff Routes - Tách theo từng nghiệp vụ chuẩn
Router::get('/staff/dashboard', 'Staff\\DashboardController@index', [AuthMiddleware::class]);

// Staff - Quản lý Hạ tầng KTX (Khu, Cụm, Tòa, Phòng)
Router::get('/staff/zones', 'Staff\\ZoneController@zones', [AuthMiddleware::class]);
Router::get('/staff/zones/export', 'Staff\\ZoneController@exportZones', [AuthMiddleware::class]);
Router::post('/staff/zones/export', 'Staff\\ZoneController@exportZones', [AuthMiddleware::class]);
Router::get('/staff/zones/template', 'Staff\\ZoneController@downloadTemplate', [AuthMiddleware::class]);
Router::post('/staff/zones/import', 'Staff\\ZoneController@importZones', [AuthMiddleware::class]);
Router::post('/staff/zones/save-khu', 'Staff\\ZoneController@saveKhu', [AuthMiddleware::class]);
Router::post('/staff/zones/delete-khu', 'Staff\\ZoneController@deleteKhu', [AuthMiddleware::class]);
Router::post('/staff/zones/save-cum', 'Staff\\ZoneController@saveCum', [AuthMiddleware::class]);
Router::post('/staff/zones/delete-cum', 'Staff\\ZoneController@deleteCum', [AuthMiddleware::class]);
Router::post('/staff/zones/save-toa', 'Staff\\ZoneController@saveToa', [AuthMiddleware::class]);
Router::post('/staff/zones/delete-toa', 'Staff\\ZoneController@deleteToa', [AuthMiddleware::class]);
Router::post('/staff/zones/save-room', 'Staff\\ZoneController@saveRoom', [AuthMiddleware::class]);
Router::post('/staff/zones/delete-room', 'Staff\\ZoneController@deleteRoom', [AuthMiddleware::class]);
Router::post('/staff/zones/approve-proposal', 'Staff\\ZoneController@approveProposal', [AuthMiddleware::class]);
Router::post('/staff/zones/reject-proposal', 'Staff\\ZoneController@rejectProposal', [AuthMiddleware::class]);

// Staff - Quản lý Phòng & Giường nội trú (TRUONG_TOA)
Router::get('/staff/rooms', 'Staff\\RoomController@rooms', [AuthMiddleware::class]);
Router::post('/staff/rooms/checkin', 'Staff\\RoomController@confirmCheckIn', [AuthMiddleware::class]);
Router::post('/staff/rooms/assign-leader', 'Staff\\RoomController@assignRoomLeader', [AuthMiddleware::class]);
Router::post('/staff/rooms/toggle-bed-maintenance', 'Staff\\RoomController@toggleBedMaintenance', [AuthMiddleware::class]);
Router::post('/staff/rooms/toggle-room-maintenance', 'Staff\\RoomController@toggleRoomMaintenance', [AuthMiddleware::class]);
Router::get('/staff/rooms/export', 'Staff\\RoomController@exportRooms', [AuthMiddleware::class]);
Router::post('/staff/rooms/export', 'Staff\\RoomController@exportRooms', [AuthMiddleware::class]);
Router::get('/staff/rooms/template', 'Staff\\RoomController@downloadRoomTemplate', [AuthMiddleware::class]);
Router::post('/staff/rooms/import', 'Staff\\RoomController@importRooms', [AuthMiddleware::class]);
Router::post('/staff/rooms/save-room', 'Staff\\RoomController@saveRoomFromRooms', [AuthMiddleware::class]);
Router::post('/staff/rooms/delete-room', 'Staff\\RoomController@deleteRoomFromRooms', [AuthMiddleware::class]);
Router::post('/staff/rooms/toggle-status', 'Staff\\RoomController@toggleRoomStatus', [AuthMiddleware::class]);
Router::post('/staff/rooms/add-bed', 'Staff\\RoomController@addBed', [AuthMiddleware::class]);
Router::post('/staff/rooms/update-bed-status', 'Staff\\RoomController@updateBedStatus', [AuthMiddleware::class]);
Router::post('/staff/rooms/delete-bed', 'Staff\\RoomController@deleteBed', [AuthMiddleware::class]);
Router::post('/staff/rooms/update-toa', 'Staff\\RoomController@updateToaInfo', [AuthMiddleware::class]);
Router::post('/staff/rooms/confirm-checkin', 'Staff\\RoomController@confirmCheckIn', [AuthMiddleware::class]);
Router::post('/staff/rooms/toggle-ct01', 'Staff\\RoomController@toggleCT01', [AuthMiddleware::class]);
Router::post('/staff/rooms/remove-student', 'Staff\\RoomController@removeStudentFromBed', [AuthMiddleware::class]);

// Staff - Hồ sơ Đăng ký & Giữ chỗ 72h
Router::get('/staff/applications', 'Staff\\ApplicationController@applications', [AuthMiddleware::class]);
Router::get('/staff/applications/export', 'Staff\\ApplicationController@exportApplications', [AuthMiddleware::class]);
Router::get('/staff/applications/template', 'Staff\\ApplicationController@downloadTemplate', [AuthMiddleware::class]);
Router::post('/staff/applications/import', 'Staff\\ApplicationController@importApplications', [AuthMiddleware::class]);
Router::post('/staff/approve-application', 'Staff\\ApplicationController@approveApplication', [AuthMiddleware::class]);
Router::post('/staff/reject-application', 'Staff\\ApplicationController@rejectApplication', [AuthMiddleware::class]);
Router::post('/staff/applications/approve', 'Staff\\ApplicationController@approveApplication', [AuthMiddleware::class]);
Router::post('/staff/applications/reject', 'Staff\\ApplicationController@rejectApplication', [AuthMiddleware::class]);
Router::post('/staff/applications/scan-release', 'Staff\\ApplicationController@scanRelease72h', [AuthMiddleware::class]);
Router::post('/staff/applications/approve-miengiam', 'Staff\\ApplicationController@approveMienGiam', [AuthMiddleware::class]);
Router::post('/staff/applications/reject-miengiam', 'Staff\\ApplicationController@rejectMienGiam', [AuthMiddleware::class]);

// Staff - Tiện ích, Yêu cầu, Bảo trì, Kỷ luật
Router::get('/staff/utilities', 'Staff\\UtilityController@utilities', [AuthMiddleware::class]);
Router::get('/staff/utilities/export', 'Staff\\UtilityController@exportUtilities', [AuthMiddleware::class]);
Router::get('/staff/utilities/template', 'Staff\\UtilityController@downloadTemplate', [AuthMiddleware::class]);
Router::post('/staff/utilities/import', 'Staff\\UtilityController@importReadings', [AuthMiddleware::class]);
Router::post('/staff/utilities/save-reading', 'Staff\\UtilityController@saveReading', [AuthMiddleware::class]);
Router::post('/staff/utilities/publish-invoice', 'Staff\\UtilityController@publishInvoice', [AuthMiddleware::class]);
Router::post('/staff/utilities/publish-cluster', 'Staff\\UtilityController@publishClusterInvoices', [AuthMiddleware::class]);
Router::post('/staff/utilities/confirm-payment', 'Staff\\UtilityController@confirmPayment', [AuthMiddleware::class]);
Router::get('/staff/requests', 'Staff\\RequestController@requests', [AuthMiddleware::class]);
Router::get('/staff/requests/export', 'Staff\\RequestController@exportRequests', [AuthMiddleware::class]);
Router::post('/staff/requests/approve', 'Staff\\RequestController@approveRequest', [AuthMiddleware::class]);
Router::post('/staff/requests/reject', 'Staff\\RequestController@rejectRequest', [AuthMiddleware::class]);
Router::get('/staff/maintenance', 'Staff\\MaintenanceController@maintenance', [AuthMiddleware::class]);
Router::get('/staff/maintenance/export', 'Staff\\MaintenanceController@exportMaintenance', [AuthMiddleware::class]);
Router::post('/staff/maintenance/assign', 'Staff\\MaintenanceController@assign', [AuthMiddleware::class]);
Router::post('/staff/maintenance/create-staff-ticket', 'Staff\\MaintenanceController@createStaffTicket', [AuthMiddleware::class]);
Router::post('/staff/maintenance/update-progress', 'Staff\\MaintenanceController@updateProgress', [AuthMiddleware::class]);
Router::post('/staff/maintenance/complete', 'Staff\\MaintenanceController@complete', [AuthMiddleware::class]);
Router::post('/staff/maintenance/cancel', 'Staff\\MaintenanceController@cancel', [AuthMiddleware::class]);
Router::get('/staff/discipline', 'Staff\\DisciplineController@discipline', [AuthMiddleware::class]);
Router::post('/staff/discipline/record', 'Staff\\DisciplineController@record', [AuthMiddleware::class]);
Router::post('/staff/discipline/lock-submit', 'Staff\\DisciplineController@lockSubmit', [AuthMiddleware::class]);
Router::post('/staff/discipline/simulate-scan', 'Staff\\DisciplineController@simulateScan', [AuthMiddleware::class]);
Router::post('/staff/discipline/absence-action', 'Staff\\DisciplineController@absenceAction', [AuthMiddleware::class]);
Router::get('/staff/discipline/history', 'Staff\\DisciplineController@history', [AuthMiddleware::class]);
Router::get('/staff/discipline/export', 'Staff\\DisciplineController@export', [AuthMiddleware::class]);

// Admin Routes - Tách theo từng nghiệp vụ chuẩn
Router::get('/admin/dashboard', 'Admin\\DashboardController@index');

// Admin - Quản lý Cán bộ & Tài khoản
Router::get('/admin/users', 'Admin\\UserController@users');
Router::get('/admin/users/export', 'Admin\\UserController@exportUsers');
Router::post('/admin/users/export', 'Admin\\UserController@exportUsers');
Router::get('/admin/users/template', 'Admin\\UserController@downloadUserTemplate');
Router::post('/admin/users/import', 'Admin\\UserController@importUsers');
Router::post('/admin/users/save', 'Admin\\UserController@saveUser');
Router::post('/admin/users/reset-password', 'Admin\\UserController@resetPassword');
Router::post('/admin/users/toggle-status', 'Admin\\UserController@toggleUserStatus');
Router::post('/admin/users/delete', 'Admin\\UserController@deleteUser');

// Admin - Quản lý Tài khoản & Hồ sơ Sinh viên
Router::get('/admin/students', 'Admin\\StudentController@students');
Router::get('/admin/students/export', 'Admin\\StudentController@exportStudents');
Router::post('/admin/students/export', 'Admin\\StudentController@exportStudents');
Router::get('/admin/students/template', 'Admin\\StudentController@downloadStudentTemplate');
Router::post('/admin/students/import', 'Admin\\StudentController@importStudents');
Router::post('/admin/students/save', 'Admin\\StudentController@saveStudent');
Router::post('/admin/students/reset-password', 'Admin\\StudentController@resetPassword');
Router::post('/admin/students/toggle-status', 'Admin\\StudentController@toggleStudentStatus');
Router::post('/admin/students/delete', 'Admin\\StudentController@deleteStudent');
Router::post('/admin/students/test-mail', 'Admin\\StudentController@testMail');

Router::get('/admin/periods', 'Admin\\DashboardController@periods');
Router::get('/admin/room-types', 'Admin\\DashboardController@roomTypes');

// Admin - Quản lý Trường Đại học liên kết
Router::get('/admin/universities', 'Admin\\UniversityController@universities');
Router::get('/admin/universities/export', 'Admin\\UniversityController@exportUniversities');
Router::post('/admin/universities/export', 'Admin\\UniversityController@exportUniversities');
Router::get('/admin/universities/template', 'Admin\\UniversityController@downloadUniversityTemplate');
Router::post('/admin/universities/import', 'Admin\\UniversityController@importUniversities');
Router::post('/admin/universities/save', 'Admin\\UniversityController@saveUniversity');
Router::post('/admin/universities/toggle-status', 'Admin\\UniversityController@toggleUniversityStatus');
Router::post('/admin/universities/delete', 'Admin\\UniversityController@deleteUniversity');

// Cron Trigger Routes
Router::get('/cron/trigger_release', function () {
    $res = \App\Services\AutoReleaseService::scanAndReleaseExpiredBeds();
    json_response([
        'status' => 'success',
        'action' => 'Cron Job 72h Auto Release Beds',
        'result' => $res
    ]);
});

Router::get('/cron/trigger_absence', function () {
    $res = \App\Services\AttendanceDisciplineService::detectLongTermAbsence(30);
    json_response([
        'status' => 'success',
        'action' => 'Cron Job 30-Day Absence Detection',
        'result' => $res
    ]);
});

// 4. Dispatch Request & Catch Errors
try {
    Router::dispatch();
} catch (Throwable $e) { // Dùng Throwable để bắt được cả Error lẫn Exception
    http_response_code(500);

    $message = $isDebug
        ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')
        : 'Đã có lỗi kỹ thuật xảy ra trong quá trình xử lý yêu cầu. Vui lòng liên hệ ban quản lý KTX.';

    $fileInfo = $isDebug
        ? htmlspecialchars($e->getFile() . ':' . $e->getLine(), ENT_QUOTES, 'UTF-8')
        : null;

    $trace = $isDebug
        ? htmlspecialchars($e->getTraceAsString(), ENT_QUOTES, 'UTF-8')
        : null;

    echo <<<HTML
    <div style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background: #0f172a; min-height: 100vh; padding: 40px 20px; box-sizing: border-box; display: flex; justify-content: center; align-items: flex-start;">
        <div style="background: #ffffff; width: 100%; max-width: 860px; border-radius: 14px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.3); overflow: hidden; border: 1px solid #e2e8f0;">
            
            <div style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); padding: 22px 28px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="background: rgba(255,255,255,0.2); width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;">
                        ⚠️
                    </div>
                    <div>
                        <h2 style="margin: 0; color: #ffffff; font-size: 19px; font-weight: 700;">Hệ Thống Gặp Sự Cố</h2>
                        <span style="color: #fecaca; font-size: 13px;">KTX ĐHQG-HCM &bull; Application Error</span>
                    </div>
                </div>
                <span style="background: #fff; color: #b91c1c; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 9999px;">HTTP 500</span>
            </div>

            <div style="padding: 28px;">
                <div style="background: #fff5f5; border-left: 4px solid #ef4444; padding: 16px 18px; border-radius: 0 8px 8px 0; margin-bottom: 20px;">
                    <div style="font-size: 12px; font-weight: 700; color: #991b1b; text-transform: uppercase; margin-bottom: 4px;">Thông báo</div>
                    <div style="color: #7f1d1d; font-size: 15px; font-weight: 600; line-height: 1.5;">{$message}</div>
                </div>
HTML;

    if ($fileInfo) {
        echo <<<HTML
                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; background: #f8fafc; padding: 10px 14px; border-radius: 6px; border: 1px solid #e2e8f0; margin-bottom: 22px;">
                    <span style="font-weight: 600; color: #334155;">Vị trí phát sinh:</span>
                    <code style="font-family: Consolas, monospace; color: #0284c7;">{$fileInfo}</code>
                </div>
HTML;
    }

    if ($trace) {
        echo <<<HTML
                <div style="margin-top: 15px;">
                    <div style="font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px;">Stack Trace (Debug Mode)</div>
                    <pre style="background: #1e293b; color: #f1f5f9; padding: 18px; border-radius: 8px; font-size: 12.5px; line-height: 1.6; font-family: Consolas, monospace; overflow-x: auto;">{$trace}</pre>
                </div>
HTML;
    }

    echo <<<HTML
            </div>
            
            <div style="background: #f8fafc; padding: 14px 28px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                <a href="/" style="display: inline-flex; align-items: center; background: #0f172a; color: #fff; text-decoration: none; font-size: 13px; font-weight: 500; padding: 8px 16px; border-radius: 6px;">
                    &larr; Quay về trang chủ
                </a>
            </div>
        </div>
    </div>
HTML;
}
