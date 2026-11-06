<?php
ob_start();
$staff = $staff ?? [];
$buildings = $buildings ?? [];
?>

<div class="container">
    <!-- Breadcrumb điều hướng -->
    <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 14px;">
        <a href="<?= url('/') ?>" style="color: var(--text-muted); text-decoration: none;"><i class="fa-solid fa-house"></i> Trang chủ</a>
        <span>/</span>
        <a href="<?= url('staff/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">BQL Cụm</a>
        <span>/</span>
        <span style="color: var(--text-primary); font-weight: 600;">Hồ sơ cán bộ</span>
    </div>

    <!-- Thông báo Alert -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Tiêu đề trang -->
    <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="page-title"><i style="color: var(--primary); margin-right: 8px;"></i> Hồ sơ cán bộ</h1>
            <p class="text-muted" style="margin-top: 4px; font-size: 0.9rem;">Thông tin định danh nhân sự, thẩm quyền vận hành và an toàn tài khoản.</p>
        </div>
    </div>

    <?php if (empty($staff)): ?>
        <div class="card" style="text-align: center; padding: 40px 20px;">
            <i class="fa-solid fa-user-slash" style="font-size: 48px; color: #cbd5e1; margin-bottom: 14px;"></i>
            <h3 style="font-size: 1.15rem; color: var(--text-primary); margin-bottom: 8px;">Không tìm thấy hồ sơ cán bộ</h3>
            <p class="text-muted">Tài khoản hiện tại chưa được liên kết với hồ sơ cán bộ trong cơ sở dữ liệu hệ thống.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2" style="margin-bottom: 24px; gap: 24px;">
            
            <!-- CỘT TRÁI: THÔNG TIN CÁ NHÂN & PHÂN QUYỀN VẬN HÀNH -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- Card 1: Thông tin nhân sự & Định danh -->
                <div class="card">
                    <div class="card-title">
                        <span><i style="margin-right: 6px; color: var(--primary);"></i> Thông tin cá nhân & Nhân sự</span>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-hashtag" style="margin-right: 6px;"></i> Mã cán bộ</td>
                            <td class="info-value">
                                <b style="font-size: 1rem; color: var(--primary); letter-spacing: 0.02em;"><?= htmlspecialchars($staff['MaNhanVien'] ?? 'N/A') ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-signature" style="margin-right: 6px;"></i> Họ và tên</td>
                            <td class="info-value"><b><?= htmlspecialchars($staff['HoVaTen'] ?? 'Chưa cập nhật') ?></b></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-briefcase" style="margin-right: 6px;"></i> Chức vụ đảm nhiệm</td>
                            <td class="info-value"><span style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($staff['ChucVu'] ?? 'Cán bộ KTX') ?></span></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-venus-mars" style="margin-right: 6px;"></i> Giới tính</td>
                            <td class="info-value"><?= htmlspecialchars($staff['GioiTinh'] ?? 'Chưa rõ') ?></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-user-gear" style="margin-right: 6px;"></i> Tên đăng nhập</td>
                            <td class="info-value"><code><?= htmlspecialchars($staff['TenDangNhap'] ?? 'N/A') ?></code></td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-toggle-on" style="margin-right: 6px;"></i> Trạng thái tài khoản</td>
                            <td class="info-value">
                                <?php if (!empty($staff['DangHoatDong'])): ?>
                                    <span class="badge badge-success"><i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đang hoạt động</span>
                                <?php else: ?>
                                    <span class="badge badge-danger"><i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Đã tạm khóa</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-regular fa-calendar-plus" style="margin-right: 6px;"></i> Ngày tạo tài khoản</td>
                            <td class="info-value">
                                <?= !empty($staff['NgayTao']) ? date('d/m/Y H:i', strtotime($staff['NgayTao'])) : 'Chưa ghi nhận' ?>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Card 2: Phân quyền & Phạm vi quản lý -->
                <div class="card">
                    <div class="card-title">
                        <span><i style="margin-right: 6px; color: var(--primary);"></i> Phạm vi phân quyền & Khu vực</span>
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-shield" style="margin-right: 6px;"></i> Vai trò hệ thống</td>
                            <td class="info-value">
                                <span class="badge badge-info" style="font-weight: 700;"><?= htmlspecialchars($staff['TenVaiTro'] ?? $staff['MaVaiTro']) ?></span>
                            </td>
                        </tr>
                        <?php if (!empty($staff['MoTaVaiTro'])): ?>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Thẩm quyền chính</td>
                            <td class="info-value" style="font-size: 0.85rem; color: var(--text-secondary); line-height: 1.5;">
                                <?= htmlspecialchars($staff['MoTaVaiTro']) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-map-location-dot" style="margin-right: 6px;"></i> Khu ký túc xá</td>
                            <td class="info-value">
                                <b><?= htmlspecialchars($staff['TenKhu'] ?? 'Toàn hệ thống (Khu A & Khu B)') ?></b>
                            </td>
                        </tr>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-building-circle-check" style="margin-right: 6px;"></i> Cụm nhà quản lý</td>
                            <td class="info-value">
                                <?php if (!empty($staff['TenCum'])): ?>
                                    <b style="color: var(--primary);"><?= htmlspecialchars($staff['TenCum']) ?></b>
                                    <?php if (!empty($staff['ViTriVanPhong'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 3px;">
                                            <i class="fa-solid fa-location-dot" style="font-size: 11px;"></i> Văn phòng: <?= htmlspecialchars($staff['ViTriVanPhong']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($staff['SoDienThoaiCum'])): ?>
                                        <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 2px;">
                                            <i class="fa-solid fa-phone" style="font-size: 11px;"></i> Hotline Cụm: <b><?= htmlspecialchars($staff['SoDienThoaiCum']) ?></b>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Quản lý chung cấp Ban Giám đốc / Phòng ban chuyên môn</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if (!empty($buildings)): ?>
                        <tr>
                            <td class="info-label"><i class="fa-solid fa-city" style="margin-right: 6px;"></i> Tòa nhà phụ trách</td>
                            <td class="info-value">
                                <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                    <?php foreach ($buildings as $b): ?>
                                        <span class="badge badge-info" style="font-size: 0.78rem;">
                                            <?= htmlspecialchars($b['TenToaNha']) ?> (<?= htmlspecialchars($b['GioiTinhQuyDinh']) ?> · <?= (int)$b['TongSoTang'] ?>T)
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>

            </div>

            <!-- CỘT PHẢI: THÔNG TIN LIÊN LẠC & ĐỔI MẬT KHẨU -->
            <div style="display: flex; flex-direction: column; gap: 24px;">
                
                <!-- Card 3: Cập nhật thông tin liên lạc -->
                <div class="card">
                    <div class="card-title">
                        <span><i style="margin-right: 6px; color: var(--primary);"></i> Thông tin liên hệ công vụ</span>
                    </div>

                    <form method="POST" action="<?= url('staff/profile/update') ?>" style="display: flex; flex-direction: column; gap: 14px;">
                        <?= csrf_field() ?>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">
                                <i class="fa-solid fa-phone" style="margin-right: 4px; color: var(--text-muted);"></i> Số điện thoại liên hệ
                            </label>
                            <input type="text" name="SoDienThoai" class="form-control" 
                                   value="<?= htmlspecialchars($staff['SoDienThoai'] ?? '') ?>" 
                                   placeholder="Ví dụ: 0909123456" required
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;">
                            <small class="text-muted" style="display: block; margin-top: 4px; font-size: 0.78rem;">Dùng để liên hệ nội bộ và nhận thông báo khẩn từ BQL KTX.</small>
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">
                                <i class="fa-solid fa-envelope" style="margin-right: 4px; color: var(--text-muted);"></i> Email công vụ / Cá nhân
                            </label>
                            <input type="email" name="Email" class="form-control" 
                                   value="<?= htmlspecialchars($staff['Email'] ?? '') ?>" 
                                   placeholder="Ví dụ: canbo@ktxhcm.edu.vn" required
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;">
                            <small class="text-muted" style="display: block; margin-top: 4px; font-size: 0.78rem;">Địa chỉ nhận báo cáo nghiệp vụ và phục vụ khôi phục tài khoản.</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 6px; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; align-self: flex-start;">
                            <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>

                <!-- Card 4: Đổi mật khẩu tài khoản -->
                <div class="card">
                    <div class="card-title">
                        <span><i style="margin-right: 6px; color: var(--primary);"></i> Đổi mật khẩu tài khoản</span>
                    </div>

                    <form method="POST" action="<?= url('staff/profile/change-password') ?>" style="display: flex; flex-direction: column; gap: 14px;">
                        <?= csrf_field() ?>
                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">Mật khẩu hiện tại</label>
                            <input type="password" name="old_password" class="form-control" placeholder="Nhập mật khẩu đang dùng" required 
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">Mật khẩu mới (tối thiểu 6 ký tự)</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Nhập mật khẩu mới an toàn" required minlength="6" 
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;">
                        </div>

                        <div>
                            <label style="display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-primary); margin-bottom: 5px;">Xác nhận mật khẩu mới</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Nhập lại mật khẩu mới" required minlength="6" 
                                   style="width: 100%; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; font-size: 0.9rem;">
                        </div>

                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px 12px; font-size: 0.8rem; color: #64748b; line-height: 1.5;">
                            <i style="color: #0284c7; margin-right: 4px;"></i>
                            Mật khẩu cán bộ bảo vệ quyền quản trị dữ liệu sinh viên. Vui lòng kết hợp chữ hoa, chữ thường, số và không chia sẻ cho người khác.
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 4px; padding: 8px 18px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; align-self: flex-start;">
                            <i class="fa-solid fa-lock" style="margin-right: 6px;"></i> Cập nhật mật khẩu
                        </button>
                    </form>
                </div>

            </div>

        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$title = 'Hồ sơ cán bộ - KTX ĐHQG-HCM';
require dirname(__DIR__) . '/layouts/main.php';
?>

