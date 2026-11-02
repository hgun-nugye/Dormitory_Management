<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Xét duyệt Chỗ ở & Hồ sơ Miễn giảm</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Xét duyệt Chỗ ở & Hồ sơ Miễn giảm</h1>
                <p class="page-subtitle">
                    <strong>Phân hệ:</strong> Xét duyệt hồ sơ đăng ký giữ chỗ 72h (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>) và Thẩm định hồ sơ miễn giảm học phí nội trú.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về Dashboard Cụm
                </a>
            </div>
        </div>

        <!-- Thanh phím tắt chuyển nhanh các phân hệ nghiệp vụ Staff -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;">
            <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📊 Tổng quan BQL</a>
            <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🏢 1. Sơ đồ Phòng & Cư trú</a>
            <a href="<?= url('staff/applications') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
    <?php if (!empty($success)): ?>
        <div class="card" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">✓</span>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="card" style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">✕</span>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #92400e; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️ Nguyên tắc xét duyệt hồ sơ & Giải phóng giường (Mục 1 & 3 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #b45309; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Quy tắc giữ chỗ 72h:</strong> Khi BQL bấm [Duyệt giữ chỗ 72h], hệ thống tự động gán <code>NguoiDuyet</code>, <code>ThoiGianDuyet = NOW()</code> và tính <code>HanChotThanhToan = NOW() + 72 giờ</code>, đồng thời phát hành hóa đơn lệ phí giữ chỗ.</li>
            <li><strong>Giải phóng giường:</strong> Nếu sinh viên bị Từ chối hoặc không thanh toán sau 72 giờ, giường đã khóa tự động được chuyển về <code>TrangThaiGiuong = 'Trống'</code>.</li>
            <li><strong>Thẩm định miễn giảm:</strong> Mức giảm quy định từ <code>0% đến 100%</code>, tự động tính <code>SoTienHoanTra</code> theo đơn giá trần phòng nội trú chuẩn.</li>
            <li><strong>Phân quyền:</strong> Cán bộ quản lý thao tác trên phạm vi cụm được phân công (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>).</li>
        </ul>
    </div>

    <!-- PHẦN 1: DANH SÁCH HỒ SƠ ĐĂNG KÝ CHỖ Ở MỚI CHỜ DUYỆT (CẤP CỤM) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>1. Danh sách Hồ sơ Đăng ký Chỗ ở Chờ duyệt (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-warning"><?= count($pendingApplications ?? []) ?> hồ sơ chờ xử lý</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Hồ Sơ</th>
                        <th>Sinh viên</th>
                        <th>Trường ĐH</th>
                        <th>Giới tính</th>
                        <th>Phòng & Giường đăng ký</th>
                        <th>Thời gian nộp</th>
                        <th>Quy định</th>
                        <th style="text-align: right;">Thao tác phê duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pendingApplications)): ?>
                        <?php foreach ($pendingApplications as $app): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($app['MaHoSo']) ?></b></td>
                                <td>
                                    <b><?= htmlspecialchars($app['HoVaTen']) ?></b> <br>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                                        MSSV: <?= htmlspecialchars($app['MSSVTaiTruong'] ?? $app['MaSinhVien']) ?> &bull; SĐT: <?= htmlspecialchars($app['SoDienThoai'] ?? '---') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($app['TenTruong'] ?? '---') ?></td>
                                <td>
                                    <span class="badge <?= ($app['GioiTinh'] === 'Nam') ? 'badge-info' : 'badge-warning' ?>">
                                        <?= htmlspecialchars($app['GioiTinh']) ?>
                                    </span>
                                </td>
                                <td>
                                    Phòng <b><?= htmlspecialchars($app['SoPhong'] ?? '---') ?></b> (<?= htmlspecialchars($app['TenToaNha'] ?? '') ?>)<br>
                                    <span class="badge badge-info">Giường: <?= htmlspecialchars($app['KyHieuGiuong'] ?? $app['MaGiuong']) ?></span>
                                    <span style="font-size: 0.75rem; color: var(--text-muted); display: block;">
                                        <?= htmlspecialchars($app['TenLoaiPhong'] ?? '') ?>
                                    </span>
                                </td>
                                <td><?= !empty($app['ThoiGianNop']) ? date('d/m/Y H:i', strtotime($app['ThoiGianNop'])) : '---' ?></td>
                                <td>
                                    <span class="badge badge-warning">Hạn 72h sau duyệt</span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận duyệt hồ sơ <?= htmlspecialchars($app['MaHoSo']) ?> và ấn định thời hạn 72h thanh toán?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($app['MaHoSo']) ?>">
                                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                            ✓ Duyệt giữ chỗ 72h
                                        </button>
                                    </form>

                                    <form action="<?= url('staff/reject-application') ?>" method="POST" style="display:inline; margin-left: 4px;" id="reject-form-<?= htmlspecialchars($app['MaHoSo']) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($app['MaHoSo']) ?>">
                                        <input type="hidden" name="lyDo" id="lyDo-<?= htmlspecialchars($app['MaHoSo']) ?>" value="">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #dc2626;" onclick="handleReject('<?= htmlspecialchars($app['MaHoSo']) ?>')">
                                            ✕ Từ chối
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 32px 16px; color: var(--text-muted);">
                                <div>🎉 Hiện không có hồ sơ nào đang chờ duyệt tại Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>.</div>
                                <span style="font-size: 0.85rem;">Tất cả hồ sơ đăng ký đã được xử lý.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHẦN 1.2: THEO DÕI HỒ SƠ ĐANG TRONG HẠN GIỮ CHỖ 72H (CHỜ THANH TOÁN) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Theo dõi Hồ sơ Đang trong hạn 72h Thanh toán (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-info"><?= count($active72hApplications ?? []) ?> hồ sơ đang giữ chỗ</span>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
            Các hồ sơ đã được BQL duyệt; sinh viên cần hoàn tất thanh toán trước khi hết hạn. Nếu quá hạn 72 giờ, tiến trình ngầm sẽ tự động thu hồi giường.
        </p>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Hồ Sơ</th>
                        <th>Sinh viên</th>
                        <th>Vị trí giường</th>
                        <th>Lệ phí chỗ ở</th>
                        <th>Thời gian duyệt</th>
                        <th>Hạn chót thanh toán</th>
                        <th>Thời gian còn lại</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($active72hApplications)): ?>
                        <?php foreach ($active72hApplications as $act): ?>
                            <tr>
                                <td><b><?= htmlspecialchars($act['MaHoSo']) ?></b></td>
                                <td>
                                    <b><?= htmlspecialchars($act['HoVaTen']) ?></b> <br>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: <?= htmlspecialchars($act['MSSVTaiTruong'] ?? $act['MaSinhVien']) ?></span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($act['TenToaNha'] ?? '') ?> - P.<?= htmlspecialchars($act['SoPhong'] ?? '') ?><br>
                                    <span class="badge badge-info">Giường <?= htmlspecialchars($act['KyHieuGiuong'] ?? $act['MaGiuong']) ?></span>
                                </td>
                                <td>
                                    <b><?= number_format($act['SoTienPhaiDong'] ?? 0, 0, ',', '.') ?> đ</b><br>
                                    <span class="badge badge-warning"><?= htmlspecialchars($act['TrangThaiHoaDon'] ?? 'Chưa thanh toán') ?></span>
                                </td>
                                <td><?= !empty($act['ThoiGianDuyet']) ? date('d/m/Y H:i', strtotime($act['ThoiGianDuyet'])) : '---' ?></td>
                                <td>
                                    <b style="color: #b45309;">
                                        <?= !empty($act['HanChotThanhToan']) ? date('d/m/Y H:i', strtotime($act['HanChotThanhToan'])) : '---' ?>
                                    </b>
                                </td>
                                <td>
                                    <?php 
                                        $gioConLai = (int)($act['SoGioConLai'] ?? 0);
                                        if ($gioConLai > 24): 
                                    ?>
                                        <span class="badge badge-info">Còn <?= $gioConLai ?> giờ</span>
                                    <?php elseif ($gioConLai > 0): ?>
                                        <span class="badge badge-warning">Còn <?= $gioConLai ?> giờ</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="background:#fee2e2; color:#991b1b;">Đã quá hạn</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right;">
                                    <form action="<?= url('staff/reject-application') ?>" method="POST" style="display:inline;" id="cancel-form-<?= htmlspecialchars($act['MaHoSo']) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($act['MaHoSo']) ?>">
                                        <input type="hidden" name="lyDo" id="cancel-lyDo-<?= htmlspecialchars($act['MaHoSo']) ?>" value="Hủy theo yêu cầu của sinh viên">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #dc2626;" onclick="handleCancelActive('<?= htmlspecialchars($act['MaHoSo']) ?>')">
                                            ✕ Hủy giữ chỗ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 24px 16px; color: var(--text-muted);">
                                Chưa có hồ sơ nào đang trong trạng thái chờ thanh toán 72h.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHẦN 2: THẨM ĐỊNH & XÉT DUYỆT HỒ SƠ MIỄN GIẢM NỘI TRÚ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>2. Thẩm định & Duyệt Hồ sơ Miễn giảm nội trú</span>
            <span class="badge badge-info">Bảng: HoSoMienGiam + HopDongNoiTru + SinhVien</span>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
            Dành cho cán bộ thẩm định điều kiện chính sách, kiểm tra giấy xác nhận minh chứng và tính toán số tiền hoàn trả hoặc cấn trừ tiền phòng:
        </p>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn MG</th>
                        <th>Sinh viên nộp</th>
                        <th>Hợp đồng</th>
                        <th>Đối tượng chính sách</th>
                        <th>Minh chứng</th>
                        <th>Mức giảm đề xuất</th>
                        <th>Số tiền hoàn trả</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>MG2026-004</b></td>
                        <td>
                            <b>Trần Thị Mai Chúc</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2212345</span>
                        </td>
                        <td>HD2026002</td>
                        <td>
                            <span class="badge badge-info">Hộ cận nghèo</span>
                        </td>
                        <td>
                            <a href="#" onclick="alert('Mở file đính kèm: giay_chung_nhan_can_ngheo.pdf'); return false;" style="font-size: 0.85rem; color: var(--primary);">
                                📎 giay_can_ngheo.pdf
                            </a>
                        </td>
                        <td><b>50%</b> (Đơn giá 650.000đ)</td>
                        <td><b style="color: #15803d;">325.000 đ / tháng</b></td>
                        <td>
                            <span class="badge badge-warning">Chờ thẩm định</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phê duyệt miễn giảm 50%! Bút toán hoàn tiền 325.000đ đã được lập.')">
                                ✓ Phê duyệt mức giảm
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã yêu cầu sinh viên bổ sung thêm giấy tờ minh chứng!')">
                                ↺ Yêu cầu bổ sung
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>MG2026-005</b></td>
                        <td>
                            <b>Lê Hoàng Phúc</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2410112</span>
                        </td>
                        <td>HD2026019</td>
                        <td>
                            <span class="badge badge-info">Con thương binh liệt sĩ</span>
                        </td>
                        <td>
                            <a href="#" onclick="alert('Mở file đính kèm: giay_thuong_binh.pdf'); return false;" style="font-size: 0.85rem; color: var(--primary);">
                                📎 the_thuong_binh.pdf
                            </a>
                        </td>
                        <td><b>100%</b> (Đơn giá 650.000đ)</td>
                        <td><b style="color: #15803d;">650.000 đ / tháng</b></td>
                        <td>
                            <span class="badge badge-warning">Chờ thẩm định</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phê duyệt miễn giảm 100%!')">
                                ✓ Phê duyệt mức giảm
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Từ chối hồ sơ')">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function handleReject(maHoSo) {
    const lyDo = prompt('Nhập lý do từ chối hồ sơ ' + maHoSo + ':');
    if (lyDo !== null) {
        const input = document.getElementById('lyDo-' + maHoSo);
        if (input) input.value = lyDo.trim();
        const form = document.getElementById('reject-form-' + maHoSo);
        if (form) form.submit();
    }
}

function handleCancelActive(maHoSo) {
    if (confirm('Xác nhận hủy chỗ hồ sơ ' + maHoSo + '? Giường sẽ được giải phóng ngay lập tức về trạng thái Trống.')) {
        const lyDo = prompt('Lý do hủy giữ chỗ:', 'Sinh viên hủy hoặc không tiếp tục giữ chỗ');
        if (lyDo !== null) {
            const input = document.getElementById('cancel-lyDo-' + maHoSo);
            if (input) input.value = lyDo.trim();
            const form = document.getElementById('cancel-form-' + maHoSo);
            if (form) form.submit();
        }
    }
}
</script>

<?php
$content = ob_get_clean();
$title = "Xét duyệt Chỗ ở & Miễn giảm - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
