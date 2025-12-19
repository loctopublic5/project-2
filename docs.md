# 📘 PROJECT DOCUMENTATION & WORKFLOW

Tài liệu hướng dẫn quy trình phát triển, cài đặt và cấu trúc dự án.
**Mô hình áp dụng:** MVC mở rộng (Controller -> Service -> Repository).
-------------------------------------------------------------------------------------------------------
**Quy trình luồng dữ liệu (Data Flow) bắt buộc:**
1.  **Input:** Route (Modular) -> Request (Validation) -> Controller -> Service (Logic) -> DB.
2.  **Output:** DB -> Service -> Controller -> Resource (Transform JSON) -> View (Blade/JSON Response).

---

## 🛠 1. Cài đặt dự án (Cho thành viên mới)

Khi clone dự án về máy, hãy chạy lần lượt các lệnh sau để thiết lập môi trường:

```bash
# 1. Tải các thư viện PHP (Backend)
composer install

# 2. Tải các thư viện JS/CSS (Frontend)
npm install

# 3. Tạo file cấu hình môi trường (Nếu chưa có)
cp .env.example .env

# 4. Tạo khóa bảo mật ứng dụng
php artisan key:generate

# 5. Cấu hình Database trong file .env (DB_DATABASE, DB_USERNAME,...)
# Sau đó chạy lệnh tạo bảng:
php artisan migrate

# 6. Chạy dự án
npm run dev       # Tab 1: Build giao diện
php artisan serve # Tab 2: Chạy Server Laravel
# 7. Xem log error của api
tail -f storage/logs/laravel.log
```
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
app/
├── Exceptions/
│   └── BusinessException.php    <-- Custom Exception cho logic nghiệp vụ
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── OrderController.php
│   ├── Requests/
│   │   ├── BaseFormRequest.php  <-- Base Request (Validation)
│   │   └── OrderRequest.php
│   ├── Resources/
│   │   └── OrderResource.php
├── Services/                    <-- Service Layer
│   └── OrderService.php
├── Traits/
│   └── ApiResponse.php          <-- Trait chuẩn hóa JSON output         
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
│   └──api.php                      <-- Entry point
└──    api/
        └── v1/                      <-- Modular Routes
            ├── auth.php
            ├── orders.php
            └── products.php
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
---------------------------------------------------------------------------------------------------------------------------------

8. Quy trình Code (Workflow) 🚀
Mọi tính năng mới BẮT BUỘC phải tuân thủ luồng dữ liệu 5 bước sau (Bỏ qua bước tạo DB/Migration):

Nguyên tắc: Data chảy theo hình chữ V. Controller gọi Service -> Service gọi Repository -> Repository gọi Model.

# 📝 Bước 1: Model (Định nghĩa dữ liệu)
Khai báo các cột được phép thao tác ($fillable) và các mối quan hệ.

PHP
```bash
class Product extends Model {
    protected $fillable = ['name', 'price', 'content'];
}
```
# 📦 Bước 2: Repository (Kho hàng)
Viết hàm để lấy hoặc lưu dữ liệu. Tuyệt đối không viết logic tính toán ở đây.

PHP
```bash
// app/Repositories/ProductRepository.php
class ProductRepository {
    public function getAll() {
        return Product::orderBy('id', 'desc')->get();
    }
}
```


# 🧠 Bước 3: Service (Xử lý nghiệp vụ)
Gọi Repository để lấy dữ liệu, sau đó tính toán, validate, xử lý logic phức tạp.

PHP
```bash
// app/Services/ProductService.php
class ProductService {
    protected $productRepo;

    
    public function __construct(ProductRepository $productRepo) {
        $this->productRepo = $productRepo;
    }

    public function getList() {
        return $this->productRepo->getAll();
    }
}
```
# 👮‍♂️ Bước 4: Controller (Điều phối)
Tiêm Service vào, gọi hàm xử lý và trả về View. Controller phải "gầy" (ít code nhất có thể).

PHP
```bash
// app/Http/Controllers/ProductController.php
class ProductController extends Controller {
    protected $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

    public function index() {
        $products = $this->productService->getList();
        return view('products.index', compact('products'));
    }
}
```
# 🎨 Bước 5: Route & View (Hiển thị)
# Khai báo đường dẫn và hiển thị dữ liệu ra màn hình.

PHP
```bash
// routes/web.php
Route::get('/san-pham', [ProductController::class, 'index']);
```

# View: Nhận data từ phía controller trả về để về html 

HTML
```bash
<!DOCTYPE html>
<html>
<head>
    <title>Danh sách nhân viên</title>
</head>
<body>
    <h1>Danh sách nhân viên công ty</h1>

    <table border="1">
        <tr>
            <th>ID</th>
            <th>Tên</th>
            <th>Email</th>
            <th>Chức vụ</th>
        </tr>
        @foreach($danhSachNhanVien as $nv)
        <tr>
            <td>{{ $nv->id }}</td>
            <td>{{ $nv->name }}</td>
            <td>{{ $nv->email }}</td>
            <td>{{ $nv->position }}</td>
        </tr>
        @endforeach
    </table>
</body>
</html>
```
-----------------------------------------------------------------------------------
# 🗄 4. Quản lý Database & Dữ liệu mẫu (New)
# Quy trình tạo bảng và sinh dữ liệu giả (Fake Data) để test:

Migration (Bản vẽ): Tạo file migration để định nghĩa cột trong bảng.

Lệnh: php artisan make:migration create_ten_bang_table

Factory (Khuôn đúc): Định nghĩa cấu trúc dữ liệu giả (Faker).

Lệnh: php artisan make:factory TenModelFactory

Cấu hình trong file: database/factories/TenModelFactory.php

Seeder (Máy sản xuất): Gọi Factory để tạo số lượng lớn dữ liệu.

Sửa file database/seeders/DatabaseSeeder.php:

PHP

TenModel::factory(100)->create(); // Tạo 100 dòng giả
Chạy lệnh nạp: php artisan db:seed