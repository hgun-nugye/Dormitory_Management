<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\RoomConsolidationService;
use App\Services\AttendanceDisciplineService;
use App\Services\AutoReleaseService;

class DashboardController extends Controller {
    /**
     * Lấy mã Cụm quản lý của cán bộ đang đăng nhập
     */
    private function getMaCum(): string {
        $user = Auth::user();
        $maCum = 'AG';
        if (!empty($user['profile']['HoVaTen'])) {
            $cum = Database::fetch(
                "SELECT MaCum FROM CumNha WHERE HoTenTruongCum = :name LIMIT 1",
                ['name' => $user['profile']['HoVaTen']]
            );
            if ($cum) {
                $maCum = $cum['MaCum'];
            }
        }
        return $maCum;
    }

    public function index(): void {
        $user = Auth::user();
        $maCum = $this->getMaCum();

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

    public function rooms(): void
    {
        $user = Auth::user();
        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $isTruongToa = ($role === 'TRUONG_TOA');

        // Phân quyền nghiêm ngặt: Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có chức năng quản lý phòng giường
        if (!$isTruongToa) {
            $this->view('staff.rooms', [
                'user' => $user,
                'isTruongToa' => false,
                'roleName' => $user['VaiTro'] ?? $role,
                'flashSuccess' => Session::flash('success'),
                'flashError' => Session::flash('error'),
                'flashWarning' => Session::flash('warning')
            ]);
            return;
        }

        // Với TRUONG_TOA: Xác định tòa nhà được phân công
        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (empty($maToa)) {
            $firstToa = Database::fetch("SELECT MaToaNha FROM ToaNha ORDER BY MaToaNha ASC LIMIT 1");
            $maToa = $firstToa['MaToaNha'] ?? 'A01';
        }

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
        }

        $request = new Request();
        $filterTang = max(0, (int)$request->input('tang', 0));
        $filterLoaiPhong = trim((string)$request->input('loaiPhong', ''));
        $filterTrangThai = trim((string)$request->input('trangThai', ''));
        $searchKeyword = trim((string)$request->input('search', ''));

        // Danh sách phòng thuộc tòa
        $phongSql = "
            SELECT p.*,
                   lp.TenLoaiPhong, lp.SoGiuongToiDa, lp.DonGiaLuuTruChuan, lp.DonGiaDichVuTangThem,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong) AS TongGiuong,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Trống') AS SoGiuongTrong,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Khóa bảo trì') AS SoGiuongBaoTri,
                   (SELECT COUNT(*) FROM Giuong g WHERE g.MaPhong = p.MaPhong AND g.TrangThaiGiuong = 'Đã có người ở') AS SoGiuongCoNguoi,
                   sv.HoVaTen AS TenTruongPhong
            FROM Phong p
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
            LEFT JOIN SinhVien sv ON p.TruongPhong = sv.MaSinhVien
            WHERE p.MaToaNha = :toa
        ";
        $phongParams = ['toa' => $maToa];

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
                WHERE sv2.HoVaTen LIKE :kw3 OR sv2.MSSVTaiTruong LIKE :kw4 OR sv2.SoCCCD LIKE :kw5
            ))";
            $kwVal = "%{$searchKeyword}%";
            $phongParams['kw1'] = $kwVal;
            $phongParams['kw2'] = $kwVal;
            $phongParams['kw3'] = $kwVal;
            $phongParams['kw4'] = $kwVal;
            $phongParams['kw5'] = $kwVal;
        }

        $phongSql .= " ORDER BY p.Tang ASC, p.SoPhong ASC";
        $phongList = Database::fetchAll($phongSql, $phongParams);

        // Danh sách giường và thông tin cư trú
        $giuongList = Database::fetchAll("
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
            WHERE p.MaToaNha = :toa
            ORDER BY g.MaPhong ASC, g.KyHieuGiuong ASC
        ", ['toa' => $maToa]);

        $giuongByPhong = [];
        foreach ($giuongList as $g) {
            $giuongByPhong[$g['MaPhong']][] = $g;
        }

        // Thống kê nhanh toàn tòa
        $stats = Database::fetch("
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
            WHERE p.MaToaNha = :toa
        ", ['toa' => $maToa]);

        $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY SoGiuongToiDa ASC");

        $this->view('staff.rooms', [
            'user' => $user,
            'isTruongToa' => true,
            'toaInfo' => $toaInfo,
            'maToa' => $maToa,
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
            'filters' => [
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

    public function confirmCheckin(): void
    {
        $request = new Request();
        $maHopDong = trim((string)$request->input('maHopDong', ''));
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $maHoSo = trim((string)$request->input('maHoSo', ''));

        Database::beginTransaction();
        try {
            $hd = null;
            if (!empty($maHopDong)) {
                $hd = Database::fetch("SELECT * FROM HopDongNoiTru WHERE MaHopDong = :maHD FOR UPDATE", ['maHD' => $maHopDong]);
            } elseif (!empty($maGiuong)) {
                $hd = Database::fetch("
                    SELECT * FROM HopDongNoiTru 
                    WHERE MaGiuong = :g AND (TrangThaiHopDong IN ('Chờ nhận phòng', 'Chờ hiệu lực') OR NgayNhanPhong IS NULL)
                    ORDER BY MaHopDong DESC LIMIT 1 FOR UPDATE
                ", ['g' => $maGiuong]);
            }

            if (!$hd) {
                // Nếu chưa có hợp đồng nhưng có hồ sơ đã duyệt/thanh toán trên giường đó
                $hoso = null;
                if (!empty($maHoSo)) {
                    $hoso = Database::fetch("SELECT * FROM HoSoDangKy WHERE MaHoSo = :hs FOR UPDATE", ['hs' => $maHoSo]);
                }
                if (!$hoso && !empty($maGiuong)) {
                    $hoso = Database::fetch("
                        SELECT * FROM HoSoDangKy 
                        WHERE MaGiuong = :g AND TrangThaiHoSo IN ('Chờ thanh toán', 'Đã duyệt')
                        ORDER BY MaHoSo DESC LIMIT 1 FOR UPDATE
                    ", ['g' => $maGiuong]);
                }

                if ($hoso) {
                    $bedId = $hoso['MaGiuong'] ?? $maGiuong;
                    $existingHD = Database::fetch("SELECT * FROM HopDongNoiTru WHERE MaHoSo = :hs FOR UPDATE", ['hs' => $hoso['MaHoSo']]);
                    if ($existingHD) {
                        Database::execute("
                            UPDATE HopDongNoiTru 
                            SET NgayNhanPhong = NOW(), TrangThaiHopDong = 'Đang hiệu lực', MaGiuong = :g
                            WHERE MaHopDong = :maHD
                        ", [
                            'maHD' => $existingHD['MaHopDong'],
                            'g' => $bedId
                        ]);
                        $hd = $existingHD;
                    } else {
                        $newMaHD = 'HD' . date('Ym') . rand(100, 999);
                        Database::execute("
                            INSERT INTO HopDongNoiTru (MaHopDong, MaHoSo, MaSinhVien, MaGiuong, NgayBatDau, NgayKetThuc, NgayNhanPhong, TrangThaiHopDong)
                            VALUES (:maHD, :maHoSo, :maSV, :maGiuong, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 10 MONTH), NOW(), 'Đang hiệu lực')
                        ", [
                            'maHD' => $newMaHD,
                            'maHoSo' => $hoso['MaHoSo'],
                            'maSV' => $hoso['MaSinhVien'],
                            'maGiuong' => $bedId
                        ]);
                        $hd = ['MaHopDong' => $newMaHD, 'MaGiuong' => $bedId, 'MaSinhVien' => $hoso['MaSinhVien']];
                    }

                    Database::execute("UPDATE HoSoDangKy SET TrangThaiHoSo = 'Đã duyệt' WHERE MaHoSo = :hs", ['hs' => $hoso['MaHoSo']]);
                } else {
                    Database::rollBack();
                    Session::flash('error', 'Không tìm thấy thông tin hợp đồng hoặc hồ sơ hợp lệ để nhận phòng.');
                    $this->redirect('staff/rooms');
                    return;
                }
            } else {
                Database::execute("
                    UPDATE HopDongNoiTru 
                    SET NgayNhanPhong = NOW(), TrangThaiHopDong = 'Đang hiệu lực'
                    WHERE MaHopDong = :maHD
                ", ['maHD' => $hd['MaHopDong']]);
            }

            $bedId = $hd['MaGiuong'] ?? $maGiuong;
            Database::execute("
                UPDATE Giuong SET TrangThaiGiuong = 'Đã có người ở' WHERE MaGiuong = :g
            ", ['g' => $bedId]);

            Database::execute("
                UPDATE SinhVien SET TrangThaiNoiTru = 'Đang nội trú' WHERE MaSinhVien = :sv
            ", ['sv' => $hd['MaSinhVien']]);

            $roomOfBed = Database::fetch("SELECT MaPhong FROM Giuong WHERE MaGiuong = :g", ['g' => $bedId]);
            if ($roomOfBed) {
                Database::execute("
                    UPDATE Phong 
                    SET SoSVDangO = (SELECT COUNT(*) FROM Giuong WHERE MaPhong = :p1 AND TrangThaiGiuong = 'Đã có người ở')
                    WHERE MaPhong = :p2
                ", [
                    'p1' => $roomOfBed['MaPhong'],
                    'p2' => $roomOfBed['MaPhong']
                ]);
            }

            Database::commit();
            Session::flash('success', "Xác nhận nhận phòng thành công cho sinh viên {$hd['MaSinhVien']} (Giường {$bedId}).");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'Lỗi khi xác nhận nhận phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms');
    }

    public function assignRoomLeader(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));

        if (empty($maPhong) || empty($maSinhVien)) {
            Session::flash('error', 'Dữ liệu chỉ định trưởng phòng không hợp lệ.');
            $this->redirect('staff/rooms');
            return;
        }

        try {
            Database::execute("
                UPDATE Phong SET TruongPhong = :sv WHERE MaPhong = :p
            ", ['sv' => $maSinhVien, 'p' => $maPhong]);

            $sv = Database::fetch("SELECT MaTaiKhoan FROM SinhVien WHERE MaSinhVien = :sv", ['sv' => $maSinhVien]);
            if ($sv && !empty($sv['MaTaiKhoan'])) {
                Database::execute("
                    UPDATE TaiKhoan SET MaVaiTro = 'TRUONG_PHONG' WHERE MaTaiKhoan = :tk
                ", ['tk' => $sv['MaTaiKhoan']]);
            }

            Session::flash('success', "Đã chỉ định sinh viên {$maSinhVien} làm Trưởng phòng {$maPhong} thành công.");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi chỉ định trưởng phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms');
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

        try {
            if ($action === 'lock') {
                $g = Database::fetch("SELECT TrangThaiGiuong FROM Giuong WHERE MaGiuong = :g", ['g' => $maGiuong]);
                if ($g && $g['TrangThaiGiuong'] !== 'Trống') {
                    Session::flash('error', 'Chỉ có thể khóa bảo trì giường đang ở trạng thái Trống.');
                    $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
    }

    public function applications(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $pendingApplications = [];
        $active72hApplications = [];
        $recentApplications = [];

        try {
            // 1. Hồ sơ Chờ duyệt trong Cụm
            $pendingApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.GioiTinh, sv.SoCCCD, sv.SoDienThoai, sv.MSSVTaiTruong,
                       t.TenTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong, lp.TenLoaiPhong,
                       (lp.DonGiaLuuTruChuan + lp.DonGiaDichVuTangThem) AS DonGiaThang
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                WHERE hs.TrangThaiHoSo = 'Chờ duyệt' AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianNop ASC
            ", ['cum' => $maCum]);

            // 2. Hồ sơ đang trong hạn giữ chỗ 72h (Chờ thanh toán)
            $active72hApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong,
                       hd.SoTienPhaiDong, hd.TrangThai AS TrangThaiHoaDon,
                       TIMESTAMPDIFF(MINUTE, NOW(), hs.HanChotThanhToan) AS SoPhutConLai,
                       TIMESTAMPDIFF(HOUR, NOW(), hs.HanChotThanhToan) AS SoGioConLai
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN HoaDon hd ON hd.MaHoaDon = CONCAT('HD-', hs.MaHoSo)
                WHERE hs.TrangThaiHoSo = 'Chờ thanh toán' AND tn.MaCum = :cum
                ORDER BY hs.HanChotThanhToan ASC
            ", ['cum' => $maCum]);

            // 3. Lịch sử hồ sơ đã xử lý gần đây
            $recentApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE hs.TrangThaiHoSo IN ('Đã duyệt', 'Đã từ chối', 'Hết hạn - Đã hủy chỗ') AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianDuyet DESC
                LIMIT 10
            ", ['cum' => $maCum]);
        } catch (\Throwable $e) {
            // Trường hợp lỗi kết nối hoặc DB chưa khởi tạo
        }

        $this->view('staff.applications', [
            'user' => $user,
            'maCum' => $maCum,
            'pendingApplications' => $pendingApplications,
            'active72hApplications' => $active72hApplications,
            'recentApplications' => $recentApplications,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    public function utilities(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.utilities', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function requests(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.requests', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function maintenance(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.maintenance', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function discipline(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $this->view('staff.discipline', [
            'user' => $user,
            'maCum' => $maCum
        ]);
    }

    public function approveApplication(): void {
        $request = new Request();
        $maHoSo = trim($request->input('maHoSo', ''));
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

        $this->redirect('staff/applications');
    }

    public function rejectApplication(): void {
        $request = new Request();
        $maHoSo = trim($request->input('maHoSo', ''));
        $lyDo = trim($request->input('lyDo', ''));
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

    // ==========================================================================
    // PHÂN HỆ QUẢN LÝ KHU, CỤM & TÒA NHÀ (STAFF ZONES & INFRASTRUCTURE)
    // CƠ CHẾ PHÂN QUYỀN THEO CẤP:
    // - Trưởng KTX: Quản lý tất cả (Khu, Cụm, Tòa, Phòng)
    // - Trưởng khu: Chỉ quản lý CRUD cấp Khu
    // - Trưởng cụm: Chỉ quản lý CRUD cấp Cụm
    // - Trưởng tòa: Chỉ quản lý CRUD cấp Tòa
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

    // ==========================================================================
    // PHÂN HỆ QUẢN LÝ PHÒNG & GIƯỜNG DÀNH RIÊNG CHO TRƯỞNG TÒA NHÀ (TRUONG_TOA)
    // ==========================================================================

    /**
     * Đồng bộ sĩ số cư trú thực tế và tự động cập nhật trạng thái phòng
     */
    private function syncRoomOccupancy(string $maPhong, bool $forceUnlock = false, bool $preserveStatus = false): void
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
     * Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền thực hiện
     */
    public function saveRoomFromRooms(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (empty($maToa)) {
            $firstToa = Database::fetch("SELECT MaToaNha FROM ToaNha ORDER BY MaToaNha ASC LIMIT 1");
            $maToa = $firstToa['MaToaNha'] ?? 'A01';
        }

        $request = new Request();
        $isEdit = (int)$request->input('isEdit', 0) === 1;
        $maPhong = trim((string)$request->input('maPhong', ''));
        $soPhong = trim((string)$request->input('soPhong', ''));
        $tang = max(1, (int)$request->input('tang', 1));
        $maLoaiPhong = trim((string)$request->input('maLoaiPhong', ''));
        $trangThaiPhong = trim((string)$request->input('trangThaiPhong', 'Còn chỗ'));

        if (empty($maLoaiPhong)) {
            Session::flash('error', 'Vui lòng chọn loại phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $loaiPhong = Database::fetch("SELECT * FROM LoaiPhong WHERE MaLoaiPhong = :lp", ['lp' => $maLoaiPhong]);
        if (!$loaiPhong) {
            Session::flash('error', 'Loại phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }
        $soGiuongToiDa = (int)($loaiPhong['SoGiuongToiDa'] ?? 4);

        try {
            if ($isEdit) {
                $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
                if (!$room || $room['MaToaNha'] !== $maToa) {
                    Session::flash('error', "Không tìm thấy phòng hoặc phòng không thuộc Tòa nhà {$maToa}!");
                    $this->redirect('staff/rooms');
                    return;
                }

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
                            $this->redirect('staff/rooms');
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
                    $this->redirect('staff/rooms');
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
                    $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
    }

    /**
     * Xóa Phòng ở trực tiếp từ sơ đồ phòng
     * Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền thực hiện
     */
    public function deleteRoomFromRooms(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room || (!empty($maToa) && $room['MaToaNha'] !== $maToa)) {
            Session::flash('error', "Không tìm thấy phòng hoặc phòng không thuộc tòa nhà phụ trách!");
            $this->redirect('staff/rooms');
            return;
        }

        // Kiểm tra nguyên tắc bảo toàn dữ liệu
        $activeResidents = Database::fetch("
            SELECT COUNT(*) AS total
            FROM HopDongNoiTru hd
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            WHERE g.MaPhong = :mp AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
        ", ['mp' => $maPhong]);

        if (($activeResidents['total'] ?? 0) > 0 || (int)$room['SoSVDangO'] > 0) {
            Session::flash('error', "Không thể xóa phòng {$maPhong} vì đang có {$activeResidents['total']} sinh viên có hợp đồng cư trú (Nguyên tắc bảo toàn dữ liệu)!");
            $this->redirect('staff/rooms');
            return;
        }

        try {
            Database::execute("DELETE FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Database::execute("DELETE FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã xóa phòng {$maPhong} và toàn bộ giường liên kết thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa phòng: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms');
    }

    /**
     * Khóa bảo trì hoặc Mở khóa hoạt động cho Phòng
     */
    public function toggleRoomStatus(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Không tìm thấy phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (!empty($maToa) && $room['MaToaNha'] !== $maToa) {
            Session::flash('error', "Bạn chỉ có quyền thao tác trên Tòa nhà {$maToa}!");
            $this->redirect('staff/rooms');
            return;
        }

        if ($room['TrangThaiPhong'] === 'Khóa bảo trì') {
            $this->syncRoomOccupancy($maPhong, true);
            $updated = Database::fetch("SELECT TrangThaiPhong FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
            $statusText = $updated['TrangThaiPhong'] ?? 'Đang sử dụng';
            Session::flash('success', "Đã mở khóa hoạt động cho phòng {$maPhong} (chuyển về trạng thái '{$statusText}')!");
        } else {
            Database::execute("UPDATE Phong SET TrangThaiPhong = 'Khóa bảo trì' WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã chuyển phòng {$maPhong} sang trạng thái Khóa bảo trì!");
        }

        $this->redirect('staff/rooms');
    }

    /**
     * Thêm giường mới / giường phụ vào phòng
     */
    public function addBed(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $kyHieu = trim((string)$request->input('kyHieuGiuong', ''));
        $tangGiuong = max(1, (int)$request->input('viTriTangGiuong', 1));

        $room = Database::fetch("
            SELECT p.*, lp.TenLoaiPhong, lp.SoGiuongToiDa 
            FROM Phong p 
            LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong 
            WHERE p.MaPhong = :mp
        ", ['mp' => $maPhong]);

        if (!$room || (!empty($maToa) && $room['MaToaNha'] !== $maToa)) {
            Session::flash('error', 'Phòng không tồn tại hoặc không thuộc quyền quản lý!');
            $this->redirect('staff/rooms');
            return;
        }

        // RÀNG BUỘC: Không cho tạo thêm giường vượt quá số giường tối đa theo Loại phòng
        $soGiuongToiDa = (int)($room['SoGiuongToiDa'] ?? 4);
        $bedCountRow = Database::fetch("SELECT COUNT(*) AS cnt FROM Giuong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        $currentBedCount = (int)($bedCountRow['cnt'] ?? 0);

        if ($currentBedCount >= $soGiuongToiDa) {
            $tenLoai = $room['TenLoaiPhong'] ?? $room['MaLoaiPhong'];
            Session::flash('error', "Không thể tạo thêm giường! Phòng {$maPhong} thuộc Loại phòng '{$tenLoai}' chỉ cho phép tối đa {$soGiuongToiDa} giường (hiện tại phòng đã có đủ {$currentBedCount} giường)!");
            $this->redirect('staff/rooms');
            return;
        }

        if (empty($kyHieu)) {
            $kyHieu = 'G' . ($currentBedCount + 1);
        }

        $maGiuong = str_replace('.', '', $maPhong) . '-' . $kyHieu;
        $dup = Database::fetch("SELECT MaGiuong FROM Giuong WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
        if ($dup) {
            Session::flash('error', "Giường '{$kyHieu}' tại phòng {$maPhong} đã tồn tại!");
            $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
    }

    /**
     * Cập nhật trạng thái Giường (Trống, Khóa bảo trì, Hỏng...)
     */
    public function updateBedStatus(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $trangThai = trim((string)$request->input('trangThaiGiuong', 'Trống'));

        $bed = Database::fetch("
            SELECT g.*, p.MaToaNha, p.MaPhong
            FROM Giuong g
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE g.MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (!$bed || (!empty($maToa) && $bed['MaToaNha'] !== $maToa)) {
            Session::flash('error', 'Giường không tồn tại hoặc không thuộc quyền quản lý!');
            $this->redirect('staff/rooms');
            return;
        }

        // Kiểm tra hợp đồng hiệu lực
        $hasActiveContract = Database::fetch("
            SELECT MaHopDong FROM HopDongNoiTru
            WHERE MaGiuong = :mg AND TrangThaiHopDong = 'Đang hiệu lực'
        ", ['mg' => $maGiuong]);

        if ($hasActiveContract && in_array($trangThai, ['Trống', 'Khóa bảo trì', 'Hỏng'], true)) {
            Session::flash('error', "Không thể đổi trạng thái giường sang '{$trangThai}' vì sinh viên đang cư trú theo hợp đồng {$hasActiveContract['MaHopDong']}!");
            $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
    }

    /**
     * Xóa Giường trống khỏi phòng
     */
    public function deleteBed(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));

        $bed = Database::fetch("
            SELECT g.*, p.MaToaNha, p.MaPhong
            FROM Giuong g
            JOIN Phong p ON g.MaPhong = p.MaPhong
            WHERE g.MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (!$bed || (!empty($maToa) && $bed['MaToaNha'] !== $maToa)) {
            Session::flash('error', 'Giường không tồn tại hoặc không thuộc quyền quản lý!');
            $this->redirect('staff/rooms');
            return;
        }

        // Kiểm tra nguyên tắc bảo toàn dữ liệu
        $contract = Database::fetch("
            SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE MaGiuong = :mg
        ", ['mg' => $maGiuong]);

        if (($contract['total'] ?? 0) > 0 || $bed['TrangThaiGiuong'] === 'Đã có người ở') {
            Session::flash('error', "Không thể xóa giường {$maGiuong} vì đã có lịch sử hợp đồng nội trú gắn liền (Nguyên tắc bảo toàn dữ liệu)!");
            $this->redirect('staff/rooms');
            return;
        }

        try {
            Database::execute("DELETE FROM Giuong WHERE MaGiuong = :mg", ['mg' => $maGiuong]);
            $this->syncRoomOccupancy($bed['MaPhong']);
            Session::flash('success', "Đã xóa giường {$maGiuong} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa giường: ' . $e->getMessage());
        }

        $this->redirect('staff/rooms');
    }

    /**
     * Chỉ định hoặc hủy Trưởng phòng
     */
    public function assignRoomLeader(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));
        $action = trim((string)$request->input('action', 'assign'));

        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room) {
            Session::flash('error', 'Không tìm thấy phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (!empty($maToa) && $room['MaToaNha'] !== $maToa) {
            Session::flash('error', "Bạn chỉ có quyền thao tác trên Tòa nhà {$maToa}!");
            $this->redirect('staff/rooms');
            return;
        }

        if ($action === 'remove' || empty($maSinhVien)) {
            Database::execute("UPDATE Phong SET TruongPhong = NULL WHERE MaPhong = :mp", ['mp' => $maPhong]);
            Session::flash('success', "Đã gỡ vai trò Trưởng phòng của phòng {$maPhong}!");
        } else {
            Database::execute("UPDATE Phong SET TruongPhong = :sv WHERE MaPhong = :mp", ['sv' => $maSinhVien, 'mp' => $maPhong]);
            Session::flash('success', "Đã chỉ định sinh viên {$maSinhVien} làm Trưởng phòng {$maPhong}!");
        }

        $this->redirect('staff/rooms');
    }

    /**
     * Xác nhận sinh viên nhận phòng thực tế (Ghi nhận NgayNhanPhong = NOW())
     */
    public function confirmCheckIn(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

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

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (!empty($maToa) && $hd['MaToaNha'] !== $maToa) {
            Session::flash('error', "Bạn chỉ có quyền thao tác trên Tòa nhà {$maToa} được phân công!");
            $this->redirect('staff/rooms');
            return;
        }

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
        $this->redirect('staff/rooms');
    }

    /**
     * Tích nhận hoặc hủy thu Mẫu CT01 khai báo cư trú
     */
    public function toggleCT01(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $request = new Request();
        $maHopDong = trim((string)$request->input('maHopDong', ''));
        $status = (int)$request->input('status', 1);

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

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (!empty($maToa) && $hd['MaToaNha'] !== $maToa) {
            Session::flash('error', "Bạn chỉ có quyền thao tác trên Tòa nhà {$maToa}!");
            $this->redirect('staff/rooms');
            return;
        }

        Database::execute("
            UPDATE HopDongNoiTru
            SET DaNopMauCT01 = :st
            WHERE MaHopDong = :hd
        ", ['st' => $status, 'hd' => $maHopDong]);

        $msg = $status ? "Đã ghi nhận sinh viên nộp mẫu CT01 thành công!" : "Đã chuyển tình trạng Mẫu CT01 về Chưa nộp!";
        Session::flash('success', $msg);
        $this->redirect('staff/rooms');
    }

    /**
     * Xóa sinh viên khỏi giường (Hủy lưu trú / Thanh lý hợp đồng / Trả giường)
     * Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền thực hiện
     */
    public function removeStudentFromBed(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền quản lý phòng và giường!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        $request = new Request();
        $maGiuong = trim((string)$request->input('maGiuong', ''));
        $maPhong = trim((string)$request->input('maPhong', ''));
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));

        if (empty($maGiuong) || empty($maPhong)) {
            Session::flash('error', 'Thông tin giường hoặc phòng không hợp lệ!');
            $this->redirect('staff/rooms');
            return;
        }

        // Kiểm tra phòng và tòa nhà
        $room = Database::fetch("SELECT * FROM Phong WHERE MaPhong = :mp", ['mp' => $maPhong]);
        if (!$room || (!empty($maToa) && $room['MaToaNha'] !== $maToa)) {
            Session::flash('error', 'Phòng không tồn tại hoặc không thuộc quyền quản lý!');
            $this->redirect('staff/rooms');
            return;
        }

        // Kiểm tra giường
        $bed = Database::fetch("SELECT * FROM Giuong WHERE MaGiuong = :mg AND MaPhong = :mp", [
            'mg' => $maGiuong,
            'mp' => $maPhong
        ]);
        if (!$bed) {
            Session::flash('error', 'Không tìm thấy giường trong phòng!');
            $this->redirect('staff/rooms');
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
            $this->redirect('staff/rooms');
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

        $this->redirect('staff/rooms');
    }

    /**
     * Xuất danh sách Phòng ở ra file CSV theo kết quả đang lọc hoặc toàn bộ
     * Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền thực hiện
     */
    public function exportRooms(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền xuất dữ liệu phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (empty($maToa)) {
            $firstToa = Database::fetch("SELECT MaToaNha FROM ToaNha ORDER BY MaToaNha ASC LIMIT 1");
            $maToa = $firstToa['MaToaNha'] ?? 'A01';
        }

        $request = new Request();
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
     * Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền thực hiện
     */
    public function importRooms(): void
    {
        $user = Auth::user();
        if (($user['MaVaiTro'] ?? '') !== 'TRUONG_TOA') {
            Session::flash('error', 'Chỉ Trưởng tòa nhà (TRUONG_TOA) mới có quyền nhập dữ liệu phòng!');
            $this->redirect('staff/rooms');
            return;
        }

        $maToa = trim((string)($user['MaCumQuanLy'] ?? ''));
        if (empty($maToa)) {
            $firstToa = Database::fetch("SELECT MaToaNha FROM ToaNha ORDER BY MaToaNha ASC LIMIT 1");
            $maToa = $firstToa['MaToaNha'] ?? 'A01';
        }

        $request = new Request();
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

        $this->redirect('staff/rooms');
    }
}
