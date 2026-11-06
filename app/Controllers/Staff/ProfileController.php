<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Controller: Hồ sơ cán bộ & Đổi mật khẩu tài khoản
 * Route: /staff/profile
 * View: views/staff/profile.php
 */
class ProfileController extends Controller
{
    /**
     * Hiển thị trang hồ sơ cán bộ
     */
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) {
            redirect('login');
            exit;
        }

        $userId = (int)($user['MaTaiKhoan'] ?? 0);
        $staff = null;
        $buildings = [];

        try {
            $staff = Database::fetch(
                "SELECT nv.*, 
                        tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong, tk.NgayTao,
                        vt.TenVaiTro, vt.MoTa AS MoTaVaiTro,
                        cn.TenCum, cn.ViTriVanPhong, cn.SoDienThoaiCum,
                        k.MaKhu, k.TenKhu, k.DiaChi AS DiaChiKhu, k.SoDienThoaiTruc
                 FROM TaiKhoan tk
                 LEFT JOIN NhanVien nv ON nv.MaTaiKhoan = tk.MaTaiKhoan
                 LEFT JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                 LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
                 LEFT JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                 WHERE tk.MaTaiKhoan = :userId
                 LIMIT 1",
                ['userId' => $userId]
            );

            // Nếu cán bộ có gán cụm quản lý, lấy thêm danh sách tòa nhà thuộc cụm
            if (!empty($staff['MaCumQuanLy'])) {
                $buildings = Database::fetchAll(
                    "SELECT MaToaNha, TenToaNha, TongSoTang, GioiTinhQuyDinh 
                     FROM ToaNha 
                     WHERE MaCum = :maCum 
                     ORDER BY MaToaNha ASC",
                    ['maCum' => $staff['MaCumQuanLy']]
                );
            }
        } catch (\Throwable $e) {
            $staff = null;
        }

        $this->view('staff.profile', [
            'staff' => $staff ?? [],
            'buildings' => $buildings,
            'title' => 'Hồ sơ cán bộ - KTX ĐHQG-HCM',
            'error' => Session::flash('error'),
            'success' => Session::flash('success')
        ]);
    }

    /**
     * Cập nhật thông tin liên hệ của cán bộ (SĐT, Email)
     */
    public function update(): void
    {
        $request = new Request();
        $token = (string)$request->input('csrf_token', '');
        $sessionToken = (string)Session::get('csrf_token', '');

        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
            redirect('staff/profile');
            exit;
        }

        $user = Auth::user();
        if (!$user) {
            redirect('login');
            exit;
        }

        $userId = (int)($user['MaTaiKhoan'] ?? 0);
        $phone = trim((string)$request->input('SoDienThoai', ''));
        $email = trim((string)$request->input('Email', ''));

        if (!preg_match('/^[0-9+\s.-]{9,15}$/', $phone)) {
            Session::flash('error', 'Số điện thoại không hợp lệ (yêu cầu từ 9 đến 15 chữ số).');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Địa chỉ email không đúng định dạng.');
        } elseif ($userId > 0) {
            try {
                Database::execute(
                    "UPDATE NhanVien SET SoDienThoai = :phone, Email = :email WHERE MaTaiKhoan = :userId",
                    ['phone' => $phone, 'email' => $email, 'userId' => $userId]
                );

                // Đồng bộ lại thông tin trong Session
                $currentUser = Session::get('user');
                if (isset($currentUser['profile'])) {
                    $currentUser['profile']['SoDienThoai'] = $phone;
                    $currentUser['profile']['Email'] = $email;
                    Session::set('user', $currentUser);
                }

                Session::flash('success', 'Đã cập nhật thông tin liên hệ cán bộ thành công.');
            } catch (\Throwable $e) {
                Session::flash('error', 'Lỗi cơ sở dữ liệu khi cập nhật: ' . $e->getMessage());
            }
        } else {
            Session::flash('error', 'Không xác định được danh tính tài khoản cán bộ.');
        }

        redirect('staff/profile');
        exit;
    }

    /**
     * Đổi mật khẩu tài khoản cán bộ
     */
    public function changePassword(): void
    {
        $request = new Request();
        $token = (string)$request->input('csrf_token', '');
        $sessionToken = (string)Session::get('csrf_token', '');

        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
            redirect('staff/profile');
            exit;
        }

        $user = Auth::user();
        if (!$user) {
            redirect('login');
            exit;
        }

        $userId = (int)($user['MaTaiKhoan'] ?? 0);
        $oldPassword = (string)$request->input('old_password', '');
        $newPassword = (string)$request->input('new_password', '');
        $confirmPassword = (string)$request->input('confirm_password', '');

        if ($oldPassword === '') {
            Session::flash('error', 'Vui lòng nhập mật khẩu hiện tại.');
            redirect('staff/profile');
            exit;
        }

        if (strlen($newPassword) < 6) {
            Session::flash('error', 'Mật khẩu mới phải có tối thiểu 6 ký tự.');
            redirect('staff/profile');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            Session::flash('error', 'Mật khẩu xác nhận không khớp với mật khẩu mới.');
            redirect('staff/profile');
            exit;
        }

        $res = Auth::changePassword($userId, $oldPassword, $newPassword);
        if ($res['success']) {
            Session::flash('success', 'Đổi mật khẩu thành công! Vui lòng ghi nhớ mật khẩu mới cho các lần đăng nhập tiếp theo.');
        } else {
            Session::flash('error', $res['message'] ?? 'Đổi mật khẩu thất bại.');
        }

        redirect('staff/profile');
        exit;
    }
}

