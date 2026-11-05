<?php
ob_start();
$sinhVien = $sinhVien ?? [];
$hopDong = $hopDong ?? null;
$hoSoDangKy = $hoSoDangKy ?? null;
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <span>/</span>
        <a href="<?= url('student/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Cổng sinh viên</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;">Hồ sơ sinh viên</span>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title"> Hồ sơ sinh viên</h1>
        </div>
    </div>

    <?php if (empty($sinhVien)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <i class="fa-solid fa-user-slash" style="font-size: 48px; color: #cbd5e1; margin-bottom: 14px;"></i>
            <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 8px;">Không tìm thấy thông tin hồ sơ</h3>
            <p class="text-muted">Tài khoản hiện tại chưa được liên kết với hồ sơ sinh viên trong hệ thống quản lý KTX.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2" style="margin-bottom: 24px;">
            <!-- COLUMN 1: PERSONAL & ACADEMIC INFO -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Personal & Academic Card -->
                <div class="card">
                    <div class="card-title">
                        <span>Thông tin cá nhân & Học vụ</span>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-hashtag" style="margin-right: 6px;"></i> Mã số SV (MSSV)</td>
                            <td class="info-value">
                                <b style="font-size: 1rem; color: var(--primary); letter-spacing: 0.02em;"><?= htmlspecialchars($sinhVien['MaSinhVien']) ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-signature" style="margin-right: 6px;"></i> Họ và tên</td>
                            <td class="info-value"><b><?= htmlspecialchars($sinhVien['HoVaTen']) ?></b></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-regular fa-calendar" style="margin-right: 6px;"></i> Ngày sinh</td>
                            <td class="info-value">
                                <?= !empty($sinhVien['NgaySinh']) ? date('d/m/Y', strtotime($sinhVien['NgaySinh'])) : 'Chưa cập nhật' ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-venus-mars" style="margin-right: 6px;"></i> Giới tính</td>
                            <td class="info-value"><?= htmlspecialchars($sinhVien['GioiTinh'] ?? 'Chưa rõ') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-regular fa-address-card" style="margin-right: 6px;"></i> Số CCCD/Định danh</td>
                            <td class="info-value"><code><?= htmlspecialchars($sinhVien['SoCCCD'] ?? 'Chưa cập nhật') ?></code></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-building-columns" style="margin-right: 6px;"></i> Trường đào tạo</td>
                            <td class="info-value"><b><?= htmlspecialchars($sinhVien['TenTruong'] ?? 'Đại học Quốc gia TP.HCM') ?></b></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-graduation-cap" style="margin-right: 6px;"></i> Khóa tuyển sinh</td>
                            <td class="info-value"><?= htmlspecialchars($sinhVien['KhoaTuyenSinh'] ?? 'Chưa cập nhật') ?></td>
                        </tr>
                    </table>
                </div>

                <!-- Housing Status Card -->
                <div class="card">
                    <div class="card-title">
                        <span>Tình trạng lưu trú</span>
                        <span class="badge <?= !empty($hopDong) ? 'badge-success' : (!empty($hoSoDangKy) ? 'badge-warning' : 'badge-danger') ?>">
                            <?= htmlspecialchars($sinhVien['TrangThaiNoiTru'] ?? (!empty($hopDong) ? 'Đang ở nội trú' : 'Chưa nội trú')) ?>
                        </span>
                    </div>

                    <?php if (!empty($hopDong)): ?>
                        <table class="info-table">
                            <tr>
                                <td class="info-label"><i class="fa-solid fa-barcode" style="margin-right: 6px;"></i> Mã hợp đồng</td>
                                <td class="info-value"><code style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($hopDong['MaHopDong']) ?></code></td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="fa-solid fa-bed" style="margin-right: 6px;"></i> Phòng & Giường</td>
                                <td class="info-value">
                                    <b>Phòng <?= htmlspecialchars($hopDong['MaPhong']) ?></b> &mdash; Giường: <b><?= htmlspecialchars($hopDong['KyHieuGiuong']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="fa-solid fa-city" style="margin-right: 6px;"></i> Tòa nhà & Cụm</td>
                                <td class="info-value">
                                    <b><?= htmlspecialchars($hopDong['TenToaNha']) ?></b> &bull; <?= htmlspecialchars($hopDong['TenCum']) ?>
                                    <?php if (!empty($hopDong['SoDienThoaiCum'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                            <i class="fa-solid fa-phone" style="font-size: 11px;"></i> Hotline Cụm: <?= htmlspecialchars($hopDong['SoDienThoaiCum']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="fa-solid fa-cubes" style="margin-right: 6px;"></i> Tiêu chuẩn phòng</td>
                                <td class="info-value"><span class="badge badge-info"><?= htmlspecialchars($hopDong['TenLoaiPhong']) ?></span></td>
                            </tr>
                            <tr>
                                <td class="info-label"><i class="fa-regular fa-calendar-check" style="margin-right: 6px;"></i> Hạn lưu trú</td>
                                <td class="info-value">
                                    Từ <?= date('d/m/Y', strtotime($hopDong['NgayBatDau'])) ?> đến <b><?= date('d/m/Y', strtotime($hopDong['NgayKetThuc'])) ?></b>
                                </td>
                            </tr>
                        </table>
                    <?php elseif (!empty($hoSoDangKy)): ?>
                        <div style="padding: 14px; border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                                <b style="color: #1e40af;"><i class="fa-solid fa-file-circle-question"></i> Hồ sơ xét duyệt giữ chỗ</b>
                                <span class="badge badge-warning"><?= htmlspecialchars($hoSoDangKy['TrangThaiHoSo']) ?></span>
                            </div>
                            <div style="font-size: 0.85rem; color: #1e3a8a; line-height: 1.6;">
                                <div>Mã hồ sơ: <b><?= htmlspecialchars($hoSoDangKy['MaHoSo']) ?></b></div>
                                <div>Khu vực đăng ký: <?= htmlspecialchars($hoSoDangKy['TenToaNha'] . ' - Phòng ' . $hoSoDangKy['MaPhong'] . ' - Giường ' . $hoSoDangKy['KyHieuGiuong']) ?></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 20px 0;">
                            <p class="text-muted" style="font-size: 0.875rem; margin-bottom: 12px;">Bạn hiện chưa có hợp đồng nội trú đang hiệu lực.</p>
                            <a href="<?= url('register') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                <i class="fa-solid fa-plus" style="margin-right:8px"></i> Đăng ký giữ chỗ KTX
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- COLUMN 2: CONTACT & EDIT FORM -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <!-- Contact Info & Quick Update Card -->
                <div class="card">
                    <div class="card-title">
                        <span>Thông tin liên lạc</span>
                    </div>

                    <table class="info-table" style="margin-bottom: 18px;">
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-phone" style="margin-right: 6px;"></i> Số điện thoại SV</td>
                            <td class="info-value"><b><?= htmlspecialchars($sinhVien['SoDienThoai'] ?? 'Chưa cập nhật') ?></b></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-envelope" style="margin-right: 6px;"></i> Email cá nhân</td>
                            <td class="info-value"><?= htmlspecialchars($sinhVien['Email'] ?? 'Chưa cập nhật') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-phone-volume" style="margin-right: 6px;"></i> SĐT Phụ huynh</td>
                            <td class="info-value"><?= htmlspecialchars($sinhVien['SDTPhuHuynh'] ?? 'Chưa cập nhật') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-building-columns" style="margin-right: 6px;"></i> STK Ngân hàng</td>
                            <td class="info-value"><code><?= htmlspecialchars($sinhVien['SoTaiKhoanNganHang'] ?? 'Chưa cập nhật') ?></code></td>
                        </tr>
                    </table>
                </div>

            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Thông tin sinh viên - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
