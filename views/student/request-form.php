<?php
$serviceType = $serviceType ?? '';
$serviceDescription = $serviceDescription ?? '';
$contract = $contract ?? null;
$beds = $beds ?? [];
$equipment = $equipment ?? [];

ob_start();
$transfer = $serviceType === 'Xin chuyển phòng';
$leave = $serviceType === 'Xin tạm vắng';
?>
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title"><?= htmlspecialchars($serviceType) ?></h1>
            <p class="page-subtitle"><?= htmlspecialchars($serviceDescription) ?></p>
        </div>
        <a href="<?= url('student/requests') ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Danh mục dịch vụ</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="card">
        <?php if (empty($contract)): ?>
            <p class="text-muted">Bạn cần có hợp đồng nội trú đang hiệu lực để gửi yêu cầu này.</p>
        <?php else: ?>
            <div class="card" style="margin-bottom:16px;background:#f8fafc;">
                <strong>Thông tin hiện tại:</strong>
                Phòng <?= htmlspecialchars($contract['MaPhong']) ?> — Giường <?= htmlspecialchars($contract['KyHieuGiuong']) ?>
            </div>
            <form method="post" action="<?= url('student/requests') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="LoaiDon" value="<?= htmlspecialchars($serviceType) ?>">
                <div class="grid grid-cols-2" style="gap:16px;">
                    <?php if ($serviceType === 'Xin sửa chữa'): ?>
                        <label>Thiết bị hỏng
                            <select class="form-control" name="MaThietBi" required>
                                <option value="">-- Chọn thiết bị trong phòng --</option>
                                <?php foreach ($equipment as $item): ?>
                                    <option value="<?= (int) $item['MaThietBi'] ?>">
                                        <?= htmlspecialchars($item['TenThietBi'] . (!empty($item['MaSoThietBi']) ? ' (' . $item['MaSoThietBi'] . ')' : '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php endif; ?>
                    <?php if ($transfer): ?>
                        <label>Giường chuyển đến
                            <select class="form-control" name="MaGiuongChuyenDen" required>
                                <option value="">-- Chọn giường trống --</option>
                                <?php foreach ($beds as $bed): ?>
                                    <?php if ($bed['MaGiuong'] !== $contract['MaGiuong']): ?>
                                        <option value="<?= htmlspecialchars($bed['MaGiuong']) ?>"><?= htmlspecialchars($bed['MaPhong'].' - '.$bed['KyHieuGiuong']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </label>
                    <?php endif; ?>
                    <?php if ($leave): ?>
                        <label>Từ ngày<input class="form-control" type="date" name="TuNgay" required></label>
                        <label>Đến ngày<input class="form-control" type="date" name="DenNgay" required></label>
                    <?php endif; ?>
                    <?php if (in_array($serviceType, ['Xin trả phòng', 'Xin gia hạn'], true)): ?>
                        <label>Ngày mong muốn<input class="form-control" type="date" name="<?= $serviceType === 'Xin gia hạn' ? 'DenNgay' : 'TuNgay' ?>" required></label>
                    <?php endif; ?>
                    <?php if ($serviceType === 'Hồ sơ miễn giảm lệ phí'): ?>
                        <label>Tệp minh chứng
                            <input class="form-control" type="file" name="TepMinhChung" accept=".jpg,.jpeg,.png,.pdf" required>
                        </label>
                    <?php endif; ?>
                </div>
                <label style="display:block;margin-top:16px;">Nội dung / lý do
                    <textarea class="form-control" name="LyDo" minlength="10" required rows="5" placeholder="Mô tả rõ yêu cầu của bạn (ít nhất 10 ký tự)"></textarea>
                </label>
                <div style="display:flex;gap:10px;margin-top:16px;">
                    <button class="btn btn-primary" type="submit"><i class="fa-solid fa-paper-plane"></i> Gửi yêu cầu</button>
                    <a href="<?= url('student/requests') ?>" class="btn btn-outline">Hủy</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
