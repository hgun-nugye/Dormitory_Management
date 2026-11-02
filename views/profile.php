<?php
$student = isset($student) && is_array($student) ? $student : [];
?>
<section class="profile-layout">
    <aside class="card identity">
        <div class="portrait"><?= mb_substr($student['HoVaTen'], 0, 1) ?></div>
        <h2><?= htmlspecialchars($student['HoVaTen']) ?></h2>
        <p>MSSV <?= htmlspecialchars($student['MaSinhVien']) ?></p>
        <span class="status-pill"><?= htmlspecialchars($student['TrangThaiNoiTru']) ?></span>
    </aside>
    <div class="detail-stack">
        <section class="card">
            <div class="card-head"><h3>Thông tin cá nhân</h3></div>
            <div class="info-grid">
                <div><small>Họ và tên</small><strong><?= htmlspecialchars($student['HoVaTen']) ?></strong></div>
                <div><small>Ngày sinh</small><strong><?= htmlspecialchars($student['NgaySinh'] ? date('d/m/Y', strtotime($student['NgaySinh'])) : '-') ?></strong></div>
                <div><small>Giới tính</small><strong><?= htmlspecialchars($student['GioiTinh'] ?? '-') ?></strong></div>
                <div><small>CCCD</small><strong><?= htmlspecialchars($student['SoCCCD'] ?? '-') ?></strong></div>
                <div><small>Trường</small><strong><?= htmlspecialchars($student['TenTruong']) ?></strong></div>
                <div><small>Trạng thái</small><strong><?= htmlspecialchars($student['TrangThaiNoiTru']) ?></strong></div>
            </div>
        </section>
        <section class="card">
            <div class="card-head"><h3>Cập nhật thông tin liên hệ</h3></div>
            <form class="request-layout" method="post">
                <input type="hidden" name="action" value="update-contact">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <label>Số điện thoại<input name="SoDienThoai" value="<?= htmlspecialchars($student['SoDienThoai'] ?? '') ?>" required></label>
                <label>Email<input type="email" name="Email" value="<?= htmlspecialchars($student['Email'] ?? '') ?>" required></label>
                <button class="button-primary" type="submit">Lưu thay đổi</button>
            </form>
        </section>
    </div>
</section>
