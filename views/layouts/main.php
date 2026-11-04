<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? config('app.name')) ?></title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- App Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>?v=<?= time() ?>">
    <style>
        .nav-links a:not(.btn) {
            text-decoration: none;
            color: var(--text-secondary, #475569);
            font-weight: 500;
            font-size: 0.9rem;
            padding: 6px 12px;
            border-radius: 6px;
            transition: all 0.15s ease;
            display: inline-flex;
            align-items: center;
        }
        .nav-links a:not(.btn):hover {
            color: #2563eb;
            background-color: #f1f5f9;
        }
        .nav-links a:not(.btn).active {
            color: #1d4ed8 !important;
            background-color: #eff6ff !important;
            font-weight: 600 !important;
            box-shadow: inset 0 0 0 1px #bfdbfe;
        }
    </style>
</head>

<body>
    <header class="navbar">
        <a href="<?= url('/') ?>" class="brand">
            <span class="brand-name">KTX ĐHQG-HCM</span>
            <span class="badge-uni">VNU-HCM</span>
        </a>
        <ul class="nav-links">
            <li><a href="<?= url('/') ?>" class="<?= nav_active_class('') ?>">Trang chủ</a></li>
            <?php if (\App\Core\Auth::check()): ?>
                <?php if (\App\Core\Auth::isSinhVien()): ?>
                    <li><a href="<?= url('student/dashboard') ?>" class="<?= nav_active_class('student/dashboard') ?>">Cổng sinh viên</a></li>
                    <li><a href="<?= url('student/profile') ?>" class="<?= nav_active_class('student/profile') ?>">Hồ sơ sinh viên</a></li>
                    <li><a href="<?= url('student/payments') ?>" class="<?= nav_active_class('student/payments') ?>">Thanh toán</a></li>
                <?php elseif (\App\Core\Auth::isNhanVien()): ?>
                    <li><a href="<?= url('staff/dashboard') ?>" class="<?= nav_active_class('staff/dashboard') ?>">BQL Cụm</a></li>
                    <?php if (\App\Core\Auth::isQuanLy()): ?>
                        <li><a href="<?= url('staff/zones') ?>" class="<?= nav_active_class('staff/zones') ?>">Khu & Cụm</a></li>
                        <li><a href="<?= url('staff/rooms') ?>" class="<?= nav_active_class('staff/rooms') ?>">Sơ đồ phòng</a></li>
                        <li><a href="<?= url('staff/applications') ?>" class="<?= nav_active_class('staff/applications') ?>">Xét duyệt 72h</a></li>
                        <li><a href="<?= url('staff/utilities') ?>" class="<?= nav_active_class('staff/utilities') ?>">Điện nước</a></li>
                        <li><a href="<?= url('staff/requests') ?>" class="<?= nav_active_class('staff/requests') ?>">Đơn 1 cửa</a></li>
                        <li><a href="<?= url('staff/maintenance') ?>" class="<?= nav_active_class('staff/maintenance') ?>">Bảo trì</a></li>
                        <li><a href="<?= url('staff/discipline') ?>" class="<?= nav_active_class('staff/discipline') ?>">An ninh</a></li>
                    <?php elseif (\App\Core\Auth::isKeToan()): ?>
                        <li><a href="<?= url('staff/utilities') ?>" class="<?= nav_active_class('staff/utilities') ?>">Điện nước & Công nợ</a></li>
                        <li><a href="<?= url('staff/requests') ?>" class="<?= nav_active_class('staff/requests') ?>">Đơn 1 cửa</a></li>
                    <?php else: /* Thợ kỹ thuật điện nước / Nhân viên bảo trì */ ?>
                        <li><a href="<?= url('staff/maintenance') ?>" class="<?= nav_active_class('staff/maintenance') ?>">Bảo trì</a></li>
                    <?php endif; ?>
                <?php elseif (\App\Core\Auth::isQuanTriVien()): ?>
                    <li><a href="<?= url('admin/dashboard') ?>" class="<?= nav_active_class('admin/dashboard') ?>">Dashboard</a></li>
                    <li><a href="<?= url('admin/users') ?>" class="<?= nav_active_class('admin/users') ?>">Cán bộ</a></li>
                    <li><a href="<?= url('admin/universities') ?>" class="<?= nav_active_class('admin/universities') ?>">Trường ĐH</a></li>
                    <li><a href="<?= url('staff/maintenance') ?>" class="<?= nav_active_class('staff/maintenance') ?>">Bảo trì</a></li>
                    <li><a href="<?= url('staff/discipline') ?>" class="<?= nav_active_class('staff/discipline') ?>">An ninh</a></li>
                <?php endif; ?>
                <li>
                    <span class="user-info">
                        <?= htmlspecialchars(\App\Core\Auth::user()['HoVaTen'] ?? \App\Core\Auth::user()['TenDangNhap']) ?>
                    </span>
                </li>
                <li><a href="<?= url('logout') ?>" class="btn btn-primary btn-sm" style="color:white">Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="<?= url('login') ?>" class="btn btn-primary btn-sm" style="color:white">Đăng nhập</a></li>
            <?php endif; ?>
        </ul>
    </header>

    <main>
        <?= $content ?? '' ?>
    </main>

    <footer>
        <p>© <?= date('Y') ?> Trung tâm Quản lý Ký túc xá Đại học Quốc gia TP.HCM</p>
    </footer>

    <!-- Reusable Confirmation Modal -->
    <?php require_once dirname(__DIR__) . '/components/confirm_modal.php'; ?>

    <script src="<?= asset('js/main.js') ?>?v=<?= time() ?>"></script>
    <script>
        // Tự động kiểm tra và đánh dấu thẻ đang active trên thanh điều hướng
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname.replace(/\/+$/, '') || '/';
            const navLinks = document.querySelectorAll('.nav-links a:not(.btn)');
            let matched = false;

            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (!href) return;
                try {
                    const url = new URL(href, window.location.origin);
                    const linkPath = url.pathname.replace(/\/+$/, '') || '/';
                    
                    if (linkPath === currentPath) {
                        link.classList.add('active');
                        matched = true;
                    }
                } catch (e) {}
            });

            // Nếu chưa khớp chính xác, khớp theo tiền tố đường dẫn (trừ trang chủ '/')
            if (!matched && currentPath !== '/') {
                navLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href) return;
                    try {
                        const url = new URL(href, window.location.origin);
                        const linkPath = url.pathname.replace(/\/+$/, '') || '/';
                        if (linkPath !== '/' && currentPath.startsWith(linkPath)) {
                            link.classList.add('active');
                        }
                    } catch (e) {}
                });
            }
        });
    </script>
</body>

</html>