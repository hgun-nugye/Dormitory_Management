<?php
ob_start();
?>

<div class="container">
    <div class="login-box">
        <div class="login-header">
            <h2>Đăng nhập hệ thống</h2>
            <p>Cổng thông tin Ký túc xá Đại học Quốc gia TP.HCM</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= url('login') ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username">Tên đăng nhập (MSSV / Mã nhân viên / Admin)</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Nhập mã sinh viên hoặc mã nhân viên" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Nhập mật khẩu" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">
                Đăng nhập
            </button>
        </form>

        <div class="login-demo-accounts">
            <span class="demo-title">Tài khoản demo kiểm thử:</span>
            <ul>
                <li>Sinh viên: <code>2110001</code> hoặc <code>2212345</code> (Mật khẩu: Demo@123)</li>
                <li>Cán bộ BQL cụm: <code>NV001</code> hoặc <code>NV002</code> (Mật khẩu: Demo@123)</li>
                <li>Quản trị viên: <code>admin</code> (Mật khẩu: Demo@123)</li>
            </ul>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Đăng nhập - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
