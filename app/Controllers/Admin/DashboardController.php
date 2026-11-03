<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

/**
 * Controller: Bảng điều khiển Quản trị viên (Admin Dashboard)
 * View: views/admin/dashboard.php
 * 
 * Lưu ý cấu trúc kiến trúc:
 * - Quản lý Cán bộ & Tài khoản: App\Controllers\Admin\UserController
 * - Quản lý Trường Đại học liên kết: App\Controllers\Admin\UniversityController
 */
class DashboardController extends Controller
{
    /**
     * Bảng điều khiển tổng quan dành cho Quản trị viên
     */
    public function index(): void
    {
        $user = Auth::user();

        // 1. Thống kê toàn hệ thống dành cho Quản trị viên
        $stats = [
            'total_students' => 0,
            'active_residents' => 0,
            'total_periods' => 0,
            'total_universities' => 0,
            'pending_applications' => 0,
            'pending_maintenance' => 0,
            'total_staff' => 0,
            'total_transactions' => 0
        ];

        $canBoList = [];
        $dotDangKyList = [];
        $loaiPhongList = [];
        $truongList = [];
        $recentTransactions = [];

        try {
            $stats['total_students'] = Database::fetch("SELECT COUNT(*) AS total FROM SinhVien")['total'] ?? 0;
            $stats['active_residents'] = Database::fetch("SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE TrangThaiHopDong = 'Đang hiệu lực'")['total'] ?? 0;
            $stats['total_periods'] = Database::fetch("SELECT COUNT(*) AS total FROM DotDangKy")['total'] ?? 0;
            $stats['total_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc")['total'] ?? 0;
            $stats['dhqg_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc WHERE ThuocDHQGHCM = 1")['total'] ?? 0;
            $stats['active_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc WHERE DangHopTac = 1")['total'] ?? 0;
            $stats['pending_applications'] = Database::fetch("SELECT COUNT(*) AS total FROM HoSoDangKy WHERE TrangThaiHoSo = 'Chờ duyệt'")['total'] ?? 0;
            $stats['pending_maintenance'] = Database::fetch("SELECT COUNT(*) AS total FROM PhieuBaoHong WHERE TrangThaiSua = 'Chờ xử lý'")['total'] ?? 0;
            $stats['total_staff'] = Database::fetch("SELECT COUNT(*) AS total FROM TaiKhoan WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')")['total'] ?? 0;
            $stats['active_staff'] = Database::fetch("SELECT COUNT(*) AS total FROM TaiKhoan WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG') AND DangHoatDong = 1")['total'] ?? 0;
            $stats['total_transactions'] = Database::fetch("SELECT COUNT(*) AS total FROM GiaoDichThanhToan")['total'] ?? 0;

            // Danh sách cán bộ & phân quyền (5 tài khoản mới nhất)
            $canBoList = Database::fetchAll("
                SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai, nv.Email,
                       cn.TenCum, k.TenKhu, toa.TenToaNha
                FROM TaiKhoan tk
                JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
                LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
                LEFT JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                LEFT JOIN ToaNha toa ON tk.MaCumQuanLy = toa.MaToaNha
                WHERE tk.MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')
                ORDER BY tk.MaTaiKhoan DESC
                LIMIT 5
            ");

            // Đợt đăng ký 72h
            $dotDangKyList = Database::fetchAll("SELECT * FROM DotDangKy ORDER BY MaDot DESC LIMIT 5");

            // Loại phòng & đơn giá
            $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY DonGiaLuuTruChuan ASC");

            // Trường đại học (5 trường kèm số lượng sinh viên nội trú)
            $truongList = Database::fetchAll("
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
                LIMIT 5
            ");

            // Doanh thu & giao dịch
            $recentTransactions = Database::fetchAll("
                SELECT gd.*, hd.LoaiHoaDon, hd.MaSinhVien, sv.HoVaTen
                FROM GiaoDichThanhToan gd
                JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
                JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 5
            ");
        } catch (\Throwable $e) {
            // Dùng dữ liệu dự phòng
        }

        // Dữ liệu mẫu dự phòng
        if (empty($canBoList)) {
            $canBoList = [
                ['TenDangNhap' => 'NV001', 'HoVaTen' => 'Trần Văn Minh', 'TenVaiTro' => 'Trưởng cụm nhà', 'MaCumQuanLy' => 'Cụm AG (Khu A)', 'SoDienThoai' => '0903123456', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'NV002', 'HoVaTen' => 'Nguyễn Thị Thu Hương', 'TenVaiTro' => 'Nhân viên hành chính', 'MaCumQuanLy' => 'Cụm AH (Khu A)', 'SoDienThoai' => '0914234567', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'TK001', 'HoVaTen' => 'Phạm Quốc Hùng', 'TenVaiTro' => 'Trưởng khu KTX', 'MaCumQuanLy' => 'Toàn bộ Khu A', 'SoDienThoai' => '0977333444', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'QLKTX01', 'HoVaTen' => 'Hoàng Minh Tuấn', 'TenVaiTro' => 'Quản lý chung KTX', 'MaCumQuanLy' => 'Toàn hệ thống', 'SoDienThoai' => '0909555666', 'DangHoatDong' => 1],
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
                ['MaTruong' => 'BK', 'TenTruong' => 'Trường Đại học Bách khoa - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.5, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'KHTN', 'TenTruong' => 'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'UIT', 'TenTruong' => 'Trường Đại học Công nghệ Thông tin - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'UEL', 'TenTruong' => 'Trường Đại học Kinh tế - Luật - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1]
            ];
        }

        $this->view('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'canBoList' => $canBoList,
            'dotDangKyList' => $dotDangKyList,
            'loaiPhongList' => $loaiPhongList,
            'truongList' => $truongList,
            'recentTransactions' => $recentTransactions
        ]);
    }

    // =========================================================================
    // CÁC HÀM TIỆN ÍCH CHUYỂN TIẾP CHO TƯƠNG THÍCH NGƯỢC (FORWARDING METHODS)
    // =========================================================================
    public function users(): void { (new UserController())->users(); }
    public function saveUser(): void { (new UserController())->saveUser(); }
    public function resetPassword(): void { (new UserController())->resetPassword(); }
    public function toggleUserStatus(): void { (new UserController())->toggleUserStatus(); }
    public function deleteUser(): void { (new UserController())->deleteUser(); }
    public function exportUsers(): void { (new UserController())->exportUsers(); }
    public function downloadUserTemplate(): void { (new UserController())->downloadUserTemplate(); }
    public function importUsers(): void { (new UserController())->importUsers(); }

    public function universities(): void { (new UniversityController())->universities(); }
    public function saveUniversity(): void { (new UniversityController())->saveUniversity(); }
    public function toggleUniversityStatus(): void { (new UniversityController())->toggleUniversityStatus(); }
    public function deleteUniversity(): void { (new UniversityController())->deleteUniversity(); }
    public function exportUniversities(): void { (new UniversityController())->exportUniversities(); }
    public function downloadUniversityTemplate(): void { (new UniversityController())->downloadUniversityTemplate(); }
    public function importUniversities(): void { (new UniversityController())->importUniversities(); }
}
