<?php
ob_start();

$isAllowed = $scope['isAllowed'] ?? false;
$currentMonth = $thang ?? (int)date('n');
$currentYear = $nam ?? (int)date('Y');
$roomList = $rooms ?? [];
$readingList = $readings ?? [];
$invoiceList = $invoices ?? [];
$missingList = $missingRooms ?? [];
$meta = $roomMetadata ?? [];
$kpiData = $kpis ?? ['tongDien' => 0, 'tongNuoc' => 0, 'batThuong' => 0, 'chuaThu' => 0, 'daGhi' => 0, 'tongPhong' => 0];

// Helper làm sạch tên tòa nhà: tránh lặp "Tòa Tòa A01"
$formatTenToa = function (?string $rawName, ?string $code = ''): string {
    if (!$rawName) return $code ?: '---';
    $clean = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', trim($rawName));
    return 'Tòa ' . (!empty($clean) ? $clean : $rawName);
};
?>

<div class="container">

    <!-- BREADCRUMB & HEADER (ĐỒNG BỘ CHUẨN ZONES, ROOMS & APPLICATIONS) -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-muted);">Điện nước & Tiện ích</span>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Ghi nhận Điện Nước & Công nợ</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Quản lý Điện Nước & Đối soát Công nợ</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ Quản lý') ?>
                    </span>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ ADMIN, ROOMS & APPLICATIONS) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($isAllowed)): ?>
                    <?php if (!empty($scope['canPublishCluster'])): ?>
                        <form method="POST" action="<?= url('staff/utilities/publish-cluster') ?>" style="display: inline;"
                            onsubmit="return confirm('Xác nhận chốt sổ và phát hành hóa đơn điện nước tháng <?= $currentMonth ?>/<?= $currentYear ?> cho tất cả các phòng trong phạm vi quản lý?');">
                            <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                            <input type="hidden" name="nam" value="<?= $currentYear ?>">
                            <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                                <i class="fa-solid fa-bullhorn"></i> Chốt sổ & Phát hành HĐ
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($scope['canRecordReading'])): ?>
                        <button type="button" class="btn btn-outline" onclick="openImportModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách chỉ số từ file CSV">
                            <i class="fa-solid fa-file-import" style="color: #2563eb;"></i> Nhập file
                        </button>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline" onclick="exportUtilities()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất dữ liệu theo tab đang xem ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (<?= count($readingList) ?>)</span>
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
        <!-- TRƯỜNG HỢP 2: CÁN BỘ ĐƯỢC PHÉP TRUY CẬP                                   -->
        <!-- ========================================================================= -->

        <!-- 4 THẺ KPI THỐNG KÊ NHANH (QUICK STATS CARDS CHUẨN TỶ LỆ ROOMS & APPS) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
            <!-- Thẻ 1: Tiêu thụ Điện -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Tiêu thụ Điện (kWh)</span>
                    <span style="background: #e0f2fe; color: #0284c7; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Biểu giá 6 bậc</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0284c7; margin: 4px 0;">
                    <?= number_format($kpiData['tongDien'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">kWh</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Biểu giá lũy tiến chuẩn EVN</div>
            </div>

            <!-- Thẻ 2: Tiêu thụ Nước -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Tiêu thụ Nước (m³)</span>
                    <span style="background: #ccfbf1; color: #0d9488; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Đơn giá KTX</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0d9488; margin: 4px 0;">
                    <?= number_format($kpiData['tongNuoc'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">m³</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Đơn giá nước sạch: 12.500 đ/m³</div>
            </div>

            <!-- Thẻ 3: Cảnh báo Rò rỉ / Chập -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#fffbeb' : '#ffffff' ?>;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#b45309' : 'var(--text-muted)' ?>; font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Cảnh báo Rò rỉ / Chập</span>
                    <span style="background: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#fee2e2' : '#f1f5f9' ?>; color: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#dc2626' : '#64748b' ?>; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">
                        <?= ($kpiData['batThuong'] ?? 0) > 0 ? 'Nguy cơ cao' : 'Bình thường' ?>
                    </span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#dc2626' : '#16a34a' ?>; margin: 4px 0;">
                    <?= (int)($kpiData['batThuong'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">phòng</span>
                </div>
                <div style="font-size: 0.8rem; color: <?= ($kpiData['batThuong'] ?? 0) > 0 ? '#b45309' : 'var(--text-muted)' ?>;">Tăng vọt gấp &gt;3 lần trung bình 3T</div>
            </div>

            <!-- Thẻ 4: Tiến độ & Công nợ -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Tiến độ & Công nợ</span>
                    <span style="background: #fef3c7; color: #b45309; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 700;">Kỳ <?= $currentMonth ?>/<?= $currentYear ?></span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #92400e; margin: 4px 0;">
                    <?= number_format($kpiData['chuaThu'] ?? 0, 0, ',', '.') ?> <span style="font-size: 0.95rem; font-weight: 500;">đ</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; justify-content: space-between;">
                    <span>Đã ghi: <b><?= (int)($kpiData['daGhi'] ?? 0) ?>/<?= (int)($kpiData['tongPhong'] ?? 0) ?></b></span>
                    <span>Chưa ghi: <b style="color: #dc2626;"><?= count($missingList) ?></b></span>
                </div>
            </div>
        </div>

        <!-- KHỐI BỘ LỌC ĐA TẦNG CASCADING (ĐỒNG BỘ BACKGROUND #f8fafc & PADDING CHUẨN ROOMS/ADMIN) -->
        <div class="card" style="margin-bottom: 24px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #f8fafc;">
            <form method="GET" action="<?= url('staff/utilities') ?>" id="filterFormUtilities" style="margin: 0;">
                <input type="hidden" name="tab" id="currentActiveTab" value="<?= htmlspecialchars($activeTab ?? 'readings') ?>">

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- 1. Ô tìm kiếm tức thời -->
                    <div style="position: relative; flex: 1; min-width: 240px; max-width: 340px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                        <input type="text" name="search" id="inputSearch" class="btn-outline"
                            placeholder="Tìm mã phòng, số phòng, mã HĐ, tên SV..."
                            value="<?= htmlspecialchars($searchTerm ?? '') ?>"
                            style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;">
                    </div>

                    <!-- 2. Chọn Kỳ: Tháng & Năm -->
                    <div style="display: flex; gap: 6px; align-items: center; background: #fff; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px; height: 38px;">
                        <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted);"><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i>Kỳ:</span>
                        <select name="thang" class="btn-outline" style="padding: 2px 6px; font-size: 0.85rem; border: none; height: 30px; font-weight: 600; background: transparent;" onchange="document.getElementById('filterFormUtilities').submit()">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m === $currentMonth ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                            <?php endfor; ?>
                        </select>
                        <select name="nam" class="btn-outline" style="padding: 2px 6px; font-size: 0.85rem; border: none; height: 30px; font-weight: 600; background: transparent;" onchange="document.getElementById('filterFormUtilities').submit()">
                            <?php for ($y = 2024; $y <= 2028; $y++): ?>
                                <option value="<?= $y ?>" <?= $y === $currentYear ? 'selected' : '' ?>>Năm <?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <!-- 3. Dropdown Khu -->
                    <?php if ($scope['level'] === 'ALL' || ($scope['level'] === 'KY_THUAT' && empty($scope['maKhu']))): ?>
                        <select name="khu" id="filterKhuSelect" class="btn-outline" style="min-width: 130px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onKhuChange(this.value)">
                            <option value="">-- Tất cả Khu --</option>
                            <?php foreach ($scope['availableKhus'] ?? [] as $k): ?>
                                <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= $selectedKhu === $k['MaKhu'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['TenKhu'] ?? ('Khu ' . $k['MaKhu'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 4. Dropdown Cụm -->
                    <?php if ($scope['level'] === 'ALL' || $scope['level'] === 'KHU' || $scope['level'] === 'KY_THUAT'): ?>
                        <select name="cum" id="filterCumSelect" class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onCumChange(this.value)">
                            <option value="">-- Tất cả Cụm --</option>
                            <?php foreach ($scope['availableCums'] ?? [] as $c): ?>
                                <?php if (empty($selectedKhu) || ($c['MaKhu'] ?? '') === $selectedKhu): ?>
                                    <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= $selectedCum === $c['MaCum'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['TenCum'] ?? ('Cụm ' . $c['MaCum'])) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 5. Dropdown Tòa (Khử lặp nhãn "Tòa Tòa") -->
                    <?php if ($scope['level'] !== 'TOA'): ?>
                        <select name="toa" id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormUtilities').submit()">
                            <option value="">-- Tất cả Tòa nhà --</option>
                            <?php foreach ($scope['availableToas'] ?? [] as $t): ?>
                                <?php
                                $matchCum = empty($selectedCum) || ($t['MaCum'] ?? '') === $selectedCum;
                                $matchKhu = empty($selectedKhu) || ($t['MaKhu'] ?? '') === $selectedKhu;
                                ?>
                                <?php if ($matchCum && $matchKhu): ?>
                                    <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" <?= $selectedToa === $t['MaToaNha'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 6. Dropdown Tầng -->
                    <select name="tang" class="btn-outline" style="min-width: 125px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormUtilities').submit()">
                        <option value="">-- Tất cả tầng --</option>
                        <?php for ($t = 1; $t <= 12; $t++): ?>
                            <option value="<?= $t ?>" <?= (string)$selectedTang === (string)$t ? 'selected' : '' ?>>Tầng <?= $t ?></option>
                        <?php endfor; ?>
                    </select>

                    <!-- 7. Dropdown Trạng thái -->
                    <select name="trangThai" class="btn-outline" style="min-width: 150px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormUtilities').submit()">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="binh_thuong" <?= $selectedTrangThai === 'binh_thuong' ? 'selected' : '' ?>>Tiêu thụ bình thường</option>
                        <option value="bat_thuong" <?= $selectedTrangThai === 'bat_thuong' ? 'selected' : '' ?>>⚠️ Đột biến &gt;3 lần</option>
                        <option value="chua_thanh_toan" <?= $selectedTrangThai === 'chua_thanh_toan' ? 'selected' : '' ?>>Chưa thanh toán</option>
                        <option value="da_thanh_toan" <?= $selectedTrangThai === 'da_thanh_toan' ? 'selected' : '' ?>>Đã thanh toán</option>
                    </select>

                    <!-- Nút Lọc & Nút Xóa bộ lọc -->
                    <?php if (!empty($searchTerm) || !empty($selectedKhu) || !empty($selectedCum) || !empty($selectedToa) || !empty($selectedTang) || !empty($selectedTrangThai)): ?>
                        <a href="<?= url('staff/utilities') ?>?thang=<?= $currentMonth ?>&nam=<?= $currentYear ?>&tab=<?= htmlspecialchars($activeTab ?? 'readings') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; height: 38px; padding: 0 14px; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; color: #dc2626; border-color: #fca5a5; background: #fff5f5; font-weight: 600;" title="Xóa toàn bộ bộ lọc và trở về mặc định">
                            <i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 3 SUB-TABS ĐIỀU HƯỚNG (ĐỒNG BỘ CHUẨN APPLICATIONS VỚI PILL BADGES) -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
            <button type="button" class="tab-btn <?= ($activeTab === 'readings') ? 'active' : '' ?>" id="tabBtnReadings" onclick="switchUtilTab('readings')"
                style="background: <?= ($activeTab === 'readings') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'readings') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'readings') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Ghi nhận Chỉ số & Cảnh báo
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #e0f2fe; color: #0369a1; border: none; font-weight: 700;"><?= count($readingList) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'invoices') ? 'active' : '' ?>" id="tabBtnInvoices" onclick="switchUtilTab('invoices')"
                style="background: <?= ($activeTab === 'invoices') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'invoices') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'invoices') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Hóa đơn & Đối soát Công nợ
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #fef3c7; color: #92400e; border: none; font-weight: 700;"><?= count($invoiceList) ?></span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'missing') ? 'active' : '' ?>" id="tabBtnMissing" onclick="switchUtilTab('missing')"
                style="background: <?= ($activeTab === 'missing') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'missing') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'missing') ? 'var(--primary)' : 'var(--text-secondary)' ?>;">
                Phòng chưa ghi số
                <span class="badge" style="font-size: 0.75rem; margin-left: 6px; padding: 3px 8px; background: #fee2e2; color: #991b1b; border: none; font-weight: 700;"><?= count($missingList) ?></span>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: GHI NHẬN CHỈ SỐ & CẢNH BÁO BẤT THƯỜNG                              -->
        <!-- ========================================================================= -->
        <div id="tabContentReadings" style="display: <?= ($activeTab === 'readings') ? 'block' : 'none' ?>;">

            <!-- FORM GHI NHANH CHỈ SỐ (ĐỒNG BỘ CARD PADDING & KÍCH CỠ) -->
            <?php if (!empty($scope['canRecordReading'])): ?>
                <div class="card" id="boxFormRecordReading" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
                    <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                            Biểu mẫu ghi nhận chỉ số công tơ Điện & Nước (Tháng <?= $currentMonth ?>/<?= $currentYear ?>)
                        </h3>
                        <span style="font-size: 0.8rem; color: var(--text-muted);">Tự động tính tiền lũy tiến 6 bậc EVN & Nước KTX</span>
                    </div>

                    <form action="<?= url('staff/utilities/save-reading') ?>" method="POST" id="formSaveReading" onsubmit="return validateReadingForm();">
                        <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                        <input type="hidden" name="nam" value="<?= $currentYear ?>">

                        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 16px;">
                            <!-- 1. Chọn phòng -->
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn phòng ghi số <span style="color: #dc2626;">*</span></label>
                                <select name="maPhong" id="selectPhong" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" required onchange="onRoomChanged(this.value)">
                                    <option value="">-- Chọn phòng --</option>
                                    <?php foreach ($roomList as $rm): ?>
                                        <option value="<?= htmlspecialchars($rm['MaPhong']) ?>">
                                            Phòng <?= htmlspecialchars($rm['SoPhong']) ?> (<?= htmlspecialchars($formatTenToa($rm['TenToaNha'], $rm['MaToaNha'])) ?> - <?= (int)$rm['SoSVDangO'] ?> SV)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- 2. Chỉ số Điện Cũ & Mới -->
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Điện Cũ &rarr; Mới (kWh) <span style="color: #dc2626;">*</span></label>
                                <div style="display: flex; gap: 6px;">
                                    <input type="number" name="dienCu" id="inputDienCu" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #f1f5f9;" readonly placeholder="Điện cũ" title="Chỉ số điện kỳ trước">
                                    <input type="number" name="dienMoi" id="inputDienMoi" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" placeholder="Điện mới" required oninput="calculatePreview()">
                                </div>
                                <div id="previewDien" style="font-size: 0.75rem; color: #0369a1; margin-top: 4px; min-height: 16px;"></div>
                            </div>

                            <!-- 3. Chỉ số Nước Cũ & Mới -->
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Nước Cũ &rarr; Mới (m³) <span style="color: #dc2626;">*</span></label>
                                <div style="display: flex; gap: 6px;">
                                    <input type="number" name="nuocCu" id="inputNuocCu" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #f1f5f9;" readonly placeholder="Nước cũ" title="Chỉ số nước kỳ trước">
                                    <input type="number" name="nuocMoi" id="inputNuocMoi" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" placeholder="Nước mới" required oninput="calculatePreview()">
                                </div>
                                <div id="previewNuoc" style="font-size: 0.75rem; color: #0f766e; margin-top: 4px; min-height: 16px;"></div>
                            </div>

                            <!-- 4. Sĩ số SV & Dự toán tổng tiền -->
                            <div>
                                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Sĩ số SV thực tế <span style="color: #dc2626;">*</span></label>
                                <input type="number" name="soSV" id="inputSoSV" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" value="1" min="1" required oninput="calculatePreview()">
                                <div id="previewTongTien" style="font-size: 0.75rem; color: #166534; font-weight: 600; margin-top: 4px; min-height: 16px;"></div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 10px; align-items: center;">
                            <button type="submit" class="btn btn-primary" style="border-radius: 6px; font-weight: 600; padding: 9px 22px; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-floppy-disk"></i> Lưu chỉ số & Kiểm tra bất thường
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- BẢNG THEO DÕI CHỈ SỐ & TIÊU THỤ (ĐỒNG BỘ CHUẨN CARD VÀ TABLE) -->
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>Danh sách Số đo & Tiêu thụ Điện Nước (Tháng <?= $currentMonth ?>/<?= $currentYear ?>)</span>
                        <span class="badge badge-info"><?= count($readingList) ?> phòng</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableReadings">
                        <thead>
                            <tr>
                                <th>Phòng & Tòa</th>
                                <th>Điện Cũ &rarr; Mới</th>
                                <th>Tiêu thụ Điện</th>
                                <th>Nước Cũ &rarr; Mới</th>
                                <th>Tiêu thụ Nước</th>
                                <th>Sĩ số</th>
                                <th>Tiền Điện / Nước</th>
                                <th>Tổng tiền</th>
                                <th>Cảnh báo</th>
                                <th style="text-align: right; min-width: 150px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="readingsTableBody">
                            <?php if (empty($readingList)): ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">⚡</div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có chỉ số điện nước nào được ghi cho kỳ tháng <?= $currentMonth ?>/<?= $currentYear ?>.</div>
                                        <div style="font-size: 0.85rem; margin-top: 4px;">Sử dụng biểu mẫu phía trên hoặc bấm "Nhập file" để nạp dữ liệu.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($readingList as $r): ?>
                                    <?php
                                    $dienKwh = max(0, (int)$r['DienMoi'] - (int)$r['DienCu']);
                                    $nuocM3 = max(0, (int)$r['NuocMoi'] - (int)$r['NuocCu']);
                                    $tongTien = (float)$r['TienDien'] + (float)$r['TienNuoc'];
                                    $isAnomaly = ((int)$r['CanhBaoBatThuong'] === 1);
                                    $hasInvoice = !empty($r['MaHoaDon']);
                                    ?>
                                    <tr class="readings-row" style="<?= $isAnomaly ? 'background: #fffbeb;' : '' ?>">
                                        <td>
                                            <b style="color: var(--primary); font-size: 0.95rem;">P.<?= htmlspecialchars($r['SoPhong']) ?></b>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= htmlspecialchars($formatTenToa($r['TenToaNha'], $r['MaToaNha'])) ?> &bull; Tầng <?= htmlspecialchars($r['Tang'] ?? '1') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.85rem;"><?= number_format($r['DienCu']) ?> &rarr; <b><?= number_format($r['DienMoi']) ?></b></span>
                                        </td>
                                        <td>
                                            <b style="<?= $isAnomaly ? 'color: #b91c1c;' : 'color: #0284c7;' ?>; font-size: 0.9rem;">
                                                <?= number_format($dienKwh) ?> kWh
                                            </b>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.85rem;"><?= number_format($r['NuocCu']) ?> &rarr; <b><?= number_format($r['NuocMoi']) ?></b></span>
                                        </td>
                                        <td>
                                            <b style="<?= $isAnomaly ? 'color: #b91c1c;' : 'color: #0d9488;' ?>; font-size: 0.9rem;">
                                                <?= number_format($nuocM3) ?> m³
                                            </b>
                                        </td>
                                        <td><?= (int)$r['SoSVThucOTaiThang'] ?> SV</td>
                                        <td style="font-size: 0.8rem; line-height: 1.4;">
                                            Điện: <?= number_format($r['TienDien'], 0, ',', '.') ?> đ<br>
                                            Nước: <?= number_format($r['TienNuoc'], 0, ',', '.') ?> đ
                                        </td>
                                        <td>
                                            <b style="color: #15803d; font-size: 0.95rem;">
                                                <?= number_format($tongTien, 0, ',', '.') ?> đ
                                            </b>
                                        </td>
                                        <td>
                                            <?php if ($isAnomaly): ?>
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                    <i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i> Đột biến &gt;3 lần!
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Bình thường
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php if ($hasInvoice): ?>
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;" title="Mã HĐ: <?= htmlspecialchars($r['MaHoaDon']) ?>">
                                                    <i class="fa-solid fa-file-invoice" style="margin-right: 4px;"></i> Đã phát hành HĐ
                                                </span>
                                            <?php elseif (!empty($scope['canPublishInvoice'])): ?>
                                                <form method="POST" action="<?= url('staff/utilities/publish-invoice') ?>" style="display: inline;"
                                                    onsubmit="return confirm('Xác nhận phát hành hóa đơn điện nước cho phòng <?= htmlspecialchars($r['MaPhong']) ?>?');">
                                                    <input type="hidden" name="maPhong" value="<?= htmlspecialchars($r['MaPhong']) ?>">
                                                    <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                                                    <input type="hidden" name="nam" value="<?= $currentYear ?>">
                                                    <button type="submit" class="btn btn-primary btn-sm" style="height: 30px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                                        <i class="fa-solid fa-paper-plane"></i> Phát hành HĐ
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">Chờ quản trị phát hành</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG TAB 1 -->
                <?php if (!empty($readingList)): ?>
                    <div id="readingsPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                            <span id="readingsPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted);">
                                Hiển thị 1 - <?= min(10, count($readingList)) ?> trên tổng số <?= count($readingList) ?> bản ghi
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted);">
                                <span>Hiển thị:</span>
                                <select id="readingsPageSizeSelect" onchange="changeUtilPageSize('readings', this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                                    <option value="10" selected>10 / trang</option>
                                    <option value="25">25 / trang</option>
                                    <option value="50">50 / trang</option>
                                </select>
                            </div>
                        </div>
                        <div id="readingsPaginationNav" style="display: flex; gap: 4px; align-items: center;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: QUẢN LÝ HÓA ĐƠN & GẠCH NỢ MÔ PHỎNG                                 -->
        <!-- ========================================================================= -->
        <div id="tabContentInvoices" style="display: <?= ($activeTab === 'invoices') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span>Hóa đơn Điện Nước & Gạch nợ Đối soát</span>
                        <span class="badge badge-info"><?= count($invoiceList) ?> hóa đơn</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableInvoices">
                        <thead>
                            <tr>
                                <th>Mã Hóa Đơn</th>
                                <th>Phòng / Đại diện</th>
                                <th>Loại hóa đơn</th>
                                <th>Số tiền phải đóng</th>
                                <th>Ngày tạo</th>
                                <th>Hạn thanh toán</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right; min-width: 200px;">Thao tác BQL</th>
                            </tr>
                        </thead>
                        <tbody id="invoicesTableBody">
                            <?php if (empty($invoiceList)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">🧾</div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có hóa đơn điện nước nào trong phạm vi quản lý.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoiceList as $inv): ?>
                                    <?php $isPaid = ($inv['TrangThai'] === 'Đã thanh toán'); ?>
                                    <tr class="invoices-row">
                                        <td>
                                            <b style="font-family: monospace; color: var(--primary); font-size: 0.95rem;">
                                                <?= htmlspecialchars($inv['MaHoaDon']) ?>
                                            </b>
                                        </td>
                                        <td>
                                            <b style="color: var(--text-primary);">Phòng <?= htmlspecialchars($inv['SoPhong'] ?? $inv['MaPhong']) ?></b>
                                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                                <?= htmlspecialchars($formatTenToa($inv['TenToaNha'] ?? '', '')) ?> &bull; Đại diện: <?= htmlspecialchars($inv['TenSinhVien'] ?? 'Trưởng phòng') ?>
                                                <?php if (!empty($inv['MSSVTaiTruong'])): ?>
                                                    (<?= htmlspecialchars($inv['MSSVTaiTruong']) ?>)
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td style="font-size: 0.85rem;"><?= htmlspecialchars($inv['LoaiHoaDon']) ?></td>
                                        <td>
                                            <b style="color: <?= $isPaid ? '#15803d' : '#b91c1c' ?>; font-size: 0.95rem;">
                                                <?= number_format($inv['SoTienPhaiDong'], 0, ',', '.') ?> đ
                                            </b>
                                        </td>
                                        <td style="font-size: 0.85rem;"><?= !empty($inv['NgayTao']) ? date('d/m/Y', strtotime($inv['NgayTao'])) : '---' ?></td>
                                        <td style="font-size: 0.85rem;"><?= !empty($inv['HanThanhToan']) ? date('d/m/Y', strtotime($inv['HanThanhToan'])) : '---' ?></td>
                                        <td>
                                            <?php if ($isPaid): ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                    <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đã thanh toán
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px; white-space: nowrap;">
                                                    <i class="fa-solid fa-clock" style="margin-right: 4px;"></i> Chưa thanh toán
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php if ($isPaid): ?>
                                                <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                                                    <span style="color: #15803d; font-weight: 600;"><i class="fa-solid fa-qrcode" style="margin-right: 4px;"></i> <?= htmlspecialchars($inv['MaGiaoDich'] ?? 'GD-QR') ?></span>
                                                    <?php if (!empty($inv['ThoiGianGiaoDich'])): ?>
                                                        <br><span><?= date('d/m/Y H:i', strtotime($inv['ThoiGianGiaoDich'])) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php elseif (!empty($scope['canConfirmPayment'])): ?>
                                                <form method="POST" action="<?= url('staff/utilities/confirm-payment') ?>" style="display: inline;"
                                                    onsubmit="return confirm('Xác nhận thanh toán mô phỏng cho hóa đơn <?= htmlspecialchars($inv['MaHoaDon']) ?> với số tiền <?= number_format($inv['SoTienPhaiDong'], 0, ',', '.') ?> đ?');">
                                                    <input type="hidden" name="maHoaDon" value="<?= htmlspecialchars($inv['MaHoaDon']) ?>">
                                                    <input type="hidden" name="phuongThuc" value="Mã QR KTX">
                                                    <input type="hidden" name="thang" value="<?= $currentMonth ?>">
                                                    <input type="hidden" name="nam" value="<?= $currentYear ?>">
                                                    <button type="submit" class="btn btn-outline btn-sm" style="height: 30px; border-radius: 6px; color: #166534; border-color: #86efac; background: #f0fdf4; font-weight: 600; font-size: 0.8rem; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                                        <i class="fa-solid fa-bolt"></i> Xác nhận thanh toán
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">Kế toán xác nhận</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG TAB 2 -->
                <?php if (!empty($invoiceList)): ?>
                    <div id="invoicesPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                            <span id="invoicesPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted);">
                                Hiển thị 1 - <?= min(10, count($invoiceList)) ?> trên tổng số <?= count($invoiceList) ?> bản ghi
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted);">
                                <span>Hiển thị:</span>
                                <select id="invoicesPageSizeSelect" onchange="changeUtilPageSize('invoices', this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                                    <option value="10" selected>10 / trang</option>
                                    <option value="25">25 / trang</option>
                                    <option value="50">50 / trang</option>
                                </select>
                            </div>
                        </div>
                        <div id="invoicesPaginationNav" style="display: flex; gap: 4px; align-items: center;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: DANH SÁCH PHÒNG CHƯA GHI CHỈ SỐ KỲ NÀY                             -->
        <!-- ========================================================================= -->
        <div id="tabContentMissing" style="display: <?= ($activeTab === 'missing') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="color: #b91c1c;">Danh sách Phòng Chưa ghi chỉ số (Tháng <?= $currentMonth ?>/<?= $currentYear ?>)</span>
                        <span class="badge badge-danger"><?= count($missingList) ?> phòng</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableMissing">
                        <thead>
                            <tr>
                                <th>Mã Phòng</th>
                                <th>Số Phòng</th>
                                <th>Vị trí Tòa & Tầng</th>
                                <th>Sĩ số SV</th>
                                <th>Điện cũ kỳ trước</th>
                                <th>Nước cũ kỳ trước</th>
                                <th style="text-align: right; min-width: 140px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody id="missingTableBody">
                            <?php if (empty($missingList)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px 16px; color: #15803d;">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">🎉</div>
                                        <div style="font-weight: 700; font-size: 1.05rem;">Tuyệt vời! 100% phòng trong phạm vi đã được ghi nhận chỉ số kỳ này!</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($missingList as $m): ?>
                                    <tr class="missing-row">
                                        <td>
                                            <b style="font-family: monospace; color: var(--primary);"><?= htmlspecialchars($m['MaPhong']) ?></b>
                                        </td>
                                        <td>
                                            <b style="font-size: 0.95rem;">Phòng <?= htmlspecialchars($m['SoPhong']) ?></b>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($formatTenToa($m['TenToaNha'], $m['MaToaNha'])) ?> &bull; Tầng <?= htmlspecialchars($m['Tang'] ?? '1') ?>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #f1f5f9; color: #475569; border: none; font-weight: 600; padding: 2px 8px;">
                                                <?= (int)$m['SoSVDangO'] ?> SV
                                            </span>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.85rem;"><?= number_format($m['dienCu'] ?? 0) ?> kWh</span>
                                        </td>
                                        <td>
                                            <span style="font-family: monospace; font-size: 0.85rem;"><?= number_format($m['nuocCu'] ?? 0) ?> m³</span>
                                        </td>
                                        <td style="text-align: right;">
                                            <?php if (!empty($scope['canRecordReading'])): ?>
                                                <button type="button" class="btn btn-primary btn-sm" onclick="quickFillRoom('<?= htmlspecialchars($m['MaPhong']) ?>')" style="height: 30px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                                                    <i class="fa-solid fa-pen-to-square"></i> Ghi số ngay
                                                </button>
                                            <?php else: ?>
                                                <span style="font-size: 0.8rem; color: var(--text-muted);">Chờ kỹ thuật</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG TAB 3 -->
                <?php if (!empty($missingList)): ?>
                    <div id="missingPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                            <span id="missingPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted);">
                                Hiển thị 1 - <?= min(10, count($missingList)) ?> trên tổng số <?= count($missingList) ?> bản ghi
                            </span>
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: var(--text-muted);">
                                <span>Hiển thị:</span>
                                <select id="missingPageSizeSelect" onchange="changeUtilPageSize('missing', this.value)" style="padding: 4px 8px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                                    <option value="10" selected>10 / trang</option>
                                    <option value="25">25 / trang</option>
                                    <option value="50">50 / trang</option>
                                </select>
                            </div>
                        </div>
                        <div id="missingPaginationNav" style="display: flex; gap: 4px; align-items: center;"></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL: NHẬP CHỈ SỐ ĐIỆN NƯỚC HÀNG LOẠT TỪ FILE CSV                        -->
<!-- ========================================================================= -->
<div id="modalImportUtilities" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 540px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                Nhập chỉ số Điện Nước từ file CSV
            </h3>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">
                &times;
            </button>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
            Tải lên file định dạng <b>.CSV</b> chứa số đo điện nước của các phòng để hệ thống tự động tính tiền lũy tiến 6 bậc và rà quét bất thường.
        </p>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
            <div>
                <div style="font-size: 0.85rem; font-weight: 600; color: #1e40af;">Chưa có file mẫu chuẩn?</div>
                <div style="font-size: 0.78rem; color: #3b82f6;">Tải file mẫu đã điền sẵn danh sách phòng của bạn</div>
            </div>
            <a href="<?= url('staff/utilities/template') ?>" class="btn btn-sm" style="background: #ffffff; border: 1px solid #bfdbfe; color: #1d4ed8; font-weight: 600; border-radius: 6px; text-decoration: none; padding: 6px 12px;">
                <i class="fa-solid fa-download" style="margin-right: 4px;"></i> Tải file mẫu
            </a>
        </div>

        <form action="<?= url('staff/utilities/import') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="thang" value="<?= $currentMonth ?>">
            <input type="hidden" name="nam" value="<?= $currentYear ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn file CSV: <span style="color: #dc2626;">*</span></label>
                <input type="file" name="csv_file" accept=".csv" required class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;">
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">File mã hóa UTF-8, kích thước tối đa 5MB.</div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải lên & Xử lý
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: AUTO-FILL, PREVIEW TÍNH TIỀN, TABS, PHÂN TRANG CLIENT-SIDE     -->
<!-- ========================================================================= -->
<script>
    const roomMeta = <?= json_encode($meta, JSON_UNESCAPED_UNICODE) ?>;

    // 1. Phân trang Client-side cho cả 3 tabs
    const utilPaginationState = {
        readings: {
            page: 1,
            pageSize: 10
        },
        invoices: {
            page: 1,
            pageSize: 10
        },
        missing: {
            page: 1,
            pageSize: 10
        }
    };

    function renderUtilPagination(tabKey) {
        const rowClass = `${tabKey}-row`;
        const rows = document.querySelectorAll(`.${rowClass}`);
        const total = rows.length;
        const state = utilPaginationState[tabKey];
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
                infoEl.innerText = `Hiển thị ${curStart} - ${curEnd} trên tổng số ${total} bản ghi (Trang ${state.page} / ${totalPages})`;
            }
        }

        const navEl = document.getElementById(`${tabKey}PaginationNav`);
        if (navEl) {
            if (totalPages <= 1) {
                navEl.innerHTML = '';
                return;
            }

            let html = '';
            // Nút trang trước
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUtilPage('${tabKey}', ${state.page - 1})" ${state.page === 1 ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''} style="padding: 4px 10px; border-radius: 6px;">&laquo; Trước</button>`;

            // Các số trang
            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= state.page - 1 && p <= state.page + 1)) {
                    const isActive = (p === state.page);
                    const btnStyle = isActive ?
                        'background: var(--primary); color: #fff; border-color: var(--primary);' :
                        'background: #fff; color: var(--text-primary); border-color: #cbd5e1;';
                    html += `<button type="button" class="btn btn-sm" onclick="goToUtilPage('${tabKey}', ${p})" style="padding: 4px 10px; border-radius: 6px; font-weight: 600; ${btnStyle}">${p}</button>`;
                } else if (p === state.page - 2 || p === state.page + 2) {
                    html += `<span style="padding: 4px 6px; color: var(--text-muted);">&hellip;</span>`;
                }
            }

            // Nút trang sau
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUtilPage('${tabKey}', ${state.page + 1})" ${state.page === totalPages ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''} style="padding: 4px 10px; border-radius: 6px;">Sau &raquo;</button>`;

            navEl.innerHTML = html;
        }
    }

    function goToUtilPage(tabKey, targetPage) {
        const rowClass = `${tabKey}-row`;
        const rows = document.querySelectorAll(`.${rowClass}`);
        const totalPages = Math.max(1, Math.ceil(rows.length / utilPaginationState[tabKey].pageSize));

        if (targetPage < 1 || targetPage > totalPages) return;
        utilPaginationState[tabKey].page = targetPage;
        renderUtilPagination(tabKey);
    }

    function changeUtilPageSize(tabKey, newSize) {
        utilPaginationState[tabKey].pageSize = parseInt(newSize, 10) || 10;
        utilPaginationState[tabKey].page = 1;
        renderUtilPagination(tabKey);
    }

    // 2. Tự động điền dữ liệu phòng khi chọn
    function onRoomChanged(maPhong) {
        if (!maPhong || !roomMeta[maPhong]) {
            document.getElementById('inputDienCu').value = '';
            document.getElementById('inputNuocCu').value = '';
            document.getElementById('inputSoSV').value = '1';
            clearPreview();
            return;
        }

        const data = roomMeta[maPhong];
        document.getElementById('inputDienCu').value = data.dienCu || 0;
        document.getElementById('inputNuocCu').value = data.nuocCu || 0;
        document.getElementById('inputSoSV').value = data.soSV || 1;

        calculatePreview();
    }

    function clearPreview() {
        const elDien = document.getElementById('previewDien');
        const elNuoc = document.getElementById('previewNuoc');
        const elTong = document.getElementById('previewTongTien');
        if (elDien) elDien.innerText = '';
        if (elNuoc) elNuoc.innerText = '';
        if (elTong) elTong.innerText = '';
    }

    function calculatePreview() {
        const dienCu = parseInt(document.getElementById('inputDienCu').value) || 0;
        const dienMoi = parseInt(document.getElementById('inputDienMoi').value);
        const nuocCu = parseInt(document.getElementById('inputNuocCu').value) || 0;
        const nuocMoi = parseInt(document.getElementById('inputNuocMoi').value);
        const soSV = parseInt(document.getElementById('inputSoSV').value) || 1;

        if (isNaN(dienMoi) && isNaN(nuocMoi)) {
            clearPreview();
            return;
        }

        let kwh = 0;
        let m3 = 0;

        if (!isNaN(dienMoi)) {
            if (dienMoi < dienCu) {
                document.getElementById('previewDien').innerHTML = '<span style="color: #b91c1c; font-weight: 600;">⚠️ Điện mới < cũ!</span>';
            } else {
                kwh = dienMoi - dienCu;
                document.getElementById('previewDien').innerHTML = `Tiêu thụ: <b>${kwh} kWh</b>`;
            }
        }

        if (!isNaN(nuocMoi)) {
            if (nuocMoi < nuocCu) {
                document.getElementById('previewNuoc').innerHTML = '<span style="color: #b91c1c; font-weight: 600;">⚠️ Nước mới < cũ!</span>';
            } else {
                m3 = nuocMoi - nuocCu;
                document.getElementById('previewNuoc').innerHTML = `Tiêu thụ: <b>${m3} m³</b>`;
            }
        }

        if (kwh >= 0 && m3 >= 0 && (!isNaN(dienMoi) || !isNaN(nuocMoi))) {
            // Ước tính sơ bộ: Nước 12.500 đ/m3 + Điện trung bình ~2.000 đ/kWh
            const estTienNuoc = m3 * 12500;
            let estTienDien = 0;
            let rem = kwh;
            const tiers = [{
                    limit: 50,
                    price: 1806
                },
                {
                    limit: 100,
                    price: 1866
                },
                {
                    limit: 200,
                    price: 2167
                },
                {
                    limit: 300,
                    price: 2729
                },
                {
                    limit: 400,
                    price: 3050
                },
                {
                    limit: 999999,
                    price: 3151
                }
            ];
            let prevLim = 0;
            for (const t of tiers) {
                const cap = t.limit - prevLim;
                if (rem > cap) {
                    estTienDien += cap * t.price;
                    rem -= cap;
                    prevLim = t.limit;
                } else {
                    estTienDien += rem * t.price;
                    break;
                }
            }

            const tong = estTienDien + estTienNuoc;
            const perSV = soSV > 0 ? Math.round(tong / soSV) : 0;
            document.getElementById('previewTongTien').innerHTML = `Dự kiến: <b>${tong.toLocaleString('vi-VN')} đ</b> (~${perSV.toLocaleString('vi-VN')} đ/SV)`;
        }
    }

    function validateReadingForm() {
        const dienCu = parseInt(document.getElementById('inputDienCu').value) || 0;
        const dienMoi = parseInt(document.getElementById('inputDienMoi').value);
        const nuocCu = parseInt(document.getElementById('inputNuocCu').value) || 0;
        const nuocMoi = parseInt(document.getElementById('inputNuocMoi').value);

        if (dienMoi < dienCu) {
            alert('Lỗi: Chỉ số điện mới (' + dienMoi + ') không được nhỏ hơn chỉ số cũ (' + dienCu + ')!');
            return false;
        }

        if (nuocMoi < nuocCu) {
            alert('Lỗi: Chỉ số nước mới (' + nuocMoi + ') không được nhỏ hơn chỉ số cũ (' + nuocCu + ')!');
            return false;
        }

        return true;
    }

    // 3. Chuyển đổi Sub-Tabs & Cập nhật số đếm nút Xuất Excel
    function updateExportButtonCount(tabKey) {
        const btnText = document.getElementById('btnExportText');
        if (!btnText) return;

        let count = 0;
        if (tabKey === 'invoices') {
            count = document.querySelectorAll('.invoices-row').length;
        } else if (tabKey === 'missing') {
            count = document.querySelectorAll('.missing-row').length;
        } else {
            count = document.querySelectorAll('.readings-row').length;
        }
        btnText.innerText = `Xuất Excel (${count})`;
    }

    function switchUtilTab(tabKey) {
        const input = document.getElementById('currentActiveTab');
        if (input) input.value = tabKey;

        document.querySelectorAll('.tab-btn').forEach(b => {
            b.style.color = 'var(--text-secondary)';
            b.style.background = 'transparent';
            b.style.fontWeight = '600';
            b.classList.remove('active');
        });

        const readingsEl = document.getElementById('tabContentReadings');
        const invoicesEl = document.getElementById('tabContentInvoices');
        const missingEl = document.getElementById('tabContentMissing');

        if (readingsEl) readingsEl.style.display = 'none';
        if (invoicesEl) invoicesEl.style.display = 'none';
        if (missingEl) missingEl.style.display = 'none';

        if (tabKey === 'invoices') {
            const btn = document.getElementById('tabBtnInvoices');
            if (btn) {
                btn.style.color = 'var(--primary)';
                btn.style.background = '#eff6ff';
                btn.style.fontWeight = '700';
                btn.classList.add('active');
            }
            if (invoicesEl) invoicesEl.style.display = 'block';
        } else if (tabKey === 'missing') {
            const btn = document.getElementById('tabBtnMissing');
            if (btn) {
                btn.style.color = 'var(--primary)';
                btn.style.background = '#eff6ff';
                btn.style.fontWeight = '700';
                btn.classList.add('active');
            }
            if (missingEl) missingEl.style.display = 'block';
        } else {
            const btn = document.getElementById('tabBtnReadings');
            if (btn) {
                btn.style.color = 'var(--primary)';
                btn.style.background = '#eff6ff';
                btn.style.fontWeight = '700';
                btn.classList.add('active');
            }
            if (readingsEl) readingsEl.style.display = 'block';
        }

        updateExportButtonCount(tabKey);
    }

    // 4. Quick Fill khi bấm "Ghi số ngay" từ Tab 3
    function quickFillRoom(maPhong) {
        switchUtilTab('readings');
        const select = document.getElementById('selectPhong');
        if (select) {
            select.value = maPhong;
            onRoomChanged(maPhong);
        }
        const formBox = document.getElementById('boxFormRecordReading');
        if (formBox) {
            formBox.scrollIntoView({
                behavior: 'smooth'
            });
        }
        const inpDien = document.getElementById('inputDienMoi');
        if (inpDien) inpDien.focus();
    }

    // 5. Cascading Dropdown Handlers
    function onKhuChange(khuVal) {
        const cumSelect = document.getElementById('filterCumSelect');
        const toaSelect = document.getElementById('filterToaSelect');
        if (cumSelect) cumSelect.value = '';
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormUtilities').submit();
    }

    function onCumChange(cumVal) {
        const toaSelect = document.getElementById('filterToaSelect');
        if (toaSelect) toaSelect.value = '';
        document.getElementById('filterFormUtilities').submit();
    }

    // 6. Xuất Excel
    function exportUtilities() {
        const urlParams = new URLSearchParams(window.location.search);
        const input = document.getElementById('currentActiveTab');
        const currentTab = input ? input.value : 'readings';
        urlParams.set('tab', currentTab);
        window.location.href = '<?= url('staff/utilities/export') ?>?' + urlParams.toString();
    }

    // 7. Modals
    function openImportModal() {
        const modal = document.getElementById('modalImportUtilities');
        if (modal) modal.style.display = 'flex';
    }

    function closeImportModal() {
        const modal = document.getElementById('modalImportUtilities');
        if (modal) modal.style.display = 'none';
    }

    // Khởi chạy khi DOM sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        renderUtilPagination('readings');
        renderUtilPagination('invoices');
        renderUtilPagination('missing');

        const input = document.getElementById('currentActiveTab');
        const initialTab = input ? input.value : 'readings';
        updateExportButtonCount(initialTab);
    });

    // ESC & Backdrop close
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportModal();
        }
    });

    window.addEventListener('click', function(e) {
        const importModal = document.getElementById('modalImportUtilities');
        if (e.target === importModal) closeImportModal();
    });
</script>

<?php
$content = ob_get_clean();
$title = !empty($isAllowed)
    ? "Điện Nước & Đối soát Công nợ - KTX ĐHQG-HCM"
    : "Từ chối truy cập - Điện Nước & Công nợ";
require dirname(__DIR__) . '/layouts/main.php';
?>