<?php
ob_start();
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Quản trị hệ thống KTX ĐHQG-HCM</h1>
            <p class="page-subtitle">Bảng điều khiển trung tâm toàn bộ Khu A và Khu B</p>
        </div>
        <div>
            <span class="badge badge-info">
                Quyền hạn: Quản trị viên
            </span>
        </div>
    </div>

    <!-- Summary Stats - Clean Minimalist Metrics -->
    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card stat-card">
            <div class="stat-label">Tổng sinh viên</div>
            <div class="stat-val"><?= number_format($stats['total_students']) ?></div>
            <div class="stat-sub">Đã tạo tài khoản</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Hợp đồng hiệu lực</div>
            <div class="stat-val"><?= number_format($stats['active_residents']) ?></div>
            <div class="stat-sub">Đang nội trú</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Hồ sơ chờ duyệt</div>
            <div class="stat-val"><?= number_format($stats['pending_applications']) ?></div>
            <div class="stat-sub">Cần xử lý trong 24h</div>
        </div>
        <div class="card stat-card">
            <div class="stat-label">Phiếu báo hỏng cần sửa</div>
            <div class="stat-val"><?= number_format($stats['pending_maintenance']) ?></div>
            <div class="stat-sub">Chờ kỹ thuật xử lý</div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-title">
            <span>Nhật ký giao dịch thanh toán gần đây</span>
        </div>
        <?php if (!empty($recentTransactions)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã giao dịch</th>
                            <th>Mã hóa đơn</th>
                            <th>Sinh viên</th>
                            <th>Kênh thanh toán</th>
                            <th>Số tiền</th>
                            <th>Thời gian</th>
                            <th>Kết quả</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTransactions as $tx): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($tx['MaGiaoDich']) ?></b></td>
                                <td><?= htmlspecialchars($tx['MaHoaDon']) ?></td>
                                <td><?= htmlspecialchars($tx['HoVaTen']) ?> (<?= htmlspecialchars($tx['MaSinhVien']) ?>)</td>
                                <td><?= htmlspecialchars($tx['KenhThanhToan'] ?? 'Mã QR KTX') ?></td>
                                <td><b><?= format_money($tx['SoTien']) ?></b></td>
                                <td><?= $tx['ThoiGianGiaoDich'] ?></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= htmlspecialchars($tx['KetQuaGiaoDich']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Chưa có giao dịch thanh toán nào được ghi nhận.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản trị hệ thống - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
