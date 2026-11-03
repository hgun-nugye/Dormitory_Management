<?php
ob_start();
?>

<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 20px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Quản lý Tòa nhà</span>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 600;">Sơ đồ Phòng & Giường</span>
        </div>

        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <div>
                <h1 class="page-title" style="margin: 0; font-size: 1.6rem; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                    <span>Sơ đồ Phòng & Giường</span>
                    <?php if (!empty($isTruongToa)): ?>
                        <span class="badge badge-success" style="font-size: 0.85rem; font-weight: 600; padding: 4px 10px;">
                            <i class="fa-solid fa-building" style="margin-right: 4px;"></i> <?= htmlspecialchars($toaInfo['TenToaNha'] ?? $maToa) ?>
                        </span>
                    <?php endif; ?>
                </h1>
            </div>

            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <?php if (!empty($isTruongToa)): ?>
                    <button type="button" class="btn btn-primary" id="btnToggleAddRoom" onclick="toggleRoomForm()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;">
                        <i class="fa-solid fa-plus" style="margin-right: 6px;"></i> Thêm phòng mới
                    </button>
                    <button type="button" class="btn btn-outline" onclick="openImportRoomModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách phòng từ file CSV">
                        <i class="fa-solid fa-file-import" style="margin-right: 6px; color: #2563eb;"></i> Nhập file (Import)
                    </button>
                    <button type="button" class="btn btn-outline" id="btnExportRooms" onclick="exportFilteredRooms()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách phòng theo kết quả lọc ra Excel/CSV">
                        <i class="fa-solid fa-file-excel" style="margin-right: 6px; color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (CSV)</span>
                    </button>
                <?php endif; ?>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px; text-decoration: none;">
                    &larr; Về Dashboard
                </a>
            </div>
        </div>

        <!-- Thanh phím tắt chuyển nhanh các phân hệ nghiệp vụ Staff -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;">
            <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📊 Tổng quan BQL</a>
            <a href="<?= url('staff/rooms') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">🏢 1. Sơ đồ Phòng & Cư trú</a>
            <a href="<?= url('staff/applications') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
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

    <?php if (empty($isTruongToa)): ?>
        <!-- ================================================================= -->
        <!-- MÀN HÌNH CẢNH BÁO PHÂN QUYỀN (CHỈ TRƯỞNG TÒA CÓ CHỨC NĂNG NÀY) -->
        <!-- ================================================================= -->
        <div class="card" style="border: 2px solid #f87171; background: #fffaf0; border-radius: 12px; padding: 36px 30px; text-align: center; margin-top: 20px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.08);">
            <div style="width: 72px; height: 72px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; color: #dc2626; font-size: 2rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 style="font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-bottom: 12px;">
                TRUY CẬP BỊ GIỚI HẠN: CHỨC NĂNG DÀNH RIÊNG CHO TRƯỞNG TÒA NHÀ
            </h2>
            <div style="max-width: 720px; margin: 0 auto 24px auto; font-size: 0.95rem; color: #475569; line-height: 1.6;">
                <p style="margin-bottom: 12px;">
                    Theo quy định phân cấp nghiệp vụ KTX ĐHQG-HCM, <strong>chức năng quản lý trực tiếp Sơ đồ phòng và Giường chỉ dành riêng cho Cán bộ Trưởng tòa nhà (TRUONG_TOA)</strong>.
                </p>
                <div style="background: #ffffff; border: 1px solid #fed7aa; border-radius: 8px; padding: 14px 20px; text-align: left; display: inline-block; width: 100%; box-sizing: border-box;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Tài khoản hiện tại:</span>
                        <strong style="color: #0f172a;"><?= htmlspecialchars($user['HoVaTen'] ?? $user['TenDangNhap'] ?? 'Cán bộ') ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                        <span style="color: #64748b;">Vai trò hệ thống:</span>
                        <span class="badge badge-warning" style="font-weight: 600;"><?= htmlspecialchars($roleName ?? $user['MaVaiTro'] ?? 'Chưa xác định') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #64748b;">Quyền hạn được cấp:</span>
                        <span style="color: #dc2626; font-weight: 600;">Không có quyền CRUD Phòng / Giường tại phân hệ này</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; justify-content: center; gap: 14px;">
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-primary" style="border-radius: 8px; padding: 10px 20px;">
                    <i class="fa-solid fa-house" style="margin-right: 6px;"></i> Về Trang chủ BQL
                </a>
                <a href="<?= url('staff/zones') ?>" class="btn btn-outline" style="border-radius: 8px; padding: 10px 20px;">
                    <i class="fa-solid fa-sitemap" style="margin-right: 6px;"></i> Xem Quản lý Khu & Cụm
                </a>
            </div>
        </div>

    <?php else: ?>
        <!-- ================================================================= -->
        <!-- GIAO DIỆN CHÍNH THỨC DÀNH CHO TRƯỞNG TÒA NHÀ (TRUONG_TOA) -->
        <!-- ================================================================= -->

        <!-- BIỂU MẪU CRUD: THÊM / CẬP NHẬT PHÒNG Ở (ẨN MẶC ĐỊNH, HIỆN KHI CLICK HOẶC SỬA) -->
        <div class="card" id="roomFormCard" style="<?= !empty($flashError) ? 'display: block;' : 'display: none;' ?> margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
            <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="formTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                    <span id="formTitleText">Thêm mới Phòng ở (Tòa <?= htmlspecialchars($toaInfo['TenToaNha'] ?? $maToa) ?>)</span>
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
                        Tòa: <strong><?= htmlspecialchars($toaInfo['TenToaNha'] ?? $maToa) ?></strong> (Mã: <code><?= htmlspecialchars($maToa) ?></code>) &bull;
                        Cụm: <strong><?= htmlspecialchars($toaInfo['TenCum'] ?? 'Cụm') ?></strong> &bull;
                        Dành cho: <strong><?= htmlspecialchars($toaInfo['GioiTinhQuyDinh'] ?? 'Nam') ?></strong>
                    </div>
                    <div style="color: #64748b; font-size: 0.8rem;">
                        Tối đa <strong><?= (int)($toaInfo['TongSoTang'] ?? 5) ?></strong> tầng
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
                            <option value="Còn chỗ">Còn chỗ (Sẵn sàng tiếp nhận sinh viên)</option>
                            <option value="Đang sử dụng">Đang sử dụng</option>
                            <option value="Đầy chỗ">Đầy chỗ</option>
                            <option value="Khóa bảo trì">Khóa bảo trì</option>
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
                    <span>Danh sách Phòng ở & Sơ đồ Giường (Tòa <?= htmlspecialchars($toaInfo['TenToaNha'] ?? $maToa) ?>)</span>
                    <span class="badge badge-info" id="roomTotalBadge"><?= count($phongList ?? []) ?> phòng</span>
                </div>
            </div>

            <!-- THANH TÌM KIẾM VÀ LỌC ĐA TIÊU CHÍ (GIỐNG MẪU USERS) -->
            <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <div style="position: relative; flex: 1; min-width: 240px; max-width: 420px;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                    <input type="text" id="searchRoomInput" class="btn-outline"
                        placeholder="Tìm theo mã phòng, số phòng, tên SV, MSSV..."
                        style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                        oninput="filterRooms()">
                </div>

                <select id="filterRoomFloor" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả các tầng --</option>
                    <?php for ($t = 1; $t <= $tongTang; $t++): ?>
                        <option value="<?= $t ?>">Tầng <?= $t ?></option>
                    <?php endfor; ?>
                </select>

                <select id="filterRoomType" class="btn-outline"
                    style="min-width: 200px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả loại phòng --</option>
                    <?php foreach ($loaiPhongList as $lp): ?>
                        <option value="<?= htmlspecialchars($lp['MaLoaiPhong'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($lp['TenLoaiPhong']) ?> (<?= (int)$lp['SoGiuongToiDa'] ?> giường)
                        </option>
                    <?php endforeach; ?>
                </select>

                <select id="filterRoomStatus" class="btn-outline"
                    style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                    onchange="filterRooms()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Còn chỗ">Còn chỗ</option>
                    <option value="Đang sử dụng">Đang sử dụng</option>
                    <option value="Đầy chỗ">Đầy chỗ</option>
                    <option value="Khóa bảo trì">Khóa bảo trì</option>
                </select>

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
                                <th style="width: 110px;">Mã phòng</th>
                                <th style="width: 130px;">Số phòng</th>
                                <th style="width: 190px;">Loại phòng</th>
                                <th style="width: 100px;">Sức chứa</th>
                                <th>Sơ đồ Giường</th>
                                <th style="width: 150px;">Trưởng phòng</th>
                                <th style="width: 120px;">Trạng thái</th>
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
                                $isLocked = ($p['TrangThaiPhong'] === 'Khóa bảo trì');

                                if ($isLocked) {
                                    $stBadge = 'badge-warning';
                                } elseif ($p['TrangThaiPhong'] === 'Đầy chỗ') {
                                    $stBadge = 'badge-danger';
                                } elseif ($p['TrangThaiPhong'] === 'Đang sử dụng' || $occupied > 0) {
                                    $stBadge = 'badge-primary';
                                } else {
                                    $stBadge = 'badge-success';
                                }
                                ?>
                                <tr class="room-row"
                                    data-id="<?= htmlspecialchars($p['MaPhong'], ENT_QUOTES, 'UTF-8') ?>"
                                    data-tang="<?= (int)$p['Tang'] ?>"
                                    data-loaiphong="<?= htmlspecialchars($p['MaLoaiPhong'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    data-status="<?= htmlspecialchars($p['TrangThaiPhong'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                    style="<?= $isLocked ? 'background: #fffbeb;' : '' ?>">

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
                                            Tầng <?= (int)$p['Tang'] ?>
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
                                    <td>
                                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                                            <b style="font-size: 0.85rem; color: #0f172a;"><?= $occupied ?> / <?= $maxCap ?> SV</b>
                                            <span style="font-size: 0.75rem; color: var(--text-muted);"><?= $fillPercent ?>%</span>
                                        </div>
                                        <div style="width: 100%; height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden;">
                                            <div style="width: <?= min(100, $fillPercent) ?>%; height: 100%; background: <?= $isLocked ? '#f59e0b' : ($fillPercent >= 100 ? '#ef4444' : ($fillPercent > 0 ? '#3b82f6' : '#10b981')) ?>; border-radius: 3px;"></div>
                                        </div>
                                    </td>

                                    <!-- 5. Sơ đồ Giường (Pills trực quan) -->
                                    <td>
                                        <div style="display: flex; gap: 4px; align-items: center; flex-wrap: wrap;">
                                            <?php foreach ($beds as $g): ?>
                                                <?php
                                                $bOccupied = !empty($g['MaSinhVien']) || ($g['TrangThaiGiuong'] === 'Đã có người ở');
                                                $bLocked = ($g['TrangThaiGiuong'] === 'Khóa bảo trì');
                                                $bLeader = (!empty($p['TruongPhong']) && $p['TruongPhong'] === $g['MaSinhVien']);

                                                if ($bOccupied) {
                                                    $pillBg = '#f0fdf4';
                                                    $pillBorder = '#bbf7d0';
                                                    $pillColor = '#166534';
                                                    $pillIcon = $bLeader ? 'fa-solid fa-star' : 'fa-solid fa-user';
                                                    $pillTitle = $g['KyHieuGiuong'] . ': ' . ($g['HoVaTen'] ?? 'Sinh viên') . ' (' . ($g['MSSVTaiTruong'] ?? '') . ')';
                                                } elseif ($bLocked) {
                                                    $pillBg = '#fffbeb';
                                                    $pillBorder = '#fde68a';
                                                    $pillColor = '#b45309';
                                                    $pillIcon = 'fa-solid fa-lock';
                                                    $pillTitle = $g['KyHieuGiuong'] . ': Khóa bảo trì';
                                                } else {
                                                    $pillBg = '#f8fafc';
                                                    $pillBorder = '#e2e8f0';
                                                    $pillColor = '#64748b';
                                                    $pillIcon = 'fa-solid fa-bed';
                                                    $pillTitle = $g['KyHieuGiuong'] . ': Trống';
                                                }
                                                ?>
                                                <span title="<?= htmlspecialchars($pillTitle) ?>"
                                                    style="display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; background: <?= $pillBg ?>; border: 1px solid <?= $pillBorder ?>; color: <?= $pillColor ?>; cursor: pointer;"
                                                    onclick="openBedDetailModal('<?= htmlspecialchars($p['MaPhong']) ?>')">
                                                    <i class="<?= $pillIcon ?>" style="font-size: 0.65rem;"></i>
                                                    <?= htmlspecialchars($g['KyHieuGiuong']) ?>
                                                </span>
                                            <?php endforeach; ?>

                                            <button type="button" class="btn btn-outline btn-sm"
                                                style="border-radius: 4px; padding: 2px 6px; font-size: 0.725rem; height: 22px; line-height: 1; color: var(--primary); border-color: var(--primary); background: #eff6ff;"
                                                onclick="openBedDetailModal('<?= htmlspecialchars($p['MaPhong']) ?>')"
                                                title="Xem chi tiết & Quản lý toàn bộ giường">
                                                <i class="fa-solid fa-gear" style="font-size: 0.65rem; margin-right: 3px;"></i> Giường
                                            </button>
                                        </div>
                                    </td>

                                    <!-- 6. Trưởng phòng -->
                                    <td>
                                        <?php if (!empty($p['TruongPhong'])): ?>
                                            <span class="badge badge-success" style="font-size: 0.8rem; font-weight: 600;" title="MSSV: <?= htmlspecialchars($p['TruongPhong']) ?>">
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

                                    <!-- 7. Trạng thái -->
                                    <td>
                                        <span class="badge <?= $stBadge ?>" style="font-size: 0.8rem;">
                                            <?= htmlspecialchars($p['TrangThaiPhong']) ?>
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

                                        <!-- Nút Khóa / Mở bảo trì -->
                                        <form method="POST" action="<?= url('staff/rooms/toggle-status') ?>" style="display: inline;" onsubmit="return confirm('<?= $isLocked ? "Xác nhận mở khóa phòng " . $p['MaPhong'] . "?" : "Xác nhận chuyển phòng " . $p['MaPhong'] . " sang trạng thái Khóa bảo trì?" ?>');">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="maPhong" value="<?= htmlspecialchars($p['MaPhong']) ?>">
                                            <button type="submit" class="btn btn-outline btn-sm"
                                                style="border-radius: 6px; margin-right: 4px; <?= $isLocked ? 'color: #15803d; border-color: #86efac; background: #f0fdf4;' : 'color: #b45309; border-color: #fde68a;' ?>"
                                                title="<?= $isLocked ? 'Mở khóa phòng' : 'Khóa bảo trì phòng' ?>">
                                                <i class="fa-solid <?= $isLocked ? 'fa-lock-open' : 'fa-lock' ?>" style="margin-right: 4px;"></i> <?= $isLocked ? 'Mở' : 'Khóa' ?>
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
        <div id="importRoomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;">
            <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
                <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
                    <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-import" style="color: #2563eb; margin-right: 6px;"></i>
                        <span>Nhập danh sách Phòng ở từ file CSV</span>
                    </div>
                    <button type="button" onclick="closeImportRoomModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form action="<?= url('staff/rooms/import') ?>" method="POST" enctype="multipart/form-data" style="margin: 0;">
                    <?= csrf_field() ?>
                    <div style="padding: 20px;">
                        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #1e40af;">
                            <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                                <i class="fa-solid fa-circle-info"></i> Quy định định dạng file nhập dữ liệu:
                            </div>
                            <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                                <li>Hỗ trợ file định dạng <b>.CSV</b> hoặc <b>.TXT</b> phân cách bởi dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>.</li>
                                <li>Cột bắt buộc: <b>SoPhong</b> (Số phòng, ví dụ: <code>101</code>, <code>202</code>...).</li>
                                <li>Cột tùy chọn: <b>Tang</b> (Tầng, mặc định: 1), <b>MaLoaiPhong</b> (ví dụ: <code>LP4</code>, <code>LP6</code>, <code>LP8</code>, <code>LP4ML</code>), <b>TrangThaiPhong</b> (mặc định: <code>Còn chỗ</code>).</li>
                                <li>Tất cả các phòng nhập sẽ được tự động gắn vào Tòa nhà <b><?= htmlspecialchars($toaInfo['TenToaNha'] ?? $maToa) ?></b> và tự động sinh các giường trống theo đúng tiêu chuẩn.</li>
                            </ul>
                            <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
                                <a href="<?= url('staff/rooms/template') ?>" class="btn btn-outline btn-sm" style="background: #fff; color: #1e40af; border-color: #93c5fd; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 5px 12px; display: inline-flex; align-items: center;">
                                    <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Tải tệp CSV mẫu chuẩn
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
                            <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                                <input type="checkbox" name="overwriteExisting" value="1" checked style="margin-top: 2px;">
                                <span>
                                    <b>Cập nhật thông tin nếu phòng đã tồn tại:</b> Khi được chọn, phòng trùng mã sẽ được cập nhật lại tầng, loại phòng và trạng thái mới.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn btn-outline" onclick="closeImportRoomModal()" style="border-radius: 6px; padding: 8px 18px;">
                            Hủy bỏ
                        </button>
                        <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600;">
                            <i class="fa-solid fa-file-import" style="margin-right: 6px;"></i> Bắt đầu nhập dữ liệu
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
                    <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-bed" style="color: var(--primary);"></i>
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
                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #991b1b; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Xác nhận Xóa Phòng
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
                        <button type="button" class="btn btn-outline" onclick="closeModal('modalDeleteRoom')" style="border-radius: 6px; padding: 8px 16px;">Hủy</button>
                        <button type="submit" class="btn btn-danger" style="border-radius: 6px; padding: 8px 18px; font-weight: 600; background: #dc2626; color: #fff;">
                            <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xác nhận Xóa
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- JAVASCRIPT XỬ LÝ PHÂN TRANG, BỘ LỌC, EXPORT, IMPORT VÀ MODAL GIƯỜNG -->
        <script>
            const currentMaToa = <?= json_encode($maToa) ?>;
            const roomsData = <?= json_encode($phongList ?? [], JSON_UNESCAPED_UNICODE) ?>;
            const bedsData = <?= json_encode($giuongByPhong ?? [], JSON_UNESCAPED_UNICODE) ?>;

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
                        exportBtnText.innerText = `Xuất Excel (${total} đã lọc)`;
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

            function resetRoomForm() {
                document.getElementById('roomIsEdit').value = '0';
                document.getElementById('roomMaPhong').value = '';
                document.getElementById('roomSoPhong').value = '';
                document.getElementById('roomSoPhong').readOnly = false;
                document.getElementById('roomTang').value = '1';
                document.getElementById('roomMaLoaiPhong').value = '';
                document.getElementById('roomTrangThaiPhong').value = 'Còn chỗ';
                document.getElementById('roomCodePreview').value = currentMaToa + '.---';
                document.getElementById('formTitleText').innerText = 'Thêm mới Phòng ở (Tòa ' + currentMaToa + ')';
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

                const soPhong = document.getElementById('roomSoPhong').value.trim();
                const preview = document.getElementById('roomCodePreview');
                if (soPhong) {
                    preview.value = currentMaToa + '.' + soPhong;
                } else {
                    preview.value = currentMaToa + '.---';
                }
            }

            function editRoom(room) {
                const card = document.getElementById('roomFormCard');
                card.style.display = 'block';

                document.getElementById('roomIsEdit').value = '1';
                document.getElementById('roomMaPhong').value = room.MaPhong;
                document.getElementById('roomSoPhong').value = room.SoPhong;
                document.getElementById('roomSoPhong').readOnly = true;
                document.getElementById('roomTang').value = room.Tang;
                document.getElementById('roomMaLoaiPhong').value = room.MaLoaiPhong;
                document.getElementById('roomTrangThaiPhong').value = room.TrangThaiPhong;
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

                summaryEl.innerHTML = `
                    <div>
                        <strong>Tòa: ${currentMaToa}</strong> &bull; Tầng: <strong>${room ? room.Tang : 1}</strong> &bull; 
                        Loại phòng: <strong>${room ? (room.TenLoaiPhong || room.MaLoaiPhong) : ''}</strong> &bull; 
                        Trạng thái: <span class="badge ${room && room.TrangThaiPhong === 'Khóa bảo trì' ? 'badge-warning' : 'badge-success'}">${room ? room.TrangThaiPhong : ''}</span>
                    </div>
                    <div>
                        Sĩ số: <strong style="color: var(--primary); font-size: 1rem;">${occupied} / ${maxCap} SV</strong> (${fillPercent}%) &bull; 
                        Trưởng phòng: <strong>${room && room.TruongPhong ? (room.TenTruongPhong ? room.TenTruongPhong + ' (' + room.TruongPhong + ')' : room.TruongPhong) : 'Chưa chỉ định'}</strong>
                    </div>
                `;

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
                        const isLocked = g.TrangThaiGiuong === 'Khóa bảo trì';
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

                            html += `</div></td>`;
                        } else {
                            // Giường trống / bảo trì
                            html += `
                                <td colspan="4" style="color: var(--text-muted); font-style: italic;">
                                    ${isLocked ? '<span style="color:#d97706; font-weight:600;"><i class="fa-solid fa-lock"></i> Giường Khóa bảo trì</span>' : 'Giường trống'}
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
                                        <input type="hidden" name="trangThaiGiuong" value="Khóa bảo trì">
                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 4px; padding: 3px 7px; font-size: 0.75rem;" title="Khóa bảo trì giường">
                                            <i class="fa-solid fa-lock"></i> Khóa
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

            function closeModal(modalId) {
                const el = document.getElementById(modalId);
                if (el) el.style.display = 'none';
            }

            // Phím Escape để đóng tất cả các modal
            window.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeBedDetailModal();
                    closeImportRoomModal();
                    closeModal('modalDeleteRoom');
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
$title = !empty($isTruongToa)
    ? "Sơ đồ Phòng & Giường - Tòa " . ($toaInfo['TenToaNha'] ?? $maToa ?? '')
    : "Quản lý Sơ đồ Phòng & Giường - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
