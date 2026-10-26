<?php

namespace App\Services;

use App\Core\Database;
use DateTime;
use Exception;

/**
 * CHỨC NĂNG 4: Nghiệp vụ Chuyển phòng & Tự động bù trừ chênh lệch phí (Room Transfer Reconciliation)
 */
class RoomTransferService {
    /**
     * Tính toán chênh lệch chi phí khi sinh viên chuyển từ phòng cũ sang phòng mới
     */
    public static function calculateTransferFee(string $maSinhVien, string $maGiuongMoi): array {
        // Lấy hợp đồng đang hiệu lực của sinh viên
        $hopDong = Database::fetch(
            "SELECT hd.*, g.MaPhong AS MaPhongCu, lp.DonGiaLuuTruChuan AS GiaCu, lp.DonGiaDichVuTangThem AS DVCu
             FROM HopDongNoiTru hd
             JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
             JOIN Phong p ON g.MaPhong = p.MaPhong
             JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
             WHERE hd.MaSinhVien = :sv AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
            ['sv' => $maSinhVien]
        );

        if (!$hopDong) {
            return ['success' => false, 'message' => 'Sinh viên chưa có hợp đồng nội trú đang hiệu lực'];
        }

        // Lấy thông tin phòng mới
        $giuongMoi = Database::fetch(
            "SELECT g.*, p.MaPhong AS MaPhongMoi, lp.DonGiaLuuTruChuan AS GiaMoi, lp.DonGiaDichVuTangThem AS DVMoi, lp.TenLoaiPhong
             FROM Giuong g
             JOIN Phong p ON g.MaPhong = p.MaPhong
             JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
             WHERE g.MaGiuong = :maGiuong",
            ['maGiuong' => $maGiuongMoi]
        );

        if (!$giuongMoi || $giuongMoi['TrangThaiGiuong'] !== 'Trống') {
            return ['success' => false, 'message' => 'Giường mới được chọn không khả dụng'];
        }

        // Tính số tháng còn lại của hợp đồng
        $now = new DateTime();
        $endDate = new DateTime($hopDong['NgayKetThuc']);
        $diff = $now->diff($endDate);
        $remainingMonths = ($diff->y * 12) + $diff->m + ($diff->d > 10 ? 1 : 0);
        if ($remainingMonths <= 0) {
            $remainingMonths = 1;
        }

        $giaThangCu = (float)$hopDong['GiaCu'] + (float)$hopDong['DVCu'];
        $giaThangMoi = (float)$giuongMoi['GiaMoi'] + (float)$giuongMoi['DVMoi'];
        $chenhLechMoiThang = $giaThangMoi - $giaThangCu;
        $tongChenhLech = $chenhLechMoiThang * $remainingMonths;

        return [
            'success' => true,
            'maHopDong' => $hopDong['MaHopDong'],
            'phongCu' => $hopDong['MaPhongCu'],
            'phongMoi' => $giuongMoi['MaPhongMoi'],
            'soThangConLai' => $remainingMonths,
            'donGiaCu' => $giaThangCu,
            'donGiaMoi' => $giaThangMoi,
            'chenhLechMoiThang' => $chenhLechMoiThang,
            'tongChenhLech' => $tongChenhLech,
            'loaiThanhToan' => $tongChenhLech > 0 ? 'Phải đóng bổ sung' : ($tongChenhLech < 0 ? 'Được hoàn trả' : 'Không đổi')
        ];
    }

    /**
     * Nộp đơn xin chuyển phòng trên cổng Một Cửa và xử lý sinh hóa đơn chênh lệch
     */
    public static function submitTransferRequest(string $maSinhVien, string $maGiuongMoi, string $lyDo): array {
        $calc = self::calculateTransferFee($maSinhVien, $maGiuongMoi);
        if (!$calc['success']) {
            return $calc;
        }

        Database::beginTransaction();
        try {
            $maDon = 'DON-CP-' . date('Ymd') . strtoupper(substr(uniqid(), -4));

            Database::execute(
                "INSERT INTO DonTuMotCua (MaDon, MaSinhVien, MaHopDong, LoaiDon, MaGiuongChuyenDen, LyDo, SoTienChenhLechHoan, TrangThaiDuyet)
                 VALUES (:maDon, :maSV, :maHD, 'Chuyển phòng nội trú', :maGiuong, :lyDo, :tien, 'Chờ duyệt')",
                [
                    'maDon' => $maDon,
                    'maSV' => $maSinhVien,
                    'maHD' => $calc['maHopDong'],
                    'maGiuong' => $maGiuongMoi,
                    'lyDo' => $lyDo,
                    'tien' => abs($calc['tongChenhLech'])
                ]
            );

            // Nếu phòng mới đắt hơn, tự động sinh hóa đơn bổ sung
            if ($calc['tongChenhLech'] > 0) {
                $maHD = 'HD-CP-' . $maDon;
                Database::execute(
                    "INSERT INTO HoaDon (MaHoaDon, MaSinhVien, MaPhong, MaHopDong, LoaiHoaDon, SoTienPhaiDong, NgayTao, HanThanhToan, TrangThai)
                     VALUES (:maHD, :maSV, :maPhong, :maHopDong, 'Bổ sung chênh lệch phí chuyển phòng', :soTien, NOW(), DATE_ADD(NOW(), INTERVAL 72 HOUR), 'Chưa thanh toán')",
                    [
                        'maHD' => $maHD,
                        'maSV' => $maSinhVien,
                        'maPhong' => $calc['phongMoi'],
                        'maHopDong' => $calc['maHopDong'],
                        'soTien' => $calc['tongChenhLech']
                    ]
                );
            }

            Database::commit();
            return [
                'success' => true,
                'maDon' => $maDon,
                'tongChenhLech' => $calc['tongChenhLech'],
                'message' => 'Nộp đơn chuyển phòng thành công. Hệ thống đã tạo bảng dự toán chênh lệch phí.'
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi nộp đơn: ' . $e->getMessage()];
        }
    }
}
