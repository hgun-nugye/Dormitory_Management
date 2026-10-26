<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {
    public function index(): void {
        $user = Auth::user();

        // Thống kê toàn hệ thống
        $stats = [
            'total_students' => Database::fetch("SELECT COUNT(*) AS total FROM SinhVien")['total'] ?? 0,
            'active_residents' => Database::fetch("SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE TrangThaiHopDong = 'Đang hiệu lực'")['total'] ?? 0,
            'total_rooms' => Database::fetch("SELECT COUNT(*) AS total FROM Phong")['total'] ?? 0,
            'total_beds' => Database::fetch("SELECT COUNT(*) AS total FROM Giuong")['total'] ?? 0,
            'occupied_beds' => Database::fetch("SELECT COUNT(*) AS total FROM Giuong WHERE TrangThaiGiuong = 'Đã có người ở'")['total'] ?? 0,
            'pending_applications' => Database::fetch("SELECT COUNT(*) AS total FROM HoSoDangKy WHERE TrangThaiHoSo = 'Chờ duyệt'")['total'] ?? 0,
            'pending_maintenance' => Database::fetch("SELECT COUNT(*) AS total FROM PhieuBaoHong WHERE TrangThaiSua = 'Chờ xử lý'")['total'] ?? 0,
        ];

        // Doanh thu gần đây
        $recentTransactions = Database::fetchAll("
            SELECT gd.*, hd.LoaiHoaDon, hd.MaSinhVien, sv.HoVaTen
            FROM GiaoDichThanhToan gd
            JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
            JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
            ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 10
        ");

        $this->view('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'recentTransactions' => $recentTransactions
        ]);
    }
}
