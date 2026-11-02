<?php
ob_start();
?>

<div class="container">
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
                <td class="info-value"><?= date('d/m/Y', strtotime($sinhVien['NgaySinh'])) ?></td>
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

    <!-- KHỐI 2: THÔNG TIN LIÊN HỆ & TÀI KHOẢN -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-title">
            <span>Thông tin liên hệ & Tài khoản</span>
        </div>
        <table class="info-table">
            <tr>
                <td class="info-label">Số điện thoại</td>
                <td class="info-value"><b><?= htmlspecialchars($sinhVien['SoDienThoai']) ?></b></td>
            </tr>
            <tr>
                <td class="info-label">Email</td>
                <td class="info-value"><?= htmlspecialchars($sinhVien['Email']) ?></td>
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
            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Mở form cập nhật thông tin liên hệ')">
                ✏️ Cập nhật thông tin liên hệ
            </button>
        </div>
    </div>

    <!-- KHỐI 3: TRẠNG THÁI LƯU TRÚ & CHỖ Ở -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-title">
            <span>Thông tin lưu trú & Chỗ ở</span>
        </div>
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
    </div>

    <!-- KHỐI 4: DỊCH VỤ LƯU TRÚ -->
    <div class="card">
        <div class="card-title">
            <span>Dịch vụ một cửa sinh viên</span>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-outline" style="border-radius: 6px;" onclick="alert('Đơn xin chuyển phòng')">
                🔄 Đơn xin chuyển phòng
            </button>
            <button type="button" class="btn btn-outline" style="border-radius: 6px;" onclick="alert('Báo hỏng thiết bị phòng')">
                🛠️ Báo hỏng thiết bị phòng
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
