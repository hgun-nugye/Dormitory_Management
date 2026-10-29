<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Quản lý & Phân quyền cán bộ</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Quản lý Tài khoản & Phân quyền Cán bộ</h1>
                <p class="page-subtitle">
                    <strong>Chức năng file:</strong> CRUD tài khoản cán bộ BQL, phân công vai trò (Trưởng khu / Trưởng cụm) và gán phạm vi quản lý.
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
            <a href="<?= url('admin/users') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">👥 Cán bộ & Phân quyền</a>
            <a href="<?= url('admin/rooms') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🏢 Phòng & Giường</a>
            <a href="<?= url('admin/periods') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">📅 Đợt đăng ký 72h</a>
            <a href="<?= url('admin/room-types') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">💰 Loại phòng & Giá</a>
            <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🎓 Trường Đại học</a>
        </div>
    </div>

    <!-- FORM SƯỜN MẪU: THÊM / CẬP NHẬT TÀI KHOẢN CÁN BỘ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu: Thêm mới hoặc Điều chỉnh phân quyền Cán bộ</span>
            <span class="badge badge-info">Bảng: TaiKhoan + NhanVien + VaiTro</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu thông tin cán bộ và phân quyền!');">
            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Tên đăng nhập (Mã nhân viên)</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: NV003, TK002..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Mật khẩu khởi tạo</label>
                    <input type="password" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Nhập mật khẩu ban đầu" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Họ và tên cán bộ</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Lê Hoàng Nam" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Số điện thoại liên hệ</label>
                    <input type="tel" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 0908 123 456">
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Vai trò hệ thống</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="TRUONG_CUM">Trưởng cụm nhà (TRUONG_CUM)</option>
                        <option value="TRUONG_KHU">Trưởng khu KTX (TRUONG_KHU)</option>
                        <option value="NV_THUONG">Nhân viên kỹ thuật / hành chính (NV_THUONG)</option>
                        <option value="ADMIN">Quản trị viên hệ thống (ADMIN)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Phạm vi được giao quản lý (Scope)</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="AG">Cụm nhà A.G (Khu A)</option>
                        <option value="AH">Cụm nhà A.H (Khu A)</option>
                        <option value="BC">Cụm nhà B.C (Khu B)</option>
                        <option value="KHU_A">Toàn bộ Khu A (Dành cho Trưởng khu)</option>
                        <option value="KHU_B">Toàn bộ Khu B (Dành cho Trưởng khu)</option>
                        <option value="ALL">Toàn hệ thống (Dành cho Admin)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 10px 20px;">
                    💾 Lưu tài khoản & Cấp quyền
                </button>
                <button type="reset" class="btn btn-outline" style="border-radius: 6px; padding: 10px 20px;">
                    Nhập lại
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH CÁN BỘ HIỆN TẠI -->
    <div class="card">
        <div class="card-title">
            <span>Danh sách tài khoản cán bộ đang quản lý trong hệ thống</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Tên đăng nhập</th>
                        <th>Họ và tên</th>
                        <th>Vai trò</th>
                        <th>Phạm vi phụ trách</th>
                        <th>Số điện thoại</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>NV001</b></td>
                        <td>Trần Văn Minh</td>
                        <td><span class="badge badge-info">Trưởng cụm nhà</span></td>
                        <td><b>Cụm nhà A.G (Khu A)</b></td>
                        <td>0903123456</td>
                        <td><span class="badge badge-success">Hoạt động</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Mở form sửa cán bộ NV001')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Khóa tài khoản')">🔒 Khóa</button>
                        </td>
                    </tr>
                    <tr>
                        <td><b>TK001</b></td>
                        <td>Phạm Quốc Hùng</td>
                        <td><span class="badge badge-info">Trưởng khu KTX</span></td>
                        <td><b>Toàn bộ Khu A</b></td>
                        <td>0977333444</td>
                        <td><span class="badge badge-success">Hoạt động</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Mở form sửa cán bộ TK001')">✏️ Sửa</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Khóa tài khoản')">🔒 Khóa</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Quản lý Cán bộ & Phân quyền - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
