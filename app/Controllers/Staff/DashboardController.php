<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\DashboardService;
use App\Services\AutoReleaseService;
use App\Services\AttendanceDisciplineService;
use App\Services\RoomConsolidationService;

/**
 * Controller: Bảng điều khiển Trung tâm BQL KTX (Staff Dashboard)
 * View: views/staff/dashboard.php
 * 
 * Kiến trúc module hóa tích hợp đa tầng RBAC:
 * - Admin / Giám đốc: Toàn cảnh 2 Khu A & B, dropdown lọc linh hoạt
 * - Trưởng Khu: Giới hạn trong Khu A hoặc Khu B
 * - Trưởng Cụm: Giới hạn trong Cụm nhà quản lý
 * - Trưởng Tòa: Khóa cố định trong Tòa nhà trực tiếp
 * - Kế toán: Dashboard Tài chính & Công nợ chuyên biệt
 * - Kỹ thuật viên: Dashboard Nhiệm vụ sửa chữa cá nhân
 * - An ninh: Dashboard Giám sát Barrier & Vắng mặt
 * - Sinh viên / Trưởng phòng: Chặn 100% bằng Red Warning Card
 */
class DashboardController extends Controller
{
    /**
     * Xác định phạm vi và quyền hạn của cán bộ trên Dashboard
     */
    public function getStaffScope(?array $user = null): array
    {
        return DashboardService::getStaffScope($user);
    }

    /**
     * Màn hình tổng quan Bảng điều khiển Trung tâm BQL KTX
     */
    public function index(): void
    {
        $user = Auth::user() ?? [];
        $scope = $this->getStaffScope($user);

        // Flash message
        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');

        // 1. Nếu bị từ chối quyền truy cập (Sinh viên, Trưởng phòng, khách vãng lai)
        if (!$scope['isAllowed']) {
            $this->view('staff.dashboard', [
                'user' => $user,
                'scope' => $scope,
                'isAllowed' => false,
                'viewType' => 'blocked',
                'flashSuccess' => $flashSuccess,
                'flashError' => $flashError,
            ]);
            return;
        }

        // 2. Phân nhánh Kế toán & Tài chính
        if (!empty($scope['isKeToan'])) {
            $accountingData = DashboardService::getAccountingDashboardData();
            $this->view('staff.dashboard', [
                'user' => $user,
                'scope' => $scope,
                'isAllowed' => true,
                'viewType' => 'accounting',
                'accountingData' => $accountingData,
                'flashSuccess' => $flashSuccess,
                'flashError' => $flashError,
            ]);
            return;
        }

        // 3. Phân nhánh Kỹ thuật viên Sửa chữa
        if (!empty($scope['isKyThuat'])) {
            $technicalData = DashboardService::getTechnicalDashboardData($scope['userId'] ?? 0, $scope['maCum'] ?? null);
            $this->view('staff.dashboard', [
                'user' => $user,
                'scope' => $scope,
                'isAllowed' => true,
                'viewType' => 'technical',
                'technicalData' => $technicalData,
                'flashSuccess' => $flashSuccess,
                'flashError' => $flashError,
            ]);
            return;
        }

        // 4. Phân nhánh Cán bộ Quản lý (Admin, Trưởng Khu, Trưởng Cụm, Trưởng Tòa)
        $filterKhu = trim($_GET['khu'] ?? '');
        $filterCum = trim($_GET['cum'] ?? '');
        $filterToa = trim($_GET['toa'] ?? '');

        // Ràng buộc bảo mật RBAC với các tham số lọc URL
        if ($scope['level'] === 'KHU') {
            $filterKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'CUM') {
            $filterKhu = $scope['maKhu'];
            $filterCum = $scope['maCum'];
        } elseif ($scope['level'] === 'TOA') {
            $filterKhu = $scope['maKhu'];
            $filterCum = $scope['maCum'];
            $filterToa = $scope['maToa'];
        }

        $dashboardData = DashboardService::getManagerDashboardData($scope, $filterKhu, $filterCum, $filterToa);

        $this->view('staff.dashboard', array_merge([
            'user' => $user,
            'scope' => $scope,
            'isAllowed' => true,
            'viewType' => 'manager',
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
        ], $dashboardData));
    }

    /**
     * Thao tác thủ công: Kích hoạt đối soát thu hồi chỗ 72h
     */
    public function triggerRelease(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền chạy tác vụ này!');
            $this->redirect('staff/dashboard');
            return;
        }

        try {
            $result = AutoReleaseService::scanAndReleaseExpiredBeds();
            $released = $result['released'] ?? 0;
            Session::flash('success', "Đã hoàn tất quét đối soát 72h: Thu hồi {$released} chỗ ở quá hạn thanh toán.");
        } catch (\Throwable $e) {
            Session::flash('error', "Lỗi khi quét đối soát: " . $e->getMessage());
        }

        $this->redirect('staff/dashboard');
    }

    /**
     * Thao tác thủ công: Kích hoạt rà quét sinh viên vắng mặt 30 ngày
     */
    public function triggerAbsence(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền chạy tác vụ này!');
            $this->redirect('staff/dashboard');
            return;
        }

        try {
            $result = AttendanceDisciplineService::detectLongTermAbsence(30);
            $count = count($result['warnings'] ?? []);
            Session::flash('success', "Đã hoàn tất quét điểm danh: Phát hiện {$count} trường hợp vắng mặt trên 30 ngày.");
        } catch (\Throwable $e) {
            Session::flash('error', "Lỗi khi quét vắng mặt: " . $e->getMessage());
        }

        $this->redirect('staff/dashboard');
    }

    // Forwarding methods cho tương thích ngược

    // Module Hạ tầng KTX (ZoneController)
    public function zones(): void { (new ZoneController())->zones(); }
    public function saveToa(): void { (new ZoneController())->saveToa(); }
    public function deleteToa(): void { (new ZoneController())->deleteToa(); }
    public function saveCum(): void { (new ZoneController())->saveCum(); }
    public function deleteCum(): void { (new ZoneController())->deleteCum(); }
    public function saveKhu(): void { (new ZoneController())->saveKhu(); }
    public function deleteKhu(): void { (new ZoneController())->deleteKhu(); }
    public function saveRoom(): void { (new ZoneController())->saveRoom(); }
    public function deleteRoom(): void { (new ZoneController())->deleteRoom(); }
    public function exportZones(): void { (new ZoneController())->exportZones(); }
    public function downloadTemplate(): void { (new ZoneController())->downloadTemplate(); }
    public function importZones(): void { (new ZoneController())->importZones(); }
    public function approveProposal(): void { (new ZoneController())->approveProposal(); }
    public function rejectProposal(): void { (new ZoneController())->rejectProposal(); }

    // Module Quản lý Phòng & Giường (RoomController)
    public function rooms(): void { (new RoomController())->rooms(); }
    public function exportRooms(): void { (new RoomController())->exportRooms(); }
    public function downloadRoomTemplate(): void { (new RoomController())->downloadRoomTemplate(); }
    public function importRooms(): void { (new RoomController())->importRooms(); }
    public function saveRoomFromRooms(): void { (new RoomController())->saveRoomFromRooms(); }
    public function deleteRoomFromRooms(): void { (new RoomController())->deleteRoomFromRooms(); }
    public function toggleRoomStatus(): void { (new RoomController())->toggleRoomStatus(); }
    public function addBed(): void { (new RoomController())->addBed(); }
    public function updateBedStatus(): void { (new RoomController())->updateBedStatus(); }
    public function deleteBed(): void { (new RoomController())->deleteBed(); }
    public function assignRoomLeader(): void { (new RoomController())->assignRoomLeader(); }
    public function confirmCheckIn(): void { (new RoomController())->confirmCheckIn(); }
    public function toggleCT01(): void { (new RoomController())->toggleCT01(); }
    public function removeStudentFromBed(): void { (new RoomController())->removeStudentFromBed(); }
    public function toggleBedMaintenance(): void { (new RoomController())->toggleBedMaintenance(); }
    public function toggleRoomMaintenance(): void { (new RoomController())->toggleRoomMaintenance(); }

    // Module Tiếp nhận & Duyệt hồ sơ (ApplicationController)
    public function applications(): void { (new ApplicationController())->applications(); }
    public function approveApplication(): void { (new ApplicationController())->approveApplication(); }
    public function rejectApplication(): void { (new ApplicationController())->rejectApplication(); }

    // Module Điện nước & Tiện ích (UtilityController)
    public function utilities(): void { (new UtilityController())->utilities(); }

    // Module Yêu cầu sinh viên (RequestController)
    public function requests(): void { (new RequestController())->requests(); }

    // Module Quản lý Bảo trì (MaintenanceController)
    public function maintenance(): void { (new MaintenanceController())->maintenance(); }

    // Module Kỷ luật & Điểm danh (DisciplineController)
    public function discipline(): void { (new DisciplineController())->discipline(); }
}
