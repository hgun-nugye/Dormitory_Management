<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Core\Auth;
use App\Services\MailService;
use PDOException;

class RegistrationController extends Controller
{
    private const BUILDING_ACTIVE = "\u{0110}ang ho\u{1EA1}t \u{0111}\u{1ED9}ng";
    private const ROOM_MAINTENANCE = "\u{0110}ang b\u{1EA3}o tr\u{00EC}";
    private const BED_AVAILABLE = "Tr\u{1ED1}ng";
    // Some existing SQL seed records contain UTF-8 text decoded as Windows-1252.
    private const BUILDING_ACTIVE_LEGACY = "\u{00C4}\u{0090}ang ho\u{00E1}\u{00BA}\u{00A1}t \u{00C4}\u{2018}\u{00E1}\u{00BB}\u{2122}ng";
    private const ROOM_MAINTENANCE_LEGACY = "\u{00C4}\u{0090}ang b\u{00E1}\u{00BA}\u{00A3}o tr\u{00C3}\u{00AC}";
    private const BED_AVAILABLE_LEGACY = "Tr\u{00E1}\u{00BB}\u{2018}ng";

    private function validCsrf(Request $request): bool
    {
        $token = (string) $request->input('csrf_token', '');
        return hash_equals((string) \App\Core\Session::get('csrf_token', ''), $token)
            && $token !== '';
    }

    public function show(): void
    {
        $user = Auth::user();
        if ($user) {
            $mssv = $user['profile']['MaSinhVien'] ?? $user['TenDangNhap'] ?? '';
            if ($mssv !== '') {
                $existingApplication = Database::fetch(
                    "SELECT MaHoSo FROM HoSoDangKy
                     WHERE MaSinhVien = :mssv
                       AND TrangThaiHoSo IN ('Chờ duyệt', 'Chờ thanh toán')
                     LIMIT 1",
                    ['mssv' => $mssv]
                );
                if ($existingApplication) {
                    $this->redirect('student/dashboard');
                    return;
                }
            }
        }

        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }

        // Kiểm tra đợt đăng ký đang có hiệu lực
        $activeDot = Database::fetch(
            "SELECT * FROM DotDangKy
             WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
             ORDER BY ThoiGianDongCong DESC LIMIT 1"
        );
        $isOpen = !empty($activeDot);
        $upcomingDot = null;
        $latestClosedDot = null;

        if (!$isOpen) {
            $upcomingDot = Database::fetch(
                "SELECT * FROM DotDangKy
                 WHERE ThoiGianMoCong > NOW()
                 ORDER BY ThoiGianMoCong ASC LIMIT 1"
            );
            $latestClosedDot = Database::fetch(
                "SELECT * FROM DotDangKy
                 WHERE ThoiGianDongCong < NOW()
                 ORDER BY ThoiGianDongCong DESC LIMIT 1"
            );
        }

        $universities = Database::fetchAll(
            "SELECT MaTruong, TenTruong FROM TruongDaiHoc WHERE DangHopTac = 1 ORDER BY TenTruong"
        );
        $areas = Database::fetchAll(
            "SELECT MaKhu, TenKhu FROM KhuKTX ORDER BY MaKhu"
        );
        $roomTypes = Database::fetchAll(
            "SELECT MaLoaiPhong, TenLoaiPhong FROM LoaiPhong ORDER BY MaLoaiPhong"
        );

        $this->view('registration', [
            'title' => 'Đăng ký hồ sơ nội trú',
            'isOpen' => $isOpen,
            'activeDot' => $activeDot,
            'upcomingDot' => $upcomingDot,
            'latestClosedDot' => $latestClosedDot,
            'universities' => $universities,
            'areas' => $areas,
            'roomTypes' => $roomTypes,
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
        ]);
    }

    public function options(): void
    {
        $request = new Request();
        $type = trim((string) $request->input('type', ''));
        $values = [];

        try {
            // Kiểm tra cổng đăng ký có đang mở không
            $activeDot = Database::fetch(
                "SELECT MaDot FROM DotDangKy
                 WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
                 LIMIT 1"
            );
            if (!$activeDot) {
                $this->json(['values' => [], 'error' => 'Cổng đăng ký nội trú hiện đang đóng. Không thể chọn chỗ ở lúc này.'], 403);
                return;
            }

            if ($type === 'buildings') {
                $area = trim((string) $request->input('area', ''));
                $gender = trim((string) $request->input('gender', ''));
                $roomType = trim((string) $request->input('roomType', ''));

                $roomTypeSql = '';
                $params = ['area' => $area, 'gender' => $gender];
                if ($roomType !== '') {
                    $roomTypeSql = " AND EXISTS (
                        SELECT 1 FROM Phong p 
                        JOIN Giuong g ON g.MaPhong = p.MaPhong 
                        WHERE p.MaToaNha = tn.MaToaNha 
                          AND p.MaLoaiPhong = :roomType 
                          AND p.TrangThaiPhong NOT IN ('Bảo trì')
                          AND g.TrangThaiGiuong IN (:available, :availableLegacy, 'Trong')
                    )";
                    $params['roomType'] = $roomType;
                    $params['available'] = self::BED_AVAILABLE;
                    $params['availableLegacy'] = self::BED_AVAILABLE_LEGACY;
                }

                $values = Database::fetchAll(
                    "SELECT DISTINCT tn.MaToaNha, tn.TenToaNha
                     FROM ToaNha tn
                     JOIN CumNha cn ON cn.MaCum = tn.MaCum
                     WHERE cn.MaKhu = :area 
                       AND (tn.GioiTinhQuyDinh = :gender OR tn.GioiTinhQuyDinh = 'Tất cả')
                       AND tn.TrangThaiToaNha NOT IN ('Ngưng sử dụng', 'Tạm đóng bảo trì')
                       {$roomTypeSql}
                     ORDER BY tn.TenToaNha",
                    $params
                );
            } elseif ($type === 'floors') {
                $building = trim((string) $request->input('building', ''));
                $roomType = trim((string) $request->input('roomType', ''));
                $roomTypeSql = $roomType !== '' ? " AND p.MaLoaiPhong = :roomType" : '';

                $values = Database::fetchAll(
                    "SELECT DISTINCT p.Tang
                     FROM Phong p
                     JOIN Giuong g ON g.MaPhong = p.MaPhong
                     WHERE p.MaToaNha = :building
                       AND p.TrangThaiPhong NOT IN ('Bảo trì')
                       AND g.TrangThaiGiuong IN (:available, :availableLegacy, 'Trong')
                       {$roomTypeSql}
                     ORDER BY p.Tang",
                    [
                        'building' => $building,
                        'available' => self::BED_AVAILABLE,
                        'availableLegacy' => self::BED_AVAILABLE_LEGACY,
                        ...($roomType !== '' ? ['roomType' => $roomType] : [])
                    ]
                );
            } elseif ($type === 'rooms') {
                $building = trim((string) $request->input('building', ''));
                $floor = trim((string) $request->input('floor', ''));
                $roomType = trim((string) $request->input('roomType', ''));
                $roomTypeSql = $roomType !== '' ? " AND p.MaLoaiPhong = :roomType" : '';

                $values = Database::fetchAll(
                    "SELECT DISTINCT p.MaPhong, p.SoPhong
                     FROM Phong p
                     JOIN Giuong g ON g.MaPhong = p.MaPhong
                     WHERE p.MaToaNha = :building
                       AND p.Tang = :floor
                       AND p.TrangThaiPhong NOT IN ('Bảo trì')
                       AND g.TrangThaiGiuong IN (:available, :availableLegacy, 'Trong')
                       {$roomTypeSql}
                     ORDER BY p.SoPhong",
                    [
                        'building' => $building,
                        'floor' => $floor,
                        'available' => self::BED_AVAILABLE,
                        'availableLegacy' => self::BED_AVAILABLE_LEGACY,
                        ...($roomType !== '' ? ['roomType' => $roomType] : [])
                    ]
                );
            } elseif ($type === 'beds') {
                $room = trim((string) $request->input('room', ''));

                $values = Database::fetchAll(
                    "SELECT g.MaGiuong, g.KyHieuGiuong
                     FROM Giuong g
                     JOIN Phong p ON p.MaPhong = g.MaPhong
                     WHERE g.MaPhong = :room
                       AND g.TrangThaiGiuong IN (:available, :availableLegacy, 'Trong')
                       AND p.TrangThaiPhong NOT IN ('Bảo trì', 'Đang bảo trì', 'Khóa bảo trì')
                     ORDER BY g.KyHieuGiuong",
                    [
                        'room' => $room,
                        'available' => self::BED_AVAILABLE,
                        'availableLegacy' => self::BED_AVAILABLE_LEGACY,
                    ]
                );
            }
        } catch (\PDOException $e) {
            $this->json(['values' => [], 'error' => 'Lỗi kết nối CSDL: ' . $e->getMessage()], 500);
            return;
        }

        $this->json(['values' => $values]);
    }

    public function submit(): void
    {
        $request = new Request();
        if (!$this->validCsrf($request)) {
            Session::flash('error', 'Phiên biểu mẫu đã hết hạn. Vui lòng tải lại trang.');
            $this->redirect('register');
            return;
        }

        // 1. RÀNG BUỘC THỜI GIAN ĐỢT ĐĂNG KÝ: Cổng phải đang mở
        $activeDot = Database::fetch(
            "SELECT MaDot, TenDot, SoGioGiuCho FROM DotDangKy
             WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
             ORDER BY ThoiGianDongCong DESC LIMIT 1"
        );
        if (!$activeDot) {
            Session::flash('error', 'Cổng đăng ký nội trú hiện đang đóng hoặc chưa tới thời gian tiếp nhận. Vui lòng theo dõi thông báo đợt đăng ký tiếp theo!');
            $this->redirect('register');
            return;
        }

        $email = trim((string) $request->input('email', ''));

        $hoTen = trim((string) $request->input('hoTen', ''));
        $mssv = trim((string) $request->input('mssv', ''));
        $cccd = trim((string) $request->input('cccd', ''));
        $phone = trim((string) $request->input('phone', ''));
        $birthday = trim((string) $request->input('birthday', ''));
        $gender = trim((string) $request->input('gender', ''));
        $university = trim((string) $request->input('university', ''));
        $bhyt = trim((string) $request->input('bhyt', ''));
        $bedId = trim((string) $request->input('bed', ''));
        $registrationType = trim((string) $request->input('registrationType', 'Đăng ký thường'));
        if ($hoTen === '' || $mssv === '' || $cccd === '' || $email === '' || $university === '' || $bedId === '') {
            Session::flash('error', 'Vui lòng điền họ tên, MSSV, CCCD, email và chọn trường cùng vị trí giường.');
            $this->redirect('register');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Session::flash('error', 'Email cá nhân không hợp lệ.');
            $this->redirect('register');
            return;
        }

        try {
            $existingStudent = Database::fetch(
                "SELECT MaSinhVien FROM SinhVien WHERE MaSinhVien = :mssv1 OR MSSVTaiTruong = :mssv2 LIMIT 1",
                ['mssv1' => $mssv, 'mssv2' => $mssv]
            );
            if ($existingStudent) {
                $existingApplication = Database::fetch(
                    "SELECT MaHoSo FROM HoSoDangKy
                     WHERE MaSinhVien = :mssv
                       AND TrangThaiHoSo IN ('Chờ duyệt', 'Chờ thanh toán')
                     LIMIT 1",
                    ['mssv' => $existingStudent['MaSinhVien']]
                );
                Session::flash(
                    'error',
                    $existingApplication
                        ? 'MSSV này đã có hồ sơ đang xử lý và không thể đăng ký lại.'
                        : 'MSSV này đã có tài khoản trong hệ thống. Hãy đăng nhập hoặc liên hệ Ban Quản lý KTX.'
                );
                $this->redirect($existingApplication ? 'student/dashboard' : 'login');
                return;
            }

            $existingAccount = Database::fetch(
                "SELECT MaTaiKhoan FROM TaiKhoan WHERE TenDangNhap = :u LIMIT 1",
                ['u' => $mssv]
            );
            if ($existingAccount) {
                Session::flash('error', 'Tài khoản với MSSV này đã tồn tại trong hệ thống. Vui lòng đăng nhập.');
                $this->redirect('login');
                return;
            }
        } catch (\Throwable $e) {
            error_log("Lỗi kiểm tra MSSV khi đăng ký: " . $e->getMessage());
            Session::flash('error', 'Không thể kiểm tra MSSV. Vui lòng thử lại.');
            $this->redirect('register');
            return;
        }

        $studentId = $mssv;
        $username = $studentId;
        // Mật khẩu sinh ngẫu nhiên đúng 8 ký tự an toàn
        $password = MailService::generatePassword(8);

        $uploads = [
            'cccdFront' => 'cccd_front',
            'cccdBack' => 'cccd_back',
            'portrait' => 'portrait',
            'bhytImage' => 'bhyt',
            'admissionLetter' => 'admission',
        ];
        $uploadedPaths = [];
        $uploadDir = dirname(__DIR__, 2) . '/storage/uploads/registration';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        try {
            foreach ($uploads as $inputName => $prefix) {
                if (!isset($_FILES[$inputName]) || ($_FILES[$inputName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $file = $_FILES[$inputName];
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new \RuntimeException('Tải tệp thất bại: ' . $inputName);
                }
                if ($file['size'] > 5 * 1024 * 1024) {
                    throw new \RuntimeException('Mỗi tệp tải lên không được vượt quá 5 MB.');
                }
                $mime = mime_content_type($file['tmp_name']);
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    throw new \RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
                }
                $extension = match ($mime) {
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg',
                };
                $filename = $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
                move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename);
                $uploadedPaths[$inputName] = 'storage/uploads/registration/' . $filename;
            }

            Database::beginTransaction();

            $bed = Database::fetch(
                "SELECT g.MaGiuong, p.MaPhong, tn.GioiTinhQuyDinh, tn.TenToaNha
                 FROM Giuong g
                 JOIN Phong p ON p.MaPhong = g.MaPhong
                 JOIN ToaNha tn ON tn.MaToaNha = p.MaToaNha
                 WHERE g.MaGiuong = :bed
                   AND g.TrangThaiGiuong IN (:bedStatus, :bedStatusLegacy, 'Trong')
                   AND p.TrangThaiPhong NOT IN (:roomMaintenance, :roomMaintenanceLegacy, 'Bảo trì')
                   AND tn.TrangThaiToaNha NOT IN ('Ngưng sử dụng', 'Tạm đóng bảo trì')
                 FOR UPDATE",
                [
                    'bed' => $bedId,
                    'bedStatus' => self::BED_AVAILABLE,
                    'bedStatusLegacy' => self::BED_AVAILABLE_LEGACY,
                    'roomMaintenance' => self::ROOM_MAINTENANCE,
                    'roomMaintenanceLegacy' => self::ROOM_MAINTENANCE_LEGACY,
                ]
            );
            if (!$bed || ($bed['GioiTinhQuyDinh'] !== 'Tất cả' && $bed['GioiTinhQuyDinh'] !== $gender)) {
                throw new \RuntimeException('Giường đã chọn không còn trống hoặc không phù hợp giới tính.');
            }

            $dot = Database::fetch(
                "SELECT MaDot FROM DotDangKy
                 WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
                 ORDER BY ThoiGianDongCong DESC LIMIT 1"
            );
            if (!$dot) {
                throw new \RuntimeException('Cổng đăng ký nội trú đã kết thúc thời hạn tiếp nhận hồ sơ trong quá trình xử lý.');
            }

            Database::execute(
                "INSERT INTO TaiKhoan (TenDangNhap, MatKhauMaHoa, MaVaiTro, DangHoatDong)
                 VALUES (:username, :password, 'SINH_VIEN', 1)",
                ['username' => $username, 'password' => password_hash($password, PASSWORD_DEFAULT)]
            );
            $accountId = (int) Database::lastInsertId();

            Database::execute(
                "INSERT INTO SinhVien
                 (MaSinhVien, MaTaiKhoan, MaTruong, MSSVTaiTruong, HoVaTen, NgaySinh, GioiTinh,
                  SoCCCD, SoDienThoai, Email, MaSoBHYT, AnhThe4x6, AnhCCCD, AnhMinhChungHocTap, TrangThaiNoiTru)
                 VALUES
                 (:student, :account, :university, :mssv, :name, :birthday, :gender,
                  :cccd, :phone, :email, :bhyt, :portrait, :cccdImage, :admission, 'Chưa ở')",
                [
                    'student' => $studentId,
                    'account' => $accountId,
                    'university' => $university,
                    'mssv' => $mssv !== '' ? $mssv : null,
                    'name' => $hoTen,
                    'birthday' => $birthday !== '' ? $birthday : null,
                    'gender' => $gender,
                    'cccd' => $cccd,
                    'phone' => $phone !== '' ? $phone : null,
                    'email' => $email,
                    'bhyt' => $bhyt !== '' ? $bhyt : null,
                    'portrait' => $uploadedPaths['portrait'] ?? null,
                    'cccdImage' => $uploadedPaths['cccdFront'] ?? null,
                    'admission' => $uploadedPaths['admissionLetter'] ?? null,
                ]
            );

            $applicationId = 'HS' . date('Y') . bin2hex(random_bytes(4));
            Database::execute(
                "INSERT INTO HoSoDangKy
                 (MaHoSo, MaSinhVien, MaDot, MaGiuong, LoaiLuongDangKy, NgayKetThucChon, TrangThaiHoSo)
                 VALUES (:application, :student, :dot, :bed, :type, NULL, 'Chờ duyệt')",
                [
                    'application' => $applicationId,
                    'student' => $studentId,
                    'dot' => (int) $dot['MaDot'],
                    'bed' => $bedId,
                    'type' => $registrationType,
                ]
            );

            Database::execute(
                "UPDATE Giuong SET TrangThaiGiuong = 'Đang giữ chỗ'
                 WHERE MaGiuong = :bed
                   AND TrangThaiGiuong IN (:available, :availableLegacy, 'Trong')",
                [
                    'bed' => $bedId,
                    'available' => self::BED_AVAILABLE,
                    'availableLegacy' => self::BED_AVAILABLE_LEGACY,
                ]
            );

            Database::commit();
            $loginUrl = function_exists('url') ? url('login') : '';
            $subject = '[KTX ĐHQG-HCM] Tài khoản đăng ký nội trú mới - Yêu cầu đổi mật khẩu';

            $plain = "Xin chào {$hoTen},\n\n"
                . "Hồ sơ đăng ký nội trú của bạn đã được tiếp nhận thành công vào hệ thống!\n"
                . "--------------------------------------------------------\n"
                . "THÔNG TIN TÀI KHOẢN VÀ HỒ SƠ:\n"
                . "- Tên đăng nhập (MSSV): {$username}\n"
                . "- Mật khẩu tạm thời (8 ký tự): {$password}\n"
                . "- Mã hồ sơ đăng ký: {$applicationId}\n"
                . "- Cổng đăng nhập KTX: {$loginUrl}\n"
                . "--------------------------------------------------------\n"
                . "YÊU CẦU BẮT BUỘC:\n"
                . "Mật khẩu trên được sinh ngẫu nhiên gồm 8 ký tự. Vì lý do an toàn bảo mật thông tin cá nhân, bạn vui lòng đăng nhập vào hệ thống tại link trên và đổi mật khẩu mới ngay trong lần đầu đăng nhập.\n\n"
                . "Trân trọng,\nBan Quản lý Ký túc xá ĐHQG-HCM";

            $html = <<<HTML
<div style="font-family: Arial, 'Segoe UI', sans-serif; line-height: 1.6; color: #1e293b; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #ffffff;">
    <div style="background: linear-gradient(135deg, #1e40af, #3b82f6); color: #ffffff; padding: 26px 20px; text-align: center;">
        <h2 style="margin: 0; font-size: 21px; font-weight: 700; letter-spacing: 0.5px;">KÝ TÚC XÁ ĐHQG-HCM</h2>
        <p style="margin: 6px 0 0; font-size: 13px; opacity: 0.92;">Cổng thông tin Đăng ký & Quản lý Cư trú Nội trú</p>
    </div>
    <div style="padding: 24px;">
        <p style="font-size: 15px; margin-top: 0;">Xin chào <strong>{$hoTen}</strong>,</p>
        <p style="font-size: 14px; color: #475569;">Hồ sơ đăng ký chỗ ở nội trú của bạn đã được tiếp nhận thành công vào hệ thống. Dưới đây là thông tin tài khoản được cấp tự động:</p>
        
        <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 18px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <tr>
                    <td style="padding: 7px 0; color: #64748b; width: 45%;">Tên đăng nhập (MSSV):</td>
                    <td style="padding: 7px 0; font-weight: 700; color: #1e40af; font-size: 15px;">{$username}</td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; color: #64748b;">Mật khẩu tạm thời (8 ký tự):</td>
                    <td style="padding: 7px 0;"><code style="background: #fee2e2; border: 1px solid #fca5a5; padding: 4px 10px; border-radius: 4px; font-size: 16px; font-weight: 700; color: #b91c1c; letter-spacing: 1.5px;">{$password}</code></td>
                </tr>
                <tr>
                    <td style="padding: 7px 0; color: #64748b;">Mã hồ sơ đăng ký:</td>
                    <td style="padding: 7px 0; font-weight: 600; color: #0f172a;">{$applicationId}</td>
                </tr>
            </table>
        </div>

        <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 14px; border-radius: 4px; margin-bottom: 22px;">
            <p style="margin: 0; font-size: 13px; color: #b45309; font-weight: 700;">⚠️ YÊU CẦU ĐỔI MẬT KHẨU:</p>
            <p style="margin: 6px 0 0; font-size: 13px; color: #92400e;">Mật khẩu trên là mật khẩu tạm thời 8 ký tự được tạo tự động cho hồ sơ của bạn. Vì lý do an toàn bảo mật thông tin cá nhân, bạn <strong>bắt buộc phải đổi mật khẩu mới</strong> ngay sau khi đăng nhập thành công vào hệ thống KTX.</p>
        </div>

        <div style="text-align: center; margin: 26px 0;">
            <a href="{$loginUrl}" style="background: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 28px; font-size: 14px; font-weight: 700; border-radius: 6px; display: inline-block;">Đăng nhập hệ thống & Đổi mật khẩu</a>
        </div>

        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 24px 0;">
        <p style="font-size: 12px; color: #94a3b8; margin: 0; text-align: center;">Đây là email tự động từ Hệ thống Quản lý KTX ĐHQG-HCM. Vui lòng không trả lời thư này.</p>
    </div>
</div>
HTML;
            $mailResult = MailService::send($email, $hoTen, $subject, $html, $plain);
            $emailDelivered = ($mailResult['mode'] ?? '') === 'smtp';

            Session::flash(
                'success',
                "Đăng ký nội trú thành công! Mã hồ sơ: <b>" . htmlspecialchars($applicationId, ENT_QUOTES, 'UTF-8') . "</b>.<br>"
                    . "Hệ thống đã tự động tạo tài khoản với Tên đăng nhập (MSSV): <b>" . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "</b> và Mật khẩu tạm thời gồm 8 ký tự.<br>"
                    . ($emailDelivered
                        ? "Thông tin đăng nhập cùng <b>yêu cầu đổi mật khẩu</b> đã được gửi đến email <b>" . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . "</b>. Vui lòng kiểm tra hộp thư để đăng nhập và đổi mật khẩu."
                        : "Tài khoản của bạn đã được tạo thành công. Bạn có thể sử dụng mật khẩu ngẫu nhiên 8 ký tự: <code>" . htmlspecialchars($password, ENT_QUOTES, 'UTF-8') . "</code> để đăng nhập và tiến hành đổi mật khẩu mới.")
            );
            $this->redirect('login');
        } catch (\Throwable $e) {
            if (Database::getConnection()->inTransaction()) {
                Database::rollBack();
            }
            Session::flash('error', $e->getMessage() ?: 'Không thể tạo hồ sơ. Vui lòng thử lại.');
            $this->redirect('register');
        }
    }
}
