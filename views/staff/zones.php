<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Hạ tầng KTX &bull; Khu, Cụm, Tòa & Phòng ở</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 class="page-title">Quản lý Hạ tầng KTX: Khu, Cụm, Tòa & Phòng ở</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ quản trị') ?>
                    </span>
                    <span style="font-size: 0.8rem; color: var(--text-muted);">&bull; Cấu hình và tra cứu trực tiếp toàn bộ dữ liệu</span>
                </p>
            </div>
            <div>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    <i class="fa-solid fa-arrow-left" style="margin-right: 6px;"></i> Về Dashboard
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

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <b>Cảnh báo:</b> <?= htmlspecialchars($flashWarning) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- ===================================================================== -->
    <!-- BỘ LỌC TRA CỨU PHÂN CẤP HẠ TẦNG (CỤM -> TÒA -> PHÒNG) -->
    <!-- ===================================================================== -->
    <div class="card" style="margin-bottom: 22px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px 20px;">
        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sitemap" style="color: var(--primary);"></i>
                <span>Bộ lọc Tra cứu Phân cấp Hạ tầng (Chọn từ trên xuống: Mã Cụm &rarr; Mã Tòa &rarr; Mã Phòng)</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span id="filterSummaryBadge" class="badge badge-info" style="font-size: 0.775rem;">Đang hiển thị tất cả</span>
            </div>
        </div>

        <div class="grid grid-cols-4" style="gap: 12px; align-items: flex-end;">
            <!-- CẤP 1: MÃ CỤM NHÀ -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    1. Mã Cụm nhà:
                </label>
                <select id="filterSelectCum" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="onCascadeCumChange()">
                    <option value="">-- Tất cả Cụm (để trống) --</option>
                    <?php foreach ($cumList as $c): ?>
                        <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']) ? 'selected' : '' ?>>
                            Cụm <?= htmlspecialchars($c['TenCum']) ?> (<?= htmlspecialchars($c['MaCum']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CẤP 2: MÃ TÒA NHÀ (LỌC THEO CỤM TỪ TRÊN XUỐNG) -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    2. Mã Tòa nhà:
                </label>
                <select id="filterSelectToa" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="onCascadeToaChange()">
                    <option value="">-- Tất cả Tòa (để trống) --</option>
                    <?php foreach ($toaList as $t): ?>
                        <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum']) ?>">
                            <?= htmlspecialchars($t['TenToaNha']) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- CẤP 3: MÃ PHÒNG Ở (LỌC THEO TÒA TỪ TRÊN XUỐNG) -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    3. Mã Phòng ở:
                </label>
                <select id="filterSelectPhong" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="onCascadePhongChange()">
                    <option value="">-- Tất cả Phòng (để trống) --</option>
                    <?php foreach ($phongList as $p): ?>
                        <option value="<?= htmlspecialchars($p['MaPhong']) ?>" data-toa="<?= htmlspecialchars($p['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($p['MaCum']) ?>">
                            Phòng <?= htmlspecialchars($p['SoPhong']) ?> (<?= htmlspecialchars($p['MaPhong']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- NÚT XÓA LỌC -->
            <div>
                <button type="button" class="btn btn-outline btn-block" onclick="clearHierarchyFilter()" style="border-radius: 6px; height: 38px; color: #dc2626; border-color: #fecaca; background: #fff; display: flex; align-items: center; justify-content: center; gap: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                </button>
            </div>
        </div>
    </div>

    <!-- TABS ĐIỀU HƯỚNG SUB-TABS -->
    <div style="display: flex; gap: 12px; margin-bottom: 22px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px; flex-wrap: wrap;">
        <button type="button" class="tab-btn active" id="tabBtnPhong" onclick="switchZoneTab('phong')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 700; cursor: pointer; border-radius: 8px; color: var(--primary); background: #eff6ff;">
            <i class="fa-solid fa-door-open" style="margin-right: 6px;"></i> Danh mục Phòng ở (<span id="tabCountPhong"><?= count($phongList ?? []) ?></span>)
        </button>
        <button type="button" class="tab-btn" id="tabBtnToa" onclick="switchZoneTab('toa')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-building" style="margin-right: 6px;"></i> Danh mục Tòa nhà (<span id="tabCountToa"><?= count($toaList ?? []) ?></span>)
        </button>
        <button type="button" class="tab-btn" id="tabBtnCum" onclick="switchZoneTab('cum')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-sitemap" style="margin-right: 6px;"></i> Danh mục Cụm nhà (<span id="tabCountCum"><?= count($cumList ?? []) ?></span>)
        </button>
        <button type="button" class="tab-btn" id="tabBtnKhu" onclick="switchZoneTab('khu')" style="background:none; border:none; padding: 10px 18px; font-size: 0.95rem; font-weight: 600; cursor: pointer; border-radius: 8px; color: var(--text-secondary);">
            <i class="fa-solid fa-layer-group" style="margin-right: 6px;"></i> Danh mục Khu KTX (<?= count($khuList ?? []) ?>)
        </button>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 1: DANH MỤC PHÒNG Ở & FORM THÊM / SỬA PHÒNG -->
    <!-- ===================================================================== -->
    <div id="tabContentPhong" style="display: block;">
        <!-- FORM THÊM / SỬA PHÒNG Ở (CÓ SELECT PHÂN CẤP CỤM -> TÒA) -->
        <div class="card" id="cardFormRoom" style="margin-bottom: 24px;">
            <div class="card-title" id="titleFormRoom">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                <span>Thêm mới Phòng ở & Khởi tạo giường tự động</span>
            </div>

            <form action="<?= url('staff/zones/save-room') ?>" method="POST" id="formRoom">
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
                                    <?= htmlspecialchars($lp['TenLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường)
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
                            <option value="Đang sử dụng">Đang sử dụng</option>
                            <option value="Đầy">Đầy</option>
                            <option value="Bảo trì">Bảo trì</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btnSubmitRoom" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu phòng & Sinh giường
                    </button>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormRoom()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới Form
                    </button>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH PHÒNG Ở -->
        <div class="card">
            <div class="card-title" style="flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div>
                    <span>Danh sách Phòng ở & Sơ đồ giường</span>
                    <span class="badge badge-info" id="roomCountBadge"><?= count($phongList ?? []) ?> phòng</span>
                </div>
                <div style="font-size: 0.85rem; color: var(--text-muted);">
                    <i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Dữ liệu tự động lọc theo bộ chọn phân cấp Cụm &rarr; Tòa &rarr; Phòng ở trên.
                </div>
            </div>

            <?php if (!empty($phongList)): ?>
                <div class="table-responsive">
                    <table class="data-table">
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
                        <tbody>
                            <?php foreach ($phongList as $p): ?>
                                <?php
                                $maxCap = (int)($p['SoGiuongToiDa'] ?? 4);
                                $occupied = (int)($p['SoSVDangO'] ?? 0);
                                $vacant = (int)($p['SoGiuongTrong'] ?? 0);
                                $percent = $maxCap > 0 ? min(100, round(($occupied / $maxCap) * 100)) : 0;
                                $beds = $giuongByPhong[$p['MaPhong']] ?? [];
                                ?>
                                <tr class="room-row"
                                    data-cum="<?= htmlspecialchars($p['MaCum']) ?>"
                                    data-toa="<?= htmlspecialchars($p['MaToaNha']) ?>"
                                    data-phong="<?= htmlspecialchars($p['MaPhong']) ?>">
                                    <td><b style="color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($p['MaPhong']) ?></b></td>
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
                                            <div style="width: <?= $percent ?>%; height: 100%; background: <?= $percent >= 100 ? '#dc2626' : '#2563eb' ?>;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="display: flex; gap: 4px; flex-wrap: wrap; max-width: 160px;">
                                            <?php foreach ($beds as $bg): ?>
                                                <?php
                                                $bgClass = 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;';
                                                $icon = 'fa-regular fa-circle';
                                                if (str_contains($bg['TrangThaiGiuong'], 'ở') || str_contains($bg['TrangThaiGiuong'], 'người')) {
                                                    $bgClass = 'background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd;';
                                                    $icon = 'fa-solid fa-user';
                                                } elseif ($bg['TrangThaiGiuong'] === 'Bảo trì') {
                                                    $bgClass = 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;';
                                                    $icon = 'fa-solid fa-wrench';
                                                }
                                                ?>
                                                <span title="<?= htmlspecialchars($bg['KyHieuGiuong']) ?>: <?= htmlspecialchars($bg['TrangThaiGiuong']) ?>"
                                                    style="display: inline-flex; align-items: center; gap: 2px; font-size: 0.675rem; padding: 2px 4px; border-radius: 4px; font-weight: 600; <?= $bgClass ?>">
                                                    <i class="<?= $icon ?>" style="font-size: 0.6rem;"></i> <?= htmlspecialchars($bg['KyHieuGiuong']) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($p['TrangThaiPhong'] === 'Bảo trì'): ?>
                                            <span class="badge badge-warning">Bảo trì</span>
                                        <?php elseif ($vacant <= 0): ?>
                                            <span class="badge badge-danger">Đầy</span>
                                        <?php else: ?>
                                            <span class="badge badge-success">Còn <?= $vacant ?> chỗ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                            onclick='editRoom(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            title="Sửa phòng ở">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                            data-ma="<?= htmlspecialchars($p['MaPhong'], ENT_QUOTES) ?>"
                                            data-occupied="<?= $occupied ?>"
                                            onclick="handleDeleteRoom(this)"
                                            title="Xóa phòng ở">
                                            <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchPhongRow" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Phòng ở nào khớp với lựa chọn phân cấp hiện tại.
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
        <!-- FORM THÊM / SỬA TÒA NHÀ -->
        <div class="card" id="cardFormToa" style="margin-bottom: 24px;">
            <div class="card-title" id="titleFormToa">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                <span>Thêm mới Tòa nhà lưu trú</span>
            </div>

            <form action="<?= url('staff/zones/save-toa') ?>" method="POST" id="formToa">
                <input type="hidden" name="isEdit" id="toaIsEdit" value="0">

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã tòa nhà <span style="color:#dc2626;">*</span>
                            <span id="noticeToaEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="toaMaToaNha" name="maToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: A01, A02, BA1..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên hiển thị Tòa nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="toaTenToaNha" name="tenToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Tòa A01, Tòa B5..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Cụm nhà trực thuộc <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaMaCum" name="maCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn Cụm nhà --</option>
                            <?php foreach ($cumList as $c): ?>
                                <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($scope['level'] === 'CUM' && $scope['maCum'] === $c['MaCum']) ? 'selected' : '' ?>>
                                    Cụm <?= htmlspecialchars($c['TenCum']) ?> (Khu <?= htmlspecialchars($c['MaKhu']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tổng số tầng <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="number" id="toaTongSoTang" name="tongSoTang" min="1" max="50" value="5" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Quy định giới tính lưu trú <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaGioiTinh" name="gioiTinhQuyDinh" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Nam">Nam</option>
                            <option value="Nữ">Nữ</option>
                            <option value="Hỗn hợp theo tầng">Hỗn hợp theo tầng</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Trạng thái vận hành <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="toaTrangThai" name="trangThaiToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Đang hoạt động">Đang hoạt động</option>
                            <option value="Tạm đóng bảo trì">Tạm đóng bảo trì</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btnSubmitToa" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Tòa nhà
                    </button>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormToa()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới Form
                    </button>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH TÒA NHÀ TRONG PHẠM VI -->
        <div class="card">
            <div class="card-title" style="flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div>
                    <span>Danh sách Tòa nhà lưu trú</span>
                    <span class="badge badge-info" id="toaCountBadge"><?= count($toaList ?? []) ?> tòa</span>
                </div>
            </div>

            <?php if (!empty($toaList)): ?>
                <div class="table-responsive">
                    <table class="data-table">
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
                        <tbody>
                            <?php foreach ($toaList as $t): ?>
                                <tr class="toa-row" data-cum="<?= htmlspecialchars($t['MaCum']) ?>" data-toa="<?= htmlspecialchars($t['MaToaNha']) ?>">
                                    <td><b style="color: var(--primary);"><?= htmlspecialchars($t['MaToaNha']) ?></b></td>
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
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                            onclick='editToa(<?= json_encode($t, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            title="Sửa thông tin tòa">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                            data-ma="<?= htmlspecialchars($t['MaToaNha'], ENT_QUOTES) ?>"
                                            data-ten="<?= htmlspecialchars($t['TenToaNha'], ENT_QUOTES) ?>"
                                            onclick="handleDeleteToa(this)"
                                            title="Xóa tòa nhà">
                                            <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchToaRow" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Tòa nhà nào phù hợp với bộ lọc.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có Tòa nhà nào trong phạm vi quản lý của bạn.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 3: DANH MỤC CỤM NHÀ & FORM THÊM / SỬA CỤM NHÀ -->
    <!-- ===================================================================== -->
    <div id="tabContentCum" style="display: none;">
        <!-- FORM CỤM NHÀ -->
        <div class="card" id="cardFormCum" style="margin-bottom: 24px;">
            <div class="card-title" id="titleFormCum">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                <span>Thêm mới / Cập nhật Cụm nhà</span>
            </div>

            <form action="<?= url('staff/zones/save-cum') ?>" method="POST" id="formCum">
                <input type="hidden" name="isEdit" id="cumIsEdit" value="0">

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã cụm nhà <span style="color:#dc2626;">*</span>
                            <span id="noticeCumEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="cumMaCum" name="maCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: AG, AH, BA, BB..." required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Khu KTX trực thuộc <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="cumMaKhu" name="maKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn Khu KTX --</option>
                            <?php foreach ($khuList as $k): ?>
                                <option value="<?= htmlspecialchars($k['MaKhu']) ?>">
                                    Khu <?= htmlspecialchars($k['MaKhu']) ?> - <?= htmlspecialchars($k['TenKhu']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên cụm nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="cumTenCum" name="tenCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Cụm nhà A.G, Cụm nhà B.A..." required>
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Vị trí Văn phòng BQL Cụm
                        </label>
                        <input type="text" id="cumViTriVanPhong" name="viTriVanPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Tầng trệt Tòa A01">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Trưởng Ban quản lý cụm
                        </label>
                        <select id="cumSelectTruongCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" onchange="onSelectCumLeader(this)">
                            <option value="">-- Chọn cán bộ có sẵn --</option>
                            <?php foreach ($truongCumList as $tc): ?>
                                <option value="<?= htmlspecialchars($tc['HoVaTen']) ?>" data-phone="<?= htmlspecialchars($tc['SoDienThoai'] ?? '') ?>">
                                    <?= htmlspecialchars($tc['HoVaTen']) ?> (<?= htmlspecialchars($tc['SoDienThoai'] ?? '') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="cumHoTenTruongCum" name="hoTenTruongCum" value="">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Số điện thoại liên hệ Cụm
                        </label>
                        <input type="text" id="cumSoDienThoaiCum" name="soDienThoaiCum" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 02837240001">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btnSubmitCum" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Cụm nhà
                    </button>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormCum()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới Form
                    </button>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH CỤM NHÀ -->
        <div class="card">
            <div class="card-title" style="margin-bottom: 16px;">
                <span>Danh mục Cụm nhà phụ trách</span>
                <span class="badge badge-info" id="cumCountBadge"><?= count($cumList ?? []) ?> cụm</span>
            </div>

            <?php if (!empty($cumList)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã cụm</th>
                                <th>Tên Cụm nhà</th>
                                <th>Khu KTX</th>
                                <th>Văn phòng BQL</th>
                                <th>Trưởng Ban quản lý</th>
                                <th>Quy mô phụ trách</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cumList as $c): ?>
                                <tr class="cum-row" data-cum="<?= htmlspecialchars($c['MaCum']) ?>">
                                    <td><b style="color: var(--primary);"><?= htmlspecialchars($c['MaCum']) ?></b></td>
                                    <td><b><?= htmlspecialchars($c['TenCum']) ?></b></td>
                                    <td><span class="badge badge-info" style="font-size: 0.75rem;">Khu <?= htmlspecialchars($c['MaKhu']) ?></span></td>
                                    <td><?= htmlspecialchars($c['ViTriVanPhong'] ?? 'Chưa cập nhật') ?></td>
                                    <td>
                                        <div><b><?= htmlspecialchars($c['HoTenTruongCum'] ?? 'Chưa phân công') ?></b></div>
                                        <div style="font-size: 0.775rem; color: var(--text-muted);"><?= htmlspecialchars($c['SoDienThoaiCum'] ?? '') ?></div>
                                    </td>
                                    <td>
                                        <div><b><?= (int)($c['TongToa'] ?? 0) ?></b> tòa nhà &bull; <b><?= (int)($c['TongPhong'] ?? 0) ?></b> phòng</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= (int)($c['TongGiuong'] ?? 0) ?> giường</div>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                            onclick='editCum(<?= json_encode($c, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            title="Sửa thông tin cụm">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                            data-ma="<?= htmlspecialchars($c['MaCum'], ENT_QUOTES) ?>"
                                            data-ten="<?= htmlspecialchars($c['TenCum'], ENT_QUOTES) ?>"
                                            onclick="handleDeleteCum(this)"
                                            title="Xóa cụm nhà">
                                            <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <tr id="noMatchCumRow" style="display: none;">
                                <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                                    <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy Cụm nhà nào phù hợp với bộ lọc.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu cụm nhà trong phạm vi.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- ===================================================================== -->
    <!-- TAB 4: DANH MỤC KHU KTX & FORM KHU KTX -->
    <!-- ===================================================================== -->
    <div id="tabContentKhu" style="display: none;">
        <div class="card" id="cardFormKhu" style="margin-bottom: 24px;">
            <div class="card-title" id="titleFormKhu">
                <i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i>
                <span>Cấu hình Khu KTX</span>
            </div>

            <form action="<?= url('staff/zones/save-khu') ?>" method="POST" id="formKhu">
                <input type="hidden" name="isEdit" id="khuIsEdit" value="0">

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã khu KTX <span style="color:#dc2626;">*</span>
                            <span id="noticeKhuEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                                <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                            </span>
                        </label>
                        <input type="text" id="khuMaKhu" name="maKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: A, B, C..." required maxlength="10">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tên gọi Khu KTX <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="khuTenKhu" name="tenKhu" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Ký túc xá Khu A, Ký túc xá Khu B..." required>
                    </div>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Địa chỉ Khu KTX
                        </label>
                        <input type="text" id="khuDiaChi" name="diaChi" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Phường Linh Trung, TP. Thủ Đức, TP.HCM">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Số điện thoại trực bảo vệ / Trực ban
                        </label>
                        <input type="text" id="khuSoDienThoaiTruc" name="soDienThoaiTruc" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 02837242265">
                    </div>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btnSubmitKhu" class="btn btn-primary" style="border-radius: 6px; padding: 10px 18px;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Khu KTX
                    </button>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetFormKhu()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới Form
                    </button>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH KHU KTX -->
        <div class="card">
            <div class="card-title" style="margin-bottom: 16px;">
                <span>Danh sách Khu KTX ĐHQG-HCM</span>
                <span class="badge badge-info"><?= count($khuList ?? []) ?> khu</span>
            </div>

            <?php if (!empty($khuList)): ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã khu</th>
                                <th>Tên Khu KTX</th>
                                <th>Địa chỉ</th>
                                <th>Hotline trực ban</th>
                                <th>Quy mô trực thuộc</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($khuList as $k): ?>
                                <tr>
                                    <td><b style="color: var(--primary); font-size: 1.05rem;">Khu <?= htmlspecialchars($k['MaKhu']) ?></b></td>
                                    <td><b><?= htmlspecialchars($k['TenKhu']) ?></b></td>
                                    <td><?= htmlspecialchars($k['DiaChi'] ?? 'Chưa cập nhật') ?></td>
                                    <td><?= htmlspecialchars($k['SoDienThoaiTruc'] ?? 'Chưa cập nhật') ?></td>
                                    <td>
                                        <div><b><?= (int)($k['TongCum'] ?? 0) ?></b> cụm &bull; <b><?= (int)($k['TongToa'] ?? 0) ?></b> tòa</div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= (int)($k['TongPhong'] ?? 0) ?> phòng ở</div>
                                    </td>
                                    <td style="text-align: right; white-space: nowrap;">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px;"
                                            onclick='editKhu(<?= json_encode($k, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            title="Sửa thông tin khu">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                        </button>
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                            data-ma="<?= htmlspecialchars($k['MaKhu'], ENT_QUOTES) ?>"
                                            data-ten="<?= htmlspecialchars($k['TenKhu'], ENT_QUOTES) ?>"
                                            onclick="handleDeleteKhu(this)"
                                            title="Xóa khu KTX">
                                            <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted" style="padding: 20px 0; font-size: 0.9rem;">Chưa có dữ liệu Khu KTX.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- JAVASCRIPT XỬ LÝ PHÂN CẤP TỪ TRÊN XUỐNG & TƯƠNG TÁC GIAO DIỆN -->
<!-- ===================================================================== -->
<script>
    // Dữ liệu nguồn từ máy chủ
    const allClusters = <?= json_encode($cumList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const allBuildings = <?= json_encode($toaList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const allRooms = <?= json_encode($phongList ?? [], JSON_UNESCAPED_UNICODE) ?>;

    // 1. Chuyển đổi giữa các Sub-tabs
    function switchZoneTab(tab) {
        const tabs = ['phong', 'toa', 'cum', 'khu'];
        tabs.forEach(t => {
            const content = document.getElementById('tabContent' + t.charAt(0).toUpperCase() + t.slice(1));
            const btn = document.getElementById('tabBtn' + t.charAt(0).toUpperCase() + t.slice(1));
            if (content && btn) {
                if (t === tab) {
                    content.style.display = 'block';
                    btn.classList.add('active');
                    btn.style.color = 'var(--primary)';
                    btn.style.background = '#eff6ff';
                    btn.style.fontWeight = '700';
                } else {
                    content.style.display = 'none';
                    btn.classList.remove('active');
                    btn.style.color = 'var(--text-secondary)';
                    btn.style.background = 'none';
                    btn.style.fontWeight = '600';
                }
            }
        });
    }

    // =========================================================================
    // 2. LOGIC BỘ LỌC PHÂN CẤP TỪ TRÊN XUỐNG: CỤM -> TÒA -> PHÒNG
    // =========================================================================

    // Khi thay đổi Cụm ở bộ lọc
    function onCascadeCumChange() {
        const selectedCum = document.getElementById('filterSelectCum').value;
        const selectToa = document.getElementById('filterSelectToa');
        const selectPhong = document.getElementById('filterSelectPhong');

        // Render lại danh sách Tòa nhà theo Cụm đã chọn
        selectToa.innerHTML = '<option value="">-- Tất cả Tòa (để trống) --</option>';
        const filteredToa = selectedCum ?
            allBuildings.filter(b => b.MaCum === selectedCum) :
            allBuildings;

        filteredToa.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.MaToaNha;
            opt.textContent = `${b.TenToaNha} (${b.MaToaNha})`;
            opt.setAttribute('data-cum', b.MaCum);
            selectToa.appendChild(opt);
        });
        selectToa.value = '';

        // Render lại danh sách Phòng theo Cụm đã chọn
        selectPhong.innerHTML = '<option value="">-- Tất cả Phòng (để trống) --</option>';
        const filteredPhong = selectedCum ?
            allRooms.filter(r => r.MaCum === selectedCum) :
            allRooms;

        filteredPhong.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.MaPhong;
            opt.textContent = `Phòng ${r.SoPhong} (${r.MaPhong})`;
            opt.setAttribute('data-toa', r.MaToaNha);
            opt.setAttribute('data-cum', r.MaCum);
            selectPhong.appendChild(opt);
        });
        selectPhong.value = '';

        applyHierarchyFilters();
    }

    // Khi thay đổi Tòa ở bộ lọc
    function onCascadeToaChange() {
        const selectCum = document.getElementById('filterSelectCum');
        const selectedToa = document.getElementById('filterSelectToa').value;
        const selectPhong = document.getElementById('filterSelectPhong');

        // Tự động đồng bộ Cụm cha nếu Cụm chưa được chọn
        if (selectedToa && !selectCum.value) {
            const b = allBuildings.find(item => item.MaToaNha === selectedToa);
            if (b && b.MaCum) {
                selectCum.value = b.MaCum;
            }
        }

        // Render lại danh sách Phòng theo Tòa đã chọn
        selectPhong.innerHTML = '<option value="">-- Tất cả Phòng (để trống) --</option>';
        const filteredPhong = selectedToa ?
            allRooms.filter(r => r.MaToaNha === selectedToa) :
            (selectCum.value ? allRooms.filter(r => r.MaCum === selectCum.value) : allRooms);

        filteredPhong.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.MaPhong;
            opt.textContent = `Phòng ${r.SoPhong} (${r.MaPhong})`;
            opt.setAttribute('data-toa', r.MaToaNha);
            opt.setAttribute('data-cum', r.MaCum);
            selectPhong.appendChild(opt);
        });
        selectPhong.value = '';

        applyHierarchyFilters();
    }

    // Khi thay đổi Phòng ở bộ lọc
    function onCascadePhongChange() {
        const selectedPhong = document.getElementById('filterSelectPhong').value;
        const selectToa = document.getElementById('filterSelectToa');
        const selectCum = document.getElementById('filterSelectCum');

        // Nếu chọn phòng, tự động đồng bộ Cụm và Tòa nếu đang để trống
        if (selectedPhong) {
            const r = allRooms.find(item => item.MaPhong === selectedPhong);
            if (r) {
                if (!selectToa.value && r.MaToaNha) selectToa.value = r.MaToaNha;
                if (!selectCum.value && r.MaCum) selectCum.value = r.MaCum;
            }
        }

        applyHierarchyFilters();
    }

    // Xóa bộ lọc phân cấp (Reset về ban đầu)
    function clearHierarchyFilter() {
        const selectCum = document.getElementById('filterSelectCum');
        const selectToa = document.getElementById('filterSelectToa');
        const selectPhong = document.getElementById('filterSelectPhong');

        if (selectCum) selectCum.value = '';

        // Khôi phục tất cả Tòa
        if (selectToa) {
            selectToa.innerHTML = '<option value="">-- Tất cả Tòa (để trống) --</option>';
            allBuildings.forEach(b => {
                const opt = document.createElement('option');
                opt.value = b.MaToaNha;
                opt.textContent = `${b.TenToaNha} (${b.MaToaNha})`;
                opt.setAttribute('data-cum', b.MaCum);
                selectToa.appendChild(opt);
            });
            selectToa.value = '';
        }

        // Khôi phục tất cả Phòng
        if (selectPhong) {
            selectPhong.innerHTML = '<option value="">-- Tất cả Phòng (để trống) --</option>';
            allRooms.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.MaPhong;
                opt.textContent = `Phòng ${r.SoPhong} (${r.MaPhong})`;
                opt.setAttribute('data-toa', r.MaToaNha);
                opt.setAttribute('data-cum', r.MaCum);
                selectPhong.appendChild(opt);
            });
            selectPhong.value = '';
        }

        applyHierarchyFilters();
    }

    // Áp dụng bộ lọc phân cấp lên các bảng dữ liệu
    function applyHierarchyFilters() {
        const cumVal = document.getElementById('filterSelectCum') ? document.getElementById('filterSelectCum').value : '';
        const toaVal = document.getElementById('filterSelectToa') ? document.getElementById('filterSelectToa').value : '';
        const phongVal = document.getElementById('filterSelectPhong') ? document.getElementById('filterSelectPhong').value : '';

        // 1. Cập nhật Badge tóm tắt
        const badge = document.getElementById('filterSummaryBadge');
        if (badge) {
            if (phongVal) {
                badge.innerText = `Đang lọc: Phòng ${phongVal}`;
                badge.className = 'badge badge-success';
            } else if (toaVal) {
                badge.innerText = `Đang lọc: Tòa ${toaVal} (Tất cả phòng)`;
                badge.className = 'badge badge-primary';
            } else if (cumVal) {
                badge.innerText = `Đang lọc: Cụm ${cumVal} (Tất cả tòa & phòng)`;
                badge.className = 'badge badge-warning';
            } else {
                badge.innerText = 'Đang hiển thị tất cả';
                badge.className = 'badge badge-info';
            }
        }

        // 2. Lọc bảng Phòng
        const roomRows = document.querySelectorAll('.room-row');
        let visibleRooms = 0;
        roomRows.forEach(row => {
            const rCum = row.getAttribute('data-cum') || '';
            const rToa = row.getAttribute('data-toa') || '';
            const rPhong = row.getAttribute('data-phong') || '';

            const matchCum = !cumVal || rCum === cumVal;
            const matchToa = !toaVal || rToa === toaVal;
            const matchPhong = !phongVal || rPhong === phongVal;

            if (matchCum && matchToa && matchPhong) {
                row.style.display = '';
                visibleRooms++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatchPhong = document.getElementById('noMatchPhongRow');
        if (noMatchPhong) noMatchPhong.style.display = (visibleRooms === 0 && roomRows.length > 0) ? '' : 'none';
        const roomBadge = document.getElementById('roomCountBadge');
        if (roomBadge) roomBadge.innerText = visibleRooms + ' phòng';

        // 3. Lọc bảng Tòa
        const toaRows = document.querySelectorAll('.toa-row');
        let visibleToas = 0;
        toaRows.forEach(row => {
            const tCum = row.getAttribute('data-cum') || '';
            const tToa = row.getAttribute('data-toa') || '';

            const matchCum = !cumVal || tCum === cumVal;
            const matchToa = !toaVal || tToa === toaVal;

            if (matchCum && matchToa) {
                row.style.display = '';
                visibleToas++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatchToa = document.getElementById('noMatchToaRow');
        if (noMatchToa) noMatchToa.style.display = (visibleToas === 0 && toaRows.length > 0) ? '' : 'none';
        const toaBadge = document.getElementById('toaCountBadge');
        if (toaBadge) toaBadge.innerText = visibleToas + ' tòa';

        // 4. Lọc bảng Cụm
        const cumRows = document.querySelectorAll('.cum-row');
        let visibleCums = 0;
        cumRows.forEach(row => {
            const cCum = row.getAttribute('data-cum') || '';
            const matchCum = !cumVal || cCum === cumVal;

            if (matchCum) {
                row.style.display = '';
                visibleCums++;
            } else {
                row.style.display = 'none';
            }
        });
        const noMatchCum = document.getElementById('noMatchCumRow');
        if (noMatchCum) noMatchCum.style.display = (visibleCums === 0 && cumRows.length > 0) ? '' : 'none';
        const cumBadge = document.getElementById('cumCountBadge');
        if (cumBadge) cumBadge.innerText = visibleCums + ' cụm';
    }

    // =========================================================================
    // 3. LOGIC FORM PHÒNG Ở (CÓ SELECT PHÂN CẤP CỤM -> TÒA -> PHÒNG)
    // =========================================================================

    // Khi chọn Cụm trong Form Thêm/Sửa Phòng, render lại các Tòa tương ứng
    function onRoomFormCumChange() {
        const cum = document.getElementById('roomFormCum').value;
        const selectToa = document.getElementById('roomFormToa');
        selectToa.innerHTML = '<option value="">-- Chọn Tòa nhà --</option>';

        const filtered = cum ?
            allBuildings.filter(b => b.MaCum === cum) :
            allBuildings;

        filtered.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.MaToaNha;
            opt.textContent = `Tòa ${b.TenToaNha} (${b.TenCum || b.MaCum})`;
            opt.setAttribute('data-cum', b.MaCum);
            selectToa.appendChild(opt);
        });

        autoSuggestRoomCode();
    }

    function autoSuggestRoomCode() {
        const isEdit = document.getElementById('roomIsEdit').value === '1';
        if (isEdit) return;

        const maToa = document.getElementById('roomFormToa').value;
        const soPhong = document.getElementById('roomFormSoPhong').value.trim();
        const inputMaPhong = document.getElementById('roomFormMaPhong');
        const inputTang = document.getElementById('roomFormTang');

        if (maToa && soPhong) {
            inputMaPhong.value = maToa + '.' + soPhong;
        }

        if (soPhong && soPhong.length >= 3) {
            const firstDigit = parseInt(soPhong.charAt(0));
            if (!isNaN(firstDigit) && firstDigit > 0) {
                inputTang.value = firstDigit;
            }
        }
    }

    function editRoom(room) {
        document.getElementById('roomIsEdit').value = '1';
        document.getElementById('titleFormRoom').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i> <span>Chỉnh sửa phòng ở: ' + room.MaPhong + '</span>';

        const inputMa = document.getElementById('roomFormMaPhong');
        inputMa.value = room.MaPhong;
        inputMa.readOnly = true;
        inputMa.style.backgroundColor = '#f1f5f9';
        inputMa.style.cursor = 'not-allowed';
        document.getElementById('noticeRoomEdit').style.display = 'inline';

        document.getElementById('roomFormCum').value = room.MaCum || '';
        onRoomFormCumChange();

        const selToa = document.getElementById('roomFormToa');
        selToa.value = room.MaToaNha || '';
        selToa.style.pointerEvents = 'none';
        selToa.style.backgroundColor = '#f1f5f9';

        const inputSo = document.getElementById('roomFormSoPhong');
        inputSo.value = room.SoPhong || '';
        inputSo.readOnly = true;
        inputSo.style.backgroundColor = '#f1f5f9';

        document.getElementById('roomFormTang').value = room.Tang || 1;
        document.getElementById('roomFormLoaiPhong').value = room.MaLoaiPhong || '';
        document.getElementById('roomFormTrangThai').value = room.TrangThaiPhong || 'Còn chỗ';

        document.getElementById('btnSubmitRoom').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật phòng';
        switchZoneTab('phong');
        document.getElementById('cardFormRoom').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetFormRoom() {
        document.getElementById('formRoom').reset();
        document.getElementById('roomIsEdit').value = '0';

        const inputMa = document.getElementById('roomFormMaPhong');
        inputMa.readOnly = false;
        inputMa.style.backgroundColor = '';
        inputMa.style.cursor = '';
        document.getElementById('noticeRoomEdit').style.display = 'none';

        const selToa = document.getElementById('roomFormToa');
        selToa.style.pointerEvents = '';
        selToa.style.backgroundColor = '';

        const inputSo = document.getElementById('roomFormSoPhong');
        inputSo.readOnly = false;
        inputSo.style.backgroundColor = '';

        document.getElementById('titleFormRoom').innerHTML = '<i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i> <span>Thêm mới Phòng ở & Khởi tạo giường tự động</span>';
        document.getElementById('btnSubmitRoom').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu phòng & Sinh giường';
    }

    function handleDeleteRoom(btn) {
        const ma = btn.getAttribute('data-ma');
        const occupied = parseInt(btn.getAttribute('data-occupied') || '0');

        if (occupied > 0) {
            alert(`Không thể xóa phòng ${ma} vì đang có ${occupied} sinh viên đang ở. Vui lòng chuyển sinh viên sang phòng khác trước khi xóa!`);
            return;
        }

        openConfirmModal({
            title: 'Xác nhận xóa phòng ở',
            message: `Bạn có chắc chắn muốn xóa phòng <code style="color:var(--primary); font-weight:700;">${ma}</code>?`,
            note: '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Thao tác này sẽ xóa phòng và toàn bộ các giường trống liên kết khỏi hệ thống.',
            btnText: 'Xác nhận xóa',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            action: '<?= url("staff/zones/delete-room") ?>',
            inputs: {
                maPhong: ma
            }
        });
    }

    // =========================================================================
    // 4. LOGIC TÒA NHÀ, CỤM NHÀ & KHU KTX
    // =========================================================================

    function editToa(toa) {
        document.getElementById('toaIsEdit').value = '1';
        document.getElementById('titleFormToa').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i> <span>Chỉnh sửa Tòa nhà: ' + toa.TenToaNha + ' (' + toa.MaToaNha + ')</span>';

        const inputMa = document.getElementById('toaMaToaNha');
        inputMa.value = toa.MaToaNha;
        inputMa.readOnly = true;
        inputMa.style.backgroundColor = '#f1f5f9';
        inputMa.style.cursor = 'not-allowed';
        document.getElementById('noticeToaEdit').style.display = 'inline';

        document.getElementById('toaTenToaNha').value = toa.TenToaNha || '';
        document.getElementById('toaMaCum').value = toa.MaCum || '';
        document.getElementById('toaTongSoTang').value = toa.TongSoTang || 5;
        document.getElementById('toaGioiTinh').value = toa.GioiTinhQuyDinh || 'Nam';
        document.getElementById('toaTrangThai').value = toa.TrangThaiToaNha || 'Đang hoạt động';

        document.getElementById('btnSubmitToa').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật Tòa nhà';
        switchZoneTab('toa');
        document.getElementById('cardFormToa').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetFormToa() {
        document.getElementById('formToa').reset();
        document.getElementById('toaIsEdit').value = '0';

        const inputMa = document.getElementById('toaMaToaNha');
        inputMa.readOnly = false;
        inputMa.style.backgroundColor = '';
        inputMa.style.cursor = '';
        document.getElementById('noticeToaEdit').style.display = 'none';

        document.getElementById('titleFormToa').innerHTML = '<i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i> <span>Thêm mới Tòa nhà lưu trú</span>';
        document.getElementById('btnSubmitToa').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Tòa nhà';
    }

    function handleDeleteToa(btn) {
        const ma = btn.getAttribute('data-ma');
        const ten = btn.getAttribute('data-ten');

        openConfirmModal({
            title: 'Xác nhận xóa Tòa nhà',
            message: `Bạn có chắc chắn muốn xóa tòa nhà <b>${ten}</b> (<code>${ma}</code>)?`,
            note: '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Lưu ý: Chỉ được xóa khi tòa nhà không có sinh viên đang cư trú và các phòng liên kết đều trống.',
            btnText: 'Xác nhận xóa',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            action: '<?= url("staff/zones/delete-toa") ?>',
            inputs: {
                maToaNha: ma
            }
        });
    }

    function onSelectCumLeader(sel) {
        const selected = sel.options[sel.selectedIndex];
        const val = sel.value;
        const phone = selected.getAttribute('data-phone') || '';
        document.getElementById('cumHoTenTruongCum').value = val;
        if (phone) {
            document.getElementById('cumSoDienThoaiCum').value = phone;
        }
    }

    function editCum(cum) {
        document.getElementById('cumIsEdit').value = '1';
        document.getElementById('titleFormCum').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i> <span>Chỉnh sửa Cụm nhà: ' + cum.TenCum + ' (' + cum.MaCum + ')</span>';

        const inputMa = document.getElementById('cumMaCum');
        inputMa.value = cum.MaCum;
        inputMa.readOnly = true;
        inputMa.style.backgroundColor = '#f1f5f9';
        inputMa.style.cursor = 'not-allowed';
        document.getElementById('noticeCumEdit').style.display = 'inline';

        document.getElementById('cumMaKhu').value = cum.MaKhu || '';
        document.getElementById('cumTenCum').value = cum.TenCum || '';
        document.getElementById('cumViTriVanPhong').value = cum.ViTriVanPhong || '';
        document.getElementById('cumSoDienThoaiCum').value = cum.SoDienThoaiCum || '';
        document.getElementById('cumHoTenTruongCum').value = cum.HoTenTruongCum || '';

        const selLeader = document.getElementById('cumSelectTruongCum');
        if (selLeader) {
            selLeader.value = cum.HoTenTruongCum || '';
        }

        document.getElementById('btnSubmitCum').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật Cụm nhà';
        switchZoneTab('cum');
        document.getElementById('cardFormCum').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetFormCum() {
        document.getElementById('formCum').reset();
        document.getElementById('cumIsEdit').value = '0';

        const inputMa = document.getElementById('cumMaCum');
        inputMa.readOnly = false;
        inputMa.style.backgroundColor = '';
        inputMa.style.cursor = '';
        document.getElementById('noticeCumEdit').style.display = 'none';

        document.getElementById('titleFormCum').innerHTML = '<i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i> <span>Thêm mới / Cập nhật Cụm nhà</span>';
        document.getElementById('btnSubmitCum').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Cụm nhà';
    }

    function handleDeleteCum(btn) {
        const ma = btn.getAttribute('data-ma');
        const ten = btn.getAttribute('data-ten');

        openConfirmModal({
            title: 'Xác nhận xóa Cụm nhà',
            message: `Bạn có chắc chắn muốn xóa Cụm nhà <b>${ten}</b> (<code>${ma}</code>)?`,
            note: '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Thao tác chỉ thực hiện được khi cụm không còn tòa nhà nào trực thuộc.',
            btnText: 'Xác nhận xóa cụm',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            action: '<?= url("staff/zones/delete-cum") ?>',
            inputs: {
                maCum: ma
            }
        });
    }

    function editKhu(khu) {
        document.getElementById('khuIsEdit').value = '1';
        document.getElementById('titleFormKhu').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i> <span>Chỉnh sửa Khu KTX: ' + khu.TenKhu + '</span>';

        const inputMa = document.getElementById('khuMaKhu');
        inputMa.value = khu.MaKhu;
        inputMa.readOnly = true;
        inputMa.style.backgroundColor = '#f1f5f9';
        inputMa.style.cursor = 'not-allowed';
        document.getElementById('noticeKhuEdit').style.display = 'inline';

        document.getElementById('khuTenKhu').value = khu.TenKhu || '';
        document.getElementById('khuDiaChi').value = khu.DiaChi || '';
        document.getElementById('khuSoDienThoaiTruc').value = khu.SoDienThoaiTruc || '';

        document.getElementById('btnSubmitKhu').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật Khu KTX';
        switchZoneTab('khu');
        document.getElementById('cardFormKhu').scrollIntoView({
            behavior: 'smooth'
        });
    }

    function resetFormKhu() {
        document.getElementById('formKhu').reset();
        document.getElementById('khuIsEdit').value = '0';

        const inputMa = document.getElementById('khuMaKhu');
        inputMa.readOnly = false;
        inputMa.style.backgroundColor = '';
        inputMa.style.cursor = '';
        document.getElementById('noticeKhuEdit').style.display = 'none';

        document.getElementById('titleFormKhu').innerHTML = '<i class="fa-solid fa-plus-circle" style="color: var(--primary); margin-right: 8px;"></i> <span>Cấu hình Khu KTX</span>';
        document.getElementById('btnSubmitKhu').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu Khu KTX';
    }

    function handleDeleteKhu(btn) {
        const ma = btn.getAttribute('data-ma');
        const ten = btn.getAttribute('data-ten');

        openConfirmModal({
            title: 'Xác nhận xóa Khu KTX',
            message: `Bạn có chắc chắn muốn xóa Khu KTX <b>${ten}</b> (<code>${ma}</code>)?`,
            note: '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Chỉ xóa được khi Khu KTX không còn Cụm nhà nào trực thuộc.',
            btnText: 'Xác nhận xóa khu',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            action: '<?= url("staff/zones/delete-khu") ?>',
            inputs: {
                maKhu: ma
            }
        });
    }
</script>

<?php
$content = ob_get_clean();
$title = "Quản lý Hạ tầng KTX (Khu, Cụm, Tòa & Phòng) - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>