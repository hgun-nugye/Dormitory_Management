<?php

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $mssv = $user['TenDangNhap'] ?? '';

        // Thông tin sinh viên
        $sinhVien = Database::fetch(
            "SELECT sv.*, t.TenTruong 
             FROM SinhVien sv 
             JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong 
             WHERE sv.MaSinhVien = :mssv",
            ['mssv' => $mssv]
        );

        // Hợp đồng hiện tại
        $hopDong = Database::fetch(
            "SELECT hd.*, g.KyHieuGiuong, g.MaPhong, lp.TenLoaiPhong, tn.TenToaNha, cn.TenCum
             FROM HopDongNoiTru hd
             JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
             JOIN Phong p ON g.MaPhong = p.MaPhong
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             JOIN CumNha cn ON tn.MaCum = cn.MaCum
             JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
             WHERE hd.MaSinhVien = :mssv AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
            ['mssv' => $mssv]
        );

        // Hóa đơn chưa thanh toán
        $hoaDons = Database::fetchAll(
            "SELECT * FROM HoaDon WHERE MaSinhVien = :mssv ORDER BY NgayTao DESC LIMIT 5",
            ['mssv' => $mssv]
        );

        // Phiếu báo hỏng
        $phieuBaoHong = Database::fetchAll(
            "SELECT pb.*, tb.TenThietBi 
             FROM PhieuBaoHong pb 
             LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi 
             WHERE pb.MaSinhVienBao = :mssv 
             ORDER BY pb.NgayBao DESC LIMIT 5",
            ['mssv' => $mssv]
        );

        $this->view('student.dashboard', [
            'user' => $user,
            'sinhVien' => $sinhVien,
            'hopDong' => $hopDong,
            'hoaDons' => $hoaDons,
            'phieuBaoHong' => $phieuBaoHong
        ]);
    }
}
