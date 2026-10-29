<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Trường Đại học liên kết</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Quản lý Trường Đại học liên kết</h1>
                <p class="page-subtitle">
                    <strong>Chức năng file:</strong> CRUD danh mục các trường đại học thành viên ĐHQG-HCM và các trường đối tác gửi sinh viên lưu trú nội trú.
                </p>
            </div>
            <div>
                <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về trang Quản trị trung tâm
                </a>
            </div>
        </div>

        <!-- Thanh chuyển nhanh các phân hệ -->
        <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 14px;">
            <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📊 Dashboard</a>
            <a href="<?= url('admin/users') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">👥 Cán bộ & Phân quyền</a>
            <a href="<?= url('admin/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🏢 Phòng & Giường</a>
            <a href="<?= url('admin/periods') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📅 Đợt đăng ký 72h</a>
            <a href="<?= url('admin/room-types') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">💰 Loại phòng & Giá</a>
            <a href="<?= url('admin/universities') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">🎓 Trường Đại học</a>
        </div>
    </div>

    <!-- FORM SƯỜN MẪU: THÊM / CẬP NHẬT TRƯỜNG ĐẠI HỌC -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu: Thêm mới hoặc Cập nhật Trường Đại học</span>
            <span class="badge badge-info">Bảng: TruongDaiHoc</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu thông tin trường đại học!');">
            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Mã trường đại học</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: BK, KHTN, UIT, USSH, UEL, IU..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Tên đầy đủ trường đại học</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Trường Đại học Bách khoa - ĐHQG-HCM" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Thời gian đào tạo chuẩn (Năm)</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 4.0 hoặc 4.5" step="0.5" value="4.0" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Phân loại đơn vị</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="1">Trường thành viên ĐHQG-HCM (Ưu tiên)</option>
                        <option value="0">Trường đại học ngoài ĐHQG-HCM</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 10px 20px;">
                    💾 Lưu thông tin trường đại học
                </button>
                <button type="reset" class="btn btn-outline" style="border-radius: 6px; padding: 10px 20px;">
                    Nhập lại
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH MỤC TRƯỜNG ĐẠI HỌC -->
    <div class="card">
        <div class="card-title">
            <span>Danh sách các trường đại học đang liên kết lưu trú</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã trường</th>
                        <th>Tên trường đại học</th>
                        <th>Phân loại</th>
                        <th>Thời gian chuẩn</th>
                        <th>Trạng thái hợp tác</th>
                        <th style="text-align: right;">Thao tác CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>BK</b></td>
                        <td>Trường Đại học Bách khoa - ĐHQG-HCM</td>
                        <td><span class="badge badge-success">Thành viên ĐHQG-HCM</span></td>
                        <td>4.5 năm</td>
                        <td><span class="badge badge-info">Đang hợp tác</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa trường BK')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Ngưng hợp tác')">Tạm dừng</button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>UIT</b></td>
                        <td>Trường Đại học Công nghệ Thông tin - ĐHQG-HCM</td>
                        <td><span class="badge badge-success">Thành viên ĐHQG-HCM</span></td>
                        <td>4.0 năm</td>
                        <td><span class="badge badge-info">Đang hợp tác</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa trường UIT')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Ngưng hợp tác')">Tạm dừng</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản lý Trường Đại học - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
