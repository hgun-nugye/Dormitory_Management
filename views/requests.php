<div class="request-layout">
    <section class="card">
        <div class="card-head"><h3>Tạo yêu cầu mới</h3></div>
        <form method="post">
            <input type="hidden" name="action" value="create-request">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <label>Loại yêu cầu
                <select name="LoaiDon"><option>Xin chuyển phòng</option><option>Xin sửa chữa</option><option>Xin tạm vắng</option><option>Xin trả phòng</option><option>Xin gia hạn</option></select>
            </label>
            <label>Giường chuyển đến
                <?php $beds = $beds ?? []; ?>
                <select name="MaGiuongChuyenDen"><option value="">Không áp dụng / để trống</option><?php foreach ($beds as $bed): ?><option value="<?= htmlspecialchars($bed['MaGiuong']) ?>"><?= htmlspecialchars($bed['MaPhong']) ?> - Giường <?= htmlspecialchars($bed['KyHieuGiuong']) ?></option><?php endforeach; ?></select>
            </label>
            <div><label>Từ ngày<input type="date" name="TuNgay"></label><label>Đến ngày<input type="date" name="DenNgay"></label></div>
            <label>Nội dung<textarea name="LyDo" minlength="10" required placeholder="Mô tả rõ lý do hoặc sự cố..."></textarea></label>
            <label>Đường dẫn ảnh minh chứng<input name="TepMinhChung"></label>
            <button class="button-primary" type="submit">Gửi yêu cầu</button>
        </form>
    </section>
    <section class="card">
        <div class="card-head"><h3>Yêu cầu gần đây</h3></div>
        <?php $requests = $requests ?? []; $repairRequests = $repairRequests ?? []; ?>
        <?php if (!$requests && !$repairRequests): ?><div class="empty-state"><span>✦</span><strong>Chưa có yêu cầu nào</strong></div><?php else: ?>
        <div class="request-list">
            <?php foreach ($requests as $request): ?><article><div><strong><?= htmlspecialchars($request['LoaiDon']) ?></strong><small><?= htmlspecialchars($request['LyDo'] ?? '') ?></small></div><span class="payment-status unpaid"><?= htmlspecialchars($request['TrangThaiDuyet']) ?></span></article><?php endforeach; ?>
            <?php foreach ($repairRequests as $request): ?><article><div><strong>Báo hỏng tại <?= htmlspecialchars($request['MaPhong']) ?></strong><small><?= htmlspecialchars($request['MoTaHuHong']) ?></small></div><span class="payment-status unpaid"><?= htmlspecialchars($request['TrangThaiSua']) ?></span></article><?php endforeach; ?>
        </div>
        <?php endif; ?>
    </section>
</div>
