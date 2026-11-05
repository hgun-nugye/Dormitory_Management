<?php
ob_start();
?>
<style>
.auth-login{--auth-ink:#15324b;--auth-muted:#6c7f91;max-width:1080px;margin:44px auto 64px;padding:0 22px;color:var(--auth-ink)}
.auth-login *{box-sizing:border-box}
.auth-login-shell{display:grid;grid-template-columns:minmax(0,1.03fr) minmax(380px,.97fr);overflow:hidden;border:1px solid #dce6ef;border-radius:24px;background:#fff;box-shadow:0 24px 70px rgba(19,51,77,.12)}
.auth-login-intro{position:relative;isolation:isolate;display:flex;flex-direction:column;justify-content:space-between;min-height:590px;overflow:hidden;padding:42px;background:linear-gradient(145deg,#0b3154 0%,#105c88 58%,#127c91 100%);color:#fff}
.auth-login-intro:before,.auth-login-intro:after{position:absolute;z-index:-1;content:"";border:1px solid rgba(255,255,255,.16);border-radius:50%;pointer-events:none}
.auth-login-intro:before{width:400px;height:400px;right:-215px;top:-155px;box-shadow:0 0 0 34px rgba(255,255,255,.045),0 0 0 72px rgba(255,255,255,.03)}
.auth-login-intro:after{width:300px;height:300px;left:-220px;bottom:-195px;box-shadow:0 0 0 35px rgba(255,255,255,.035)}
.auth-intro-copy{max-width:450px;margin:54px 0 36px}.auth-kicker{display:inline-flex;align-items:center;gap:8px;margin-bottom:17px;color:#bceafa;font-size:.72rem;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.auth-kicker:before{width:22px;height:2px;background:#69d3df;content:""}.auth-intro-copy h1{margin:0 0 15px;color:#fff;font-size:clamp(2rem,4vw,2.85rem);letter-spacing:-.045em;line-height:1.08}.auth-intro-copy p{max-width:410px;margin:0;color:#d3e8f2;font-size:.96rem;line-height:1.75}
.auth-benefits{display:grid;gap:12px;margin:0;padding:0;list-style:none}.auth-benefits li{display:flex;align-items:center;gap:12px;color:#e7f5fa;font-size:.85rem;line-height:1.5}.auth-benefit-icon{display:grid;width:31px;height:31px;flex:0 0 31px;place-items:center;border:1px solid rgba(255,255,255,.2);border-radius:10px;background:rgba(255,255,255,.1);color:#a8e7ef;font-size:.8rem}.auth-intro-footer{display:flex;align-items:center;gap:9px;margin-top:38px;color:#b8d5e2;font-size:.75rem}.auth-status-dot{width:8px;height:8px;border-radius:50%;background:#64d6a1;box-shadow:0 0 0 4px rgba(100,214,161,.14)}
.auth-login-panel{display:flex;flex-direction:column;justify-content:center;padding:48px clamp(28px,5vw,58px)}.auth-panel-top{margin-bottom:33px}.auth-panel-label{display:inline-flex;align-items:center;gap:8px;margin-bottom:17px;color:#2879a8;font-size:.7rem;font-weight:800;letter-spacing:.12em;text-transform:uppercase}.auth-panel-label span{display:grid;width:24px;height:24px;place-items:center;border-radius:8px;background:#eaf5fb;font-size:.9rem}.auth-login-panel h2{margin:0 0 8px;color:#17344e;font-size:1.9rem;letter-spacing:-.04em}.auth-login-panel .auth-subtitle{margin:0;color:var(--auth-muted);font-size:.91rem;line-height:1.6}
.auth-alert{display:flex;align-items:flex-start;gap:10px;margin:0 0 20px;padding:12px 14px;border:1px solid #f1c7c5;border-radius:11px;background:#fff5f4;color:#a52a28;font-size:.86rem;line-height:1.5}.auth-alert-success{border-color:#b7e4ce;background:#f0fbf5;color:#16734a}.auth-form{display:grid;gap:19px}.auth-field-label{display:block;margin-bottom:8px;color:#294359;font-size:.84rem;font-weight:750}.auth-input{width:100%;min-height:49px;border:1px solid #cbd8e3;border-radius:11px;background:#fbfdff;padding:12px 14px;color:#15324b;font:inherit;font-size:.91rem;transition:border-color .18s,box-shadow .18s,background .18s}.auth-input::placeholder{color:#9aabba}.auth-input:hover{border-color:#a8bfd0;background:#fff}.auth-input:focus{outline:0;border-color:#2582b5;background:#fff;box-shadow:0 0 0 4px rgba(37,130,181,.14)}.auth-login .password-field-wrapper{min-height:49px;border:1px solid #cbd8e3;border-radius:11px;background:#fbfdff;transition:border-color .18s,box-shadow .18s,background .18s}.auth-login .password-field-wrapper:focus-within{border-color:#2582b5;background:#fff;box-shadow:0 0 0 4px rgba(37,130,181,.14)}.auth-login .password-field-wrapper input{min-height:47px;border:0!important;border-radius:11px;background:transparent!important;padding:12px 45px 12px 14px!important;box-shadow:none!important;font:inherit;font-size:.91rem}.auth-login .password-field-wrapper input:focus{outline:0}.auth-login .btn-toggle-password{right:9px!important;color:#71869a!important}.auth-login .btn-toggle-password:hover{color:#1769aa!important}.auth-hint{margin:8px 0 0;color:#8292a2;font-size:.76rem;line-height:1.5}.auth-submit{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;min-height:50px;margin-top:3px;border:0;border-radius:11px;background:linear-gradient(110deg,#1769aa,#117c98);box-shadow:0 9px 20px rgba(23,105,170,.2);color:#fff;font:inherit;font-size:.87rem;font-weight:800;letter-spacing:.045em;cursor:pointer;transition:transform .18s,box-shadow .18s}.auth-submit:hover{transform:translateY(-1px);box-shadow:0 13px 24px rgba(23,105,170,.27)}.auth-submit:focus-visible{outline:3px solid #74c5e4;outline-offset:3px}.auth-login-divider{height:1px;margin:26px 0 20px;background:#e9eef3}.auth-register{color:#738498;text-align:center;font-size:.84rem;line-height:1.6}.auth-register a{color:#1769aa;font-weight:750;text-decoration:none}.auth-register a:hover{text-decoration:underline}.auth-security-note{display:flex;justify-content:center;align-items:center;gap:7px;margin-top:25px;color:#8b9aaa;font-size:.73rem}
.auth-panel-label{color:#15577f}.auth-register a{color:#145b91}.auth-security-note{color:#687b8d}
@media(max-width:800px){.auth-login{max-width:600px;margin:28px auto 45px;padding:0 16px}.auth-login-shell{grid-template-columns:1fr;border-radius:20px}.auth-login-intro{min-height:0;padding:26px 28px 24px}.auth-intro-copy{margin:28px 0 20px}.auth-intro-copy h1{font-size:2rem}.auth-intro-copy p{font-size:.88rem}.auth-benefits{grid-template-columns:1fr 1fr;gap:9px}.auth-benefits li{align-items:flex-start;font-size:.77rem}.auth-intro-footer{margin-top:20px}.auth-login-panel{padding:32px 28px 34px}.auth-panel-top{margin-bottom:25px}}
@media(max-width:480px){.auth-login{margin:18px auto 32px;padding:0 12px}.auth-login-shell{border-radius:17px}.auth-login-intro{padding:22px 20px}.auth-intro-copy{margin:24px 0 17px}.auth-intro-copy h1{font-size:1.78rem}.auth-benefits{grid-template-columns:1fr}.auth-benefits li:nth-child(n+3){display:none}.auth-intro-footer{font-size:.7rem}.auth-login-panel{padding:27px 20px 28px}.auth-panel-top{margin-bottom:22px}.auth-login-panel h2{font-size:1.65rem}}
</style>

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
                <div class="auth-panel-label"><span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></span> Đăng nhập an toàn</div>
                <h2>Chào mừng trở lại</h2>
                <p class="auth-subtitle">Sử dụng tài khoản KTX được cấp để tiếp tục.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="auth-alert" role="alert"><i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i><span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span></div>
            <?php endif; ?>
            <?php if (!empty($success)): ?>
                <div class="auth-alert auth-alert-success" role="status"><i class="fa-solid fa-circle-check" aria-hidden="true"></i><span><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></span></div>
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
