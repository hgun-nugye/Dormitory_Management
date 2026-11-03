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
        $maCum = $this->getMaCum();
        $request = new Request();

        $filterToa = trim((string)$request->input('toaNha', ''));
        $filterTang = trim((string)$request->input('tang', ''));
        $filterTrangThai = trim((string)$request->input('trangThai', ''));

        $buildings = [];
        $rooms = [];
        $bedsByRoom = [];
        $underOccupiedRooms = [];

        try {
            // 1. Danh sách tòa nhà thuộc cụm của cán bộ
            $buildings = Database::fetchAll(
                "SELECT * FROM ToaNha WHERE MaCum = :cum ORDER BY MaToaNha ASC",
                ['cum' => $maCum]
            );

            // 2. Danh sách phòng theo bộ lọc
            $sqlRooms = "
                SELECT p.*, lp.TenLoaiPhong, lp.SoGiuongToiDa, tn.TenToaNha,
                       ROUND((p.SoSVDangO / lp.SoGiuongToiDa) * 100, 0) AS TyLeLapDay
                FROM Phong p
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                WHERE tn.MaCum = :cum
            ";
            $paramsRooms = ['cum' => $maCum];

            if (!empty($filterToa)) {
                $sqlRooms .= " AND p.MaToaNha = :toa";
                $paramsRooms['toa'] = $filterToa;
            }
            if (!empty($filterTang)) {
                $sqlRooms .= " AND p.Tang = :tang";
                $paramsRooms['tang'] = (int)$filterTang;
            }
            if (!empty($filterTrangThai)) {
                $sqlRooms .= " AND p.TrangThaiPhong = :trangThai";
                $paramsRooms['trangThai'] = $filterTrangThai;
            }
            $sqlRooms .= " ORDER BY p.MaToaNha ASC, p.Tang ASC, p.SoPhong ASC";
            $rooms = Database::fetchAll($sqlRooms, $paramsRooms);

            // 3. Danh sách giường và thông tin sinh viên / hợp đồng tương ứng
            $bedsQuery = Database::fetchAll("
                SELECT g.*, 
                       hd.MaHopDong, hd.NgayNhanPhong, hd.TrangThaiHopDong, hd.NgayBatDau, hd.NgayKetThuc,
                       COALESCE(sv.MaSinhVien, sv_hs.MaSinhVien) AS MaSinhVien,
                       COALESCE(sv.HoVaTen, sv_hs.HoVaTen) AS HoVaTen,
                       COALESCE(sv.MSSVTaiTruong, sv_hs.MSSVTaiTruong) AS MSSVTaiTruong,
                       COALESCE(sv.GioiTinh, sv_hs.GioiTinh) AS GioiTinh,
                       COALESCE(sv.SoCCCD, sv_hs.SoCCCD) AS SoCCCD,
                       COALESCE(sv.SoDienThoai, sv_hs.SoDienThoai) AS SoDienThoai,
                       COALESCE(t.TenTruong, t_hs.TenTruong) AS TenTruong,
                       hs.MaHoSo
                FROM Giuong g
                LEFT JOIN HopDongNoiTru hd ON g.MaGiuong = hd.MaGiuong AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Hiệu lực', 'Chờ nhận phòng')
                LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                LEFT JOIN (
                    SELECT h1.MaHoSo, h1.MaSinhVien, h1.MaGiuong
                    FROM HoSoDangKy h1
                    INNER JOIN (
                        SELECT MaGiuong, MAX(MaHoSo) AS MaxHoSo
                        FROM HoSoDangKy
                        WHERE TrangThaiHoSo IN ('Chờ thanh toán', 'Đã duyệt')
                        GROUP BY MaGiuong
                    ) h2 ON h1.MaHoSo = h2.MaxHoSo
                ) hs ON g.MaGiuong = hs.MaGiuong
                LEFT JOIN SinhVien sv_hs ON hs.MaSinhVien = sv_hs.MaSinhVien
                LEFT JOIN TruongDaiHoc t_hs ON sv_hs.MaTruong = t_hs.MaTruong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE tn.MaCum = :cum
                ORDER BY g.KyHieuGiuong ASC
            ", ['cum' => $maCum]);

            foreach ($bedsQuery as $b) {
                $bedsByRoom[$b['MaPhong']][] = $b;
            }

            // 4. Phòng đề xuất dồn phòng (lấp đầy <= 50%)
            $underOccupiedRooms = RoomConsolidationService::getUnderOccupiedRooms($maCum);
        } catch (\Throwable $e) {
            // Tránh ngắt trang nếu lỗi
        }

        $this->view('staff.rooms', [
            'user' => $user,
            'maCum' => $maCum,
            'buildings' => $buildings,
            'rooms' => $rooms,
            'bedsByRoom' => $bedsByRoom,
            'underOccupiedRooms' => $underOccupiedRooms,
            'filterToa' => $filterToa,
            'filterTang' => $filterTang,
            'filterTrangThai' => $filterTrangThai,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
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
}
