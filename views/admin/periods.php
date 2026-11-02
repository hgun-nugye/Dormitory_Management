<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Cấu hình đợt đăng ký 72h</span>
        </div>

        <div class="page-header" style="align-items: center;">
            <div>
                <h1 class="page-title">Cấu hình Đợt Đăng ký & Giữ chỗ 72h</h1>
                <p class="page-subtitle">
                    <strong>Chức năng file:</strong> Thiết lập đợt tuyển sinh KTX, cài đặt số giờ khóa giữ chỗ tự động (72h) và thời gian mở/đóng cổng nộp hồ sơ.
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
            <a href="<?= url('admin/periods') ?>" class="btn btn-primary btn-sm" style="border-radius: 6px;">📅 Đợt đăng ký 72h</a>
            <a href="<?= url('admin/room-types') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">💰 Loại phòng & Giá</a>
            <a href="<?= url('admin/universities') ?>" class="btn btn-outline btn-sm" style="border-radius: 6px;">🎓 Trường Đại học</a>
        </div>
    </div>

    <!-- FORM SƯỜN MẪU: THÊM / CẬP NHẬT ĐỢT ĐĂNG KÝ -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title">
            <span>Biểu mẫu: Thiết lập Đợt đăng ký nội trú & Quy định giữ chỗ</span>
            <span class="badge badge-info">Bảng: DotDangKy</span>
        </div>

        <form action="#" method="POST" onsubmit="event.preventDefault(); alert('Mô phỏng: Đã lưu cấu hình đợt đăng ký tuyển sinh KTX!');">
            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Tên đợt đăng ký</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Đăng ký nội trú năm học 2026-2027" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Năm học áp dụng</label>
                    <input type="text" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 2026-2027" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Thời gian mở cổng tiếp nhận</label>
                    <input type="datetime-local" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Thời gian đóng cổng tiếp nhận</label>
                    <input type="datetime-local" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Hạn định giữ chỗ tự động (Giờ)</label>
                    <input type="number" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" value="72" min="12" max="168" required>
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Mặc định: 72 giờ (Quá hạn chưa đóng tiền sẽ tự động thu hồi giường).</small>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Trạng thái đợt</label>
                    <select class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="OPEN">Đang mở tiếp nhận</option>
                        <option value="UPCOMING">Sắp diễn ra</option>
                        <option value="CLOSED">Đã đóng cổng</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 10px 20px;">
                    💾 Lưu cấu hình đợt đăng ký
                </button>
                <button type="reset" class="btn btn-outline" style="border-radius: 6px; padding: 10px 20px;">
                    Nhập lại
                </button>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH ĐỢT ĐĂNG KÝ -->
    <div class="card">
        <div class="card-title">
            <span>Danh sách các đợt đăng ký đã cấu hình</span>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Mã đợt</th>
                        <th>Tên đợt</th>
                        <th>Năm học</th>
                        <th>Thời gian mở - đóng</th>
                        <th>Hạn giữ chỗ</th>
                        <th>Trạng thái</th>
                        <th style="text-align: right;">Thao tác CRUD</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><b>Đợt #1</b></td>
                        <td>Đăng ký nội trú năm học 2026-2027</td>
                        <td>2026-2027</td>
                        <td>01/06/2026 &rarr; 31/10/2026</td>
                        <td><span class="badge badge-info">72 giờ</span></td>
                        <td><span class="badge badge-success">Đang mở</span></td>
                        <td style="text-align: right;">
                            <button class="btn btn-outline btn-sm" onclick="alert('Chỉnh sửa đợt')">⚙️ Cấu hình</button>
                            <button class="btn btn-outline btn-sm" style="color:#b91c1c;" onclick="alert('Đóng cổng')">⏹️ Đóng</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Cấu hình Đợt Đăng ký 72h - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>
