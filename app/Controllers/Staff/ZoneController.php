<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Controller: Quản lý Hạ tầng Ký túc xá (Khu, Cụm, Tòa, Phòng)
 * View: views/staff/zones.php
 * Phân quyền phân cấp:
 * - QUAN_LY / TRUONG_KHU: Toàn quyền hoặc quản lý theo Khu
 * - TRUONG_CUM: Quản lý theo Cụm nhà phụ trách
 * - TRUONG_TOA: Quản lý theo Tòa nhà phụ trách
 */
class ZoneController extends Controller
{
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));

        // 0. Quản trị viên hệ thống (ADMIN): Đã tách biệt hoàn toàn nghiệp vụ, không trực tiếp quản lý hạ tầng KTX
        if ($role === 'ADMIN' || ($user['VaiTro'] ?? '') === 'QuanTriVien') {
            return [
                'level' => 'VIEW_ONLY',
                'title' => 'Quản trị viên (Chỉ xem)',
                'badgeClass' => 'badge-info',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canCreateKhu' => false,
                'canReadKhu' => true,
                'canUpdateKhu' => false,
                'canDeleteKhu' => false,
                'canCreateCum' => false,
                'canReadCum' => true,
                'canUpdateCum' => false,
                'canDeleteCum' => false,
                'canCreateToa' => false,
                'canReadToa' => true,
                'canUpdateToa' => false,
                'canDeleteToa' => false,
                'canCreatePhong' => false,
                'canReadPhong' => true,
                'canUpdatePhong' => false,
                'canDeletePhong' => false,
                'canCrudKhu' => false,
                'canCrudCum' => false,
                'canCrudToa' => false,
                'canCrudPhong' => false,
                'defaultTab' => 'phong'
            ];
        }

        // 1. Quản lý toàn KTX (NV_QUAN_LY_KTX) hoặc cán bộ quản lý KTX cấp cao
        if (
            $role === 'NV_QUAN_LY_KTX' ||
            (empty($cumQuanLy) && $role === 'NV_QUAN_LY_KTX')
        ) {
            return [
                'level' => 'FULL',
                'title' => 'Quản lý KTX',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canCreateKhu' => true,
                'canReadKhu' => true,
                'canUpdateKhu' => true,
                'canDeleteKhu' => true,
                'canCreateCum' => true,
                'canReadCum' => true,
                'canUpdateCum' => true,
                'canDeleteCum' => true,
                'canCreateToa' => true,
                'canReadToa' => true,
                'canUpdateToa' => true,
                'canDeleteToa' => true,
                'canCreatePhong' => true,
                'canReadPhong' => true,
                'canUpdatePhong' => true,
                'canDeletePhong' => true,
                'canCrudKhu' => true,
                'canCrudCum' => true,
                'canCrudToa' => true,
                'canCrudPhong' => true,
                'defaultTab' => 'phong'
            ];
        }

        // 2. Trưởng khu KTX (phạm vi: Khu A hoặc Khu B...)
        if ($role === 'TRUONG_KHU' || str_starts_with($cumQuanLy, 'KHU_')) {
            $maKhu = str_starts_with($cumQuanLy, 'KHU_') ? substr($cumQuanLy, 4) : (!empty($cumQuanLy) ? $cumQuanLy : 'A');
            return [
                'level' => 'KHU',
                'title' => "Quản lý Khu KTX ({$maKhu})",
                'badgeClass' => 'badge-info',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canCreateKhu' => false,
                'canReadKhu' => true,
                'canUpdateKhu' => true,
                'canDeleteKhu' => false,
                'canCreateCum' => true,
                'canReadCum' => true,
                'canUpdateCum' => true,
                'canDeleteCum' => true,
                'canCreateToa' => false,
                'canReadToa' => true,
                'canUpdateToa' => false,
                'canDeleteToa' => false,
                'canCreatePhong' => false,
                'canReadPhong' => true,
                'canUpdatePhong' => false,
                'canDeletePhong' => false,
                'canCrudKhu' => true,
                'canCrudCum' => true,
                'canCrudToa' => false,
                'canCrudPhong' => false,
                'defaultTab' => 'khu'
            ];
        }

        // 3. Trưởng tòa nhà (phạm vi: Tòa A01, H01, B02...)
        $isToaRole = ($role === 'TRUONG_TOA');
        if (!$isToaRole && !empty($cumQuanLy)) {
            $toaCheck = Database::fetch("SELECT MaToaNha FROM ToaNha WHERE MaToaNha = :t", ['t' => $cumQuanLy]);
            if ($toaCheck) {
                $isToaRole = true;
            }
        }

        if ($isToaRole) {
            $maToa = !empty($cumQuanLy) ? $cumQuanLy : 'A01';
            $toaRow = Database::fetch("
                SELECT t.TenToaNha, t.MaCum, c.MaKhu, c.TenCum
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                WHERE t.MaToaNha = :t
            ", ['t' => $maToa]);

            $tenToa = $toaRow['TenToaNha'] ?? $maToa;
            $maCum = $toaRow['MaCum'] ?? 'AG';
            $maKhu = $toaRow['MaKhu'] ?? 'A';

            return [
                'level' => 'TOA',
                'title' => "Quản lý Tòa nhà ({$tenToa})",
                'badgeClass' => 'badge-success',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => $maToa,
                'canCreateKhu' => false,
                'canReadKhu' => true,
                'canUpdateKhu' => false,
                'canDeleteKhu' => false,
                'canCreateCum' => false,
                'canReadCum' => true,
                'canUpdateCum' => false,
                'canDeleteCum' => false,
                'canCreateToa' => false,
                'canReadToa' => true,
                'canUpdateToa' => true,
                'canDeleteToa' => false,
                'canCreatePhong' => true,
                'canReadPhong' => true,
                'canUpdatePhong' => true,
                'canDeletePhong' => true,
                'canCrudKhu' => false,
                'canCrudCum' => false,
                'canCrudToa' => true,
                'canCrudPhong' => true,
                'defaultTab' => 'toa'
            ];
        }

        // 4. Trưởng cụm nhà (phạm vi: Cụm AG, AH, BA, BB, BC...)
        $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
        $cumRow = Database::fetch("SELECT MaKhu, TenCum FROM CumNha WHERE MaCum = :c", ['c' => $maCum]);
        $maKhu = $cumRow['MaKhu'] ?? 'A';
        $tenCum = $cumRow['TenCum'] ?? $maCum;

        return [
            'level' => 'CUM',
            'title' => "Quản lý Cụm nhà ({$tenCum})",
            'badgeClass' => 'badge-warning',
            'maKhu' => $maKhu,
            'maCum' => $maCum,
            'maToa' => null,
            'canCreateKhu' => false,
            'canReadKhu' => true,
            'canUpdateKhu' => false,
            'canDeleteKhu' => false,
            'canCreateCum' => false,
            'canReadCum' => true,
            'canUpdateCum' => true,
            'canDeleteCum' => false,
            'canCreateToa' => true,
            'canReadToa' => true,
            'canUpdateToa' => true,
            'canDeleteToa' => true,
            'canCreatePhong' => false,
            'canReadPhong' => true,
            'canUpdatePhong' => false,
            'canDeletePhong' => false,
            'canCrudKhu' => false,
            'canCrudCum' => true,
            'canCrudToa' => true,
            'canCrudPhong' => false,
            'defaultTab' => 'cum'
        ];
    }

    /**
     * Màn hình Quản lý Khu, Cụm, Tòa & Phòng ở cho Staff
     */
    public function zones(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $khuList = [];
        $cumList = [];
        $toaList = [];
        $phongList = [];
        $giuongByPhong = [];
        $loaiPhongList = [];
        $truongCumList = [];

        try {
            // Danh sách Khu
            if (in_array($scope['level'], ['FULL', 'VIEW_ONLY'], true)) {
                $khuList = Database::fetchAll("
                    SELECT k.*,
                           (SELECT COUNT(*) FROM CumNha c WHERE c.MaKhu = k.MaKhu) AS TongCum,
                           (SELECT COUNT(*) FROM ToaNha t JOIN CumNha c ON t.MaCum = c.MaCum WHERE c.MaKhu = k.MaKhu) AS TongToa,
                           (SELECT COUNT(*) FROM Phong p JOIN ToaNha t ON p.MaToaNha = t.MaToaNha JOIN CumNha c ON t.MaCum = c.MaCum WHERE c.MaKhu = k.MaKhu) AS TongPhong
                    FROM KhuKTX k
                    ORDER BY k.MaKhu ASC
                ");
            } else {
                $khuList = Database::fetchAll("
                    SELECT k.*,
                           (SELECT COUNT(*) FROM CumNha c WHERE c.MaKhu = k.MaKhu) AS TongCum,
                           (SELECT COUNT(*) FROM ToaNha t JOIN CumNha c ON t.MaCum = c.MaCum WHERE c.MaKhu = k.MaKhu) AS TongToa,
                           (SELECT COUNT(*) FROM Phong p JOIN ToaNha t ON p.MaToaNha = t.MaToaNha JOIN CumNha c ON t.MaCum = c.MaCum WHERE c.MaKhu = k.MaKhu) AS TongPhong
                    FROM KhuKTX k
                    WHERE k.MaKhu = :khu
                    ORDER BY k.MaKhu ASC
                ", ['khu' => $scope['maKhu']]);
            }

            // Danh sách Cụm
            if (in_array($scope['level'], ['FULL', 'VIEW_ONLY'], true)) {
                $cumList = Database::fetchAll("
                    SELECT c.*, k.TenKhu,
                           (SELECT COUNT(*) FROM ToaNha t WHERE t.MaCum = c.MaCum) AS TongToa,
                           (SELECT COUNT(*) FROM Phong p JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongGiuong
                    FROM CumNha c
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    ORDER BY c.MaKhu ASC, c.MaCum ASC
                ");
            } elseif ($scope['level'] === 'KHU') {
                $cumList = Database::fetchAll("
                    SELECT c.*, k.TenKhu,
                           (SELECT COUNT(*) FROM ToaNha t WHERE t.MaCum = c.MaCum) AS TongToa,
                           (SELECT COUNT(*) FROM Phong p JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongGiuong
                    FROM CumNha c
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE c.MaKhu = :khu
                    ORDER BY c.MaCum ASC
                ", ['khu' => $scope['maKhu']]);
            } else {
                $cumList = Database::fetchAll("
                    SELECT c.*, k.TenKhu,
                           (SELECT COUNT(*) FROM ToaNha t WHERE t.MaCum = c.MaCum) AS TongToa,
                           (SELECT COUNT(*) FROM Phong p JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong JOIN ToaNha t ON p.MaToaNha = t.MaToaNha WHERE t.MaCum = c.MaCum) AS TongGiuong
                    FROM CumNha c
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE c.MaCum = :cum
                ", ['cum' => $scope['maCum']]);
            }

            // Danh sách Tòa nhà
            if (in_array($scope['level'], ['FULL', 'VIEW_ONLY'], true)) {
                $toaList = Database::fetchAll("
                    SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu,
                           (SELECT COUNT(*) FROM Phong p WHERE p.MaToaNha = t.MaToaNha) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong WHERE p.MaToaNha = t.MaToaNha) AS TongGiuong
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    ORDER BY c.MaKhu ASC, c.MaCum ASC, t.MaToaNha ASC
                ");
            } elseif ($scope['level'] === 'KHU') {
                $toaList = Database::fetchAll("
                    SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu,
                           (SELECT COUNT(*) FROM Phong p WHERE p.MaToaNha = t.MaToaNha) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong WHERE p.MaToaNha = t.MaToaNha) AS TongGiuong
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE c.MaKhu = :khu
                    ORDER BY c.MaCum ASC, t.MaToaNha ASC
                ", ['khu' => $scope['maKhu']]);
            } elseif ($scope['level'] === 'CUM') {
                $toaList = Database::fetchAll("
                    SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu,
                           (SELECT COUNT(*) FROM Phong p WHERE p.MaToaNha = t.MaToaNha) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong WHERE p.MaToaNha = t.MaToaNha) AS TongGiuong
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE t.MaCum = :cum
                    ORDER BY t.MaToaNha ASC
                ", ['cum' => $scope['maCum']]);
            } else {
                // Cấp TOA: Chỉ tải tòa nhà phân công
                $toaList = Database::fetchAll("
                    SELECT t.*, c.TenCum, c.MaKhu, k.TenKhu,
                           (SELECT COUNT(*) FROM Phong p WHERE p.MaToaNha = t.MaToaNha) AS TongPhong,
                           (SELECT COUNT(*) FROM Giuong g JOIN Phong p ON g.MaPhong = p.MaPhong WHERE p.MaToaNha = t.MaToaNha) AS TongGiuong
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE t.MaToaNha = :toa
                    ORDER BY t.MaToaNha ASC
                ", ['toa' => $scope['maToa']]);
            }

            // Gợi ý Trưởng cụm
            $cumExisting = Database::fetchAll("
                SELECT DISTINCT HoTenTruongCum, SoDienThoaiCum, MaCum 
                FROM CumNha 
                WHERE HoTenTruongCum IS NOT NULL AND HoTenTruongCum != ''
            ");
            $leaderMap = [];
            foreach ($cumExisting as $ce) {
                $name = trim($ce['HoTenTruongCum']);
                if (!empty($name)) {
                    $leaderMap[$name] = [
                        'HoVaTen' => $name,
                        'SoDienThoai' => $ce['SoDienThoaiCum'] ?? '',
                        'ChucVu' => 'Trưởng Ban quản lý cụm ' . $ce['MaCum'],
                        'MaCumQuanLy' => $ce['MaCum']
                    ];
                }
            }
            $truongCumList = array_values($leaderMap);

            // Danh sách Phòng ở
            $phongSql = "
                SELECT p.*, tn.TenToaNha, tn.MaCum, cn.TenCum, cn.MaKhu, k.TenKhu,
                       lp.TenLoaiPhong, lp.SoGiuongToiDa, lp.DonGiaLuuTruChuan,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong) AS TongGiuong,
                       (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Trống') AS SoGiuongTrong
                FROM Phong p
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha cn ON tn.MaCum = cn.MaCum
                JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            ";
            $phongParams = [];
            if ($scope['level'] === 'KHU') {
                $phongSql .= " WHERE cn.MaKhu = :khu ";
                $phongParams['khu'] = $scope['maKhu'];
            } elseif ($scope['level'] === 'CUM') {
                $phongSql .= " WHERE cn.MaCum = :cum ";
                $phongParams['cum'] = $scope['maCum'];
            } elseif ($scope['level'] === 'TOA') {
                $phongSql .= " WHERE tn.MaToaNha = :toa ";
                $phongParams['toa'] = $scope['maToa'];
            }
            $phongSql .= " ORDER BY cn.MaCum ASC, tn.MaToaNha ASC, p.Tang ASC, p.SoPhong ASC";
            $phongList = Database::fetchAll($phongSql, $phongParams);

            $giuongList = Database::fetchAll("SELECT * FROM Giuong ORDER BY MaPhong ASC, KyHieuGiuong ASC");
            $giuongByPhong = [];
            foreach ($giuongList as $g) {
                $giuongByPhong[$g['MaPhong']][] = $g;
            }

            $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY SoGiuongToiDa ASC");
        } catch (\Throwable $e) {
            $phongList = [];
            $giuongByPhong = [];
            $loaiPhongList = [];
        }

        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        $this->view('staff.zones', [
            'user' => $user,
            'scope' => $scope,
            'khuList' => $khuList,
            'cumList' => $cumList,
            'toaList' => $toaList,
            'phongList' => $phongList,
            'giuongByPhong' => $giuongByPhong,
            'loaiPhongList' => $loaiPhongList,
            'truongCumList' => $truongCumList,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning
        ]);
    }

    /**
     * Thêm / Sửa Tòa nhà trực tiếp
     * Quyền:
     * - Create Tòa: Quản lý Cụm (CD Tòa) hoặc Quản lý KTX
     * - Update Tòa: Trưởng tòa phụ trách (RU Tòa) hoặc Quản lý KTX
     */
    public function saveToa(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $request = new Request();
        $maToa = strtoupper(trim((string)$request->input('maToaNha', '')));
        $maCum = trim((string)$request->input('maCum', ''));
        $tenToa = trim((string)$request->input('tenToaNha', ''));
        $tongSoTang = max(1, (int)$request->input('tongSoTang', 1));
        $gioiTinhQuyDinh = trim((string)$request->input('gioiTinhQuyDinh', 'Nam'));
        $trangThaiToaNha = trim((string)$request->input('trangThaiToaNha', 'Đang hoạt động'));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

        if ($isEdit) {
            if (empty($scope['canUpdateToa'])) {
                Session::flash('error', 'Bạn không có quyền sửa Tòa nhà! Nghiệp vụ này dành cho Trưởng tòa phụ trách (RU tòa) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'TOA' && !empty($scope['maToa']) && $maToa !== $scope['maToa']) {
                Session::flash('error', "Bạn chỉ được quyền sửa Tòa nhà được phân công ({$scope['maToa']})!");
                $this->redirect('staff/zones');
                return;
            }
        } else {
            if (empty($scope['canCreateToa'])) {
                Session::flash('error', 'Bạn không có quyền tạo mới Tòa nhà! Nghiệp vụ này dành cho Quản lý cụm (CD tòa) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'CUM' && !empty($scope['maCum']) && $maCum !== $scope['maCum']) {
                Session::flash('error', "Bạn chỉ được quyền tạo Tòa nhà trực thuộc Cụm {$scope['maCum']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        if (empty($maToa) || empty($maCum) || empty($tenToa)) {
            Session::flash('error', 'Vui lòng điền đầy đủ Mã tòa, Cụm trực thuộc và Tên tòa nhà!');
            $this->redirect('staff/zones');
            return;
        }

        try {
            if ($isEdit) {
                Database::execute("
                    UPDATE ToaNha 
                    SET MaCum = :cum, TenToaNha = :ten, TongSoTang = :tang, GioiTinhQuyDinh = :gt, TrangThaiToaNha = :st
                    WHERE MaToaNha = :ma
                ", [
                    'cum' => $maCum,
                    'ten' => $tenToa,
                    'tang' => $tongSoTang,
                    'gt' => $gioiTinhQuyDinh,
                    'st' => $trangThaiToaNha,
                    'ma' => $maToa
                ]);
                Session::flash('success', "Đã cập nhật Tòa nhà {$tenToa} ({$maToa}) thành công!");
            } else {
                $exists = Database::fetch("SELECT MaToaNha FROM ToaNha WHERE MaToaNha = :ma", ['ma' => $maToa]);
                if ($exists) {
                    Session::flash('error', "Mã tòa '{$maToa}' đã tồn tại!");
                    $this->redirect('staff/zones');
                    return;
                }
                Database::execute("
                    INSERT INTO ToaNha (MaToaNha, MaCum, TenToaNha, TongSoTang, GioiTinhQuyDinh, TrangThaiToaNha)
                    VALUES (:ma, :cum, :ten, :tang, :gt, :st)
                ", [
                    'ma' => $maToa,
                    'cum' => $maCum,
                    'ten' => $tenToa,
                    'tang' => $tongSoTang,
                    'gt' => $gioiTinhQuyDinh,
                    'st' => $trangThaiToaNha
                ]);
                Session::flash('success', "Đã thêm mới Tòa nhà {$tenToa} ({$maToa}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý Tòa nhà: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Tòa nhà trực tiếp
     * Quyền: Quản lý Cụm (CD Tòa) hoặc Quản lý KTX
     */
    public function deleteToa(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (empty($scope['canDeleteToa'])) {
            Session::flash('error', 'Bạn không có quyền xóa Tòa nhà! Nghiệp vụ này dành cho Quản lý cụm (CD tòa) hoặc Quản lý KTX.');
            $this->redirect('staff/zones');
            return;
        }

        $request = new Request();
        $maToa = trim((string)$request->input('maToaNha', ''));

        if (empty($maToa)) {
            Session::flash('error', 'Mã tòa không hợp lệ!');
            $this->redirect('staff/zones');
            return;
        }

        if ($scope['level'] === 'CUM' && !empty($scope['maCum'])) {
            $toaCheck = Database::fetch("SELECT MaCum FROM ToaNha WHERE MaToaNha = :t", ['t' => $maToa]);
            if (!$toaCheck || $toaCheck['MaCum'] !== $scope['maCum']) {
                Session::flash('error', "Bạn chỉ được quyền xóa Tòa nhà thuộc Cụm {$scope['maCum']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        try {
            $occupied = Database::fetch("
                SELECT COUNT(*) AS total
                FROM HopDongNoiTru hd
                JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                WHERE p.MaToaNha = :toa AND hd.TrangThaiHopDong = 'Đang hiệu lực'
            ", ['toa' => $maToa]);

            if (($occupied['total'] ?? 0) > 0) {
                Session::flash('error', "Không thể xóa Tòa {$maToa} vì hiện có {$occupied['total']} sinh viên đang cư trú (Quy tắc bảo toàn dữ liệu)!");
                $this->redirect('staff/zones');
                return;
            }

            Database::execute("DELETE FROM Giuong WHERE MaPhong IN (SELECT MaPhong FROM Phong WHERE MaToaNha = :toa)", ['toa' => $maToa]);
            Database::execute("DELETE FROM Phong WHERE MaToaNha = :toa", ['toa' => $maToa]);
            Database::execute("DELETE FROM ToaNha WHERE MaToaNha = :toa", ['toa' => $maToa]);
            Session::flash('success', "Đã xóa Tòa nhà {$maToa} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa Tòa nhà: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Thêm / Sửa Cụm nhà trực tiếp
     * Quyền:
     * - Create Cụm: Quản lý Khu (CD Cụm) hoặc Quản lý KTX
     * - Update Cụm: Trưởng cụm phụ trách (RU Cụm) hoặc Quản lý KTX
     */
    public function saveCum(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $request = new Request();
        $maCum = strtoupper(trim((string)$request->input('maCum', '')));
        $maKhu = strtoupper(trim((string)$request->input('maKhu', '')));
        $tenCum = trim((string)$request->input('tenCum', ''));
        $viTriVanPhong = trim((string)$request->input('viTriVanPhong', ''));
        $hoTenTruongCum = trim((string)$request->input('hoTenTruongCum', ''));
        $soDienThoaiCum = trim((string)$request->input('soDienThoaiCum', ''));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

        if ($isEdit) {
            if (empty($scope['canUpdateCum'])) {
                Session::flash('error', 'Bạn không có quyền sửa Cụm nhà! Nghiệp vụ này dành cho Trưởng cụm phụ trách (RU cụm) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'CUM' && !empty($scope['maCum']) && $maCum !== $scope['maCum']) {
                Session::flash('error', "Bạn chỉ được quyền sửa Cụm nhà được phân công ({$scope['maCum']})!");
                $this->redirect('staff/zones');
                return;
            }
        } else {
            if (empty($scope['canCreateCum'])) {
                Session::flash('error', 'Bạn không có quyền thêm mới Cụm nhà! Nghiệp vụ này dành cho Quản lý khu (CD cụm) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'KHU' && !empty($scope['maKhu']) && $maKhu !== $scope['maKhu']) {
                Session::flash('error', "Bạn chỉ được quyền tạo Cụm nhà trực thuộc Khu {$scope['maKhu']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        if (empty($maCum) || empty($maKhu) || empty($tenCum)) {
            Session::flash('error', 'Vui lòng điền đầy đủ Mã cụm, Khu trực thuộc và Tên cụm nhà!');
            $this->redirect('staff/zones');
            return;
        }

        try {
            if ($isEdit) {
                Database::execute("
                    UPDATE CumNha 
                    SET MaKhu = :khu, TenCum = :ten, ViTriVanPhong = :vp, HoTenTruongCum = :tc, SoDienThoaiCum = :sdt
                    WHERE MaCum = :ma
                ", [
                    'khu' => $maKhu,
                    'ten' => $tenCum,
                    'vp' => $viTriVanPhong,
                    'tc' => $hoTenTruongCum,
                    'sdt' => $soDienThoaiCum,
                    'ma' => $maCum
                ]);
                Session::flash('success', "Đã cập nhật Cụm nhà {$tenCum} ({$maCum}) thành công!");
            } else {
                $exists = Database::fetch("SELECT MaCum FROM CumNha WHERE MaCum = :ma", ['ma' => $maCum]);
                if ($exists) {
                    Session::flash('error', "Mã cụm '{$maCum}' đã tồn tại!");
                    $this->redirect('staff/zones');
                    return;
                }
                Database::execute("
                    INSERT INTO CumNha (MaCum, MaKhu, TenCum, ViTriVanPhong, HoTenTruongCum, SoDienThoaiCum)
                    VALUES (:ma, :khu, :ten, :vp, :tc, :sdt)
                ", [
                    'ma' => $maCum,
                    'khu' => $maKhu,
                    'ten' => $tenCum,
                    'vp' => $viTriVanPhong,
                    'tc' => $hoTenTruongCum,
                    'sdt' => $soDienThoaiCum
                ]);
                Session::flash('success', "Đã thêm mới Cụm nhà {$tenCum} ({$maCum}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý Cụm nhà: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Cụm nhà
     * Quyền: Quản lý Khu (CD Cụm) hoặc Quản lý KTX
     */
    public function deleteCum(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (empty($scope['canDeleteCum'])) {
            Session::flash('error', 'Bạn không có quyền xóa Cụm nhà! Nghiệp vụ này dành cho Quản lý khu (CD cụm) hoặc Quản lý KTX.');
            $this->redirect('staff/zones');
            return;
        }

        $request = new Request();
        $maCum = trim((string)$request->input('maCum', ''));

        if (empty($maCum)) {
            Session::flash('error', 'Mã cụm không hợp lệ!');
            $this->redirect('staff/zones');
            return;
        }

        if ($scope['level'] === 'KHU' && !empty($scope['maKhu'])) {
            $cumCheck = Database::fetch("SELECT MaKhu FROM CumNha WHERE MaCum = :c", ['c' => $maCum]);
            if (!$cumCheck || $cumCheck['MaKhu'] !== $scope['maKhu']) {
                Session::flash('error', "Bạn chỉ được quyền xóa Cụm nhà thuộc Khu {$scope['maKhu']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        try {
            $childToa = Database::fetch("SELECT COUNT(*) AS total FROM ToaNha WHERE MaCum = :ma", ['ma' => $maCum]);
            if (($childToa['total'] ?? 0) > 0) {
                Session::flash('error', "Không thể xóa Cụm {$maCum} vì đang có {$childToa['total']} Tòa nhà trực thuộc. Vui lòng chuyển hoặc xóa các Tòa nhà trước!");
                $this->redirect('staff/zones');
                return;
            }

            Database::execute("UPDATE TaiKhoan SET MaCumQuanLy = NULL WHERE MaCumQuanLy = :ma", ['ma' => $maCum]);
            Database::execute("DELETE FROM CumNha WHERE MaCum = :ma", ['ma' => $maCum]);
            Session::flash('success', "Đã xóa thành công Cụm nhà {$maCum}!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa Cụm nhà: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Thêm / Sửa Khu KTX
     * Quyền:
     * - Create Khu: Quản lý KTX
     * - Update Khu: Quản lý khu phụ trách (RU Khu) hoặc Quản lý KTX
     */
    public function saveKhu(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $request = new Request();
        $maKhu = strtoupper(trim((string)$request->input('maKhu', '')));
        $tenKhu = trim((string)$request->input('tenKhu', ''));
        $diaChi = trim((string)$request->input('diaChi', ''));
        $soDienThoaiTruc = trim((string)$request->input('soDienThoaiTruc', ''));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

        if ($isEdit) {
            if (empty($scope['canUpdateKhu'])) {
                Session::flash('error', 'Bạn không có quyền cập nhật Khu KTX! Nghiệp vụ này dành cho Quản lý khu (RU khu) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'KHU' && !empty($scope['maKhu']) && $maKhu !== $scope['maKhu']) {
                Session::flash('error', "Bạn chỉ được quyền cập nhật Khu KTX được phân công (Khu {$scope['maKhu']})!");
                $this->redirect('staff/zones');
                return;
            }
        } else {
            if (empty($scope['canCreateKhu'])) {
                Session::flash('error', 'Bạn không có quyền tạo mới Khu KTX! Nghiệp vụ này chỉ dành cho Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
        }

        if (empty($maKhu) || empty($tenKhu)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ Mã khu (VD: A, B...) và Tên khu KTX!');
            $this->redirect('staff/zones');
            return;
        }

        try {
            if ($isEdit) {
                Database::execute("
                    UPDATE KhuKTX 
                    SET TenKhu = :ten, DiaChi = :diaChi, SoDienThoaiTruc = :sdt
                    WHERE MaKhu = :ma
                ", [
                    'ten' => $tenKhu,
                    'diaChi' => $diaChi,
                    'sdt' => $soDienThoaiTruc,
                    'ma' => $maKhu
                ]);
                Session::flash('success', "Đã cập nhật thông tin Khu KTX {$tenKhu} ({$maKhu}) thành công!");
            } else {
                $exists = Database::fetch("SELECT MaKhu FROM KhuKTX WHERE MaKhu = :ma", ['ma' => $maKhu]);
                if ($exists) {
                    Session::flash('error', "Mã khu '{$maKhu}' đã tồn tại!");
                    $this->redirect('staff/zones');
                    return;
                }
                Database::execute("
                    INSERT INTO KhuKTX (MaKhu, TenKhu, DiaChi, SoDienThoaiTruc)
                    VALUES (:ma, :ten, :diaChi, :sdt)
                ", [
                    'ma' => $maKhu,
                    'ten' => $tenKhu,
                    'diaChi' => $diaChi,
                    'sdt' => $soDienThoaiTruc
                ]);
                Session::flash('success', "Đã thêm mới Khu KTX {$tenKhu} ({$maKhu}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý Khu KTX: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Khu KTX
     * Quyền: Quản lý KTX
     */
    public function deleteKhu(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (empty($scope['canDeleteKhu'])) {
            Session::flash('error', 'Bạn không có quyền xóa Khu KTX! Nghiệp vụ này chỉ dành cho Quản lý KTX.');
            $this->redirect('staff/zones');
            return;
        }

        $request = new Request();
        $maKhu = trim((string)$request->input('maKhu', ''));

        if (empty($maKhu)) {
            Session::flash('error', 'Mã khu không hợp lệ!');
            $this->redirect('staff/zones');
            return;
        }

        try {
            $childCum = Database::fetch("SELECT COUNT(*) AS total FROM CumNha WHERE MaKhu = :ma", ['ma' => $maKhu]);
            if (($childCum['total'] ?? 0) > 0) {
                Session::flash('error', "Không thể xóa Khu {$maKhu} vì đang có {$childCum['total']} Cụm nhà trực thuộc!");
                $this->redirect('staff/zones');
                return;
            }

            Database::execute("DELETE FROM KhuKTX WHERE MaKhu = :ma", ['ma' => $maKhu]);
            Session::flash('success', "Đã xóa thành công Khu KTX {$maKhu}!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa Khu KTX: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Thêm / Sửa Phòng ở & Khởi tạo Giường trực tiếp
     * Quyền:
     * - Create Phòng: Trưởng tòa phụ trách (CD Phòng) hoặc Quản lý KTX
     * - Update Phòng: Quản lý KTX (RU Phòng chỉ dành cho Quản lý KTX)
     */
    public function saveRoom(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maToaNha = trim((string)$request->input('maToaNha', ''));
        $maLoaiPhong = trim((string)$request->input('maLoaiPhong', ''));
        $soPhong = trim((string)$request->input('soPhong', ''));
        $tang = max(1, (int)$request->input('tang', 1));
        $trangThaiPhong = trim((string)$request->input('trangThaiPhong', 'Còn chỗ'));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

        if ($isEdit) {
            if (empty($scope['canUpdatePhong'])) {
                Session::flash('error', 'Bạn không có quyền sửa thông tin Phòng ở! Nghiệp vụ này chỉ dành cho Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
        } else {
            if (empty($scope['canCreatePhong'])) {
                Session::flash('error', 'Bạn không có quyền thêm mới Phòng ở! Nghiệp vụ này dành cho Trưởng tòa (CD phòng) hoặc Quản lý KTX.');
                $this->redirect('staff/zones');
                return;
            }
            if ($scope['level'] === 'TOA' && !empty($scope['maToa']) && $maToaNha !== $scope['maToa']) {
                Session::flash('error', "Bạn chỉ được quyền tạo Phòng ở thuộc Tòa nhà {$scope['maToa']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        if (empty($maPhong) && !empty($maToaNha) && !empty($soPhong)) {
            $maPhong = $maToaNha . '.' . $soPhong;
        }

        if (empty($maPhong) || empty($maToaNha) || empty($maLoaiPhong) || empty($soPhong)) {
            Session::flash('error', 'Vui lòng điền đầy đủ Mã phòng, Tòa nhà, Loại phòng và Số phòng!');
            $this->redirect('staff/zones');
            return;
        }

        $loaiPhong = Database::fetch("SELECT * FROM LoaiPhong WHERE MaLoaiPhong = :lp", ['lp' => $maLoaiPhong]);
        $soGiuongToiDa = (int)($loaiPhong['SoGiuongToiDa'] ?? 4);

        try {
            if ($isEdit) {
                Database::execute("
                    UPDATE Phong 
                    SET MaLoaiPhong = :lp, Tang = :tang, TrangThaiPhong = :st
                    WHERE MaPhong = :mp
                ", [
                    'lp' => $maLoaiPhong,
                    'tang' => $tang,
                    'st' => $trangThaiPhong,
                    'mp' => $maPhong
                ]);
                Session::flash('success', "Đã cập nhật thành công phòng {$maPhong}!");
            } else {
                $dup = Database::fetch("SELECT MaPhong FROM Phong WHERE MaPhong = :mp OR (MaToaNha = :toa AND SoPhong = :so)", [
                    'mp' => $maPhong,
                    'toa' => $maToaNha,
                    'so' => $soPhong
                ]);
                if ($dup) {
                    Session::flash('error', "Phòng '{$maPhong}' hoặc số phòng '{$soPhong}' tại tòa '{$maToaNha}' đã tồn tại!");
                    $this->redirect('staff/zones');
                    return;
                }
                Database::execute("
                    INSERT INTO Phong (MaPhong, MaToaNha, MaLoaiPhong, SoPhong, Tang, SoSVDangO, TrangThaiPhong)
                    VALUES (:mp, :toa, :lp, :so, :tang, 0, :st)
                ", [
                    'mp' => $maPhong,
                    'toa' => $maToaNha,
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
            Session::flash('error', 'Lỗi lưu phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Phòng ở trực tiếp
     * Quyền: Trưởng tòa phụ trách (CD Phòng) hoặc Quản lý KTX
     */
    public function deleteRoom(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (empty($scope['canDeletePhong'])) {
            Session::flash('error', 'Bạn không có quyền xóa Phòng ở! Nghiệp vụ này dành cho Trưởng tòa (CD phòng) hoặc Quản lý KTX.');
            $this->redirect('staff/zones');
            return;
        }

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/zones');
            return;
        }

        if ($scope['level'] === 'TOA' && !empty($scope['maToa'])) {
            $roomCheck = Database::fetch("SELECT MaToaNha FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            if (!$roomCheck || $roomCheck['MaToaNha'] !== $scope['maToa']) {
                Session::flash('error', "Bạn chỉ được quyền xóa Phòng thuộc Tòa {$scope['maToa']}!");
                $this->redirect('staff/zones');
                return;
            }
        }

        try {
            $occupied = Database::fetch("SELECT SoSVDangO FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            if (($occupied['SoSVDangO'] ?? 0) > 0) {
                Session::flash('error', "Không thể xóa phòng {$maPhong} vì đang có {$occupied['SoSVDangO']} sinh viên đang cư trú!");
                $this->redirect('staff/zones');
                return;
            }

            Database::execute("DELETE FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Database::execute("DELETE FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã xóa phòng {$maPhong} và toàn bộ giường liên kết!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xóa phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Phê duyệt đề xuất hạ tầng (Stub/Placeholder)
     */
    public function approveProposal(): void
    {
        Session::flash('info', 'Chức năng phê duyệt đề xuất đang được xử lý.');
        $this->redirect('staff/zones');
    }

    /**
     * Từ chối đề xuất hạ tầng (Stub/Placeholder)
     */
    public function rejectProposal(): void
    {
        Session::flash('info', 'Chức năng từ chối đề xuất đang được xử lý.');
        $this->redirect('staff/zones');
    }
}
