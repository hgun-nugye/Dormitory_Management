# HỆ THỐNG QUẢN LÝ KÝ TÚC XÁ ĐẠI HỌC QUỐC GIA TP. HỒ CHÍ MINH (KTX ĐHQG-HCM)

Dự án phần mềm mã nguồn mở PHP xây dựng theo kiến trúc **MVC (Model - View - Controller)** chuẩn hóa **PSR-4**, phục vụ công tác điều hành, quản lý lưu trú sinh viên, xét duyệt giữ chỗ theo hạn định 72h, dồn phòng tối ưu, tính toán hóa đơn điện nước bậc thang và giám sát an ninh tại Ký túc xá Khu A và Khu B ĐHQG-HCM.

---

## 1. THÔNG TIN KỸ THUẬT & KIẾN TRÚC HỆ THỐNG

- **Ngôn ngữ & Phiên bản:** PHP >= 8.0 (Khuyến nghị PHP 8.2)
- **Kiến trúc phần mềm:** MVC hướng đối tượng (OOP), chuẩn nạp tự động PSR-4 (`App\` $\rightarrow$ `app/`)
- **Hệ quản trị CSDL:** MySQL 8.0+ / MariaDB (Storage Engine: InnoDB, Charset: `utf8mb4_unicode_ci`)
- **Mức độ chuẩn hóa CSDL:** Dạng chuẩn 3NF (Third Normal Form) gồm 22 bảng thực thể
- **Môi trường máy chủ máy khách:** Apache Web Server (XAMPP / WampServer / Laragon / Docker)
- **Cơ chế định tuyến (Routing):** Clean URL Routing với Front Controller pattern (`public/index.php`) kết hợp `.htaccess`

---

## 2. CẤU TRÚC THƯ MỤC DỰ ÁN

```
Dormitory_Management/
│
├── .env                              # Cấu hình môi trường (DB, URL, Mail, Cron)
├── .env.example                      # Mẫu biến môi trường
├── .gitignore                        # Loại trừ tệp rác, vendor và logs
├── .htaccess                         # Điều hướng Apache từ thư mục gốc vào public/
├── composer.json                     # Định nghĩa autoload PSR-4
├── README.md                         # Tài liệu kỹ thuật dự án
│
├── app/                              # MÃ NGUỒN CHÍNH (Namespace: App\)
│   ├── Core/                         # Nhân hệ thống cốt lõi
│   │   ├── Database.php              # PDO Singleton, Transaction & SELECT FOR UPDATE
│   │   ├── Router.php                # Bộ định tuyến Clean URL & Middleware
│   │   ├── Controller.php            # Base Controller
│   │   ├── Model.php                 # Base Model
│   │   ├── Request.php               # Bóc tách URI & xử lý HTTP Request
│   │   ├── Response.php              # Xử lý HTTP Response
│   │   ├── Session.php               # Quản lý Session & Flash message
│   │   └── Auth.php                  # Xác thực & phân quyền 3 vai trò
│   │
│   ├── Services/                     # TẦNG NGHIỆP VỤ 8 CHỨC NĂNG CỐT LÕI
│   │   ├── BedLockService.php        # [CN 1] Khóa giường tức thời & kiểm soát điều kiện
│   │   ├── AutoReleaseService.php    # [CN 2] Duyệt hồ sơ 24h & Thu hồi giường sau 72h
│   │   ├── RoomConsolidationService.php # [CN 3] Thuật toán dồn phòng tối ưu công suất
│   │   ├── RoomTransferService.php   # [CN 4] Chuyển phòng & bảng tính bù trừ chênh lệch
│   │   ├── ExemptionRefundService.php # [CN 5] Xét miễn giảm trần & hoàn phí 7 ngày
│   │   ├── UtilityBillingService.php # [CN 6] Điện nước bậc thang & cảnh báo bất thường
│   │   ├── MaintenanceService.php    # [CN 7] Điều phối bảo trì chống trùng phiếu
│   │   └── AttendanceDisciplineService.php # [CN 8] Giám sát vắng 30 ngày & Báo cáo ĐRL
│   │
│   ├── Controllers/                  # Tầng điều khiển theo phân hệ
│   │   ├── AuthController.php        # Đăng nhập (MSSV/Mã NV), Đăng xuất
│   │   ├── HomeController.php        # Trang chủ giới thiệu KTX & thống kê
│   │   ├── Student/                  # Phân hệ Cổng Sinh Viên
│   │   │   └── DashboardController.php
│   │   ├── Staff/                    # Phân hệ BQL Cụm Nhà
│   │   │   └── DashboardController.php
│   │   ├── Admin/                    # Phân hệ Quản Trị Hệ Thống
│   │   │   └── DashboardController.php
│   │   └── Api/                      # API endpoint tương tác Ajax
│   │
│   ├── Models/                       # Các thực thể dữ liệu ORM
│   ├── Middlewares/                  # Bộ lọc trung gian (Auth, Role, Csrf)
│   └── Helpers/
│       └── functions.php             # Bộ hàm tiện ích: view(), url(), asset(), format_money()...
│
├── config/                           # Cấu hình kết nối
│   ├── app.php                       # Cấu hình chung, múi giờ, quy định 72h/30 ngày
│   └── database.php                  # Cấu hình CSDL MySQL
│
├── cron/                             # Tiến trình ngầm chạy độc lập (CLI Cron Job)
│   ├── cron_auto_release_72h.php     # Chạy mỗi 15 phút: thu hồi giường quá hạn thanh toán
│   └── cron_check_absence_30d.php    # Chạy hàng đêm: quét sinh viên vắng mặt quá 30 ngày
│
├── database/
│   └── qlktx_dhqg_hcm.sql            # Kịch bản tạo 22 bảng và nạp dữ liệu mẫu
│
├── docs/                             # Tài liệu trích xuất kỹ thuật và đặc tả
├── public/                           # Web Root an toàn
│   ├── index.php                     # Front Controller tiếp nhận mọi request
│   ├── .htaccess                     # Rewrite URL chuẩn Apache
│   └── assets/
│       └── css/style.css             # Giao diện tối giản, chuẩn học thuật
│
├── storage/                          # Lưu trữ tệp phát sinh (logs, uploads)
└── views/                            # Tầng hiển thị giao diện (View Templates)
    ├── layouts/main.php              # Bố cục giao diện chung
    ├── auth/login.php                # Giao diện đăng nhập
    ├── home.php                      # Trang chủ giới thiệu hệ thống
    ├── student/                      # Giao diện sinh viên
    ├── staff/                        # Giao diện BQL Cụm
    └── admin/                        # Giao diện Quản trị viên
```

---

## 3. DANH MỤC 8 NGHIỆP VỤ CỐT LÕI CẦN THỰC HIỆN

Hệ thống được thiết kế giải quyết trực tiếp 8 bài toán nghiệp vụ đặc thù của Ký túc xá ĐHQG-HCM:

### 3.1. Kiểm soát điều kiện lưu trú & Chống xung đột đặt giường (Concurrency Engine)
- **Ràng buộc điều kiện tự động:**
  - Tự động lọc danh sách phòng/giường theo đúng giới tính sinh viên (sinh viên nam không thể xem/đặt giường thuộc tòa/tầng nữ).
  - Tự động kiểm tra thời hạn đào tạo chuẩn: so khớp `KhoaTuyenSinh` của sinh viên và `SoNamDaoTaoChuan` của trường đại học. Nếu sinh viên đã học quá thời gian chuẩn, hệ thống bắt buộc tải lên tệp minh chứng *"Giấy xác nhận còn đang học tại trường"* mới cho phép gửi đơn.
- **Khóa giường tức thời (Real-time Slot Locking):**
  - Vào ngày mở cổng đăng ký, hệ thống áp dụng cơ chế Database Transaction kết hợp khóa dòng `SELECT ... FOR UPDATE` để chuyển trạng thái giường sang `Đang giữ chỗ`, ngăn chặn race condition khi nhiều sinh viên cùng bấm đặt một giường ở cùng mili-giây.
- **Tệp xử lý:** `app/Services/BedLockService.php`

### 3.2. Duyệt hồ sơ 24h & Tiến trình ngầm tự động thu hồi chỗ sau 72h (Background Auto-Release)
- **Quy trình duyệt và phát sinh hạn chót:** Cán bộ Cụm nhà bấm duyệt hồ sơ $\rightarrow$ hệ thống tự động cộng thêm 72 giờ (03 ngày) tạo mốc `HanChotThanhToan`, đồng thời sinh hóa đơn lệ phí kèm mã thanh toán VietQR định danh.
- **Dịch vụ chạy ngầm định kỳ (Background Worker / Cron Job):** Định kỳ mỗi 15 phút, tiến trình quét toàn bộ các hồ sơ có trạng thái `Chờ thanh toán` nhưng thời gian hiện tại đã vượt quá `HanChotThanhToan`:
  - Đổi trạng thái hồ sơ sang `Hết hạn - Đã hủy chỗ`.
  - Hoàn trả trạng thái giường về `Trống` để sinh viên khác tiếp tục đăng ký.
  - Hủy hóa đơn lệ phí chưa thanh toán tương ứng.
- **Tệp xử lý:** `app/Services/AutoReleaseService.php`, `cron/cron_auto_release_72h.php`

### 3.3. Nghiệp vụ Dồn phòng & Tối ưu công suất lưu trú (Room Consolidation)
- **Bài toán thực tế:** Vào giữa năm học hoặc khi chuẩn bị sửa chữa cuốn chiếu, nhiều phòng chỉ có 2–3 sinh viên ở trên sức chứa 8 người ($\le 50\%$) gây lãng phí điện nước và khó bảo trì.
- **Chức năng đề xuất ghép phòng thông minh:**
  - Tự động thống kê và xếp hạng các phòng có tỷ lệ lấp đầy thấp ($\le 50\%$).
  - Tự động tìm kiếm các phòng đích còn chỗ trống cùng Cụm nhà, cùng Loại phòng, cùng Giới tính để đề xuất phương án điều chuyển nhóm sinh viên sang phòng đích, giải phóng nguyên phòng trống bàn giao sửa chữa.
- **Tệp xử lý:** `app/Services/RoomConsolidationService.php`

### 3.4. Nghiệp vụ Chuyển phòng & Tự động bù trừ chênh lệch chi phí (Room Transfer)
- **Tính toán tài chính tự động khi chuyển phòng:**
  - Tự động xác định số tháng còn lại của hợp đồng nội trú hiện tại.
  - Lập bảng tính chênh lệch giữa đơn giá phòng mới (Đơn giá lưu trú + Phí dịch vụ) và đơn giá phòng cũ.
  - Nếu chuyển sang phòng có đơn giá cao hơn: Tự động sinh hóa đơn thu bổ sung chênh lệch có hạn định 72h.
- **Quy trình bàn giao:** Hợp đồng chỉ được cập nhật sang mã giường mới khi sinh viên thanh toán xong tiền chênh lệch và Cán bộ Cụm nhà kiểm tra, xác nhận bàn giao cơ sở vật chất phòng cũ không hư hại.
- **Tệp xử lý:** `app/Services/RoomTransferService.php`

### 3.5. Nghiệp vụ Xét miễn giảm theo trần & Hoàn phí trả phòng trước hạn
- **Thuật toán tính miễn giảm theo trần:** Sinh viên diện chính sách (khuyết tật miễn 100%; mồ côi, hộ nghèo, cận nghèo giảm 50%) được tính số tiền hoàn trả căn cứ theo đơn giá trần của phòng tiêu chuẩn 6/8 giường (350.000đ/tháng). Nếu sinh viên chọn ở phòng dịch vụ giá cao hơn thì phần dịch vụ tăng thêm tự chi trả (trừ trường hợp khuyết tật).
- **Kiểm tra quy tắc hoàn phí khi trả phòng sớm:**
  - Tự ý bỏ KTX: Đánh dấu không hoàn trả lệ phí.
  - Có lý do chính đáng (tốt nghiệp sớm, bảo lưu, du học) nộp hồ sơ trong vòng 07 ngày kể từ ngày trường ra quyết định: Tự động tính số tháng thực tế chưa ở để lập phiếu hoàn trả tiền về tài khoản ngân hàng của sinh viên.
- **Tệp xử lý:** `app/Services/ExemptionRefundService.php`

### 3.6. Tính tiền Điện – Nước bậc thang & Kiểm tra bất thường khi nhập liệu (Utility Anomaly Detection)
- **Kiểm tra dữ liệu nhập hàng loạt (Excel/Form):**
  - Cảnh báo đỏ nếu Chỉ số mới < Chỉ số cũ (nghi ngờ sai sót nhập liệu hoặc đồng hồ quay vòng).
  - Cảnh báo đỏ nếu Sản lượng tiêu thụ tăng đột biến $> 300\%$ trung bình 3 tháng liền kề của chính phòng đó (phát hiện chập điện hoặc rò rỉ đường ống nước).
- **Tính định mức và lũy tiến:** Áp dụng biểu giá điện 6 bậc thang lũy tiến của EVN, tính tổng tiền điện và tiền nước, sau đó chia đều theo số lượng sinh viên thực tế ở trong tháng tại phòng.
- **Tệp xử lý:** `app/Services/UtilityBillingService.php`

### 3.7. Điều phối bảo trì & Chống trùng lặp phiếu (Smart Maintenance Deduplication)
- **Cơ chế chống trùng phiếu:** Một phòng có nhiều sinh viên cùng ở; khi thiết bị trong phòng (bóng đèn, quạt trần, máy lạnh) gặp sự cố, nhiều bạn thường cùng tạo phiếu báo hỏng.
- **Giải pháp:** Hệ thống kiểm tra xem thiết bị đó trong phòng hiện có phiếu nào đang ở trạng thái `Chờ tiếp nhận`, `Chờ xử lý` hoặc `Đang sửa chữa` hay không. Nếu đã có, hệ thống hiển thị thông tin bạn cùng phòng đã gửi phiếu và cho phép bấm *"Theo dõi cùng phiếu"* thay vì tạo phiếu mới gây rối danh sách của nhân viên kỹ thuật.
- **Tệp xử lý:** `app/Services/MaintenanceService.php`

### 3.8. Giám sát vắng mặt 30 ngày liên tục & Tổng hợp điểm rèn luyện gửi các Trường
- **Tự động phát hiện sinh viên vắng mặt 30 ngày:**
  - Theo quy chế KTX ĐHQG-HCM: sinh viên vắng mặt quá 30 ngày liên tục không phép sẽ bị xem xét xóa tên.
  - Hệ thống tự động đối chiếu bảng `NhatKyRaVao` (lịch sử quét thẻ cổng) và bảng `DonTuMotCua` (đơn xin tạm vắng đã duyệt). Nếu không có lượt quét thẻ nào trong 30 ngày và không có đơn xin phép, hệ thống đưa vào danh sách cảnh báo đỏ cho Trưởng Cụm để lập biên bản xóa tên và báo cáo trường.
- **Kết xuất Điểm rèn luyện:** Cuối mỗi học kỳ, hệ thống tổng hợp điểm rèn luyện tại KTX (điểm hoạt động, điểm trừ vi phạm), tự động phân nhóm theo từng trường thành viên (`TruongDaiHoc`) để xuất tệp báo cáo chuẩn gửi về trường.
- **Tệp xử lý:** `app/Services/AttendanceDisciplineService.php`, `cron/cron_check_absence_30d.php`

---

## 4. CHI TIẾT CÁC PHÂN HỆ CHỨC NĂNG THEO NGƯỜI DÙNG

### 4.1. Phân hệ Cổng Dịch vụ Sinh viên (Student Portal)
- Đăng nhập bảo mật theo MSSV và mật khẩu mã hóa.
- Đăng ký nội trú đợt mới: xem sơ đồ phòng/giường trống hợp lệ theo giới tính, chọn vị trí giường, tải lên minh chứng học tập/diện ưu đãi.
- Quản lý hợp đồng nội trú, xem thông tin phòng ở, danh sách bạn cùng phòng.
- Xem danh sách hóa đơn và lịch sử thanh toán; quét mã VietQR ngân hàng.
- Nộp đơn thủ tục Một Cửa: xin chuyển phòng, xin miễn giảm, xin tạm vắng, xin trả phòng trước hạn.
- Báo hỏng thiết bị phòng có kiểm tra chống trùng lặp, theo dõi tiến độ sửa chữa, chấm sao đánh giá sau sửa.
- Theo dõi nhật ký ra vào cổng an ninh và điểm rèn luyện nội trú từng kỳ.

### 4.2. Phân hệ Ban Quản lý Cụm nhà (Staff Portal)
- Tiếp nhận và xét duyệt hồ sơ đăng ký giữ chỗ của sinh viên trong cụm; thiết lập hạn thanh toán 72h.
- Quản lý danh mục tòa nhà, tầng, phòng ở, số lượng sinh viên đang ở thực tế.
- Bàn giao phòng, đối soát hồ sơ gốc và kích hoạt hợp đồng nội trú khi sinh viên đến nhận phòng.
- Màn hình theo dõi các phòng có tỷ lệ lấp đầy $\le 50\%$ và áp dụng đề xuất dồn phòng.
- Phê duyệt / từ chối các loại đơn từ Một Cửa (chuyển phòng, tạm vắng, miễn giảm, trả phòng).
- Tiếp nhận danh sách cảnh báo sinh viên vắng mặt 30 ngày không phép $\rightarrow$ xử lý biên bản vi phạm.

### 4.3. Phân hệ Kế toán & Quản lý Điện Nước (Billing & Accounting)
- Nhập chỉ số công tơ điện nước định kỳ hàng tháng (hỗ trợ nhập theo phòng hoặc import file Excel).
- Hệ thống tự động kiểm tra phát hiện dữ liệu bất thường và phát hành hóa đơn phòng theo biểu giá bậc thang.
- Theo dõi nhật ký giao dịch ngân hàng/VietQR và thực hiện gạch nợ tự động trên hóa đơn.

### 4.4. Phân hệ Kỹ thuật & Bảo trì (Maintenance)
- Tiếp nhận danh sách phiếu báo hỏng thiết bị đã qua bộ lọc chống trùng.
- Cập nhật tiến độ xử lý (`Chờ tiếp nhận` $\rightarrow$ `Đang sửa chữa` $\rightarrow$ `Hoàn thành`).
- Cập nhật tình trạng thiết bị sau sửa chữa (`Tốt`, `Hỏng`, `Đã thay mới`).

### 4.5. Phân hệ Quản trị viên Hệ thống (Admin Portal)
- Quản lý danh mục dùng chung: Trường đại học thành viên, Khu KTX, Cụm nhà, Tòa nhà, Loại phòng.
- Thiết lập các đợt mở cổng đăng ký (`DotDangKy`) với thời gian đóng/mở và số giờ giữ chỗ.
- Quản lý tài khoản cán bộ nhân viên, sinh viên và phân quyền phụ trách theo cụm nhà.
- Thống kê toàn diện: tổng doanh thu, tỷ lệ lấp đầy Khu A & Khu B, xuất báo cáo điểm rèn luyện theo trường.

---

## 5. HƯỚNG DẪN CÀI ĐẶT & CHẠY DỰ ÁN TRÊN XAMPP

### 5.1. Chuẩn bị môi trường
1. Cài đặt **XAMPP** (tích hợp PHP >= 8.0, Apache và MySQL).
2. Đặt toàn bộ mã nguồn dự án vào thư mục:
   ```
   C:\xampp\htdocs\Dormitory_Management
   ```
3. Khởi động dịch vụ **Apache** và **MySQL** trong XAMPP Control Panel.

### 5.2. Khởi tạo Cơ sở dữ liệu (Database)
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`.
2. Tạo database mới với tên: `qlktx_dhqg_hcm`, bảng mã: `utf8mb4_unicode_ci`.
3. Nhấp chọn database `qlktx_dhqg_hcm`, chuyển sang tab **Import**, chọn tệp `qlktx_dhqg_hcm.sql` (hoặc `database/qlktx_dhqg_hcm.sql`) và bấm **Import**.

### 5.3. Cấu hình biến môi trường (.env)
Tệp `.env` đã được cấu hình mặc định sẵn sàng cho XAMPP:
```ini
APP_NAME="Hệ thống Quản lý KTX ĐHQG-HCM"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost/Dormitory_Management

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qlktx_dhqg_hcm
DB_USERNAME=root
DB_PASSWORD=""
DB_CHARSET=utf8mb4

HOLDING_HOURS=72
ABSENCE_WARNING_DAYS=30
```

### 5.4. Cập nhật Autoload của Composer (Nếu cần)
Mở cửa sổ dòng lệnh (Terminal/PowerShell) tại thư mục dự án và chạy:
```bash
composer dump-autoload
```

### 5.5. Truy cập ứng dụng & Tài khoản kiểm thử
Mở trình duyệt web và truy cập:
 **`http://localhost/Dormitory_Management`**

Danh sách tài khoản kiểm thử đã được nạp sẵn trong CSDL:

| Phân hệ / Vai trò | Tên đăng nhập | Mật khẩu mẫu | Ghi chú tài khoản |
| :--- | :--- | :--- | :--- |
| **Sinh viên** | `2110001` | `Demo@123` | Sinh viên Nguyễn Văn An (Trường ĐH Bách khoa, Phòng A01.101) |
| **Sinh viên** | `2212345` | `Demo@123` | Sinh viên Trần Thị Mai Chúc (Trường ĐH Khoa học Tự nhiên) |
| **Cán bộ BQL Cụm** | `NV001` | `Demo@123` | Cán bộ Trần Văn Minh (Trưởng BQL Cụm A.G) |
| **Cán bộ BQL Cụm** | `NV002` | `Demo@123` | Cán bộ Nguyễn Thị Thu Hương (Cụm A.H) |
| **Quản trị viên** | `admin` | `Demo@123` | Quản trị viên toàn hệ thống Khu A & Khu B |

### 5.6. Chạy các tiến trình ngầm (Cron Jobs qua dòng lệnh)
- **Quét thu hồi chỗ quá hạn 72h:**
  ```bash
  php cron/cron_auto_release_72h.php
  ```
- **Quét sinh viên vắng mặt liên tục quá 30 ngày:**
  ```bash
  php cron/cron_check_absence_30d.php
  ```
- *(Tùy chọn tiện ích)*: Có thể chạy trực tiếp trên trình duyệt qua URL:
  - `http://localhost/Dormitory_Management/cron/trigger_release`
  - `http://localhost/Dormitory_Management/cron/trigger_absence`
