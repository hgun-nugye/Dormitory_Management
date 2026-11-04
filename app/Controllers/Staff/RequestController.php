<?php

namespace App\Controllers\Staff;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Session;
use App\Services\RoomTransferService;

/**
 * Controller: Tiếp nhận & Xử lý Yêu cầu / Đơn một cửa của Sinh viên
 * View: views/staff/requests.php
 */
class RequestController extends Controller
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

    public function requests(): void
    {
        $user = Auth::user();
        $maCum = $this->getMaCum();

        $type = $_GET['type'] ?? 'all';
        $status = $_GET['status'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        $sql = "
            SELECT d.*,
                   sv.HoVaTen, sv.MSSVTaiTruong, sv.SoDienThoai, sv.MaTruong,
                   dh.TenTruong,
                   hd.MaGiuong AS MaGiuongHienTai,
                   gCur.MaPhong AS MaPhongHienTai,
                   pCur.SoPhong AS SoPhongHienTai,
                   tCur.TenToaNha AS TenToaHienTai,
                   cCur.MaCum AS MaCumHienTai,
                   gNew.MaPhong AS MaPhongMoi,
                   pNew.SoPhong AS SoPhongMoi,
                   tNew.TenToaNha AS TenToaMoi,
                   cNew.MaCum AS MaCumMoi
            FROM DonTuMotCua d
            LEFT JOIN SinhVien sv ON d.MaSinhVien = sv.MaSinhVien
            LEFT JOIN TruongDaiHoc dh ON sv.MaTruong = dh.MaTruong
            LEFT JOIN HopDongNoiTru hd ON d.MaHopDong = hd.MaHopDong
            LEFT JOIN Giuong gCur ON hd.MaGiuong = gCur.MaGiuong
            LEFT JOIN Phong pCur ON gCur.MaPhong = pCur.MaPhong
            LEFT JOIN ToaNha tCur ON pCur.MaToaNha = tCur.MaToaNha
            LEFT JOIN CumNha cCur ON tCur.MaCum = cCur.MaCum
            LEFT JOIN Giuong gNew ON d.MaGiuongChuyenDen = gNew.MaGiuong
            LEFT JOIN Phong pNew ON gNew.MaPhong = pNew.MaPhong
            LEFT JOIN ToaNha tNew ON pNew.MaToaNha = tNew.MaToaNha
            LEFT JOIN CumNha cNew ON tNew.MaCum = cNew.MaCum
            WHERE 1=1
        ";

        $params = [];

        // Lọc theo loại đơn
        if ($type === 'chuyen-phong') {
            $sql .= " AND (d.LoaiDon LIKE '%chuyển phòng%' OR d.LoaiDon LIKE '%Chuyển phòng%')";
        } elseif ($type === 'tam-vang') {
            $sql .= " AND (d.LoaiDon LIKE '%tạm vắng%' OR d.LoaiDon LIKE '%Tạm vắng%')";
        } elseif ($type === 'tra-phong') {
            $sql .= " AND (d.LoaiDon LIKE '%trả phòng%' OR d.LoaiDon LIKE '%Trả phòng%')";
        } elseif ($type === 'dong-phi') {
            $sql .= " AND (d.LoaiDon LIKE '%phí%' OR d.LoaiDon LIKE '%học kỳ%')";
        }

        // Lọc theo trạng thái duyệt
        if ($status === 'cho-duyet') {
            $sql .= " AND d.TrangThaiDuyet = 'Chờ duyệt'";
        } elseif ($status === 'da-duyet') {
            $sql .= " AND d.TrangThaiDuyet = 'Đã duyệt'";
        } elseif ($status === 'tu-choi') {
            $sql .= " AND (d.TrangThaiDuyet = 'Từ chối' OR d.TrangThaiDuyet = 'Đã từ chối')";
        }

        // Tìm kiếm theo từ khóa
        if (!empty($search)) {
            $sql .= " AND (d.MaDon LIKE :s OR sv.HoVaTen LIKE :s OR sv.MSSVTaiTruong LIKE :s OR sv.MaSinhVien LIKE :s)";
            $params['s'] = "%{$search}%";
        }

        $sql .= " ORDER BY (d.TrangThaiDuyet = 'Chờ duyệt') DESC, d.MaDon DESC";

        $requests = Database::fetchAll($sql, $params);

        // Tính counts cho các Tabs
        $counts = [
            'all' => Database::fetch("SELECT COUNT(*) AS c FROM DonTuMotCua")['c'] ?? 0,
            'chuyen_phong' => Database::fetch("SELECT COUNT(*) AS c FROM DonTuMotCua WHERE LoaiDon LIKE '%chuyển phòng%' OR LoaiDon LIKE '%Chuyển phòng%'")['c'] ?? 0,
            'tam_vang' => Database::fetch("SELECT COUNT(*) AS c FROM DonTuMotCua WHERE LoaiDon LIKE '%tạm vắng%' OR LoaiDon LIKE '%Tạm vắng%'")['c'] ?? 0,
            'tra_phong' => Database::fetch("SELECT COUNT(*) AS c FROM DonTuMotCua WHERE LoaiDon LIKE '%trả phòng%' OR LoaiDon LIKE '%Trả phòng%'")['c'] ?? 0,
            'cho_duyet' => Database::fetch("SELECT COUNT(*) AS c FROM DonTuMotCua WHERE TrangThaiDuyet = 'Chờ duyệt'")['c'] ?? 0,
        ];

        $this->view('staff.requests', [
            'user' => $user,
            'maCum' => $maCum,
            'requests' => $requests,
            'counts' => $counts,
            'type' => $type,
            'status' => $status,
            'search' => $search,
            'success' => Session::flash('success'),
            'error' => Session::flash('error')
        ]);
    }

    /**
     * POST: Phê duyệt đơn một cửa
     */
    public function approveRequest(): void
    {
        $request = new Request();
        $maDon = trim((string)$request->input('maDon', ''));
        $userId = Auth::id() ?? 3;

        if (empty($maDon)) {
            Session::flash('error', 'Mã đơn không hợp lệ.');
            $this->redirect('staff/requests');
            return;
        }

        $result = RoomTransferService::approveRequest($maDon, (string)$userId);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/requests');
    }

    /**
     * POST: Từ chối đơn một cửa
     */
    public function rejectRequest(): void
    {
        $request = new Request();
        $maDon = trim((string)$request->input('maDon', ''));
        $lyDo = trim((string)$request->input('lyDoTuChoi', ''));

        if (empty($maDon)) {
            Session::flash('error', 'Mã đơn không hợp lệ.');
            $this->redirect('staff/requests');
            return;
        }

        if (empty($lyDo)) {
            $lyDo = 'Không đáp ứng điều kiện quy định KTX';
        }

        $result = RoomTransferService::rejectRequest($maDon, $lyDo);

        if ($result['success']) {
            Session::flash('success', $result['message']);
        } else {
            Session::flash('error', $result['message']);
        }

        $this->redirect('staff/requests');
    }
}
