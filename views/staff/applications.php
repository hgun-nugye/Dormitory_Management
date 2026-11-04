<?php
ob_start();

$formatTenToa = function(?string $name, string $code = '') {
    $str = trim((string)($name ?: $code));
    $cleaned = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', $str);
    return 'Tòa ' . (!empty($cleaned) ? $cleaned : $str);
};

$filterKhu = $filters['khu'] ?? '';
$filterCum = $filters['cum'] ?? '';
$filterToa = $filters['toa'] ?? '';
$filterStatus = $filters['status'] ?? '';
$searchKeyword = $filters['search'] ?? '';
$activeTab = $filters['tab'] ?? 'cho_duyet';
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-muted);">Công tác Sinh viên & Lưu trú</span>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Xét duyệt Đăng ký 72h</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Xét duyệt Đăng ký Chỗ ở & Hạn định 72h</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ quản trị') ?>
                    </span>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ ADMIN & ZONES: QUÉT 72H, IMPORT, EXPORT, VỀ DASHBOARD) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($isAllowed)): ?>
                    <?php if (!empty($scope['canScanRelease'])): ?>
                        <form action="<?= url('staff/applications/scan-release') ?>" method="POST" style="display: inline;" onsubmit="return confirm('Kích hoạt tiến trình ngầm kiểm tra và giải phóng toàn bộ các giường quá hạn thanh toán 72h?');">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-outline" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #b45309; border-color: #fde68a; background: #fffbeb;" title="Kích hoạt quét đối soát thu hồi các hồ sơ quá hạn 72h">
                                <i class="fa-solid fa-clock-rotate-left" style="margin-right: 6px; color: #d97706;"></i> Quét thu hồi 72h
                            </button>
                        </form>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline" onclick="openImportModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách xét duyệt đợt đăng ký">
                        <i class="fa-solid fa-file-import" style="margin-right: 6px; color: #2563eb;"></i> Nhập file
                    </button>
                    <button type="button" class="btn btn-outline" onclick="exportApplications()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách hồ sơ ra Excel">
                        <i class="fa-solid fa-file-excel" style="margin-right: 6px; color: #16a34a;"></i> Xuất Excel (<?= count($pendingApplications ?? []) + count($active72hApplications ?? []) ?>)
                    </button>
                <?php endif; ?>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 14px;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <b>Thành công:</b> <?= $flashSuccess ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <b>Cảnh báo:</b> <?= $flashWarning ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($isAllowed)): ?>
        <!-- ================================================================= -->
        <!-- MÀN HÌNH CHẶN 100% TRUY CẬP (DÀNH CHO NHÂN VIÊN KHÔNG PHẢI QUẢN LÝ) -->
        <!-- ================================================================= -->
        <div class="card" style="border: 2px solid #ef4444; background: #fef2f2; border-radius: 12px; padding: 40px 30px; text-align: center; margin-top: 24px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.12);">
            <div style="width: 76px; height: 76px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #dc2626; font-size: 2.2rem; border: 2px solid #fca5a5;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                Từ chối quyền truy cập
            </h2>
            <div style="max-width: 840px; margin: 0 auto 24px auto; font-size: 1.05rem; color: #b91c1c; line-height: 1.6; font-weight: 600;">
                Từ chối quyền truy cập: Phân hệ Xét duyệt Đăng ký & Giữ chỗ 72h chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX). Nhân viên Kế toán và Kỹ thuật không có quyền thao tác!
            </div>
            <div style="max-width: 600px; margin: 0 auto 24px auto; background: #ffffff; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 20px; text-align: left; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">Tài khoản hiện tại:</span>
                    <strong style="color: #0f172a;"><?= htmlspecialchars($user['HoVaTen'] ?? $user['TenDangNhap'] ?? 'Cán bộ') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">Vai trò / Chức danh:</span>
                    <span class="badge badge-danger" style="font-weight: 600;"><?= htmlspecialchars($roleName ?? $user['MaVaiTro'] ?? 'Nhân viên') ?></span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #64748b;">Trạng thái hệ thống:</span>
                    <span style="color: #dc2626; font-weight: 700;">ĐÃ CHẶN 100% - KHÔNG CÓ QUYỀN THAO TÁC</span>
                </div>
            </div>

            <div style="display: flex; justify-content: center; gap: 14px;">
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-primary" style="border-radius: 8px; padding: 10px 24px; font-weight: 600;">
                    <i class="fa-solid fa-house" style="margin-right: 6px;"></i> Về Trang chủ BQL
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- ================================================================= -->
        <!-- GIAO DIỆN CHÍNH THỨC DÀNH CHO CÁN BỘ QUẢN LÝ HỢP LỆ -->
        <!-- ================================================================= -->

        <!-- THẺ THỐNG KÊ KPI NHANH (QUICK STATS CARDS) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Chờ duyệt mới</span>
                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Hạn 24h</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #b45309;"><?= number_format($stats['pending'] ?? 0) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Cần cán bộ thẩm định chỗ ở</div>
            </div>

            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Đang giữ chỗ 72h</span>
                    <span style="background: #dbeafe; color: #1d4ed8; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Đếm ngược 72h</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1d4ed8;"><?= number_format($stats['active'] ?? 0) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Chờ sinh viên nộp lệ phí</div>
            </div>

            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Quá hạn 72h</span>
                    <span style="background: #fee2e2; color: #b91c1c; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Auto-Release</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #b91c1c;"><?= number_format($stats['expired'] ?? 0) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Đã giải phóng về quỹ phòng trống</div>
            </div>

            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 600;">Hoàn tất nhận phòng</span>
                    <span style="background: #dcfce7; color: #15803d; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Thành công</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #15803d;"><?= number_format($stats['completed'] ?? 0) ?></div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 4px;">Đã đóng tiền & check-in hợp lệ</div>
            </div>
        </div>

        <!-- BỘ LỌC ĐA TẦNG CASCADING FILTERS VÀ TÌM KIẾM TỨC THỜI -->
        <div class="card" style="margin-bottom: 24px; padding: 18px 20px; border-radius: 10px; border: 1px solid var(--border);">
            <form id="filterFormApplications" method="GET" action="<?= url('staff/applications') ?>">
                <input type="hidden" name="tab" id="currentActiveTab" value="<?= htmlspecialchars($activeTab) ?>">

                <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <!-- 1. Ô tìm kiếm thông minh -->
                    <div style="position: relative; flex: 1; min-width: 260px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($searchKeyword) ?>" class="form-control" placeholder="Tìm theo mã hồ sơ, MSSV, họ tên, SĐT, số phòng..." style="padding-left: 38px; border-radius: 8px; height: 38px;">
                    </div>

                    <!-- 2. Bộ lọc Khu KTX (Dành cho cấp Admin/Giám đốc) -->
                    <?php if ($scope['level'] === 'ALL'): ?>
                        <div style="min-width: 150px;">
                            <select name="khu" id="filterKhuSelect" class="form-control" style="border-radius: 8px; height: 38px;" onchange="onKhuChange(this.value)">
                                <option value="">-- Tất cả Khu KTX --</option>
                                <?php foreach ($availableKhus as $k): ?>
                                    <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= ($filterKhu === $k['MaKhu']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($k['TenKhu'] ?? ('Khu ' . $k['MaKhu'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- 3. Bộ lọc Cụm nhà (Dành cho cấp Trưởng Khu trở lên) -->
                    <?php if (in_array($scope['level'], ['ALL', 'KHU'], true)): ?>
                        <div style="min-width: 170px;">
                            <select name="cum" id="filterCumSelect" class="form-control" style="border-radius: 8px; height: 38px;" onchange="onCumChange(this.value)">
                                <option value="">-- Tất cả Cụm nhà --</option>
                                <?php foreach ($availableCums as $c): ?>
                                    <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($filterCum === $c['MaCum']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['TenCum'] ?? ('Cụm ' . $c['MaCum'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- 4. Bộ lọc Tòa nhà (Dành cho cấp Trưởng Cụm trở lên) -->
                    <?php if (in_array($scope['level'], ['ALL', 'KHU', 'CUM'], true)): ?>
                        <div style="min-width: 180px;">
                            <select name="toa" id="filterToaSelect" class="form-control" style="border-radius: 8px; height: 38px;" onchange="document.getElementById('filterFormApplications').submit()">
                                <option value="">-- Tất cả Tòa nhà --</option>
                                <?php foreach ($availableToas as $t): ?>
                                    <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" <?= ($filterToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- 5. Nút xóa bộ lọc -->
                    <?php if (!empty($searchKeyword) || !empty($filterKhu) || !empty($filterCum) || !empty($filterToa) || !empty($filterStatus)): ?>
                        <a href="<?= url('staff/applications?tab=' . urlencode($activeTab)) ?>" class="btn btn-outline" style="border-radius: 8px; height: 38px; display: inline-flex; align-items: center; color: #dc2626; border-color: #fca5a5; background: #fff5f5;" title="Xóa toàn bộ bộ lọc và trở về mặc định">
                            <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 6px;"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABS ĐIỀU HƯỚNG SUB-TABS XÉT DUYỆT -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
            <button type="button" class="tab-btn <?= ($activeTab === 'cho_duyet') ? 'active' : '' ?>" id="tabBtnPending" onclick="switchAppTab('cho_duyet')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 700; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'cho_duyet') ? 'var(--primary)' : 'var(--text-secondary)' ?>; background: <?= ($activeTab === 'cho_duyet') ? '#eff6ff' : 'transparent' ?>;">
                <i class="fa-solid fa-clock" style="margin-right: 6px;"></i> 1. Chờ xét duyệt (24h)
                <span class="badge" style="font-size:0.75rem; margin-left:6px; padding:3px 8px; background: #fef3c7; color: #92400e; border: none; font-weight: 700;"><?= count($pendingApplications ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'dang_giu_cho') ? 'active' : '' ?>" id="tabBtnActive72h" onclick="switchAppTab('dang_giu_cho')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'dang_giu_cho') ? 'var(--primary)' : 'var(--text-secondary)' ?>; background: <?= ($activeTab === 'dang_giu_cho') ? '#eff6ff' : 'transparent' ?>;">
                <i class="fa-solid fa-hourglass-half" style="margin-right: 6px;"></i> 2. Đang giữ chỗ 72h
                <span class="badge" style="font-size:0.75rem; margin-left:6px; padding:3px 8px; background: #dbeafe; color: #1e40af; border: none; font-weight: 700;"><?= count($active72hApplications ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'lich_su') ? 'active' : '' ?>" id="tabBtnHistory" onclick="switchAppTab('lich_su')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'lich_su') ? 'var(--primary)' : 'var(--text-secondary)' ?>; background: <?= ($activeTab === 'lich_su') ? '#eff6ff' : 'transparent' ?>;">
                <i class="fa-solid fa-clipboard-check" style="margin-right: 6px;"></i> 3. Lịch sử & Thu hồi 72h
                <span class="badge" style="font-size:0.75rem; margin-left:6px; padding:3px 8px; background: #f1f5f9; color: #475569; border: none; font-weight: 700;"><?= count($historyApplications ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'mien_giam') ? 'active' : '' ?>" id="tabBtnMienGiam" onclick="switchAppTab('mien_giam')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'mien_giam') ? 'var(--primary)' : 'var(--text-secondary)' ?>; background: <?= ($activeTab === 'mien_giam') ? '#eff6ff' : 'transparent' ?>;">
                <i class="fa-solid fa-certificate" style="margin-right: 6px;"></i> 4. Thẩm định Miễn giảm nội trú
                <span class="badge" style="font-size:0.75rem; margin-left:6px; padding:3px 8px; background: #dcfce7; color: #166534; border: none; font-weight: 700;"><?= count($mienGiamList ?? []) ?></span>
            </button>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 1: DANH SÁCH HỒ SƠ ĐĂNG KÝ MỚI CHỜ DUYỆT (24H) -->
        <!-- ===================================================================== -->
        <div id="tabContentPending" style="display: <?= ($activeTab === 'cho_duyet') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border-radius: 10px; border: 1px solid var(--border);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 16px;">
                    <div>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Danh sách Hồ sơ Đăng ký Chờ duyệt</span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 2px;">Thời hạn quy định thẩm định hồ sơ: trong vòng 24 giờ kể từ khi sinh viên nộp</span>
                    </div>
                    <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-size: 0.85rem; padding: 5px 12px; font-weight: 600;">
                        <?= count($pendingApplications ?? []) ?> hồ sơ chờ xử lý
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tablePendingApplications">
                        <thead>
                            <tr>
                                <th>Mã hồ sơ</th>
                                <th>Sinh viên đăng ký</th>
                                <th>Trường Đại học</th>
                                <th>Vị trí phòng & Giường</th>
                                <th>Thời gian nộp</th>
                                <th>Quy định</th>
                                <th style="text-align: right; width: 220px;">Thao tác phê duyệt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingApplications)): ?>
                                <?php foreach ($pendingApplications as $app): ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 0.95rem;">
                                                <?= htmlspecialchars($app['MaHoSo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($app['HoVaTen']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                MSSV: <b><?= htmlspecialchars($app['MSSVTaiTruong'] ?? $app['MaSinhVien']) ?></b> &bull; <?= htmlspecialchars($app['GioiTinh']) ?> &bull; SĐT: <?= htmlspecialchars($app['SoDienThoai'] ?? '---') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-secondary);"><?= htmlspecialchars($app['TenTruong'] ?? '---') ?></span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                Phòng <b><?= htmlspecialchars($app['SoPhong'] ?? '---') ?></b> (<?= htmlspecialchars($formatTenToa($app['TenToaNha'] ?? $app['MaToaNha'], $app['MaToaNha'])) ?>)
                                            </div>
                                            <div style="margin-top: 4px; display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border: none; font-size: 0.75rem; padding: 2px 8px; font-weight: 600;">
                                                    Giường <?= htmlspecialchars($app['KyHieuGiuong'] ?? $app['MaGiuong']) ?>
                                                </span>
                                                <span style="font-size: 0.75rem; color: var(--text-muted);">
                                                    <?= htmlspecialchars($app['TenLoaiPhong'] ?? '') ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                                <?= !empty($app['ThoiGianNop']) ? date('d/m/Y H:i', strtotime($app['ThoiGianNop'])) : '---' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #fef3c7; color: #92400e; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                <i class="fa-solid fa-stopwatch" style="margin-right: 4px;"></i> Hạn 72h sau duyệt
                                            </span>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận duyệt hồ sơ <?= htmlspecialchars($app['MaHoSo']) ?> và ấn định thời hạn 72h thanh toán cho sinh viên?');">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($app['MaHoSo']) ?>">
                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600; padding: 6px 12px; display: inline-flex; align-items: center;">
                                                    <i class="fa-solid fa-check" style="margin-right: 5px;"></i> Duyệt 72h
                                                </button>
                                            </form>

                                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-weight: 600; padding: 6px 12px; color: #dc2626; border-color: #fca5a5; background: #fff5f5; margin-left: 4px;" onclick="openRejectModal('<?= htmlspecialchars($app['MaHoSo']) ?>', '<?= htmlspecialchars(addslashes($app['HoVaTen'])) ?>')">
                                                <i class="fa-solid fa-xmark" style="margin-right: 4px;"></i> Từ chối
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 36px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 8px; color: #cbd5e1;"><i class="fa-solid fa-folder-open"></i></div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Hiện không có hồ sơ nào đang chờ duyệt trong phạm vi phụ trách.</div>
                                        <span style="font-size: 0.85rem;">Tất cả hồ sơ đăng ký chỗ ở đã được xử lý xong.</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 2: THEO DÕI HỒ SƠ ĐANG TRONG HẠN GIỮ CHỖ 72H (CHỜ THANH TOÁN) -->
        <!-- ===================================================================== -->
        <div id="tabContentActive72h" style="display: <?= ($activeTab === 'dang_giu_cho') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border-radius: 10px; border: 1px solid var(--border);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 16px;">
                    <div>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Theo dõi Hồ sơ Đang trong hạn Giữ chỗ 72h</span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 2px;">Tiến trình ngầm (Auto-Release) sẽ tự động thu hồi và mở lại giường Trống nếu sinh viên quá hạn thanh toán</span>
                    </div>
                    <span class="badge" style="background: #dbeafe; color: #1e40af; border: none; font-size: 0.85rem; padding: 5px 12px; font-weight: 600;">
                        <?= count($active72hApplications ?? []) ?> hồ sơ đang giữ chỗ
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableActive72hApplications">
                        <thead>
                            <tr>
                                <th>Mã hồ sơ</th>
                                <th>Sinh viên</th>
                                <th>Vị trí phòng & Giường</th>
                                <th>Lệ phí giữ chỗ</th>
                                <th>Thời gian duyệt</th>
                                <th>Hạn chót thanh toán</th>
                                <th style="width: 200px;">Thời gian còn lại (72h)</th>
                                <th style="text-align: right; width: 140px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($active72hApplications)): ?>
                                <?php foreach ($active72hApplications as $act): ?>
                                    <?php
                                    $phutConLai = (int)($act['SoPhutConLai'] ?? 0);
                                    $gio = floor($phutConLai / 60);
                                    $phut = $phutConLai % 60;
                                    // Tổng 72h = 4320 phút
                                    $percentElapsed = max(0, min(100, round((4320 - max(0, $phutConLai)) / 4320 * 100)));
                                    ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 0.95rem;">
                                                <?= htmlspecialchars($act['MaHoSo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($act['HoVaTen']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                MSSV: <b><?= htmlspecialchars($act['MSSVTaiTruong'] ?? $act['MaSinhVien']) ?></b> &bull; <?= htmlspecialchars($act['SoDienThoai'] ?? '---') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: #1e293b;">
                                                <?= htmlspecialchars($formatTenToa($act['TenToaNha'] ?? $act['MaToaNha'], $act['MaToaNha'])) ?> - P.<?= htmlspecialchars($act['SoPhong'] ?? '') ?>
                                            </div>
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; border: none; font-size: 0.75rem; padding: 2px 8px; font-weight: 600; margin-top: 4px; display: inline-block;">
                                                Giường <?= htmlspecialchars($act['KyHieuGiuong'] ?? $act['MaGiuong']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                                                <?= number_format($act['SoTienPhaiDong'] ?? 0, 0, ',', '.') ?> đ
                                            </div>
                                            <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-size: 0.72rem; padding: 2px 8px; font-weight: 600; margin-top: 3px; display: inline-block;">
                                                <?= htmlspecialchars($act['TrangThaiHoaDon'] ?? 'Chưa thanh toán') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-secondary);">
                                                <?= !empty($act['ThoiGianDuyet']) ? date('d/m/Y H:i', strtotime($act['ThoiGianDuyet'])) : '---' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #b45309; font-size: 0.9rem;">
                                                <?= !empty($act['HanChotThanhToan']) ? date('d/m/Y H:i', strtotime($act['HanChotThanhToan'])) : '---' ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if ($phutConLai > 24 * 60): ?>
                                                <span class="badge" style="background: #dbeafe; color: #1e40af; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-hourglass-start" style="margin-right: 4px;"></i> Còn <?= $gio ?>h <?= $phut ?>p
                                                </span>
                                            <?php elseif ($phutConLai > 0): ?>
                                                <span class="badge" style="background: #ffedd5; color: #c2410c; border: none; white-space: nowrap; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Còn <?= $gio ?>h <?= $phut ?>p
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; white-space: nowrap; font-weight: 700; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Đã quá hạn 72h
                                                </span>
                                            <?php endif; ?>

                                            <!-- Thanh tiến trình 72h trực quan -->
                                            <div style="width: 100%; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 6px;" title="Đã trôi qua <?= $percentElapsed ?>% thời hạn 72h">
                                                <div style="height: 100%; width: <?= $percentElapsed ?>%; background: <?= ($percentElapsed > 75) ? '#ef4444' : (($percentElapsed > 50) ? '#f59e0b' : '#3b82f6') ?>; border-radius: 3px;"></div>
                                            </div>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-weight: 600; padding: 6px 12px; color: #dc2626; border-color: #fca5a5; background: #fff5f5;" onclick="openCancelModal('<?= htmlspecialchars($act['MaHoSo']) ?>', '<?= htmlspecialchars(addslashes($act['HoVaTen'])) ?>')">
                                                <i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Hủy giữ chỗ
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 36px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2rem; margin-bottom: 8px; color: #cbd5e1;"><i class="fa-solid fa-check-double"></i></div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có hồ sơ nào đang trong trạng thái chờ thanh toán 72h.</div>
                                        <span style="font-size: 0.85rem;">Các vị trí giường giữ chỗ đều được đối soát và xử lý theo đúng tiến độ.</span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 3: LỊCH SỬ XỬ LÝ & THU HỒI 72H -->
        <!-- ===================================================================== -->
        <div id="tabContentHistory" style="display: <?= ($activeTab === 'lich_su') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border-radius: 10px; border: 1px solid var(--border);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 16px;">
                    <div>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Lịch sử Xét duyệt & Nhật ký Thu hồi Chỗ ở</span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 2px;">Các hồ sơ đã hoàn tất thanh toán hoặc tự động thu hồi quá hạn 72h</span>
                    </div>
                    <span class="badge" style="background: #f1f5f9; color: #475569; border: none; font-size: 0.85rem; padding: 5px 12px; font-weight: 600;">
                        <?= count($historyApplications ?? []) ?> bản ghi lịch sử
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableHistoryApplications">
                        <thead>
                            <tr>
                                <th>Mã hồ sơ</th>
                                <th>Sinh viên</th>
                                <th>Vị trí phòng & Giường</th>
                                <th>Lệ phí chỗ ở</th>
                                <th>Trạng thái hồ sơ</th>
                                <th>Ghi chú / Lý do</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($historyApplications)): ?>
                                <?php foreach ($historyApplications as $h): ?>
                                    <tr>
                                        <td>
                                            <span style="font-weight: 700; color: var(--primary); font-family: monospace; font-size: 0.95rem;">
                                                <?= htmlspecialchars($h['MaHoSo']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($h['HoVaTen']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                MSSV: <?= htmlspecialchars($h['MSSVTaiTruong'] ?? $h['MaSinhVien']) ?> &bull; <?= htmlspecialchars($h['SoDienThoai'] ?? '---') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($formatTenToa($h['TenToaNha'] ?? $h['MaToaNha'], $h['MaToaNha'])) ?> - P.<?= htmlspecialchars($h['SoPhong'] ?? '') ?><br>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; border: none; font-size: 0.75rem; padding: 2px 8px; margin-top: 2px; display: inline-block;">
                                                Giường <?= htmlspecialchars($h['KyHieuGiuong'] ?? $h['MaGiuong']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <b><?= number_format($h['SoTienPhaiDong'] ?? 0, 0, ',', '.') ?> đ</b>
                                        </td>
                                        <td>
                                            <?php if ($h['TrangThaiHoSo'] === 'Đã duyệt'): ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã duyệt / Hoàn tất
                                                </span>
                                            <?php elseif (in_array($h['TrangThaiHoSo'], ['Hết hạn - Đã hủy chỗ', 'Hết hạn thanh toán'])): ?>
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Quá hạn 72h - Đã thu hồi
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fef2f2; color: #991b1b; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> <?= htmlspecialchars($h['TrangThaiHoSo']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($h['GhiChuTuChoi'] ?? 'Xử lý qua cổng sinh viên') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 36px 16px; color: var(--text-muted);">
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có bản ghi lịch sử xử lý nào.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- TAB 4: THẨM ĐỊNH & XÉT DUYỆT HỒ SƠ MIỄN GIẢM NỘI TRÚ -->
        <!-- ===================================================================== -->
        <div id="tabContentMienGiam" style="display: <?= ($activeTab === 'mien_giam') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border-radius: 10px; border: 1px solid var(--border);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 14px; margin-bottom: 16px;">
                    <div>
                        <span style="font-size: 1.1rem; font-weight: 700; color: var(--text-primary);">Thẩm định Hồ sơ Chính sách Miễn giảm Nội trú</span>
                        <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-top: 2px;">Xét duyệt mức hỗ trợ 50% - 100% cho sinh viên diện chính sách, hộ nghèo, khuyết tật</span>
                    </div>
                    <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-size: 0.85rem; padding: 5px 12px; font-weight: 600;">
                        <?= count($mienGiamList ?? []) ?> hồ sơ chính sách
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableMienGiamApplications">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Sinh viên nộp</th>
                                <th>Hợp đồng & Phòng</th>
                                <th>Đối tượng chính sách</th>
                                <th>Minh chứng</th>
                                <th>Mức giảm đề xuất</th>
                                <th>Số tiền hoàn trả</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right; width: 180px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mienGiamList)): ?>
                                <?php foreach ($mienGiamList as $mg): ?>
                                    <tr>
                                        <td><b>MG-<?= htmlspecialchars($mg['MaMienGiam']) ?></b></td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($mg['HoVaTen']) ?></div>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                MSSV: <?= htmlspecialchars($mg['MSSVTaiTruong'] ?? $mg['MaSinhVien']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($mg['MaHopDong']) ?></span><br>
                                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                                <?= htmlspecialchars($mg['TenToaNha'] ?? '') ?> - P.<?= htmlspecialchars($mg['SoPhong'] ?? '---') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #eff6ff; color: #1d4ed8; border: none; font-size: 0.8rem; padding: 3px 8px; font-weight: 600;">
                                                <?= htmlspecialchars($mg['DoiTuongMienGiam']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($mg['TepMinhChung'])): ?>
                                                <a href="<?= htmlspecialchars($mg['TepMinhChung']) ?>" target="_blank" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; display: inline-flex; align-items: center;">
                                                    <i class="fa-solid fa-paperclip" style="margin-right: 4px;"></i> Xem tệp
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">Không có tệp</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <b style="color: #0f172a;"><?= (int)$mg['PhanTramGiam'] ?>%</b>
                                        </td>
                                        <td>
                                            <b style="color: #15803d;"><?= number_format($mg['SoTienHoanTra'] ?? 0, 0, ',', '.') ?> đ</b>
                                        </td>
                                        <td>
                                            <?php if ($mg['TrangThaiDuyet'] === 'Đã duyệt'): ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    Đã duyệt
                                                </span>
                                            <?php elseif ($mg['TrangThaiDuyet'] === 'Từ chối'): ?>
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    Từ chối
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fef3c7; color: #92400e; border: none; white-space: nowrap; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                    Chờ thẩm định
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <?php if ($mg['TrangThaiDuyet'] === 'Chờ duyệt'): ?>
                                                <form action="<?= url('staff/applications/approve-miengiam') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận phê duyệt hồ sơ miễn giảm nội trú MG-<?= htmlspecialchars($mg['MaMienGiam']) ?>?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="maMienGiam" value="<?= htmlspecialchars($mg['MaMienGiam']) ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600; padding: 5px 10px;">
                                                        ✓ Duyệt
                                                    </button>
                                                </form>

                                                <form action="<?= url('staff/applications/reject-miengiam') ?>" method="POST" style="display:inline; margin-left: 4px;" onsubmit="return confirm('Từ chối hồ sơ miễn giảm nội trú này?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="maMienGiam" value="<?= htmlspecialchars($mg['MaMienGiam']) ?>">
                                                    <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; font-weight: 600; padding: 5px 10px; color: #dc2626; border-color: #fca5a5; background: #fff5f5;">
                                                        ✕ Từ chối
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">Đã hoàn tất</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 36px 16px; color: var(--text-muted);">
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có hồ sơ miễn giảm chính sách nào.</div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- ===================================================================== -->
<!-- MODAL: TỪ CHỐI HỒ SƠ ĐĂNG KÝ (CÓ NHẬP LÝ DO CHUẨN) -->
<!-- ===================================================================== -->
<div id="modalRejectApplication" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 92%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fef2f2;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #991b1b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Từ chối hồ sơ đăng ký chỗ ở
            </h3>
            <button type="button" onclick="closeRejectModal()" style="border: none; background: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="<?= url('staff/reject-application') ?>" method="POST" id="formRejectApplication">
            <?= csrf_field() ?>
            <input type="hidden" name="maHoSo" id="modalRejectMaHoSo" value="">
            <div style="padding: 20px 24px;">
                <p style="margin-top: 0; color: #475569; font-size: 0.95rem;">
                    Xác nhận từ chối hồ sơ <b id="modalRejectLabelMaHoSo" style="color: #991b1b;"></b> của sinh viên <b id="modalRejectStudentName"></b>. Vị trí giường sẽ lập tức được giải phóng về quỹ phòng Trống.
                </p>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 6px; display: block;">Lý do từ chối hồ sơ:</label>
                    <select class="form-control" id="selectQuickReason" onchange="onQuickReasonChange(this.value)" style="border-radius: 8px; margin-bottom: 10px;">
                        <option value="">-- Chọn lý do mẫu hoặc tự nhập bên dưới --</option>
                        <option value="Sinh viên không thuộc đối tượng xét tuyển ưu tiên trong đợt này">Sinh viên không thuộc đối tượng xét tuyển ưu tiên trong đợt này</option>
                        <option value="Thông tin cá nhân / minh chứng nộp chưa chính xác, cần nộp lại">Thông tin cá nhân / minh chứng nộp chưa chính xác, cần nộp lại</option>
                        <option value="Sinh viên chủ động liên hệ hủy đăng ký để đổi nguyện vọng">Sinh viên chủ động liên hệ hủy đăng ký để đổi nguyện vọng</option>
                        <option value="Phòng đăng ký đang có kế hoạch sửa chữa đột xuất">Phòng đăng ký đang có kế hoạch sửa chữa đột xuất</option>
                    </select>
                    <textarea name="lyDo" id="modalRejectLyDo" class="form-control" rows="3" placeholder="Nhập chi tiết lý do từ chối để gửi thông báo cho sinh viên..." style="border-radius: 8px; width: 100%;" required></textarea>
                </div>
            </div>
            <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()" style="border-radius: 8px;">Đóng</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; background: #dc2626; border-color: #dc2626;">
                    Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL: HỦY GIỮ CHỖ 72H (CHO HỒ SƠ ĐANG CHỜ THANH TOÁN) -->
<!-- ===================================================================== -->
<div id="modalCancelActive" class="modal" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
    <div style="background: #ffffff; border-radius: 12px; max-width: 500px; width: 92%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2); overflow: hidden; border: 1px solid #e2e8f0;">
        <div style="padding: 18px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fffbeb;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #92400e; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Hủy giữ chỗ 72h trước hạn
            </h3>
            <button type="button" onclick="closeCancelModal()" style="border: none; background: none; font-size: 1.4rem; color: #94a3b8; cursor: pointer;">&times;</button>
        </div>
        <form action="<?= url('staff/reject-application') ?>" method="POST" id="formCancelActive">
            <?= csrf_field() ?>
            <input type="hidden" name="maHoSo" id="modalCancelMaHoSo" value="">
            <div style="padding: 20px 24px;">
                <p style="margin-top: 0; color: #475569; font-size: 0.95rem;">
                    Hủy giữ chỗ hồ sơ <b id="modalCancelLabelMaHoSo" style="color: #b45309;"></b> của sinh viên <b id="modalCancelStudentName"></b>. Vị trí giường và hóa đơn học kỳ tương ứng sẽ được giải phóng ngay lập tức.
                </p>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label style="font-weight: 600; font-size: 0.9rem; color: #334155; margin-bottom: 6px; display: block;">Lý do hủy giữ chỗ:</label>
                    <input type="text" name="lyDo" class="form-control" value="Sinh viên liên hệ xin rút hồ sơ / Hủy nguyện vọng giữ chỗ" style="border-radius: 8px;" required>
                </div>
            </div>
            <div style="padding: 14px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCancelModal()" style="border-radius: 8px;">Đóng</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 8px; background: #d97706; border-color: #d97706;">
                    Xác nhận hủy giữ chỗ
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchAppTab(tabName) {
    document.getElementById('currentActiveTab').value = tabName;
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.color = 'var(--text-secondary)';
        b.style.background = 'transparent';
        b.classList.remove('active');
    });

    document.getElementById('tabContentPending').style.display = 'none';
    document.getElementById('tabContentActive72h').style.display = 'none';
    document.getElementById('tabContentHistory').style.display = 'none';
    document.getElementById('tabContentMienGiam').style.display = 'none';

    if (tabName === 'cho_duyet') {
        const btn = document.getElementById('tabBtnPending');
        btn.style.color = 'var(--primary)';
        btn.style.background = '#eff6ff';
        btn.classList.add('active');
        document.getElementById('tabContentPending').style.display = 'block';
    } else if (tabName === 'dang_giu_cho') {
        const btn = document.getElementById('tabBtnActive72h');
        btn.style.color = 'var(--primary)';
        btn.style.background = '#eff6ff';
        btn.classList.add('active');
        document.getElementById('tabContentActive72h').style.display = 'block';
    } else if (tabName === 'lich_su') {
        const btn = document.getElementById('tabBtnHistory');
        btn.style.color = 'var(--primary)';
        btn.style.background = '#eff6ff';
        btn.classList.add('active');
        document.getElementById('tabContentHistory').style.display = 'block';
    } else if (tabName === 'mien_giam') {
        const btn = document.getElementById('tabBtnMienGiam');
        btn.style.color = 'var(--primary)';
        btn.style.background = '#eff6ff';
        btn.classList.add('active');
        document.getElementById('tabContentMienGiam').style.display = 'block';
    }
}

function onKhuChange(khuVal) {
    const cumSelect = document.getElementById('filterCumSelect');
    const toaSelect = document.getElementById('filterToaSelect');
    if (cumSelect) cumSelect.value = '';
    if (toaSelect) toaSelect.value = '';
    document.getElementById('filterFormApplications').submit();
}

function onCumChange(cumVal) {
    const toaSelect = document.getElementById('filterToaSelect');
    if (toaSelect) toaSelect.value = '';
    document.getElementById('filterFormApplications').submit();
}

function openRejectModal(maHoSo, studentName) {
    document.getElementById('modalRejectMaHoSo').value = maHoSo;
    document.getElementById('modalRejectLabelMaHoSo').textContent = maHoSo;
    document.getElementById('modalRejectStudentName').textContent = studentName;
    document.getElementById('modalRejectLyDo').value = '';
    document.getElementById('selectQuickReason').value = '';
    document.getElementById('modalRejectApplication').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('modalRejectApplication').style.display = 'none';
}

function onQuickReasonChange(val) {
    if (val) {
        document.getElementById('modalRejectLyDo').value = val;
    }
}

function openCancelModal(maHoSo, studentName) {
    document.getElementById('modalCancelMaHoSo').value = maHoSo;
    document.getElementById('modalCancelLabelMaHoSo').textContent = maHoSo;
    document.getElementById('modalCancelStudentName').textContent = studentName;
    document.getElementById('modalCancelActive').style.display = 'flex';
}

function closeCancelModal() {
    document.getElementById('modalCancelActive').style.display = 'none';
}

function openImportModal() {
    alert('Tính năng Nhập file đợt xét duyệt đang được kích hoạt.');
}

function exportApplications() {
    const urlParams = new URLSearchParams(window.location.search);
    alert('Đang kết xuất danh sách hồ sơ đăng ký ra tệp Excel (.xlsx)...');
}
</script>

<?php
$content = ob_get_clean();
$title = !empty($isAllowed)
    ? "Xét duyệt Đăng ký 72h - KTX ĐHQG-HCM"
    : "Từ chối truy cập - Xét duyệt 72h";
require dirname(__DIR__) . '/layouts/main.php';
?>
