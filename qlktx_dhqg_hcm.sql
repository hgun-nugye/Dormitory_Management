-- ==============================================================================
-- KỊCH BẢN CSDL & DỮ LIỆU MẪU THỰC TẾ (SEED DATA)
-- CƠ SỞ DỮ LIỆU QUẢN LÝ KÝ TÚC XÁ ĐẠI HỌC QUỐC GIA THÀNH PHỐ HỒ CHÍ MINH
-- Hệ thống KTX ĐHQG-HCM (Khu A & Khu B)
-- Engine: InnoDB | Charset: utf8mb4 | Collation: utf8mb4_unicode_ci
-- Phiên bản: v2.0 (Bổ sung bộ dữ liệu mẫu đầy đủ & thực tế)
-- ==============================================================================

CREATE DATABASE IF NOT EXISTS `qlktx_dhqg_hcm` 
    DEFAULT CHARACTER SET utf8mb4 
    DEFAULT COLLATE utf8mb4_unicode_ci;

USE `qlktx_dhqg_hcm`;

-- Tắt kiểm tra khóa ngoại tạm thời để xóa và khởi tạo lại các bảng
SET FOREIGN_KEY_CHECKS = 0;

-- ==============================================================================
-- PART 1: KHỞI TẠO CẤU TRÚC BẢNG (SCHEMA)
-- ==============================================================================

DROP TABLE IF EXISTS `SV_THAMGIAM_HOATDONG`;
DROP TABLE IF EXISTS `HOATDONG_RENLUYEN`;
DROP TABLE IF EXISTS `THANH_TOAN`;
DROP TABLE IF EXISTS `HOADON`;
DROP TABLE IF EXISTS `NV_BAOVE`;
DROP TABLE IF EXISTS `NV_HANHCHINH`;
DROP TABLE IF EXISTS `NHANVIEN`;
DROP TABLE IF EXISTS `DANGKY_HOPDONG`;
DROP TABLE IF EXISTS `DIEN_MIEN_GIAM`;
DROP TABLE IF EXISTS `THANNHAN`;
DROP TABLE IF EXISTS `SV_TAMTRU`;
DROP TABLE IF EXISTS `SV_NOITRU`;
DROP TABLE IF EXISTS `SINHVIEN`;
DROP TABLE IF EXISTS `PHONG_O`;
DROP TABLE IF EXISTS `BAN_QUAN_LY_CUM_NHA`;
DROP TABLE IF EXISTS `CHUCVU`;
DROP TABLE IF EXISTS `TRUONG`;

-- 1. BẢNG TRUONG
CREATE TABLE `TRUONG` (
    `MSTRG` VARCHAR(10) NOT NULL COMMENT 'Mã trường (PK)',
    `TENTRUONG` VARCHAR(150) NOT NULL COMMENT 'Tên trường đại học',
    `DIACHI` VARCHAR(255) DEFAULT NULL COMMENT 'Địa chỉ cơ sở chính/phân hiệu',
    `SDT` VARCHAR(15) DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
    `EMAIL` VARCHAR(100) DEFAULT NULL COMMENT 'Email công tác sinh viên',
    PRIMARY KEY (`MSTRG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục các Trường Đại học';

-- 2. BẢNG CHUCVU
CREATE TABLE `CHUCVU` (
    `MACV` VARCHAR(10) NOT NULL COMMENT 'Mã chức vụ (PK)',
    `TENCV` VARCHAR(100) NOT NULL COMMENT 'Tên chức vụ',
    `DIEN_GIAI` VARCHAR(255) DEFAULT NULL COMMENT 'Mô tả nhiệm vụ chức vụ',
    PRIMARY KEY (`MACV`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục Chức vụ';

-- 3. BẢNG BAN_QUAN_LY_CUM_NHA
CREATE TABLE `BAN_QUAN_LY_CUM_NHA` (
    `MACUM` VARCHAR(10) NOT NULL COMMENT 'Mã cụm nhà (PK)',
    `TENCUM` VARCHAR(100) NOT NULL COMMENT 'Tên Ban quản lý Cụm nhà',
    `KHU_VUC` ENUM('Khu A', 'Khu B') NOT NULL COMMENT 'Phân khu A hoặc Khu B',
    `DIACHI_VANPHONG` VARCHAR(255) DEFAULT NULL COMMENT 'Địa chỉ văn phòng BQL',
    `SDT_NOIBO` VARCHAR(50) DEFAULT NULL COMMENT 'Số nội bộ tổng đài 1900.05.55.59',
    `EMAIL_CUM` VARCHAR(100) DEFAULT NULL COMMENT 'Email liên hệ cụm nhà',
    PRIMARY KEY (`MACUM`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý Cụm nhà KTX';

-- 4. BẢNG PHONG_O
CREATE TABLE `PHONG_O` (
    `MAPHG` VARCHAR(15) NOT NULL COMMENT 'Mã phòng ở (PK)',
    `TENPHG` VARCHAR(50) NOT NULL COMMENT 'Tên phòng',
    `MACUM` VARCHAR(10) NOT NULL COMMENT 'Mã cụm nhà quản lý (FK)',
    `TOA_NHA` VARCHAR(20) NOT NULL COMMENT 'Tòa nhà (VD: A01, H01, BA1...)',
    `TANG_SO` INT NOT NULL COMMENT 'Số tầng',
    `LOAI_PHONG` VARCHAR(50) DEFAULT 'Tiêu chuẩn' COMMENT 'Loại phòng (2, 4, 6, 8 SV...)',
    `SUC_CHUA` INT NOT NULL DEFAULT 4 COMMENT 'Sức chứa thiết kế',
    `SO_SV_HIENTAI` INT NOT NULL DEFAULT 0 COMMENT 'Số sinh viên đang ở',
    `DONGIA_PHONG` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Đơn giá thuê phòng/tháng',
    `TRPHG_MSSV` VARCHAR(15) DEFAULT NULL COMMENT 'MSSV Trưởng phòng',
    PRIMARY KEY (`MAPHG`),
    CONSTRAINT `FK_PHONGO_CUM` FOREIGN KEY (`MACUM`) REFERENCES `BAN_QUAN_LY_CUM_NHA` (`MACUM`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Danh mục Phòng ở Sinh viên';

-- 5. BẢNG SINHVIEN
CREATE TABLE `SINHVIEN` (
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (PK)',
    `HOTENSV` VARCHAR(100) NOT NULL COMMENT 'Họ và tên sinh viên',
    `GIOITINH` ENUM('Nam', 'Nữ', 'Khác') NOT NULL COMMENT 'Giới tính',
    `NGAYSINH` DATE NOT NULL COMMENT 'Ngày tháng năm sinh',
    `NOISINH` VARCHAR(100) DEFAULT NULL COMMENT 'Nơi sinh / Tỉnh thành',
    `DIACHI_THUONGTRU` VARCHAR(255) NOT NULL COMMENT 'Địa chỉ thường trú',
    `SDT` VARCHAR(15) NOT NULL COMMENT 'Số điện thoại cá nhân',
    `EMAIL` VARCHAR(100) NOT NULL COMMENT 'Email sinh viên',
    `CCCD` VARCHAR(20) NOT NULL UNIQUE COMMENT 'Số Căn cước công dân',
    `BHYT_VSSID` VARCHAR(20) DEFAULT NULL COMMENT 'Mã thẻ BHYT / VssID',
    `ANH_FACEID` VARCHAR(255) DEFAULT NULL COMMENT 'Đường dẫn ảnh thẻ FaceID',
    `MSTRG` VARCHAR(10) NOT NULL COMMENT 'Mã trường đại học đang học (FK)',
    `MAPHG` VARCHAR(15) DEFAULT NULL COMMENT 'Mã phòng đang ở (FK)',
    `MACV` VARCHAR(10) DEFAULT NULL COMMENT 'Chức vụ trong KTX (FK)',
    `TRANG_THAI_LUU_TRU` ENUM('Chờ duyệt', 'Nội trú', 'Tạm trú', 'Đã trả phòng') DEFAULT 'Chờ duyệt',
    PRIMARY KEY (`MSSV`),
    CONSTRAINT `FK_SV_TRUONG` FOREIGN KEY (`MSTRG`) REFERENCES `TRUONG` (`MSTRG`) ON UPDATE CASCADE,
    CONSTRAINT `FK_SV_PHONG` FOREIGN KEY (`MAPHG`) REFERENCES `PHONG_O` (`MAPHG`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_SV_CHUCVU` FOREIGN KEY (`MACV`) REFERENCES `CHUCVU` (`MACV`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Quản lý Sinh viên KTX';

ALTER TABLE `PHONG_O` 
    ADD CONSTRAINT `FK_PHONGO_TRPHG` FOREIGN KEY (`TRPHG_MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE SET NULL ON UPDATE CASCADE;

-- 6. BẢNG SV_NOITRU
CREATE TABLE `SV_NOITRU` (
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (PK, FK)',
    `THE_NOI_TRU_SO` VARCHAR(20) DEFAULT NULL UNIQUE COMMENT 'Số Thẻ nội trú do BQL cấp',
    `NGAY_CAP_THE` DATE DEFAULT NULL COMMENT 'Ngày cấp thẻ nội trú',
    `NGAY_BAT_DAU_NOI_TRU` DATE NOT NULL COMMENT 'Ngày chính thức bắt đầu nội trú',
    PRIMARY KEY (`MSSV`),
    CONSTRAINT `FK_SVNOITRU_SV` FOREIGN KEY (`MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sinh viên Nội trú';

-- 7. BẢNG SV_TAMTRU
CREATE TABLE `SV_TAMTRU` (
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (PK, FK)',
    `LY_DO_TAM_TRU` VARCHAR(255) NOT NULL COMMENT 'Lý do ở tạm trú',
    `NGAY_BAT_DAU` DATE NOT NULL COMMENT 'Ngày bắt đầu tạm trú',
    `NGAY_KET_THUC` DATE NOT NULL COMMENT 'Ngày kết thúc tạm trú',
    PRIMARY KEY (`MSSV`),
    CONSTRAINT `FK_SVTAMTRU_SV` FOREIGN KEY (`MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Sinh viên Tạm trú';

-- 8. BẢNG THANNHAN
CREATE TABLE `THANNHAN` (
    `ID_THANNHAN` INT AUTO_INCREMENT COMMENT 'Mã định danh thân nhân (PK)',
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (FK)',
    `TENTN` VARCHAR(100) NOT NULL COMMENT 'Tên thân nhân',
    `QUANHE` VARCHAR(50) NOT NULL COMMENT 'Mối quan hệ',
    `SDT_LIENHE` VARCHAR(15) NOT NULL COMMENT 'Số điện thoại liên hệ khẩn cấp',
    `DIACHI` VARCHAR(255) DEFAULT NULL COMMENT 'Địa chỉ thân nhân',
    PRIMARY KEY (`ID_THANNHAN`),
    CONSTRAINT `FK_THANNHAN_SV` FOREIGN KEY (`MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Thân nhân Sinh viên';

-- 9. BẢNG DIEN_MIEN_GIAM
CREATE TABLE `DIEN_MIEN_GIAM` (
    `MADIEN` VARCHAR(10) NOT NULL COMMENT 'Mã diện miễn giảm (PK)',
    `TEN_DIEN` VARCHAR(150) NOT NULL COMMENT 'Tên diện ưu đãi',
    `TYLE_MIEN_GIAM` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT 'Tỷ lệ miễn giảm (%)',
    `GIAY_TO_MINH_CHUNG` VARCHAR(255) DEFAULT NULL COMMENT 'Yêu cầu giấy tờ minh chứng',
    PRIMARY KEY (`MADIEN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Chính sách Miễn giảm Lệ phí';

-- 10. BẢNG DANGKY_HOPDONG
CREATE TABLE `DANGKY_HOPDONG` (
    `MSHDG` VARCHAR(20) NOT NULL COMMENT 'Mã số hợp đồng (PK)',
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (FK)',
    `MAPHG` VARCHAR(15) NOT NULL COMMENT 'Mã phòng ở đăng ký (FK)',
    `TGBD` DATE NOT NULL COMMENT 'Thời gian bắt đầu hợp đồng',
    `TGKT` DATE NOT NULL COMMENT 'Thời gian kết thúc hợp đồng',
    `NGAY_NHAN_PHONG_DU_KIEN` DATE DEFAULT NULL COMMENT 'Ngày nhận phòng dự kiến',
    `MADIEN_MIEN_GIAM` VARCHAR(10) DEFAULT NULL COMMENT 'Diện miễn giảm áp dụng (FK)',
    `TIEN_THE_CHAN` DECIMAL(12,2) DEFAULT 0.00 COMMENT 'Tiền thế chân tài sản',
    `TRANG_THAI_HOPDONG` ENUM('Chờ duyệt thanh toán', 'Hiệu lực', 'Thanh lý', 'Đã hủy') DEFAULT 'Chờ duyệt thanh toán',
    `MA_XAC_THUC_OTP` VARCHAR(10) DEFAULT NULL COMMENT 'Mã xác thực OTP',
    PRIMARY KEY (`MSHDG`),
    CONSTRAINT `FK_HOPDONG_SV` FOREIGN KEY (`MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_HOPDONG_PHONG` FOREIGN KEY (`MAPHG`) REFERENCES `PHONG_O` (`MAPHG`) ON UPDATE CASCADE,
    CONSTRAINT `FK_HOPDONG_MIENGIAM` FOREIGN KEY (`MADIEN_MIEN_GIAM`) REFERENCES `DIEN_MIEN_GIAM` (`MADIEN`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hợp đồng thuê chỗ ở KTX';

-- 11. BẢNG NHANVIEN
CREATE TABLE `NHANVIEN` (
    `MANV` VARCHAR(15) NOT NULL COMMENT 'Mã số nhân viên (PK)',
    `HOTENNV` VARCHAR(100) NOT NULL COMMENT 'Họ và tên nhân viên',
    `GIOITINH` ENUM('Nam', 'Nữ') NOT NULL COMMENT 'Giới tính',
    `SDT` VARCHAR(15) NOT NULL COMMENT 'Số điện thoại cá nhân',
    `EMAIL` VARCHAR(100) DEFAULT NULL COMMENT 'Email công vụ',
    `DIACHI` VARCHAR(255) DEFAULT NULL COMMENT 'Địa chỉ cư trú',
    `MACV` VARCHAR(10) DEFAULT NULL COMMENT 'Mã chức vụ (FK)',
    PRIMARY KEY (`MANV`),
    CONSTRAINT `FK_NV_CHUCVU` FOREIGN KEY (`MACV`) REFERENCES `CHUCVU` (`MACV`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhân viên BQL KTX';

-- 12. BẢNG NV_HANHCHINH
CREATE TABLE `NV_HANHCHINH` (
    `MANV` VARCHAR(15) NOT NULL COMMENT 'Mã số nhân viên (PK, FK)',
    `PHONG_BAN` VARCHAR(100) NOT NULL COMMENT 'Phòng chuyên môn',
    `MPHC` VARCHAR(10) DEFAULT NULL COMMENT 'Mã phòng làm việc',
    PRIMARY KEY (`MANV`),
    CONSTRAINT `FK_NVHC_NV` FOREIGN KEY (`MANV`) REFERENCES `NHANVIEN` (`MANV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhân viên Hành chính';

-- 13. BẢNG NV_BAOVE
CREATE TABLE `NV_BAOVE` (
    `MANV` VARCHAR(15) NOT NULL COMMENT 'Mã số nhân viên (PK, FK)',
    `CA_TRUC` VARCHAR(50) DEFAULT NULL COMMENT 'Ca trực phụ trách',
    `KHU_VUC_PHU_TRACH` VARCHAR(100) DEFAULT NULL COMMENT 'Khu vực phụ trách an ninh',
    PRIMARY KEY (`MANV`),
    CONSTRAINT `FK_NVBV_NV` FOREIGN KEY (`MANV`) REFERENCES `NHANVIEN` (`MANV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhân viên Bảo vệ';

-- 14. BẢNG HOADON
CREATE TABLE `HOADON` (
    `MAHD` VARCHAR(20) NOT NULL COMMENT 'Mã số hóa đơn (PK)',
    `MAPHG` VARCHAR(15) NOT NULL COMMENT 'Mã phòng ở (FK)',
    `THANG` INT NOT NULL COMMENT 'Tháng tính tiền',
    `NAM` INT NOT NULL COMMENT 'Năm tính tiền',
    `CHISO_DIEN_DAU` INT NOT NULL DEFAULT 0,
    `CHISO_DIEN_CUOI` INT NOT NULL DEFAULT 0,
    `CHISO_NUOC_DAU` INT NOT NULL DEFAULT 0,
    `CHISO_NUOC_CUOI` INT NOT NULL DEFAULT 0,
    `DINH_MUC_DIEN` INT DEFAULT 0,
    `DINH_MUC_NUOC` INT DEFAULT 0,
    `DON_GIA_DIEN` DECIMAL(10,2) NOT NULL,
    `DON_GIA_NUOC` DECIMAL(10,2) NOT NULL,
    `TTIEN_DIEN` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `TTIEN_NUOC` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `TTIEN` DECIMAL(12,2) NOT NULL COMMENT 'Tổng số tiền cần thanh toán',
    `NGAY_LAP_PHIEU` DATE NOT NULL,
    `MANV_LAP` VARCHAR(15) DEFAULT NULL COMMENT 'Mã nhân viên lập (FK)',
    PRIMARY KEY (`MAHD`),
    CONSTRAINT `FK_HOADON_PHONG` FOREIGN KEY (`MAPHG`) REFERENCES `PHONG_O` (`MAPHG`) ON UPDATE CASCADE,
    CONSTRAINT `FK_HOADON_NV` FOREIGN KEY (`MANV_LAP`) REFERENCES `NHANVIEN` (`MANV`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hóa đơn Điện Nước Dịch vụ';

-- 15. BẢNG THANH_TOAN
CREATE TABLE `THANH_TOAN` (
    `MATT` VARCHAR(25) NOT NULL COMMENT 'Mã giao dịch thanh toán (PK)',
    `MAHD` VARCHAR(20) DEFAULT NULL COMMENT 'Mã hóa đơn điện nước (FK)',
    `MSHDG` VARCHAR(20) DEFAULT NULL COMMENT 'Mã hợp đồng thuê phòng (FK)',
    `PHUONG_THUC_TT` ENUM('Mã QR KTX', 'BIDV App', 'Vietcombank', 'VNPAY Gateway', 'Chuyển khoản định danh CCCD', 'Tiền mặt') NOT NULL,
    `MA_GIAO_DICH_BANK` VARCHAR(50) DEFAULT NULL COMMENT 'Mã tham chiếu ngân hàng',
    `NGAY_THANH_TOAN` DATETIME NOT NULL,
    `SO_TIEN_TT` DECIMAL(12,2) NOT NULL,
    `TRANG_THAI_TT` ENUM('Thành công', 'Đang xử lý', 'Thất bại') DEFAULT 'Thành công',
    PRIMARY KEY (`MATT`),
    CONSTRAINT `FK_THANHTOAN_HOADON` FOREIGN KEY (`MAHD`) REFERENCES `HOADON` (`MAHD`) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT `FK_THANHTOAN_HOPDONG` FOREIGN KEY (`MSHDG`) REFERENCES `DANGKY_HOPDONG` (`MSHDG`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Nhật ký Thanh toán Trực tuyến';

-- 16. BẢNG HOATDONG_RENLUYEN
CREATE TABLE `HOATDONG_RENLUYEN` (
    `MHDONG` VARCHAR(15) NOT NULL COMMENT 'Mã hoạt động (PK)',
    `TENHD` VARCHAR(150) NOT NULL COMMENT 'Tên hoạt động',
    `TGTCHUC` DATETIME NOT NULL COMMENT 'Thời gian tổ chức',
    `DIADIEM` VARCHAR(150) DEFAULT NULL,
    `DIEM_REN_LUYEN` INT DEFAULT 0,
    PRIMARY KEY (`MHDONG`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Hoạt động Sinh hoạt KTX';

-- 17. BẢNG SV_THAMGIAM_HOATDONG
CREATE TABLE `SV_THAMGIAM_HOATDONG` (
    `MHDONG` VARCHAR(15) NOT NULL COMMENT 'Mã hoạt động (PK, FK)',
    `MSSV` VARCHAR(15) NOT NULL COMMENT 'Mã số sinh viên (PK, FK)',
    `DIEM_DAT_DUOC` INT DEFAULT 0,
    `GHICHU` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`MHDONG`, `MSSV`),
    CONSTRAINT `FK_SVHD_HOATDONG` FOREIGN KEY (`MHDONG`) REFERENCES `HOATDONG_RENLUYEN` (`MHDONG`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `FK_SVHD_SV` FOREIGN KEY (`MSSV`) REFERENCES `SINHVIEN` (`MSSV`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Đánh giá Rèn luyện Sinh viên';

-- Mở lại kiểm tra khóa ngoại
SET FOREIGN_KEY_CHECKS = 1;


-- ==============================================================================
-- PART 2: DỮ LIỆU MẪU BÁM SÁT THỰC TẾ (REALISTIC SEED DATA)
-- ==============================================================================

-- 1. BẢNG CHUCVU (Chức vụ BQL & Ban Tự quản Sinh viên)
INSERT INTO `CHUCVU` (`MACV`, `TENCV`, `DIEN_GIAI`) VALUES
('CV01', 'Trưởng BQL Cụm nhà', 'Điều hành toàn bộ hoạt động hành chính, an ninh cụm nhà'),
('CV02', 'Cán bộ Hành chính - Dịch vụ', 'Tiếp nhận hồ sơ đăng ký, gia hạn, xác nhận sinh viên'),
('CV03', 'Cán bộ An ninh - PCCC', 'Chịu trách nhiệm công tác an ninh trật tự, phòng cháy chữa cháy'),
('CV04', 'Nhân viên Bảo vệ', 'Trực cổng gác, kiểm soát ra vào bằng thẻ/FaceID, tuần tra đêm'),
('CV05', 'Cán bộ Kế toán - Tài chính', 'Quản lý thu lệ phí chỗ ở, hóa đơn điện nước và quyết toán'),
('CV06', 'Trưởng phòng Sinh viên', 'Đại diện sinh viên trong phòng, quản lý vệ sinh và tài sản'),
('CV07', 'Phó phòng Sinh viên', 'Hỗ trợ trưởng phòng điều hành sinh hoạt nội bộ phòng ở');

-- 2. BẢNG TRUONG (8 Trường đại học thành viên / trực thuộc ĐHQG-HCM)
INSERT INTO `TRUONG` (`MSTRG`, `TENTRUONG`, `DIACHI`, `SDT`, `EMAIL`) VALUES
('BK', 'Trường Đại học Bách khoa ĐHQG-HCM', '268 Lý Thường Kiệt, P.14, Q.10 / Khu Đô thị ĐHQG, TP. Thủ Đức', '02838654087', 'ctsv@hcmut.edu.vn'),
('KTN', 'Trường Đại học Khoa học Tự nhiên ĐHQG-HCM', '227 Nguyễn Văn Cừ, Q.5 / Linh Trung, TP. Thủ Đức', '02838353193', 'p_ctsv@hcmus.edu.vn'),
('NV', 'Trường Đại học Khoa học Xã hội và Nhân văn ĐHQG-HCM', '10-12 Đinh Tiên Hoàng, Q.1 / Linh Trung, TP. Thủ Đức', '02838293828', 'ctsv@hcmussh.edu.vn'),
('IT', 'Trường Đại học Công nghệ Thông tin ĐHQG-HCM', 'Khu đô thị ĐHQG-HCM, P. Linh Trung, TP. Thủ Đức', '02837252002', 'ctsv@uit.edu.vn'),
('UEL', 'Trường Đại học Kinh tế - Luật ĐHQG-HCM', '669 Quốc Lộ 1K, P. Linh Xuân, TP. Thủ Đức, TP.HCM', '02837244555', 'ctsv@uel.edu.vn'),
('QT', 'Trường Đại học Quốc Tế ĐHQG-HCM', 'Khu đô thị ĐHQG-HCM, P. Linh Trung, TP. Thủ Đức', '02837244270', 'osa@hcmiu.edu.vn'),
('Y', 'Trường Đại học Khoa học Sức khỏe ĐHQG-HCM', 'Tòa nhà YAP, Khu đô thị ĐHQG-HCM, TP. Thủ Đức', '02837242160', 'ctsv@medvnu.edu.vn'),
('AGU', 'Trường Đại học An Giang ĐHQG-HCM', '18 Ung Văn Khiêm, P. Đông Xuyên, TP. Long Xuyên, An Giang', '02966256565', 'ctsv@agu.edu.vn');

-- 3. BẢNG BAN_QUAN_LY_CUM_NHA (Các Cụm nhà thuộc Khu A & Khu B)
INSERT INTO `BAN_QUAN_LY_CUM_NHA` (`MACUM`, `TENCUM`, `KHU_VUC`, `DIACHI_VANPHONG`, `SDT_NOIBO`, `EMAIL_CUM`) VALUES
('A.G', 'Ban quản lý Cụm nhà A.G', 'Khu A', 'Tầng trệt Tòa nhà A01, Khu A KTX ĐHQG', '1900.05.55.59 - 121', 'bql-ag@ktxhcm.edu.vn'),
('A.H', 'Ban quản lý Cụm nhà A.H', 'Khu A', 'Tầng trệt Tòa nhà H01-H02, Khu A KTX ĐHQG', '1900.05.55.59 - 122', 'bql-ah@ktxhcm.edu.vn'),
('A.E', 'Ban quản lý Cụm nhà A.E', 'Khu A', 'Tầng trệt Tòa nhà A19-A20, Khu A KTX ĐHQG', '1900.05.55.59 - 128', 'bql-ae@ktxhcm.edu.vn'),
('B.A', 'Ban quản lý Cụm nhà B.A', 'Khu B', 'Tầng trệt Tòa nhà BA1, Khu B KTX ĐHQG', '1900.05.55.59 - 123', 'bql-ba@ktxhcm.edu.vn'),
('B.B', 'Ban quản lý Cụm nhà B.B', 'Khu B', 'Tầng trệt Tòa nhà B02, Khu B KTX ĐHQG', '1900.05.55.59 - 124', 'bql-bb@ktxhcm.edu.vn'),
('B.C', 'Ban quản lý Cụm nhà B.C', 'Khu B', 'Tầng trệt Tòa nhà C06, Khu B KTX ĐHQG', '1900.05.55.59 - 125', 'bql-bc@ktxhcm.edu.vn'),
('B.D', 'Ban quản lý Cụm nhà B.D', 'Khu B', 'Tầng trệt Tòa nhà D03, Khu B KTX ĐHQG', '1900.05.55.59 - 126', 'bql-bd@ktxhcm.edu.vn'),
('B.E', 'Ban quản lý Cụm nhà B.E', 'Khu B', 'Tầng trệt Tòa nhà E04, Khu B KTX ĐHQG', '1900.05.55.59 - 127', 'bql-be@ktxhcm.edu.vn');

-- 4. BẢNG DIEN_MIEN_GIAM (Các chính sách miễn giảm lệ phí)
INSERT INTO `DIEN_MIEN_GIAM` (`MADIEN`, `TEN_DIEN`, `TYLE_MIEN_GIAM`, `GIAY_TO_MINH_CHUNG`) VALUES
('MG00', 'Không thuộc diện miễn giảm', 0.00, NULL),
('MG100', 'Sinh viên Khuyết tật đặc biệt / Mồ côi cả cha lẫn mẹ', 100.00, 'Giấy xác nhận tàn tật do UBND cấp / Giấy chứng tử cha mẹ'),
('MG50', 'Sinh viên Hộ nghèo / Hộ cận nghèo quốc gia', 50.00, 'Sổ hộ nghèo / Hộ cận nghèo năm hiện hành có công chứng'),
('MG30', 'Sinh viên Con thương binh, bệnh binh / Vùng đặc biệt khó khăn', 30.00, 'Thẻ thương binh cha/mẹ / Hộ khẩu thuộc xã Khu vực III');

-- 5. BẢNG PHONG_O (Đa dạng phòng ở từ Khu A đến Khu B với các mức giá)
INSERT INTO `PHONG_O` (`MAPHG`, `TENPHG`, `MACUM`, `TOA_NHA`, `TANG_SO`, `LOAI_PHONG`, `SUC_CHUA`, `SO_SV_HIENTAI`, `DONGIA_PHONG`, `TRPHG_MSSV`) VALUES
('A01.101', 'Phòng A01-101', 'A.G', 'A01', 1, '4 SV / Thường', 4, 3, 650000.00, NULL),
('A01.102', 'Phòng A01-102', 'A.G', 'A01', 1, '4 SV / Thường', 4, 2, 650000.00, NULL),
('A02.201', 'Phòng A02-201', 'A.G', 'A02', 2, '6 SV / Thường', 6, 4, 350000.00, NULL),
('H01.301', 'Phòng H01-301', 'A.H', 'H01', 3, '4 SV / Máy lạnh', 4, 4, 950000.00, NULL),
('H02.402', 'Phòng H02-402', 'A.H', 'H02', 4, '2 SV / Dịch vụ cao cấp', 2, 2, 1500000.00, NULL),
('BA1.101', 'Phòng BA1-101', 'B.A', 'BA1', 1, '4 SV / Dịch vụ', 4, 3, 950000.00, NULL),
('BA1.202', 'Phòng BA1-202', 'B.A', 'BA1', 2, '4 SV / Dịch vụ', 4, 3, 950000.00, NULL),
('B02.105', 'Phòng B02-105', 'B.B', 'B02', 1, '8 SV / Tiêu chuẩn', 8, 5, 220000.00, NULL),
('C06.305', 'Phòng C06-305', 'B.C', 'C06', 3, '8 SV / Tiêu chuẩn', 8, 4, 220000.00, NULL),
('D03.201', 'Phòng D03-201', 'B.D', 'D03', 2, '4 SV / Máy lạnh', 4, 2, 950000.00, NULL),
('E04.401', 'Phòng E04-401', 'B.E', 'E04', 4, '6 SV / Thường', 6, 3, 350000.00, NULL);

-- 6. BẢNG NHANVIEN (Cán bộ Ban Quản lý, Hành chính, An ninh, Bảo vệ)
INSERT INTO `NHANVIEN` (`MANV`, `HOTENNV`, `GIOITINH`, `SDT`, `EMAIL`, `DIACHI`, `MACV`) VALUES
('NV001', 'Trần Văn Minh', 'Nam', '0903123456', 'tvminh@ktxhcm.edu.vn', 'Số 1 Đường Bác Hòa, TP. Thủ Đức, TP.HCM', 'CV01'),
('NV002', 'Nguyễn Thị Thu Hương', 'Nữ', '0914234567', 'ntthuong@ktxhcm.edu.vn', 'Khu phố 6, P. Linh Trung, TP. Thủ Đức', 'CV02'),
('NV003', 'Lê Hoàng Nam', 'Nam', '0985345678', 'lhnam@ktxhcm.edu.vn', 'Đường Tân Lập, Dĩ An, Bình Dương', 'CV03'),
('NV004', 'Phạm Quốc Hùng', 'Nam', '0976456789', 'pqhung@ktxhcm.edu.vn', 'Phường Đông Hòa, Dĩ An, Bình Dương', 'CV04'),
('NV005', 'Ngô Thanh Sơn', 'Nam', '0937567890', 'ntson@ktxhcm.edu.vn', 'Khu phố 3, P. Linh Xuân, TP. Thủ Đức', 'CV04'),
('NV006', 'Vũ Thị Ngọc Ánh', 'Nữ', '0908678901', 'vtnanh@ktxhcm.edu.vn', '120 Võ Văn Ngân, TP. Thủ Đức, TP.HCM', 'CV05');

-- 7. BẢNG NV_HANHCHINH & NV_BAOVE
INSERT INTO `NV_HANHCHINH` (`MANV`, `PHONG_BAN`, `MPHC`) VALUES
('NV001', 'Ban Giám đốc / BQL Cụm A.G', 'HC-A01'),
('NV002', 'Phòng Công tác Sinh viên (CTSV)', 'HC-BA1'),
('NV003', 'Phòng An ninh - Quản lý Rủi ro', 'HC-AN01'),
('NV006', 'Phòng Kế hoạch - Tài chính', 'HC-KT02');

INSERT INTO `NV_BAOVE` (`MANV`, `CA_TRUC`, `KHU_VUC_PHU_TRACH`) VALUES
('NV004', 'Ca 1 (06:00 - 14:00)', 'Cổng chính Khu A & Cụm A.G'),
('NV005', 'Ca 3 (22:00 - 06:00)', 'Tuần tra an ninh Cụm B.A - B.B Khu B');

-- 8. BẢNG SINHVIEN (20 sinh viên đại diện các trường thành viên)
INSERT INTO `SINHVIEN` (`MSSV`, `HOTENSV`, `GIOITINH`, `NGAYSINH`, `NOISINH`, `DIACHI_THUONGTRU`, `SDT`, `EMAIL`, `CCCD`, `BHYT_VSSID`, `ANH_FACEID`, `MSTRG`, `MAPHG`, `MACV`, `TRANG_THAI_LUU_TRU`) VALUES
('2110001', 'Nguyễn Văn An', 'Nam', '2003-05-15', 'TP. Hồ Chí Minh', '123 Tạ Quang Bửu, P. Linh Trung, TP. Thủ Đức', '0901234567', 'an.nguyen2110001@sis.hcmut.edu.vn', '079203001234', 'DN4791234567890', '/uploads/faceid/2110001.jpg', 'BK', 'A01.101', 'CV06', 'Nội trú'),
('2110002', 'Lê Quốc Bảo', 'Nam', '2003-08-20', 'Đồng Nai', '45 Nguyễn Ai Quốc, TP. Biên Hòa, Đồng Nai', '0912345678', 'bao.le2110002@sis.hcmut.edu.vn', '075203002345', 'DN4752345678901', '/uploads/faceid/2110002.jpg', 'BK', 'A01.101', NULL, 'Nội trú'),
('2212345', 'Trần Thị Mai Chúc', 'Nữ', '2004-02-10', 'Bình Dương', '88 Đại lộ Bình Dương, TP. Thủ Dầu Một', '0923456789', 'ttmchuc@student.hcmus.edu.vn', '074204003456', 'HS4743456789012', '/uploads/faceid/2212345.jpg', 'KTN', 'A01.102', 'CV06', 'Nội trú'),
('2212346', 'Phạm Hoàng Dũng', 'Nam', '2004-11-05', 'Long An', '12 Quốc Lộ 1A, TP. Tân An, Long An', '0934567890', 'phdung@student.hcmus.edu.vn', '080204004567', 'DN4804567890123', '/uploads/faceid/2212346.jpg', 'KTN', 'A01.101', NULL, 'Nội trú'),
('2352011', 'Đỗ Thanh Giang', 'Nam', '2005-01-25', 'Tiền Giang', '56 Lý Thường Kiệt, TP. Mỹ Tho, Tiền Giang', '0945678901', '2352011@gm.uit.edu.vn', '082205005678', 'HS4825678901234', '/uploads/faceid/2352011.jpg', 'IT', 'BA1.101', 'CV06', 'Nội trú'),
('2352012', 'Nguyễn Hà Hoàng', 'Nam', '2005-06-18', 'Bến Tre', '102 Đồng Khởi, TP. Bến Tre, Bến Tre', '0956789012', '2352012@gm.uit.edu.vn', '083205006789', 'DN4836789012345', '/uploads/faceid/2352012.jpg', 'IT', 'BA1.101', NULL, 'Nội trú'),
('2411890', 'Bùi Minh Trí', 'Nam', '2006-03-30', 'Đà Nẵng', '234 Hùng Vương, Q. Hải Châu, Đà Nẵng', '0967890123', '2411890@st.uel.edu.vn', '048206007890', 'HS4487890123456', '/uploads/faceid/2411890.jpg', 'UEL', 'BA1.101', NULL, 'Nội trú'),
('2411891', 'Đặng Ngọc Khánh', 'Nữ', '2006-09-12', 'Lâm Đồng', '78 Trần Phú, TP. Đà Lạt, Lâm Đồng', '0978901234', '2411891@st.uel.edu.vn', '068206008901', 'HS4688901234567', '/uploads/faceid/2411891.jpg', 'UEL', 'BA1.202', 'CV06', 'Nội trú'),
('2156001', 'Hoàng Văn Long', 'Nam', '2003-12-01', 'Thừa Thiên Huế', '15 Lê Lợi, TP. Huế, Thừa Thiên Huế', '0989012345', '2156001@hcmussh.edu.vn', '046203009012', 'DN4469012345678', '/uploads/faceid/2156001.jpg', 'NV', 'H01.301', 'CV06', 'Nội trú'),
('2156002', 'Vũ Tuyết Nhi', 'Nữ', '2003-04-22', 'Khánh Hòa', '89 Trần Phú, TP. Nha Trang, Khánh Hòa', '0990123456', '2156002@hcmussh.edu.vn', '056203000123', 'DN4560123456789', '/uploads/faceid/2156002.jpg', 'NV', 'BA1.202', NULL, 'Nội trú'),
('2250101', 'Ngô Quốc Phương', 'Nam', '2004-07-14', 'Cần Thơ', '45 3 Tháng 2, Q. Ninh Kiều, Cần Thơ', '0902345678', '2250101@hcmiu.edu.vn', '092204001234', 'HS4921234567890', '/uploads/faceid/2250101.jpg', 'QT', 'H01.301', NULL, 'Nội trú'),
('2250102', 'Trịnh Thu Thảo', 'Nữ', '2004-10-28', 'Quảng Nam', '12 Nguyễn Tất Thành, TP. Hội An', '0913456789', '2250102@hcmiu.edu.vn', '049204002345', 'HS4492345678901', '/uploads/faceid/2250102.jpg', 'QT', 'H02.402', 'CV06', 'Nội trú'),
('2351101', 'Nguyễn Đăng Khoa', 'Nam', '2005-02-14', 'An Giang', '234 Tôn Đức Thắng, TP. Long Xuyên', '0924567890', '2351101@medvnu.edu.vn', '089205003456', 'DN4893456789012', '/uploads/faceid/2351101.jpg', 'Y', 'H01.301', NULL, 'Nội trú'),
('2351102', 'Lương Minh Tuấn', 'Nam', '2005-08-08', 'Tây Ninh', '67 Cách Mạng Tháng 8, TP. Tây Ninh', '0935678901', '2351102@medvnu.edu.vn', '072205004567', 'DN4724567890123', '/uploads/faceid/2351102.jpg', 'Y', 'H01.301', NULL, 'Nội trú'),
('2450001', 'Phan Văn Hải', 'Nam', '2006-05-05', 'Kiên Giang', '12 Trần Phú, TP. Rạch Giá, Kiên Giang', '0946789012', '2450001@agu.edu.vn', '091206005678', 'HS4915678901234', '/uploads/faceid/2450001.jpg', 'AGU', 'C06.305', 'CV06', 'Nội trú'),
('2450002', 'Đào Quang Vinh', 'Nam', '2006-12-19', 'Cà Mau', '99 Phan Ngọc Hiển, TP. Cà Mau', '0957890123', '2450002@agu.edu.vn', '096206006789', 'HS4966789012345', '/uploads/faceid/2450002.jpg', 'AGU', 'C06.305', NULL, 'Nội trú'),
('2352099', 'Nguyễn Tấn Đạt', 'Nam', '2005-03-03', 'Sóc Trăng', '45 Lê Hồng Phong, TP. Sóc Trăng', '0968901234', '2352099@gm.uit.edu.vn', '094205007890', 'DN4947890123456', '/uploads/faceid/2352099.jpg', 'IT', 'D03.201', 'CV06', 'Nội trú'),
('2352100', 'Hà Thị Bích Vân', 'Nữ', '2005-07-21', 'Vũng Tàu', '150 Thùy Vân, TP. Vũng Tàu', '0979012345', '2352100@gm.uit.edu.vn', '077205008901', 'HS4778901234567', '/uploads/faceid/2352100.jpg', 'IT', 'BA1.202', NULL, 'Nội trú'),
('2210888', 'Lý Gia Uy', 'Nam', '2004-01-11', 'TP. Hồ Chí Minh', '78 Nguyễn Trãi, Q.5, TP.HCM', '0980123456', 'uy.ly2210888@sis.hcmut.edu.vn', '079204009012', 'DN4799012345678', '/uploads/faceid/2210888.jpg', 'BK', 'A02.201', 'CV06', 'Nội trú'),
('2312999', 'Trần Hữu Thịnh', 'Nam', '2005-11-30', 'Đồng Tháp', '23 Nguyễn Huệ, TP. Cao Lãnh, Đồng Tháp', '0991234567', 'ththinh@student.hcmus.edu.vn', '087205000123', 'DN4870123456789', '/uploads/faceid/2312999.jpg', 'KTN', NULL, NULL, 'Chờ duyệt');

-- Cập nhật Trưởng phòng cho các phòng đã có sinh viên đại diện
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2110001' WHERE `MAPHG` = 'A01.101';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2212345' WHERE `MAPHG` = 'A01.102';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2210888' WHERE `MAPHG` = 'A02.201';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2156001' WHERE `MAPHG` = 'H01.301';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2250102' WHERE `MAPHG` = 'H02.402';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2352011' WHERE `MAPHG` = 'BA1.101';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2411891' WHERE `MAPHG` = 'BA1.202';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2450001' WHERE `MAPHG` = 'C06.305';
UPDATE `PHONG_O` SET `TRPHG_MSSV` = '2352099' WHERE `MAPHG` = 'D03.201';

-- 9. BẢNG SV_NOITRU & SV_TAMTRU
INSERT INTO `SV_NOITRU` (`MSSV`, `THE_NOI_TRU_SO`, `NGAY_CAP_THE`, `NGAY_BAT_DAU_NOI_TRU`) VALUES
('2110001', 'TNT-2026-A01101', '2026-08-25', '2026-09-01'),
('2110002', 'TNT-2026-A01102', '2026-08-25', '2026-09-01'),
('2212345', 'TNT-2026-A01103', '2026-08-26', '2026-09-01'),
('2212346', 'TNT-2026-A01104', '2026-08-26', '2026-09-01'),
('2352011', 'TNT-2026-BA1101', '2026-08-27', '2026-09-01'),
('2352012', 'TNT-2026-BA1102', '2026-08-27', '2026-09-01'),
('2411890', 'TNT-2026-BA1103', '2026-08-28', '2026-09-01'),
('2411891', 'TNT-2026-BA1201', '2026-08-28', '2026-09-01'),
('2156001', 'TNT-2026-H01301', '2026-08-25', '2026-09-01'),
('2250102', 'TNT-2026-H02401', '2026-08-25', '2026-09-01'),
('2450001', 'TNT-2026-C06301', '2026-08-29', '2026-09-01'),
('2352099', 'TNT-2026-D03201', '2026-08-27', '2026-09-01');

INSERT INTO `SV_TAMTRU` (`MSSV`, `LY_DO_TAM_TRU`, `NGAY_BAT_DAU`, `NGAY_KET_THUC`) VALUES
('2351101', 'Học thực tập lâm sàng tại Bệnh viện Chợ Rẫy', '2026-10-01', '2026-12-31'),
('2351102', 'Tham gia Đề tài Nghiên cứu Khoa học ĐHQG', '2026-10-01', '2026-11-30');

-- 10. BẢNG THANNHAN (Liên hệ khẩn cấp)
INSERT INTO `THANNHAN` (`MSSV`, `TENTN`, `QUANHE`, `SDT_LIENHE`, `DIACHI`) VALUES
('2110001', 'Nguyễn Văn Bình', 'Cha', '0918123456', '123 Tạ Quang Bửu, P. Linh Trung, TP. Thủ Đức'),
('2212345', 'Trần Đình Trọng', 'Cha', '0918234567', '88 Đại lộ Bình Dương, TP. Thủ Dầu Một'),
('2352011', 'Đỗ Văn Thành', 'Cha', '0918345678', '56 Lý Thường Kiệt, TP. Mỹ Tho, Tiền Giang'),
('2411890', 'Bùi Thị Hà', 'Mẹ', '0918456789', '234 Hùng Vương, Q. Hải Châu, Đà Nẵng'),
('2156001', 'Hoàng Đình Khoa', 'Cha', '0918567890', '15 Lê Lợi, TP. Huế, Thừa Thiên Huế');

-- 11. BẢNG DANGKY_HOPDONG (Hợp đồng thuê chỗ ở KTX)
INSERT INTO `DANGKY_HOPDONG` (`MSHDG`, `MSSV`, `MAPHG`, `TGBD`, `TGKT`, `NGAY_NHAN_PHONG_DU_KIEN`, `MADIEN_MIEN_GIAM`, `TIEN_THE_CHAN`, `TRANG_THAI_HOPDONG`, `MA_XAC_THUC_OTP`) VALUES
('HD202609001', '2110001', 'A01.101', '2026-09-01', '2027-06-30', '2026-08-28', 'MG00', 500000.00, 'Hiệu lực', '891023'),
('HD202609002', '2110002', 'A01.101', '2026-09-01', '2027-06-30', '2026-08-28', 'MG30', 500000.00, 'Hiệu lực', '124578'),
('HD202609003', '2212345', 'A01.102', '2026-09-01', '2027-06-30', '2026-08-29', 'MG00', 500000.00, 'Hiệu lực', '345678'),
('HD202609004', '2352011', 'BA1.101', '2026-09-01', '2027-06-30', '2026-08-30', 'MG50', 500000.00, 'Hiệu lực', '987654'),
('HD202609005', '2411890', 'BA1.101', '2026-09-01', '2027-06-30', '2026-08-30', 'MG00', 500000.00, 'Hiệu lực', '456123'),
('HD202609006', '2156001', 'H01.301', '2026-09-01', '2027-06-30', '2026-08-28', 'MG00', 1000000.00, 'Hiệu lực', '789456'),
('HD202609007', '2250102', 'H02.402', '2026-09-01', '2027-06-30', '2026-08-28', 'MG100', 1000000.00, 'Hiệu lực', '654321'),
('HD202609008', '2450001', 'C06.305', '2026-09-01', '2027-06-30', '2026-08-31', 'MG50', 300000.00, 'Hiệu lực', '321987'),
('HD202609009', '2312999', 'A02.201', '2026-09-01', '2027-06-30', '2026-10-15', 'MG00', 500000.00, 'Chờ duyệt thanh toán', '112233');

-- 12. BẢNG HOADON (Hóa đơn điện nước dịch vụ tháng 9 & tháng 10/2026)
INSERT INTO `HOADON` (`MAHD`, `MAPHG`, `THANG`, `NAM`, `CHISO_DIEN_DAU`, `CHISO_DIEN_CUOI`, `CHISO_NUOC_DAU`, `CHISO_NUOC_CUOI`, `DINH_MUC_DIEN`, `DINH_MUC_NUOC`, `DON_GIA_DIEN`, `DON_GIA_NUOC`, `TTIEN_DIEN`, `TTIEN_NUOC`, `TTIEN`, `NGAY_LAP_PHIEU`, `MANV_LAP`) VALUES
('HD202609-A01101', 'A01.101', 9, 2026, 1000, 1150, 400, 430, 100, 20, 2500.00, 12000.00, 375000.00, 360000.00, 735000.00, '2026-09-30', 'NV006'),
('HD202609-BA1101', 'BA1.101', 9, 2026, 2100, 2350, 800, 845, 100, 20, 2500.00, 12000.00, 625000.00, 540000.00, 1165000.00, '2026-09-30', 'NV006'),
('HD202609-H01301', 'H01.301', 9, 2026, 3000, 3320, 1100, 1150, 100, 20, 2500.00, 12000.00, 800000.00, 600000.00, 1400000.00, '2026-09-30', 'NV006'),
('HD202609-C06305', 'C06.305', 9, 2026, 1500, 1680, 500, 560, 200, 40, 2500.00, 12000.00, 450000.00, 720000.00, 1170000.00, '2026-09-30', 'NV006'),
('HD202610-A01101', 'A01.101', 10, 2026, 1150, 1320, 430, 462, 100, 20, 2500.00, 12000.00, 425000.00, 384000.00, 809000.00, '2026-10-05', 'NV006'),
('HD202610-BA1101', 'BA1.101', 10, 2026, 2350, 2600, 845, 890, 100, 20, 2500.00, 12000.00, 625000.00, 540000.00, 1165000.00, '2026-10-05', 'NV006');

-- 13. BẢNG THANH_TOAN (Lịch sử giao dịch trực tuyến qua Ngân hàng / QR Code)
INSERT INTO `THANH_TOAN` (`MATT`, `MAHD`, `MSHDG`, `PHUONG_THUC_TT`, `MA_GIAO_DICH_BANK`, `NGAY_THANH_TOAN`, `SO_TIEN_TT`, `TRANG_THAI_TT`) VALUES
('TT20260901001', NULL, 'HD202609001', 'Mã QR KTX', 'QR2026082800123', '2026-08-28 10:15:00', 500000.00, 'Thành công'),
('TT20260901002', NULL, 'HD202609004', 'BIDV App', 'BIDV20260830998', '2026-08-30 14:22:10', 500000.00, 'Thành công'),
('TT20260930001', 'HD202609-A01101', NULL, 'Mã QR KTX', 'FT262761009823', '2026-10-02 14:30:00', 735000.00, 'Thành công'),
('TT20260930002', 'HD202609-BA1101', NULL, 'Chuyển khoản định danh CCCD', 'VCB202610030811', '2026-10-03 09:12:45', 1165000.00, 'Thành công'),
('TT20260930003', 'HD202609-H01301', NULL, 'VNPAY Gateway', 'VNPAY20261004112', '2026-10-04 16:45:00', 1400000.00, 'Thành công'),
('TT20261005001', 'HD202610-A01101', NULL, 'BIDV App', 'BIDV20261006554', '2026-10-06 11:05:30', 809000.00, 'Thành công');

-- 14. BẢNG HOATDONG_RENLUYEN & SV_THAMGIAM_HOATDONG
INSERT INTO `HOATDONG_RENLUYEN` (`MHDONG`, `TENHD`, `TGTCHUC`, `DIADIEM`, `DIEM_REN_LUYEN`) VALUES
('HD20260901', 'Ngày hội Chào đón Tân Sinh viên KTX ĐHQG-HCM 2026', '2026-09-15 18:30:00', 'Sân khấu Trung tâm Khu A KTX', 10),
('HD20260902', 'Tập huấn PCCC & Cứu nạn Cứu hộ Cụm nhà B.A - B.B', '2026-09-22 08:00:00', 'Sân tản bộ Tòa nhà BA1 Khu B', 15),
('HD20261001', 'Chiến dịch Chủ nhật Xanh: Vệ sinh không gian chung KTX', '2026-10-08 07:00:00', 'Toàn bộ khuôn viên Khu A & Khu B', 10);

INSERT INTO `SV_THAMGIAM_HOATDONG` (`MHDONG`, `MSSV`, `DIEM_DAT_DUOC`, `GHICHU`) VALUES
('HD20260901', '2110001', 10, 'Ban tổ chức hỗ trợ hướng dẫn sinh viên'),
('HD20260901', '2212345', 10, 'Tham gia trọn vẹn chương trình'),
('HD20260901', '2352011', 10, 'Tham gia trọn vẹn chương trình'),
('HD20260902', '2352011', 15, 'Thực hành diễn tập sử dụng bình chữa cháy thành công'),
('HD20260902', '2411890', 15, 'Thực hành diễn tập PCCC đạt yêu cầu'),
('HD20261001', '2110001', 10, 'Trưởng nhóm tình nguyện viên Cụm A.G'),
('HD20261001', '2450001', 10, 'Hoàn thành dọn dẹp khu vực Cụm B.C');

-- ==============================================================================
-- KẾT THÚC KỊCH BẢN KHỞI TẠO CSDL VÀ DỮ LIỆU MẪU (VERSION 2.0)
-- ==============================================================================
