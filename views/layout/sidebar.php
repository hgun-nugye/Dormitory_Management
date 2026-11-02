<?php
$studentName = isset($student['HoVaTen']) ? $student['HoVaTen'] : '';
$studentCode = isset($student['MaSinhVien']) ? $student['MaSinhVien'] : '';
?>
<aside class="sidebar">
    <a class="brand" href="?view=dashboard">
        <span class="brand-mark">KTX</span>
        <span><strong>ĐHQG-HCM</strong><small>Ký túc xá</small></span>
    </a>
    <nav>
        <a class="<?= $view === 'dashboard' ? 'active' : '' ?>" href="?view=dashboard"><span>⌂</span>Tổng quan</a>
        <a class="<?= $view === 'profile' ? 'active' : '' ?>" href="?view=profile"><span>♙</span>Thông tin cá nhân</a>
        <a class="<?= $view === 'registration' ? 'active' : '' ?>" href="?view=registration"><span>▤</span>Đăng ký chỗ ở</a>
        <a class="<?= $view === 'payments' ? 'active' : '' ?>" href="?view=payments"><span>▣</span>Thanh toán</a>
        <a class="<?= $view === 'activities' ? 'active' : '' ?>" href="?view=activities"><span>★</span>Hoạt động</a>
        <a class="<?= $view === 'requests' ? 'active' : '' ?>" href="?view=requests"><span>✦</span>Yêu cầu hỗ trợ</a>
    </nav>
    <div class="sidebar-user">
        <div class="avatar"><?= htmlspecialchars($studentName !== '' ? mb_substr($studentName, 0, 1) : '') ?></div>
        <div>
            <strong><?= htmlspecialchars($studentName) ?></strong>
            <small><?= htmlspecialchars($studentCode) ?></small>
        </div>
    </div>
</aside>
