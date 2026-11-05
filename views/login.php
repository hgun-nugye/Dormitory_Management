<main class="login-page">
    <a class="login-back" href="index.php"><span>←</span> Quay lại Cổng thông tin Website KTX</a>
    <section class="login-shell">
        <div class="login-intro">
            <div class="institution-badge"><span>★</span> TRƯỜNG ĐẠI HỌC CÔNG LẬP TRỌNG ĐIỂM QUỐC GIA</div>
            <div class="institution-brand">
                <div class="institution-logo">KTX<span>ĐHQG</span></div>
                <div><strong>TRUNG TÂM QUẢN LÝ KTX DHQG-HCM</strong></div>
            </div>
            <p class="intro-description">Nền tảng số hóa quản trị ký túc xá thông minh, hỗ trợ sinh viên và ban quản lý truy cập dịch vụ thuận tiện 24/7.</p>
            <div class="feature-list">
                <article><div class="feature-icon">▦</div><div><strong>Quản lý Ký túc xá trực tuyến</strong><span>Quản lý hồ sơ, phòng ở, hợp đồng lưu trú và các dịch vụ liên quan</span></div></article>
                <article><div class="feature-icon blue">▣</div><div><strong>Thanh toán hóa đơn 24/7</strong><span>Tra cứu công nợ và theo dõi trạng thái thanh toán một cách minh bạch</span></div></article>
                <article><div class="feature-icon green">◉</div><div><strong>Tiếp nhận & xử lý yêu cầu</strong><span>Gửi yêu cầu chuyển phòng, tạm vắng, trả phòng và báo sự cố nhanh chóng</span></div></article>
            </div>
            <div class="intro-footer"><span><i></i> Hệ thống KTX trực tuyến</span><span>Học kỳ 2026 - 2027</span></div>
        </div>
        <div class="login-panel">
            <div class="portal-label"><span>◈</span> CỔNG XÁC THỰC TẬP TRUNG</div>
            <div class="login-heading"><h1>Đăng Nhập Hệ Thống</h1><p>Cán bộ quản lý và Sinh viên nội trú</p></div>
            <div class="role-tabs" role="radiogroup" aria-label="Vai trò đăng nhập">
                <label class="active"><input type="radio" name="display_role" value="staff" checked><span>♟</span> Cán bộ / BQL KTX</label>
                <label><input type="radio" name="display_role" value="student"><span>♧</span> Sinh viên Nội trú</label>
            </div>
            <?php if (!empty($message)): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <form method="post" class="login-form">
                <input type="hidden" name="action" value="login">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <label class="field-label"><span>♙ <b>Tên đăng nhập cần có:</b></span><a href="#">Quản trị &amp; Cán bộ</a>
                    <div class="input-wrap username-wrap"><span>♙</span><input name="username" required autocomplete="username" placeholder="Nhập tên đăng nhập (Ví dụ: thudv, admin, ha.nt...)"></div>
                </label>
                <p class="field-note">ⓘ Cán bộ dùng tài khoản do Quản trị viên cấp.</p>
                <label class="field-label"><span><b>Mật khẩu:</b></span>
                    <div class="input-wrap password-wrap"><span>●</span><input type="password" name="password" required autocomplete="current-password" placeholder="Nhập mật khẩu..."><button type="button" class="password-toggle" aria-label="Hiển thị mật khẩu">◉</button></div>
                </label>
                <div class="form-options"><label><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label><a href="#">ⓘ Trợ giúp đăng nhập?</a></div>
                <button class="login-submit" type="submit">ĐĂNG NHẬP HỆ THỐNG <span>→</span></button>
            </form>
            <div class="login-footer"><span>☎ Hotline: <strong>0886140140</strong></span><span>♢ <strong>SSL 256-bit</strong></span></div>
        </div>
    </section>
</main>
