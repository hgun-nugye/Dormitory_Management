<section class="card">
    <div class="card-head"><h3>Hợp đồng & đăng ký nội trú</h3></div>
    <?php $contract = isset($contract) && is_array($contract) ? $contract : []; ?>
    <div class="info-grid">
        <div><small>Mã hợp đồng</small><strong><?= htmlspecialchars((string)($contract['MaHopDong'] ?? 'Chưa có')) ?></strong></div>
        <div><small>Trạng thái</small><strong><?= htmlspecialchars((string)($contract['TrangThaiHopDong'] ?? 'Chưa có')) ?></strong></div>
        <div><small>Ngày bắt đầu</small><strong><?= htmlspecialchars(isset($contract['NgayBatDau']) && $contract['NgayBatDau'] ? date('d/m/Y', strtotime($contract['NgayBatDau'])) : 'Chưa có') ?></strong></div>
        <div><small>Ngày kết thúc</small><strong><?= htmlspecialchars(isset($contract['NgayKetThuc']) && $contract['NgayKetThuc'] ? date('d/m/Y', strtotime($contract['NgayKetThuc'])) : 'Chưa có') ?></strong></div>
    </div>
</section>
