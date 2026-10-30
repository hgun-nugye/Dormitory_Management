<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Services\RoomConsolidationService;
use App\Services\AttendanceDisciplineService;

class DashboardController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

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
            // Dữ liệu sườn mẫu dự phòng
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

    public function rooms(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.rooms', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function applications(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.applications', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function utilities(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.utilities', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function requests(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.requests', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function maintenance(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.maintenance', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function discipline(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.discipline', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function approveApplication(): void {
        $request = new \App\Core\Request();
        $maHoSo = $request->input('maHoSo');
        $userId = Auth::id() ?? 3;

        if ($maHoSo) {
            \App\Services\AutoReleaseService::approveApplication($maHoSo, $userId, 72);
        }

        $this->redirect('staff/dashboard');
    }
}
