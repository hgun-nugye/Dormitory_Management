<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Services\AutoReleaseService;
use Throwable;

class ApplicationApiController extends BaseApiController
{
    /**
     * API quét và tự động thu hồi/giải phóng giường các hồ sơ quá hạn thanh toán 72h
     * Route: POST /api/applications/scan-release hoặc POST /staff/applications/scan-release
     */
    public function scanRelease72h(): void
    {
        // 1. Kiểm tra quyền cán bộ / quản lý
        if (!Auth::isNhanVien() && !Auth::isQuanLy() && !Auth::isQuanTriVien()) {
            $this->forbidden('Từ chối quyền: Phân hệ quét giữ chỗ 72h chỉ dành riêng cho Cán bộ Quản lý KTX!');
            return;
        }

        try {
            $result = AutoReleaseService::scanAndReleaseExpiredBeds();
            $totalReleased = (int)($result['total_released'] ?? 0);
            $totalScanned = (int)($result['total_scanned'] ?? 0);

            $message = $totalReleased > 0
                ? "Đã tự động hủy giữ chỗ và giải phóng {$totalReleased} vị trí giường quá hạn 72h."
                : "Hệ thống đã kiểm tra: Hiện không có hồ sơ nào quá hạn 72h.";

            // Trả về cấu trúc JSON tương thích hoàn toàn với frontend hiện tại
            $this->json([
                'success' => true,
                'total_scanned' => $totalScanned,
                'total_released' => $totalReleased,
                'released_beds' => $result['released_beds'] ?? [],
                'released_records' => $result['released_records'] ?? [],
                'message' => $message,
            ]);
        } catch (Throwable $e) {
            $this->error('Lỗi khi thực hiện quét 72h: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API tra cứu chi tiết tiến độ xét duyệt / giữ chỗ hồ sơ nội trú
     * Route: GET /api/applications/status
     */
    public function status(): void
    {
        $request = new Request();
        $code = trim((string)($request->input('code') ?: $request->input('maHoSo') ?: $request->input('mssv') ?: ''));

        if (empty($code)) {
            $this->error('Vui lòng cung cấp Mã hồ sơ hoặc Mã số sinh viên để tra cứu.', 422);
            return;
        }

        try {
            $application = Database::fetch(
                "SELECT hs.MaHoSo, hs.MaSinhVien, hs.MaGiuong, hs.NgayDangKy, hs.TrangThaiHoSo, 
                        hs.ThoiGianHetHanThanhToan, hs.LyDoTuChoi,
                        sv.HoVaTen, sv.MSSVTaiTruong, sv.Email, sv.SoDienThoai,
                        g.KyHieuGiuong, p.SoPhong, p.Tang, tn.TenToaNha, c.TenCum, k.TenKhu
                 FROM HoSoDangKy hs
                 JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                 LEFT JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                 LEFT JOIN Phong p ON g.MaPhong = p.MaPhong
                 LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                 LEFT JOIN CumNha c ON tn.MaCum = c.MaCum
                 LEFT JOIN KhuKTX k ON c.MaKhu = k.MaKhu
                 WHERE hs.MaHoSo = :code OR hs.MaSinhVien = :code OR sv.MSSVTaiTruong = :code
                 ORDER BY hs.NgayDangKy DESC
                 LIMIT 1",
                ['code' => $code]
            );

            if (!$application) {
                $this->notFound('Không tìm thấy thông tin hồ sơ đăng ký tương ứng.');
                return;
            }

            // Tính toán thời gian đếm ngược 72h nếu đang ở trạng thái giữ chỗ
            $isHolding = in_array($application['TrangThaiHoSo'], ['Đã duyệt', 'Chờ thanh toán', 'Giữ chỗ']);
            $remainingSeconds = 0;
            if ($isHolding && !empty($application['ThoiGianHetHanThanhToan'])) {
                $remainingSeconds = max(0, strtotime($application['ThoiGianHetHanThanhToan']) - time());
            }

            $this->success([
                'application' => $application,
                'isHolding' => $isHolding,
                'remainingSeconds' => $remainingSeconds,
            ], 'Tra cứu hồ sơ thành công.');
        } catch (Throwable $e) {
            $this->error('Lỗi khi tra cứu hồ sơ: ' . $e->getMessage(), 500);
        }
    }
}

