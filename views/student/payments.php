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
            <span style="color: var(--text-primary); font-weight: 500;">Thông tin thanh toán</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Thông tin thanh toán</h1>
                <p class="page-subtitle">Hóa đơn lệ phí, chỉ số điện nước phòng ở và lịch sử giao dịch</p>
            </div>
            <div>
                <a href="<?= url('student/profile') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    👤 Xem thông tin sinh viên &rarr;
                </a>
            </div>
        </div>
    </div>

    <!-- KHỐI 1: DANH SÁCH HÓA ĐƠN -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Hóa đơn cần thanh toán</span>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khoản thu</th>
                        <th>Kỳ thanh toán</th>
                        <th>Số tiền</th>
                        <th>Hạn thanh toán</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($hoaDons as $hd): ?>
                        <tr>
                            <td><b><?= htmlspecialchars($hd['MaHoaDon']) ?></b></td>
                            <td><?= htmlspecialchars($hd['LoaiHoaDon']) ?></td>
                            <td><?= htmlspecialchars($hd['KyThanhToan'] ?? 'Định kỳ') ?></td>
                            <td><b><?= format_money($hd['SoTienPhaiDong']) ?></b></td>
                            <td><?= !empty($hd['HanThanhToan']) ? date('d/m/Y', strtotime($hd['HanThanhToan'])) : '-' ?></td>
                            <td>
                                <?php if ($hd['TrangThai'] === 'Đã thanh toán'): ?>
                                    <span class="badge badge-success">Đã thanh toán</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Chưa thanh toán</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right;">
                                <?php if ($hd['TrangThai'] === 'Chưa thanh toán'): ?>
                                    <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Mở cổng thanh toán hóa đơn <?= $hd['MaHoaDon'] ?>')">
                                        💳 Thanh toán
                                    </button>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">Đã hoàn tất</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 2: CHỈ SỐ ĐIỆN NƯỚC CỦA PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Bảng theo dõi Điện - Nước phòng ở</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kỳ tháng</th>
                        <th>Phòng / Tòa</th>
                        <th>Điện (Cũ - Mới)</th>
                        <th>Tiêu thụ Điện</th>
                        <th>Tiền điện</th>
                        <th>Nước (Cũ - Mới)</th>
                        <th>Tiêu thụ Nước</th>
                        <th>Tiền nước</th>
                        <th>Số người</th>
                        <th>Chia mỗi người</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($chiSoDienNuoc as $cs): 
                        $dienTieuThu = $cs['DienTieuThu'] ?? ($cs['DienMoi'] - $cs['DienCu']);
                        $nuocTieuThu = $cs['NuocTieuThu'] ?? ($cs['NuocMoi'] - $cs['NuocCu']);
                        $tongTien = ($cs['TienDien'] ?? 0) + ($cs['TienNuoc'] ?? 0);
                        $tienMoiSV = $cs['TienMoiSV'] ?? (!empty($cs['SoSVThucOTaiThang']) ? ($tongTien / $cs['SoSVThucOTaiThang']) : 0);
                    ?>
                        <tr>
                            <td><b>Tháng <?= $cs['Thang'] ?>/<?= $cs['Nam'] ?></b></td>
                            <td>Phòng <?= htmlspecialchars($cs['SoPhong'] ?? '304') ?> (<?= htmlspecialchars($cs['TenToaNha'] ?? 'Tòa A01') ?>)</td>
                            <td><?= number_format($cs['DienCu']) ?> &rarr; <?= number_format($cs['DienMoi']) ?></td>
                            <td><span class="badge badge-info"><?= number_format($dienTieuThu) ?> kWh</span></td>
                            <td><?= format_money($cs['TienDien']) ?></td>
                            <td><?= number_format($cs['NuocCu']) ?> &rarr; <?= number_format($cs['NuocMoi']) ?></td>
                            <td><span class="badge badge-info"><?= number_format($nuocTieuThu) ?> m³</span></td>
                            <td><?= format_money($cs['TienNuoc']) ?></td>
                            <td><?= $cs['SoSVThucOTaiThang'] ?> người</td>
                            <td><b style="color: var(--primary);"><?= format_money($tienMoiSV) ?></b></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 3: LỊCH SỬ GIAO DỊCH -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Lịch sử giao dịch thanh toán</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã giao dịch</th>
                        <th>Mã hóa đơn</th>
                        <th>Hình thức thanh toán</th>
                        <th>Thời gian</th>
                        <th>Số tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($giaoDichs as $gd): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($gd['MaGiaoDich']) ?></code></td>
                            <td><b><?= htmlspecialchars($gd['MaHoaDon']) ?></b></td>
                            <td><?= htmlspecialchars($gd['KenhThanhToan']) ?></td>
                            <td><?= htmlspecialchars($gd['ThoiGianGiaoDich']) ?></td>
                            <td><b><?= format_money($gd['SoTien']) ?></b></td>
                            <td>
                                <span class="badge badge-success">
                                    <?= htmlspecialchars($gd['KetQuaGiaoDich']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 4: KHỞI TẠO THANH TOÁN -->
    <div class="card">
        <div class="card-title">
            <span>Cổng thanh toán trực tuyến</span>
        </div>
        <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
            <button type="button" class="btn btn-primary" style="border-radius: 6px;" onclick="alert('Mở cổng quét mã VietQR')">
                📲 Quét mã VietQR thanh toán
            </button>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Nội dung chuyển khoản: <code>KTX [MSSV] [MaHoaDon]</code>
            </span>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
?>
