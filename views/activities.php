<section class="development card">
    <div class="development-icon">★</div>
    <span class="status-pill">Tính năng đang phát triển</span>
    <h2>Lịch sử hoạt động rèn luyện</h2>
    <p>Hiện chỉ xem dữ liệu; không tự ghi điểm hoặc ghi nhận điểm danh.</p>
    <?php foreach ($activities ?? [] as $activity): ?>
    <div class="request-list"><strong><?= htmlspecialchars($activity['NoiDung']) ?></strong><small><?= htmlspecialchars($activity['NamHoc']) ?> · Học kỳ <?= htmlspecialchars($activity['HocKy']) ?> · <?= number_format($activity['TongDiemCuoiKy'] ?? 0) ?> điểm</small></div>
    <?php endforeach; ?>
    <?php if (empty($activities)): ?><p>Chưa có ghi nhận hoạt động.</p><?php endif; ?>
</section>
