<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\UtilityBillingService;

/**
 * Controller: Quản lý Điện nước, Ghi chỉ số, Phát hiện bất thường & Đối soát công nợ (staff/utilities)
 * 
 * Áp dụng Ma trận Phân quyền RBAC kết hợp:
 * - 4 Cấp Quản lý địa bàn (Admin / Trưởng Khu / Trưởng Cụm / Trưởng Tòa)
 * - Chuyên môn: Kỹ thuật điện nước (ghi số, cảnh báo rò rỉ) & Kế toán (quản lý hóa đơn, gạch nợ)
 * - Chặn 100% Non-staff (Sinh viên, Nhân viên thường, Lễ tân...)
 */
class UtilityController extends Controller
{
    /**
     * Xác định phân quyền cán bộ truy cập phân hệ Điện Nước & Đối soát Công nợ
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

        // 1. CHUYÊN VIÊN KỸ THUẬT ĐIỆN NƯỚC (NV_KTB01, NV_KTA02, role KY_THUAT hoặc ChucVu kỹ thuật)
        $isTechnician = ($role === 'KY_THUAT')
            || (str_starts_with($username, 'NV_KT') && !str_contains($chucVu, 'kế toán'))
            || (str_contains($chucVu, 'kỹ thuật') && !str_contains($chucVu, 'kế toán'))
            || str_contains($chucVu, 'điện nước');

        // 2. CHUYÊN VIÊN KẾ TOÁN (KE_TOAN, username KT..., ChucVu kế toán)
        $isAccountant = ($role === 'KE_TOAN')
            || (str_starts_with($username, 'KT') && !str_starts_with($username, 'NV_KT'))
            || str_contains($chucVu, 'kế toán');

        // 3. CÁN BỘ QUẢN LÝ (Admin / Quản trị viên / Trưởng Khu / Trưởng Cụm / Trưởng Tòa)
        $isManager = in_array($role, ['ADMIN', 'QuanTriVien', 'NV_QUAN_LY_KTX', 'TRUONG_KHU', 'TRUONG_CUM', 'TRUONG_TOA'], true);

        // 4. CHẶN TUYỆT ĐỐI CÁC TÀI KHOẢN KHÔNG CÓ THẨM QUYỀN (Sinh viên, Nhân viên thường, Lễ tân...)
        if (!$isTechnician && !$isAccountant && !$isManager) {
            $userRoleName = match ($role) {
                'SINH_VIEN' => 'Sinh viên',
                'NV_THUONG' => 'Nhân viên thường',
                'NV_LE_TAN' => 'Nhân viên Lễ tân',
                'TRUONG_PHONG' => 'Trưởng phòng sinh viên',
                default => !empty($user['ChucVu']) ? $user['ChucVu'] : (!empty($role) ? $role : 'Tài khoản của bạn')
            };

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
                'canRecordReading' => false,
                'canPublishInvoice' => false,
                'canPublishCluster' => false,
                'canConfirmPayment' => false,
                'availableToas' => [],
                'availableCums' => [],
                'availableKhus' => [],
                'message' => "Từ chối quyền truy cập: Phân hệ Điện Nước & Công nợ chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX), Nhân viên Kỹ thuật và Kế toán KTX. {$userRoleName} không có quyền thao tác!"
            ];
        }

        // --- NẾU LÀ CÁN BỘ KỸ THUẬT ĐIỆN NƯỚC ---
        if ($isTechnician) {
            $maKhu = null;
            if (str_contains($username, 'KTA') || str_contains($chucVu, 'khu a')) {
                $maKhu = 'A';
            } elseif (str_contains($username, 'KTB') || str_contains($chucVu, 'khu b')) {
                $maKhu = 'B';
            }

            $khus = $maKhu
                ? Database::fetchAll("SELECT MaKhu, TenKhu FROM KhuKTX WHERE MaKhu = :k", ['k' => $maKhu])
                : Database::fetchAll("SELECT MaKhu, TenKhu FROM KhuKTX ORDER BY MaKhu ASC");

            $cums = $maKhu
                ? Database::fetchAll("SELECT MaCum, TenCum, MaKhu FROM CumNha WHERE MaKhu = :k ORDER BY MaCum ASC", ['k' => $maKhu])
                : Database::fetchAll("SELECT MaCum, TenCum, MaKhu FROM CumNha ORDER BY MaKhu ASC, MaCum ASC");

            $toas = $maKhu
                ? Database::fetchAll("
                    SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    WHERE c.MaKhu = :k
                    ORDER BY c.MaCum ASC, t.MaToaNha ASC
                ", ['k' => $maKhu])
                : Database::fetchAll("
                    SELECT t.MaToaNha, t.TenToaNha, t.TongSoTang, t.GioiTinhQuyDinh, t.TrangThaiToaNha, c.MaCum, c.TenCum, c.MaKhu, k.TenKhu
                    FROM ToaNha t
                    JOIN CumNha c ON t.MaCum = c.MaCum
                    JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                    ORDER BY c.MaKhu ASC, c.MaCum ASC, t.MaToaNha ASC
                ");

            $title = 'Nhân viên Kỹ thuật Điện Nước' . ($maKhu ? " (Khu {$maKhu})" : '');

            return [
                'isAllowed' => true,
                'level' => 'KY_THUAT',
                'levelNumber' => 0,
                'role' => $role,
                'title' => $title,
                'badgeClass' => 'badge-warning',
                'userRoleName' => 'Nhân viên Kỹ thuật',
                'maKhu' => $maKhu,
                'maCum' => null,
                'maToa' => null,
                'canRecordReading' => true,
                'canPublishInvoice' => false,
                'canPublishCluster' => false,
                'canConfirmPayment' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // --- NẾU LÀ CHUYÊN VIÊN KẾ TOÁN ---
        if ($isAccountant) {
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
                'level' => 'KE_TOAN',
                'levelNumber' => 0,
                'role' => $role,
                'title' => 'Chuyên viên Kế toán KTX',
                'badgeClass' => 'badge-success',
                'userRoleName' => 'Kế toán',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canRecordReading' => false,
                'canPublishInvoice' => false,
                'canPublishCluster' => false,
                'canConfirmPayment' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // --- CẤP 4: QUẢN LÝ TOÀN KTX (ADMIN / GIÁM ĐỐC) ---
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
                'userRoleName' => 'Cán bộ Quản trị',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canRecordReading' => true,
                'canPublishInvoice' => true,
                'canPublishCluster' => true,
                'canConfirmPayment' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // --- CẤP 3: TRƯỞNG KHU (TK001 -> Khu A, TK002 -> Khu B) ---
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
                'canRecordReading' => true,
                'canPublishInvoice' => true,
                'canPublishCluster' => true,
                'canConfirmPayment' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // --- CẤP 2: TRƯỞNG CỤM (CUMAG -> AG...) ---
        if ($role === 'TRUONG_CUM') {
            $maCum = !empty($cumQuanLy) ? $cumQuanLy : 'AG';
            $cumRow = Database::fetch("
                SELECT c.MaCum, c.TenCum, c.MaKhu, k.TenKhu 
                FROM CumNha c 
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu 
                WHERE c.MaCum = :c
            ", ['c' => $maCum]);

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
                'userRoleName' => 'Trưởng BQL Cụm',
                'maKhu' => $maKhu,
                'maCum' => $maCum,
                'maToa' => null,
                'canRecordReading' => true,
                'canPublishInvoice' => true,
                'canPublishCluster' => true,
                'canConfirmPayment' => true,
                'availableToas' => $toas,
                'availableCums' => [$cumRow],
                'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $cumRow['TenKhu'] ?? $maKhu]],
                'message' => ''
            ];
        }

        // --- CẤP 1: TRƯỞNG TÒA (TT001 -> A01...) ---
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
            'canRecordReading' => true,
            'canPublishInvoice' => true,
            'canPublishCluster' => false,
            'canConfirmPayment' => false,
            'availableToas' => [$toaRow],
            'availableCums' => [['MaCum' => $maCum, 'TenCum' => $toaRow['TenCum'] ?? $maCum, 'MaKhu' => $maKhu]],
            'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $toaRow['TenKhu'] ?? $maKhu]],
            'message' => ''
        ];
    }

    /**
     * Màn hình chính Quản lý Điện Nước & Công nợ
     */
    public function utilities(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        // NẾU TÀI KHOẢN BỊ CHẶN (Sinh viên, Nhân viên thường...): Render view chặn ngay
        if (!$scope['isAllowed']) {
            $this->view('staff.utilities', [
                'user' => $user,
                'scope' => $scope,
                'thang' => (int)date('n'),
                'nam' => (int)date('Y'),
                'rooms' => [],
                'roomMetadata' => [],
                'readings' => [],
                'invoices' => [],
                'missingRooms' => [],
                'kpis' => ['tongDien' => 0, 'tongNuoc' => 0, 'batThuong' => 0, 'chuaThu' => 0, 'daGhi' => 0, 'tongPhong' => 0],
                'selectedKhu' => '',
                'selectedCum' => '',
                'selectedToa' => '',
                'selectedTang' => '',
                'selectedTrangThai' => '',
                'searchTerm' => '',
                'activeTab' => 'readings',
                'success' => Session::flash('success'),
                'error' => Session::flash('error')
            ]);
            return;
        }

        // Kỳ ghi nhận
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));
        if ($thang < 1 || $thang > 12) $thang = (int)date('n');
        if ($nam < 2020 || $nam > 2030) $nam = (int)date('Y');

        // Bộ lọc Cascading & Tìm kiếm
        $selectedKhu = trim((string)($_GET['khu'] ?? ''));
        $selectedCum = trim((string)($_GET['cum'] ?? ''));
        $selectedToa = trim((string)($_GET['toa'] ?? ''));
        $selectedTang = trim((string)($_GET['tang'] ?? ''));
        $selectedTrangThai = trim((string)($_GET['trangThai'] ?? ''));
        $searchTerm = trim((string)($_GET['search'] ?? ''));
        $activeTab = trim((string)($_GET['tab'] ?? 'readings'));

        // Áp đặt phạm vi cứng theo quyền hạn
        if ($scope['level'] === 'TOA') {
            $selectedToa = $scope['maToa'];
            $selectedCum = $scope['maCum'];
            $selectedKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'CUM') {
            $selectedCum = $scope['maCum'];
            $selectedKhu = $scope['maKhu'];
        } elseif ($scope['level'] === 'KHU' || ($scope['level'] === 'KY_THUAT' && $scope['maKhu'])) {
            $selectedKhu = $scope['maKhu'];
        }

        // 1. Danh sách Phòng trong phạm vi (để điền form ghi số & modal)
        $roomWhere = ["1=1"];
        $roomParams = [];

        if (!empty($selectedToa)) {
            $roomWhere[] = "p.MaToaNha = :rt_toa";
            $roomParams['rt_toa'] = $selectedToa;
        } elseif (!empty($selectedCum)) {
            $roomWhere[] = "tn.MaCum = :rt_cum";
            $roomParams['rt_cum'] = $selectedCum;
        } elseif (!empty($selectedKhu)) {
            $roomWhere[] = "c.MaKhu = :rt_khu";
            $roomParams['rt_khu'] = $selectedKhu;
        }

        $roomSql = "
            SELECT p.MaPhong, p.SoPhong, p.Tang, p.SoSVDangO, tn.TenToaNha, tn.MaToaNha, c.MaCum, c.MaKhu
            FROM Phong p
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha c ON tn.MaCum = c.MaCum
            WHERE " . implode(' AND ', $roomWhere) . "
            ORDER BY c.MaKhu ASC, c.MaCum ASC, tn.TenToaNha ASC, p.Tang ASC, p.SoPhong ASC
        ";
        $rooms = Database::fetchAll($roomSql, $roomParams);

        // Metadata phòng để JavaScript tính tiền & điền chỉ số cũ tự động
        $roomMetadata = [];
        foreach ($rooms as $r) {
            $prev = UtilityBillingService::getPreviousReading($r['MaPhong'], $thang, $nam);
            $cleanToa = preg_replace('/^Tòa\s*/ui', '', $r['TenToaNha']);
            $roomMetadata[$r['MaPhong']] = [
                'dienCu' => $prev['dienCu'],
                'nuocCu' => $prev['nuocCu'],
                'soSV' => max(1, (int)$r['SoSVDangO']),
                'soPhong' => $r['SoPhong'],
                'toa' => "Tòa " . $cleanToa,
                'maToa' => $r['MaToaNha']
            ];
        }

        // 2. Danh sách Chỉ số đã ghi (Tab 1)
        $readWhere = ["cs.Thang = :thang", "cs.Nam = :nam"];
        $readParams = ['thang' => $thang, 'nam' => $nam];

        if (!empty($selectedToa)) {
            $readWhere[] = "p.MaToaNha = :rd_toa";
            $readParams['rd_toa'] = $selectedToa;
        } elseif (!empty($selectedCum)) {
            $readWhere[] = "tn.MaCum = :rd_cum";
            $readParams['rd_cum'] = $selectedCum;
        } elseif (!empty($selectedKhu)) {
            $readWhere[] = "c.MaKhu = :rd_khu";
            $readParams['rd_khu'] = $selectedKhu;
        }

        if (!empty($selectedTang)) {
            $readWhere[] = "p.Tang = :rd_tang";
            $readParams['rd_tang'] = $selectedTang;
        }

        if (!empty($searchTerm)) {
            $readWhere[] = "(p.MaPhong LIKE :rd_kw1 OR p.SoPhong LIKE :rd_kw2 OR tn.TenToaNha LIKE :rd_kw3)";
            $rdKw = "%{$searchTerm}%";
            $readParams['rd_kw1'] = $rdKw;
            $readParams['rd_kw2'] = $rdKw;
            $readParams['rd_kw3'] = $rdKw;
        }

        if ($selectedTrangThai === 'bat_thuong') {
            $readWhere[] = "cs.CanhBaoBatThuong = 1";
        } elseif ($selectedTrangThai === 'binh_thuong') {
            $readWhere[] = "cs.CanhBaoBatThuong = 0";
        }

        $readSql = "
            SELECT cs.*, p.SoPhong, p.Tang, tn.TenToaNha, tn.MaToaNha, c.MaCum, c.MaKhu,
                   (SELECT hd.MaHoaDon FROM HoaDon hd 
                    WHERE hd.MaPhong = cs.MaPhong 
                      AND hd.LoaiHoaDon LIKE '%iện nước%' 
                      AND (hd.LoaiHoaDon LIKE CONCAT('%', LPAD(cs.Thang, 2, '0'), '/', cs.Nam, '%')
                           OR hd.MaHoaDon LIKE CONCAT('%', cs.MaPhong, '-', cs.Nam, LPAD(cs.Thang, 2, '0'), '%'))
                    LIMIT 1) AS MaHoaDon,
                   (SELECT hd.TrangThai FROM HoaDon hd 
                    WHERE hd.MaPhong = cs.MaPhong 
                      AND hd.LoaiHoaDon LIKE '%iện nước%' 
                      AND (hd.LoaiHoaDon LIKE CONCAT('%', LPAD(cs.Thang, 2, '0'), '/', cs.Nam, '%')
                           OR hd.MaHoaDon LIKE CONCAT('%', cs.MaPhong, '-', cs.Nam, LPAD(cs.Thang, 2, '0'), '%'))
                    LIMIT 1) AS TrangThaiHoaDon
            FROM ChiSoDienNuoc cs
            JOIN Phong p ON cs.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha c ON tn.MaCum = c.MaCum
            WHERE " . implode(' AND ', $readWhere) . "
            ORDER BY cs.CanhBaoBatThuong DESC, c.MaKhu ASC, tn.TenToaNha ASC, p.SoPhong ASC
        ";
        $readings = Database::fetchAll($readSql, $readParams);

        // 3. Danh sách Hóa đơn Điện nước & Đối soát (Tab 2)
        $invWhere = ["hd.LoaiHoaDon LIKE '%iện nước%'"];
        $invParams = [];

        if (!empty($selectedToa)) {
            $invWhere[] = "p.MaToaNha = :iv_toa";
            $invParams['iv_toa'] = $selectedToa;
        } elseif (!empty($selectedCum)) {
            $invWhere[] = "tn.MaCum = :iv_cum";
            $invParams['iv_cum'] = $selectedCum;
        } elseif (!empty($selectedKhu)) {
            $invWhere[] = "c.MaKhu = :iv_khu";
            $invParams['iv_khu'] = $selectedKhu;
        }

        if (!empty($searchTerm)) {
            $invWhere[] = "(hd.MaHoaDon LIKE :iv_kw1 OR p.MaPhong LIKE :iv_kw2 OR p.SoPhong LIKE :iv_kw3 OR sv.HoVaTen LIKE :iv_kw4 OR sv.MSSVTaiTruong LIKE :iv_kw5)";
            $ivKw = "%{$searchTerm}%";
            $invParams['iv_kw1'] = $ivKw;
            $invParams['iv_kw2'] = $ivKw;
            $invParams['iv_kw3'] = $ivKw;
            $invParams['iv_kw4'] = $ivKw;
            $invParams['iv_kw5'] = $ivKw;
        }

        if ($selectedTrangThai === 'da_thanh_toan') {
            $invWhere[] = "hd.TrangThai = 'Đã thanh toán'";
        } elseif ($selectedTrangThai === 'chua_thanh_toan') {
            $invWhere[] = "hd.TrangThai = 'Chưa thanh toán'";
        }

        $invSql = "
            SELECT hd.*, p.SoPhong, p.Tang, tn.TenToaNha, c.MaCum, c.MaKhu, sv.HoVaTen AS TenSinhVien, sv.MSSVTaiTruong,
                   gd.MaGiaoDich, gd.KenhThanhToan, gd.ThoiGianGiaoDich
            FROM HoaDon hd
            LEFT JOIN Phong p ON hd.MaPhong = p.MaPhong
            LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            LEFT JOIN CumNha c ON tn.MaCum = c.MaCum
            LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
            LEFT JOIN GiaoDichThanhToan gd ON hd.MaHoaDon = gd.MaHoaDon
            WHERE " . implode(' AND ', $invWhere) . "
            ORDER BY (hd.TrangThai = 'Chưa thanh toán') DESC, hd.NgayTao DESC
            LIMIT 100
        ";
        $invoices = Database::fetchAll($invSql, $invParams);

        // 4. Danh sách Phòng CHƯA ghi chỉ số kỳ này (Tab 3)
        $recordedRoomIds = array_column(
            Database::fetchAll(
                "SELECT DISTINCT MaPhong FROM ChiSoDienNuoc WHERE Thang = :thang AND Nam = :nam",
                ['thang' => $thang, 'nam' => $nam]
            ),
            'MaPhong'
        );
        $recordedMap = array_flip($recordedRoomIds);

        $missingRooms = [];
        foreach ($rooms as $rm) {
            if (!isset($recordedMap[$rm['MaPhong']])) {
                $prev = $roomMetadata[$rm['MaPhong']] ?? ['dienCu' => 0, 'nuocCu' => 0];
                $rm['dienCu'] = $prev['dienCu'];
                $rm['nuocCu'] = $prev['nuocCu'];
                $missingRooms[] = $rm;
            }
        }

        // 5. Thẻ KPI Thống kê nhanh
        $tongKwh = 0;
        $tongM3 = 0;
        $tongBatThuong = 0;
        foreach ($readings as $rd) {
            $tongKwh += max(0, (int)$rd['DienMoi'] - (int)$rd['DienCu']);
            $tongM3 += max(0, (int)$rd['NuocMoi'] - (int)$rd['NuocCu']);
            if ((int)$rd['CanhBaoBatThuong'] === 1) {
                $tongBatThuong++;
            }
        }

        $chuaThu = 0;
        foreach ($invoices as $inv) {
            if ($inv['TrangThai'] === 'Chưa thanh toán') {
                $chuaThu += (float)$inv['SoTienPhaiDong'];
            }
        }

        $kpis = [
            'tongDien' => $tongKwh,
            'tongNuoc' => $tongM3,
            'batThuong' => $tongBatThuong,
            'chuaThu' => $chuaThu,
            'daGhi' => count($readings),
            'tongPhong' => count($rooms)
        ];

        $this->view('staff.utilities', [
            'user' => $user,
            'scope' => $scope,
            'thang' => $thang,
            'nam' => $nam,
            'rooms' => $rooms,
            'roomMetadata' => $roomMetadata,
            'readings' => $readings,
            'invoices' => $invoices,
            'missingRooms' => $missingRooms,
            'kpis' => $kpis,
            'selectedKhu' => $selectedKhu,
            'selectedCum' => $selectedCum,
            'selectedToa' => $selectedToa,
            'selectedTang' => $selectedTang,
            'selectedTrangThai' => $selectedTrangThai,
            'searchTerm' => $searchTerm,
            'activeTab' => $activeTab,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    /**
     * POST: Lưu chỉ số điện nước & kiểm tra bất thường
     */
    public function saveReading(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canRecordReading']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền ghi chỉ số công tơ điện nước!');
            $this->redirect('staff/utilities');
            return;
        }

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));
        $dienMoi = (int)$request->input('dienMoi', 0);
        $nuocMoi = (int)$request->input('nuocMoi', 0);
        $dienCu = $request->input('dienCu') !== null ? (int)$request->input('dienCu') : null;
        $nuocCu = $request->input('nuocCu') !== null ? (int)$request->input('nuocCu') : null;
        $soSV = (int)$request->input('soSV', 0);

        if (empty($maPhong)) {
            Session::flash('error', 'Vui lòng chọn phòng cần ghi chỉ số.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        // Kiểm tra quyền hạn theo Tòa
        if ($scope['level'] === 'TOA') {
            $room = Database::fetch("SELECT MaToaNha FROM Phong WHERE MaPhong = :p", ['p' => $maPhong]);
            if (!$room || $room['MaToaNha'] !== $scope['maToa']) {
                Session::flash('error', 'Từ chối quyền: Phòng này không thuộc Tòa nhà bạn phụ trách!');
                $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
                return;
            }
        }

        $res = UtilityBillingService::recordReading($maPhong, $thang, $nam, $dienMoi, $nuocMoi, $dienCu, $nuocCu, $soSV);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
    }

    /**
     * POST: Phát hành hóa đơn điện nước cho 1 phòng
     */
    public function publishInvoice(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canPublishInvoice']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền phát hành hóa đơn điện nước!');
            $this->redirect('staff/utilities');
            return;
        }

        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        $res = UtilityBillingService::publishInvoice($maPhong, $thang, $nam);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}&tab=readings");
    }

    /**
     * POST: Chốt sổ & Phát hành hóa đơn cho toàn Cụm / Khu
     */
    public function publishClusterInvoices(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canPublishCluster']) {
            Session::flash('error', 'Từ chối quyền: Chỉ Cán bộ Quản lý Cụm / Khu / Admin mới có quyền chốt sổ toàn Cụm!');
            $this->redirect('staff/utilities');
            return;
        }

        $request = new Request();
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));
        $maCum = $scope['maCum'] ?? 'AG';

        $res = UtilityBillingService::publishClusterInvoices($maCum, $thang, $nam);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}&tab=readings");
    }

    /**
     * POST: Xác nhận thanh toán mô phỏng / Gạch nợ hóa đơn
     */
    public function confirmPayment(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canConfirmPayment']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền xác nhận gạch nợ thanh toán!');
            $this->redirect('staff/utilities');
            return;
        }

        $request = new Request();
        $maHoaDon = trim((string)$request->input('maHoaDon', ''));
        $phuongThuc = trim((string)$request->input('phuongThuc', 'Mã QR KTX'));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));

        if (empty($maHoaDon)) {
            Session::flash('error', 'Mã hóa đơn không hợp lệ.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}&tab=invoices");
            return;
        }

        $res = UtilityBillingService::simulatePayment($maHoaDon, $phuongThuc);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}&tab=invoices");
    }

    /**
     * GET: Tải file mẫu CSV ghi chỉ số điện nước (hỗ trợ Excel UTF-8 BOM)
     */
    public function downloadTemplate(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            header('Location: ' . url('staff/utilities'));
            exit;
        }

        $filename = 'Mau_Ghi_Chi_So_Dien_Nuoc_' . date('Y_m') . '.csv';

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // Ghi UTF-8 BOM để Excel mở tiếng Việt không bị lỗi font
        fwrite($output, "\xEF\xBB\xBF");

        // Header CSV
        fputcsv($output, [
            'Mã phòng (*)',
            'Số phòng',
            'Tòa nhà',
            'Chỉ số Điện Mới (kWh) (*)',
            'Chỉ số Nước Mới (m3) (*)',
            'Sĩ số SV đang ở',
            'Ghi chú'
        ]);

        // Đổ sẵn danh sách phòng thuộc phạm vi của cán bộ kèm chỉ số cũ làm gợi ý
        $thang = (int)date('n');
        $nam = (int)date('Y');

        $where = ["1=1"];
        $params = [];
        if (!empty($scope['maToa'])) {
            $where[] = "p.MaToaNha = :toa";
            $params['toa'] = $scope['maToa'];
        } elseif (!empty($scope['maCum'])) {
            $where[] = "tn.MaCum = :cum";
            $params['cum'] = $scope['maCum'];
        } elseif (!empty($scope['maKhu'])) {
            $where[] = "c.MaKhu = :khu";
            $params['khu'] = $scope['maKhu'];
        }

        $rooms = Database::fetchAll("
            SELECT p.MaPhong, p.SoPhong, p.SoSVDangO, tn.TenToaNha
            FROM Phong p
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha c ON tn.MaCum = c.MaCum
            WHERE " . implode(' AND ', $where) . "
            ORDER BY tn.TenToaNha ASC, p.SoPhong ASC
            LIMIT 50
        ", $params);

        foreach ($rooms as $r) {
            $prev = UtilityBillingService::getPreviousReading($r['MaPhong'], $thang, $nam);
            fputcsv($output, [
                $r['MaPhong'],
                $r['SoPhong'],
                $r['TenToaNha'],
                $prev['dienCu'], // Để sẵn số cũ làm mẫu gợi ý
                $prev['nuocCu'],
                max(1, (int)$r['SoSVDangO']),
                'Điện cũ: ' . $prev['dienCu'] . ', Nước cũ: ' . $prev['nuocCu']
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * GET: Xuất file Excel / CSV theo Tab hiện tại (hỗ trợ Excel UTF-8 BOM)
     */
    public function exportUtilities(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            header('Location: ' . url('staff/utilities'));
            exit;
        }

        $tab = trim((string)($_GET['tab'] ?? 'readings'));
        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));
        $selectedToa = trim((string)($_GET['toa'] ?? $scope['maToa'] ?? ''));
        $selectedCum = trim((string)($_GET['cum'] ?? $scope['maCum'] ?? ''));
        $selectedKhu = trim((string)($_GET['khu'] ?? $scope['maKhu'] ?? ''));

        $filename = "Dien_Nuoc_T{$thang}_{$nam}_" . $tab . ".csv";

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF"); // UTF-8 BOM

        if ($tab === 'invoices') {
            // XUẤT HÓA ĐƠN & CÔNG NỢ
            fputcsv($output, [
                'Mã Hóa Đơn',
                'Mã Phòng',
                'Số Phòng',
                'Tòa Nhà',
                'Sinh Viên Đại Diện',
                'MSSV',
                'Loại Hóa Đơn',
                'Số Tiền Phải Đóng (VNĐ)',
                'Ngày Tạo',
                'Hạn Thanh Toán',
                'Trạng Thái',
                'Mã Giao Dịch',
                'Thời Gian Thanh Toán'
            ]);

            $where = ["hd.LoaiHoaDon LIKE '%iện nước%'"];
            $params = [];
            if (!empty($selectedToa)) {
                $where[] = "p.MaToaNha = :toa";
                $params['toa'] = $selectedToa;
            } elseif (!empty($selectedCum)) {
                $where[] = "tn.MaCum = :cum";
                $params['cum'] = $selectedCum;
            } elseif (!empty($selectedKhu)) {
                $where[] = "c.MaKhu = :khu";
                $params['khu'] = $selectedKhu;
            }

            $rows = Database::fetchAll("
                SELECT hd.*, p.SoPhong, tn.TenToaNha, sv.HoVaTen, sv.MSSVTaiTruong,
                       gd.MaGiaoDich, gd.ThoiGianGiaoDich
                FROM HoaDon hd
                LEFT JOIN Phong p ON hd.MaPhong = p.MaPhong
                LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN CumNha c ON tn.MaCum = c.MaCum
                LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                LEFT JOIN GiaoDichThanhToan gd ON hd.MaHoaDon = gd.MaHoaDon
                WHERE " . implode(' AND ', $where) . "
                ORDER BY hd.NgayTao DESC
            ", $params);

            foreach ($rows as $r) {
                fputcsv($output, [
                    $r['MaHoaDon'],
                    $r['MaPhong'],
                    $r['SoPhong'] ?? '',
                    $r['TenToaNha'] ?? '',
                    $r['HoVaTen'] ?? 'N/A',
                    $r['MSSVTaiTruong'] ?? '',
                    $r['LoaiHoaDon'],
                    $r['SoTienPhaiDong'],
                    $r['NgayTao'],
                    $r['HanThanhToan'],
                    $r['TrangThai'],
                    $r['MaGiaoDich'] ?? '',
                    $r['ThoiGianGiaoDich'] ?? ''
                ]);
            }
        } elseif ($tab === 'missing') {
            // XUẤT DANH SÁCH PHÒNG CHƯA GHI SỐ
            fputcsv($output, [
                'Mã Phòng',
                'Số Phòng',
                'Tầng',
                'Tòa Nhà',
                'Sĩ Số SV',
                'Chỉ Số Điện Kỳ Trước',
                'Chỉ Số Nước Kỳ Trước',
                'Trạng Thái'
            ]);

            $where = ["1=1"];
            $params = ['thang' => $thang, 'nam' => $nam];
            if (!empty($selectedToa)) {
                $where[] = "p.MaToaNha = :toa";
                $params['toa'] = $selectedToa;
            } elseif (!empty($selectedCum)) {
                $where[] = "tn.MaCum = :cum";
                $params['cum'] = $selectedCum;
            } elseif (!empty($selectedKhu)) {
                $where[] = "c.MaKhu = :khu";
                $params['khu'] = $selectedKhu;
            }

            $rooms = Database::fetchAll("
                SELECT p.MaPhong, p.SoPhong, p.Tang, p.SoSVDangO, tn.TenToaNha
                FROM Phong p
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                WHERE " . implode(' AND ', $where) . "
                  AND NOT EXISTS (
                      SELECT 1 FROM ChiSoDienNuoc cs 
                      WHERE cs.MaPhong = p.MaPhong AND cs.Thang = :thang AND cs.Nam = :nam
                  )
                ORDER BY tn.TenToaNha ASC, p.Tang ASC, p.SoPhong ASC
            ", $params);

            foreach ($rooms as $r) {
                $prev = UtilityBillingService::getPreviousReading($r['MaPhong'], $thang, $nam);
                fputcsv($output, [
                    $r['MaPhong'],
                    $r['SoPhong'],
                    $r['Tang'],
                    $r['TenToaNha'],
                    $r['SoSVDangO'],
                    $prev['dienCu'],
                    $prev['nuocCu'],
                    'Chưa ghi chỉ số kỳ tháng ' . $thang . '/' . $nam
                ]);
            }
        } else {
            // XUẤT CHỈ SỐ ĐIỆN NƯỚC (TAB 1)
            fputcsv($output, [
                'Mã Phòng',
                'Số Phòng',
                'Tòa Nhà',
                'Tháng',
                'Năm',
                'Điện Cũ',
                'Điện Mới',
                'Điện Tiêu Thụ (kWh)',
                'Tiền Điện (VNĐ)',
                'Nước Cũ',
                'Nước Mới',
                'Nước Tiêu Thụ (m3)',
                'Tiền Nước (VNĐ)',
                'Tổng Tiền (VNĐ)',
                'Sĩ Số SV',
                'Cảnh Báo Bất Thường',
                'Mã Hóa Đơn'
            ]);

            $where = ["cs.Thang = :thang", "cs.Nam = :nam"];
            $params = ['thang' => $thang, 'nam' => $nam];
            if (!empty($selectedToa)) {
                $where[] = "p.MaToaNha = :toa";
                $params['toa'] = $selectedToa;
            } elseif (!empty($selectedCum)) {
                $where[] = "tn.MaCum = :cum";
                $params['cum'] = $selectedCum;
            } elseif (!empty($selectedKhu)) {
                $where[] = "c.MaKhu = :khu";
                $params['khu'] = $selectedKhu;
            }

            $rows = Database::fetchAll("
                SELECT cs.*, p.SoPhong, tn.TenToaNha,
                       (SELECT hd.MaHoaDon FROM HoaDon hd 
                        WHERE hd.MaPhong = cs.MaPhong 
                          AND hd.LoaiHoaDon LIKE '%iện nước%' 
                          AND hd.LoaiHoaDon LIKE CONCAT('%', LPAD(cs.Thang, 2, '0'), '/', cs.Nam, '%')
                        LIMIT 1) AS MaHoaDon
                FROM ChiSoDienNuoc cs
                JOIN Phong p ON cs.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha c ON tn.MaCum = c.MaCum
                WHERE " . implode(' AND ', $where) . "
                ORDER BY cs.CanhBaoBatThuong DESC, tn.TenToaNha ASC, p.SoPhong ASC
            ", $params);

            foreach ($rows as $r) {
                $dienTieuThu = max(0, (int)$r['DienMoi'] - (int)$r['DienCu']);
                $nuocTieuThu = max(0, (int)$r['NuocMoi'] - (int)$r['NuocCu']);
                $tongTien = (float)$r['TienDien'] + (float)$r['TienNuoc'];
                $canhBao = ((int)$r['CanhBaoBatThuong'] === 1) ? 'ĐỘT BIẾN >3 LẦN' : 'Bình thường';

                fputcsv($output, [
                    $r['MaPhong'],
                    $r['SoPhong'],
                    $r['TenToaNha'],
                    $r['Thang'],
                    $r['Nam'],
                    $r['DienCu'],
                    $r['DienMoi'],
                    $dienTieuThu,
                    $r['TienDien'],
                    $r['NuocCu'],
                    $r['NuocMoi'],
                    $nuocTieuThu,
                    $r['TienNuoc'],
                    $tongTien,
                    $r['SoSVThucOTaiThang'],
                    $canhBao,
                    $r['MaHoaDon'] ?? 'Chưa phát hành'
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * POST: Nhập chỉ số điện nước hàng loạt từ file CSV
     */
    public function importReadings(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canRecordReading']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền nhập chỉ số điện nước!');
            $this->redirect('staff/utilities');
            return;
        }

        $request = new Request();
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn file CSV chỉ số điện nước hợp lệ.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        $fileTmp = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($fileTmp, 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể đọc nội dung file đã tải lên.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        // Bỏ qua dòng Header
        $firstLine = fgets($handle);

        $successCount = 0;
        $anomalyCount = 0;
        $errorCount = 0;
        $errorDetails = [];
        $rowIdx = 1;

        while (($data = fgetcsv($handle, 1000, ",")) !== false) {
            $rowIdx++;
            if (empty($data) || count($data) < 3) continue;

            $maPhong = trim((string)($data[0] ?? ''));
            $dienMoi = isset($data[3]) && $data[3] !== '' ? (int)$data[3] : -1;
            $nuocMoi = isset($data[4]) && $data[4] !== '' ? (int)$data[4] : -1;
            $soSV = isset($data[5]) && (int)$data[5] > 0 ? (int)$data[5] : null;

            if (empty($maPhong) || $dienMoi < 0 || $nuocMoi < 0) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: Dữ liệu phòng hoặc chỉ số không hợp lệ.";
                continue;
            }

            // Kiểm tra phạm vi phân quyền
            if ($scope['level'] === 'TOA') {
                $room = Database::fetch("SELECT MaToaNha FROM Phong WHERE MaPhong = :p", ['p' => $maPhong]);
                if (!$room || $room['MaToaNha'] !== $scope['maToa']) {
                    $errorCount++;
                    $errorDetails[] = "Dòng {$rowIdx}: Phòng {$maPhong} không thuộc Tòa nhà bạn quản lý.";
                    continue;
                }
            }

            $res = UtilityBillingService::recordReading($maPhong, $thang, $nam, $dienMoi, $nuocMoi, null, null, $soSV);

            if ($res['success']) {
                $successCount++;
                if (!empty($res['warnings']) || !empty($res['isAnomaly'])) {
                    $anomalyCount++;
                }
            } else {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx} ({$maPhong}): " . $res['message'];
            }
        }

        fclose($handle);

        $msg = "Nhập file hoàn tất: Thành công ghi nhận <b>{$successCount}</b> phòng.";
        if ($anomalyCount > 0) {
            $msg .= " Có <b>{$anomalyCount}</b> phòng có chỉ số tăng đột biến nghi ngờ rò rỉ.";
        }
        if ($errorCount > 0) {
            $msg .= " Lỗi <b>{$errorCount}</b> dòng: " . implode('; ', array_slice($errorDetails, 0, 3));
            Session::flash('error', $msg);
        } else {
            Session::flash('success', $msg);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}&tab=readings");
    }
}
