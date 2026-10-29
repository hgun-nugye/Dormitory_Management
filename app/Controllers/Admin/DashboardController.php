<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Quản trị viên (Toàn quyền hệ thống)
 * Quản trị toàn diện:
 * 1. Phân quyền & Quản lý tài khoản cán bộ (Trưởng khu, Trưởng cụm, Nhân viên)
 * 2. Cơ sở vật chất KTX (Khu, Cụm, Tòa, Phòng, Giường)
 * 3. Cấu hình Đợt đăng ký & Giữ chỗ 72h
 * 4. Danh mục Loại phòng & Đơn giá chuẩn
 * 5. Danh mục Trường ĐH & Chỉ tiêu
 * 6. Giám sát Tài chính & Nhật ký Giao dịch
 */
class DashboardController extends Controller {
    public function index(): void {
        $user = Auth::user();

        // 1. Thống kê toàn hệ thống
        $stats = [
            'total_students' => 0,
            'active_residents' => 0,
            'total_rooms' => 0,
            'total_beds' => 0,
            'occupied_beds' => 0,
            'pending_applications' => 0,
            'pending_maintenance' => 0,
            'total_staff' => 0
        ];

        $canBoList = [];
        $cumNhaList = [];
        $dotDangKyList = [];
        $loaiPhongList = [];
        $truongList = [];
        $recentTransactions = [];

        try {
            $stats['total_students'] = Database::fetch("SELECT COUNT(*) AS total FROM SinhVien")['total'] ?? 0;
            $stats['active_residents'] = Database::fetch("SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE TrangThaiHopDong = 'Đang hiệu lực'")['total'] ?? 0;
            $stats['total_rooms'] = Database::fetch("SELECT COUNT(*) AS total FROM Phong")['total'] ?? 0;
            $stats['total_beds'] = Database::fetch("SELECT COUNT(*) AS total FROM Giuong")['total'] ?? 0;
            $stats['occupied_beds'] = Database::fetch("SELECT COUNT(*) AS total FROM Giuong WHERE TrangThaiGiuong = 'Đã có người ở'")['total'] ?? 0;
            $stats['pending_applications'] = Database::fetch("SELECT COUNT(*) AS total FROM HoSoDangKy WHERE TrangThaiHoSo = 'Chờ duyệt'")['total'] ?? 0;
            $stats['pending_maintenance'] = Database::fetch("SELECT COUNT(*) AS total FROM PhieuBaoHong WHERE TrangThaiSua = 'Chờ xử lý'")['total'] ?? 0;
            $stats['total_staff'] = Database::fetch("SELECT COUNT(*) AS total FROM NhanVien")['total'] ?? 0;

            // Danh sách cán bộ & phân quyền
            $canBoList = Database::fetchAll("
                SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong,
                       vt.TenVaiTro, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai, nv.Email
                FROM TaiKhoan tk
                JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
                WHERE tk.MaVaiTro != 'SINH_VIEN' AND tk.MaVaiTro != 'TRUONG_PHONG'
                ORDER BY tk.MaTaiKhoan ASC
            ");

            // Danh mục cụm nhà & hạ tầng
            $cumNhaList = Database::fetchAll("
                SELECT c.*, k.TenKhu,
                       COUNT(DISTINCT tn.MaToaNha) AS TongToa,
                       COUNT(DISTINCT p.MaPhong) AS TongPhong,
                       COUNT(DISTINCT g.MaGiuong) AS TongGiuong
                FROM CumNha c
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                LEFT JOIN ToaNha tn ON c.MaCum = tn.MaCum
                LEFT JOIN Phong p ON tn.MaToaNha = p.MaToaNha
                LEFT JOIN Giuong g ON p.MaPhong = g.MaPhong
                GROUP BY c.MaCum
                ORDER BY c.MaKhu, c.MaCum
            ");

            // Đợt đăng ký 72h
            $dotDangKyList = Database::fetchAll("SELECT * FROM DotDangKy ORDER BY MaDot DESC LIMIT 5");

            // Loại phòng & đơn giá
            $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY DonGiaLuuTruChuan ASC");

            // Trường đại học
            $truongList = Database::fetchAll("SELECT * FROM TruongDaiHoc ORDER BY ThuocDHQGHCM DESC, MaTruong ASC");

            // Doanh thu & giao dịch
            $recentTransactions = Database::fetchAll("
                SELECT gd.*, hd.LoaiHoaDon, hd.MaSinhVien, sv.HoVaTen
                FROM GiaoDichThanhToan gd
                JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
                JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 5
            ");
        } catch (\Throwable $e) {
            // Dùng dữ liệu sườn mẫu
        }

        // Dữ liệu sườn mẫu dự phòng nếu CSDL chưa có dữ liệu
        if (empty($canBoList)) {
            $canBoList = [
                ['TenDangNhap' => 'NV001', 'HoVaTen' => 'Trần Văn Minh', 'TenVaiTro' => 'Trưởng cụm nhà', 'MaCumQuanLy' => 'Cụm AG (Khu A)', 'SoDienThoai' => '0903123456', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'NV002', 'HoVaTen' => 'Nguyễn Thị Thu Hương', 'TenVaiTro' => 'Nhân viên hành chính', 'MaCumQuanLy' => 'Cụm AH (Khu A)', 'SoDienThoai' => '0914234567', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'TK001', 'HoVaTen' => 'Phạm Quốc Hùng', 'TenVaiTro' => 'Trưởng khu KTX', 'MaCumQuanLy' => 'Toàn bộ Khu A', 'SoDienThoai' => '0977333444', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'QLKTX01', 'HoVaTen' => 'Hoàng Minh Tuấn', 'TenVaiTro' => 'Quản lý chung KTX', 'MaCumQuanLy' => 'Toàn hệ thống', 'SoDienThoai' => '0909555666', 'DangHoatDong' => 1],
            ];
        }

        if (empty($cumNhaList)) {
            $cumNhaList = [
                ['MaCum' => 'AG', 'TenCum' => 'Cụm nhà A.G', 'TenKhu' => 'Khu A', 'TongToa' => 4, 'TongPhong' => 120, 'TongGiuong' => 480],
                ['MaCum' => 'AH', 'TenCum' => 'Cụm nhà A.H', 'TenKhu' => 'Khu A', 'TongToa' => 3, 'TongPhong' => 90, 'TongGiuong' => 360],
                ['MaCum' => 'BC', 'TenCum' => 'Cụm nhà B.C', 'TenKhu' => 'Khu B', 'TongToa' => 6, 'TongPhong' => 180, 'TongGiuong' => 720]
            ];
        }

        if (empty($dotDangKyList)) {
            $dotDangKyList = [
                ['TenDot' => 'Đăng ký nội trú năm học 2026-2027', 'NamHoc' => '2026-2027', 'ThoiGianMoCong' => '2026-06-01 08:00', 'ThoiGianDongCong' => '2026-10-31 23:59', 'SoGioGiuCho' => 72]
            ];
        }

        if (empty($loaiPhongList)) {
            $loaiPhongList = [
                ['MaLoaiPhong' => 'LP4', 'TenLoaiPhong' => 'Phòng tiêu chuẩn 4 giường', 'SoGiuongToiDa' => 4, 'DonGiaLuuTruChuan' => 650000, 'DonGiaDichVuTangThem' => 0],
                ['MaLoaiPhong' => 'LP4ML', 'TenLoaiPhong' => 'Phòng 4 giường có máy lạnh', 'SoGiuongToiDa' => 4, 'DonGiaLuuTruChuan' => 650000, 'DonGiaDichVuTangThem' => 300000],
                ['MaLoaiPhong' => 'LP8', 'TenLoaiPhong' => 'Phòng tiêu chuẩn 8 giường', 'SoGiuongToiDa' => 8, 'DonGiaLuuTruChuan' => 220000, 'DonGiaDichVuTangThem' => 0]
            ];
        }

        if (empty($truongList)) {
            $truongList = [
                ['MaTruong' => 'BK', 'TenTruong' => 'Trường Đại học Bách khoa - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.5],
                ['MaTruong' => 'KHTN', 'TenTruong' => 'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0],
                ['MaTruong' => 'UIT', 'TenTruong' => 'Trường Đại học Công nghệ Thông tin - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0],
                ['MaTruong' => 'UEL', 'TenTruong' => 'Trường Đại học Kinh tế - Luật - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0]
            ];
        }

        $this->view('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'canBoList' => $canBoList,
            'cumNhaList' => $cumNhaList,
            'dotDangKyList' => $dotDangKyList,
            'loaiPhongList' => $loaiPhongList,
            'truongList' => $truongList,
            'recentTransactions' => $recentTransactions
        ]);
    }

    public function users(): void {
        $this->view('admin.users', ['user' => Auth::user()]);
    }

    public function rooms(): void {
        $this->view('admin.rooms', ['user' => Auth::user()]);
    }

    public function periods(): void {
        $this->view('admin.periods', ['user' => Auth::user()]);
    }

    public function roomTypes(): void {
        $this->view('admin.room_types', ['user' => Auth::user()]);
    }

    public function universities(): void {
        $this->view('admin.universities', ['user' => Auth::user()]);
    }
}
