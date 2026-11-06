<?php
ob_start();

$isAllowed = $scope['isAllowed'] ?? false;
$activeTab = $activeTab ?? 'pending';
$kpiData = $kpis ?? ['pending' => 0, 'fixing' => 0, 'infra' => 0, 'completed' => 0, 'cancelled' => 0, 'total' => 0];

// Helper làm sạch tên tòa: tránh lặp "Tòa Tòa A01"
$formatTenToa = function (?string $rawName, ?string $code = ''): string {
    if (!$rawName) return $code ?: '---';
    $clean = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', trim($rawName));
    return 'Tòa ' . (!empty($clean) ? $clean : $rawName);
};

// Helper render lý do / ghi chú tiến độ
function renderMaintenanceNote(?string $note): void
{
    if (empty($note)) {
        echo "<span style='color: var(--text-muted); font-size: 0.8rem;'>Chưa có ghi chú tiến độ</span>";
        return;
    }
    if (str_contains($note, 'Lý do hủy:')) {
        $parts = explode('Lý do hủy:', $note);
        $reason = trim($parts[1] ?? $note);
        echo "<div style='font-size: 0.8rem; color: #991b1b; font-weight: 600; background: #fef2f2; padding: 3px 8px; border-radius: 4px; display: inline-block;'>🛑 Lý do hủy: " . htmlspecialchars($reason) . "</div>";
    } else {
        echo "<div style='font-size: 0.82rem; color: var(--text-secondary); line-height: 1.4;'>" . nl2br(htmlspecialchars($note)) . "</div>";
    }
}
?>

<div class="container">

    <!-- BREADCRUMB & HEADER (ĐỒNG BỘ CHUẨN ZONES, ROOMS, UTILITIES, REQUESTS) -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Điều phối Sửa chữa & Phiếu Báo Hỏng</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Điều phối Sửa chữa & Phiếu Báo Hỏng</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid <?= $scope['isTechnician'] ? 'fa-wrench' : 'fa-building-user' ?>" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ Quản lý') ?>
                    </span>
                    <?php if (!empty($scope['isTechnician'])): ?>
                        <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.75rem; padding: 3px 8px;">
                            <i class="fa-solid fa-screwdriver-wrench"></i> Chế độ Kỹ thuật viên (Thi công & Nghiệm thu)
                        </span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ ADMIN & ROOMS) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($scope['canCreateStaffTicket'])): ?>
                    <button type="button" class="btn btn-primary" onclick="openStaffTicketModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                        <i class="fa-solid fa-plus"></i> Lập phiếu sự cố hạ tầng
                    </button>
                <?php endif; ?>

                <?php if (!empty($isAllowed)): ?>
                    <button type="button" class="btn btn-outline" onclick="exportMaintenance()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất dữ liệu theo tab đang xem ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (<?= count($pendingTickets ?? []) ?>)</span>
                    </button>
                <?php endif; ?>

                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <b>Thành công:</b> <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <b>Cảnh báo:</b> <?= htmlspecialchars($flashWarning) ?>
        </div>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- TRƯỜNG HỢP 1: TỪ CHỐI TRUY CẬP (RED WARNING CARD) CHO NON-STAFF           -->
    <!-- ========================================================================= -->
    <?php if (!$isAllowed): ?>
        <div class="card" style="border: 2px solid #ef4444; background: #fef2f2; border-radius: 12px; padding: 40px 30px; text-align: center; margin-top: 24px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.12);">
            <div style="width: 76px; height: 76px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #dc2626; font-size: 2.2rem; border: 2px solid #fca5a5;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px;">
                Từ chối quyền truy cập
            </h2>
            <div style="max-width: 840px; margin: 0 auto 24px auto; font-size: 1.05rem; color: #b91c1c; line-height: 1.6; font-weight: 600;">
                <?= htmlspecialchars($scope['message'] ?? 'Từ chối quyền truy cập: Bạn không có quyền thao tác trên phân hệ này!') ?>
            </div>
            <div style="max-width: 600px; margin: 0 auto 24px auto; background: #ffffff; border: 1px solid #fecaca; border-radius: 8px; padding: 14px 20px; text-align: left; box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">Tài khoản hiện tại:</span>
                    <strong style="color: #0f172a;"><?= htmlspecialchars($user['HoVaTen'] ?? $user['TenDangNhap'] ?? 'Cán bộ') ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #64748b;">Vai trò / Chức danh:</span>
                    <span class="badge badge-danger" style="font-weight: 600;"><?= htmlspecialchars($scope['userRoleName'] ?? 'Nhân viên') ?></span>
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

        <!-- ========================================================================= -->
        <!-- TRƯỜNG HỢP 2: CÁN BỘ & KỸ THUẬT VIÊN ĐƯỢC PHÉP TRUY CẬP                   -->
        <!-- ========================================================================= -->

        <!-- 4 THẺ KPI THỐNG KÊ NHANH (QUICK STATS CARDS CHUẨN TỶ LỆ ROOMS & UTILITIES) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
            <!-- Thẻ 1: Chờ xử lý & Điều phối -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Chờ xử lý & Điều phối</span>
                    <span style="background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Hạn 24h</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #dc2626; margin: 4px 0;">
                    <?= number_format($kpiData['pending'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">phiếu</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Cần cán bộ điều phối phân công thợ</div>
            </div>

            <!-- Thẻ 2: Đang sửa chữa -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Đang sửa chữa</span>
                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Đang thi công</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e40af; margin: 4px 0;">
                    <?= number_format($kpiData['fixing'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">phiếu</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Kỹ thuật viên đang khắc phục tại chỗ</div>
            </div>

            <!-- Thẻ 3: Sự cố Hạ tầng chung -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Sự cố Hạ tầng chung</span>
                    <span style="background: #f3e8ff; color: #7e22ce; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Công cộng</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #7e22ce; margin: 4px 0;">
                    <?= number_format($kpiData['infra'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">phiếu</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Hành lang, thang máy & khuôn viên</div>
            </div>

            <!-- Thẻ 4: Đã hoàn tất & Nghiệm thu -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Đã hoàn tất</span>
                    <span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Nghiệm thu</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #166534; margin: 4px 0;">
                    <?= number_format($kpiData['completed'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">/ <?= (int)($kpiData['total'] ?? 0) ?></span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Thiết bị đã hoàn nguyên về trạng thái tốt</div>
            </div>
        </div>

        <!-- KHỐI BỘ LỌC ĐA TẦNG CASCADING (ĐỒNG BỘ BACKGROUND #f8fafc & PADDING CHUẨN ROOMS/ADMIN) -->
        <div class="card" style="margin-bottom: 24px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #f8fafc;">
            <form method="GET" action="<?= url('staff/maintenance') ?>" id="filterFormMaintenance" style="margin: 0;">
                <input type="hidden" name="tab" id="currentActiveTab" value="<?= htmlspecialchars($activeTab ?? 'pending') ?>">

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- 1. Ô tìm kiếm tức thời -->
                    <div style="position: relative; flex: 1; min-width: 240px; max-width: 320px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                        <input type="text" name="search" id="inputSearch" class="btn-outline"
                            placeholder="Tìm mã phiếu, phòng, thiết bị, tên SV, thợ..."
                            value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                            style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;">
                    </div>

                    <!-- 2. Dropdown Loại sự cố -->
                    <select name="type" class="btn-outline" style="min-width: 165px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormMaintenance').submit()">
                        <option value="all" <?= ($selectedType === 'all') ? 'selected' : '' ?>>-- Tất cả loại sự cố --</option>
                        <option value="PHONG" <?= ($selectedType === 'PHONG') ? 'selected' : '' ?>>Phòng sinh viên</option>
                        <option value="HA_TANG" <?= ($selectedType === 'HA_TANG') ? 'selected' : '' ?>>Hạ tầng công cộng</option>
                    </select>

                    <!-- 3. Dropdown Khu (cho Admin) -->
                    <?php if ($scope['level'] === 'ALL'): ?>
                        <select name="khu" id="filterKhuSelect" class="btn-outline" style="min-width: 130px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onKhuChange(this.value)">
                            <option value="">-- Tất cả Khu --</option>
                            <?php foreach ($scope['availableKhus'] ?? [] as $k): ?>
                                <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= ($selectedKhu === $k['MaKhu']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['TenKhu'] ?? ('Khu ' . $k['MaKhu'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 4. Dropdown Cụm (cho Admin / Khu) -->
                    <?php if ($scope['level'] === 'ALL' || $scope['level'] === 'KHU'): ?>
                        <select name="cum" id="filterCumSelect" class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onCumChange(this.value)">
                            <option value="">-- Tất cả Cụm --</option>
                            <?php foreach ($scope['availableCums'] ?? [] as $c): ?>
                                <?php if (empty($selectedKhu) || ($c['MaKhu'] ?? '') === $selectedKhu): ?>
                                    <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($selectedCum === $c['MaCum']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['TenCum'] ?? ('Cụm ' . $c['MaCum'])) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 5. Dropdown Tòa (Khử lặp nhãn "Tòa Tòa") -->
                    <?php if ($scope['level'] !== 'TOA'): ?>
                        <select name="toa" id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormMaintenance').submit()">
                            <option value="">-- Tất cả Tòa nhà --</option>
                            <?php foreach ($scope['availableToas'] ?? [] as $t): ?>
                                <?php
                                $matchCum = empty($selectedCum) || ($t['MaCum'] ?? '') === $selectedCum;
                                $matchKhu = empty($selectedKhu) || ($t['MaKhu'] ?? '') === $selectedKhu;
                                ?>
                                <?php if ($matchCum && $matchKhu): ?>
                                    <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" <?= ($selectedToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 6. Dropdown Tầng -->
                    <select name="tang" class="btn-outline" style="min-width: 125px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormMaintenance').submit()">
                        <option value="">-- Tất cả tầng --</option>
                        <?php for ($t = 1; $t <= 12; $t++): ?>
                            <option value="<?= $t ?>" <?= ((string)$selectedTang === (string)$t) ? 'selected' : '' ?>>Tầng <?= $t ?></option>
                        <?php endfor; ?>
                    </select>

                    <!-- 7. Dropdown Kỹ thuật viên phụ trách -->
                    <select name="tech" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormMaintenance').submit()">
                        <option value="">-- Kỹ thuật viên --</option>
                        <option value="none" <?= ($selectedTech === 'none') ? 'selected' : '' ?>>⚠️ Chưa phân công</option>
                        <?php foreach ($technicians ?? [] as $tc): ?>
                            <option value="<?= htmlspecialchars($tc['MaTaiKhoan']) ?>" <?= ((string)$selectedTech === (string)$tc['MaTaiKhoan']) ? 'selected' : '' ?>>
                                👨‍🔧 <?= htmlspecialchars($tc['HoVaTen']) ?> (Khu <?= htmlspecialchars($tc['MaCumQuanLy'] ?? 'A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- 8. Dropdown Trạng thái -->
                    <select name="status" class="btn-outline" style="min-width: 145px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormMaintenance').submit()">
                        <option value="all" <?= ($selectedStatus === 'all') ? 'selected' : '' ?>>-- Tất cả trạng thái --</option>
                        <option value="Chờ xử lý" <?= ($selectedStatus === 'Chờ xử lý') ? 'selected' : '' ?>>⏳ Chờ xử lý</option>
                        <option value="Đang sửa chữa" <?= ($selectedStatus === 'Đang sửa chữa') ? 'selected' : '' ?>>🛠️ Đang sửa chữa</option>
                        <option value="Đã hoàn thành" <?= ($selectedStatus === 'Đã hoàn thành') ? 'selected' : '' ?>>✓ Đã hoàn thành</option>
                        <option value="Đã hủy" <?= ($selectedStatus === 'Đã hủy') ? 'selected' : '' ?>>✕ Đã hủy</option>
                    </select>

                    <!-- Nút Lọc & Nút Xóa bộ lọc -->
                    <?php if (!empty($searchTerm) || $selectedType !== 'all' || $selectedStatus !== 'all' || !empty($selectedKhu) || !empty($selectedCum) || !empty($selectedToa) || !empty($selectedTang) || !empty($selectedTech)): ?>
                        <a href="<?= url('staff/maintenance?tab=' . urlencode($activeTab)) ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; height: 38px; padding: 0 14px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; color: #dc2626; border-color: #fca5a5; background: #fff5f5; font-weight: 600;" title="Xóa toàn bộ bộ lọc và trở về mặc định">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 5 SUB-TABS ĐIỀU HƯỚNG (ĐỒNG BỘ CHUẨN APPLICATIONS VỚI PILL BADGES) -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
            <button type="button" class="tab-btn <?= ($activeTab === 'pending') ? 'active' : '' ?>" id="tabBtnPending" onclick="switchMaintenanceTab('pending')"
                style="background: <?= ($activeTab === 'pending') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'pending') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'pending') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Chờ xử lý & Điều phối
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #fee2e2; color: #991b1b; border: none; font-weight: 700;"><?= count($pendingTickets ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'fixing') ? 'active' : '' ?>" id="tabBtnFixing" onclick="switchMaintenanceTab('fixing')"
                style="background: <?= ($activeTab === 'fixing') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'fixing') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'fixing') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Đang sửa chữa
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #dbeafe; color: #1e40af; border: none; font-weight: 700;"><?= count($fixingTickets ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'infra') ? 'active' : '' ?>" id="tabBtnInfra" onclick="switchMaintenanceTab('infra')"
                style="background: <?= ($activeTab === 'infra') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'infra') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'infra') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Sự cố Hạ tầng chung
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #f3e8ff; color: #7e22ce; border: none; font-weight: 700;"><?= count($infraTickets ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'completed') ? 'active' : '' ?>" id="tabBtnCompleted" onclick="switchMaintenanceTab('completed')"
                style="background: <?= ($activeTab === 'completed') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'completed') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'completed') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Đã hoàn tất & Nghiệm thu
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #dcfce7; color: #166534; border: none; font-weight: 700;"><?= count($completedTickets ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'cancelled') ? 'active' : '' ?>" id="tabBtnCancelled" onclick="switchMaintenanceTab('cancelled')"
                style="background: <?= ($activeTab === 'cancelled') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'cancelled') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'cancelled') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Đã hủy / Đóng
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #f1f5f9; color: #475569; border: none; font-weight: 700;"><?= count($cancelledTickets ?? []) ?></span>
            </button>
        </div>

        <?php
        // Danh sách cấu hình hiển thị 5 Tabs
        $tabConfigs = [
            'pending' => ['title' => 'Danh sách Sự cố Chờ xử lý & Điều phối phân công', 'list' => $pendingTickets ?? []],
            'fixing' => ['title' => 'Danh sách Sự cố Đang sửa chữa (Đang thi công khắc phục)', 'list' => $fixingTickets ?? []],
            'infra' => ['title' => 'Danh sách Sự cố Hạ tầng công cộng & Khuôn viên KTX', 'list' => $infraTickets ?? []],
            'completed' => ['title' => 'Lịch sử Sự cố Đã hoàn tất & Nghiệm thu thành công', 'list' => $completedTickets ?? []],
            'cancelled' => ['title' => 'Lịch sử Sự cố Đã hủy / Đóng phiếu', 'list' => $cancelledTickets ?? []]
        ];
        ?>

        <?php foreach ($tabConfigs as $tabKey => $cfg): ?>
            <div id="tabContent_<?= $tabKey ?>" style="display: <?= ($activeTab === $tabKey) ? 'block' : 'none' ?>;">
                <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span><?= htmlspecialchars($cfg['title']) ?></span>
                            <span class="badge badge-info"><?= count($cfg['list']) ?> phiếu</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="table_<?= $tabKey ?>">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Mã Phiếu</th>
                                    <th>Vị Trí</th>
                                    <th>Hạng Mục</th>
                                    <th>Nội Dung Sự Cố</th>
                                    <th>Kỹ Thuật Phụ Trách</th>
                                    <th>Trạng Thái</th>
                                    <th style="text-align: right; width: 195px; min-width: 195px; white-space: nowrap;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_<?= $tabKey ?>">
                                <?php if (empty($cfg['list'])): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                            <div style="font-weight: 400; color: var(--text-secondary);">Không có phiếu sự cố bảo trì nào trong mục này.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cfg['list'] as $t): ?>
                                        <?php
                                        $status = $t['TrangThaiSua'] ?? '';
                                        $isPending = ($status === 'Chờ xử lý');
                                        $isFixing = ($status === 'Đang sửa chữa');
                                        $isCompleted = ($status === 'Đã hoàn thành');
                                        $isCancelled = ($status === 'Đã hủy');
                                        $isInfra = ($t['LoaiSuCo'] === 'HA_TANG');

                                        // Kiểm tra thẩm quyền thao tác trên phiếu cụ thể
                                        $canManagerAct = !empty($scope['canAssign']) && (!$isInfra || !empty($scope['canApproveStaffTicket']) || $scope['level'] !== 'TOA');
                                        $canTechAct = !empty($scope['isTechnician']);
                                        ?>
                                        <tr class="<?= $tabKey ?>-row">
                                            <!-- 1. Mã Phiếu & Loại -->
                                            <td>
                                                <b style="font-family: monospace; color: var(--primary); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($t['MaPhieu']) ?>
                                                </b>
                                                <div style="margin-top: 4px;">
                                                    <?php if ($isInfra): ?>
                                                        <span class="badge" style="background: #f3e8ff; color: #7e22ce; border: none; font-size: 0.72rem; font-weight: 700; padding: 2px 6px;">
                                                            Hạ tầng chung
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge" style="background: #e0f2fe; color: #0369a1; border: none; font-size: 0.72rem; font-weight: 700; padding: 2px 6px;">
                                                            Phòng SV
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                                                    <?= !empty($t['NgayBao']) ? date('d/m/Y H:i', strtotime($t['NgayBao'])) : '' ?>
                                                </div>
                                            </td>

                                            <!-- 2. Vị Trí & Khu Vực -->
                                            <td>
                                                <?php if (!empty($t['SoPhong'])): ?>
                                                    <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                                        P.<?= htmlspecialchars($t['SoPhong']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div style="font-size: 0.825rem; font-weight: 600; color: #1e40af; margin-top: 2px;">
                                                    <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['FinalToaNha'], $t['FinalToaNha'])) ?>
                                                </div>
                                                <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                                    Cụm <?= htmlspecialchars($t['MaCum'] ?? '---') ?> &bull; Khu <?= htmlspecialchars($t['MaKhu'] ?? '---') ?>
                                                </div>
                                            </td>

                                            <!-- 3. Hạng Mục / Thiết Bị -->
                                            <td>
                                                <div style="font-weight: 600; color: var(--text-primary); font-size: 0.9rem;">
                                                    <?= htmlspecialchars($t['TenThietBi'] ?? 'Cơ sở vật chất') ?>
                                                </div>
                                                <?php if (!empty($t['MaSoThietBi'])): ?>
                                                    <div style="font-size: 0.78rem; color: var(--text-muted); font-family: monospace;">
                                                        Mã TB: <?= htmlspecialchars($t['MaSoThietBi']) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if (!empty($t['TinhTrangThietBi'])): ?>
                                                    <span class="badge" style="background: <?= ($t['TinhTrangThietBi'] === 'Tốt') ? '#dcfce7' : '#fee2e2' ?>; color: <?= ($t['TinhTrangThietBi'] === 'Tốt') ? '#166534' : '#991b1b' ?>; border: none; font-size: 0.72rem; padding: 2px 6px; margin-top: 4px; display: inline-block;">
                                                        <?= htmlspecialchars($t['TinhTrangThietBi']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- 4. Nội Dung Sự Cố & Người Báo -->
                                            <td style="max-width: 280px;">
                                                <div style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.4; margin-bottom: 4px;">
                                                    <?= htmlspecialchars($t['MoTaHuHong']) ?>
                                                </div>
                                                <?php if (!empty($t['HinhAnh'])): ?>
                                                    <div style="margin-bottom: 4px;">
                                                        <a href="<?= url('student/requests/attachment') ?>?kind=repair&amp;id=<?= rawurlencode($t['MaPhieu']) ?>" target="_blank" rel="noopener" style="font-size: 0.75rem; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                            <i class="fa-solid fa-image"></i> Xem ảnh hiện trường
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                    Người báo: <b><?= htmlspecialchars($t['TenSinhVien'] ?? $t['TenNhanVienBao'] ?? 'Cán bộ KTX') ?></b>
                                                    <?php if (!empty($t['SdtSinhVien'])): ?>
                                                        (<?= htmlspecialchars($t['SdtSinhVien']) ?>)
                                                    <?php endif; ?>
                                                </div>
                                            </td>

                                            <!-- 5. Kỹ Thuật Phụ Trách & Tiến Độ -->
                                            <td style="max-width: 220px;">
                                                <?php if (!empty($t['TenTho'])): ?>
                                                    <div style="font-weight: 700; color: #0284c7; font-size: 0.88rem; display: flex; align-items: center; gap: 5px;">
                                                        <i class="fa-solid fa-user-gear"></i> <?= htmlspecialchars($t['TenTho']) ?>
                                                    </div>
                                                    <?php if (!empty($t['SdtTho'])): ?>
                                                        <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                            SĐT: <?= htmlspecialchars($t['SdtTho']) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge" style="background: #fef3c7; color: #b45309; border: none; font-size: 0.75rem; padding: 2px 6px;">
                                                        ⚠️ Chưa chỉ định thợ
                                                    </span>
                                                <?php endif; ?>

                                                <div style="margin-top: 6px;">
                                                    <?php renderMaintenanceNote($t['GhiChuTienDo'] ?? ''); ?>
                                                </div>
                                            </td>

                                            <!-- 6. Trạng Thái -->
                                            <td>
                                                <?php if ($isCompleted): ?>
                                                    <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã hoàn thành
                                                    </span>
                                                <?php elseif ($isFixing): ?>
                                                    <span class="badge" style="background: #dbeafe; color: #1e40af; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-wrench" style="margin-right: 4px;"></i> Đang sửa chữa
                                                    </span>
                                                <?php elseif ($isCancelled): ?>
                                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Đã hủy
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Chờ xử lý
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- 7. Thao Tác (CÂN ĐỐI 100% INLINE-FLEX KHÔNG RỚT DÒNG) -->
                                            <td style="text-align: right; white-space: nowrap; width: 195px; min-width: 195px;">
                                                <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                                    <!-- A. DÀNH CHO CÁN BỘ QUẢN LÝ (PHÂN CÔNG & HỦY) -->
                                                    <?php if ($canManagerAct && ($isPending || $isFixing)): ?>
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="openAssignModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars(addslashes($t['TenThietBi'] ?? 'Cơ sở vật chất')) ?>', '<?= htmlspecialchars($t['NhanVienKyThuat'] ?? '') ?>', '<?= htmlspecialchars($t['MaKhu'] ?? 'A') ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Phân công hoặc đổi kỹ thuật viên phụ trách">
                                                            <i class="fa-solid fa-user-gear"></i> Phân công
                                                        </button>

                                                        <button type="button" class="btn btn-outline btn-sm" onclick="openCancelModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars(addslashes($t['TenThietBi'] ?? 'Cơ sở vật chất')) ?>')" style="border-radius: 6px; color: #dc2626; border-color: #fca5a5; background: #fff5f5; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Hủy phiếu báo hỏng này">
                                                            <i class="fa-solid fa-xmark"></i> Hủy
                                                        </button>

                                                        <!-- B. DÀNH CHO KỸ THUẬT VIÊN (TIẾP NHẬN, CẬP NHẬT & NGHIỆM THU) -->
                                                    <?php elseif ($canTechAct && ($isPending || $isFixing)): ?>
                                                        <?php if ($isPending): ?>
                                                            <form action="<?= url('staff/maintenance/update-progress') ?>" method="POST" style="margin: 0; display: inline-flex;">
                                                                <input type="hidden" name="maPhieu" value="<?= htmlspecialchars($t['MaPhieu']) ?>">
                                                                <input type="hidden" name="trangThai" value="Đang sửa chữa">
                                                                <input type="hidden" name="ghiChu" value="Kỹ thuật viên đã tiếp nhận phiếu và đang chuẩn bị trang thiết bị vật tư xử lý.">
                                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 600; padding: 5px 12px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 5px; height: 32px; white-space: nowrap; flex-shrink: 0;">
                                                                    <i class="fa-solid fa-play"></i> Tiếp nhận
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-outline btn-sm" onclick="openProgressModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars(addslashes($t['MoTaHuHong'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($t['GhiChuTienDo'] ?? '')) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; color: #0284c7; border-color: #7dd3fc; background: #f0f9ff; flex-shrink: 0;" title="Cập nhật tiến độ hoặc báo cáo hoàn thành">
                                                                <i class="fa-solid fa-pen-to-square"></i> Cập nhật
                                                            </button>

                                                            <button type="button" class="btn btn-primary btn-sm" onclick="openCompleteModal('<?= htmlspecialchars($t['MaPhieu']) ?>', '<?= htmlspecialchars(addslashes($t['TenThietBi'] ?? 'Cơ sở vật chất')) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; background: #16a34a; border-color: #16a34a; flex-shrink: 0;" title="Nghiệm thu hoàn tất sửa chữa">
                                                                <i class="fa-solid fa-check"></i> Xong
                                                            </button>
                                                        <?php endif; ?>

                                                        <!-- C. PHIẾU ĐÃ HOÀN TẤT HOẶC ĐÃ HỦY -->
                                                    <?php else: ?>
                                                        <?php if ($isCompleted): ?>
                                                            <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                                <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã nghiệm thu
                                                            </span>
                                                        <?php elseif ($isCancelled): ?>
                                                            <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                                <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Đã đóng phiếu
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Đã giải quyết</span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- THANH PHÂN TRANG -->
                    <?php if (!empty($cfg['list'])): ?>
                        <div id="<?= $tabKey ?>PaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                                <span id="<?= $tabKey ?>PaginationInfo" style="font-size: 0.85rem; color: var(--text-muted);">
                                    Hiển thị 1 - <?= min(10, count($cfg['list'])) ?> trên tổng số <?= count($cfg['list']) ?> phiếu
                                </span>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted);">
                                    <span>Hiển thị:</span>
                                    <select id="<?= $tabKey ?>PageSizeSelect" onchange="changeMaintenancePageSize('<?= $tabKey ?>', this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                                        <option value="10" selected>10 / trang</option>
                                        <option value="25">25 / trang</option>
                                        <option value="50">50 / trang</option>
                                    </select>
                                </div>
                            </div>
                            <div id="<?= $tabKey ?>PaginationNav" style="display: flex; gap: 4px; align-items: center;"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: DUYỆT & PHÂN CÔNG KỸ THUẬT VIÊN (CHO CÁN BỘ QUẢN LÝ)             -->
<!-- ========================================================================= -->
<div id="modalAssignTech" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e40af; margin: 0;">
                Phân công Kỹ thuật viên sửa chữa
            </h3>
            <button type="button" onclick="closeAssignModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/maintenance/assign') ?>" method="POST" id="formModalAssign">
            <input type="hidden" name="maPhieu" id="assignModalMaPhieu" value="">

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Mã phiếu:</span>
                    <strong style="color: var(--primary); font-family: monospace;" id="assignModalMaPhieuText">---</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Hạng mục thiết bị:</span>
                    <strong style="color: var(--text-primary);" id="assignModalTenThietBi">---</strong>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Chọn Kỹ thuật viên phụ trách: <span style="color: #dc2626;">*</span>
                </label>
                <select name="maNhanVien" id="selectAssignTho" required class="btn-outline" style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
                    <option value="">-- Chọn kỹ thuật viên --</option>
                    <?php foreach ($technicians ?? [] as $tc): ?>
                        <option value="<?= htmlspecialchars($tc['MaTaiKhoan']) ?>">
                            <?= htmlspecialchars($tc['HoVaTen']) ?> - SĐT: <?= htmlspecialchars($tc['SoDienThoai'] ?? '---') ?> (Khu <?= htmlspecialchars($tc['MaCumQuanLy'] ?? 'A') ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--text-muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                    * Lưu ý: Hệ thống bắt buộc phân công Kỹ thuật viên cùng Khu với phiếu báo hỏng.
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Chuyển trạng thái phiếu:
                </label>
                <select name="trangThai" class="btn-outline" style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
                    <option value="Đang sửa chữa" selected>Đang sửa chữa (Đang thi công)</option>
                    <option value="Chờ xử lý">Chờ xử lý (Chờ thợ sắp xếp lịch)</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeAssignModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Đóng
                </button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Xác nhận phân công
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: CẬP NHẬT TIẾN ĐỘ SỬA CHỮA (CHO KỸ THUẬT VIÊN)                    -->
<!-- ========================================================================= -->
<div id="modalProgress" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #0284c7; margin: 0;">
                Cập nhật tiến độ & Vật tư sửa chữa
            </h3>
            <button type="button" onclick="closeProgressModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/maintenance/update-progress') ?>" method="POST" id="formModalProgress">
            <input type="hidden" name="maPhieu" id="progressModalMaPhieu" value="">

            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #0369a1;">Mã phiếu:</span>
                    <strong style="color: #0284c7; font-family: monospace;" id="progressModalMaPhieuText">---</strong>
                </div>
                <div style="color: #334155;">
                    <span style="color: #0369a1;">Mô tả ban đầu:</span>
                    <em id="progressModalMoTa">---</em>
                </div>
            </div>

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Trạng thái công việc:
                </label>
                <select name="trangThai" id="selectProgressTrangThai" class="btn-outline" style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
                    <option value="Đang sửa chữa" selected>Đang sửa chữa (Đang thi công khắc phục)</option>
                    <option value="Đã hoàn thành">Đã hoàn thành (Nghiệm thu sửa xong)</option>
                    <option value="Chờ xử lý">Chờ xử lý (Chờ vật tư phụ tùng)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Nhật ký tiến độ & Linh kiện thay thế: <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="ghiChu" id="textareaProgressGhiChu" rows="4" required class="btn-outline" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff; resize: vertical;" placeholder="Ví dụ: Đã thay tụ điện quạt trần, kiểm tra đường dây nguồn hoạt động tốt..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeProgressModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Đóng
                </button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu cập nhật
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: BÁO CÁO NGHIỆM THU HOÀN THÀNH (QUICK COMPLETE)                   -->
<!-- ========================================================================= -->
<div id="modalComplete" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #15803d; margin: 0;">
                Xác nhận hoàn thành sửa chữa
            </h3>
            <button type="button" onclick="closeCompleteModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/maintenance/complete') ?>" method="POST" id="formModalComplete">
            <input type="hidden" name="maPhieu" id="completeModalMaPhieu" value="">

            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
                Bạn có chắc chắn muốn xác nhận hoàn thành công tác sửa chữa cho phiếu <strong style="color: var(--primary);" id="completeModalMaPhieuText">---</strong>?
                Hệ thống sẽ tự động hoàn nguyên tình trạng thiết bị về <b>Tốt</b>.
            </p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Ghi chú nghiệm thu bàn giao:
                </label>
                <textarea name="ghiChu" rows="3" class="btn-outline" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff; resize: vertical;" placeholder="Đã kiểm tra vận hành thử nghiệm, sinh viên xác nhận hài lòng..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCompleteModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px; background: #16a34a; border-color: #16a34a; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Xác nhận hoàn thành
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: LẬP PHIẾU SỰ CỐ HẠ TẦNG CHUNG (DÀNH CHO CÁN BỘ)                  -->
<!-- ========================================================================= -->
<div id="modalStaffTicket" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 560px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e40af; margin: 0;">
                Lập phiếu sự cố hạ tầng công cộng
            </h3>
            <button type="button" onclick="closeStaffTicketModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/maintenance/create-staff-ticket') ?>" method="POST" enctype="multipart/form-data" id="formModalStaffTicket">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Tòa nhà xảy ra sự cố: <span style="color: #dc2626;">*</span>
                </label>
                <select name="maToaNha" id="staffTicketToaSelect" required class="btn-outline" style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
                    <option value="">-- Chọn tòa nhà --</option>
                    <?php foreach ($scope['availableToas'] ?? [] as $t): ?>
                        <option value="<?= htmlspecialchars($t['MaToaNha']) ?>">
                            <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Vị trí cụ thể (Hành lang, Sảnh, Thang máy, Sân thể thao...):
                </label>
                <input type="text" name="maPhong" class="btn-outline" placeholder="Ví dụ: Hành lang tầng 3, Thang máy số 2, Nhà xe..." style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Mô tả chi tiết hiện tượng hư hỏng: <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="moTa" rows="3" required class="btn-outline" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff; resize: vertical;" placeholder="Mô tả mức độ nghiêm trọng, nguy cơ mất an toàn..."></textarea>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Đính kèm hình ảnh hiện trường (Tối đa 5MB, JPG/PNG):
                </label>
                <input type="file" name="hinhAnhFile" accept="image/*" class="btn-outline" style="width: 100%; padding: 6px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeStaffTicketModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Đóng
                </button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-paper-plane"></i> Gửi phiếu sự cố
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: HỦY PHIẾU BÁO HỎNG (KÈM LÝ DO)                                    -->
<!-- ========================================================================= -->
<div id="modalCancelTicket" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #991b1b; margin: 0;">
                Hủy phiếu sự cố bảo trì
            </h3>
            <button type="button" onclick="closeCancelModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/maintenance/cancel') ?>" method="POST" id="formModalCancel">
            <input type="hidden" name="maPhieu" id="cancelModalMaPhieu" value="">

            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: #991b1b;">Mã phiếu:</span>
                    <strong style="color: #b91c1c; font-family: monospace;" id="cancelModalMaPhieuText">---</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: #991b1b;">Thiết bị:</span>
                    <strong style="color: #7f1d1d;" id="cancelModalTenThietBi">---</strong>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Chọn nhanh lý do hủy:
                </label>
                <select id="selectQuickCancelReason" class="btn-outline" style="width: 100%; height: 38px; padding: 0 12px; border-radius: 6px; font-size: 0.85rem; background: #fff;" onchange="onQuickCancelChange(this.value)">
                    <option value="">-- Chọn lý do mẫu --</option>
                    <option value="Báo trùng lặp sự cố đã có phiếu xử lý trước đó">Báo trùng lặp sự cố đã có phiếu xử lý trước đó</option>
                    <option value="Sinh viên báo nhầm, thiết bị vẫn hoạt động bình thường sau khi kiểm tra">Sinh viên báo nhầm, thiết bị vẫn hoạt động bình thường</option>
                    <option value="Hư hỏng do sinh viên cố tình làm gãy/vỡ, chuyển xử lý bồi thường tài sản">Hư hỏng do cố ý làm vỡ, chuyển xử lý bồi thường</option>
                    <option value="Khu vực đang chuẩn bị cải tạo tổng thể toàn diện">Khu vực đang chuẩn bị cải tạo tổng thể toàn diện</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Lý do hủy chi tiết: <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="lyDo" id="inputCancelReason" rows="3" required class="btn-outline" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff; resize: vertical;" placeholder="Nhập lý do hủy bỏ phiếu sự cố này..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeCancelModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Đóng
                </button>
                <button type="submit" class="btn btn-danger" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px; color: #fff; background: #dc2626; border-color: #dc2626; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Xác nhận hủy phiếu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: PHÂN TRANG, CHUYỂN TAB, MODALS & XUẤT CSV                      -->
<!-- ========================================================================= -->
<script>
    // 1. Phân trang Client-side cho cả 5 tabs
    const maintenancePaginationState = {
        pending: {
            page: 1,
            pageSize: 10
        },
        fixing: {
            page: 1,
            pageSize: 10
        },
        infra: {
            page: 1,
            pageSize: 10
        },
        completed: {
            page: 1,
            pageSize: 10
        },
        cancelled: {
            page: 1,
            pageSize: 10
        }
    };

    function renderMaintenancePagination(tabKey) {
        const tbody = document.getElementById(`tbody_${tabKey}`);
        if (!tbody) return;
        const rows = Array.from(tbody.querySelectorAll(`.${tabKey}-row`));
        const total = rows.length;

        if (total === 0) return;

        const state = maintenancePaginationState[tabKey] || {
            page: 1,
            pageSize: 10
        };
        const pageSize = state.pageSize;
        const totalPages = Math.ceil(total / pageSize) || 1;
        if (state.page > totalPages) state.page = totalPages;
        const curPage = state.page;

        const startIdx = (curPage - 1) * pageSize;
        const endIdx = startIdx + pageSize;

        rows.forEach((row, i) => {
            row.style.display = (i >= startIdx && i < endIdx) ? '' : 'none';
        });

        const info = document.getElementById(`${tabKey}PaginationInfo`);
        if (info) {
            const toShow = Math.min(endIdx, total);
            info.innerText = `Hiển thị ${startIdx + 1} - ${toShow} trên tổng số ${total} phiếu`;
        }

        const nav = document.getElementById(`${tabKey}PaginationNav`);
        if (nav) {
            let html = '';
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToMaintenancePage('${tabKey}', ${curPage - 1})" ${curPage <= 1 ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}><i class="fa-solid fa-chevron-left"></i></button>`;

            let startP = Math.max(1, curPage - 2);
            let endP = Math.min(totalPages, curPage + 2);

            if (startP > 1) {
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToMaintenancePage('${tabKey}', 1)">1</button>`;
                if (startP > 2) html += `<span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>`;
            }

            for (let p = startP; p <= endP; p++) {
                const isActive = (p === curPage);
                html += `<button type="button" class="btn btn-sm ${isActive ? 'btn-primary' : 'btn-outline'}" onclick="goToMaintenancePage('${tabKey}', ${p})" style="min-width: 32px; ${isActive ? 'font-weight: 700;' : ''}">${p}</button>`;
            }

            if (endP < totalPages) {
                if (endP < totalPages - 1) html += `<span style="padding: 0 4px; color: var(--text-muted);">&hellip;</span>`;
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToMaintenancePage('${tabKey}', ${totalPages})">${totalPages}</button>`;
            }

            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToMaintenancePage('${tabKey}', ${curPage + 1})" ${curPage >= totalPages ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}><i class="fa-solid fa-chevron-right"></i></button>`;
            nav.innerHTML = html;
        }
    }

    function goToMaintenancePage(tabKey, p) {
        if (p < 1) return;
        maintenancePaginationState[tabKey].page = p;
        renderMaintenancePagination(tabKey);
    }

    function changeMaintenancePageSize(tabKey, newSize) {
        maintenancePaginationState[tabKey].pageSize = parseInt(newSize, 10);
        maintenancePaginationState[tabKey].page = 1;
        renderMaintenancePagination(tabKey);
    }

    // 2. Chuyển Tab Client-side mượt mà
    function updateExportButtonCount(tabKey) {
        const tbody = document.getElementById(`tbody_${tabKey}`);
        const rows = tbody ? tbody.querySelectorAll(`.${tabKey}-row`).length : 0;
        const btnText = document.getElementById('btnExportText');
        if (btnText) {
            btnText.innerText = `Xuất Excel (${rows})`;
        }
    }

    function switchMaintenanceTab(tabKey) {
        const input = document.getElementById('currentActiveTab');
        if (input) input.value = tabKey;

        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', url.toString());

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.color = 'var(--text-secondary)';
            b.style.background = 'transparent';
            b.style.fontWeight = '600';
            b.classList.remove('active');
        });

        ['pending', 'fixing', 'infra', 'completed', 'cancelled'].forEach(k => {
            const el = document.getElementById(`tabContent_${k}`);
            if (el) el.style.display = 'none';
        });

        const activeBtn = document.getElementById(`tabBtn${tabKey.charAt(0).toUpperCase() + tabKey.slice(1)}`);
        if (activeBtn) {
            activeBtn.style.color = 'var(--primary)';
            activeBtn.style.background = '#eff6ff';
            activeBtn.style.fontWeight = '700';
            activeBtn.classList.add('active');
        }

        const activeEl = document.getElementById(`tabContent_${tabKey}`);
        if (activeEl) activeEl.style.display = 'block';

        updateExportButtonCount(tabKey);
    }

    // 3. Dropdown liên hoàn (Cascading Dropdowns)
    function onKhuChange(khuVal) {
        const cumSelect = document.getElementById('filterCumSelect');
        const toaSelect = document.getElementById('filterToaSelect');
        if (cumSelect) cumSelect.value = '';
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormMaintenance').submit();
    }

    function onCumChange(cumVal) {
        const toaSelect = document.getElementById('filterToaSelect');
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormMaintenance').submit();
    }

    // 4. Xuất Excel / CSV
    function exportMaintenance() {
        const urlParams = new URLSearchParams(window.location.search);
        const input = document.getElementById('currentActiveTab');
        const currentTab = input ? input.value : 'pending';
        urlParams.set('tab', currentTab);
        window.location.href = '<?= url('staff/maintenance/export') ?>?' + urlParams.toString();
    }

    // 5. Xử lý Modals
    // Modal Phân công
    function openAssignModal(maPhieu, tenThietBi, currentTechId, maKhu) {
        document.getElementById('assignModalMaPhieu').value = maPhieu;
        document.getElementById('assignModalMaPhieuText').innerText = maPhieu;
        document.getElementById('assignModalTenThietBi').innerText = tenThietBi;

        const select = document.getElementById('selectAssignTho');
        if (select) {
            select.value = currentTechId || '';
        }
        document.getElementById('modalAssignTech').style.display = 'flex';
    }

    function closeAssignModal() {
        document.getElementById('modalAssignTech').style.display = 'none';
    }

    // Modal Cập nhật tiến độ
    function openProgressModal(maPhieu, moTa, currentNote) {
        document.getElementById('progressModalMaPhieu').value = maPhieu;
        document.getElementById('progressModalMaPhieuText').innerText = maPhieu;
        document.getElementById('progressModalMoTa').innerText = moTa || 'Không có mô tả chi tiết';
        document.getElementById('textareaProgressGhiChu').value = currentNote || '';
        document.getElementById('modalProgress').style.display = 'flex';
    }

    function closeProgressModal() {
        document.getElementById('modalProgress').style.display = 'none';
    }

    // Modal Hoàn thành
    function openCompleteModal(maPhieu, tenThietBi) {
        document.getElementById('completeModalMaPhieu').value = maPhieu;
        document.getElementById('completeModalMaPhieuText').innerText = maPhieu;
        document.getElementById('modalComplete').style.display = 'flex';
    }

    function closeCompleteModal() {
        document.getElementById('modalComplete').style.display = 'none';
    }

    // Modal Lập phiếu sự cố hạ tầng
    function openStaffTicketModal() {
        document.getElementById('modalStaffTicket').style.display = 'flex';
    }

    function closeStaffTicketModal() {
        document.getElementById('modalStaffTicket').style.display = 'none';
    }

    // Modal Hủy phiếu
    function openCancelModal(maPhieu, tenThietBi) {
        document.getElementById('cancelModalMaPhieu').value = maPhieu;
        document.getElementById('cancelModalMaPhieuText').innerText = maPhieu;
        document.getElementById('cancelModalTenThietBi').innerText = tenThietBi;
        document.getElementById('inputCancelReason').value = '';
        document.getElementById('selectQuickCancelReason').value = '';
        document.getElementById('modalCancelTicket').style.display = 'flex';
    }

    function closeCancelModal() {
        document.getElementById('modalCancelTicket').style.display = 'none';
    }

    function onQuickCancelChange(val) {
        if (val) {
            document.getElementById('inputCancelReason').value = val;
        }
    }

    // Khởi chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        ['pending', 'fixing', 'infra', 'completed', 'cancelled'].forEach(k => {
            renderMaintenancePagination(k);
        });

        const input = document.getElementById('currentActiveTab');
        const initialTab = input ? input.value : 'pending';
        updateExportButtonCount(initialTab);
    });

    // ESC & Backdrop close
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAssignModal();
            closeProgressModal();
            closeCompleteModal();
            closeStaffTicketModal();
            closeCancelModal();
        }
    });

    window.addEventListener('click', function(e) {
        const modals = [
            document.getElementById('modalAssignTech'),
            document.getElementById('modalProgress'),
            document.getElementById('modalComplete'),
            document.getElementById('modalStaffTicket'),
            document.getElementById('modalCancelTicket')
        ];
        modals.forEach(m => {
            if (m && e.target === m) {
                m.style.display = 'none';
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
$title = !empty($isAllowed)
    ? "Điều phối Sửa chữa & Phiếu Báo Hỏng - KTX ĐHQG-HCM"
    : "Từ chối truy cập - Điều phối Bảo trì";
require dirname(__DIR__) . '/layouts/main.php';
?>