<?php
ob_start();
?>

<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Ban Quản lý Cụm nhà</h1>
            <p class="page-subtitle">Cán bộ phụ trách: <b><?= htmlspecialchars($user['HoVaTen'] ?? '') ?></b> — Cụm quản lý: <b><?= htmlspecialchars($maCum) ?></b></p>
        </div>
        <div class="header-actions">
            <a href="<?= url('cron/trigger_release') ?>" class="btn btn-outline btn-sm">
                Chạy đối soát thu hồi 72h
            </a>
            <a href="<?= url('cron/trigger_absence') ?>" class="btn btn-outline btn-sm">
                Quét vắng mặt 30 ngày
            </a>
        </div>
    </div>

    <!-- Absence Warnings (Feature 8) -->
    <?php if (!empty($absenceWarnings['warnings'])): ?>
        <div class="alert alert-danger">
            <div class="alert-title">Cảnh báo: Sinh viên vắng mặt liên tục quá 30 ngày không phép</div>
            <ul class="alert-list">
                <?php foreach ($absenceWarnings['warnings'] as $w): ?>
                    <li>
                        MSSV <b><?= htmlspecialchars($w['maSinhVien']) ?></b> (<?= htmlspecialchars($w['hoVaTen']) ?>) — Phòng <b><?= htmlspecialchars($w['maPhong']) ?></b> — Vắng <b><?= $w['soNgayVang'] ?> ngày</b> (SĐT Phụ huynh: <?= htmlspecialchars($w['sdtPhuHuynh']) ?>)
                        — <a href="#" class="alert-action">Lập biên bản báo trường</a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Pending Applications (72h workflow) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Hồ sơ đăng ký chờ duyệt</span>
            <span class="badge badge-warning"><?= count($pendingApplications) ?> hồ sơ</span>
        </div>
        <?php if (!empty($pendingApplications)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã hồ sơ</th>
                            <th>Sinh viên</th>
                            <th>Trường đại học</th>
                            <th>Giới tính</th>
                            <th>Phòng đề xuất</th>
                            <th>Thời gian nộp</th>
                            <th>Xử lý</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingApplications as $app): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($app['MaHoSo']) ?></b></td>
                                <td><?= htmlspecialchars($app['HoVaTen']) ?> (<?= htmlspecialchars($app['MaSinhVien']) ?>)</td>
                                <td><?= htmlspecialchars($app['TenTruong']) ?></td>
                                <td><?= htmlspecialchars($app['GioiTinh']) ?></td>
                                <td><?= htmlspecialchars($app['MaPhong']) ?> (Giường <?= htmlspecialchars($app['MaGiuong']) ?>)</td>
                                <td><?= $app['ThoiGianNop'] ?></td>
                                <td>
                                    <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($app['MaHoSo']) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Duyệt 72h
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Không có hồ sơ nào đang chờ duyệt trong cụm <?= htmlspecialchars($maCum) ?>.</p>
        <?php endif; ?>
    </div>

    <!-- Room Consolidation Suggestion (Feature 3) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Đề xuất dồn phòng (Công suất lưu trú ≤ 50%)</span>
            <span class="badge badge-info"><?= count($consolidation) ?> phòng</span>
        </div>
        <?php if (!empty($consolidation)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Phòng</th>
                            <th>Tòa nhà</th>
                            <th>Loại phòng</th>
                            <th>Đang ở / Sức chứa</th>
                            <th>Tỷ lệ lấp đầy</th>
                            <th>Phương án</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($consolidation as $r): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($r['MaPhong']) ?></b></td>
                                <td><?= htmlspecialchars($r['MaToaNha']) ?></td>
                                <td><?= htmlspecialchars($r['TenLoaiPhong']) ?></td>
                                <td><?= $r['SoSVDangO'] ?> / <?= $r['SoGiuongToiDa'] ?> SV</td>
                                <td>
                                    <span class="badge <?= $r['TyLeLapDay'] == 0 ? 'badge-danger' : 'badge-warning' ?>">
                                        <?= $r['TyLeLapDay'] ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($r['SoSVDangO'] == 0): ?>
                                        <span class="status-success">Phòng trống, sẵn sàng bàn giao sửa chữa</span>
                                    <?php else: ?>
                                        <span class="status-primary">Đề xuất chuyển <?= $r['SoSVDangO'] ?> SV sang phòng cùng loại</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Các phòng trong cụm đều đạt công suất lưu trú tối ưu (&gt; 50%).</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "BQL Cụm nhà - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
