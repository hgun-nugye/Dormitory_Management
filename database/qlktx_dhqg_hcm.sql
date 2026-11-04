-- ====================================================================================
-- CƠ SỞ DỮ LIỆU CHUẨN: QUẢN LÝ NỘI TRÚ KÝ TÚC XÁ ĐHQG-HCM (23 BẢNG)
-- Hệ quản trị: MySQL 8.0+ / MariaDB 10.4+ (XAMPP / phpMyAdmin)
-- Tích hợp đầy đủ 4 lớp Ràng buộc Toàn vẹn: PK/FK + UNIQUE + CHECK + TRIGGER
-- Bố cục:
--   + PHẦN 1: CẤU TRÚC BẢNG, KHÓA NGOẠI, UNIQUE, CHECK & TRIGGER (SCHEMA)
--   + PHẦN 2: DỮ LIỆU MẪU ĐỒNG BỘ 100% (SAMPLE DATA MỞ RỘNG KIỂM THỬ)
-- ====================================================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+07:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `qlktx_dhqg_hcm`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `qlktx_dhqg_hcm`;

-- ====================================================================================
-- PHẦN 1: CẤU TRÚC CƠ SỞ DỮ LIỆU & CÁC LỚP RÀNG BUỘC TOÀN VẸN
-- ====================================================================================

-- 1.1. Tắt kiểm tra khóa ngoại tạm thời CHỈ ĐỂ xóa sạch bảng cũ
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `dexuatthaydoicosovatchat`;
DROP TABLE IF EXISTS `chisodiennuoc`;
DROP TABLE IF EXISTS `dontumotcua`;
DROP TABLE IF EXISTS `giaodichthanhtoan`;
DROP TABLE IF EXISTS `hoadon`;
DROP TABLE IF EXISTS `hosomiengiam`;
DROP TABLE IF EXISTS `hopdongnoitru`;
DROP TABLE IF EXISTS `hosodangky`;
DROP TABLE IF EXISTS `dotdangky`;
DROP TABLE IF EXISTS `nhatkyravao`;
DROP TABLE IF EXISTS `phieubaohong`;
DROP TABLE IF EXISTS `renluyennoitru`;
DROP TABLE IF EXISTS `thietbiphong`;
DROP TABLE IF EXISTS `giuong`;
DROP TABLE IF EXISTS `phong`;
DROP TABLE IF EXISTS `nhanvien`;
DROP TABLE IF EXISTS `sinhvien`;
DROP TABLE IF EXISTS `taikhoan`;
DROP TABLE IF EXISTS `vaitro`;
DROP TABLE IF EXISTS `truongdaihoc`;
DROP TABLE IF EXISTS `loaiphong`;
DROP TABLE IF EXISTS `toanha`;
DROP TABLE IF EXISTS `cumnha`;
DROP TABLE IF EXISTS `khuktx`;

-- Bật lại kiểm tra khóa ngoại ngay lập tức trước khi tạo bảng và nạp dữ liệu
SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------------------------------
-- 1.2. Tạo cấu trúc 23 bảng (Sắp xếp theo thứ tự phụ thuộc từ bảng Cha đến bảng Con)
-- ------------------------------------------------------------------------------------

CREATE TABLE `khuktx` (
  `MaKhu` varchar(10) NOT NULL,
  `TenKhu` varchar(100) NOT NULL,
  `DiaChi` varchar(255) DEFAULT NULL,
  `SoDienThoaiTruc` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`MaKhu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cumnha` (
  `MaCum` varchar(10) NOT NULL,
  `MaKhu` varchar(10) NOT NULL,
  `TenCum` varchar(100) NOT NULL,
  `ViTriVanPhong` varchar(150) DEFAULT NULL,
  `HoTenTruongCum` varchar(100) DEFAULT NULL,
  `SoDienThoaiCum` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`MaCum`),
  KEY `fk_cumnha_khu` (`MaKhu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `toanha` (
  `MaToaNha` varchar(10) NOT NULL,
  `MaCum` varchar(10) NOT NULL,
  `TenToaNha` varchar(50) NOT NULL,
  `TongSoTang` int(11) NOT NULL DEFAULT 1,
  `GioiTinhQuyDinh` varchar(20) NOT NULL,
  `TrangThaiToaNha` varchar(50) NOT NULL DEFAULT 'Đang hoạt động',
  PRIMARY KEY (`MaToaNha`),
  KEY `fk_toanha_cum` (`MaCum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `loaiphong` (
  `MaLoaiPhong` varchar(20) NOT NULL,
  `TenLoaiPhong` varchar(100) NOT NULL,
  `SoGiuongToiDa` int(11) NOT NULL,
  `DonGiaLuuTruChuan` decimal(18,0) NOT NULL DEFAULT 0,
  `DonGiaDichVuTangThem` decimal(18,0) NOT NULL DEFAULT 0,
  `ChoPhepDongTheoKy` tinyint(1) NOT NULL DEFAULT 1,
  `DanhChoSVKhuyetTat` tinyint(1) NOT NULL DEFAULT 0,
  `AnhThamQuan360` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`MaLoaiPhong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `truongdaihoc` (
  `MaTruong` varchar(20) NOT NULL,
  `TenTruong` varchar(150) NOT NULL,
  `SoNamDaoTaoChuan` decimal(3,1) NOT NULL DEFAULT 4.0,
  `ThuocDHQGHCM` tinyint(1) NOT NULL DEFAULT 1,
  `DangHopTac` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`MaTruong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `vaitro` (
  `MaVaiTro` varchar(50) NOT NULL,
  `TenVaiTro` varchar(100) NOT NULL,
  `MoTa` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`MaVaiTro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `taikhoan` (
  `MaTaiKhoan` int(11) NOT NULL AUTO_INCREMENT,
  `TenDangNhap` varchar(50) NOT NULL COMMENT 'MSSV hoac MaNhanVien',
  `MatKhauMaHoa` varchar(255) NOT NULL,
  `MaVaiTro` varchar(50) NOT NULL,
  `MaCumQuanLy` varchar(10) DEFAULT NULL COMMENT 'Mã Khu (A/B), Mã Cụm (AG..BE) hoặc Mã Tòa (A01..)',
  `DangHoatDong` tinyint(1) NOT NULL DEFAULT 1,
  `NgayTao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaTaiKhoan`),
  UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  KEY `fk_taikhoan_vaitro` (`MaVaiTro`),
  KEY `idx_taikhoan_phamvi` (`MaCumQuanLy`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nhanvien` (
  `MaNhanVien` varchar(20) NOT NULL,
  `MaTaiKhoan` int(11) NOT NULL,
  `HoVaTen` varchar(100) NOT NULL,
  `GioiTinh` varchar(10) DEFAULT NULL,
  `SoDienThoai` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `ChucVu` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`MaNhanVien`),
  UNIQUE KEY `MaTaiKhoan` (`MaTaiKhoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sinhvien` (
  `MaSinhVien` varchar(20) NOT NULL,
  `MaTaiKhoan` int(11) NOT NULL,
  `MaTruong` varchar(20) NOT NULL,
  `MSSVTaiTruong` varchar(30) DEFAULT NULL,
  `HoVaTen` varchar(100) NOT NULL,
  `NgaySinh` date DEFAULT NULL,
  `GioiTinh` varchar(10) DEFAULT NULL,
  `SoCCCD` varchar(15) DEFAULT NULL,
  `KhoaTuyenSinh` int(11) DEFAULT NULL,
  `SoDienThoai` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `MaSoBHYT` varchar(20) DEFAULT NULL,
  `AnhThe4x6` varchar(255) DEFAULT NULL,
  `AnhCCCD` varchar(255) DEFAULT NULL,
  `AnhMinhChungHocTap` varchar(255) DEFAULT NULL,
  `SoTaiKhoanNganHang` varchar(30) DEFAULT NULL,
  `TenNganHang` varchar(100) DEFAULT NULL,
  `SDTPhuHuynh` varchar(15) DEFAULT NULL,
  `TrangThaiNoiTru` varchar(50) NOT NULL DEFAULT 'Chưa ở',
  PRIMARY KEY (`MaSinhVien`),
  UNIQUE KEY `MaTaiKhoan` (`MaTaiKhoan`),
  UNIQUE KEY `SoCCCD` (`SoCCCD`),
  KEY `fk_sinhvien_truong` (`MaTruong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `phong` (
  `MaPhong` varchar(20) NOT NULL,
  `MaToaNha` varchar(10) NOT NULL,
  `MaLoaiPhong` varchar(20) NOT NULL,
  `SoPhong` varchar(10) NOT NULL,
  `Tang` int(11) NOT NULL,
  `SoSVDangO` int(11) NOT NULL DEFAULT 0,
  `TrangThaiPhong` varchar(50) NOT NULL DEFAULT 'Còn chỗ',
  `TruongPhong` varchar(20) DEFAULT NULL COMMENT 'Mã sinh viên làm trưởng phòng',
  PRIMARY KEY (`MaPhong`),
  UNIQUE KEY `uq_phong_toa_sophong` (`MaToaNha`,`SoPhong`),
  KEY `fk_phong_toanha` (`MaToaNha`),
  KEY `fk_phong_loaiphong` (`MaLoaiPhong`),
  KEY `fk_phong_truongphong` (`TruongPhong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `giuong` (
  `MaGiuong` varchar(30) NOT NULL,
  `MaPhong` varchar(20) NOT NULL,
  `KyHieuGiuong` varchar(10) NOT NULL,
  `ViTriTangGiuong` int(11) NOT NULL DEFAULT 1,
  `TrangThaiGiuong` varchar(50) NOT NULL DEFAULT 'Trống',
  PRIMARY KEY (`MaGiuong`),
  UNIQUE KEY `uq_giuong_phong_kyhieu` (`MaPhong`,`KyHieuGiuong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `thietbiphong` (
  `MaThietBi` int(11) NOT NULL AUTO_INCREMENT,
  `MaPhong` varchar(20) NOT NULL,
  `TenThietBi` varchar(100) NOT NULL,
  `LoaiThietBi` varchar(50) DEFAULT NULL,
  `MaSoThietBi` varchar(50) DEFAULT NULL,
  `TinhTrangThietBi` varchar(50) NOT NULL DEFAULT 'Tốt',
  PRIMARY KEY (`MaThietBi`),
  UNIQUE KEY `uq_thietbi_maso` (`MaSoThietBi`),
  KEY `fk_thietbi_phong` (`MaPhong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dotdangky` (
  `MaDot` int(11) NOT NULL AUTO_INCREMENT,
  `TenDot` varchar(150) NOT NULL,
  `NamHoc` varchar(20) NOT NULL,
  `ThoiGianMoCong` datetime NOT NULL,
  `ThoiGianDongCong` datetime NOT NULL,
  `MocKetThuc1` date DEFAULT NULL,
  `MocKetThuc2` date DEFAULT NULL,
  `SoGioGiuCho` int(11) NOT NULL DEFAULT 72,
  PRIMARY KEY (`MaDot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hosodangky` (
  `MaHoSo` varchar(30) NOT NULL,
  `MaSinhVien` varchar(20) NOT NULL,
  `MaDot` int(11) NOT NULL,
  `MaGiuong` varchar(30) DEFAULT NULL,
  `LoaiLuongDangKy` varchar(50) NOT NULL,
  `NgayKetThucChon` date DEFAULT NULL,
  `ThoiGianNop` datetime NOT NULL DEFAULT current_timestamp(),
  `NguoiDuyet` int(11) DEFAULT NULL,
  `ThoiGianDuyet` datetime DEFAULT NULL,
  `HanChotThanhToan` datetime DEFAULT NULL,
  `TrangThaiHoSo` varchar(50) NOT NULL DEFAULT 'Chờ duyệt',
  `GhiChuTuChoi` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`MaHoSo`),
  KEY `fk_hoso_sv` (`MaSinhVien`),
  KEY `fk_hoso_dot` (`MaDot`),
  KEY `fk_hoso_giuong` (`MaGiuong`),
  KEY `fk_hoso_duyet` (`NguoiDuyet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hopdongnoitru` (
  `MaHopDong` varchar(30) NOT NULL,
  `MaHoSo` varchar(30) NOT NULL,
  `MaSinhVien` varchar(20) NOT NULL,
  `MaGiuong` varchar(30) NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `NgayNhanPhong` datetime DEFAULT NULL,
  `DaNopMauCT01` tinyint(1) NOT NULL DEFAULT 0,
  `TrangThaiHopDong` varchar(50) NOT NULL DEFAULT 'Chờ hiệu lực',
  PRIMARY KEY (`MaHopDong`),
  UNIQUE KEY `MaHoSo` (`MaHoSo`),
  KEY `fk_hopdong_sv` (`MaSinhVien`),
  KEY `fk_hopdong_giuong` (`MaGiuong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hosomiengiam` (
  `MaMienGiam` int(11) NOT NULL AUTO_INCREMENT,
  `MaSinhVien` varchar(20) NOT NULL,
  `MaHopDong` varchar(30) NOT NULL,
  `DoiTuongMienGiam` varchar(100) NOT NULL,
  `TepMinhChung` varchar(255) DEFAULT NULL,
  `DonGiaTranApDung` decimal(18,0) NOT NULL DEFAULT 0,
  `PhanTramGiam` int(11) NOT NULL DEFAULT 0,
  `SoTienHoanTra` decimal(18,0) NOT NULL DEFAULT 0,
  `TrangThaiDuyet` varchar(50) NOT NULL DEFAULT 'Chờ duyệt',
  PRIMARY KEY (`MaMienGiam`),
  KEY `fk_miengiam_sv` (`MaSinhVien`),
  KEY `fk_miengiam_hd` (`MaHopDong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `chisodiennuoc` (
  `MaChiSo` int(11) NOT NULL AUTO_INCREMENT,
  `MaPhong` varchar(20) NOT NULL,
  `Thang` int(11) NOT NULL,
  `Nam` int(11) NOT NULL,
  `DienCu` int(11) NOT NULL DEFAULT 0,
  `DienMoi` int(11) NOT NULL DEFAULT 0,
  `NuocCu` int(11) NOT NULL DEFAULT 0,
  `NuocMoi` int(11) NOT NULL DEFAULT 0,
  `SoSVThucOTaiThang` int(11) NOT NULL DEFAULT 0,
  `TienDien` decimal(18,0) NOT NULL DEFAULT 0,
  `TienNuoc` decimal(18,0) NOT NULL DEFAULT 0,
  `CanhBaoBatThuong` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`MaChiSo`),
  UNIQUE KEY `uq_chiso_phong_thang` (`MaPhong`,`Thang`,`Nam`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `hoadon` (
  `MaHoaDon` varchar(30) NOT NULL,
  `MaSinhVien` varchar(20) NOT NULL,
  `MaPhong` varchar(20) DEFAULT NULL,
  `MaHopDong` varchar(30) DEFAULT NULL,
  `LoaiHoaDon` varchar(50) NOT NULL,
  `SoTienPhaiDong` decimal(18,0) NOT NULL,
  `NgayTao` datetime NOT NULL DEFAULT current_timestamp(),
  `HanThanhToan` datetime DEFAULT NULL,
  `TrangThai` varchar(50) NOT NULL DEFAULT 'Chưa thanh toán',
  PRIMARY KEY (`MaHoaDon`),
  KEY `fk_hoadon_sv` (`MaSinhVien`),
  KEY `fk_hoadon_phong` (`MaPhong`),
  KEY `fk_hoadon_hd` (`MaHopDong`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `giaodichthanhtoan` (
  `MaGiaoDich` varchar(30) NOT NULL,
  `MaHoaDon` varchar(30) NOT NULL,
  `LoaiGiaoDich` varchar(30) NOT NULL,
  `KenhThanhToan` varchar(50) DEFAULT NULL,
  `MaThamChieuNganHang` varchar(50) DEFAULT NULL,
  `SoTien` decimal(18,0) NOT NULL,
  `ThoiGianGiaoDich` datetime NOT NULL DEFAULT current_timestamp(),
  `KetQuaGiaoDich` varchar(50) NOT NULL,
  PRIMARY KEY (`MaGiaoDich`),
  KEY `fk_giaodich_hoadon` (`MaHoaDon`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `dontumotcua` (
  `MaDon` varchar(30) NOT NULL,
  `MaSinhVien` varchar(20) NOT NULL,
  `MaHopDong` varchar(30) DEFAULT NULL,
  `LoaiDon` varchar(100) NOT NULL,
  `MaGiuongChuyenDen` varchar(30) DEFAULT NULL,
  `TuNgay` date DEFAULT NULL,
  `DenNgay` date DEFAULT NULL,
  `LyDo` varchar(500) DEFAULT NULL,
  `TepMinhChung` varchar(255) DEFAULT NULL,
  `SoTienChenhLechHoan` decimal(18,0) NOT NULL DEFAULT 0,
  `TrangThaiDuyet` varchar(50) NOT NULL DEFAULT 'Chờ duyệt',
  PRIMARY KEY (`MaDon`),
  KEY `fk_don_sv` (`MaSinhVien`),
  KEY `fk_don_hd` (`MaHopDong`),
  KEY `fk_don_giuong` (`MaGiuongChuyenDen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `phieubaohong` (
  `MaPhieu` varchar(30) NOT NULL,
  `MaPhong` varchar(20) DEFAULT NULL,
  `MaToaNha` varchar(10) DEFAULT NULL,
  `MaThietBi` int(11) DEFAULT NULL,
  `MaSinhVienBao` varchar(20) DEFAULT NULL,
  `MaNhanVienBao` varchar(20) DEFAULT NULL,
  `LoaiSuCo` varchar(20) NOT NULL DEFAULT 'PHONG',
  `MoTaHuHong` varchar(500) NOT NULL,
  `GhiChuTienDo` text DEFAULT NULL,
  `HinhAnh` varchar(255) DEFAULT NULL,
  `NhanVienKyThuat` int(11) DEFAULT NULL,
  `NguoiDuyet` int(11) DEFAULT NULL,
  `TrangThaiSua` varchar(50) NOT NULL DEFAULT 'Chờ xử lý',
  `NgayBao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaPhieu`),
  KEY `fk_baohong_phong` (`MaPhong`),
  KEY `fk_baohong_toanha` (`MaToaNha`),
  KEY `fk_baohong_tb` (`MaThietBi`),
  KEY `fk_baohong_sv` (`MaSinhVienBao`),
  KEY `fk_baohong_nvbao` (`MaNhanVienBao`),
  KEY `fk_baohong_nv` (`NhanVienKyThuat`),
  KEY `fk_baohong_duyet` (`NguoiDuyet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `nhatkyravao` (
  `MaLuot` bigint(20) NOT NULL AUTO_INCREMENT,
  `MaSinhVien` varchar(20) NOT NULL,
  `CongKiemSoat` varchar(50) NOT NULL,
  `HuongDi` varchar(10) NOT NULL,
  `ThoiGianQuet` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaLuot`),
  KEY `fk_nhatky_sv` (`MaSinhVien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `renluyennoitru` (
  `MaDanhGia` int(11) NOT NULL AUTO_INCREMENT,
  `MaSinhVien` varchar(20) NOT NULL,
  `NamHoc` varchar(20) NOT NULL,
  `HocKy` int(11) NOT NULL,
  `LoaiGhiNhan` varchar(50) NOT NULL,
  `NoiDung` varchar(500) DEFAULT NULL,
  `DiemThayDoi` int(11) NOT NULL DEFAULT 0,
  `TongDiemCuoiKy` int(11) DEFAULT NULL,
  `XepLoai` varchar(30) DEFAULT NULL,
  `DaGuiVeTruong` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`MaDanhGia`),
  KEY `fk_renluyen_sv` (`MaSinhVien`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------------
-- 1.3. Thiết lập Khóa ngoại (FOREIGN KEYS - 29 Ràng buộc Tham chiếu)
-- ------------------------------------------------------------------------------------

ALTER TABLE `cumnha`
  ADD CONSTRAINT `fk_cumnha_khu` FOREIGN KEY (`MaKhu`) REFERENCES `khuktx` (`MaKhu`) ON UPDATE CASCADE;

ALTER TABLE `toanha`
  ADD CONSTRAINT `fk_toanha_cum` FOREIGN KEY (`MaCum`) REFERENCES `cumnha` (`MaCum`) ON UPDATE CASCADE;

ALTER TABLE `taikhoan`
  ADD CONSTRAINT `fk_taikhoan_vaitro` FOREIGN KEY (`MaVaiTro`) REFERENCES `vaitro` (`MaVaiTro`) ON UPDATE CASCADE;

ALTER TABLE `nhanvien`
  ADD CONSTRAINT `fk_nhanvien_taikhoan` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`) ON UPDATE CASCADE;

ALTER TABLE `sinhvien`
  ADD CONSTRAINT `fk_sinhvien_taikhoan` FOREIGN KEY (`MaTaiKhoan`) REFERENCES `taikhoan` (`MaTaiKhoan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sinhvien_truong` FOREIGN KEY (`MaTruong`) REFERENCES `truongdaihoc` (`MaTruong`) ON UPDATE CASCADE;

ALTER TABLE `phong`
  ADD CONSTRAINT `fk_phong_loaiphong` FOREIGN KEY (`MaLoaiPhong`) REFERENCES `loaiphong` (`MaLoaiPhong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phong_toanha` FOREIGN KEY (`MaToaNha`) REFERENCES `toanha` (`MaToaNha`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_phong_truongphong` FOREIGN KEY (`TruongPhong`) REFERENCES `sinhvien` (`MaSinhVien`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `giuong`
  ADD CONSTRAINT `fk_giuong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `thietbiphong`
  ADD CONSTRAINT `fk_thietbi_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hosodangky`
  ADD CONSTRAINT `fk_hoso_dot` FOREIGN KEY (`MaDot`) REFERENCES `dotdangky` (`MaDot`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoso_duyet` FOREIGN KEY (`NguoiDuyet`) REFERENCES `taikhoan` (`MaTaiKhoan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoso_giuong` FOREIGN KEY (`MaGiuong`) REFERENCES `giuong` (`MaGiuong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoso_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `hopdongnoitru`
  ADD CONSTRAINT `fk_hopdong_giuong` FOREIGN KEY (`MaGiuong`) REFERENCES `giuong` (`MaGiuong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_hoso` FOREIGN KEY (`MaHoSo`) REFERENCES `hosodangky` (`MaHoSo`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hopdong_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `hosomiengiam`
  ADD CONSTRAINT `fk_miengiam_hd` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdongnoitru` (`MaHopDong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_miengiam_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `chisodiennuoc`
  ADD CONSTRAINT `fk_chiso_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON UPDATE CASCADE;

ALTER TABLE `hoadon`
  ADD CONSTRAINT `fk_hoadon_hd` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdongnoitru` (`MaHopDong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoadon_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_hoadon_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `giaodichthanhtoan`
  ADD CONSTRAINT `fk_giaodich_hoadon` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoadon` (`MaHoaDon`) ON UPDATE CASCADE;

ALTER TABLE `dontumotcua`
  ADD CONSTRAINT `fk_don_giuong` FOREIGN KEY (`MaGiuongChuyenDen`) REFERENCES `giuong` (`MaGiuong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_don_hd` FOREIGN KEY (`MaHopDong`) REFERENCES `hopdongnoitru` (`MaHopDong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_don_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `phieubaohong`
  ADD CONSTRAINT `fk_baohong_duyet` FOREIGN KEY (`NguoiDuyet`) REFERENCES `taikhoan` (`MaTaiKhoan`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_nv` FOREIGN KEY (`NhanVienKyThuat`) REFERENCES `taikhoan` (`MaTaiKhoan`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_nvbao` FOREIGN KEY (`MaNhanVienBao`) REFERENCES `nhanvien` (`MaNhanVien`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_phong` FOREIGN KEY (`MaPhong`) REFERENCES `phong` (`MaPhong`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_sv` FOREIGN KEY (`MaSinhVienBao`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_tb` FOREIGN KEY (`MaThietBi`) REFERENCES `thietbiphong` (`MaThietBi`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_baohong_toanha` FOREIGN KEY (`MaToaNha`) REFERENCES `toanha` (`MaToaNha`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `nhatkyravao`
  ADD CONSTRAINT `fk_nhatky_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

ALTER TABLE `renluyennoitru`
  ADD CONSTRAINT `fk_renluyen_sv` FOREIGN KEY (`MaSinhVien`) REFERENCES `sinhvien` (`MaSinhVien`) ON UPDATE CASCADE;

-- ------------------------------------------------------------------------------------
-- 1.4. Thiết lập Ràng buộc Duy nhất bổ sung (ADDITIONAL UNIQUE CONSTRAINTS)
-- ------------------------------------------------------------------------------------

-- Chặn 1 sinh viên làm Trưởng phòng của 2 phòng cùng lúc
ALTER TABLE `phong`
  ADD UNIQUE KEY `uq_phong_truongphong` (`TruongPhong`);

-- Chặn trùng MSSV trong cùng 1 trường, trùng SĐT, Email và Mã số BHYT của sinh viên
ALTER TABLE `sinhvien`
  ADD UNIQUE KEY `uq_sinhvien_truong_mssv` (`MaTruong`, `MSSVTaiTruong`),
  ADD UNIQUE KEY `uq_sinhvien_sdt` (`SoDienThoai`),
  ADD UNIQUE KEY `uq_sinhvien_email` (`Email`),
  ADD UNIQUE KEY `uq_sinhvien_bhyt` (`MaSoBHYT`);

-- Chặn trùng Email và SĐT của cán bộ nhân viên
ALTER TABLE `nhanvien`
  ADD UNIQUE KEY `uq_nhanvien_email` (`Email`),
  ADD UNIQUE KEY `uq_nhanvien_sdt` (`SoDienThoai`);

-- Chặn ghi nhận trùng mã tham chiếu giao dịch ngân hàng (chống trùng Webhook)
ALTER TABLE `giaodichthanhtoan`
  ADD UNIQUE KEY `uq_giaodich_mathamchieu` (`MaThamChieuNganHang`);

-- ------------------------------------------------------------------------------------
-- 1.5. Thiết lập Ràng buộc Miền giá trị & Liên thuộc tính (CHECK CONSTRAINTS)
-- ------------------------------------------------------------------------------------

-- 1. Bảng toanha
ALTER TABLE `toanha`
  ADD CONSTRAINT `chk_toanha_sotang` CHECK (`TongSoTang` BETWEEN 1 AND 30),
  ADD CONSTRAINT `chk_toanha_gioitinh` CHECK (`GioiTinhQuyDinh` IN ('Nam', 'Nữ')),
  ADD CONSTRAINT `chk_toanha_trangthai` CHECK (`TrangThaiToaNha` IN ('Đang hoạt động', 'Tạm đóng bảo trì', 'Ngưng sử dụng'));

-- 2. Bảng loaiphong
ALTER TABLE `loaiphong`
  ADD CONSTRAINT `chk_loaiphong_sogiuong` CHECK (`SoGiuongToiDa` IN (2, 4, 6, 8)),
  ADD CONSTRAINT `chk_loaiphong_dongia` CHECK (`DonGiaLuuTruChuan` >= 0 AND `DonGiaDichVuTangThem` >= 0),
  ADD CONSTRAINT `chk_loaiphong_co` CHECK (`ChoPhepDongTheoKy` IN (0, 1) AND `DanhChoSVKhuyetTat` IN (0, 1));

-- 3. Bảng truongdaihoc
ALTER TABLE `truongdaihoc`
  ADD CONSTRAINT `chk_truong_namdaotao` CHECK (`SoNamDaoTaoChuan` BETWEEN 3.0 AND 7.0),
  ADD CONSTRAINT `chk_truong_co` CHECK (`ThuocDHQGHCM` IN (0, 1) AND `DangHopTac` IN (0, 1));

-- 4. Bảng taikhoan, nhanvien, sinhvien
ALTER TABLE `taikhoan`
  ADD CONSTRAINT `chk_taikhoan_hoatdong` CHECK (`DangHoatDong` IN (0, 1));

ALTER TABLE `nhanvien`
  ADD CONSTRAINT `chk_nhanvien_gioitinh` CHECK (`GioiTinh` IS NULL OR `GioiTinh` IN ('Nam', 'Nữ'));

ALTER TABLE `sinhvien`
  ADD CONSTRAINT `chk_sinhvien_gioitinh` CHECK (`GioiTinh` IN ('Nam', 'Nữ')),
  ADD CONSTRAINT `chk_sinhvien_khoa` CHECK (`KhoaTuyenSinh` BETWEEN 2015 AND 2035),
  ADD CONSTRAINT `chk_sinhvien_trangthai` CHECK (`TrangThaiNoiTru` IN ('Chưa ở', 'Đang nội trú', 'Tạm vắng', 'Đã trả phòng', 'Bị kỷ luật'));

-- 5. Bảng phong, giuong, thietbiphong
ALTER TABLE `phong`
  ADD CONSTRAINT `chk_phong_tang` CHECK (`Tang` >= 1),
  ADD CONSTRAINT `chk_phong_sosv` CHECK (`SoSVDangO` BETWEEN 0 AND 8),
  ADD CONSTRAINT `chk_phong_trangthai` CHECK (`TrangThaiPhong` IN ('Còn chỗ', 'Đã đầy', 'Đang sử dụng', 'Đang bảo trì'));

ALTER TABLE `giuong`
  ADD CONSTRAINT `chk_giuong_tang` CHECK (`ViTriTangGiuong` IN (1, 2)),
  ADD CONSTRAINT `chk_giuong_trangthai` CHECK (`TrangThaiGiuong` IN ('Trống', 'Đang giữ chỗ', 'Đã có người ở', 'Đang bảo trì'));

ALTER TABLE `thietbiphong`
  ADD CONSTRAINT `chk_thietbi_tinhtrang` CHECK (`TinhTrangThietBi` IN ('Tốt', 'Đang báo hỏng', 'Đang bảo trì', 'Hỏng'));

-- 6. Bảng dotdangky, hosodangky, hopdongnoitru, hosomiengiam
ALTER TABLE `dotdangky`
  ADD CONSTRAINT `chk_dot_thoigian` CHECK (`ThoiGianDongCong` > `ThoiGianMoCong`),
  ADD CONSTRAINT `chk_dot_mocketthuc` CHECK (`MocKetThuc1` IS NULL OR `MocKetThuc2` IS NULL OR `MocKetThuc2` >= `MocKetThuc1`),
  ADD CONSTRAINT `chk_dot_giucho` CHECK (`SoGioGiuCho` > 0);

ALTER TABLE `hosodangky`
  ADD CONSTRAINT `chk_hoso_thoigianduyet` CHECK (`ThoiGianDuyet` IS NULL OR `ThoiGianDuyet` >= `ThoiGianNop`),
  ADD CONSTRAINT `chk_hoso_hanchot` CHECK (`HanChotThanhToan` IS NULL OR `ThoiGianDuyet` IS NULL OR `HanChotThanhToan` >= `ThoiGianDuyet`),
  ADD CONSTRAINT `chk_hoso_trangthai` CHECK (`TrangThaiHoSo` IN ('Chờ duyệt', 'Chờ thanh toán', 'Đã duyệt', 'Từ chối', 'Hết hạn thanh toán', 'Đã hủy'));

ALTER TABLE `hopdongnoitru`
  ADD CONSTRAINT `chk_hopdong_ngay` CHECK (`NgayKetThuc` > `NgayBatDau`),
  ADD CONSTRAINT `chk_hopdong_ct01` CHECK (`DaNopMauCT01` IN (0, 1)),
  ADD CONSTRAINT `chk_hopdong_trangthai` CHECK (`TrangThaiHopDong` IN ('Chờ hiệu lực', 'Đang hiệu lực', 'Đã kết thúc', 'Chấm dứt trước hạn'));

ALTER TABLE `hosomiengiam`
  ADD CONSTRAINT `chk_miengiam_phantram` CHECK (`PhanTramGiam` BETWEEN 0 AND 100),
  ADD CONSTRAINT `chk_miengiam_sotien` CHECK (`DonGiaTranApDung` >= 0 AND `SoTienHoanTra` >= 0),
  ADD CONSTRAINT `chk_miengiam_trangthai` CHECK (`TrangThaiDuyet` IN ('Chờ duyệt', 'Đã duyệt', 'Từ chối'));

-- 7. Bảng chisodiennuoc, hoadon, giaodichthanhtoan
ALTER TABLE `chisodiennuoc`
  ADD CONSTRAINT `chk_chiso_thangnam` CHECK (`Thang` BETWEEN 1 AND 12 AND `Nam` >= 2020),
  ADD CONSTRAINT `chk_chiso_dien` CHECK (`DienCu` >= 0 AND `DienMoi` >= `DienCu`),
  ADD CONSTRAINT `chk_chiso_nuoc` CHECK (`NuocCu` >= 0 AND `NuocMoi` >= `NuocCu`),
  ADD CONSTRAINT `chk_chiso_thanhphan` CHECK (`SoSVThucOTaiThang` >= 0 AND `TienDien` >= 0 AND `TienNuoc` >= 0 AND `CanhBaoBatThuong` IN (0, 1));

ALTER TABLE `hoadon`
  ADD CONSTRAINT `chk_hoadon_sotien` CHECK (`SoTienPhaiDong` >= 0),
  ADD CONSTRAINT `chk_hoadon_han` CHECK (`HanThanhToan` IS NULL OR `HanThanhToan` >= `NgayTao`),
  ADD CONSTRAINT `chk_hoadon_trangthai` CHECK (`TrangThai` IN ('Chưa thanh toán', 'Đã thanh toán', 'Quá hạn', 'Đã hủy'));

ALTER TABLE `giaodichthanhtoan`
  ADD CONSTRAINT `chk_giaodich_sotien` CHECK (`SoTien` > 0),
  ADD CONSTRAINT `chk_giaodich_loai` CHECK (`LoaiGiaoDich` IN ('Thu tiền', 'Hoàn trả lệ phí')),
  ADD CONSTRAINT `chk_giaodich_ketqua` CHECK (`KetQuaGiaoDich` IN ('Thành công', 'Thất bại', 'Đang xử lý'));

-- 8. Bảng dontumotcua, phieubaohong, nhatkyravao, renluyennoitru
ALTER TABLE `dontumotcua`
  ADD CONSTRAINT `chk_don_ngay` CHECK (`TuNgay` IS NULL OR `DenNgay` IS NULL OR `DenNgay` >= `TuNgay`),
  ADD CONSTRAINT `chk_don_sotien` CHECK (`SoTienChenhLechHoan` >= 0),
  ADD CONSTRAINT `chk_don_trangthai` CHECK (`TrangThaiDuyet` IN ('Chờ duyệt', 'Đã duyệt', 'Từ chối'));

ALTER TABLE `phieubaohong`
  ADD CONSTRAINT `chk_baohong_trangthai` CHECK (`TrangThaiSua` IN ('Chờ xử lý', 'Đang sửa chữa', 'Đã hoàn thành', 'Đã hủy'));

ALTER TABLE `nhatkyravao`
  ADD CONSTRAINT `chk_nhatky_huongdi` CHECK (`HuongDi` IN ('Vào', 'Ra'));

ALTER TABLE `renluyennoitru`
  ADD CONSTRAINT `chk_renluyen_hocky` CHECK (`HocKy` IN (1, 2, 3)),
  ADD CONSTRAINT `chk_renluyen_loai` CHECK (`LoaiGhiNhan` IN ('Hoạt động', 'Khen thưởng', 'Vi phạm', 'Kỷ luật')),
  ADD CONSTRAINT `chk_renluyen_tongdiem` CHECK (`TongDiemCuoiKy` IS NULL OR (`TongDiemCuoiKy` BETWEEN 0 AND 100)),
  ADD CONSTRAINT `chk_renluyen_xeploai` CHECK (`XepLoai` IS NULL OR `XepLoai` IN ('Xuất sắc', 'Tốt', 'Khá', 'Trung bình', 'Yếu', 'Kém')),
  ADD CONSTRAINT `chk_renluyen_dagui` CHECK (`DaGuiVeTruong` IN (0, 1));

-- ------------------------------------------------------------------------------------
-- 1.6. Thiết lập Trigger Ràng buộc Nghiệp vụ Liên bảng (INTER-TABLE TRIGGERS)
-- ------------------------------------------------------------------------------------

DELIMITER $$

-- 1. Trigger kiểm tra khi THÊM phòng: Tầng <= Tổng số tầng, Sĩ số <= Số giường tối đa, Trưởng phòng đúng giới tính tòa
CREATE TRIGGER `trg_phong_before_insert`
BEFORE INSERT ON `phong`
FOR EACH ROW
BEGIN
    DECLARE v_max_tang INT;
    DECLARE v_max_giuong INT;
    DECLARE v_gioitinh_toa VARCHAR(20);
    DECLARE v_gioitinh_tp VARCHAR(10);

    SELECT `TongSoTang`, `GioiTinhQuyDinh` INTO v_max_tang, v_gioitinh_toa
    FROM `toanha` WHERE `MaToaNha` = NEW.`MaToaNha`;

    SELECT `SoGiuongToiDa` INTO v_max_giuong
    FROM `loaiphong` WHERE `MaLoaiPhong` = NEW.`MaLoaiPhong`;

    IF NEW.`Tang` > v_max_tang THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi RBTV: Tầng của phòng vượt quá tổng số tầng của tòa nhà!';
    END IF;

    IF NEW.`SoSVDangO` > v_max_giuong THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi RBTV: Số sinh viên đang ở vượt quá sức chứa tối đa của loại phòng!';
    END IF;

    IF NEW.`TruongPhong` IS NOT NULL THEN
        SELECT `GioiTinh` INTO v_gioitinh_tp
        FROM `sinhvien` WHERE `MaSinhVien` = NEW.`TruongPhong`;

        IF v_gioitinh_tp <> v_gioitinh_toa THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lỗi RBTV: Giới tính của Trưởng phòng không khớp với giới tính quy định của tòa nhà!';
        END IF;
    END IF;
END$$

-- 2. Trigger kiểm tra khi CẬP NHẬT phòng
CREATE TRIGGER `trg_phong_before_update`
BEFORE UPDATE ON `phong`
FOR EACH ROW
BEGIN
    DECLARE v_max_tang INT;
    DECLARE v_max_giuong INT;
    DECLARE v_gioitinh_toa VARCHAR(20);
    DECLARE v_gioitinh_tp VARCHAR(10);

    SELECT `TongSoTang`, `GioiTinhQuyDinh` INTO v_max_tang, v_gioitinh_toa
    FROM `toanha` WHERE `MaToaNha` = NEW.`MaToaNha`;

    SELECT `SoGiuongToiDa` INTO v_max_giuong
    FROM `loaiphong` WHERE `MaLoaiPhong` = NEW.`MaLoaiPhong`;

    IF NEW.`Tang` > v_max_tang THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi RBTV: Tầng của phòng vượt quá tổng số tầng của tòa nhà!';
    END IF;

    IF NEW.`SoSVDangO` > v_max_giuong THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi RBTV: Số sinh viên đang ở vượt quá sức chứa tối đa của loại phòng!';
    END IF;

    IF NEW.`TruongPhong` IS NOT NULL THEN
        SELECT `GioiTinh` INTO v_gioitinh_tp
        FROM `sinhvien` WHERE `MaSinhVien` = NEW.`TruongPhong`;

        IF v_gioitinh_tp <> v_gioitinh_toa THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lỗi RBTV: Giới tính của Trưởng phòng không khớp với giới tính quy định của tòa nhà!';
        END IF;
    END IF;
END$$

-- 3. Trigger kiểm tra giới tính Sinh viên khớp Tòa nhà khi THÊM/SỬA Hồ sơ đăng ký
CREATE TRIGGER `trg_hosodangky_before_insert`
BEFORE INSERT ON `hosodangky`
FOR EACH ROW
BEGIN
    DECLARE v_gioitinh_sv VARCHAR(10);
    DECLARE v_gioitinh_toa VARCHAR(20);

    IF NEW.`MaGiuong` IS NOT NULL THEN
        SELECT `GioiTinh` INTO v_gioitinh_sv
        FROM `sinhvien` WHERE `MaSinhVien` = NEW.`MaSinhVien`;

        SELECT t.`GioiTinhQuyDinh` INTO v_gioitinh_toa
        FROM `giuong` g
        JOIN `phong` p ON g.`MaPhong` = p.`MaPhong`
        JOIN `toanha` t ON p.`MaToaNha` = t.`MaToaNha`
        WHERE g.`MaGiuong` = NEW.`MaGiuong`;

        IF v_gioitinh_sv <> v_gioitinh_toa THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lỗi RBTV: Giới tính sinh viên không khớp với giới tính quy định của tòa nhà đăng ký!';
        END IF;
    END IF;
END$$

CREATE TRIGGER `trg_hosodangky_before_update`
BEFORE UPDATE ON `hosodangky`
FOR EACH ROW
BEGIN
    DECLARE v_gioitinh_sv VARCHAR(10);
    DECLARE v_gioitinh_toa VARCHAR(20);

    IF NEW.`MaGiuong` IS NOT NULL THEN
        SELECT `GioiTinh` INTO v_gioitinh_sv
        FROM `sinhvien` WHERE `MaSinhVien` = NEW.`MaSinhVien`;

        SELECT t.`GioiTinhQuyDinh` INTO v_gioitinh_toa
        FROM `giuong` g
        JOIN `phong` p ON g.`MaPhong` = p.`MaPhong`
        JOIN `toanha` t ON p.`MaToaNha` = t.`MaToaNha`
        WHERE g.`MaGiuong` = NEW.`MaGiuong`;

        IF v_gioitinh_sv <> v_gioitinh_toa THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lỗi RBTV: Giới tính sinh viên không khớp với giới tính quy định của tòa nhà đăng ký!';
        END IF;
    END IF;
END$$

-- 4. Trigger kiểm tra giới tính Sinh viên khớp Tòa nhà khi THÊM Hợp đồng nội trú
CREATE TRIGGER `trg_hopdong_before_insert`
BEFORE INSERT ON `hopdongnoitru`
FOR EACH ROW
BEGIN
    DECLARE v_gioitinh_sv VARCHAR(10);
    DECLARE v_gioitinh_toa VARCHAR(20);

    SELECT `GioiTinh` INTO v_gioitinh_sv
    FROM `sinhvien` WHERE `MaSinhVien` = NEW.`MaSinhVien`;

    SELECT t.`GioiTinhQuyDinh` INTO v_gioitinh_toa
    FROM `giuong` g
    JOIN `phong` p ON g.`MaPhong` = p.`MaPhong`
    JOIN `toanha` t ON p.`MaToaNha` = t.`MaToaNha`
    WHERE g.`MaGiuong` = NEW.`MaGiuong`;

    IF v_gioitinh_sv <> v_gioitinh_toa THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Lỗi RBTV: Không thể ký hợp đồng xếp sinh viên vào tòa nhà khác giới tính!';
    END IF;
END$$

-- 5. Trigger kiểm tra giới tính khi nộp Đơn Một cửa xin chuyển phòng
CREATE TRIGGER `trg_dontumotcua_before_insert`
BEFORE INSERT ON `dontumotcua`
FOR EACH ROW
BEGIN
    DECLARE v_gioitinh_sv VARCHAR(10);
    DECLARE v_gioitinh_toa VARCHAR(20);

    IF NEW.`MaGiuongChuyenDen` IS NOT NULL THEN
        SELECT `GioiTinh` INTO v_gioitinh_sv
        FROM `sinhvien` WHERE `MaSinhVien` = NEW.`MaSinhVien`;

        SELECT t.`GioiTinhQuyDinh` INTO v_gioitinh_toa
        FROM `giuong` g
        JOIN `phong` p ON g.`MaPhong` = p.`MaPhong`
        JOIN `toanha` t ON p.`MaToaNha` = t.`MaToaNha`
        WHERE g.`MaGiuong` = NEW.`MaGiuongChuyenDen`;

        IF v_gioitinh_sv <> v_gioitinh_toa THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Lỗi RBTV: Giường xin chuyển đến thuộc tòa nhà không khớp giới tính sinh viên!';
        END IF;
    END IF;
END$$

DELIMITER ;


-- ====================================================================================
-- PHẦN 2: DỮ LIỆU MẪU ĐỒNG BỘ 100% (SAMPLE DATA - VƯỢT QUA TOÀN BỘ RÀNG BUỘC)
-- ====================================================================================

-- 2.1. Khu KTX
INSERT INTO `khuktx` (`MaKhu`, `TenKhu`, `DiaChi`, `SoDienThoaiTruc`) VALUES
('A', 'Ký túc xá Khu A - ĐHQG-HCM', 'Đường Tạ Quang Bửu, Khu phố 33, Phường Linh Xuân, TP. Hồ Chí Minh', '1900055559'),
('B', 'Ký túc xá Khu B - ĐHQG-HCM', 'Đường Mạc Đĩnh Chi, Khu phố Tân Hòa, Phường Đông Hòa, TP. Hồ Chí Minh', '1900055559');

-- 2.2. Cụm nhà (7 Ban Quản lý Cụm nhà thực tế)
INSERT INTO `cumnha` (`MaCum`, `MaKhu`, `TenCum`, `ViTriVanPhong`, `HoTenTruongCum`, `SoDienThoaiCum`) VALUES
('AG', 'A', 'Ban Quản lý Cụm nhà A.G', 'Tầng trệt Tòa A01, KTX Khu A', 'Trần Văn Minh', '02837240111'),
('AH', 'A', 'Ban Quản lý Cụm nhà A.H', 'Tầng trệt Tòa H01, KTX Khu A', 'Nguyễn Thị Thu Hương', '02837240112'),
('BA', 'B', 'Ban Quản lý Cụm nhà B.A', 'Tầng trệt Tòa BA1, KTX Khu B', 'Lê Hoàng Nam', '02837240201'),
('BB', 'B', 'Ban Quản lý Cụm nhà B.B', 'Tầng trệt Tòa B02, KTX Khu B', 'Vũ Quốc Đạt', '02837240202'),
('BC', 'B', 'Ban Quản lý Cụm nhà B.C', 'Tầng trệt Tòa C05, KTX Khu B', 'Ngô Thanh Sơn', '02837240203'),
('BD', 'B', 'Ban Quản lý Cụm nhà B.D', 'Tầng trệt Tòa D05, KTX Khu B', 'Đinh Trọng Khôi', '02837240204'),
('BE', 'B', 'Ban Quản lý Cụm nhà B.E', 'Tầng trệt Tòa E04, KTX Khu B', 'Bùi Thị Mai Anh', '02837240205');

-- 2.3. Tòa nhà
INSERT INTO `toanha` (`MaToaNha`, `MaCum`, `TenToaNha`, `TongSoTang`, `GioiTinhQuyDinh`, `TrangThaiToaNha`) VALUES
('A01', 'AG', 'Tòa A01', 5, 'Nam', 'Đang hoạt động'),
('G01', 'AG', 'Tòa G01', 12, 'Nam', 'Đang hoạt động'),
('A12', 'AG', 'Tòa A12', 5, 'Nam', 'Tạm đóng bảo trì'),
('A07', 'AH', 'Tòa A07', 5, 'Nữ', 'Đang hoạt động'),
('H01', 'AH', 'Tòa H01', 12, 'Nữ', 'Đang hoạt động'),
('BA1', 'BA', 'Tòa BA1', 12, 'Nam', 'Đang hoạt động'),
('BA4', 'BA', 'Tòa BA4', 12, 'Nữ', 'Đang hoạt động'),
('B02', 'BB', 'Tòa B02', 16, 'Nam', 'Đang hoạt động'),
('C05', 'BC', 'Tòa C05', 16, 'Nữ', 'Đang hoạt động'),
('D05', 'BD', 'Tòa D05', 16, 'Nữ', 'Đang hoạt động'),
('E04', 'BE', 'Tòa E04', 12, 'Nam', 'Đang hoạt động');

-- 2.4. Loại phòng
INSERT INTO `loaiphong` (`MaLoaiPhong`, `TenLoaiPhong`, `SoGiuongToiDa`, `DonGiaLuuTruChuan`, `DonGiaDichVuTangThem`, `ChoPhepDongTheoKy`, `DanhChoSVKhuyetTat`, `AnhThamQuan360`) VALUES
('LP8', 'Phòng tiêu chuẩn 8 giường', 8, 220000, 0, 1, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-8-chuan'),
('LP6', 'Phòng tiêu chuẩn 6 giường', 6, 300000, 0, 1, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-6-chuan'),
('LP4', 'Phòng tiêu chuẩn 4 giường', 4, 450000, 0, 1, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-4-chuan'),
('LP4ML', 'Phòng 4 giường dịch vụ máy lạnh', 4, 450000, 350000, 0, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-4-maylanh'),
('LP4FULL', 'Phòng 4 giường dịch vụ đầy đủ tiện nghi', 4, 450000, 650000, 0, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-4-full'),
('LP2DV', 'Phòng 2 giường dịch vụ cao cấp', 2, 850000, 900000, 0, 0, 'https://thamquan.ktxhcm.edu.vn/#phong-2-dichvu'),
('LP4KT', 'Phòng 4 giường hỗ trợ sinh viên khuyết tật', 4, 220000, 0, 1, 1, 'https://thamquan.ktxhcm.edu.vn/#phong-khuyettat');

-- 2.5. Trường Đại học
INSERT INTO `truongdaihoc` (`MaTruong`, `TenTruong`, `SoNamDaoTaoChuan`, `ThuocDHQGHCM`, `DangHopTac`) VALUES
('BK', 'Trường Đại học Bách khoa - ĐHQG-HCM', 4.5, 1, 1),
('KHTN', 'Trường Đại học Khoa học Tự nhiên - ĐHQG-HCM', 4.0, 1, 1),
('KHXHNV', 'Trường Đại học Khoa học Xã hội và Nhân văn - ĐHQG-HCM', 4.0, 1, 1),
('UIT', 'Trường Đại học Công nghệ Thông tin - ĐHQG-HCM', 4.0, 1, 1),
('UEL', 'Trường Đại học Kinh tế - Luật - ĐHQG-HCM', 4.0, 1, 1),
('IU', 'Trường Đại học Quốc tế - ĐHQG-HCM', 4.0, 1, 1),
('UHS', 'Trường Đại học Khoa học Sức khỏe - ĐHQG-HCM', 6.0, 1, 1),
('AGU', 'Trường Đại học An Giang - ĐHQG-HCM', 4.0, 1, 1),
('NLU', 'Trường Đại học Nông Lâm TP. Hồ Chí Minh', 4.0, 0, 1),
('SPKT', 'Trường Đại học Sư phạm Kỹ thuật TP. Hồ Chí Minh', 4.0, 0, 1);

-- 2.6. Vai trò hệ thống
INSERT INTO `vaitro` (`MaVaiTro`, `TenVaiTro`, `MoTa`) VALUES
('ADMIN', 'Quản trị viên hệ thống', 'Quản trị cấp cao hạ tầng kỹ thuật và phân quyền hệ thống IT'),
('KE_TOAN', 'Kế toán ký túc xá', 'Quản lý bảng giá, hóa đơn, đối soát giao dịch và giải ngân hoàn phí'),
('NV_QUAN_LY_KTX', 'Nhân viên quản lý chung KTX', 'Chuyên viên quản lý, giám sát nghiệp vụ toàn bộ hệ thống KTX'),
('NV_THUONG', 'Nhân viên thường', 'Nhân viên kỹ thuật điện nước, bảo trì, hành chính cụm nhà'),
('SINH_VIEN', 'Sinh viên nội trú', 'Sinh viên đăng ký hoặc đang lưu trú tại KTX'),
('TRUONG_CUM', 'Trưởng cụm nhà', 'Cán bộ quản lý phụ trách toàn diện một Ban quản lý Cụm nhà'),
('TRUONG_KHU', 'Trưởng khu KTX', 'Cán bộ quản lý phụ trách điều hành toàn bộ Khu A hoặc Khu B'),
('TRUONG_PHONG', 'Trưởng phòng nội trú', 'Sinh viên đại diện phòng nội trú (nhận thông báo, đại diện báo hỏng/điện nước)'),
('TRUONG_TOA', 'Trưởng tòa nhà', 'Cán bộ quản lý phụ trách trực tiếp một tòa nhà KTX');

-- 2.7. Tài khoản hệ thống (45 tài khoản: Cán bộ nhân viên & Sinh viên các trạng thái)
INSERT INTO `taikhoan` (`MaTaiKhoan`, `TenDangNhap`, `MatKhauMaHoa`, `MaVaiTro`, `MaCumQuanLy`, `DangHoatDong`, `NgayTao`) VALUES
(1, 'ADMIN', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'ADMIN', NULL, 1, '2026-08-01 08:00:00'),
(2, 'QLKTX01', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'NV_QUAN_LY_KTX', NULL, 1, '2026-08-01 08:05:00'),
(3, 'KT001', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'KE_TOAN', NULL, 1, '2026-08-01 08:10:00'),
(4, 'TK001', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_KHU', 'A', 1, '2026-08-01 08:15:00'),
(5, 'TK002', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_KHU', 'B', 1, '2026-08-01 08:20:00'),
(6, 'CUMAG', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'AG', 1, '2026-08-01 08:25:00'),
(7, 'CUMAH', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'AH', 1, '2026-08-01 08:30:00'),
(8, 'CUMBA', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'BA', 1, '2026-08-01 08:35:00'),
(9, 'CUMBB', '$2y$10$8Vu1kw4slLwzQVDEr.XHEuJAhMHGVIWecC7WYZDq9aENg.u3rmt.q', 'TRUONG_CUM', 'BB', 1, '2026-08-01 08:40:00'),
(10, 'CUMBC', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'BC', 1, '2026-08-01 08:45:00'),
(11, 'CUMBE', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'BE', 1, '2026-08-01 08:50:00'),
(12, 'TT001', '$2y$10$5FeDAShvBLpeqNATkVofEuGlhvPTZMk19VpJn.bDlSpT0V.9I8B7G', 'TRUONG_TOA', 'A01', 1, '2026-08-02 09:00:00'),
(13, 'TT002', '$2y$10$UbXt3qI86oXozCaV758uRORVLL8R1MESjm4cbuqfYzAny3wQr.OlK', 'TRUONG_TOA', 'H01', 1, '2026-08-02 09:05:00'),
(14, 'NV_KT01', '$2y$10$ep1Swd3zLNIv.mWQSl.xxOKjJXPZ7gXPraKN.eTY25ZW2icvtYzMK', 'NV_THUONG', 'AG', 1, '2026-08-02 09:10:00'),
(15, 'NV_KT02', '$2y$10$ep1Swd3zLNIv.mWQSl.xxOKjJXPZ7gXPraKN.eTY25ZW2icvtYzMK', 'NV_THUONG', 'BA', 1, '2026-08-02 09:15:00'),
(16, '2110001', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-15 10:00:00'),
(17, '2352010', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-15 10:05:00'),
(18, '2412005', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-15 10:10:00'),
(19, '2212345', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-15 10:15:00'),
(20, '2450112', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-15 10:20:00'),
(21, '2357022', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-15 10:25:00'),
(22, '2215099', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-16 09:00:00'),
(23, '2452088', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-16 09:10:00'),
(24, '2313045', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-16 09:20:00'),
(25, '2416077', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-16 09:30:00'),
(26, '2652001', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-09-05 14:00:00'),
(27, '2650099', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-11-02 15:30:00'),
(28, 'CUMBD', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_CUM', 'BD', 1, '2026-08-01 08:48:00'),
(29, 'TT003', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_TOA', 'BA1', 1, '2026-08-02 09:20:00'),
(30, 'TT004', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_TOA', 'C05', 1, '2026-08-02 09:25:00'),
(31, 'NV_KT03', '$2y$10$ep1Swd3zLNIv.mWQSl.xxOKjJXPZ7gXPraKN.eTY25ZW2icvtYzMK', 'NV_THUONG', 'AH', 1, '2026-08-02 09:30:00'),
(32, 'NV_KT04', '$2y$10$ep1Swd3zLNIv.mWQSl.xxOKjJXPZ7gXPraKN.eTY25ZW2icvtYzMK', 'NV_THUONG', 'BC', 1, '2026-08-02 09:35:00'),
(33, '2210555', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-16 10:00:00'),
(34, '2352111', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-16 10:05:00'),
(35, '2411222', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-16 10:10:00'),
(36, '2456333', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-16 10:15:00'),
(37, '2254011', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-16 10:20:00'),
(38, '2350222', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-16 10:25:00'),
(39, '2114888', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2025-08-10 08:00:00'),
(40, '2218999', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 0, '2026-08-16 11:00:00'),
(41, '2610101', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-10-09 08:00:00'),
(42, '2657088', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-10-19 08:30:00'),
(43, '2352999', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-17 09:00:00'),
(44, '2416088', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'SINH_VIEN', NULL, 1, '2026-08-17 09:15:00'),
(45, '2358012', '$2y$10$w0JG.DkFmxn8CBTeslXVCOKuP5JOeYFPuPICiNe9x0kD7.9leDXY2', 'TRUONG_PHONG', NULL, 1, '2026-08-17 09:30:00');

-- 2.8. Nhân viên (20 cán bộ nhân viên)
INSERT INTO `nhanvien` (`MaNhanVien`, `MaTaiKhoan`, `HoVaTen`, `GioiTinh`, `SoDienThoai`, `Email`, `ChucVu`) VALUES
('ADMIN', 1, 'Nguyễn Kim Kha', 'Nam', '0987452125', 'admin@ktxhcm.edu.vn', 'Quản trị hệ thống IT'),
('QLKTX01', 2, 'Hoàng Minh Tuấn', 'Nam', '0909555666', 'tuan.hm@ktxhcm.edu.vn', 'Chuyên viên Phòng CTSV - Chuyển đổi số'),
('KT001', 3, 'Lê Thị Bích Ngọc', 'Nữ', '0913444555', 'ngoc.ltb@ktxhcm.edu.vn', 'Kế toán trưởng Phòng Kế hoạch - Tài chính'),
('TK001', 4, 'Phạm Quốc Hùng', 'Nam', '0977333444', 'hung.pq@ktxhcm.edu.vn', 'Trưởng khu KTX Khu A'),
('TK002', 5, 'Đặng Văn Sơn', 'Nam', '0978999000', 'son.dv@ktxhcm.edu.vn', 'Trưởng khu KTX Khu B'),
('CUMAG', 6, 'Trần Văn Minh', 'Nam', '0903123456', 'minh.tv@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà A.G'),
('CUMAH', 7, 'Nguyễn Thị Thu Hương', 'Nữ', '0914234567', 'huong.ntt@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà A.H'),
('CUMBA', 8, 'Lê Hoàng Nam', 'Nam', '0905112233', 'nam.lh@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà B.A'),
('CUMBB', 9, 'Vũ Quốc Đạt', 'Nam', '0874512369', 'dat.vq@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà B.B'),
('CUMBC', 10, 'Ngô Thanh Sơn', 'Nam', '0938776655', 'son.nt@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà B.C'),
('CUMBE', 11, 'Bùi Thị Mai Anh', 'Nữ', '0945667788', 'anh.btm@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà B.E'),
('TT001', 12, 'Lê Văn Tòa', 'Nam', '0908777888', 'toa.lv@ktxhcm.edu.vn', 'Trưởng tòa nhà A01'),
('TT002', 13, 'Trần Thị Mai', 'Nữ', '0912345678', 'mai.tt@ktxhcm.edu.vn', 'Trưởng tòa nhà H01'),
('NV_KT01', 14, 'Nguyễn Văn Hải', 'Nam', '0923456789', 'hai.nv@ktxhcm.edu.vn', 'Nhân viên kỹ thuật điện nước Khu A'),
('NV_KT02', 15, 'Phan Trọng Nghĩa', 'Nam', '0934567890', 'nghia.pt@ktxhcm.edu.vn', 'Nhân viên kỹ thuật điện nước Khu B'),
('CUMBD', 28, 'Đinh Trọng Khôi', 'Nam', '0939887766', 'khoi.dt@ktxhcm.edu.vn', 'Trưởng Ban Quản lý Cụm nhà B.D'),
('TT003', 29, 'Phạm Văn Cường', 'Nam', '0915111222', 'cuong.pv@ktxhcm.edu.vn', 'Trưởng tòa nhà BA1'),
('TT004', 30, 'Lương Thị Hồng', 'Nữ', '0916222333', 'hong.lt@ktxhcm.edu.vn', 'Trưởng tòa nhà C05'),
('NV_KT03', 31, 'Trương Công Định', 'Nam', '0924555666', 'dinh.tc@ktxhcm.edu.vn', 'Nhân viên kỹ thuật bảo trì Cụm A.H'),
('NV_KT04', 32, 'Hồ Văn Lợi', 'Nam', '0935666777', 'loi.hv@ktxhcm.edu.vn', 'Nhân viên kỹ thuật điện nước Cụm B.C');

-- 2.9. Sinh viên (25 sinh viên đủ 5 trạng thái: Đang nội trú, Chưa ở, Tạm vắng, Đã trả phòng, Bị kỷ luật)
INSERT INTO `sinhvien` (`MaSinhVien`, `MaTaiKhoan`, `MaTruong`, `MSSVTaiTruong`, `HoVaTen`, `NgaySinh`, `GioiTinh`, `SoCCCD`, `KhoaTuyenSinh`, `SoDienThoai`, `Email`, `MaSoBHYT`, `AnhThe4x6`, `AnhCCCD`, `AnhMinhChungHocTap`, `SoTaiKhoanNganHang`, `TenNganHang`, `SDTPhuHuynh`, `TrangThaiNoiTru`) VALUES
('2110001', 16, 'BK', '2110001', 'Nguyễn Văn An', '2004-05-15', 'Nam', '079204001234', 2022, '0901234567', 'an.nguyen2110001@hcmut.edu.vn', 'SV479204001234', '/uploads/avatar/2110001_4x6.jpg', '/uploads/cccd/2110001_cccd.jpg', '/uploads/minhchung/2110001_xacnhan.pdf', '1029384756', 'Vietcombank', '0918123456', 'Đang nội trú'),
('2352010', 17, 'UIT', '2352010', 'Lê Minh Khoa', '2005-08-20', 'Nam', '079205002345', 2023, '0902345678', '2352010@gm.uit.edu.vn', 'SV479205002345', '/uploads/avatar/2352010_4x6.jpg', '/uploads/cccd/2352010_cccd.jpg', '/uploads/minhchung/2352010_thesv.jpg', '050123456789', 'Sacombank', '0918234561', 'Đang nội trú'),
('2412005', 18, 'KHTN', '2412005', 'Trần Quốc Bảo', '2006-11-12', 'Nam', '075206003456', 2024, '0903456789', '2412005@student.hcmus.edu.vn', 'SV475206003456', '/uploads/avatar/2412005_4x6.jpg', '/uploads/cccd/2412005_cccd.jpg', '/uploads/minhchung/2412005_thesv.jpg', '9876543210', 'BIDV', '0918345672', 'Đang nội trú'),
('2212345', 19, 'KHTN', '2212345', 'Trần Thị Mai Chúc', '2005-02-10', 'Nữ', '074305003456', 2023, '0923456789', '2212345@student.hcmus.edu.vn', 'SV474305003456', '/uploads/avatar/2212345_4x6.jpg', '/uploads/cccd/2212345_cccd.jpg', '/uploads/minhchung/2212345_thesv.jpg', '1903456789012', 'Techcombank', '0918234567', 'Đang nội trú'),
('2450112', 20, 'UEL', 'K2450112', 'Phạm Ngọc Lan', '2006-04-25', 'Nữ', '082306004567', 2024, '0934567812', 'lanpn2450112@st.uel.edu.vn', 'SV482306004567', '/uploads/avatar/2450112_4x6.jpg', '/uploads/cccd/2450112_cccd.jpg', '/uploads/minhchung/2450112_thesv.jpg', '104005678912', 'VietinBank', '0919456783', 'Đang nội trú'),
('2357022', 21, 'KHXHNV', '2357022', 'Võ Thị Thanh Thảo', '2005-09-05', 'Nữ', '052305005678', 2023, '0945678923', '2357022@hcmussh.edu.vn', 'SV452305005678', '/uploads/avatar/2357022_4x6.jpg', '/uploads/cccd/2357022_cccd.jpg', '/uploads/minhchung/2357022_thesv.jpg', '3141000234567', 'BIDV', '0917567894', 'Đang nội trú'),
('2215099', 22, 'BK', '2215099', 'Hoàng Gia Huy', '2004-12-01', 'Nam', '068204006789', 2022, '0967890123', 'huy.hoang2215099@hcmut.edu.vn', 'SV468204006789', '/uploads/avatar/2215099_4x6.jpg', '/uploads/cccd/2215099_cccd.jpg', '/uploads/minhchung/2215099_thesv.jpg', '0071001234567', 'Vietcombank', '0988112233', 'Đang nội trú'),
('2452088', 23, 'UIT', '2452088', 'Đỗ Tuấn Kiệt', '2006-07-18', 'Nam', '079206007890', 2024, '0978901234', '2452088@gm.uit.edu.vn', 'SV479206007890', '/uploads/avatar/2452088_4x6.jpg', '/uploads/cccd/2452088_cccd.jpg', '/uploads/minhchung/2452088_thesv.jpg', '0381009876543', 'MBBank', '0989223344', 'Đang nội trú'),
('2313045', 24, 'NLU', '2313045', 'Nguyễn Hữu Phúc', '2005-03-30', 'Nam', '080205008901', 2023, '0989012345', '2313045@st.hcmuaf.edu.vn', 'GD480205008901', '/uploads/avatar/2313045_4x6.jpg', '/uploads/cccd/2313045_cccd.jpg', '/uploads/minhchung/2313045_thesv.jpg', '6600205123456', 'Agribank', '0972334455', 'Đang nội trú'),
('2416077', 25, 'UHS', 'Y2416077', 'Huỳnh Thị Bích Trâm', '2006-01-19', 'Nữ', '083306009012', 2024, '0911223344', 'tramhtb2416@uhsvnu.edu.vn', 'SV483306009012', '/uploads/avatar/2416077_4x6.jpg', '/uploads/cccd/2416077_cccd.jpg', '/uploads/minhchung/2416077_thesv.jpg', '101876543210', 'VietinBank', '0913445566', 'Đang nội trú'),
('2652001', 26, 'UIT', '2652001', 'Ngô Gia Khiêm', '2008-06-14', 'Nam', '079208001122', 2026, '0908112233', '2652001@gm.uit.edu.vn', 'SV479208001122', '/uploads/avatar/2652001_4x6.jpg', '/uploads/cccd/2652001_cccd.jpg', '/uploads/minhchung/2652001_giaybaonhaphoc.pdf', '103811223344', 'Vietcombank', '0909887766', 'Chưa ở'),
('2650099', 27, 'UEL', 'K2650099', 'Lý Ngọc Hân', '2008-10-08', 'Nữ', '079308002233', 2026, '0908223344', 'hanln2650099@st.uel.edu.vn', 'SV479308002233', '/uploads/avatar/2650099_4x6.jpg', '/uploads/cccd/2650099_cccd.jpg', '/uploads/minhchung/2650099_giaybaonhaphoc.pdf', '190399887766', 'Techcombank', '0909776655', 'Chưa ở'),
('2210555', 33, 'BK', '2210555', 'Phan Thanh Tùng', '2004-03-22', 'Nam', '079204005551', 2022, '0904111222', 'tung.phan2210555@hcmut.edu.vn', 'SV479204005551', '/uploads/avatar/2210555_4x6.jpg', '/uploads/cccd/2210555_cccd.jpg', '/uploads/minhchung/2210555_thesv.jpg', '1029381111', 'Vietcombank', '0918000111', 'Đang nội trú'),
('2352111', 34, 'UIT', '2352111', 'Đặng Hoàng Long', '2005-07-11', 'Nam', '079205005552', 2023, '0904222333', '2352111@gm.uit.edu.vn', 'SV479205005552', '/uploads/avatar/2352111_4x6.jpg', '/uploads/cccd/2352111_cccd.jpg', '/uploads/minhchung/2352111_thesv.jpg', '1029382222', 'ACB', '0918000222', 'Đang nội trú'),
('2411222', 35, 'KHTN', '2411222', 'Bùi Minh Nhật', '2006-09-19', 'Nam', '075206005553', 2024, '0904333444', '2411222@student.hcmus.edu.vn', 'SV475206005553', '/uploads/avatar/2411222_4x6.jpg', '/uploads/cccd/2411222_cccd.jpg', '/uploads/minhchung/2411222_thesv.jpg', '1029383333', 'BIDV', '0918000333', 'Đang nội trú'),
('2456333', 36, 'IU', 'ITITIU24333', 'Trịnh Quốc Khánh', '2006-12-05', 'Nam', '079206005554', 2024, '0904444555', 'ITITIU24333@student.hcmiu.edu.vn', 'SV479206005554', '/uploads/avatar/2456333_4x6.jpg', '/uploads/cccd/2456333_cccd.jpg', '/uploads/minhchung/2456333_thesv.jpg', '1029384444', 'MBBank', '0918000444', 'Đang nội trú'),
('2254011', 37, 'IU', 'BABAIU22011', 'Nguyễn Lê Thảo Vy', '2004-04-14', 'Nữ', '079304005555', 2022, '0904555666', 'BABAIU22011@student.hcmiu.edu.vn', 'SV479304005555', '/uploads/avatar/2254011_4x6.jpg', '/uploads/cccd/2254011_cccd.jpg', '/uploads/minhchung/2254011_thesv.jpg', '1029385555', 'Techcombank', '0918000555', 'Đang nội trú'),
('2350222', 38, 'UEL', 'K2350222', 'Đinh Thị Tuyết Nhi', '2005-10-28', 'Nữ', '082305005556', 2023, '0904666777', 'nhidtt2350222@st.uel.edu.vn', 'SV482305005556', '/uploads/avatar/2350222_4x6.jpg', '/uploads/cccd/2350222_cccd.jpg', '/uploads/minhchung/2350222_thesv.jpg', '1029386666', 'VietinBank', '0918000666', 'Đang nội trú'),
('2114888', 39, 'BK', '2114888', 'Lâm Chấn Huy', '2003-08-08', 'Nam', '079203005557', 2021, '0904777888', 'huy.lam2114888@hcmut.edu.vn', 'SV479203005557', '/uploads/avatar/2114888_4x6.jpg', '/uploads/cccd/2114888_cccd.jpg', '/uploads/minhchung/2114888_thesv.jpg', '1029387777', 'Vietcombank', '0918000777', 'Đã trả phòng'),
('2218999', 40, 'SPKT', '2218999', 'Tạ Minh Quân', '2004-11-30', 'Nam', '077204005558', 2022, '0904888999', '2218999@student.hcmute.edu.vn', 'SV477204005558', '/uploads/avatar/2218999_4x6.jpg', '/uploads/cccd/2218999_cccd.jpg', '/uploads/minhchung/2218999_thesv.jpg', '1029388888', 'Sacombank', '0918000888', 'Bị kỷ luật'),
('2610101', 41, 'BK', '2610101', 'Vương Gia Bảo', '2008-02-17', 'Nam', '079208005559', 2026, '0904999000', 'bao.vuong2610101@hcmut.edu.vn', 'SV479208005559', '/uploads/avatar/2610101_4x6.jpg', '/uploads/cccd/2610101_cccd.jpg', '/uploads/minhchung/2610101_giaybaonhaphoc.pdf', '1029389999', 'BIDV', '0918000999', 'Chưa ở'),
('2657088', 42, 'KHXHNV', '2657088', 'Mai Phương Thúy', '2008-05-21', 'Nữ', '079308005560', 2026, '0905000111', '2657088@hcmussh.edu.vn', 'SV479308005560', '/uploads/avatar/2657088_4x6.jpg', '/uploads/cccd/2657088_cccd.jpg', '/uploads/minhchung/2657088_giaybaonhaphoc.pdf', '1029380001', 'Vietcombank', '0918001001', 'Chưa ở'),
('2352999', 43, 'UIT', '2352999', 'Châu Tinh Trì', '2005-01-12', 'Nam', '079205005561', 2023, '0905222444', '2352999@gm.uit.edu.vn', 'SV479205005561', '/uploads/avatar/2352999_4x6.jpg', '/uploads/cccd/2352999_cccd.jpg', '/uploads/minhchung/2352999_thesv.jpg', '1029380002', 'TPBank', '0918001002', 'Tạm vắng'),
('2416088', 44, 'UHS', 'Y2416088', 'Lê Thị Thanh Tâm', '2006-06-25', 'Nữ', '083306005562', 2024, '0905333555', 'tamltt2416@uhsvnu.edu.vn', 'SV483306005562', '/uploads/avatar/2416088_4x6.jpg', '/uploads/cccd/2416088_cccd.jpg', '/uploads/minhchung/2416088_thesv.jpg', '1029380003', 'Agribank', '0918001003', 'Đang nội trú'),
('2358012', 45, 'AGU', 'DTH2358012', 'Trần Ngọc Ánh', '2005-09-09', 'Nữ', '089305005563', 2023, '0905444666', 'anh_dth2358012@student.agu.edu.vn', 'SV489305005563', '/uploads/avatar/2358012_4x6.jpg', '/uploads/cccd/2358012_cccd.jpg', '/uploads/minhchung/2358012_thesv.jpg', '1029380004', 'VietinBank', '0918001004', 'Đang nội trú');

-- 2.10. Phòng ở (15 phòng trải đều các tòa và các trạng thái: Còn chỗ, Đã đầy, Đang bảo trì)
INSERT INTO `phong` (`MaPhong`, `MaToaNha`, `MaLoaiPhong`, `SoPhong`, `Tang`, `SoSVDangO`, `TrangThaiPhong`, `TruongPhong`) VALUES
('A01.101', 'A01', 'LP4', '101', 1, 3, 'Còn chỗ', '2110001'),
('A01.102', 'A01', 'LP4', '102', 1, 0, 'Còn chỗ', NULL),
('A01.201', 'A01', 'LP8', '201', 2, 0, 'Đang bảo trì', NULL),
('G01.201', 'G01', 'LP4ML', '201', 2, 0, 'Còn chỗ', NULL),
('G01.202', 'G01', 'LP4FULL', '202', 2, 4, 'Đã đầy', '2210555'),
('A07.101', 'A07', 'LP6', '101', 1, 0, 'Còn chỗ', NULL),
('H01.301', 'H01', 'LP4ML', '301', 3, 3, 'Còn chỗ', '2212345'),
('H01.302', 'H01', 'LP2DV', '302', 3, 2, 'Đã đầy', '2254011'),
('BA1.101', 'BA1', 'LP6', '101', 1, 3, 'Còn chỗ', '2215099'),
('BA4.201', 'BA4', 'LP4FULL', '201', 2, 0, 'Còn chỗ', NULL),
('B02.105', 'B02', 'LP8', '105', 1, 0, 'Còn chỗ', NULL),
('B02.111', 'B02', 'LP6', '111', 1, 0, 'Còn chỗ', NULL),
('C05.305', 'C05', 'LP8', '305', 3, 2, 'Còn chỗ', '2416077'),
('D05.201', 'D05', 'LP4', '201', 2, 1, 'Còn chỗ', '2358012'),
('E04.101', 'E04', 'LP4KT', '101', 1, 1, 'Còn chỗ', '2313045');

-- 2.11. Giường (72 giường đủ các trạng thái: Trống, Đang giữ chỗ, Đã có người ở, Đang bảo trì)
INSERT INTO `giuong` (`MaGiuong`, `MaPhong`, `KyHieuGiuong`, `ViTriTangGiuong`, `TrangThaiGiuong`) VALUES
('A01101-G1', 'A01.101', 'G1', 1, 'Đã có người ở'),
('A01101-G2', 'A01.101', 'G2', 2, 'Đã có người ở'),
('A01101-G3', 'A01.101', 'G3', 1, 'Đã có người ở'),
('A01101-G4', 'A01.101', 'G4', 2, 'Trống'),
('A01102-G1', 'A01.102', 'G1', 1, 'Trống'),
('A01102-G2', 'A01.102', 'G2', 2, 'Trống'),
('A01102-G3', 'A01.102', 'G3', 1, 'Trống'),
('A01102-G4', 'A01.102', 'G4', 2, 'Trống'),
('A01201-G1', 'A01.201', 'G1', 1, 'Đang bảo trì'),
('A01201-G2', 'A01.201', 'G2', 2, 'Đang bảo trì'),
('A01201-G3', 'A01.201', 'G3', 1, 'Đang bảo trì'),
('A01201-G4', 'A01.201', 'G4', 2, 'Đang bảo trì'),
('G01201-G1', 'G01.201', 'G1', 1, 'Trống'),
('G01201-G2', 'G01.201', 'G2', 2, 'Trống'),
('G01201-G3', 'G01.201', 'G3', 1, 'Trống'),
('G01201-G4', 'G01.201', 'G4', 2, 'Trống'),
('G01202-G1', 'G01.202', 'G1', 1, 'Đã có người ở'),
('G01202-G2', 'G01.202', 'G2', 2, 'Đã có người ở'),
('G01202-G3', 'G01.202', 'G3', 1, 'Đã có người ở'),
('G01202-G4', 'G01.202', 'G4', 2, 'Đã có người ở'),
('A07101-G1', 'A07.101', 'G1', 1, 'Trống'),
('A07101-G2', 'A07.101', 'G2', 2, 'Trống'),
('A07101-G3', 'A07.101', 'G3', 1, 'Trống'),
('A07101-G4', 'A07.101', 'G4', 2, 'Trống'),
('A07101-G5', 'A07.101', 'G5', 1, 'Trống'),
('A07101-G6', 'A07.101', 'G6', 2, 'Trống'),
('H01301-G1', 'H01.301', 'G1', 1, 'Đã có người ở'),
('H01301-G2', 'H01.301', 'G2', 2, 'Đã có người ở'),
('H01301-G3', 'H01.301', 'G3', 1, 'Đã có người ở'),
('H01301-G4', 'H01.301', 'G4', 2, 'Trống'),
('H01302-G1', 'H01.302', 'G1', 1, 'Đã có người ở'),
('H01302-G2', 'H01.302', 'G2', 1, 'Đã có người ở'),
('BA1101-G1', 'BA1.101', 'G1', 1, 'Đã có người ở'),
('BA1101-G2', 'BA1.101', 'G2', 2, 'Đã có người ở'),
('BA1101-G3', 'BA1.101', 'G3', 1, 'Đã có người ở'),
('BA1101-G4', 'BA1.101', 'G4', 2, 'Trống'),
('BA1101-G5', 'BA1.101', 'G5', 1, 'Trống'),
('BA1101-G6', 'BA1.101', 'G6', 2, 'Trống'),
('BA4201-G1', 'BA4.201', 'G1', 1, 'Trống'),
('BA4201-G2', 'BA4.201', 'G2', 2, 'Trống'),
('BA4201-G3', 'BA4.201', 'G3', 1, 'Trống'),
('BA4201-G4', 'BA4.201', 'G4', 2, 'Trống'),
('B02105-G1', 'B02.105', 'G1', 1, 'Đang giữ chỗ'),
('B02105-G2', 'B02.105', 'G2', 2, 'Trống'),
('B02105-G3', 'B02.105', 'G3', 1, 'Trống'),
('B02105-G4', 'B02.105', 'G4', 2, 'Trống'),
('B02105-G5', 'B02.105', 'G5', 1, 'Trống'),
('B02105-G6', 'B02.105', 'G6', 2, 'Trống'),
('B02105-G7', 'B02.105', 'G7', 1, 'Trống'),
('B02105-G8', 'B02.105', 'G8', 2, 'Trống'),
('B02111-G1', 'B02.111', 'G1', 1, 'Trống'),
('B02111-G2', 'B02.111', 'G2', 2, 'Trống'),
('B02111-G3', 'B02.111', 'G3', 1, 'Trống'),
('B02111-G4', 'B02.111', 'G4', 2, 'Trống'),
('B02111-G5', 'B02.111', 'G5', 1, 'Trống'),
('B02111-G6', 'B02.111', 'G6', 2, 'Trống'),
('C05305-G1', 'C05.305', 'G1', 1, 'Đã có người ở'),
('C05305-G2', 'C05.305', 'G2', 2, 'Đang giữ chỗ'),
('C05305-G3', 'C05.305', 'G3', 1, 'Đã có người ở'),
('C05305-G4', 'C05.305', 'G4', 2, 'Trống'),
('C05305-G5', 'C05.305', 'G5', 1, 'Trống'),
('C05305-G6', 'C05.305', 'G6', 2, 'Trống'),
('C05305-G7', 'C05.305', 'G7', 1, 'Trống'),
('C05305-G8', 'C05.305', 'G8', 2, 'Trống'),
('D05201-G1', 'D05.201', 'G1', 1, 'Đã có người ở'),
('D05201-G2', 'D05.201', 'G2', 2, 'Trống'),
('D05201-G3', 'D05.201', 'G3', 1, 'Trống'),
('D05201-G4', 'D05.201', 'G4', 2, 'Trống'),
('E04101-G1', 'E04.101', 'G1', 1, 'Đã có người ở'),
('E04101-G2', 'E04.101', 'G2', 1, 'Trống'),
('E04101-G3', 'E04.101', 'G3', 1, 'Trống'),
('E04101-G4', 'E04.101', 'G4', 1, 'Trống');

-- 2.12. Thiết bị phòng (20 thiết bị đủ 4 trạng thái: Tốt, Đang báo hỏng, Đang bảo trì, Hỏng)
INSERT INTO `thietbiphong` (`MaThietBi`, `MaPhong`, `TenThietBi`, `LoaiThietBi`, `MaSoThietBi`, `TinhTrangThietBi`) VALUES
(1, 'A01.101', 'Quạt đảo trần Senko', 'Thiết bị tiêu chuẩn', 'QT-A01101-01', 'Đang bảo trì'),
(2, 'A01.101', 'Bộ bàn học gỗ 4 chỗ', 'Nội thất', 'BH-A01101-01', 'Tốt'),
(3, 'A01.101', 'Tủ sắt cá nhân 4 ngăn', 'Nội thất', 'TS-A01101-01', 'Tốt'),
(4, 'H01.301', 'Máy lạnh Daikin Inverter 1.5HP', 'Thiết bị tăng thêm', 'ML-H01301-01', 'Tốt'),
(5, 'H01.301', 'Bình nước nóng Ariston 20L', 'Thiết bị tăng thêm', 'NN-H01301-01', 'Tốt'),
(6, 'H01.301', 'Rèm cửa chống nắng', 'Thiết bị tăng thêm', 'RC-H01301-01', 'Tốt'),
(7, 'H01.302', 'Máy lạnh Panasonic 1.5HP', 'Thiết bị tăng thêm', 'ML-H01302-01', 'Tốt'),
(8, 'H01.302', 'Tủ lạnh Aqua 130L', 'Thiết bị tăng thêm', 'TL-H01302-01', 'Đang báo hỏng'),
(9, 'BA1.101', 'Quạt treo tường Công nghiệp', 'Thiết bị tiêu chuẩn', 'QT-BA1101-01', 'Tốt'),
(10, 'BA1.101', 'Hệ thống đèn LED chiếu sáng', 'Thiết bị tiêu chuẩn', 'DL-BA1101-01', 'Đang báo hỏng'),
(11, 'C05.305', 'Quạt đảo trần Senko', 'Thiết bị tiêu chuẩn', 'QT-C05305-01', 'Tốt'),
(12, 'E04.101', 'Tay vịn hỗ trợ nhà vệ sinh', 'Thiết bị chuyên dụng', 'TV-E04101-01', 'Tốt'),
(13, 'G01.202', 'Máy lạnh Toshiba Inverter 1.5HP', 'Thiết bị tăng thêm', 'ML-G01202-01', 'Tốt'),
(14, 'G01.202', 'Máy giặt lồng đứng Aqua 8kg', 'Thiết bị tăng thêm', 'MG-G01202-01', 'Đang bảo trì'),
(15, 'G01.202', 'Tủ lạnh Beko 120L', 'Thiết bị tăng thêm', 'TL-G01202-01', 'Tốt'),
(16, 'A01.201', 'Quạt đảo trần Senko', 'Thiết bị tiêu chuẩn', 'QT-A01201-01', 'Hỏng'),
(17, 'C05.305', 'Vòi sen tắm Inax', 'Thiết bị vệ sinh', 'VS-C05305-01', 'Đang báo hỏng'),
(18, 'D05.201', 'Hệ thống đèn tuýp LED Rạng Đông', 'Thiết bị tiêu chuẩn', 'DL-D05201-01', 'Tốt'),
(19, 'D05.201', 'Ổ khóa cửa chính tay gạt', 'Thiết bị tiêu chuẩn', 'KC-D05201-01', 'Đang báo hỏng'),
(20, 'E04.101', 'Chuông gọi hỗ trợ khẩn cấp', 'Thiết bị chuyên dụng', 'CG-E04101-01', 'Tốt');

-- 2.13. Đợt đăng ký
INSERT INTO `dotdangky` (`MaDot`, `TenDot`, `NamHoc`, `ThoiGianMoCong`, `ThoiGianDongCong`, `MocKetThuc1`, `MocKetThuc2`, `SoGioGiuCho`) VALUES
(1, 'Đăng ký nội trú năm học 2025-2026', '2025-2026', '2025-08-01 08:00:00', '2025-10-15 23:59:59', '2026-06-30', '2026-07-31', 72),
(2, 'Đăng ký nội trú dành cho Sinh viên năm 2 trở lên năm học 2026-2027', '2026-2027', '2026-08-01 08:00:00', '2026-09-15 23:59:59', '2027-06-30', '2027-07-31', 72),
(3, 'Đăng ký nội trú dành cho Tân sinh viên năm học 2026-2027', '2026-2027', '2026-08-14 08:00:00', '2026-11-30 23:59:59', '2027-06-30', '2027-07-31', 72),
(4, 'Đăng ký nội trú bổ sung Học kỳ 2 năm học 2026-2027', '2026-2027', '2027-01-05 08:00:00', '2027-02-20 23:59:59', '2027-06-30', '2027-07-31', 48);

-- 2.14. Hồ sơ đăng ký (27 hồ sơ đủ cả 6 trạng thái: Chờ duyệt, Chờ thanh toán, Đã duyệt, Từ chối, Hết hạn thanh toán, Đã hủy)
INSERT INTO `hosodangky` (`MaHoSo`, `MaSinhVien`, `MaDot`, `MaGiuong`, `LoaiLuongDangKy`, `NgayKetThucChon`, `ThoiGianNop`, `NguoiDuyet`, `ThoiGianDuyet`, `HanChotThanhToan`, `TrangThaiHoSo`, `GhiChuTuChoi`) VALUES
('HS2025001', '2114888', 1, 'A01102-G1', 'Đăng ký thường', '2026-06-30', '2025-08-10 09:00:00', 6, '2025-08-10 15:00:00', '2025-08-13 15:00:00', 'Đã duyệt', NULL),
('HS2025002', '2110001', 1, 'A01101-G1', 'Đăng ký thường', '2026-06-30', '2025-08-11 10:00:00', 6, '2025-08-11 16:00:00', '2025-08-14 16:00:00', 'Đã duyệt', NULL),
('HS2026001', '2110001', 2, 'A01101-G1', 'Đăng ký thường', '2027-06-30', '2026-08-15 08:30:00', 6, '2026-08-15 14:00:00', '2026-08-18 14:00:00', 'Đã duyệt', NULL),
('HS2026002', '2352010', 2, 'A01101-G2', 'Đăng ký thường', '2027-06-30', '2026-08-15 09:15:00', 6, '2026-08-15 14:10:00', '2026-08-18 14:10:00', 'Đã duyệt', NULL),
('HS2026003', '2412005', 2, 'A01101-G3', 'Đăng ký thường', '2027-06-30', '2026-08-16 10:00:00', 6, '2026-08-16 15:00:00', '2026-08-19 15:00:00', 'Đã duyệt', NULL),
('HS2026004', '2212345', 2, 'H01301-G1', 'Đăng ký miễn giảm giá phòng', '2027-06-30', '2026-08-15 10:30:00', 7, '2026-08-15 16:00:00', '2026-08-18 16:00:00', 'Đã duyệt', NULL),
('HS2026005', '2450112', 2, 'H01301-G2', 'Đăng ký thường', '2027-06-30', '2026-08-16 11:00:00', 7, '2026-08-16 16:30:00', '2026-08-19 16:30:00', 'Đã duyệt', NULL),
('HS2026006', '2357022', 2, 'H01301-G3', 'Đăng ký miễn giảm giá phòng', '2027-06-30', '2026-08-17 08:45:00', 7, '2026-08-17 14:00:00', '2026-08-20 14:00:00', 'Đã duyệt', NULL),
('HS2026007', '2215099', 2, 'BA1101-G1', 'Đăng ký thường', '2027-07-31', '2026-08-18 09:00:00', 8, '2026-08-18 13:30:00', '2026-08-21 13:30:00', 'Đã duyệt', NULL),
('HS2026008', '2452088', 2, 'BA1101-G2', 'Đăng ký thường', '2027-06-30', '2026-08-18 09:30:00', 8, '2026-08-18 13:45:00', '2026-08-21 13:45:00', 'Đã duyệt', NULL),
('HS2026009', '2313045', 2, 'E04101-G1', 'Đăng ký miễn giảm giá phòng', '2027-06-30', '2026-08-19 08:00:00', 11, '2026-08-19 10:00:00', '2026-08-22 10:00:00', 'Đã duyệt', NULL),
('HS2026010', '2416077', 2, 'C05305-G1', 'Đăng ký thường', '2027-07-31', '2026-08-19 14:00:00', 10, '2026-08-19 17:00:00', '2026-08-22 17:00:00', 'Đã duyệt', NULL),
('HS2026011', '2652001', 3, 'B02105-G1', 'Đăng ký thường', '2027-06-30', '2026-11-01 09:00:00', 9, '2026-11-02 08:30:00', '2026-11-05 08:30:00', 'Chờ thanh toán', NULL),
('HS2026012', '2650099', 3, 'C05305-G2', 'Đăng ký thường', '2027-06-30', '2026-11-03 10:15:00', NULL, NULL, NULL, 'Chờ duyệt', NULL),
('HS2026013', '2210555', 2, 'G01202-G1', 'Đăng ký thường', '2027-06-30', '2026-08-16 11:00:00', 6, '2026-08-16 15:30:00', '2026-08-19 15:30:00', 'Đã duyệt', NULL),
('HS2026014', '2352111', 2, 'G01202-G2', 'Đăng ký thường', '2027-06-30', '2026-08-16 11:15:00', 6, '2026-08-16 15:40:00', '2026-08-19 15:40:00', 'Đã duyệt', NULL),
('HS2026015', '2411222', 2, 'G01202-G3', 'Đăng ký thường', '2027-06-30', '2026-08-16 11:30:00', 6, '2026-08-16 15:50:00', '2026-08-19 15:50:00', 'Đã duyệt', NULL),
('HS2026016', '2456333', 2, 'G01202-G4', 'Đăng ký thường', '2027-06-30', '2026-08-16 11:45:00', 6, '2026-08-16 16:00:00', '2026-08-19 16:00:00', 'Đã duyệt', NULL),
('HS2026017', '2254011', 2, 'H01302-G1', 'Đăng ký thường', '2027-07-31', '2026-08-17 09:00:00', 7, '2026-08-17 14:30:00', '2026-08-20 14:30:00', 'Đã duyệt', NULL),
('HS2026018', '2350222', 2, 'H01302-G2', 'Đăng ký thường', '2027-07-31', '2026-08-17 09:20:00', 7, '2026-08-17 14:45:00', '2026-08-20 14:45:00', 'Đã duyệt', NULL),
('HS2026019', '2218999', 2, 'B02111-G1', 'Đăng ký thường', '2027-06-30', '2026-08-18 10:00:00', 9, '2026-08-18 15:00:00', '2026-08-21 15:00:00', 'Đã duyệt', NULL),
('HS2026020', '2352999', 2, 'BA1101-G3', 'Đăng ký thường', '2027-06-30', '2026-08-18 10:30:00', 8, '2026-08-18 15:30:00', '2026-08-21 15:30:00', 'Đã duyệt', NULL),
('HS2026021', '2416088', 2, 'C05305-G3', 'Đăng ký miễn giảm giá phòng', '2027-06-30', '2026-08-19 15:00:00', 10, '2026-08-19 17:30:00', '2026-08-22 17:30:00', 'Đã duyệt', NULL),
('HS2026022', '2358012', 2, 'D05201-G1', 'Đăng ký miễn giảm giá phòng', '2027-06-30', '2026-08-20 08:30:00', 28, '2026-08-20 11:30:00', '2026-08-23 11:30:00', 'Đã duyệt', NULL),
('HS2026023', '2610101', 3, 'A01102-G2', 'Đăng ký thường', '2027-06-30', '2026-10-10 08:00:00', 6, '2026-10-10 14:00:00', NULL, 'Từ chối', 'Ảnh chụp CCCD bị mờ và thiếu giấy báo nhập học hợp lệ'),
('HS2026024', '2610101', 3, 'A01102-G2', 'Đăng ký thường', '2027-06-30', '2026-10-12 09:00:00', 6, '2026-10-12 15:00:00', '2026-10-15 15:00:00', 'Hết hạn thanh toán', 'Quá thời hạn 72 giờ giữ chỗ nhưng sinh viên chưa thanh toán lệ phí'),
('HS2026025', '2657088', 3, 'A07101-G1', 'Đăng ký thường', '2027-06-30', '2026-10-20 10:00:00', NULL, NULL, NULL, 'Đã hủy', 'Sinh viên chủ động hủy hồ sơ đăng ký để chọn lại loại phòng khác');

-- 2.15. Hợp đồng nội trú (22 hợp đồng đủ 4 trạng thái: Đang hiệu lực, Chờ hiệu lực, Đã kết thúc, Chấm dứt trước hạn)
INSERT INTO `hopdongnoitru` (`MaHopDong`, `MaHoSo`, `MaSinhVien`, `MaGiuong`, `NgayBatDau`, `NgayKetThuc`, `NgayNhanPhong`, `DaNopMauCT01`, `TrangThaiHopDong`) VALUES
('HD2025001', 'HS2025001', '2114888', 'A01102-G1', '2025-09-01', '2026-06-30', '2025-09-01 08:00:00', 1, 'Đã kết thúc'),
('HD2025002', 'HS2025002', '2110001', 'A01101-G1', '2025-09-01', '2026-06-30', '2025-09-01 08:30:00', 1, 'Đã kết thúc'),
('HD2026001', 'HS2026001', '2110001', 'A01101-G1', '2026-09-01', '2027-06-30', '2026-09-01 08:00:00', 1, 'Đang hiệu lực'),
('HD2026002', 'HS2026002', '2352010', 'A01101-G2', '2026-09-01', '2027-06-30', '2026-09-01 08:30:00', 1, 'Đang hiệu lực'),
('HD2026003', 'HS2026003', '2412005', 'A01101-G3', '2026-09-01', '2027-06-30', '2026-09-01 09:15:00', 1, 'Đang hiệu lực'),
('HD2026004', 'HS2026004', '2212345', 'H01301-G1', '2026-09-01', '2027-06-30', '2026-09-01 09:00:00', 1, 'Đang hiệu lực'),
('HD2026005', 'HS2026005', '2450112', 'H01301-G2', '2026-09-01', '2027-06-30', '2026-09-01 10:00:00', 1, 'Đang hiệu lực'),
('HD2026006', 'HS2026006', '2357022', 'H01301-G3', '2026-09-01', '2027-06-30', '2026-09-02 08:45:00', 1, 'Đang hiệu lực'),
('HD2026007', 'HS2026007', '2215099', 'BA1101-G1', '2026-09-01', '2027-07-31', '2026-09-01 13:30:00', 1, 'Đang hiệu lực'),
('HD2026008', 'HS2026008', '2452088', 'BA1101-G2', '2026-09-01', '2027-06-30', '2026-09-02 14:00:00', 1, 'Đang hiệu lực'),
('HD2026009', 'HS2026009', '2313045', 'E04101-G1', '2026-09-01', '2027-06-30', '2026-09-01 07:45:00', 1, 'Đang hiệu lực'),
('HD2026010', 'HS2026010', '2416077', 'C05305-G1', '2026-09-01', '2027-07-31', '2026-09-02 09:30:00', 1, 'Đang hiệu lực'),
('HD2026011', 'HS2026013', '2210555', 'G01202-G1', '2026-09-01', '2027-06-30', '2026-09-01 09:00:00', 1, 'Đang hiệu lực'),
('HD2026012', 'HS2026014', '2352111', 'G01202-G2', '2026-09-01', '2027-06-30', '2026-09-01 09:15:00', 1, 'Đang hiệu lực'),
('HD2026013', 'HS2026015', '2411222', 'G01202-G3', '2026-09-01', '2027-06-30', '2026-09-01 09:30:00', 1, 'Đang hiệu lực'),
('HD2026014', 'HS2026016', '2456333', 'G01202-G4', '2026-09-01', '2027-06-30', '2026-09-01 09:45:00', 0, 'Đang hiệu lực'),
('HD2026015', 'HS2026017', '2254011', 'H01302-G1', '2026-09-01', '2027-07-31', '2026-09-01 10:30:00', 1, 'Đang hiệu lực'),
('HD2026016', 'HS2026018', '2350222', 'H01302-G2', '2026-09-01', '2027-07-31', '2026-09-01 10:45:00', 0, 'Đang hiệu lực'),
('HD2026017', 'HS2026019', '2218999', 'B02111-G1', '2026-09-01', '2027-06-30', '2026-09-01 14:00:00', 1, 'Chấm dứt trước hạn'),
('HD2026018', 'HS2026020', '2352999', 'BA1101-G3', '2026-09-01', '2027-06-30', '2026-09-01 15:00:00', 1, 'Đang hiệu lực'),
('HD2026019', 'HS2026021', '2416088', 'C05305-G3', '2026-09-01', '2027-06-30', '2026-09-02 10:00:00', 1, 'Đang hiệu lực'),
('HD2026020', 'HS2026022', '2358012', 'D05201-G1', '2026-09-01', '2027-06-30', '2026-09-03 08:30:00', 0, 'Chờ hiệu lực');

-- 2.16. Hồ sơ miễn giảm lệ phí phòng ở (Đủ 3 trạng thái: Đã duyệt, Chờ duyệt, Từ chối)
INSERT INTO `hosomiengiam` (`MaMienGiam`, `MaSinhVien`, `MaHopDong`, `DoiTuongMienGiam`, `TepMinhChung`, `DonGiaTranApDung`, `PhanTramGiam`, `SoTienHoanTra`, `TrangThaiDuyet`) VALUES
(1, '2313045', 'HD2026009', 'Sinh viên khuyết tật có giấy xác nhận', '/uploads/miengiam/2313045_khuyettat.pdf', 220000, 100, 2200000, 'Đã duyệt'),
(2, '2212345', 'HD2026004', 'Sinh viên thuộc hộ cận nghèo', '/uploads/miengiam/2212345_canngheo.pdf', 300000, 50, 1500000, 'Đã duyệt'),
(3, '2357022', 'HD2026006', 'Sinh viên thuộc hộ nghèo vượt khó', '/uploads/miengiam/2357022_hongheo.pdf', 300000, 50, 1500000, 'Chờ duyệt'),
(4, '2416088', 'HD2026019', 'Con thương binh, bệnh binh hạng 2/4', '/uploads/miengiam/2416088_thuongbinh.pdf', 220000, 100, 2200000, 'Đã duyệt'),
(5, '2358012', 'HD2026020', 'Sinh viên dân tộc thiểu số vùng đặc biệt khó khăn', '/uploads/miengiam/2358012_dantoc.pdf', 300000, 100, 3000000, 'Chờ duyệt'),
(6, '2412005', 'HD2026003', 'Sinh viên thuộc hộ cận nghèo', '/uploads/miengiam/2412005_sohongheo_hethan.pdf', 300000, 50, 0, 'Từ chối');

-- 2.17. Chỉ số điện nước (16 bản ghi qua các tháng 9, 10/2026 và có cảnh báo bất thường)
INSERT INTO `chisodiennuoc` (`MaChiSo`, `MaPhong`, `Thang`, `Nam`, `DienCu`, `DienMoi`, `NuocCu`, `NuocMoi`, `SoSVThucOTaiThang`, `TienDien`, `TienNuoc`, `CanhBaoBatThuong`) VALUES
(1, 'A01.101', 9, 2026, 1000, 1120, 400, 418, 3, 300000, 180000, 0),
(2, 'H01.301', 9, 2026, 2400, 2650, 610, 632, 3, 625000, 220000, 0),
(3, 'BA1.101', 9, 2026, 850, 930, 210, 222, 3, 200000, 120000, 0),
(4, 'C05.305', 9, 2026, 500, 540, 120, 126, 2, 100000, 60000, 0),
(5, 'E04.101', 9, 2026, 310, 345, 90, 95, 1, 87500, 50000, 0),
(6, 'A01.101', 10, 2026, 1120, 1250, 418, 438, 3, 325000, 200000, 0),
(7, 'H01.301', 10, 2026, 2650, 2910, 632, 695, 3, 650000, 630000, 1),
(8, 'BA1.101', 10, 2026, 930, 1020, 222, 235, 3, 225000, 130000, 0),
(9, 'G01.202', 9, 2026, 3100, 3420, 510, 538, 4, 800000, 280000, 0),
(10, 'G01.202', 10, 2026, 3420, 3760, 538, 568, 4, 850000, 300000, 0),
(11, 'H01.302', 9, 2026, 1500, 1710, 300, 314, 2, 525000, 140000, 0),
(12, 'H01.302', 10, 2026, 1710, 2150, 314, 330, 2, 1100000, 160000, 1),
(13, 'C05.305', 10, 2026, 540, 590, 126, 134, 2, 125000, 80000, 0),
(14, 'D05.201', 9, 2026, 200, 230, 50, 54, 1, 75000, 40000, 0),
(15, 'D05.201', 10, 2026, 230, 265, 54, 59, 1, 87500, 50000, 0),
(16, 'E04.101', 10, 2026, 345, 385, 95, 101, 1, 100000, 60000, 0);

-- 2.18. Hóa đơn (33 hóa đơn đủ 4 trạng thái: Đã thanh toán, Chưa thanh toán, Quá hạn, Đã hủy)
INSERT INTO `hoadon` (`MaHoaDon`, `MaSinhVien`, `MaPhong`, `MaHopDong`, `LoaiHoaDon`, `SoTienPhaiDong`, `NgayTao`, `HanThanhToan`, `TrangThai`) VALUES
('HD-LP-2026-001', '2110001', 'A01.101', 'HD2026001', 'Tiền phòng năm học 2026-2027 (10 tháng)', 4500000, '2026-08-15 14:00:00', '2026-08-18 14:00:00', 'Đã thanh toán'),
('HD-LP-2026-002', '2352010', 'A01.101', 'HD2026002', 'Tiền phòng năm học 2026-2027 (10 tháng)', 4500000, '2026-08-15 14:10:00', '2026-08-18 14:10:00', 'Đã thanh toán'),
('HD-LP-2026-003', '2412005', 'A01.101', 'HD2026003', 'Tiền phòng năm học 2026-2027 (10 tháng)', 4500000, '2026-08-16 15:00:00', '2026-08-19 15:00:00', 'Đã thanh toán'),
('HD-LP-2026-004', '2212345', 'H01.301', 'HD2026004', 'Tiền phòng & Dịch vụ máy lạnh 2026-2027', 8000000, '2026-08-15 16:00:00', '2026-08-18 16:00:00', 'Đã thanh toán'),
('HD-LP-2026-005', '2450112', 'H01.301', 'HD2026005', 'Tiền phòng & Dịch vụ máy lạnh 2026-2027', 8000000, '2026-08-16 16:30:00', '2026-08-19 16:30:00', 'Đã thanh toán'),
('HD-LP-2026-006', '2357022', 'H01.301', 'HD2026006', 'Tiền phòng & Dịch vụ máy lạnh 2026-2027', 8000000, '2026-08-17 14:00:00', '2026-08-20 14:00:00', 'Đã thanh toán'),
('HD-LP-2026-007', '2215099', 'BA1.101', 'HD2026007', 'Tiền phòng năm học 2026-2027 (11 tháng)', 3300000, '2026-08-18 13:30:00', '2026-08-21 13:30:00', 'Đã thanh toán'),
('HD-LP-2026-008', '2452088', 'BA1.101', 'HD2026008', 'Tiền phòng Học kỳ 1 năm học 2026-2027 (5 tháng)', 1500000, '2026-08-18 13:45:00', '2026-08-21 13:45:00', 'Đã thanh toán'),
('HD-LP-2026-009', '2313045', 'E04.101', 'HD2026009', 'Tiền phòng năm học 2026-2027 (10 tháng)', 2200000, '2026-08-19 10:00:00', '2026-08-22 10:00:00', 'Đã thanh toán'),
('HD-LP-2026-010', '2416077', 'C05.305', 'HD2026010', 'Tiền phòng năm học 2026-2027 (11 tháng)', 2420000, '2026-08-19 17:00:00', '2026-08-22 17:00:00', 'Đã thanh toán'),
('HD-LP-2026-011', '2652001', 'B02.105', NULL, 'Tiền phòng Tân sinh viên năm học 2026-2027', 2200000, '2026-11-02 08:30:00', '2026-11-05 08:30:00', 'Chưa thanh toán'),
('HD-LP-2026-012', '2210555', 'G01.202', 'HD2026011', 'Tiền phòng & Dịch vụ tiện nghi 2026-2027', 11000000, '2026-08-16 15:30:00', '2026-08-19 15:30:00', 'Đã thanh toán'),
('HD-LP-2026-013', '2352111', 'G01.202', 'HD2026012', 'Tiền phòng & Dịch vụ tiện nghi 2026-2027', 11000000, '2026-08-16 15:40:00', '2026-08-19 15:40:00', 'Đã thanh toán'),
('HD-LP-2026-014', '2411222', 'G01.202', 'HD2026013', 'Tiền phòng & Dịch vụ tiện nghi 2026-2027', 11000000, '2026-08-16 15:50:00', '2026-08-19 15:50:00', 'Đã thanh toán'),
('HD-LP-2026-015', '2456333', 'G01.202', 'HD2026014', 'Tiền phòng & Dịch vụ tiện nghi 2026-2027', 11000000, '2026-08-16 16:00:00', '2026-08-19 16:00:00', 'Đã thanh toán'),
('HD-LP-2026-016', '2254011', 'H01.302', 'HD2026015', 'Tiền phòng 2 giường dịch vụ cao cấp (11 tháng)', 19250000, '2026-08-17 14:30:00', '2026-08-20 14:30:00', 'Đã thanh toán'),
('HD-LP-2026-017', '2350222', 'H01.302', 'HD2026016', 'Tiền phòng 2 giường dịch vụ cao cấp (11 tháng)', 19250000, '2026-08-17 14:45:00', '2026-08-20 14:45:00', 'Đã thanh toán'),
('HD-LP-2026-018', '2218999', 'B02.111', 'HD2026017', 'Tiền phòng năm học 2026-2027 (10 tháng)', 3000000, '2026-08-18 15:00:00', '2026-08-21 15:00:00', 'Đã thanh toán'),
('HD-LP-2026-019', '2352999', 'BA1.101', 'HD2026018', 'Tiền phòng năm học 2026-2027 (10 tháng)', 3000000, '2026-08-18 15:30:00', '2026-08-21 15:30:00', 'Đã thanh toán'),
('HD-LP-2026-020', '2416088', 'C05.305', 'HD2026019', 'Tiền phòng năm học 2026-2027 (10 tháng)', 2200000, '2026-08-19 17:30:00', '2026-08-22 17:30:00', 'Đã thanh toán'),
('HD-LP-2026-021', '2358012', 'D05.201', 'HD2026020', 'Tiền phòng năm học 2026-2027 (10 tháng)', 4500000, '2026-08-20 11:30:00', '2026-08-23 11:30:00', 'Đã thanh toán'),
('HD-LP-2026-022', '2610101', 'A01.102', NULL, 'Tiền phòng Tân sinh viên năm học 2026-2027', 4500000, '2026-10-12 15:00:00', '2026-10-15 15:00:00', 'Đã hủy'),
('HD-DN-202609-001', '2110001', 'A01.101', 'HD2026001', 'Điện nước tháng 09/2026', 480000, '2026-09-30 08:00:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-002', '2212345', 'H01.301', 'HD2026004', 'Điện nước tháng 09/2026', 845000, '2026-09-30 08:15:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-003', '2215099', 'BA1.101', 'HD2026007', 'Điện nước tháng 09/2026', 320000, '2026-09-30 08:30:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-004', '2416077', 'C05.305', 'HD2026010', 'Điện nước tháng 09/2026', 160000, '2026-09-30 08:45:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-005', '2313045', 'E04.101', 'HD2026009', 'Điện nước tháng 09/2026', 137500, '2026-09-30 09:00:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-006', '2210555', 'G01.202', 'HD2026011', 'Điện nước tháng 09/2026', 1080000, '2026-09-30 09:10:00', '2026-10-10 23:59:59', 'Đã thanh toán'),
('HD-DN-202609-007', '2254011', 'H01.302', 'HD2026015', 'Điện nước tháng 09/2026', 665000, '2026-09-30 09:20:00', '2026-10-10 23:59:59', 'Quá hạn'),
('HD-DN-202610-001', '2110001', 'A01.101', 'HD2026001', 'Điện nước tháng 10/2026', 525000, '2026-10-31 09:00:00', '2026-11-10 23:59:59', 'Chưa thanh toán'),
('HD-DN-202610-002', '2212345', 'H01.301', 'HD2026004', 'Điện nước tháng 10/2026', 1280000, '2026-10-31 09:10:00', '2026-11-10 23:59:59', 'Chưa thanh toán'),
('HD-DN-202610-003', '2215099', 'BA1.101', 'HD2026007', 'Điện nước tháng 10/2026', 355000, '2026-10-31 09:20:00', '2026-11-10 23:59:59', 'Chưa thanh toán'),
('HD-DN-202610-004', '2210555', 'G01.202', 'HD2026011', 'Điện nước tháng 10/2026', 1150000, '2026-10-31 09:30:00', '2026-11-10 23:59:59', 'Chưa thanh toán');

-- 2.19. Giao dịch thanh toán (30 giao dịch đủ loại: Thu tiền, Hoàn trả lệ phí và đủ kết quả: Thành công, Thất bại, Đang xử lý)
INSERT INTO `giaodichthanhtoan` (`MaGiaoDich`, `MaHoaDon`, `LoaiGiaoDich`, `KenhThanhToan`, `MaThamChieuNganHang`, `SoTien`, `ThoiGianGiaoDich`, `KetQuaGiaoDich`) VALUES
('GD202608001', 'HD-LP-2026-001', 'Thu tiền', 'VietQR Liên ngân hàng', 'VCB20260816091201', 4500000, '2026-08-16 09:12:01', 'Thành công'),
('GD202608002', 'HD-LP-2026-002', 'Thu tiền', 'VietQR Liên ngân hàng', 'STB20260816103022', 4500000, '2026-08-16 10:30:22', 'Thành công'),
('GD202608003', 'HD-LP-2026-003', 'Thu tiền', 'VietQR Liên ngân hàng', 'BIDV20260817081500', 4500000, '2026-08-17 08:15:00', 'Thành công'),
('GD202608004', 'HD-LP-2026-004', 'Thu tiền', 'VietQR Liên ngân hàng', 'TCB20260816154510', 8000000, '2026-08-16 15:45:10', 'Thành công'),
('GD202608005', 'HD-LP-2026-005', 'Thu tiền', 'VietQR Liên ngân hàng', 'CTG20260817112030', 8000000, '2026-08-17 11:20:30', 'Thành công'),
('GD202608006', 'HD-LP-2026-006', 'Thu tiền', 'VietQR Liên ngân hàng', 'BIDV20260818094015', 8000000, '2026-08-18 09:40:15', 'Thành công'),
('GD202608007', 'HD-LP-2026-007', 'Thu tiền', 'VietQR Liên ngân hàng', 'VCB20260819082000', 3300000, '2026-08-19 08:20:00', 'Thành công'),
('GD202608008', 'HD-LP-2026-008', 'Thu tiền', 'VietQR Liên ngân hàng', 'MBB20260819141005', 1500000, '2026-08-19 14:10:05', 'Thành công'),
('GD202608009', 'HD-LP-2026-009', 'Thu tiền', 'VietQR Liên ngân hàng', 'VBA20260820090511', 2200000, '2026-08-20 09:05:11', 'Thành công'),
('GD202608010', 'HD-LP-2026-010', 'Thu tiền', 'VietQR Liên ngân hàng', 'CTG20260820163045', 2420000, '2026-08-20 16:30:45', 'Thành công'),
('GD202608011', 'HD-LP-2026-012', 'Thu tiền', 'VietQR Liên ngân hàng', 'VCB20260817090011', 11000000, '2026-08-17 09:00:11', 'Thành công'),
('GD202608012', 'HD-LP-2026-013', 'Thu tiền', 'VietQR Liên ngân hàng', 'ACB20260817093022', 11000000, '2026-08-17 09:30:22', 'Thành công'),
('GD202608013', 'HD-LP-2026-014', 'Thu tiền', 'VietQR Liên ngân hàng', 'BIDV20260817101533', 11000000, '2026-08-17 10:15:33', 'Thành công'),
('GD202608014', 'HD-LP-2026-015', 'Thu tiền', 'VietQR Liên ngân hàng', 'MBB20260817110044', 11000000, '2026-08-17 11:00:44', 'Thành công'),
('GD202608015', 'HD-LP-2026-016', 'Thu tiền', 'VietQR Liên ngân hàng', 'TCB20260818083055', 19250000, '2026-08-18 08:30:55', 'Thành công'),
('GD202608016', 'HD-LP-2026-017', 'Thu tiền', 'VietQR Liên ngân hàng', 'CTG20260818091566', 19250000, '2026-08-18 09:15:00', 'Thành công'),
('GD202608017', 'HD-LP-2026-018', 'Thu tiền', 'VietQR Liên ngân hàng', 'STB20260819100077', 3000000, '2026-08-19 10:00:00', 'Thành công'),
('GD202608018', 'HD-LP-2026-019', 'Thu tiền', 'VietQR Liên ngân hàng', 'TPB20260819112088', 3000000, '2026-08-19 11:20:00', 'Thành công'),
('GD202608019', 'HD-LP-2026-020', 'Thu tiền', 'VietQR Liên ngân hàng', 'VBA20260820140099', 2200000, '2026-08-20 14:00:00', 'Thành công'),
('GD202608020', 'HD-LP-2026-021', 'Thu tiền', 'VietQR Liên ngân hàng', 'CTG20260821084500', 4500000, '2026-08-21 08:45:00', 'Thành công'),
('GD202610001', 'HD-DN-202609-001', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610021015', 480000, '2026-10-02 10:15:00', 'Thành công'),
('GD202610002', 'HD-DN-202609-002', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610031420', 845000, '2026-10-03 14:20:00', 'Thành công'),
('GD202610003', 'HD-DN-202609-003', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610040930', 320000, '2026-10-04 09:30:00', 'Thành công'),
('GD202610004', 'HD-DN-202609-004', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610041510', 160000, '2026-10-04 15:10:00', 'Thành công'),
('GD202610005', 'HD-DN-202609-005', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610050845', 137500, '2026-10-05 08:45:00', 'Thành công'),
('GD202610006', 'HD-LP-2026-004', 'Hoàn trả lệ phí', 'Chuyển khoản Kho bạc/Ngân hàng', 'REFUND20261020001', 1500000, '2026-10-20 11:00:00', 'Thành công'),
('GD202610007', 'HD-LP-2026-009', 'Hoàn trả lệ phí', 'Chuyển khoản Kho bạc/Ngân hàng', 'REFUND20261020002', 2200000, '2026-10-20 11:15:00', 'Thành công'),
('GD202610008', 'HD-DN-202609-006', 'Thu tiền', 'Mã QR KTX', 'QRKTX202610061600', 1080000, '2026-10-06 16:00:00', 'Thành công'),
('GD202610009', 'HD-LP-2026-020', 'Hoàn trả lệ phí', 'Chuyển khoản Kho bạc/Ngân hàng', 'REFUND20261025003', 2200000, '2026-10-25 14:30:00', 'Thành công'),
('GD202611001', 'HD-DN-202610-001', 'Thu tiền', 'Mã QR KTX', 'QRKTX202611030910', 525000, '2026-11-03 09:10:00', 'Thất bại'),
('GD202611002', 'HD-LP-2026-011', 'Thu tiền', 'VietQR Liên ngân hàng', 'VCB20261104081500', 2200000, '2026-11-04 08:15:00', 'Đang xử lý');

-- 2.20. Đơn từ Một cửa (8 đơn đủ các nghiệp vụ: Xin tạm vắng, Xin chuyển phòng, Đóng phí theo kỳ, Xin trả phòng trước hạn)
INSERT INTO `dontumotcua` (`MaDon`, `MaSinhVien`, `MaHopDong`, `LoaiDon`, `MaGiuongChuyenDen`, `TuNgay`, `DenNgay`, `LyDo`, `TepMinhChung`, `SoTienChenhLechHoan`, `TrangThaiDuyet`) VALUES
('DON2026001', '2110001', 'HD2026001', 'Xin tạm vắng', NULL, '2026-10-20', '2026-10-25', 'Về quê thăm gia đình và giải quyết việc cá nhân', NULL, 0, 'Đã duyệt'),
('DON2026002', '2412005', 'HD2026003', 'Xin chuyển phòng', 'G01201-G1', '2026-11-01', '2027-06-30', 'Xin chuyển từ phòng 4 tiêu chuẩn (A01.101) sang phòng 4 có máy lạnh (G01.201) cùng Cụm AG', NULL, 2800000, 'Chờ duyệt'),
('DON2026003', '2452088', 'HD2026008', 'Đăng ký đóng phí theo từng học kỳ', NULL, '2026-09-01', '2027-01-31', 'Gia đình gặp khó khăn đột xuất về tài chính, xin chia đóng lệ phí phòng theo 2 học kỳ', '/uploads/motcua/2452088_xacnhankhokhan.pdf', 0, 'Đã duyệt'),
('DON2026004', '2215099', 'HD2026007', 'Xin tạm vắng', NULL, '2026-11-05', '2026-11-20', 'Đi thực tập doanh nghiệp ngoài tỉnh theo lịch của Khoa', '/uploads/motcua/2215099_giaygioithieuthuctap.pdf', 0, 'Chờ duyệt'),
('DON2026005', '2352999', 'HD2026018', 'Xin tạm vắng', NULL, '2026-10-28', '2026-11-15', 'Về quê điều trị sức khỏe ngắn hạn theo chỉ định của bác sĩ', '/uploads/motcua/2352999_giaykhamvien.pdf', 0, 'Đã duyệt'),
('DON2026006', '2450112', 'HD2026005', 'Xin chuyển phòng', 'BA4201-G1', '2026-11-10', '2027-06-30', 'Xin chuyển sang Khu B để thuận tiện đi học chuyên ngành tại cơ sở mới', NULL, 2400000, 'Chờ duyệt'),
('DON2026007', '2352010', 'HD2026002', 'Xin trả phòng trước hạn', NULL, '2026-12-01', '2027-06-30', 'Xin rút hợp đồng nội trú để đi trao đổi sinh viên quốc tế 1 học kỳ', '/uploads/motcua/2352010_quyetdinhtraodoi.pdf', 2700000, 'Chờ duyệt'),
('DON2026008', '2350222', 'HD2026016', 'Đăng ký đóng phí theo từng học kỳ', NULL, '2026-09-01', '2027-01-31', 'Xin chia đóng lệ phí phòng dịch vụ 2 giường theo học kỳ', NULL, 0, 'Từ chối');

-- 2.21. Phiếu báo hỏng (Đã xóa cột SoSaoDanhGia - 12 phiếu đủ 4 trạng thái: Chờ xử lý, Đang sửa chữa, Đã hoàn thành, Đã hủy)
INSERT INTO `phieubaohong` (`MaPhieu`, `MaPhong`, `MaToaNha`, `MaThietBi`, `MaSinhVienBao`, `LoaiSuCo`, `MoTaHuHong`, `HinhAnh`, `NhanVienKyThuat`, `TrangThaiSua`, `NgayBao`) VALUES
('BH2026001', 'A01.101', 'A01', 1, '2110001', 'PHONG', 'Quạt đảo trần phát ra tiếng kêu lớn và quay rất chậm ở mức số 3', '/uploads/baohong/quat_a01101.jpg', 14, 'Đang sửa chữa', '2026-11-01 14:20:00'),
('BH2026002', 'H01.301', 'H01', 4, '2212345', 'PHONG', 'Máy lạnh bị chảy nước ở dàn lạnh xuống sàn phòng', '/uploads/baohong/maylanh_h01301.jpg', 31, 'Đã hoàn thành', '2026-10-15 09:00:00'),
('BH2026003', 'BA1.101', 'BA1', 10, '2215099', 'PHONG', 'Bóng đèn LED khu vực bàn học bị chập chờn không sáng', '/uploads/baohong/den_ba1101.jpg', 15, 'Chờ xử lý', '2026-11-02 19:30:00'),
('BH2026004', 'G01.202', 'G01', 14, '2210555', 'PHONG', 'Máy giặt bị lỗi E4 không xả nước khi đến chu trình vắt', '/uploads/baohong/maygiat_g01202.jpg', 14, 'Đang sửa chữa', '2026-11-01 16:45:00'),
('BH2026005', 'H01.302', 'H01', 8, '2254011', 'PHONG', 'Ngăn mát tủ lạnh không đủ độ lạnh, gioăng cao su cửa tủ bị hở', '/uploads/baohong/tulanh_h01302.jpg', 31, 'Chờ xử lý', '2026-11-03 08:15:00'),
('BH2026006', 'C05.305', 'C05', 17, '2416077', 'PHONG', 'Vòi sen tắm trong phòng vệ sinh bị rò rỉ nước liên tục ở khớp nối', '/uploads/baohong/voisen_c05305.jpg', 32, 'Đang sửa chữa', '2026-11-02 10:30:00'),
('BH2026007', 'D05.201', 'D05', 19, '2358012', 'PHONG', 'Ổ khóa tay gạt cửa chính bị kẹt chốt trong, đóng mở rất khó khăn', '/uploads/baohong/khoacua_d05201.jpg', NULL, 'Chờ xử lý', '2026-11-03 17:00:00'),
('BH2026008', 'E04.101', 'E04', 20, '2313045', 'PHONG', 'Nút nhấn chuông gọi hỗ trợ khẩn cấp bị lỏng chân tiếp xúc', '/uploads/baohong/chuong_e04101.jpg', 15, 'Đã hoàn thành', '2026-10-22 11:00:00'),
('BH2026009', 'A01.101', 'A01', NULL, '2352010', 'PHONG', 'Ống thoát nước sàn ban công bị nghẹt lá cây gây đọng nước khi mưa', '/uploads/baohong/bancong_a01101.jpg', 14, 'Đã hoàn thành', '2026-10-18 15:20:00'),
('BH2026010', 'H01.301', 'H01', 5, '2450112', 'PHONG', 'Đèn báo nhiệt bình nước nóng không sáng khi bật công tắc', '/uploads/baohong/binhnuocnong_h01301.jpg', 31, 'Đã hoàn thành', '2026-10-25 08:40:00'),
('BH2026011', 'BA1.101', 'BA1', 9, '2452088', 'PHONG', 'Báo nhầm quạt treo tường hỏng do lỏng phích cắm ổ điện', NULL, NULL, 'Đã hủy', '2026-10-28 20:10:00'),
('BH2026012', 'G01.202', 'G01', NULL, '2352111', 'PHONG', 'Vòi xịt bồn cầu nhà vệ sinh bị nứt dây dẫn nước', '/uploads/baohong/voixit_g01202.jpg', NULL, 'Chờ xử lý', '2026-11-04 07:45:00');

-- 2.22. Nhật ký ra vào cổng (20 lượt quét thẻ/FaceID phục vụ kiểm thử tra cứu, phát hiện đi trễ sau 23h và kiểm tra SV tạm vắng)
INSERT INTO `nhatkyravao` (`MaLuot`, `MaSinhVien`, `CongKiemSoat`, `HuongDi`, `ThoiGianQuet`) VALUES
(1, '2110001', 'Cổng chính Khu A - Tòa A01', 'Ra', '2026-11-02 07:15:20'),
(2, '2110001', 'Cổng chính Khu A - Tòa A01', 'Vào', '2026-11-02 17:45:10'),
(3, '2352010', 'Cổng chính Khu A - Tòa A01', 'Ra', '2026-11-02 07:30:05'),
(4, '2352010', 'Cổng chính Khu A - Tòa A01', 'Vào', '2026-11-02 23:25:42'),
(5, '2212345', 'Trạm FaceID Tòa H01', 'Ra', '2026-11-02 08:00:12'),
(6, '2212345', 'Trạm FaceID Tòa H01', 'Vào', '2026-11-02 19:20:00'),
(7, '2215099', 'Trạm FaceID Tòa BA1 - Khu B', 'Vào', '2026-11-02 20:05:33'),
(8, '2313045', 'Trạm kiểm soát Tòa E04 - Khu B', 'Vào', '2026-11-02 16:50:19'),
(9, '2412005', 'Cổng chính Khu A - Tòa A01', 'Ra', '2026-09-25 08:00:00'),
(10, '2210555', 'Trạm FaceID Tòa G01 - Khu A', 'Ra', '2026-11-03 06:50:00'),
(11, '2210555', 'Trạm FaceID Tòa G01 - Khu A', 'Vào', '2026-11-03 18:15:30'),
(12, '2352111', 'Trạm FaceID Tòa G01 - Khu A', 'Ra', '2026-11-03 07:40:11'),
(13, '2352111', 'Trạm FaceID Tòa G01 - Khu A', 'Vào', '2026-11-03 21:10:05'),
(14, '2254011', 'Trạm FaceID Tòa H01', 'Ra', '2026-11-03 08:30:00'),
(15, '2254011', 'Trạm FaceID Tòa H01', 'Vào', '2026-11-03 20:45:19'),
(16, '2416077', 'Trạm kiểm soát Tòa C05 - Khu B', 'Ra', '2026-11-03 07:05:44'),
(17, '2416077', 'Trạm kiểm soát Tòa C05 - Khu B', 'Vào', '2026-11-03 17:30:22'),
(18, '2416088', 'Trạm kiểm soát Tòa C05 - Khu B', 'Ra', '2026-11-03 07:06:01'),
(19, '2416088', 'Trạm kiểm soát Tòa C05 - Khu B', 'Vào', '2026-11-03 19:55:10'),
(20, '2352999', 'Trạm FaceID Tòa BA1 - Khu B', 'Ra', '2026-10-28 08:00:00');

-- 2.23. Rèn luyện nội trú (12 bản ghi đủ 4 loại ghi nhận: Hoạt động, Khen thưởng, Vi phạm, Kỷ luật và các mức xếp loại)
INSERT INTO `renluyennoitru` (`MaDanhGia`, `MaSinhVien`, `NamHoc`, `HocKy`, `LoaiGhiNhan`, `NoiDung`, `DiemThayDoi`, `TongDiemCuoiKy`, `XepLoai`, `DaGuiVeTruong`) VALUES
(1, '2110001', '2026-2027', 1, 'Hoạt động', 'Tham gia Chủ nhật xanh vệ sinh khu vực Cụm nhà A.G và hỗ trợ đón Tân sinh viên', 10, 92, 'Xuất sắc', 0),
(2, '2212345', '2026-2027', 1, 'Hoạt động', 'Tham gia hiến máu tình nguyện và hỗ trợ Bếp ăn chia sẻ sinh viên tại nhà C05', 10, 95, 'Xuất sắc', 0),
(3, '2352010', '2026-2027', 1, 'Vi phạm', 'Làm ồn sau 23h00 giờ giới nghiêm tại phòng A01.101 và về trễ quá giờ quy định (Nhắc nhở lần 1)', -5, 78, 'Khá', 0),
(4, '2215099', '2026-2027', 1, 'Hoạt động', 'Tham gia đội tự quản an ninh trật tự và diễn tập PCCC Cụm nhà B.A', 10, 88, 'Tốt', 0),
(5, '2210555', '2026-2027', 1, 'Khen thưởng', 'Đạt giải Nhất hội thao sinh viên nội trú KTX ĐHQG-HCM và giữ gìn phòng ở kiểu mẫu', 15, 96, 'Xuất sắc', 0),
(6, '2313045', '2026-2027', 1, 'Khen thưởng', 'Tấm gương sinh viên vượt khó học giỏi, tích cực hỗ trợ Ban quản lý Cụm nhà B.E', 15, 94, 'Xuất sắc', 0),
(7, '2416077', '2026-2027', 1, 'Hoạt động', 'Tham gia đội tình nguyện sơ cấp cứu y tế học đường tại Cụm nhà B.C', 10, 89, 'Tốt', 0),
(8, '2350222', '2026-2027', 1, 'Vi phạm', 'Chậm nộp biểu mẫu xác nhận thông tin cư trú CT01 và đóng tiền điện nước trễ hạn', -10, 68, 'Trung bình', 0),
(9, '2412005', '2026-2027', 1, 'Vi phạm', 'Nấu ăn bằng bếp điện từ công suất lớn không đúng quy định trong phòng tiêu chuẩn', -15, 62, 'Trung bình', 0),
(10, '2218999', '2026-2027', 1, 'Kỷ luật', 'Đưa người lạ không đăng ký vào lưu trú trái phép qua đêm và tái phạm nội quy an ninh KTX (Buộc chấm dứt hợp đồng)', -40, 35, 'Kém', 1),
(11, '2114888', '2025-2026', 2, 'Hoạt động', 'Hoàn thành tốt nghĩa vụ sinh viên nội trú và bàn giao phòng đúng hạn năm học 2025-2026', 10, 86, 'Tốt', 1),
(12, '2110001', '2025-2026', 2, 'Khen thưởng', 'Trưởng phòng gương mẫu, phòng A01.101 đạt danh hiệu Phòng ở Văn hóa - Sạch đẹp cấp Khu A', 15, 93, 'Xuất sắc', 1);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;