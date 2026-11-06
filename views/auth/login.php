<?php
ob_start();
?>

<div class="auth-login">
    <section class="auth-login-shell" aria-label="Đăng nhập KTX ĐHQG-HCM">
        <aside class="auth-login-intro">
            <div>
                <div class="auth-intro-copy">
                    <div class="auth-kicker">Cổng thông tin nội trú</div>
                    <h1>Thông tin lưu trú và dịch vụ sinh viên</h1>
                    <p>Đăng nhập để theo dõi hồ sơ, chỗ ở, hóa đơn và gửi yêu cầu hỗ trợ đến Ban quản lý.</p>
                </div>
                <ul class="auth-benefits">
                    <li><span class="auth-benefit-icon"><i class="fa-solid fa-house-user" aria-hidden="true"></i></span>Quản lý hồ sơ và thông tin lưu trú</li>
                    <li><span class="auth-benefit-icon"><i class="fa-solid fa-file-invoice" aria-hidden="true"></i></span>Tra cứu hóa đơn và chi phí dịch vụ</li>
                    <li><span class="auth-benefit-icon"><i class="fa-solid fa-headset" aria-hidden="true"></i></span>Gửi yêu cầu, theo dõi tiến độ xử lý</li>
                </ul>
            </div>
            <div class="auth-intro-footer"><span class="auth-status-dot"></span> Hệ thống hỗ trợ sinh viên và cán bộ KTX</div>
        </aside>

        <div class="auth-login-panel">
            <div class="auth-panel-top">
                <h2>Chào mừng trở lại</h2>
                <p class="auth-subtitle">Sử dụng tài khoản KTX được cấp để tiếp tục.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert" role="alert">
                    <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                    <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="auth-alert auth-alert-success" role="status">
                    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                    <span><?= $success ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= url('login') ?>" method="post" class="auth-form">
                <?= csrf_field() ?>
                <div>
                    <label class="auth-field-label" for="username">Tên đăng nhập</label>
                    <input class="auth-input" type="text" id="username" name="username" required autofocus autocomplete="username" placeholder="Nhập MSSV hoặc mã tài khoản">
                    <p class="auth-hint">Sinh viên dùng MSSV; cán bộ dùng tài khoản do quản trị viên cấp.</p>
                </div>
                <div>
                    <label class="auth-field-label" for="password">Mật khẩu</label>
                    <?= \App\Core\Auth::passwordInput('password', 'password', 'Nhập mật khẩu', true, 'auth-input') ?>
                </div>
                <button class="auth-submit" type="submit">Đăng nhập <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
            </form>

            <div class="auth-login-divider"></div>
            <div class="auth-register">Chưa có hồ sơ nội trú? <a href="<?= url('register') ?>">Đăng ký ngay</a></div>
            <div class="auth-security-note"><i class="fa-solid fa-lock" aria-hidden="true"></i> Thông tin đăng nhập chỉ dùng cho hệ thống KTX</div>
        </div>
    </section>
</div>

<?php
$content = ob_get_clean();
$title = 'Đăng nhập - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
?>