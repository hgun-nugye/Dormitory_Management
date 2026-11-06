<?php
ob_start();
$requests = $requests ?? [];
$repairs = $repairs ?? [];
$contract = $contract ?? null;

$totalCount = count($requests) + count($repairs);

$services = [
    [
        'name' => 'Xin chuyển phòng',
        'desc' => 'Đổi sang phòng hoặc giường khác phù hợp hơn, tự động tính bù trừ lệ phí chênh lệch.',
        'route' => 'student/requests/transfer',
        'icon' => 'fa-solid fa-right-left',
        'color' => '#2563eb',
        'bg' => '#eff6ff'
    ],
    [
        'name' => 'Báo hỏng thiết bị phòng',
        'desc' => 'Phản ánh hư hỏng thiết bị điện, nước, giường, khóa cửa để bộ phận kỹ thuật sửa chữa.',
        'route' => 'student/requests/repair',
        'icon' => 'fa-solid fa-screwdriver-wrench',
        'color' => '#d97706',
        'bg' => '#fef3c7'
    ],
    [
        'name' => 'Khai báo tạm vắng',
        'desc' => 'Khai báo thời gian không ở KTX nhằm đảm bảo an ninh và tránh cảnh báo vắng 30 ngày.',
        'route' => 'student/requests/leave',
        'icon' => 'fa-solid fa-person-walking-arrow-right',
        'color' => '#0284c7',
        'bg' => '#e0f2fe'
    ],
    [
        'name' => 'Xin trả phòng sớm',
        'desc' => 'Thanh lý hợp đồng nội trú trước hạn, kiểm kê tài sản phòng và hoàn tất thủ tục rời KTX.',
        'route' => 'student/requests/return',
        'icon' => 'fa-solid fa-door-open',
        'color' => '#dc2626',
        'bg' => '#fee2e2'
    ],
    [
        'name' => 'Hồ sơ miễn giảm lệ phí',
        'desc' => 'Nộp hồ sơ và minh chứng để xét duyệt miễn hoặc giảm tiền phòng theo chính sách ưu tiên.',
        'route' => 'student/requests/extension',
        'icon' => 'fa-solid fa-file-circle-check',
        'color' => '#16a34a',
        'bg' => '#dcfce7'
    ],
];
?>

<div class="container">
    <!-- Breadcrumb -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;">Trang chủ</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;">Dịch vụ một cửa</span>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">Cổng dịch vụ một cửa sinh viên</h1>
        </div>
        <div class="header-actions">
            <a href="#history-section" class="btn btn-primary btn-sm" style="border-radius: 8px; color: #fff;">
                Xem lịch sử gửi đơn (<?= $totalCount ?>)
            </a>
            <a href="<?= url('student/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 8px;">
                Quay lại Cổng sinh viên
            </a>
        </div>
    </div>

    <!-- Process Workflow Banner -->
    <div class="card" style="margin-bottom: 24px; background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 1px solid var(--border);">
        <div style="display: flex; align-items: center; justify-content: space-around; flex-wrap: wrap; gap: 16px; text-align: center;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #eff6ff; color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700;">1</div>
                <div style="text-align: left;">
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">Nộp đơn trực tuyến</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Điền biểu mẫu và gửi minh chứng</div>
                </div>
            </div>
            <span style="color: #cbd5e1; font-weight: bold;">&rarr;</span>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-weight: 700;">2</div>
                <div style="text-align: left;">
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">Cán bộ KTX xét duyệt</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Trưởng tòa / Trưởng cụm thẩm tra</div>
                </div>
            </div>
            <span style="color: #cbd5e1; font-weight: bold;">&rarr;</span>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-weight: 700;">3</div>
                <div style="text-align: left;">
                    <div style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">Nhận kết quả & Theo dõi</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Xem trạng thái chi tiết tại bảng Lịch sử</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5 Service Selection Cards -->
    <div style="margin-bottom: 32px;">
        <h3 style="font-size: 1.05rem; font-weight: 600; color: var(--text-primary); margin-bottom: 14px;">
            Tạo yêu cầu mới
        </h3>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px;">
            <?php foreach ($services as $srv): ?>
                <a href="<?= url($srv['route']) ?>" class="card" style="text-decoration: none; color: inherit; display: flex; flex-direction: column; justify-content: space-between; border-radius: 10px; transition: transform 0.15s ease, box-shadow 0.15s ease; border: 1px solid var(--border);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.06)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                            <div style="width: 44px; height: 44px; border-radius: 10px; background: <?= $srv['bg'] ?>; color: <?= $srv['color'] ?>; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="<?= $srv['icon'] ?>"></i>
                            </div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); font-weight: 500;">Dịch vụ</span>
                        </div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">
                            <?= htmlspecialchars($srv['name']) ?>
                        </h4>
                        <p style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                            <?= htmlspecialchars($srv['desc']) ?>
                        </p>
                    </div>
                    <div style="margin-top: 16px; padding-top: 10px; border-top: 1px solid var(--border-subtle); display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; font-weight: 600; color: <?= $srv['color'] ?>;">
                        <span>Mở biểu mẫu &rarr;</span>
                        <span>&rarr;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- PHẦN: LỊCH SỬ GỬI ĐƠN & PHIẾU BÁO HỎNG -->
    <!-- ========================================== -->
    <div id="history-section" style="scroll-margin-top: 80px; margin-bottom: 36px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid var(--border);">
            <div>
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin: 0;">
                    Lịch sử đơn từ & Yêu cầu đã gửi
                </h2>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin: 4px 0 0 0;">
                    Theo dõi tiến độ, tình trạng xử lý và phản hồi từ Ban quản lý KTX đối với tất cả đơn của bạn
                </p>
            </div>
            <!-- Tabs Filter -->
            <div style="display: flex; gap: 8px;">
                <button type="button" id="tab-all-btn" onclick="showTab('all')" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                    Tất cả (<?= $totalCount ?>)
                </button>
                <button type="button" id="tab-requests-btn" onclick="showTab('requests')" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                    Đơn hành chính (<?= count($requests) ?>)
                </button>
                <button type="button" id="tab-repairs-btn" onclick="showTab('repairs')" class="btn btn-outline btn-sm" style="border-radius: 6px;">
                    Báo hỏng kỹ thuật (<?= count($repairs) ?>)
                </button>
            </div>
        </div>

        <!-- 1. BẢNG ĐƠN MỘT CỬA HÀNH CHÍNH -->
        <div id="tab-requests-content" class="card" style="margin-bottom: 24px;">
            <div class="card-title">
                <span>Danh sách Đơn Một cửa hành chính</span>
                <span class="badge badge-info"><?= count($requests) ?> đơn</span>
            </div>

            <?php if (empty($requests)): ?>
                <div style="text-align: center; padding: 32px 0;">
                    <p class="text-muted" style="font-size: 0.875rem;">Bạn chưa nộp đơn hành chính nào vào hệ thống Một cửa.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã đơn</th>
                                <th>Loại dịch vụ</th>
                                <th>Thông tin chi tiết</th>
                                <th>Nội dung / Lý do</th>
                                <th>Trạng thái xét duyệt</th>
                                <th style="text-align: right;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody id="requestTableBody">
                            <?php foreach ($requests as $idx => $item): ?>
                                <tr class="request-row">
                                    <td>
                                        <code style="font-weight: 700; color: var(--primary);"><?= htmlspecialchars($item['MaDon']) ?></code>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($item['LoaiDon']) ?></div>
                                        <?php if (!empty($item['MaHopDong'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">HĐ: <?= htmlspecialchars($item['MaHopDong']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($item['GiuongMoi'])): ?>
                                            <div style="font-size: 0.8rem; color: #1e40af;">
                                                Chuyển đến: P.<?= htmlspecialchars($item['PhongMoi'] ?? '') ?> - Giường <?= htmlspecialchars($item['GiuongMoi']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['TuNgay'])): ?>
                                            <div style="font-size: 0.8rem; color: var(--text-secondary);">
                                                <?= date('d/m/Y', strtotime($item['TuNgay'])) ?>
                                                <?= !empty($item['DenNgay']) ? ' &rarr; ' . date('d/m/Y', strtotime($item['DenNgay'])) : '' ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($item['TepMinhChung'])): ?>
                                            <div style="margin-top: 4px;">
                                                <a href="<?= url('student/requests/attachment') ?>?kind=proof&amp;id=<?= rawurlencode($item['MaDon']) ?>" target="_blank" rel="noopener" style="font-size: 0.75rem; color: #0284c7; text-decoration: none;">
                                                    Tệp minh chứng
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($item['LyDo']) ?>">
                                            <?= htmlspecialchars($item['LyDo']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status = $item['TrangThaiDuyet'] ?? 'Chờ duyệt';
                                        $badgeClass = 'badge-warning';
                                        if (str_contains($status, 'Duyệt') || $status === 'Chấp thuận' || $status === 'Hoàn thành') {
                                            $badgeClass = 'badge-success';
                                        } elseif (str_contains($status, 'Từ chối') || str_contains($status, 'Hủy')) {
                                            $badgeClass = 'badge-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeClass ?>">
                                            <?= htmlspecialchars($status) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.75rem;" onclick="viewRequestDetails(<?= htmlspecialchars(json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)">
                                            Xem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thanh phân trang Đơn hành chính (5 mục / trang) -->
                <div id="requestPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 10px;">
                    <span id="requestPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;"></span>
                    <div id="requestPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. BẢNG PHIẾU BÁO HỎNG KỸ THUẬT -->
        <div id="tab-repairs-content" class="card">
            <div class="card-title">
                <span>Danh sách Phiếu Báo hỏng thiết bị</span>
                <span class="badge badge-warning"><?= count($repairs) ?> phiếu</span>
            </div>

            <?php if (empty($repairs)): ?>
                <div style="text-align: center; padding: 32px 0;">
                    <p class="text-muted" style="font-size: 0.875rem;">Không có phiếu sự cố kỹ thuật nào được tạo. Phòng ở đang vận hành tốt.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Thiết bị hư hỏng</th>
                                <th>Ngày gửi</th>
                                <th>Mô tả hiện tượng</th>
                                <th>Trạng thái xử lý</th>
                                <th style="text-align: right;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody id="repairTableBody">
                            <?php foreach ($repairs as $pb): ?>
                                <tr class="repair-row">
                                    <td>
                                        <code style="font-weight: 700; color: #d97706;"><?= htmlspecialchars($pb['MaPhieu']) ?></code>
                                    </td>
                                    <td>
                                        <b><?= htmlspecialchars($pb['TenThietBi'] ?? 'Chung trong phòng') ?></b>
                                        <?php if (!empty($pb['MaSoThietBi'])): ?>
                                            <div style="font-size: 0.75rem; color: var(--text-muted);">Mã TB: <?= htmlspecialchars($pb['MaSoThietBi']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= !empty($pb['NgayBao']) ? date('d/m/Y H:i', strtotime($pb['NgayBao'])) : '-' ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= htmlspecialchars($pb['MoTaHuHong']) ?>">
                                            <?= htmlspecialchars($pb['MoTaHuHong']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $statusSua = $pb['TrangThaiSua'] ?? 'Chờ xử lý';
                                        $badgeSua = 'badge-warning';
                                        if ($statusSua === 'Hoàn thành') {
                                            $badgeSua = 'badge-success';
                                        } elseif ($statusSua === 'Đang xử lý') {
                                            $badgeSua = 'badge-info';
                                        } elseif ($statusSua === 'Từ chối') {
                                            $badgeSua = 'badge-danger';
                                        }
                                        ?>
                                        <span class="badge <?= $badgeSua ?>">
                                            <?= htmlspecialchars($statusSua) ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; font-size: 0.75rem;" onclick="viewRepairDetails(<?= htmlspecialchars(json_encode($pb, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP), ENT_QUOTES, 'UTF-8') ?>)">
                                            Xem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Thanh phân trang Báo hỏng kỹ thuật (5 mục / trang) -->
                <div id="repairPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 10px;">
                    <span id="repairPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;"></span>
                    <div id="repairPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;"></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL XEM CHI TIẾT ĐƠN / PHIẾU BÁO HỎNG -->
<!-- ========================================== -->
<div id="detailModal" style="display: none; position: fixed; inset: 0; z-index: 1000; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(2px); align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 600px; width: 100%; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden;">
        <div style="padding: 16px 20px; background: #f8fafc; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 700; font-size: 1.05rem; color: var(--text-primary);">
                <span id="modalTitle">Chi tiết yêu cầu</span>
            </div>
            <button type="button" onclick="closeDetailModal()" style="border: none; background: none; font-size: 20px; color: var(--text-muted); cursor: pointer; padding: 4px;">
                &times;
            </button>
        </div>

        <div style="padding: 20px; max-height: 70vh; overflow-y: auto;" id="modalBody">
            <!-- Nội dung chi tiết được inject qua JavaScript -->
        </div>

        <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid var(--border); display: flex; justify-content: flex-end;">
            <button type="button" class="btn btn-outline btn-sm" onclick="closeDetailModal()" style="border-radius: 6px; padding: 6px 16px;">
                Đóng
            </button>
        </div>
    </div>
</div>

<script>
    const REQUESTS_PER_PAGE = 5;
    let requestCurrentPage = 1;

    const REPAIRS_PER_PAGE = 5;
    let repairCurrentPage = 1;

    function renderRequestPagination() {
        const rows = Array.from(document.querySelectorAll('#requestTableBody tr.request-row'));
        const total = rows.length;
        const infoEl = document.getElementById('requestPaginationInfo');
        const controlsEl = document.getElementById('requestPaginationControls');
        const barEl = document.getElementById('requestPaginationBar');

        // Nếu số lượng đơn từ <= 5, hiển thị trọn vẹn và ẩn thanh nút phân trang
        if (total <= REQUESTS_PER_PAGE) {
            rows.forEach(row => {
                row.style.display = '';
            });
            if (barEl) barEl.style.display = 'none';
            return;
        }
        if (barEl) barEl.style.display = 'flex';

        const totalPages = Math.ceil(total / REQUESTS_PER_PAGE);
        if (requestCurrentPage > totalPages) requestCurrentPage = totalPages;
        if (requestCurrentPage < 1) requestCurrentPage = 1;

        const startIdx = (requestCurrentPage - 1) * REQUESTS_PER_PAGE;
        const endIdx = startIdx + REQUESTS_PER_PAGE;

        rows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        if (infoEl) {
            const displayStart = startIdx + 1;
            const displayEnd = Math.min(endIdx, total);
            infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> đơn (Trang <b>${requestCurrentPage}</b> / <b>${totalPages}</b>)`;
        }

        if (!controlsEl) return;
        if (totalPages <= 1) {
            controlsEl.innerHTML = '';
            return;
        }

        let html = '';
        const disabledPrev = requestCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRequestPage(${requestCurrentPage - 1})" ${disabledPrev}>&larr; Trước</button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === requestCurrentPage) {
                html += `<button type="button" class="btn btn-primary btn-sm" style="padding: 5px 10px; border-radius: 6px; font-weight: 700;">${p}</button>`;
            } else {
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRequestPage(${p})" style="padding: 5px 10px; border-radius: 6px;">${p}</button>`;
            }
        }

        const disabledNext = requestCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRequestPage(${requestCurrentPage + 1})" ${disabledNext}>Sau &rarr;</button>`;

        controlsEl.innerHTML = html;
    }

    function goToRequestPage(page) {
        requestCurrentPage = page;
        renderRequestPagination();
    }

    function renderRepairPagination() {
        const rows = Array.from(document.querySelectorAll('#repairTableBody tr.repair-row'));
        const total = rows.length;
        const infoEl = document.getElementById('repairPaginationInfo');
        const controlsEl = document.getElementById('repairPaginationControls');
        const barEl = document.getElementById('repairPaginationBar');

        // Nếu số lượng phiếu sự cố <= 5, hiển thị trọn vẹn và ẩn thanh nút phân trang
        if (total <= REPAIRS_PER_PAGE) {
            rows.forEach(row => {
                row.style.display = '';
            });
            if (barEl) barEl.style.display = 'none';
            return;
        }
        if (barEl) barEl.style.display = 'flex';

        const totalPages = Math.ceil(total / REPAIRS_PER_PAGE);
        if (repairCurrentPage > totalPages) repairCurrentPage = totalPages;
        if (repairCurrentPage < 1) repairCurrentPage = 1;

        const startIdx = (repairCurrentPage - 1) * REPAIRS_PER_PAGE;
        const endIdx = startIdx + REPAIRS_PER_PAGE;

        rows.forEach((row, idx) => {
            row.style.display = (idx >= startIdx && idx < endIdx) ? '' : 'none';
        });

        if (infoEl) {
            const displayStart = startIdx + 1;
            const displayEnd = Math.min(endIdx, total);
            infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> phiếu (Trang <b>${repairCurrentPage}</b> / <b>${totalPages}</b>)`;
        }

        if (!controlsEl) return;
        if (totalPages <= 1) {
            controlsEl.innerHTML = '';
            return;
        }

        let html = '';
        const disabledPrev = repairCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRepairPage(${repairCurrentPage - 1})" ${disabledPrev}>&larr; Trước</button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === repairCurrentPage) {
                html += `<button type="button" class="btn btn-primary btn-sm" style="padding: 5px 10px; border-radius: 6px; font-weight: 700;">${p}</button>`;
            } else {
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRepairPage(${p})" style="padding: 5px 10px; border-radius: 6px;">${p}</button>`;
            }
        }

        const disabledNext = repairCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 10px; border-radius: 6px;"' : 'style="padding: 5px 10px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToRepairPage(${repairCurrentPage + 1})" ${disabledNext}>Sau &rarr;</button>`;

        controlsEl.innerHTML = html;
    }

    function goToRepairPage(page) {
        repairCurrentPage = page;
        renderRepairPagination();
    }

    function showTab(type) {
        const reqContent = document.getElementById('tab-requests-content');
        const repContent = document.getElementById('tab-repairs-content');
        const allBtn = document.getElementById('tab-all-btn');
        const reqBtn = document.getElementById('tab-requests-btn');
        const repBtn = document.getElementById('tab-repairs-btn');

        [allBtn, reqBtn, repBtn].forEach(btn => {
            btn.className = 'btn btn-outline btn-sm';
        });

        if (type === 'all') {
            reqContent.style.display = 'block';
            repContent.style.display = 'block';
            allBtn.className = 'btn btn-primary btn-sm';
        } else if (type === 'requests') {
            reqContent.style.display = 'block';
            repContent.style.display = 'none';
            reqBtn.className = 'btn btn-primary btn-sm';
        } else if (type === 'repairs') {
            reqContent.style.display = 'none';
            repContent.style.display = 'block';
            repBtn.className = 'btn btn-primary btn-sm';
        }
    }

    function viewRequestDetails(item) {
        document.getElementById('modalTitle').textContent = 'Chi tiết Đơn Một cửa: ' + item.MaDon;

    let extraHtml = '';
    if (item.GiuongMoi) {
        extraHtml += `<tr><td class="info-label">Giường chuyển đến:</td><td class="info-value"><b>Phòng ${escapeHtml(item.PhongMoi || '')} - Giường ${escapeHtml(item.GiuongMoi)}</b></td></tr>`;
    }
    if (item.TuNgay) {
        extraHtml += `<tr><td class="info-label">Thời gian áp dụng:</td><td class="info-value">Từ ngày ${escapeHtml(item.TuNgay)} ${item.DenNgay ? 'đến ' + escapeHtml(item.DenNgay) : ''}</td></tr>`;
    }
    if (item.TepMinhChung) {
        extraHtml += `<tr><td class="info-label">Tệp minh chứng:</td><td class="info-value"><a href="<?= url('student/requests/attachment') ?>?kind=proof&id=${encodeURIComponent(item.MaDon)}" target="_blank" rel="noopener" style="color: #0284c7; text-decoration: underline;">Tải / Xem tệp đính kèm</a></td></tr>`;
    }

        document.getElementById('modalBody').innerHTML = `
        <table class="info-table" style="margin-bottom: 16px;">
            <tr><td class="info-label">Mã đơn:</td><td class="info-value"><code><b>${escapeHtml(item.MaDon)}</b></code></td></tr>
            <tr><td class="info-label">Loại dịch vụ:</td><td class="info-value"><b>${escapeHtml(item.LoaiDon)}</b></td></tr>
            <tr><td class="info-label">Trạng thái duyệt:</td><td class="info-value"><span class="badge ${item.TrangThaiDuyet === 'Chờ duyệt' ? 'badge-warning' : (item.TrangThaiDuyet === 'Từ chối' ? 'badge-danger' : 'badge-success')}">${escapeHtml(item.TrangThaiDuyet || 'Chờ duyệt')}</span></td></tr>
            ${item.MaHopDong ? `<tr><td class="info-label">Mã hợp đồng:</td><td class="info-value"><code>${escapeHtml(item.MaHopDong)}</code></td></tr>` : ''}
            ${extraHtml}
        </table>
        <div>
            <label style="display:block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Nội dung chi tiết / Lý do gửi:</label>
            <div style="background: #f8fafc; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.875rem; color: var(--text-primary); line-height: 1.6; white-space: pre-wrap;">${escapeHtml(item.LyDo || 'Không có')}</div>
        </div>
        <div style="margin-top: 14px; padding: 10px 12px; background: #eff6ff; border-radius: 6px; font-size: 0.8rem; color: #1e40af;">
            Nếu cần hỗ trợ khẩn cấp, sinh viên vui lòng liên hệ trực tiếp Trưởng tòa hoặc Văn phòng Ban quản lý KTX.
        </div>
    `;

        const modal = document.getElementById('detailModal');
        modal.style.display = 'flex';
    }

    function viewRepairDetails(item) {
        document.getElementById('modalTitle').textContent = 'Chi tiết Phiếu báo hỏng: ' + item.MaPhieu;

        document.getElementById('modalBody').innerHTML = `
        <table class="info-table" style="margin-bottom: 16px;">
            <tr><td class="info-label">Mã phiếu sự cố:</td><td class="info-value"><code><b>${escapeHtml(item.MaPhieu)}</b></code></td></tr>
            <tr><td class="info-label">Thiết bị hỏng:</td><td class="info-value"><b>${escapeHtml(item.TenThietBi || 'Chung trong phòng')}</b> ${item.MaSoThietBi ? '(' + escapeHtml(item.MaSoThietBi) + ')' : ''}</td></tr>
            <tr><td class="info-label">Thời gian báo:</td><td class="info-value">${escapeHtml(item.NgayBao || '')}</td></tr>
            <tr><td class="info-label">Trạng thái sửa chữa:</td><td class="info-value"><span class="badge ${item.TrangThaiSua === 'Hoàn thành' ? 'badge-success' : (item.TrangThaiSua === 'Đang xử lý' ? 'badge-info' : 'badge-warning')}">${escapeHtml(item.TrangThaiSua || 'Chờ xử lý')}</span></td></tr>
            ${item.GhiChuTienDo ? `<tr><td class="info-label">Tiến độ kỹ thuật:</td><td class="info-value"><b style="color: #0284c7;">${escapeHtml(item.GhiChuTienDo)}</b></td></tr>` : ''}
        </table>
        <div>
            <label style="display:block; font-size: 0.85rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Mô tả hiện tượng hư hỏng:</label>
            <div style="background: #f8fafc; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--border); font-size: 0.875rem; color: var(--text-primary); line-height: 1.6; white-space: pre-wrap;">${escapeHtml(item.MoTaHuHong || 'Không có')}</div>
        </div>
        ${item.HinhAnh ? `<div style="margin-top:14px"><a href="<?= url('student/requests/attachment') ?>?kind=repair&id=${encodeURIComponent(item.MaPhieu)}" target="_blank" rel="noopener" style="color:#0284c7;text-decoration:underline">Xem ảnh hiện trường</a></div>` : ''}
    `;

        const modal = document.getElementById('detailModal');
        modal.style.display = 'flex';
    }

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    window.addEventListener('click', function(e) {
        const modal = document.getElementById('detailModal');
        if (e.target === modal) {
            closeDetailModal();
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        renderRequestPagination();
        renderRepairPagination();
    });
</script>

<?php
$content = ob_get_clean();
$title = 'Cổng dịch vụ một cửa & Lịch sử - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
