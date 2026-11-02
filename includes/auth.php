<?php
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'login') {
    if (!validateCsrfToken($_POST)) {
        $message = 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $loggedIn = false;

        if (isset($pdo) && $pdo) {
            $stmt = $pdo->prepare(
                'SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MatKhauMaHoa, tk.MaVaiTro, tk.DangHoatDong,
                        sv.MaSinhVien
                 FROM TaiKhoan tk
                 LEFT JOIN SinhVien sv ON sv.MaTaiKhoan = tk.MaTaiKhoan
                 WHERE tk.TenDangNhap = ? LIMIT 1'
            );
            $stmt->execute([$username]);
            $account = $stmt->fetch();

            if (
                $account
                && $account['DangHoatDong']
                && $account['MaSinhVien']
                && in_array($account['MaVaiTro'], ['SINH_VIEN', 'TRUONG_PHONG'], true)
                && password_verify($password, $account['MatKhauMaHoa'])
            ) {
                $_SESSION['student_id'] = $account['MaSinhVien'];
                $_SESSION['account_id'] = $account['MaTaiKhoan'];
                $_SESSION['student_role'] = $account['MaVaiTro'];
                session_regenerate_id(true);
                $loggedIn = true;
            }
        } else {
            $message = 'Không thể kết nối cơ sở dữ liệu.';
        }

        if (!$loggedIn && !$message) {
            $message = 'Tên đăng nhập, mật khẩu hoặc quyền truy cập không hợp lệ.';
        } elseif ($loggedIn) {
            header('Location: index.php');
            exit;
        }
    }
}
