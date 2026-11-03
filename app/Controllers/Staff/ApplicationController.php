<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\AutoReleaseService;

/**
 * Controller: Quản lý Hồ sơ Đăng ký nội trú & Xét duyệt 72h
 * View: views/staff/applications.php
 */
class ApplicationController extends Controller
{
    private function getMaCum(): string
    {
        $user = Auth::user();
        if (!empty($user['MaCumQuanLy'])) {
            return $user['MaCumQuanLy'];
        }
        $firstCum = Database::fetch("SELECT MaCum FROM CumNha LIMIT 1");
        return $firstCum['MaCum'] ?? 'AG';
    }

    /**
     * Danh sách hồ sơ đăng ký & duyệt chỗ 72h
     */
    public function applications(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $pendingApplications = [];
        $active72hApplications = [];
        $recentApplications = [];

        try {
            // 1. Hồ sơ Chờ duyệt trong Cụm
            $pendingApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.GioiTinh, sv.SoCCCD, sv.SoDienThoai, sv.MSSVTaiTruong,
                       t.TenTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong, lp.TenLoaiPhong,
                       (lp.DonGiaLuuTruChuan + lp.DonGiaDichVuTangThem) AS DonGiaThang
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN TruongDaiHoc t ON sv.MaTruong = t.MaTruong
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
                WHERE hs.TrangThaiHoSo = 'Chờ duyệt' AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianNop ASC
            ", ['cum' => $maCum]);

            // 2. Hồ sơ đang trong hạn giữ chỗ 72h (Chờ thanh toán)
            $active72hApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong,
                       hd.SoTienPhaiDong, hd.TrangThai AS TrangThaiHoaDon,
                       TIMESTAMPDIFF(MINUTE, NOW(), hs.HanChotThanhToan) AS SoPhutConLai,
                       TIMESTAMPDIFF(HOUR, NOW(), hs.HanChotThanhToan) AS SoGioConLai
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                LEFT JOIN HoaDon hd ON hd.MaHoaDon = CONCAT('HD-', hs.MaHoSo)
                WHERE hs.TrangThaiHoSo = 'Chờ thanh toán' AND tn.MaCum = :cum
                ORDER BY hs.HanChotThanhToan ASC
            ", ['cum' => $maCum]);

            // 3. Lịch sử hồ sơ đã xử lý gần đây
            $recentApplications = Database::fetchAll("
                SELECT hs.*, sv.HoVaTen, sv.MSSVTaiTruong, p.SoPhong, tn.TenToaNha, g.KyHieuGiuong
                FROM HoSoDangKy hs
                JOIN SinhVien sv ON hs.MaSinhVien = sv.MaSinhVien
                JOIN Giuong g ON hs.MaGiuong = g.MaGiuong
                JOIN Phong p ON g.MaPhong = p.MaPhong
                JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
                WHERE hs.TrangThaiHoSo IN ('Đã duyệt', 'Đã từ chối', 'Hết hạn - Đã hủy chỗ') AND tn.MaCum = :cum
                ORDER BY hs.ThoiGianDuyet DESC
                LIMIT 10
            ", ['cum' => $maCum]);
        } catch (\Throwable $e) {
            // Trường hợp lỗi kết nối hoặc DB chưa khởi tạo
        }

        $this->view('staff.applications', [
            'user' => $user,
            'maCum' => $maCum,
            'pendingApplications' => $pendingApplications,
            'active72hApplications' => $active72hApplications,
            'recentApplications' => $recentApplications,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    /**
     * Phê duyệt hồ sơ đăng ký và cấp quyền giữ chỗ 72h
     */
    public function approveApplication(): void
    {
        $request = new Request();
        $maHoSo = trim((string)$request->input('maHoSo', ''));
        $userId = Auth::id() ?? 3;

        if (empty($maHoSo)) {
            Session::flash('error', 'Mã hồ sơ không hợp lệ.');
            $this->redirect('staff/applications');
            return;
        }

        $result = AutoReleaseService::approveApplication($maHoSo, $userId, 72);

        if ($result['success']) {
            $hanChotFmt = date('d/m/Y H:i', strtotime($result['hanChotThanhToan']));
            Session::flash('success', "Đã phê duyệt giữ chỗ 72h cho hồ sơ {$maHoSo}. Hạn chót thanh toán: {$hanChotFmt}. Hóa đơn lệ phí đã được tự động khởi tạo.");
        } else {
            Session::flash('error', $result['message'] ?? 'Phê duyệt hồ sơ thất bại.');
        }

        $this->redirect('staff/applications');
    }

    /**
     * Từ chối hồ sơ đăng ký nội trú
     */
    public function rejectApplication(): void
    {
        $request = new Request();
        $maHoSo = trim((string)$request->input('maHoSo', ''));
        $lyDo = trim((string)$request->input('lyDo', ''));
        $userId = Auth::id() ?? 3;

        if (empty($maHoSo)) {
            Session::flash('error', 'Mã hồ sơ không hợp lệ.');
            $this->redirect('staff/applications');
            return;
        }

        $result = AutoReleaseService::rejectApplication($maHoSo, $userId, $lyDo);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message'] ?? 'Từ chối hồ sơ thất bại.');
        }

        $this->redirect('staff/applications');
    }
}

