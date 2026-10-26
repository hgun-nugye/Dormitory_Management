<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 2: Duyệt hồ sơ 24h & Tiến trình ngầm tự động thu hồi chỗ sau 72h (Background Auto-Release)
 */
class AutoReleaseService {
    /**
     * Cán bộ duyệt hồ sơ: cộng 72 giờ vào thời điểm duyệt để tạo HanChotThanhToan, chuyển trạng thái sang 'Chờ thanh toán'
     */
    public static function approveApplication(string $maHoSo, int $nguoiDuyetTaiKhoanId, int $soGioGiuCho = 72): array {
        Database::beginTransaction();
        try {
            $hoso = Database::fetch("SELECT * FROM HoSoDangKy WHERE MaHoSo = :maHoSo FOR UPDATE", ['maHoSo' => $maHoSo]);
            if (!$hoso) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy hồ sơ'];
            }

            if ($hoso['TrangThaiHoSo'] !== 'Chờ duyệt') {
                Database::rollBack();
                return ['success' => false, 'message' => 'Hồ sơ đã được xử lý trước đó'];
            }

            $hanChot = date('Y-m-d H:i:s', strtotime("+{$soGioGiuCho} hours"));

            Database::execute(
                "UPDATE HoSoDangKy 
                 SET TrangThaiHoSo = 'Chờ thanh toán',
                     NguoiDuyet = :nguoiDuyet,
                     ThoiGianDuyet = NOW(),
                     HanChotThanhToan = :hanChot
                 WHERE MaHoSo = :maHoSo",
                [
                    'nguoiDuyet' => $nguoiDuyetTaiKhoanId,
                    'hanChot' => $hanChot,
                    'maHoSo' => $maHoSo
                ]
            );

            // Tự động sinh hóa đơn lệ phí chỗ ở nếu chưa có
            $maHoaDon = 'HD-' . $maHoSo;
            $existingHoaDon = Database::fetch("SELECT * FROM HoaDon WHERE MaHoaDon = :hd", ['hd' => $maHoaDon]);
            if (!$existingHoaDon) {
                // Lấy đơn giá phòng
                $phong = Database::fetch(
                    "SELECT lp.DonGiaLuuTruChuan, lp.DonGiaDichVuTangThem, g.MaPhong
                     FROM Giuong g
                     JOIN Phong p ON g.MaPhong = p.MaPhong
                     JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                     WHERE g.MaGiuong = :maGiuong",
                    ['maGiuong' => $hoso['MaGiuong']]
                );

                $tienPhong = ($phong['DonGiaLuuTruChuan'] ?? 650000) * 5; // Tính tạm kỳ 5 tháng
                Database::execute(
                    "INSERT INTO HoaDon (MaHoaDon, MaSinhVien, MaPhong, LoaiHoaDon, SoTienPhaiDong, NgayTao, HanThanhToan, TrangThai)
                     VALUES (:maHD, :maSV, :maPhong, 'Lệ phí chỗ ở học kỳ', :soTien, NOW(), :hanChot, 'Chưa thanh toán')",
                    [
                        'maHD' => $maHoaDon,
                        'maSV' => $hoso['MaSinhVien'],
                        'maPhong' => $phong['MaPhong'] ?? null,
                        'soTien' => $tienPhong,
                        'hanChot' => $hanChot
                    ]
                );
            }

            Database::commit();

            return [
                'success' => true,
                'hanChotThanhToan' => $hanChot,
                'message' => "Duyệt hồ sơ thành công. Thời hạn thanh toán được tạo: {$hanChot}"
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi duyệt hồ sơ: ' . $e->getMessage()];
        }
    }

    /**
     * Dịch vụ chạy ngầm (Background Worker / Cron Job):
     * Quét toàn bộ hồ sơ 'Chờ thanh toán' đã quá HanChotThanhToan:
     * - Đổi trạng thái sang 'Hết hạn - Đã hủy chỗ'
     * - Giải phóng giường về lại 'Trống'
     */
    public static function scanAndReleaseExpiredBeds(): array {
        $now = date('Y-m-d H:i:s');
        $expiredRecords = Database::fetchAll(
            "SELECT * FROM HoSoDangKy 
             WHERE TrangThaiHoSo = 'Chờ thanh toán' AND HanChotThanhToan < :now",
            ['now' => $now]
        );

        $releasedCount = 0;
        $releasedBeds = [];

        foreach ($expiredRecords as $rec) {
            Database::beginTransaction();
            try {
                // 1. Cập nhật hồ sơ hết hạn
                Database::execute(
                    "UPDATE HoSoDangKy 
                     SET TrangThaiHoSo = 'Hết hạn - Đã hủy chỗ',
                         GhiChuTuChoi = 'Tự động hủy do quá hạn 72 giờ thanh toán'
                     WHERE MaHoSo = :maHoSo",
                    ['maHoSo' => $rec['MaHoSo']]
                );

                // 2. Mở khóa giường về lại 'Trống'
                if (!empty($rec['MaGiuong'])) {
                    Database::execute(
                        "UPDATE Giuong SET TrangThaiGiuong = 'Trống' WHERE MaGiuong = :maGiuong",
                        ['maGiuong' => $rec['MaGiuong']]
                    );
                    $releasedBeds[] = $rec['MaGiuong'];
                }

                // 3. Hủy hóa đơn liên quan (nếu chưa thanh toán)
                Database::execute(
                    "UPDATE HoaDon SET TrangThai = 'Đã hủy' WHERE MaHoaDon = :maHD AND TrangThai = 'Chưa thanh toán'",
                    ['maHD' => 'HD-' . $rec['MaHoSo']]
                );

                Database::commit();
                $releasedCount++;
            } catch (Exception $e) {
                Database::rollBack();
            }
        }

        return [
            'total_scanned' => count($expiredRecords),
            'total_released' => $releasedCount,
            'released_beds' => $releasedBeds,
            'timestamp' => $now
        ];
    }
}
