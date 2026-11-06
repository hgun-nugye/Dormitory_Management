<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Services\AttendanceDisciplineService;

/**
 * Controller: Giám sát An ninh Ra Vào & Đánh giá Rèn luyện Nội trú
 * URL: /staff/discipline
 * View: views/staff/discipline.php
 * 
 * PHÂN HỆ RBAC 5 CẤP:
 * - Cấp 4 (ALL): Quản trị viên / Ban Giám đốc toàn KTX (Khu A & B)
 * - Cấp 3 (KHU): Trưởng Ban Quản lý Khu (Khu A hoặc Khu B)
 * - Cấp 2 (CUM): Trưởng Ban Quản lý Cụm (Cụm AG, AH, BA, BB, BC...)
 * - Cấp 1 (TOA): Trưởng Tòa nhà (A01, H01, BA1...)
 * - Cấp An ninh (AN_NINH / BAO_VE): Cán bộ Đội An ninh & Bảo vệ KTX (Quản lý Barrier & Vi phạm trật tự)
 * - CHẶN 100%: Sinh viên, Trưởng phòng SV, Kế toán, Kỹ thuật viên điện nước, Nhân viên thường phi an ninh.
 */
class DisciplineController extends Controller
{
    /**
     * Xác định phân quyền và phạm vi quản lý của cán bộ đăng nhập
     */
    public function getStaffScope(?array $user = null): array
    {
        if (!$user) {
            $user = Auth::user() ?? [];
        }

        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = trim((string)($user['MaCumQuanLy'] ?? ''));
        $username = trim((string)($user['TenDangNhap'] ?? ''));
        $chucVu = mb_strtolower(trim((string)($user['ChucVu'] ?? $user['profile']['ChucVu'] ?? '')));

        // Xác định tên vai trò / chức vụ hiển thị thân thiện (để hiển thị động trong thông báo từ chối)
        $userRoleName = 'Người dùng';
        if ($role === 'SINH_VIEN' || $role === 'TRUONG_PHONG') {
            $userRoleName = ($role === 'TRUONG_PHONG') ? 'Trưởng phòng Sinh viên' : 'Sinh viên nội trú';
        } elseif ($role === 'KE_TOAN' || str_contains($chucVu, 'kế toán')) {
            $userRoleName = 'Nhân viên Kế toán';
        } elseif (str_starts_with($username, 'NV_KT') || str_starts_with($username, 'KT') || str_contains($chucVu, 'kỹ thuật') || str_contains($chucVu, 'điện nước') || str_contains($chucVu, 'bảo trì')) {
            $userRoleName = 'Chuyên viên Kỹ thuật điện nước';
        } elseif ($role === 'NV_LE_TAN' || str_contains($chucVu, 'lễ tân')) {
            $userRoleName = 'Nhân viên Lễ tân';
        } elseif (str_contains($chucVu, 'hành chính') || str_contains($chucVu, 'văn phòng')) {
            $userRoleName = 'Nhân viên Hành chính';
        } elseif (!empty($user['ChucVu'])) {
            $userRoleName = trim($user['ChucVu']);
        } elseif ($role === 'NV_THUONG') {
            $userRoleName = 'Nhân viên thường';
        }

        // Kiểm tra xem có phải Cán bộ Đội An ninh / Bảo vệ hay không
        $isSecurityCandidate = in_array($role, ['AN_NINH', 'BAO_VE', 'NV_AN_NINH', 'NV_BAO_VE'], true)
            || str_starts_with($username, 'NV_BV')
            || str_starts_with($username, 'BV')
            || str_contains($chucVu, 'bảo vệ')
            || str_contains($chucVu, 'an ninh')
            || str_contains($chucVu, 'trực barrier')
            || str_contains($chucVu, 'tuần tra');

        // Danh sách vai trò Cán bộ Quản lý hợp lệ
        $isManager = in_array($role, ['ADMIN', 'QuanTriVien', 'NV_QUAN_LY_KTX', 'BGD', 'BAN_GIAM_DOC', 'TRUONG_KHU', 'TRUONG_CUM', 'TRUONG_TOA'], true);

        // 1. CHẶN 100% CÁC VAI TRÒ KHÔNG CÓ THẨM QUYỀN
        // Sinh viên, Kế toán, Kỹ thuật viên điện nước, Nhân viên thường phi an ninh
        $isNonManagerBlocked = in_array($role, ['KE_TOAN', 'SINH_VIEN', 'TRUONG_PHONG'], true)
            || str_contains($chucVu, 'kế toán')
            || str_starts_with($username, 'NV_KT')
            || str_starts_with($username, 'KT')
            || str_contains($chucVu, 'kỹ thuật')
            || str_contains($chucVu, 'điện nước')
            || str_contains($chucVu, 'bảo trì');

        if (empty($role) || $isNonManagerBlocked || (!$isManager && !$isSecurityCandidate)) {
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
                'canLockPoints' => false,
                'canUnlockPoints' => false,
                'canRecordEvent' => false,
                'canHandleAbsence' => false,
                'canSimulateScan' => false,
                'canFilterAll' => false,
                'isSecurity' => false,
                'availableToas' => [],
                'availableCums' => [],
                'availableKhus' => [],
                'message' => "Từ chối quyền truy cập: Phân hệ Giám sát An ninh Ra Vào & Đánh giá Rèn luyện chỉ dành riêng cho Cán bộ Quản lý KTX (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX) và Cán bộ Đội An ninh KTX. {$userRoleName} không có quyền thao tác!"
            ];
        }

        // 2. CẤP 4: Quản trị viên hệ thống / Quản lý toàn KTX (Admin / Ban Giám đốc)
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
                'title' => 'Quản trị viên / Quản lý toàn KTX',
                'badgeClass' => 'badge-primary',
                'userRoleName' => 'Ban Giám đốc / Cán bộ Quản trị',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'maToaNha' => null,
                'canLockPoints' => true,
                'canUnlockPoints' => true,
                'canRecordEvent' => true,
                'canHandleAbsence' => true,
                'canSimulateScan' => true,
                'canFilterAll' => true,
                'isSecurity' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 3. CẤP AN NINH: Cán bộ Đội An ninh & Bảo vệ KTX (Chế độ giám sát Barrier & Xử lý sự cố trật tự)
        if ($isSecurityCandidate && !$isManager) {
            $maKhu = 'A';
            if ($cumQuanLy === 'A' || $cumQuanLy === 'B') {
                $maKhu = $cumQuanLy;
            } elseif (!empty($cumQuanLy)) {
                $cumRow = Database::fetch("SELECT MaKhu FROM CumNha WHERE MaCum = :c", ['c' => $cumQuanLy]);
                if ($cumRow && !empty($cumRow['MaKhu'])) {
                    $maKhu = $cumRow['MaKhu'];
                }
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
                'level' => 'AN_NINH',
                'levelNumber' => 0,
                'role' => $role,
                'title' => "Cán bộ Đội An ninh (Khu {$maKhu})",
                'badgeClass' => 'badge-info',
                'userRoleName' => "Cán bộ An ninh Khu {$maKhu}",
                'maKhu' => $maKhu,
                'maCum' => !empty($cumQuanLy) ? $cumQuanLy : null,
                'maToa' => null,
                'maToaNha' => null,
                'canLockPoints' => false,            // Tuyệt đối không chốt điểm
                'canUnlockPoints' => false,          // Không mở khóa điểm
                'canRecordEvent' => true,            // Ghi nhận vi phạm an ninh / trật tự
                'canHandleAbsence' => false,         // Không xử lý vắng mặt (do BQL đảm nhận)
                'canSimulateScan' => true,           // Được quét thẻ barrier
                'canFilterAll' => false,
                'isSecurity' => true,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 4. CẤP 3: Trưởng ban Quản lý Khu KTX (TRUONG_KHU: ví dụ TK001 -> Khu A, TK002 -> Khu B)
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
                'canLockPoints' => true,             // Chốt điểm toàn Khu
                'canUnlockPoints' => true,           // Mở khóa điểm toàn Khu
                'canRecordEvent' => true,
                'canHandleAbsence' => true,          // Lập biên bản vắng mặt
                'canSimulateScan' => true,
                'canFilterAll' => false,
                'isSecurity' => false,
                'availableToas' => $toas,
                'availableCums' => $cums,
                'availableKhus' => $khus,
                'message' => ''
            ];
        }

        // 5. CẤP 2: Trưởng Ban Quản lý Cụm nhà (TRUONG_CUM: ví dụ CUMAG -> AG...)
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
                'canLockPoints' => true,             // Chốt điểm sinh viên Cụm gửi trường
                'canUnlockPoints' => false,          // Không tự mở khóa điểm sau khi gửi
                'canRecordEvent' => true,
                'canHandleAbsence' => true,
                'canSimulateScan' => true,
                'canFilterAll' => false,
                'isSecurity' => false,
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

        $tenToa = $toaRow['TenToaNha'] ?? $maToa;
        $cleanToa = preg_replace('/^(Tòa\s*nhà\s*|Tòa\s*)+/ui', '', $tenToa);
        $toaTitle = "Trưởng Tòa " . (!empty($cleanToa) ? $cleanToa : $tenToa);
        $maCum = $toaRow['MaCum'] ?? 'AG';
        $maKhu = $toaRow['MaKhu'] ?? 'A';

        return [
            'isAllowed' => true,
            'level' => 'TOA',
            'levelNumber' => 1,
            'role' => $role,
            'title' => $toaTitle,
            'badgeClass' => 'badge-success',
            'userRoleName' => 'Trưởng Tòa nhà',
            'maKhu' => $maKhu,
            'maCum' => $maCum,
            'maToa' => $maToa,
            'maToaNha' => $maToa,
            'canLockPoints' => false,            // TUYỆT ĐỐI KHÔNG CHỐT ĐIỂM GỬI TRƯỜNG
            'canUnlockPoints' => false,          // Không mở khóa điểm
            'canRecordEvent' => true,            // Ghi nhận vi phạm / khen thưởng tòa nhà
            'canHandleAbsence' => true,          // Liên hệ xác minh sinh viên vắng mặt
            'canSimulateScan' => true,
            'canFilterAll' => false,
            'isSecurity' => false,
            'availableToas' => [$toaRow],
            'availableCums' => [['MaCum' => $maCum, 'TenCum' => $toaRow['TenCum'] ?? $maCum, 'MaKhu' => $maKhu]],
            'availableKhus' => [['MaKhu' => $maKhu, 'TenKhu' => $toaRow['TenKhu'] ?? $maKhu]],
            'message' => ''
        ];
    }

    /**
     * Màn hình chính Giám sát An ninh Ra Vào & Đánh giá Rèn luyện
     */
    public function discipline(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        // TH1: TỪ CHỐI TRUY CẬP (Hiển thị Thẻ cảnh báo đỏ chuẩn mực)
        if (!$scope['isAllowed']) {
            $this->view('staff.discipline', [
                'user' => $user,
                'scope' => $scope,
                'isAllowed' => false,
                'namHoc' => '2026-2027',
                'hocKy' => 1,
                'search' => '',
                'huongDi' => '',
                'activeTab' => 'canh-bao',
                'selectedKhu' => '',
                'selectedCum' => '',
                'selectedToa' => '',
                'selectedTang' => '',
                'selectedRating' => 'all',
                'selectedLockStatus' => 'all',
                'absenceWarnings' => [],
                'barrierLogs' => [],
                'barrierTotal' => 0,
                'summaryList' => [],
                'clusters' => [],
                'universities' => [],
                'residentStudents' => [],
                'stats' => [
                    'totalResident' => 0,
                    'absenceWarnings' => 0,
                    'totalBarrierScans' => 0,
                    'excellentCount' => 0,
                    'goodCount' => 0,
                    'fairCount' => 0,
                    'averageCount' => 0,
                    'weakCount' => 0,
                    'lockedCount' => 0
                ],
                'flashSuccess' => null,
                'flashError' => null,
                'flashWarning' => null
            ]);
            return;
        }

        // TH2: CÁN BỘ & ĐỘI AN NINH ĐƯỢC PHÉP TRUY CẬP
        $namHoc = trim($_GET['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_GET['hocKy'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $huongDi = trim($_GET['huongDi'] ?? '');
        $activeTab = trim($_GET['tab'] ?? 'canh-bao');
        $selectedRating = trim($_GET['rating'] ?? 'all');
        $selectedLockStatus = trim($_GET['lockStatus'] ?? 'all');
        $selectedTang = trim($_GET['tang'] ?? '');

        // Lấy phạm vi mặc định từ Scope
        $filterKhu = $scope['maKhu'];
        $filterCum = $scope['maCum'];
        $filterToa = $scope['maToa'];

        // Xử lý bộ lọc cascading theo quyền hạn
        if ($scope['level'] === 'ALL') {
            $khuParam = trim($_GET['khu'] ?? '');
            if (!empty($khuParam)) {
                $filterKhu = $khuParam;
            }
            $cumParam = trim($_GET['cum'] ?? '');
            if (!empty($cumParam) && $cumParam !== 'all') {
                $filterCum = $cumParam;
            }
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') {
                $filterToa = $toaParam;
            }
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'AN_NINH') {
            $cumParam = trim($_GET['cum'] ?? '');
            if (!empty($cumParam) && $cumParam !== 'all') {
                $filterCum = $cumParam;
            }
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') {
                $filterToa = $toaParam;
            }
        } elseif ($scope['level'] === 'CUM') {
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') {
                $filterToa = $toaParam;
            }
        }

        // 1. Lấy danh sách cảnh báo vắng mặt > 30 ngày không phép
        $absenceData = AttendanceDisciplineService::detectLongTermAbsence(30, $filterCum, $filterToa, $filterKhu);
        $absenceWarnings = $absenceData['warnings'] ?? [];

        // Lọc tìm kiếm & tầng cho cảnh báo vắng mặt
        if (!empty($search)) {
            $searchLower = mb_strtolower($search);
            $absenceWarnings = array_values(array_filter($absenceWarnings, function ($w) use ($searchLower) {
                return str_contains(mb_strtolower($w['maSinhVien'] ?? ''), $searchLower)
                    || str_contains(mb_strtolower($w['hoVaTen'] ?? ''), $searchLower)
                    || str_contains(mb_strtolower($w['maPhong'] ?? ''), $searchLower)
                    || str_contains(mb_strtolower($w['maToaNha'] ?? ''), $searchLower);
            }));
        }

        // 2. Lấy nhật ký quẹt thẻ Barrier mới nhất
        $barrierData = AttendanceDisciplineService::getBarrierLogs(
            $filterCum,
            $filterToa,
            $filterKhu,
            $search,
            $huongDi,
            100,
            0
        );
        $barrierLogs = $barrierData['logs'] ?? [];

        // 3. Lấy danh sách tổng hợp điểm rèn luyện
        $summaryList = AttendanceDisciplineService::getStudentDisciplineSummary(
            $namHoc,
            $hocKy,
            $filterCum,
            $filterToa,
            $filterKhu,
            $search
        );

        // Lọc nâng cao cho bảng Điểm rèn luyện: Tầng, Xếp loại, Trạng thái gửi trường
        if (!empty($selectedTang) || $selectedRating !== 'all' || $selectedLockStatus !== 'all') {
            $summaryList = array_values(array_filter($summaryList, function ($row) use ($selectedTang, $selectedRating, $selectedLockStatus) {
                // Lọc tầng theo mã phòng (ví dụ P101 -> tầng 1, P1205 -> tầng 12)
                if (!empty($selectedTang)) {
                    $phong = (string)($row['MaPhong'] ?? '');
                    $floor = preg_replace('/\D/', '', $phong);
                    $floorNumber = (strlen($floor) >= 3) ? (int)substr($floor, 0, -2) : 0;
                    if ($floorNumber !== (int)$selectedTang) {
                        return false;
                    }
                }

                // Lọc theo xếp loại
                if ($selectedRating !== 'all' && ($row['XepLoai'] ?? '') !== $selectedRating) {
                    return false;
                }

                // Lọc theo trạng thái gửi trường
                if ($selectedLockStatus === 'da-gui' && (int)($row['DaGuiVeTruong'] ?? 0) !== 1) {
                    return false;
                }
                if ($selectedLockStatus === 'chua-gui' && (int)($row['DaGuiVeTruong'] ?? 0) === 1) {
                    return false;
                }

                return true;
            }));
        }

        // 4. Lấy danh sách Cụm, Tòa, Trường đại học phục vụ bộ lọc & modal
        $clusters = Database::fetchAll("
            SELECT cn.MaCum, cn.TenCum, cn.MaKhu, k.TenKhu
            FROM CumNha cn
            JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
            ORDER BY cn.MaKhu ASC, cn.MaCum ASC
        ");

        $universities = Database::fetchAll("SELECT MaTruong, TenTruong FROM TruongDaiHoc ORDER BY TenTruong ASC");

        // 5. Lấy danh sách sinh viên đang nội trú trong phạm vi để đổ vào dropdown modal
        $studentsWhere = ["hd.TrangThaiHopDong = 'Đang hiệu lực'"];
        $studentsParams = [];
        if (!empty($filterToa)) {
            $studentsWhere[] = "tn.MaToaNha = :s_toa";
            $studentsParams['s_toa'] = $filterToa;
        } elseif (!empty($filterCum)) {
            $studentsWhere[] = "tn.MaCum = :s_cum";
            $studentsParams['s_cum'] = $filterCum;
        } elseif (!empty($filterKhu)) {
            $studentsWhere[] = "cn.MaKhu = :s_khu";
            $studentsParams['s_khu'] = $filterKhu;
        }
        $studentsWhereSql = implode(' AND ', $studentsWhere);

        $residentStudents = Database::fetchAll("
            SELECT sv.MaSinhVien, sv.HoVaTen, g.MaPhong, tn.MaToaNha, cn.TenCum
            FROM SinhVien sv
            JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien
            JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
            JOIN Phong p ON g.MaPhong = p.MaPhong
            JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
            JOIN CumNha cn ON tn.MaCum = cn.MaCum
            WHERE {$studentsWhereSql}
            ORDER BY sv.HoVaTen ASC
        ", $studentsParams);

        // 6. Thống kê nhanh 4 chỉ số KPI Cards
        $stats = [
            'totalResident' => count($summaryList),
            'absenceWarnings' => count($absenceWarnings),
            'totalBarrierScans' => $barrierData['total'] ?? count($barrierLogs),
            'excellentCount' => 0,
            'goodCount' => 0,
            'fairCount' => 0,
            'averageCount' => 0,
            'weakCount' => 0,
            'lockedCount' => 0
        ];

        foreach ($summaryList as $row) {
            $r = $row['XepLoai'] ?? '';
            if ($r === 'Xuất sắc') $stats['excellentCount']++;
            elseif ($r === 'Tốt') $stats['goodCount']++;
            elseif ($r === 'Khá') $stats['fairCount']++;
            elseif ($r === 'Trung bình') $stats['averageCount']++;
            elseif ($r === 'Yếu') $stats['weakCount']++;

            if ((int)($row['DaGuiVeTruong'] ?? 0) === 1) $stats['lockedCount']++;
        }

        $this->view('staff.discipline', [
            'user' => $user,
            'scope' => $scope,
            'isAllowed' => true,
            'namHoc' => $namHoc,
            'hocKy' => $hocKy,
            'search' => $search,
            'huongDi' => $huongDi,
            'activeTab' => $activeTab,
            'selectedKhu' => $filterKhu ?? '',
            'selectedCum' => $filterCum ?? '',
            'selectedToa' => $filterToa ?? '',
            'selectedTang' => $selectedTang,
            'selectedRating' => $selectedRating,
            'selectedLockStatus' => $selectedLockStatus,
            'absenceWarnings' => $absenceWarnings,
            'barrierLogs' => $barrierLogs,
            'barrierTotal' => $barrierData['total'] ?? count($barrierLogs),
            'summaryList' => $summaryList,
            'clusters' => $clusters,
            'universities' => $universities,
            'residentStudents' => $residentStudents,
            'stats' => $stats,
            'flashSuccess' => Session::flash('success'),
            'flashError' => Session::flash('error'),
            'flashWarning' => Session::flash('warning')
        ]);
    }

    /**
     * Ghi nhận sự kiện rèn luyện / vi phạm nội quy
     */
    public function record(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || empty($scope['canRecordEvent'])) {
            Session::flash('error', 'Từ chối quyền: Tài khoản của bạn không có quyền ghi nhận vi phạm và điểm rèn luyện!');
            $this->redirect('staff/dashboard');
            return;
        }

        $maSinhVien = trim($_POST['maSinhVien'] ?? '');
        $namHoc = trim($_POST['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_POST['hocKy'] ?? 1);
        $loaiGhiNhan = trim($_POST['loaiGhiNhan'] ?? 'Khen thưởng');
        $noiDung = trim($_POST['noiDung'] ?? '');
        $diemThayDoi = (int)($_POST['diemThayDoi'] ?? 0);

        if (empty($maSinhVien) || empty($noiDung) || $diemThayDoi === 0) {
            Session::flash('error', 'Vui lòng chọn sinh viên, nhập nội dung chi tiết và số điểm thay đổi (+ hoặc -)!');
            $this->redirect('staff/discipline?tab=ren-luyen');
            return;
        }

        $result = AttendanceDisciplineService::recordDisciplineEvent(
            $maSinhVien,
            $namHoc,
            $hocKy,
            $loaiGhiNhan,
            $noiDung,
            $diemThayDoi,
            $user['HoVaTen'] ?? $user['TenDangNhap'] ?? 'Cán bộ'
        );

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/discipline?tab=ren-luyen&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy);
    }

    /**
     * Chốt điểm và gửi báo cáo điểm rèn luyện về trường (Khóa chỉnh sửa)
     */
    public function lockSubmit(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || empty($scope['canLockPoints'])) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền chốt điểm rèn luyện gửi trường.');
            $this->redirect('staff/discipline?tab=ren-luyen');
            return;
        }

        $namHoc = trim($_POST['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_POST['hocKy'] ?? 1);
        $maTruong = trim($_POST['maTruong'] ?? '');
        $maCum = $scope['maCum'];

        $result = AttendanceDisciplineService::lockAndSendToUniversity(
            $namHoc,
            $hocKy,
            !empty($maTruong) ? $maTruong : null,
            $maCum
        );

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/discipline?tab=ren-luyen&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy);
    }

    /**
     * Quét thẻ Barrier giả lập
     */
    public function simulateScan(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || empty($scope['canSimulateScan'])) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền thao tác trên hệ thống kiểm soát barrier!');
            $this->redirect('staff/dashboard');
            return;
        }

        $maSinhVien = trim($_POST['maSinhVien'] ?? '');
        $congKiemSoat = trim($_POST['congKiemSoat'] ?? 'Cổng chính A01');
        $huongDi = trim($_POST['huongDi'] ?? 'Vào');

        if (empty($maSinhVien) || empty($congKiemSoat)) {
            Session::flash('error', 'Vui lòng chọn sinh viên và vị trí cổng quét thẻ!');
            $this->redirect('staff/discipline?tab=barrier');
            return;
        }

        $result = AttendanceDisciplineService::simulateBarrierScan($maSinhVien, $congKiemSoat, $huongDi);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/discipline?tab=barrier');
    }

    /**
     * Xử lý trường hợp vắng mặt kéo dài (Lập biên bản vi phạm hoặc liên hệ)
     */
    public function absenceAction(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || empty($scope['canHandleAbsence'])) {
            Session::flash('error', 'Từ chối quyền: Chỉ cán bộ quản lý mới có quyền xử lý cảnh báo vắng mặt!');
            $this->redirect('staff/dashboard');
            return;
        }

        $maSinhVien = trim($_POST['maSinhVien'] ?? '');
        $hanhDong = trim($_POST['hanhDong'] ?? 'lien_he');
        $ghiChu = trim($_POST['ghiChu'] ?? '');
        $namHoc = trim($_POST['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_POST['hocKy'] ?? 1);

        if (empty($maSinhVien)) {
            Session::flash('error', 'Mã sinh viên không hợp lệ.');
            $this->redirect('staff/discipline?tab=canh-bao');
            return;
        }

        $result = AttendanceDisciplineService::createAbsenceAction(
            $maSinhVien,
            $hanhDong,
            $ghiChu,
            $namHoc,
            $hocKy
        );

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/discipline?tab=canh-bao');
    }

    /**
     * Lấy lịch sử chi tiết rèn luyện của sinh viên (Trả về JSON)
     */
    public function history(): void
    {
        (new \App\Controllers\Api\DisciplineApiController())->history();
    }

    /**
     * Xuất báo cáo dữ liệu theo Tab ra file CSV kèm UTF-8 BOM
     */
    public function export(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền xuất dữ liệu phân hệ này!');
            $this->redirect('staff/discipline');
            return;
        }

        $tab = trim($_GET['tab'] ?? 'ren-luyen');
        $namHoc = trim($_GET['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_GET['hocKy'] ?? 1);
        $maTruong = trim($_GET['maTruong'] ?? '');

        // Lấy phạm vi mặc định từ Scope
        $filterKhu = $scope['maKhu'];
        $filterCum = $scope['maCum'];
        $filterToa = $scope['maToa'];

        if ($scope['level'] === 'ALL') {
            $khuParam = trim($_GET['khu'] ?? '');
            if (!empty($khuParam)) $filterKhu = $khuParam;
            $cumParam = trim($_GET['cum'] ?? '');
            if (!empty($cumParam) && $cumParam !== 'all') $filterCum = $cumParam;
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') $filterToa = $toaParam;
        } elseif ($scope['level'] === 'KHU' || $scope['level'] === 'AN_NINH') {
            $cumParam = trim($_GET['cum'] ?? '');
            if (!empty($cumParam) && $cumParam !== 'all') $filterCum = $cumParam;
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') $filterToa = $toaParam;
        } elseif ($scope['level'] === 'CUM') {
            $toaParam = trim($_GET['toa'] ?? '');
            if (!empty($toaParam) && $toaParam !== 'all') $filterToa = $toaParam;
        }

        // =====================================================================
        // TRƯỜNG HỢP 1: TAB CẢNH BÁO VẮNG MẶT > 30 NGÀY
        // =====================================================================
        if ($tab === 'canh-bao') {
            $absenceData = AttendanceDisciplineService::detectLongTermAbsence(30, $filterCum, $filterToa, $filterKhu);
            $warnings = $absenceData['warnings'] ?? [];

            $fileName = "CanhBao_VangMat_30Ngay_" . date('Ymd_His') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo "\xEF\xBB\xBF";

            $output = fopen('php://output', 'w');
            fputcsv($output, ['STT', 'Mã Sinh Viên', 'Họ và Tên', 'SĐT Sinh Viên', 'SĐT Phụ Huynh', 'Phòng', 'Tòa Nhà', 'Cụm', 'Khu', 'Trường ĐH', 'Lần Quét Cuối', 'Cổng Quét', 'Hướng', 'Số Ngày Vắng', 'Tình Trạng']);

            $stt = 1;
            foreach ($warnings as $w) {
                fputcsv($output, [
                    $stt++,
                    $w['maSinhVien'] ?? '',
                    $w['hoVaTen'] ?? '',
                    $w['soDienThoai'] ?? '',
                    $w['sdtPhuHuynh'] ?? '',
                    $w['maPhong'] ?? '',
                    $w['maToaNha'] ?? '',
                    $w['maCum'] ?? '',
                    $w['maKhu'] ?? '',
                    $w['tenTruong'] ?? '',
                    $w['ngayQuetCuoi'] ?? '',
                    $w['congCuoi'] ?? '',
                    $w['huongCuoi'] ?? '',
                    $w['soNgayVang'] ?? '',
                    'Không có đơn xin tạm vắng'
                ]);
            }
            fclose($output);
            exit;
        }

        // =====================================================================
        // TRƯỜNG HỢP 2: TAB NHẬT KÝ QUÉT THẺ BARRIER
        // =====================================================================
        if ($tab === 'barrier') {
            $search = trim($_GET['search'] ?? '');
            $huongDi = trim($_GET['huongDi'] ?? '');
            $barrierData = AttendanceDisciplineService::getBarrierLogs(
                $filterCum,
                $filterToa,
                $filterKhu,
                $search,
                $huongDi,
                500,
                0
            );
            $logs = $barrierData['logs'] ?? [];

            $fileName = "NhatKy_QuetThe_Barrier_" . date('Ymd_His') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            echo "\xEF\xBB\xBF";

            $output = fopen('php://output', 'w');
            fputcsv($output, ['STT', 'Mã Lượt', 'Mã Sinh Viên', 'Họ và Tên', 'Phòng', 'Tòa Nhà', 'Cụm', 'Trường ĐH', 'Cổng Kiểm Soát', 'Hướng Đi', 'Thời Gian Quét', 'Trạng Thái']);

            $stt = 1;
            foreach ($logs as $l) {
                fputcsv($output, [
                    $stt++,
                    $l['MaLuot'] ?? '',
                    $l['MaSinhVien'] ?? '',
                    $l['HoVaTen'] ?? '',
                    $l['MaPhong'] ?? '',
                    $l['MaToaNha'] ?? '',
                    $l['TenCum'] ?? '',
                    $l['TenTruong'] ?? $l['MaTruong'] ?? '',
                    $l['CongKiemSoat'] ?? '',
                    $l['HuongDi'] ?? '',
                    $l['ThoiGianQuet'] ?? '',
                    'Hợp lệ'
                ]);
            }
            fclose($output);
            exit;
        }

        // =====================================================================
        // TRƯỜNG HỢP 3: TAB ĐIỂM RÈN LUYỆN NỘI TRÚ (MẶC ĐỊNH)
        // =====================================================================
        $data = AttendanceDisciplineService::exportTrainingPointsByUniversity($hocKy, $namHoc, !empty($maTruong) ? $maTruong : null);

        $fileName = "BaoCao_DiemRenLuyen_HK{$hocKy}_{$namHoc}_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');

        // Xuất BOM cho Excel hiển thị chuẩn tiếng Việt
        echo "\xEF\xBB\xBF";

        $output = fopen('php://output', 'w');
        fputcsv($output, ['STT', 'Trường ĐH', 'Mã Sinh Viên', 'Họ và Tên', 'Học Kỳ', 'Năm Học', 'Tổng Điểm', 'Xếp Loại', 'Trạng Thái']);

        $stt = 1;
        foreach ($data as $group) {
            foreach ($group['students'] as $s) {
                fputcsv($output, [
                    $stt++,
                    $group['tenTruong'],
                    $s['MaSinhVien'],
                    $s['HoVaTen'],
                    $s['HocKy'],
                    $s['NamHoc'],
                    $s['TongDiemCuoiKy'],
                    $s['XepLoai'],
                    ((int)$s['DaGuiVeTruong'] === 1) ? 'Đã gửi trường' : 'Chưa gửi'
                ]);
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách điểm rèn luyện từ file CSV
     * Format: MaSinhVien, LoaiGhiNhan, NoiDung, DiemThayDoi, NamHoc, HocKy
     */
    public function import(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        if (!$scope['isAllowed'] || !$scope['canRecordEvent']) {
            Session::flash('error', 'Từ chối quyền: Bạn không có quyền nhập điểm rèn luyện!');
            $this->redirect('staff/discipline');
            return;
        }

        $namHoc = trim($_POST['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_POST['hocKy'] ?? 1);

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn file CSV hợp lệ.');
            $this->redirect("staff/discipline?namHoc={$namHoc}&hocKy={$hocKy}");
            return;
        }

        $fileTmp = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($fileTmp, 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể đọc nội dung file đã tải lên.');
            $this->redirect("staff/discipline?namHoc={$namHoc}&hocKy={$hocKy}");
            return;
        }

        // Bỏ BOM nếu có và bỏ dòng header
        $firstLine = fgets($handle);

        $successCount = 0;
        $errorCount = 0;
        $errorDetails = [];
        $rowIdx = 1;

        while (($data = fgetcsv($handle, 2000, ",")) !== false) {
            $rowIdx++;
            if (empty($data) || count($data) < 4) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: Thiếu cột dữ liệu (cần ít nhất 4 cột).";
                continue;
            }

            $maSv      = trim((string)($data[0] ?? ''));
            $loai      = trim((string)($data[1] ?? ''));
            $noiDung   = trim((string)($data[2] ?? ''));
            $diemThayDoi = (int)($data[3] ?? 0);
            $rowNamHoc = !empty(trim($data[4] ?? '')) ? trim($data[4]) : $namHoc;
            $rowHocKy  = !empty(trim($data[5] ?? '')) ? (int)$data[5] : $hocKy;

            // Validate cơ bản
            if (empty($maSv)) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: Mã sinh viên trống.";
                continue;
            }

            if (!in_array($loai, ['Vi phạm', 'Khen thưởng'])) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: Loại ghi nhận không hợp lệ (phải là 'Vi phạm' hoặc 'Khen thưởng').";
                continue;
            }

            if ($diemThayDoi == 0) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx} ({$maSv}): Điểm thay đổi = 0 (bỏ qua).";
                continue;
            }

            // Kiểm tra SV có tồn tại và thuộc phạm vi quản lý
            $svCheck = Database::fetch(
                "SELECT sv.MaSinhVien, sv.MaPhong, p.MaToaNha, t.MaCum, c.MaKhu
                 FROM SinhVien sv
                 LEFT JOIN Phong p ON sv.MaPhong = p.MaPhong
                 LEFT JOIN ToaNha t ON p.MaToaNha = t.MaToaNha
                 LEFT JOIN CumKTX c ON t.MaCum = c.MaCum
                 WHERE sv.MaSinhVien = :ma",
                ['ma' => $maSv]
            );

            if (!$svCheck) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: Sinh viên {$maSv} không tồn tại trong hệ thống.";
                continue;
            }

            // Kiểm tra phạm vi phân quyền
            $inScope = true;
            if ($scope['level'] === 'TOA' && $svCheck['MaToaNha'] !== $scope['maToa']) $inScope = false;
            if ($scope['level'] === 'CUM' && $svCheck['MaCum'] !== $scope['maCum']) $inScope = false;
            if ($scope['level'] === 'KHU' && $svCheck['MaKhu'] !== $scope['maKhu']) $inScope = false;

            if (!$inScope) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx}: SV {$maSv} không thuộc phạm vi quản lý của bạn.";
                continue;
            }

            // Tính tổng điểm hiện tại
            $currentScore = Database::fetch(
                "SELECT COALESCE(SUM(DiemThayDoi), 0) AS total
                 FROM RenLuyenNoiTru
                 WHERE MaSinhVien = :ma AND NamHoc = :nh AND HocKy = :hk",
                ['ma' => $maSv, 'nh' => $rowNamHoc, 'hk' => $rowHocKy]
            );
            $tongDiem = (int)($currentScore['total'] ?? 0) + $diemThayDoi;

            // Xếp loại
            if ($tongDiem >= 90) $xepLoai = 'Xuất sắc';
            elseif ($tongDiem >= 80) $xepLoai = 'Tốt';
            elseif ($tongDiem >= 65) $xepLoai = 'Khá';
            elseif ($tongDiem >= 50) $xepLoai = 'Trung bình';
            elseif ($tongDiem >= 35) $xepLoai = 'Yếu';
            else $xepLoai = 'Kém';

            try {
                Database::execute(
                    "INSERT INTO RenLuyenNoiTru (MaSinhVien, NamHoc, HocKy, LoaiGhiNhan, NoiDung, DiemThayDoi, TongDiemCuoiKy, XepLoai, DaGuiVeTruong)
                     VALUES (:ma, :nh, :hk, :loai, :nd, :diem, :tong, :xl, 0)",
                    [
                        'ma'   => $maSv,
                        'nh'   => $rowNamHoc,
                        'hk'   => $rowHocKy,
                        'loai' => $loai,
                        'nd'   => $noiDung,
                        'diem' => $diemThayDoi,
                        'tong' => $tongDiem,
                        'xl'   => $xepLoai,
                    ]
                );
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errorDetails[] = "Dòng {$rowIdx} ({$maSv}): Lỗi DB - " . $e->getMessage();
            }
        }
        fclose($handle);

        $msg = "Nhập file hoàn tất: {$successCount} dòng thành công";
        if ($errorCount > 0) {
            $msg .= ", {$errorCount} dòng lỗi";
            if (count($errorDetails) <= 5) {
                $msg .= " (" . implode('; ', $errorDetails) . ")";
            } else {
                $msg .= " (" . implode('; ', array_slice($errorDetails, 0, 5)) . "; ...)";
            }
        }

        if ($successCount > 0) {
            Session::flash('success', $msg);
        } else {
            Session::flash('error', $msg);
        }

        $this->redirect("staff/discipline?namHoc={$namHoc}&hocKy={$hocKy}&tab=ren-luyen");
    }
}
