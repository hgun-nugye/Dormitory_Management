<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 7: Điều phối Bảo trì chống trùng lặp & Tự động hoàn nguyên thiết bị (Smart Maintenance Dispatching)
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

        Database::beginTransaction();
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

            // Cập nhật tình trạng thiết bị sang 'Đang báo hỏng'
            if ($maThietBi) {
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = 'Đang báo hỏng' WHERE MaThietBi = :tb",
                    ['tb' => $maThietBi]
                );
            }

            Database::commit();

            return [
                'success' => true,
                'maPhieu' => $maPhieu,
                'message' => 'Tạo phiếu báo hỏng thành công. Nhân viên kỹ thuật sẽ tiếp nhận và xử lý sớm.'
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi tạo phiếu: ' . $e->getMessage()];
        }
    }

    /**
     * Phân công cán bộ kỹ thuật phụ trách phiếu báo hỏng (Ràng buộc theo Khu A/B)
     */
    public static function assignTechnician(string $maPhieu, int $maTaiKhoanNV, string $trangThai = 'Đang sửa chữa'): array {
        // Lấy thông tin phiếu và Khu vực tương ứng
        $ticket = Database::fetch(
            "SELECT pb.*, p.MaPhong, tn.MaToaNha, tn.MaCum, cn.MaKhu, cn.TenCum
             FROM PhieuBaoHong pb
             JOIN Phong p ON pb.MaPhong = p.MaPhong
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             JOIN CumNha cn ON tn.MaCum = cn.MaCum
             WHERE pb.MaPhieu = :id",
            ['id' => $maPhieu]
        );
        if (!$ticket) {
            return ['success' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        $allowedStatuses = ['Chờ xử lý', 'Đang sửa chữa', 'Đã hoàn thành', 'Đã hủy'];
        if (!in_array($trangThai, $allowedStatuses)) {
            $trangThai = 'Đang sửa chữa';
        }

        // Lấy tên cán bộ được phân công và Khu quản lý của cán bộ
        $tech = Database::fetch(
            "SELECT tk.MaTaiKhoan, tk.MaCumQuanLy AS MaKhu, nv.HoVaTen, nv.ChucVu
             FROM TaiKhoan tk
             LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
             WHERE tk.MaTaiKhoan = :tk",
            ['tk' => $maTaiKhoanNV]
        );

        if (!$tech) {
            return ['success' => false, 'message' => "Không tìm thấy tài khoản nhân viên kỹ thuật (ID: {$maTaiKhoanNV})"];
        }

        // RÀNG BUỘC THEO KHU: Nhân viên kỹ thuật của Khu nào chỉ phụ trách sửa chữa phiếu thuộc Khu đó
        $ticketKhu = $ticket['MaKhu'] ?? '';
        $techKhu = $tech['MaKhu'] ?? '';
        if (!empty($ticketKhu) && !empty($techKhu) && in_array($techKhu, ['A', 'B']) && $techKhu !== $ticketKhu) {
            return [
                'success' => false,
                'message' => "Không thể phân công: Kỹ thuật viên {$tech['HoVaTen']} thuộc Khu {$techKhu}, không thể phụ trách phòng {$ticket['MaPhong']} thuộc Khu {$ticketKhu}!"
            ];
        }

        Database::beginTransaction();
        try {
            // 1. Cập nhật phiếu báo hỏng
            Database::execute(
                "UPDATE PhieuBaoHong
                 SET NhanVienKyThuat = :nv, TrangThaiSua = :st
                 WHERE MaPhieu = :id",
                [
                    'nv' => $maTaiKhoanNV,
                    'st' => $trangThai,
                    'id' => $maPhieu
                ]
            );

            // 2. Cập nhật thiết bị tương ứng sang 'Đang bảo trì' nếu đang sửa
            if (!empty($ticket['MaThietBi'])) {
                $tbStatus = ($trangThai === 'Đang sửa chữa') ? 'Đang bảo trì' : (($trangThai === 'Đã hoàn thành') ? 'Tốt' : 'Đang báo hỏng');
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = :st WHERE MaThietBi = :tb",
                    ['st' => $tbStatus, 'tb' => $ticket['MaThietBi']]
                );
            }

            Database::commit();

            $tenTho = $tech['HoVaTen'] ?? ('Kỹ thuật viên #' . $maTaiKhoanNV);
            return [
                'success' => true,
                'message' => "Đã phân công cán bộ kỹ thuật {$tenTho} cho phiếu {$maPhieu} (Trạng thái: {$trangThai})."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi phân công kỹ thuật: ' . $e->getMessage()];
        }
    }

    /**
     * Xác nhận hoàn thành sửa chữa & Tự động hoàn nguyên thiết bị về 'Tốt'
     */
    public static function completeRepair(string $maPhieu): array {
        $ticket = Database::fetch(
            "SELECT pb.*, tb.TenThietBi
             FROM PhieuBaoHong pb
             LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
             WHERE pb.MaPhieu = :id",
            ['id' => $maPhieu]
        );

        if (!$ticket) {
            return ['success' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        if ($ticket['TrangThaiSua'] === 'Đã hoàn thành') {
            return ['success' => false, 'message' => "Phiếu {$maPhieu} đã được hoàn thành trước đó."];
        }

        Database::beginTransaction();
        try {
            // 1. Chuyển trạng thái phiếu sang 'Đã hoàn thành'
            Database::execute(
                "UPDATE PhieuBaoHong SET TrangThaiSua = 'Đã hoàn thành' WHERE MaPhieu = :id",
                ['id' => $maPhieu]
            );

            // 2. Tự động hoàn nguyên tình trạng thiết bị về 'Tốt'
            if (!empty($ticket['MaThietBi'])) {
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = 'Tốt' WHERE MaThietBi = :tb",
                    ['tb' => $ticket['MaThietBi']]
                );
            }

            Database::commit();

            $tenTB = $ticket['TenThietBi'] ?? 'Thiết bị';
            return [
                'success' => true,
                'message' => "Đã hoàn thành sửa chữa phiếu {$maPhieu}! Tình trạng thiết bị ({$tenTB}) đã được tự động hoàn nguyên về 'Tốt'."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi hoàn thành phiếu: ' . $e->getMessage()];
        }
    }

    /**
     * Hủy phiếu báo hỏng (nếu báo sai hoặc không cần sửa chữa)
     */
    public static function cancelTicket(string $maPhieu, ?string $lyDo = null): array {
        $ticket = Database::fetch("SELECT * FROM PhieuBaoHong WHERE MaPhieu = :id", ['id' => $maPhieu]);
        if (!$ticket) {
            return ['success' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE PhieuBaoHong SET TrangThaiSua = 'Đã hủy' WHERE MaPhieu = :id",
                ['id' => $maPhieu]
            );

            // Hoàn trả thiết bị về 'Tốt' nếu trước đó bị báo hỏng
            if (!empty($ticket['MaThietBi'])) {
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = 'Tốt' WHERE MaThietBi = :tb",
                    ['tb' => $ticket['MaThietBi']]
                );
            }

            Database::commit();

            return [
                'success' => true,
                'message' => "Đã hủy phiếu báo hỏng {$maPhieu}. Thiết bị đã được trả về trạng thái bình thường."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi hủy phiếu: ' . $e->getMessage()];
        }
    }
}
