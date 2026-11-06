<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Controller: Quản lý Danh mục Trường Đại học liên kết
 * View: views/admin/universities.php
 */
class UniversityController extends Controller
{
    /**
     * Danh sách trường Đại học liên kết & chỉ tiêu
     */
    public function universities(): void
    {
        $user = Auth::user();

        $universityList = [];
        try {
            $universityList = Database::fetchAll("
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
            ");
        } catch (\Throwable $e) {
            $universityList = [];
        }

        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        $this->view('admin.universities', [
            'user' => $user,
            'universityList' => $universityList,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning
        ]);
    }

    /**
     * Thêm mới hoặc Cập nhật trường Đại học
     */
    public function saveUniversity(): void
    {
        $request = new Request();
        $isEdit = (int)$request->input('isEdit', 0);
        $maTruong = strtoupper(trim((string)$request->input('maTruong', '')));
        $tenTruong = trim((string)$request->input('tenTruong', ''));
        $soNamDaoTao = (float)$request->input('soNamDaoTaoChuan', 4.0);
        $thuocDHQGHCM = (int)$request->input('thuocDHQGHCM', 1);
        $dangHopTac = (int)$request->input('dangHopTac', 1);

        if (empty($maTruong) || empty($tenTruong)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ Mã trường và Tên trường Đại học!');
            $this->redirect('admin/universities');
            return;
        }

        if ($soNamDaoTao <= 0 || $soNamDaoTao > 10) {
            Session::flash('error', 'Thời gian đào tạo chuẩn không hợp lệ (từ 1.0 đến 10.0 năm)!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            if ($isEdit === 1) {
                // ==================== CẬP NHẬT (UPDATE) ====================
                $existing = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                if (!$existing) {
                    Session::flash('error', "Không tìm thấy trường Đại học '{$maTruong}' để cập nhật!");
                    $this->redirect('admin/universities');
                    return;
                }

                Database::execute("
                    UPDATE TruongDaiHoc 
                    SET TenTruong = :ten, SoNamDaoTaoChuan = :nam, ThuocDHQGHCM = :thuoc, DangHopTac = :hoptac
                    WHERE MaTruong = :ma
                ", [
                    'ten' => $tenTruong,
                    'nam' => $soNamDaoTao,
                    'thuoc' => $thuocDHQGHCM,
                    'hoptac' => $dangHopTac,
                    'ma' => $maTruong
                ]);

                Session::flash('success', "Đã cập nhật thông tin trường Đại học '{$tenTruong}' ({$maTruong}) thành công!");
            } else {
                // ==================== THÊM MỚI (CREATE) ====================
                $dup = Database::fetch("SELECT MaTruong FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                if ($dup) {
                    Session::flash('error', "Mã trường '{$maTruong}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác!");
                    $this->redirect('admin/universities');
                    return;
                }

                Database::execute("
                    INSERT INTO TruongDaiHoc (MaTruong, TenTruong, SoNamDaoTaoChuan, ThuocDHQGHCM, DangHopTac)
                    VALUES (:ma, :ten, :nam, :thuoc, :hoptac)
                ", [
                    'ma' => $maTruong,
                    'ten' => $tenTruong,
                    'nam' => $soNamDaoTao,
                    'thuoc' => $thuocDHQGHCM,
                    'hoptac' => $dangHopTac
                ]);

                Session::flash('success', "Đã thêm mới trường Đại học '{$tenTruong}' ({$maTruong}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    /**
     * Bật / Tắt trạng thái tiếp nhận sinh viên của trường
     */
    public function toggleUniversityStatus(): void
    {
        $request = new Request();
        $maTruong = trim((string)$request->input('maTruong', ''));

        if (empty($maTruong)) {
            Session::flash('error', 'Mã trường đại học không hợp lệ!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $uni = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
            if (!$uni) {
                Session::flash('error', 'Không tìm thấy trường đại học cần thao tác!');
                $this->redirect('admin/universities');
                return;
            }

            $newStatus = ((int)$uni['DangHopTac'] === 1) ? 0 : 1;
            Database::execute("UPDATE TruongDaiHoc SET DangHopTac = :st WHERE MaTruong = :ma", [
                'st' => $newStatus,
                'ma' => $maTruong
            ]);

            $stText = ($newStatus === 1) ? 'TIẾP TỤC HỢP TÁC' : 'TẠM DỪNG TIẾP NHẬN';
            Session::flash('success', "Đã chuyển trạng thái trường '{$uni['TenTruong']}' sang {$stText} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi cập nhật trạng thái: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    /**
     * Xóa trường Đại học (áp dụng quy tắc bảo toàn lịch sử dữ liệu sinh viên)
     */
    public function deleteUniversity(): void
    {
        $request = new Request();
        $maTruong = trim((string)$request->input('maTruong', ''));

        if (empty($maTruong)) {
            Session::flash('error', 'Mã trường đại học không hợp lệ!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $uni = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
            if (!$uni) {
                Session::flash('error', 'Không tìm thấy trường đại học cần xóa!');
                $this->redirect('admin/universities');
                return;
            }

            // Kiểm tra ràng buộc bảo toàn dữ liệu sinh viên
            $svCheck = Database::fetch("SELECT COUNT(*) AS total FROM SinhVien WHERE MaTruong = :ma", ['ma' => $maTruong]);
            $svCount = (int)($svCheck['total'] ?? 0);

            if ($svCount > 0) {
                // Bảo toàn dữ liệu: Không xóa vật lý khi đã có sinh viên liên kết, tự động chuyển sang tạm dừng tiếp nhận
                Database::execute("UPDATE TruongDaiHoc SET DangHopTac = 0 WHERE MaTruong = :ma", [
                    'ma' => $maTruong
                ]);
                Session::flash('warning', "Trường '{$uni['TenTruong']}' ({$maTruong}) đang có {$svCount} sinh viên trong hệ thống KTX. Để bảo toàn lịch sử dữ liệu nội trú, hệ thống đã tự động chuyển sang trạng thái TẠM DỪNG TIẾP NHẬN thay vì xóa vật lý!");
            } else {
                Database::execute("DELETE FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                Session::flash('success', "Đã xóa hoàn toàn trường Đại học '{$uni['TenTruong']}' ({$maTruong}) khỏi hệ thống!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa trường đại học: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    /**
     * Xuất danh sách trường đại học ra file CSV / Excel (có UTF-8 BOM)
     */
    public function exportUniversities(): void
    {
        $request = new Request();
        $codesParam = $request->input('ids');
        $keyword = trim((string)$request->input('keyword', ''));
        $category = $request->input('category');
        $status = $request->input('status');
        $duration = $request->input('duration');

        try {
            $whereConditions = [];
            $params = [];
            $isFiltered = false;

            if (!empty($codesParam)) {
                $rawCodes = is_array($codesParam) ? $codesParam : explode(',', (string)$codesParam);
                $cleanCodes = array_filter(array_map('trim', $rawCodes), fn($c) => !empty($c));
                if (!empty($cleanCodes)) {
                    $placeholders = [];
                    foreach ($cleanCodes as $idx => $code) {
                        $pKey = 'code_' . $idx;
                        $placeholders[] = ':' . $pKey;
                        $params[$pKey] = $code;
                    }
                    $whereConditions[] = "t.MaTruong IN (" . implode(',', $placeholders) . ")";
                    $isFiltered = true;
                }
            } else {
                if ($category !== null && $category !== '') {
                    $whereConditions[] = "t.ThuocDHQGHCM = :cat";
                    $params['cat'] = (int)$category;
                    $isFiltered = true;
                }
                if ($status !== null && $status !== '') {
                    $whereConditions[] = "t.DangHopTac = :st";
                    $params['st'] = (int)$status;
                    $isFiltered = true;
                }
                if ($duration !== null && $duration !== '') {
                    $dur = (float)$duration;
                    if ($dur >= 5.0) {
                        $whereConditions[] = "t.SoNamDaoTaoChuan >= 5.0";
                    } else {
                        $whereConditions[] = "t.SoNamDaoTaoChuan = :dur";
                        $params['dur'] = $dur;
                    }
                    $isFiltered = true;
                }
                if (!empty($keyword)) {
                    $whereConditions[] = "(t.MaTruong LIKE :kw1 OR t.TenTruong LIKE :kw2)";
                    $kwVal = "%{$keyword}%";
                    $params['kw1'] = $kwVal;
                    $params['kw2'] = $kwVal;
                    $isFiltered = true;
                }
            }

            $whereSql = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

            $sql = "
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                {$whereSql}
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
            ";

            $uniList = Database::fetchAll($sql, $params);

            $filenamePrefix = $isFiltered ? 'DS_TruongDaiHoc_KTX_Filtered_' : 'DS_TruongDaiHoc_KTX_';
            $filename = $filenamePrefix . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'STT',
                'Mã trường',
                'Tên trường đại học',
                'Thời gian đào tạo chuẩn (Năm)',
                'Phân loại đơn vị',
                'Số sinh viên KTX',
                'Trạng thái hợp tác'
            ]);

            $stt = 1;
            foreach ($uniList as $u) {
                $isDHQG = ((int)($u['ThuocDHQGHCM'] ?? 0)) === 1;
                $isPaused = ((int)($u['DangHopTac'] ?? 1)) === 0;

                fputcsv($output, [
                    $stt++,
                    $u['MaTruong'],
                    $u['TenTruong'],
                    $u['SoNamDaoTaoChuan'],
                    $isDHQG ? 'Thành viên ĐHQG-HCM' : 'Ngoài ĐHQG-HCM (Liên kết)',
                    (int)($u['TongSinhVien'] ?? 0),
                    $isPaused ? 'Tạm dừng tiếp nhận' : 'Đang hợp tác tiếp nhận'
                ]);
            }

            fclose($output);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xuất danh sách trường: ' . $e->getMessage());
            $this->redirect('admin/universities');
        }
    }

    /**
     * Tải file mẫu CSV để nhập danh sách trường đại học
     */
    public function downloadUniversityTemplate(): void
    {
        $filename = 'Mau_Import_TruongDaiHoc_KTX.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'MaTruong',
            'TenTruong',
            'SoNamDaoTaoChuan',
            'ThuocDHQGHCM',
            'DangHopTac'
        ]);

        fputcsv($output, [
            'BK',
            'Trường Đại học Bách khoa - ĐHQG-HCM',
            '4.5',
            '1',
            '1'
        ]);
        fputcsv($output, [
            'KHTN',
            'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM',
            '4.0',
            '1',
            '1'
        ]);
        fputcsv($output, [
            'HUTECH',
            'Trường Đại học Công nghệ TP.HCM',
            '4.0',
            '0',
            '1'
        ]);

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách trường đại học từ file CSV
     */
    public function importUniversities(): void
    {
        $request = new Request();
        $rawOverwrite = $request->input('overwriteExisting', null);
        if ($rawOverwrite === null) {
            $rawOverwrite = $_POST['overwriteExisting'] ?? '0';
        }
        $overwrite = ($rawOverwrite === '1' || $rawOverwrite === 1 || $rawOverwrite === true || $rawOverwrite === 'on');

        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn tệp CSV hợp lệ để nhập danh sách trường!');
            $this->redirect('admin/universities');
            return;
        }

        $file = $_FILES['importFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::flash('error', 'Hệ thống chỉ hỗ trợ tệp định dạng CSV (.csv) hoặc văn bản (.txt)!');
            $this->redirect('admin/universities');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể mở tệp tải lên để đọc dữ liệu!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                Session::flash('error', 'Tệp tải lên rỗng!');
                $this->redirect('admin/universities');
                return;
            }

            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            $headers = str_getcsv(trim($firstLine), $delimiter);

            $colMap = [];
            foreach ($headers as $idx => $h) {
                $clean = strtolower(trim($h));
                $clean = str_replace([' ', '_', '-', '.'], '', $clean);
                if (in_array($clean, ['matruong', 'code', 'ma', 'matruongdaihoc', 'id'])) {
                    $colMap['code'] = $idx;
                } elseif (in_array($clean, ['tentruong', 'name', 'ten', 'tentruongdaihoc', 'truong'])) {
                    $colMap['name'] = $idx;
                } elseif (in_array($clean, ['sonamdaotaochuan', 'sonam', 'duration', 'daotao', 'sonamdaotao'])) {
                    $colMap['duration'] = $idx;
                } elseif (in_array($clean, ['thuocdhqghcm', 'thuocdhqg', 'dhqg', 'isdhqg', 'phanloai', 'thanhvien'])) {
                    $colMap['dhqg'] = $idx;
                } elseif (in_array($clean, ['danghoptac', 'hoptac', 'trangthai', 'status', 'tiepnhan'])) {
                    $colMap['status'] = $idx;
                }
            }

            if (!isset($colMap['code']) || !isset($colMap['name'])) {
                fclose($handle);
                Session::flash('error', 'Tệp CSV không đúng định dạng. Cần có tối thiểu 2 cột: MaTruong, TenTruong!');
                $this->redirect('admin/universities');
                return;
            }

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorRows = [];
            $rowNum = 1;

            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                $rowNum++;
                if (empty($data) || (count($data) === 1 && trim($data[0]) === '')) {
                    continue;
                }

                $code = isset($colMap['code'], $data[$colMap['code']]) ? strtoupper(trim($data[$colMap['code']])) : '';
                $name = isset($colMap['name'], $data[$colMap['name']]) ? trim($data[$colMap['name']]) : '';
                $rawDuration = isset($colMap['duration'], $data[$colMap['duration']]) ? trim($data[$colMap['duration']]) : '4.0';
                $rawDhqg = isset($colMap['dhqg'], $data[$colMap['dhqg']]) ? trim($data[$colMap['dhqg']]) : '';
                $rawStatus = isset($colMap['status'], $data[$colMap['status']]) ? trim($data[$colMap['status']]) : '1';

                if (empty($code) || empty($name)) {
                    $errorRows[] = "Dòng {$rowNum}: Thiếu Mã trường hoặc Tên trường Đại học.";
                    continue;
                }

                $duration = floatval(str_replace(',', '.', $rawDuration));
                if ($duration <= 0 || $duration > 10.0) {
                    $duration = 4.0;
                }

                $isDhqg = 0;
                if ($rawDhqg === '1' || strtolower($rawDhqg) === 'true' || stripos($rawDhqg, 'đhqg') !== false || stripos($rawDhqg, 'thành viên') !== false || stripos($name, 'đhqg') !== false) {
                    $isDhqg = 1;
                }

                $status = 1;
                if ($rawStatus === '0' || strtolower($rawStatus) === 'false' || stripos($rawStatus, 'tạm dừng') !== false || stripos($rawStatus, 'dừng') !== false) {
                    $status = 0;
                }

                $existing = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $code]);

                if ($existing) {
                    if ($overwrite) {
                        Database::execute("
                            UPDATE TruongDaiHoc 
                            SET TenTruong = :ten, SoNamDaoTaoChuan = :dur, ThuocDHQGHCM = :dhqg, DangHopTac = :st
                            WHERE MaTruong = :ma
                        ", [
                            'ten' => $name,
                            'dur' => $duration,
                            'dhqg' => $isDhqg,
                            'st' => $status,
                            'ma' => $code
                        ]);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    Database::execute("
                        INSERT INTO TruongDaiHoc (MaTruong, TenTruong, SoNamDaoTaoChuan, ThuocDHQGHCM, DangHopTac)
                        VALUES (:ma, :ten, :dur, :dhqg, :st)
                    ", [
                        'ma' => $code,
                        'ten' => $name,
                        'dur' => $duration,
                        'dhqg' => $isDhqg,
                        'st' => $status
                    ]);
                    $createdCount++;
                }
            }

            fclose($handle);

            $msg = "Nhập dữ liệu hoàn tất: Đã thêm mới <b>{$createdCount}</b> trường, Cập nhật <b>{$updatedCount}</b> trường, Bỏ qua <b>{$skippedCount}</b> trường đã có.";
            if (!empty($errorRows)) {
                $errorDetails = implode('<br>', array_slice($errorRows, 0, 5));
                if (count($errorRows) > 5) {
                    $errorDetails .= '<br>...và ' . (count($errorRows) - 5) . ' dòng lỗi khác.';
                }
                Session::flash('warning', $msg . '<br><div style="margin-top:6px;"><b>Chi tiết cảnh báo dòng dữ liệu:</b><br>' . $errorDetails . '</div>');
            } else {
                Session::flash('success', $msg);
            }
        } catch (\Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            Session::flash('error', 'Lỗi trong quá trình đọc và nhập file CSV: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }
}

