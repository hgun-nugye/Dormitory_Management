<?php
ob_start();
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Cổng thông tin sinh viên</h1>
            <p class="page-subtitle"><?= htmlspecialchars($sinhVien['HoVaTen'] ?? '') ?> — MSSV: <?= htmlspecialchars($sinhVien['MaSinhVien'] ?? '') ?> — <?= htmlspecialchars($sinhVien['TenTruong'] ?? '') ?></p>
        </div>
        <div>
            <span class="badge badge-success">
                Trạng thái: <?= htmlspecialchars($sinhVien['TrangThaiNoiTru'] ?? 'Chưa ở') ?>
            </span>
        </div>
    </div>

    <!-- Contract & Room Info -->
    <div class="grid grid-cols-2" style="margin-bottom: 24px;">
        <div class="card">
            <div class="card-title">
                <span>Thông tin chỗ ở hiện tại</span>
                <span class="badge badge-info"><?= $hopDong['TrangThaiHopDong'] ?? 'Chưa có hợp đồng' ?></span>
            </div>
            <?php if ($hopDong): ?>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Mã hợp đồng</td>
                        <td class="info-value"><b><?= htmlspecialchars($hopDong['MaHopDong']) ?></b></td>
                    </tr>
                    <tr>
                        <td class="info-label">Phòng / Giường</td>
                        <td class="info-value">Phòng <?= htmlspecialchars($hopDong['MaPhong']) ?> (Giường <?= htmlspecialchars($hopDong['KyHieuGiuong']) ?>)</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tòa nhà / Cụm</td>
                        <td class="info-value"><?= htmlspecialchars($hopDong['TenToaNha']) ?> — <?= htmlspecialchars($hopDong['TenCum']) ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Loại phòng</td>
                        <td class="info-value"><?= htmlspecialchars($hopDong['TenLoaiPhong']) ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Thời hạn lưu trú</td>
                        <td class="info-value">Từ <?= $hopDong['NgayBatDau'] ?> đến <?= $hopDong['NgayKetThuc'] ?></td>
                    </tr>
                </table>
            <?php else: ?>
                <p class="text-muted">Hiện tại bạn chưa có hợp đồng nội trú đang hiệu lực.</p>
                <a href="#register" class="btn btn-primary" style="margin-top: 14px;">Đăng ký giữ chỗ</a>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">
                <span>Dịch vụ một cửa</span>
            </div>
            <div class="action-list">
                <a href="#" class="action-item">
                    <span class="action-name">Nộp đơn xin chuyển phòng</span>
                    <span class="action-desc">Tự động tính bù trừ chênh lệch đơn giá</span>
                </a>
                <a href="#" class="action-item">
                    <span class="action-name">Báo hỏng thiết bị phòng</span>
                    <span class="action-desc">Kiểm tra và tránh gửi trùng phiếu</span>
                </a>
                <a href="#" class="action-item">
                    <span class="action-name">Hồ sơ miễn giảm lệ phí</span>
                    <span class="action-desc">Áp dụng theo trần phòng chuẩn</span>
                </a>
                <a href="#" class="action-item">
                    <span class="action-name">Khai báo tạm vắng</span>
                    <span class="action-desc">Cập nhật lưu trú tránh vi phạm vắng 30 ngày</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Invoices & Maintenance -->
    <div class="grid grid-cols-2">
        <div class="card">
            <div class="card-title">Hóa đơn lệ phí & dịch vụ</div>
            <?php if (!empty($hoaDons)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã hóa đơn</th>
                                <th>Loại</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hoaDons as $hd): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($hd['MaHoaDon']) ?></b></td>
                                    <td><?= htmlspecialchars($hd['LoaiHoaDon']) ?></td>
                                    <td><?= format_money($hd['SoTienPhaiDong']) ?></td>
                                    <td>
                                        <span class="badge <?= $hd['TrangThai'] === 'Đã thanh toán' ? 'badge-success' : 'badge-danger' ?>">
                                            <?= htmlspecialchars($hd['TrangThai']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Chưa có hóa đơn nào phát sinh.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-title">Phiếu báo hỏng thiết bị</div>
            <?php if (!empty($phieuBaoHong)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Thiết bị</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($phieuBaoHong as $pb): ?>
                                <tr>
                                    <td><b><?= htmlspecialchars($pb['MaPhieu']) ?></b></td>
                                    <td><?= htmlspecialchars($pb['TenThietBi'] ?? 'Chung') ?></td>
                                    <td><?= htmlspecialchars($pb['MoTaHuHong']) ?></td>
                                    <td>
                                        <span class="badge <?= $pb['TrangThaiSua'] === 'Hoàn thành' ? 'badge-success' : 'badge-warning' ?>">
                                            <?= htmlspecialchars($pb['TrangThaiSua']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">Không có phiếu báo hỏng nào.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Cổng sinh viên - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
