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
        if ($type === 'buildings') {
            $area = trim((string) $request->input('area', ''));
            $gender = trim((string) $request->input('gender', ''));

            $values = Database::fetchAll(
                "SELECT DISTINCT tn.MaToaNha, tn.TenToaNha
                 FROM ToaNha tn
                 JOIN CumNha cn ON cn.MaCum = tn.MaCum
                 WHERE cn.MaKhu = :area 
                   AND LOWER(tn.GioiTinhQuyDinh) = LOWER(:gender)
                   AND tn.TrangThaiToaNha NOT IN ('Ngưng sử dụng', 'Tạm đóng bảo trì')
                 ORDER BY tn.TenToaNha",
                ['area' => $area, 'gender' => $gender]
            );
        } elseif ($type === 'floors') {
            $building = trim((string) $request->input('building', ''));
            $roomType = trim((string) $request->input('roomType', ''));
            $roomTypeSql = $roomType !== '' ? " AND p.MaLoaiPhong = :roomType" : '';

            $values = Database::fetchAll(
                "SELECT DISTINCT p.Tang
                 FROM Phong p
                 WHERE p.MaToaNha = :building
                   AND p.TrangThaiPhong NOT IN ('Đang bảo trì')
                   {$roomTypeSql}
                 ORDER BY p.Tang",
                [
                    'building' => $building,
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
                 WHERE p.MaToaNha = :building
                   AND p.Tang = :floor
                   AND p.TrangThaiPhong NOT IN ('Đang bảo trì')
                   {$roomTypeSql}
                 ORDER BY p.SoPhong",
                [
                    'building' => $building,
                    'floor' => $floor,
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
                   AND g.TrangThaiGiuong IN (:available, :availableLegacy)
                   AND p.TrangThaiPhong NOT IN ('Đang bảo trì')
                 ORDER BY g.KyHieuGiuong",
                [
                    'room' => $room,
                    'available' => self::BED_AVAILABLE,
                    'availableLegacy' => self::BED_AVAILABLE_LEGACY,
                ]
            );
        }
    } catch (\PDOException $e) {
        $this->json(['values' => [], 'error' => 'Lỗi kết nối CSDL.'], 500);
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
                "SELECT MaSinhVien FROM SinhVien WHERE MaSinhVien = :mssv OR MSSVTaiTruong = :mssv LIMIT 1",
                ['mssv' => $mssv]
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
                        : 'MSSV này đã có tài khoản trong hệ thống. Hãy đăng nhập hoặc sử dụng chức năng quên tài khoản.'
                );
                $this->redirect($existingApplication ? 'student/dashboard' : 'login');
                return;
            }
        } catch (\Throwable $e) {
            Session::flash('error', 'Không thể kiểm tra MSSV. Vui lòng thử lại.');
            $this->redirect('register');
            return;
        }

        $studentId = $mssv;
        $username = $studentId;
        $password = 'KTX-' . bin2hex(random_bytes(5));

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
                "SELECT g.MaGiuong, p.MaPhong, tn.GioiTinhQuyDinh
                 FROM Giuong g
                 JOIN Phong p ON p.MaPhong = g.MaPhong
                 JOIN ToaNha tn ON tn.MaToaNha = p.MaToaNha
                 WHERE g.MaGiuong = :bed
                   AND g.TrangThaiGiuong IN (:bedStatus, :bedStatusLegacy)
                   AND p.TrangThaiPhong NOT IN (:roomMaintenance, :roomMaintenanceLegacy)
                   AND tn.TrangThaiToaNha IN (:buildingStatus, :buildingStatusLegacy)
                 FOR UPDATE",
                ['bed' => $bedId, 'bedStatus' => self::BED_AVAILABLE, 'bedStatusLegacy' => self::BED_AVAILABLE_LEGACY, 'roomMaintenance' => self::ROOM_MAINTENANCE, 'roomMaintenanceLegacy' => self::ROOM_MAINTENANCE_LEGACY, 'buildingStatus' => self::BUILDING_ACTIVE, 'buildingStatusLegacy' => self::BUILDING_ACTIVE_LEGACY]
            );
            if (!$bed || $bed['GioiTinhQuyDinh'] !== $gender) {
                throw new \RuntimeException('Giường không còn trống hoặc không phù hợp giới tính.');
            }

            $dot = Database::fetch(
                "SELECT MaDot FROM DotDangKy
                 WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
                 ORDER BY ThoiGianDongCong DESC LIMIT 1"
            );
            if (!$dot) {
                $dot = Database::fetch("SELECT MaDot FROM DotDangKy ORDER BY MaDot DESC LIMIT 1");
            }
            if (!$dot) {
                throw new \RuntimeException('Hiện không có đợt đăng ký nội trú.');
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
            $subject = '[KTX ĐHQG-HCM] Tài khoản sinh viên đã được tạo';
            $plain = "Nộp hồ sơ thành công. MSSV/Tên đăng nhập: {$username}\nMật khẩu: {$password}\nMã hồ sơ: {$applicationId}\nĐăng nhập tại: {$loginUrl}";
            $html = '<p>Xin chào <strong>' . htmlspecialchars($hoTen) . '</strong>,</p>'
                . '<p>Hồ sơ nội trú của bạn đã được gửi thành công.</p>'
                . '<div style="background:#f1f5f9;padding:16px;border-radius:8px">'
                . '<p><strong>Tên đăng nhập (MSSV):</strong> ' . htmlspecialchars($username) . '</p>'
                . '<p><strong>Mật khẩu tạm thời:</strong> <code>' . htmlspecialchars($password) . '</code></p>'
                . '<p><strong>Mã hồ sơ:</strong> ' . htmlspecialchars($applicationId) . '</p>'
                . '</div>'
                . '<p><a href="' . htmlspecialchars($loginUrl) . '">Đăng nhập hệ thống KTX</a></p>';
            $mailResult = MailService::send($email, $hoTen, $subject, $html, $plain);
            $emailDelivered = ($mailResult['mode'] ?? '') === 'smtp';

            Session::flash(
                'success',
                "Nộp hồ sơ thành công. Mã hồ sơ: {$applicationId}. "
                . "Tài khoản đăng nhập {$username} đã được tạo. "
                . ($emailDelivered
                    ? "Thông tin đăng nhập và mật khẩu đã được gửi đến {$email}."
                    : 'Email chưa được gửi qua máy chủ SMTP. Hãy kiểm tra cấu hình MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD hoặc liên hệ quản trị viên.')
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
