<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\RoomConsolidationService;
use App\Services\AttendanceDisciplineService;

class DashboardController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        // Hồ sơ chờ duyệt
        $pendingApplications = [];
        $consolidation = [];
        $absenceWarnings = [];
        $maintenanceTickets = [];

        try {
            $pendingApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.GioiTinh, g.MaPhong, t.TenTruong
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE hs.TrangThaiHoSo = 'Chờ duyệt' AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianNop ASC
            ", ['cum' => $maCum]);

            // Đề xuất dồn phòng (Chức năng 3)
            $consolidation = RoomConsolidationService::getUnderOccupiedRooms($maCum);

            // Cảnh báo vắng mặt 30 ngày (Chức năng 8)
            $absenceWarnings = AttendanceDisciplineService::detectLongTermAbsence(30);

            // Phiếu báo hỏng cần xử lý
            $maintenanceTickets = Database::fetchAll("
                SELECT pb.*, sv.HoVaTen AS NguoiBao, tb.TenThietBi, p.SoPhong, tn.TenToaNha
                FROM PhieuBaoHong pb
                JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
                JOIN Phong p ON pb.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
                WHERE tn.MaCum = :cum AND pb.TrangThaiSua IN ('Chờ tiếp nhận', 'Chờ xử lý')
                ORDER BY pb.NgayBao DESC
            ", ['cum' => $maCum]);
        } catch (\Throwable $e) {
            // Dữ liệu sườn mẫu dự phòng
        }

        $this->view('staff.dashboard', [
            'user' => $user,
            'maCum' => $maCum,
            'pendingApplications' => $pendingApplications,
            'consolidation' => $consolidation,
            'absenceWarnings' => $absenceWarnings,
            'maintenanceTickets' => $maintenanceTickets
        ]);
    }

    public function rooms(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.rooms', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function applications(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.applications', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function utilities(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.utilities', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function requests(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.requests', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function maintenance(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.maintenance', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function discipline(): void {
        $user = Auth::user();
        $maCum = $user['MaCumQuanLy'] ?? 'AG';

        $this->view('staff.discipline', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function approveApplication(): void {
        $request = new \App\Core\Request();
        $maHoSo = $request->input('maHoSo');
        $userId = Auth::id() ?? 3;

        if ($maHoSo) {
            \App\Services\AutoReleaseService::approveApplication($maHoSo, $userId, 72);
        }

        $this->redirect('staff/dashboard');
    }

    // ==========================================================================
    // PHÂN HỆ QUẢN LÝ KHU, CỤM & TÒA NHÀ (STAFF ZONES & INFRASTRUCTURE)
    // CƠ CHẾ PHÂN QUYỀN & PHÊ DUYỆT ĐỀ XUẤT THAY ĐỔI
    // ==========================================================================

    /**
     * Xác định quyền hạn & phạm vi của Cán bộ đang đăng nhập
     */
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));

        // 1. Quản lý toàn KTX (NV_QUAN_LY_KTX / ADMIN / QuanTriVien) hoặc không bị giới hạn cụm
        if (
            in_array($role, ['NV_QUAN_LY_KTX', 'ADMIN', 'QuanTriVien'], true) ||
            ($user['VaiTro'] ?? '') === 'QuanTriVien' ||
            (empty($cumQuanLy) && in_array($role, ['NV_QUAN_LY_KTX', 'ADMIN'], true))
        ) {
            return [
                'level' => 'FULL',
                'title' => 'Quản lý toàn KTX (Toàn quyền hệ thống & Phê duyệt đề xuất)',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'canDirectAction' => true,
                'canApprove' => true
            ];
        }

        // 2. Trưởng khu KTX (phạm vi: Khu A hoặc Khu B...)
        if ($role === 'TRUONG_KHU' || str_starts_with($cumQuanLy, 'KHU_')) {
            $maKhu = str_starts_with($cumQuanLy, 'KHU_') ? substr($cumQuanLy, 4) : 'A';
            return [
                'level' => 'KHU',
                'title' => "Trưởng khu KTX (Phạm vi: Khu {$maKhu})",
                'badgeClass' => 'badge-info',
                'maKhu' => $maKhu,
                'maCum' => null,
                'canDirectAction' => false,
                'canApprove' => false
            ];
        }

        // 3. Trưởng cụm / Nhân viên thường phụ trách Cụm nhà
        $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
        $cumRow = Database::fetch("SELECT MaKhu, TenCum FROM CumNha WHERE MaCum = :c", ['c' => $maCum]);
        $maKhu = $cumRow['MaKhu'] ?? 'A';
        $tenCum = $cumRow['TenCum'] ?? $maCum;

        return [
            'level' => 'CUM',
            'title' => "Trưởng Ban quản lý cụm (Phạm vi: Cụm {$tenCum} - Khu {$maKhu})",
            'badgeClass' => 'badge-warning',
            'maKhu' => $maKhu,
            'maCum' => $maCum,
            'canDirectAction' => true,
            'canApprove' => true
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
            if ($scope['level'] === 'FULL') {
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
            if ($scope['level'] === 'FULL') {
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
            if ($scope['level'] === 'FULL') {
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
            } else {
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
     */
    public function saveToa(): void
    {
        $request = new Request();
        $maToa = strtoupper(trim((string)$request->input('maToaNha', '')));
        $maCum = trim((string)$request->input('maCum', ''));
        $tenToa = trim((string)$request->input('tenToaNha', ''));
        $tongSoTang = max(1, (int)$request->input('tongSoTang', 1));
        $gioiTinhQuyDinh = trim((string)$request->input('gioiTinhQuyDinh', 'Nam'));
        $trangThaiToaNha = trim((string)$request->input('trangThaiToaNha', 'Đang hoạt động'));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

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
     */
    public function deleteToa(): void
    {
        $request = new Request();
        $maToa = trim((string)$request->input('maToaNha', ''));

        if (empty($maToa)) {
            Session::flash('error', 'Mã tòa không hợp lệ!');
            $this->redirect('staff/zones');
            return;
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
     */
    public function saveCum(): void
    {
        $request = new Request();
        $maCum = strtoupper(trim((string)$request->input('maCum', '')));
        $maKhu = strtoupper(trim((string)$request->input('maKhu', '')));
        $tenCum = trim((string)$request->input('tenCum', ''));
        $viTriVanPhong = trim((string)$request->input('viTriVanPhong', ''));
        $hoTenTruongCum = trim((string)$request->input('hoTenTruongCum', ''));
        $soDienThoaiCum = trim((string)$request->input('soDienThoaiCum', ''));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

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
                Session::flash('success', "Đã tạo mới Cụm nhà {$tenCum} ({$maCum}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi lưu Cụm nhà: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Cụm nhà
     */
    public function deleteCum(): void
    {
        $request = new Request();
        $maCum = trim((string)$request->input('maCum', ''));

        if (empty($maCum)) {
            Session::flash('error', 'Mã cụm không hợp lệ!');
            $this->redirect('staff/zones');
            return;
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
     */
    public function saveKhu(): void
    {
        $request = new Request();
        $maKhu = strtoupper(trim((string)$request->input('maKhu', '')));
        $tenKhu = trim((string)$request->input('tenKhu', ''));
        $diaChi = trim((string)$request->input('diaChi', ''));
        $soDienThoaiTruc = trim((string)$request->input('soDienThoaiTruc', ''));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

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
                Session::flash('success', "Đã cập nhật Khu KTX {$tenKhu} ({$maKhu}) thành công!");
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
                Session::flash('success', "Đã tạo mới Khu KTX {$tenKhu} ({$maKhu}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi lưu Khu KTX: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }

    /**
     * Xóa Khu KTX
     */
    public function deleteKhu(): void
    {
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
     */
    public function saveRoom(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maToaNha = trim((string)$request->input('maToaNha', ''));
        $maLoaiPhong = trim((string)$request->input('maLoaiPhong', ''));
        $soPhong = trim((string)$request->input('soPhong', ''));
        $tang = max(1, (int)$request->input('tang', 1));
        $trangThaiPhong = trim((string)$request->input('trangThaiPhong', 'Còn chỗ'));
        $isEdit = (int)$request->input('isEdit', 0) === 1;

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
                    'mp' => $maPhong, 'toa' => $maToaNha, 'so' => $soPhong
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
                    'mp' => $maPhong, 'toa' => $maToaNha, 'lp' => $maLoaiPhong, 'so' => $soPhong, 'tang' => $tang, 'st' => $trangThaiPhong
                ]);

                for ($i = 1; $i <= $soGiuongToiDa; $i++) {
                    $maGiuong = str_replace('.', '', $maPhong) . '-G' . $i;
                    Database::execute("
                        INSERT INTO Giuong (MaGiuong, MaPhong, KyHieuGiuong, ViTriTangGiuong, TrangThaiGiuong)
                        VALUES (:mg, :mp, :kh, :vt, 'Trống')
                        ON DUPLICATE KEY UPDATE TrangThaiGiuong = VALUES(TrangThaiGiuong)
                    ", [
                        'mg' => $maGiuong, 'mp' => $maPhong, 'kh' => 'G' . $i, 'vt' => ($i % 2 === 1) ? 1 : 2
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
     */
    public function deleteRoom(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/zones');
            return;
        }

        $occupied = Database::fetch("
            SELECT COUNT(*) AS total FROM HopDongNoiTru hd JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            WHERE g.MaPhong = :mp AND hd.TrangThaiHopDong = 'Đang hiệu lực'
        ", ['mp' => $maPhong]);

        if (($occupied['total'] ?? 0) > 0) {
            Session::flash('error', "Không thể xóa phòng {$maPhong} vì hiện có {$occupied['total']} sinh viên đang ở (Quy tắc bảo toàn dữ liệu)!");
            $this->redirect('staff/zones');
            return;
        }

        try {
            Database::execute("DELETE FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Database::execute("DELETE FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã xóa phòng {$maPhong} và các giường trống liên kết thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/zones');
    }
}
