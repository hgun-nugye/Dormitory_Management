<?php
ob_start();
?>

<div class="container">
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Quản lý Tài khoản Sinh viên</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px; display: flex; justify-content: space-between;">
            <div>
                <h1 class="page-title">Quản lý Tài khoản Sinh viên</h1>
            </div>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <button type="button" class="btn btn-primary" id="btnToggleAddStudent" onclick="showCreateStudentForm()" style="border-radius: 6px; font-weight: 600; padding: 7px 14px; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i> Thêm sinh viên mới
                </button>
                <button type="button" class="btn btn-outline" onclick="openImportStudentModal()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #2563eb; border-color: #bfdbfe; background: #eff6ff;" title="Nhập danh sách cán bộ từ file CSV">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px; color: #2563eb;"></i> Nhập file
                </button>
                <button type="button" class="btn btn-outline" onclick="exportFilteredStudents()" style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; color: #15803d; border-color: #86efac; background: #f0fdf4; cursor: pointer;" title="Xuất danh sách sinh viên theo kết quả lọc hiện tại ra Excel/CSV">
                    <i class="fa-solid fa-file-excel" style="margin-right: 6px; color: #16a34a;"></i> <span id="btnExportText">Xuất Excel</span>
                </button>
                <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline" style="border-radius: 6px; padding: 6px 14px; text-decoration: none;">
                    &larr; Về Dashboard
                </a>
            </div>
        </div>
    </div>
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
    <div class="card" id="studentFormCard" style="<?= !empty($flashError) ? 'display: block;' : 'display: none;' ?> margin-bottom: 24px; border: 2px solid #bfdbfe; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.08);">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="formTitle" style="font-size: 1.05rem; font-weight: 700; color: #1e3a8a; margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-user-plus" style="color: var(--primary); margin-right: 8px;"></i>
                <span id="formTitleText">Thêm mới hoặc Điều chỉnh thông tin Sinh viên</span>
            </h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="hideStudentForm()" style="border-radius: 6px; color: var(--text-muted); cursor: pointer;" title="Đóng biểu mẫu">
                <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
            </button>
        </div>

        <form id="studentActionForm" action="<?= url('admin/students/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="maTaiKhoan" id="studentMaTaiKhoan" value="0">
            <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Mã sinh viên (MSSV) <span style="color:#dc2626;">*</span>
                        <span id="noticeStudentEdit" style="display: none; font-size: 0.75rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                            <i class="fa-solid fa-lock" style="margin-right: 4px;"></i>
                        </span>
                    </label>
                    <input type="text" id="studentMaSinhVien" name="maSinhVien" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 2110001, 2212345..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Họ và tên sinh viên <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" id="studentHoVaTen" name="hoVaTen" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Trần Văn An..." required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Trường Đại học đào tạo <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="studentMaTruong" name="maTruong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                        <option value="">-- Chọn Trường Đại học --</option>
                        <?php foreach ($truongList as $tr): ?>
                            <option value="<?= htmlspecialchars($tr['MaTruong']) ?>">
                                <?= htmlspecialchars($tr['TenTruong']) ?> (<?= htmlspecialchars($tr['MaTruong']) ?>) <?= !empty($tr['ThuocDHQGHCM']) ? '[ĐHQG]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Giới tính <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="studentGioiTinh" name="gioiTinh" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                        <option value="Nam">Nam</option>
                        <option value="Nữ">Nữ</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Ngày sinh:
                    </label>
                    <input type="date" id="studentNgaySinh" name="ngaySinh" class="btn-outline" style="width: 100%; padding: 9px; border-radius: 6px; font-size: 0.9rem;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Số CCCD / CMND:
                    </label>
                    <input type="text" id="studentSoCCCD" name="soCCCD" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="12 chữ số CCCD...">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Khóa tuyển sinh:
                    </label>
                    <input type="number" id="studentKhoaTuyenSinh" name="khoaTuyenSinh" min="2015" max="2035" value="<?= date('Y') ?>" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                </div>
            </div>
            <div class="grid grid-cols-4" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Số điện thoại liên hệ:
                    </label>
                    <input type="text" id="studentSoDienThoai" name="soDienThoai" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 0903123456...">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Địa chỉ Email:
                    </label>
                    <input type="email" id="studentEmail" name="email" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="sinhvien@hcmut.edu.vn...">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Tình trạng nội trú:
                    </label>
                    <select id="studentTrangThaiNoiTru" name="trangThaiNoiTru" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="Chưa ở">Chưa ở</option>
                        <option value="Đang nội trú">Đang nội trú</option>
                        <option value="Tạm vắng">Tạm vắng</option>
                        <option value="Đã trả phòng">Đã trả phòng</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Vai trò hệ thống:
                    </label>
                    <select id="studentMaVaiTro" name="maVaiTro" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;">
                        <option value="SINH_VIEN">Sinh viên nội trú</option>
                        <option value="TRUONG_PHONG">Trưởng phòng</option>
                    </select>
                </div>
            </div>
            <div id="boxStudentPassword" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                    Mật khẩu khởi tạo ban đầu (Mặc định: <code>123456</code> nếu để trống):
                </label>
                <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                    <input type="text" id="studentMatKhau" name="matKhau" class="btn-outline" style="width: 100%; max-width: 380px; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem;" placeholder="Nhập mật khẩu khởi tạo (tối thiểu 6 ký tự)...">
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        <i class="fa-solid fa-circle-info" style="color: #2563eb;"></i> Để đổi mật khẩu cho sinh viên đã có trong hệ thống, vui lòng dùng nút <b>"Đổi MK"</b> tại bảng danh sách để tạo mật khẩu 8 ký tự và tự động gửi email.
                    </small>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; gap: 10px;">
                    <button type="submit" id="btnSubmitStudent" class="btn btn-primary" style="border-radius: 6px; padding: 10px 22px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> <span id="btnSubmitStudentText">Lưu tài khoản Sinh viên</span>
                    </button>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetStudentForm()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm mới
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px; color: #dc2626; border-color: #fecaca;" onclick="hideStudentForm()">
                        <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <span>Danh sách Tài khoản Sinh viên đang quản lý</span>
                <span class="badge badge-info" id="studentTotalBadge"><?= count($studentList ?? []) ?> sinh viên</span>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 12px; margin: 0 0 20px; flex-wrap: wrap; background: #f8fafc; padding: 14px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
            <div style="position: relative; flex: 1; min-width: 240px; max-width: 380px;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                <input type="text" id="searchStudentInput" class="btn-outline"
                    placeholder="Tìm theo MSSV, họ tên, email, SĐT, CCCD, phòng..."
                    style="width: 100%; padding: 8px 12px 8px 36px; border-radius: 6px; font-size: 0.9rem;"
                    oninput="filterStudents()">
            </div>
            <select id="filterStudentTruong" class="btn-outline"
                style="min-width: 180px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterStudents()">
                <option value="">-- Tất cả Trường ĐH --</option>
                <?php foreach ($truongList as $tr): ?>
                    <option value="<?= htmlspecialchars($tr['MaTruong']) ?>">
                        <?= htmlspecialchars($tr['TenTruong']) ?> (<?= htmlspecialchars($tr['MaTruong']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="filterStudentGender" class="btn-outline"
                style="min-width: 140px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterStudents()">
                <option value="">-- Tất cả giới tính --</option>
                <option value="Nam">Nam</option>
                <option value="Nữ">Nữ</option>
            </select>
            <select id="filterStudentAccStatus" class="btn-outline"
                style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterStudents()">
                <option value="">-- Tất cả tài khoản --</option>
                <option value="1">Đang hoạt động</option>
                <option value="0">Đang bị khóa</option>
            </select>
            <select id="filterStudentResStatus" class="btn-outline"
                style="min-width: 160px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterStudents()">
                <option value="">-- Tình trạng nội trú --</option>
                <option value="Đang nội trú">Đang nội trú</option>
                <option value="Chưa ở">Chưa ở</option>
                <option value="Tạm vắng">Tạm vắng</option>
                <option value="Đã trả phòng">Đã trả phòng</option>
            </select>
            <select id="filterStudentRole" class="btn-outline"
                style="min-width: 150px; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem;"
                onchange="filterStudents()">
                <option value="">-- Tất cả vai trò --</option>
                <option value="SINH_VIEN">Sinh viên</option>
                <option value="TRUONG_PHONG">Trưởng phòng</option>
            </select>
            <div>
                <button type="button" class="btn btn-outline btn-sm" onclick="clearStudentFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer; padding: 8px 14px;">
                    <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                </button>
            </div>
        </div>

        <?php if (!empty($studentList)): ?>
            <div class="table-responsive">
                <table class="data-table" id="tableAdminStudents">
                    <thead>
                        <tr>
                            <th>MSSV</th>
                            <th>Họ và tên</th>
                            <th>Trường ĐH</th>
                            <th>Thông tin</th>
                            <th>Chỗ ở nội trú</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th style="text-align: right; min-width: 200px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <?php foreach ($studentList as $s): ?>
                            <?php
                            $searchVal = strtolower(trim(
                                ($s['MaSinhVien'] ?? '') . ' ' .
                                    ($s['HoVaTen'] ?? '') . ' ' .
                                    ($s['SoDienThoai'] ?? '') . ' ' .
                                    ($s['Email'] ?? '') . ' ' .
                                    ($s['SoCCCD'] ?? '') . ' ' .
                                    ($s['TenTruong'] ?? '') . ' ' .
                                    ($s['MaTruong'] ?? '') . ' ' .
                                    ($s['MaPhong'] ?? '') . ' ' .
                                    ($s['TenToaNha'] ?? '')
                            ));
                            $isActive = (int)($s['DangHoatDong'] ?? 1) === 1;
                            $isLeader = ($s['MaVaiTro'] === 'TRUONG_PHONG');
                            ?>
                            <tr class="student-row"
                                data-id="<?= htmlspecialchars($s['MaSinhVien'], ENT_QUOTES, 'UTF-8') ?>"
                                data-acc-id="<?= (int)$s['MaTaiKhoan'] ?>"
                                data-truong="<?= htmlspecialchars($s['MaTruong'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-gender="<?= htmlspecialchars($s['GioiTinh'] ?? 'Nam', ENT_QUOTES, 'UTF-8') ?>"
                                data-acc-status="<?= $isActive ? '1' : '0' ?>"
                                data-res-status="<?= htmlspecialchars($s['TrangThaiNoiTru'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                data-role="<?= htmlspecialchars($s['MaVaiTro'] ?? 'SINH_VIEN', ENT_QUOTES, 'UTF-8') ?>"
                                data-search="<?= htmlspecialchars($searchVal, ENT_QUOTES, 'UTF-8') ?>"
                                style="<?= !$isActive ? 'background: #fef2f2;' : '' ?>">
                                <td>
                                    <b style="color: var(--primary); font-size: 0.95rem; font-family: monospace;">
                                        <?= htmlspecialchars($s['MaSinhVien']) ?>
                                    </b>
                                    <div style="font-size: 0.75rem; color: var(--text-muted);">
                                        Tạo: <?= !empty($s['NgayTao']) ? date('d/m/Y', strtotime($s['NgayTao'])) : '---' ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: #0f172a; font-size: 0.95rem;">
                                        <?= htmlspecialchars($s['HoVaTen']) ?>
                                    </div>
                                    <div style="font-size: 0.775rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 2px; margin-top: 2px;">
                                        <?php if (!empty($s['Email'])): ?>
                                            <span><i class="fa-regular fa-envelope" style="width: 14px;"></i> <?= htmlspecialchars($s['Email']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['SoDienThoai'])): ?>
                                            <span><i class="fa-solid fa-phone" style="width: 14px;"></i> <?= htmlspecialchars($s['SoDienThoai']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: #1e293b; font-size: 0.85rem;">
                                        <?= htmlspecialchars($s['TenTruong'] ?? $s['MaTruong']) ?>
                                    </div>
                                    <div style="font-size: 0.775rem; color: var(--text-muted);">
                                        Mã: <code style="font-weight: 600;"><?= htmlspecialchars($s['MaTruong']) ?></code>
                                        <?php if (!empty($s['KhoaTuyenSinh'])): ?>
                                            &bull; K<?= (int)$s['KhoaTuyenSinh'] ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php if ($s['GioiTinh'] === 'Nữ'): ?>
                                            <span style="color: #db2777; font-weight: 600;"><i class="fa-solid fa-venus"></i> Nữ</span>
                                        <?php else: ?>
                                            <span style="color: #2563eb; font-weight: 600;"><i class="fa-solid fa-mars"></i> Nam</span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['NgaySinh'])): ?>
                                            <span style="font-size: 0.775rem; color: var(--text-muted); margin-left: 4px;">
                                                (<?= date('d/m/Y', strtotime($s['NgaySinh'])) ?>)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                        CCCD: <?= !empty($s['SoCCCD']) ? htmlspecialchars($s['SoCCCD']) : '<i>Chưa cập nhật</i>' ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if (!empty($s['MaPhong'])): ?>
                                        <div style="font-weight: 700; color: #15803d; font-size: 0.85rem;">
                                            Phòng <?= htmlspecialchars($s['MaPhong']) ?>
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--text-muted);">
                                            <?= htmlspecialchars($s['TenToaNha'] ?? '') ?> (<?= htmlspecialchars($s['KyHieuGiuong'] ?? '') ?>)
                                        </div>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 0.75rem;">Chưa xếp phòng</span>
                                    <?php endif; ?>
                                    <div style="margin-top: 4px;">
                                        <?php if ($s['TrangThaiNoiTru'] === 'Đang nội trú'): ?>
                                            <span class="badge badge-success" style="font-size: 0.7rem;">Đang nội trú</span>
                                        <?php elseif ($s['TrangThaiNoiTru'] === 'Tạm vắng'): ?>
                                            <span class="badge badge-warning" style="font-size: 0.7rem;">Tạm vắng</span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #e2e8f0; color: #475569; font-size: 0.7rem;"><?= htmlspecialchars($s['TrangThaiNoiTru'] ?? 'Chưa ở') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isLeader): ?>
                                        <span class="badge badge-warning" style="font-weight: 700; font-size: 0.775rem;">
                                            Trưởng phòng
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-primary" style="font-size: 0.775rem;">Sinh viên</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($isActive): ?>
                                        <span class="badge badge-success" style="font-weight: 600;">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Hoạt động
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" style="font-weight: 600;">
                                            <i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Đã khóa
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px; padding: 4px 8px;"
                                        data-item="<?= htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="editStudent(this)"
                                        title="Chỉnh sửa thông tin sinh viên">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 5px;"></i> Sửa
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px; padding: 4px 8px; color: #7c3aed; border-color: #ddd6fe; background: #faf5ff;"
                                        data-item="<?= htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="openChangePasswordModal(this)"
                                        title="Đổi hoặc Đặt lại mật khẩu tài khoản sinh viên">
                                        <i class="fa-solid fa-key" style="margin-right: 5px;"></i> Đổi MK
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; margin-right: 4px; padding: 4px 8px; <?= $isActive ? 'color: #d97706; border-color: #fde68a; background: #fffbeb;' : 'color: #16a34a; border-color: #bbf7d0; background: #f0fdf4;' ?>"
                                        data-item="<?= htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="openToggleLockModal(this)"
                                        title="<?= $isActive ? 'Khóa tài khoản sinh viên' : 'Mở khóa tài khoản sinh viên' ?>">
                                        <i class="fa-solid <?= $isActive ? 'fa-lock' : 'fa-lock-open' ?>" style="margin-right: 5px;"></i> <?= $isActive ? 'Khóa' : 'Mở' ?>
                                    </button>
                                    <button type="button" class="btn btn-outline btn-sm" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; padding: 4px 8px;"
                                        data-item="<?= htmlspecialchars(json_encode($s, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="confirmDeleteStudent(this)"
                                        title="Xóa tài khoản sinh viên">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="noMatchStudentRow" style="display: none;">
                            <td colspan="8" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy sinh viên nào khớp với bộ lọc và từ khóa hiện tại.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="studentPaginationBar" style="display: flex; justify-content: space-between; align-items: center; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border); flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                    <span id="studentPaginationInfo" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500;">
                        Hiển thị 1 - 10 trên tổng số <?= count($studentList ?? []) ?> sinh viên
                    </span>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <label for="studentPageSize" style="font-size: 0.825rem; color: var(--text-secondary);">Hiển thị:</label>
                        <select id="studentPageSize" class="btn-outline" onchange="changeStudentPageSize(this.value)" style="padding: 4px 8px; border-radius: 6px; font-size: 0.825rem;">
                            <option value="10" selected>10 / trang</option>
                            <option value="25">25 / trang</option>
                            <option value="50">50 / trang</option>
                            <option value="100">100 / trang</option>
                            <option value="-1">Tất cả</option>
                        </select>
                    </div>
                </div>

                <div id="studentPaginationControls" style="display: flex; align-items: center; gap: 4px; flex-wrap: wrap;">
                </div>
            </div>
        <?php else: ?>
            <p class="text-muted" style="padding: 24px 0; font-size: 0.9rem;">Chưa có dữ liệu tài khoản sinh viên trong hệ thống.</p>
        <?php endif; ?>
    </div>
</div>
<div id="importStudentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 620px; max-height: calc(100vh - 40px); display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; flex-shrink: 0;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-file-import" style="color: #2563eb; margin-right: 6px;"></i>
                <span>Nhập danh sách Tài khoản Sinh viên từ file CSV</span>
            </div>
            <button type="button" onclick="closeImportStudentModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form action="<?= url('admin/students/import') ?>" method="POST" enctype="multipart/form-data" style="margin: 0; display: flex; flex-direction: column; flex: 1 1 auto; overflow: hidden;">
            <?= csrf_field() ?>
            <div style="padding: 20px; overflow-y: auto; flex: 1 1 auto; max-height: calc(100vh - 170px);">
                <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 14px 16px; margin-bottom: 18px; font-size: 0.85rem; color: #1e40af;">
                    <div style="font-weight: 700; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-circle-info"></i> Quy định định dạng file nhập dữ liệu:
                    </div>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.6;">
                        <li>Hỗ trợ file định dạng <b>.CSV</b> hoặc <b>.TXT</b> (mã hóa UTF-8), phân cách bởi dấu phẩy <code>,</code> hoặc chấm phẩy <code>;</code>.</li>
                        <li><b>Cột bắt buộc:</b> <code>MSSV</code>, <code>HoVaTen</code>, <code>MaTruong</code> (BK, KHTN, UIT, USSH, QL...).</li>
                        <li><b>Cột tùy chọn:</b> <code>GioiTinh</code> (Nam/Nữ), <code>NgaySinh</code> (YYYY-MM-DD), <code>SoCCCD</code>, <code>KhoaTuyenSinh</code>, <code>SoDienThoai</code>, <code>Email</code>, <code>TrangThaiNoiTru</code>, <code>MatKhau</code> (mặc định <code>123456</code>).</li>
                        <li><i>Hệ thống tự động khởi tạo tài khoản đăng nhập với mật khẩu được mã hóa an toàn!</i></li>
                    </ul>
                    <div style="margin-top: 12px; display: flex; justify-content: flex-end;">
                        <a href="<?= url('admin/students/template') ?>" class="btn btn-outline btn-sm" style="background: #fff; color: #1e40af; border-color: #93c5fd; font-weight: 600; text-decoration: none; border-radius: 6px; padding: 6px 14px; display: inline-flex; align-items: center;">
                            <i class="fa-solid fa-download" style="margin-right: 6px;"></i> Tải tệp CSV mẫu chuẩn
                        </a>
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 6px;">
                        Chọn tệp CSV sinh viên <span style="color: #dc2626;">*</span>
                    </label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required class="btn-outline" style="width: 100%; padding: 9px 12px; border-radius: 6px; font-size: 0.9rem; background: #fff;">
                    <small style="color: var(--text-muted); display: block; margin-top: 4px;">Tệp tối đa 10MB, mã hóa UTF-8.</small>
                </div>

                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 14px;">
                    <input type="hidden" name="overwriteExisting" value="0">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; color: #334155; cursor: pointer; user-select: none;">
                        <input type="checkbox" id="overwriteExistingStudents" name="overwriteExisting" value="1" checked style="margin-top: 3px; width: 16px; height: 16px; cursor: pointer; accent-color: #2563eb;">
                        <span>
                            <b style="color: #1e293b;">Cập nhật / Ghi đè nếu sinh viên đã tồn tại:</b><br>
                            <span style="font-size: 0.8rem; color: #64748b; line-height: 1.5; display: block; margin-top: 2px;">
                                • <b>Nếu chọn:</b> Cập nhật lại thông tin cá nhân (họ tên, trường, ngày sinh, CCCD, SĐT, email) nếu sinh viên đã có trong hệ thống.<br>
                                • <b>Nếu bỏ chọn:</b> Bỏ qua sinh viên đã có, chỉ thêm mới các sinh viên chưa từng tồn tại.
                            </span>
                        </span>
                    </label>
                </div>
            </div>

            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px; flex-shrink: 0;">
                <button type="button" class="btn btn-outline" onclick="closeImportStudentModal()" style="border-radius: 6px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px; padding: 8px 20px; font-weight: 600;">
                    <i class="fa-solid fa-file-import" style="margin-right: 6px;"></i> Bắt đầu nhập dữ liệu
                </button>
            </div>
        </form>
    </div>
</div>
<div id="lockStudentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <div id="lockModalIconCircle" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid transparent;">
                    <i class="fa-solid fa-lock" style="font-size: 1.15rem;"></i>
                </div>
                <span id="lockModalTitle">Xác nhận Khóa tài khoản sinh viên</span>
            </div>
            <button type="button" onclick="closeLockStudentModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="<?= url('admin/students/toggle-status') ?>" method="POST" style="margin: 0;">
            <?= csrf_field() ?>
            <input type="hidden" name="maTaiKhoan" id="lockModalMaTaiKhoan" value="">
            <input type="hidden" name="status" id="lockModalTargetStatus" value="">

            <div style="padding: 20px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                        <div>
                            <div id="lockModalHoVaTen" style="font-weight: 700; font-size: 1.05rem; color: #0f172a;"></div>
                            <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 2px;">
                                MSSV: <code id="lockModalMSSV" style="font-weight: 700; color: var(--primary);"></code>
                                &bull; <span id="lockModalTruong"></span>
                            </div>
                        </div>
                        <span id="lockModalCurrentBadge"></span>
                    </div>
                    <div style="display: flex; gap: 16px; font-size: 0.8rem; color: var(--text-secondary); border-top: 1px dashed #cbd5e1; padding-top: 8px; margin-top: 8px; flex-wrap: wrap;">
                        <div><i class="fa-solid fa-bed" style="color: #64748b; margin-right: 4px;"></i> <span id="lockModalPhong"></span></div>
                        <div><i class="fa-solid fa-phone" style="color: #64748b; margin-right: 4px;"></i> <span id="lockModalSDT"></span></div>
                    </div>
                </div>
                <div id="lockModalNotice" style="border-radius: 8px; padding: 12px 14px; font-size: 0.85rem; line-height: 1.5; margin-bottom: 6px;">
                </div>
            </div>
            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeLockStudentModal()" style="border-radius: 6px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" id="lockModalSubmitBtn" class="btn btn-warning" style="border-radius: 6px; padding: 8px 20px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-lock"></i> <span>Xác nhận Khóa</span>
                </button>
            </div>
        </form>
    </div>
</div>
<div id="changePasswordStudentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); z-index: 999999; align-items: center; justify-content: center; padding: 20px 16px; box-sizing: border-box; overflow-y: auto;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; overflow: hidden; margin: auto;">
        <div style="padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: #f8fafc;">
            <div style="font-size: 1.05rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 10px;">
                <div style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #eff6ff; border: 1px solid #bfdbfe; color: #2563eb;">
                    <i class="fa-solid fa-key" style="font-size: 1.15rem;"></i>
                </div>
                <span>Cấp lại Mật khẩu & Gửi Email Sinh viên</span>
            </div>
            <button type="button" onclick="closeChangePasswordModal()" style="background: none; border: none; font-size: 1.25rem; color: #64748b; cursor: pointer; padding: 4px;" title="Đóng">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <form action="<?= url('admin/students/reset-password') ?>" method="POST" onsubmit="return validateChangePasswordSubmit(event)" style="margin: 0;">
            <?= csrf_field() ?>
            <input type="hidden" name="maTaiKhoan" id="pwdModalMaTaiKhoan" value="">

            <div style="padding: 20px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                        <div>
                            <div style="font-weight: 700; font-size: 1.05rem; color: #0f172a;" id="pwdModalHoVaTen"></div>
                            <div style="font-size: 0.825rem; color: var(--text-muted); margin-top: 2px;">
                                MSSV: <code id="pwdModalMSSV" style="font-weight: 700; color: var(--primary);"></code>
                                &bull; <span id="pwdModalTruong"></span>
                            </div>
                        </div>
                        <span id="pwdModalEmailBadge" class="badge badge-success" style="font-size: 0.725rem;">Đã có Email</span>
                    </div>
                    <div style="margin-top: 10px; padding: 8px 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; font-size: 0.85rem; color: #1e40af; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-envelope" style="color: #2563eb;"></i>
                        <span>Email nhận mật khẩu: <b id="pwdModalEmailDisplay" style="color: #1e3a8a;"></b></span>
                    </div>
                    <div id="pwdModalEmailMissingBox" style="display: none; margin-top: 10px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 12px; font-size: 0.85rem; color: #92400e;">
                        <div style="font-weight: 600; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-triangle-exclamation"></i> Sinh viên chưa cập nhật Email trong hồ sơ!
                        </div>
                        <div style="font-size: 0.8rem; margin-bottom: 6px;">Vui lòng nhập email của sinh viên để hệ thống gửi mật khẩu và lưu vào hồ sơ:</div>
                        <input type="email" id="pwdModalEmailInput" name="emailNhan" placeholder="Nhập địa chỉ email của sinh viên..." class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem; background: #fff;">
                    </div>
                </div>
                <div style="background: #faf5ff; border: 1px solid #e9d5ff; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <label style="font-size: 0.85rem; font-weight: 700; color: #581c87; margin: 0; display: flex; align-items: center; gap: 6px;">Mật khẩu mới ngẫu nhiên (8 ký tự):
                        </label>
                        <button type="button" class="btn btn-outline btn-sm" onclick="generateNewRandomPassword()" style="font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; color: #7c3aed; border-color: #ddd6fe; background: #fff; cursor: pointer;" title="Tạo chuỗi mật khẩu 8 ký tự khác">
                            <i class="fa-solid fa-rotate" style="margin-right: 4px;"></i> Sinh chuỗi khác
                        </button>
                    </div>

                    <div style="display: flex; gap: 8px; align-items: center;">
                        <div style="position: relative; flex: 1;">
                            <input type="text" id="pwdModalNewInput" name="matKhauMoi" class="btn-outline" style="width: 100%; padding: 10px 14px; border-radius: 6px; font-size: 1.25rem; font-family: 'Courier New', Courier, monospace; font-weight: 800; color: #6b21a8; letter-spacing: 3px; background: #fff; text-align: center;" required>
                        </div>
                        <button type="button" class="btn btn-outline" onclick="copyPasswordToClipboard()" style="padding: 10px 14px; border-radius: 6px; font-size: 0.85rem; color: #475569; background: #fff; white-space: nowrap; cursor: pointer;" title="Sao chép mật khẩu">
                            <i class="fa-regular fa-copy" id="btnCopyIcon" style="margin-right: 4px;"></i> <span id="btnCopyText">Chép</span>
                        </button>
                    </div>
                </div>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 0.85rem; color: #166534; cursor: pointer; user-select: none; margin: 0;">
                        <input type="checkbox" id="pwdModalSendEmailCheck" name="sendEmail" value="1" checked style="margin-top: 3px; width: 16px; height: 16px; accent-color: #16a34a; cursor: pointer;">
                        <span>
                            <b style="color: #15803d; font-size: 0.875rem;">Gửi mật khẩu ngẫu nhiên này về Email của sinh viên</b><br>
                        </span>
                    </label>
                </div>
            </div>
            <div style="padding: 14px 20px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn btn-outline" onclick="closeChangePasswordModal()" style="border-radius: 6px; padding: 8px 18px;">
                    Hủy bỏ
                </button>
                <button type="submit" id="btnSubmitChangePassword" class="btn btn-primary" style="border-radius: 6px; padding: 8px 22px; font-weight: 600; background: #2563eb; border-color: #2563eb; color: #fff; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-paper-plane"></i> <span>Cấp lại & Gửi Email</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let studentCurrentPage = 1;
    let studentPageSize = 10;
    let studentMatchedRows = [];

    function filterStudents() {
        const searchVal = (document.getElementById('searchStudentInput')?.value || '').toLowerCase().trim();
        const truongVal = document.getElementById('filterStudentTruong')?.value || '';
        const genderVal = document.getElementById('filterStudentGender')?.value || '';
        const accStatusVal = document.getElementById('filterStudentAccStatus')?.value || '';
        const resStatusVal = document.getElementById('filterStudentResStatus')?.value || '';
        const roleVal = document.getElementById('filterStudentRole')?.value || '';

        const allRows = Array.from(document.querySelectorAll('.student-row'));
        studentMatchedRows = [];

        allRows.forEach(row => {
            const sTruong = row.getAttribute('data-truong') || '';
            const sGender = row.getAttribute('data-gender') || '';
            const sAccStatus = row.getAttribute('data-acc-status') || '';
            const sResStatus = row.getAttribute('data-res-status') || '';
            const sRole = row.getAttribute('data-role') || '';
            const sSearch = row.getAttribute('data-search') || '';

            const matchSearch = !searchVal || sSearch.includes(searchVal);
            const matchTruong = !truongVal || sTruong === truongVal;
            const matchGender = !genderVal || sGender === genderVal;
            const matchAccStatus = !accStatusVal || sAccStatus === accStatusVal;
            const matchResStatus = !resStatusVal || sResStatus === resStatusVal;
            const matchRole = !roleVal || sRole === roleVal;

            if (matchSearch && matchTruong && matchGender && matchAccStatus && matchResStatus && matchRole) {
                studentMatchedRows.push(row);
            }
        });

        studentCurrentPage = 1;
        renderStudentPagination();
    }

    function renderStudentPagination() {
        const allRows = document.querySelectorAll('.student-row');
        const total = studentMatchedRows.length;
        const totalPages = studentPageSize > 0 ? Math.ceil(total / studentPageSize) : 1;

        if (studentCurrentPage > totalPages) studentCurrentPage = totalPages;
        if (studentCurrentPage < 1) studentCurrentPage = 1;

        const startIdx = studentPageSize > 0 ? (studentCurrentPage - 1) * studentPageSize : 0;
        const endIdx = studentPageSize > 0 ? startIdx + studentPageSize : total;

        allRows.forEach(row => row.style.display = 'none');
        studentMatchedRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const noMatch = document.getElementById('noMatchStudentRow');
        if (noMatch) noMatch.style.display = (total === 0 && allRows.length > 0) ? '' : 'none';

        const totalBadge = document.getElementById('studentTotalBadge');
        if (totalBadge) totalBadge.innerText = total + ' sinh viên';
        const tabBadge = document.getElementById('tabCountStudent');
        if (tabBadge) tabBadge.innerText = total;

        const infoEl = document.getElementById('studentPaginationInfo');
        if (infoEl) {
            if (total === 0) {
                infoEl.innerHTML = 'Không có sinh viên nào phù hợp';
            } else {
                const displayStart = startIdx + 1;
                const displayEnd = Math.min(endIdx, total);
                infoEl.innerHTML = `Hiển thị <b>${displayStart} - ${displayEnd}</b> trên tổng số <b>${total}</b> sinh viên (Trang <b>${studentCurrentPage}</b> / <b>${totalPages}</b>)`;
            }
        }

        const controlsEl = document.getElementById('studentPaginationControls');
        if (!controlsEl) return;
        controlsEl.innerHTML = '';
        if (totalPages <= 1) return;

        const btnFirst = document.createElement('button');
        btnFirst.type = 'button';
        btnFirst.className = 'btn btn-outline btn-sm';
        btnFirst.innerHTML = '<i class="fa-solid fa-angles-left"></i>';
        btnFirst.disabled = (studentCurrentPage === 1);
        btnFirst.onclick = () => goToStudentPage(1);
        btnFirst.style.borderRadius = '6px';
        controlsEl.appendChild(btnFirst);

        const btnPrev = document.createElement('button');
        btnPrev.type = 'button';
        btnPrev.className = 'btn btn-outline btn-sm';
        btnPrev.innerHTML = '<i class="fa-solid fa-angle-left"></i>';
        btnPrev.disabled = (studentCurrentPage === 1);
        btnPrev.onclick = () => goToStudentPage(studentCurrentPage - 1);
        btnPrev.style.borderRadius = '6px';
        controlsEl.appendChild(btnPrev);

        let startPage = Math.max(1, studentCurrentPage - 2);
        let endPage = Math.min(totalPages, studentCurrentPage + 2);
        if (startPage > 1) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.padding = '0 4px';
            controlsEl.appendChild(dot);
        }
        for (let p = startPage; p <= endPage; p++) {
            const btnP = document.createElement('button');
            btnP.type = 'button';
            btnP.className = (p === studentCurrentPage) ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm';
            btnP.innerText = p;
            btnP.style.borderRadius = '6px';
            btnP.onclick = () => goToStudentPage(p);
            controlsEl.appendChild(btnP);
        }
        if (endPage < totalPages) {
            const dot = document.createElement('span');
            dot.innerText = '...';
            dot.style.padding = '0 4px';
            controlsEl.appendChild(dot);
        }

        const btnNext = document.createElement('button');
        btnNext.type = 'button';
        btnNext.className = 'btn btn-outline btn-sm';
        btnNext.innerHTML = '<i class="fa-solid fa-angle-right"></i>';
        btnNext.disabled = (studentCurrentPage === totalPages);
        btnNext.onclick = () => goToStudentPage(studentCurrentPage + 1);
        btnNext.style.borderRadius = '6px';
        controlsEl.appendChild(btnNext);

        const btnLast = document.createElement('button');
        btnLast.type = 'button';
        btnLast.className = 'btn btn-outline btn-sm';
        btnLast.innerHTML = '<i class="fa-solid fa-angles-right"></i>';
        btnLast.disabled = (studentCurrentPage === totalPages);
        btnLast.onclick = () => goToStudentPage(totalPages);
        btnLast.style.borderRadius = '6px';
        controlsEl.appendChild(btnLast);
    }

    function goToStudentPage(page) {
        studentCurrentPage = page;
        renderStudentPagination();
    }

    function changeStudentPageSize(val) {
        studentPageSize = parseInt(val, 10);
        studentCurrentPage = 1;
        renderStudentPagination();
    }

    function clearStudentFilters() {
        if (document.getElementById('searchStudentInput')) document.getElementById('searchStudentInput').value = '';
        if (document.getElementById('filterStudentTruong')) document.getElementById('filterStudentTruong').value = '';
        if (document.getElementById('filterStudentGender')) document.getElementById('filterStudentGender').value = '';
        if (document.getElementById('filterStudentAccStatus')) document.getElementById('filterStudentAccStatus').value = '';
        if (document.getElementById('filterStudentResStatus')) document.getElementById('filterStudentResStatus').value = '';
        if (document.getElementById('filterStudentRole')) document.getElementById('filterStudentRole').value = '';
        filterStudents();
    }

    function showCreateStudentForm() {
        resetStudentForm();
        const card = document.getElementById('studentFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }

    function hideStudentForm() {
        const card = document.getElementById('studentFormCard');
        if (card) card.style.display = 'none';
    }

    function resetStudentForm() {
        const form = document.getElementById('studentActionForm');
        if (form) form.reset();

        document.getElementById('studentMaTaiKhoan').value = '0';
        document.getElementById('formTitleText').innerText = 'Thêm mới hoặc Điều chỉnh thông tin Sinh viên';
        document.getElementById('btnSubmitStudentText').innerText = 'Lưu tài khoản Sinh viên';
        document.getElementById('noticeStudentEdit').style.display = 'none';
        document.getElementById('studentMaSinhVien').readOnly = false;

        const pwdBox = document.getElementById('boxStudentPassword');
        if (pwdBox) pwdBox.style.display = 'block';
        const matKhauInp = document.getElementById('studentMatKhau');
        if (matKhauInp) matKhauInp.value = '';
    }

    function editStudent(btn) {
        const raw = btn.getAttribute('data-item');
        if (!raw) return;
        const s = JSON.parse(raw);

        const card = document.getElementById('studentFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        document.getElementById('studentMaTaiKhoan').value = s.MaTaiKhoan || '0';
        document.getElementById('formTitleText').innerText = `Chỉnh sửa thông tin Sinh viên: ${s.HoVaTen} (${s.MaSinhVien})`;
        document.getElementById('btnSubmitStudentText').innerText = 'Cập nhật Sinh viên';
        document.getElementById('noticeStudentEdit').style.display = 'inline-block';

        const inpMsv = document.getElementById('studentMaSinhVien');
        inpMsv.value = s.MaSinhVien || '';
        inpMsv.readOnly = true;

        document.getElementById('studentHoVaTen').value = s.HoVaTen || '';
        document.getElementById('studentMaTruong').value = s.MaTruong || '';
        document.getElementById('studentGioiTinh').value = s.GioiTinh || 'Nam';
        document.getElementById('studentNgaySinh').value = s.NgaySinh || '';
        document.getElementById('studentSoCCCD').value = s.SoCCCD || '';
        document.getElementById('studentKhoaTuyenSinh').value = s.KhoaTuyenSinh || <?= date('Y') ?>;
        document.getElementById('studentSoDienThoai').value = s.SoDienThoai || '';
        document.getElementById('studentEmail').value = s.Email || '';
        document.getElementById('studentTrangThaiNoiTru').value = s.TrangThaiNoiTru || 'Chưa ở';
        document.getElementById('studentMaVaiTro').value = s.MaVaiTro || 'SINH_VIEN';

        const pwdBox = document.getElementById('boxStudentPassword');
        if (pwdBox) pwdBox.style.display = 'none';
    }

    function openToggleLockModal(btn) {
        let s = {};
        if (typeof btn === 'object' && btn !== null) {
            if (btn.getAttribute && btn.getAttribute('data-item')) {
                try {
                    s = JSON.parse(btn.getAttribute('data-item'));
                } catch (e) {
                    console.error('Lỗi đọc data-item:', e);
                }
            } else {
                s = btn;
            }
        }

        const maTaiKhoan = s.MaTaiKhoan || 0;
        const isCurrentlyActive = (s.DangHoatDong == 1);
        const targetStatus = isCurrentlyActive ? 0 : 1; // 0: Khóa, 1: Mở
        const name = s.HoVaTen || 'Sinh viên';
        const mssv = s.MaSinhVien || '---';
        const truong = s.TenTruong || s.MaTruong || '---';
        const phong = s.MaPhong ? ('Phòng ' + s.MaPhong + (s.KyHieuGiuong ? ' (' + s.KyHieuGiuong + ')' : '')) : 'Chưa xếp phòng';
        const sdt = s.SoDienThoai || 'Chưa cập nhật SĐT';

        document.getElementById('lockModalMaTaiKhoan').value = maTaiKhoan;
        document.getElementById('lockModalTargetStatus').value = targetStatus;
        document.getElementById('lockModalHoVaTen').innerText = name;
        document.getElementById('lockModalMSSV').innerText = mssv;
        document.getElementById('lockModalTruong').innerText = truong;
        document.getElementById('lockModalPhong').innerText = phong;
        document.getElementById('lockModalSDT').innerText = sdt;

        const iconCircle = document.getElementById('lockModalIconCircle');
        const title = document.getElementById('lockModalTitle');
        const currentBadge = document.getElementById('lockModalCurrentBadge');
        const noticeBox = document.getElementById('lockModalNotice');
        const submitBtn = document.getElementById('lockModalSubmitBtn');

        if (isCurrentlyActive) {
            title.innerText = 'Xác nhận Khóa tài khoản sinh viên';
            iconCircle.style.background = '#fffbeb';
            iconCircle.style.borderColor = '#fde68a';
            iconCircle.style.color = '#d97706';
            iconCircle.innerHTML = '<i class="fa-solid fa-lock" style="font-size: 1.15rem;"></i>';

            currentBadge.className = 'badge badge-success';
            currentBadge.innerHTML = '<i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Đang hoạt động';

            noticeBox.style.background = '#fffbeb';
            noticeBox.style.border = '1px solid #fde68a';
            noticeBox.style.color = '#92400e';
            noticeBox.innerHTML = `
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-triangle-exclamation"></i> Cảnh báo khi tạm khóa tài khoản:
                </div>
                <div>Sau khi khóa, sinh viên <b>không thể đăng nhập</b> vào cổng thông tin KTX, không thể nộp đơn hoặc thực hiện các giao dịch trực tuyến. Thông tin phòng ở và hợp đồng hiện tại vẫn được bảo lưu an toàn.</div>
            `;

            submitBtn.className = 'btn btn-warning';
            submitBtn.style.background = '#d97706';
            submitBtn.style.borderColor = '#d97706';
            submitBtn.style.color = '#ffffff';
            submitBtn.innerHTML = '<i class="fa-solid fa-lock" style="margin-right: 6px;"></i> Xác nhận Khóa tài khoản';
        } else {
            title.innerText = 'Xác nhận Mở khóa tài khoản sinh viên';
            iconCircle.style.background = '#f0fdf4';
            iconCircle.style.borderColor = '#bbf7d0';
            iconCircle.style.color = '#16a34a';
            iconCircle.innerHTML = '<i class="fa-solid fa-lock-open" style="font-size: 1.15rem;"></i>';

            currentBadge.className = 'badge badge-danger';
            currentBadge.innerHTML = '<i class="fa-solid fa-ban" style="margin-right: 4px;"></i> Đang bị khóa';

            noticeBox.style.background = '#f0fdf4';
            noticeBox.style.border = '1px solid #bbf7d0';
            noticeBox.style.color = '#166534';
            noticeBox.innerHTML = `
                <div style="font-weight: 700; margin-bottom: 4px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-circle-check"></i> Thông báo mở khóa:
                </div>
                <div>Tài khoản của sinh viên sẽ được kích hoạt trở lại ngay lập tức. Sinh viên có thể đăng nhập bình thường vào hệ thống với mật khẩu hiện tại.</div>
            `;

            submitBtn.className = 'btn btn-success';
            submitBtn.style.background = '#16a34a';
            submitBtn.style.borderColor = '#16a34a';
            submitBtn.style.color = '#ffffff';
            submitBtn.innerHTML = '<i class="fa-solid fa-lock-open" style="margin-right: 6px;"></i> Mở khóa ngay';
        }

        const modal = document.getElementById('lockStudentModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeLockStudentModal() {
        const modal = document.getElementById('lockStudentModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function openChangePasswordModal(btn) {
        let s = {};
        if (typeof btn === 'object' && btn !== null) {
            if (btn.getAttribute && btn.getAttribute('data-item')) {
                try {
                    s = JSON.parse(btn.getAttribute('data-item'));
                } catch (e) {
                    console.error('Lỗi đọc data-item:', e);
                }
            } else {
                s = btn;
            }
        }

        const maTaiKhoan = s.MaTaiKhoan || 0;
        const name = s.HoVaTen || 'Sinh viên';
        const mssv = s.MaSinhVien || '---';
        const truong = s.TenTruong || s.MaTruong || '---';
        const email = (s.Email || '').trim();

        document.getElementById('pwdModalMaTaiKhoan').value = maTaiKhoan;
        document.getElementById('pwdModalHoVaTen').innerText = name;
        document.getElementById('pwdModalMSSV').innerText = mssv;
        document.getElementById('pwdModalTruong').innerText = truong;

        generateNewRandomPassword();

        const emailDisplay = document.getElementById('pwdModalEmailDisplay');
        const emailBadge = document.getElementById('pwdModalEmailBadge');
        const emailMissingBox = document.getElementById('pwdModalEmailMissingBox');
        const emailInput = document.getElementById('pwdModalEmailInput');
        const sendEmailCheck = document.getElementById('pwdModalSendEmailCheck');

        if (sendEmailCheck) sendEmailCheck.checked = true;

        if (email && email !== '') {
            emailDisplay.innerText = email;
            emailBadge.style.display = 'inline-block';
            emailBadge.className = 'badge badge-success';
            emailBadge.innerText = 'Đã có Email';
            emailMissingBox.style.display = 'none';
            if (emailInput) {
                emailInput.value = email;
                emailInput.removeAttribute('required');
            }
        } else {
            emailDisplay.innerText = 'Chưa cập nhật Email trong hồ sơ';
            emailBadge.style.display = 'inline-block';
            emailBadge.className = 'badge badge-danger';
            emailBadge.innerText = 'Chưa có Email';
            emailMissingBox.style.display = 'block';
            if (emailInput) {
                emailInput.value = '';
                emailInput.setAttribute('required', 'required');
            }
        }

        const modal = document.getElementById('changePasswordStudentModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('changePasswordStudentModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    /**
     * Sinh chuỗi mật khẩu ngẫu nhiên đúng 8 ký tự (chữ hoa, chữ thường, số)
     * Tránh các ký tự dễ nhầm lẫn như: 0, O, 1, l, I
     */
    function generateNewRandomPassword() {
        const uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lowercase = 'abcdefghijkmnpqrstuvwxyz';
        const digits = '23456789';

        let res = [];
        res.push(uppercase.charAt(Math.floor(Math.random() * uppercase.length)));
        res.push(lowercase.charAt(Math.floor(Math.random() * lowercase.length)));
        res.push(digits.charAt(Math.floor(Math.random() * digits.length)));

        const combined = uppercase + lowercase + digits;
        for (let i = 3; i < 8; i++) {
            res.push(combined.charAt(Math.floor(Math.random() * combined.length)));
        }

        res.sort(() => Math.random() - 0.5);

        const pass = res.join('');
        const input = document.getElementById('pwdModalNewInput');
        if (input) input.value = pass;

        const copyBtn = document.getElementById('btnCopyText');
        if (copyBtn) copyBtn.innerText = 'Chép';
    }

    function copyPasswordToClipboard() {
        const input = document.getElementById('pwdModalNewInput');
        if (!input || !input.value) return;

        navigator.clipboard.writeText(input.value).then(() => {
            const copyBtn = document.getElementById('btnCopyText');
            if (copyBtn) copyBtn.innerText = 'Đã chép!';
            setTimeout(() => {
                if (copyBtn) copyBtn.innerText = 'Chép';
            }, 2000);
        }).catch(err => {
            input.select();
            document.execCommand('copy');
            const copyBtn = document.getElementById('btnCopyText');
            if (copyBtn) copyBtn.innerText = 'Đã chép!';
        });
    }

    function validateChangePasswordSubmit(event) {
        const input = document.getElementById('pwdModalNewInput');
        if (!input || !input.value.trim() || input.value.trim().length < 6) {
            event.preventDefault();
            alert('Mật khẩu mới không hợp lệ hoặc dưới 6 ký tự!');
            return false;
        }

        const emailMissingBox = document.getElementById('pwdModalEmailMissingBox');
        const emailInput = document.getElementById('pwdModalEmailInput');
        const sendEmailCheck = document.getElementById('pwdModalSendEmailCheck');

        if (sendEmailCheck && sendEmailCheck.checked && emailMissingBox && emailMissingBox.style.display !== 'none') {
            if (!emailInput || !emailInput.value.trim()) {
                if (!confirm('Sinh viên chưa có email trong hồ sơ và bạn chưa nhập email nhận mật khẩu. Bạn có chắc chắn muốn đổi mật khẩu mà KHÔNG gửi email?')) {
                    event.preventDefault();
                    if (emailInput) emailInput.focus();
                    return false;
                }
            }
        }

        const submitBtn = document.getElementById('btnSubmitChangePassword');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Đang tạo & Gửi email...';
        }

        return true;
    }

    function confirmDeleteStudent(btn) {
        let s = {};
        if (typeof btn === 'object' && btn !== null) {
            if (btn.getAttribute && btn.getAttribute('data-item')) {
                try {
                    s = JSON.parse(btn.getAttribute('data-item'));
                } catch (e) {
                    console.error('Lỗi parse data-item:', e);
                }
            } else {
                s = btn;
            }
        }

        const name = s.HoVaTen || 'sinh viên';
        const mssv = s.MaSinhVien || '';
        const maTaiKhoan = s.MaTaiKhoan || 0;

        if (!maTaiKhoan) return;

        if (typeof openConfirmModal === 'function') {
            openConfirmModal({
                title: 'Xác nhận xóa tài khoản sinh viên',
                icon: 'fa-solid fa-trash-can',
                iconColor: '#dc2626',
                message: `Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản và hồ sơ của sinh viên <b>${name}</b> (<code style="color:var(--primary); font-weight:700;">${mssv}</code>)?`,
                note: '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px; color:#dc2626;"></i> <b>Ràng buộc an toàn:</b> Hệ thống sẽ chặn xóa nếu sinh viên đang có hợp đồng nội trú hiệu lực hoặc hóa đơn lệ phí chưa thanh toán.',
                btnText: 'Xác nhận xóa',
                btnClass: 'btn btn-danger',
                btnIcon: 'fa-solid fa-trash-can',
                action: '<?= url("admin/students/delete") ?>',
                inputs: {
                    maTaiKhoan: maTaiKhoan
                }
            });
        } else {
            if (confirm(`CẢNH BÁO: Bạn có chắc chắn muốn xóa tài khoản sinh viên "${name}" (${mssv})?`)) {
                const f = document.createElement('form');
                f.method = 'POST';
                f.action = '<?= url("admin/students/delete") ?>';
                f.style.display = 'none';
                f.innerHTML = `<?= csrf_field() ?><input type="hidden" name="maTaiKhoan" value="${maTaiKhoan}">`;
                document.body.appendChild(f);
                f.submit();
            }
        }
    }

    function triggerResetPassword(id, name, mssv) {
        openChangePasswordModal({
            MaTaiKhoan: id,
            HoVaTen: name,
            MaSinhVien: mssv
        });
    }

    function triggerToggleStatus(id, name, currentStatus) {
        openToggleLockModal({
            MaTaiKhoan: id,
            HoVaTen: name,
            DangHoatDong: currentStatus
        });
    }

    function triggerDeleteStudent(id, name, mssv) {
        confirmDeleteStudent({
            MaTaiKhoan: id,
            HoVaTen: name,
            MaSinhVien: mssv
        });
    }

    function openImportStudentModal() {
        const modal = document.getElementById('importStudentModal');
        if (modal) {
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    }

    function closeImportStudentModal() {
        const modal = document.getElementById('importStudentModal');
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }

    function exportFilteredStudents() {
        const matchedIds = [];
        studentMatchedRows.forEach(row => {
            const id = row.getAttribute('data-id');
            if (id) matchedIds.push(id);
        });

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?= url("admin/students/export") ?>';
        form.style.display = 'none';

        const csrfInp = document.createElement('input');
        csrfInp.type = 'hidden';
        csrfInp.name = 'csrf_token';
        csrfInp.value = '<?= csrf_token() ?>';
        form.appendChild(csrfInp);

        if (matchedIds.length > 0) {
            const idsInp = document.createElement('input');
            idsInp.type = 'hidden';
            idsInp.name = 'ids';
            idsInp.value = matchedIds.join(',');
            form.appendChild(idsInp);
        } else {
            const truongVal = document.getElementById('filterStudentTruong')?.value || '';
            const genderVal = document.getElementById('filterStudentGender')?.value || '';
            const accStatusVal = document.getElementById('filterStudentAccStatus')?.value || '';
            const resStatusVal = document.getElementById('filterStudentResStatus')?.value || '';
            const roleVal = document.getElementById('filterStudentRole')?.value || '';
            const searchVal = document.getElementById('searchStudentInput')?.value || '';

            if (truongVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'maTruong';
                i.value = truongVal;
                form.appendChild(i);
            }
            if (genderVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'gioiTinh';
                i.value = genderVal;
                form.appendChild(i);
            }
            if (accStatusVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'trangThaiTaiKhoan';
                i.value = accStatusVal;
                form.appendChild(i);
            }
            if (resStatusVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'trangThaiNoiTru';
                i.value = resStatusVal;
                form.appendChild(i);
            }
            if (roleVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'maVaiTro';
                i.value = roleVal;
                form.appendChild(i);
            }
            if (searchVal) {
                const i = document.createElement('input');
                i.type = 'hidden';
                i.name = 'keyword';
                i.value = searchVal;
                form.appendChild(i);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }

    document.addEventListener('DOMContentLoaded', function() {
        filterStudents();
    });

    window.addEventListener('click', function(e) {
        const importModal = document.getElementById('importStudentModal');
        if (e.target === importModal) closeImportStudentModal();

        const lockModal = document.getElementById('lockStudentModal');
        if (e.target === lockModal) closeLockStudentModal();

        const pwdModal = document.getElementById('changePasswordStudentModal');
        if (e.target === pwdModal) closeChangePasswordModal();
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImportStudentModal();
            closeLockStudentModal();
            closeChangePasswordModal();
        }
    });
</script>

<?php
$content = ob_get_clean();
$title = "Quản lý Tài khoản Sinh viên - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>