<?php
ob_start();
$user = $user ?? \App\Core\Auth::user();
$sinhVien = $sinhVien ?? [];
$hopDong = $hopDong ?? null;
$hoSoDangKy = $hoSoDangKy ?? null;
$hoaDons = $hoaDons ?? [];
$phieuBaoHong = $phieuBaoHong ?? [];

// Calculate unpaid bills and days remaining in contract
$unpaidCount = 0;
$totalUnpaid = 0;
foreach ($hoaDons as $hd) {
    if (($hd['TrangThai'] ?? '') !== 'Đã thanh toán') {
        $unpaidCount++;
        $totalUnpaid += (int) ($hd['SoTienPhaiDong'] ?? 0);
    }
}

$daysRemaining = null;
if (!empty($hopDong['NgayKetThuc'])) {
    $now = new DateTime();
    $endDate = new DateTime($hopDong['NgayKetThuc']);
    $diff = $now->diff($endDate);
    $daysRemaining = $endDate >= $now ? $diff->days : - ($diff->days);
}
?>

<div class="container student-dashboard">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;">Cổng thông tin sinh viên</span>
    </div>

    <!-- Student Welcome Banner -->
    <div class="card student-welcome" style="margin-bottom: 24px; background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);  box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
            <div style="display: flex; align-items: center; gap: 18px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 26px; border: 2px solid #bfdbfe; flex-shrink: 0;">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <h1 style="font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                            <?= htmlspecialchars($sinhVien['HoVaTen'] ?? ($user['HoVaTen'] ?? 'Sinh viên')) ?>
                        </h1>
                        <span class="badge <?= !empty($hopDong) ? 'badge-success' : (!empty($hoSoDangKy) ? 'badge-warning' : 'badge-danger') ?>">
                            <i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 4px;"></i>
                            <?= htmlspecialchars($sinhVien['TrangThaiNoiTru'] ?? (!empty($hopDong) ? 'Đang ở nội trú' : 'Chưa nội trú')) ?>
                        </span>
                    </div>
                    <div style="color: var(--text-secondary); font-size: 0.875rem; margin-top: 4px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                        <span><b>MSSV:</b> <?= htmlspecialchars($sinhVien['MaSinhVien'] ?? ($user['profile']['MaSinhVien'] ?? 'N/A')) ?></span>

                        <span>•</span>

                        <span><?= htmlspecialchars($sinhVien['TenTruong'] ?? 'Đại học Quốc gia TP.HCM') ?></span>

                        <?php if (!empty($sinhVien['KhoaTuyenSinh'])): ?>
                            <span>•</span>
                            <span>Khóa <?= htmlspecialchars($sinhVien['KhoaTuyenSinh']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 KPI Stat Cards -->
    <div class="grid grid-cols-4 student-kpis" style="margin-bottom: 24px;">
        <!-- Stat 1: Chỗ ở -->
        <div class="card stat-card" style="border-top: 3px solid #3b82f6;">
            <span class="stat-label"><i class="fa-solid fa-bed" style="color: #3b82f6; margin-right: 6px;"></i> Phòng & Giường</span>
            <div class="stat-val" style="font-size: 1.4rem;">
                <?php if (!empty($hopDong)): ?>
                    P.<?= htmlspecialchars($hopDong['MaPhong']) ?> <span style="font-size: 0.95rem; color: var(--text-muted); font-weight: 500;">/ <?= htmlspecialchars($hopDong['KyHieuGiuong']) ?></span>
                <?php else: ?>
                    <span style="font-size: 1.1rem; color: var(--text-muted); font-weight: 500;">Chưa có chỗ</span>
                <?php endif; ?>
            </div>
            <span class="stat-sub">
                <?= !empty($hopDong) ? htmlspecialchars($hopDong['TenToaNha'] . ' - ' . $hopDong['TenCum']) : 'Vui lòng đăng ký giữ chỗ' ?>
            </span>
        </div>

        <!-- Stat 2: Hợp đồng -->
        <div class="card stat-card" style="border-top: 3px solid #10b981;">
            <span class="stat-label"><i class="fa-solid fa-file-contract" style="color: #10b981; margin-right: 6px;"></i> Hợp đồng lưu trú</span>
            <div class="stat-val" style="font-size: 1.4rem; color: #10b981;">
                <?php if (!empty($hopDong)): ?>
                    <?= htmlspecialchars($hopDong['TrangThaiHopDong'] ?? 'Hiệu lực') ?>
                <?php elseif (!empty($hoSoDangKy)): ?>
                    <span style="font-size: 1.1rem; color: #f59e0b;"><?= htmlspecialchars($hoSoDangKy['TrangThaiHoSo']) ?></span>
                <?php else: ?>
                    <span style="font-size: 1.1rem; color: var(--text-muted); font-weight: 500;">Chưa đăng ký</span>
                <?php endif; ?>
            </div>
            <span class="stat-sub">
                <?php if ($daysRemaining !== null): ?>
                    <?= $daysRemaining >= 0 ? "Còn $daysRemaining ngày lưu trú" : "Đã hết hạn $daysRemaining ngày" ?>
                <?php else: ?>
                    Theo dõi quy trình xét duyệt
                <?php endif; ?>
            </span>
        </div>

        <!-- Stat 3: Công nợ lệ phí -->
        <div class="card stat-card" style="border-top: 3px solid <?= $unpaidCount > 0 ? '#ef4444' : '#10b981' ?>;">
            <span class="stat-label"><i class="fa-solid fa-receipt" style="color: <?= $unpaidCount > 0 ? '#ef4444' : '#10b981' ?>; margin-right: 6px;"></i> Lệ phí cần nộp</span>
            <div class="stat-val" style="font-size: 1.4rem; color: <?= $unpaidCount > 0 ? '#b91c1c' : '#15803d' ?>;">
                <?= $unpaidCount > 0 ? format_money($totalUnpaid) : '0 đ' ?>
            </div>
            <span class="stat-sub">
                <?= $unpaidCount > 0 ? "$unpaidCount hóa đơn chưa thanh toán" : 'Đã hoàn thành mọi khoản thu' ?>
            </span>
        </div>

        <!-- Stat 4: Yêu cầu & Báo hỏng -->
        <div class="card stat-card" style="border-top: 3px solid #8b5cf6;">
            <span class="stat-label"><i class="fa-solid fa-screwdriver-wrench" style="color: #8b5cf6; margin-right: 6px;"></i> Báo hỏng & Sự cố</span>
            <div class="stat-val" style="font-size: 1.4rem; color: #7c3aed;">
                <?= count($phieuBaoHong) ?>
            </div>
            <span class="stat-sub">
                Phiếu kỹ thuật đã ghi nhận
            </span>
        </div>
    </div>

    <!-- Main Content 2 Columns -->
    <div class="grid grid-cols-2" style="margin-bottom: 24px;">
        <!-- COLUMN 1: ROOM & INVOICES -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Room & Contract Details -->
            <div class="card">
                <div class="card-title">
                    <span>Thông tin chỗ ở hiện tại</span>
                    <span class="badge <?= !empty($hopDong) ? 'badge-success' : 'badge-warning' ?>">
                        <?= $hopDong['TrangThaiHopDong'] ?? 'Chưa có hợp đồng' ?>
                    </span>
                </div>

                <?php if (!empty($hopDong)): ?>
                    <table class="info-table">
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-barcode" style="margin-right: 6px;"></i> Mã hợp đồng</td>
                            <td class="info-value"><code style="font-weight: 700; color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($hopDong['MaHopDong']) ?></code></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-door-closed" style="margin-right: 6px;"></i> Phòng & Giường</td>
                            <td class="info-value"><b>Phòng <?= htmlspecialchars($hopDong['MaPhong']) ?></b> &mdash; Giường <b><?= htmlspecialchars($hopDong['KyHieuGiuong']) ?></b></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-city" style="margin-right: 6px;"></i> Tòa nhà & Cụm</td>
                            <td class="info-value"><b><?= htmlspecialchars($hopDong['TenToaNha']) ?></b> &bull; <?= htmlspecialchars($hopDong['TenCum']) ?></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-shapes" style="margin-right: 6px;"></i> Loại phòng</td>
                            <td class="info-value"><span class="badge badge-info"><?= htmlspecialchars($hopDong['TenLoaiPhong']) ?></span></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-regular fa-calendar-check" style="margin-right: 6px;"></i> Thời hạn lưu trú</td>
                            <td class="info-value">
                                <?= date('d/m/Y', strtotime($hopDong['NgayBatDau'])) ?> &rarr; <b><?= date('d/m/Y', strtotime($hopDong['NgayKetThuc'])) ?></b>
                            </td>
                        </tr>
                    </table>

                    <div style="margin-top: 16px; padding: 12px 14px; background: #f8fafc; border: 1px dashed var(--border); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 0.85rem; color: var(--text-secondary);">
                            <i class="fa-solid fa-circle-info" style="color: var(--primary); margin-right: 4px;"></i> Hỗ trợ sinh viên nội trú
                        </span>
                        <a href="<?= url('student/requests/transfer') ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-right-left" style="margin-right: 8px;"></i> Xin chuyển phòng
                        </a>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 24px 16px;">
                        <h4 style="font-size: 1rem; color: var(--text-primary); margin-bottom: 6px;">Bạn chưa có chỗ ở chính thức</h4>
                        <?php if (!empty($hoSoDangKy)): ?>
                            <div style="padding: 14px; border: 1px solid #bfdbfe; border-radius: 8px; background: #eff6ff; text-align: left; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="font-weight: 700; color: #1e40af;"><i class="fa-solid fa-clock-rotate-left"></i> Hồ sơ đăng ký đang xử lý</span>
                                    <span class="badge badge-warning"><?= htmlspecialchars($hoSoDangKy['TrangThaiHoSo']) ?></span>
                                </div>
                                <div style="font-size: 0.85rem; color: #1e3a8a; line-height: 1.6;">
                                    <div><b>Mã hồ sơ:</b> <code><?= htmlspecialchars($hoSoDangKy['MaHoSo']) ?></code></div>
                                    <div><b>Khu vực đăng ký:</b> <?= htmlspecialchars($hoSoDangKy['TenToaNha'] . ' - Phòng ' . $hoSoDangKy['MaPhong'] . ' (Giường ' . $hoSoDangKy['KyHieuGiuong'] . ')') ?></div>
                                    <div style="margin-top: 6px; font-size: 0.8rem; color: #3b82f6;"><i class="fa-solid fa-shield-halved"></i> Ban quản trị KTX xét duyệt tự động trong vòng 72 giờ làm việc.</div>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= url('register') ?>" class="btn btn-primary" style="border-radius: 8px;">
                                Đăng ký giữ chỗ KTX ngay
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Invoices List -->
            <div class="card">
                <div class="card-title">
                    <span>Hóa đơn lệ phí & dịch vụ</span>
                    <a href="<?= url('student/payments') ?>" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 500;">
                        Xem tất cả &rarr;
                    </a>
                </div>

                <?php if (!empty($hoaDons)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã HĐ</th>
                                    <th>Khoản thu</th>
                                    <th>Số tiền</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($hoaDons, 0, 4) as $hd): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($hd['MaHoaDon']) ?></code></td>
                                        <td>
                                            <b><?= htmlspecialchars($hd['LoaiHoaDon']) ?></b>
                                            <?php if (!empty($hd['KyThanhToan'])): ?>
                                                <div style="font-size: 0.75rem; color: var(--text-muted);">Kỳ: <?= htmlspecialchars($hd['KyThanhToan']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><b><?= format_money($hd['SoTienPhaiDong']) ?></b></td>
                                        <td>
                                            <span class="badge <?= ($hd['TrangThai'] ?? '') === 'Đã thanh toán' ? 'badge-success' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($hd['TrangThai']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 18px 0; font-size: 0.875rem;">
                        <i class="fa-regular fa-circle-check" style="color: #10b981; margin-right: 6px;"></i> Bạn không có hóa đơn nào phát sinh hoặc chưa thanh toán.
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- COLUMN 2: SERVICES & REPAIRS -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <!-- Quick Actions Service -->
            <div class="card">
                <div class="card-title">
                    <span>Dịch vụ một cửa trực tuyến</span>
                    <a href="<?= url('student/requests') ?>" style="font-size: 0.8rem; color: var(--primary); text-decoration: none; font-weight: 500;">
                        Xem tất cả &rarr;
                    </a>
                </div>

                <div class="action-list">
                    <a href="<?= url('student/requests/transfer') ?>" class="action-item" style="display: flex; flex-direction: row; align-items: center; gap: 14px; padding: 12px 14px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                            <i class="fa-solid fa-right-left"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="action-name">Nộp đơn xin chuyển phòng</div>
                            <div class="action-desc">Tự động kiểm tra đối chiếu giường trống và bù trừ chênh lệch đơn giá</div>
                        </div>
                        <i class="fa-solid fa-angle-right" style="color: var(--text-muted); font-size: 14px;"></i>
                    </a>

                    <a href="<?= url('student/requests/repair') ?>" class="action-item" style="display: flex; flex-direction: row; align-items: center; gap: 14px; padding: 12px 14px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                            <i class="fa-solid fa-screwdriver-wrench"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="action-name">Báo hỏng thiết bị phòng ở</div>
                            <div class="action-desc">Phản ánh sự cố điện nước, bóng đèn, quạt trần, khóa cửa cho kỹ thuật</div>
                        </div>
                        <i class="fa-solid fa-angle-right" style="color: var(--text-muted); font-size: 14px;"></i>
                    </a>

                    <a href="<?= url('student/requests/extension') ?>" class="action-item" style="display: flex; flex-direction: row; align-items: center; gap: 14px; padding: 12px 14px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                            <i class="fa-solid fa-file-circle-check"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="action-name">Hồ sơ miễn giảm lệ phí phòng</div>
                            <div class="action-desc">Gửi minh chứng diện ưu tiên chính sách hoặc hỗ trợ hoàn cảnh khó khăn</div>
                        </div>
                        <i class="fa-solid fa-angle-right" style="color: var(--text-muted); font-size: 14px;"></i>
                    </a>

                    <a href="<?= url('student/requests/leave') ?>" class="action-item" style="display: flex; flex-direction: row; align-items: center; gap: 14px; padding: 12px 14px;">
                        <div style="width: 38px; height: 38px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;">
                            <i class="fa-solid fa-person-walking-arrow-right"></i>
                        </div>
                        <div style="flex: 1;">
                            <div class="action-name">Khai báo tạm vắng lưu trú</div>
                            <div class="action-desc">Cập nhật vắng mặt theo quy định nội trú để tránh cảnh báo an ninh 30 ngày</div>
                        </div>
                        <i class="fa-solid fa-angle-right" style="color: var(--text-muted); font-size: 14px;"></i>
                    </a>
                </div>
            </div>

            <!-- Maintenance & Repair Tickets -->
            <div class="card">
                <div class="card-title">
                    <span>Phiếu báo hỏng kỹ thuật</span>
                    <a href="<?= url('student/requests/repair') ?>" class="btn btn-outline btn-sm" style="font-size: 0.75rem; border-radius: 6px;">
                        <i class="fa-solid fa-plus" style="margin-right:8px"></i> Báo hỏng mới
                    </a>
                </div>

                <?php if (!empty($phieuBaoHong)): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Mã phiếu</th>
                                    <th>Thiết bị</th>
                                    <th>Mô tả sự cố</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($phieuBaoHong, 0, 4) as $pb): ?>
                                    <tr>
                                        <td><code><?= htmlspecialchars($pb['MaPhieu']) ?></code></td>
                                        <td><b><?= htmlspecialchars($pb['TenThietBi'] ?? 'Chung trong phòng') ?></b></td>
                                        <td style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            <?= htmlspecialchars($pb['MoTaHuHong']) ?>
                                        </td>
                                        <td>
                                            <?php
                                            $stt = $pb['TrangThaiSua'] ?? '';
                                            $sttBadge = 'badge-warning';
                                            if ($stt === 'Hoàn thành') $sttBadge = 'badge-success';
                                            elseif ($stt === 'Đang xử lý') $sttBadge = 'badge-info';
                                            elseif ($stt === 'Từ chối') $sttBadge = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $sttBadge ?>">
                                                <?= htmlspecialchars($stt) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted" style="text-align: center; padding: 18px 0; font-size: 0.875rem;">
                        Không có phiếu sự cố nào cần xử lý. Thiết bị phòng đang vận hành tốt.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Cổng sinh viên - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
