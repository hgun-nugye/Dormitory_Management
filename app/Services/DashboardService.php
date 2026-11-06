<?php

namespace App\Services;

use App\Core\Auth;
use App\Core\Database;

/**
 * Service: Tổng hợp dữ liệu Trung tâm Điều hành BQL KTX (Staff Dashboard)
 * Hỗ trợ phân quyền đa tầng (ALL, KHU, CUM, TOA, KE_TOAN, KY_THUAT, AN_NINH)
 * Tích hợp dữ liệu thời gian thực từ 6 phân hệ nghiệp vụ.
 */
class DashboardService
{
    /**
     * Xác định phạm vi và quyền hạn của cán bộ trên Dashboard
     */
    public static function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));
        $username = strtoupper($user['TenDangNhap'] ?? '');
        $chucVu = mb_strtolower($user['ChucVu'] ?? $user['profile']['ChucVu'] ?? '');
        $userId = isset($user['MaTaiKhoan']) ? (int)$user['MaTaiKhoan'] : 0;

        // Xác định tên vai trò hiển thị thân thiện
        $userRoleName = 'Cán bộ KTX';
        if ($role === 'SINH_VIEN' || $role === 'TRUONG_PHONG') {
            $userRoleName = ($role === 'TRUONG_PHONG') ? 'Trưởng phòng Sinh viên' : 'Sinh viên nội trú';
        } elseif ($role === 'KE_TOAN' || str_contains($chucVu, 'kế toán')) {
            $userRoleName = 'Nhân viên Kế toán';
        } elseif ($role === 'NV_LE_TAN' || str_contains($chucVu, 'lễ tân')) {
            $userRoleName = 'Nhân viên Lễ tân';
        } elseif (str_starts_with($username, 'NV_KT') || str_contains($chucVu, 'kỹ thuật') || str_contains($chucVu, 'điện nước')) {
            $userRoleName = 'Kỹ thuật viên KTX';
        } elseif ($role === 'AN_NINH' || $role === 'BAO_VE' || str_contains($chucVu, 'an ninh') || str_contains($chucVu, 'bảo vệ')) {
            $userRoleName = 'Cán bộ An ninh / Bảo vệ';
        } elseif ($role === 'TRUONG_TOA') {
            $userRoleName = 'Trưởng Tòa nhà';
        } elseif ($role === 'TRUONG_CUM') {
            $userRoleName = 'Trưởng BQL Cụm';
        } elseif ($role === 'TRUONG_KHU') {
            $userRoleName = 'Trưởng BQL Khu';
        } elseif ($role === 'ADMIN' || $role === 'QuanTriVien' || $role === 'NV_QUAN_LY_KTX') {
            $userRoleName = 'Quản trị viên / Ban Giám đốc';
        }

        // =========================================================================
        // 1. CHẶN TUYỆT ĐỐI SINH VIÊN VÀ CÁC ĐỐI TƯỢNG KHÔNG THUỘC BQL KTX
        // =========================================================================
        if ($role === 'SINH_VIEN' || $role === 'TRUONG_PHONG' || empty($role)) {
            return [
                'isAllowed' => false,
                'level' => 'DENIED',
                'levelNumber' => -1,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => 'Từ chối quyền truy cập',
                'badgeClass' => 'badge-danger',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'message' => "Từ chối quyền truy cập: Bạn đang đăng nhập với vai trò {$userRoleName}. Phân hệ Bảng điều khiển BQL KTX chỉ dành riêng cho Cán bộ Quản lý và Nhân viên KTX!"
            ];
        }

        // =========================================================================
        // 2. NHÓM CHUYÊN TRÁCH: KẾ TOÁN & TÀI CHÍNH
        // =========================================================================
        if ($role === 'KE_TOAN' || str_starts_with($username, 'KT') || str_contains($chucVu, 'kế toán')) {
            return [
                'isAllowed' => true,
                'level' => 'KE_TOAN',
                'levelNumber' => 0,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => 'Cán bộ Kế toán KTX',
                'badgeClass' => 'badge-success',
                'isKeToan' => true,
                'isKyThuat' => false,
                'isAnNinh' => false,
                'isManager' => false,
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
            ];
        }

        // =========================================================================
        // 3. NHÓM CHUYÊN TRÁCH: KỸ THUẬT VIÊN & THỢ SỬA CHỮA
        // =========================================================================
        if (str_starts_with($username, 'NV_KT') || str_contains($chucVu, 'kỹ thuật') || str_contains($chucVu, 'điện nước') || str_contains($chucVu, 'sửa chữa')) {
            return [
                'isAllowed' => true,
                'level' => 'KY_THUAT',
                'levelNumber' => 0,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => 'Kỹ thuật viên Sửa chữa KTX',
                'badgeClass' => 'badge-warning',
                'isKeToan' => false,
                'isKyThuat' => true,
                'isAnNinh' => false,
                'isManager' => false,
                'userId' => $userId,
                'maKhu' => null,
                'maCum' => !empty($cumQuanLy) ? $cumQuanLy : null,
                'maToa' => null,
            ];
        }

        // =========================================================================
        // 4. NHÓM CHUYÊN TRÁCH: AN NINH & BẢO VỆ
        // =========================================================================
        if ($role === 'AN_NINH' || $role === 'BAO_VE' || str_contains($chucVu, 'an ninh') || str_contains($chucVu, 'bảo vệ')) {
            return [
                'isAllowed' => true,
                'level' => 'AN_NINH',
                'levelNumber' => 0,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => 'Cán bộ An ninh KTX',
                'badgeClass' => 'badge-dark',
                'isKeToan' => false,
                'isKyThuat' => false,
                'isAnNinh' => true,
                'isManager' => false,
                'maKhu' => null,
                'maCum' => !empty($cumQuanLy) ? $cumQuanLy : null,
                'maToa' => null,
            ];
        }

        // =========================================================================
        // 5. NHÓM CÁN BỘ QUẢN LÝ (MANAGER TIER: CẤP 1 ĐẾN CẤP 4)
        // =========================================================================

        // Danh sách tra cứu cơ sở hạ tầng
        $khus = Database::fetchAll("SELECT MaKhu, TenKhu FROM KhuKTX ORDER BY MaKhu ASC");
        $cums = Database::fetchAll("SELECT MaCum, TenCum, MaKhu FROM CumNha ORDER BY MaKhu ASC, MaCum ASC");
        $toas = Database::fetchAll("
            SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu
            FROM ToaNha t
            JOIN CumNha c ON t.MaCum = c.MaCum
            ORDER BY c.MaKhu ASC, c.MaCum ASC, t.MaToaNha ASC
        ");

        // CẤP 4: Quản lý toàn KTX (ADMIN / Ban Giám đốc)
        if ($role === 'ADMIN' || $role === 'QuanTriVien' || $role === 'NV_QUAN_LY_KTX') {
            return [
                'isAllowed' => true,
                'level' => 'ALL',
                'levelNumber' => 4,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => 'Quản lý toàn KTX (Giám đốc / Admin)',
                'badgeClass' => 'badge-info',
                'isKeToan' => false,
                'isKyThuat' => false,
                'isAnNinh' => false,
                'isManager' => true,
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canFilterKhu' => true,
                'canFilterCum' => true,
                'canFilterToa' => true,
                'availableKhus' => $khus,
                'availableCums' => $cums,
                'availableToas' => $toas,
            ];
        }

        // CẤP 3: Trưởng Ban Quản lý Khu (A hoặc B)
        if ($role === 'TRUONG_KHU' || str_contains($chucVu, 'trưởng khu')) {
            $maKhu = ($cumQuanLy === 'A' || $cumQuanLy === 'B') ? $cumQuanLy : (str_contains($chucVu, 'khu b') ? 'B' : 'A');
            $cumsInKhu = array_values(array_filter($cums, fn($c) => $c['MaKhu'] === $maKhu));
            $toasInKhu = array_values(array_filter($toas, fn($t) => $t['MaKhu'] === $maKhu));

            return [
                'isAllowed' => true,
                'level' => 'KHU',
                'levelNumber' => 3,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => "Trưởng BQL KTX Khu {$maKhu}",
                'badgeClass' => 'badge-primary',
                'isKeToan' => false,
                'isKyThuat' => false,
                'isAnNinh' => false,
                'isManager' => true,
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canFilterKhu' => false,
                'canFilterCum' => true,
                'canFilterToa' => true,
                'availableKhus' => array_values(array_filter($khus, fn($k) => $k['MaKhu'] === $maKhu)),
                'availableCums' => $cumsInKhu,
                'availableToas' => $toasInKhu,
            ];
        }

        // CẤP 2: Trưởng Ban Quản lý Cụm
        if ($role === 'TRUONG_CUM' || str_contains($chucVu, 'trưởng cụm') || str_contains($chucVu, 'trưởng ban quản lý cụm')) {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $matchedCum = array_values(array_filter($cums, fn($c) => $c['MaCum'] === $maCum));
            $maKhu = !empty($matchedCum) ? $matchedCum[0]['MaKhu'] : 'A';
            $toasInCum = array_values(array_filter($toas, fn($t) => $t['MaCum'] === $maCum));

            return [
                'isAllowed' => true,
                'level' => 'CUM',
                'levelNumber' => 2,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => "Trưởng BQL Cụm nhà {$maCum}",
                'badgeClass' => 'badge-warning',
                'isKeToan' => false,
                'isKyThuat' => false,
                'isAnNinh' => false,
                'isManager' => true,
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'canFilterKhu' => false,
                'canFilterCum' => false,
                'canFilterToa' => true,
                'availableKhus' => array_values(array_filter($khus, fn($k) => $k['MaKhu'] === $maKhu)),
                'availableCums' => $matchedCum,
                'availableToas' => $toasInCum,
            ];
        }

        // CẤP 1: Trưởng Tòa nhà
        if ($role === 'TRUONG_TOA' || str_contains($chucVu, 'trưởng tòa')) {
            $maToa = !empty($cumQuanLy) ? $cumQuanLy : 'A01';
            $matchedToa = array_values(array_filter($toas, fn($t) => $t['MaToaNha'] === $maToa));
            $maCum = !empty($matchedToa) ? $matchedToa[0]['MaCum'] : 'AG';
            $maKhu = !empty($matchedToa) ? $matchedToa[0]['MaKhu'] : 'A';

            return [
                'isAllowed' => true,
                'level' => 'TOA',
                'levelNumber' => 1,
                'role' => $role,
                'userRoleName' => $userRoleName,
                'title' => "Trưởng Tòa nhà {$maToa}",
                'badgeClass' => 'badge-success',
                'isKeToan' => false,
                'isKyThuat' => false,
                'isAnNinh' => false,
                'isManager' => true,
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => $maToa,
                'canFilterKhu' => false,
                'canFilterCum' => false,
                'canFilterToa' => false,
                'availableKhus' => array_values(array_filter($khus, fn($k) => $k['MaKhu'] === $maKhu)),
                'availableCums' => array_values(array_filter($cums, fn($c) => $c['MaCum'] === $maCum)),
                'availableToas' => $matchedToa,
            ];
        }

        // Mặc định: Phân loại theo nhân viên thông thường
        return [
            'isAllowed' => true,
            'level' => 'VIEW_ONLY',
            'levelNumber' => 1,
            'role' => $role,
            'userRoleName' => $userRoleName,
            'title' => 'Cán bộ Nhân viên KTX',
            'badgeClass' => 'badge-info',
            'isKeToan' => false,
            'isKyThuat' => false,
            'isAnNinh' => false,
            'isManager' => true,
            'maKhu' => null,
            'maCum' => !empty($cumQuanLy) ? $cumQuanLy : null,
            'maToa' => null,
            'canFilterKhu' => false,
            'canFilterCum' => false,
            'canFilterToa' => false,
            'availableKhus' => $khus,
            'availableCums' => $cums,
            'availableToas' => $toas,
        ];
    }

    /**
     * Lấy toàn bộ dữ liệu Bảng điều khiển Quản lý đa tầng
     */
    public static function getManagerDashboardData(array $scope, ?string $filterKhu = null, ?string $filterCum = null, ?string $filterToa = null): array
    {
        // 1. Giải quyết phạm vi lọc thực tế theo RBAC Scope
        $activeKhu = $scope['maKhu'] ?: $filterKhu;
        $activeCum = $scope['maCum'] ?: $filterCum;
        $activeToa = $scope['maToa'] ?: $filterToa;

        if ($activeCum === 'all') $activeCum = null;
        if ($activeToa === 'all') $activeToa = null;
        if ($activeKhu === 'all') $activeKhu = null;

        // 2. TÍNH TOÁN 6 CHỈ SỐ KPI THỜI GIAN THỰC
        $kpis = self::calculateManagerKPIs($activeKhu, $activeCum, $activeToa);

        // 3. DANH SÁCH TÁC VỤ CẤP BÁCH CẦN XỬ LÝ NGAY (URGENT ACTIONS)
        $urgentActions = self::getUrgentActions($activeKhu, $activeCum, $activeToa);

        // 4. THỐNG KÊ CÔNG SUẤT VÀ TỶ LỆ LẤP ĐẦY THEO TÒA/CỤM
        $occupancyStats = self::getOccupancyStats($activeKhu, $activeCum, $activeToa);

        // 5. DANH SÁCH PHÒNG LẤP ĐẦY <= 50% CẦN DỒN GHÉP
        $underOccupiedRooms = self::getUnderOccupiedRooms($activeKhu, $activeCum, $activeToa);

        // 6. NHẬT KÝ HOẠT ĐỘNG VẬN HÀNH GẦN ĐÂY (LIVE ACTIVITY TIMELINE)
        $recentActivities = self::getRecentActivities($activeKhu, $activeCum, $activeToa);

        return [
            'scope' => $scope,
            'activeKhu' => $activeKhu,
            'activeCum' => $activeCum,
            'activeToa' => $activeToa,
            'kpis' => $kpis,
            'urgentActions' => $urgentActions,
            'occupancyStats' => $occupancyStats,
            'underOccupiedRooms' => $underOccupiedRooms,
            'recentActivities' => $recentActivities,
        ];
    }

    /**
     * Tính toán 6 chỉ số KPI cho Cán bộ Quản lý
     */
    private static function calculateManagerKPIs(?string $khu, ?string $cum, ?string $toa): array
    {
        // Base params for scope filtering
        $whereJoins = "
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
        ";
        $whereCond = " WHERE 1=1";
        $params = [];

        if (!empty($toa)) {
            $whereCond .= " AND tn.MaToaNha = :toa";
            $params['toa'] = $toa;
        } elseif (!empty($cum)) {
            $whereCond .= " AND tn.MaCum = :cum";
            $params['cum'] = $cum;
        } elseif (!empty($khu)) {
            $whereCond .= " AND cn.MaKhu = :khu";
            $params['khu'] = $khu;
        }

        // KPI 1: Hồ sơ 72h chờ duyệt (tổng + số hồ sơ sắp quá 48h)
        $sqlApp = "
            SELECT 
                COUNT(*) AS TotalPending,
                SUM(CASE WHEN hs.ThoiGianNop <= DATE_SUB(NOW(), INTERVAL 48 HOUR) THEN 1 ELSE 0 END) AS NearDeadline
            FROM HoSoDangKy hs
            JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
            {$whereJoins}
            {$whereCond} AND hs.TrangThaiHoSo = 'Chờ duyệt'
        ";
        $appRow = Database::fetch($sqlApp, $params) ?: ['TotalPending' => 0, 'NearDeadline' => 0];

        // KPI 2: Phòng lấp đầy <= 50%
        $sqlRoom = "
            SELECT 
                COUNT(*) AS TotalRooms,
                SUM(CASE WHEN (p.SoSVDangO / lp.SoGiuongToiDa) <= 0.50 THEN 1 ELSE 0 END) AS UnderOccupied,
                SUM(CASE WHEN p.SoSVDangO = 0 THEN 1 ELSE 0 END) AS EmptyRooms,
                ROUND(AVG(p.SoSVDangO / lp.SoGiuongToiDa) * 100, 1) AS AvgOccupancyRate,
                SUM(lp.SoGiuongToiDa - p.SoSVDangO) AS TotalEmptyBeds
            FROM Phong p
            JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE 1=1
        ";
        $roomParams = [];
        if (!empty($toa)) {
            $sqlRoom .= " AND tn.MaToaNha = :toa";
            $roomParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlRoom .= " AND tn.MaCum = :cum";
            $roomParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlRoom .= " AND cn.MaKhu = :khu";
            $roomParams['khu'] = $khu;
        }
        $roomRow = Database::fetch($sqlRoom, $roomParams) ?: [];

        // KPI 3: Sự cố Bảo trì đang chờ xử lý
        $sqlMaint = "
            SELECT 
                COUNT(*) AS TotalPending,
                SUM(CASE WHEN pb.MoTaHuHong LIKE '%khẩn%' OR pb.MoTaHuHong LIKE '%chập%' OR pb.MoTaHuHong LIKE '%ngập%' OR pb.MoTaHuHong LIKE '%vỡ%' THEN 1 ELSE 0 END) AS UrgentCount
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý', 'Đang sửa chữa')
        ";
        $maintParams = [];
        if (!empty($toa)) {
            $sqlMaint .= " AND tn.MaToaNha = :toa";
            $maintParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlMaint .= " AND tn.MaCum = :cum";
            $maintParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlMaint .= " AND cn.MaKhu = :khu";
            $maintParams['khu'] = $khu;
        }
        $maintRow = Database::fetch($sqlMaint, $maintParams) ?: ['TotalPending' => 0, 'UrgentCount' => 0];

        // KPI 4: Công nợ Điện Nước chưa thanh toán
        $sqlDebt = "
            SELECT 
                COUNT(DISTINCT hd.MaPhong) AS DebtRoomCount,
                COALESCE(SUM(hd.SoTienPhaiDong), 0) AS TotalDebtAmount
            FROM HoaDon hd
            JOIN Phong p ON hd.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE hd.TrangThai = 'Chưa thanh toán'
        ";
        $debtParams = [];
        if (!empty($toa)) {
            $sqlDebt .= " AND tn.MaToaNha = :toa";
            $debtParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlDebt .= " AND tn.MaCum = :cum";
            $debtParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlDebt .= " AND cn.MaKhu = :khu";
            $debtParams['khu'] = $khu;
        }
        $debtRow = Database::fetch($sqlDebt, $debtParams) ?: ['DebtRoomCount' => 0, 'TotalDebtAmount' => 0];

        // KPI 5: Sinh viên vắng mặt kéo dài > 30 ngày (sử dụng AttendanceDisciplineService)
        $absenceData = AttendanceDisciplineService::detectLongTermAbsence(30, $cum, $toa, $khu);
        $absenceCount = count($absenceData['warnings'] ?? []);

        // KPI 6: Đơn từ Một Cửa đang chờ duyệt
        $sqlReq = "
            SELECT COUNT(*) AS PendingRequests
            FROM DonTuMotCua dt
            LEFT JOIN HopDongNoiTru hd ON dt.MaHopDong = hd.MaHopDong
            LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE dt.TrangThaiDuyet = 'Chờ duyệt'
        ";
        $reqParams = [];
        if (!empty($toa)) {
            $sqlReq .= " AND tn.MaToaNha = :toa";
            $reqParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlReq .= " AND tn.MaCum = :cum";
            $reqParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlReq .= " AND cn.MaKhu = :khu";
            $reqParams['khu'] = $khu;
        }
        $reqRow = Database::fetch($sqlReq, $reqParams) ?: ['PendingRequests' => 0];

        return [
            'applications' => [
                'total' => (int)($appRow['TotalPending'] ?? 0),
                'urgent' => (int)($appRow['NearDeadline'] ?? 0),
            ],
            'rooms' => [
                'underOccupied' => (int)($roomRow['UnderOccupied'] ?? 0),
                'emptyRooms' => (int)($roomRow['EmptyRooms'] ?? 0),
                'avgRate' => (float)($roomRow['AvgOccupancyRate'] ?? 0),
                'emptyBeds' => (int)($roomRow['TotalEmptyBeds'] ?? 0),
            ],
            'maintenance' => [
                'total' => (int)($maintRow['TotalPending'] ?? 0),
                'urgent' => (int)($maintRow['UrgentCount'] ?? 0),
            ],
            'utilities' => [
                'debtRooms' => (int)($debtRow['DebtRoomCount'] ?? 0),
                'totalDebt' => (float)($debtRow['TotalDebtAmount'] ?? 0),
            ],
            'absence' => [
                'warnings' => $absenceCount,
            ],
            'requests' => [
                'total' => (int)($reqRow['PendingRequests'] ?? 0),
            ],
        ];
    }

    /**
     * Lấy danh sách các tác vụ khẩn cấp (Urgent Action Center)
     */
    private static function getUrgentActions(?string $khu, ?string $cum, ?string $toa): array
    {
        // 1. Hồ sơ 72h chờ duyệt lâu nhất (Top 5)
        $sqlApp = "
            SELECT 
                hs.MaHoSo, hs.MaSinhVien, hs.ThoiGianNop, hs.MaGiuong,
                sv.HoVaTen, sv.SoDienThoai, sv.GioiTinh,
                p.MaPhong, p.SoPhong, tn.MaToaNha, tn.TenToaNha, cn.TenCum,
                ROUND(TIMESTAMPDIFF(MINUTE, hs.ThoiGianNop, NOW()) / 60, 1) AS GioDaTroiQua
            FROM HoSoDangKy hs
            JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
            JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE hs.TrangThaiHoSo = 'Chờ duyệt'
        ";
        $appParams = [];
        if (!empty($toa)) {
            $sqlApp .= " AND tn.MaToaNha = :toa";
            $appParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlApp .= " AND tn.MaCum = :cum";
            $appParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlApp .= " AND cn.MaKhu = :khu";
            $appParams['khu'] = $khu;
        }
        $sqlApp .= " ORDER BY hs.ThoiGianNop ASC LIMIT 5";
        $urgentApps = Database::fetchAll($sqlApp, $appParams);

        // 2. Sự cố bảo trì cần giải quyết gấp (Top 5)
        $sqlMaint = "
            SELECT 
                pb.MaPhieu, pb.MoTaHuHong, pb.NgayBao, pb.TrangThaiSua, pb.LoaiSuCo,
                COALESCE(pb.MaPhong, 'Chung') AS MaPhong,
                tn.MaToaNha, tn.TenToaNha,
                sv.HoVaTen AS NguoiBao, sv.SoDienThoai AS SdtNguoiBao,
                tb.TenThietBi
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            WHERE pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý', 'Đang sửa chữa')
        ";
        $maintParams = [];
        if (!empty($toa)) {
            $sqlMaint .= " AND tn.MaToaNha = :toa";
            $maintParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlMaint .= " AND tn.MaCum = :cum";
            $maintParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlMaint .= " AND cn.MaKhu = :khu";
            $maintParams['khu'] = $khu;
        }
        $sqlMaint .= " ORDER BY pb.NgayBao ASC LIMIT 5";
        $urgentMaint = Database::fetchAll($sqlMaint, $maintParams);

        // 3. Top sinh viên vắng mặt kéo dài không phép (Top 5)
        $absenceData = AttendanceDisciplineService::detectLongTermAbsence(30, $cum, $toa, $khu);
        $urgentAbsence = array_slice($absenceData['warnings'] ?? [], 0, 5);

        // 4. Top Đơn Một Cửa chờ xử lý (Top 5)
        $sqlReq = "
            SELECT 
                dt.MaDon, dt.MaSinhVien, dt.LoaiDon, dt.LyDo, dt.TrangThaiDuyet, dt.SoTienChenhLechHoan,
                sv.HoVaTen, sv.SoDienThoai,
                COALESCE(p.MaPhong, 'N/A') AS MaPhong,
                COALESCE(tn.TenToaNha, 'Chung') AS TenToaNha
            FROM DonTuMotCua dt
            JOIN SinhVien sv ON dt.MaSinhVien = sv.MaSinhVien
            LEFT JOIN HopDongNoiTru hd ON dt.MaHopDong = hd.MaHopDong
            LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE dt.TrangThaiDuyet = 'Chờ duyệt'
        ";
        $reqParams = [];
        if (!empty($toa)) {
            $sqlReq .= " AND tn.MaToaNha = :toa";
            $reqParams['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sqlReq .= " AND tn.MaCum = :cum";
            $reqParams['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sqlReq .= " AND cn.MaKhu = :khu";
            $reqParams['khu'] = $khu;
        }
        $sqlReq .= " LIMIT 5";
        $urgentRequests = Database::fetchAll($sqlReq, $reqParams);

        return [
            'applications' => $urgentApps,
            'maintenance' => $urgentMaint,
            'absence' => $urgentAbsence,
            'requests' => $urgentRequests,
        ];
    }

    /**
     * Thống kê tỷ lệ lấp đầy theo Cụm/Tòa nhà
     */
    private static function getOccupancyStats(?string $khu, ?string $cum, ?string $toa): array
    {
        $sql = "
            SELECT 
                tn.MaToaNha,
                tn.TenToaNha,
                cn.MaCum,
                cn.TenCum,
                cn.MaKhu,
                COUNT(p.MaPhong) AS TongSoPhong,
                SUM(p.SoSVDangO) AS TongSVDangO,
                SUM(lp.SoGiuongToiDa) AS TongSucChua,
                ROUND((SUM(p.SoSVDangO) / SUM(lp.SoGiuongToiDa)) * 100, 1) AS TyLeLapDay,
                SUM(CASE WHEN (p.SoSVDangO / lp.SoGiuongToiDa) <= 0.50 THEN 1 ELSE 0 END) AS SoPhongDuoi50
            FROM ToaNha tn
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN Phong p ON tn.MaToaNha = p.MaToaNha
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            WHERE 1=1
        ";
        $params = [];
        if (!empty($toa)) {
            $sql .= " AND tn.MaToaNha = :toa";
            $params['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sql .= " AND tn.MaCum = :cum";
            $params['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sql .= " AND cn.MaKhu = :khu";
            $params['khu'] = $khu;
        }

        $sql .= " GROUP BY tn.MaToaNha, tn.TenToaNha, cn.MaCum, cn.TenCum, cn.MaKhu ORDER BY cn.MaKhu ASC, cn.MaCum ASC, tn.MaToaNha ASC LIMIT 15";
        return Database::fetchAll($sql, $params);
    }

    /**
     * Lấy danh sách phòng có tỷ lệ lấp đầy <= 50%
     */
    private static function getUnderOccupiedRooms(?string $khu, ?string $cum, ?string $toa): array
    {
        $sql = "
            SELECT 
                p.MaPhong,
                p.SoPhong,
                p.Tang,
                p.SoSVDangO,
                lp.MaLoaiPhong,
                lp.TenLoaiPhong,
                lp.SoGiuongToiDa,
                tn.MaToaNha,
                tn.TenToaNha,
                cn.MaCum,
                cn.TenCum,
                cn.MaKhu,
                ROUND((p.SoSVDangO / lp.SoGiuongToiDa) * 100, 1) AS TyLeLapDay,
                (lp.SoGiuongToiDa - p.SoSVDangO) AS SoGiuongTrong
            FROM Phong p
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            WHERE (p.SoSVDangO / lp.SoGiuongToiDa) <= 0.50
        ";
        $params = [];
        if (!empty($toa)) {
            $sql .= " AND tn.MaToaNha = :toa";
            $params['toa'] = $toa;
        } elseif (!empty($cum)) {
            $sql .= " AND tn.MaCum = :cum";
            $params['cum'] = $cum;
        } elseif (!empty($khu)) {
            $sql .= " AND cn.MaKhu = :khu";
            $params['khu'] = $khu;
        }

        $sql .= " ORDER BY TyLeLapDay ASC, p.SoSVDangO ASC LIMIT 10";
        return Database::fetchAll($sql, $params);
    }

    /**
     * Nhật ký hoạt động vận hành gần đây (Live Activity Feed)
     */
    private static function getRecentActivities(?string $khu, ?string $cum, ?string $toa): array
    {
        $activities = [];

        // 1. Quẹt thẻ barrier gần nhất
        try {
            $barrierLogs = AttendanceDisciplineService::getBarrierLogs($cum, $toa, $khu, '', '', 5, 0);
            foreach ($barrierLogs['logs'] ?? [] as $log) {
                $phongInfo = !empty($log['MaPhong']) ? " - Phòng {$log['MaPhong']} (" . ($log['MaToaNha'] ?? '') . ")" : '';
                $activities[] = [
                    'time' => $log['ThoiGianQuet'] ?? '',
                    'icon' => ($log['HuongDi'] === 'Vào') ? 'fa-arrow-right-to-bracket' : 'fa-arrow-right-from-bracket',
                    'color' => ($log['HuongDi'] === 'Vào') ? '#16a34a' : '#2563eb',
                    'title' => "Sinh viên " . ($log['HoVaTen'] ?? '') . " (" . ($log['MaSinhVien'] ?? '') . ")",
                    'desc' => "Quét thẻ " . ($log['HuongDi'] ?? '') . " tại " . ($log['CongKiemSoat'] ?? '') . $phongInfo,
                    'type' => 'barrier'
                ];
            }
        } catch (\Throwable $e) {}

        // 2. Phiếu bảo trì báo hỏng mới nhất
        try {
            $sqlMaint = "
                SELECT pb.MaPhieu, pb.MoTaHuHong, pb.NgayBao, pb.TrangThaiSua, COALESCE(pb.MaPhong, 'Chung') AS MaPhong, tn.TenToaNha, sv.HoVaTen
                FROM PhieuBaoHong pb
                LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
                LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
                LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
                ORDER BY pb.NgayBao DESC LIMIT 5
            ";
            $maintLogs = Database::fetchAll($sqlMaint);
            foreach ($maintLogs as $m) {
                $activities[] = [
                    'time' => $m['NgayBao'] ?? '',
                    'icon' => 'fa-wrench',
                    'color' => '#d97706',
                    'title' => "Báo hỏng: {$m['MaPhieu']} (Phòng {$m['MaPhong']} - {$m['TenToaNha']})",
                    'desc' => "{$m['HoVaTen']}: " . mb_substr($m['MoTaHuHong'], 0, 60) . '...',
                    'type' => 'maintenance'
                ];
            }
        } catch (\Throwable $e) {}

        // Sắp xếp theo thời gian mới nhất
        usort($activities, fn($a, $b) => strcmp($b['time'], $a['time']));
        return array_slice($activities, 0, 8);
    }

    /**
     * Dữ liệu chuyên biệt dành cho Cán bộ Kế toán (Accounting Dashboard)
     */
    public static function getAccountingDashboardData(): array
    {
        // 1. Tổng hợp doanh thu và công nợ
        $sqlStats = "
            SELECT 
                COUNT(*) AS TongSoHoaDon,
                SUM(CASE WHEN TrangThai = 'Đã thanh toán' THEN SoTienPhaiDong ELSE 0 END) AS DaThu,
                SUM(CASE WHEN TrangThai = 'Chưa thanh toán' THEN SoTienPhaiDong ELSE 0 END) AS ConNo,
                COUNT(CASE WHEN TrangThai = 'Chưa thanh toán' THEN 1 END) AS SoHoaDonChuaThu,
                COUNT(DISTINCT CASE WHEN TrangThai = 'Chưa thanh toán' THEN MaPhong END) AS SoPhongNo
            FROM HoaDon
        ";
        $financeStats = Database::fetch($sqlStats) ?: [];

        // 2. Danh sách Top 10 phòng nợ cước điện nước cao nhất
        $sqlTopDebt = "
            SELECT 
                hd.MaPhong, p.SoPhong, tn.TenToaNha, cn.TenCum,
                COUNT(hd.MaHoaDon) AS SoKyNo,
                SUM(hd.SoTienPhaiDong) AS TongTienNo
            FROM HoaDon hd
            JOIN Phong p ON hd.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE hd.TrangThai = 'Chưa thanh toán'
            GROUP BY hd.MaPhong, p.SoPhong, tn.TenToaNha, cn.TenCum
            ORDER BY TongTienNo DESC
            LIMIT 10
        ";
        $topDebtRooms = Database::fetchAll($sqlTopDebt);

        // 3. Danh sách Đơn Một Cửa xin hoàn phí đang chờ Kế toán duyệt giải ngân
        $sqlRefund = "
            SELECT 
                dt.MaDon, dt.MaSinhVien, dt.LoaiDon, dt.SoTienChenhLechHoan, dt.LyDo, dt.TrangThaiDuyet,
                sv.HoVaTen, sv.SoDienThoai, sv.Email
            FROM DonTuMotCua dt
            JOIN SinhVien sv ON dt.MaSinhVien = sv.MaSinhVien
            WHERE dt.TrangThaiDuyet = 'Chờ duyệt' AND (dt.SoTienChenhLechHoan > 0 OR dt.LoaiDon LIKE '%hoàn%')
            ORDER BY dt.MaDon DESC
            LIMIT 10
        ";
        $pendingRefunds = Database::fetchAll($sqlRefund);

        return [
            'stats' => $financeStats,
            'topDebtRooms' => $topDebtRooms,
            'pendingRefunds' => $pendingRefunds,
        ];
    }

    /**
     * Dữ liệu chuyên biệt dành cho Kỹ thuật viên (Technical Dashboard)
     */
    public static function getTechnicalDashboardData(int $userId, ?string $maCum): array
    {
        // 1. Thống kê công việc kỹ thuật viên
        $sqlStats = "
            SELECT 
                COUNT(*) AS TongPhieuDuocGiao,
                SUM(CASE WHEN TrangThaiSua = 'Đang sửa chữa' THEN 1 ELSE 0 END) AS DangSua,
                SUM(CASE WHEN TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý') THEN 1 ELSE 0 END) AS ChoTiepNhan,
                SUM(CASE WHEN TrangThaiSua = 'Đã hoàn thành' THEN 1 ELSE 0 END) AS DaHoanThanh
            FROM PhieuBaoHong
            WHERE NhanVienKyThuat = :uid OR NhanVienKyThuat IS NULL
        ";
        $techStats = Database::fetch($sqlStats, ['uid' => $userId]) ?: [];

        // 2. Danh sách phiếu bảo trì cần sửa của kỹ thuật viên
        $sqlTickets = "
            SELECT 
                pb.MaPhieu, pb.MoTaHuHong, pb.NgayBao, pb.TrangThaiSua, pb.LoaiSuCo, pb.HinhAnh,
                COALESCE(pb.MaPhong, 'Khu vực chung') AS MaPhong,
                tn.TenToaNha, cn.TenCum,
                sv.HoVaTen AS NguoiBao, sv.SoDienThoai AS SdtNguoiBao,
                tb.TenThietBi
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            WHERE pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý', 'Đang sửa chữa')
              AND (pb.NhanVienKyThuat = :uid OR pb.NhanVienKyThuat IS NULL)
            ORDER BY pb.NgayBao ASC
            LIMIT 15
        ";
        $myTickets = Database::fetchAll($sqlTickets, ['uid' => $userId]);

        return [
            'stats' => $techStats,
            'myTickets' => $myTickets,
        ];
    }
}
