<?php
ob_start();
$hopDong = $hopDong ?? null;
?>

<div class="container">
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <!-- Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;">Trang chủ</a>
            <span>/</span>
            <a href="<?= url('student/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Cổng sinh viên</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Thông tin sinh viên</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Thông tin sinh viên</h1>
                <p class="page-subtitle">Hồ sơ cá nhân, thông tin liên lạc và tình trạng phòng ở nội trú</p>
            </div>
            <div>
                <a href="<?= url('student/payments') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    💳 Xem thông tin thanh toán &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- KHỐI 1: THÔNG TIN CÁ NHÂN & HỌC VỤ -->
    <?php if (empty($sinhVien)): ?>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-title"><span>Thông tin cá nhân & Học vụ</span></div>
            <p class="text-muted">Không tìm thấy hồ sơ sinh viên gắn với tài khoản hiện tại.</p>
        </div>
    <?php else: ?>
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-title">
            <span>Thông tin cá nhân & Học vụ</span>
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Mã sinh viên (MSSV)</td>
                <td class="info-value"><b><?= htmlspecialchars($sinhVien['MaSinhVien']) ?></b></td>
            </tr>
            <tr>
                <td class="info-label">Họ và tên</td>
                <td class="info-value"><b><?= htmlspecialchars($sinhVien['HoVaTen']) ?></b></td>
            </tr>
            <tr>
                <td class="info-label">Ngày sinh</td>
                <td class="info-value"><?= !empty($sinhVien['NgaySinh']) ? date('d/m/Y', strtotime($sinhVien['NgaySinh'])) : 'Chưa cập nhật' ?></td>
            </tr>
            <tr>
                <td class="info-label">Giới tính</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['GioiTinh']) ?></td>
            </tr>
            <tr>
                <td class="info-label">Số CCCD</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['SoCCCD']) ?></td>
            </tr>
            <tr>
                <td class="info-label">Trường đại học</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['TenTruong']) ?></td>
            </tr>
            <tr>
                <td class="info-label">Khóa tuyển sinh</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['KhoaTuyenSinh']) ?></td>
            </tr>
        </table>
    </div>
    <?php endif; ?>

    <!-- KHỐI 2: THÔNG TIN LIÊN HỆ & TÀI KHOẢN -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-title">
            <span>Thông tin liên hệ & Tài khoản</span>
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Số điện thoại</td>
                <td class="info-value"><b><?= htmlspecialchars($sinhVien['SoDienThoai'] ?? 'Chưa cập nhật') ?></b></td>
            </tr>
            <tr>
                <td class="info-label">Email</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['Email'] ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
                <td class="info-label">Số ĐT phụ huynh</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['SDTPhuHuynh'] ?? 'Chưa cập nhật') ?></td>
            </tr>
            <tr>
                <td class="info-label">Số tài khoản ngân hàng</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['SoTaiKhoanNganHang'] ?? 'Chưa cập nhật') ?></td>
            </tr>
        </table>
        <div style="margin-top: 14px;">
            <form method="post" action="<?= url('student/profile/update') ?>" class="grid grid-cols-2" style="gap:12px;align-items:end;">
                <?= csrf_field() ?>
                <label>Số điện thoại
                    <input class="form-control" name="SoDienThoai" value="<?= htmlspecialchars($sinhVien['SoDienThoai'] ?? '') ?>" required>
                </label>
                <label>Email
                    <input class="form-control" type="email" name="Email" value="<?= htmlspecialchars($sinhVien['Email'] ?? '') ?>" required>
                </label>
                <button type="submit" class="btn btn-outline btn-sm" style="border-radius:6px;width:max-content;">✏️ Lưu thông tin liên hệ</button>
            </form>
        </div>
    </div>

    <!-- KHỐI 3: TRẠNG THÁI LƯU TRÚ & CHỖ Ở -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-title">
            <span>Thông tin lưu trú & Chỗ ở</span>
        </div>
        <?php if (!$hopDong): ?>
        <?php if (!empty($hoSoDangKy)): ?>
        <div style="padding: 12px; border: 1px solid #dbeafe; border-radius: 8px; background: #eff6ff;">
            <p style="margin: 0 0 8px;"><b>Hồ sơ đang xử lý</b></p>
            <p style="margin: 0 0 6px;">Mã hồ sơ: <b><?= htmlspecialchars($hoSoDangKy['MaHoSo']) ?></b></p>
            <p style="margin: 0 0 6px;">Trạng thái: <b><?= htmlspecialchars($hoSoDangKy['TrangThaiHoSo']) ?></b></p>
            <p style="margin: 0;">Chỗ đăng ký: <?= htmlspecialchars($hoSoDangKy['TenToaNha'] . ' - Phòng ' . $hoSoDangKy['MaPhong'] . ' - Giường ' . $hoSoDangKy['KyHieuGiuong']) ?></p>
        </div>
        <?php else: ?>
        <p class="text-muted">Tài khoản chưa có hợp đồng nội trú đang hiệu lực.</p>
        <?php endif; ?>
        <?php else: ?>
        <table class="info-table">
            <tr>
                <td class="info-label">Trạng thái lưu trú</td>
                <td class="info-value"><span class="badge badge-success">● <?= htmlspecialchars($sinhVien['TrangThaiNoiTru'] ?? 'Đang ở nội trú') ?></span></td>
            </tr>
            <tr>
                <td class="info-label">Phòng ở & Giường</td>
                <td class="info-value"><b>Phòng <?= htmlspecialchars($hopDong['MaPhong']) ?></b> &mdash; Giường: <b><?= htmlspecialchars($hopDong['KyHieuGiuong']) ?></b></td>
            </tr>
            <tr>
                <td class="info-label">Tòa nhà / Cụm nhà</td>
                <td class="info-value"><?= htmlspecialchars($hopDong['TenToaNha']) ?> &bull; <?= htmlspecialchars($hopDong['TenCum']) ?> (ĐT: <?= htmlspecialchars($hopDong['SoDienThoaiCum']) ?>)</td>
            </tr>
            <tr>
                <td class="info-label">Thời hạn lưu trú</td>
                <td class="info-value">Từ <?= date('d/m/Y', strtotime($hopDong['NgayBatDau'])) ?> đến <?= date('d/m/Y', strtotime($hopDong['NgayKetThuc'])) ?></td>
            </tr>
        </table>
        <?php endif; ?>
    </div>

    <!-- KHỐI 4: DỊCH VỤ LƯU TRÚ -->
    <div class="card">
        <div class="card-title">
            <span>Dịch vụ một cửa sinh viên</span>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="<?= url('student/requests/transfer') ?>" class="btn btn-outline" style="border-radius: 6px;">
                🔄 Đơn xin chuyển phòng
            </a>
            <a href="<?= url('student/requests/repair') ?>" class="btn btn-outline" style="border-radius: 6px;">
                🛠️ Báo hỏng thiết bị phòng
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
