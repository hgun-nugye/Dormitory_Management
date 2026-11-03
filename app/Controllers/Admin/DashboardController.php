<?php

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;

/**
 * Controller: Quản trị viên (Toàn quyền quản trị hệ thống)
 * Các phân hệ quản trị chính:
 * 1. Phân quyền & Quản lý tài khoản cán bộ (Trưởng khu, Trưởng cụm, Trưởng tòa, Quản lý KTX)
 * 2. Cấu hình Đợt đăng ký tuyển sinh & Giữ chỗ 72h
 * 3. Danh mục Loại phòng & Đơn giá lưu trú chuẩn
 * 4. Danh mục Trường Đại học liên kết & Chỉ tiêu đào tạo
 * 5. Giám sát Tài chính & Nhật ký Giao dịch thanh toán
 * 
 * Lưu ý: Nghiệp vụ quản lý Hạ tầng KTX (Khu, Cụm, Tòa, Phòng, Giường)
 * đã được chuyển giao hoàn toàn sang phân hệ Cán bộ BQL KTX (Staff).
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();

        // 1. Thống kê toàn hệ thống dành cho Quản trị viên
        $stats = [
            'total_students' => 0,
            'active_residents' => 0,
            'total_periods' => 0,
            'total_universities' => 0,
            'pending_applications' => 0,
            'pending_maintenance' => 0,
            'total_staff' => 0,
            'total_transactions' => 0
        ];

        $canBoList = [];
        $dotDangKyList = [];
        $loaiPhongList = [];
        $truongList = [];
        $recentTransactions = [];

        try {
            $stats['total_students'] = Database::fetch("SELECT COUNT(*) AS total FROM SinhVien")['total'] ?? 0;
            $stats['active_residents'] = Database::fetch("SELECT COUNT(*) AS total FROM HopDongNoiTru WHERE TrangThaiHopDong = 'Đang hiệu lực'")['total'] ?? 0;
            $stats['total_periods'] = Database::fetch("SELECT COUNT(*) AS total FROM DotDangKy")['total'] ?? 0;
            $stats['total_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc")['total'] ?? 0;
            $stats['dhqg_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc WHERE ThuocDHQGHCM = 1")['total'] ?? 0;
            $stats['active_universities'] = Database::fetch("SELECT COUNT(*) AS total FROM TruongDaiHoc WHERE DangHopTac = 1")['total'] ?? 0;
            $stats['pending_applications'] = Database::fetch("SELECT COUNT(*) AS total FROM HoSoDangKy WHERE TrangThaiHoSo = 'Chờ duyệt'")['total'] ?? 0;
            $stats['pending_maintenance'] = Database::fetch("SELECT COUNT(*) AS total FROM PhieuBaoHong WHERE TrangThaiSua = 'Chờ xử lý'")['total'] ?? 0;
            $stats['total_staff'] = Database::fetch("SELECT COUNT(*) AS total FROM TaiKhoan WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')")['total'] ?? 0;
            $stats['active_staff'] = Database::fetch("SELECT COUNT(*) AS total FROM TaiKhoan WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG') AND DangHoatDong = 1")['total'] ?? 0;
            $stats['total_transactions'] = Database::fetch("SELECT COUNT(*) AS total FROM GiaoDichThanhToan")['total'] ?? 0;

            // Danh sách cán bộ & phân quyền (5 tài khoản mới nhất)
            $canBoList = Database::fetchAll("
                SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai, nv.Email,
                       cn.TenCum, k.TenKhu, toa.TenToaNha
                FROM TaiKhoan tk
                JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
                LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
                LEFT JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                LEFT JOIN ToaNha toa ON tk.MaCumQuanLy = toa.MaToaNha
                WHERE tk.MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')
                ORDER BY tk.MaTaiKhoan DESC
                LIMIT 5
            ");

            // Đợt đăng ký 72h
            $dotDangKyList = Database::fetchAll("SELECT * FROM DotDangKy ORDER BY MaDot DESC LIMIT 5");

            // Loại phòng & đơn giá
            $loaiPhongList = Database::fetchAll("SELECT * FROM LoaiPhong ORDER BY DonGiaLuuTruChuan ASC");

            // Trường đại học (5 trường kèm số lượng sinh viên nội trú)
            $truongList = Database::fetchAll("
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
                LIMIT 5
            ");

            // Doanh thu & giao dịch
            $recentTransactions = Database::fetchAll("
                SELECT gd.*, hd.LoaiHoaDon, hd.MaSinhVien, sv.HoVaTen
                FROM GiaoDichThanhToan gd
                JOIN HoaDon hd ON gd.MaHoaDon = hd.MaHoaDon
                JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                ORDER BY gd.ThoiGianGiaoDich DESC LIMIT 5
            ");
        } catch (\Throwable $e) {
            // Dùng dữ liệu sườn mẫu
        }

        // Dữ liệu sườn mẫu dự phòng nếu CSDL chưa có dữ liệu
        if (empty($canBoList)) {
            $canBoList = [
                ['TenDangNhap' => 'NV001', 'HoVaTen' => 'Trần Văn Minh', 'TenVaiTro' => 'Trưởng cụm nhà', 'MaCumQuanLy' => 'Cụm AG (Khu A)', 'SoDienThoai' => '0903123456', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'NV002', 'HoVaTen' => 'Nguyễn Thị Thu Hương', 'TenVaiTro' => 'Nhân viên hành chính', 'MaCumQuanLy' => 'Cụm AH (Khu A)', 'SoDienThoai' => '0914234567', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'TK001', 'HoVaTen' => 'Phạm Quốc Hùng', 'TenVaiTro' => 'Trưởng khu KTX', 'MaCumQuanLy' => 'Toàn bộ Khu A', 'SoDienThoai' => '0977333444', 'DangHoatDong' => 1],
                ['TenDangNhap' => 'QLKTX01', 'HoVaTen' => 'Hoàng Minh Tuấn', 'TenVaiTro' => 'Quản lý chung KTX', 'MaCumQuanLy' => 'Toàn hệ thống', 'SoDienThoai' => '0909555666', 'DangHoatDong' => 1],
            ];
        }

        if (empty($dotDangKyList)) {
            $dotDangKyList = [
                ['TenDot' => 'Đăng ký nội trú năm học 2026-2027', 'NamHoc' => '2026-2027', 'ThoiGianMoCong' => '2026-06-01 08:00', 'ThoiGianDongCong' => '2026-10-31 23:59', 'SoGioGiuCho' => 72]
            ];
        }

        if (empty($loaiPhongList)) {
            $loaiPhongList = [
                ['MaLoaiPhong' => 'LP4', 'TenLoaiPhong' => 'Phòng tiêu chuẩn 4 giường', 'SoGiuongToiDa' => 4, 'DonGiaLuuTruChuan' => 650000, 'DonGiaDichVuTangThem' => 0],
                ['MaLoaiPhong' => 'LP4ML', 'TenLoaiPhong' => 'Phòng 4 giường có máy lạnh', 'SoGiuongToiDa' => 4, 'DonGiaLuuTruChuan' => 650000, 'DonGiaDichVuTangThem' => 300000],
                ['MaLoaiPhong' => 'LP8', 'TenLoaiPhong' => 'Phòng tiêu chuẩn 8 giường', 'SoGiuongToiDa' => 8, 'DonGiaLuuTruChuan' => 220000, 'DonGiaDichVuTangThem' => 0]
            ];
        }

        if (empty($truongList)) {
            $truongList = [
                ['MaTruong' => 'BK', 'TenTruong' => 'Trường Đại học Bách khoa - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.5, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'KHTN', 'TenTruong' => 'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'UIT', 'TenTruong' => 'Trường Đại học Công nghệ Thông tin - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1],
                ['MaTruong' => 'UEL', 'TenTruong' => 'Trường Đại học Kinh tế - Luật - ĐHQG-HCM', 'ThuocDHQGHCM' => 1, 'SoNamDaoTaoChuan' => 4.0, 'TongSinhVien' => 0, 'DangHopTac' => 1]
            ];
        }

        $this->view('admin.dashboard', [
            'user' => $user,
            'stats' => $stats,
            'canBoList' => $canBoList,
            'dotDangKyList' => $dotDangKyList,
            'loaiPhongList' => $loaiPhongList,
            'truongList' => $truongList,
            'recentTransactions' => $recentTransactions
        ]);
    }

    public function users(): void
    {
        $user = Auth::user();

        // 1. Danh sách tài khoản cán bộ đang quản lý trong hệ thống
        $userList = [];
        try {
            $userList = Database::fetchAll("
                SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai, nv.Email,
                       cn.TenCum, k.TenKhu, toa.TenToaNha
                FROM TaiKhoan tk
                JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
                LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
                LEFT JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                LEFT JOIN ToaNha toa ON tk.MaCumQuanLy = toa.MaToaNha
                WHERE tk.MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')
                ORDER BY tk.MaTaiKhoan DESC
            ");
        } catch (\Throwable $e) {
            $userList = [];
        }

        // 2. Danh sách nhân sự / cán bộ có sẵn để gợi ý & chọn nhanh
        $allStaff = [];
        try {
            $existingStaff = Database::fetchAll("
                SELECT nv.MaNhanVien, nv.HoVaTen, nv.SoDienThoai, nv.Email, nv.ChucVu,
                       tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy
                FROM NhanVien nv
                LEFT JOIN TaiKhoan tk ON nv.MaTaiKhoan = tk.MaTaiKhoan
                ORDER BY nv.HoVaTen ASC
            ");

            $allStaffMap = [];
            foreach ($existingStaff as $st) {
                if (!empty($st['HoVaTen'])) {
                    $allStaffMap[$st['HoVaTen']] = $st;
                }
            }

            // Bổ sung các cán bộ trưởng cụm từ danh mục Cụm nhà nếu chưa có
            $cumStaff = Database::fetchAll("SELECT DISTINCT HoTenTruongCum, SoDienThoaiCum, MaCum FROM CumNha WHERE HoTenTruongCum IS NOT NULL AND HoTenTruongCum != ''");
            foreach ($cumStaff as $cs) {
                $name = trim($cs['HoTenTruongCum']);
                if (!isset($allStaffMap[$name])) {
                    $allStaffMap[$name] = [
                        'MaNhanVien' => '',
                        'HoVaTen' => $name,
                        'SoDienThoai' => $cs['SoDienThoaiCum'] ?? '',
                        'Email' => '',
                        'ChucVu' => 'Trưởng Ban quản lý cụm ' . $cs['MaCum'],
                        'MaTaiKhoan' => null,
                        'TenDangNhap' => '',
                        'MaVaiTro' => 'TRUONG_CUM',
                        'MaCumQuanLy' => $cs['MaCum']
                    ];
                }
            }

            // Thêm một số cán bộ nhân sự phổ biến để gợi ý linh hoạt
            $defaultStaffPool = [
                ['HoVaTen' => 'Lê Hoàng Nam', 'SoDienThoai' => '0908123456', 'Email' => 'nam.lh@qlktx.vn', 'ChucVu' => 'Trưởng Ban quản lý cụm BA', 'MaVaiTro' => 'TRUONG_CUM', 'MaCumQuanLy' => 'BA'],
                ['HoVaTen' => 'Ngô Thanh Sơn', 'SoDienThoai' => '0938112233', 'Email' => 'son.nt@qlktx.vn', 'ChucVu' => 'Trưởng Ban quản lý cụm BC', 'MaVaiTro' => 'TRUONG_CUM', 'MaCumQuanLy' => 'BC'],
                ['HoVaTen' => 'Võ Văn Kiệt', 'SoDienThoai' => '0912345678', 'Email' => 'kiet.vv@qlktx.vn', 'ChucVu' => 'Kỹ thuật điện lạnh KTX', 'MaVaiTro' => 'NV_THUONG', 'MaCumQuanLy' => 'AG'],
                ['HoVaTen' => 'Đặng Thị Lan', 'SoDienThoai' => '0987654321', 'Email' => 'lan.dt@qlktx.vn', 'ChucVu' => 'Kế toán quản lý hóa đơn', 'MaVaiTro' => 'NV_QUAN_LY_KTX', 'MaCumQuanLy' => '']
            ];
            foreach ($defaultStaffPool as $dp) {
                if (!isset($allStaffMap[$dp['HoVaTen']])) {
                    $allStaffMap[$dp['HoVaTen']] = [
                        'MaNhanVien' => '',
                        'HoVaTen' => $dp['HoVaTen'],
                        'SoDienThoai' => $dp['SoDienThoai'],
                        'Email' => $dp['Email'],
                        'ChucVu' => $dp['ChucVu'],
                        'MaTaiKhoan' => null,
                        'TenDangNhap' => '',
                        'MaVaiTro' => $dp['MaVaiTro'],
                        'MaCumQuanLy' => $dp['MaCumQuanLy']
                    ];
                }
            }

            $allStaff = array_values($allStaffMap);
        } catch (\Throwable $e) {
            $allStaff = [];
        }

        // 3. Danh mục Vai trò
        $vaitroList = [];
        try {
            $vaitroList = Database::fetchAll("SELECT * FROM VaiTro WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG') ORDER BY MaVaiTro ASC");
        } catch (\Throwable $e) {
            $vaitroList = [];
        }

        // 4. Danh mục Khu, Cụm, Tòa nhà phục vụ phân quyền theo vai trò
        $khuList = [];
        $cumNhaList = [];
        $toaNhaList = [];
        try {
            $khuList = Database::fetchAll("SELECT * FROM KhuKTX ORDER BY MaKhu ASC");
            $cumNhaList = Database::fetchAll("
                SELECT c.*, k.TenKhu 
                FROM CumNha c 
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu 
                ORDER BY c.MaKhu ASC, c.MaCum ASC
            ");
            $toaNhaList = Database::fetchAll("
                SELECT t.MaToaNha, t.TenToaNha, c.TenCum, c.MaKhu, k.TenKhu
                FROM ToaNha t
                JOIN CumNha c ON t.MaCum = c.MaCum
                JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                ORDER BY c.MaKhu ASC, c.MaCum ASC, t.MaToaNha ASC
            ");
        } catch (\Throwable $e) {
            $khuList = [];
            $cumNhaList = [];
            $toaNhaList = [];
        }

        if (empty($khuList)) {
            $khuList = [
                ['MaKhu' => 'A', 'TenKhu' => 'Khu A KTX ĐHQG-HCM'],
                ['MaKhu' => 'B', 'TenKhu' => 'Khu B KTX ĐHQG-HCM']
            ];
        }

        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        $this->view('admin.users', [
            'user' => $user,
            'userList' => $userList,
            'allStaff' => $allStaff,
            'vaitroList' => $vaitroList,
            'khuList' => $khuList,
            'cumNhaList' => $cumNhaList,
            'toaNhaList' => $toaNhaList,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning
        ]);
    }

    public function saveUser(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $tenDangNhap = trim((string)$request->input('tenDangNhap', ''));
        $matKhau = trim((string)$request->input('matKhau', ''));
        $hoVaTen = trim((string)$request->input('hoVaTen', ''));
        $soDienThoai = trim((string)$request->input('soDienThoai', ''));
        $email = trim((string)$request->input('email', ''));
        $maVaiTro = trim((string)$request->input('maVaiTro', ''));
        $maCum = trim((string)$request->input('maCumQuanLy', ''));

        // Xử lý phạm vi quản lý: vai trò toàn quyền thì lưu NULL; các vai trò khác lưu mã khu, cụm, tòa tương ứng
        if ($maCum === '' || $maCum === 'ALL' || in_array($maVaiTro, ['ADMIN', 'NV_QUAN_LY_KTX'])) {
            $maCum = null;
        }

        if (empty($tenDangNhap) || empty($hoVaTen) || empty($maVaiTro)) {
            Session::flash('error', 'Vui lòng điền đầy đủ Tên đăng nhập, Họ và tên và Vai trò của cán bộ!');
            $this->redirect('admin/users');
            return;
        }

        try {
            if ($maTaiKhoan > 0) {
                // ==================== CẬP NHẬT (UPDATE) ====================
                $existing = Database::fetch("SELECT * FROM TaiKhoan WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
                if (!$existing) {
                    Session::flash('error', 'Không tìm thấy tài khoản cán bộ để cập nhật!');
                    $this->redirect('admin/users');
                    return;
                }

                // Cố định tên đăng nhập khi sửa (không cho phép sửa tên đăng nhập)
                $tenDangNhap = $existing['TenDangNhap'];
                $resetPassword = (bool)$request->input('resetPassword', false);

                // Cập nhật thông tin TaiKhoan (chỉ cho phép reset mật khẩu về Demo@123)
                if ($resetPassword) {
                    $hash = Auth::hashPassword('Demo@123');
                    Database::execute("
                        UPDATE TaiKhoan 
                        SET MatKhauMaHoa = :hash, MaVaiTro = :vaiTro, MaCumQuanLy = :cum
                        WHERE MaTaiKhoan = :id
                    ", [
                        'hash' => $hash,
                        'vaiTro' => $maVaiTro,
                        'cum' => $maCum,
                        'id' => $maTaiKhoan
                    ]);
                } else {
                    Database::execute("
                        UPDATE TaiKhoan 
                        SET MaVaiTro = :vaiTro, MaCumQuanLy = :cum
                        WHERE MaTaiKhoan = :id
                    ", [
                        'vaiTro' => $maVaiTro,
                        'cum' => $maCum,
                        'id' => $maTaiKhoan
                    ]);
                }

                // Cập nhật thông tin NhanVien
                $nv = Database::fetch("SELECT * FROM NhanVien WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
                if ($nv) {
                    Database::execute("
                        UPDATE NhanVien
                        SET MaNhanVien = :maNV, HoVaTen = :ten, SoDienThoai = :sdt, Email = :email
                        WHERE MaTaiKhoan = :id
                    ", [
                        'maNV' => $tenDangNhap,
                        'ten' => $hoVaTen,
                        'sdt' => $soDienThoai,
                        'email' => $email,
                        'id' => $maTaiKhoan
                    ]);
                } else {
                    Database::execute("
                        INSERT INTO NhanVien (MaNhanVien, MaTaiKhoan, HoVaTen, SoDienThoai, Email)
                        VALUES (:maNV, :id, :ten, :sdt, :email)
                    ", [
                        'maNV' => $tenDangNhap,
                        'id' => $maTaiKhoan,
                        'ten' => $hoVaTen,
                        'sdt' => $soDienThoai,
                        'email' => $email
                    ]);
                }

                if ($resetPassword) {
                    Session::flash('success', "Đã cập nhật thông tin cán bộ '{$hoVaTen}' và đặt lại mật khẩu về mặc định 'Demo@123' thành công!");
                } else {
                    Session::flash('success', "Đã cập nhật thành công thông tin & quyền hạn cho cán bộ: {$hoVaTen} ({$tenDangNhap})!");
                }
            } else {
                // ==================== THÊM MỚI (CREATE) ====================
                if (empty($matKhau)) {
                    Session::flash('error', 'Vui lòng nhập mật khẩu khởi tạo cho tài khoản cán bộ mới!');
                    $this->redirect('admin/users');
                    return;
                }

                // Kiểm tra tên đăng nhập đã tồn tại chưa
                $dup = Database::fetch("SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :user", ['user' => $tenDangNhap]);
                if ($dup) {
                    Session::flash('error', "Tên đăng nhập '{$tenDangNhap}' đã tồn tại trong hệ thống. Vui lòng chọn tên đăng nhập khác!");
                    $this->redirect('admin/users');
                    return;
                }

                $hash = Auth::hashPassword($matKhau);
                Database::execute("
                    INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, MaCumQuanLy, DangHoatDong)
                    VALUES (:user, :hash, :vaiTro, :cum, 1)
                ", [
                    'user' => $tenDangNhap,
                    'hash' => $hash,
                    'vaiTro' => $maVaiTro,
                    'cum' => $maCum
                ]);
                $newId = (int)Database::lastInsertId();

                // Tạo hoặc cập nhật hồ sơ NhanVien
                Database::execute("
                    INSERT INTO NhanVien (MaNhanVien, MaTaiKhoan, HoVaTen, SoDienThoai, Email)
                    VALUES (:maNV, :id, :ten, :sdt, :email)
                    ON DUPLICATE KEY UPDATE MaTaiKhoan = VALUES(MaTaiKhoan), HoVaTen = VALUES(HoVaTen), SoDienThoai = VALUES(SoDienThoai), Email = VALUES(Email)
                ", [
                    'maNV' => $tenDangNhap,
                    'id' => $newId,
                    'ten' => $hoVaTen,
                    'sdt' => $soDienThoai,
                    'email' => $email
                ]);

                Session::flash('success', "Đã thêm mới tài khoản cán bộ '{$hoVaTen}' ({$tenDangNhap}) với vai trò thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi xử lý dữ liệu: ' . $e->getMessage());
        }

        $this->redirect('admin/users');
    }

    public function resetPassword(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản không hợp lệ!');
            $this->redirect('admin/users');
            return;
        }

        try {
            $user = Database::fetch("
                SELECT tk.*, nv.HoVaTen 
                FROM TaiKhoan tk 
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan 
                WHERE tk.MaTaiKhoan = :id
            ", ['id' => $maTaiKhoan]);

            if (!$user) {
                Session::flash('error', 'Không tìm thấy tài khoản để đặt lại mật khẩu!');
                $this->redirect('admin/users');
                return;
            }

            $hash = Auth::hashPassword('Demo@123');
            Database::execute("UPDATE TaiKhoan SET MatKhauMaHoa = :hash WHERE MaTaiKhoan = :id", [
                'hash' => $hash,
                'id' => $maTaiKhoan
            ]);

            $name = $user['HoVaTen'] ?? $user['TenDangNhap'];
            Session::flash('success', "Đã đặt lại mật khẩu cho cán bộ '{$name}' ({$user['TenDangNhap']}) về mặc định: Demo@123 thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi đặt lại mật khẩu: ' . $e->getMessage());
        }

        $this->redirect('admin/users');
    }

    public function toggleUserStatus(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $currentUser = Auth::user();

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản không hợp lệ!');
            $this->redirect('admin/users');
            return;
        }

        if ($currentUser && (int)($currentUser['MaTaiKhoan'] ?? 0) === $maTaiKhoan) {
            Session::flash('error', 'Bạn không thể tự khóa tài khoản của chính mình đang đăng nhập!');
            $this->redirect('admin/users');
            return;
        }

        try {
            $user = Database::fetch("SELECT * FROM TaiKhoan WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
            if (!$user) {
                Session::flash('error', 'Không tìm thấy tài khoản cần thao tác!');
                $this->redirect('admin/users');
                return;
            }

            $newStatus = ($user['DangHoatDong'] == 1) ? 0 : 1;
            Database::execute("UPDATE TaiKhoan SET DangHoatDong = :st WHERE MaTaiKhoan = :id", [
                'st' => $newStatus,
                'id' => $maTaiKhoan
            ]);

            $stText = $newStatus == 1 ? 'MỞ KHÓA' : 'TẠM KHÓA';
            Session::flash('success', "Đã {$stText} tài khoản cán bộ '{$user['TenDangNhap']}' thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi cập nhật trạng thái: ' . $e->getMessage());
        }

        $this->redirect('admin/users');
    }

    public function deleteUser(): void
    {
        $request = new Request();
        $maTaiKhoan = (int)$request->input('maTaiKhoan', 0);
        $currentUser = Auth::user();

        if ($maTaiKhoan <= 0) {
            Session::flash('error', 'Mã tài khoản không hợp lệ!');
            $this->redirect('admin/users');
            return;
        }

        if ($currentUser && (int)($currentUser['MaTaiKhoan'] ?? 0) === $maTaiKhoan) {
            Session::flash('error', 'Bạn không thể tự xóa tài khoản của chính bạn!');
            $this->redirect('admin/users');
            return;
        }

        try {
            $user = Database::fetch("SELECT * FROM TaiKhoan WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
            if (!$user) {
                Session::flash('error', 'Không tìm thấy tài khoản cần xóa!');
                $this->redirect('admin/users');
                return;
            }

            // Kiểm tra ràng buộc bảo toàn dữ liệu (Theo tài liệu phân quyền Admin KTX):
            // 1. Đã duyệt hồ sơ đăng ký?
            $approvalCheck = Database::fetch("SELECT COUNT(*) AS total FROM HoSoDangKy WHERE NguoiDuyet = :id", ['id' => $maTaiKhoan]);
            $approvalCount = (int)($approvalCheck['total'] ?? 0);

            // 2. Đã được gán phiếu bảo trì kỹ thuật?
            $techCheck = Database::fetch("SELECT COUNT(*) AS total FROM PhieuBaoHong WHERE NhanVienKyThuat = :id", ['id' => $maTaiKhoan]);
            $techCount = (int)($techCheck['total'] ?? 0);

            if ($approvalCount > 0 || $techCount > 0) {
                // Quy tắc bảo toàn: Tự động chuyển sang khóa (DangHoatDong = 0)
                Database::execute("UPDATE TaiKhoan SET DangHoatDong = 0 WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
                Session::flash('warning', "Tài khoản '{$user['TenDangNhap']}' đã tham gia duyệt {$approvalCount} hồ sơ và xử lý {$techCount} phiếu bảo trì. Theo Quy tắc bảo toàn dữ liệu (Mục 1 & 3 PDF), hệ thống không xóa vật lý mà đã tự động chuyển tài khoản sang trạng thái KHÓA!");
            } else {
                // Được phép xóa an toàn
                Database::execute("DELETE FROM NhanVien WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
                Database::execute("DELETE FROM TaiKhoan WHERE MaTaiKhoan = :id", ['id' => $maTaiKhoan]);
                Session::flash('success', "Đã xóa hoàn toàn tài khoản cán bộ '{$user['TenDangNhap']}' khỏi hệ thống!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa tài khoản: ' . $e->getMessage());
        }

        $this->redirect('admin/users');
    }

    /**
     * Xuất danh sách cán bộ quản trị hệ thống ra file CSV / Excel (có UTF-8 BOM)
     * Hỗ trợ xuất theo kết quả bộ lọc hiện tại (danh sách ID hoặc điều kiện lọc)
     */
    public function exportUsers(): void
    {
        $request = new Request();
        $idsParam = $request->input('ids');
        $keyword = trim((string)$request->input('keyword', ''));
        $role = trim((string)$request->input('role', ''));
        $status = $request->input('status');

        try {
            $whereClause = "WHERE tk.MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')";
            $params = [];
            $isFiltered = false;

            if (!empty($idsParam)) {
                $rawIds = is_array($idsParam) ? $idsParam : explode(',', (string)$idsParam);
                $cleanIds = array_filter(array_map('intval', $rawIds), fn($id) => $id > 0);
                if (!empty($cleanIds)) {
                    $inPlaceholders = implode(',', $cleanIds);
                    $whereClause .= " AND tk.MaTaiKhoan IN ($inPlaceholders)";
                    $isFiltered = true;
                }
            } else {
                if (!empty($role)) {
                    $whereClause .= " AND tk.MaVaiTro = :role";
                    $params['role'] = $role;
                    $isFiltered = true;
                }
                if ($status !== null && $status !== '') {
                    $whereClause .= " AND tk.DangHoatDong = :status";
                    $params['status'] = (int)$status;
                    $isFiltered = true;
                }
                if (!empty($keyword)) {
                    $whereClause .= " AND (tk.TenDangNhap LIKE :kw OR nv.HoVaTen LIKE :kw OR nv.SoDienThoai LIKE :kw OR nv.Email LIKE :kw)";
                    $params['kw'] = "%{$keyword}%";
                    $isFiltered = true;
                }
            }

            $sql = "
                SELECT tk.MaTaiKhoan, tk.TenDangNhap, tk.MaVaiTro, tk.MaCumQuanLy, tk.DangHoatDong, tk.NgayTao,
                       vt.TenVaiTro, nv.MaNhanVien, nv.HoVaTen, nv.ChucVu, nv.SoDienThoai, nv.Email,
                       cn.TenCum, k.TenKhu
                FROM TaiKhoan tk
                JOIN VaiTro vt ON tk.MaVaiTro = vt.MaVaiTro
                LEFT JOIN NhanVien nv ON tk.MaTaiKhoan = nv.MaTaiKhoan
                LEFT JOIN CumNha cn ON tk.MaCumQuanLy = cn.MaCum
                LEFT JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                {$whereClause}
                ORDER BY tk.MaTaiKhoan ASC
            ";

            $userList = Database::fetchAll($sql, $params);

            $filenamePrefix = $isFiltered ? 'DS_CanBo_KTX_Filtered_' : 'DS_CanBo_KTX_';
            $filename = $filenamePrefix . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            // Ghi UTF-8 BOM để Microsoft Excel đọc tiếng Việt chuẩn xác
            fwrite($output, "\xEF\xBB\xBF");

            // Tiêu đề cột
            fputcsv($output, [
                'STT',
                'Mã tài khoản',
                'Tên đăng nhập',
                'Họ và tên cán bộ',
                'Chức vụ',
                'Mã vai trò',
                'Tên vai trò',
                'Phạm vi phụ trách',
                'Số điện thoại',
                'Email',
                'Trạng thái hoạt động',
                'Ngày tạo tài khoản'
            ]);

            $stt = 1;
            foreach ($userList as $u) {
                $scopeLabel = 'Toàn hệ thống KTX';
                if ($u['MaCumQuanLy'] === 'KHU_A') {
                    $scopeLabel = 'Toàn bộ Khu A';
                } elseif ($u['MaCumQuanLy'] === 'KHU_B') {
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

                $statusLabel = ((int)($u['DangHoatDong'] ?? 1) === 1) ? 'Đang hoạt động' : 'Đã khóa';
                $ngayTao = !empty($u['NgayTao']) ? date('d/m/Y H:i', strtotime($u['NgayTao'])) : '';

                fputcsv($output, [
                    $stt++,
                    $u['MaTaiKhoan'],
                    $u['TenDangNhap'],
                    $u['HoVaTen'] ?? '',
                    $u['ChucVu'] ?? '',
                    $u['MaVaiTro'],
                    $u['TenVaiTro'] ?? $u['MaVaiTro'],
                    $scopeLabel,
                    $u['SoDienThoai'] ?? '',
                    $u['Email'] ?? '',
                    $statusLabel,
                    $ngayTao
                ]);
            }

            fclose($output);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xuất danh sách cán bộ: ' . $e->getMessage());
            $this->redirect('admin/users');
        }
    }

    /**
     * Tải file mẫu CSV để nhập danh sách cán bộ
     */
    public function downloadUserTemplate(): void
    {
        $filename = 'Mau_Import_CanBo_KTX.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'TenDangNhap',
            'HoVaTen',
            'MatKhau',
            'MaVaiTro',
            'MaCumQuanLy',
            'SoDienThoai',
            'Email',
            'ChucVu'
        ]);

        // Dữ liệu mẫu minh họa
        fputcsv($output, [
            'NV_MAU01',
            'Nguyễn Văn An',
            'Demo@123',
            'TRUONG_CUM',
            'AG',
            '0901234567',
            'an.nguyen@qlktx.vn',
            'Trưởng cụm nhà AG'
        ]);
        fputcsv($output, [
            'NV_MAU02',
            'Trần Thị Mai',
            'Demo@123',
            'TRUONG_TOA',
            'BA',
            '0912345678',
            'mai.tran@qlktx.vn',
            'Trưởng tòa nhà BA.01'
        ]);
        fputcsv($output, [
            'NV_MAU03',
            'Lê Hoàng Nam',
            '',
            'NV_THUONG',
            '',
            '0923456789',
            'nam.le@qlktx.vn',
            'Kỹ thuật điện nước'
        ]);

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách cán bộ từ file CSV
     */
    public function importUsers(): void
    {
        $request = new Request();
        $overwrite = (bool)$request->input('overwriteExisting', false);

        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn tệp CSV hợp lệ để nhập danh sách cán bộ!');
            $this->redirect('admin/users');
            return;
        }

        $file = $_FILES['importFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::flash('error', 'Hệ thống chỉ hỗ trợ tệp định dạng CSV (.csv) hoặc văn bản (.txt)!');
            $this->redirect('admin/users');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể mở tệp tải lên để đọc dữ liệu!');
            $this->redirect('admin/users');
            return;
        }

        try {
            // Đọc dòng tiêu đề (header)
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                Session::flash('error', 'Tệp tải lên rỗng!');
                $this->redirect('admin/users');
                return;
            }

            // Xóa UTF-8 BOM nếu có
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            $headers = str_getcsv(trim($firstLine), $delimiter);

            // Nhận diện cột linh hoạt
            $colMap = [];
            foreach ($headers as $idx => $h) {
                $clean = strtolower(trim($h));
                $clean = str_replace([' ', '_', '-', '.'], '', $clean);
                if (in_array($clean, ['tendangnhap', 'username', 'mand', 'manhanvien', 'manv', 'taikhoan'])) {
                    $colMap['username'] = $idx;
                } elseif (in_array($clean, ['hovaten', 'hoten', 'ten', 'fullname', 'name', 'canbo'])) {
                    $colMap['fullname'] = $idx;
                } elseif (in_array($clean, ['matkhau', 'password', 'mk'])) {
                    $colMap['password'] = $idx;
                } elseif (in_array($clean, ['mavaitro', 'vaitro', 'role', 'roleid', 'quyen'])) {
                    $colMap['role'] = $idx;
                } elseif (in_array($clean, ['macumquanly', 'macum', 'cum', 'phamvi', 'cumquanly', 'cluster'])) {
                    $colMap['cluster'] = $idx;
                } elseif (in_array($clean, ['sodienthoai', 'sdt', 'phone', 'dienthoai', 'tel'])) {
                    $colMap['phone'] = $idx;
                } elseif (in_array($clean, ['email', 'mail'])) {
                    $colMap['email'] = $idx;
                } elseif (in_array($clean, ['chucvu', 'vitri', 'position', 'chucdanh'])) {
                    $colMap['position'] = $idx;
                }
            }

            if (!isset($colMap['username']) || !isset($colMap['fullname']) || !isset($colMap['role'])) {
                fclose($handle);
                Session::flash('error', 'Tệp CSV không đúng định dạng. Cần có tối thiểu các cột: TenDangNhap, HoVaTen, MaVaiTro!');
                $this->redirect('admin/users');
                return;
            }

            // Lấy danh sách vai trò hợp lệ
            $rolesDb = Database::fetchAll("SELECT MaVaiTro FROM VaiTro WHERE MaVaiTro NOT IN ('SINH_VIEN', 'TRUONG_PHONG')");
            $validRoles = array_column($rolesDb, 'MaVaiTro');

            // Lấy danh sách cụm nhà hợp lệ
            $cumsDb = Database::fetchAll("SELECT MaCum FROM CumNha");
            $validCums = array_column($cumsDb, 'MaCum');

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorRows = [];
            $rowNum = 1;

            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                $rowNum++;
                if (empty($data) || (count($data) === 1 && trim($data[0]) === '')) {
                    continue;
                }

                $username = isset($colMap['username'], $data[$colMap['username']]) ? trim($data[$colMap['username']]) : '';
                $fullname = isset($colMap['fullname'], $data[$colMap['fullname']]) ? trim($data[$colMap['fullname']]) : '';
                $password = isset($colMap['password'], $data[$colMap['password']]) ? trim($data[$colMap['password']]) : '';
                $role = isset($colMap['role'], $data[$colMap['role']]) ? strtoupper(trim($data[$colMap['role']])) : '';
                $cluster = isset($colMap['cluster'], $data[$colMap['cluster']]) ? trim($data[$colMap['cluster']]) : '';
                $phone = isset($colMap['phone'], $data[$colMap['phone']]) ? trim($data[$colMap['phone']]) : '';
                $email = isset($colMap['email'], $data[$colMap['email']]) ? trim($data[$colMap['email']]) : '';
                $position = isset($colMap['position'], $data[$colMap['position']]) ? trim($data[$colMap['position']]) : '';

                if (empty($username) || empty($fullname)) {
                    $errorRows[] = "Dòng {$rowNum}: Thiếu tên đăng nhập hoặc họ tên cán bộ.";
                    continue;
                }

                if (empty($role) || !in_array($role, $validRoles)) {
                    $errorRows[] = "Dòng {$rowNum} ({$username}): Mã vai trò '{$role}' không hợp lệ trong hệ thống.";
                    continue;
                }

                // Chuẩn hóa cụm quản lý
                if (empty($cluster) || in_array($cluster, ['ALL', 'KHU_A', 'KHU_B'])) {
                    $cluster = null;
                } elseif (!in_array($cluster, $validCums)) {
                    $cluster = null;
                }

                // Kiểm tra tài khoản đã tồn tại chưa
                $existing = Database::fetch("SELECT * FROM TaiKhoan WHERE TenDangNhap = :u", ['u' => $username]);

                if ($existing) {
                    if ($overwrite) {
                        Database::execute("
                            UPDATE TaiKhoan 
                            SET MaVaiTro = :role, MaCumQuanLy = :cum
                            WHERE MaTaiKhoan = :id
                        ", [
                            'role' => $role,
                            'cum' => $cluster,
                            'id' => $existing['MaTaiKhoan']
                        ]);

                        Database::execute("
                            INSERT INTO NhanVien (MaNhanVien, MaTaiKhoan, HoVaTen, SoDienThoai, Email, ChucVu)
                            VALUES (:maNV, :id, :ten, :sdt, :email, :chucVu)
                            ON DUPLICATE KEY UPDATE HoVaTen = VALUES(HoVaTen), SoDienThoai = VALUES(SoDienThoai), Email = VALUES(Email), ChucVu = VALUES(ChucVu)
                        ", [
                            'maNV' => $username,
                            'id' => $existing['MaTaiKhoan'],
                            'ten' => $fullname,
                            'sdt' => $phone,
                            'email' => $email,
                            'chucVu' => $position
                        ]);

                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    $passToUse = !empty($password) ? $password : 'Demo@123';
                    $hash = Auth::hashPassword($passToUse);

                    Database::execute("
                        INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, MaCumQuanLy, DangHoatDong)
                        VALUES (:u, :p, :role, :cum, 1)
                    ", [
                        'u' => $username,
                        'p' => $hash,
                        'role' => $role,
                        'cum' => $cluster
                    ]);
                    $newId = (int)Database::lastInsertId();

                    Database::execute("
                        INSERT INTO NhanVien (MaNhanVien, MaTaiKhoan, HoVaTen, SoDienThoai, Email, ChucVu)
                        VALUES (:maNV, :id, :ten, :sdt, :email, :chucVu)
                        ON DUPLICATE KEY UPDATE MaTaiKhoan = VALUES(MaTaiKhoan), HoVaTen = VALUES(HoVaTen), SoDienThoai = VALUES(SoDienThoai), Email = VALUES(Email), ChucVu = VALUES(ChucVu)
                    ", [
                        'maNV' => $username,
                        'id' => $newId,
                        'ten' => $fullname,
                        'sdt' => $phone,
                        'email' => $email,
                        'chucVu' => $position
                    ]);

                    $createdCount++;
                }
            }

            fclose($handle);

            $msg = "Nhập dữ liệu hoàn tất: Đã tạo mới <b>{$createdCount}</b> cán bộ, Cập nhật <b>{$updatedCount}</b> cán bộ, Bỏ qua <b>{$skippedCount}</b> tài khoản đã tồn tại.";
            if (!empty($errorRows)) {
                $errorDetails = implode('<br>', array_slice($errorRows, 0, 5));
                if (count($errorRows) > 5) {
                    $errorDetails .= '<br>...và ' . (count($errorRows) - 5) . ' dòng lỗi khác.';
                }
                Session::flash('warning', $msg . '<br><div style="margin-top:6px;"><b>Chi tiết cảnh báo dòng dữ liệu:</b><br>' . $errorDetails . '</div>');
            } else {
                Session::flash('success', $msg);
            }
        } catch (\Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            Session::flash('error', 'Lỗi trong quá trình đọc và nhập file CSV: ' . $e->getMessage());
        }

        $this->redirect('admin/users');
    }

    public function universities(): void
    {
        $user = Auth::user();

        $universityList = [];
        try {
            $universityList = Database::fetchAll("
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
            ");
        } catch (\Throwable $e) {
            $universityList = [];
        }

        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        $flashWarning = Session::flash('warning');

        $this->view('admin.universities', [
            'user' => $user,
            'universityList' => $universityList,
            'flashSuccess' => $flashSuccess,
            'flashError' => $flashError,
            'flashWarning' => $flashWarning
        ]);
    }

    public function saveUniversity(): void
    {
        $request = new Request();
        $isEdit = (int)$request->input('isEdit', 0);
        $maTruong = strtoupper(trim((string)$request->input('maTruong', '')));
        $tenTruong = trim((string)$request->input('tenTruong', ''));
        $soNamDaoTao = (float)$request->input('soNamDaoTaoChuan', 4.0);
        $thuocDHQGHCM = (int)$request->input('thuocDHQGHCM', 1);
        $dangHopTac = (int)$request->input('dangHopTac', 1);

        if (empty($maTruong) || empty($tenTruong)) {
            Session::flash('error', 'Vui lòng nhập đầy đủ Mã trường và Tên trường Đại học!');
            $this->redirect('admin/universities');
            return;
        }

        if ($soNamDaoTao <= 0 || $soNamDaoTao > 10) {
            Session::flash('error', 'Thời gian đào tạo chuẩn không hợp lệ (từ 1.0 đến 10.0 năm)!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            if ($isEdit === 1) {
                // ==================== CẬP NHẬT (UPDATE) ====================
                $existing = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                if (!$existing) {
                    Session::flash('error', "Không tìm thấy trường Đại học '{$maTruong}' để cập nhật!");
                    $this->redirect('admin/universities');
                    return;
                }

                Database::execute("
                    UPDATE TruongDaiHoc 
                    SET TenTruong = :ten, SoNamDaoTaoChuan = :nam, ThuocDHQGHCM = :thuoc, DangHopTac = :hoptac
                    WHERE MaTruong = :ma
                ", [
                    'ten' => $tenTruong,
                    'nam' => $soNamDaoTao,
                    'thuoc' => $thuocDHQGHCM,
                    'hoptac' => $dangHopTac,
                    'ma' => $maTruong
                ]);

                Session::flash('success', "Đã cập nhật thông tin trường Đại học '{$tenTruong}' ({$maTruong}) thành công!");
            } else {
                // ==================== THÊM MỚI (CREATE) ====================
                $dup = Database::fetch("SELECT MaTruong FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                if ($dup) {
                    Session::flash('error', "Mã trường '{$maTruong}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác!");
                    $this->redirect('admin/universities');
                    return;
                }

                Database::execute("
                    INSERT INTO TruongDaiHoc (MaTruong, TenTruong, SoNamDaoTaoChuan, ThuocDHQGHCM, DangHopTac)
                    VALUES (:ma, :ten, :nam, :thuoc, :hoptac)
                ", [
                    'ma' => $maTruong,
                    'ten' => $tenTruong,
                    'nam' => $soNamDaoTao,
                    'thuoc' => $thuocDHQGHCM,
                    'hoptac' => $dangHopTac
                ]);

                Session::flash('success', "Đã thêm mới trường Đại học '{$tenTruong}' ({$maTruong}) thành công!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi lưu dữ liệu: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    public function toggleUniversityStatus(): void
    {
        $request = new Request();
        $maTruong = trim((string)$request->input('maTruong', ''));

        if (empty($maTruong)) {
            Session::flash('error', 'Mã trường đại học không hợp lệ!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $uni = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
            if (!$uni) {
                Session::flash('error', 'Không tìm thấy trường đại học cần thao tác!');
                $this->redirect('admin/universities');
                return;
            }

            $newStatus = ((int)$uni['DangHopTac'] === 1) ? 0 : 1;
            Database::execute("UPDATE TruongDaiHoc SET DangHopTac = :st WHERE MaTruong = :ma", [
                'st' => $newStatus,
                'ma' => $maTruong
            ]);

            $stText = ($newStatus === 1) ? 'TIẾP TỤC HỢP TÁC' : 'TẠM DỪNG TIẾP NHẬN';
            Session::flash('success', "Đã chuyển trạng thái trường '{$uni['TenTruong']}' sang {$stText} thành công!");
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi cập nhật trạng thái: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    public function deleteUniversity(): void
    {
        $request = new Request();
        $maTruong = trim((string)$request->input('maTruong', ''));

        if (empty($maTruong)) {
            Session::flash('error', 'Mã trường đại học không hợp lệ!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $uni = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
            if (!$uni) {
                Session::flash('error', 'Không tìm thấy trường đại học cần xóa!');
                $this->redirect('admin/universities');
                return;
            }

            // Kiểm tra ràng buộc bảo toàn dữ liệu sinh viên
            $svCheck = Database::fetch("SELECT COUNT(*) AS total FROM SinhVien WHERE MaTruong = :ma", ['ma' => $maTruong]);
            $svCount = (int)($svCheck['total'] ?? 0);

            if ($svCount > 0) {
                // Bảo toàn dữ liệu: Không xóa vật lý khi đã có sinh viên liên kết, tự động chuyển sang tạm dừng tiếp nhận
                Database::execute("UPDATE TruongDaiHoc SET DangHopTac = 0 WHERE MaTruong = :ma", [
                    'ma' => $maTruong
                ]);
                Session::flash('warning', "Trường '{$uni['TenTruong']}' ({$maTruong}) đang có {$svCount} sinh viên trong hệ thống KTX. Để bảo toàn lịch sử dữ liệu nội trú, hệ thống đã tự động chuyển sang trạng thái TẠM DỪNG TIẾP NHẬN thay vì xóa vật lý!");
            } else {
                Database::execute("DELETE FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $maTruong]);
                Session::flash('success', "Đã xóa hoàn toàn trường Đại học '{$uni['TenTruong']}' ({$maTruong}) khỏi hệ thống!");
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xóa trường đại học: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }

    /**
     * Xuất danh sách trường đại học ra file CSV / Excel (có UTF-8 BOM)
     * Hỗ trợ xuất theo kết quả lọc (danh sách mã trường hoặc điều kiện tìm kiếm)
     */
    public function exportUniversities(): void
    {
        $request = new Request();
        $codesParam = $request->input('ids'); // client gửi danh sách mã trường phân cách bởi dấu phẩy
        $keyword = trim((string)$request->input('keyword', ''));
        $category = $request->input('category');
        $status = $request->input('status');
        $duration = $request->input('duration');

        try {
            $whereConditions = [];
            $params = [];
            $isFiltered = false;

            if (!empty($codesParam)) {
                $rawCodes = is_array($codesParam) ? $codesParam : explode(',', (string)$codesParam);
                $cleanCodes = array_filter(array_map('trim', $rawCodes), fn($c) => !empty($c));
                if (!empty($cleanCodes)) {
                    $placeholders = [];
                    foreach ($cleanCodes as $idx => $code) {
                        $pKey = 'code_' . $idx;
                        $placeholders[] = ':' . $pKey;
                        $params[$pKey] = $code;
                    }
                    $whereConditions[] = "t.MaTruong IN (" . implode(',', $placeholders) . ")";
                    $isFiltered = true;
                }
            } else {
                if ($category !== null && $category !== '') {
                    $whereConditions[] = "t.ThuocDHQGHCM = :cat";
                    $params['cat'] = (int)$category;
                    $isFiltered = true;
                }
                if ($status !== null && $status !== '') {
                    $whereConditions[] = "t.DangHopTac = :st";
                    $params['st'] = (int)$status;
                    $isFiltered = true;
                }
                if ($duration !== null && $duration !== '') {
                    $dur = (float)$duration;
                    if ($dur >= 5.0) {
                        $whereConditions[] = "t.SoNamDaoTaoChuan >= 5.0";
                    } else {
                        $whereConditions[] = "t.SoNamDaoTaoChuan = :dur";
                        $params['dur'] = $dur;
                    }
                    $isFiltered = true;
                }
                if (!empty($keyword)) {
                    $whereConditions[] = "(t.MaTruong LIKE :kw OR t.TenTruong LIKE :kw)";
                    $params['kw'] = "%{$keyword}%";
                    $isFiltered = true;
                }
            }

            $whereSql = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

            $sql = "
                SELECT t.*, COUNT(sv.MaSinhVien) AS TongSinhVien
                FROM TruongDaiHoc t
                LEFT JOIN SinhVien sv ON t.MaTruong = sv.MaTruong
                {$whereSql}
                GROUP BY t.MaTruong, t.TenTruong, t.SoNamDaoTaoChuan, t.ThuocDHQGHCM, t.DangHopTac
                ORDER BY t.ThuocDHQGHCM DESC, t.MaTruong ASC
            ";

            $uniList = Database::fetchAll($sql, $params);

            $filenamePrefix = $isFiltered ? 'DS_TruongDaiHoc_KTX_Filtered_' : 'DS_TruongDaiHoc_KTX_';
            $filename = $filenamePrefix . date('Ymd_His') . '.csv';
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'STT',
                'Mã trường',
                'Tên trường đại học',
                'Thời gian đào tạo chuẩn (Năm)',
                'Phân loại đơn vị',
                'Số sinh viên KTX',
                'Trạng thái hợp tác'
            ]);

            $stt = 1;
            foreach ($uniList as $u) {
                $isDHQG = ((int)($u['ThuocDHQGHCM'] ?? 0)) === 1;
                $isPaused = ((int)($u['DangHopTac'] ?? 1)) === 0;

                fputcsv($output, [
                    $stt++,
                    $u['MaTruong'],
                    $u['TenTruong'],
                    $u['SoNamDaoTaoChuan'],
                    $isDHQG ? 'Thành viên ĐHQG-HCM' : 'Ngoài ĐHQG-HCM (Liên kết)',
                    (int)($u['TongSinhVien'] ?? 0),
                    $isPaused ? 'Tạm dừng tiếp nhận' : 'Đang hợp tác tiếp nhận'
                ]);
            }

            fclose($output);
            exit;
        } catch (\Throwable $e) {
            Session::flash('error', 'Lỗi khi xuất danh sách trường: ' . $e->getMessage());
            $this->redirect('admin/universities');
        }
    }

    /**
     * Tải file mẫu CSV để nhập danh sách trường đại học
     */
    public function downloadUniversityTemplate(): void
    {
        $filename = 'Mau_Import_TruongDaiHoc_KTX.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fwrite($output, "\xEF\xBB\xBF");

        // Headers
        fputcsv($output, [
            'MaTruong',
            'TenTruong',
            'SoNamDaoTaoChuan',
            'ThuocDHQGHCM',
            'DangHopTac'
        ]);

        // Dữ liệu mẫu minh họa
        fputcsv($output, [
            'BK',
            'Trường Đại học Bách khoa - ĐHQG-HCM',
            '4.5',
            '1',
            '1'
        ]);
        fputcsv($output, [
            'KHTN',
            'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM',
            '4.0',
            '1',
            '1'
        ]);
        fputcsv($output, [
            'HUTECH',
            'Trường Đại học Công nghệ TP.HCM',
            '4.0',
            '0',
            '1'
        ]);

        fclose($output);
        exit;
    }

    /**
     * Nhập danh sách trường đại học từ file CSV
     */
    public function importUniversities(): void
    {
        $request = new Request();
        $overwrite = (bool)$request->input('overwriteExisting', false);

        if (!isset($_FILES['importFile']) || $_FILES['importFile']['error'] !== UPLOAD_ERR_OK) {
            Session::flash('error', 'Vui lòng chọn tệp CSV hợp lệ để nhập danh sách trường!');
            $this->redirect('admin/universities');
            return;
        }

        $file = $_FILES['importFile'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['csv', 'txt'])) {
            Session::flash('error', 'Hệ thống chỉ hỗ trợ tệp định dạng CSV (.csv) hoặc văn bản (.txt)!');
            $this->redirect('admin/universities');
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            Session::flash('error', 'Không thể mở tệp tải lên để đọc dữ liệu!');
            $this->redirect('admin/universities');
            return;
        }

        try {
            $firstLine = fgets($handle);
            if ($firstLine === false) {
                fclose($handle);
                Session::flash('error', 'Tệp tải lên rỗng!');
                $this->redirect('admin/universities');
                return;
            }

            // Xóa UTF-8 BOM
            $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine);

            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            $headers = str_getcsv(trim($firstLine), $delimiter);

            // Nhận diện cột linh hoạt
            $colMap = [];
            foreach ($headers as $idx => $h) {
                $clean = strtolower(trim($h));
                $clean = str_replace([' ', '_', '-', '.'], '', $clean);
                if (in_array($clean, ['matruong', 'code', 'ma', 'matruongdaihoc', 'id'])) {
                    $colMap['code'] = $idx;
                } elseif (in_array($clean, ['tentruong', 'name', 'ten', 'tentruongdaihoc', 'truong'])) {
                    $colMap['name'] = $idx;
                } elseif (in_array($clean, ['sonamdaotaochuan', 'sonam', 'duration', 'daotao', 'sonamdaotao'])) {
                    $colMap['duration'] = $idx;
                } elseif (in_array($clean, ['thuocdhqghcm', 'thuocdhqg', 'dhqg', 'isdhqg', 'phanloai', 'thanhvien'])) {
                    $colMap['dhqg'] = $idx;
                } elseif (in_array($clean, ['danghoptac', 'hoptac', 'trangthai', 'status', 'tiepnhan'])) {
                    $colMap['status'] = $idx;
                }
            }

            if (!isset($colMap['code']) || !isset($colMap['name'])) {
                fclose($handle);
                Session::flash('error', 'Tệp CSV không đúng định dạng. Cần có tối thiểu 2 cột: MaTruong, TenTruong!');
                $this->redirect('admin/universities');
                return;
            }

            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $errorRows = [];
            $rowNum = 1;

            while (($data = fgetcsv($handle, 4096, $delimiter)) !== false) {
                $rowNum++;
                if (empty($data) || (count($data) === 1 && trim($data[0]) === '')) {
                    continue;
                }

                $code = isset($colMap['code'], $data[$colMap['code']]) ? strtoupper(trim($data[$colMap['code']])) : '';
                $name = isset($colMap['name'], $data[$colMap['name']]) ? trim($data[$colMap['name']]) : '';
                $rawDuration = isset($colMap['duration'], $data[$colMap['duration']]) ? trim($data[$colMap['duration']]) : '4.0';
                $rawDhqg = isset($colMap['dhqg'], $data[$colMap['dhqg']]) ? trim($data[$colMap['dhqg']]) : '';
                $rawStatus = isset($colMap['status'], $data[$colMap['status']]) ? trim($data[$colMap['status']]) : '1';

                if (empty($code) || empty($name)) {
                    $errorRows[] = "Dòng {$rowNum}: Thiếu Mã trường hoặc Tên trường Đại học.";
                    continue;
                }

                $duration = floatval(str_replace(',', '.', $rawDuration));
                if ($duration <= 0 || $duration > 10.0) {
                    $duration = 4.0;
                }

                $isDhqg = 0;
                if ($rawDhqg === '1' || strtolower($rawDhqg) === 'true' || stripos($rawDhqg, 'đhqg') !== false || stripos($rawDhqg, 'thành viên') !== false || stripos($name, 'đhqg') !== false) {
                    $isDhqg = 1;
                }

                $status = 1;
                if ($rawStatus === '0' || strtolower($rawStatus) === 'false' || stripos($rawStatus, 'tạm dừng') !== false || stripos($rawStatus, 'dừng') !== false) {
                    $status = 0;
                }

                // Kiểm tra xem trường đã tồn tại chưa
                $existing = Database::fetch("SELECT * FROM TruongDaiHoc WHERE MaTruong = :ma", ['ma' => $code]);

                if ($existing) {
                    if ($overwrite) {
                        Database::execute("
                            UPDATE TruongDaiHoc 
                            SET TenTruong = :ten, SoNamDaoTaoChuan = :dur, ThuocDHQGHCM = :dhqg, DangHopTac = :st
                            WHERE MaTruong = :ma
                        ", [
                            'ten' => $name,
                            'dur' => $duration,
                            'dhqg' => $isDhqg,
                            'st' => $status,
                            'ma' => $code
                        ]);
                        $updatedCount++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    Database::execute("
                        INSERT INTO TruongDaiHoc (MaTruong, TenTruong, SoNamDaoTaoChuan, ThuocDHQGHCM, DangHopTac)
                        VALUES (:ma, :ten, :dur, :dhqg, :st)
                    ", [
                        'ma' => $code,
                        'ten' => $name,
                        'dur' => $duration,
                        'dhqg' => $isDhqg,
                        'st' => $status
                    ]);
                    $createdCount++;
                }
            }

            fclose($handle);

            $msg = "Nhập dữ liệu hoàn tất: Đã thêm mới <b>{$createdCount}</b> trường, Cập nhật <b>{$updatedCount}</b> trường, Bỏ qua <b>{$skippedCount}</b> trường đã có.";
            if (!empty($errorRows)) {
                $errorDetails = implode('<br>', array_slice($errorRows, 0, 5));
                if (count($errorRows) > 5) {
                    $errorDetails .= '<br>...và ' . (count($errorRows) - 5) . ' dòng lỗi khác.';
                }
                Session::flash('warning', $msg . '<br><div style="margin-top:6px;"><b>Chi tiết cảnh báo dòng dữ liệu:</b><br>' . $errorDetails . '</div>');
            } else {
                Session::flash('success', $msg);
            }
        } catch (\Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            Session::flash('error', 'Lỗi trong quá trình đọc và nhập file CSV: ' . $e->getMessage());
        }

        $this->redirect('admin/universities');
    }
}
