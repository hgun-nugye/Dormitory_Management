<?php
ob_start();
$services = [
    ['Xin chuyển phòng', 'Đổi sang phòng hoặc giường phù hợp hơn', 'student/requests/transfer', 'fa-solid fa-right-left', 'primary'],
    ['Xin sửa chữa', 'Báo hỏng thiết bị trong phòng ở', 'student/requests/repair', 'fa-solid fa-screwdriver-wrench', 'warning'],
    ['Xin tạm vắng', 'Khai báo thời gian không ở KTX', 'student/requests/leave', 'fa-solid fa-person-walking-arrow-right', 'info'],
    ['Xin trả phòng', 'Kết thúc hợp đồng và trả lại chỗ ở', 'student/requests/return', 'fa-solid fa-door-open', 'danger'],
    ['Hồ sơ miễn giảm lệ phí', 'Nộp minh chứng xét miễn hoặc giảm phí phòng ở', 'student/requests/extension', 'fa-solid fa-file-circle-check', 'success'],
];
?>
<div class="container">
    <div class="page-header">
        <div>
            <h1 class="page-title">Cổng dịch vụ một cửa</h1>
            <p class="page-subtitle">Chọn đúng loại dịch vụ. Mỗi dịch vụ có biểu mẫu và quy trình xử lý riêng.</p>
        </div>
        <a href="<?= url('student/dashboard') ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Cổng sinh viên</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-2" style="gap:16px;margin-bottom:24px;">
        <?php foreach ($services as [$name, $description, $route, $icon]): ?>
            <a href="<?= url($route) ?>" class="card" style="text-decoration:none;color:inherit;min-height:120px;transition:transform .15s,box-shadow .15s;">
                <div style="display:flex;gap:14px;align-items:flex-start;">
                    <span class="badge badge-info" style="width:44px;height:44px;display:inline-flex;align-items:center;justify-content:center;font-size:18px;">
                        <i class="<?= $icon ?>"></i>
                    </span>
                    <span>
                        <strong style="display:block;color:var(--text-primary);margin-bottom:6px;"><?= htmlspecialchars($name) ?></strong>
                        <span class="text-muted"><?= htmlspecialchars($description) ?></span>
                        <span style="display:block;color:var(--primary);margin-top:12px;font-weight:600;">Mở biểu mẫu →</span>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-2">
        <div class="card">
            <div class="card-title">Đơn một cửa gần đây</div>
            <?php if (empty($requests)): ?><p class="text-muted">Chưa có đơn nào.</p><?php else: foreach ($requests as $item): ?>
                <div class="action-item">
                    <span class="action-name"><?= htmlspecialchars($item['LoaiDon']) ?></span>
                    <span class="action-desc"><?= htmlspecialchars($item['TrangThaiDuyet'].' — '.$item['LyDo']) ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="card">
            <div class="card-title">Phiếu báo hỏng gần đây</div>
            <?php if (empty($repairs)): ?><p class="text-muted">Chưa có phiếu báo hỏng.</p><?php else: foreach ($repairs as $item): ?>
                <div class="action-item">
                    <span class="action-name"><?= htmlspecialchars($item['MaPhieu']) ?></span>
                    <span class="action-desc"><?= htmlspecialchars($item['TrangThaiSua'].' — '.$item['MoTaHuHong']) ?></span>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require dirname(__DIR__) . '/layouts/main.php';
