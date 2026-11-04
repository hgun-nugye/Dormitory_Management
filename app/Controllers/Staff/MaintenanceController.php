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
 * PHÂN CẤP QUYỀN HẠN CHUẨN HÓA (RBAC HIERARCHY):
 * - Cấp 4 (ADMIN / NV_QUAN_LY_KTX): Toàn quyền trên toàn bộ hệ thống KTX.
 * - Cấp 3 (TRUONG_KHU): Toàn quyền trong Khu (Khu A hoặc Khu B); duyệt thay Cụm & Tòa.
 * - Cấp 2 (TRUONG_CUM): Quản lý Cụm (AG, AH, BA...); duyệt thay Trưởng Tòa trong Cụm; duyệt phiếu do Trưởng Tòa lập.
 * - Cấp 1 (TRUONG_TOA): Quản lý Tòa (A01, H01...); duyệt & phân công phiếu SV trong Tòa; lập phiếu sự cố chung (chờ cấp trên duyệt).
 * - Cấp 0 (NV_THUONG - Kỹ thuật viên): CHỈ XEM phiếu trong Khu / phiếu được gán; cập nhật tiến độ & hoàn thành; TUYỆT ĐỐI KHÔNG PHÂN CÔNG.
 */
class MaintenanceController extends Controller
{
    /**
     * Xác định cấp bậc và phạm vi phân quyền quản lý của cán bộ đang đăng nhập
     */
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));

        if (empty($role)) {
            return [
                'level' => 'DENIED',
                'levelNumber' => -1,
                'role' => '',
                'title' => 'Chưa xác thực',
                'badgeClass' => 'badge-danger',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canAssign' => false,
                'canCreateStaffTicket' => false,
                'canApproveStaffTicket' => false,
                'canFilterAll' => false,
                'isTechnician' => false
            ];
        }

        // 1. CẤP 4: Quản trị viên hệ thống hoặc Quản lý toàn KTX
        if ($role === 'ADMIN' || $role === 'NV_QUAN_LY_KTX') {
            return [
                'level' => 'ALL',
                'levelNumber' => 4,
                'role' => $role,
                'title' => 'Quản trị viên / Quản lý toàn KTX',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canAssign' => true,
                'canCreateStaffTicket' => true,
                'canApproveStaffTicket' => true,
                'canFilterAll' => true,
                'isTechnician' => false
            ];
        }

        // 2. CẤP 1: Trưởng Tòa nhà (TRUONG_TOA: ví dụ TT001 -> A01, TT002 -> H01, TT003 -> BA1...)
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
                'levelNumber' => 1,
                'role' => $role,
                'title' => "Trưởng tòa nhà {$tenToa}",
                'badgeClass' => 'badge-success',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => $maToa,
                'tenToa' => $tenToa,
                'tenCum' => $toaRow['TenCum'] ?? $maCum,
                'canAssign' => true,               // Duyệt & phân công phiếu Sinh viên trong Tòa
                'canCreateStaffTicket' => true,     // Được lập phiếu sự cố chung của Tòa
                'canApproveStaffTicket' => false,   // Phiếu cán bộ phải do cấp trên (Cụm/Khu) duyệt
                'canFilterAll' => false,
                'isTechnician' => false
            ];
        }

        // 3. CẤP 2: Trưởng Cụm nhà (TRUONG_CUM: ví dụ CUMAG -> AG, CUMAH -> AH, CUMBA -> BA...)
        if ($role === 'TRUONG_CUM') {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("SELECT MaKhu, TenCum FROM CumNha WHERE MaCum = :c", ['c' => $maCum]);
            $maKhu = $cumRow['MaKhu'] ?? 'A';

            return [
                'level' => 'CUM',
                'levelNumber' => 2,
                'role' => $role,
                'title' => "Trưởng BQL Cụm nhà " . ($cumRow['TenCum'] ?? $maCum),
                'badgeClass' => 'badge-warning',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'tenCum' => $cumRow['TenCum'] ?? $maCum,
                'canAssign' => true,               // Duyệt phiếu SV trong Cụm + phiếu Tòa lập
                'canCreateStaffTicket' => true,     // Được lập phiếu sự cố của Cụm
                'canApproveStaffTicket' => true,    // Duyệt phiếu hạ tầng do Trưởng Tòa lập
                'canFilterAll' => false,
                'isTechnician' => false
            ];
        }

        // 4. CẤP 0: Kỹ thuật viên (NV_THUONG: ví dụ NV_KTA01, NV_KTA02, NV_KTB01...)
        $isTech = ($role === 'NV_THUONG') 
            || str_starts_with($user['TenDangNhap'] ?? '', 'NV_KT') 
            || str_contains(mb_strtolower($user['ChucVu'] ?? ''), 'kỹ thuật');

        if ($isTech) {
            $maKhu = in_array($cumQuanLy, ['A', 'B']) ? $cumQuanLy : 'A';
            return [
                'level' => 'NV_KT',
                'levelNumber' => 0,
                'role' => $role,
                'title' => "Nhân viên Kỹ thuật (Khu {$maKhu})",
                'badgeClass' => 'badge-secondary',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canAssign' => false,              // TUYỆT ĐỐI KHÔNG ĐƯỢC PHÂN CÔNG
                'canCreateStaffTicket' => false,    // Không có chức năng tạo phiếu
                'canApproveStaffTicket' => false,
                'canFilterAll' => false,
                'isTechnician' => true             // Chế độ dành riêng cho Thợ Kỹ thuật
            ];
        }

        // 5. CẤP 3: Trưởng khu KTX (TRUONG_KHU: ví dụ TK001 -> Khu A, TK002 -> Khu B)
        if ($role === 'TRUONG_KHU' || $cumQuanLy === 'A' || $cumQuanLy === 'B') {
            $maKhu = in_array($cumQuanLy, ['A', 'B']) ? $cumQuanLy : 'A';
            return [
                'level' => 'KHU',
                'levelNumber' => 3,
                'role' => $role,
                'title' => "Trưởng ban Quản lý KTX Khu {$maKhu}",
                'badgeClass' => 'badge-info',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canAssign' => true,               // Duyệt toàn bộ phiếu trong Khu
                'canCreateStaffTicket' => true,     // Được lập phiếu sự cố trong Khu
                'canApproveStaffTicket' => true,    // Duyệt phiếu do Cụm và Tòa lập
                'canFilterAll' => false,
                'isTechnician' => false
            ];
        }

        // 6. Kế toán KTX: Chỉ xem thống kê, không có quyền điều phối bảo trì
        if ($role === 'KE_TOAN' || str_contains(mb_strtolower($user['ChucVu'] ?? ''), 'kế toán')) {
            return [
                'level' => 'KE_TOAN',
                'levelNumber' => 0,
                'role' => $role,
                'title' => 'Kế toán KTX',
                'badgeClass' => 'badge-secondary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canAssign' => false,
                'canCreateStaffTicket' => false,
                'canApproveStaffTicket' => false,
                'canFilterAll' => true,
                'isTechnician' => false
            ];
        }

        // Mặc định an toàn
        return [
            'level' => 'CUM',
            'levelNumber' => 2,
            'role' => $role,
            'title' => "Cán bộ KTX ({$cumQuanLy})",
            'badgeClass' => 'badge-info',
            'maKhu' => 'A',
            'maCum' => !empty($cumQuanLy) ? $cumQuanLy : 'AG',
            'maToa' => null,
            'canAssign' => true,
            'canCreateStaffTicket' => true,
            'canApproveStaffTicket' => true,
            'canFilterAll' => false,
            'isTechnician' => false
        ];
    }

    /**
     * Kiểm tra quyền thực hiện thao tác trên một phiếu cụ thể
     */
    private function verifyTicketPermission(string $maPhieu, array $scope, string $action = 'assign'): array
    {
        $ticket = Database::fetch("
            SELECT pb.*, 
                   COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha,
                   tn.TenToaNha, tn.MaCum, cn.MaKhu, cn.TenCum
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE pb.MaPhieu = :id
        ", ['id' => $maPhieu]);

        if (!$ticket) {
            return ['allowed' => false, 'message' => "Không tìm thấy phiếu sự cố: {$maPhieu}"];
        }

        // Kỹ thuật viên không có quyền phân công hoặc hủy phiếu
        if ($scope['isTechnician'] && in_array($action, ['assign', 'cancel'])) {
            return [
                'allowed' => false,
                'message' => "Từ chối quyền: Kỹ thuật viên chỉ có quyền cập nhật tiến độ và báo cáo hoàn thành sửa chữa, không có quyền điều phối hoặc hủy phiếu!"
            ];
        }

        // Kế toán không có quyền phân công hoặc hủy phiếu
        if (($scope['level'] === 'KE_TOAN' || ($scope['role'] ?? '') === 'KE_TOAN') && in_array($action, ['assign', 'cancel'])) {
            return [
                'allowed' => false,
                'message' => "Từ chối quyền: Nhân viên Kế toán không có quyền phân công hoặc hủy phiếu sự cố bảo trì!"
            ];
        }

        // Kiểm tra phạm vi địa bàn theo cấp bậc
        if ($scope['level'] === 'TOA') {
            if ($ticket['FinalToaNha'] !== $scope['maToa']) {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Bạn là Trưởng tòa {$scope['maToa']}, không có quyền xử lý phiếu {$maPhieu} của Tòa {$ticket['FinalToaNha']}!"
                ];
            }

            // Với phiếu do Cán bộ lập (HA_TANG): Trưởng tòa tạo thì phải cấp trên duyệt!
            if ($action === 'assign' && $ticket['LoaiSuCo'] === 'HA_TANG') {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Phiếu sự cố hạ tầng do Cán bộ lập cần cấp trên (Trưởng Cụm / Khu / Quản lý) kiểm tra và phê duyệt!"
                ];
            }

            // Với phiếu do Cán bộ lập: Muốn Hủy cũng cần cấp trên duyệt!
            if ($action === 'cancel' && $ticket['LoaiSuCo'] === 'HA_TANG') {
                return [
                    'allowed' => false,
                    'message' => "Từ chối quyền: Yêu cầu hủy phiếu sự cố hạ tầng do Cán bộ lập phải do Cấp trên (Trưởng Cụm / Khu) phê duyệt!"
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

        // 1. Lấy danh sách Cụm nhà & Khu
        $clusters = Database::fetchAll("
            SELECT cn.MaCum, cn.TenCum, cn.MaKhu, k.TenKhu
            FROM CumNha cn
            JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
            ORDER BY cn.MaKhu ASC, cn.MaCum ASC
        ");

        // 2. Lấy danh sách Tòa nhà trong phạm vi của cán bộ (cho Modal lập phiếu hạ tầng)
        $toaWhere = "";
        $toaParams = [];
        if ($scope['level'] === 'TOA') {
            $toaWhere = "WHERE tn.MaToaNha = :t_scope";
            $toaParams['t_scope'] = $scope['maToa'];
        } elseif ($scope['level'] === 'CUM') {
            $toaWhere = "WHERE tn.MaCum = :c_scope";
            $toaParams['c_scope'] = $scope['maCum'];
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            $toaWhere = "WHERE cn.MaKhu = :k_scope";
            $toaParams['k_scope'] = $scope['maKhu'];
        }
        $buildings = Database::fetchAll("
            SELECT tn.MaToaNha, tn.TenToaNha, tn.MaCum, cn.MaKhu
            FROM ToaNha tn
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            {$toaWhere}
            ORDER BY tn.MaToaNha ASC
        ", $toaParams);

        // 3. Xây dựng điều kiện lọc theo phân quyền
        $whereConditions = [];
        $params = [];
        $countWhere = [];
        $countParams = [];
        $pendingWhereSql = "";
        $pendingParams = [];

        if ($scope['level'] === 'TOA') {
            // Trưởng tòa: CHỈ xem phiếu của Tòa mình phụ trách, tuyệt đối không hiện tòa khác
            $whereConditions[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :scope_toa";
            $params['scope_toa'] = $scope['maToa'];

            $countWhere[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :count_toa";
            $countParams['count_toa'] = $scope['maToa'];

            $pendingWhereSql = "AND COALESCE(pb.MaToaNha, p.MaToaNha) = :pending_toa";
            $pendingParams['pending_toa'] = $scope['maToa'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $scope['maCum'];

        } elseif ($scope['level'] === 'CUM') {
            // Trưởng cụm: Xem toàn bộ các Tòa trong Cụm
            $whereConditions[] = "tn.MaCum = :scope_cum";
            $params['scope_cum'] = $scope['maCum'];

            $countWhere[] = "tn.MaCum = :count_cum";
            $countParams['count_cum'] = $scope['maCum'];

            $pendingWhereSql = "AND tn.MaCum = :pending_cum";
            $pendingParams['pending_cum'] = $scope['maCum'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $scope['maCum'];

        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            // Trưởng khu hoặc kỹ thuật viên: Xem phiếu trong Khu
            $whereConditions[] = "cn.MaKhu = :scope_khu";
            $params['scope_khu'] = $scope['maKhu'];

            $countWhere[] = "cn.MaKhu = :count_khu";
            $countParams['count_khu'] = $scope['maKhu'];

            $pendingWhereSql = "AND cn.MaKhu = :pending_khu";
            $pendingParams['pending_khu'] = $scope['maKhu'];

            $targetKhu = $scope['maKhu'];
            $selectedCum = $_GET['cum'] ?? 'all';

            // Nếu lọc tiếp theo Cụm trong Khu
            if (!empty($_GET['cum']) && $_GET['cum'] !== 'all' && $_GET['cum'] !== "khu-{$scope['maKhu']}") {
                $whereConditions[] = "tn.MaCum = :user_cum_filter";
                $params['user_cum_filter'] = $_GET['cum'];

                $countWhere[] = "tn.MaCum = :user_count_cum";
                $countParams['user_count_cum'] = $_GET['cum'];

                $pendingWhereSql .= " AND tn.MaCum = :user_pend_cum";
                $pendingParams['user_pend_cum'] = $_GET['cum'];
            }

        } else {
            // Quản trị viên (ADMIN) / Toàn KTX
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
            $whereConditions[] = "(pb.MaPhieu LIKE :search1 OR pb.MaPhong LIKE :search2 OR tb.TenThietBi LIKE :search3 OR sv.HoVaTen LIKE :search4 OR nvBao.HoVaTen LIKE :search5 OR pb.MoTaHuHong LIKE :search6)";
            $params['search1'] = "%{$search}%";
            $params['search2'] = "%{$search}%";
            $params['search3'] = "%{$search}%";
            $params['search4'] = "%{$search}%";
            $params['search5'] = "%{$search}%";
            $params['search6'] = "%{$search}%";
        }

        $whereSql = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

        // 4. Truy vấn danh sách phiếu báo hỏng
        $ticketsSql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MaToaNha, pb.MaThietBi, pb.MaSinhVienBao, pb.MaNhanVienBao, pb.LoaiSuCo,
                   pb.MoTaHuHong, pb.GhiChuTienDo, pb.HinhAnh, pb.NhanVienKyThuat, pb.NguoiDuyet, pb.TrangThaiSua, pb.NgayBao,
                   COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha,
                   tn.TenToaNha, tn.MaCum, cn.TenCum, cn.MaKhu,
                   tb.TenThietBi, tb.LoaiThietBi, tb.MaSoThietBi, tb.TinhTrangThietBi,
                   sv.HoVaTen AS TenSinhVien, sv.SoDienThoai AS SdtSinhVien,
                   nvBao.HoVaTen AS TenNhanVienBao, nvBao.ChucVu AS ChucVuNhanVienBao,
                   nvTho.HoVaTen AS TenTho, nvTho.ChucVu AS ChucVuTho, nvTho.SoDienThoai AS SdtTho,
                   tkTho.MaCumQuanLy AS TechKhu,
                   nvDuyet.HoVaTen AS TenNguoiDuyet
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
            LEFT JOIN NhanVien nvBao ON pb.MaNhanVienBao = nvBao.MaNhanVien
            LEFT JOIN TaiKhoan tkTho ON pb.NhanVienKyThuat = tkTho.MaTaiKhoan
            LEFT JOIN NhanVien nvTho ON tkTho.MaTaiKhoan = nvTho.MaTaiKhoan
            LEFT JOIN TaiKhoan tkDuyet ON pb.NguoiDuyet = tkDuyet.MaTaiKhoan
            LEFT JOIN NhanVien nvDuyet ON tkDuyet.MaTaiKhoan = nvDuyet.MaTaiKhoan
            {$whereSql}
            ORDER BY pb.NgayBao DESC
        ";
        $tickets = Database::fetchAll($ticketsSql, $params);

        // 5. Đếm số lượng phiếu theo trạng thái
        $countWhereSql = !empty($countWhere) ? "WHERE " . implode(" AND ", $countWhere) : "";

        $countsRaw = Database::fetchAll("
            SELECT pb.TrangThaiSua, COUNT(*) AS total
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
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

        // 6. Lấy danh sách kỹ thuật viên lọc theo Khu
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

        // 7. Lấy danh sách các phiếu chưa hoàn thành để nạp vào form phân công nhanh
        $pendingTicketsSql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MaToaNha, pb.LoaiSuCo, pb.MoTaHuHong, tb.TenThietBi, pb.NhanVienKyThuat,
                   COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha,
                   tn.MaCum, cn.MaKhu, cn.TenCum
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
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
            'buildings' => $buildings,
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
     * Xử lý Duyệt & Phân công kỹ thuật viên (Kiểm tra nghiêm ngặt quyền)
     */
    public function assign(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['canAssign']) {
            Session::flash('error', 'Kỹ thuật viên không có quyền điều phối hoặc phân công nhân sự!');
            $this->redirect('staff/maintenance');
            return;
        }

        $maPhieu = trim($_POST['maPhieu'] ?? '');
        $maNhanVien = (int)($_POST['maNhanVien'] ?? 0);
        $trangThai = trim($_POST['trangThai'] ?? 'Đang sửa chữa');

        if (empty($maPhieu) || $maNhanVien <= 0) {
            Session::flash('error', 'Vui lòng chọn đầy đủ phiếu sự cố và cán bộ kỹ thuật phụ trách.');
            $this->redirect('staff/maintenance');
            return;
        }

        // KIỂM TRA PHÂN QUYỀN
        $perm = $this->verifyTicketPermission($maPhieu, $scope, 'assign');
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $maTaiKhoanDuyet = (int)($user['MaTaiKhoan'] ?? 1);
        $result = MaintenanceService::approveAndAssignTechnician($maPhieu, $maNhanVien, $maTaiKhoanDuyet, $trangThai);
        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/maintenance');
    }

    /**
     * Cán bộ lập phiếu sự cố hạ tầng / thiết bị chung
     */
    public function createStaffTicket(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['canCreateStaffTicket']) {
            Session::flash('error', 'Tài khoản của bạn không có quyền lập phiếu sự cố hạ tầng.');
            $this->redirect('staff/maintenance');
            return;
        }

        $maToaNha = trim($_POST['maToaNha'] ?? '');
        $maPhong = trim($_POST['maPhong'] ?? '');
        $moTa = trim($_POST['moTa'] ?? '');
        $hinhAnh = trim($_POST['hinhAnh'] ?? '');

        if (empty($maToaNha) || empty($moTa)) {
            Session::flash('error', 'Vui lòng chọn Tòa nhà xảy ra sự cố và nhập mô tả chi tiết hư hỏng.');
            $this->redirect('staff/maintenance');
            return;
        }

        // Kiểm tra quyền theo Tòa
        if ($scope['level'] === 'TOA' && $maToaNha !== $scope['maToa']) {
            Session::flash('error', "Bạn là Trưởng tòa {$scope['maToa']}, không có quyền lập phiếu cho Tòa {$maToaNha}!");
            $this->redirect('staff/maintenance');
            return;
        }

        // Xử lý upload tệp hình ảnh hiện trường nếu người dùng chọn tệp
        if (isset($_FILES['hinhAnhFile']) && $_FILES['hinhAnhFile']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['hinhAnhFile'];

            // 1. Kiểm tra dung lượng tối đa (5MB)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                Session::flash('error', 'Dung lượng ảnh hiện trường vượt quá 5MB. Vui lòng nén hoặc chọn ảnh nhỏ hơn.');
                $this->redirect('staff/maintenance');
                return;
            }

            // 2. Kiểm tra phần mở rộng tệp
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowedExts, true)) {
                Session::flash('error', 'Định dạng tệp không được hỗ trợ! Vui lòng chỉ tải lên ảnh JPG, PNG, WEBP hoặc GIF.');
                $this->redirect('staff/maintenance');
                return;
            }

            // 3. Kiểm tra MIME Type thực tế
            $mime = '';
            if (function_exists('mime_content_type')) {
                $mime = mime_content_type($file['tmp_name']);
            }
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if ($mime && !in_array($mime, $allowedMimes, true)) {
                Session::flash('error', 'Nội dung tệp không phải là tệp ảnh hợp lệ!');
                $this->redirect('staff/maintenance');
                return;
            }

            // 4. Tạo thư mục public/uploads/maintenance/ nếu chưa có
            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/maintenance/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    Session::flash('error', 'Không thể khởi tạo thư mục lưu trữ ảnh tải lên trên máy chủ.');
                    $this->redirect('staff/maintenance');
                    return;
                }
            }

            // 5. Đặt tên tệp duy nhất, an toàn chống tấn công path traversal
            $safeFileName = 'scene_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath = $uploadDir . $safeFileName;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $hinhAnh = 'uploads/maintenance/' . $safeFileName;
            } else {
                Session::flash('error', 'Không thể lưu trữ tệp ảnh lên máy chủ. Vui lòng kiểm tra lại quyền ghi thư mục.');
                $this->redirect('staff/maintenance');
                return;
            }
        }

        $maNhanVien = $user['profile']['MaNhanVien'] ?? $user['MaNhanVien'] ?? $user['TenDangNhap'] ?? null;
        if (!$maNhanVien && !empty($user['MaTaiKhoan'])) {
            $nvRow = Database::fetch("SELECT MaNhanVien FROM NhanVien WHERE MaTaiKhoan = :id", ['id' => $user['MaTaiKhoan']]);
            $maNhanVien = $nvRow['MaNhanVien'] ?? null;
        }
        if (!$maNhanVien) {
            $maNhanVien = 'CUMAG';
        }
        $result = MaintenanceService::createStaffInfrastructureTicket(
            $maNhanVien,
            $maToaNha,
            !empty($maPhong) ? $maPhong : null,
            $moTa,
            !empty($hinhAnh) ? $hinhAnh : null
        );

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/maintenance');
    }

    /**
     * Kỹ thuật viên cập nhật tiến độ sửa chữa
     */
    public function updateProgress(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        $maPhieu = trim($_POST['maPhieu'] ?? '');
        $ghiChu = trim($_POST['ghiChu'] ?? '');
        $trangThai = trim($_POST['trangThai'] ?? 'Đang sửa chữa');

        if (empty($maPhieu) || empty($ghiChu)) {
            Session::flash('error', 'Vui lòng nhập nội dung ghi chú tiến độ sửa chữa.');
            $this->redirect('staff/maintenance');
            return;
        }

        $perm = $this->verifyTicketPermission($maPhieu, $scope, 'progress');
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $result = MaintenanceService::updateRepairProgress($maPhieu, $ghiChu, $trangThai);
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
        $ghiChu = trim($_POST['ghiChu'] ?? '');

        if (empty($maPhieu)) {
            Session::flash('error', 'Mã phiếu không hợp lệ.');
            $this->redirect('staff/maintenance');
            return;
        }

        // KIỂM TRA PHÂN QUYỀN
        $perm = $this->verifyTicketPermission($maPhieu, $scope, 'complete');
        if (!$perm['allowed']) {
            Session::flash('error', $perm['message']);
            $this->redirect('staff/maintenance');
            return;
        }

        $result = MaintenanceService::completeRepair($maPhieu, !empty($ghiChu) ? $ghiChu : null);
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
        $perm = $this->verifyTicketPermission($maPhieu, $scope, 'cancel');
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
