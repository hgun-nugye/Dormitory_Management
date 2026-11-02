<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 1: Kiểm soát điều kiện lưu trú & Chống xung đột đặt giường (Concurrency & Eligibility Engine)
 */
class BedLockService {
    /**
     * Kiểm tra tính hợp lệ của sinh viên khi đăng ký phòng:
     * 1. Giới tính của sinh viên phù hợp với quy định tòa nhà.
     * 2. Thời hạn đào tạo chuẩn: KhoaTuyenSinh + SoNamDaoTaoChuan >= Năm hiện tại.
     */
    public static function checkEligibility(string $maSinhVien, string $maGiuong): array {
        $sv = Database::fetch(
            "SELECT sv.*, t.SoNamDaoTaoChuan, t.TenTruong 
             FROM SinhVien sv
             JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
             WHERE sv.MaSinhVien = :svId",
            ['svId' => $maSinhVien]
        );

        if (!$sv) {
            return ['eligible' => false, 'message' => 'Không tìm thấy hồ sơ sinh viên'];
        }

        $giuong = Database::fetch(
            "SELECT g.*, p.MaToaNha, tn.GioiTinhQuyDinh, p.TrangThaiPhong
             FROM Giuong g
             JOIN Phong p ON g.MaPhong = p.MaPhong
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             WHERE g.MaGiuong = :maGiuong",
            ['maGiuong' => $maGiuong]
        );

        if (!$giuong) {
            return ['eligible' => false, 'message' => 'Không tìm thấy thông tin giường'];
        }

        // 1. Kiểm tra giới tính
        if ($giuong['GioiTinhQuyDinh'] !== 'Nam/Nữ' && $giuong['GioiTinhQuyDinh'] !== $sv['GioiTinh']) {
            return [
                'eligible' => false,
                'message' => "Tòa nhà {$giuong['MaToaNha']} quy định dành cho sinh viên {$giuong['GioiTinhQuyDinh']}. Bạn không thể đăng ký."
            ];
        }

        // 2. Kiểm tra thời gian đào tạo chuẩn
        $currentYear = (int)date('Y');
        $maxGraduationYear = (int)$sv['KhoaTuyenSinh'] + (float)$sv['SoNamDaoTaoChuan'];
        $requireStudyProof = false;

        if ($currentYear > $maxGraduationYear) {
            $requireStudyProof = true;
            if (empty($sv['AnhMinhChungHocTap'])) {
                return [
                    'eligible' => false,
                    'require_proof' => true,
                    'message' => "Bạn đã học quá thời gian đào tạo chuẩn ({$sv['SoNamDaoTaoChuan']} năm). Bắt buộc tải lên 'Giấy xác nhận còn đang học tại trường' để được xét duyệt."
                ];
            }
        }

        return [
            'eligible' => true,
            'require_proof' => $requireStudyProof,
            'message' => 'Đủ điều kiện đăng ký giữ chỗ.'
        ];
    }

    /**
     * Khóa giường tức thời sử dụng Database Transaction & Row-Level Locking (SELECT ... FOR UPDATE)
     * Ngăn chặn Race Condition khi hàng nghìn sinh viên cùng truy cập vào thời điểm mở cổng.
     */
    public static function lockBedWithTransaction(string $maSinhVien, string $maGiuong, int $maDot, string $loaiLuong): array {
        Database::beginTransaction();

        try {
            // Khóa dòng bản ghi Giường
            $giuong = Database::fetch(
                "SELECT * FROM Giuong WHERE MaGiuong = :maGiuong FOR UPDATE",
                ['maGiuong' => $maGiuong]
            );

            if (!$giuong) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Mã giường không tồn tại'];
            }

            if ($giuong['TrangThaiGiuong'] !== 'Trống') {
                Database::rollBack();
                return ['success' => false, 'message' => 'Giường vừa được sinh viên khác giữ chỗ. Vui lòng chọn giường khác!'];
            }

            // Chuyển ngay trạng thái giường sang 'Đang giữ chỗ'
            Database::execute(
                "UPDATE Giuong SET TrangThaiGiuong = 'Đang giữ chỗ' WHERE MaGiuong = :maGiuong",
                ['maGiuong' => $maGiuong]
            );

            // Tạo hồ sơ đăng ký
            $maHoSo = 'HS' . date('Ymd') . strtoupper(substr(uniqid(), -5));
            Database::execute(
                "INSERT INTO HoSoDangKy (MaHoSo, MaSinhVien, MaDot, MaGiuong, LoaiLuongDangKy, ThoiGianNop, TrangThaiHoSo)
                 VALUES (:maHoSo, :maSV, :maDot, :maGiuong, :loaiLuong, NOW(), 'Chờ duyệt')",
                [
                    'maHoSo' => $maHoSo,
                    'maSV' => $maSinhVien,
                    'maDot' => $maDot,
                    'maGiuong' => $maGiuong,
                    'loaiLuong' => $loaiLuong
                ]
            );

            Database::commit();

            return [
                'success' => true,
                'maHoSo' => $maHoSo,
                'message' => 'Giữ chỗ thành công! Hồ sơ của bạn đã được tiếp nhận và chuyển đến cán bộ quản lý cụm nhà.'
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi hệ thống khi khóa giường: ' . $e->getMessage()];
        }
    }
}
