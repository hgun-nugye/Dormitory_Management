<?php

namespace App\Services;

use Throwable;

/**
 * Service quản lý gửi Email & Thông báo hệ thống KTX ĐHQG-HCM
 */
class MailService
{
    /**
     * Tạo mật khẩu ngẫu nhiên an toàn (Mặc định 8 ký tự, gồm chữ hoa, chữ thường và chữ số)
     * Tránh các ký tự dễ nhầm lẫn như: 0, O, 1, l, I
     */
    public static function generatePassword(int $length = 8): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Bỏ I, O
        $lowercase = 'abcdefghijkmnpqrstuvwxyz'; // Bỏ l
        $digits = '23456789';                   // Bỏ 0, 1

        // Đảm bảo có ít nhất 1 chữ hoa, 1 chữ thường và 1 số
        $pwd = [];
        $pwd[] = $uppercase[random_int(0, strlen($uppercase) - 1)];
        $pwd[] = $lowercase[random_int(0, strlen($lowercase) - 1)];
        $pwd[] = $digits[random_int(0, strlen($digits) - 1)];

        $combined = $uppercase . $lowercase . $digits;
        for ($i = 3; $i < $length; $i++) {
            $pwd[] = $combined[random_int(0, strlen($combined) - 1)];
        }

        shuffle($pwd);
        return implode('', $pwd);
    }

    /**
     * Lấy cấu hình Mail hiện tại từ config hoặc .env
     */
    public static function getConfig(): array
    {
        $config = function_exists('config') ? config('mail') : [];
        $host = $config['host'] ?? (getenv('MAIL_HOST') ?: 'smtp.mailtrap.io');
        $port = (int)($config['port'] ?? (getenv('MAIL_PORT') ?: 2525));
        $username = $config['username'] ?? getenv('MAIL_USERNAME');
        $password = $config['password'] ?? getenv('MAIL_PASSWORD');
        if (is_string($password)) {
            $password = preg_replace('/\s+/', '', $password) ?? $password;
        }
        $encryption = $config['encryption'] ?? (getenv('MAIL_ENCRYPTION') ?: 'tls');
        $fromEmail = $config['from']['address'] ?? (getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@ktx.vnuhcm.edu.vn');
        $fromName = $config['from']['name'] ?? (getenv('MAIL_FROM_NAME') ?: 'KTX ĐHQG-HCM');

        $hasAuth = !empty($username) && $username !== 'null' && !empty($password) && $password !== 'null';

        return [
            'host' => $host,
            'port' => $port,
            'username' => $username,
            'password' => $password,
            'encryption' => $encryption,
            'from_address' => $fromEmail,
            'from_name' => $fromName,
            'has_auth' => $hasAuth
        ];
    }

    /**
     * Gửi email chung (hỗ trợ SMTP socket hoặc ghi Log hệ thống khi môi trường local/demo)
     *
     * @param string $toEmail   Email người nhận
     * @param string $toName    Tên người nhận
     * @param string $subject   Tiêu đề thư
     * @param string $htmlBody  Nội dung định dạng HTML
     * @param string $plainText Nội dung văn bản thô
     * @return array ['success' => bool, 'mode' => 'smtp'|'log'|'error', 'message' => string, 'trace' => array]
     */
    public static function send(string $toEmail, string $toName, string $subject, string $htmlBody, string $plainText = '', bool $writeLog = true): array
    {
        $toEmail = trim($toEmail);
        if (empty($toEmail) || !filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'mode' => 'error',
                'message' => 'Địa chỉ email người nhận không hợp lệ: ' . htmlspecialchars($toEmail),
                'trace' => []
            ];
        }

        $cfg = self::getConfig();
        $trace = [];

        // Nếu có thông tin xác thực SMTP (User & Pass không rỗng) thì tiến hành kết nối gửi qua SMTP
        if ($cfg['has_auth']) {
            try {
                $smtpSent = self::sendViaSmtp(
                    $cfg['host'],
                    $cfg['port'],
                    $cfg['encryption'],
                    $cfg['username'],
                    $cfg['password'],
                    $cfg['from_address'],
                    $cfg['from_name'],
                    $toEmail,
                    $toName,
                    $subject,
                    $htmlBody,
                    $trace
                );

                if ($smtpSent) {
                    if ($writeLog) {
                        self::logEmail($toEmail, $toName, $subject, $htmlBody, 'SMTP_SUCCESS');
                    }
                    return [
                        'success' => true,
                        'mode' => 'smtp',
                        'message' => "Đã gửi email thành công đến {$toEmail} qua máy chủ SMTP ({$cfg['host']}:{$cfg['port']}).",
                        'trace' => $trace
                    ];
                }
            } catch (Throwable $e) {
                // Ghi nhận lỗi SMTP nhưng vẫn tiếp tục ghi log dự phòng để hệ thống không bị crash
                if ($writeLog) {
                    self::logEmail($toEmail, $toName, $subject, $htmlBody, 'SMTP_ERROR: ' . $e->getMessage());
                }
                return [
                    'success' => $writeLog,
                    'mode' => $writeLog ? 'log_fallback' : 'error',
                    'message' => $writeLog
                        ? "Lỗi kết nối SMTP ngoài ({$e->getMessage()}). Nội dung thư đã được lưu an toàn vào hệ thống log KTX."
                        : "Lỗi kết nối SMTP ngoài ({$e->getMessage()}).",
                    'trace' => $trace
                ];
            }
        }

        if (!$writeLog) {
            return [
                'success' => false,
                'mode' => 'error',
                'message' => 'Tài khoản SMTP chưa được cấu hình; email chưa được gửi.',
                'trace' => ["Mode: LOCAL_LOG disabled for this message"]
            ];
        }

        // Môi trường thử nghiệm / Localhost: ghi nhận email vào log hệ thống KTX (storage/logs/mail.log)
        self::logEmail($toEmail, $toName, $subject, $htmlBody, 'LOG_SUCCESS (Dev/Local)');
        return [
            'success' => true,
            'mode' => 'log',
            'message' => "Email đã được tạo và lưu thành công vào file log (storage/logs/mail.log) do máy chủ chưa cấu hình tài khoản SMTP.",
            'trace' => ["Mode: LOCAL_LOG (MAIL_USERNAME/MAIL_PASSWORD is empty or null)"]
        ];
    }

    /**
     * Gửi email cấp lại mật khẩu 8 ký tự cho sinh viên
     */
    public static function sendStudentPasswordResetEmail(string $studentEmail, string $studentName, string $mssv, string $newPassword, ?string $university = null): array
    {
        $subject = "[KTX ĐHQG-HCM] Cấp lại mật khẩu tài khoản sinh viên - MSSV: {$mssv}";

        $loginUrl = function_exists('url') ? url('login') : 'http://localhost/Dormitory_Management/login';
        $contactUrl = function_exists('url') ? url('') : 'http://localhost/Dormitory_Management';
        $sentTime = date('H:i:s d/m/Y');

        $uniText = !empty($university) ? " - {$university}" : '';


        $html = <<<HTML
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$subject}</title>
</head>
<body style="margin:0; padding:20px; font-family:Arial,sans-serif; color:#333; background:#ffffff;">

    <div style="max-width:550px; margin:0 auto;">

        <h2 style="font-size:20px; color:#1e40af; margin-bottom:20px;">
            KÝ TÚC XÁ ĐHQG-HCM
        </h2>

        <hr style="border:0; border-top:1px solid #ddd;">

        <h3 style="font-size:17px; margin-top:25px;">
            Thông báo cấp lại mật khẩu
        </h3>

        <p style="font-size:14px; line-height:1.7;">
            Xin chào <strong>{$studentName}</strong>,
        </p>

        <p style="font-size:14px; line-height:1.7;">
            Yêu cầu cấp lại mật khẩu tài khoản KTX của bạn đã được xử lý thành công.
        </p>

        <p style="font-size:14px;">
            <strong>Mã số sinh viên:</strong> {$mssv}
        </p>

        <p style="font-size:14px; margin-bottom:8px;">
            <strong>Mật khẩu đăng nhập mới:</strong>
        </p>

        <div style="padding:15px; background:#f1f5f9;
            text-align:center; font-size:26px; font-weight:bold;
            color:#2563eb; letter-spacing:5px;">
            {$newPassword}
        </div>

        <p style="font-size:14px; line-height:1.8; margin-top:20px;">
            Bạn vui lòng sử dụng mật khẩu mới để đăng nhập và chủ động
            đổi mật khẩu sau khi đăng nhập thành công.
        </p>

        <p style="font-size:13px; color:red; line-height:1.7;">
            <strong>Lưu ý:</strong> Không chia sẻ mật khẩu này cho người khác.
            Nếu bạn không yêu cầu cấp lại mật khẩu, vui lòng liên hệ Ban Quản lý KTX.
        </p>

        <p style="font-size:14px; margin-top:25px;">
            Trân trọng,<br>
            <strong>Ban Quản lý Ký túc xá ĐHQG-HCM</strong>
        </p>

        <hr style="border:0; border-top:1px solid #ddd; margin-top:25px;">

        <p style="font-size:12px; color:#888; line-height:1.7;">
            Hotline: 1900 055559<br>
            Email: info@ktx.vnuhcm.edu.vn<br>
            Đây là email tự động, vui lòng không trả lời email này.
        </p>

    </div>

</body>
</html>
HTML;


        return self::send($studentEmail, $studentName, $subject, $html);
    }

    /**
     * Lấy các dòng log email gần đây nhất từ storage/logs/mail.log
     */
    public static function getRecentLogs(int $limit = 5): array
    {
        try {
            $logFile = dirname(__DIR__, 2) . '/storage/logs/mail.log';
            if (!file_exists($logFile)) {
                return [];
            }
            $content = file_get_contents($logFile);
            $separator = str_repeat('-', 80);
            $parts = explode($separator, $content);
            $logs = [];
            foreach (array_reverse($parts) as $part) {
                $trimmed = trim($part);
                if (!empty($trimmed)) {
                    $logs[] = $trimmed;
                }
                if (count($logs) >= $limit) {
                    break;
                }
            }
            return $logs;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Ghi nhận lịch sử gửi thư vào storage/logs/mail.log
     */
    private static function logEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $status): void
    {
        try {
            $logDir = dirname(__DIR__, 2) . '/storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            $logFile = $logDir . '/mail.log';
            $time = date('Y-m-d H:i:s');
            $separator = str_repeat('-', 80);

            $entry = "[$time] STATUS: {$status}\n";
            $entry .= "TO: {$toName} <{$toEmail}>\n";
            $entry .= "SUBJECT: {$subject}\n";
            $entry .= "CONTENT_HTML_LEN: " . strlen($htmlBody) . " bytes\n";
            $entry .= "{$separator}\n\n";

            file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
        } catch (Throwable $e) {
            // Không ngắt luồng nếu lỗi ghi file log
        }
    }

    /**
     * Gửi trực tiếp qua SMTP Socket với xử lý mã trạng thái chuẩn RFC
     */
    private static function sendViaSmtp(string $host, int $port, string $encryption, string $user, string $pass, string $fromEmail, string $fromName, string $toEmail, string $toName, string $subject, string $htmlBody, array &$trace = []): bool
    {
        $timeout = 8;
        $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ]);

        $socket = @stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT, $context);
        if (!$socket) {
            throw new \Exception("Không thể kết nối đến máy chủ SMTP {$host}:{$port} ({$errstr})");
        }

        stream_set_timeout($socket, $timeout);
        $read = self::smtpRead($socket);
        $trace[] = "CONNECT: " . trim($read);
        if (substr($read, 0, 3) !== '220') {
            throw new \Exception("Máy chủ SMTP không sẵn sàng: {$read}");
        }

        // HELO/EHLO
        self::smtpWrite($socket, "EHLO " . gethostname());
        $read = self::smtpRead($socket);
        $trace[] = "EHLO: " . trim($read);

        // STARTTLS
        if ($encryption === 'tls') {
            self::smtpWrite($socket, "STARTTLS");
            $read = self::smtpRead($socket);
            $trace[] = "STARTTLS: " . trim($read);
            if (substr($read, 0, 3) !== '220') {
                throw new \Exception("Máy chủ không hỗ trợ STARTTLS: {$read}");
            }
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new \Exception("Không thể thiết lập mã hóa TLS với máy chủ SMTP");
            }
            self::smtpWrite($socket, "EHLO " . gethostname());
            $read = self::smtpRead($socket);
            $trace[] = "EHLO_TLS: " . trim($read);
        }

        // AUTH LOGIN
        self::smtpWrite($socket, "AUTH LOGIN");
        $read = self::smtpRead($socket);
        $trace[] = "AUTH_LOGIN: " . trim($read);
        if (substr($read, 0, 3) !== '334') {
            throw new \Exception("Máy chủ từ chối lệnh AUTH LOGIN: {$read}");
        }

        self::smtpWrite($socket, base64_encode($user));
        $read = self::smtpRead($socket);
        $trace[] = "USER: " . trim($read);
        if (substr($read, 0, 3) !== '334') {
            throw new \Exception("Tên đăng nhập SMTP bị từ chối: {$read}");
        }

        self::smtpWrite($socket, base64_encode($pass));
        $read = self::smtpRead($socket);
        $trace[] = "PASS: " . trim($read);
        if (substr($read, 0, 3) !== '235') {
            throw new \Exception("Xác thực SMTP thất bại (Sai mật khẩu hoặc cần App Password): {$read}");
        }

        // MAIL FROM
        self::smtpWrite($socket, "MAIL FROM: <{$fromEmail}>");
        $read = self::smtpRead($socket);
        $trace[] = "MAIL_FROM: " . trim($read);
        if (substr($read, 0, 3) !== '250') {
            throw new \Exception("Địa chỉ gửi MAIL FROM bị từ chối: {$read}");
        }

        // RCPT TO
        self::smtpWrite($socket, "RCPT TO: <{$toEmail}>");
        $read = self::smtpRead($socket);
        $trace[] = "RCPT_TO: " . trim($read);
        if (substr($read, 0, 3) !== '250' && substr($read, 0, 3) !== '251') {
            throw new \Exception("Địa chỉ nhận RCPT TO bị từ chối: {$read}");
        }

        // DATA
        self::smtpWrite($socket, "DATA");
        $read = self::smtpRead($socket);
        $trace[] = "DATA: " . trim($read);
        if (substr($read, 0, 3) !== '354') {
            throw new \Exception("Máy chủ không sẵn sàng nhận dữ liệu DATA: {$read}");
        }

        $headers = [];
        $headers[] = "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>";
        $headers[] = "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>";
        $headers[] = "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=";
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "Date: " . date('r');

        $payload = implode("\r\n", $headers) . "\r\n\r\n" . $htmlBody . "\r\n.";
        self::smtpWrite($socket, $payload);
        $read = self::smtpRead($socket);
        $trace[] = "BODY_SEND: " . trim($read);
        if (substr($read, 0, 3) !== '250') {
            throw new \Exception("Lỗi gửi nội dung thư: {$read}");
        }

        // QUIT
        self::smtpWrite($socket, "QUIT");
        @fclose($socket);

        return true;
    }

    private static function smtpWrite($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    private static function smtpRead($socket): string
    {
        $res = '';
        while (!feof($socket)) {
            $line = fgets($socket, 515);
            $res .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $res;
    }
}
