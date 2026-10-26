<?php

namespace App\Services;

use App\Core\Database;

/**
 * CHỨC NĂNG 6: Tính tiền Điện – Nước bậc thang & Kiểm tra bất thường khi nhập liệu (Utility Anomaly Detection)
 */
class UtilityBillingService {
    // Biểu giá điện bậc thang (VNĐ/kWh)
    const TIER_DIEN = [
        ['limit' => 50,  'price' => 1806],
        ['limit' => 100, 'price' => 1866],
        ['limit' => 200, 'price' => 2167],
        ['limit' => 300, 'price' => 2729],
        ['limit' => 400, 'price' => 3050],
        ['limit' => PHP_INT_MAX, 'price' => 3151],
    ];

    // Đơn giá nước sạch KTX (VNĐ/m3)
    const DON_GIA_NUOC = 12500;

    /**
     * Kiểm tra tính bất thường của chỉ số điện/nước khi nhập liệu hàng loạt
     */
    public static function validateUtilityInput(string $maPhong, int $dienCu, int $dienMoi, int $nuocCu, int $nuocMoi): array {
        $warnings = [];
        $isAnomaly = false;

        // 1. Kiểm tra chỉ số mới < chỉ số cũ
        if ($dienMoi < $dienCu) {
            $warnings[] = "Chỉ số điện mới ({$dienMoi}) < chỉ số cũ ({$dienCu}). Có thể sai sót nhập liệu hoặc đồng hồ quay vòng.";
            $isAnomaly = true;
        }

        if ($nuocMoi < $nuocCu) {
            $warnings[] = "Chỉ số nước mới ({$nuocMoi}) < chỉ số cũ ({$nuocCu}). Cần kiểm tra lại.";
            $isAnomaly = true;
        }

        $dienTieuThu = max(0, $dienMoi - $dienCu);
        $nuocTieuThu = max(0, $nuocMoi - $nuocCu);

        // 2. So sánh với trung bình 3 tháng gần nhất của phòng
        $history = Database::fetchAll(
            "SELECT (DienMoi - DienCu) AS Dien, (NuocMoi - NuocCu) AS Nuoc
             FROM ChiSoDienNuoc
             WHERE MaPhong = :phong
             ORDER BY Nam DESC, Thang DESC
             LIMIT 3",
            ['phong' => $maPhong]
        );

        if (!empty($history)) {
            $avgDien = array_sum(array_column($history, 'Dien')) / count($history);
            $avgNuoc = array_sum(array_column($history, 'Nuoc')) / count($history);

            if ($avgDien > 0 && $dienTieuThu >= ($avgDien * 3)) {
                $warnings[] = "Tiêu thụ điện ({$dienTieuThu} kWh) tăng đột biến gấp >3 lần trung bình 3 tháng gần nhất ({$avgDien} kWh). Nghi ngờ chập điện!";
                $isAnomaly = true;
            }

            if ($avgNuoc > 0 && $nuocTieuThu >= ($avgNuoc * 3)) {
                $warnings[] = "Tiêu thụ nước ({$nuocTieuThu} m3) tăng đột biến gấp >3 lần trung bình 3 tháng gần nhất ({$avgNuoc} m3). Nghi ngờ rò rỉ đường ống nước!";
                $isAnomaly = true;
            }
        }

        return [
            'isAnomaly' => $isAnomaly,
            'dienTieuThu' => $dienTieuThu,
            'nuocTieuThu' => $nuocTieuThu,
            'warnings' => $warnings
        ];
    }

    /**
     * Tính tiền điện theo biểu giá bậc thang lũy tiến
     */
    public static function calculateElectricityBill(int $kwh): float {
        $total = 0;
        $remaining = $kwh;
        $prevLimit = 0;

        foreach (self::TIER_DIEN as $tier) {
            $tierCapacity = $tier['limit'] - $prevLimit;
            if ($remaining > $tierCapacity) {
                $total += $tierCapacity * $tier['price'];
                $remaining -= $tierCapacity;
                $prevLimit = $tier['limit'];
            } else {
                $total += $remaining * $tier['price'];
                break;
            }
        }

        return $total;
    }

    /**
     * Tính hóa đơn tổng hợp cho cả phòng trong tháng
     */
    public static function calculateMonthlyBill(string $maPhong, int $thang, int $nam, int $dienCu, int $dienMoi, int $nuocCu, int $nuocMoi, int $soSV): array {
        $check = self::validateUtilityInput($maPhong, $dienCu, $dienMoi, $nuocCu, $nuocMoi);
        $tienDien = self::calculateElectricityBill($check['dienTieuThu']);
        $tienNuoc = $check['nuocTieuThu'] * self::DON_GIA_NUOC;
        $tongTien = $tienDien + $tienNuoc;
        $tienMoiSV = $soSV > 0 ? round($tongTien / $soSV) : 0;

        return [
            'maPhong' => $maPhong,
            'thang' => $thang,
            'nam' => $nam,
            'dienTieuThu' => $check['dienTieuThu'],
            'nuocTieuThu' => $check['nuocTieuThu'],
            'tienDien' => $tienDien,
            'tienNuoc' => $tienNuoc,
            'tongTien' => $tongTien,
            'soSV' => $soSV,
            'tienMoiSV' => $tienMoiSV,
            'canhBaoBatThuong' => $check['isAnomaly'],
            'chiTietCanhBao' => $check['warnings']
        ];
    }
}
