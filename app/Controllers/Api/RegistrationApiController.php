<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\MailService;
use PDOException;
use Throwable;

class RegistrationApiController extends BaseApiController
{
    private const BED_AVAILABLE = 'Trống';
    private const BED_AVAILABLE_LEGACY = 'Còn trống';

    /**
     * API gửi mã OTP xác thực email khi đăng ký nội trú
     * Route: POST /api/registration/send-otp hoặc POST /register/send-otp
     */
    public function sendOtp(): void
    {
        $request = new Request();

        // 1. Kiểm tra CSRF Token (hỗ trợ cả form body và HTTP Header X-CSRF-TOKEN)
        $token = (string)($request->input('csrf_token') ?: ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        $sessionToken = (string)Session::get('csrf_token', '');

        if ($token === '' || $sessionToken === '' || !hash_equals($sessionToken, $token)) {
            $this->error('Phiên biểu mẫu đã hết hạn. Vui lòng tải lại trang.', 419);
            return;
        }

        // 2. Kiểm tra định dạng Email
        $email = strtolower(trim((string)$request->input('email', '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
            $this->error('Vui lòng nhập địa chỉ email hợp lệ.', 422);
            return;
        }

        // 3. Kiểm tra tính trùng lặp email với tài khoản hiện có
        try {
            $user = Auth::user();
            $currentStudentId = $user['profile']['MaSinhVien'] ?? ($user['TenDangNhap'] ?? '');

            $existingStudent = Database::fetch(
                'SELECT MaSinhVien FROM SinhVien WHERE Email = :email AND MaSinhVien <> :self LIMIT 1',
                ['email' => $email, 'self' => $currentStudentId]
            );
            $existingStaff = Database::fetch(
                'SELECT MaNhanVien FROM NhanVien WHERE Email = :email LIMIT 1',
                ['email' => $email]
            );

            if ($existingStudent || $existingStaff) {
                $this->error('Email này đã được sử dụng cho một tài khoản khác.', 409);
                return;
            }
        } catch (Throwable $e) {
            $this->error('Không thể kiểm tra email lúc này. Vui lòng thử lại.', 500);
            return;
        }

        // 4. Rate limiting: Giới hạn tần suất gửi OTP (60 giây)
        $lastSentAt = (int)Session::get('registration_email_otp_last_sent', 0);
        $waitSeconds = 60 - (time() - $lastSentAt);
        if ($lastSentAt > 0 && $waitSeconds > 0) {
            $this->error("Vui lòng chờ {$waitSeconds} giây trước khi gửi mã khác.", 429);
            return;
        }

        // 5. Tạo mã OTP 6 chữ số ngẫu nhiên
        Session::set('registration_email_otp_last_sent', time());
        Session::remove('registration_email_otp');

        $code = (string)random_int(100000, 999999);
        $subject = '[KTX ĐHQG-HCM] Mã xác thực email đăng ký nội trú';
        $html = '<div style="font-family:Arial,sans-serif;max-width:520px;margin:auto;padding:28px;border:1px solid #dbe5ee;border-radius:12px;color:#18344c">'
            . '<h2 style="margin:0 0 12px;color:#15577f">Xác thực email đăng ký</h2>'
            . '<p>Nhập mã xác thực sau vào biểu mẫu đăng ký KTX ĐHQG-HCM:</p>'
            . '<p style="margin:22px 0;padding:16px;text-align:center;background:#eff7fc;border-radius:10px;font-size:30px;font-weight:800;letter-spacing:8px;color:#145b91">' . $code . '</p>'
            . '<p>Mã có hiệu lực trong <b>10 phút</b>. Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email.</p></div>';
        $plain = "Mã xác thực email đăng ký KTX của bạn là: {$code}\nMã có hiệu lực trong 10 phút. Nếu bạn không yêu cầu mã này, hãy bỏ qua email.";

        $result = MailService::send($email, 'Sinh viên', $subject, $html, $plain, false);

        if (($result['mode'] ?? '') !== 'smtp') {
            $this->error('Máy chủ chưa gửi được email xác thực. Vui lòng kiểm tra cấu hình SMTP rồi thử lại sau.', 502);
            return;
        }

        // Lưu thông tin OTP vào Session
        Session::set('registration_email_otp', [
            'email' => $email,
            'hash' => password_hash($code, PASSWORD_DEFAULT),
            'expires_at' => time() + 600,
            'attempts' => 0,
        ]);

        $this->success(null, 'Mã xác thực đã được gửi. Vui lòng kiểm tra hộp thư đến và thư rác.');
    }

    /**
     * API tải danh sách động (Tòa, Tầng, Phòng, Giường) theo bộ lọc phân cấp
     * Route: GET/POST /api/registration/options hoặc GET/POST /register/options
     */
    public function options(): void
    {
        $request = new Request();
        $type = trim((string)$request->input('type', ''));
        $values = [];

        try {
            // 1. Kiểm tra xem đợt đăng ký có đang mở hay không
            $activeDot = Database::fetch(
                "SELECT MaDot FROM DotDangKy
                 WHERE ThoiGianMoCong <= NOW() AND ThoiGianDongCong >= NOW()
                 LIMIT 1"
            );

            if (!$activeDot) {
                // Giữ format tương thích với frontend hiện tại
                $this->json(['values' => [], 'error' => 'Cổng đăng ký nội trú hiện đang đóng. Không thể chọn chỗ ở lúc này.'], 403);
                return;
            }

            // 2. Xử lý theo từng cấp độ
            if ($type === 'buildings') {
                $area = trim((string)$request->input('area', ''));
                $gender = trim((string)$request->input('gender', ''));
                $roomType = trim((string)$request->input('roomType', ''));

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
                $building = trim((string)$request->input('building', ''));
                $roomType = trim((string)$request->input('roomType', ''));
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
                $building = trim((string)$request->input('building', ''));
                $floor = trim((string)$request->input('floor', ''));
                $roomType = trim((string)$request->input('roomType', ''));
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
                $room = trim((string)$request->input('room', ''));

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
        } catch (PDOException $e) {
            $this->json(['values' => [], 'error' => 'Lỗi kết nối CSDL: ' . $e->getMessage()], 500);
            return;
        }

        $this->json(['values' => $values]);
    }

    /**
     * API kiểm tra tình trạng hợp lệ của MSSV trước khi nộp hồ sơ
     * Route: GET/POST /api/registration/check-mssv
     */
    public function checkMssv(): void
    {
        $request = new Request();
        $mssv = trim((string)$request->input('mssv', ''));

        if (empty($mssv)) {
            $this->error('Vui lòng cung cấp mã số sinh viên.', 422);
            return;
        }

        try {
            $student = Database::fetch(
                "SELECT sv.MaSinhVien, sv.HoVaTen, sv.GioiTinh, sv.MaTruong, dh.TenTruong
                 FROM SinhVien sv
                 LEFT JOIN TruongDaiHoc dh ON sv.MaTruong = dh.MaTruong
                 WHERE sv.MSSVTaiTruong = :mssv OR sv.MaSinhVien = :mssv LIMIT 1",
                ['mssv' => $mssv]
            );

            if (!$student) {
                $this->success(['eligible' => true, 'exists' => false], 'MSSV hợp lệ để đăng ký mới.');
                return;
            }

            // Kiểm tra xem sinh viên đã có hợp đồng còn hạn hoặc đơn đang chờ duyệt chưa
            $activeContract = Database::fetch(
                "SELECT MaHopDong, MaGiuong FROM HopDongNoiTru 
                 WHERE MaSinhVien = :sv AND TrangThaiHopDong = 'Đang hiệu lực' LIMIT 1",
                ['sv' => $student['MaSinhVien']]
            );

            $pendingApp = Database::fetch(
                "SELECT MaHoSo, TrangThaiHoSo FROM HoSoDangKy 
                 WHERE MaSinhVien = :sv AND TrangThaiHoSo IN ('Chờ duyệt', 'Đã duyệt', 'Chờ thanh toán') LIMIT 1",
                ['sv' => $student['MaSinhVien']]
            );

            $this->success([
                'eligible' => !$activeContract && !$pendingApp,
                'exists' => true,
                'student' => $student,
                'hasActiveContract' => (bool)$activeContract,
                'hasPendingApp' => (bool)$pendingApp,
            ], 'Tra cứu MSSV thành công.');
        } catch (Throwable $e) {
            $this->error('Lỗi khi tra cứu sinh viên: ' . $e->getMessage(), 500);
        }
    }
}

