<?php
ob_start();
?>

<div class="container">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px;">
        <div style="display: flex; gap: 8px; font-size: 0.85rem; color: var(--text-muted); margin-bottom: 8px;">
            <a href="<?= url('admin/dashboard') ?>" style="color: var(--text-muted); text-decoration: none;">Quản trị hệ thống</a>
            <span>/</span>
            <span style="color: var(--text-primary); font-weight: 500;">Trường Đại học liên kết & Thành viên</span>
        </div>

        <div class="page-header" style="align-items: center; flex-wrap: wrap; gap: 12px;">
            <div>
                <h1 class="page-title">Quản lý Trường Đại học liên kết & Thành viên</h1>
                <p class="page-subtitle">
                    <strong>Chức năng phân hệ:</strong> Quản trị danh mục các trường đại học thành viên ĐHQG-HCM và các trường đối tác, cấu hình thời gian đào tạo chuẩn và trạng thái hợp tác tiếp nhận sinh viên nội trú.
                </p>
            </div>
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <a href="<?= url('admin/dashboard') ?>" class="btn btn-outline" style="border-radius: 8px;">
                    &larr; Về trang Quản trị trung tâm
                </a>
            </div>
        </div>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i> <b>Thành công:</b> <?= htmlspecialchars($flashSuccess) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashWarning)): ?>
        <div class="alert alert-warning" style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <b>Cảnh báo:</b> <?= htmlspecialchars($flashWarning) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
        <div class="alert alert-danger" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <i class="fa-solid fa-circle-xmark" style="margin-right: 6px;"></i> <b>Lỗi:</b> <?= htmlspecialchars($flashError) ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
        <button type="button"
            class="btn btn-primary"
            id="btnToggleAddUni"
            onclick="toggleUniversityForm()"
            style="border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center;">
            <i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i>
            Thêm trường mới
        </button>
    </div>

    <!-- BIỂU MẪU CRUD: THÊM / CẬP NHẬT TRƯỜNG ĐẠI HỌC (ẨN MẶC ĐỊNH, CHỈ HIỆN KHI CLICK BUTTON HOẶC SỬA) -->
    <div class="card" id="universityFormCard" style="<?= !empty($flashError) ? 'display: block;' : 'display: none;' ?> margin-bottom: 24px; border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <h3 id="formTitle" style="font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 8px;"></i>
                <span id="formTitleText">Thêm mới thông tin Trường Đại học</span>
            </h3>
            <button type="button" class="btn btn-outline btn-sm" onclick="hideUniversityForm()" style="border-radius: 6px; padding: 4px 10px; color: var(--text-muted);" title="Đóng biểu mẫu">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="universityActionForm" action="<?= url('admin/universities/save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" id="isEdit" name="isEdit" value="0">

            <div class="grid grid-cols-2" style="gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Mã trường đại học <span style="color:#dc2626;">*</span>
                        <span id="maTruongLockNotice" style="display: none; font-size: 0.775rem; color: #b45309; font-weight: normal; margin-left: 6px;">
                            <i class="fa-solid fa-lock" style="margin-right: 4px;"></i> Cố định khi sửa
                        </span>
                    </label>
                    <input type="text" id="maTruong" name="maTruong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem; text-transform: uppercase;" placeholder="Ví dụ: BK, KHTN, UIT, USSH, UEL, IU..." required maxlength="20">
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Tên đầy đủ trường đại học <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="text" id="tenTruong" name="tenTruong" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: Trường Đại học Bách khoa - ĐHQG-HCM" required maxlength="150">
                </div>
            </div>

            <div class="grid grid-cols-3" style="gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Thời gian đào tạo chuẩn (Năm) <span style="color:#dc2626;">*</span>
                    </label>
                    <input type="number" id="soNamDaoTaoChuan" name="soNamDaoTaoChuan" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" placeholder="Ví dụ: 4.0 hoặc 4.5" step="0.5" min="1.0" max="10.0" value="4.0" required>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Phân loại đơn vị <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="thuocDHQGHCM" name="thuocDHQGHCM" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                        <option value="1">Trường thành viên ĐHQG-HCM (Ưu tiên xét tuyển)</option>
                        <option value="0">Trường đại học ngoài ĐHQG-HCM (Liên kết)</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px;">
                        Trạng thái hợp tác <span style="color:#dc2626;">*</span>
                    </label>
                    <select id="dangHopTac" name="dangHopTac" class="btn-outline" style="width: 100%; padding: 10px; border-radius: 6px; font-size: 0.9rem;" required>
                        <option value="1">Đang hợp tác (Tiếp nhận hồ sơ SV)</option>
                        <option value="0">Tạm dừng (Không tiếp nhận SV mới)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <button type="submit" id="btnSubmitForm" class="btn btn-primary" style="border-radius: 6px; padding: 10px 22px; font-weight: 600;">
                        <i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i>
                        <span id="btnSubmitText">Lưu trường Đại học</span>
                    </button>
                    <button type="button" id="btnCancelEdit" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="resetUniversityForm()">
                        <i class="fa-solid fa-rotate-left" style="margin-right: 6px;"></i> Làm lại
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-outline" style="border-radius: 6px; padding: 10px 18px;" onclick="hideUniversityForm()">
                        <i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng biểu mẫu
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- ===================================================================== -->
    <!-- BỘ LỌC TÌM KIẾM ĐA TIÊU CHÍ (SEARCH & ADVANCED FILTERS) -->
    <!-- ===================================================================== -->
    <div class="card" style="margin-bottom: 22px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 16px 20px;">
        <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem; margin-bottom: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-filter" style="color: var(--primary); margin-right: 4px;"></i>
                <span>Tìm kiếm Trường Đại học</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <span id="filterSummaryBadge" class="badge badge-info" style="font-size: 0.775rem;">
                    Hiển thị tất cả (<?= count($universityList ?? []) ?> trường)
                </span>
            </div>
        </div>

        <div class="grid grid-cols-4" style="gap: 12px; align-items: flex-end;">
            <!-- 1. TÌM KIẾM THEO TỪ KHÓA -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    1. Từ khóa (Mã hoặc Tên trường):
                </label>
                <div style="position: relative;">
                    <input type="text" id="filterKeyword" class="btn-outline" style="width: 100%; padding: 8px 10px 8px 30px; border-radius: 6px; font-size: 0.85rem;" placeholder="Tìm BK, KHTN, Bách khoa..." oninput="filterUniversities()">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                </div>
            </div>

            <!-- 2. PHÂN LOẠI THÀNH VIÊN -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    2. Phân loại đơn vị:
                </label>
                <select id="filterCategory" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="filterUniversities()">
                    <option value="">-- Tất cả phân loại --</option>
                    <option value="1">Thành viên ĐHQG-HCM</option>
                    <option value="0">Ngoài ĐHQG-HCM (Liên kết)</option>
                </select>
            </div>

            <!-- 3. TRẠNG THÁI HỢP TÁC -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    3. Trạng thái tiếp nhận:
                </label>
                <select id="filterStatus" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="filterUniversities()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="1">Đang hợp tác tiếp nhận</option>
                    <option value="0">Tạm dừng tiếp nhận</option>
                </select>
            </div>

            <!-- 4. THỜI GIAN ĐÀO TẠO CHUẨN -->
            <div>
                <label style="display: block; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 4px;">
                    4. Thời gian đào tạo:
                </label>
                <select id="filterDuration" class="btn-outline" style="width: 100%; padding: 8px 10px; border-radius: 6px; font-size: 0.85rem;" onchange="filterUniversities()">
                    <option value="">-- Tất cả thời gian --</option>
                    <option value="4.0">4.0 năm chuẩn</option>
                    <option value="4.5">4.5 năm chuẩn</option>
                    <option value="5.0">5.0 năm trở lên</option>
                </select>
            </div>
        </div>

        <!-- HÀNG PHỤ: LỌC THEO SINH VIÊN KTX & NÚT XÓA BỘ LỌC -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px; padding-top: 12px; border-top: 1px dashed #cbd5e1; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; gap: 18px; align-items: center; flex-wrap: wrap;">
                <span style="font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">
                    <i class="fa-solid fa-user-graduate" style="margin-right: 4px;"></i> Sinh viên nội trú:
                </span>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; color: var(--text-primary);">
                    <input type="radio" name="filterStudentRadio" value="" checked onchange="filterUniversities()">
                    Tất cả
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; color: var(--text-primary);">
                    <input type="radio" name="filterStudentRadio" value="has_students" onchange="filterUniversities()">
                    Đang có sinh viên (&gt; 0)
                </label>
                <label style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; font-weight: 500; cursor: pointer; color: var(--text-primary);">
                    <input type="radio" name="filterStudentRadio" value="no_students" onchange="filterUniversities()">
                    Chưa có sinh viên (0 SV)
                </label>
            </div>

            <div>
                <button type="button" class="btn btn-outline btn-sm" onclick="clearUniversityFilters()" style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff; display: inline-flex; align-items: center; gap: 6px; font-weight: 600; cursor: pointer;">
                    <i class="fa-solid fa-filter-circle-xmark" style="margin-right: 4px;"></i> Xóa bộ lọc
                </button>
            </div>
        </div>
    </div>

    <!-- BẢNG DANH MỤC TRƯỜNG ĐẠI HỌC (CRUD TABLE) -->
    <div class="card">
        <div class="card-title" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-building-columns" style="color: var(--primary); margin-right: 4px;"></i>
                <span>Danh sách các trường đại học thành viên & liên kết</span>
                <span class="badge badge-info" id="uniCountBadge"><?= count($universityList ?? []) ?> trường</span>
            </div>
        </div>

        <?php if (!empty($universityList)): ?>
            <div class="table-responsive">
                <table class="data-table" id="tableUniversities">
                    <thead>
                        <tr>
                            <th>Mã trường</th>
                            <th>Tên trường đại học</th>
                            <th>Phân loại thành viên</th>
                            <th>Thời gian chuẩn</th>
                            <th>Sinh viên KTX</th>
                            <th>Trạng thái hợp tác</th>
                            <th style="text-align: right;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($universityList as $u): ?>
                            <?php
                            $isDHQG = ((int)($u['ThuocDHQGHCM'] ?? 0)) === 1;
                            $isPaused = ((int)($u['DangHopTac'] ?? 1)) === 0;
                            $svCount = (int)($u['TongSinhVien'] ?? 0);
                            $duration = (float)($u['SoNamDaoTaoChuan'] ?? 4.0);
                            ?>
                            <tr class="uni-row"
                                data-matruong="<?= htmlspecialchars($u['MaTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                data-tentruong="<?= htmlspecialchars($u['TenTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                data-dhqg="<?= $isDHQG ? '1' : '0' ?>"
                                data-danghoptac="<?= $isPaused ? '0' : '1' ?>"
                                data-duration="<?= $duration ?>"
                                data-students="<?= $svCount ?>"
                                style="<?= $isPaused ? 'background: #fef2f2;' : '' ?>">
                                <td>
                                    <b style="color: var(--primary); font-size: 0.95rem;"><?= htmlspecialchars($u['MaTruong']) ?></b>
                                </td>
                                <td>
                                    <b><?= htmlspecialchars($u['TenTruong']) ?></b>
                                </td>
                                <td>
                                    <?php if ($isDHQG): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-check" style="margin-right: 4px;"></i> Thành viên ĐHQG-HCM
                                        </span>
                                    <?php else: ?>
                                        <span class="badge" style="background: #f1f5f9; color: #475569;">
                                            <i class="fa-solid fa-handshake" style="margin-right: 4px;"></i> Ngoài ĐHQG-HCM
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($u['SoNamDaoTaoChuan']) ?> năm
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        <i class="fa-solid fa-user-graduate" style="margin-right: 4px;"></i> <?= number_format($svCount) ?> sinh viên
                                    </span>
                                </td>
                                <td>
                                    <?php if (!$isPaused): ?>
                                        <span class="badge badge-success">
                                            <i class="fa-solid fa-circle-dot" style="margin-right: 4px;"></i> Đang hợp tác
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">
                                            <i class="fa-solid fa-pause" style="margin-right: 4px;"></i> Tạm dừng
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: right; white-space: nowrap;">
                                    <!-- NÚT SỬA (UPDATE) -->
                                    <button type="button" class="btn btn-outline btn-sm"
                                        style="border-radius: 6px; margin-right: 4px;"
                                        data-item="<?= htmlspecialchars(json_encode($u, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                        onclick="editUniversity(this)"
                                        title="Chỉnh sửa thông tin trường">
                                        <i class="fa-solid fa-pen-to-square" style="margin-right: 6px;"></i> Sửa
                                    </button>

                                    <!-- NÚT TIẾP TỤC / TẠM DỪNG HỢP TÁC -->
                                    <button type="button" class="btn btn-outline btn-sm"
                                        style="border-radius: 6px; margin-right: 4px; <?= $isPaused ? 'color: #15803d; border-color: #86efac;' : 'color: #b45309; border-color: #fde68a;' ?>"
                                        data-matruong="<?= htmlspecialchars($u['MaTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-tentruong="<?= htmlspecialchars($u['TenTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-paused="<?= $isPaused ? '1' : '0' ?>"
                                        onclick="handleToggleUniversity(this)"
                                        title="<?= $isPaused ? 'Tiếp tục hợp tác tiếp nhận' : 'Tạm dừng hợp tác' ?>">
                                        <i class="fa-solid <?= $isPaused ? 'fa-play' : 'fa-pause' ?>" style="margin-right: 6px;"></i> <?= $isPaused ? 'Tiếp nhận' : 'Tạm dừng' ?>
                                    </button>

                                    <!-- NÚT XÓA -->
                                    <button type="button" class="btn btn-outline btn-sm"
                                        style="border-radius: 6px; color: #dc2626; border-color: #fecaca; background: #fff;"
                                        data-matruong="<?= htmlspecialchars($u['MaTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-tentruong="<?= htmlspecialchars($u['TenTruong'], ENT_QUOTES, 'UTF-8') ?>"
                                        data-count="<?= $svCount ?>"
                                        onclick="handleDeleteUniversity(this)"
                                        title="Xóa trường đại học">
                                        <i class="fa-solid fa-trash-can" style="margin-right: 6px;"></i> Xóa
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr id="noMatchRow" style="display: none;">
                            <td colspan="7" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                <i class="fa-solid fa-circle-info" style="margin-right: 6px;"></i> Không tìm thấy trường Đại học nào phù hợp với tiêu chí lọc.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted" style="font-size: 0.9rem; padding: 20px 0;">Chưa có danh mục trường đại học nào trong hệ thống.</p>
        <?php endif; ?>
    </div>
</div>

<!-- JAVASCRIPT XỬ LÝ CRUD & TƯƠNG TÁC GIAO DIỆN -->
<script>
    // 1. Toggle & hiển thị Form Thêm mới
    function showCreateUniversityForm() {
        resetUniversityForm();
        const card = document.getElementById('universityFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        const btn = document.getElementById('btnToggleAddUni');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-xmark" style="margin-right: 6px;"></i> Đóng form';
            btn.className = 'btn btn-outline';
        }
    }

    function hideUniversityForm() {
        const card = document.getElementById('universityFormCard');
        if (card) {
            card.style.display = 'none';
        }
        const btn = document.getElementById('btnToggleAddUni');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm trường mới';
            btn.className = 'btn btn-primary';
        }
    }

    function toggleUniversityForm() {
        const card = document.getElementById('universityFormCard');
        if (!card) return;
        const isHidden = (card.style.display === 'none' || card.style.display === '');
        if (isHidden) {
            showCreateUniversityForm();
        } else {
            const isEdit = document.getElementById('isEdit').value === '1';
            if (isEdit) {
                // Đang ở chế độ sửa thì chuyển về thêm mới
                showCreateUniversityForm();
            } else {
                hideUniversityForm();
            }
        }
    }

    // 2. Chuyển form sang chế độ Chỉnh sửa (Update)
    function editUniversity(el) {
        let uni = el;
        if (el instanceof HTMLElement) {
            const raw = el.getAttribute('data-item');
            uni = raw ? JSON.parse(raw) : {};
        } else if (typeof el === 'string') {
            uni = JSON.parse(el);
        }

        document.getElementById('isEdit').value = '1';

        const maTruongInput = document.getElementById('maTruong');
        maTruongInput.value = uni.MaTruong || '';
        maTruongInput.readOnly = true;
        maTruongInput.style.backgroundColor = '#f1f5f9';
        maTruongInput.style.cursor = 'not-allowed';
        document.getElementById('maTruongLockNotice').style.display = 'inline';

        document.getElementById('tenTruong').value = uni.TenTruong || '';
        document.getElementById('soNamDaoTaoChuan').value = uni.SoNamDaoTaoChuan || 4.0;
        document.getElementById('thuocDHQGHCM').value = (uni.ThuocDHQGHCM !== undefined) ? uni.ThuocDHQGHCM : '1';
        document.getElementById('dangHopTac').value = (uni.DangHopTac !== undefined) ? uni.DangHopTac : '1';

        document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-pen-to-square" style="color: var(--primary); margin-right: 8px;"></i> <span>Chỉnh sửa thông tin Trường: ' + (uni.TenTruong || uni.MaTruong) + ' (' + uni.MaTruong + ')</span>';
        document.getElementById('btnSubmitForm').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Cập nhật trường';

        // Hiển thị biểu mẫu nếu đang ẩn
        const card = document.getElementById('universityFormCard');
        if (card) {
            card.style.display = 'block';
            card.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        const btn = document.getElementById('btnToggleAddUni');
        if (btn) {
            btn.innerHTML = '<i class="fa-solid fa-plus-circle" style="margin-right: 6px;"></i> + Thêm trường mới';
            btn.className = 'btn btn-primary';
        }
    }

    // 3. Làm mới form về trạng thái Thêm mới (Create)
    function resetUniversityForm() {
        document.getElementById('universityActionForm').reset();
        document.getElementById('isEdit').value = '0';

        const maTruongInput = document.getElementById('maTruong');
        maTruongInput.readOnly = false;
        maTruongInput.style.backgroundColor = '';
        maTruongInput.style.cursor = '';
        document.getElementById('maTruongLockNotice').style.display = 'none';

        document.getElementById('soNamDaoTaoChuan').value = '4.0';
        document.getElementById('thuocDHQGHCM').value = '1';
        document.getElementById('dangHopTac').value = '1';

        document.getElementById('formTitle').innerHTML = '<i class="fa-solid fa-graduation-cap" style="color: var(--primary); margin-right: 8px;"></i> <span>Thêm mới thông tin Trường Đại học</span>';
        document.getElementById('btnSubmitForm').innerHTML = '<i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Lưu trường Đại học';
    }

    // 4. Modal xác nhận Tạm dừng / Tiếp tục hợp tác (Tái sử dụng Component confirm_modal)
    function handleToggleUniversity(btn) {
        const maTruong = btn.getAttribute('data-matruong');
        const tenTruong = btn.getAttribute('data-tentruong') || maTruong;
        const isPaused = btn.getAttribute('data-paused') === '1';
        confirmToggleUniversity(maTruong, tenTruong, isPaused);
    }

    function confirmToggleUniversity(maTruong, tenTruong, isPaused) {
        const actionText = isPaused ? 'tiếp tục hợp tác tiếp nhận' : 'tạm dừng tiếp nhận sinh viên';
        if (typeof openConfirmModal !== 'function') {
            if (confirm(`Bạn có chắc chắn muốn ${actionText} đối với trường ${tenTruong} (${maTruong})?`)) {
                submitActionForm('<?= url("admin/universities/toggle-status") ?>', {
                    maTruong: maTruong
                });
            }
            return;
        }

        openConfirmModal({
            title: isPaused ? 'Xác nhận tiếp tục hợp tác tiếp nhận' : 'Xác nhận tạm dừng tiếp nhận sinh viên',
            message: `Bạn có chắc chắn muốn <b>${actionText}</b> đối với trường đại học <b>${tenTruong}</b> (<code style="color:var(--primary); font-weight:700;">${maTruong}</code>)?`,
            note: isPaused ?
                '<i class="fa-solid fa-circle-check" style="color:#15803d; margin-right: 6px;"></i> Sau khi tiếp tục hợp tác, sinh viên từ trường này sẽ có thể đăng ký xét tuyển lưu trú nội trú trong các đợt mở cổng.' : '<i class="fa-solid fa-triangle-exclamation" style="color:#b45309; margin-right: 6px;"></i> Khi tạm dừng hợp tác, hệ thống sẽ tạm khóa tiếp nhận đăng ký mới từ sinh viên thuộc trường này (các hồ sơ và hợp đồng cũ vẫn được duy trì).',
            btnText: isPaused ? 'Tiếp nhận ngay' : 'Tạm dừng tiếp nhận',
            btnClass: isPaused ? 'btn btn-success' : 'btn btn-warning',
            btnIcon: isPaused ? 'fa-solid fa-play' : 'fa-solid fa-pause',
            icon: isPaused ? 'fa-solid fa-circle-check' : 'fa-solid fa-triangle-exclamation',
            iconColor: isPaused ? '#15803d' : '#d97706',
            action: '<?= url("admin/universities/toggle-status") ?>',
            inputs: {
                maTruong: maTruong
            }
        });
    }

    // 5. Modal xác nhận Xóa trường đại học (Tái sử dụng Component confirm_modal)
    function handleDeleteUniversity(btn) {
        const maTruong = btn.getAttribute('data-matruong');
        const tenTruong = btn.getAttribute('data-tentruong') || maTruong;
        const count = parseInt(btn.getAttribute('data-count') || '0', 10);
        confirmDeleteUniversity(maTruong, tenTruong, count);
    }

    function confirmDeleteUniversity(maTruong, tenTruong, count) {
        if (typeof openConfirmModal !== 'function') {
            if (confirm(`Bạn có chắc chắn muốn xóa trường đại học ${tenTruong} (${maTruong})?`)) {
                submitActionForm('<?= url("admin/universities/delete") ?>', {
                    maTruong: maTruong
                });
            }
            return;
        }

        let noteHtml = '<i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> Thao tác xóa sẽ loại bỏ trường khỏi danh sách liên kết đào tạo.';
        if (count > 0) {
            noteHtml = `<i class="fa-solid fa-shield-halved" style="color:#b45309; margin-right: 6px;"></i> <b>Quy tắc bảo toàn dữ liệu:</b> Trường này đang có <b>${count} sinh viên</b> trong KTX. Hệ thống sẽ <b>không xóa vật lý</b> để bảo toàn lịch sử hồ sơ, mà sẽ tự động chuyển sang trạng thái <b>TẠM DỪNG TIẾP NHẬN</b>.`;
        }

        openConfirmModal({
            title: 'Xác nhận xóa trường đại học',
            message: `Bạn có chắc chắn muốn xóa trường đại học <b>${tenTruong}</b> (<code style="color:var(--primary); font-weight:700;">${maTruong}</code>)?`,
            note: noteHtml,
            btnText: 'Xác nhận xóa',
            btnClass: 'btn btn-danger',
            btnIcon: 'fa-solid fa-trash-can',
            icon: 'fa-solid fa-trash-can',
            iconColor: '#dc2626',
            action: '<?= url("admin/universities/delete") ?>',
            inputs: {
                maTruong: maTruong
            }
        });
    }

    // 6. Submit Form qua Javascript kèm CSRF token
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

    // 7. Bộ lọc đa tiêu chí cho danh sách trường đại học
    function filterUniversities() {
        const keyword = (document.getElementById('filterKeyword')?.value || '').trim().toLowerCase();
        const category = document.getElementById('filterCategory')?.value || '';
        const status = document.getElementById('filterStatus')?.value || '';
        const duration = document.getElementById('filterDuration')?.value || '';
        const studentRadio = document.querySelector('input[name="filterStudentRadio"]:checked')?.value || '';

        const rows = document.querySelectorAll('.uni-row');
        let matched = 0;
        const total = rows.length;

        rows.forEach(row => {
            const ma = (row.getAttribute('data-matruong') || '').toLowerCase();
            const ten = (row.getAttribute('data-tentruong') || '').toLowerCase();
            const dhqg = row.getAttribute('data-dhqg') || '';
            const danghoptac = row.getAttribute('data-danghoptac') || '';
            const dur = parseFloat(row.getAttribute('data-duration') || '0');
            const students = parseInt(row.getAttribute('data-students') || '0', 10);

            let isMatch = true;

            // 1. Keyword search (mã hoặc tên trường)
            if (keyword && !ma.includes(keyword) && !ten.includes(keyword)) {
                isMatch = false;
            }

            // 2. Phân loại thành viên
            if (isMatch && category !== '' && dhqg !== category) {
                isMatch = false;
            }

            // 3. Trạng thái tiếp nhận
            if (isMatch && status !== '' && danghoptac !== status) {
                isMatch = false;
            }

            // 4. Thời gian đào tạo chuẩn
            if (isMatch && duration !== '') {
                const reqDur = parseFloat(duration);
                if (reqDur === 5.0) {
                    if (dur < 5.0) isMatch = false;
                } else {
                    if (dur !== reqDur) isMatch = false;
                }
            }

            // 5. Số lượng sinh viên nội trú
            if (isMatch && studentRadio === 'has_students' && students <= 0) {
                isMatch = false;
            }
            if (isMatch && studentRadio === 'no_students' && students > 0) {
                isMatch = false;
            }

            if (isMatch) {
                row.style.display = '';
                matched++;
            } else {
                row.style.display = 'none';
            }
        });

        const noMatchRow = document.getElementById('noMatchRow');
        if (noMatchRow) {
            noMatchRow.style.display = (matched === 0 && total > 0) ? '' : 'none';
        }

        const countBadge = document.getElementById('uniCountBadge');
        if (countBadge) {
            countBadge.innerText = matched + ' trường';
        }

        const summaryBadge = document.getElementById('filterSummaryBadge');
        if (summaryBadge) {
            if (matched === total) {
                summaryBadge.innerText = `Hiển thị tất cả (${total} trường)`;
                summaryBadge.className = 'badge badge-info';
            } else {
                summaryBadge.innerText = `Tìm thấy ${matched} / ${total} trường`;
                summaryBadge.className = 'badge badge-warning';
            }
        }
    }

    // 8. Đặt lại toàn bộ bộ lọc về mặc định
    function clearUniversityFilters() {
        if (document.getElementById('filterKeyword')) document.getElementById('filterKeyword').value = '';
        if (document.getElementById('filterCategory')) document.getElementById('filterCategory').value = '';
        if (document.getElementById('filterStatus')) document.getElementById('filterStatus').value = '';
        if (document.getElementById('filterDuration')) document.getElementById('filterDuration').value = '';
        const allRadio = document.querySelector('input[name="filterStudentRadio"][value=""]');
        if (allRadio) allRadio.checked = true;
        filterUniversities();
    }
</script>

<?php
$content = ob_get_clean();
$title = "Quản lý Trường Đại học liên kết & Thành viên - KTX ĐHQG-HCM";
require dirname(__DIR__) . '/layouts/main.php';
?>