<?php
ob_start();
?>

<div class="container">
    <div style="margin-bottom: 24px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Trang chủ</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Bảng điều khiển Quản trị trung tâm</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 0;">
            <div>
                <h1 class="page-title" style="display: flex; align-items: center; gap: 10px;">
                    Quản trị hệ thống KTX ĐHQG-HCM
                </h1>
            </div>
        </div>
    </div>

    <?php
    $flashError = $flashError ?? \App\Core\Session::flash('error');
    $flashSuccess = $flashSuccess ?? \App\Core\Session::flash('success');
    $flashWarning = $flashWarning ?? \App\Core\Session::flash('warning');
    ?>
    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; padding: 14px 18px; background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 18px; flex-shrink: 0;"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; padding: 14px 18px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #15803d; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i class="fa-solid fa-circle-check" style="font-size: 18px; flex-shrink: 0;"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="margin-bottom: 20px; padding: 14px 18px; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; border-radius: 8px; font-size: 14px; display: flex; align-items: center; gap: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <i class="fa-solid fa-circle-info" style="font-size: 18px; flex-shrink: 0;"></i>
            <span><?= htmlspecialchars($flashWarning) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-4" style="gap: 18px; margin-bottom: 28px;">
        <a href="<?= url('admin/users') ?>" style="text-decoration: none; color: inherit;">
            <div class="card stat-card" style="border-left: 4px solid #2563eb; transition: transform 0.15s ease, box-shadow 0.15s ease; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Cán bộ Ban Quản lý</div>
                        <div class="stat-val" style="color: #1e40af;"><?= number_format($stats['total_staff'] ?? 0) ?></div>
                    </div>
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                </div>
                <div class="stat-sub">
                    <b><?= number_format($stats['active_staff'] ?? $stats['total_staff'] ?? 0) ?></b> đang hoạt động &bull; Phân quyền BQL
                </div>
            </div>
        </a>

        <a href="<?= url('admin/universities') ?>" style="text-decoration: none; color: inherit;">
            <div class="card stat-card" style="border-left: 4px solid #10b981; transition: transform 0.15s ease, box-shadow 0.15s ease; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Trường Đại học liên kết</div>
                        <div class="stat-val" style="color: #15803d;"><?= number_format($stats['total_universities'] ?? 0) ?></div>
                    </div>
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
                <div class="stat-sub">
                    <b><?= number_format($stats['dhqg_universities'] ?? 0) ?></b> ĐHQG &bull; <b><?= number_format($stats['active_universities'] ?? $stats['total_universities'] ?? 0) ?></b> đang hợp tác
                </div>
            </div>
        </a>

        <a href="<?= url('admin/periods') ?>" style="text-decoration: none; color: inherit;">
            <div class="card stat-card" style="border-left: 4px solid #7c3aed; transition: transform 0.15s ease, box-shadow 0.15s ease; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Đợt tuyển sinh KTX</div>
                        <div class="stat-val" style="color: #6d28d9;"><?= number_format($stats['total_periods'] ?? count($dotDangKyList ?? [])) ?></div>
                    </div>
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                </div>
                <div class="stat-sub">
                    Cấu hình đăng ký & giữ chỗ 72h
                </div>
            </div>
        </a>

        <a href="<?= url('admin/students') ?>" style="text-decoration: none; color: inherit;">
            <div class="card stat-card" style="border-left: 4px solid #f59e0b; transition: transform 0.15s ease, box-shadow 0.15s ease; cursor: pointer;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div>
                        <div class="stat-label">Sinh viên nội trú</div>
                        <div class="stat-val" style="color: #b45309;"><?= number_format($stats['active_residents'] ?? 0) ?></div>
                    </div>
                    <div style="width: 38px; height: 38px; border-radius: 8px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
                <div class="stat-sub">
                    Trên tổng số <b><?= number_format($stats['total_students'] ?? 0) ?></b> tài khoản sinh viên &bull; Quản lý
                </div>
            </div>
        </a>
    </div>

    <div id="phan-quyen" class="card" style="margin-bottom: 26px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">

                <span>Quản lý tài khoản</span>
                <span class="badge badge-info" style="font-size: 0.775rem;">
                    <?= count($canBoList ?? []) ?> gần đây / <?= number_format($stats['total_staff'] ?? 0) ?> cán bộ
                </span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-list-check" style="margin-right: 6px;"></i> Quản lý toàn bộ cán bộ
                </a>
                <a href="<?= url('admin/users') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i>Thêm cán bộ mới
                </a>
            </div>
        </div>

        <?php if (!empty($canBoList)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Họ và tên cán bộ</th>
                            <th>Vai trò phân quyền</th>
                            <th>Phạm vi phụ trách</th>
                            <th>Số điện thoại & Email</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($canBoList as $cb): ?>
                            <?php
                            $isLocked = ((int)($cb['DangHoatDong'] ?? 1)) === 0;

                            $scopeLabel = 'Toàn hệ thống KTX';
                            if ($cb['MaCumQuanLy'] === 'KHU_A') {
                                $scopeLabel = 'Toàn bộ Khu A';
                            } elseif ($cb['MaCumQuanLy'] === 'KHU_B') {
                                $scopeLabel = 'Toàn bộ Khu B';
                            } elseif (!empty($cb['TenToaNha'])) {
                                $scopeLabel = $cb['TenToaNha'] . ' (' . $cb['MaCumQuanLy'] . ')';
                            } elseif (!empty($cb['TenCum'])) {
                                $scopeLabel = $cb['TenCum'] . ' (' . ($cb['TenKhu'] ?? 'KTX') . ')';
                            } elseif (!empty($cb['MaCumQuanLy'])) {
                                $scopeLabel = 'Phạm vi: ' . $cb['MaCumQuanLy'];
                            } elseif ($cb['MaVaiTro'] === 'TRUONG_KHU') {
                                $scopeLabel = 'Toàn bộ Khu KTX';
                            }

                            $badgeClass = 'badge-info';
                            if ($cb['MaVaiTro'] === 'ADMIN') $badgeClass = 'badge-danger';
                            elseif ($cb['MaVaiTro'] === 'TRUONG_KHU') $badgeClass = 'badge-warning';
                            elseif ($cb['MaVaiTro'] === 'TRUONG_CUM') $badgeClass = 'badge-success';
                            elseif ($cb['MaVaiTro'] === 'TRUONG_TOA') $badgeClass = 'badge-primary';
                            ?>
                            <tr style="<?= $isLocked ? 'background: #fef2f2;' : '' ?>">

                                <td>
                                    <b><?= htmlspecialchars($cb['HoVaTen'] ?? 'Cán bộ BQL') ?></b>
                                    <?php if (!empty($cb['ChucVu'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($cb['ChucVu']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($cb['TenVaiTro'] ?? $cb['MaVaiTro']) ?>
                                    </span>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($scopeLabel) ?></b>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($cb['SoDienThoai'] ?? '—') ?></div>
                                    <?php if (!empty($cb['Email'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($cb['Email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Đã khóa
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Hoạt động
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; text-decoration: none;" title="Chỉnh sửa phân quyền & tài khoản">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 4px;"></i> Phân quyền
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); font-size: 0.85rem; color: var(--text-muted); flex-wrap: wrap; gap: 8px;">

                <a href="<?= url('admin/users') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    Xem toàn bộ danh sách <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem; padding: 20px 0;">Chưa có tài khoản cán bộ nào trong hệ thống.</p>
        <?php endif; ?>
    </div>

    <div id="truong-hoc" class="card" style="margin-bottom: 26px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 8px;">

                <span>Danh mục Trường Đại học liên kết</span>
                <span class="badge badge-info" style="font-size: 0.775rem;">
                    <?= count($truongList ?? []) ?> tiêu biểu / <?= number_format($stats['total_universities'] ?? 0) ?> trường
                </span>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-list-check" style="margin-right: 6px;"></i> Quản lý toàn bộ danh mục
                </a>
                <a href="<?= url('admin/universities') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> Thêm trường mới
                </a>
            </div>
        </div>

        <?php if (!empty($truongList)): ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã trường</th>
                            <th>Tên trường đại học</th>
                            <th>Phân loại thành viên</th>
                            <th>Thời gian chuẩn</th>
                            <th>Sinh viên KTX</th>
                            <th>Trạng thái hợp tác</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($truongList as $t): ?>
                            <?php
                            $isDHQG = ((int)($t['ThuocDHQGHCM'] ?? 0)) === 1;
                            $isPaused = ((int)($t['DangHopTac'] ?? 1)) === 0;
                            $svCount = (int)($t['TongSinhVien'] ?? 0);
                            ?>
                            <tr style="<?= $isPaused ? 'background: #fef2f2;' : '' ?>">
                                <td>
                                    <b style="color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($t['MaTruong']) ?></b>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($t['TenTruong']) ?></b>
                                </td>
                                <td>
                                    <?php if ($isDHQG): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Thành viên ĐHQG-HCM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #475569;">
                                            <i class="fa-solid fa-handshake" style="margin-right: 4px;"></i> Ngoài ĐHQG-HCM
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($t['SoNamDaoTaoChuan'] ?? 4.0) ?> năm
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-user-graduate" style="margin-right: 4px;"></i> <?= number_format($svCount) ?> sinh viên
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$isPaused): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-dot" style="margin-right: 4px;"></i> Đang hợp tác
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-pause" style="margin-right: 4px;"></i> Tạm dừng
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; text-decoration: none;" title="Chỉnh sửa thông tin trường">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 4px;"></i> Chi tiết
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); font-size: 0.85rem; color: var(--text-muted); flex-wrap: wrap; gap: 8px;">

                <a href="<?= url('admin/universities') ?>" style="color: var(--primary); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    Xem toàn bộ danh mục trường<i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem; padding: 20px 0;">Chưa có danh mục trường đại học nào trong hệ thống.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản trị hệ thống - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>