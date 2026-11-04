<?php
ob_start();
?>

<div class="container" style="max-width: 1400px; margin: 0 auto; padding: 20px 15px;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: #64748b; margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: #64748b; text-decoration: none;">BQL KTX</a>
            <span>/</span>
            <span style="color: #1e293b; font-weight: 500;">An ninh Ra Vào & Đánh giá Rèn luyện Nội trú</span>
        </div>

        <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 15px;">
            <div>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                    <h1 class="page-title" style="margin: 0; font-size: 1.6rem; color: #0f172a;">Giám sát An ninh Ra Vào & Điểm Rèn luyện</h1>
                    <span class="badge <?= htmlspecialchars($scope['badgeClass'] ?? 'badge-info') ?>" style="padding: 4px 10px; font-size: 0.8rem; border-radius: 6px;">
                        <?= htmlspecialchars($scope['title'] ?? 'Cán bộ KTX') ?>
                    </span>
                </div>
                <p class="page-subtitle" style="margin: 0; font-size: 0.875rem; color: #64748b;">
                    Theo dõi lịch sử quét thẻ cổng barrier, tự động đối soát phát hiện vắng mặt > 30 ngày, ghi nhận lỗi vi phạm/khen thưởng và tổng kết điểm rèn luyện gửi các Trường ĐH thành viên.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px; font-size: 0.85rem;">
                    &larr; Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Thông báo Flash Messages -->
    <?php if (!empty($flashSuccess)): ?>
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
            <span>✓</span> <strong>Thành công:</strong> <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
            <span>✕</span> <strong>Lỗi:</strong> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px;">
            <span>⚠️</span> <strong>Lưu ý:</strong> <?= htmlspecialchars($flashWarning) ?>
        </div>
    <?php endif; ?>

    <!-- THẺ THỐNG KÊ NHANH (KPI STATS) -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 14px; margin-bottom: 20px;">
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Sinh viên đang theo dõi</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #1e293b; margin-top: 4px;"><?= number_format($stats['totalResident']) ?></div>
            <div style="font-size: 0.75rem; color: #0284c7; margin-top: 4px;">Đang lưu trú có hợp đồng</div>
        </div>

        <div style="background: #fff; border: 1px solid <?= ($stats['absenceWarnings'] > 0) ? '#fecaca' : '#e2e8f0' ?>; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 0.78rem; font-weight: 600; color: <?= ($stats['absenceWarnings'] > 0) ? '#dc2626' : '#64748b' ?>; text-transform: uppercase;">Cảnh báo vắng > 30 ngày</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: <?= ($stats['absenceWarnings'] > 0) ? '#dc2626' : '#1e293b' ?>; margin-top: 4px; display: flex; align-items: center; gap: 8px;">
                <span><?= number_format($stats['absenceWarnings']) ?></span>
                <?php if ($stats['absenceWarnings'] > 0): ?>
                    <span class="badge" style="background: #dc2626; color: #fff; font-size: 0.7rem; padding: 2px 6px;">Cần xử lý</span>
                <?php endif; ?>
            </div>
            <div style="font-size: 0.75rem; color: #dc2626; margin-top: 4px;">Không có đơn tạm vắng</div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Lượt quét Barrier</div>
            <div style="font-size: 1.6rem; font-weight: 700; color: #2563eb; margin-top: 4px;"><?= number_format($stats['totalBarrierScans']) ?></div>
            <div style="font-size: 0.75rem; color: #16a34a; margin-top: 4px;">✓ Dữ liệu cổng thời gian thực</div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="font-size: 0.78rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Xếp loại rèn luyện</div>
            <div style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-top: 8px; display: flex; gap: 6px; flex-wrap: wrap;">
                <span class="badge badge-success" style="font-size: 0.75rem;">XS: <?= $stats['excellentCount'] ?></span>
                <span class="badge badge-info" style="font-size: 0.75rem;">Tốt: <?= $stats['goodCount'] ?></span>
                <span class="badge badge-warning" style="font-size: 0.75rem;">Khá: <?= $stats['fairCount'] ?></span>
                <?php if ($stats['weakCount'] > 0): ?>
                    <span class="badge badge-danger" style="font-size: 0.75rem;">Yếu: <?= $stats['weakCount'] ?></span>
                <?php endif; ?>
            </div>
            <div style="font-size: 0.75rem; color: #64748b; margin-top: 6px;">
                Đã gửi trường: <strong><?= $stats['lockedCount'] ?>/<?= $stats['totalResident'] ?></strong>
            </div>
        </div>
    </div>

    <!-- KHUNG ĐIỀU HƯỚNG TAB & BỘ LỌC TỔNG THỂ -->
    <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
            <!-- Tabs điều hướng -->
            <div style="display: flex; gap: 6px; background: #f1f5f9; padding: 4px; border-radius: 8px;">
                <a href="<?= url('staff/discipline?tab=canh-bao&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   style="padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; <?= ($activeTab === 'canh-bao') ? 'background: #fff; color: #dc2626; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' ?>">
                    <span>🚨</span> Cảnh báo Vắng mặt > 30 ngày
                    <?php if ($stats['absenceWarnings'] > 0): ?>
                        <span style="background: #dc2626; color: #fff; border-radius: 10px; padding: 1px 6px; font-size: 0.7rem;">
                            <?= $stats['absenceWarnings'] ?>
                        </span>
                    <?php endif; ?>
                </a>

                <a href="<?= url('staff/discipline?tab=barrier&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   style="padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; <?= ($activeTab === 'barrier') ? 'background: #fff; color: #2563eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' ?>">
                    <span>🛡️</span> Giám sát Quét thẻ Barrier (<?= $stats['totalBarrierScans'] ?>)
                </a>

                <a href="<?= url('staff/discipline?tab=ren-luyen&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   style="padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: flex; align-items: center; gap: 6px; <?= ($activeTab === 'ren-luyen') ? 'background: #fff; color: #16a34a; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' : 'color: #64748b;' ?>">
                    <span>⭐</span> Điểm Rèn luyện & Kỷ luật (<?= $stats['totalResident'] ?>)
                </a>
            </div>

            <!-- Form Bộ lọc chung (Năm học, Học kỳ, Cụm/Khu, Tìm kiếm) -->
            <form action="<?= url('staff/discipline') ?>" method="GET" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin: 0;">
                <input type="hidden" name="tab" value="<?= htmlspecialchars($activeTab) ?>">

                <select name="namHoc" onchange="this.form.submit()" style="padding: 7px 10px; border-radius: 6px; font-size: 0.82rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="2026-2027" <?= ($namHoc === '2026-2027') ? 'selected' : '' ?>>Năm học 2026-2027</option>
                    <option value="2025-2026" <?= ($namHoc === '2025-2026') ? 'selected' : '' ?>>Năm học 2025-2026</option>
                </select>

                <select name="hocKy" onchange="this.form.submit()" style="padding: 7px 10px; border-radius: 6px; font-size: 0.82rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="1" <?= ($hocKy === 1) ? 'selected' : '' ?>>Học kỳ 1</option>
                    <option value="2" <?= ($hocKy === 2) ? 'selected' : '' ?>>Học kỳ 2</option>
                    <option value="3" <?= ($hocKy === 3) ? 'selected' : '' ?>>Học kỳ hè</option>
                </select>

                <?php if ($scope['canFilterAll']): ?>
                    <select name="cum" onchange="this.form.submit()" style="padding: 7px 10px; border-radius: 6px; font-size: 0.82rem; border: 1px solid #cbd5e1; background: #fff;">
                        <option value="all" <?= ($selectedCum === 'all') ? 'selected' : '' ?>>-- Toàn bộ KTX --</option>
                        <option value="khu-A" <?= ($selectedCum === 'khu-A') ? 'selected' : '' ?>>🏢 Toàn bộ Khu A</option>
                        <option value="khu-B" <?= ($selectedCum === 'khu-B') ? 'selected' : '' ?>>🏢 Toàn bộ Khu B</option>
                        <?php foreach ($clusters as $c): ?>
                            <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($selectedCum === $c['MaCum']) ? 'selected' : '' ?>>
                                Cụm <?= htmlspecialchars($c['MaCum']) ?> - <?= htmlspecialchars($c['TenCum']) ?> (Khu <?= htmlspecialchars($c['MaKhu']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <div style="position: relative;">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm MSSV, họ tên, phòng..."
                           style="padding: 7px 10px 7px 28px; border-radius: 6px; font-size: 0.82rem; border: 1px solid #cbd5e1; width: 190px;">
                    <span style="position: absolute; left: 8px; top: 7px; color: #94a3b8; font-size: 0.8rem;">🔍</span>
                </div>

                <button type="submit" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 7px 12px; font-size: 0.82rem;">Lọc</button>
                <?php if ($search || $selectedCum !== 'all'): ?>
                    <a href="<?= url('staff/discipline?tab=' . $activeTab) ?>" style="font-size: 0.78rem; color: #ef4444; text-decoration: none;">✕ Xóa lọc</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- ========================================================================= -->
    <!-- TAB 1: CẢNH BÁO SINH VIÊN VẮNG MẶT > 30 NGÀY LIÊN TỤC KHÔNG PHÉP -->
    <!-- ========================================================================= -->
    <?php if ($activeTab === 'canh-bao'): ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-left: 5px solid #dc2626; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>🚨</span> Danh Sách Cảnh Báo Sinh Viên Vắng Mặt Quá 30 Ngày Không Phép
                    </h2>
                    <p style="margin: 4px 0 0 0; font-size: 0.82rem; color: #64748b;">
                        Đối soát tự động giữa lần quét cổng barrier gần nhất và bảng đơn từ trực tuyến <code>DonTuMotCua</code>. Các trường hợp không có đơn tạm vắng hợp lệ được liệt kê dưới đây:
                    </p>
                </div>
                <div>
                    <span class="badge" style="background: #fee2e2; color: #dc2626; font-size: 0.82rem; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                        <?= count($absenceWarnings) ?> trường hợp cần xác minh
                    </span>
                </div>
            </div>

            <?php if (empty($absenceWarnings)): ?>
                <div style="text-align: center; padding: 40px 20px; background: #f0fdf4; border: 1px dashed #86efac; border-radius: 8px;">
                    <div style="font-size: 2.2rem; margin-bottom: 8px;">🎉</div>
                    <strong style="color: #15803d; font-size: 0.95rem;">Không phát hiện trường hợp sinh viên nào vắng mặt quá 30 ngày không phép!</strong>
                    <p style="margin: 4px 0 0 0; font-size: 0.82rem; color: #166534;">
                        Tất cả sinh viên nội trú đều có lượt quét thẻ barrier đều đặn hoặc có đơn xin tạm vắng đã duyệt hợp lệ.
                    </p>
                </div>
            <?php else: ?>
                <div class="table-responsive" style="overflow-x: auto;">
                    <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: left;">
                                <th style="padding: 10px;">MSSV</th>
                                <th style="padding: 10px;">Họ và Tên</th>
                                <th style="padding: 10px;">Vị trí Phòng / Giường</th>
                                <th style="padding: 10px;">Trường Đại học</th>
                                <th style="padding: 10px;">Lần quét thẻ cuối</th>
                                <th style="padding: 10px;">Số ngày vắng</th>
                                <th style="padding: 10px;">Đơn xin tạm vắng</th>
                                <th style="padding: 10px; text-align: right;">Biện pháp xử lý</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($absenceWarnings as $w): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; background: #fff5f5;">
                                    <td style="padding: 12px 10px; font-family: monospace; font-weight: 700; color: #1e293b;">
                                        <?= htmlspecialchars($w['maSinhVien']) ?>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($w['hoVaTen']) ?></div>
                                        <div style="font-size: 0.75rem; color: #64748b;">SĐT: <?= htmlspecialchars($w['soDienThoai']) ?></div>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <div style="font-weight: 600; color: #2563eb;">Phòng <?= htmlspecialchars($w['maPhong']) ?> (<?= htmlspecialchars($w['maGiuong']) ?>)</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">Tòa <?= htmlspecialchars($w['maToaNha']) ?> &bull; Cụm <?= htmlspecialchars($w['maCum']) ?></div>
                                    </td>
                                    <td style="padding: 12px 10px; color: #334155;">
                                        <?= htmlspecialchars($w['tenTruong']) ?>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <div style="color: #1e293b; font-weight: 500;">
                                            <?= htmlspecialchars($w['ngayQuetCuoi']) ?>
                                        </div>
                                        <div style="font-size: 0.74rem; color: #64748b;">
                                            Cổng: <?= htmlspecialchars($w['congCuoi']) ?> (<?= htmlspecialchars($w['huongCuoi']) ?>)
                                        </div>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <b style="color: #b91c1c; font-size: 1rem;"><?= htmlspecialchars($w['soNgayVang']) ?> ngày</b>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <span class="badge" style="background: #fee2e2; color: #991b1b; padding: 2px 7px; border-radius: 4px; font-size: 0.72rem; font-weight: 600;">
                                            ✕ Không có đơn
                                        </span>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: right;">
                                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.75rem; padding: 4px 8px;"
                                                    onclick="openAbsenceActionModal('<?= htmlspecialchars($w['maSinhVien']) ?>', '<?= htmlspecialchars($w['hoVaTen']) ?>', '<?= htmlspecialchars($w['sdtPhuHuynh']) ?>', 'lien_he')">
                                                📞 Liên hệ
                                            </button>
                                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px; font-size: 0.75rem; padding: 4px 8px; background: #dc2626; border-color: #dc2626;"
                                                    onclick="openAbsenceActionModal('<?= htmlspecialchars($w['maSinhVien']) ?>', '<?= htmlspecialchars($w['hoVaTen']) ?>', '<?= htmlspecialchars($w['sdtPhuHuynh']) ?>', 'lap_bien_ban')">
                                                📝 Lập biên bản (-25đ)
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- TAB 2: GIÁM SÁT NHẬT KÝ QUÉT THẺ BARRIER CỔNG KTX (READ-ONLY) -->
    <!-- ========================================================================= -->
    <?php if ($activeTab === 'barrier'): ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>🛡️</span> Nhật Ký Quét Thẻ Ra - Vào Cổng Barrier (Dữ Liệu Chỉ Đọc)
                    </h2>
                    <p style="margin: 4px 0 0 0; font-size: 0.82rem; color: #64748b;">
                        Dữ liệu lưu vết thời gian thực tại các cổng barrier phân khu KTX. Dữ liệu chỉ đọc từ thiết bị đọc thẻ/FaceID.
                    </p>
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px; background: #0284c7; border: none; font-size: 0.82rem; display: flex; align-items: center; gap: 5px;"
                            onclick="openSimulateScanModal()">
                        <span>⚡</span> Giả lập quét thẻ Barrier (Demo)
                    </button>
                </div>
            </div>

            <!-- Thanh lọc hướng đi -->
            <div style="display: flex; gap: 10px; margin-bottom: 14px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 0.82rem; font-weight: 600; color: #475569;">Lọc hướng:</span>
                <a href="<?= url('staff/discipline?tab=barrier&huongDi=&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   class="badge" style="padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= empty($huongDi) ? 'background: #0f172a; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                    Tất cả hướng
                </a>
                <a href="<?= url('staff/discipline?tab=barrier&huongDi=Vào&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   class="badge" style="padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= ($huongDi === 'Vào') ? 'background: #16a34a; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                    ⬇️ Vào KTX
                </a>
                <a href="<?= url('staff/discipline?tab=barrier&huongDi=Ra&namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy . '&cum=' . urlencode($selectedCum)) ?>"
                   class="badge" style="padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 0.78rem; <?= ($huongDi === 'Ra') ? 'background: #2563eb; color: #fff;' : 'background: #f1f5f9; color: #475569;' ?>">
                    ⬆️ Ra ngoài KTX
                </a>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: left;">
                            <th style="padding: 10px;">Mã lượt</th>
                            <th style="padding: 10px;">Sinh viên</th>
                            <th style="padding: 10px;">Phòng / Tòa / Cụm</th>
                            <th style="padding: 10px;">Trường Đại học</th>
                            <th style="padding: 10px;">Cổng kiểm soát</th>
                            <th style="padding: 10px;">Hướng đi</th>
                            <th style="padding: 10px;">Thời gian quét</th>
                            <th style="padding: 10px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($barrierLogs)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; padding: 30px; color: #94a3b8;">
                                    Không có dữ liệu nhật ký quét thẻ nào phù hợp với bộ lọc hiện tại.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($barrierLogs as $log): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="padding: 10px; font-family: monospace; color: #64748b; font-weight: 600;">
                                        #<?= htmlspecialchars($log['MaLuot']) ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <div style="font-weight: 600; color: #1e293b;"><?= htmlspecialchars($log['HoVaTen']) ?></div>
                                        <div style="font-size: 0.75rem; font-family: monospace; color: #2563eb;"><?= htmlspecialchars($log['MaSinhVien']) ?></div>
                                    </td>
                                    <td style="padding: 10px; color: #475569;">
                                        <?php if (!empty($log['MaPhong'])): ?>
                                            <b><?= htmlspecialchars($log['MaPhong']) ?></b> &bull; Tòa <?= htmlspecialchars($log['MaToaNha']) ?> (Cụm <?= htmlspecialchars($log['TenCum'] ?? 'A') ?>)
                                        <?php else: ?>
                                            <span style="color: #94a3b8; font-style: italic;">Chưa xếp phòng</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px; color: #475569;">
                                        <?= htmlspecialchars($log['TenTruong'] ?? $log['MaTruong']) ?>
                                    </td>
                                    <td style="padding: 10px; font-weight: 500; color: #334155;">
                                        <?= htmlspecialchars($log['CongKiemSoat']) ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <?php if ($log['HuongDi'] === 'Vào'): ?>
                                            <span class="badge" style="background: #dcfce7; color: #15803d; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.74rem;">
                                                ⬇️ Vào KTX
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-weight: 600; font-size: 0.74rem;">
                                                ⬆️ Ra ngoài
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 10px; color: #1e293b; font-family: monospace;">
                                        <?= date('d/m/Y H:i:s', strtotime($log['ThoiGianQuet'])) ?>
                                    </td>
                                    <td style="padding: 10px;">
                                        <span style="color: #15803d; font-weight: 600; font-size: 0.78rem;">✓ Hợp lệ</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- ========================================================================= -->
    <!-- TAB 3: QUẢN LÝ ĐIỂM RÈN LUYỆN NỘI TRÚ & KỶ LUẬT HỌC KỲ -->
    <!-- ========================================================================= -->
    <?php if ($activeTab === 'ren-luyen'): ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 20px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                <div>
                    <h2 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <span>⭐</span> Bảng Tổng Kết Điểm Rèn Luyện & Kỷ Luật Nội Trú (Học kỳ <?= $hocKy ?> / Năm <?= htmlspecialchars($namHoc) ?>)
                    </h2>
                    <p style="margin: 4px 0 0 0; font-size: 0.82rem; color: #64748b;">
                        Khởi điểm 100 điểm/kỳ; ghi nhận sự kiện khen thưởng (+) và vi phạm nội quy (-). Chốt gửi điểm về Trường thành viên để xét học bổng.
                    </p>
                </div>

                <!-- Các nút tác vụ nhanh -->
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px; font-size: 0.82rem; background: #059669; border: none; display: flex; align-items: center; gap: 5px;"
                            onclick="openRecordModal()">
                        <span>➕</span> Ghi nhận Vi phạm / Khen thưởng
                    </button>

                    <?php if ($scope['canLockPoints']): ?>
                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.82rem; border-color: #f59e0b; color: #b45309; display: flex; align-items: center; gap: 5px;"
                                onclick="openLockSubmitModal()">
                            <span>🔒</span> Chốt & Gửi điểm về Trường
                        </button>
                    <?php endif; ?>

                    <a href="<?= url('staff/discipline/export?namHoc=' . urlencode($namHoc) . '&hocKy=' . $hocKy) ?>"
                       class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.82rem; display: flex; align-items: center; gap: 5px;">
                        <span>📥</span> Xuất Excel/CSV
                    </a>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto;">
                <table class="data-table" style="width: 100%; border-collapse: collapse; font-size: 0.84rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-align: left;">
                            <th style="padding: 10px;">MSSV</th>
                            <th style="padding: 10px;">Họ và Tên</th>
                            <th style="padding: 10px;">Vị trí Phòng / Tòa</th>
                            <th style="padding: 10px;">Trường Đại học</th>
                            <th style="padding: 10px;">Điểm gốc</th>
                            <th style="padding: 10px;">Điểm +/-</th>
                            <th style="padding: 10px;">Điểm cuối kỳ</th>
                            <th style="padding: 10px;">Xếp loại</th>
                            <th style="padding: 10px;">Tình trạng gửi trường</th>
                            <th style="padding: 10px; text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($summaryList)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 30px; color: #94a3b8;">
                                    Không tìm thấy dữ liệu sinh viên nào trong phạm vi quản lý.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($summaryList as $row): ?>
                                <?php
                                $score = (int)$row['TongDiemCuoiKy'];
                                $rating = $row['XepLoai'];
                                $isLocked = ((int)$row['DaGuiVeTruong'] === 1);
                                $totalChange = (int)$row['TotalChange'];
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; <?= $isLocked ? 'background: #f8fafc;' : '' ?>">
                                    <td style="padding: 12px 10px; font-family: monospace; font-weight: 700; color: #1e293b;">
                                        <?= htmlspecialchars($row['MaSinhVien']) ?>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <div style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($row['HoVaTen']) ?></div>
                                        <div style="font-size: 0.74rem; color: #64748b;">SĐT: <?= htmlspecialchars($row['SoDienThoai']) ?></div>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <span style="font-weight: 600; color: #2563eb;">Phòng <?= htmlspecialchars($row['MaPhong']) ?></span> &bull; Tòa <?= htmlspecialchars($row['MaToaNha']) ?>
                                    </td>
                                    <td style="padding: 12px 10px; color: #475569;">
                                        <?= htmlspecialchars($row['TenTruong'] ?? $row['MaTruong']) ?>
                                    </td>
                                    <td style="padding: 12px 10px; color: #64748b;">
                                        100 đ
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <?php if ($totalChange > 0): ?>
                                            <b style="color: #16a34a;">+<?= $totalChange ?> đ</b>
                                        <?php elseif ($totalChange < 0): ?>
                                            <b style="color: #dc2626;"><?= $totalChange ?> đ</b>
                                        <?php else: ?>
                                            <span style="color: #94a3b8;">0 đ</span>
                                        <?php endif; ?>
                                        <?php if ((int)$row['TotalEvents'] > 0): ?>
                                            <span style="font-size: 0.72rem; color: #64748b;">(<?= $row['TotalEvents'] ?> sự kiện)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <b style="font-size: 1.05rem; color: <?= ($score >= 80) ? '#1e40af' : (($score >= 65) ? '#d97706' : '#dc2626') ?>;">
                                            <?= $score ?> đ
                                        </b>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <?php if ($rating === 'Xuất sắc'): ?>
                                            <span class="badge badge-success" style="padding: 2px 8px; border-radius: 4px; font-size: 0.74rem;">Xuất sắc</span>
                                        <?php elseif ($rating === 'Tốt'): ?>
                                            <span class="badge badge-info" style="padding: 2px 8px; border-radius: 4px; font-size: 0.74rem;">Tốt</span>
                                        <?php elseif ($rating === 'Khá'): ?>
                                            <span class="badge badge-warning" style="padding: 2px 8px; border-radius: 4px; font-size: 0.74rem;">Khá</span>
                                        <?php elseif ($rating === 'Trung bình'): ?>
                                            <span class="badge" style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 4px; font-size: 0.74rem;">Trung bình</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger" style="padding: 2px 8px; border-radius: 4px; font-size: 0.74rem;">Yếu (Cảnh báo)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 10px;">
                                        <?php if ($isLocked): ?>
                                            <span class="badge" style="background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600;">
                                                🔒 Đã gửi trường (Đã khóa)
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #fef3c7; color: #92400e; padding: 3px 8px; border-radius: 4px; font-size: 0.72rem; font-weight: 600;">
                                                🟡 Đang cập nhật
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px 10px; text-align: right;">
                                        <div style="display: flex; gap: 6px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.75rem; padding: 4px 8px;"
                                                    onclick="viewStudentHistory('<?= htmlspecialchars($row['MaSinhVien']) ?>')">
                                                🔍 Lịch sử
                                            </button>

                                            <?php if (!$isLocked): ?>
                                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.75rem; padding: 4px 8px;"
                                                        onclick="openRecordModalForStudent('<?= htmlspecialchars($row['MaSinhVien']) ?>', '<?= htmlspecialchars($row['HoVaTen']) ?>')">
                                                    ➕ Ghi nhận
                                                </button>
                                            <?php else: ?>
                                                <span style="font-size: 0.75rem; color: #94a3b8; font-style: italic; align-self: center;">Đã khóa</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: GHI NHẬN SỰ KIỆN RÈN LUYỆN / VI PHẠM / KHEN THƯỞNG -->
<!-- ========================================================================= -->
<div id="recordModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 520px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>➕</span> Ghi Nhận Điểm Rèn Luyện / Vi Phạm
            </h3>
            <button type="button" onclick="closeRecordModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/record') ?>" method="POST">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= $hocKy ?>">

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">
                    Sinh viên: <span style="color: #dc2626;">*</span>
                </label>
                <select name="maSinhVien" id="recordSelectSinhVien" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="">-- Chọn sinh viên --</option>
                    <?php foreach ($residentStudents as $s): ?>
                        <option value="<?= htmlspecialchars($s['MaSinhVien']) ?>">
                            <?= htmlspecialchars($s['HoVaTen']) ?> (<?= htmlspecialchars($s['MaSinhVien']) ?>) - Phòng <?= htmlspecialchars($s['MaPhong']) ?> - Tòa <?= htmlspecialchars($s['MaToaNha']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Loại ghi nhận: <span style="color: #dc2626;">*</span></label>
                    <select name="loaiGhiNhan" id="selectLoaiGhiNhan" onchange="onLoaiGhiNhanChange()" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                        <option value="Hoạt động">Hoạt động phong trào</option>
                        <option value="Khen thưởng">Khen thưởng KTX</option>
                        <option value="Vi phạm">Vi phạm nội quy</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Điểm thay đổi (+/-): <span style="color: #dc2626;">*</span></label>
                    <input type="number" name="diemThayDoi" id="inputDiemThayDoi" required value="10" placeholder="+10 hoặc -15" style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Nội dung chi tiết sự việc: <span style="color: #dc2626;">*</span></label>
                <textarea name="noiDung" id="inputNoiDung" rows="3" required placeholder="Ví dụ: Tham gia ngày Chủ nhật xanh dọn dẹp vệ sinh khuôn viên tòa nhà..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeRecordModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #059669; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">💾 Lưu ghi nhận</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: XỬ LÝ SINH VIÊN VẮNG MẶT KÉO DÀI > 30 NGÀY -->
<!-- ========================================================================= -->
<div id="absenceActionModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>📝</span> Xử Lý Sinh Viên Vắng Mặt Kéo Dài
            </h3>
            <button type="button" onclick="closeAbsenceActionModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <form action="<?= url('staff/discipline/absence-action') ?>" method="POST">
            <input type="hidden" name="maSinhVien" id="absenceMaSinhVien">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= $hocKy ?>">

            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 14px; font-size: 0.85rem; line-height: 1.6;">
                <div><strong>Sinh viên:</strong> <span id="absenceStudentName" style="font-weight: 600; color: #0f172a;"></span> (<span id="absenceStudentCode" style="font-family: monospace;"></span>)</div>
                <div><strong>SĐT Phụ huynh:</strong> <span id="absenceParentPhone" style="font-weight: 600; color: #dc2626;"></span></div>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Biện pháp xử lý: <span style="color: #dc2626;">*</span></label>
                <select name="hanhDong" id="absenceHanhDong" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="lien_he">📞 Đã gọi điện phụ huynh / Trưởng phòng xác nhận tình trạng</option>
                    <option value="lap_bien_ban">📝 Lập biên bản vi phạm nội quy vắng mặt không phép (Trừ 25 điểm)</option>
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Ghi chú chi tiết kết quả xử lý: <span style="color: #dc2626;">*</span></label>
                <textarea name="ghiChu" rows="3" required placeholder="Ghi nhận thông tin phản hồi từ gia đình sinh viên hoặc biên bản tại phòng..." style="width: 100%; padding: 8px 12px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 0.85rem; box-sizing: border-box;"></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeAbsenceActionModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #dc2626; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">Xác nhận xử lý</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: GIẢ LẬP QUÉT THẺ BARRIER CỔNG KTX -->
<!-- ========================================================================= -->
<div id="simulateScanModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 480px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>⚡</span> Giả Lập Quét Thẻ Cổng Barrier
            </h3>
            <button type="button" onclick="closeSimulateScanModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <p style="font-size: 0.82rem; color: #64748b; margin-top: 0; margin-bottom: 14px;">
            Công cụ hỗ trợ chạy kiểm thử và trình diễn tích hợp thiết bị Barrier tự động. Lượt quẹt sẽ được lưu ngay vào bảng <code>NhatKyRaVao</code>.
        </p>

        <form action="<?= url('staff/discipline/simulate-scan') ?>" method="POST">
            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Sinh viên quẹt thẻ: <span style="color: #dc2626;">*</span></label>
                <select name="maSinhVien" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="">-- Chọn sinh viên nội trú --</option>
                    <?php foreach ($residentStudents as $s): ?>
                        <option value="<?= htmlspecialchars($s['MaSinhVien']) ?>">
                            <?= htmlspecialchars($s['HoVaTen']) ?> (<?= htmlspecialchars($s['MaSinhVien']) ?>) - Phòng <?= htmlspecialchars($s['MaPhong']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom: 14px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Vị trí cổng barrier: <span style="color: #dc2626;">*</span></label>
                <select name="congKiemSoat" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="Cổng chính Khu A - Tòa A01">Cổng chính Khu A - Tòa A01</option>
                    <option value="Cổng phụ Khu A - Tòa H01">Cổng phụ Khu A - Tòa H01</option>
                    <option value="Trạm Barrier Tòa BA1 (Khu B)">Trạm Barrier Tòa BA1 (Khu B)</option>
                    <option value="Cổng Tây Khu B - Tòa C05">Cổng Tây Khu B - Tòa C05</option>
                </select>
            </div>

            <div style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Hướng di chuyển: <span style="color: #dc2626;">*</span></label>
                <select name="huongDi" required style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="Vào">⬇️ Vào KTX</option>
                    <option value="Ra">⬆️ Ra ngoài KTX</option>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeSimulateScanModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #0284c7; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">⚡ Quét thẻ ngay</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 4: XÁC NHẬN CHỐT & KHÓA ĐIỂM RÈN LUYỆN GỬI VỀ TRƯỜNG -->
<!-- ========================================================================= -->
<div id="lockSubmitModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 500px; width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #b45309; display: flex; align-items: center; gap: 8px;">
                <span>🔒</span> Chốt & Gửi Điểm Rèn Luyện Về Trường
            </h3>
            <button type="button" onclick="closeLockSubmitModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: 0.82rem; color: #92400e; line-height: 1.55;">
            <strong>⚠️ Lưu ý quan trọng:</strong><br>
            Khi thực hiện thao tác này, toàn bộ điểm rèn luyện của sinh viên trong phạm vi quản lý sẽ được chuyển sang trạng thái <code>DaGuiVeTruong = 1</code>.
            Hệ thống sẽ <strong>KHÓA CỨNG</strong> chức năng thêm/sửa/xóa điểm rèn luyện của các sinh viên đó để bảo đảm tính pháp lý khi gửi cho Phòng Công tác Sinh viên các trường ĐH.
        </div>

        <form action="<?= url('staff/discipline/lock-submit') ?>" method="POST">
            <input type="hidden" name="namHoc" value="<?= htmlspecialchars($namHoc) ?>">
            <input type="hidden" name="hocKy" value="<?= $hocKy ?>">

            <div style="margin-bottom: 16px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Phạm vi trường gửi điểm (tùy chọn):</label>
                <select name="maTruong" style="width: 100%; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; background: #fff;">
                    <option value="">-- Gửi cho tất cả các Trường ĐH thành viên --</option>
                    <?php foreach ($universities as $u): ?>
                        <option value="<?= htmlspecialchars($u['MaTruong']) ?>">
                            <?= htmlspecialchars($u['TenTruong']) ?> (<?= htmlspecialchars($u['MaTruong']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" onclick="closeLockSubmitModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Hủy</button>
                <button type="submit" style="padding: 8px 18px; border-radius: 6px; border: none; background: #b45309; color: #fff; font-size: 0.85rem; font-weight: 600; cursor: pointer;">🔒 Xác nhận Chốt & Khóa điểm</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 5: XEM LỊCH SỬ CHI TIẾT SỰ KIỆN RÈN LUYỆN CỦA SINH VIÊN -->
<!-- ========================================================================= -->
<div id="historyModal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 10px; max-width: 650px; width: 90%; max-height: 85vh; display: flex; flex-direction: column; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
            <h3 style="margin: 0; font-size: 1.15rem; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <span>🔍</span> Lịch Sử Rèn Luyện Chi Tiết Của Sinh Viên
            </h3>
            <button type="button" onclick="closeHistoryModal()" style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>

        <div id="historyStudentInfo" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 14px; font-size: 0.85rem; line-height: 1.6;">
            <!-- Render qua JS -->
        </div>

        <div style="flex: 1; overflow-y: auto; margin-bottom: 16px;">
            <div id="historyTimelineContainer">
                <!-- Render qua JS -->
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeHistoryModal()" style="padding: 8px 16px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; color: #475569; font-size: 0.85rem; cursor: pointer;">Đóng</button>
        </div>
    </div>
</div>

<script>
/**
 * Đóng mở Modal Ghi nhận
 */
function openRecordModal() {
    document.getElementById('recordSelectSinhVien').value = '';
    document.getElementById('selectLoaiGhiNhan').value = 'Hoạt động';
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
    if (loai === 'Vi phạm') {
        if (parseInt(diem.value) > 0) diem.value = '-10';
    } else if (loai === 'Khen thưởng') {
        if (parseInt(diem.value) < 0) diem.value = '15';
    } else {
        if (parseInt(diem.value) < 0) diem.value = '10';
    }
}

/**
 * Đóng mở Modal Xử lý Vắng mặt kéo dài
 */
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

/**
 * Đóng mở Modal Quét thẻ Giả lập
 */
function openSimulateScanModal() {
    document.getElementById('simulateScanModal').style.display = 'flex';
}
function closeSimulateScanModal() {
    document.getElementById('simulateScanModal').style.display = 'none';
}

/**
 * Đóng mở Modal Khóa điểm gửi trường
 */
function openLockSubmitModal() {
    document.getElementById('lockSubmitModal').style.display = 'flex';
}
function closeLockSubmitModal() {
    document.getElementById('lockSubmitModal').style.display = 'none';
}

/**
 * Đóng mở & Gọi AJAX Modal Xem lịch sử chi tiết
 */
function viewStudentHistory(maSv) {
    const infoBox = document.getElementById('historyStudentInfo');
    const container = document.getElementById('historyTimelineContainer');
    infoBox.innerHTML = '<em>Đang tải thông tin sinh viên...</em>';
    container.innerHTML = '<div style="text-align: center; padding: 20px; color: #64748b;">Đang truy vấn lịch sử rèn luyện...</div>';

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
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">${stu.HoVaTen || maSv} (${stu.MaSinhVien})</div>
                        <div style="font-size: 0.78rem; color: #64748b;">Trường: ${stu.TenTruong || stu.MaTruong || 'KTX'} &bull; Phòng: <b>${stu.MaPhong || 'Chưa rõ'}</b> (Tòa ${stu.MaToaNha || 'Chưa rõ'})</div>
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
                        <div>✨</div>
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
                            <span class="badge" style="background: ${isPositive ? '#dcfce7' : '#fee2e2'}; color: ${color}; font-size: 0.74rem; font-weight: 700;">
                                ${ev.LoaiGhiNhan}
                            </span>
                            <span style="font-weight: 800; font-size: 0.95rem; color: ${color};">
                                ${sign}${ev.DiemThayDoi} điểm
                            </span>
                        </div>
                        <div style="font-size: 0.84rem; color: #1e293b; line-height: 1.45;">
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
</script>

<?php
$content = ob_get_clean();
$title = "An ninh & Rèn luyện - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>