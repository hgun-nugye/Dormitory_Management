<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Hạ tầng KTX</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Quản lý Hạ tầng KTX</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ quản trị') ?>
                    </span>
                </p>
            </div>
            <!-- CÁC NÚT THAO TÁC HEADER (GIỐNG PHÒNG VÀ USERS: IMPORT, EXPORT, TEMPLATE) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline" onclick="openImportZoneModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách phòng từ file CSV">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px;"></i> Nhập file
                </button>
                <button type="button" class="btn btn-outline" onclick="exportZoneData()" " style=" border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách phòng theo kết quả lọc ra Excel/CSV">
                    <i class="fa-solid fa-file-excel" style="margin-right: 6px; color: #16a34a;"></i> <span id="btnExportText">Xuất Excel</span>
                </button>
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

    <!-- TABS ĐIỀU HƯỚNG SUB-TABS HẠ TẦNG -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" id="tabBtnPhong" onclick="switchZoneTab('phong')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 700; cursor: pointer; border-radius: 8px; color: var(--primary); background: #eff6ff;">
            <i class="fa-solid fa-door-open" style="margin-right: 6px;"></i> 1. Danh mục Phòng ở (<span id="tabCountPhong"><?= count($phongList ?? []) ?></span>)
            <?php if (!empty($scope['canCreatePhong']) && !empty($scope['canUpdatePhong'])): ?>
                <span class="badge badge-success" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Toàn quyền</span>
            <?php elseif (!empty($scope['canCreatePhong']) || !empty($scope['canDeletePhong'])): ?>
                <span class="badge badge-primary" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Thêm & Xóa (CD)</span>
            <?php elseif (!empty($scope['canUpdatePhong'])): ?>
                <span class="badge badge-warning" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Cập nhật (RU)</span>
            <?php else: ?>
                <span class="badge" style="font-size:0.65rem; margin-left:6px; padding:2px 6px; background:#e2e8f0; color:#64748b;">Xem</span>
            <?php endif; ?>
        </button>

        <button type="button" class="tab-btn" id="tabBtnToa" onclick="switchZoneTab('toa')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-building" style="margin-right: 6px;"></i> 2. Danh mục Tòa nhà (<span id="tabCountToa"><?= count($toaList ?? []) ?></span>)
            <?php if (!empty($scope['canCreateToa']) && !empty($scope['canUpdateToa'])): ?>
                <span class="badge badge-success" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Toàn quyền</span>
            <?php elseif (!empty($scope['canCreateToa']) || !empty($scope['canDeleteToa'])): ?>
                <span class="badge badge-primary" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Thêm & Xóa (CD)</span>
            <?php elseif (!empty($scope['canUpdateToa'])): ?>
                <span class="badge badge-warning" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Cập nhật (RU)</span>
            <?php else: ?>
                <span class="badge" style="font-size:0.65rem; margin-left:6px; padding:2px 6px; background:#e2e8f0; color:#64748b;">Xem</span>
            <?php endif; ?>
        </button>

        <button type="button" class="tab-btn" id="tabBtnCum" onclick="switchZoneTab('cum')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-sitemap" style="margin-right: 6px;"></i> 3. Danh mục Cụm nhà (<span id="tabCountCum"><?= count($cumList ?? []) ?></span>)
            <?php if (!empty($scope['canCreateCum']) && !empty($scope['canUpdateCum'])): ?>
                <span class="badge badge-success" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Toàn quyền</span>
            <?php elseif (!empty($scope['canCreateCum']) || !empty($scope['canDeleteCum'])): ?>
                <span class="badge badge-primary" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Thêm & Xóa (CD)</span>
            <?php elseif (!empty($scope['canUpdateCum'])): ?>
                <span class="badge badge-warning" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Cập nhật (RU)</span>
            <?php else: ?>
                <span class="badge" style="font-size:0.65rem; margin-left:6px; padding:2px 6px; background:#e2e8f0; color:#64748b;">Xem</span>
            <?php endif; ?>
        </button>

        <button type="button" class="tab-btn" id="tabBtnKhu" onclick="switchZoneTab('khu')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-layer-group" style="margin-right: 6px;"></i> 4. Danh mục Khu KTX (<span id="tabCountKhu"><?= count($khuList ?? []) ?></span>)
            <?php if (!empty($scope['canCreateKhu']) && !empty($scope['canUpdateKhu'])): ?>
                <span class="badge badge-success" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Toàn quyền</span>
            <?php elseif (!empty($scope['canCreateKhu']) || !empty($scope['canDeleteKhu'])): ?>
                <span class="badge badge-primary" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Thêm & Xóa (CD)</span>
            <?php elseif (!empty($scope['canUpdateKhu'])): ?>
                <span class="badge badge-warning" style="font-size:0.65rem; margin-left:6px; padding:2px 6px;">Cập nhật (RU)</span>
            <?php else: ?>
                <span class="badge" style="font-size:0.65rem; margin-left:6px; padding:2px 6px; background:#e2e8f0; color:#64748b;">Xem</span>
            <?php endif; ?>
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: DANH MỤC PHÒNG Ở & FORM THÊM / SỬA PHÒNG -->
    <!-- ===================================================================== -->
    <div id="tabContentPhong" style="display: block;">

        <!-- FORM THÊM / SỬA PHÒNG Ở (ẨN MẶC ĐỊNH, HIỂN THỊ KHI BẤM THÊM / SỬA) -->
        <div class="card" id="cardFormRoom" style="display: none; margin-bottom: 24px; border: 2px solid #bfdbfe; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <div id="titleFormRoom" style="font-weight: 700; color: #1e3a8a; font-size: 1.05rem;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                    <span id="titleFormRoomText">Thêm mới Phòng ở & Khởi tạo giường tự động</span>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="hideRoomForm()" style="border-radius: 6px; color: var(--text-muted); cursor: pointer;" title="Đóng biểu mẫu">
                    <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
                </button>
            </div>

            <form action="<?= url('staff/zones/save-room') ?>" method="POST" id="formRoom">
                <?= csrf_field() ?>
                <input type="hidden" name="isEdit" id="roomIsEdit" value="0">

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            1. Cụm nhà trực thuộc <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomFormCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" onchange="onRoomFormCumChange()">
                            <option value="">-- Chọn Cụm nhà --</option>
                            <?php foreach ($cumList as $c): ?>
                                <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']) ? 'selected' : '' ?>>
                                    Cụm <?= htmlspecialchars($c['TenCum']) ?> (<?= htmlspecialchars($c['MaCum']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            2. Tòa nhà lưu trú <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomFormToa" name="maToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required onchange="autoSuggestRoomCode()">
                            <option value="">-- Chọn Tòa nhà --</option>
                            <?php foreach ($toaList as $t): ?>
                                <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum']) ?>">
                                    Tòa <?= htmlspecialchars($t['TenToaNha']) ?> (<?= htmlspecialchars($t['TenCum']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            3. Số phòng <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="roomFormSoPhong" name="soPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 101, 102, 203..." required oninput="autoSuggestRoomCode()">
                    </div>
                </div>

                <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã phòng định danh <span style="color:#dc2626;">*</span>
                            <span id="noticeRoomEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="roomFormMaPhong" name="maPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Tự sinh: Tòa.SốPhòng (VD: A01.101)" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tầng lầu <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="number" id="roomFormTang" name="tang" min="1" value="1" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Loại phòng & Sức chứa <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomFormLoaiPhong" name="maLoaiPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn Loại phòng --</option>
                            <?php foreach ($loaiPhongList as $lp): ?>
                                <option value="<?= htmlspecialchars($lp['MaLoaiPhong']) ?>">
                                    <?= htmlspecialchars($lp['TenLoaiPhong'] ?? $lp['MaLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tình trạng phòng <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomFormTrangThai" name="trangThaiPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Còn chỗ">Còn chỗ</option>
                            <option value="Đã đầy">Đã đầy</option>
                            <option value="Bảo trì">Bảo trì</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="btnSubmitRoom" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu phòng & Sinh giường
                        </button>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormRoom()">
                            <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px; color: #dc2626; border-color: #fecaca;" onclick="hideRoomForm()">
                            <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH PHÒNG Ở: CÁC THANH, FILTER NẰM TRONG DIV CARD VỚI TABLE VÀ PHÂN TRANG -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>Danh sách Phòng ở & Sơ đồ giường</span>
                    <span class="badge badge-info" id="roomCountBadge"><?= count($phongList ?? []) ?> phòng</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if (!empty($scope['canCreatePhong'])): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showCreateRoomForm()" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                            <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm phòng mới
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC ĐA TIÊU CHÍ NẰM TRONG DIV CARD VỚI TABLE (GIỐNG PHÒNG) -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <!-- 1. TÌM KIẾM TỪ KHÓA -->
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 380px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchRoomInput" class="btn-outline"
                        placeholder="Tìm theo mã phòng, số phòng, tên tòa, cụm..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterRooms()">
                </div>

                <!-- 2. LỌC CỤM NHÀ -->
                <select id="filterSelectCum" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="onCascadeCumChange()">
                    <option value="">-- Tất cả Cụm --</option>
                    <?php foreach ($cumList as $c): ?>
                        <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']) ? 'selected' : '' ?>>
                            Cụm <?= htmlspecialchars($c['TenCum']) ?> (<?= htmlspecialchars($c['MaCum']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- 3. LỌC TÒA NHÀ (CASCADE THEO CỤM) -->
                <select id="filterSelectToa" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="onCascadeToaChange()">
                    <option value="">-- Tất cả Tòa --</option>
                    <?php foreach ($toaList as $t): ?>
                        <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum']) ?>">
                            <?= htmlspecialchars($t['TenToaNha']) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- 4. LỌC TẦNG -->
                <select id="filterRoomFloor" class="btn-outline"
                    style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả tầng --</option>
                    <?php for ($f = 1; $f <= 12; $f++): ?>
                        <option value="<?= $f ?>">Tầng <?= $f ?></option>
                    <?php endfor; ?>
                </select>

                <!-- 5. LỌC LOẠI PHÒNG -->
                <select id="filterRoomType" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả loại phòng --</option>
                    <?php foreach ($loaiPhongList as $lp): ?>
                        <option value="<?= htmlspecialchars($lp['MaLoaiPhong'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($lp['TenLoaiPhong'] ?? $lp['MaLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- 6. LỌC TRẠNG THÁI -->
                <select id="filterRoomStatus" class="btn-outline"
                    style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Còn chỗ">Còn chỗ</option>
                    <option value="Đã đầy">Đã đầy</option>
                    <option value="Bảo trì">Bảo trì</option>
                </select>

                <!-- 7. NÚT XÓA BỘ LỌC -->
                <div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="clearRoomFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                        <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>

            <?php if (!empty($phongList)): ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableStaffRooms">
                        <thead>
                            <tr>
                                <th>Mã phòng</th>
                                <th>Cụm & Tòa nhà</th>
                                <th>Số phòng & Tầng</th>
                                <th>Loại phòng</th>
                                <th>Sĩ số</th>
                                <th>Sơ đồ giường</th>
                                <th>Tình trạng</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="roomTableBody">
                            <?php foreach ($phongList as $p): ?>
                                <?php
                                $maxCap = (int)($p['SoGiuongToiDa'] ?? 4);
                                $occupied = (int)($p['SoSVDangO'] ?? 0);
                                $beds = $giuongByPhong[$p['MaPhong']] ?? [];

                                // Kiểm tra số giường đã có người và tổng số giường
                                $totalBeds = count($beds);
                                $occupiedBeds = 0;

                                foreach ($beds as $bed) {
                                    if (
                                        !empty($bed['MaSinhVien']) ||
                                        (($bed['TrangThaiGiuong'] ?? '') === 'Đã có người ở')
                                    ) {
                                        $occupiedBeds++;
                                    }
                                }

                                // Phòng FULL khi tất cả giường hiện có đều có người
                                $isRoomFull = ($totalBeds > 0 && $occupiedBeds === $totalBeds);


                                // Đếm số giường theo trạng thái
                                $emptyBedsCount = 0;
                                $maintenanceBedsCount = 0;
                                foreach ($beds as $bg) {
                                    $stG = mb_strtolower(trim($bg['TrangThaiGiuong'] ?? ''));
                                    if (str_contains($stG, 'trống') || str_contains($stG, 'trong')) {
                                        $emptyBedsCount++;
                                    } elseif (str_contains($stG, 'trì') || str_contains($stG, 'tri') || str_contains($stG, 'khóa') || str_contains($stG, 'khoa')) {
                                        $maintenanceBedsCount++;
                                    }
                                }

                                // Số chỗ còn trống có thể tiếp nhận
                                $uncreatedBeds = max(0, $maxCap - count($beds));
                                $vacant = $emptyBedsCount + $uncreatedBeds;
                                $percent = $maxCap > 0 ? min(100, round(($occupied / $maxCap) * 100)) : 0;

                                // Nhận diện trạng thái bảo trì:
                                // Thuộc tính TrangThaiPhong chứa 'bảo trì' / 'khóa' HOẶC toàn bộ giường hiện có đang bảo trì và chưa có người ở
                                $rawStatus = trim($p['TrangThaiPhong'] ?? '');
                                $isMaintenance = str_contains(mb_strtolower($rawStatus), 'bảo trì')
                                    || str_contains(mb_strtolower($rawStatus), 'bao tri')
                                    || str_contains(mb_strtolower($rawStatus), 'khóa')
                                    || str_contains(mb_strtolower($rawStatus), 'khoa');

                                // Nhận diện phòng đầy:
                                $isFull = !$isMaintenance && (($occupied >= $maxCap && $maxCap > 0) || in_array($rawStatus, ['Đã đầy', 'Đầy', 'Đầy chỗ'], true));

                                if ($isMaintenance) {
                                    $statusKey = 'Bảo trì';
                                    $badgeClass = 'badge-warning';
                                    $statusText = 'Bảo trì';
                                } elseif ($isFull) {
                                    $statusKey = 'Đã đầy';
                                    $badgeClass = 'badge-success';
                                    $statusText = 'Đã đầy';
                                } else {
                                    $statusKey = 'Còn chỗ';
                                    $badgeClass = 'badge-info';
                                    $statusText = 'Còn chỗ (' . $vacant . ' trống)';
                                }

                                $searchContent = strtolower(trim(($p['MaPhong'] ?? '') . ' ' . ($p['SoPhong'] ?? '') . ' ' . ($p['TenToaNha'] ?? '') . ' ' . ($p['TenCum'] ?? '') . ' ' . ($p['TenLoaiPhong'] ?? '')));
                                ?>
                                <tr class="room-row"
                                    data-id="<?= htmlspecialchars($p['MaPhong'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-cum="<?= htmlspecialchars($p['MaCum']) ?>"
                                    data-toa="<?= htmlspecialchars($p['MaToaNha']) ?>"
                                    data-phong="<?= htmlspecialchars($p['MaPhong']) ?>"
                                    data-tang="<?= (int)$p['Tang'] ?>"
                                    data-loaiphong="<?= htmlspecialchars($p['MaLoaiPhong'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-status="<?= htmlspecialchars($statusKey, ENT_QUOTES, 'UTF-8') ?>"
                                    data-search="<?= htmlspecialchars($searchContent, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><b style="color: var(--primary); font-size: 0.95rem; font-family: monospace;"><?= htmlspecialchars($p['MaPhong']) ?></b></td>
                                    <td>
                                        <div><b><?= htmlspecialchars($p['TenToaNha']) ?></b></div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?= htmlspecialchars($p['TenCum']) ?></div>
                                    </td>
                                    <td>
                                        <div>Phòng <b><?= htmlspecialchars($p['SoPhong']) ?></b></div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);">Tầng <?= (int)$p['Tang'] ?></div>
                                    </td>
                                    <td>
                                        <div><b><?= htmlspecialchars($p['MaLoaiPhong']) ?></b></div>
                                        <div style="font-size: 0.775rem; color: var(--text-secondary);"><?= $maxCap ?> giường &bull; <?= number_format($p['DonGiaLuuTruChuan'] ?? 0, 0, ',', '.') ?> đ</div>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <b><?= $occupied ?> / <?= $maxCap ?></b>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);">(<?= $percent ?>%)</span>
                                        </div>
                                        <div style="width: 70px; height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 4px;">
                                            <div style="width: <?= $percent ?>%; height: 100%; background: <?= $isMaintenance ? '#f59e0b' : ($percent >= 100 ? '#16a34a' : '#2563eb') ?>;"></div>
                                        </div>
                                    </td>
                                    <!-- 6. Sơ đồ Giường -->
                                    <td style="vertical-align: middle;">
                                        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap; max-width: 170px;">

                                            <?php foreach ($beds as $bg): ?>
                                                <?php
                                                // Xác định trạng thái giường
                                                $bedStatus = trim($bg['TrangThaiGiuong'] ?? '');

                                                $bOccupied = (
                                                    !empty($bg['MaSinhVien']) ||
                                                    $bedStatus === 'Đã có người ở'
                                                );

                                                $bLocked = (
                                                    $bedStatus === 'Khóa bảo trì' ||
                                                    $bedStatus === 'Đang bảo trì'
                                                );

                                                // Xác định giường trưởng phòng
                                                $bLeader = (
                                                    !empty($p['TruongPhong']) &&
                                                    $p['TruongPhong'] === ($bg['MaSinhVien'] ?? '')
                                                );

                                                // Mặc định
                                                $pillBg = '#f8fafc';
                                                $pillColor = '#64748b';
                                                $pillBorder = '#e2e8f0';
                                                $pillIcon = 'fa-solid fa-bed';
                                                $pillTitle = $bg['KyHieuGiuong'] . ': Trống';

                                                if ($bOccupied) {

                                                    if ($bLeader) {
                                                        // Giường trưởng phòng: VÀNG
                                                        $pillBg = '#fef3c7';
                                                        $pillColor = '#b45309';
                                                        $pillBorder = '#fde68a';
                                                        $pillIcon = 'fa-solid fa-star';

                                                        $pillTitle = $bg['KyHieuGiuong'] . ': '
                                                            . ($bg['HoVaTen'] ?? 'Sinh viên')
                                                            . ' (Trưởng phòng)';
                                                    } elseif ($isRoomFull) {
                                                        // Giường thuộc phòng FULL: XANH LÁ
                                                        $pillBg = '#dcfce7';
                                                        $pillColor = '#15803d';
                                                        $pillBorder = '#bbf7d0';
                                                        $pillIcon = 'fa-solid fa-user';

                                                        $pillTitle = $bg['KyHieuGiuong'] . ': '
                                                            . ($bg['HoVaTen'] ?? 'Sinh viên')
                                                            . ' (Phòng đã FULL)';
                                                    } else {
                                                        // Giường có người, phòng chưa FULL: XANH DƯƠNG
                                                        $pillBg = '#dbeafe';
                                                        $pillColor = '#1d4ed8';
                                                        $pillBorder = '#bfdbfe';
                                                        $pillIcon = 'fa-solid fa-user';

                                                        $pillTitle = $bg['KyHieuGiuong'] . ': '
                                                            . ($bg['HoVaTen'] ?? 'Sinh viên')
                                                            . ' ('
                                                            . ($bg['MSSVTaiTruong'] ?? '')
                                                            . ')';
                                                    }
                                                } elseif ($bLocked) {
                                                    // Giường bảo trì: ĐỎ
                                                    $pillBg = '#fee2e2';
                                                    $pillColor = '#991b1b';
                                                    $pillBorder = '#fecaca';
                                                    $pillIcon = 'fa-solid fa-lock';

                                                    $pillTitle = $bg['KyHieuGiuong'] . ': Đang bảo trì';
                                                } else {
                                                    // Giường trống: XÁM
                                                    $pillBg = '#f8fafc';
                                                    $pillColor = '#64748b';
                                                    $pillBorder = '#e2e8f0';
                                                    $pillIcon = 'fa-solid fa-bed';

                                                    $pillTitle = $bg['KyHieuGiuong'] . ': Trống';
                                                }
                                                ?>

                                                <span
                                                    title="<?= htmlspecialchars($pillTitle, ENT_QUOTES, 'UTF-8') ?>"
                                                    style="
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    gap: 4px;
                    min-width: 46px;
                    height: 24px;
                    padding: 0 6px;
                    border-radius: 5px;
                    font-size: 0.725rem;
                    font-weight: 600;
                    background: <?= $pillBg ?>;
                    border: 1px solid <?= $pillBorder ?>;
                    color: <?= $pillColor ?>;
                    box-sizing: border-box;
                ">
                                                    <i class="<?= $pillIcon ?>" style="font-size: 0.65rem;"></i>
                                                    <span><?= htmlspecialchars($bg['KyHieuGiuong']) ?></span>
                                                </span>

                                            <?php endforeach; ?>

                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?>"><?= $statusText ?></span>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php
                                        $canEditThisRoom = !empty($scope['canUpdatePhong']);
                                        $canDeleteThisRoom = !empty($scope['canDeletePhong']) && ($scope['level'] === 'FULL' || empty($scope['maToa']) || $scope['maToa'] === $p['MaToaNha']);
                                        ?>
                                        <?php if ($canEditThisRoom || $canDeleteThisRoom): ?>
                                            <?php if ($canEditThisRoom): ?>
                                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                                    data-item="<?= htmlspecialchars(json_encode($p, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick='editRoom(this)'
                                                    title="Sửa phòng ở">
                                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDeleteThisRoom): ?>
                                                <button type="button" class="btn btn-outline btn-sm"
                                                    style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                                    data-ma="<?= htmlspecialchars($p['MaPhong'], ENT_QUOTES) ?>"
                                                    data-occupied="<?= $occupied ?>"
                                                    onclick="handleDeleteRoom(this)"
                                                    title="Xóa phòng ở">
                                                    <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.775rem;">
                                                <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Chỉ xem
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchPhongRow" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Phòng ở nào khớp với tiêu chí tìm kiếm và bộ lọc hiện tại.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG (PAGINATION CONTROLS GIỐNG PHÒNG VÀ USERS) -->
                <div id="roomPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="roomPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị 1 - 10 trên tổng số <?= count($phongList ?? []) ?> phòng
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="roomPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="roomPageSize" class="btn-outline" onchange="changeRoomPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="100">100 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>

                    <div id="roomPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                        <!-- Sinh tự động bằng Javascript -->
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu phòng ở trong phạm vi.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 2: DANH MỤC TÒA NHÀ & FORM THÊM / SỬA TÒA NHÀ -->
    <!-- ===================================================================== -->
    <div id="tabContentToa" style="display: none;">

        <!-- FORM THÊM / SỬA TÒA NHÀ (ẨN MẶC ĐỊNH) -->
        <div class="card" id="cardFormToa" style="display: none; margin-bottom: 24px; border: 2px solid #bfdbfe; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <div id="titleFormToa" style="font-weight: 700; color: #1e3a8a; font-size: 1.05rem;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                    <span id="titleFormToaText">Thêm mới Tòa nhà lưu trú</span>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="hideToaForm()" style="border-radius: 6px; color: var(--text-muted); cursor: pointer;" title="Đóng biểu mẫu">
                    <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
                </button>
            </div>

            <form action="<?= url('staff/zones/save-toa') ?>" method="POST" id="formToa">
                <?= csrf_field() ?>
                <input type="hidden" name="isEdit" id="toaIsEdit" value="0">

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã Tòa nhà <span style="color:#dc2626;">*</span>
                            <span id="noticeToaEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="toaFormMaToa" name="maToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: A01, B02..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên Tòa nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="toaFormTenToa" name="tenToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Tòa nhà A01..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Cụm nhà trực thuộc <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaFormMaCum" name="maCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn Cụm nhà --</option>
                            <?php foreach ($cumList as $c): ?>
                                <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']) ? 'selected' : '' ?>>
                                    Cụm <?= htmlspecialchars($c['TenCum']) ?> (Khu <?= htmlspecialchars($c['MaKhu'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tổng số tầng lầu <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="number" id="toaFormTongTang" name="tongSoTang" min="1" value="5" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Quy định Giới tính lưu trú <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaFormGioiTinh" name="gioiTinhQuyDinh" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Hỗn hợp">Hỗn hợp</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Trạng thái hoạt động <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaFormTrangThai" name="trangThaiToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Đang hoạt động">Đang hoạt động</option>
                            <option value="Tạm đóng">Tạm đóng</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="btnSubmitToa" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Tòa nhà
                        </button>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormToa()">
                            <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px; color: #dc2626; border-color: #fecaca;" onclick="hideToaForm()">
                            <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH TÒA NHÀ TRONG PHẠM VI (TÍCH HỢP TÌM KIẾM & PHÂN TRANG) -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>Danh sách Tòa nhà lưu trú</span>
                    <span class="badge badge-info" id="toaCountBadge"><?= count($toaList ?? []) ?> tòa</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if (!empty($scope['canCreateToa'])): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showCreateToaForm()" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                            <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm tòa nhà mới
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC TÒA NHÀ -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 380px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchToaInput" class="btn-outline"
                        placeholder="Tìm theo mã tòa, tên tòa, cụm..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterToas()">
                </div>

                <select id="filterToaCum" class="btn-outline"
                    style="min-width: 180px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterToas()">
                    <option value="">-- Tất cả Cụm --</option>
                    <?php foreach ($cumList as $c): ?>
                        <option value="<?= htmlspecialchars($c['MaCum']) ?>">
                            Cụm <?= htmlspecialchars($c['TenCum']) ?> (<?= htmlspecialchars($c['MaCum']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="filterToaGender" class="btn-outline"
                    style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterToas()">
                    <option value="">-- Tất cả giới tính --</option>
                    <option value="Nam">Nam</option>
                    <option value="Nữ">Nữ</option>
                    <option value="Hỗn hợp">Hỗn hợp</option>
                </select>

                <select id="filterToaStatus" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterToas()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Đang hoạt động">Đang hoạt động</option>
                    <option value="Tạm đóng">Tạm đóng</option>
                </select>

                <div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="clearToaFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                        <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>

            <?php if (!empty($toaList)): ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableStaffToa">
                        <thead>
                            <tr>
                                <th>Mã tòa</th>
                                <th>Tên Tòa & Cụm</th>
                                <th>Khu KTX</th>
                                <th>Số tầng</th>
                                <th>Giới tính</th>
                                <th>Quy mô phòng</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="toaTableBody">
                            <?php foreach ($toaList as $t): ?>
                                <?php
                                $searchToa = strtolower(trim(($t['MaToaNha'] ?? '') . ' ' . ($t['TenToaNha'] ?? '') . ' ' . ($t['TenCum'] ?? '') . ' ' . ($t['MaKhu'] ?? '')));
                                ?>
                                <tr class="toa-row"
                                    data-toa="<?= htmlspecialchars($t['MaToaNha']) ?>"
                                    data-cum="<?= htmlspecialchars($t['MaCum']) ?>"
                                    data-gender="<?= htmlspecialchars($t['GioiTinhQuyDinh'] ?? '') ?>"
                                    data-status="<?= htmlspecialchars($t['TrangThaiToaNha'] ?? '') ?>"
                                    data-search="<?= htmlspecialchars($searchToa, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><b style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($t['MaToaNha']) ?></b></td>
                                    <td>
                                        <div><b><?= htmlspecialchars($t['TenToaNha']) ?></b></div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?= htmlspecialchars($t['TenCum']) ?></div>
                                    </td>
                                    <td><span class="badge badge-info" style="font-size: 0.75rem;">Khu <?= htmlspecialchars($t['MaKhu'] ?? '') ?></span></td>
                                    <td><?= (int)$t['TongSoTang'] ?> tầng</td>
                                    <td>
                                        <?php if ($t['GioiTinhQuyDinh'] === 'Nam'): ?>
                                            <span style="color: #2563eb; font-weight: 600;"><i class="fa-solid fa-mars" style="margin-right: 4px;"></i> Nam</span>
                                        <?php elseif ($t['GioiTinhQuyDinh'] === 'Nữ'): ?>
                                            <span style="color: #db2777; font-weight: 600;"><i class="fa-solid fa-venus" style="margin-right: 4px;"></i> Nữ</span>
                                        <?php else: ?>
                                            <span style="color: #059669; font-weight: 600;"><i class="fa-solid fa-venus-mars" style="margin-right: 4px;"></i> <?= htmlspecialchars($t['GioiTinhQuyDinh']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><b><?= (int)($t['TongPhong'] ?? 0) ?></b> phòng</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= (int)($t['TongGiuong'] ?? 0) ?> giường</div>
                                    </td>
                                    <td>
                                        <?php if ($t['TrangThaiToaNha'] === 'Đang hoạt động'): ?>
                                            <span class="badge badge-success">Hoạt động</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Tạm đóng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php
                                        $canEditThisToa = !empty($scope['canUpdateToa']) && ($scope['level'] === 'FULL' || empty($scope['maToa']) || $scope['maToa'] === $t['MaToaNha']);
                                        $canDeleteThisToa = !empty($scope['canDeleteToa']) && ($scope['level'] === 'FULL' || ($scope['level'] === 'CUM' && $scope['maCum'] === $t['MaCum']));
                                        ?>
                                        <?php if ($canEditThisToa || $canDeleteThisToa): ?>
                                            <?php if ($canEditThisToa): ?>
                                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                                    data-item="<?= htmlspecialchars(json_encode($t, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick='editToa(this)'
                                                    title="Sửa thông tin tòa">
                                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDeleteThisToa): ?>
                                                <button type="button" class="btn btn-outline btn-sm"
                                                    style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                                    data-ma="<?= htmlspecialchars($t['MaToaNha'], ENT_QUOTES) ?>"
                                                    data-rooms="<?= (int)($t['TongPhong'] ?? 0) ?>"
                                                    onclick="handleDeleteToa(this)"
                                                    title="Xóa tòa nhà">
                                                    <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.775rem;">
                                                <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Chỉ xem
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchToaRow" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Tòa nhà nào khớp với tiêu chí tìm kiếm.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG TÒA NHÀ -->
                <div id="toaPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="toaPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị 1 - 10 trên tổng số <?= count($toaList ?? []) ?> tòa
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="toaPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="toaPageSize" class="btn-outline" onchange="changeToaPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>
                    <div id="toaPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu tòa nhà.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: DANH MỤC CỤM NHÀ & FORM THÊM / SỬA CỤM NHÀ -->
    <!-- ===================================================================== -->
    <div id="tabContentCum" style="display: none;">

        <!-- FORM THÊM / SỬA CỤM NHÀ (ẨN MẶC ĐỊNH) -->
        <div class="card" id="cardFormCum" style="display: none; margin-bottom: 24px; border: 2px solid #bfdbfe; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <div id="titleFormCum" style="font-weight: 700; color: #1e3a8a; font-size: 1.05rem;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                    <span id="titleFormCumText">Thêm mới Cụm nhà lưu trú</span>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="hideCumForm()" style="border-radius: 6px; color: var(--text-muted); cursor: pointer;" title="Đóng biểu mẫu">
                    <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
                </button>
            </div>

            <form action="<?= url('staff/zones/save-cum') ?>" method="POST" id="formCum">
                <?= csrf_field() ?>
                <input type="hidden" name="isEdit" id="cumIsEdit" value="0">

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã Cụm nhà <span style="color:#dc2626;">*</span>
                            <span id="noticeCumEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="cumFormMaCum" name="maCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: AG, AH, BA..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên Cụm nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="cumFormTenCum" name="tenCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Cụm AG..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Khu KTX trực thuộc <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="cumFormMaKhu" name="maKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn Khu KTX --</option>
                            <?php foreach ($khuList as $k): ?>
                                <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= ($scope['level'] === 'KHU' && $scope['maKhu'] === $k['MaKhu']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['TenKhu']) ?> (<?= htmlspecialchars($k['MaKhu']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Họ tên Trưởng ban quản lý cụm:
                        </label>
                        <input type="text" id="cumFormTruongCum" name="hoTenTruongCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Nhập họ tên cán bộ Trưởng cụm...">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Số điện thoại liên hệ cụm:
                        </label>
                        <input type="text" id="cumFormSdt" name="soDienThoaiCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 028.37242160...">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="btnSubmitCum" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Cụm nhà
                        </button>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormCum()">
                            <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px; color: #dc2626; border-color: #fecaca;" onclick="hideCumForm()">
                            <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH CỤM NHÀ (TÍCH HỢP TÌM KIẾM & PHÂN TRANG) -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>Danh sách Cụm nhà trực thuộc</span>
                    <span class="badge badge-info" id="cumCountBadge"><?= count($cumList ?? []) ?> cụm</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if (!empty($scope['canCreateCum'])): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showCreateCumForm()" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                            <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm cụm mới
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC CỤM NHÀ -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 420px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchCumInput" class="btn-outline"
                        placeholder="Tìm theo mã cụm, tên cụm, trưởng cụm..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterCums()">
                </div>

                <select id="filterCumKhu" class="btn-outline"
                    style="min-width: 180px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterCums()">
                    <option value="">-- Tất cả Khu KTX --</option>
                    <?php foreach ($khuList as $k): ?>
                        <option value="<?= htmlspecialchars($k['MaKhu']) ?>">
                            <?= htmlspecialchars($k['TenKhu']) ?> (<?= htmlspecialchars($k['MaKhu']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>

                <div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="clearCumFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                        <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>

            <?php if (!empty($cumList)): ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableStaffCum">
                        <thead>
                            <tr>
                                <th>Mã cụm</th>
                                <th>Tên Cụm nhà</th>
                                <th>Khu KTX</th>
                                <th>Trưởng ban quản lý cụm</th>
                                <th>Số ĐT liên hệ</th>
                                <th>Quy mô</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="cumTableBody">
                            <?php foreach ($cumList as $c): ?>
                                <?php
                                $searchCum = strtolower(trim(($c['MaCum'] ?? '') . ' ' . ($c['TenCum'] ?? '') . ' ' . ($c['HoTenTruongCum'] ?? '') . ' ' . ($c['MaKhu'] ?? '') . ' ' . ($c['TenKhu'] ?? '')));
                                ?>
                                <tr class="cum-row"
                                    data-cum="<?= htmlspecialchars($c['MaCum']) ?>"
                                    data-khu="<?= htmlspecialchars($c['MaKhu']) ?>"
                                    data-search="<?= htmlspecialchars($searchCum, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><b style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($c['MaCum']) ?></b></td>
                                    <td><b><?= htmlspecialchars($c['TenCum']) ?></b></td>
                                    <td><span class="badge badge-info" style="font-size: 0.75rem;"><?= htmlspecialchars($c['TenKhu'] ?? ('Khu ' . $c['MaKhu'])) ?></span></td>
                                    <td>
                                        <?php if (!empty($c['HoTenTruongCum'])): ?>
                                            <b><?= htmlspecialchars($c['HoTenTruongCum']) ?></b>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted); font-style: italic;">Chưa phân công</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($c['SoDienThoaiCum'] ?? '---') ?></td>
                                    <td>
                                        <div><b><?= (int)($c['TongToa'] ?? 0) ?></b> tòa</div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?= (int)($c['TongPhong'] ?? 0) ?> phòng &bull; <?= (int)($c['TongGiuong'] ?? 0) ?> giường</div>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php
                                        $canEditThisCum = !empty($scope['canUpdateCum']) && ($scope['level'] === 'FULL' || ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']));
                                        $canDeleteThisCum = !empty($scope['canDeleteCum']) && ($scope['level'] === 'FULL' || ($scope['level'] === 'KHU' && $scope['maKhu'] === $c['MaKhu']));
                                        ?>
                                        <?php if ($canEditThisCum || $canDeleteThisCum): ?>
                                            <?php if ($canEditThisCum): ?>
                                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                                    data-item="<?= htmlspecialchars(json_encode($c, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick='editCum(this)'
                                                    title="Sửa thông tin cụm">
                                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDeleteThisCum): ?>
                                                <button type="button" class="btn btn-outline btn-sm"
                                                    style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                                    data-ma="<?= htmlspecialchars($c['MaCum'], ENT_QUOTES) ?>"
                                                    data-toas="<?= (int)($c['TongToa'] ?? 0) ?>"
                                                    onclick="handleDeleteCum(this)"
                                                    title="Xóa cụm nhà">
                                                    <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.775rem;">
                                                <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Chỉ xem
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchCumRow" style="display: none;">
                                <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Cụm nhà nào khớp với tiêu chí tìm kiếm.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG CỤM NHÀ -->
                <div id="cumPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="cumPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị 1 - 10 trên tổng số <?= count($cumList ?? []) ?> cụm
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="cumPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="cumPageSize" class="btn-outline" onchange="changeCumPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="50">50 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>
                    <div id="cumPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu cụm nhà.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: DANH MỤC KHU KTX & FORM THÊM / SỬA KHU KTX -->
    <!-- ===================================================================== -->
    <div id="tabContentKhu" style="display: none;">

        <!-- FORM THÊM / SỬA KHU KTX (ẨN MẶC ĐỊNH) -->
        <div class="card" id="cardFormKhu" style="display: none; margin-bottom: 24px; border: 2px solid #bfdbfe; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px; margin-bottom: 16px;">
                <div id="titleFormKhu" style="font-weight: 700; color: #1e3a8a; font-size: 1.05rem;">
                    <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                    <span id="titleFormKhuText">Thêm mới Khu KTX</span>
                </div>
                <button type="button" class="btn btn-outline btn-sm" onclick="hideKhuForm()" style="border-radius: 6px; color: var(--text-muted); cursor: pointer;" title="Đóng biểu mẫu">
                    <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
                </button>
            </div>

            <form action="<?= url('staff/zones/save-khu') ?>" method="POST" id="formKhu">
                <?= csrf_field() ?>
                <input type="hidden" name="isEdit" id="khuIsEdit" value="0">

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã Khu KTX <span style="color:#dc2626;">*</span>
                            <span id="noticeKhuEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="khuFormMaKhu" name="maKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: A, B..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên Khu KTX <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="khuFormTenKhu" name="tenKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Khu A - ĐHQG-HCM..." required>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Địa chỉ địa lý:
                    </label>
                    <input type="text" id="khuFormDiaChi" name="diaChi" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Phường Linh Trung, TP. Thủ Đức, TP.HCM...">
                </div>

                <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" id="btnSubmitKhu" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Khu KTX
                        </button>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormKhu()">
                            <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px; color: #dc2626; border-color: #fecaca;" onclick="hideKhuForm()">
                            <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH MỤC KHU KTX (TÍCH HỢP TÌM KIẾM & PHÂN TRANG) -->
        <div class="card" style="margin-bottom: 24px;">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>Danh mục Khu KTX</span>
                    <span class="badge badge-info" id="khuCountBadge"><?= count($khuList ?? []) ?> khu</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <?php if (!empty($scope['canCreateKhu'])): ?>
                        <button type="button" class="btn btn-primary btn-sm" onclick="showCreateKhuForm()" style="border-radius: 6px; font-weight: 600; padding: 7px 14px;">
                            <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm khu mới
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- THANH TÌM KIẾM KHU KTX -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 420px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchKhuInput" class="btn-outline"
                        placeholder="Tìm theo mã khu, tên khu, địa chỉ..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterKhus()">
                </div>

                <div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="clearKhuFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                        <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>

            <?php if (!empty($khuList)): ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableStaffKhu">
                        <thead>
                            <tr>
                                <th>Mã khu</th>
                                <th>Tên Khu KTX</th>
                                <th>Địa chỉ</th>
                                <th>Quy mô Cụm & Tòa</th>
                                <th>Tổng phòng ở</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="khuTableBody">
                            <?php foreach ($khuList as $k): ?>
                                <?php
                                $searchKhu = strtolower(trim(($k['MaKhu'] ?? '') . ' ' . ($k['TenKhu'] ?? '') . ' ' . ($k['DiaChi'] ?? '')));
                                ?>
                                <tr class="khu-row"
                                    data-khu="<?= htmlspecialchars($k['MaKhu']) ?>"
                                    data-search="<?= htmlspecialchars($searchKhu, ENT_QUOTES, 'UTF-8') ?>">
                                    <td><b style="color: var(--primary); font-family: monospace;"><?= htmlspecialchars($k['MaKhu']) ?></b></td>
                                    <td><b><?= htmlspecialchars($k['TenKhu']) ?></b></td>
                                    <td><?= htmlspecialchars($k['DiaChi'] ?? '---') ?></td>
                                    <td>
                                        <div><b><?= (int)($k['TongCum'] ?? 0) ?></b> cụm</div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?= (int)($k['TongToa'] ?? 0) ?> tòa nhà</div>
                                    </td>
                                    <td><b><?= (int)($k['TongPhong'] ?? 0) ?></b> phòng</td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <?php
                                        $canEditThisKhu = !empty($scope['canUpdateKhu']);
                                        $canDeleteThisKhu = !empty($scope['canDeleteKhu']);
                                        ?>
                                        <?php if ($canEditThisKhu || $canDeleteThisKhu): ?>
                                            <?php if ($canEditThisKhu): ?>
                                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                                    data-item="<?= htmlspecialchars(json_encode($k, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                    onclick='editKhu(this)'
                                                    title="Sửa thông tin khu">
                                                    <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($canDeleteThisKhu): ?>
                                                <button type="button" class="btn btn-outline btn-sm"
                                                    style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                                    data-ma="<?= htmlspecialchars($k['MaKhu'], ENT_QUOTES) ?>"
                                                    data-cums="<?= (int)($k['TongCum'] ?? 0) ?>"
                                                    onclick="handleDeleteKhu(this)"
                                                    title="Xóa khu KTX">
                                                    <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.775rem;">
                                                <i class="fa-solid fa-eye" style="margin-right: 4px;"></i> Chỉ xem
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchKhuRow" style="display: none;">
                                <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Khu KTX nào khớp với tiêu chí tìm kiếm.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG KHU KTX -->
                <div id="khuPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                        <span id="khuPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                            Hiển thị 1 - 10 trên tổng số <?= count($khuList ?? []) ?> khu
                        </span>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <label for="khuPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                            <select id="khuPageSize" class="btn-outline" onchange="changeKhuPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                                <option value="10" selected>10 / trang</option>
                                <option value="25">25 / trang</option>
                                <option value="-1">Tất cả</option>
                            </select>
                        </div>
                    </div>
                    <div id="khuPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu khu KTX.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL IMPORT DANH SÁCH PHÒNG TỪ FILE CSV (GIỐNG MẪU PHÒNG & USERS) -->
<!-- ========================================================================= -->
<div id="importZoneModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; max-height: calc(100vh - 40px); display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; flex-shrink: 0;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-import" style="color: #2563eb; margin-right: 6px;"></i>
                <span>Nhập danh sách Phòng ở Hạ tầng từ CSV</span>
            </div>
            <button type="button" onclick="closeImportZoneModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= url('staff/zones/import') ?>" method="POST" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; flex: 1 1 auto; overflow: hidden;">
            <?= csrf_field() ?>
            <div style="padding: 20px; overflow-y: auto; flex: 1 1 auto; max-height: calc(100vh - 170px);">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #1e40af;">
                    <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Quy định file CSV nhập dữ liệu:
                    </div>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                        <li>Hỗ trợ file định dạng <b>.CSV</b> hoặc <b>.TXT</b> (mã hóa UTF-8), phân cách bởi dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>.</li>
                        <li><b>Cột bắt buộc:</b> <code>MaToaNha</code>, <code>SoPhong</code>.</li>
                        <li><b>Cột thông tin bổ sung:</b> <code>MaPhong</code> (tùy chọn, mặc định tự sinh: <code>MaToa.SoPhong</code>), <code>Tang</code>, <code>MaLoaiPhong</code> (LP01, LP02...), <code>TrangThaiPhong</code> (Còn chỗ, Đã đầy, Bảo trì).</li>
                        <li><i>Hệ thống tự động sinh các giường trống (G1, G2...) tương ứng theo đúng loại phòng đã chọn!</i></li>
                    </ul>
                    <div style="margin-top: 12px; display: flex; justify-content: flex-end;">
                        <a href="<?= url('staff/zones/template') ?>" class="btn btn-outline btn-sm" style="background: #fff; color: #1e40af; border-color: #93c5fd; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 6px 14px; display: inline-flex; align-items: center;">
                            <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Tải tệp CSV mẫu chuẩn
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                        Chọn tệp CSV phòng ở <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="btn-outline" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem; background: #fff;">
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Tệp tối đa 10MB, mã hóa UTF-8.</small>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                    <input type="hidden" name="overwriteExisting" value="0">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                        <input type="checkbox" id="overwriteExistingZones" name="overwriteExisting" value="1" checked style="margin-top: 3px; width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;">
                        <span>
                            <b style="color: #1e293b;">Cập nhật / Ghi đè nếu phòng đã tồn tại:</b><br>
                            <span style="font-size: 0.8rem; color: #64748b; line-height: 1.5; display: block; margin-top: 2px;">
                                • <b>Nếu chọn:</b> Cập nhật lại thông tin tầng, loại phòng và trạng thái nếu phòng đã có trong hệ thống.<br>
                                • <b>Nếu bỏ chọn:</b> Bỏ qua các phòng đã có, chỉ thêm mới các phòng chưa tồn tại.
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" class="btn btn-outline" onclick="closeImportZoneModal()" style="border-radius: 6px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600;">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px;"></i> Bắt đầu nhập dữ liệu
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Dữ liệu danh mục tham chiếu cho Cascade & Form
    const allToasData = <?= json_encode($toaList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const allCumsData = <?= json_encode($cumList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const allKhusData = <?= json_encode($khuList ?? [], JSON_UNESCAPED_UNICODE) ?>;

    // =========================================================================
    // 1. QUẢN LÝ TABS ĐIỀU HƯỚNG
    // =========================================================================
    function switchZoneTab(tab) {
        const tabs = ['phong', 'toa', 'cum', 'khu'];
        tabs.forEach(t => {
            const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
            const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1));
            if (!btn || !content) return;

            if (t === tab) {
                btn.style.color = 'var(--primary)';
                btn.style.background = '#eff6ff';
                btn.style.fontWeight = '700';
                content.style.display = 'block';
            } else {
                btn.style.color = 'var(--text-secondary)';
                btn.style.background = 'none';
                btn.style.fontWeight = '600';
                content.style.display = 'none';
            }
        });
    }

    // =========================================================================
    // 2. PHÂN TRANG VÀ LỌC PHÒNG Ở (REAL-TIME PAGINATION & FILTER NHƯ USERS & ROOMS)
    // =========================================================================
    let roomCurrentPage = 1;
    let roomPageSize = 10;
    let roomMatchedRows = [];

    function filterRooms() {
        const searchVal = (document.getElementById('searchRoomInput')?.value || '').toLowerCase().trim();
        const cumVal = document.getElementById('filterSelectCum')?.value || '';
        const toaVal = document.getElementById('filterSelectToa')?.value || '';
        const floorVal = document.getElementById('filterRoomFloor')?.value || '';
        const typeVal = document.getElementById('filterRoomType')?.value || '';
        const statusVal = document.getElementById('filterRoomStatus')?.value || '';

        const allRows = Array.from(document.querySelectorAll('.room-row'));
        roomMatchedRows = [];

        allRows.forEach(row => {
            const rCum = row.getAttribute('data-cum') || '';
            const rToa = row.getAttribute('data-toa') || '';
            const rTang = row.getAttribute('data-tang') || '';
            const rType = row.getAttribute('data-loaiphong') || '';
            const rStatus = row.getAttribute('data-status') || '';
            const rSearch = row.getAttribute('data-search') || '';

            const matchSearch = !searchVal || rSearch.includes(searchVal);
            const matchCum = !cumVal || rCum === cumVal;
            const matchToa = !toaVal || rToa === toaVal;
            const matchFloor = !floorVal || rTang === floorVal;
            const matchType = !typeVal || rType === typeVal;
            const matchStatus = !statusVal || (
                statusVal === 'Bảo trì' ? (rStatus === 'Bảo trì' || rStatus.includes('bảo trì') || rStatus.includes('Khóa')) :
                statusVal === 'Đã đầy' ? (rStatus === 'Đã đầy' || rStatus.includes('đầy') || rStatus.includes('Đầy')) :
                rStatus === statusVal
            );

            if (matchSearch && matchCum && matchToa && matchFloor && matchType && matchStatus) {
                roomMatchedRows.push(row);
            }
        });

        roomCurrentPage = 1;
        renderRoomPagination();
    }

    function renderRoomPagination() {
        const allRows = document.querySelectorAll('.room-row');
        const total = roomMatchedRows.length;
        const totalPages = roomPageSize > 0 ? Math.ceil(total / roomPageSize) : 1;

        if (roomCurrentPage > totalPages) roomCurrentPage = totalPages;
        if (roomCurrentPage < 1) roomCurrentPage = 1;

        const startIdx = roomPageSize > 0 ? (roomCurrentPage - 1) * roomPageSize : 0;
        const endIdx = roomPageSize > 0 ? startIdx + roomPageSize : total;

        allRows.forEach(row => row.style.display = 'none');
        roomMatchedRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const noMatch = document.getElementById('noMatchPhongRow');
        if (noMatch) noMatch.style.display = (total === 0 && allRows.length > 0) ? '' : 'none';

        const countBadge = document.getElementById('roomCountBadge');
        if (countBadge) countBadge.innerText = total + ' phòng';
        const tabBadge = document.getElementById('tabCountPhong');
        if (tabBadge) tabBadge.innerText = total;

        // Cập nhật thông tin phân trang
        const infoEl = document.getElementById('roomPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = 'Không có phòng nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> phòng (Trang <b>${roomCurrentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        // Tạo nút phân trang
        const controlsEl = document.getElementById('roomPaginationControls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';

        if (totalPages <= 1) return;

        // First & Prev
        const btnFirst = document.createElement('button');
        btnFirst.type = 'button';
        btnFirst.className = 'btn btn-outline btn-sm';
        btnFirst.innerHTML = '<i class="fa-solid fa-angles-left"></i>';
        btnFirst.disabled = (roomCurrentPage === 1);
        btnFirst.onclick = () => goToRoomPage(1);
        btnFirst.style.borderRadius = '6px';
        controlsEl.appendChild(btnFirst);

        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'btn btn-outline btn-sm';
        btnPrev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
        btnPrev.disabled = (roomCurrentPage === 1);
        btnPrev.onclick = () => goToRoomPage(roomCurrentPage - 1);
        btnPrev.style.borderRadius = '6px';
        controlsEl.appendChild(btnPrev);

        // Page buttons
        let startPage = Math.max(1, roomCurrentPage - 2);
        let endPage = Math.min(totalPages, roomCurrentPage + 2);
        if (startPage > 1) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.padding = '0 4px';
            controlsEl.appendChild(dot);
        }
        for (let p = startPage; p <= endPage; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === roomCurrentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.borderRadius = '6px';
            btnP.onclick = () => goToRoomPage(p);
            controlsEl.appendChild(btnP);
        }
        if (endPage < totalPages) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.padding = '0 4px';
            controlsEl.appendChild(dot);
        }

        // Next & Last
        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'btn btn-outline btn-sm';
        btnNext.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
        btnNext.disabled = (roomCurrentPage === totalPages);
        btnNext.onclick = () => goToRoomPage(roomCurrentPage + 1);
        btnNext.style.borderRadius = '6px';
        controlsEl.appendChild(btnNext);

        const btnLast = document.createElement('button');
        btnLast.type = 'button';
        btnLast.className = 'btn btn-outline btn-sm';
        btnLast.innerHTML = '<i class="fa-solid fa-angles-right"></i>';
        btnLast.disabled = (roomCurrentPage === totalPages);
        btnLast.onclick = () => goToRoomPage(totalPages);
        btnLast.style.borderRadius = '6px';
        controlsEl.appendChild(btnLast);
    }

    function goToRoomPage(page) {
        roomCurrentPage = page;
        renderRoomPagination();
    }

    function changeRoomPageSize(val) {
        roomPageSize = parseInt(val, 10);
        roomCurrentPage = 1;
        renderRoomPagination();
    }

    function clearRoomFilters() {
        if (document.getElementById('searchRoomInput')) document.getElementById('searchRoomInput').value = '';
        if (document.getElementById('filterSelectCum')) document.getElementById('filterSelectCum').value = '';
        if (document.getElementById('filterSelectToa')) {
            document.getElementById('filterSelectToa').value = '';
            // Reset danh sách tòa
            updateToaFilterOptions('');
        }
        if (document.getElementById('filterRoomFloor')) document.getElementById('filterRoomFloor').value = '';
        if (document.getElementById('filterRoomType')) document.getElementById('filterRoomType').value = '';
        if (document.getElementById('filterRoomStatus')) document.getElementById('filterRoomStatus').value = '';

        filterRooms();
    }

    function onCascadeCumChange() {
        const cumVal = document.getElementById('filterSelectCum')?.value || '';
        updateToaFilterOptions(cumVal);
        filterRooms();
    }

    function onCascadeToaChange() {
        filterRooms();
    }

    function updateToaFilterOptions(cumVal) {
        const selectToa = document.getElementById('filterSelectToa');
        if (!selectToa) return;

        selectToa.innerHTML = '<option value="">-- Tất cả Tòa --</option>';
        allToasData.forEach(t => {
            if (!cumVal || t.MaCum === cumVal) {
                const opt = document.createElement('option');
                opt.value = t.MaToaNha;
                opt.setAttribute('data-cum', t.MaCum);
                opt.textContent = `${t.TenToaNha} (${t.MaToaNha})`;
                selectToa.appendChild(opt);
            }
        });
    }

    // =========================================================================
    // 3. PHÂN TRANG VÀ LỌC TÒA NHÀ (TAB 2)
    // =========================================================================
    let toaCurrentPage = 1;
    let toaPageSize = 10;
    let toaMatchedRows = [];

    function filterToas() {
        const searchVal = (document.getElementById('searchToaInput')?.value || '').toLowerCase().trim();
        const cumVal = document.getElementById('filterToaCum')?.value || '';
        const genderVal = document.getElementById('filterToaGender')?.value || '';
        const statusVal = document.getElementById('filterToaStatus')?.value || '';

        const allRows = Array.from(document.querySelectorAll('.toa-row'));
        toaMatchedRows = [];

        allRows.forEach(row => {
            const tCum = row.getAttribute('data-cum') || '';
            const tGender = row.getAttribute('data-gender') || '';
            const tStatus = row.getAttribute('data-status') || '';
            const tSearch = row.getAttribute('data-search') || '';

            const matchSearch = !searchVal || tSearch.includes(searchVal);
            const matchCum = !cumVal || tCum === cumVal;
            const matchGender = !genderVal || tGender === genderVal;
            const matchStatus = !statusVal || tStatus === statusVal;

            if (matchSearch && matchCum && matchGender && matchStatus) {
                toaMatchedRows.push(row);
            }
        });

        toaCurrentPage = 1;
        renderToaPagination();
    }

    function renderToaPagination() {
        const allRows = document.querySelectorAll('.toa-row');
        const total = toaMatchedRows.length;
        const totalPages = toaPageSize > 0 ? Math.ceil(total / toaPageSize) : 1;

        if (toaCurrentPage > totalPages) toaCurrentPage = totalPages;
        if (toaCurrentPage < 1) toaCurrentPage = 1;

        const startIdx = toaPageSize > 0 ? (toaCurrentPage - 1) * toaPageSize : 0;
        const endIdx = toaPageSize > 0 ? startIdx + toaPageSize : total;

        allRows.forEach(row => row.style.display = 'none');
        toaMatchedRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const noMatch = document.getElementById('noMatchToaRow');
        if (noMatch) noMatch.style.display = (total === 0 && allRows.length > 0) ? '' : 'none';

        const countBadge = document.getElementById('toaCountBadge');
        if (countBadge) countBadge.innerText = total + ' tòa';
        const tabBadge = document.getElementById('tabCountToa');
        if (tabBadge) tabBadge.innerText = total;

        const infoEl = document.getElementById('toaPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = 'Không có tòa nhà nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> tòa (Trang <b>${toaCurrentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        const controlsEl = document.getElementById('toaPaginationControls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';
        if (totalPages <= 1) return;

        for (let p = 1; p <= totalPages; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === toaCurrentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.borderRadius = '6px';
            btnP.onclick = () => {
                toaCurrentPage = p;
                renderToaPagination();
            };
            controlsEl.appendChild(btnP);
        }
    }

    function changeToaPageSize(val) {
        toaPageSize = parseInt(val, 10);
        toaCurrentPage = 1;
        renderToaPagination();
    }

    function clearToaFilters() {
        if (document.getElementById('searchToaInput')) document.getElementById('searchToaInput').value = '';
        if (document.getElementById('filterToaCum')) document.getElementById('filterToaCum').value = '';
        if (document.getElementById('filterToaGender')) document.getElementById('filterToaGender').value = '';
        if (document.getElementById('filterToaStatus')) document.getElementById('filterToaStatus').value = '';
        filterToas();
    }

    // =========================================================================
    // 4. PHÂN TRANG VÀ LỌC CỤM NHÀ (TAB 3)
    // =========================================================================
    let cumCurrentPage = 1;
    let cumPageSize = 10;
    let cumMatchedRows = [];

    function filterCums() {
        const searchVal = (document.getElementById('searchCumInput')?.value || '').toLowerCase().trim();
        const khuVal = document.getElementById('filterCumKhu')?.value || '';

        const allRows = Array.from(document.querySelectorAll('.cum-row'));
        cumMatchedRows = [];

        allRows.forEach(row => {
            const cKhu = row.getAttribute('data-khu') || '';
            const cSearch = row.getAttribute('data-search') || '';

            const matchSearch = !searchVal || cSearch.includes(searchVal);
            const matchKhu = !khuVal || cKhu === khuVal;

            if (matchSearch && matchKhu) {
                cumMatchedRows.push(row);
            }
        });

        cumCurrentPage = 1;
        renderCumPagination();
    }

    function renderCumPagination() {
        const allRows = document.querySelectorAll('.cum-row');
        const total = cumMatchedRows.length;
        const totalPages = cumPageSize > 0 ? Math.ceil(total / cumPageSize) : 1;

        if (cumCurrentPage > totalPages) cumCurrentPage = totalPages;
        if (cumCurrentPage < 1) cumCurrentPage = 1;

        const startIdx = cumPageSize > 0 ? (cumCurrentPage - 1) * cumPageSize : 0;
        const endIdx = cumPageSize > 0 ? startIdx + cumPageSize : total;

        allRows.forEach(row => row.style.display = 'none');
        cumMatchedRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const noMatch = document.getElementById('noMatchCumRow');
        if (noMatch) noMatch.style.display = (total === 0 && allRows.length > 0) ? '' : 'none';

        const countBadge = document.getElementById('cumCountBadge');
        if (countBadge) countBadge.innerText = total + ' cụm';
        const tabBadge = document.getElementById('tabCountCum');
        if (tabBadge) tabBadge.innerText = total;

        const infoEl = document.getElementById('cumPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = 'Không có cụm nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> cụm (Trang <b>${cumCurrentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        const controlsEl = document.getElementById('cumPaginationControls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';
        if (totalPages <= 1) return;

        for (let p = 1; p <= totalPages; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === cumCurrentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.borderRadius = '6px';
            btnP.onclick = () => {
                cumCurrentPage = p;
                renderCumPagination();
            };
            controlsEl.appendChild(btnP);
        }
    }

    function changeCumPageSize(val) {
        cumPageSize = parseInt(val, 10);
        cumCurrentPage = 1;
        renderCumPagination();
    }

    function clearCumFilters() {
        if (document.getElementById('searchCumInput')) document.getElementById('searchCumInput').value = '';
        if (document.getElementById('filterCumKhu')) document.getElementById('filterCumKhu').value = '';
        filterCums();
    }

    // =========================================================================
    // 5. PHÂN TRANG VÀ LỌC KHU KTX (TAB 4)
    // =========================================================================
    let khuCurrentPage = 1;
    let khuPageSize = 10;
    let khuMatchedRows = [];

    function filterKhus() {
        const searchVal = (document.getElementById('searchKhuInput')?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('.khu-row'));
        khuMatchedRows = [];

        allRows.forEach(row => {
            const kSearch = row.getAttribute('data-search') || '';
            const matchSearch = !searchVal || kSearch.includes(searchVal);
            if (matchSearch) {
                khuMatchedRows.push(row);
            }
        });

        khuCurrentPage = 1;
        renderKhuPagination();
    }

    function renderKhuPagination() {
        const allRows = document.querySelectorAll('.khu-row');
        const total = khuMatchedRows.length;
        const totalPages = khuPageSize > 0 ? Math.ceil(total / khuPageSize) : 1;

        if (khuCurrentPage > totalPages) khuCurrentPage = totalPages;
        if (khuCurrentPage < 1) khuCurrentPage = 1;

        const startIdx = khuPageSize > 0 ? (khuCurrentPage - 1) * khuPageSize : 0;
        const endIdx = khuPageSize > 0 ? startIdx + khuPageSize : total;

        allRows.forEach(row => row.style.display = 'none');
        khuMatchedRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const noMatch = document.getElementById('noMatchKhuRow');
        if (noMatch) noMatch.style.display = (total === 0 && allRows.length > 0) ? '' : 'none';

        const countBadge = document.getElementById('khuCountBadge');
        if (countBadge) countBadge.innerText = total + ' khu';
        const tabBadge = document.getElementById('tabCountKhu');
        if (tabBadge) tabBadge.innerText = total;

        const infoEl = document.getElementById('khuPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = 'Không có khu KTX nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> khu`;
            }
        }

        const controlsEl = document.getElementById('khuPaginationControls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';
        if (totalPages <= 1) return;

        for (let p = 1; p <= totalPages; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === khuCurrentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.borderRadius = '6px';
            btnP.onclick = () => {
                khuCurrentPage = p;
                renderKhuPagination();
            };
            controlsEl.appendChild(btnP);
        }
    }

    function changeKhuPageSize(val) {
        khuPageSize = parseInt(val, 10);
        khuCurrentPage = 1;
        renderKhuPagination();
    }

    function clearKhuFilters() {
        if (document.getElementById('searchKhuInput')) document.getElementById('searchKhuInput').value = '';
        filterKhus();
    }

    // =========================================================================
    // 6. XUẤT VÀ NHẬP CSV (EXPORT & IMPORT MODAL)
    // =========================================================================
    function openImportZoneModal() {
        const modal = document.getElementById('importZoneModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeImportZoneModal() {
        const modal = document.getElementById('importZoneModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function exportZoneData() {
        const matchedIds = [];
        roomMatchedRows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (id) matchedIds.push(id);
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url("staff/zones/export") ?>';
        form.style.display = 'none';

        const csrfInp = document.createElement('input');
        csrfInp.type = 'hidden';
        csrfInp.name = 'csrf_token';
        csrfInp.value = '<?= csrf_token() ?>';
        form.appendChild(csrfInp);

        if (matchedIds.length > 0) {
            const idsInp = document.createElement('input');
            idsInp.type = 'hidden';
            idsInp.name = 'ids';
            idsInp.value = matchedIds.join(',');
            form.appendChild(idsInp);
        } else {
            // Lấy theo bộ lọc
            const cumVal = document.getElementById('filterSelectCum')?.value || '';
            const toaVal = document.getElementById('filterSelectToa')?.value || '';
            const floorVal = document.getElementById('filterRoomFloor')?.value || '';
            const typeVal = document.getElementById('filterRoomType')?.value || '';
            const statusVal = document.getElementById('filterRoomStatus')?.value || '';
            const searchVal = document.getElementById('searchRoomInput')?.value || '';

            if (cumVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'cum';
                inp.value = cumVal;
                form.appendChild(inp);
            }
            if (toaVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'toa';
                inp.value = toaVal;
                form.appendChild(inp);
            }
            if (floorVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'tang';
                inp.value = floorVal;
                form.appendChild(inp);
            }
            if (typeVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'loaiPhong';
                inp.value = typeVal;
                form.appendChild(inp);
            }
            if (statusVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'trangThai';
                inp.value = statusVal;
                form.appendChild(inp);
            }
            if (searchVal) {
                const inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'keyword';
                inp.value = searchVal;
                form.appendChild(inp);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }

    // =========================================================================
    // 7. QUẢN LÝ BIỂU MẪU CRUD PHÒNG Ở (FORM ROOM)
    // =========================================================================
    function onRoomFormCumChange() {
        const cumVal = document.getElementById('roomFormCum')?.value || '';
        const selectToa = document.getElementById('roomFormToa');
        if (!selectToa) return;

        selectToa.innerHTML = '<option value="">-- Chọn Tòa nhà --</option>';
        allToasData.forEach(t => {
            if (!cumVal || t.MaCum === cumVal) {
                const opt = document.createElement('option');
                opt.value = t.MaToaNha;
                opt.textContent = `Tòa ${t.TenToaNha} (${t.TenCum})`;
                selectToa.appendChild(opt);
            }
        });
        autoSuggestRoomCode();
    }

    function autoSuggestRoomCode() {
        const isEdit = document.getElementById('roomIsEdit')?.value === '1';
        if (isEdit) return;

        const toa = document.getElementById('roomFormToa')?.value || '';
        const so = document.getElementById('roomFormSoPhong')?.value || '';
        const inputMa = document.getElementById('roomFormMaPhong');
        const inputTang = document.getElementById('roomFormTang');

        if (toa && so) {
            if (inputMa) inputMa.value = toa + '.' + so;
            if (inputTang && so.length >= 3) {
                const t = parseInt(so.charAt(0), 10);
                if (t > 0) inputTang.value = t;
            }
        }
    }

    function editRoom(btn) {
        const raw = btn.getAttribute('data-item');
        if (!raw) return;
        const room = JSON.parse(raw);

        const card = document.getElementById('cardFormRoom');
        const titleEl = document.getElementById('titleFormRoomText');
        const isEditInp = document.getElementById('roomIsEdit');
        const noticeEdit = document.getElementById('noticeRoomEdit');

        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        if (titleEl) titleEl.innerText = `Chỉnh sửa thông tin Phòng ${room.MaPhong}`;
        if (isEditInp) isEditInp.value = '1';
        if (noticeEdit) noticeEdit.style.display = 'inline-block';

        const form = document.getElementById('formRoom');
        if (!form) return;

        if (form.roomFormCum) form.roomFormCum.value = room.MaCum || '';
        onRoomFormCumChange();
        if (form.roomFormToa) {
            form.roomFormToa.value = room.MaToaNha || '';
            form.roomFormToa.style.pointerEvents = 'none';
            form.roomFormToa.style.backgroundColor = '#f1f5f9';
        }
        if (form.roomFormSoPhong) {
            form.roomFormSoPhong.value = room.SoPhong || '';
            form.roomFormSoPhong.readOnly = true;
        }
        if (form.roomFormMaPhong) {
            form.roomFormMaPhong.value = room.MaPhong || '';
            form.roomFormMaPhong.readOnly = true;
        }
        if (form.roomFormTang) form.roomFormTang.value = room.Tang || 1;
        if (form.roomFormLoaiPhong) form.roomFormLoaiPhong.value = room.MaLoaiPhong || '';

        let stVal = (room.TrangThaiPhong || 'Còn chỗ').trim();
        if (stVal.toLowerCase().includes('trì') || stVal.toLowerCase().includes('khóa')) {
            stVal = 'Bảo trì';
        } else if (stVal.toLowerCase().includes('đầy') || stVal.toLowerCase().includes('day')) {
            stVal = 'Đã đầy';
        } else {
            stVal = 'Còn chỗ';
        }
        if (form.roomFormTrangThai) form.roomFormTrangThai.value = stVal;

        const btnSubmit = document.getElementById('btnSubmitRoom');
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-check" style="margin-right: 6px;"></i> Cập nhật Phòng ở';
    }

    document.getElementById('formRoom')?.addEventListener('submit', function() {
        const toaEl = document.getElementById('roomFormToa');
        if (toaEl) toaEl.disabled = false;
    });

    function resetFormRoom() {
        const form = document.getElementById('formRoom');
        if (form) form.reset();

        const isEditInp = document.getElementById('roomIsEdit');
        const noticeEdit = document.getElementById('noticeRoomEdit');
        const titleEl = document.getElementById('titleFormRoomText');
        const btnSubmit = document.getElementById('btnSubmitRoom');

        if (isEditInp) isEditInp.value = '0';
        if (noticeEdit) noticeEdit.style.display = 'none';
        if (titleEl) titleEl.innerText = 'Thêm mới Phòng ở & Khởi tạo giường tự động';
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu phòng & Sinh giường';

        if (form.roomFormToa) {
            form.roomFormToa.disabled = false;
            form.roomFormToa.style.pointerEvents = 'auto';
            form.roomFormToa.style.backgroundColor = '#fff';
        }
        if (form.roomFormSoPhong) form.roomFormSoPhong.readOnly = false;
        if (form.roomFormMaPhong) form.roomFormMaPhong.readOnly = false;
    }

    function showCreateRoomForm() {
        resetFormRoom();
        const card = document.getElementById('cardFormRoom');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function hideRoomForm() {
        const card = document.getElementById('cardFormRoom');
        if (card) card.style.display = 'none';
    }

    function handleDeleteRoom(btn) {
        const maPhong = btn.getAttribute('data-ma') || '';
        const occupied = parseInt(btn.getAttribute('data-occupied') || '0', 10);

        if (occupied > 0) {
            alert(`Không thể xóa Phòng ${maPhong} vì hiện đang có ${occupied} sinh viên cư trú hợp lệ! Vui lòng thanh lý hoặc dồn phòng trước.`);
            return;
        }

        if (confirm(`Xác nhận xóa vĩnh viễn Phòng ${maPhong} cùng toàn bộ giường trống liên kết?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("staff/zones/delete-room") ?>';
            form.style.display = 'none';

            const csrfInp = document.createElement('input');
            csrfInp.type = 'hidden';
            csrfInp.name = 'csrf_token';
            csrfInp.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInp);

            const maInp = document.createElement('input');
            maInp.type = 'hidden';
            maInp.name = 'maPhong';
            maInp.value = maPhong;
            form.appendChild(maInp);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =========================================================================
    // 8. QUẢN LÝ BIỂU MẪU CRUD TÒA NHÀ (FORM TOA)
    // =========================================================================
    function editToa(btn) {
        const raw = btn.getAttribute('data-item');
        if (!raw) return;
        const toa = JSON.parse(raw);

        const card = document.getElementById('cardFormToa');
        const titleEl = document.getElementById('titleFormToaText');
        const isEditInp = document.getElementById('toaIsEdit');
        const noticeEdit = document.getElementById('noticeToaEdit');

        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        if (titleEl) titleEl.innerText = `Chỉnh sửa thông tin Tòa ${toa.MaToaNha}`;
        if (isEditInp) isEditInp.value = '1';
        if (noticeEdit) noticeEdit.style.display = 'inline-block';

        const form = document.getElementById('formToa');
        if (!form) return;

        if (form.toaFormMaToa) {
            form.toaFormMaToa.value = toa.MaToaNha || '';
            form.toaFormMaToa.readOnly = true;
        }
        if (form.toaFormTenToa) form.toaFormTenToa.value = toa.TenToaNha || '';
        if (form.toaFormMaCum) form.toaFormMaCum.value = toa.MaCum || '';
        if (form.toaFormTongTang) form.toaFormTongTang.value = toa.TongSoTang || 5;
        if (form.toaFormGioiTinh) form.toaFormGioiTinh.value = toa.GioiTinhQuyDinh || 'Nam';
        if (form.toaFormTrangThai) form.toaFormTrangThai.value = toa.TrangThaiToaNha || 'Đang hoạt động';

        const btnSubmit = document.getElementById('btnSubmitToa');
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-check" style="margin-right: 6px;"></i> Cập nhật Tòa nhà';
    }

    function resetFormToa() {
        const form = document.getElementById('formToa');
        if (form) form.reset();

        const isEditInp = document.getElementById('toaIsEdit');
        const noticeEdit = document.getElementById('noticeToaEdit');
        const titleEl = document.getElementById('titleFormToaText');
        const btnSubmit = document.getElementById('btnSubmitToa');

        if (isEditInp) isEditInp.value = '0';
        if (noticeEdit) noticeEdit.style.display = 'none';
        if (titleEl) titleEl.innerText = 'Thêm mới Tòa nhà lưu trú';
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Tòa nhà';

        if (form.toaFormMaToa) form.toaFormMaToa.readOnly = false;
    }

    function showCreateToaForm() {
        resetFormToa();
        const card = document.getElementById('cardFormToa');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function hideToaForm() {
        const card = document.getElementById('cardFormToa');
        if (card) card.style.display = 'none';
    }

    function handleDeleteToa(btn) {
        const maToa = btn.getAttribute('data-ma') || '';
        const rooms = parseInt(btn.getAttribute('data-rooms') || '0', 10);

        if (rooms > 0) {
            alert(`Không thể xóa Tòa nhà ${maToa} vì đang chứa ${rooms} phòng ở trực thuộc!`);
            return;
        }

        if (confirm(`Xác nhận xóa Tòa nhà ${maToa}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("staff/zones/delete-toa") ?>';
            form.style.display = 'none';

            const csrfInp = document.createElement('input');
            csrfInp.type = 'hidden';
            csrfInp.name = 'csrf_token';
            csrfInp.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInp);

            const maInp = document.createElement('input');
            maInp.type = 'hidden';
            maInp.name = 'maToaNha';
            maInp.value = maToa;
            form.appendChild(maInp);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =========================================================================
    // 9. QUẢN LÝ BIỂU MẪU CRUD CỤM NHÀ (FORM CUM)
    // =========================================================================
    function editCum(btn) {
        const raw = btn.getAttribute('data-item');
        if (!raw) return;
        const cum = JSON.parse(raw);

        const card = document.getElementById('cardFormCum');
        const titleEl = document.getElementById('titleFormCumText');
        const isEditInp = document.getElementById('cumIsEdit');
        const noticeEdit = document.getElementById('noticeCumEdit');

        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        if (titleEl) titleEl.innerText = `Chỉnh sửa thông tin Cụm ${cum.MaCum}`;
        if (isEditInp) isEditInp.value = '1';
        if (noticeEdit) noticeEdit.style.display = 'inline-block';

        const form = document.getElementById('formCum');
        if (!form) return;

        if (form.cumFormMaCum) {
            form.cumFormMaCum.value = cum.MaCum || '';
            form.cumFormMaCum.readOnly = true;
        }
        if (form.cumFormTenCum) form.cumFormTenCum.value = cum.TenCum || '';
        if (form.cumFormMaKhu) form.cumFormMaKhu.value = cum.MaKhu || '';
        if (form.cumFormTruongCum) form.cumFormTruongCum.value = cum.HoTenTruongCum || '';
        if (form.cumFormSdt) form.cumFormSdt.value = cum.SoDienThoaiCum || '';

        const btnSubmit = document.getElementById('btnSubmitCum');
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-check" style="margin-right: 6px;"></i> Cập nhật Cụm nhà';
    }

    function resetFormCum() {
        const form = document.getElementById('formCum');
        if (form) form.reset();

        const isEditInp = document.getElementById('cumIsEdit');
        const noticeEdit = document.getElementById('noticeCumEdit');
        const titleEl = document.getElementById('titleFormCumText');
        const btnSubmit = document.getElementById('btnSubmitCum');

        if (isEditInp) isEditInp.value = '0';
        if (noticeEdit) noticeEdit.style.display = 'none';
        if (titleEl) titleEl.innerText = 'Thêm mới Cụm nhà lưu trú';
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Cụm nhà';

        if (form.cumFormMaCum) form.cumFormMaCum.readOnly = false;
    }

    function showCreateCumForm() {
        resetFormCum();
        const card = document.getElementById('cardFormCum');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function hideCumForm() {
        const card = document.getElementById('cardFormCum');
        if (card) card.style.display = 'none';
    }

    function handleDeleteCum(btn) {
        const maCum = btn.getAttribute('data-ma') || '';
        const toas = parseInt(btn.getAttribute('data-toas') || '0', 10);

        if (toas > 0) {
            alert(`Không thể xóa Cụm ${maCum} vì đang chứa ${toas} tòa nhà trực thuộc!`);
            return;
        }

        if (confirm(`Xác nhận xóa Cụm nhà ${maCum}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("staff/zones/delete-cum") ?>';
            form.style.display = 'none';

            const csrfInp = document.createElement('input');
            csrfInp.type = 'hidden';
            csrfInp.name = 'csrf_token';
            csrfInp.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInp);

            const maInp = document.createElement('input');
            maInp.type = 'hidden';
            maInp.name = 'maCum';
            maInp.value = maCum;
            form.appendChild(maInp);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =========================================================================
    // 10. QUẢN LÝ BIỂU MẪU CRUD KHU KTX (FORM KHU)
    // =========================================================================
    function editKhu(btn) {
        const raw = btn.getAttribute('data-item');
        if (!raw) return;
        const khu = JSON.parse(raw);

        const card = document.getElementById('cardFormKhu');
        const titleEl = document.getElementById('titleFormKhuText');
        const isEditInp = document.getElementById('khuIsEdit');
        const noticeEdit = document.getElementById('noticeKhuEdit');

        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        if (titleEl) titleEl.innerText = `Chỉnh sửa thông tin Khu ${khu.MaKhu}`;
        if (isEditInp) isEditInp.value = '1';
        if (noticeEdit) noticeEdit.style.display = 'inline-block';

        const form = document.getElementById('formKhu');
        if (!form) return;

        if (form.khuFormMaKhu) {
            form.khuFormMaKhu.value = khu.MaKhu || '';
            form.khuFormMaKhu.readOnly = true;
        }
        if (form.khuFormTenKhu) form.khuFormTenKhu.value = khu.TenKhu || '';
        if (form.khuFormDiaChi) form.khuFormDiaChi.value = khu.DiaChi || '';

        const btnSubmit = document.getElementById('btnSubmitKhu');
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-check" style="margin-right: 6px;"></i> Cập nhật Khu KTX';
    }

    function resetFormKhu() {
        const form = document.getElementById('formKhu');
        if (form) form.reset();

        const isEditInp = document.getElementById('khuIsEdit');
        const noticeEdit = document.getElementById('noticeKhuEdit');
        const titleEl = document.getElementById('titleFormKhuText');
        const btnSubmit = document.getElementById('btnSubmitKhu');

        if (isEditInp) isEditInp.value = '0';
        if (noticeEdit) noticeEdit.style.display = 'none';
        if (titleEl) titleEl.innerText = 'Thêm mới Khu KTX';
        if (btnSubmit) btnSubmit.innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Khu KTX';

        if (form.khuFormMaKhu) form.khuFormMaKhu.readOnly = false;
    }

    function showCreateKhuForm() {
        resetFormKhu();
        const card = document.getElementById('cardFormKhu');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function hideKhuForm() {
        const card = document.getElementById('cardFormKhu');
        if (card) card.style.display = 'none';
    }

    function handleDeleteKhu(btn) {
        const maKhu = btn.getAttribute('data-ma') || '';
        const cums = parseInt(btn.getAttribute('data-cums') || '0', 10);

        if (cums > 0) {
            alert(`Không thể xóa Khu ${maKhu} vì đang có ${cums} Cụm nhà trực thuộc!`);
            return;
        }

        if (confirm(`Xác nhận xóa Khu KTX ${maKhu}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("staff/zones/delete-khu") ?>';
            form.style.display = 'none';

            const csrfInp = document.createElement('input');
            csrfInp.type = 'hidden';
            csrfInp.name = 'csrf_token';
            csrfInp.value = '<?= csrf_token() ?>';
            form.appendChild(csrfInp);

            const maInp = document.createElement('input');
            maInp.type = 'hidden';
            maInp.name = 'maKhu';
            maInp.value = maKhu;
            form.appendChild(maInp);

            document.body.appendChild(form);
            form.submit();
        }
    }

    // =========================================================================
    // KHỞI TẠO KHI TẢI TRANG
    // =========================================================================
    document.addEventListener('DOMContentLoaded', function() {
        const initialTab = '<?= $scope["defaultTab"] ?? "phong" ?>';
        switchZoneTab(initialTab);

        // Khởi tạo phân trang cho toàn bộ các tab
        filterRooms();
        filterToas();
        filterCums();
        filterKhus();
    });
</script>

<?php
$content = ob_get_clean();
$title = "Quản lý Hạ tầng KTX (Khu, Cụm, Tòa & Phòng) - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>