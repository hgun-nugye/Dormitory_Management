<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\RoomConsolidationService;
use App\Services\AttendanceDisciplineService;
use App\Services\AutoReleaseService;

/**
 * Controller: Bảng điều khiển Cán bộ BQL KTX (Staff Dashboard)
 * View: views/staff/dashboard.php
 * 
 * Kiến trúc module hóa tách biệt theo từng nghiệp vụ:
 * - Hạ tầng KTX (Khu, Cụm, Tòa, Phòng): App\Controllers\Staff\ZoneController
 * - Quản lý Phòng & Giường nội trú: App\Controllers\Staff\RoomController
 * - Hồ sơ Đăng ký & Duyệt giữ chỗ: App\Controllers\Staff\ApplicationController
 * - Điện nước & Tiện ích: App\Controllers\Staff\UtilityController
 * - Yêu cầu sinh viên: App\Controllers\Staff\RequestController
 * - Quản lý Bảo trì & Sửa chữa: App\Controllers\Staff\MaintenanceController
 * - Kỷ luật & Điểm danh lưu trú: App\Controllers\Staff\DisciplineController
 */
class DashboardController extends Controller
{
    /**
     * Lấy mã Cụm quản lý của cán bộ đang đăng nhập
     */
    private function getMaCum(): string
    {
        $user = Auth::user();
        $maCum = 'AG';
        if (!empty($user['profile']['HoVaTen'])) {
            $cum = Database::fetch(
                "SELECT MaCum FROM CumNha WHERE HoTenTruongCum = :name LIMIT 1",
                ['name' => $user['profile']['HoVaTen']]
            );
            if ($cum) {
                $maCum = $cum['MaCum'];
            }
        }
        return $maCum;
    }

    /**
     * Màn hình tổng quan Bảng điều khiển Cán bộ KTX
     */
    public function index(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        // Hồ sơ chờ duyệt
        $pendingApplications = [];
        $consolidation = [];
        $absenceWarnings = [];
        $maintenanceTickets = [];

        try {
            $pendingApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.GioiTinh, g.MaPhong, t.TenTruong
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE hs.TrangThaiHoSo = 'Chờ duyệt' AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianNop ASC
            ", ['cum' => $maCum]);

            // Đề xuất dồn phòng (Chức năng 3)
            $consolidation = RoomConsolidationService::getUnderOccupiedRooms($maCum);

            // Cảnh báo vắng mặt 30 ngày (Chức năng 8)
            $absenceWarnings = AttendanceDisciplineService::detectLongTermAbsence(30);

            // Phiếu báo hỏng cần xử lý
            $maintenanceTickets = Database::fetchAll("
                SELECT pb.*, sv.HoVaTen AS NguoiBao, tb.TenThietBi, p.SoPhong, tn.TenToaNha
                FROM PhieuBaoHong pb
                JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
                JOIN Phong p ON pb.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
                WHERE tn.MaCum = :cum AND pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý')
                ORDER BY pb.NgayBao DESC
            ", ['cum' => $maCum]);
        } catch (\Throwable $e) {
            // Dữ liệu mẫu dự phòng nếu bảng chưa sẵn sàng
        }

        $this->view('staff.dashboard', [
            'user' => $user,
            'maCum' => $maCum,
            'pendingApplications' => $pendingApplications,
            'consolidation' => $consolidation,
            'absenceWarnings' => $absenceWarnings,
            'maintenanceTickets' => $maintenanceTickets
        ]);
    }

    // =========================================================================
    // CÁC HÀM TIỆN ÍCH CHUYỂN TIẾP TƯƠNG THÍCH NGƯỢC (FORWARDING METHODS)
    // =========================================================================

    // Module Hạ tầng KTX (ZoneController)
    public function zones(): void { (new ZoneController())->zones(); }
    public function getStaffScope(?array $user = null): array { return (new ZoneController())->getStaffScope($user); }
    public function saveToa(): void { (new ZoneController())->saveToa(); }
    public function deleteToa(): void { (new ZoneController())->deleteToa(); }
    public function saveCum(): void { (new ZoneController())->saveCum(); }
    public function deleteCum(): void { (new ZoneController())->deleteCum(); }
    public function saveKhu(): void { (new ZoneController())->saveKhu(); }
    public function deleteKhu(): void { (new ZoneController())->deleteKhu(); }
    public function saveRoom(): void { (new ZoneController())->saveRoom(); }
    public function deleteRoom(): void { (new ZoneController())->deleteRoom(); }
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
