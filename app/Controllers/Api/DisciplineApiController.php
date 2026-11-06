<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Request;
use App\Services\AttendanceDisciplineService;
use Throwable;

class DisciplineApiController extends BaseApiController
{
    /**
     * API tra cứu chi tiết lịch sử điểm rèn luyện & vi phạm của sinh viên
     * Route: GET /api/discipline/history hoặc GET /staff/discipline/history
     */
    public function history(): void
    {
        // 1. Kiểm tra xác thực cán bộ
        if (!Auth::isNhanVien() && !Auth::isQuanLy() && !Auth::isQuanTriVien() && !Auth::isSinhVien()) {
            $this->unauthorized('Vui lòng đăng nhập để xem lịch sử kỷ luật rèn luyện.');
            return;
        }

        $request = new Request();
        $maSinhVien = trim((string)($request->input('sv') ?: $request->input('maSinhVien') ?: ''));
        $namHoc = trim((string)($request->input('nam') ?: $request->input('namHoc') ?: '2026-2027'));
        $hocKy = (int)($request->input('hk') ?: $request->input('hocKy') ?: 1);

        // Sinh viên chỉ được tra cứu lịch sử của chính mình
        if (Auth::isSinhVien()) {
            $user = Auth::user();
            $currentMssv = $user['profile']['MaSinhVien'] ?? ($user['TenDangNhap'] ?? '');
            if ($maSinhVien !== '' && $maSinhVien !== $currentMssv) {
                $this->forbidden('Bạn chỉ có thể xem lịch sử rèn luyện của chính mình.');
                return;
            }
            $maSinhVien = $currentMssv;
        }

        if (empty($maSinhVien)) {
            $this->error('Thiếu mã sinh viên cần tra cứu.', 422);
            return;
        }

        try {
            $data = AttendanceDisciplineService::getStudentDisciplineHistory($maSinhVien, $namHoc, $hocKy);
            $this->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Throwable $e) {
            $this->error('Lỗi khi tra cứu lịch sử rèn luyện: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API quét thẻ RFID / Barrier tự động tại cổng KTX
     * Route: POST /api/discipline/scan
     */
    public function scan(): void
    {
        // Kiểm tra quyền (Cán bộ KTX hoặc máy quẹt barrier)
        if (!Auth::isNhanVien() && !Auth::isQuanLy() && !Auth::isQuanTriVien()) {
            $this->forbidden('Từ chối quyền: Bạn không có quyền thao tác trên hệ thống kiểm soát barrier!');
            return;
        }

        $request = new Request();
        $maSinhVien = trim((string)$request->input('maSinhVien', ''));
        $congKiemSoat = trim((string)$request->input('congKiemSoat', 'Cổng chính A01'));
        $huongDi = trim((string)$request->input('huongDi', 'Vào'));

        if (empty($maSinhVien) || empty($congKiemSoat)) {
            $this->error('Vui lòng cung cấp mã sinh viên và vị trí cổng quét thẻ!', 422);
            return;
        }

        try {
            $result = AttendanceDisciplineService::simulateBarrierScan($maSinhVien, $congKiemSoat, $huongDi);
            if (!empty($result['success'])) {
                $this->success($result, $result['message'] ?? 'Quẹt thẻ thành công.');
            } else {
                $this->error($result['message'] ?? 'Quẹt thẻ không hợp lệ.', 400, $result);
            }
        } catch (Throwable $e) {
            $this->error('Lỗi khi xử lý quẹt thẻ: ' . $e->getMessage(), 500);
        }
    }
}

