<?php
$pdo = $pdo ?? null;

$demoStudent = [
    'MaSinhVien' => '2110001',
    'HoVaTen' => 'Nguyễn Văn An',
    'NgaySinh' => '2005-05-15',
    'GioiTinh' => 'Nam',
    'SoCCCD' => '079205001234',
    'SoDienThoai' => '0901234567',
    'Email' => 'an2110001@example.com',
    'TenTruong' => 'Đại học Bách khoa',
    'TrangThaiNoiTru' => 'Đang nội trú',
];

$studentId = $_SESSION['student_id'];
$studentRole = $_SESSION['student_role'] ?? 'SINH_VIEN';
$student = $demoStudent;
$contract = [
    'MaHopDong' => 'HD2026001',
    'NgayBatDau' => '2026-09-01',
    'NgayKetThuc' => '2027-06-30',
    'TrangThaiHopDong' => 'Đang hiệu lực',
];
$room = ['MaPhong' => 'A01.101', 'SoPhong' => '101', 'MaLoaiPhong' => 'LP4', 'TenLoaiPhong' => 'Phòng tiêu chuẩn', 'Tang' => 1, 'MaToaNha' => 'A01'];
$bills = [];
$transactions = [];
$requests = [];
$repairRequests = [];
$activities = [];
$beds = [];

if ($pdo) {
    try {
        $stmt = $pdo->prepare(
            'SELECT sv.*, td.TenTruong, tk.MaVaiTro
             FROM SinhVien sv
             JOIN TruongDaiHoc td ON td.MaTruong = sv.MaTruong
             JOIN TaiKhoan tk ON tk.MaTaiKhoan = sv.MaTaiKhoan
             WHERE sv.MaSinhVien = ?'
        );
        $stmt->execute([$studentId]);
        if ($row = $stmt->fetch()) {
            $student = array_merge($student, $row);
            $studentRole = $row['MaVaiTro'];
        }

        $stmt = $pdo->prepare(
            'SELECT hd.*, g.KyHieuGiuong, p.*, lp.TenLoaiPhong, tn.TenToaNha
             FROM HopDongNoiTru hd
             JOIN Giuong g ON g.MaGiuong = hd.MaGiuong
             JOIN Phong p ON p.MaPhong = g.MaPhong
             JOIN LoaiPhong lp ON lp.MaLoaiPhong = p.MaLoaiPhong
             JOIN ToaNha tn ON tn.MaToaNha = p.MaToaNha
             WHERE hd.MaSinhVien = ? AND hd.TrangThaiHopDong NOT IN ("Đã thanh lý","Đã hủy")
             ORDER BY hd.NgayBatDau DESC LIMIT 1'
        );
        $stmt->execute([$studentId]);
        if ($row = $stmt->fetch()) {
            $contract = $row;
            $room = $row;
        }

        $stmt = $pdo->prepare('SELECT * FROM HoaDon WHERE MaSinhVien = ? ORDER BY NgayTao DESC LIMIT 20');
        $stmt->execute([$studentId]);
        $bills = $stmt->fetchAll();

        if ($studentRole === 'TRUONG_PHONG' && !empty($room['MaPhong'])) {
            $stmt = $pdo->prepare(
                'SELECT h.* FROM HoaDon h
                 JOIN Phong p ON p.MaPhong = h.MaPhong
                 WHERE p.MaPhong = ? AND p.TruongPhong = ?
                 ORDER BY h.NgayTao DESC LIMIT 20'
            );
            $stmt->execute([$room['MaPhong'], $studentId]);
            foreach ($stmt->fetchAll() as $roomBill) {
                $roomBill['_roomRepresentative'] = true;
                $bills[] = $roomBill;
            }
        }

        $placeholders = implode(',', array_fill(0, count($bills), '?'));
        if ($placeholders) {
            $stmt = $pdo->prepare('SELECT g.* FROM GiaoDichThanhToan g WHERE g.MaHoaDon IN (' . $placeholders . ') ORDER BY g.ThoiGianGiaoDich DESC');
            $stmt->execute(array_column($bills, 'MaHoaDon'));
            $transactions = $stmt->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT d.*, h.TrangThaiHopDong FROM DonTuMotCua d LEFT JOIN HopDongNoiTru h ON h.MaHopDong=d.MaHopDong WHERE d.MaSinhVien=? ORDER BY d.MaDon DESC LIMIT 20');
        $stmt->execute([$studentId]);
        $requests = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT MaPhieu, MaPhong, MoTaHuHong, TrangThaiSua, NgayBao FROM PhieuBaoHong WHERE MaSinhVienBao=? ORDER BY NgayBao DESC LIMIT 20');
        $stmt->execute([$studentId]);
        $repairRequests = $stmt->fetchAll();

        $stmt = $pdo->prepare('SELECT * FROM RenLuyenNoiTru WHERE MaSinhVien=? ORDER BY NamHoc DESC, HocKy DESC, MaDanhGia DESC');
        $stmt->execute([$studentId]);
        $activities = $stmt->fetchAll();

        if (!empty($contract['MaHopDong'])) {
            $stmt = $pdo->prepare('SELECT g.MaGiuong, g.KyHieuGiuong, p.MaPhong, p.SoPhong FROM Giuong g JOIN Phong p ON p.MaPhong=g.MaPhong WHERE g.TrangThaiGiuong IN ("Trống","Cho phép chuyển") ORDER BY p.MaPhong,g.KyHieuGiuong LIMIT 30');
            $stmt->execute();
            $beds = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        $message = 'Không thể tải đầy đủ dữ liệu từ cơ sở dữ liệu.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update-contact') {
    if (!validateCsrfToken($_POST)) {
        $message = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
    } else {
        $phone = trim($_POST['SoDienThoai'] ?? '');
        $email = trim($_POST['Email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Email không hợp lệ.';
        } elseif (!preg_match('/^[0-9+\s.-]{9,15}$/', $phone)) {
            $message = 'Số điện thoại không hợp lệ.';
        } elseif ($pdo) {
            $stmt = $pdo->prepare('UPDATE SinhVien SET SoDienThoai=?, Email=? WHERE MaSinhVien=?');
            $stmt->execute([$phone, $email, $studentId]);
            $student['SoDienThoai'] = $phone;
            $student['Email'] = $email;
            $message = 'Đã cập nhật thông tin liên hệ.';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create-request') {
    if (!validateCsrfToken($_POST)) {
        $message = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
    } else {
        $type = trim($_POST['LoaiDon'] ?? '');
        $reason = trim($_POST['LyDo'] ?? '');
        $allowed = ['Xin chuyển phòng', 'Xin sửa chữa', 'Xin tạm vắng', 'Xin trả phòng', 'Xin gia hạn'];
        if (!in_array($type, $allowed, true) || mb_strlen($reason) < 10) {
            $message = 'Vui lòng chọn loại đơn và nhập lý do ít nhất 10 ký tự.';
        } elseif ($pdo && isset($contract['MaHopDong'])) {
            try {
                $pdo->beginTransaction();
                if ($type === 'Xin sửa chữa') {
                    $equipmentId = !empty($_POST['MaThietBi']) ? filter_var($_POST['MaThietBi'], FILTER_VALIDATE_INT) : null;
                    if ($equipmentId !== null) {
                        $stmt = $pdo->prepare('SELECT MaThietBi FROM ThietBiPhong WHERE MaThietBi=? AND MaPhong=?');
                        $stmt->execute([$equipmentId, $room['MaPhong']]);
                        if (!$stmt->fetch()) {
                            throw new InvalidArgumentException('ThietBi khong thuoc phong hien tai');
                        }
                    }
                    $stmt = $pdo->prepare('INSERT INTO PhieuBaoHong (MaPhieu,MaPhong,MaThietBi,MaSinhVienBao,MoTaHuHong,HinhAnh,TrangThaiSua,NgayBao) VALUES (?,?,?,?,?,?,?,NOW())');
                    $stmt->execute(['BH' . date('YmdHis'), $room['MaPhong'], $equipmentId, $studentId, $reason, (!empty($_POST['TepMinhChung']) ? $_POST['TepMinhChung'] : null), 'Chờ xử lý']);
                } else {
                    $destinationBed = !empty($_POST['MaGiuongChuyenDen']) ? trim($_POST['MaGiuongChuyenDen']) : null;
                    if ($destinationBed !== null) {
                        $stmt = $pdo->prepare(
                            'SELECT g.MaGiuong FROM Giuong g
                             WHERE g.MaGiuong=? AND g.TrangThaiGiuong IN (?,?) AND g.MaPhong<>?'
                        );
                        $stmt->execute([$destinationBed, 'Trống', 'Cho phép chuyển', $room['MaPhong']]);
                        if (!$stmt->fetch()) {
                            throw new InvalidArgumentException('Giuong chuyen den khong hop le');
                        }
                    }
                    $stmt = $pdo->prepare('INSERT INTO DonTuMotCua (MaDon,MaSinhVien,MaHopDong,LoaiDon,MaGiuongChuyenDen,TuNgay,DenNgay,LyDo,TepMinhChung,TrangThaiDuyet) VALUES (?,?,?,?,?,?,?,?,?,?)');
                    $stmt->execute(['DON' . date('YmdHis'), $studentId, $contract['MaHopDong'], $type, $destinationBed, (!empty($_POST['TuNgay']) ? $_POST['TuNgay'] : null), (!empty($_POST['DenNgay']) ? $_POST['DenNgay'] : null), $reason, (!empty($_POST['TepMinhChung']) ? $_POST['TepMinhChung'] : null), 'Chờ duyệt']);
                }
                $pdo->commit();
                $message = 'Đã gửi yêu cầu thành công.';
            } catch (InvalidArgumentException $e) {
                $pdo->rollBack();
                $message = 'Thông tin giường hoặc thiết bị không hợp lệ.';
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = 'Không thể gửi yêu cầu.';
            }
        }
    }
}
