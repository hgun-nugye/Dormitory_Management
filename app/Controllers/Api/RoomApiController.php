<?php

namespace App\Controllers\Api;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use Throwable;

class RoomApiController extends BaseApiController
{
    /**
     * API tra cứu danh sách phòng & tình trạng giường
     * Route: GET /api/rooms
     */
    public function index(): void
    {
        $request = new Request();
        $building = trim((string)$request->input('building', ''));
        $area = trim((string)$request->input('area', ''));
        $floor = trim((string)$request->input('floor', ''));
        $status = trim((string)$request->input('status', ''));

        $where = ["1=1"];
        $params = [];

        if ($building !== '') {
            $where[] = "p.MaToaNha = :building";
            $params['building'] = $building;
        }

        if ($area !== '') {
            $where[] = "cn.MaKhu = :area";
            $params['area'] = $area;
        }

        if ($floor !== '') {
            $where[] = "p.Tang = :floor";
            $params['floor'] = $floor;
        }

        if ($status !== '') {
            $where[] = "p.TrangThaiPhong = :status";
            $params['status'] = $status;
        }

        try {
            $sql = "
                SELECT p.MaPhong, p.SoPhong, p.Tang, p.MaToaNha, tn.TenToaNha, cn.TenCum, k.TenKhu,
                       lp.TenLoaiPhong, lp.GiaThue, lp.SoLuongNguoi,
                       p.TrangThaiPhong,
                       COUNT(g.MaGiuong) AS TongSoGiuong,
                       SUM(CASE WHEN g.TrangThaiGiuong IN ('Đang ở', 'Da_o', 'Đã duyệt') THEN 1 ELSE 0 END) AS SoGiuongDangO,
                       SUM(CASE WHEN g.TrangThaiGiuong IN ('Trống', 'Còn trống', 'Trong') THEN 1 ELSE 0 END) AS SoGiuongTrong,
                       SUM(CASE WHEN g.TrangThaiGiuong IN ('Bảo trì', 'Khoá') THEN 1 ELSE 0 END) AS SoGiuongBaoTri
                FROM Phong p
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN CumNha cn ON tn.MaCum = cn.MaCum
                JOIN KhuKTX k ON cn.MaKhu = k.MaKhu
                LEFT JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                LEFT JOIN Giuong g ON p.MaPhong = g.MaPhong
                WHERE " . implode(' AND ', $where) . "
                GROUP BY p.MaPhong
                ORDER BY k.TenKhu, tn.TenToaNha, p.Tang, p.SoPhong
                LIMIT 200
            ";

            $rooms = Database::fetchAll($sql, $params);
            $this->success($rooms, 'Lấy danh sách phòng thành công.');
        } catch (Throwable $e) {
            $this->error('Lỗi khi tra cứu danh sách phòng: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API tra cứu chi tiết các giường trong 1 phòng
     * Route: GET /api/rooms/beds
     */
    public function beds(): void
    {
        $request = new Request();
        $roomId = trim((string)$request->input('room', ''));

        if (empty($roomId)) {
            $this->error('Vui lòng cung cấp mã phòng (room).', 422);
            return;
        }

        try {
            $beds = Database::fetchAll(
                "SELECT g.MaGiuong, g.KyHieuGiuong, g.ViTriTangGiuong, g.TrangThaiGiuong,
                        sv.MaSinhVien, sv.HoVaTen, sv.MSSVTaiTruong, sv.SoDienThoai
                 FROM Giuong g
                 LEFT JOIN HopDongNoiTru hd ON hd.MaGiuong = g.MaGiuong AND hd.TrangThaiHopDong = 'Đang hiệu lực'
                 LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
                 WHERE g.MaPhong = :room
                 ORDER BY g.KyHieuGiuong",
                ['room' => $roomId]
            );

            $this->success($beds, 'Lấy danh sách giường thành công.');
        } catch (Throwable $e) {
            $this->error('Lỗi khi tra cứu danh sách giường: ' . $e->getMessage(), 500);
        }
    }

    /**
     * API thống kê tổng quan hạ tầng KTX (số phòng, công suất, chỗ trống)
     * Route: GET /api/rooms/stats
     */
    public function stats(): void
    {
        try {
            $stats = Database::fetch(
                "SELECT 
                    COUNT(DISTINCT p.MaPhong) AS TongSoPhong,
                    COUNT(g.MaGiuong) AS TongSoGiuong,
                    SUM(CASE WHEN g.TrangThaiGiuong IN ('Đang ở', 'Da_o', 'Đã duyệt') THEN 1 ELSE 0 END) AS TongDangO,
                    SUM(CASE WHEN g.TrangThaiGiuong IN ('Trống', 'Còn trống', 'Trong') THEN 1 ELSE 0 END) AS TongTrong,
                    SUM(CASE WHEN g.TrangThaiGiuong IN ('Bảo trì', 'Khoá') THEN 1 ELSE 0 END) AS TongBaoTri
                 FROM Phong p
                 LEFT JOIN Giuong g ON p.MaPhong = g.MaPhong"
            );

            $this->success($stats, 'Lấy thống kê hạ tầng thành công.');
        } catch (Throwable $e) {
            $this->error('Lỗi khi tính toán thống kê: ' . $e->getMessage(), 500);
        }
    }
}

