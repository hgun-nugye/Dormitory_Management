<?php

namespace App\Controllers\Student;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

class ProfileController extends Controller {
    public function index(): void {
        $user = Auth::user();
        $mssv = $user['profile']['MaSinhVien'] ?? $user['TenDangNhap'] ?? '';
        $sinhVien = null;
        $hopDong = null;
        $hoSoDangKy = null;

        try {
            $sinhVien = Database::fetch(
                "SELECT sv.*, t.TenTruong
                 FROM SinhVien sv
                 JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                 WHERE sv.MaSinhVien = :mssv",
                ['mssv' => $mssv]
            );
            if ($sinhVien) {
                $hopDong = Database::fetch(
                    "SELECT hd.*, g.KyHieuGiuong, g.MaPhong, lp.TenLoaiPhong, tn.TenToaNha, cn.TenCum, cn.SoDienThoaiCum
                     FROM HopDongNoiTru hd
                     JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
                     JOIN Phong p ON g.MaPhong = p.MaPhong
                     JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                     JOIN CumNha cn ON tn.MaCum = cn.MaCum
                     JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                     WHERE hd.MaSinhVien = :mssv AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
                    ['mssv' => $mssv]
                );
                $hoSoDangKy = Database::fetch(
                    "SELECT hs.*, g.KyHieuGiuong, g.MaPhong, p.Tang, tn.TenToaNha, cn.TenCum
                     FROM HoSoDangKy hs
                     JOIN Giuong g ON g.MaGiuong = hs.MaGiuong
                     JOIN Phong p ON p.MaPhong = g.MaPhong
                     JOIN ToaNha tn ON tn.MaToaNha = p.MaToaNha
                     JOIN CumNha cn ON cn.MaCum = tn.MaCum
                     WHERE hs.MaSinhVien = :mssv
                     AND hs.TrangThaiHoSo IN ('Chờ duyệt', 'Chờ thanh toán')
                     ORDER BY hs.ThoiGianNop DESC, hs.MaHoSo DESC
                     LIMIT 1",
                    ['mssv' => $mssv]
                );
            }
        } catch (\Throwable $e) {
            $sinhVien = null;
        }

        $this->view('student.profile', [
            'sinhVien' => $sinhVien ?? [],
            'hopDong' => $hopDong,
            'hoSoDangKy' => $hoSoDangKy,
            'title' => 'Thông tin sinh viên',
            'error' => Session::flash('error'),
            'success' => Session::flash('success')
        ]);
    }

    public function update(): void {
        $request = new Request();
        $token = (string) $request->input('csrf_token', '');
        $sessionToken = (string) Session::get('csrf_token', '');
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
            $this->redirect('student/profile');
            return;
        }

        $user = Auth::user();
        $mssv = $user['profile']['MaSinhVien'] ?? $user['TenDangNhap'] ?? '';
        $phone = trim((string) $request->input('SoDienThoai', ''));
        $email = trim((string) $request->input('Email', ''));
        if (!preg_match('/^[0-9+\s.-]{9,15}$/', $phone)) {
            Session::flash('error', 'Số điện thoại không hợp lệ.');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email không hợp lệ.');
        } elseif ($mssv !== '') {
            Database::execute(
                'UPDATE SinhVien SET SoDienThoai = :phone, Email = :email WHERE MaSinhVien = :mssv',
                ['phone' => $phone, 'email' => $email, 'mssv' => $mssv]
            );
            Session::flash('success', 'Đã cập nhật thông tin liên hệ.');
        } else {
            Session::flash('error', 'Không xác định được hồ sơ sinh viên.');
        }
        $this->redirect('student/profile');
    }

    public function changePassword(): void {
        $request = new Request();
        $token = (string) $request->input('csrf_token', '');
        $sessionToken = (string) Session::get('csrf_token', '');
        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
            $this->redirect('student/profile');
            return;
        }

        $user = Auth::user();
        if (!$user) {
            $this->redirect('login');
            return;
        }

        $oldPassword = (string)$request->input('old_password', '');
        $newPassword = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'Mật khẩu xác nhận không khớp với mật khẩu mới.');
            $this->redirect('student/profile');
            return;
        }

        $res = Auth::changePassword((int)$user['MaTaiKhoan'], $oldPassword, $newPassword);
        if ($res['success']) {
            Session::flash('success', 'Đổi mật khẩu thành công! Hãy ghi nhớ mật khẩu mới cho các lần đăng nhập tiếp theo.');
        } else {
            Session::flash('error', $res['message'] ?? 'Đổi mật khẩu thất bại.');
        }
        $this->redirect('student/profile');
    }
}
