<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Loại phòng & Đơn giá</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Quản lý Loại phòng & Đơn giá chuẩn</h1>
                <p class="page-subtitle">
                    <strong>Chức năng file:</strong> CRUD danh mục các loại phòng KTX, thiết lập biểu giá thuê chuẩn và phụ thu dịch vụ máy lạnh toàn hệ thống.
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
            <a href="<?= url('admin/room-types') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">💰 Loại phòng & Giá</a>
            <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🎓 Trường Đại học</a>
        </div>
    </div>

    <!-- FORM SƯỜN MẪU: THÊM / CẬP NHẬT LOẠI PHÒNG -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu: Thêm mới hoặc Điều chỉnh Đơn giá Loại phòng</span>
            <span class="badge badge-info">Bảng: LoaiPhong</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu danh mục loại phòng và cập nhật đơn giá chuẩn!');">
            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Mã loại phòng</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: LP4, LP6, LP8, LP4ML..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Tên loại phòng</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Phòng tiêu chuẩn 4 giường" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Số giường tối đa (Sức chứa chuẩn)</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 4" min="1" max="12" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Đơn giá lưu trú chuẩn / tháng (VNĐ)</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 650000" min="0" step="10000" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Đơn giá dịch vụ gia tăng / tháng (Máy lạnh)</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Nếu không có thì nhập 0 (VD: 300000)" min="0" step="10000">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Dành cho sinh viên khuyết tật</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="0">Phòng thông thường</option>
                        <option value="1">Ưu tiên sinh viên khuyết tật (Tầng trệt)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 10px 20px;">
                    💾 Lưu biểu giá loại phòng
                </button>
                <button type="reset" class="btn btn-outline" style="border-radius: 6px; padding: 10px 20px;">
                    Nhập lại
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH MỤC LOẠI PHÒNG HIỆN CÓ -->
    <div class="card">
        <div class="card-title">
            <span>Danh mục các loại phòng và đơn giá đang áp dụng</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã loại</th>
                        <th>Tên loại phòng</th>
                        <th>Sức chứa</th>
                        <th>Đơn giá chuẩn / tháng</th>
                        <th>Phụ thu dịch vụ</th>
                        <th style="text-align: right;">Thao tác CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>LP4</b></td>
                        <td>Phòng tiêu chuẩn 4 giường</td>
                        <td>4 giường</td>
                        <td><b>650.000 đ</b></td>
                        <td>0 đ</td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa LP4')">✏️ Sửa giá</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Xóa')">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>LP4ML</b></td>
                        <td>Phòng 4 giường có máy lạnh</td>
                        <td>4 giường</td>
                        <td><b>650.000 đ</b></td>
                        <td><span class="badge badge-info">+300.000 đ (Máy lạnh)</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa LP4ML')">✏️ Sửa giá</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Xóa')">🗑️</button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>LP8</b></td>
                        <td>Phòng tiêu chuẩn 8 giường</td>
                        <td>8 giường</td>
                        <td><b>220.000 đ</b></td>
                        <td>0 đ</td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa LP8')">✏️ Sửa giá</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Xóa')">🗑️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản lý Loại phòng & Đơn giá - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
