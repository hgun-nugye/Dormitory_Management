<?php
ob_start();
$hoaDons = $hoaDons ?? [];
$chiSoDienNuoc = $chiSoDienNuoc ?? [];
$giaoDichs = $giaoDichs ?? [];
$tongCongNo = $tongCongNo ?? 0;

$unpaidInvoices = array_filter($hoaDons, fn($hd) => ($hd['TrangThai'] ?? '') !== 'Đã thanh toán');
$paidCount = count($hoaDons) - count($unpaidInvoices);

$latestUtility = !empty($chiSoDienNuoc) ? $chiSoDienNuoc[0] : null;
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;">Trang chủ</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;">Thông tin thanh toán</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Thông tin thanh toán</h1>
        </div>
    </div>

    <!-- 3 Summary KPI Cards -->
    <div class="grid grid-cols-4" style="margin-bottom: 24px;">
        <div class="card stat-card" style="border-top: 3px solid <?= $tongCongNo > 0 ? '#ef4444' : '#10b981' ?>;">
            <span class="stat-label">Tổng công nợ hiện tại</span>
            <div class="stat-val" style="font-size: 1.45rem; color: <?= $tongCongNo > 0 ? '#b91c1c' : '#15803d' ?>;">
                <?= format_money($tongCongNo) ?>
            </div>
            <span class="stat-sub">
                <?= count($unpaidInvoices) > 0 ? count($unpaidInvoices) . ' hóa đơn cần thanh toán' : 'Không còn công nợ quá hạn' ?>
            </span>
        </div>

        <div class="card stat-card" style="border-top: 3px solid #3b82f6;">
            <span class="stat-label">Điện nước kỳ mới nhất</span>
            <div class="stat-val" style="font-size: 1.45rem; color: #1e40af;">
                <?php if ($latestUtility): ?>
                    <?= format_money($latestUtility['TienMoiSV'] ?? ((($latestUtility['TienDien'] ?? 0) + ($latestUtility['TienNuoc'] ?? 0)) / max(1, (int)($latestUtility['SoSVThucOTaiThang'] ?? 1)))) ?>
                <?php else: ?>
                    0 đ
                <?php endif; ?>
            </div>
            <span class="stat-sub">
                <?= $latestUtility ? 'Kỳ tháng ' . $latestUtility['Thang'] . '/' . $latestUtility['Nam'] : 'Chưa có kỳ chỉ số mới' ?>
            </span>
        </div>

        <div class="card stat-card" style="border-top: 3px solid #10b981;">
            <span class="stat-label">Hóa đơn đã hoàn thành</span>
            <div class="stat-val" style="font-size: 1.45rem; color: #10b981;">
                <?= $paidCount ?>
            </div>
            <span class="stat-sub">
                Đã đối soát vào hệ thống
            </span>
        </div>

        <div class="card stat-card" style="border-top: 3px solid #8b5cf6;">
            <span class="stat-label">Lịch sử giao dịch</span>
            <div class="stat-val" style="font-size: 1.45rem; color: #7c3aed;">
                <?= count($giaoDichs) ?>
            </div>
            <span class="stat-sub">
                Giao dịch ngân hàng ghi nhận
            </span>
        </div>
    </div>

    <!-- KHỐI 1: DANH SÁCH HÓA ĐƠN -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách hóa đơn lệ phí</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã hóa đơn</th>
                        <th>Khoản thu</th>
                        <th>Kỳ thanh toán</th>
                        <th>Số tiền phải đóng</th>
                        <th>Hạn nộp</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Hướng dẫn</th>
                    </tr>
                </thead>
                <tbody id="invoiceTableBody">
                    <?php if (empty($hoaDons)): ?>
                        <tr>
                            <td colspan="7" class="text-muted" style="text-align: center; padding: 24px 0;">
                                Hiện tại bạn chưa có hóa đơn lệ phí nào phát sinh.
                            </td>
                        </tr>
                        <?php else: foreach ($hoaDons as $hd): ?>
                            <tr class="invoice-row">
                                <td><code style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($hd['MaHoaDon']) ?></code></td>
                                <td>
                                    <b><?= htmlspecialchars($hd['LoaiHoaDon']) ?></b>
                                    <?php if (!empty($hd['GhiChu'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($hd['GhiChu']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($hd['KyThanhToan'] ?? 'Định kỳ') ?></td>
                                <td><b style="font-size: 0.95rem; color: var(--text-primary);"><?= format_money($hd['SoTienPhaiDong']) ?></b></td>
                                <td>
                                    <?= !empty($hd['HanThanhToan']) ? date('d/m/Y', strtotime($hd['HanThanhToan'])) : '-' ?>
                                </td>
                                <td>
                                    <?php if (($hd['TrangThai'] ?? '') === 'Đã thanh toán'): ?>
                                        <span class="badge badge-success">Đã thanh toán</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Chưa thanh toán</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <?php if (($hd['TrangThai'] ?? '') !== 'Đã thanh toán'): ?>
                                        <a href="#payment-instruction" class="btn btn-primary btn-sm" style="border-radius: 6px; text-decoration: none; font-size: 0.75rem;">
                                            Thanh toán
                                        </a>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted); font-size: 0.8rem;">Đã tất toán</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Thanh phân trang hóa đơn (5 mục / trang) -->
        <div id="invoicePaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 10px;">
            <span id="invoicePaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;"></span>
            <div id="invoicePaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
        </div>
    </div>

    <!-- KHỐI 2: CHỈ SỐ ĐIỆN NƯỚC CỦA PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Bảng theo dõi điện - nước phòng ở định kỳ</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Kỳ tháng</th>
                        <th>Phòng / Tòa</th>
                        <th>Điện</th>
                        <th>Số kWh</th>
                        <th>Tiền điện</th>
                        <th>Nước</th>
                        <th>Số m³</th>
                        <th>Tiền nước</th>
                        <th>Tổng tiền</th>
                        <th>Số người</th>
                        <th>Chia mỗi SV</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($chiSoDienNuoc)): ?>
                        <tr>
                            <td colspan="11" class="text-muted" style="text-align: center; padding: 24px 0;">
                                Chưa có dữ liệu chỉ số điện nước cho phòng đang lưu trú.
                            </td>
                        </tr>
                        <?php else: foreach ($chiSoDienNuoc as $cs):
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
                                <td><?= format_money($cs['TienNuoc'] + $cs['TienDien']) ?></td>
                                <td><?= $cs['SoSVThucOTaiThang'] ?> người</td>
                                <td><b style="color: var(--primary); font-size: 0.95rem;"><?= format_money($tienMoiSV) ?></b></td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 3: LỊCH SỬ GIAO DỊCH THANH TOÁN -->
    <div class="card">
        <div class="card-title">
            <span>Lịch sử giao dịch thanh toán đã ghi nhận</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã giao dịch</th>
                        <th>Mã hóa đơn</th>
                        <th>Khoản thu</th>
                        <th>Kênh thanh toán</th>
                        <th>Thời gian giao dịch</th>
                        <th>Số tiền</th>
                        <th>Kết quả</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($giaoDichs)): ?>
                        <tr>
                            <td colspan="7" class="text-muted" style="text-align: center; padding: 20px 0;">
                                Chưa có giao dịch thanh toán nào được ghi nhận.
                            </td>
                        </tr>
                        <?php else: foreach ($giaoDichs as $gd): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($gd['MaGiaoDich']) ?></code></td>
                                <td><b><?= htmlspecialchars($gd['MaHoaDon']) ?></b></td>
                                <td><?= htmlspecialchars($gd['LoaiHoaDon'] ?? 'Lệ phí') ?></td>
                                <td><?= htmlspecialchars($gd['KenhThanhToan']) ?></td>
                                <td><?= htmlspecialchars($gd['ThoiGianGiaoDich']) ?></td>
                                <td><b><?= format_money($gd['SoTien']) ?></b></td>
                                <td>
                                    <span class="badge badge-success">
                                        <?= htmlspecialchars($gd['KetQuaGiaoDich']) ?>
                                    </span>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 4: HƯỚNG DẪN CHUYỂN KHOẢN THANH TOÁN -->
    <div id="payment-instruction" class="card" style="margin-top: 24px; background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border: 1px solid #bfdbfe;">
        <div class="card-title">
            <span style="color: #1e40af;">
                Hướng dẫn thanh toán qua Chuyển khoản ngân hàng
            </span>
        </div>

        <div class="payment-info-grid" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; align-items: stretch;">
            <!-- KHUNG 1: THÔNG TIN TÀI KHOẢN -->
            <div class="payment-info-box" style="padding: 18px; background: #ffffff; border: 1px solid #dbeafe; border-radius: 10px; font-size: 0.875rem; color: var(--text-secondary); line-height: 1.7; display: flex; flex-direction: column;">
                <div style="font-weight: 700; color: #1e40af; margin-bottom: 12px;">Thông tin tài khoản thụ hưởng</div>
                <p style="margin: 0 0 12px;">Sinh viên thanh toán lệ phí nội trú và tiền điện nước theo thông tin tài khoản chuyên thu chính thức:</p>
                <div style="background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #dbeafe; flex: 1;">
                    <div style="margin-bottom: 8px;">• <b>Đơn vị thụ hưởng:</b> TRUNG TÂM QUẢN LÝ KÝ TÚC XÁ ĐHQG-HCM</div>
                    <div style="margin-bottom: 8px;">• <b>Số tài khoản:</b> <code style="font-size: 1rem; font-weight: 700; color: #1e40af;">13010000123456</code></div>
                    <div style="margin-bottom: 8px;">• <b>Ngân hàng:</b> BIDV - Chi nhánh Đông Sài Gòn</div>
                    <div style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #dbeafe;">
                        • <b>Cú pháp chuyển khoản chuẩn:</b>
                        <div style="margin-top: 6px;">
                            <span style="display: inline-block; background: #fef3c7; color: #92400e; padding: 5px 12px; border-radius: 6px; font-weight: 700; font-family: monospace;">KTX [MSSV] [MaHoaDon]</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KHUNG 2: LƯU Ý ĐỐI SOÁT -->
            <div class="payment-info-box" style="padding: 18px; background: #ffffff; border: 1px solid #dbeafe; border-radius: 10px; font-size: 0.875rem; color: var(--text-secondary); line-height: 1.7; display: flex; flex-direction: column;">
                <div style="font-weight: 700; color: #1e40af; margin-bottom: 12px;">Lưu ý đối soát giao dịch</div>
                <div style="background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #dbeafe; flex: 1;">
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
                        <li style="margin-bottom: 12px;">Hệ thống tự động quét và gạch nợ trong vòng <b>2 - 4 giờ</b> làm việc sau khi nhận được thông báo biến động số dư.</li>
                        <li style="margin-bottom: 12px;">Vui lòng ghi chính xác cú pháp chuyển khoản để tránh chậm trễ đối soát.</li>
                        <li>Nếu sau 24 giờ hóa đơn chưa chuyển sang trạng thái <b>Đã thanh toán</b>, vui lòng chụp biên lai nộp cho Phòng Tài vụ Cụm nhà.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const INVOICES_PER_PAGE = 5;
    let invoiceCurrentPage = 1;

    function renderInvoicePagination() {
        const rows = Array.from(document.querySelectorAll('#invoiceTableBody tr.invoice-row'));
        const total = rows.length;
        const infoEl = document.getElementById('invoicePaginationInfo');
        const controlsEl = document.getElementById('invoicePaginationControls');
        const barEl = document.getElementById('invoicePaginationBar');

        // Nếu số lượng hóa đơn <= 5, hiển thị trọn vẹn và ẩn thanh nút phân trang
        if (total <= INVOICES_PER_PAGE) {
            rows.forEach(row => {
                row.style.display = '';
            });
            if (barEl) barEl.style.display = 'none';
            return;
        }
        if (barEl) barEl.style.display = 'flex';

        const totalPages = Math.ceil(total / INVOICES_PER_PAGE);
        if (invoiceCurrentPage > totalPages) invoiceCurrentPage = totalPages;
        if (invoiceCurrentPage < 1) invoiceCurrentPage = 1;

        const startIdx = (invoiceCurrentPage - 1) * INVOICES_PER_PAGE;
        const endIdx = startIdx + INVOICES_PER_PAGE;

        rows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        if (infoEl) {
            const displayStart = startIdx + 1;
            const displayEnd = Math.min(endIdx, total);
            infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> hóa đơn (Trang <b>${invoiceCurrentPage}</b> / <b>${totalPages}</b>)`;
        }

        if (!controlsEl) return;
        if (totalPages <= 1) {
            controlsEl.innerHTML = '';
            return;
        }

        let html = '';
        const disabledPrev = invoiceCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToInvoicePage(${invoiceCurrentPage - 1})" ${disabledPrev}>&larr; Trước</button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === invoiceCurrentPage) {
                html += `<button type="button" class="btn btn-primary btn-sm" style="padding: 5px 10px; border-radius: 6px; font-weight: 700;">${p}</button>`;
            } else {
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToInvoicePage(${p})" style="padding: 5px 10px; border-radius: 6px;">${p}</button>`;
            }
        }

        const disabledNext = invoiceCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToInvoicePage(${invoiceCurrentPage + 1})" ${disabledNext}>Sau &rarr;</button>`;

        controlsEl.innerHTML = html;
    }

    function goToInvoicePage(page) {
        invoiceCurrentPage = page;
        renderInvoicePagination();
    }

    document.addEventListener('DOMContentLoaded', function() {
        renderInvoicePagination();
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Thông tin thanh toán - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
