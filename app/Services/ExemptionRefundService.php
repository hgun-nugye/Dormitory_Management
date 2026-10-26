<?php

namespace App\Services;

use App\Core\Database;
use DateTime;
use Exception;

/**
 * CHỨC NĂNG 5: Nghiệp vụ Xét miễn giảm theo Trần định mức & Trả phòng hoàn phí trước hạn
 */
class ExemptionRefundService {
    // Giá trần tối đa theo quy định áp dụng cho phòng tiêu chuẩn 6-8 người (350.000 đ/tháng)
    const DON_GIA_TRAN_MAC_DINH = 350000;

    /**
     * Thuật toán tính số tiền hoàn trả miễn giảm theo trần định mức:
     */
    public static function calculateExemptionAmount(string $doiTuong, float $donGiaPhong, float $phiDichVuTangThem, int $soThang = 5): array {
        $phanTramGiam = 0;
        $tranApDung = self::DON_GIA_TRAN_MAC_DINH;
        $mienCaDichVu = false;

        switch ($doiTuong) {
            case 'Sinh viên khuyết tật':
                $phanTramGiam = 100;
                $mienCaDichVu = true;
                break;
            case 'Mồ côi cả cha lẫn mẹ':
            case 'Hộ nghèo':
            case 'Hộ cận nghèo':
            case 'Bệnh hiểm nghèo':
                $phanTramGiam = 50;
                break;
            case 'Con thương binh/liệt sĩ':
                $phanTramGiam = 30;
                break;
            default:
                $phanTramGiam = 0;
        }

        // Định mức cơ sở để tính giảm: min(donGiaPhong, tranApDung)
        $giaCoSo = min($donGiaPhong, $tranApDung);
        $tienGiamMoiThang = ($giaCoSo * $phanTramGiam) / 100;

        if ($mienCaDichVu) {
            $tienGiamMoiThang += $phiDichVuTangThem;
        }

        $tongHoanTra = $tienGiamMoiThang * $soThang;

        return [
            'doiTuong' => $doiTuong,
            'phanTramGiam' => $phanTramGiam,
            'donGiaTran' => $tranApDung,
            'giaCoSoApDung' => $giaCoSo,
            'tienGiamMoiThang' => $tienGiamMoiThang,
            'soThang' => $soThang,
            'soTienHoanTra' => $tongHoanTra
        ];
    }

    /**
     * Kiểm tra điều kiện hoàn phí khi trả phòng sớm:
     * - Nếu tự ý bỏ: không hoàn phí.
     * - Nếu có lý do hợp lệ (tốt nghiệp, bảo lưu, du học) và làm thủ tục trong 7 ngày: hoàn số tháng thực tế chưa ở.
     */
    public static function processEarlyCheckout(string $maSinhVien, string $lyDo, ?string $ngayQuyetDinhTruong, string $tepMinhChung): array {
        $hopDong = Database::fetch(
            "SELECT hd.*, lp.DonGiaLuuTruChuan, lp.DonGiaDichVuTangThem
             FROM HopDongNoiTru hd
             JOIN Giuong g ON hd.MaGiuong = g.MaGiuong
             JOIN Phong p ON g.MaPhong = p.MaPhong
             JOIN LoaiPhong lp ON p.MaLoaiPhong = lp.MaLoaiPhong
             WHERE hd.MaSinhVien = :sv AND hd.TrangThaiHopDong = 'Đang hiệu lực'",
            ['sv' => $maSinhVien]
        );

        if (!$hopDong) {
            return ['success' => false, 'message' => 'Không tìm thấy hợp đồng nội trú còn hiệu lực'];
        }

        $hopLe = false;
        $soTienHoan = 0;
        $ghiChu = '';

        $lyDoHopLe = ['Tốt nghiệp sớm', 'Bảo lưu kết quả học tập', 'Đi du học', 'Đình chỉ học tập do lý do sức khỏe'];

        if (in_array($lyDo, $lyDoHopLe) && !empty($ngayQuyetDinhTruong)) {
            $qdDate = new DateTime($ngayQuyetDinhTruong);
            $today = new DateTime();
            $interval = $today->diff($qdDate)->days;

            if ($interval <= 7) {
                $hopLe = true;
                // Tính số tháng thực tế chưa ở
                $endDate = new DateTime($hopDong['NgayKetThuc']);
                $diff = $today->diff($endDate);
                $soThangChuaO = ($diff->y * 12) + $diff->m;
                if ($soThangChuaO > 0) {
                    $donGiaThang = (float)$hopDong['DonGiaLuuTruChuan'] + (float)$hopDong['DonGiaDichVuTangThem'];
                    $soTienHoan = $soThangChuaO * $donGiaThang;
                    $ghiChu = "Hồ sơ hợp lệ (nộp trong {$interval} ngày). Hoàn phí {$soThangChuaO} tháng chưa ở.";
                }
            } else {
                $ghiChu = "Quá thời hạn 07 ngày kể từ ngày trường ra quyết định ({$interval} ngày). Không áp dụng hoàn phí.";
            }
        } else {
            $ghiChu = "Sinh viên trả phòng trước hạn vì lý do cá nhân / tự ý rời KTX. Theo quy chế không hoàn phí.";
        }

        return [
            'success' => true,
            'hopLe' => $hopLe,
            'soTienHoanTra' => $soTienHoan,
            'ghiChu' => $ghiChu
        ];
    }
}
