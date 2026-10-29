<?php

namespace App\Core;

class Auth
{
    // Kiểm tra người dùng đã đăng nhập chưa
    public static function check(): bool
    {
        return Session::has('user');
    }

    // Lấy thông tin người dùng đang đăng nhập
    public static function user(): ?array
    {
        return Session::get('user');
    }

    // Lấy mã tài khoản
    public static function id(): ?int
    {
        $user = self::user();
        return isset($user['MaTaiKhoan'])
            ? (int) $user['MaTaiKhoan']
            : null;
    }

    // Lấy vai trò người dùng
    public static function role(): ?string
    {
        $user = self::user();
        return $user['MaVaiTro'] ?? $user['VaiTro'] ?? null;
    }

    // Kiểm tra có phải sinh viên không (gồm cả Sinh viên nội trú & Trưởng phòng)
    public static function isSinhVien(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = $user['VaiTro'] ?? '';
        return in_array($ma, ['SinhVien', 'SINH_VIEN', 'TRUONG_PHONG'], true)
            || str_contains(mb_strtolower($ten), 'sinh viên')
            || str_contains(mb_strtolower($ten), 'trưởng phòng');
    }

    // Kiểm tra có phải nhân viên không
    public static function isNhanVien(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = $user['VaiTro'] ?? '';
        return in_array($ma, ['NhanVien', 'NV_THUONG', 'TRUONG_CUM', 'TRUONG_KHU', 'NV_QUAN_LY_KTX'], true)
            || str_contains(mb_strtolower($ten), 'nhân viên')
            || str_contains(mb_strtolower($ten), 'trưởng cụm')
            || str_contains(mb_strtolower($ten), 'trưởng khu');
    }

    // Kiểm tra có phải quản trị viên không
    public static function isQuanTriVien(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = $user['VaiTro'] ?? '';
        return in_array($ma, ['QuanTriVien', 'ADMIN'], true)
            || str_contains(mb_strtolower($ten), 'quản trị');
    }

    // Đăng nhập
    public static function login(
        string $username,
        string $password
    ): bool {
        // 1. Tìm tài khoản và lấy tên vai trò
        $user = Database::fetch(
            "SELECT tk.*, vt.TenVaiTro AS VaiTro
             FROM TaiKhoan tk
             JOIN VaiTro vt
                 ON tk.MaVaiTro = vt.MaVaiTro
             WHERE tk.TenDangNhap = :username
               AND tk.DangHoatDong = 1
             LIMIT 1",
            ['username' => $username]
        );

        // Không tìm thấy tài khoản
        if (!$user) {
            return false;
        }

        // 2. Kiểm tra mật khẩu đã mã hóa
        $hash = $user['MatKhauMaHoa'] ?? '';

        $isMatch = password_verify($password, $hash);

        // Chỉ hỗ trợ mật khẩu demo nếu dữ liệu chứa hash mẫu
        if (
            !$isMatch
            && str_starts_with($hash, '$2y$10$demo_hash')
            && in_array(
                $password,
                ['Demo@123', '123456'],
                true
            )
        ) {
            $isMatch = true;
        }

        // Mật khẩu không đúng
        if (!$isMatch) {
            return false;
        }

        // 3. Lấy hồ sơ theo vai trò
        $maVT = $user['MaVaiTro'] ?? '';
        $tenVT = mb_strtolower($user['VaiTro'] ?? '');

        if (in_array($maVT, ['SinhVien', 'SINH_VIEN', 'TRUONG_PHONG'], true) || str_contains($tenVT, 'sinh viên') || str_contains($tenVT, 'trưởng phòng')) {
            $sv = Database::fetch(
                "SELECT *
                 FROM SinhVien
                 WHERE MaTaiKhoan = :id",
                ['id' => $user['MaTaiKhoan']]
            );

            if ($sv) {
                $user['profile'] = $sv;
                $user['HoVaTen'] = $sv['HoVaTen'];
            }
        } elseif (in_array($maVT, ['NhanVien', 'NV_THUONG', 'TRUONG_CUM', 'TRUONG_KHU', 'NV_QUAN_LY_KTX'], true) || str_contains($tenVT, 'nhân viên')) {
            $nv = Database::fetch(
                "SELECT *
                 FROM NhanVien
                 WHERE MaTaiKhoan = :id",
                ['id' => $user['MaTaiKhoan']]
            );

            if ($nv) {
                $user['profile'] = $nv;
                $user['HoVaTen'] = $nv['HoVaTen'];
            }
        } elseif ($maVT === 'ADMIN' || $maVT === 'QuanTriVien' || str_contains($tenVT, 'quản trị')) {
            $user['HoVaTen'] = 'Quản trị viên Hệ thống';
        }

        // 4. Lưu thông tin đăng nhập
        Session::set('user', $user);

        return true;
    }

    // Đăng xuất
    public static function logout(): void
    {
        Session::remove('user');
        Session::destroy();
    }
}
