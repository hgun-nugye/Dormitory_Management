<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm nhà</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">An ninh Ra Vào & Đánh giá Rèn luyện Nội trú</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Giám sát An ninh Ra Vào & Điểm Rèn luyện</h1>
            </div>
            <div>
                <a href="<?= url('staff/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về Dashboard Cụm
                </a>
            </div>
        </div>
    </div>

    <!-- KHỐI 1: CẢNH BÁO SINH VIÊN VẮNG MẶT > 30 NGÀY KHÔNG CÓ ĐƠN TẠM VẮNG -->
    <div class="card" style="margin-bottom: 24px; border-left: 4px solid #dc2626;">
        <div class="card-title">
            <span>Danh sách Cảnh báo Sinh viên vắng mặt quá 30 ngày (Cụm <?= htmlspecialchars($maCum ?? 'AG') ?>)</span>
            <span class="badge badge-danger">1 trường hợp cần xác minh</span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>MSSV</th>
                        <th>Họ và tên</th>
                        <th>Phòng / Giường</th>
                        <th>Trường ĐH</th>
                        <th>Lần quét thẻ cuối</th>
                        <th>Số ngày vắng</th>
                        <th>Tình trạng đơn tạm vắng</th>
                        <th style="text-align: right;">Biện pháp xử lý</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="background: #fef2f2;">
                        <td><b>2311999</b></td>
                        <td><b>Phạm Quốc Trọng</b></td>
                        <td>Phòng A01.105 (G2)</td>
                        <td>ĐH Bách Khoa</td>
                        <td>25/09/2026 18:30 (Cổng A01)</td>
                        <td><b style="color: #b91c1c;">35 ngày</b></td>
                        <td>
                            <span class="badge badge-danger">✕ Không có đơn</span>
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã gửi thông báo yêu cầu Trưởng phòng xác nhận sự có mặt của sinh viên và gọi điện phụ huynh!')">
                                📞 Liên hệ phụ huynh
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" style="border-radius: 6px;" onclick="alert('Lập biên bản vắng mặt kéo dài không phép!')">
                                📝 Lập biên bản
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 2: NHẬT KÝ QUÉT THẺ RA VÀO CỔNG (READ-ONLY) -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Nhật ký Quét thẻ Ra - Vào Cổng KTX </span>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã lượt</th>
                        <th>Sinh viên</th>
                        <th>MSSV</th>
                        <th>Cổng kiểm soát</th>
                        <th>Hướng di chuyển</th>
                        <th>Thời gian quét</th>
                        <th>Trạng thái hợp lệ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#89124</td>
                        <td><b>Nguyễn Văn An</b></td>
                        <td>2110001</td>
                        <td>Cổng chính A01</td>
                        <td><span class="badge badge-success">⬇️ Vào KTX</span></td>
                        <td>29/10/2026 21:15:30</td>
                        <td><span style="color: #15803d; font-size: 0.8rem; font-weight: 600;">✓ Hợp lệ</span></td>
                    </tr>
                    <tr>
                        <td>#89123</td>
                        <td><b>Trần Thị Mai Chúc</b></td>
                        <td>2212345</td>
                        <td>Cổng phụ H01</td>
                        <td><span class="badge badge-info">⬆️ Ra ngoài</span></td>
                        <td>29/10/2026 20:45:12</td>
                        <td><span style="color: #15803d; font-size: 0.8rem; font-weight: 600;">✓ Hợp lệ</span></td>
                    </tr>
                    <tr>
                        <td>#89120</td>
                        <td><b>Lê Văn Hùng</b></td>
                        <td>2311200</td>
                        <td>Cổng chính A01</td>
                        <td><span class="badge badge-success">⬇️ Vào KTX</span></td>
                        <td>29/10/2026 19:10:05</td>
                        <td><span style="color: #15803d; font-size: 0.8rem; font-weight: 600;">✓ Hợp lệ</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- KHỐI 3: ĐÁNH GIÁ ĐIỂM RÈN LUYỆN NỘI TRÚ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Quản lý Điểm Rèn luyện Nội trú & Kỷ luật (Học kỳ 1 / 2026-2027)</span>
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Đã xuất báo cáo điểm rèn luyện toàn Cụm nhà ra file Excel!')">
                    📥 Xuất báo cáo gửi trường
                </button>
            </div>
        </div>

        <!-- FORM MÔ PHỎNG GHI NHẬN VI PHẠM HOẶC KHEN THƯỞNG -->
        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu ghi nhận rèn luyện và cộng/trừ điểm thành công!');" style="background: #f8fafc; padding: 14px; border-radius: 6px; margin-bottom: 16px; border: 1px solid var(--border);">
            <div style="font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 8px;">
                ➕ Ghi nhận Vi phạm Nội quy / Hoạt động phong trào:
            </div>
            <div class="grid grid-cols-4" style="gap: 12px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 4px;">Sinh viên:</label>
                    <select class="btn-outline" style="width: 100%; padding: 7px; border-radius: 6px; font-size: 0.85rem;" required>
                        <option value="2110001">Nguyễn Văn An (2110001)</option>
                        <option value="2212345">Trần Thị Mai Chúc (2212345)</option>
                        <option value="2311200">Lê Văn Hùng (2311200)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 4px;">Loại ghi nhận:</label>
                    <select class="btn-outline" style="width: 100%; padding: 7px; border-radius: 6px; font-size: 0.85rem;" required>
                        <option value="Khen thưởng">Khen thưởng / Hoạt động</option>
                        <option value="Vi phạm nhẹ">Vi phạm nhẹ (-5 đến -10đ)</option>
                        <option value="Vi phạm nghiêm trọng">Vi phạm nghiêm trọng (-25đ)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 4px;">Điểm thay đổi (+/-):</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 7px; border-radius: 6px; font-size: 0.85rem;" value="10" placeholder="+10 hoặc -15" required>
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" class="btn btn-primary btn-block" style="border-radius: 6px; height: 36px;">
                        💾 Ghi nhận điểm
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>MSSV</th>
                        <th>Họ và tên</th>
                        <th>Trường ĐH</th>
                        <th>Điểm gốc</th>
                        <th>Điểm cộng/trừ</th>
                        <th>Tổng điểm cuối kỳ</th>
                        <th>Xếp loại</th>
                        <th>Tình trạng gửi trường</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>2110001</b></td>
                        <td><b>Nguyễn Văn An</b></td>
                        <td>ĐH Bách Khoa</td>
                        <td>100 đ</td>
                        <td><b style="color: #15803d;">+10 đ</b> (Vệ sinh chung)</td>
                        <td><b style="font-size: 1rem; color: #1e40af;">100 đ</b></td>
                        <td><span class="badge badge-success">Xuất sắc</span></td>
                        <td><span class="badge badge-warning">Chưa gửi</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Xem chi tiết các lỗi và minh chứng phong trào của sinh viên')">
                                🔍 Lịch sử
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>2212345</b></td>
                        <td><b>Trần Thị Mai Chúc</b></td>
                        <td>ĐH KHTN</td>
                        <td>100 đ</td>
                        <td><b style="color: #b91c1c;">-10 đ</b> (Nấu ăn sai quy định)</td>
                        <td><b style="font-size: 1rem; color: #1e40af;">90 đ</b></td>
                        <td><span class="badge badge-info">Tốt</span></td>
                        <td><span class="badge badge-warning">Chưa gửi</span></td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px;" onclick="alert('Xem chi tiết')">
                                🔍 Lịch sử
                            </button>
                        </td>
                    </tr>
                    <!-- BẢN ĐÃ CHỐT VÀ GỬI VỀ TRƯỜNG: ĐÃ KHÓA SỬA THEO PDF -->
                    <tr style="background: #f1f5f9;">
                        <td><b>2010888</b></td>
                        <td><b>Lê Minh Hoàng</b></td>
                        <td>ĐH Công nghệ Thông tin</td>
                        <td>100 đ</td>
                        <td>0 đ</td>
                        <td><b>100 đ</b></td>
                        <td><span class="badge badge-success">Xuất sắc</span></td>
                        <td><span class="badge badge-success">✓ Đã gửi trường (DaGuiVeTruong = 1)</span></td>
                        <td style="text-align: right;">
                            <span style="font-size: 0.8rem; color: var(--text-muted); font-style: italic;">🔒 Đã khóa sửa</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "An ninh & Rèn luyện - BQL KTX";
require dirname(__DIR__) . '/layouts/main.php';
?>