<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Xét duyệt Chỗ ở & Hồ sơ Miễn giảm</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Xét duyệt Chỗ ở & Hồ sơ Miễn giảm</h1>
                <p class="page-subtitle">
                    <strong>Phân hệ:</strong> Xét duyệt hồ sơ đăng ký giữ chỗ 72h (Trưởng cụm) và Thẩm định hồ sơ miễn giảm học phí nội trú (Cấp Quản lý KTX).
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
            <a href="<?= url('staff/applications') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">📋 2. Xét duyệt Chỗ ở & Miễn giảm</a>
            <a href="<?= url('staff/utilities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #92400e; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>⚠️ Nguyên tắc xét duyệt hồ sơ & Giải phóng giường (Mục 1 & 3 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #b45309; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Quy tắc giữ chỗ 72h:</strong> Khi BQL bấm [Duyệt giữ chỗ 72h], hệ thống tự động gán <code>NguoiDuyet</code>, <code>ThoiGianDuyet = NOW()</code> và tính <code>HanChotThanhToan = NOW() + 72 giờ</code>.</li>
            <li><strong>Giải phóng giường:</strong> Nếu sinh viên bị Từ chối hoặc không thanh toán sau 72 giờ, giường đã khóa tự động được chuyển về <code>TrangThaiGiuong = 'Trống'</code>.</li>
            <li><strong>Thẩm định miễn giảm:</strong> Mức giảm quy định từ <code>0% đến 100%</code>, tự động tính <code>SoTienHoanTra</code> theo đơn giá trần phòng nội trú chuẩn.</li>
            <li><strong>Phân quyền:</strong> Nhân viên thường (<code>NV_THUONG</code>) không có quyền duyệt hồ sơ hoặc duyệt miễn giảm.</li>
        </ul>
    </div>

    <!-- PHẦN 1: DANH SÁCH HỒ SƠ ĐĂNG KÝ CHỖ Ở MỚI CHỜ DUYỆT (CẤP CỤM) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>1. Danh sách Hồ sơ Đăng ký Chỗ ở Chờ duyệt (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-warning">Bảng: HoSoDangKy + DotDangKy + Giuong</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Hồ Sơ</th>
                        <th>Sinh viên</th>
                        <th>Trường ĐH</th>
                        <th>Giới tính</th>
                        <th>Phòng & Giường đề xuất</th>
                        <th>Thời gian nộp</th>
                        <th>Thời hạn 72h</th>
                        <th style="text-align: right;">Thao tác phê duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>HS2026-089</b></td>
                        <td>
                            <b>Nguyễn Trần Quang</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2410888</span>
                        </td>
                        <td>ĐH Bách Khoa</td>
                        <td>Nam</td>
                        <td>
                            Phòng <b>A01.101</b> <br>
                            <span class="badge badge-info">Giường A01101-G3</span>
                        </td>
                        <td>28/10/2026 14:15</td>
                        <td><span class="badge badge-warning">Còn 42h</span></td>
                        <td style="text-align: right;">
                            <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="maHoSo" value="HS2026-089">
                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                    ✓ Duyệt giữ chỗ 72h
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="const lyDo = prompt('Nhập lý do từ chối hồ sơ:'); if(lyDo) alert('Đã từ chối hồ sơ. Giường A01101-G3 đã được trả về trạng thái Trống!');">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>HS2026-092</b></td>
                        <td>
                            <b>Phạm Văn Khang</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2411054</span>
                        </td>
                        <td>ĐH Khoa học Tự nhiên</td>
                        <td>Nam</td>
                        <td>
                            Phòng <b>A01.102</b> <br>
                            <span class="badge badge-info">Giường A01102-G2</span>
                        </td>
                        <td>29/10/2026 09:30</td>
                        <td><span class="badge badge-warning">Còn 68h</span></td>
                        <td style="text-align: right;">
                            <form action="<?= url('staff/approve-application') ?>" method="POST" style="display:inline;">
                                <?= csrf_field() ?>
                                <input type="hidden" name="maHoSo" value="HS2026-092">
                                <button type="submit" class="btn btn-primary btn-sm" style="border-radius: 6px;">
                                    ✓ Duyệt giữ chỗ 72h
                                </button>
                            </form>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="const lyDo = prompt('Nhập lý do từ chối hồ sơ:'); if(lyDo) alert('Đã từ chối hồ sơ!');">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHẦN 2: THẨM ĐỊNH & XÉT DUYỆT HỒ SƠ MIỄN GIẢM NỘI TRÚ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>2. Thẩm định & Duyệt Hồ sơ Miễn giảm nội trú</span>
            <span class="badge badge-info">Bảng: HoSoMienGiam + HopDongNoiTru + SinhVien</span>
        </div>

        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
            Dành cho cán bộ thẩm định điều kiện chính sách, kiểm tra giấy xác nhận minh chứng và tính toán số tiền hoàn trả hoặc cấn trừ tiền phòng:
        </p>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đơn MG</th>
                        <th>Sinh viên nộp</th>
                        <th>Hợp đồng</th>
                        <th>Đối tượng chính sách</th>
                        <th>Minh chứng</th>
                        <th>Mức giảm đề xuất</th>
                        <th>Số tiền hoàn trả</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>MG2026-004</b></td>
                        <td>
                            <b>Trần Thị Mai Chúc</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2212345</span>
                        </td>
                        <td>HD2026002</td>
                        <td>
                            <span class="badge badge-info">Hộ cận nghèo</span>
                        </td>
                        <td>
                            <a href="#" onclick="alert('Mở file đính kèm: giay_chung_nhan_can_ngheo.pdf'); return false;" style="font-size: 0.85rem; color: var(--primary);">
                                📎 giay_can_ngheo.pdf
                            </a>
                        </td>
                        <td><b>50%</b> (Đơn giá 650.000đ)</td>
                        <td><b style="color: #15803d;">325.000 đ / tháng</b></td>
                        <td>
                            <span class="badge badge-warning">Chờ thẩm định</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phê duyệt miễn giảm 50%! Bút toán hoàn tiền 325.000đ đã được lập.')">
                                ✓ Phê duyệt mức giảm
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã yêu cầu sinh viên bổ sung thêm giấy tờ minh chứng!')">
                                ↺ Yêu cầu bổ sung
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>MG2026-005</b></td>
                        <td>
                            <b>Lê Hoàng Phúc</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2410112</span>
                        </td>
                        <td>HD2026019</td>
                        <td>
                            <span class="badge badge-info">Con thương binh liệt sĩ</span>
                        </td>
                        <td>
                            <a href="#" onclick="alert('Mở file đính kèm: giay_thuong_binh.pdf'); return false;" style="font-size: 0.85rem; color: var(--primary);">
                                📎 the_thuong_binh.pdf
                            </a>
                        </td>
                        <td><b>100%</b> (Đơn giá 650.000đ)</td>
                        <td><b style="color: #15803d;">650.000 đ / tháng</b></td>
                        <td>
                            <span class="badge badge-warning">Chờ thẩm định</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phê duyệt miễn giảm 100%!')">
                                ✓ Phê duyệt mức giảm
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Từ chối hồ sơ')">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Xét duyệt Chỗ ở & Miễn giảm - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
