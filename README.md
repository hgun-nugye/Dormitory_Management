# HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ ĐẠI HỌC QUỐC GIA TP. HỒ CHÍ MINH (KTX ĐHQG-HCM)

Hệ thống Quản lý Ký túc xá Đại học Quốc gia Thành phố Hồ Chí Minh là giải pháp tích hợp cơ sở dữ liệu và quy trình nghiệp vụ nhằm tự động hóa công tác điều hành, quản lý lưu trú sinh viên, an ninh ra vào, tính toán thu phí dịch vụ và theo dõi rèn luyện tại Khu A và Khu B.

---

## 1. THÔNG TIN KỸ THUẬT

- **Hệ quản trị CSDL:** MySQL 8.0+ / MariaDB
- **Storage Engine:** InnoDB
- **Bảng mã ký tự (Charset & Collation):** `utf8mb4` / `utf8mb4_unicode_ci`
- **Mức độ chuẩn hóa:** Chuẩn hóa dạng 3NF (Third Normal Form)
- **Tệp kịch bản CSDL:** `qlktx_dhqg_hcm.sql` (Bao gồm cấu trúc 17 bảng và dữ liệu mẫu thực tế)
- **Đơn vị vận hành:** Trung tâm Quản lý Ký túc xá Đại học Quốc gia TP.HCM (TTQLKTX)

---

## 2. PHÂN HỆ VÀ MÔ TẢ CÔNG VIỆC CỦA CÁC BỘ PHẬN

Hệ thống phân công và phân quyền công việc rõ ràng cho từng bộ phận nghiệp vụ:

### 2.1. Cán bộ Hành chính - Công tác Sinh viên (Bảng `NV_HANHCHINH`)
- Tiếp nhận, xét duyệt hồ sơ kê khai trực tuyến từ cổng dịch vụ.
- Kiểm tra giấy tờ minh chứng để duyệt các mức miễn giảm lệ phí theo quy định.
- Quản lý việc lập, duyệt hợp đồng lưu trú và cấp thẻ nội trú hoặc phê duyệt tạm trú.

### 2.2. Ban Quản lý Cụm nhà (Bảng `BAN_QUAN_LY_CUM_NHA`, `PHONG_O`)
- Quản lý danh mục các tòa nhà, tầng số và từng phòng ở KTX.
- Quản lý phân bổ số lượng sinh viên thực tế so với sức chứa tối đa của phòng.
- Chỉ định Trưởng phòng sinh viên đại diện hỗ trợ quản lý sinh hoạt nội bộ phòng.

### 2.3. Lực lượng Bảo vệ - An ninh (Bảng `NV_BAOVE`)
- Vận hành và giám sát cổng kiểm soát ra vào tích hợp nhận diện khuôn mặt (`ANH_FACEID`).
- Phân công ca trực (Sáng, Chiều, Đêm) chịu trách nhiệm an ninh tại các cụm nhà được giao.
- Nắm bắt thông tin liên hệ thân nhân khi có sự cố khẩn cấp.

### 2.4. Bộ phận Kế toán - Thu ngân (Bảng `HOADON`, `THANH_TOAN`)
- Chốt chỉ số điện, nước định kỳ hàng tháng theo từng phòng.
- Tính toán tiền điện, tiền nước căn cứ theo định mức và phát hành hóa đơn phòng.
- Theo dõi nhật ký giao dịch thanh toán trực tuyến và thực hiện gạch nợ tự động.

### 2.5. Ban Tổ chức Hoạt động Phong trào (Bảng `HOATDONG_RENLUYEN`, `SV_THAMGIAM_HOATDONG`)
- Lập kế hoạch và tổ chức các hoạt động văn hóa, rèn luyện, tập huấn PCCC, tình nguyện.
- Ghi nhận việc tham gia và chấm điểm rèn luyện làm căn cứ ưu tiên duyệt lưu trú các năm tiếp theo.

---

## 3. QUY TRÌNH NGHIỆP VỤ VẬN HÀNH

### 3.1. Quy trình Đăng ký chỗ ở & Hợp đồng (Quy tắc 72 Giờ)
1. **Kê khai hồ sơ:** Sinh viên truy cập cổng dịch vụ, khai báo thông tin cá nhân (MSSV, CCCD, BHYT/VssID, tải ảnh FaceID) và xác thực mã OTP.
2. **Chọn phòng & Chính sách:** Chọn cụm nhà, loại phòng ở (2, 4, 6, 8 chỗ; loại tiêu chuẩn hoặc máy lạnh) và đính kèm minh chứng miễn giảm (nếu có).
3. **Xét duyệt & Giữ chỗ:** Hệ thống đối soát thông tin trường ĐH, kiểm tra sức chứa khả dụng (`SO_SV_HIENTAI < SUC_CHUA`) và tạo hợp đồng ở trạng thái `Chờ duyệt thanh toán`.
4. **Hạn mức 72 giờ:** Sinh viên hoàn tất thanh toán tiền lệ phí phòng và tiền thế chân trong vòng 72 giờ. Nếu quá thời hạn, hệ thống tự động hủy hợp đồng và thu hồi chỗ trống.
5. **Nhận phòng:** Sinh viên đối soát hồ sơ gốc tại BQL Cụm nhà, xác minh khuôn mặt trực tiếp, nhận bàn giao tài sản và nhận Thẻ nội trú.

### 3.2. Quy trình Quản lý Lưu trú & Thân nhân
- **Nội trú (`SV_NOITRU`):** Áp dụng cho sinh viên chính quy ở dài hạn theo năm học, được cấp số thẻ nội trú riêng.
- **Tạm trú (`SV_TAMTRU`):** Áp dụng cho sinh viên ở ngắn hạn (thực tập, tham gia đề tài nghiên cứu khoa học, học hè) với thời hạn ngày bắt đầu và kết thúc cụ thể.
- **Thân nhân (`THANNHAN`):** Sinh viên bắt buộc lưu ít nhất một người thân (Cha, Mẹ, Người giám hộ) gồm tên, quan hệ, số điện thoại và địa chỉ để xử lý các sự cố khẩn cấp.

### 3.3. Chính sách Ưu đãi Miễn giảm Lệ phí
- **MG00 (0%):** Không thuộc diện miễn giảm.
- **MG30 (Giảm 30%):** Con thương binh, bệnh binh; sinh viên vùng sâu vùng xa / xã đặc biệt khó khăn.
- **MG50 (Giảm 50%):** Sinh viên hộ nghèo, hộ cận nghèo theo chuẩn quốc gia.
- **MG100 (Miễn 100%):** Sinh viên khuyết tật đặc biệt nặng; mồ côi cả cha lẫn mẹ.

### 3.4. Quy trình Tính Điện - Nước & Phát hành Hóa đơn
- **Chốt số đo:** Cuối tháng, nhân viên ghi nhận chỉ số điện đầu/cuối (`CHISO_DIEN_DAU`, `CHISO_DIEN_CUOI`) và chỉ số nước đầu/cuối (`CHISO_NUOC_DAU`, `CHISO_NUOC_CUOI`).
- **Tính tiền:** Áp dụng công thức trừ số tiêu thụ, đối chiếu định mức quy định (`DINH_MUC_DIEN`, `DINH_MUC_NUOC`) để tính thành tiền điện và tiền nước.
- **Phát hành:** Tự động tạo bản ghi `HOADON` và gửi thông báo cước đến phòng ở.

### 3.5. Quy trình Thanh toán Trực tuyến Đa kênh
Tích hợp đối soát tự động qua 5 phương thức trực tuyến:
1. **Mã QR KTX:** Tự động sinh mã kèm số tiền và mã hóa đơn/hợp đồng.
2. **App Ngân hàng BIDV:** Thanh toán dịch vụ KTX ĐHQG trực tiếp qua ứng dụng ngân hàng.
3. **Vietcombank (VCB Digibank):** Nhận kết quả thanh toán tự động qua Webhook.
4. **Cổng VNPAY Gateway:** Hỗ trợ thanh toán qua thẻ ngân hàng nội địa, Visa/Mastercard hoặc ví VNPAY.
5. **Chuyển khoản định danh CCCD:** Chuyển khoản với cú pháp định danh CCCD/MSSV, hệ thống tự động đối soát.

---

## 4. CẤU TRÚC DỮ LIỆU (17 BẢNG THỰC THỂ)

Hệ thống được chia thành 5 khối thực thể chính:

| Khối chức năng | Danh sách Bảng | Ý nghĩa và Vai trò |
| :--- | :--- | :--- |
| **Cơ sở & Đào tạo** | `TRUONG`, `BAN_QUAN_LY_CUM_NHA`, `PHONG_O` | Quản lý danh mục các trường ĐH thành viên, các BQL Cụm nhà Khu A/B và sơ đồ phòng ở. |
| **Sinh viên & Thân nhân** | `SINHVIEN`, `SV_NOITRU`, `SV_TAMTRU`, `THANNHAN` | Lưu trữ hồ sơ sinh viên, nhận diện FaceID, thông tin thẻ nội trú, lý do tạm trú và liên hệ người thân. |
| **Thủ tục & Hợp đồng** | `DIEN_MIEN_GIAM`, `DANGKY_HOPDONG` | Quản lý chính sách miễn giảm lệ phí, hợp đồng thuê phòng kèm mã xác thực OTP. |
| **Nhân sự & Phân công** | `CHUCVU`, `NHANVIEN`, `NV_HANHCHINH`, `NV_BAOVE` | Quản lý cán bộ BQL, nhân viên hành chính, nhân viên bảo vệ và ca trực an ninh. |
| **Tài chính & Rèn luyện** | `HOADON`, `THANH_TOAN`, `HOATDONG_RENLUYEN`, `SV_THAMGIAM_HOATDONG` | Theo dõi hóa đơn điện nước hàng tháng, nhật ký thanh toán trực tuyến và điểm rèn luyện ngoại khóa. |

---

## 5. HƯỚNG DẪN CÀI ĐẶT & SỬ DỤNG CSDL TRÊN PHPMYADMIN

### 5.1. Yêu cầu môi trường
* Đã cài đặt môi trường máy chủ cục bộ (XAMPP, WampServer, Laragon hoặc Docker) tích hợp sẵn dịch vụ MySQL/MariaDB và phpMyAdmin.
* Đảm bảo Apache (hoặc Nginx) và MySQL đã được khởi động (`Start`).

---

### 5.2. Các bước Import Cơ sở dữ liệu và Dữ liệu mẫu (qlktx_dhqg_hcm.sql)

#### Cách 1: Sử dụng tính năng Import trực tiếp giao diện
1. **Mở phpMyAdmin:** Truy cập trình duyệt web qua địa chỉ `http://localhost/phpmyadmin`.
2. **Khởi tạo cơ sở dữ liệu (Database):**
   * Tại thanh menu chính bên trái hoặc tab phía trên, nhấn vào **New** (hoặc tab **Databases**).
   * Tại ô **Database name**, nhập chính xác tên: `qlktx_dhqg_hcm`.
   * Tại ô **Collation**, chọn: `utf8mb4_unicode_ci`.
   * Nhấn nút **Create** để hoàn tất tạo database rỗng.
3. **Thực hiện nạp dữ liệu (Import):**
   * Nhấp chọn cơ sở dữ liệu `qlktx_dhqg_hcm` vừa tạo ở danh sách bên cột trái.
   * Nhấp vào tab **Import** (Nhập) trên thanh menu công cụ bên trên.
   * Tại mục **File to import**, nhấn **Choose File** (Chọn tệp) và tìm trỏ đến tệp `qlktx_dhqg_hcm.sql` trên máy tính.
   * Tại mục **Character set of the file**, giữ nguyên `utf-8` (hoặc `utf8mb4`).
   * Kéo xuống cuối trang và nhấn nút **Import** (hoặc **Go** / Thực hiện).
4. **Kiểm tra kết quả:**
   * Hệ thống hiển thị thông báo màu xanh: *"Import has been successfully finished..."*.
   * Kiểm tra cột bên trái, xác nhận hiển thị đầy đủ danh sách **17 bảng thực thể** (từ `BAN_QUAN_LY_CUM_NHA`, `CHUCVU`, `SINHVIEN` đến `THANH_TOAN`...).

---

#### Cách 2: Thực thi qua Tab SQL (Áp dụng khi tệp dung lượng vừa phải hoặc bị giới hạn upload)
1. Mở tệp `qlktx_dhqg_hcm.sql` bằng một trình soạn thảo văn bản (Notepad, VS Code, Sublime Text...) và sao chép toàn bộ nội dung mã nguồn.
2. Truy cập `http://localhost/phpmyadmin`.
3. Nhấp trực tiếp vào tab **SQL** trên thanh menu chính của phpMyAdmin.
4. Dán toàn bộ nội dung vừa sao chép vào khung nhập lệnh.
5. Nhấn nút **Go** (Thực hiện) ở góc dưới bên phải. Hệ thống sẽ tự động tạo cơ sở dữ liệu `qlktx_dhqg_hcm`, dựng cấu trúc 17 bảng và nạp toàn bộ bộ dữ liệu mẫu thực tế.

---

### 5.3. Tạo các Chỉ mục tối ưu hóa truy vấn
Sau khi đã import thành công, để tối ưu tốc độ tra cứu khi hệ thống vận hành nhiều bản ghi, hãy thực hiện nạp thêm các chỉ mục:

1. Nhấp chọn cơ sở dữ liệu `qlktx_dhqg_hcm` ở danh sách bên trái.
2. Chuyển sang tab **SQL** trên thanh menu và dán đoạn lệnh sau:

```sql
-- 1. Tối ưu tìm kiếm sinh viên theo CCCD và Trường đại học
CREATE INDEX idx_sv_cccd ON SINHVIEN(CCCD);
CREATE INDEX idx_sv_truong ON SINHVIEN(MSTRG);

-- 2. Tối ưu tra cứu hóa đơn điện nước theo phòng và kỳ tính tiền
CREATE INDEX idx_hoadon_phong_thang ON HOADON(MAPHG, THANG, NAM);

-- 3. Tối ưu tra cứu và đối soát giao dịch ngân hàng
CREATE INDEX idx_thanhtoan_bank ON THANH_TOAN(MA_GIAO_DICH_BANK);
