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
 * - Cấp 3 (TRUONG_KHU): Toàn quyền trong Khu (Khu A hoặc Khu B); duyệt thay Cụm & Tòa; phân công KTV trong Khu.
 * - Cấp 2 (TRUONG_CUM): Quản lý Cụm (AG, AH, BA...); duyệt thay Trưởng Tòa trong Cụm; duyệt phiếu hạ tầng do Trưởng Tòa lập.
 * - Cấp 1 (TRUONG_TOA): Quản lý Tòa (A01, H01...); duyệt & phân công phiếu SV trong Tòa; lập phiếu sự cố chung (chờ cấp trên duyệt).
 * - Cấp KTV (NV_KT / Thợ Kỹ thuật): XEM phiếu trong Khu / phiếu được gán; cập nhật tiến độ & nghiệm thu; TUYỆT ĐỐI KHÔNG PHÂN CÔNG / HỦY.
 * - Chặn 100% (SINH_VIEN, TRUONG_PHONG, KE_TOAN, NV_THUONG khác): Trả về Red Warning Card với vai trò động.
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
        $chucVu = mb_strtolower(trim((string)($user['ChucVu'] ?? '')));
        $tenDangNhap = trim((string)($user['TenDangNhap'] ?? ''));

        // Xác định tên vai trò hiển thị thân thiện (để hiển thị động trong thông báo từ chối)
        $userRoleName = 'Người dùng';
        if ($role === 'SINH_VIEN' || $role === 'TRUONG_PHONG') {
            $userRoleName = ($role === 'TRUONG_PHONG') ? 'Trưởng phòng Sinh viên' : 'Sinh viên nội trú';
        } elseif ($role === 'KE_TOAN' || str_contains($chucVu, 'kế toán')) {
            $userRoleName = 'Nhân viên Kế toán';
        } elseif ($role === 'NV_LE_TAN' || str_contains($chucVu, 'lễ tân')) {
            $userRoleName = 'Nhân viên Lễ tân';
        } elseif (str_contains($chucVu, 'hành chính') || str_contains($chucVu, 'văn phòng')) {
            $userRoleName = 'Nhân viên Hành chính';
        } elseif (str_contains($chucVu, 'bảo vệ')) {
            $userRoleName = 'Nhân viên Bảo vệ';
        } elseif (!empty($user['ChucVu'])) {
            $userRoleName = trim($user['ChucVu']);
        } elseif ($role === 'NV_THUONG') {
            $userRoleName = 'Nhân viên thường';
        }

        // Kiểm tra xem có phải Chuyên viên Kỹ thuật hay không
        $isTechCandidate = str_starts_with($tenDangNhap, 'NV_KT')
                        || str_contains($chucVu, 'kỹ thuật')
                        || str_contains($chucVu, 'điện nước')
                        || str_contains($chucVu, 'bảo trì')
                        || $role === 'KY_THUAT';

        // 1. CHẶN 100% CÁC VAI TRÒ KHÔNG ĐƯỢC PHÉP TRUY CẬP:
        // Sinh viên, Trưởng phòng SV, Kế toán, và Nhân viên thường KHÔNG PHẢI KỸ THUẬT
        $isBlocked = empty($role)
            || in_array($role, ['SINH_VIEN', 'TRUONG_PHONG', 'KE_TOAN'])
            || str_contains($chucVu, 'kế toán')
            || ($role === 'NV_THUONG' && !$isTechCandidate);

        if ($isBlocked) {
            return [
                'isAllowed' => false,
                'level' => 'DENIED',
                'levelNumber' => -1,
                'role' => $role,
                'title' => 'Không có quyền truy cập',
                'badgeClass' => 'badge-danger',
                'userRoleName' => $userRoleName,
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'maToaNha' => null,
                'canAssign' => false,
                'canCreateStaffTicket' => false,
                'canApproveStaffTicket' => false,
                'canFilterAll' => false,
                'isTechnician' => false,
                'availableToas' => [],
                'availableCums' => [],
                'availableKhus' => [],
                'message' => "Từ chối quyền truy cập: Phân hệ Điều phối Sửa chữa & Phiếu Báo Hỏng chỉ dành riêng cho Cán bộ Quản lý KTX (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX) và Nhân viên Kỹ thuật KTX. {$userRoleName} không có quyền thao tác!"
            ];
        }

        // 2. CẤP 4: Quản trị viên hệ thống hoặc Quản lý toàn KTX (Admin / Ban Giám đốc)
        if ($role === 'ADMIN' || $role === 'QuanTriVien' || $role === 'NV_QUAN_LY_KTX' || $role === 'BGD' || $role === 'BAN_GIAM_DOC') {
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
                'title' => 'Ban quản lý KTX',
                'badgeClass' => 'badge-primary',
                'userRoleName' => 'Cán bộ Quản trị',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'maToaNha' => null,
                'canAssign' => true,
                'canCreateStaffTicket' => true,
                'canApproveStaffTicket' => true,
                'canFilterAll' => true,
                'isTechnician' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 3. CẤP KỸ THUẬT VIÊN: Nhân viên kỹ thuật điện nước / bảo trì (Chế độ thi công)
        if ($isTechCandidate) {
            $maKhu = 'A';
            if ($cumQuanLy === 'A' || $cumQuanLy === 'B') {
                $maKhu = $cumQuanLy;
            } elseif (!empty($cumQuanLy)) {
                $cumRow = Database::fetch("SELECT MaKhu FROM CumNha WHERE MaCum = :c", ['c' => $cumQuanLy]);
                if ($cumRow && !empty($cumRow['MaKhu'])) {
                    $maKhu = $cumRow['MaKhu'];
                }
            } elseif (str_contains($chucVu, 'khu b') || str_contains($tenDangNhap, 'KTB')) {
                $maKhu = 'B';
            }

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
                'level' => 'NV_KT',
                'levelNumber' => 0,
                'role' => $role,
                'title' => "Nhân viên Kỹ thuật (Khu {$maKhu})",
                'badgeClass' => 'badge-info',
                'userRoleName' => "Kỹ thuật viên Khu {$maKhu}",
                'maKhu' => $maKhu,
                'maCum' => !empty($cumQuanLy) ? $cumQuanLy : null,
                'maToa' => null,
                'maToaNha' => null,
                'canAssign' => false,              // TUYỆT ĐỐI KHÔNG PHÂN CÔNG
                'canCreateStaffTicket' => false,    // Không lập phiếu sự cố
                'canApproveStaffTicket' => false,   // Không duyệt phiếu
                'canFilterAll' => false,
                'isTechnician' => true,             // Chế độ dành riêng cho Thợ Kỹ thuật
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 4. CẤP 3: Trưởng khu KTX (TRUONG_KHU: ví dụ TK001 -> Khu A, TK002 -> Khu B)
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
                'userRoleName' => "Trưởng BQL Khu {$maKhu}",
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'maToaNha' => null,
                'canAssign' => true,               // Duyệt toàn bộ phiếu trong Khu
                'canCreateStaffTicket' => true,     // Được lập phiếu sự cố trong Khu
                'canApproveStaffTicket' => true,    // Duyệt phiếu do Cụm và Tòa lập
                'canFilterAll' => false,
                'isTechnician' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 5. CẤP 2: Trưởng Cụm nhà (TRUONG_CUM: ví dụ CUMAG -> AG...)
        if ($role === 'TRUONG_CUM') {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("SELECT c.MaCum, c.TenCum, c.MaKhu, k.TenKhu FROM CumNha c JOIN KhuKTX k ON c.MaKhu = k.MaKhu WHERE c.MaCum = :c", ['c' => $maCum]);
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
                'userRoleName' => 'Trưởng BQL Cụm',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'maToaNha' => null,
                'canAssign' => true,               // Duyệt phiếu SV trong Cụm + phiếu Tòa lập
                'canCreateStaffTicket' => true,     // Được lập phiếu sự cố của Cụm
                'canApproveStaffTicket' => true,    // Duyệt phiếu hạ tầng do Trưởng Tòa lập
                'canFilterAll' => false,
                'isTechnician' => false,
                'availableToas' => $toas,
                'availableCums' => [$cumRow],
                'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $cumRow['TenKhu'] ?? $maKhu]],
                'message' => ''
            ];
        }

        // 6. CẤP 1: Trưởng Tòa nhà (TRUONG_TOA: ví dụ TT001 -> A01...)
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
        }

        $maKhu = $toaRow['MaKhu'] ?? 'A';
        $maCum = $toaRow['MaCum'] ?? 'AG';
        $maToa = $toaRow['MaToaNha'] ?? 'A01';
        $tenToa = $toaRow['TenToaNha'] ?? $maToa;
        $cleanToa = preg_replace('/^Tòa\s*/ui', '', $tenToa);

        return [
            'isAllowed' => true,
            'level' => 'TOA',
            'levelNumber' => 1,
            'role' => $role,
            'title' => "Trưởng Tòa {$cleanToa}",
            'badgeClass' => 'badge-success',
            'userRoleName' => "Trưởng Tòa {$cleanToa}",
            'maKhu' => $maKhu,
            'maCum' => $maCum,
            'maToa' => $maToa,
            'maToaNha' => $maToa,
            'canAssign' => true,
            'canCreateStaffTicket' => true,
            'canApproveStaffTicket' => false,
            'canFilterAll' => false,
            'isTechnician' => false,
            'availableToas' => [$toaRow],
            'availableCums' => [['MaCum' => $maCum, 'TenCum' => $toaRow['TenCum'] ?? $maCum, 'MaKhu' => $maKhu]],
            'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $toaRow['TenKhu'] ?? $maKhu]],
            'message' => ''
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
                'message' => "Từ chối quyền: Kỹ thuật viên chỉ có quyền tiếp nhận, cập nhật tiến độ và báo cáo hoàn thành sửa chữa, không có quyền điều phối hoặc hủy phiếu!"
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

        // NẾU TÀI KHOẢN BỊ CHẶN 100% -> HIỂN THỊ RED WARNING CARD
        if (!$scope['isAllowed']) {
            $this->view('staff.maintenance', [
                'user' => $user,
                'scope' => $scope,
                'kpis' => ['pending' => 0, 'fixing' => 0, 'infra' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => 0],
                'pendingTickets' => [],
                'fixingTickets' => [],
                'infraTickets' => [],
                'completedTickets' => [],
                'cancelledTickets' => [],
                'technicians' => [],
                'activeTab' => 'pending',
                'searchTerm' => '',
                'selectedType' => 'all',
                'selectedKhu' => '',
                'selectedCum' => '',
                'selectedToa' => '',
                'selectedTang' => '',
                'selectedTech' => '',
                'selectedStatus' => 'all',
                'flashSuccess' => Session::flash('success'),
                'flashError' => Session::flash('error'),
                'flashWarning' => Session::flash('warning')
            ]);
            return;
        }

        // BỘ LỌC TỪ REQUEST GET
        $searchTerm = trim($_GET['search'] ?? '');
        $selectedType = trim($_GET['type'] ?? 'all');
        $selectedKhu = trim($_GET['khu'] ?? '');
        $selectedCum = trim($_GET['cum'] ?? '');
        $selectedToa = trim($_GET['toa'] ?? '');
        $selectedTang = trim($_GET['tang'] ?? '');
        $selectedTech = trim($_GET['tech'] ?? '');
        $selectedStatus = trim($_GET['status'] ?? 'all');
        $activeTab = trim($_GET['tab'] ?? 'pending');

        $allowedTabs = ['pending', 'fixing', 'infra', 'completed', 'cancelled'];
        if (!in_array($activeTab, $allowedTabs)) {
            $activeTab = 'pending';
        }

        // 1. ÁP DỤNG RÀNG BUỘC PHÂN QUYỀN (RBAC SCOPE)
        $scopeConditions = [];
        $scopeParams = [];

        if ($scope['level'] === 'TOA') {
            $scopeConditions[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :scope_toa";
            $scopeParams['scope_toa'] = $scope['maToa'];
            $selectedToa = $scope['maToa'];
            $selectedCum = $scope['maCum'];
            $selectedKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'CUM') {
            $scopeConditions[] = "tn.MaCum = :scope_cum";
            $scopeParams['scope_cum'] = $scope['maCum'];
            $selectedCum = $scope['maCum'];
            $selectedKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            $scopeConditions[] = "cn.MaKhu = :scope_khu";
            $scopeParams['scope_khu'] = $scope['maKhu'];
            $selectedKhu = $scope['maKhu'];
        }

        // 2. TÍNH TOÁN 4 KPI THỐNG KÊ NHANH TRONG PHẠM VI
        $kpiWhere = !empty($scopeConditions) ? "WHERE " . implode(" AND ", $scopeConditions) : "";
        $kpiRows = Database::fetchAll("
            SELECT pb.TrangThaiSua, pb.LoaiSuCo, COUNT(*) as c
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            {$kpiWhere}
            GROUP BY pb.TrangThaiSua, pb.LoaiSuCo
        ", $scopeParams);

        $kpis = [
            'pending' => 0,
            'fixing' => 0,
            'infra' => 0,
            'completed' => 0,
            'cancelled' => 0,
            'total' => 0
        ];

        foreach ($kpiRows as $kr) {
            $count = (int)$kr['c'];
            $kpis['total'] += $count;
            if ($kr['TrangThaiSua'] === 'Chờ xử lý') {
                $kpis['pending'] += $count;
            } elseif ($kr['TrangThaiSua'] === 'Đang sửa chữa') {
                $kpis['fixing'] += $count;
            } elseif ($kr['TrangThaiSua'] === 'Đã hoàn thành') {
                $kpis['completed'] += $count;
            } elseif ($kr['TrangThaiSua'] === 'Đã hủy') {
                $kpis['cancelled'] += $count;
            }
            if ($kr['LoaiSuCo'] === 'HA_TANG') {
                $kpis['infra'] += $count;
            }
        }

        // 3. XÂY DỰNG QUERY LỌC CHI TIẾT
        $queryConditions = $scopeConditions;
        $queryParams = $scopeParams;

        if ($selectedType !== 'all') {
            $queryConditions[] = "pb.LoaiSuCo = :filter_type";
            $queryParams['filter_type'] = $selectedType;
        }

        if (!empty($selectedKhu) && $scope['level'] === 'ALL') {
            $queryConditions[] = "cn.MaKhu = :filter_khu";
            $queryParams['filter_khu'] = $selectedKhu;
        }

        if (!empty($selectedCum) && ($scope['level'] === 'ALL' || $scope['level'] === 'KHU')) {
            $queryConditions[] = "tn.MaCum = :filter_cum";
            $queryParams['filter_cum'] = $selectedCum;
        }

        if (!empty($selectedToa) && $scope['level'] !== 'TOA') {
            $queryConditions[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :filter_toa";
            $queryParams['filter_toa'] = $selectedToa;
        }

        if (!empty($selectedTang)) {
            $queryConditions[] = "p.Tang = :filter_tang";
            $queryParams['filter_tang'] = $selectedTang;
        }

        if (!empty($selectedTech)) {
            if ($selectedTech === 'none') {
                $queryConditions[] = "pb.NhanVienKyThuat IS NULL";
            } else {
                $queryConditions[] = "pb.NhanVienKyThuat = :filter_tech";
                $queryParams['filter_tech'] = (int)$selectedTech;
            }
        }

        if ($selectedStatus !== 'all') {
            $queryConditions[] = "pb.TrangThaiSua = :filter_status";
            $queryParams['filter_status'] = $selectedStatus;
        }

        if (!empty($searchTerm)) {
            $queryConditions[] = "(
                pb.MaPhieu LIKE :kw1 
                OR pb.MaPhong LIKE :kw2 
                OR p.SoPhong LIKE :kw3
                OR tb.TenThietBi LIKE :kw4 
                OR sv.HoVaTen LIKE :kw5 
                OR sv.MSSVTaiTruong LIKE :kw6
                OR nvBao.HoVaTen LIKE :kw7 
                OR nvTho.HoVaTen LIKE :kw8 
                OR pb.MoTaHuHong LIKE :kw9
            )";
            $queryParams['kw1'] = "%{$searchTerm}%";
            $queryParams['kw2'] = "%{$searchTerm}%";
            $queryParams['kw3'] = "%{$searchTerm}%";
            $queryParams['kw4'] = "%{$searchTerm}%";
            $queryParams['kw5'] = "%{$searchTerm}%";
            $queryParams['kw6'] = "%{$searchTerm}%";
            $queryParams['kw7'] = "%{$searchTerm}%";
            $queryParams['kw8'] = "%{$searchTerm}%";
            $queryParams['kw9'] = "%{$searchTerm}%";
        }

        $whereClause = !empty($queryConditions) ? "WHERE " . implode(" AND ", $queryConditions) : "";

        // 4. TRUY VẤN TOÀN BỘ PHIẾU THEO BỘ LỌC
        $sql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MaToaNha, pb.MaThietBi, pb.MaSinhVienBao, pb.MaNhanVienBao, pb.LoaiSuCo,
                   pb.MoTaHuHong, pb.GhiChuTienDo, pb.HinhAnh, pb.NhanVienKyThuat, pb.NguoiDuyet, pb.TrangThaiSua, pb.NgayBao,
                   COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha,
                   p.SoPhong, p.Tang,
                   tn.TenToaNha, tn.MaCum, cn.TenCum, cn.MaKhu,
                   tb.TenThietBi, tb.LoaiThietBi, tb.MaSoThietBi, tb.TinhTrangThietBi,
                   sv.HoVaTen AS TenSinhVien, sv.MSSVTaiTruong, sv.SoDienThoai AS SdtSinhVien,
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
            {$whereClause}
            ORDER BY pb.NgayBao DESC
        ";

        $allTickets = Database::fetchAll($sql, $queryParams);

        // 5. PHÂN LOẠI DANH SÁCH THEO 5 TABS
        $pendingTickets = [];
        $fixingTickets = [];
        $infraTickets = [];
        $completedTickets = [];
        $cancelledTickets = [];

        foreach ($allTickets as $t) {
            $status = $t['TrangThaiSua'] ?? '';
            $isInfra = ($t['LoaiSuCo'] === 'HA_TANG');

            if ($status === 'Chờ xử lý') {
                $pendingTickets[] = $t;
            } elseif ($status === 'Đang sửa chữa') {
                $fixingTickets[] = $t;
            } elseif ($status === 'Đã hoàn thành') {
                $completedTickets[] = $t;
            } elseif ($status === 'Đã hủy') {
                $cancelledTickets[] = $t;
            }

            if ($isInfra) {
                $infraTickets[] = $t;
            }
        }

        // 6. LẤY DANH SÁCH KỸ THUẬT VIÊN (cho Modal phân công & dropdown lọc)
        $techWhere = "WHERE tk.DangHoatDong = 1 AND (nv.ChucVu LIKE '%kỹ thuật%' OR tk.TenDangNhap LIKE 'NV_KT%')";
        $techParams = [];
        $targetKhu = $scope['maKhu'] ?? $selectedKhu;
        if (!empty($targetKhu) && in_array($targetKhu, ['A', 'B'])) {
            $techWhere .= " AND (tk.MaCumQuanLy = :tech_khu OR cn.MaKhu = :tech_khu2)";
            $techParams['tech_khu'] = $targetKhu;
            $techParams['tech_khu2'] = $targetKhu;
        }

        $technicians = Database::fetchAll("
            SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, cn.MaKhu, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai
            FROM TaiKhoan tk
            JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
            LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
            {$techWhere}
            ORDER BY nv.HoVaTen ASC
        ", $techParams);

        $this->view('staff.maintenance', [
            'user' => $user,
            'scope' => $scope,
            'kpis' => $kpis,
            'pendingTickets' => $pendingTickets,
            'fixingTickets' => $fixingTickets,
            'infraTickets' => $infraTickets,
            'completedTickets' => $completedTickets,
            'cancelledTickets' => $cancelledTickets,
            'technicians' => $technicians,
            'activeTab' => $activeTab,
            'searchTerm' => $searchTerm,
            'selectedType' => $selectedType,
            'selectedKhu' => $selectedKhu,
            'selectedCum' => $selectedCum,
            'selectedToa' => $selectedToa,
            'selectedTang' => $selectedTang,
            'selectedTech' => $selectedTech,
            'selectedStatus' => $selectedStatus,
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
            'flashWarning' => Session::flash('warning')
        ]);
    }

    /**
     * Xuất danh sách sự cố bảo trì ra file Excel / CSV có UTF-8 BOM
     */
    public function exportMaintenance(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            Session::flash('error', 'Bạn không có quyền xuất dữ liệu bảo trì!');
            $this->redirect('staff/maintenance');
            return;
        }

        $activeTab = trim($_GET['tab'] ?? 'pending');
        $searchTerm = trim($_GET['search'] ?? '');
        $selectedType = trim($_GET['type'] ?? 'all');
        $selectedKhu = trim($_GET['khu'] ?? '');
        $selectedCum = trim($_GET['cum'] ?? '');
        $selectedToa = trim($_GET['toa'] ?? '');
        $selectedTang = trim($_GET['tang'] ?? '');
        $selectedTech = trim($_GET['tech'] ?? '');
        $selectedStatus = trim($_GET['status'] ?? 'all');

        $queryConditions = [];
        $queryParams = [];

        // Áp dụng phạm vi cán bộ
        if ($scope['level'] === 'TOA') {
            $queryConditions[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :scope_toa";
            $queryParams['scope_toa'] = $scope['maToa'];
        } elseif ($scope['level'] === 'CUM') {
            $queryConditions[] = "tn.MaCum = :scope_cum";
            $queryParams['scope_cum'] = $scope['maCum'];
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'NV_KT') {
            $queryConditions[] = "cn.MaKhu = :scope_khu";
            $queryParams['scope_khu'] = $scope['maKhu'];
        }

        // Lọc theo Tab đang chọn
        if ($activeTab === 'pending') {
            $queryConditions[] = "pb.TrangThaiSua = 'Chờ xử lý'";
        } elseif ($activeTab === 'fixing') {
            $queryConditions[] = "pb.TrangThaiSua = 'Đang sửa chữa'";
        } elseif ($activeTab === 'infra') {
            $queryConditions[] = "pb.LoaiSuCo = 'HA_TANG'";
        } elseif ($activeTab === 'completed') {
            $queryConditions[] = "pb.TrangThaiSua = 'Đã hoàn thành'";
        } elseif ($activeTab === 'cancelled') {
            $queryConditions[] = "pb.TrangThaiSua = 'Đã hủy'";
        }

        if ($selectedType !== 'all') {
            $queryConditions[] = "pb.LoaiSuCo = :filter_type";
            $queryParams['filter_type'] = $selectedType;
        }

        if (!empty($selectedKhu) && $scope['level'] === 'ALL') {
            $queryConditions[] = "cn.MaKhu = :filter_khu";
            $queryParams['filter_khu'] = $selectedKhu;
        }

        if (!empty($selectedCum) && ($scope['level'] === 'ALL' || $scope['level'] === 'KHU')) {
            $queryConditions[] = "tn.MaCum = :filter_cum";
            $queryParams['filter_cum'] = $selectedCum;
        }

        if (!empty($selectedToa) && $scope['level'] !== 'TOA') {
            $queryConditions[] = "COALESCE(pb.MaToaNha, p.MaToaNha) = :filter_toa";
            $queryParams['filter_toa'] = $selectedToa;
        }

        if (!empty($selectedTang)) {
            $queryConditions[] = "p.Tang = :filter_tang";
            $queryParams['filter_tang'] = $selectedTang;
        }

        if (!empty($selectedTech)) {
            if ($selectedTech === 'none') {
                $queryConditions[] = "pb.NhanVienKyThuat IS NULL";
            } else {
                $queryConditions[] = "pb.NhanVienKyThuat = :filter_tech";
                $queryParams['filter_tech'] = (int)$selectedTech;
            }
        }

        if (!empty($searchTerm)) {
            $queryConditions[] = "(pb.MaPhieu LIKE :kw1 OR pb.MaPhong LIKE :kw2 OR p.SoPhong LIKE :kw3 OR tb.TenThietBi LIKE :kw4 OR sv.HoVaTen LIKE :kw5 OR sv.MSSVTaiTruong LIKE :kw6 OR nvBao.HoVaTen LIKE :kw7 OR nvTho.HoVaTen LIKE :kw8 OR pb.MoTaHuHong LIKE :kw9)";
            $queryParams['kw1'] = "%{$searchTerm}%";
            $queryParams['kw2'] = "%{$searchTerm}%";
            $queryParams['kw3'] = "%{$searchTerm}%";
            $queryParams['kw4'] = "%{$searchTerm}%";
            $queryParams['kw5'] = "%{$searchTerm}%";
            $queryParams['kw6'] = "%{$searchTerm}%";
            $queryParams['kw7'] = "%{$searchTerm}%";
            $queryParams['kw8'] = "%{$searchTerm}%";
            $queryParams['kw9'] = "%{$searchTerm}%";
        }

        $whereClause = !empty($queryConditions) ? "WHERE " . implode(" AND ", $queryConditions) : "";

        $sql = "
            SELECT pb.MaPhieu, pb.MaPhong, pb.MaToaNha, pb.LoaiSuCo, pb.MoTaHuHong, pb.GhiChuTienDo,
                   pb.TrangThaiSua, pb.NgayBao,
                   COALESCE(pb.MaToaNha, p.MaToaNha) AS FinalToaNha,
                   p.SoPhong, p.Tang,
                   tn.TenToaNha, tn.MaCum, cn.TenCum, cn.MaKhu,
                   tb.TenThietBi, tb.MaSoThietBi,
                   sv.HoVaTen AS TenSinhVien, sv.MSSVTaiTruong, sv.SoDienThoai AS SdtSinhVien,
                   nvBao.HoVaTen AS TenNhanVienBao,
                   nvTho.HoVaTen AS TenTho, nvTho.SoDienThoai AS SdtTho
            FROM PhieuBaoHong pb
            LEFT JOIN Phong p ON pb.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON COALESCE(pb.MaToaNha, p.MaToaNha) = tn.MaToaNha
            LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
            LEFT JOIN ThietBiPhong tb ON pb.MaThietBi = tb.MaThietBi
            LEFT JOIN SinhVien sv ON pb.MaSinhVienBao = sv.MaSinhVien
            LEFT JOIN NhanVien nvBao ON pb.MaNhanVienBao = nvBao.MaNhanVien
            LEFT JOIN TaiKhoan tkTho ON pb.NhanVienKyThuat = tkTho.MaTaiKhoan
            LEFT JOIN NhanVien nvTho ON tkTho.MaTaiKhoan = nvTho.MaTaiKhoan
            {$whereClause}
            ORDER BY pb.NgayBao DESC
        ";

        $records = Database::fetchAll($sql, $queryParams);

        $filename = "bao_tri_KTX_" . $activeTab . "_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Ghi UTF-8 BOM
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');

        fputcsv($output, [
            'Mã Phiếu',
            'Loại Sự Cố',
            'Vị Trí Phòng / Tòa Nhà',
            'Khu / Cụm',
            'Hạng Mục / Thiết Bị',
            'Người Báo Hỏng',
            'MSSV / Mã Cán Bộ',
            'SĐT Liên Hệ',
            'Kỹ Thuật Phụ Trách',
            'SĐT Kỹ Thuật',
            'Trạng Thái',
            'Ngày Báo',
            'Nội Dung Mô Tả Sự Cố',
            'Ghi Chú Tiến Độ / Nghiệm Thu'
        ]);

        foreach ($records as $r) {
            $isInfra = ($r['LoaiSuCo'] === 'HA_TANG');
            $loaiText = $isInfra ? 'Hạ tầng chung' : 'Phòng sinh viên';
            $viTri = !empty($r['SoPhong']) 
                ? "P.{$r['SoPhong']} ({$r['TenToaNha']})" 
                : "{$r['TenToaNha']} ({$r['FinalToaNha']})";
            $khuCum = "Khu {$r['MaKhu']} - Cụm {$r['MaCum']}";
            $nguoiBao = !empty($r['TenSinhVien']) ? $r['TenSinhVien'] : ($r['TenNhanVienBao'] ?? 'Cán bộ KTX');
            $maNguoiBao = !empty($r['MSSVTaiTruong']) ? $r['MSSVTaiTruong'] : '---';
            $sdtNguoiBao = !empty($r['SdtSinhVien']) ? $r['SdtSinhVien'] : '---';
            $tenTho = !empty($r['TenTho']) ? $r['TenTho'] : 'Chưa phân công';
            $sdtTho = !empty($r['SdtTho']) ? $r['SdtTho'] : '---';

            fputcsv($output, [
                $r['MaPhieu'],
                $loaiText,
                $viTri,
                $khuCum,
                $r['TenThietBi'] ?? 'Cơ sở vật chất',
                $nguoiBao,
                $maNguoiBao,
                $sdtNguoiBao,
                $tenTho,
                $sdtTho,
                $r['TrangThaiSua'],
                !empty($r['NgayBao']) ? date('d/m/Y H:i', strtotime($r['NgayBao'])) : '',
                $r['MoTaHuHong'],
                $r['GhiChuTienDo'] ?? ''
            ]);
        }

        fclose($output);
        exit;
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

            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                Session::flash('error', 'Dung lượng ảnh hiện trường vượt quá 5MB. Vui lòng nén hoặc chọn ảnh nhỏ hơn.');
                $this->redirect('staff/maintenance');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowedExts, true)) {
                Session::flash('error', 'Định dạng tệp không được hỗ trợ! Vui lòng chỉ tải lên ảnh JPG, PNG, WEBP hoặc GIF.');
                $this->redirect('staff/maintenance');
                return;
            }

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

            $uploadDir = dirname(__DIR__, 2) . '/public/uploads/maintenance/';
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true) && !is_dir($uploadDir)) {
                    Session::flash('error', 'Không thể khởi tạo thư mục lưu trữ ảnh tải lên trên máy chủ.');
                    $this->redirect('staff/maintenance');
                    return;
                }
            }

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
     * Kỹ thuật viên hoặc Cán bộ cập nhật tiến độ sửa chữa
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
