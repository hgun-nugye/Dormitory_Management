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
                    <strong>Phạm vi Cụm: <?= htmlspecialchars($maCum ?? 'AG') ?></strong> &bull; Hiển thị phòng/giường trực quan, chỉ định Trưởng phòng, kiểm tra tình trạng nộp mẫu CT01 và xác nhận nhận phòng thực tế.
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

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #166534; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>📌 Quy tắc nghiệp vụ Cư trú & Sơ đồ phòng (Mục 1 & 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #15803d; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Phạm vi dữ liệu:</strong> Cán bộ cấp Cụm (<code>TRUONG_CUM</code>, <code>NV_THUONG</code>) chỉ truy vấn và thao tác trên các tòa nhà thuộc cụm phân công (<code>MaCumQuanLy</code>).</li>
            <li><strong>Xác nhận nhận phòng:</strong> Chỉ thực hiện khi hồ sơ đăng ký đã duyệt và thanh toán thành công; hệ thống tự động cập nhật <code>NgayNhanPhong</code> và đồng bộ sĩ số <code>SoSVDangO</code>.</li>
            <li><strong>Chỉ định Trưởng phòng:</strong> Trưởng cụm có quyền gán cờ <code>TruongPhong</code> cho 1 sinh viên nội trú đại diện nhận hóa đơn và gửi phản ánh chung.</li>
            <li><strong>Bảo toàn dữ liệu:</strong> Tuyệt đối không xóa (DELETE) phòng/giường đang có hợp đồng hiệu lực. Không chỉnh sửa thủ công số người lệch với hợp đồng.</li>
        </ul>
    </div>

    <!-- BỘ LỌC TÒA NHÀ & TẦNG TRONG CỤM -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Bộ lọc tra cứu Sơ đồ phòng (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-info">JOIN: KhuKTX + CumNha + ToaNha + Phong + Giuong</span>
        </div>
        <div class="grid grid-cols-4" style="gap: 14px;">
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tòa nhà:</label>
                <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                    <option value="">-- Tất cả tòa trong Cụm --</option>
                    <option value="A01" selected>Tòa A01 (Nam)</option>
                    <option value="A02">Tòa A02 (Nữ)</option>
                    <option value="A03">Tòa A03 (Nam)</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tầng:</label>
                <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                    <option value="">-- Tất cả các tầng --</option>
                    <option value="1" selected>Tầng 1</option>
                    <option value="2">Tầng 2</option>
                    <option value="3">Tầng 3</option>
                    <option value="4">Tầng 4</option>
                    <option value="5">Tầng 5</option>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">Tình trạng phòng:</label>
                <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="Đang sử dụng">Đang sử dụng</option>
                    <option value="Còn chỗ">Còn chỗ</option>
                    <option value="Đầy chỗ">Đầy chỗ</option>
                    <option value="Khóa bảo trì">Khóa bảo trì</option>
                </select>
            </div>
            <div style="display: flex; align-items: flex-end;">
                <button type="button" class="btn btn-primary btn-block" style="border-radius: 6px; height: 38px;">
                    🔍 Lọc dữ liệu
                </button>
            </div>
        </div>
    </div>

    <!-- DANH SÁCH SƠ ĐỒ PHÒNG VÀ HỒ SƠ SINH VIÊN CƯ TRÚ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách Phòng ở & Thành viên Lưu trú</span>
            <span class="badge badge-success">Sĩ số cập nhật tự động</span>
        </div>

        <!-- MÔ PHỎNG PHÒNG 1: A01.101 (Có Trưởng phòng, Đã nộp CT01) -->
        <div style="border: 1px solid var(--border); border-radius: 8px; margin-bottom: 20px; overflow: hidden;">
            <div style="background: #f8fafc; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
                <div>
                    <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">Phòng A01.101</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: 10px;">(Tòa A01 &bull; Tầng 1 &bull; Tiêu chuẩn 4 giường)</span>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="badge badge-info">Sĩ số: 2 / 4 SV</span>
                    <span class="badge badge-warning">Lấp đầy: 50%</span>
                    <button class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Mô phỏng: Cập nhật trạng thái phòng sang Khóa bảo trì')">
                        🔒 Khóa bảo trì phòng
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã Giường</th>
                            <th>Sinh viên lưu trú</th>
                            <th>Trường ĐH</th>
                            <th>Số CCCD / SĐT</th>
                            <th>Vai trò phòng</th>
                            <th>Tờ khai CT01</th>
                            <th>Ngày nhận phòng</th>
                            <th style="text-align: right;">Thao tác BQL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>A01101-G1</b></td>
                            <td>
                                <b>Nguyễn Văn An</b> <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2110001</span>
                            </td>
                            <td>Trường ĐH Bách khoa</td>
                            <td>
                                079205001234 <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">0901234567</span>
                            </td>
                            <td>
                                <span class="badge badge-success">⭐ Trưởng phòng</span>
                            </td>
                            <td>
                                <span class="badge badge-success">✓ Đã nộp CT01</span>
                            </td>
                            <td>01/09/2026 08:00</td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã đồng bộ thông tin cư trú!')">
                                    📄 Xem HĐ
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td><b>A01101-G2</b></td>
                            <td>
                                <b>Trần Đình Trọng</b> <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2110099</span>
                            </td>
                            <td>Trường ĐH Bách khoa</td>
                            <td>
                                079205009876 <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">0909888777</span>
                            </td>
                            <td>
                                <span style="font-size: 0.85rem; color: var(--text-muted);">Thành viên</span>
                            </td>
                            <td>
                                <span class="badge badge-warning">⏳ Chưa nộp CT01</span>
                            </td>
                            <td>01/09/2026 09:30</td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Mô phỏng: Gán sinh viên này làm Trưởng phòng!')">
                                    ⭐ Gán Trưởng phòng
                                </button>
                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Mô phỏng: Đã tích nhận hồ sơ Mẫu CT01 cư trú!')">
                                    📥 Thu CT01
                                </button>
                            </td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td><b>A01101-G3</b></td>
                            <td colspan="6" style="color: var(--text-muted); font-style: italic;">
                                ⚪ Giường trống (Sẵn sàng tiếp nhận sinh viên mới)
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-info">Trống</span>
                            </td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td><b>A01101-G4</b></td>
                            <td colspan="6" style="color: var(--text-muted); font-style: italic;">
                                ⚪ Giường trống (Sẵn sàng tiếp nhận sinh viên mới)
                            </td>
                            <td style="text-align: right;">
                                <span class="badge badge-info">Trống</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MÔ PHỎNG PHÒNG 2: A01.102 (Đang có sinh viên chờ nhận phòng thực tế) -->
        <div style="border: 1px solid var(--border); border-radius: 8px; overflow: hidden;">
            <div style="background: #f8fafc; padding: 12px 18px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border);">
                <div>
                    <span style="font-size: 1.1rem; font-weight: 700; color: var(--primary);">Phòng A01.102</span>
                    <span style="font-size: 0.85rem; color: var(--text-muted); margin-left: 10px;">(Tòa A01 &bull; Tầng 1 &bull; Tiêu chuẩn 4 giường)</span>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="badge badge-info">Sĩ số: 1 / 4 SV</span>
                    <span class="badge badge-danger">Lấp đầy: 25% (Đề xuất dồn phòng)</span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Mã Giường</th>
                            <th>Sinh viên lưu trú</th>
                            <th>Trường ĐH</th>
                            <th>Số CCCD / SĐT</th>
                            <th>Vai trò phòng</th>
                            <th>Tờ khai CT01</th>
                            <th>Ngày nhận phòng</th>
                            <th style="text-align: right;">Thao tác BQL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><b>A01102-G1</b></td>
                            <td>
                                <b>Lê Văn Hùng</b> <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2311200</span>
                            </td>
                            <td>Trường ĐH KHTN</td>
                            <td>
                                072205001122 <br>
                                <span style="font-size: 0.8rem; color: var(--text-muted);">0918123123</span>
                            </td>
                            <td>
                                <span class="badge badge-success">⭐ Trưởng phòng</span>
                            </td>
                            <td>
                                <span class="badge badge-warning">⏳ Chưa nộp CT01</span>
                            </td>
                            <td>
                                <span class="badge badge-danger">Chưa nhận phòng</span>
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Mô phỏng: Xác nhận sinh viên đã đến nhận phòng thực tế, ghi nhận NgayNhanPhong = NOW() và kích hoạt hợp đồng!')">
                                    🔑 Xác nhận nhận phòng
                                </button>
                                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Mô phỏng: Đã tích nhận Mẫu CT01 cư trú!')">
                                    📥 Tích thu CT01
                                </button>
                            </td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td><b>A01102-G2</b></td>
                            <td colspan="6" style="color: var(--text-muted); font-style: italic;">⚪ Giường trống</td>
                            <td style="text-align: right;"><span class="badge badge-info">Trống</span></td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td><b>A01102-G3</b></td>
                            <td colspan="6" style="color: var(--text-muted); font-style: italic;">⚪ Giường trống</td>
                            <td style="text-align: right;"><span class="badge badge-info">Trống</span></td>
                        </tr>
                        <tr style="background: #fafafa;">
                            <td><b>A01102-G4</b></td>
                            <td colspan="6" style="color: var(--text-muted); font-style: italic;">⚪ Giường trống</td>
                            <td style="text-align: right;"><span class="badge badge-info">Trống</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Sơ đồ Phòng & Cư trú - BQL Cụm " . ($maCum ?? 'AG');
require dirname(__DIR__) . '/layouts/main.php';
?>
