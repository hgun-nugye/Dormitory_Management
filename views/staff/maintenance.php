<?php
ob_start();
?>

<div class="container" style="max-width: 1200px; margin: 0 auto; padding: 20px 15px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted, #64748b); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted, #64748b); text-decoration: none;">BQL Ký túc xá</a>
            <span>/</span>
            <span style="color: var(--text-primary, #1e293b); font-weight: 500;">Điều phối Sửa chữa & Phiếu Báo Hỏng</span>
        </div>

        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 1.6rem; color: var(--text-primary, #1e293b);">
                    🛠️ Điều phối Sửa chữa & Phiếu Báo Hỏng
                </h1>
                <p class="page-subtitle" style="margin: 5px 0 0 0; color: var(--text-muted, #64748b); font-size: 0.9rem;">
                    <strong>Phân hệ Kỹ thuật - Bảo trì:</strong> Phân cấp quyền hạn chặt chẽ (Tòa &bull; Cụm &bull; Khu &bull; Quản lý KTX); điều phối kỹ thuật viên đúng Khu và tự động hoàn nguyên thiết bị về <strong>'Tốt'</strong>.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <?php if ($scope['canCreateStaffTicket']): ?>
                    <button type="button" class="btn btn-primary" onclick="openStaffTicketModal()" style="border-radius: 8px; padding: 8px 16px; font-size: 0.88rem; background: #059669; border: none; color: #fff; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                        <span>➕</span> Lập phiếu sự cố hạ tầng chung
                    </button>
                <?php endif; ?>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px; text-decoration: none; padding: 8px 16px; border: 1px solid #cbd5e1; color: #334155;">
                    &larr; Về Dashboard
                </a>
            </div>
        </div>

        <!-- Thanh phím tắt chuyển nhanh các phân hệ nghiệp vụ Staff -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;">
            <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">📊 Tổng quan BQL</a>
            <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">🏢 1. Sơ đồ Phòng & Cư trú</a>
            <a href="<?= url('staff/applications') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; background: #2563eb; color: #fff; text-decoration: none;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #e2e8f0; color: #475569; text-decoration: none;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- HỘP THÔNG TIN VAI TRÒ & PHÂN QUYỀN ĐANG ĐĂNG NHẬP -->
    <div class="card" style="margin-bottom: 20px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.4rem;">
                <?= $scope['isTechnician'] ? '🔧' : '🛡️' ?>
            </span>
            <div>
                <div style="font-size: 0.88rem; color: #1e293b; font-weight: 600;">
                    Tài khoản: <strong><?= htmlspecialchars($user['HoVaTen'] ?? $user['TenDangNhap']) ?></strong> (<code><?= htmlspecialchars($user['TenDangNhap']) ?></code>)
                </div>
                <div style="font-size: 0.82rem; color: #64748b; margin-top: 2px;">
                    Vai trò: <strong style="color: #2563eb;"><?= htmlspecialchars($scope['title']) ?></strong>
                    <?php if ($scope['isTechnician']): ?>
                        &bull; <span style="color: #0284c7; font-weight: 600;">Chế độ Kỹ thuật viên: Xem phiếu, cập nhật tiến độ & nghiệm thu hoàn thành (Không phân công).</span>
                    <?php elseif ($scope['level'] === 'TOA'): ?>
                        &bull; <span style="color: #059669; font-weight: 600;">Chỉ xử lý phiếu Tòa <?= htmlspecialchars($scope['maToa']) ?>; được lập phiếu sự cố Tòa (cần cấp trên duyệt).</span>
                    <?php elseif ($scope['level'] === 'CUM'): ?>
                        &bull; <span style="color: #059669; font-weight: 600;">Quản lý Cụm <?= htmlspecialchars($scope['maCum']) ?>; duyệt phiếu SV & duyệt phiếu hạ tầng do Trưởng Tòa lập.</span>
                    <?php elseif ($scope['level'] === 'KHU'): ?>
                        &bull; <span style="color: #059669; font-weight: 600;">Quản lý toàn bộ Khu <?= htmlspecialchars($scope['maKhu']) ?>; duyệt mọi phiếu trong Khu.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <span class="badge" style="background: <?= $scope['isTechnician'] ? '#f1f5f9' : '#e0f2fe' ?>; color: <?= $scope['isTechnician'] ? '#475569' : '#0284c7' ?>; padding: 6px 12px; border-radius: 6px; font-weight: 600; font-size: 0.8rem;">
            <?= htmlspecialchars($scope['title']) ?>
        </span>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
    <?php if (!empty($flashSuccess)): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">✅</span>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #166534;">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">❌</span>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #991b1b;">&times;</button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 1.2rem;">⚠️</span>
                <span><?= htmlspecialchars($flashWarning) ?></span>
            </div>
            <button type="button" onclick="this.parentElement.style.display='none'" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: #92400e;">&times;</button>
        </div>
    <?php endif; ?>

    <!-- KHỐI 1: FORM ĐIỀU PHỐI & PHÂN CÔNG (CHỈ DÀNH CHO CÁN BỘ QUẢN LÝ, ẨN HOÀN TOÀN VỚI KỸ THUẬT VIÊN) -->
    <?php if ($scope['canAssign']): ?>
        <div class="card" style="margin-bottom: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                    <span>🛠️ Biểu mẫu Duyệt & Điều phối Kỹ thuật viên</span>
                    <span id="badgeCurrentKhuScope" class="badge" style="background: #e0f2fe; color: #0369a1; padding: 3px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">
                        <?= ($currentKhu !== 'all') ? "Phạm vi: Khu {$currentKhu}" : "Toàn hệ thống KTX" ?>
                    </span>
                </div>
                <span style="font-size: 0.8rem; color: #64748b;">
                    Duyệt phiếu & bắt buộc phân công Thợ đúng Khu phụ trách
                </span>
            </div>

            <form action="<?= url('staff/maintenance/assign') ?>" method="POST" id="formAssign">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">
                            1. Phiếu sự cố chờ xử lý (trong phạm vi phân quyền): <span style="color: #dc2626;">*</span>
                        </label>
                        <select name="maPhieu" id="selectMaPhieu" required onchange="onTicketChange()" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                            <option value="">-- Chọn phiếu báo hỏng cần xử lý --</option>
                            <?php if (!empty($pendingTickets)): ?>
                                <?php foreach ($pendingTickets as $pt): ?>
                                    <?php
                                    $isHaTang = ($pt['LoaiSuCo'] === 'HA_TANG');
                                    $typeTag = $isHaTang ? '[Hạ tầng chung]' : '[Phòng SV]';
                                    ?>
                                    <option value="<?= htmlspecialchars($pt['MaPhieu']) ?>" data-khu="<?= htmlspecialchars($pt['MaKhu'] ?? '') ?>" data-nv="<?= (int)($pt['NhanVienKyThuat'] ?? 0) ?>" data-type="<?= htmlspecialchars($pt['LoaiSuCo']) ?>">
                                        <?= $typeTag ?> [Tòa <?= htmlspecialchars($pt['FinalToaNha']) ?> &bull; Khu <?= htmlspecialchars($pt['MaKhu'] ?? 'A') ?>] <?= htmlspecialchars($pt['MaPhieu']) ?> - <?= htmlspecialchars($pt['TenThietBi'] ?? 'Hạng mục chung') ?> (<?= htmlspecialchars(mb_strimwidth($pt['MoTaHuHong'], 0, 28, '...')) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>(Hiện không có phiếu chờ xử lý trong phạm vi quản lý của bạn)</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">
                            2. Kỹ thuật viên phụ trách: <span style="color: #dc2626;">*</span>
                            <span id="techKhuNote" style="font-size: 0.75rem; font-weight: normal; color: #0284c7; margin-left: 4px;"></span>
                        </label>
                        <select name="maNhanVien" id="selectMaNhanVien" required style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                            <!-- Đổ tự động bằng JavaScript lọc theo Khu của phiếu -->
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #475569; margin-bottom: 6px;">
                            3. Trạng thái điều phối:
                        </label>
                        <select name="trangThai" id="selectTrangThai" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                            <option value="Đang sửa chữa" selected>Đang sửa chữa (Đồng bộ TB sang 'Đang bảo trì')</option>
                            <option value="Chờ xử lý">Chờ xử lý</option>
                            <option value="Đã hoàn thành">Đã hoàn thành (Hoàn nguyên TB về 'Tốt')</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                    <button type="submit" class="btn btn-primary" style="background: #2563eb; color: #fff; padding: 9px 20px; border-radius: 6px; border: none; font-size: 0.88rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                        <span>🛠️</span> Phê duyệt & Gán thợ kỹ thuật
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- KHỐI BỘ LỌC PHẠM VI & TÌM KIẾM -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
        <!-- Lọc theo Khu & Cụm nhà (Tùy theo phân quyền) -->
        <div>
            <?php if ($scope['level'] === 'TOA'): ?>
                <div style="font-size: 0.85rem; font-weight: 600; color: #1e40af; background: #dbeafe; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🏢</span> Phạm vi hiển thị: <strong><?= htmlspecialchars($scope['title']) ?></strong> (Khu <?= htmlspecialchars($scope['maKhu']) ?>)
                </div>
            <?php elseif ($scope['level'] === 'CUM'): ?>
                <div style="font-size: 0.85rem; font-weight: 600; color: #92400e; background: #fef3c7; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🏘️</span> Phạm vi hiển thị: <strong><?= htmlspecialchars($scope['title']) ?></strong>
                </div>
            <?php elseif ($scope['isTechnician']): ?>
                <div style="font-size: 0.85rem; font-weight: 600; color: #475569; background: #f1f5f9; padding: 7px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <span>🔧</span> Địa bàn kỹ thuật phụ trách: <strong>Khu <?= htmlspecialchars($scope['maKhu']) ?></strong>
                </div>
            <?php else: ?>
                <!-- Trưởng khu hoặc Quản trị viên: Có bộ lọc -->
                <form action="<?= url('staff/maintenance') ?>" method="GET" style="display: flex; align-items: center; gap: 10px; margin: 0; flex-wrap: wrap;">
                    <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
                    <label style="font-size: 0.85rem; font-weight: 600; color: #475569; white-space: nowrap;">Lọc theo:</label>
                    <select name="cum" onchange="this.form.submit()" style="padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; background: #fff;">
                        <?php if ($scope['canFilterAll']): ?>
                            <option value="all" <?= ($maCum === 'all') ? 'selected' : '' ?>>🏢 Tất cả các Khu / Cụm KTX</option>
                        <?php endif; ?>

                        <?php if ($scope['canFilterAll'] || $scope['maKhu'] === 'A'): ?>
                            <optgroup label="── KHU A (KTX Khu A) ──">
                                <option value="khu-A" <?= ($maCum === 'khu-A') ? 'selected' : '' ?>>📍 Toàn bộ KTX Khu A (Cụm AG, AH)</option>
                                <?php foreach ($clusters as $c): ?>
                                    <?php if ($c['MaKhu'] === 'A'): ?>
                                        <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($maCum === $c['MaCum']) ? 'selected' : '' ?>>
                                            &nbsp;&nbsp;&bull; Cụm <?= htmlspecialchars($c['MaCum']) ?> - <?= htmlspecialchars($c['TenCum']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>

                        <?php if ($scope['canFilterAll'] || $scope['maKhu'] === 'B'): ?>
                            <optgroup label="── KHU B (KTX Khu B) ──">
                                <option value="khu-B" <?= ($maCum === 'khu-B') ? 'selected' : '' ?>>📍 Toàn bộ KTX Khu B (Cụm BA, BB, BC, BD, BE)</option>
                                <?php foreach ($clusters as $c): ?>
                                    <?php if ($c['MaKhu'] === 'B'): ?>
                                        <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($maCum === $c['MaCum']) ? 'selected' : '' ?>>
                                            &nbsp;&nbsp;&bull; Cụm <?= htmlspecialchars($c['MaCum']) ?> - <?= htmlspecialchars($c['TenCum']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endif; ?>
                    </select>
                    <?php if (!empty($currentSearch)): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($currentSearch) ?>">
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </div>

        <!-- Ô Tìm kiếm nhanh -->
        <form action="<?= url('staff/maintenance') ?>" method="GET" style="display: flex; align-items: center; gap: 8px; margin: 0;">
            <input type="hidden" name="cum" value="<?= htmlspecialchars($maCum) ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($currentStatus) ?>">
            <input type="text" name="search" value="<?= htmlspecialchars($currentSearch) ?>" placeholder="Tìm mã phiếu, phòng, thiết bị, tên..." style="padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; width: 260px; background: #fff;">
            <button type="submit" class="btn btn-outline" style="padding: 7px 14px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; background: #f8fafc; cursor: pointer;">
                🔍 Tìm
            </button>
            <?php if (!empty($currentSearch)): ?>
                <a href="<?= url('staff/maintenance?cum=' . urlencode($maCum) . '&status=' . urlencode($currentStatus)) ?>" style="font-size: 0.82rem; color: #ef4444; text-decoration: none;">Xóa lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- KHỐI 2: THANH TABS TRẠNG THÁI & DANH SÁCH PHIẾU BÁO HỎNG -->
    <div class="card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
        <!-- Thanh Tabs -->
        <div style="display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 18px; flex-wrap: wrap;">
            <?php
            $tabAllUrl = url('staff/maintenance?cum=' . urlencode($maCum) . '&status=all' . ($currentSearch ? '&search=' . urlencode($currentSearch) : ''));
            $tabChoUrl = url('staff/maintenance?cum=' . urlencode($maCum) . '&status=cho-xu-ly' . ($currentSearch ? '&search=' . urlencode($currentSearch) : ''));
            $tabDangUrl = url('staff/maintenance?cum=' . urlencode($maCum) . '&status=dang-sua' . ($currentSearch ? '&search=' . urlencode($currentSearch) : ''));
            $tabXongUrl = url('staff/maintenance?cum=' . urlencode($maCum) . '&status=da-hoan-thanh' . ($currentSearch ? '&search=' . urlencode($currentSearch) : ''));
            $tabHuyUrl = url('staff/maintenance?cum=' . urlencode($maCum) . '&status=da-huy' . ($currentSearch ? '&search=' . urlencode($currentSearch) : ''));
            ?>
            <a href="<?= $tabAllUrl ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; <?= ($currentStatus === 'all') ? 'background: #2563eb; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                <span>Tất cả</span>
                <span style="background: <?= ($currentStatus === 'all') ? '#1d4ed8' : '#e2e8f0' ?>; color: <?= ($currentStatus === 'all') ? '#fff' : '#1e293b' ?>; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem;"><?= $counts['all'] ?></span>
            </a>

            <a href="<?= $tabChoUrl ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; <?= ($currentStatus === 'cho-xu-ly') ? 'background: #dc2626; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                <span>⏳ Chờ xử lý</span>
                <span style="background: <?= ($currentStatus === 'cho-xu-ly') ? '#b91c1c' : '#fee2e2' ?>; color: <?= ($currentStatus === 'cho-xu-ly') ? '#fff' : '#991b1b' ?>; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem;"><?= $counts['cho_xu_ly'] ?></span>
            </a>

            <a href="<?= $tabDangUrl ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; <?= ($currentStatus === 'dang-sua') ? 'background: #d97706; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                <span>🛠️ Đang sửa chữa</span>
                <span style="background: <?= ($currentStatus === 'dang-sua') ? '#b45309' : '#fef3c7' ?>; color: <?= ($currentStatus === 'dang-sua') ? '#fff' : '#92400e' ?>; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem;"><?= $counts['dang_sua'] ?></span>
            </a>

            <a href="<?= $tabXongUrl ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; <?= ($currentStatus === 'da-hoan-thanh') ? 'background: #16a34a; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                <span>✓ Đã hoàn thành</span>
                <span style="background: <?= ($currentStatus === 'da-hoan-thanh') ? '#15803d' : '#dcfce7' ?>; color: <?= ($currentStatus === 'da-hoan-thanh') ? '#fff' : '#166534' ?>; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem;"><?= $counts['da_hoan_thanh'] ?></span>
            </a>

            <a href="<?= $tabHuyUrl ?>" style="padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; <?= ($currentStatus === 'da-huy') ? 'background: #64748b; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                <span>✕ Đã hủy</span>
                <span style="background: <?= ($currentStatus === 'da-huy') ? '#475569' : '#e2e8f0' ?>; color: <?= ($currentStatus === 'da-huy') ? '#fff' : '#334155' ?>; padding: 2px 7px; border-radius: 10px; font-size: 0.75rem;"><?= $counts['da_huy'] ?></span>
            </a>
        </div>

        <!-- Bảng danh sách phiếu báo hỏng -->
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.86rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; text-align: left; color: #475569;">
                        <th style="padding: 12px 10px;">Mã Phiếu & Loại</th>
                        <th style="padding: 12px 10px;">Vị trí & Khu</th>
                        <th style="padding: 12px 10px;">Hạng mục / Thiết bị</th>
                        <th style="padding: 12px 10px;">Nội dung sự cố & Người báo</th>
                        <th style="padding: 12px 10px;">Kỹ thuật phụ trách</th>
                        <th style="padding: 12px 10px;">Trạng thái</th>
                        <th style="padding: 12px 10px; text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tickets)): ?>
                        <?php foreach ($tickets as $t): ?>
                            <?php
                            $rowBg = '#ffffff';
                            if ($t['TrangThaiSua'] === 'Đã hoàn thành') {
                                $rowBg = '#f0fdf4';
                            } elseif ($t['TrangThaiSua'] === 'Đã hủy') {
                                $rowBg = '#f8fafc';
                            }
                            $ticketKhu = $t['MaKhu'] ?? 'A';
                            $isHaTang = ($t['LoaiSuCo'] === 'HA_TANG');
                            $canManagerApproveThis = $scope['canAssign'] && (!$isHaTang || $scope['canApproveStaffTicket']);
                            ?>
                            <tr style="background: <?= $rowBg ?>; border-bottom: 1px solid #f1f5f9;">
                                <!-- Cột 1: Mã phiếu & Loại sự cố -->
                                <td style="padding: 12px 10px; vertical-align: top;">
                                    <strong style="color: #1e293b; font-family: monospace; font-size: 0.95rem;"><?= htmlspecialchars($t['MaPhieu']) ?></strong>
                                    <div style="margin-top: 4px;">
                                        <?php if ($isHaTang): ?>
                                            <span class="badge" style="background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                                🏢 Hạ tầng chung
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                                👤 Sinh viên báo
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                                        <?= date('d/m/Y H:i', strtotime($t['NgayBao'])) ?>
                                    </div>
                                </td>

                                <!-- Cột 2: Vị trí & Khu -->
                                <td style="padding: 12px 10px; vertical-align: top;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <?php if (!empty($t['MaPhong'])): ?>
                                            <strong style="color: #2563eb; font-size: 0.95rem;">P.<?= htmlspecialchars($t['MaPhong']) ?></strong>
                                        <?php else: ?>
                                            <strong style="color: #d97706; font-size: 0.88rem;">Khu vực chung</strong>
                                        <?php endif; ?>
                                        <span class="badge" style="background: <?= ($ticketKhu === 'A') ? '#dbeafe' : '#fef3c7' ?>; color: <?= ($ticketKhu === 'A') ? '#1e40af' : '#92400e' ?>; padding: 1px 6px; border-radius: 4px; font-size: 0.72rem; font-weight: 700;">
                                            Khu <?= htmlspecialchars($ticketKhu) ?>
                                        </span>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #475569; margin-top: 2px;">
                                        Tòa <strong><?= htmlspecialchars($t['FinalToaNha']) ?></strong> &bull; Cụm <?= htmlspecialchars($t['MaCum'] ?? 'N/A') ?>
                                    </div>
                                </td>

                                <!-- Cột 3: Hạng mục / Thiết bị -->
                                <td style="padding: 12px 10px; vertical-align: top;">
                                    <?php if (!empty($t['TenThietBi'])): ?>
                                        <div style="font-weight: 600; color: #1e293b;">
                                            <?= htmlspecialchars($t['TenThietBi']) ?>
                                        </div>
                                        <?php if (!empty($t['MaSoThietBi'])): ?>
                                            <div style="font-size: 0.75rem; color: #64748b;">
                                                Tem: <code><?= htmlspecialchars($t['MaSoThietBi']) ?></code>
                                            </div>
                                        <?php endif; ?>
                                        <div style="margin-top: 4px;">
                                            <?php
                                            $tbStatusColor = '#16a34a';
                                            if ($t['TinhTrangThietBi'] === 'Đang báo hỏng') $tbStatusColor = '#dc2626';
                                            elseif ($t['TinhTrangThietBi'] === 'Đang bảo trì') $tbStatusColor = '#d97706';
                                            elseif ($t['TinhTrangThietBi'] === 'Hỏng') $tbStatusColor = '#7f1d1d';
                                            ?>
                                            <span style="font-size: 0.72rem; padding: 2px 6px; border-radius: 4px; background: #f1f5f9; color: <?= $tbStatusColor ?>; font-weight: 600;">
                                                TB: <?= htmlspecialchars($t['TinhTrangThietBi'] ?? 'Tốt') ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div style="font-weight: 600; color: #334155;">Hạ tầng công cộng</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">(Tường, cây xanh, điện chiếu sáng...)</div>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 4: Nội dung sự cố & Người báo & Tiến độ -->
                                <td style="padding: 12px 10px; vertical-align: top; max-width: 320px;">
                                    <div style="color: #1e293b; line-height: 1.4; margin-bottom: 6px; font-weight: 500;">
                                        <?= nl2br(htmlspecialchars($t['MoTaHuHong'])) ?>
                                    </div>

                                    <!-- Thông tin người lập -->
                                    <div style="font-size: 0.78rem; color: #64748b;">
                                        <?php if ($isHaTang): ?>
                                            🏢 Lập bởi: <strong><?= htmlspecialchars($t['TenNhanVienBao'] ?? $t['MaNhanVienBao'] ?? 'Cán bộ quản lý') ?></strong>
                                            <?php if (!empty($t['ChucVuNhanVienBao'])): ?>
                                                (<?= htmlspecialchars($t['ChucVuNhanVienBao']) ?>)
                                            <?php endif; ?>
                                        <?php else: ?>
                                            👤 Báo bởi: <strong><?= htmlspecialchars($t['TenSinhVien'] ?? $t['MaSinhVienBao']) ?></strong>
                                            <?php if (!empty($t['SdtSinhVien'])): ?>
                                                &bull; 📞 <?= htmlspecialchars($t['SdtSinhVien']) ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Thông tin người duyệt -->
                                    <?php if (!empty($t['TenNguoiDuyet'])): ?>
                                        <div style="font-size: 0.75rem; color: #059669; margin-top: 3px;">
                                            ✓ Phê duyệt bởi: <strong><?= htmlspecialchars($t['TenNguoiDuyet']) ?></strong>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Ghi chú tiến độ KTV -->
                                    <?php if (!empty($t['GhiChuTienDo'])): ?>
                                        <div style="margin-top: 6px; font-size: 0.75rem; color: #0284c7; background: #f0f9ff; border: 1px solid #bae6fd; padding: 4px 8px; border-radius: 4px;">
                                            <strong>Tiến độ KTV:</strong> <?= htmlspecialchars($t['GhiChuTienDo']) ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Ảnh hiện trường -->
                                    <?php if (!empty($t['HinhAnh'])): ?>
                                        <div style="margin-top: 6px;">
                                            <a href="javascript:void(0)" onclick="viewImage('<?= htmlspecialchars($t['HinhAnh']) ?>')" style="font-size: 0.75rem; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                📷 Xem ảnh hiện trường
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 5: Kỹ thuật phụ trách (Kèm Khu) -->
                                <td style="padding: 12px 10px; vertical-align: top;">
                                    <?php if (!empty($t['TenTho'])): ?>
                                        <div style="font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 6px;">
                                            <span><?= htmlspecialchars($t['TenTho']) ?></span>
                                            <?php if (!empty($t['TechKhu'])): ?>
                                                <span class="badge" style="background: #e2e8f0; color: #334155; padding: 1px 5px; border-radius: 4px; font-size: 0.7rem;">
                                                    Khu <?= htmlspecialchars($t['TechKhu']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: #64748b;">
                                            <?= htmlspecialchars($t['ChucVuTho'] ?? 'Nhân viên kỹ thuật') ?>
                                        </div>
                                        <?php if (!empty($t['SdtTho'])): ?>
                                            <div style="font-size: 0.75rem; color: #475569;">
                                                📞 <?= htmlspecialchars($t['SdtTho']) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                            Chưa phân công
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 6: Trạng thái sửa chữa -->
                                <td style="padding: 12px 10px; vertical-align: top;">
                                    <?php if ($t['TrangThaiSua'] === 'Chờ xử lý'): ?>
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                            ⏳ Chờ xử lý
                                        </span>
                                    <?php elseif ($t['TrangThaiSua'] === 'Đang sửa chữa'): ?>
                                        <span class="badge" style="background: #fef3c7; color: #92400e; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                            🛠️ Đang sửa chữa
                                        </span>
                                    <?php elseif ($t['TrangThaiSua'] === 'Đã hoàn thành'): ?>
                                        <span class="badge" style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                            ✓ Đã hoàn thành
                                        </span>
                                    <?php elseif ($t['TrangThaiSua'] === 'Đã hủy'): ?>
                                        <span class="badge" style="background: #e2e8f0; color: #475569; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                            ✕ Đã hủy
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #334155; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; display: inline-block;">
                                            <?= htmlspecialchars($t['TrangThaiSua']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Cột 7: Thao tác theo cấp bậc -->
                                <td style="padding: 12px 10px; vertical-align: top; text-align: right;">
                                    <div style="display: flex; flex-direction: column; gap: 6px; align-items: flex-end;">
                                        <!-- DÀNH CHO KỸ THUẬT VIÊN: Cập nhật tiến độ & Hoàn thành -->
                                        <?php if ($scope['isTechnician']): ?>
                                            <?php if (in_array($t['TrangThaiSua'], ['Chờ xử lý', 'Đang sửa chữa'])): ?>
                                                <button type="button" class="btn btn-outline btn-sm" onclick="openProgressModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars($t['GhiChuTienDo'] ?? '') ?>', '<?= htmlspecialchars($t['TrangThaiSua']) ?>')" style="padding: 4px 10px; border-radius: 5px; border: 1px solid #0284c7; color: #0284c7; background: #fff; font-size: 0.78rem; cursor: pointer;">
                                                    ⚙️ Cập nhật tiến độ
                                                </button>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="openCompleteModal('<?= htmlspecialchars($t['MaPhieu']) ?>')" style="background: #16a34a; border: none; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                                    ✓ Nghiệm thu xong
                                                </button>
                                            <?php else: ?>
                                                <span style="font-size: 0.78rem; color: #166534; font-style: italic;">Đã hoàn thành</span>
                                            <?php endif; ?>

                                        <!-- DÀNH CHO CÁN BỘ QUẢN LÝ (TRƯỞNG TÒA, CỤM, KHU, ADMIN) -->
                                        <?php else: ?>
                                            <?php if (in_array($t['TrangThaiSua'], ['Chờ xử lý', 'Đang sửa chữa'])): ?>
                                                <!-- Nút Duyệt & Phân công -->
                                                <?php if ($canManagerApproveThis): ?>
                                                    <button type="button" class="btn btn-outline btn-sm" onclick="openAssignModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars($t['MaPhong'] ?? 'Hạ tầng chung') ?>', '<?= htmlspecialchars($t['TenThietBi'] ?? 'Hạng mục chung') ?>', '<?= htmlspecialchars($ticketKhu) ?>', '<?= (int)($t['NhanVienKyThuat'] ?? 0) ?>')" style="padding: 4px 10px; border-radius: 5px; border: 1px solid #cbd5e1; background: #fff; font-size: 0.78rem; cursor: pointer; color: #2563eb; font-weight: 600;">
                                                        🔧 Duyệt & Phân công
                                                    </button>
                                                <?php elseif ($isHaTang && $scope['level'] === 'TOA'): ?>
                                                    <span style="font-size: 0.75rem; color: #d97706; background: #fef3c7; padding: 2px 6px; border-radius: 4px;">
                                                        ⏳ Chờ Cấp trên duyệt
                                                    </span>
                                                <?php endif; ?>

                                                <!-- Nút Nghiệm thu hoàn thành -->
                                                <button type="button" class="btn btn-primary btn-sm" onclick="openCompleteModal('<?= htmlspecialchars($t['MaPhieu']) ?>')" style="background: #16a34a; border: none; color: #fff; padding: 5px 10px; border-radius: 5px; font-size: 0.78rem; font-weight: 600; cursor: pointer;">
                                                    ✓ Hoàn thành
                                                </button>

                                                <!-- Nút Hủy phiếu (Trưởng Tòa không tự hủy phiếu hạ tầng do mình tạo mà phải cấp trên duyệt) -->
                                                <?php if (!($isHaTang && $scope['level'] === 'TOA')): ?>
                                                    <button type="button" onclick="openCancelModal('<?= htmlspecialchars($t['MaPhieu']) ?>')" style="background: none; border: none; color: #dc2626; font-size: 0.75rem; text-decoration: underline; cursor: pointer; padding: 2px;">
                                                        Hủy phiếu
                                                    </button>
                                                <?php endif; ?>

                                            <?php elseif ($t['TrangThaiSua'] === 'Đã hoàn thành'): ?>
                                                <span style="font-size: 0.78rem; color: #166534; font-weight: 500; font-style: italic;">
                                                    ✓ Đã hoàn tất (TB Tốt)
                                                </span>
                                            <?php else: ?>
                                                <span style="font-size: 0.78rem; color: #94a3b8; font-style: italic;">
                                                    Đã đóng
                                                </span>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px 20px; color: #64748b;">
                                <div style="font-size: 2rem; margin-bottom: 8px;">📋</div>
                                <div style="font-weight: 600; font-size: 1rem; color: #334155;">Không có phiếu báo hỏng nào trong phạm vi quản lý của bạn</div>
                                <div style="font-size: 0.85rem; margin-top: 4px; color: #64748b;">
                                    <?php if ($scope['level'] === 'TOA'): ?>
                                        Hệ thống đang khóa lọc phạm vi Tòa nhà <?= htmlspecialchars($scope['maToa']) ?>.
                                    <?php elseif ($scope['level'] === 'CUM'): ?>
                                        Hệ thống đang khóa lọc phạm vi Cụm <?= htmlspecialchars($scope['maCum']) ?>.
                                    <?php elseif ($scope['level'] === 'KHU' || $scope['isTechnician']): ?>
                                        Hệ thống đang khóa lọc phạm vi Khu <?= htmlspecialchars($scope['maKhu']) ?>.
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL 1: CÁN BỘ LẬP PHIẾU SỰ CỐ HẠ TẦNG CHUNG -->
<div id="staffTicketModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 540px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>➕</span> Lập Phiếu Sự Cố Hạ Tầng / Thiết Bị Chung
            </h3>
            <button type="button" onclick="closeStaffTicketModal()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <p style="font-size: 0.85rem; color: #64748b; margin-top: 0; margin-bottom: 16px; line-height: 1.5;">
            Dành cho cán bộ ghi nhận sự cố công cộng (nứt tường, hư cây, đèn hành lang, vỡ kính, hỏng cổng...). Phiếu sẽ được chuyển lên cấp trên phê duyệt & cử thợ.
        </p>

        <form action="<?= url('staff/maintenance/create-staff-ticket') ?>" method="POST">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Tòa nhà xảy ra sự cố: <span style="color: #dc2626;">*</span>
                </label>
                <select name="maToaNha" required style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <?php foreach ($buildings as $b): ?>
                        <option value="<?= htmlspecialchars($b['MaToaNha']) ?>">
                            Tòa <?= htmlspecialchars($b['MaToaNha']) ?> - <?= htmlspecialchars($b['TenToaNha']) ?> (Cụm <?= htmlspecialchars($b['MaCum']) ?> &bull; Khu <?= htmlspecialchars($b['MaKhu']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Vị trí chi tiết / Phòng (nếu có):
                </label>
                <input type="text" name="maPhong" placeholder="Ví dụ: Hành lang Tầng 3, Sảnh chính, Ban công, Bồn nước..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Mô tả chi tiết hiện trường & mức độ hư hỏng: <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="moTa" rows="3" required placeholder="Mô tả cụ thể sự cố (ví dụ: Nứt tường ban công tầng 2 có nguy cơ thấm dột, cây đổ gãy cành chắn lối đi...)" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Đường dẫn ảnh hiện trường (tùy chọn):</label>
                <input type="text" name="hinhAnh" placeholder="/uploads/baohong/sư_co_tuong.jpg" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeStaffTicketModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #059669; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">➕ Gửi phiếu chờ phê duyệt</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: DUYỆT & PHÂN CÔNG KỸ THUẬT VIÊN THEO ĐÚNG KHU -->
<div id="assignModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 500px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>🔧</span> Duyệt & Phân công Kỹ thuật viên
            </h3>
            <button type="button" onclick="closeAssignModal()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 0.85rem; line-height: 1.6;">
            <div><strong>Mã phiếu:</strong> <span id="modalMaPhieuText" style="font-family: monospace; font-weight: bold; color: #1e293b;"></span></div>
            <div><strong>Vị trí:</strong> <span id="modalMaPhongText" style="font-weight: bold; color: #2563eb;"></span> &bull; <span id="modalMaKhuBadge" class="badge" style="background: #dbeafe; color: #1e40af; padding: 1px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: bold;"></span></div>
            <div><strong>Hạng mục:</strong> <span id="modalTenTBText" style="font-weight: 500; color: #334155;"></span></div>
        </div>

        <form action="<?= url('staff/maintenance/assign') ?>" method="POST">
            <input type="hidden" name="maPhieu" id="modalMaPhieu">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Cán bộ kỹ thuật phụ trách: <span style="color: #dc2626;">*</span>
                    <span id="modalTechKhuNote" style="font-size: 0.75rem; font-weight: normal; color: #0284c7; margin-left: 4px;"></span>
                </label>
                <select name="maNhanVien" id="modalSelectMaNhanVien" required style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <!-- Đổ tự động theo Khu của phiếu qua JS -->
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Trạng thái điều phối:</label>
                <select name="trangThai" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="Đang sửa chữa" selected>Đang sửa chữa (Đồng bộ TB sang 'Đang bảo trì')</option>
                    <option value="Chờ xử lý">Chờ xử lý</option>
                    <option value="Đã hoàn thành">Đã hoàn thành (Hoàn nguyên TB về 'Tốt')</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAssignModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Đóng</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #2563eb; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">🛠️ Phê duyệt & Gán thợ</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: KỸ THUẬT VIÊN CẬP NHẬT TIẾN ĐỘ -->
<div id="progressModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; font-size: 1.15rem; color: #1e293b;">⚙️ Cập nhật Tiến độ Sửa chữa</h3>
        <p style="font-size: 0.85rem; color: #475569; margin-bottom: 16px;">
            Nhập ghi chú hiện trường (vật tư thay thế, tình trạng, hẹn giờ sinh viên...) để cán bộ BQL cùng theo dõi.
        </p>
        <form action="<?= url('staff/maintenance/update-progress') ?>" method="POST">
            <input type="hidden" name="maPhieu" id="progressMaPhieu">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Ghi chú tiến độ KTV: <span style="color: #dc2626;">*</span></label>
                <textarea name="ghiChu" id="progressGhiChu" rows="3" required placeholder="Ví dụ: Đã kiểm tra quạt trần, thay tụ điện mới; đang theo dõi chạy thử..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Cập nhật trạng thái:</label>
                <select name="trangThai" id="progressTrangThai" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="Đang sửa chữa">Đang sửa chữa</option>
                    <option value="Chờ xử lý">Chờ linh kiện / Chờ xử lý</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeProgressModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Đóng</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #0284c7; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Lưu tiến độ</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: NGHIỆM THU HOÀN THÀNH -->
<div id="completeModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; font-size: 1.15rem; color: #166534;">✓ Nghiệm thu Hoàn thành Sửa chữa</h3>
        <p style="font-size: 0.88rem; color: #475569; line-height: 1.5;">
            Xác nhận phiếu <strong id="completeMaPhieuText"></strong> đã được xử lý xong. Hệ thống sẽ <strong>tự động hoàn nguyên thiết bị về trạng thái 'Tốt'</strong>.
        </p>
        <form action="<?= url('staff/maintenance/complete') ?>" method="POST">
            <input type="hidden" name="maPhieu" id="completeMaPhieu">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Ghi chú nghiệm thu (tùy chọn):</label>
                <textarea name="ghiChu" rows="2" placeholder="Ví dụ: Đã thay linh kiện mới, kiểm tra hoạt động tốt 100%..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeCompleteModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Đóng</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #16a34a; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Xác nhận Hoàn thành</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 5: HỦY PHIẾU BÁO HỎNG -->
<div id="cancelModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <h3 style="margin-top: 0; font-size: 1.15rem; color: #1e293b;">✕ Xác nhận Hủy phiếu sự cố</h3>
        <p style="font-size: 0.88rem; color: #475569; line-height: 1.5;">
            Phiếu sự cố sẽ được chuyển sang trạng thái <strong>'Đã hủy'</strong>. Thiết bị tương ứng (nếu có) sẽ được hoàn trả về trạng thái bình thường.
        </p>
        <form action="<?= url('staff/maintenance/cancel') ?>" method="POST">
            <input type="hidden" name="maPhieu" id="cancelMaPhieu">
            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Lý do hủy (tùy chọn):</label>
                <textarea name="lyDo" rows="3" placeholder="Nhập lý do hủy phiếu (ví dụ: Sinh viên báo nhầm, đã tự khắc phục...)" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeCancelModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Đóng</button>
                <button type="submit" style="padding: 8px 16px; border-radius: 6px; border: none; background: #dc2626; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Xác nhận Hủy</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 6: XEM ẢNH HIỆN TRƯỜNG -->
<div id="imageModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.75); align-items: center; justify-content: center;" onclick="this.style.display='none'">
    <div style="background: #fff; border-radius: 8px; max-width: 600px; width: 90%; padding: 16px; position: relative;" onclick="event.stopPropagation()">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
            <strong style="font-size: 0.95rem; color: #1e293b;">Ảnh chụp sự cố hiện trường</strong>
            <button type="button" onclick="document.getElementById('imageModal').style.display='none'" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        <div style="text-align: center; max-height: 480px; overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #0f172a; border-radius: 6px; padding: 10px;">
            <img id="imgViewer" src="" alt="Hiện trường sự cố" style="max-width: 100%; max-height: 430px; object-fit: contain;" onerror="this.style.display='none'; document.getElementById('imgFallback').style.display='block';">
            <div id="imgFallback" style="display: none; color: #94a3b8; font-size: 0.85rem; padding: 30px 20px;">
                📷 Tệp hình ảnh sự cố lưu trữ cục bộ: <br>
                <code id="imgFallbackPath" style="color: #38bdf8; word-break: break-all;"></code>
            </div>
        </div>
    </div>
</div>

<script>
// Danh sách Kỹ thuật viên đã chuẩn hóa theo Khu
const allTechnicians = <?= json_encode($technicians, JSON_UNESCAPED_UNICODE) ?>;
const currentViewKhu = '<?= htmlspecialchars($currentKhu) ?>';

/**
 * Hàm nạp danh sách Kỹ thuật viên vào thẻ <select> chỉ lọc đúng theo Khu A hoặc Khu B
 */
function renderTechnicianOptions(selectElement, targetKhu, selectedId = null) {
    if (!selectElement) return;
    selectElement.innerHTML = '';

    const defaultOpt = document.createElement('option');
    defaultOpt.value = '';
    if (targetKhu && targetKhu !== 'all') {
        defaultOpt.textContent = `-- Chọn Kỹ thuật viên (Chỉ Khu ${targetKhu}) --`;
    } else {
        defaultOpt.textContent = '-- Chọn nhân viên kỹ thuật --';
    }
    selectElement.appendChild(defaultOpt);

    const filtered = allTechnicians.filter(t => {
        if (!targetKhu || targetKhu === 'all') return true;
        return t.MaKhu === targetKhu;
    });

    filtered.forEach(tech => {
        const opt = document.createElement('option');
        opt.value = tech.MaTaiKhoan;
        opt.setAttribute('data-khu', tech.MaKhu);
        opt.textContent = `${tech.HoVaTen} (Khu ${tech.MaKhu} - ${tech.TenDangNhap}${tech.SoDienThoai ? ' - 📞' + tech.SoDienThoai : ''})`;
        if (selectedId && parseInt(selectedId) === parseInt(tech.MaTaiKhoan)) {
            opt.selected = true;
        }
        selectElement.appendChild(opt);
    });

    if (filtered.length === 0) {
        const emptyOpt = document.createElement('option');
        emptyOpt.disabled = true;
        emptyOpt.textContent = `(Không tìm thấy nhân viên kỹ thuật thuộc Khu ${targetKhu})`;
        selectElement.appendChild(emptyOpt);
    }
}

/**
 * Xử lý khi người dùng chọn phiếu sự cố ở Form trên đầu trang
 */
function onTicketChange() {
    const selectPhieu = document.getElementById('selectMaPhieu');
    const selectNV = document.getElementById('selectMaNhanVien');
    const note = document.getElementById('techKhuNote');
    if (!selectPhieu || !selectNV) return;

    const selectedOption = selectPhieu.options[selectPhieu.selectedIndex];
    if (!selectedOption || !selectedOption.value) {
        const defaultKhu = (currentViewKhu !== 'all') ? currentViewKhu : null;
        renderTechnicianOptions(selectNV, defaultKhu);
        if (note) note.textContent = defaultKhu ? `(Lọc theo Khu ${defaultKhu})` : '';
        return;
    }

    const ticketKhu = selectedOption.getAttribute('data-khu') || 'A';
    const assignedNV = selectedOption.getAttribute('data-nv') || 0;

    renderTechnicianOptions(selectNV, ticketKhu, assignedNV);
    if (note) note.textContent = `(Tự động khóa chỉ hiển thị KTV Khu ${ticketKhu})`;
}

/**
 * Modal lập phiếu sự cố hạ tầng cán bộ
 */
function openStaffTicketModal() {
    document.getElementById('staffTicketModal').style.display = 'flex';
}
function closeStaffTicketModal() {
    document.getElementById('staffTicketModal').style.display = 'none';
}

/**
 * Modal Phân công nhanh theo từng dòng phiếu
 */
function openAssignModal(maPhieu, maPhong, tenTB, maKhu, currentNhanVienId) {
    document.getElementById('modalMaPhieu').value = maPhieu;
    document.getElementById('modalMaPhieuText').textContent = maPhieu;
    document.getElementById('modalMaPhongText').textContent = maPhong;
    document.getElementById('modalMaKhuBadge').textContent = 'Khu ' + maKhu;
    document.getElementById('modalTenTBText').textContent = tenTB;

    const modalSelectNV = document.getElementById('modalSelectMaNhanVien');
    const modalNote = document.getElementById('modalTechKhuNote');
    if (modalNote) modalNote.textContent = `(Chỉ hiển thị KTV phụ trách Khu ${maKhu})`;

    renderTechnicianOptions(modalSelectNV, maKhu, currentNhanVienId);
    document.getElementById('assignModal').style.display = 'flex';
}
function closeAssignModal() {
    document.getElementById('assignModal').style.display = 'none';
}

/**
 * Modal cập nhật tiến độ cho KTV
 */
function openProgressModal(maPhieu, ghiChu, trangThai) {
    document.getElementById('progressMaPhieu').value = maPhieu;
    document.getElementById('progressGhiChu').value = ghiChu || '';
    if (trangThai) {
        document.getElementById('progressTrangThai').value = trangThai;
    }
    document.getElementById('progressModal').style.display = 'flex';
}
function closeProgressModal() {
    document.getElementById('progressModal').style.display = 'none';
}

/**
 * Modal nghiệm thu hoàn thành
 */
function openCompleteModal(maPhieu) {
    document.getElementById('completeMaPhieu').value = maPhieu;
    document.getElementById('completeMaPhieuText').textContent = maPhieu;
    document.getElementById('completeModal').style.display = 'flex';
}
function closeCompleteModal() {
    document.getElementById('completeModal').style.display = 'none';
}

/**
 * Modal Hủy phiếu
 */
function openCancelModal(maPhieu) {
    document.getElementById('cancelMaPhieu').value = maPhieu;
    document.getElementById('cancelModal').style.display = 'flex';
}
function closeCancelModal() {
    document.getElementById('cancelModal').style.display = 'none';
}

/**
 * Xem ảnh hiện trường
 */
function viewImage(rawUrl) {
    const img = document.getElementById('imgViewer');
    const fallback = document.getElementById('imgFallback');
    const pathText = document.getElementById('imgFallbackPath');
    
    img.style.display = 'block';
    fallback.style.display = 'none';

    let finalUrl = rawUrl;
    if (rawUrl && !rawUrl.startsWith('http://') && !rawUrl.startsWith('https://')) {
        finalUrl = '<?= url() ?>/' + rawUrl.replace(/^\/+/, '');
    }
    
    img.src = finalUrl;
    pathText.textContent = rawUrl;
    document.getElementById('imageModal').style.display = 'flex';
}

// Khởi tạo ban đầu khi tải trang
document.addEventListener('DOMContentLoaded', function() {
    const selectNV = document.getElementById('selectMaNhanVien');
    const selectPhieu = document.getElementById('selectMaPhieu');

    if (selectPhieu && selectPhieu.value) {
        onTicketChange();
    } else if (selectNV) {
        const defaultKhu = (currentViewKhu !== 'all') ? currentViewKhu : null;
        renderTechnicianOptions(selectNV, defaultKhu);
        const note = document.getElementById('techKhuNote');
        if (note && defaultKhu) {
            note.textContent = `(Lọc theo Khu ${defaultKhu})`;
        }
    }
});
</script>

<?php
$content = ob_get_clean();
$title = "Điều phối Sửa chữa Thiết bị - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
