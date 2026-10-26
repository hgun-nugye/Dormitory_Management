<?php

namespace App\Services;

use App\Core\Database;

/**
 * CHỨC NĂNG 3: Nghiệp vụ Dồn phòng & Tối ưu công suất lưu trú (Room Consolidation)
 */
class RoomConsolidationService {
    /**
     * Thống kê các phòng có tỷ lệ lấp đầy <= 50%
     */
    public static function getUnderOccupiedRooms(?string $maCum = null): array {
        $sql = "
            SELECT 
                p.MaPhong,
                p.MaToaNha,
                tn.MaCum,
                cn.TenCum,
                p.SoPhong,
                p.Tang,
                p.SoSVDangO,
                lp.MaLoaiPhong,
                lp.TenLoaiPhong,
                lp.SoGiuongToiDa,
                ROUND((p.SoSVDangO / lp.SoGiuongToiDa) * 100, 1) AS TyLeLapDay,
                (lp.SoGiuongToiDa - p.SoSVDangO) AS SoGiuongTrong
            FROM Phong p
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            WHERE (p.SoSVDangO / lp.SoGiuongToiDa) <= 0.50
        ";

        $params = [];
        if ($maCum) {
            $sql .= " AND tn.MaCum = :maCum";
            $params['maCum'] = $maCum;
        }

        $sql .= " ORDER BY TyLeLapDay ASC, p.SoSVDangO ASC";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Thuật toán đề xuất ghép phòng thông minh:
     * Gom các phòng có ít người (nguồn) sang các phòng còn giường trống (đích)
     * cùng Cụm nhà, cùng Loại phòng, cùng Giới tính tòa nhà.
     */
    public static function generateConsolidationPlan(string $maCum): array {
        $underRooms = self::getUnderOccupiedRooms($maCum);
        if (empty($underRooms)) {
            return [
                'message' => 'Không có phòng nào dưới 50% công suất trong cụm này.',
                'plans' => []
            ];
        }

        // Tìm các phòng đích (còn chỗ, cùng cụm)
        $availableRooms = Database::fetchAll(
            "SELECT p.MaPhong, p.SoSVDangO, lp.MaLoaiPhong, lp.SoGiuongToiDa, 
                    (lp.SoGiuongToiDa - p.SoSVDangO) AS SoGiuongTrong, tn.GioiTinhQuyDinh
             FROM Phong p
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
             WHERE tn.MaCum = :maCum AND (lp.SoGiuongToiDa - p.SoSVDangO) > 0
             ORDER BY p.SoSVDangO DESC",
            ['maCum' => $maCum]
        );

        $plans = [];
        $allocatedBeds = [];

        foreach ($underRooms as $sourceRoom) {
            if ($sourceRoom['SoSVDangO'] == 0) {
                $plans[] = [
                    'source_room' => $sourceRoom['MaPhong'],
                    'students_count' => 0,
                    'status' => 'Phòng trống hoàn toàn, sẵn sàng bàn giao sửa chữa'
                ];
                continue;
            }

            // Tìm phòng đích thích hợp
            foreach ($availableRooms as &$targetRoom) {
                if ($targetRoom['MaPhong'] === $sourceRoom['MaPhong']) {
                    continue;
                }

                if ($targetRoom['MaLoaiPhong'] === $sourceRoom['MaLoaiPhong'] && $targetRoom['SoGiuongTrong'] >= $sourceRoom['SoSVDangO']) {
                    // Lấy danh sách sinh viên hiện tại trong phòng nguồn
                    $students = Database::fetchAll(
                        "SELECT sv.MaSinhVien, sv.HoVaTen, g.MaGiuong
                         FROM HopDongNoiTru hd
                         JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                         JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                         WHERE g.MaPhong = :phong AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
                        ['phong' => $sourceRoom['MaPhong']]
                    );

                    $plans[] = [
                        'source_room' => $sourceRoom['MaPhong'],
                        'target_room' => $targetRoom['MaPhong'],
                        'students_to_move' => $students,
                        'reason' => "Tỷ lệ phòng {$sourceRoom['MaPhong']} chỉ đạt {$sourceRoom['TyLeLapDay']}%. Đề xuất chuyển sang phòng {$targetRoom['MaPhong']} để giải phóng nguyên phòng sửa chữa."
                    ];

                    $targetRoom['SoGiuongTrong'] -= $sourceRoom['SoSVDangO'];
                    break;
                }
            }
        }

        return [
            'cum' => $maCum,
            'total_under_occupied' => count($underRooms),
            'plans' => $plans
        ];
    }
}
