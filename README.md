# E-commerce Room Rental Platform (Backend)

Đây là mã nguồn Backend (API) cho nền tảng thuê phòng (E-commerce Room Rental Platform). Dự án được xây dựng dựa trên framework **Laravel** (PHP).

## Hướng dẫn cài đặt và khởi chạy dự án khi mới clone về

Vui lòng làm theo các bước dưới đây để thiết lập và chạy dự án trên máy tính của bạn:

### 1. Yêu cầu hệ thống
Đảm bảo máy tính của bạn đã được cài đặt sẵn:
- **PHP**: Phiên bản 8.2 trở lên.
- **Composer**: Trình quản lý package cho PHP.
- **Node.js & npm/pnpm**: Dành cho việc xử lý các file tài nguyên tĩnh (Vite).

### 2. Cài đặt các thư viện (Dependencies)
Mở terminal tại thư mục gốc của dự án vừa clone và chạy các lệnh sau:

Cài đặt các gói PHP thông qua Composer:
```bash
composer install
```

Cài đặt các gói Javascript thông qua npm:
```bash
npm install
```

### 3. Thiết lập biến môi trường
Tạo file cấu hình môi trường `.env` bằng cách sao chép từ file `.env.example`:
```bash
cp .env.example .env
```
*(Trên Windows CMD bạn có thể dùng lệnh `copy .env.example .env` hoặc làm thủ công).*

Sau đó, sinh khóa bảo mật (Application Key) cho ứng dụng bằng lệnh:
```bash
php artisan key:generate
```

### 4. Cấu hình và khởi tạo Cơ sở dữ liệu
Mặc định trong file `.env.example`, dự án đang sử dụng cơ sở dữ liệu **SQLite** (`DB_CONNECTION=sqlite`). Đây là cách nhanh nhất để khởi chạy trên máy local mà không cần cài đặt phần mềm CSDL nào khác. 
*(Nếu bạn muốn sử dụng MySQL/PostgreSQL, hãy chỉnh sửa lại các thông số `DB_*` trong file `.env`)*.

Tiến hành tạo các bảng trong cơ sở dữ liệu bằng lệnh:
```bash
php artisan migrate
```
*(Lưu ý: Nếu terminal hỏi bạn có muốn tạo file database `database.sqlite` không, hãy chọn Yes/Y)*.

Nếu dự án có dữ liệu mẫu (Seeder) và bạn muốn khởi tạo sẵn dữ liệu để test, bạn có thể chạy:
```bash
php artisan migrate --seed
```

### 5. Khởi chạy server phát triển (Development)
Dự án đã được thiết lập sẵn một lệnh tiện lợi để chạy đồng thời cả PHP Server, Vite và Queue. Chạy lệnh sau:
```bash
composer run dev
```

*(Hoặc nếu bạn muốn chạy thủ công, hãy mở 2 cửa sổ terminal: một bên chạy `php artisan serve` và bên kia chạy `npm run dev`).*

Sau khi chạy xong, Backend API của bạn sẽ hoạt động ở địa chỉ: `http://localhost:8000`. Frontend của bạn có thể gọi API tới địa chỉ này.
