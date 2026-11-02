<?php
ob_start();
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
                <h1 class="page-title">Xử lý Đơn từ Dịch vụ Một Cửa</h1>
                <p class="page-subtitle">
                    <strong>Phân hệ Dịch vụ Một Cửa:</strong> Tiếp nhận và xử lý tập trung Đơn xin Chuyển phòng, Đơn xin Tạm vắng và Đơn Trả phòng sớm / Chấm dứt hợp đồng.
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
            <a href="<?= url('staff/requests') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">📬 4. Đơn Một Cửa</a>
            <a href="<?= url('staff/maintenance') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛠️ 5. Điều phối Bảo trì</a>
            <a href="<?= url('staff/discipline') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🛡️ 6. An ninh & Rèn luyện</a>
        </div>
    </div>

    <!-- HỘP THÔNG TIN NGUYÊN TẮC BẢO TOÀN DỮ LIỆU & PHÂN QUYỀN (THEO PDF) -->
    <div class="card" style="margin-bottom: 24px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 16px;">
        <div style="font-weight: 700; color: #92400e; font-size: 0.9rem; margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
            <span>📋 Quy tắc xử lý Đơn từ Một Cửa & Hoán đổi Giường (Mục 1, 3, 4 PDF):</span>
        </div>
        <ul style="font-size: 0.85rem; color: #b45309; line-height: 1.6; padding-left: 20px; margin: 0;">
            <li><strong>Lưu vết hành chính:</strong> Tuyệt đối không xóa vật lý các đơn từ mà sinh viên đã nộp để phục vụ công tác đối soát, thanh tra.</li>
            <li><strong>Duyệt chuyển phòng tự động:</strong> Khi Trưởng cụm phê duyệt đơn chuyển phòng, hệ thống tự động:
                <ol style="padding-left: 20px; margin-top: 4px;">
                    <li>Cập nhật <code>MaGiuong</code> trong hợp đồng sang giường mới.</li>
                    <li>Chuyển trạng thái giường cũ về <code>'Trống'</code>, giường mới thành <code>'Đã có người ở'</code>.</li>
                    <li>Tự động tăng giảm sĩ số <code>SoSVDangO</code> của cả 2 phòng (phòng đi và phòng đến).</li>
                </ol>
            </li>
            <li><strong>Chuyển phòng khác cụm & Hoàn tiền:</strong> Thuộc thẩm quyền Trưởng Khu (<code>TRUONG_KHU</code>) hoặc Quản lý KTX trung tâm phê duyệt số tiền chênh lệch (<code>SoTienChenhLechHoan</code>).</li>
            <li><strong>Đơn xin tạm vắng:</strong> Cần được phê duyệt để hệ thống giám sát an ninh loại trừ khi quét cảnh báo vắng mặt quá 30 ngày.</li>
        </ul>
    </div>

    <!-- BỘ LỌC DANH SÁCH ĐƠN THEO LOẠI -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Danh sách Đơn từ Một Cửa chờ xử lý (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-info">Bảng: DonTuMotCua + HopDongNoiTru + Giuong + SinhVien</span>
        </div>

        <div style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
            <button class="btn btn-primary btn-sm" style="border-radius: 6px;">Tất cả đơn (4)</button>
            <button class="btn btn-outline btn-sm" style="border-radius: 6px;">🔄 Xin chuyển phòng (2)</button>
            <button class="btn btn-outline btn-sm" style="border-radius: 6px;">✈️ Xin tạm vắng (1)</button>
            <button class="btn btn-outline btn-sm" style="border-radius: 6px;">🚪 Trả phòng sớm (1)</button>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã Đơn</th>
                        <th>Sinh viên</th>
                        <th>Loại đơn</th>
                        <th>Nội dung chi tiết</th>
                        <th>Chênh lệch / Hoàn tiền</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác BQL</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- ĐƠN 1: XIN CHUYỂN PHÒNG CÙNG CỤM -->
                    <tr>
                        <td><b>DON2026-CP01</b></td>
                        <td>
                            <b>Nguyễn Văn An</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2110001 (ĐH Bách Khoa)</span>
                        </td>
                        <td>
                            <span class="badge badge-info">🔄 Chuyển phòng</span>
                        </td>
                        <td>
                            Hiện tại: <b>Phòng A01.101</b> (G1) &rarr; <br>
                            Đề xuất sang: <b>Phòng A01.102</b> (Giường <b>A01102-G2</b>) <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Lý do: Muốn chuyển sang cùng nhóm làm đồ án</span>
                        </td>
                        <td>0 đ (Cùng loại phòng 4 chỗ)</td>
                        <td>
                            <span class="badge badge-warning">Chờ Trưởng cụm duyệt</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã duyệt chuyển phòng thành công! Tự động cập nhật: Giường A01101-G1 về Trống, giường A01102-G2 thành Đã có người ở, đồng bộ sĩ số 2 phòng.')">
                                ✓ Duyệt đổi giường
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Từ chối đơn!')">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>

                    <!-- ĐƠN 2: XIN CHUYỂN PHÒNG KHÁC CỤM (CẦN CẤP KHU DUYỆT) -->
                    <tr style="background: #fafafa;">
                        <td><b>DON2026-CP02</b></td>
                        <td>
                            <b>Trần Thị Mai Chúc</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2212345 (ĐH KHTN)</span>
                        </td>
                        <td>
                            <span class="badge badge-info">🔄 Chuyển phòng liên cụm</span>
                        </td>
                        <td>
                            Hiện tại: <b>Phòng A01.102</b> (Cụm AG) &rarr; <br>
                            Đề xuất sang: <b>Phòng H01.301</b> (Cụm AH - Có máy lạnh) <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Lý do: Chuyển sang tòa nhà nữ</span>
                        </td>
                        <td><b style="color: #b91c1c;">+300.000 đ/tháng</b> (Máy lạnh)</td>
                        <td>
                            <span class="badge badge-warning">Chờ Trưởng Khu A duyệt</span>
                        </td>
                        <td style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Thẩm quyền Trưởng Khu A</span>
                        </td>
                    </tr>

                    <!-- ĐƠN 3: XIN TẠM VẮNG -->
                    <tr>
                        <td><b>DON2026-TV01</b></td>
                        <td>
                            <b>Lê Văn Hùng</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2311200</span>
                        </td>
                        <td>
                            <span class="badge badge-success">✈️ Tạm vắng</span>
                        </td>
                        <td>
                            Từ ngày: <b>01/11/2026</b> &rarr; Đến ngày: <b>15/12/2026</b> (45 ngày) <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Lý do: Đi thực tập tại doanh nghiệp ở Bình Dương</span>
                        </td>
                        <td>Không hoàn tiền</td>
                        <td>
                            <span class="badge badge-warning">Chờ BQL cụm duyệt</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Đã phê duyệt đơn tạm vắng! Hệ thống an ninh sẽ không tính cảnh báo vắng 30 ngày cho sinh viên này.')">
                                ✓ Duyệt tạm vắng
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Từ chối đơn!')">
                                ✕ Từ chối
                            </button>
                        </td>
                    </tr>

                    <!-- ĐƠN 4: TRẢ PHÒNG SỚM / HOÀN TIỀN -->
                    <tr>
                        <td><b>DON2026-TP01</b></td>
                        <td>
                            <b>Võ Hoàng Nam</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">MSSV: 2110444</span>
                        </td>
                        <td>
                            <span class="badge badge-danger">🚪 Trả phòng sớm</span>
                        </td>
                        <td>
                            Hợp đồng: <b>HD2026005</b> &bull; Ngày trả phòng: <b>31/10/2026</b> <br>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">Lý do: Hoàn thành khóa học sớm, về địa phương</span>
                        </td>
                        <td><b style="color: #15803d;">Hoàn trả: 1.300.000 đ</b> (2 tháng còn lại)</td>
                        <td>
                            <span class="badge badge-warning">Chờ QLKTX duyệt hoàn tiền</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã nghiệm thu thiết bị phòng và xác nhận bàn giao giường! Chuyển trạng thái hợp đồng sang Kết thúc.')">
                                📋 Nghiệm thu & Chấm dứt HĐ
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
$title = "Dịch vụ Một Cửa - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>
