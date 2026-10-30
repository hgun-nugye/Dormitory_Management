<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Điều phối Sửa chữa & Phiếu Báo Hỏng</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Điều phối Sửa chữa & Phiếu Báo Hỏng</h1>
                <p class="page-subtitle">
                    <strong>Phân hệ Kỹ thuật - Bảo trì:</strong> Trưởng cụm phân công nhân viên kỹ thuật; nhân viên thường cập nhật tiến độ sửa chữa thiết bị phòng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>).
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
            <a href="<?= url('staff/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🏢 1. Sơ đồ Phòng & Cư trú</a>
            <a href="<?= url('staff/applications') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #991b1b; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>🛠️ Quy tắc điều phối & bảo toàn dữ liệu Bảo trì (Mục 1, 3, 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #b91c1c; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Điều phối công việc:</strong> Trưởng cụm (<code>TRUONG_CUM</code>) có quyền gán nhân viên kỹ thuật phụ trách (<code>NhanVienKyThuat</code>) theo từng phiếu báo hỏng.</li>
            <li><strong>Cập nhật trạng thái sửa:</strong> Nhân viên thường (<code>NV_THUONG</code>) cập nhật trạng thái sửa chữa (<code>TrangThaiSua</code>: 'Chờ tiếp nhận' &rarr; 'Đang sửa chữa' &rarr; 'Đã hoàn thành').</li>
            <li><strong>Tự động hoàn nguyên thiết bị:</strong> Khi chuyển <code>TrangThaiSua = 'Đã hoàn thành'</code>, hệ thống tự động cập nhật <code>ThietBiPhong.TinhTrangThietBi = 'Tốt'</code>.</li>
            <li><strong>Bảo toàn dữ liệu:</strong> Nhân viên kỹ thuật tuyệt đối không được sửa đổi nội dung mô tả sự cố ban đầu hoặc điểm đánh giá sao (<code>SoSaoDanhGia</code> 1-5 sao) do sinh viên phản hồi.</li>
        </ul>
    </div>

    <!-- KHỐI 1: FORM MÔ PHỎNG PHÂN CÔNG THỢ & TIẾP NHẬN BẢO TRÌ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu Điều phối & Phân công Cán bộ Kỹ thuật</span>
            <span class="badge badge-info">Bảng: PhieuBaoHong + NhanVien</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã phân công cán bộ kỹ thuật và gửi thông báo Zalo/Email cho sinh viên!');">
            <div class="grid grid-cols-4" style="gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Phiếu sự cố:</label>
                    <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" required>
                        <option value="BH2026001">BH2026001 - Quạt trần kêu to (Phòng A01.101)</option>
                        <option value="BH2026002">BH2026002 - Máy lạnh chảy nước (Phòng H01.301)</option>
                        <option value="BH2026003">BH2026003 - Vòi sen rỉ nước (Phòng A01.102)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Cán bộ kỹ thuật phụ trách:</label>
                    <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" required>
                        <option value="3">Trần Văn Minh (Tổ điện - nước)</option>
                        <option value="4">Nguyễn Văn Đạt (Kỹ thuật điện lạnh)</option>
                        <option value="6">Lê Văn Hùng (Tổ mộc - xây dựng)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Hạn hoàn thành dự kiến:</label>
                    <input type="date" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" value="2026-11-01" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Trạng thái điều phối:</label>
                    <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                        <option value="Đang sửa chữa">Đang sửa chữa</option>
                        <option value="Chờ linh kiện">Chờ linh kiện thay thế</option>
                        <option value="Đã hoàn thành">Đã hoàn thành (Cập nhật TB về Tốt)</option>
                    </select>
                </div>
            </div>

            <div style="text-align: right;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px;">
                    🛠️ Cập nhật điều phối & Gán thợ
                </button>
            </div>
        </form>
    </div>

    <!-- KHỐI 2: DANH SÁCH PHIẾU BÁO HỎNG & ĐÁNH GIÁ CỦA SINH VIÊN -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách Phiếu Báo hỏng Thiết bị trong Cụm <?= htmlspecialchars($maCum ?? 'AG') ?></span>
            <span class="badge badge-warning">3 phiếu đang xử lý</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Phiếu</th>
                        <th>Phòng / Tòa</th>
                        <th>Thiết bị hư hỏng</th>
                        <th>Mô tả sự cố của Sinh viên</th>
                        <th>Kỹ thuật phụ trách</th>
                        <th>Trạng thái sửa chữa</th>
                        <th>Đánh giá sao</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>BH2026001</b></td>
                        <td>
                            <b>A01.101</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Tòa A01</span>
                        </td>
                        <td>
                            <b>Quạt trần</b> <br>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">QT-A01101-01</span>
                        </td>
                        <td>
                            Quạt trần phát ra tiếng kêu rè rè khi bật số lớn, cần tra dầu hoặc thay bạc đạn. <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Báo bởi: Nguyễn Văn An (2110001) lúc 10/10/2026</span>
                        </td>
                        <td>
                            <b>Trần Văn Minh</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Tổ điện nước</span>
                        </td>
                        <td>
                            <span class="badge badge-warning">Đang sửa chữa</span>
                        </td>
                        <td>
                            <span style="color: var(--text-muted); font-size: 0.8rem;">Chờ hoàn thành</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã hoàn thành sửa chữa! Hệ thống tự động chuyển tình trạng thiết bị Quạt trần về Tốt.')">
                                ✓ Hoàn thành
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <td><b>BH2026002</b></td>
                        <td>
                            <b>H01.301</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Tòa H01</span>
                        </td>
                        <td>
                            <b>Máy lạnh</b> <br>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">ML-H01301-01</span>
                        </td>
                        <td>
                            Máy lạnh không lạnh sâu và bị chảy nước nhỏ giọt xuống sàn gỗ. <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Báo bởi: Trần Thị Mai Chúc (2212345)</span>
                        </td>
                        <td>
                            <span class="badge badge-danger">Chưa phân công</span>
                        </td>
                        <td>
                            <span class="badge badge-danger">Chờ tiếp nhận</span>
                        </td>
                        <td>
                            <span style="color: var(--text-muted); font-size: 0.8rem;">Chưa có</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Tiếp nhận phiếu và mở form gán thợ điện lạnh!')">
                                🔧 Phân công thợ
                            </button>
                        </td>
                    </tr>

                    <!-- PHIẾU ĐÃ SỬA XONG VÀ ĐƯỢC SINH VIÊN CHẤM SAO (READ-ONLY CHO KỸ THUẬT) -->
                    <tr style="background: #f0fdf4;">
                        <td><b>BH2026-X09</b></td>
                        <td>
                            <b>A01.201</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Tòa A01</span>
                        </td>
                        <td>
                            <b>Bóng đèn LED</b> <br>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">Đèn tuýp LED 1.2m</span>
                        </td>
                        <td>
                            Đèn bị chập chờn rồi tắt hẳn. <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Báo bởi: Lê Hoàng Hải lúc 20/10/2026</span>
                        </td>
                        <td>
                            <b>Trần Văn Minh</b>
                        </td>
                        <td>
                            <span class="badge badge-success">✓ Đã hoàn thành</span>
                        </td>
                        <td>
                            <b style="color: #f59e0b;">⭐⭐⭐⭐⭐ (5/5)</b> <br>
                            <span style="font-size: 0.75rem; color: var(--text-muted);">"Thợ nhiệt tình, thay nhanh"</span>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">Đã nghiệm thu (Khóa sửa)</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Điều phối Sửa chữa Thiết bị - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
