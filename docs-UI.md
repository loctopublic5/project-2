## 1. NGUYÊN TẮC CỐT LÕI (CORE PRINCIPLES)
```bash
 npm install axios bootstrap @popperjs/core sass
--------------------------------------------------

 ```
-----------------------------------------------
resources/views/
├── layouts/
│   └── app.blade.php       <-- Master Layout (Chứa khung xương chính)
├── partials/               <-- Các thành phần dùng chung
│   ├── header.blade.php    <-- Navbar, Logo, Cart Icon, User Dropdown
│   ├── footer.blade.php    <-- Copyright, Links
│   └── alert.blade.php     <-- Toast Notification (Success/Error)
├── pages/                  <-- Các trang cụ thể
│   ├── home.blade.php      <-- Trang chủ
│   ├── auth/               <-- Login/Register
│   └── products/           <-- Danh sách/Chi tiết sản phẩm
└── vendor/                 <-- (Nếu publish từ package)
-----------------------------------------------
Đây là "Luật sinh tồn" của dự án. Em cần tuân thủ tuyệt đối 4 nguyên tắc này:

# 🛡️ Nguyên tắc 1: Backend là Chân Lý (Single Source of Truth)

KHÔNG bao giờ tự tính toán tiền nong (Tổng tiền = Giá * Số lượng - Voucher) bằng Javascript.

Frontend chỉ làm nhiệm vụ: Hiển thị con số mà Backend (hoặc Mock Data) trả về.

Lý do: Javascript tính toán số thực (float) rất hay bị sai số (VD: 0.1 + 0.2 !== 0.3), dẫn đến lệch tiền với Database.

# 🚀 Nguyên tắc 2: Không chờ đợi (Mock First)

Backend đang code song song, API có thể chưa có hoặc bị lỗi.

Hành động: Khi chưa có API, em phải dùng file mock_data.js để hiển thị dữ liệu giả lên màn hình ngay lập tức.

Tuyệt đối không ngồi chơi đợi API. Giao diện phải chạy mượt với dữ liệu giả trước.

# 📱 Nguyên tắc 3: Mobile First (Ưu tiên điện thoại)

Khách hàng B2C mua sắm chủ yếu trên điện thoại.

Yêu cầu: Luôn bật Chrome DevTools (F12) -> Chế độ Mobile (iPhone 12/14 Pro) trong suốt quá trình code CSS.

Nếu giao diện vỡ trên Mobile -> Task đó chưa đạt (Failed).

# ⏳ Nguyên tắc 4: Phản hồi người dùng (User Feedback)

Khi người dùng bấm nút (Đăng nhập, Mua hàng, Thanh toán), hệ thống phải phản hồi ngay:

Disable nút bấm ngay lập tức (Để tránh bấm đúp gửi 2 đơn hàng).

Hiển thị Spinner/Loading icon.

Sau khi xong thì mới mở lại nút hoặc chuyển trang.

## 2. QUY TRÌNH XỬ LÝ 1 TASK (WORKFLOW)
```bash
Khi nhận một task (ví dụ: Làm màn hình Giỏ hàng), em hãy làm theo 5 bước sau:

Đọc kỹ UI & Data: Xem file ERD_ver3.pdf xem màn hình đó cần hiện những trường nào (VD: stock_qty, sale_price).

Dựng HTML tĩnh (Static): Dùng Bootstrap 5 dựng khung, chia cột (col-), tạo nút bấm cứng. Đảm bảo đẹp trên Mobile.

Binding Sự kiện: Viết code cho các nút bấm (Click nút Xóa thì làm gì? Click Tăng số lượng thì làm gì?).

Tích hợp API (Cuối cùng): Khi API xong, sử dụng hàm gọi axios.
```

## 3. BỘ TỪ KHÓA TRA CỨU (KEYWORDS FOR RESEARCH)

Nếu gặp khó, hãy copy các từ khóa này paste vào Google/ChatGPT để tìm code mẫu nhanh nhất.

# 🔐 Phần 1: Authentication (Đăng nhập/Token)

Làm sao lưu token đăng nhập?

🔍 Keyword: javascript localstorage setitem getitem, axios bearer token header interceptor

Làm sao kiểm tra đã login chưa để ẩn/hiện menu?

🔍 Keyword: javascript check localstorage key exists, dom manipulation show hide element classlist toggle

# 🛒 Phần 2: Catalog & Hiển thị (Sản phẩm)

Làm sao format số 100000 thành 100.000 đ?

🔍 Keyword: javascript Intl.NumberFormat currency vnd

Làm sao hiển thị HTML từ biến (Render Template)?

🔍 Keyword: javascript template literals map join, es6 destructuring assignment

Làm sao gạch ngang giá cũ?

🔍 Keyword: bootstrap 5 text-decoration-line-through class, bootstrap 5 text-danger

# 🛍️ Phần 3: Giỏ hàng & Logic (Cart)

Người dùng nhập số lượng quá nhanh, làm sao chặn spam API?

🔍 Keyword: javascript debounce function input event

Làm sao tính tổng tiền tạm tính ở Frontend (chỉ để hiện chơi)?

🔍 Keyword: javascript array reduce calculate sum

# 💳 Phần 4: Thanh toán & Xử lý lỗi (Checkout)

Làm sao bắt lỗi Backend trả về (Ví dụ: Thiếu tiền)?

🔍 Keyword: axios try catch error response status, javascript switch case http status code

Làm sao chuyển trang bằng code?

🔍 Keyword: javascript window location href, javascript window location replace

Làm sao lấy tham số trên URL (VD: ?id=123)?

🔍 Keyword: javascript urlsearchparams get param