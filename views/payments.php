<?php
$bills = isset($bills) && is_array($bills) ? $bills : [];
?>
<section class="balance-banner">
    <div><small>Số hóa đơn của bạn</small><strong><?= count($bills) ?></strong><span><?= isset($studentRole) && $studentRole === 'TRUONG_PHONG' ? 'Bao gồm hóa đơn phòng đại diện' : 'Hóa đơn cá nhân' ?></span></div>
    <button class="button-primary" onclick="document.getElementById('paymentModal').showModal()">Thanh toán demo</button>
</section>
<section class="card">
    <div class="card-head"><h3>Hóa đơn & giao dịch</h3></div>
    <div class="table-wrap"><table><thead><tr><th>Mã hóa đơn</th><th>Loại</th><th>Phòng</th><th>Số tiền</th><th>Hạn</th><th>Trạng thái</th></tr></thead><tbody>
        <?php foreach ($bills as $bill): ?>
        <tr><td><strong><?= htmlspecialchars($bill['MaHoaDon']) ?></strong><?= !empty($bill['_roomRepresentative']) ? '<br><small>Đại diện phòng</small>' : '' ?></td><td><?= htmlspecialchars($bill['LoaiHoaDon']) ?></td><td><?= htmlspecialchars($bill['MaPhong'] ?? '-') ?></td><td><strong><?= number_format($bill['SoTienPhaiDong'], 0, ',', '.') ?>đ</strong></td><td><?= htmlspecialchars($bill['HanThanhToan'] ? date('d/m/Y', strtotime($bill['HanThanhToan'])) : '-') ?></td><td><span class="payment-status <?= $bill['TrangThai'] === 'Đã thanh toán' ? 'paid' : 'unpaid' ?>"><?= htmlspecialchars($bill['TrangThai']) ?></span></td></tr>
        <?php endforeach; ?>
        <?php if (!$bills): ?><tr><td colspan="6">Chưa có hóa đơn.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<dialog id="paymentModal"><button class="dialog-close" onclick="this.closest('dialog').close()">×</button><h3>Thanh toán demo</h3><p>Chưa kết nối cổng thanh toán</p><div class="qr"><div></div></div><strong class="qr-amount">Giao dịch mô phỏng</strong><button class="button-primary full-button" onclick="this.closest('dialog').close()">Đóng</button></dialog>
