<?php
ob_start();
?>

<div class="container">
    <!-- Hero Section -->
    <div class="hero-section-custom">
        <div class="brand-badge">
            Đại học Quốc gia TP. Hồ Chí Minh
        </div>
        <h1 class="hero-title-gradient">
            Hệ thống Quản lý Ký túc xá ĐHQG-HCM
        </h1>
        <p class="hero-subtitle" style="max-width: 720px; margin: 0 auto 32px; color: #475569; font-size: 1.1rem; line-height: 1.6;">
            Giải pháp tích hợp quản lý lưu trú sinh viên, xét duyệt giữ chỗ theo hạn định, dồn phòng tối ưu, thanh toán điện nước và giám sát an ninh nội trú.
        </p>
        <div class="hero-actions" style="display: flex; gap: 14px; justify-content: center; flex-wrap: wrap;">
            <?php if (\App\Core\Auth::check()): ?>
                <?php if (\App\Core\Auth::isSinhVien()): ?>
                    <a href="<?= url('student/dashboard') ?>" class="btn hero-btn-primary" style="padding: 12px 28px; border-radius: 10px;">
                        Vào Cổng sinh viên &rarr;
                    </a>
                <?php elseif (\App\Core\Auth::isNhanVien()): ?>
                    <a href="<?= url('staff/dashboard') ?>" class="btn hero-btn-primary" style="padding: 12px 28px; border-radius: 10px;">
                        Vào BQL cụm nhà &rarr;
                    </a>
                <?php elseif (\App\Core\Auth::isQuanTriVien()): ?>
                    <a href="<?= url('admin/dashboard') ?>" class="btn hero-btn-primary" style="padding: 12px 28px; border-radius: 10px;">
                        Vào Quản trị hệ thống &rarr;
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= url('register') ?>" class="btn hero-btn-primary" style="padding: 12px 28px; border-radius: 10px;">
                    Dành cho sinh viên chưa ở KTX - Đăng ký
                </a>
                <a href="<?= url('login') ?>" class="btn hero-btn-primary" style="padding: 12px 28px; border-radius: 10px;">
                    Dành cho sinh viên đang ở KTX - Đăng nhập
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Stats Section -->
    <?php
    $statsDefaults = [
        'total_students' => 0,
        'total_rooms' => 0,
        'total_beds' => 0,
        'available_beds' => 0,
    ];
    $stats = isset($stats) && is_array($stats) ? $stats : [];
    $homeStats = array_merge($statsDefaults, $stats);
    ?>
    <div class="grid grid-cols-4" style="gap: 20px; margin-bottom: 56px;">
        <div class="stat-card-custom stat-blue">
            <div class="stat-label" style="color: #64748b; font-size: 13px; font-weight: 600;">Sinh viên đăng ký</div>
            <div class="stat-number"><?= number_format($homeStats['total_students']) ?></div>
            <div class="stat-sub" style="color: #94a3b8; font-size: 12px;">Hồ sơ trong hệ thống</div>
        </div>

        <div class="stat-card-custom stat-purple">
            <div class="stat-label" style="color: #64748b; font-size: 13px; font-weight: 600;">Tổng số phòng ở</div>
            <div class="stat-number"><?= number_format($homeStats['total_rooms']) ?></div>
            <div class="stat-sub" style="color: #94a3b8; font-size: 12px;">Khu A và Khu B</div>
        </div>

        <div class="stat-card-custom stat-amber">
            <div class="stat-label" style="color: #64748b; font-size: 13px; font-weight: 600;">Tổng số giường</div>
            <div class="stat-number"><?= number_format($homeStats['total_beds']) ?></div>
            <div class="stat-sub" style="color: #94a3b8; font-size: 12px;">Công suất thiết kế</div>
        </div>

        <div class="stat-card-custom stat-emerald">
            <div class="stat-label" style="color: #64748b; font-size: 13px; font-weight: 600;">Giường còn trống</div>
            <div class="stat-number stat-highlight-emerald"><?= number_format($homeStats['available_beds']) ?></div>
            <div class="stat-sub" style="color: #059669; font-weight: 500; font-size: 12px;">● Sẵn sàng tiếp nhận</div>
        </div>
    </div>

    <!-- 8 Business features showcase -->
    <div id="features" style="margin-top: 48px;">
        <div class="section-heading" style="text-align: center; margin-bottom: 40px;">
            <div style="color: #2563eb; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px;">Tiêu chuẩn vận hành</div>
            <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a;">8 Quy trình nghiệp vụ cốt lõi</h2>
            <p style="color: #64748b; font-size: 1rem; margin-top: 6px;">Hệ thống hóa các nghiệp vụ vận hành thực tế tại KTX ĐHQG-HCM</p>
        </div>

        <div class="grid grid-cols-2" style="gap: 24px;">
            <div class="feature-card-modern">
                <span class="feature-tag tag-blue">QUY TRÌNH 01</span>
                <h3 class="feature-card-title">Khóa giường & Kiểm soát điều kiện lưu trú</h3>
                <p class="feature-card-desc">
                    Tự động lọc theo giới tính quy định của tòa nhà và kiểm tra thời gian đào tạo chuẩn theo trường đại học. Áp dụng kỹ thuật giao dịch cơ sở dữ liệu và khóa dòng (Row-Level Locking) để ngăn ngừa xung đột giữ chỗ cùng lúc.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-emerald">QUY TRÌNH 02</span>
                <h3 class="feature-card-title">Duyệt hồ sơ & Tự động thu hồi chỗ sau 72 giờ</h3>
                <p class="feature-card-desc">
                    Khi cán bộ duyệt hồ sơ, hệ thống tự động thiết lập hạn chót thanh toán 72 giờ. Dịch vụ chạy ngầm định kỳ quét các hồ sơ quá hạn chưa hoàn tất nghĩa vụ tài chính để thu hồi giường về trạng thái trống.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-purple">QUY TRÌNH 03</span>
                <h3 class="feature-card-title">Dồn phòng & Tối ưu công suất lưu trú</h3>
                <p class="feature-card-desc">
                    Tự động thống kê các phòng có tỷ lệ lấp đầy từ 50% trở xuống vào giữa năm học. Đề xuất phương án điều chuyển sinh viên sang các phòng cùng loại trong cụm để giải phóng nguyên phòng phục vụ bảo trì.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-cyan">QUY TRÌNH 04</span>
                <h3 class="feature-card-title">Chuyển phòng & Bù trừ chênh lệch chi phí</h3>
                <p class="feature-card-desc">
                    Xác định số tháng còn lại của hợp đồng nội trú, tự động đối chiếu chênh lệch giữa đơn giá phòng mới và phòng cũ để phát hành hóa đơn nộp bổ sung hoặc lập phiếu hoàn trả trước khi đổi phòng.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-amber">QUY TRÌNH 05</span>
                <h3 class="feature-card-title">Xét miễn giảm theo trần & Hoàn phí trước hạn</h3>
                <p class="feature-card-desc">
                    Áp mức trần theo đơn giá phòng tiêu chuẩn đối với các trường hợp sinh viên diện chính sách. Xác thực điều kiện trả phòng trước hạn (tốt nghiệp, bảo lưu, du học) trong vòng 07 ngày để hoàn tiền số tháng chưa ở.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-indigo">QUY TRÌNH 06</span>
                <h3 class="feature-card-title">Tính tiền điện nước bậc thang & Kiểm tra bất thường</h3>
                <p class="feature-card-desc">
                    Kiểm tra tính hợp lệ khi nhập chỉ số hàng loạt: cảnh báo nếu chỉ số mới nhỏ hơn chỉ số cũ hoặc mức tiêu thụ tăng đột biến vượt quá 3 lần trung bình 3 tháng. Tự động áp dụng biểu giá bậc thang lũy tiến.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-teal">QUY TRÌNH 07</span>
                <h3 class="feature-card-title">Điều phối bảo trì & Chống trùng lặp phiếu</h3>
                <p class="feature-card-desc">
                    Kiểm tra các phiếu báo hỏng thiết bị đang xử lý trong cùng một phòng. Nếu thiết bị đã được bạn cùng phòng gửi yêu cầu, hệ thống điều hướng sinh viên theo dõi tiến độ chung, tránh phát sinh phiếu trùng lặp.
                </p>
            </div>

            <div class="feature-card-modern">
                <span class="feature-tag tag-rose">QUY TRÌNH 08</span>
                <h3 class="feature-card-title">Giám sát vắng mặt 30 ngày & Báo cáo điểm rèn luyện</h3>
                <p class="feature-card-desc">
                    Đối soát dữ liệu quét thẻ ra vào cổng với đơn xin tạm vắng đã duyệt. Cảnh báo các trường hợp vắng mặt liên tục quá 30 ngày không lý do; tổng hợp và kết xuất báo cáo điểm rèn luyện định kỳ gửi về từng trường thành viên.
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = "Hệ thống Quản lý Ký túc xá ĐHQG-HCM";
require __DIR__ . '/layouts/main.php';
?>