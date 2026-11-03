<?php
ob_start();

$currentMonth = $thang ?? (int)date('n');
$currentYear = $nam ?? (int)date('Y');
$roomList = $rooms ?? [];
$readingList = $readings ?? [];
$invoiceList = $invoices ?? [];
$meta = $roomMetadata ?? [];
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Ghi nhận Điện Nước & Quản lý Hóa đơn Công nợ</span>
        </div>

        <div class="page-header" style="align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 class="page-title">Ghi nhận Điện Nước & Quản lý Công nợ</h1>
                <p class="page-subtitle">
                    <strong>Phân hệ Điện Nước:</strong> Tính tiền điện theo <strong>biểu giá bậc thang 6 bậc EVN</strong>, phát hiện bất thường &gt; 3 lần và đối soát gạch nợ mô phỏng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>).
                </p>
            </div>

            <!-- Bộ lọc chọn Kỳ ghi nhận Tháng / Năm -->
            <form method="GET" action="<?= url('staff/utilities') ?>" style="display: flex; gap: 8px; align-items: center; background: #ffffff; padding: 6px 12px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                <label style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted);">Kỳ:</label>
                <select name="thang" class="form-control" style="padding: 5px 8px; font-size: 0.85rem; border-radius: 4px; border: 1px solid #cbd5e1;">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m === $currentMonth ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                    <?php endfor; ?>
                </select>
                <select name="nam" class="form-control" style="padding: 5px 8px; font-size: 0.85rem; border-radius: 4px; border: 1px solid #cbd5e1;">
                    <?php for ($y = 2024; $y <= 2028; $y++): ?>
                        <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>>Năm <?= $y ?></option>
                    <?php endfor; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 4px; padding: 5px 12px;">
                    Xem kỳ này
                </button>
            </form>
        </div>

        <!-- Thanh phím tắt chuyển nhanh các phân hệ nghiệp vụ Staff -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;">
            <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📊 Tổng quan BQL</a>
            <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🏢 1. Sơ đồ Phòng & Cư trú</a>
            <a href="<?= url('staff/applications') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($success)): ?>
        <div class="card" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem; font-weight: bold;">✓</span>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="card" style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem; font-weight: bold;">✕</span>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & BIỂU GIÁ (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #1e40af; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>⚡ Biểu giá Điện bậc thang EVN & Quy tắc phát hiện rò rỉ (Mục 1, 3, 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #1e3a8a; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Biểu giá điện 6 bậc chuẩn EVN:</strong> Bậc 1 (0-50 kWh): 1.806 đ &bull; Bậc 2 (51-100 kWh): 1.866 đ &bull; Bậc 3 (101-200 kWh): 2.167 đ &bull; Bậc 4 (201-300 kWh): 2.729 đ &bull; Bậc 5 (301-400 kWh): 3.050 đ &bull; Bậc 6 (&gt;400 kWh): 3.151 đ.</li>
            <li><strong>Đơn giá nước sạch KTX:</strong> 12.500 đ/m³.</li>
            <li><strong>Cảnh báo bất thường (Smart Anomaly):</strong> Nếu mức tiêu thụ điện hoặc nước tăng vọt gấp &gt;= 3 lần trung bình 3 tháng gần nhất, hệ thống tự động gắn cờ <code>CanhBaoBatThuong = 1</code> để nhân viên kỹ thuật kiểm tra rò rỉ hoặc chập điện trước khi phát hành hóa đơn.</li>
            <li><strong>Đối soát gạch nợ mô phỏng:</strong> Cho phép cán bộ nhấn <em>"Xác nhận thanh toán mô phỏng"</em> để kiểm thử luồng gạch nợ tức thì mà không cần tích hợp cổng thanh toán ngân hàng thật.</li>
        </ul>
    </div>

    <!-- KHỐI 1: BIỂU MẪU NHẬP CHỈ SỐ ĐIỆN NƯỚC THEO PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <span>Biểu mẫu ghi nhận chỉ số công tơ Điện & Nước (Kỳ tháng <?= $currentMonth ?>/<?= $currentYear ?>)</span>
            <span class="badge badge-info">Bảng: ChiSoDienNuoc</span>
        </div>

        <form action="<?= url('staff/utilities/save-reading') ?>" method="POST" id="formSaveReading" onsubmit="return validateReadingForm();">
            <input type="hidden" name="thang" value="<?= $currentMonth ?>">
            <input type="hidden" name="nam" value="<?= $currentYear ?>">

            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 16px;">
                <!-- 1. Chọn phòng -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn phòng ghi số:</label>
                    <select name="maPhong" id="selectPhong" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;" required onchange="onRoomChanged(this.value)">
                        <option value="">-- Chọn phòng --</option>
                        <?php foreach ($roomList as $rm): ?>
                            <option value="<?= htmlspecialchars($rm['MaPhong']) ?>">
                                Phòng <?= htmlspecialchars($rm['SoPhong']) ?> (<?= htmlspecialchars($rm['TenToaNha']) ?> - <?= (int)$rm['SoSVDangO'] ?> SV)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2. Chỉ số Điện cũ / Mới -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chỉ số Điện (kWh) Cũ / Mới:</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="number" name="dienCu" id="inputDienCu" class="form-control" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; background: #f1f5f9;" readonly placeholder="Điện cũ" title="Chỉ số điện kỳ trước">
                        <input type="number" name="dienMoi" id="inputDienMoi" class="form-control" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;" placeholder="Điện mới" required oninput="calculatePreview()">
                    </div>
                    <div id="previewDien" style="font-size: 0.75rem; color: #0369a1; margin-top: 4px; height: 16px;"></div>
                </div>

                <!-- 3. Chỉ số Nước cũ / Mới -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chỉ số Nước (m³) Cũ / Mới:</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="number" name="nuocCu" id="inputNuocCu" class="form-control" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; background: #f1f5f9;" readonly placeholder="Nước cũ" title="Chỉ số nước kỳ trước">
                        <input type="number" name="nuocMoi" id="inputNuocMoi" class="form-control" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;" placeholder="Nước mới" required oninput="calculatePreview()">
                    </div>
                    <div id="previewNuoc" style="font-size: 0.75rem; color: #0369a1; margin-top: 4px; height: 16px;"></div>
                </div>

                <!-- 4. Sĩ số thực tế -->
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Sĩ số SV đang ở:</label>
                    <input type="number" name="soSV" id="inputSoSV" class="form-control" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;" value="1" min="1" required oninput="calculatePreview()">
                    <div id="previewTongTien" style="font-size: 0.75rem; color: #166534; font-weight: 600; margin-top: 4px; height: 16px;"></div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; font-weight: 500;">
                    💾 Lưu chỉ số & Kiểm tra bất thường
                </button>
            </div>
        </form>
    </div>

    <!-- KHỐI 2: BẢNG THEO DÕI CHỈ SỐ VÀ CẢNH BÁO BẤT THƯỜNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div>
                <span>Danh sách Số đo & Tiêu thụ Điện Nước (Kỳ tháng <?= $currentMonth ?>/<?= $currentYear ?> - Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
                <span class="badge" style="background: #e0f2fe; color: #0369a1; margin-left: 8px;">Đã ghi: <?= count($readingList) ?> phòng</span>
            </div>

            <!-- Nút phát hành hóa đơn toàn Cụm -->
            <form method="POST" action="<?= url('staff/utilities/publish-cluster') ?>" style="display: inline;"
                  onsubmit="return confirm('Bạn có chắc chắn muốn chốt sổ và phát hành hóa đơn điện nước tháng <?= $currentMonth ?>/<?= $currentYear ?> cho tất cả phòng trong Cụm <?= htmlspecialchars($maCum ?? 'AG') ?> không?');">
                <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                <input type="hidden" name="nam" value="<?= $currentYear ?>">
                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                    📢 Chốt sổ & Phát hành Hóa đơn Cụm
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Phòng</th>
                        <th>Điện cũ &rarr; Mới</th>
                        <th>Tiêu thụ Điện</th>
                        <th>Nước cũ &rarr; Mới</th>
                        <th>Tiêu thụ Nước</th>
                        <th>Sĩ số</th>
                        <th>Tiền điện / Nước</th>
                        <th>Tổng tiền</th>
                        <th>Cảnh báo bất thường</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($readingList)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 6px;">⚡</div>
                                <div>Chưa có dữ liệu chỉ số điện nước nào được ghi cho kỳ tháng <b><?= $currentMonth ?>/<?= $currentYear ?></b>.</div>
                                <div style="font-size: 0.8rem; margin-top: 4px;">Sử dụng biểu mẫu bên trên để nhập chỉ số công tơ cho từng phòng.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($readingList as $r): ?>
                            <?php
                            $dienKwh = max(0, (int)$r['DienMoi'] - (int)$r['DienCu']);
                            $nuocM3 = max(0, (int)$r['NuocMoi'] - (int)$r['NuocCu']);
                            $tongTien = (float)$r['TienDien'] + (float)$r['TienNuoc'];
                            $isAnomaly = ((int)$r['CanhBaoBatThuong'] === 1);
                            $hasInvoice = !empty($r['MaHoaDon']);
                            ?>
                            <tr style="<?= $isAnomaly ? 'background: #fffbeb;' : '' ?>">
                                <td>
                                    <b><?= htmlspecialchars($r['MaPhong']) ?></b>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($r['TenToaNha'] ?? '') ?></div>
                                </td>
                                <td><?= number_format($r['DienCu']) ?> &rarr; <?= number_format($r['DienMoi']) ?></td>
                                <td>
                                    <b style="<?= $isAnomaly ? 'color: #b91c1c;' : 'color: #0284c7;' ?>">
                                        <?= number_format($dienKwh) ?> kWh
                                    </b>
                                </td>
                                <td><?= number_format($r['NuocCu']) ?> &rarr; <?= number_format($r['NuocMoi']) ?></td>
                                <td>
                                    <b style="<?= $isAnomaly ? 'color: #b91c1c;' : 'color: #0d9488;' ?>">
                                        <?= number_format($nuocM3) ?> m³
                                    </b>
                                </td>
                                <td><?= (int)$r['SoSVThucOTaiThang'] ?> SV</td>
                                <td style="font-size: 0.8rem; line-height: 1.4;">
                                    Điện: <?= number_format($r['TienDien'], 0, ',', '.') ?> đ <br>
                                    Nước: <?= number_format($r['TienNuoc'], 0, ',', '.') ?> đ
                                </td>
                                <td>
                                    <b style="color: #15803d; font-size: 0.9rem;">
                                        <?= number_format($tongTien, 0, ',', '.') ?> đ
                                    </b>
                                </td>
                                <td>
                                    <?php if ($isAnomaly): ?>
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                            ⚠️ Tăng đột biến &gt;3 lần!
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                                            ✓ Bình thường
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($hasInvoice): ?>
                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd;" title="Mã HĐ: <?= htmlspecialchars($r['MaHoaDon']) ?>">
                                            Đã phát hành HĐ
                                        </span>
                                    <?php else: ?>
                                        <form method="POST" action="<?= url('staff/utilities/publish-invoice') ?>" style="display: inline;"
                                              onsubmit="return confirm('Xác nhận phát hành hóa đơn điện nước cho phòng <?= htmlspecialchars($r['MaPhong']) ?>?');">
                                            <input type="hidden" name="maPhong" value="<?= htmlspecialchars($r['MaPhong']) ?>">
                                            <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                                            <input type="hidden" name="nam" value="<?= $currentYear ?>">
                                            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                                                📄 Phát hành HĐ
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 3: THEO DÕI HÓA ĐƠN & ĐỐI SOÁT CÔNG NỢ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <span>Theo dõi Hóa đơn Công nợ Điện Nước & Gạch nợ Mô phỏng</span>
                <span class="badge badge-info">Bảng: HoaDon + GiaoDichThanhToan</span>
            </div>
            <span style="font-size: 0.85rem; color: var(--text-muted);">
                Hiển thị <?= count($invoiceList) ?> hóa đơn gần nhất
            </span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 160px;">Mã Hóa Đơn</th>
                        <th>Phòng / Đại diện</th>
                        <th>Loại hóa đơn</th>
                        <th>Số tiền phải đóng</th>
                        <th>Ngày tạo</th>
                        <th>Hạn thanh toán</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right; min-width: 220px;">Thao tác BQL</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($invoiceList)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                                <div style="font-size: 2rem; margin-bottom: 6px;">🧾</div>
                                <div>Chưa có hóa đơn điện nước nào được phát hành trong Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($invoiceList as $inv): ?>
                            <?php $isPaid = ($inv['TrangThai'] === 'Đã thanh toán'); ?>
                            <tr>
                                <td>
                                    <b style="font-family: monospace; color: var(--primary);">
                                        <?= htmlspecialchars($inv['MaHoaDon']) ?>
                                    </b>
                                </td>
                                <td>
                                    <b>Phòng <?= htmlspecialchars($inv['SoPhong'] ?? $inv['MaPhong']) ?></b>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">
                                        Đại diện: <?= htmlspecialchars($inv['TenSinhVien'] ?? 'N/A') ?>
                                        <?php if (!empty($inv['MSSVTaiTruong'])): ?>
                                            (<?= htmlspecialchars($inv['MSSVTaiTruong']) ?>)
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="font-size: 0.85rem;"><?= htmlspecialchars($inv['LoaiHoaDon']) ?></td>
                                <td>
                                    <b style="color: <?= $isPaid ? '#15803d' : '#b91c1c' ?>; font-size: 0.95rem;">
                                        <?= number_format($inv['SoTienPhaiDong'], 0, ',', '.') ?> đ
                                    </b>
                                </td>
                                <td style="font-size: 0.85rem;"><?= !empty($inv['NgayTao']) ? date('d/m/Y', strtotime($inv['NgayTao'])) : 'N/A' ?></td>
                                <td style="font-size: 0.85rem;"><?= !empty($inv['HanThanhToan']) ? date('d/m/Y', strtotime($inv['HanThanhToan'])) : 'N/A' ?></td>
                                <td>
                                    <?php if ($isPaid): ?>
                                        <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                                            ✓ Đã thanh toán
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                            ⏳ Chưa thanh toán
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if ($isPaid): ?>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                                            <span>Mã GD: <b><?= htmlspecialchars($inv['MaGiaoDich'] ?? 'GD-QR') ?></b></span>
                                            <?php if (!empty($inv['ThoiGianGiaoDich'])): ?>
                                                <br><span><?= date('d/m/Y H:i', strtotime($inv['ThoiGianGiaoDich'])) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Nút xác nhận thanh toán mô phỏng -->
                                        <form method="POST" action="<?= url('staff/utilities/confirm-payment') ?>" style="display: inline;"
                                              onsubmit="return confirm('Xác nhận thanh toán mô phỏng cho hóa đơn <?= htmlspecialchars($inv['MaHoaDon']) ?> với số tiền <?= number_format($inv['SoTienPhaiDong'], 0, ',', '.') ?> đ?');">
                                            <input type="hidden" name="maHoaDon" value="<?= htmlspecialchars($inv['MaHoaDon']) ?>">
                                            <input type="hidden" name="phuongThuc" value="Mã QR KTX">
                                            <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                                            <input type="hidden" name="nam" value="<?= $currentYear ?>">
                                            <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #166534; border-color: #86efac; font-weight: 500;">
                                                ⚡ Xác nhận thanh toán mô phỏng
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- JAVASCRIPT HỖ TRỢ AUTO-FILL & TÍNH TIỀN THỰC THỜI -->
<script>
const roomMeta = <?= json_encode($meta, JSON_UNESCAPED_UNICODE) ?>;

function onRoomChanged(maPhong) {
    if (!maPhong || !roomMeta[maPhong]) {
        document.getElementById('inputDienCu').value = '';
        document.getElementById('inputNuocCu').value = '';
        document.getElementById('inputSoSV').value = '1';
        clearPreview();
        return;
    }

    const data = roomMeta[maPhong];
    document.getElementById('inputDienCu').value = data.dienCu || 0;
    document.getElementById('inputNuocCu').value = data.nuocCu || 0;
    document.getElementById('inputSoSV').value = data.soSV || 1;

    calculatePreview();
}

function clearPreview() {
    document.getElementById('previewDien').innerText = '';
    document.getElementById('previewNuoc').innerText = '';
    document.getElementById('previewTongTien').innerText = '';
}

function calculatePreview() {
    const dienCu = parseInt(document.getElementById('inputDienCu').value) || 0;
    const dienMoi = parseInt(document.getElementById('inputDienMoi').value);
    const nuocCu = parseInt(document.getElementById('inputNuocCu').value) || 0;
    const nuocMoi = parseInt(document.getElementById('inputNuocMoi').value);
    const soSV = parseInt(document.getElementById('inputSoSV').value) || 1;

    if (isNaN(dienMoi) && isNaN(nuocMoi)) {
        clearPreview();
        return;
    }

    if (!isNaN(dienMoi)) {
        if (dienMoi < dienCu) {
            document.getElementById('previewDien').innerHTML = '<span style="color: #b91c1c;">⚠️ Điện mới < cũ!</span>';
        } else {
            const diffDien = dienMoi - dienCu;
            document.getElementById('previewDien').innerHTML = `Tiêu thụ: <b>${diffDien} kWh</b>`;
        }
    }

    if (!isNaN(nuocMoi)) {
        if (nuocMoi < nuocCu) {
            document.getElementById('previewNuoc').innerHTML = '<span style="color: #b91c1c;">⚠️ Nước mới < cũ!</span>';
        } else {
            const diffNuoc = nuocMoi - nuocCu;
            document.getElementById('previewNuoc').innerHTML = `Tiêu thụ: <b>${diffNuoc} m³</b>`;
        }
    }
}

function validateReadingForm() {
    const dienCu = parseInt(document.getElementById('inputDienCu').value) || 0;
    const dienMoi = parseInt(document.getElementById('inputDienMoi').value);
    const nuocCu = parseInt(document.getElementById('inputNuocCu').value) || 0;
    const nuocMoi = parseInt(document.getElementById('inputNuocMoi').value);

    if (dienMoi < dienCu) {
        alert('Lỗi: Chỉ số điện mới (' + dienMoi + ') không được nhỏ hơn chỉ số cũ (' + dienCu + ')!');
        return false;
    }

    if (nuocMoi < nuocCu) {
        alert('Lỗi: Chỉ số nước mới (' + nuocMoi + ') không được nhỏ hơn chỉ số cũ (' + nuocCu + ')!');
        return false;
    }

    return true;
}
</script>

<?php
$content = ob_get_clean();
$title = "Điện Nước & Công nợ - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
