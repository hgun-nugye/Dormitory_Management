<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\MailService;

/**
 * Controller: Quản trị Tài khoản & Hồ sơ Sinh viên
 * View: views/admin/students.php
 * Quyền hạn: Dành riêng cho Quản trị viên hệ thống (ADMIN)
 * 
 * Nghiệp vụ:
 * 1. Danh sách tài khoản sinh viên kèm thông tin trường, phòng ở, tình trạng nội trú
 * 2. Phân trang thời gian thực, lọc đa tiêu chí (Trường, Giới tính, Trạng thái, Vai trò)
 * 3. Thêm mới / Cập nhật tài khoản sinh viên và hồ sơ cá nhân
 * 4. Đặt lại (Reset) mật khẩu về mặc định (123456)
 * 5. Khóa / Mở khóa tài khoản sinh viên (Toggle status)
 * 6. Xóa tài khoản sinh viên an toàn (ràng buộc hợp đồng)
 * 7. Xuất danh sách sinh viên ra file CSV / Excel chuẩn UTF-8 BOM
 * 8. Tải file CSV mẫu chuẩn nhập liệu
 * 9. Nhập danh sách sinh viên hàng loạt từ file CSV (tự động tạo tài khoản & mã hóa mật khẩu)
 */
class StudentController extends Controller
{
    /**
     * Màn hình chính Quản lý Tài khoản Sinh viên
     */
    public function students(): void
    {
        $user = Auth::user();

        // 1. Danh sách tài khoản sinh viên
        $studentList = [];
        try {
            $studentList = Database::fetchAll("
                SELECT sv.MaSinhVien, sv.MaTaiKhoan, sv.MaTruong, sv.MSSVTaiTruong, sv.HoVaTen,
                       sv.NgaySinh, sv.GioiTinh, sv.SoCCCD, sv.KhoaTuyenSinh, sv.SoDienThoai, sv.Email,
                       sv.TrangThaiNoiTru,
                       tk.TenDangNhap, tk.MaVaiTro, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro,
                       t.TenTruong, t.ThuocDHQGHCM,
                       hd.MaHopDong, hd.MaGiuong, hd.NgayBatDau, hd.NgayKetThuc, hd.TrangThaiHopDong,
                       g.KyHieuGiuong, g.MaPhong,
                       p.SoPhong, tn.TenToaNha, cn.TenCum
                FROM SinhVien sv
                JOIN TaiKhoan tk ON sv.MaTaiKhoan = tk.MaTaiKhoan
                LEFT JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                LEFT JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
                LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
                ORDER BY sv.MaSinhVien DESC
            ");
        } catch (\Throwable $e) {
            $studentList = [];
        }

        // 2. Danh mục Trường Đại học đối tác
        $truongList = [];
        try {
            $truongList = Database::fetchAll("
                SELECT MaTruong, TenTruong, ThuocDHQGHCM, DangHopTac
                FROM TruongDaiHoc
                ORDER BY ThuocDHQGHCM DESC, TenTruong ASC
            ");
        } catch (\Throwable $e) {
            $truongList = [];
        }

        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        $mailConfig = MailService::getConfig();
        $recentMailLogs = MailService::getRecentLogs(5);

        $this->view('admin.students', [
            'user' => $user,
            'studentList' => $studentList,
            'truongList' => $truongList,
            'mailConfig' => $mailConfig,
            'recentMailLogs' => $recentMailLogs,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning
        ]);
    }

    /**
     * Thêm mới hoặc Cập nhật tài khoản sinh viên
     */
    public function saveStudent(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));
        $hoVaTen = trim((string)$request->input('hoVaTen', ''));
        $maTruong = trim((string)$request->input('maTruong', ''));
        $gioiTinh = trim((string)$request->input('gioiTinh', 'Nam'));
        $ngaySinh = trim((string)$request->input('ngaySinh', ''));
        $soCCCD = trim((string)$request->input('soCCCD', ''));
        $khoaTuyenSinh = (int)$request->input('khoaTuyenSinh', date('Y'));
        $soDienThoai = trim((string)$request->input('soDienThoai', ''));
        $email = trim((string)$request->input('email', ''));
        $trangThaiNoiTru = trim((string)$request->input('trangThaiNoiTru', 'Chưa ở'));
        $maVaiTro = trim((string)$request->input('maVaiTro', 'SINH_VIEN'));
        $matKhau = trim((string)$request->input('matKhau', ''));

        if (empty($maSinhVien) || empty($hoVaTen) || empty($maTruong)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ MSSV, Họ và tên và Trường Đại học!');
            $this->redirect('admin/students');
            return;
        }

        if (!in_array($maVaiTro, ['SINH_VIEN', 'TRUONG_PHONG'], true)) {
            $maVaiTro = 'SINH_VIEN';
        }

        Database::beginTransaction();
        try {
            if ($maTaiKhoan > 0) {
                // ==================== CẬP NHẬT (UPDATE) ====================
                $existingAcc = Database::fetch("SELECT * FROM TaiKhoan WHERE MaTaiKhoan = :id FOR UPDATE", ['id' => $maTaiKhoan]);
                if (!$existingAcc) {
                    throw new \Exception('Không tìm thấy tài khoản sinh viên để cập nhật.');
                }

                // Cập nhật thông tin vai trò tài khoản (Không can thiệp mật khẩu tại form hồ sơ)
                Database::execute("
                    UPDATE TaiKhoan 
                    SET MaVaiTro = :vaiTro
                    WHERE MaTaiKhoan = :id
                ", [
                    'vaiTro' => $maVaiTro,
                    'id' => $maTaiKhoan
                ]);

                // Cập nhật thông tin hồ sơ SinhVien
                Database::execute("
                    UPDATE SinhVien
                    SET HoVaTen = :ten, MaTruong = :truong, GioiTinh = :gt,
                        NgaySinh = :ns, SoCCCD = :cccd, KhoaTuyenSinh = :khoa,
                        SoDienThoai = :sdt, Email = :email, TrangThaiNoiTru = :tt
                    WHERE MaTaiKhoan = :id
                ", [
                    'ten' => $hoVaTen,
                    'truong' => $maTruong,
                    'gt' => $gioiTinh,
                    'ns' => !empty($ngaySinh) ? $ngaySinh : null,
                    'cccd' => !empty($soCCCD) ? $soCCCD : null,
                    'khoa' => $khoaTuyenSinh,
                    'sdt' => $soDienThoai,
                    'email' => $email,
                    'tt' => $trangThaiNoiTru,
                    'id' => $maTaiKhoan
                ]);

                Database::commit();
                Session::flash('success', "Đã cập nhật thành công thông tin sinh viên: <b>{$hoVaTen}</b> ({$maSinhVien})!");
            } else {
                // ==================== THÊM MỚI (CREATE) ====================
                // Kiểm tra trùng lặp MSSV hoặc Tên đăng nhập
                $dup = Database::fetch("SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :u", ['u' => $maSinhVien]);
                if ($dup) {
                    throw new \Exception("Mã sinh viên / Tên đăng nhập '{$maSinhVien}' đã tồn tại trong hệ thống!");
                }

                $dupSv = Database::fetch("SELECT MaSinhVien FROM SinhVien WHERE MaSinhVien = :msv", ['msv' => $maSinhVien]);
                if ($dupSv) {
                    throw new \Exception("Mã sinh viên '{$maSinhVien}' đã có hồ sơ sinh viên tồn tại!");
                }

                if (!empty($soCCCD)) {
                    $dupCccd = Database::fetch("SELECT MaSinhVien FROM SinhVien WHERE SoCCCD = :cccd", ['cccd' => $soCCCD]);
                    if ($dupCccd) {
                        throw new \Exception("Số CCCD/CMND '{$soCCCD}' đã được đăng ký bởi sinh viên khác!");
                    }
                }

                $pass = !empty($matKhau) ? $matKhau : '123456';
                $hash = Auth::hashPassword($pass);

                Database::execute("
                    INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, DangHoatDong)
                    VALUES (:user, :hash, :vaiTro, 1)
                ", [
                    'user' => $maSinhVien,
                    'hash' => $hash,
                    'vaiTro' => $maVaiTro
                ]);
                $newAccId = (int)Database::lastInsertId();

                Database::execute("
                    INSERT INTO SinhVien (MaSinhVien, MaTaiKhoan, MaTruong, MSSVTaiTruong, HoVaTen, NgaySinh, GioiTinh, SoCCCD, KhoaTuyenSinh, SoDienThoai, Email, TrangThaiNoiTru)
                    VALUES (:msv, :id, :truong, :mssv, :ten, :ns, :gt, :cccd, :khoa, :sdt, :email, :tt)
                ", [
                    'msv' => $maSinhVien,
                    'id' => $newAccId,
                    'truong' => $maTruong,
                    'mssv' => $maSinhVien,
                    'ten' => $hoVaTen,
                    'ns' => !empty($ngaySinh) ? $ngaySinh : null,
                    'gt' => $gioiTinh,
                    'cccd' => !empty($soCCCD) ? $soCCCD : null,
                    'khoa' => $khoaTuyenSinh,
                    'sdt' => $soDienThoai,
                    'email' => $email,
                    'tt' => $trangThaiNoiTru
                ]);

                Database::commit();
                Session::flash('success', "Đã tạo mới thành công tài khoản sinh viên: <b>{$hoVaTen}</b> (MSSV: {$maSinhVien}) với mật khẩu mặc định!");
            }
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'Lỗi lưu thông tin sinh viên: ' . $e->getMessage());
        }

        $this->redirect('admin/students');
    }

    /**
     * Đặt lại (Reset) hoặc Đổi mật khẩu sinh viên
     * Tạo mật khẩu ngẫu nhiên 8 ký tự và tự động gửi email thông báo cho sinh viên
     */
    public function resetPassword(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $newPass = trim((string)$request->input('matKhauMoi', ''));
        $emailNhan = trim((string)$request->input('emailNhan', ''));
        $sendEmail = $request->input('sendEmail', '1') !== '0';

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản sinh viên không hợp lệ!');
            $this->redirect('admin/students');
            return;
        }

        // Tự động sinh mật khẩu ngẫu nhiên 8 ký tự nếu để trống hoặc chưa chỉ định
        if (empty($newPass)) {
            $newPass = MailService::generatePassword(8);
        }

        if (strlen($newPass) < 6) {
            Session::flash('error', 'Mật khẩu phải có tối thiểu 6 ký tự!');
            $this->redirect('admin/students');
            return;
        }

        try {
            $acc = Database::fetch("
                SELECT tk.*, sv.MaSinhVien, sv.HoVaTen, sv.Email, sv.SoDienThoai, t.TenTruong 
                FROM TaiKhoan tk 
                LEFT JOIN SinhVien sv ON tk.MaTaiKhoan = sv.MaTaiKhoan 
                LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                WHERE tk.MaTaiKhoan = :id
            ", ['id' => $maTaiKhoan]);

            if (!$acc) {
                Session::flash('error', 'Không tìm thấy tài khoản sinh viên!');
                $this->redirect('admin/students');
                return;
            }

            // Nếu sinh viên chưa có email trong DB nhưng Admin bổ sung email nhận, cập nhật vào hồ sơ
            $targetEmail = trim((string)($acc['Email'] ?? ''));
            if (!empty($emailNhan) && filter_var($emailNhan, FILTER_VALIDATE_EMAIL)) {
                if ($targetEmail !== $emailNhan) {
                    Database::execute("UPDATE SinhVien SET Email = :email WHERE MaTaiKhoan = :id", [
                        'email' => $emailNhan,
                        'id' => $maTaiKhoan
                    ]);
                    $targetEmail = $emailNhan;
                }
            }

            // Cập nhật mật khẩu băm mã hóa an toàn vào cơ sở dữ liệu
            $hash = Auth::hashPassword($newPass);
            Database::execute("UPDATE TaiKhoan SET MatKhauMaHoa = :hash WHERE MaTaiKhoan = :id", [
                'hash' => $hash,
                'id' => $maTaiKhoan
            ]);

            $tenHienThi = $acc['HoVaTen'] ?? $acc['TenDangNhap'];
            $mssv = $acc['MaSinhVien'] ?? $acc['TenDangNhap'];
            $truong = $acc['TenTruong'] ?? '';

            // Tiến hành gửi Email chứa mật khẩu mới 8 ký tự cho sinh viên
            $emailSentInfo = '';
            if ($sendEmail && !empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
                $mailRes = MailService::sendStudentPasswordResetEmail($targetEmail, $tenHienThi, $mssv, $newPass, $truong);
                if (!empty($mailRes['success'])) {
                    $emailSentInfo = " và đã gửi email thông báo mật khẩu đến <b>{$targetEmail}</b> thành công!";
                } else {
                    $emailSentInfo = ", tuy nhiên gửi email thất bại: " . ($mailRes['message'] ?? 'Lỗi dịch vụ mail');
                }
            } elseif (empty($targetEmail)) {
                $emailSentInfo = ". <i>(Lưu ý: Sinh viên chưa cập nhật Email trong hồ sơ nên chưa gửi được thư thông báo)</i>";
            }

            Session::flash('success', "Đã tạo mật khẩu 8 ký tự mới cho sinh viên <b>{$tenHienThi}</b>");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi đặt lại mật khẩu: ' . $e->getMessage());
        }

        $this->redirect('admin/students');
    }

    /**
     * Bật / Tắt trạng thái hoạt động của tài khoản sinh viên (Khóa / Mở khóa)
     */
    public function toggleStudentStatus(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $status = $request->input('status');

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản không hợp lệ!');
            $this->redirect('admin/students');
            return;
        }

        try {
            $acc = Database::fetch("SELECT tk.*, sv.HoVaTen FROM TaiKhoan tk LEFT JOIN SinhVien sv ON tk.MaTaiKhoan = sv.MaTaiKhoan WHERE tk.MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
            if (!$acc) {
                Session::flash('error', 'Không tìm thấy tài khoản sinh viên!');
                $this->redirect('admin/students');
                return;
            }

            $newStatus = ($status !== null) ? ((int)$status ? 1 : 0) : ($acc['DangHoatDong'] ? 0 : 1);
            Database::execute("UPDATE TaiKhoan SET DangHoatDong = :st WHERE MaTaiKhoan = :id", [
                'st' => $newStatus,
                'id' => $maTaiKhoan
            ]);

            $stLabel = $newStatus ? 'KÍCH HOẠT' : 'KHÓA';
            Session::flash('success', "Đã {$stLabel} tài khoản sinh viên <b>" . ($acc['HoVaTen'] ?? $acc['TenDangNhap']) . "</b> thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi cập nhật trạng thái: ' . $e->getMessage());
        }

        $this->redirect('admin/students');
    }

    /**
     * Xóa tài khoản sinh viên
     */
    public function deleteStudent(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản không hợp lệ!');
            $this->redirect('admin/students');
            return;
        }

        Database::beginTransaction();
        try {
            $sv = Database::fetch("SELECT * FROM SinhVien WHERE MaTaiKhoan = :id FOR UPDATE", ['id' => $maTaiKhoan]);
            if (!$sv) {
                throw new \Exception('Không tìm thấy hồ sơ sinh viên tương ứng.');
            }

            // Kiểm tra ràng buộc hợp đồng nội trú đang hiệu lực
            $activeHd = Database::fetch("
                SELECT COUNT(*) AS total 
                FROM HopDongNoiTru 
                WHERE MaSinhVien = :msv AND TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
            ", ['msv' => $sv['MaSinhVien']]);

            if (($activeHd['total'] ?? 0) > 0) {
                throw new \Exception("Sinh viên {$sv['HoVaTen']} ({$sv['MaSinhVien']}) hiện đang có hợp đồng nội trú còn hiệu lực! Vui lòng thanh lý hoặc trả phòng trước khi xóa tài khoản.");
            }

            // Kiểm tra công nợ / hóa đơn chưa thanh toán
            $unpaidBills = Database::fetch("
                SELECT COUNT(*) AS total 
                FROM HoaDon 
                WHERE MaSinhVien = :msv AND TrangThai = 'Chưa thanh toán'
            ", ['msv' => $sv['MaSinhVien']]);

            if (($unpaidBills['total'] ?? 0) > 0) {
                throw new \Exception("Sinh viên {$sv['HoVaTen']} còn {$unpaidBills['total']} hóa đơn chưa thanh toán! Không thể xóa tài khoản.");
            }

            // Xóa hồ sơ sinh viên và tài khoản
            Database::execute("DELETE FROM SinhVien WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
            Database::execute("DELETE FROM TaiKhoan WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);

            Database::commit();
            Session::flash('success', "Đã xóa hoàn toàn tài khoản và hồ sơ sinh viên <b>{$sv['HoVaTen']}</b> ({$sv['MaSinhVien']}) khỏi hệ thống!");
        } catch (\Throwable $e) {
            Database::rollBack();
            Session::flash('error', 'Lỗi khi xóa tài khoản: ' . $e->getMessage());
        }

        $this->redirect('admin/students');
    }

    /**
     * Xuất danh sách sinh viên ra file CSV / Excel
     */
    public function exportStudents(): void
    {
        $request = new Request();
        $truong = trim((string)$request->input('maTruong', ''));
        $gioiTinh = trim((string)$request->input('gioiTinh', ''));
        $trangThaiTaiKhoan = $request->input('trangThaiTaiKhoan');
        $trangThaiNoiTru = trim((string)$request->input('trangThaiNoiTru', ''));
        $maVaiTro = trim((string)$request->input('maVaiTro', ''));
        $keyword = trim((string)$request->input('keyword', ''));
        $idsParam = $request->input('ids');

        try {
            $whereConditions = [];
            $params = [];

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
                    $whereConditions[] = "sv.MaSinhVien IN (" . implode(',', $placeholders) . ")";
                }
            } else {
                if (!empty($truong)) {
                    $whereConditions[] = "sv.MaTruong = :truong";
                    $params['truong'] = $truong;
                }
                if (!empty($gioiTinh)) {
                    $whereConditions[] = "sv.GioiTinh = :gt";
                    $params['gt'] = $gioiTinh;
                }
                if ($trangThaiTaiKhoan !== null && $trangThaiTaiKhoan !== '') {
                    $whereConditions[] = "tk.DangHoatDong = :stAcc";
                    $params['stAcc'] = (int)$trangThaiTaiKhoan;
                }
                if (!empty($trangThaiNoiTru)) {
                    $whereConditions[] = "sv.TrangThaiNoiTru = :stNt";
                    $params['stNt'] = $trangThaiNoiTru;
                }
                if (!empty($maVaiTro)) {
                    $whereConditions[] = "tk.MaVaiTro = :vt";
                    $params['vt'] = $maVaiTro;
                }
                if (!empty($keyword)) {
                    $whereConditions[] = "(sv.MaSinhVien LIKE :kw1 OR sv.HoVaTen LIKE :kw2 OR sv.SoDienThoai LIKE :kw3 OR sv.Email LIKE :kw4 OR sv.SoCCCD LIKE :kw5)";
                    $kw = "%{$keyword}%";
                    $params['kw1'] = $kw;
                    $params['kw2'] = $kw;
                    $params['kw3'] = $kw;
                    $params['kw4'] = $kw;
                    $params['kw5'] = $kw;
                }
            }

            $whereSql = !empty($whereConditions) ? ('WHERE ' . implode(' AND ', $whereConditions)) : '';

            $sql = "
                SELECT sv.*, tk.TenDangNhap, tk.MaVaiTro, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro,
                       t.TenTruong,
                       g.MaPhong, tn.TenToaNha, cn.TenCum
                FROM SinhVien sv
                JOIN TaiKhoan tk ON sv.MaTaiKhoan = tk.MaTaiKhoan
                LEFT JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                LEFT JOIN HopDongNoiTru hd ON sv.MaSinhVien = hd.MaSinhVien AND hd.TrangThaiHopDong IN ('Đang hiệu lực', 'Chờ hiệu lực')
                LEFT JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
                LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN CumNha cn ON tn.MaCum = cn.MaCum
                {$whereSql}
                ORDER BY sv.MaSinhVien DESC
            ";
            $students = Database::fetchAll($sql, $params);

            $filename = 'DS_TaiKhoan_SinhVien_' . date('Ymd_His') . '.csv';

            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, [
                'Mã SV / Tên đăng nhập',
                'Họ và tên',
                'Trường Đại học',
                'Mã trường',
                'Giới tính',
                'Ngày sinh',
                'Số CCCD / CMND',
                'Khóa tuyển sinh',
                'Số điện thoại',
                'Email',
                'Phòng ở hiện tại',
                'Tòa nhà',
                'Cụm nhà',
                'Trạng thái nội trú',
                'Vai trò',
                'Trạng thái tài khoản',
                'Ngày tạo'
            ]);

            foreach ($students as $s) {
                fputcsv($output, [
                    $s['MaSinhVien'],
                    $s['HoVaTen'],
                    $s['TenTruong'] ?? $s['MaTruong'],
                    $s['MaTruong'],
                    $s['GioiTinh'] ?? 'Nam',
                    $s['NgaySinh'] ?? '',
                    $s['SoCCCD'] ?? '',
                    $s['KhoaTuyenSinh'] ?? '',
                    $s['SoDienThoai'] ?? '',
                    $s['Email'] ?? '',
                    $s['MaPhong'] ?? 'Chưa xếp phòng',
                    $s['TenToaNha'] ?? '',
                    $s['TenCum'] ?? '',
                    $s['TrangThaiNoiTru'] ?? 'Chưa ở',
                    ($s['MaVaiTro'] === 'TRUONG_PHONG') ? 'Trưởng phòng' : 'Sinh viên',
                    $s['DangHoatDong'] ? 'Đang hoạt động' : 'Đang bị khóa',
                    $s['NgayTao'] ?? ''
                ]);
            }

            fclose($output);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xuất dữ liệu sinh viên: ' . $e->getMessage());
            $this->redirect('admin/students');
        }
    }

    /**
     * Tải file CSV mẫu chuẩn nhập danh sách sinh viên
     */
    public function downloadStudentTemplate(): void
    {
        $filename = 'Mau_Nhap_Sinh_Vien_KTX.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($output, [
            'MSSV',
            'HoVaTen',
            'MaTruong',
            'GioiTinh',
            'NgaySinh',
            'SoCCCD',
            'KhoaTuyenSinh',
            'SoDienThoai',
            'Email',
            'TrangThaiNoiTru',
            'MatKhau'
        ]);

        // Dữ liệu mẫu minh họa
        fputcsv($output, ['2110001', 'Trần Văn An', 'BK', 'Nam', '2003-05-15', '079203001234', '2021', '0903123456', 'an.tv@hcmut.edu.vn', 'Chưa ở', '123456']);
        fputcsv($output, ['2210002', 'Nguyễn Thị Bình', 'KHTN', 'Nữ', '2004-09-20', '079304005678', '2022', '0912345678', 'binh.nt@hcmus.edu.vn', 'Chưa ở', '123456']);
        fputcsv($output, ['2310003', 'Lê Hoàng Cường', 'UIT', 'Nam', '2005-01-10', '079205009876', '2023', '0987654321', 'cuong.lh@uit.edu.vn', 'Chưa ở', '123456']);

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách sinh viên từ file CSV
     */
    public function importStudents(): void
    {
        if (empty($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn file CSV hợp lệ để tải lên!');
            $this->redirect('admin/students');
            return;
        }

        $filePath = $_FILES['csv_file']['tmp_name'];
        $request = new Request();
        $overwrite = (bool)$request->input('overwriteExisting', false);

        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception('Không thể mở file CSV tải lên.');
            }

            // Bỏ qua BOM nếu có
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $firstLine = fgets($handle);
            if (!$firstLine) {
                fclose($handle);
                Session::flash('error', 'File CSV rỗng!');
                $this->redirect('admin/students');
                return;
            }

            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            rewind($handle);
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $headers = fgetcsv($handle, 0, $delimiter);
            if (!$headers) {
                fclose($handle);
                Session::flash('error', 'Không thể đọc dòng tiêu đề cột của file CSV!');
                $this->redirect('admin/students');
                return;
            }

            $headerMap = [];
            foreach ($headers as $idx => $h) {
                $norm = strtolower(trim(str_replace([' ', '_', '-'], '', $h)));
                $headerMap[$norm] = $idx;
            }

            $colMsv = $headerMap['mssv'] ?? $headerMap['masinhvien'] ?? $headerMap['masv'] ?? null;
            $colTen = $headerMap['hovaten'] ?? $headerMap['hoten'] ?? $headerMap['ten'] ?? null;
            $colTruong = $headerMap['matruong'] ?? $headerMap['truong'] ?? null;
            $colGt = $headerMap['gioitinh'] ?? null;
            $colNs = $headerMap['ngaysinh'] ?? null;
            $colCccd = $headerMap['socccd'] ?? $headerMap['cccd'] ?? $headerMap['cmnd'] ?? null;
            $colKhoa = $headerMap['khoatuyensinh'] ?? $headerMap['khoa'] ?? null;
            $colSdt = $headerMap['sodienthoai'] ?? $headerMap['sdt'] ?? null;
            $colEmail = $headerMap['email'] ?? null;
            $colTt = $headerMap['trangthainoitru'] ?? $headerMap['trangthai'] ?? null;
            $colPass = $headerMap['matkhau'] ?? $headerMap['password'] ?? null;

            if ($colMsv === null || $colTen === null || $colTruong === null) {
                fclose($handle);
                Session::flash('error', 'File CSV thiếu cột bắt buộc: Cần có tối thiểu các cột "MSSV", "HoVaTen" và "MaTruong"!');
                $this->redirect('admin/students');
                return;
            }

            // Danh sách mã trường hợp lệ
            $validTruongRows = Database::fetchAll("SELECT MaTruong FROM TruongDaiHoc");
            $validTruongCodes = array_column($validTruongRows, 'MaTruong');
            $defaultTruong = !empty($validTruongCodes) ? $validTruongCodes[0] : 'BK';

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorRows = [];
            $lineNum = 1;

            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $lineNum++;
                if (empty(array_filter($row))) {
                    continue;
                }

                $rawMsv = trim($row[$colMsv] ?? '');
                $rawTen = trim($row[$colTen] ?? '');
                $rawTruong = strtoupper(trim($row[$colTruong] ?? ''));
                $rawGt = ($colGt !== null && !empty(trim($row[$colGt] ?? ''))) ? trim($row[$colGt]) : 'Nam';
                $rawNs = ($colNs !== null && !empty(trim($row[$colNs] ?? ''))) ? trim($row[$colNs]) : null;
                $rawCccd = ($colCccd !== null && !empty(trim($row[$colCccd] ?? ''))) ? trim($row[$colCccd]) : null;
                $rawKhoa = ($colKhoa !== null && !empty(trim($row[$colKhoa] ?? ''))) ? (int)trim($row[$colKhoa]) : (int)date('Y');
                $rawSdt = ($colSdt !== null && !empty(trim($row[$colSdt] ?? ''))) ? trim($row[$colSdt]) : '';
                $rawEmail = ($colEmail !== null && !empty(trim($row[$colEmail] ?? ''))) ? trim($row[$colEmail]) : '';
                $rawTt = ($colTt !== null && !empty(trim($row[$colTt] ?? ''))) ? trim($row[$colTt]) : 'Chưa ở';
                $rawPass = ($colPass !== null && !empty(trim($row[$colPass] ?? ''))) ? trim($row[$colPass]) : '123456';

                if (empty($rawMsv) || empty($rawTen)) {
                    $errorRows[] = "Dòng {$lineNum}: Thiếu MSSV hoặc Họ tên sinh viên.";
                    $skippedCount++;
                    continue;
                }

                if (!in_array($rawTruong, $validTruongCodes, true)) {
                    $rawTruong = $defaultTruong;
                }

                // Kiểm tra xem tài khoản đã tồn tại chưa
                $existingAcc = Database::fetch("SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :u", ['u' => $rawMsv]);

                if ($existingAcc) {
                    if ($overwrite) {
                        Database::execute("
                            UPDATE SinhVien
                            SET HoVaTen = :ten, MaTruong = :truong, GioiTinh = :gt,
                                NgaySinh = :ns, SoCCCD = :cccd, KhoaTuyenSinh = :khoa,
                                SoDienThoai = :sdt, Email = :email, TrangThaiNoiTru = :tt
                            WHERE MaTaiKhoan = :id
                        ", [
                            'ten' => $rawTen,
                            'truong' => $rawTruong,
                            'gt' => $rawGt,
                            'ns' => $rawNs,
                            'cccd' => $rawCccd,
                            'khoa' => $rawKhoa,
                            'sdt' => $rawSdt,
                            'email' => $rawEmail,
                            'tt' => $rawTt,
                            'id' => $existingAcc['MaTaiKhoan']
                        ]);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    $hash = Auth::hashPassword($rawPass);
                    Database::execute("
                        INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, DangHoatDong)
                        VALUES (:u, :h, 'SINH_VIEN', 1)
                    ", [
                        'u' => $rawMsv,
                        'h' => $hash
                    ]);
                    $newAccId = (int)Database::lastInsertId();

                    Database::execute("
                        INSERT INTO SinhVien (MaSinhVien, MaTaiKhoan, MaTruong, MSSVTaiTruong, HoVaTen, NgaySinh, GioiTinh, SoCCCD, KhoaTuyenSinh, SoDienThoai, Email, TrangThaiNoiTru)
                        VALUES (:msv, :id, :truong, :mssv, :ten, :ns, :gt, :cccd, :khoa, :sdt, :email, :tt)
                    ", [
                        'msv' => $rawMsv,
                        'id' => $newAccId,
                        'truong' => $rawTruong,
                        'mssv' => $rawMsv,
                        'ten' => $rawTen,
                        'ns' => $rawNs,
                        'gt' => $rawGt,
                        'cccd' => $rawCccd,
                        'khoa' => $rawKhoa,
                        'sdt' => $rawSdt,
                        'email' => $rawEmail,
                        'tt' => $rawTt
                    ]);
                    $createdCount++;
                }
            }

            fclose($handle);

            $msg = "Nhập danh sách sinh viên hoàn tất: Đã tạo mới <b>{$createdCount}</b> tài khoản, Cập nhật <b>{$updatedCount}</b> sinh viên, Bỏ qua <b>{$skippedCount}</b> sinh viên.";
            if (!empty($errorRows)) {
                $errSnippet = implode('<br>', array_slice($errorRows, 0, 5));
                if (count($errorRows) > 5) {
                    $errSnippet .= '<br>...và ' . (count($errorRows) - 5) . ' cảnh báo khác.';
                }
                Session::flash('warning', $msg . '<br><div style="margin-top:6px;"><b>Chi tiết cảnh báo:</b><br>' . $errSnippet . '</div>');
            } else {
                Session::flash('success', $msg);
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý file CSV sinh viên: ' . $e->getMessage());
        }

        $this->redirect('admin/students');
    }
}
