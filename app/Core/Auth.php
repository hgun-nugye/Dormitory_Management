<?php

namespace App\Core;

class Auth
{
    // Kiểm tra người dùng đã đăng nhập chưa và phiên có hợp lệ không
    public static function check(): bool
    {
        if (!Session::has('user')) {
            return false;
        }

        if (!Session::validateSecurity(3600)) {
            Session::flash('error', 'Phiên làm việc của bạn đã hết hạn do không hoạt động. Vui lòng đăng nhập lại.');
            return false;
        }

        return true;
    }

    // Kiểm tra tính kích hoạt thực tế của tài khoản trong CSDL
    public static function isUserActive(): bool
    {
        $userId = self::id();
        if (!$userId) {
            return false;
        }

        // Kiểm tra CSDL định kỳ mỗi 60s để giảm tải
        $lastCheck = (int)Session::get('last_db_check', 0);
        if (time() - $lastCheck < 60) {
            return true;
        }

        try {
            $row = Database::fetch(
                "SELECT DangHoatDong, MaVaiTro FROM TaiKhoan WHERE MaTaiKhoan = :id LIMIT 1",
                ['id' => $userId]
            );

            if (!$row || (int)($row['DangHoatDong'] ?? 0) !== 1) {
                self::logout();
                Session::flash('error', 'Tài khoản của bạn đã bị khóa hoặc ngừng hoạt động.');
                return false;
            }

            // Đồng bộ lại vai trò nếu bị quản trị viên thay đổi giữa phiên
            $currentUser = Session::get('user');
            if ($currentUser && ($currentUser['MaVaiTro'] ?? '') !== $row['MaVaiTro']) {
                $currentUser['MaVaiTro'] = $row['MaVaiTro'];
                $vt = Database::fetch("SELECT TenVaiTro FROM VaiTro WHERE MaVaiTro = :vt LIMIT 1", ['vt' => $row['MaVaiTro']]);
                if ($vt) {
                    $currentUser['VaiTro'] = $vt['TenVaiTro'];
                }
                Session::set('user', $currentUser);
            }

            Session::set('last_db_check', time());
            return true;
        } catch (\Throwable $e) {
            return true;
        }
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

    // Kiểm tra có phải nhân viên không (Bao gồm Cán bộ quản lý KTX, Kế toán và Kỹ thuật, không bao gồm Admin)
    public static function isNhanVien(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        // Quản trị viên hệ thống (Admin) tách biệt hoàn toàn với Cán bộ KTX
        if (self::isQuanTriVien()) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = $user['VaiTro'] ?? '';
        return in_array($ma, ['NhanVien', 'NV_THUONG', 'TRUONG_TOA', 'TRUONG_CUM', 'TRUONG_KHU', 'NV_QUAN_LY_KTX', 'KE_TOAN'], true)
            || str_contains(mb_strtolower($ten), 'nhân viên')
            || str_contains(mb_strtolower($ten), 'trưởng tòa')
            || str_contains(mb_strtolower($ten), 'trưởng cụm')
            || str_contains(mb_strtolower($ten), 'trưởng khu')
            || str_contains(mb_strtolower($ten), 'kế toán');
    }

    // Kiểm tra có phải cán bộ quản lý KTX không (Quản lý KTX, Trưởng Khu, Trưởng Cụm, Trưởng Tòa)
    public static function isQuanLy(): bool
    {
        $user = self::user();
        if (!$user || self::isQuanTriVien()) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = mb_strtolower($user['VaiTro'] ?? '');
        $username = strtoupper($user['TenDangNhap'] ?? '');
        $chucVu = mb_strtolower($user['profile']['ChucVu'] ?? '');

        // Chặn tuyệt đối nếu là kế toán, kỹ thuật điện nước, hoặc sinh viên
        if (in_array($ma, ['KE_TOAN', 'NV_THUONG', 'SINH_VIEN', 'TRUONG_PHONG'], true)) {
            return false;
        }
        if (str_starts_with($username, 'NV_KT') || str_starts_with($username, 'KT')) {
            return false;
        }
        if (str_contains($chucVu, 'kế toán') || str_contains($chucVu, 'kỹ thuật') || str_contains($chucVu, 'điện nước')) {
            return false;
        }

        return in_array($ma, ['NV_QUAN_LY_KTX', 'TRUONG_KHU', 'TRUONG_CUM', 'TRUONG_TOA'], true)
            || str_contains($ten, 'quản lý')
            || str_contains($ten, 'trưởng khu')
            || str_contains($ten, 'trưởng cụm')
            || str_contains($ten, 'trưởng tòa')
            || str_contains($chucVu, 'trưởng tòa')
            || str_contains($chucVu, 'trưởng ban')
            || str_contains($chucVu, 'trưởng khu');
    }

    // Kiểm tra có phải nhân viên kế toán không
    public static function isKeToan(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $ten = $user['VaiTro'] ?? '';
        $username = strtoupper($user['TenDangNhap'] ?? '');
        $chucVu = mb_strtolower($user['profile']['ChucVu'] ?? '');
        return $ma === 'KE_TOAN'
            || str_starts_with($username, 'KT')
            || str_contains(mb_strtolower($ten), 'kế toán')
            || str_contains($chucVu, 'kế toán');
    }

    // Kiểm tra có phải nhân viên kỹ thuật (thợ điện nước, bảo trì) không
    public static function isKyThuat(): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        $ma = $user['MaVaiTro'] ?? '';
        $username = strtoupper($user['TenDangNhap'] ?? '');
        $chucVu = mb_strtolower($user['profile']['ChucVu'] ?? '');
        return ($ma === 'NV_THUONG' || str_starts_with($username, 'NV_KT'))
            && (str_contains($chucVu, 'kỹ thuật') || str_contains($chucVu, 'điện nước') || str_contains($chucVu, 'bảo trì') || str_starts_with($username, 'NV_KT'));
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
        } elseif (in_array($maVT, ['NhanVien', 'NV_THUONG', 'TRUONG_TOA', 'TRUONG_CUM', 'TRUONG_KHU', 'NV_QUAN_LY_KTX', 'KE_TOAN'], true) || str_contains($tenVT, 'nhân viên') || str_contains($tenVT, 'trưởng') || str_contains($tenVT, 'kế toán')) {
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

        // 4. Chống Session Fixation: Tái tạo session ID mới trước khi cấp quyền
        Session::regenerate(true);

        // Lưu thông tin đăng nhập và khởi tạo chữ ký bảo mật
        Session::set('user', $user);
        Session::initSecurityFingerprint();
        Session::set('last_db_check', time());

        return true;
    }

    // Đăng xuất
    public static function logout(): void
    {
        Session::remove('user');
        Session::destroy();
        Session::regenerate(true);
    }
}
