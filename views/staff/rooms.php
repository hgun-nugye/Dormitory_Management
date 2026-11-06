<?php
ob_start();

$formatTenToa = function (?string $name, string $code = '') {
    $str = trim((string)($name ?: $code));
    $cleaned = preg_replace('/^(tòa\s*|toà\s*)+/ui', '', $str);
    return 'Tòa ' . (!empty($cleaned) ? $cleaned : $str);
};

$filterToa = $filterToa ?? ($filters['toa'] ?? '');
$maToa = $maToa ?? ($toaInfo['MaToaNha'] ?? '');
$isAllToas = $isAllToas ?? empty($filterToa);
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Sơ đồ Phòng & Giường</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Sơ đồ Phòng & Giường</h1>
                <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                    <span>Phạm vi cán bộ:</span>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-primary') ?>" style="font-size: 0.8rem; padding: 4px 10px;">
                        <i class="fa-solid fa-building-user" style="margin-right: 6px;"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ quản trị') ?>
                    </span>
                </p>
            </div>

            <!-- CÁC NÚT THAO TÁC HEADER (ĐỒNG BỘ ADMIN & ZONES: THÊM MỚI, NHẬP FILE, XUẤT EXCEL, CẬP NHẬT TÒA, VỀ DASHBOARD) -->
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($isAllowed)): ?>
                    <?php if (!empty($canCrudPhong)): ?>
                        <button type="button" class="btn btn-primary" id="btnToggleAddRoom" onclick="toggleRoomForm()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                            <i class="fa-solid fa-plus"></i> Thêm phòng mới
                        </button>
                        <button type="button" class="btn btn-outline" onclick="openImportRoomModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách phòng từ file CSV">
                            <i class="fa-solid fa-file-import" style="color: #2563eb;"></i> Nhập file
                        </button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-outline" id="btnExportRooms" onclick="exportFilteredRooms()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách phòng theo kết quả lọc ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (<?= count($phongList ?? []) ?>)</span>
                    </button>
                    <?php if (!empty($canUpdateToa)): ?>
                        <button type="button" class="btn btn-outline" onclick="openUpdateToaModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px; color: #4338ca; border-color: #c7d2fe; background: #eef2ff;" title="Cập nhật thông tin Tòa nhà (Tên, Giới tính, Trạng thái)">
                            <i class="fa-solid fa-gear" style="color: #4f46e5;"></i> Cập nhật Tòa nhà
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 14px; display: inline-flex; align-items: center; justify-content: center; gap: 6px;">
                    <i class="fa-solid fa-arrow-left"></i> Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
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
                Từ chối quyền truy cập: Phân hệ Sơ đồ phòng và Hạ tầng KTX chỉ dành riêng cho Cán bộ Quản lý (Trưởng Tòa, Trưởng Cụm, Trưởng Khu, Ban Giám đốc KTX). Nhân viên Kế toán và Kỹ thuật không có quyền thao tác!
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

        <!-- THẺ THỐNG KÊ NHANH TÒA NHÀ (QUICK STATS CARDS) -->
        <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 20px;">
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Tổng số phòng</div>
                <div style="font-size: 1.6rem; font-weight: 700; color: #0f172a; margin: 4px 0;"><?= (int)($stats['TongPhong'] ?? 0) ?></div>
                <div style="font-size: 0.775rem; color: #16a34a; font-weight: 500;">
                    <b><?= (int)($stats['PhongHoatDong'] ?? 0) ?></b> hoạt động &bull; <span style="color:#d97706;"><b><?= (int)($stats['PhongBaoTri'] ?? 0) ?></b> bảo trì</span>
                </div>
            </div>
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Tổng số giường</div>
                <div style="font-size: 1.6rem; font-weight: 700; color: #2563eb; margin: 4px 0;"><?= (int)($stats['TongGiuong'] ?? 0) ?></div>
                <div style="font-size: 0.775rem; color: var(--text-secondary);">
                    <b style="color:#15803d;"><?= (int)($stats['GiuongDangO'] ?? 0) ?></b> có người &bull; <b style="color:#0284c7;"><?= (int)($stats['GiuongTrong'] ?? 0) ?></b> trống &bull; <b style="color:#d97706;"><?= (int)($stats['GiuongBaoTri'] ?? 0) ?></b> bảo trì
                </div>
            </div>
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Tỷ lệ lấp đầy</div>
                <?php $tyLe = ($stats['TongGiuong'] ?? 0) > 0 ? round((($stats['GiuongDangO'] ?? 0) / $stats['TongGiuong']) * 100) : 0; ?>
                <div style="font-size: 1.6rem; font-weight: 700; color: <?= $tyLe >= 90 ? '#dc2626' : ($tyLe >= 60 ? '#2563eb' : '#16a34a') ?>; margin: 4px 0;"><?= $tyLe ?>%</div>
                <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin-top: 6px;">
                    <div style="width: <?= min(100, $tyLe) ?>%; height: 100%; background: <?= $tyLe >= 90 ? '#dc2626' : ($tyLe >= 60 ? '#2563eb' : '#16a34a') ?>;"></div>
                </div>
            </div>
            <div class="card" style="padding: 16px 20px; border-radius: 10px; border: 1px solid var(--border); box-shadow: 0 2px 6px rgba(0,0,0,0.03);">
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">
                    <?= !empty($isAllToas) ? 'Phạm vi hiển thị' : 'Tòa nhà mục tiêu' ?>
                </div>
                <div style="font-size: 1.2rem; font-weight: 700; color: #0f172a; margin: 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?>">
                    <?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?>
                </div>
                <div style="font-size: 0.775rem; color: #64748b;">
                    <?= !empty($isAllToas) ? 'Toàn bộ tòa nhà phụ trách' : ('Cụm <b>' . htmlspecialchars($toaInfo['TenCum'] ?? '---') . '</b> &bull; Khu <b>' . htmlspecialchars($toaInfo['TenKhu'] ?? '---') . '</b> &bull; Dành cho <b>' . htmlspecialchars($toaInfo['GioiTinhQuyDinh'] ?? 'Nam') . '</b>') ?>
                </div>
            </div>
        </div>

        <!-- BIỂU MẪU CRUD: THÊM / CẬP NHẬT PHÒNG Ở (ẨN MẶC ĐỊNH, HIỆN KHI CLICK HOẶC SỬA) -->
        <div class="card" id="roomFormCard" style="<?= !empty($flashError) ? 'display: block;' : 'display: none;' ?> margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="formTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span id="formTitleText">Thêm mới Phòng ở<?= !empty($isAllToas) ? '' : ' (' . htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) . ')' ?></span>
                </h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="hideRoomForm()" style="border-radius: 6px; padding: 4px 10px; color: var(--text-muted);" title="Đóng biểu mẫu">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="roomActionForm" action="<?= url('staff/rooms/save-room') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" id="roomIsEdit" name="isEdit" value="0">
                <input type="hidden" id="roomMaPhong" name="maPhong" value="">

                <div style="background: #f1f5f9; padding: 10px 14px; border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; color: #334155; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
                    <div>
                        <?php if (!empty($isAllToas) && count($availableToas) > 1): ?>
                            <span style="font-weight: 600;">Chọn Tòa nhà:</span>
                            <select id="roomMaToaSelect" name="maToa" class="btn-outline" style="padding: 4px 8px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; margin-left: 6px; background: #fff;" onchange="onRoomFormToaChange(this.value)">
                                <?php foreach ($availableToas as $at): ?>
                                    <option value="<?= htmlspecialchars($at['MaToaNha']) ?>" data-tang="<?= (int)($at['TongSoTang'] ?? 5) ?>">
                                        <?= htmlspecialchars($formatTenToa($at['TenToaNha'] ?? $at['MaToaNha'], $at['MaToaNha'])) ?> (<?= htmlspecialchars($at['MaToaNha']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="hidden" id="roomMaToaHidden" name="maToa" value="<?= htmlspecialchars($maToa) ?>">
                            Tòa: <strong><?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?></strong> (Mã: <code><?= htmlspecialchars($maToa) ?></code>) &bull;
                            Cụm: <strong><?= htmlspecialchars($toaInfo['TenCum'] ?? 'Cụm') ?></strong> &bull;
                            Dành cho: <strong><?= htmlspecialchars($toaInfo['GioiTinhQuyDinh'] ?? 'Nam') ?></strong>
                        <?php endif; ?>
                    </div>
                    <div style="color: #64748b; font-size: 0.8rem;">
                        Tối đa <strong id="roomFormMaxFloor"><?= (int)($toaInfo['TongSoTang'] ?? 5) ?></strong> tầng
                    </div>
                </div>

                <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Số phòng <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="roomSoPhong" name="soPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 101, 102, 203..." required oninput="updateRoomCodePreview()">
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Tầng <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomTang" name="tang" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required onchange="updateRoomCodePreview()">
                            <?php
                            $tongTang = (int)($toaInfo['TongSoTang'] ?? 5);
                            for ($t = 1; $t <= $tongTang; $t++):
                            ?>
                                <option value="<?= $t ?>">Tầng <?= $t ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mã phòng định danh (Xem trước)
                        </label>
                        <input type="text" id="roomCodePreview" class="btn-outline" readonly style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem; background: #f8fafc; font-weight: 700; color: var(--primary);" value="<?= htmlspecialchars($maToa) ?>.---">
                    </div>
                </div>

                <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Loại phòng tiêu chuẩn <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomMaLoaiPhong" name="maLoaiPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="">-- Chọn loại phòng --</option>
                            <?php foreach ($loaiPhongList as $lp): ?>
                                <option value="<?= htmlspecialchars($lp['MaLoaiPhong']) ?>">
                                    <?= htmlspecialchars($lp['TenLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường &bull; <?= format_money($lp['DonGiaLuuTruChuan'] ?? 0) ?>/tháng)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Trạng thái phòng <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="roomTrangThaiPhong" name="trangThaiPhong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Còn chỗ">Còn chỗ</option>
                            <option value="Đã đầy">Đã đầy</option>
                            <option value="Bảo trì">Bảo trì</option>
                        </select>
                    </div>
                </div>

                <div id="roomTruongPhongBox" style="display: none; margin-bottom: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #92400e; margin-bottom: 6px;">
                        Chỉ định Trưởng phòng nội trú:
                    </label>
                    <select id="roomTruongPhong" name="truongPhong" class="btn-outline" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem; background: #fff;">
                        <option value="">-- Chưa chỉ định Trưởng phòng --</option>
                    </select>
                    <div style="font-size: 0.75rem; color: #78350f; margin-top: 4px;">
                        * Danh sách hiển thị các sinh viên đang cư trú tại phòng này. Có thể chọn để chỉ định Trưởng phòng đại diện hoặc bỏ chọn để hủy.
                    </div>
                </div>

                <div id="roomAutoBedNotice" style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 0.85rem; color: #166534;">
                    <i class="fa-solid fa-wand-magic-sparkles" style="margin-right: 6px;"></i>
                    <strong>Tự động khởi tạo giường:</strong> Khi tạo phòng mới, hệ thống sẽ tự động khởi tạo các giường trống (<code>G1, G2, G3...</code>) theo đúng sức chứa tối đa của loại phòng đã chọn.
                </div>

                <!-- CÁC NÚT CHỨC NĂNG FORM -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 8px; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <button type="submit" id="btnSubmitRoomForm" class="btn btn-primary" style="border-radius: 6px; padding: 10px 22px; font-weight: 600;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> <span id="btnSubmitRoomText">Lưu phòng & Khởi tạo giường</span>
                        </button>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetRoomForm()">
                            <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="hideRoomForm()">
                            <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- BẢNG DANH SÁCH PHÒNG Ở & PHÂN TRANG (CRUD TABLE NHƯ USERS) -->
        <div class="card">
            <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span>Danh sách Phòng ở (<?= !empty($isAllToas) ? 'Tất cả Tòa nhà' : htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?>)</span>
                    <span class="badge badge-info" id="roomTotalBadge"><?= count($phongList ?? []) ?> phòng</span>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC PHÂN CẤP CASCADING THEO QUYỀN HẠN (ĐỒNG BỘ GIAO DIỆN ADMIN #f8fafc) -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <!-- 1. TÌM KIẾM THÔNG MINH -->
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 360px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchRoomInput" class="btn-outline"
                        placeholder="Tìm theo mã phòng, số phòng, tên SV, MSSV, CCCD..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterRooms()">
                </div>

                <!-- 2. BỘ LỌC CẤP BẬC HIERARCHICAL CASCADING (CẤP CAO HƠN CÓ NHIỀU BỘ LỌC HƠN) -->
                <?php if ($scope['level'] === 'ALL'): ?>
                    <!-- Admin / Giám đốc: Lọc Khu -> Cụm -> Tòa -->
                    <select id="filterKhuSelect" class="btn-outline" style="min-width: 135px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onKhuChange(this.value)">
                        <option value="">-- Tất cả Khu --</option>
                        <?php foreach ($availableKhus as $k): ?>
                            <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= (($filters['khu'] ?? '') === $k['MaKhu']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($k['TenKhu'] ?? $k['MaKhu']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select id="filterCumSelect" class="btn-outline" style="min-width: 145px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onCumChange(this.value)">
                        <option value="">-- Tất cả Cụm --</option>
                        <?php foreach ($availableCums as $c): ?>
                            <option value="<?= htmlspecialchars($c['MaCum']) ?>" data-khu="<?= htmlspecialchars($c['MaKhu'] ?? '') ?>" <?= (($filters['cum'] ?? '') === $c['MaCum']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['TenCum'] ?? $c['MaCum']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onToaChange(this.value)">
                        <option value="" <?= empty($filterToa) ? 'selected' : '' ?>>-- Tất cả Tòa nhà --</option>
                        <?php foreach ($availableToas as $t): ?>
                            <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum'] ?? '') ?>" <?= ($filterToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($scope['level'] === 'KHU'): ?>
                    <!-- Trưởng Khu: Cố định Khu, lọc Cụm -> Tòa -->
                    <select class="btn-outline" style="min-width: 120px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #e2e8f0; color: #475569;" disabled>
                        <option><?= htmlspecialchars($scope['availableKhus'][0]['TenKhu'] ?? ('Khu ' . $scope['maKhu'])) ?></option>
                    </select>

                    <select id="filterCumSelect" class="btn-outline" style="min-width: 145px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onCumChange(this.value)">
                        <option value="">-- Tất cả Cụm --</option>
                        <?php foreach ($availableCums as $c): ?>
                            <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= (($filters['cum'] ?? '') === $c['MaCum']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['TenCum'] ?? $c['MaCum']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onToaChange(this.value)">
                        <option value="" <?= empty($filterToa) ? 'selected' : '' ?>>-- Tất cả Tòa nhà --</option>
                        <?php foreach ($availableToas as $t): ?>
                            <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" data-cum="<?= htmlspecialchars($t['MaCum'] ?? '') ?>" <?= ($filterToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php elseif ($scope['level'] === 'CUM'): ?>
                    <!-- Trưởng Cụm: Cố định Cụm, lọc Tòa -->
                    <select class="btn-outline" style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; background: #e2e8f0; color: #475569;" disabled>
                        <option><?= htmlspecialchars($scope['availableCums'][0]['TenCum'] ?? ('Cụm ' . $scope['maCum'])) ?></option>
                    </select>

                    <select id="filterToaSelect" class="btn-outline" style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;" onchange="onToaChange(this.value)">
                        <option value="" <?= empty($filterToa) ? 'selected' : '' ?>>-- Tất cả Tòa nhà --</option>
                        <?php foreach ($availableToas as $t): ?>
                            <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" <?= ($filterToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($formatTenToa($t['TenToaNha'] ?? $t['MaToaNha'], $t['MaToaNha'])) ?> (<?= htmlspecialchars($t['MaToaNha']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>

                <?php else: ?>
                    <!-- Trưởng Tòa: Cố định Tòa -->
                    <span class="badge badge-success" style="padding: 8px 14px; font-size: 0.85rem; font-weight: 600;">
                        <?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?>
                    </span>
                <?php endif; ?>

                <!-- 3. LỌC TẦNG -->
                <select id="filterRoomFloor" class="btn-outline"
                    style="min-width: 130px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả các tầng --</option>
                    <?php for ($t = 1; $t <= $tongTang; $t++): ?>
                        <option value="<?= $t ?>">Tầng <?= $t ?></option>
                    <?php endfor; ?>
                </select>

                <!-- 4. LỌC LOẠI PHÒNG -->
                <select id="filterRoomType" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả loại phòng --</option>
                    <?php foreach ($loaiPhongList as $lp): ?>
                        <option value="<?= htmlspecialchars($lp['MaLoaiPhong'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($lp['TenLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường)
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- 5. LỌC TRẠNG THÁI -->
                <select id="filterRoomStatus" class="btn-outline"
                    style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Còn chỗ">Còn chỗ</option>
                    <option value="Đã đầy">Đã đầy</option>
                    <option value="Bảo trì">Bảo trì</option>
                </select>

                <!-- 6. NÚT XÓA BỘ LỌC -->
                <div>
                    <button type="button" class="btn btn-outline btn-sm" onclick="clearRoomFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                        <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                    </button>
                </div>
            </div>

            <!-- BẢNG DANH SÁCH PHÒNG: MỖI PHÒNG LÀ 1 TABLE ROW GỌN GÀNG -->
            <?php if (!empty($phongList)): ?>
                <div class="table-responsive">
                    <table class="data-table" id="tableStaffRooms">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Mã phòng</th>
                                <th style="width: 120px;">Số phòng</th>
                                <th style="width: 180px;">Loại phòng</th>
                                <th style="width: 130px;">Sức chứa</th>
                                <th style="width: 230px;">Sơ đồ Giường</th>
                                <th style="width: 140px;">Trưởng phòng</th>
                                <th style="width: 120px; text-align: center;">Trạng thái</th>
                                <th style="text-align: right; min-width: 220px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody id="roomTableBody">
                            <?php foreach ($phongList as $p): ?>
                                <?php
                                $beds = $giuongByPhong[$p['MaPhong']] ?? [];
                                $maxCap = (int)($p['SoGiuongToiDa'] ?? 4);
                                $occupied = (int)($p['SoSVDangO'] ?? 0);
                                $fillPercent = ($maxCap > 0) ? round(($occupied / $maxCap) * 100) : 0;

                                $rawStatus = trim($p['TrangThaiPhong'] ?? '');
                                $maintenanceBedsCount = 0;
                                foreach ($beds as $bg) {
                                    $stG = mb_strtolower(trim($bg['TrangThaiGiuong'] ?? ''));
                                    if (str_contains($stG, 'trì') || str_contains($stG, 'tri') || str_contains($stG, 'khóa') || str_contains($stG, 'khoa')) {
                                        $maintenanceBedsCount++;
                                    }
                                }
                                $isMaintenance = in_array($rawStatus, ['Bảo trì', 'Đang bảo trì', 'Khóa bảo trì'], true)
                                    || str_contains(mb_strtolower($rawStatus), 'bảo trì')
                                    || str_contains(mb_strtolower($rawStatus), 'bao tri')
                                    || str_contains(mb_strtolower($rawStatus), 'khóa')
                                    || str_contains(mb_strtolower($rawStatus), 'khoa');

                                $isFull = !$isMaintenance && (($occupied >= $maxCap && $maxCap > 0) || in_array($rawStatus, ['Đã đầy', 'Đầy', 'Đầy chỗ'], true));

                                if ($isMaintenance) {
                                    $roomStatus = 'Bảo trì';
                                } elseif ($isFull) {
                                    $roomStatus = 'Đã đầy';
                                } else {
                                    $roomStatus = 'Còn chỗ';
                                }
                                ?>
                                <tr class="room-row"
                                    data-id="<?= htmlspecialchars($p['MaPhong'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-tang="<?= (int)$p['Tang'] ?>"
                                    data-loaiphong="<?= htmlspecialchars($p['MaLoaiPhong'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-status="<?= $roomStatus ?>"
                                    style="<?= $isMaintenance ? 'background: #fffbeb;' : '' ?>">

                                    <!-- 1. Mã phòng -->
                                    <td>
                                        <b style="color: var(--primary); font-size: 0.95rem; font-family: monospace;">
                                            <?= htmlspecialchars($p['MaPhong']) ?>
                                        </b>
                                    </td>

                                    <!-- 2. Số phòng & Tầng -->
                                    <td>
                                        <b>Phòng <?= htmlspecialchars($p['SoPhong']) ?></b>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            Tầng <?= (int)$p['Tang'] ?><?= !empty($p['TenToaNha']) ? ' &bull; ' . htmlspecialchars($formatTenToa($p['TenToaNha'], $p['MaToaNha'])) : '' ?>
                                        </div>
                                    </td>

                                    <!-- 3. Loại phòng & Giá -->
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                            <?= htmlspecialchars($p['TenLoaiPhong'] ?? 'Tiêu chuẩn') ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?= format_money($p['DonGiaLuuTruChuan'] ?? 0) ?>/tháng
                                        </div>
                                    </td>

                                    <!-- 4. Sĩ số & Tiến độ -->
                                    <td style="vertical-align: middle;">
                                        <div style="min-width: 105px; max-width: 125px;">
                                            <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 5px;">
                                                <span style="font-weight: 700; color: #0f172a; font-size: 0.875rem;">
                                                    <?= $occupied ?> <span style="font-weight: 500; font-size: 0.75rem; color: #64748b;">/ <?= $maxCap ?> chỗ</span>
                                                </span>
                                                <span style="font-size: 0.75rem; font-weight: 700; color: <?= $isMaintenance ? '#d97706' : ($isFull ? '#16a34a' : ($fillPercent > 0 ? '#2563eb' : '#94a3b8')) ?>;">
                                                    <?= $fillPercent ?>%
                                                </span>
                                            </div>
                                            <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 9999px; overflow: hidden;">
                                                <div style="width: <?= min(100, $fillPercent) ?>%; height: 100%; background: <?= $isMaintenance ? '#f59e0b' : ($isFull ? '#16a34a' : ($fillPercent > 0 ? '#3b82f6' : '#cbd5e1')) ?>; border-radius: 9999px;"></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 5. Sơ đồ Giường -->
                                    <td style="vertical-align: middle;">
                                        <?php
                                        // Kiểm tra số giường đã có người và tổng số giường
                                        $totalBeds = count($beds);
                                        $occupiedBeds = 0;

                                        foreach ($beds as $bed) {
                                            if (!empty($bed['MaSinhVien']) || ($bed['TrangThaiGiuong'] === 'Đã có người ở')) {
                                                $occupiedBeds++;
                                            }
                                        }

                                        // Phòng FULL khi tất cả giường đều có người
                                        $isRoomFull = ($totalBeds > 0 && $occupiedBeds === $totalBeds);
                                        ?>

                                        <div style="display: flex; gap: 5px; align-items: center; flex-wrap: wrap; max-width: 230px;">
                                            <?php foreach ($beds as $g): ?>
                                                <?php
                                                $bOccupied = !empty($g['MaSinhVien']) || ($g['TrangThaiGiuong'] === 'Đã có người ở');
                                                $bLocked = ($g['TrangThaiGiuong'] === 'Khóa bảo trì' || $g['TrangThaiGiuong'] === 'Đang bảo trì');
                                                $bLeader = (!empty($p['TruongPhong']) && $p['TruongPhong'] === $g['MaSinhVien']);

                                                if ($bOccupied) {
                                                    if ($bLeader) {
                                                        // Giường trưởng phòng: VÀNG
                                                        $pillBg = '#fef3c7';
                                                        $pillColor = '#b45309';
                                                        $pillBorder = '#fde68a';
                                                        $pillIcon = 'fa-solid fa-star';
                                                        $pillTitle = $g['KyHieuGiuong'] . ': ' . ($g['HoVaTen'] ?? 'Sinh viên') . ' (Trưởng phòng)';
                                                    } elseif ($isRoomFull) {
                                                        // Giường thuộc phòng FULL: XANH LÁ
                                                        $pillBg = '#dcfce7';
                                                        $pillColor = '#15803d';
                                                        $pillBorder = '#bbf7d0';
                                                        $pillIcon = 'fa-solid fa-user';
                                                        $pillTitle = $g['KyHieuGiuong'] . ': ' . ($g['HoVaTen'] ?? 'Sinh viên') . ' (Phòng đã FULL)';
                                                    } else {
                                                        // Giường đang sử dụng nhưng phòng chưa FULL: XANH DƯƠNG
                                                        $pillBg = '#dbeafe';
                                                        $pillColor = '#1d4ed8';
                                                        $pillBorder = '#bfdbfe';
                                                        $pillIcon = 'fa-solid fa-user';
                                                        $pillTitle = $g['KyHieuGiuong'] . ': ' . ($g['HoVaTen'] ?? 'Sinh viên') . ' (' . ($g['MSSVTaiTruong'] ?? '') . ')';
                                                    }
                                                } elseif ($bLocked) {
                                                    // Giường bảo trì: ĐỎ
                                                    $pillBg = '#fee2e2';
                                                    $pillColor = '#991b1b';
                                                    $pillBorder = '#fecaca';
                                                    $pillIcon = 'fa-solid fa-lock';
                                                    $pillTitle = $g['KyHieuGiuong'] . ': Đang bảo trì';
                                                } else {
                                                    // Giường trống: XÁM
                                                    $pillBg = '#f8fafc';
                                                    $pillColor = '#64748b';
                                                    $pillBorder = '#e2e8f0';
                                                    $pillIcon = 'fa-solid fa-bed';
                                                    $pillTitle = $g['KyHieuGiuong'] . ': Trống';
                                                }
                                                ?>

                                                <span title="<?= htmlspecialchars($pillTitle) ?>"
                                                    style="display: inline-flex; align-items: center; justify-content: center; gap: 4px; min-width: 46px; height: 24px; padding: 0 6px; border-radius: 5px; font-size: 0.725rem; font-weight: 600; background: <?= $pillBg ?>; border: 1px solid <?= $pillBorder ?>; color: <?= $pillColor ?>; cursor: pointer; box-sizing: border-box;"
                                                    onclick="openBedDetailModal('<?= htmlspecialchars($p['MaPhong']) ?>')">
                                                    <i class="<?= $pillIcon ?>" style="font-size: 0.65rem;"></i>
                                                    <span><?= htmlspecialchars($g['KyHieuGiuong']) ?></span>
                                                </span>

                                            <?php endforeach; ?>
                                        </div>
                                    </td>

                                    <!-- 6. Trưởng phòng -->
                                    <td style="vertical-align: middle;">
                                        <?php if (!empty($p['TruongPhong'])): ?>
                                            <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; background: #f0fdf4; color: #166534; font-size: 0.8rem; font-weight: 600; border: none;" title="MSSV: <?= htmlspecialchars($p['TruongPhong']) ?>">
                                                <?= htmlspecialchars($p['TenTruongPhong'] ?? $p['TruongPhong']) ?>
                                            </span>
                                        <?php elseif ($occupied > 0): ?>
                                            <a href="javascript:void(0)" onclick="openBedDetailModal('<?= htmlspecialchars($p['MaPhong']) ?>')"
                                                style="font-size: 0.8rem; color: var(--primary); font-weight: 600; text-decoration: underline; display: inline-flex; align-items: center; gap: 4px;"
                                                title="Bấm để chọn sinh viên làm Trưởng phòng">
                                                Chọn Trưởng phòng
                                            </a>
                                        <?php else: ?>
                                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">
                                                — Phòng trống
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- 7. Trạng thái (BỎ VIỀN 100%) -->
                                    <td style="vertical-align: middle; text-align: center;">
                                        <?php
                                        if ($isMaintenance) {
                                            $stBg = '#fef3c7';
                                            $stColor = '#b45309';
                                            $stLabel = 'Bảo trì';
                                        } elseif ($isFull) {
                                            $stBg = '#dcfce7';
                                            $stColor = '#15803d';
                                            $stLabel = 'Đã đầy';
                                        } else {
                                            $stBg = '#eff6ff';
                                            $stColor = '#1d4ed8';
                                            $stLabel = 'Còn chỗ';
                                        }
                                        ?>
                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 6px; font-size: 0.775rem; font-weight: 600; background: <?= $stBg ?>; color: <?= $stColor ?>; border: none; white-space: nowrap; line-height: 1.2;">
                                            <?= $stLabel ?>
                                        </span>
                                    </td>

                                    <!-- 8. Thao tác -->
                                    <td style="text-align: right; white-space: nowrap;">
                                        <!-- Nút Xem chi tiết giường -->
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; margin-right: 4px;"
                                            onclick="openBedDetailModal('<?= htmlspecialchars($p['MaPhong']) ?>')"
                                            title="Quản lý sơ đồ giường và cư trú">
                                            <i class="fa-solid fa-bed" style="margin-right: 4px;"></i> Giường
                                        </button>

                                        <!-- Nút Sửa -->
                                        <button type="button" class="btn btn-outline btn-sm"
                                            style="border-radius: 6px; margin-right: 4px;"
                                            onclick='editRoom(<?= json_encode($p, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            title="Chỉnh sửa thông tin phòng">
                                            <i class="fa-solid fa-pen-to-square" style="margin-right: 4px;"></i> Sửa
                                        </button>

                                        <!-- Nút Bảo trì / Mở lại hoạt động -->
                                        <form method="POST" action="<?= url('staff/rooms/toggle-status') ?>" style="display: inline;" onsubmit="return confirm('<?= $isMaintenance ? "Xác nhận mở lại hoạt động cho phòng " . $p['MaPhong'] . "?" : "Xác nhận chuyển phòng " . $p['MaPhong'] . " sang trạng thái Bảo trì?" ?>');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="maPhong" value="<?= htmlspecialchars($p['MaPhong']) ?>">
                                            <button type="submit" class="btn btn-outline btn-sm"
                                                style="border-radius: 6px; margin-right: 4px; <?= $isMaintenance ? 'color: #15803d; border-color: #86efac; background: #f0fdf4;' : 'color: #b45309; border-color: #fde68a;' ?>"
                                                title="<?= $isMaintenance ? 'Mở lại hoạt động' : 'Chuyển sang Bảo trì' ?>">
                                                <i class="fa-solid <?= $isMaintenance ? 'fa-lock-open' : 'fa-lock' ?>" style="margin-right: 4px;"></i> <?= $isMaintenance ? 'Mở' : 'Bảo trì' ?>
                                            </button>
                                        </form>

                                        <!-- Nút Xóa (chỉ hiển thị nếu phòng trống không có sinh viên) -->
                                        <?php if ($occupied === 0): ?>
                                            <button type="button" class="btn btn-outline btn-sm"
                                                style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                                onclick="openDeleteRoomModal('<?= htmlspecialchars($p['MaPhong']) ?>')"
                                                title="Xóa phòng trống">
                                                <i class="fa-solid fa-trash-can" style="margin-right: 4px;"></i> Xóa
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- THANH PHÂN TRANG (PAGINATION CONTROLS NHƯ USERS) -->
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
                        <!-- Sẽ được sinh động bằng Javascript -->
                    </div>
                </div>
            <?php else: ?>
                <p class="text-muted" style="font-size: 0.9rem; padding: 20px 0;">Chưa có phòng ở nào được khởi tạo trong tòa nhà.</p>
            <?php endif; ?>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL IMPORT DANH SÁCH PHÒNG TỪ FILE CSV (GIỐNG MẪU USERS) -->
        <!-- ========================================================================= -->
        <div id="importRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box; overflow-y: auto;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; max-height: calc(100vh - 40px); display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; flex-shrink: 0;">
                    <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b;">
                        Nhập danh sách Phòng ở từ file CSV
                    </div>
                    <button type="button" onclick="closeImportRoomModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="<?= url('staff/rooms/import') ?>" method="POST" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; flex: 1 1 auto; overflow: hidden;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="maToa" value="<?= htmlspecialchars($maToa) ?>">
                    <div style="padding: 20px; overflow-y: auto; flex: 1 1 auto; max-height: calc(100vh - 170px);">
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #1e40af;">
                            <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-info"></i> Quy định định dạng file nhập dữ liệu:
                            </div>
                            <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                                <li>Hỗ trợ file định dạng <b>.CSV</b> hoặc <b>.TXT</b> (mã hóa UTF-8), phân cách bởi dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>.</li>
                                <li><b>Cột bắt buộc:</b> <code>SoPhong</code> (Số phòng, ví dụ: <code>101</code>, <code>102</code>...).</li>
                                <li><b>Thông tin phòng:</b> <code>Tang</code>, <code>MaLoaiPhong</code> (LP4, LP6, LP8...), <code>TrangThaiPhong</code> (Còn chỗ, Đã đầy, Bảo trì).</li>
                                <li><b>Sơ đồ giường & Sinh viên cư trú:</b> <code>KyHieuGiuong</code> (G1, G2...), <code>ViTriTangGiuong</code> (1: dưới, 2: trên), <code>MaSinhVien</code> (MSSV sinh viên đang ở giường này), <code>HoVaTen</code>, <code>GioiTinh</code>, <code>SoCCCD</code>, <code>SoDienThoai</code>, <code>Email</code>, <code>MaTruong</code>.</li>
                                <li><b>Chức vụ & Tạm trú:</b> <code>LaTruongPhong</code> (1: Trưởng phòng, 0: Thành viên), <code>DaNopCT01</code> (1: Đã nộp, 0: Chưa nộp).</li>
                                <li><i>Hệ thống tự động liên kết hợp đồng nội trú cho sinh viên và tự động sinh đủ số giường còn lại theo đúng tiêu chuẩn Loại phòng!</i></li>
                            </ul>
                            <div style="margin-top: 12px; display: flex; justify-content: flex-end;">
                                <a href="<?= url('staff/rooms/template') ?>" class="btn btn-outline btn-sm" style="background: #fff; color: #1e40af; border-color: #93c5fd; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 6px 14px; display: inline-flex; align-items: center;">
                                    <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Tải tệp CSV mẫu chuẩn (Có Giường & Sinh viên)
                                </a>
                            </div>
                        </div>

                        <div style="margin-bottom: 16px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                                Chọn tệp CSV danh sách phòng <span style="color: #dc2626;">*</span>
                            </label>
                            <input type="file" name="importFile" accept=".csv,.txt" required class="btn-outline" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem; background: #fff;">
                            <small style="color: var(--text-muted); display: block; margin-top: 4px;">Tệp tối đa 10MB, mã hóa UTF-8.</small>
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                            <input type="hidden" name="overwriteExisting" value="0">
                            <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                                <input type="checkbox" id="overwriteExistingRooms" name="overwriteExisting" value="1" checked style="margin-top: 3px; width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;">
                                <span>
                                    <b style="color: #1e293b;">Cập nhật / Ghi đè nếu phòng đã tồn tại:</b><br>
                                    <span style="font-size: 0.8rem; color: #64748b; line-height: 1.5; display: block; margin-top: 2px;">
                                        • <b>Nếu chọn:</b> Cập nhật thông tin phòng (tầng, loại phòng, trạng thái) và đồng bộ sơ đồ giường, sinh viên lưu trú theo đúng dữ liệu trong file.<br>
                                        • <b>Nếu bỏ chọn:</b> Bỏ qua các phòng đã có trong hệ thống, chỉ thêm mới các phòng chưa từng tồn tại.
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                        <button type="button" class="btn btn-outline" onclick="closeImportRoomModal()" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px;">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-file-import"></i> Bắt đầu nhập dữ liệu
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL CHI TIẾT SƠ ĐỒ GIƯỜNG & LƯU TRÚ (BED DETAILS & MANAGEMENT MODAL) -->
        <!-- ========================================================================= -->
        <div id="bedDetailModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 1000px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
                <!-- Header -->
                <div style="padding: 16px 22px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b;">
                        <span>Chi tiết Giường & Sinh viên cư trú - Phòng <span id="modalBedRoomCode" style="color: var(--primary);"></span></span>
                    </div>
                    <button type="button" onclick="closeBedDetailModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- Body (Scrollable) -->
                <div style="padding: 20px; overflow-y: auto; flex: 1;">
                    <div id="modalBedRoomSummary" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #334155; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <!-- Sinh bằng JS -->
                    </div>

                    <!-- Bảng các giường -->
                    <div class="table-responsive" style="margin-bottom: 20px;">
                        <table class="data-table" style="margin: 0; font-size: 0.85rem;">
                            <thead>
                                <tr style="background: #f1f5f9; font-size: 0.8rem; text-transform: uppercase;">
                                    <th>Mã Giường</th>
                                    <th>Vị trí tầng</th>
                                    <th>Sinh viên lưu trú</th>
                                    <th>Trường ĐH</th>
                                    <th>CCCD / SĐT</th>
                                    <th>Vai trò</th>
                                    <th>Tờ khai CT01</th>
                                    <th>Nhận phòng</th>
                                    <th style="text-align: right;">Thao tác BQL</th>
                                </tr>
                            </thead>
                            <tbody id="modalBedTableBody">
                                <!-- Sinh bằng JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Form Thêm giường phụ nhanh -->
                    <div id="addBedBoxWrapper" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 18px;">
                        <div id="addBedBoxHeader" style="font-weight: 700; font-size: 0.85rem; color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-plus-circle" style="color: var(--primary);"></i>
                            <span>Bổ sung thêm giường vào phòng này</span>
                        </div>
                        <form id="addBedForm" method="POST" action="<?= url('staff/rooms/add-bed') ?>" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap; margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" id="addBedModalFormMaPhong" name="maPhong" value="">
                            <div>
                                <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #475569; margin-bottom: 4px;">Ký hiệu giường (VD: G5, G6...):</label>
                                <input type="text" id="addBedModalFormKyHieu" name="kyHieuGiuong" class="btn-outline" placeholder="VD: G5" required style="padding: 7px 10px; border-radius: 6px; font-size: 0.85rem; width: 140px;">
                            </div>
                            <div>
                                <label style="display: block; font-size: 0.775rem; font-weight: 600; color: #475569; margin-bottom: 4px;">Vị trí tầng giường:</label>
                                <select name="viTriTangGiuong" class="btn-outline" style="padding: 7px 10px; border-radius: 6px; font-size: 0.85rem;">
                                    <option value="1">Tầng 1 (Giường dưới)</option>
                                    <option value="2">Tầng 2 (Giường trên)</option>
                                </select>
                            </div>
                            <button type="submit" id="btnAddBedSubmit" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 8px 16px; font-weight: 600; height: 35px;">
                                <i class="fa-solid fa-plus" style="margin-right: 4px;"></i> Thêm giường
                            </button>
                        </form>
                        <div id="addBedMaxNotice" style="display: none; background: #fff1f2; border: 1px solid #fecdd3; border-radius: 6px; padding: 12px 16px; color: #9f1239; font-size: 0.85rem;">
                            <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px; color: #e11d48;"></i>
                            <span id="addBedMaxNoticeText">Phòng đã đủ số lượng giường tối đa theo quy chuẩn loại phòng.</span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div style="padding: 12px 22px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeBedDetailModal()" style="border-radius: 6px; padding: 7px 18px;">
                        Đóng
                    </button>
                </div>
            </div>
        </div>

        <!-- MODAL XÁC NHẬN XÓA PHÒNG -->
        <div id="modalDeleteRoom" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); z-index: 999999; align-items: center; justify-content: center; padding: 16px;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); overflow: hidden; margin: auto;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #fef2f2;">
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #991b1b;">
                        Xác nhận Xóa Phòng
                    </h3>
                    <button type="button" onclick="closeModal('modalDeleteRoom')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b;">&times;</button>
                </div>

                <form method="POST" action="<?= url('staff/rooms/delete-room') ?>" style="padding: 20px; margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" id="deleteRoomInputMa" name="maPhong" value="">

                    <p style="font-size: 0.95rem; color: #334155; line-height: 1.6; margin-bottom: 14px;">
                        Bạn có chắc chắn muốn xóa phòng <strong id="deleteRoomDisplayMa" style="color: #dc2626;"></strong> cùng toàn bộ các giường trống liên kết không?
                    </p>

                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; font-size: 0.8rem; color: #92400e; margin-bottom: 18px;">
                        <strong>Quy tắc bảo toàn dữ liệu:</strong> Hệ thống sẽ từ chối xóa nếu phòng này đang có sinh viên cư trú hoặc lịch sử hợp đồng chưa thanh lý.
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline" onclick="closeModal('modalDeleteRoom')" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">Hủy</button>
                        <button type="submit" class="btn btn-danger" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px; background: #dc2626; color: #fff; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-trash-can"></i> Xác nhận Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL XÁC NHẬN XÓA SINH VIÊN KHỎI GIƯỜNG -->
        <div id="modalRemoveStudent" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(2px); z-index: 1000001; align-items: center; justify-content: center; padding: 16px;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 500px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4); overflow: hidden; margin: auto; border: 1px solid #fecaca;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #fee2e2; display: flex; justify-content: space-between; align-items: center; background: #fef2f2;">
                    <h3 style="margin: 0; font-size: 1.05rem; font-weight: 700; color: #991b1b;">
                        Xác nhận Xóa Sinh viên khỏi Giường
                    </h3>
                    <button type="button" onclick="closeModal('modalRemoveStudent')" style="background: none; border: none; font-size: 1.25rem; cursor: pointer; color: #64748b; line-height: 1;">&times;</button>
                </div>

                <form method="POST" action="<?= url('staff/rooms/remove-student') ?>" style="padding: 20px; margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" id="removeStudentInputGiuong" name="maGiuong" value="">
                    <input type="hidden" id="removeStudentInputPhong" name="maPhong" value="">
                    <input type="hidden" id="removeStudentInputMsv" name="maSinhVien" value="">

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px; font-size: 0.9rem;">
                        <div style="margin-bottom: 8px;">
                            Sinh viên: <strong id="removeStudentDisplayName" style="color: #0f172a; font-size: 0.95rem;"></strong>
                            <span style="color: var(--text-muted); font-size: 0.825rem;"> (MSSV: <code id="removeStudentDisplayMssv"></code>)</span>
                        </div>
                        <div style="margin-bottom: 8px; color: #475569;">
                            Trường ĐH: <strong id="removeStudentDisplayTruong"></strong>
                        </div>
                        <div style="color: #475569;">
                            Vị trí: Giường <strong id="removeStudentDisplayBed" style="color: var(--primary);"></strong> &bull; Phòng <strong id="removeStudentDisplayRoom" style="color: var(--primary);"></strong>
                        </div>
                    </div>

                    <p style="font-size: 0.9rem; color: #334155; line-height: 1.5; margin-bottom: 14px;">
                        Bạn có chắc chắn muốn hủy cư trú và xóa sinh viên này khỏi giường không?
                    </p>

                    <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 10px 14px; font-size: 0.8rem; color: #92400e; margin-bottom: 18px; line-height: 1.5;">
                        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 4px;"></i>
                        <strong>Lưu ý hệ thống:</strong> Hợp đồng nội trú của sinh viên tại giường này sẽ được chuyển sang <b>Đã thanh lý</b>, giường sẽ trở về trạng thái <b>Trống</b>. Nếu sinh viên đang giữ vai trò Trưởng phòng, vai trò sẽ được hủy tự động.
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline" onclick="closeModal('modalRemoveStudent')" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 16px;">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="btn btn-danger" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px; background: #dc2626; color: #fff; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-user-minus"></i> Xác nhận Xóa SV
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL CẬP NHẬT THÔNG TIN TÒA NHÀ (RU TÒA NHÀ) -->
        <!-- ========================================================================= -->
        <div id="modalUpdateToa" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b;">
                        <span>Cập nhật Thông tin Tòa nhà</span>
                    </div>
                    <button type="button" onclick="closeModal('modalUpdateToa')" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="<?= url('staff/rooms/update-toa') ?>" method="POST" style="margin: 0; padding: 20px;">
                    <?= csrf_field() ?>

                    <?php if (!empty($isAllToas) && count($availableToas) > 1): ?>
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #334155;">
                                Chọn Tòa nhà cần cập nhật <span style="color:#dc2626;">*</span>
                            </label>
                            <select id="modalSelectToa" name="maToa" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem; font-weight: 600; background: #fff;" onchange="onSelectToaToUpdate(this.value)">
                                <?php foreach ($availableToas as $at): ?>
                                    <option value="<?= htmlspecialchars($at['MaToaNha']) ?>">
                                        <?= htmlspecialchars($formatTenToa($at['TenToaNha'] ?? $at['MaToaNha'], $at['MaToaNha'])) ?> (<?= htmlspecialchars($at['MaToaNha']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="maToa" value="<?= htmlspecialchars($maToa) ?>">
                        <div style="margin-bottom: 14px;">
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #334155;">
                                Mã Tòa nhà định danh
                            </label>
                            <input type="text" class="btn-outline" value="<?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?> (<?= htmlspecialchars($maToa) ?>)" readonly style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem; background: #f1f5f9; font-weight: 700; color: #1e293b;">
                        </div>
                    <?php endif; ?>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #334155;">
                            Tên hiển thị Tòa nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <input type="text" id="modalTenToaNha" name="tenToaNha" class="btn-outline" value="<?= htmlspecialchars($formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa)) ?>" required style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #334155;">
                            Giới tính quy định lưu trú <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="modalGioiTinhQuyDinh" name="gioiTinhQuyDinh" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Nam" <?= (($toaInfo['GioiTinhQuyDinh'] ?? 'Nam') === 'Nam') ? 'selected' : '' ?>>Nam (Ký túc xá Nam)</option>
                            <option value="Nữ" <?= (($toaInfo['GioiTinhQuyDinh'] ?? '') === 'Nữ') ? 'selected' : '' ?>>Nữ (Ký túc xá Nữ)</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 18px;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #334155;">
                            Trạng thái vận hành Tòa nhà <span style="color:#dc2626;">*</span>
                        </label>
                        <select id="modalTrangThaiToaNha" name="trangThaiToaNha" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                            <option value="Đang hoạt động" <?= (($toaInfo['TrangThaiToaNha'] ?? '') === 'Đang hoạt động') ? 'selected' : '' ?>>Đang hoạt động</option>
                            <option value="Tạm khóa bảo trì" <?= (($toaInfo['TrangThaiToaNha'] ?? '') === 'Tạm khóa bảo trì') ? 'selected' : '' ?>>Tạm khóa bảo trì</option>
                        </select>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline" onclick="closeModal('modalUpdateToa')" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 18px;">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="btn btn-primary" style="height: 38px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; padding: 8px 20px; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-floppy-disk"></i> Lưu cập nhật Tòa nhà
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- JAVASCRIPT XỬ LÝ PHÂN TRANG, BỘ LỌC, EXPORT, IMPORT VÀ MODAL GIƯỜNG -->
        <script>
            const currentMaToa = <?= json_encode($maToa) ?>;
            const currentScopeLevel = <?= json_encode($scope['level'] ?? 'TOA') ?>;
            const allAvailableToas = <?= json_encode($availableToas ?? [], JSON_UNESCAPED_UNICODE) ?>;
            const roomsData = <?= json_encode($phongList ?? [], JSON_UNESCAPED_UNICODE) ?>;
            const bedsData = <?= json_encode($giuongByPhong ?? [], JSON_UNESCAPED_UNICODE) ?>;

            function onToaChange(toaCode) {
                const url = new URL(window.location.href);
                if (!toaCode) {
                    url.searchParams.delete('toa');
                } else {
                    url.searchParams.set('toa', toaCode);
                }
                window.location.href = url.toString();
            }

            function onKhuChange(khuCode) {
                const url = new URL(window.location.href);
                if (!khuCode) {
                    url.searchParams.delete('khu');
                } else {
                    url.searchParams.set('khu', khuCode);
                }
                url.searchParams.delete('cum');
                url.searchParams.delete('toa');
                window.location.href = url.toString();
            }

            function onCumChange(cumCode) {
                const url = new URL(window.location.href);
                if (!cumCode) {
                    url.searchParams.delete('cum');
                } else {
                    url.searchParams.set('cum', cumCode);
                }
                url.searchParams.delete('toa');
                window.location.href = url.toString();
            }

            function onSelectToaToUpdate(toaCode) {
                if (!toaCode) return;
                const found = allAvailableToas.find(t => t.MaToaNha === toaCode);
                if (found) {
                    const tenInput = document.getElementById('modalTenToaNha');
                    const gtSelect = document.getElementById('modalGioiTinhQuyDinh');
                    const ttSelect = document.getElementById('modalTrangThaiToaNha');
                    if (tenInput) tenInput.value = found.TenToaNha || found.MaToaNha;
                    if (gtSelect) gtSelect.value = found.GioiTinhQuyDinh || 'Nam';
                    if (ttSelect) ttSelect.value = found.TrangThaiToaNha || 'Đang hoạt động';
                }
            }

            function openUpdateToaModal() {
                const el = document.getElementById('modalUpdateToa');
                if (el) {
                    el.style.display = 'flex';
                    const select = document.getElementById('modalSelectToa');
                    if (select && select.value) {
                        onSelectToaToUpdate(select.value);
                    }
                }
            }

            function onRoomFormToaChange(toaCode) {
                const found = allAvailableToas.find(t => t.MaToaNha === toaCode);
                const tongTang = found ? parseInt(found.TongSoTang || 5) : 5;
                const tangSelect = document.getElementById('roomTang');
                if (tangSelect) {
                    const curTang = parseInt(tangSelect.value) || 1;
                    tangSelect.innerHTML = '';
                    for (let t = 1; t <= tongTang; t++) {
                        const opt = document.createElement('option');
                        opt.value = t;
                        opt.innerText = 'Tầng ' + t;
                        if (t === curTang) opt.selected = true;
                        tangSelect.appendChild(opt);
                    }
                }
                const maxFloorLabel = document.getElementById('roomFormMaxFloor');
                if (maxFloorLabel) maxFloorLabel.innerText = tongTang;
                updateRoomCodePreview();
            }

            // =========================================================================
            // 1. PHÂN TRANG VÀ LỌC TÌM KIẾM PHÒNG (REAL-TIME PAGINATION & FILTER)
            // =========================================================================
            let roomCurrentPage = 1;
            let roomPageSize = 10;
            let roomMatchedRows = [];

            function filterRooms() {
                const searchInput = document.getElementById('searchRoomInput');
                const floorSelect = document.getElementById('filterRoomFloor');
                const typeSelect = document.getElementById('filterRoomType');
                const statusSelect = document.getElementById('filterRoomStatus');
                const tableBody = document.getElementById('roomTableBody');

                if (!tableBody) return;

                const keyword = searchInput ? searchInput.value.trim().toLocaleLowerCase('vi') : '';
                const selectedFloor = floorSelect ? floorSelect.value : '';
                const selectedType = typeSelect ? typeSelect.value : '';
                const selectedStatus = statusSelect ? statusSelect.value : '';

                const allRows = tableBody.querySelectorAll('tr.room-row');
                roomMatchedRows = [];

                allRows.forEach(row => {
                    const text = row.textContent.toLocaleLowerCase('vi');
                    const tang = row.getAttribute('data-tang') || '';
                    const loai = row.getAttribute('data-loaiphong') || '';
                    const status = row.getAttribute('data-status') || '';

                    const matchesKeyword = !keyword || text.includes(keyword);
                    const matchesFloor = !selectedFloor || tang === selectedFloor;
                    const matchesType = !selectedType || loai === selectedType;
                    const matchesStatus = !selectedStatus || status === selectedStatus;

                    if (matchesKeyword && matchesFloor && matchesType && matchesStatus) {
                        roomMatchedRows.push(row);
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Xử lý dòng thông báo khi không có kết quả
                let noResultRow = document.getElementById('noRoomSearchResult');
                if (roomMatchedRows.length === 0 && allRows.length > 0) {
                    if (!noResultRow) {
                        noResultRow = document.createElement('tr');
                        noResultRow.id = 'noRoomSearchResult';
                        noResultRow.innerHTML = '<td colspan="8" style="text-align:center; padding:28px; color:var(--text-muted);"><i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy phòng nào phù hợp với tiêu chí lọc.</td>';
                        tableBody.appendChild(noResultRow);
                    }
                    noResultRow.style.display = '';
                } else if (noResultRow) {
                    noResultRow.style.display = 'none';
                }

                renderRoomPagination();
            }

            function renderRoomPagination() {
                const total = roomMatchedRows.length;
                let totalPages = roomPageSize > 0 ? Math.ceil(total / roomPageSize) : 1;
                if (totalPages < 1) totalPages = 1;

                if (roomCurrentPage > totalPages) roomCurrentPage = totalPages;
                if (roomCurrentPage < 1) roomCurrentPage = 1;

                const startIdx = roomPageSize > 0 ? (roomCurrentPage - 1) * roomPageSize : 0;
                const endIdx = roomPageSize > 0 ? startIdx + roomPageSize : total;

                roomMatchedRows.forEach((row, idx) => {
                    if (idx >= startIdx && idx < endIdx) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Cập nhật thông tin phân trang
                const infoEl = document.getElementById('roomPaginationInfo');
                if (infoEl) {
                    if (total === 0) {
                        infoEl.innerText = 'Không có phòng nào phù hợp';
                    } else {
                        const displayStart = startIdx + 1;
                        const displayEnd = Math.min(endIdx, total);
                        infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> phòng (Trang <b>${roomCurrentPage}</b> / <b>${totalPages}</b>)`;
                    }
                }

                const totalBadge = document.getElementById('roomTotalBadge');
                if (totalBadge) {
                    totalBadge.innerText = total + ' phòng';
                }

                // Cập nhật số lượng trên nút Xuất Excel
                const exportBtnText = document.getElementById('btnExportText');
                if (exportBtnText) {
                    const allRoomRows = document.querySelectorAll('#roomTableBody tr.room-row');
                    if (total === allRoomRows.length) {
                        exportBtnText.innerText = `Xuất Excel (${total})`;
                    } else {
                        exportBtnText.innerText = `Xuất Excel (${total})`;
                    }
                }

                // Cập nhật các nút phân trang
                const controlsEl = document.getElementById('roomPaginationControls');
                if (!controlsEl) return;

                if (totalPages <= 1) {
                    controlsEl.innerHTML = '';
                    return;
                }

                let html = '';

                // Nút Đầu (<<)
                const disabledFirst = roomCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(1)" ${disabledFirst} title="Trang đầu"><i class="fa-solid fa-angles-left"></i></button>`;

                // Nút Trước (<)
                const disabledPrev = roomCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(${roomCurrentPage - 1})" ${disabledPrev} title="Trang trước"><i class="fa-solid fa-angle-left"></i></button>`;

                // Các nút số trang (hiển thị thông minh)
                let startPage = Math.max(1, roomCurrentPage - 2);
                let endPage = Math.min(totalPages, roomCurrentPage + 2);

                if (startPage > 1) {
                    html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(1)" style="padding: 5px 10px; border-radius: 6px;">1</button>`;
                    if (startPage > 2) {
                        html += `<span style="padding: 0 4px; color: var(--text-muted);">...</span>`;
                    }
                }

                for (let p = startPage; p <= endPage; p++) {
                    if (p === roomCurrentPage) {
                        html += `<button type="button" class="btn btn-primary btn-sm" style="padding: 5px 10px; border-radius: 6px; font-weight: 700;">${p}</button>`;
                    } else {
                        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(${p})" style="padding: 5px 10px; border-radius: 6px;">${p}</button>`;
                    }
                }

                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        html += `<span style="padding: 0 4px; color: var(--text-muted);">...</span>`;
                    }
                    html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(${totalPages})" style="padding: 5px 10px; border-radius: 6px;">${totalPages}</button>`;
                }

                // Nút Sau (>)
                const disabledNext = roomCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(${roomCurrentPage + 1})" ${disabledNext} title="Trang sau"><i class="fa-solid fa-angle-left fa-rotate-180"></i></button>`;

                // Nút Cuối (>>)
                const disabledLast = roomCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRoomPage(${totalPages})" ${disabledLast} title="Trang cuối"><i class="fa-solid fa-angles-right"></i></button>`;

                controlsEl.innerHTML = html;
            }

            function goToRoomPage(page) {
                const total = roomMatchedRows.length;
                let totalPages = roomPageSize > 0 ? Math.ceil(total / roomPageSize) : 1;
                if (totalPages < 1) totalPages = 1;
                if (page < 1) page = 1;
                if (page > totalPages) page = totalPages;

                roomCurrentPage = page;
                renderRoomPagination();

                const tableCard = document.getElementById('tableStaffRooms');
                if (tableCard) {
                    tableCard.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }

            function changeRoomPageSize(val) {
                roomPageSize = parseInt(val, 10);
                roomCurrentPage = 1;
                renderRoomPagination();
            }

            function clearRoomFilters() {
                if (document.getElementById('searchRoomInput')) document.getElementById('searchRoomInput').value = '';
                if (document.getElementById('filterRoomFloor')) document.getElementById('filterRoomFloor').value = '';
                if (document.getElementById('filterRoomType')) document.getElementById('filterRoomType').value = '';
                if (document.getElementById('filterRoomStatus')) document.getElementById('filterRoomStatus').value = '';
                roomCurrentPage = 1;
                filterRooms();
            }

            // =========================================================================
            // 2. XUẤT FILE CSV THEO ĐÚNG KẾT QUẢ ĐANG LỌC (GIỐNG MẪU USERS)
            // =========================================================================
            function exportFilteredRooms() {
                if (!roomMatchedRows || roomMatchedRows.length === 0) {
                    alert('Không có phòng nào phù hợp với bộ lọc hiện tại để xuất file!');
                    return;
                }

                const matchedIds = [];
                roomMatchedRows.forEach(row => {
                    const id = row.getAttribute('data-id');
                    if (id) matchedIds.push(id);
                });

                if (matchedIds.length === 0) {
                    alert('Không tìm thấy dữ liệu phòng hợp lệ để xuất!');
                    return;
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '<?= url("staff/rooms/export") ?>';
                form.style.display = 'none';

                const csrfInp = document.createElement('input');
                csrfInp.type = 'hidden';
                csrfInp.name = 'csrf_token';
                csrfInp.value = '<?= csrf_token() ?>';
                form.appendChild(csrfInp);

                const idsInp = document.createElement('input');
                idsInp.type = 'hidden';
                idsInp.name = 'ids';
                idsInp.value = matchedIds.join(',');
                form.appendChild(idsInp);

                const toaInp = document.createElement('input');
                toaInp.type = 'hidden';
                toaInp.name = 'toa';
                toaInp.value = currentMaToa;
                form.appendChild(toaInp);

                document.body.appendChild(form);
                form.submit();
            }

            // =========================================================================
            // 3. QUẢN LÝ BIỂU MẪU THÊM MỚI / CHỈNH SỬA PHÒNG (#roomFormCard)
            // =========================================================================
            function toggleRoomForm() {
                const card = document.getElementById('roomFormCard');
                if (card.style.display === 'none' || card.style.display === '') {
                    resetRoomForm();
                    card.style.display = 'block';
                    card.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                    document.getElementById('roomSoPhong').focus();
                } else {
                    hideRoomForm();
                }
            }

            function hideRoomForm() {
                const card = document.getElementById('roomFormCard');
                if (card) card.style.display = 'none';
            }

            document.getElementById('roomActionForm')?.addEventListener('submit', function() {
                const toaSelect = document.getElementById('roomMaToaSelect');
                if (toaSelect) toaSelect.disabled = false;
            });

            function resetRoomForm() {
                document.getElementById('roomIsEdit').value = '0';
                document.getElementById('roomMaPhong').value = '';
                document.getElementById('roomSoPhong').value = '';
                document.getElementById('roomSoPhong').readOnly = false;
                document.getElementById('roomTang').value = '1';
                document.getElementById('roomMaLoaiPhong').value = '';
                document.getElementById('roomTrangThaiPhong').value = 'Còn chỗ';

                const toaSelect = document.getElementById('roomMaToaSelect');
                if (toaSelect) {
                    toaSelect.disabled = false;
                    toaSelect.style.pointerEvents = 'auto';
                    toaSelect.style.background = '#fff';
                    onRoomFormToaChange(toaSelect.value);
                }
                const activeToa = toaSelect ? toaSelect.value : currentMaToa;
                document.getElementById('roomCodePreview').value = (activeToa || 'TOA') + '.---';
                document.getElementById('formTitleText').innerText = 'Thêm mới Phòng ở' + (activeToa ? ' (Tòa ' + activeToa + ')' : '');
                document.getElementById('btnSubmitRoomText').innerText = 'Lưu phòng & Khởi tạo giường';
                document.getElementById('roomAutoBedNotice').style.display = 'block';

                const tpBox = document.getElementById('roomTruongPhongBox');
                if (tpBox) tpBox.style.display = 'none';
                const tpSelect = document.getElementById('roomTruongPhong');
                if (tpSelect) tpSelect.innerHTML = '<option value="">-- Chưa chỉ định Trưởng phòng --</option>';
            }

            function updateRoomCodePreview() {
                const isEdit = document.getElementById('roomIsEdit').value === '1';
                if (isEdit) return;

                const toaSelect = document.getElementById('roomMaToaSelect');
                const maToa = toaSelect ? toaSelect.value : (currentMaToa || (allAvailableToas[0] ? allAvailableToas[0].MaToaNha : 'A01'));
                const soPhong = document.getElementById('roomSoPhong').value.trim();
                const preview = document.getElementById('roomCodePreview');
                if (soPhong) {
                    preview.value = (maToa || 'TOA') + '.' + soPhong;
                } else {
                    preview.value = (maToa || 'TOA') + '.---';
                }
            }

            function editRoom(room) {
                const card = document.getElementById('roomFormCard');
                card.style.display = 'block';

                document.getElementById('roomIsEdit').value = '1';
                document.getElementById('roomMaPhong').value = room.MaPhong;
                document.getElementById('roomSoPhong').value = room.SoPhong;
                document.getElementById('roomSoPhong').readOnly = true;

                const toaSelect = document.getElementById('roomMaToaSelect');
                if (toaSelect) {
                    toaSelect.value = room.MaToaNha;
                    toaSelect.style.pointerEvents = 'none';
                    toaSelect.style.background = '#f1f5f9';
                    onRoomFormToaChange(room.MaToaNha);
                }

                document.getElementById('roomTang').value = room.Tang;
                document.getElementById('roomMaLoaiPhong').value = room.MaLoaiPhong;

                let curStatus = (room.TrangThaiPhong || 'Còn chỗ').trim();
                if (curStatus.toLowerCase().includes('trì') || curStatus.toLowerCase().includes('khóa')) {
                    curStatus = 'Bảo trì';
                } else if (curStatus.toLowerCase().includes('đầy') || curStatus.toLowerCase().includes('day')) {
                    curStatus = 'Đã đầy';
                } else {
                    curStatus = 'Còn chỗ';
                }
                const stSelect = document.getElementById('roomTrangThaiPhong');
                if (stSelect) stSelect.value = curStatus;

                document.getElementById('roomCodePreview').value = room.MaPhong;

                document.getElementById('formTitleText').innerText = 'Chỉnh sửa thông tin Phòng ' + room.MaPhong;
                document.getElementById('btnSubmitRoomText').innerText = 'Lưu cập nhật phòng';
                document.getElementById('roomAutoBedNotice').style.display = 'none';

                // Nạp danh sách sinh viên đang cư trú vào dropdown chọn Trưởng phòng
                const tpBox = document.getElementById('roomTruongPhongBox');
                const tpSelect = document.getElementById('roomTruongPhong');
                const beds = bedsData[room.MaPhong] || [];
                const residents = beds.filter(b => !!b.MaSinhVien);

                if (residents.length > 0) {
                    let optHtml = '<option value="">-- Chưa chỉ định Trưởng phòng --</option>';
                    residents.forEach(b => {
                        const isSelected = (room.TruongPhong && room.TruongPhong === b.MaSinhVien) ? 'selected' : '';
                        optHtml += `<option value="${b.MaSinhVien}" ${isSelected}> ${b.HoVaTen} (MSSV: ${b.MSSVTaiTruong || b.MaSinhVien} - Giường ${b.KyHieuGiuong})</option>`;
                    });
                    tpSelect.innerHTML = optHtml;
                    tpBox.style.display = 'block';
                } else {
                    tpSelect.innerHTML = '<option value="">-- Chưa chỉ định Trưởng phòng (Phòng chưa có SV) --</option>';
                    tpBox.style.display = 'none';
                }

                card.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            // =========================================================================
            // 4. MODAL CHI TIẾT SƠ ĐỒ GIƯỜNG & CƯ TRÚ THEO PHÒNG (#bedDetailModal)
            // =========================================================================
            function openBedDetailModal(maPhong) {
                const room = roomsData.find(r => r.MaPhong === maPhong);
                const beds = bedsData[maPhong] || [];

                document.getElementById('modalBedRoomCode').innerText = maPhong;
                document.getElementById('addBedModalFormMaPhong').value = maPhong;

                const maxCap = room ? parseInt(room.SoGiuongToiDa || 4) : 4;

                // RÀNG BUỘC: Kiểm tra số giường tối đa theo Loại phòng
                const addBedForm = document.getElementById('addBedForm');
                const addBedHeader = document.getElementById('addBedBoxHeader');
                const maxNotice = document.getElementById('addBedMaxNotice');
                const maxNoticeText = document.getElementById('addBedMaxNoticeText');

                if (beds.length >= maxCap) {
                    if (addBedForm) addBedForm.style.display = 'none';
                    if (addBedHeader) addBedHeader.style.display = 'none';
                    if (maxNotice) {
                        maxNotice.style.display = 'block';
                        maxNoticeText.innerHTML = `<strong>Phòng đã đủ số giường tối đa (${beds.length}/${maxCap} giường)</strong> theo quy chuẩn Loại phòng <strong>${room ? (room.TenLoaiPhong || room.MaLoaiPhong) : ''}</strong>. Hệ thống không cho phép tạo thêm giường!`;
                    }
                } else {
                    if (addBedForm) addBedForm.style.display = 'flex';
                    if (addBedHeader) addBedHeader.style.display = 'flex';
                    if (maxNotice) maxNotice.style.display = 'none';
                    document.getElementById('addBedModalFormKyHieu').value = 'G' + (beds.length + 1);
                }

                // Summary info
                const summaryEl = document.getElementById('modalBedRoomSummary');
                const occupied = room ? parseInt(room.SoSVDangO || 0) : 0;
                const fillPercent = maxCap > 0 ? Math.round((occupied / maxCap) * 100) : 0;

                let summaryHtml = `
                    <div>
                        <strong>Tòa: ${currentMaToa}</strong> &bull; Tầng: <strong>${room ? room.Tang : 1}</strong> &bull; 
                        Loại phòng: <strong>${room ? (room.TenLoaiPhong || room.MaLoaiPhong) : ''}</strong> &bull; 
                        Trạng thái: <span class="badge ${room && (room.TrangThaiPhong === 'Bảo trì' || room.TrangThaiPhong === 'Đang bảo trì' || room.TrangThaiPhong === 'Khóa bảo trì') ? 'badge-warning' : (room && (room.TrangThaiPhong === 'Đã đầy' || room.TrangThaiPhong === 'Đầy chỗ') ? 'badge-success' : 'badge-info')}">${room ? (room.TrangThaiPhong.includes('trì') || room.TrangThaiPhong.includes('khóa') ? 'Bảo trì' : (room.TrangThaiPhong.includes('đầy') ? 'Đã đầy' : 'Còn chỗ')) : ''}</span>
                    </div>
                    <div>
                        Sĩ số: <strong style="color: var(--primary); font-size: 1rem;">${occupied} / ${maxCap} SV</strong> (${fillPercent}%) &bull; 
                        Trưởng phòng: <strong>${room && room.TruongPhong ? (room.TenTruongPhong ? room.TenTruongPhong + ' (' + room.TruongPhong + ')' : room.TruongPhong) : 'Chưa chỉ định'}</strong>
                    </div>
                `;

                if (occupied >= maxCap) {
                    summaryHtml += `
                        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 8px 12px; margin-top: 8px; color: #991b1b; font-size: 0.8rem; display: flex; align-items: center; gap: 6px;">
                            <span><strong>Cảnh báo sức chứa:</strong> Phòng đã đạt đủ số lượng sinh viên tối đa cho phép (${occupied}/${maxCap} SV). Không thể tiếp nhận thêm sinh viên vào phòng này!</span>
                        </div>
                    `;
                }

                summaryEl.innerHTML = summaryHtml;

                // Bảng giường
                const tbody = document.getElementById('modalBedTableBody');
                if (beds.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 24px; font-style: italic;">
                                Phòng này chưa có giường nào được khởi tạo. Bạn có thể thêm giường mới ở bên dưới.
                            </td>
                        </tr>
                    `;
                } else {
                    let html = '';
                    beds.forEach(g => {
                        const isOccupied = !!g.MaSinhVien || g.TrangThaiGiuong === 'Đã có người ở';
                        const isLocked = g.TrangThaiGiuong === 'Đang bảo trì' || g.TrangThaiGiuong === 'Khóa bảo trì' || (g.TrangThaiGiuong && g.TrangThaiGiuong.includes('trì'));
                        const isLeader = room && room.TruongPhong && room.TruongPhong === g.MaSinhVien;
                        const isCheckedIn = !!g.NgayNhanPhong;
                        const hasCT01 = parseInt(g.DaNopMauCT01 || 0) === 1;

                        const rowBg = isLocked ? 'background:#fffbeb;' : (isOccupied ? 'background:#ffffff;' : 'background:#fafafa;');

                        html += `<tr style="${rowBg}">`;
                        // Mã giường
                        html += `
                            <td>
                                <b style="color: #0f172a;">${g.KyHieuGiuong}</b>
                                <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace;">${g.MaGiuong}</div>
                            </td>
                        `;
                        // Tầng giường
                        html += `<td>${parseInt(g.ViTriTangGiuong) === 2 ? 'Tầng 2 (Trên)' : 'Tầng 1 (Dưới)'}</td>`;

                        if (isOccupied) {
                            // SV lưu trú
                            html += `
                                <td>
                                    <b>${g.HoVaTen || 'Sinh viên'}</b>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">MSSV: <strong>${g.MSSVTaiTruong || g.MaSinhVien}</strong> &bull; ${g.GioiTinh || ''}</div>
                                </td>
                                <td><span style="font-size: 0.8rem;">${g.TenTruong || 'ĐHQG-HCM'}</span></td>
                                <td>
                                    <div style="font-family: monospace; font-size: 0.8rem;">${g.SoCCCD || '---'}</div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">${g.SoDienThoai || '---'}</div>
                                </td>
                                <td>
                                    ${isLeader ? '<span class="badge badge-success" style="font-size: 0.75rem;"> Trưởng phòng</span>' : '<span style="color: var(--text-muted); font-size: 0.8rem;">Thành viên</span>'}
                                </td>
                                <td>
                                    ${hasCT01 ? '<span class="badge badge-success" style="font-size: 0.75rem;"> Đã nộp CT01</span>' : '<span class="badge badge-warning" style="font-size: 0.75rem;">⏳ Chưa nộp</span>'}
                                </td>
                                <td>
                                    ${isCheckedIn ? `<span style="color: #166534; font-weight: 600; font-size: 0.8rem;">${g.NgayNhanPhong}</span>` : '<span class="badge badge-danger" style="font-size: 0.75rem;">Chưa nhận phòng</span>'}
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: flex; gap: 4px; justify-content: flex-end; flex-wrap: wrap;">
                            `;

                            // Nút nhận phòng thực tế
                            if (!isCheckedIn && g.MaHopDong) {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/confirm-checkin') ?>" style="display:inline;" onsubmit="return confirm('Xác nhận sinh viên ${g.HoVaTen} đã đến nhận phòng thực tế?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHopDong" value="${g.MaHopDong}">
                                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem;" title="Xác nhận nhận phòng thực tế">
                                            🔑 Nhận phòng
                                        </button>
                                    </form>
                                `;
                            }

                            // Nút gán/hủy Trưởng phòng
                            if (isLeader) {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/assign-leader') ?>" style="display:inline;" onsubmit="return confirm('Xác nhận hủy vai trò Trưởng phòng của sinh viên ${g.HoVaTen}?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maPhong" value="${maPhong}">
                                        <input type="hidden" name="action" value="remove">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 8px; font-size: 0.75rem; color: #991b1b; border-color: #fecaca; background: #fef2f2;" title="Hủy vai trò Trưởng phòng">
                                            Hủy Trưởng phòng
                                        </button>
                                    </form>
                                `;
                            } else {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/assign-leader') ?>" style="display:inline;" onsubmit="return confirm('Chỉ định sinh viên ${g.HoVaTen} làm Trưởng phòng ${maPhong}?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maPhong" value="${maPhong}">
                                        <input type="hidden" name="maSinhVien" value="${g.MaSinhVien}">
                                        <input type="hidden" name="action" value="assign">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 8px; font-size: 0.75rem; color: #15803d; border-color: #86efac; background: #f0fdf4;" title="Chỉ định sinh viên này làm Trưởng phòng">
                                           Chọn làm Trưởng phòng
                                        </button>
                                    </form>
                                `;
                            }

                            // Nút toggle CT01
                            if (g.MaHopDong) {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/toggle-ct01') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maHopDong" value="${g.MaHopDong}">
                                        <input type="hidden" name="status" value="${hasCT01 ? '0' : '1'}">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem;" title="${hasCT01 ? 'Hủy tích nhận CT01' : 'Tích đã nộp Mẫu CT01'}">
                                            ${hasCT01 ? 'Hủy CT01' : '📥 Thu CT01'}
                                        </button>
                                    </form>
                                `;
                            }

                            // Nút Xóa sinh viên khỏi giường (Mở Modal xác nhận)
                            const safeHoTen = (g.HoVaTen || 'Sinh viên').replace(/'/g, "\\'");
                            const safeMssv = (g.MSSVTaiTruong || g.MaSinhVien || '').replace(/'/g, "\\'");
                            const safeTruong = (g.TenTruong || 'ĐHQG-HCM').replace(/'/g, "\\'");
                            const safeKyHieu = (g.KyHieuGiuong || '').replace(/'/g, "\\'");

                            html += `
                                <button type="button" class="btn btn-outline btn-sm" 
                                        onclick="openRemoveStudentModal('${g.MaGiuong}', '${maPhong}', '${g.MaSinhVien}', '${safeHoTen}', '${safeMssv}', '${safeTruong}', '${safeKyHieu}')"
                                        style="border-radius: 4px; padding: 3px 8px; font-size: 0.75rem; color: #b91c1c; border-color: #fca5a5; background: #fff5f5;" 
                                        title="Xóa sinh viên khỏi giường này (Mở hộp thoại xác nhận)">
                                    <i class="fa-solid fa-user-minus" style="margin-right: 3px;"></i> Xóa SV
                                </button>
                            `;

                            html += `</div></td>`;
                        } else {
                            // Giường trống / bảo trì
                            html += `
                                <td colspan="4" style="color: var(--text-muted); font-style: italic;">
                                    ${isLocked ? '<span style="color:#d97706; font-weight:600;"><i class="fa-solid fa-lock"></i> Giường đang bảo trì</span>' : 'Giường trống'}
                                </td>
                                <td>—</td>
                                <td>
                                    <span class="badge ${isLocked ? 'badge-warning' : 'badge-info'}" style="font-size: 0.75rem;">${g.TrangThaiGiuong}</span>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <div style="display: flex; gap: 4px; justify-content: flex-end;">
                            `;

                            if (isLocked) {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/update-bed-status') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maGiuong" value="${g.MaGiuong}">
                                        <input type="hidden" name="trangThaiGiuong" value="Trống">
                                        <button type="submit" class="btn btn-success btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem;" title="Mở lại giường">
                                            <i class="fa-solid fa-lock-open"></i> Mở lại
                                        </button>
                                    </form>
                                `;
                            } else {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/update-bed-status') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maGiuong" value="${g.MaGiuong}">
                                        <input type="hidden" name="trangThaiGiuong" value="Đang bảo trì">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem;" title="Bảo trì giường">
                                            <i class="fa-solid fa-lock"></i> Bảo trì
                                        </button>
                                    </form>
                                `;
                            }

                            if (!g.MaHopDong) {
                                html += `
                                    <form method="POST" action="<?= url('staff/rooms/delete-bed') ?>" style="display:inline;" onsubmit="return confirm('Xác nhận xóa giường ${g.KyHieuGiuong}?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="maGiuong" value="${g.MaGiuong}">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem; color:#dc2626; border-color:#fecaca;" title="Xóa giường trống này">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                `;
                            }

                            html += `</div></td>`;
                        }

                        html += `</tr>`;
                    });
                    tbody.innerHTML = html;
                }

                document.getElementById('bedDetailModal').style.display = 'flex';
            }

            function closeBedDetailModal() {
                const el = document.getElementById('bedDetailModal');
                if (el) el.style.display = 'none';
            }

            // =========================================================================
            // 5. CÁC HÀM XỬ LÝ MODAL KHÁC
            // =========================================================================
            function openImportRoomModal() {
                document.getElementById('importRoomModal').style.display = 'flex';
            }

            function closeImportRoomModal() {
                document.getElementById('importRoomModal').style.display = 'none';
            }

            function openDeleteRoomModal(maPhong) {
                document.getElementById('deleteRoomDisplayMa').innerText = maPhong;
                document.getElementById('deleteRoomInputMa').value = maPhong;
                document.getElementById('modalDeleteRoom').style.display = 'flex';
            }

            function openRemoveStudentModal(maGiuong, maPhong, maSinhVien, hoTen, mssv, tenTruong, kyHieuGiuong) {
                document.getElementById('removeStudentInputGiuong').value = maGiuong;
                document.getElementById('removeStudentInputPhong').value = maPhong;
                document.getElementById('removeStudentInputMsv').value = maSinhVien;

                document.getElementById('removeStudentDisplayName').innerText = hoTen || 'Sinh viên';
                document.getElementById('removeStudentDisplayMssv').innerText = mssv || maSinhVien;
                document.getElementById('removeStudentDisplayTruong').innerText = tenTruong || 'ĐHQG-HCM';
                document.getElementById('removeStudentDisplayBed').innerText = kyHieuGiuong || maGiuong;
                document.getElementById('removeStudentDisplayRoom').innerText = maPhong;

                document.getElementById('modalRemoveStudent').style.display = 'flex';
            }

            function closeModal(modalId) {
                const el = document.getElementById(modalId);
                if (el) el.style.display = 'none';
            }

            // Phím Escape để đóng modal (ưu tiên modal xác nhận trên cùng trước)
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const removeModal = document.getElementById('modalRemoveStudent');
                    if (removeModal && removeModal.style.display === 'flex') {
                        closeModal('modalRemoveStudent');
                        return;
                    }
                    const delRoomModal = document.getElementById('modalDeleteRoom');
                    if (delRoomModal && delRoomModal.style.display === 'flex') {
                        closeModal('modalDeleteRoom');
                        return;
                    }
                    closeBedDetailModal();
                    closeImportRoomModal();
                }
            });

            // Đóng modal khi click ra ngoài vùng backdrop
            window.addEventListener('click', function(e) {
                const importModal = document.getElementById('importRoomModal');
                if (e.target === importModal) {
                    closeImportRoomModal();
                }
                const removeModal = document.getElementById('modalRemoveStudent');
                if (e.target === removeModal) {
                    closeModal('modalRemoveStudent');
                }
                const delRoomModal = document.getElementById('modalDeleteRoom');
                if (e.target === delRoomModal) {
                    closeModal('modalDeleteRoom');
                }
                const bedModal = document.getElementById('bedDetailModal');
                if (e.target === bedModal) {
                    closeBedDetailModal();
                }
            });

            // Khởi tạo phân trang và lọc khi trang được tải xong
            document.addEventListener('DOMContentLoaded', function() {
                filterRooms();
            });
        </script>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = !empty($isAllowed)
    ? "Sơ đồ Phòng & Giường - " . (!empty($isAllToas) ? 'Tất cả Tòa nhà' : $formatTenToa($toaInfo['TenToaNha'] ?? $maToa, $maToa ?? ''))
    : "Từ chối truy cập - Sơ đồ Phòng & Giường KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>