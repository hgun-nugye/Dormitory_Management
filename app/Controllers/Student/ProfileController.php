<?php

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Giao diện mẫu - Hiển thị thông tin của sinh viên
 * Các bảng dữ liệu: SINHVIEN + SV_NOITRU / SV_TAMTRU + TRUONG / BAN_QUAN_LY_CUM_NHA / PHONG
 */
class ProfileController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $mssv = $user['TenDangNhap'] ?? '2110001';

        // Lấy dữ liệu hoặc dùng mẫu sườn tiêu chuẩn
        $sinhVien = null;
        $hopDong = null;

        try {
            $sinhVien = Database::fetch(
                "SELECT sv.*, t.TenTruong 
                 FROM SinhVien sv 
                 JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong 
                 WHERE sv.MaSinhVien = :mssv",
                ['mssv' => $mssv]
            );

            if ($sinhVien) {
                $hopDong = Database::fetch(
                    "SELECT hd.*, g.KyHieuGiuong, g.MaPhong, lp.TenLoaiPhong, tn.TenToaNha, cn.TenCum, cn.SoDienThoaiCum
                     FROM HopDongNoiTru hd
                     JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                     JOIN Phong p ON g.MaPhong = p.MaPhong
                     JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                     JOIN CumNha cn ON tn.MaCum = cn.MaCum
                     JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                     WHERE hd.MaSinhVien = :mssv AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
                    ['mssv' => $mssv]
                );
            }
        } catch (\Throwable $e) {
            // Fallback sang khung sườn mẫu
        }

        // Khung sườn mẫu các trường cần hiển thị (theo CSDL & PDF)
        if (!$sinhVien) {
            $sinhVien = [
                'MaSinhVien' => '2110001',
                'HoVaTen' => 'Nguyễn Văn An',
                'NgaySinh' => '2003-08-15',
                'GioiTinh' => 'Nam',
                'SoCCCD' => '079203001234',
                'TenTruong' => 'Trường Đại học Bách Khoa (ĐHQG-HCM)',
                'KhoaTuyenSinh' => 'Khóa 2021',
                'SoDienThoai' => '0901 234 567',
                'Email' => 'an.nguyen@student.vnuhcm.edu.vn',
                'SDTPhuHuynh' => '0912 345 678',
                'SoTaiKhoanNganHang' => '19036789101112 (Techcombank)',
                'TrangThaiNoiTru' => 'Đang ở nội trú'
            ];
        }

        if (!$hopDong) {
            $hopDong = [
                'MaHopDong' => 'HD-2026-001',
                'MaPhong' => 'P.304',
                'KyHieuGiuong' => 'G02 (Tầng 1)',
                'TenToaNha' => 'Tòa A01',
                'TenCum' => 'Cụm nhà A1 - A4 (Khu A)',
                'SoDienThoaiCum' => '028.3724.2160',
                'NgayBatDau' => '2026-09-01',
                'NgayKetThuc' => '2027-06-30'
            ];
        }

        $this->view('student.profile', [
            'sinhVien' => $sinhVien,
            'hopDong' => $hopDong,
            'title' => 'Giao diện mẫu: Hiển thị thông tin sinh viên'
        ]);
    }
}
