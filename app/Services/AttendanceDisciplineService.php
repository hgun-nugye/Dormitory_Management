<?php

namespace App\Services;

use App\Core\Database;
use Exception;

/**
 * CHỨC NĂNG 6: Giám sát An ninh Ra Vào & Đánh giá Điểm Rèn luyện Nội trú Sinh viên
 */
class AttendanceDisciplineService
{
    /**
     * Tự động phát hiện sinh viên vắng mặt quá 30 ngày liên tục mà không có đơn xin phép tạm vắng hợp lệ
     */
    public static function detectLongTermAbsence(
        int $daysThreshold = 30,
        ?string $maCum = null,
        ?string $maToa = null,
        ?string $maKhu = null
    ): array {
        $whereConditions = ["hd.TrangThaiHopDong = 'Đang hiệu lực'"];
        $params = [];

        if (!empty($maToa)) {
            $whereConditions[] = "tn.MaToaNha = :toa";
            $params['toa'] = $maToa;
        } elseif (!empty($maCum)) {
            $whereConditions[] = "tn.MaCum = :cum";
            $params['cum'] = $maCum;
        } elseif (!empty($maKhu)) {
            $whereConditions[] = "cn.MaKhu = :khu";
            $params['khu'] = $maKhu;
        }

        $whereSql = implode(' AND ', $whereConditions);

        // Lấy danh sách tất cả sinh viên đang nội trú theo phạm vi
        $activeStudents = Database::fetchAll("
            SELECT sv.MaSinhVien, sv.HoVaTen, sv.MaTruong, sv.SoDienThoai, sv.SDTPhuHuynh,
                   hd.MaHopDong, g.MaGiuong, g.MaPhong, tn.MaToaNha, tn.TenToaNha,
                   cn.MaCum, cn.TenCum, cn.MaKhu,
                   t.TenTruong
            FROM SinhVien sv
            JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            WHERE {$whereSql}
            ORDER BY cn.MaKhu ASC, tn.MaToaNha ASC, g.MaPhong ASC
        ", $params);

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

            // Nếu không có lượt quét nào hoặc lượt quét cuối trước cutoffDate (> 30 ngày)
            if (!$lastActiveTime || $lastActiveTime < $cutoffDate) {
                // Kiểm tra có đơn xin tạm vắng đã duyệt bao phủ khoảng thời gian hiện tại hay không
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
                    $daysAbsent = $lastActiveTime ? (int)floor((time() - strtotime($lastActiveTime)) / 86400) : $daysThreshold + 1;
                    $warningList[] = [
                        'maSinhVien' => $sv['MaSinhVien'],
                        'hoVaTen' => $sv['HoVaTen'],
                        'maTruong' => $sv['MaTruong'],
                        'tenTruong' => $sv['TenTruong'] ?? $sv['MaTruong'],
                        'maPhong' => $sv['MaPhong'],
                        'maGiuong' => $sv['MaGiuong'],
                        'maToaNha' => $sv['MaToaNha'],
                        'tenToaNha' => $sv['TenToaNha'],
                        'maCum' => $sv['MaCum'],
                        'tenCum' => $sv['TenCum'],
                        'maKhu' => $sv['MaKhu'],
                        'soDienThoai' => $sv['SoDienThoai'],
                        'sdtPhuHuynh' => $sv['SDTPhuHuynh'],
                        'ngayQuetCuoi' => $lastActiveTime ?: 'Chưa từng quét thẻ',
                        'congCuoi' => $lastScan['CongKiemSoat'] ?? 'Chưa có',
                        'huongCuoi' => $lastScan['HuongDi'] ?? 'Chưa có',
                        'soNgayVang' => $daysAbsent,
                        'mucDoCanhBao' => ($daysAbsent > 45) ? 'Khẩn cấp (Vắng > 45 ngày)' : 'Cảnh báo vắng kéo dài'
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
     * Ghi nhận sự kiện rèn luyện / vi phạm nội quy KTX (Khởi điểm 100đ, clamp 0-100, chặn khi đã khóa)
     */
    public static function recordDisciplineEvent(
        string $maSinhVien,
        string $namHoc,
        int $hocKy,
        string $loaiGhiNhan,
        string $noiDung,
        int $diemThayDoi,
        ?string $nguoiGhiNhan = null
    ): array {
        // 1. Kiểm tra sinh viên có tồn tại không
        $student = Database::fetch("SELECT MaSinhVien, HoVaTen FROM SinhVien WHERE MaSinhVien = :sv", ['sv' => $maSinhVien]);
        if (!$student) {
            return ['success' => false, 'message' => "Không tìm thấy sinh viên có mã {$maSinhVien}!"];
        }

        // 2. Kiểm tra học kỳ rèn luyện của sinh viên đã bị khóa gửi trường chưa
        $lockedCheck = Database::fetch(
            "SELECT DaGuiVeTruong FROM RenLuyenNoiTru 
             WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk AND DaGuiVeTruong = 1 
             LIMIT 1",
            ['sv' => $maSinhVien, 'nam' => $namHoc, 'hk' => $hocKy]
        );

        if ($lockedCheck) {
            return [
                'success' => false,
                'message' => "Điểm rèn luyện của sinh viên {$student['HoVaTen']} trong Học kỳ {$hocKy} ({$namHoc}) đã được gửi về Trường và ĐÃ KHÓA SỬA ĐỔI!"
            ];
        }

        Database::beginTransaction();
        try {
            // 3. Thêm bản ghi sự kiện mới
            Database::execute(
                "INSERT INTO RenLuyenNoiTru (MaSinhVien, NamHoc, HocKy, LoaiGhiNhan, NoiDung, DiemThayDoi, TongDiemCuoiKy, XepLoai, DaGuiVeTruong)
                 VALUES (:sv, :nam, :hk, :loai, :nd, :diem, 100, 'Xuất sắc', 0)",
                [
                    'sv' => $maSinhVien,
                    'nam' => $namHoc,
                    'hk' => $hocKy,
                    'loai' => $loaiGhiNhan,
                    'nd' => $noiDung,
                    'diem' => $diemThayDoi
                ]
            );

            // 4. Tính toán tổng điểm rèn luyện cuối kỳ: Khởi điểm 100 + SUM(DiemThayDoi)
            $sumRow = Database::fetch(
                "SELECT COALESCE(SUM(DiemThayDoi), 0) AS TotalChange 
                 FROM RenLuyenNoiTru 
                 WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk",
                ['sv' => $maSinhVien, 'nam' => $namHoc, 'hk' => $hocKy]
            );

            $totalChange = (int)($sumRow['TotalChange'] ?? 0);
            $finalScore = max(0, min(100, 100 + $totalChange));

            // 5. Xác định xếp loại rèn luyện
            $rating = self::determineRating($finalScore);

            // 6. Cập nhật đồng bộ TongDiemCuoiKy và XepLoai cho tất cả các bản ghi của sinh viên trong kỳ
            Database::execute(
                "UPDATE RenLuyenNoiTru 
                 SET TongDiemCuoiKy = :score, XepLoai = :rating 
                 WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk",
                [
                    'score' => $finalScore,
                    'rating' => $rating,
                    'sv' => $maSinhVien,
                    'nam' => $namHoc,
                    'hk' => $hocKy
                ]
            );

            Database::commit();

            return [
                'success' => true,
                'finalScore' => $finalScore,
                'rating' => $rating,
                'message' => "Đã ghi nhận sự kiện rèn luyện thành công cho sinh viên {$student['HoVaTen']} ({$maSinhVien}). Điểm hiện tại: {$finalScore}/100 ({$rating})."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi ghi nhận điểm rèn luyện: ' . $e->getMessage()];
        }
    }

    /**
     * Xác định xếp loại rèn luyện dựa trên tổng điểm cuối kỳ
     */
    public static function determineRating(int $score): string
    {
        if ($score >= 90) return 'Xuất sắc';
        if ($score >= 80) return 'Tốt';
        if ($score >= 65) return 'Khá';
        if ($score >= 50) return 'Trung bình';
        return 'Yếu';
    }

    /**
     * Lấy danh sách tổng hợp điểm rèn luyện nội trú của sinh viên
     */
    public static function getStudentDisciplineSummary(
        string $namHoc,
        int $hocKy,
        ?string $maCum = null,
        ?string $maToa = null,
        ?string $maKhu = null,
        ?string $search = null
    ): array {
        $whereConditions = ["hd.TrangThaiHopDong = 'Đang hiệu lực'"];
        $params = [
            'nam' => $namHoc,
            'hk' => $hocKy
        ];

        if (!empty($maToa)) {
            $whereConditions[] = "tn.MaToaNha = :toa";
            $params['toa'] = $maToa;
        } elseif (!empty($maCum)) {
            $whereConditions[] = "tn.MaCum = :cum";
            $params['cum'] = $maCum;
        } elseif (!empty($maKhu)) {
            $whereConditions[] = "cn.MaKhu = :khu";
            $params['khu'] = $maKhu;
        }

        if (!empty($search)) {
            $whereConditions[] = "(sv.MaSinhVien LIKE :search1 OR sv.HoVaTen LIKE :search2 OR g.MaPhong LIKE :search3)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $whereSql = implode(' AND ', $whereConditions);

        $sql = "
            SELECT sv.MaSinhVien, sv.HoVaTen, sv.MaTruong, sv.SoDienThoai,
                   t.TenTruong,
                   g.MaPhong, g.MaGiuong, tn.MaToaNha, tn.TenToaNha, cn.MaCum, cn.TenCum, cn.MaKhu,
                   COALESCE(sub.TongDiemCuoiKy, 100) AS TongDiemCuoiKy,
                   COALESCE(sub.XepLoai, 'Xuất sắc') AS XepLoai,
                   COALESCE(sub.DaGuiVeTruong, 0) AS DaGuiVeTruong,
                   COALESCE(sub.TotalEvents, 0) AS TotalEvents,
                   COALESCE(sub.TotalChange, 0) AS TotalChange
            FROM SinhVien sv
            JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            LEFT JOIN (
                SELECT MaSinhVien,
                       MAX(TongDiemCuoiKy) AS TongDiemCuoiKy,
                       MAX(XepLoai) AS XepLoai,
                       MAX(DaGuiVeTruong) AS DaGuiVeTruong,
                       COUNT(*) AS TotalEvents,
                       SUM(DiemThayDoi) AS TotalChange
                FROM RenLuyenNoiTru
                WHERE NamHoc = :nam AND HocKy = :hk
                GROUP BY MaSinhVien
            ) sub ON sv.MaSinhVien = sub.MaSinhVien
            WHERE {$whereSql}
            ORDER BY cn.MaKhu ASC, tn.MaToaNha ASC, g.MaPhong ASC, sv.HoVaTen ASC
        ";

        return Database::fetchAll($sql, $params);
    }

    /**
     * Lấy toàn bộ timeline lịch sử các sự kiện rèn luyện của 1 sinh viên trong kỳ
     */
    public static function getStudentDisciplineHistory(string $maSinhVien, string $namHoc, int $hocKy): array
    {
        $events = Database::fetchAll("
            SELECT rl.*, sv.HoVaTen
            FROM RenLuyenNoiTru rl
            JOIN SinhVien sv ON rl.MaSinhVien = sv.MaSinhVien
            WHERE rl.MaSinhVien = :sv AND rl.NamHoc = :nam AND rl.HocKy = :hk
            ORDER BY rl.MaDanhGia DESC
        ", [
            'sv' => $maSinhVien,
            'nam' => $namHoc,
            'hk' => $hocKy
        ]);

        $student = Database::fetch("
            SELECT sv.MaSinhVien, sv.HoVaTen, sv.MaTruong, t.TenTruong, g.MaPhong, tn.MaToaNha
            FROM SinhVien sv
            LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            LEFT JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien AND hd.TrangThaiHopDong = 'Đang hiệu lực'
            LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            WHERE sv.MaSinhVien = :sv
        ", ['sv' => $maSinhVien]);

        $finalScore = 100;
        $rating = 'Xuất sắc';
        $isLocked = false;

        if (!empty($events)) {
            $finalScore = (int)$events[0]['TongDiemCuoiKy'];
            $rating = $events[0]['XepLoai'];
            $isLocked = ((int)$events[0]['DaGuiVeTruong'] === 1);
        }

        return [
            'student' => $student,
            'namHoc' => $namHoc,
            'hocKy' => $hocKy,
            'finalScore' => $finalScore,
            'rating' => $rating,
            'isLocked' => $isLocked,
            'events' => $events
        ];
    }

    /**
     * Chốt điểm và gửi báo cáo điểm rèn luyện về các trường đại học thành viên (Khóa cứng DaGuiVeTruong = 1)
     */
    public static function lockAndSendToUniversity(
        string $namHoc,
        int $hocKy,
        ?string $maTruong = null,
        ?string $maCum = null
    ): array {
        Database::beginTransaction();
        try {
            // 1. Lấy danh sách tất cả sinh viên đang nội trú phù hợp phạm vi
            $where = ["hd.TrangThaiHopDong = 'Đang hiệu lực'"];
            $params = [];

            if (!empty($maTruong)) {
                $where[] = "sv.MaTruong = :truong";
                $params['truong'] = $maTruong;
            }
            if (!empty($maCum)) {
                $where[] = "tn.MaCum = :cum";
                $params['cum'] = $maCum;
            }

            $whereSql = implode(' AND ', $where);

            $students = Database::fetchAll("
                SELECT sv.MaSinhVien
                FROM SinhVien sv
                JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien
                JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE {$whereSql}
            ", $params);

            if (empty($students)) {
                Database::rollBack();
                return ['success' => false, 'message' => 'Không tìm thấy sinh viên nào trong phạm vi để chốt điểm rèn luyện.'];
            }

            $lockedCount = 0;
            foreach ($students as $s) {
                $svId = $s['MaSinhVien'];

                // Kiểm tra xem sinh viên đã có bản ghi nào trong RenLuyenNoiTru học kỳ này chưa
                $hasRecord = Database::fetch(
                    "SELECT COUNT(*) AS total FROM RenLuyenNoiTru 
                     WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk",
                    ['sv' => $svId, 'nam' => $namHoc, 'hk' => $hocKy]
                );

                if (($hasRecord['total'] ?? 0) > 0) {
                    // Cập nhật DaGuiVeTruong = 1
                    Database::execute(
                        "UPDATE RenLuyenNoiTru 
                         SET DaGuiVeTruong = 1 
                         WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk",
                        ['sv' => $svId, 'nam' => $namHoc, 'hk' => $hocKy]
                    );
                } else {
                    // Nếu sinh viên chưa có vi phạm hoặc khen thưởng nào -> Tạo 1 bản ghi chốt điểm chuẩn 100/100
                    Database::execute(
                        "INSERT INTO RenLuyenNoiTru (MaSinhVien, NamHoc, HocKy, LoaiGhiNhan, NoiDung, DiemThayDoi, TongDiemCuoiKy, XepLoai, DaGuiVeTruong)
                         VALUES (:sv, :nam, :hk, 'Khen thưởng', 'Chấp hành tốt mọi nội quy và quy chế KTX trong học kỳ', 0, 100, 'Xuất sắc', 1)",
                        ['sv' => $svId, 'nam' => $namHoc, 'hk' => $hocKy]
                    );
                }
                $lockedCount++;
            }

            Database::commit();

            return [
                'success' => true,
                'totalLocked' => $lockedCount,
                'message' => "Đã chốt và khóa thành công điểm rèn luyện của {$lockedCount} sinh viên gửi về Trường (Học kỳ {$hocKy} - Năm học {$namHoc}). Dữ liệu đã chuyển sang trạng thái chỉ đọc."
            ];
        } catch (Exception $e) {
            Database::rollBack();
            return ['success' => false, 'message' => 'Lỗi khi chốt điểm rèn luyện gửi trường: ' . $e->getMessage()];
        }
    }

    /**
     * Mở khóa điểm rèn luyện khi có đơn khiếu nại được Ban Giám đốc chấp thuận (Chỉ Quản trị viên/Admin)
     */
    public static function unlockDisciplineRecord(string $maSinhVien, string $namHoc, int $hocKy, string $lyDo): array
    {
        $updated = Database::execute(
            "UPDATE RenLuyenNoiTru 
             SET DaGuiVeTruong = 0 
             WHERE MaSinhVien = :sv AND NamHoc = :nam AND HocKy = :hk",
            ['sv' => $maSinhVien, 'nam' => $namHoc, 'hk' => $hocKy]
        );

        if ($updated > 0) {
            return [
                'success' => true,
                'message' => "Đã mở khóa chỉnh sửa điểm rèn luyện cho sinh viên {$maSinhVien} (Lý do: {$lyDo})."
            ];
        }

        return ['success' => false, 'message' => "Không tìm thấy bản ghi rèn luyện phù hợp để mở khóa."];
    }

    /**
     * Giả lập quẹt thẻ Barrier cổng KTX (Phục vụ Demo & Tích hợp thiết bị ngoại vi)
     */
    public static function simulateBarrierScan(string $maSinhVien, string $congKiemSoat, string $huongDi): array
    {
        $student = Database::fetch("SELECT MaSinhVien, HoVaTen FROM SinhVien WHERE MaSinhVien = :sv", ['sv' => $maSinhVien]);
        if (!$student) {
            return ['success' => false, 'message' => "Mã sinh viên {$maSinhVien} không tồn tại trên hệ thống KTX!"];
        }

        $huongDiFormatted = ($huongDi === 'Vào' || $huongDi === 'Vao') ? 'Vào' : 'Ra';

        Database::execute(
            "INSERT INTO NhatKyRaVao (MaSinhVien, CongKiemSoat, HuongDi, ThoiGianQuet)
             VALUES (:sv, :cong, :huong, NOW())",
            [
                'sv' => $maSinhVien,
                'cong' => $congKiemSoat,
                'huong' => $huongDiFormatted
            ]
        );

        $nowStr = date('H:i:s d/m/Y');
        return [
            'success' => true,
            'message' => "Quét thẻ thành công: Sinh viên {$student['HoVaTen']} ({$maSinhVien}) {$huongDiFormatted} tại {$congKiemSoat} lúc {$nowStr}."
        ];
    }

    /**
     * Lấy nhật ký quét thẻ Barrier (Read-only) có phân trang & bộ lọc
     */
    public static function getBarrierLogs(
        ?string $maCum = null,
        ?string $maToa = null,
        ?string $maKhu = null,
        ?string $search = null,
        ?string $huongDi = null,
        int $limit = 50,
        int $offset = 0
    ): array {
        $whereConditions = [];
        $params = [];

        if (!empty($maToa)) {
            $whereConditions[] = "tn.MaToaNha = :toa";
            $params['toa'] = $maToa;
        } elseif (!empty($maCum)) {
            $whereConditions[] = "tn.MaCum = :cum";
            $params['cum'] = $maCum;
        } elseif (!empty($maKhu)) {
            $whereConditions[] = "cn.MaKhu = :khu";
            $params['khu'] = $maKhu;
        }

        if (!empty($huongDi) && in_array($huongDi, ['Vào', 'Ra'])) {
            $whereConditions[] = "nk.HuongDi = :huong";
            $params['huong'] = $huongDi;
        }

        if (!empty($search)) {
            $whereConditions[] = "(nk.MaSinhVien LIKE :search1 OR sv.HoVaTen LIKE :search2 OR nk.CongKiemSoat LIKE :search3)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
        }

        $whereSql = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

        // Đếm tổng số
        $countSql = "
            SELECT COUNT(*) AS total
            FROM NhatKyRaVao nk
            JOIN SinhVien sv ON nk.MaSinhVien = sv.MaSinhVien
            LEFT JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien AND hd.TrangThaiHopDong = 'Đang hiệu lực'
            LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            {$whereSql}
        ";
        $totalRow = Database::fetch($countSql, $params);
        $total = (int)($totalRow['total'] ?? 0);

        // Truy vấn dữ liệu
        $dataSql = "
            SELECT nk.MaLuot, nk.MaSinhVien, nk.CongKiemSoat, nk.HuongDi, nk.ThoiGianQuet,
                   sv.HoVaTen, sv.MaTruong, t.TenTruong,
                   g.MaPhong, tn.MaToaNha, cn.TenCum, cn.MaKhu
            FROM NhatKyRaVao nk
            JOIN SinhVien sv ON nk.MaSinhVien = sv.MaSinhVien
            LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            LEFT JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien AND hd.TrangThaiHopDong = 'Đang hiệu lực'
            LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            {$whereSql}
            ORDER BY nk.ThoiGianQuet DESC
            LIMIT {$limit} OFFSET {$offset}
        ";
        $logs = Database::fetchAll($dataSql, $params);

        return [
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'logs' => $logs
        ];
    }

    /**
     * Xử lý trường hợp vắng mặt kéo dài: Lập biên bản vi phạm trừ điểm hoặc ghi nhận liên hệ
     */
    public static function createAbsenceAction(
        string $maSinhVien,
        string $hanhDong,
        string $ghiChu,
        string $namHoc,
        int $hocKy
    ): array {
        $student = Database::fetch("SELECT MaSinhVien, HoVaTen FROM SinhVien WHERE MaSinhVien = :sv", ['sv' => $maSinhVien]);
        if (!$student) {
            return ['success' => false, 'message' => "Không tìm thấy sinh viên {$maSinhVien}!"];
        }

        if ($hanhDong === 'lap_bien_ban') {
            // Lập biên bản vi phạm vắng mặt kéo dài không phép -> Trừ 25 điểm rèn luyện
            $nd = "Lập biên bản vi phạm nội quy: Vắng mặt nội trú kéo dài quá 30 ngày không có đơn xin phép tạm vắng. Ghi chú: " . $ghiChu;
            return self::recordDisciplineEvent($maSinhVien, $namHoc, $hocKy, 'Vi phạm', $nd, -25);
        }

        // Trường hợp liên hệ phụ huynh hoặc nhắc nhở
        return [
            'success' => true,
            'message' => "Đã ghi nhận tình trạng xử lý cho sinh viên {$student['HoVaTen']} ({$maSinhVien}): {$ghiChu}"
        ];
    }

    /**
     * Tự động tổng hợp Điểm rèn luyện theo từng Trường thành viên
     */
    public static function exportTrainingPointsByUniversity(int $hocKy, string $namHoc, ?string $maTruong = null): array
    {
        $sql = "
            SELECT 
                t.MaTruong,
                t.TenTruong,
                sv.MaSinhVien,
                sv.HoVaTen,
                :hk_out AS HocKy,
                :nam_out AS NamHoc,
                COALESCE(rl.TongDiemCuoiKy, 100) AS TongDiemCuoiKy,
                COALESCE(rl.XepLoai, 'Xuất sắc') AS XepLoai,
                COALESCE(rl.DaGuiVeTruong, 0) AS DaGuiVeTruong
            FROM SinhVien sv
            JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
            LEFT JOIN (
                SELECT MaSinhVien,
                       MAX(TongDiemCuoiKy) AS TongDiemCuoiKy,
                       MAX(XepLoai) AS XepLoai,
                       MAX(DaGuiVeTruong) AS DaGuiVeTruong
                FROM RenLuyenNoiTru
                WHERE HocKy = :hk AND NamHoc = :nam
                GROUP BY MaSinhVien
            ) rl ON sv.MaSinhVien = rl.MaSinhVien
            WHERE sv.TrangThaiNoiTru = 'Đang nội trú'
        ";

        $params = [
            'hk_out' => $hocKy,
            'nam_out' => $namHoc,
            'hk' => $hocKy,
            'nam' => $namHoc
        ];

        if ($maTruong) {
            $sql .= " AND t.MaTruong = :truong";
            $params['truong'] = $maTruong;
        }

        $sql .= " ORDER BY t.MaTruong ASC, COALESCE(rl.TongDiemCuoiKy, 100) DESC";

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
