<?php
ob_start();

$isAllowed = $isAllowed ?? $scope['isAllowed'] ?? false;
$activeTab = $activeTab ?? 'canh-bao';
$namHoc = $namHoc ?? '2026-2027';
$hocKy = $hocKy ?? 1;

// Helper làm sạch tên tòa nhà (loại bỏ lặp "Tòa Tòa")
$formatTenToa = function (?string $tenToa, ?string $maToa): string {
    $toa = $tenToa ?: $maToa ?: '';
    $clean = preg_replace('/^(Tòa\s*nhà\s*|Tòa\s*)+/ui', '', trim($toa));
    return 'Tòa ' . (!empty($clean) ? $clean : $toa);
};
?>

<div class="container">

    <!-- ========================================================================= -->
    <!-- BREADCRUMB & HEADER (ĐỒNG BỘ CHUẨN ĐIỆN NƯỚC & ZONES - CÁC NÚT PHÂN BỐ TỪ PHẢI) -->
    <!-- ========================================================================= -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-muted);">An ninh & Kỷ luật</span>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Giám sát An ninh & Điểm Rèn luyện</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Giám sát An ninh Ra Vào & Đánh giá Rèn luyện</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge" style="background: #fefce8; color: #a16207; border: 1px solid #fef08a; border-radius: 6px; font-weight: 600; padding: 4px 10px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-building-user"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ Quản lý') ?>
                    </span>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ CHUẨN STAFF/MAINTENANCE - ĐẶT PHÍA BÊN PHẢI) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; margin-left: auto;">
                <?php if (!empty($isAllowed)): ?>
                    <?php if (!empty($scope['canRecordEvent'])): ?>
                        <button type="button" class="btn btn-primary" onclick="openRecordModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.825rem; white-space: nowrap; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                            <i class="fa-solid fa-plus"></i> Ghi nhận Vi phạm / Khen thưởng
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($scope['canSimulateScan'])): ?>
                        <button type="button" class="btn btn-outline" onclick="openSimulateScanModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.825rem; color: #2563eb; border-color: #bfdbfe; background: #eff6ff; white-space: nowrap;" title="Giả lập quẹt thẻ ra/vào cổng Barrier">
                            <i class="fa-solid fa-bolt" style="color: #2563eb;"></i> Quét thẻ Barrier
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($scope['canLockPoints'])): ?>
                        <button type="button" class="btn btn-outline" onclick="openLockSubmitModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.825rem; color: #b45309; border-color: #fde68a; background: #fffbeb; white-space: nowrap;" title="Chốt điểm rèn luyện gửi trường ĐH thành viên">
                            <i class="fa-solid fa-lock" style="color: #d97706;"></i> Chốt & Gửi trường
                        </button>
                    <?php endif; ?>

                    <?php if (!empty($scope['canRecordEvent'])): ?>
                        <button type="button" class="btn btn-outline" onclick="openImportModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.825rem; color: #2563eb; border-color: #bfdbfe; background: #eff6ff; white-space: nowrap;" title="Nhập danh sách điểm rèn luyện từ file CSV">
                            <i class="fa-solid fa-file-import" style="color: #2563eb;"></i> Nhập file
                        </button>
                    <?php endif; ?>

                    <button type="button" class="btn btn-outline" onclick="exportDisciplineCurrentTab()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.825rem; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer; white-space: nowrap;" title="Xuất dữ liệu theo tab đang xem ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (<?= ($activeTab === 'canh-bao') ? count($absenceWarnings ?? []) : (($activeTab === 'barrier') ? count($barrierLogs ?? []) : count($summaryList ?? [])) ?>)</span>
                    </button>
                <?php endif; ?>

                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; white-space: nowrap;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGES -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px; color: #16a34a;"></i> <b>Thành công:</b> <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px; color: #dc2626;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px; color: #d97706;"></i> <b>Lưu ý:</b> <?= htmlspecialchars($flashWarning) ?>
        </div>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- TRƯỜNG HỢP 1: TỪ CHỐI TRUY CẬP (RED WARNING CARD CHUẨN ĐỒNG BỘ 100%)       -->
    <!-- ========================================================================= -->
    <?php if (empty($isAllowed)): ?>
        <div class="card" style="border: 2px solid #f87171; background: #fef2f2; border-radius: 12px; padding: 40px 24px; text-align: center; margin-top: 10px; margin-bottom: 30px; box-shadow: 0 4px 12px rgba(220, 38, 38, 0.08);">
            <div style="font-size: 3.5rem; color: #dc2626; margin-bottom: 12px;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 style="color: #991b1b; font-size: 1.45rem; font-weight: 700; margin-bottom: 8px;">
                TRUY CẬP BỊ TỪ CHỐI / ACCESS DENIED
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
        <!-- TRƯỜNG HỢP 2: CÁN BỘ & ĐỘI AN NINH ĐƯỢC PHÉP TRUY CẬP                     -->
        <!-- ========================================================================= -->

        <!-- 4 THẺ KPI THỐNG KÊ NHANH (QUICK STATS CARDS CHUẨN TỶ LỆ ROOMS & UTILITIES - ẢNH 1) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
            <!-- Thẻ 1: Cảnh báo Vắng mặt > 30 ngày -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Cảnh báo Vắng > 30 ngày</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #dc2626; margin: 4px 0;">
                    <?= number_format($stats['absenceWarnings'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">sinh viên</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Không có đơn xin tạm vắng</div>
            </div>

            <!-- Thẻ 2: Lượt quét thẻ Barrier -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Lượt quét Barrier</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #2563eb; margin: 4px 0;">
                    <?= number_format($stats['totalBarrierScans'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">lượt</span>
                </div>
                <div style="font-size: 0.8rem; color: #16a34a;">Dữ liệu cổng vào / ra KTX</div>
            </div>

            <!-- Thẻ 3: Tổng sinh viên theo dõi -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Sinh viên theo dõi</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0d9488; margin: 4px 0;">
                    <?= number_format($stats['totalResident'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">sinh viên</span>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Hợp đồng nội trú còn hiệu lực</div>
            </div>

            <!-- Thẻ 4: Tiến độ Chốt điểm & Gửi trường -->
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="color: var(--text-muted); font-size: 0.8rem; font-weight: 600; text-transform: uppercase;">Tiến độ Gửi trường</span>
                </div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #166534; margin: 4px 0;">
                    <?= number_format($stats['lockedCount'] ?? 0) ?> <span style="font-size: 0.95rem; font-weight: 500;">/ <?= (int)($stats['totalResident'] ?? 0) ?></span>
                </div>
                <div style="display: flex; gap: 4px; flex-wrap: wrap; margin-top: 4px;">
                    <span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem; padding: 1px 5px;">XS: <?= $stats['excellentCount'] ?? 0 ?></span>
                    <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.7rem; padding: 1px 5px;">Tốt: <?= $stats['goodCount'] ?? 0 ?></span>
                    <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.7rem; padding: 1px 5px;">Khá: <?= $stats['fairCount'] ?? 0 ?></span>
                    <?php if (($stats['weakCount'] ?? 0) > 0): ?>
                        <span class="badge" style="background: #fee2e2; color: #991b1b; font-size: 0.7rem; padding: 1px 5px;">Yếu: <?= $stats['weakCount'] ?? 0 ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- KHỐI BỘ LỌC ĐA TẦNG CASCADING (ĐỒNG BỘ BACKGROUND #f8fafc & PADDING CHUẨN ROOMS/ADMIN) -->
        <div class="card" style="margin-bottom: 24px; padding: 14px 16px; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.03); background: #f8fafc;">
            <form method="GET" action="<?= url('staff/discipline') ?>" id="filterFormDiscipline" style="margin: 0;">
                <input type="hidden" name="tab" id="currentActiveTab" value="<?= htmlspecialchars($activeTab ?? 'canh-bao') ?>">

                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <!-- 1. Ô tìm kiếm tức thời -->
                    <div style="position: relative; flex: 1; min-width: 240px; max-width: 320px;">
                        <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                        <input type="text" name="search" id="inputSearch" class="btn-outline"
                            placeholder="Tìm MSSV, tên SV, phòng, cổng..."
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;">
                    </div>

                    <!-- 2. Dropdown Năm học -->
                    <select name="namHoc" class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                        <option value="2026-2027" <?= ($namHoc === '2026-2027') ? 'selected' : '' ?>>Năm 2026-2027</option>
                        <option value="2025-2026" <?= ($namHoc === '2025-2026') ? 'selected' : '' ?>>Năm 2025-2026</option>
                    </select>

                    <!-- 3. Dropdown Học kỳ -->
                    <select name="hocKy" class="btn-outline" style="min-width: 120px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                        <option value="1" <?= ($hocKy === 1) ? 'selected' : '' ?>>Học kỳ 1</option>
                        <option value="2" <?= ($hocKy === 2) ? 'selected' : '' ?>>Học kỳ 2</option>
                        <option value="3" <?= ($hocKy === 3) ? 'selected' : '' ?>>Học kỳ hè</option>
                    </select>

                    <!-- 4. Dropdown Khu (cho Admin / ALL) -->
                    <?php if ($scope['level'] === 'ALL'): ?>
                        <select name="khu" id="filterKhuSelect" class="btn-outline" style="min-width: 130px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onKhuChange(this.value)">
                            <option value="">-- Tất cả Khu --</option>
                            <?php foreach ($scope['availableKhus'] ?? [] as $k): ?>
                                <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= ($selectedKhu === $k['MaKhu']) ? 'selected' : '' ?>>
                                    Khu <?= htmlspecialchars($k['MaKhu']) ?> (<?= htmlspecialchars($k['TenKhu']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 5. Dropdown Cụm (Cascading) -->
                    <?php if (in_array($scope['level'], ['ALL', 'KHU', 'AN_NINH'])): ?>
                        <select name="cum" id="filterCumSelect" class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="onCumChange(this.value)">
                            <option value="">-- Tất cả Cụm --</option>
                            <?php foreach ($scope['availableCums'] ?? [] as $c): ?>
                                <option value="<?= htmlspecialchars($c['MaCum']) ?>" data-khu="<?= htmlspecialchars($c['MaKhu']) ?>" <?= ($selectedCum === $c['MaCum']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['TenCum']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 6. Dropdown Tòa nhà (Cascading, không lặp "Tòa Tòa") -->
                    <?php if (in_array($scope['level'], ['ALL', 'KHU', 'CUM', 'AN_NINH'])): ?>
                        <select name="toa" id="filterToaSelect" class="btn-outline" style="min-width: 145px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                            <option value="">-- Tất cả Tòa nhà --</option>
                            <?php foreach ($scope['availableToas'] ?? [] as $t): ?>
                                <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum']) ?>" data-khu="<?= htmlspecialchars($t['MaKhu']) ?>" <?= ($selectedToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($formatTenToa($t['TenToaNha'], $t['MaToaNha'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>

                    <!-- 7. Dropdown Tầng -->
                    <select name="tang" class="btn-outline" style="min-width: 125px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                        <option value="">-- Tất cả tầng --</option>
                        <?php for ($fl = 1; $fl <= 12; $fl++): ?>
                            <option value="<?= $fl ?>" <?= ($selectedTang == $fl) ? 'selected' : '' ?>>Tầng <?= $fl ?></option>
                        <?php endfor; ?>
                    </select>

                    <!-- 8. Dropdown Xếp loại -->
                    <select name="rating" class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                        <option value="all" <?= ($selectedRating === 'all') ? 'selected' : '' ?>>-- Tất cả Xếp loại --</option>
                        <option value="Xuất sắc" <?= ($selectedRating === 'Xuất sắc') ? 'selected' : '' ?>>Xuất sắc (90-100đ)</option>
                        <option value="Tốt" <?= ($selectedRating === 'Tốt') ? 'selected' : '' ?>>Tốt (80-89đ)</option>
                        <option value="Khá" <?= ($selectedRating === 'Khá') ? 'selected' : '' ?>>Khá (65-79đ)</option>
                        <option value="Trung bình" <?= ($selectedRating === 'Trung bình') ? 'selected' : '' ?>>Trung bình (50-64đ)</option>
                        <option value="Yếu" <?= ($selectedRating === 'Yếu') ? 'selected' : '' ?>>Yếu (<50đ)< /option>
                    </select>

                    <!-- 9. Dropdown Trạng thái gửi trường -->
                    <select name="lockStatus" class="btn-outline" style="min-width: 155px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; height: 38px; background: #fff;" onchange="document.getElementById('filterFormDiscipline').submit()">
                        <option value="all" <?= ($selectedLockStatus === 'all') ? 'selected' : '' ?>>-- Tình trạng gửi trường --</option>
                        <option value="da-gui" <?= ($selectedLockStatus === 'da-gui') ? 'selected' : '' ?>>Đã gửi trường (Đã khóa)</option>
                        <option value="chua-gui" <?= ($selectedLockStatus === 'chua-gui') ? 'selected' : '' ?>>Đang cập nhật (Chưa khóa)</option>
                    </select>

                    <!-- Nút Lọc & Nút Xóa bộ lọc -->
                    <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 8px 14px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>

        <!-- 3 SUB-TABS ĐIỀU HƯỚNG GẮN PILL BADGES (ĐỒNG BỘ CHUẨN APPLICATIONS & UTILITIES) -->
        <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
            <button type="button" class="tab-btn <?= ($activeTab === 'canh-bao') ? 'active' : '' ?>" id="tabBtnCanhBao" onclick="switchDisciplineTab('canh-bao')"
                style="background: <?= ($activeTab === 'canh-bao') ? '#fee2e2' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'canh-bao') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'canh-bao') ? '#dc2626' : 'var(--text-secondary)' ?>; display: inline-flex; align-items: center; gap: 8px;">
                <span>Cảnh báo Vắng mặt > 30 ngày</span>
                <span class="badge" style="background: <?= ($activeTab === 'canh-bao') ? '#dc2626' : '#e2e8f0' ?>; color: <?= ($activeTab === 'canh-bao') ? '#ffffff' : '#64748b' ?>; font-size: 0.75rem; border-radius: 12px; padding: 2px 8px; border: none;">
                    <?= count($absenceWarnings) ?>
                </span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'barrier') ? 'active' : '' ?>" id="tabBtnBarrier" onclick="switchDisciplineTab('barrier')"
                style="background: <?= ($activeTab === 'barrier') ? '#eff6ff' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'barrier') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'barrier') ? 'var(--primary)' : 'var(--text-secondary)' ?>; display: inline-flex; align-items: center; gap: 8px;">
                <span>Nhật ký Quét thẻ Barrier</span>
                <span class="badge" style="background: <?= ($activeTab === 'barrier') ? 'var(--primary)' : '#e2e8f0' ?>; color: <?= ($activeTab === 'barrier') ? '#ffffff' : '#64748b' ?>; font-size: 0.75rem; border-radius: 12px; padding: 2px 8px; border: none;">
                    <?= count($barrierLogs) ?>
                </span>
            </button>

            <button type="button" class="tab-btn <?= ($activeTab === 'ren-luyen') ? 'active' : '' ?>" id="tabBtnRenLuyen" onclick="switchDisciplineTab('ren-luyen')"
                style="background: <?= ($activeTab === 'ren-luyen') ? '#f0fdf4' : 'transparent' ?>; border: none; padding: 10px 18px; font-size: 0.95rem; font-weight: <?= ($activeTab === 'ren-luyen') ? '700' : '600' ?>; cursor: pointer; border-radius: 8px; color: <?= ($activeTab === 'ren-luyen') ? '#16a34a' : 'var(--text-secondary)' ?>; display: inline-flex; align-items: center; gap: 8px;">
                <span>Điểm Rèn luyện & Kỷ luật</span>
                <span class="badge" style="background: <?= ($activeTab === 'ren-luyen') ? '#16a34a' : '#e2e8f0' ?>; color: <?= ($activeTab === 'ren-luyen') ? '#ffffff' : '#64748b' ?>; font-size: 0.75rem; border-radius: 12px; padding: 2px 8px; border: none;">
                    <?= count($summaryList) ?>
                </span>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: CẢNH BÁO VẮNG MẶT > 30 NGÀY                                        -->
        <!-- ========================================================================= -->
        <div id="tabContent_canh-bao" class="tab-content" style="display: <?= ($activeTab === 'canh-bao') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-triangle-exclamation" style="color: #dc2626;"></i>
                        <span style="font-weight: 700; font-size: 1.05rem;">Danh sách Sinh viên Vắng mặt Kéo dài > 30 ngày Không phép</span>
                        <span class="badge badge-danger"><?= count($absenceWarnings) ?> sinh viên</span>
                    </div>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        Tự động rà soát qua cổng Barrier & kiểm tra Đơn tạm vắng
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableCanhBao">
                        <thead>
                            <tr>
                                <th>MSSV & Họ tên</th>
                                <th>Phòng & Tòa</th>
                                <th>Trường Đại học</th>
                                <th>Lần quét thẻ cuối</th>
                                <th>Số ngày vắng</th>
                                <th>Tình trạng phép</th>
                                <th style="text-align: right; width: 195px; min-width: 195px; white-space: nowrap;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCanhBao">
                            <?php if (empty($absenceWarnings)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">✨</div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Không có sinh viên nào vắng mặt > 30 ngày không phép trong phạm vi quản lý.</div>
                                        <div style="font-size: 0.85rem; margin-top: 4px;">Tất cả sinh viên đều duy trì quét thẻ cổng barrier hoặc đã có đơn tạm vắng hợp lệ.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($absenceWarnings as $w): ?>
                                    <tr class="canh-bao-row">
                                        <td>
                                            <b style="font-family: monospace; color: var(--primary); font-size: 0.95rem;">
                                                <?= htmlspecialchars($w['maSinhVien']) ?>
                                            </b>
                                            <div style="font-weight: 600; color: var(--text-primary); margin-top: 2px;">
                                                <?= htmlspecialchars($w['hoVaTen']) ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                                SĐT: <?= htmlspecialchars($w['soDienThoai']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #1e40af; font-size: 0.95rem;">
                                                P.<?= htmlspecialchars($w['maPhong']) ?> (<?= htmlspecialchars($w['maGiuong']) ?>)
                                            </div>
                                            <div style="font-size: 0.825rem; font-weight: 600; color: var(--text-secondary); margin-top: 2px;">
                                                <?= htmlspecialchars($formatTenToa($w['tenToaNha'] ?? $w['maToaNha'], $w['maToaNha'])) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                                Cụm <?= htmlspecialchars($w['maCum']) ?> &bull; Khu <?= htmlspecialchars($w['maKhu']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500; color: var(--text-primary); font-size: 0.85rem;">
                                                <?= htmlspecialchars($w['tenTruong'] ?? 'KTX ĐHQG') ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?= htmlspecialchars($w['ngayQuetCuoi']) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 2px;">
                                                Cổng: <?= htmlspecialchars($w['congCuoi']) ?> (<?= htmlspecialchars($w['huongCuoi']) ?>)
                                            </div>
                                        </td>
                                        <td>
                                            <b style="color: #dc2626; font-size: 1.05rem;">
                                                <?= (int)$w['soNgayVang'] ?> ngày
                                            </b>
                                        </td>
                                        <td>
                                            <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-size: 0.75rem; font-weight: 600; padding: 4px 8px; border-radius: 6px; width: 100px;">Không có đơn</span>
                                        </td>
                                        <!-- CỘT THAO TÁC CÂN ĐỐI 100% INLINE-FLEX KHÔNG RỚT DÒNG (ẢNH 2) -->
                                        <td style="text-align: right; white-space: nowrap; width: 195px; min-width: 195px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                                <button type="button" class="btn btn-outline btn-sm" onclick="openAbsenceActionModal('<?= htmlspecialchars($w['maSinhVien']) ?>', '<?= htmlspecialchars(addslashes($w['hoVaTen'])) ?>', '<?= htmlspecialchars($w['sdtPhuHuynh'] ?? '') ?>', 'lien_he')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Liên hệ sinh viên hoặc phụ huynh">
                                                    <i class="fa-solid fa-phone"></i> Liên hệ
                                                </button>

                                                <?php if (!empty($scope['canHandleAbsence'])): ?>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="openAbsenceActionModal('<?= htmlspecialchars($w['maSinhVien']) ?>', '<?= htmlspecialchars(addslashes($w['hoVaTen'])) ?>', '<?= htmlspecialchars($w['sdtPhuHuynh'] ?? '') ?>', 'lap_bien_ban')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0; background: #dc2626; border-color: #dc2626;" title="Lập biên bản vi phạm kỷ luật (-25đ)">
                                                        <i class="fa-solid fa-file-signature"></i> Biên bản
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG (PAGINATION CONTROLS CHUẨN STAFF/ZONES) -->
                <div id="canhBaoPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px; padding: 12px 16px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="canhBaoPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị <b>1 - <?= min(count($absenceWarnings), 10) ?></b> trên tổng số <b><?= count($absenceWarnings) ?></b> sinh viên
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="canhBaoPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="canhBaoPageSize" class="btn-outline" onchange="changeDisciplinePageSize('canhBao', this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem; height: 32px; background: #fff;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="100">100 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>

                    <div id="canhBaoPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                        <!-- Sinh tự động bằng Javascript chuẩn staff/zones -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: NHẬT KÝ QUÉT THẺ BARRIER CỔNG KTX                                  -->
        <!-- ========================================================================= -->
        <div id="tabContent_barrier" class="tab-content" style="display: <?= ($activeTab === 'barrier') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-shield-halved" style="color: #2563eb;"></i>
                        <span style="font-weight: 700; font-size: 1.05rem;">Nhật ký Quét thẻ Cổng Barrier Thời gian thực</span>
                        <span class="badge badge-info"><?= count($barrierLogs) ?> lượt gần nhất</span>
                    </div>

                    <!-- Lọc nhanh hướng đi -->
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <span style="font-size: 0.82rem; font-weight: 600; color: var(--text-muted);">Hướng:</span>
                        <a href="<?= url('staff/discipline?tab=barrier&huongDi=&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy) ?>" class="badge" style="padding: 4px 8px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= empty($huongDi) ? 'background: #0f172a; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">Tất cả</a>
                        <a href="<?= url('staff/discipline?tab=barrier&huongDi=Vào&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy) ?>" class="badge" style="padding: 4px 8px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= ($huongDi === 'Vào') ? 'background: #16a34a; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">⬇️ Vào KTX</a>
                        <a href="<?= url('staff/discipline?tab=barrier&huongDi=Ra&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy) ?>" class="badge" style="padding: 4px 8px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= ($huongDi === 'Ra') ? 'background: #2563eb; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">⬆️ Ra ngoài</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableBarrier">
                        <thead>
                            <tr>
                                <th>Mã lượt</th>
                                <th>Sinh viên & MSSV</th>
                                <th>Phòng & Tòa</th>
                                <th>Cổng kiểm soát</th>
                                <th>Hướng đi</th>
                                <th>Thời gian quét</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right; width: 195px; min-width: 195px; white-space: nowrap;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBarrier">
                            <?php if (empty($barrierLogs)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">📂</div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Chưa có lượt quét thẻ barrier nào phù hợp với bộ lọc hiện tại.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($barrierLogs as $log): ?>
                                    <tr class="barrier-row">
                                        <td style="font-family: monospace; color: var(--text-muted); font-weight: 600;">
                                            #<?= htmlspecialchars($log['MaLuot']) ?>
                                        </td>
                                        <td>
                                            <div style="font-weight: 600; color: var(--text-primary);">
                                                <?= htmlspecialchars($log['HoVaTen']) ?>
                                            </div>
                                            <div style="font-size: 0.78rem; font-family: monospace; color: var(--primary);">
                                                <?= htmlspecialchars($log['MaSinhVien']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($log['MaPhong'])): ?>
                                                <b style="color: #1e40af;">P.<?= htmlspecialchars($log['MaPhong']) ?></b> &bull; <?= htmlspecialchars($formatTenToa($log['MaToaNha'], $log['MaToaNha'])) ?>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-style: italic;">Chưa xếp phòng</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 500; color: var(--text-primary);">
                                            <?= htmlspecialchars($log['CongKiemSoat']) ?>
                                        </td>
                                        <td>
                                            <?php if ($log['HuongDi'] === 'Vào'): ?>
                                                <span class="badge" style="background: #dcfce7; color: #15803d; border: none; font-weight: 600; padding: 3px 8px; border-radius: 4px; font-size: 0.74rem;">
                                                    ⬇️ Vào KTX
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #dbeafe; color: #1e40af; border: none; font-weight: 600; padding: 3px 8px; border-radius: 4px; font-size: 0.74rem;">
                                                    ⬆️ Ra ngoài
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-family: monospace; color: var(--text-primary);">
                                            <?= date('d/m/Y H:i:s', strtotime($log['ThoiGianQuet'])) ?>
                                        </td>
                                        <td>
                                            <span style="color: #15803d; font-weight: 600; font-size: 0.78rem;">Hợp lệ</span>
                                        </td>
                                        <!-- CỘT THAO TÁC CÂN ĐỐI 100% INLINE-FLEX (ẢNH 2) -->
                                        <td style="text-align: right; white-space: nowrap; width: 195px; min-width: 195px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                                <button type="button" class="btn btn-outline btn-sm" onclick="viewStudentHistory('<?= htmlspecialchars($log['MaSinhVien']) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Xem lịch sử rèn luyện của sinh viên">
                                                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử
                                                </button>

                                                <?php if (!empty($scope['canRecordEvent'])): ?>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="openRecordModalForStudent('<?= htmlspecialchars($log['MaSinhVien']) ?>', '<?= htmlspecialchars(addslashes($log['HoVaTen'])) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Ghi nhận vi phạm hoặc điểm thưởng">
                                                        <i class="fa-solid fa-pen-to-square"></i> Ghi nhận
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG (PAGINATION CONTROLS CHUẨN STAFF/ZONES) -->
                <div id="barrierPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px; padding: 12px 16px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="barrierPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị <b>1 - <?= min(count($barrierLogs), 10) ?></b> trên tổng số <b><?= count($barrierLogs) ?></b> lượt quét
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="barrierPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="barrierPageSize" class="btn-outline" onchange="changeDisciplinePageSize('barrier', this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem; height: 32px; background: #fff;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="100">100 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>

                    <div id="barrierPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                        <!-- Sinh tự động bằng Javascript chuẩn staff/zones -->
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: ĐIỂM RÈN LUYỆN & KỶ LUẬT HỌC KỲ                                    -->
        <!-- ========================================================================= -->
        <div id="tabContent_ren-luyen" class="tab-content" style="display: <?= ($activeTab === 'ren-luyen') ? 'block' : 'none' ?>;">
            <div class="card" style="margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-star" style="color: #eab308;"></i>
                        <span style="font-weight: 700; font-size: 1.05rem;">Bảng Tổng Kết Điểm Rèn Luyện & Kỷ Luật Nội Trú (HK<?= (int)$hocKy ?> / Năm <?= htmlspecialchars($namHoc) ?>)</span>
                        <span class="badge badge-success"><?= count($summaryList) ?> sinh viên</span>
                    </div>

                    <div style="font-size: 0.85rem; color: var(--text-muted);">
                        Khởi điểm 100đ/kỳ &bull; Cập nhật tự động sau mỗi sự kiện vi phạm / khen thưởng
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="tableRenLuyen">
                        <thead>
                            <tr>
                                <th>MSSV & Họ tên</th>
                                <th>Phòng & Tòa</th>
                                <th>Trường Đại học</th>
                                <th>Điểm gốc</th>
                                <th>Điểm +/-</th>
                                <th>Điểm cuối kỳ</th>
                                <th>Xếp loại</th>
                                <th>Tình trạng gửi trường</th>
                                <th style="text-align: right; width: 195px; min-width: 195px; white-space: nowrap;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyRenLuyen">
                            <?php if (empty($summaryList)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 40px 16px; color: var(--text-muted);">
                                        <div style="font-size: 2.2rem; margin-bottom: 6px;">📂</div>
                                        <div style="font-weight: 600; color: var(--text-secondary);">Không tìm thấy dữ liệu sinh viên nào trong phạm vi quản lý.</div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($summaryList as $row): ?>
                                    <?php
                                    $score = (int)($row['TongDiemCuoiKy'] ?? 100);
                                    $rating = $row['XepLoai'] ?? 'Xuất sắc';
                                    $isLocked = ((int)($row['DaGuiVeTruong'] ?? 0) === 1);
                                    $totalChange = (int)($row['TotalChange'] ?? 0);
                                    ?>
                                    <tr class="ren-luyen-row" style="<?= $isLocked ? 'background: #f8fafc;' : '' ?>">
                                        <td>
                                            <b style="font-family: monospace; color: var(--primary); font-size: 0.95rem;">
                                                <?= htmlspecialchars($row['MaSinhVien']) ?>
                                            </b>
                                            <div style="font-weight: 600; color: var(--text-primary); margin-top: 2px;">
                                                <?= htmlspecialchars($row['HoVaTen']) ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                                SĐT: <?= htmlspecialchars($row['SoDienThoai']) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 700; color: #1e40af; font-size: 0.95rem;">
                                                P.<?= htmlspecialchars($row['MaPhong']) ?>
                                            </div>
                                            <div style="font-size: 0.825rem; font-weight: 600; color: var(--text-secondary); margin-top: 2px;">
                                                <?= htmlspecialchars($formatTenToa($row['TenToaNha'] ?? $row['MaToaNha'], $row['MaToaNha'])) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="font-weight: 500; color: var(--text-primary); font-size: 0.85rem;">
                                                <?= htmlspecialchars($row['TenTruong'] ?? $row['MaTruong'] ?? 'KTX ĐHQG') ?>
                                            </div>
                                        </td>
                                        <td style="color: var(--text-muted);">
                                            100 đ
                                        </td>
                                        <td>
                                            <?php if ($totalChange > 0): ?>
                                                <b style="color: #16a34a;">+<?= $totalChange ?> đ</b>
                                            <?php elseif ($totalChange < 0): ?>
                                                <b style="color: #dc2626;"><?= $totalChange ?> đ</b>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">0 đ</span>
                                            <?php endif; ?>
                                            <?php if ((int)($row['TotalEvents'] ?? 0) > 0): ?>
                                                <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                                                    (<?= (int)$row['TotalEvents'] ?> sự kiện)
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <b style="font-size: 1.15rem; color: <?= ($score >= 80) ? '#1e40af' : (($score >= 65) ? '#d97706' : '#dc2626') ?>;">
                                                <?= $score ?> đ
                                            </b>
                                        </td>
                                        <td>
                                            <?php if ($rating === 'Xuất sắc'): ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Xuất sắc</span>
                                            <?php elseif ($rating === 'Tốt'): ?>
                                                <span class="badge" style="background: #e0f2fe; color: #0369a1; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Tốt</span>
                                            <?php elseif ($rating === 'Khá'): ?>
                                                <span class="badge" style="background: #fef3c7; color: #92400e; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Khá</span>
                                            <?php elseif ($rating === 'Trung bình'): ?>
                                                <span class="badge" style="background: #f1f5f9; color: #475569; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Trung bình</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fee2e2; color: #991b1b; border: none; font-weight: 600; padding: 4px 10px; border-radius: 6px;">Yếu (Cảnh báo)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($isLocked): ?>
                                                <span class="badge" style="background: #dcfce7; color: #166534; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                                    <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Đã gửi trường
                                                </span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #fef3c7; color: #92400e; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                                    <i class="fa-solid fa-pen" style="margin-right: 4px;"></i> Đang cập nhật
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <!-- CỘT THAO TÁC CÂN ĐỐI 100% INLINE-FLEX (ẢNH 2) -->
                                        <td style="text-align: right; white-space: nowrap; width: 195px; min-width: 195px;">
                                            <div style="display: inline-flex; align-items: center; justify-content: flex-end; gap: 6px; white-space: nowrap;">
                                                <button type="button" class="btn btn-outline btn-sm" onclick="viewStudentHistory('<?= htmlspecialchars($row['MaSinhVien']) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Xem lịch sử rèn luyện chi tiết">
                                                    <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử
                                                </button>

                                                <?php if (!$isLocked && !empty($scope['canRecordEvent'])): ?>
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="openRecordModalForStudent('<?= htmlspecialchars($row['MaSinhVien']) ?>', '<?= htmlspecialchars(addslashes($row['HoVaTen'])) ?>')" style="border-radius: 6px; font-weight: 600; padding: 5px 11px; font-size: 0.825rem; display: inline-flex; align-items: center; gap: 4px; height: 32px; white-space: nowrap; flex-shrink: 0;" title="Ghi nhận vi phạm hoặc điểm thưởng">
                                                        <i class="fa-solid fa-plus"></i> Ghi nhận
                                                    </button>
                                                <?php else: ?>
                                                    <span style="font-size: 0.78rem; color: var(--text-muted); font-style: italic; display: inline-flex; align-items: center; height: 32px; padding: 0 4px;">
                                                        🔒 Đã khóa
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG (PAGINATION CONTROLS CHUẨN STAFF/ZONES) -->
                <div id="renLuyenPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px; padding: 12px 16px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="renLuyenPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị <b>1 - <?= min(count($summaryList), 10) ?></b> trên tổng số <b><?= count($summaryList) ?></b> sinh viên
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="renLuyenPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="renLuyenPageSize" class="btn-outline" onchange="changeDisciplinePageSize('renLuyen', this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem; height: 32px; background: #fff;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="100">100 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>

                    <div id="renLuyenPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                        <!-- Sinh tự động bằng Javascript chuẩn staff/zones -->
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<!-- ========================================================================= -->
<!-- MODAL 1: GHI NHẬN SỰ KIỆN RÈN LUYỆN / VI PHẠM / KHEN THƯỞNG               -->
<!-- ========================================================================= -->
<div id="recordModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e40af; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-plus-circle"></i> Ghi nhận Sự kiện Rèn luyện & Kỷ luật
            </h3>
            <button type="button" onclick="closeRecordModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/record') ?>" method="POST" style="margin: 0;">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= (int)$hocKy ?>">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Sinh viên nhận sự kiện: *</label>
                <select name="maSinhVien" id="recordSelectSinhVien" required style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                    <option value="">-- Chọn sinh viên --</option>
                    <?php foreach ($residentStudents as $st): ?>
                        <option value="<?= htmlspecialchars($st['MaSinhVien']) ?>">
                            <?= htmlspecialchars($st['HoVaTen']) ?> (<?= htmlspecialchars($st['MaSinhVien']) ?>) - P.<?= htmlspecialchars($st['MaPhong']) ?> - Tòa <?= htmlspecialchars($st['MaToaNha']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Loại ghi nhận: *</label>
                    <select name="loaiGhiNhan" id="selectLoaiGhiNhan" onchange="onLoaiGhiNhanChange()" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                        <option value="Khen thưởng">Khen thưởng (+ Điểm)</option>
                        <option value="Hoạt động">Hoạt động phong trào (+ Điểm)</option>
                        <option value="Vi phạm">Vi phạm nội quy (- Điểm)</option>
                        <option value="Giới nghiêm">Vi phạm giờ giới nghiêm (- Điểm)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Điểm thay đổi: *</label>
                    <input type="number" name="diemThayDoi" id="inputDiemThayDoi" value="10" required
                        style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Nội dung chi tiết / Lý do giải trình: *</label>
                <textarea name="noiDung" id="inputNoiDung" rows="3" required placeholder="Nhập chi tiết hành vi vi phạm hoặc thành tích khen thưởng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeRecordModal()" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-floppy-disk"></i> Lưu sự kiện
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: XỬ LÝ CẢNH BÁO VẮNG MẶT KÉO DÀI                                  -->
<!-- ========================================================================= -->
<div id="absenceActionModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #dc2626; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-triangle-exclamation"></i> Xử lý Sinh viên Vắng mặt > 30 ngày
            </h3>
            <button type="button" onclick="closeAbsenceActionModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/absence-action') ?>" method="POST" style="margin: 0;">
            <input type="hidden" name="maSinhVien" id="absenceMaSinhVien" value="">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= (int)$hocKy ?>">

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                    <span style="color: var(--text-muted);">Sinh viên:</span>
                    <strong style="color: var(--text-primary);"><span id="absenceStudentName">---</span> (<span id="absenceStudentCode">---</span>)</strong>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">SĐT Phụ huynh:</span>
                    <strong style="color: #0284c7;" id="absenceParentPhone">---</strong>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Hình thức xử lý: *</label>
                <select name="hanhDong" id="absenceHanhDong" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                    <option value="lien_he">Đã liên hệ xác minh (Chưa lập biên bản)</option>
                    <option value="lap_bien_ban">Lập biên bản vi phạm kỷ luật (Trừ 25 điểm)</option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Ghi chú cuộc gọi / Nội dung biên bản: *</label>
                <textarea name="ghiChu" id="absenceGhiChu" rows="3" required placeholder="Ghi nhận phản hồi của sinh viên hoặc gia đình..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeAbsenceActionModal()" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; background: #dc2626; border-color: #dc2626;">Xác nhận xử lý</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: XEM LỊCH SỬ ĐIỂM RÈN LUYỆN CHI TIẾT (AJAX)                       -->
<!-- ========================================================================= -->
<div id="historyModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 640px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2); max-height: 85vh; display: flex; flex-direction: column;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-shrink: 0;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #1e40af; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử Rèn luyện Chi tiết
            </h3>
            <button type="button" onclick="closeHistoryModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <div id="historyStudentInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; font-size: 0.85rem; flex-shrink: 0;">
            <!-- Render động bằng JavaScript -->
        </div>

        <div id="historyTimelineContainer" style="overflow-y: auto; flex: 1; padding-right: 4px;">
            <!-- Danh sách sự kiện render bằng AJAX -->
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 16px; flex-shrink: 0;">
            <button type="button" class="btn btn-outline" onclick="closeHistoryModal()" style="border-radius: 6px; padding: 8px 20px; font-weight: 500;">Đóng</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: CHỐT ĐIỂM & GỬI BÁO CÁO VỀ TRƯỜNG ĐH                              -->
<!-- ========================================================================= -->
<div id="lockSubmitModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 520px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #b45309; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-lock"></i> Chốt & Gửi Điểm Rèn luyện về Trường
            </h3>
            <button type="button" onclick="closeLockSubmitModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/lock-submit') ?>" method="POST" style="margin: 0;">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= (int)$hocKy ?>">

            <p style="font-size: 0.9rem; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
                Hệ thống sẽ khóa bảng điểm của các sinh viên thuộc phạm vi phụ trách và đánh dấu <strong>"Đã gửi trường"</strong>. Sau khi chốt, các sự kiện rèn luyện trong kỳ sẽ không thể tự ý sửa đổi.
            </p>

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn Trường Đại học thành viên (để trống nếu gửi tất cả):</label>
                <select name="maTruong" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                    <option value="">-- Toàn bộ các trường thành viên --</option>
                    <?php foreach ($universities as $u): ?>
                        <option value="<?= htmlspecialchars($u['MaTruong']) ?>">
                            <?= htmlspecialchars($u['TenTruong']) ?> (<?= htmlspecialchars($u['MaTruong']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeLockSubmitModal()" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; background: #b45309; border-color: #b45309;">Xác nhận chốt & gửi </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: GIẢ LẬP QUÉT THẺ BARRIER (DEMO / KIỂM SOÁT)                      -->
<!-- ========================================================================= -->
<div id="simulateScanModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 500px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: #0284c7; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bolt"></i> Giả lập Quét thẻ Ra - Vào Barrier
            </h3>
            <button type="button" onclick="closeSimulateScanModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/simulate-scan') ?>" method="POST" style="margin: 0;">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Sinh viên quẹt thẻ: *</label>
                <select name="maSinhVien" required style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                    <option value="">-- Chọn sinh viên --</option>
                    <?php foreach ($residentStudents as $st): ?>
                        <option value="<?= htmlspecialchars($st['MaSinhVien']) ?>">
                            <?= htmlspecialchars($st['HoVaTen']) ?> (<?= htmlspecialchars($st['MaSinhVien']) ?>) - P.<?= htmlspecialchars($st['MaPhong']) ?> - Tòa <?= htmlspecialchars($st['MaToaNha']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Cổng kiểm soát: *</label>
                    <select name="congKiemSoat" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                        <option value="Cổng chính Khu A">Cổng chính Khu A</option>
                        <option value="Cổng phụ Khu A">Cổng phụ Khu A</option>
                        <option value="Cổng chính Khu B">Cổng chính Khu B</option>
                        <option value="Cổng BA Khu B">Cổng BA Khu B</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Hướng đi: *</label>
                    <select name="huongDi" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid var(--border); font-size: 0.85rem; height: 38px;">
                        <option value="Vào">⬇️ Vào KTX</option>
                        <option value="Ra">⬆️ Ra ngoài</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeSimulateScanModal()" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">Hủy bỏ</button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; background: #0284c7; border-color: #0284c7;">
                    <i class="fa-solid fa-bolt"></i> Ghi nhận lượt quét
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 6: NHẬP FILE ĐIỂM RÈN LUYỆN TỪ CSV                                -->
<!-- ========================================================================= -->
<div id="importDisciplineModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 540px; border-radius: 12px; padding: 24px; background: #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-import" style="color: var(--primary);"></i> Nhập điểm Rèn luyện từ file CSV
            </h3>
            <button type="button" onclick="closeImportModal()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: var(--text-muted);">
                &times;
            </button>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-secondary); margin: 0 0 16px; line-height: 1.5;">
            Tải lên file định dạng <b>.CSV</b> chứa danh sách điểm rèn luyện nội trú (vi phạm / khen thưởng) của sinh viên để hệ thống tự động cập nhật bảng điểm rèn luyện.
        </p>

        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 12px 16px; margin-bottom: 20px;">
            <div style="font-size: 0.85rem; font-weight: 600; color: #1e40af; margin-bottom: 4px;">Định dạng file CSV yêu cầu:</div>
            <div style="font-size: 0.78rem; color: #3b82f6; line-height: 1.6;">
                <code style="background: #dbeafe; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem;">MaSinhVien, LoaiGhiNhan, NoiDung, DiemThayDoi, NamHoc, HocKy</code><br>
                • <b>LoaiGhiNhan</b>: <code>Vi phạm</code> hoặc <code>Khen thưởng</code><br>
                • <b>DiemThayDoi</b>: Số nguyên (âm = trừ điểm, dương = cộng điểm)
            </div>
        </div>

        <form action="<?= url('staff/discipline/import') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= (int)$hocKy ?>">

            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Chọn file CSV: <span style="color: #dc2626;">*</span></label>
                <input type="file" name="csv_file" accept=".csv" required class="form-control" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1;">
                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">File mã hóa UTF-8, kích thước tối đa 5MB.</div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportModal()" style="border-radius: 6px; padding: 8px 16px; font-weight: 500;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Tải lên & Xử lý
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT: PHÂN TRANG CHUẨN STAFF/ZONES, TABS & MODALS                  -->
<!-- ========================================================================= -->
<script>
    // 1. Chuyển đổi Sub-tabs & cập nhật số lượng nút Xuất Excel
    function switchDisciplineTab(tabKey) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'transparent';
            btn.style.color = 'var(--text-secondary)';
            btn.style.fontWeight = '600';
        });

        const activeContent = document.getElementById('tabContent_' + tabKey);
        if (activeContent) activeContent.style.display = 'block';

        const activeBtn = document.getElementById('tabBtn' + (tabKey === 'canh-bao' ? 'CanhBao' : (tabKey === 'barrier' ? 'Barrier' : 'RenLuyen')));
        if (activeBtn) {
            activeBtn.classList.add('active');
            if (tabKey === 'canh-bao') {
                activeBtn.style.background = '#fee2e2';
                activeBtn.style.color = '#dc2626';
            } else if (tabKey === 'barrier') {
                activeBtn.style.background = '#eff6ff';
                activeBtn.style.color = 'var(--primary)';
            } else {
                activeBtn.style.background = '#f0fdf4';
                activeBtn.style.color = '#16a34a';
            }
            activeBtn.style.fontWeight = '700';
        }

        const inputTab = document.getElementById('currentActiveTab');
        if (inputTab) inputTab.value = tabKey;

        // Cập nhật text nút Xuất Excel theo tab
        const btnExportText = document.getElementById('btnExportText');
        if (btnExportText) {
            let count = 0;
            if (tabKey === 'canh-bao') {
                count = document.querySelectorAll('.canh-bao-row').length;
            } else if (tabKey === 'barrier') {
                count = document.querySelectorAll('.barrier-row').length;
            } else {
                count = document.querySelectorAll('.ren-luyen-row').length;
            }
            btnExportText.innerText = `Xuất Excel (${count})`;
        }

        // Render lại phân trang cho tab vừa mở
        const mapKey = {
            'canh-bao': 'canhBao',
            'barrier': 'barrier',
            'ren-luyen': 'renLuyen'
        };
        if (mapKey[tabKey]) {
            renderDisciplinePagination(mapKey[tabKey]);
        }

        const url = new URL(window.location);
        url.searchParams.set('tab', tabKey);
        window.history.replaceState({}, '', url);
    }

    // 2. Xuất Excel dữ liệu của tab hiện tại
    function exportDisciplineCurrentTab() {
        const inputTab = document.getElementById('currentActiveTab');
        const tab = inputTab ? inputTab.value : 'canh-bao';
        const namHoc = '<?= htmlspecialchars($namHoc) ?>';
        const hocKy = '<?= (int)$hocKy ?>';
        const search = encodeURIComponent(document.getElementById('inputSearch') ? document.getElementById('inputSearch').value : '');

        let url = '<?= url('staff/discipline/export') ?>?tab=' + encodeURIComponent(tab) + '&namHoc=' + encodeURIComponent(namHoc) + '&hocKy=' + encodeURIComponent(hocKy);
        if (search) url += '&search=' + search;

        window.location.href = url;
    }

    // 3. Cascading dropdowns: Khu -> Cụm -> Tòa
    function onKhuChange(khu) {
        const cumSelect = document.getElementById('filterCumSelect');
        const toaSelect = document.getElementById('filterToaSelect');
        if (cumSelect) {
            Array.from(cumSelect.options).forEach(opt => {
                if (!opt.value) return;
                opt.style.display = (!khu || opt.dataset.khu === khu) ? '' : 'none';
            });
            cumSelect.value = '';
        }
        if (toaSelect) {
            Array.from(toaSelect.options).forEach(opt => {
                if (!opt.value) return;
                opt.style.display = (!khu || opt.dataset.khu === khu) ? '' : 'none';
            });
            toaSelect.value = '';
        }
        document.getElementById('filterFormDiscipline').submit();
    }

    function onCumChange(cum) {
        const toaSelect = document.getElementById('filterToaSelect');
        if (toaSelect) {
            Array.from(toaSelect.options).forEach(opt => {
                if (!opt.value) return;
                opt.style.display = (!cum || opt.dataset.cum === cum) ? '' : 'none';
            });
            toaSelect.value = '';
        }
        document.getElementById('filterFormDiscipline').submit();
    }

    // 4. PHÂN TRANG CHUẨN STAFF/ZONES (PAGINATION CONTROLS ĐỒNG BỘ 100%)
    const disciplinePagination = {
        canhBao: {
            currentPage: 1,
            pageSize: 10,
            rowClass: 'canh-bao-row',
            barId: 'canhBaoPaginationBar',
            infoId: 'canhBaoPaginationInfo',
            controlsId: 'canhBaoPaginationControls',
            unit: 'sinh viên'
        },
        barrier: {
            currentPage: 1,
            pageSize: 10,
            rowClass: 'barrier-row',
            barId: 'barrierPaginationBar',
            infoId: 'barrierPaginationInfo',
            controlsId: 'barrierPaginationControls',
            unit: 'lượt quét'
        },
        renLuyen: {
            currentPage: 1,
            pageSize: 10,
            rowClass: 'ren-luyen-row',
            barId: 'renLuyenPaginationBar',
            infoId: 'renLuyenPaginationInfo',
            controlsId: 'renLuyenPaginationControls',
            unit: 'sinh viên'
        }
    };

    function renderDisciplinePagination(key) {
        const config = disciplinePagination[key];
        if (!config) return;

        const allRows = Array.from(document.querySelectorAll('.' + config.rowClass));
        const total = allRows.length;
        const totalPages = config.pageSize > 0 ? Math.ceil(total / config.pageSize) : 1;

        if (config.currentPage > totalPages) config.currentPage = totalPages;
        if (config.currentPage < 1) config.currentPage = 1;

        const startIdx = config.pageSize > 0 ? (config.currentPage - 1) * config.pageSize : 0;
        const endIdx = config.pageSize > 0 ? startIdx + config.pageSize : total;

        allRows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        // Cập nhật thông tin phân trang góc trái
        const infoEl = document.getElementById(config.infoId);
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = `Không có ${config.unit} nào phù hợp`;
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> ${config.unit} (Trang <b>${config.currentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        // Tạo các nút phân trang góc phải (chuẩn staff/zones)
        const controlsEl = document.getElementById(config.controlsId);
        if (!controlsEl) return;
        controlsEl.innerHTML = '';

        if (totalPages <= 1) return;

        // Nút Đầu (First Page)
        const btnFirst = document.createElement('button');
        btnFirst.type = 'button';
        btnFirst.className = 'btn btn-outline btn-sm';
        btnFirst.innerHTML = '<i class="fa-solid fa-angles-left"></i>';
        btnFirst.disabled = (config.currentPage === 1);
        btnFirst.onclick = () => goToDisciplinePage(key, 1);
        btnFirst.style.cssText = 'border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;';
        btnFirst.title = 'Trang đầu tiên';
        controlsEl.appendChild(btnFirst);

        // Nút Trước (Prev Page)
        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'btn btn-outline btn-sm';
        btnPrev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
        btnPrev.disabled = (config.currentPage === 1);
        btnPrev.onclick = () => goToDisciplinePage(key, config.currentPage - 1);
        btnPrev.style.cssText = 'border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;';
        btnPrev.title = 'Trang trước';
        controlsEl.appendChild(btnPrev);

        // Các nút số trang có dấu ...
        let startPage = Math.max(1, config.currentPage - 2);
        let endPage = Math.min(totalPages, config.currentPage + 2);

        if (startPage > 1) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.cssText = 'padding: 0 4px; color: var(--text-muted);';
            controlsEl.appendChild(dot);
        }

        for (let p = startPage; p <= endPage; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === config.currentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.cssText = 'border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;' + (p === config.currentPage ? ' font-weight: 700;' : '');
            btnP.onclick = () => goToDisciplinePage(key, p);
            controlsEl.appendChild(btnP);
        }

        if (endPage < totalPages) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.cssText = 'padding: 0 4px; color: var(--text-muted);';
            controlsEl.appendChild(dot);
        }

        // Nút Tiếp (Next Page)
        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'btn btn-outline btn-sm';
        btnNext.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
        btnNext.disabled = (config.currentPage === totalPages);
        btnNext.onclick = () => goToDisciplinePage(key, config.currentPage + 1);
        btnNext.style.cssText = 'border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;';
        btnNext.title = 'Trang kế tiếp';
        controlsEl.appendChild(btnNext);

        // Nút Cuối (Last Page)
        const btnLast = document.createElement('button');
        btnLast.type = 'button';
        btnLast.className = 'btn btn-outline btn-sm';
        btnLast.innerHTML = '<i class="fa-solid fa-angles-right"></i>';
        btnLast.disabled = (config.currentPage === totalPages);
        btnLast.onclick = () => goToDisciplinePage(key, totalPages);
        btnLast.style.cssText = 'border-radius: 6px; padding: 4px 10px; font-size: 0.8rem;';
        btnLast.title = 'Trang cuối cùng';
        controlsEl.appendChild(btnLast);
    }

    function goToDisciplinePage(key, page) {
        if (disciplinePagination[key]) {
            disciplinePagination[key].currentPage = page;
            renderDisciplinePagination(key);
        }
    }

    function changeDisciplinePageSize(key, size) {
        if (disciplinePagination[key]) {
            disciplinePagination[key].pageSize = parseInt(size, 10);
            disciplinePagination[key].currentPage = 1;
            renderDisciplinePagination(key);
        }
    }

    // Khởi chạy phân trang khi tải trang xong
    document.addEventListener('DOMContentLoaded', function() {
        renderDisciplinePagination('canhBao');
        renderDisciplinePagination('barrier');
        renderDisciplinePagination('renLuyen');
    });

    // 5. Các hàm đóng mở Modal
    function openRecordModal() {
        document.getElementById('recordSelectSinhVien').value = '';
        document.getElementById('selectLoaiGhiNhan').value = 'Khen thưởng';
        document.getElementById('inputDiemThayDoi').value = '10';
        document.getElementById('inputNoiDung').value = '';
        document.getElementById('recordModal').style.display = 'flex';
    }

    function openRecordModalForStudent(maSv, hoTen) {
        document.getElementById('recordSelectSinhVien').value = maSv;
        document.getElementById('selectLoaiGhiNhan').value = 'Vi phạm';
        document.getElementById('inputDiemThayDoi').value = '-10';
        document.getElementById('inputNoiDung').value = '';
        document.getElementById('recordModal').style.display = 'flex';
    }

    function closeRecordModal() {
        document.getElementById('recordModal').style.display = 'none';
    }

    function onLoaiGhiNhanChange() {
        const loai = document.getElementById('selectLoaiGhiNhan').value;
        const diem = document.getElementById('inputDiemThayDoi');
        if (loai === 'Vi phạm' || loai === 'Giới nghiêm') {
            if (parseInt(diem.value) > 0) diem.value = '-10';
        } else {
            if (parseInt(diem.value) < 0) diem.value = '10';
        }
    }

    function openAbsenceActionModal(maSv, hoTen, sdtPhuHuynh, hanhDong) {
        document.getElementById('absenceMaSinhVien').value = maSv;
        document.getElementById('absenceStudentName').textContent = hoTen;
        document.getElementById('absenceStudentCode').textContent = maSv;
        document.getElementById('absenceParentPhone').textContent = sdtPhuHuynh || 'Chưa cập nhật';
        document.getElementById('absenceHanhDong').value = hanhDong || 'lien_he';
        document.getElementById('absenceActionModal').style.display = 'flex';
    }

    function closeAbsenceActionModal() {
        document.getElementById('absenceActionModal').style.display = 'none';
    }

    function openSimulateScanModal() {
        document.getElementById('simulateScanModal').style.display = 'flex';
    }

    function closeSimulateScanModal() {
        document.getElementById('simulateScanModal').style.display = 'none';
    }

    function openLockSubmitModal() {
        document.getElementById('lockSubmitModal').style.display = 'flex';
    }

    function closeLockSubmitModal() {
        document.getElementById('lockSubmitModal').style.display = 'none';
    }

    function openImportModal() {
        document.getElementById('importDisciplineModal').style.display = 'flex';
    }

    function closeImportModal() {
        document.getElementById('importDisciplineModal').style.display = 'none';
    }

    // 6. AJAX Xem chi tiết lịch sử rèn luyện
    function viewStudentHistory(maSv) {
        const infoBox = document.getElementById('historyStudentInfo');
        const container = document.getElementById('historyTimelineContainer');
        infoBox.innerHTML = '<em>Đang tải thông tin sinh viên...</em>';
        container.innerHTML = '<div style="text-align: center; padding: 20px; color: #64748b;"><i class="fa-solid fa-spinner fa-spin"></i> Đang truy vấn lịch sử rèn luyện...</div>';

        document.getElementById('historyModal').style.display = 'flex';

        const url = '<?= url('staff/discipline/history') ?>?sv=' + encodeURIComponent(maSv) +
            '&nam=' + encodeURIComponent('<?= $namHoc ?>') + '&hk=<?= $hocKy ?>';

        fetch(url)
            .then(response => response.json())
            .then(res => {
                if (!res.success) {
                    container.innerHTML = '<div style="color: #dc2626; padding: 10px;">' + res.message + '</div>';
                    return;
                }

                const data = res.data;
                const stu = data.student || {};

                infoBox.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">${stu.HoVaTen || maSv} (${stu.MaSinhVien})</div>
                        <div style="font-size: 0.78rem; color: #64748b; margin-top: 2px;">Trường: <strong>${stu.TenTruong || stu.MaTruong || 'KTX'}</strong> &bull; Phòng: <b>${stu.MaPhong || '---'}</b> (Tòa ${stu.MaToaNha || '---'})</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 1.15rem; font-weight: 800; color: ${data.finalScore >= 80 ? '#1e40af' : (data.finalScore >= 65 ? '#d97706' : '#dc2626')};">
                            ${data.finalScore}/100 đ
                        </div>
                        <span class="badge ${data.finalScore >= 90 ? 'badge-success' : (data.finalScore >= 80 ? 'badge-info' : 'badge-warning')}" style="font-size: 0.75rem;">
                            ${data.rating}
                        </span>
                        ${data.isLocked ? '<span class="badge" style="background: #dcfce7; color: #166534; font-size: 0.7rem; margin-left: 4px;">🔒 Đã khóa</span>' : ''}
                    </div>
                </div>
            `;

                if (!data.events || data.events.length === 0) {
                    container.innerHTML = `
                    <div style="text-align: center; padding: 30px; background: #f8fafc; border-radius: 8px; color: #64748b;">
                        <div style="font-size: 2rem; margin-bottom: 6px;">✨</div>
                        <strong>Sinh viên chưa có sự kiện cộng hoặc trừ điểm nào trong học kỳ này.</strong>
                        <div style="font-size: 0.78rem; margin-top: 4px;">Điểm rèn luyện khởi điểm mặc định: 100/100 (Xuất sắc)</div>
                    </div>
                `;
                    return;
                }

                let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
                data.events.forEach((ev, idx) => {
                    const isPositive = (parseInt(ev.DiemThayDoi) > 0);
                    const isZero = (parseInt(ev.DiemThayDoi) === 0);
                    const color = isPositive ? '#16a34a' : (isZero ? '#64748b' : '#dc2626');
                    const sign = isPositive ? '+' : '';

                    html += `
                    <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; background: #fff; border-left: 4px solid ${color};">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                            <span class="badge" style="background: ${isPositive ? '#dcfce7' : '#fee2e2'}; color: ${color}; font-size: 0.74rem; font-weight: 700; border: none;">
                                ${ev.LoaiGhiNhan}
                            </span>
                            <span style="font-weight: 800; font-size: 0.95rem; color: ${color};">
                                ${sign}${ev.DiemThayDoi} điểm
                            </span>
                        </div>
                        <div style="font-size: 0.85rem; color: #1e293b; line-height: 1.45;">
                            ${ev.NoiDung}
                        </div>
                    </div>
                `;
                });
                html += '</div>';

                container.innerHTML = html;
            })
            .catch(err => {
                container.innerHTML = '<div style="color: #dc2626; padding: 10px;">Lỗi kết nối khi tải lịch sử: ' + err + '</div>';
            });
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none';
    }

    // ESC & Backdrop close cho tất cả modals
    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportModal();
        }
    });
    window.addEventListener('click', function(e) {
        const importModal = document.getElementById('importDisciplineModal');
        if (e.target === importModal) closeImportModal();
    });
</script>

<?php
$content = ob_get_clean();
$title = "Giám sát An ninh & Điểm Rèn luyện - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>