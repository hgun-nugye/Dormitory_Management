<?php
$serviceType = $serviceType ?? '';
$serviceDescription = $serviceDescription ?? '';
$contract = $contract ?? null;
$beds = $beds ?? [];
$equipment = $equipment ?? [];

ob_start();
$transfer = $serviceType === 'Xin chuyển phòng';
$leave = $serviceType === 'Xin tạm vắng';
$repair = $serviceType === 'Xin sửa chữa';
$returnRoom = $serviceType === 'Xin trả phòng';
$extension = $serviceType === 'Hồ sơ miễn giảm lệ phí';
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <span>/</span>
        <a href="<?= url('student/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Cổng sinh viên</a>
        <span>/</span>
        <a href="<?= url('student/requests') ?>" style="color: var(--text-muted); text-decoration: none;">Dịch vụ một cửa</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;"><?= htmlspecialchars($serviceType) ?></span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <?= htmlspecialchars($serviceType) ?>
            </h1>
            <p class="page-subtitle"><?= htmlspecialchars($serviceDescription) ?></p>
        </div>
        <div class="header-actions">
            <a href="<?= url('student/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 8px;">
                <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Danh mục dịch vụ
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 900px; margin: 0 auto; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
        <?php if (empty($contract)): ?>
            <div style="text-align: center; padding: 36px 20px;">
                <i class="fa-solid fa-file-circle-xmark" style="font-size: 48px; color: #f59e0b; margin-bottom: 14px;"></i>
                <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 8px;">Chưa đủ điều kiện gửi đơn</h3>
                <p class="text-muted" style="max-width: 500px; margin: 0 auto 18px; font-size: 0.9rem;">
                    Bạn cần có <b>hợp đồng nội trú đang hiệu lực</b> tại KTX ĐHQG-HCM để sử dụng dịch vụ này.
                </p>
                <a href="<?= url('student/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                    <i class="fa-solid fa-arrow-left" style="margin-right:8px"></i> Quay lại Cổng sinh viên
                </a>
            </div>
        <?php else: ?>
            <!-- Current Contract Callout -->
            <div style=" margin-bottom: 24px; padding: 14px 18px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: #ffffff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="fa-solid fa-bed"></i>
                    </div>
                    <div>
                        <div style="font-weight: 600; color: #1e40af; font-size: 0.9rem;">Chỗ ở nội trú hiện tại</div>
                        <div style="font-size: 0.85rem; color: #1e3a8a;">
                            <b>Phòng <?= htmlspecialchars($contract['MaPhong']) ?></b> &mdash; Giường <b><?= htmlspecialchars($contract['KyHieuGiuong']) ?></b>
                            (HĐ: <code><?= htmlspecialchars($contract['MaHopDong']) ?></code>)
                        </div>
                    </div>
                </div>
                <span class="badge badge-success"><i class="fa-solid fa-check"></i> Đang hiệu lực</span>
            </div>

            <!-- Form -->
            <form method="post" action="<?= url('student/requests') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="LoaiDon" value="<?= htmlspecialchars($serviceType) ?>">

                <div class="grid grid-cols-2" style="gap: 18px; margin-bottom: 20px;">
                    <!-- Trường 1: Sửa chữa thiết bị -->
                    <?php if ($repair): ?>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Thiết bị hư hỏng trong phòng <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-control" name="MaThietBi" required style="width: 100%;">
                                <option value="">-- Chọn thiết bị cần báo hỏng --</option>
                                <?php foreach ($equipment as $item): ?>
                                    <option value="<?= (int) $item['MaThietBi'] ?>">
                                        <?= htmlspecialchars($item['TenThietBi'] . (!empty($item['MaSoThietBi']) ? ' (' . $item['MaSoThietBi'] . ')' : '')) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div style="margin-top:14px"><label style="display:block;font-size:.85rem;font-weight:600;margin-bottom:6px">Ảnh hiện trường (không bắt buộc)</label><input class="form-control" type="file" name="HinhAnh" accept="image/jpeg,image/png,image/webp"><small class="text-muted">JPG, PNG hoặc WEBP, tối đa 5 MB.</small></div>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fa-solid fa-info-circle"></i> Nếu thiết bị chung phòng chưa có trong danh mục, hãy mô tả chi tiết ở phần nội dung bên dưới.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Trường 2: Chuyển phòng -->
                    <?php if ($transfer): ?>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Giường trống muốn chuyển đến <span style="color: #ef4444;">*</span>
                            </label>
                            <select class="form-control" name="MaGiuongChuyenDen" required style="width: 100%;">
                                <option value="">-- Chọn giường trống mong muốn --</option>
                                <?php foreach ($beds as $bed): ?>
                                    <?php if ($bed['MaGiuong'] !== $contract['MaGiuong']): ?>
                                        <option value="<?= htmlspecialchars($bed['MaGiuong']) ?>">
                                            Phòng <?= htmlspecialchars($bed['MaPhong']) ?> - Giường <?= htmlspecialchars($bed['KyHieuGiuong']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fa-solid fa-circle-info"></i> Hệ thống sẽ tự động đối soát và tính bù trừ chênh lệch đơn giá phòng tương ứng.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Trường 3: Tạm vắng -->
                    <?php if ($leave): ?>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Tạm vắng từ ngày <span style="color: #ef4444;">*</span>
                            </label>
                            <input class="form-control" type="date" name="TuNgay" required value="<?= date('Y-m-d') ?>">
                        </div>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Đến ngày dự kiến trở lại <span style="color: #ef4444;">*</span>
                            </label>
                            <input class="form-control" type="date" name="DenNgay" required value="<?= date('Y-m-d', strtotime('+7 days')) ?>">
                        </div>
                    <?php endif; ?>

                    <!-- Trường 4: Trả phòng -->
                    <?php if ($returnRoom): ?>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Ngày mong muốn trả phòng<span style="color: #ef4444;">*</span>
                            </label>
                            <input class="form-control" type="date" name="TuNgay" required value="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fa-solid fa-clock"></i> Sinh viên cần gửi trước ít nhất 3 ngày để Ban quản lý tòa nhà sắp xếp lịch kiểm kê tài sản.
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Trường 5: Hồ sơ miễn giảm -->
                    <?php if ($extension): ?>
                        <div style="grid-column: 1 / -1;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                                Tệp minh chứng chính sách (PDF, Ảnh chụp bản công chứng) <span style="color: #ef4444;">*</span>
                            </label>
                            <input class="form-control" type="file" name="TepMinhChung" accept=".jpg,.jpeg,.png,.pdf" required>
                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                <i class="fa-solid fa-file-pdf"></i> Chấp nhận file .PDF, .JPG, .PNG dung lượng tối đa 5MB (Sổ hộ nghèo, giấy xác nhận mồ côi, con thương binh...).
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Lý do / Nội dung chi tiết -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">
                        Nội dung chi tiết / Lý do gửi yêu cầu <span style="color: #ef4444;">*</span>
                    </label>
                    <textarea class="form-control" name="LyDo" minlength="10" required rows="5" placeholder="Vui lòng mô tả chi tiết lý do, hoàn cảnh hoặc hiện tượng hư hỏng để cán bộ xử lý nhanh chóng (tối thiểu 10 ký tự)..."></textarea>
                </div>

                <!-- Nút thao tác -->
                <div style="display: flex; gap: 12px; align-items: center; border-top: 1px solid var(--border); padding-top: 18px;">
                    <button class="btn btn-primary" type="submit" style="border-radius: 8px; padding: 10px 22px;">
                        <i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Gửi yêu cầu ngay
                    </button>
                    <a href="<?= url('student/requests') ?>" class="btn btn-outline" style="border-radius: 8px; padding: 10px 18px;">
                        Hủy bỏ
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = htmlspecialchars($serviceType) . ' - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
