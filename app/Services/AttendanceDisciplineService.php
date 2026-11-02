<?php

namespace App\Services;

use App\Core\Database;

/**
 * CHỨC NĂNG 8: Giám sát Vắng mặt 30 ngày liên tục & Tổng hợp Điểm rèn luyện gửi các Trường
 */
class AttendanceDisciplineService {
    /**
     * Tự động phát hiện sinh viên vắng mặt quá 30 ngày liên tục mà không có đơn xin phép tạm vắng hợp lệ
     */
    public static function detectLongTermAbsence(int $daysThreshold = 30): array {
        // Lấy danh sách tất cả sinh viên đang nội trú
        $activeStudents = Database::fetchAll("
            SELECT sv.MaSinhVien, sv.HoVaTen, sv.MaTruong, sv.SoDienThoai, sv.SDTPhuHuynh,
                   hd.MaHopDong, g.MaPhong, tn.MaToaNha, cn.TenCum
            FROM SinhVien sv
            JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE hd.TrangThaiHopDong = 'Đang hiệu lực'
        ");

        $warningList = [];
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$daysThreshold} days"));

        foreach ($activeStudents as $sv) {
            // Lần quét cổng gần nhất
            $lastScan = Database::fetch(
                "SELECT ThoiGianQuet, CongKiemSoat, HuongDi 
                 FROM NhatKyRaVao 
                 WHERE MaSinhVien = :sv 
                 ORDER BY ThoiGianQuet DESC LIMIT 1",
                ['sv' => $sv['MaSinhVien']]
            );

            $lastActiveTime = $lastScan ? $lastScan['ThoiGianQuet'] : null;

            // Nếu không có lượt quét nào hoặc lượt quét cuối trước cutoffDate
            if (!$lastActiveTime || $lastActiveTime < $cutoffDate) {
                // Kiểm tra có đơn xin tạm vắng đã duyệt bao phủ khoảng thời gian này hay không
                $hasApprovedLeave = Database::fetch(
                    "SELECT * FROM DonTuMotCua
                     WHERE MaSinhVien = :sv 
                       AND LoaiDon = 'Xin tạm vắng' 
                       AND TrangThaiDuyet = 'Đã duyệt'
                       AND DenNgay >= CURDATE()
                     LIMIT 1",
                    ['sv' => $sv['MaSinhVien']]
                );

                if (!$hasApprovedLeave) {
                    $daysAbsent = $lastActiveTime ? (int)((time() - strtotime($lastActiveTime)) / 86400) : $daysThreshold + 1;
                    $warningList[] = [
                        'maSinhVien' => $sv['MaSinhVien'],
                        'hoVaTen' => $sv['HoVaTen'],
                        'maTruong' => $sv['MaTruong'],
                        'maPhong' => $sv['MaPhong'],
                        'tenCum' => $sv['TenCum'],
                        'sdtPhuHuynh' => $sv['SDTPhuHuynh'],
                        'ngayQuetCuoi' => $lastActiveTime ?: 'Chưa từng quét thẻ',
                        'soNgayVang' => $daysAbsent,
                        'mucDoCanhBao' => 'Đề xuất lập biên bản xóa tên & báo cáo Trường'
                    ];
                }
            }
        }

        return [
            'total_checked' => count($activeStudents),
            'total_warnings' => count($warningList),
            'warnings' => $warningList
        ];
    }

    /**
     * Tự động tổng hợp Điểm rèn luyện theo từng Trường thành viên
     */
    public static function exportTrainingPointsByUniversity(int $hocKy, string $namHoc, ?string $maTruong = null): array {
        $sql = "
            SELECT 
                t.MaTruong,
                t.TenTruong,
                sv.MaSinhVien,
                sv.HoVaTen,
                rl.HocKy,
                rl.NamHoc,
                COALESCE(rl.TongDiemCuoiKy, 80) AS TongDiemCuoiKy,
                COALESCE(rl.XepLoai, 'Tốt') AS XepLoai,
                rl.DaGuiVeTruong
            FROM SinhVien sv
            JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            LEFT JOIN RenLuyenNoiTru rl ON sv.MaSinhVien = rl.MaSinhVien 
                 AND rl.HocKy = :hk AND rl.NamHoc = :nam
            WHERE sv.TrangThaiNoiTru = 'Đang nội trú'
        ";

        $params = ['hk' => $hocKy, 'nam' => $namHoc];

        if ($maTruong) {
            $sql .= " AND t.MaTruong = :truong";
            $params['truong'] = $maTruong;
        }

        $sql .= " ORDER BY t.MaTruong ASC, rl.TongDiemCuoiKy DESC";

        $results = Database::fetchAll($sql, $params);

        // Gom nhóm theo trường
        $grouped = [];
        foreach ($results as $row) {
            $tr = $row['MaTruong'];
            if (!isset($grouped[$tr])) {
                $grouped[$tr] = [
                    'maTruong' => $tr,
                    'tenTruong' => $row['TenTruong'],
                    'students' => []
                ];
            }
            $grouped[$tr]['students'][] = $row;
        }

        return $grouped;
    }
}
