<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Ghi nhận Điện Nước & Quản lý Hóa đơn Công nợ</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Ghi nhận Điện Nước & Quản lý Công nợ</h1>
                <p class="page-subtitle">
                    <strong>Kỳ ghi nhận: Tháng 10/2026</strong> &bull; Hỗ trợ ghi chỉ số công tơ, tự động phát hiện số liệu bất thường, phát hành hóa đơn điện nước và đối soát công nợ.
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
            <a href="<?= url('staff/utilities') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">⚡ 3. Điện Nước & Công nợ</a>
            <a href="<?= url('staff/requests') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #1e40af; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>⚡ Nguyên tắc ghi chỉ số điện nước & Phát hành hóa đơn (Mục 1, 3, 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #1e3a8a; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Quy tắc số đo:</strong> <code>DienMoi >= DienCu</code> và <code>NuocMoi >= NuocCu</code>. Nếu nhập số mới bé hơn số cũ hệ thống sẽ chặn và cảnh báo lỗi công tơ quay vòng.</li>
            <li><strong>Cảnh báo bất thường (Smart Anomaly):</strong> Nếu lượng tiêu thụ tăng vọt gấp > 3 lần mức trung bình 3 tháng trước đó, hệ thống gắn cờ <code>CanhBaoBatThuong = 1</code> để nhân viên kiểm tra rò rỉ hoặc chập điện trước khi chốt hóa đơn.</li>
            <li><strong>Phát hành & Đối soát:</strong> Sau khi Trưởng cụm chốt số, hóa đơn điện nước phòng được phát hành. Tuyệt đối không xóa/sửa số tiền của hóa đơn đã ở trạng thái <code>'Đã thanh toán'</code>.</li>
            <li><strong>Mô phỏng demo:</strong> Cho phép BQL bấm nút <em>"Xác nhận thanh toán mô phỏng"</em> để kiểm thử luồng gạch nợ tự động.</li>
        </ul>
    </div>

    <!-- KHỐI 1: BIỂU MẪU NHẬP CHỈ SỐ ĐIỆN NƯỚC THEO PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu ghi nhận chỉ số công tơ Điện & Nước (Kỳ tháng 10/2026)</span>
            <span class="badge badge-info">Bảng: ChiSoDienNuoc</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu chỉ số điện nước thành công! Hệ thống đã tính thành tiền theo biểu giá bậc thang.');">
            <div class="grid grid-cols-4" style="gap: 14px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Mã phòng:</label>
                    <select class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" required>
                        <option value="A01.101">Phòng A01.101 (2 SV)</option>
                        <option value="A01.102">Phòng A01.102 (1 SV)</option>
                        <option value="A01.201">Phòng A01.201 (4 SV)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Chỉ số Điện cũ / Mới:</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="number" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" value="1150" readonly title="Chỉ số điện cũ từ tháng trước">
                        <input type="number" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" placeholder="Điện mới" value="1320" required>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Chỉ số Nước cũ / Mới:</label>
                    <div style="display: flex; gap: 6px;">
                        <input type="number" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" value="430" readonly title="Chỉ số nước cũ từ tháng trước">
                        <input type="number" class="btn-outline" style="width: 50%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" placeholder="Nước mới" value="465" required>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 600; margin-bottom: 4px;">Số SV thực tế:</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 8px; border-radius: 6px; font-size: 0.85rem;" value="2" min="1">
                </div>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" style="border-radius: 6px;" onclick="alert('Mở hộp thoại Import file Excel danh sách chỉ số toàn Cụm nhà')">
                    📁 Import Excel hàng loạt
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px;">
                    💾 Lưu chỉ số & Kiểm tra bất thường
                </button>
            </div>
        </form>
    </div>

    <!-- KHỐI 2: BẢNG THEO DÕI CHỈ SỐ VÀ CẢNH BÁO BẤT THƯỜNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách Số đo Công tơ & Tiêu thụ Điện Nước (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã chốt sổ và phát hành hóa đơn điện nước tháng 10/2026 cho tất cả phòng trong cụm!')">
                    📢 Chốt sổ & Phát hành Hóa đơn Cụm
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Phòng</th>
                        <th>Điện cũ &rarr; Mới</th>
                        <th>Tiêu thụ Điện</th>
                        <th>Nước cũ &rarr; Mới</th>
                        <th>Tiêu thụ Nước</th>
                        <th>Sĩ số</th>
                        <th>Tổng tiền dự kiến</th>
                        <th>Cảnh báo bất thường</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>A01.101</b></td>
                        <td>1000 &rarr; 1150</td>
                        <td><b>150 kWh</b></td>
                        <td>400 &rarr; 430</td>
                        <td><b>30 m³</b></td>
                        <td>2 SV</td>
                        <td><b>735.000 đ</b></td>
                        <td>
                            <span class="badge badge-success">Bình thường</span>
                        </td>
                        <td style="text-align: right;">
                            <span class="badge badge-success">Đã phát hành HĐ</span>
                        </td>
                    </tr>
                    <tr style="background: #fffbeb;">
                        <td><b>A01.102</b></td>
                        <td>850 &rarr; 1400</td>
                        <td><b style="color: #b91c1c;">550 kWh (Tăng 3.6x)</b></td>
                        <td>310 &rarr; 330</td>
                        <td><b>20 m³</b></td>
                        <td>1 SV</td>
                        <td><b>1.680.000 đ</b></td>
                        <td>
                            <span class="badge badge-danger">⚠️ Tăng đột biến &gt;3 lần!</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Yêu cầu nhân viên kỹ thuật xuống phòng A01.102 kiểm tra rò điện trước khi xuất hóa đơn!')">
                                🔍 Kiểm tra đồng hồ
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>A01.201</b></td>
                        <td>2100 &rarr; 2280</td>
                        <td><b>180 kWh</b></td>
                        <td>510 &rarr; 545</td>
                        <td><b>35 m³</b></td>
                        <td>4 SV</td>
                        <td><b>890.000 đ</b></td>
                        <td>
                            <span class="badge badge-success">Bình thường</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phát hành hóa đơn phòng A01.201!')">
                                📄 Phát hành HĐ
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 3: THEO DÕI HÓA ĐƠN & ĐỐI SOÁT CÔNG NỢ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Theo dõi Hóa đơn Công nợ & Đối soát Giao dịch</span>
            <span class="badge badge-info">Bảng: HoaDon + GiaoDichThanhToan</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Hóa Đơn</th>
                        <th>Đối tượng nộp</th>
                        <th>Loại hóa đơn</th>
                        <th>Số tiền phải đóng</th>
                        <th>Ngày tạo</th>
                        <th>Hạn thanh toán</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác BQL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>HD-DN-202609-001</b></td>
                        <td>Phòng A01.101 (Đại diện: Nguyễn Văn An)</td>
                        <td>Điện nước tháng 09/2026</td>
                        <td><b>735.000 đ</b></td>
                        <td>30/09/2026</td>
                        <td>15/10/2026</td>
                        <td>
                            <span class="badge badge-success">✓ Đã thanh toán</span>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Mã GD: GD202610001 (QR KTX)</span>
                        </td>
                    </tr>
                    <tr>
                        <td><b>HD-DN-202610-002</b></td>
                        <td>Phòng A01.201 (Đại diện: Lê Hoàng Hải)</td>
                        <td>Điện nước tháng 10/2026</td>
                        <td><b>890.000 đ</b></td>
                        <td>25/10/2026</td>
                        <td>05/11/2026</td>
                        <td>
                            <span class="badge badge-warning">⏳ Chưa thanh toán</span>
                        </td>
                        <td style="text-align: right;">
                            <!-- NÚT DEMO XÁC NHẬN THANH TOÁN MÔ PHỎNG THEO QUY ĐỊNH FILE PDF -->
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #166534; border-color: #86efac;" onclick="alert('Xác nhận thanh toán mô phỏng thành công! Đã ghi nhận bút toán vào GiaoDichThanhToan và chuyển trạng thái hóa đơn sang Đã thanh toán.')">
                                ⚡ Xác nhận thanh toán mô phỏng
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
$title = "Điện Nước & Công nợ - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
