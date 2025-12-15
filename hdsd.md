### HƯỚNG DẪN SỬ DỤNG TERMINAL
- Copy .env.example -> .env ( thêm APP_KEY và tên db ) 

1. Nhóm lệnh Cài đặt & Thư viện (Chạy đầu tiên khi tải dự án về)
# Tải toàn bộ thư viện PHP (Backend) - Bắt buộc khi clone về
composer install

# Tải toàn bộ thư viện JS/CSS (Frontend) - Bắt buộc khi clone về
npm install

# Tạo khóa bảo mật(bắt buộc)
php artisan key:generate

# Cài thêm một thư viện mới (Ví dụ cài gói Excel)
composer require maatwebsite/excel

# Gỡ bỏ một thư viện không dùng nữa
composer remove ten-goi-thu-vien

# Sắp xếp lại autoload (Dùng khi bị lỗi "Class not found")
composer dump-autoload
---------------------------------------------------------------------------

2. Nhóm lệnh Database (Cơ sở dữ liệu)
# Chạy migration để tạo bảng mới (Cập nhật thay đổi vào DB)
php artisan migrate

# Xóa sạch Database cũ và tạo lại từ đầu (Cẩn thận mất dữ liệu)
php artisan migrate:fresh

# Xóa sạch DB, tạo lại bảng VÀ chèn dữ liệu mẫu (Fake data)
php artisan migrate:fresh --seed
---------------------------------------------------------------------------

3. Nhóm lệnh Tạo file code (Tiết kiệm thời gian)
# Tạo một Model mới kèm theo file Migration (để tạo bảng)
php artisan make:model TenModel -m

# Tạo một Controller mới (để xử lý logic)
php artisan make:controller TenController

# Tạo một file Seeder (để tạo dữ liệu mẫu)
php artisan make:seeder TenSeeder
---------------------------------------------------------------------------

4. Nhóm lệnh Kiểm thử (Testing)
# Tạo một file test mới
php artisan make:test TenChucNangTest

# Chạy TOÀN BỘ các bài test trong dự án
php artisan test

# Chỉ chạy RIÊNG một bài test cụ thể (Ví dụ test Login)
php artisan test --filter LoginTest

# Dừng ngay lập tức nếu gặp lỗi đầu tiên (đỡ phải chờ hết)
php artisan test --stop-on-failure
---------------------------------------------------------------------------

5. Nhóm lệnh Vận hành & Sửa lỗi (Dùng hàng ngày)
# Bật Server ảo của Laravel (Chạy web)
php artisan serve

# Bật trình biên dịch Frontend (Để web tự nhận CSS/JS mới)
npm run dev

# Xóa cache cấu hình (Dùng khi sửa file .env mà code không nhận)
php artisan optimize:clear

# Hiển thị danh sách toàn bộ đường link (URL) của dự án
php artisan route:list
--------------------------------------------------------------------------
6. Cấu trúc thư mục làm việc theo yêu cầu:
project-2/
├── app/                  <-- QUAN TRỌNG NHẤT (Logic code nằm đây)
│   ├── Http/
│   │   └── Controllers/  <-- Nơi viết các hàm xử lý (Controller)
│   └── Models/           <-- Nơi định nghĩa dữ liệu (Model)
├── bootstrap/            <-- (Kệ nó - Bộ khởi động hệ thống)
├── config/               <-- Nơi chứa các cài đặt chung (ít khi sửa)
├── database/             <-- QUAN TRỌNG
│   └── migrations/       <-- Nơi thiết kế các bảng dữ liệu (Table)
├── public/               <-- Nơi chứa ảnh, file css/js đã xuất bản (Public ra ngoài)
├── resources/            <-- QUAN TRỌNG (Giao diện nằm đây)
│   ├── css/              <-- File CSS gốc
│   ├── js/               <-- File JS gốc
│   └── views/            <-- Các file HTML (đuôi .blade.php)
├── routes/               <-- QUAN TRỌNG (Định nghĩa đường link)
│   └── web.php           <-- File quy định các đường dẫn web
├── storage/              <-- Nơi lưu log lỗi, file upload tạm (ít đụng)
├── tests/                <-- Nơi viết code kiểm thử (User mới chưa cần quan tâm)
├── vendor/               <-- CẤM ĐỤNG VÀO (Thư viện PHP do Composer tải về)
├── node_modules/         <-- CẤM ĐỤNG VÀO (Thư viện JS do NPM tải về)
├── .env                  <-- CỰC QUAN TRỌNG (Cấu hình Database, Mật khẩu)
├── .gitignore            <-- File quy định cái gì không đẩy lên Github
├── composer.json         <-- Danh sách thư viện PHP cần dùng
└── package.json          <-- Danh sách thư viện JS cần dùng

7. MẸO
Trên Terminal, bạn có thể bấm phím Mũi tên đi lên (↑) trên bàn phím để gọi lại lệnh vừa gõ xong. Đỡ phải copy paste nhiều lần!