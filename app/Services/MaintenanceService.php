<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 5: Điều phối Bảo trì & Sửa chữa Thiết bị (Phân cấp quyền hạn & Kỹ thuật theo Khu)
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
            "SELECT pb.*, COALESCE(sv.HoVaTen, nv.HoVaTen, 'Cán bộ') AS NguoiBao, tb.TenThietBi
             FROM PhieuBaoHong pb
             LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
             LEFT JOIN NhanVien nv ON pb.MaNhanVienBao = nv.MaNhanVien
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
                'message' => "Thiết bị '{$activeTicket['TenThietBi']}' trong phòng đã được báo hỏng vào lúc {$activeTicket['NgayBao']} (Trạng thái: {$activeTicket['TrangThaiSua']}). Bạn có thể theo dõi tiến độ phiếu này thay vì tạo phiếu mới."
            ];
        }

        return ['has_duplicate' => false];
    }

    /**
     * Tạo phiếu báo hỏng mới từ Sinh viên
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

        // Lấy mã tòa từ phòng
        $phong = Database::fetch("SELECT MaToaNha FROM Phong WHERE MaPhong = :p", ['p' => $maPhong]);
        $maToa = $phong['MaToaNha'] ?? null;

        $maPhieu = 'BH' . date('Ymd') . strtoupper(substr(uniqid(), -4));

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO PhieuBaoHong (MaPhieu, MaPhong, MaToaNha, MaThietBi, MaSinhVienBao, LoaiSuCo, MoTaHuHong, HinhAnh, TrangThaiSua, NgayBao)
                 VALUES (:maPhieu, :maPhong, :maToa, :maTB, :maSV, 'PHONG', :moTa, :hinhAnh, 'Chờ xử lý', NOW())",
                [
                    'maPhieu' => $maPhieu,
                    'maPhong' => $maPhong,
                    'maToa' => $maToa,
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
                'message' => 'Tạo phiếu báo hỏng thành công. Ban Quản lý sẽ tiếp nhận và cử kỹ thuật xử lý sớm.'
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi tạo phiếu: ' . $e->getMessage()];
        }
    }

    /**
     * Cán bộ lập phiếu sự cố hạ tầng công cộng / thiết bị chung (hỏng tường, hư cây, đèn hành lang...)
     */
    public static function createStaffInfrastructureTicket(
        string $maNhanVien,
        string $maToaNha,
        ?string $maPhong,
        string $moTa,
        ?string $hinhAnh = null
    ): array {
        if (empty($maToaNha) || empty($moTa)) {
            return ['success' => false, 'message' => 'Vui lòng chọn tòa nhà và nhập mô tả chi tiết sự cố hạ tầng.'];
        }

        // Kiểm tra xem maPhong có phải là một mã phòng có thật trong CSDL hay không
        $validPhong = null;
        $finalMoTa = $moTa;
        if (!empty($maPhong)) {
            $phongCheck = Database::fetch("SELECT MaPhong FROM Phong WHERE MaPhong = :p", ['p' => $maPhong]);
            if ($phongCheck) {
                $validPhong = $maPhong;
            } else {
                // Nếu là mô tả vị trí chung (ví dụ: Hành lang, Sảnh chính...), bổ sung vào nội dung mô tả
                $finalMoTa = "[Vị trí: {$maPhong}] " . $moTa;
            }
        }

        $maPhieu = 'HT' . date('Ymd') . strtoupper(substr(uniqid(), -4));

        Database::beginTransaction();
        try {
            Database::execute(
                "INSERT INTO PhieuBaoHong (MaPhieu, MaPhong, MaToaNha, MaSinhVienBao, MaNhanVienBao, LoaiSuCo, MoTaHuHong, HinhAnh, TrangThaiSua, NgayBao)
                 VALUES (:maPhieu, :maPhong, :maToa, NULL, :maNV, 'HA_TANG', :moTa, :hinhAnh, 'Chờ xử lý', NOW())",
                [
                    'maPhieu' => $maPhieu,
                    'maPhong' => $validPhong,
                    'maToa' => $maToaNha,
                    'maNV' => $maNhanVien,
                    'moTa' => $finalMoTa,
                    'hinhAnh' => $hinhAnh
                ]
            );

            Database::commit();

            return [
                'success' => true,
                'maPhieu' => $maPhieu,
                'message' => "Lập phiếu sự cố hạ tầng {$maPhieu} thành công. Phiếu đã được chuyển sang trạng thái chờ cấp trên phê duyệt & điều phối."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi lập phiếu sự cố: ' . $e->getMessage()];
        }
    }

    /**
     * Duyệt phiếu và phân công Kỹ thuật viên (Bắt buộc KTV cùng Khu A/B)
     */
    public static function approveAndAssignTechnician(
        string $maPhieu,
        int $maTaiKhoanTho,
        int $maTaiKhoanNguoiDuyet,
        string $trangThai = 'Đang sửa chữa'
    ): array {
        // Lấy thông tin phiếu và Khu vực tương ứng
        $ticket = Database::fetch(
            "SELECT pb.*, cn.MaKhu, COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha
             FROM PhieuBaoHong pb
             LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
             LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
             LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
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
            ['tk' => $maTaiKhoanTho]
        );

        if (!$tech) {
            return ['success' => false, 'message' => "Không tìm thấy tài khoản nhân viên kỹ thuật (ID: {$maTaiKhoanTho})"];
        }

        // RÀNG BUỘC THEO KHU: Nhân viên kỹ thuật của Khu nào chỉ phụ trách sửa chữa phiếu thuộc Khu đó
        $ticketKhu = $ticket['MaKhu'] ?? '';
        $techKhu = $tech['MaKhu'] ?? '';
        if (!empty($ticketKhu) && !empty($techKhu) && in_array($techKhu, ['A', 'B']) && $techKhu !== $ticketKhu) {
            return [
                'success' => false,
                'message' => "Không thể phân công: Kỹ thuật viên {$tech['HoVaTen']} thuộc Khu {$techKhu}, không thể phụ trách sự cố thuộc Khu {$ticketKhu}!"
            ];
        }

        Database::beginTransaction();
        try {
            Database::execute(
                "UPDATE PhieuBaoHong
                 SET NhanVienKyThuat = :nv, NguoiDuyet = :duyet, TrangThaiSua = :st
                 WHERE MaPhieu = :id",
                [
                    'nv' => $maTaiKhoanTho,
                    'duyet' => $maTaiKhoanNguoiDuyet,
                    'st' => $trangThai,
                    'id' => $maPhieu
                ]
            );

            // Cập nhật thiết bị tương ứng sang 'Đang bảo trì' nếu đang sửa
            if (!empty($ticket['MaThietBi'])) {
                $tbStatus = ($trangThai === 'Đang sửa chữa') ? 'Đang bảo trì' : (($trangThai === 'Đã hoàn thành') ? 'Tốt' : 'Đang báo hỏng');
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = :st WHERE MaThietBi = :tb",
                    ['st' => $tbStatus, 'tb' => $ticket['MaThietBi']]
                );
            }

            Database::commit();

            $tenTho = $tech['HoVaTen'] ?? ('Kỹ thuật viên #' . $maTaiKhoanTho);
            return [
                'success' => true,
                'message' => "Đã duyệt và phân công Kỹ thuật viên {$tenTho} cho phiếu {$maPhieu} (Trạng thái: {$trangThai})."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi duyệt & phân công: ' . $e->getMessage()];
        }
    }

    /**
     * Kỹ thuật viên cập nhật tiến độ sửa chữa
     */
    public static function updateRepairProgress(
        string $maPhieu,
        string $ghiChu,
        ?string $trangThai = null
    ): array {
        $ticket = Database::fetch("SELECT * FROM PhieuBaoHong WHERE MaPhieu = :id", ['id' => $maPhieu]);
        if (!$ticket) {
            return ['success' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        $params = [
            'id' => $maPhieu,
            'note' => $ghiChu
        ];

        $setSql = "GhiChuTienDo = :note";
        if (!empty($trangThai) && in_array($trangThai, ['Chờ xử lý', 'Đang sửa chữa', 'Đã hoàn thành'])) {
            $setSql .= ", TrangThaiSua = :st";
            $params['st'] = $trangThai;
        }

        try {
            Database::execute("UPDATE PhieuBaoHong SET {$setSql} WHERE MaPhieu = :id", $params);
            return ['success' => true, 'message' => "Cập nhật tiến độ sửa chữa phiếu {$maPhieu} thành công."];
        } catch (Exception $e) {
            return ['success' => false, 'message' => "Lỗi cập nhật tiến độ: " . $e->getMessage()];
        }
    }

    /**
     * Xác nhận hoàn thành sửa chữa & Tự động hoàn nguyên thiết bị về 'Tốt'
     */
    public static function completeRepair(string $maPhieu, ?string $ghiChu = null): array {
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
            return ['success' => false, 'message' => "Phiếu {$maPhieu} đã được nghiệm thu hoàn thành trước đó."];
        }

        Database::beginTransaction();
        try {
            $params = ['id' => $maPhieu];
            $noteSql = "";
            if (!empty($ghiChu)) {
                $noteSql = ", GhiChuTienDo = :note";
                $params['note'] = $ghiChu;
            }

            // 1. Chuyển trạng thái phiếu sang 'Đã hoàn thành'
            Database::execute(
                "UPDATE PhieuBaoHong SET TrangThaiSua = 'Đã hoàn thành' {$noteSql} WHERE MaPhieu = :id",
                $params
            );

            // 2. Tự động hoàn nguyên tình trạng thiết bị về 'Tốt'
            if (!empty($ticket['MaThietBi'])) {
                Database::execute(
                    "UPDATE ThietBiPhong SET TinhTrangThietBi = 'Tốt' WHERE MaThietBi = :tb",
                    ['tb' => $ticket['MaThietBi']]
                );
            }

            Database::commit();

            $tenTB = $ticket['TenThietBi'] ?? 'Cơ sở vật chất';
            return [
                'success' => true,
                'message' => "Đã nghiệm thu hoàn thành sửa chữa phiếu {$maPhieu}! Tình trạng thiết bị ({$tenTB}) đã được tự động hoàn nguyên về 'Tốt'."
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
            $note = !empty($lyDo) ? ("Lý do hủy: " . $lyDo) : "Đã hủy phiếu";
            Database::execute(
                "UPDATE PhieuBaoHong SET TrangThaiSua = 'Đã hủy', GhiChuTienDo = :note WHERE MaPhieu = :id",
                ['id' => $maPhieu, 'note' => $note]
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
                'message' => "Đã hủy phiếu báo hỏng {$maPhieu}. Thiết bị đã được hoàn trả về trạng thái bình thường."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi hủy phiếu: ' . $e->getMessage()];
        }
    }
}
