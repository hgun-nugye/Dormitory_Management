<?php

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

/**
 * Controller: Giao diện mẫu - Xem thông tin thanh toán
 * Các bảng dữ liệu: DANGKY_HOPDONG + HOADON + THANH_TOAN + CHISODIENNUOC
 */
class PaymentController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $mssv = $user['TenDangNhap'] ?? '2110001';

        $hoaDons = [];
        $chiSoDienNuoc = [];
        $giaoDichs = [];

        try {
            $hoaDons = Database::fetchAll(
                "SELECT * FROM HoaDon WHERE MaSinhVien = :mssv ORDER BY NgayTao DESC LIMIT 3",
                ['mssv' => $mssv]
            );

            $chiSoDienNuoc = Database::fetchAll(
                "SELECT cs.*, p.SoPhong, tn.TenToaNha 
                 FROM ChiSoDienNuoc cs
                 JOIN Phong p ON cs.MaPhong = p.MaPhong
                 JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                 JOIN Giuong g ON g.MaPhong = p.MaPhong
                 JOIN HopDongNoiTru hd ON hd.MaGiuong = g.MaGiuong
                 WHERE hd.MaSinhVien = :mssv
                 ORDER BY cs.Nam DESC, cs.Thang DESC LIMIT 2",
                ['mssv' => $mssv]
            );

            $giaoDichs = Database::fetchAll(
                "SELECT gd.*, hd.LoaiHoaDon 
                 FROM GiaoDichThanhToan gd
                 JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
                 WHERE hd.MaSinhVien = :mssv
                 ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 3",
                ['mssv' => $mssv]
            );
        } catch (\Throwable $e) {
            // Fallback sang khung sườn mẫu
        }

        // Khung sườn mẫu các trường cần hiển thị (theo CSDL & PDF)
        if (empty($hoaDons)) {
            $hoaDons = [
                [
                    'MaHoaDon' => 'HD-2026-10-01',
                    'LoaiHoaDon' => 'Tiền điện & nước sinh hoạt',
                    'KyThanhToan' => 'Tháng 10/2026',
                    'SoTienPhaiDong' => 228750,
                    'HanThanhToan' => '2026-11-05',
                    'TrangThai' => 'Chưa thanh toán'
                ],
                [
                    'MaHoaDon' => 'HD-2026-08-01',
                    'LoaiHoaDon' => 'Lệ phí phòng lưu trú KTX',
                    'KyThanhToan' => 'Học kỳ 1 (2026-2027)',
                    'SoTienPhaiDong' => 1500000,
                    'HanThanhToan' => '2026-08-25',
                    'TrangThai' => 'Đã thanh toán'
                ]
            ];
        }

        if (empty($chiSoDienNuoc)) {
            $chiSoDienNuoc = [
                [
                    'Thang' => 10,
                    'Nam' => 2026,
                    'SoPhong' => '304',
                    'TenToaNha' => 'Tòa A01',
                    'DienCu' => 1250,
                    'DienMoi' => 1520,
                    'DienTieuThu' => 270,
                    'TienDien' => 675000,
                    'NuocCu' => 45,
                    'NuocMoi' => 65,
                    'NuocTieuThu' => 20,
                    'TienNuoc' => 240000,
                    'SoSVThucOTaiThang' => 4,
                    'TienMoiSV' => 228750
                ]
            ];
        }

        if (empty($giaoDichs)) {
            $giaoDichs = [
                [
                    'MaGiaoDich' => 'GD-2026-8801',
                    'MaHoaDon' => 'HD-2026-09-02',
                    'KenhThanhToan' => 'VietQR / Mã QR KTX',
                    'ThoiGianGiaoDich' => '2026-10-02 14:30',
                    'SoTien' => 201000,
                    'KetQuaGiaoDich' => 'Thành công (Demo)'
                ]
            ];
        }

        $this->view('student.payments', [
            'hoaDons' => $hoaDons,
            'chiSoDienNuoc' => $chiSoDienNuoc,
            'giaoDichs' => $giaoDichs,
            'tongCongNo' => 228750,
            'title' => 'Giao diện mẫu: Xem thông tin thanh toán'
        ]);
    }
}
