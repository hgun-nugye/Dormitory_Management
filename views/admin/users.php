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

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 class="page-title">Quản lý Tài khoản</h1>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary" id="btnToggleAddUser" onclick="toggleUserForm()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i> Thêm cán bộ mới
                </button>
                <button type="button" class="btn btn-outline" onclick="openImportUserModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách cán bộ từ file CSV">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px; color: #2563eb;"></i> Nhập file (Import)
                </button>
                <button type="button" class="btn btn-outline" id="btnExportUsers" onclick="exportFilteredUsers()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách cán bộ theo kết quả lọc hiện tại ra Excel/CSV">
                    <i class="fa-solid fa-file-excel" style="margin-right: 6px; color: #16a34a;"></i> <span id="btnExportText">Xuất Excel (CSV)</span>
                </button>
                <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px; text-decoration: none;">
                    &larr; Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <b>Thành công:</b> <?= $flashSuccess ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <b>Cảnh báo:</b> <?= $flashWarning ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <!-- BIỂU MẪU CRUD: THÊM / CẬP NHẬT TÀI KHOẢN CÁN BỘ (ẨN MẶC ĐỊNH, CHỈ HIỆN KHI CLICK BUTTON HOẶC SỬA) -->
    <div class="card" id="userFormCard" style="<?= !empty($flashError) ? 'display: block;' : 'display: none;' ?> margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="formTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-plus" style="color: var(--primary); margin-right: 8px;"></i>
                <span id="formTitleText">Thêm mới hoặc Điều chỉnh phân quyền Cán bộ</span>
            </h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="hideUserForm()" style="border-radius: 6px; padding: 4px 10px; color: var(--text-muted);" title="Đóng biểu mẫu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="userActionForm" action="<?= url('admin/users/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" id="editMaTaiKhoan" name="maTaiKhoan" value="">

            <!-- Datalist gợi ý họ và tên cán bộ khi nhập tay -->
            <datalist id="staffSuggestions">
                <?php foreach ($allStaff as $st): ?>
                    <option value="<?= htmlspecialchars($st['HoVaTen']) ?>">
                        <?= htmlspecialchars($st['HoVaTen']) ?> (SĐT: <?= htmlspecialchars($st['SoDienThoai'] ?? '') ?> &bull; <?= htmlspecialchars($st['ChucVu'] ?? '') ?>)
                    </option>
                <?php endforeach; ?>
            </datalist>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Họ và tên cán bộ <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" list="staffSuggestions" id="hoVaTen" name="hoVaTen" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Gõ tên cán bộ (VD: Trần Văn Minh, Lê Hoàng Nam...)" required autocomplete="off">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Số điện thoại liên hệ
                    </label>
                    <input type="tel" id="soDienThoai" name="soDienThoai" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="SĐT sẽ tự động hiển thị sau khi nhập/chọn họ tên">
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Tên đăng nhập (Mã nhân viên) <span style="color:#dc2626;">*</span>
                        <span id="usernameLockNotice" style="display: none; font-size: 0.775rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                            <i class="fa-solid fa-lock" style="margin-right: 4px;"></i>
                        </span>
                    </label>
                    <input type="text" id="tenDangNhap" name="tenDangNhap" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: NV003, TK002, admin..." required>
                </div>
                <div>
                    <!-- CHẾ ĐỘ THÊM MỚI: NHẬP MẬT KHẨU KHỞI TẠO -->
                    <div id="createPasswordBox">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                            Mật khẩu khởi tạo <span style="color:#dc2626;">*</span>
                        </label>
                        <?= \App\Core\Auth::passwordInput('matKhau', 'matKhau', 'Nhập mật khẩu khởi tạo cho cán bộ', true, 'btn-outline') ?>
                        <div id="passwordHelp" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            * Bắt buộc khi tạo mới. Gợi ý chuẩn: <code>Demo@123</code>.
                        </div>
                    </div>

                    <!-- CHẾ ĐỘ SỬA: KHÔNG CHO XEM MẬT KHẨU, CHỈ CHO RESET VỀ Demo@123 -->
                    <div id="editPasswordBox" style="display: none;">
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: var(--text-secondary);">
                            Mật khẩu tài khoản
                        </label>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="btnQuickResetPassword" style="border-radius: 6px; font-size: 0.8rem; color: #b45309; border-color: #fde68a; background: #fffbeb;" onclick="confirmQuickResetPassword()">
                                    <i class="fa-solid fa-key" style="margin-right: 4px;"></i> Reset ngay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">Email công vụ / liên hệ</label>
                    <input type="email" id="email" name="email" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: canbo@qlktx.vn">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Vai trò hệ thống <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="maVaiTro" name="maVaiTro" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                        <option value="">-- Chọn vai trò phân quyền --</option>
                        <?php if (!empty($vaitroList)): ?>
                            <?php foreach ($vaitroList as $vt): ?>
                                <option value="<?= htmlspecialchars($vt['MaVaiTro']) ?>">
                                    <?= htmlspecialchars($vt['TenVaiTro']) ?> (<?= htmlspecialchars($vt['MaVaiTro']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="TRUONG_TOA">Trưởng tòa nhà (TRUONG_TOA)</option>
                            <option value="TRUONG_CUM">Trưởng cụm nhà (TRUONG_CUM)</option>
                            <option value="TRUONG_KHU">Trưởng khu KTX (TRUONG_KHU)</option>
                            <option value="NV_THUONG">Nhân viên kỹ thuật / hành chính (NV_THUONG)</option>
                            <option value="NV_QUAN_LY_KTX">Nhân viên quản lý chung KTX (NV_QUAN_LY_KTX)</option>
                            <option value="ADMIN">Quản trị viên hệ thống (ADMIN)</option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label id="scopeLabel" for="maCumQuanLy" style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        <span id="scopeLabelText">Phạm vi phụ trách (Chỉ định Cụm / Khu / Tòa)</span>
                        <span id="scopeRequiredStar" style="color:#dc2626; display:none;">*</span>
                    </label>
                    <select id="maCumQuanLy" name="maCumQuanLy" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="">-- Vui lòng chọn Vai trò hệ thống trước --</option>
                    </select>
                    <div id="scopeHelpText" style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                        * Phạm vi quản lý sẽ tự động điều chỉnh theo vai trò được chọn (Trưởng khu chọn Khu, Trưởng cụm chọn Cụm, Trưởng tòa chọn Tòa...).
                    </div>
                </div>
            </div>
            <!-- CÁC NÚT CHỨC NĂNG FORM -->
            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 8px; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" id="btnSubmitForm" class="btn btn-primary"
                        style="border-radius: 6px; padding: 10px 22px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>
                        Lưu tài khoản & Cấp quyền
                    </button>

                    <button type="button" id="btnCancelEdit" class="btn btn-outline"
                        style="border-radius: 6px; padding: 10px 18px;"
                        onclick="resetUserForm()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i>
                        Làm mới
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="hideUserForm()">
                        <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i>
                        Đóng biểu mẫu
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH TÀI KHOẢN CÁN BỘ & PHÂN TRANG (CRUD TABLE) -->
    <div class="card">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>Danh sách tài khoản cán bộ đang quản lý trong hệ thống</span>
                <span class="badge badge-info" id="userTotalBadge"><?= count($userList ?? []) ?> tài khoản</span>
            </div>
        </div>

        <!-- THANH TÌM KIẾM VÀ LỌC ĐA TIÊU CHÍ -->
        <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="position: relative; flex: 1; min-width: 240px; max-width: 420px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" id="searchUserInput" class="btn-outline"
                    placeholder="Tìm theo tên, tài khoản, SĐT, email..."
                    style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                    oninput="filterUsers()">
            </div>

            <select id="filterUserRole" class="btn-outline"
                style="min-width: 200px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterUsers()">
                <option value="">-- Tất cả vai trò --</option>
                <?php if (!empty($vaitroList)): ?>
                    <?php foreach ($vaitroList as $vt): ?>
                        <option value="<?= htmlspecialchars($vt['MaVaiTro'], ENT_QUOTES, 'UTF-8') ?>">
                            <?= htmlspecialchars($vt['TenVaiTro']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="ADMIN">Quản trị viên hệ thống</option>
                    <option value="TRUONG_KHU">Trưởng khu KTX</option>
                    <option value="TRUONG_CUM">Trưởng cụm nhà</option>
                    <option value="TRUONG_TOA">Trưởng tòa nhà</option>
                    <option value="NV_THUONG">Nhân viên kỹ thuật / hành chính</option>
                    <option value="NV_QUAN_LY_KTX">Nhân viên quản lý chung KTX</option>
                <?php endif; ?>
            </select>

            <select id="filterUserStatus" class="btn-outline"
                style="min-width: 170px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterUsers()">
                <option value="">-- Tất cả trạng thái --</option>
                <option value="1">Đang hoạt động</option>
                <option value="0">Đã khóa</option>
            </select>

            <div>
                <button type="button" class="btn btn-outline btn-sm" onclick="clearUserFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                    <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                </button>
            </div>
        </div>

        <?php if (!empty($userList)): ?>
            <div class="table-responsive">
                <table class="data-table" id="tableStaffUsers">
                    <thead>
                        <tr>
                            <th>Tên đăng nhập</th>
                            <th>Họ và tên cán bộ</th>
                            <th>Vai trò hệ thống</th>
                            <th>Phạm vi phụ trách</th>
                            <th>Số điện thoại & Email</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php foreach ($userList as $u): ?>
                            <?php
                            $isLocked = ((int)($u['DangHoatDong'] ?? 1)) === 0;
                            $scopeLabel = 'Toàn hệ thống KTX';
                            if ($u['MaCumQuanLy'] === 'KHU_A' || $u['MaCumQuanLy'] === 'A') {
                                $scopeLabel = 'Toàn bộ Khu A';
                            } elseif ($u['MaCumQuanLy'] === 'KHU_B' || $u['MaCumQuanLy'] === 'B') {
                                $scopeLabel = 'Toàn bộ Khu B';
                            } elseif (!empty($u['TenToaNha'])) {
                                $scopeLabel = $u['TenToaNha'] . ' (' . $u['MaCumQuanLy'] . ')';
                            } elseif (!empty($u['TenCum'])) {
                                $scopeLabel = $u['TenCum'] . ' (' . ($u['TenKhu'] ?? 'KTX') . ')';
                            } elseif (!empty($u['MaCumQuanLy'])) {
                                $scopeLabel = 'Phạm vi: ' . $u['MaCumQuanLy'];
                            } elseif ($u['MaVaiTro'] === 'TRUONG_KHU') {
                                $scopeLabel = 'Toàn bộ Khu KTX';
                            }
                            ?>
                            <tr class="user-row"
                                data-id="<?= (int)$u['MaTaiKhoan'] ?>"
                                data-role="<?= htmlspecialchars($u['MaVaiTro'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-status="<?= ((int)($u['DangHoatDong'] ?? 1) === 1) ? '1' : '0' ?>"
                                style="<?= $isLocked ? 'background: #fef2f2;' : '' ?>">
                                <td>
                                    <b style="color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($u['TenDangNhap']) ?></b>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($u['HoVaTen'] ?? 'Cán bộ BQL') ?></b>
                                    <?php if (!empty($u['ChucVu'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($u['ChucVu']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $badgeClass = 'badge-info';
                                    if ($u['MaVaiTro'] === 'ADMIN') $badgeClass = 'badge-danger';
                                    elseif ($u['MaVaiTro'] === 'TRUONG_KHU') $badgeClass = 'badge-warning';
                                    elseif ($u['MaVaiTro'] === 'TRUONG_CUM') $badgeClass = 'badge-success';
                                    elseif ($u['MaVaiTro'] === 'TRUONG_TOA') $badgeClass = 'badge-primary';
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($u['TenVaiTro'] ?? $u['MaVaiTro']) ?>
                                    </span>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($scopeLabel) ?></b>
                                </td>
                                <td>
                                    <div><?= htmlspecialchars($u['SoDienThoai'] ?? '—') ?></div>
                                    <?php if (!empty($u['Email'])): ?>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);"><?= htmlspecialchars($u['Email']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge badge-danger">Đã khóa</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Hoạt động</span>
                                    <?php endif; ?>
                                </td>
                                <td style="font-size: 0.8rem; color: var(--text-muted);">
                                    <?= !empty($u['NgayTao']) ? date('d/m/Y', strtotime($u['NgayTao'])) : '—' ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <!-- NÚT SỬA (UPDATE) -->
                                    <button type="button" class="btn btn-outline btn-sm"
                                        style="border-radius: 6px; margin-right: 4px;"
                                        onclick='editUser(<?= json_encode($u, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                        title="Chỉnh sửa thông tin & phân quyền">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                    </button>

                                    <!-- NÚT KHÓA / MỞ KHÓA -->
                                    <button type="button" class="btn btn-outline btn-sm btn-toggle-user"
                                        style="border-radius: 6px; margin-right: 4px; <?= $isLocked ? 'color: #15803d; border-color: #86efac;' : 'color: #b45309; border-color: #fde68a;' ?>"
                                        data-id="<?= (int)$u['MaTaiKhoan'] ?>"
                                        data-username="<?= htmlspecialchars($u['TenDangNhap'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-locked="<?= $isLocked ? '1' : '0' ?>"
                                        onclick="handleToggleUser(this)"
                                        title="<?= $isLocked ? 'Mở khóa tài khoản' : 'Khóa tài khoản' ?>">
                                        <i class="fa-solid <?= $isLocked ? 'fa-lock-open' : 'fa-lock' ?>"
                                            style="margin-right: 6px;"></i> <?= $isLocked ? 'Mở khóa' : 'Khóa' ?>
                                    </button>

                                    <!-- NÚT XÓA -->
                                    <button type="button" class="btn btn-outline btn-sm btn-delete-user"
                                        style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                        data-id="<?= (int)$u['MaTaiKhoan'] ?>"
                                        data-username="<?= htmlspecialchars($u['TenDangNhap'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-fullname="<?= htmlspecialchars($u['HoVaTen'] ?? $u['TenDangNhap'], ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="handleDeleteUser(this)"
                                        title="Xóa tài khoản">
                                        <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- THANH PHÂN TRANG (PAGINATION CONTROLS) -->
            <div id="userPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <span id="userPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Hiển thị 1 - 10 trên tổng số <?= count($userList ?? []) ?> tài khoản
                    </span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label for="userPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                        <select id="userPageSize" class="btn-outline" onchange="changeUserPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                            <option value="10" selected>10 / trang</option>
                            <option value="25">25 / trang</option>
                            <option value="50">50 / trang</option>
                            <option value="100">100 / trang</option>
                            <option value="-1">Tất cả</option>
                        </select>
                    </div>
                </div>

                <div id="userPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                    <!-- Sẽ được sinh động bằng Javascript -->
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem; padding: 20px 0;">Chưa có tài khoản cán bộ nào trong hệ thống.</p>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL IMPORT DANH SÁCH CÁN BỘ TỪ FILE CSV -->
<!-- ========================================================================= -->
<div id="importUserModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 16px; box-sizing: border-box;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 580px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
        <!-- Modal Header -->
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-import" style="color: #2563eb; margin-right: 6px;"></i>
                <span>Nhập danh sách Cán bộ từ file CSV</span>
            </div>
            <button type="button" onclick="closeImportUserModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Modal Body & Form -->
        <form action="<?= url('admin/users/import') ?>" method="POST" enctype="multipart/form-data" style="margin: 0;">
            <?= csrf_field() ?>
            <div style="padding: 20px;">
                <!-- Hướng dẫn & Tải file mẫu -->
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #1e40af;">
                    <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Quy định định dạng file nhập dữ liệu:
                    </div>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                        <li>Hỗ trợ file định dạng <b>.CSV</b> (hoặc <b>.TXT</b>), phân cách bởi dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>.</li>
                        <li>Các cột bắt buộc: <b>TenDangNhap</b>, <b>HoVaTen</b>, <b>MaVaiTro</b>.</li>
                        <li>Mã vai trò hợp lệ: <code>ADMIN</code>, <code>NV_QUAN_LY_KTX</code>, <code>TRUONG_KHU</code>, <code>TRUONG_CUM</code>, <code>TRUONG_TOA</code>, <code>NV_THUONG</code>.</li>
                        <li>Cột mật khẩu (<b>MatKhau</b>): Nếu để trống hệ thống sẽ tự động gán mật khẩu mặc định <code>Demo@123</code>.</li>
                    </ul>
                    <div style="margin-top: 10px; display: flex; justify-content: flex-end;">
                        <a href="<?= url('admin/users/template') ?>" class="btn btn-outline btn-sm" style="background: #fff; color: #1e40af; border-color: #93c5fd; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 5px 12px; display: inline-flex; align-items: center;">
                            <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Tải tệp CSV mẫu chuẩn
                        </a>
                    </div>
                </div>

                <!-- Chọn file -->
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                        Chọn tệp CSV danh sách cán bộ <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="file" name="importFile" accept=".csv,.txt" required class="btn-outline" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem; background: #fff;">
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Tệp tối đa 10MB, hỗ trợ font tiếng Việt UTF-8.</small>
                </div>

                <!-- Tùy chọn ghi đè -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                    <label style="display: flex; align-items: flex-start; gap: 8px; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                        <input type="checkbox" name="overwriteExisting" value="1" checked style="margin-top: 2px;">
                        <span>
                            <b>Cập nhật thông tin nếu tài khoản đã tồn tại:</b> Khi được chọn, cán bộ có trùng Tên đăng nhập sẽ được cập nhật Họ tên, SĐT, Email, Vai trò và Cụm quản lý mới.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeImportUserModal()" style="border-radius: 6px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600;">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px;"></i> Bắt đầu nhập dữ liệu
                </button>
            </div>
        </form>
    </div>
</div>

<!-- JAVASCRIPT XỬ LÝ PHÂN TRANG, BỘ LỌC, MODAL VÀ FORM CRUD -->
<script>
    // Dữ liệu cán bộ và hạ tầng phân cấp có sẵn để tra cứu & gán phạm vi phụ trách
    const staffPool = <?= json_encode($allStaff ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const khuPool = <?= json_encode($khuList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const cumPool = <?= json_encode($cumNhaList ?? [], JSON_UNESCAPED_UNICODE) ?>;
    const toaPool = <?= json_encode($toaNhaList ?? [], JSON_UNESCAPED_UNICODE) ?>;

    // =========================================================================
    // 1. PHÂN TRANG VÀ LỌC TÌM KIẾM CÁN BỘ (REAL-TIME PAGINATION & FILTER)
    // =========================================================================
    let userCurrentPage = 1;
    let userPageSize = 10;
    let userMatchedRows = [];

    function filterUsers() {
        const searchInput = document.getElementById('searchUserInput');
        const roleSelect = document.getElementById('filterUserRole');
        const statusSelect = document.getElementById('filterUserStatus');
        const userTableBody = document.getElementById('userTableBody');

        if (!userTableBody) return;

        const keyword = searchInput ? searchInput.value.trim().toLocaleLowerCase('vi') : '';
        const selectedRole = roleSelect ? roleSelect.value : '';
        const selectedStatus = statusSelect ? statusSelect.value : '';

        const allRows = userTableBody.querySelectorAll('tr.user-row');
        userMatchedRows = [];

        allRows.forEach(row => {
            const text = row.textContent.toLocaleLowerCase('vi');
            const role = row.getAttribute('data-role') || '';
            const status = row.getAttribute('data-status') || '';

            const matchesKeyword = !keyword || text.includes(keyword);
            const matchesRole = !selectedRole || role === selectedRole;
            const matchesStatus = !selectedStatus || status === selectedStatus;

            if (matchesKeyword && matchesRole && matchesStatus) {
                userMatchedRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Xử lý dòng thông báo khi không có kết quả
        let noResultRow = document.getElementById('noUserSearchResult');
        if (userMatchedRows.length === 0 && allRows.length > 0) {
            if (!noResultRow) {
                noResultRow = document.createElement('tr');
                noResultRow.id = 'noUserSearchResult';
                noResultRow.innerHTML = '<td colspan="8" style="text-align:center; padding:28px; color:var(--text-muted);"><i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy tài khoản cán bộ nào phù hợp với tiêu chí lọc.</td>';
                userTableBody.appendChild(noResultRow);
            }
            noResultRow.style.display = '';
        } else if (noResultRow) {
            noResultRow.style.display = 'none';
        }

        renderUserPagination();
    }

    function renderUserPagination() {
        const total = userMatchedRows.length;
        let totalPages = userPageSize > 0 ? Math.ceil(total / userPageSize) : 1;
        if (totalPages < 1) totalPages = 1;

        if (userCurrentPage > totalPages) userCurrentPage = totalPages;
        if (userCurrentPage < 1) userCurrentPage = 1;

        const startIdx = userPageSize > 0 ? (userCurrentPage - 1) * userPageSize : 0;
        const endIdx = userPageSize > 0 ? startIdx + userPageSize : total;

        userMatchedRows.forEach((row, idx) => {
            if (idx >= startIdx && idx < endIdx) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Cập nhật dòng thông tin phân trang
        const infoEl = document.getElementById('userPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerText = 'Không có tài khoản nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> cán bộ (Trang <b>${userCurrentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        const totalBadge = document.getElementById('userTotalBadge');
        if (totalBadge) {
            totalBadge.innerText = total + ' tài khoản';
        }

        // Cập nhật số lượng trên nút Xuất Excel
        const exportBtnText = document.getElementById('btnExportText');
        if (exportBtnText) {
            const allStaffRows = document.querySelectorAll('#userTableBody tr.user-row');
            if (total === allStaffRows.length) {
                exportBtnText.innerText = `Xuất Excel (${total})`;
            } else {
                exportBtnText.innerText = `Xuất Excel (${total} đã lọc)`;
            }
        }

        // Cập nhật các nút phân trang
        const controlsEl = document.getElementById('userPaginationControls');
        if (!controlsEl) return;

        if (totalPages <= 1) {
            controlsEl.innerHTML = '';
            return;
        }

        let html = '';

        // Nút Đầu (<<)
        const disabledFirst = userCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(1)" ${disabledFirst} title="Trang đầu"><i class="fa-solid fa-angles-left"></i></button>`;

        // Nút Trước (<)
        const disabledPrev = userCurrentPage === 1 ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(${userCurrentPage - 1})" ${disabledPrev} title="Trang trước"><i class="fa-solid fa-angle-left"></i></button>`;

        // Các nút số trang
        let startPage = Math.max(1, userCurrentPage - 2);
        let endPage = Math.min(totalPages, userCurrentPage + 2);

        if (startPage > 1) {
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(1)" style="padding: 5px 10px; border-radius: 6px;">1</button>`;
            if (startPage > 2) {
                html += `<span style="padding: 4px 6px; color: var(--text-muted);">&bull;&bull;&bull;</span>`;
            }
        }

        for (let p = startPage; p <= endPage; p++) {
            if (p === userCurrentPage) {
                html += `<button type="button" class="btn btn-primary btn-sm" style="padding: 5px 12px; border-radius: 6px; font-weight: 700;">${p}</button>`;
            } else {
                html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(${p})" style="padding: 5px 10px; border-radius: 6px;">${p}</button>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span style="padding: 4px 6px; color: var(--text-muted);">&bull;&bull;&bull;</span>`;
            }
            html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(${totalPages})" style="padding: 5px 10px; border-radius: 6px;">${totalPages}</button>`;
        }

        // Nút Sau (>)
        const disabledNext = userCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(${userCurrentPage + 1})" ${disabledNext} title="Trang sau"><i class="fa-solid fa-angle-right"></i></button>`;

        // Nút Cuối (>>)
        const disabledLast = userCurrentPage === totalPages ? 'disabled style="opacity: 0.45; cursor: not-allowed; padding: 5px 9px; border-radius: 6px;"' : 'style="padding: 5px 9px; border-radius: 6px;"';
        html += `<button type="button" class="btn btn-outline btn-sm" onclick="goToUserPage(${totalPages})" ${disabledLast} title="Trang cuối"><i class="fa-solid fa-angles-right"></i></button>`;

        controlsEl.innerHTML = html;
    }

    function goToUserPage(page) {
        userCurrentPage = page;
        renderUserPagination();
        const tableCard = document.getElementById('tableStaffUsers');
        if (tableCard) {
            tableCard.scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    function changeUserPageSize(val) {
        userPageSize = parseInt(val, 10);
        userCurrentPage = 1;
        renderUserPagination();
    }

    function clearUserFilters() {
        if (document.getElementById('searchUserInput')) document.getElementById('searchUserInput').value = '';
        if (document.getElementById('filterUserRole')) document.getElementById('filterUserRole').value = '';
        if (document.getElementById('filterUserStatus')) document.getElementById('filterUserStatus').value = '';
        userCurrentPage = 1;
        filterUsers();
    }

    // Xuất file CSV danh sách cán bộ theo đúng kết quả đang hiển thị theo bộ lọc
    function exportFilteredUsers() {
        if (!userMatchedRows || userMatchedRows.length === 0) {
            alert('Không có cán bộ nào phù hợp với bộ lọc hiện tại để xuất file!');
            return;
        }

        const matchedIds = [];
        userMatchedRows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (id) matchedIds.push(id);
        });

        if (matchedIds.length === 0) {
            alert('Không tìm thấy dữ liệu cán bộ hợp lệ để xuất!');
            return;
        }

        // Tạo form ẩn gửi danh sách ID cán bộ đã lọc sang backend để xuất CSV chuẩn UTF-8
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url("admin/users/export") ?>';
        form.style.display = 'none';

        const csrfInp = document.createElement('input');
        csrfInp.type = 'hidden';
        csrfInp.name = 'csrf_token';
        csrfInp.value = '<?= csrf_token() ?>';
        form.appendChild(csrfInp);

        const idsInp = document.createElement('input');
        idsInp.type = 'hidden';
        idsInp.name = 'ids';
        idsInp.value = matchedIds.join(',');
        form.appendChild(idsInp);

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    // =========================================================================
    // 2. MODAL IMPORT DANH SÁCH CÁN BỘ
    // =========================================================================
    function openImportUserModal() {
        const modal = document.getElementById('importUserModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeImportUserModal() {
        const modal = document.getElementById('importUserModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    // Đóng modal khi click ra ngoài vùng dialog
    window.addEventListener('click', function(e) {
        const importModal = document.getElementById('importUserModal');
        if (e.target === importModal) {
            closeImportUserModal();
        }
    });

    // =========================================================================
    // 3. XỬ LÝ GỢI Ý HỌ TÊN CÁN BỘ TỪ DATALIST & PHẠM VI THEO VAI TRÒ
    // =========================================================================
    const hoVaTenInput = document.getElementById('hoVaTen');
    const soDienThoaiInput = document.getElementById('soDienThoai');
    const tenDangNhapInput = document.getElementById('tenDangNhap');
    const emailInput = document.getElementById('email');
    const maVaiTroSelect = document.getElementById('maVaiTro');
    const maCumQuanLySelect = document.getElementById('maCumQuanLy');
    const editMaTaiKhoanInput = document.getElementById('editMaTaiKhoan');

    /**
     * Render linh hoạt danh sách Phạm vi phụ trách (Khu / Cụm / Tòa / Toàn hệ thống) theo Vai trò được chọn
     * @param {string} role - Mã vai trò (TRUONG_KHU, TRUONG_CUM, TRUONG_TOA, ADMIN, NV_QUAN_LY_KTX, NV_THUONG...)
     * @param {string} selectedValue - Giá trị phạm vi đã được gán trước đó (khi sửa)
     */
    function renderScopeByRole(role, selectedValue = '') {
        const scopeSelect = document.getElementById('maCumQuanLy');
        const scopeLabel = document.getElementById('scopeLabelText');
        const scopeStar = document.getElementById('scopeRequiredStar');
        const scopeHelp = document.getElementById('scopeHelpText');

        if (!scopeSelect) return;

        scopeSelect.disabled = false;
        scopeSelect.innerHTML = '';
        scopeSelect.removeAttribute('required');
        if (scopeStar) scopeStar.style.display = 'none';

        if (role === 'TRUONG_KHU') {
            // 1. Trưởng khu KTX: Chỉ định Khu KTX (Khu A / Khu B)
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi phụ trách (Chỉ định Khu KTX)';
            if (scopeStar) scopeStar.style.display = 'inline';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Trưởng khu có toàn quyền quản lý trên toàn bộ các Cụm và Tòa nhà thuộc Khu KTX được phân công.';

            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.innerText = '-- Chọn Khu KTX phụ trách --';
            scopeSelect.appendChild(optDefault);

            khuPool.forEach(k => {
                const opt = document.createElement('option');
                opt.value = 'KHU_' + k.MaKhu;
                opt.innerText = (k.TenKhu || ('Khu ' + k.MaKhu)) + ' (KHU_' + k.MaKhu + ')';
                scopeSelect.appendChild(opt);
            });

            scopeSelect.setAttribute('required', 'required');
        } else if (role === 'TRUONG_CUM') {
            // 2. Trưởng cụm nhà: Chỉ định Cụm nhà (AG, AH, BA, BB, BC...)
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi phụ trách (Chỉ định Cụm nhà)';
            if (scopeStar) scopeStar.style.display = 'inline';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Trưởng cụm phụ trách quản lý cơ sở vật chất, nhân sự và xét duyệt hồ sơ tại Cụm nhà được gán.';

            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.innerText = '-- Chọn Cụm nhà phụ trách --';
            scopeSelect.appendChild(optDefault);

            // Gom nhóm theo Khu A và Khu B
            const khuGroups = {};
            cumPool.forEach(c => {
                const kCode = c.MaKhu || 'Khác';
                if (!khuGroups[kCode]) khuGroups[kCode] = [];
                khuGroups[kCode].push(c);
            });

            for (const [kCode, cList] of Object.entries(khuGroups)) {
                const optGroup = document.createElement('optgroup');
                optGroup.label = 'Các Cụm nhà thuộc Khu ' + kCode + ':';
                cList.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.MaCum;
                    opt.innerText = (c.TenCum || ('Cụm ' + c.MaCum)) + ' (Mã: ' + c.MaCum + ')';
                    optGroup.appendChild(opt);
                });
                scopeSelect.appendChild(optGroup);
            }

            scopeSelect.setAttribute('required', 'required');
        } else if (role === 'TRUONG_TOA') {
            // 3. Trưởng tòa nhà: Chỉ định Tòa nhà (A01, H01, BA1, B02, C06...)
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi phụ trách (Chỉ định Tòa nhà)';
            if (scopeStar) scopeStar.style.display = 'inline';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Trưởng tòa phụ trách trực tiếp sinh viên, cơ sở vật chất và nề nếp tại Tòa nhà được gán.';

            const optDefault = document.createElement('option');
            optDefault.value = '';
            optDefault.innerText = '-- Chọn Tòa nhà phụ trách --';
            scopeSelect.appendChild(optDefault);

            // Gom nhóm theo Cụm
            const cumGroups = {};
            toaPool.forEach(t => {
                const cCode = t.MaCum || 'Khác';
                if (!cumGroups[cCode]) cumGroups[cCode] = [];
                cumGroups[cCode].push(t);
            });

            for (const [cCode, tList] of Object.entries(cumGroups)) {
                const optGroup = document.createElement('optgroup');
                const cumObj = cumPool.find(c => c.MaCum === cCode);
                optGroup.label = (cumObj ? cumObj.TenCum : ('Cụm ' + cCode)) + ':';
                tList.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.MaToaNha;
                    opt.innerText = (t.TenToaNha || ('Tòa ' + t.MaToaNha)) + ' (Mã: ' + t.MaToaNha + ')';
                    optGroup.appendChild(opt);
                });
                scopeSelect.appendChild(optGroup);
            }

            scopeSelect.setAttribute('required', 'required');
        } else if (role === 'ADMIN' || role === 'NV_QUAN_LY_KTX') {
            // 4. Admin hoặc Quản lý chung KTX: Toàn quyền toàn hệ thống
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi quản trị (Toàn hệ thống KTX)';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-circle-check" style="color:#15803d; margin-right: 4px;"></i> Vai trò này có toàn quyền quản trị và giám sát trên toàn bộ hệ thống KTX ĐHQG-HCM (không giới hạn khu/cụm/tòa).';

            const opt = document.createElement('option');
            opt.value = '';
            opt.innerText = 'Toàn hệ thống KTX (Không giới hạn phạm vi)';
            scopeSelect.appendChild(opt);
        } else if (role === 'NV_THUONG') {
            // 5. Nhân viên thường / Kỹ thuật / Hành chính: Cho phép chọn theo Cụm, Tòa hoặc Toàn hệ thống
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi phân công kỹ thuật / công việc';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-circle-info" style="margin-right: 4px;"></i> Nhân viên có thể được phân công làm việc theo một Cụm, một Tòa hoặc làm việc trên Toàn hệ thống.';

            const optAll = document.createElement('option');
            optAll.value = '';
            optAll.innerText = '-- Toàn hệ thống KTX (Không giới hạn) --';
            scopeSelect.appendChild(optAll);

            if (cumPool.length > 0) {
                const groupCum = document.createElement('optgroup');
                groupCum.label = 'Phụ trách theo Cụm nhà:';
                cumPool.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.MaCum;
                    opt.innerText = (c.TenCum || c.MaCum) + ' (Khu ' + (c.MaKhu || '') + ')';
                    groupCum.appendChild(opt);
                });
                scopeSelect.appendChild(groupCum);
            }

            if (toaPool.length > 0) {
                const groupToa = document.createElement('optgroup');
                groupToa.label = 'Phụ trách theo Tòa nhà:';
                toaPool.forEach(t => {
                    const opt = document.createElement('option');
                    opt.value = t.MaToaNha;
                    opt.innerText = (t.TenToaNha || t.MaToaNha) + ' (Cụm ' + (t.MaCum || '') + ')';
                    groupToa.appendChild(opt);
                });
                scopeSelect.appendChild(groupToa);
            }
        } else {
            // Chưa chọn vai trò
            if (scopeLabel) scopeLabel.innerText = 'Phạm vi phụ trách (Chỉ định Khu / Cụm / Tòa)';
            if (scopeHelp) scopeHelp.innerHTML = '<i class="fa-solid fa-arrow-up" style="margin-right: 4px;"></i> Vui lòng chọn Vai trò hệ thống ở trên để hiển thị phạm vi phân quyền phù hợp.';

            const opt = document.createElement('option');
            opt.value = '';
            opt.innerText = '-- Vui lòng chọn Vai trò hệ thống trước --';
            scopeSelect.appendChild(opt);
        }

        // Khôi phục giá trị đã chọn nếu có
        if (selectedValue !== undefined && selectedValue !== null && selectedValue !== '') {
            scopeSelect.value = selectedValue;
            if (!scopeSelect.value && selectedValue.startsWith('KHU_')) {
                scopeSelect.value = selectedValue.replace('KHU_', '');
            }
            if (!scopeSelect.value && (selectedValue === 'A' || selectedValue === 'B')) {
                scopeSelect.value = 'KHU_' + selectedValue;
            }
        }
    }

    if (maVaiTroSelect) {
        maVaiTroSelect.addEventListener('change', function() {
            renderScopeByRole(this.value);
        });
    }

    function findStaffByName(name) {
        if (!name) return null;
        const cleanName = name.trim().toLowerCase();
        return staffPool.find(s => s.HoVaTen && s.HoVaTen.trim().toLowerCase() === cleanName);
    }

    if (hoVaTenInput) {
        hoVaTenInput.addEventListener('input', function() {
            const matched = findStaffByName(this.value);
            if (matched) {
                if (matched.SoDienThoai && soDienThoaiInput) {
                    soDienThoaiInput.value = matched.SoDienThoai;
                }
                if (matched.Email && emailInput && !emailInput.value) {
                    emailInput.value = matched.Email;
                }
                if (!editMaTaiKhoanInput.value && !tenDangNhapInput.value && matched.MaNhanVien) {
                    tenDangNhapInput.value = matched.MaNhanVien;
                }
                if (!editMaTaiKhoanInput.value && matched.MaVaiTro && maVaiTroSelect) {
                    maVaiTroSelect.value = matched.MaVaiTro;
                    renderScopeByRole(matched.MaVaiTro, matched.MaCumQuanLy || '');
                }
            }
        });
    }

    // =========================================================================
    // 4. ĐIỀU KHIỂN FORM THÊM / SỬA (CRUD)
    // =========================================================================
    function showCreateUserForm() {
        resetUserForm();
        const card = document.getElementById('userFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        const btn = document.getElementById('btnToggleAddUser');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng form';
            btn.className = 'btn btn-outline';
        }
    }

    function hideUserForm() {
        const card = document.getElementById('userFormCard');
        if (card) {
            card.style.display = 'none';
        }
        const btn = document.getElementById('btnToggleAddUser');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i> + Thêm cán bộ mới';
            btn.className = 'btn btn-primary';
        }
    }

    function toggleUserForm() {
        const card = document.getElementById('userFormCard');
        if (!card) return;
        const isHidden = (card.style.display === 'none' || card.style.display === '');
        if (isHidden) {
            showCreateUserForm();
        } else {
            const isEdit = document.getElementById('editMaTaiKhoan').value !== '';
            if (isEdit) {
                showCreateUserForm();
            } else {
                hideUserForm();
            }
        }
    }

    function editUser(user) {
        document.getElementById('formTitle').innerHTML = '<span>Chỉnh sửa thông tin cán bộ: ' + (user.HoVaTen || user.TenDangNhap) + '</span>';
        document.getElementById('editMaTaiKhoan').value = user.MaTaiKhoan;

        // Cố định tên đăng nhập khi sửa
        const tenDangNhapInput = document.getElementById('tenDangNhap');
        tenDangNhapInput.value = user.TenDangNhap;
        tenDangNhapInput.readOnly = true;
        tenDangNhapInput.style.backgroundColor = '#f1f5f9';
        tenDangNhapInput.style.cursor = 'not-allowed';
        const lockNotice = document.getElementById('usernameLockNotice');
        if (lockNotice) lockNotice.style.display = 'inline';

        document.getElementById('hoVaTen').value = user.HoVaTen || '';
        document.getElementById('soDienThoai').value = user.SoDienThoai || '';
        document.getElementById('email').value = user.Email || '';

        if (maVaiTroSelect) {
            maVaiTroSelect.value = user.MaVaiTro || '';
        }
        renderScopeByRole(user.MaVaiTro || '', user.MaCumQuanLy || '');

        // Chuyển vùng mật khẩu
        const createBox = document.getElementById('createPasswordBox');
        const editBox = document.getElementById('editPasswordBox');
        if (createBox) createBox.style.display = 'none';
        if (editBox) editBox.style.display = 'block';

        const matKhauInput = document.getElementById('matKhau');
        if (matKhauInput) {
            matKhauInput.removeAttribute('required');
            matKhauInput.value = '';
        }

        document.getElementById('btnSubmitForm').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật tài khoản';

        const card = document.getElementById('userFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        const btn = document.getElementById('btnToggleAddUser');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i> Thêm cán bộ mới';
            btn.className = 'btn btn-primary';
        }
    }

    function resetUserForm() {
        document.getElementById('userActionForm').reset();
        document.getElementById('editMaTaiKhoan').value = '';

        const tenDangNhapInput = document.getElementById('tenDangNhap');
        tenDangNhapInput.readOnly = false;
        tenDangNhapInput.style.backgroundColor = '';
        tenDangNhapInput.style.cursor = '';
        const lockNotice = document.getElementById('usernameLockNotice');
        if (lockNotice) lockNotice.style.display = 'none';

        const createBox = document.getElementById('createPasswordBox');
        const editBox = document.getElementById('editPasswordBox');
        if (createBox) createBox.style.display = 'block';
        if (editBox) editBox.style.display = 'none';

        const matKhauInput = document.getElementById('matKhau');
        if (matKhauInput) {
            matKhauInput.setAttribute('required', 'required');
            matKhauInput.value = '';
            matKhauInput.placeholder = 'Nhập mật khẩu khởi tạo cho cán bộ';
        }

        document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-user-plus" style="color: var(--primary); margin-right: 8px;"></i> <span>Thêm mới hoặc Điều chỉnh phân quyền Cán bộ</span>';
        document.getElementById('btnSubmitForm').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu tài khoản & Cấp quyền';
        renderScopeByRole('');
    }

    function confirmQuickResetPassword() {
        const maTaiKhoan = document.getElementById('editMaTaiKhoan').value;
        const tenDangNhap = document.getElementById('tenDangNhap').value;
        const hoVaTen = document.getElementById('hoVaTen').value || tenDangNhap;

        if (!maTaiKhoan) return;

        openConfirmModal({
            title: 'Đặt lại mật khẩu về mặc định',
            message: `Bạn có chắc chắn muốn đặt lại mật khẩu của cán bộ <b>${hoVaTen}</b> (<code style="color:var(--primary); font-weight:700;">${tenDangNhap}</code>) về mật khẩu chuẩn <code style="color:#dc2626; font-weight:700;">Demo@123</code>?`,
            note: '<i class="fa-solid fa-shield-halved" style="margin-right: 6px;"></i> Sau khi đặt lại, cán bộ hãy sử dụng mật khẩu <b>Demo@123</b> để đăng nhập vào hệ thống.',
            btnText: 'Đặt lại mật khẩu',
            btnClass: 'btn btn-warning',
            btnIcon: 'fa-solid fa-key',
            icon: 'fa-solid fa-key',
            iconColor: '#d97706',
            action: '<?= url("admin/users/reset-password") ?>',
            inputs: {
                maTaiKhoan: maTaiKhoan
            }
        });
    }

    function handleDeleteUser(btn) {
        const id = btn.getAttribute('data-id');
        const username = btn.getAttribute('data-username');
        const fullname = btn.getAttribute('data-fullname') || username;
        confirmDeleteUser(id, username, fullname);
    }

    function confirmDeleteUser(maTaiKhoan, username, fullName) {
        if (typeof openConfirmModal !== 'function') {
            if (confirm(`Bạn có chắc chắn muốn xóa tài khoản cán bộ ${fullName} (${username})?`)) {
                submitActionForm('<?= url("admin/users/delete") ?>', {
                    maTaiKhoan: maTaiKhoan
                });
            }
            return;
        }

        openConfirmModal({
            title: 'Xác nhận xóa tài khoản cán bộ',
            message: `Bạn có chắc chắn muốn xóa tài khoản của cán bộ <b>${fullName}</b> (<code style="color:var(--primary); font-weight:700;">${username}</code>)?`,
            note: '<i class="fa-solid fa-shield-halved" style="margin-right: 6px;"></i> <b>Lưu ý:</b> Nếu tài khoản này đã từng duyệt hồ sơ hoặc tiếp nhận bảo trì thiết bị, hệ thống sẽ tự động chuyển sang trạng thái <b>KHÓA</b> để bảo toàn lịch sử dữ liệu.',
            btnText: 'Xác nhận xóa',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            action: '<?= url("admin/users/delete") ?>',
            inputs: {
                maTaiKhoan: maTaiKhoan
            }
        });
    }

    function handleToggleUser(btn) {
        const id = btn.getAttribute('data-id');
        const username = btn.getAttribute('data-username');
        const isLocked = btn.getAttribute('data-locked') === '1';
        confirmToggleStatus(id, username, isLocked);
    }

    function confirmToggleStatus(maTaiKhoan, username, isLocked) {
        const actionText = isLocked ? 'mở khóa' : 'tạm khóa';
        if (typeof openConfirmModal !== 'function') {
            if (confirm(`Bạn có chắc chắn muốn ${actionText} tài khoản cán bộ ${username}?`)) {
                submitActionForm('<?= url("admin/users/toggle-status") ?>', {
                    maTaiKhoan: maTaiKhoan
                });
            }
            return;
        }

        openConfirmModal({
            title: isLocked ? 'Xác nhận mở khóa tài khoản' : 'Xác nhận tạm khóa tài khoản',
            message: `Bạn có chắc chắn muốn <b>${actionText}</b> tài khoản cán bộ <code style="color:var(--primary); font-weight:700;">${username}</code>?`,
            note: isLocked ?
                '<i class="fa-solid fa-circle-check" style="color:#15803d; margin-right: 6px;"></i> Sau khi mở khóa, cán bộ sẽ có thể đăng nhập và tiếp tục thực hiện nghiệp vụ bình thường.' : '<i class="fa-solid fa-triangle-exclamation" style="color:#b45309; margin-right: 6px;"></i> Sau khi khóa, tài khoản này sẽ bị từ chối đăng nhập vào hệ thống.',
            btnText: isLocked ? 'Mở khóa ngay' : 'Khóa tài khoản',
            btnClass: isLocked ? 'btn btn-success' : 'btn btn-warning',
            btnIcon: isLocked ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock',
            icon: isLocked ? 'fa-solid fa-lock-open' : 'fa-solid fa-lock',
            iconColor: isLocked ? '#15803d' : '#d97706',
            action: '<?= url("admin/users/toggle-status") ?>',
            inputs: {
                maTaiKhoan: maTaiKhoan
            }
        });
    }

    function submitActionForm(action, data) {
        const f = document.createElement('form');
        f.method = 'POST';
        f.action = action;
        f.style.display = 'none';
        f.innerHTML = '<?= csrf_field() ?>';
        for (const [k, v] of Object.entries(data)) {
            const inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = k;
            inp.value = v;
            f.appendChild(inp);
        }
        document.body.appendChild(f);
        f.submit();
    }

    // Khởi tạo bộ lọc, phân trang và phạm vi theo vai trò ngay khi trang tải xong
    document.addEventListener('DOMContentLoaded', function() {
        filterUsers();
        if (maVaiTroSelect) {
            renderScopeByRole(maVaiTroSelect.value);
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = "Quản lý Cán bộ & Phân quyền - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>