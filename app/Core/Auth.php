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

    // Băm mật khẩu theo chuẩn BCrypt
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    // Kiểm tra tính hợp lệ của mật khẩu (hỗ trợ hash BCrypt & demo fallback)
    public static function verifyPassword(string $password, string $hash): bool
    {
        $isMatch = password_verify($password, $hash);

        // Chỉ hỗ trợ mật khẩu demo nếu dữ liệu chứa hash mẫu
        if (
            !$isMatch
            && str_starts_with($hash, '$2y$10$demo_hash')
            && in_array($password, ['Demo@123', '123456'], true)
        ) {
            $isMatch = true;
        }

        return $isMatch;
    }

    // Đổi mật khẩu người dùng (Bảo toàn dữ liệu theo PDF: Mật khẩu băm BCrypt)
    public static function changePassword(int $userId, string $oldPassword, string $newPassword): array
    {
        $user = Database::fetch(
            "SELECT * FROM TaiKhoan WHERE MaTaiKhoan = :id LIMIT 1",
            ['id' => $userId]
        );

        if (!$user) {
            return ['success' => false, 'message' => 'Không tìm thấy tài khoản.'];
        }

        if (!self::verifyPassword($oldPassword, $user['MatKhauMaHoa'] ?? '')) {
            return ['success' => false, 'message' => 'Mật khẩu cũ không chính xác.'];
        }

        if (strlen($newPassword) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.'];
        }

        $newHash = self::hashPassword($newPassword);
        Database::execute(
            "UPDATE TaiKhoan SET MatKhauMaHoa = :hash WHERE MaTaiKhoan = :id",
            ['hash' => $newHash, 'id' => $userId]
        );

        return ['success' => true, 'message' => 'Đổi mật khẩu thành công.'];
    }

    /**
     * Render trình / ô nhập mật khẩu có sẵn nút ẩn/hiện (Show/Hide Password Widget)
     * Sử dụng được cho cả form đăng nhập và form đổi mật khẩu
     */
    public static function passwordInput(
        string $name = 'password',
        string $id = 'password',
        string $placeholder = 'Nhập mật khẩu',
        bool $required = true,
        string $extraClass = ''
    ): string {
        $reqAttr = $required ? 'required' : '';
        $cls = trim('form-control ' . $extraClass);
        $escapedPlaceholder = htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8');

        return <<<HTML
        <div class="password-field-wrapper" style="position: relative; display: flex; align-items: center; width: 100%;">
            <input type="password" id="{$id}" name="{$name}" class="{$cls}" placeholder="{$escapedPlaceholder}" {$reqAttr} style="padding-right: 42px; width: 100%;">
            <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility('{$id}', this)" title="Hiện mật khẩu" aria-label="Hiện mật khẩu" style="position: absolute; right: 8px; background: none; border: none; cursor: pointer; padding: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-muted); transition: color 0.15s ease;">
                <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </button>
        </div>
HTML;
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
        if (!self::verifyPassword($password, $hash)) {
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
