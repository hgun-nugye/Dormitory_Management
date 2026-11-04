<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\UtilityBillingService;

/**
 * Controller: Quản lý Điện nước, Ghi chỉ số, Phát hiện bất thường & Đối soát công nợ
 * View: views/staff/utilities.php
 */
class UtilityController extends Controller
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

    public function utilities(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $thang = (int)($_GET['thang'] ?? date('n'));
        $nam = (int)($_GET['nam'] ?? date('Y'));
        if ($thang < 1 || $thang > 12) $thang = (int)date('n');
        if ($nam < 2020 || $nam > 2030) $nam = (int)date('Y');

        // 1. Danh sách phòng thuộc Cụm quản lý
        $rooms = Database::fetchAll(
            "SELECT p.MaPhong, p.SoPhong, p.Tang, p.SoSVDangO, tn.TenToaNha
             FROM Phong p
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             WHERE tn.MaCum = :cum
             ORDER BY tn.TenToaNha ASC, p.SoPhong ASC",
            ['cum' => $maCum]
        );

        // Lấy chỉ số cũ của các phòng để phục vụ auto-fill JavaScript
        $roomMetadata = [];
        foreach ($rooms as $r) {
            $prev = UtilityBillingService::getPreviousReading($r['MaPhong'], $thang, $nam);
            $roomMetadata[$r['MaPhong']] = [
                'dienCu' => $prev['dienCu'],
                'nuocCu' => $prev['nuocCu'],
                'soSV' => max(1, (int)$r['SoSVDangO']),
                'soPhong' => $r['SoPhong'],
                'toa' => $r['TenToaNha']
            ];
        }

        // 2. Danh sách chỉ số đã ghi trong kỳ của Cụm
        $readings = Database::fetchAll(
            "SELECT cs.*, p.SoPhong, tn.TenToaNha,
                    (SELECT hd.MaHoaDon FROM HoaDon hd 
                     WHERE hd.MaPhong = cs.MaPhong 
                       AND hd.LoaiHoaDon LIKE '%iện nước%' 
                       AND hd.LoaiHoaDon LIKE CONCAT('%', LPAD(cs.Thang, 2, '0'), '/', cs.Nam, '%') 
                     LIMIT 1) AS MaHoaDon,
                    (SELECT hd.TrangThai FROM HoaDon hd 
                     WHERE hd.MaPhong = cs.MaPhong 
                       AND hd.LoaiHoaDon LIKE '%iện nước%' 
                       AND hd.LoaiHoaDon LIKE CONCAT('%', LPAD(cs.Thang, 2, '0'), '/', cs.Nam, '%') 
                     LIMIT 1) AS TrangThaiHoaDon
             FROM ChiSoDienNuoc cs
             JOIN Phong p ON cs.MaPhong = p.MaPhong
             JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             WHERE tn.MaCum = :cum AND cs.Thang = :thang AND cs.Nam = :nam
             ORDER BY cs.CanhBaoBatThuong DESC, cs.MaPhong ASC",
            ['cum' => $maCum, 'thang' => $thang, 'nam' => $nam]
        );

        // 3. Danh sách Hóa đơn Điện nước & Giao dịch thanh toán
        $invoices = Database::fetchAll(
            "SELECT hd.*, p.SoPhong, tn.TenToaNha, sv.HoVaTen AS TenSinhVien, sv.MSSVTaiTruong,
                    gd.MaGiaoDich, gd.KenhThanhToan, gd.ThoiGianGiaoDich
             FROM HoaDon hd
             LEFT JOIN Phong p ON hd.MaPhong = p.MaPhong
             LEFT JOIN ToaNha tn ON p.MaToaNha = tn.MaToaNha
             LEFT JOIN SinhVien sv ON hd.MaSinhVien = sv.MaSinhVien
             LEFT JOIN GiaoDichThanhToan gd ON hd.MaHoaDon = gd.MaHoaDon
             WHERE (tn.MaCum = :cum OR tn.MaCum IS NULL)
               AND hd.LoaiHoaDon LIKE '%iện nước%'
             ORDER BY (hd.TrangThai = 'Chưa thanh toán') DESC, hd.NgayTao DESC
             LIMIT 50",
            ['cum' => $maCum]
        );

        $this->view('staff.utilities', [
            'user' => $user,
            'maCum' => $maCum,
            'thang' => $thang,
            'nam' => $nam,
            'rooms' => $rooms,
            'roomMetadata' => $roomMetadata,
            'readings' => $readings,
            'invoices' => $invoices,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    /**
     * POST: Lưu chỉ số điện nước & kiểm tra bất thường
     */
    public function saveReading(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));
        $dienMoi = (int)$request->input('dienMoi', 0);
        $nuocMoi = (int)$request->input('nuocMoi', 0);
        $dienCu = $request->input('dienCu') !== null ? (int)$request->input('dienCu') : null;
        $nuocCu = $request->input('nuocCu') !== null ? (int)$request->input('nuocCu') : null;
        $soSV = (int)$request->input('soSV', 0);

        if (empty($maPhong)) {
            Session::flash('error', 'Vui lòng chọn mã phòng.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        $res = UtilityBillingService::recordReading($maPhong, $thang, $nam, $dienMoi, $nuocMoi, $dienCu, $nuocCu, $soSV);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
    }

    /**
     * POST: Phát hành hóa đơn điện nước cho 1 phòng
     */
    public function publishInvoice(): void
    {
        $request = new Request();
        $maPhong = trim((string)$request->input('maPhong', ''));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));

        if (empty($maPhong)) {
            Session::flash('error', 'Mã phòng không hợp lệ.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        $res = UtilityBillingService::publishInvoice($maPhong, $thang, $nam);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
    }

    /**
     * POST: Chốt sổ & Phát hành hóa đơn cho toàn Cụm nhà
     */
    public function publishClusterInvoices(): void
    {
        $request = new Request();
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));
        $maCum = $this->getMaCum();

        $res = UtilityBillingService::publishClusterInvoices($maCum, $thang, $nam);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
    }

    /**
     * POST: Xác nhận thanh toán mô phỏng / Gạch nợ
     */
    public function confirmPayment(): void
    {
        $request = new Request();
        $maHoaDon = trim((string)$request->input('maHoaDon', ''));
        $phuongThuc = trim((string)$request->input('phuongThuc', 'Mã QR KTX'));
        $thang = (int)$request->input('thang', date('n'));
        $nam = (int)$request->input('nam', date('Y'));

        if (empty($maHoaDon)) {
            Session::flash('error', 'Mã hóa đơn không hợp lệ.');
            $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
            return;
        }

        $res = UtilityBillingService::simulatePayment($maHoaDon, $phuongThuc);

        if ($res['success']) {
            Session::flash('success', $res['message']);
        } else {
            Session::flash('error', $res['message']);
        }

        $this->redirect("staff/utilities?thang={$thang}&nam={$nam}");
    }
}
