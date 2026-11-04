<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Services\MaintenanceService;

/**
 * Controller: Điều phối & Quản lý Bảo trì Thiết bị, Cơ sở vật chất KTX
 * View: views/staff/maintenance.php
 * 
 * PHÂN QUYỀN CHẶT CHẼ THEO CẤP BẬC:
 * 1. TRƯỞNG TÒA NHÀ (TRUONG_TOA): Chỉ xem và thực hiện các phiếu thuộc Tòa nhà phụ trách (không hiện phiếu của tòa khác).
 * 2. TRƯỞNG CỤM NHÀ (TRUONG_CUM): Chỉ xem và thực hiện các phiếu thuộc Cụm nhà phụ trách (AG, AH, BA, BB, BC...).
 * 3. TRƯỞNG KHU KTX (TRUONG_KHU): Chỉ xem và thực hiện các phiếu thuộc Khu KTX phụ trách (Khu A hoặc Khu B).
 * 4. KỸ THUẬT VIÊN (NV_THUONG): Xem phiếu trong Khu phụ trách và các phiếu được phân công.
 * 5. QUẢN TRỊ VIÊN / QUẢN LÝ KTX (ADMIN, NV_QUAN_LY_KTX): Toàn quyền xem và điều phối trên toàn hệ thống.
 */
class MaintenanceController extends Controller
{
    /**
     * Xác định phạm vi phân quyền quản lý của cán bộ đang đăng nhập
     */
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));

        // 1. Quản trị viên hệ thống hoặc Quản lý toàn KTX
        if ($role === 'ADMIN' || $role === 'NV_QUAN_LY_KTX' || empty($role)) {
            return [
                'level' => 'ALL',
                'role' => $role,
                'title' => 'Quản trị viên / Quản lý toàn KTX',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canFilterAll' => true
            ];
        }

        // 2. Trưởng Tòa nhà (TRUONG_TOA: ví dụ TT001 -> A01, TT002 -> H01, TT003 -> BA1...)
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
                'role' => $role,
                'title' => "Trưởng tòa nhà {$tenToa}",
                'badgeClass' => 'badge-success',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => $maToa,
                'tenToa' => $tenToa,
                'tenCum' => $toaRow['TenCum'] ?? $maCum,
                'canFilterAll' => false
            ];
        }

        // 3. Trưởng Cụm nhà (TRUONG_CUM: ví dụ CUMAG -> AG, CUMAH -> AH, CUMBA -> BA...)
        if ($role === 'TRUONG_CUM') {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("SELECT MaKhu, TenCum FROM CumNha WHERE MaCum = :c", ['c' => $maCum]);
            $maKhu = $cumRow['MaKhu'] ?? 'A';

            return [
                'level' => 'CUM',
                'role' => $role,
                'title' => "Trưởng BQL Cụm nhà " . ($cumRow['TenCum'] ?? $maCum),
                'badgeClass' => 'badge-warning',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'tenCum' => $cumRow['TenCum'] ?? $maCum,
                'canFilterAll' => false
            ];
        }

        // 4. Trưởng khu KTX (TRUONG_KHU: ví dụ TK001 -> Khu A, TK002 -> Khu B)
        if ($role === 'TRUONG_KHU' || $cumQuanLy === 'A' || $cumQuanLy === 'B') {
            $maKhu = in_array($cumQuanLy, ['A', 'B']) ? $cumQuanLy : 'A';
            return [
                'level' => 'KHU',
                'role' => $role,
                'title' => "Trưởng khu KTX Khu {$maKhu}",
                'badgeClass' => 'badge-info',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canFilterAll' => false
            ];
        }

        // 5. Nhân viên kỹ thuật / Nhân viên thường (NV_THUONG)
        if ($role === 'NV_THUONG') {
            $maKhu = in_array($cumQuanLy, ['A', 'B']) ? $cumQuanLy : 'A';
            return [
                'level' => 'NV_KT',
                'role' => $role,
                'title' => "Kỹ thuật viên Khu {$maKhu}",
                'badgeClass' => 'badge-secondary',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canFilterAll' => false
            ];
        }

        // Mặc định an toàn
        return [
            'level' => 'CUM',
            'role' => $role,
            'title' => "Cán bộ KTX ({$cumQuanLy})",
            'badgeClass' => 'badge-info',
            'maKhu' => 'A',
            'maCum' => !empty($cumQuanLy) ? $cumQuanLy : 'AG',
            'maToa' => null,
            'canFilterAll' => false
        ];
    }

    /**
     * Kiểm tra quyền thực hiện thao tác trên một phiếu cụ thể
     */
    private function verifyTicketPermission(string $maPhieu, array $scope): ?array
    {
        $ticket = Database::fetch("
            SELECT pb.MaPhieu, pb.MaPhong, p.MaToaNha, tn.TenToaNha, tn.MaCum, cn.MaKhu, cn.TenCum
            FROM PhieuBaoHong pb
            JOIN Phong p ON pb.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE pb.MaPhieu = :id
        ", ['id' => $maPhieu]);

        if (!$ticket) {
            return ['allowed' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        // Kiểm tra phạm vi
        if ($scope['level'] === 'TOA') {
            if ($ticket['MaToaNha'] !== $scope['maToa']) {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Bạn là Trưởng tòa {$scope['maToa']}, không có quyền xử lý phiếu {$maPhieu} của phòng {$ticket['MaPhong']} (Tòa {$ticket['MaToaNha']})!"
                ];
            }
        } elseif ($scope['level'] === 'CUM') {
            if ($ticket['MaCum'] !== $scope['maCum']) {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Bạn là Trưởng Cụm {$scope['maCum']}, không có quyền xử lý phiếu {$maPhieu} của Cụm {$ticket['MaCum']}!"
                ];
            }
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            if ($ticket['MaKhu'] !== $scope['maKhu']) {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Bạn thuộc Khu {$scope['maKhu']}, không có quyền xử lý phiếu {$maPhieu} của Khu {$ticket['MaKhu']}!"
                ];
            }
        }

        return ['allowed' => true, 'ticket' => $ticket];
    }

    /**
     * Màn hình danh sách phiếu sự cố & điều phối kỹ thuật phân quyền
     */
    public function maintenance(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $status = $_GET['status'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        // 1. Lấy danh sách Cụm nhà & Khu để hiển thị bộ lọc
        $clusters = Database::fetchAll("
            SELECT cn.MaCum, cn.TenCum, cn.MaKhu, k.TenKhu
            FROM CumNha cn
            JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
            ORDER BY cn.MaKhu ASC, cn.MaCum ASC
        ");

        // 2. Xác định phạm vi hiển thị theo phân quyền người dùng
        $whereConditions = [];
        $params = [];
        $countWhere = [];
        $countParams = [];
        $pendingWhereSql = "";
        $pendingParams = [];

        // ÁP ĐẶT PHẠM VI BẮT BUỘC THEO PHÂN QUYỀN
        if ($scope['level'] === 'TOA') {
            // Trưởng tòa: CHỈ xem phiếu của Tòa mình phụ trách, tuyệt đối không hiện phiếu tòa khác
            $whereConditions[] = "p.MaToaNha = :scope_toa";
            $params['scope_toa'] = $scope['maToa'];

            $countWhere[] = "p.MaToaNha = :count_toa";
            $countParams['count_toa'] = $scope['maToa'];

            $pendingWhereSql = "AND p.MaToaNha = :pending_toa";
            $pendingParams['pending_toa'] = $scope['maToa'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $scope['maCum'];

        } elseif ($scope['level'] === 'CUM') {
            // Trưởng cụm: CHỈ xem phiếu của Cụm mình phụ trách
            $whereConditions[] = "tn.MaCum = :scope_cum";
            $params['scope_cum'] = $scope['maCum'];

            $countWhere[] = "tn.MaCum = :count_cum";
            $countParams['count_cum'] = $scope['maCum'];

            $pendingWhereSql = "AND tn.MaCum = :pending_cum";
            $pendingParams['pending_cum'] = $scope['maCum'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $scope['maCum'];

        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            // Trưởng khu hoặc kỹ thuật viên: CHỈ xem phiếu thuộc Khu của mình
            $whereConditions[] = "cn.MaKhu = :scope_khu";
            $params['scope_khu'] = $scope['maKhu'];

            $countWhere[] = "cn.MaKhu = :count_khu";
            $countParams['count_khu'] = $scope['maKhu'];

            $pendingWhereSql = "AND cn.MaKhu = :pending_khu";
            $pendingParams['pending_khu'] = $scope['maKhu'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $_GET['cum'] ?? 'all';

            // Nếu Trưởng khu lọc tiếp theo Cụm trong Khu của mình
            if (!empty($_GET['cum']) && $_GET['cum'] !== 'all' && $_GET['cum'] !== "khu-{$scope['maKhu']}") {
                $whereConditions[] = "tn.MaCum = :user_cum_filter";
                $params['user_cum_filter'] = $_GET['cum'];

                $countWhere[] = "tn.MaCum = :user_count_cum";
                $countParams['user_count_cum'] = $_GET['cum'];

                $pendingWhereSql .= " AND tn.MaCum = :user_pend_cum";
                $pendingParams['user_pend_cum'] = $_GET['cum'];
            }

        } else {
            // Quản trị viên (ADMIN) / Toàn KTX: Cho phép lọc tự do
            $cumFilter = $_GET['cum'] ?? 'all';
            $selectedCum = $cumFilter;
            $targetKhu = 'all';

            if ($cumFilter === 'khu-A') {
                $whereConditions[] = "cn.MaKhu = 'A'";
                $countWhere[] = "cn.MaKhu = 'A'";
                $pendingWhereSql = "AND cn.MaKhu = 'A'";
                $targetKhu = 'A';
            } elseif ($cumFilter === 'khu-B') {
                $whereConditions[] = "cn.MaKhu = 'B'";
                $countWhere[] = "cn.MaKhu = 'B'";
                $pendingWhereSql = "AND cn.MaKhu = 'B'";
                $targetKhu = 'B';
            } elseif ($cumFilter !== 'all') {
                $whereConditions[] = "tn.MaCum = :admin_cum";
                $params['admin_cum'] = $cumFilter;

                $countWhere[] = "tn.MaCum = :admin_count_cum";
                $countParams['admin_count_cum'] = $cumFilter;

                $pendingWhereSql = "AND tn.MaCum = :admin_pend_cum";
                $pendingParams['admin_pend_cum'] = $cumFilter;

                foreach ($clusters as $c) {
                    if ($c['MaCum'] === $cumFilter) {
                        $targetKhu = $c['MaKhu'];
                        break;
                    }
                }
            }
        }

        // Lọc theo Tab trạng thái
        if ($status === 'cho-xu-ly') {
            $whereConditions[] = "pb.TrangThaiSua = 'Chờ xử lý'";
        } elseif ($status === 'dang-sua') {
            $whereConditions[] = "pb.TrangThaiSua = 'Đang sửa chữa'";
        } elseif ($status === 'da-hoan-thanh') {
            $whereConditions[] = "pb.TrangThaiSua = 'Đã hoàn thành'";
        } elseif ($status === 'da-huy') {
            $whereConditions[] = "pb.TrangThaiSua = 'Đã hủy'";
        }

        // Lọc theo từ khóa tìm kiếm
        if ($search !== '') {
            $whereConditions[] = "(pb.MaPhieu LIKE :search1 OR pb.MaPhong LIKE :search2 OR tb.TenThietBi LIKE :search3 OR sv.HoVaTen LIKE :search4 OR pb.MoTaHuHong LIKE :search5)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
            $params['search5'] = "%{$search}%";
        }

        $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // 3. Truy vấn danh sách phiếu báo hỏng trong phạm vi cho phép
        $ticketsSql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MaThietBi, pb.MaSinhVienBao, pb.MoTaHuHong, pb.HinhAnh, pb.NhanVienKyThuat, pb.TrangThaiSua, pb.NgayBao,
                   p.MaToaNha, tn.TenToaNha, tn.MaCum, cn.TenCum, cn.MaKhu,
                   tb.TenThietBi, tb.LoaiThietBi, tb.MaSoThietBi, tb.TinhTrangThietBi,
                   sv.HoVaTen AS TenSinhVien, sv.SoDienThoai AS SdtSinhVien,
                   nv.HoVaTen AS TenNhanVien, nv.ChucVu AS ChucVuNhanVien, nv.SoDienThoai AS SdtNhanVien,
                   tkTech.MaCumQuanLy AS TechKhu
            FROM PhieuBaoHong pb
            JOIN Phong p ON pb.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
            LEFT JOIN TaiKhoan tkTech ON pb.NhanVienKyThuat = tkTech.MaTaiKhoan
            LEFT JOIN NhanVien nv ON tkTech.MaTaiKhoan = nv.MaTaiKhoan
            {$whereSql}
            ORDER BY pb.NgayBao DESC
        ";
        $tickets = Database::fetchAll($ticketsSql, $params);

        // 4. Đếm số lượng phiếu theo trạng thái trong phạm vi phân quyền
        $countWhereSql = !empty($countWhere) ? "WHERE " . implode(" AND ", $countWhere) : "";

        $countsRaw = Database::fetchAll("
            SELECT pb.TrangThaiSua, COUNT(*) AS total
            FROM PhieuBaoHong pb
            JOIN Phong p ON pb.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            {$countWhereSql}
            GROUP BY pb.TrangThaiSua
        ", $countParams);

        $counts = [
            'all' => 0,
            'cho_xu_ly' => 0,
            'dang_sua' => 0,
            'da_hoan_thanh' => 0,
            'da_huy' => 0
        ];

        foreach ($countsRaw as $row) {
            $counts['all'] += (int)$row['total'];
            if ($row['TrangThaiSua'] === 'Chờ xử lý') {
                $counts['cho_xu_ly'] = (int)$row['total'];
            } elseif ($row['TrangThaiSua'] === 'Đang sửa chữa') {
                $counts['dang_sua'] = (int)$row['total'];
            } elseif ($row['TrangThaiSua'] === 'Đã hoàn thành') {
                $counts['da_hoan_thanh'] = (int)$row['total'];
            } elseif ($row['TrangThaiSua'] === 'Đã hủy') {
                $counts['da_huy'] = (int)$row['total'];
            }
        }

        // 5. Lấy danh sách kỹ thuật viên: Nếu có phạm vi Khu cụ thể, chỉ lấy KTV của Khu đó!
        $techWhere = "WHERE tk.DangHoatDong = 1 AND (nv.ChucVu LIKE '%kỹ thuật%' OR tk.TenDangNhap LIKE 'NV_KT%')";
        $techParams = [];
        if (!empty($targetKhu) && in_array($targetKhu, ['A', 'B'])) {
            $techWhere .= " AND tk.MaCumQuanLy = :tech_khu";
            $techParams['tech_khu'] = $targetKhu;
        }

        $technicians = Database::fetchAll("
            SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy AS MaKhu, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai
            FROM TaiKhoan tk
            JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
            {$techWhere}
            ORDER BY tk.MaCumQuanLy ASC, nv.HoVaTen ASC
        ", $techParams);

        // 6. Lấy danh sách các phiếu chưa hoàn thành để nạp vào form phân công nhanh
        $pendingTicketsSql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MoTaHuHong, tb.TenThietBi, pb.NhanVienKyThuat,
                   tn.MaToaNha, tn.MaCum, cn.MaKhu, cn.TenCum
            FROM PhieuBaoHong pb
            JOIN Phong p ON pb.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            WHERE pb.TrangThaiSua IN ('Chờ xử lý', 'Đang sửa chữa')
            {$pendingWhereSql}
            ORDER BY pb.NgayBao DESC
        ";
        $pendingTickets = Database::fetchAll($pendingTicketsSql, $pendingParams);

        $this->view('staff.maintenance', [
            'user' => $user,
            'scope' => $scope,
            'maCum' => $selectedCum,
            'currentKhu' => $targetKhu,
            'clusters' => $clusters,
            'tickets' => $tickets,
            'counts' => $counts,
            'technicians' => $technicians,
            'pendingTickets' => $pendingTickets,
            'currentStatus' => $status,
            'currentSearch' => $search,
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
            'flashWarning' => Session::flash('warning')
        ]);
    }

    /**
     * Xử lý Phân công nhân viên kỹ thuật phụ trách phiếu (Kiểm tra nghiêm ngặt quyền)
     */
    public function assign(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $maPhieu = trim($_POST['maPhieu'] ?? '');
        $maNhanVien = (int)($_POST['maNhanVien'] ?? 0);
        $trangThai = trim($_POST['trangThai'] ?? 'Đang sửa chữa');

        if (empty($maPhieu) || $maNhanVien <= 0) {
            Session::flash('error', 'Vui lòng chọn đầy đủ phiếu sự cố và cán bộ kỹ thuật phụ trách.');
            $this->redirect('staff/maintenance');
            return;
        }

        // KIỂM TRA PHÂN QUYỀN
        $perm = $this->verifyTicketPermission($maPhieu, $scope);
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $result = MaintenanceService::assignTechnician($maPhieu, $maNhanVien, $trangThai);
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/maintenance');
    }

    /**
     * Xác nhận hoàn thành công tác sửa chữa & Hoàn nguyên thiết bị về 'Tốt'
     */
    public function complete(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $maPhieu = trim($_POST['maPhieu'] ?? '');
        if (empty($maPhieu)) {
            Session::flash('error', 'Mã phiếu không hợp lệ.');
            $this->redirect('staff/maintenance');
            return;
        }

        // KIỂM TRA PHÂN QUYỀN
        $perm = $this->verifyTicketPermission($maPhieu, $scope);
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $result = MaintenanceService::completeRepair($maPhieu);
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/maintenance');
    }

    /**
     * Hủy phiếu báo hỏng (Kiểm tra phân quyền)
     */
    public function cancel(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $maPhieu = trim($_POST['maPhieu'] ?? '');
        $lyDo = trim($_POST['lyDo'] ?? '');

        if (empty($maPhieu)) {
            Session::flash('error', 'Mã phiếu không hợp lệ.');
            $this->redirect('staff/maintenance');
            return;
        }

        // KIỂM TRA PHÂN QUYỀN
        $perm = $this->verifyTicketPermission($maPhieu, $scope);
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $result = MaintenanceService::cancelTicket($maPhieu, $lyDo);
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/maintenance');
    }
}
