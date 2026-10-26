<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 7: Điều phối Bảo trì chống trùng lặp (Smart Maintenance Deduplication)
 */
class MaintenanceService {
    /**
     * Kiểm tra xem thiết bị trong phòng đã có phiếu báo hỏng đang mở hay chưa
     */
    public static function checkDuplicateTicket(string $maPhong, ?int $maThietBi): array {
        if (!$maThietBi) {
            return ['has_duplicate' => false];
        }

        $activeTicket = Database::fetch(
            "SELECT pb.*, sv.HoVaTen AS NguoiBao, tb.TenThietBi
             FROM PhieuBaoHong pb
             JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
             LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
             WHERE pb.MaPhong = :phong 
               AND pb.MaThietBi = :tb 
               AND pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý', 'Đang sửa chữa')
             ORDER BY pb.NgayBao DESC LIMIT 1",
            ['phong' => $maPhong, 'tb' => $maThietBi]
        );

        if ($activeTicket) {
            return [
                'has_duplicate' => true,
                'existing_ticket' => $activeTicket,
                'message' => "Thiết bị '{$activeTicket['TenThietBi']}' trong phòng đã được bạn {$activeTicket['NguoiBao']} báo hỏng vào lúc {$activeTicket['NgayBao']} (Trạng thái: {$activeTicket['TrangThaiSua']}). Bạn có thể theo dõi tiến độ phiếu này thay vì tạo phiếu mới."
            ];
        }

        return ['has_duplicate' => false];
    }

    /**
     * Tạo phiếu báo hỏng mới nếu không bị trùng
     */
    public static function createTicket(string $maSinhVien, string $maPhong, ?int $maThietBi, string $moTa, ?string $hinhAnh = null): array {
        $dupCheck = self::checkDuplicateTicket($maPhong, $maThietBi);
        if ($dupCheck['has_duplicate']) {
            return [
                'success' => false,
                'is_duplicate' => true,
                'duplicate_info' => $dupCheck,
                'message' => $dupCheck['message']
            ];
        }

        $maPhieu = 'BH' . date('Ymd') . strtoupper(substr(uniqid(), -4));

        try {
            Database::execute(
                "INSERT INTO PhieuBaoHong (MaPhieu, MaPhong, MaThietBi, MaSinhVienBao, MoTaHuHong, HinhAnh, TrangThaiSua, NgayBao)
                 VALUES (:maPhieu, :maPhong, :maTB, :maSV, :moTa, :hinhAnh, 'Chờ xử lý', NOW())",
                [
                    'maPhieu' => $maPhieu,
                    'maPhong' => $maPhong,
                    'maTB' => $maThietBi,
                    'maSV' => $maSinhVien,
                    'moTa' => $moTa,
                    'hinhAnh' => $hinhAnh
                ]
            );

            return [
                'success' => true,
                'maPhieu' => $maPhieu,
                'message' => 'Tạo phiếu báo hỏng thành công. Nhân viên kỹ thuật sẽ tiếp nhận và xử lý sớm.'
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Lỗi khi tạo phiếu: ' . $e->getMessage()];
        }
    }
}
