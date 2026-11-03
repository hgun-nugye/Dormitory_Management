<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Sơ đồ Phòng, Giường & Hồ sơ Cư trú</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Quản lý Sơ đồ Phòng & Hồ sơ Cư trú</h1>
                <p class="page-subtitle">
                    <strong>Phạm vi Cụm: <?= htmlspecialchars($maCum ?? 'AG') ?></strong> &bull; Hiển thị phòng/giường trực quan, chỉ định Trưởng phòng, kiểm tra tình trạng cư trú và xác nhận nhận phòng thực tế.
                </p>
            </div>
            <div>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về Dashboard Cụm
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
    <?php if (!empty($success)): ?>
        <div class="card" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">✓</span>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="card" style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem;">✕</span>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #166534; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>📌 Quy tắc nghiệp vụ Cư trú & Sơ đồ phòng (Mục 1 & 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #15803d; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Phạm vi dữ liệu:</strong> Cán bộ cấp Cụm chỉ truy vấn và thao tác trên các tòa nhà thuộc cụm phân công (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>).</li>
            <li><strong>Xác nhận nhận phòng (Check-in):</strong> Khi sinh viên đến nhận chìa khóa, BQL bấm [Xác nhận nhận phòng] để kích hoạt hợp đồng và đồng bộ sĩ số phòng thực tế <code>SoSVDangO</code>.</li>
            <li><strong>Chỉ định Trưởng phòng:</strong> Trưởng cụm có quyền gán cờ <code>TruongPhong</code> cho 1 sinh viên nội trú đại diện nhận hóa đơn và phản ánh chung.</li>
            <li><strong>Bảo toàn dữ liệu:</strong> Tuyệt đối không xóa cứng phòng/giường đang có hợp đồng hiệu lực.</li>
        </ul>
    </div>

    <!-- KHUNG CẢNH BÁO ĐỀ XUẤT DỒN PHÒNG (NẾU CÓ PHÒNG <= 50%) -->
    <?php if (!empty($underOccupiedRooms)): ?>
        <div class="card" style="margin-bottom: 24px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 8px; padding: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div style="font-weight: 700; color: #9a3412; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️ Cảnh báo Dồn phòng tối ưu (Chức năng 3): Có <?= count($underOccupiedRooms) ?> phòng lấp đầy &le; 50%</span>
                </div>
                <span class="badge badge-warning">Cụm <?= htmlspecialchars($maCum ?? 'AG') ?></span>
            </div>
            <p style="font-size: 0.85rem; color: #c2410c; margin-bottom: 10px;">
                Các phòng dưới đây có công suất thấp, gây lãng phí điện nước công cộng và cần theo dõi để đề xuất phương án ghép phòng dồn tòa:
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php foreach ($underOccupiedRooms as $ur): ?>
                    <div style="background: #fff; border: 1px solid #fdba74; border-radius: 6px; padding: 6px 12px; font-size: 0.85rem;">
                        <b>Phòng <?= htmlspecialchars($ur['SoPhong']) ?></b> (Tòa <?= htmlspecialchars($ur['MaToaNha']) ?>): 
                        <span style="color: #ea580c; font-weight: 600;"><?= htmlspecialchars($ur['SoSVDangO']) ?> / <?= htmlspecialchars($ur['SoGiuongToiDa']) ?> SV (<?= htmlspecialchars($ur['TyLeLapDay']) ?>%)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- BỘ LỌC TÒA NHÀ & TẦNG TRONG CỤM -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Bộ lọc tra cứu Sơ đồ phòng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-info">JOIN: CumNha + ToaNha + Phong + Giuong</span>
        </div>
        <form method="GET" action="<?= url('staff/rooms') ?>">
            <div class="grid grid-cols-4" style="gap: 14px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tòa nhà:</label>
                    <select name="toaNha" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                        <option value="">-- Tất cả tòa trong Cụm --</option>
                        <?php if (!empty($buildings)): ?>
                            <?php foreach ($buildings as $b): ?>
                                <option value="<?= htmlspecialchars($b['MaToaNha']) ?>" <?= ($filterToa === $b['MaToaNha']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['TenToaNha']) ?> (<?= htmlspecialchars($b['GioiTinhQuyDinh']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="A01" selected>Tòa A01 (Nam)</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tầng:</label>
                    <select name="tang" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                        <option value="">-- Tất cả các tầng --</option>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?= $i ?>" <?= ($filterTang == (string)$i) ? 'selected' : '' ?>>Tầng <?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tình trạng phòng:</label>
                    <select name="trangThai" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="Đang sử dụng" <?= ($filterTrangThai === 'Đang sử dụng') ? 'selected' : '' ?>>Đang sử dụng</option>
                        <option value="Còn chỗ" <?= ($filterTrangThai === 'Còn chỗ') ? 'selected' : '' ?>>Còn chỗ</option>
                        <option value="Đầy chỗ" <?= ($filterTrangThai === 'Đầy chỗ') ? 'selected' : '' ?>>Đầy chỗ</option>
                        <option value="Khóa bảo trì" <?= ($filterTrangThai === 'Khóa bảo trì') ? 'selected' : '' ?>>Khóa bảo trì</option>
                    </select>
                </div>
                <div style="display: flex; align-items: flex-end; gap: 8px;">
                    <button type="submit" class="btn btn-primary" style="border-radius: 6px; height: 38px; flex: 1;">
                        🔍 Lọc dữ liệu
                    </button>
                    <a href="<?= url('staff/rooms') ?>" class="btn btn-outline" style="border-radius: 6px; height: 38px; display: flex; align-items: center; justify-content: center;" title="Xóa bộ lọc">
                        ↺
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- DANH SÁCH SƠ ĐỒ PHÒNG VÀ HỒ SƠ SINH VIÊN CƯ TRÚ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách Phòng ở & Thành viên Lưu trú</span>
            <span class="badge badge-success"><?= count($rooms ?? []) ?> phòng hiển thị</span>
        </div>

        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                <?php 
                    $beds = $bedsByRoom[$room['MaPhong']] ?? [];
                    $rate = (float)($room['TyLeLapDay'] ?? 0);
                    $badgeRateClass = 'badge-success';
                    if ($rate <= 50) {
                        $badgeRateClass = 'badge-danger';
                    } elseif ($rate < 100) {
                        $badgeRateClass = 'badge-warning';
                    }
                    $isRoomLocked = ($room['TrangThaiPhong'] === 'Khóa bảo trì');
                ?>
                <div style="border: 1px solid var(--border); border-radius: 8px; margin-bottom: 22px; overflow: hidden; background: #fff;">
                    <!-- Room Header Bar -->
                    <div style="background: #f8fafc; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); flex-wrap: wrap; gap: 10px;">
                        <div>
                            <span style="font-size: 1.15rem; font-weight: 700; color: var(--primary);">
                                Phòng <?= htmlspecialchars($room['SoPhong']) ?>
                            </span>
                            <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: 10px;">
                                (<?= htmlspecialchars($room['TenToaNha']) ?> &bull; Tầng <?= htmlspecialchars($room['Tang']) ?> &bull; <?= htmlspecialchars($room['TenLoaiPhong']) ?>)
                            </span>
                            <?php if (!empty($room['TruongPhong'])): ?>
                                <span class="badge badge-success" style="margin-left: 8px;">
                                    ⭐ Trưởng phòng: MSSV <?= htmlspecialchars($room['TruongPhong']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div style="display: flex; gap: 8px; align-items: center;">
                            <span class="badge badge-info">
                                Sĩ số: <?= htmlspecialchars($room['SoSVDangO']) ?> / <?= htmlspecialchars($room['SoGiuongToiDa']) ?> SV
                            </span>
                            <span class="badge <?= $badgeRateClass ?>">
                                Lấp đầy: <?= $rate ?>% <?= ($rate <= 50) ? '(Đề xuất dồn)' : '' ?>
                            </span>
                            <span class="badge <?= $isRoomLocked ? 'badge-danger' : 'badge-info' ?>">
                                <?= htmlspecialchars($room['TrangThaiPhong']) ?>
                            </span>

                            <!-- Nút Khóa / Mở bảo trì phòng -->
                            <form method="POST" action="<?= url('staff/rooms/toggle-room-maintenance') ?>" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="maPhong" value="<?= htmlspecialchars($room['MaPhong']) ?>">
                                <input type="hidden" name="action" value="<?= $isRoomLocked ? 'unlock' : 'lock' ?>">
                                <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; <?= $isRoomLocked ? 'color:#15803d;' : 'color:#dc2626;' ?>" onclick="return confirm('Xác nhận <?= $isRoomLocked ? 'mở lại' : 'khóa bảo trì' ?> phòng <?= htmlspecialchars($room['SoPhong']) ?>?');">
                                    <?= $isRoomLocked ? '🔓 Mở lại phòng' : '🔒 Khóa bảo trì phòng' ?>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Beds and Residents Table -->
                    <div class="table-responsive">
                        <table class="data-table" style="margin-bottom: 0;">
                            <thead>
                                <tr>
                                    <th style="width: 110px;">Mã Giường</th>
                                    <th>Sinh viên lưu trú</th>
                                    <th>Trường ĐH</th>
                                    <th>Số CCCD / SĐT</th>
                                    <th>Vai trò</th>
                                    <th>Ngày nhận phòng</th>
                                    <th>Trạng thái</th>
                                    <th style="text-align: right;">Thao tác BQL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($beds)): ?>
                                    <?php foreach ($beds as $bed): ?>
                                        <?php 
                                            $sttGiuong = $bed['TrangThaiGiuong'];
                                            $hasStudent = !empty($bed['MaSinhVien']);
                                            $isLeader = (!empty($room['TruongPhong']) && $room['TruongPhong'] === $bed['MaSinhVien']);
                                            $hasCheckin = !empty($bed['NgayNhanPhong']);
                                        ?>
                                        <?php if ($sttGiuong === 'Đã có người ở' && $hasStudent): ?>
                                            <!-- GIƯỜNG ĐÃ CÓ NGƯỜI Ở -->
                                            <tr>
                                                <td><b><?= htmlspecialchars($bed['KyHieuGiuong'] ?? $bed['MaGiuong']) ?></b></td>
                                                <td>
                                                    <b><?= htmlspecialchars($bed['HoVaTen']) ?></b> <br>
                                                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                                                        MSSV: <?= htmlspecialchars($bed['MSSVTaiTruong'] ?? $bed['MaSinhVien']) ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($bed['TenTruong'] ?? '---') ?></td>
                                                <td>
                                                    <?= htmlspecialchars($bed['SoCCCD'] ?? '---') ?> <br>
                                                    <span style="font-size: 0.8rem; color: var(--text-muted);"><?= htmlspecialchars($bed['SoDienThoai'] ?? '---') ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($isLeader): ?>
                                                        <span class="badge badge-success">⭐ Trưởng phòng</span>
                                                    <?php else: ?>
                                                        <span style="font-size: 0.85rem; color: var(--text-muted);">Thành viên</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= !empty($bed['NgayNhanPhong']) ? date('d/m/Y H:i', strtotime($bed['NgayNhanPhong'])) : '---' ?>
                                                </td>
                                                <td>
                                                    <span class="badge badge-info">Đã nhận phòng</span>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <?php if (!$isLeader): ?>
                                                        <form method="POST" action="<?= url('staff/rooms/assign-leader') ?>" style="display:inline;">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="maPhong" value="<?= htmlspecialchars($room['MaPhong']) ?>">
                                                            <input type="hidden" name="maSinhVien" value="<?= htmlspecialchars($bed['MaSinhVien']) ?>">
                                                            <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="return confirm('Chỉ định sinh viên <?= htmlspecialchars($bed['HoVaTen']) ?> làm Trưởng phòng?');">
                                                                ⭐ Đặt Trưởng phòng
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>

                                        <?php elseif ($sttGiuong === 'Đang giữ chỗ'): ?>
                                            <!-- GIƯỜNG ĐANG GIỮ CHỖ (CHỜ NHẬN PHÒNG HOẶC CHỜ THANH TOÁN) -->
                                            <tr style="background: #fffbeb;">
                                                <td><b><?= htmlspecialchars($bed['KyHieuGiuong'] ?? $bed['MaGiuong']) ?></b></td>
                                                <td>
                                                    <?php if ($hasStudent): ?>
                                                        <b><?= htmlspecialchars($bed['HoVaTen']) ?></b> <br>
                                                        <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: <?= htmlspecialchars($bed['MSSVTaiTruong'] ?? $bed['MaSinhVien']) ?></span>
                                                    <?php else: ?>
                                                        <span style="color: #b45309; font-style: italic;">Sinh viên đang giữ chỗ (Đã duyệt 72h)</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= htmlspecialchars($bed['TenTruong'] ?? '---') ?></td>
                                                <td><?= htmlspecialchars($bed['SoDienThoai'] ?? '---') ?></td>
                                                <td><span style="font-size: 0.85rem; color: var(--text-muted);">---</span></td>
                                                <td>
                                                    <span class="badge badge-warning">⏳ Chưa nhận phòng</span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-warning">Đang giữ chỗ</span>
                                                </td>
                                                <td style="text-align: right; white-space: nowrap;">
                                                    <form method="POST" action="<?= url('staff/rooms/checkin') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="maGiuong" value="<?= htmlspecialchars($bed['MaGiuong']) ?>">
                                                        <input type="hidden" name="maHopDong" value="<?= htmlspecialchars($bed['MaHopDong'] ?? '') ?>">
                                                        <input type="hidden" name="maHoSo" value="<?= htmlspecialchars($bed['MaHoSo'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="return confirm('Xác nhận bàn giao chìa khóa và hoàn tất nhận phòng cho giường <?= htmlspecialchars($bed['MaGiuong']) ?>?');">
                                                            🔑 Xác nhận nhận phòng
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                        <?php elseif ($sttGiuong === 'Khóa bảo trì'): ?>
                                            <!-- GIƯỜNG KHÓA BẢO TRÌ -->
                                            <tr style="background: #fef2f2;">
                                                <td><b><?= htmlspecialchars($bed['KyHieuGiuong'] ?? $bed['MaGiuong']) ?></b></td>
                                                <td colspan="5" style="color: #b91c1c; font-style: italic;">
                                                    🔒 Giường đang khóa bảo trì (Hư hỏng hoặc chờ sửa chữa cơ sở vật chất)
                                                </td>
                                                <td><span class="badge badge-danger">Khóa bảo trì</span></td>
                                                <td style="text-align: right;">
                                                    <form method="POST" action="<?= url('staff/rooms/toggle-bed-maintenance') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="maGiuong" value="<?= htmlspecialchars($bed['MaGiuong']) ?>">
                                                        <input type="hidden" name="action" value="unlock">
                                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #15803d;" onclick="return confirm('Mở lại giường <?= htmlspecialchars($bed['MaGiuong']) ?> về trạng thái Trống?');">
                                                            🔓 Mở lại giường
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>

                                        <?php else: ?>
                                            <!-- GIƯỜNG TRỐNG -->
                                            <tr style="background: #fafafa;">
                                                <td><b><?= htmlspecialchars($bed['KyHieuGiuong'] ?? $bed['MaGiuong']) ?></b></td>
                                                <td colspan="5" style="color: var(--text-muted); font-style: italic;">
                                                    ⚪ Giường trống (Sẵn sàng tiếp nhận sinh viên mới)
                                                </td>
                                                <td><span class="badge badge-info">Trống</span></td>
                                                <td style="text-align: right;">
                                                    <form method="POST" action="<?= url('staff/rooms/toggle-bed-maintenance') ?>" style="display:inline;">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="maGiuong" value="<?= htmlspecialchars($bed['MaGiuong']) ?>">
                                                        <input type="hidden" name="action" value="lock">
                                                        <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #dc2626;" onclick="return confirm('Xác nhận khóa bảo trì giường <?= htmlspecialchars($bed['MaGiuong']) ?>?');">
                                                            🔒 Khóa bảo trì
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 16px;">
                                            Chưa cấu hình danh sách giường cho phòng này.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 48px 16px; color: var(--text-muted);">
                <div style="font-size: 1.2rem; font-weight: 600; margin-bottom: 6px;">Không tìm thấy phòng nào phù hợp</div>
                <p style="font-size: 0.9rem;">Vui lòng thử điều chỉnh lại bộ lọc Tòa nhà, Tầng hoặc Trạng thái phòng ở trên.</p>
                <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-top: 8px;">
                    ↺ Xóa bộ lọc tra cứu
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Sơ đồ Phòng & Cư trú - BQL Cụm " . htmlspecialchars($maCum ?? 'AG');
require dirname(__DIR__) . '/layouts/main.php';
?>
