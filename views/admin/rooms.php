<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Quản lý phòng ở & Giường</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Quản lý Cơ sở vật chất & Phòng ở</h1>
                <p class="page-subtitle">
                    <strong>Chức năng file:</strong> CRUD phòng ở, tự động tạo giường theo sức chứa, phân loại phòng tiêu chuẩn/máy lạnh và gán tòa nhà.
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
            <a href="<?= url('admin/rooms') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">🏢 Phòng & Giường</a>
            <a href="<?= url('admin/periods') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📅 Đợt đăng ký 72h</a>
            <a href="<?= url('admin/room-types') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">💰 Loại phòng & Giá</a>
            <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🎓 Trường Đại học</a>
        </div>
    </div>

    <!-- FORM SƯỜN MẪU: THÊM / CẬP NHẬT PHÒNG Ở -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu: Thêm mới hoặc Cập nhật Phòng ở</span>
            <span class="badge badge-info">Bảng: Phong + Giuong + ToaNha</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu phòng và tự động sinh danh sách giường!');">
            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Mã phòng</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: A01.103, H01.205..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Số phòng & Tầng</label>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" class="btn-outline" style="flex: 1; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Số phòng (VD: 103)" required>
                        <input type="number" class="btn-outline" style="width: 120px; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Tầng (VD: 1)" min="1" required>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Tòa nhà & Cụm trực thuộc</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="A01">Tòa A01 (Cụm A.G - Khu A)</option>
                        <option value="H01">Tòa H01 (Cụm A.H - Khu A)</option>
                        <option value="BA1">Tòa BA1 (Cụm B.A - Khu B)</option>
                        <option value="C06">Tòa C06 (Cụm B.C - Khu B)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Loại phòng & Sức chứa</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="LP4">LP4 - Phòng tiêu chuẩn 4 giường (650.000 đ/tháng)</option>
                        <option value="LP4ML">LP4ML - Phòng 4 giường có máy lạnh (950.000 đ/tháng)</option>
                        <option value="LP6">LP6 - Phòng tiêu chuẩn 6 giường (350.000 đ/tháng)</option>
                        <option value="LP8">LP8 - Phòng tiêu chuẩn 8 giường (220.000 đ/tháng)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 10px 20px;">
                    💾 Lưu phòng & Sinh giường
                </button>
                <button type="reset" class="btn btn-outline" style="border-radius: 6px; padding: 10px 20px;">
                    Nhập lại
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH PHÒNG HIỆN CÓ -->
    <div class="card">
        <div class="card-title">
            <span>Danh sách phòng ở toàn hệ thống</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã phòng</th>
                        <th>Tòa nhà</th>
                        <th>Tầng</th>
                        <th>Loại phòng</th>
                        <th>Sức chứa</th>
                        <th>Đang ở</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>A01.101</b></td>
                        <td>Tòa A01 (Cụm A.G)</td>
                        <td>Tầng 1</td>
                        <td>Phòng tiêu chuẩn 4 giường</td>
                        <td>4 giường</td>
                        <td>2 bạn</td>
                        <td><span class="badge badge-success">Đang sử dụng</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa phòng A01.101')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Xóa phòng')">🗑️ Xóa</button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>H01.301</b></td>
                        <td>Tòa H01 (Cụm A.H)</td>
                        <td>Tầng 3</td>
                        <td>Phòng 4 giường có máy lạnh</td>
                        <td>4 giường</td>
                        <td>1 bạn</td>
                        <td><span class="badge badge-warning">Còn chỗ</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Sửa phòng H01.301')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Xóa phòng')">🗑️ Xóa</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản lý Phòng ở - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
