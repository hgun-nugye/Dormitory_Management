<?php
ob_start();

$isAllowed = $scope['isAllowed'] ?? false;
$activeTab = $activeTab ?? 'pending';
$kpiData = $kpis ?? ['pending' => 0, 'transfer' => 0, 'checkout' => 0, 'resolved' => 0, 'total' => 0];

// Helper làm sạch tên tòa: tránh lặp "Tòa Tòa A01"
$formatTenToa = function (?string $rawName, ?string $code = ''): string {
    if (!$rawName) return $code ?: '---';
    $clean = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', trim($rawName));
    return 'Tòa ' . (!empty($clean) ? $clean : $rawName);
};

// Helper render lý do đơn & lý do từ chối nếu có
function renderRequestReason(?string $rawReason): void
{
    if (empty($rawReason)) {
        echo "<span style='color: var(--text-muted); font-size: 0.8rem;'>Không có ghi chú</span>";
        return;
    }
    if (str_contains($rawReason, '[BQL từ chối:')) {
        $parts = explode('[BQL từ chối:', $rawReason);
        $svReason = trim($parts[0] ?? '');
        $bqlReason = rtrim(trim($parts[1] ?? ''), ']');
        if (!empty($svReason)) {
            echo "<div style='font-size: 0.8rem; color: var(--text-secondary);'><em>Lý do SV:</em> " . htmlspecialchars($svReason) . "</div>";
        }
        echo "<div style='font-size: 0.8rem; color: #991b1b; font-weight: 600; margin-top: 4px; background: #fef2f2; padding: 3px 8px; border-radius: 4px; display: inline-block;'>🛑 BQL từ chối: " . htmlspecialchars($bqlReason) . "</div>";
    } else {
        echo "<div style='font-size: 0.8rem; color: var(--text-secondary);'><em>Lý do:</em> " . htmlspecialchars($rawReason) . "</div>";
    }
}
?>

<div class="container">

    <!-- BREADCRUMB & HEADER (ĐỒNG BỘ CHUẨN ZONES, ROOMS, UTILITIES) -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Dịch vụ Một Cửa</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Dịch vụ Một Cửa</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ Quản lý') ?>
                    </span>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ ADMIN & ROOMS) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($isAllowed)): ?>
                    <button type="button" class="btn btn-outline" onclick="exportRequests()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất dữ liệu theo tab đang xem ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (<?= count($pendingRequests ?? []) ?>)</span>
                    </button>
                <?php endif; ?>

                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <b>Thành công:</b> <?= $success ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($error) ?>
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
        <!-- TRƯỜNG HỢP 2: CÁN BỘ ĐƯỢC PHÉP TRUY CẬP (ĐỒNG BỘ CHUẨN GIAO DIỆN)          -->
        <!-- ========================================================================= -->

        <!-- 4 THẺ KPI THỐNG KÊ NHANH (QUICK STATS CARDS CHUẨN TỶ LỆ ROOMS & APPS) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
            <!-- Thẻ 1: Chờ xử lý mới -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Chờ xử lý mới</span>
                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Hạn 24h</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #b45309; margin: 4px 0;">
                    <?= number_format($kpiData['pending'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">đơn</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Cần cán bộ quản lý phê duyệt</div>
            </div>

            <!-- Thẻ 2: Xin chuyển phòng -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Xin chuyển phòng</span>
                    <span style="background: #dbeafe; color: #1e40af; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Đổi giường</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #1e40af; margin: 4px 0;">
                    <?= number_format($kpiData['transfer'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">đơn</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Đổi phòng & tính bù trừ chênh lệch</div>
            </div>

            <!-- Thẻ 3: Trả phòng & Tạm vắng -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Trả phòng & Tạm vắng</span>
                    <span style="background: #f3e8ff; color: #7e22ce; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Lưu trú</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #7e22ce; margin: 4px 0;">
                    <?= number_format($kpiData['checkout'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">đơn</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Thanh lý hợp đồng & quản lý cư trú</div>
            </div>

            <!-- Thẻ 4: Đã giải quyết / Hoàn tất -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Đã giải quyết</span>
                    <span style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Hoàn tất</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #166534; margin: 4px 0;">
                    <?= number_format($kpiData['resolved'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">/ <?= (int)($kpiData['total'] ?? 0) ?></span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Đã được thẩm định & phê duyệt</div>
            </div>
        </div>

        <!-- KHỐI BỘ LỌC ĐA TẦNG CASCADING (ĐỒNG BỘ BACKGROUND #f8fafc & PADDING CHUẨN ROOMS/ADMIN) -->
        <div class="card" style="margin-bottom: 24px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #f8fafc;">
            <form method="GET" action="<?= url('staff/requests') ?>" id="filterFormRequests" style="margin: 0;">
                <input type="hidden" name="tab" id="currentActiveTab" value="<?= htmlspecialchars($activeTab ?? 'pending') ?>">

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- 1. Ô tìm kiếm tức thời -->
                    <div style="position: relative; flex: 1; min-width: 240px; max-width: 320px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                        <input type="text" name="search" id="inputSearch" class="btn-outline"
                            placeholder="Tìm mã đơn, MSSV, tên SV, SĐT, phòng..."
                            value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                            style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;">
                    </div>

                    <!-- 2. Dropdown Loại đơn -->
                    <select name="type" class="btn-outline" style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormRequests').submit()">
                        <option value="all" <?= ($selectedType === 'all') ? 'selected' : '' ?>>-- Tất cả loại đơn --</option>
                        <option value="chuyen-phong" <?= ($selectedType === 'chuyen-phong') ? 'selected' : '' ?>>Xin chuyển phòng</option>
                        <option value="tra-phong" <?= ($selectedType === 'tra-phong') ? 'selected' : '' ?>>Trả phòng sớm</option>
                        <option value="tam-vang" <?= ($selectedType === 'tam-vang') ? 'selected' : '' ?>>Xin tạm vắng</option>
                        <option value="dong-phi" <?= ($selectedType === 'dong-phi') ? 'selected' : '' ?>>Đăng ký đóng phí</option>
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
                        <select name="toa" id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormRequests').submit()">
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
                    <select name="tang" class="btn-outline" style="min-width: 125px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormRequests').submit()">
                        <option value="">-- Tất cả tầng --</option>
                        <?php for ($t = 1; $t <= 12; $t++): ?>
                            <option value="<?= $t ?>" <?= ((string)$selectedTang === (string)$t) ? 'selected' : '' ?>>Tầng <?= $t ?></option>
                        <?php endfor; ?>
                    </select>

                    <!-- 7. Dropdown Trạng thái -->
                    <select name="status" class="btn-outline" style="min-width: 145px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormRequests').submit()">
                        <option value="all" <?= ($selectedStatus === 'all') ? 'selected' : '' ?>>-- Tất cả trạng thái --</option>
                        <option value="cho-duyet" <?= ($selectedStatus === 'cho-duyet') ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="da-duyet" <?= ($selectedStatus === 'da-duyet') ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="tu-choi" <?= ($selectedStatus === 'tu-choi') ? 'selected' : '' ?>>Từ chối</option>
                    </select>

                    <!-- Nút Lọc & Nút Xóa bộ lọc -->
                    <?php if (!empty($searchTerm) || $selectedType !== 'all' || $selectedStatus !== 'all' || !empty($selectedKhu) || !empty($selectedCum) || !empty($selectedToa) || !empty($selectedTang)): ?>
                        <a href="<?= url('staff/requests?tab=' . urlencode($activeTab)) ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; height: 38px; padding: 0 14px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; color: #dc2626; border-color: #fca5a5; background: #fff5f5; font-weight: 600;" title="Xóa toàn bộ bộ lọc và trở về mặc định">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 4 SUB-TABS ĐIỀU HƯỚNG (ĐỒNG BỘ CHUẨN APPLICATIONS VỚI PILL BADGES) -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
            <button type="button" class="tab-btn <?= ($activeTab === 'pending') ? 'active' : '' ?>" id="tabBtnPending" onclick="switchRequestTab('pending')"
                style="background: <?= ($activeTab === 'pending') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'pending') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'pending') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Chờ xét duyệt (24h)
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #fef3c7; color: #92400e; border: none; font-weight: 700;"><?= count($pendingRequests ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'transfer') ? 'active' : '' ?>" id="tabBtnTransfer" onclick="switchRequestTab('transfer')"
                style="background: <?= ($activeTab === 'transfer') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'transfer') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'transfer') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Xin chuyển phòng
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #dbeafe; color: #1e40af; border: none; font-weight: 700;"><?= count($transferRequests ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'checkout') ? 'active' : '' ?>" id="tabBtnCheckout" onclick="switchRequestTab('checkout')"
                style="background: <?= ($activeTab === 'checkout') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'checkout') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'checkout') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Trả phòng & Tạm vắng
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #f3e8ff; color: #7e22ce; border: none; font-weight: 700;"><?= count($checkoutRequests ?? []) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'history') ? 'active' : '' ?>" id="tabBtnHistory" onclick="switchRequestTab('history')"
                style="background: <?= ($activeTab === 'history') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'history') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'history') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Lịch sử đã giải quyết
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #f1f5f9; color: #475569; border: none; font-weight: 700;"><?= count($historyRequests ?? []) ?></span>
            </button>
        </div>

        <?php
        // Danh sách render theo từng Tab
        $tabConfigs = [
            'pending' => ['title' => 'Đơn Chờ xét duyệt (Ưu tiên giải quyết trong 24h)', 'list' => $pendingRequests ?? []],
            'transfer' => ['title' => 'Danh sách Đơn Xin chuyển phòng & Bù trừ chi phí', 'list' => $transferRequests ?? []],
            'checkout' => ['title' => 'Danh sách Đơn Xin trả phòng sớm & Xin tạm vắng', 'list' => $checkoutRequests ?? []],
            'history' => ['title' => 'Lịch sử giải quyết Đơn từ Một Cửa (Đã duyệt & Đã từ chối)', 'list' => $historyRequests ?? []]
        ];
        ?>

        <?php foreach ($tabConfigs as $tabKey => $cfg): ?>
            <div id="tabContent_<?= $tabKey ?>" style="display: <?= ($activeTab === $tabKey) ? 'block' : 'none' ?>;">
                <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span><?= htmlspecialchars($cfg['title']) ?></span>
                            <span class="badge badge-info"><?= count($cfg['list']) ?> hồ sơ</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="data-table" id="table_<?= $tabKey ?>">
                            <thead>
                                <tr>
                                    <th>Mã Đơn</th>
                                    <th>Sinh Viên</th>
                                    <th>Loại Đơn & Vị Trí Hiện Tại</th>
                                    <th>Đề Xuất / Chi Tiết</th>
                                    <th>Lý Do / Minh Chứng</th>
                                    <th>Trạng Thái</th>
                                    <th style="text-align: right; width: 180px; min-width: 180px; white-space: nowrap;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_<?= $tabKey ?>">
                                <?php if (empty($cfg['list'])): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                            <div style="font-size: 2.2rem; margin-bottom: 6px;">📂</div>
                                            <div style="font-weight: 600; color: var(--text-secondary);">Không có hồ sơ đơn từ nào trong mục này.</div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($cfg['list'] as $req): ?>
                                        <?php
                                        $status = $req['TrangThaiDuyet'] ?? '';
                                        $isPending = ($status === 'Chờ duyệt');
                                        $isApproved = ($status === 'Đã duyệt');
                                        $isRejected = ($status === 'Từ chối' || $status === 'Đã từ chối');
                                        $loaiDon = $req['LoaiDon'] ?? '';
                                        $isTransfer = str_contains(mb_strtolower($loaiDon), 'chuyển phòng');
                                        $isCheckout = str_contains(mb_strtolower($loaiDon), 'trả phòng');
                                        $isAbsence = str_contains(mb_strtolower($loaiDon), 'tạm vắng');
                                        ?>
                                        <tr class="<?= $tabKey ?>-row">
                                            <!-- 1. Mã đơn -->
                                            <td>
                                                <b style="font-family: monospace; color: var(--primary); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($req['MaDon']) ?>
                                                </b>
                                            </td>

                                            <!-- 2. Sinh viên -->
                                            <td>
                                                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">
                                                    <?= htmlspecialchars($req['HoVaTen'] ?? 'N/A') ?>
                                                </div>
                                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                    MSSV: <b><?= htmlspecialchars($req['MSSVTaiTruong'] ?? $req['MaSinhVien']) ?></b> &bull; <?= htmlspecialchars($req['SoDienThoai'] ?? '---') ?>
                                                </div>
                                                <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">
                                                    <?= htmlspecialchars($req['TenTruong'] ?? '') ?>
                                                </div>
                                            </td>

                                            <!-- 3. Loại đơn & Vị trí hiện tại -->
                                            <td>
                                                <span class="badge" style="background: #f1f5f9; color: #334155; border: none; font-weight: 700; padding: 3px 8px; font-size: 0.8rem;">
                                                    <?= htmlspecialchars($loaiDon) ?>
                                                </span>
                                                <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-top: 6px;">
                                                    P.<?= htmlspecialchars($req['SoPhongHienTai'] ?? '---') ?> (<?= htmlspecialchars($formatTenToa($req['TenToaHienTai'] ?? '', '')) ?>)
                                                </div>
                                                <div style="font-size: 0.78rem; color: var(--text-muted);">
                                                    Giường <?= htmlspecialchars($req['MaGiuongHienTai'] ?? '---') ?>
                                                </div>
                                            </td>

                                            <!-- 4. Đề xuất / Chi tiết -->
                                            <td>
                                                <?php if ($isTransfer): ?>
                                                    <div style="font-size: 0.85rem;">
                                                        <span style="color: var(--text-muted);">Đổi sang:</span>
                                                        <b style="color: #1e40af;">P.<?= htmlspecialchars($req['SoPhongMoi'] ?? '---') ?> (<?= htmlspecialchars($formatTenToa($req['TenToaMoi'] ?? '', '')) ?>)</b>
                                                    </div>
                                                    <div style="font-size: 0.8rem; color: #059669; font-weight: 600; margin-top: 2px;">
                                                        Giường: <?= htmlspecialchars($req['MaGiuongChuyenDen'] ?? '---') ?>
                                                    </div>
                                                    <?php if ((float)$req['SoTienChenhLechHoan'] != 0): ?>
                                                        <div style="font-size: 0.78rem; color: #d97706; margin-top: 2px;">
                                                            Bù trừ: <b><?= number_format($req['SoTienChenhLechHoan'], 0, ',', '.') ?> đ</b>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php elseif ($isAbsence): ?>
                                                    <div style="font-size: 0.82rem; color: var(--text-primary);">
                                                        Từ: <b><?= !empty($req['TuNgay']) ? date('d/m/Y', strtotime($req['TuNgay'])) : '---' ?></b><br>
                                                        Đến: <b><?= !empty($req['DenNgay']) ? date('d/m/Y', strtotime($req['DenNgay'])) : '---' ?></b>
                                                    </div>
                                                <?php elseif ($isCheckout): ?>
                                                    <div style="font-size: 0.82rem; color: #dc2626; font-weight: 600;">
                                                        <i class="fa-solid fa-box-archive" style="margin-right: 4px;"></i> Thanh lý hợp đồng
                                                    </div>
                                                <?php else: ?>
                                                    <span style="font-size: 0.8rem; color: var(--text-muted);">Thủ tục hành chính</span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- 5. Lý do / Minh chứng -->
                                            <td style="max-width: 280px;">
                                                <?php renderRequestReason($req['LyDo'] ?? ''); ?>
                                                <?php if (!empty($req['TepMinhChung'])): ?>
                                                    <div style="margin-top: 4px;">
                                                        <a href="<?= url('uploads/' . htmlspecialchars($req['TepMinhChung'])) ?>" target="_blank" style="font-size: 0.75rem; color: #2563eb; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                                            <i class="fa-solid fa-paperclip"></i> Xem tệp đính kèm
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                            <!-- 6. Trạng thái -->
                                            <td>
                                                <?php if ($isApproved): ?>
                                                    <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã duyệt
                                                    </span>
                                                <?php elseif ($isRejected): ?>
                                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Từ chối
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                        <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Chờ duyệt
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <!-- 7. Thao tác -->
                                            <td style="text-align: right; white-space: nowrap; width: 180px; min-width: 180px;">
                                                <?php if ($isPending && !empty($scope['canApprove'])): ?>
                                                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                                        <button type="button" class="btn btn-primary btn-sm" onclick="openApproveModal('<?= htmlspecialchars($req['MaDon']) ?>', '<?= htmlspecialchars($loaiDon) ?>', '<?= htmlspecialchars($req['HoVaTen'] ?? '') ?>', '<?= $isTransfer ? ('Chuyển từ P.' . ($req['SoPhongHienTai'] ?? '') . ' sang P.' . ($req['SoPhongMoi'] ?? '')) : '' ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 12px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 5px; height: 32px; white-space: nowrap;">
                                                            <i class="fa-solid fa-check"></i> Duyệt
                                                        </button>
                                                        <button type="button" class="btn btn-outline btn-sm" onclick="openRejectModal('<?= htmlspecialchars($req['MaDon']) ?>', '<?= htmlspecialchars($req['HoVaTen'] ?? '') ?>')" style="border-radius: 6px; color: #dc2626; border-color: #fca5a5; background: #fff5f5; font-weight: 600; padding: 5px 12px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 5px; height: 32px; white-space: nowrap;">
                                                            <i class="fa-solid fa-xmark"></i> Từ chối
                                                        </button>
                                                    </div>
                                                <?php else: ?>
                                                    <div style="display: inline-flex; align-items: center; justify-content: flex-end; white-space: nowrap;">
                                                        <?php if ($isApproved): ?>
                                                            <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                                <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã hoàn tất
                                                            </span>
                                                        <?php elseif ($isRejected): ?>
                                                            <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-size: 0.78rem; font-weight: 600; padding: 4px 10px; border-radius: 6px;">
                                                                <i class="fa-solid fa-circle-xmark" style="margin-right: 4px;"></i> Đã từ chối
                                                            </span>
                                                        <?php else: ?>
                                                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Đã giải quyết</span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
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
                                    Hiển thị 1 - <?= min(10, count($cfg['list'])) ?> trên tổng số <?= count($cfg['list']) ?> bản ghi
                                </span>
                                <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted);">
                                    <span>Hiển thị:</span>
                                    <select id="<?= $tabKey ?>PageSizeSelect" onchange="changeRequestPageSize('<?= $tabKey ?>', this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
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
<!-- MODAL: PHÊ DUYỆT ĐƠN MỘT CỬA                                              -->
<!-- ========================================================================= -->
<div id="modalApproveRequest" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #15803d; margin: 0;">
                Xác nhận phê duyệt đơn
            </h3>
            <button type="button" onclick="closeApproveModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
            Bạn có chắc chắn muốn phê duyệt hồ sơ đơn này? Hệ thống sẽ tự động cập nhật hợp đồng, hoán đổi giường và đồng bộ sĩ số phòng liên quan.
        </p>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; font-size: 0.85rem;">
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-muted);">Mã đơn:</span>
                <b id="approveModalMaDonText" style="font-family: monospace; color: var(--primary);">---</b>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-muted);">Sinh viên:</span>
                <b id="approveModalStudentName">---</b>
            </div>
            <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                <span style="color: var(--text-muted);">Loại đơn:</span>
                <span id="approveModalLoaiDon" style="font-weight: 600; color: #1e40af;">---</span>
            </div>
            <div id="approveModalDetailRow" style="display: none; justify-content: space-between;">
                <span style="color: var(--text-muted);">Chi tiết:</span>
                <span id="approveModalDetailText" style="font-weight: 600; color: #059669;">---</span>
            </div>
        </div>

        <form action="<?= url('staff/requests/approve') ?>" method="POST">
            <input type="hidden" name="maDon" id="approveModalMaDon">
            <input type="hidden" name="tab" id="approveModalTab" value="<?= htmlspecialchars($activeTab ?? 'pending') ?>">

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeApproveModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Xác nhận phê duyệt
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL: TỪ CHỐI ĐƠN MỘT CỬA (KÈM CHỌN LÝ DO NHANH)                        -->
<!-- ========================================================================= -->
<div id="modalRejectRequest" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 540px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #dc2626; margin: 0;">
                Từ chối yêu cầu của Sinh viên
            </h3>
            <button type="button" onclick="closeRejectModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 0.85rem; color: #991b1b;">
            Từ chối đơn: <b id="rejectModalMaDonText" style="font-family: monospace;">---</b> của sinh viên <b id="rejectModalStudentName">---</b>
        </div>

        <form action="<?= url('staff/requests/reject') ?>" method="POST">
            <input type="hidden" name="maDon" id="rejectModalMaDon">
            <input type="hidden" name="tab" id="rejectModalTab" value="<?= htmlspecialchars($activeTab ?? 'pending') ?>">

            <!-- Lý do chọn nhanh -->
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn nhanh lý do mẫu:</label>
                <select id="selectQuickReason" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem; background: #fff;" onchange="onQuickReasonChange(this.value)">
                    <option value="">-- Tùy chọn lý do mẫu --</option>
                    <option value="Giường chuyển đến hiện không còn khả dụng hoặc đã có người khác giữ chỗ.">Giường mới không còn trống</option>
                    <option value="Hợp đồng nội trú hiện tại của sinh viên không đủ điều kiện theo quy chế.">Hợp đồng không đủ điều kiện</option>
                    <option value="Sinh viên còn công nợ tiền phòng hoặc tiền điện nước chưa thanh toán.">Còn công nợ chưa hoàn tất</option>
                    <option value="Lý do xin chuyển/trả phòng không đáp ứng quy chế quản lý nội trú KTX.">Không đáp ứng quy chế</option>
                    <option value="Thời gian xin tạm vắng trùng với đợt kiểm tra quy chế lưu trú tập trung.">Trùng đợt kiểm tra tập trung</option>
                </select>
            </div>

            <!-- Ô nhập lý do chi tiết -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                    Lý do từ chối cụ thể: <span style="color: #dc2626;">*</span>
                </label>
                <textarea name="lyDoTuChoi" id="inputRejectReason" rows="3" class="btn-outline" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #fff; resize: vertical;" placeholder="Nhập lý do cụ thể để phản hồi cho sinh viên..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">Hủy</button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px; background: #dc2626; border-color: #dc2626; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-xmark"></i> Xác nhận từ chối
                </button>
            </div>
        </form>
    </div>
</div>
</div>
</form>
</div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: TABS, PHÂN TRANG CLIENT-SIDE, MODALS VÀ XUẤT EXCEL            -->
<!-- ========================================================================= -->
<script>
    // 1. Quản lý phân trang cho cả 4 tabs
    const requestPaginationState = {
        pending: {
            page: 1,
            pageSize: 10
        },
        transfer: {
            page: 1,
            pageSize: 10
        },
        checkout: {
            page: 1,
            pageSize: 10
        },
        history: {
            page: 1,
            pageSize: 10
        }
    };

    function renderRequestPagination(tabKey) {
        const rowClass = `${tabKey}-row`;
        const rows = document.querySelectorAll(`.${rowClass}`);
        const total = rows.length;
        const state = requestPaginationState[tabKey];
        if (!state) return;

        const totalPages = Math.max(1, Math.ceil(total / state.pageSize));
        if (state.page > totalPages) state.page = totalPages;

        const startIdx = (state.page - 1) * state.pageSize;
        const endIdx = startIdx + state.pageSize;

        rows.forEach((row, idx) => {
            if (idx >= startIdx && idx < endIdx) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        const infoEl = document.getElementById(`${tabKey}PaginationInfo`);
        if (infoEl) {
            if (total === 0) {
                infoEl.innerText = 'Không có bản ghi nào';
            } else {
                const curStart = startIdx + 1;
                const curEnd = Math.min(endIdx, total);
                infoEl.innerText = `Hiển thị ${curStart} - ${curEnd} trên tổng số ${total} hồ sơ (Trang ${state.page} / ${totalPages})`;
            }
        }

        const navEl = document.getElementById(`${tabKey}PaginationNav`);
        if (navEl) {
            if (totalPages <= 1) {
                navEl.innerHTML = '';
                return;
            }

            let html = '';
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRequestPage('${tabKey}', ${state.page - 1})" ${state.page === 1 ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''} style="padding: 4px 10px; border-radius: 6px;">&laquo; Trước</button>`;

            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= state.page - 1 && p <= state.page + 1)) {
                    const isActive = (p === state.page);
                    const btnStyle = isActive ?
                        'background: var(--primary); color: #fff; border-color: var(--primary);' :
                        'background: #fff; color: var(--text-primary); border-color: #cbd5e1;';
                    html += `<button type="button" class="btn btn-sm" onclick="goToRequestPage('${tabKey}', ${p})" style="padding: 4px 10px; border-radius: 6px; font-weight: 600; ${btnStyle}">${p}</button>`;
                } else if (p === state.page - 2 || p === state.page + 2) {
                    html += `<span style="padding: 4px 6px; color: var(--text-muted);">&hellip;</span>`;
                }
            }

            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRequestPage('${tabKey}', ${state.page + 1})" ${state.page === totalPages ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''} style="padding: 4px 10px; border-radius: 6px;">Sau &raquo;</button>`;
            navEl.innerHTML = html;
        }
    }

    function goToRequestPage(tabKey, targetPage) {
        const rowClass = `${tabKey}-row`;
        const rows = document.querySelectorAll(`.${rowClass}`);
        const state = requestPaginationState[tabKey];
        if (!state) return;
        const totalPages = Math.max(1, Math.ceil(rows.length / state.pageSize));

        if (targetPage < 1 || targetPage > totalPages) return;
        state.page = targetPage;
        renderRequestPagination(tabKey);
    }

    function changeRequestPageSize(tabKey, newSize) {
        if (!requestPaginationState[tabKey]) return;
        requestPaginationState[tabKey].pageSize = parseInt(newSize, 10) || 10;
        requestPaginationState[tabKey].page = 1;
        renderRequestPagination(tabKey);
    }

    // 2. Chuyển đổi Sub-Tabs & Cập nhật số đếm nút Xuất Excel
    function updateExportButtonCount(tabKey) {
        const btnText = document.getElementById('btnExportText');
        if (!btnText) return;

        let count = 0;
        const rows = document.querySelectorAll(`.${tabKey}-row`);
        count = rows.length;
        btnText.innerText = `Xuất Excel (${count})`;
    }

    function switchRequestTab(tabKey) {
        const input = document.getElementById('currentActiveTab');
        if (input) input.value = tabKey;

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.color = 'var(--text-secondary)';
            b.style.background = 'transparent';
            b.style.fontWeight = '600';
            b.classList.remove('active');
        });

        ['pending', 'transfer', 'checkout', 'history'].forEach(k => {
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

    // 3. Cascading Dropdown Handlers
    function onKhuChange(khuVal) {
        const cumSelect = document.getElementById('filterCumSelect');
        const toaSelect = document.getElementById('filterToaSelect');
        if (cumSelect) cumSelect.value = '';
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormRequests').submit();
    }

    function onCumChange(cumVal) {
        const toaSelect = document.getElementById('filterToaSelect');
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormRequests').submit();
    }

    // 4. Xuất Excel
    function exportRequests() {
        const urlParams = new URLSearchParams(window.location.search);
        const input = document.getElementById('currentActiveTab');
        const currentTab = input ? input.value : 'pending';
        urlParams.set('tab', currentTab);
        window.location.href = '<?= url('staff/requests/export') ?>?' + urlParams.toString();
    }

    // 5. Modals: Duyệt & Từ chối
    function openApproveModal(maDon, loaiDon, studentName, detailInfo) {
        document.getElementById('approveModalMaDon').value = maDon;
        document.getElementById('approveModalMaDonText').innerText = maDon;
        document.getElementById('approveModalStudentName').innerText = studentName;
        document.getElementById('approveModalLoaiDon').innerText = loaiDon;

        const rowDetail = document.getElementById('approveModalDetailRow');
        if (detailInfo) {
            document.getElementById('approveModalDetailText').innerText = detailInfo;
            rowDetail.style.display = 'flex';
        } else {
            rowDetail.style.display = 'none';
        }

        const curTab = document.getElementById('currentActiveTab');
        document.getElementById('approveModalTab').value = curTab ? curTab.value : 'pending';
        document.getElementById('modalApproveRequest').style.display = 'flex';
    }

    function closeApproveModal() {
        document.getElementById('modalApproveRequest').style.display = 'none';
    }

    function openRejectModal(maDon, studentName) {
        document.getElementById('rejectModalMaDon').value = maDon;
        document.getElementById('rejectModalMaDonText').innerText = maDon;
        document.getElementById('rejectModalStudentName').innerText = studentName;
        document.getElementById('inputRejectReason').value = '';
        document.getElementById('selectQuickReason').value = '';

        const curTab = document.getElementById('currentActiveTab');
        document.getElementById('rejectModalTab').value = curTab ? curTab.value : 'pending';
        document.getElementById('modalRejectRequest').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('modalRejectRequest').style.display = 'none';
    }

    function onQuickReasonChange(val) {
        if (val) {
            document.getElementById('inputRejectReason').value = val;
        }
    }

    // Khởi chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        renderRequestPagination('pending');
        renderRequestPagination('transfer');
        renderRequestPagination('checkout');
        renderRequestPagination('history');

        const input = document.getElementById('currentActiveTab');
        const initialTab = input ? input.value : 'pending';
        updateExportButtonCount(initialTab);
    });

    // ESC & Backdrop close
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApproveModal();
            closeRejectModal();
        }
    });

    window.addEventListener('click', function(e) {
        const modalApprove = document.getElementById('modalApproveRequest');
        if (e.target === modalApprove) closeApproveModal();

        const modalReject = document.getElementById('modalRejectRequest');
        if (e.target === modalReject) closeRejectModal();
    });
</script>

<?php
$content = ob_get_clean();
$title = !empty($isAllowed)
    ? "Dịch vụ Một Cửa - KTX ĐHQG-HCM"
    : "Từ chối truy cập - Dịch vụ Một Cửa";
require dirname(__DIR__) . '/layouts/main.php';
?>