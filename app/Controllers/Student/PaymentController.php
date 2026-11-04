<?php

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class PaymentController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $mssv = $user['profile']['MaSinhVien'] ?? $user['TenDangNhap'] ?? '';
        $hoaDons = [];
        $chiSoDienNuoc = [];
        $giaoDichs = [];

        try {
            $hoaDons = Database::fetchAll(
                "SELECT * FROM HoaDon WHERE MaSinhVien = :mssv ORDER BY NgayTao DESC LIMIT 20",
                ['mssv' => $mssv]
            );
            $chiSoDienNuoc = Database::fetchAll(
                "                SELECT DISTINCT cs.*, p.SoPhong, tn.TenToaNha
                 FROM ChiSoDienNuoc cs
                 JOIN Phong p ON cs.MaPhong = p.MaPhong
                 JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                 JOIN HopDongNoiTru hd ON hd.MaGiuong IN (SELECT MaGiuong FROM Giuong WHERE MaPhong = p.MaPhong)
                 WHERE hd.MaSinhVien = :mssv
                 ORDER BY cs.Nam DESC, cs.Thang DESC LIMIT 20",
                ['mssv' => $mssv]
            );
            $giaoDichs = Database::fetchAll(
                "SELECT gd.*, hd.LoaiHoaDon
                 FROM GiaoDichThanhToan gd
                 JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
                 WHERE hd.MaSinhVien = :mssv
                 ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 20",
                ['mssv' => $mssv]
            );
        } catch (\Throwable $e) {
            // Keep empty lists so the view reports no data instead of fake records.
        }

        $this->view('student.payments', [
            'hoaDons' => $hoaDons,
            'chiSoDienNuoc' => $chiSoDienNuoc,
            'giaoDichs' => $giaoDichs,
            'tongCongNo' => array_sum(array_map(
                fn($hd) => $hd['TrangThai'] === 'Đã thanh toán' ? 0 : (int) $hd['SoTienPhaiDong'],
                $hoaDons
            )),
            'title' => 'Thông tin thanh toán'
        ]);
    }
}
