<?php

namespace App\Controllers\Api;

use App\Core\Controller;

abstract class BaseApiController extends Controller
{
    /**
     * Phản hồi JSON thành công theo chuẩn RESTful
     *
     * @param mixed $data Dữ liệu trả về
     * @param string $message Thông điệp kết quả
     * @param int $status Mã trạng thái HTTP (mặc định 200)
     * @param array $extra Các thông tin bổ sung đi kèm
     */
    protected function success($data = null, string $message = 'Thành công', int $status = 200, array $extra = []): void
    {
        $payload = array_merge([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $extra);

        $this->json($payload, $status);
    }

    /**
     * Phản hồi JSON lỗi theo chuẩn RESTful
     *
     * @param string $message Thông báo lỗi
     * @param int $status Mã trạng thái HTTP (400, 401, 403, 404, 422, 500...)
     * @param mixed $errors Chi tiết các lỗi validation (nếu có)
     */
    protected function error(string $message = 'Đã có lỗi xảy ra', int $status = 400, $errors = null): void
    {
        $payload = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $payload['errors'] = $errors;
        }

        $this->json($payload, $status);
    }

    /**
     * Phản hồi từ chối quyền truy cập (403 Forbidden)
     */
    protected function forbidden(string $message = 'Từ chối quyền truy cập: Bạn không có quyền thực hiện thao tác này.'): void
    {
        $this->error($message, 403);
    }

    /**
     * Phản hồi chưa xác thực đăng nhập (401 Unauthorized)
     */
    protected function unauthorized(string $message = 'Vui lòng đăng nhập để tiếp tục.'): void
    {
        $this->error($message, 401);
    }

    /**
     * Phản hồi không tìm thấy tài nguyên (404 Not Found)
     */
    protected function notFound(string $message = 'Không tìm thấy tài nguyên yêu cầu.'): void
    {
        $this->error($message, 404);
    }

    /**
     * Lấy dữ liệu dạng mảng khi client gửi payload qua JSON body (application/json)
     */
    protected function getJsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}

