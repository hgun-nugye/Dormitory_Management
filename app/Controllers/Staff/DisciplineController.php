<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Services\AttendanceDisciplineService;

/**
 * Controller: An ninh Ra Vào & Đánh giá Rèn luyện Sinh viên
 * View: views/staff/discipline.php
 */
class DisciplineController extends Controller
{
    /**
     * Xác định phân quyền và phạm vi quản lý của cán bộ
     */
    private function getStaffScope(?array $user): array
    {
        $role = $user['MaVaiTro'] ?? $user['VaiTro'] ?? '';
        $cumQuanLy = $user['MaCumQuanLy'] ?? '';

        // 1. CẤP 4: Quản trị viên hệ thống / Quản lý toàn KTX
        if ($role === 'ADMIN' || $role === 'NV_QUAN_LY_KTX' || empty($role)) {
            return [
                'level' => 'ALL',
                'levelNumber' => 4,
                'role' => $role,
                'title' => 'Quản trị viên / Quản lý toàn KTX',
                'badgeClass' => 'badge-primary',
                'maKhu' => null,
                'maCum' => null,
                'maToa' => null,
                'canLockPoints' => true,
                'canUnlockPoints' => true,
                'canFilterAll' => true
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
                'canLockPoints' => false,
                'canUnlockPoints' => false,
                'canFilterAll' => false
            ];
        }

        // 3. CẤP 2: Trưởng Cụm nhà (TRUONG_CUM: ví dụ CUMAG -> AG, CUMAH -> AH...)
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
                'canLockPoints' => true,
                'canUnlockPoints' => false,
                'canFilterAll' => false
            ];
        }

        // 4. CẤP 3: Trưởng khu KTX (TRUONG_KHU: ví dụ TK001 -> Khu A, TK002 -> Khu B)
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
                'canLockPoints' => true,
                'canUnlockPoints' => true,
                'canFilterAll' => false
            ];
        }

        // Mặc định Cán bộ Cụm
        return [
            'level' => 'CUM',
            'levelNumber' => 2,
            'role' => $role,
            'title' => "Cán bộ KTX ({$cumQuanLy})",
            'badgeClass' => 'badge-secondary',
            'maKhu' => 'A',
            'maCum' => !empty($cumQuanLy) ? $cumQuanLy : 'AG',
            'maToa' => null,
            'canLockPoints' => true,
            'canUnlockPoints' => false,
            'canFilterAll' => false
        ];
    }

    /**
     * Màn hình chính Giám sát An ninh & Điểm Rèn luyện
     */
    public function discipline(): void
    {
        $user = Auth::user();
        $scope = $this->getStaffScope($user);

        // Tham số lọc
        $namHoc = trim($_GET['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_GET['hocKy'] ?? 1);
        $search = trim($_GET['search'] ?? '');
        $huongDi = trim($_GET['huongDi'] ?? '');
        $activeTab = trim($_GET['tab'] ?? 'canh-bao');

        // Xác định phạm vi địa bàn theo phân quyền
        $filterKhu = $scope['maKhu'];
        $filterCum = $scope['maCum'];
        $filterToa = $scope['maToa'];

        if ($scope['level'] === 'ALL') {
            $cumParam = $_GET['cum'] ?? 'all';
            if ($cumParam === 'khu-A') {
                $filterKhu = 'A';
                $filterCum = null;
            } elseif ($cumParam === 'khu-B') {
                $filterKhu = 'B';
                $filterCum = null;
            } elseif ($cumParam !== 'all' && !empty($cumParam)) {
                $filterCum = $cumParam;
            }
        } elseif ($scope['level'] === 'KHU') {
            $cumParam = $_GET['cum'] ?? 'all';
            if ($cumParam !== 'all' && !empty($cumParam)) {
                $filterCum = $cumParam;
            }
        }

        // 1. Lấy danh sách cảnh báo vắng mặt > 30 ngày không phép
        $absenceData = AttendanceDisciplineService::detectLongTermAbsence(30, $filterCum, $filterToa, $filterKhu);

        // 2. Lấy nhật ký quẹt thẻ Barrier mới nhất
        $barrierData = AttendanceDisciplineService::getBarrierLogs(
            $filterCum,
            $filterToa,
            $filterKhu,
            $search,
            $huongDi,
            30,
            0
        );

        // 3. Lấy danh sách tổng hợp điểm rèn luyện
        $summaryList = AttendanceDisciplineService::getStudentDisciplineSummary(
            $namHoc,
            $hocKy,
            $filterCum,
            $filterToa,
            $filterKhu,
            $search
        );

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

        // 6. Thống kê nhanh
        $stats = [
            'totalResident' => count($summaryList),
            'absenceWarnings' => count($absenceData['warnings']),
            'totalBarrierScans' => $barrierData['total'],
            'excellentCount' => 0,
            'goodCount' => 0,
            'fairCount' => 0,
            'averageCount' => 0,
            'weakCount' => 0,
            'lockedCount' => 0
        ];

        foreach ($summaryList as $row) {
            if ($row['XepLoai'] === 'Xuất sắc') $stats['excellentCount']++;
            elseif ($row['XepLoai'] === 'Tốt') $stats['goodCount']++;
            elseif ($row['XepLoai'] === 'Khá') $stats['fairCount']++;
            elseif ($row['XepLoai'] === 'Trung bình') $stats['averageCount']++;
            elseif ($row['XepLoai'] === 'Yếu') $stats['weakCount']++;

            if ((int)$row['DaGuiVeTruong'] === 1) $stats['lockedCount']++;
        }

        $this->view('staff.discipline', [
            'user' => $user,
            'scope' => $scope,
            'namHoc' => $namHoc,
            'hocKy' => $hocKy,
            'search' => $search,
            'huongDi' => $huongDi,
            'activeTab' => $activeTab,
            'selectedCum' => $filterCum ?? 'all',
            'absenceWarnings' => $absenceData['warnings'],
            'barrierLogs' => $barrierData['logs'],
            'barrierTotal' => $barrierData['total'],
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

        $maSinhVien = trim($_POST['maSinhVien'] ?? '');
        $namHoc = trim($_POST['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_POST['hocKy'] ?? 1);
        $loaiGhiNhan = trim($_POST['loaiGhiNhan'] ?? 'Khen thưởng');
        $noiDung = trim($_POST['noiDung'] ?? '');
        $diemThayDoi = (int)($_POST['diemThayDoi'] ?? 0);

        if (empty($maSinhVien) || empty($noiDung) || $diemThayDoi === 0) {
            Session::flash('error', 'Vui lòng điền đầy đủ sinh viên, nội dung chi tiết và số điểm thay đổi (+ hoặc -)!');
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

        if (!$scope['canLockPoints']) {
            Session::flash('error', 'Tài khoản của bạn không có quyền chốt điểm rèn luyện gửi trường.');
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
        header('Content-Type: application/json; charset=utf-8');

        $maSinhVien = trim($_GET['sv'] ?? '');
        $namHoc = trim($_GET['nam'] ?? '2026-2027');
        $hocKy = (int)($_GET['hk'] ?? 1);

        if (empty($maSinhVien)) {
            echo json_encode(['success' => false, 'message' => 'Thiếu mã sinh viên']);
            exit;
        }

        $data = AttendanceDisciplineService::getStudentDisciplineHistory($maSinhVien, $namHoc, $hocKy);
        echo json_encode(['success' => true, 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Xuất báo cáo điểm rèn luyện ra file CSV kèm UTF-8 BOM
     */
    public function export(): void
    {
        $namHoc = trim($_GET['namHoc'] ?? '2026-2027');
        $hocKy = (int)($_GET['hocKy'] ?? 1);
        $maTruong = trim($_GET['maTruong'] ?? '');

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
}
