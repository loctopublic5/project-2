# README - 
## 1. Hướng dẫn Chạy Dự Án

### Chạy Server Local
- Lệnh chạy server phía local: `php artisan serve`
- ... (Thêm hướng dẫn cài đặt môi trường nếu cần, ví dụ: composer install, npm install)

### Build và Watch Frontend/Upload
- Build frontend: `mix watch --mix-config=webpack.frontend.mix.js`
- Lắng nghe upload: `mix watch --mix-config=webpack.upload.mix.js`
- ... (Thêm lệnh production build: mix --production)

## 2. Tài Khoản và Mật Khẩu

### Tài Khoản Admin và Quản Lý
- Admin: `admin@gmail.com` / `admin@321`

### Tài Khoản Super Admin
- Super Admin: `sieuadmin@gmail.com` / `admins100`

### Tài Khoản Test
- Quản lý kho test: `khach1@gmail.com` / `khachso1`
- ... (Thêm tài khoản khác nếu cần, ví dụ: user test khách hàng)



## 3. Công Cụ Debug và Code Cleanup

### Regex cho Tìm Kiếm và Xóa
- Tìm kiếm toàn bộ `console.log`: `^\s*console\.log\(([^)]+)\);\s`
- Xóa các dòng trống: `^\s*^` (thay thế bằng trống)
- Xóa xuống dòng và các dòng `console.log`: `^\s*console\.log\(([^)]+)\);\s*^`

### Debug Query Laravel
- Lấy ra chi tiết query: `dd($query->toSql(), $query->getBindings());`
- ... (Thêm cách sử dụng Laravel Debugbar nếu tích hợp)

## 4. Lệnh SQL và Bảo Trì

### SQL Xóa Dữ Liệu Không Hợp Lệ
- Xóa not in:
  ```
  DELETE FROM ic_nhapkhothucte_item
  WHERE nhapkhothucte_id NOT IN (SELECT id FROM ic_nhapkhothucte1 WHERE id IS NOT NULL /*This line is unlikely to be needed but using NOT IN...*/ )
  ```
- ... (Thêm cảnh báo: Backup database trước khi chạy)


