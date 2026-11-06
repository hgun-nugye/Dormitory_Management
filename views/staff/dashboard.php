<?php
ob_start();

$isAllowed = $isAllowed ?? $scope['isAllowed'] ?? false;
$viewType = $viewType ?? 'manager';
?>
<div class="container">

    <!-- ========================================================================= -->
    <!-- TRƯỜNG HỢP BỊ CHẶN: RENDER THẺ CẢNH BÁO ĐỎ (RED WARNING CARD)            -->
    <!-- ========================================================================= -->
    <?php if (empty($isAllowed)): ?>
        <div style="margin-bottom: 20px;">
            <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                <a href="<?= url('') ?>" style="color: var(--text-muted); text-decoration: none;">KTX ĐHQG-HCM</a>
                <span>/</span>
                <span style="color: var(--text-primary); font-weight: 500;">Bảng điều khiển BQL</span>
            </div>
            <h1 class="page-title">Bảng điều khiển BQL Ký túc xá</h1>
        </div>

        <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 36px 24px; text-align: center; margin: 30px auto; max-width: 720px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="width: 68px; height: 68px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; color: #dc2626; font-size: 2rem;">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h2 style="color: #991b1b; font-size: 1.35rem; font-weight: 700; margin-bottom: 10px;">Từ chối quyền truy cập</h2>
            <p style="color: #b91c1c; font-size: 0.95rem; max-width: 580px; margin: 0 auto 24px; line-height: 1.6;">
                <?= htmlspecialchars($scope['message'] ?? 'Bạn đang đăng nhập với tài khoản không có thẩm quyền truy cập phân hệ Quản lý KTX!') ?>
            </p>
            <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= url('student/dashboard') ?>" class="btn btn-primary" style="border-radius: 8px; font-weight: 600; padding: 10px 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-graduation-cap"></i> Về Cổng Dịch vụ Sinh viên
                </a>
                <a href="<?= url('') ?>" class="btn btn-outline" style="border-radius: 8px; font-weight: 600; padding: 10px 20px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-house"></i> Về Trang chủ
                </a>
            </div>
        </div>
    <?php else: ?>

        <!-- ========================================================================= -->
        <!-- HEADER ĐIỀU HÀNH & BREADCRUMB (ĐỒNG BỘ CHUẨN STAFF SYSTEM)               -->
        <!-- ========================================================================= -->
        <div style="margin-bottom: 20px;">
            <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
                <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL KTX</a>
                <span>/</span>
                <span style="color: var(--text-muted);">Trung tâm Điều hành</span>
                <span>/</span>
                <span style="color: var(--text-primary); font-weight: 500;">Tổng quan BQL</span>
            </div>

            <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
                <div>
                    <h1 class="page-title">
                        <?php if ($viewType === 'accounting'): ?>
                            Bảng điều khiển Kế toán & Tài chính KTX
                        <?php elseif ($viewType === 'technical'): ?>
                            Bảng điều khiển Nhiệm vụ Kỹ thuật & Sửa chữa
                        <?php else: ?>
                            Trung tâm Điều hành & Bảng điều khiển BQL KTX
                        <?php endif; ?>
                    </h1>
                    <p class="page-subtitle" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                        <span>Phạm vi cán bộ:</span>
                        <span class="badge" style="background: #fefce8; color: #a16207; border: 1px solid #fef08a; border-radius: 6px; font-weight: 600; padding: 4px 10px; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-building-user"></i> <?= htmlspecialchars($scope['title'] ?? 'Cán bộ Quản lý') ?>
                        </span>
                        <span style="color: var(--text-muted);">&bull;</span>
                        <span style="color: var(--text-secondary); font-size: 0.85rem;">
                            Cán bộ: <b><?= htmlspecialchars($user['HoVaTen'] ?? $user['TenDangNhap'] ?? 'Cán bộ KTX') ?></b>
                        </span>
                    </p>
                </div>

                <!-- CỤM NÚT THAO TÁC HEADER (CĂN BÊN PHẢI - RIGHT ALIGNED) -->
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap; justify-content: flex-end; margin-left: auto;">
                    <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; white-space: nowrap; font-weight: 600;">
                        <i class="fa-solid fa-rotate" style="margin-right: 4px;"></i> Làm mới dữ liệu
                    </a>

                    <?php if ($viewType === 'manager'): ?>
                        <a href="<?= url('cron/trigger_release') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; color: #b45309; border-color: #fde68a; background: #fffbeb; white-space: nowrap; font-weight: 600;" title="Kích hoạt quét đối soát và tự động thu hồi giữ chỗ quá hạn 72h">
                            <i class="fa-solid fa-clock" style="color: #d97706; margin-right: 4px;"></i> Quét đối soát 72h
                        </a>

                        <a href="<?= url('cron/trigger_absence') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 6px 12px; color: #2563eb; border-color: #bfdbfe; background: #eff6ff; white-space: nowrap; font-weight: 600;" title="Kích hoạt quét rà soát sinh viên vắng mặt trên 30 ngày">
                            <i class="fa-solid fa-clipboard-check" style="color: #2563eb; margin-right: 4px;"></i> Quét vắng 30 ngày
                        </a>
                    <?php endif; ?>
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
                <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px; color: #dc2626;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
            </div>
        <?php endif; ?>

        <!-- ========================================================================= -->
        <!-- PHÂN NHÁNH 1: GIAO DIỆN KẾ TOÁN & TÀI CHÍNH (ACCOUNTING DASHBOARD)       -->
        <!-- ========================================================================= -->
        <?php if ($viewType === 'accounting'): ?>
            <?php
            $accStats = $accountingData['stats'] ?? [];
            $topDebtRooms = $accountingData['topDebtRooms'] ?? [];
            $pendingRefunds = $accountingData['pendingRefunds'] ?? [];
            ?>

            <!-- 4 KPI Cards Kế toán -->
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
                <div class="card stat-card" style="border-left: 4px solid #16a34a; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Doanh thu đã thu</div>
                    <div class="stat-val" style="color: #15803d; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= number_format($accStats['DaThu'] ?? 0, 0, ',', '.') ?> <span style="font-size: 0.85rem; font-weight: 500;">đ</span>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Hóa đơn đã gạch nợ thành công</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #dc2626; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Tổng công nợ tồn đọng</div>
                    <div class="stat-val" style="color: #b91c1c; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= number_format($accStats['ConNo'] ?? 0, 0, ',', '.') ?> <span style="font-size: 0.85rem; font-weight: 500;">đ</span>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);"><?= (int)($accStats['SoHoaDonChuaThu'] ?? 0) ?> hóa đơn chưa thanh toán</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #d97706; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Số phòng nợ cước</div>
                    <div class="stat-val" style="color: #b45309; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= (int)($accStats['SoPhongNo'] ?? 0) ?> <span style="font-size: 0.85rem; font-weight: 500;">phòng</span>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Cần gửi thông báo nhắc cước</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #2563eb; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Đơn hoàn phí chờ chi</div>
                    <div class="stat-val" style="color: #1d4ed8; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= count($pendingRefunds) ?> <span style="font-size: 0.85rem; font-weight: 500;">đơn</span>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Chờ Kế toán duyệt giải ngân</div>
                </div>
            </div>

            <!-- Bảng 1: Top phòng nợ cước điện nước -->
            <div class="card" style="margin-bottom: 24px; border-radius: 10px;">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    <span style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-file-invoice-dollar" style="color: #dc2626;"></i> Danh sách phòng nợ cước điện nước cao nhất
                    </span>
                    <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                        Vào phân hệ Điện Nước &rarr;
                    </a>
                </div>

                <div class="table-responsive" style="margin-top: 14px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>Tòa nhà</th>
                                <th>Cụm KTX</th>
                                <th style="text-align: center;">Số kỳ nợ</th>
                                <th style="text-align: right;">Tổng tiền nợ</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($topDebtRooms)): ?>
                                <?php foreach ($topDebtRooms as $r): ?>
                                    <tr>
                                        <td><b>Phòng <?= htmlspecialchars($r['SoPhong'] ?? $r['MaPhong']) ?></b></td>
                                        <td><?= htmlspecialchars($r['TenToaNha'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($r['TenCum'] ?? '') ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge badge-danger"><?= $r['SoKyNo'] ?> kỳ</span>
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #b91c1c;">
                                            <?= number_format($r['TongTienNo'], 0, ',', '.') ?> đ
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= url('staff/utilities?tab=invoices') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                Đối soát
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        Tuyệt vời! Không có phòng nào đang tồn đọng công nợ điện nước.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Bảng 2: Đơn Một Cửa xin hoàn phí -->
            <div class="card" style="border-radius: 10px;">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    <span style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-money-bill-transfer" style="color: #2563eb;"></i> Đơn xin hoàn phí / chênh lệch tiền cọc đang chờ xử lý
                    </span>
                    <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                        Vào phân hệ Một Cửa &rarr;
                    </a>
                </div>

                <div class="table-responsive" style="margin-top: 14px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Sinh viên</th>
                                <th>Loại yêu cầu</th>
                                <th style="text-align: right;">Số tiền hoàn</th>
                                <th>Lý do</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pendingRefunds)): ?>
                                <?php foreach ($pendingRefunds as $rf): ?>
                                    <tr>
                                        <td><b><?= htmlspecialchars($rf['MaDon']) ?></b></td>
                                        <td>
                                            <?= htmlspecialchars($rf['HoVaTen'] ?? '') ?><br>
                                            <small style="color: var(--text-muted);"><?= htmlspecialchars($rf['MaSinhVien']) ?> &bull; <?= htmlspecialchars($rf['SoDienThoai'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($rf['LoaiDon']) ?></td>
                                        <td style="text-align: right; font-weight: 700; color: #15803d;">
                                            <?= number_format($rf['SoTienChenhLechHoan'], 0, ',', '.') ?> đ
                                        </td>
                                        <td style="max-width: 250px; font-size: 0.85rem; color: var(--text-secondary);">
                                            <?= htmlspecialchars(mb_substr($rf['LyDo'] ?? '', 0, 70)) ?>...
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= url('staff/requests') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                Xử lý giải ngân
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        Không có đơn xin hoàn phí nào đang chờ xử lý.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- PHÂN NHÁNH 2: GIAO DIỆN KỸ THUẬT VIÊN SỬA CHỮA (TECHNICAL DASHBOARD)     -->
            <!-- ========================================================================= -->
        <?php elseif ($viewType === 'technical'): ?>
            <?php
            $tStats = $technicalData['stats'] ?? [];
            $myTickets = $technicalData['myTickets'] ?? [];
            ?>

            <!-- 4 KPI Cards Kỹ thuật -->
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 24px;">
                <div class="card stat-card" style="border-left: 4px solid #2563eb; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Tổng phiếu được giao</div>
                    <div class="stat-val" style="color: #1d4ed8; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= (int)($tStats['TongPhieuDuocGiao'] ?? 0) ?>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Tổng sự cố cá nhân phụ trách</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #d97706; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Đang tiến hành sửa chữa</div>
                    <div class="stat-val" style="color: #b45309; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= (int)($tStats['DangSua'] ?? 0) ?>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Phiếu đang mở tại hiện trường</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #dc2626; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Chờ tiếp nhận xử lý</div>
                    <div class="stat-val" style="color: #b91c1c; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= (int)($tStats['ChoTiepNhan'] ?? 0) ?>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Cần xếp lịch đến kiểm tra</div>
                </div>

                <div class="card stat-card" style="border-left: 4px solid #16a34a; padding: 16px;">
                    <div class="stat-label" style="font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Đã hoàn thành</div>
                    <div class="stat-val" style="color: #15803d; font-size: 1.5rem; font-weight: 700; margin: 4px 0;">
                        <?= (int)($tStats['DaHoanThanh'] ?? 0) ?>
                    </div>
                    <div class="stat-sub" style="font-size: 0.8rem; color: var(--text-secondary);">Sự cố đã khắc phục thành công</div>
                </div>
            </div>

            <!-- Danh sách phiếu của Kỹ thuật viên -->
            <div class="card" style="border-radius: 10px;">
                <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                    <span style="font-weight: 700; font-size: 1.05rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-wrench" style="color: #d97706;"></i> Danh sách phiếu sửa chữa được phân công
                    </span>
                    <a href="<?= url('staff/maintenance') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                        Vào phân hệ Bảo Trì chi tiết &rarr;
                    </a>
                </div>

                <div class="table-responsive" style="margin-top: 14px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Vị trí</th>
                                <th>Thiết bị</th>
                                <th>Mô tả hư hỏng</th>
                                <th>Người báo</th>
                                <th>Trạng thái</th>
                                <th style="text-align: right;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($myTickets)): ?>
                                <?php foreach ($myTickets as $tk): ?>
                                    <tr>
                                        <td><b><?= htmlspecialchars($tk['MaPhieu']) ?></b></td>
                                        <td>
                                            Phòng <b><?= htmlspecialchars($tk['MaPhong']) ?></b><br>
                                            <small style="color: var(--text-muted);"><?= htmlspecialchars($tk['TenToaNha'] ?? '') ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($tk['TenThietBi'] ?? 'Chung') ?></td>
                                        <td style="max-width: 260px; font-size: 0.85rem;">
                                            <?= htmlspecialchars($tk['MoTaHuHong']) ?>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($tk['NguoiBao'] ?? '') ?><br>
                                            <small style="color: var(--text-muted);"><?= htmlspecialchars($tk['SdtNguoiBao'] ?? '') ?></small>
                                        </td>
                                        <td>
                                            <span class="badge <?= ($tk['TrangThaiSua'] === 'Đang sửa chữa') ? 'badge-warning' : 'badge-danger' ?>">
                                                <?= htmlspecialchars($tk['TrangThaiSua']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                Cập nhật tiến độ
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 24px;">
                                        Hiện không có phiếu bảo trì nào đang chờ bạn xử lý.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- PHÂN NHÁNH 3: GIAO DIỆN CÁN BỘ QUẢN LÝ (MANAGER TIERS 1-4)                -->
            <!-- ========================================================================= -->
        <?php else: ?>

            <!-- BỘ LỌC PHẠM VI PHÂN CẤP (CASCADING SCOPE FILTER BAR) -->
            <?php if (!empty($scope['canFilterKhu']) || !empty($scope['canFilterCum']) || !empty($scope['canFilterToa'])): ?>
                <div class="card" style="padding: 14px 18px; margin-bottom: 20px; border-radius: 10px; background: #f8fafc; border: 1px solid #e2e8f0;">
                    <form method="GET" action="<?= url('staff/dashboard') ?>" style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap;">

                        <!-- Chọn Khu (Dành cho Admin) -->
                        <?php if (!empty($scope['canFilterKhu'])): ?>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <select name="khu" id="filterKhu" class="form-control" style="padding: 4px 8px; font-size: 0.825rem; border-radius: 6px; height: 32px;" onchange="updateFilterOptions(); this.form.submit();">
                                    <option value="all">-- Toàn KTX (Khu A & B) --</option>
                                    <?php foreach ($scope['availableKhus'] ?? [] as $k): ?>
                                        <option value="<?= htmlspecialchars($k['MaKhu']) ?>" <?= ($activeKhu === $k['MaKhu']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($k['TenKhu']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Chọn Cụm (Dành cho Admin & Trưởng Khu) -->
                        <?php if (!empty($scope['canFilterCum'])): ?>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <select name="cum" id="filterCum" class="form-control" style="padding: 4px 8px; font-size: 0.825rem; border-radius: 6px; height: 32px;" onchange="this.form.submit();">
                                    <option value="all">-- Tất cả Cụm --</option>
                                    <?php foreach ($scope['availableCums'] ?? [] as $c): ?>
                                        <?php if (empty($activeKhu) || $activeKhu === 'all' || ($c['MaKhu'] ?? '') === $activeKhu): ?>
                                            <option value="<?= htmlspecialchars($c['MaCum']) ?>" <?= ($activeCum === $c['MaCum']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['TenCum']) ?> (<?= htmlspecialchars($c['MaCum']) ?>)
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <!-- Chọn Tòa (Dành cho Admin, Trưởng Khu & Trưởng Cụm) -->
                        <?php if (!empty($scope['canFilterToa'])): ?>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <select name="toa" id="filterToa" class="form-control" style="padding: 4px 8px; font-size: 0.825rem; border-radius: 6px; height: 32px;" onchange="this.form.submit();">
                                    <option value="all">-- Tất cả Tòa --</option>
                                    <?php foreach ($scope['availableToas'] ?? [] as $t): ?>
                                        <?php
                                        $matchKhu = empty($activeKhu) || $activeKhu === 'all' || ($t['MaKhu'] ?? '') === $activeKhu;
                                        $matchCum = empty($activeCum) || $activeCum === 'all' || ($t['MaCum'] ?? '') === $activeCum;
                                        if ($matchKhu && $matchCum):
                                        ?>
                                            <option value="<?= htmlspecialchars($t['MaToaNha']) ?>" <?= ($activeToa === $t['MaToaNha']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['TenToaNha']) ?>
                                            </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($activeKhu) || !empty($activeCum) || !empty($activeToa)): ?>
                            <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 8px 14px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; justify-content: center; gap: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                                <i class="fa-solid fa-filter-circle-xmark"></i> Xóa bộ lọc
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            <?php endif; ?>


            <!-- TRUNG TÂM ĐIỀU HÀNH - OPERATIONAL ACTION CARDS -->
            <div class="kpi-action-grid" style="margin-bottom: 24px;">
                <!-- 1. HỒ SƠ GIỮ CHỖ -->
                <div class="card stat-card" style="border-left: 4px solid #f59e0b; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Hồ sơ giữ chỗ</span>

                    </div>
                    <div style="font-size: 1.8rem; font-weight: 750; color: #b45309; margin: 8px 0;">
                        <?= (int)($kpis['applications']['total'] ?? 0) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #dc2626; margin-bottom: 12px;">
                        <i class="fa-solid fa-clock"></i>
                        <?= (int)($kpis['applications']['urgent'] ?? 0) ?> hồ sơ quá 48 giờ
                    </div>
                    <a href="<?= url('staff/applications') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Xử lý hồ sơ <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- 2. PHÒNG CẦN DỒN GHÉP -->
                <div class="card stat-card" style="border-left: 4px solid #2563eb; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Phòng cần dồn ghép</span>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 750; color: #1e40af; margin: 8px 0;">
                        <?= (int)($kpis['rooms']['underOccupied'] ?? 0) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        <i class="fa-solid fa-chart-pie"></i>
                        Lấp đầy trung bình: <b><?= $kpis['rooms']['avgRate'] ?? 0 ?>%</b>
                    </div>
                    <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Điều phối phòng <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- 3. SỰ CỐ BẢO TRÌ -->
                <div class="card stat-card" style="border-left: 4px solid #dc2626; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Sự cố bảo trì</span>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 750; color: #b91c1c; margin: 8px 0;">
                        <?= (int)($kpis['maintenance']['total'] ?? 0) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: #dc2626; margin-bottom: 12px;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <?= (int)($kpis['maintenance']['urgent'] ?? 0) ?> sự cố khẩn cấp
                    </div>
                    <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Điều phối sửa chữa <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- 4. CÔNG NỢ ĐIỆN NƯỚC -->
                <div class="card stat-card" style="border-left: 4px solid #10b981; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Công nợ điện nước</span>
                    </div>
                    <div style="font-size: 1.65rem; font-weight: 750; color: #15803d; margin: 8px 0;">
                        <?= number_format($kpis['utilities']['totalDebt'] ?? 0, 0, ',', '.') ?> <span style="font-size: 0.85rem;">đ</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        <i class="fa-solid fa-house"></i>
                        <?= (int)($kpis['utilities']['debtRooms'] ?? 0) ?> phòng chưa thanh toán
                    </div>
                    <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Đối soát công nợ <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- 5. CẢNH BÁO VẮNG MẶT -->
                <div class="card stat-card" style="border-left: 4px solid #8b5cf6; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Cảnh báo vắng mặt</span>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 750; color: #6d28d9; margin: 8px 0;">
                        <?= (int)($kpis['absence']['warnings'] ?? 0) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        Vắng trên 30 ngày, chưa có đơn
                    </div>
                    <a href="<?= url('staff/discipline?tab=canh-bao') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Kiểm tra an ninh <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

                <!-- 6. YÊU CẦU MỘT CỬA -->
                <div class="card stat-card" style="border-left: 4px solid #0d9488; padding: 16px; border-radius: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="stat-label" style="font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">Yêu cầu Một Cửa</span>
                    </div>
                    <div style="font-size: 1.8rem; font-weight: 750; color: #0f766e; margin: 8px 0;">
                        <?= (int)($kpis['requests']['total'] ?? 0) ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 12px;">
                        <i class="fa-solid fa-list-check"></i>
                        Chuyển phòng / Tạm vắng / Hoàn phí
                    </div>
                    <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="width: 100%; display: flex; justify-content: center; align-items: center; gap: 6px; text-decoration: none;">
                        Giải quyết yêu cầu <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>

            </div>


            <!-- ===================================================================== -->
            <!-- TRUNG TÂM TÁC VỤ CẤP BÁCH CẦN XỬ LÝ NGAY (URGENT ACTION CENTER)        -->
            <!-- ===================================================================== -->
            <div class="card" style="margin-bottom: 24px; border-radius: 12px; border: 1px solid #fed7aa; background: #fffaf0;">
                <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid #ffedd5;">
                    <div style="display: flex; align-items: center; gap: 8px;">

                        <h3 style="font-size: 1.05rem; font-weight: 700; color: #9a3412; margin: 0;">
                            Tác vụ Cấp bách
                        </h3>
                    </div>
                </div>

                <!-- Sub-nav tabs tác vụ khẩn -->
                <div style="display: flex; gap: 8px; margin: 14px 0; border-bottom: 1px solid #fed7aa; padding-bottom: 8px;">
                    <button type="button" class="btn btn-sm urgent-tab-btn active" onclick="switchUrgentTab('app')" id="urgentTabApp" style="border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 12px; background: #ea580c; color: #ffffff; border: none;">
                        Hồ sơ sắp quá 72h (<?= count($urgentActions['applications'] ?? []) ?>)
                    </button>
                    <button type="button" class="btn btn-sm urgent-tab-btn" onclick="switchUrgentTab('maint')" id="urgentTabMaint" style="border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 12px; background: transparent; color: #9a3412; border: none;">
                        Sự cố khẩn cấp (<?= count($urgentActions['maintenance'] ?? []) ?>)
                    </button>
                    <button type="button" class="btn btn-sm urgent-tab-btn" onclick="switchUrgentTab('absence')" id="urgentTabAbsence" style="border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 12px; background: transparent; color: #9a3412; border: none;">
                        Vắng &gt; 30 ngày (<?= count($urgentActions['absence'] ?? []) ?>)
                    </button>
                    <button type="button" class="btn btn-sm urgent-tab-btn" onclick="switchUrgentTab('req')" id="urgentTabReq" style="border-radius: 6px; font-weight: 600; font-size: 0.8rem; padding: 4px 12px; background: transparent; color: #9a3412; border: none;">
                        Đơn Một Cửa (<?= count($urgentActions['requests'] ?? []) ?>)
                    </button>
                </div>

                <!-- 1. Danh sách hồ sơ 72h -->
                <div id="urgentContentApp" class="urgent-content">
                    <?php if (!empty($urgentActions['applications'])): ?>
                        <div class="table-responsive">
                            <table class="data-table" style="background: #ffffff; border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Mã hồ sơ</th>
                                        <th>Sinh viên</th>
                                        <th>Vị trí phòng</th>
                                        <th>Đã nộp được</th>
                                        <th>Thời hạn 72h</th>
                                        <th style="text-align: right;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urgentActions['applications'] as $uApp): ?>
                                        <tr>
                                            <td><b><?= htmlspecialchars($uApp['MaHoSo']) ?></b></td>
                                            <td>
                                                <?= htmlspecialchars($uApp['HoVaTen']) ?> (<?= htmlspecialchars($uApp['MaSinhVien']) ?>)<br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($uApp['SoDienThoai']) ?></small>
                                            </td>
                                            <td>
                                                Phòng <b><?= htmlspecialchars($uApp['SoPhong'] ?? $uApp['MaPhong']) ?></b> - <?= htmlspecialchars($uApp['TenToaNha']) ?><br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($uApp['TenCum']) ?></small>
                                            </td>
                                            <td><b><?= $uApp['GioDaTroiQua'] ?></b> giờ</td>
                                            <td>
                                                <?php $hoursLeft = 72 - (float)$uApp['GioDaTroiQua']; ?>
                                                <?php if ($hoursLeft <= 24): ?>
                                                    <span class="badge badge-danger">Còn <?= max(0, round($hoursLeft, 1)) ?>h</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Còn <?= round($hoursLeft, 1) ?>h</span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('staff/applications') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                    Duyệt hồ sơ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #9a3412; padding: 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 6px;"></i> Không có hồ sơ nào đang cận kề hạn 72h.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 2. Danh sách sự cố khẩn cấp -->
                <div id="urgentContentMaint" class="urgent-content" style="display: none;">
                    <?php if (!empty($urgentActions['maintenance'])): ?>
                        <div class="table-responsive">
                            <table class="data-table" style="background: #ffffff; border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Vị trí</th>
                                        <th>Mô tả sự cố</th>
                                        <th>Người báo</th>
                                        <th>Thời gian báo</th>
                                        <th style="text-align: right;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urgentActions['maintenance'] as $uM): ?>
                                        <tr>
                                            <td><b><?= htmlspecialchars($uM['MaPhieu']) ?></b></td>
                                            <td>
                                                Phòng <b><?= htmlspecialchars($uM['MaPhong']) ?></b> (<?= htmlspecialchars($uM['TenToaNha'] ?? '') ?>)
                                            </td>
                                            <td style="color: #b91c1c; font-weight: 500;">
                                                <?= htmlspecialchars($uM['MoTaHuHong']) ?>
                                            </td>
                                            <td>
                                                <?= htmlspecialchars($uM['NguoiBao'] ?? '') ?><br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($uM['SdtNguoiBao'] ?? '') ?></small>
                                            </td>
                                            <td><?= $uM['NgayBao'] ?></td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('staff/maintenance') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                    Điều phối thợ
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #9a3412; padding: 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 6px;"></i> Không có sự cố bảo trì khẩn cấp nào tồn đọng.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 3. Danh sách vắng > 30 ngày -->
                <div id="urgentContentAbsence" class="urgent-content" style="display: none;">
                    <?php if (!empty($urgentActions['absence'])): ?>
                        <div class="table-responsive">
                            <table class="data-table" style="background: #ffffff; border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Sinh viên</th>
                                        <th>Phòng / Tòa</th>
                                        <th>SĐT Sinh viên</th>
                                        <th>SĐT Phụ huynh</th>
                                        <th>Số ngày vắng</th>
                                        <th style="text-align: right;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urgentActions['absence'] as $uAb): ?>
                                        <tr>
                                            <td>
                                                <b><?= htmlspecialchars($uAb['hoVaTen'] ?? '') ?></b><br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($uAb['maSinhVien'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                Phòng <b><?= htmlspecialchars($uAb['maPhong'] ?? '') ?></b> (<?= htmlspecialchars($uAb['maToaNha'] ?? '') ?>)
                                            </td>
                                            <td><?= htmlspecialchars($uAb['soDienThoai'] ?? '') ?></td>
                                            <td><b style="color: #b91c1c;"><?= htmlspecialchars($uAb['sdtPhuHuynh'] ?? '') ?></b></td>
                                            <td>
                                                <span class="badge badge-danger"><?= $uAb['soNgayVang'] ?? 30 ?> ngày</span>
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('staff/discipline?tab=canh-bao') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                    Xác minh
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #9a3412; padding: 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 6px;"></i> Không phát hiện sinh viên nào vắng mặt trên 30 ngày.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- 4. Danh sách đơn Một Cửa -->
                <div id="urgentContentReq" class="urgent-content" style="display: none;">
                    <?php if (!empty($urgentActions['requests'])): ?>
                        <div class="table-responsive">
                            <table class="data-table" style="background: #ffffff; border-radius: 8px;">
                                <thead>
                                    <tr>
                                        <th>Mã đơn</th>
                                        <th>Sinh viên</th>
                                        <th>Loại đơn</th>
                                        <th>Lý do đề nghị</th>
                                        <th style="text-align: right;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($urgentActions['requests'] as $uR): ?>
                                        <tr>
                                            <td><b><?= htmlspecialchars($uR['MaDon']) ?></b></td>
                                            <td>
                                                <?= htmlspecialchars($uR['HoVaTen'] ?? '') ?><br>
                                                <small style="color: var(--text-muted);"><?= htmlspecialchars($uR['MaSinhVien']) ?> &bull; <?= htmlspecialchars($uR['SoDienThoai'] ?? '') ?></small>
                                            </td>
                                            <td><span class="badge badge-info"><?= htmlspecialchars($uR['LoaiDon']) ?></span></td>
                                            <td style="max-width: 250px; font-size: 0.85rem;">
                                                <?= htmlspecialchars(mb_substr($uR['LyDo'] ?? '', 0, 80)) ?>...
                                            </td>
                                            <td style="text-align: right;">
                                                <a href="<?= url('staff/requests') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px; padding: 4px 10px; font-size: 0.78rem;">
                                                    Xử lý đơn
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; color: #9a3412; padding: 18px; font-size: 0.9rem;">
                            <i class="fa-solid fa-circle-check" style="color: #16a34a; margin-right: 6px;"></i> Tất cả đơn từ dịch vụ sinh viên đều đã được xử lý.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ===================================================================== -->
            <!-- KHỐI 2 CỘT: CÔNG SUẤT LƯU TRÚ (TRÁI) & NHẬT KÝ THỜI GIAN THỰC (PHẢI) -->
            <!-- ===================================================================== -->
            <div style="display: grid; grid-template-columns: 1.4fr 1fr; gap: 20px; margin-bottom: 24px;">

                <!-- CỘT TRÁI: CÔNG SUẤT VÀ ĐỀ XUẤT DỒN PHÒNG <= 50% -->
                <div class="card" style="border-radius: 10px; padding: 20px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border);">
                        <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                            Công suất lưu trú
                        </h3>
                        <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.78rem;">
                            Sơ đồ phòng &rarr;
                        </a>
                    </div>

                    <!-- Danh sách phòng <= 50% -->
                    <div style="margin-top: 14px;">
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 8px;">
                            Phòng công suất thấp cần ghép:
                        </div>
                        <?php if (!empty($underOccupiedRooms)): ?>
                            <div class="table-responsive">
                                <table class="data-table" style="font-size: 0.85rem;">
                                    <thead>
                                        <tr>
                                            <th>Phòng</th>
                                            <th>Tòa / Cụm</th>
                                            <th>Đang ở / Tối đa</th>
                                            <th>Lấp đầy</th>
                                            <th>Đề xuất</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($underOccupiedRooms, 0, 5) as $uR): ?>
                                            <tr>
                                                <td><b><?= htmlspecialchars($uR['SoPhong'] ?? $uR['MaPhong']) ?></b></td>
                                                <td><?= htmlspecialchars($uR['TenToaNha']) ?> (<?= htmlspecialchars($uR['MaCum']) ?>)</td>
                                                <td><?= $uR['SoSVDangO'] ?> / <?= $uR['SoGiuongToiDa'] ?> SV</td>
                                                <td>
                                                    <span class="badge <?= ($uR['TyLeLapDay'] == 0) ? 'badge-danger' : 'badge-warning' ?>">
                                                        <?= $uR['TyLeLapDay'] ?>%
                                                    </span>
                                                </td>
                                                <td style="font-size: 0.8rem; color: var(--primary); font-weight: 500;">
                                                    <?= ($uR['SoSVDangO'] == 0) ? 'Phòng trống, sẵn sàng nhận SV' : "Ghép {$uR['SoSVDangO']} SV sang phòng cùng loại" ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div style="color: var(--text-muted); font-size: 0.85rem; padding: 12px; background: #f8fafc; border-radius: 6px; text-align: center;">
                                Không có phòng nào dưới 50% công suất trong phạm vi quản lý.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Thanh tiến độ công suất từng tòa -->
                    <div style="margin-top: 20px;">
                        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 10px;">
                            Tỷ lệ lấp đầy các Tòa nhà:
                        </div>
                        <?php if (!empty($occupancyStats)): ?>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <?php foreach (array_slice($occupancyStats, 0, 6) as $occ): ?>
                                    <?php
                                    $rate = (float)($occ['TyLeLapDay'] ?? 0);
                                    $barColor = ($rate >= 80) ? '#16a34a' : (($rate >= 50) ? '#2563eb' : '#dc2626');
                                    ?>
                                    <div>
                                        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 4px;">
                                            <span><b><?= htmlspecialchars($occ['TenToaNha']) ?></b> (Cụm <?= htmlspecialchars($occ['MaCum']) ?>)</span>
                                            <span><?= $occ['TongSVDangO'] ?> / <?= $occ['TongSucChua'] ?> SV (<b><?= $rate ?>%</b>)</span>
                                        </div>
                                        <div style="height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden;">
                                            <div style="height: 100%; width: <?= min(100, $rate) ?>%; background: <?= $barColor ?>; border-radius: 4px;"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- CỘT PHẢI: NHẬT KÝ THỜI GIAN THỰC & LỐI TẮT NHANH -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Nhật ký sự kiện thời gian thực -->
                    <div class="card" style="border-radius: 10px; padding: 18px; flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                            <h3 style="font-size: 1.05rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-clock-rotate-left" style="color: var(--primary); font-size: 1rem;"></i>
                                Hoạt động gần đây
                            </h3>
                            <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">
                                Nhấp mục để xem chi tiết
                            </span>
                        </div>

                        <div style="margin-top: 14px; display: flex; flex-direction: column; gap: 10px;">
                            <?php if (!empty($recentActivities)): ?>
                                <?php foreach ($recentActivities as $act): ?>
                                    <a href="<?= htmlspecialchars($act['url'] ?? '#') ?>"
                                        class="recent-activity-link"
                                        title="Nhấn để xem chi tiết trên phân hệ tương ứng">
                                        <span class="activity-icon-wrap" style="background: <?= $act['bg'] ?? '#f1f5f9' ?>; color: <?= $act['color'] ?>;">
                                            <i class="fa-solid <?= $act['icon'] ?>"></i>
                                        </span>
                                        <div class="activity-content">
                                            <div class="activity-header">
                                                <div class="activity-title">
                                                    <?= htmlspecialchars($act['title']) ?>
                                                </div>
                                                <span class="activity-time">
                                                    <?= htmlspecialchars($act['time_formatted'] ?? $act['time']) ?>
                                                </span>
                                            </div>
                                            <div class="activity-desc">
                                                <?= htmlspecialchars($act['desc']) ?>
                                            </div>
                                            <div class="activity-footer">
                                                <span class="badge" style="background: <?= $act['bg'] ?? '#f1f5f9' ?>; color: <?= $act['color'] ?>; border: none; font-size: 0.68rem; padding: 2px 7px; border-radius: 4px; font-weight: 600;">
                                                    <?= htmlspecialchars($act['category'] ?? 'Hoạt động') ?>
                                                </span>
                                                <span class="activity-cta">
                                                    Xem chi tiết <i class="fa-solid fa-arrow-right"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="color: var(--text-muted); font-size: 0.85rem; text-align: center; padding: 24px;">
                                    <i class="fa-regular fa-folder-open" style="font-size: 1.8rem; color: #cbd5e1; margin-bottom: 8px; display: block;"></i>
                                    Chưa có nhật ký hoạt động nào gần đây.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    <?php endif; ?>

</div>

<script>
    // Chuyển đổi tab Tác vụ cấp bách
    function switchUrgentTab(tabKey) {
        document.querySelectorAll('.urgent-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.urgent-tab-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.background = 'transparent';
            btn.style.color = '#9a3412';
        });

        const activeEl = document.getElementById('urgentContent' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1));
        const activeBtn = document.getElementById('urgentTab' + tabKey.charAt(0).toUpperCase() + tabKey.slice(1));
        if (activeEl) activeEl.style.display = 'block';
        if (activeBtn) {
            activeBtn.classList.add('active');
            activeBtn.style.background = '#ea580c';
            activeBtn.style.color = '#ffffff';
        }
    }
</script>

<?php
$content = ob_get_clean();
$title = "Trung tâm Điều hành & Bảng điều khiển BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>