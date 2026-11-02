<section class="hero">
    <div><span class="eyebrow">KỸ TÚC XÁ ĐHQG-HCM</span><h2>Xin chào, <?= htmlspecialchars($student['HoVaTen'] ?? '-') ?>!</h2><p>Cổng thông tin dành cho sinh viên nội trú</p></div>
    <div class="hero-status"><small>Trạng thái lưu trú</small><strong>● <?= htmlspecialchars($student['TrangThaiNoiTru'] ?? '-') ?></strong></div>
</section>
<section class="stat-grid">
    <article><div class="stat-icon blue">⌂</div><div><small>Phòng đang ở</small><strong><?= htmlspecialchars($room['MaPhong'] ?? '-') ?></strong><span>Tòa <?= htmlspecialchars($room['MaToaNha'] ?? '-') ?> · Tầng <?= htmlspecialchars($room['Tang'] ?? '-') ?></span></div></article>
    <article><div class="stat-icon green">✓</div><div><small>Hợp đồng</small><strong><?= htmlspecialchars($contract['TrangThaiHopDong'] ?? '-') ?></strong><span>Đến <?= htmlspecialchars(isset($contract['NgayKetThuc']) ? date('d/m/Y', strtotime($contract['NgayKetThuc'])) : '-') ?></span></div></article>
    <article><div class="stat-icon orange">₫</div><div><small>Vai trò phòng</small><strong><?= (($studentRole ?? '') === 'TRUONG_PHONG') ? 'Trưởng phòng' : 'Sinh viên' ?></strong><span><?= (($studentRole ?? '') === 'TRUONG_PHONG') ? 'Được thanh toán hộ điện nước' : 'Không quản lý tài chính phòng' ?></span></div></article>
</section>
