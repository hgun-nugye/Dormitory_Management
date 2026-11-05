<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Controller: Quản lý Phòng & Giường nội trú KTX
 * Phân quyền: DÀNH RIÊNG CHO TRƯỞNG TÒA NHÀ (TRUONG_TOA)
 * View: views/staff/rooms.php
 * Nghiệp vụ:
 * 1. Danh sách phòng, phân trang, lọc tầng/loại/trạng thái, tìm kiếm
 * 2. Chi tiết giường, hợp đồng sinh viên, thẻ cư trú Mẫu CT01
 * 3. Thêm/Sửa/Xóa phòng và giường trong phạm vi Tòa được phân công
 * 4. Khóa bảo trì / Mở khóa phòng & giường
 * 5. Xác nhận sinh viên nhận phòng thực tế (check-in)
 * 6. Chỉ định hoặc gỡ Trưởng phòng
 * 7. Xóa sinh viên khỏi giường (Hủy lưu trú / Thanh lý hợp đồng / Trả giường)
 * 8. Xuất file Excel/CSV danh sách phòng & giường
 * 9. Nhập file CSV danh sách phòng, giường và sinh viên lưu trú
 */
class RoomController extends Controller
{
    /**
     * Xác định phân quyền cán bộ quản lý Sơ đồ phòng & Giường
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

        // 1. CHẶN TUYỆT ĐỐI NHÂN VIÊN KHÔNG PHẢI CÁN BỘ QUẢN LÝ (Kế toán, Kỹ thuật điện nước, Nhân viên thường, Sinh viên)
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
                'canCrudPhong' => false,
                'canUpdateToa' => false,
                'canCrudToa' => false,
                'canCrudCum' => false,
                'canCrudKhu' => false,
                'availableToas' => [],
                'availableCums' => [],
                'availableKhus' => [],
                'message' => 'Từ chối quyền truy cập: Phân hệ Sơ đồ phòng và Hạ tầng KTX chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX). Nhân viên Kế toán và Kỹ thuật không có quyền thao tác!'
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
                'canCrudPhong' => true,
                'canUpdateToa' => true,
                'canCrudToa' => true,
                'canCrudCum' => true,
                'canCrudKhu' => true,
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
                'canCrudPhong' => true,
                'canUpdateToa' => true,
                'canCrudToa' => true,
                'canCrudCum' => true,
                'canCrudKhu' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus
            ];
        }

        // 4. CẤP 2: Trưởng Cụm (CUMAG -> AG...)
        if ($role === 'TRUONG_CUM') {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("SELECT c.MaCum, c.TenCum, c.MaKhu, k.TenKhu FROM CumNha c JOIN KhuKTX k ON c.MaKhu = k.MaKhu WHERE c.MaCum = :c", ['c' => $maCum]);
            $maKhu = $cumRow['MaKhu'] ?? 'A';
            $tenCum = $cumRow['TenCum'] ?? $maCum;

            // Làm sạch nhãn cụm: tránh lặp từ "Trưởng BQL Cụm nhà Ban Quản lý Cụm nhà A.G"
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
                'canCrudPhong' => true,
                'canUpdateToa' => true,
                'canCrudToa' => true,
                'canCrudCum' => false,
                'canCrudKhu' => false,
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

        return [
            'isAllowed' => true,
            'level' => 'TOA',
            'levelNumber' => 1,
            'role' => $role,
            'title' => "Trưởng tòa nhà " . ($toaRow['TenToaNha'] ?? $maToa),
            'badgeClass' => 'badge-success',
            'maKhu' => $toaRow['MaKhu'] ?? 'A',
            'maCum' => $toaRow['MaCum'] ?? 'AG',
            'maToa' => $maToa,
            'canCrudPhong' => true,
            'canUpdateToa' => true,
            'canCrudToa' => false,
            'canCrudCum' => false,
            'canCrudKhu' => false,
            'availableToas' => [$toaRow],
            'availableCums' => [['MaCum' => $toaRow['MaCum'], 'TenCum' => $toaRow['TenCum']]],
            'availableKhus' => [['MaKhu' => $toaRow['MaKhu'], 'TenKhu' => $toaRow['TenKhu']]]
        ];
    }

    /**
     * Xác thực quyền quản trị phòng ở và đối soát Tòa nhà hợp lệ
     */
    private function authorizeRoomAction(string $targetToaOrRoom = ''): array
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed'] || empty($scope['canCrudPhong'])) {
            Session::flash('error', 'Từ chối quyền truy cập: Phân hệ Sơ đồ phòng và Hạ tầng KTX chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX). Nhân viên Kế toán và Kỹ thuật không có quyền thao tác!');
            $this->redirect('staff/rooms');
            exit;
        }

        $validToas = array_column($scope['availableToas'], 'MaToaNha');

        if (!empty($targetToaOrRoom)) {
            $maToa = $targetToaOrRoom;
            if (str_contains($targetToaOrRoom, '.')) {
                $p = Database::fetch("SELECT MaToaNha FROM Phong WHERE MaPhong = :mp", ['mp' => $targetToaOrRoom]);
                $maToa = $p['MaToaNha'] ?? '';
            }

            if (!empty($maToa) && !in_array($maToa, $validToas, true)) {
                Session::flash('error', 'Từ chối quyền: Bạn không có quyền thao tác trên Tòa nhà ngoài phạm vi phân công!');
                $this->redirect('staff/rooms');
                exit;
            }
        }

        return $scope;
    }

    /**
     * Màn hình Sơ đồ Phòng & Giường nội trú KTX
     */
    public function rooms(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        // Chặn tuyệt đối nhân viên không phải cán bộ quản lý (Kế toán, Thợ kỹ thuật, Sinh viên)
        if (!$scope['isAllowed']) {
            $this->view('staff.rooms', [
                'user' => $user,
                'scope' => $scope,
                'isAllowed' => false,
                'roleName' => $user['VaiTro'] ?? $scope['role'],
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
        $filterTang = max(0, (int)$request->input('tang', 0));
        $filterLoaiPhong = trim((string)$request->input('loaiPhong', ''));
        $filterTrangThai = trim((string)$request->input('trangThai', ''));
        $searchKeyword = trim((string)$request->input('search', ''));

        // Cascading filter options theo quyền hạn
        $availableKhus = $scope['availableKhus'] ?? [];
        $availableCums = $scope['availableCums'] ?? [];
        $availableToas = $scope['availableToas'] ?? [];

        // Lọc danh sách Cụm theo Khu đã chọn (nếu có)
        if (!empty($filterKhu)) {
            $filteredCums = array_filter($availableCums, fn($c) => ($c['MaKhu'] ?? '') === $filterKhu);
            if (!empty($filteredCums)) {
                $availableCums = array_values($filteredCums);
            }
        }

        // Lọc danh sách Tòa theo Cụm đã chọn (nếu có)
        if (!empty($filterCum)) {
            $filteredToas = array_filter($availableToas, fn($t) => ($t['MaCum'] ?? '') === $filterCum);
            if (!empty($filteredToas)) {
                $availableToas = array_values($filteredToas);
            }
        }

        // Xác định Tòa nhà mục tiêu hiển thị
        $validToaCodes = array_values(array_unique(array_filter(array_column($availableToas, 'MaToaNha'))));
        $isAllToas = empty($filterToa) && ($scope['level'] !== 'TOA');

        $baseParams = [];
        if ($isAllToas) {
            $maToa = '';
            $inList = !empty($validToaCodes) ? $validToaCodes : ['__NONE__'];
            $inPlaceholders = [];
            foreach ($inList as $idx => $code) {
                $phKey = 'tcode_' . $idx;
                $inPlaceholders[] = ':' . $phKey;
                $baseParams[$phKey] = $code;
            }
            $toaCondition = "p.MaToaNha IN (" . implode(',', $inPlaceholders) . ")";

            $maxTang = 1;
            foreach ($availableToas as $toaItem) {
                if ((int)($toaItem['TongSoTang'] ?? 0) > $maxTang) {
                    $maxTang = (int)$toaItem['TongSoTang'];
                }
            }
            $toaInfo = [
                'MaToaNha' => '',
                'TenToaNha' => 'Tất cả Tòa nhà (' . count($validToaCodes) . ' tòa)',
                'TongSoTang' => $maxTang,
                'GioiTinhQuyDinh' => 'Nam / Nữ',
                'TrangThaiToaNha' => 'Đang hoạt động',
                'TenCum' => !empty($filterCum) ? ($availableCums[0]['TenCum'] ?? $filterCum) : 'Tất cả Cụm',
                'TenKhu' => !empty($filterKhu) ? ($availableKhus[0]['TenKhu'] ?? $filterKhu) : 'Tất cả Khu'
            ];
        } else {
            if (!empty($filterToa) && in_array($filterToa, $validToaCodes, true)) {
                $maToa = $filterToa;
            } else {
                $maToa = $validToaCodes[0] ?? ($scope['maToa'] ?? 'A01');
            }

            $toaCondition = "p.MaToaNha = :targetToa";
            $baseParams['targetToa'] = $maToa;

            $toaInfo = Database::fetch("
                SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                WHERE t.MaToaNha = :toa
            ", ['toa' => $maToa]);

            if (!$toaInfo) {
                $toaInfo = Database::fetch("
                    SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    ORDER BY t.MaToaNha ASC LIMIT 1
                ");
                $maToa = $toaInfo['MaToaNha'] ?? 'A01';
                $baseParams['targetToa'] = $maToa;
            }
        }

        // Danh sách phòng thuộc tòa
        $phongSql = "
            SELECT p.*,
                   t.TenToaNha,
                   lp.TenLoaiPhong, lp.SoGiuongToiDa, lp.DonGiaLuuTruChuan, lp.DonGiaDichVuTangThem,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong) AS TongGiuong,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Trống') AS SoGiuongTrong,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Khóa bảo trì') AS SoGiuongBaoTri,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Đã có người ở') AS SoGiuongCoNguoi,
                   sv.HoVaTen AS TenTruongPhong
            FROM Phong p
            JOIN ToaNha t ON p.MaToaNha = t.MaToaNha
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            LEFT JOIN SinhVien sv ON p.TruongPhong = sv.MaSinhVien
            WHERE {$toaCondition}
        ";
        $phongParams = $baseParams;

        if ($filterTang > 0) {
            $phongSql .= " AND p.Tang = :tang";
            $phongParams['tang'] = $filterTang;
        }
        if (!empty($filterLoaiPhong)) {
            $phongSql .= " AND p.MaLoaiPhong = :lp";
            $phongParams['lp'] = $filterLoaiPhong;
        }
        if (!empty($filterTrangThai)) {
            $phongSql .= " AND p.TrangThaiPhong = :st";
            $phongParams['st'] = $filterTrangThai;
        }
        if (!empty($searchKeyword)) {
            $phongSql .= " AND (p.SoPhong LIKE :kw1 OR p.MaPhong LIKE :kw2 OR p.MaPhong IN (
                SELECT g2.MaPhong FROM Giuong g2
                JOIN HopDongNoiTru hd2 ON g2.MaGiuong = hd2.MaGiuong AND hd2.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                JOIN SinhVien sv2 ON hd2.MaSinhVien = sv2.MaSinhVien
                WHERE sv2.HoVaTen LIKE :kw3 OR sv2.MSSVTaiTruong LIKE :kw4 OR sv2.SoCCCD LIKE :kw5 OR sv2.SoDienThoai LIKE :kw6
            ))";
            $kwVal = "%{$searchKeyword}%";
            $phongParams['kw1'] = $kwVal;
            $phongParams['kw2'] = $kwVal;
            $phongParams['kw3'] = $kwVal;
            $phongParams['kw4'] = $kwVal;
            $phongParams['kw5'] = $kwVal;
            $phongParams['kw6'] = $kwVal;
        }

        $phongSql .= " ORDER BY p.MaToaNha ASC, p.Tang ASC, p.SoPhong ASC";
        $phongList = Database::fetchAll($phongSql, $phongParams);

        // Danh sách giường và thông tin cư trú
        $giuongSql = "
            SELECT g.*,
                   hd.MaHopDong, hd.MaSinhVien, hd.NgayNhanPhong, hd.DaNopMauCT01, hd.TrangThaiHopDong,
                   sv.MSSVTaiTruong, sv.HoVaTen, sv.GioiTinh, sv.SoCCCD, sv.SoDienThoai, sv.Email,
                   tdh.TenTruong
            FROM Giuong g
            JOIN Phong p ON g.MaPhong = p.MaPhong
            LEFT JOIN HopDongNoiTru hd ON hd.MaHopDong = (
                SELECT hd2.MaHopDong FROM HopDongNoiTru hd2 
                WHERE hd2.MaGiuong = g.MaGiuong AND hd2.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                ORDER BY hd2.NgayNhanPhong DESC, hd2.MaHopDong DESC LIMIT 1
            )
            LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
            LEFT JOIN TruongDaiHoc tdh ON sv.MaTruong = tdh.MaTruong
            WHERE {$toaCondition}
            ORDER BY g.MaPhong ASC, g.KyHieuGiuong ASC
        ";
        $giuongList = Database::fetchAll($giuongSql, $baseParams);

        $giuongByPhong = [];
        foreach ($giuongList as $g) {
            $giuongByPhong[$g['MaPhong']][] = $g;
        }

        // Thống kê nhanh toàn tòa
        $statsSql = "
            SELECT 
                COUNT(DISTINCT p.MaPhong) AS TongPhong,
                SUM(CASE WHEN p.TrangThaiPhong != 'Khóa bảo trì' THEN 1 ELSE 0 END) AS PhongHoatDong,
                SUM(CASE WHEN p.TrangThaiPhong = 'Khóa bảo trì' THEN 1 ELSE 0 END) AS PhongBaoTri,
                COUNT(g.MaGiuong) AS TongGiuong,
                SUM(CASE WHEN g.TrangThaiGiuong = 'Trống' THEN 1 ELSE 0 END) AS GiuongTrong,
                SUM(CASE WHEN g.TrangThaiGiuong = 'Đã có người ở' THEN 1 ELSE 0 END) AS GiuongDangO,
                SUM(CASE WHEN g.TrangThaiGiuong = 'Khóa bảo trì' THEN 1 ELSE 0 END) AS GiuongBaoTri
            FROM Phong p
            LEFT JOIN Giuong g ON p.MaPhong = g.MaPhong
            WHERE {$toaCondition}
        ";
        $stats = Database::fetch($statsSql, $baseParams);

        $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY SoGiuongToiDa ASC");

        $this->view('staff.rooms', [
            'user' => $user,
            'scope' => $scope,
            'isAllowed' => true,
            'isTruongToa' => ($scope['level'] === 'TOA'),
            'isAllToas' => $isAllToas,
            'canCrudPhong' => $scope['canCrudPhong'] ?? true,
            'canUpdateToa' => $scope['canUpdateToa'] ?? true,
            'canCrudToa' => $scope['canCrudToa'] ?? false,
            'toaInfo' => $toaInfo,
            'maToa' => $maToa,
            'filterToa' => $filterToa,
            'phongList' => $phongList,
            'giuongByPhong' => $giuongByPhong,
            'stats' => $stats ?: [
                'TongPhong' => 0,
                'PhongHoatDong' => 0,
                'PhongBaoTri' => 0,
                'TongGiuong' => 0,
                'GiuongTrong' => 0,
                'GiuongDangO' => 0,
                'GiuongBaoTri' => 0
            ],
            'loaiPhongList' => $loaiPhongList,
            'availableKhus' => $availableKhus,
            'availableCums' => $availableCums,
            'availableToas' => $availableToas,
            'filters' => [
                'khu' => $filterKhu,
                'cum' => $filterCum,
                'toa' => $filterToa,
                'tang' => $filterTang,
                'loaiPhong' => $filterLoaiPhong,
                'trangThai' => $filterTrangThai,
                'search' => $searchKeyword
            ],
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
            'flashWarning' => Session::flash('warning')
        ]);
    }

    /**
     * Cập nhật thông tin Tòa nhà (RU Tòa nhà cho Trưởng Tòa, CRUD cho Cấp trên)
     */
    public function updateToaInfo(): void
    {
        $scope = $this->getStaffScope();
        if (!$scope['isAllowed'] || empty($scope['canUpdateToa'])) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền chỉnh sửa thông tin Tòa nhà!');
            $this->redirect('staff/rooms');
            return;
        }

        $request = new Request();
        $maToa = trim((string)$request->input('maToa', ''));
        $tenToa = trim((string)$request->input('tenToaNha', ''));
        $gioiTinh = trim((string)$request->input('gioiTinhQuyDinh', 'Nam'));
        $trangThai = trim((string)$request->input('trangThaiToaNha', 'Đang hoạt động'));

        $validToas = array_column($scope['availableToas'], 'MaToaNha');
        if (!in_array($maToa, $validToas, true)) {
            Session::flash('error', 'Từ chối quyền: Tòa nhà không thuộc phạm vi quản lý của bạn!');
            $this->redirect('staff/rooms');
            return;
        }

        try {
            Database::execute("
                UPDATE ToaNha 
                SET TenToaNha = :ten, GioiTinhQuyDinh = :gt, TrangThaiToaNha = :tt 
                WHERE MaToaNha = :t
            ", [
                'ten' => $tenToa,
                'gt' => $gioiTinh,
                'tt' => $trangThai,
                't' => $maToa
            ]);
            Session::flash('success', "Cập nhật thông tin Tòa nhà {$maToa} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', "Lỗi cập nhật Tòa nhà: " . $e->getMessage());
        }

        $this->redirect("staff/rooms?toa=" . urlencode($maToa));
    }


    public function toggleBedMaintenance(): void
    {
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $action = trim((string)$request->input('action', 'lock'));

        if (empty($maGiuong)) {
            Session::flash('error', 'Mã giường không hợp lệ.');
            $this->redirect('staff/rooms');
            return;
        }

        $g = Database::fetch("SELECT g.TrangThaiGiuong, p.MaToaNha FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong WHERE g.MaGiuong = :g", ['g' => $maGiuong]);
        if (!$g) {
            Session::flash('error', 'Không tìm thấy giường.');
            $this->redirect('staff/rooms');
            return;
        }

        $this->authorizeRoomAction($g['MaToaNha']);
        $maToa = $g['MaToaNha'];

        try {
            if ($action === 'lock') {
                if ($g['TrangThaiGiuong'] !== 'Trống') {
                    Session::flash('error', 'Chỉ có thể khóa bảo trì giường đang ở trạng thái Trống.');
                    $this->redirect('staff/rooms?toa=' . urlencode($maToa));
                    return;
                }
                Database::execute("UPDATE Giuong SET TrangThaiGiuong = 'Khóa bảo trì' WHERE MaGiuong = :g", ['g' => $maGiuong]);
                Session::flash('success', "Đã chuyển giường {$maGiuong} sang trạng thái Khóa bảo trì.");
            } else {
                Database::execute("UPDATE Giuong SET TrangThaiGiuong = 'Trống' WHERE MaGiuong = :g", ['g' => $maGiuong]);
                Session::flash('success', "Đã mở lại giường {$maGiuong} về trạng thái Trống.");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi cập nhật trạng thái giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    public function toggleRoomMaintenance(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $action = trim((string)$request->input('action', 'lock'));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ.');
            $this->redirect('staff/rooms');
            return;
        }

        $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("SELECT MaToaNha, SoSVDangO, MaLoaiPhong FROM Phong WHERE MaPhong = :p", ['p' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Phòng không tồn tại.');
            $this->redirect('staff/rooms');
            return;
        }
        $maToa = $room['MaToaNha'];

        try {
            if ($action === 'lock') {
                Database::execute("UPDATE Phong SET TrangThaiPhong = 'Khóa bảo trì' WHERE MaPhong = :p", ['p' => $maPhong]);
                Session::flash('success', "Đã khóa bảo trì phòng {$maPhong}.");
            } else {
                $p = Database::fetch("
                    SELECT p.SoSVDangO, lp.SoGiuongToiDa 
                    FROM Phong p 
                    JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
                    WHERE p.MaPhong = :p
                ", ['p' => $maPhong]);
                $newStatus = 'Còn chỗ';
                if ($p) {
                    if ($p['SoSVDangO'] >= $p['SoGiuongToiDa']) {
                        $newStatus = 'Đầy chỗ';
                    } elseif ($p['SoSVDangO'] > 0) {
                        $newStatus = 'Đang sử dụng';
                    }
                }
                Database::execute("UPDATE Phong SET TrangThaiPhong = :st WHERE MaPhong = :p", ['st' => $newStatus, 'p' => $maPhong]);
                Session::flash('success', "Đã mở lại phòng {$maPhong} (Trạng thái: {$newStatus}).");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi cập nhật trạng thái phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }


    /**
     * Đồng bộ sĩ số cư trú thực tế và tự động cập nhật trạng thái phòng
     */
    public function syncRoomOccupancy(string $maPhong, bool $forceUnlock = false, bool $preserveStatus = false): void
    {
        // 1. Lấy thông tin phòng, sức chứa và sĩ số thực tế (đếm theo số sinh viên có hợp đồng hiệu lực ở giường có người ở)
        $info = Database::fetch("
            SELECT p.MaPhong, p.TrangThaiPhong, lp.SoGiuongToiDa,
                   (SELECT COUNT(DISTINCT hd.MaSinhVien) 
                    FROM Giuong g 
                    JOIN HopDongNoiTru hd ON g.MaGiuong = hd.MaGiuong 
                    WHERE g.MaPhong = p.MaPhong 
                      AND hd.TrangThaiHopDong = 'Đang hiệu lực' 
                      AND g.TrangThaiGiuong = 'Đã có người ở') AS SoSV
            FROM Phong p
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            WHERE p.MaPhong = :mp
        ", ['mp' => $maPhong]);

        if (!$info) return;

        $maxCap = max(1, (int)($info['SoGiuongToiDa'] ?? 4));
        $rawSoSV = (int)($info['SoSV'] ?? 0);
        // Ràng buộc tuyệt đối: Sĩ số phòng không bao giờ được vượt quá sức chứa tối đa theo Loại phòng
        $soSV = min($maxCap, max(0, $rawSoSV));
        $currentStatus = $info['TrangThaiPhong'] ?? 'Còn chỗ';

        $newStatus = $currentStatus;
        if (!$preserveStatus) {
            if ($forceUnlock || $currentStatus !== 'Khóa bảo trì') {
                if ($soSV > 0) {
                    $newStatus = 'Đang sử dụng';
                } else {
                    $newStatus = 'Còn chỗ';
                }
            }
        }

        Database::execute("
            UPDATE Phong 
            SET SoSVDangO = :sv, TrangThaiPhong = :st 
            WHERE MaPhong = :mp
        ", [
            'sv' => $soSV,
            'st' => $newStatus,
            'mp' => $maPhong
        ]);
    }

    /**
     * Thêm / Sửa Phòng ở từ giao diện Sơ đồ phòng
     */
    public function saveRoomFromRooms(): void
    {
        $request = new Request();
        $isEdit = (int)$request->input('isEdit', 0) === 1;
        $maPhong = trim((string)$request->input('maPhong', ''));
        $reqToa = trim((string)$request->input('maToa', ''));

        $target = $isEdit ? $maPhong : $reqToa;
        $scope = $this->authorizeRoomAction($target);

        $validToas = array_column($scope['availableToas'], 'MaToaNha');
        if ($isEdit) {
            $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            if (!$room) {
                Session::flash('error', "Không tìm thấy phòng {$maPhong}!");
                $this->redirect('staff/rooms');
                return;
            }
            $maToa = $room['MaToaNha'];
        } else {
            $maToa = (!empty($reqToa) && in_array($reqToa, $validToas, true))
                ? $reqToa
                : ($scope['maToa'] ?? ($validToas[0] ?? 'A01'));
        }

        $soPhong = trim((string)$request->input('soPhong', ''));
        $tang = max(1, (int)$request->input('tang', 1));
        $maLoaiPhong = trim((string)$request->input('maLoaiPhong', ''));
        $trangThaiPhong = trim((string)$request->input('trangThaiPhong', 'Còn chỗ'));

        if (empty($maLoaiPhong)) {
            Session::flash('error', 'Vui lòng chọn loại phòng!');
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        $loaiPhong = Database::fetch("SELECT * FROM LoaiPhong WHERE MaLoaiPhong = :lp", ['lp' => $maLoaiPhong]);
        if (!$loaiPhong) {
            Session::flash('error', 'Loại phòng không hợp lệ!');
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }
        $soGiuongToiDa = (int)($loaiPhong['SoGiuongToiDa'] ?? 4);

        try {
            if ($isEdit) {
                // Nếu thay đổi loại phòng, kiểm tra số giường
                $oldLoaiPhong = Database::fetch("SELECT * FROM LoaiPhong WHERE MaLoaiPhong = :lp", ['lp' => $room['MaLoaiPhong']]);
                $oldGiuongToiDa = (int)($oldLoaiPhong['SoGiuongToiDa'] ?? 4);

                if ($soGiuongToiDa > $oldGiuongToiDa) {
                    // Thêm các giường còn thiếu
                    for ($i = $oldGiuongToiDa + 1; $i <= $soGiuongToiDa; $i++) {
                        $maGiuong = str_replace('.', '', $maPhong) . '-G' . $i;
                        Database::execute("
                            INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                            VALUES (:mg, :mp, :kh, :vt, 'Trống')
                            ON DUPLICATE KEY UPDATE TrangThaiGiuong = VALUES(TrangThaiGiuong)
                        ", [
                            'mg' => $maGiuong,
                            'mp' => $maPhong,
                            'kh' => 'G' . $i,
                            'vt' => ($i % 2 === 1) ? 1 : 2
                        ]);
                    }
                } elseif ($soGiuongToiDa < $oldGiuongToiDa) {
                    // Kiểm tra xem các giường vượt số lượng mới có ai đang ở không
                    $excessBeds = Database::fetchAll("
                        SELECT g.MaGiuong, g.KyHieuGiuong, g.TrangThaiGiuong, hd.MaHopDong
                        FROM Giuong g
                        LEFT JOIN HopDongNoiTru hd ON g.MaGiuong = hd.MaGiuong AND hd.TrangThaiHopDong = 'Đang hiệu lực'
                        WHERE g.MaPhong = :mp
                        ORDER BY g.KyHieuGiuong ASC
                    ", ['mp' => $maPhong]);

                    if (count($excessBeds) > $soGiuongToiDa) {
                        $canShrink = true;
                        $bedsToDelete = [];
                        for ($idx = $soGiuongToiDa; $idx < count($excessBeds); $idx++) {
                            $b = $excessBeds[$idx];
                            if (!empty($b['MaHopDong']) || $b['TrangThaiGiuong'] === 'Đã có người ở') {
                                $canShrink = false;
                                break;
                            }
                            $bedsToDelete[] = $b['MaGiuong'];
                        }

                        if (!$canShrink) {
                            Session::flash('error', "Không thể hạ số giường xuống {$soGiuongToiDa} vì các giường dư thừa hiện đang có sinh viên cư trú!");
                            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
                            return;
                        }

                        foreach ($bedsToDelete as $delMg) {
                            Database::execute("DELETE FROM Giuong WHERE MaGiuong = :mg", ['mg' => $delMg]);
                        }
                    }
                }

                $truongPhongInput = trim((string)$request->input('truongPhong', ''));
                $truongPhongVal = !empty($truongPhongInput) ? $truongPhongInput : null;

                Database::execute("
                    UPDATE Phong
                    SET Tang = :tang, MaLoaiPhong = :lp, TrangThaiPhong = :st, TruongPhong = :tp
                    WHERE MaPhong = :mp
                ", [
                    'tang' => $tang,
                    'lp' => $maLoaiPhong,
                    'st' => $trangThaiPhong,
                    'tp' => $truongPhongVal,
                    'mp' => $maPhong
                ]);

                $this->syncRoomOccupancy($maPhong, false, true);
                Session::flash('success', "Đã cập nhật phòng {$maPhong} thành công!");
            } else {
                // Thêm mới phòng
                if (empty($soPhong)) {
                    Session::flash('error', 'Vui lòng nhập số phòng!');
                    $this->redirect('staff/rooms?toa=' . urlencode($maToa));
                    return;
                }

                $maPhong = $maToa . '.' . $soPhong;

                $dup = Database::fetch("SELECT MaPhong FROM Phong WHERE MaPhong = :mp OR (MaToaNha = :toa AND SoPhong = :so)", [
                    'mp' => $maPhong,
                    'toa' => $maToa,
                    'so' => $soPhong
                ]);
                if ($dup) {
                    Session::flash('error', "Phòng '{$maPhong}' hoặc số phòng '{$soPhong}' tại tòa '{$maToa}' đã tồn tại!");
                    $this->redirect('staff/rooms?toa=' . urlencode($maToa));
                    return;
                }

                Database::execute("
                    INSERT INTO Phong (MaPhong, MaToaNha, MaLoaiPhong, SoPhong, Tang, SoSVDangO, TrangThaiPhong)
                    VALUES (:mp, :toa, :lp, :so, :tang, 0, :st)
                ", [
                    'mp' => $maPhong,
                    'toa' => $maToa,
                    'lp' => $maLoaiPhong,
                    'so' => $soPhong,
                    'tang' => $tang,
                    'st' => $trangThaiPhong
                ]);

                for ($i = 1; $i <= $soGiuongToiDa; $i++) {
                    $maGiuong = str_replace('.', '', $maPhong) . '-G' . $i;
                    Database::execute("
                        INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                        VALUES (:mg, :mp, :kh, :vt, 'Trống')
                        ON DUPLICATE KEY UPDATE TrangThaiGiuong = VALUES(TrangThaiGiuong)
                    ", [
                        'mg' => $maGiuong,
                        'mp' => $maPhong,
                        'kh' => 'G' . $i,
                        'vt' => ($i % 2 === 1) ? 1 : 2
                    ]);
                }

                Session::flash('success', "Đã thêm mới phòng {$maPhong} và tự động khởi tạo {$soGiuongToiDa} giường trống thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi lưu thông tin phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Xóa Phòng ở trực tiếp từ sơ đồ phòng
     */
    public function deleteRoomFromRooms(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', "Không tìm thấy phòng {$maPhong}!");
            $this->redirect('staff/rooms');
            return;
        }
        $maToa = $room['MaToaNha'];

        // Kiểm tra nguyên tắc bảo toàn dữ liệu
        $activeResidents = Database::fetch("
            SELECT COUNT(*) AS total
            FROM HopDongNoiTru hd
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            WHERE g.MaPhong = :mp AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
        ", ['mp' => $maPhong]);

        if (($activeResidents['total'] ?? 0) > 0 || (int)$room['SoSVDangO'] > 0) {
            Session::flash('error', "Không thể xóa phòng {$maPhong} vì đang có {$activeResidents['total']} sinh viên có hợp đồng cư trú (Nguyên tắc bảo toàn dữ liệu)!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        try {
            Database::execute("DELETE FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Database::execute("DELETE FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã xóa phòng {$maPhong} và toàn bộ giường liên kết thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Khóa bảo trì hoặc Mở khóa hoạt động cho Phòng
     */
    public function toggleRoomStatus(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Không tìm thấy phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = $room['MaToaNha'];

        if ($room['TrangThaiPhong'] === 'Khóa bảo trì') {
            $this->syncRoomOccupancy($maPhong, true);
            $updated = Database::fetch("SELECT TrangThaiPhong FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            $statusText = $updated['TrangThaiPhong'] ?? 'Đang sử dụng';
            Session::flash('success', "Đã mở khóa hoạt động cho phòng {$maPhong} (chuyển về trạng thái '{$statusText}')!");
        } else {
            Database::execute("UPDATE Phong SET TrangThaiPhong = 'Khóa bảo trì' WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã chuyển phòng {$maPhong} sang trạng thái Khóa bảo trì!");
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Thêm giường mới / giường phụ vào phòng
     */
    public function addBed(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $kyHieu = trim((string)$request->input('kyHieuGiuong', ''));
        $tangGiuong = max(1, (int)$request->input('viTriTangGiuong', 1));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("
            SELECT p.*, lp.TenLoaiPhong, lp.SoGiuongToiDa 
            FROM Phong p 
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
            WHERE p.MaPhong = :mp
        ", ['mp' => $maPhong]);

        if (!$room) {
            Session::flash('error', 'Phòng không tồn tại!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = $room['MaToaNha'];

        // RÀNG BUỘC: Không cho tạo thêm giường vượt quá số giường tối đa theo Loại phòng
        $soGiuongToiDa = (int)($room['SoGiuongToiDa'] ?? 4);
        $bedCountRow = Database::fetch("SELECT COUNT(*) AS cnt FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        $currentBedCount = (int)($bedCountRow['cnt'] ?? 0);

        if ($currentBedCount >= $soGiuongToiDa) {
            $tenLoai = $room['TenLoaiPhong'] ?? $room['MaLoaiPhong'];
            Session::flash('error', "Không thể tạo thêm giường! Phòng {$maPhong} thuộc Loại phòng '{$tenLoai}' chỉ cho phép tối đa {$soGiuongToiDa} giường (hiện tại phòng đã có đủ {$currentBedCount} giường)!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        if (empty($kyHieu)) {
            $kyHieu = 'G' . ($currentBedCount + 1);
        }

        $maGiuong = str_replace('.', '', $maPhong) . '-' . $kyHieu;
        $dup = Database::fetch("SELECT MaGiuong FROM Giuong WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
        if ($dup) {
            Session::flash('error', "Giường '{$kyHieu}' tại phòng {$maPhong} đã tồn tại!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        try {
            Database::execute("
                INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                VALUES (:mg, :mp, :kh, :vt, 'Trống')
            ", [
                'mg' => $maGiuong,
                'mp' => $maPhong,
                'kh' => $kyHieu,
                'vt' => $tangGiuong
            ]);
            $this->syncRoomOccupancy($maPhong);
            Session::flash('success', "Đã thêm giường {$kyHieu} vào phòng {$maPhong} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi thêm giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Cập nhật trạng thái Giường (Trống, Khóa bảo trì, Hỏng...)
     */
    public function updateBedStatus(): void
    {
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $trangThai = trim((string)$request->input('trangThaiGiuong', 'Trống'));

        if (empty($maGiuong)) {
            Session::flash('error', 'Mã giường không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $bed = Database::fetch("
            SELECT g.*, p.MaToaNha, p.MaPhong
            FROM Giuong g
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE g.MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (!$bed) {
            Session::flash('error', 'Giường không tồn tại!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($bed['MaToaNha']);
        $maToa = $bed['MaToaNha'];

        // Kiểm tra hợp đồng hiệu lực
        $hasActiveContract = Database::fetch("
            SELECT MaHopDong FROM HopDongNoiTru
            WHERE MaGiuong = :mg AND TrangThaiHopDong = 'Đang hiệu lực'
        ", ['mg' => $maGiuong]);

        if ($hasActiveContract && in_array($trangThai, ['Trống', 'Khóa bảo trì', 'Hỏng'], true)) {
            Session::flash('error', "Không thể đổi trạng thái giường sang '{$trangThai}' vì sinh viên đang cư trú theo hợp đồng {$hasActiveContract['MaHopDong']}!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        try {
            Database::execute("UPDATE Giuong SET TrangThaiGiuong = :st WHERE MaGiuong = :mg", [
                'st' => $trangThai,
                'mg' => $maGiuong
            ]);
            $this->syncRoomOccupancy($bed['MaPhong']);
            Session::flash('success', "Đã cập nhật trạng thái giường {$maGiuong} sang '{$trangThai}' thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi cập nhật giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Xóa Giường trống khỏi phòng
     */
    public function deleteBed(): void
    {
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));

        if (empty($maGiuong)) {
            Session::flash('error', 'Mã giường không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $bed = Database::fetch("
            SELECT g.*, p.MaToaNha, p.MaPhong
            FROM Giuong g
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE g.MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (!$bed) {
            Session::flash('error', 'Giường không tồn tại!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($bed['MaToaNha']);
        $maToa = $bed['MaToaNha'];

        // Kiểm tra nguyên tắc bảo toàn dữ liệu
        $contract = Database::fetch("
            SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (($contract['total'] ?? 0) > 0 || $bed['TrangThaiGiuong'] === 'Đã có người ở') {
            Session::flash('error', "Không thể xóa giường {$maGiuong} vì đã có lịch sử hợp đồng nội trú gắn liền (Nguyên tắc bảo toàn dữ liệu)!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        try {
            Database::execute("DELETE FROM Giuong WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
            $this->syncRoomOccupancy($bed['MaPhong']);
            Session::flash('success', "Đã xóa giường {$maGiuong} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Chỉ định hoặc hủy Trưởng phòng
     */
    public function assignRoomLeader(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));
        $action = trim((string)$request->input('action', 'assign'));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Không tìm thấy phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = $room['MaToaNha'];

        if ($action === 'remove' || empty($maSinhVien)) {
            Database::execute("UPDATE Phong SET TruongPhong = NULL WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã gỡ vai trò Trưởng phòng của phòng {$maPhong}!");
        } else {
            Database::execute("UPDATE Phong SET TruongPhong = :sv WHERE MaPhong = :mp", ['sv' => $maSinhVien, 'mp' => $maPhong]);
            Session::flash('success', "Đã chỉ định sinh viên {$maSinhVien} làm Trưởng phòng {$maPhong}!");
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Xác nhận sinh viên nhận phòng thực tế (Ghi nhận NgayNhanPhong = NOW())
     */
    public function confirmCheckIn(): void
    {
        $request = new Request();
        $maHopDong = trim((string)$request->input('maHopDong', ''));
        if (empty($maHopDong)) {
            Session::flash('error', 'Mã hợp đồng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $hd = Database::fetch("
            SELECT hd.*, g.MaPhong, p.MaToaNha
            FROM HopDongNoiTru hd
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE hd.MaHopDong = :hd
        ", ['hd' => $maHopDong]);

        if (!$hd) {
            Session::flash('error', 'Không tìm thấy thông tin hợp đồng nội trú!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($hd['MaToaNha']);
        $maToa = $hd['MaToaNha'];

        Database::execute("
            UPDATE HopDongNoiTru
            SET NgayNhanPhong = NOW(), TrangThaiHopDong = 'Đang hiệu lực'
            WHERE MaHopDong = :hd
        ", ['hd' => $maHopDong]);

        Database::execute("
            UPDATE Giuong
            SET TrangThaiGiuong = 'Đã có người ở'
            WHERE MaGiuong = :mg
        ", ['mg' => $hd['MaGiuong']]);

        $this->syncRoomOccupancy($hd['MaPhong']);

        Session::flash('success', "Đã xác nhận sinh viên nhận phòng thực tế cho hợp đồng {$maHopDong} thành công! Giờ nhận phòng đã được ghi nhận.");
        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Tích nhận hoặc hủy thu Mẫu CT01 khai báo cư trú
     */
    public function toggleCT01(): void
    {
        $request = new Request();
        $maHopDong = trim((string)$request->input('maHopDong', ''));
        $status = (int)$request->input('status', 1);

        if (empty($maHopDong)) {
            Session::flash('error', 'Mã hợp đồng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $hd = Database::fetch("
            SELECT hd.*, p.MaToaNha
            FROM HopDongNoiTru hd
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE hd.MaHopDong = :hd
        ", ['hd' => $maHopDong]);

        if (!$hd) {
            Session::flash('error', 'Không tìm thấy hợp đồng!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($hd['MaToaNha']);
        $maToa = $hd['MaToaNha'];

        Database::execute("
            UPDATE HopDongNoiTru
            SET DaNopMauCT01 = :st
            WHERE MaHopDong = :hd
        ", ['st' => $status, 'hd' => $maHopDong]);

        $msg = $status ? "Đã ghi nhận sinh viên nộp mẫu CT01 thành công!" : "Đã chuyển tình trạng Mẫu CT01 về Chưa nộp!";
        Session::flash('success', $msg);
        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Xóa sinh viên khỏi giường (Hủy lưu trú / Thanh lý hợp đồng / Trả giường)
     */
    public function removeStudentFromBed(): void
    {
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));

        if (empty($maGiuong) || empty($maPhong)) {
            Session::flash('error', 'Thông tin giường hoặc phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $scope = $this->authorizeRoomAction($maPhong);

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Phòng không tồn tại!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = $room['MaToaNha'];

        // Kiểm tra giường
        $bed = Database::fetch("SELECT * FROM Giuong WHERE MaGiuong = :mg AND MaPhong = :mp", [
            'mg' => $maGiuong,
            'mp' => $maPhong
        ]);
        if (!$bed) {
            Session::flash('error', 'Không tìm thấy giường trong phòng!');
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        // Lấy hợp đồng đang hiệu lực trên giường này
        $activeHd = Database::fetch("
            SELECT hd.*, sv.HoVaTen, sv.MSSVTaiTruong 
            FROM HopDongNoiTru hd
            LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
            WHERE hd.MaGiuong = :mg AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
        ", ['mg' => $maGiuong]);

        if (!$activeHd) {
            Database::execute("UPDATE Giuong SET TrangThaiGiuong = 'Trống' WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
            $this->syncRoomOccupancy($maPhong, true);
            Session::flash('success', "Giường {$bed['KyHieuGiuong']} không có hợp đồng đang hiệu lực, đã đưa về trạng thái Trống!");
            $this->redirect('staff/rooms?toa=' . urlencode($maToa));
            return;
        }

        $targetMsv = $activeHd['MaSinhVien'];
        $svTen = $activeHd['HoVaTen'] ?? $targetMsv;

        try {
            // 1. Thanh lý TOÀN BỘ hợp đồng nội trú đang hiệu lực trên giường này (tránh sót hợp đồng trùng lặp)
            Database::execute("
                UPDATE HopDongNoiTru 
                SET TrangThaiHopDong = 'Đã thanh lý'
                WHERE MaGiuong = :mg AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
            ", ['mg' => $maGiuong]);

            // 2. Chuyển trạng thái giường về 'Trống'
            Database::execute("
                UPDATE Giuong 
                SET TrangThaiGiuong = 'Trống' 
                WHERE MaGiuong = :mg
            ", ['mg' => $maGiuong]);

            // 3. Nếu sinh viên đang là Trưởng phòng của phòng này, hủy vai trò Trưởng phòng
            if ($room['TruongPhong'] === $targetMsv) {
                Database::execute("
                    UPDATE Phong 
                    SET TruongPhong = NULL 
                    WHERE MaPhong = :mp
                ", ['mp' => $maPhong]);
            }

            // 4. Kiểm tra xem sinh viên này còn hợp đồng nào khác đang hiệu lực không
            $otherHd = Database::fetch("
                SELECT COUNT(*) AS cnt 
                FROM HopDongNoiTru 
                WHERE MaSinhVien = :sv AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
            ", ['sv' => $targetMsv]);

            if ((int)($otherHd['cnt'] ?? 0) === 0) {
                Database::execute("
                    UPDATE SinhVien 
                    SET TrangThaiNoiTru = 'Chưa ở' 
                    WHERE MaSinhVien = :sv
                ", ['sv' => $targetMsv]);
            }

            // 5. Đồng bộ lại sĩ số phòng và trạng thái phòng
            $this->syncRoomOccupancy($maPhong, true);

            Session::flash('success', "Đã xóa sinh viên {$svTen} (MSSV: {$targetMsv}) khỏi giường {$bed['KyHieuGiuong']} thành công! Giường đã chuyển về trạng thái Trống.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa sinh viên khỏi giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }

    /**
     * Xuất danh sách Phòng ở ra file CSV theo kết quả đang lọc hoặc toàn bộ
     */
    public function exportRooms(): void
    {
        $request = new Request();
        $reqToa = trim((string)$request->input('toa', ''));
        $scope = $this->authorizeRoomAction($reqToa);

        $validToas = array_column($scope['availableToas'], 'MaToaNha');
        if (!empty($reqToa) && in_array($reqToa, $validToas, true)) {
            $maToa = $reqToa;
        } else {
            $maToa = $scope['maToa'] ?? ($validToas[0] ?? 'A01');
        }
        $idsParam = $request->input('ids');
        $tang = $request->input('tang');
        $loaiPhong = trim((string)$request->input('loaiPhong', ''));
        $trangThai = trim((string)$request->input('trangThai', ''));
        $keyword = trim((string)$request->input('keyword', ''));

        try {
            $whereClause = "WHERE p.MaToaNha = :toa";
            $params = ['toa' => $maToa];
            $isFiltered = false;

            if (!empty($idsParam)) {
                $rawIds = is_array($idsParam) ? $idsParam : explode(',', (string)$idsParam);
                $cleanIds = array_filter(array_map('trim', $rawIds));
                if (!empty($cleanIds)) {
                    $placeholders = [];
                    foreach (array_values($cleanIds) as $k => $id) {
                        $ph = ':id_' . $k;
                        $placeholders[] = $ph;
                        $params['id_' . $k] = $id;
                    }
                    $whereClause .= " AND p.MaPhong IN (" . implode(',', $placeholders) . ")";
                    $isFiltered = true;
                }
            } else {
                if ($tang !== null && $tang !== '' && (int)$tang > 0) {
                    $whereClause .= " AND p.Tang = :tang";
                    $params['tang'] = (int)$tang;
                    $isFiltered = true;
                }
                if (!empty($loaiPhong)) {
                    $whereClause .= " AND p.MaLoaiPhong = :lp";
                    $params['lp'] = $loaiPhong;
                    $isFiltered = true;
                }
                if (!empty($trangThai)) {
                    $whereClause .= " AND p.TrangThaiPhong = :st";
                    $params['st'] = $trangThai;
                    $isFiltered = true;
                }
                if (!empty($keyword)) {
                    $whereClause .= " AND (p.SoPhong LIKE :kw1 OR p.MaPhong LIKE :kw2)";
                    $kwVal = "%{$keyword}%";
                    $params['kw1'] = $kwVal;
                    $params['kw2'] = $kwVal;
                    $isFiltered = true;
                }
            }

            $sql = "
                SELECT p.*,
                       lp.TenLoaiPhong, lp.SoGiuongToiDa, lp.DonGiaLuuTruChuan,
                       tn.TenToaNha,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong) AS TongGiuong,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Trống') AS SoGiuongTrong,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Đã có người ở') AS SoGiuongCoNguoi,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Khóa bảo trì') AS SoGiuongBaoTri,
                       sv.HoVaTen AS TenTruongPhong
                FROM Phong p
                LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN SinhVien sv ON p.TruongPhong = sv.MaSinhVien
                {$whereClause}
                ORDER BY p.Tang ASC, p.SoPhong ASC
            ";
            $rooms = Database::fetchAll($sql, $params);

            // Truy vấn toàn bộ giường của tòa để ghép dữ liệu
            $allBeds = Database::fetchAll("
                SELECT g.MaPhong, g.KyHieuGiuong, g.TrangThaiGiuong, sv.HoVaTen, sv.MSSVTaiTruong
                FROM Giuong g
                JOIN Phong p ON g.MaPhong = p.MaPhong
                LEFT JOIN HopDongNoiTru hd ON g.MaGiuong = hd.MaGiuong AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                WHERE p.MaToaNha = :toa
                ORDER BY g.KyHieuGiuong ASC
            ", ['toa' => $maToa]);

            $bedsMap = [];
            foreach ($allBeds as $bg) {
                $statusTxt = $bg['TrangThaiGiuong'];
                if (!empty($bg['HoVaTen'])) {
                    $statusTxt .= ' (' . $bg['HoVaTen'] . ' - ' . ($bg['MSSVTaiTruong'] ?? '') . ')';
                }
                $bedsMap[$bg['MaPhong']][] = $bg['KyHieuGiuong'] . ': ' . $statusTxt;
            }

            $filenamePrefix = $isFiltered ? ('DS_Phong_Toa_' . $maToa . '_Filtered_') : ('DS_Phong_Toa_' . $maToa . '_');
            $filename = $filenamePrefix . date('Ymd_His') . '.csv';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            // Header columns
            fputcsv($output, [
                'STT',
                'Mã phòng',
                'Tòa nhà',
                'Tầng',
                'Số phòng',
                'Mã loại phòng',
                'Tên loại phòng',
                'Đơn giá (VNĐ/tháng)',
                'Sức chứa tối đa (giường)',
                'Sĩ số đang ở (SV)',
                'Số giường trống',
                'Số giường bảo trì',
                'Trạng thái phòng',
                'Mã SV Trưởng phòng',
                'Họ tên Trưởng phòng',
                'Chi tiết sơ đồ giường & sinh viên'
            ]);

            $stt = 1;
            foreach ($rooms as $r) {
                $bedDetails = isset($bedsMap[$r['MaPhong']]) ? implode('; ', $bedsMap[$r['MaPhong']]) : '';
                fputcsv($output, [
                    $stt++,
                    $r['MaPhong'],
                    $r['TenToaNha'] ?? $r['MaToaNha'],
                    $r['Tang'],
                    $r['SoPhong'],
                    $r['MaLoaiPhong'],
                    $r['TenLoaiPhong'] ?? '',
                    (int)($r['DonGiaLuuTruChuan'] ?? 0),
                    (int)($r['SoGiuongToiDa'] ?? 4),
                    (int)($r['SoSVDangO'] ?? 0),
                    (int)($r['SoGiuongTrong'] ?? 0),
                    (int)($r['SoGiuongBaoTri'] ?? 0),
                    $r['TrangThaiPhong'] ?? 'Còn chỗ',
                    $r['TruongPhong'] ?? '',
                    $r['TenTruongPhong'] ?? '',
                    $bedDetails
                ]);
            }

            fclose($output);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xuất file danh sách phòng: ' . $e->getMessage());
            $this->redirect('staff/rooms');
        }
    }

    /**
     * Tải file mẫu CSV để nhập danh sách phòng ở kèm sơ đồ giường và sinh viên cư trú
     */
    public function downloadRoomTemplate(): void
    {
        $filename = 'Mau_Import_Phong_Va_Giuong_SV_KTX.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        // Headers chi tiết hỗ trợ nhập Phòng, Giường và Sinh viên đang ở
        fputcsv($output, [
            'SoPhong',
            'Tang',
            'MaLoaiPhong',
            'TrangThaiPhong',
            'KyHieuGiuong',
            'ViTriTangGiuong',
            'MaSinhVien',
            'HoVaTen',
            'GioiTinh',
            'SoCCCD',
            'SoDienThoai',
            'Email',
            'MaTruong',
            'LaTruongPhong',
            'DaNopCT01'
        ]);

        // Dữ liệu mẫu minh họa:
        // Phòng 101 (LP4 - 4 giường): Có 2 sinh viên đang ở, G1 là Trưởng phòng đã nộp CT01
        fputcsv($output, ['101', '1', 'LP4', 'Đang sử dụng', 'G1', '1', '2110001', 'Nguyễn Văn An', 'Nam', '079205001234', '0901234567', 'an.nguyen@example.com', 'BK', '1', '1']);
        fputcsv($output, ['101', '1', 'LP4', 'Đang sử dụng', 'G2', '1', '2310002', 'Nguyễn Thị Ngọc Anh', 'Nữ', '079206001002', '0912345678', 'anh.nguyen@example.com', 'KHTN', '0', '1']);
        fputcsv($output, ['101', '1', 'LP4', 'Đang sử dụng', 'G3', '2', '', '', '', '', '', '', '', '0', '0']);
        fputcsv($output, ['101', '1', 'LP4', 'Đang sử dụng', 'G4', '2', '', '', '', '', '', '', '', '0', '0']);

        // Phòng 102 (LP4): Có 1 sinh viên ở G1 (Trưởng phòng)
        fputcsv($output, ['102', '1', 'LP4', 'Còn chỗ', 'G1', '1', '2212345', 'Trần Thị Mai Chúc', 'Nữ', '074205003456', '0987654321', 'chuc.tran@example.com', 'UIT', '1', '1']);
        fputcsv($output, ['102', '1', 'LP4', 'Còn chỗ', 'G2', '1', '', '', '', '', '', '', '', '0', '0']);
        fputcsv($output, ['102', '1', 'LP4', 'Còn chỗ', 'G3', '2', '', '', '', '', '', '', '', '0', '0']);
        fputcsv($output, ['102', '1', 'LP4', 'Còn chỗ', 'G4', '2', '', '', '', '', '', '', '', '0', '0']);

        // Phòng 201 (LP6): Phòng trống (hệ thống tự sinh G1 đến G6)
        fputcsv($output, ['201', '2', 'LP6', 'Còn chỗ', 'G1', '1', '', '', '', '', '', '', '', '0', '0']);

        // Phòng 301 (LP4ML): Phòng đang khóa bảo trì
        fputcsv($output, ['301', '3', 'LP4ML', 'Khóa bảo trì', 'G1', '1', '', '', '', '', '', '', '', '0', '0']);

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách phòng ở từ file CSV (hỗ trợ nhập kèm danh sách sinh viên theo giường)
     */
    public function importRooms(): void
    {
        $request = new Request();
        $reqToa = trim((string)$request->input('maToa', ''));
        $scope = $this->authorizeRoomAction($reqToa);

        $validToas = array_column($scope['availableToas'], 'MaToaNha');
        if (!empty($reqToa) && in_array($reqToa, $validToas, true)) {
            $maToa = $reqToa;
        } else {
            $maToa = $scope['maToa'] ?? ($validToas[0] ?? 'A01');
        }

        $rawOverwrite = $request->input('overwriteExisting', null);
        if ($rawOverwrite === null) {
            $rawOverwrite = $_POST['overwriteExisting'] ?? '0';
        }
        $overwrite = ($rawOverwrite === '1' || $rawOverwrite === 1 || $rawOverwrite === true || $rawOverwrite === 'on');

        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn tệp CSV hợp lệ để nhập danh sách phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $file = $_FILES['importFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::flash('error', 'Hệ thống chỉ hỗ trợ tệp định dạng CSV (.csv) hoặc văn bản (.txt)!');
            $this->redirect('staff/rooms');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể mở tệp tải lên để đọc dữ liệu!');
            $this->redirect('staff/rooms');
            return;
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                Session::flash('error', 'Tệp tải lên rỗng!');
                $this->redirect('staff/rooms');
                return;
            }

            // Xóa UTF-8 BOM
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            if (substr_count($firstLine, "\t") > substr_count($firstLine, $delimiter)) {
                $delimiter = "\t";
            }
            $headers = str_getcsv(trim($firstLine), $delimiter);

            // Hàm chuẩn hóa chuỗi tiêu đề cột (bỏ dấu, chuyển chữ thường, bỏ ký tự đặc biệt)
            $normalize = function(string $str): string {
                $str = mb_strtolower(trim($str), 'UTF-8');
                $str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/u", 'a', $str);
                $str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/u", 'e', $str);
                $str = preg_replace("/(ì|í|ị|ỉ|ĩ)/u", 'i', $str);
                $str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/u", 'o', $str);
                $str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/u", 'u', $str);
                $str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/u", 'y', $str);
                $str = preg_replace("/(đ)/u", 'd', $str);
                return preg_replace('/[^a-z0-9]/', '', $str);
            };

            $colMap = [];
            foreach ($headers as $idx => $h) {
                $clean = $normalize($h);
                if (in_array($clean, ['sophong', 'phong', 'room', 'roomnumber', 'sp', 'sophongonoiduoc'])) {
                    $colMap['sophong'] = $idx;
                } elseif (in_array($clean, ['tang', 'floor', 'sotang', 'tanglau'])) {
                    $colMap['tang'] = $idx;
                } elseif (in_array($clean, ['maloaiphong', 'loaiphong', 'type', 'roomtype', 'tenloaiphong'])) {
                    $colMap['loaiphong'] = $idx;
                } elseif (in_array($clean, ['trangthaiphong', 'trangthai', 'status', 'trangthaiphongo'])) {
                    $colMap['trangthai'] = $idx;
                } elseif (in_array($clean, ['kyhieugiuong', 'giuong', 'magiuong', 'bed', 'bedcode', 'kh', 'sogiuong'])) {
                    $colMap['kyhieu'] = $idx;
                } elseif (in_array($clean, ['vitritanggiuong', 'tanggiuong', 'vitri', 'bedlevel', 'level'])) {
                    $colMap['vitri'] = $idx;
                } elseif (in_array($clean, ['trangthaigiuong', 'bedstatus'])) {
                    $colMap['trangthaigiuong'] = $idx;
                } elseif (in_array($clean, ['masinhvien', 'mssv', 'masv', 'studentid', 'sv'])) {
                    $colMap['masv'] = $idx;
                } elseif (in_array($clean, ['hovaten', 'hoten', 'tensinhvien', 'tensv', 'fullname', 'name'])) {
                    $colMap['hoten'] = $idx;
                } elseif (in_array($clean, ['gioitinh', 'gender', 'sex', 'phai'])) {
                    $colMap['gioitinh'] = $idx;
                } elseif (in_array($clean, ['socccd', 'cccd', 'cmnd', 'idcard', 'cancuoc'])) {
                    $colMap['cccd'] = $idx;
                } elseif (in_array($clean, ['sodienthoai', 'sdt', 'phone', 'dienthoai', 'mobile'])) {
                    $colMap['sdt'] = $idx;
                } elseif (in_array($clean, ['email', 'mail'])) {
                    $colMap['email'] = $idx;
                } elseif (in_array($clean, ['matruong', 'truong', 'school', 'university', 'tentruong'])) {
                    $colMap['truong'] = $idx;
                } elseif (in_array($clean, ['latruongphong', 'truongphong', 'latp', 'chucvu', 'isleader', 'istruongphong', 'tp'])) {
                    $colMap['latruongphong'] = $idx;
                } elseif (in_array($clean, ['danopct01', 'ct01', 'danopmauct01', 'tamtrumauct01', 'tamtru'])) {
                    $colMap['ct01'] = $idx;
                } elseif (in_array($clean, ['ngaybatdau', 'batdau', 'startdate', 'tungay'])) {
                    $colMap['startdate'] = $idx;
                } elseif (in_array($clean, ['ngayketthuc', 'ketthuc', 'enddate', 'denngay'])) {
                    $colMap['enddate'] = $idx;
                }
            }

            if (!isset($colMap['sophong'])) {
                fclose($handle);
                Session::flash('error', 'Tệp CSV không đúng định dạng. Cột bắt buộc tối thiểu: SoPhong (Số phòng)!');
                $this->redirect('staff/rooms');
                return;
            }

            $loaiPhongListDb = Database::fetchAll("SELECT MaLoaiPhong, SoGiuongToiDa FROM LoaiPhong");
            $validLoaiPhong = [];
            foreach ($loaiPhongListDb as $lp) {
                $validLoaiPhong[$lp['MaLoaiPhong']] = (int)$lp['SoGiuongToiDa'];
            }
            $defaultLoaiPhong = array_key_first($validLoaiPhong) ?: 'LP4';

            // Trường ĐH mặc định
            $firstTruong = Database::fetch("SELECT MaTruong FROM TruongDaiHoc LIMIT 1");
            $defaultTruong = $firstTruong['MaTruong'] ?? 'BK';

            // Đợt đăng ký mặc định
            $dotRow = Database::fetch("SELECT MaDot FROM DotDangKy ORDER BY MaDot DESC LIMIT 1");
            $defaultMaDot = (int)($dotRow['MaDot'] ?? 1);

            // Năm học mặc định cho hợp đồng
            $currentYear = (int)date('Y');
            $currentMonth = (int)date('n');
            if ($currentMonth >= 9) {
                $defaultStart = $currentYear . '-09-01';
                $defaultEnd = ($currentYear + 1) . '-06-30';
            } else {
                $defaultStart = ($currentYear - 1) . '-09-01';
                $defaultEnd = $currentYear . '-06-30';
            }

            // Đọc toàn bộ các dòng và nhóm theo Số phòng
            $roomGroups = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (empty($row) || (count($row) === 1 && $row[0] === null)) {
                    continue;
                }

                $soPhong = isset($colMap['sophong'], $row[$colMap['sophong']]) ? trim((string)$row[$colMap['sophong']]) : '';
                if (empty($soPhong)) {
                    continue;
                }

                if (!isset($roomGroups[$soPhong])) {
                    $tang = isset($colMap['tang'], $row[$colMap['tang']]) ? max(1, (int)$row[$colMap['tang']]) : 1;
                    $maLoaiPhong = isset($colMap['loaiphong'], $row[$colMap['loaiphong']]) ? trim((string)$row[$colMap['loaiphong']]) : '';
                    if (empty($maLoaiPhong) || !isset($validLoaiPhong[$maLoaiPhong])) {
                        $maLoaiPhong = $defaultLoaiPhong;
                    }
                    $trangThaiPhong = isset($colMap['trangthai'], $row[$colMap['trangthai']]) ? trim((string)$row[$colMap['trangthai']]) : 'Còn chỗ';
                    if (!in_array($trangThaiPhong, ['Còn chỗ', 'Đang sử dụng', 'Đầy chỗ', 'Khóa bảo trì'], true)) {
                        $trangThaiPhong = 'Còn chỗ';
                    }

                    $roomGroups[$soPhong] = [
                        'soPhong' => $soPhong,
                        'tang' => $tang,
                        'maLoaiPhong' => $maLoaiPhong,
                        'trangThaiPhong' => $trangThaiPhong,
                        'soGiuongToiDa' => $validLoaiPhong[$maLoaiPhong] ?? 4,
                        'beds' => []
                    ];
                }

                // Đọc thông tin Giường & Sinh viên
                $kyHieu = isset($colMap['kyhieu'], $row[$colMap['kyhieu']]) ? trim((string)$row[$colMap['kyhieu']]) : '';
                $viTriTang = isset($colMap['vitri'], $row[$colMap['vitri']]) ? (int)$row[$colMap['vitri']] : 0;
                $stGiuong = isset($colMap['trangthaigiuong'], $row[$colMap['trangthaigiuong']]) ? trim((string)$row[$colMap['trangthaigiuong']]) : '';
                $maSV = isset($colMap['masv'], $row[$colMap['masv']]) ? trim((string)$row[$colMap['masv']]) : '';
                $hoTen = isset($colMap['hoten'], $row[$colMap['hoten']]) ? trim((string)$row[$colMap['hoten']]) : '';
                $gioiTinh = isset($colMap['gioitinh'], $row[$colMap['gioitinh']]) ? trim((string)$row[$colMap['gioitinh']]) : '';
                $cccd = isset($colMap['cccd'], $row[$colMap['cccd']]) ? trim((string)$row[$colMap['cccd']]) : '';
                $sdt = isset($colMap['sdt'], $row[$colMap['sdt']]) ? trim((string)$row[$colMap['sdt']]) : '';
                $email = isset($colMap['email'], $row[$colMap['email']]) ? trim((string)$row[$colMap['email']]) : '';
                $truong = isset($colMap['truong'], $row[$colMap['truong']]) ? trim((string)$row[$colMap['truong']]) : '';

                $isTP = false;
                if (isset($colMap['latruongphong'])) {
                    $tpVal = mb_strtolower(trim((string)$row[$colMap['latruongphong']]), 'UTF-8');
                    $isTP = in_array($tpVal, ['1', 'true', 'co', 'yes', 'tp', 'truongphong']);
                }

                $hasCT01 = 0;
                if (isset($colMap['ct01'])) {
                    $ctVal = mb_strtolower(trim((string)$row[$colMap['ct01']]), 'UTF-8');
                    $hasCT01 = in_array($ctVal, ['1', 'true', 'co', 'yes', 'danop']) ? 1 : 0;
                }

                $startD = isset($colMap['startdate'], $row[$colMap['startdate']]) && !empty(trim((string)$row[$colMap['startdate']])) ? trim((string)$row[$colMap['startdate']]) : $defaultStart;
                $endD = isset($colMap['enddate'], $row[$colMap['enddate']]) && !empty(trim((string)$row[$colMap['enddate']])) ? trim((string)$row[$colMap['enddate']]) : $defaultEnd;

                // Nếu có chỉ định giường hoặc sinh viên
                if (!empty($kyHieu) || !empty($maSV)) {
                    if (empty($kyHieu)) {
                        $kyHieu = 'G' . (count($roomGroups[$soPhong]['beds']) + 1);
                    } else {
                        $kyHieu = strtoupper($kyHieu);
                    }

                    if ($viTriTang <= 0) {
                        preg_match('/\d+/', $kyHieu, $mDigits);
                        $num = !empty($mDigits[0]) ? (int)$mDigits[0] : (count($roomGroups[$soPhong]['beds']) + 1);
                        $viTriTang = ($num % 2 === 1) ? 1 : 2;
                    }

                    $bedIdx = $kyHieu;
                    $roomGroups[$soPhong]['beds'][$bedIdx] = [
                        'kyHieu' => $kyHieu,
                        'viTriTang' => $viTriTang,
                        'trangThaiGiuong' => !empty($stGiuong) ? $stGiuong : (!empty($maSV) ? 'Đã có người ở' : 'Trống'),
                        'maSV' => $maSV,
                        'hoTen' => $hoTen,
                        'gioiTinh' => $gioiTinh,
                        'cccd' => $cccd,
                        'sdt' => $sdt,
                        'email' => $email,
                        'truong' => $truong,
                        'isTP' => $isTP,
                        'hasCT01' => $hasCT01,
                        'startDate' => $startD,
                        'endDate' => $endD
                    ];
                }
            }
            fclose($handle);

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $assignedStudentCount = 0;
            $newStudentCount = 0;
            $warningRooms = [];

            foreach ($roomGroups as $soPhong => $rData) {
                $maPhong = $maToa . '.' . $soPhong;
                $tang = $rData['tang'];
                $maLoaiPhong = $rData['maLoaiPhong'];
                $soGiuongToiDa = $rData['soGiuongToiDa'];
                $trangThaiPhong = $rData['trangThaiPhong'];

                $existing = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);

                if ($existing) {
                    if (!$overwrite) {
                        $skippedCount++;
                        continue;
                    }
                    Database::execute("
                        UPDATE Phong
                        SET Tang = :tang, MaLoaiPhong = :lp, TrangThaiPhong = :st
                        WHERE MaPhong = :mp
                    ", [
                        'tang' => $tang,
                        'lp' => $maLoaiPhong,
                        'st' => $trangThaiPhong,
                        'mp' => $maPhong
                    ]);
                    $updatedCount++;
                } else {
                    Database::execute("
                        INSERT INTO Phong (MaPhong, MaToaNha, MaLoaiPhong, SoPhong, Tang, SoSVDangO, TrangThaiPhong)
                        VALUES (:mp, :toa, :lp, :so, :tang, 0, :st)
                    ", [
                        'mp' => $maPhong,
                        'toa' => $maToa,
                        'lp' => $maLoaiPhong,
                        'so' => $soPhong,
                        'tang' => $tang,
                        'st' => $trangThaiPhong
                    ]);
                    $createdCount++;
                }

                // Xử lý Giường theo ràng buộc số giường tối đa của Loại phòng
                $cleanPrefix = str_replace('.', '', $maPhong);
                $bedsToProcess = $rData['beds'];
                $truongPhongMaSV = null;

                // Dọn dẹp các giường dư thừa trong DB nếu loại phòng mới có số giường nhỏ hơn hoặc do tàn dư dữ liệu cũ
                $oldExcessBeds = Database::fetchAll("
                    SELECT MaGiuong FROM Giuong 
                    WHERE MaPhong = :mp AND CAST(SUBSTRING_INDEX(KyHieuGiuong, 'G', -1) AS UNSIGNED) > :maxCap
                ", ['mp' => $maPhong, 'maxCap' => $soGiuongToiDa]);
                foreach ($oldExcessBeds as $oeb) {
                    Database::execute("UPDATE HopDongNoiTru SET TrangThaiHopDong = 'Đã thanh lý' WHERE MaGiuong = :mg", ['mg' => $oeb['MaGiuong']]);
                    Database::execute("DELETE FROM Giuong WHERE MaGiuong = :mg", ['mg' => $oeb['MaGiuong']]);
                }

                // CẢNH BÁO: Kiểm tra số lượng sinh viên được chỉ định trong file có vượt quá sức chứa không
                $residentStudentsInFile = array_filter($bedsToProcess, fn($b) => !empty(trim((string)$b['maSV'])));
                $totalResidentInFile = count($residentStudentsInFile);
                if ($totalResidentInFile > $soGiuongToiDa) {
                    $excess = $totalResidentInFile - $soGiuongToiDa;
                    $warningRooms[] = "Phòng <b>{$soPhong}</b> (Loại <b>{$maLoaiPhong}</b>): Trong file có <b>{$totalResidentInFile}</b> sinh viên cư trú, vượt quá sức chứa tối đa cho phép <b>{$soGiuongToiDa} SV</b>! Hệ thống chỉ tiếp nhận {$soGiuongToiDa} sinh viên đầu tiên và từ chối {$excess} sinh viên vượt mức.";
                }

                // 1. Tạo hoặc cập nhật các giường có trong file (chỉ cho phép tối đa $soGiuongToiDa giường)
                $assignedBedKeys = [];
                $bedOrder = 0;
                $admittedResidentCount = 0;

                foreach ($bedsToProcess as $bKey => $bInfo) {
                    $bedOrder++;
                    if ($bedOrder > $soGiuongToiDa) {
                        break; // Ràng buộc tuyệt đối: không vượt quá số giường tối đa theo loại phòng
                    }

                    $kyHieu = $bInfo['kyHieu'];
                    $maGiuong = $cleanPrefix . '-' . $kyHieu;
                    $viTri = $bInfo['viTriTang'];
                    $stGiuong = $bInfo['trangThaiGiuong'];
                    $assignedBedKeys[] = $kyHieu;

                    $mssvInput = trim((string)$bInfo['maSV']);
                    $canAdmitStudent = false;

                    if (!empty($mssvInput)) {
                        if ($admittedResidentCount < $soGiuongToiDa) {
                            $canAdmitStudent = true;
                            $admittedResidentCount++;
                        } else {
                            // Vượt mức quy chuẩn sức chứa -> chuyển trạng thái giường về Trống
                            $stGiuong = 'Trống';
                        }
                    }

                    // Tạo hoặc cập nhật giường
                    Database::execute("
                        INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                        VALUES (:mg, :mp, :kh, :vt, :st)
                        ON DUPLICATE KEY UPDATE ViTriTangGiuong = VALUES(ViTriTangGiuong), TrangThaiGiuong = VALUES(TrangThaiGiuong)
                    ", [
                        'mg' => $maGiuong,
                        'mp' => $maPhong,
                        'kh' => $kyHieu,
                        'vt' => $viTri,
                        'st' => $stGiuong
                    ]);

                    // 2. Nếu tiếp nhận sinh viên vào giường này -> Xử lý cư trú
                    if ($canAdmitStudent) {
                        // Tìm sinh viên trong hệ thống (theo MaSinhVien, MSSVTaiTruong, hoặc SoCCCD)
                        $sv = Database::fetch("
                            SELECT * FROM SinhVien 
                            WHERE MaSinhVien = :s1 OR MSSVTaiTruong = :s2 OR (SoCCCD IS NOT NULL AND SoCCCD = :s3)
                            LIMIT 1
                        ", ['s1' => $mssvInput, 's2' => $mssvInput, 's3' => $mssvInput]);

                        if ($sv) {
                            $realMsv = $sv['MaSinhVien'];
                            Database::execute("UPDATE SinhVien SET TrangThaiNoiTru = 'Đang nội trú' WHERE MaSinhVien = :sv", ['sv' => $realMsv]);
                        } else {
                            // Tạo tài khoản và hồ sơ Sinh viên mới
                            $realMsv = $mssvInput;
                            $tkCheck = Database::fetch("SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :u", ['u' => $realMsv]);
                            if ($tkCheck) {
                                $idTk = (int)$tkCheck['MaTaiKhoan'];
                            } else {
                                Database::execute("
                                    INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, DangHoatDong)
                                    VALUES (:u, :p, 'SINH_VIEN', 1)
                                ", [
                                    'u' => $realMsv,
                                    'p' => Auth::hashPassword($realMsv . '@123')
                                ]);
                                $idTk = (int)Database::lastInsertId();
                            }

                            $truongVal = !empty($bInfo['truong']) ? $bInfo['truong'] : $defaultTruong;
                            $tenVal = !empty($bInfo['hoTen']) ? $bInfo['hoTen'] : ('Sinh viên ' . $realMsv);
                            $gtVal = !empty($bInfo['gioiTinh']) ? $bInfo['gioiTinh'] : 'Nam';
                            $cccdVal = !empty($bInfo['cccd']) ? $bInfo['cccd'] : null;
                            $sdtVal = !empty($bInfo['sdt']) ? $bInfo['sdt'] : null;
                            $emailVal = !empty($bInfo['email']) ? $bInfo['email'] : ($realMsv . '@student.vnuhcm.edu.vn');

                            Database::execute("
                                INSERT INTO SinhVien (MaSinhVien, MaTaiKhoan, MaTruong, MSSVTaiTruong, HoVaTen, GioiTinh, SoCCCD, SoDienThoai, Email, TrangThaiNoiTru)
                                VALUES (:msv, :tk, :tr, :mssv, :ten, :gt, :cccd, :sdt, :mail, 'Đang nội trú')
                                ON DUPLICATE KEY UPDATE HoVaTen = VALUES(HoVaTen), TrangThaiNoiTru = 'Đang nội trú'
                            ", [
                                'msv' => $realMsv,
                                'tk' => $idTk,
                                'tr' => $truongVal,
                                'mssv' => $realMsv,
                                'ten' => $tenVal,
                                'gt' => $gtVal,
                                'cccd' => $cccdVal,
                                'sdt' => $sdtVal,
                                'mail' => $emailVal
                            ]);
                            $newStudentCount++;
                        }

                        // Tạo / cập nhật Hồ sơ đăng ký (HoSoDangKy)
                        $maHoSo = 'HS_' . $maGiuong . '_' . $realMsv;
                        $existingHs = Database::fetch("SELECT MaHoSo FROM HoSoDangKy WHERE MaHoSo = :hs", ['hs' => $maHoSo]);
                        if (!$existingHs) {
                            Database::execute("
                                INSERT INTO HoSoDangKy (MaHoSo, MaSinhVien, MaDot, MaGiuong, LoaiLuongDangKy, TrangThaiHoSo, ThoiGianNop, ThoiGianDuyet)
                                VALUES (:hs, :sv, :dot, :mg, 'Chính thức', 'Đã duyệt', NOW(), NOW())
                            ", [
                                'hs' => $maHoSo,
                                'sv' => $realMsv,
                                'dot' => $defaultMaDot,
                                'mg' => $maGiuong
                            ]);
                        }

                        // 1. THANH LÝ TOÀN BỘ hợp đồng đang hiệu lực trên giường này (loại bỏ tuyệt đối hợp đồng cũ trùng lặp)
                        Database::execute("
                            UPDATE HopDongNoiTru 
                            SET TrangThaiHopDong = 'Đã thanh lý' 
                            WHERE MaGiuong = :mg AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                        ", ['mg' => $maGiuong]);

                        // 2. THANH LÝ bất kỳ hợp đồng đang hiệu lực nào khác của sinh viên này ở giường/phòng khác
                        Database::execute("
                            UPDATE HopDongNoiTru 
                            SET TrangThaiHopDong = 'Đã thanh lý' 
                            WHERE MaSinhVien = :sv AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                        ", ['sv' => $realMsv]);

                        // 3. Tạo hoặc cập nhật Hợp đồng nội trú
                        $maHopDong = 'HD_' . $maGiuong . '_' . $realMsv;
                        Database::execute("
                            INSERT INTO HopDongNoiTru (MaHopDong, MaHoSo, MaSinhVien, MaGiuong, NgayBatDau, NgayKetThuc, NgayNhanPhong, DaNopMauCT01, TrangThaiHopDong)
                            VALUES (:hd, :hs, :sv, :mg, :start, :end, NOW(), :ct01, 'Đang hiệu lực')
                            ON DUPLICATE KEY UPDATE 
                                TrangThaiHopDong = 'Đang hiệu lực', 
                                DaNopMauCT01 = VALUES(DaNopMauCT01), 
                                NgayNhanPhong = COALESCE(NgayNhanPhong, VALUES(NgayNhanPhong))
                        ", [
                            'hd' => $maHopDong,
                            'hs' => $maHoSo,
                            'sv' => $realMsv,
                            'mg' => $maGiuong,
                            'start' => $bInfo['startDate'],
                            'end' => $bInfo['endDate'],
                            'ct01' => $bInfo['hasCT01']
                        ]);

                        // Cập nhật trạng thái Giường
                        Database::execute("UPDATE Giuong SET TrangThaiGiuong = 'Đã có người ở' WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
                        $assignedStudentCount++;

                        if ($bInfo['isTP']) {
                            $truongPhongMaSV = $realMsv;
                        }
                    } else {
                        // Nếu giường trống hoặc bị từ chối do vượt quá sức chứa: thanh lý mọi hợp đồng cũ trên giường này
                        Database::execute("
                            UPDATE HopDongNoiTru 
                            SET TrangThaiHopDong = 'Đã thanh lý' 
                            WHERE MaGiuong = :mg AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                        ", ['mg' => $maGiuong]);
                        Database::execute("UPDATE Giuong SET TrangThaiGiuong = 'Trống' WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
                    }
                }

                // 3. Tự động sinh thêm các giường còn thiếu cho đủ $soGiuongToiDa
                for ($i = 1; $i <= $soGiuongToiDa; $i++) {
                    $kCheck = 'G' . $i;
                    if (!in_array($kCheck, $assignedBedKeys)) {
                        $mGiuongAuto = $cleanPrefix . '-' . $kCheck;
                        Database::execute("
                            INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                            VALUES (:mg, :mp, :kh, :vt, 'Trống')
                            ON DUPLICATE KEY UPDATE ViTriTangGiuong = VALUES(ViTriTangGiuong)
                        ", [
                            'mg' => $mGiuongAuto,
                            'mp' => $maPhong,
                            'kh' => $kCheck,
                            'vt' => ($i % 2 === 1) ? 1 : 2
                        ]);
                    }
                }

                // Gán Trưởng phòng nếu có
                if ($truongPhongMaSV) {
                    Database::execute("UPDATE Phong SET TruongPhong = :tp WHERE MaPhong = :mp", ['tp' => $truongPhongMaSV, 'mp' => $maPhong]);
                }

                // Đồng bộ lại sĩ số và cập nhật trạng thái phòng (Đang sử dụng / Còn chỗ)
                $this->syncRoomOccupancy($maPhong, true);
            }

            $msg = "Nhập danh sách phòng thành công! Đã thêm mới {$createdCount} phòng, cập nhật {$updatedCount} phòng";
            if ($skippedCount > 0) {
                $msg .= ", bỏ qua {$skippedCount} phòng trùng lặp";
            }
            if ($assignedStudentCount > 0) {
                $msg .= ". Đồng thời đã xếp {$assignedStudentCount} sinh viên vào giường (khởi tạo mới {$newStudentCount} hồ sơ SV)";
            }
            $msg .= "!";

            if (!empty($warningRooms)) {
                Session::flash('warning', "<b>⚠️ CẢNH BÁO VƯỢT QUÁ SỨC CHỨA PHÒNG:</b><br>" . implode('<br>', $warningRooms));
            }

            Session::flash('success', $msg);
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý tệp CSV: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms?toa=' . urlencode($maToa));
    }
}
