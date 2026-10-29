<?php
ob_start();
?>

<div class="container">
    <!-- Header -->
    <div class="page-header" style="margin-bottom: 24px;">
        <div>
            <h1 class="page-title">Quản lý Cụm nhà KTX</h1>
            <p class="page-subtitle">
                Cán bộ phụ trách: <b><?= htmlspecialchars($user['HoVaTen'] ?? 'Cán bộ BQL') ?></b> &bull; Cụm quản lý: <b><?= htmlspecialchars($maCum ?? 'Cụm AG') ?></b>
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            <a href="<?= url('cron/trigger_release') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                ⏱️ Đối soát thu hồi 72h
            </a>
            <a href="<?= url('cron/trigger_absence') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                📋 Quét vắng mặt 30 ngày
            </a>
        </div>
    </div>

    <!-- Thống kê nhanh trong cụm -->
    <div class="grid grid-cols-4" style="gap: 18px; margin-bottom: 24px;">
        <div class="card stat-card" style="border-left: 4px solid #f59e0b;">
            <div class="stat-label">Hồ sơ chờ duyệt</div>
            <div class="stat-val" style="color: #b45309;"><?= count($pendingApplications ?? []) ?></div>
            <div class="stat-sub">Cần xử lý trong hạn 72h</div>
        </div>
        <div class="card stat-card" style="border-left: 4px solid #2563eb;">
            <div class="stat-label">Phòng cần theo dõi</div>
            <div class="stat-val" style="color: #1e40af;"><?= count($consolidation ?? []) ?></div>
            <div class="stat-sub">Phòng lấp đầy dưới 50%</div>
        </div>
        <div class="card stat-card" style="border-left: 4px solid #dc2626;">
            <div class="stat-label">Phiếu báo hỏng thiết bị</div>
            <div class="stat-val" style="color: #b91c1c;"><?= count($maintenanceTickets ?? []) ?></div>
            <div class="stat-sub">Chờ kỹ thuật xử lý</div>
        </div>
        <div class="card stat-card" style="border-left: 4px solid #10b981;">
            <div class="stat-label">Chỉ số điện nước</div>
            <div class="stat-val" style="color: #15803d;">Kỳ 10/2026</div>
            <div class="stat-sub">Đã chốt sổ định kỳ</div>
        </div>
    </div>

    <!-- KHỐI 1: XÉT DUYỆT HỒ SƠ ĐĂNG KÝ CHỖ Ở (TRONG CỤM PHỤ TRÁCH) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>1. Danh sách hồ sơ đăng ký chỗ ở chờ duyệt (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-warning"><?= count($pendingApplications ?? []) ?> hồ sơ</span>
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
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingApplications as $app): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($app['MaHoSo']) ?></b></td>
                                <td><?= htmlspecialchars($app['HoVaTen']) ?> (<?= htmlspecialchars($app['MaSinhVien']) ?>)</td>
                                <td><?= htmlspecialchars($app['TenTruong']) ?></td>
                                <td><?= htmlspecialchars($app['GioiTinh']) ?></td>
                                <td>Phòng <b><?= htmlspecialchars($app['MaPhong']) ?></b> (Giường <?= htmlspecialchars($app['MaGiuong']) ?>)</td>
                                <td><?= $app['ThoiGianNop'] ?></td>
                                <td style="text-align: right;">
                                    <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($app['MaHoSo']) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                            ✓ Duyệt giữ chỗ 72h
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Từ chối hồ sơ')">
                                        ✕ Từ chối
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem;">Hiện không có hồ sơ nào đang chờ duyệt trong cụm <?= htmlspecialchars($maCum ?? 'AG') ?>.</p>
        <?php endif; ?>
    </div>

    <!-- KHỐI 2: TÌNH TRẠNG LƯU TRÚ & ĐỀ XUẤT DỒN PHÒNG (TRONG CỤM) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>2. Theo dõi công suất phòng & Đề xuất dồn phòng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-info"><?= count($consolidation ?? []) ?> phòng</span>
        </div>
        
        <?php if (!empty($consolidation)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã phòng</th>
                            <th>Tòa nhà</th>
                            <th>Loại phòng</th>
                            <th>Đang ở / Sức chứa</th>
                            <th>Tỷ lệ lấp đầy</th>
                            <th>Phương án đề xuất</th>
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
                                        <span style="color: var(--success); font-weight: 500;">Phòng trống, sẵn sàng tiếp nhận hoặc sửa chữa</span>
                                    <?php else: ?>
                                        <span style="color: var(--primary); font-weight: 500;">Đề xuất chuyển <?= $r['SoSVDangO'] ?> SV sang phòng cùng loại</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem;">Tất cả các phòng trong cụm đều đạt công suất tối ưu (&gt; 50%).</p>
        <?php endif; ?>
    </div>

    <!-- KHỐI 3: PHIẾU BÁO HỎNG & SỬA CHỮA THIẾT BỊ PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>3. Xử lý phiếu báo hỏng thiết bị phòng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-danger"><?= count($maintenanceTickets ?? []) ?> phiếu</span>
        </div>
        
        <?php if (!empty($maintenanceTickets)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Phòng / Tòa</th>
                            <th>Thiết bị</th>
                            <th>Mô tả hư hỏng</th>
                            <th>Người báo</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($maintenanceTickets as $mb): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($mb['MaPhieu']) ?></b></td>
                                <td>Phòng <b><?= htmlspecialchars($mb['SoPhong'] ?? $mb['MaPhong']) ?></b> (<?= htmlspecialchars($mb['TenToaNha'] ?? '') ?>)</td>
                                <td><?= htmlspecialchars($mb['TenThietBi'] ?? 'Chung') ?></td>
                                <td><?= htmlspecialchars($mb['MoTaHuHong']) ?></td>
                                <td><?= htmlspecialchars($mb['NguoiBao'] ?? '') ?></td>
                                <td>
                                    <span class="badge badge-warning"><?= htmlspecialchars($mb['TrangThaiSua']) ?></span>
                                </td>
                                <td style="text-align: right;">
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Tiếp nhận xử lý kỹ thuật')">
                                        🛠️ Tiếp nhận xử lý
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem;">Không có sự cố thiết bị nào cần xử lý trong cụm.</p>
        <?php endif; ?>
    </div>

    <!-- KHỐI 4: GHI NHẬN CHỈ SỐ ĐIỆN NƯỚC ĐỊNH KỲ -->
    <div class="card">
        <div class="card-title">
            <span>4. Ghi nhận chỉ số Điện - Nước định kỳ hàng tháng</span>
        </div>
        <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 12px;">
            Nhập số đo công tơ ngày 25 hàng tháng cho các phòng thuộc cụm quản lý:
        </p>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary" style="border-radius: 6px;" onclick="alert('Mở biểu mẫu nhập chỉ số điện nước tháng')">
                ⚡ Nhập chỉ số Điện & Nước tháng này
            </button>
            <button type="button" class="btn btn-outline" style="border-radius: 6px;" onclick="alert('Xuất danh sách chỉ số ra Excel')">
                📥 Xuất bảng chỉ số cụm
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "BQL Cụm nhà - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
