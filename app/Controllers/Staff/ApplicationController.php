<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AutoReleaseService;

/**
 * Controller: Quản lý Hồ sơ Đăng ký nội trú & Xét duyệt 72h
 * View: views/staff/applications.php
 * Phân quyền 4 cấp: Ban Giám đốc (ALL) / Trưởng Khu (KHU) / Trưởng Cụm (CUM) / Trưởng Tòa (TOA)
 * Chặn 100%: Kế toán, Kỹ thuật điện nước, Nhân viên thường, Sinh viên
 */
class ApplicationController extends Controller
{
    /**
     * Xác định phân quyền cán bộ quản lý Xét duyệt 72h (RBAC Scope Matrix)
     */
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));
        $username = strtoupper($user['TenDangNhap'] ?? '');
        $chucVu = mb_strtolower($user['ChucVu'] ?? $user['profile']['ChucVu'] ?? '');

        // 1. CHẶN TUYỆT ĐỐI NHÂN VIÊN KHÔNG PHẢI CÁN BỘ QUẢN LÝ
        $isNonManager = in_array($role, ['KE_TOAN', 'NV_THUONG', 'SINH_VIEN', 'TRUONG_PHONG'], true)
            || str_starts_with($username, 'NV_KT')
            || str_starts_with($username, 'KT')
            || str_contains($chucVu, 'kế toán')
            || str_contains($chucVu, 'kỹ thuật')
            || str_contains($chucVu, 'điện nước');

        $isManager = in_array($role, ['ADMIN', 'QuanTriVien', 'NV_QUAN_LY_KTX', 'TRUONG_KHU', 'TRUONG_CUM', 'TRUONG_TOA'], true);

        if ($isNonManager || !$isManager) {
            return [
                'isAllowed' => false,
                'level' => 'DENIED',
                'levelNumber' => -1,
                'role' => $role,
                'title' => 'Không có quyền truy cập',
                'badgeClass' => 'badge-danger',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canApprove' => false,
                'canReject' => false,
                'canCancel' => false,
                'canScanRelease' => false,
                'availableToas' => [],
                'availableCums' => [],
                'availableKhus' => [],
                'message' => 'Từ chối quyền truy cập: Phân hệ Xét duyệt Đăng ký & Giữ chỗ 72h chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX). Nhân viên Kế toán và Kỹ thuật không có quyền thao tác!'
            ];
        }

        // 2. CẤP 4: Quản lý toàn KTX (Giám đốc / Admin)
        if ($role === 'ADMIN' || $role === 'QuanTriVien' || $role === 'NV_QUAN_LY_KTX') {
            $khus = Database::fetchAll("SELECT MaKhu, TenKhu FROM KhuKTX ORDER BY MaKhu ASC");
            $cums = Database::fetchAll("SELECT MaCum, TenCum, MaKhu FROM CumNha ORDER BY MaKhu ASC, MaCum ASC");
            $toas = Database::fetchAll("
                SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                ORDER BY c.MaKhu ASC, c.MaCum ASC, t.MaToaNha ASC
            ");

            return [
                'isAllowed' => true,
                'level' => 'ALL',
                'levelNumber' => 4,
                'role' => $role,
                'title' => 'Quản lý toàn KTX (Giám đốc / Admin)',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canApprove' => true,
                'canReject' => true,
                'canCancel' => true,
                'canScanRelease' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus
            ];
        }

        // 3. CẤP 3: Trưởng Khu (TK001 -> Khu A, TK002 -> Khu B)
        if ($role === 'TRUONG_KHU' || $cumQuanLy === 'A' || $cumQuanLy === 'B') {
            $maKhu = in_array($cumQuanLy, ['A', 'B']) ? $cumQuanLy : 'A';
            $khus = Database::fetchAll("SELECT MaKhu, TenKhu FROM KhuKTX WHERE MaKhu = :k", ['k' => $maKhu]);
            $cums = Database::fetchAll("SELECT MaCum, TenCum, MaKhu FROM CumNha WHERE MaKhu = :k ORDER BY MaCum ASC", ['k' => $maKhu]);
            $toas = Database::fetchAll("
                SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                WHERE c.MaKhu = :k
                ORDER BY c.MaCum ASC, t.MaToaNha ASC
            ", ['k' => $maKhu]);

            return [
                'isAllowed' => true,
                'level' => 'KHU',
                'levelNumber' => 3,
                'role' => $role,
                'title' => "Trưởng BQL Khu {$maKhu}",
                'badgeClass' => 'badge-info',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canApprove' => true,
                'canReject' => true,
                'canCancel' => true,
                'canScanRelease' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus
            ];
        }

        // 4. CẤP 2: Trưởng Cụm (TC001 -> CUMAG...)
        if ($role === 'TRUONG_CUM' || str_starts_with($cumQuanLy, 'CUM') || in_array($cumQuanLy, ['AG', 'AH', 'BA', 'BB'])) {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("SELECT c.MaCum, c.TenCum, c.MaKhu, k.TenKhu FROM CumNha c JOIN KhuKTX k ON c.MaKhu = k.MaKhu WHERE c.MaCum = :c", ['c' => $maCum]);
            if (!$cumRow) {
                $cumRow = Database::fetch("SELECT c.MaCum, c.TenCum, c.MaKhu, k.TenKhu FROM CumNha c JOIN KhuKTX k ON c.MaKhu = k.MaKhu LIMIT 1");
                $maCum = $cumRow['MaCum'] ?? 'AG';
            }
            $maKhu = $cumRow['MaKhu'] ?? 'A';
            $tenCum = $cumRow['TenCum'] ?? $maCum;

            $cleanCum = preg_replace('/^(Ban Quản lý\s*|Cụm\s*nhà\s*)+/ui', '', $tenCum);
            $cumTitle = "Trưởng BQL Cụm " . (!empty($cleanCum) ? $cleanCum : $tenCum);

            $toas = Database::fetchAll("
                SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                WHERE t.MaCum = :c
                ORDER BY t.MaToaNha ASC
            ", ['c' => $maCum]);

            return [
                'isAllowed' => true,
                'level' => 'CUM',
                'levelNumber' => 2,
                'role' => $role,
                'title' => $cumTitle,
                'badgeClass' => 'badge-warning',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'canApprove' => true,
                'canReject' => true,
                'canCancel' => true,
                'canScanRelease' => true,
                'availableToas' => $toas,
                'availableCums' => [$cumRow],
                'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $cumRow['TenKhu'] ?? $maKhu]]
            ];
        }

        // 5. CẤP 1: Trưởng Tòa (TT001 -> A01...)
        $maToa = !empty($cumQuanLy) ? $cumQuanLy : 'A01';
        $toaRow = Database::fetch("
            SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
            FROM ToaNha t
            JOIN CumNha c ON t.MaCum = c.MaCum
            JOIN KhuKTX k ON c.MaKhu = k.MaKhu
            WHERE t.MaToaNha = :t
        ", ['t' => $maToa]);

        if (!$toaRow) {
            $toaRow = Database::fetch("
                SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                ORDER BY t.MaToaNha ASC LIMIT 1
            ");
            $maToa = $toaRow['MaToaNha'] ?? 'A01';
        }

        $rawToaName = $toaRow['TenToaNha'] ?? $maToa;
        $cleanToaName = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', trim($rawToaName));
        $displayToa = !empty($cleanToaName) ? $cleanToaName : $rawToaName;

        return [
            'isAllowed' => true,
            'level' => 'TOA',
            'levelNumber' => 1,
            'role' => $role,
            'title' => "Trưởng tòa " . $displayToa,
            'badgeClass' => 'badge-success',
            'maKhu' => $toaRow['MaKhu'] ?? 'A',
            'maCum' => $toaRow['MaCum'] ?? 'AG',
            'maToa' => $maToa,
            'canApprove' => true,
            'canReject' => true,
            'canCancel' => true,
            'canScanRelease' => false,
            'availableToas' => [$toaRow],
            'availableCums' => [['MaCum' => $toaRow['MaCum'], 'TenCum' => $toaRow['TenCum'] ?? $toaRow['MaCum'], 'MaKhu' => $toaRow['MaKhu']]],
            'availableKhus' => [['MaKhu' => $toaRow['MaKhu'], 'TenKhu' => $toaRow['TenKhu'] ?? $toaRow['MaKhu']]]
        ];
    }

    /**
     * Danh sách hồ sơ đăng ký & duyệt chỗ 72h (Hỗ trợ Cascading Filters & Quick Search)
     */
    public function applications(): void
    {
        $user = Auth::user() ?? [];
        $scope = $this->getStaffScope($user);

        // NẾU KHÔNG CÓ QUYỀN QUẢN LÝ: HIỂN THỊ MÀN HÌNH CHẶN 100%
        if (!$scope['isAllowed']) {
            $this->view('staff.applications', [
                'user' => $user,
                'isAllowed' => false,
                'scope' => $scope,
                'roleName' => $user['VaiTro'] ?? $user['MaVaiTro'] ?? 'Nhân viên',
                'pendingApplications' => [],
                'active72hApplications' => [],
                'historyApplications' => [],
                'mienGiamList' => [],
                'stats' => ['pending' => 0, 'active' => 0, 'expired' => 0, 'completed' => 0, 'miengiam' => 0],
                'filters' => ['khu' => '', 'cum' => '', 'toa' => '', 'status' => '', 'search' => '', 'tab' => 'cho_duyet'],
                'availableKhus' => [],
                'availableCums' => [],
                'availableToas' => [],
                'flashSuccess' => Session::flash('success'),
                'flashError' => Session::flash('error'),
                'flashWarning' => Session::flash('warning')
            ]);
            return;
        }

        $request = new Request();
        $filterKhu = trim((string)$request->input('khu', ''));
        $filterCum = trim((string)$request->input('cum', ''));
        $filterToa = trim((string)$request->input('toa', ''));
        $filterStatus = trim((string)$request->input('status', ''));
        $search = trim((string)$request->input('search', ''));
        $activeTab = trim((string)$request->input('tab', 'cho_duyet'));

        // Áp đặt phạm vi bảo mật theo cấp bậc cán bộ
        if ($scope['level'] === 'KHU') {
            $filterKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'CUM') {
            $filterKhu = $scope['maKhu'];
            $filterCum = $scope['maCum'];
        } elseif ($scope['level'] === 'TOA') {
            $filterKhu = $scope['maKhu'];
            $filterCum = $scope['maCum'];
            $filterToa = $scope['maToa'];
        }

        // Lọc danh sách cụm và tòa cascading tương thích
        $availableKhus = $scope['availableKhus'] ?? [];
        $availableCums = $scope['availableCums'] ?? [];
        $availableToas = $scope['availableToas'] ?? [];

        if (!empty($filterKhu)) {
            $availableCums = array_values(array_filter($availableCums, function($c) use ($filterKhu) {
                return ($c['MaKhu'] ?? '') === $filterKhu;
            }));
            $availableToas = array_values(array_filter($availableToas, function($t) use ($filterKhu) {
                return ($t['MaKhu'] ?? '') === $filterKhu;
            }));
        }

        if (!empty($filterCum)) {
            $availableToas = array_values(array_filter($availableToas, function($t) use ($filterCum) {
                return ($t['MaCum'] ?? '') === $filterCum;
            }));
        }

        // Xây dựng điều kiện lọc chung
        $scopeConditions = [];
        $queryParams = [];

        if (!empty($filterToa)) {
            $scopeConditions[] = "p.MaToaNha = :f_toa";
            $queryParams['f_toa'] = $filterToa;
        } elseif (!empty($filterCum)) {
            $scopeConditions[] = "tn.MaCum = :f_cum";
            $queryParams['f_cum'] = $filterCum;
        } elseif (!empty($filterKhu)) {
            $scopeConditions[] = "c.MaKhu = :f_khu";
            $queryParams['f_khu'] = $filterKhu;
        }

        $searchSql = "";
        if (!empty($search)) {
            $searchSql = " AND (hs.MaHoSo LIKE :kw1 OR sv.HoVaTen LIKE :kw2 OR sv.MSSVTaiTruong LIKE :kw3 OR sv.SoDienThoai LIKE :kw4 OR sv.SoCCCD LIKE :kw5 OR p.SoPhong LIKE :kw6)";
            $kw = "%{$search}%";
            $queryParams['kw1'] = $kw;
            $queryParams['kw2'] = $kw;
            $queryParams['kw3'] = $kw;
            $queryParams['kw4'] = $kw;
            $queryParams['kw5'] = $kw;
            $queryParams['kw6'] = $kw;
        }

        $baseScopeWhere = !empty($scopeConditions) ? " AND " . implode(" AND ", $scopeConditions) : "";

        $pendingApplications = [];
        $active72hApplications = [];
        $historyApplications = [];
        $mienGiamList = [];

        try {
            // 1. Danh sách hồ sơ Chờ duyệt mới (Cần duyệt trong 24h)
            $sqlPending = "
                SELECT hs.*, sv.HoVaTen, sv.GioiTinh, sv.SoCCCD, sv.SoDienThoai, sv.MSSVTaiTruong,
                       t.TenTruong, p.SoPhong, tn.TenToaNha, tn.MaToaNha, tn.MaCum, c.TenCum, c.MaKhu, k.TenKhu,
                       g.KyHieuGiuong, lp.TenLoaiPhong,
                       (lp.DonGiaLuuTruChuan + lp.DonGiaDichVuTangThem) AS DonGiaThang
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                WHERE hs.TrangThaiHoSo = 'Chờ duyệt' {$baseScopeWhere} {$searchSql}
                ORDER BY hs.ThoiGianNop ASC
            ";
            $pendingApplications = Database::fetchAll($sqlPending, $queryParams);

            // 2. Danh sách hồ sơ đang trong hạn 72h (Chờ thanh toán)
            $sqlActive = "
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, sv.SoDienThoai, p.SoPhong, tn.TenToaNha, tn.MaToaNha, tn.MaCum, c.TenCum, c.MaKhu, k.TenKhu,
                       g.KyHieuGiuong, lp.TenLoaiPhong,
                       hd.SoTienPhaiDong, hd.TrangThai AS TrangThaiHoaDon,
                       TIMESTAMPDIFF(MINUTE, NOW(), hs.HanChotThanhToan) AS SoPhutConLai,
                       TIMESTAMPDIFF(HOUR, NOW(), hs.HanChotThanhToan) AS SoGioConLai
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                LEFT JOIN HoaDon hd ON hd.MaHoaDon = CONCAT('HD-', hs.MaHoSo)
                WHERE hs.TrangThaiHoSo = 'Chờ thanh toán' {$baseScopeWhere} {$searchSql}
                ORDER BY hs.HanChotThanhToan ASC
            ";
            $active72hApplications = Database::fetchAll($sqlActive, $queryParams);

            // 3. Lịch sử hồ sơ đã xử lý (Đã duyệt, Đã thanh toán, Quá hạn 72h, Đã từ chối)
            $historyStatusSql = " AND hs.TrangThaiHoSo IN ('Đã duyệt', 'Đã từ chối', 'Từ chối', 'Hết hạn - Đã hủy chỗ', 'Hết hạn thanh toán', 'Đã hủy')";
            if (!empty($filterStatus) && $filterStatus !== 'ALL') {
                $historyStatusSql = " AND hs.TrangThaiHoSo = :f_stat";
                $queryParams['f_stat'] = $filterStatus;
            }

            $sqlHistory = "
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, sv.SoDienThoai, p.SoPhong, tn.TenToaNha, tn.MaToaNha, tn.MaCum, c.TenCum, c.MaKhu, k.TenKhu,
                       g.KyHieuGiuong, lp.TenLoaiPhong,
                       hd.SoTienPhaiDong, hd.TrangThai AS TrangThaiHoaDon
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                LEFT JOIN HoaDon hd ON hd.MaHoaDon = CONCAT('HD-', hs.MaHoSo)
                WHERE 1=1 {$historyStatusSql} {$baseScopeWhere} {$searchSql}
                ORDER BY COALESCE(hs.ThoiGianDuyet, hs.ThoiGianNop) DESC
                LIMIT 100
            ";
            $historyApplications = Database::fetchAll($sqlHistory, $queryParams);

            // 4. Danh sách hồ sơ thẩm định Miễn giảm nội trú
            $mgScopeWhere = "";
            $mgParams = [];
            if (!empty($filterToa)) {
                $mgScopeWhere .= " AND p.MaToaNha = :mg_toa";
                $mgParams['mg_toa'] = $filterToa;
            } elseif (!empty($filterCum)) {
                $mgScopeWhere .= " AND tn.MaCum = :mg_cum";
                $mgParams['mg_cum'] = $filterCum;
            } elseif (!empty($filterKhu)) {
                $mgScopeWhere .= " AND c.MaKhu = :mg_khu";
                $mgParams['mg_khu'] = $filterKhu;
            }
            if (!empty($search)) {
                $mgScopeWhere .= " AND (mg.MaSinhVien LIKE :mg_kw OR sv.HoVaTen LIKE :mg_kw OR sv.MSSVTaiTruong LIKE :mg_kw)";
                $mgParams['mg_kw'] = "%{$search}%";
            }

            $sqlMienGiam = "
                SELECT mg.*, sv.HoVaTen, sv.MSSVTaiTruong, sv.SoDienThoai, tdh.TenTruong,
                       p.SoPhong, tn.TenToaNha, g.KyHieuGiuong
                FROM HoSoMienGiam mg
                JOIN SinhVien sv ON mg.MaSinhVien = sv.MaSinhVien
                LEFT JOIN TruongDaiHoc tdh ON sv.MaTruong = tdh.MaTruong
                LEFT JOIN HopDongNoiTru hd ON mg.MaHopDong = hd.MaHopDong
                LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
                LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN CumNha c ON tn.MaCum = c.MaCum
                WHERE 1=1 {$mgScopeWhere}
                ORDER BY mg.MaMienGiam DESC
            ";
            $mienGiamList = Database::fetchAll($sqlMienGiam, $mgParams);

            // 5. Thống kê KPI Nhanh
            $statParams = [];
            $statScopeWhere = "";
            if (!empty($filterToa)) {
                $statScopeWhere .= " AND p.MaToaNha = :st_toa";
                $statParams['st_toa'] = $filterToa;
            } elseif (!empty($filterCum)) {
                $statScopeWhere .= " AND tn.MaCum = :st_cum";
                $statParams['st_cum'] = $filterCum;
            } elseif (!empty($filterKhu)) {
                $statScopeWhere .= " AND c.MaKhu = :st_khu";
                $statParams['st_khu'] = $filterKhu;
            }

            $statRow = Database::fetch("
                SELECT 
                    SUM(CASE WHEN hs.TrangThaiHoSo = 'Chờ duyệt' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN hs.TrangThaiHoSo = 'Chờ thanh toán' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN hs.TrangThaiHoSo IN ('Hết hạn - Đã hủy chỗ', 'Hết hạn thanh toán') THEN 1 ELSE 0 END) AS expired,
                    SUM(CASE WHEN hs.TrangThaiHoSo = 'Đã duyệt' THEN 1 ELSE 0 END) AS completed
                FROM HoSoDangKy hs
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                WHERE 1=1 {$statScopeWhere}
            ", $statParams);

            $stats = [
                'pending' => (int)($statRow['pending'] ?? 0),
                'active' => (int)($statRow['active'] ?? 0),
                'expired' => (int)($statRow['expired'] ?? 0),
                'completed' => (int)($statRow['completed'] ?? 0),
                'miengiam' => count($mienGiamList)
            ];

        } catch (\Throwable $e) {
            $stats = ['pending' => 0, 'active' => 0, 'expired' => 0, 'completed' => 0, 'miengiam' => 0];
        }

        $this->view('staff.applications', [
            'user' => $user,
            'isAllowed' => true,
            'scope' => $scope,
            'roleName' => $user['VaiTro'] ?? $user['MaVaiTro'] ?? 'Cán bộ',
            'pendingApplications' => $pendingApplications,
            'active72hApplications' => $active72hApplications,
            'historyApplications' => $historyApplications,
            'mienGiamList' => $mienGiamList,
            'stats' => $stats,
            'filters' => [
                'khu' => $filterKhu,
                'cum' => $filterCum,
                'toa' => $filterToa,
                'status' => $filterStatus,
                'search' => $search,
                'tab' => $activeTab
            ],
            'availableKhus' => $availableKhus,
            'availableCums' => $availableCums,
            'availableToas' => $availableToas,
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
            'flashWarning' => Session::flash('warning')
        ]);
    }

    /**
     * Phê duyệt hồ sơ đăng ký và cấp quyền giữ chỗ 72h
     */
    public function approveApplication(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('staff/applications');
            return;
        }

        $request = new Request();
        $maHoSo = trim((string)$request->input('maHoSo', ''));
        $userId = Auth::id() ?? 3;

        if (empty($maHoSo)) {
            Session::flash('error', 'Mã hồ sơ không hợp lệ.');
            $this->redirect('staff/applications');
            return;
        }

        $result = AutoReleaseService::approveApplication($maHoSo, $userId, 72);

        if ($result['success']) {
            $hanChotFmt = date('d/m/Y H:i', strtotime($result['hanChotThanhToan']));
            Session::flash('success', "Đã phê duyệt giữ chỗ 72h cho hồ sơ {$maHoSo}. Hạn chót thanh toán: {$hanChotFmt}. Hóa đơn lệ phí đã được tự động khởi tạo.");
        } else {
            Session::flash('error', $result['message'] ?? 'Phê duyệt hồ sơ thất bại.');
        }

        $this->redirect('staff/applications?tab=dang_giu_cho');
    }

    /**
     * Từ chối hồ sơ đăng ký nội trú & giải phóng giường
     */
    public function rejectApplication(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('staff/applications');
            return;
        }

        $request = new Request();
        $maHoSo = trim((string)$request->input('maHoSo', ''));
        $lyDo = trim((string)$request->input('lyDo', ''));
        $userId = Auth::id() ?? 3;

        if (empty($maHoSo)) {
            Session::flash('error', 'Mã hồ sơ không hợp lệ.');
            $this->redirect('staff/applications');
            return;
        }

        $result = AutoReleaseService::rejectApplication($maHoSo, $userId, $lyDo);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message'] ?? 'Từ chối hồ sơ thất bại.');
        }

        $this->redirect('staff/applications');
    }

    /**
     * Cán bộ kích hoạt quét thu hồi giường quá hạn 72h tức thời (Background Scan)
     */
    public function scanRelease72h(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('staff/applications');
            return;
        }

        $result = AutoReleaseService::scanAndReleaseExpiredBeds();
        $totalReleased = (int)($result['total_released'] ?? 0);
        $totalScanned = (int)($result['total_scanned'] ?? 0);

        if ($totalReleased > 0) {
            Session::flash('success', "Đã hoàn tất quét đối soát 72h: Tìm thấy {$totalScanned} hồ sơ và tự động giải phóng {$totalReleased} vị trí giường quá hạn thanh toán về lại quỹ phòng trống!");
        } else {
            Session::flash('success', "Đã quét đối soát 72h hệ thống: Hiện không có hồ sơ nào quá hạn thanh toán cần thu hồi.");
        }

        $this->redirect('staff/applications?tab=dang_giu_cho');
    }

    /**
     * Phê duyệt hồ sơ miễn giảm nội trú
     */
    public function approveMienGiam(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('staff/applications');
            return;
        }

        $request = new Request();
        $maMienGiam = (int)$request->input('maMienGiam', 0);

        if ($maMienGiam <= 0) {
            Session::flash('error', 'Mã hồ sơ miễn giảm không hợp lệ.');
            $this->redirect('staff/applications?tab=mien_giam');
            return;
        }

        try {
            Database::execute("
                UPDATE HoSoMienGiam 
                SET TrangThaiDuyet = 'Đã duyệt' 
                WHERE MaMienGiam = :id
            ", ['id' => $maMienGiam]);

            Session::flash('success', "Đã phê duyệt hồ sơ miễn giảm nội trú #{$maMienGiam} thành công.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi duyệt hồ sơ miễn giảm: ' . $e->getMessage());
        }

        $this->redirect('staff/applications?tab=mien_giam');
    }

    /**
     * Từ chối hồ sơ miễn giảm nội trú
     */
    public function rejectMienGiam(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('staff/applications');
            return;
        }

        $request = new Request();
        $maMienGiam = (int)$request->input('maMienGiam', 0);

        if ($maMienGiam <= 0) {
            Session::flash('error', 'Mã hồ sơ miễn giảm không hợp lệ.');
            $this->redirect('staff/applications?tab=mien_giam');
            return;
        }

        try {
            Database::execute("
                UPDATE HoSoMienGiam 
                SET TrangThaiDuyet = 'Từ chối' 
                WHERE MaMienGiam = :id
            ", ['id' => $maMienGiam]);

            Session::flash('success', "Đã từ chối hồ sơ miễn giảm nội trú #{$maMienGiam}.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi từ chối hồ sơ miễn giảm: ' . $e->getMessage());
        }

        $this->redirect('staff/applications?tab=mien_giam');
    }
}
