<?php
ob_start();

$activeType = $type ?? 'all';
$activeStatus = $status ?? 'all';
$searchVal = $search ?? '';
$reqList = $requests ?? [];
//---------------
function renderRequestReason(?string $rawReason): void
{
    if (empty($rawReason)) return;
    if (str_contains($rawReason, '[BQL từ chối:')) {
        $parts = explode('[BQL từ chối:', $rawReason);
        $svReason = trim($parts[0] ?? '');
        $bqlReason = rtrim(trim($parts[1] ?? ''), ']');
        if (!empty($svReason)) {
            echo "<div style='font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;'><em>Lý do SV:</em> " . htmlspecialchars($svReason) . "</div>";
        }
        echo "<div style='font-size: 0.8rem; color: #991b1b; font-weight: 600; margin-top: 4px; background: #fef2f2; padding: 4px 8px; border-radius: 4px; border: 1px solid #fecaca;'>🛑 Lý do BQL từ chối: " . htmlspecialchars($bqlReason) . "</div>";
    } else {
        echo "<div style='font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;'><em>Lý do:</em> " . htmlspecialchars($rawReason) . "</div>";
    }
}
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Dịch vụ Một Cửa (Đơn từ Sinh viên)</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Dịch vụ Một Cửa (Đơn từ Sinh viên)</h1>
            </div>
            <div>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về Dashboard Cụm
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (!empty($success)): ?>
        <div class="card" style="margin-bottom: 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem; font-weight: bold;">✓</span>
            <div><?= htmlspecialchars($success) ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="card" style="margin-bottom: 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 14px 18px; border-radius: 8px; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 1.2rem; font-weight: bold;">✕</span>
            <div><?= htmlspecialchars($error) ?></div>
        </div>
    <?php endif; ?>
</div>

<!-- KHUNG CHÍNH: TABS, BỘ LỌC VÀ BẢNG DANH SÁCH -->
<div class="card" style="margin-bottom: 24px;">
    <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
        <div style="font-size: 1.05rem; font-weight: 700;">
            Danh sách Đơn từ Một Cửa (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)
        </div>
        <div style="display: flex; gap: 8px; align-items: center;">
            <span class="badge" style="background: #fef3c7; color: #92400e; font-size: 0.85rem; padding: 4px 10px;">
                Chờ duyệt: <b><?= (int)($counts['cho_duyet'] ?? 0) ?></b>
            </span>
            <span class="badge" style="background: #e0f2fe; color: #0369a1; font-size: 0.85rem; padding: 4px 10px;">
                Tổng số đơn: <b><?= (int)($counts['all'] ?? 0) ?></b>
            </span>
        </div>
    </div>

    <!-- BỘ LỌC TABS THEO LOẠI ĐƠN -->
    <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
        <a href="<?= url('staff/requests?type=all&status=' . urlencode($activeStatus)) ?>"
            class="btn <?= $activeType === 'all' ? 'btn-primary' : 'btn-outline' ?> btn-sm"
            style="border-radius: 6px; text-decoration: none;">
            Tất cả đơn (<?= (int)($counts['all'] ?? 0) ?>)
        </a>
        <a href="<?= url('staff/requests?type=chuyen-phong&status=' . urlencode($activeStatus)) ?>"
            class="btn <?= $activeType === 'chuyen-phong' ? 'btn-primary' : 'btn-outline' ?> btn-sm"
            style="border-radius: 6px; text-decoration: none;">
            🔄 Xin chuyển phòng (<?= (int)($counts['chuyen_phong'] ?? 0) ?>)
        </a>
        <a href="<?= url('staff/requests?type=tam-vang&status=' . urlencode($activeStatus)) ?>"
            class="btn <?= $activeType === 'tam-vang' ? 'btn-primary' : 'btn-outline' ?> btn-sm"
            style="border-radius: 6px; text-decoration: none;">
            ✈️ Xin tạm vắng (<?= (int)($counts['tam_vang'] ?? 0) ?>)
        </a>
        <a href="<?= url('staff/requests?type=tra-phong&status=' . urlencode($activeStatus)) ?>"
            class="btn <?= $activeType === 'tra-phong' ? 'btn-primary' : 'btn-outline' ?> btn-sm"
            style="border-radius: 6px; text-decoration: none;">
            🚪 Trả phòng sớm (<?= (int)($counts['tra_phong'] ?? 0) ?>)
        </a>
    </div>

    <!-- FORM TÌM KIẾM & LỌC TRẠNG THÁI -->
    <form method="GET" action="<?= url('staff/requests') ?>" style="display: flex; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <input type="hidden" name="type" value="<?= htmlspecialchars($activeType) ?>">

        <div style="flex: 1; min-width: 220px;">
            <input type="text" name="search" value="<?= htmlspecialchars($searchVal) ?>"
                placeholder="🔍 Tìm theo Mã đơn, MSSV, Họ tên..."
                class="form-control" style="width: 100%; padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
        </div>

        <div style="min-width: 160px;">
            <select name="status" class="form-control" style="width: 100%; padding: 7px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem;">
                <option value="all" <?= $activeStatus === 'all' ? 'selected' : '' ?>>Tất cả trạng thái</option>
                <option value="cho-duyet" <?= $activeStatus === 'cho-duyet' ? 'selected' : '' ?>>⏳ Chờ duyệt</option>
                <option value="da-duyet" <?= $activeStatus === 'da-duyet' ? 'selected' : '' ?>>✓ Đã duyệt</option>
                <option value="tu-choi" <?= $activeStatus === 'tu-choi' ? 'selected' : '' ?>>✕ Từ chối</option>
            </select>
        </div>

        <div style="display: flex; gap: 8px;">
            <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 7px 14px;">
                Lọc dữ liệu
            </button>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 7px 14px; text-decoration: none;">
                Đặt lại
            </a>
        </div>
    </form>

    <!-- BẢNG DỮ LIỆU ĐỘNG -->
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 110px;">Mã Đơn</th>
                    <th style="min-width: 180px;">Sinh viên</th>
                    <th style="width: 130px;">Loại đơn</th>
                    <th style="min-width: 280px;">Nội dung chi tiết</th>
                    <th style="width: 140px;">Chênh lệch phí</th>
                    <th style="width: 110px;">Trạng thái</th>
                    <th style="min-width: 200px; text-align: right;">Thao tác BQL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reqList)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 36px 20px; color: var(--text-muted);">
                            <div style="font-size: 2.2rem; margin-bottom: 8px;">📬</div>
                            <div style="font-size: 0.95rem; font-weight: 500;">Không có đơn từ nào phù hợp với bộ lọc hiện tại.</div>
                            <div style="font-size: 0.85rem; margin-top: 4px;">Thử chọn Tab khác hoặc đặt lại bộ lọc tìm kiếm.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reqList as $req): ?>
                        <?php
                        $loaiDonLower = mb_strtolower($req['LoaiDon'] ?? '', 'UTF-8');
                        $isChuyenPhong = str_contains($loaiDonLower, 'chuyển phòng');
                        $isTamVang = str_contains($loaiDonLower, 'tạm vắng');
                        $isTraPhong = str_contains($loaiDonLower, 'trả phòng');
                        $isPending = ($req['TrangThaiDuyet'] === 'Chờ duyệt');
                        ?>
                        <tr style="<?= $isPending ? 'background: #ffffff;' : 'background: #fafafa;' ?>">
                            <!-- Mã Đơn -->
                            <td>
                                <b style="font-family: monospace; color: var(--primary);"><?= htmlspecialchars($req['MaDon']) ?></b>
                            </td>

                            <!-- Sinh viên -->
                            <td>
                                <div style="font-weight: 600; color: var(--text-primary);">
                                    <?= htmlspecialchars($req['HoVaTen'] ?? $req['MaSinhVien']) ?>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.4;">
                                    MSSV: <b><?= htmlspecialchars($req['MSSVTaiTruong'] ?? $req['MaSinhVien']) ?></b>
                                    <?php if (!empty($req['TenTruong'])): ?>
                                        <br><?= htmlspecialchars($req['TenTruong']) ?>
                                    <?php endif; ?>
                                    <?php if (!empty($req['SoDienThoai'])): ?>
                                        &bull; SĐT: <?= htmlspecialchars($req['SoDienThoai']) ?>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Loại đơn -->
                            <td>
                                <?php if ($isChuyenPhong): ?>
                                    <span class="badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;">🔄 Chuyển phòng</span>
                                <?php elseif ($isTamVang): ?>
                                    <span class="badge" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;">✈️ Tạm vắng</span>
                                <?php elseif ($isTraPhong): ?>
                                    <span class="badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">🚪 Trả phòng</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #f3f4f6; color: #475569; border: 1px solid #e2e8f0;">📄 <?= htmlspecialchars($req['LoaiDon'] ?? 'Khác') ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Nội dung chi tiết -->
                            <td>
                                <?php if ($isChuyenPhong): ?>
                                    <div style="font-size: 0.85rem; line-height: 1.5;">
                                        <div>
                                            Hiện tại: <b>Phòng <?= htmlspecialchars($req['SoPhongHienTai'] ?? $req['MaPhongHienTai'] ?? 'N/A') ?></b>
                                            (Giường <?= htmlspecialchars($req['MaGiuongHienTai'] ?? 'N/A') ?>)
                                        </div>
                                        <div style="color: #0369a1;">
                                            &rarr; Chuyển sang: <b>Phòng <?= htmlspecialchars($req['SoPhongMoi'] ?? $req['MaPhongMoi'] ?? 'N/A') ?></b>
                                            (Giường <b><?= htmlspecialchars($req['MaGiuongChuyenDen'] ?? 'N/A') ?></b>)
                                        </div>
                                        <?php renderRequestReason($req['LyDo'] ?? ''); ?>
                                    </div>
                                <?php elseif ($isTamVang): ?>
                                    <div style="font-size: 0.85rem; line-height: 1.5;">
                                        <div>
                                            Thời gian: <b><?= !empty($req['TuNgay']) ? date('d/m/Y', strtotime($req['TuNgay'])) : '?' ?></b>
                                            &rarr; <b><?= !empty($req['DenNgay']) ? date('d/m/Y', strtotime($req['DenNgay'])) : '?' ?></b>
                                            <?php
                                            if (!empty($req['TuNgay']) && !empty($req['DenNgay'])) {
                                                $d1 = new DateTime($req['TuNgay']);
                                                $d2 = new DateTime($req['DenNgay']);
                                                $days = $d1->diff($d2)->days + 1;
                                                echo " <span style='color: var(--text-muted); font-size: 0.8rem;'>({$days} ngày)</span>";
                                            }
                                            ?>
                                        </div>
                                        <?php renderRequestReason($req['LyDo'] ?? ''); ?>
                                    </div>
                                <?php elseif ($isTraPhong): ?>
                                    <div style="font-size: 0.85rem; line-height: 1.5;">
                                        <div>
                                            Hợp đồng: <b><?= htmlspecialchars($req['MaHopDong'] ?? 'N/A') ?></b>
                                        </div>
                                        <?php renderRequestReason($req['LyDo'] ?? ''); ?>
                                    </div>
                                <?php else: ?>
                                    <div style="font-size: 0.85rem;">
                                        <?php renderRequestReason($req['LyDo'] ?? 'Không có mô tả'); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Chênh lệch phí / Hoàn tiền -->
                            <td>
                                <?php
                                $tien = (float)($req['SoTienChenhLechHoan'] ?? 0);
                                if ($tien > 0): ?>
                                    <b style="color: #b91c1c; font-size: 0.9rem;">+<?= number_format($tien, 0, ',', '.') ?> đ</b>
                                <?php elseif ($tien < 0): ?>
                                    <b style="color: #15803d; font-size: 0.9rem;">Hoàn: <?= number_format(abs($tien), 0, ',', '.') ?> đ</b>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">0 đ</span>
                                <?php endif; ?>
                            </td>

                            <!-- Trạng thái duyệt -->
                            <td>
                                <?php if ($req['TrangThaiDuyet'] === 'Chờ duyệt'): ?>
                                    <span class="badge" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;">
                                        ⏳ Chờ duyệt
                                    </span>
                                <?php elseif ($req['TrangThaiDuyet'] === 'Đã duyệt'): ?>
                                    <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;">
                                        ✓ Đã duyệt
                                    </span>
                                <?php elseif ($req['TrangThaiDuyet'] === 'Từ chối' || $req['TrangThaiDuyet'] === 'Đã từ chối'): ?>
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
                                        ✕ Từ chối
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-secondary"><?= htmlspecialchars($req['TrangThaiDuyet']) ?></span>
                                <?php endif; ?>
                            </td>

                            <!-- Thao tác BQL -->
                            <td style="text-align: right;">
                                <?php if ($isPending): ?>
                                    <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                                        <!-- Form Duyệt -->
                                        <form method="POST" action="<?= url('staff/requests/approve') ?>" style="display: inline;"
                                            onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt đơn này không?');">
                                            <input type="hidden" name="maDon" value="<?= htmlspecialchars($req['MaDon']) ?>">

                                            <?php if ($isChuyenPhong): ?>
                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                                                    ✓ Duyệt đổi giường
                                                </button>
                                            <?php elseif ($isTamVang): ?>
                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                                                    ✓ Duyệt tạm vắng
                                                </button>
                                            <?php elseif ($isTraPhong): ?>
                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                                                    📋 Nghiệm thu & Trả phòng
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px; font-weight: 500;">
                                                    ✓ Phê duyệt
                                                </button>
                                            <?php endif; ?>
                                        </form>

                                        <!-- Nút mở Modal Từ chối -->
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #dc2626; border-color: #fca5a5;"
                                            onclick="openRejectModal('<?= htmlspecialchars($req['MaDon'], ENT_QUOTES) ?>', '<?= htmlspecialchars($req['HoVaTen'] ?? $req['MaSinhVien'], ENT_QUOTES) ?>')">
                                            ✕ Từ chối
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: var(--text-muted);">
                                        Đã hoàn tất
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- MODAL POPUP TỪ CHỐI ĐƠN -->
<div id="rejectModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
    <div class="card" style="width: 100%; max-width: 480px; margin: 60px auto; padding: 24px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); background: #ffffff;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #991b1b; display: flex; align-items: center; gap: 8px;">
                <span>✕ Từ chối Đơn từ Một Cửa</span>
            </h3>
            <button type="button" onclick="closeRejectModal()" style="background: none; border: none; font-size: 1.3rem; cursor: pointer; color: var(--text-muted);">&times;</button>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
            Vui lòng nhập lý do từ chối đơn <b id="rejectModalMaDon"></b> của sinh viên <b id="rejectModalTenSV"></b>. Lý do này sẽ được phản hồi công khai cho sinh viên trên Cổng dịch vụ.
        </p>

        <form method="POST" action="<?= url('staff/requests/reject') ?>">
            <input type="hidden" name="maDon" id="modalInputMaDon" value="">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Lý do từ chối:</label>
                <textarea name="lyDoTuChoi" class="form-control" rows="3" required
                    style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; resize: vertical;"
                    placeholder="Ví dụ: Giường chuyển đến đang bảo trì, không đáp ứng điều kiện quy định..."></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRejectModal()" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-danger btn-sm" style="border-radius: 6px; background: #dc2626; color: #ffffff;">
                    Xác nhận từ chối đơn
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRejectModal(maDon, tenSV) {
        document.getElementById('modalInputMaDon').value = maDon;
        document.getElementById('rejectModalMaDon').innerText = maDon;
        document.getElementById('rejectModalTenSV').innerText = tenSV;
        document.getElementById('rejectModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').style.display = 'none';
    }

    // Đóng modal khi bấm ra ngoài khung
    window.onclick = function(event) {
        var modal = document.getElementById('rejectModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
</script>

<?php
$content = ob_get_clean();
$title = "Dịch vụ Một Cửa - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>